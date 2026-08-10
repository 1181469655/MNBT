---
title: 常见问题
description: 安装、对接、使用过程中的常见问题解答
---

# 常见问题

## Q: 安装时提示数据库连接失败？

检查 `config.php` 中的数据库主机、端口、用户名、密码、数据库名是否正确，确认 MySQL 服务已启动。

## Q: 宝塔面板连接失败？

1. 确认宝塔面板 API 接口已开启（面板设置 → API 接口）
2. 确认 API 密钥已正确填写
3. 检查面板地址协议（HTTP/HTTPS）和端口号是否正确
4. 如使用 HTTPS，确认证书有效或关闭 SSL 验证

## Q: 用户无法登录控制面板？

1. 确认主机已开通且状态正常
2. 检查 `MN_config` 表中 `kzmbqk` 字段是否为 `true`（控制面板总开关）
3. 检查主机到期时间是否已过期

## Q: 文件管理上传失败？

1. 检查 PHP `upload_max_filesize` 和 `post_max_size` 配置
2. 检查主机空间配额是否已满
3. 检查网站目录写入权限

## Q: 监控任务如何生效？

1. 每个用户最多 5 个监控任务
2. URL 监控默认间隔 60 秒，资源监控固定 180 秒
3. 需在宝塔计划任务中配置每分钟访问 `jk_monitor.php?my=API密钥`
4. 通知日志会自动记录到期提醒（7/3/1/0 天）和流量超额（>=80%）

## Q: PHP 8.x 兼容吗？

已全面兼容 PHP 7.4 ~ 8.4。修复内容包括：`each()` 替换为 `foreach`、`count($string)` 替换为 `strlen`、`get_magic_quotes_gpc()` 写死返回 `false`、`var` 改为 `public`、移除 PHP 4 构造器、`strftime` 替换、`json_decode(null)` 保护等。

## Q: 如何升级到新版本？

1. 备份数据库和代码
2. 上传新版本代码覆盖
3. 运行 `install/install.sql` 中的增量 SQL（如有）
4. 清空 `runtime/` 目录下的缓存
