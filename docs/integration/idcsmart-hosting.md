---
title: 魔方财务 × 虚拟主机对接
description: 将梦奈宝塔虚拟主机作为标准服务器产品接入魔方财务（idcsmart）的完整指南
---

# 魔方财务 × 虚拟主机对接

将梦奈宝塔虚拟主机作为标准服务器产品接入魔方财务（idcsmart），实现"产品 → 自动开通 → 前台管理 → 到期续费/删除"的完整闭环。

> 详见 PRD：[虚拟主机×魔方财务对接 PRD](../prd/hosting-idcsmart.md)

---

## 架构

```
魔方财务（idcsmart）
  前台用户 ──下单/续费/删除──▶ shd_host / 订单体系
      │
      ▼
/modules/servers/mnbthost/mnbthost.php
  (server module：CreateAccount/Renew/Status/...)
      │ HTTPS POST（mn_bh/mn_key/mn_keye 鉴权）
      ▼
MNBT 外部 API：api/api.php (gn=cfif/kt/zt/xf/jc/tz/czmm/zjmode/ztcx/start/stop)
      │
      ▼
MN_bt（节点）/ MN_zj（主机/站点）
```

**核心模型**：虚拟主机 = 宝塔站点。`CreateAccount` 调 `kt` **立即建站**（含 FTP、数据库），配额（网站空间/数据库空间/流量）随产品配置选项落库。

---

## 服务器接口设置

| 魔方服务器字段 | MNBT 对应 | 说明 |
|---------------|----------|------|
| 服务器 IP/域名 | MNBT 域名 | 站点地址 |
| 端口 | 80/443 | |
| **用户名** | `MN_bt.btdh` | ⚠️ 宝塔开通代号，**不是自增 id** |
| **密码** | `md5(ktmy . qmk)` | 调用密钥，必填（节点 ktmy/qmk 添加时自动生成） |
| **Access Hash** | 系统 API 密钥 | `$conf['api']` |
| SSL | 按站点 HTTPS | |

### 获取调用密钥

MNBT 后台「宝塔列表」→ 找到节点 → **ktmy 列**点击图标，显示 `md5(ktmy.qmk)`。

---

## 产品配置（ConfigOptions）

| key | 类型 | 默认 | 说明 |
|-----|------|------|------|
| `webdx` | text | 500 | 网站空间（MB） |
| `sqldx` | text | 100 | 数据库空间（MB） |
| `sizemax` | text | 0 | 流量上限（MB，0=不限） |
| `ymbds` | text | 1 | 域名绑定上限 |
| `console_url` | text | 空 | 用户控制台地址 |

---

## 模块方法映射

| 魔方操作 | MNBT gn | 说明 |
|----------|---------|------|
| 开通 | `kt` | 开通即建站（FTP+数据库+站点），username=魔方 domain |
| 暂停 | `zt` | 停站点+FTP，qk=false |
| 恢复 | `jc` | qk=true，恢复站点+FTP |
| 删除 | `tz` | 删站点+删 `MN_zj` 行 |
| 续费 | `xf` | 更新到期时间（setdate=nextduedate） |
| 升降级 | `zjmode` | 更新空间/数据库/流量配额（仅传变更项） |
| 改密 | `czmm` | 重置 FTP+控制面板密码 |
| 开机 | `start` | 站点启动（SiteStart），不改 qk |
| 关机 | `stop` | 站点停止（SiteStop），不改 qk |
| 状态/同步 | `ztcx` | 状态+配额用量查询 |

---

## 状态映射

| MNBT 状态 | 魔方 status | 说明 |
|-----------|-------------|------|
| `qk=true` 且未到期 | `on` | 运行中 |
| `qk=false`（暂停） | `suspend` | 已暂停 |
| `datae` 已过 | `suspend` | 已到期 |
| 用户不存在 | `unknown` | — |

---

## 前台功能

- **主机信息** 选项卡：控制台地址/账号/密码（一键复制）+ 主机状态 + 配额用量进度条（空间/数据库/流量）
- **打开主机控制台** 按钮：跳转用户控制台

---

## 注意事项

- 用户名使用魔方 `domain`（随机主机名），与 MNBT 现有用户不冲突
- `ztcx` 直接读 MNBT 本地 `MN_zj` 表配额字段，不调宝塔，响应快
- 虚拟主机开通为**同步建站**，无 `creating/waiting` 中间态（与 Docker 不同）
- 配额单位为 MB

> 完整部署说明见 `mf_modules/servers/mnbthost/README.md`
