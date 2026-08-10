---
title: 钩子与数据库
description: MNBT 插件钩子一览、数据库（系统表与插件自建表）、生命周期、安全清单、常见问题、文件索引与版本路线
---

# 钩子与数据库

本文是 [插件开发手册](./guide.md) 的扩展章节，涵盖钩子一览、数据库、生命周期、安全清单、常见问题、相关文件索引与版本路线。

## 4. 钩子一览（核心触发点）

| 钩子 | 类型 | 参数 | 触发位置（约） |
|------|------|------|----------------|
| `boot` | action | — | 全部插件 bootstrap 之后 |
| `init.admin` | action | — | 管理员已登录 |
| `init.user` | action | — | 用户已登录 |
| `host.created` | action | `$host`, `$ctx` | 后台添加主机、外部 API 开通 |
| `host.paused` | action | `$host`, `$ctx` | 后台改状态、API 暂停 |
| `host.unpaused` | action | `$host`, `$ctx` | 后台恢复、API 解除暂停 |
| `host.renewed` | action | `$host`, `$ctx` | 后台改到期、API 续费；`$ctx` 含 `old_date`/`new_date` |
| `host.deleted` | action | `$host`, `$ctx` | 后台删除、API 删除 |
| `order.paid` | action | `$order`, `$ctx` | 支付插件回调验签后调 `mnbt_pay_settle_order()` 时触发（V1.81 P3 起从 `notify_url.php` 迁移到支付插件） |
| `cron` | action | `$info` | `jk_monitor.php` 末尾 |
| `menu.admin` / `menu.user` | filter | `$items` | 渲染侧栏插件菜单前 |
| `dashboard.admin.widgets` / `dashboard.user.widgets` | filter | `$items` | 渲染小部件前 |
| `settings.admin.tabs` | filter | `$items` | 插件管理页快捷入口 |

`$ctx` 常见字段：`source` = `admin` | `api` | `pay_plugin`。

**主机 `$host` 敏感字段**（密码等）可能存在于数组中；对外推送时务必自行脱敏（参见 [webhook_notify](./builtin/webhook-notify.md)）。

---

## 5. 数据库

### 系统表

| 表 | 用途 |
|----|------|
| `MN_plugin` | 已安装插件：slug、name、version、enabled |
| `MN_plugin_option` | 插件键值配置 |

升级已有站点：执行 `update/update_v181_plugin.sql`，或首次访问时引擎 `CREATE TABLE IF NOT EXISTS`。

### 插件自建表

`install.sql` / `uninstall.sql` 在安装/卸载时执行。建议表名前缀：

```sql
-- install.sql
CREATE TABLE IF NOT EXISTS `plg_my_plugin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `created_at` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

访问数据：

```php
global $DB;
$rows = $DB->get_all_prepare("SELECT * FROM plg_my_plugin_log ORDER BY id DESC LIMIT 20") ?: [];
```

优先使用 `query_prepare` / `get_row_prepare` / `get_all_prepare`，避免拼接 SQL。

---

## 6. 生命周期：安装 / 启用 / 卸载

| 操作 | 行为 |
|------|------|
| 安装 | 写 `MN_plugin` 行；执行 `install.sql` |
| 启用 | `enabled=true`；下次请求加载 `bootstrap.php` |
| 禁用 | `enabled=false`；不再加载 |
| 卸载 | 执行 `uninstall.sql`；删除 `MN_plugin` 与该插件 option；**不删磁盘文件** |

管理 AJAX（核心，勿占用）：

- `plugin_list` / `plugin_install` / `plugin_enable` / `plugin_uninstall`

---

## 9. 安全清单

- [ ] 所有写操作校验登录（`mnbt_plugin_require_admin` / `require_user`）
- [ ] 用户输入长度与格式校验；输出 `htmlspecialchars`
- [ ] AJAX `gn` 使用 `p_{slug}_` 前缀，避免与核心冲突
- [ ] 不 `eval`、不远程下载执行 PHP
- [ ] 不写核心目录；不改 `MN_config` 表结构
- [ ] 出站 HTTP 用 `mnbt_http_*`，谨慎开启 `allow_private` / `insecure`
- [ ] 推送外部时脱敏密码、API 密钥
- [ ] 生产环境插件目录权限合理（Web 可执行 PHP，但勿对匿名可写）

---

## 10. 常见问题

### 启用后没有菜单？

整页刷新后台框架（`admin/index.php`）。菜单在框架页渲染，仅刷新 iframe 不够。

### AJAX 返回「系统指令不存在」？

1. 插件是否**已启用**（不是仅安装）  
2. `gn` 是否与 `mnbt_register_ajax` 完全一致  
3. 是否请求了正确侧：`admin/ajax.php` vs `user/ajax.php`

### 页面 404 / 插件页面文件无效？

- `mnbt_register_page` 的文件路径相对于插件根目录  
- 文件必须在 `app_plugins/{slug}/` 内（realpath 校验）

### 钩子不触发？

- 确认插件已启用  
- 确认走了对应代码路径（例如 API 开通才会 `source=api`）  
- 看 `runtime/logs/php-error.log` 是否有插件异常

### 与在线更新冲突？

插件放在 `app_plugins/`，官方更新包应避免覆盖该目录；自定义插件勿改核心文件。

---

## 11. 相关文件索引

| 路径 | 说明 |
|------|------|
| [插件系统总览](./index.md) | 插件目录总览、快速启用 |
| `MPHX/plugin.php` | 引擎实现（P0-P3 API 全部在此） |
| `MPHX/lib/pay.function.php` | P3 支付公共函数（`mnbt_pay_settle_order`、`mnbt_pay_log`） |
| `MPHX/common.php` | 启动 `mnbt_plugins_boot()` |
| `admin/plugin.php` | 插件管理 + 插件页面入口 |
| `admin/pay_settings.php` | P3 支付设置页（启用付款方式、显示名、排序） |
| `admin/api/setting.php` | 含 `setpaymethods` AJAX 处理器 |
| `admin/api/plugin.php` | 安装/启用/卸载 AJAX |
| `user/plugin.php` | 用户端插件页面入口 |
| `user/pay.php` | 创建订单后调 `mnbt_pay_dispatch_gateway()` 分发到支付插件 |
| `user/ajax.php` / `admin/ajax.php` | 插件 AJAX 优先分发 |
| `update/update_v181_plugin.sql` | 已有站点升级表结构（P0-P1） |
| `update/update_v181_p3_pay.sql` | P3 支付字段迁移（`MN_config.pay_methods`） |
| `home_demo/` | P2 示例：首页接管 + 通用路由 |
| `webhook_notify/` | P1 示例：Webhook 推送 |
| `epay/` | P3 示例：易支付插件（支付宝/微信/QQ） |
| `alipay_official/` | P3 示例：支付宝官方 API（PC + 当面付） |
| `user_info/` | 用户中心插件（独立账户系统、登录/注册/资料/密码） |
| `balance/` | 余额插件（依赖 user_info；后台余额列表、用户充值/消费日志） |
| `hosting_shop/` | 主机商店插件（依赖 user_info + balance；套餐下单、自动开通） |
| `domain_shop/` | 域名商店插件：二级域名售卖 + DNSPod DNS 解析 + `host.created` 钩子自动建 A 记录；接管原核心 `ymgm` 业务与 `MN_ym` 表的售卖/绑定逻辑 |

---

## 12. 版本与路线

| 版本 | 能力 |
|------|------|
| V1.81 P0 | 引擎、安装启用、AJAX/菜单/页面、host 钩子、cron、示例 |
| V1.81 P1 | HTTP、widget、settings_tab、order.paid、host.renewed、用户菜单、Webhook 插件 |
| V1.81 P2 | 首页接管（`mnbt_register_home`）、通用路由（`mnbt_register_route`）、路径参数匹配、`_router.php` 路由分发 |
| V1.81 P3 | 支付插件系统（`mnbt_register_payment`、`mnbt_pay_settle_order`）、统一支付设置页、易支付/支付宝官方插件、旧 `notify_url.php`/`return_url.php` 完全废弃 |

后续可能：zip 安装、`gn` 冲突检测 UI、SPA 菜单协议、细粒度能力 ACL。
