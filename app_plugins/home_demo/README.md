# home_demo 首页接管示例插件

演示 `mnbt_register_home` 接管站点首页（重定向/渲染/关闭三模式），以及 `mnbt_register_route` 注册通用路由。

## 功能

- 首页接管三模式：
  - `off`：不接管，回退默认 `/user` 跳转
  - `redirect`：重定向到指定 URL
  - `render`：渲染自定义首页模板
- 通用路由示例：
  - `GET /landing` — 活动落地页
  - `GET /promo/{id}` — 带命名参数的推广页
- 后台设置页（选择模式、配置重定向目标）

## 依赖

无。这是演示插件，无业务依赖。

## 安装

1. 后台 → 系统管理 → 插件管理 → 安装 `home_demo` → 启用
2. 在插件设置页选择模式

## 配置项

存储在 `MN_plugin_option` 表（plugin_slug='home_demo'）：

| key | 说明 |
|-----|------|
| `mode` | 模式：off / redirect / render |
| `redirect_target` | 重定向目标 URL（mode=redirect 时有效） |

## 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/` | 首页（通过 `mnbt_register_home` 接管） |
| GET | `/landing` | 活动落地页演示 |
| GET | `/promo/{id}` | 带命名参数的推广页演示 |

访问方式（无需 rewrite）：

```
index.php?_r=/landing
index.php?_r=/promo/mnbt2026
```

## 管理员端

| 页面 | 入口 |
|------|------|
| 设置页 | `plugin.php?p=home_demo&page=index` |

管理员 AJAX：

| gn | 说明 |
|----|------|
| `p_home_demo_save` | 保存模式与重定向目标 |

## 演示要点

本插件展示了 P2 路由系统的两个核心 API：

### mnbt_register_home

```php
mnbt_register_home(function ($ctx) {
    $mode = mnbt_plugin_option_get('home_demo', 'mode', 'off');
    if ($mode === 'off') return false;           // 不接管
    if ($mode === 'redirect') return '/target';  // 返回字符串=重定向
    if ($mode === 'render') { /* echo */ return true; } // 返回 true=已渲染
});
```

### mnbt_register_route

```php
// 命名参数 {id} 匹配 [^/]+
mnbt_register_route('GET', '/promo/{id}', function ($params, $ctx) {
    echo '推广 ID: ' . $params['id'];
});
```

## 文件结构

```
home_demo/
├── plugin.json
├── bootstrap.php        # 主入口（首页接管 + 路由 + 设置页 + AJAX）
├── install.sql          # 无自建表
└── admin/
    └── index.php        # 设置页
└── user/
    ├── home.php         # 自定义首页模板（render 模式）
    └── landing.php      # 活动落地页模板
```

## 相关文档

- [PLUGIN_DEV.md §3.11 通用路由](../PLUGIN_DEV.md) — P2 路由系统 API
