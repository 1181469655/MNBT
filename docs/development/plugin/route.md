---
title: 首页接管与通用路由
description: MNBT 插件 P2 路由系统：首页接管（mnbt_register_home）与通用路由（mnbt_register_route）详解
---

# 首页接管与通用路由（V1.81 P2）

本文是 [插件开发手册](./guide.md) §3.10 / §3.11 的拆分章节，介绍 P2 路由系统的两个核心 API。

## 3.10 首页接管（V1.81 P2）

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

## 3.11 通用路由（V1.81 P2）

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

通用路由支持两种访问方式：

**方式一：查询参数路由（无需 rewrite，推荐）**

引擎支持通过 `index.php?_r=/path` 访问任意插件路由，无需任何 Web 服务器配置。
插件提供的 `xxx_url()` 辅助函数（如 `user_info_url()`、`balance_url()`、`hosting_url()`）
已默认生成此格式的 URL，直接可用。

```
http://example.com/index.php?_r=/account/register
http://example.com/index.php?_r=/balance/recharge
http://example.com/index.php?_r=/shop
```

**方式二：伪静态路径（需 rewrite，URL 更美观）**

如希望使用 `/account/register` 这样无 `index.php?_r=` 前缀的简洁 URL，
需配置 Web 服务器把未命中实际文件的请求转发到 `index.php`：

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

参考示例：内置插件 [home_demo](./builtin/home-demo.md)（首页接管 + 通用路由演示）。
