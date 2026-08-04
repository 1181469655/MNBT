# tdesign 主题改造计划：支持用户端 + 管理端

## 目标

将 `tdesign-admin` 主题改造为 `tdesign`，同时支持用户端（user）和管理端（admin），两端均采用 Vue 3 + TDesign Vue Next SPA 架构，覆盖全部页面。

---

## 一、目录重命名与结构变更

### 1.1 重命名主题目录
```
templates/tdesign-admin/  →  templates/tdesign/
```

### 1.2 新增 user 目录
```
templates/tdesign/
├── theme.json              # scope 改为 ["user", "admin"]
├── theme.php               # 新增：注册 user/admin 菜单渲染器
├── admin/                  # 现有管理端（不变）
│   ├── _spa_boot.php
│   ├── dist/
│   ├── index.php
│   ├── login.php
│   ├── set.php
│   ├── list.php
│   ├── add.php
│   ├── node.php
│   ├── pay_settings.php
│   ├── plugin_manage.php
│   ├── sy.php
│   ├── tutorial.php
│   └── update.php
├── user/                   # 新增：用户端
│   ├── _spa_boot.php       # 用户端 SPA 启动片段
│   ├── dist/               # 用户端构建产物
│   │   └── assets/
│   ├── index.php           # 框架壳入口
│   ├── login.php           # 登录入口
│   ├── sy.php              # 仪表盘入口
│   ├── site_stats.php      # 站点统计入口
│   ├── set.php             # 设置页入口（所有 gn 统一入口）
│   ├── monitor.php         # 监控任务入口
│   ├── monitor_log.php     # 监控日志入口
│   ├── notice.php          # 通知日志入口
│   ├── webgl.php           # 一键部署入口
│   ├── sqlgl.php           # SQL备份入口
│   └── ftp.php             # 文件管理入口（iframe 嵌入 amftp）
└── spa/                    # SPA 源码（双入口）
    ├── admin.html          # 管理端入口 HTML
    ├── user.html           # 用户端入口 HTML（新增）
    ├── package.json
    ├── vite.config.js      # 改为多入口构建
    └── src/
        ├── main-admin.js   # 管理端入口（原 main.js）
        ├── main-user.js    # 用户端入口（新增）
        ├── App-admin.vue   # 管理端根组件
        ├── App-user.vue    # 用户端根组件
        ├── router/
        │   ├── admin.js    # 管理端路由（原 index.js）
        │   └── user.js     # 用户端路由（新增）
        ├── layouts/
        │   ├── AdminLayout.vue   # 现有
        │   └── UserLayout.vue     # 新增：用户端布局
        ├── api/            # 共享 http.js，各端独立 api 模块
        │   ├── http.js     # 共享
        │   ├── auth.js     # 管理端认证
        │   ├── user-auth.js # 用户端认证
        │   ├── user-site.js # 用户端站点设置 API
        │   ├── user-stats.js # 站点统计 API
        │   ├── user-monitor.js # 监控 API
        │   ├── user-deploy.js # 部署 API
        │   ├── user-database.js # 数据库 API
        │   ├── user-file.js # 文件管理 API
        │   └── ...现有 admin api 模块
        ├── views/
        │   ├── ...现有 admin 视图
        │   └── user/       # 新增：用户端视图
        │       ├── UserLoginView.vue
        │       ├── UserDashboardView.vue
        │       ├── UserSiteStatsView.vue
        │       ├── UserSettingsView.vue     # 统一设置页（按 gn 切换面板）
        │       ├── UserMonitorView.vue
        │       ├── UserMonitorLogView.vue
        │       ├── UserNoticeView.vue
        │       ├── UserDeployView.vue
        │       ├── UserSqlBackupView.vue
        │       └── UserFtpView.vue          # iframe 嵌入 amftp
        └── styles/
            └── theme.scss   # 共享样式
```

### 1.3 更新激活文件
- 检查 `templates/active_admin_theme` 内容，若为 `tdesign-admin` 则改为 `tdesign`
- 检查 `templates/active_user_theme`，若需启用则设为 `tdesign`
- 检查数据库 `MN_config` 表的 `admintheme` 字段（若存在）

---

## 二、SPA 构建配置改造

### 2.1 vite.config.js 改为多入口

```js
export default defineConfig({
  plugins: [vue()],
  base: './',
  resolve: { alias: { '@': resolve(__dirname, 'src') } },
  build: {
    emptyOutDir: false, // 不清空，两次构建分别输出到 admin/dist 和 user/dist
    rollupOptions: {
      // 通过 --mode 或 input 参数区分
    },
  },
})
```

### 2.2 构建脚本

`package.json` 的 scripts：
```json
{
  "scripts": {
    "build:admin": "vite build --config vite.admin.config.js",
    "build:user": "vite build --config vite.user.config.js",
    "build": "npm run build:admin && npm run build:user",
    "dev:admin": "vite --config vite.admin.config.js",
    "dev:user": "vite --config vite.user.config.js"
  }
}
```

或采用单 vite.config.js + mode 切换，两个配置文件分别指定 input 和 outDir。

### 2.3 构建产物
- 管理端：`admin/dist/assets/index.js` + `admin/dist/assets/index.css`
- 用户端：`user/dist/assets/index.js` + `user/dist/assets/index.css`

---

## 三、PHP 入口文件

### 3.1 用户端 _spa_boot.php

仿照 admin/_spa_boot.php，注入 `window.__TD_BOOT__`，但使用用户端变量：

```php
$boot = [
    'siteName'    => $conf['name'] ?? 'MNBT',
    'footer'      => $conf['hxp'] ?? '',
    'loggedIn'    => isset($islogins) && (int)$islogins === 1,
    'needCaptcha' => isset($conf['yzm']) && $conf['yzm'] === 'true',
    'ajaxBase'    => './ajax.php',
    'codeUrl'     => './code.php',
    'entry'       => $td_entry ?? 'dashboard',
    'user'        => $user ?? '',
    'zjid'        => $zjid ?? 0,
    'ssbt'        => $ssbt ?? '',
    'yhc'         => $yhc ?? [],
    'pluginMenuHtml' => _tdboot_render_plugin_menu_html(..., 'user'),
    // ...
];
```

### 3.2 用户端各 PHP 入口

所有用户端控制器页面（sy.php, set.php, site_stats.php 等）的主题模板都改为：
```php
<?php
$td_entry = 'dashboard'; // 或 'settings', 'stats' 等
$td_hash = '#/settings/php'; // 根据 $_GET['gn'] 映射到 SPA 路由
include __DIR__ . '/_spa_boot.php';
```

### 3.3 gn 到 SPA 路由的映射

用户端 set.php 的 gn 参数映射：
| gn | SPA 路由 |
|----|----------|
| php | #/settings/php |
| url | #/settings/domain |
| pass | #/settings/pass |
| mrwd | #/settings/default-doc |
| yxml | #/settings/run-dir |
| wjt | #/settings/rewrite |
| ssl | #/settings/ssl |
| fdl | #/settings/hotlink |
| gzip | #/settings/gzip |
| cache | #/settings/cache |
| xgpass | #/settings/password |
| mysqlcz | #/settings/sql-auth |

---

## 四、用户端 SPA 路由

```js
// router/user.js
const routes = [
  { path: '/login', component: UserLoginView, meta: { guest: true } },
  {
    path: '/',
    component: UserLayout,
    meta: { auth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', component: UserDashboardView },
      { path: 'stats', component: UserSiteStatsView },
      { path: 'settings/:tab', component: UserSettingsView },
      { path: 'monitor', component: UserMonitorView },
      { path: 'monitor-log', component: UserMonitorLogView },
      { path: 'notice', component: UserNoticeView },
      { path: 'deploy', component: UserDeployView },
      { path: 'sql-backup', component: UserSqlBackupView },
      { path: 'ftp', component: UserFtpView },
    ],
  },
]
```

---

## 五、用户端布局 UserLayout.vue

### 5.1 侧边栏菜单结构

```
概览
  └─ 控制面板 (/dashboard)
  └─ 站点统计 (/stats)

基本配置
  ├─ PHP版本切换 (/settings/php)
  ├─ 域名修改 (/settings/domain)
  ├─ 密码访问 (/settings/pass)
  ├─ 默认文档 (/settings/default-doc)
  ├─ 运行目录 (/settings/run-dir)
  ├─ 伪静态 (/settings/rewrite)
  ├─ SSL配置 (/settings/ssl)
  ├─ 防盗链 (/settings/hotlink)
  ├─ Gzip配置 (/settings/gzip)
  ├─ 缓存配置 (/settings/cache)
  └─ 修改密码 (/settings/password)

数据管理
  ├─ 在线文件管理 (/ftp)
  ├─ SQL管理面板 (新窗口 /mysql.php)
  ├─ SQL数据备份 (/sql-backup)
  └─ SQL权限设置 (/settings/sql-auth)

网站管理
  ├─ 一键部署 (/deploy)
  ├─ 监控任务 (/monitor)
  └─ 通知日志 (/notice)

插件菜单（动态注入）
```

### 5.2 顶部工具栏
- 侧栏开关
- 刷新按钮
- 用户菜单（用户名、退出登录）

---

## 六、用户端视图组件

### 6.1 UserLoginView.vue
- 仿 LoginView.vue，使用用户端登录 API
- 动态背景效果复用

### 6.2 UserDashboardView.vue
- 网页空间使用情况（进度条）
- 数据库空间使用情况
- 流量使用情况
- 站点基本信息
- 从 boot.yhc 获取数据

### 6.3 UserSiteStatsView.vue
- 访问统计图表（Chart.js 或 ECharts）
- 流量统计
- 请求数统计

### 6.4 UserSettingsView.vue
- 统一设置页，通过 `:tab` 参数切换面板
- 每个面板对应一个 gn：
  - PHP 版本切换
  - 域名修改
  - 密码访问设置
  - 默认文档修改
  - 运行目录设置
  - 伪静态设置
  - SSL 配置
  - 防盗链
  - Gzip 配置
  - 缓存配置
  - 修改密码
  - SQL 权限设置

### 6.5 UserMonitorView.vue
- 监控任务列表
- 添加/编辑/删除任务

### 6.6 UserMonitorLogView.vue
- 监控日志分页表格

### 6.7 UserNoticeView.vue
- 通知日志分页表格

### 6.8 UserDeployView.vue
- 一键部署程序列表
- 部署操作

### 6.9 UserSqlBackupView.vue
- 备份列表
- 创建备份/恢复/删除/下载

### 6.10 UserFtpView.vue
- **iframe 嵌入** amftp 文件管理器
- 不用 SPA 重写（太复杂），直接 iframe 加载 `./ftp.php` 的原始 amftp 界面
- 加载时显示 loading 占位

---

## 七、用户端 API 模块

### 7.1 共享 http.js
复用现有 `api/http.js`（postGn / parseResult / apiGn），ajaxBase 从 boot 读取。

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

新增 `templates/tdesign/theme.php`，注册 user 和 admin 两个 scope 的菜单渲染器：

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

## 九、实施步骤（按顺序）

### 阶段 1：目录重命名与基础结构
1. 重命名 `templates/tdesign-admin/` → `templates/tdesign/`
2. 更新 theme.json（name、scope）
3. 创建 `user/` 目录
4. 更新 active_admin_theme 激活文件

### 阶段 2：SPA 构建配置
5. 创建 `spa/admin.html` 和 `spa/user.html`
6. 重命名 `main.js` → `main-admin.js`，创建 `main-user.js`
7. 拆分 `App.vue` → `App-admin.vue` + `App-user.vue`
8. 拆分 `router/index.js` → `router/admin.js` + `router/user.js`
9. 修改 vite.config.js 为多入口（或两个配置文件）
10. 验证管理端构建正常（npm run build:admin）

### 阶段 3：用户端 PHP 入口
11. 创建 `user/_spa_boot.php`
12. 创建 `user/index.php`、`user/login.php`、`user/sy.php`
13. 创建 `user/set.php`（gn 到 hash 路由映射）
14. 创建其他用户端 PHP 入口（site_stats, monitor, notice, webgl, sqlgl, ftp）
15. 创建 `theme.php`

### 阶段 4：用户端布局与登录
16. 创建 `UserLayout.vue`（侧边栏 + 顶栏）
17. 创建 `UserLoginView.vue`
18. 创建用户端 API 模块（user-auth.js）
19. 验证用户端登录流程

### 阶段 5：用户端核心页面
20. UserDashboardView.vue（仪表盘）
21. UserSiteStatsView.vue（站点统计）
22. UserSettingsView.vue（设置页，含 12 个 gn 面板）
23. UserMonitorView.vue（监控任务）
24. UserMonitorLogView.vue（监控日志）
25. UserNoticeView.vue（通知日志）

### 阶段 6：用户端数据管理页面
26. UserSqlBackupView.vue（SQL 备份）
27. UserDeployView.vue（一键部署）
28. UserFtpView.vue（iframe 嵌入 amftp）

### 阶段 7：构建与测试
29. 完整构建（npm run build）
30. 测试用户端所有页面
31. 测试管理端所有页面（确认改造无回归）
32. 测试插件菜单在两端正常显示

---

## 十、风险与注意事项

1. **ftp.php 不重写**：amftp 是独立的 PHP 文件管理系统，包含大量 JS/CSS，SPA 重写成本极高。采用 iframe 嵌入方案，保持原始功能。

2. **用户端 AJAX 接口**：user/ajax.php 的 gn 分发逻辑与管理端不同，需要逐个 api/*.php 文件确认参数格式。部分接口可能返回非标准格式，http.js 的 parseResult 已兼容多种格式。

3. **插件菜单**：用户端插件菜单通过 `mnbt_plugin_render_menu_user_html()` 渲染，需要在 _spa_boot.php 中用 tdesign 侧栏 HTML 结构输出。

4. **回退兼容**：如果用户端某个页面暂时未完成，可让对应 PHP 入口不创建文件，自动回退到 default 主题。但需要确保 head.php 同时加载 Bootstrap（回退页依赖）。由于我们采用 SPA 全覆盖，不需要 head.php 回退兼容。

5. **SQL管理面板**：mysql.php 是独立跳转到 phpMyAdmin，不在 SPA 内，保持 `target="_blank"` 新窗口打开。

6. **数据库字段**：重命名主题目录后，若 `MN_config.admintheme` 字段存储的是 `tdesign-admin`，需更新为 `tdesign`。可通过后台重新切换主题触发，或直接 SQL 更新。

---

## 十一、预期产出

- `templates/tdesign/` 主题目录，支持 user + admin 双端
- 用户端 SPA 覆盖全部 12 个页面
- 管理端 SPA 保持现有功能不变
- 一条 `npm run build` 命令同时构建两端产物
- theme.json scope 为 ["user", "admin"]
