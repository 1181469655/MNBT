---
title: MNBT 虚拟主机 × 魔方财务（idcsmart）对接 PRD — 附录
description: 虚拟主机×魔方财务 PRD 的附录：里程碑与验收、决策点、风险与开放问题，及部署配置速览
---

# MNBT 虚拟主机 × 魔方财务（idcsmart）对接 PRD — 附录

> 本文为 [虚拟主机×魔方财务 PRD](./hosting-idcsmart.md)（§1-§7）的附录部分，含里程碑与验收、决策点、风险与开放问题，以及部署配置速览。

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

---

## 11. 部署配置速览

> 摘要自魔方模块自带文档 `mf_modules/servers/mnbthost/README.md`，完整部署说明见该文件。

### 前置条件

- **魔方财务**：已安装并正常运行
- **MNBT**：`api/api.php` 已包含 `cfif/kt/zt/jc/tz/xf/czmm/zjmode/start/stop/ztcx`（本地仓库已实现，部署时确保服务器文件为最新版）
- MNBT 后台已配置宝塔节点（`MN_bt`，含 `btdh` 开通代号）

### 安装模块

1. 将 `mf_modules/servers/mnbthost/` 复制到魔方财务的 `/modules/servers/mnbthost/`
2. 清魔方缓存
3. 后台「产品 → 服务器」确认模块出现「梦奈宝塔虚拟主机」

### 服务器接口设置（含 btdh 提醒）

| 字段 | 值 |
|------|-----|
| 服务器 IP/域名 | MNBT 站点域名 |
| 端口 | 80/443 |
| **用户名** | `MN_bt.btdh`（宝塔开通代号，⚠️ 不是自增 id！） |
| **密码** | **调用密钥** `md5(ktmy . qmk)`（⚠️ 必填，虚拟主机节点 ktmy/qmk 添加时自动生成，留空会鉴权失败） |
| **Access Hash** | 系统 API 密钥（`$conf['api']`） |
| SSL | 按站点是否 HTTPS 勾选 |

> 调用密钥获取：MNBT 后台「宝塔列表」→ 找到对应节点 → **ktmy 列**点击👁️图标，显示的值即为 `md5(ktmy.qmk)`，直接复制填入魔方密码字段。虚拟主机节点的 `ktmy`/`qmk` 是后台添加宝塔时**自动生成**的（见 `admin/api/bt.php`），与 Docker 节点不同——Docker 节点可留空，虚拟主机节点不可留空。

### 产品配额

后台「产品 → 添加产品」→ 类型「服务器产品」→ 模块「梦奈宝塔虚拟主机」→ 配置配额：

| 配置项 | 说明 |
|--------|------|
| 网站空间 | MB（开通 webdx） |
| 数据库空间 | MB（开通 sqldx） |
| 流量 | MB，0=不限（开通 sizemax） |
| 域名绑定数 | 个（开通 ymbds） |
| 控制台地址 | 用户控制台 URL |

> 完整部署说明见魔方模块自带文档 `mf_modules/servers/mnbthost/README.md`（保持纯文本路径）。
