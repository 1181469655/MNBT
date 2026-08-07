# 梦奈宝塔Docker对接插件 — 魔方财务 server module

将梦奈宝塔Docker容器作为标准服务器产品接入魔方财务（idcsmart）。

## 前置条件

- **魔方财务**：已安装并正常运行
- **MNBT V1.83+**：Docker 模块已完成 M1 API 扩展（`api/docker.php` 支持 `gn=kt/zt/jc/tj/xf/bg/czmm/ztcx/sy/start/stop/restart`）
- MNBT 后台已配置 Docker 节点（`MN_docker_node`）、套餐（`MN_docker_plan`）

## 安装

1. 将 `mf_modules/servers/mnbtdocker/` 整个目录复制到魔方财务的 `/modules/servers/mnbtdocker/`
2. 清魔方缓存（后台清或删除 `runtime/cache/`）
3. 后台「产品 → 服务器」→ 模块下拉应出现 **梦奈宝塔Docker对接插件**

## 配置

### 方式一：服务器接口设置（推荐，简洁）

后台「产品 → 服务器」添加服务器：

| 字段 | 填写 |
|------|------|
| 服务器 IP/域名 | MNBT 站点域名 |
| 端口 | MNBT 站点端口（80/443） |
| 用户名 | 节点编号（`MN_docker_node.id`） |
| 密码 | 调用密钥 md5（`md5(ktmy.qmk)`） |
| Access Hash | 系统 API 密钥（`$conf['api']`） |
| SSL | 按站点是否 HTTPS 勾选 |

产品关联模块后只需填写可选配置（如 `plan_id`），其余从服务器字段自动解析。

### 方式二：模块配置选项（精确控制）

产品配置选项中填写：

| key | 说明 |
|-----|------|
| `api_url` | `https://mnbt.example.com/api/docker.php` |
| `api_key` | 系统 API 密钥 |
| `node_id` | 节点编号 |
| `call_key` | `md5(节点ktmy . 节点qmk)` |
| `plan_id` | 默认套餐 ID（可选） |
| `console_url` | `https://mnbt.example.com/docker/login.php` |

## 产品关联

1. 后台「产品 → 添加产品」→ 类型：**服务器产品** → 模块：**梦奈宝塔Docker对接插件**
2. 可配置选项添加「套餐 ID」字段，映射 key 为 `plan_id`（可选，用于升降级）
3. 上架后即可前台购买

## 模块方法说明

| 魔方操作 | 触发的 MNBT API | 说明 |
|----------|-----------------|------|
| 开通 | `gn=kt` | 仅开通账号，不创建容器 |
| 暂停 | `gn=zt` | 停容器 + qk=paused |
| 恢复 | `gn=jc` | qk=active |
| 删除 | `gn=tj` | 删容器 + 删用户行（立即） |
| 续费 | `gn=xf` | 更新到期时间 |
| 升降级 | `gn=bg` | 更新 plan_id |
| 改密 | `gn=czmm` | 重置密码 |
| 开机/关机/重启 | `gn=start/stop/restart` | 容器启停 |
| 状态 | `gn=ztcx` | 查询容器状态 |
| 同步 | `gn=ztcx` | 同上，同步到魔方 |

## 前台功能

- **容器控制台** 选项卡：显示容器状态 + 「前往控制台」按钮
- **打开容器控制台** 按钮：跳转 梦奈宝塔Docker对接插件 登录页

## 注意事项

- **单容器模型**：每个账号仅一个容器；开通后用户需在 MNBT 控制台的应用商店自行创建容器
- **双登录体系**：魔方登录态 ≠ 梦奈宝塔Docker对接插件_token，用户需用 Docker 账号二次登录控制台
- **P1 自动登录**：下期通过 `gn=dl` 一次性票据实现免登录跳转
