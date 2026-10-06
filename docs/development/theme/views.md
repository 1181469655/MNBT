---
title: 视图清单
description: 主题开发必选/可选视图清单:用户端、管理端、Docker 控制台三套 scope 与不走主题的路径
---

# 必选 / 可选视图清单

本文是 [主题开发手册](./guide.md) §3 的完整视图清单,共四套 scope 与"不走主题的路径"说明。核心原则:**不提供的文件会回退 `default`,不必一次抄全**。

## 3.1 用户端 `templates/{theme}/user/`

| 视图文件 | 说明 | 建议 |
|----------|------|------|
| `head.php` | 公共 `<head>` + 公共 CSS/JS | 改整体风格必改 |
| `login.php` | 登录页 | 强烈建议覆盖 |
| `index.php` | 框架壳(侧栏 + 多标签 iframe) | 强烈建议覆盖 |
| `sy.php` | 仪表盘 | 建议 |
| `set.php` | 站点设置(PHP/SSL/Gzip 等) | 建议 |
| `site_stats.php` | 站点统计 | 可选 |
| `monitor.php` | 监控任务 | 可选 |
| `monitor_log.php` | 监控日志 | 可选 |
| `notice.php` | 通知日志 | 可选 |
| `webgl.php` | 一键部署 | 可选 |
| `sqlgl.php` | 数据库备份 | 可选 |
| `ftp.php` | 在线文件管理 | 复杂,可不覆盖 |

## 3.2 管理端 `templates/{theme}/admin/`

| 视图文件 | 说明 | 建议 |
|----------|------|------|
| `head.php` | 公共头 | 改整体风格必改 |
| `login.php` | 后台登录 | 强烈建议 |
| `index.php` | 后台框架壳 | 强烈建议 |
| `sy.php` | 仪表盘 | 建议 |
| `set.php` | 系统设置(含前端模板页) | 建议 |
| `list.php` | 列表(宝塔/主机/域名/日志等) | 可选(体积大) |
| `add.php` | 添加页 | 可选 |
| `node.php` | 节点管理 | 可选 |
| `tutorial.php` | 教程与监控说明 | 可选 |
| `update.php` | 系统更新 | 可选 |

## 3.3 Docker 控制台 `templates/{theme}/docker/`(V1.83 新增)

Docker 控制台是独立于用户端/管理端的第三套视图体系,有独立的认证机制(`docker_token` cookie)。

tdesign 主题下 Docker 端与用户端/管理端一致:`login.php`、`console.php`、`appstore.php`、`proxy.php`、`image.php`、`volume.php`、`compose.php` 均为 SPA 路由入口页,由 `_spa_boot.php` 注入 `window.__TD_BOOT__` 后经 vue3-sfc-loader 挂载 `spa/src/docker/` 下的 Vue 页面。

**静态资源**:`templates/{theme}/docker/assets/`,使用 `mnbt_theme_asset('xxx', 'docker')` 引用(如 `captcha-images/` 滑块验证码图集)。

**主题 scope 注册**:`docker` scope 在 `MPHX/theme.php` 中注册,与 `user`/`admin` 独立。`theme.json` 可声明 `"scope": ["user", "admin", "docker"]`。

## 3.5 主页（V1.88 起不属于主题）

> 独立主页系统（V1.84 引入的第四个主题 scope）已于 V1.88 移回 `official_site` 插件：
> 入口模板在 `app_plugins/official_site/views/tdesign/home.php`，SPA 源码在
> `app_plugins/official_site/assets/spa/`，主页设置在后台「官网内容 → 主页设置」。
> 主题不再包含 `home/` 目录，`mnbt_theme_name()` 等也不再接受 `home` 作用域。
> 历史说明见本文档旧版或 [独立主页系统 PRD](../../prd/home.md)。

