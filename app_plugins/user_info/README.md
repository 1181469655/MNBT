# user_info 用户信息插件

独立用户系统，提供注册、登录、个人信息、修改密码。独立于核心主机账号体系，一个用户可绑定多台主机。

## 功能

- 用户注册（用户名 3-32 位字母数字下划线，密码 bcrypt 哈希）
- 用户登录（独立 `account_token` cookie，与核心 `user_token` 不冲突）
- 个人信息（邮箱、QQ）
- 修改密码（改密后旧 cookie 自动失效）
- 管理员端：用户列表、搜索、启用/禁用、重置密码、编辑用户

## 依赖

无。其他插件（balance、hosting_shop）依赖本插件提供认证。

## 安装

1. 后台 → 系统管理 → 插件管理 → 安装 `user_info` → 启用
2. 自动创建 `MN_plugin_user` 表

## 数据表

### MN_plugin_user

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT AUTO_INCREMENT | 主键 |
| username | VARCHAR(64) | 用户名（唯一） |
| password_hash | VARCHAR(255) | bcrypt 哈希 |
| email | VARCHAR(128) | 邮箱（可选） |
| qq | VARCHAR(20) | QQ（可选） |
| status | TINYINT | 1=正常 0=禁用 |
| created_at | DATETIME | 注册时间 |
| updated_at | DATETIME | 更新时间 |

## 访问路径

无需 Web 服务器 rewrite，通过查询参数路由访问：

| 页面 | URL |
|------|-----|
| 登录 | `index.php?_r=/account/login` |
| 注册 | `index.php?_r=/account/register` |
| 个人信息 | `index.php?_r=/account/profile` |
| 修改密码 | `index.php?_r=/account/password` |
| 退出 | `index.php?_r=/account/logout` |

如已配置 Nginx/Apache rewrite，也可用 `/account/login` 等简洁路径。

## API 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/account/api/login` | 登录 |
| POST | `/account/api/register` | 注册 |
| POST | `/account/api/update_profile` | 更新个人信息 |
| POST | `/account/api/change_password` | 修改密码 |

## 管理员端

| 页面 | 入口 |
|------|------|
| 用户列表 | `plugin.php?p=user_info&page=users` |
| 用户编辑 | `plugin.php?p=user_info&page=user_edit&id={id}` |

管理员 AJAX：

| gn | 说明 |
|----|------|
| `user_info_admin_reset_password` | 重置用户密码 |

## 认证机制

- Cookie 名：`account_token`
- 加密：`authcode($user_id \t $session_hash, SYS_KEY)`
- `session_hash = md5($user_id . $password_hash . SYS_KEY)`
- 修改密码后 `password_hash` 变化 → `session_hash` 变化 → 旧 cookie 自动失效
- 密码存储：`password_hash()` / `password_verify()`（bcrypt）

## 核心 API

| 函数 | 说明 |
|------|------|
| `user_info_auth_current()` | 获取当前登录用户（数组），未登录返回 null |
| `user_info_auth_require()` | 要求登录，未登录跳转登录页，返回用户数组 |
| `user_info_auth_login($user_id, $password_hash)` | 设置登录 cookie |
| `user_info_auth_logout()` | 清除登录 cookie |
| `user_info_url($path)` | 生成插件页面 URL |
| `user_info_asset_url($path)` | 生成静态资源 URL（自动补 `assets/` 前缀） |

## 文件结构

```
user_info/
├── plugin.json          # 插件元数据
├── bootstrap.php        # 主入口（路由 + API + 管理员页面注册）
├── install.sql          # 建表 SQL
├── uninstall.sql        # 删表 SQL
├── lib/
│   └── auth.php         # 认证函数库
├── assets/
│   └── style.css        # 用户端样式
└── views/
    ├── layout.php       # 公共布局
    ├── login.php        # 登录页
    ├── register.php     # 注册页
    ├── profile.php      # 个人信息页
    ├── password.php     # 修改密码页
    └── admin/
        ├── users.php    # 管理员-用户列表
        └── user_edit.php # 管理员-用户编辑
```
