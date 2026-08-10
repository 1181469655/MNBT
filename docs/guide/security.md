---
title: 安全说明
description: 敏感文件清单、推荐 .gitignore 与部署安全建议
---

# 安全说明

⚠️ **本系统仅限内部部署，请勿将以下文件/目录上传至公开仓库**：

- `config.php` - 包含数据库账号密码
- `install/install.lock` - 安装锁定文件
- `runtime/logs/*.log` - 错误日志，可能包含敏感信息
- `plugins/wwwlogs/*.log` - 访问日志
- `.env` - 环境变量（如使用）

## 推荐 .gitignore

```gitignore
# 安装锁定
install/install.lock

# 配置文件
config.php
.env

# 日志文件
*.log
runtime/logs/
plugins/wwwlogs/
runtime/cache/
runtime/temp/

# 临时文件
.DS_Store
Thumbs.db
.idea/
.vscode/
*.swp
*.swo

# 敏感信息
*.sql.bak
backup/
```

## 部署安全建议

1. ✅ 使用安装时设置的强密码，并定期更换管理员密码
2. ✅ 修改 API 密钥（系统设置 → API 接口）
3. ✅ 启用 HTTPS（SSL 证书配置）
4. ✅ 修改宝塔面板默认端口
5. ✅ 限制管理后台 IP 访问（Nginx/Apache 配置）
6. ✅ 定期更新 PHP 版本
7. ✅ 关闭 PHP 错误显示（`display_errors = Off`）
8. ✅ 定期备份数据库
9. ✅ 监控异常登录和操作日志
