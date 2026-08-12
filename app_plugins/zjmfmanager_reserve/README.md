# 魔方财务代理分销（zjmfmanager_reserve）

以代理商身份分销[魔方财务](https://www.zjmf.com/)（cube_finance）产品的 MNBT 业务插件。

- 供应商：可维护多个魔方财务上游站点，各自独立的 API 账号、加价规则与启用状态
- 商品：同步弹窗勾选（按供应商）+ 手动添加 + 供应商/单品加价 + 上架管理
- 下单：本地余额（或已启用支付方式）购买，按订单所属供应商直通开通（主机归属代理商账号）
- 主机：状态/流量查看，开关机/重启/重置密码/重装
- 升级：配置升级 + 产品升降级（余额支付差额，按主机所属供应商路由）
- 管理端：供应商管理、商品管理、订单管理、主机管理、操作日志

依赖插件：`user_info`（认证）、`balance`（余额支付/退款）。

## 安装

1. 将 `zjmfmanager_reserve` 目录放入 `app_plugins/`。
2. 后台 → 系统管理 → 插件管理 → 安装 → 启用（自动执行 `install.sql` 建表）。
3. 供应商管理页新增供应商（名称、站点 URL、API 用户名、API 密钥、加价规则），保存后点击「连通测试」验证。
4. 商品管理页「同步商品」弹窗选择供应商并勾选商品同步，或「手动添加」商品；按需配置单品加价并上架。
5. 停用供应商后其商品自动不可售，主机操作与升级将被拒绝。

## 目录结构

```text
app_plugins/zjmfmanager_reserve/
├── plugin.json           # 插件元信息（声明依赖 user_info/balance）
├── bootstrap.php         # 主入口：注册钩子/路由/菜单/AJAX
├── install.sql           # 建表（supplier/product/order/host/log）
├── uninstall.sql         # 卸载清理
├── lib/
│   ├── CubeFinanceClient.php  # 上游 API 客户端（JWT 登录/请求封装）
│   ├── upstream.php           # ZjmfUpstream 服务层（按供应商路由：同步/开通/主机/升级）
│   └── zjmf.php               # 辅助函数 + 数据表操作 + 开通编排
├── views/
│   ├── layout.php             # 用户端公共布局
│   ├── shop.php               # 商品列表（按供应商分组）
│   ├── order.php              # 下单页
│   ├── orders.php             # 我的订单
│   ├── hosts.php              # 我的主机
│   ├── host.php               # 主机详情（状态/流量/操作）
│   ├── upgrade.php            # 升级页（配置/产品）
│   └── admin/                 # 管理端页面
│       ├── suppliers.php      # 供应商管理（新增/编辑/连通测试/启停/删除）
│       ├── products.php       # 商品管理（同步弹窗/手动添加/加价/上下架）
│       ├── orders.php         # 订单管理（供应商/状态筛选）
│       ├── hosts.php          # 主机管理（刷新状态）
│       └── logs.php           # 操作日志
└── assets/style.css           # 用户端样式
```

## 多供应商模型

- 每个供应商一行 `MN_plugin_zjmf_supplier`，保存独立的站点地址、API 账号、加价规则。
- 商品/订单/主机/日志均记录 `supplier_id`；下单、主机操作、升级按该字段路由到对应上游。
- JWT 缓存按供应商隔离（`runtime/cache/s{id}`），多供应商凭证互不串扰。
- 商品加价优先级：单品已配置加价 > 所属供应商加价。

## 关键流程

### 购买开通

```
商品列表（按供应商分组）→ 下单页（选周期/支付方式）
        → 创建本地订单(pending, 含 supplier_id) + MN_dd(lx=zjmf)
        → 支付网关确认 → mnbt_pay_settle_order() 触发 order.paid 钩子
        → 本插件按 ddh 前缀 ZJM 过滤 → 标记 paid → zjmf_open_host()
        → 校验供应商启用 → 按订单 supplier_id 调上游下单+余额抵扣 → 建主机映射（密码 authcode 加密）
        → 供应商缺失/停用或开通失败 → 订单置 failed + balance_add(refund) 自动退款
```

### 升级

```
主机详情 → 升级页 → 选择目标 → 试算差额（GET 页面端点，不产生提交）
        → 确认 → 创建升级订单（含 supplier_id）→ balance_deduct 扣差额
        → 按主机供应商提交上游（POST）→ 成功回填订单/更新主机缓存；失败自动退款
```

### 金额约定

所有金额以「分」整数存储；上游元金额通过 `toCents()` 转换，避免浮点误差。
加价比例以千分比存储（如 10 表示 +1%），固定加价直接存分。

## 联调注意点

以下端点/字段因上游版本与定制模块而异，已集中在 `lib/upstream.php` 便于调整
（详见 PRD §3.3 与开放问题 Q1/Q3）：

- 开通端点：`cart/checkout`（`ZJMF_CHECKOUT_PATH`），如上游为 provision 直通则修改此处
- 主机操作 func：`on/off/reboot/passwd/reinstall`（`zjmf_action_func()` 映射）
- 升级端点：`upgrade/upgrade_config_page|_post`、`upgrade/upgrade_product_page|_post`
- 响应字段解析均为防御式（`pickPrice`/`findId`/`findHostId`/`parseTrialPrice`）

## 安全说明

- 上游 API 密钥存入 `MN_plugin_zjmf_supplier.api_password`，编辑页不回显，日志脱敏
- 主机密码使用 `authcode()` 加密入库，页面默认打码
- 操作日志内容脱敏，不落明文密码/密钥
- 用户端全部操作经路由鉴权（`zjmf_require_user()`），管理员 AJAX 使用 `p_zjmf_*` 前缀并鉴权
