---
title: Docker 容器服务
description: Docker 容器托管模块的架构、数据表、入口与到期软删流程
---

# Docker 容器服务（V1.83）

基于宝塔面板 Docker 模块的容器托管能力，独立于主机业务，单容器模型。

## 架构

- **独立认证**：`docker/` 控制台使用独立 `docker_token` cookie，与 `admin_token`/`user_token` 隔离
- **API 封装**：`MPHX/bt_docker.php`，GET 路由 `/btdocker/*`（签名入 query）+ POST 路由 `/mod/docker/com/*/stype`（签名入 body），与 `bt_api` 分离
- **单容器模型**：每个 Docker 账户最多创建一个容器，`MN_docker_user.service_name/container_id` 锚定
- **主题 scope**：`theme.php` 新增 `docker` scope，视图位于 `templates/{theme}/docker/`

## 数据表

| 表 | 说明 |
|------|------|
| `MN_docker_user` | Docker 用户（账号/密码/节点/套餐/容器锚点/到期软删字段） |
| `MN_docker_plan` | 套餐（CPU/内存配额/价格） |
| `MN_docker_order` | 订单（P0 预留，未接支付） |

升级 SQL：`update/update_v183_docker.sql`（全新安装已在 `install/install.sql` 内置）

## 入口

| 路径 | 说明 |
|------|------|
| `docker/login.php` | Docker 用户登录 |
| `docker/console.php` | 我的容器（状态/启停/安装日志轮询） |
| `docker/appstore.php` | 应用商店（get_apps + create_app 异步安装） |
| `docker/image.php` `docker/volume.php` `docker/compose.php` | 镜像/存储卷/Compose |
| `docker/ajax.php` | 控制台 AJAX（gn=login/logout/my_container/container_*/app_*/install_log） |
| `admin/docker.php` | 后台管理（用户/套餐/节点容器三 Tab） |
| `admin/api/docker.php` | 后台 AJAX（docker_user_* / docker_plan_* / docker_node_*） |
| `api/docker.php` | 对外开通 API（gn=kt，鉴权同 api/api.php） |
| `docker_cron.php` | 到期软删定时任务（建议每 30 分钟：`/docker_cron.php?my=API密钥`） |

## 到期软删流程

1. `active` 且到期 → `qk=expired`，`expired_at=到期时间`
2. `expired` 满 7 天 → cron 删除节点容器 → `qk=pruned`，`prune_due=当天`
3. `pruned` 满 7 天 → 物理删除用户行

## create_app 异步安装

应用安装为异步任务：提交后返回"等待 1-5 分钟初始化"，前端轮询 `install_log`（`get_cmd_log`）跟进进度，`console.php` 每 8 秒刷新容器状态。

## 鉴权说明

- 控制台 cookie：`docker_token`，`authcode` 加密，`session_hash = md5(user_id . password_hash . SYS_KEY)`，改密后旧 cookie 失效
- 密码：`password_hash`/`password_verify`（bcrypt）
- CSRF：复用 `mnbt_csrf_*`，AJAX 携带 `_csrf` 或 `X-CSRF-TOKEN`

---

详细 API 见 ../api/docker.md，产品设计见 ../prd/docker.md
