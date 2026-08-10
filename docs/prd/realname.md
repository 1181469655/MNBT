---
title: 实名认证插件 PRD
description: MNBT 实名认证插件：三要素认证 + 身份证 OCR 本地识别 + 自动审核 + 购买支付拦截（待审批）
---

# 实名认证插件 PRD

> 版本：v1.0（已批准）
> 日期：2026-08-10
> 状态：已批准
> 关联文档：[插件开发手册](../development/plugin/guide.md)、[菜单与页面](../development/plugin/menu.md)、[钩子与数据库](../development/plugin/hooks.md)、[支付插件系统](../development/plugin/payment.md)、[P3 支付公共函数](../api/overview.md)

---

## 1. 背景与目标

### 1.1 背景

MNBT（梦奈宝塔主机系统）通过 `user_info` 插件建立了独立用户体系（`MN_plugin_user`），并在此基础上衍生出多个商店插件（`hosting_shop` 主机售卖、`docker_shop` Docker 售卖、`domain_shop` 域名售卖）与 `balance` 余额插件。所有商店购买最终统一走 `mnbt_pay_dispatch_gateway()` 分发到支付插件完成收款。

当前**没有任何身份核验机制**：任何人注册账号即可购买产品并支付。为满足合规要求（网络实名制）与风控需求，需在支付环节前引入**实名认证**：用户提供姓名、手机号、身份证号并上传身份证正反面与手持身份证照片，系统通过**本地 OCR** 自动识别身份证正面信息并**自动审核**，未实名用户无法发起支付购买。

### 1.2 核心约束（用户明确要求）

| 约束 | 说明 |
|------|------|
| 全程不调用外部 API | OCR 识别、三要素校验、自动审核全部本地完成，无任何第三方接口（不调公安/运营商核验接口） |
| 本地算法识别 | 前端 tesseract.js（WASM）做 OCR，模型与库随插件打包，同源加载 |
| 手持照片不识别 | 手持身份证照片仅作为存档审核材料上传，不做 OCR |
| 购买前拦截 | 未实名用户禁止发起支付（拦截点为支付分发，覆盖所有购买入口） |
| 用户体系 | 仅针对插件用户（`MN_plugin_user`，user_info 体系） |

### 1.3 名词定义

| 名词 | 说明 |
|------|------|
| 三要素 | 姓名（real_name）、手机号（phone）、身份证号（id_card） |
| OCR 结果 | 前端 tesseract.js 从身份证正面图识别出的姓名（ocr_name）与身份证号（ocr_id_card） |
| 自动审核 | 服务端本地算法综合校验（详见 §6），通过→`approved`，失败→`rejected` |

### 1.4 范围

| 期次 | 内容 |
|------|------|
| **P0（本期）** | 用户端实名申请/状态页、三要素提交、三张照片上传、tesseract.js 本地 OCR 识别身份证正面、服务端自动审核、管理端审核列表/详情/通过/驳回、**支付分发统一拦截**、照片鉴权访问、敏感数据加密存储 |
| **P1（后续）** | 人工复核队列优化、认证到期策略、按产品类型差异化要求、审计日志导出 |

---

## 2. 总体架构

### 2.1 架构图

```
┌───────────────────────────────  用户端（浏览器）  ───────────────────────────────┐
│  /realname/apply  实名申请页                                                       │
│   ├─ 填写 姓名 / 手机号 / 身份证号                                                  │
│   ├─ 上传 身份证正面 / 反面 / 手持照片（canvas 压缩）                                │
│   ├─ tesseract.js（WASM + chi_sim 模型，同源本地加载）                              │
│   │     └─ 识别正面图 → 提取 姓名 + 身份证号 → 自动回填表单（可修改确认）              │
│   └─ 提交 POST /realname/api/submit                                                  │
└──────────────────────────────────┬─────────────────────────────────────────────┘
                                    ▼
┌───────────────────────────────  MNBT 服务端（PHP）  ─────────────────────────────┐
│  lib/auth.php                                                                       │
│   ├─ 三要素格式校验：身份证 18 位校验码算法 + 出生日期/性别/地区解析，手机号 11 位号段校验 │
│   ├─ 一致性校验：OCR 姓名 vs 表单姓名、OCR 身份证号 vs 表单身份证号                    │
│   ├─ 照片存储：runtime/realname/{user_id}/（非 Web 可访问目录，随机文件名）             │
│   ├─ 敏感信息：身份证号 AES 加密存储，展示一律掩码                                     │
│   └─ 自动审核 → 写 plg_realname_auth（pending/approved/rejected）                     │
└──────────────────────────────────┬─────────────────────────────────────────────┘
                                    ▼
┌──────────────────────────────  购买拦截（统一）  ────────────────────────────────┐
│  mnbt_pay_dispatch_gateway() 新增 filter 钩子（核心引擎 +4 行，唯一核心改动）          │
│   pay.dispatch.before：                                                              │
│    ├─ 无已登录插件用户 → 放行（实名体系仅针对插件用户）                                │
│    ├─ 已实名 approved     → 放行                                                      │
│    └─ 未实名/审核中/被驳回 → 输出引导提示（跳转实名页）并终止支付                        │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 模块命名

| 项 | 值 |
|----|----|
| 插件目录 | `app_plugins/realname/` |
| 插件 ID | `realname` |
| 显示名 | 实名认证 |
| 用户端页面 | `user/plugin.php?p=realname&page=apply`（申请） / `...&page=status`（状态） |
| 用户端 API | P2 路由 `/realname/api/*`（参考 user_info/hosting_shop 模式） |
| 管理端页面 | `admin/plugin.php?p=realname&page=audits`（tdesign 后台通过 iframe 加载） |
| 数据表 | `plg_realname_auth`（install.sql） |
| 照片目录 | `runtime/realname/{user_id}/`（`.gitignore` 忽略，非 Web 直接可访问） |

### 2.3 依赖

| 依赖 | 说明 |
|------|------|
| `user_info` 插件 | 必须：认证绑定 `MN_plugin_user`，取 `user_info_auth_current()` 登录态 |
| `requires_mnbt` | `1.81`（需 P2 路由 + P3 支付 dispatch） |
| tesseract.js | v5，库 + `chi_sim.traineddata`（best_int 版约 1.7MB）打包进 `assets/ocr/`，同源加载不触网 |

---

## 3. 数据模型

### 3.1 表 `plg_realname_auth`

```sql
CREATE TABLE IF NOT EXISTS `plg_realname_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,                 -- MN_plugin_user.id
  `username` varchar(64) NOT NULL DEFAULT '', -- 冗余用户名，便于后台展示
  `real_name` varchar(64) NOT NULL DEFAULT '',-- 姓名（明文）
  `phone` varchar(20) NOT NULL DEFAULT '',    -- 手机号（明文）
  `id_card` varchar(255) NOT NULL DEFAULT '', -- 身份证号（AES 加密存储）
  `front_img` varchar(255) NOT NULL DEFAULT '', -- 身份证正面（相对 runtime/realname 路径）
  `back_img` varchar(255) NOT NULL DEFAULT '',  -- 身份证反面
  `hand_img` varchar(255) NOT NULL DEFAULT '',  -- 手持身份证
  `ocr_name` varchar(64) NOT NULL DEFAULT '',   -- OCR 识别的姓名
  `ocr_id_card` varchar(64) NOT NULL DEFAULT '',-- OCR 识别的身份证号（AES 加密存储）
  `status` varchar(16) NOT NULL DEFAULT 'pending', -- pending/approved/rejected
  `audit_note` varchar(255) NOT NULL DEFAULT '',   -- 审核备注（自动失败原因 / 管理员驳回原因）
  `created_at` varchar(50) NOT NULL DEFAULT '',    -- 提交时间
  `updated_at` varchar(50) NOT NULL DEFAULT '',    -- 最后更新时间
  `audited_at` varchar(50) NOT NULL DEFAULT '',    -- 审核时间
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

说明：
- **一人一记录**：`user_id` 唯一，重复提交即覆盖更新（重新审核）。
- 身份存在状态机：`pending → approved` / `pending → rejected`；被驳回后可重新提交（置回 `pending` 并覆盖材料）。

### 3.2 敏感数据存储策略

| 数据 | 存储方式 | 理由 |
|------|----------|------|
| 身份证号 | AES-256-CBC 加密（密钥来自插件 option，`mnbt_plugin_option_get`） | 防脱库泄露；展示时一律掩码 `110***********1234` |
| OCR 身份证号 | 同上加密 | |
| 姓名/手机号 | 明文（用于后台人工复核），后台列表展示掩码 | |
| 三张照片 | 存 `runtime/realname/{user_id}/`（不在 Web 根可访问路径），随机文件名，**仅通过鉴权下载接口访问** | 防 URL 直接泄露 |

### 3.3 照片访问鉴权

- 下载接口：`mnbt_register_route('GET', '/realname/api/img', ...)`，参数 `id`（auth 记录）与 `type`（front/back/hand）。
- 权限规则：
  - 已登录插件用户且 `auth.user_id == 当前用户` → 可看本人照片；
  - 管理员（`mnbt_plugin_require_admin`）→ 可看全部。
- 响应带 `Content-Disposition: inline` + `X-Content-Type-Options: nosniff`。

---

## 4. 用户端流程

### 4.1 页面路由

| 路径 | 页面 | 说明 |
|------|------|------|
| `GET /realname/apply` | 申请/重新提交页 | 未认证或已驳回可提交；已认证通过跳转状态页 |
| `GET /realname/status` | 状态查看页 | pending 显示等待审核、approved 显示认证信息（掩码）、rejected 显示原因 |
| `GET /realname/api/me` | 当前认证状态 API | 返回 status + 掩码信息 |
| `POST /realname/api/submit` | 提交认证 | 见 §4.4 |
| `GET /realname/api/img` | 照片下载 | 鉴权后输出图片 |

用户端页面复用 `hosting_shop` / `user_info` 的 PHP 视图风格（`views/` 目录 + `mnbt_render` 同构），并通过 `mnbt_register_menu('user', ...)` 注册「实名认证」菜单分组（或在购买被拦截时引导跳转）。

### 4.2 申请表单

| 字段 | 类型 | 校验（前端） | 校验（服务端） |
|------|------|--------------|----------------|
| 姓名 | 文本 | 2~20 字符 | 长度 2~20 |
| 手机号 | 文本 | 11 位 | 正则 `^1[3-9]\d{9}$` + 号段表 |
| 身份证号 | 文本 | 18 位 | 18 位校验码算法（GB 11643-1999） |
| 身份证正面 | 图片 | ≤8MB，jpg/png | 扩展名+GD 尺寸校验 |
| 身份证反面 | 图片 | ≤8MB，jpg/png | 同上 |
| 手持照片 | 图片 | ≤8MB，jpg/png | 同上 |

- 图片前端先 canvas 压缩：最长边 1280px、JPEG 0.85，限制体积，减小上传与 OCR 输入。
- 提交成功后前端已展示 OCR 识别结果（姓名/身份证号），用户可确认或修正后提交。

### 4.3 OCR 识别流程（tesseract.js，全本地）

1. 用户选择身份证正面图后，前端用 canvas 压缩并按 2:3 比例（85.6mm×54mm 标准卡）预裁切。
2. 加载 `assets/ocr/tesseract.min.js` + `chi_sim.traineddata`（同源，不触网），`Tesseract.recognize(image, 'chi_sim', ...)`。
3. 识别文本后本地解析：
   - **身份证号**：优先匹配 `^\d{17}[\dXx]$` 模式的 18 位串（可选 `x`），再走校验码算法二次确认；
   - **姓名**：在「姓名」标签行之后、或文本块中姓氏（赵钱孙李等《百家姓》开头字）优先的行提取。
4. 识别结果回填表单对应字段，用户**必须确认**（可手动修正）后才能提交。
5. 全程无网络请求；模型失败/加载慢时给出提示，允许用户手动填写（此时自动审核的一致性校验会降低通过率，提示手动审核）。

### 4.4 提交接口 `POST /realname/api/submit`

入参：`real_name`、`phone`、`id_card`、`front_img`、`back_img`、`hand_img`（已上传后的文件 token）、`ocr_name`、`ocr_id_card`（前端识别结果，用于一致性比对，不作为唯一依据）。

流程：
1. 登录校验（`user_info_auth_current`）。
2. 文件上传落盘（§3.2 目录 + 随机名），返回 token。
3. 服务端三要素格式校验（§6.1）。
4. 服务端一致性校验（§6.2）：**重新独立计算**——身份证校验码 + OCR 文本中再提取身份证号？OCR 文本不回传，则以表单 id_card 为主；一致性以「表单 id_card 合法」+「表单 real_name 与 ocr_name 比对 + 表单 id_card 与 ocr_id_card 比对」执行。
5. 写入/覆盖 `plg_realname_auth`，状态置 `pending`。
6. 自动审核（§6.3）立刻执行，返回最终状态与原因（通常即时完成）。

---

## 5. 自动审核算法（全本地，无 API）

### 5.1 身份证号校验（GB 11643-1999）

```
1. 格式：18 位，前 17 位数字，末位数字或 X（不区分大小写）
2. 地区码（前 6 位）：行政区划表内存校验（省市两级）
3. 出生日期（第 7-14 位）：YYYYMMDD，真实存在（闰年处理）
4. 顺序码（第 15-17 位）：000~999
5. 校验码（第 18 位）：加权因子 [7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2]
   余数映射 ['1','0','X','9','8','7','6','5','4','3','2']
```

### 5.2 手机号校验

- 正则 `^1[3-9]\d{9}$`（11 位）。
- 号段表：3 开头的 30-39 号段、4 开头 40-49（虚拟运营）、5/6/7/8/9 开头主流号段；号段表内置常量，便于更新。

### 5.3 一致性校验（"本地三要素验证"）

由于不调公安接口，本地三要素验证 = **格式合法性 + 交叉一致性**：

| 检查项 | 规则 |
|--------|------|
| 姓名 | 非空，2~20 字；OCR 姓名与表单姓名完全一致（忽略空格/繁体不做匹配则仅宽松判定，可配 `allow_ocr_name_diff` 选项） |
| 身份证号 | 通过 §5.1 全部校验 |
| 手机号 | 通过 §5.2 |
| OCR 身份证号 vs 表单身份证号 | 完全一致（忽略大小写 X/x） |
| 表单身份证号解析出的出生日期 | 需为 18 岁以上（成年校验，可配置 `min_age` 默认 18） |

### 5.4 自动审核结论

| 结论 | 条件 | 后续 |
|------|------|------|
| `approved` | 5.1 + 5.2 + 5.3 全部通过 | 允许支付 |
| `rejected` | 任一硬性校验失败（身份证不合法、手机号不合法、OCR 身份证号不一致、年龄不足） | 展示原因，可重新提交 |
| `pending` | OCR 姓名无法比对（前端 OCR 失败转手动填写）且其余通过 | 进入人工复核；管理员可快速通过 |

> 配置项（`mnbt_plugin_option`）：`min_age`（默认 18）、`allow_ocr_name_diff`（默认 false，即要求姓名一致）、`require_hand_photo`（默认 true）。

---

## 6. 管理端

### 6.1 页面与菜单

- `mnbt_register_page('admin', 'audits', 'admin/audits.php', '认证审核')`
- `mnbt_register_page('admin', 'audit_detail', 'admin/audit_detail.php', '审核详情')`
- `mnbt_register_menu('admin', ...)`：独立分组「实名认证」→ 子菜单「审核列表」（icon `mdi-account-check`）
- tdesign 后台通过 iframe 加载 `admin/plugin.php?p=realname&page=audits`（现有 `PluginPageView` 机制，无需改主题）

### 6.2 审核列表（audits.php）

| 列 | 内容 |
|----|------|
| ID / 用户名 | MN_plugin_user.username |
| 姓名 | 掩码 `张*` |
| 手机号 | 掩码 `138****1234` |
| 身份证号 | 掩码 `110***********1234` |
| 状态 | pending/approved/rejected 徽标 |
| 提交/审核时间 | |
| 操作 | 查看详情 / 直接通过 / 驳回（需填原因） |

- 筛选：按状态、关键字（用户名/姓名）。
- 管理端 AJAX：`mnbt_register_ajax('admin', 'realname_admin_list'/'realname_admin_approve'/'realname_admin_reject')`。

### 6.3 审核详情（audit_detail.php）

- 三张照片（正面/反面/手持）放大查看，支持缩放（CSS `object-fit: contain` + 点击大图）。
- 显示：表单三要素（掩码）、OCR 结果、自动审核结论与原因。
- 操作：通过（含"强制通过"备注）、驳回（填原因）。
- 身份证号明文：管理员输入后台密码二次确认后可见（可选，P0 提供"解密查看"按钮）。

---

## 7. 购买拦截（核心能力）

### 7.1 拦截点

所有购买（`user/pay.php` 部署程序、`hosting_shop`、`docker_shop`、`domain_shop`、`balance` 充值）最终统一调用 `mnbt_pay_dispatch_gateway()` 分发支付。在该函数**开头**新增 filter 钩子：

```php
// MPHX/plugin.php  mnbt_pay_dispatch_gateway() 函数体内第一行（唯一核心改动，+4 行）
$guard = function_exists('mnbt_apply_filters') ? mnbt_apply_filters('pay.dispatch.before', null, $type, $order_context) : null;
if (is_string($guard) && $guard !== '') {
    if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
    echo $guard;
    exit;
}
```

### 7.2 拦截规则（实名插件注册的 filter）

```
pay.dispatch.before:
  $user = user_info_auth_current()          // 未登录 user_info → 无插件用户 → 放行
  if (!$user) return null
  $auth = 查 plg_realname_auth WHERE user_id
  if ($auth && status == 'approved') return null   // 已实名 → 放行
  // 未实名 / 审核中 / 已驳回 → 阻止
  return html(提示 + 「前往实名认证」按钮链接 /realname/apply)
```

- **拦截范围**：仅插件用户（user_info 体系）发起的所有支付（含商店购买与余额充值）。
- 核心 `MN_hack` 用户（无插件账号）发起支付 → 无插件用户登录态 → 放行（符合"仅插件用户"约束）。
- 被拦截时输出友好提示页（不产生订单），用户点击前往 `/realname/apply`。

### 7.3 为什么必须改核心引擎（唯一核心改动）

- 路由同名拦截只能覆盖走 P2 路由的下单接口（`/shop/api/create_order`、`/docker-shop/api/create_order`、`/domain/buy`），**无法覆盖** `user/pay.php`（实际文件，不经路由）与 `balance` 的充值入口。
- 统一在支付分发处拦截，一处覆盖所有支付入口，改动仅 4 行且风险极低（新增一个不存在的 filter 调用，无插件时 `mnbt_apply_filters` 返回 null 直接放行）。

---

## 8. 安装 / 卸载

- **安装**：`install.sql` 建表；`bootstrap.php` 注册路由/页面/菜单/filter。
- **卸载**：`uninstall.sql` 删表；删除插件 option；**照片文件保留**（`runtime/realname/` 属运行时数据，随站点清理策略处理）——PRD 默认不删照片，避免误删证据材料，可在卸载确认时提示管理员手动清理。
- 依赖检测：`requires_plugins: ["user_info"]`（引擎已有依赖检查）。
- 升级：`requires_mnbt: "1.81"`。

---

## 9. 安全与隐私清单

- [ ] 所有写操作校验登录：用户端 `user_info_auth_current`，管理端 `mnbt_plugin_require_admin`
- [ ] 照片目录不在 Web 可访问路径，仅经鉴权下载接口输出
- [ ] 身份证号 AES 加密存储；所有展示掩码；管理员解密查看需二次密码
- [ ] 上传类型白名单（jpg/png）+ GD `getimagesize` 校验 + 文件大小限制；随机文件名防路径穿越
- [ ] 服务端重新校验三要素（不信任前端结果）
- [ ] AJAX/路由 `gn` 使用 `p_realname_*` / `/realname/*` 前缀
- [ ] 输入 `htmlspecialchars` 输出；`prepare` 参数化 SQL
- [ ] 拦截输出不泄露他人信息
- [ ] 密钥（AES key）生成后存插件 option，不落代码

---

## 10. 文件结构

```
app_plugins/realname/
├── plugin.json                  # id/name/version/requires_mnbt/requires_plugins
├── bootstrap.php                # 路由/页面/菜单/filter/AJAX 注册
├── install.sql                  # plg_realname_auth 建表
├── uninstall.sql                # 删表
├── lib/
│   ├── auth.php                 # 核心库：认证记录 CRUD、自动审核、拦截 filter、照片上传/下载
│   └── idcard.php               # 身份证 18 位校验码/出生日期/性别/地区 + 手机号号段校验
├── admin/
│   ├── audits.php               # 审核列表页
│   └── audit_detail.php         # 审核详情页
├── views/
│   ├── apply.php                # 用户申请页
│   └── status.php               # 用户状态页
└── assets/
    ├── ocr/
    │   ├── tesseract.min.js     # tesseract.js 库（同源）
    │   └── chi_sim.traineddata  # 中文模型（best_int 版约 1.7MB，同源加载）
    ├── realname.js              # 前端 OCR 与表单逻辑
    └── realname.css
```

---

## 11. 测试清单

- [ ] 插件安装/启用/卸载正常，表与 option 正确清理
- [ ] 提交合法三要素 + 清晰身份证 → 自动 `approved`，可正常支付
- [ ] 身份证号校验码错误 / 出生日期非法 / 手机号号段非法 → `rejected` 且原因明确
- [ ] OCR 姓名/身份证号与表单不一致 → `rejected`（或按配置转人工）
- [ ] OCR 失败手动填写 → `pending` 转人工，管理员可通过/驳回
- [ ] 未实名用户发起 `/shop`、`/docker-shop`、`/domain/buy`、`user/pay.php`、充值 → 全部被拦截并引导实名
- [ ] 已实名用户以上入口全部放行
- [ ] 照片仅本人/管理员可查看；未登录访问 403
- [ ] 身份证号后台掩码展示；解密查看需二次密码
- [ ] 被驳回后重新提交覆盖记录并可再次审核
- [ ] 无 user_info 插件（或未启用）时安装失败且有明确提示
- [ ] 核心引擎 filter 改动在未安装实名插件时零影响（支付一切如常）

---

## 12. 待确认事项

1. **核心引擎最小改动**（§7.3，`mnbt_pay_dispatch_gateway` +4 行）是否可接受？备选：仅拦截商店 P2 路由下单接口（不改核心，但覆盖不了 `user/pay.php` 与充值）。
2. **tesseract.js 模型体积**（已确认）：`chi_sim.traineddata` 选用 best_int 版仅约 1.7MB，随插件打包，首次识别较快。
3. **实名与充值的关系**：余额充值是否同样要求实名（当前设计为：是，统一拦截）？
