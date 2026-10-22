---
title: 安全说明
description: PHP 运行环境安全基线（php2shell）、同批次 CVE 影响面、出站 TLS 校验与部署安全建议
---

# 安全说明

## PHP 运行环境安全基线

面板自身的运行环境建议使用已修复 php2shell 的 PHP 构建（这是安全建议，安装向导只做警示与确认，不替你决定）：

| PHP 分支 | 最低已修补版本 | 说明 |
|---------|--------------|------|
| 8.2 | 8.2.35 | 该分支 ≥ 8.2.35 视为已修复 |
| 8.3 | 8.3.36 | 同上 |
| 8.4 | 8.4.27 | 推荐版本（安装向导提示 8.4.27+） |
| 8.5 | 8.5.12 | 同上；高于表内分支的新版本一并放行 |
| 7.4 / 8.0 / 8.1 | 无 | 分支已停止上游维护，官方不会发布该修复；安装向导会标成警示态，需要你显式勾选确认才能继续 |

- 漏洞本体：CVE-2026-109618 / CVE-2026-109619（php2shell，CVSS 9.2），未认证攻击者构造 multipart POST 请求即可远程执行代码，触发点是 PHP 的会话上传进度通道。
- **面板不会以 PHP 版本为由拒绝安装。** 用不用旧版本是运维自己的决定（内网、有前置 WAF、暂时排期不上都可能是合理选择），面板只负责把风险讲清楚并把选择记录下来。
- 具体机制：`MPHX/BL.php` 的 `mnbt_php_security_status()` 是唯一判定来源（安装向导与后台仪表盘共用），`install/install.api.php` 的 `mnbt_php_security_gate()` 只做包装。版本不合格时安装向导把 PHP 检查项标成警示态（不是失败态），并显示一张确认卡「我已知悉当前 PHP 未修复 php2shell，风险自负，继续安装」；勾选后请求才会带上 `php_risk_ack=1`，接口在**未收到该确认**时返回的是「请先勾选确认」而不是拒绝。未勾选时无法提交，勾选后即可继续，服务端不会按版本号硬性拦截。
- 判定口径：按 `PHP_MAJOR_VERSION . PHP_MINOR_VERSION` 取分支，再用 `version_compare` 比对表内最低补丁版本；分支不在表内且低于 8.5.0 记为警示（`warning`）或更高危提示（`eol`），两者都只是提示，行为一致。
- 「代码兼容 PHP 7.4 ~ 8.4」只描述源码语法/移植性，不等于 7.4 / 8.0 / 8.1 可以享受官方补丁；`composer.json` 保留 `"php": ">=7.4"` 即为此目的。

## 暂时无法升级 PHP 时的缓解

只能作为过渡措施，不能替代升级：

1. **面板自身** `php.ini`：设置 `session.upload_progress.enabled = 0`，保存后 reload 面板所在版本的 PHP-FPM。`MPHX/common.php` 在请求初始化时另有 `@ini_set('session.upload_progress.enabled', '0')` 兜底，但不经过入口脚本的请求不受其覆盖，以 php.ini 为准。
2. **租户站点（重要前提）**：`bash.conf.php` 的 `$fpm_conf` 池模板已追加 `php_admin_value[session.upload_progress.enabled] = 0`（同批加入 `pm.max_requests = 500`，限制残留请求状态跨请求累积）。用 `php_admin_value` 是为了让租户无法通过 `.user.ini` 或 `ini_set` 重新打开。但 ⚠️ **该模板在当前版本没有下发通道**：`bash.conf.php` 全仓库仅被 `MPHX/bt_api.php` 的 `GetAllFile()` 引用且只用其中的 `$shell_get_file`，`$fpm_conf` / `$nginx_conf` / `$shell_phpset_bash` 均无消费方；`bash.conf.php` 文末注释提到的「后台 → 宝塔列表 → 修复配置」按钮也已随 V1.87 移除 classic 后台一起消失。租户池实际是宝塔节点在 `AddSite` / `SetPHPVersion` 时按节点默认参数生成的，所以这条改动目前只是"模板已备好"，**不会自动落到任何一个站点**。
3. **让缓解真正生效（在每个宝塔节点上执行，不需要重启面板或节点上的宝塔服务）**：

   ```bash
   for f in /www/server/mnbt_php_conf/*/fpm.conf /www/server/php/*/etc/php-fpm.conf; do
       [ -f "$f" ] && grep -q upload_progress "$f" || \
       { [ -f "$f" ] && echo 'php_admin_value[session.upload_progress.enabled] = 0' >> "$f"; }
   done
   # 然后按版本逐个 reload，版本号为节点上已安装的 PHP 主版本（如 82、83）
   /etc/init.d/php-fpm-82 reload
   ```

   若节点用的是宝塔共享池（`/tmp/php-cgi-{版本}.sock`），把该指令加进对应的 `/www/server/php/{版本}/etc/php-fpm.conf` 的 `[www]` 段即可，之后新建站点自动继承。
   验证方式：建一个探针页输出 `ini_get('session.upload_progress.enabled')`，应返回 `0`（或空/关）。
4. 在入口前置 Nginx/防火墙拦截异常 multipart 请求，并尽快排期升级到上表版本。

## 同批次其他 CVE 与本产品的关系

| CVE | 触及点 | 对本产品的意义 |
|-----|-------|--------------|
| CVE-2026-109618 / CVE-2026-109619 | 会话上传进度（multipart POST） | php2shell 未授权 RCE，本基线的由来 |
| CVE-2026-91769 / CVE-2026-91767 | OpenSSL 对端名称（SAN/CN）校验 | 影响所有 HTTPS 出站：宝塔节点 API、Docker/反向代理模块、支付回调 |
| CVE-2026-91766 | HTTP 流包装器跨源重定向携带凭证 | 面板请求节点时会带上按节点分存的 cookie jar（`runtime/bt_cookie/`，内含面板会话凭据），已在 `MPHX/bt_api.php` 用 `CURLOPT_PROTOCOLS` / `CURLOPT_REDIR_PROTOCOLS` 限定只跟随 http/https 跳转 |
| CVE-2026-93682 | 同一批 PHP 修复项 | 随升级到上表版本一并处理 |
| CVE-2026-6103 | phar 归档处理 | 本仓库源码只用 `ZipArchive`（`MPHX/function.php`、`admin/api/cx.php` 等），无 `Phar` / `PharData` 调用；风险主要在节点侧与第三方依赖 |
| CVE-2026-6735 | PHP-FPM status 页 XSS | `$fpm_conf` 未设置 `pm.status_path`，默认不暴露 status 页；节点自行开启过 status 的需升级 |
| CVE-2026-14355 | `openssl_encrypt` 的 AES-WRAP-PAD | 本面板加密链路用的是 `aes-256-gcm`（`MPHX/secret.php`、`app_plugins/zjmfmanager_reserve`）与 `aes-256-cbc`/`aes-128-cbc`（`app_plugins/realname`、`app_plugins/qmzl_domain`），未使用 AES-WRAP-PAD；随版本一并收口 |
| CVE-2026-12184 | https + 代理场景下 `file_get_contents` 凭证泄漏 | 面板出站请求走 cURL 且代码内不配置代理；若在 `php.ini` 或环境中为 HTTPS 配了代理，仍需升级 |
| CVE-2026-17543 / CVE-2025-14179 | pgsql 扩展 | **本产品不使用 pgsql**（全仓库无 `pg_*` / `pgsql_` 调用，数据库为 MySQL/MariaDB + SQLite PDO），不构成本地风险 |

## 出站 TLS 校验

面板对外的 HTTPS 请求按**对端是不是我们自己的机器**分两档（`MPHX/function.php` 的 `mnbt_curl_ssl_verify($ch, $scope, $force)` 是唯一入口；校验开启时 `CURLOPT_SSL_VERIFYHOST` 固定传 2——只有值为 2 时 libcurl 才校验证书主机名/SAN，1 已废弃、0/false 等于放弃身份校验）：

| 档位 | 调用点 | 缺省行为 | 可调开关 |
|-----|-------|---------|---------|
| `node`（自管机器） | 宝塔节点 API `MPHX/bt_api.php` 的 `HttpPostCookie()`、Docker 节点 `MPHX/bt_docker.php`、反向代理节点 `MPHX/bt_proxy.php`、魔方财务 server module `mf_modules/servers/mnbthost`、`mnbtdocker`、预约网关 `app_plugins/zjmfmanager_reserve/lib/CubeFinanceClient.php` | **不校验证书**（宝塔面板与自架魔方财务默认就是自签证书，一刀切强校验会让存量客户的节点对接当场失败） | `panel/ssl_strict=1` 收紧 |
| `public`（公网服务） | 通用取回 `MPHX/function.php` 的 `curl_get()`、插件与更新下载 `MPHX/plugin.php`、`MPHX/github_updater.php`、监控探活 `MPHX/monitor.function.php`、聚名网域名 API `app_plugins/qmzl_domain/lib/api.php`、支付宝官方插件 | **强校验**（这些站点有公网 CA 签发的证书，校验零成本） | `panel/ssl_insecure=1` 放宽 |

- 两个开关都存既有键值表 `MN_plugin_option`（`plugin_slug=panel`，`k=ssl_insecure` / `ssl_strict`，值 `1` 或 `true` 生效），没有新增表也没有新增列。**后台已有开关**：系统设置 → 控制面板页面的「自管节点强制证书校验」「全局关闭证书校验(紧急退路)」两行（读取用 `gn=getpaneltls`，随该页保存走 `gn=setkzmb` 的 `ssl_strict` / `ssl_insecure` 字段）。改完当次请求不生效——两个判定各自在进程内缓存，下一次请求才读到新值。需要直接操作数据库时：

  ```sql
  -- 节点已换成可信证书后收紧全部自管链路
  INSERT INTO MN_plugin_option (plugin_slug, k, v) VALUES ('panel', 'ssl_strict', '1')
    ON DUPLICATE KEY UPDATE v='1';
  -- 应急：连公网服务也不校验（会让更新源、支付回调等一并失去证书保护，事后请删除）
  INSERT INTO MN_plugin_option (plugin_slug, k, v) VALUES ('panel', 'ssl_insecure', '1')
    ON DUPLICATE KEY UPDATE v='1';
  DELETE FROM MN_plugin_option WHERE plugin_slug='panel' AND k IN ('ssl_strict','ssl_insecure');
  ```

- 优先级：`ssl_insecure`（放宽全部）> `ssl_strict`（收紧 node 档）> 各档缺省。调用点还可以传 `$force=true` 表达"这条一定要校验"，此时连 `ssl_insecure` 也压不住它——魔方财务那两个 server module 就是这样处理入口文件里的 `define('MNBT_SSL_VERIFY', true)` 的。
- 支付网关不受任何开关影响：`AlipayService` 在 `curl_setopt_array` 之后硬写 `VERIFYPEER=true` / `VERIFYHOST=2`，调用方传选项也关不掉。
- `node` 档默认宽容的代价要说明白：这条链路上带着节点凭据（`ktmy/qmk` 派生的鉴权与按节点分存的 cookie jar），不校验证书意味着中间的 HTTPS 请求仍可被出示任意证书的中间人篡改。想把它收紧又暂时拿不到公网证书，做法是自建一个 CA 签节点证书，把该 CA 追加进系统信任或在面板 `php.ini` 设 `curl.cainfo`（及 `openssl.cafile`）指向那个 bundle，然后把 `ssl_strict` 打开；宝塔节点能申请 Let's Encrypt 的（必须用域名，不能用 IP）直接换证书更省事。
- 部分精简 PHP 环境没有系统 CA bundle，`public` 档会连正规证书都报 `unable to get local issuer certificate`；这不是策略过严，装 `ca-certificates` 并配好 `curl.cainfo` 即可。

## 加密密钥丢失的识别与恢复

`MPHX/secret.php` 的加密密钥 `$mnbt_secret_key` 只存在根目录 `config.php` 里，密钥一丢，库里全部 `enc1:` 密文就永久解不开。**这次故障的成因是 `config.php` 被在线覆盖更新冲掉**：更新流程原本走"先备份 → 解压覆盖站点根 → 完成后还原"，一旦在中途被 FPM 硬杀、502、磁盘满打断，站点上留下的就是**打包人自己的 config.php**——数据库配置错还能改回来，`$mnbt_secret_key` 换成别人的等于把所有密文判死刑且现场不可恢复。这一版把这条路径改成不依赖"事后还原"：

- **`config.php` 只由安装生成**：它本就 gitignored，GitHub/Gitee 自动源码包里根本没有；在线更新的解压阶段现在直接**跳过**本地文件条目（`MPHX/github_updater.php` 的 `mnbt_updater_local_keep()`：`config.php`、`cf_up.php`、`runtime/bt_cookie/`、`templates/active_*_theme`），包里带着也不写盘，备份还原只作为额外保险保留。样例见新增入库的 `config.bak.php`（程序不读它，只给手工部署的人抄）。
- **安装向导不再覆盖已有 `config.php`**：`database_info_wire` 检测到根目录已有 `config.php` 就拒绝写入并说明后果——`install.lock` 丢了但站点还活着时重走向导，以前会静默换掉密钥。要全新重装请先在服务器上删除或改名该文件。
- **解密只读现有密钥，任何情况下都不再生成、不再写 `config.php`**。以前解密路径也会触发"缺密钥就自动生成一把新的追加写回"，等于把旧密文判死刑还不留现场——这条路径已经删掉。
- **加密（写入）路径缺密钥时直接报错**：`mnbt_secret_key()` 先扫库里是否已有 `enc1:` 值，有则抛 `MNBT_SecretException`（提示恢复原 `config.php` 或重录凭据），**不会**生成新密钥；库里一条密文都没有才按老行为自动生成。`MPHX/common.php` 装了未捕获异常兜底，这类错误会以一句话原文回给前端并写日志 `runtime/logs/php-error.log`，其他异常只回"服务器内部错误"，细节不外泄。
- **列表与表单显式区分"没设置"和"解不开"**：`gn=listbt`、`gn=docker_node_list` 多返回一个 `sec_unreadable`（1=库里是解不开的密文），密钥/调用密钥一律留空，`dymy` 也不再拿 `md5('')` 冒充一个看起来正常的调用密钥；宝塔列表与 Docker 节点页显示"密钥不可用"标记。
- **"打开编辑页再保存"不会再毁掉密文**：`gn=xgjl`（宝塔）、`gn=docker_node_edit`、邮件设置、以及主机修改都改成先读库里的原始密文再决定写什么——提交为空且原值解不开时原样保留旧密文（`mnbt_secret_keep_unreadable()`）。主机修改这一条更严格：`pass`/`sqlpass` 会同时被推到宝塔节点，解不开时直接拒绝保存并说明原因，而不是把空密码写进两边。
- **通信检测先说清是谁的问题**：`gn=btztjc` 与 `gn=docker_node_config` 在调节点前先判断密钥可解与否，解不开就直接提示"config.php 缺少/换掉了加密密钥"，不再让人去查宝塔 API 白名单。

识别方法（三选一即可判定）：

```bash
grep -c mnbt_secret_key config.php              # 0 = 本机已经没有这把密钥
ls -l config.php                                # 近期被覆盖/重建过就是它
```

```sql
SELECT id, btdh, SUBSTRING(btmy,1,6) AS head FROM MN_bt;   -- head=enc1 说明库里是密文
```

恢复顺序：**优先从备份拿回原 `config.php`（或只把原来那行 `$mnbt_secret_key='64位hex';` 补回当前 config.php）**，密钥找回后所有密文自动重新可读，无需迁移。确实找不回来时，只能逐个重录：宝塔/Docker 节点密钥（宝塔面板 → 面板设置 → API接口 里能看到接口密钥；`ktmy/qmk` 由宝塔侧重新生成或手工填一组并同步到魔方财务），主机密码（重设后走面板的改密流程），邮箱授权码，DNS 服务商密钥，资源池密码。重录前别跑 `admin/migrate_secrets.php`，也别在后台保存任何节点/主机表单。

## 客户/运维升级动作清单

1. 在宝塔节点装好已修复版本的 PHP（8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 及以上），把面板站点切换到该版本；已安装状态下可用 `php -v` / `phpinfo()` 核对实际运行版本（安装向导的环境检测在存在 `install/install.lock` 时不再执行）。
2. 复核面板 `php.ini` 的 `session.upload_progress.enabled`，升级后建议仍保持 `0`。
3. 按上文「缓解」第 3 条的循环，在每个节点上给存量池与共享池追加 `php_admin_value[session.upload_progress.enabled] = 0`，再逐版本 reload php-fpm——面板当前不会自动下发该指令，新建站点也一样不带它。
4. 升级后在后台「宝塔列表 → 状态列 → 点我检测」逐节点复测通信。节点这一档默认不校验证书，自签节点不会报错；如果你在 `public` 档链路（更新下载、支付回调、监控探活）遇到 `unable to get local issuer certificate`，那是缺 CA bundle，装 `ca-certificates` 并配 `curl.cainfo` 解决，不要开 `ssl_insecure`。
5. 节点证书换成可信证书后，把 `panel/ssl_strict` 置 1 并再复测一轮；Docker 节点、魔方财务对接的 `mnbthost`/`mnbtdocker`、支付宝回调、`curl_get` 相关取回、监控计划任务与邮件发送一并回归。
6. 配置与凭据变更后重新登录管理后台；同版本还包含库内凭据密文迁移，执行 `admin/migrate_secrets.php` 后所有管理员需重新登录。
7. 仍停留在 7.4 / 8.0 / 8.1 的服务器：面板允许你在显式确认后继续安装/运行，但这些分支拿不到官方补丁，php2shell 只能靠下文 ini 缓解 + 前置拦截压住风险，请把它当成排期项而不是长期状态。

## 敏感文件清单

⚠️ **本系统仅限内部部署，请勿将以下文件/目录上传至公开仓库**：

- `config.php` - 包含数据库账号密码
- `install/install.lock` - 安装锁定文件
- `runtime/logs/*.log` - 错误日志，可能包含敏感信息
- `plugins/wwwlogs/*.log` - 访问日志
- `.env` - 环境变量（如使用）

## 推荐 .gitignore

```gitignore
# 安装锁定
install/install.lock

# 配置文件
config.php
.env

# 日志文件
*.log
runtime/logs/
plugins/wwwlogs/
runtime/cache/
runtime/temp/

# 临时文件
.DS_Store
Thumbs.db
.idea/
.vscode/
*.swp
*.swo

# 敏感信息
*.sql.bak
backup/
```

## 部署安全建议

1. ✅ 使用安装时设置的强密码，并定期更换管理员密码
2. ✅ 修改 API 密钥（系统设置 → API 接口）
3. ✅ 启用 HTTPS（SSL 证书配置）
4. ✅ 修改宝塔面板默认端口
5. ✅ 限制管理后台 IP 访问（Nginx/Apache 配置）
6. ✅ 面板运行环境保持在本文开头「PHP 运行环境安全基线」的已修补版本之上
7. ✅ 关闭 PHP 错误显示（`display_errors = Off`）
8. ✅ 定期备份数据库
9. ✅ 监控异常登录和操作日志
