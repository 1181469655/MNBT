---
title: docker_shop Docker 售卖插件 PRD
description: Docker 容器售卖插件（docker_shop）与 tdesign 用户中心 account 主题改造的产品需求文档
---

# docker_shop Docker 售卖插件 PRD

> 版本：v0.1（初稿）
> 日期：2026-08-10
> 状态：评审中
> 关联文档：[插件开发手册](../development/plugin/guide.md)、[hosting_shop 主机售卖插件](../development/plugin/builtin/hosting-shop.md)、[Docker 集成 PRD](./docker.md)、[Docker 魔方财务对接](../integration/idcsmart-docker.md)

## 1. 背景与目标

### 1.1 背景

MNBT 已具备两套并行业务线：

1. **虚拟主机业务**（`MN_zj` / `MN_bt`）：通过 `hosting_shop` 插件实现了完整的售卖闭环——管理员配置套餐价格、用户浏览下单、余额/在线支付、`order.paid` 钩子自动开通。
2. **Docker 容器业务**（`MN_docker_user` / `MN_docker_plan` / `MN_docker_node`）：已有独立控制台（`docker/` scope）、独立认证（`docker_token`）、管理员端节点/套餐/用户管理，以及魔方财务对接（`api/docker.php`）。但 **Docker 没有自营售卖闭环**——管理员无法通过后台把 Docker 配额套餐挂上价格卖给 user_info 用户。

当前只有 `hosting_shop` 主机售卖插件，缺少 Docker 售卖插件；tdesign 主题的 **account（用户中心）** 部分仅展示主机商城（ShopView/HostingView/OrdersView），无法售卖和查看 Docker 资产。

### 1.2 目标

1. 新增 **docker_shop** 插件，参照 `hosting_shop` 模式实现 Docker 售卖闭环：
   - 管理员配置「售卖套餐」（绑定现有 Docker 配额套餐 + 固定开通节点 + 周期价格）
   - 用户（user_info 账号）浏览 Docker 套餐、下单、支付（余额/在线支付）
   - 支付成功后 `order.paid` 钩子自动开通 Docker 账号（写入 `MN_docker_user`）
   - 资产页展示 Docker 账号凭据与容器状态，一键进入 Docker 控制台
2. 改造 **tdesign 主题 account 部分**：新增「Docker 商城 / 我的 Docker / Docker 订单」菜单与页面，与现有主机商城并列。

### 1.3 非目标（本期不做）

- 不接管/修改现有 Docker 控制台（`docker/` scope）与独立认证逻辑
- 不改动 `MN_docker_user` / `MN_docker_plan` / `MN_docker_node` 核心表结构
- 不做 Docker 套餐自助续费（续费由管理员后台人工处理，后续版本可加）
- 不做多容器/自定义镜像购买（沿用单容器模型，容器由用户在控制台创建）

## 2. 现状与差距

| 环节 | hosting_shop（主机） | Docker（现状） | 差距 |
|------|----------------------|----------------|------|
| 套餐价格配置 | `MN_plugin_hosting_plan`（周期价格） | `MN_docker_plan` 仅 `jg` 单价格，无周期 | 缺售卖价格模型 |
| 用户下单 | P2 路由 `/shop/*` + `MN_dd` 支付 | 无 | 缺购买链路 |
| 自动开通 | `order.paid` → `hosting_open_host()` 写 `MN_zj` | `api/docker.php?gn=kt` 仅外部 API | 缺内部开通函数 |
| 用户资产页 | `MN_plugin_hosting_asset` + HostingView | 无自营资产 | 缺资产模型 |
| 用户中心入口 | account SPA「商城」分组 | 无 | 缺菜单/页面 |

## 3. 范围（P0）

- `docker_shop` 插件：3 张插件自有表、开通逻辑、订单/资产管理、管理员端页面
- tdesign account SPA：新增 Docker 商城/资产/订单三个页面与菜单入口
- 文档：本 PRD + 内置插件文档

## 4. 架构总览

```
user_info 用户（account SPA / tdesign 主题）
   │ 浏览 /docker-shop/api/*（P2 路由）
   ▼
docker_shop 插件
   ├─ 售卖套餐 MN_plugin_docker_plan ──引用──▶ MN_docker_plan（配额，复用控制台强制）
   ├─ 订单 MN_plugin_docker_order ──关联──▶ MN_dd.ddh（支付系统）
   ├─ 资产 MN_plugin_docker_asset ──记录──▶ MN_docker_user（Docker 账号）
   │
   └─ order.paid 钩子（lx=docker）→ docker_shop_open_account()
        │  生成账号密码 / 计算到期 / 写 MN_docker_user
        ▼
   Docker 控制台（docker/login.php，docker_token 独立登录）
```

关键设计：**售卖套餐与配额套餐分离**。

- `MN_docker_plan` 是 Docker 配额基准（`cpu_max/mem_max/disk_max/proxy_max`），由 Docker 控制台与管理员 Docker 套餐页管理，**不动核心表**。
- `MN_plugin_docker_plan` 是售卖配置（周期价格 + 固定节点 + 绑定基准套餐），由插件管理。
- 开通时 `MN_docker_user.plan_id = 基准套餐 id`、`MN_docker_user.ssbt = 节点 id`，Docker 控制台的配额强制逻辑直接生效。

## 5. 数据库设计（插件自有表，前缀 `MN_plugin_`）

### 5.1 `MN_plugin_docker_plan` — 售卖套餐

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| name | VARCHAR(120) | 售卖套餐名称 |
| description | TEXT | 售卖介绍 |
| category | VARCHAR(50) | 分类 |
| node | INT | 固定开通节点 `MN_docker_node.id` |
| base_plan_id | INT | 绑定配额套餐 `MN_docker_plan.id` |
| price_month_cents | INT | 月付价格（分） |
| price_quarter_cents | INT | 季付价格（分） |
| price_half_year_cents | INT | 半年付价格（分） |
| price_year_cents | INT | 年付价格（分） |
| price_two_year_cents | INT | 两年付价格（分） |
| price_three_year_cents | INT | 三年付价格（分） |
| enabled_periods | VARCHAR(255) | 允许周期，逗号分隔 |
| status | VARCHAR(20) | active/inactive |
| sort | INT | 排序 |
| created_at / updated_at | VARCHAR(50) | 时间戳 |

### 5.2 `MN_plugin_docker_order` — 订单

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| user_id | INT | user_info 用户 ID |
| plan_id | INT | 售卖套餐 ID |
| plan_name | VARCHAR(120) | 套餐名称快照 |
| period | VARCHAR(10) | 周期 |
| amount_cents | INT | 金额（分） |
| order_no | VARCHAR(64) | 关联 `MN_dd.ddh` |
| node | INT | 开通节点 |
| docker_user_id | INT | 开通后回填 `MN_docker_user.id` |
| status | VARCHAR(20) | pending/paid/opened/failed/cancelled |
| remark | TEXT | 备注/失败原因/账号密码 |
| created_at / paid_at / opened_at | VARCHAR(50) | 时间 |

### 5.3 `MN_plugin_docker_asset` — 资产

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| user_id | INT | 所属用户 |
| order_id | INT | 开通订单 |
| docker_user_id | INT | `MN_docker_user.id` |
| plan_id | INT | 售卖套餐 ID |
| plan_name | VARCHAR(120) | 套餐名快照 |
| docker_username | VARCHAR(64) | Docker 控制台登录名（明文冗余，同 `MN_zj.user` 惯例） |
| docker_password | VARCHAR(64) | Docker 控制台登录密码（明文冗余，同 `MN_zj.pass` 惯例） |
| expire_at | VARCHAR(50) | 到期时间（0000-00-00=永久） |
| status | VARCHAR(20) | active/expired/cancelled |
| created_at | VARCHAR(50) | 创建时间 |

## 6. 插件设计

### 6.1 依赖

- **user_info**（认证）：购买用户即 user_info 用户
- **balance**（支付）：余额扣款作为可用支付方式
- 核心 Docker 能力（`MN_docker_user` / `MN_docker_plan` / `MN_docker_node`、`docker_auth_password_hash()`）——非插件依赖，按核心函数存在性保护

### 6.2 访问路径（P2 路由）

| 页面 | URL |
|------|-----|
| Docker 商城 | `index.php?_r=/docker-shop` |
| Docker 下单 | `index.php?_r=/docker-shop/order/{plan_id}` |
| 我的 Docker | `index.php?_r=/docker-shop/assets` |
| Docker 订单 | `index.php?_r=/docker-shop/orders` |
| 控制台入口 | `{base}/docker/login.php`（独立登录） |

### 6.3 API 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/docker-shop/api/plans` | 售卖套餐列表（含基准配额展示） |
| GET | `/docker-shop/api/plan/{plan_id}` | 套餐详情 + 支付方式 |
| GET | `/docker-shop/api/assets` | 我的 Docker 资产 |
| GET | `/docker-shop/api/orders` | Docker 订单（分页） |
| GET | `/docker-shop/api/methods` | 可用支付方式 |
| POST | `/docker-shop/api/create_order` | 创建订单（创建 `MN_dd` lx=docker + 分发支付网关） |
| POST | `/docker-shop/api/reset_password` | 重置 Docker 账号密码（返回新密码一次） |

### 6.4 核心流程

```
用户选择售卖套餐 → 校验周期/价格/节点/基准套餐
  → 创建 MN_plugin_docker_order(status=pending)
  → 创建 MN_dd(lx=docker, zffs=支付方式) → 分发支付网关
  → 支付回调 → mnbt_pay_settle_order → order.paid 钩子
  → docker_shop_open_account()：
      1. 校验订单 paid 未开通
      2. 生成 Docker 登录名（优先 user_info username，冲突则加随机后缀）
      3. 生成随机密码（12 位），docker_auth_password_hash 写入
      4. 计算到期时间（周期 months），写入 MN_docker_user
         （plan_id=基准套餐, ssbt=节点, qk=active, container_status=none）
      5. 写入 MN_plugin_docker_asset（含明文账号密码）
      6. 回填订单 status=opened, docker_user_id
      7. 触发 docker.user.created 钩子
  → 用户在前端资产页查看凭据 → 前往 Docker 控制台登录
```

**0 元免费套餐**：下单后直接标记 paid 并开通，不经过支付网关（同 hosting_shop）。

### 6.5 管理员端

| 页面 | 入口 |
|------|------|
| 售卖套餐管理 | `plugin.php?p=docker_shop&page=plans` |
| 售卖套餐编辑 | `plugin.php?p=docker_shop&page=plan_edit&id={id}` |
| 订单管理 | `plugin.php?p=docker_shop&page=orders` |
| 资产管理 | `plugin.php?p=docker_shop&page=assets` |

侧边栏菜单「Docker 售卖」（三级结构，同 hosting_shop）。套餐编辑页提供「绑定配额套餐（`MN_docker_plan` 下拉）+ 固定节点（`MN_docker_node` 下拉）+ 周期价格 + 启用周期」表单。

### 6.6 钩子

| 钩子 | 优先级 | 说明 |
|------|--------|------|
| `order.paid` | 20 | 处理 `lx=docker` 订单：防重复 → 标记 paid → 开通 |

### 6.7 安全边界

- 所有 API 路由前置 `user_info_auth_current()` 校验，未登录返回 `not_login`
- 开通函数禁止由前端直接调用（仅 `order.paid` 钩子与 0 元下单内部触发）
- 管理员页面沿用 `mnbt_plugin_require_admin()` 鉴权
- 密码仅明文存资产表（同 `MN_zj.pass` 惯例），API 返回资产列表时**剔除 `password_hash`**
- 节点信息仅返回 id/名称，不暴露 `btip/btdk/ptl/btmy`

## 7. tdesign 主题 account 部分改造

### 7.1 入口 boot（`templates/tdesign/account/_spa_boot.php`）

- `plugins.docker_shop`：插件启用标志（account SPA 据此显示 Docker 菜单）
- `dockerUrl`：Docker 控制台登录入口（`{base}/docker/login.php`）

### 7.2 SPA 前端（`templates/tdesign/spa/src/account/`）

| 文件 | 改动 |
|------|------|
| `api/plugins.js` | 新增 docker_shop API 封装：`getDockerShopPlans` / `getDockerShopAssets` / `getDockerShopOrders` / `createDockerShopOrder` / `resetDockerPassword` / `dockerConsoleUrl` |
| `router/index.js` | 新增路由：`/docker-shop`、`/docker-assets`、`/docker-orders` |
| `layouts/AccountLayout.vue` | 新增「Docker」侧栏分组（Docker 商城 / 我的 Docker / Docker 订单），用户菜单下拉补充入口 |
| `views/DockerShopView.vue` | 售卖套餐卡片 + 下单对话框（复用 ShopView 交互：周期 + 支付方式 + 0 元提示） |
| `views/DockerAssetsView.vue` | Docker 资产卡片：账号密码、到期时间、容器状态（含「刷新状态」「重置密码」）、进入控制台 |
| `views/DockerOrdersView.vue` | Docker 订单列表（分页，状态标签） |
| `views/DashboardView.vue` | 概览卡片与快速入口按 `plugins.docker_shop` 追加 Docker 资产数/入口 |

### 7.3 构建

`cd templates/tdesign/spa && npm run build:account`，产物输出到 `templates/tdesign/account/dist/`。

## 8. 文件结构

```
app_plugins/docker_shop/
├── plugin.json
├── bootstrap.php            # 主入口（钩子 + 路由 + 管理员页面 + 菜单 + AJAX）
├── install.sql
├── uninstall.sql
├── lib/
│   └── docker_shop.php      # 周期/URL/认证/套餐/订单/资产/开通
├── assets/
│   └── style.css
└── views/
    ├── layout.php           # 用户端公共布局
    ├── shop.php             # Docker 商城（非 SPA 兜底）
    ├── order.php            # 下单页（兜底）
    ├── assets.php           # 我的 Docker（兜底）
    ├── orders.php           # Docker 订单（兜底）
    └── admin/
        ├── plans.php        # 售卖套餐管理
        ├── plan_edit.php    # 售卖套餐编辑
        ├── orders.php       # 订单管理
        └── assets.php       # 资产管理
```

## 9. 自测清单

- [ ] 插件可安装/启用；后台侧栏出现「Docker 售卖」菜单
- [ ] 管理员可创建售卖套餐（绑定配额套餐 + 节点 + 周期价格）；下架后用户端不可见
- [ ] 未登录访问 `/docker-shop` 跳登录
- [ ] 余额支付购买成功 → `order.paid` → Docker 账号开通，订单/资产状态正确
- [ ] 0 元套餐直接开通
- [ ] 同一用户名重复购买生成不冲突的 Docker 登录名
- [ ] 在线支付（epay）购买成功回调后同样开通
- [ ] account SPA：Docker 菜单按插件启用与否显隐；资产页凭据可查看、可重置密码
- [ ] 用开通的 Docker 账号可在 `/docker/login.php` 登录控制台，配额套餐生效
- [ ] 禁用插件后菜单/路由失效
