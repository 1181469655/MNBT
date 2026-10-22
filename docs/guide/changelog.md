---
title: 更新日志
description: MNBT 版本更新记录（V1.89 ~ V1.60）
---

# 更新日志

## V1.89

**安全加固：库内密码与密钥全面密文化**

- **密文存储落地**：新增 `MPHX/secret.php` 统一加解密助手（AES-256-GCM，密文形如 `enc1:` + base64(nonce+tag+密文)），加密密钥 `$mnbt_secret_key`（64 位 hex）存 `config.php`——该文件本就 gitignored 且在在线更新保留清单内不会被覆盖；新装在写数据库配置时一并生成，老站首次需要加密时自动补写（要求 `config.php` 可写，不可写时明确报错提示手工添加）。纳入加密的字段：`MN_zj.pass`（同时是面板登录密码与 FTP 密码，必须可逆）、`MN_zj.sqlpass`、`MN_bt` 与 `MN_docker_node` 的 `btmy/ktmy/qmk`、`MN_config.mailpassword`、DNS 服务商 `plg_dns_provider.api_secret`、资源池 `MN_plugin_respool.password`。这条防线挡的是拖库、备份外泄、SQL 注入一次性带走全部凭据；密钥与数据库同机，整机被控不在防线内
- **读取全部走解密，与明文双向兼容**：`bt_api` / `bt_docker` / `bt_proxy` 三个类的构造器统一解密，一次覆盖 30 余处节点密钥调用点；其余读取点逐个改造——用户登录校验、会话派生、一键登录、phpMyAdmin 免登表单、一键部署配置模板、数据库连通测试、后台主机/宝塔/Docker 节点列表与编辑回填、外部 API 与 Docker API 的 `md5(ktmy.qmk)` 鉴权、邮箱授权码（在 `common.php` 读配置时统一还原，三处发信入口自动生效）。**没有 `enc1:` 前缀的值原样透传，所以"先部署代码、后迁移数据"是安全的**，明文密文混合期功能不受影响；已是密文的值再写回也幂等，不会被二次加密。聚名网域名插件（自有 authcode 加密）与移动桥接（只存令牌哈希）不属本次范围
- **管理员密码收口 bcrypt**：`MN_config.pwd` 的明文残留统一改为 bcrypt 哈希（V1.87 引入的登录时惰性升级继续保留）。realname 插件「查看身份证号」的二次密码校验此前直接比对明文，密码转哈希后会永久验证失败，已改走 `mnbt_admin_password_verify()`
- **新增一次性迁移脚本 `admin/migrate_secrets.php`**：客户主机普遍没有 PHP CLI，脚本走浏览器执行——管理员登录后台后访问 `/admin/migrate_secrets.php`。它先按 information_schema 把相关 varchar 列加宽到 500（密文比明文长），再逐表加密存量明文、把管理员密码转哈希，最后打印每张表的处理条数；全程幂等可重复执行，全部成功后自动删除自身，有失败项则保留文件、修好后刷新重跑。判表存在性不走 `SHOW TABLES LIKE ?`——该写法在部分 MySQL/MariaDB 的预处理协议下不被支持，`mysqli_prepare` 失败会被误判成"表不存在"而空跑
- **升级须知（发给客户）**：① 部署新代码后**先跑一次迁移脚本再对外开通主机**——`MN_zj.pass` 建表是 `varchar(50)`，未加宽前写入密文会被 MySQL 静默截断成不可解的坏值（本项目连接层 `sql_mode=''`，不报错只截断）；② 脚本会把管理员密码转哈希，跑完所有管理员需重新登录，用户侧登录态与续费流程不受影响；③ `config.php` 需可写；④ 后台列表与用户面板的 FTP/数据库信息、phpMyAdmin 免登等处仍会显示明文，那是解密后给管理员/租户本人用的，属功能本身

**安全加固：PHP 运行环境基线与近半年 PHP 核心漏洞（php2shell）**

- **背景**：PHP 官方于 2026-10-21 紧急发布修复 php2shell——CVE-2026-109618 / CVE-2026-109619（CVSS v4.0 9.2，构造 multipart POST 即可未授权远程执行代码），仅修在 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12；7.4 / 8.0 / 8.1 已停止上游维护，官方永远不会出补丁。本面板是按分销形式给一批客户发 PHP 运行时的，所以这不只是自身的事：默认配置下 `session.upload_progress.enabled=1` 会随池参数带到每一个租户站
- **安装强制门槛**：`install/install.api.php` 新增 `mnbt_php_security_gate()`，按 `PHP_MAJOR_VERSION.PHP_MINOR_VERSION` 查「分支 → 最低已修补版本」表，在 `system`（环境检测）、`database_info_wire`、`install` 三处拦截，版本不合格直接拒绝安装并给出可执行的说明。**已装站点不受影响**（存在 `install/install.lock` 时不再执行环境检测），`repair` / `check_upgrade` 不设门槛，避免把存量站卡在维护流程之外。`composer.json` 的 `"php": ">=7.4"` 保持不动——那是源码移植性声明，改成 `>=8.2` 会是假话（`MPHX/common.php`、`MPHX/function.php` 仍有 `PHP_VERSION_ID >= 70300` 的旧分支），运行环境要求由安装门槛与文档表达
- **缓解措施（不是根治，升级到上表版本才是）**：`MPHX/common.php` 在 `session_start()` 前 `@ini_set('session.upload_progress.enabled', '0')` 兜底面板自身（不经入口脚本的请求仍以 `php.ini` 为准）；`bash.conf.php` 的 `$fpm_conf` 池模板追加 `php_admin_value[session.upload_progress.enabled] = 0`（用 `php_admin_value` 是为了让租户无法通过 `.user.ini` 或 `ini_set` 重新打开）与 `pm.max_requests = 500`（限制残留请求状态跨请求累积）。⚠️ **如实说明：该模板当前没有下发通道**——`bash.conf.php` 全仓库仅被 `MPHX/bt_api.php` 的 `GetAllFile()` 引用且只用其中的 `$shell_get_file`，`$fpm_conf` / `$nginx_conf` / `$shell_phpset_bash` 均无消费方，文末注释提到的「后台 → 宝塔列表 → 修复配置」按钮已随 V1.87 移除 classic 后台一起消失，租户池实际是节点在 `AddSite` / `SetPHPVersion` 时按默认参数生成的。因此这条改动目前只是模板备好，要让缓解真正落到站点，须在每个节点手工给池文件追加该指令并逐版本 reload php-fpm，命令与验证方法见新增的 `docs/guide/security.md`；补一个真下发通道得走节点 shell 执行通道，等于新增一条命令执行面，本次没有做
- **出站 TLS 从「默认不校验」改为「默认强校验」**：原本有 5 处硬关掉证书校验——`bt_api`（宝塔节点 API，携带含节点密钥的 cookie jar 且跟随重定向）、`bt_docker`、`bt_proxy`、`curl_get`、支付宝官方插件；安全审计又查出 3 处：`mf_modules/servers/mnbthost`、`mnbtdocker`（后者会把 `mn_key` / `mn_keye` 发出去）与 `app_plugins/zjmfmanager_reserve/lib/CubeFinanceClient.php`。现统一走新增的 `MPHX/function.php` `mnbt_curl_ssl_verify()`，`CURLOPT_SSL_VERIFYHOST` 传 2（只有值为 2 时 libcurl 才校验主机名/SAN）；开启重定向的调用点补 `CURLOPT_PROTOCOLS` / `CURLOPT_REDIR_PROTOCOLS` 限定只跳 http/https，防止 302 把带凭据的请求拐到 `file://`、`gopher://`；`send_post()`（HTTP 流包装器默认跟随 20 跳）改为不跟随。唯一退路是面板级选项 `panel/ssl_insecure`（存既有 `MN_plugin_option` 键值表，无新增表，暂无后台开关需执行 SQL 设置）；支付宝网关不受该开关影响，校验硬写在 `curl_setopt_array` 之后，调用方也关不掉。**破坏性提示**：宝塔节点默认是自签证书，升级后节点对接会直接报证书错误，正解是给 `php.ini` 配 `curl.cainfo` 指向 CA bundle，而不是打开 `ssl_insecure`
- **同窗口其余 PHP 公告的影响面判定**：CVE-2026-91769 / CVE-2026-91767（OpenSSL 对端名称校验，SAN 不匹配后回退 CN、通配符名堆溢出）与 CVE-2026-91766（重定向跨源携带凭证）都落在上一节那条出站链路上；CVE-2026-6735（FPM status 页 XSS）不涉及，`$fpm_conf` 未设 `pm.status_path`；CVE-2026-6103（phar TAR 条目注入）不涉及，仓库只用 `ZipArchive`，无 `Phar` / `PharData` 调用；CVE-2026-17543 / CVE-2025-14179 属 pgsql 扩展，本产品不使用；CVE-2026-14355 是 `openssl_encrypt` 的 AES-WRAP-PAD 填充，本面板加密链路用 `aes-256-gcm` / `aes-256-cbc` / `aes-128-cbc`，未涉及。以上随升级到基线版本一并收口
- **顺带修掉的代码缺陷**：`admin/api/cx.php` 的 `cxtj` / `cxxgjl` 两个分支都把 POST 的**键名**直接当文件名拼路径（写盘以及先 `@unlink` 再写），现按仓库既有写法加 `^[a-zA-Z0-9_\-]+\z` 白名单拒绝非法名（用 `\z` 而非 `$`，避免结尾换行绕过）；`cxfiledru` 的 `ZipArchive::extractTo` 改为逐条校验条目名，拒绝绝对路径、盘符与 `..` 路径段，解压后再用 `realpath` 前缀复核实际落点（zip-slip）；删除 `bt_api::GetFileCentent()`——它把参数直接拼进 `cat` 后交给节点 shell 执行，全仓库零调用方
- **文档**：新增 `docs/guide/security.md`（PHP 运行环境基线表、无法升级时的缓解与节点侧命令、同批次 CVE 影响面、出站 TLS 与退路开关、客户/运维升级动作清单）；README 与 `docs/index.md` 徽章、`docs/guide/install.md` / `faq.md` / `directory.md` 的「PHP 7.4 ~ 8.4」口径同步改为运行环境基线，`faq.md` 新增「服务器暂时不能升级 PHP 怎么办」；`docs/guide/intro.md` 的「一键切换 PHP 版本（5.2 ~ 8.1）」澄清为**租户站点**选择器，并注明 8.1 及以下无法修复 php2shell
- **未验证与待办**：开发环境无 PHP CLI，本次改动未跑过 `php -l`，部署前请对改动的 `.php` 文件做语法检查并在测试节点点一次「点我检测」确认出站链路；前端仍捆绑 jQuery 3.4.1（< 3.5.0 存在 XSS，官方已在 3.5.0 修复）、Bootstrap 4.4.1、Vue 2.6.10（已 EOL），随主题一起分发给客户，尚未处理

**问题修复**

- **流量统计页所有卡片与图表显示 0**：统计插件返回的是 `{status, data:{…}}` 信封，而 SPA 的 `parseResult` 会把整个信封当数据载荷交给前端，前端按扁平字段取值全部落空；同时前端请求的区间名 `7days/30days` 与 `mnbt_connector` 侧 `_date_range` 认识的 `7d/30d` 不一致，区间永远命中空数据。现按信封解包（并识别 `status:false` 弹出插件真实错误），下拉选项与请求词汇一并对齐
- **用户端 dashboard 流量显示"几万G"**：`MN_zj.llmax.dq` 存的是**字节**、`max` 才是 GB（与 `jk.php` 的统计口径一致），此前把字节数直接标注为 G，几百 GB 的用量就显示成几万 G，超量判定同步失真。用户端 dashboard 与管理端主机列表均改为按字节还原单位

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
- **API 设置页找回「对外 API 协议模式」**：V1.85 该开关渲染在 classic 主题的设置页上，V1.87 删除 classic 时未移植到 tdesign，导致后台没有入口、只剩后端接口。现补进 tdesign「API 设置」页的下拉框，并随「保存修改」一并提交（原独立端点 `egn=setapicompat` 已并入 `setapi`；请求未带 `api_compat` 时不改该列，避免旧页面把兼容模式误切回严格模式）
- **控制面板设置页补回 Logo 上传**：同属 V1.87 删 classic 时漏移植的功能，`setkzmb` 后端一直支持 `loa/lob/loc` 三个文件字段，但 tdesign 页面只剩一段"请用 default 主题上传"的失效指引。现补上三张 Logo（登录页/侧栏/浏览器图标）的选择与预览，并加服务端校验：只收真实 PNG/JPG、单张 ≤2MB（`mnbt_panel_logo_max_mb()`），三张全部校验通过才落盘，避免半更新；`name` 为空直接报错（上传体积超过 PHP `post_max_size` 时 `$_POST` 会整体为空，原逻辑会把面板名洗成空串）。注意这三张图供用户端面板与官网首页读取，管理端品牌图另有 `imsetes/admin_logo/`，不受此处影响
- **「添加程序」源码包上传从未生效**：`postGn()` 把传入的 `FormData` 当普通对象做 `Object.keys()` 遍历，字段与文件全部丢失，后端 `cxtj` 收不到文件按「禁止留空」拒绝；该响应是 `{"code":100,"msg":…}` 且不带 `success`，被前端当成纯数据载荷放行，于是页面提示"添加成功"但列表里的程序没有源码包。`postGn()` 现识别 `FormData` 并按 multipart 原样发送，`addProgram()` 去掉自己重复拼 `gn` 的写法

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
