# TDesign 双端主题（tdesign）v0.2.0

现代化 **双端** 主题：基于 TDesign 品牌蓝，覆盖**用户端 + 管理端**全部页面。卡片化布局、侧栏 + 顶栏、echarts 数据可视化、左侧背景图登录页。

技术栈：**Vue 3 + Vue Router (Hash) + TDesign Vue Next + Vite + ECharts**。

---

## 特性

### 用户端（user scope）

| 模块 | 说明 |
|------|------|
| 登录页 | 左侧背景图 + 右侧登录区,验证码支持,登录态自动跳转 |
| 控制台壳 | 侧栏 + 顶栏,折叠/移动端抽屉,退出登录确认 |
| 首页仪表盘 | 资源使用 echarts gauge 仪表盘 / 月度流量趋势柱状图+折线图 / 快捷操作平铺按钮 / 4 张站点信息卡片 |
| 站点设置 | PHP 版本 / 密码访问 / 默认文档 / 运行目录 / 伪静态 / SSL / 防盗链 / Gzip / 缓存 / 修改密码 / SQL 权限 |
| 文件管理 | iframe 嵌入默认主题 ftp.php(复用成熟的文件管理 UI) |
| SQL 备份 | 备份列表 / 立即备份 / 下载 / 恢复 / 删除 |
| 监控任务 | 任务列表 / 新增 / 编辑 / 删除 / 监控日志 |
| 站点统计 | 概览卡片 + 访问路径 / IP 排行 / 错误日志分类标签页 |
| 一键部署 | 部署程序列表 |
| 插件页面 | 通过 SPA 路由 + iframe 在 layout 内加载,不再新窗口打开 |
| 公告弹窗 | NoticeDialog 组件,sessionStorage 记忆已读 |
| 邮箱绑定 | MailBindDialog 组件,未绑定时强制弹出 |

### 管理端（admin scope）

| 模块 | 说明 |
|------|------|
| 登录页 | 左侧背景图 + 右侧登录区,验证码支持,登录态自动跳转 |
| 控制台壳 | 深色侧栏 + 顶栏,折叠/移动端抽屉,退出登录确认 |
| 仪表盘 | 公告 / 系统信息 / 检查更新(`sy.php` 注入 `$sy`) |
| 系统管理 | 网站设置 / 管理设置 / API / 邮箱 / 控制面板 / 监控 / 系统更新 / 操作日志 |
| 主机管理 | 主机列表(服务端分页) / 添加主机 / 批量删除 |
| 节点与宝塔 | 宝塔列表(通信检测 / PHP 版本管理) / 添加宝塔 / 节点列表 / 违禁词扫描 |
| 一键部署 | 订单列表 / 程序列表 / 添加程序 / 导入程序 |
| 支付设置 | 动态渲染支付插件及子付款方式,启用/显示名/图标/排序 |
| 插件管理 | 安装 / 启用 / 卸载,设置入口跳转 |
| 前端模板 | 用户端 / 管理端主题切换 |
| 教程 / 修复 / 更新 | 教程与监控、系统修复、系统更新 |
| 插件页面 | 通过 SPA 路由 + iframe 在 layout 内加载 |
| 路由 | Hash 模式,不改 PHP 控制器 URL |

---

## 目录结构

```
templates/tdesign/
├── theme.json                 # 主题元信息(scope: ["user", "admin"])
├── theme.php                  # 注册双端菜单渲染器(插件菜单 → 侧栏 HTML)
├── README.md                  # 本说明
├── PLAN_USER_SCOPE.md         # 双端改造计划文档
│
├── admin/                     # 管理端 PHP 主题入口
│   ├── _spa_boot.php          # 注入 window.__TD_BOOT__ + 加载 dist
│   ├── login.php              # 登录页入口
│   ├── index.php / sy.php     # 仪表盘入口(sy.php 注入 $sy)
│   ├── set.php                # 设置类页面(set.php?gn=xxx → SPA 路由)
│   ├── list.php               # 列表类页面(list.php?gn=xxx → SPA 路由)
│   ├── add.php                # 添加类页面(add.php?gn=xxx → SPA 路由)
│   ├── node.php               # 节点入口(node.php?tab=scan → 违禁词)
│   ├── plugin_manage.php      # 插件管理
│   ├── pay_settings.php       # 支付设置
│   ├── tutorial.php           # 教程与监控
│   ├── update.php             # 系统更新
│   └── dist/                  # ★ Vite 构建产物(需提交,勿 gitignore)
│       ├── admin.html
│       └── assets/
│           ├── index.js
│           ├── index.css
│           └── login-bg.webp
│
├── user/                      # 用户端 PHP 主题入口
│   ├── _spa_boot.php          # 注入 window.__TD_BOOT__ + 加载 dist + 插件菜单渲染
│   ├── login.php              # 登录页入口
│   ├── index.php / sy.php     # 首页入口
│   ├── set.php                # 设置类页面(set.php?gn=xxx → SPA 路由)
│   ├── site_stats.php         # 站点统计
│   ├── sqlgl.php              # SQL 备份
│   ├── monitor.php            # 监控任务
│   ├── monitor_log.php        # 监控日志
│   ├── notice.php             # 公告
│   ├── webgl.php              # 一键部署
│   └── dist/                  # ★ Vite 构建产物(需提交,勿 gitignore)
│       ├── user.html
│       └── assets/
│           ├── index.js
│           ├── index.css
│           └── login-bg.webp
│
└── spa/                       # SPA 源码(开发用,按端分层)
    ├── package.json           # 双入口构建脚本(build:admin / build:user)
    ├── vite.admin.config.js   # 管理端 Vite 配置
    ├── vite.user.config.js    # 用户端 Vite 配置
    ├── admin.html             # 管理端 HTML 模板
    ├── user.html              # 用户端 HTML 模板
    ├── .gitignore             # 仅忽略 node_modules 等,不忽略 dist
    └── src/
        ├── App-admin.vue      # 管理端根组件
        ├── App-user.vue       # 用户端根组件
        ├── main-admin.js      # 管理端入口
        ├── main-user.js       # 用户端入口
        │
        ├── admin/             # 管理端代码(全部集中于此)
        │   ├── api/           # 13 个 API 文件(auth/baota/dashboard/host/...)
        │   ├── layouts/AdminLayout.vue
        │   ├── router/index.js
        │   └── views/         # 按业务模块分目录
        │       ├── baota/     # 宝塔(List/Add)
        │       ├── host/      # 主机(List/Add)
        │       ├── node/      # 节点(List/Scan)
        │       ├── program/   # 程序(List/Add/Import)
        │       ├── order/     # 订单
        │       ├── log/       # 日志
        │       ├── plugin/    # 插件
        │       ├── pay/       # 支付
        │       ├── settings/  # 设置(Website/Admin/Api/Mail/Panel/Monitor/Theme)
        │       └── *.vue      # 顶层散落 view(Dashboard/Login/PluginPage/Repair/Tutorial/Update)
        │
        ├── user/              # 用户端代码(全部集中于此)
        │   ├── api/           # 7 个 API 文件(auth/common/database/deploy/monitor/site/stats)
        │   ├── components/    # MailBindDialog / NoticeDialog
        │   ├── layouts/UserLayout.vue
        │   ├── router/index.js
        │   └── views/         # 按业务模块分目录
        │       ├── dashboard/ # 首页仪表盘(gauge + 流量趋势 + 快捷操作 + 站点信息)
        │       ├── settings/  # 站点设置
        │       ├── ftp/       # 文件管理
        │       ├── database/  # SQL 备份
        │       ├── monitor/   # 监控任务 + 监控日志
        │       ├── stats/     # 站点统计
        │       ├── deploy/    # 一键部署
        │       └── *.vue      # 顶层 view(Login/Notice/Plugin)
        │
        └── shared/            # 双端共用代码
            ├── api/http.js    # apiGn/postGn/parseResult 统一请求封装
            ├── utils/echarts.js # echarts 按需引入(Gauge/Bar/Line + 组件)
            ├── assets/login-bg.webp  # 登录页背景图
            └── styles/theme.scss     # 全局样式 + CSS 变量
```

---

## 编译说明

### 环境

- Node.js **18+**(推荐 20 LTS)
- npm 9+ 或 pnpm / yarn

### 安装依赖

```bash
cd templates/tdesign/spa
npm install
```

### 开发(可选)

```bash
# 管理端开发服务器
npm run dev:admin

# 用户端开发服务器
npm run dev:user
```

开发配置见 `vite.admin.config.js` / `vite.user.config.js` 的 `server.proxy`。  
开发模式 HTML 内置最小 `window.__TD_BOOT__`,可在无 PHP 环境下预览 UI。

### 生产构建

```bash
cd templates/tdesign/spa

# 单独构建
npm run build:admin    # 输出到 ../admin/dist/
npm run build:user     # 输出到 ../user/dist/

# 双端一起构建
npm run build
```

产物输出：

| 端 | 输出目录 | HTML | 入口 JS |
|----|----------|------|---------|
| 管理端 | `templates/tdesign/admin/dist/` | `admin.html` | `assets/index.js` |
| 用户端 | `templates/tdesign/user/dist/` | `user.html` | `assets/index.js` |

PHP 入口通过 `mnbt_theme_url('dist/assets/index.js')` 加载。  
**请将 `admin/dist` 和 `user/dist` 一并提交/部署**,服务器无需安装 Node 即可运行主题。

构建配置要点（双端一致）：

- `base: './'` —— 相对路径,适配 PHP 子目录部署
- `inlineDynamicImports: true` —— 打成单 JS 包,避免动态 chunk 相对路径错位
- `cssCodeSplit: false` —— 单 CSS 文件
- `assetsDir: 'assets'`,固定输出 `assets/index.js` 与 `assets/index.css`
- `@` alias 指向 `src`,import 路径形如 `@/admin/api/xxx`、`@/user/views/xxx`、`@/shared/utils/echarts`

### 未构建时

打开管理后台/用户端会显示「TDesign 主题尚未构建」提示与编译命令。

---

## 启用主题

1. 确保已 `npm run build` 且存在 `admin/dist/assets/index.js` 和 `user/dist/assets/index.js`
2. 管理后台 → **系统管理** → **前端模板**
3. **管理端主题** 选择 **TDesign 双端主题** → 保存
4. **用户端主题** 选择 **TDesign 双端主题** → 保存  
   或写入文件:`templates/active_admin_theme` 和 `templates/active_user_theme` 内容均为 `tdesign`

---

## 与 PHP 的对接

### 管理端入口映射

| 访问 | SPA 路由 |
|------|----------|
| `/admin/login.php` | `#/login` |
| `/admin/index.php` `/admin/sy.php` | `#/dashboard` |
| `/admin/set.php?gn=wz` | `#/settings/website` |
| `/admin/set.php?gn=gl` | `#/settings/admin` |
| `/admin/set.php?gn=api` | `#/settings/api` |
| `/admin/set.php?gn=mail` | `#/settings/mail` |
| `/admin/set.php?gn=kzmb` | `#/settings/panel` |
| `/admin/set.php?gn=jk` | `#/settings/monitor` |
| `/admin/set.php?gn=theme` | `#/settings/theme` |
| `/admin/set.php?gn=yzf` | `#/pay` |
| `/admin/list.php?gn=zj` | `#/host` |
| `/admin/list.php?gn=bt` | `#/baota` |
| `/admin/list.php?gn=dd` | `#/order` |
| `/admin/list.php?gn=cx` | `#/program` |
| `/admin/list.php?gn=log` | `#/log` |
| `/admin/add.php?gn=zj` | `#/host/add` |
| `/admin/add.php?gn=bt` | `#/baota/add` |
| `/admin/add.php?gn=cx` | `#/program/add` |
| `/admin/add.php?gn=dr` | `#/program/import` |
| `/admin/node.php` | `#/node` |
| `/admin/node.php?tab=scan` | `#/node/scan` |
| `/admin/plugin_manage.php` | `#/plugin` |
| `/admin/pay_settings.php` | `#/pay` |
| `/admin/tutorial.php` | `#/tutorial` |
| `/admin/update.php` | `#/update` |

### 用户端入口映射

| 访问 | SPA 路由 |
|------|----------|
| `/user/login.php` | `#/login` |
| `/user/index.php` `/user/sy.php` | `#/dashboard` |
| `/user/set.php?gn=php` | `#/settings/php` |
| `/user/set.php?gn=pass` | `#/settings/pass` |
| `/user/set.php?gn=mrwd` | `#/settings/default-doc` |
| `/user/set.php?gn=yxml` | `#/settings/run-dir` |
| `/user/set.php?gn=wjt` | `#/settings/rewrite` |
| `/user/set.php?gn=ssl` | `#/settings/ssl` |
| `/user/set.php?gn=fdl` | `#/settings/hotlink` |
| `/user/set.php?gn=gzip` | `#/settings/gzip` |
| `/user/set.php?gn=cache` | `#/settings/cache` |
| `/user/set.php?gn=xgpass` | `#/settings/password` |
| `/user/set.php?gn=mysqlcz` | `#/settings/sql-auth` |
| `/user/site_stats.php` | `#/stats` |
| `/user/sqlgl.php` | `#/sql-backup` |
| `/user/monitor.php` | `#/monitor` |
| `/user/monitor_log.php` | `#/monitor/log` |
| `/user/notice.php` | `#/notice` |
| `/user/webgl.php` | `#/deploy` |
| `/user/ftp.php` | `#/ftp`(iframe 嵌入默认主题) |
| `/user/plugin.php?p=xxx&page=yyy` | `#/plugin?p=xxx&page=yyy`(iframe 在 layout 内加载) |

### 启动数据 `window.__TD_BOOT__`

由 `_spa_boot.php` 注入,字段如下：

```js
{
  siteName, footer, user, adminUser, loggedIn, needCaptcha,
  ajaxBase: './ajax.php', codeUrl: './code.php',
  logo, logoHead, logoIndex, auther,
  theme: 'tdesign', version: '0.2.0',
  entry, hash,                  // 当前 SPA 入口与目标 hash
  pluginMenuHtml,               // 主题渲染器输出的插件菜单 HTML
  conf,                         // 站点配置(全部 $conf)
  yhc,                          // 用户端主机信息(仅 user scope)
  serverHost, serverProto,
  themeList, curUserTheme, curAdminTheme,
  paymentPlugins, enabledPayments, pluginSettingsTabs
}
```

视图可在 `include '_spa_boot.php'` 前设置 `$td_inject` 数组,把页面级数据合并进 boot。

### AJAX

仍请求 **`./ajax.php`**,`gn` 与官方一致。

**管理端 gn 示例**：

| 模块 | gn 示例 |
|------|---------|
| 登录 / 退出 | `login` |
| 仪表盘 | `sxsyxx`(系统信息)、`gglist`(公告)、`check_update`(检查更新) |
| 设置 | `phpxg`(网站)、`glxg`(管理)、`apixg`(API)、`mailxg`(邮箱)、`kzmbxg`(面板)、`jkxg`(监控)、`theme`(主题切换) |
| 主机 | `listzj` / `addzj` / `editzj` / `delzj` / `delzjs` |
| 宝塔 | `listbt` / `addbt` / `editbt` / `delbt` / `checkbt` / `listnodephp` / `autodetectphp` |
| 节点 | `listnode` / `addnode` / `delnode` / `nodestatus` / `nodeconfig` / `nodeping` / `forbiddenscan` |
| 程序 / 订单 / 日志 | `listcx` / `addcx` / `editcx` / `delcx` / `listdd` / `deldelete` / `listlog` / `dellog` / `clearlog` |
| 插件 | `listzj_admin`(插件列表)、`plugin_install` / `plugin_enable` / `plugin_uninstall` |
| 支付 | `listpayments` / `savepaymentmethods` |
| 修复 / 更新 | `repair` / `systemupdate` |

**用户端 gn 示例**：

| 模块 | gn 示例 |
|------|---------|
| 登录 / 退出 | `login` |
| 首页 | `indexconf`(站点配置+流量+空间)、`refresh_space`(刷新空间) |
| 站点设置 | `set_init`(PHP列表)、`phpxg`(切PHP)、`hqjt`(伪静态)、`setwjt`、`getssl` / `setssl` / `clossl`、`fdl`、`gzip`、`cache`、`mrwd`、`yxml` |
| 密码 / SQL | `xgpass`(改密码)、`mysqlcz`(SQL权限) |
| 文件管理 | `ftp` 系列(由默认主题 ftp.php 处理) |
| SQL 备份 | `database` 系列 |
| 监控 | `monitor` 系列 |
| 站点统计 | `site_stats`(act=overview/uri_rank/ip_rank/errors/trend/spider/client/method/recent) |
| 邮箱绑定 | `mailbd` |
| 一键部署 | `webgl` |

响应解析由 `shared/api/http.js` 的 `parseResult` 统一处理,兼容以下格式：

- `{qk:1|4, code, msg}` —— panel 风格
- `{success, code, msg}` —— `json_exit` 风格
- `{code: 'xxx成功'}` —— 旧接口
- `{total, rows}` / `{ip, dk}` —— 纯数据载荷
- 纯文本(包含「失败/错误」判否,其余判成功)

错误时自动 `console.warn` / `console.error` 输出详情,方便调试。

### 插件菜单

`theme.php` 通过 `mnbt_register_theme_menu_renderer` 注册双端渲染器,  
将引擎传入的插件菜单树转为侧栏 HTML(`td-side-submenu` / `td-side-leaf`),  
由 `_spa_boot.php` 注入到 `window.__TD_BOOT__.pluginMenuHtml`。

**独立分组**：插件菜单在侧栏中作为独立的"扩展与工具"分组显示,与业务菜单视觉分离。

**SPA 路由跳转**：插件叶子菜单项带 `data-td-route="/plugin?p=xxx&page=yyy"` 属性,  
点击时由 SPA 拦截,通过 `router.push` 在 layout 内通过 iframe 加载插件页面,不再新窗口打开。

---

## 设计规范

| 项 | 值 |
|----|----|
| 主色 | `#0052D9`(TDesign 品牌蓝) |
| 主色浅底 | `#E8F3FF` |
| 成功 / 警告 / 危险 | `#2BA471` / `#E37318` / `#D54941` |
| 正文 / 次要 / 占位 | `#181818` / `#595959` / `#8C8C8C` |
| 边框 / 背景 / 表面 | `#E7E7E7` / `#F2F3F5` / `#FFFFFF` |
| 侧栏深色 | 背景 `#1F2B3A`,文字 `#C5CDD6` |
| 侧栏宽度 | `220px`(折叠 `64px`) |
| 顶栏高度 | `56px` |
| 圆角 | `6px` / 大圆角 `10px` |
| 字体 | 系统 UI / 苹方 / 微软雅黑 |
| 阴影 | `0 1px 2px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.04)` |

CSS 变量定义在 `spa/src/shared/styles/theme.scss` 顶部 `:root`,修改后重新 `npm run build`。

### 通用样式类

| 类 | 用途 |
|----|------|
| `.td-page` / `.td-page-head` / `.td-page-title` / `.td-page-subtitle` | 页面容器与标题 |
| `.td-card` / `.td-card-head` / `.td-card-bd` | 卡片 |
| `.td-form` / `.td-form-row` / `.td-form-actions` / `.td-form-switch` | 表单页 |
| `.td-toolbar` / `.td-toolbar-spacer` | 表格工具条 |
| `.td-table-wrap` | 表格容器(自带白底/边框/圆角/阴影) |
| `.td-set-card` / `.td-set-card-hd` / `.td-set-card-bd` | 设置卡片 |
| `.td-chip` / `.td-chip-success` / `.td-chip-danger` | 状态徽标 |
| `.td-empty` / `.td-code` / `.td-mono` / `.td-flex-center` / `.td-gap-8` / `.td-row-actions` | 通用工具 |

### 长表单滚动

`t-dialog` 弹窗内长表单通过 `.t-dialog__body .td-form` 自动启用垂直滚动：  
`max-height: calc(100vh - 220px)` + `overflow-y: auto` + `padding-right: 6px`。

---

## 开发约定

1. **不要改** `admin/*.php` / `user/*.php` 控制器与 `ajax.php` 接口路径
2. 新增纯前端页面：
   - 管理端：`src/admin/views/` 添加 `.vue` + `src/admin/router/index.js` 注册路由
   - 用户端：`src/user/views/` 添加 `.vue` + `src/user/router/index.js` 注册路由
3. import 路径统一使用 `@` alias：
   - 管理端：`@/admin/api/xxx`、`@/admin/views/xxx`
   - 用户端：`@/user/api/xxx`、`@/user/views/xxx`
   - 共用：`@/shared/api/http`、`@/shared/utils/echarts`、`@/shared/styles/theme.scss`
4. 列表页统一服务端分页,前端只做查询条件与渲染
5. 表单/表格统一使用 `.td-form` / `.td-table-wrap` / `.td-toolbar` 等通用类,避免重复样式
6. 表格工具条 `.td-toolbar` 使用 `padding: 12px 16px` 确保与边框间距
7. `t-dialog` 组件必须使用 `v-model:visible` 而非 `v-model`(避免 Vue modelValue 错误)
8. `spa/.gitignore` 忽略 `node_modules`,**不忽略** `admin/dist` 和 `user/dist`
9. 版本号同步:`theme.json` 与 `spa/package.json`
10. 修改源码后必须 `npm run build:admin` / `npm run build:user`,否则线上不会生效

---

## 已知限制

- 文件管理页面(ftp)采用 iframe 嵌入默认主题 ftp.php(复用成熟的文件管理 UI,避免重写复杂组件)
- 插件自带页面仍由插件自行渲染,主题仅提供菜单入口与 iframe 容器
- 部分旧接口字段因版本差异可能需在 `parseResult` 或视图层做兼容调整

---

## 版本

- **0.2.0** 双端主题：用户端全部页面原生化(仪表盘/设置/文件管理/SQL备份/监控/统计/部署/插件) + 按端分层目录重构(admin/user/shared) + 左侧背景图登录页 + echarts gauge 仪表盘 + 快捷操作平铺按钮 + 插件页面 iframe 内嵌
- **0.1.0** 首版：SPA 壳 + 登录 + 全部后台页面原生化(仪表盘 / 设置 / 主机 / 宝塔 / 节点 / 程序 / 订单 / 日志 / 插件 / 支付 / 主题切换 / 教程 / 更新 / 修复)
