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

| 视图文件 | 说明 | 建议 |
|----------|------|------|
| `head.php` | 公共引导文件(主题配置、节点信息、用户信息、导航菜单) | 改整体风格必改 |
| `foot.php` | 公共尾部(Toast/Modal 容器、HTML 文档闭合) | 改整体风格必改 |
| `login.php` | Docker 登录页 | 强烈建议覆盖 |
| `console.php` | 我的容器(单容器详情页,含磁盘用量/配额显示) | 建议 |
| `appstore.php` | 应用商店(含配额展示条:CPU/内存/磁盘/代理数) | 建议 |
| `proxy.php` | 反向代理管理(列表/添加/删除,端口从容器端口选择,IP 锁定 127.0.0.1) | 建议 |
| `image.php` | 镜像管理 | 可选 |
| `volume.php` | 数据卷管理 | 可选 |
| `compose.php` | Compose 模板 | 可选 |

**静态资源**:`templates/{theme}/docker/assets/`(CSS、JS、图片、SVG),使用 `mnbt_theme_asset('docker.css', 'docker')` 引用。

**Docker 视图依赖的公共 JS 函数**(在 `head.php` 中加载):

| 函数 | 用途 |
|------|------|
| `dkAjax(gn, data, opts)` | Docker AJAX 请求(自动附带 CSRF token) |
| `dkToast(msg, type)` | 消息提示(`info` / `success` / `error`) |
| `dkModal(html, title)` | 模态框(html 为内容,title 为标题) |
| `dkCloseModal()` | 关闭模态框 |
| `dockerLogout()` | 退出登录 |
| `dkContainerOp(gn)` | 容器操作(`container_start` / `container_stop` / `container_restart`) |
| `dkContainerRemove()` | 删除容器(带确认弹窗,调用 `remove_app` 卸载) |
| `dkProxyAdd()` | 打开反向代理添加弹窗(先加载容器端口列表) |
| `dkProxyDel(id, name)` | 删除反向代理规则 |

**Docker 视图可用的 PHP 变量**(由控制器传入):

| 变量 | 说明 |
|------|------|
| `$me` | 当前 Docker 用户(`MN_docker_user` 行) |
| `$plan` | 用户套餐(`MN_docker_plan` 行,含 `cpu_max`/`mem_max`/`disk_max`/`proxy_max`) |
| `$node` | 所属节点信息(`MN_docker_node` 行) |
| `$title` | 页面标题 |
| `$active` | 当前激活的导航项(`console`/`appstore`/`proxy`/`image`/`volume`/`compose`) |

**Docker 关键 CSS 类**:

| 类名 | 用途 |
|------|------|
| `.dk-card` / `.dk-card-head` / `.dk-card-body` | 卡片容器 |
| `.dk-btn` / `.dk-btn-sm` / `.dk-btn-primary` / `.dk-btn-danger` / `.dk-btn-warning` / `.dk-btn-success` / `.dk-btn-ghost` / `.dk-btn-block` | 按钮 |
| `.dk-tag` / `.dk-tag-running` / `.dk-tag-stopped` / `.dk-tag-creating` / `.dk-tag-none` / `.dk-tag-expired` / `.dk-tag-paused` | 状态标签 |
| `.dk-quota-bar` / `.dk-quota-item` / `.dk-quota-label` / `.dk-quota-val` | 安装弹窗中的配额展示条(蓝色底,三列居中) |
| `.dk-alert` / `.dk-alert-info` / `.dk-alert-warn` / `.dk-alert-danger` | 提示条 |
| `.dk-form-grid` / `.dk-field` / `.dk-field-full` | 表单布局 |
| `.dk-metrics` / `.dk-metric` / `.dk-m-label` / `.dk-m-value` | 指标卡片(容器详情页) |
| `.dk-empty` / `.dk-empty-ico` | 空状态提示 |
| `.dk-spinner-overlay` / `.dk-spin` | 加载动画 |
| `.dk-app-card` / `.dk-app-head` / `.dk-app-icon` / `.dk-app-desc` | 应用卡片(应用商店列表) |
| `.dk-modal` / `.dk-modal-head` / `.dk-modal-body` / `.dk-modal-foot` / `.dk-modal-close` | 模态框 |
| `.dk-toast` | Toast 消息 |
| `.dk-sidebar` / `.dk-nav` / `.dk-nav-section` | 侧栏导航 |
| `.dk-topbar` / `.dk-badges` | 顶栏 |
| `.dk-mono` | 等宽字体 |

**Docker 控制器路径**:

| 控制器 | 路径 |
|--------|------|
| 页面入口 | `docker/console.php`、`docker/appstore.php` 等 |
| AJAX 后端 | `docker/ajax.php` |
| CSS 样式 | `templates/classic/docker/assets/docker.css` |

**主题 scope 注册**:`docker` scope 在 `MPHX/theme.php` 中注册,与 `user`/`admin` 独立。`theme.json` 可声明 `"scope": ["user", "admin", "docker"]`。

## 3.5 主页（V1.88 起不属于主题）

> 独立主页系统（V1.84 引入的第四个主题 scope）已于 V1.88 移回 `official_site` 插件：
> 入口模板在 `app_plugins/official_site/views/tdesign/home.php`，SPA 源码在
> `app_plugins/official_site/assets/spa/`，主页设置在后台「官网内容 → 主页设置」。
> 主题不再包含 `home/` 目录，`mnbt_theme_name()` 等也不再接受 `home` 作用域。
> 历史说明见本文档旧版或 [独立主页系统 PRD](../../prd/home.md)。

