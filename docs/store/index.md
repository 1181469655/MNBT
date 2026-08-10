---
title: 插件商店 & 主题商店
description: MNBT 插件商店与主题商店 Web 应用的需求规格，含架构、数据库、API、页面与业务流程
---

# 插件商店 & 主题商店

为 MNBT 平台搭建独立的「插件商店」与「主题商店」Web 应用，供开发者上传、分发，用户浏览、下载。

---

## 技术架构

| 层 | 选型 |
|----|------|
| 后端 | Node.js + TypeScript（Express） |
| 前端 | 原生 HTML + LayUI |
| 数据库 | SQLite |
| 文件存储 | 本地文件系统（zip/图片存放于可配置目录） |

---

## 识别规则

### 插件识别

| 项目 | 规则 |
|------|------|
| 目录名 | 插件 slug，仅允许 `a-z A-Z 0-9 _ -`，最长 63 字符 |
| 必含文件 | `plugin.json` + `bootstrap.php` |
| `plugin.json` 必填字段 | `name`（显示名）、`version`（版本号） |
| `plugin.json` 可选字段 | `id`、`author`、`description`、`requires_mnbt`、`type` |
| 可选目录 | `admin/`、`user/`、`assets/`、`install.sql`、`uninstall.sql` |
| zip 根层 | 解压后第一层必须有且仅有一个目录 |

### 主题识别

| 项目 | 规则 |
|------|------|
| 目录名 | 主题 ID，仅允许 `a-zA-Z0-9_-` |
| 必含文件 | `theme.json` |
| `theme.json` 必填字段 | `title`（显示名） |
| `theme.json` 可选字段 | `name`、`version`、`description`、`author`、`scope` |
| 可选目录 | `user/`、`admin/`（至少存在一个视为有效） |
| zip 根层 | 解压后第一层必须有且仅有一个目录 |

---

## 数据库表

| 表 | 说明 |
|----|------|
| `users` | 用户（username/password/email/role/status） |
| `items` | 插件/主题主表（type/slug/name/version/price/downloads/status） |
| `item_versions` | 版本历史 |
| `download_logs` | 下载日志 |
| `reviews` | 评价表（预留） |
| `edit_requests` | 修改审核表 |

联合唯一约束 `(type, slug)`。

---

## API 设计

所有 API 以 `/api` 为前缀，Session 鉴权。

### 认证

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/api/auth/register` | 用户注册 |
| POST | `/api/auth/login` | 用户登录 |
| POST | `/api/auth/logout` | 退出登录 |
| GET | `/api/auth/me` | 当前用户信息 |
| PUT | `/api/auth/password` | 修改密码 |

### 商店公开接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/items` | 列表（筛选/分页/排序） |
| GET | `/api/items/:id` | 详情 |
| GET | `/api/items/:id/download` | 下载 |
| GET | `/api/items/:id/versions` | 版本历史 |

### 开发者接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/developer/items` | 我的列表 |
| POST | `/api/developer/items` | 提交新插件/主题 |
| PUT | `/api/developer/items/:id` | 修改信息 |
| POST | `/api/developer/items/:id/versions` | 上传新版本 |

### 管理员接口

| 方法 | 路径 | 说明 |
|------|------|------|
| PUT | `/api/admin/items/:id/approve` | 审核通过 |
| PUT | `/api/admin/items/:id/reject` | 驳回 |
| PUT | `/api/admin/items/:id/suspend` | 下架 |
| GET | `/api/admin/stats` | 统计概览 |

---

## 前端页面

| 页面 | 路由 | 说明 |
|------|------|------|
| 首页 | `/` | 列表/搜索/分类筛选/排序/分页 |
| 详情页 | `/detail.html?id={id}` | 介绍/版本/截图/下载 |
| 登录页 | `/login.html` | 用户名/密码 |
| 注册页 | `/register.html` | 用户名/邮箱/密码 |
| 提交页 | `/submit.html` | 开发者提交 |
| 开发者中心 | `/developer.html` | 管理已提交列表 |
| 管理员后台 | `/admin.html` | 审核、用户管理 |

---

## 提交流程

```
开发者提交 → 系统校验 zip（路径穿越/文件数/大小/必含文件）
  ↓
校验失败 → 返回错误
校验成功 → 读取 metadata → 入库（status=pending）
  ↓
管理员审核 → 通过（approved，公开可见）/ 驳回（rejected，附备注）
```

---

## 安全规范

- 密码 bcrypt 哈希存储
- Session/Cookie httpOnly
- zip 最大 50MB，图片最大 5MB
- zip 解压前校验路径穿越、文件数 ≤500、单文件 ≤10MB
- HTML 简介使用 DOMPurify/sanitize-html 防 XSS
- 上传目录不可直接 URL 访问

> 完整 API 文档见 [商店 API](./api.md)
