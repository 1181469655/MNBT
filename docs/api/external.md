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
| `jk_monitor.php` | URL 监控 + 资源监控 + 到期/流量提醒 | 宝塔计划任务每分钟访问 `?my=API密钥` |
| `jk.php` | 域名/文件监控 | 内部调用 |

---

[API 参考总览](../api/overview.md)
