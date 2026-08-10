# PRD：MNBT 官网（guanwang1 移植）

> 版本：0.1　日期：2026-08-10　状态：已批准（已实施 M1~M4）

## 1. 背景与目标

`D:\documents\GitHub\guanwang1` 是一个独立部署的科技企业官网前端（Vue 3 + Vite + lucide 图标），包含前台 7 个页面与一套内容管理后台（产品/新闻/留言 CRUD），数据依赖独立后端 API（项目内未含）。

本任务将该官网"照搬"进 MNBT 的 tdesign home 主题（SPA），并按用户决策补齐 MNBT 缺失的能力：

- 新增**官网内容管理插件**（产品、新闻、留言的数据表 + 前台 API + 管理后台 CRUD）
- home 前台新增 **关于我们 / 产品中心 / 新闻资讯 / 联系我们** 页面，改造落地页（轮播、新闻预览、客户评价）
- **保留 guanwang1 绿色视觉**（#42b983），内容适配 **MNBT 业务语境**（虚拟主机售卖平台）

## 2. 用户决策记录

| 问题 | 决策 |
| --- | --- |
| 数据支撑方案 | 为缺失功能**编写插件**实现（`official_site`） |
| 后台归属 | **尽可能用插件实现**，并入 MNBT admin scope（插件菜单 + iframe 页面） |
| 视觉风格 | **保留 guanwang1 绿色风格**（#42b983） |
| 内容语义 | **适配为 MNBT 业务语境**（平台介绍/主机产品/公告新闻/客服渠道） |

## 3. 范围

### 3.1 前台（tdesign home SPA，绿色风格）

| 页面 | 路由 | 数据来源 | 说明 |
| --- | --- | --- | --- |
| 落地页（改造） | `/` | boot + 插件 API | 现有售卖落地页基础上，新增轮播区、新闻预览区、客户评价区；整体切换绿色主题变量 |
| 关于我们 | `/about` | 静态 + site config | 平台简介 / 使命愿景 / 核心团队（MNBT 语境） |
| 产品中心 | `/site/products` | 插件 API | 产品卡片 + 分类筛选（全部/AI/云主机/域名/安全/服务） |
| 产品详情 | `/site/products/:id` | 插件 API | 大图 + 特性列表 + 联系我们 CTA |
| 新闻资讯 | `/site/news` | 插件 API | 列表分页 + 侧边栏（热门标签/热门文章/关于我们） |
| 新闻详情 | `/site/news/:id` | 插件 API | 标题/时间/分类/浏览量 + 正文 |
| 联系我们 | `/site/contact` | 静态 + POST API | 联系信息 + 留言表单（提交到插件） |

> 现有售卖相关页面（`/shop*`、`/balance*`、`/login` 等）保留不动，仅视觉变量随主题切换为绿色。

### 3.2 官网内容管理插件 `official_site`

- **数据表**（3 张）：
  - `MN_plugin_site_product`（产品）：id / name / category / description / features(JSON) / image / sort / status / created_at
  - `MN_plugin_site_news`（新闻）：id / title / category / content / views / sort / status / created_at
  - `MN_plugin_site_message`（留言）：id / name / email / phone / message / is_read / created_at
- **前台 API**（P2 路由，`/site/api/*`，前缀在 home 入口的 routeBase 下）：
  - `GET /site/api/products`：产品列表（支持 `?category=` 筛选）
  - `GET /site/api/products/{id}`：产品详情
  - `GET /site/api/news`：新闻分页列表（支持 `?page=`、`?category=`）
  - `GET /site/api/news/{id}`：新闻详情（views +1）
  - `GET /site/api/news/popular`：热门文章（按浏览量）
  - `POST /site/api/contact`：提交留言（校验 + 入库）
- **管理后台**（`mnbt_register_page('admin', ...)` + 侧边栏菜单「官网内容」）：
  - 产品管理 `views/admin/products.php`：列表 + 添加/编辑/删除（模态框）
  - 新闻管理 `views/admin/news.php`：列表 + 添加/编辑/删除
  - 留言管理 `views/admin/messages.php`：列表 + 标记已读 + 删除
- **能力探测**：`has_site`（`mnbt_plugin_enabled('official_site')`）注入 home boot；未启用时自动禁用前台官网页面与导航（复用 hasShop 机制）

### 3.3 初始数据

安装时自动写入示例数据（适配 MNBT 语境）：
- 产品：AI 智能分析平台 / 云服务器 / 虚拟主机 / 域名注册 / SSL 证书 / 安全防护（分类：ai / cloud / hosting / domain / security）
- 新闻：产品发布 / 优惠活动 / 行业动态 / 平台公告 各 2 条（共 8 条）

## 4. 视觉规范（绿色风格）

- 主色 `#42b983`（guanwang1 原色），hover 加深 `#359c6d`
- 结合 MNBT 用户偏好：浅色背景 + 圆角卡片（8-14px）+ 轻阴影，保留 guanwang1 的扁平留白节奏
- home.scss 引入 `--hd-brand: #42b983` 等绿色变量（覆盖现有品牌蓝），各页 scoped 样式照搬 guanwang1 后圆角化
- 页面 header：图片背景 + 居中标题（照搬 guanwang1 的 page-header）
- 管理后台 PHP 页面：维持 MNBT 后台简洁风格（不强制绿色），保证后台统一性

## 5. 技术方案

### 5.1 插件结构（参照 hosting_shop）

```
app_plugins/official_site/
├── bootstrap.php          # 插件注册、路由、admin 页面/菜单注册、安装数据
├── install.sql            # 3 张表
├── uninstall.sql
├── plugin.json
├── lib/site.php           # 数据访问层（list/get/save/delete）
└── views/admin/
    ├── products.php       # 产品管理（列表+模态框 CRUD）
    ├── news.php           # 新闻管理
    └── messages.php       # 留言管理
```

### 5.2 home SPA 新增

```
src/home/views/site/
├── AboutView.vue          # 关于我们
├── products/ProductsView.vue / ProductDetailView.vue
├── news/NewsView.vue / NewsDetailView.vue
└── ContactView.vue        # 联系我们
```

- `router/index.js` 新增路由，`meta.cap: 'site'` 挂能力守卫
- `api/site.js`：封装 5 个 API（复用 `routeRequest`）
- `utils/format.js` 增加日期格式化（`formatDate`）
- `HomeLayout.vue` 导航新增：关于我们 / 产品中心 / 新闻资讯 / 联系我们（`v-if="boot.hasSite"`）
- `LandingView.vue` 改造：轮播 hero + 特性 + 新闻预览（插件 API）+ 客户评价 + 套餐区 + CTA
- 背景图：`bg1/bg2/bg3.jpg` 从 guanwang1 复制到 home 静态资源（或使用主题背景图占位）

### 5.3 能力禁用

沿用已有机制（已实施）：
- `MPHX/frontend.php` `mnbt_home_data()` 增加 `'has_site' => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('official_site')`
- `templates/tdesign/home/index.php` boot 数组注入 `'hasSite' => !empty($has_site)`（JS 端为 `boot.hasSite`）
- home 路由守卫 `meta.cap: 'site'`：`boot.hasSite === false` 时 redirect 到首页
- `HomeLayout.vue` 导航、`LandingView.vue` 新闻预览区均 `v-if="boot.hasSite"`

## 6. 路由映射（guanwang1 → MNBT home）

| guanwang1 | MNBT home |
| --- | --- |
| `/`（Home） | `/`（LandingView 改造） |
| `/about` | `/about` |
| `/products`、`/products/:id` | `/site/products`、`/site/products/:id` |
| `/news`、`/news/:id` | `/site/news`、`/site/news/:id` |
| `/contact` | `/site/contact` |
| `/admin/*` | 并入 admin scope：插件菜单「官网内容」（iframe 加载 plugin.php） |

## 7. 验收标准

1. 启用 `official_site` 插件后，home 前台出现 4 个新导航，所有页面可访问、数据来自插件 API
2. 留言表单提交成功入库，后台留言管理可见
3. 后台可对产品/新闻增删改查，前台实时反映
4. 停用插件后，官网页面/导航自动消失（hasSite 机制），不产生报错
5. 全部页面为绿色风格（#42b983），与售卖区、账户区视觉统一
6. `npm run build:home` 构建通过；插件无 PHP 语法错误

## 8. 里程碑

- M1：插件 `official_site`（表 + lib + admin CRUD 页面 + 前台 API + 初始数据）
- M2：home SPA 新增 4 页面 + api 封装 + 路由/守卫 + 导航
- M3：落地页改造（轮播/新闻预览/客户评价）+ 绿色变量切换
- M4：构建验证 + 插件安装/停用验证 + 文档同步

## 9. 风险与注意

- 插件 admin PHP 页面在 iframe 中渲染，需自包含 HTML（参照 hosting_shop 写法）
- guanwang1 使用 lucide 图标，home 使用 MDI（mdi-*）图标体系，移植时全部替换为 MDI
- 绿色变量需同时覆盖 TDesign 主题色（`--td-brand` 由 boot.sitePrimary 控制，home 端按绿色注入）
