---
title: 用户控制面板接口
description: user/ajax.php 用户控制面板 AJAX 接口（站点配置、文件管理、域名、SSL、数据库、监控等）
---

# 用户控制面板接口（user/ajax.php）

**入口**：`POST /user/ajax.php`  
**鉴权**：除 `gn=login` 外，全部需要用户登录（`user_token` Cookie）。  
**路由文件**：user/ajax.php → user/api/*.php

> 插件 AJAX（`gn=p_xxx`）会优先由 `mnbt_plugin_dispatch_ajax('user', $egn)` 分发，详见 [插件对接 API](./plugin.md)。

## 3.1 登录与初始化

### `gn=login` —— 用户登录

| 项 | 值 |
|----|----|
| 文件 | user/api/login.php |
| 鉴权 | 公开 |
| 参数 | `user`、`pass`、`code`（验证码，可选） |
| 返回 | `{"code":"登陆成功"}`，并写入 `user_token` Cookie（7天有效期） |
| 退出 | 同 `gn=login` 传 `logout=1` |

---

## 3.2 站点配置（site）

文件：user/api/site.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `phpxg` | 切换 PHP 版本 | `php`（版本号） | `code` |
| `sqldr` | 设置运行目录 | `dr`（路径） | `code` |
| `xgmrwd` | 修改默认文档 | `mrwd`（逗号分隔） | `code` |
| `hqjt` | 获取伪静态规则 | 无 | `code`/规则列表 |
| `setwjt` | 设置伪静态规则 | `wjt`（模板名） | `code` |
| `tjmmfw` | 添加密码访问目录 | `ml`、`user`、`pass` | `code` |
| `scmmfw` | 删除密码访问目录 | `ml` | `code` |
| `ftpjy` | FTP 禁用/启用 | `qk` | `code` |
| `xgpass` | 修改 FTP/面板密码 | `pass` | `code` |
| `setyxml` | 设置运行目录 | `yxml` | `code` |
| `setgzip` / `gzip` | 设置 Gzip 配置 | Gzip 相关参数 | `code` |
| `sxsyxx` | 刷新所有用量信息 | 无 | 用量 JSON |

---

## 3.3 文件管理（file）

文件：user/api/file.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `listfile` | 获取目录文件列表 | `path` | `code`、文件数组 |
| `hqwj` | 获取文件内容 | `path`、`name` | `code`、内容 |
| `setwj` | 保存文件内容 | `path`、`name`、`content` | `code` |
| `xjwj` | 新建文件 | `ml`、`wjname` | `code` |
| `xjwjj` | 新建文件夹 | `ml`、`wjname` | `code` |
| `ftpsc` | 删除文件/目录 | `lx`（file/dir）、`path`、`name` | `code` |
| `ftpscxz` | 批量删除 | `path`、`idsz`（数组） | `code` |
| `setname` | 重命名 | `path`、`name`、`newname` | `code` |
| `filecp` | 复制文件 | `path`、`name`、`newpath` | `code` |
| `fileys` | 压缩文件 | `path`、`name`、`zipname` | `code` |
| `fileupload` | 上传文件（分片） | `path`、`name`、`file`、`fesw`、`zsize` | `error`、`size`、`msg` |
| `file_upload_size` | 获取上传限制 | 无 | `code`、`size` |
| `hqdx` | 获取目录大小 | `path` | `code`、`size` |

**安全限制：**

- 路径必须以 `/` 开头
- 文件名不能包含 `/`
- 禁止操作 `.user.ini`

---

## 3.4 域名管理（domain）

文件：user/api/domain.php

> 域名 CRUD 已迁移到 `domain_shop` 插件，本文件仅保留查询接口。

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `hqzmlls` | 获取子目录域名列表 | 无 | `["domain1","domain2"]` 或 `false` |
| `listurl` | 获取域名绑定列表 | 无 | `{"domains":[{"name":"..."}]}` |

---

## 3.5 SSL 证书（ssl）

文件：user/api/ssl.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `sqssl` | 申请/续签 Let's Encrypt | `list`（域名数组）、`type`（`false`=申请 / `true`=续签） | `{"qk":1\|4,"code":"..."}` |
| `setssl` | 设置自定义证书 | `key`、`pem` | `{"qk":1\|4,"code":"..."}` |
| `getssl` | 获取证书配置 | 无 | `{"key","csr","httpToHttps","status","cert_data","type"}` |
| `clossl` | 关闭 SSL | 无 | `{"qk":1\|4,"code":"..."}` |
| `httpsqz` | 强制 HTTPS 开关 | `qk`（`true`/`false`） | `{"qk":1\|4,"code":"..."}` |

---

## 3.6 数据库管理（database）

文件：user/api/database.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `databaseadd` | 创建数据库 | `name`、`pass` | `code` |
| `databasedel` | 删除数据库 | `name` | `code` |
| `databasedownload` | 下载备份 | `name` | 文件流 |
| `databaserestore` | 恢复备份 | `name` | `code` |
| `databaseaq1` | 设置数据库权限 | `name`、`aq`（权限） | `code` |
| `Delalldatabase` | 删除所有数据库 | 无 | `code` |

---

## 3.7 缓存配置（cache）

文件：user/api/cache.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `cache_add` | 添加缓存规则 | `suffix`、`time_out`、`unit` | `code` |
| `cache_edit` | 修改缓存规则 | `id`、`suffix`、`time_out`、`unit` | `code` |
| `cache_del` | 删除缓存规则 | `id` | `code` |
| `cache_list` | 缓存规则列表 | 无 | 规则数组 |

**`unit` 枚举：** `second`、`minute`、`hour`、`day`

---

## 3.8 一键部署（deploy）

文件：user/api/deploy.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `yjbs` | 执行部署 | `id`、`datas`（表单数据） | `code` |
| `yjbsform` | 获取部署表单 | `id` | `code`、表单配置 |

---

## 3.9 监控与通知（monitor / notice）

文件：user/api/monitor.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `monitor_save` | 新建/修改监控任务 | `id`（0=新建）、`name`、`task_type`、`url`、`resource_type`、`resource_threshold`、`method`、`interval_seconds`、`timeout_seconds`、`status_rule`、`status_value`、`content_rule`、`content_value`、`fail_threshold`、`notify_email`、`enabled` | `code` |
| `monitor_del` | 删除监控任务 | `id` | `code` |
| `monitor_toggle` | 启用/禁用任务 | `id`、`enabled` | `code` |
| `notice_read` | 标记通知已读 | `id`（0=全部） | `code` |

**`task_type` 枚举：** `url`、`resource`  
**`resource_type` 枚举：** `web`、`sql`、`traffic`  
**`status_rule` 枚举：** `eq`、`ne`、`lt`、`gt`、`ge`、`le`  
**`content_rule` 枚举：** `none`、`contains`、`not_contains`  
**`method` 枚举：** `GET`、`POST`、`HEAD`  
**限制：** 每用户最多 5 个任务；URL 监控默认 60s，资源监控固定 180s；URL 禁止指向内网（SSRF 防护）。

---

## 3.10 站点统计（site_stats）

文件：user/api/site_stats.php

统一入口 `gn=site_stats`，通过 `type` 参数区分：

| `type` | 功能 | 返回 |
|--------|------|------|
| `overview` | 概览统计 | PV/UV/请求数等 |
| `trend` | 流量趋势 | 时间序列数据 |
| `ip_rank` | IP 排行 | IP 列表 |
| `uri_rank` | URI 排行 | URI 列表 |
| `errors` | 错误统计 | 状态码分布 |
| `spider` | 蜘蛛访问 | 蜘蛛列表 |
| `client` | 客户端统计 | 客户端分布 |
| `method` | 请求方法统计 | 方法分布 |
| `recent` | 最近访问 | 访问列表 |

---

## 3.11 其他（other）

文件：user/api/other.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `fdlkg` | 防盗链开关 | `qk` | `code` |
| `getfdl` | 获取防盗链配置 | 无 | 配置 JSON |
| `mailbd` | 绑定邮箱 | `mail` | `code` |
| `fzjh` | 反向代理 | `qk`、`url` | `code` |
| `indexconf` | 索引配置 | `qk` | `code` |

---

## 3.12 SPA 列表接口（panel）

文件：user/api/panel.php

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `monitor_list` | 监控任务列表 | 无 | `{"qk":1,"code":"获取成功","msg":{"tasks":[],"task_count":0,"max":5}}` |
| `monitor_log_list` | 监控检测日志 | `id`、`page`、`page_size` | `{"qk":1,"msg":{"logs":[],"total":0,"page":1,"page_size":20,"total_pages":1,"task_id":0}}` |
| `notice_list` | 通知日志列表 | `type`、`level`、`read`、`keyword`、`page`、`page_size` | `{"qk":1,"msg":{"logs":[],"total":0,"page":1,"page_size":15,"total_pages":1}}` |
| `backup_list` | 数据库备份列表 | 无 | `{"qk":1,"msg":{"list":[],"count":0,"db_id":"","user":""}}` |
| `deploy_list` | 一键部署程序列表 | 无 | `{"qk":1,"msg":{"list":[],"web":[],"sql":[]}}` |
| `pass_list` | 密码访问目录列表 | 无 | `{"qk":1,"msg":{"list":[]}}` |
| `set_init` | 设置页初始化数据 | `section` | `{"qk":1,"msg":{"section":"...","..."}}` |

**`section` 枚举：** `php`、`mrwd`、`yxml`、`wjt`、`gzip`、`cache`、`xgpass`、`mysqlcz`、`url`、`pass`

**`notice_list` 过滤参数：**

- `type`：通知类型（如 `expire`、`traffic`、`monitor`）
- `level`：级别（如 `info`、`warning`、`critical`）
- `read`：`true` / `false`
- `keyword`：标题/内容模糊搜索

**`page_size` 允许值：** `monitor_log_list` → 10/15/20/25/50/100；`notice_list` → 10/15/25/50/100。

---

**相关文档：**

- [API 通用约定](./overview.md)
- [后台管理接口](./admin.md)
- [插件对接 API](./plugin.md)
