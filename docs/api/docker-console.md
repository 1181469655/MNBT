---
title: Docker 控制台与内部实现
description: 宝塔 Docker API 封装（bt_docker）、Docker 认证机制与数据库表结构
---

# Docker 控制台与内部实现

> 本文为 Docker API 文档的配套篇，覆盖宝塔 Docker API 封装（`bt_docker`）、认证机制与数据库表结构。外部开通 API、用户控制台 AJAX、后台管理与魔方财务对接见 [Docker API](./docker.md)。

## 1. 宝塔 Docker API 封装

### 1.1 类说明

**文件**：`MPHX/bt_docker.php`  
**类名**：`bt_docker`

独立于 `bt_api`（网站管理 API），专用于宝塔 Docker 模块。自持 HTTP transport 与签名逻辑，不与 `bt_api` 共享状态。

**签名算法**（与 `bt_api` 一致）：

```
request_token = md5(request_time . md5(BT_KEY))
```

- GET 路由：`/btdocker/<module>/<method>`，签名 + `request_time` 拼入 query string，`request_token` 写入 cookie
- POST 路由：`/mod/docker/com/<method>/stype`，签名 + `request_time` + 业务参数拼入 body，`request_token` 写入 cookie

### 1.2 方法列表

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
| `app_create($params)` | `create_app` | 安装应用（异步），参数见 [Docker API §3.5](./docker.md#35-创建应用开通容器) |
| `app_dependence($app)` | `get_dependence_apps` | 查询依赖应用安装状态 |
| `installed_apps()` | `get_installed_apps` | **已安装应用列表（含完整容器详情、端口、应用参数）**，是获取容器信息的主要数据源 |

---

## 2. 认证机制

### 2.1 Docker 用户认证

**文件**：`MPHX/docker.member.php`

| 函数 | 用途 |
|------|------|
| `docker_auth_login($uid, $hash)` | 登录：写入 `docker_token` cookie（`uid + '|' + md5(uid . hash . IP)`） |
| `docker_auth_logout()` | 登出：删除 `docker_token` cookie |
| `docker_auth_current()` | 获取当前登录用户行（查 `MN_docker_user`） |
| `docker_auth_require()` | 获取当前用户，未登录则 `docker_json(401, '请先登录')` |
| `docker_auth_password_hash($pass)` | 密码哈希 |
| `docker_auth_password_verify($pass, $hash)` | 密码验证 |

### 2.2 外部 API 鉴权

详见 [Docker API §2.1 鉴权](./docker.md#21-鉴权)。

### 2.3 CSRF 保护

`docker/ajax.php` 所有请求（login/logout 除外）均需携带 CSRF Token（`MNBT_CSRF_TOKEN` 字段，或 `_csrf` 字段 / `X-CSRF-TOKEN` 头），由 `mnbt_csrf_validate_request()` 验证。

---

## 3. 数据库表结构

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

## 相关文档

- [Docker API](./docker.md) —— 外部开通、用户控制台、后台管理、生命周期与魔方财务对接
- 产品设计：[../prd/docker.md](../prd/docker.md)
- 测试：[../prd/docker-test.md](../prd/docker-test.md)
- 主题：[../development/theme/index.md](../development/theme/index.md)
- 宝塔 Docker API 文档：https://docs.bt.cn/api/docker/
