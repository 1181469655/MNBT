---
title: 与 PHP 的对接
description: tdesign 主题与 PHP 的对接:双端入口映射、__TD_BOOT__ 启动数据、AJAX gn 列表与插件菜单
---

# 与 PHP 的对接(tdesign)

本文是 [TDesign 双端主题](./tdesign.md) 的对接细节:管理端 / 用户端入口映射、`window.__TD_BOOT__` 启动数据、AJAX 调用与插件菜单渲染。

## 管理端入口映射

| 访问 | SPA 路由 |
|------|----------|
| `/admin/login.php` | `#/login` |
| `/admin/index.php` `/admin/sy.php` | `#/dashboard` |
| `/admin/set.php?gn=wz` | `#/settings/website` |
| `/admin/set.php?gn=gl` | `#/settings/admin` |
| `/admin/set.php?gn=api` | `#/settings/api` |
| `/admin/set.php?gn=mail` | `#/settings/mail` |
| `/admin/set.php?gn=kzmb` | `#/settings/panel` |
| `/admin/set.php?gn=jk` | `#/settings/monitor` |
| `/admin/set.php?gn=theme` | `#/settings/theme` |
| `/admin/set.php?gn=yzf` | `#/pay` |
| `/admin/list.php?gn=zj` | `#/host` |
| `/admin/list.php?gn=bt` | `#/baota` |
| `/admin/list.php?gn=dd` | `#/order` |
| `/admin/list.php?gn=cx` | `#/program` |
| `/admin/list.php?gn=log` | `#/log` |
| `/admin/add.php?gn=zj` | `#/host/add` |
| `/admin/add.php?gn=bt` | `#/baota/add` |
| `/admin/add.php?gn=cx` | `#/program/add` |
| `/admin/add.php?gn=dr` | `#/program/import` |
| `/admin/node.php` | `#/node` |
| `/admin/node.php?tab=scan` | `#/node/scan` |
| `/admin/plugin_manage.php` | `#/plugin` |
| `/admin/pay_settings.php` | `#/pay` |
| `/admin/tutorial.php` | `#/tutorial` |
| `/admin/update.php` | `#/update` |

## 用户端入口映射

| 访问 | SPA 路由 |
|------|----------|
| `/user/login.php` | `#/login` |
| `/user/index.php` `/user/sy.php` | `#/dashboard` |
| `/user/set.php?gn=php` | `#/settings/php` |
| `/user/set.php?gn=pass` | `#/settings/pass` |
| `/user/set.php?gn=mrwd` | `#/settings/default-doc` |
| `/user/set.php?gn=yxml` | `#/settings/run-dir` |
| `/user/set.php?gn=wjt` | `#/settings/rewrite` |
| `/user/set.php?gn=ssl` | `#/settings/ssl` |
| `/user/set.php?gn=fdl` | `#/settings/hotlink` |
| `/user/set.php?gn=gzip` | `#/settings/gzip` |
| `/user/set.php?gn=cache` | `#/settings/cache` |
| `/user/set.php?gn=xgpass` | `#/settings/password` |
| `/user/set.php?gn=mysqlcz` | `#/settings/sql-auth` |
| `/user/site_stats.php` | `#/stats` |
| `/user/sqlgl.php` | `#/sql-backup` |
| `/user/monitor.php` | `#/monitor` |
| `/user/monitor_log.php` | `#/monitor/log` |
| `/user/notice.php` | `#/notice` |
| `/user/webgl.php` | `#/deploy` |
| `/user/ftp.php` | `#/ftp`(iframe 嵌入默认主题) |
| `/user/plugin.php?p=xxx&page=yyy` | `#/plugin?p=xxx&page=yyy`(iframe 在 layout 内加载) |

## 启动数据 `window.__TD_BOOT__`

由 `_spa_boot.php` 注入,字段如下:

```js
{
  siteName, footer, user, adminUser, loggedIn, needCaptcha,
  ajaxBase: './ajax.php', codeUrl: './code.php',
  logo, logoHead, logoIndex, auther,
  theme: 'tdesign', version: '0.2.0',
  entry, hash,                  // 当前 SPA 入口与目标 hash
  pluginMenuHtml,               // 主题渲染器输出的插件菜单 HTML
  conf,                         // 站点配置(全部 $conf)
  yhc,                          // 用户端主机信息(仅 user scope)
  serverHost, serverProto,
  themeList, curUserTheme, curAdminTheme,
  paymentPlugins, enabledPayments, pluginSettingsTabs
}
```

视图可在 `include '_spa_boot.php'` 前设置 `$td_inject` 数组,把页面级数据合并进 boot。

## AJAX

仍请求 **`./ajax.php`**,`gn` 与官方一致。

**管理端 gn 示例**:

| 模块 | gn 示例 |
|------|---------|
| 登录 / 退出 | `login` |
| 仪表盘 | `sxsyxx`(系统信息)、`gglist`(公告)、`check_update`(检查更新) |
| 设置 | `phpxg`(网站)、`glxg`(管理)、`apixg`(API)、`mailxg`(邮箱)、`kzmbxg`(面板)、`jkxg`(监控)、`theme`(主题切换) |
| 主机 | `listzj` / `addzj` / `editzj` / `delzj` / `delzjs` |
| 宝塔 | `listbt` / `addbt` / `editbt` / `delbt` / `checkbt` / `listnodephp` / `autodetectphp` |
| 节点 | `listnode` / `addnode` / `delnode` / `nodestatus` / `nodeconfig` / `nodeping` / `forbiddenscan` |
| 程序 / 订单 / 日志 | `listcx` / `addcx` / `editcx` / `delcx` / `listdd` / `deldelete` / `listlog` / `dellog` / `clearlog` |
| 插件 | `listzj_admin`(插件列表)、`plugin_install` / `plugin_enable` / `plugin_uninstall` |
| 支付 | `listpayments` / `savepaymentmethods` |
| 修复 / 更新 | `repair` / `systemupdate` |

**用户端 gn 示例**:

| 模块 | gn 示例 |
|------|---------|
| 登录 / 退出 | `login` |
| 首页 | `indexconf`(站点配置+流量+空间)、`refresh_space`(刷新空间) |
| 站点设置 | `set_init`(PHP列表)、`phpxg`(切PHP)、`hqjt`(伪静态)、`setwjt`、`getssl` / `setssl` / `clossl`、`fdl`、`gzip`、`cache`、`mrwd`、`yxml` |
| 密码 / SQL | `xgpass`(改密码)、`mysqlcz`(SQL权限) |
| 文件管理 | `ftp` 系列(由默认主题 ftp.php 处理) |
| SQL 备份 | `database` 系列 |
| 监控 | `monitor` 系列 |
| 站点统计 | `site_stats`(act=overview/uri_rank/ip_rank/errors/trend/spider/client/method/recent) |
| 邮箱绑定 | `mailbd` |
| 一键部署 | `webgl` |

响应解析由 `shared/api/http.js` 的 `parseResult` 统一处理,兼容以下格式:

- `{qk:1|4, code, msg}` —— panel 风格
- `{success, code, msg}` —— `json_exit` 风格
- `{code: 'xxx成功'}` —— 旧接口
- `{total, rows}` / `{ip, dk}` —— 纯数据载荷
- 纯文本(包含「失败/错误」判否,其余判成功)

错误时自动 `console.warn` / `console.error` 输出详情,方便调试。

## 插件菜单

`theme.php` 通过 `mnbt_register_theme_menu_renderer` 注册双端渲染器,  
将引擎传入的插件菜单树转为侧栏 HTML(`td-side-submenu` / `td-side-leaf`),  
由 `_spa_boot.php` 注入到 `window.__TD_BOOT__.pluginMenuHtml`。

**独立分组**:插件菜单在侧栏中作为独立的"扩展与工具"分组显示,与业务菜单视觉分离。

**SPA 路由跳转**:插件叶子菜单项带 `data-td-route="/plugin?p=xxx&page=yyy"` 属性,  
点击时由 SPA 拦截,通过 `router.push` 在 layout 内通过 iframe 加载插件页面,不再新窗口打开。
