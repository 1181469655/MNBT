---
title: Docker API
description: Docker 容器服务 API（外部开通、用户控制台、后台管理、容器生命周期与魔方财务对接）
---

# Docker API（V1.83）

> **版本**：V1.83  
> **更新日期**：2026-08-04  
> **适用范围**：MNBT Docker 模块的内外部 API 对接，包括第三方开通接口、用户控制台 AJAX、后台管理、魔方财务对接

> 本文档由原 API.md §8 与 Docker_API.md 合并去重而来。宝塔 Docker API 封装（bt_docker）、认证机制与数据库表结构见 [Docker 控制台与内部实现](./docker-console.md)。

## 1. 架构概述

```
第三方平台 / 插件
    │
    │ POST api/docker.php?gn=kt (外部 API，mn_key 鉴权)
    ▼
┌──────────────────────────────────────────────────┐
│                    MNBT 系统                       │
│                                                    │
│  ┌──────────────┐  ┌──────────────────────────────┐│
│  │ api/docker.php│  │     docker/ (用户控制台)      ││
│  │ (外部 API)    │  │                              ││
│  │              │  │  ├─ docker/ajax.php (AJAX)    ││
│  │ 开通/续费/删除│  │  ├─ console.php (我的容器)     ││
│  │              │  │  ├─ appstore.php (应用商店)    ││
│  └──────┬───────┘  │  ├─ image.php (镜像管理)      ││
│         │          │  ├─ volume.php (存储卷)       ││
│         │          │  └─ compose.php (Compose)     ││
│         │          └──────────────┬───────────────┘│
│         │                         │                 │
│  ┌──────▼─────────────────────────▼──────────────┐ │
│  │            MPHX/bt_docker.php                  │ │
│  │          (宝塔 Docker API 封装)                 │ │
│  └──────────────────────┬───────────────────────┘ │
│                         │                          │
│                  MN_docker_* 表                     │
│              (node/user/plan/order)                │
└─────────────────────────┼──────────────────────────┘
                          │
                          │ HTTP API (签名)
                          ▼
              ┌───────────────────────┐
              │   宝塔面板 Docker 模块  │
              │  (独立于网站管理)       │
              └───────────────────────┘
```

**核心概念**：

- **单容器模型**：每个 Docker 账户最多创建一个容器，通过 `MN_docker_user.service_name` + `container_id` 锚定
- **独立认证**：Docker 控制台使用 `docker_token` cookie，与 `admin_token`/`user_token` 完全隔离
- **独立节点**：Docker 节点存于 `MN_docker_node` 表，与 `MN_bt`（网站宝塔节点）解耦

---

## 2. 外部 API（第三方对接）

**入口**：`POST api/docker.php?gn=<操作>`

### 2.1 鉴权

所有请求均需携带以下 POST 参数（鉴权方式与 `api/api.php` 完全一致）：

| 参数 | 类型 | 说明 |
|------|------|------|
| `mn_key` | string | 系统后台 API 密钥（`MN_config.api` 字段值） |
| `mn_bh` | int | Docker 节点编号（`MN_docker_node.id`） |
| `mn_keye` | string | `md5(节点ktmy . 节点qmk)` |
| `mn_vs` | int | 插件版本号，必须 ≥ 15 |
| `username` | string | 待操作 Docker 账号 |

可通过 `gn=cfif` 验证连接是否正常。

### 2.2 连接验证

```
POST api/docker.php?gn=cfif
```

```bash
curl -X POST "http://your-domain/api/docker.php?gn=cfif" \
  -d "mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=test"
```

**请求体**（鉴权参数同上，`username` 可填任意值）：

| 参数 | 值 |
|------|-----|
| `mn_key` | 系统 API 密钥 |
| `mn_bh` | 1 |
| `mn_keye` | md5(节点ktmy . 节点qmk) |
| `mn_vs` | 15 |
| `username` | test |

**成功响应**：

```json
{
  "success": true,
  "code": 200,
  "msg": "连接验证成功！"
}
```

### 2.3 开通 Docker 账户

```
POST api/docker.php?gn=kt
```

**请求体**：

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `mn_key` | string | 是 | 系统 API 密钥 |
| `mn_bh` | int | 是 | 节点编号 |
| `mn_keye` | string | 是 | `md5(ktmy . qmk)` |
| `mn_vs` | int | 是 | ≥ 15 |
| `username` | string | 是 | Docker 账号（≥ 4 位，唯一） |
| `password` | string | 是 | 登录密码（≥ 6 位，bcrypt 存储） |
| `dqtime` | string | 否 | 到期时间，传 `"0"` 表示永久（默认） |
| `plan_id` | int | 否 | 套餐 ID（`MN_docker_plan.id`，需为上架状态），不传则不绑定套餐 |
| `email` | string | 否 | 邮箱 |

```bash
curl -X POST "http://your-domain/api/docker.php?gn=kt" \
  -d "mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=duser1&password=dpass123&dqtime=2026-12-31&plan_id=1"
```

**成功响应**：

```json
{
  "success": true,
  "code": 200,
  "msg": "Docker 账户开通成功！"
}
```

**错误码**：

| code | 含义 |
|------|------|
| 100 | 参数错误 / 账号已存在 / 节点不存在 / 密钥不匹配 |
| 300 | 插件版本过低 |

> 开通仅创建账户，容器由用户登录 `docker/` 控制台后在应用商店自行创建（单容器模型）。

### 2.4 续费

通过系统后台订单系统续费，或直接更新 `MN_docker_user.datae`（到期时间）与 `qk='active'`；第三方平台也可直接调用外部运维 API 的 `gn=xf` 接口续费（见 [外部运维 API](#6-外部运维-apimagic-square-finance-对接)）。

到期后的自动软删处理由 cron 任务 `docker_cron.php` 执行，详见 [容器生命周期](#5-容器生命周期)。

### 2.5 删除

通过管理后台 `admin/api/docker.php` 删除用户，或直接 DELETE `MN_docker_user`；第三方平台可调用外部运维 API 的 `gn=tj` 接口删除（见 [外部运维 API](#6-外部运维-apimagic-square-finance-对接)）。

---

## 3. 内部 API（用户控制台）

**入口**：`POST docker/ajax.php?gn=<操作>`  
**认证**：`docker_token` cookie（login/logout 除外）  
**CSRF**：所有请求（login/logout 除外）需携带 CSRF Token（`MNBT_CSRF_TOKEN` 字段，或 `_csrf` 字段 / `X-CSRF-TOKEN` 头），由 `mnbt_csrf_validate_request()` 验证

### 3.1 登录 / 登出

#### 登录

```
POST docker/ajax.php?gn=login
```

| 参数 | 说明 |
|------|------|
| `username` | Docker 账号 |
| `password` | 密码 |
| `MNBT_CSRF_TOKEN` | CSRF Token |

**响应**：

```json
{"code": 200, "msg": "登录成功"}
```

#### 登出

```
POST docker/ajax.php?gn=logout
```

### 3.2 我的容器

```
POST docker/ajax.php?gn=my_container
```

**无需额外参数**（自动读取当前用户信息）。

**响应**：

```json
{
  "code": 200,
  "msg": "ok",
  "container": {
    "service_name": "mnbt_test",
    "appname": "frps",
    "apptitle": "FRP 服务端",
    "appdesc": "专注于内网穿透的高性能反向代理应用",
    "status": "running",
    "port": ["29369", "26219", "36043", "41074"],
    "server_ip": "150.158.137.178",
    "host_ip": "0.0.0.0",
    "container_id": "b0a0d1bb7d53...",
    "m_version": "latest",
    "s_version": "",
    "version": "latest",
    "home": "https://github.com/snowdreamtech/frp",
    "appinfo": [
      {"fieldKey": "frps_web_port", "fieldTitle": "frp服务器web端口", "fieldValue": "29369"},
      {"fieldKey": "frps_server_port", "fieldTitle": "frp服务器端口", "fieldValue": "26219"}
    ]
  },
  "me": {
    "id": 1,
    "username": "test",
    "container_status": "running",
    "container_id": "b0a0d1bb7d53...",
    "service_name": "mnbt_test",
    "app_name": "frps",
    "container_spec": "{...}",
    "qk": "active",
    "datae": "0000-00-00"
  },
  "node": {
    "btip": "150.158.137.178",
    "ptl": "true"
  }
}
```

**`container` 字段说明**（来自宝塔 `get_installed_apps` 接口）：

| 字段 | 说明 |
|------|------|
| `service_name` | 服务名（容器的唯一标识，创建时传入） |
| `appname` | 应用英文名 |
| `apptitle` | 应用中文名 |
| `appdesc` | 应用简介 |
| `status` | 容器状态（`running` / `stopped` / `creating`） |
| `port` | 端口数组（宿主机端口，如 `["29369","26219"]`） |
| `server_ip` | 节点外网 IP |
| `host_ip` | 容器绑定的 host IP |
| `container_id` | Docker 容器 ID（64 位 hex） |
| `m_version` | 主版本号 |
| `s_version` | 子版本号 |
| `version` | 完整版本号 |
| `home` | 应用主页链接 |
| `appinfo` | 应用参数数组，每项含 `fieldKey`/`fieldTitle`/`fieldValue` |

**`container_status` 枚举**：

| 值 | 含义 |
|-----|------|
| `none` | 未创建容器 |
| `creating` | 创建中（前端每 8 秒轮询此接口） |
| `running` | 运行中 |
| `stopped` | 已停止 |

### 3.3 容器启停

```
POST docker/ajax.php?gn=container_start
POST docker/ajax.php?gn=container_stop
POST docker/ajax.php?gn=container_restart
```

**无需参数**（自动操作当前用户的容器）。

**响应**：

```json
{"code": 200, "msg": "操作完成", "raw": {...}}
```

### 3.4 应用商店

#### 应用列表

```
POST docker/ajax.php?gn=app_list
```

返回宝塔应用市场全部应用（约 291 个），每项含 `appname`/`apptitle`/`apptype`/`appversion`/`depend`/`env`/`field`。

#### 应用详情

```
POST docker/ajax.php?gn=app_detail
```

| 参数 | 说明 |
|------|------|
| `appname` | 应用英文名 |

#### 依赖查询

```
POST docker/ajax.php?gn=app_dependence
```

| 参数 | 说明 |
|------|------|
| `appname` | 应用英文名 |

### 3.5 创建应用（开通容器）

```
POST docker/ajax.php?gn=app_create
```

**单容器限制**：已有容器的用户调用此接口会返回错误。

**请求体**：

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `app_name` | string | 是 | 应用英文名（如 `frps`、`wordpress`） |
| `m_version` | string | 是 | 主版本号（如 `latest`、`8`） |
| `s_version` | string | 否 | 子版本号（如 `0`、`7.2`），无子版本时留空 |
| `cpus` | int | 否 | CPU 核数限制（0=不限制，受套餐上限约束） |
| `memory_limit` | int | 否 | 内存限制 MB（0=不限制，受套餐上限约束） |
| `allow_access` | string | 否 | 是否允许外部访问（`"1"` 或 `"0"`） |
| 其他 | string | 否 | 应用专属参数（如 `frps_web_port`、`frps_password` 等），由前端表单动态生成 |

**`service_name` 自动生成**：`mnbt_` + 用户名净化（去除非字母数字 → 小写 → 截取前 20 位）。

**成功响应**：

```json
{
  "code": 200,
  "msg": "应用创建请求已提交，请耐心等待 1-5 分钟初始化",
  "service_name": "mnbt_test"
}
```

创建后系统自动设置 `container_status='creating'`，前端每 8 秒轮询 `my_container` 接口检查状态。

### 3.6 镜像 / 存储卷 / Compose / 安装日志

```
POST docker/ajax.php?gn=image_list     → 镜像列表
POST docker/ajax.php?gn=volume_list    → 存储卷列表
POST docker/ajax.php?gn=compose_list   → Compose 模板 + 项目列表
POST docker/ajax.php?gn=install_log    → 安装进度日志（get_cmd_log）
```

**无需额外参数**。

> `install_log` 用于应用异步安装期间跟进进度（宝塔 `get_cmd_log`，注意仅返回布尔值 `true`，不返回日志内容），与 `console.php` 每 8 秒刷新容器状态配合。

---

## 4. 后台管理接口

需管理员登录，入口 `admin/ajax.php`，指令前缀 `docker_`：

| `gn` | 说明 |
|------|------|
| `docker_user_list` | 用户列表（bootstrap-table） |
| `docker_user_add` / `docker_user_edit` / `docker_user_del` | 用户增改删 |
| `docker_user_reset` | 重置密码 |
| `docker_user_pause` / `docker_user_resume` | 暂停/恢复 |
| `docker_plan_list` / `docker_plan_add` / `docker_plan_edit` / `docker_plan_del` | 套餐管理 |
| `docker_node_config` | 节点 Docker 配置（get_config） |
| `docker_node_containers` | 节点容器列表 |
| `docker_options` | 节点/套餐下拉数据 |

---

## 5. 容器生命周期

```
用户开通 Docker 账户（外部 API 或管理后台）
    │
    │ qk=active, container_status=none
    ▼
用户登录控制台 → 应用商店选择应用 → 创建容器
    │
    │ 调用 bt_docker::app_create()
    │ container_status=creating
    ▼
宝塔异步安装（1-5 分钟）
    │
    │ 前端每 8 秒轮询 my_container → installed_apps()
    ▼
status=running → container_status=running（同步到 MN_docker_user）
    │
    ├─ 用户操作：停止 → stopped / 启动 → running / 重启
    │
    └─ 到期处理（Cron 每天执行）：
        │
        ├─ active 且到期 → qk=expired, expired_at=到期时间
        │
        ├─ expired 满 7 天 → 删除节点容器 → qk=pruned, prune_due=当天
        │
        └─ pruned 满 7 天 → 物理删除用户行
```

### 到期软删定时任务

```bash
# 建议每 30 分钟执行
curl "http://your-domain/docker_cron.php?my=YOUR_API_KEY"
```

三阶段软删：`active→expired`（到期）→ `pruned`（满 7 天删容器）→ 物理删除（再满 7 天）。

---

## 6. 外部运维 API（魔方财务对接）

供魔方财务（idcsmart）server module 调用的 `zt`（暂停）/ `jc`（恢复）/ `tj`（删除）/ `xf`（续费）/ `bg`（变更套餐）/ `czmm`（重置密码）/ `ztcx`（状态查询）/ `sy`（用量查询）/ `start` / `stop` / `restart`（容器启停）接口，鉴权方式与 [2.1 鉴权](#21-鉴权) 完全一致，所有请求均携带 `mn_bh` / `mn_key` / `mn_keye` / `mn_vs=15` / `username`。

完整请求/响应示例见 [外部运维 API（魔方财务对接）](./docker-mofang.md)。

---

## 相关文档

- [Docker 控制台与内部实现](./docker-console.md) —— 宝塔 Docker API 封装、认证机制、数据库表结构
- 产品设计：[../prd/docker.md](../prd/docker.md)
- 测试：[../prd/docker-test.md](../prd/docker-test.md)
- 主题：[../development/theme/index.md](../development/theme/index.md)
- 宝塔 Docker API 文档：https://docs.bt.cn/api/docker/
