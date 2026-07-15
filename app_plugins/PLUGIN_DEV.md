# MNBT 插件开发手册

本文面向需要**开发 PHP 业务插件**的开发者。  
先读 [README.md](./README.md) 了解启用方式与目录约定。

> **注意**：本文的插件与宝塔侧 `plugins/mnbt_connector`（Python 节点代理）**无关**。  
> PHP 业务插件只放在站点根目录 `app_plugins/` 下。

---

## 1. 设计原则

1. **不改核心 URL**：业务接口仍走 `user/ajax.php` / `admin/ajax.php`；页面走 `plugin.php?p=slug&page=...`。
2. **插件只注册、不劫持核心 `gn`**：自定义 AJAX 的 `gn` 必须用前缀，建议 `p_{slug}_{action}`。
3. **配置进 option 表**：用 `MN_plugin_option`，不要往 `MN_config` 加列。
4. **文件只在插件目录内**：页面路径禁止跳出 `app_plugins/{slug}/`。
5. **与主题分离**：主题管外观（`templates/`）；插件管业务能力。
6. **升级友好**：自定义代码放在 `app_plugins/`，避免改 `MPHX/`、`user/`、`admin/` 核心文件。

---

## 2. 新建插件（最短路径）

### 步骤 1：创建目录

```text
app_plugins/
└── my_plugin/              # 目录名 = 插件 ID（字母数字下划线横线，≤63）
    ├── plugin.json         # 必填
    ├── bootstrap.php       # 必填
    ├── install.sql         # 可选：建插件自有表
    ├── uninstall.sql       # 可选：卸载时清理
    ├── admin/              # 可选：后台页面
    │   └── index.php
    ├── user/               # 可选：用户端页面
    │   └── index.php
    └── assets/             # 可选：静态资源
```

### 步骤 2：编写 `plugin.json`

```json
{
  "id": "my_plugin",
  "name": "我的插件",
  "version": "1.0.0",
  "author": "YourName",
  "description": "插件一句话说明",
  "requires_mnbt": "1.81",
  "type": ["business"]
}
```

| 字段 | 必填 | 说明 |
|------|------|------|
| `id` | 建议 | 与目录名一致；引擎以**目录名**为准 |
| `name` | 是 | 后台「插件管理」显示名 |
| `version` | 是 | 版本号 |
| `author` | 否 | 作者 |
| `description` | 否 | 简介 |
| `requires_mnbt` | 否 | 文档用最低版本 |
| `type` | 否 | 文档分类，如 `business` / `lifecycle` / `integration` |

### 步骤 3：编写 `bootstrap.php`

```php
<?php
if (!defined('IN_CRONLITE')) {
	exit;
}

mnbt_plugin_register('my_plugin', ['name' => '我的插件']);

// 后台菜单
mnbt_register_menu('admin', [
	'title' => '我的插件',
	'page' => 'index',
	'icon' => 'mdi-puzzle',
	'order' => 20,
	'multitabs' => true,
]);

// 后台页面（相对插件根目录）
mnbt_register_page('admin', 'index', 'admin/index.php', '我的插件');

// 可选：出现在「插件管理」页顶部快捷入口
mnbt_register_settings_tab([
	'title' => '我的插件设置',
	'page' => 'index',
	'order' => 20,
]);

// AJAX：POST admin/ajax.php  gn=p_my_plugin_ping
mnbt_register_ajax('admin', 'p_my_plugin_ping', function () {
	mnbt_plugin_require_admin();
	json_exit_success('pong', ['time' => date('Y-m-d H:i:s')]);
});

// 生命周期钩子
mnbt_add_action('host.created', function ($host, $ctx = []) {
	// $host 为主机行数组；$ctx 含 source 等
});
```

### 步骤 4：后台页面示例 `admin/index.php`

```php
<?php
if (!defined('IN_CRONLITE')) {
	exit;
}
mnbt_admin_include('head');
?>
<div class="container-fluid p-t-15">
  <div class="card">
    <div class="card-header"><h4>我的插件</h4></div>
    <div class="card-body">
      <button type="button" class="btn btn-primary" id="btn-ping">Ping</button>
    </div>
  </div>
</div>
<script>
$('#btn-ping').on('click', function () {
  $.post('ajax.php', {gn: 'p_my_plugin_ping'}, function (res) {
    try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
    alert(res.msg || res.code || JSON.stringify(res));
  });
});
</script>
```

### 步骤 5：启用

1. 将目录放到 `app_plugins/my_plugin/`
2. 后台 → 系统管理 → **插件管理** → **安装** → **启用**
3. **整页刷新**后台（侧栏菜单才会出现）

### 步骤 6：自测清单

- [ ] 插件管理中可见、可启用/禁用  
- [ ] 侧栏菜单可打开页面  
- [ ] AJAX 成功返回 JSON  
- [ ] 禁用后接口/菜单失效  
- [ ] 主机开通/删除等钩子（若用了）有预期行为  

---

## 3. 核心 API 详解

引擎文件：`MPHX/plugin.php`（由 `common.php` 在鉴权后启动 `mnbt_plugins_boot()`）。

### 3.1 注册与元信息

| 函数 | 说明 |
|------|------|
| `mnbt_plugin_register($id, $meta)` | 注册元信息（可选，推荐写） |
| `mnbt_plugin_id()` | 当前插件 slug（钩子/AJAX 回调内有效） |
| `mnbt_plugin_path($slug = null)` | 插件绝对路径，末尾带 `/` |
| `mnbt_plugin_url($slug = null, $rel = '')` | 资源 URL，如 `/app_plugins/my_plugin/assets/a.css` |
| `mnbt_plugin_enabled($slug)` | 是否已启用 |

### 3.2 钩子（Action / Filter）

```php
// 监听
mnbt_add_action('host.created', function ($host, $ctx = []) { ... }, 10);
mnbt_add_filter('menu.admin', function ($items) {
	// 可改菜单数组后 return
	return $items;
}, 10);

// 触发（一般由核心调用，插件很少自己 do_action）
mnbt_do_action('my_event', $arg1, $arg2);
$value = mnbt_apply_filters('my_filter', $value, $extra);
```

- 同一钩子可多个回调；`$priority` 数字越小越先执行（默认 `10`）。
- 回调异常会被捕获并写入 PHP 错误日志，不中断主流程。

### 3.3 AJAX

```php
mnbt_register_ajax('admin', 'p_my_plugin_save', function ($egn, $side) {
	mnbt_plugin_require_admin();
	// 读 $_POST，写 option，返回 JSON
	json_exit_success('已保存');
});

mnbt_register_ajax('user', 'p_my_plugin_list', function () {
	mnbt_plugin_require_user();
	// 全局 $yhc 为当前主机用户
	json_exit_success('ok', ['items' => []]);
});
```

| 侧 | 请求 | 鉴权 |
|----|------|------|
| admin | `POST admin/ajax.php`，`gn=...` | 管理员 cookie；回调内再 `mnbt_plugin_require_admin()` |
| user | `POST user/ajax.php`，`gn=...` | 用户已登录；回调内再 `mnbt_plugin_require_user()` |

分发顺序：**插件注册表 → 核心 `api/*.php`**。  
重复 `gn` 后注册失败并写错误日志。

**推荐返回：**

```php
json_exit_success($msg, $extra);  // qk=1
json_exit_error($msg, $extra);    // qk=4
// 或兼容旧式：
json_exit('提示文案');
```

### 3.4 菜单与页面

```php
mnbt_register_menu('admin', [
	'title' => '标题',
	'page' => 'settings',     // 自动生成 url: plugin.php?p=slug&page=settings
	// 或 'url' => 'https://...',
	'icon' => 'mdi-webhook',  // Material Design Icons 类名（不含 mdi 前缀时引擎会加）
	'order' => 20,
	'multitabs' => true,
]);

mnbt_register_page('admin', 'settings', 'admin/settings.php', '页面标题');
mnbt_register_page('user', 'index', 'user/index.php', '用户页');
```

| 入口 | URL |
|------|-----|
| 管理页 | `admin/plugin.php?p={slug}&page={page}` |
| 用户页 | `user/plugin.php?p={slug}&page={page}` |

页面文件内可：

- 管理端：`mnbt_admin_include('head');`
- 用户端：`mnbt_theme_include('head');`（与 default 主题一致时）
- 使用全局 `$DB`、`$conf`、`$yhc`（用户端）、`$islogin` / `$islogins`

### 3.5 配置存储

表：`MN_plugin_option`（`plugin_slug` + `k` + `v`）。

```php
mnbt_plugin_option_set('my_plugin', 'api_key', 'xxx');
$v = mnbt_plugin_option_get('my_plugin', 'api_key', '默认值');
// 数组/对象会自动 JSON 编解码
mnbt_plugin_option_set('my_plugin', 'flags', ['a' => true]);
$all = mnbt_plugin_option_all('my_plugin');
```

### 3.6 仪表盘小部件

```php
mnbt_register_widget('admin', [
	'title' => '统计卡片',
	'order' => 10,
	'class' => 'col-sm-6',
	'callback' => function ($side) {
		echo '<p>内容 HTML</p>';
	},
	// 或 'html' => '<p>静态 HTML</p>',
]);
```

渲染位置：

- 管理首页：`templates/default/admin/sy.php`
- 用户仪表盘：`templates/default/user/sy.php`

### 3.7 设置快捷入口

```php
mnbt_register_settings_tab([
	'title' => 'Webhook 通知',
	'page' => 'settings',
	'order' => 10,
]);
```

显示在后台 **插件管理** 页顶部按钮区。

### 3.8 HTTP 出站

```php
$res = mnbt_http_post('https://example.com/hook', [
	'event' => 'test',
], [
	'timeout' => 10,
	'headers' => ['X-Token: abc'],
	// 'insecure' => true,      // 跳过 SSL 校验（不推荐）
	// 'allow_private' => true, // 允许内网（默认禁止）
]);
// $res = ['ok'=>bool, 'code'=>int, 'body'=>string, 'error'=>string]
```

仅允许 `http://` / `https://`；默认拒绝 localhost / 私网 IP。

### 3.9 日志

```php
mnbt_log($user ?: '系统', '插件-我的插件', '做了某事', '成功', $DB);
```

### 3.10 首页接管（V1.81 P2）

让插件接管站点根路径 `/` 的响应。默认行为是 `header("Location: user")`，注册后可改为重定向到任意地址，或直接渲染自定义首页。

```php
mnbt_register_home(function ($ctx) {
    // $ctx = ['path'=>'/', 'method'=>'GET', 'base'=>'']

    // 模式 A：重定向到其他地址
    return '/user/plugin.php?p=my_home&page=index';

    // 模式 B：直接渲染首页内容
    // echo '<!doctype html><h1>自定义首页</h1>';
    // return true;

    // 模式 C：不接管，回退到默认行为
    // return false;
}, 10);
```

**回调返回值约定：**

| 返回值 | 引擎行为 |
|--------|----------|
| `string`（非空） | 视为重定向 URL，`header("Location: ...")` + `exit` |
| `true` | 视为已渲染（回调内自行 `echo`），引擎 `exit` |
| `false` / `null` | 不接管，继续下一个回调或回退到默认 `/user` |

- `$priority` 数字越小越先执行（默认 10）。
- 多个插件注册时，第一个返回 `string` 或 `true` 的回调会终止请求。
- 回调异常会被捕获并写日志，不会中断主流程。
- 仅当请求路径为 `/` 时才会触发；其他路径请用 [通用路由](#311-通用路由-v181-p2)。

### 3.11 通用路由（V1.81 P2）

让插件接管任意路径的响应，例如 `/landing`、`/promo/{id}`。路径支持命名参数，方法可限定。

```php
// 简单路径
mnbt_register_route('GET', '/landing', function ($params, $ctx) {
    // $ctx = ['path'=>'/landing', 'method'=>'GET', 'base'=>'', 'plugin'=>'my_plugin', 'route'=>'/landing']
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>活动落地页</h1>';
    // 不返回或返回 true → 视为已处理
});

// 带命名参数
mnbt_register_route('GET', '/promo/{id}', function ($params, $ctx) {
    $id = $params['id'];  // 从路径提取
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>推广 ID: ' . htmlspecialchars($id) . '</h1>';
});

// POST 接口
mnbt_register_route('POST', '/api/custom-hook', function ($params, $ctx) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => true]);
});

// 匹配任意方法
mnbt_register_route('*', '/health', function ($params, $ctx) {
    echo 'ok';
});
```

**回调返回值约定：**

| 返回值 | 引擎行为 |
|--------|----------|
| `false` | 显式不接管，继续匹配下一个路由 |
| `true` / `null` | 视为已处理，引擎 `exit` |
| `string`（非空） | 若未自行输出，引擎会以 `text/html` 输出该字符串 |

**路径规则：**

- 必须以 `/` 开头（否则引擎自动补 `/`）。
- 命名参数格式 `{name}`，匹配 `[^/]+`（不含斜杠的任意字符）。
- 尾斜杠可选：注册 `/landing` 时，`/landing/` 也会匹配。
- 路径基于站点根（已自动剥离子目录前缀），子目录部署时插件无需关心 base path。

**Web 服务器配置：**

通用路由需要 Web 服务器把未命中实际文件的请求转发到 `index.php`：

- **开发环境（PHP 内置服务器）**：`_router.php` 已自动支持，无需额外配置。
  ```bash
  php -S localhost:8080 _router.php
  ```
- **Nginx**：在站点配置中加入：
  ```nginx
  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }
  ```
- **Apache**：在站点根目录 `.htaccess` 中加入：
  ```apache
  <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteRule ^(.*)$ index.php [QSA,L]
  </IfModule>
  ```

### 3.12 支付插件系统（V1.81 P3）

把"发起支付 + 异步回调 + 订单结算"做成插件化架构。系统提供注册 API、订单结算函数、统一支付设置页；插件只负责构造网关请求与验签。

#### 3.12.1 概念与数据流

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

#### 3.12.2 关键约定

| 项目 | 约定 |
|------|------|
| **支付方式 type** | 格式 `{plugin_id}__{method_id}`，如 `epay__alipay`、`alipay_official__pc` |
| **已启用付款方式** | 存 `MN_config.pay_methods` 字段（JSON 数组），由 `admin/pay_settings.php` 维护 |
| **插件 API 凭证** | 存 `MN_plugin_option` 表，由插件自身的设置页维护 |
| **回调路径** | 推荐 `/pay/{slug}/notify`（异步）+ `/pay/{slug}/return`（同步），用 `mnbt_register_route` 注册 |
| **订单结算** | 统一调 `mnbt_pay_settle_order($out_trade_no, $trade_status, $money)` |

#### 3.12.3 注册支付插件

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

#### 3.12.4 订单上下文 `$order`

`user/pay.php` 在创建 `MN_dd` 订单记录后，构造以下数组传给 `mnbt_pay_dispatch_gateway`：

| 字段 | 说明 |
|------|------|
| `out_trade_no` | 商户订单号（`MN_dd.ddh`，全局唯一） |
| `name`         | 订单标题（展示用） |
| `money`        | 金额（元，字符串） |
| `type`         | 支付方式 type，如 `epay__alipay` |
| `siteurl`      | 站点根 URL（带协议 + 末尾斜杠） |
| `pay_lx`       | 业务类型：`yjbs` 一键部署 / `ymgm` 域名购买 |

插件用 `siteurl` 拼接 `notify_url` / `return_url`，例如：
```php
$notifyUrl = rtrim($order['siteurl'], '/') . '/pay/my_pay/notify';
```

#### 3.12.5 回调路由与订单结算

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
    // 3. 调用统一结算函数（处理 yjbs/ymgm 业务、标记订单完成、触发 order.paid）
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

#### 3.12.6 公共函数

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

#### 3.12.7 后台支付设置页

系统内置 `admin/pay_settings.php`，自动列出所有已注册支付插件及其子方式，管理员可：

- 勾选启用的子付款方式
- 设置客户端显示名（默认取 `methods[m]['name']`）
- 设置图标 class（默认取 `methods[m]['icon']`）
- 设置排序（数字越小越靠前）

**插件的 API 凭证不在支付设置页配置**，而是在插件自身的设置页（通过 `mnbt_register_page('admin', 'settings', ...)` 注册）。支付设置页会自动显示"插件设置"按钮跳转。

#### 3.12.8 客户端模板适配

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

#### 3.12.9 完整示例

参考 `app_plugins/epay/`（易支付协议）与 `app_plugins/alipay_official/`（支付宝官方 API）。

- **epay**：3 个子方式（alipay/wxpay/qqpay），MD5 签名，自动迁移旧 `MN_config.hxe/hxr/hxt` 配置
- **alipay_official**：2 个子方式（pc 电脑网站支付 / qrcode 当面付），RSA2 签名，基于 dedemao/alipay SDK

---

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

**主机 `$host` 敏感字段**（密码等）可能存在于数组中；对外推送时务必自行脱敏（参见 `webhook_notify`）。

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

## 7. 与主题 / SPA 的关系

| 前端 | 插件如何扩展 |
|------|----------------|
| **default 管理端** | 菜单 + `plugin.php` 页面 + 小部件（完整支持） |
| **default 用户端** | 侧栏插件菜单 + `user/plugin.php` + 仪表盘小部件 |
| **qingliangyun SPA** | 优先提供 JSON AJAX（`user/ajax.php`）；SPA 原生菜单/路由需另约定（当前未内置协议） |

不要在插件里硬编码 `../imsetes/`；公共资源用 `mnbt_asset_url()`，主题资源用 `mnbt_theme_asset()`。

---

## 8. 完整示例：监听开通并写日志

```php
// app_plugins/open_log/bootstrap.php
<?php
if (!defined('IN_CRONLITE')) exit;

mnbt_plugin_register('open_log', ['name' => '开通日志']);

mnbt_add_action('host.created', function ($host, $ctx = []) {
	$u = is_array($host) ? ($host['user'] ?? '') : '';
	$src = is_array($ctx) ? ($ctx['source'] ?? '') : '';
	$line = date('Y-m-d H:i:s') . " open user={$u} source={$src}";
	$log = mnbt_plugin_option_get('open_log', 'lines', []);
	if (!is_array($log)) $log = [];
	array_unshift($log, $line);
	mnbt_plugin_option_set('open_log', 'lines', array_slice($log, 0, 100));
});
```

更完整的可运行示例：

| 目录 | 演示点 |
|------|--------|
| `hello_demo/` | 菜单、配置读写、Ping AJAX、主机/订单事件本地日志 |
| `webhook_notify/` | 设置页、事件开关、HTTP POST、HMAC 签名、投递日志、后台小部件 |

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
| [README.md](./README.md) | 插件目录总览、快速启用 |
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

---

## 12. 版本与路线

| 版本 | 能力 |
|------|------|
| V1.81 P0 | 引擎、安装启用、AJAX/菜单/页面、host 钩子、cron、示例 |
| V1.81 P1 | HTTP、widget、settings_tab、order.paid、host.renewed、用户菜单、Webhook 插件 |
| V1.81 P2 | 首页接管（`mnbt_register_home`）、通用路由（`mnbt_register_route`）、路径参数匹配、`_router.php` 路由分发 |
| V1.81 P3 | 支付插件系统（`mnbt_register_payment`、`mnbt_pay_settle_order`）、统一支付设置页、易支付/支付宝官方插件、旧 `notify_url.php`/`return_url.php` 完全废弃 |

后续可能：zip 安装、`gn` 冲突检测 UI、qingliangyun SPA 菜单协议、细粒度能力 ACL。
