# MNBT 主机管理 APP — 服务端接口契约（mobile_bridge v1.0.0）

> 读者：APP 端（Android / Kotlin）开发。
> 本文档描述 **站点侧插件 `app_plugins/mobile_bridge` 实际会返回什么**，不是 PRD 的复述。
> 凡与 PRD / mock server（`tools/mock/server.mjs`）不一致的地方，以本文为准，差异都收在 §12「APP 侧需要改的地方」。
> 部署与运维要求见同目录 `deploy-guide.md`。

---

## 0. 先给结论

1. 一条通道打通全部功能：`POST {base}/app/v1/{action}`，body 用 `application/x-www-form-urlencoded`（文件类动作用 `multipart/form-data`），鉴权用 `Authorization: Bearer <token>`。**没有 cookie、没有 CSRF、没有 GET 业务接口。**
2. 响应永远是那一个包络（§2），HTTP 状态码恒为 200，成败只看 body。**APP 现有的 `EnvelopeParser` 判定优先级（success > qk > 数字 code > 文案）与本插件完全对齐，不用改。**
3. 能力缺口不装作支持：面板做不到的动作一律 `code=1010` + 一句中文原因（§11）。APP 现在把 1010 映射成 `MnbtException.PluginTooOld` → `HostOutcome.Failed` → Snackbar，**这条路径正合适，别改成整页失效**。
4. kotlinx 是严格解码：**类型不对就整包失败**，多余字段无所谓。所有字段的 JSON 类型在 §3 和逐 action 表里写死了，请照抄 DTO，不要指望"字符串数字也能parse"。
5. 本次全部逻辑在插件目录内完成，**没有改 MNBT 核心任何一个文件**；插件只新增两张自己的表。
6. 覆盖度：`MnbtActions.kt` 里声明的 **69 个 action（含 `pair`）全部有实现**，一个都不缺。插件另外多给 5 个当前 APP 没调的接口：`device.list`、`db.access.get`、`db.backup.create`、`db.backup.delete`、`site.proxy.get`（前 4 个正是 §12 里让 APP 改接的目标）。GET `/app/v1/pair_page`（扫码落地页）也在插件里。

---

## 1. 通道

### 1.1 URL 的两种形态

| 形态 | 请求行 | 前提 |
|---|---|---|
| A（推荐，无 rewrite 也能跑） | `POST {base}/index.php?_r=/app/v1/{action}` | 无 |
| B（美化路径） | `POST {base}/app/v1/{action}` | Web 服务器把所有非静态请求交给 `index.php` |

- `{base}` = 站点根，含子目录部署前缀、不带尾斜杠，例如 `https://panel.example.com` 或 `https://example.com/mnbt`。
- `{action}` 就是 §5~§10 表里的动作名，含点号（`db.list`、`file.upload_prepare`），插件按 `[^a-z0-9_.]` 过滤后小写匹配。
- 仓库**没有自带 .htaccess**，形态 B 是否可用取决于客户环境。APP 建议**统一用形态 A**，配对二维码里服务端给的也是形态 A。
- 配对二维码内容（面板「APP 配对」页生成）：
  `{base}/index.php?_r=/app/v1/pair_page&c={8位码}`
  APP 扫码器需要**从这条 URL 里同时解析出 `{base}` 和 `c`**，然后向 `{base}` 发 `pair`。非本 APP 的通用扫码器打开该 URL 会看到一个纯 HTML 说明页（GET，无鉴权）。

### 1.2 请求格式

- `Content-Type: application/x-www-form-urlencoded`，字段值一律字符串。
- 数组用重复键（`paths[]=…`）或 JSON 字符串（`file.list`/`paths` 两种都接受）——每个 action 在表里写明。
- 文件类动作（`file.upload`、`db.sql.import`）用 `multipart/form-data`，文件字段名固定 **`file`**；其余字段照常平铺。
- 鉴权头：`Authorization: Bearer <token>`。
  插件读取顺序：`getallheaders()` → `$_SERVER['HTTP_AUTHORIZATION']` → `$_SERVER['REDIRECT_HTTP_AUTHORIZATION']` → `apache_request_headers()`。
  **只要客户环境把 Authorization 头整层剥掉（常见于部分 WAF/宝塔防火墙/FastCGI 配置），所有 action 都会回 1001**，这点要在错误提示里给用户指路（详见 deploy-guide）。
- 除 `pair`、GET `pair_page` 之外全部要求令牌。

### 1.3 超时

APP 侧 `HostHttpClientFactory` 默认 CONNECT 15s / READ 30s。以下动作**必须单独放宽 readTimeout**：

| action | 建议 readTimeout |
|---|---|
| `deploy.run` | 180s（核心逐步 `sleep(3)`，实测几十秒起） |
| `ssl.apply` / `ssl.renew` | 120s（签发要过 ACME 校验） |
| `db.sql.import`、`file.upload`、`file.unzip`、`file.compress` | 120s |
| `domain.add` / `domain.del` | 60s（要动宝塔站点配置） |

---

## 2. 响应包络

```json
{
  "success": true,
  "code": 0,
  "msg": "ok",
  "redirect": null,
  "data": { "list": [], "total": 0 },
  "meta": {
    "action": "domain.list",
    "gn": "GetLogsdy",
    "api": "app/v1",
    "bridge_version": "1.0.0",
    "mnbt_version": "1.87",
    "ts": 1753000000
  },
  "list": [],
  "total": 0
}
```

- **`data` 的每个键同时平铺到顶层**（上例的 `list`/`total`）。这是刻意兼容 `EnvelopeParser.payloadOf()` 的"顶层非包络键 + data 合并"取法，APP 两端读法都不必调整。
- `meta` 是诊断信息，`ignoreUnknownKeys` 会自然忽略；`meta.action` 与请求一致，`meta.gn` 是这次实际转发的核心分支名，排查问题让客户把 `meta.gn` 报上来。
- 失败样例：

```json
{"success":false,"code":1008,"msg":"操作过于频繁，请稍后再试","redirect":null,
 "data":{"retry_after":473},"meta":{"action":"pair","api":"app/v1","bridge_version":"1.0.0","ts":1753000000},
 "retry_after":473}
```

### 2.1 错误码表

| code | 含义 | APP 建议处理 |
|---|---|---|
| 0 | 成功 | — |
| 1001 | 令牌缺失/无效/已吊销（核心会话类文案「请登陆」也归这条） | 走 `NeedRepair`，引导重新扫码 |
| 1002 | 配对码格式错/不存在/已用/已过期 | 留在配对页，提示重出码 |
| 1003 | 主机已到期 | 整页只读 `Blocked` |
| 1004 | 主机被封禁 | 整页只读 `Blocked` |
| 1005 | 主机已被删除 | 移除主机条目 |
| 1006 | 宝塔节点配置错误（站点侧问题） | `Failed` + 提示联系管理员 |
| 1007 | 参数错误 / 业务前置校验不通过 | Snackbar 显示 `msg`（msg 是中文可直接展示） |
| 1008 | 限流 | 用 `retry_after`（秒）倒计时 |
| 1009 | 上游（宝塔/核心 handler）失败 | Snackbar 显示 `msg` |
| 1010 | 该能力面板侧不存在 / 插件版本过低 | Snackbar，**不要**当作整页失效 |
| 5000 | 服务端异常（含核心致命错误、输出非 JSON） | Snackbar + `data.trace_id`，让客户报这个串 |

### 2.2 判定优先级与实现口径

- 插件永远显式给 `success` 布尔，所以 §EnvelopeParser 的第一优先级就够了，不会掉进 qk/数字 code 分支。
- `msg` 在成功时是中文提示（如「添加成功」），失败时是可直接展示的中文原因。**APP 直接把 `msg` 弹 Snackbar 是安全的。**
- 少数核心分支会"嘴上说成功"（历史代码里 `json_exit` 恒 `success=true`），插件在归一化层按中文关键词（失败/错误/禁止/最多/只能/未填写/已超过…）兜底翻成 `success=false` + 1009/1001。**所以 APP 不要再自己猜文案。**
- 非 JSON 输出（PHP 报错页、sysmsg HTML、WAF 拦截页）会被插件拦成 `code=5000`，**不会**把 HTML 吐给 APP。APP 仍保留自己的 `classifyNonJson()` 作为第二道防线（插件被禁用时直接 404/首页 HTML 就会走到那条路）。

---

## 3. kotlinx 类型红线

多余键无害，**类型不符就是硬解码失败**。插件已保证：

| 类别 | 字段 | JSON 类型 |
|---|---|---|
| 整数 | `id` `cycle` `code` `used_ms` `seconds` `size` `mtime` `total` `page` `page_size` `total_pages` `port` `bound` `limit` `ymbds` `expire_ts` `deleted`(计数) `remaining` `used_by` `max_tasks` `url_min_cycle` `resource_cycle` `fail_count` `resource_threshold` `level`(gzip) | `Int` / `Long`（字节数一律 `Long`） |
| 浮点 | `Quota.used` `Quota.max` `DbQuota.used` `DbQuota.max` `MemUsage.*` | `Double` |
| 布尔 | `ssl` `applied` `added` `deleted`（布尔位）`saved` `closed` `restored` `imported` `force_https` `need_db` `on_sale` `required` `done` `revoked` | `Bool` |
| 字符串 | `user` `domain` `name` `path` `dir` `src` `ver` `expire` `time` `addtime` `charset` `state` `unit` `status` `access` `perm` `owner` `group` `token` `content` `content_base64` `encoding` `current` `url` `result` `trace_id` | `String`（永不为 `null`，无值即 `""`） |
| 列表 | `list` `options` `dirs` `domains` `actions` `failed` `backups` `fields` | 对应对象的 `List` |
| 可空 | `last_code`（未跑过的任务）`cpu` `mem` | 显式 `null` |

特别点名三个坑：

1. **`Quota` 用 `max` 不是 `total`。** `PairModels.kt` 的 `Quota{used, max, unit}` 是唯一口径；`max<=0` 表示不限（`unlimited`）。列表里的 `total` 是分页计数，别喂给 Quota。
2. **`host` 是对象不是字符串。** `pair` / `host.profile` / `device.info` 返回的 `host` 是 §5.1 的那个块。
3. **`current` 永远是字符串**（gzip 是 `"on"`/`"off"`，PHP 版本是 `"7.4"`，运行目录是 `"/"`）。伪静态的 `current` **恒为 `""`**——宝塔查不到"当前生效的模板名"，APP 不要预选中。

---

## 4. 配对与设备（`pair` / `device.*`）

### 4.1 时序

```
面板「APP 配对」页点「生成配对码」
  → 面板 AJAX（cookie 会话）返回 {code, expires_in:120, qr_url}
  → APP 扫 qr_url，解析出 base + code
  → POST {base}/index.php?_r=/app/v1/pair   （无 Authorization）
      code, device_id, device_name, device_model, app_version, android_version
  → 返回 {token, site, host}；token 明文只出现这一次，库里只存 sha256
  → 之后所有 action 带 Authorization: Bearer {token}
```

- 配对码：8 位，字符集 `ABCDEFGHJKLMNPQRSTUVWXYZ23456789`（无 0/O/1/I），**TTL 120 秒、单次使用**。并发双花用 MyISAM `affected_rows` 抢占挡住。
- 同一 `device_id` 再次配对 = **换绑**：旧设备行 `status` 置 0（旧令牌立刻失效），插新行发新令牌。APP 换手机/重装要预期"旧设备自动下线"。
- 限流：`pair` 按 **IP 5 次 / 600 秒**，只统计**失败**尝试，成功一次即清零。超限回 `1008 + retry_after`。
- 面板侧生成配对码另有 **20 次 / 600 秒 / 用户** 的限流（走面板提示，不是 bridge 包络）。
- `device_id` 建议用 Android 侧稳定标识（安装期生成并持久化，别用 IMEI/序列号），最长 64 字符，其余字段分别截断 120/120/20/20。

### 4.2 逐 action

| action | 鉴权 | 入参 | 成功 `data` |
|---|---|---|---|
| `pair` | 无 | `code` `device_id`（必填）`device_name` `device_model` `app_version` `android_version` | `token`(64 hex) `site{base,name,mnbt_version}` `host`(§5.1 块) |
| `device.ping` | Bearer | — | `server_time`(`"Y-m-d H:i:s"`) |
| `device.info` | Bearer | — | `bridge_version` `mnbt_version` `actions`(String[]) `capabilities`(String[]) |
| `device.list` | Bearer | — | `list[]{id:Int, device_id, device_name, device_model, app_version, created_at, last_seen_at, last_ip}`（只含启用中的设备） |
| `device.revoke` | Bearer | — | `revoked:Bool`（吊销**当前令牌**，之后自己回 1001） |

- `site.base` 取「scheme + 本次请求的 Host + 站点部署前缀」，前缀按项目物理根相对 Web 根目录推出（子目录部署时是 `/mnbt` 这类），**不含 `/user`、`/admin` 等入口脚本目录**；**APP 只在与其一致时才采纳**（防串站），不一致就保留用户手填的 base。
- `actions` 是服务端支持的 action 全集，可用来做能力探测/灰度隐藏入口。`capabilities` 当前固定 `["file_bytes_transparent","chunked_upload","signed_download"]`。
- 面板的「APP 配对」页也能列设备/吊销设备（走 cookie AJAX），与 `device.list` 数据同源。

---

## 5. 主机概览与档案（`host.*`）

### 5.1 复用的主机块（`pair` / `host.profile` 的 `host`）

```jsonc
{
  "user": "u1001",            // String  主机账号
  "domain": "abc.example.com",// String  站点主域（sqldz）
  "site_name": "abc.example.com",
  "status": "running",        // String  running | banned
  "expire": "2026-12-31",     // String  原样日期，0000-00-00 表示不限
  "expire_ts": 1798761600,    // Int     同上，0 表示不限/无
  "node": "1",                // String  所属宝塔节点标识
  "ymbds": 5,                 // Int     域名绑定上限，0 = 不限
  "created_at": "2024-05-01", // String
  "web_quota":     {"used": 123.4, "max": 1024.0, "unit": "MB"},
  "sql_quota":     {"used": 12.0,  "max": 100.0,  "unit": "MB"},
  "traffic_quota": {"used": 0.0,   "max": 50.0,   "unit": "GB"}
}
```

单位口径照搬面板，**不要怀疑**：

- 网页 / 数据库空间：核心按 `size/(1024*1000)` 写入，所以是 **MB**（十进制）。
- 流量：库里 `llmax.dq` 是**字节**、`llmax.max` 是 **GB**，插件把 used 和 max 一起除以 `1024³`，统一报 **GB**。

### 5.2 逐 action

| action | 入参 | 成功 `data` | 说明 |
|---|---|---|---|
| `host.overview` | — | `web_quota` `sql_quota` `traffic_quota` `cpu:null` `mem:null` `inode_used:0` `inode_total:0` | 每次调用**先跑一次核心的用量刷新**（`gn=sxsyxx`，输出丢弃），再回读库里数据，所以拿到的是准实时值 |
| `host.profile` | — | §5.1 全部键 + `php`(String 当前 PHP 版本) | **不再返回 FTP/SQL 明文口令**（原 `credential` 块已删除） |

- `cpu` / `mem` / `inode` 面板端**没有按主机采集的入口**（核心只统计空间与流量），固定 `null` / `0`。APP 请把这三项做成"无数据就隐藏"，不要画 0% 的条。
- `host.profile` 的 `php` 是实时问宝塔拿的，可能为空串（节点不通时）。

---

## 6. 站点设置（19 个 + `site.init`）

### 6.1 `site.init`：所有读侧统一入口

入参 `section`（缺省 `php`）。返回 `section` + 该分支的字段：

| `section` | 返回 |
|---|---|
| `php` | `current`(String 当前版本) `options`(String[]) |
| `yxml`（运行目录） | `current`(String) `options`(String[]) |
| `mrwd`（默认文档） | `current`(String) |
| `wjt`（伪静态模板列表） | `current`(**恒 `""`**) `list`(String[]) |
| `gzip` | `current`(`"on"`/`"off"`) `level`(String) `min_len`(String) `types`(String) |
| `pass` / `site.passdir.list` | `list[]{dir:String, user:String, pass:"****"}` — **口令不回显，固定 4 个星** |
| `xgpass` | `user`(String) `ftp_hint`(String) |
| `cache` / `site.cache.list` | `list[]{id:Int, url:String, type:String, seconds:Int, time_out:String}` |
| `url`（反代） | `btip`(String) `als`(String `"true"`/`"false"`) `list[]{proxy_url,target_url}` |
| `mysqlcz`（数据库访问权限） | `current`(String `127.0.0.1`/`%`) `access`(String 同值) |
| `indexconf` | **`1010`**（见 §11） |

### 6.2 写侧逐 action

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `site.php.set` | `php_version` | `php_version` `phpversion`(同值 String) | |
| `site.rundir.set` | `rundir` | `rundir` | |
| `site.indexdoc.set` | `index` | `indexdoc` | |
| `site.rewrite.get` | `name`（可省，= 当前生效内容） | `current:""` `rewrite`(String nginx 片段原文) `list`(String[]) `name` `content` | 内容为空是合法的（站点没写伪静态） |
| `site.rewrite.set` | `rewrite` | `rewrite` | 插件补记面板日志「伪静态」 |
| `site.gzip.set` | `gzip`(`on`/`off`，兼容 `status`)，可选 `level` `min_len` `types` | `gzip`(`"on"`/`"off"`) | **不带可选参数时沿用核心默认**（level 6 / 1k / 默认类型串），别为了"填全"而回写默认值 |
| `site.passdir.add` | `user_dir`(或 `dir`，目录全路径) `user` `pass` `name`(可选，省略则取 `user_dir` 的最后一段) | `action:"add"` `added:Bool` `dir` `user` | |
| `site.passdir.del` | `user_dir`(或 `dir`) | `action:"del"` `deleted:Bool` `dir` | |
| `site.password.set` | `pass`（或 `newpass`；可分别用 `ftp` `sql` 覆盖） | `action:"password"` `user` | **口令绝不进日志、绝不回显**；`ftp`/`sql` 都为空 → 1007 |
| `site.hotlink.get` | — | `open:Int`(0/1) `lasts:Int`(**恒 0**) `domains:String` `exts:String` | `lasts`：宝塔防盗链没有"有效时长"概念，APP 按不限时显示 |
| `site.hotlink.set` | `open`(或 `status`) `exts`(或 `fix`) `domains` `return_rule` `http_status` | 同上结构（回显提交值） | 插件补记日志；`return_rule`/`http_status` 只透传核心、不回显 |
| `site.proxy.get` | — | `list[]{proxy_url:String, target_url:String}`（**下划线**，与 `site.init?section=url` 同形） | 插件补的扩展 action，`MnbtActions` 里没有；APP 现在用 `site.init(section=url)` 拿同一份数据 |
| `site.proxy.set` | `proxy_url`(默认 `/`) `target_url` | `list[]{proxyUrl:String, targetUrl:String}`（**驼峰**，与 APP 的 `SiteAProxyRule` 一致，刻意与读侧不同名） | **`proxy_url` 非 `/` → `1010`**；`target_url` 为空 = 删除该 location 的规则（APP 现有"置空提交"的删法直接可用）；新增时会自动剥掉 `http://` 前缀（宝塔原语自己加） |
| `site.cache.add` | `url`(=后缀，兼容 `suffix`/`type`) `seconds` | `action:"add"` `added:Bool` `url` `id:Int` `type` `seconds` | `id` 是**新增后按后缀回查的序号** |
| `site.cache.edit` | `id` `url`(新后缀，可省) `seconds` `old_suffix`(可省) | `action:"edit"` `url` `old_suffix` `id` | `id` 解析不到后缀 → 1007「缓存规则不存在（id 已失效，请刷新列表）」 |
| `site.cache.del` | `id` `suffix`(可省) | `action:"del"` `deleted:Bool` `url` `id` | 同上 |
| `site.indexconf.set` | — | — | **`1010`**（见 §11） |

**静态缓存的 id 协议（重要）**：宝塔的缓存规则**没有主键**，真实契约是「后缀 + 时长」。插件把列表按出现顺序赋 `id = 1,2,3…`，`edit`/`del` 收到 `id` 后再反查后缀。
→ APP 必须**先 `site.cache.list` 再改删**，且**不要把 id 持久化**（列表顺序变了 id 就变）。同一次列表里用 id 是稳定的。

---

## 7. 文件管理（16 个）

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `file.list` | `path`(默认 `/`) `page` `limit`(默认 500) `sort` `sortOrder` | `cwd` `path` `list[]{name,path,type:"file"/"dir",size:Long,mtime:Long(Unix 秒),perms:"",owner:"",group:""}` `total:Long` | **只返回该层的直接子项**（mock 的递归展平请去掉）。`path` 已拼好，APP 直接下钻即可。`perms`/`owner`/`group` 面板单层列表拿不到，恒空串 |
| `file.read` | `path`（兼容 `file`） | `path` `name` `encoding`(String，宝塔判定，可能空) `size:Long`(字节) `content_base64`(String) `content`(String) | **优先用 `content_base64` 解码**——GBK/二进制走 `content` 是有损兜底。`encoding` 仅供参考，APP 侧 `EditorModels` 的严格 UTF-8 自行判定是更可靠的依据 |
| `file.save` | `path` `content` | `path` `saved:Bool` `size:Int` | `content` 按原字节写，不做任何转义/再编码 |
| `file.create` | `path`(完整目标路径) `type`(`file`/`dir`) | `path` | 目录/文件同一条，按 `type` 分 |
| `file.delete` | `path` `type` | `path` | |
| `file.delete_batch` | `paths[]`（数组或 JSON 串） | `deleted:Int` `failed:String[]` | 按目录分组批删；`.user.ini`、运行目录、绑定子目录受核心保护。有任一项失败 → `success:false` + `code:1009`，但 `deleted`/`failed` 仍带计数 |
| `file.rename` | `src`(旧全路径) `dest`(新全路径)，兼容 `old_name`/`new_name` | `src` `dest` | |
| `file.copy` | `src` `dest` `type`(`copy`/`cut`) | `src` `dest`(实际落点) `type` | `dest` 以 `/` 结尾=目录（保留同名），否则视为含改名的完整目标。拒绝源目标同路径、拒绝把目录复制进自己 |
| `file.compress` | `files`(或 `path`，单个全路径) `dest` `type`(默认 `zip`) | `dest` | |
| `file.unzip` | `src` `dest`(默认同目录) `coding`(默认 `UTF-8`) `password` | `dir` | 加密包传 `password` |
| `file.size` | `files`(或 `path`) | `path` `size:Long` `unit:"B"` `files` | 核心回的是浮点，插件已整型化 |
| `file.upload_prepare` | `path`(完整目标路径) `size`(总字节数) | `upload_id`(`dir|name`) `path` `name` `size:Long` `max_size:Long`(0=不限) `chunk_size:Long`(2MB) `resume_offset:Long` | `resume_offset` 是宝塔已收到的字节数，APP 据此跳过分片 |
| `file.upload` | **multipart**：`file`=分片二进制；`upload_id` `size`(总字节，**必填**) `index`(1-based 分片序号) `total` `name`(兼容 `file_name`；无 `upload_id` 时改用 `path`) | 未完成：`received:Long`(累计已收) `total_size:Long` `size:Long`(同 received) `done:false`；完成：`done:true` `path` `size:Long` | 见下方"分片协议"；**APP 现在一个 `size` 都没发，这条不改就必然 1007** |
| `file.download` | `file`(或 `path`) | `filename` `file` `url`(可直接 GET 的外链) `expires_hint:"24h"` | URL 是宝塔 token 型链接，**APP 用 rawClient GET，不带 Authorization**。面板拿不到文件大小：APP 的 `FileDownloadPayload.size` 会取默认 0，别显示它 |
| `file.access.get` | `path` | `path` `perm`(`"0644"` 形式) `owner` `group` | |
| `file.access.set` | `path` `perm`(兼容 `access`) `recurse`(`0`/`1`) | `path` `perm` `recurse` | |

### 7.1 分片上传协议（APP↔插件翻译）

宝塔的原始协议是 `start`（**累计字节偏移**）+ `size`（**整个文件的总字节数，全程不可变**），分片落 `名字.<size>.upload.tmp`；APP 侧发的是 `index/total`（1-based **分片序号**）。插件按 `主机|目录|文件名` 记偏移量做翻译：

- `index <= 1` 或状态文件超过 24h → `start=0`（重头开始）。
- 其余情况 `start = 已收字节数`，每次成功后累加。
- **`size` 必须随每个分片一起上报**，缺失直接 `1007`（文案里写明了原因）。
- 中断重启用 `file.upload_prepare` 拿 `resume_offset`；APP 若从第 N 片续传，直接把 `index` 设为对应序号即可（插件信任偏移量、按字节推进）。
- 并发上传同一文件名会互相踩状态；APP 串行传同一文件。
- 网页空间配额在插件里预检（三条核心文案原样透传，`msg` 可直接展示）。
- `.user.ini` 禁止上传/删除。

---

## 8. 数据库（11 个）

**面板现实：一台主机 = 一个库 = 一个账号**，库随主机开通产生，没有建库/删库原语。插件据此把语义对齐到"备份"：

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `db.list` | — | `list[]{name,username,charset,state:"RUNNING"/"",addtime,backups[]{name,size:Long,time}}` `quota{used,max,unit:"次"}` `sql_space{used,max,unit:"MB"}` `db_id` `backup_count:Int` | `list` **恒 0 或 1 条**，`name`/`username` 都是 `sqluser`。**`quota` 是"还能备份几次"的次数配额（`unit` 就是 `"次"`），`sql_space` 才是数据库空间配额** —— APP 的 `DbListData.quota` 目前绑的是前者，若要显示空间请把 `sql_space` 也接上。`charset` 取不到时为空串（仅影响显示） |
| `db.backup.create` | — | `action:"backup_create"` `name` `username` `quota` | **这是 APP「新建数据库」按钮该改接的动作**（新增备份） |
| `db.backup.delete` | `name`(备份文件名，兼容 `file`/`filename`) 或 `id` | `action:"backup_delete"` `name` `deleted:Bool` `quota` | 核心删备份要数字 id，插件按文件名反查 |
| `db.delete_all` | — | `deleted:Int`(清空前的表数) `remaining:0` | 破坏性：清空当前库的所有表。面板核心分支不记日志，插件补记「清空数据库」 |
| `db.backup.download` | `file`(兼容 `filename`/`name`) | `file` `url`(可直接 GET) `size:0` | `size` 面板拿不到，固定 0 |
| `db.backup.restore` | `name`(备份文件名) `data_account`(可选，只校验) | `name` `db`(=`sqluser`) `restored:Bool` | 目标库一律取本主机 `sqluser`；APP 传了别的库名 → 1007 说明原因 |
| `db.access.set` | `host`(或 `access`：`localhost`/`%`) `port` | `db` `access`(`localhost`/`%`) `port:Int` `applied:Bool` | 核心权限值是 `127.0.0.1`/`%`，插件双向映射成 APP 的 `localhost`/`%`。**面板没有改端口能力，`port` 只回显不生效**（宝塔端口固定） |
| `db.access.get` | — | `db` `access` `port:0` `applied:false` | |
| `db.sql.import` | **multipart**：`file`=SQL 文件；非文件字段 `name`(=目标库名，只校验)；或直投时 `path`/`file`/`filename` 指向站点内已存在的 .sql | `file`(客户端原名) `db` `imported:Bool` | 两种形态都支持：直投（插件落安全临时名 `app_时间戳_摘要.sql`，用完即删）或先用 `file.upload` 传好再引路径。**大 SQL 请走 multipart 并放宽 readTimeout** |
| `db.create` | — | — | **`1010`** |
| `db.delete` | — | — | **`1010`** |

- 1010 的 `msg` 已经写了替代路径（"请调 db.backup.create / db.backup.delete"），APP 直接把 `msg` 展示出来不会误导，但**更好的做法是按 §12 把按钮语义改掉**。
- 数据库口令**从不出现在任何 action 的响应或日志里**。

---

## 9. 域名与 SSL（10 个）

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `domain.list` | — | `list[]{domain,port:Int,ssl:Bool,type:"main"/"top"/"subdir",status:"ok"}` `total:Int` `limit:Int`(=`ymbds`，0=不限) | **主域恒为 `list[0]`**（APP 用 `index==0` 打「主机主域名」标签，成立）。`ssl` 是站点级状态，所有条目同值 |
| `domain.subdir.list` | — | `list[]{domain,dir}` `dirs:String[]` | `list`=已存在的"域名→目录"绑定；`dirs`=运行目录下可选的子目录名（**APP 现在没用它**，改版时把候选换成 `dirs` 即可） |
| `domain.add` | `domain` `dir`(或 `dirs`：含 `/` 视为目录绑定) | `domain` `port:80` `added:Bool` `bound:Int` `limit:Int` | 校验：格式、禁止本站 IP、重复绑定、`ymbds` 上限（口径与网页面板逐字一致：已绑定数 ≥ `ymbds+1` 就拦）、商品域名限制插件在场时一并校验。`dir` 含 `/` 走目录绑定，否则走顶级域名绑定 |
| `domain.del` | `domain` `port`(可选) `dir`(可选) | `domain` `port:Int` `deleted:Bool` | 禁删主机主域。APP 的 `DomainItemDto` 不带 `dir`，插件按域名查绑定表自动判定走普通删除还是目录删除 |
| `ssl.get` | — | `ssl{expire,issuer,domains:String[],status}` `force_https:Bool` `pem` `key` `type` `csr` | `ssl.status` 是可直接展示的中文；无证书时 `expire`/`issuer` 为空串、`domains` 为空数组 |
| `ssl.apply` | `domains`(可选，String[] 或空格/逗号分隔) | `order_id:""` `domains:String[]` `result`(String 核心原话) `expire:""` | 不传就自动取站点全部域名申请。**`order_id`/`expire` 面板拿不到，固定空串** |
| `ssl.renew` | 同上 | 同上 | 当前站点没有生效证书 → `1007`，文案引导改用「申请证书」 |
| `ssl.custom` | `key` `pem` | `saved:Bool` `has_key:Bool` `has_pem:Bool` | 留空 → `1007`「错误！禁止留空！」。**证书内容绝不进日志** |
| `ssl.close` | — | `closed:Bool` | |
| `ssl.force_https` | `qk`(`1`/`0`，缺省 1=开启) | `force_https:Bool` | |

---

## 10. 监控 / 通知 / 一键部署（10 个）

### 10.1 监控

`limit` 块（`monitor.list` 返回）固定 `{max_tasks:5, url_min_cycle:60, resource_cycle:180}`。
（核心 URL 任务的下限其实是 15 秒，面板 UI 用 60 秒，插件跟随 UI 口径。）

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `monitor.list` | — | `list`(任务行) `tasks`(同一份，兼容旧键) `limit` `task_count:Int` `max:5` `alert_count:Int` | `alert_count` = 最近状态码 ≥400 的任务数 |
| `monitor.save` | `id`(新增传 0/省略) `name` `type`(`url`/`resource`) `target` `cycle` | `id:Int` `name` `type` `saved:Bool` | 见下方"字段补齐"与"资源任务的 target" |
| `monitor.delete` | `id` | `id` `deleted:Bool` | |
| `monitor.toggle` | `id` `status`(`on`/`off`) | `id` `status`(`"on"`/`"off"`) | 核心读的是 `enabled='true'/'false'`，插件做转换 |
| `monitor.log` | `id`(0=全部) `page` `page_size`(10/15/20/25/50/100，默认 20) | `list[]{time,target,code:Int,used_ms:Int,status,error}` `total` `page` `page_size` `total_pages` | 资源任务的日志 `target` 回任务名（其 url 恒空） |

任务行结构（`list`/`tasks` 元素）：

```jsonc
{ "id":1, "name":"首页", "type":"url",            // url | resource
  "target":"https://abc.example.com",             // resource 任务时这里是 "web"|"sql"|"traffic"
  "cycle":60, "status":"on",                      // on | off
  "last_code":200,                                 // Int，未跑过为 null
  "last_time":"2026-10-20 12:00:00",
  "resource_threshold":80, "last_status":"ok", "last_error":"", "fail_count":0 }
```

- **字段补齐**：APP 表单只发 5 个字段，核心还要求 method/timeout/判定规则/失败阈值/是否通知/是否启用。插件策略是「新增取默认（GET / 10s / eq 200 / none / 阈值 1 / 邮件通知开 / 启用开），编辑沿用旧行」——**编辑一条任务不会把隐藏字段重置掉**。
- **资源任务的类型走 `target`**：APP 没有"资源类型"选择器，插件把 `resource_type` 放在 `target` 位置回传、也接受同名回传，所以 APP 现表单原样提交即可复原。要让用户在 APP 上选 web/sql/traffic，就在 `target` 里放这三个词之一（非法值回落到旧值或 `web`）。
- 新增任务的自增 id 核心不回，插件按「本人 + 同名最新一条」回查，拿到就是 0 表示回查失败。

### 10.2 通知

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `notice.list` | `page` `page_size`(默认 15) `type` `level` `keyword` | `list[]{id:Int,title,content,time,read:0/1,type,level}` `unread:Int` `total` `page` `page_size` `total_pages` | `unread` 是**标记已读之前**预查的真实未读数 |
| `notice.read` | `id`（**省略或 0 = 全部标记已读**） | `id` `read:1/0` | |

### 10.3 一键部署

| action | 入参 | 成功 `data` | 备注 |
|---|---|---|---|
| `deploy.list` | — | `list[]{id:Int,name,ver:"",src:String,dir:"/",intro,price,used_by:Int,on_sale:Bool}` | `src` 是面板存的图标路径（相对站点）。`ver` **恒空串**：`MN_bs` 表没有版本列 |
| `deploy.form` | `bs_id`（兼容 `id`） | `id:Int` `name` `need_db:Bool` `create_dir:"/"` `fields[]` `intro` | `need_db` = 该程序要求数据库空间 > 0 |
| `deploy.run` | `bs_id` + **`fields[].key` 平铺成同名参数** | `task_id:""` `status`(String，核心原话) | **破坏性动作** |

`fields[]` 元素：

```jsonc
{ "key":"mysql_name",         // 提交时用的参数名（= 核心 MN_bs.inp 的 cz）
  "label":"数据库名", "type":"input",
  "default":"",               // 恒空串：isnr 在面板里是 placeholder，不能当值提交
  "options":[], "required":true, "placeholder":"请输入数据库名", "max_length":30 }
```

- 面板的部署表单只有文本/数字 input，**没有 select**，所以 `type` 恒 `"input"`、`options` 恒空。
- **`deploy.run` 会先删光站点根目录文件再解压源码包，且逐步 `sleep(3)`**，整轮几十秒 → 必须放宽 readTimeout（§1.3），并在 APP 上做二次确认 + 进行中不可重复提交。
- `task_id` 面板不返回（是同步执行，不是队列任务），APP 现有 `message = taskId.ifBlank{status}` 的取法会自然落到 `status`，无需改。
- 日志只记「任务 id」，**表单值一律不进日志**（管理员在里面填的是明文口令）。

---

## 11. 面板做不到的事（全部返回 1010 + 中文原因）

| action | 为什么 |
|---|---|
| `db.create` | 无建库原语：一主机一库，库随主机开通产生。请改接 `db.backup.create` |
| `db.delete` | 删库会连带主机账号失效，面板没这个动作。删除语义只针对备份文件 → `db.backup.delete` |
| `site.indexconf.set` | 核心 `indexconf` 是首页的只读汇总页，**没有写入入口**，且不拿站点开关冒充 |
| `site.init?section=indexconf` | 同上 |
| `site.proxy.set`（`proxy_url` ≠ `/`） | 宝塔原语把代理路径写死 `/`，且签名请求函数是 private，插件无法自建子路径规则 → 与其建一条错规则不如明确拒绝 |

另外这些字段是"面板拿不到"而非失败，用固定值占位，**APP 请按 §12 处理展示**：
`host.overview` 的 `cpu`/`mem`/`inode_*`；`deploy.list.ver`；`deploy.run.task_id`；`ssl.apply`/`renew` 的 `order_id`/`expire`；`db.backup.download.size`；`site.rewrite.get.current`；`site.passdir.list` 的 `pass`（固定 `****`）；`db.access.set.port`（回显不生效）。

---

## 12. APP 侧需要改的地方（逐条可执行）

| # | 位置 | 现状 | 要改成 |
|---|---|---|---|
| 1 | `DatabaseModels.kt` / 数据库页 | 「新建数据库」发 `db.create`、「删除」发 `db.delete` | 语义改成**备份**：按钮接 `db.backup.create` / `db.backup.delete`；`db.list` 恒 ≤1 条，页面别按多库设计 |
| 2 | 同上，备份删除按钮 | 传 `id` | 传 **`name`（备份文件名）**（插件会反查 id；传 id 也兼容） |
| 3 | SQL 导入 | 按普通 form 发 | 走 **multipart**，文件字段 `file`，非文件字段 `name`=目标库名（可省略），并放宽 readTimeout |
| 4 | `DbQuota` / `Quota` | 读 `total` | 读 **`max`**，`max<=0` 显示"不限"；`unit` 直接用（网页/库=MB，流量=GB，**数据库页的 `quota` 是"次"**）。数据库页建议把 `db.list.sql_space` 也接上：`quota` 是备份次数配额，空间配额在它之外 |
| 5 | `PairData` / 配对流程 | host 当字符串、流量按 MB 画 | `host` 是对象（§5.1）；**流量单位是 GB**；令牌只在配对响应出现一次，落盘要加密 |
| 6 | `HostOverview` | 画 CPU/内存/inode 条 | 三项**隐藏**（服务端恒 `null`/`0`） |
| 7 | `HostProfile` | 有/计划有 FTP+SQL 凭证展示 | **凭证块服务端不再下发**，凭证页要么去掉要么改成"只改不读"（改密走 `site.password.set`） |
| 8 | 监控资源任务 | 表单只有 target 一个输入框 | 资源任务把 `target` 当**资源类型**回显/回传（`web`/`sql`/`traffic`）；要做选择器就往 `target` 放这三个词 |
| 9 | 监控开关 | 发 `enabled` | 发 **`status=on/off`**（或两者都不发按默认）；核心字段转换在插件里 |
| 10 | 通知已读 | — | 支持"全部已读"：`notice.read` 不传 `id` 或传 0 |
| 11 | `deploy.run` | 30s 默认读超时、可连点 | `readTimeoutSeconds≈180` + 二次确认 + 执行期禁用按钮 |
| 12 | `deploy.form` | 期望 `default` 有值、可能有 select | `default` 恒空、`options` 恒空，只渲染 input + `placeholder` + `required` + `max_length` |
| 13 | 域名子目录候选 | 用 `${domain} → ${dir}` 现绑定 | 改用 **`domain.subdir.list.dirs`** 作候选列表（`list` 保留给已绑定项） |
| 14 | 反代 | 支持子路径规则 | 只支持根路径 `/`：新增按钮只在 `location=="/"` 可用，子路径明确提示不支持 |
| 15 | 静态缓存 | 持久化 `id` | 每次改删前先 `site.cache.list` 取最新 id；**不要缓存 id** |
| 16 | 文件列表 | 期望递归展平 | 只有一层，按 `type=="dir"` 下钻（`path` 已拼好） |
| 17 | 文件读/写 | 用 `content` | 读优先 **`content_base64`**；写按原字节提交 `content` |
| 18 | 分片上传 `FileBrowserViewModel.streamChunks` | 只发 `upload_id/path/file_name/index/total`，**没有 `size`** | **每片必须带 `size`（整个文件的总字节数）**，否则必然 1007；`UploadPreparePayload` 还要**加 `resume_offset` 字段**才能做断点续传（服务端已回，APP DTO 里没有，现在拿不到偏移） |
| 19 | 下载 | 带 Authorization GET | **rawClient GET，不带鉴权头**（宝塔 token 外链） |
| 20 | 错误映射 | — | `1010` 保持 Snackbar（**非破坏性**），`1003/1004` 走整页只读，`1001/1005` 走重新配对，`1008` 用 `retry_after` 倒计时 |
| 21 | URL 拼接 | rewrite 形态 | 统一 `{base}/index.php?_r=/app/v1/{action}`；扫码时从 `qr_url` 解析 `base` |

---

## 13. 未经真机验证的假设（出问题时先查这些）

本地没有 PHP 运行环境，也没有可用站点，**以下 8 条是照源码推出来的，上线首轮回归要重点覆盖**：

1. **`ymbds` 计数口径**：按网页面板同款"已绑定数 ≥ `ymbds+1` 才拦"。若客户反馈"还能多绑一个"，是这条口径而非插件 bug。
2. **`gzip` 状态键**：核心回 `status` 还是 `qk` 没实测过，插件两个键都试，都取不到就当 `"off"`。
3. **反代规则形状**：从 `Getnginx` 原文正则里抠 `location` / `proxy_pass`。宝塔改版加注释或换写法就解析不到（表现为 `site.proxy.get` 空列表）。
4. **静态缓存 id 的稳定性**：依赖 `get_static_cache` 返回顺序稳定。宝塔按字典序重排的话，改删会指向错条目（所以 §6.2 强调先取列表）。
5. **`ssl.get` 的"是否有证书"判定**：靠 `cert_data` 非空 + `status`。续签前置检查可能误判（有证书被判没有 → 拦住续签）。
6. **分片 `size` 语义**：按"宝塔用 `名字.<size>.upload.tmp` 判完成"实现。若节点版本用别的字段，表现是永远收不到 `done:true`。
7. **PDO 是否把整数列回成数字串**：取决于 `PDO::ATTR_EMULATE_PREPARES`。插件已在所有 Int 字段显式 `(int)`，两种配置都安全，但**别在 APP 侧放宽成"字符串数字也行"**。
8. **`db.list` 的 charset 来源**（`sqlkjhq` 分支的键名）取不到时回空串，仅影响显示。

---

## 14. 安全红线（插件已做到，APP 侧注意配合）

- 令牌 = `bin2hex(random_bytes(32))`，明文只在 `pair` 响应里出现一次；库里只存 `hash('sha256', token)`，**吊销即时生效、无缓存**。没用 `authcode()`/`SYS_KEY`（仓库公开，可伪造）。
- 配对码 120 秒 + 单次使用 + IP 维度限流；面板生成码另有用户维度限流。
- 口令与证书内容**永不进 `MN_log`，也永不在响应里回显**：FTP/SQL 改密、密码目录（回 `****`）、自定义证书（只回 `has_key`/`has_pem`）、部署表单值（只记任务 id）。
- 主机到期/封禁/删除/节点缺失全部是 JSON 错误码，**不会**返回 `sysmsg()` 的 HTML 提示页（APP 不发 cookie，核心那条 HTML 分支整体旁路）。
- 核心致命错误、WAF 拦截页、任何非 JSON 输出，一律收敛成 `code=5000` + `trace_id`，**不透传堆栈与 HTML**。
- 审计沿用面板 `MN_log`，操作者标记 `用户名(APP)`，做到"一个 APP 动作 = 一条面板日志"（核心已记日志的分支不重复记）。
- APP 侧：令牌按 Android Keystore 加密保存，不要写进日志/崩溃上报；`trace_id` 可以上报。
