---
title: 外部运维 API（魔方财务对接）
description: 魔方财务（idcsmart）server module 调用的 Docker 外部运维接口（zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart）
---

# 外部运维 API（魔方财务对接）

以下 `gn` 供魔方财务（idcsmart）server module 调用，鉴权方式与 [Docker API §2.1 鉴权](./docker.md#21-鉴权) 完全一致。

> 所有请求均携带：`mn_bh` / `mn_key` / `mn_keye` / `mn_vs=15` / `username`。

## 暂停 Docker 账户（gn=zt）

```
POST api/docker.php?gn=zt
```

停容器（若存在）＋ `qk=paused`，禁止登录。已暂停/到期状态视为成功。  
响应：`{"success":true,"code":200,"msg":"Docker 账户已暂停"}`

## 恢复 Docker 账户（gn=jc）

```
POST api/docker.php?gn=jc
```

`paused → active`，恢复登录。仅 `paused` 状态可恢复（`expired` 走续费 `xf`）。  
响应：`{"success":true,"code":200,"msg":"Docker 账户已恢复"}`

## 删除 Docker 账户（gn=tj）

```
POST api/docker.php?gn=tj
```

先删除节点容器（失败则拒绝删行），再物理删除用户行。与 7 天软删 cron 互不冲突。  
响应：`{"success":true,"code":200,"msg":"Docker 账户已删除"}`

## 续费（gn=xf）

```
POST api/docker.php?gn=xf
```

| 参数 | 说明 |
|------|------|
| `setdate` | 到期日期 `Y-m-d`，`0`=永久 |

若原 `expired` 且新到期未过，同时恢复 `active` 并尝试启动容器。  
响应：`{"success":true,"code":200,"msg":"Docker 账户续费成功"}`

## 变更套餐（gn=bg）

```
POST api/docker.php?gn=bg
```

| 参数 | 说明 |
|------|------|
| `plan_id` | 新套餐 ID（`MN_docker_plan.id`） |

仅更新 `plan_id` 记录，运行中容器配额不实时调整（重装/重建容器时生效）。  
响应：`{"success":true,"code":200,"msg":"套餐变更成功"}`

## 重置密码（gn=czmm）

```
POST api/docker.php?gn=czmm
```

| 参数 | 说明 |
|------|------|
| `password` | 新密码（≥ 6 位） |

旧 `docker_token` 因 session_hash 依赖密码 hash 自动失效。  
响应：`{"success":true,"code":200,"msg":"密码重置成功"}`

## 状态查询（gn=ztcx）

```
POST api/docker.php?gn=ztcx
```

返回用户信息、容器详情（调 `installed_apps` 前缀匹配 `service_name`）、节点信息。容器未创建时 `container` 为 `null`。

响应：

```json
{
  "success": true, "code": 200, "msg": "ok",
  "data": {
    "user": { "username": "test", "qk": "active", "datae": "0000-00-00",
              "plan_id": 1, "container_status": "running",
              "service_name": "mnbt_test", "disk_usage": 12345678 },
    "container": { "service_name": "mnbt_test", "apptitle": "FRP 服务端",
                   "status": "running", "port": ["29369"],
                   "container_id": "b0a0d1bb...", "appinfo": [...] },
    "node": { "btip": "150.158.137.178", "ptl": "true" }
  }
}
```

## 用量查询（gn=sy）

```
POST api/docker.php?gn=sy
```

容器运行中时实时刷新磁盘用量（`installed_apps` → `get_path_size`）。

```json
{
  "success": true, "code": 200, "msg": "ok",
  "data": {
    "disk_usage": 12345678, "disk_max": 1048576,
    "disk_max_mb": 1024, "unit": "bytes", "quota_reached": false
  }
}
```

## 容器启停

```
POST api/docker.php?gn=start   # 启动
POST api/docker.php?gn=stop    # 停止
POST api/docker.php?gn=restart # 重启
```

要求 `qk=active` 且已创建容器。  
响应：`{"success":true,"code":200,"msg":"容器已启动/已停止/已重启"}`

---

## 相关文档

- [Docker API](./docker.md) —— 外部开通、用户控制台、后台管理、容器生命周期
- 产品设计：[../prd/docker.md](../prd/docker.md)
- 测试：[../prd/docker-test.md](../prd/docker-test.md)
