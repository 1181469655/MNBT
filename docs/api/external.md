---
title: 外部对接 API
description: api/api.php 主机生命周期 API 与 api/node.php MNBT 节点 API 对接说明
---

# 外部对接 API

## 4.1 主机生命周期 API（api/api.php）

**入口**：`POST /api/api.php?gn=<动作>`  
**文件**：api/api.php  
**Content-Type**：`application/json; charset=UTF-8`

### 鉴权参数（所有请求必带）

| 参数 | 说明 |
|------|------|
| `mn_bh` | 宝塔节点编号（`MN_bt.btdh`） |
| `mn_key` | 系统密钥（= `MN_config.api`） |
| `mn_keye` | 宝塔调用密钥 = `md5(MN_bt.ktmy . MN_bt.qmk)` |
| `mn_vs` | 插件版本号（必须 >= 15） |
| `username` | 主机用户名（`MN_zj.user`） |

### 接口列表

| `gn` | 功能 | 额外参数 | 返回 |
|------|------|----------|------|
| `cfif` | 连接验证 | 无 | `{"success":true,"code":200,"msg":"连接验证成功！"}` |
| `kt` | 开通主机 | `password`、`sizemax`、`dqtime`、`webdx`、`sqldx`、`ymbds` | 同上 |
| `zt` | 暂停主机 | 无 | 同上 |
| `jc` | 解除暂停 | 无 | 同上 |
| `tz` | 删除主机 | 无 | 同上 |
| `xf` | 续费主机 | `setdate`（续期日期） | 同上 |
| `czmm` | 重置 FTP/面板密码 | `password` | 同上 |
| `zjmode` | 修改主机配额 | `websize`、`sqlsize`、`ll` | 同上 |

### 请求示例

```http
POST /api/api.php?gn=kt HTTP/1.1
Host: your-domain.com
Content-Type: application/x-www-form-urlencoded

mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_OF_BT_KEY&mn_vs=15&username=testuser&password=testpass123&sizemax=1024&dqtime=2026-12-31&webdx=1024&sqldx=512&ymbds=5
```

### 响应示例

```json
{
  "success": true,
  "code": 200,
  "msg": "主机开通成功！"
}
```

```json
{
  "success": false,
  "code": 100,
  "msg": "错误！该主机已经存在，请重新开通！"
}
```

### 开通规则

- 账号、密码长度 >= 6
- 账号不能重复
- 自动检测节点 PHP 版本（优先使用 `MN_bt.mrbts_php`，否则取最新已安装版本）
- 自动触发 `host.created` 钩子（插件可监听）

### 协议兼容开关（`MN_config.api_compat`）

V1.83+ 在不变的传输包络（POST 表单 + `{success,code,msg}` 响应）之上加入了若干**行为约束**，仍在使用 1.81 版对接模块（`wp_seller_plugin`、魔方财务 `mnbthost` server module）的客户升级 MNBT 后可能被拒。为此提供开关：

- 取值：`0` = 新版协议（1.83+ 严格，默认）；`1` = 兼容 1.81 对接模块
- 后台切换：系统设置 → API 设置 → 「对外 API 协议模式」
- 代码判断：`api/api.php` 内统一走 `mnbt_api_compat_mode()`（`MPHX/function.php`）读取该配置

两种模式的行为差异（仅 `api/api.php`）：

| 差异点 | 新版协议（api_compat=0） | 兼容 1.81（api_compat=1） |
|--------|--------------------------|---------------------------|
| 跨节点归属校验（除 `kt`/`cfif` 外，含 `zjmode`） | `MN_zj.ssbt` 与 `mn_bh` 严格比较，不一致返回 `主机不属于该节点` | 跳过该校验（老模块传的 `username` 可能与节点代号对不上） |
| 空主机检查 | `不存在主机用户名` | 保留不变（1.81 靠弱类型访问空行是 bug，不还原） |
| `kt` 站点名 | `mnbt.<随机>.<后缀>`，写库失败重取号重试 | `mnbt.<max(id)+1><随机>.<后缀>`，单次取号插入（老模块按 id 序列猜站点名） |
| `zjmode` 配额 | 仅在传入 `websize`/`sqlsize`/`ll` 非空时覆盖 | 无条件覆盖，缺参即清零（老模块依赖该语义） |
| `xf` 续费自动解停 | 仅当 `qk=='false'`（真暂停）才解停 | 对 `qk` 做真值判断（1.81 原行为：总会解停） |
| 宝塔调用密钥校验失败 | `调用密钥不匹配！`，日志带 key 前缀 | `错误！您所传输的宝塔调用密钥与该宝塔的调用密钥不匹配！`，日志不带前缀（老模块按 msg 文案分支） |
| `kt` 主机存在判断 | `if($et_zj)` | 同左，刻意不还原 1.81 的恒真表达式（那是 bug 不是兼容需求） |
| `tz` 删除主机 | 级联删除 `MN_plugin_hosting_asset` | 同左，保留不动 |

> **提示：** 兼容模式只是过渡期方案，会放宽跨节点归属等安全约束。建议尽快将对接模块升级到适配 1.83+ 协议的版本，然后切回「新版协议」。

---

## 4.2 MNBT 节点 API（api/node.php）

**入口**：`POST /api/node.php?act=<动作>`  
**文件**：api/node.php  
**Content-Type**：`application/json`（请求体为 JSON）  
**鉴权**：通过 `mnbt_node_authenticate()` 校验 `node_id` + `node_secret`

### 接口列表

| `act` | 功能 | 请求体 | 返回 |
|-------|------|--------|------|
| `heartbeat` | 心跳上报 | 节点状态信息 | `{"success":true,"msg":"heartbeat ok","server_time":"..."}` |
| `pull_task` | 拉取待执行任务 | 无 | `{"success":true,"msg":"pull task ok","task":{...}}` |
| `report_result` | 上报任务结果 | 任务执行结果 | `{"success":true,"msg":"..."}` |
| `get_config` | 获取节点配置 | 无 | `{"success":true,"msg":"config ok","config":{"forbidden_scan":{...}}}` |

### `get_config` 返回的违禁词扫描配置

```json
{
  "config": {
    "forbidden_scan": {
      "enabled": true,
      "content": "违禁词1,违禁词2",
      "scan_changed_only": true,
      "scan_dir": "/www/wwwroot",
      "skip_dirs": ".git,node_modules,vendor,runtime,cache,logs",
      "skip_exts": ".jpg,.png,.gif,.webp,.mp4,.zip,.rar,.7z,.pdf,.woff,.ttf",
      "max_file_size": 5242880,
      "max_matches": 1000,
      "full_scan_enabled": true,
      "full_scan_cron": "0 3 * * *"
    }
  }
}
```

### 监控任务执行入口

| 文件 | 用途 | 触发方式 |
|------|------|----------|
| `jk_monitor.php` | URL 监控 + 资源监控 + 到期/流量提醒 | 宝塔计划任务每分钟访问 `?t=<unix>&sign=<hash_hmac('sha256','jk_monitor|<t>',API密钥)>`（±300秒有效；兼容旧 `?my=`，已弃用） |
| `jk.php` | 域名/文件监控 | 内部调用 |

---

[API 参考总览](../api/overview.md)
