# MNBT 主题开发手册

本文面向需要**新建主题**或**改版默认皮肤**的开发者。  
阅读前请先看 [README.md](./README.md) 了解切换方式与目录结构。

---

## 1. 设计原则

1. **路由不变**：访问地址仍是 `/user/login.php`、`/admin/set.php?gn=wz`，不要改控制器 URL。
2. **逻辑与视图分离**：鉴权、查库、调宝塔 API 放在 `user/`、`admin/` 控制器；HTML 放主题。
3. **AJAX 路径不变**：页面内请求仍使用 `./ajax.php`、`../user/ajax.php` 等现有接口。
4. **缺页回退**：自定义主题只需覆盖要改的页面，其余自动使用 `default`。
5. **双端独立**：用户端主题与管理端主题可分别选择。

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

> 官方示例 SPA 主题：`templates/qingliangyun/`（Vue 3 + Element Plus），见 [qingliangyun/README.md](./qingliangyun/README.md)。

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
3. 或写入 `active_user_theme` / `active_admin_theme`

### 步骤 5：自测清单

- [ ] 登录 / 退出  
- [ ] 框架页侧栏与多标签（index）  
- [ ] 至少 2～3 个业务子页（仪表盘、设置、列表）  
- [ ] 表单提交与 AJAX 弹窗  
- [ ] 静态资源 404 检查（CSS/JS/图片）  

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

### 3.3 不走主题的路径（一般不要动）

| 路径 | 原因 |
|------|------|
| `user/ajax.php`、`user/api/*` | JSON API |
| `admin/ajax.php`、`admin/api/*` | JSON API |
| `user/pay.php`、`notify_url.php` 等 | 支付跳转 |
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

---

## 6. DOM / JS 兼容注意

现有大量页面 JS 依赖固定元素 `id` 与 class（如 `#username`、`#password`、`setwz()`、`msalert` 等）。

**改外观时：**

- 可以改 class、布局、样式  
- **不要随意改**表单控件 `id`、关键按钮的 `onclick` 函数名  
- 若必须改结构，需同步修改页面内 JS 或独立 `assets/*.js`

公共交互函数多在：

- `imsetes/js/fn-hs.js`（`msalert`、`msloading` 等）
- `imsetes/js/xtset.js`（后台设置页）
- 各页面内联 `<script>`

---

## 7. 主题引擎行为细节

### 7.1 解析顺序（以用户端 `sy` 为例）

1. `templates/{当前主题}/user/sy.php`  
2. 若不存在：`templates/default/user/sy.php`  
3. 仍不存在：输出错误 `Theme view not found`

### 7.2 主题名校验

仅保留 `[a-zA-Z0-9_-]`，防止路径注入。

### 7.3 写入激活文件

`mnbt_theme_set_active('user', 'my_theme')` 会：

1. 检查目录 `templates/my_theme/user` 是否存在  
2. 写入 `templates/active_user_theme`  
3. 尝试 `UPDATE MN_config SET usertheme=?`（字段不存在则失败被忽略）

管理端同理（`admintheme` / `active_admin_theme`）。

---

## 8. 从 default 改版 vs 独立主题

| 方式 | 适用 | 优点 | 缺点 |
|------|------|------|------|
| 直接改 `templates/default/` | 官方迭代、唯一皮肤 | 简单 | 升级时冲突大 |
| 新建 `templates/xxx/` 只覆盖部分页 | 多皮肤、客户定制 | 可回退、可切换 | 需维护契约 |

**推荐**：客户定制用独立主题目录；核心功能升级只动 `default` 与控制器。

---

## 9. 发布主题包建议

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

## 10. 常见问题

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

---

## 11. 相关文件索引

| 文件 | 职责 |
|------|------|
| `MPHX/theme.php` | 引擎实现（render / asset URL / 切换） |
| `MPHX/common.php` | 加载引擎 |
| `admin/set.php?gn=theme` | 切换 UI |
| `admin/api/setting.php` → `settheme` | 保存接口 |
| `imsetes/js/xtset.js` → `settheme()` | 前端保存脚本 |
| `imsetes/` | 公共静态资源（`mnbt_asset_url`） |
| `templates/default/**/assets/` | 默认主题私有资源（`mnbt_theme_asset`） |
| `templates/default/**` | 官方默认视图 |

---

## 12. 版本与兼容

- 主题系统自 MNBT 主题化改造版本起提供（见主仓库 `dev/v1.80` 及后续正式版）  
- 升级程序时：自定义主题目录一般可保留；若官方 `default` 新增页面，旧主题未覆盖则自动用新 default  
- 若官方修改某页 DOM 结构，依赖旧 DOM 的自定义主题可能需跟进调整  
- 资源 API：`mnbt_theme_url` 会对主题私有文件做 default 回退；`mnbt_asset_url` 始终指向 `imsetes/`  

如有疑问，可在项目 Issue 中反馈并附上主题目录结构与报错截图。
