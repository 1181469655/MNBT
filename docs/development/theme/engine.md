---
title: 主题引擎进阶
description: 主题引擎行为细节、混合 UI 框架主题策略、官方主题示例、发布主题包建议与版本兼容
---

# 主题引擎进阶

本文是 [主题系统总览](./index.md) 与 [主题开发手册](./guide.md) 的进阶篇,涵盖:

- [8. 主题引擎行为细节](#8-主题引擎行为细节)
- [9. 混合 UI 框架主题策略](#9-混合-ui-框架主题策略)
- [10. 官方主题示例](#10-官方主题示例)
- [11. 发布主题包建议](#11-发布主题包建议)
- [14. 版本与兼容](#14-版本与兼容)

---

## 8. 主题引擎行为细节

### 8.1 解析顺序(以用户端 `sy` 为例)

1. `templates/{当前主题}/user/sy.php`
2. 若不存在:`templates/default/user/sy.php`
3. 仍不存在:输出错误 `Theme view not found`

### 8.2 主题名校验

仅保留 `[a-zA-Z0-9_-]`,防止路径注入。

### 8.3 写入激活文件

`mnbt_theme_set_active('user', 'my_theme')` 会:

1. 检查目录 `templates/my_theme/user` 是否存在
2. 写入 `templates/active_user_theme`
3. 尝试 `UPDATE MN_config SET usertheme=?`(字段不存在则失败被忽略)

管理端同理(`admintheme` / `active_admin_theme`)。

### 8.4 页面接管机制(Page Override)

从 V1.82 起,主题引擎在 `mnbt_render()` 和 `mnbt_theme_include()` 中增加了**前置 override**机制,允许插件接管或包裹主题文件输出:

| 引擎函数 | override 名 | 触发时机 |
|----------|-------------|----------|
| `mnbt_render($view)` | `render.{scope}.{view}` | 加载主题页面前 |
| `mnbt_theme_include($view)` | `include.{scope}.{view}` | 加载 partial 前 |

**回调返回值的三种模式**:
- `null` → 不接管,主题文件照常加载(默认行为)
- `string` → 完全接管,直接输出该字符串,主题文件**不会被执行**
- `['before' => string, 'after' => string]` → 包裹模式,在主题文件输出前后插入内容

**多插件协作**:
- 按 priority 升序遍历,第一个返回非 null 的回调生效(短路语义)
- 后续回调不再调用

**典型场景**:
- 完全接管:插件替换某页面分支(如 `set.php?gn=url`)
- 包裹模式:在所有页面注入全局 banner / 公告 / 统计代码
- 优先级控制:低 priority 抢注接管,高 priority 包裹装饰

**主题开发者注意**:
- 此机制**不影响**主题文件的编写,只是多了一个"被插件接管/包裹"的可能
- 如果想让某页面**完全不被插件接管**,可在主题中直接 `include` 而非 `mnbt_render()`(不推荐,会破坏插件生态)
- 完全接管模式下,插件返回的 HTML 通常已自带 `mnbt_theme_include('head')`,主题无需担心样式缺失
- 包裹模式下,主题文件正常执行,插件只是在前后追加内容,不影响主题布局

详见 [插件开发手册](../plugin/index.md) §3.4.1。

---

## 9. 混合 UI 框架主题策略

如果主题采用 Bootstrap 之外的 UI 库(如 Layui、Element、Tailwind),同时又想让未覆盖页面**回退到 default**,需要遵守以下策略:

### 9.1 head.php 必须同时加载两套栈

```php
<!-- 1. 回退页依赖的 Bootstrap / jQuery 栈(保留) -->
<link href="<?= mnbt_asset_url('css/bootstrap.min.css') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery.min.js') ?>"></script>
<script src="<?= mnbt_asset_url('js/fn-hs.js') ?>"></script>

<!-- 2. 主题专属 UI 库(新增) -->
<link href="https://unpkg.com/layui@2.9.8/dist/css/layui.css" rel="stylesheet">

<!-- 3. 主题覆盖样式(最后加载,优先级最高) -->
<link href="<?= mnbt_theme_asset('theme.css') ?>" rel="stylesheet">
```

### 9.2 避免类名冲突

Layui 的 `layui-container`、`layui-row`、`layui-card` 与 Bootstrap 的 `container`、`row`、`card` 互不覆盖,可共存。

但要注意:

- Layui 的 `layui-btn` 与 Bootstrap 的 `btn` 样式不同,**不要在回退页混用**
- 自定义类建议加前缀(如 `my-`、`ly-`)避免被覆盖

### 9.3 仅覆盖少量页面的策略

最经济的做法:

| 覆盖文件 | 改造内容 |
|------|------|
| `head.php` | 加载两套栈 + 主题覆盖样式 |
| `login.php` | 完全用新 UI 库重写 |
| `index.php` | 完全用新 UI 库重写(框架壳) |
| `sy.php` | 完全用新 UI 库重写(仪表盘) |

其余业务页(`set.php`、`ftp.php` 等)回退 default,因 head.php 仍加载 Bootstrap,能正常工作。

参考实现:`templates/layui/`(用户端 + 管理端框架壳、登录页、仪表盘用 Layui,业务页回退 default)。

---

## 10. 官方主题示例

| 主题 | 目录 | 技术栈 | 覆盖范围 |
|------|------|--------|----------|
| `default` | `templates/default/` | Light Year Admin(Bootstrap 4 + jQuery) | 全部页面 |
| `layui` | `templates/layui/` | Layui 2.9 + Bootstrap 回退栈 | head/login/index/sy(其余回退 default) |

`layui` 主题是**混合栈**示例:保留 Bootstrap 以兼容回退页,新增 Layui 用于框架壳与登录页,覆盖样式将主色调统一为 Layui 蓝(`#1e9fff`)。

---

## 11. 发布主题包建议

压缩包结构:

```text
my_theme.zip
└── my_theme/
    ├── theme.json
    ├── user/
    └── admin/
```

安装:解压到站点 `templates/` 下,后台选择启用。

请勿包含:

- `config.php`、数据库账号
- 木马/webshell
- 覆盖 `user/api`、`admin/api` 的业务后门

---

## 14. 版本与兼容

- 主题系统自 MNBT 主题化改造版本起提供(见主仓库 `dev/v1.80` 及后续正式版)
- 升级程序时:自定义主题目录一般可保留;若官方 `default` 新增页面,旧主题未覆盖则自动用新 default
- 若官方修改某页 DOM 结构,依赖旧 DOM 的自定义主题可能需跟进调整
- 资源 API:`mnbt_theme_url` 会对主题私有文件做 default 回退;`mnbt_asset_url` 始终指向 `imsetes/`
- `layui` 主题自 v1.81 起提供,作为混合栈示例
- `docker` scope 自 v1.83 起支持,提供独立的 Docker 控制台视图体系
- `home` scope 自 v1.84 起支持,提供独立的站点主页视图体系(内置默认主页 + 主题化切换 + 自定义设置字段声明)

如有疑问,可在项目 Issue 中反馈并附上主题目录结构与报错截图。
