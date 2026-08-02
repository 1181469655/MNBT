# MNBT 插件商店 & 主题商店 — 产品需求文档 (PRD)

> 版本：V1.0  
> 日期：2025-07

---

## 一、产品概述

### 1.1 背景

MNBT 平台已支持 PHP 插件系统（`app_plugins/`）与前端主题系统（`templates/`），并提供完整的插件开发手册（`PLUGIN_DEV.md`）与主题开发手册（`THEME_DEV.md`）。为促进生态繁荣，需搭建独立的「插件商店」与「主题商店」Web 应用，供开发者上传、分发，用户浏览、下载。

### 1.2 目标

- 提供统一入口，展示所有已上架的 MNBT 插件与主题
- 支持开发者注册、登录、提交/管理自己的插件与主题
- 支持管理员审核、上架/下架、管理全部内容
- 支持定价（免费/付费），统计下载量

---

## 二、技术架构

| 层 | 选型 |
|----|------|
| 后端 | Node.js + TypeScript（Express） |
| 前端 | 原生 HTML + LayUI |
| 数据库 | SQLite（使用 `better-sqlite3` 以外的库，如 `sql.js` 或原生 `sqlite3` 包） |
| 文件存储 | 本地文件系统（zip 存放于可配置目录） |

后端负责：
- 业务 API（JSON）
- 静态资源托管（前端 HTML/JS/CSS/LayUI）
- 文件上传/下载
- 数据库读写

---

## 三、插件与主题识别规则

基于 `PLUGIN_DEV.md` 与 `THEME_DEV.md` 规范，系统在上传 zip 时自动校验。

### 3.1 插件识别

| 项目 | 规则 |
|------|------|
| 目录名 | 插件 slug，仅允许 `a-z A-Z 0-9 _ -`，最长 63 字符 |
| 必含文件 | `plugin.json` + `bootstrap.php` |
| `plugin.json` 必填字段 | `name`（显示名）、`version`（版本号） |
| `plugin.json` 可选字段 | `id`、`author`、`description`、`requires_mnbt`、`type` |
| 可选目录 | `admin/`、`user/`、`assets/`、`install.sql`、`uninstall.sql` |
| zip 根层 | 解压后第一层必须有且仅有一个目录（即插件 slug 目录） |

### 3.2 主题识别

| 项目 | 规则 |
|------|------|
| 目录名 | 主题 ID，仅允许 `a-zA-Z0-9_-` |
| 必含文件 | `theme.json` |
| `theme.json` 必填字段 | `title`（显示名） |
| `theme.json` 可选字段 | `name`、`version`、`description`、`author`、`scope` |
| 可选目录 | `user/`、`admin/`（至少存在一个视为有效） |
| zip 根层 | 解压后第一层必须有且仅有一个目录（即主题目录） |

---

## 四、数据库设计（SQLite）

### 4.1 表结构

#### `users` — 用户表

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | INTEGER PRIMARY KEY AUTOINCREMENT | 主键 |
| `username` | TEXT UNIQUE NOT NULL | 用户名 |
| `password` | TEXT NOT NULL | 密码（bcrypt 哈希） |
| `email` | TEXT UNIQUE NOT NULL | 邮箱 |
| `role` | TEXT NOT NULL DEFAULT 'developer' | 角色：`admin` / `developer` |
| `avatar` | TEXT DEFAULT '' | 头像 URL |
| `bio` | TEXT DEFAULT '' | 个人简介 |
| `status` | TEXT NOT NULL DEFAULT 'active' | `active` / `banned` |
| `created_at` | TEXT NOT NULL | 创建时间 |
| `updated_at` | TEXT NOT NULL | 更新时间 |

#### `items` — 插件/主题主表

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | INTEGER PRIMARY KEY AUTOINCREMENT | 主键 |
| `type` | TEXT NOT NULL | `plugin` / `theme` |
| `slug` | TEXT NOT NULL | 唯一标识（目录名） |
| `name` | TEXT NOT NULL | 显示名称 |
| `version` | TEXT NOT NULL | 当前版本 |
| `author_id` | INTEGER NOT NULL | 作者（users.id） |
| `author_name` | TEXT NOT NULL | 作者显示名 |
| `price` | REAL NOT NULL DEFAULT 0 | 价格（0 = 免费） |
| `description` | TEXT NOT NULL DEFAULT '' | 简介（支持 HTML） |
| `main_image` | TEXT DEFAULT '' | 主图相对路径 |
| `screenshots` | TEXT DEFAULT '[]' | 截图列表（JSON 数组） |
| `zip_path` | TEXT NOT NULL | zip 文件相对路径 |
| `zip_size` | INTEGER NOT NULL DEFAULT 0 | zip 文件体积（字节） |
| `downloads` | INTEGER NOT NULL DEFAULT 0 | 下载次数 |
| `status` | TEXT NOT NULL DEFAULT 'pending' | `pending` / `approved` / `rejected` / `suspended` |
| `review_msg` | TEXT DEFAULT '' | 审核备注 |
| `requires_mnbt` | TEXT DEFAULT '' | 要求最低 MNBT 版本 |
| `category` | TEXT DEFAULT '' | 分类标签 |
| `tags` | TEXT DEFAULT '[]' | 标签（JSON 数组） |
| `homepage` | TEXT DEFAULT '' | 项目主页 |
| `created_at` | TEXT NOT NULL | 创建时间 |
| `updated_at` | TEXT NOT NULL | 更新时间 |

联合唯一约束：`(type, slug)` 确保同类型下 slug 唯一。

#### `item_versions` — 版本历史

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | INTEGER PRIMARY KEY AUTOINCREMENT | 主键 |
| `item_id` | INTEGER NOT NULL | 关联 items.id |
| `version` | TEXT NOT NULL | 版本号 |
| `zip_path` | TEXT NOT NULL | 该版本 zip 路径 |
| `zip_size` | INTEGER NOT NULL DEFAULT 0 | 该版本 zip 大小 |
| `changelog` | TEXT DEFAULT '' | 更新日志（支持 HTML） |
| `status` | TEXT NOT NULL DEFAULT 'pending' | 审核状态 |
| `created_at` | TEXT NOT NULL | 创建时间 |

#### `download_logs` — 下载日志

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | INTEGER PRIMARY KEY AUTOINCREMENT | 主键 |
| `item_id` | INTEGER NOT NULL | 关联 items.id |
| `user_id` | INTEGER | 下载者（可空，未登录也可下载） |
| `ip` | TEXT NOT NULL | 下载 IP |
| `created_at` | TEXT NOT NULL | 下载时间 |

#### `reviews` — 评价表（为未来扩展预留）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | INTEGER PRIMARY KEY AUTOINCREMENT | 主键 |
| `item_id` | INTEGER NOT NULL | 关联 items.id |
| `user_id` | INTEGER NOT NULL | 评价者 |
| `rating` | INTEGER NOT NULL | 评分 1-5 |
| `content` | TEXT DEFAULT '' | 评价内容 |
| `created_at` | TEXT NOT NULL | 创建时间 |

#### `edit_requests` — 修改审核表（开发者修改插件信息需审核）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | INTEGER PRIMARY KEY AUTOINCREMENT | 主键 |
| `item_id` | INTEGER NOT NULL | 关联 items.id |
| `field` | TEXT NOT NULL | 修改字段名 |
| `old_value` | TEXT | 旧值 |
| `new_value` | TEXT NOT NULL | 新值 |
| `status` | TEXT NOT NULL DEFAULT 'pending' | `pending` / `approved` / `rejected` |
| `created_at` | TEXT NOT NULL | 创建时间 |

### 4.2 索引

- `items(type, status)` — 商店列表查询
- `items(author_id)` — 开发者管理
- `items(type, slug)` — 唯一约束
- `items(downloads)` — 热门排序
- `edit_requests(item_id, status)` — 审核查询

---

## 五、API 设计

所有 API 以 `/api` 为前缀，返回 JSON。

### 5.1 认证相关

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| POST | `/api/auth/register` | 用户注册 | 否 |
| POST | `/api/auth/login` | 用户登录 | 否 |
| POST | `/api/auth/logout` | 退出登录 | 是 |
| GET | `/api/auth/me` | 获取当前用户信息 | 是 |
| PUT | `/api/auth/password` | 修改密码 | 是 |

### 5.2 插件/主题公开接口

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| GET | `/api/items` | 列表（支持筛选/分页/排序） | 否 |
| GET | `/api/items/:id` | 详细信息 | 否 |
| GET | `/api/items/:id/download` | 下载 | 否（记录 IP） |
| GET | `/api/items/:id/versions` | 版本历史 | 否 |

查询参数（`GET /api/items`）：
- `type` — `plugin` / `theme`
- `status` — 默认 `approved`
- `keyword` — 搜索关键词
- `category` — 分类
- `author_id` — 作者筛选
- `min_price` / `max_price` — 价格区间
- `sort` — `downloads` / `newest` / `price`
- `page` / `page_size` — 分页

### 5.3 开发者接口

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| GET | `/api/developer/items` | 我提交的列表 | 开发者 |
| POST | `/api/developer/items` | 提交新插件/主题 | 开发者 |
| PUT | `/api/developer/items/:id` | 修改信息（生成 edit_request） | 开发者 |
| POST | `/api/developer/items/:id/versions` | 上传新版本 | 开发者 |

### 5.4 管理员接口

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| GET | `/api/admin/items` | 所有插件/主题（含待审） | 管理员 |
| PUT | `/api/admin/items/:id/approve` | 审核通过 | 管理员 |
| PUT | `/api/admin/items/:id/reject` | 审核驳回 | 管理员 |
| PUT | `/api/admin/items/:id/suspend` | 下架 | 管理员 |
| DELETE | `/api/admin/items/:id` | 删除 | 管理员 |
| GET | `/api/admin/edit-requests` | 修改审核列表 | 管理员 |
| PUT | `/api/admin/edit-requests/:id/approve` | 批准修改 | 管理员 |
| PUT | `/api/admin/edit-requests/:id/reject` | 驳回修改 | 管理员 |
| GET | `/api/admin/users` | 用户列表 | 管理员 |
| PUT | `/api/admin/users/:id` | 管理用户（封禁等） | 管理员 |
| GET | `/api/admin/stats` | 统计概览 | 管理员 |

### 5.5 文件上传

| 方法 | 路径 | 说明 | 认证 |
|------|------|------|------|
| POST | `/api/upload/image` | 上传主图/截图 | 是 |
| POST | `/api/upload/zip` | 上传 zip 包 | 是 |

---

## 六、前端页面

### 6.1 页面清单

| 页面 | 路由 | 说明 |
|------|------|------|
| 首页 | `/` | 展示插件/主题列表，支持搜索、分类筛选、排序 |
| 详情页 | `/detail.html?id={id}` | 插件/主题详细介绍、版本、截图、下载 |
| 登录页 | `/login.html` | 用户名/密码登录 |
| 注册页 | `/register.html` | 用户名/邮箱/密码注册 |
| 提交页 | `/submit.html` | 开发者提交新插件/主题 |
| 开发者中心 | `/developer.html` | 管理已提交列表、查看状态、修改信息 |
| 开发者-修改密码 | `/password.html` | 修改登录密码 |
| 管理员后台 | `/admin.html` | 所有内容审核、管理 |
| 管理员-修改密码 | `/admin-password.html` | 修改登录密码 |

### 6.2 首页功能

- 顶部导航（Logo、搜索框、登录/注册/用户菜单）
- 类型切换 Tab（插件 / 主题）
- 筛选：分类、价格区间
- 排序：最新、最多下载、价格
- 卡片列表（主图、名称、版本、作者、价格、下载量、简介摘要）
- 分页

### 6.3 详情页

- 主图轮播/大图
- 名称、版本、作者、价格、下载量
- 简介（HTML 渲染，sanitize 后展示）
- 截图展示
- 版本历史
- 下载按钮（记录下载日志）
- 分类与标签

### 6.4 开发者管理页

- 我的插件/主题列表（表格 + 状态标签）
- 每个条目可操作：查看详情、修改信息、上传新版本
- 修改信息后进入 `pending` 状态等待管理员审核
- 审核中/被驳回的状态展示

### 6.5 管理员后台

- 统计卡片（总数、待审数、用户数、总下载量）
- 审核队列（待审核列表，支持通过 / 驳回 / 备注）
- 全部内容管理（搜索、筛选、下架、删除）
- 修改审核队列（开发者信息修改待批）
- 用户管理（列表、封禁/解封）

---

## 七、业务流程

### 7.1 插件/主题提交

```
开发者登录 → 提交页 → 填写信息 + 上传 zip
    ↓
系统解压 zip 校验结构
    ├── 校验失败 → 返回错误（缺少 plugin.json/theme.json 等）
    └── 校验成功 → 读取 metadata → 存入数据库 (status=pending)
                        ↓
                  管理员审核
                  ├── 通过 → 状态改为 approved，公开可见
                  └── 驳回 → 状态改为 rejected，附审核备注
```

### 7.2 开发者修改信息

```
开发者登录 → 开发者中心 → 修改某插件信息
    ↓
新信息 → 存入 edit_requests 表（status=pending）
    ↓
管理员审核
  ├── 通过 → 更新 items 表
  └── 驳回 → 保留旧值，附驳回原因
```

### 7.3 下载流程

```
用户点击下载 → 后端 +1 downloads（items 表）
    → 写入 download_logs（user_id / ip / time）
    → 以附件形式返回 zip 文件
```

---

## 八、安全规范

1. 所有密码使用 bcrypt 哈希存储
2. Session/Cookie 使用 httpOnly、sameSite 策略
3. 文件上传限制：
   - zip 最大 50MB
   - 图片最大 5MB（jpg/png/gif/webp）
4. zip 解压前校验：
   - 不包含 `..` 路径穿越
   - 不包含系统文件
   - 解压后总文件数不超过 500
   - 单个文件不超过 10MB
5. HTML 简介使用 DOMPurify（前端）或 sanitize-html（后端）过滤 XSS
6. API 鉴权：Session 机制，管理接口校验 role=admin
7. zip 下载路径校验：仅允许下载已审核通过的文件
8. 上传目录不可直接通过 URL 访问（由后端托管流式返回）

---

## 九、非功能需求

1. 响应式设计，支持移动端基础浏览
2. 后端静态文件托管（LayUI、自定义 HTML/CSS/JS）
3. 日志记录（操作日志输出到控制台/文件）
4. zip 大小、下载量、价格、作者等信息在首页列表和详情页正确展示
5. 首页列表支持分页，每页默认 12 条
6. 数据库初始化自动建表

---

## 十、目录结构规划

```
MNBT_Store/
├── PRD.md                    # 本文档
├── package.json
├── tsconfig.json
├── src/
│   ├── index.ts              # Express 入口，静态文件托管 + API 路由挂载
│   ├── db.ts                 # SQLite 初始化与连接
│   ├── auth.ts               # 登录/注册/密码修改
│   ├── routes/
│   │   ├── auth.ts           # 认证路由
│   │   ├── items.ts          # 公开接口
│   │   ├── developer.ts      # 开发者接口
│   │   ├── admin.ts          # 管理员接口
│   │   └── upload.ts         # 文件上传
│   ├── services/
│   │   ├── item.service.ts   # 插件/主题业务逻辑
│   │   ├── user.service.ts   # 用户业务逻辑
│   │   └── zip.service.ts    # zip 校验与解压
│   └── middleware/
│       ├── auth.ts           # Session 鉴权中间件
│       └── upload.ts         # multer 文件处理中间件
├── public/                   # 前端文件（由后端托管）
│   ├── index.html            # 首页
│   ├── detail.html           # 详情页
│   ├── login.html            # 登录页
│   ├── register.html         # 注册页
│   ├── submit.html           # 提交页
│   ├── developer.html        # 开发者中心
│   ├── password.html         # 修改密码（开发者）
│   ├── admin.html            # 管理员后台
│   ├── admin-password.html   # 修改密码（管理员）
│   ├── css/
│   │   └── style.css         # 自定义样式
│   ├── js/
│   │   └── app.js            # 通用 JS 逻辑
│   └── lib/
│       └── layui/            # LayUI 库文件
├── uploads/                  # 上传文件存储（zip + 图片）
│   ├── images/
│   └── packages/
├── data/
│   └── store.db             # SQLite 数据库文件
└── logs/                     # 日志目录
```

---

## 十一、待定/未来扩展

- [ ] 付费插件支付集成
- [ ] 用户评价/评分系统
- [ ] 插件依赖声明
- [ ] 在线安装接口（供 MNBT 面板直接从商店安装）
- [ ] 邮件通知（审核结果、密码重置）
- [ ] API Token 认证（供面板集成）
- [ ] RABC 权限细化
