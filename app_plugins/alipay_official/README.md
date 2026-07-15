# alipay_official 支付宝官方插件

支付宝官方 API 直连支付插件，支持电脑网站支付（PC）与当面付（扫码）。基于 dedemao/alipay 单文件 SDK，RSA2 签名。

## 功能

- 支付宝官方 API 直连（非第三方中转）
- 电脑网站支付（PC 网页支付）
- 当面付（扫码支付）
- RSA2 签名（SHA256WithRSA）
- 异步通知验签
- 管理员端设置页（配置 AppID / 私钥 / 公钥）

## 依赖

无。支付插件不依赖其他插件。

## 安装

1. 后台 → 系统管理 → 插件管理 → 安装 `alipay_official` → 启用
2. 在插件设置页配置 AppID、应用私钥、支付宝公钥
3. 在「系统管理 → 支付设置」中启用本插件对应的支付方式

## 配置项

存储在 `MN_plugin_option` 表（plugin_slug='alipay_official'）：

| key | 说明 |
|-----|------|
| `app_id` | 支付宝应用 AppID |
| `app_private_key` | 应用私钥（RSA2） |
| `alipay_public_key` | 支付宝公钥 |
| `gateway_url` | 网关地址（默认 `https://openapi.alipay.com/gateway.do`） |

## 支付方式

本插件注册的支付方式 type 值：

| type | 说明 |
|------|------|
| `alipay_pc` | 支付宝电脑网站支付 |
| `alipay_qr` | 支付宝当面付（扫码） |

具体注册的 type 取决于插件实现，在「支付设置」页面可查看和启用。

## 工作流程

```
用户发起支付 → mnbt_pay_dispatch_gateway(type, ctx)
  → alipay_official 构造支付请求（RSA2 签名）
  → PC：返回 HTML 表单自动跳转到支付宝收银台
  → 扫码：返回二维码 URL，前端展示二维码
  → 用户支付完成
  → 支付宝异步通知 → 回调本插件 notify
  → 验签（RSA2） → mnbt_pay_settle_order() 标记订单完成
  → 触发 order.paid 钩子
```

## SDK

基于 [dedemao/alipay](https://github.com/dedemao/alipay) 单文件 SDK，无 composer 依赖，已内置在插件目录中。

## 管理员端

| 页面 | 入口 |
|------|------|
| 插件设置 | `plugin.php?p=alipay_official&page=settings` |

## 文件结构

```
alipay_official/
├── plugin.json
├── bootstrap.php        # 主入口（注册支付方式 + 设置页 + 网关构造 + 验签回调）
├── install.sql          # 无自建表（配置存 MN_plugin_option）
├── uninstall.sql
├── lib/
│   └── AlipayService.php # dedemao/alipay SDK
└── views/
    └── admin/
        └── settings.php # 设置页
```

## 相关文档

- [PLUGIN_DEV.md §3.12 支付插件系统](../PLUGIN_DEV.md) — 支付插件架构与 API
- [epay](../epay/README.md) — 易支付插件
