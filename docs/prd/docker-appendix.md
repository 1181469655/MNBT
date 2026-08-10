---
title: MNBT Docker 集成 PRD — 附录
description: Docker 集成 PRD 的附录：用户侧用量限制、目录结构汇总、已确认决策点、里程碑与验收、风险与开放问题
---

# MNBT Docker 集成 PRD — 附录

> 本文为 [Docker 集成 PRD](./docker.md)（§1-§8）的附录部分，含用户侧用量限制说明、目录结构汇总、已确认决策点、里程碑与验收、风险与开放问题。

---

**用户侧用量限制与应用商店落地参考（`docker/` 控制台内）：**

| 能力 | 宝塔 API | 说明 |
|------|----------|------|
| 应用商店列表 | `get_apps`（`bt_docker::app_list()`） | 289 个应用及参数定义，供用户选购 |
| 用量限制 | `create_app` 的 `cpus` / `memory_limit` | 安装时传入，取值不得超过 `MN_docker_user.cpu_max` / `mem_max` |
| 应用安装 | `create_app`（`bt_docker::app_create()`） | 返回"等待 1-5 分钟初始化"，**P0 直接实现，前端轮询 `get_cmd_log` 异步跟进安装进度** |
| **磁盘配额** | `get_installed_apps`（获取 `path`）+ `get_path_size`（获取路径大小） | 用户查看容器时实时采集磁盘用量，与 `MN_docker_plan.disk_max` 比对；用量 ≥ 90% 时前端标红告警 |

> **磁盘配额实现方案**：Docker 本身无磁盘容量限制端口，宝塔也未提供此功能。采用两个端点变通实现：
> 1. `bt_docker::installed_apps()` → 返回每个已安装应用的 `path`（容器安装目录）
> 2. `bt_docker::get_path_size($path)` → 调用宝塔文件 API `/files?action=get_path_size` 获取该路径磁盘占用大小
> 3. 用户每次查看"我的容器"时实时采集并存入 `MN_docker_user.disk_usage`，与套餐 `disk_max`（MB，0=不限制）比对展示
> 4. **磁盘超额自动停机**：当检测到磁盘用量超过配额且容器正在运行，自动调用 `container_stop` 停机，记录操作日志，前端展示红色告警提示

---

## 9. 目录结构汇总（新增）

```
MPHX/bt_docker.php                  # Docker API 封装
MPHX/docker.member.php              # Docker 用户独立认证
api/docker.php                      # 对外对接 API
docker/                             # Docker 用户控制台控制器
templates/default/docker/           # Docker scope 默认视图
templates/active_docker_theme       # Docker 当前主题
admin/docker.php                    # 管理员 Docker 管理页
admin/api/docker.php                # 管理员 Docker AJAX 模块（挂 admin/ajax.php）
install/install.sql                 # 追加三张 Docker 表
update/update_v183_docker.sql       # 增量升级 SQL
docs/prd/docker.md                  # 本文档
```

---

## 10. 已确认决策点

> 以下 7 项已由负责人拍板，实现照此执行。

| # | 决策 | 结论 |
|---|------|------|
| 1 | 管理员侧栏入口范围 | **仅 default 主题**，其余主题下期补 |
| 2 | Docker 支付 | **P0 不接支付**，只建 `MN_docker_order` 表（P1 再接） |
| 3 | 用户容器来源 | **用户自助选择** Docker 镜像 / 应用商店 |
| 4 | `create_app` 实现 | **P0 直接实现**，前端轮询 `get_cmd_log` 异步跟进安装 |
| 5 | 容器权限隔离 | 用户**仅允许查看自己的容器**（P0）；且**每账户固定一个容器**（单容器模型） |
| 6 | 到期删除策略 | 到期先**软删（停容器）7 天**，7 天后**物理删除容器数据** |
| 7 | 开通校验节点可达 | **必须验证**，节点 Docker 不可达则**拒绝开通**（`code=100`） |

---

## 11. 里程碑与验收

### M1 — 封装层与认证
- [ ] `bt_docker` 完成；`get_config`/`container_list` **真机联调通过**。
- [ ] `MN_docker_user` 三表 + `docker.member.php` 独立认证。
- **验收**：php 脚本调 `bt_docker` 返回真实容器 JSON；`docker/login.php` 可登录/登出。

### M2 — 用户 Docker 控制台（单容器 + 应用商店）
- [ ] `theme.php` docker scope + `templates/default/docker/` 页面。
- [ ] 我的容器（单容器）/ 镜像 / 应用商店；`create_app` **P0 直接实现**，前端轮询 `get_cmd_log` 异步跟进。
- [ ] **容器隔离**：每个账号仅装配一个容器，用户只看到自己的容器数据。
- **验收**：Docker 账号登录控制台，选购应用并异步安装成功；仅见本人容器。

### M3 — 管理端闭环（default 主题）
- [ ] 管理员开通/暂停/删除 Docker 用户（侧栏仅 default 主题）、套餐管理、节点容器总览。
- [ ] **到期软删**：到期停容器并预留 7 天，7 天后物理删除容器数据。
- **验收**：完整走"添加用户 → 登录控制台 → 选购安装 → 到期软删 → 7 天清容器"。

### M4 — 文档与升级
- [ ] `install.sql`、增量 SQL、`README.md` / `API.md` Docker 章节。

---

## 12. 风险与开放问题

1. **Docker API 签名/路由未官方确认** — M1 必须真机联调（见 §5.3）。
2. **`create_app` 耗时长（1-5 分钟）** — P0 已直接实现，前端轮询 `get_cmd_log` 异步跟进。
3. **面板未装 Docker** — `get_config` 先检测，未装引导安装（`MN_bt.btos` 区分系统）。
4. **theme.php 改动影响面** — 仅新增 docker scope 分支，需回归验证现有 user/admin scope 正常。
5. **侧栏硬编码入口** — 本期仅 default 主题，其余主题下期补（见决策 1）。
