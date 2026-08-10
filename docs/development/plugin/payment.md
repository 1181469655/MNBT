---
title: 支付插件系统
description: MNBT 支付插件系统（V1.81 P3）：概念数据流、关键约定、注册支付插件、订单上下文、回调结算、公共函数、设置页与完整示例
---

# 支付插件系统（V1.81 P3）

本文是 [插件开发手册](./guide.md) §3.12 的拆分章节。把"发起支付 + 异步回调 + 订单结算"做成插件化架构。系统提供注册 API、订单结算函数、统一支付设置页；插件只负责构造网关请求与验签。

## 3.12.1 概念与数据流

```
客户端                    系统层                      支付插件
  │                         │                            │
  │  POST /user/pay.php     │                            │
  │  type=epay__alipay      │                            │
  │ ───────────────────────>│                            │
  │                         │ mnbt_pay_dispatch_gateway  │
  │                         │ ──────────────────────────>│
  │                         │                            │ build(method, order, cfg)
  │                         │ <──────────────────────────│ HTML 表单 / 扫码页
  │ <───────────────────────│                            │
  │                         │                            │
  │  跳转第三方网关 → 支付                                  │
  │                         │                            │
  │  异步回调 /pay/{slug}/notify                          │
  │ ───────────────────────>│ mnbt_register_route        │
  │                         │ ──────────────────────────>│ 验签
  │                         │                            │ mnbt_pay_settle_order()
  │                         │                            │ echo 'success'
```

## 3.12.2 关键约定

| 项目 | 约定 |
|------|------|
| **支付方式 type** | 格式 `{plugin_id}__{method_id}`，如 `epay__alipay`、`alipay_official__pc` |
| **已启用付款方式** | 存 `MN_config.pay_methods` 字段（JSON 数组），由 `admin/pay_settings.php` 维护 |
| **插件 API 凭证** | 存 `MN_plugin_option` 表，由插件自身的设置页维护 |
| **回调路径** | 推荐 `/pay/{slug}/notify`（异步）+ `/pay/{slug}/return`（同步），用 `mnbt_register_route` 注册 |
| **订单结算** | 统一调 `mnbt_pay_settle_order($out_trade_no, $trade_status, $money)` |

## 3.12.3 注册支付插件

```php
mnbt_register_payment('my_pay', [
    'name'        => '我的支付',
    'description' => '一句话说明',
    'icon'        => 'mdi-credit-card',
    'methods'     => [
        'alipay' => ['name' => '支付宝', 'icon' => 'mdi-alpha-a-circle'],
        'wxpay'  => ['name' => '微信',   'icon' => 'mdi-wechat'],
    ],
    'build' => function ($method, $order, $plugin_config) {
        // $method: 'alipay' / 'wxpay'（来自 methods 的 key）
        // $order: ['out_trade_no'=>..., 'name'=>..., 'money'=>..., 'type'=>..., 'siteurl'=>..., 'pay_lx'=>...]
        // $plugin_config: 该插件所有 option（来自 MN_plugin_option）
        // 返回 HTML 字符串（通常是自动提交的表单），或 false 表示不接管

        $cfg = $plugin_config;  // 读取 apiurl/key 等
        // 构造表单...
        return '<form>...</form>';
    },
]);
```

## 3.12.4 订单上下文 `$order`

`user/pay.php` 在创建 `MN_dd` 订单记录后，构造以下数组传给 `mnbt_pay_dispatch_gateway`：

| 字段 | 说明 |
|------|------|
| `out_trade_no` | 商户订单号（`MN_dd.ddh`，全局唯一） |
| `name`         | 订单标题（展示用） |
| `money`        | 金额（元，字符串） |
| `type`         | 支付方式 type，如 `epay__alipay` |
| `siteurl`      | 站点根 URL（带协议 + 末尾斜杠） |
| `pay_lx`       | 业务类型：`yjbs` 一键部署（核心结算内置）；其他业务（如 `ymgm` 域名购买）由对应插件在 `order.paid` 钩子内自行处理 |

插件用 `siteurl` 拼接 `notify_url` / `return_url`，例如：
```php
$notifyUrl = rtrim($order['siteurl'], '/') . '/pay/my_pay/notify';
```

## 3.12.5 回调路由与订单结算

异步通知路由示例：

```php
mnbt_register_route('*', '/pay/my_pay/notify', function ($params, $ctx) {
    @header('Content-Type: text/plain; charset=UTF-8');
    $cfg = mnbt_plugin_option_all('my_pay');
    // 1. 从 $_POST / $_GET 取回调数据
    // 2. 用 $cfg 中的 key 验签
    if (!验签通过) {
        mnbt_pay_log('验签失败', '验签失败', $_POST['out_trade_no'] ?? '');
        echo 'fail';
        return;
    }
    // 3. 调用统一结算函数（处理 yjbs 业务、标记订单完成、触发 order.paid；
    //    其他业务类型如 ymgm 由对应插件在 order.paid 钩子内自行处理）
    $result = mnbt_pay_settle_order(
        $_POST['out_trade_no'],
        $_POST['trade_status'],  // 支付宝系: TRADE_SUCCESS
        $_POST['money']
    );
    echo !empty($result['ok']) ? 'success' : 'fail';
});
```

同步返回路由示例（仅展示，不做业务处理）：

```php
mnbt_register_route('*', '/pay/my_pay/return', function ($params, $ctx) {
    $base = $ctx['base'] ?? '';
    @header('Location: ' . $base . '/user');
});
```

## 3.12.6 公共函数

| 函数 | 说明 |
|------|------|
| `mnbt_register_payment($slug, $config)` | 注册支付插件 |
| `mnbt_get_payment_plugins()` | 获取所有已注册的支付插件（用于支付设置页） |
| `mnbt_pay_type($slug, $method)` | 构造 type 字符串 |
| `mnbt_pay_parse_type($type)` | 解析 type，返回 `['plugin'=>..., 'method'=>...]` 或 false |
| `mnbt_get_enabled_payment_methods()` | 读取已启用的付款方式（按 sort 排序） |
| `mnbt_save_payment_methods($list)` | 保存付款方式列表 |
| `mnbt_pay_dispatch_gateway($type, $order)` | 内部分发：根据 type 调用对应插件的 build 回调 |
| `mnbt_pay_settle_order($no, $status, $money)` | **插件可调**：处理支付成功的订单，返回 `['ok'=>bool,'msg'=>string]` |
| `mnbt_pay_log($content, $status, $orderNo)` | 记录支付日志到 `MN_log` |

## 3.12.7 后台支付设置页

系统内置 `admin/pay_settings.php`，自动列出所有已注册支付插件及其子方式，管理员可：

- 勾选启用的子付款方式
- 设置客户端显示名（默认取 `methods[m]['name']`）
- 设置图标 class（默认取 `methods[m]['icon']`）
- 设置排序（数字越小越靠前）

**插件的 API 凭证不在支付设置页配置**，而是在插件自身的设置页（通过 `mnbt_register_page('admin', 'settings', ...)` 注册）。支付设置页会自动显示"插件设置"按钮跳转。

## 3.12.8 客户端模板适配

客户端支付方式选择已改为动态渲染：

```php
<?php $__methods = function_exists('mnbt_get_enabled_payment_methods')
    ? mnbt_get_enabled_payment_methods() : []; ?>
<?php foreach ($__methods as $__idx => $__m): ?>
<?php $__type = $__m['plugin'] . '__' . $__m['method']; ?>
<label class="lyear-radio radio-inline radio-primary col">
  <input type="radio" name="type" value="<?=htmlspecialchars($__type)?>" <?=$__idx===0?'checked':''?>>
  <i class="mdi <?=htmlspecialchars($__m['icon'] ?? 'mdi-payment')?>"></i>
  <span><?=htmlspecialchars($__m['display_name'])?></span>
</label>
<?php endforeach; ?>
```

## 3.12.9 完整示例

参考 `app_plugins/epay/`（易支付协议）与 `app_plugins/alipay_official/`（支付宝官方 API）。

- **epay**：3 个子方式（alipay/wxpay/qqpay），MD5 签名，自动迁移旧 `MN_config.hxe/hxr/hxt` 配置
- **alipay_official**：2 个子方式（pc 电脑网站支付 / qrcode 当面付），RSA2 签名，基于 dedemao/alipay SDK

更多细节见内置插件文档：[epay](./builtin/epay.md)、[alipay_official](./builtin/alipay-official.md)。
