# MNBT API 文档

> **版本**：V1.82  
> **更新日期**：2026-07-26  
> **适用范围**：梦奈宝塔主机系统（MNBT）前端、移动端、第三方对接、插件开发

本文件汇总 MNBT 全部对外接口，包括：

- **后台管理 AJAX 接口**（`admin/ajax.php` → `admin/api/*.php`）
- **用户控制面板 AJAX 接口**（`user/ajax.php` → `user/api/*.php`）
- **外部对接 REST API**（`api/api.php`、`api/node.php`）
- **插件对接 API**（`MPHX/plugin.php` 钩子 / AJAX / 路由 / 支付 / 页面）
- **核心工具函数**（`MPHX/function.php`、`Response.php`、`member.php`）

---

## 目录

- [1. 通用约定](#1-通用约定)
  - [1.1 请求约定](#11-请求约定)
  - [1.2 统一响应格式](#12-统一响应格式)
  - [1.3 错误码](#13-错误码)
  - [1.4 鉴权机制](#14-鉴权机制)
- [2. 后台管理接口（admin/ajax.php）](#2-后台管理接口adminajaxphp)
  - [2.1 登录与系统信息](#21-登录与系统信息)
  - [2.2 宝塔节点管理（bt）](#22-宝塔节点管理bt)
  - [2.3 主机管理（zj）](#23-主机管理zj)
  - [2.4 一键部署程序（cx）](#24-一键部署程序cx)
  - [2.5 订单管理（dd）](#25-订单管理dd)
  - [2.6 系统公告与更新（gg）](#26-系统公告与更新gg)
  - [2.7 操作日志（log）](#27-操作日志log)
  - [2.8 MNBT 节点管理（node）](#28-mnbt-节点管理node)
  - [2.9 系统设置（setting）](#29-系统设置setting)
  - [2.10 系统修复（repair）](#210-系统修复repair)
  - [2.11 插件管理（plugin）](#211-插件管理plugin)
- [3. 用户控制面板接口（user/ajax.php）](#3-用户控制面板接口userajaxphp)
  - [3.1 登录与初始化](#31-登录与初始化)
  - [3.2 站点配置（site）](#32-站点配置site)
  - [3.3 文件管理（file）](#33-文件管理file)
  - [3.4 域名管理（domain）](#34-域名管理domain)
  - [3.5 SSL 证书（ssl）](#35-ssl-证书ssl)
  - [3.6 数据库管理（database）](#36-数据库管理database)
  - [3.7 缓存配置（cache）](#37-缓存配置cache)
  - [3.8 一键部署（deploy）](#38-一键部署deploy)
  - [3.9 监控与通知（monitor / notice）](#39-监控与通知monitor--notice)
  - [3.10 站点统计（site_stats）](#310-站点统计site_stats)
  - [3.11 其他（other）](#311-其他other)
  - [3.12 SPA 列表接口（panel）](#312-spa-列表接口panel)
- [4. 外部对接 API](#4-外部对接-api)
  - [4.1 主机生命周期 API（api/api.php）](#41-主机生命周期-apiapiapiphp)
  - [4.2 MNBT 节点 API（api/node.php）](#42-mnbt-节点-apiapinodephp)
- [5. 插件对接 API](#5-插件对接-api)
  - [5.1 插件 AJAX 调用规则](#51-插件-ajax-调用规则)
  - [5.2 插件页面 URL 规则](#52-插件页面-url-规则)
  - [5.3 插件注册函数](#53-插件注册函数)
  - [5.4 钩子（Hooks）一览](#54-钩子hooks一览)
  - [5.5 支付插件 API](#55-支付插件-api)
  - [5.6 自动鉴权机制](#56-自动鉴权机制)
- [6. 核心工具函数](#6-核心工具函数)
  - [6.1 响应函数](#61-响应函数)
  - [6.2 业务函数](#62-业务函数)
- [7. 数据库表速查](#7-数据库表速查)

---

## 1. 通用约定

### 1.1 请求约定

| 项目 | 约定 |
|------|------|
| 编码 | UTF-8（`Content-Type: text/html; charset=UTF-8` 或 `application/json; charset=UTF-8`） |
| 请求方法 | 默认 `POST`（外部 API 同时需要 `?gn=xxx` 查询参数） |
| 参数类型 | `application/x-www-form-urlencoded`（文件上传除外） |
| 必带参数 | `gn` —— 操作动作名（action） |
| Cookie | 已登录用户/管理员需带上对应的 `user_token` / `admin_token` |
| 路径前缀 | 后台：`/admin/ajax.php`；用户端：`/user/ajax.php`；外部：`/api/api.php` |

**请求示例：**

```http
POST /admin/ajax.php HTTP/1.1
Host: your-domain.com
Cookie: admin_token=xxxxxx
Content-Type: application/x-www-form-urlencoded

gn=listzj&page=1&limit=20&sortOrder=desc&sort=id
```

### 1.2 统一响应格式

系统使用 `Response` 类（`MPHX/Response.php`）和 `mnbt_json_encode()` 函数统一输出 JSON。

**结构：**

```json
{
  "success": true,
  "code": "返回信息或状态码",
  "msg": "返回信息",
  "redirect": null,
  "data": {}
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| `success` | bool | 业务是否成功 |
| `code` | string/int | 状态码或文本消息（`'0'`、`'4'`、`'100'`、`'300'` 视为失败） |
| `msg` | string | 文本消息（与 `code` 同义） |
| `redirect` | string/null | 跳转地址（可选） |
| `data` | object/array | 业务数据（可选） |

> **历史兼容**：部分老接口直接返回 `{"code": "文本消息"}` 或 `{"qk": 1|4, "code": "..."}`，以接口实际说明为准。

### 1.3 错误码

| code 值 | 含义 | success |
|---------|------|---------|
| `1` / `200` / 文本 | 成功 | `true` |
| `0` / `4` / `100` / `300` | 失败 | `false` |
| `"请登陆"` / `"请登陆后台"` | 未登录 | `false` |
| `"请求错误！"` | 未知 `gn` | `false` |

**外部 API 错误码：**

| code | 含义 |
|------|------|
| 200 | 成功 |
| 100 | 参数错误 / 鉴权失败 / 业务失败 |
| 300 | 插件版本不兼容 |

### 1.4 鉴权机制

#### Cookie Token 鉴权（管理端 / 用户端）

| 端 | Cookie 名 | 加密方式 | 校验流程 |
|----|-----------|----------|----------|
| 管理后台 | `admin_token` | `authcode()` + `SYS_KEY` | 解密出 `user\tsid`，`sid = md5(conf.user.conf.pwd.password_hash)` |
| 用户面板 | `user_token` | `authcode()` + `SYS_KEY` | 解密后查 `MN_zj` 表，校验 `md5(user.pass.password_hash)` |
| 插件独立 | `account_token` | `authcode()` + 独立盐 | 插件自维护 `MN_plugin_user` 表（参见 user_info 插件） |

> `authcode()` 是双向加解密函数（RC4 风格 + base64），定义在 `MPHX/function.php`。  
> `password_hash` 为全局混淆盐，在 `MPHX/common.php` 中初始化。

**鉴权失败响应：**

- 管理后台：`{"code":"请登陆"}`
- 用户面板：`{"code":"请登陆"}`
- 控制面板关闭：`{"code":"宝塔服务器配置错误，请联系管理员"}`

#### 外部 API 双重密钥鉴权

外部 API（`api/api.php`）需要同时验证：

1. **系统密钥**：`mn_key` == `MN_config.api`（后台「系统设置 → API 密钥」）
2. **宝塔调用密钥**：`mn_keye` == `md5(MN_bt.ktmy . MN_bt.qmk)`（每个宝塔节点独立）

#### MNBT 节点 API 鉴权

- 使用 `node_id` + `node_secret` 组合
- 通过 `mnbt_node_authenticate()` 校验请求体与节点信息
- 支持 nonce 防重放攻击（`MN_node_nonce` 表）

---

## 2. 后台管理接口（admin/ajax.php）

**入口**：`POST /admin/ajax.php`  
**鉴权**：除 `gn=login` 外，全部需要管理员登录（`admin_token` Cookie）。  
**路由文件**：[admin/ajax.php](file:///d:/documents/GitHub/MNBT/admin/ajax.php) → `admin/api/*.php`

### 2.1 登录与系统信息

#### `gn=login` —— 管理员登录

| 项 | 值 |
|----|----|
| 文件 | [admin/api/login.php](file:///d:/documents/GitHub/MNBT/admin/api/login.php) |
| 鉴权 | 公开 |
| 参数 | `user`、`pass`、`code`（验证码，可选） |
| 返回 | `{"code":"登陆成功"}`，并写入 `admin_token` Cookie |

#### `gn=system_info` —— 系统信息

| 项 | 值 |
|----|----|
| 文件 | [admin/api/gg.php](file:///d:/documents/GitHub/MNBT/admin/api/gg.php) |
| 参数 | 无 |
| 返回字段 | `hosts`、`bt_panels`、`nodes`、`orders`、`os`、`hostname`、`php_version`、`php_sapi`、`server_soft`、`server_ip`、`server_port`、`server_time`、`timezone`、`memory_limit`、`max_exec_time`、`upload_max`、`post_max`、`ext_count`、`disk_total`、`disk_free`、`disk_used`、`disk_pct`、`mem_current`、`mem_peak`、`load_avg`、`db_version`、`web_version`、`sql_version` |

#### `gn=gglist` —— 公告列表

返回系统公告 JSON 数组。

#### `gn=update` —— 在线系统更新

| 项 | 值 |
|----|----|
| 参数 | 无 |
| 返回 | `{"code":"...", "msg":"..."}` |

---

### 2.2 宝塔节点管理（bt）

文件：[admin/api/bt.php](file:///d:/documents/GitHub/MNBT/admin/api/bt.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listbt` | 宝塔列表（分页） | `page`、`limit`、`sort`、`sortOrder` | `total`、`rows[]` |
| `addbt` | 添加宝塔节点 | `ip`、`dk`、`key`、`bh`、`btos`、`urlla`、`ftpdz`、`xieyi`、`kg` | `code`、`msg` |
| `btsj` | 获取宝塔详情 | `id` | `code`、`ip`、`dk`、`my`、`kg`、`btos` |
| `xgjl` | 修改宝塔信息 | `id`、`ip`、`dk`、`key`、`kg`、`btos`、`urlla`、`ftpdz`、`xieyi` | `code`、`msg` |
| `btsc` | 删除宝塔 | `id` | `code`、`msg` |
| `btztjc` | 检测连接状态 | `btid` | `qk`、`code`、`titco` |
| `mnbt` | 获取系统版本 | 无 | `cl`、`gx`、`vs`、`gg` |
| `list_node_php` | 获取节点 PHP 版本列表 | `btdh` | `qk`、`versions`、`latest`、`current_default` |
| `auto_detect_node_php` | 自动检测节点 PHP | `btdh` | `qk`、`version`、`msg` |
| `set_node_php` | 设置节点默认 PHP | `btdh`、`version` | `qk`、`msg` |

**参数说明：**

- `btos`：操作系统（`1`=Linux，`0`=Windows）
- `xieyi`：协议（`http`/`https`）
- `btdh` / `bh`：宝塔节点编号

---

### 2.3 主机管理（zj）

文件：[admin/api/zj.php](file:///d:/documents/GitHub/MNBT/admin/api/zj.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listzj` | 主机列表（分页） | `page`、`limit`、`sort`、`sortOrder`、`where`（可选） | `total`、`rows[]` |
| `addzj` | 添加主机 | `btdh`、`user`、`pass`、`datae`、`webkj`、`sqlkj`、`ll`、`ymbds`、`kg` | `code`、`msg` |
| `zjxgjl` | 修改主机配置 | `id`、`user`、`pass`、`sqluser`、`sqlpass`、`ymbds`、`datar`、`webkj`、`sqlkj`、`llmax`、`kg` | `code`、`msg` |
| `zjsc` | 删除主机 | `id` | `code`、`msg` |
| `zjscxz` | 批量删除主机 | `idsz`（数组） | `code`、`msg` |

**参数说明：**

- `datae`/`datar`：到期日期（`Y-m-d`，`0000-00-00` 表示永久）
- `webkj`/`sqlkj`/`llmax`：网页空间、数据库空间、流量上限（单位 MB）
- `ymbds`：域名绑定数

---

### 2.4 一键部署程序（cx）

文件：[admin/api/cx.php](file:///d:/documents/GitHub/MNBT/admin/api/cx.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listbs` | 部署程序列表 | `page`、`limit`、`sort`、`sortOrder` | `total`、`rows[]` |
| `cxtj` | 添加部署程序 | `cxname`、`cxjs`、`cxrmb`、`cxwebkj`、`cxsqlkj`、`alerts`、`kg`、`filecx`、`imgfile`、`czf`、`bdf` | `code`、`msg` |
| `cxxgjl` | 修改部署程序 | `id`、`cxname`、`cxjc`、`webkj`、`sqlkj`、`cxrmb`、`alerts`、`cxkg`、`czf`、`bdf` | `code`、`msg` |
| `cxsc` | 删除部署程序 | `id` | `code`、`msg` |
| `cxscxz` | 批量删除 | `idsz` | `code`、`msg` |
| `cxdc` | 导出部署程序 | `id`（多个用 `,` 分隔） | `code`、`msg` |
| `cxfiledru` | 导入部署程序（分片上传） | `fesw`、`zsize`、`file` | `error`、`size`、`msg` |

**字段说明：**

- `czf`：安装配置（JSON，部署引擎脚本）
- `bdf`：安装前表单填写配置（JSON）
- `alerts`：部署完成后的提示文案

---

### 2.5 订单管理（dd）

文件：[admin/api/dd.php](file:///d:/documents/GitHub/MNBT/admin/api/dd.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listdd` | 订单列表（分页） | `page`、`limit`、`sort`、`sortOrder` | `total`、`rows[]` |
| `ddsc` | 删除订单 | `id` | `code`、`msg` |
| `ddscxz` | 批量删除订单 | `idsz` | `code`、`msg` |

---

### 2.6 系统公告与更新（gg）

文件：[admin/api/gg.php](file:///d:/documents/GitHub/MNBT/admin/api/gg.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `system_info` | 系统信息 | 无 | 见 [2.1](#21-登录与系统信息) |
| `gglist` | 公告列表 | 无 | 公告 JSON |
| `update` | 系统更新 | 无 | `code`、`msg` |

---

### 2.7 操作日志（log）

文件：[admin/api/log.php](file:///d:/documents/GitHub/MNBT/admin/api/log.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listlog` | 日志列表（分页/搜索） | `page`、`limit`、`sort`、`sortOrder`、`where` | `total`、`rows[]` |
| `logsc` | 删除日志 | `id` | `code`、`msg` |
| `logscxz` | 批量删除日志 | `idsz` | `code`、`msg` |
| `logclear` | 清空全部日志 | 无 | `code`、`msg` |

**日志字段：** `czuser`（操作用户）、`date`（时间）、`lx`（类型）、`lr`（内容）、`ip`、`qk`（情况）。

---

### 2.8 MNBT 节点管理（node）

文件：[admin/api/node.php](file:///d:/documents/GitHub/MNBT/admin/api/node.php)

#### 节点 CRUD

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listnode` | 节点列表 | `page`、`limit`、`sort`、`sortOrder`、`keyword`、`status` | `total`、`rows[]` |
| `addnode` | 添加节点 | `bt_id`、`node_name`、`node_id`、`interval_seconds` | `success`、`code`、`msg`、`id`、`node_id`、`config`、`config_json` |
| `delnode` | 删除节点 | `id` | `success`、`code`、`msg` |
| `setnodestatus` | 启用/禁用节点 | `id`、`enabled` | `success`、`code`、`msg` |
| `nodeconfig` | 获取节点配置 | `id` | `success`、`code`、`msg`、`config`、`config_json` |
| `nodeping` | 发起 Ping 任务 | `id` | `success`、`code`、`msg`、`task_id` |
| `nodestats` | 节点统计 | 无 | `success`、`code`、`msg`、`data` |

#### 违禁词扫描

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `nodeforbiddenscan` | 发起扫描任务 | `id`、`root`、`keywords`、`site`、`max_file_size_mb`、`max_matches` | `success`、`code`、`msg`、`task_id` |
| `listforbiddenscan` | 扫描结果列表 | `page`、`limit`、`sort`、`sortOrder`、`node_pk`、`time_filter`、`status_filter` | `total`、`rows[]` |
| `listforbiddenmatch` | 命中记录列表 | `page`、`limit`、`task_id` | `total`、`rows[]` |
| `get_global_keywords` | 获取全局违禁词 | 无 | `success`、`code`、`msg`、`data` |
| `savescancfg` | 保存扫描配置 | `skg`、`snr`、`sgbfx`、`sml`、`stqml`、`stqhz`、`sdzmax`、`sdhmax`、`sqzcskg`、`sqzcs` | `success`、`code`、`msg` |
| `clearoldscans` | 清理旧扫描 | `days` | `success`、`code`、`msg`、`deleted_scans`、`deleted_matches`、`deleted_tasks` |

**`time_filter` 枚举：** `today`、`yesterday`、`week`、`month`  
**`status_filter` 枚举：** `success`、`failed`、`has_matches`

#### 节点任务

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listnodetask` | 节点任务列表 | `page`、`limit`、`sort`、`sortOrder`、`node_pk`、`status`、`action` | `total`、`rows[]` |

#### 节点日志

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `nodeloglist` | 日志文件列表 | `node_id` | `success`、`code`、`msg`、`data`、`total_count` |
| `nodelogcontent` | 读取日志内容 | `node_id`、`log_file`、`offset`、`limit`、`keyword`、`level` | `success`、`code`、`msg`、`data`、`total_lines`、`file_size`、`current_offset`、`has_more` |
| `nodelogclear` | 清空日志文件 | `node_id`、`log_file` | `success`、`code`、`msg` |
| `nodeloglevel` | 设置日志级别 | `node_id`、`level`（DEBUG/INFO/WARNING/ERROR） | `success`、`code`、`msg`、`level`、`available_levels` |
| `nodelogstats` | 日志统计 | `node_id`、`log_file` | `success`、`code`、`msg`、`data` |

#### 站点统计

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `reset_sitestats` | 重置站点统计 | `node_id`、`site` | `success`、`code`、`msg`、`deleted` |

---

### 2.9 系统设置（setting）

文件：[admin/api/setting.php](file:///d:/documents/GitHub/MNBT/admin/api/setting.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `setwz` | 修改网站设置 | `gg`、`qq`、`yzm`、`zjyx` | `code`、`msg` |
| `setapi` | 修改 API 设置 | `apikey`、`apiqk`、`linux`、`windows` | `code`、`msg` |
| `setkzmb` | 修改控制面板 | `name`、`ftp`、`yzm`、`kg`、`bq`、`loa`、`lob`、`loc` | `code`、`msg` |
| `setpaymethods` | 设置支付方式 | `methods`（JSON） | `code`、`msg` |
| `gl` | 修改管理员账户 | `yuser`、`ypass`、`xuser`、`xpass` | `code`、`msg` |
| `mailmode` | 邮箱配置 | `host`、`user`、`password`、`port` | `code`、`msg` |
| `jkscsz` | 监控/违禁词扫描设置 | `ymkg`、`ymyjkg`、`ymtsyz`、`wjkg`、`wjyjkg`、`wjtsyz`、`option` | `code`、`msg` |
| `settheme` | 切换主题 | `usertheme`、`admintheme` | `code`、`msg` |

---

### 2.10 系统修复（repair）

文件：[admin/api/repair.php](file:///d:/documents/GitHub/MNBT/admin/api/repair.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `xtxf` | 系统修复 | `xx`（修复选项）、`xe`（版本标识） | `code`、`msg` |

---

### 2.11 插件管理（plugin）

文件：[admin/api/plugin.php](file:///d:/documents/GitHub/MNBT/admin/api/plugin.php)

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `plugin_list` | 插件列表 | 无 | `total`、`rows[]` |
| `plugin_install` | 安装插件 | `slug` | `code`、`msg` |
| `plugin_enable` | 启用/禁用插件 | `slug`、`enabled` | `code`、`msg` |
| `plugin_uninstall` | 卸载插件 | `slug` | `code`、`msg` |

---

## 3. 用户控制面板接口（user/ajax.php）

**入口**：`POST /user/ajax.php`  
**鉴权**：除 `gn=login` 外，全部需要用户登录（`user_token` Cookie）。  
**路由文件**：[user/ajax.php](file:///d:/documents/GitHub/MNBT/user/ajax.php) → `user/api/*.php`

> 插件 AJAX（`gn=p_xxx`）会优先由 `mnbt_plugin_dispatch_ajax('user', $egn)` 分发，详见 [5.1](#51-插件-ajax-调用规则)。

### 3.1 登录与初始化

#### `gn=login` —— 用户登录

| 项 | 值 |
|----|----|
| 文件 | [user/api/login.php](file:///d:/documents/GitHub/MNBT/user/api/login.php) |
| 鉴权 | 公开 |
| 参数 | `user`、`pass`、`code`（验证码，可选） |
| 返回 | `{"code":"登陆成功"}`，并写入 `user_token` Cookie（7天有效期） |
| 退出 | 同 `gn=login` 传 `logout=1` |

---

### 3.2 站点配置（site）

文件：[user/api/site.php](file:///d:/documents/GitHub/MNBT/user/api/site.php)

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

### 3.3 文件管理（file）

文件：[user/api/file.php](file:///d:/documents/GitHub/MNBT/user/api/file.php)

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

### 3.4 域名管理（domain）

文件：[user/api/domain.php](file:///d:/documents/GitHub/MNBT/user/api/domain.php)

> 域名 CRUD 已迁移到 `domain_shop` 插件，本文件仅保留查询接口。

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `hqzmlls` | 获取子目录域名列表 | 无 | `["domain1","domain2"]` 或 `false` |
| `listurl` | 获取域名绑定列表 | 无 | `{"domains":[{"name":"..."}]}` |

---

### 3.5 SSL 证书（ssl）

文件：[user/api/ssl.php](file:///d:/documents/GitHub/MNBT/user/api/ssl.php)

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `sqssl` | 申请/续签 Let's Encrypt | `list`（域名数组）、`type`（`false`=申请 / `true`=续签） | `{"qk":1\|4,"code":"..."}` |
| `setssl` | 设置自定义证书 | `key`、`pem` | `{"qk":1\|4,"code":"..."}` |
| `getssl` | 获取证书配置 | 无 | `{"key","csr","httpToHttps","status","cert_data","type"}` |
| `clossl` | 关闭 SSL | 无 | `{"qk":1\|4,"code":"..."}` |
| `httpsqz` | 强制 HTTPS 开关 | `qk`（`true`/`false`） | `{"qk":1\|4,"code":"..."}` |

---

### 3.6 数据库管理（database）

文件：[user/api/database.php](file:///d:/documents/GitHub/MNBT/user/api/database.php)

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `databaseadd` | 创建数据库 | `name`、`pass` | `code` |
| `databasedel` | 删除数据库 | `name` | `code` |
| `databasedownload` | 下载备份 | `name` | 文件流 |
| `databaserestore` | 恢复备份 | `name` | `code` |
| `databaseaq1` | 设置数据库权限 | `name`、`aq`（权限） | `code` |
| `Delalldatabase` | 删除所有数据库 | 无 | `code` |

---

### 3.7 缓存配置（cache）

文件：[user/api/cache.php](file:///d:/documents/GitHub/MNBT/user/api/cache.php)

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `cache_add` | 添加缓存规则 | `suffix`、`time_out`、`unit` | `code` |
| `cache_edit` | 修改缓存规则 | `id`、`suffix`、`time_out`、`unit` | `code` |
| `cache_del` | 删除缓存规则 | `id` | `code` |
| `cache_list` | 缓存规则列表 | 无 | 规则数组 |

**`unit` 枚举：** `second`、`minute`、`hour`、`day`

---

### 3.8 一键部署（deploy）

文件：[user/api/deploy.php](file:///d:/documents/GitHub/MNBT/user/api/deploy.php)

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `yjbs` | 执行部署 | `id`、`datas`（表单数据） | `code` |
| `yjbsform` | 获取部署表单 | `id` | `code`、表单配置 |

---

### 3.9 监控与通知（monitor / notice）

文件：[user/api/monitor.php](file:///d:/documents/GitHub/MNBT/user/api/monitor.php)

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

### 3.10 站点统计（site_stats）

文件：[user/api/site_stats.php](file:///d:/documents/GitHub/MNBT/user/api/site_stats.php)

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

### 3.11 其他（other）

文件：[user/api/other.php](file:///d:/documents/GitHub/MNBT/user/api/other.php)

| `gn` | 功能 | 必需参数 | 返回 |
|------|------|----------|------|
| `fdlkg` | 防盗链开关 | `qk` | `code` |
| `getfdl` | 获取防盗链配置 | 无 | 配置 JSON |
| `mailbd` | 绑定邮箱 | `mail` | `code` |
| `fzjh` | 反向代理 | `qk`、`url` | `code` |
| `indexconf` | 索引配置 | `qk` | `code` |

---

### 3.12 SPA 列表接口（panel）

文件：[user/api/panel.php](file:///d:/documents/GitHub/MNBT/user/api/panel.php)

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

## 4. 外部对接 API

### 4.1 主机生命周期 API（api/api.php）

**入口**：`POST /api/api.php?gn=<动作>`  
**文件**：[api/api.php](file:///d:/documents/GitHub/MNBT/api/api.php)  
**Content-Type**：`application/json; charset=UTF-8`

#### 鉴权参数（所有请求必带）

| 参数 | 说明 |
|------|------|
| `mn_bh` | 宝塔节点编号（`MN_bt.btdh`） |
| `mn_key` | 系统密钥（= `MN_config.api`） |
| `mn_keye` | 宝塔调用密钥 = `md5(MN_bt.ktmy . MN_bt.qmk)` |
| `mn_vs` | 插件版本号（必须 >= 15） |
| `username` | 主机用户名（`MN_zj.user`） |

#### 接口列表

| `gn` | 功能 | 额外参数 | 返回 |
|------|------|----------|------|
| `cfif` | 连接验证 | 无 | `{"success":true,"code":200,"msg":"连接验证成功！"}` |
| `kt` | 开通主机 | `password`、`sizemax`、`dqtime`、`webdx`、`sqldx`、`ymbds` | 同上 |
| `zt` | 暂停主机 | 无 | 同上 |
| `jc` | 解除暂停 | 无 | 同上 |
| `tz` | 删除主机 | 无 | 同上 |
| `xf` | 续费主机 | `setdate`（续期日期） | 同上 |
| `czmm` | 重置 FTP/面板密码 | `password` | 同上 |
| `zjmode` | 修改主机配额 | `websize`、`sqlsize`、`ll` | 同上 |

#### 请求示例

```http
POST /api/api.php?gn=kt HTTP/1.1
Host: your-domain.com
Content-Type: application/x-www-form-urlencoded

mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_OF_BT_KEY&mn_vs=15&username=testuser&password=testpass123&sizemax=1024&dqtime=2026-12-31&webdx=1024&sqldx=512&ymbds=5
```

#### 响应示例

```json
{
  "success": true,
  "code": 200,
  "msg": "主机开通成功！"
}
```

```json
{
  "success": false,
  "code": 100,
  "msg": "错误！该主机已经存在，请重新开通！"
}
```

**开通规则：**

- 账号、密码长度 >= 6
- 账号不能重复
- 自动检测节点 PHP 版本（优先使用 `MN_bt.mrbts_php`，否则取最新已安装版本）
- 自动触发 `host.created` 钩子（插件可监听）

---

### 4.2 MNBT 节点 API（api/node.php）

**入口**：`POST /api/node.php?act=<动作>`  
**文件**：[api/node.php](file:///d:/documents/GitHub/MNBT/api/node.php)  
**Content-Type**：`application/json`（请求体为 JSON）  
**鉴权**：通过 `mnbt_node_authenticate()` 校验 `node_id` + `node_secret`

#### 接口列表

| `act` | 功能 | 请求体 | 返回 |
|-------|------|--------|------|
| `heartbeat` | 心跳上报 | 节点状态信息 | `{"success":true,"msg":"heartbeat ok","server_time":"..."}` |
| `pull_task` | 拉取待执行任务 | 无 | `{"success":true,"msg":"pull task ok","task":{...}}` |
| `report_result` | 上报任务结果 | 任务执行结果 | `{"success":true,"msg":"..."}` |
| `get_config` | 获取节点配置 | 无 | `{"success":true,"msg":"config ok","config":{"forbidden_scan":{...}}}` |

#### `get_config` 返回的违禁词扫描配置

```json
{
  "config": {
    "forbidden_scan": {
      "enabled": true,
      "content": "违禁词1,违禁词2",
      "scan_changed_only": true,
      "scan_dir": "/www/wwwroot",
      "skip_dirs": ".git,node_modules,vendor,runtime,cache,logs",
      "skip_exts": ".jpg,.png,.gif,.webp,.mp4,.zip,.rar,.7z,.pdf,.woff,.ttf",
      "max_file_size": 5242880,
      "max_matches": 1000,
      "full_scan_enabled": true,
      "full_scan_cron": "0 3 * * *"
    }
  }
}
```

#### 监控任务执行入口

| 文件 | 用途 | 触发方式 |
|------|------|----------|
| [jk_monitor.php](file:///d:/documents/GitHub/MNBT/jk_monitor.php) | URL 监控 + 资源监控 + 到期/流量提醒 | 宝塔计划任务每分钟访问 `?my=API密钥` |
| [jk.php](file:///d:/documents/GitHub/MNBT/jk.php) | 域名/文件监控 | 内部调用 |

---

## 5. 插件对接 API

### 5.1 插件 AJAX 调用规则

**管理端：** `POST /admin/ajax.php`，`gn` 必须以 `p_` 开头（建议 `p_{slug}_xxx`）  
**用户端：** `POST /user/ajax.php`，`gn` 必须以 `p_` 开头（建议 `p_{slug}_xxx`）

> 框架在分发核心 `gn` 前会优先调用 `mnbt_plugin_dispatch_ajax($scope, $egn)` 命中插件回调。

**示例（domain_shop 插件）：**

```http
POST /admin/ajax.php
gn=p_domain_addym&url=example.com&bt=1&jg=10&ymjs=介绍&kg=true&channel=pan&provider_id=0
```

```http
POST /user/ajax.php
gn=p_dns_record_list&type=A
```

### 5.2 插件页面 URL 规则

| 端 | URL 模板 |
|----|----------|
| 管理端 | `/admin/plugin.php?p={slug}&page={page}` |
| 用户端 | `/user/plugin.php?p={slug}&page={page}` |

**示例：**

- `/admin/plugin.php?p=domain_shop&page=products`
- `/user/plugin.php?p=domain_shop&page=bind`

### 5.3 插件注册函数

> 全部定义在 [MPHX/plugin.php](file:///d:/documents/GitHub/MNBT/MPHX/plugin.php)，详见 [app_plugins/PLUGIN_DEV.md](file:///d:/documents/GitHub/MNBT/app_plugins/PLUGIN_DEV.md)

#### 基础注册

| 函数 | 用途 |
|------|------|
| `mnbt_plugin_register($slug, $info)` | 注册插件基础信息 |
| `mnbt_add_action($hook, $callback, $priority=10)` | 注册动作钩子 |
| `mnbt_do_action($hook, ...$args)` | 触发动作 |
| `mnbt_add_filter($hook, $callback, $priority=10)` | 注册过滤器 |
| `mnbt_apply_filters($hook, $value, ...$args)` | 应用过滤器 |

#### 能力注册

| 函数 | 用途 |
|------|------|
| `mnbt_register_ajax($scope, $gn, $callback, $auth=null)` | 注册 AJAX 接口（`scope`: `admin`/`user`） |
| `mnbt_register_page($scope, $page, $file, $title)` | 注册插件页面 |
| `mnbt_register_menu($scope, $menu)` | 注册菜单（支持 `children` 子菜单） |
| `mnbt_register_widget($scope, $area, $callback)` | 注册小部件（仪表盘） |
| `mnbt_register_settings_tab($scope, $tab)` | 注册设置页 Tab |
| `mnbt_register_home($callback, $priority=10)` | 接管站点首页 `/` |
| `mnbt_register_route($method, $path, $callback, $priority=10, $auth=null)` | 注册通用路由 |
| `mnbt_register_page_override($scope, $view, $callback)` | 接管主题页面渲染 |
| `mnbt_register_partial_override($scope, $view, $callback)` | 接管主题 partial |

#### 通用路由示例

```php
// 命名参数 {id}、尾斜杠可选
mnbt_register_route('GET', '/api/items/{id}', function ($params, $ctx) {
    return ['id' => $params['id']];
}, 10, 'user');
```

#### 配置存取

| 函数 | 用途 |
|------|------|
| `mnbt_plugin_option_get($slug, $key, $default=null)` | 获取插件配置 |
| `mnbt_plugin_option_set($slug, $key, $value)` | 设置插件配置 |
| `mnbt_plugin_require_admin()` | 强制要求管理员（手动调用） |
| `mnbt_plugin_require_user()` | 强制要求用户（手动调用） |

#### HTTP 工具

| 函数 | 用途 |
|------|------|
| `mnbt_http_get($url, $headers=[])` | GET 请求（默认禁内网） |
| `mnbt_http_post($url, $data, $headers=[])` | POST 请求（默认禁内网） |

### 5.4 钩子（Hooks）一览

| 钩子名 | 触发位置 | 参数 |
|--------|----------|------|
| `boot` | 系统启动完成 | 无 |
| `host.created` | 主机开通后 | `$user`、`$zjid`、`$bh` |
| `host.paused` | 主机暂停 | `$user`、`$zjid` |
| `host.unpaused` | 主机解除暂停 | `$user`、`$zjid` |
| `host.renewed` | 主机续费 | `$user`、`$zjid`、`$new_date` |
| `host.deleted` | 主机删除 | `$user`、`$zjid` |
| `order.paid` | 订单支付成功 | `$order` |
| `cron` | 计划任务触发 | 无 |
| `menu.admin` | 管理端菜单构建 | `&$menu` |
| `menu.user` | 用户端菜单构建 | `&$menu` |
| `dashboard.admin.widgets` | 管理端仪表盘小部件 | `&$widgets` |
| `dashboard.user.widgets` | 用户端仪表盘小部件 | `&$widgets` |
| `settings.admin.tabs` | 管理端设置页 Tab | `&$tabs` |

**示例：监听主机开通钩子自动建 DNS 记录**

```php
mnbt_add_action('host.created', function ($user, $zjid, $bh) {
    // 自动为新主机创建 A 记录
    domain_shop_auto_create_dns($user, $bh);
}, 10);
```

### 5.5 支付插件 API

| 函数 | 用途 |
|------|------|
| `mnbt_register_payment($slug, $info)` | 注册支付插件 |
| `mnbt_get_payment_plugins()` | 获取所有已注册支付插件 |
| `mnbt_get_enabled_payment_methods()` | 获取已启用的支付方式 |
| `mnbt_save_payment_methods($methods)` | 保存支付方式配置 |
| `mnbt_pay_type($slug, $method)` | 构造支付方式 type 字符串 |
| `mnbt_pay_parse_type($type)` | 解析支付方式 type |
| `mnbt_pay_dispatch_gateway($order, $type)` | 分发到支付插件 build 回调 |
| `mnbt_pay_settle_order($order, $gateway_slug, $extra=[])` | **支付结算核心**（更新订单状态 + 触发 `order.paid` 钩子） |
| `mnbt_pay_log($content, $level='info')` | 支付日志记录 |

**支付回调路由约定：**

| URL | 类型 | 说明 |
|-----|------|------|
| `/pay/{slug}/notify` | 异步通知 | 支付网关服务器回调 |
| `/pay/{slug}/return` | 同步返回 | 用户支付完成跳转 |

**支付插件注册示例（epay）：**

```php
mnbt_register_payment('epay', [
    'name'    => '易支付',
    'methods' => ['alipay' => '支付宝', 'wxpay' => '微信', 'qqpay' => 'QQ'],
    'build'   => function ($order, $method) { /* 跳转支付 */ },
    'notify'  => function () { /* 验签 + mnbt_pay_settle_order */ },
    'return'  => function () { /* 同步跳转 */ },
]);
```

**支付流程时序：**

```
用户下单 → mnbt_pay_dispatch_gateway → epay.build() → 跳转易支付
  ↓
用户支付完成 → /pay/epay/notify → epay.notify() → mnbt_pay_settle_order()
  ↓
mnbt_pay_settle_order() → 更新 MN_dd → 触发 order.paid 钩子 → 插件自动开通业务
```

### 5.6 自动鉴权机制

V1.81+ 插件系统支持通过 `auth` 参数声明权限要求，框架自动验证。

#### `auth` 取值

| 值 | 含义 | 失败响应 |
|----|------|----------|
| `null` / `''` / `'none'` | 无验证（默认） | — |
| `'admin'` | 需要管理员登录 | `{"code":"请登陆后台"}` |
| `'user'` | 需要用户登录 | `{"code":"请登陆"}` |
| 回调函数 | 自定义验证，返回 `true`/`false` | 自动跳转登录或返回 `{"code":"请登陆"}` |

#### 路由自动鉴权

```php
mnbt_register_route('POST', '/api/admin/config', function ($params, $ctx) {
    // 框架已自动验证管理员身份
}, 10, 'admin');
```

#### AJAX 自动鉴权

```php
mnbt_register_ajax('admin', 'my_plugin_save', function () {
    // 框架已自动验证管理员
}, 'admin');

mnbt_register_ajax('user', 'my_plugin_get_data', function () {
    // 框架已自动验证用户
}, 'user');
```

#### 核心函数

| 函数 | 用途 |
|------|------|
| `mnbt_plugin_auth_check($auth)` | 验证鉴权（返回 bool） |
| `mnbt_plugin_auth_fail($auth)` | 鉴权失败处理（自动 exit） |

---

## 6. 核心工具函数

### 6.1 响应函数

> 全部定义在 [MPHX/function.php](file:///d:/documents/GitHub/MNBT/MPHX/function.php) 和 [MPHX/Response.php](file:///d:/documents/GitHub/MNBT/MPHX/Response.php)

| 函数 | 用途 |
|------|------|
| `json_exit($code, $extra=[])` | 输出 JSON 并退出 |
| `json_exit_success($msg, $extra=[])` | 输出成功 JSON 并退出（自动 `qk=1`） |
| `json_exit_error($msg, $extra=[])` | 输出失败 JSON 并退出（自动 `qk=4`） |
| `json_echo($code, $extra=[])` | 输出 JSON 不退出 |
| `json_return($code, $extra=[])` | 返回 JSON 字符串 |
| `mnbt_json_encode($code, $extra=[], $success=null)` | 构造统一 JSON 字符串 |
| `Response::build($code, $msg, $data, $redirect, $success)` | 构造响应数组 |
| `Response::exit_json(...)` | 输出 JSON 并退出 |
| `Response::exit_success($msg, $data, $redirect)` | 输出成功 JSON 并退出 |
| `Response::exit_error($msg, $data, $redirect)` | 输出失败 JSON 并退出 |

### 6.2 业务函数

| 函数 | 用途 |
|------|------|
| `logjl($czuser, $lx, $lr, $qk, $DB)` | 写操作日志到 `MN_log` |
| `mnbt_log($user, $type, $content, $status, $db)` | `logjl` 别名 |
| `send_post($url, $post_data)` | HTTP POST（`file_get_contents` + stream context） |
| `curl_get($url)` | HTTP GET（cURL，10s 超时） |
| `authcode($string, $operation, $key, $expiry)` | 双向加解密（RC4 风格） |
| `daddslashes($string, $force, $strip)` | 递归 addslashes |
| `showmsg($content, $type, $back)` | 显示 HTML 提示页 |
| `sysmsg($msg, $die)` | 显示系统错误页 |
| `deldir($dir)` | 递归删除目录 |
| `zipfile($path, $zipth, $paths, $filetext)` | 递归压缩目录 |

---

## 7. 数据库表速查

> 完整表结构见 [install/install.sql](file:///d:/documents/GitHub/MNBT/install/install.sql)

| 表名 | 说明 | 关键字段 |
|------|------|----------|
| `MN_config` | 系统配置 | `api`、`kzmbqk`、`hxi`、`hxo`、`pay_methods` |
| `MN_bt` | 宝塔节点 | `btdh`、`btip`、`btdk`、`btmy`、`ktmy`、`qmk`、`btos`、`mrbts_php` |
| `MN_zj` | 主机账号 | `id`、`btid`、`user`、`pass`、`ssbt`、`sqldz`、`datae`、`hxa`、`hxb`、`llmax` |
| `MN_ym` | 售卖域名 | `url`、`bt`、`jg`、`kg` |
| `MN_bs` | 一键部署程序 | `name`、`jg`、`src`、`sxpz`、`tj`、`qk` |
| `MN_dd` | 订单 | `user`、`rmb`、`ff`、`scene`、`qk` |
| `MN_log` | 操作日志 | `czuser`、`date`、`lx`、`lr`、`ip`、`qk` |
| `MN_monitor_task` | 监控任务 | `user`、`task_type`、`url`、`interval_seconds`、`enabled` |
| `MN_monitor_log` | 监控检测日志 | `task_id`、`user`、`status_code`、`response_time` |
| `MN_notice_log` | 通知日志 | `user`、`type`、`level`、`title`、`content`、`is_read` |
| `MN_node` | 节点注册 | `node_id`、`node_secret`、`capabilities`、`last_heartbeat` |
| `MN_node_task` | 节点任务队列 | `node_id`、`action`、`payload`、`status` |
| `MN_node_nonce` | 防重放 nonce | `node_id`、`nonce`、`expires_at` |
| `MN_forbidden_scan` | 违禁词扫描摘要 | `node_id`、`task_id`、`status` |
| `MN_forbidden_match` | 违禁词命中记录 | `scan_id`、`file`、`keyword` |
| `MN_plugin` | 插件表 | `slug`、`enabled`、`version` |
| `MN_plugin_option` | 插件配置 | `slug`、`key`、`value` |
| `MN_plugin_user` | 插件独立用户表 | `username`、`password_hash`、`email`、`qq`、`status` |

---

## 附录：常用请求示例

### 用户登录示例

```bash
curl -X POST http://your-domain/user/ajax.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "gn=login&user=testuser&pass=testpass&code=1234"
```

### 获取主机列表

```bash
curl -X POST http://your-domain/admin/ajax.php \
  -H "Cookie: admin_token=xxxxxx" \
  -d "gn=listzj&page=1&limit=20&sort=id&sortOrder=desc"
```

### 外部 API 开通主机

```bash
curl -X POST "http://your-domain/api/api.php?gn=kt" \
  -d "mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=newuser&password=newpass123&sizemax=1024&dqtime=2026-12-31&webdx=1024&sqldx=512&ymbds=5"
```

### 节点心跳

```bash
curl -X POST "http://your-domain/api/node.php?act=heartbeat" \
  -H "Content-Type: application/json" \
  -d '{"node_id":"n1","node_secret":"xxx","status":"online","load":1.2}'
```

### 插件 AJAX（domain_shop）

```bash
curl -X POST http://your-domain/admin/ajax.php \
  -H "Cookie: admin_token=xxxxxx" \
  -d "gn=p_domain_addym&url=example.com&bt=1&jg=10&ymjs=介绍&kg=true&channel=pan&provider_id=0"
```

---

## 8. Docker 容器服务 API（V1.83）

### 8.1 外部开通 API（api/docker.php）

鉴权与 `api/api.php` 完全一致：`mn_key`=系统 API 密钥，`mn_bh`=节点编号，`mn_keye`=md5(节点 ktmy . qmk)，`mn_vs`>=15。

#### 连接验证（gn=cfif）

```bash
curl -X POST "http://your-domain/api/docker.php?gn=cfif" \
  -d "mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=test"
```

#### 开通 Docker 账户（gn=kt）

| 参数 | 必填 | 说明 |
|------|------|------|
| `username` | 是 | Docker 账号（≥4位，唯一） |
| `password` | 是 | 密码（≥6位，bcrypt 存储） |
| `dqtime` | 否 | 到期时间，`0`=永久（默认） |
| `plan_id` | 否 | 套餐 ID（需为上架状态） |
| `email` | 否 | 邮箱 |

```bash
curl -X POST "http://your-domain/api/docker.php?gn=kt" \
  -d "mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=duser1&password=dpass123&dqtime=2026-12-31&plan_id=1"
```

响应：`{"success":true,"code":200,"msg":"Docker 账户开通成功！"}`

> 开通仅创建账户，容器由用户登录 `docker/` 控制台后在应用商店自行创建（单容器模型）。

### 8.2 Docker 控制台 AJAX（docker/ajax.php）

认证：`docker_token` cookie + CSRF（`_csrf` 字段或 `X-CSRF-TOKEN` 头）。

| `gn` | 方法 | 说明 |
|------|------|------|
| `login` | POST | 登录（username/password） |
| `logout` | POST | 登出 |
| `my_container` | POST | 获取我的容器（单容器隔离过滤） |
| `container_start` | POST | 启动我的容器 |
| `container_stop` | POST | 停止我的容器 |
| `container_restart` | POST | 重启我的容器 |
| `install_log` | POST | 安装进度日志（get_cmd_log） |
| `image_list` | POST | 本地镜像列表 |
| `volume_list` | POST | 存储卷列表 |
| `compose_list` | POST | Compose 模板 + 项目 |
| `app_list` | POST | 应用商店列表（get_apps） |
| `app_detail` | POST | 单应用详情（appname） |
| `app_dependence` | POST | 依赖查询 |
| `app_create` | POST | 创建应用/开通容器（单容器+配额校验） |

`app_create` 关键参数：`app_name`/`m_version`/`s_version`/`cpus`/`memory_limit`/`allow_access` + 应用专属字段（来自 get_apps 的 env/field）。后端自动生成 `service_name=mnbt_<username>`，强制 cpus/memory 不超过套餐上限。

### 8.3 后台管理 AJAX（admin/ajax.php，gn=docker_*）

需管理员登录。指令前缀 `docker_`：

| `gn` | 说明 |
|------|------|
| `docker_user_list` | 用户列表（bootstrap-table） |
| `docker_user_add` / `docker_user_edit` / `docker_user_del` | 用户增改删 |
| `docker_user_reset` | 重置密码 |
| `docker_user_pause` / `docker_user_resume` | 暂停/恢复 |
| `docker_plan_list` / `docker_plan_add` / `docker_plan_edit` / `docker_plan_del` | 套餐管理 |
| `docker_node_config` | 节点 Docker 配置（get_config） |
| `docker_node_containers` | 节点容器列表 |
| `docker_options` | 节点/套餐下拉数据 |

### 8.4 到期软删定时任务

```bash
# 建议每 30 分钟执行
curl "http://your-domain/docker_cron.php?my=YOUR_API_KEY"
```

三阶段：`active→expired`（到期）→ `pruned`（满7天删容器）→ 物理删除（再满7天）。

---

**相关文档：**

- [README.md](file:///d:/documents/GitHub/MNBT/README.md) —— 项目总览
- [app_plugins/PLUGIN_DEV.md](file:///d:/documents/GitHub/MNBT/app_plugins/PLUGIN_DEV.md) —— 插件开发手册
- [templates/THEME_DEV.md](file:///d:/documents/GitHub/MNBT/templates/THEME_DEV.md) —— 主题开发手册

---

<div align="center">

**MNBT API 文档** © 2022-2026 梦奈云 版权所有

</div>
