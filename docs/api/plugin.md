---
title: 插件对接 API
description: MNBT PHP 业务插件系统对接（AJAX、页面、注册函数、钩子、支付插件、自动鉴权）
---

# 插件对接 API

## 5.1 插件 AJAX 调用规则

**管理端：** `POST /admin/ajax.php`，`gn` 必须以 `p_` 开头（建议 `p_{slug}_xxx`）  
**用户端：** `POST /user/ajax.php`，`gn` 必须以 `p_` 开头（建议 `p_{slug}_xxx`）

> 框架在分发核心 `gn` 前会优先调用 `mnbt_plugin_dispatch_ajax($scope, $egn)` 命中插件回调。

**示例（domain_shop 插件）：**

```http
POST /admin/ajax.php
gn=p_domain_addym&url=example.com&bt=1&jg=10&ymjs=介绍&kg=true&channel=pan&provider_id=0
```

```http
POST /user/ajax.php
gn=p_dns_record_list&type=A
```

## 5.2 插件页面 URL 规则

| 端 | URL 模板 |
|----|----------|
| 管理端 | `/admin/plugin.php?p={slug}&page={page}` |
| 用户端 | `/user/plugin.php?p={slug}&page={page}` |

**示例：**

- `/admin/plugin.php?p=domain_shop&page=products`
- `/user/plugin.php?p=domain_shop&page=bind`

## 5.3 插件注册函数

> 全部定义在 `MPHX/plugin.php`，详见 `app_plugins/PLUGIN_DEV.md`

### 基础注册

| 函数 | 用途 |
|------|------|
| `mnbt_plugin_register($slug, $info)` | 注册插件基础信息 |
| `mnbt_add_action($hook, $callback, $priority=10)` | 注册动作钩子 |
| `mnbt_do_action($hook, ...$args)` | 触发动作 |
| `mnbt_add_filter($hook, $callback, $priority=10)` | 注册过滤器 |
| `mnbt_apply_filters($hook, $value, ...$args)` | 应用过滤器 |

### 能力注册

| 函数 | 用途 |
|------|------|
| `mnbt_register_ajax($scope, $gn, $callback, $auth=null)` | 注册 AJAX 接口（`scope`: `admin`/`user`） |
| `mnbt_register_page($scope, $page, $file, $title)` | 注册插件页面 |
| `mnbt_register_menu($scope, $menu)` | 注册菜单（支持 `children` 子菜单） |
| `mnbt_register_widget($scope, $area, $callback)` | 注册小部件（仪表盘） |
| `mnbt_register_settings_tab($scope, $tab)` | 注册设置页 Tab |
| `mnbt_register_home($callback, $priority=10)` | 接管站点首页 `/` |
| `mnbt_register_route($method, $path, $callback, $priority=10, $auth=null)` | 注册通用路由 |
| `mnbt_register_page_override($scope, $view, $callback)` | 接管主题页面渲染 |
| `mnbt_register_partial_override($scope, $view, $callback)` | 接管主题 partial |

### 通用路由示例

```php
// 命名参数 {id}、尾斜杠可选
mnbt_register_route('GET', '/api/items/{id}', function ($params, $ctx) {
    return ['id' => $params['id']];
}, 10, 'user');
```

### 配置存取

| 函数 | 用途 |
|------|------|
| `mnbt_plugin_option_get($slug, $key, $default=null)` | 获取插件配置 |
| `mnbt_plugin_option_set($slug, $key, $value)` | 设置插件配置 |
| `mnbt_plugin_require_admin()` | 强制要求管理员（手动调用） |
| `mnbt_plugin_require_user()` | 强制要求用户（手动调用） |

### HTTP 工具

| 函数 | 用途 |
|------|------|
| `mnbt_http_get($url, $headers=[])` | GET 请求（默认禁内网） |
| `mnbt_http_post($url, $data, $headers=[])` | POST 请求（默认禁内网） |

## 5.4 钩子（Hooks）一览

| 钩子名 | 触发位置 | 参数 |
|--------|----------|------|
| `boot` | 系统启动完成 | 无 |
| `host.created` | 主机开通后 | `$user`、`$zjid`、`$bh` |
| `host.paused` | 主机暂停 | `$user`、`$zjid` |
| `host.unpaused` | 主机解除暂停 | `$user`、`$zjid` |
| `host.renewed` | 主机续费 | `$user`、`$zjid`、`$new_date` |
| `host.deleted` | 主机删除 | `$user`、`$zjid` |
| `order.paid` | 订单支付成功 | `$order` |
| `cron` | 计划任务触发 | 无 |
| `menu.admin` | 管理端菜单构建 | `&$menu` |
| `menu.user` | 用户端菜单构建 | `&$menu` |
| `dashboard.admin.widgets` | 管理端仪表盘小部件 | `&$widgets` |
| `dashboard.user.widgets` | 用户端仪表盘小部件 | `&$widgets` |
| `settings.admin.tabs` | 管理端设置页 Tab | `&$tabs` |

**示例：监听主机开通钩子自动建 DNS 记录**

```php
mnbt_add_action('host.created', function ($user, $zjid, $bh) {
    // 自动为新主机创建 A 记录
    domain_shop_auto_create_dns($user, $bh);
}, 10);
```

## 5.5 支付插件 API

| 函数 | 用途 |
|------|------|
| `mnbt_register_payment($slug, $info)` | 注册支付插件 |
| `mnbt_get_payment_plugins()` | 获取所有已注册支付插件 |
| `mnbt_get_enabled_payment_methods()` | 获取已启用的支付方式 |
| `mnbt_save_payment_methods($methods)` | 保存支付方式配置 |
| `mnbt_pay_type($slug, $method)` | 构造支付方式 type 字符串 |
| `mnbt_pay_parse_type($type)` | 解析支付方式 type |
| `mnbt_pay_dispatch_gateway($order, $type)` | 分发到支付插件 build 回调 |
| `mnbt_pay_settle_order($order, $gateway_slug, $extra=[])` | **支付结算核心**（更新订单状态 + 触发 `order.paid` 钩子） |
| `mnbt_pay_log($content, $level='info')` | 支付日志记录 |

**支付回调路由约定：**

| URL | 类型 | 说明 |
|-----|------|------|
| `/pay/{slug}/notify` | 异步通知 | 支付网关服务器回调 |
| `/pay/{slug}/return` | 同步返回 | 用户支付完成跳转 |

**支付插件注册示例（epay）：**

```php
mnbt_register_payment('epay', [
    'name'    => '易支付',
    'methods' => ['alipay' => '支付宝', 'wxpay' => '微信', 'qqpay' => 'QQ'],
    'build'   => function ($order, $method) { /* 跳转支付 */ },
    'notify'  => function () { /* 验签 + mnbt_pay_settle_order */ },
    'return'  => function () { /* 同步跳转 */ },
]);
```

**支付流程时序：**

```
用户下单 → mnbt_pay_dispatch_gateway → epay.build() → 跳转易支付
  ↓
用户支付完成 → /pay/epay/notify → epay.notify() → mnbt_pay_settle_order()
  ↓
mnbt_pay_settle_order() → 更新 MN_dd → 触发 order.paid 钩子 → 插件自动开通业务
```

## 5.6 自动鉴权机制

V1.81+ 插件系统支持通过 `auth` 参数声明权限要求，框架自动验证。

### `auth` 取值

| 值 | 含义 | 失败响应 |
|----|------|----------|
| `null` / `''` / `'none'` | 无验证（默认） | — |
| `'admin'` | 需要管理员登录 | `{"code":"请登陆后台"}` |
| `'user'` | 需要用户登录 | `{"code":"请登陆"}` |
| 回调函数 | 自定义验证，返回 `true`/`false` | 自动跳转登录或返回 `{"code":"请登陆"}` |

### 路由自动鉴权

```php
mnbt_register_route('POST', '/api/admin/config', function ($params, $ctx) {
    // 框架已自动验证管理员身份
}, 10, 'admin');
```

### AJAX 自动鉴权

```php
mnbt_register_ajax('admin', 'my_plugin_save', function () {
    // 框架已自动验证管理员
}, 'admin');

mnbt_register_ajax('user', 'my_plugin_get_data', function () {
    // 框架已自动验证用户
}, 'user');
```

### 核心函数

| 函数 | 用途 |
|------|------|
| `mnbt_plugin_auth_check($auth)` | 验证鉴权（返回 bool） |
| `mnbt_plugin_auth_fail($auth)` | 鉴权失败处理（自动 exit） |

---

**相关文档：**

- [API 通用约定](./overview.md)
- [后台管理接口](./admin.md) —— `plugin_*` 管理接口
- [用户控制面板接口](./user.md) —— 插件 AJAX 分发
- [核心工具函数](./functions.md)
