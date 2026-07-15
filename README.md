# 梦奈宝塔主机系统 (MNBT) V1.81

基于宝塔面板 API 的虚拟主机分销管理系统，支持多节点宝塔面板统一管理、用户自主开通主机、一键部署网站程序、在线文件管理、Gzip/缓存配置、URL/资源监控告警、违禁词扫描、**可切换前端主题**、**PHP 业务插件**等功能。

![PHP](https://img.shields.io/badge/PHP-7.4%20~%208.4-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.6%2B-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-Commercial-blue)
![Version](https://img.shields.io/badge/version-1.81-green)

---

## 目录

- [项目简介](#项目简介)
- [核心特性](#核心特性)
- [功能概览](#功能概览)
- [环境要求](#环境要求)
- [快速开始](#快速开始)
- [目录结构](#目录结构)
- [数据库](#数据库)
- [API 文档](#api-文档)
- [宝塔面板对接](#宝塔面板对接)
- [MNBT 节点插件](#mnbt-节点插件)
- [前端模板](#前端模板)
- [主题开发](#主题开发)
- [PHP 业务插件](#php-业务插件)
- [插件开发](#插件开发)
- [安全说明](#安全说明)
- [常见问题](#常见问题)
- [更新日志](#更新日志)
- [许可证](#许可证)
- [联系方式](#联系方式)

---

## 项目简介

**MNBT（MengNai BT）** 是一套面向虚拟主机经销商的完整解决方案，通过对接宝塔面板官方 API，将多台服务器、多操作系统（Linux/Windows）的宝塔面板统一纳管，对外提供可视化的主机分销系统。

适用于：虚拟主机经销商、IDC 服务商、云主机分销平台、企业内部多服务器管理等场景。

## 核心特性

- ✅ **多节点管理**：同时管理多台宝塔面板（Linux/Windows）
- ✅ **一键部署**：可视化配置部署流程，10 种自动化操作，支持模板导入导出
- ✅ **Gzip 压缩**：用户端可视化配置宝塔 Gzip 参数
- ✅ **静态缓存**：用户端自定义静态文件缓存规则
- ✅ **监控告警**：URL 监控（状态码/内容匹配）+ 资源监控（空间/流量）+ 到期提醒 + 邮件通知
- ✅ **SSL 证书**：Let's Encrypt 一键申请/续签
- ✅ **MNBT 节点插件**：分布式节点管理、异步任务队列、违禁词扫描
- ✅ **PHP 8.x 全面兼容**：已修复全部废弃语法
- ✅ **SQL 参数化查询**：彻底消除 SQL 注入风险
- ✅ **完善的操作日志**：所有关键操作可追溯
- ✅ **可切换前端主题**：用户端 / 管理端独立皮肤，缺页回退 default
- ✅ **清凉云 SPA 主题**：Vue 3 + Element Plus，白绿圆角现代化用户端
- ✅ **PHP 业务插件**：`app_plugins/` 目录插件，钩子 / AJAX / 菜单 / 配置（与宝塔节点 Python 插件分离）

---

## 功能概览

### 管理后台

| 模块 | 功能 |
|------|------|
| 仪表盘 | 站点数据统计、系统公告、版本更新检测、系统修复 |
| 宝塔管理 | 添加/编辑/删除宝塔节点，支持 Linux/Windows，连接状态检测 |
| 主机管理 | 开通/暂停/删除主机，配额调整（空间/流量/域名数），到期时间设置 |
| 域名管理 | 域名添加、价格设置、绑定宝塔节点、上下架 |
| 一键部署 | 可编程部署引擎，10 种自动化操作 + 模板变量，支持导入/导出 |
| 订单管理 | 所有支付订单查询与管理 |
| 系统设置 | 网站公告、邮箱配置（SMTP）、API 密钥、PHP 版本、建站目录、监控告警、违禁词扫描、**前端模板切换** |
| **支付设置** | 插件化支付架构，内置易支付/支付宝官方插件，支持启用付款方式、自定义显示名与排序 |
| **插件管理** | 扫描 `app_plugins/`、安装/启用/禁用/卸载、插件菜单与页面 |
| 操作日志 | 完整操作日志记录、搜索、分页、清空 |
| 系统更新 | 远程在线升级 |

### 用户控制面板

| 模块 | 功能 |
|------|------|
| 仪表盘 | 资源用量展示（网页空间/数据库空间/流量）、月度流量趋势图（环比百分比）、Chart.js 动态图表 |
| 域名管理 | 添加/删除域名、子目录绑定、本站二级域名购置 |
| PHP 管理 | 一键切换 PHP 版本（5.2 ~ 8.1） |
| 文件管理 | 在线编辑/上传/下载/压缩/解压/复制/重命名，分片断点续传 |
| 数据库管理 | phpMyAdmin 入口、创建/删除/恢复备份、数据库权限设置 |
| SSL 证书 | Let's Encrypt 申请/续签、自定义证书上传、强制 HTTPS |
| 网站设置 | 默认文档、运行目录、伪静态规则、防盗链、密码访问目录、反向代理 |
| **Gzip 配置** | 开关、压缩级别、最小压缩长度、MIME 类型，通过宝塔 API 配置 |
| **缓存配置** | 新增/编辑/删除静态文件缓存规则，支持文件后缀 + 单位选择（秒/分钟/小时/天），通过宝塔 API 配置 |
| **监控任务** | URL 监控（状态码规则/内容匹配/SSRF 防护） + 资源监控（网页空间/数据库空间/流量阈值），每用户最多 5 个 |
| **检测日志** | 监控检测历史记录，查看状态码/响应时间/错误信息 |
| **通知日志** | 到期提醒、流量超额、监控告警，支持筛选/搜索/分页/全部已读 |
| 一键部署 | 选购并自动部署网站程序（WordPress、彩虹网盘等） |

### 外部 API 接口

| 接口 | 方法 | 说明 |
|------|------|------|
| `gn=kt` | POST | 开通主机（含 FTP + 数据库） |
| `gn=zt` | POST | 暂停主机 |
| `gn=jc` | POST | 解除暂停 |
| `gn=tz` | POST | 删除主机 |
| `gn=xf` | POST | 续费主机 |
| `gn=czmm` | POST | 重置密码 |
| `gn=zjmode` | POST | 修改主机配置 |

鉴权：`bh` + `user` + `key`（API 密钥）+ `keye`（宝塔二级密钥）。

### MNBT 节点插件系统

支持分布式宝塔节点管理，包含：
- 节点注册/心跳/能力声明
- 异步任务队列（轮询拉取）
- 非重放攻击防护（nonce）
- 违禁词扫描（定时全量 + 增量扫描）

---

## 环境要求

| 依赖 | 版本要求 |
|------|---------|
| PHP | **7.4 ~ 8.4**（已全面兼容 PHP 8.x） |
| MySQL | 5.6+ |
| Web 服务器 | Nginx / Apache |
| 宝塔面板 | Linux 版 / Windows 版 |
| PHP 扩展 | curl、mysqli、json、mbstring |
| Composer | 可选（仅用于 PHPMailer 安装） |

> 已修复 `each()`、`count($string)`、`get_magic_quotes_gpc()` 等 PHP 8 废弃语法；所有 SQL 已迁移至参数化查询。

---

## 安装部署

### 1. 准备环境

确保服务器已安装 PHP 7.4+ + MySQL，并已安装宝塔面板。

### 2. 部署代码

```bash
# 将安装包上传至网站目录并解压
# 设置网站运行目录为 /
# 配置伪静态规则（如使用 ThinkPHP 风格 URL）
```

### 3. 运行安装向导

访问 `http://你的域名/install`，按向导提示完成：

1. 欢迎 / 许可协议
2. 环境检测（PHP 版本 >= 7.4.0、curl 等）
3. 数据库配置（填写 MySQL 连接信息）
4. **站点与管理员**（控制面板名称、站长 QQ、公告、管理员账号密码）
5. 初始化数据库（自动导入表结构，或重装模式）
6. 完成安装（完成页会显示你设置的管理员账号）

### 4. 登录管理

- 管理后台：`http://你的域名/admin`
- 账号/密码：安装向导中设置的管理员信息（不再固定 `admin/123456`）
- ⚠️ **请妥善保存密码；安装后建议删除 `install` 目录**

### 5. 对接宝塔面板

1. 登录管理后台 → 宝塔管理 → 添加宝塔
2. 填写宝塔面板地址、端口、API 密钥
3. 宝塔面板中需开启 API 接口：面板设置 → API 接口 → 开启
4. 设置默认建站目录（Linux：`/www/wwwroot`，Windows：`D:/wwwroot`）

### 6. 配置计划任务（监控系统）

在宝塔面板 → 计划任务中添加：

| 任务类型 | 执行周期 | 执行命令 |
|---------|---------|---------|
| 访问 URL | 每分钟 | `http://你的域名/jk_monitor.php?my=API密钥` |

API 密钥可在管理后台 → 系统设置 → API 接口中查看。

### 7. 安装节点插件（可选）

如需使用分布式节点管理 + 违禁词扫描功能：

```bash
# 1. 在管理后台 → 节点管理 → 添加节点
# 2. 将 plugins/mnbt_connector/ 部署到宝塔服务器
# 3. 复制生成的 config.json 配置
# 4. 重启插件服务
systemctl restart mnbt-connector.service
```

## 快速开始

最简部署流程：

```bash
# 1. 上传代码
cd /www/wwwroot/your-domain
# 解压安装包

# 2. 设置权限
chmod -R 755 .
chown -R www:www .

# 3. 访问安装向导
# 浏览器打开 http://your-domain/install

# 4. 登录后台，修改默认密码
# 浏览器打开 http://your-domain/admin

# 5. 添加第一台宝塔服务器
# 后台 → 宝塔管理 → 添加宝塔

# 6. 配置默认建站目录和监控任务
# 后台 → 系统设置 → 基础设置
# 宝塔面板 → 计划任务 → 添加监控任务
```

---

## 目录结构

```
├── admin/                    # 管理后台
│   ├── index.php             # 后台框架（多标签页）
│   ├── login.php             # 管理员登录
│   ├── sy.php                # 仪表盘（数据统计）
│   ├── set.php               # 系统设置中心
│   ├── list.php              # 列表管理（宝塔/主机/域名/日志）
│   ├── add.php               # 添加管理
│   ├── ajax.php              # 路由入口 → 10 个模块文件
│   ├── api/                  # AJAX 模块
│   │   ├── bt.php            # 宝塔节点（增删改查/状态检测/域名处理）
│   │   ├── zj.php            # 主机管理（开通/暂停/删除/配额/开通）
│   │   ├── ym.php            # 域名管理
│   │   ├── dd.php            # 订单管理
│   │   ├── gg.php            # 公告管理
│   │   ├── cx.php            # 宝塔状态/支付信息查询
│   │   ├── node.php          # MNBT 节点管理
│   │   ├── login.php         # 登录
│   │   ├── repair.php        # 系统修复
│   │   ├── setting.php       # 系统设置
│   │   ├── plugin.php        # 插件管理 AJAX
│   │   └── log.php           # 操作日志
│   ├── plugin.php            # 插件管理页 / 插件页面入口
│   ├── class.php             # bt_api 引用包装器
│   ├── mail.php              # 邮件发送
│   └── update.php            # 系统更新
│
├── user/                     # 用户控制面板
│   ├── index.php             # 用户面板框架（侧边栏导航）
│   ├── login.php             # 用户登录
│   ├── sy.php                # 用户仪表盘（资源用量/流量趋势图）
│   ├── set.php               # 用户设置（PHP/Gzip/缓存/防盗链/SSL等）
│   ├── monitor.php           # 监控任务管理（URL + 资源监控）
│   ├── monitor_log.php       # 监控检测日志
│   ├── notice.php            # 通知日志（到期/流量/监控告警）
│   ├── webgl.php             # 一键部署
│   ├── ajax.php              # 路由入口 → 11 个模块文件
│   ├── api/                  # AJAX 模块
│   │   ├── login.php         # 登录/退出
│   │   ├── domain.php        # 域名管理
│   │   ├── file.php          # 文件管理
│   │   ├── cache.php         # 缓存配置
│   │   ├── site.php          # 站点配置（Gzip/密码/伪静态等）
│   │   ├── ssl.php           # SSL 证书
│   │   ├── monitor.php       # 监控任务 CRUD + 通知已读
│   │   ├── deploy.php        # 一键部署
│   │   ├── database.php      # 数据库管理
│   │   ├── other.php         # 其他功能（重置密码/邮箱绑定）
│   │   └── cdn.php           # CDN 相关
│   ├── ftp.php               # 在线文件管理
│   ├── mysql.php             # SQL 管理面板
│   ├── sqlgl.php             # SQL 数据备份
│   ├── pay.php               # 支付处理
│   └── amftp/                # AMFTP 文件管理器
│
├── MPHX/                     # 核心框架
│   ├── common.php            # 全局初始化（数据库/配置/错误日志）
│   ├── db.class.php          # 三合一 DB 类（MySQLi + MySQL 降级 + SQLite PDO）
│   ├── function.php          # 工具函数（json_exit/daddslashes/logjl/send_post）
│   ├── Response.php          # 统一响应处理类
│   ├── member.php            # 登录认证（Cookie Token）
│   ├── bt_api.php            # 统一宝塔 API 操作类（100+ 方法，12 功能区）
│   ├── monitor.function.php  # 监控函数库（自动建表/URL检测/资源百分比/SSRF防护/邮件通知）
│   ├── security.php          # 安全过滤
│   ├── node.function.php     # MNBT 节点函数库
│   ├── theme.php             # 主题加载引擎（render / 切换 / 回退）
│   ├── plugin.php            # PHP 业务插件引擎（钩子 / AJAX / 配置）
│   ├── database_backup.function.php
│   ├── BL.php / SQ.php       # 业务辅助
│   ├── lib/                  # 公共函数库（pay.function.php 支付结算逻辑）
│   └── 360safe/              # WAF 防护
│
├── templates/                # 前端主题（用户端 + 管理端视图）
│   ├── README.md             # 主题系统说明
│   ├── THEME_DEV.md          # 主题开发手册
│   ├── active_user_theme     # 当前用户端主题名
│   ├── active_admin_theme    # 当前管理端主题名
│   └── default/              # 官方默认主题
│       ├── theme.json
│       ├── user/             # 用户控制面板视图
│       └── admin/            # 管理后台视图
│
├── app_plugins/              # PHP 业务插件（非宝塔 Python 插件）
│   ├── README.md             # 插件开发说明
│   ├── PLUGIN_DEV.md         # 插件开发手册
│   ├── hello_demo/           # 官方示例（菜单 / AJAX / 配置）
│   ├── home_demo/            # 首页接管 + 通用路由示例（P2）
│   ├── epay/                 # 易支付插件（P3，从核心迁移）
│   └── alipay_official/      # 支付宝官方 API 插件（P3，PC + 当面付）
│
├── api/                      # 外部 API 接口
│   ├── api.php               # RESTful API 入口
│   ├── api.class.php         # bt_api 引用包装器
│   └── node.php              # MNBT 节点 API
│
├── install/                  # 安装向导
│   ├── index.php             # 安装步骤页面
│   ├── install.api.php       # 安装接口（PHP 版本 >= 7.4.0）
│   ├── install.sql           # 完整数据库表结构（含监控表/节点表/违禁词扫描表/插件表）
│   └── db.class.php          # 安装专用数据库类
│
├── jk.php                    # 域名/文件监控
├── jk_monitor.php            # 监控计划任务执行脚本（URL检测/资源阈值/到期提醒）
├── config.php                # 数据库配置文件
├── bash.conf.php             # Shell 命令配置
├── composer.json             # Composer（vendor-dir: mail/vendor）
├── mail/                     # PHPMailer 6.x 邮件库
├── filecx/                   # 一键部署程序包
├── plugins/                  # MNBT 节点插件（宝塔侧 Python）
├── runtime/                  # 运行时文件
│   └── logs/                 # PHP 错误日志
└── imsetes/                  # 静态资源（CSS/JS/字体/图标/CodeMirror/FullCalendar）
```

---

## 数据库

### 核心表结构（共 13 张表）

| 表名 | 说明 |
|------|------|
| `MN_config` | 系统配置（网站信息/支付/邮箱/监控/API/违禁词扫描等） |
| `MN_bt` | 宝塔面板节点（IP/端口/密钥/操作系统/状态） |
| `MN_zj` | 主机账号（宝塔关联/配额/到期时间/状态/邮箱绑定） |
| `MN_ym` | 售卖域名（绑定宝塔/价格/介绍/上下架） |
| `MN_bs` | 一键部署程序（名称/价格/安装配置/上架状态） |
| `MN_dd` | 支付订单（金额/方式/场景/状态） |
| `MN_log` | 操作日志（操作用户/时间/类型/IP/结果） |
| `MN_monitor_task` | 监控任务（URL/资源/规则/间隔/失败计数） |
| `MN_monitor_log` | 监控检测日志（HTTP 状态码/响应时间/错误信息） |
| `MN_notice_log` | 通知日志（到期/流量/监控告警，支持已读标记） |
| `MN_node` | MNBT 节点注册（ID/密钥/能力/心跳） |
| `MN_node_task` | 节点异步任务队列 |
| `MN_node_nonce` | 节点防重放攻击 nonce 表 |
| `MN_forbidden_scan` | 违禁词扫描任务摘要 |
| `MN_forbidden_match` | 违禁词扫描命中记录 |

### 业务关系

```
MN_bt (宝塔节点)  ──1:N──>  MN_zj (主机账号)
MN_bt (宝塔节点)  ──1:N──>  MN_ym (域名资源)
MN_zj (主机账号)  ──1:1──>  MN_ym? (域名绑定)
MN_dd (订单)      ──N:1──>  MN_bs / MN_ym (支付场景)
MN_zj (主机账号)  ──1:N──>  MN_monitor_task (监控任务)
MN_monitor_task   ──1:N──>  MN_monitor_log (检测日志)
MN_zj (主机账号)  ──1:N──>  MN_notice_log (通知日志)
```

---

## 宝塔面板对接

### 官方 API

项目使用 `MPHX/bt_api.php` 统一封装宝塔面板官方 API，遵循双重 MD5 签名规范：

```php
$params = [
    'request_token' => md5($time . md5($BT_KEY)),
    'request_time'  => $time
];
```

### API 操作覆盖（100+ 接口，12 功能区）

| 功能区 | 操作 |
|--------|------|
| 站点管理 | 创建/删除/启用/停用/到期时间/域名列表/获取站点列表 |
| 域名管理 | 添加/删除域名、子目录绑定、获取域名列表 |
| FTP 管理 | 创建/删除/修改密码、设置状态 |
| 数据库管理 | 创建/删除/修改密码、备份/恢复、权限设置 |
| SSL 证书 | Let's Encrypt 申请/续签、上传证书、关闭 SSL、强制 HTTPS |
| 文件管理 | 读取/写入/创建/删除/复制/移动/压缩/解压/上传/下载 |
| 反向代理 | 添加/删除 |
| 防盗链 | 开启/关闭/获取状态 |
| PHP 管理 | 切换版本、获取已安装版本列表 |
| 计划任务 | 添加/删除/执行 Shell 脚本 |
| Gzip 配置 | 获取状态/设置/关闭 |
| 静态缓存 | 获取规则/设置/删除 |

---

## 前端模板

### 内置主题

| 主题 ID | 名称 | 范围 | 技术栈 | 说明 |
|---------|------|------|--------|------|
| `default` | 默认主题 | 用户端 + 管理端 | Light Year Admin（Bootstrap 4 + jQuery） | 官方完整功能皮肤；登录页为简洁圆角白卡片 |
| `qingliangyun` | 清凉云 | 用户端 | Vue 3 + Element Plus + Vite | 现代化白绿配色 SPA，业务页原生化 |

默认 UI 基于 **Light Year Admin**：

| 资源 | 地址 |
|------|------|
| 开源仓库 | https://gitee.com/yinqi/Light-Year-Admin-Template-v5 |
| 模板文档 | http://www.itshubao.com/doc/lyearadmin5.html |

### 前端依赖（default）

- Bootstrap 4 + jQuery
- Material Design Icons
- Chart.js 3.x（流量趋势图/仪表盘图表）
- CodeMirror 在线代码编辑器
- FullCalendar 日历组件
- Bootstrap Table 数据表格
- jQuery Confirm 弹窗

### 前端依赖（清凉云）

- Vue 3 + Vue Router
- Element Plus + 图标
- Axios
- Vite 构建（产物提交于 `templates/qingliangyun/user/dist/`）

---

## 主题开发

MNBT 已支持**可切换前端主题**：`user/`、`admin/` 为控制器，页面 HTML 位于 `templates/{主题名}/`。

### 文档入口

| 文档 | 内容 |
|------|------|
| [templates/README.md](templates/README.md) | 目录结构、切换方式、API 一览、安全说明 |
| [templates/THEME_DEV.md](templates/THEME_DEV.md) | **完整开发手册**：新建主题、页面清单、变量约定、资源引用、FAQ |
| [templates/qingliangyun/README.md](templates/qingliangyun/README.md) | 清凉云 SPA：编译说明、路由、panel API |

### 快速切换

1. 后台登录 → **系统管理** → **前端模板**（`admin/set.php?gn=theme`）
2. 分别选择「用户端主题」「管理端主题」→ 保存
3. 或编辑 `templates/active_user_theme` / `templates/active_admin_theme`（写入主题目录名，如 `qingliangyun`）

### 新建主题（摘要）

```text
templates/my_theme/
├── theme.json          # 名称、版本、简介
├── user/               # 覆盖用户端页面（可只放部分文件）
│   ├── login.php
│   └── assets/
└── admin/              # 覆盖管理端页面（可只放部分文件）
    ├── login.php
    └── assets/
```

未提供的页面自动回退到 `templates/default/`。详细步骤与必选页面列表见 [THEME_DEV.md](templates/THEME_DEV.md)。

### 资源约定

| 类型 | 目录 | 引用 |
|------|------|------|
| 公共（Bootstrap / jQuery / logo 等） | `imsetes/` | `mnbt_asset_url('css/...')` |
| 主题私有 CSS/JS | `templates/{theme}/{user\|admin}/assets/` | `mnbt_theme_asset('xxx.css')` |

主题私有资源缺文件时自动回退 `default` 主题同路径。

### 核心代码

| 路径 | 说明 |
|------|------|
| `MPHX/theme.php` | `mnbt_render` / `mnbt_theme_asset` / 主题列表与切换 |
| `templates/default/user/` | 默认用户端视图 + `assets/` |
| `templates/default/admin/` | 默认管理端视图 + `assets/` |
| `templates/qingliangyun/` | 清凉云用户端 SPA 主题 |
| `user/api/panel.php` | SPA 列表/初始化 JSON 接口 |
| `admin/api/setting.php` | `settheme` 保存接口 |

---

## PHP 业务插件

与宝塔侧 `plugins/mnbt_connector`（Python 节点代理）**分离**。业务扩展放在 `app_plugins/`。

### 快速使用

1. 将插件目录放入 `app_plugins/{slug}/`（需 `plugin.json` + `bootstrap.php`）
2. 后台 → 系统管理 → **插件管理** → 安装 → 启用
3. **整页刷新**后台；侧栏出现「插件」菜单（若插件注册了菜单）
4. 已有站点执行一次 `update/update_v181_plugin.sql`（或依赖自动建表）

### 文档与示例

| 路径 | 说明 |
|------|------|
| **[app_plugins/PLUGIN_DEV.md](app_plugins/PLUGIN_DEV.md)** | **插件开发手册**（新建步骤、API、钩子、安全、FAQ） |
| [app_plugins/README.md](app_plugins/README.md) | 目录约定、快速启用、API 摘要 |
| `MPHX/plugin.php` | 插件引擎源码 |

### 自带插件

#### 业务插件

| 插件 | 说明 | README |
|------|------|--------|
| **user_info** | 独立用户系统（注册/登录/个人信息/修改密码） | [README](app_plugins/user_info/README.md) |
| **balance** | 余额管理（充值/消费/流水，依赖 user_info） | [README](app_plugins/balance/README.md) |
| **hosting_shop** | 主机售卖（套餐/购买/自动开通，依赖 user_info + balance） | [README](app_plugins/hosting_shop/README.md) |

#### 支付插件

| 插件 | 说明 | README |
|------|------|--------|
| **epay** | 易支付（彩虹易支付协议，支付宝/微信/QQ） | [README](app_plugins/epay/README.md) |
| **alipay_official** | 支付宝官方 API（PC + 当面付，RSA2） | [README](app_plugins/alipay_official/README.md) |

#### 集成与示例插件

| 插件 | 说明 | README |
|------|------|--------|
| **webhook_notify** | Webhook 通知（主机事件/订单支付，HMAC 签名） | [README](app_plugins/webhook_notify/README.md) |
| **home_demo** | 首页接管 + 通用路由示例（P2） | [README](app_plugins/home_demo/README.md) |
| **hello_demo** | 基础示例（菜单/AJAX/配置/钩子） | [README](app_plugins/hello_demo/README.md) |

### 能力

- 钩子：`boot`、`host.*`、`order.paid`、`cron`、`menu.*`、dashboard widgets
- 注册：`mnbt_register_ajax` / `page` / `menu` / `widget` / `settings_tab`
- 路由：`mnbt_register_home` / `mnbt_register_route`（P2，首页接管 + 通用路由）
- 支付：`mnbt_register_payment` / `mnbt_pay_settle_order`（P3，支付插件化）
- HTTP：`mnbt_http_get` / `mnbt_http_post`（默认禁内网）
- 配置：`mnbt_plugin_option_get/set`
- 页面：`admin/plugin.php?p=slug&page=...`、`user/plugin.php?p=slug&page=...`
- 内置插件：`user_info`、`balance`、`hosting_shop`、`epay`、`alipay_official`、`webhook_notify`、`home_demo`、`hello_demo`

---

## 插件开发

完整开发说明见 **[app_plugins/PLUGIN_DEV.md](app_plugins/PLUGIN_DEV.md)**（与 [主题开发手册](templates/THEME_DEV.md) 并列）。

### 最短新建流程

```text
app_plugins/my_plugin/
├── plugin.json      # name / version / description
├── bootstrap.php    # 注册菜单、AJAX、钩子
└── admin/index.php  # 可选后台页
```

1. 编写 `plugin.json` + `bootstrap.php`（用 `mnbt_register_*` / `mnbt_add_action`）
2. 后台 → **插件管理** → 安装 → 启用 → 整页刷新
3. AJAX：`POST admin/ajax.php`，`gn` 建议 `p_my_plugin_*`
4. 页面：`admin/plugin.php?p=my_plugin&page=index`

### 手册章节索引

| 章节 | 内容 |
|------|------|
| 设计原则 | 不改核心 URL、option 表、路径隔离 |
| 新建插件 | 目录、`plugin.json`、`bootstrap` 模板、自测清单 |
| 核心 API | 钩子 / AJAX / 菜单页面 / 配置 / 小部件 / HTTP |
| 钩子一览 | `host.*`、`order.paid`、`cron` 及触发位置 |
| 数据库 | `MN_plugin`、自建表前缀 `plg_*` |
| 安全与 FAQ | 鉴权、冲突、SPA、在线更新 |

### 与主题开发对照

| | 主题 | 插件 |
|--|------|------|
| 目录 | `templates/{id}/` | `app_plugins/{slug}/` |
| 手册 | [templates/THEME_DEV.md](templates/THEME_DEV.md) | [app_plugins/PLUGIN_DEV.md](app_plugins/PLUGIN_DEV.md) |
| 职责 | 外观与页面 HTML | 业务能力、事件、对接 |
| 后台开关 | 系统管理 → 前端模板 | 系统管理 → 插件管理 |

---

## 常见问题

### Q: 安装时提示数据库连接失败？

检查 `config.php` 中的数据库主机、端口、用户名、密码、数据库名是否正确，确认 MySQL 服务已启动。

### Q: 宝塔面板连接失败？

1. 确认宝塔面板 API 接口已开启（面板设置 → API 接口）
2. 确认 API 密钥已正确填写
3. 检查面板地址协议（HTTP/HTTPS）和端口号是否正确
4. 如使用 HTTPS，确认证书有效或关闭 SSL 验证

### Q: 用户无法登录控制面板？

1. 确认主机已开通且状态正常
2. 检查 `MN_config` 表中 `kzmbqk` 字段是否为 `true`（控制面板总开关）
3. 检查主机到期时间是否已过期

### Q: 文件管理上传失败？

1. 检查 PHP `upload_max_filesize` 和 `post_max_size` 配置
2. 检查主机空间配额是否已满
3. 检查网站目录写入权限

### Q: 监控任务如何生效？

1. 每个用户最多 5 个监控任务
2. URL 监控默认间隔 60 秒，资源监控固定 180 秒
3. 需在宝塔计划任务中配置每分钟访问 `jk_monitor.php?my=API密钥`
4. 通知日志会自动记录到期提醒（7/3/1/0 天）和流量超额（>=80%）

### Q: PHP 8.x 兼容吗？

已全面兼容 PHP 7.4 ~ 8.4。修复内容包括：`each()` 替换为 `foreach`、`count($string)` 替换为 `strlen`、`get_magic_quotes_gpc()` 写死返回 `false`、`var` 改为 `public`、移除 PHP 4 构造器、`strftime` 替换、`json_decode(null)` 保护等。

### Q: 如何升级到新版本？

1. 备份数据库和代码
2. 上传新版本代码覆盖
3. 运行 `install/install.sql` 中的增量 SQL（如有）
4. 清空 `runtime/` 目录下的缓存

---

## 安全说明

⚠️ **本系统仅限内部部署，请勿将以下文件/目录上传至公开仓库**：

- `config.php` - 包含数据库账号密码
- `install/install.lock` - 安装锁定文件
- `runtime/logs/*.log` - 错误日志，可能包含敏感信息
- `plugins/wwwlogs/*.log` - 访问日志
- `.env` - 环境变量（如使用）

### 推荐 .gitignore

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

### 部署安全建议

1. ✅ 使用安装时设置的强密码，并定期更换管理员密码
2. ✅ 修改 API 密钥（系统设置 → API 接口）
3. ✅ 启用 HTTPS（SSL 证书配置）
4. ✅ 修改宝塔面板默认端口
5. ✅ 限制管理后台 IP 访问（Nginx/Apache 配置）
6. ✅ 定期更新 PHP 版本
7. ✅ 关闭 PHP 错误显示（`display_errors = Off`）
8. ✅ 定期备份数据库
9. ✅ 监控异常登录和操作日志

---

## 更新日志

### V1.81（当前）

**PHP 业务插件系统（P0 + P1）**

- 引擎：`MPHX/plugin.php`，启动挂载于 `common.php`
- 目录：`app_plugins/{slug}/`（`plugin.json` + `bootstrap.php`）
- 表：`MN_plugin`、`MN_plugin_option`（升级 SQL：`update/update_v181_plugin.sql`）
- 后台：系统管理 → 插件管理；侧栏可注入「插件」菜单（管理端 + 用户端）
- AJAX：`user/ajax.php` / `admin/ajax.php` 优先分发插件 `gn`
- 钩子：`boot`、`host.created/paused/unpaused/renewed/deleted`、`order.paid`、`cron`、`menu.*`、dashboard widgets
- P1：`mnbt_http_*`、`mnbt_register_widget`、`mnbt_register_settings_tab`
- 示例：`hello_demo`、`webhook_notify`（主机/订单 Webhook + HMAC）
- 文档：[app_plugins/README.md](app_plugins/README.md)

**插件引擎扩展（P2 路由系统）**

- `mnbt_register_home()`：接管站点根 `/` 的响应（重定向或渲染自定义首页）
- `mnbt_register_route($method, $path, $cb)`：通用路由，支持命名参数 `{id}`、尾斜杠可选
- `index.php` 提供回退路由分发；`_router.php` 支持 PHP 内置开发服务器
- Nginx/Apache 需配置 `try_files` / `RewriteRule` 将未命中请求转发至 `index.php`
- 示例：`home_demo`（首页接管 + 通用路由）

**支付插件系统（P3 重构）**

- 支付架构插件化：易支付、支付宝官方均改为独立插件（`app_plugins/epay/`、`app_plugins/alipay_official/`）
- 新增 API：`mnbt_register_payment`、`mnbt_pay_dispatch_gateway`、`mnbt_pay_settle_order`、`mnbt_get_enabled_payment_methods`、`mnbt_save_payment_methods`
- 新增统一支付设置页 `admin/pay_settings.php`：仅管理启用/禁用、显示名、图标、排序
- 支付插件 API 凭证由插件自身设置页维护（`MN_plugin_option`），与系统层解耦
- 客户端 `user/pay.php` 重写为插件分发；模板 `webgl.php`、`set.php` 动态渲染付款方式
- 异步/同步回调改由 P2 通用路由处理：`/pay/{slug}/notify`、`/pay/{slug}/return`
- 易支付插件支持自动迁移旧 `MN_config.hxe/hxr/hxt` 配置
- 旧文件清理：`user/notify_url.php`、`user/return_url.php`、`MPHX/lib/submit.class.php`、`notify.class.php`、`core.function.php`、`md5.function.php`
- 升级 SQL：`update/update_v181_p3_pay.sql`（新增 `MN_config.pay_methods` 字段）

**安装向导增强**

- 新增「站点与管理员」步骤：控制面板名称、站长 QQ、公告、管理员账号/密码
- 安装完成写入 `MN_config`；完成页展示登录信息（不再固定 admin/123456）

**文档**

- 插件开发手册：[app_plugins/PLUGIN_DEV.md](app_plugins/PLUGIN_DEV.md)
- 仓库 README 增加 [插件开发](#插件开发) 入口

### V1.80

**前端主题系统**

- 用户端 / 管理端视图迁入 `templates/`，支持独立切换与缺页回退
- 主题引擎 `MPHX/theme.php`（`mnbt_render` / `mnbt_theme_url` / `mnbt_theme_asset` / `mnbt_asset_url`）
- 后台「系统管理 → 前端模板」可视化切换；配置文件 `active_user_theme` / `active_admin_theme`
- 主题资源隔离：公共资源 `imsetes/` 与主题私有 `templates/*/assets/`（缺文件回退 default）
- 主题文档：`templates/README.md`、`templates/THEME_DEV.md`

**清凉云主题（qingliangyun）**

- Vue 3 + Element Plus + Vite SPA（用户端）
- 白绿配色、圆角响应式布局；仪表盘圆形进度 + 月度流量趋势图（柱+折线）
- 业务页原生化：设置（PHP/域名/SSL/Gzip/缓存/防盗链等）、监控、通知、统计、备份、一键部署
- SPA 数据接口 `user/api/panel.php`（列表与 `set_init` 等）
- 构建产物随仓库提交（`templates/qingliangyun/user/dist/`）；开发说明见主题 README

**UI 与体验**

- 默认主题登录页改为简洁圆角白卡片（用户端 + 管理端）
- 管理端系统设置页改为现代卡片布局

**修复**

- 安装 SQL 跳过空语句，避免 `Query was empty`
- 用户端刷新用量 `sxsyxx` 错误 include 路径导致 500
- 清凉云设置页误报「操作失败」（纯数据接口响应解析）
- 默认文档读取误用 SetIndex 导致「默认文档不能为空」

### V1.79

**PHP 8.x 全兼容**
- 修复 `each()`、`count($string)`、`get_magic_quotes_gpc()`、`strftime` 等全部 PHP 8 废弃语法
- `var` 属性声明改为 `public`，移除 PHP 4 构造器
- 安装向导 PHP 版本检查放宽为 `>= 7.4.0`

**宝塔 API 重构**
- 合并 4 份重复的 bt_api 操作类（`bt_api` / `bt_api_set` / `win_bt_api` / `bt_api_rj`）到统一 `MPHX/bt_api.php`
- 修复 `stopjq()`、`urllist()` 命名冲突，添加向后兼容别名
- 新增 Gzip API：`get_gzip_status()` / `set_gzip()` / `remove_gzip_status()`
- 新增静态缓存 API：`get_static_cache()` / `set_static_cache()` / `remove_static_cache()`

**SQL 安全**
- DB 类新增 `prepare()` / `get_row_prepare()` / `get_all_prepare()` / `query_prepare()` / `count_prepare()`（MySQLi + SQLite PDO）
- 全部约 150 处 SQL 查询迁移至参数化查询，彻底消除 SQL 注入

**代码架构优化**
- `admin/ajax.php`（1106 行）拆分为 20 行路由 + 10 个模块文件（`admin/api/`）
- `user/ajax.php`（1209 行）拆分为 35 行路由 + 11 个模块文件（`user/api/`）
- 创建 `MPHX/Response.php` 统一响应类 + `MPHX/function.php`（`json_exit` 系列函数）
- 替换所有 `exit('{"code":...}')` 为统一响应函数

**操作日志系统**
- 修复 `logjl()` 函数中 `$DB` → `$DBZHER` 参数引用
- 启用管理端 19 处原被注释的日志调用
- 新增强制 HTTPS/重置密码/密码访问/SSL/邮箱绑定等 26 处用户端日志
- 管理后台日志查看页 `admin/list.php?gn=log`，支持搜索/分页/清空

**PHPMailer 升级**
- PHPMailer 5.2.28 → 6.12.0（Composer，`vendor-dir` → `mail/vendor`）
- 重写 `mail.php` / `admin/mail.php`，改用 `use PHPMailer\PHPMailer\PHPMailer`，try/catch 异常处理，UTF-8

**用户端新增功能**
- Gzip 配置页面（`user/set.php?gn=gzip`）：开关/压缩级别/最小长度/MIME 类型
- 缓存配置页面（`user/set.php?gn=cache`）：文件后缀/过期时间（秒/分钟/小时/天）
- URL 监控 + 资源监控（`user/monitor.php`）：状态码规则/内容匹配/SSRF 防护/失败计数
- 监控检测日志（`user/monitor_log.php`）
- 通知日志（`user/notice.php`）：到期提醒/流量超额/监控告警，筛选/搜索/分页/全部已读
- 功能菜单重排（`user/sy.php`）：Gzip/缓存移到防盗链后方，修复合提前闭合
- 流量趋势图升级：标题栏环比百分比，柱状叠加紫色折线，图例顶部显示
- 一键部署修复：`qk` 多值兼容、空数据提示、JS `==` 赋值 bug

**修复列表**
- `foreach(null)` / `json_decode(null)` 空保护
- `addzj` INSERT NOT NULL 约束（`$aedfs`/`$sqlfs` 默认 `'0'`）
- `gglist` 双重输出（`return` → `exit()`，`send_post('null')` → `send_post([])`）
- 数据库/FTP 账号重复检测改为本地查 `MN_zj` 表
- 用户面板 Chart.js 自适应（`maintainAspectRatio`）
- `send_post()` 兼容 PHP 8 `CURLOPT_POSTFIELDS` 数组 + `http_build_query`

**MNBT 节点插件系统**
- 插件注册/心跳/异步任务队列
- 违禁词扫描（定时全量 + 增量）
- `plugins/mnbt_connector/` 插件包

### V1.78

- 新增域名监控/文件监控功能
- 新增邮箱绑定与通知
- 新增负载均衡配置页面（开发中）

### V1.70

- 一键部署引擎全面升级（10 种自定义操作）
- 支持分片上传大文件
- 新增 SSL 证书自动申请

### V1.60

- 首个公开版本
- 基础主机分销功能完成
- 对接宝塔面板 API
- 集成易支付接口

---

## 许可证

本项目采用**宽松许可证**，版权归 [梦奈云](https://github.com/mengnai) 所有。

✅ **允许**：
- ✅ 商业用途
- ✅ 二次开发
- ✅ 分发传播

⚠️ **要求**：
- 保留原作者版权声明
- 修改后的文件需注明修改内容

### 第三方组件许可

| 组件 | 许可协议 |
|------|---------|
| [Light Year Admin v5](https://gitee.com/yinqi/Light-Year-Admin-Template-v5) | MIT |
| [Bootstrap 4](https://getbootstrap.com/) | MIT |
| [Chart.js](https://www.chartjs.org/) | MIT |
| [CodeMirror](https://codemirror.net/) | MIT |
| [FullCalendar](https://fullcalendar.io/) | MIT |
| [PHPMailer](https://github.com/PHPMailer/PHPMailer) | LGPL 2.1 |
| [Bootstrap Table](http://bootstrap-table.wenzhixin.net.cn/) | MIT |

---

## 联系方式

- **官方 QQ 群**：994752422
- **技术支持**：1181469655@qq.com
- **商务合作**：1181469655@qq.com
- **问题反馈**：[GitHub Issues](https://github.com/mengnai/mnbt/issues)

---

<div align="center">

**MNBT** © 2022-2026 梦奈云 版权所有

Made with ❤️ by [梦奈云](https://github.com/mengnai)

</div>
