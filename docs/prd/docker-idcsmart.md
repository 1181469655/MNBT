---
title: MNBT Docker × 魔方财务（idcsmart）对接 PRD
description: 魔方财务 server module 对接 MNBT Docker 容器产品的产品需求文档（已实现）
---

# MNBT Docker × 魔方财务（idcsmart）server module 对接模块 PRD

> 版本：v1.0（M1-M3 已交付，待联调验证）
> 日期：2026-08-07
> 状态：已实现
> 关联文档：[Docker 集成 PRD](./docker.md)、[Docker API 文档](../api/docker.md)、[Docker 测试文档](./docker-test.md)、[API 总览](../api/overview.md)、魔方财务《[服务器模块（server module）开发文档](https://docs.idcsmart.com/docs/%E8%B4%A2%E5%8A%A1%E7%B3%BB%E7%BB%9F%E5%BC%80%E5%8F%91%E6%96%87%E6%A1%A3/%E6%9C%8D%E5%8A%A1%E5%99%A8%E6%A8%A1%E5%9D%97%EF%BC%88server%20module%EF%BC%89)》

---

## 1. 背景与目标

### 1.1 背景

MNBT（V1.83）已具备 **Docker 容器分销**能力：独立表（`MN_docker_node/user/plan/order`）、独立 Docker 用户控制台（`docker/`）、宝塔 Docker API 封装（`MPHX/bt_docker.php`）以及对外开通 API（`api/docker.php`，当前仅 `cfif` 连接验证与 `kt` 开通两个动作）。

魔方财务（idcsmart）通过 **server module（服务器模块）** 机制接入第三方服务器/容器产品：模块位于 `/modules/servers/`，通过约定的方法名（`CreateAccount`/`SuspendAccount`/`TerminateAccount`/`Renew` 等）与外部系统交互，从而实现"产品 → 自动开通 → 前台管理 → 到期续费/删除"的完整闭环。

**目标**：开发一个魔方财务 server module（下称"对接模块"），使魔方财务可以将 MNBT Docker 容器作为标准服务器产品对外销售；同时补齐 MNBT 外部 Docker API 的运维动作（暂停/恢复/删除/续费/变更套餐/重置密码/状态/用量/容器启停），供魔方模块调用。

### 1.2 非目标

- 不改动 MNBT 现有 Docker 控制台、`bt_docker` 封装与宝塔侧逻辑。
- 不涉及 MNBT 自身的订单/支付体系（魔方财务承担下单与计费）。
- 魔方侧不新建数据库表（沿用 `shd_host` 等标准产品表）。

### 1.3 范围

| 期次 | 内容 |
|------|------|
| **P0（本期）** | 魔方侧：`mnbtdocker` 模块骨架（MetaData/ConfigOptions）＋ 生命周期方法（CreateAccount/SuspendAccount/UnsuspendAccount/TerminateAccount/Renew/ChangePackage）＋ Status/Sync ＋ ClientArea（状态页＋外链控制台）。MNBT 侧：`api/docker.php` 补齐 `zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart` |
| **P1（下期）** | 自动登录（SSO，`gn=dl` 一次性票据）＋ ClientArea 内嵌 iframe 控制台 ＋ Chart 磁盘用量图表 ＋ UsageUpdate 批量用量定时上报 |

---

## 2. 总体架构

### 2.1 架构图

```
┌─────────────────────────────────────────────────────────────┐
│                    魔方财务（idcsmart）                        │
│                                                             │
│  前台用户  ──下单/续费/删除/控制台──▶  shd_host / 订单体系        │
│                                       │                      │
│                                       ▼                      │
│            /modules/servers/mnbtdocker/mnbtdocker.php         │
│            (server module：CreateAccount/Renew/Status/...)    │
└──────────────────────────────────┬──────────────────────────┘
                                   │ HTTPS POST（mn_key/mn_bh/mn_keye 鉴权）
                                   ▼
┌─────────────────────────────────────────────────────────────┐
│              MNBT 外部 API：api/docker.php                    │
│  gn = cfif/kt/zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart │
│  (本期扩展 zt 之后全部动作；kt/cfif 已存在)                     │
└──────────────────────────────────┬──────────────────────────┘
                                   │
              ┌────────────────────┼────────────────────┐
              ▼                    ▼                    ▼
      MN_docker_node       MN_docker_user/plan/order   MPHX/bt_docker.php
      （节点信息）            （账号/套餐/订单）           （宝塔 Docker API 封装）
                                                         │
                                                         ▼
                                                   宝塔面板 Docker
                                              （容器/镜像/应用商店）
```

**单容器模型**：每个 MNBT Docker 账户仅对应一个容器；`CreateAccount` 只开通账号，容器由用户登录 MNBT Docker 控制台后在应用商店自行创建。魔方侧 `Status` 在未创建容器时返回 `waiting`。

### 2.2 模块命名

按魔方规范（模块名必须为单个单词、小写字母＋数字、字母开头）：

| 项 | 值 |
|----|----|
| 模块目录 | `/modules/servers/mnbtdocker/` |
| 主文件 | `mnbtdocker.php` |
| 显示名 | `MNBT Docker` |

> ⚠️ 不使用 `mnbt_docker`（含下划线，不符合"仅小写字母和数字"约束）。

---

## 3. 魔方侧：server module 设计

### 3.1 目录结构

```
modules/servers/mnbtdocker/
├── mnbtdocker.php        # 模块主文件（全部方法）
├── templates/
│   └── console.html      # ClientAreaOutput 控制台页模板（P0 用 thinkphp 模板）
└── README.md             # 安装/配置说明
```

### 3.2 MetaData

```php
function mnbtdocker_MetaData() {
    return [
        'DisplayName' => 'MNBT Docker',
        'APIVersion'  => '1.1',
        'HelpDoc'     => 'https://github.com/1181469655/MNBT', // 指向 MNBT 仓库
    ];
}
```

### 3.3 ConfigOptions（配置选项）

最多支持 24 项；**以 `key` 支持产品级可配置选项覆盖**（产品可配置选项若未定义对应 key，则读取模块配置值）。

| # | key | 类型 | 必填 | 默认 | 说明 |
|---|-----|------|------|------|------|
| 1 | `api_url` | text | 是 | 空 | MNBT 外部 API 地址，如 `https://mnbt.example.com/api/docker.php`；为空时用服务器字段兜底拼接（见 §3.4） |
| 2 | `api_key` | password | 是 | 空 | 系统 API 密钥（MNBT 后台「系统设置→API」的密钥，即 `$conf['api']`） |
| 3 | `node_id` | text | 是 | 空 | Docker 节点编号（`MN_docker_node.id`） |
| 4 | `call_key` | password | 是 | 空 | 调用密钥，`md5(节点ktmy . 节点qmk)`（直接在魔方后台粘贴 md5 值即可） |
| 5 | `plan_id` | text | 否 | 空 | 默认套餐 ID（`MN_docker_plan.id`），不填则不绑定套餐 |
| 6 | `console_url` | text | 否 | 空 | Docker 控制台地址，如 `https://mnbt.example.com/docker/login.php`；用于前台控制台入口 |
| 7 | `auto_login` | yesno | 否 | `0` | 是否启用自动登录（P1，`gn=dl` 一次性票据）；P0 恒为外链跳转 |

### 3.4 参数解析规则（config option 优先，server 兜底）

模块向 MNBT 发起请求前按以下优先级解析连接参数：

| 用途 | 优先读取 | 兜底来源（魔方服务器设置） |
|------|----------|----------------------------|
| API 地址 | `configoption1`（api_url） | `{server_secure==1?'https':'http'}://{server_ip}:{server_port}/api/docker.php` |
| 系统密钥 | `configoption2`（api_key） | `server_accesshash` |
| 节点编号 | `configoption3`（node_id） | `server_username` |
| 调用密钥 | `configoption4`（call_key） | `server_password` |

> 这样两种用法都可用：① 只填服务器设置，产品级零配置；② 在服务器设置留占位，按产品在配置选项精确指定。两者都缺失时返回配置缺失错误。

### 3.5 内置方法实现规格

通用约定：
- 所有调用为 `POST {api_url}?gn=<动作>`，表单携带 `mn_bh`、`mn_key`、`mn_keye`、`mn_vs=15`、`username` 及各动作参数（与 MNBT `api/docker.php` 现有鉴权完全一致）。
- 请求超时：同步操作 30s；`ztcx`/`sy` 因可能实时调用宝塔，超时放宽至 60s。
- 成功判定：MNBT 返回 `code==200` 且 `success==true`。
- 失败返回 `['status'=>'error','msg'=><MNBT 的 msg>]`；成功返回 `['status'=>'success']`（或 `'ok'`）。

| 魔方方法 | MNBT gn | 关键入参（来自 `$params`） | 说明 |
|----------|---------|---------------------------|------|
| `CreateAccount` | `kt` | `username`=`$params['username']`，`password`=`$params['password']`，`dqtime`=`nextduedate`（永久/空传 `0`），`plan_id`=配置项，`email`=`$params['user_info']['email']` | **只开通账号，不创建容器**（容器由用户登录控制台后在应用商店创建，单容器模型） |
| `SuspendAccount` | `zt` | `username` | 停容器＋`qk=paused`，禁止登录 |
| `UnsuspendAccount` | `jc` | `username` | `qk=active`，恢复登录与容器操作 |
| `TerminateAccount` | `tj` | `username` | 删除节点容器＋物理删除用户行（立即删除，见决策 D2） |
| `Renew` | `xf` | `username`，`setdate`=`nextduedate` | 更新 `datae`；若原 `qk=expired` 且新到期未过则自动恢复 `active` |
| `ChangePackage` | `bg` | `username`，`plan_id`=新的套餐 ID | 升降级后更新 `plan_id`（运行中容器配额不实时调整，见决策 D5） |
| `Status` | `ztcx` | `username` | 返回机器状态映射（见 §5.1） |
| `Sync` | `ztcx` | `username` | 拉取最新状态回填魔方本地，返回成功 |
| `On` | `start` | `username` | 启动用户容器 |
| `Off` | `stop` | `username` | 停止用户容器 |
| `Reboot` | `restart` | `username` | 重启用户容器 |
| `CrackPassword` | `czmm` | `username`，`password` | 重置密码，旧 `docker_token` 自动失效 |
| `UsageUpdate` | `sy` | 全量 hostID（P1） | 见 §5.2 |
| `CreateTicket` / `ReplyTicket` | — | — | 本期不实现（可选下一步） |

**CreateAccount 细节**：
1. 若 `$params['password']` 为空，按魔方默认规则生成随机密码并作为产品密码落库。
2. `nextduedate` 为 `0000-00-00`（永久周期）或空时，`dqtime` 传 `0`。
3. 幂等：MNBT 返回「账号已存在」视为错误返回（管理员可先 Sync 排查），不静默吞掉。

**TerminateAccount 细节**：直接调用 `gn=tj` 立即删除容器与账号行，与 MNBT `api/api.php` 的 `gn=tz`（删除主机）语义一致，不走 7 天软删（软删只服务于 MNBT 自身的到期 cron）。

### 3.6 ClientArea 前台自定义输出

```php
function mnbtdocker_ClientArea($params) {
    return [
        'console' => ['name' => '容器控制台'],
    ];
}
```

`ClientAreaOutput`（key=`console`）：
- **P0**：调 `gn=ztcx` 查询状态，模板渲染容器概要（状态/应用/端口/磁盘用量）＋「前往控制台」按钮（链接 `console_url`，用户需用 MNBT Docker 账号登录，双登录体系见风险 R2）。
- **P1**：调 `gn=dl` 获取一次性票据，直接 `302` 跳转控制台或内嵌 iframe 免登录使用（自动登录）。

### 3.7 ClientButton 前台自定义按钮

```php
function mnbtdocker_ClientButton($params) {
    return [
        'console' => ['place' => 'console', 'name' => '打开容器控制台'],
    ];
}
```

点击调用自定义方法 `mnbtdocker_console`：P0 返回 `console_url`（idcsmart 会以 URL 形式新窗口打开），P1 返回 `gn=dl` 生成的免登录跳转 URL。

### 3.8 AllowFunction 自定义方法

```php
function mnbtdocker_AllowFunction() {
    return [
        'client' => ['console'],
        'admin'  => [],
    ];
}
```

### 3.9 Chart 图表（P1，可选）

```php
function mnbtdocker_Chart() {
    return [
        'disk' => ['title' => '磁盘用量', 'select' => []],
    ];
}
function mnbtdocker_ChartData($params) {
    // 调 gn=sy 取 disk_usage/disk_max，返回 line 图数据
}
```

---

## 4. MNBT 侧：外部 API 扩展（api/docker.php）

### 4.1 现状

`api/docker.php` 已实现：
- `gn=cfif` 连接验证；
- `gn=kt` 开通账户（校验 API 开关、系统密钥、节点存在且启用、调用密钥、账号≥4 位/密码≥6 位、账号查重、套餐校验、bcrypt 落库、`docker.user.created` 钩子）。

> 与 `api/api.php`（主机）不同，Docker 外部 API **未校验节点 Docker 可达性**（原 PRD 决策 7 要求开通时校验，实现时未做，本期 `kt` 保持现状，见风险 R4）。

### 4.2 新增 gn 一览

| gn | 动作 | 对应魔方方法 | 对应现有 api.php 参照 |
|----|------|--------------|------------------------|
| `zt` | 暂停（停容器＋`qk=paused`） | SuspendAccount | `zt` |
| `jc` | 恢复（`qk=active`） | UnsuspendAccount | `jc` |
| `tj` | 删除（删容器＋删用户行） | TerminateAccount | `tz` |
| `xf` | 续费（更新 `datae`，必要时恢复） | Renew | `xf` |
| `bg` | 变更套餐（更新 `plan_id`） | ChangePackage | `zjmode`（参照思路） |
| `czmm` | 重置密码 | CrackPassword | `czmm` |
| `ztcx` | 状态查询（用户＋容器＋节点） | Status / Sync | 新 |
| `sy` | 用量查询（磁盘等） | UsageUpdate | 新 |
| `start` | 启动容器 | On | 新 |
| `stop` | 停止容器 | Off | 新 |
| `restart` | 重启容器 | Reboot | 新 |

> 各 gn 统一沿用现有鉴权头（`mn_bh/mn_key/mn_keye/mn_vs/username`）与响应风格（`success/code/msg`），并复用 `api_lifecycle_log` 写操作日志、触发 `mnbt_do_action` 钩子（`docker.user.paused/unpaused/deleted/renewed/package_changed/password_reset` 等）。

### 4.3 各 gn 规格

以下请求均默认携带通用鉴权参数（§4.2 说明），仅列业务参数。

#### 4.3.1 `zt` 暂停

| 参数 | 必填 | 说明 |
|------|------|------|
| `username` | 是 | Docker 账号 |

逻辑：查用户 → 存在且 `qk=active` → 若 `container_id/service_name` 非空则 `container_stop`（失败仅记日志，不阻断）→ `UPDATE qk='paused'`。
响应：`{"success":true,"code":200,"msg":"Docker 账户已暂停"}`
幂等：已 `paused`/`expired` 视为成功。
钩子：`docker.user.paused`

#### 4.3.2 `jc` 恢复

逻辑：`qk='paused'` → `UPDATE qk='active'`（`expired` 用户需走续费 `xf` 恢复）。
响应：`{"success":true,"code":200,"msg":"Docker 账户已恢复"}`
钩子：`docker.user.unpaused`

#### 4.3.3 `tj` 删除

逻辑：查用户 → 容器存在则 `container_del`（失败返回错误，不删行）→ `DELETE FROM MN_docker_user WHERE username=?`。
响应：`{"success":true,"code":200,"msg":"Docker 账户已删除"}`
钩子：`docker.user.deleted`
> 与 MNBT 7 天软删 cron 互不冲突：`tj` 是外部终止语义（立即删），cron 只处理到期未删除的 `expired/pruned` 账号。

#### 4.3.4 `xf` 续费

| 参数 | 必填 | 说明 |
|------|------|------|
| `setdate` | 是 | 到期日期 `Y-m-d`，`0`=永久 |

逻辑：更新 `datae`；若原 `qk=expired` 且新到期时间未过 → 同时 `qk='active'` 并尝试 `container_start` 恢复容器。
响应：`{"success":true,"code":200,"msg":"Docker 账户续费成功"}`
钩子：`docker.user.renewed`

#### 4.3.5 `bg` 变更套餐

| 参数 | 必填 | 说明 |
|------|------|------|
| `plan_id` | 是 | 新套餐 ID |

逻辑：校验套餐存在且上架 → `UPDATE plan_id`。
响应：`{"success":true,"code":200,"msg":"套餐变更成功"}`
钩子：`docker.user.package_changed`
> 说明：运行中容器配额（cpus/memory_limit 在 `create_app` 时固化）不实时调整；新配额在用户重装/重建容器时生效（决策 D5）。

#### 4.3.6 `czmm` 重置密码

| 参数 | 必填 | 说明 |
|------|------|------|
| `password` | 是 | 新密码（≥6 位） |

逻辑：`docker_auth_password_hash` 生成新 hash → `UPDATE password_hash`（旧 `docker_token` 因 session_hash 依赖密码 hash 自动失效）。
响应：`{"success":true,"code":200,"msg":"密码重置成功"}`
钩子：`docker.user.password_reset`

#### 4.3.7 `ztcx` 状态查询

逻辑：查用户 → 查节点 → 若容器存在，调 `bt_docker::installed_apps()` 前缀匹配 `service_name` 取容器详情。
响应：

```json
{
  "success": true, "code": 200, "msg": "ok",
  "data": {
    "user": {
      "username": "test", "qk": "active", "datae": "0000-00-00",
      "plan_id": 1, "container_status": "running",
      "disk_usage": 12345678, "disk_usage_at": "2026-08-07 10:00:00"
    },
    "container": {
      "service_name": "mnbt_test", "apptitle": "FRP 服务端",
      "status": "running", "port": ["29369", "26219"],
      "container_id": "b0a0d1bb7d53..."
    },
    "node": { "btip": "150.158.137.178", "ptl": "true" }
  }
}
```

用户不存在 → `code 100`；容器未创建 → `container` 为 `null`（魔方 `Status` 映射为 `waiting`）。

#### 4.3.8 `sy` 用量查询

| 参数 | 必填 | 说明 |
|------|------|------|
| `username` | 是 | Docker 账号（P0 单账号；P1 支持逗号分隔批量） |

逻辑：若容器运行，先刷新磁盘用量（`installed_apps` 取 path → `get_path_size`，写入 `disk_usage`）再返回；同时返回套餐 `disk_max`。
响应：

```json
{
  "success": true, "code": 200, "msg": "ok",
  "data": {
    "disk_usage": 12345678, "disk_max": 1048576,
    "unit": "bytes", "quota_reached": false
  }
}
```

#### 4.3.9 `start` / `stop` / `restart` 容器启停

逻辑：查用户 → 容器存在 → 调 `container_start/stop/restart` → 同步 `container_status`。
响应：`{"success":true,"code":200,"msg":"容器已启动/已停止/已重启"}`
约束：`qk != 'active'` 拒绝；未创建容器（`none`）返回错误提示。

---

## 5. 状态与用量映射

### 5.1 Status 映射表（魔方 `Status` 方法）

| MNBT 侧状态 | 魔方 status | des |
|-------------|-------------|-----|
| `qk=paused` / `qk=expired` | `suspend` | 已暂停 / 已到期 |
| `container_status=creating` | `waiting` | 容器创建中 |
| `container_status=running` | `on` | 运行中 |
| `container_status=stopped` | `off` | 已停止 |
| `container_status=none`（未创建容器） | `waiting` | 未创建容器（请在控制台创建） |
| 查询失败 / 用户不存在 | `unknown` | 未知状态 |
| `qk=pruned`（软删已清容器） | `suspend` | 容器已清理 |

### 5.2 UsageUpdate 数据模型（P1）

魔方 `UsageUpdate` 传入该模块全部 `hostID`。模块按 hostID 查 `shd_host.username`（对应 MNBT Docker 账号），逐个调 `gn=sy` 聚合返回：

```php
['status' => 'success', 'data' => [
    ['hostid' => 1, 'disk_usage' => 12345678, 'disk_max' => 1048576, 'unit' => 'bytes'],
    ['hostid' => 2, 'disk_usage' => 0,        'disk_max' => 0,        'unit' => 'bytes'],
]]
```

> ⚠️ 魔方官方文档未给出 `UsageUpdate` 的返回结构示例，P1 实现时需按魔方核心 `modules/servers/` 其他模块（如 noKVM）的实际返回格式对齐（见开放问题 Q2）。

---

> 后续章节（安全设计、安装部署与配置、里程碑与验收、决策点、风险与开放问题）见 [docker-idcsmart-appendix.md](./docker-idcsmart-appendix.md)。
