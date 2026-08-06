# MNBT 主题开发手册

本文面向需要**新建主题**或**改版默认皮肤**的开发者。
阅读前请先看 [README.md](./README.md) 了解切换方式与目录结构。

## 目录

- [1. 设计原则](#1-设计原则)
- [2. 新建主题（最短路径）](#2-新建主题最短路径)
- [3. 必选 / 可选视图清单](#3-必选--可选视图清单)
- [4. 控制器与视图约定](#4-控制器与视图约定)
- [5. 静态资源隔离](#5-静态资源隔离)
- [6. 公共交互函数与 DOM 契约](#6-公共交互函数与-dom-契约)
- [7. 框架页（index.php）实现要点](#7-框架页indexphp实现要点)
- [8. 主题引擎行为细节](#8-主题引擎行为细节)
- [9. 混合 UI 框架主题策略](#9-混合-ui-框架主题策略)
- [10. 官方主题示例](#10-官方主题示例)
- [11. 发布主题包建议](#11-发布主题包建议)
- [12. 常见问题](#12-常见问题)
- [13. 相关文件索引](#13-相关文件索引)
- [14. 版本与兼容](#14-版本与兼容)

---

## 1. 设计原则

1. **路由不变**：访问地址仍是 `/user/login.php`、`/admin/set.php?gn=wz`，不要改控制器 URL。
2. **逻辑与视图分离**：鉴权、查库、调宝塔 API 放在 `user/`、`admin/` 控制器；HTML 放主题。
3. **AJAX 路径不变**：页面内请求仍使用 `./ajax.php`、`../user/ajax.php` 等现有接口。
4. **缺页回退**：自定义主题只需覆盖要改的页面，其余自动使用 `default`。
5. **双端独立**：用户端主题与管理端主题可分别选择。
6. **DOM 契约保留**：表单 `id`、关键按钮 `onclick` 函数名、`msalert` / `msloading` 等公共函数不能改名（详见 [§6](#6-公共交互函数与-dom-契约)）。

---

## 2. 新建主题（最短路径）

### 步骤 1：复制骨架

```text
templates/
└── my_theme/                 # 目录名 = 主题 ID（仅字母数字下划线横线）
    ├── theme.json
    ├── user/
    │   └── assets/
    └── admin/
        └── assets/
```

> 当前官方主题包含 `default`（Bootstrap 4 + jQuery）与 `layui`（Layui 2.x）。新建主题建议以 `default` 为基准复制后修改，或参考 `layui` 主题的"混合栈"做法。

### 步骤 2：编写 `theme.json`

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
| `scope` | 否 | 文档用；实际以是否存在 `user/`、`admin/` 目录为准 |

### 步骤 3：覆盖页面

从 `templates/default/user/` 或 `admin/` **复制**要改的文件到 `my_theme` 对应目录，再修改 HTML/CSS。

示例：只改用户登录页外观：

```text
templates/my_theme/user/login.php
```

其余用户页仍走 `default`。

### 步骤 4：启用

1. 确保 `templates/` 可写
2. 后台 → 系统管理 → **前端模板** → 选择 `my_theme` → 保存
3. 或写入 `templates/active_user_theme` / `templates/active_admin_theme`

### 步骤 5：自测清单

- [ ] 登录 / 退出
- [ ] 框架页侧栏与多标签（index）
- [ ] 至少 2～3 个业务子页（仪表盘、设置、列表）
- [ ] 表单提交与 AJAX 弹窗
- [ ] 静态资源 404 检查（CSS/JS/图片）
- [ ] **回退页**（未覆盖的页面）样式是否正常

---

## 3. 必选 / 可选视图清单

### 3.1 用户端 `templates/{theme}/user/`

| 视图文件 | 说明 | 建议 |
|----------|------|------|
| `head.php` | 公共 `<head>` + 公共 CSS/JS | 改整体风格必改 |
| `login.php` | 登录页 | 强烈建议覆盖 |
| `index.php` | 框架壳（侧栏 + 多标签 iframe） | 强烈建议覆盖 |
| `sy.php` | 仪表盘 | 建议 |
| `set.php` | 站点设置（PHP/SSL/Gzip 等） | 建议 |
| `site_stats.php` | 站点统计 | 可选 |
| `monitor.php` | 监控任务 | 可选 |
| `monitor_log.php` | 监控日志 | 可选 |
| `notice.php` | 通知日志 | 可选 |
| `webgl.php` | 一键部署 | 可选 |
| `sqlgl.php` | 数据库备份 | 可选 |
| `ftp.php` | 在线文件管理 | 复杂，可不覆盖 |

> 不提供的文件会回退 `default`，**不必一次抄全**。

### 3.2 管理端 `templates/{theme}/admin/`

| 视图文件 | 说明 | 建议 |
|----------|------|------|
| `head.php` | 公共头 | 改整体风格必改 |
| `login.php` | 后台登录 | 强烈建议 |
| `index.php` | 后台框架壳 | 强烈建议 |
| `sy.php` | 仪表盘 | 建议 |
| `set.php` | 系统设置（含前端模板页） | 建议 |
| `list.php` | 列表（宝塔/主机/域名/日志等） | 可选（体积大） |
| `add.php` | 添加页 | 可选 |
| `node.php` | 节点管理 | 可选 |
| `tutorial.php` | 教程与监控说明 | 可选 |
| `update.php` | 系统更新 | 可选 |

### 3.3 Docker 控制台 `templates/{theme}/docker/` <small>（V1.83 新增）</small>

Docker 控制台是独立于用户端/管理端的第三套视图体系，有独立的认证机制（`docker_token` cookie）。

| 视图文件 | 说明 | 建议 |
|----------|------|------|
| `head.php` | 公共引导文件（主题配置、节点信息、用户信息、导航菜单） | 改整体风格必改 |
| `foot.php` | 公共尾部（Toast/Modal 容器、HTML 文档闭合） | 改整体风格必改 |
| `login.php` | Docker 登录页 | 强烈建议覆盖 |
| `console.php` | 我的容器（单容器详情页） | 建议 |
| `appstore.php` | 应用商店（宝塔应用市场） | 建议 |
| `image.php` | 镜像管理 | 可选 |
| `volume.php` | 数据卷管理 | 可选 |
| `compose.php` | Compose 模板 | 可选 |

**静态资源**：`templates/{theme}/docker/assets/`（CSS、JS、图片、SVG），使用 `mnbt_theme_asset('docker.css', 'docker')` 引用。

**Docker 视图依赖的公共 JS 函数**（在 `head.php` 中加载）：

| 函数 | 用途 |
|------|------|
| `dkAjax(gn, data, opts)` | Docker AJAX 请求（自动附带 CSRF token） |
| `dkToast(msg, type)` | 消息提示（`info` / `success` / `error`） |
| `dkModal(html, title)` | 模态框（html 为内容，title 为标题） |
| `dkCloseModal()` | 关闭模态框 |
| `dockerLogout()` | 退出登录 |
| `dkContainerOp(gn)` | 容器操作（`container_start` / `container_stop` / `container_restart`） |
| `dkContainerRemove()` | 删除容器（带确认弹窗，调用 `remove_app` 卸载） |

**Docker 视图可用的 PHP 变量**（由控制器传入）：

| 变量 | 说明 |
|------|------|
| `$me` | 当前 Docker 用户（`MN_docker_user` 行） |
| `$plan` | 用户套餐（`MN_docker_plan` 行，含 `cpu_max`/`mem_max`/`disk_max`） |
| `$node` | 所属节点信息（`MN_docker_node` 行） |
| `$title` | 页面标题 |
| `$active` | 当前激活的导航项（`console`/`appstore`/`image`/`volume`/`compose`） |

**Docker 关键 CSS 类**：

| 类名 | 用途 |
|------|------|
| `.dk-card` / `.dk-card-head` / `.dk-card-body` | 卡片容器 |
| `.dk-btn` / `.dk-btn-sm` / `.dk-btn-primary` / `.dk-btn-danger` / `.dk-btn-warning` / `.dk-btn-success` / `.dk-btn-ghost` / `.dk-btn-block` | 按钮 |
| `.dk-tag` / `.dk-tag-running` / `.dk-tag-stopped` / `.dk-tag-creating` / `.dk-tag-none` / `.dk-tag-expired` / `.dk-tag-paused` | 状态标签 |
| `.dk-quota-bar` / `.dk-quota-item` / `.dk-quota-label` / `.dk-quota-val` | 安装弹窗中的配额展示条（蓝色底，三列居中） |
| `.dk-alert` / `.dk-alert-info` / `.dk-alert-warn` / `.dk-alert-danger` | 提示条 |
| `.dk-form-grid` / `.dk-field` / `.dk-field-full` | 表单布局 |
| `.dk-metrics` / `.dk-metric` / `.dk-m-label` / `.dk-m-value` | 指标卡片（容器详情页） |
| `.dk-empty` / `.dk-empty-ico` | 空状态提示 |
| `.dk-spinner-overlay` / `.dk-spin` | 加载动画 |
| `.dk-app-card` / `.dk-app-head` / `.dk-app-icon` / `.dk-app-desc` | 应用卡片（应用商店列表） |
| `.dk-modal` / `.dk-modal-head` / `.dk-modal-body` / `.dk-modal-foot` / `.dk-modal-close` | 模态框 |
| `.dk-toast` | Toast 消息 |
| `.dk-sidebar` / `.dk-nav` / `.dk-nav-section` | 侧栏导航 |
| `.dk-topbar` / `.dk-badges` | 顶栏 |
| `.dk-mono` | 等宽字体 |

**Docker 控制器路径**：

| 控制器 | 路径 |
|--------|------|
| 页面入口 | `docker/console.php`、`docker/appstore.php` 等 |
| AJAX 后端 | `docker/ajax.php` |
| CSS 样式 | `templates/default/docker/assets/docker.css` |

**主题 scope 注册**：`docker` scope 在 `MPHX/theme.php` 中注册，与 `user`/`admin` 独立。`theme.json` 可声明 `"scope": ["user", "admin", "docker"]`。

### 3.4 不走主题的路径（一般不要动）

| 路径 | 原因 |
|------|------|
| `user/ajax.php`、`user/api/*` | JSON API |
| `admin/ajax.php`、`admin/api/*` | JSON API |
| `user/pay.php` 等 | 支付跳转（V1.81 P3 起回调由支付插件路由处理） |
| `user/mysql.php` | 跳转 phpMyAdmin |
| `user/amftp/*` | 独立文件管理器 |

---

## 4. 控制器与视图约定

### 4.1 用户端控制器示例

```php
<?php
// user/sy.php
include("../MPHX/common.php");
$title = 'MN宝塔主机首页目录';
mnbt_user_require_login();
// 此处可准备 $data 等变量（会进入 $GLOBALS，视图可直接使用）
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

`index.php` 一般是完整 HTML 文档，**可不** include head。

### 4.4 视图中可用的常见变量

由 `common.php` / `member.php` 注入，视图可直接使用：

| 变量 | 端 | 说明 |
|------|----|------|
| `$conf` | 双端 | 系统配置行（`MN_config`） |
| `$DB` | 双端 | 数据库对象 |
| `$date` | 双端 | 当前时间字符串 |
| `$title` | 双端 | 页面标题（控制器设置） |
| `$islogins` / `$yhc` | 用户端 | 登录态 / 主机信息 |
| `$user` / `$zjid` / `$ssbt` | 用户端 | 账号、站点 ID、所属宝塔代号 |
| `$islogin` | 管理端 | 管理员登录态 |
| `$siteid` | 双端 | 配置站点 ID（通常 1） |
| `$mn_conf` | 双端 | 内部运行配置（含 `xf` 修复标记） |

部分页面控制器还会准备专用变量，例如：

- `monitor.php`：`$tasks`、`$task_count`
- `monitor_log.php` / `notice.php`：`$logs`、`$page`、`$total` 等
- `sqlgl.php`：`$bf_data`、`$hxd`

---

## 5. 静态资源隔离

### 5.1 两类资源（必须分清）

| 类型 | 目录 | API | 是否随主题切换 |
|------|------|-----|----------------|
| **公共资源** | `imsetes/` | `mnbt_asset_url()` | 否 |
| **主题私有** | `templates/{theme}/{scope}/assets/` | `mnbt_theme_asset()` / `mnbt_theme_url()` | 是（缺文件回退 default） |

**公共资源**（不要复制进主题）：Bootstrap、jQuery、CodeMirror、图表库、上传 logo（`upload_logo/` / `admin_logo/`）、业务脚本（`fn-hs.js`、`xtset.js` 等）。

**主题私有**（改皮肤放这里）：覆盖样式、登录页背景、主题专属 JS/图片。

### 5.2 公共资源写法

```php
<link href="<?= mnbt_asset_url('css/bootstrap.min.css') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery.min.js') ?>"></script>
<img src="<?= mnbt_asset_url('upload_logo/logo.login.png') ?>?<?= $conf['auther'] ?>">
```

等价于 `../imsetes/...`。**模板中禁止再写死 `../imsetes/`**，便于以后改公共资源根路径。

### 5.3 主题私有资源

```text
templates/my_theme/user/assets/login.css
templates/my_theme/admin/assets/set-page.css
templates/my_theme/admin/assets/admin-common.css
```

推荐写法（自动加 `assets/` 前缀）：

```php
<link href="<?= mnbt_theme_asset('login.css') ?>" rel="stylesheet">
<link href="<?= mnbt_theme_asset('set-page.css', 'admin') ?>" rel="stylesheet">
```

等价于：

```php
<link href="<?= mnbt_theme_url('assets/login.css') ?>" rel="stylesheet">
```

### 5.4 资源回退规则

与页面模板相同：

1. 当前主题：`templates/{theme}/{scope}/assets/xxx.css`
2. 不存在 → `templates/default/{scope}/assets/xxx.css`
3. 仍不存在 → 仍返回当前主题 URL（便于你补文件时定位 404）

因此自定义主题**只需覆盖要改的 CSS**，其余私有资源会用 default 的。

### 5.5 缓存

```php
<script src="<?= mnbt_asset_url('js/fn-hs.js') ?>?1.80"></script>
```

Logo 等已使用 `$conf['auther']` 作为缓存戳。

### 5.6 引入第三方 UI 库（如 Layui）

第三方库**不属于公共资源**，可由主题自行引入：

- 优先：CDN（如 `https://unpkg.com/layui@2.9.8/dist/css/layui.css`）
- 离线：放入 `templates/{theme}/user/assets/lib/` 后用 `mnbt_theme_asset('lib/layui.css')` 引用

> 注意：如果主题仅覆盖部分页面（其余回退 default），第三方库需在 `head.php` 中加载，使回退页也能取到；但同时**不要移除** Bootstrap / jQuery / `fn-hs.js`，否则回退页会样式错乱或脚本失效（详见 [§9](#9-混合-ui-框架主题策略)）。

---

## 6. 公共交互函数与 DOM 契约

现有大量页面 JS 依赖固定元素 `id`、class 与全局函数。改外观时**必须保留**以下契约。

### 6.1 全局函数（来自 `imsetes/js/fn-hs.js`）

| 函数 | 用途 |
|------|------|
| `msalert(type, msg, timeout)` | 消息提示（1 成功 / 2 公告 / 3 警告 / 4 错误） |
| `msalertb(type, title, content, ...)` | 带标题的弹窗 |
| `msloading(text, ...)` | 显示加载遮罩 |
| `msloadingde()` | 关闭加载遮罩 |
| `ylalert(text)` | 用量超限提示 |

### 6.2 登录页契约（用户端 + 管理端）

| 元素 / 函数 | 说明 |
|------|------|
| `#username`、`#password` | 输入框 id，登录 JS 直接读取 |
| `#csyzmiq` | 验证码输入框（开启验证码时） |
| `#captcha` | 验证码图片（刷新用） |
| `chkre()` | 登录提交函数（`onclick="chkre()"`） |

### 6.3 框架页契约

| 元素 / 函数 | 说明 |
|------|------|
| `#iframe-content` | iframe 容器（多标签插件挂载点） |
| `#iframe_shuax` | 刷新当前标签按钮 |
| `chteci()` | 退出登录函数 |
| `.multitabs` | 多标签点击触发类 |
| `xiaole()` | 邮箱绑定弹窗（用户端，按需） |

### 6.4 改外观的边界

- ✅ 可以改：class、布局、颜色、字体、间距、图标库
- ❌ 不要改：表单控件 `id`、关键按钮 `onclick` 函数名、`<script>` 内联逻辑的函数名
- 若必须改结构，需同步修改页面内 JS 或独立 `assets/*.js`

---

## 7. 框架页（index.php）实现要点

`index.php` 是整套皮肤中**最复杂**的页面，承担：

1. 左侧导航菜单
2. 顶部工具栏（侧栏开关、刷新、用户菜单、配色切换）
3. 多标签 iframe 容器（`#iframe-content`）
4. 退出登录、系统修复等弹窗逻辑

### 7.1 默认主题的实现

默认主题使用 `bootstrap-multitabs` 插件驱动多标签：

```php
<script src="<?= mnbt_asset_url('js/bootstrap-multitabs/multitabs.min.js') ?>"></script>
<script src="<?= mnbt_asset_url('js/index.min.js') ?>"></script>
```

菜单项添加 `class="multitabs"` 即可被插件劫持为 iframe 标签页。

### 7.2 自定义框架页注意

- 必须保留 `#iframe-content` 容器，否则多标签插件无处挂载
- 保留 `.multitabs` 类与 `data-url` / `data-title` 属性
- 保留 `#iframe_shuax` 刷新按钮与 `chteci()` 退出函数
- 保留 iframe loading 占位 HTML（含 `css/index.loading.css`）的结构

### 7.3 插件菜单挂载点

```php
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
```

管理端用 `mnbt_plugin_render_menu_admin_html()`。**必须保留**，否则插件菜单不会显示。

### 7.4 插件菜单多主题适配（菜单渲染器）

从 V1.82 起，引擎支持**主题注册自己的菜单渲染器**，解决"插件菜单在不同主题下结构不兼容"问题。

**原理**：
- 插件通过 `mnbt_register_menu('user', ...)` 注册的是**菜单数据树**（title/icon/url/order/children），不带 HTML 结构
- 主题通过 `mnbt_register_theme_menu_renderer('user', $callback)` 注册**渲染器**，把这棵树转换成当前主题需要的 HTML
- 主题 `index.php` 中调用 `mnbt_plugin_render_menu_user_html()` 时，引擎自动使用当前主题注册的渲染器
- 若主题未注册渲染器，引擎回退到 default 主题（lyear）结构

**主题开发者需要做的两件事**：

1. 在主题根目录创建 `theme.php`，注册渲染器：

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

2. 在 `index.php` 的侧边栏合适位置调用：

```php
<?php
if (function_exists('mnbt_plugin_render_menu_user_html')) {
  echo mnbt_plugin_render_menu_user_html();
}
?>
```

**注意事项**：
- `theme.php` 会在引擎首次解析该主题视图时**自动加载**（通过 `mnbt_theme_ensure_loaded`），无需手动 include
- 叶子项建议统一归入一个分组（如「插件管理」），或按主题风格平铺
- 必须给链接加 `multitabs` 类，才能让多标签插件正确接管
- 管理端菜单同理，注册 `'admin'` scope 的渲染器

---

## 8. 主题引擎行为细节

### 8.1 解析顺序（以用户端 `sy` 为例）

1. `templates/{当前主题}/user/sy.php`
2. 若不存在：`templates/default/user/sy.php`
3. 仍不存在：输出错误 `Theme view not found`

### 8.2 主题名校验

仅保留 `[a-zA-Z0-9_-]`，防止路径注入。

### 8.3 写入激活文件

`mnbt_theme_set_active('user', 'my_theme')` 会：

1. 检查目录 `templates/my_theme/user` 是否存在
2. 写入 `templates/active_user_theme`
3. 尝试 `UPDATE MN_config SET usertheme=?`（字段不存在则失败被忽略）

管理端同理（`admintheme` / `active_admin_theme`）。

### 8.4 页面接管机制（Page Override）

从 V1.82 起，主题引擎在 `mnbt_render()` 和 `mnbt_theme_include()` 中增加了**前置 override**机制，允许插件接管或包裹主题文件输出：

| 引擎函数 | override 名 | 触发时机 |
|----------|-------------|----------|
| `mnbt_render($view)` | `render.{scope}.{view}` | 加载主题页面前 |
| `mnbt_theme_include($view)` | `include.{scope}.{view}` | 加载 partial 前 |

**回调返回值的三种模式**：
- `null` → 不接管，主题文件照常加载（默认行为）
- `string` → 完全接管，直接输出该字符串，主题文件**不会被执行**
- `['before' => string, 'after' => string]` → 包裹模式，在主题文件输出前后插入内容

**多插件协作**：
- 按 priority 升序遍历，第一个返回非 null 的回调生效（短路语义）
- 后续回调不再调用

**典型场景**：
- 完全接管：插件替换某页面分支（如 `set.php?gn=url`）
- 包裹模式：在所有页面注入全局 banner / 公告 / 统计代码
- 优先级控制：低 priority 抢注接管，高 priority 包裹装饰

**主题开发者注意**：
- 此机制**不影响**主题文件的编写，只是多了一个"被插件接管/包裹"的可能
- 如果想让某页面**完全不被插件接管**，可在主题中直接 `include` 而非 `mnbt_render()`（不推荐，会破坏插件生态）
- 完全接管模式下，插件返回的 HTML 通常已自带 `mnbt_theme_include('head')`，主题无需担心样式缺失
- 包裹模式下，主题文件正常执行，插件只是在前后追加内容，不影响主题布局

详见 `app_plugins/PLUGIN_DEV.md` §3.4.1。

---

## 9. 混合 UI 框架主题策略

如果主题采用 Bootstrap 之外的 UI 库（如 Layui、Element、Tailwind），同时又想让未覆盖页面**回退到 default**，需要遵守以下策略：

### 9.1 head.php 必须同时加载两套栈

```php
<!-- 1. 回退页依赖的 Bootstrap / jQuery 栈（保留） -->
<link href="<?= mnbt_asset_url('css/bootstrap.min.css') ?>" rel="stylesheet">
<script src="<?= mnbt_asset_url('js/jquery.min.js') ?>"></script>
<script src="<?= mnbt_asset_url('js/fn-hs.js') ?>"></script>

<!-- 2. 主题专属 UI 库（新增） -->
<link href="https://unpkg.com/layui@2.9.8/dist/css/layui.css" rel="stylesheet">

<!-- 3. 主题覆盖样式（最后加载，优先级最高） -->
<link href="<?= mnbt_theme_asset('theme.css') ?>" rel="stylesheet">
```

### 9.2 避免类名冲突

Layui 的 `layui-container`、`layui-row`、`layui-card` 与 Bootstrap 的 `container`、`row`、`card` 互不覆盖，可共存。

但要注意：

- Layui 的 `layui-btn` 与 Bootstrap 的 `btn` 样式不同，**不要在回退页混用**
- 自定义类建议加前缀（如 `my-`、`ly-`）避免被覆盖

### 9.3 仅覆盖少量页面的策略

最经济的做法：

| 覆盖文件 | 改造内容 |
|------|------|
| `head.php` | 加载两套栈 + 主题覆盖样式 |
| `login.php` | 完全用新 UI 库重写 |
| `index.php` | 完全用新 UI 库重写（框架壳） |
| `sy.php` | 完全用新 UI 库重写（仪表盘） |

其余业务页（`set.php`、`ftp.php` 等）回退 default，因 head.php 仍加载 Bootstrap，能正常工作。

参考实现：`templates/layui/`（用户端 + 管理端框架壳、登录页、仪表盘用 Layui，业务页回退 default）。

---

## 10. 官方主题示例

| 主题 | 目录 | 技术栈 | 覆盖范围 |
|------|------|--------|----------|
| `default` | `templates/default/` | Light Year Admin（Bootstrap 4 + jQuery） | 全部页面 |
| `layui` | `templates/layui/` | Layui 2.9 + Bootstrap 回退栈 | head/login/index/sy（其余回退 default） |

`layui` 主题是**混合栈**示例：保留 Bootstrap 以兼容回退页，新增 Layui 用于框架壳与登录页，覆盖样式将主色调统一为 Layui 蓝（`#1e9fff`）。

---

## 11. 发布主题包建议

压缩包结构：

```text
my_theme.zip
└── my_theme/
    ├── theme.json
    ├── user/
    └── admin/
```

安装：解压到站点 `templates/` 下，后台选择启用。

请勿包含：

- `config.php`、数据库账号
- 木马/webshell
- 覆盖 `user/api`、`admin/api` 的业务后门

---

## 12. 常见问题

### Q: 改了主题文件不生效？

1. 确认当前激活主题名（`active_*` 或后台显示）
2. 确认文件路径是否为 `templates/{主题}/{user|admin}/xxx.php`
3. 清理浏览器 / CDN / OPcache
4. 是否改错了 `user/xxx.php` 控制器（控制器里不应再写大段 HTML）

### Q: 只想换颜色，不想复制整页？

优先改：

- `user/assets/*.css` / `admin/assets/*.css`
- 或 `head.php` 里增加覆盖样式

不必复制所有业务页。

### Q: 管理端设置页样式在哪？

默认主题：

- 布局：`templates/default/admin/set.php`
- 样式：`templates/default/admin/assets/set-page.css`

### Q: 主题里能否直接查数据库？

技术上可以（`$DB` 可用），但**不推荐**。
查询应放控制器，视图只负责展示，便于换皮与维护。

### Q: 如何调试当前加载的是哪个文件？

可在视图临时输出：

```php
<?php /* echo mnbt_theme_resolve('login', 'user'); */ ?>
```

或查看 `mnbt_theme_name('user')` / `mnbt_theme_name('admin')`。

### Q: 回退页样式错乱？

通常是 `head.php` 漏加载了 Bootstrap / jQuery / `fn-hs.js` / `style.min.css`。
检查 head.php 是否完整保留了 default 主题的公共资源引用，再追加新 UI 库。

### Q: 主题加载了 Layui 但 `layui.form.render()` 报错？

业务回退页里没有 `.layui-form` 容器，调用 `render` 无意义。
仅在自定义页面（如登录页、框架页）里使用 `layui.form.render()`，不要放到 `head.php` 全局执行。

---

## 13. 相关文件索引

| 文件 | 职责 |
|------|------|
| `MPHX/theme.php` | 引擎实现（render / asset URL / 切换） |
| `MPHX/common.php` | 加载引擎 |
| `admin/set.php?gn=theme` | 切换 UI |
| `admin/api/setting.php` → `settheme` | 保存接口 |
| `imsetes/js/xtset.js` → `settheme()` | 前端保存脚本 |
| `imsetes/js/fn-hs.js` | 公共交互函数（`msalert`、`msloading` 等） |
| `imsetes/` | 公共静态资源（`mnbt_asset_url`） |
| `templates/default/**/assets/` | 默认主题私有资源（`mnbt_theme_asset`） |
| `templates/default/**` | 官方默认视图 |
| `templates/layui/**` | Layui 混合栈示例主题 |
| `templates/default/docker/` | Docker 控制台默认视图（V1.83+） |
| `docker/` | Docker 控制台控制器（V1.83+） |

---

## 14. 版本与兼容

- 主题系统自 MNBT 主题化改造版本起提供（见主仓库 `dev/v1.80` 及后续正式版）
- 升级程序时：自定义主题目录一般可保留；若官方 `default` 新增页面，旧主题未覆盖则自动用新 default
- 若官方修改某页 DOM 结构，依赖旧 DOM 的自定义主题可能需跟进调整
- 资源 API：`mnbt_theme_url` 会对主题私有文件做 default 回退；`mnbt_asset_url` 始终指向 `imsetes/`
- `layui` 主题自 v1.81 起提供，作为混合栈示例
- `docker` scope 自 v1.83 起支持，提供独立的 Docker 控制台视图体系

如有疑问，可在项目 Issue 中反馈并附上主题目录结构与报错截图。
