---
title: 数据库
description: 核心表结构与业务关系说明
---

# 数据库

## 核心表结构（共 13 张表）

| 表名 | 说明 |
|------|------|
| `MN_config` | 系统配置（网站信息/支付/邮箱/监控/API/违禁词扫描等） |
| `MN_bt` | 宝塔面板节点（IP/端口/密钥/操作系统/状态） |
| `MN_zj` | 主机账号（宝塔关联/配额/到期时间/状态/邮箱绑定） |
| `MN_ym` | 售卖域名（绑定宝塔/价格/介绍/上下架） |
| `MN_bs` | 一键部署程序（名称/价格/安装配置/上架状态） |
| `MN_dd` | 支付订单（金额/方式/场景/状态） |
| `MN_log` | 操作日志（操作用户/时间/类型/IP/结果） |
| `MN_monitor_task` | 监控任务（URL/资源/规则/间隔/失败计数） |
| `MN_monitor_log` | 监控检测日志（HTTP 状态码/响应时间/错误信息） |
| `MN_notice_log` | 通知日志（到期/流量/监控告警，支持已读标记） |
| `MN_node` | MNBT 节点注册（ID/密钥/能力/心跳） |
| `MN_node_task` | 节点异步任务队列 |
| `MN_node_nonce` | 节点防重放攻击 nonce 表 |
| `MN_forbidden_scan` | 违禁词扫描任务摘要 |
| `MN_forbidden_match` | 违禁词扫描命中记录 |

## 业务关系

```
MN_bt (宝塔节点)  ──1:N──>  MN_zj (主机账号)
MN_bt (宝塔节点)  ──1:N──>  MN_ym (域名资源)
MN_zj (主机账号)  ──1:1──>  MN_ym? (域名绑定)
MN_dd (订单)      ──N:1──>  MN_bs / MN_ym (支付场景)
MN_zj (主机账号)  ──1:N──>  MN_monitor_task (监控任务)
MN_monitor_task   ──1:N──>  MN_monitor_log (检测日志)
MN_zj (主机账号)  ──1:N──>  MN_notice_log (通知日志)
```
