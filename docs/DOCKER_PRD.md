# MNBT Docker 集成 PRD（产品需求文档）

> 版本：v0.2（完全独立方案，待评审）
> 日期：2026-08-04
> 状态：待评审
> 关联文档：[API.md](../API.md)、[README.md](../README.md)、[app_plugins/PLUGIN_DEV.md](../app_plugins/PLUGIN_DEV.md)

## 1. 背景与目标

### 1.1 背景

MNBT 目前是一个基于宝塔面板 API 的虚拟主机分销系统，核心链路（管理员 → 宝塔节点 → 虚拟主机 `MN_zj`）成熟但**与站点强耦合**（`bt_api` 建站、`MN_zj` 存站点/FTP/SQL 字段、`MN_bs` 套餐绑定一键部署）。

现需新增 **Docker 容器管理**能力。经评审，**彻底放弃复用虚拟主机逻辑**——虚拟主机与 Docker 业务模型差异大（站点 vs 容器、域名/FTP/SQL vs 镜像/Compose/应用商店），强行复用会互相污染。

### 1.2 决策：完全独立，只复用管理员后台

| 组件 | 策略 |
|------|------|
| Docker 用户 | **独立表** `MN_docker_user`，独立认证 cookie，不碰 `MN_zj` |
| Docker 套餐 | **独立表** `MN_docker_plan`，不碰 `MN_bs` |
| Docker 订单 | **独立表** `MN_docker_order`，不碰 `MN_dd` |
| 用户端 UI | **独立 scope** `templates/docker/` + 独立 `docker/` 控制器，不碰 `user/` |
| 宝塔对接 | **独立类** `bt_docker`，不碰 `bt_api` |
| 对外 API | **独立入口** `api/docker.php` |
| **管理员后台** | ✅ **复用**：admin 登录、`admin/` 框架、admin scope 主题、操作日志 |

> 原则：**两个产品线互不可见、互不依赖**。未来任一方的改动不影响另一方。

### 1.3 范围

**核心产品模型：每个 Docker 账户仅对应一个容器（单容器交付）**。用户账户 = 一个容器实例，用户登录后在应用商店/镜像页自助选择并创建，创建走异步任务 + 轮询。

**本期（P0）**
- `bt_docker` 类：容器/镜像/存储卷/网络/Compose/应用商店查询与管理。
- `MN_docker_user / MN_docker_plan / MN_docker_order` 三张独立表 + 独立认证。
- 独立 Docker 用户控制台（登录/自助选择镜像/应用商店创建/我的容器）。
- **`create_app` P0 直接实现，异步任务 + 前端轮询拉取安装结果**。
- **容器权限隔离 P0 实现：用户仅能查看/操作自己的单容器**。
- **到期软删：到期后软删（停用）7 天，7 天后删除容器数据**。
- 管理员端：Docker 用户管理、节点容器总览、套餐管理。
- `api/docker.php` 对外对接 API（仅开通，校验节点 Docker 可达，不通则拒绝）。

**下期（P1，本期不做）**
- Docker 用户自助注册/购买下单 + 支付闭环。
- 容器实时用量采集与配额告警。
- 多容器账户扩展（当前锁定单容器）。

---

## 2. 架构总览

```
 Docker用户(独立控制台)                        第三方系统
        │                                         │
┌───────▼────────┐                        ┌───────▼──────┐
│ docker/ 控制器   │                        │ api/docker.php│
│ 独立cookie认证    │                        │  (对外API)     │
│ 独立scope模板    │                        └───────┬──────┘
└───────┬────────┘                                 │ 独立鉴权
        │ 统一调用                                  ▼
        └──────────────┬──────────────────────────────────┐
                       ▼                                   ▼
              ┌─────────────────────────┐        ┌───────────────────────┐
              │ MPHX/bt_docker.php(类)   │        │ MN_docker_user/plan/  │
              │ 容器/镜像/卷/网络/Compose │        │ order (独立表)         │
              └──────────┬──────────────┘        └──────────┬────────────┘
                         │ GET /btdocker/*  POST /mod/docker/com/*/stype
                         ▼
                  宝塔面板 Docker API
```

管理员侧：复用 `admin/` 框架（`admin/login.php` 登录、admin scope 主题）新增 Docker 管理页，走 `bt_docker` + 独立三表。

---

## 3. 数据库设计（全新，不碰现有表结构）

### 3.1 `MN_docker_user` — Docker 用户（单容器模型）

> 每个账户 = 一个容器。`qk` 记录生命周期状态，`datae` 到期后进入 7 天软删。

```sql
CREATE TABLE IF NOT EXISTS `MN_docker_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,           -- 登录名（唯一），同时作为容器命名前缀
  `password_hash` varchar(255) NOT NULL,     -- password_hash/bcrypt
  `email` varchar(128) DEFAULT NULL,
  `ssbt` varchar(250) NOT NULL,              -- 所属宝塔 MN_bt.btdh
  `data` varchar(50) NOT NULL,               -- 开通时间
  `datae` varchar(50) NOT NULL,              -- 到期时间（0000-00-00=永久）
  `qk` varchar(20) NOT NULL DEFAULT 'active',-- 状态：active/expired/paused
  `plan_id` int(11) DEFAULT NULL,            -- 套餐 MN_docker_plan.id
  `container_id` varchar(64) DEFAULT NULL,   -- 已创建容器 ID（单容器）
  `service_name` varchar(64) DEFAULT NULL,   -- create_app 的 service_name（唯一）
  `app_name` varchar(64) DEFAULT NULL,       -- 应用名（源自 get_apps）
  `container_spec` text,                     -- 用户选择的容器规格 JSON（镜像/版本/cpus/mem/appenv）
  `container_status` varchar(20) DEFAULT 'none', -- none/creating/running/stopped/failed
  `expired_at` varchar(50) DEFAULT NULL,     -- 软删开始时间（到期时间）
  `prune_due` varchar(50) DEFAULT NULL,      -- 7 天物理删除到期时间（空=未排程）
  `extra` text,                              -- JSON 扩展（compose_dir 等）
  `created_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_ssbt` (`ssbt`),
  KEY `idx_qk` (`qk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

### 3.2 `MN_docker_plan` — Docker 套餐（单容器配额）

```sql
CREATE TABLE IF NOT EXISTS `MN_docker_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,               -- 套餐名
  `jc` text,                                 -- 介绍
  `cpu_max` varchar(20) NOT NULL DEFAULT '1',-- CPU 核上限（create_app.cpus）
  `mem_max` varchar(20) NOT NULL DEFAULT '512',-- 内存 MB 上限（create_app.memory_limit）
  `jg` varchar(50) NOT NULL,                 -- 价格
  `qk` varchar(10) NOT NULL DEFAULT 'true',  -- 上架/下架
  `date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```
> 单容器模型下无 `container_max` 字段（每账户固定 1 容器）。

### 3.3 `MN_docker_order` — Docker 订单（预留，P0 可只建表不接支付）

```sql
CREATE TABLE IF NOT EXISTS `MN_docker_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,           -- MN_docker_user.username
  `plan_id` int(11) NOT NULL,
  `rmb` varchar(50) NOT NULL,
  `qk` varchar(10) NOT NULL DEFAULT 'false', -- 支付状态
  `date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

### 3.4 升级 SQL

- `install/install.sql` 追加三张表建表语句。
- 新增 `update/update_v183_docker.sql` 增量脚本（`CREATE TABLE IF NOT EXISTS`）。

---

## 4. Docker 用户认证（独立于 member.php）

### 4.1 方案

仿照 `user_info` 插件的思路但落在核心层，**不改** `MPHX/member.php`：

- Cookie 名：`docker_token`（与 `admin_token`/`user_token` 完全独立）。
- 加密：`authcode($user_id . "\t" . $session_hash, 'ENCODE', SYS_KEY)`。
- `session_hash = md5($user_id . $password_hash . SYS_KEY)`（改密后旧 cookie 自动失效）。
- 密码：`password_hash` / `password_verify`（bcrypt）。

### 4.2 认证函数（新增 `MPHX/docker.member.php`）

```php
function docker_auth_login($user_id, $password_hash);
function docker_auth_logout();
function docker_auth_current();      // 查 MN_docker_user，校验状态/到期
function docker_auth_require();      // 未登录跳转 docker/login.php
```

### 4.3 登录流程

- `docker/login.php`：登录表单 → `POST docker/ajax.php`（`gn=login`）→ 校验 → 写 cookie → 跳 `docker/console.php`。
- 到期检查：登录时 `datae` 已过且非永久 → 拒绝并提示（沿用现有到期语义）。
- 暂停检查：`qk=expired` → 提示到期；`qk=paused` → 提示已暂停。

---

## 5. `MPHX/bt_docker.php`（宝塔 Docker API 封装）

### 5.1 定位

独立类，**不得**修改 `MPHX/bt_api.php`。两者共享签名算法但不继承。

### 5.2 类原型

```php
class bt_docker
{
    public $BT_PANEL;
    public $BT_KEY;

    public function __construct($bt_panel = null, $bt_key = null);

    private function GetKeyData();       // md5(time.md5(key)) + time（自持，不改 bt_api）
    private function HttpGet($path, $params = [], $timeout = 30);  // 签名入 query + cookie
    private function HttpPost($path, $params = [], $timeout = 60); // 签名入 body + cookie

    // —— 安装与配置（GET /btdocker/setup/）——
    public function get_config();
    public function install_docker_program();
    public function get_registry_mirrors();
    public function set_registry_mirrors($mirrors);

    // —— 容器（GET /btdocker/container/）——
    public function container_list();
    public function container_start($id, $name);
    public function container_stop($id, $name);
    public function container_restart($id, $name);
    public function container_prune();
    public function container_log($id, $name, $lines = 500);

    // —— 镜像（GET /btdocker/image/）——
    public function image_list();
    public function image_search($keyword);
    public function image_prune();

    // —— 存储卷（GET /btdocker/volume/）——
    public function volume_list();
    public function volume_add($name, $driver = 'local');
    public function volume_prune();

    // —— 网络（GET /btdocker/network/）——
    public function network_list();
    public function network_create($name, $driver = 'bridge');
    public function network_prune();

    // —— 仓库（GET /btdocker/registry/）——
    public function registry_list();

    // —— Compose/项目（GET /btdocker/compose|project/）——
    public function template_list();
    public function project_list();

    // —— 应用商店（POST /mod/docker/com/）——
    public function app_list();                 // get_apps：应用列表及参数定义
    public function app_create($params);        // create_app：P0 直接实现（异步任务封装）
    public function app_dependence($app);       // get_dependence_apps
    public function get_cmd_log();              // 容器执行日志（轮询安装进度用）
}
```

### 5.3 签名与请求方式（关键风险点）

宝塔官方文档仅给路由，**未标注签名/cookie**，且注明接口不稳定。假设沿用面板统一签名：

- `GET /btdocker/*`：签名拼入 query string + cookie。
- `POST /mod/docker/com/*/stype`：签名入 body + cookie。

> ⚠️ **M1 首要任务：真机联调验证**（先 `get_config`、`container_list`）。如有出入，仅改 `bt_docker` 内部 transport，不影响上层。

---

## 6. 前端：独立 Docker scope

### 6.1 `theme.php` 增加 `docker` scope

`theme.php` 当前只认 `user|admin`，新增 `docker` scope（**唯一核心文件改动**，不影响现有 5 主题）：

- `mnbt_theme_resolve($view, 'docker')` → `templates/{theme}/docker/{view}.php`，回退 `templates/default/docker/`。
- `mnbt_theme_name('docker')` → 读 `active_docker_theme` / `conf['docker_theme']`。
- `mnbt_render($view, $vars, $exit, 'docker')` 与 `mnbt_theme_url('/assets/..','docker')` 支持 docker scope。

### 6.2 目录约定

```
templates/
├── default/docker/            # 默认 Docker scope 视图（回退基准）
│   ├── login.php
│   ├── console.php            # 容器控制台
│   ├── image.php              # 镜像管理
│   ├── volume.php             # 存储卷
│   ├── compose.php            # Compose/项目
│   ├── appstore.php           # 应用商店
│   └── assets/
├── active_docker_theme        # 当前 Docker 主题名
└── layui|jqueryui|bootstrapui|tdesign   # —— 无需改动 ——
```

### 6.3 Docker 控制器

```
docker/                        # 新顶层控制器
├── login.php                  # 登录（独立 docker_token 认证）
├── index.php                  # scope 外壳
├── console.php                # 我的容器
├── image.php
├── volume.php
├── compose.php
├── appstore.php
└── ajax.php                   # gn 分发（docker_* 操作）
```

### 6.4 Docker 账号生命周期（到期软删）

`MN_docker_user.qk` 状态机 + 7 天软删（决策 6）：

| 状态 | 触发 | 行为 |
|------|------|------|
| `active` | 开通 | 用户可登录、可管理/安装容器 |
| `expired` | `datae` 到期 | `container_stop` 停容器；写 `expired_at`；进入 7 天软删期，`qk=expired`，禁止登录/新建 |
| 7 天后 | cron 扫描 `prune_due` 到期 | `container_prune` 物理清理容器数据 + `MN_docker_user` 置 `qk=pruned` |

- 7 天软删期内管理员可**续期恢复**：`datae` 延后 + `qk=active`。
- `prune_due = expired_at + 7 天`，由定时任务（cron）扫描执行。
- 永久账号（`datae=0000-00-00`）不进入软删流程。

---

## 7. 管理员端（复用 admin 框架）

管理员侧栏硬编码新增分组 **Docker 管理**（系统级，不属于插件菜单）：

| 页面 | 说明 |
|------|------|
| `admin/docker.php` | Docker 管理入口页（Tab：用户 / 套餐 / 节点容器总览） |
| 用户 Tab | `MN_docker_user` 列表：添加/编辑/暂停/删除/重置密码 |
| 套餐 Tab | `MN_docker_plan` 列表：增删改、上架/下架 |
| 节点 Tab | 选节点 → `bt_docker::container_list` 容器总览 |

复用点：admin 登录（`admin/login.php`）、admin scope 主题（`mnbt_admin_render`）、`MN_log` 操作日志、`admin/api/` 模块分发。

> 侧栏"Docker 管理"是**系统级硬编码项**，本期仅加 default 主题的 `admin/index.php`；layui/jqueryui/bootstrapui/tdesign 下期补（见决策 1）。

---

## 8. 对外对接 API：`api/docker.php`

### 8.1 定位

`api/docker.php` **仅提供开通（provision）逻辑**，与 `api/api.php` 的 `gn=kt`（开通主机）对称。开通只创建账号记录（`MN_docker_user`），**不创建任何容器**；容器创建由用户登录 Docker 控制台后在应用商店 / 镜像界面引导完成（内部走 `bt_docker::app_create`，用量限制经 create_app 的 `cpus` / `memory_limit` 生效）。

### 8.2 鉴权（独立于 api/api.php）

| 参数 | 来源 |
|------|------|
| `mn_bh` | `MN_bt.btdh` |
| `mn_key` | = `MN_config.api`（系统密钥） |
| `mn_keye` | = `md5(MN_bt.ktmy . MN_bt.qmk)`（宝塔调用密钥） |
| `mn_vs` | 协议版本号 >= 15 |
| `username` | 待开通的 Docker 用户名 |

> 不复用 `MN_zj`，只认 `MN_docker_user`。

### 8.3 接口：`gn=kt` 开通 Docker 用户

传入/传出结构参考 `api/api.php` 的 `gn=kt`。

**请求（POST /api/docker.php?gn=kt）：**

| 参数 | 必填 | 说明 |
|------|------|------|
| `mn_bh` | 是 | 宝塔节点编号 |
| `mn_key` | 是 | 系统密钥 |
| `mn_keye` | 是 | 宝塔调用密钥 = `md5(MN_bt.ktmy . MN_bt.qmk)` |
| `mn_vs` | 是 | 协议版本号 >= 15 |
| `username` | 是 | Docker 用户名（>= 6 位，唯一） |
| `password` | 是 | 密码（>= 6 位） |
| `plan_id` | 否 | 套餐 ID（来自 `MN_docker_plan`）；不传则用默认配额 |
| `dqtime` | 否 | 到期日期 `Y-m-d`，`0` = 永久 |

**处理逻辑（对照 api.php 的 kt）：**

1. `MN_config.apiqk` 关闭则拒绝。
2. 校验参数完整 + `mn_key` 匹配 + 节点存在且启用 + `mn_keye` 匹配。
3. 查重 `MN_docker_user.username`，重复则拒绝。
4. 账号/密码长度 >= 6。
5. 按 `plan_id` 解析配额（`cpu_max` / `mem_max`）落库 `MN_docker_user`（单容器模型，无 `container_max`）。
6. **校验节点 Docker 可达**（`bt_docker->get_config()`）；不通则**拒绝开通**（`code=100`，"节点 Docker 不可用"）。
7. 写 `MN_log`，触发 `host.created` 钩子。
8. **不创建容器**（交给用户登录后引导创建）。

**响应（成功）：**

```json
{ "success": true, "code": 200, "msg": "Docker 账号开通成功！" }
```

**响应（失败，对齐 api/api.php 风格）：**

```json
{ "success": false, "code": 100, "msg": "错误！该账号已存在！" }
```

### 8.4 不包含（P0）

容器/镜像/存储卷/网络/Compose 的管理与查询、应用商店列表与安装、用户登录令牌等，**均不在 `api/docker.php` 内**，归属用户控制台 `docker/` + `bt_docker`。`api/docker.php` 只做开通一件事。

---

**用户侧用量限制与应用商店落地参考（`docker/` 控制台内）：**

| 能力 | 宝塔 API | 说明 |
|------|----------|------|
| 应用商店列表 | `get_apps`（`bt_docker::app_list()`） | 289 个应用及参数定义，供用户选购 |
| 用量限制 | `create_app` 的 `cpus` / `memory_limit` | 安装时传入，取值不得超过 `MN_docker_user.cpu_max` / `mem_max` |
| 应用安装 | `create_app`（`bt_docker::app_create()`） | 返回"等待 1-5 分钟初始化"，**P0 直接实现，前端轮询 `get_cmd_log` 异步跟进安装进度** |

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
docs/DOCKER_PRD.md                  # 本文档
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