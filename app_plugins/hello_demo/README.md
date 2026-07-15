# hello_demo 示例插件

基础示例插件，演示菜单注册、AJAX 接口、配置存储与主机生命周期钩子。

## 功能

- 后台菜单与页面注册
- AJAX 接口（ping 计数、保存欢迎语）
- 插件配置读写（`mnbt_plugin_option_get/set`）
- 主机生命周期钩子监听（created/deleted/paused/renewed）
- 订单支付钩子监听（order.paid）
- 事件日志（保留最近 50 条）

## 依赖

无。这是演示插件，无业务依赖。

## 安装

1. 后台 → 系统管理 → 插件管理 → 安装 `hello_demo` → 启用
2. 整页刷新后台，侧栏「插件」分组出现「Hello 示例」菜单

## 配置项

存储在 `MN_plugin_option` 表（plugin_slug='hello_demo'）：

| key | 说明 |
|-----|------|
| `welcome` | 欢迎语（ping AJAX 返回此内容） |
| `ping_count` | ping 次数计数器 |
| `host_events` | 主机事件日志数组（最近 50 条） |

## 钩子

| 钩子 | 说明 |
|------|------|
| `host.created` | 记录主机开通事件 |
| `host.deleted` | 记录主机删除事件 |
| `host.paused` | 记录主机暂停事件 |
| `host.renewed` | 记录主机续费事件（含旧/新到期时间） |
| `order.paid` | 记录订单支付事件（含订单号、金额） |

## 管理员端

| 页面 | 入口 |
|------|------|
| 示例页 | `plugin.php?p=hello_demo&page=index` |

管理员 AJAX：

| gn | 说明 |
|----|------|
| `p_hello_demo_ping` | ping 接口，返回欢迎语并递增计数 |
| `p_hello_demo_save` | 保存欢迎语 |

## 演示要点

本插件展示了插件开发的基础模式：

### 注册菜单与页面

```php
mnbt_register_menu('admin', [
    'title' => 'Hello 示例',
    'page'  => 'index',
    'icon'  => 'mdi-hand-okay',
]);
mnbt_register_page('admin', 'index', 'admin/index.php', 'Hello 示例');
```

### 注册 AJAX

```php
mnbt_register_ajax('admin', 'p_hello_demo_ping', function () {
    mnbt_plugin_require_admin();  // 鉴权
    $msg = mnbt_plugin_option_get('hello_demo', 'welcome', '默认值');
    json_exit_success($msg, ['ping_count' => $count]);
});
```

### 监听钩子

```php
mnbt_add_action('host.created', function ($host, $ctx = []) {
    // $host 是主机数组，$ctx 是上下文
    $user = $host['user'] ?? '';
    // 记录到插件配置
    mnbt_plugin_option_set('hello_demo', 'host_events', $log);
});
```

## 文件结构

```
hello_demo/
├── plugin.json
├── bootstrap.php        # 主入口（菜单 + AJAX + 钩子）
├── install.sql          # 无自建表
└── admin/
    └── index.php        # 示例页
```

## 相关文档

- [PLUGIN_DEV.md](../PLUGIN_DEV.md) — 插件开发手册
