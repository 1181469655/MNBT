---
title: 主页主题开发
description: "主页主题(scope: home)开发说明：新建主题、自定义设置字段注册、模板读取、持久化与扩展区块"
---

# 主页主题开发(V1.84)

独立主页系统是 V1.84 引入的核心功能,使主页(站点根路径 `/`)脱离插件依赖,成为可独立切换的第四主题 scope(`home`)。主页视图清单速览见 [视图清单 §3.5](./views.md#35-主页-templatesthemehomev184-新增),本文是完整开发说明。

## 15.1 概述

**核心机制**:
- 主页模板:`templates/{主题}/home/index.php`(缺页回退 `default`)
- 主题切换:后台「前端模板」→ 主页主题下拉(与用户端/管理端独立)
- 内容配置:后台「前端模板」→ 主页内容(标题、Hero、主色、Logo 等通用设置)
- 自定义设置:主题通过 `theme.php` 声明字段,后台自动渲染并持久化

## 15.2 新建主页主题

**目录结构**:

```text
templates/
└── my_home/
    ├── theme.json          # 主题元信息(可选)
    ├── theme.php           # 注册自定义设置字段声明
    └── home/
        ├── index.php       # 主页落地页模板(必选)
        └── assets/         # 静态资源(可选)
            └── style.css
```

**最小配置**:只需 `home/index.php` 即可被识别为主页主题,在后台「前端模板 → 主页主题」下拉中显示。

## 15.3 注册自定义设置(theme.php)

主题通过 `mnbt_register_home_setting()` 声明设置字段,**不需要写 HTML**——渲染由当前 Admin 主题负责。

```php
<?php
// templates/my_home/theme.php
if (!defined('IN_CRONLITE')) exit;

mnbt_register_home_setting([
    'key'         => 'bg_color',
    'label'       => '背景颜色',
    'type'        => 'color',
    'default'     => '#f0f4ff',
    'placeholder' => '#f0f4ff',
    'hint'        => '主页 body 背景色',
]);

mnbt_register_home_setting([
    'key'     => 'hero_style',
    'label'   => '标题风格',
    'type'    => 'select',
    'default' => 'large',
    'options' => [
        ['value' => 'small',  'label' => '小号'],
        ['value' => 'medium', 'label' => '中号'],
        ['value' => 'large',  'label' => '大号'],
    ],
    'hint'    => 'Hero 标题字号',
]);

mnbt_register_home_setting([
    'key'     => 'show_badge',
    'label'   => '显示徽章',
    'type'    => 'switch',
    'default' => true,
]);

mnbt_register_home_setting([
    'key'         => 'footer_msg',
    'label'       => '页脚消息',
    'type'        => 'textarea',
    'placeholder' => '可选自定义内容',
]);
```

**支持的字段类型**:

| type | 渲染组件 | default 后台 | tdesign 后台 |
|------|----------|-------------|-------------|
| `text` | 文本输入框 | `<input class="form-control">` | `<t-input>` |
| `color` | 色盘 + 文本输入 | `<input type="color">` + `<input>` | `<input type="color">` + `<t-input>` |
| `select` | 下拉选择 | `<select class="form-control">` | `<t-select>` |
| `switch` | 开关 | Bootstrap `.custom-switch` | `<t-switch>` |
| `textarea` | 多行文本 | `<textarea class="form-control">` | `<t-textarea>` |
| `number` | 数字输入 | `<input type="number">` | `<t-input type="number">` |

**字段参数**:

| 参数 | 必填 | 说明 |
|------|------|------|
| `key` | 是 | 唯一标识符,`/^[a-zA-Z_][a-zA-Z0-9_]+$/`,用于模板读取和持久化 |
| `label` | 是 | 显示标签 |
| `type` | 否 | 组件类型,默认 `text` |
| `default` | 否 | 默认值(switch 默认 `false`) |
| `placeholder` | 否 | 占位文本 |
| `hint` | 否 | 字段下方的提示说明 |
| `options` | select 专用 | `[['value'=>'', 'label'=>''], ...]` |

## 15.4 模板中读取设置值

使用 `mnbt_home_theme_setting($key, $default)` 读取已保存的主题自定义设置:

```php
<?php
// templates/my_home/home/index.php
$bgColor   = function_exists('mnbt_home_theme_setting') ? mnbt_home_theme_setting('bg_color', '#fff') : '#fff';
$heroStyle = function_exists('mnbt_home_theme_setting') ? mnbt_home_theme_setting('hero_style', 'large') : 'large';
$showBadge = function_exists('mnbt_home_theme_setting') ? mnbt_home_theme_setting('show_badge', true) : true;
$footerMsg = function_exists('mnbt_home_theme_setting') ? mnbt_home_theme_setting('footer_msg', '') : '';
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($site_title) ?></title>
  <style>
    body { background: <?= htmlspecialchars($bgColor) ?>; }
    h1 { font-size: <?= $heroStyle === 'small' ? '1.4rem' : ($heroStyle === 'medium' ? '2rem' : '2.8rem') ?>; }
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($site_hero) ?></h1>
  <?php if ($showBadge): ?><div class="badge">推荐</div><?php endif; ?>
  <?php if ($footerMsg): ?><footer><?= htmlspecialchars($footerMsg) ?></footer><?php endif; ?>
</body>
</html>
```

## 15.5 持久化

所有自定义字段统一保存在 `MN_config.home_theme_settings`(JSON 列),切换主题后设置保留。主题重新切回来时值仍在。

## 15.6 扩展区块

主页模板可通过 `$blocks` 变量渲染插件注入的扩展区块:

```php
<?php foreach ($blocks as $block): ?>
<section class="sec">
  <?php if (!empty($block['title'])): ?>
    <h2><?= htmlspecialchars($block['title']) ?></h2>
  <?php endif; ?>
  <?= $block['html'] ?>
</section>
<?php endforeach; ?>
```

插件通过 `mnbt_add_filter('home.blocks', callback)` 注入区块。

## 15.7 启用

1. 将主题文件夹放入 `templates/`
2. 后台 → 系统设置 → 前端模板 → 主页主题下拉 → 选择 `my_home` → 保存
3. 主页内容区域的通用设置和主题自定义字段均可在此面板中配置
4. 访问站点 `/` 即可看到效果

## 15.8 相关文件索引

| 文件 | 职责 |
|------|------|
| `MPHX/frontend.php` | 主页引擎(分发、数据组装、字段注册 API、default 渲染器) |
| `MPHX/theme.php` | `home` scope 注册(`mnbt_theme_name/list/set_active`) |
| `index.php` | 请求分发入口(`mnbt_home_dispatch` 调用点) |
| `templates/default/home/index.php` | 内置默认主页模板 |
| `templates/default/admin/set.php` | default 后台渲染器(`gn=theme`) |
| `templates/tdesign/spa/src/admin/views/settings/ThemeView.vue` | tdesign 后台渲染器 |
| `templates/tdesign/admin/_spa_boot.php` | tdesign boot 数据注入 |
| `admin/api/setting.php` | `save_home_settings` / `home_upload_icon` / `settheme(hometheme)` 接口 |
| `install/install.sql` | `MN_config.home_*` 字段定义 |
| `update/update_v184_home.sql` | 增量升级 SQL |
