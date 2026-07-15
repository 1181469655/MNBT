# MNBT 前端主题系统

MNBT 支持用户端（控制面板）与管理端（后台）独立切换主题。  
业务逻辑仍在 `user/*.php` / `admin/*.php`，**外观与页面 HTML 在 `templates/` 下**。

| 文档 | 说明 |
|------|------|
| 本文档 | 快速说明、目录、切换方式 |
| [THEME_DEV.md](./THEME_DEV.md) | **主题开发完整手册**（新建主题、页面清单、API、约定） |

---

## 目录结构

```
templates/
├── README.md                 # 本说明
├── THEME_DEV.md              # 主题开发手册
├── active_user_theme         # 当前用户端主题目录名（纯文本）
├── active_admin_theme        # 当前管理端主题目录名（纯文本）
└── default/                  # 官方默认主题
    ├── theme.json            # 主题元信息
    ├── user/                 # 用户控制面板视图
    │   ├── head.php
    │   ├── login.php
    │   ├── index.php
    │   ├── sy.php
    │   ├── set.php
    │   ├── ...
    │   └── assets/           # 主题私有 CSS/JS/图片
    └── admin/                # 管理后台视图
        ├── head.php
        ├── login.php
        ├── index.php
        ├── set.php
        ├── ...
        └── assets/
```

自定义主题示例：

```
templates/my_skin/
├── theme.json
├── user/          # 可只放要覆盖的页面
│   └── login.php
└── admin/
    └── login.php
```

未提供的页面会**自动回退**到 `templates/default/` 同名文件。

---

## 切换主题

### 1. 后台界面（推荐）

管理后台 → **系统管理** → **前端模板** → 选择用户端 / 管理端主题 → 保存

对应页面：`admin/set.php?gn=theme`

### 2. 配置文件

编辑（内容仅为主题目录名，如 `default`）：

- `templates/active_user_theme`
- `templates/active_admin_theme`

### 3. 数据库（可选）

若 `MN_config` 表存在字段 `usertheme` / `admintheme`，则**优先于文件**读取。  
保存主题时会尝试写入这两个字段（字段不存在则忽略，不影响文件切换）。

### 优先级

```
MN_config.usertheme / admintheme
  → active_user_theme / active_admin_theme
  → default
```

---

## 架构一览

```
浏览器请求 user/sy.php
    │
    ▼
user/sy.php（控制器）
  · include MPHX/common.php
  · 登录校验 / 取数据
  · mnbt_render('sy')
    │
    ▼
MPHX/theme.php
  · 解析当前主题 + fallback
    │
    ▼
templates/{theme}/user/sy.php（视图）
  · mnbt_theme_include('head')
  · HTML / CSS / JS
```

管理端同理，使用 `mnbt_admin_render()` / `mnbt_admin_include()`。

---

## 核心 API（`MPHX/theme.php`）

| 函数 | 用途 |
|------|------|
| `mnbt_render($view, $vars=[], $exit=true, $scope='user')` | 渲染视图 |
| `mnbt_admin_render($view, ...)` | 渲染管理端视图 |
| `mnbt_theme_include($view, $vars=[], $scope='user')` | 引入局部模板（如 head） |
| `mnbt_admin_include($view, ...)` | 管理端局部模板 |
| `mnbt_theme_url($path, $scope)` | 主题内静态资源 URL |
| `mnbt_asset_url($path)` | 公共资源 `imsetes/` URL |
| `mnbt_theme_list($scope)` | 扫描已安装主题 |
| `mnbt_theme_set_active($scope, $name)` | 切换当前主题 |
| `mnbt_theme_name($scope)` | 当前主题名 |

详细参数与示例见 [THEME_DEV.md](./THEME_DEV.md)。

---

## 权限与安全

- 主题目录名仅允许：`a-z A-Z 0-9 _ -`
- 禁止路径穿越（`..`）
- 主题内 PHP 与站点同权限运行，**不要**安装不可信第三方主题
- 建议仅站长上传主题包；`templates/` 目录需可写（用于 `active_*` 文件）

---

## 相关代码位置

| 路径 | 说明 |
|------|------|
| `MPHX/theme.php` | 主题引擎 |
| `MPHX/common.php` | 自动加载 theme.php |
| `user/*.php` / `admin/*.php` | 控制器入口 |
| `admin/api/setting.php` | `settheme` 保存接口 |
| `imsetes/` | 公共前端静态资源（Bootstrap 等） |
