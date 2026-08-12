---
title: MNBT 智简魔方（魔方财务）代理分销插件 PRD
description: MNBT 代理分销魔方财务产品：商品同步加价、本地余额购买、代理商直通开通、主机管理与升降级
---

# MNBT 智简魔方（魔方财务）代理分销插件 PRD

> 版本：v1.1（待评审）
> 日期：2026-08-12
> 状态：评审中
> 关联文档：[插件开发手册](../development/plugin/guide.md)、[user_info](../development/plugin/builtin/user-info.md)、
> [balance](../development/plugin/builtin/balance.md)、示例 SDK `app_plugins/zjmfmanager_reserve/example/`

---

## 1. 背景与目标

### 1.1 背景

MNBT 需以**代理商（Reseller）**身份对外销售一个或多个上游**智简魔方 / 魔方财务（cube_finance）**
站点的产品。上游通过客户 API（`zjmf_api_login` + JWT）提供商品、下单、开通、主机管理能力，
示例 SDK 已封装登录/商品/主机/升级等基础请求。

本插件在 MNBT 侧以业务插件形式实现代理分销，依赖已内置的 `user_info`（用户认证）
与 `balance`（余额支付）插件，复用其认证与支付能力。

### 1.2 目标

| # | 目标 |
|---|------|
| G1 | 管理员维护多个上游供应商（魔方财务站点 + 代理商 API 账号），按供应商同步商品并加价出售 |
| G2 | 本地用户浏览商品（按供应商分组）、选择周期、用本地余额支付下单 |
| G3 | 支付成功后由插件调用对应供应商接口为**代理商账号**直通开通主机，用户获得主机信息 |
| G4 | 用户查看主机状态/流量，执行开关机/重启/重置密码/重装等操作 |
| G5 | 用户提交配置升级 / 产品升降级，余额支付后同步到上游 |
| G6 | 管理员管理供应商、订单与主机，记录操作日志 |

### 1.3 范围

| 期次 | 内容 |
|------|------|
| **P0（本期）** | 供应商管理（多上游）；商品同步（弹窗选择）+ 手动添加 + 加价 + 上架；本地下单 + 余额支付 + 代理商直通开通；<br>用户主机查看（状态/流量）+ 基本操作（开关机/重启/重置密码/重装）；配置升级 + 产品升降级（余额支付）；管理员订单/主机管理；操作日志 |
| **P1（下期）** | 上游余额/成本对账；定时任务（cron）自动同步商品与价格、批量状态刷新；监控用量图表；商品配置项（config options）可视化选择 |

> 说明：P0 下单暂不支持上游商品复杂配置项的自定义（如需可手填备注参数，见 Q2），
> 仅支持商品 + 计费周期的选购。

---

## 2. 总体架构

### 2.1 架构图

```
┌────────────────────────────────────────────────────────────┐
│                        MNBT（本系统）                         │
│                                                             │
│  用户(user_info 认证)                                        │
│    │ 浏览/下单/余额支付(balance)                              │
│    ▼                                                        │
│  zjmfmanager_reserve 插件                                     │
│    ├─ 供应商管理（多上游，各自独立 API 账号/JWT）               │
│    ├─ 商品同步/加价（按供应商）                                │
│    ├─ 本地订单 → 余额扣款 → 对应供应商开通（代理商直通）          │
│    ├─ 主机查询 / provision 操作 / 升级                        │
│    └─ 管理员：供应商、商品、订单、主机、日志                    │
└──────────────┬───────────────────────────────┬─────────────┘
               │ HTTPS + JWT                     │ HTTPS + JWT
               ▼                                 ▼
┌─────────────────────────────┐   ┌─────────────────────────────┐
│   上游 魔方财务 A（供应商1）   │   │   上游 魔方财务 B（供应商2）   │
│  zjmf_api_login / product   │   │  zjmf_api_login / product   │
│  cart/set_config / host/*   │ … │  cart/set_config / host/*   │
│  provision/* / upgrade/*    │   │  provision/* / upgrade/*    │
└─────────────────────────────┘   └─────────────────────────────┘
```

**核心模型**：本地用户不直接接触上游客户体系。每个供应商使用各自配置的**代理商 API 账号**
在上游完成开通与操作，主机归属对应供应商的代理商账号，本地用户持有主机账号密码。
商品、订单、主机均记录 `supplier_id`，下单/操作/升级按供应商路由到对应客户端实例
（每个供应商独立 JWT 缓存，互不串扰）。

### 2.2 模块命名

| 项 | 值 |
|----|----|
| 插件 slug | `zjmfmanager_reserve`（与目录名一致） |
| 显示名 | 魔方财务代理分销 |
| 依赖插件 | `user_info`、`balance`（`plugin.json` 用 `requires_plugins` 声明） |

---

## 3. 上游 API 约定

### 3.1 认证

- `POST {BASE}/zjmf_api_login`（username=客户用户名, password=API 密钥）→ 顶层 `jwt`
- 后续请求头 `Authorization: Bearer {jwt}`
- JWT 缓存约 2 小时；返回 `status=401/405` 时强制重登重试一次
- **多供应商**：JWT 缓存 key 含 `supplier_id`，各供应商独立登录、独立缓存、互不串扰
- 客户端类改编自 `example/sdk/CubeFinanceClient.php`，置于插件 `lib/` 下

### 3.2 接口清单与映射

| 场景 | 上游接口 | 插件方法（lib/upstream.php） |
|------|----------|------------------------------|
| 登录 | `POST zjmf_api_login` | `login()` |
| 连通测试 | 登录 + 商品列表 | `testConnection()` |
| 商品列表 | `GET api/product/list` | `productList()` |
| 商品详情（代理价） | `GET api/product/{id}?price_basis=agent` | `productDetail($id)` |
| 价格试算 | `GET cart/set_config` | `cartSetConfig($params)` |
| 开通（下单） | 见 §3.3（Q1） | `purchase($params)` |
| 主机头信息 | `GET host/header?host_id=` | `hostHeader($hostId)` |
| 流量使用 | `GET host/trafficusage` | `hostTrafficUsage($hostId)` |
| 主机操作 | `POST provision/default` | `hostAction($hostId, $func, $extra)` |
| 配置升级页 | `GET upgrade/index/{hostId}` | `upgradeIndex($hostId)` |
| 配置升级确认 | `GET upgrade/upgrade_config_page` | `upgradeConfigPage($hostId)` |
| 提交配置升级 | `POST upgrade/upgrade_config_post` | `upgradeConfigPost($params)` |
| 产品升降级选项 | `GET upgrade/upgrade_product/{hostId}` | `upgradeProduct($hostId)` |
| 提交产品升降级 | `POST upgrade/upgrade_product_post` | `upgradeProductPost($params)` |
| 余额抵扣（开放问题） | `POST apply_credit` | `applyCredit($params)` |

### 3.3 开通（下单）流程

代理商直通开通的**具体上游端点需联调确认**（魔方财务各版本/定制站存在差异）：

**方案 A（推荐，先按此实现）**：提交上游订单 → `apply_credit` 余额支付 → 上游自动开通。
- 步骤：`cart/set_config` 试算 → 创建上游订单（端点待确认，Q1）→ `apply_credit` 抵扣支付
  → 查询订单/主机获取 `host_id` 与账号密码。

**方案 B（备选）**：若开通可由 provision 类接口直接触发，则 `provision/default` 携 `func=create`
类动作创建主机。

> 插件将上游调用收敛在 `ZjmfUpstream::purchase()` 单一方法内，后续仅需改该方法适配，
> 不影响订单、支付、页面逻辑。默认实现按方案 A 编码，联调按实际站点调整。

---

## 4. 数据表设计

### 4.1 `MN_plugin_zjmf_supplier` 上游供应商表（v1.1 新增）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AI | 主键 |
| name | VARCHAR(50) | 供应商名称（站点标识，可自定） |
| api_url | VARCHAR(255) | 上游站点根地址 |
| api_username | VARCHAR(64) | API 用户名 |
| api_password | VARCHAR(255) | API 密钥（仅保存时写入，不回显） |
| api_timeout | INT | 请求超时（秒），默认 30 |
| markup_type | TINYINT | 该供应商加价方式：0=比例 1=固定（分） |
| markup_value | BIGINT | 加价比例（千分比）或固定加价（分） |
| status | TINYINT | 1=启用 0=停用（停用后商品不可售、不可操作） |
| sort | INT | 排序 |
| remark | VARCHAR(255) | 备注 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

> 加价规则按供应商独立配置（商品可单品覆盖）；不再使用全局 option 加价。

### 4.2 `MN_plugin_zjmf_product` 本地商品表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AI | 主键 |
| supplier_id | INT | 所属供应商 ID（v1.1 新增，联合唯一索引） |
| up_product_id | INT | 上游商品 ID（与 supplier_id 联合唯一索引） |
| name | VARCHAR(100) | 商品名 |
| description | TEXT | 描述 |
| currency | VARCHAR(10) | 货币代码（同步自上游） |
| agent_price_cents | BIGINT | 上游代理价（分，最近同步） |
| markup_type | TINYINT | 单品加价方式：0=比例 1=固定（分）；0/0=未配置取供应商 |
| markup_value | BIGINT | 加价比例（千分比）或固定加价（分） |
| cycles | TEXT | 周期 JSON：`[{"cycle":"monthly","name":"月付","price_cents":12345}]`（本地售价） |
| status | TINYINT | 1=上架 0=下架 |
| sort | INT | 排序 |
| synced_at | DATETIME | 最近同步时间 |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

> 价格计算：本地售价 = 上游代理价 ×（1 + 比例%）或 + 固定加价，单位**分**整数存储。
> 未单独配置加价时取所属供应商（`MN_plugin_zjmf_supplier.markup_type/value`）配置。

### 4.3 `MN_plugin_zjmf_order` 本地订单表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AI | 主键 |
| order_no | VARCHAR(64) | 本地订单号（唯一，前缀 `ZJM`） |
| supplier_id | INT | 所属供应商 ID（v1.1 新增，开通路由依据） |
| user_id | INT | 本地用户 ID |
| product_id | INT | 本地商品 ID |
| up_product_id | INT | 上游商品 ID（冗余） |
| product_name | VARCHAR(100) | 商品名（冗余） |
| cycle | VARCHAR(20) | 计费周期（billingcycle） |
| cycle_name | VARCHAR(20) | 周期文案（冗余） |
| amount_cents | BIGINT | 本地售价（分） |
| cost_cents | BIGINT | 上游成本价（分） |
| order_params | TEXT | 下单参数 JSON（备注/配置项透传） |
| up_order_id | INT | 上游订单 ID（开通后回填） |
| up_host_id | INT | 上游主机 ID（开通后回填） |
| username | VARCHAR(64) | 上游主机账号（冗余，展示脱敏） |
| status | VARCHAR(20) | `pending/paid/opened/failed/cancelled` |
| pay_time | DATETIME | 支付时间 |
| opened_at | DATETIME | 开通时间 |
| remark | VARCHAR(255) | 备注 |
| created_at | DATETIME | 下单时间 |

### 4.4 `MN_plugin_zjmf_host` 上游主机映射表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AI | 主键 |
| supplier_id | INT | 所属供应商 ID（v1.1 新增，操作/升级路由依据） |
| user_id | INT | 本地用户 ID |
| order_id | INT | 本地订单 ID |
| up_host_id | INT | 上游主机 ID |
| up_product_id | INT | 上游商品 ID |
| name | VARCHAR(100) | 主机名 |
| username | VARCHAR(64) | 上游账号 |
| password | VARCHAR(255) | 上游密码（`authcode` 加密存储） |
| cycle | VARCHAR(20) | 周期 |
| status | VARCHAR(20) | 状态缓存：active/suspend/unknown |
| renew_date | VARCHAR(20) | 到期日（缓存） |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### 4.5 `MN_plugin_zjmf_log` 操作日志表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AI | 主键 |
| user_id | INT | 本地用户 ID（可为 0=系统） |
| supplier_id | INT | 供应商 ID（v1.1 新增，便于追溯） |
| order_no | VARCHAR(64) | 关联订单号 |
| action | VARCHAR(50) | 操作：sync/purchase/power/reset_password/upgrade/... |
| result | VARCHAR(20) | success/failed |
| content | TEXT | 详情 JSON（脱敏，不含密码/密钥） |
| created_at | DATETIME | 时间 |

> 建表语句写入 `install.sql`，删除语句写入 `uninstall.sql`，
> 均用 `CREATE TABLE IF NOT EXISTS` / `DROP TABLE IF EXISTS`。
> **迁移说明（v1.1）**：新增 `MN_plugin_zjmf_supplier` 表并在 product/order/host/log
> 增加 `supplier_id` 字段属结构变更；插件未正式发布，安装环境需**卸载后重装**（数据清空）。
> 若存在需保留数据的部署，另提供 `upgrade-1.1.sql` 做 `ALTER TABLE` + 默认供应商迁移。

---

## 5. 管理员端设计

### 5.1 页面（`plugin.php?p=zjmfmanager_reserve&page=...`）

| 页面 | page | 说明 |
|------|------|------|
| 供应商管理 | `suppliers` | 供应商列表（名称/站点/加价/启用/排序）；新增/编辑弹窗（连接信息 + 加价配置）；连通测试；停用后商品不可售 |
| 商品管理 | `products` | 商品列表（所属供应商、上架状态、售价、同步时间）；「同步商品」打开选择弹窗；「手动添加」表单；启停/编辑 |
| 商品编辑 | `product_edit` | 单品加价（比例/固定）、排序、上架开关、查看上游代理价 |
| 订单管理 | `orders` | 本地订单列表（供应商/状态筛选、按订单号/用户搜索） |
| 主机管理 | `hosts` | 本地映射列表 + 上游主机详情查询入口 |

### 5.2 供应商与商品同步

**供应商管理**
1. 新增供应商：名称、站点 URL、API 用户名、API 密钥、超时、加价方式/数值、启用、排序。
2. 保存后可「连通测试」（登录 + 商品列表）验证凭证。
3. 停用供应商后，其商品自动下架不可售、主机操作与升级拒绝执行（返回明确错误）。

**商品同步（弹窗选择）**
1. 商品管理页点击「同步商品」→ 弹出同步对话框。
2. 对话框第一步选择供应商 → 拉取该供应商 `api/product/list` 列表（名称/描述/代理价）。
3. 管理员勾选要同步的商品（默认全选，可过滤）→ 确认后逐条同步。
4. 每个商品拉取 `api/product/{id}?price_basis=agent` 代理价，并按 `cart/set_config`
   试算各周期价格（失败则跳过该周期，标记该商品「价格未同步」）。
5. 同步策略（幂等）：按 `supplier_id + up_product_id` upsert；仅新增或更新名称/描述/代理价，
   **不覆盖**管理员自定义的加价、上架、排序。

**手动添加商品**
1. 商品管理页点击「手动添加」→ 表单：所属供应商、商品名称、上游商品 ID、描述。
2. 保存后立即按该商品拉取上游代理价与各周期价格（依赖上游 `api/product/{id}` 详情接口，
   失败则该商品标记「价格未同步」，可稍后再次同步）。
3. 手动添加商品同样遵循单品加价配置。

> 本地售价 = 代理价 × 加价（供应商或单品），写入 `cycles`。

### 5.3 管理员 AJAX（`gn` 全部使用 `p_zjmf_admin_` 前缀）

| gn | 说明 |
|----|------|
| `p_zjmf_admin_save_supplier` | 保存供应商（新增/编辑，密码留空不修改） |
| `p_zjmf_admin_toggle_supplier` | 启用/停用供应商 |
| `p_zjmf_admin_delete_supplier` | 删除供应商（有商品/主机时拒绝） |
| `p_zjmf_admin_test_supplier` | 连通测试（登录 + 商品列表） |
| `p_zjmf_admin_upstream_products` | 拉取指定供应商商品列表（供同步弹窗选择） |
| `p_zjmf_admin_sync_products` | 按所选供应商 + 勾选商品 ID 列表同步 |
| `p_zjmf_admin_save_product` | 保存单品加价/上架/排序 |
| `p_zjmf_admin_toggle_product` | 上下架切换 |
| `p_zjmf_admin_add_product` | 手动添加商品（供应商 + 上游产品 ID + 名称 + 描述） |
| `p_zjmf_admin_order_list` | 订单分页列表 |
| `p_zjmf_admin_host_list` | 主机分页列表 |
| `p_zjmf_admin_fetch_host` | 拉取上游主机详情（header/流量） |

---

## 6. 用户端设计

### 6.1 页面与路由（P2 通用路由，`index.php?_r=...` 或伪静态）

| 页面 | 路由 | 说明 |
|------|------|------|
| 商品列表 | `GET /reserve/shop` | 上架商品卡片（价格/周期）；**按供应商分组展示**（Tab 或分组区块，含供应商名/状态标识） |
| 商品详情 | `GET /reserve/product/{id}` | 周期切换试算、下单按钮 |
| 我的主机 | `GET /reserve/hosts` | 主机列表（状态/到期，含所属供应商标识） |
| 主机详情 | `GET /reserve/hosts/{id}` | 状态、流量、操作按钮、升级入口 |
| 我的订单 | `GET /reserve/orders` | 本地订单列表与状态 |

API 路由：

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/reserve/api/create_order` | 创建订单并余额支付（核心，见 §7.1） |
| POST | `/reserve/api/host_action` | 主机操作（on/off/reboot/reset_password/reinstall） |
| POST | `/reserve/api/upgrade` | 提交配置升级 / 产品升降级（余额支付，见 §7.2） |
| POST | `/reserve/api/refresh_price` | 前端周期价格试算（读本地售价，不上游） |

> 辅助函数：`zjmf_url($path)`、`zjmf_admin_url($page)`、`zjmf_format_cents($cents)`、
> `zjmf_require_user()`（包装 `user_info_auth_require`，输出统一布局）。

### 6.2 下单支付流程

```
用户选择商品+周期 → 前端 POST /reserve/api/create_order
  → zjmf_require_user()
  → 校验商品上架、周期有效 → 写 MN_plugin_zjmf_order(status=pending)
  → balance_deduct(user_id, amount, 'consume', order_no, 备注)   # 余额扣款
  → 扣款成功 → 订单 status=paid → 触发开通（§7.1）
  → 返回结果（成功/余额不足）
```

- 余额不足时 `balance_deduct` 返回 false → 订单置 `cancelled` → 提示充值。
- 余额扣款成功后，若上游开通失败，订单置 `failed` 并**自动原路退回**余额
  （`balance_add`，type=refund），日志记录完整链路。

### 6.3 主机操作与升级

- 操作（开关机/重启/重置密码/重装）：调对应供应商 `provision/default`，参数含 `host_id` 与 `func`。
  重装/重置密码等高危操作需二次确认；前端确认后调用。
- 配置升级 / 产品升降级：先按主机所属供应商调 `upgrade/index`、`upgrade/upgrade_config_page` 或
  `upgrade_product` 拉取选项与差额 → 前端确认 → 本地再建一条升级订单（`action` 标记，记录 supplier_id）
  → `balance_deduct` 扣差额 → 调 `upgrade_config_post` / `upgrade_product_post` 提交对应上游
  → 更新主机 `cycle/status/renew_date` 缓存。

---

## 7. 核心流程与钩子

### 7.1 下单开通闭环

```
创建本地订单(pending, 含 supplier_id) → 余额扣款(balance_deduct)
  → balance 插件触发 order.paid 钩子（与 hosting_shop 同模式，priority 默认 10）
  → 本插件监听 order.paid（priority 20）
      - 仅处理 order_no 前缀 ZJM 的订单，其余 return
      - 幂等：status 已 paid/opened 直接 return
      - 按订单 supplier_id 取对应供应商配置，调 ZjmfUpstream::purchase() 在上游开通
      - 回填 up_order_id / up_host_id / username / password
      - 写 MN_plugin_zjmf_host（含 supplier_id），订单 status=opened
      - 失败：订单 status=failed + 原路退回余额 + 写日志
```

> 注：`order.paid` 由 balance 扣款链路触发（详见 balance 插件文档中 hosting_shop
> 同款流程）；支付插件（充值时）也会触发该钩子，本插件以订单号前缀严格过滤，
> 避免误处理余额充值订单。供应商停用/缺失时开通返回明确错误并走失败退款路径。

### 7.2 升级闭环

```
拉取升级选项/差额 → 前端确认 → 创建升级单(pending, action=upgrade_config|upgrade_product, 含 supplier_id)
  → 余额扣款 → 按 supplier_id 提交对应上游 → 成功置 opened 并更新主机缓存 / 失败置 failed 并退余额
```

### 7.3 钩子清单

| 钩子 | 优先级 | 说明 |
|------|--------|------|
| `order.paid` | 20 | 本地购买订单支付成功后触发上游开通（§7.1） |
| `cron` | 10 | P1：定时同步商品价格、刷新主机状态 |

---

## 8. 状态映射

### 8.1 上游主机状态 → 本地展示

| 上游状态（host/header 返回值，以实际为准） | 本地展示 |
|--------------------------------------------|----------|
| 正常 / `qk=1` | 运行中 |
| 暂停 / `qk=0` | 已暂停 |
| 到期未续费 | 已到期 |
| 查询失败 / 无数据 | 未知（缓存兜底） |

### 8.2 本地订单状态

`pending`（待支付）→ `paid`（已支付）→ `opened`（已开通）；
失败路径：`pending`→`cancelled`（未支付关闭）、`paid`→`failed`（开通失败，余额已退）。

---

## 9. 安全设计

| 项 | 方案 |
|----|------|
| 上游凭证 | 存 `MN_plugin_zjmf_supplier.api_password`；编辑页密码框不回显；日志/响应脱敏 |
| 主机密码 | `authcode()` 加密入库；页面默认打码，可「显示」二次确认 |
| 用户侧鉴权 | 全部 API 走 `zjmf_require_user()`（user_info 认证） |
| 管理侧鉴权 | 全部 AJAX 走 `mnbt_plugin_require_admin()` |
| 出站请求 | 仅 `http(s)://`；JWT 缓存目录权限收敛；超时上限校验 |
| AJAX 命名 | 管理员 `p_zjmf_admin_*`，避免与核心 gn 冲突 |
| 越权 | 主机/订单查询均带 `user_id` 条件，无跨用户访问路径；供应商操作仅管理员 |
| 订单号 | 前缀 `ZJM` + 时间 + 随机，全局唯一 |

---

## 10. 里程碑与验收

### M1 — 供应商管理 + 上游服务层
- [ ] `lib/CubeFinanceClient.php`（改编 SDK）+ `lib/upstream.php` 服务层（按供应商实例化，JWT 独立缓存）
- [ ] 供应商管理页：新增/编辑/启停/删除、连接配置 + 加价配置、连通测试
- **验收**：可添加多个供应商并分别连通测试；错误凭证返回明确错误；停用供应商后商品不可售。

### M2 — 商品同步（弹窗选择/手动添加）+ 用户端商品与下单
- [ ] 同步弹窗（选供应商 → 拉取列表 → 勾选同步）、手动添加商品、加价、上下架
- [ ] 商品列表/详情页（按供应商分组）、`create_order`、余额支付闭环
- **验收**：同步后本地售价 = 代理价 × 加价（供应商或单品）；可仅同步勾选商品；
  手动添加后价格同步成功；下单扣款成功；余额不足被拒。

### M3 — 上游开通 + 主机管理
- [ ] `purchase()` 开通（Q1 方案 A，按 supplier_id 路由）；`MN_plugin_zjmf_host` 落库
- [ ] 主机列表/详情、状态与流量查询、开关机/重启/重置密码/重装
- **验收**：支付后对应供应商上游出现主机并回填 host_id；操作类功能状态同步变化；
  开通失败自动退余额；停用供应商主机操作被拒。

### M4 — 升级 + 管理员订单/主机 + 日志
- [ ] 配置升级 / 产品升降级闭环（余额支付，按 supplier_id 路由）
- [ ] 管理员订单/主机列表（供应商筛选）、拉取上游详情
- [ ] 操作日志表完整记录
- **验收**：升级扣差额并同步对应上游；订单/主机列表筛选正确；日志无明文密码/密钥。

### M5 — 文档收尾
- [ ] 插件 README、PRD 附录补充部署与联调用例；mdi 图标可用性核对

---

## 11. 决策点（待评审）

| # | 决策 | 建议 |
|---|------|------|
| D1 | 开通模式 | 代理商直通开通（方案 A：上游下单 + apply_credit，Q1 确认端点） |
| D2 | 定价 | 同步 + 加价；**按供应商独立配置加价**，商品可单品覆盖；单位分 |
| D3 | 开通触发 | 复用 `order.paid` 钩子（priority 20），订单号前缀过滤 |
| D4 | 开通失败处理 | 订单置 failed 并自动原路退回余额 |
| D5 | 上游主机归属 | 全部归各供应商代理商 API 账号，本地不建上游客户 |
| D6 | SDK 来源 | 改编 `example/sdk/CubeFinanceClient.php`，避免改动示例原文件 |
| D7 | 多供应商（v1.1） | 新增 `MN_plugin_zjmf_supplier` 表；商品/订单/主机/日志记录 supplier_id；客户端按供应商实例化、JWT 独立缓存 |
| D8 | 商品同步（v1.1） | 弹窗选择供应商与商品后同步；支持手动添加（基础信息 + 同步周期） |
| D9 | 管理端图标 | 仅使用本项目 mdi 库中存在的图标（`mdi-cog` 不存在，改用 `mdi-settings`/`mdi-account-key` 等） |

---

## 12. 风险与开放问题

| # | 风险/问题 | 影响 | 应对 |
|---|-----------|------|------|
| R1 | 上游「开通/下单」端点各版本差异 | 开通失败 | 收敛于 `ZjmfUpstream::purchase()`，联调按实际站点适配（§3.3） |
| R2 | `order.paid` 触发链路与预期不符 | 支付后不开通 | 以 hosting_shop 同款 balance 扣款流程为准，联调验证；备选：create_order 内同步触发 |
| R3 | 上游商品周期/价格口径差异 | 本地售价不准 | 以 `cart/set_config` 试算结果为准；失败标记「价格未同步」 |
| R4 | 多供应商凭证/字段差异（v1.1） | 部分供应商不可用 | 供应商维度独立配置与缓存；连通测试逐供应商验证 |
| Q1 | 上游创建订单的确切端点 | 影响 purchase() | 联调确认（`orders/checkout`/`cart/checkout` 等），默认方案 A 实现 |
| Q2 | 商品配置项（config options）如何透传 | 影响下单参数 | P0 支持备注透传 `order_params`；可视化选择放 P1 |
| Q3 | 上游 host/header 状态字段名 | 影响状态映射 | 以实际返回为准，§8.1 表联调校准 |
| Q4 | 重置密码后上游主机密码回传时机 | 影响入库 | 开通/重置后主动查询 host/header 或订单回传字段 |
