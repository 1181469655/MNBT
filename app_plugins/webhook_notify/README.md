# webhook_notify Webhook 通知插件

在主机开通/暂停/恢复/续费/删除及订单支付成功时，向配置的 URL 发送 JSON Webhook 通知。

## 功能

- 监听 6 类事件并发送 JSON Webhook
- HMAC-SHA256 签名验证（可选 secret）
- 自定义事件订阅（按需开关每类事件）
- 发送日志（保留最近 40 条）
- 测试发送按钮
- 仪表盘小部件（显示 Webhook 状态）
- 支持 HTTP/HTTPS，可选跳过 SSL 证书验证

## 依赖

无。

## 安装

1. 后台 → 系统管理 → 插件管理 → 安装 `webhook_notify` → 启用
2. 在插件设置页配置 Webhook URL 和 Secret

## 监听事件

| 事件 | 触发时机 |
|------|----------|
| `host.created` | 主机开通 |
| `host.paused` | 主机暂停 |
| `host.unpaused` | 主机恢复 |
| `host.renewed` | 主机续费 |
| `host.deleted` | 主机删除 |
| `order.paid` | 订单支付成功 |

## 配置项

存储在 `MN_plugin_option` 表（plugin_slug='webhook_notify'）：

| key | 说明 |
|-----|------|
| `url` | Webhook 接收 URL（http/https） |
| `secret` | 签名密钥（可选，用于 HMAC-SHA256） |
| `enabled` | 是否启用（true/false） |
| `insecure_ssl` | 跳过 SSL 证书验证（true/false） |
| `events` | 事件订阅 JSON（每类事件 true/false） |
| `delivery_log` | 发送日志数组（最近 40 条） |

## Webhook 报文格式

```json
{
  "event": "host.created",
  "time": "2026-07-16T12:00:00+08:00",
  "source": "mnbt",
  "payload": {
    "host": { "id": 1, "user": "username", "ssbt": "...", "btid": "..." },
    "ctx": { "source": "manual" }
  }
}
```

### 签名验证

若配置了 secret，请求头包含 `X-MNBT-Signature: sha256={hmac}`，接收方可用 HMAC-SHA256 验证：

```
signature = hash_hmac('sha256', request_body, secret)
```

请求头：

| Header | 说明 |
|--------|------|
| `Content-Type` | `application/json; charset=utf-8` |
| `X-MNBT-Event` | 事件名（如 `host.created`） |
| `X-MNBT-Signature` | `sha256={hmac}`（仅配置了 secret 时） |

## 管理员端

| 页面 | 入口 |
|------|------|
| 设置页 | `plugin.php?p=webhook_notify&page=settings` |

管理员 AJAX：

| gn | 说明 |
|----|------|
| `p_webhook_notify_save` | 保存配置 |
| `p_webhook_notify_test` | 发送测试 Webhook |

## 文件结构

```
webhook_notify/
├── plugin.json
├── bootstrap.php        # 主入口（钩子 + 设置页 + AJAX + 仪表盘小部件）
├── install.sql          # 无自建表（配置存 MN_plugin_option）
└── admin/
    └── settings.php     # 设置页
```
