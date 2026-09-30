---
title: 更新日志
description: MNBT 版本更新记录（V1.85 ~ V1.60）
---

# 更新日志

## V1.85

**外部主机 API 协议兼容开关**

- 新增全局开关 `MN_config.api_compat`：`0`=新版协议（1.83+ 严格，默认），`1`=兼容 1.81 对接模块
- 后台入口：系统设置 → API 设置 → 「对外 API 协议模式」（`egn=setapicompat`）；代码侧统一走 `mnbt_api_compat_mode()`（`MPHX/function.php`）
- 兼容模式只放宽 1.83+ 新增的**行为约束**（跨节点归属校验、`kt` 站点名按 `max(id)+1`、`zjmode` 缺参清零、`xf` 真值解停、鉴权失败老文案），传输包络与响应结构两个版本一直一致
- 老版 `kt` 里恒真的「主机已存在」表达式与 `tz` 的资产级联删除属 bug/新能力，兼容模式刻意不还原
- 详见 [外部对接 API](/api/external)

**系统更新改造：改走 GitHub Release**

- 更新不再回连自建更新服务器（`check.php`），也不再校验 `authcode`；统一从 GitHub Release 拉包覆盖站点
- 新增 `MPHX/github_updater.php`（纯函数库，受 `IN_CRONLITE` 保护），承载配置读写、Release 元数据获取、流式下载与安全校验
- 后台入口：`admin/api/gg.php`（`egn=update` 执行更新）、`admin/api/update_setting.php`（`egn=upcheck` 检查、`egn=upset` 保存设置）；`admin/api/bt.php`（`egn=mnbt`）改用新检查器
- 界面：`admin/update.php` + `templates/default/admin/update.php`、`templates/tdesign/admin/update.php`（含 SPA `UpdateView.vue`）展示最新可用版本、Release 正文作为更新日志、包来源与下载通道，并提供「更新设置」区块（仓库 owner/repo、镜像增删、可选 Token）

**包来源优先级**

1. Release 自定义 `.zip` 附件（`zip` 内容即站点根）
2. 回落 GitHub 自动源码包（codeload），解压时剥掉顶层目录使内容对齐站点根

**下载通道（多候选）**

- 每个来源依次尝试：`github.com` 直连 → 配置镜像（原地址前加 `https://镜像/` 前缀）
- Release 元数据接口（`api.github.com`）也按同样顺序走镜像兜底，否则国内环境直连不通时明明有镜像却检查不到新版本；Token 只随直连发出，不会带给镜像
- 仓库与镜像在后台「更新设置」配置，默认 `1181469655/MNBT` + `https://gh-proxy.com/`，写入 `cf_up.php` 的 `update` 段（其余键原样保留）
- Token 仅用于提升 GitHub API 速率，只以「是否已设置」回显，不随下载请求发给镜像，也不明文存储回显

**安全与可靠性**

- 下载/API 地址 host 白名单：github.com、api.github.com、codeload.github.com、objects.githubusercontent.com 及配置镜像域；解析到内网/保留 IP 一律拒绝（防 SSRF）
- 流式下载：校验 `Content-Length` 与实际字节、`PK\x03\x04` zip 魔数，失败即删临时文件
- 解压前扫描 ZipArchive 条目名，拒绝绝对路径、盘符、`..` 段
- 覆盖前备份并在完成后还原 `config.php`、`cf_up.php`、`MPHX/SQ.php`、`install/install.lock`、`api/cookie/`；包自带的 `install.lock` 若站点原本没有会被删除，避免被判定为未安装
- `set_time_limit(600)` + `ignore_user_abort` + `register_shutdown_function` 兜底：任何中断路径都会还原改名后的后台目录、还原本地配置并清理临时包
- 升级 SQL 走版本化迁移链：`update/update_v<3 位或 4 位>_<slug>.sql` 按版本号升序执行，仅跑游标之后、目标版本以内的增量（详见下节）

**发版运维约定**

> 发版时请在 GitHub Release 挂载 zip 附件（内容即为站点根目录文件），否则将走源码包回落路径（自动剥顶层目录）。附件请确保不含本地配置文件的覆盖内容，运行时配置由更新流程的备份还原机制保护。

**版本号单一数据源**

- `$WEBQB`（`MPHX/BL.php`）是版本号唯一来源，新增格式化函数 `mnbt_version_num()`（`1850`→`"1.85"`）与 `mnbt_version()`（→`"V1.85"`）
- 安装向导（`install/index.php` 的徽章/升级文案、`install/install.api.php` 的接口 `vs` 字段与升级完成提示）、后台版本显示（`admin/api/bt.php`）全部改为从 `$WEBQB` 计算，不再写死字面量；发版改产品内版本只需动 `BL.php` 一处
- README 与 docs 首页的 shields.io 徽章是静态 Markdown（PHP 渲染不到），由 `tools/sync-version.sh` 从 `$WEBQB` 同步；发版流程：改 `BL.php` → `bash tools/sync-version.sh` → 追加 changelog 段落

**版本化迁移链**

- 新增 `MPHX/migrations.php`：按文件版本号排序、以游标表驱动依次执行的迁移引擎；任意老版本一键跳到目标版本，无需人工拼 SQL
- 游标表 `MN_dbver`（`version` PK / `file` / `applied_at`）独立记录每个版本何时应用，不塞进 `MN_config`，也留下审计；安装向导与 `install.sql`、`repair_tables.sql` 一并建表
- 迁移文件命名：`update/update_v<3位或4位>_<slug>.sql`；3 位取 `183`→`1830`，4 位取 `1860`→`1860`；不符合此命名的旧式单文件 `update.sql` 走一次性兼容路径执行后删除
- 语句切分支持 `DELIMITER $$` 与存储过程守护写法（v183/v184 的幂等「列存在则跳过」），引号/注释内的分隔符不参与切分
- 幂等容忍错误码：1050 表已存在、1060 列重复、1061/1826 索引重复（MySQL/MariaDB）、1091 无法删除不存在列/索引；其它错误码致命并停链，`update/` 目录保留、游标停在最后成功的版本，修复后可再次点击继续
- 基线播种：全新安装/升级完成后调用 `mnbt_migrations_seed($dbconfig, $WEBQB)` 把游标抬到当前版本，之后在线更新只跑更高版本的增量，不会重放历史迁移
- 发版约定：每个版本的增量放独立小 SQL 追加到 `update/`，每条迁移保持幂等；跨版本升级由客户端自动顺序执行游标之后的所有文件

## V1.83

**Docker 容器托管**

- 独立于主机业务的 Docker 容器托管模块，单容器模型（每用户最多创建一个容器）
- 独立认证：`docker/` 控制台使用独立 `docker_token` cookie，与 `admin_token`/`user_token` 隔离
- 独立表：`MN_docker_node`（节点）、`MN_docker_user`（用户）、`MN_docker_plan`（套餐）、`MN_docker_order`（订单）
- 宝塔 Docker API 封装：`MPHX/bt_docker.php`，支持容器列表/创建/启停/删除、镜像/应用商店/已安装应用、Compose 模板/数据卷等全部接口
- 用户端：`docker/` 控制台（我的容器、应用商店、镜像管理、数据卷、Compose 模板），支持容器创建进度追踪、端口映射可视化、应用参数中文说明
- 管理端：Docker 节点管理、套餐管理、用户管理、订单管理、到期软删流程（`active` → `expired` → `pruned` → 物理删除）
- 外部 API：`api/docker.php`（`mn_key` 鉴权），供第三方对接容器开通/续费/删除
- 升级 SQL：`update/update_v183_docker.sql`
- 文档：`docs/Docker_API.md`（内部 API 对接文档）

**前端设计升级**

- Docker 控制台整体改为浅色简约圆角设计，白色侧边栏 + 浅灰背景
- 新增 `docker.svg` 横向 logo，登录页与侧边栏统一使用
- 主题色从 `#3a7bd5` 升级为 `#2563eb`，圆角从 10px 提升到 14px
- 顶部栏毛玻璃效果（`backdrop-filter: blur`），输入框/按钮 focus 光晕
- 应用商店搜索框胶囊形圆角，卡片 hover 柔和投影

**模板开发文档更新**

- `templates/THEME_DEV.md` 新增 Docker 视图清单与主题开发说明
- 新增 `docker` scope 视图支持（`templates/{theme}/docker/`），缺页回退 `default`

## V1.81

**PHP 业务插件系统（P0 + P1）**

- 引擎：`MPHX/plugin.php`，启动挂载于 `common.php`
- 目录：`app_plugins/{slug}/`（`plugin.json` + `bootstrap.php`）
- 表：`MN_plugin`、`MN_plugin_option`（升级 SQL：`update/update_v181_plugin.sql`）
- 后台：系统管理 → 插件管理；侧栏可注入「插件」菜单（管理端 + 用户端）
- AJAX：`user/ajax.php` / `admin/ajax.php` 优先分发插件 `gn`
- 钩子：`boot`、`host.created/paused/unpaused/renewed/deleted`、`order.paid`、`cron`、`menu.*`、dashboard widgets
- P1：`mnbt_http_*`、`mnbt_register_widget`、`mnbt_register_settings_tab`
- 示例：`hello_demo`、`webhook_notify`（主机/订单 Webhook + HMAC）
- 文档：`app_plugins/README.md`

**插件引擎扩展（P2 路由系统）**

- `mnbt_register_home()`：接管站点根 `/` 的响应（重定向或渲染自定义首页）
- `mnbt_register_route($method, $path, $cb)`：通用路由，支持命名参数 `{id}`、尾斜杠可选
- `index.php` 提供回退路由分发；`_router.php` 支持 PHP 内置开发服务器
- Nginx/Apache 需配置 `try_files` / `RewriteRule` 将未命中请求转发至 `index.php`
- 示例：`home_demo`（首页接管 + 通用路由）

**支付插件系统（P3 重构）**

- 支付架构插件化：易支付、支付宝官方均改为独立插件（`app_plugins/epay/`、`app_plugins/alipay_official/`）
- 新增 API：`mnbt_register_payment`、`mnbt_pay_dispatch_gateway`、`mnbt_pay_settle_order`、`mnbt_get_enabled_payment_methods`、`mnbt_save_payment_methods`
- 新增统一支付设置页 `admin/pay_settings.php`：仅管理启用/禁用、显示名、图标、排序
- 支付插件 API 凭证由插件自身设置页维护（`MN_plugin_option`），与系统层解耦
- 客户端 `user/pay.php` 重写为插件分发；模板 `webgl.php`、`set.php` 动态渲染付款方式
- 异步/同步回调改由 P2 通用路由处理：`/pay/{slug}/notify`、`/pay/{slug}/return`
- 易支付插件支持自动迁移旧 `MN_config.hxe/hxr/hxt` 配置
- 旧文件清理：`user/notify_url.php`、`user/return_url.php`、`MPHX/lib/submit.class.php`、`notify.class.php`、`core.function.php`、`md5.function.php`
- 升级 SQL：`update/update_v181_p3_pay.sql`（新增 `MN_config.pay_methods` 字段）

**安装向导增强**

- 新增「站点与管理员」步骤：控制面板名称、站长 QQ、公告、管理员账号/密码
- 安装完成写入 `MN_config`；完成页展示登录信息（不再固定 admin/123456）

**文档**

- 插件开发手册：`app_plugins/PLUGIN_DEV.md`

## V1.80

**前端主题系统**

- 用户端 / 管理端视图迁入 `templates/`，支持独立切换与缺页回退
- 主题引擎 `MPHX/theme.php`（`mnbt_render` / `mnbt_theme_url` / `mnbt_theme_asset` / `mnbt_asset_url`）
- 后台「系统管理 → 前端模板」可视化切换；配置文件 `active_user_theme` / `active_admin_theme`
- 主题资源隔离：公共资源 `imsetes/` 与主题私有 `templates/*/assets/`（缺文件回退 default）
- 主题文档：`templates/README.md`、`templates/THEME_DEV.md`

**UI 与体验**

- 默认主题登录页改为简洁圆角白卡片（用户端 + 管理端）
- 管理端系统设置页改为现代卡片布局

**修复**

- 安装 SQL 跳过空语句，避免 `Query was empty`
- 用户端刷新用量 `sxsyxx` 错误 include 路径导致 500
- 默认文档读取误用 SetIndex 导致「默认文档不能为空」

## V1.79

**PHP 8.x 全兼容**

- 修复 `each()`、`count($string)`、`get_magic_quotes_gpc()`、`strftime` 等全部 PHP 8 废弃语法
- `var` 属性声明改为 `public`，移除 PHP 4 构造器
- 安装向导 PHP 版本检查放宽为 `>= 7.4.0`

**宝塔 API 重构**

- 合并 4 份重复的 bt_api 操作类（`bt_api` / `bt_api_set` / `win_bt_api` / `bt_api_rj`）到统一 `MPHX/bt_api.php`
- 修复 `stopjq()`、`urllist()` 命名冲突，添加向后兼容别名
- 新增 Gzip API：`get_gzip_status()` / `set_gzip()` / `remove_gzip_status()`
- 新增静态缓存 API：`get_static_cache()` / `set_static_cache()` / `remove_static_cache()`

**SQL 安全**

- DB 类新增 `prepare()` / `get_row_prepare()` / `get_all_prepare()` / `query_prepare()` / `count_prepare()`（MySQLi + SQLite PDO）
- 全部约 150 处 SQL 查询迁移至参数化查询，彻底消除 SQL 注入

**代码架构优化**

- `admin/ajax.php`（1106 行）拆分为 20 行路由 + 10 个模块文件（`admin/api/`）
- `user/ajax.php`（1209 行）拆分为 35 行路由 + 11 个模块文件（`user/api/`）
- 创建 `MPHX/Response.php` 统一响应类 + `MPHX/function.php`（`json_exit` 系列函数）
- 替换所有 `exit('{"code":...}')` 为统一响应函数

**操作日志系统**

- 修复 `logjl()` 函数中 `$DB` → `$DBZHER` 参数引用
- 启用管理端 19 处原被注释的日志调用
- 新增强制 HTTPS/重置密码/密码访问/SSL/邮箱绑定等 26 处用户端日志
- 管理后台日志查看页 `admin/list.php?gn=log`，支持搜索/分页/清空

**PHPMailer 升级**

- PHPMailer 5.2.28 → 6.12.0（Composer，`vendor-dir` → `mail/vendor`）
- 重写 `mail.php` / `admin/mail.php`，改用 `use PHPMailer\PHPMailer\PHPMailer`，try/catch 异常处理，UTF-8

**用户端新增功能**

- Gzip 配置页面（`user/set.php?gn=gzip`）：开关/压缩级别/最小长度/MIME 类型
- 缓存配置页面（`user/set.php?gn=cache`）：文件后缀/过期时间（秒/分钟/小时/天）
- URL 监控 + 资源监控（`user/monitor.php`）：状态码规则/内容匹配/SSRF 防护/失败计数
- 监控检测日志（`user/monitor_log.php`）
- 通知日志（`user/notice.php`）：到期提醒/流量超额/监控告警，筛选/搜索/分页/全部已读
- 功能菜单重排（`user/sy.php`）：Gzip/缓存移到防盗链后方，修复合提前闭合
- 流量趋势图升级：标题栏环比百分比，柱状叠加紫色折线，图例顶部显示
- 一键部署修复：`qk` 多值兼容、空数据提示、JS `==` 赋值 bug

**修复列表**

- `foreach(null)` / `json_decode(null)` 空保护
- `addzj` INSERT NOT NULL 约束（`$aedfs`/`$sqlfs` 默认 `'0'`）
- `gglist` 双重输出（`return` → `exit()`，`send_post('null')` → `send_post([])`）
- 数据库/FTP 账号重复检测改为本地查 `MN_zj` 表
- 用户面板 Chart.js 自适应（`maintainAspectRatio`）
- `send_post()` 兼容 PHP 8 `CURLOPT_POSTFIELDS` 数组 + `http_build_query`

**MNBT 节点插件系统**

- 插件注册/心跳/异步任务队列
- 违禁词扫描（定时全量 + 增量）
- `plugins/mnbt_connector/` 插件包

## V1.78

- 新增域名监控/文件监控功能，新增邮箱绑定与通知，新增负载均衡配置页面（开发中）

## V1.70

- 一键部署引擎全面升级（10 种自定义操作），支持分片上传大文件，新增 SSL 证书自动申请

## V1.60

- 首个公开版本，完成基础主机分销功能，对接宝塔面板 API，集成易支付接口
