---
title: MNBT Docker 集成 — 测试文档
description: MNBT V1.83 Docker 集成（M1-M4）的完整手动测试流程：部署初始化、后台测试、API 测试、控制台测试、软删测试、安全隔离与 FAQ
---

# MNBT Docker 集成 — 测试文档

> 本机无 PHP/MySQL 环境，以下为在你服务器上的完整手动测试流程。
> 适用版本：MNBT V1.83 Docker 集成（M1-M4 全量交付）

---

## 0. 交付物清单

| 文件 | 类型 | 说明 |
|------|------|------|
| `MPHX/bt_docker.php` | 新增 | 宝塔 Docker API 封装类 |
| `MPHX/docker.member.php` | 新增 | Docker 独立认证 |
| `MPHX/theme.php` | 修改 | 新增 `docker` scope |
| `install/install.sql` | 修改 | 追加 3 张 Docker 表 |
| `update/update_v183_docker.sql` | 新增 | 升级 SQL |
| `templates/active_docker_theme` | 新增 | 主题标记（内容 `default`） |
| `docker/head.php` `ajax.php` `login.php` `index.php` `console.php` `image.php` `volume.php` `compose.php` `appstore.php` | 新增 | Docker 控制台控制器 |
| `templates/default/docker/head.php` `foot.php` `login.php` `console.php` `image.php` `volume.php` `compose.php` `appstore.php` `assets/docker.css` | 新增 | Docker 控制台视图 |
| `admin/docker.php` | 新增 | 后台管理控制器 |
| `admin/api/docker.php` | 新增 | 后台 AJAX 模块 |
| `admin/ajax.php` | 修改 | 注册 docker 模块 |
| `templates/default/admin/index.php` | 修改 | 侧栏新增 Docker 管理（含「添加 Docker 节点」入口） |
| `templates/default/admin/docker.php` | 新增 | 后台管理视图 |
| `templates/default/admin/add.php` | 修改 | 新增 `dknode` 分支：独立「添加 Docker 节点」表单页 |
| `api/docker.php` | 新增 | 对外开通 API |
| `docker_cron.php` | 新增 | 到期软删定时任务 |
| `README.md` `API.md` | 修改 | 文档 |

---

## 1. 环境前置条件

1. **PHP 7.4 ~ 8.4**（需 `curl`、`pdo_mysql`、`openssl` 扩展）
2. **MySQL 5.7+**
3. **宝塔面板节点**（用于 Docker）：
   - 已开启 API 接口，记录 `btip`、`btdk`、`btmy`（接口密钥）、`ktmy`（调用密钥）、`qmk`（二级验证）
   - **已安装宝塔 Docker 管理器插件**（应用商店搜索 "Docker管理器" 安装）
   - 节点信息在 Docker 模块**独立管理**（`MN_docker_node` 表），**不依赖** MNBT 的「宝塔列表」（`MN_bt`）
4. MNBT 系统已正常安装并可登录后台

---

## 2. 部署与初始化

### 2.1 上传代码

将本次新增/修改的文件按原目录结构覆盖到服务器 MNBT 根目录。

### 2.2 执行数据库升级

**已有 MNBT 系统（升级）**：在数据库管理（phpMyAdmin / 宝塔数据库）中导入：

```
update/update_v183_docker.sql
```

确认成功创建 4 张表：`MN_docker_node`、`MN_docker_user`、`MN_docker_plan`、`MN_docker_order`。

**全新安装**：直接运行 `install/install.sql`（已内置 4 表）。

### 2.3 目录权限

确保以下目录可写：
- `api/cookie/`（curl cookie jar，已存在则跳过）
- `templates/`（主题标记文件写入）

### 2.4 PHP 语法检查（可选但建议）

在服务器 SSH 执行：

```bash
cd /www/wwwroot/你的MNBT目录
php -l MPHX/bt_docker.php
php -l MPHX/docker.member.php
php -l MPHX/theme.php
php -l docker/head.php
php -l docker/ajax.php
php -l docker/console.php
php -l docker/appstore.php
php -l admin/docker.php
php -l admin/api/docker.php
php -l api/docker.php
php -l docker_cron.php
```

全部应输出 `No syntax errors detected`。如有报错，把完整报错反馈给我。

---

## 3. 后台管理测试

登录 MNBT 后台，左侧导航应出现 **「Docker 管理」** 菜单（含 4 个子项：节点管理、添加 Docker 节点、Docker 用户、套餐管理）。

> 整页刷新后台（F5）才能加载新菜单项。

### 3.1 节点管理（独立添加页面）

Docker 节点**独立于** MNBT 的「宝塔列表」（`MN_bt` 表），存于 `MN_docker_node` 表，因此**不依赖**「添加宝塔」页面的 `ssbt` 选项。

**添加节点（独立页面）：**

1. 进入 **Docker 管理 → 添加 Docker 节点**（或在「节点管理」页点「添加节点」按钮）
2. 填写表单：
   - 节点名称 *：`北京节点A`（仅显示用）
   - 宝塔面板地址 *：`1.2.3.4`（IP 或域名）
   - 宝塔端口：`8888`
   - 安全访问 (HTTPS)：宝塔开启面板 SSL 时打开
   - 宝塔接口密钥 *：宝塔面板 → 设置 → API 接口 → 接口密钥
   - 调用密钥：外部 API 鉴权用（留空则不校验）
   - 二级验证密钥：与调用密钥组合 md5 校验
   - 节点开关：启用
3. 点「确认添加」→ 提示「添加成功」→ 自动跳转回「节点管理」页

**预期**：列表出现该节点，操作日志在「系统管理 → 操作日志」有「Docker节点 → 添加节点 北京节点A」记录。

**节点管理页其他操作：**

1. **编辑**：点列表「编辑」→ 弹窗修改名称/密钥/启停 → 保存生效（弹窗仅用于编辑，添加走独立页面）
2. **删除**：点「删除」→ 节点下有用户时拒绝，无用户时成功
3. **节点容器查询**：选择节点 → 点「查询」→ 顶部显示 Docker 安装状态，下方表格显示该节点所有容器

**若容器查询失败**，检查：
- `MN_docker_node` 记录的 `btip/btdk/btmy/ptl` 是否正确
- 节点宝塔是否已安装 Docker 管理器插件
- 节点 API 是否放行了 MNBT 服务器 IP

### 3.2 套餐管理

1. 进入 **Docker 管理 → 套餐管理**
2. 点击「添加套餐」：名称 `测试套餐A`，CPU `1`，内存 `512`，价格 `10`，上架
3. 确认列表出现该套餐
4. 编辑：把内存改为 `1024`，保存，确认更新
5. 删除：确认可删除（无用户关联时）

**预期**：增删改查正常，操作日志在「系统管理 → 操作日志」有记录。

### 3.3 Docker 用户管理

1. 进入 **Docker 管理 → Docker 用户**
2. 点击「添加用户」：
   - 账号 `dtest01`（≥4位）
   - 密码 `dtest123`（≥6位）
   - 邮箱留空
   - 节点：选择 3.1 中添加的 Docker 节点（下拉来自 `MN_docker_node`）
   - 套餐：选「测试套餐A」
   - 到期：`0000-00-00`（永久）
3. 确认列表出现该用户，状态「正常」，容器状态「未创建」
4. 测试「改密」「暂停/恢复」「编辑」「删除」

**预期**：用户 CRUD 正常，密码以 bcrypt 存储（数据库 `password_hash` 字段以 `$2y$` 开头）。

---

## 4. 对外开通 API 测试

> 用 curl 或 Postman 测试 `api/docker.php`

替换变量：
- `YOUR_DOMAIN` = MNBT 域名
- `YOUR_API_KEY` = MNBT 后台「系统设置 → API」的密钥（`$conf['api']`）
- `NODE_BH` = Docker 节点编号（`MN_docker_node.id`，在「Docker 管理 → 节点管理」列表的 ID 列查看）
- `MD5_KEY` = `md5(节点ktmy . 节点qmk)`，可在 PHP 中执行 `echo md5($ktmy.$qmk);` 获得（`ktmy`/`qmk` 取自该节点的 `MN_docker_node` 记录）

### 4.1 连接验证（gn=cfif）

```bash
curl -X POST "http://YOUR_DOMAIN/api/docker.php?gn=cfif" \
  -d "mn_bh=NODE_BH&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=test"
```

**预期**：`{"success":true,"code":200,"msg":"连接验证成功！"}`

如返回密钥错误，核对 `mn_key`（系统 API 密钥）与 `mn_keye`（节点调用密钥 md5）。

### 4.2 开通账户（gn=kt）

```bash
curl -X POST "http://YOUR_DOMAIN/api/docker.php?gn=kt" \
  -d "mn_bh=NODE_BH&mn_key=YOUR_API_KEY&mn_keye=MD5_KEY&mn_vs=15&username=apiuser01&password=apipass123&dqtime=2026-12-31&plan_id=1"
```

**预期**：`{"success":true,"code":200,"msg":"Docker 账户开通成功！""}`
后台「Docker 用户」列表应出现 `apiuser01`。

### 4.3 异常用例

- 账号重复 → 返回「该 Docker 账号已存在」
- 账号 <4 位 → 返回「账号不少于4位」
- 密钥错误 → 返回「系统 API 密钥不匹配」
- 节点关闭 → 返回「该 Docker 节点不存在或已被关闭」

---

## 5. Docker 控制台测试

访问 `http://YOUR_DOMAIN/docker/login.php`

### 5.1 登录

1. 用 3.2 创建的 `dtest01` / `dtest123` 登录
2. 错误密码 → 提示「账号或密码错误」
3. 正确密码 → 跳转 `console.php`

**预期**：登录成功，侧栏显示用户名/节点/套餐，cookie 名为 `docker_token`（浏览器 F12 → Application → Cookies 查看）。

### 5.2 我的容器（空状态）

1. 首次进入「我的容器」 → 显示空状态「您还没有容器」+ 「前往应用商店」按钮

### 5.3 应用商店创建容器（核心流程）

1. 进入「应用商店」 → 等待应用列表加载（首次 get_apps 可能较慢，超时 90s）
2. 搜索框输入应用名过滤
3. 点击某应用「安装」→ 弹窗显示版本选择 + CPU/内存 + 应用专属参数
4. CPU/内存默认值不超过套餐上限；手动输入超过上限会被后端强制截断
5. 点击「确认安装」→ Toast「创建请求已提交」→ 自动跳转 `console.php`

**预期**：
- `console.php` 显示「容器创建中」+ 安装日志轮询（每 8 秒刷新）
- 数据库 `MN_docker_user` 的 `service_name`=`mnbt_dtest01`，`container_status`=`creating`，`container_spec` 有 JSON
- 1-5 分钟后容器变为 `running`，显示容器详情（名称/镜像/端口/配额）

**若应用列表加载失败**：
- 确认节点已安装宝塔 Docker 管理器
- 节点首次需初始化应用商店（宝塔面板 → Docker → 应用商店 → 初始化）
- 查看 `MN_docker_user` 对应节点能否调用 `get_apps`

### 5.4 容器启停

1. 容器 running 时点「停止」→ 状态变 stopped
2. stopped 时点「启动」→ 状态变 running
3. 点「重启」→ 状态刷新

**预期**：操作成功 Toast，1.5 秒后列表刷新状态。

### 5.5 单容器隔离验证

1. 用 `dtest01` 登录，已有容器
2. 再次进入应用商店 → 顶部黄色提示「您已创建容器，单容器模型下无法再次创建」
3. 点击任意应用「安装」→ Toast「您已创建容器，无法再次创建」

### 5.6 其他页面

- 「本地镜像」→ 表格显示节点镜像
- 「存储卷」→ 表格显示存储卷
- 「Compose」→ 模板表 + 项目表

### 5.7 登出

点侧栏「退出登录」→ 跳转登录页，`docker_token` 被清除。

---

## 6. 到期软删定时任务测试

### 6.1 配置计划任务

宝塔面板 → 计划任务 → 添加：
- 类型：访问 URL
- 周期：每 30 分钟
- URL：`http://YOUR_DOMAIN/docker_cron.php?my=YOUR_API_KEY`

### 6.2 手动触发测试

```bash
curl "http://YOUR_DOMAIN/docker_cron.php?my=YOUR_API_KEY"
```

**预期**：输出 `docker_cron done @ 2026-xx-xx ...`，下方列出被处理的用户（无则空）。

### 6.3 到期流程验证（造数据）

1. 在后台编辑某测试用户，到期时间设为昨天（如 `2026-08-03`）
2. 手动执行 cron → 该用户 `qk` 变 `expired`，`expired_at`=`2026-08-03`
3. 把 `expired_at` 改为 8 天前（如 `2026-07-26`）模拟满 7 天
4. 再执行 cron → 节点容器被删除，`qk` 变 `pruned`，`prune_due`=今天，`container_id` 清空
5. 把 `prune_due` 改为 8 天前
6. 再执行 cron → 用户行被物理删除

**预期**：三阶段流转正确，每步操作日志有记录。

### 6.4 密钥错误

```bash
curl "http://YOUR_DOMAIN/docker_cron.php?my=wrong"
```
**预期**：输出 `密钥错误`。

---

## 7. 安全与隔离检查

| 检查项 | 方法 | 预期 |
|--------|------|------|
| cookie 隔离 | docker 登录后访问 `user/` 页面 | 不互踢（独立 token） |
| 改密失效 | 后台改某用户密码后，该用户旧 `docker_token` | 自动失效，需重新登录 |
| CSRF | 篡改 ajax 请求去掉 `_csrf` | 返回 CSRF 失败 |
| 越权容器 | 用户 A 登录，构造请求传用户 B 的 service_name | 后端按 `me.service_name` 过滤，看不到 B 的容器 |
| 到期登录 | `qk=expired` 的用户尝试登录 | 提示「已到期」拒绝 |
| 暂停登录 | `qk=paused` 的用户尝试登录 | 提示「已被暂停」拒绝 |

---

## 8. 已知假设与需确认项

以下因无法在本机验证宝塔实际接口，**请在你的宝塔环境确认**，如有出入反馈给我调整：

1. **容器删除接口**：`bt_docker::container_del` 调用 `GET /btdocker/container/del`。若你的宝塔版本该接口路径不同（可能是 `remove`/`delete`/`kill`），cron 删除容器会失败（不影响其他流程）。**确认方法**：宝塔面板 → Docker → 容器列表，F12 抓包看删除请求的实际路径，反馈给我。
2. **get_apps 响应结构**：前端按 `data.data` 或 `data` 数组解析，应用字段按 `appname/apptitle/apptype/appversion/env/field` 取值。若实际字段名不同，应用商店卡片/安装表单会显示异常。**确认方法**：在「节点容器」页或抓包看 `get_apps` 返回的 JSON 结构。
3. **create_app 异步判定**：后端按 `status==true` 或 `code==0 && status==true` 判定成功。若宝塔返回结构不同，可能误判失败。**确认方法**：抓包看 `create_app` 成功响应。
4. **容器列表字段**：前端兼容 `name/Names`、`image/Image`、`status/State`、`ports/Ports`、`time/Created`、`id/Id` 两种命名，实际取其一即可。
5. **应用专属参数透传**：`app_create` 会把 `env`/`field` 中除通用参数外的 POST 字段透传给宝塔。若某应用必填字段名与通用参数冲突，可能需在后端 `$builtins` 白名单补充。

---

## 9. 常见问题

**Q: 应用商店一直转圈加载失败？**
A: 节点宝塔未安装 Docker 管理器，或未初始化应用商店。去宝塔面板 → Docker → 应用商店点一次初始化。

**Q: 创建容器后状态一直「创建中」不变 running？**
A: 查数据库 `MN_docker_user.container_status`。若容器实际已运行但状态没同步，检查 `my_container` 接口返回的 `container` 是否为 null（可能是 `service_name` 与宝塔实际容器名不匹配）。宝塔 create_app 的容器名通常是 `<service_name>_<数字>`，前端用 `service_name` 前缀匹配可能漏匹配 —— 如有此情况反馈，我调整为前缀匹配。

**Q: 后台侧栏没有 Docker 管理？**
A: 整页刷新后台（F5），multitabs 需刷新才加载新菜单。确认 `templates/default/admin/index.php` 已更新。

**Q: docker_cron 提示密钥错误？**
A: `my` 参数必须等于后台「系统设置 → API」的密钥，与 `jk_monitor.php` 用的是同一个。

---

## 10. 测试结果反馈

测试时如遇问题，请提供：
1. 对应接口的**完整响应 JSON**（浏览器 F12 → Network → Response）
2. `MN_docker_user` 表该用户行的截图/数据
3. 宝塔面板 Docker 容器列表截图（对照容器名是否匹配）
4. PHP 报错（若有，开 `display_errors` 或看 `runtime/logs/`）

我会据此定位并修正。

---

> 魔方财务联调测试见 [Docker × 魔方财务对接 PRD](./docker-idcsmart.md) 及 [Docker 魔方财务 API](../api/docker-mofang.md)。
