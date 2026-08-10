---
title: 主题开发手册
description: 新建主题最短路径、控制器与视图约定、静态资源隔离、公共交互函数与 DOM 契约、框架页实现要点与常见问题
---

# MNBT 主题开发手册

本文面向需要**新建主题**或**改版默认皮肤**的开发者。
阅读前请先看 [主题系统总览](./index.md) 了解切换方式与目录结构。

## 1. 设计原则

1. **路由不变**:访问地址仍是 `/user/login.php`、`/admin/set.php?gn=wz`,不要改控制器 URL。
2. **逻辑与视图分离**:鉴权、查库、调宝塔 API 放在 `user/`、`admin/` 控制器;HTML 放主题。
3. **AJAX 路径不变**:页面内请求仍使用 `./ajax.php`、`../user/ajax.php` 等现有接口。
4. **缺页回退**:自定义主题只需覆盖要改的页面,其余自动使用 `default`。
5. **双端独立**:用户端主题与管理端主题可分别选择。
6. **DOM 契约保留**:表单 `id`、关键按钮 `onclick` 函数名、`msalert` / `msloading` 等公共函数不能改名(详见 [§6](#6-公共交互函数与-dom-契约))。

## 2. 新建主题(最短路径)

### 步骤 1:复制骨架

```text
templates/
└── my_theme/                 # 目录名 = 主题 ID(仅字母数字下划线横线)
    ├── theme.json
    ├── user/
    │   └── assets/
    └── admin/
        └── assets/
```

> 当前官方主题包含 `default`(Bootstrap 4 + jQuery)与 `layui`(Layui 2.x)。新建主题建议以 `default` 为基准复制后修改,或参考 `layui` 主题的"混合栈"做法(见 [主题引擎进阶 §9](./engine.md#9-混合-ui-框架主题策略))。

### 步骤 2:编写 `theme.json`

```json
{
  "name": "my_theme",
  "title": "我的主题",
  "version": "1.0.0",
  "description": "自定义用户端与管理端皮肤",
  "author": "YourName",
  "scope": ["user", "admin"]
}
```

| 字段 | 必填 | 说明 |
|------|------|------|
| `name` | 建议 | 与目录名一致 |
| `title` | 是 | 后台「前端模板」列表显示名 |
| `version` | 否 | 版本号 |
| `description` | 否 | 简介 |
| `author` | 否 | 作者 |
| `scope` | 否 | 文档用;实际以是否存在 `user/`、`admin/` 目录为准 |

### 步骤 3:覆盖页面

从 `templates/default/user/` 或 `admin/` **复制**要改的文件到 `my_theme` 对应目录,再修改 HTML/CSS。示例:只改用户登录页外观(其余用户页仍走 `default`):

```text
templates/my_theme/user/login.php
```

### 步骤 4:启用

1. 确保 `templates/` 可写
2. 后台 → 系统管理 → **前端模板** → 选择 `my_theme` → 保存
3. 或写入 `templates/active_user_theme` / `templates/active_admin_theme`

### 步骤 5:自测清单

- [ ] 登录 / 退出
- [ ] 框架页侧栏与多标签(index)
- [ ] 至少 2～3 个业务子页(仪表盘、设置、列表)
- [ ] 表单提交与 AJAX 弹窗
- [ ] 静态资源 404 检查(CSS/JS/图片)
- [ ] **回退页**(未覆盖的页面)样式是否正常

## 3. 必选 / 可选视图清单

完整视图清单已拆分到独立小节 [视图清单(views.md)](./views.md),包含:

- 3.1 用户端 `templates/{theme}/user/`
- 3.2 管理端 `templates/{theme}/admin/`
- 3.3 Docker 控制台 `templates/{theme}/docker/`(V1.83 新增)
- 3.5 主页 `templates/{theme}/home/`(V1.84 新增)
- 3.6 不走主题的路径(一般不要动)

核心原则:不提供的文件会回退 `default`,不必一次抄全。

## 4. 控制器与视图约定

### 4.1 用户端控制器示例

```php
<?php
// user/sy.php
include("../MPHX/common.php");
$title = 'MN宝塔主机首页目录';
mnbt_user_require_login();
// 此处可准备 $data 等变量(会进入 $GLOBALS,视图可直接使用)
mnbt_render('sy');
```

### 4.2 管理端控制器示例

```php
<?php
// admin/set.php
include("../MPHX/common.php");
$title = 'MN宝塔主机系统设置';
mnbt_admin_require_login();
mnbt_admin_render('set');
```

### 4.3 视图内引入公共头

```php
<?php mnbt_theme_include('head'); ?>
<!-- 或管理端 -->
<?php mnbt_admin_include('head'); ?>
```

`index.php` 一般是完整 HTML 文档,**可不** include head。

### 4.4 视图中可用的常见变量

由 `common.php` / `member.php` 注入,视图可直接使用:

| 变量 | 端 | 说明 |
|------|----|------|
| `$conf` | 双端 | 系统配置行(`MN_config`) |
| `$DB` | 双端 | 数据库对象 |
| `$date` | 双端 | 当前时间字符串 |
| `$title` | 双端 | 页面标题(控制器设置) |
| `$islogins` / `$yhc` | 用户端 | 登录态 / 主机信息 |
| `$user` / `$zjid` / `$ssbt` | 用户端 | 账号、站点 ID、所属宝塔代号 |
| `$islogin` | 管理端 | 管理员登录态 |
| `$siteid` | 双端 | 配置站点 ID(通常 1) |
| `$mn_conf` | 双端 | 内部运行配置(含 `xf` 修复标记) |

部分页面控制器还会准备专用变量,例如 `monitor.php` 的 `$tasks`、`$task_count`,`monitor_log.php` / `notice.php` 的 `$logs`、`$page`、`$total`,`sqlgl.php` 的 `$bf_data`、`$hxd`。

## 5. 静态资源隔离

### 5.1 两类资源(必须分清)

| 类型 | 目录 | API | 是否随主题切换 |
|------|------|-----|----------------|
| **公共资源** | `imsetes/` | `mnbt_asset_url()` | 否 |
| **主题私有** | `templates/{theme}/{scope}/assets/` | `mnbt_theme_asset()` / `mnbt_theme_url()` | 是(缺文件回退 default) |

**公共资源**(不要复制进主题):Bootstrap、jQuery、CodeMirror、图表库、上传 logo(`upload_logo/` / `admin_logo/`)、业务脚本(`fn-hs.js`、`xtset.js` 等)。

**主题私有**(改皮肤放这里):覆盖样式、登录页背景、主题专属 JS/图片。

### 5.2 公共资源写法

```php
<link href="<?= mnbt_asset_url('css/bootstrap.min.css') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery.min.js') ?>"></script>
<img src="<?= mnbt_asset_url('upload_logo/logo.login.png') ?>?<?= $conf['auther'] ?>">
```

等价于 `../imsetes/...`。**模板中禁止再写死 `../imsetes/`**,便于以后改公共资源根路径。

### 5.3 主题私有资源

```text
templates/my_theme/user/assets/login.css
templates/my_theme/admin/assets/set-page.css
templates/my_theme/admin/assets/admin-common.css
```

推荐写法(自动加 `assets/` 前缀):

```php
<link href="<?= mnbt_theme_asset('login.css') ?>" rel="stylesheet">
<link href="<?= mnbt_theme_asset('set-page.css', 'admin') ?>" rel="stylesheet">
```

等价于:

```php
<link href="<?= mnbt_theme_url('assets/login.css') ?>" rel="stylesheet">
```

### 5.4 资源回退规则

与页面模板相同:当前主题 `templates/{theme}/{scope}/assets/xxx.css` → 不存在则 `templates/default/{scope}/assets/xxx.css` → 仍不存在则返回当前主题 URL(便于你补文件时定位 404)。

因此自定义主题**只需覆盖要改的 CSS**,其余私有资源会用 default 的。

### 5.5 缓存

```php
<script src="<?= mnbt_asset_url('js/fn-hs.js') ?>?1.80"></script>
```

Logo 等已使用 `$conf['auther']` 作为缓存戳。

### 5.6 引入第三方 UI 库(如 Layui)

第三方库**不属于公共资源**,可由主题自行引入:

- 优先:CDN(如 `https://unpkg.com/layui@2.9.8/dist/css/layui.css`)
- 离线:放入 `templates/{theme}/user/assets/lib/` 后用 `mnbt_theme_asset('lib/layui.css')` 引用

> 注意:如果主题仅覆盖部分页面(其余回退 default),第三方库需在 `head.php` 中加载,使回退页也能取到;但同时**不要移除** Bootstrap / jQuery / `fn-hs.js`,否则回退页会样式错乱或脚本失效(详见 [主题引擎进阶 §9 混合 UI 框架主题策略](./engine.md#9-混合-ui-框架主题策略))。

## 6. 公共交互函数与 DOM 契约

现有大量页面 JS 依赖固定元素 `id`、class 与全局函数。改外观时**必须保留**以下契约。

### 6.1 全局函数(来自 `imsetes/js/fn-hs.js`)

| 函数 | 用途 |
|------|------|
| `msalert(type, msg, timeout)` | 消息提示(1 成功 / 2 公告 / 3 警告 / 4 错误) |
| `msalertb(type, title, content, ...)` | 带标题的弹窗 |
| `msloading(text, ...)` | 显示加载遮罩 |
| `msloadingde()` | 关闭加载遮罩 |
| `ylalert(text)` | 用量超限提示 |

### 6.2 登录页契约(用户端 + 管理端)

| 元素 / 函数 | 说明 |
|------|------|
| `#username`、`#password` | 输入框 id,登录 JS 直接读取 |
| `#csyzmiq` | 验证码输入框(开启验证码时) |
| `#captcha` | 验证码图片(刷新用) |
| `chkre()` | 登录提交函数(`onclick="chkre()"`) |

### 6.3 框架页契约

| 元素 / 函数 | 说明 |
|------|------|
| `#iframe-content` | iframe 容器(多标签插件挂载点) |
| `#iframe_shuax` | 刷新当前标签按钮 |
| `chteci()` | 退出登录函数 |
| `.multitabs` | 多标签点击触发类 |
| `xiaole()` | 邮箱绑定弹窗(用户端,按需) |

### 6.4 改外观的边界

- ✅ 可以改:class、布局、颜色、字体、间距、图标库
- ❌ 不要改:表单控件 `id`、关键按钮 `onclick` 函数名、`<script>` 内联逻辑的函数名;若必须改结构,需同步修改页面内 JS 或独立 `assets/*.js`

## 7. 框架页(index.php)实现要点

`index.php` 是整套皮肤中**最复杂**的页面,承担:左侧导航菜单、顶部工具栏(侧栏开关、刷新、用户菜单、配色切换)、多标签 iframe 容器(`#iframe-content`)、退出登录与系统修复等弹窗逻辑。

### 7.1 默认主题的实现

默认主题使用 `bootstrap-multitabs` 插件驱动多标签:

```php
<script src="<?= mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.js') ?>"></script>
<script src="<?= mnbt_asset_url('js/index.min.js') ?>"></script>
```

菜单项添加 `class="multitabs"` 即可被插件劫持为 iframe 标签页。

### 7.2 自定义框架页注意

- 必须保留 `#iframe-content` 容器,否则多标签插件无处挂载
- 保留 `.multitabs` 类与 `data-url` / `data-title` 属性
- 保留 `#iframe_shuax` 刷新按钮与 `chteci()` 退出函数
- 保留 iframe loading 占位 HTML(含 `css/index.loading.css`)的结构

### 7.3 插件菜单挂载点

```php
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
```

管理端用 `mnbt_plugin_render_menu_admin_html()`。**必须保留**,否则插件菜单不会显示。

### 7.4 插件菜单多主题适配(菜单渲染器)

从 V1.82 起,引擎支持**主题注册自己的菜单渲染器**,解决"插件菜单在不同主题下结构不兼容"问题。

**原理**:插件通过 `mnbt_register_menu('user', ...)` 注册的是**菜单数据树**(title/icon/url/order/children),不带 HTML 结构;主题通过 `mnbt_register_theme_menu_renderer('user', $callback)` 注册**渲染器**,把这棵树转换成当前主题需要的 HTML。主题 `index.php` 中调用 `mnbt_plugin_render_menu_user_html()` 时,引擎自动使用当前主题注册的渲染器;若主题未注册渲染器,引擎回退到 default 主题(lyear)结构。

**主题开发者需要做的两件事**:

1. 在主题根目录创建 `theme.php`,注册渲染器:

```php
<?php
// templates/my_theme/theme.php
if (!defined('IN_CRONLITE')) exit;

mnbt_register_theme_menu_renderer('user', function ($items) {
    $html = '';
    foreach ($items as $it) {
        $title = htmlspecialchars($it['title'] ?? '');
        $icon  = htmlspecialchars($it['icon'] ?? 'mdi-puzzle');
        if (!empty($it['children'])) {
            // 分组
            $html .= '<li class="my-submenu">'
                   . '<a href="javascript:;"><i class="mdi ' . $icon . '"></i> ' . $title . '</a>'
                   . '<ul class="my-subnav">';
            foreach ($it['children'] as $child) {
                $childTitle = htmlspecialchars($child['title'] ?? '');
                $childUrl   = htmlspecialchars($child['url'] ?? 'javascript:void(0)');
                $mt = !empty($child['multitabs']) || strpos($childUrl, 'plugin.php') !== false ? ' multitabs' : '';
                $html .= '<li><a href="' . $childUrl . '" class="' . trim($mt) . '">' . $childTitle . '</a></li>';
            }
            $html .= '</ul></li>';
        } else {
            // 叶子项
            $url = htmlspecialchars($it['url'] ?? 'javascript:void(0)');
            $mt = !empty($it['multitabs']) || strpos($url, 'plugin.php') !== false ? ' multitabs' : '';
            $html .= '<li><a href="' . $url . '" class="' . trim($mt) . '"><i class="mdi ' . $icon . '"></i> ' . $title . '</a></li>';
        }
    }
    return $html;
});
```

2. 在 `index.php` 的侧边栏合适位置调用:

```php
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
```

**注意事项**:`theme.php` 会在引擎首次解析该主题视图时**自动加载**(通过 `mnbt_theme_ensure_loaded`),无需手动 include;叶子项建议统一归入一个分组(如「插件管理」),或按主题风格平铺。必须给链接加 `multitabs` 类,才能让多标签插件正确接管;管理端菜单同理,注册 `'admin'` scope 的渲染器。

## 12. 常见问题

### Q: 改了主题文件不生效?

1. 确认当前激活主题名(`active_*` 或后台显示),并确认文件路径是否为 `templates/{主题}/{user|admin}/xxx.php`
2. 清理浏览器 / CDN / OPcache;检查是否改错了 `user/xxx.php` 控制器(控制器里不应再写大段 HTML)

### Q: 只想换颜色,不想复制整页?

优先改 `user/assets/*.css` / `admin/assets/*.css`,或 `head.php` 里增加覆盖样式,不必复制所有业务页。

### Q: 管理端设置页样式在哪?

默认主题布局:`templates/default/admin/set.php`,样式:`templates/default/admin/assets/set-page.css`。

### Q: 主题里能否直接查数据库?

技术上可以(`$DB` 可用),但**不推荐**——查询应放控制器,视图只负责展示,便于换皮与维护。

### Q: 如何调试当前加载的是哪个文件?

可在视图临时输出:

```php
<?php /* echo mnbt_theme_resolve('login', 'user'); */ ?>
```

或查看 `mnbt_theme_name('user')` / `mnbt_theme_name('admin')`。

### Q: 回退页样式错乱?

通常是 `head.php` 漏加载了 Bootstrap / jQuery / `fn-hs.js` / `style.min.css`。检查 head.php 是否完整保留了 default 主题的公共资源引用,再追加新 UI 库。

### Q: 主题加载了 Layui 但 `layui.form.render()` 报错?

业务回退页里没有 `.layui-form` 容器,调用 `render` 无意义。仅在自定义页面(如登录页、框架页)里使用 `layui.form.render()`,不要放到 `head.php` 全局执行。
