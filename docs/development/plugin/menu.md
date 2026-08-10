---
title: 菜单与页面
description: 插件菜单注册、页面注册、页面接管（Page Override）与 Partial 接管（Partial Override）详解
---

# 菜单与页面

本文是 [插件开发手册](./guide.md) §3.4 的拆分章节，介绍插件菜单注册、页面注册、页面接管（Page Override）与 Partial 接管。

## 3.4 菜单与页面

```php
// 单个入口：会被自动归入「插件管理」分组
mnbt_register_menu('admin', [
	'title' => '标题',
	'page' => 'settings',     // 自动生成 url: plugin.php?p=slug&page=settings
	// 或 'url' => 'https://...',
	'icon' => 'mdi-webhook',  // Material Design Icons 类名（不含 mdi 前缀时引擎会加）
	'order' => 20,
	'multitabs' => true,
]);

// 独立分组（推荐：一个插件有多个入口时）
mnbt_register_menu('user', [
	'title'    => '域名服务',
	'icon'     => 'mdi-web',
	'order'    => 40,
	'children' => [
		['title' => '域名绑定', 'page' => 'bind', 'icon' => 'mdi-domain', 'multitabs' => true],
		['title' => 'DNS 记录', 'page' => 'dns_records', 'icon' => 'mdi-dns', 'multitabs' => true],
	],
]);

mnbt_register_page('admin', 'settings', 'admin/settings.php', '页面标题');
mnbt_register_page('user', 'index', 'user/index.php', '用户页');
```

| 入口 | URL |
|------|-----|
| 管理页 | `admin/plugin.php?p={slug}&page={page}` |
| 用户页 | `user/plugin.php?p={slug}&page={page}` |

**菜单渲染规则**：
- 注册时带 `children` 的项 → 渲染为独立侧边栏分组（如「域名服务」）
- 注册时不带 `children` 的项 → 统一归入「插件管理」分组
- 分组和叶子项都按 `order` 升序排列

**多主题适配**：
- 插件只负责提供**菜单数据树**，不要写死 HTML 结构
- 每个主题可注册自己的菜单渲染器（详见 [主题开发手册](../theme/index.md) §7.4）
- 引擎会自动使用当前主题渲染器，未注册时回退到 default 主题结构
- 因此插件新增/修改菜单项后，**无需修改任何主题文件**

页面文件内可：

- 管理端：`mnbt_admin_include('head');`
- 用户端：`mnbt_theme_include('head');`（与 default 主题一致时）
- 使用全局 `$DB`、`$conf`、`$yhc`（用户端）、`$islogin` / `$islogins`

### 3.4.1 页面接管（Page Override）

让插件**接管或包裹已有主题页面的整页输出**，无需修改主题文件。当核心入口（`user/set.php`、`admin/list.php` 等）调用 `mnbt_render($view)` 时，引擎会按 priority 升序遍历所有注册的 override 回调，第一个返回非 null 的值即生效，后续回调不再调用（短路语义）。

```php
// 模式 1：完全接管（替换整页）
mnbt_register_page_override('user', 'set', function ($vars) {
    if (($_GET['gn'] ?? '') !== 'my_section') return null; // 其他 gn 走原逻辑
    return '<div>我的自定义内容</div>';
});

// 模式 2：包裹模式（在原页面前后插入 banner）
mnbt_register_page_override('user', 'index', function ($vars) {
    return [
        'before' => '<div class="banner">公告</div>',
        'after'  => '<script>console.log("page loaded")</script>',
    ];
});

// 模式 3：管理端接管 + 优先级控制
mnbt_register_page_override('admin', 'list', function ($vars) {
    if (($_GET['gn'] ?? '') === 'plugin_section') {
        return render_my_plugin_page();
    }
    return null;
}, 5); // priority=5，比默认 10 更早执行
```

| 参数 | 说明 |
|------|------|
| `scope` | `'user'` 或 `'admin'` |
| `view` | 视图名（如 `'set'`、`'list'`、`'sy'`、`'index'`），对应 `mnbt_render($view)` 的参数 |
| `callback` | 签名 `function(array $vars): mixed`，详见下方回调返回值 |
| `priority` | 优先级（数字越小越先执行），默认 10 |

**回调返回值（三选一）**：
- `null` → 不接管，继续加载原主题文件（默认行为）
- `string` → 完全接管，直接输出该字符串，跳过原主题文件
- `['before' => string, 'after' => string]` → 包裹模式，在原主题文件输出前后插入内容（`before`/`after` 任一可省略）

**多插件协作**：
- 多个插件注册同一 view 的 override 时，按 priority 升序执行
- 第一个返回非 null 的回调生效，后续回调不再调用（短路语义）
- 想让某插件优先接管，给它更低的 priority（如 `5`、`1`）

**适用场景**：
- 插件化原核心功能（如接管 `set.php` 的某个 `gn` 分支）
- 在所有页面注入全局 banner / 公告 / 统计代码（包裹模式 + 高 priority）
- 替换整个页面输出（如自定义登录页、自定义后台首页）
- 根据请求参数（`$_GET['gn']`）决定是否接管

### 3.4.2 Partial 接管（Partial Override）

让插件**接管或包裹主题局部模板**（如 `head`、`footer`）的输出。当主题调用 `mnbt_theme_include($view)` 时，引擎会按 priority 升序遍历所有注册的 override 回调，第一个返回非 null 的值即生效。

```php
// 模式 1：完全接管 head
mnbt_register_partial_override('user', 'head', function ($vars) {
    return '<!-- 自定义 head -->...';
});

// 模式 2：在 head 末尾追加自定义 CSS（包裹模式）
mnbt_register_partial_override('user', 'head', function ($vars) {
    return ['after' => '<style>.my-plugin-banner{color:red}</style>'];
});

// 模式 3：在 footer 开头插入统计代码
mnbt_register_partial_override('user', 'footer', function ($vars) {
    return ['before' => '<script src="analytics.js"></script>'];
});
```

参数与回调返回值与 `mnbt_register_page_override` 完全相同。
