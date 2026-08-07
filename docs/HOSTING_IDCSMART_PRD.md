# MNBT 虚拟主机 × 魔方财务（idcsmart）server module 对接插件 PRD

> 版本：v1.0（M1-M3 已交付，待联调验证）
> 日期：2026-08-07
> 状态：已实现
> 关联文档：[DOCKER_IDCSMART_PRD.md](./DOCKER_IDCSMART_PRD.md)、[API.md](../API.md)、[api/api.php](../api/api.php) 实现、魔方财务《[服务器模块（server module）开发文档](https://docs.idcsmart.com/docs/%E8%B4%A2%E5%8A%A1%E7%B3%BB%E7%BB%9F%E5%BC%80%E5%8F%91%E6%96%87%E6%A1%A3/%E6%9C%8D%E5%8A%A1%E5%99%A8%E6%A8%A1%E5%9D%97%EF%BC%88server%20module%EF%BC%89)》

---

## 1. 背景与目标

### 1.1 背景

MNBT（梦奈宝塔主机系统，V1.83）的核心产品线是**宝塔虚拟主机分销**：通过 `api/api.php` 外部 API 完成开通（`kt`）、暂停（`zt`）、续费（`xf`）、恢复（`jc`）、删除（`tz`）、重置密码（`czmm`）、修改配额（`zjmode`）等生命周期动作，数据落库 `MN_bt`（节点）/ `MN_zj`（主机）。

此前已完成 **Docker 容器**与魔方财务的对接插件（`mnbtdocker`），验证了 server module 对接模式可行。现需将 **虚拟主机**同样接入魔方财务，作为标准服务器产品对外销售。

### 1.2 与 Docker 对接的核心差异

| 维度 | Docker 对接（已完成） | 虚拟主机对接（本 PRD） |
|------|----------------------|------------------------|
| 业务模型 | 容器（单容器/应用商店） | 宝塔站点（FTP + MySQL + 配额） |
| 开通语义 | 仅建账号，容器后建 | **开通即建站**（`webkt`） |
| 配额管理 | create_app 时 cpus/mem | **空间/数据库/流量三级配额**（hxa/hxb/llmax） |
| 状态查询 | 需新增 `ztcx` | **需新增 `ztcx`/`sy`**（现无状态查询 gn） |
| 容器启停 | start/stop/restart | 站点启停（siteqt），魔方 On/Off 可选 |
| 对外 API | `api/docker.php`（M1 扩展） | `api/api.php`（生命周期已成熟，仅补查询） |
| 节点标识 | `MN_docker_node.id` | **`MN_bt.btdh`**（宝塔开通代号） |

### 1.3 范围

| 期次 | 内容 |
|------|------|
| **P0（本期）** | 魔方侧：`mnbthost` 模块骨架 + 生命周期闭环（CreateAccount/Suspend/Unsuspend/Terminate/Renew/ChangePackage/CrackPassword）+ **站点启停（On/Off）** + TestLink + ClientArea（登录信息+配额用量展示）。MNBT 侧：`api/api.php` 新增 `ztcx`（状态+配额用量查询）、`start`/`stop`（站点启停） |
| **P1（下期）** | `sy` 用量单独接口 + UsageUpdate 批量定时上报 + 前台图表（空间/流量占用） |

---

## 2. 总体架构

### 2.1 架构图

```
┌─────────────────────────────────────────────────────────────┐
│                    魔方财务（idcsmart）                        │
│  前台用户 ──下单/续费/删除/控制台──▶ shd_host / 订单体系          │
│                                       │                      │
│                                       ▼                      │
│         /modules/servers/mnbthost/mnbthost.php                │
│         (server module：CreateAccount/Renew/Status/...)       │
└──────────────────────────────────┬──────────────────────────┘
                                   │ HTTPS POST（mn_bh/mn_key/mn_keye 鉴权）
                                   ▼
┌─────────────────────────────────────────────────────────────┐
│             MNBT 外部 API：api/api.php                        │
│  cfif/kt/zt/xf/jc/tz/czmm/zjmode（已成熟）                     │
│  + ztcx（本期新增：状态 + 配额用量）                             │
└──────────────────────────────────┬──────────────────────────┘
                                   │
              ┌────────────────────┼────────────────────┐
              ▼                    ▼                    ▼
        MN_bt（节点）       MN_zj（主机/站点）      MPHX/bt_api.php
      btdh/btmy/ktmy/qmk   hxa/hxb/llmax 配额      （宝塔站点 API）
```

**核心模型**：虚拟主机 = 宝塔站点。`CreateAccount` 调 `kt` **立即建站**（含 FTP、数据库），配额（网站空间 hxa / 数据库空间 hxb / 流量 llmax）随产品配置选项落库，魔方侧负责计费与生命周期管理。

### 2.2 模块命名

| 项 | 值 |
|----|----|
| 模块目录 | `/modules/servers/mnbthost/` |
| 主文件 | `mnbthost.php` |
| 显示名 | 梦奈宝塔虚拟主机 |

> ⚠️ 不使用 `mnbt_host`（含下划线，不符合魔方"仅小写字母和数字"命名约束）。

---

## 3. 魔方侧：server module 设计

### 3.1 目录结构

```
modules/servers/mnbthost/
├── mnbthost.php          # 模块主文件（全部方法）
├── templates/
│   └── console.html      # ClientAreaOutput 登录信息页（P0）
└── README.md             # 安装/配置说明
```

### 3.2 MetaData

```php
function mnbthost_MetaData() {
    return [
        'DisplayName' => '梦奈宝塔虚拟主机',
        'APIVersion'  => '1.1',
        'HelpDoc'     => 'https://github.com/MNBT/API.md', // 指向 MNBT API 文档在线地址
    ];
}
```

### 3.3 ConfigOptions（产品级配额配置）

> 与 noKVM 相同模式：**连接信息全部走服务器字段**（§3.4），ConfigOptions 只承载产品配额与展示项，开通时通过 `$params['configoptions']` 读取。

| # | key | 类型 | 必填 | 默认 | 说明 |
|---|-----|------|------|------|------|
| 1 | `webdx` | text | 否 | `500` | 网站空间（MB），即 `kt` 的 `webdx` |
| 2 | `sqldx` | text | 否 | `100` | 数据库空间（MB），即 `kt` 的 `sqldx` |
| 3 | `sizemax` | text | 否 | `0` | 流量上限（MB，0=不限），即 `kt` 的 `sizemax` |
| 4 | `ymbds` | text | 否 | `1` | 域名绑定上限（个），即 `kt` 的 `ymbds` |
| 5 | `console_url` | text | 否 | 空 | 虚拟主机用户控制台地址（如 `https://mnbt.example.com/user/login.php`） |

### 3.4 服务器字段映射（连接信息）

| 魔方服务器字段 | 用途 | MNBT 侧 |
|----------------|------|---------|
| `server_ip` / `server_host` | API 主机 | MNBT 站点域名 |
| `port` | API 端口 | 80/443 |
| `secure` | HTTPS | 按站点是否 HTTPS |
| `server_username` | 节点编号 | **`MN_bt.btdh`**（⚠️ 注意：虚拟主机节点标识是 `btdh` 开通代号，不是自增 `id`，与 Docker 的 `MN_docker_node.id` 不同） |
| `server_password` | 调用密钥 | `md5(节点ktmy . 节点qmk)`，空则自动兜底 `md5('')` |
| `accesshash` | 系统 API 密钥 | `$conf['api']` |

API 地址 = `{scheme}://{host}[:port]/api/api.php`，由模块自动拼装，无需单独配置。

### 3.5 内置方法实现规格

通用约定（与 Docker 模块一致）：
- `POST {api_url}?gn=<动作>`，表单携带 `mn_bh`（btdh）、`mn_key`、`mn_keye`、`mn_vs=15`、`username`。
- 成功判定：MNBT 返回 `code==200` 且 `success==true`（对齐 `api/api.php` 现有 `api_json_exit` 风格）。
- 失败返回 `['status'=>'error','msg'=>...]`；成功返回 `'success'` 或 `['status'=>'success']`。
- 用户名：**使用魔方 `$params['domain']`**（主机名，随机唯一），空时兜底 `zh_{hostid}`。密码用 `$params['password']`，空时随机生成。

| 魔方方法 | MNBT gn | 关键入参 | 说明 |
|----------|---------|----------|------|
| `TestLink` | `cfif` | username=`test` | 校验连接与鉴权（返回 `['status'=>200,'data'=>['server_status'=>1|0,'msg'=>...]]`） |
| `CreateAccount` | `kt` | username、password、`webdx`/`sqldx`/`sizemax`/`ymbds`（取自 configoptions）、`dqtime`=nextduedate（永久/空传 `0`） | **开通即建站**（FTP+数据库+站点） |
| `SuspendAccount` | `zt` | username | 停站点+FTP，`qk=false` |
| `UnsuspendAccount` | `jc` | username | `qk=true`，恢复站点+FTP |
| `TerminateAccount` | `tz` | username | 删站点 → 删 `MN_zj` 行 |
| `Renew` | `xf` | username、`setdate`=nextduedate | 更新 `datae`，必要时恢复 |
| `ChangePackage` | `zjmode` | username、`websize`/`sqlsize`/`ll`（新配额绝对值） | 升降级后更新配额；通过 `configoptions_upgrade` 检测变更项（参照 noKVM 写法） |
| `CrackPassword` | `czmm` | `$new_pass`（第二参数） | 重置 FTP+控制面板密码 |
| `Status` | `ztcx`（新增） | username | 状态映射（见 §5） |
| `Sync` | `ztcx` | username | 同步状态/配额回魔方 |
| `On` | `start`（新增） | username | 启动站点（SiteStart）；`qk=false` 拒绝 |
| `Off` | `stop`（新增） | username | 停止站点（SiteStop） |
| `UsageUpdate`（P1） | `sy`（新增） | 全量 hostID | 空间/流量用量批量上报 |

**CreateAccount 细节**：
1. `mn_vs=15`；`username` 用魔方 `domain`（如 `seri914WksqNY`），保证唯一且不与现有用户冲突。
2. `dqtime`：`nextduedate` 为 `0000-00-00` 或空 → 传 `0`（永久）。
3. 配额参数单位与 MNBT 现有口径一致（hxa/hxb/llmax 直接存值，PRD 假定 MB，联调时以魔方/宝塔实际口径校准，见 Q1）。
4. 返回 `ok` 后魔方自动回写账号密码到产品表（`shd_host.username/password`）。

**ChangePackage 细节**：`zjmode` 接收 `websize`/`sqlsize`/`ll` 三个**绝对值**参数（见 api/api.php L192-202：`$hxa_array['max'] = $_POST['websize']`）。魔方侧遍历 `$params['configoptions_upgrade']`，对变更的 `webdx/sqldx/sizemax` 分别传值；未变更的项不传（保持原值）。php 版本字段由 MNBT 节点默认值管理，不参与升降级。

### 3.6 ClientArea 前台自定义输出

```php
function mnbthost_ClientArea($params) {
    return [
        'console' => ['name' => '主机信息'],
    ];
}
```

`ClientAreaOutput`（key=`console`）：
- 调 `gn=ztcx` 查询状态与配额用量。
- 模板（`templates/console.html`）展示：**控制台地址（可点击+一键复制）、账号、密码、网站/数据库/流量用量进度条、状态图标**。
- 样式沿用 Docker 模块的轻量 CSS+JS 方案（不依赖 UI 框架，含 `dockerCopy` 一键复制脚本），保持一致观感。

### 3.7 ClientButton 前台自定义按钮

```php
function mnbthost_ClientButton($params) {
    return [
        'console' => ['place' => 'console', 'name' => '打开主机控制台'],
    ];
}
```

自定义方法 `mnbthost_console` 返回 `['status'=>'success','url'=>$console_url]`（P0 外链跳转，用户用主机账号登录）。

---

## 4. MNBT 侧：外部 API 扩展（api/api.php）

### 4.1 现状（已成熟，无需改动）

| gn | 动作 | 魔方对应 |
|----|------|----------|
| `cfif` | 连接验证 | TestLink |
| `kt` | 开通主机（建站+FTP+数据库） | CreateAccount |
| `zt` | 暂停（停站点+FTP） | SuspendAccount |
| `jc` | 恢复 | UnsuspendAccount |
| `xf` | 续费（`setdate`） | Renew |
| `tz` | 删除（删站点+删行） | TerminateAccount |
| `czmm` | 重置密码 | CrackPassword |
| `zjmode` | 修改配额（`websize`/`sqlsize`/`ll`） | ChangePackage |

> 现有 8 个 gn 已满足生命周期闭环，**本期新增 3 个只读/操作 gn**：`ztcx`（状态+配额查询）、`start`/`stop`（站点启停）。其余不改动。

### 4.2 新增 gn：`ztcx` 状态与配额查询

```
POST api/api.php?gn=ztcx
```

**逻辑**：按 `username` 查 `MN_zj` → 解析 `hxa/hxb/llmax`（JSON，`max`/`dq` 字段）→ 组装返回。

**响应**：

```json
{
  "success": true, "code": 200, "msg": "ok",
  "data": {
    "user": {
      "username": "seri914WksqNY", "qk": "true", "datae": "2027-12-31",
      "domain": "seri914WksqNY", "sqluser": "seri914", "btid": "12",
      "created_at": "2026-08-07"
    },
    "quota": {
      "web_size_max": 500, "web_size_used": 123, "unit": "MB",
      "sql_size_max": 100, "sql_size_used": 12, "unit": "MB",
      "flow_max": 0, "flow_used": 0, "unit": "MB"
    },
    "node": { "btip": "150.158.137.178", "ptl": "true" }
  }
}
```

- 用户不存在 → `code 100`。
- 配额单位沿用 `MN_zj.hxa/hxb/llmax` 存储口径（MB），与 `kt`/`zjmode` 入参一致。
- 不调宝塔接口（用量存于本地 JSON 字段），响应快、不依赖节点在线。

### 4.3 新增 gn：`start` / `stop` 站点启停

```
POST api/api.php?gn=start   # 启动站点（SiteStart）
POST api/api.php?gn=stop    # 停止站点（SiteStop）
```

**逻辑**：
- 按 `username` 查 `MN_zj`，用户不存在 → `code 100`。
- `start` 额外校验：`qk=false`（已暂停）→ 拒绝，提示"该主机已暂停，请先解除暂停"。
- 调宝塔 `siteqt($btid, $sqldz, $start)`，成功（`status==1`）→ `code 200`；失败返回宝塔 `msg`。

响应：`{"success":true,"code":200,"msg":"站点启动成功！"}` / `"站点停止成功！"`

> 与 `zt`/`jc`（账户暂停/恢复，改 `qk` 并停 FTP）语义不同：`start`/`stop` 仅操作站点运行状态，**不改变 `qk` 账户状态**。

### 4.4 预留 gn：`sy` 用量查询（P1）

单账号/批量（逗号分隔）返回 `web_size_used`/`sql_size_used`/`flow_used`，供魔方 `UsageUpdate` 定时拉取。P0 不做。

---

## 5. 状态与用量映射

### 5.1 Status 映射表

| MNBT 侧状态 | 魔方 status | des |
|-------------|-------------|-----|
| `qk=true` 且未到期 | `on` | 运行中 |
| `qk=false`（暂停） | `suspend` | 已暂停 |
| `datae` 已过（非永久） | `suspend` | 已到期 |
| 用户不存在 / 查询失败 | `unknown` | 未知状态 |

> 虚拟主机开通为**同步建站**，无 `creating/waiting` 中间态（与 Docker 不同）。

### 5.2 用量模型（P1 UsageUpdate）

```php
['status' => 'success', 'data' => [
    ['hostid' => 1, 'web_size_used' => 123, 'web_size_max' => 500,
     'sql_size_used' => 12, 'sql_size_max' => 100,
     'flow_used' => 0, 'flow_max' => 0, 'unit' => 'MB'],
]]
```

---

## 6. 安全设计

| 项 | 方案 |
|----|------|
| 鉴权 | 沿用 MNBT 三层：系统密钥（`mn_key`）+ 节点调用密钥 md5（`mn_keye`）+ 节点启用校验（`MN_bt.qk`） |
| 密钥存储 | 魔方侧 `server_password`（调用密钥）、`accesshash`（系统密钥），不回显不落日志 |
| 日志 | MNBT `api_lifecycle_log` 仅记录用户名与动作，不记录密码 |
| 越权 | 所有 gn 按 `username` 定位 `MN_zj`，无跨用户访问路径 |
| 传输 | HTTPS 建议项；`api_url` 仅接受 `http(s)://` |

---

## 7. 安装部署与配置

### 7.1 安装模块

1. 将 `mf_modules/servers/mnbthost/` 复制到魔方财务 `/modules/servers/mnbthost/`。
2. 清魔方缓存，后台「产品 → 服务器」确认模块出现。

### 7.2 服务器接口设置

| 字段 | 值 |
|------|-----|
| 服务器 IP/域名 | MNBT 域名 |
| 端口 | 80/443 |
| 用户名 | `MN_bt.btdh`（宝塔开通代号，⚠️ 非自增 id） |
| 密码 | `md5(节点ktmy . 节点qmk)` 或留空 |
| Access Hash | 系统 API 密钥（`$conf['api']`） |
| SSL | 按站点勾选 |

### 7.3 产品关联模块

后台「产品 → 添加产品」→ 类型「服务器产品」→ 模块「梦奈宝塔虚拟主机」→ 配置产品配额（网站空间/数据库空间/流量/域名数）。

### 7.4 联调验证流程

1. 前台下单 → 支付 → 自动开通 → MNBT 后台「主机列表」出现站点（含 FTP/数据库）。
2. 后台执行 暂停/恢复/续费/删除/改密/升降级 → `MN_zj` 状态与配额正确变化。
3. 前台点「开机/关机」→ 站点运行状态同步变化（`siteqt`）。
4. 前台「主机信息」页显示账号密码、配额用量进度条、状态图标。

---

## 8. 里程碑与验收

### M1 — MNBT API 查询扩展
- [ ] `api/api.php` 新增 `gn=ztcx`（状态+配额用量）、`start`/`stop`（站点启停）。
- **验收**：curl 验证三个 gn 正常/异常用例；`start` 对暂停账户返回拒绝；`MN_zj` 配额 JSON 正确解析。

### M2 — 魔方模块骨架 + 生命周期闭环
- [ ] `mnbthost.php`：MetaData/ConfigOptions/TestLink/CreateAccount/Suspend/Unsuspend/Terminate/Renew/ChangePackage/CrackPassword + On/Off。
- **验收**：魔方后台可下单开通、暂停、恢复、续费、删除、升降级、改密、开机/关机，MNBT 侧全部正确。

### M3 — 前台集成
- [ ] Status（`ztcx`）+ ClientArea + ClientButton + `templates/console.html`（登录信息 + 一键复制 + 用量进度条）。
- **验收**：前台显示登录信息与配额用量；控制台按钮可跳转。

### M4 — 文档收尾
- [ ] 模块 README、API.md 增加虚拟主机对接章节、测试文档联调用例。

---

## 9. 决策点（待评审）

| # | 决策 | 建议 |
|---|------|------|
| D1 | 模块命名 | `mnbthost`，显示名「梦奈宝塔虚拟主机」 |
| D2 | 用户名来源 | 魔方 `domain`（随机主机名），空时 `zh_{hostid}`；与 Docker 模块一致 |
| D3 | 节点标识 | `MN_bt.btdh`（开通代号），需在 README 醒目提示与 Docker 的 id 区别 |
| D4 | 配额单位 | 沿用 MNBT 现有口径（MB），不新增单位字段 |
| D5 | 站点启停（On/Off） | **P0 实现**（`gn=start`/`stop` 调 `siteqt`，不改 `qk` 账户状态） |
| D6 | `ztcx` 是否调宝塔 | **不调**，直接读 `MN_zj` 本地字段（快、不依赖节点） |

---

## 10. 风险与开放问题

| # | 风险/问题 | 影响 | 应对 |
|---|-----------|------|------|
| R1 | `MN_bt.btdh` 与 `MN_docker_node.id` 混淆 | 配错节点导致鉴权失败 | README + 错误提示中显式标注 |
| R2 | `username` 用魔方 domain，与 MNBT 现有用户命名规则（≥6位）冲突？ | 开通失败 | `kt` 要求 username/password ≥6 位，魔方 domain 通常满足；联调验证 |
| R3 | `zjmode` 配额绝对值语义 | 升降级可能把未变更项重置 | ChangePackage 仅传变更项（`configoptions_upgrade` 检测） |
| Q1 | hxa/hxb/llmax 实际单位（MB/GB） | 影响用量展示口径 | M2 联调时以 `MN_zj` 实际存储值与宝塔口径校准 |
| Q2 | 魔方 `ChangePackage` 的 `configoptions_upgrade` 字段名 | 影响实现细节 | 参照 noKVM 现有写法，联调打印 `$params` 确认 |
| Q3 | 站点删除（`tz`）时宝塔 `delsite` 是否连带清数据库 | 影响删除完整性 | 沿用现有 api.php `tz` 行为，不新增逻辑 |
