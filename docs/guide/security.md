---
title: 安全说明
description: PHP 运行环境安全基线（php2shell）、同批次 CVE 影响面、出站 TLS 校验与部署安全建议
---

# 安全说明

## PHP 运行环境安全基线

面板自身的运行环境必须是已修复 php2shell 的 PHP 构建：

| PHP 分支 | 最低已修补版本 | 说明 |
|---------|--------------|------|
| 8.2 | 8.2.35 | 该分支 ≥ 8.2.35 视为已修复 |
| 8.3 | 8.3.36 | 同上 |
| 8.4 | 8.4.27 | 推荐版本（安装向导提示 8.4.27+） |
| 8.5 | 8.5.12 | 同上；高于表内分支的新版本一并放行 |
| 7.4 / 8.0 / 8.1 | 无 | 分支已停止上游维护，官方不会发布该修复，安装向导直接拒绝 |

- 漏洞本体：CVE-2026-109618 / CVE-2026-109619（php2shell，CVSS 9.2），未认证攻击者构造 multipart POST 请求即可远程执行代码，触发点是 PHP 的会话上传进度通道。
- 强制点：`install/install.api.php` 的 `mnbt_php_security_gate()`，在 `action=system`（环境检测）、`action=database_info_wire`、`action=install` 三处调用；版本不合格时接口返回拒绝原因并中止安装，安装向导页面把 PHP 检查项标红并给出提示。
- 判定口径：按 `PHP_MAJOR_VERSION . PHP_MINOR_VERSION` 取分支，再用 `version_compare` 比对表内最低补丁版本；分支不在表内且低于 8.5.0 一律拒绝。
- 「代码兼容 PHP 7.4 ~ 8.4」只描述源码语法/移植性，不等于 7.4 / 8.0 / 8.1 可以作为运行环境；`composer.json` 保留 `"php": ">=7.4"` 即为此目的。

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

V1.89 起，面板对外的 HTTPS 请求默认校验证书（`CURLOPT_SSL_VERIFYPEER = true`、`CURLOPT_SSL_VERIFYHOST = 2`；只有值为 2 时 libcurl 才校验主机名/SAN）：

| 调用点 | 位置 |
|-------|------|
| 宝塔节点 API | `MPHX/bt_api.php` 的 `HttpPostCookie()` |
| Docker 节点 | `MPHX/bt_docker.php` |
| 反向代理节点 | `MPHX/bt_proxy.php` |
| 通用取回 | `MPHX/function.php` 的 `curl_get()` |
| 魔方财务 server module | `mf_modules/servers/mnbthost`、`mnbtdocker`（独立部署、面板函数不可加载时兜底同样强校验） |
| 支付宝官方插件 | `app_plugins/alipay_official/lib/AlipayService.php` |

- 统一入口：`MPHX/function.php` 的 `mnbt_curl_ssl_verify()`，退路开关为面板级选项 `ssl_insecure`（存 `MN_plugin_option`，`plugin_slug=panel` / `k=ssl_insecure`，值 `1`/`true` 生效），由 `mnbt_ssl_insecure_enabled()` 读取；默认（无配置）一律校验。
- 支付网关不受该开关影响：`AlipayService` 在 `curl_setopt_array` 之后硬写校验，调用方传选项也关不掉。
- 保留退路的原因：宝塔节点默认使用自签证书，一刀切强校验会让存量客户的节点对接直接失败。**推荐做法是把节点证书/CA 加入系统信任，或在 `php.ini` 设置 `curl.cainfo`（及 `openssl.cafile`）指向 CA bundle，而不是打开 `ssl_insecure`**；确需打开时按节点逐个验证后再全局启用，事后关闭。

## 客户/运维升级动作清单

1. 在宝塔节点装好已修复版本的 PHP（8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 及以上），把面板站点切换到该版本；已安装状态下可用 `php -v` / `phpinfo()` 核对实际运行版本（安装向导的环境检测在存在 `install/install.lock` 时不再执行）。
2. 复核面板 `php.ini` 的 `session.upload_progress.enabled`，升级后建议仍保持 `0`。
3. 按上文「缓解」第 3 条的循环，在每个节点上给存量池与共享池追加 `php_admin_value[session.upload_progress.enabled] = 0`，再逐版本 reload php-fpm——面板当前不会自动下发该指令，新建站点也一样不带它。
4. 升级后在后台「宝塔列表 → 状态列 → 点我检测」逐节点复测通信；出现证书错误时按上一节修 CA 信任（`curl.cainfo`），不要直接开 `ssl_insecure`。
5. 复测其他出站链路：Docker 节点、魔方财务对接的 `mnbthost`/`mnbtdocker`、支付宝回调、`curl_get` 相关取回、监控计划任务与邮件发送。
6. 配置与凭据变更后重新登录管理后台；同版本还包含库内凭据密文迁移，执行 `admin/migrate_secrets.php` 后所有管理员需重新登录。
7. 仍停留在 7.4 / 8.0 / 8.1 的服务器：无法通过安装校验，也无法获得官方补丁，只能按上文做 ini 缓解 + 前置拦截，并尽快迁移到已修补分支。

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
