---
title: 目录结构
description: MNBT 源码目录结构说明
---

# 目录结构

MNBT 采用模块化目录组织：`admin/`（管理后台）、`user/`（用户控制面板）、`MPHX/`（核心框架）、`templates/`（前端主题）、`app_plugins/`（PHP 业务插件）、`api/`（外部 API 接口）等。

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
│   │   └── other.php         # 其他功能（重置密码/邮箱绑定）
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

> 说明：上图为源码目录结构描述，其中提到的 `README.md`、`THEME_DEV.md`、`PLUGIN_DEV.md` 等文件位于对应源码目录内，主题开发与插件开发文档已迁移至本文档站，可分别在「主题开发」与「插件开发」章节查阅。
