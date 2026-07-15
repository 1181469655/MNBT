# epay 易支付插件

彩虹易支付协议支付插件，支持支付宝、微信、QQ 钱包。V1.81 P3 起从核心代码迁移为独立插件。

## 功能

- 彩虹易支付协议（pid / key / apiurl）
- 支持支付宝、微信、QQ 钱包等多种支付方式
- 签名方式：MD5
- 返回方式：HTML 表单自动跳转
- 异步通知验签
- 管理员端设置页（配置 pid/key/apiurl）

## 依赖

无。支付插件不依赖其他插件。

## 安装

1. 后台 → 系统管理 → 插件管理 → 安装 `epay` → 启用
2. 在插件设置页配置 pid、key、apiurl
3. 在「系统管理 → 支付设置」中启用本插件对应的支付方式

## 配置项

存储在 `MN_plugin_option` 表（plugin_slug='epay'）：

| key | 说明 |
|-----|------|
| `pid` | 易支付商户 ID |
| `key` | 易支付商户密钥 |
| `apiurl` | 易支付 API 地址（如 `https://pay.example.com`） |

## 支付方式

本插件注册的支付方式 type 值：

| type | 说明 |
|------|------|
| `epay_alipay` | 易支付-支付宝 |
| `epay_wxpay` | 易支付-微信 |
| `epay_qqpay` | 易支付-QQ 钱包 |

具体注册的 type 取决于插件实现，在「支付设置」页面可查看和启用。

## 工作流程

```
用户发起支付 → mnbt_pay_dispatch_gateway(type, ctx)
  → epay 构造签名表单（pid/key/apiurl + 订单参数）
  → 返回 HTML 表单（自动提交跳转到易支付网关）
  → 用户支付完成
  → 易支付异步通知 → 回调本插件 notify
  → 验签（MD5） → mnbt_pay_settle_order() 标记订单完成
  → 触发 order.paid 钩子
```

## 管理员端

| 页面 | 入口 |
|------|------|
| 插件设置 | `plugin.php?p=epay&page=settings` |

## 旧配置迁移

V1.81 P3 升级时，若核心 `MN_config` 表存在旧易支付配置（`hxe`/`hxr`/`hxt` 字段），插件会自动迁移到 `MN_plugin_option`。

## 文件结构

```
epay/
├── plugin.json
├── bootstrap.php        # 主入口（注册支付方式 + 设置页 + 网关构造 + 验签回调）
├── install.sql          # 无自建表（配置存 MN_plugin_option）
├── uninstall.sql
└── views/
    └── admin/
        └── settings.php # 设置页
```

## 相关文档

- [PLUGIN_DEV.md §3.12 支付插件系统](../PLUGIN_DEV.md) — 支付插件架构与 API
- [alipay_official](../alipay_official/README.md) — 支付宝官方 API 插件
