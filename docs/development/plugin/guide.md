---
title: 插件开发手册
description: MNBT 插件开发手册：设计原则、新建插件（最短路径）、核心 API 详解（3.1-3.11）与完整示例
---

# MNBT 插件开发手册

本文面向需要**开发 PHP 业务插件**的开发者。  
先读 [插件系统总览](./index.md) 了解启用方式与目录约定。

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

**注意：与核心 `gn` 同名的陷阱**  
插件 AJAX 在 `user/ajax.php` 中分发时，**早于**核心条件分支（如 CDN 产品检查 `hxc=='1'`）。
因此若插件注册了 `gn='tjurl'`，则 CDN 产品的 `tjurl` 请求也会被插件处理器接管，
即便插件本意只想处理非 CDN 场景。最佳实践：始终使用 `p_{slug}_{action}` 前缀。
参考 `domain_shop` 插件：原 `tjurl/scurl/seturl` 改名为 `p_domain_tjurl` 等，
让 CDN 产品继续走核心 `user/api/cdn.php`。

**推荐返回：**

```php
json_exit_success($msg, $extra);  // qk=1
json_exit_error($msg, $extra);    // qk=4
// 或兼容旧式：
json_exit('提示文案');
```

### 3.4 菜单与页面

菜单注册、页面注册、页面接管（Page Override）与 Partial 接管见 **[菜单与页面](./menu.md)**。

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

让插件接管站点根路径 `/` 的响应（重定向 / 渲染 / 关闭三模式），见 **[首页接管与通用路由](./route.md)**。

### 3.11 通用路由（V1.81 P2）

让插件接管任意路径的响应（支持命名参数、方法限定、两种访问方式），见 **[首页接管与通用路由](./route.md)**。

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

更多章节：钩子与数据库、安全清单、FAQ、文件索引见 **[钩子与数据库](./hooks.md)**；支付插件系统见 **[支付插件系统](./payment.md)**。
