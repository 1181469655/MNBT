# balance 余额管理插件

用户余额系统，提供余额查询、充值（调用支付插件）、消费、流水记录。

## 功能

- 余额查询与展示
- 余额充值（调用 epay/alipay_official 等支付插件 API）
- 余额消费（扣减，原子操作，余额不足返回失败）
- 流水记录（充值/消费/退款/调整）
- 管理员端：用户余额列表、加款/扣款、流水查询

## 依赖

- **user_info**（认证）：调用 `user_info_auth_current()` 获取当前用户
- 支付插件（epay 或 alipay_official）：充值时调用 `mnbt_pay_dispatch_gateway()`

## 安装

1. 先安装并启用 `user_info` 插件
2. 安装并启用 `balance` 插件
3. 自动创建 `MN_plugin_balance` 和 `MN_plugin_balance_log` 表

## 数据表

### MN_plugin_balance

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| user_id | INT | 用户 ID（关联 MN_plugin_user.id） |
| balance | BIGINT | 余额（**单位：分**，整数存储避免浮点误差） |
| updated_at | DATETIME | 更新时间 |

### MN_plugin_balance_log

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| user_id | INT | 用户 ID |
| amount | BIGINT | 变动金额（分，正=入账，负=出账） |
| balance_after | BIGINT | 变动后余额（分） |
| type | VARCHAR(20) | 类型：recharge/consume/refund/adjust |
| order_no | VARCHAR(64) | 关联订单号 |
| remark | VARCHAR(255) | 备注 |
| created_at | DATETIME | 时间 |

## 访问路径

| 页面 | URL |
|------|-----|
| 余额首页 | `index.php?_r=/balance` |
| 充值页 | `index.php?_r=/balance/recharge` |

## API 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/balance/api/create_recharge` | 创建充值订单，返回支付插件跳转 HTML |

## 管理员端

| 页面 | 入口 |
|------|------|
| 用户余额 | `plugin.php?p=balance&page=balances` |
| 余额流水 | `plugin.php?p=balance&page=balance_logs` |

管理员 AJAX：

| gn | 说明 |
|----|------|
| `balance_admin_adjust` | 加款/扣款（参数：user_id, amount, direction, remark） |

## 钩子

| 钩子 | 优先级 | 说明 |
|------|--------|------|
| `order.paid` | 10 | 充值订单支付成功后增加余额（检查防重复入账） |

## 余额单位约定

- 所有余额以**分**为单位整数存储，避免浮点误差
- 前端显示用 `balance_format($cents)` 转为元（保留 2 位小数）
- 充值金额：前端传元 → `round($yuan * 100)` 转分
- 扣减使用原子操作：`WHERE balance >= ?` 确保余额充足

## 核心 API

| 函数 | 说明 |
|------|------|
| `balance_get($user_id)` | 获取用户余额（分） |
| `balance_add($user_id, $amount, $type, $order_no, $remark)` | 增加余额 |
| `balance_deduct($user_id, $amount, $type, $order_no, $remark)` | 扣减余额（余额不足返回 false） |
| `balance_logs($user_id, $page, $per_page)` | 获取用户流水（分页） |
| `balance_format($cents)` | 分→元格式化 |
| `balance_require_user()` | 要求登录，返回用户数组 |
| `balance_admin_list($page, $per_page, $kw)` | 管理员-用户余额列表 |
| `balance_admin_logs($page, $per_page, $filters)` | 管理员-流水查询 |

## 文件结构

```
balance/
├── plugin.json
├── bootstrap.php        # 主入口（路由 + API + 钩子 + 管理员页面注册）
├── install.sql
├── uninstall.sql
├── lib/
│   └── balance.php      # 余额操作函数库 + 管理员辅助函数
├── assets/
│   └── style.css
└── views/
    ├── layout.php       # 公共布局
    ├── balance.php      # 余额首页
    ├── recharge.php     # 充值页
    └── admin/
        ├── balances.php # 管理员-用户余额
        └── logs.php     # 管理员-流水查询
```
