---
title: API 通用约定
description: MNBT 全部接口的请求约定、统一响应格式、错误码与鉴权机制
---

# API 通用约定

## 1.1 请求约定

| 项目 | 约定 |
|------|------|
| 编码 | UTF-8（`Content-Type: text/html; charset=UTF-8` 或 `application/json; charset=UTF-8`） |
| 请求方法 | 默认 `POST`（外部 API 同时需要 `?gn=xxx` 查询参数） |
| 参数类型 | `application/x-www-form-urlencoded`（文件上传除外） |
| 必带参数 | `gn` —— 操作动作名（action） |
| Cookie | 已登录用户/管理员需带上对应的 `user_token` / `admin_token` |
| 路径前缀 | 后台：`/admin/ajax.php`；用户端：`/user/ajax.php`；外部：`/api/api.php` |

**请求示例：**

```http
POST /admin/ajax.php HTTP/1.1
Host: your-domain.com
Cookie: admin_token=xxxxxx
Content-Type: application/x-www-form-urlencoded

gn=listzj&page=1&limit=20&sortOrder=desc&sort=id
```

## 1.2 统一响应格式

系统使用 `Response` 类（`MPHX/Response.php`）和 `mnbt_json_encode()` 函数统一输出 JSON。

**结构：**

```json
{
  "success": true,
  "code": "返回信息或状态码",
  "msg": "返回信息",
  "redirect": null,
  "data": {}
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| `success` | bool | 业务是否成功 |
| `code` | string/int | 状态码或文本消息（`'0'`、`'4'`、`'100'`、`'300'` 视为失败） |
| `msg` | string | 文本消息（与 `code` 同义） |
| `redirect` | string/null | 跳转地址（可选） |
| `data` | object/array | 业务数据（可选） |

> **历史兼容**：部分老接口直接返回 `{"code": "文本消息"}` 或 `{"qk": 1|4, "code": "..."}`，以接口实际说明为准。

## 1.3 错误码

| code 值 | 含义 | success |
|---------|------|---------|
| `1` / `200` / 文本 | 成功 | `true` |
| `0` / `4` / `100` / `300` | 失败 | `false` |
| `"请登陆"` / `"请登陆后台"` | 未登录 | `false` |
| `"请求错误！"` | 未知 `gn` | `false` |

**外部 API 错误码：**

| code | 含义 |
|------|------|
| 200 | 成功 |
| 100 | 参数错误 / 鉴权失败 / 业务失败 |
| 300 | 插件版本不兼容 |

## 1.4 鉴权机制

### Cookie Token 鉴权（管理端 / 用户端）

| 端 | Cookie 名 | 加密方式 | 校验流程 |
|----|-----------|----------|----------|
| 管理后台 | `admin_token` | `authcode()` + `SYS_KEY` | 解密出 `user\tsid`，`sid = md5(conf.user.conf.pwd.password_hash)` |
| 用户面板 | `user_token` | `authcode()` + `SYS_KEY` | 解密后查 `MN_zj` 表，校验 `md5(user.pass.password_hash)` |
| 插件独立 | `account_token` | `authcode()` + 独立盐 | 插件自维护 `MN_plugin_user` 表（参见 user_info 插件） |

> `authcode()` 是双向加解密函数（RC4 风格 + base64），定义在 `MPHX/function.php`。  
> `password_hash` 为全局混淆盐，在 `MPHX/common.php` 中初始化。

**鉴权失败响应：**

- 管理后台：`{"code":"请登陆"}`
- 用户面板：`{"code":"请登陆"}`
- 控制面板关闭：`{"code":"宝塔服务器配置错误，请联系管理员"}`

### 外部 API 双重密钥鉴权

外部 API（`api/api.php`）需要同时验证：

1. **系统密钥**：`mn_key` == `MN_config.api`（后台「系统设置 → API 密钥」）
2. **宝塔调用密钥**：`mn_keye` == `md5(MN_bt.ktmy . MN_bt.qmk)`（每个宝塔节点独立）

### MNBT 节点 API 鉴权

- 使用 `node_id` + `node_secret` 组合
- 通过 `mnbt_node_authenticate()` 校验请求体与节点信息
- 支持 nonce 防重放攻击（`MN_node_nonce` 表）

---

## 接口文档导航

- [后台管理接口](./admin.md) —— `admin/ajax.php`：登录、宝塔节点、主机管理、一键部署、订单、系统设置等
- [用户控制面板接口](./user.md) —— `user/ajax.php`：站点配置、文件管理、域名、SSL、数据库、监控等
- [外部对接 API](./external.md) —— `api/api.php` 主机生命周期 API、`api/node.php` 节点 API
- [插件对接 API](./plugin.md) —— 插件 AJAX / 页面 / 注册函数 / 钩子 / 支付 / 自动鉴权
- [核心工具函数](./functions.md) —— `MPHX/function.php`、`MPHX/Response.php` 工具函数
- [数据库表速查](./database.md) —— 全部 18 张数据表与常用请求示例
- [Docker 容器服务 API](./docker.md) —— 外部开通、用户控制台、后台管理、容器生命周期
- [外部运维 API（魔方财务对接）](./docker-mofang.md) —— 魔方财务 server module 对接接口
- [Docker 控制台与内部实现](./docker-console.md) —— 宝塔 Docker API 封装、认证机制、数据库表结构
