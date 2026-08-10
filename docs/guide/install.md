---
title: 安装部署
description: 环境要求、安装部署步骤与快速开始
---

# 安装部署

## 环境要求

| 依赖 | 版本要求 |
|------|---------|
| PHP | **7.4 ~ 8.4**（已全面兼容 PHP 8.x） |
| MySQL | 5.6+ |
| Web 服务器 | Nginx / Apache |
| 宝塔面板 | Linux 版 / Windows 版 |
| PHP 扩展 | curl、mysqli、json、mbstring |
| Composer | 可选（仅用于 PHPMailer 安装） |

> 已修复 `each()`、`count($string)`、`get_magic_quotes_gpc()` 等 PHP 8 废弃语法；所有 SQL 已迁移至参数化查询。

## 安装部署

### 1. 准备环境

确保服务器已安装 PHP 7.4+ + MySQL，并已安装宝塔面板。

### 2. 部署代码

```bash
# 将安装包上传至网站目录并解压
# 设置网站运行目录为 /
# 配置伪静态规则（如使用 ThinkPHP 风格 URL）
```

### 3. 运行安装向导

访问 `http://你的域名/install`，按向导提示完成：

1. 欢迎 / 许可协议
2. 环境检测（PHP 版本 >= 7.4.0、curl 等）
3. 数据库配置（填写 MySQL 连接信息）
4. **站点与管理员**（控制面板名称、站长 QQ、公告、管理员账号密码）
5. 初始化数据库（自动导入表结构，或重装模式）
6. 完成安装（完成页会显示你设置的管理员账号）

### 4. 登录管理

- 管理后台：`http://你的域名/admin`
- 账号/密码：安装向导中设置的管理员信息（不再固定 `admin/123456`）
- ⚠️ **请妥善保存密码；安装后建议删除 `install` 目录**

### 5. 对接宝塔面板

1. 登录管理后台 → 宝塔管理 → 添加宝塔
2. 填写宝塔面板地址、端口、API 密钥
3. 宝塔面板中需开启 API 接口：面板设置 → API 接口 → 开启
4. 设置默认建站目录（Linux：`/www/wwwroot`，Windows：`D:/wwwroot`）

### 6. 配置计划任务（监控系统）

在宝塔面板 → 计划任务中添加：

| 任务类型 | 执行周期 | 执行命令 |
|---------|---------|---------|
| 访问 URL | 每分钟 | `http://你的域名/jk_monitor.php?my=API密钥` |

API 密钥可在管理后台 → 系统设置 → API 接口中查看。

### 7. 安装节点插件（可选）

如需使用分布式节点管理 + 违禁词扫描功能：

```bash
# 1. 在管理后台 → 节点管理 → 添加节点
# 2. 将 plugins/mnbt_connector/ 部署到宝塔服务器
# 3. 复制生成的 config.json 配置
# 4. 重启插件服务
systemctl restart mnbt-connector.service
```

## 快速开始

最简部署流程：

```bash
# 1. 上传代码
cd /www/wwwroot/your-domain
# 解压安装包

# 2. 设置权限
chmod -R 755 .
chown -R www:www .

# 3. 访问安装向导
# 浏览器打开 http://your-domain/install

# 4. 登录后台，修改默认密码
# 浏览器打开 http://your-domain/admin

# 5. 添加第一台宝塔服务器
# 后台 → 宝塔管理 → 添加宝塔

# 6. 配置默认建站目录和监控任务
# 后台 → 系统设置 → 基础设置
# 宝塔面板 → 计划任务 → 添加监控任务
```
