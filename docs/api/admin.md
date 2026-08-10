---
title: 后台管理接口
description: admin/ajax.php 后台管理 AJAX 接口（登录、宝塔节点、主机管理、一键部署、订单、系统设置等）
---

# 后台管理接口（admin/ajax.php）

**入口**：`POST /admin/ajax.php`  
**鉴权**：除 `gn=login` 外，全部需要管理员登录（`admin_token` Cookie）。  
**路由文件**：admin/ajax.php → admin/api/*.php

## 2.1 登录与系统信息

### `gn=login` —— 管理员登录

| 项 | 值 |
|----|----|
| 文件 | admin/api/login.php |
| 鉴权 | 公开 |
| 参数 | `user`、`pass`、`code`（验证码，可选） |
| 返回 | `{"code":"登陆成功"}`，并写入 `admin_token` Cookie |

### `gn=system_info` —— 系统信息

| 项 | 值 |
|----|----|
| 文件 | admin/api/gg.php |
| 参数 | 无 |
| 返回字段 | `hosts`、`bt_panels`、`nodes`、`orders`、`os`、`hostname`、`php_version`、`php_sapi`、`server_soft`、`server_ip`、`server_port`、`server_time`、`timezone`、`memory_limit`、`max_exec_time`、`upload_max`、`post_max`、`ext_count`、`disk_total`、`disk_free`、`disk_used`、`disk_pct`、`mem_current`、`mem_peak`、`load_avg`、`db_version`、`web_version`、`sql_version` |

### `gn=gglist` —— 公告列表

返回系统公告 JSON 数组。

### `gn=update` —— 在线系统更新

| 项 | 值 |
|----|----|
| 参数 | 无 |
| 返回 | `{"code":"...", "msg":"..."}` |

---

## 2.2 宝塔节点管理（bt）

文件：admin/api/bt.php

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

## 2.3 主机管理（zj）

文件：admin/api/zj.php

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

## 2.4 一键部署程序（cx）

文件：admin/api/cx.php

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

## 2.5 订单管理（dd）

文件：admin/api/dd.php

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listdd` | 订单列表（分页） | `page`、`limit`、`sort`、`sortOrder` | `total`、`rows[]` |
| `ddsc` | 删除订单 | `id` | `code`、`msg` |
| `ddscxz` | 批量删除订单 | `idsz` | `code`、`msg` |

---

## 2.6 系统公告与更新（gg）

文件：admin/api/gg.php

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `system_info` | 系统信息 | 无 | 见 [2.1 登录与系统信息](#21-登录与系统信息) |
| `gglist` | 公告列表 | 无 | 公告 JSON |
| `update` | 系统更新 | 无 | `code`、`msg` |

---

## 2.7 操作日志（log）

文件：admin/api/log.php

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listlog` | 日志列表（分页/搜索） | `page`、`limit`、`sort`、`sortOrder`、`where` | `total`、`rows[]` |
| `logsc` | 删除日志 | `id` | `code`、`msg` |
| `logscxz` | 批量删除日志 | `idsz` | `code`、`msg` |
| `logclear` | 清空全部日志 | 无 | `code`、`msg` |

**日志字段：** `czuser`（操作用户）、`date`（时间）、`lx`（类型）、`lr`（内容）、`ip`、`qk`（情况）。

---

## 2.8 MNBT 节点管理（node）

文件：admin/api/node.php

### 节点 CRUD

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listnode` | 节点列表 | `page`、`limit`、`sort`、`sortOrder`、`keyword`、`status` | `total`、`rows[]` |
| `addnode` | 添加节点 | `bt_id`、`node_name`、`node_id`、`interval_seconds` | `success`、`code`、`msg`、`id`、`node_id`、`config`、`config_json` |
| `delnode` | 删除节点 | `id` | `success`、`code`、`msg` |
| `setnodestatus` | 启用/禁用节点 | `id`、`enabled` | `success`、`code`、`msg` |
| `nodeconfig` | 获取节点配置 | `id` | `success`、`code`、`msg`、`config`、`config_json` |
| `nodeping` | 发起 Ping 任务 | `id` | `success`、`code`、`msg`、`task_id` |
| `nodestats` | 节点统计 | 无 | `success`、`code`、`msg`、`data` |

### 违禁词扫描

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

### 节点任务

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `listnodetask` | 节点任务列表 | `page`、`limit`、`sort`、`sortOrder`、`node_pk`、`status`、`action` | `total`、`rows[]` |

### 节点日志

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `nodeloglist` | 日志文件列表 | `node_id` | `success`、`code`、`msg`、`data`、`total_count` |
| `nodelogcontent` | 读取日志内容 | `node_id`、`log_file`、`offset`、`limit`、`keyword`、`level` | `success`、`code`、`msg`、`data`、`total_lines`、`file_size`、`current_offset`、`has_more` |
| `nodelogclear` | 清空日志文件 | `node_id`、`log_file` | `success`、`code`、`msg` |
| `nodeloglevel` | 设置日志级别 | `node_id`、`level`（DEBUG/INFO/WARNING/ERROR） | `success`、`code`、`msg`、`level`、`available_levels` |
| `nodelogstats` | 日志统计 | `node_id`、`log_file` | `success`、`code`、`msg`、`data` |

### 站点统计

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `reset_sitestats` | 重置站点统计 | `node_id`、`site` | `success`、`code`、`msg`、`deleted` |

---

## 2.9 系统设置（setting）

文件：admin/api/setting.php

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

## 2.10 系统修复（repair）

文件：admin/api/repair.php

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `xtxf` | 系统修复 | `xx`（修复选项）、`xe`（版本标识） | `code`、`msg` |

---

## 2.11 插件管理（plugin）

文件：admin/api/plugin.php

| `gn` | 功能 | 必需参数 | 返回要点 |
|------|------|----------|----------|
| `plugin_list` | 插件列表 | 无 | `total`、`rows[]` |
| `plugin_install` | 安装插件 | `slug` | `code`、`msg` |
| `plugin_enable` | 启用/禁用插件 | `slug`、`enabled` | `code`、`msg` |
| `plugin_uninstall` | 卸载插件 | `slug` | `code`、`msg` |

---

**相关文档：**

- [API 通用约定](./overview.md)
- [用户控制面板接口](./user.md)
