# hosting_shop 主机售卖插件

虚拟主机售卖系统，管理员配置套餐与价格，用户可购买主机、查看资产与订单。购买时调用宝塔 API 自动开通。

## 功能

- 管理员端：套餐管理（增删改查）、订单管理、资产管理
- 用户端：套餐浏览、下单购买、我的主机、订单记录
- 购买流程：创建订单 → 余额支付 → 自动开通（调用宝塔 API）
- 支持月付/年付周期

## 依赖

- **user_info**（认证）
- **balance**（支付：用户余额扣款）

安装时 `plugin.json` 的 `requires_plugins` 字段会强制检查依赖。

## 安装

1. 先安装并启用 `user_info` 和 `balance` 插件
2. 安装并启用 `hosting_shop` 插件
3. 自动创建 `MN_plugin_hosting_plan` 和 `MN_plugin_hosting_order` 表

## 数据表

### MN_plugin_hosting_plan

套餐配置表。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| name | VARCHAR(100) | 套餐名称 |
| description | TEXT | 套餐描述 |
| price_monthly_cents | INT | 月付价格（分） |
| price_yearly_cents | INT | 年付价格（分） |
| node | VARCHAR(100) | 开通节点（宝塔面板标识） |
| specs | TEXT | 规格 JSON（磁盘/流量/数据库等） |
| status | TINYINT | 1=上架 0=下架 |
| sort | INT | 排序 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### MN_plugin_hosting_order

订单表。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| order_no | VARCHAR(64) | 订单号（唯一） |
| user_id | INT | 用户 ID |
| plan_id | INT | 套餐 ID |
| plan_name | VARCHAR(100) | 套餐名称（冗余） |
| period | VARCHAR(10) | 周期：month/year |
| amount_cents | INT | 金额（分） |
| node | VARCHAR(100) | 开通节点 |
| host_id | INT | 开通后的主机 ID（关联 MN_zj） |
| status | VARCHAR(20) | pending/paid/opened/failed/cancelled |
| created_at | DATETIME | 下单时间 |
| opened_at | DATETIME | 开通时间 |
| remark | VARCHAR(255) | 备注 |

## 访问路径

| 页面 | URL |
|------|-----|
| 套餐列表 | `index.php?_r=/shop` |
| 下单页 | `index.php?_r=/shop/order/{plan_id}` |
| 我的主机 | `index.php?_r=/shop/assets` |
| 我的订单 | `index.php?_r=/shop/orders` |

## API 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/shop/api/create_order` | 创建订单（余额扣款 + 触发开通） |

## 管理员端

| 页面 | 入口 |
|------|------|
| 套餐管理 | `plugin.php?p=hosting_shop&page=plans` |
| 套餐编辑 | `plugin.php?p=hosting_shop&page=plan_edit&id={id}` |
| 订单管理 | `plugin.php?p=hosting_shop&page=orders` |
| 资产管理 | `plugin.php?p=hosting_shop&page=assets` |

## 钩子

| 钩子 | 优先级 | 说明 |
|------|--------|------|
| `order.paid` | 20 | 订单支付成功后触发 `hosting_open_host()` 调用宝塔 API 开通主机 |

## 核心流程

```
用户选择套餐 → 创建订单(MN_plugin_hosting_order, status=pending)
  → 余额扣款(balance_deduct, status=paid)
  → 触发 order.paid 钩子
  → hosting_open_host() 调用宝塔 API 创建站点
  → 写入 MN_zj 表，更新订单 status=opened, host_id=xxx
```

## 核心 API

| 函数 | 说明 |
|------|------|
| `hosting_require_user()` | 要求登录，返回用户数组 |
| `hosting_format_cents($cents)` | 分→元格式化 |
| `hosting_url($path)` | 生成插件页面 URL |
| `hosting_admin_url($page, $extra)` | 生成管理员页面 URL |
| `hosting_order_list_all($page, $per_page, $filters)` | 管理员-订单列表 |
| `hosting_open_host($order)` | 调用宝塔 API 开通主机 |

## 文件结构

```
hosting_shop/
├── plugin.json
├── bootstrap.php        # 主入口（路由 + API + 钩子 + 管理员页面注册）
├── install.sql
├── uninstall.sql
├── lib/
│   └── hosting.php      # URL/金额辅助函数 + 用户认证
├── assets/
│   └── style.css
└── views/
    ├── layout.php       # 公共布局
    ├── shop.php         # 套餐列表
    ├── order.php        # 下单页
    ├── assets.php       # 我的主机
    ├── orders.php       # 我的订单
    └── admin/
        ├── plans.php    # 管理员-套餐管理
        ├── plan_edit.php # 管理员-套餐编辑
        ├── orders.php   # 管理员-订单管理
        └── assets.php   # 管理员-资产管理
```
