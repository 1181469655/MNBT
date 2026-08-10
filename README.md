# 梦奈宝塔主机系统 (MNBT) V1.83

基于宝塔面板 API 的虚拟主机分销管理系统，支持多节点宝塔面板统一管理、用户自主开通主机、一键部署网站程序、在线文件管理、Gzip/缓存配置、URL/资源监控告警、违禁词扫描、**可切换前端主题**、**PHP 业务插件**等功能。

![PHP](https://img.shields.io/badge/PHP-7.4%20~%208.4-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.6%2B-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-Commercial-blue)
![Version](https://img.shields.io/badge/version-1.83-green)

---

## 核心特性

- ✅ **多节点管理**：同时管理多台宝塔面板（Linux/Windows）
- ✅ **一键部署**：可视化配置部署流程，10 种自动化操作，支持模板导入导出
- ✅ **监控告警**：URL 监控（状态码/内容匹配）+ 资源监控（空间/流量）+ 到期提醒 + 邮件通知
- ✅ **SSL 证书**：Let's Encrypt 一键申请/续签
- ✅ **PHP 8.x 全面兼容 + SQL 参数化查询**：已修复全部废弃语法，彻底消除 SQL 注入风险
- ✅ **完善的操作日志**：所有关键操作可追溯
- ✅ **可切换前端主题**：用户端 / 管理端独立皮肤，缺页回退 default
- ✅ **PHP 业务插件**：`app_plugins/` 目录插件，钩子 / AJAX / 菜单 / 配置（与宝塔节点 Python 插件分离）

---

## 环境要求

| 依赖 | 版本要求 |
|------|---------|
| PHP | **7.4 ~ 8.4**（已全面兼容 PHP 8.x） |
| MySQL | 5.6+ |
| Web 服务器 | Nginx / Apache |
| 宝塔面板 | Linux 版 / Windows 版 |
| PHP 扩展 | curl、mysqli、json、mbstring |

---

## 快速开始

```bash
# 1. 上传代码并解压至网站目录，设置网站运行目录为 /

# 2. 浏览器打开 http://your-domain/install 运行安装向导
#    （环境检测 → 数据库配置 → 站点与管理员 → 初始化数据库）

# 3. 登录管理后台 http://your-domain/admin（安装向导中设置的管理员账号）

# 4. 后台 → 宝塔管理 → 添加宝塔（填写面板地址、端口、API 密钥）
#    宝塔面板需开启 API 接口：面板设置 → API 接口 → 开启

# 5. 配置默认建站目录与监控计划任务（每分钟访问 /jk_monitor.php?my=API密钥）
```

详细安装步骤见 **完整文档** 中的「使用指南 → 安装部署」。

---

## 完整文档见 docs/

| 文档 | 说明 |
|------|------|
| [使用指南](docs/guide/intro.md) | 项目简介、功能概览、安装部署、目录结构、数据库、宝塔对接、Docker、监控、常见问题、安全说明 |
| [API 参考](docs/api/overview.md) | 后台/用户/外部对接/插件对接接口文档 |
| [主题开发](docs/development/theme/index.md) | 前端主题系统与主题开发手册 |
| [插件开发](docs/development/plugin/index.md) | PHP 业务插件系统与插件开发手册 |
| [集成对接](docs/integration/idcsmart-hosting.md) | IDC 系统集成对接 |
| [更新日志](docs/guide/changelog.md) | 版本更新记录 |

---

## 许可证

本项目采用**宽松许可证**，版权归 [梦奈云](https://github.com/1181469655/MNBT) 所有。

✅ **允许**：商业用途、二次开发、分发传播。

⚠️ **要求**：保留原作者版权声明；修改后的文件需注明修改内容。

---

## 联系方式

- **官方 QQ 群**：994752422
- **技术支持**：1181469655@qq.com
- **商务合作**：1181469655@qq.com
- **问题反馈**：[GitHub Issues](https://github.com/1181469655/MNBT/issues)

---

<div align="center">

**MNBT** © 2022-2026 梦奈云 版权所有

Made with ❤️ by [梦奈云](https://github.com/1181469655/MNBT)

</div>
