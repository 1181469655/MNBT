---
title: MNBT Docker × 魔方财务（idcsmart）对接 PRD — 附录
description: Docker×魔方财务 PRD 的附录：安全设计、安装部署与配置、里程碑与验收、决策点、风险与开放问题，及部署配置速览
---

# MNBT Docker × 魔方财务（idcsmart）对接 PRD — 附录

> 本文为 [Docker×魔方财务 PRD](./docker-idcsmart.md)（§1-§5）的附录部分，含安全设计、安装部署与配置、里程碑与验收、决策点、风险与开放问题，以及部署配置速览。

---

## 6. 安全设计

| 项 | 方案 |
|----|------|
| 传输 | 强制 HTTPS 建议项；`api_url` 仅接受 `http(s)://`，模块校验协议头 |
| 鉴权 | 沿用 MNBT 三层鉴权：系统密钥（`mn_key`）＋ 节点调用密钥 md5（`mn_keye`）＋ 节点启用校验 |
| 密钥存储 | 魔方侧 `api_key/call_key` 用 `password` 类型配置项存储；不写入日志、不回显 |
| 日志脱敏 | MNBT `api_lifecycle_log` 仅记录用户名与动作，不记录密码/密钥 |
| 越权隔离 | MNBT `ztcx/sy/start/stop/restart` 一律按 `username` 定位用户，容器操作以 `MN_docker_user.service_name` 为锚点，无越权路径 |
| 自动登录（P1） | `gn=dl` 一次性票据：`md5(uid . timestamp . SYS_KEY)`，5 分钟有效、单次使用、不落明文密码；`docker/login.php?token=` 兑换会话后立即作废 |
| 请求超时 | 模块侧 curl 超时（同步 30s / 查询 60s），失败即返回错误，不静默 |

---

## 7. 安装部署与配置

### 7.1 安装模块

1. 将 `modules/servers/mnbtdocker/` 上传至魔方财务根目录。
2. 清魔方缓存（后台或 `runtime` 缓存目录）。
3. 后台「产品 → 服务器」确认模块出现在模块下拉中。

### 7.2 服务器接口设置（可选，兜底用）

后台「产品 → 服务器」添加服务器：
- 服务器 IP/域名：MNBT 域名
- 端口：MNBT 站点端口
- 用户名：节点编号（`MN_docker_node.id`）
- 密码：调用密钥 md5
- Access Hash：系统 API 密钥
- SSL：按站点是否 HTTPS 勾选

### 7.3 产品关联模块

后台「产品 → 添加产品」：
- 类型：**服务器产品**
- 模块：**MNBT Docker**
- 配置选项：按需填写 `api_url`/`api_key`/`node_id`/`call_key`/`plan_id`/`console_url`（若 §7.2 已填服务器兜底字段，可只填 `plan_id`）
- 可配置选项：如「套餐 ID」字段映射到 `plan_id` key，则按用户所选套餐自动传参

### 7.4 开通闭环验证流程

1. 前台下单购买该产品 → 支付 → 自动开通 → 魔方后台该产品「操作」显示成功。
2. 用户前台「产品详情 → 容器控制台」标签页显示容器概要。
3. 后台执行 暂停/恢复/删除/续费/升降级/改密 → 对应 MNBT `MN_docker_user` 状态正确变化，操作日志可见。
4. 用户在前台点「开机/关机/重启」→ MNBT 容器状态同步变化。

---

## 8. 里程碑与验收

### M1 — MNBT API 扩展（前置依赖）
- [ ] `api/docker.php` 新增 `zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart`。
- [ ] 各 gn 写操作日志、触发钩子；与 `api/api.php` 现有风格一致。
- **验收**：curl 逐一验证 12 个 gn 的正常/异常用例；`MN_docker_user` 状态流转正确。

### M2 — 魔方模块骨架＋生命周期闭环
- [ ] `mnbtdocker.php`：MetaData/ConfigOptions/参数解析。
- [ ] CreateAccount/SuspendAccount/UnsuspendAccount/TerminateAccount/Renew/ChangePackage。
- **验收**：魔方后台可下单开通、暂停、恢复、续费、删除、升降级，MNBT 侧状态与日志全部正确。

### M3 — 状态/前台集成
- [ ] Status/Sync；ClientArea ＋ ClientAreaOutput（P0 外链控制台）。
- [ ] On/Off/Reboot、CrackPassword。
- **验收**：前台产品页显示容器状态与控制台入口；开机/关机/重启/改密全链路可用。

### M4 — 文档与收尾
- [ ] 模块 README、API.md 增加魔方对接章节、DOCKER_TEST.md 增加联调用例。
- [ ] （P1）自动登录 `gn=dl`、UsageUpdate、Chart。

---

## 9. 决策点（待评审）

| # | 决策 | 建议 |
|---|------|------|
| D1 | 模块命名 | `mnbtdocker`（单单词，符合魔方命名约束），最终以负责人确认为准 |
| D2 | `TerminateAccount` 语义 | **立即删除容器与账号行**（推荐，与 api.php `tz` 一致）；不走 7 天软删 |
| D3 | 连接参数配置方式 | **config option 优先＋服务器字段兜底**（§3.4），两种用法并存 |
| D4 | 前台控制台呈现 | **P0 外链跳转**；P1 自动登录票据＋iframe（需评审票据安全） |
| D5 | 套餐升降级是否调整运行中容器 | **P0 仅更新 `plan_id` 记录**，配额在重装/重建容器时生效 |
| D6 | `ChangePackage` 触发 | 魔方可配置选项「套餐 ID」key 与 `plan_id` 绑定，升降级自动调 `gn=bg`；无映射则不调用 |

---

## 10. 风险与开放问题

| # | 风险/问题 | 影响 | 应对 |
|---|-----------|------|------|
| R1 | MNBT `api/docker.php` 现状仅 `cfif/kt`，生命周期动作全部依赖本期扩展 | 阻塞 M2 | 将 M1 设为前置里程碑，先交付后联调 |
| R2 | 双登录体系：魔方登录态 ≠ MNBT `docker_token` | 用户需二次登录控制台 | P0 外链说明；P1 自动登录票据方案 |
| R3 | `create_app` 异步（1-5 分钟） | 魔方 `Status` 短时间显示 waiting | 状态映射已覆盖 `creating→waiting` |
| R4 | 原 PRD 决策 7（开通校验节点 Docker 可达）当前 `kt` 未实现 | 开通时节点 Docker 故障仍会成功 | 本期在 `kt` 补充 `get_config` 校验（需真机验证）或在决策中降级 |
| R5 | 宝塔接口字段不稳定（`container_list` 端口为空、`get_cmd_log` 仅返回布尔等已知问题） | 状态/用量口径偏差 | 以 `installed_apps` 为主要数据源，沿用 DOCKER_TEST.md 的兼容处理 |
| Q1 | 魔方 server module 的 `Renew`/`ChangePackage` 具体传参（如 `nextduedate` 格式、可配置选项变更回传） | 影响实现细节 | M2 联调时以魔方实际 `$params` 打印为准 |
| Q2 | `UsageUpdate` 返回结构魔方文档未给出示例 | 影响 P1 | 参照 noKVM 模块源码对齐，P0 不做 |
| Q3 | `gn=dl` 票据方案与 `docker/login.php` 改造涉及 MNBT 控制台 | 影响 P1 与安全 | P1 单独评审，本期不动登录页 |

---

## 11. 部署配置速览

> 摘要自魔方模块自带文档 `mf_modules/servers/mnbtdocker/README.md`，完整部署说明见该文件。

### 前置条件

- **魔方财务**：已安装并正常运行
- **MNBT V1.83+**：Docker 模块已完成 M1 API 扩展（`api/docker.php` 支持 `gn=kt/zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart`）
- MNBT 后台已配置 Docker 节点（`MN_docker_node`）、套餐（`MN_docker_plan`）

### 安装模块

1. 将 `mf_modules/servers/mnbtdocker/` 整个目录复制到魔方财务的 `/modules/servers/mnbtdocker/`
2. 清魔方缓存（后台清或删除 `runtime/cache/`）
3. 后台「产品 → 服务器」→ 模块下拉应出现「梦奈宝塔Docker对接插件」

### 服务器字段（方式一：服务器接口设置，推荐）

| 字段 | 填写 |
|------|------|
| 服务器 IP/域名 | MNBT 站点域名 |
| 端口 | MNBT 站点端口（80/443） |
| 用户名 | 节点编号（`MN_docker_node.id`） |
| 密码 | 调用密钥 md5（`md5(ktmy.qmk)`） |
| Access Hash | 系统 API 密钥（`$conf['api']`） |
| SSL | 按站点是否 HTTPS 勾选 |

产品关联模块后只需填写可选配置（如 `plan_id`），其余从服务器字段自动解析。

### 产品关联要点

1. 后台「产品 → 添加产品」→ 类型：**服务器产品** → 模块：**梦奈宝塔Docker对接插件**
2. 可配置选项添加「套餐 ID」字段，映射 key 为 `plan_id`（可选，用于升降级）
3. 上架后即可前台购买

> 完整部署说明见魔方模块自带文档 `mf_modules/servers/mnbtdocker/README.md`（保持纯文本路径）。
