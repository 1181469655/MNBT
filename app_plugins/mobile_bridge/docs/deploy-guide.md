# mobile_bridge 部署与运维指南（给站长/客户）

插件：`app_plugins/mobile_bridge`（移动桥接（APP），v1.0.0）
作用：给「MNBT 主机管理 APP」提供 `/app/v1` JSON 接口和设备配对页。
接口字段细节在 `api-contract.md`，本文只管**装起来、跑起来、出问题怎么查**。

---

## 1. 前置条件

| 项 | 要求 |
|---|---|
| MNBT 版本 | ≥ **1.88**（`plugin.json` 里 `requires_mnbt` 已声明） |
| 宝塔节点 | 现有主机面板功能可用即可（插件只是转发，不新增依赖） |
| 访问方式 | **必须 HTTPS**。明文 HTTP 上跑 Bearer 令牌等于把令牌 broadcast |
| PHP | 沿用站点现版本；见 §4 的三项 PHP 参数 |
| 其它插件 | 无必需依赖。装了「域名商品限制」（`domain_shop`）时，APP 添加域名会一并走它的限制 |

---

## 2. 安装

1. **上传整个插件目录**到站点 `app_plugins/mobile_bridge/`，目录结构必须完整：

   ```
   app_plugins/mobile_bridge/
   ├── plugin.json
   ├── bootstrap.php
   ├── install.sql / uninstall.sql
   ├── lib/            （11 个 php，缺一不可）
   ├── views/app_bind.php
   └── runtime/
       ├── .htaccess                 ← 隐藏文件，打包/上传最容易漏
       └── upload_state/.gitkeep     ← 分片上传状态目录，漏了首次上传会失败
   ```

   - **用 `git clone` 部署的**：`.gitkeep` 和 `.htaccess` 都已入库，正常拉取就有。
   - **用 zip 打包部署的**：确认打包命令带上隐藏文件（`zip -r` 默认带；手工在面板里逐个上传的会漏）。
2. 后台 → **插件管理** → 列表里出现「移动桥接（APP）」→ 点**安装** → 点**启用**。
   - 安装会自动执行 `install.sql`，**只需建两张表，都是插件自己的**：
     `MN_plugin_app_device`（设备令牌，存 sha256 哈希）、`MN_plugin_app_pair`（一次性配对码）。
   - **不需要手工跑任何 SQL、不需要改核心文件、不需要清缓存。**
   - 直接点「启用」也行：引擎发现没装会自动先装。
3. 启用后立刻可用，无需重启 PHP/FPM：
   - 用户端多一个菜单项 **「APP 配对」**（图标 `mdi-cellphone-link`，排序 78）。
   - 站点多一条路由 `POST /app/v1/{action}`。

---

## 3. URL 形态：要不要配 rewrite

**不配也能跑。** APP 默认用查询串形态：

```
POST https://你的域名/index.php?_r=/app/v1/db.list
```

这条路径由 MNBT 核心的通用路由承载（`index.php` → `mnbt_plugin_dispatch_route`），仓库**没有自带 .htaccess**，不要去网上抄 rewrite 规则硬套。

想用好看的 `/app/v1/db.list` 形态再配 rewrite，且必须**先把形态 A 验证通了再配**：

```nginx
# nginx：把非静态文件统一交给 index.php
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

```apache
# Apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{!f}
  RewriteCond %{!d}
  RewriteRule ^ /index.php [QSA,L]
</IfModule>
```

配完 APP 侧只是把 `{base}/index.php?_r=/app/v1/x` 换成 `{base}/app/v1/x`，其余不变。

---

## 4. 必须放行的两样东西（装完 90% 的问题都在这）

### 4.1 `Authorization` 请求头必须原样传到 PHP

插件只认 `Authorization: Bearer <token>`，**不认 cookie**。以下环境会把它剥掉，症状是「配对成功、之后所有操作都提示要重新扫码（1001）」：

- **nginx + PHP-FPM**：确认 fastcgi 参数里有这一行（宝塔默认站点配置一般已带，自建环境常漏）：

  ```nginx
  fastcgi_param HTTP_AUTHORIZATION $http_authorization;
  ```

- **Apache + mod_php / php-fpm**：VirtualHost 或 .htaccess 里加：

  ```apache
  SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
  ```

- **宝塔「网站防火墙」/ 安全组 / CDN（含 Cloudflare）**：把 `/app/v1/*` 加进白名单，确认规则里没有「过滤 Authorization 头」。
- 插件已内置 `REDIRECT_HTTP_AUTHORIZATION` 兜底（部分 Apache 内部跳转场景），但**不要指望它替代上面的配置**。

**一分钟自检**（在服务器上执行，不带令牌访问一个需要令牌的接口）：

```
curl -s -X POST "https://你的域名/index.php?_r=/app/v1/host.profile" -d "x=1"
```

- 回 `{"success":false,"code":1001,...}` → **通道正常**（1001 就是"没带令牌"，符合预期）。
- 回一整个 HTML 页面 / 404 / 空白 → 路由没接管：插件没启用，或 §3 的 URL 形态写错，或被 WAF 拦成拦截页。
- 回 `{"success":false,"code":1010,"msg":"未知接口：xxx"}` → 插件已启用且通道正常，只是 action 名拼错了（对照 `api-contract.md` 的表）。

### 4.2 两个目录要 Web 用户可写

```
app_plugins/mobile_bridge/runtime/upload_state/   ← 分片上传的字节偏移状态
runtime/temp/throttle/                            ← 限流计数桶（核心已有目录，会自动建）
```

```
chown -R www:www app_plugins/mobile_bridge/runtime && chmod -R 755 app_plugins/mobile_bridge/runtime
```

不可写的症状：**文件上传传不完（永远收不到 `done:true`）**、`pair` 接口限流失效（不会崩，只是保护没了）。
`runtime/` 目录里只存「偏移量 + 时间戳」，不含令牌与口令；插件已附 `.htaccess` 拒绝 Web 直读，**nginx 站点请加一条**：

```nginx
location ~* ^/(runtime|app_plugins/.*/runtime)/ { deny all; }
```

---

## 5. PHP 参数建议

| 参数 | 建议值 | 为什么 |
|---|---|---|
| `upload_max_filesize` / `post_max_size` | ≥ **8M**（分片 2MB，留余量） | 小了会在分片上传时直接 413/空响应 |
| `max_execution_time` | ≥ **300** | `deploy.run`（逐步 sleep）和 `ssl.apply`（ACME 校验）实测几十秒起；同时 APP 侧对应接口放宽了读超时 |
| `display_errors` | **Off** | 核心打开时 PHP 会把 Warning 打在 JSON 前面。插件有截取兜底（会从文本里抠出 `{...}`），但日志会被刷脏 |
| `memory_limit` | ≥ 128M | 大文件读取（`file.read`）会把内容整段读进内存做 base64 |

---

## 6. 客户端（用户）怎么用

1. 登录用户面板 → 菜单 **「APP 配对」** → 点「生成配对码」。
2. 页面出一个 **8 位配对码 + 二维码**，**120 秒内有效、只能用一次**。
3. APP 里「扫码配对」（或手输配对码 + 站点地址）。
4. 配对页同时列出这台主机已绑定的设备，可**逐个吊销**（吊销即时生效，那个设备立刻掉线）。
5. 面板生成配对码限流 **20 次 / 10 分钟 / 用户**；APP 侧扫码限流 **5 次 / 10 分钟 / IP**（只计失败次数）。

安全说明（可以放心转告用户）：

- 配对成功后手机拿到的是**这台主机自己的档案**：站点地址、站点名、面板版本、主机账号名、网站名（域名）、到期时间、域名绑定上限、网页/数据库空间与流量额度、所属宝塔节点的**代号**（不是 IP/端口/密钥）。**不含**任何口令：面板密码、数据库账号密码、FTP 密码、宝塔接口密钥一概不下发。
- 令牌是 64 位随机十六进制，**只在配对成功那一次响应里出现**，数据库里只存 sha256，管理员也拿不回明文。
- **FTP / 数据库口令在任何接口响应和操作日志里都不出现**：密码目录固定显示 `****`，改密动作只记「做了什么」不记「填了什么」。
- 所有 APP 操作在面板「操作日志」里都有一条对应记录，操作者标记为 **`用户名(APP)`**，跟网页操作区分得开。

---

## 7. 故障速查

| 现象 | 最可能的原因 | 处理 |
|---|---|---|
| 所有接口 1001「请重新扫码配对」 | `Authorization` 头被剥掉 | §4.1 |
| 返回 HTML 而不是 JSON | 通用路由没接管（rewrite/`_r` 写错），或安全软件把请求拦成拦截页 | §3、§4.1 |
| 返回 `code:1010 未知接口：xxx` | 路由已通，但 action 名拼错；或 APP 用了比站点插件更新的能力（`meta.bridge_version` 会带上当前版本） | 对照 `api-contract.md` 的 action 表；需要时升级站点插件 |
| 整页 HTML/404，连 1001 都拿不到 | 插件未启用、URL 形态不对、或安全软件把请求整体拦掉 | §2 步骤 2 确认启用、§3、§4.1 |
| 返回 `code:1006` | 主机绑定的宝塔节点在 `MN_bt` 里查不到 | 后台检查该主机的节点配置 |
| 返回 `code:5000` + `trace_id` | 核心 handler 抛致命错误（少见：PHP 版本过低、缺扩展、目录不可读） | 让客户报 `trace_id`；在 PHP 错误日志里 `grep '\[mobile_bridge\]'`，同一条 trace 能定位到 action 与原因 |
| 上传永远不到完成、状态文件不生成 | `runtime/upload_state` 不可写 | §4.2 |
| 上传直接 1007「缺少 size」 | **APP 没发 `size` 字段**（这是 APP 侧待改项，见 `api-contract.md` §12-18） | 升级到修了此项的 APP |
| 大 SQL 导入报 413 | WAF/`post_max_size` 卡住整包 | 放宽 §5，或改用「先 `file.upload` 传进去、再传路径」的导法（`api-contract.md` §8） |
| 配对码总提示已过期 | 服务器时间不准（配对码按服务器时间算 TTL） | 校准 NTP |
| 扫码后报「配对码不存在或已失效」，但手输 8 位码能成 | 二维码里的站点地址不对（子目录部署、或域名/HTTPS 走的不是同一个 Host） | 看配对页码下方「APP 将连接的地址」是否就是用户访问面板的地址；不对就按 §3 检查部署根目录与 `DOCUMENT_ROOT` |
| 点「生成配对码」提示「生成过于频繁」 | 命中面板侧 20 次/10 分钟限流 | 等窗口过去；正常用户点不到这个量 |
| APP 显示的空间/流量是 0 或不涨 | 核心本来就不实时写这几个字段 | `host.overview` 每次都会先刷一遍用量再回；APP 首页要调 `host.overview` 而不是只用 `pair` 时拿到的快照 |
| 想全量下线所有 APP | — | 后台停用插件即可（路由不再注册，接口回 404/HTML）；要清数据就卸载，见 §8 |

---

## 8. 卸载

后台 → 插件管理 → **卸载**：会执行 `uninstall.sql`，**DROP 掉 `MN_plugin_app_device` 和 `MN_plugin_app_pair` 两张表**，并清掉插件配置行。

- 后果：**所有已配对设备令牌立即失效**，用户必须重新配对。核心数据（主机、站点、备份、日志）一律不动。
- 代码目录 `app_plugins/mobile_bridge/` 手工删除即可；`runtime/` 里的状态文件随之删除。
- 想重装：直接装，表会重建，历史配对不恢复。

---

## 9. 升级

插件的 `MOBILE_BRIDGE_VERSION`（`lib/envelope.php`）= `1.0.0`，接口响应 `meta.bridge_version` 会带这个值，APP 可据此做能力协商：

- 以后新增/变更接口时把 `1.0.0` 往上升，并在 `mnbt_app_required_bridge_version()` 里给对应 action 标注最低版本；旧插件收到新请求会回 `1010 + 中文说明`，APP 侧是 Snackbar 提示而不是整页坏掉。
- 覆盖升级只换插件目录文件，**不涉及数据库变更**（除非另行说明）。
