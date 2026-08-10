---
title: 核心工具函数
description: MPHX/function.php 与 MPHX/Response.php 的响应函数、业务函数速查
---

# 核心工具函数

## 6.1 响应函数

> 全部定义在 `MPHX/function.php` 和 `MPHX/Response.php`

| 函数 | 用途 |
|------|------|
| `json_exit($code, $extra=[])` | 输出 JSON 并退出 |
| `json_exit_success($msg, $extra=[])` | 输出成功 JSON 并退出（自动 `qk=1`） |
| `json_exit_error($msg, $extra=[])` | 输出失败 JSON 并退出（自动 `qk=4`） |
| `json_echo($code, $extra=[])` | 输出 JSON 不退出 |
| `json_return($code, $extra=[])` | 返回 JSON 字符串 |
| `mnbt_json_encode($code, $extra=[], $success=null)` | 构造统一 JSON 字符串 |
| `Response::build($code, $msg, $data, $redirect, $success)` | 构造响应数组 |
| `Response::exit_json(...)` | 输出 JSON 并退出 |
| `Response::exit_success($msg, $data, $redirect)` | 输出成功 JSON 并退出 |
| `Response::exit_error($msg, $data, $redirect)` | 输出失败 JSON 并退出 |

## 6.2 业务函数

| 函数 | 用途 |
|------|------|
| `logjl($czuser, $lx, $lr, $qk, $DB)` | 写操作日志到 `MN_log` |
| `mnbt_log($user, $type, $content, $status, $db)` | `logjl` 别名 |
| `send_post($url, $post_data)` | HTTP POST（`file_get_contents` + stream context） |
| `curl_get($url)` | HTTP GET（cURL，10s 超时） |
| `authcode($string, $operation, $key, $expiry)` | 双向加解密（RC4 风格） |
| `daddslashes($string, $force, $strip)` | 递归 addslashes |
| `showmsg($content, $type, $back)` | 显示 HTML 提示页 |
| `sysmsg($msg, $die)` | 显示系统错误页 |
| `deldir($dir)` | 递归删除目录 |
| `zipfile($path, $zipth, $paths, $filetext)` | 递归压缩目录 |

---

**相关文档：**

- [API 通用约定](./overview.md)
- [数据库表速查](./database.md)
