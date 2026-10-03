---
title: TDesign 三端主题
description: tdesign 主题说明:特性、目录结构、编译、启用、设计规范、开发约定、已知限制与版本
---

# TDesign 三端主题(tdesign)v0.4.0

现代化 **三端** 主题:基于 TDesign 品牌蓝,覆盖**用户端 + 管理端**全部页面（主页售卖端随 official_site 插件）。卡片化布局、侧栏 + 顶栏、echarts 数据可视化、左侧背景图登录页、独立主页售卖前端。

技术栈:**Vue 3 + Vue Router (Hash) + TDesign Vue Next + vue3-sfc-loader(免构建)+ ECharts(UMD 全量)**。

> **V1.87 起 tdesign 为系统默认主题**(classic 即原 default 主题冻结保留,不再维护);SPA 不再需要 Node 构建链,由 [vue3-sfc-loader](https://github.com/FranckFreiburger/vue3-sfc-loader) 在浏览器内直接编译 `spa/src/` 下的 `.vue` 源码运行,修改源码刷新页面即生效。
>
> 历史文档:双端改造计划见 [tdesign 双端改造计划](../plan/tdesign-user-scope.md);与 PHP 的对接细节见 [与 PHP 的对接](./tdesign-php.md)。

---

## 特性

### 用户端(user scope)

| 模块 | 说明 |
|------|------|
| 登录页 | 左侧背景图 + 右侧登录区,验证码支持,登录态自动跳转 |
| 控制台壳 | 侧栏 + 顶栏,折叠/移动端抽屉,退出登录确认 |
| 首页仪表盘 | 资源使用 echarts gauge 仪表盘 / 月度流量趋势柱状图+折线图 / 快捷操作平铺按钮 / 4 张站点信息卡片 |
| 站点设置 | PHP 版本 / 密码访问 / 默认文档 / 运行目录 / 伪静态 / SSL / 防盗链 / Gzip / 缓存 / 修改密码 / SQL 权限 |
| 文件管理 | SPA 原生文件管理(目录浏览/在线编辑/分片上传+断点续传/拖拽上传/复制剪切/压缩解压/权限/图片预览/SQL 导入) |
| SQL 备份 | 备份列表 / 立即备份 / 下载 / 恢复 / 删除 |
| 监控任务 | 任务列表 / 新增 / 编辑 / 删除 / 监控日志 |
| 站点统计 | 概览卡片 + 访问路径 / IP 排行 / 错误日志分类标签页 |
| 一键部署 | 部署程序列表 |
| 插件页面 | 通过 SPA 路由 + iframe 在 layout 内加载,不再新窗口打开 |
| 公告弹窗 | NoticeDialog 组件,sessionStorage 记忆已读 |
| 邮箱绑定 | MailBindDialog 组件,未绑定时强制弹出 |

### 管理端(admin scope)

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

### 主页(V1.88 起随 official_site 插件,不再属于主题)

> 主页（站点根路径 `/`）与官网内容页面已随 `official_site` 插件迁移
> （`app_plugins/official_site/assets/spa/`），tdesign 主题仅保留三端
> （用户端 / 管理端 / Docker 端）与共享运行时。下文为主页仍随主题时的历史说明。

完整售卖系统前端,通过**插件 API 路由**(`index.php?_r=/xxx/api/xxx`)驱动,不依赖 iframe。依赖 `user_info`(认证)、`balance`(余额)、`hosting_shop`(商店)插件;官网页面由 `official_site` 插件提供。

| 模块 | 说明 |
|------|------|
| 落地页 | Hero + 公告 + 套餐卡 + 特性区块(数据来自 `mnbt_home_data()` 注入的 `$plans` / `$blocks`) + 轮播 hero + 新闻预览 + 客户评价(绿色风格) |
| 账户 | 登录 / 注册 / 个人信息 / 修改密码(`user_info` 插件 `GET /account/api/*`) |
| 商店 | 套餐列表 / 下单(选周期 + 支付方式,支付 HTML 用 `document.write` 跳转) / 我的主机 / 我的订单(`hosting_shop` 插件 `GET /shop/api/*`) |
| 余额 | 余额卡 + 流水表格 / 充值(`balance` 插件 `GET /balance/api/*`) |
| 官网内容 | 关于我们 / 产品中心(列表+详情) / 新闻资讯(列表+详情) / 联系我们(留言表单)(`official_site` 插件 `GET /site/api/*`、`POST /site/api/contact`,`meta.cap: 'site'` 能力守卫) |
| 登录态 | `auth.js` store 启动时探测 `/account/api/me`,路由守卫统一拦截,未登录访问受保护页自动跳登录 |

---

## 目录结构

```
templates/tdesign/
├── theme.json                 # 主题元信息(scope: ["user", "admin", "docker"])
├── theme.php                  # 注册双端菜单渲染器(插件菜单 → 侧栏 HTML)
├── assets/
│   └── td-boot.js             # ★ vue3-sfc-loader 免构建引导(所有端共用)
│
├── admin/                     # 管理端 PHP 主题入口
│   ├── _spa_boot.php          # 注入 window.__TD_BOOT__ + 加载 vendor UMD + td-boot.js
│   ├── head.php               # 插件页 iframe 壳(Bootstrap/lyear,与 classic 一致)
│   ├── assets/                # head 壳所需 CSS(admin-common 等,与 classic 同源)
│   ├── login.php              # 登录页入口
│   ├── index.php / sy.php     # 仪表盘入口(sy.php 注入 $sy)
│   ├── set.php                # 设置类页面(set.php?gn=xxx → SPA 路由)
│   ├── list.php               # 列表类页面(list.php?gn=xxx → SPA 路由)
│   ├── add.php                # 添加类页面(add.php?gn=xxx → SPA 路由)
│   ├── node.php               # 节点入口(node.php?tab=scan → 违禁词)
│   ├── plugin_manage.php      # 插件管理
│   ├── pay_settings.php       # 支付设置
│   ├── tutorial.php           # 教程与监控
│   └── update.php             # 系统更新
│
├── user/                      # 用户端 PHP 主题入口
│   ├── _spa_boot.php          # 注入 window.__TD_BOOT__ + 加载 vendor UMD + td-boot.js
│   ├── head.php               # 插件页 iframe 壳
│   ├── assets/                # head 壳所需 CSS(user-common 等)
│   ├── login.php              # 登录页入口
│   ├── index.php / sy.php     # 首页入口
│   ├── ftp.php                # 在线文件管理入口
│   ├── set.php                # 设置类页面(set.php?gn=xxx → SPA 路由)
│   ├── site_stats.php         # 站点统计
│   ├── sqlgl.php              # SQL 备份
│   ├── monitor.php            # 监控任务
│   ├── monitor_log.php        # 监控日志
│   ├── notice.php             # 公告
│   └── webgl.php              # 一键部署
│
├── docker/                    # Docker 控制台 PHP 入口(7 视图 + _spa_boot.php)
│   └── assets/captcha-images/ # 滑块验证码图集
│
└── spa/                       # SPA 源码(浏览器内直出,按端分层)
    └── src/
        ├── App-*.vue          # 各端根组件(admin/user/docker/account,home 随 official_site 插件)
        ├── main-*.js          # 各端入口(createApp + router + TDesign)
        │
        ├── admin/             # 管理端代码(全部集中于此)
        │   ├── api/           # 13 个 API 文件(auth/baota/dashboard/host/...)
        │   ├── layouts/AdminLayout.vue
        │   ├── router/index.js
        │   └── views/         # 按业务模块分目录(baota/docker/host/node/program/order/log/plugin/pay/settings + 顶层 view)
        │
        ├── user/              # 用户端代码(全部集中于此)
        │   ├── api/           # 8 个 API 文件(auth/common/database/deploy/ftp/monitor/site/stats)
        │   ├── utils/         # uploader.js 分片上传器 / codemirror.js 动态加载
        │   ├── components/    # MailBindDialog / NoticeDialog
        │   ├── layouts/UserLayout.vue
        │   ├── router/index.js
        │   └── views/         # 按业务模块分目录(dashboard/settings/ftp/database/monitor/stats/deploy + 顶层 view)
        │
        ├── docker/            # Docker 控制台代码(api/components/layouts/router/views)
        ├── account/           # 用户中心(user_info 插件,layouts/api/router/views)
        │   │                  # home 售卖端同样迁至 official_site 插件 assets/spa/
        └── shared/            # 各端共用代码
            ├── api/http.js    # apiGn/postGn/parseResult 统一请求封装
            ├── utils/echarts.js # echarts UMD 全局桥接
            ├── assets/        # login-bg.webp / bg1-3.jpg 等(经 handleModule 以 URL 导入)
            └── styles/theme.css # 全局样式 + CSS 变量(由 theme.scss 编译,SCSS 源文件已移除)
```

---

## 免构建加载机制(V1.87)

SPA 不再有 Node 构建链(vite/package.json 已移除)。`_spa_boot.php` 按顺序输出:

1. `window.__TD_BOOT__`(含 `scope` / `themeBase` / `vendorBase` / `assetBase`)
2. vendor UMD(`imsetes/vendor/`):`vue.global.prod.js` → `vue-router.global.prod.js` → `axios.min.js` → `tdesign.min.js`(+`tdesign.min.css`) → `echarts.min.js`(仅 user/admin) → `vue3-sfc-loader.js`
3. `templates/tdesign/assets/td-boot.js` —— 共用引导

`td-boot.js` 职责:

- **moduleCache 桥接**:`vue` / `vue-router` / `tdesign-vue-next` / `axios` / `echarts` 及其子路径映射到 UMD 全局,并补 `default` 导出
- **pathResolve**:`@/xxx` → `spa/src/xxx`;相对导入按引用方目录折叠;裸模块名原样返回命中 moduleCache
- **getFile**:无扩展名导入按 node 顺序探测(`.js` → `/index.js` → `.vue` → `.css` → `.json`),结果按 URL 记忆
- **handleModule**:`.webp/.jpg/.png/.svg` 等图片导入返回 URL(替代 vite 的资产管线)
- **addStyle**:SFC `<style>` 块与 `.css` 导入注入 `<style>` 标签

### 版本与升级

vendor 依赖版本记录在 `imsetes/vendor/VERSIONS.txt`;升级时用同版本 UMD 文件覆盖即可(vue3-sfc-loader 当前 **0.9.5**)。

### 已知取舍

- 源码直出:前端代码不经压缩/混淆地发布(本就随分发包可见)
- 首次导航按需编译,低端设备可感知延迟;浏览器 HTTP 缓存命中后无感
- 无 HMR/构建期校验:改 `.vue` 保存后刷新生效,语法错误在浏览器控制台暴露
- SCSS 源文件已移除(编译为 `theme.css` / `home.css` 提交);新增全局样式请直接写 CSS 或在 `.vue` 内写 `<style>`

---

## 启用主题

1. 确认 `imsetes/vendor/` 目录完整(UMD 依赖随分发包内置)
2. tdesign 已是系统默认主题;如需手动切换:管理后台 → **系统管理** → **前端模板** 选择 **TDesign 三端主题** → 保存  
   或写入文件:`templates/active_admin_theme` / `templates/active_user_theme` / `templates/active_docker_theme` 内容均为 `tdesign`

---

## 与 PHP 的对接

管理端 / 用户端入口映射、`__TD_BOOT__` 启动数据、AJAX gn 列表、插件菜单对接、主页 API 路由等,见 [与 PHP 的对接(tdesign-php.md)](./tdesign-php.md)。

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

CSS 变量定义在 `spa/src/shared/styles/theme.css` 顶部 `:root`,修改后刷新页面即生效。

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

`t-dialog` 弹窗内长表单通过 `.t-dialog__body .td-form` 自动启用垂直滚动:  
`max-height: calc(100vh - 220px)` + `overflow-y: auto` + `padding-right: 6px`。

---

## 开发约定

1. **不要改** `admin/*.php` / `user/*.php` / `home/*.php` 控制器与 `ajax.php` 接口路径
2. 新增纯前端页面:
   - 管理端:`src/admin/views/` 添加 `.vue` + `src/admin/router/index.js` 注册路由
   - 用户端:`src/user/views/` 添加 `.vue` + `src/user/router/index.js` 注册路由
   - 主页:`src/home/views/` 添加 `.vue` + `src/home/router/index.js` 注册路由(需登录页加 `meta.auth`、访客页加 `meta.guest`)
3. import 路径统一使用 `@` alias:
   - 管理端:`@/admin/api/xxx`、`@/admin/views/xxx`
   - 用户端:`@/user/api/xxx`、`@/user/views/xxx`
   - 主页:`@/home/api/xxx`、`@/home/views/xxx`、`@/home/store/auth`
   - 共用:`@/shared/api/http`、`@/shared/utils/echarts`、`@/shared/styles/theme.css`
4. 列表页统一服务端分页,前端只做查询条件与渲染
5. 表单/表格统一使用 `.td-form` / `.td-table-wrap` / `.td-toolbar` 等通用类,避免重复样式
6. 表格工具条 `.td-toolbar` 使用 `padding: 12px 16px` 确保与边框间距
7. `t-dialog` 组件必须使用 `v-model:visible` 而非 `v-model`(避免 Vue modelValue 错误)
8. 修改源码后**无需任何构建**,刷新页面即生效(浏览器内由 vue3-sfc-loader 编译)
9. 图片等静态资产放 `spa/src/shared/assets/`,以 `import url from '@/shared/assets/x.webp'` 方式引用(loader 返回 URL)
10. 版本号同步:`theme.json` 与各端 `_spa_boot.php` 注入的 `version` 字段

---

## 已知限制

- SPA 由 vue3-sfc-loader 浏览器内编译,不支持构建期优化(压缩/摇树/HMR),首次访问有按需编译开销
- SPA 全局样式为预编译 CSS(无 SCSS),需预处理样式的场景请在 `.vue` 内手写或离线编译
- 插件自带页面仍由插件自行渲染(Bootstrap/lyear 壳),主题提供 head.php 壳 + iframe 容器
- 主页售卖端依赖 `user_info` / `balance` / `hosting_shop` 三个插件;官网页面依赖 `official_site` 插件(`boot.hasSite` 为 false 时导航与页面自动隐藏)
- 部分旧接口字段因版本差异可能需在 `parseResult` 或视图层做兼容调整

---

## 版本

- **0.4.0** 免构建重构:引入 vue3-sfc-loader,移除 Vite/npm 构建链,dist 由 `spa/src` 直出替代;文件管理器 SPA 原生化(目录/编辑/分片上传/压缩解压/回收站/权限);补齐 user/admin head.php 插件页壳;echarts 改 UMD 全量;SCSS 预编译为 CSS;V1.87 起 tdesign 为系统默认主题(classic 冻结)
- **0.3.0** 主页售卖端:新增 home scope(落地页/登录注册/个人信息/商店/下单/我的主机/订单/余额/充值),插件 API 路由(`index.php?_r=`)驱动 + 独立 Vite 入口(`build:home`),登录态由 `auth.js` store 统一探测
- **0.2.0** 双端主题:用户端全部页面原生化(仪表盘/设置/文件管理/SQL备份/监控/统计/部署/插件) + 按端分层目录重构(admin/user/shared) + 左侧背景图登录页 + echarts gauge 仪表盘 + 快捷操作平铺按钮 + 插件页面 iframe 内嵌
- **0.1.0** 首版:SPA 壳 + 登录 + 全部后台页面原生化(仪表盘 / 设置 / 主机 / 宝塔 / 节点 / 程序 / 订单 / 日志 / 插件 / 支付 / 主题切换 / 教程 / 更新 / 修复)
