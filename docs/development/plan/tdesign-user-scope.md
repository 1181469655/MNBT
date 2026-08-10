---
title: tdesign 双端改造计划
description: tdesign 主题用户端+管理端改造历史计划:目录结构、构建配置、PHP 入口、路由与布局(上篇)
---

# tdesign 主题改造计划:支持用户端 + 管理端

> 历史计划文档,保留原结构。本文为上篇,覆盖计划 §1 目录结构至 §6 用户端视图组件;下篇(§7 用户端 API 模块至 §11 预期产出)见 [tdesign-user-scope-2.md](./tdesign-user-scope-2.md)。

## 目标

将 `tdesign-admin` 主题改造为 `tdesign`,同时支持用户端(user)和管理端(admin),两端均采用 Vue 3 + TDesign Vue Next SPA 架构,覆盖全部页面。

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
├── theme.php               # 新增:注册 user/admin 菜单渲染器
├── admin/                  # 现有管理端(不变)
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
├── user/                   # 新增:用户端
│   ├── _spa_boot.php       # 用户端 SPA 启动片段
│   ├── dist/               # 用户端构建产物
│   │   └── assets/
│   ├── index.php           # 框架壳入口
│   ├── login.php           # 登录入口
│   ├── sy.php              # 仪表盘入口
│   ├── site_stats.php      # 站点统计入口
│   ├── set.php             # 设置页入口(所有 gn 统一入口)
│   ├── monitor.php         # 监控任务入口
│   ├── monitor_log.php     # 监控日志入口
│   ├── notice.php          # 通知日志入口
│   ├── webgl.php           # 一键部署入口
│   ├── sqlgl.php           # SQL备份入口
│   └── ftp.php             # 文件管理入口(iframe 嵌入 amftp)
└── spa/                    # SPA 源码(双入口)
    ├── admin.html          # 管理端入口 HTML
    ├── user.html           # 用户端入口 HTML(新增)
    ├── package.json
    ├── vite.config.js      # 改为多入口构建
    └── src/
        ├── main-admin.js   # 管理端入口(原 main.js)
        ├── main-user.js    # 用户端入口(新增)
        ├── App-admin.vue   # 管理端根组件
        ├── App-user.vue    # 用户端根组件
        ├── router/
        │   ├── admin.js    # 管理端路由(原 index.js)
        │   └── user.js     # 用户端路由(新增)
        ├── layouts/
        │   ├── AdminLayout.vue   # 现有
        │   └── UserLayout.vue     # 新增:用户端布局
        ├── api/            # 共享 http.js,各端独立 api 模块
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
        │   └── user/       # 新增:用户端视图
        │       ├── UserLoginView.vue
        │       ├── UserDashboardView.vue
        │       ├── UserSiteStatsView.vue
        │       ├── UserSettingsView.vue     # 统一设置页(按 gn 切换面板)
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
- 检查 `templates/active_admin_theme` 内容,若为 `tdesign-admin` 则改为 `tdesign`
- 检查 `templates/active_user_theme`,若需启用则设为 `tdesign`
- 检查数据库 `MN_config` 表的 `admintheme` 字段(若存在)

---

## 二、SPA 构建配置改造

### 2.1 vite.config.js 改为多入口

```js
export default defineConfig({
  plugins: [vue()],
  base: './',
  resolve: { alias: { '@': resolve(__dirname, 'src') } },
  build: {
    emptyOutDir: false, // 不清空,两次构建分别输出到 admin/dist 和 user/dist
    rollupOptions: {
      // 通过 --mode 或 input 参数区分
    },
  },
})
```

### 2.2 构建脚本

`package.json` 的 scripts:
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

或采用单 vite.config.js + mode 切换,两个配置文件分别指定 input 和 outDir。

### 2.3 构建产物
- 管理端:`admin/dist/assets/index.js` + `admin/dist/assets/index.css`
- 用户端:`user/dist/assets/index.js` + `user/dist/assets/index.css`

---

## 三、PHP 入口文件

### 3.1 用户端 _spa_boot.php

仿照 admin/_spa_boot.php,注入 `window.__TD_BOOT__`,但使用用户端变量:

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

所有用户端控制器页面(sy.php, set.php, site_stats.php 等)的主题模板都改为:
```php
<?php
$td_entry = 'dashboard'; // 或 'settings', 'stats' 等
$td_hash = '#/settings/php'; // 根据 $_GET['gn'] 映射到 SPA 路由
include __DIR__ . '/_spa_boot.php';
```

### 3.3 gn 到 SPA 路由的映射

用户端 set.php 的 gn 参数映射:
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

插件菜单(动态注入)
```

### 5.2 顶部工具栏
- 侧栏开关
- 刷新按钮
- 用户菜单(用户名、退出登录)

---

## 六、用户端视图组件

### 6.1 UserLoginView.vue
- 仿 LoginView.vue,使用用户端登录 API
- 动态背景效果复用

### 6.2 UserDashboardView.vue
- 网页空间使用情况(进度条)
- 数据库空间使用情况
- 流量使用情况
- 站点基本信息
- 从 boot.yhc 获取数据

### 6.3 UserSiteStatsView.vue
- 访问统计图表(Chart.js 或 ECharts)
- 流量统计
- 请求数统计

### 6.4 UserSettingsView.vue
- 统一设置页,通过 `:tab` 参数切换面板
- 每个面板对应一个 gn:
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
- 不用 SPA 重写(太复杂),直接 iframe 加载 `./ftp.php` 的原始 amftp 界面
- 加载时显示 loading 占位
