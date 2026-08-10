---
title: 插件系统总览
description: MNBT 插件系统（V1.81）总览：快速启用、目录结构、核心 API、钩子、页面与 AJAX 入口、官方示例与安全约定
---

# MNBT 插件系统（V1.81）

PHP 业务插件目录。与宝塔侧 `plugins/mnbt_connector`（Python 节点代理）**无关**。

| 文档 | 说明 |
|------|------|
| **[插件开发手册](./guide.md)** | 插件开发手册（新建、API、钩子、安全、FAQ） |

完整项目说明见 [../../guide/intro.md](../../guide/intro.md)。

---

## 快速启用

1. 将插件文件夹放入 `app_plugins/{slug}/`（需 `plugin.json` + `bootstrap.php`）
2. 后台 → 系统管理 → **插件管理** → 安装 → 启用
3. **整页刷新**后台；侧栏出现「插件」分组（若注册了菜单）
4. 已有站点可执行 `update/update_v181_plugin.sql`（或首次访问自动建表）

---

## 目录结构

```
app_plugins/{slug}/
  plugin.json      # 必填：名称、版本等
  bootstrap.php    # 必填：注册钩子/菜单/AJAX
  install.sql      # 可选
  uninstall.sql    # 可选
  admin/           # 可选后台页
  user/            # 可选用户页
  assets/          # 可选静态资源
```

---

## 核心 API（摘要）

| API | 说明 |
|-----|------|
| `mnbt_plugin_register` | 注册元信息 |
| `mnbt_add_action` / `mnbt_do_action` | 事件 |
| `mnbt_add_filter` / `mnbt_apply_filters` | 过滤 |
| `mnbt_register_ajax('user'\|'admin', $gn, $cb)` | AJAX，`gn` 建议 `p_{slug}_*` |
| `mnbt_register_page` / `mnbt_register_menu` | 页面与菜单 |
| `mnbt_plugin_option_get/set` | 插件配置 |
| `mnbt_plugin_path` / `mnbt_plugin_url` | 路径与资源 URL |
| `mnbt_register_widget` | 仪表盘小部件 |
| `mnbt_register_settings_tab` | 插件管理页快捷入口 |
| `mnbt_http_get` / `mnbt_http_post` | 安全出站 HTTP |
| `mnbt_register_home` | 首页接管（`/` 路径，P2） |
| `mnbt_register_route` | 通用路由，支持路径参数（P2） |
| `mnbt_register_payment` | 注册支付插件（P3） |
| `mnbt_pay_settle_order` | 支付成功后统一结算订单（P3） |
| `mnbt_pay_log` | 记录支付日志（P3） |
| `mnbt_get_enabled_payment_methods` | 读取已启用的付款方式（P3） |

完整参数与示例见 **[插件开发手册](./guide.md)**，支付系统见 **[支付插件系统](./payment.md)**。

---

## 钩子（摘要）

| 钩子 | 时机 |
|------|------|
| `boot` | 插件加载后 |
| `init.admin` / `init.user` | 已登录 |
| `host.created` / `paused` / `unpaused` / `renewed` / `deleted` | 主机生命周期 |
| `order.paid` | 支付成功 |
| `cron` | `jk_monitor.php` 末尾 |
| `menu.*` / `dashboard.*.widgets` / `settings.admin.tabs` | UI 扩展 |

完整钩子一览与数据库说明见 **[钩子与数据库](./hooks.md)**。

---

## 页面与 AJAX 入口

| 类型 | 地址 |
|------|------|
| 管理页 | `admin/plugin.php?p={slug}&page={page}` |
| 用户页 | `user/plugin.php?p={slug}&page={page}` |
| 管理 AJAX | `POST admin/ajax.php`，`gn=...` |
| 用户 AJAX | `POST user/ajax.php`，`gn=...` |

---

## 官方示例

| 插件 | 说明 |
|------|------|
| `hello_demo/` | 菜单、配置、Ping、主机/订单事件日志 |
| `home_demo/` | P2 示例：首页接管 + 通用路由 |
| `webhook_notify/` | Webhook 推送（URL + HMAC + 事件开关 + 测试） |
| `epay/` | P3 示例：易支付插件（支付宝/微信/QQ 钱包，MD5 签名） |
| `alipay_official/` | P3 示例：支付宝官方 API（PC 电脑网站支付 + 当面付扫码，RSA2 签名） |
| `user_info/` | 用户中心插件（独立账户系统、登录/注册/资料/密码） |
| `balance/` | 余额插件（依赖 user_info；后台余额列表、用户充值/消费日志） |
| `hosting_shop/` | 主机商店插件（依赖 user_info + balance；套餐下单、自动开通） |
| `domain_shop/` | 域名商店插件：二级域名售卖 + DNSPod DNS 解析 + `host.created` 钩子自动建 A 记录；接管原核心 `ymgm` 业务与 `MN_ym` 表的售卖/绑定逻辑 |

各插件详细文档见 **[内置插件](./builtin/)** 目录（user_info、balance、hosting_shop、epay、alipay_official、webhook_notify、home_demo、hello_demo、shop_frontend）。

Webhook 验签：请求头 `X-MNBT-Signature: sha256=<hex>`，  
`hash_hmac('sha256', raw_body, secret) === hex`。

---

## 安全约定

- 仅管理员可安装/启用/卸载  
- 插件文件必须在 `app_plugins/{slug}/` 内  
- 配置只用 option 表，勿改 `MN_config` 结构  
- 不覆盖核心文件；不远程执行代码  
- 详见 [钩子与数据库 §9](./hooks.md#9-安全清单)
