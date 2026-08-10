---
title: 魔方财务 × Docker 对接
description: 将 MNBT Docker 容器作为标准服务器产品接入魔方财务（idcsmart）的完整指南
---

# 魔方财务 × Docker 对接

将梦奈宝塔 Docker 容器作为标准服务器产品接入魔方财务（idcsmart），实现"产品 → 自动开通 → 前台管理 → 到期续费/删除"的完整闭环。

> 详见 PRD：[Docker×魔方财务对接 PRD](../prd/docker-idcsmart.md)

---

## 架构

```
魔方财务（idcsmart）
  前台用户 ──下单/续费/删除/控制台──▶ shd_host / 订单体系
      │
      ▼
/modules/servers/mnbtdocker/mnbtdocker.php
  (server module：CreateAccount/Renew/Status/...)
      │ HTTPS POST（mn_key/mn_bh/mn_keye 鉴权）
      ▼
MNBT 外部 API：api/docker.php (gn=cfif/kt/zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart)
      │
      ▼
MN_docker_node（节点）/ MN_docker_user（账号）/ MN_docker_plan（套餐）
```

**单容器模型**：`CreateAccount` 只开通账号，容器由用户登录 MNBT Docker 控制台后在应用商店自行创建。

---

## 配置方式

### 方式一：服务器接口设置（简洁）

| 魔方字段 | 填写 |
|----------|------|
| 服务器 IP | MNBT IP地址 |
| 端口 | MNBT 端口 |
| 用户名 | 节点ID（自增ID）（`MN_docker_node.id`） |
| 密码 | 调用密钥（可留空）（`md5(ktmy.qmk)`） |
| Access Hash | 系统 API 密钥 |
| SSL | 按站点 |

### 方式二：产品配置选项（精确）

| key | 说明 |
|-----|------|
| `api_url` | `https://mnbt.example.com/api/docker.php` |
| `api_key` | 系统 API 密钥 |
| `node_id` | 节点ID |
| `call_key` | `md5(ktmy.qmk)` |
| `plan_id` | 默认套餐 ID（可选） |
| `console_url` | `https://mnbt.example.com/docker/login.php` |

---

## 模块方法映射

| 魔方操作 | MNBT gn | 说明 |
|----------|---------|------|
| 开通 | `kt` | 仅开通账号，不创建容器（容器由用户在控制台创建） |
| 暂停 | `zt` | 停容器 + qk=paused |
| 恢复 | `jc` | qk=active |
| 删除 | `tj` | 删容器 + 删用户行（立即） |
| 续费 | `xf` | 更新到期时间 |
| 升降级 | `bg` | 更新 plan_id（运行中容器配额不实时调整） |
| 改密 | `czmm` | 重置密码，旧 docker_token 自动失效 |
| 开机/关机/重启 | `start/stop/restart` | 容器启停 |
| 状态/同步 | `ztcx` | 查询容器状态 |

---

## 状态映射

| MNBT 状态 | 魔方 status | 说明 |
|-----------|-------------|------|
| `qk=paused` / `qk=expired` | `suspend` | 已暂停/已到期 |
| `container_status=creating` | `waiting` | 容器创建中 |
| `container_status=running` | `on` | 运行中 |
| `container_status=stopped` | `off` | 已停止 |
| `container_status=none` | `waiting` | 未创建容器 |

---

## 前台功能

- **容器控制台** 选项卡：容器状态 + 「前往控制台」按钮
- **打开容器控制台** 按钮：跳转 MNBT Docker 登录页

---

## 注意事项

- **双登录体系**：魔方登录态 ≠ MNBT `docker_token`，用户需用 Docker 账号二次登录控制台
- 用户名使用魔方 `username`
- 节点编号 `MN_docker_node.id` 与虚拟主机的 `MN_bt.btdh` 不同

> 完整部署说明见 `mf_modules/servers/mnbtdocker/README.md`
