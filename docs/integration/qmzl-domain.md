---
title: 域名注册 API 对接
description: 第三方域名注册商 API 对接文档，含域名查询、价格、信息模板、购物车与下单完整流程（基于 idcsmart 域名模块）
---

# 域名注册 API 对接

## 基本信息

| 项目 | 说明 |
|------|------|
| **Base URL** | `{host}/console/v1` |
| **认证方式** | `Authorization: Bearer {token}` |
| **Content-Type** | `application/json`（文件上传用 `multipart/form-data`） |
| **响应格式** | `{ status: 200, msg: "...", data: {...} }` |

---

## 1. 域名配置

### 获取域名配置

`GET /idcsmart_domain/config`

返回系统配置：域名注册/信息服务协议链接、可搜索后缀列表、默认搜索后缀。

---

## 2. 域名查询

### 查询域名可注册状态

`GET /idcsmart_domain/check_domain`

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `domain` | string | 是 | 域名前缀（不含后缀） |
| `suffix` | string | 是 | 后缀（含点号，如 `.com`） |
| `host_id` | int | 是 | 固定 `0` |

响应 `data[]` 每项：`name`（完整域名）、`avail`（1=可注册, 0=已注册）。

---

## 3. 域名价格

### 获取域名注册价格

`GET /idcsmart_domain/get_price`

| 参数 | 说明 |
|------|------|
| `name` | 完整域名 |
| `host_id` | 固定 `0` |

响应：按年限的 `buyyear` / `buyprice` 数组。

---

## 4. 信息模板

模板状态：`0` 未认证、`1` 已认证、`2` 审核中、`3` 认证失败、`4` 异常。

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/idcsmart_domain/info_template` | 模板列表 |
| GET | `/idcsmart_domain/info_template/{id}` | 模板详情 |
| POST | `/idcsmart_domain/info_template` | 创建模板（multipart/form-data） |
| PUT | `/idcsmart_domain/info_template/{id}` | 更新模板 |
| DELETE | `/idcsmart_domain/info_template/{id}` | 删除模板 |
| POST | `/idcsmart_domain/info_template/{id}/certifications` | 提交实名认证 |

证件类型：`SFZ`（身份证）、`HZ`（护照）、`YYZZ`（营业执照）、`ORG`（组织机构代码证）、`TYDM`（统一社会信用代码证）等。

---

## 5. 购物车与下单

### 加入购物车

`POST /cart`

```json
{
  "product_id": 12,
  "config_options": { "domain": "mydomain.com", "year": 3 },
  "qty": 1,
  "customfield": { "is_domain": 1 }
}
```

### 结算创建订单

`POST /cart/settle`

```json
{
  "positions": [0],
  "customfield": {
    "auto_renew": 1,
    "lock_status": 1,
    "c_sysid": "1",
    "host_id": 0
  }
}
```

响应 `{ "status": 200, "data": { "order_id": "..." } }`。

下单成功后跳转支付：`/console/payment?orderId={id}&amount={金额}&gateway={网关}`

---

## 6. 已购域名列表

`GET /host?page={n}&limit={n}`

status 枚举：`Active`（正常）、`Suspended`（已暂停）、`Pending`（处理中）、`Expired`（已过期）、`Deleted`（已删除）。

---

## 7. 辅助接口

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/product/group/first` | 一级产品分组 |
| GET | `/product/group/second?id={id}` | 二级产品分组 |
| GET | `/product?keywords=域名` | 搜索域名产品 |
| GET | `/gateway` | 支付网关列表 |

---

## 完整注册流程

```
1. GET  /idcsmart_domain/config              → 获取配置
2. GET  /idcsmart_domain/check_domain        → 查询域名
3. GET  /idcsmart_domain/get_price           → 获取价格
4. GET  /idcsmart_domain/info_template       → 获取模板列表
5. POST /cart                                → 加入购物车
6. POST /cart/settle                         → 结算创建订单
7. 跳转 /console/payment                     → 支付页面
```

---

## 错误处理

统一格式：`{ "status": 400, "msg": "错误描述" }`

> 完整原始文档见 `app_plugins/qmzl_domain/域名注册API对接文档.md`
