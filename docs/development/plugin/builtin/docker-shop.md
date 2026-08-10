---
title: docker_shop Docker 售卖插件
description: Docker 容器售卖系统插件：管理员配置售卖套餐（绑定 Docker 配额套餐与固定节点、设置周期价格），用户购买后自动开通 Docker 账号，可在控制台创建容器
---

# docker_shop Docker 售卖插件

Docker 容器售卖系统。管理员配置售卖套餐（绑定 Docker 配额套餐与固定节点、设置周期价格），用户购买后自动开通 Docker 账号，登录控制台创建容器。

## 功能

- 管理员端：售卖套餐管理（增删改查）、订单管理、资产管理
- 用户端：套餐浏览、下单购买、我的 Docker（密码重置/状态刷新）、订单记录
- 购买流程：创建订单 → 支付（含余额支付）→ 自动开通 Docker 账号（bcrypt 密码、回填节点）
- 售卖套餐与配额套餐分离：售卖套餐决定「卖多少钱、开在哪个节点」，配额套餐（`MN_docker_plan`）决定「容器能占多少资源」
- 支持月付/季付/半年付/年付/两年付/三年付周期，0 元套餐免支付直接开通
- tdesign 主题用户中心（account SPA）内置 Docker 商城 / 我的 Docker / Docker 订单页面

## 依赖

- **user_info**（认证：`user_info_auth_current()`）
- **balance**（余额支付，可选）
- **Docker 核心模块**（`MN_docker_node` / `MN_docker_plan` / `MN_docker_user`、`MPHX/docker.member.php`、`docker/head.php`）

安装时 `plugin.json` 的 `requires_plugins` 字段会强制检查 `user_info` 与 `balance` 依赖。

## 安装

1. 先安装并启用 `user_info`、`balance` 插件
2. 在后台「节点管理」添加 Docker 节点，在「配额套餐」创建配额套餐（CPU/内存/磁盘/代理限制）
3. 安装并启用 `docker_shop` 插件
4. 自动创建 `MN_plugin_docker_plan` / `MN_plugin_docker_order` / `MN_plugin_docker_asset` 表
5. 进入「Docker 售卖 → 售卖套餐」新增套餐：绑定配额套餐、选择固定节点、勾选周期并填写价格

## 数据表

### MN_plugin_docker_plan（售卖套餐）

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
| enabled_periods | VARCHAR(255) | 允许周期，逗号分隔（空则按价格>0 推断） |
| status | VARCHAR(20) | active/inactive |
| sort | INT | 排序（小到大） |
| created_at / updated_at | VARCHAR(50) | 创建/更新时间 |

### MN_plugin_docker_order（订单）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| user_id | INT | 购买者 user_info 用户 ID |
| plan_id / plan_name | INT / VARCHAR | 套餐 ID 与名称快照 |
| period | VARCHAR(10) | 购买周期 |
| amount_cents | INT | 订单金额（分） |
| order_no | VARCHAR(64) | 关联 `MN_dd.ddh` |
| node | INT | 开通节点 `MN_docker_node.id` |
| docker_user_id | INT | 开通后回填 `MN_docker_user.id` |
| status | VARCHAR(20) | pending/paid/opened/failed/cancelled |
| remark | TEXT | 备注/失败原因 |
| created_at / paid_at / opened_at | VARCHAR(50) | 下单/支付/开通时间 |

### MN_plugin_docker_asset（资产）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| user_id | INT | 所属 user_info 用户 ID |
| order_id | INT | 开通订单 ID |
| docker_user_id | INT | `MN_docker_user.id` |
| plan_id / plan_name | INT / VARCHAR | 套餐 ID 与名称快照 |
| docker_username | VARCHAR(64) | Docker 控制台登录名 |
| docker_password | VARCHAR(64) | Docker 控制台登录密码 |
| expire_at | VARCHAR(50) | 到期时间（0000-00-00=永久） |
| status | VARCHAR(20) | active/expired/cancelled |
| created_at | VARCHAR(50) | 开通时间 |

## 访问路径

| 页面 | URL |
|------|-----|
| 套餐列表 | `index.php?_r=/docker-shop` |
| 下单页 | `index.php?_r=/docker-shop/order/{plan_id}` |
| 我的 Docker | `index.php?_r=/docker-shop/assets` |
| 我的订单 | `index.php?_r=/docker-shop/orders` |

## API 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/docker-shop/api/plans` | 上架套餐列表（含配额/节点/价格） |
| GET | `/docker-shop/api/plan/{plan_id}` | 套餐详情 + 支付方式 |
| GET | `/docker-shop/api/assets` | 我的 Docker 资产 |
| GET | `/docker-shop/api/orders` | 我的订单（分页） |
| GET | `/docker-shop/api/methods` | 可用支付方式 |
| POST | `/docker-shop/api/create_order` | 创建订单（0 元直接开通，否则跳转支付） |
| POST | `/docker-shop/api/reset_password` | 重置 Docker 密码（校验资产归属） |
| POST | `/docker-shop/api/sync_status` | 刷新容器状态（查询宝塔已安装应用） |

## 管理员端

| 页面 | 入口 |
|------|------|
| 售卖套餐 | `plugin.php?p=docker_shop&page=plans` |
| 套餐编辑 | `plugin.php?p=docker_shop&page=plan_edit&id={id}` |
| 订单管理 | `plugin.php?p=docker_shop&page=orders` |
| 资产管理 | `plugin.php?p=docker_shop&page=assets` |

侧边栏菜单「Docker 售卖」（order=61）。

## 钩子

| 钩子 | 优先级 | 说明 |
|------|--------|------|
| `order.paid` | 20 | 支付成功后处理 `lx=docker` 订单：标记 paid → 调用 `docker_shop_open_account()` 自动开通 |

## 核心流程

```
管理员配置售卖套餐（绑定配额套餐 + 固定节点 + 周期价格）
  → 用户选择套餐下单
  → 0 元：直接标记 paid 并开通
  → 非 0 元：创建 MN_dd（lx=docker）→ 支付网关 → 支付成功触发 order.paid
  → docker_shop_open_account() 写入 MN_docker_user（bcrypt 密码、回填节点）
  → 写入 MN_plugin_docker_asset（明文密码，控制台登录用）
  → 回填订单 docker_user_id / status=opened
```

## 核心 API

| 函数 | 说明 |
|------|------|
| `docker_shop_require_user()` | 要求登录，返回 user_info 用户数组 |
| `docker_shop_url($path)` | 生成插件页面 URL |
| `docker_shop_admin_url($page, $extra)` | 生成管理员页面 URL |
| `docker_shop_format_cents($cents)` | 分→元格式化 |
| `docker_shop_plan_list_active()` | 上架套餐列表 |
| `docker_shop_plan_to_api($plan)` | 套餐 → 用户端 JSON（含 base_plan/node/prices） |
| `docker_shop_order_create($user, $plan, $period)` | 创建购买订单 |
| `docker_shop_order_set_status($id, $status, $remark)` | 更新订单状态 |
| `docker_shop_open_account($order_id)` | 开通 Docker 账号（核心） |
| `docker_shop_reset_password($asset_id)` | 重置 Docker 密码（同步资产表明文） |
| `docker_shop_sync_container_status($asset)` | 同步容器状态（复用 `docker_find_my_installed_app`） |

## 文件结构

```
docker_shop/
├── plugin.json
├── bootstrap.php          # 主入口（钩子 + P2 路由 + API + 管理员页面 + AJAX）
├── install.sql
├── uninstall.sql
├── lib/
│   └── docker_shop.php    # 核心库（套餐/订单/资产/开通/重置/同步）
├── assets/
│   └── style.css          # Layui 风格覆盖（与 hosting_shop 共用 hs-* 语义类）
└── views/
    ├── layout.php         # 用户端公共布局
    ├── shop.php           # 套餐列表（兜底主题）
    ├── order.php          # 下单页（兜底主题）
    ├── assets.php         # 我的 Docker（兜底主题）
    ├── orders.php         # 我的订单（兜底主题）
    └── admin/
        ├── plans.php      # 管理员-售卖套餐
        ├── plan_edit.php  # 管理员-套餐编辑
        ├── orders.php     # 管理员-订单管理
        └── assets.php     # 管理员-资产管理
```

## 主题集成

tdesign 主题用户中心（account SPA）在 `plugins.docker_shop` 为 true 时展示：

- 侧栏「Docker」分组：Docker 商城 / 我的 Docker / Docker 订单
- 概览页「我的 Docker」资产数卡片与快速入口
- Docker 控制台入口（`boot.dockerUrl` → `{base}/docker/`）
- 视图文件：`templates/tdesign/spa/src/account/views/DockerShopView.vue` / `DockerAssetsView.vue` / `DockerOrdersView.vue`
- API 封装：`templates/tdesign/spa/src/account/api/plugins.js`（`getDockerShopPlans` 等）

## 相关文档

- [user_info](./user-info.md) — 认证依赖插件
- [balance](./balance.md) — 余额支付依赖插件
- [Docker 集成 PRD](../../../prd/docker.md) — Docker 核心模块表结构
- [docker_shop Docker 售卖 PRD](../../../prd/docker-shop.md) — 本插件设计文档
- [插件开发手册](../guide.md) — 插件开发手册
