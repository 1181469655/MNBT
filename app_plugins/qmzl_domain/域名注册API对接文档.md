# 域名注册 API 对接文档

## 基本信息

| 项目 | 说明 |
|------|------|
| **Base URL** | `{host}/console/v1` |
| **认证方式** | `Authorization: Bearer {token}`（token 存储于 localStorage `auth_token`） |
| **Content-Type** | `application/json`（文件上传除外，使用 `multipart/form-data`） |
| **响应格式** | `{ status: 200, msg: "...", data: {...} }` |
| **成功判断** | `status === 200` |

---

## 1. 域名配置

### 1.1 获取域名配置

获取系统配置：域名注册协议链接、域名信息服务协议链接、可搜索的后缀列表、默认搜索后缀。

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/idcsmart_domain/config` |

**响应示例：**

```json
{
  "status": 200,
  "msg": "success",
  "data": {
    "domain_register_agreement_url": "https://xxx.com/agreement/register",
    "domain_information_service_agreement_url": "https://xxx.com/agreement/service",
    "specify_search_domain": [".com", ".cn", ".net", ".com.cn", ".xyz", ".top"],
    "default_search_domain": ".com"
  }
}
```

---

## 2. 域名后缀

### 2.1 获取域名后缀列表

获取系统支持的所有 TLD 后缀列表（兜底数据，config 接口已包含时可不调用）。

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/idcsmart_domain/domain_suffix` |

**响应示例：**

```json
{
  "status": 200,
  "data": [
    { "suffix": ".com" },
    { "suffix": ".cn" },
    { "suffix": ".net" }
  ]
}
```

---

## 3. 域名查询

### 3.1 查询域名可注册状态

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/idcsmart_domain/check_domain` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `domain` | string | 是 | 域名前缀，不含后缀。如 `mydomain` |
| `suffix` | string | 是 | 后缀，含点号。如 `.com` |
| `host_id` | int | 是 | 固定传 `0` |

**调用示例：**

```
GET /console/v1/idcsmart_domain/check_domain?domain=mydomain&suffix=.com&host_id=0
```

**响应示例：**

```json
{
  "status": 200,
  "data": [
    {
      "name": "mydomain.com",
      "avail": 1
    },
    {
      "name": "mydomain.net",
      "avail": 0
    },
    {
      "name": "mydomain.cn",
      "avail": 1
    }
  ]
}
```

**字段说明：**

| 字段 | 类型 | 说明 |
|------|------|------|
| `name` | string | 完整域名 |
| `avail` | int | `1` = 可注册，`0` = 已注册 |

---

## 4. 域名价格

### 4.1 获取域名注册价格

查询指定域名的注册价格（按年限）。

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/idcsmart_domain/get_price` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `name` | string | 是 | 完整域名，如 `mydomain.com` |
| `host_id` | int | 是 | 固定传 `0` |

**调用示例：**

```
GET /console/v1/idcsmart_domain/get_price?name=mydomain.com&host_id=0
```

**响应示例：**

```json
{
  "status": 200,
  "data": [
    { "buyyear": 1, "buyprice": "55.00" },
    { "buyyear": 3, "buyprice": "150.00" },
    { "buyyear": 5, "buyprice": "230.00" },
    { "buyyear": 10, "buyprice": "420.00" }
  ]
}
```

**字段说明：**

| 字段 | 类型 | 说明 |
|------|------|------|
| `buyyear` | int | 购买年限 |
| `buyprice` | string | 该年限总价（元）。前端计算年均价：`buyprice / buyyear` |

---

## 5. 信息模板

信息模板用于域名注册时提交所有者实名信息。模板状态说明：

| status | 含义 |
|--------|------|
| `0` | 未认证 |
| `1` | 已认证 |
| `2` | 审核中 |
| `3` | 认证失败 |
| `4` | 异常 |

### 5.1 获取模板列表

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/idcsmart_domain/info_template` |

**响应示例：**

```json
{
  "status": 200,
  "data": {
    "list": [
      {
        "id": 1,
        "type": "personal",
        "status": 1,
        "zh_owner": "张三",
        "en_owner": "ZHANG SAN",
        "email": "zhangsan@example.com",
        "phone": "13800138000",
        "zh_address": "北京市朝阳区xxx路xxx号",
        "en_address": "XXX ROAD, CHAOYANG, BEIJING, CHINA",
        "postal_code": "100000",
        "country": "CN",
        "idtype": "SFZ",
        "idnum": "110101199001011234"
      }
    ]
  }
}
```

### 5.2 获取模板详情

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/idcsmart_domain/info_template/{id}` |

**响应示例：**

```json
{
  "status": 200,
  "data": {
    "info_template": {
      "id": 1,
      "type": "personal",
      "status": 1,
      "zh_owner": "张三",
      "zh_all_name": "张三",
      "zh_last_name": "张",
      "zh_first_name": "三",
      "en_owner": "ZHANG SAN",
      "en_all_name": "ZHANG SAN",
      "en_last_name": "ZHANG",
      "en_first_name": "SAN",
      "email": "zhangsan@example.com",
      "phone": "13800138000",
      "zh_address": "北京市朝阳区xxx路xxx号",
      "en_address": "XXX ROAD, CHAOYANG, BEIJING, CHINA",
      "postal_code": "100000",
      "country": "CN",
      "idtype": "SFZ",
      "idnum": "110101199001011234"
    }
  }
}
```

### 5.3 创建模板

| 项目 | 说明 |
|------|------|
| **方法** | `POST` |
| **路径** | `/idcsmart_domain/info_template` |
| **Content-Type** | `multipart/form-data`（**不设** `Content-Type`，由浏览器自动生成） |

**请求参数（FormData）：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `type` | string | 是 | `personal`（个人）或 `enterprise`（企业） |
| `zh_owner` | string | 是 | 域名所有者（中文），个人填姓名，企业填公司名 |
| `zh_all_name` | string | 是 | 联系人全名（中文） |
| `zh_last_name` | string | 否 | 联系人姓（中文） |
| `zh_first_name` | string | 否 | 联系人名（中文） |
| `en_owner` | string | 是 | 域名所有者（英文/拼音大写） |
| `en_all_name` | string | 否 | 联系人全名（英文） |
| `en_last_name` | string | 是 | 联系人姓氏（英文/拼音大写） |
| `en_first_name` | string | 是 | 联系人名字（英文/拼音大写） |
| `email` | string | 是 | 电子邮箱 |
| `phone` | string | 是 | 手机号码 |
| `zh_address` | string | 是 | 中文地址 |
| `en_address` | string | 是 | 英文地址（拼音大写） |
| `postal_code` | string | 否 | 邮政编码 |
| `country` | string | 否 | 国家代码，默认 `CN` |
| `idtype` | string | 是 | 证件类型（见下表） |
| `idnum` | string | 是 | 证件号码 |
| `img_front` | File | 是 | 证件正面照片 |
| `img_back` | File | 否 | 证件反面照片 |

**证件类型（idtype）枚举：**

| 值 | 适用类型 | 说明 |
|------|----------|------|
| `SFZ` | personal | 身份证 |
| `HZ` | personal | 护照 |
| `GAJMTX` | personal | 港澳通行证 |
| `TWJMTX` | personal | 台湾通行证 |
| `YYZZ` | enterprise | 营业执照 |
| `ORG` | enterprise | 组织机构代码证 |
| `TYDM` | enterprise | 统一社会信用代码证 |

### 5.4 更新模板

| 项目 | 说明 |
|------|------|
| **方法** | `PUT` |
| **路径** | `/idcsmart_domain/info_template/{id}` |
| **Content-Type** | `multipart/form-data` |

参数同 5.3 创建模板。

### 5.5 删除模板

| 项目 | 说明 |
|------|------|
| **方法** | `DELETE` |
| **路径** | `/idcsmart_domain/info_template/{id}` |

### 5.6 提交实名认证

| 项目 | 说明 |
|------|------|
| **方法** | `POST` |
| **路径** | `/idcsmart_domain/info_template/{id}/certifications` |
| **Content-Type** | `multipart/form-data` |

**请求参数（FormData）：**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `img_front` | File | 是 | 证件正面照片 |
| `img_back` | File | 否 | 证件反面照片 |

---

## 6. 购物车与下单

### 6.1 加入购物车

| 项目 | 说明 |
|------|------|
| **方法** | `POST` |
| **路径** | `/cart` |
| **Content-Type** | `application/json` |

**请求体：**

```json
{
  "product_id": 12,
  "config_options": {
    "domain": "mydomain.com",
    "year": 3
  },
  "qty": 1,
  "customfield": {
    "is_domain": 1
  }
}
```

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `product_id` | int | 是 | 域名产品 ID |
| `config_options.domain` | string | 是 | 完整域名 |
| `config_options.year` | int | 是 | 购买年限 |
| `qty` | int | 是 | 数量，固定 `1` |
| `customfield.is_domain` | int | 是 | 标记为域名订单，固定 `1` |

### 6.2 结算创建订单

| 项目 | 说明 |
|------|------|
| **方法** | `POST` |
| **路径** | `/cart/settle` |
| **Content-Type** | `application/json` |

**请求体：**

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

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `positions` | int[] | 是 | 购物车位置索引，固定 `[0]` |
| `customfield.auto_renew` | int | 是 | 自动续费：`1` 开 / `0` 关 |
| `customfield.lock_status` | int | 是 | 转移锁：`1` 开 / `0` 关 |
| `customfield.c_sysid` | string | 是 | 信息模板 ID |
| `customfield.host_id` | int | 是 | 固定 `0` |

**响应示例：**

```json
{
  "status": 200,
  "data": {
    "order_id": 202401010001
  }
}
```

下单成功后，跳转到支付页面：

```
/console/payment?orderId={order_id}&amount={总价}&gateway={支付网关名称}
```

---

## 7. 已购域名列表

获取用户已购买的域名列表。

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/host` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `page` | int | 是 | 页码 |
| `limit` | int | 是 | 每页条数，建议 `50` |

**响应示例：**

```json
{
  "status": 200,
  "data": {
    "list": [
      {
        "id": 1001,
        "name": "mydomain.com",
        "domain": "mydomain.com",
        "type": "domain",
        "product_name": "域名注册",
        "status": "Active",
        "module_name": "xxx注册商",
        "registrar": "xxx注册商",
        "active_time": 1700000000,
        "create_time": 1700000000,
        "due_time": 1731536000,
        "lock_status": 1
      }
    ]
  }
}
```

**status 枚举：**

| 值 | 含义 |
|------|------|
| `Active` | 正常 |
| `Suspended` | 已暂停 |
| `Pending` | 处理中 |
| `Expired` | 已过期 |
| `Deleted` | 已删除 |
| `Cancelled` | 已取消 |
| `Fraud` | 欺诈 |

---

## 8. 辅助接口

### 8.1 获取域名产品 ID

通过产品分组遍历查找域名产品，用于加入购物车。

| 方法 | 路径 | 说明 |
|------|------|------|
| `GET` | `/product/group/first` | 获取一级产品分组 |
| `GET` | `/product/group/second?id={group_id}` | 获取二级产品分组 |
| `GET` | `/product?id={second_group_id}` | 获取分组下的产品列表 |
| `GET` | `/product?keywords=域名` | 按关键词搜索产品 |

在产品列表中查找 `name` 包含"域名"或"domain"的产品，取其 `id` 作为 `product_id`。

### 8.2 获取支付网关

| 项目 | 说明 |
|------|------|
| **方法** | `GET` |
| **路径** | `/gateway` |

**响应示例：**

```json
{
  "status": 200,
  "data": {
    "list": [
      { "name": "alipay", "title": "支付宝" },
      { "name": "wxpay", "title": "微信支付" }
    ]
  }
}
```

---

## 9. 完整注册流程

```
1. GET  /idcsmart_domain/config              → 获取配置（后缀列表、协议链接）
2. GET  /idcsmart_domain/domain_suffix       → 获取后缀列表（兜底）
3. GET  /idcsmart_domain/check_domain        → 查询域名是否可注册
4. GET  /idcsmart_domain/get_price           → 获取域名各年限价格
5. GET  /idcsmart_domain/info_template       → 获取用户信息模板列表
6. POST /cart                                → 加入购物车
7. POST /cart/settle                         → 结算、创建订单
8. 跳转 /console/payment?orderId=xxx          → 支付页面
```

---

## 10. 错误处理

所有接口统一错误格式：

```json
{
  "status": 400,
  "msg": "错误描述信息"
}
```

- 网络异常时前端提示"网络错误，请检查连接后重试"
- 业务异常时展示 `msg` 字段内容
- 非 JSON 响应（如 CDN 错误页）时提示"服务器返回异常"
