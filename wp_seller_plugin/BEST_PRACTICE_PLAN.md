# 最佳实践计划 — WordPress 虚拟主机销售插件（wp_seller_plugin）

> 目标：以工程最佳实践交付一个安全、可靠、可维护、可扩展的 WordPress 主机销售插件，复用 MNBT 现有 API 与用户控制台，向 cPanel 式体验靠拢。

---

## 1. 总体原则

1. **复用优先**：销售用 WordPress/WooCommerce，深度管理复用 MNBT 用户控制台，插件只做「编排 + 网关 + 用户中心」。
2. **适配器隔离**：MNBT 对接封装在独立适配器层，未来切换/增加面板（如直接接宝塔、接其他财务系统）不改业务层。
3. **幂等与补偿**：开通、续费等有外部副作用的操作必须幂等，失败可重试/回滚。
4. **安全默认**：密钥加密存储、输出转义、权限校验、最小暴露。

---

## 2. WordPress 插件开发最佳实践

### 2.1 代码规范

- **命名空间**：统一 `MnbtWp\`，类文件 `class-*.php`，文件名小写连字符。
- **表/选项前缀**：数据库表 `wp_mnbt_*`（`$wpdb->prefix . 'mnbt_'`）；选项 `mnbt_*`。
- **唯一前缀**：所有函数/常量/钩子用 `mnbt_` 前缀，避免与主题/其他插件冲突。
- **直接文件访问拦截**：每个 PHP 文件头部 `if (!defined('ABSPATH')) exit;`。
- **i18n**：文案统一 `__('...', 'wp-seller-plugin')`，文本域 `wp-seller-plugin`。
- **PHP 版本**：目标 PHP ≥ 7.4，使用类型声明与强类型。

### 2.2 Hooks 使用规范

- 初始化、加载类统一走 `plugins_loaded`；菜单走 `admin_menu`。
- 前台输出统一走 Shortcode/Block（`[mnbt_my_hosts]` 等），避免硬编码 HTML。
- 定时任务用 WP-Cron 注册（`wp_schedule_event`），**用 `transient` 加锁**防止并发重叠执行。
- WooCommerce 集成用其官方钩子：`woocommerce_payment_complete`、`woocommerce_order_status_*`，不监听私有事件。

### 2.3 数据层

- 全部用 `$wpdb->prepare()` 预处理，禁止拼接 SQL。
- 建表在激活钩子执行，带 `dbDelta()`；卸载删除自定义表需用户确认（`uninstall.php`）。
- 密钥（`mn_key`/`mn_keye`）用 WordPress 密钥加密（`$wpdb->prepare` + `wp_salt()` 派生加密），前台/日志绝不输出明文。
- 定时任务、列表页数据优先读本地表（通过 `ztcx` 同步），避免实时远程调用拖慢页面。

### 2.4 安全清单

| 项 | 要求 |
|----|------|
| 权限 | 后台所有 AJAX/页面校验 `current_user_can('manage_options')` |
| 转义 | 输出用 `esc_html/esc_attr/esc_url`；属性与 JS 上下文正确转义 |
| CSRF | 后台表单加 `nonce`；AJAX 校验 `check_ajax_referer` |
| SSRF | MNBT API 地址允许配置但请求目标限定 `https?://`，禁止内网地址（复用 MNBT 插件引擎同策略） |
| 错误信息 | 对用户隐藏密钥/内部堆栈，写入日志 |
| 上传 | 插件不接收文件上传（V1），避免文件处理面 |

---

## 3. MNBT 对接最佳实践

### 3.1 适配器接口（便于扩展）

```php
interface MnbtWp\Mnbt\HostProviderInterface {
    public function testConnection(): bool;
    public function createHost(array $params): array;   // 返回 ['ok'=>bool,'msg'=>string,'data'=>[...]]
    public function suspendHost(string $username): bool;
    public function resumeHost(string $username): bool;
    public function deleteHost(string $username): bool;
    public function renewHost(string $username, string $expireDate): bool;
    public function changePassword(string $username, string $password): bool;
    public function changePackage(string $username, array $quota): bool;
    public function startSite(string $username): bool;
    public function stopSite(string $username): bool;
    public function getHostStatus(string $username): array;  // 状态+配额
}
```

- `MnbtWp\Mnbt\Client`（实现类）封装 MNBT `api/api.php`。
- 业务层（`Provision`/`Billing`）只依赖接口，不依赖具体实现。

### 3.2 Client 网关要点

- **请求**：`wp_remote_post($url, ['timeout'=>15, 'body'=>$params])`，校验 `is_wp_error`。
- **鉴权**：每次请求拼装 `mn_bh/mn_key/mn_keye/mn_vs/username`。
- **超时与重试**：网络层失败（超时/连接失败）自动重试 2 次（间隔 1s/3s）；业务失败（`code!=200`）不重试。
- **错误归一化**：统一抛出 `MnbtWp\Mnbt\Exception`，`message` 用 MNBT `msg`，`context` 记录 request_id/params（脱敏）。
- **日志**：每次调用写 `mnbt_api_log` 表（时间、动作、参数摘要、响应 code/msg、耗时）。

### 3.3 开通流程（幂等 + 补偿）

```
状态机：pending → provisioning → active / failed
1. 前置校验：本地表该用户无 active 主机；参数完整
2. 生成用户名：wp_{userId}_{rand4}（≥6位）；生成初始密码
3. 调 kt：成功→写本地表 active，记录账号/密码/站点名/到期时间
4. 失败：若 kt 部分成功（宝塔已建站但本地写库失败）→ 调 tz 回滚
5. 通知：成功通知客户（含账号密码与控制台地址）；失败通知站长
```

### 3.4 到期与续费

- **每日 cron**：扫描 `datae` 到期的 active 主机：
  - 过期且在宽限期（可配，默认 0）内 → 发提醒；
  - 过期超宽限期 → 调 `zt` 暂停 + 本地状态 `expired`；
  - 续费订单支付成功 → 调 `xf` 更新到期时间 → 若已暂停调 `jc` 恢复。
- **续费商品**：生成按月的 WooCommerce 订阅/一次性续费商品，订单成功后走同一 `renewHost`。

### 3.5 状态与用量同步

- cron 每 10 分钟调 `ztcx` 更新本地 `mnbt_hosts`（状态、空间/数据库/流量用量），前台从本地表读。
- 用量单位为 MB（与 MNBT 一致），进度条按 `used/max` 计算。

---

## 4. 开发阶段计划

| 阶段 | 目标 | 交付物 | 完成标志 |
|------|------|--------|----------|
| **P0 脚手架** | 插件骨架可安装 | 主文件、激活建表、后台设置页、i18n 框架 | 后台可配置 MNBT 连接并保存 |
| **P1 网关打通** | MNBT 通信可用 | Client/Adapter/Logger、连接测试、错误归一化 | `cfif` 测试通过，日志可查 |
| **P2 购买闭环** | 能卖并能开通 | WooCommerce 商品扩展、支付回调开通、我的主机页、开通通知 | 支付后自动开通，前台可见 |
| **P3 管理闭环** | 全生命周期 | 启停/改密/续费/删除/升降级、到期定时任务、续费流程 | 管理操作可用，到期自动暂停/续费恢复 |
| **P4 体验增强** | 接近 cPanel | 用量进度条、控制台跳转、到期提醒邮件、管理端主机总览、审计日志 | 自助体验完善 |

> 每个阶段结束做一次**安全自审 + 回归测试**（WordPress 5.x/6.x、PHP 7.4/8.x、WooCommerce 8/9）。

---

## 5. 测试与发布

### 5.1 测试策略

- **单元测试**：PHPUnit + WP_Mock（针对 Client 签名、配额计算、错误归一化）。
- **集成测试**：本地 WordPress + 测试 MNBT 节点（可用 docker 起宝塔或 mock api/api.php）。
- **关键场景用例**：
  1. 支付成功→开通→前台可见；
  2. 重复支付回调不重复开通（幂等）；
  3. kt 失败可重试，无脏数据；
  4. 到期自动暂停、续费恢复；
  5. 密钥错误时测试连接给出明确错误；
  6. 禁用插件数据保留、卸载删除按确认执行。

### 5.2 发布

- 版本管理：SemVer；主文件版本 + `readme.txt` 同步。
- 分发：Git 仓库（git tag）+ 可选提交 WordPress.org（遵守其插件指南：无隐藏外链、GPL）。
- 升级：`upgrader_process_complete` 钩子做数据迁移（表结构变更用 `dbDelta`）。

---

## 6. 运维与监控

- **日志**：`mnbt_api_log` 保留 N 天，后台可查；关键操作（开通/删除/改密）记审计。
- **监控**：每日检查 `failed` 状态主机数量；API 连续失败计数超阈值发邮件提醒站长。
- **备份**：依赖 WordPress 数据库备份；插件自身不处理站点文件备份（V1）。

---

## 7. 未来演进（Backlog）

- SSO 单点登录跳转 MNBT 用户控制台（自动登录）。
- 流量超额限速/通知；空间/数据库用量实时刷新。
- 一键部署应用（对接 MNBT 部署能力）。
- 多面板适配器（直接对接宝塔 / 对接其他财务系统）。
- 多币种、优惠码、自动续费（WooCommerce Subscriptions 深度集成）。
