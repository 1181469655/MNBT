---
title: 更新日志
description: MNBT 版本更新记录（V1.88 ~ V1.60）
---

# 更新日志

## V1.88

**插件化收口与售卖闭环**

- **官网首页迁回 official_site 插件**：删除核心 `MPHX/frontend.php` 与 `index.php` 主页调度，站点根路径 `/` 由 official_site 插件 `mnbt_register_home` 兜底接管渲染，保持"插件优先、默认兜底"语义（shop_frontend 等第三方接管不受影响，`home_enable=false` 时回落跳转用户面板）；主页 SPA 源码随插件走（`boot.srcBase` / `boot.srcAliases` 指向插件目录，`@/shared/` 仍复用主题共享目录）；`MN_config.home_*` 11 列配置迁移进插件 options（`update_v188` SQL 先拷贝后删列，插件引导另有直读兜底）；核心 theme.php 主题体系收窄为 user/admin/docker 三端，主页内容设置移至后台「官网内容 → 主页设置」。**自建主页/魔改主题的用户升级前请阅读插件文档确认接管顺序**
- **账户中心（account）SPA 收回 user_info 插件**：删除 `templates/tdesign/account/`，入口页与 SPA 源码移入 `app_plugins/user_info/`，`/account/*` 路由 URL 不变；用户主题为 tdesign 时直接使用插件自带入口页，其余主题回退插件 Layui 视图
- **主机/Docker 售卖补齐自助续费闭环**：hosting_shop 与 docker_shop 两插件此前只有购买开通与到期清理，现复用插件订单表新增续费单（`order.paid` 钩子分流到续费应用），延长到期并同步宝塔到期/容器状态；支付走现有网关分发（含余额支付），0 元续费直接应用；用户端两插件各新增续费页与资产入口，account 资产卡片加续费弹窗（周期价格、支付方式、续费后到期预览、pruned/paused 警告），套餐下架的资产提示联系管理员

**新功能**

- **虚拟主机计划任务**：用户端面板新增「计划任务」——定时备份网站/备份数据库/日志切割/访问 URL，周期支持每 N 分钟/每小时/每天/每 N 天，可立即执行、暂停启用、查看执行日志、编辑，每主机上限 10 个。宝塔 crontab 为面板级接口，本功能做了完整租户隔离与防提权：新增 `MN_cron_task` 归属登记表，列表按登记过滤、全部写操作先校验归属再下发宝塔；任务类型白名单排除 toShell/toPython（任意命令执行），bt_api 亦不封装相关调用；站点名/库名/URL 由服务端从会话主机数据派生，URL 任务限本站点已绑定域名（防节点侧 SSRF）
- **宝塔列表通信检测拉取版本号**：`btztjc` 检测改用新增的 `btapi_version()`（`/system?action=GetSystemTotal`）拉取面板版本号，成功时显示「通信正常(版本号)」；拉取失败（API 未开启、调用 IP 未加白名单、连接失败、非 JSON 响应）统一引导用户到宝塔 面板设置 → API接口 检查接口开关与本系统服务器 IP 白名单

**文件管理改版（tdesign 用户端）**

- **用户端回收站下线**：宝塔文件回收站（`Get_Recycle_bin` 等）返回的是**节点全局**数据，租户可见/可操作其他站点删除的文件，且"清空回收站/回收站开关"作用于整个节点。删除 `user/api/file.php` 的 5 个 `recycle_*` 动作与 `MPHX/bt_api.php` 对应方法；tdesign `FtpView.vue` 移除回收站入口与弹窗（classic `ftp.php` 同步注释停用）。删除文件仍正常进入节点回收站，恢复改由站点管理员在宝塔面板操作
- **文件管理新增拖拽上传**：文件拖入列表区即按队列串行上传到当前目录（复用 `ChunkUploader` 分片/断点续传），顶部常驻进度条（序号/文件/速度/剩余/取消）；同名文件逐个询问覆盖（先删后传）/跳过/改名，服务端检测到已有内容时一律中止不续写，防止污染；暂不支持拖入文件夹
- **文件管理页改版**：路径导航从工具栏拆为独立路径栏（面包屑独占一行，新增「返回上级」按钮，路径过长横向细滚动条可滚）；工具栏按用途分区（新建/上传靠左、批量操作靠右）并常驻拖拽上传提示；行操作列由 9 个平铺按钮收纳为「编辑/下载/更多/删除」，重命名/复制/剪切/解压/导入 SQL/预览/权限并入「更多」下拉，列宽 300→150；表格去斑马纹与全边框，改行 hover 浅蓝底，文件夹链接由绿色改品牌蓝

**问题修复**

- **主机到期不再阻断登录与管理端**：到期/封禁/面板关闭提示原本会杀死一切携带 `user_token` cookie 的请求——登录页打不开、登录与注销接口被拦（换任何账号都登不上）、同浏览器下的管理员登录与后台请求也被一并挡住。现限制为只阻断用户面板页面本身，登录页/验证码/登录与注销请求/管理端全部放行；面板页的到期提示行为不变（保留 cookie，续费后刷新恢复）。顺带加固：账号被删除后不再产生 PHP 警告污染响应
- **TDesign 主题五处功能缺陷**：邮箱绑定弹窗仅在站长开启「主机邮箱绑定」要求时强制弹出且不可关，否则可正常取消；Gzip 改开关式界面，可正常关闭并回显当前状态（级别/长度/类型可配）；SQL 备份不可用——补齐后端必需参数（备份传数据库 id、下载传 filename 走签名链接、恢复传 user+filename）；SQL 权限配置不可用——补传 `dataAccess` 并回显当前访问权限；API 密钥保存后切页返回不再回显旧值。另：后端 `json_exit` 失败时 `success` 仍为 true，相关操作按返回消息关键字二次判定真实结果
- **全新安装失败修复**：主页迁移移除 `MN_config.home_*` 列时种子数据 VALUES 元组残留 39 值对 28 列，安装报 "Column count doesn't match value count at row 1"，已修正种子行

**界面优化**

- 管理端表单页统一排版：设置类页面改 `td-form-page` 居中窄列，添加类页面宽列加 `td-form-grid` 响应式栅格，前端模板页一并纳入，清理四处视图内重复的 scoped 栅格定义
- 用户端站点设置页套用同一居中列约定：11 个子设置的页头下拉换成横向导航条并带各子项说明，去掉与页头重复的卡片头

## V1.87

**安全加固**

- **监控/定时入口鉴权升级**：`jk.php` / `jk_monitor.php` / `docker_cron.php` 新增 `?t=<unix>&sign=<hash_hmac('sha256','<脚本名>|<t>',API密钥)>` 鉴权方式（±300 秒有效，`mnbt_cron_auth_check()`）；旧 `?my=` 查询串**过渡期仍可用**但记录弃用日志（密钥不再建议出现在访问日志/代理中）；后台「教程与监控」两套主题的命令展示同步更新为 HMAC 签名命令
- **管理员密码 bcrypt 化（惰性迁移）**：`MN_config.pwd` 支持 bcrypt 哈希存储（`mnbt_admin_password_verify/hash`），首次用旧明文密码登录成功即自动升级并写审计日志；后台改密、安装向导均直接落哈希，不再存明文（用户侧 `MN_zj.pass` 为 FTP 密码、须明文展示，不参与）。**升级后管理员需重新登录一次**
- **安装器加固**：`config.php` / `MPHX/SQ.php` 写入改为 `var_export`，杜绝 POST 原文拼接进 PHP 源码的代码注入窗口；删除安装 API 内 335 行永远不会执行的旧版向导 HTML（`$do` 分支），未知 action 返回 JSON
- **pay.php 补 CSRF 校验**：全站唯一未过 CSRF 的 POST 端点收口（classic 部署表单经注入脚本自动携带 token，正常流程无感）
- **BT cookie jar 迁出 web 根**：`api/cookie/` → `runtime/bt_cookie/`（`mnbt_bt_cookie_file()`，含旧文件一次性迁移），新增 `runtime/.htaccess` 与 `api/cookie/.htaccess` 拒绝 Web 访问（nginx 用户请在站点配置加 `location ~* ^/(runtime|api/cookie)/ { deny all; }`）；更新器保留清单同步
- **360safe WAF 修正**：API/定时/安装入口按请求路径豁免正则拦截（自带鉴权且 POST 体含建站/SQL 内容属正常业务，原 `webscan_white` 白名单因要求查询串为空而基本失效）；`webscan_slog` 从空实现改为真实落盘 `runtime/logs/waf.log`
- jk.php 全部 `$_GET['gn']` 读取补兜底，消除 PHP8 未定义索引告警
- **后台更新进度 UI 对齐（TDesign）**：UpdateView 移植 classic 的异步更新体验——`gn=update` 异步启动（`async=1`）+ 1.5 秒轮询 `gn=upprogress` 渲染步骤条/进度条/已用时间，HTTP 中断不再当作终态，5 分钟进度卡死与 30 分钟总等待兜底判定；设置页新增「下载策略」（镜像优先/GitHub 优先/仅镜像/仅直连）并修正过时的下载通道文案；修正 `upprogress` 端点覆盖 `ok` 终态字段导致更新失败被误报为成功的问题

## V1.86

**文件管理功能重构：彻底移除 AMFTP，全面对齐宝塔官方 API**

- **移除 AMFTP**：整个 `user/amftp/` 子系统（97 个文件）删除。AMFTP 基于裸 FTP 协议、凭据经隐藏表单明文提交、独立会话鉴权，绕过 MNBT 的配额与审计体系，且其 `Amysql/Config.php` 本就缺失。文件管理统一由内置的宝塔 API 方案承担；`MN_config.hxw` 引擎开关不再读写（后台设置项同步移除，数据库字段保留但弃用）
- **后端重写 `user/api/file.php`**：动作名与参数对齐宝塔官方文件接口（docs.bt.cn/api/files），21 个动作：`file_list / file_read / file_save / file_create / file_delete / file_delete_batch / file_rename / file_copy / file_compress / file_unzip / file_size / file_upload_prepare / file_upload / file_download / file_access / file_access_set / recycle_list / recycle_restore / recycle_delete / recycle_clear / recycle_switch`
- **路径安全加固（重要）**：旧版仅校验"路径以 / 开头"，`/..` 即可穿越站点根目录，读写、外链下载节点任意文件；新版统一走 `mnbt_file_norm_path()` 规范化（拒绝 `..`、反斜杠、空字节、控制字符），并把节点返回的 `PATH` 与请求路径强一致校验，异常时强制回落站点根目录
- **文件下载入口合并**：`user/wjxz.php` 删除，下载并入 `gn=file_download`（外链创建、密码外链重开、流量计入 `MN_zj.llmax` 的逻辑保持不变，并补齐同样的穿越校验）
- **解压增强**：`ftpjy`（site.php）并入 `file_unzip`，压缩类型按扩展名自动识别 zip / tar.gz / rar（旧版硬编码 zip），解压结果如实回报；`bt_api::GetLogsjywj()` 增加可选 `$type` 参数（向后兼容）
- **新增回收站管理**：回收站列表 / 恢复 / 彻底删除 / 清空 / 开关，封装 `recycle_list / recycle_restore / recycle_clear / recycle_switch / recycle_delete`（`MPHX/bt_api.php`），参数与官方文档一致
- **新增文件权限查看/修改**：`GetFileAccess` / `SetFileAccess` 封装与界面入口
- **前端重写**：`templates/default/user/ftp.php` 全部重写（TDesign 主题经 iframe 复用该页自动生效）——目录面包屑导航、分页/排序表格、同名文件覆盖确认、上传进度条与取消、在线编辑器（主题记忆）、回收站弹窗、权限弹窗，输出统一走 HTML 转义（修复旧版文件名注入 XSS）；旧版 `imsetes/js/upload.js` 替换为 `imsetes/js/mnbt-uploader.js`（自适应分片 1MB→8MB、断点续传、速度/剩余时间回调）
- **其它修复**：旧版复制/压缩等操作 `exit()` 后才写日志的顺序错误；`dirfiles()` 补齐缺失索引兜底，消除 PHP8 未定义索引告警；后台「控制面板设置」保存脚本 `xtset.js` 不再读取已删除的 FTP 面板下拉框

**主题体系重构：tdesign 升格默认主题，SPA 改为 vue3-sfc-loader 免构建加载**

- **default 主题更名 `classic` 并冻结**：不再维护、仅随包保留供回退；tdesign（Vue 3 + TDesign）自本版本起为系统默认主题，缺页兜底常量 `MNBT_THEME_DEFAULT` 指向 tdesign；`templates/active_*_theme` 加入更新保留清单（`MPHX/github_updater.php`），管理员手动选择的主题跨更新保留
- **移除 Vite/npm 构建链**：`vite.*.config.js` / `package.json` / 各端 HTML 模板 / `dist` 全部删除，SPA 由 [vue3-sfc-loader](https://github.com/FranckFreiburger/vue3-sfc-loader) 0.9.5 在浏览器内编译 `spa/src/` 源码直出——修改 `.vue` 保存刷新即生效，部署不再需要 Node
- **vendor UMD 入库 `imsetes/vendor/`**：vue 3.5 / vue-router 4.6 / tdesign 1.20 / axios / echarts 6.1（全量版）+ loader 本体，版本记录见 `VERSIONS.txt`；`templates/tdesign/assets/td-boot.js` 为五端共用引导（moduleCache 桥接 / `@` 别名解析 / 无扩展名探测 / 图片资产 URL 化）
- **文件管理器 SPA 化**：`FtpView.vue` 从 iframe 嵌入改为原生 TDesign 实现（目录面包屑、服务端分页排序、分片上传+断点续传+取消、同名冲突处理、在线编辑器 CodeMirror 动态加载、回收站、权限、图片预览、SQL 导入），对接 V1.86 的 `file_*` API；新增 `user/api/ftp.js`、`user/utils/uploader.js`、`user/utils/codemirror.js`
- **缺口补齐**：tdesign 新增 `user/head.php` / `admin/head.php` 插件页壳（Bootstrap/lyear，与 classic 同构）及配套 CSS 资产；新增 `user/ftp.php` 包装视图；滑块验证码图集迁至 `tdesign/docker/assets/`；`_router.php` MIME 表补 vue/svg/webp/json/woff2；孤儿控制器 `admin/bt_php.php` 删除（底层函数与 AJAX 动作保留）
- **SCSS 预编译**：`theme.scss` / `home.scss` 编译为 CSS 提交，SCSS 源移除（loader 不支持浏览器端 sass）；echarts 按需引入改为 UMD 全局桥接
- 主题元信息 `theme.json` 升至 0.4.0；文档同步（tdesign.md / tdesign-php.md / engine / guide / home / views / directory）

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
