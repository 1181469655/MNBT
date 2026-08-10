---
title: 数据库表速查
description: MNBT 全部 18 张数据表关键字段速查与常用请求示例
---

# 数据库表速查

> 完整表结构见 `install/install.sql`

| 表名 | 说明 | 关键字段 |
|------|------|----------|
| `MN_config` | 系统配置 | `api`、`kzmbqk`、`hxi`、`hxo`、`pay_methods` |
| `MN_bt` | 宝塔节点 | `btdh`、`btip`、`btdk`、`btmy`、`ktmy`、`qmk`、`btos`、`mrbts_php` |
| `MN_zj` | 主机账号 | `id`、`btid`、`user`、`pass`、`ssbt`、`sqldz`、`datae`、`hxa`、`hxb`、`llmax` |
| `MN_ym` | 售卖域名 | `url`、`bt`、`jg`、`kg` |
| `MN_bs` | 一键部署程序 | `name`、`jg`、`src`、`sxpz`、`tj`、`qk` |
| `MN_dd` | 订单 | `user`、`rmb`、`ff`、`scene`、`qk` |
| `MN_log` | 操作日志 | `czuser`、`date`、`lx`、`lr`、`ip`、`qk` |
| `MN_monitor_task` | 监控任务 | `user`、`task_type`、`url`、`interval_seconds`、`enabled` |
| `MN_monitor_log` | 监控检测日志 | `task_id`、`user`、`status_code`、`response_time` |
| `MN_notice_log` | 通知日志 | `user`、`type`、`level`、`title`、`content`、`is_read` |
| `MN_node` | 节点注册 | `node_id`、`node_secret`、`capabilities`、`last_heartbeat` |
| `MN_node_task` | 节点任务队列 | `node_id`、`action`、`payload`、`status` |
| `MN_node_nonce` | 防重放 nonce | `node_id`、`nonce`、`expires_at` |
| `MN_forbidden_scan` | 违禁词扫描摘要 | `node_id`、`task_id`、`status` |
| `MN_forbidden_match` | 违禁词命中记录 | `scan_id`、`file`、`keyword` |
| `MN_plugin` | 插件表 | `slug`、`enabled`、`version` |
| `MN_plugin_option` | 插件配置 | `slug`、`key`、`value` |
| `MN_plugin_user` | 插件独立用户表 | `username`、`password_hash`、`email`、`qq`、`status` |

> Docker 模块的 4 张表（`MN_docker_node` / `MN_docker_user` / `MN_docker_plan` / `MN_docker_order`）见 [Docker 控制台与内部实现](./docker-console.md)。

---

## 附录：常用请求示例

### 用户登录示例

```bash
curl -X POST http://your-domain/user/ajax.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "gn=login&user=testuser&pass=testpass&code=1234"
```

### 获取主机列表

```bash
curl -X POST http://your-domain/admin/ajax.php \
  -H "Cookie: admin_token=xxxxxx" \
  -d "gn=listzj&page=1&limit=20&sort=id&sortOrder=desc"
```

### 外部 API 开通主机

```bash
curl -X POST "http://your-domain/api/api.php?gn=kt" \
  -d "mn_bh=1&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=newuser&password=newpass123&sizemax=1024&dqtime=2026-12-31&webdx=1024&sqldx=512&ymbds=5"
```

### 节点心跳

```bash
curl -X POST "http://your-domain/api/node.php?act=heartbeat" \
  -H "Content-Type: application/json" \
  -d '{"node_id":"n1","node_secret":"xxx","status":"online","load":1.2}'
```

### 插件 AJAX（domain_shop）

```bash
curl -X POST http://your-domain/admin/ajax.php \
  -H "Cookie: admin_token=xxxxxx" \
  -d "gn=p_domain_addym&url=example.com&bt=1&jg=10&ymjs=介绍&kg=true&channel=pan&provider_id=0"
```

---

**相关文档：**

- [API 通用约定](./overview.md)
- [核心工具函数](./functions.md)
