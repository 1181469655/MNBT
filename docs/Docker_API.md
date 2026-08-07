# MNBT Docker API 对接文档

> **版本**：V1.83  
> **更新日期**：2026-08-04  
> **适用范围**：MNBT Docker 模块的内外部 API 对接，包括第三方开通接口、用户控制台 AJAX、宝塔 Docker API 封装

---

## 目录

- [1. 架构概述](#1-架构概述)
- [2. 外部 API（第三方对接）](#2-外部-api第三方对接)
  - [2.1 鉴权](#21-鉴权)
  - [2.2 连接验证](#22-连接验证)
  - [2.3 开通 Docker 账户](#23-开通-docker-账户)
  - [2.4 续费](#24-续费)
  - [2.5 删除](#25-删除)
- [3. 内部 API（用户控制台）](#3-内部-api用户控制台)
  - [3.1 登录 / 登出](#31-登录--登出)
  - [3.2 我的容器](#32-我的容器)
  - [3.3 容器启停](#33-容器启停)
  - [3.4 应用商店](#34-应用商店)
  - [3.5 创建应用（开通容器）](#35-创建应用开通容器)
  - [3.6 镜像 / 存储卷 / Compose](#36-镜像--存储卷--compose)
- [4. 宝塔 Docker API 封装](#4-宝塔-docker-api-封装)
  - [4.1 类说明](#41-类说明)
  - [4.2 方法列表](#42-方法列表)
- [5. 认证机制](#5-认证机制)
- [6. 数据库表结构](#6-数据库表结构)
- [7. 容器生命周期](#7-容器生命周期)

---

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

所有请求均需携带以下 POST 参数：

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

**请求体**（鉴权参数同上，略 `username` 可填任意值）：

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
| `username` | string | 是 | Docker 账号（≥ 4 位） |
| `password` | string | 是 | 登录密码（≥ 6 位） |
| `dqtime` | string | 否 | 到期时间，传 `"0"` 表示永久 |
| `plan_id` | int | 否 | 套餐 ID（`MN_docker_plan.id`），不传则不绑定套餐 |
| `email` | string | 否 | 邮箱 |

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

### 2.4 续费

通过系统后台订单系统续费，或直接更新 `MN_docker_user.datae`（到期时间）与 `qk='active'`。

Cron 任务 `docker/user_cron.php` 每天自动处理：
- 到期账户：`qk` 从 `active` → `expired`
- 过期 7 天：删除节点容器 → `qk=pruned`
- 摘除 7 天：物理删除用户行

### 2.5 删除

通过管理后台 `admin/api/docker.php` 删除用户，或直接 DELETE `MN_docker_user`。

---

## 3. 内部 API（用户控制台）

**入口**：`POST docker/ajax.php?gn=<操作>`  
**认证**：`docker_token` cookie（login/logout 除外）  
**CSRF**：所有请求需附带 `MNBT_CSRF_TOKEN` 字段

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

### 3.6 镜像 / 存储卷 / Compose

```
POST docker/ajax.php?gn=image_list     → 镜像列表
POST docker/ajax.php?gn=volume_list    → 存储卷列表
POST docker/ajax.php?gn=compose_list   → Compose 模板 + 项目列表
```

**无需额外参数**。

---

## 4. 宝塔 Docker API 封装

### 4.1 类说明

**文件**：`MPHX/bt_docker.php`  
**类名**：`bt_docker`

独立于 `bt_api`（网站管理 API），专用于宝塔 Docker 模块。自持 HTTP transport 与签名逻辑，不与 `bt_api` 共享状态。

**签名算法**（与 `bt_api` 一致）：

```
request_token = md5(request_time . md5(BT_KEY))
```

- GET 路由：`/btdocker/<module>/<method>`，签名 + `request_time` 拼入 query string，`request_token` 写入 cookie
- POST 路由：`/mod/docker/com/<method>/stype`，签名 + `request_time` + 业务参数拼入 body，`request_token` 写入 cookie

### 4.2 方法列表

#### 安装与配置（GET /btdocker/setup/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `get_config()` | `get_config` | Docker 服务状态（service_status / docker_installed / docker_compose_installed） |
| `install_docker_program()` | `install_docker_program` | 安装 Docker 程序 |
| `get_registry_mirrors()` | `get_registry_mirrors` | 镜像加速配置 |
| `set_registry_mirrors($mirrors)` | `set_registry_mirrors` | 设置镜像加速 |
| `set_monitor_save_date($day)` | `set_monitor_save_date` | 监控数据保留天数 |

#### 容器管理（GET /btdocker/container/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `container_list()` | `get_list` | 所有容器列表（**注意：`ports` 字段恒为空数组，端口信息需用 `installed_apps()` 获取**） |
| `container_start($id, $name)` | `start` | 启动容器 |
| `container_stop($id, $name)` | `stop` | 停止容器 |
| `container_restart($id, $name)` | `restart` | 重启容器 |
| `container_del($id, $name)` | `del` | 删除容器 |
| `container_prune()` | `prune` | 清理无用容器 |
| `container_cmd_log($id, $name)` | `get_cmd_log` | 容器执行日志（**注意：仅返回布尔值 `true`，不返回日志内容**） |

#### 镜像管理（GET /btdocker/image/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `image_list()` | `image_list` | 本地镜像列表 |
| `image_prune()` | `prune` | 清理无用镜像 |

#### 存储卷（GET /btdocker/volume/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `volume_list()` | `get_volume_list` | 存储卷列表 |
| `volume_add($name, $driver)` | `add` | 创建存储卷 |
| `volume_prune()` | `prune` | 清理无用存储卷 |

#### 网络（GET /btdocker/network/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `network_list()` | `get_host_network` | 网络列表 |
| `network_create($name, $driver)` | `create_network` | 创建网络 |
| `network_prune()` | `prune` | 清理无用网络 |

#### 仓库（GET /btdocker/registry/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `registry_list()` | `registry_list` | 镜像仓库列表 |
| `registry_set_remark($id, $remark)` | `set_remark` | 设置仓库备注 |

#### Compose（GET /btdocker/compose|project/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `template_list()` | `template_list` | Compose 模板列表 |
| `project_list()` | `get_project_list` | Docker 项目列表 |

#### 应用商店（POST /mod/docker/com/）

| 方法 | 宝塔端点 | 说明 |
|------|----------|------|
| `app_list()` | `get_apps` | 应用列表（约 291 个应用，含 appname/apptitle/apptype/appversion/depend/env/field） |
| `app_create($params)` | `create_app` | 安装应用（异步），参数见 [§3.5](#35-创建应用开通容器) |
| `app_dependence($app)` | `get_dependence_apps` | 查询依赖应用安装状态 |
| `installed_apps()` | `get_installed_apps` | **已安装应用列表（含完整容器详情、端口、应用参数）**，是获取容器信息的主要数据源 |

---

## 5. 认证机制

### 5.1 Docker 用户认证

**文件**：`MPHX/docker.member.php`

| 函数 | 用途 |
|------|------|
| `docker_auth_login($uid, $hash)` | 登录：写入 `docker_token` cookie（`uid + '|' + md5(uid . hash . IP)`） |
| `docker_auth_logout()` | 登出：删除 `docker_token` cookie |
| `docker_auth_current()` | 获取当前登录用户行（查 `MN_docker_user`） |
| `docker_auth_require()` | 获取当前用户，未登录则 `docker_json(401, '请先登录')` |
| `docker_auth_password_hash($pass)` | 密码哈希 |
| `docker_auth_password_verify($pass, $hash)` | 密码验证 |

### 5.2 外部 API 鉴权

详见 [§2.1](#21-鉴权)。

### 5.3 CSRF 保护

`docker/ajax.php` 所有请求（login/logout 除外）均需携带 `MNBT_CSRF_TOKEN` 字段，由 `mnbt_csrf_validate_request()` 验证。

---

## 6. 数据库表结构

### MN_docker_node（Docker 节点）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int(11) | 主键 |
| `name` | varchar(64) | 节点名称（显示用） |
| `btip` | varchar(128) | 宝塔面板地址（IP/域名） |
| `btdk` | varchar(10) | 宝塔端口（默认 `8888`） |
| `ptl` | varchar(10) | 是否 HTTPS（`true`/`false`） |
| `btmy` | varchar(255) | 宝塔接口密钥 |
| `ktmy` | varchar(255) | 调用密钥（外部 API 鉴权用） |
| `qmk` | varchar(255) | 二级验证密钥 |
| `qk` | varchar(10) | 启用/禁用（`true`/`false`） |
| `date` | varchar(50) | 添加时间 |

### MN_docker_user（Docker 用户）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int(11) | 主键 |
| `username` | varchar(64) | 登录账号 |
| `password_hash` | varchar(255) | 密码哈希 |
| `email` | varchar(128) | 邮箱 |
| `ssbt` | int(11) | 所属节点 ID（→ `MN_docker_node.id`） |
| `service_name` | varchar(64) | 宝塔服务名（创建容器时分配，唯一标识） |
| `app_name` | varchar(64) | 已安装应用名 |
| `container_id` | varchar(128) | Docker 容器 ID |
| `container_spec` | text | 容器规格 JSON（app_name/m_version/cpus/memory_limit/params） |
| `container_status` | varchar(20) | 容器状态（`none`/`creating`/`running`/`stopped`） |
| `data` | varchar(50) | 开通时间 |
| `datae` | varchar(50) | 到期时间（`0000-00-00`=永久） |
| `qk` | varchar(20) | 用户状态（`active`/`expired`/`pruned`/`paused`） |
| `plan_id` | int(11) | 套餐 ID |
| `expired_at` | varchar(50) | 到期时间戳 |
| `prune_due` | varchar(50) | 摘除期限 |
| `created_at` | varchar(50) | 创建时间 |

### MN_docker_plan（Docker 套餐）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int(11) | 主键 |
| `name` | varchar(64) | 套餐名称 |
| `cpu_max` | float | CPU 上限 |
| `mem_max` | float | 内存上限（MB） |
| `price` | decimal(10,2) | 价格 |
| `qk` | varchar(10) | 启用/禁用 |

### MN_docker_order（Docker 订单）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int(11) | 主键 |
| `user_id` | int(11) | 用户 ID |
| `plan_id` | int(11) | 套餐 ID |
| `amount` | decimal(10,2) | 金额 |
| `status` | varchar(20) | 订单状态 |
| `date` | varchar(50) | 下单时间 |

---

## 7. 容器生命周期

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

---

## 参考

- 宝塔 Docker API 文档：https://docs.bt.cn/api/docker/
- MNBT 项目 PRD：`docs/DOCKER_PRD.md`
- 测试文档：`docs/DOCKER_TEST.md`
- 主题开发文档：`templates/THEME_DEV.md`