# MNBT Connector

MNBT 私有节点宝塔面板插件。支持文件扫描、站点访问统计等功能。

## 目标

- **违禁词扫描** — 增量/全量扫描站点文件，检测违禁词并上报
- **站点访问统计** — 实时采集 nginx 访问日志，聚合 PV/UV/IP/URI/IP排行/客户端/蜘蛛/状态码等指标，存储在本地 SQLite
- **远程查询** — MNBT 主控通过 BT 面板 API 按需查询统计数据，不主动上报

## 架构

```
┌─ BT 节点服务器 ───────────────────────────────────────────────────┐
│                                                                     │
│  /www/wwwlogs/*.log                                                 │
│         │  stat 检测变化                                             │
│         ▼                                                           │
│  worker.py (后台常驻，每 10s 一轮)                                    │
│    ├─ heartbeat          →  MNBT /api/node.php?act=heartbeat         │
│    ├─ collect_site_stats →  解析新行 → maxiaole.db (SQLite 聚合)     │
│    ├─ forbidden_scan     →  扫描文件 → 上报 MNBT                     │
│    └─ pull_task          →  执行 MNBT 下发的任务                      │
│                                                                     │
│  mnbt_connector_main.py (BT 面板插件 API 入口)                       │
│    ├─ get_status / start / stop / run_once                           │
│    ├─ trigger_full_scan                                               │
│    └─ get_site_list / get_site_detail / get_site_trend / ...         │
│         ↑                                                           │
│  maxiaole.db (SQLite)  ←── 查询数据                                  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
         ↕ BT 面板 API (/plugin?action=a&name=mnbt_connector&s=...)
┌─ MNBT 主控 (PHP) ───────────────────────────────────────────────────┐
│  bt_api.php → pluginRequest() 调用插件 API                           │
│  管理端 / 用户端 → 展示图表                                          │
└─────────────────────────────────────────────────────────────────────┘
```

## 数据流

```
日志写入 → worker 轮询检测文件变更 → 增量读取 → 逐行解析 → 批量写入 SQLite
                                                                    ↓
MNBT PHP → bt_api.php → BT 面板鉴权 → 插件 API → 查询 SQLite → 返回 JSON
```

## 站点统计功能

| 功能 | 说明 |
|---|---|
| 实时采集 | 轮询 `/www/wwwlogs/*.log`，stat 检测文件变化，增量读取新行 |
| 小时级聚合 | 每小时 PV/UV/流量，支持折线趋势图 |
| URI 排行 | 当日 Top URI 请求数 + 流量 |
| IP 排行 | 当日 Top IP 请求数 + 流量 |
| 状态码分布 | 2xx / 3xx / 4xx / 5xx 各状态码请求数和流量 |
| 蜘蛛统计 | 识别 Baidu/Google/Bing/Sogou/360 等蜘蛛 |
| 客户端统计 | PC vs 移动端、Chrome/Firefox/Safari/Edge 等浏览器分布 |
| 日志位置追踪 | SQLite `log_position` 表记录每个日志文件的 inode/size/mtime，支持日志轮转 |

## SQLite 表结构 (maxiaole.db)

| 表名 | 用途 | 唯一键 |
|---|---|---|
| `log_position` | 文件读取位置追踪 | site_name |
| `site_hourly_stats` | 小时级 PV/UV/流量 | site_name + hour |
| `site_uri_stats` | URI 排行 | site_name + date + uri |
| `site_ip_stats` | IP 排行 | site_name + date + ip |
| `site_spider_stats` | 蜘蛛统计 | site_name + date + spider_name |
| `site_client_stats` | 客户端分布 | site_name + date + client_type + client_name |
| `site_status_stats` | 状态码分布 | site_name + date + status_code |
| `site_method_stats` | 请求方式统计 | site_name + date + method |
| `site_error_logs` | 错误日志逐条存储 | 自增 id |

## 插件 API

通过 BT 面板插件路由调用：`POST /plugin?action=a&name=mnbt_connector&s={method}`

| 方法 | 参数 | 返回 |
|---|---|---|
| `get_status` | - | 运行状态、节点信息 |
| `start` | - | 启动工作进程 |
| `stop` | - | 停止工作进程 |
| `run_once` | - | 执行一次工作周期 |
| `trigger_full_scan` | - | 触发全量扫描 |
| **站点统计** | | |
| `get_site_list` | - | 所有站点 + 今日 PV/UV/流量 |
| `get_site_overview` | site, range | 今日/昨日/7d/30d 概览指标 |
| `get_site_trend` | site, range | 趋势数据点列表 |
| `get_site_spider_analysis` | site, date | 蜘蛛来访分析 |
| `get_site_client_stats` | site, date | 客户端(PC/移动/浏览器)分布 |
| `get_site_method_stats` | site, date | 请求方式(GET/POST等)统计 |
| `get_site_ip_rank` | site, date, limit | IP 排行 |
| `get_site_uri_rank` | site, date, limit | URI 排行 |
| `get_site_error_logs` | site, date, limit, min_status | 错误日志列表 |
| `get_site_recent_logs` | site, lines | 原始日志(实时读文件) |

## 计划

- [x] 违禁词扫描
- [x] 节点心跳 + 任务拉取
- [x] 站点日志采集 + SQLite 聚合 (worker.py)
- [x] 插件查询 API (mnbt_connector_main.py)
- [ ] BT 面板 Web 展示页 (index.html)
- [ ] MNBT 主控 bt_api.php pluginRequest 方法
- [ ] 管理端 / 用户端统计页面

## Config

| 字段 | 说明 |
|---|---|
| `mnbt_url` | MNBT 主控地址 |
| `platform_secret` | 平台密钥 (MN_config.api) |
| `node_id` | 节点 ID |
| `node_secret` | 节点密钥 |
| `node_name` | 节点名称 |
| `interval_seconds` | 工作间隔 (秒) |
| `capabilities` | 能力列表 |

## 部署

```bash
# 安装插件
bash /www/server/panel/plugin/mnbt_connector/install.sh

# 编辑配置
vim /www/server/panel/plugin/mnbt_connector/config.json

# 手动执行一次
python3 /www/server/panel/plugin/mnbt_connector/worker.py --once
```
