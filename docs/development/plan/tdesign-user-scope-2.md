---
title: tdesign 双端改造计划(下)
description: tdesign 双端改造计划后半:用户端 API 模块、theme.php 菜单渲染器、实施步骤、风险与预期产出
---

# tdesign 双端改造计划:支持用户端 + 管理端(下)

> 本文是 [tdesign-user-scope.md](./tdesign-user-scope.md) 的续篇,覆盖原计划 §7 用户端 API 模块至 §11 预期产出。

---

## 七、用户端 API 模块

### 7.1 共享 http.js
复用现有 `api/http.js`(postGn / parseResult / apiGn),ajaxBase 从 boot 读取。

### 7.2 用户端 API 函数

```js
// api/user-auth.js
export function userLogin(user, pass, code) { return apiGn('login', { user, pass, code }) }
export function userLogout() { return apiGn('login', { logout: 'tclogin' }, { silent: true }) }

// api/user-site.js
export function getPhpVersions() { return apiGn('site', { act: 'php' }) }
export function setPhpVersion(ver) { return apiGn('site', { act: 'php', ver }) }
export function getDomain() { return apiGn('site', { act: 'url' }) }
export function setDomain(domain) { return apiGn('site', { act: 'url', domain }) }
// ... 其他 gn 操作

// api/user-stats.js
export function getSiteStats(range) { return apiGn('site_stats', { range }) }

// api/user-monitor.js
export function listMonitorTasks() { return apiGn('monitor', { act: 'list' }) }
export function addMonitorTask(data) { return apiGn('monitor', { act: 'add', ...data }) }

// api/user-deploy.js
export function listDeployPrograms() { return apiGn('deploy', { act: 'list' }) }

// api/user-database.js
export function listSqlBackup() { return apiGn('database', { act: 'backup_list' }) }
export function createBackup() { return apiGn('database', { act: 'backup' }) }
```

---

## 八、theme.php 菜单渲染器

新增 `templates/tdesign/theme.php`,注册 user 和 admin 两个 scope 的菜单渲染器:

```php
<?php
if (!defined('IN_CRONLITE')) exit;

mnbt_register_theme_menu_renderer('user', function ($items) {
    // 渲染为 tdesign 侧栏 HTML 结构
    // 与 _spa_boot.php 中的 _tdboot_render_plugin_menu_html 一致
});

mnbt_register_theme_menu_renderer('admin', function ($items) {
    // 同上
});
```

---

## 九、实施步骤(按顺序)

### 阶段 1:目录重命名与基础结构
1. 重命名 `templates/tdesign-admin/` → `templates/tdesign/`
2. 更新 theme.json(name、scope)
3. 创建 `user/` 目录
4. 更新 active_admin_theme 激活文件

### 阶段 2:SPA 构建配置
5. 创建 `spa/admin.html` 和 `spa/user.html`
6. 重命名 `main.js` → `main-admin.js`,创建 `main-user.js`
7. 拆分 `App.vue` → `App-admin.vue` + `App-user.vue`
8. 拆分 `router/index.js` → `router/admin.js` + `router/user.js`
9. 修改 vite.config.js 为多入口(或两个配置文件)
10. 验证管理端构建正常(npm run build:admin)

### 阶段 3:用户端 PHP 入口
11. 创建 `user/_spa_boot.php`
12. 创建 `user/index.php`、`user/login.php`、`user/sy.php`
13. 创建 `user/set.php`(gn 到 hash 路由映射)
14. 创建其他用户端 PHP 入口(site_stats, monitor, notice, webgl, sqlgl, ftp)
15. 创建 `theme.php`

### 阶段 4:用户端布局与登录
16. 创建 `UserLayout.vue`(侧边栏 + 顶栏)
17. 创建 `UserLoginView.vue`
18. 创建用户端 API 模块(user-auth.js)
19. 验证用户端登录流程

### 阶段 5:用户端核心页面
20. UserDashboardView.vue(仪表盘)
21. UserSiteStatsView.vue(站点统计)
22. UserSettingsView.vue(设置页,含 12 个 gn 面板)
23. UserMonitorView.vue(监控任务)
24. UserMonitorLogView.vue(监控日志)
25. UserNoticeView.vue(通知日志)

### 阶段 6:用户端数据管理页面
26. UserSqlBackupView.vue(SQL 备份)
27. UserDeployView.vue(一键部署)
28. UserFtpView.vue(iframe 嵌入 amftp)

### 阶段 7:构建与测试
29. 完整构建(npm run build)
30. 测试用户端所有页面
31. 测试管理端所有页面(确认改造无回归)
32. 测试插件菜单在两端正常显示

---

## 十、风险与注意事项

1. **ftp.php 不重写**:amftp 是独立的 PHP 文件管理系统,包含大量 JS/CSS,SPA 重写成本极高。采用 iframe 嵌入方案,保持原始功能。

2. **用户端 AJAX 接口**:user/ajax.php 的 gn 分发逻辑与管理端不同,需要逐个 api/*.php 文件确认参数格式。部分接口可能返回非标准格式,http.js 的 parseResult 已兼容多种格式。

3. **插件菜单**:用户端插件菜单通过 `mnbt_plugin_render_menu_user_html()` 渲染,需要在 _spa_boot.php 中用 tdesign 侧栏 HTML 结构输出。

4. **回退兼容**:如果用户端某个页面暂时未完成,可让对应 PHP 入口不创建文件,自动回退到 default 主题。但需要确保 head.php 同时加载 Bootstrap(回退页依赖)。由于我们采用 SPA 全覆盖,不需要 head.php 回退兼容。

5. **SQL管理面板**:mysql.php 是独立跳转到 phpMyAdmin,不在 SPA 内,保持 `target="_blank"` 新窗口打开。

6. **数据库字段**:重命名主题目录后,若 `MN_config.admintheme` 字段存储的是 `tdesign-admin`,需更新为 `tdesign`。可通过后台重新切换主题触发,或直接 SQL 更新。

---

## 十一、预期产出

- `templates/tdesign/` 主题目录,支持 user + admin 双端
- 用户端 SPA 覆盖全部 12 个页面
- 管理端 SPA 保持现有功能不变
- 一条 `npm run build` 命令同时构建两端产物
- theme.json scope 为 ["user", "admin"]
