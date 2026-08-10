---
title: 内置插件
description: MNBT 内置 PHP 业务插件清单与快速索引
---

# 内置插件

## 业务插件

| 插件 | 说明 | 文档 |
|------|------|------|
| **user_info** | 独立用户系统（注册/登录/个人信息/修改密码） | [用户信息插件](./user-info.md) |
| **balance** | 余额管理（充值/消费/流水，依赖 user_info） | [余额插件](./balance.md) |
| **hosting_shop** | 主机售卖（套餐/购买/自动开通，依赖 user_info + balance） | [主机售卖插件](./hosting-shop.md) |

## 支付插件

| 插件 | 说明 | 文档 |
|------|------|------|
| **epay** | 易支付（彩虹易支付协议，支付宝/微信/QQ） | [易支付插件](./epay.md) |
| **alipay_official** | 支付宝官方 API（PC + 当面付，RSA2） | [支付宝插件](./alipay-official.md) |

## 集成与示例插件

| 插件 | 说明 | 文档 |
|------|------|------|
| **webhook_notify** | Webhook 通知（主机事件/订单支付，HMAC 签名） | [Webhook 通知](./webhook-notify.md) |
| **shop_frontend** | 售卖前端皮肤（统一品牌落地页 + 用户端页面） | [售卖前端](./shop-frontend.md) |
| **home_demo** | 首页接管 + 通用路由示例（P2） | [首页示例](./home-demo.md) |
| **hello_demo** | 基础示例（菜单/AJAX/配置/钩子） | [基础示例](./hello-demo.md) |

## 其他

| 插件 | 目录 | 说明 |
|------|------|------|
| domain_shop | `app_plugins/domain_shop/` | 域名商店：二级域名售卖 + DNSPod DNS 解析 |
| auto_deploy | `app_plugins/auto_deploy/` | 自动部署插件 |
| qmzl_domain | `app_plugins/qmzl_domain/` | 启明智联域名注册插件 |
