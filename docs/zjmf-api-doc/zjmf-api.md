# zjmf finance API doc

> crawled from http://w2.test.idcsmart.com/doc at 2026-08-15 09:06 | actions: 1759
> sources: doc_list.json + raw/*.html | re-crawl: powershell -ExecutionPolicy Bypass -File fetch_api_docs.ps1

---

## 前台首页

### 前台首页 -- GET index

- controller: ``app\home\controller\IndexController::index``
- desc: 前台首页 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client":[{//客户信息
      "username":"用户名",
      "phonenumber":"手机号",
      "credit":"余额",
    }]
    "ticket_count":"待处理工单",
    "order_count":"待支付订单",
    "over_due":"即将过期",
    "intotal":"用户消费",
    "invoice_unpaid：本月待支付":"",
    "news":"公告通知",
    "allow_recharge":"是否允许充值，1允许，充值按钮可用，0不允许，充值按钮不可用",
  }
}
```

### 销售列表 -- GET sale_list

- controller: ``app\home\controller\IndexController::SaleList``
- desc: 销售列表 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//销售列表
    }]
  }
}
```

### 清除登录禁用缓存 -- GET del_cwxt_home_login

- controller: ``app\home\controller\IndexController::del_cwxt_home_login``
- desc: 清除登录禁用缓存 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//销售列表
    }]
  }
}
```

### 公共配置 --  GET common_list

- controller: ``app\home\controller\IndexController::common_list``
- desc: 公共配置 -- 菜鸟

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "string logo_url - Logo地址1(登录页)":"",
    "string logo_url_home - Logo地址2(客户中心)":"",
    "string main_tenance_mode - 维护模式1=开启 0=关闭":"",
    "string main_tenance_mode_message - 维护模式提示":"",
    "string main_tenance_mode_url - 维护模式跳转地址":"",
    "string language - 语言":"",
    "string company_name - 公司名称":"",
    "string domain - 网站域名":"",
    "string system_url - 系统链接":"",
    "string company_email - 公司邮箱":"",
    "string certifi_open - 身份认证是否开启1=开启2=关闭":"",
    "string map - 坐标":"",
    "string company_profile - 公司简介":"",
    "string msfntk - 作为cookie写入,并在发送短信时作为token传入":"",
    "string main_phone - 手机":"",
    "string main_address -地址":"",
  }
}
```

### 已开通列表 -- GET create_list

- controller: ``app\home\controller\IndexController::createList``
- desc: 已开通列表 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//销售列表
    }]
  }
}
```

### 头部底部 -- GET /config_general/header

- controller: ``app\home\controller\IndexController::getHeader``
- desc: 头部底部 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "header":"头部",
    "footer":"底部",
  }
}
```

### 友情链接 -- GET config_general/friendlyLinks

- controller: ``app\home\controller\IndexController::getFriendlyLinks``
- desc: 友情链接 -- x

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array":"",
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\IndexController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\IndexController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\IndexController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 微信

### 扫码登录(线上) -- GET /wechat_login

- controller: ``app\home\controller\WechatController::index``
- desc: 扫码登录(线上) -- 上官磨刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| key | 必填 | - | 秘钥(区别设置) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data 微信二维码地址":"",
  }
}
```

### 获取微信二维码配置 -- GET /get_wechat_config

- controller: ``app\home\controller\WechatController::get_wechat_config``
- desc: 获取微信二维码配置 -- 上官磨刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| key | 必填 | - | 秘钥(区别设置) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".appid":"",
    ".redirect_uri":"回调地址",
    ".state":"签名",
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\WechatController::addindexPage``
- desc: 用户可添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| key | 必填 | - | 秘钥(区别设置) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\WechatController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| key | 必填 | - | 秘钥(区别设置) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\WechatController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| key | 必填 | - | 秘钥(区别设置) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台用户

### 基本信息 -- GET /user_info

- controller: ``app\home\controller\UserController::index``
- desc: 基本信息 -- 上官🔪

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
    ".usertype":"用户类型",
    ".sex":"性别",
    ".avatar":"头像",
    ".profession":"职业",
    ".signature":"个性签名",
    ".companyname":"所在公司",
    ".email":"邮件   邮箱有则绑定",
    ".wechat_id":"微信id   大于0有则绑定",
    ".country":"国家",
    ".province":"省份",
    ".city":"城市",
    ".region":"区",
    ".address1":"具体地址1",
    ".postcode":"邮编",
    ".phonenumber":"电话 有则绑定手机",
    ".tax_id":"税号ID",
    ".authmodule":"授权模块",
    ".authdata":"授权数据",
    ".currency":"使用货币ID",
    ".defaultgateway":"选择默认支付接口",
    ".credit":"信用卡",
    ".taxexempt":"免税（1：是",
    ".latefeeoveride":"滞纳金覆盖（1：是；0：否）",
    ".overideduenotices":"覆盖过期notices（是，否）",
    ".separateinvoices":"单独发票（1：是；0：否）",
    ".disableautocc":"禁用自动CC处理（是，否）",
    ".datecreated":"创建日期",
    ".notes":"备注",
    ".billingcid":"付款联系人（子账户）ID",
    ".groupid":"用户组ID",
    ".cardlastfour":"信用卡后四位",
    ".cardnum":"信用卡号",
    ".lastlogin":"最后登录时间",
    ".host":"主机",
    ".status":"状态（1激活，0未激活，2关闭）",
    ".language":"语言",
    ".marketing_emails_opt_in":"发送客户营销邮件（1：是；0：否）",
    ".create_time":"创建时间",
    ".update_time":"更新时间",
    ".pwresetexpiry":"密码重置过期时间",
    ".know_us":"了解途径",
    ".initiative_renew":"是否使用余额自动续费(1使用,0不使用)",
    ".is_login_sms_reminder":"是否开启登录短信提醒(1开启,0不开启)",
    ".email_remind":"是否开启登录邮件提醒(1开启默认,0不开启)",
    ".certifi.status 个人认证信息状态和失败原因(1已认证，2未通过，3待审核，4已提交资料)0=为认证":"",
    ".certifi.type 认证类型certifi_pseson个人认证，certifi_company企业认证":"",
    ".certifi.auth_fail 失败原因":"",
    ".is_password 是否设置密码1=设置 0=未设置":"",
    ".second_verify":" 是否开启二次验证：0否默认，1是",
    "allow_resource_api":"是否开启api设置菜单",
    "allow_second_verify":"是否开启二次验证设置菜单",
    "second_verify_action_home":"需要二次验证的动作,数组(['name'=>'on','name_zh'=>'开机'],",
    "cart_product_description":"购物车页面 应用说明",
    "shd_allow_sms_send":"短信设置",
    "shd_allow_email_send":"邮件设置",
  }
}
```

### 二次验证切换开关 -- POST /toggle_second_verify

- controller: ``app\home\controller\UserController::toggleSecondVerify``
- desc: 二次验证切换开关 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| second_verify | tinyint | 非必填 | 0 | 0关闭，1开启 | - |
| code | 字符串 | 非必填 | 0 | 验证码 | - |
| type | 字符串 | 非必填 | 0 | 发送验证方式,email,phone | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 二次验证页面 -- GET /second_verify_page

- controller: ``app\home\controller\UserController::getSecondVerifyPage``
- desc: 二次验证页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 二次验证发送验证码 -- POST /second_verify_send

- controller: ``app\home\controller\UserController::secondVerifySend``
- desc: 二次验证发送验证码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 0 | 发送方式email,phone | - |
| action | 字符串 | 必填 | 0 | 发送动作(closed关闭二次验证) | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改api秘钥页面 -- GET /get_api_pwd

- controller: ``app\home\controller\UserController::getApiPwd``
- desc: 修改api秘钥页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改api秘钥 -- POST /modify_api_pwd

- controller: ``app\home\controller\UserController::modifyApiPwd``
- desc: 修改api秘钥 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| api_password | 字符串 | 非必填 | 0 | api秘钥 | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 随机生成api秘钥 -- GET /auto_api_pwd

- controller: ``app\home\controller\UserController::autoApiPwd``
- desc: 随机生成api秘钥 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改用户资料 -- PUT /user_info

- controller: ``app\home\controller\UserController::update``
- desc: 修改用户资料 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 必填 | 1 | - | 用户名 |
| sex | 整型 | 必填 | 1 | - | 性别（0未知，1男，2女） |
| avatar | 字符串 | 非必填 | 1 | - | 头像 |
| profession | 字符串 | 非必填 | 1 | - | 职业 |
| signature | 字符串 | 非必填 | 1 | - | 个性签名 |
| companyname | 字符串 | 非必填 | 1 | - | 所在公司 |
| email | 字符串 | 非必填 | 0 | - | 邮件 |
| country | 字符串 | 非必填 | 0 | - | 国家 |
| province | 字符串 | 非必填 | 0 | - | 省份 |
| city | 字符串 | 非必填 | 0 | - | 城市 |
| region | 字符串 | 非必填 | 0 | - | 区 |
| address1 | 字符串 | 非必填 | 1 | - | 具体地址1 |
| postcode | 字符串 | 非必填 | 1 | - | 邮编 |
| phone_code | 整型 | 非必填 | 1 | - | 电话区号 |
| phonenumber | 字符串 | 非必填 | 1 | - | 电话 |
| currency | 整型 | 必填 | 1 | - | 使用货币ID |
| defaultgateway | 字符串 | 必填 | 1 | - | 选择默认支付接口 |
| notes | 字符串 | 非必填 | 0 | - | 管理员备注 |
| groupid | 整型 | 非必填 | 0 | - | 用户组ID |
| status | 整型 | 非必填 | 0 | - | 状态（0未激活，1激活，2关闭） |
| language | 字符串 | 必填 | 0 | - | 语言(传zh_cn/zh_xg/en_us等) |
| know_us | 字符串 | 非必填 | 0 | - | 了解途径 |
| custom[id] | 字符串 | 必填 | 0 | - | 自定义字段值.形式：custom[id] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
    ".usertype":"用户类型",
    ".sex":"性别",
    ".avatar":"头像",
    ".profession":"职业",
    ".signature":"个性签名",
    ".companyname":"所在公司",
    ".email":"邮件",
    ".country":"国家",
    ".province":"省份",
    ".city":"城市",
    ".region":"区",
    ".address1":"具体地址1",
    ".postcode":"邮编",
    ".phonenumber":"电话",
    ".tax_id":"税号ID",
    ".authmodule":"授权模块",
    ".authdata":"授权数据",
    ".currency":"使用货币ID",
    ".defaultgateway":"选择默认支付接口",
    ".credit":"信用卡",
    ".taxexempt":"免税（1：是",
    ".latefeeoveride":"滞纳金覆盖（1：是；0：否）",
    ".overideduenotices":"覆盖过期notices（是，否）",
    ".separateinvoices":"单独发票（1：是；0：否）",
    ".disableautocc":"禁用自动CC处理（是，否）",
    ".datecreated":"创建日期",
    ".notes":"备注",
    ".billingcid":"付款联系人（子账户）ID",
    ".groupid":"用户组ID",
    ".cardlastfour":"信用卡后四位",
    ".cardnum":"信用卡号",
    ".lastlogin":"最后登录时间",
    ".host":"主机",
    ".status":"状态（1激活，0未激活，2关闭）",
    ".language":"语言",
    ".marketing_emails_opt_in":"发送客户营销邮件（1：是；0：否）",
    ".create_time":"创建时间",
    ".update_time":"更新时间",
    ".pwresetexpiry":"密码重置过期时间",
    ".know_us":"了解途径",
  }
}
```

### 绑定手机:发送验证码 --页面 -- GET check_origin_phone

- controller: ``app\home\controller\UserController::checkOriginPhone``
- desc: 绑定手机:发送验证码 --页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 绑定手机:发送验证码 -- POST /bind_phone

- controller: ``app\home\controller\UserController::bind_phone_send``
- desc: 绑定手机:发送验证码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| mk | 字符串 | 必填 | - | - | common_list接口返回的msfntk作为cookie写入,并在发送短信时作为token传入 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 绑定手机 -- POST bind_phone_handle

- controller: ``app\home\controller\UserController::bind_phone_handle``
- desc: 绑定手机 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| code | 整型 | 必填 | - | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更绑手机：发送手机验证码 -- GET /bind_phone_code

- controller: ``app\home\controller\UserController::bind_phone_code``
- desc: 更绑手机：发送手机验证码 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 整型 | 必填 | - | - | 区号 |
| tel | 整型 | 必填 | - | - | 手机号 |
| mk | 字符串 | 必填 | - | - | common_list接口返回的msfntk作为cookie写入,并在发送短信时作为token传入 |
| type | 整型 | 非必填 | - | - | 1为原手机验证，2为新手机验证 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更绑手机 -- POST bind_phone_change

- controller: ``app\home\controller\UserController::bind_phone_change``
- desc: 更绑手机 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tel | 整型 | 必填 | - | - | 手机号 |
| code | 整型 | 必填 | - | - | 验证码 |
| type | 整型 | 非必填 | - | - | 1为原手机验证，2为新手机验证 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 展示 绑定微信二维码 -- GET /bind_wechat

- controller: ``app\home\controller\UserController::bind_wechat``
- desc: 展示 绑定微信二维码 -- 上官🔪

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data 微信二维码地址":"",
  }
}
```

### 微信绑定处理 -- GET bind_wechat_handle/:id/

- controller: ``app\home\controller\UserController::bind_wechat_handle``
- desc: 微信绑定处理 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| code | 整型 | 必填 | - | - | 用户授权 |
| state | 整型 | 必填 | - | - | 微信state |
| id | 整型 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱绑定:获取验证码 -- POST /bind_email

- controller: ``app\home\controller\UserController::bind_email``
- desc: 邮箱绑定:获取验证码 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | str | 必填 | - | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱绑定:执行 -- POST /bind_email_handle

- controller: ``app\home\controller\UserController::bind_email_handle``
- desc: 邮箱绑定:执行 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | str | 必填 | - | - | 邮箱 |
| code | 整型 | 必填 | - | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱更绑:获取验证码 -- POST /change_email

- controller: ``app\home\controller\UserController::change_email``
- desc: 邮箱更绑:获取验证码 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | str | 必填 | - | - | 邮箱 |
| type | 整型 | 非必填 | 1 | - | 1：原邮箱获取，2：新邮箱获取 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱更绑:执行验证 -- POST /change_email_handle

- controller: ``app\home\controller\UserController::change_email_handle``
- desc: 邮箱更绑:执行验证 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | str | 必填 | - | - | 邮箱 |
| code | str | 必填 | - | - | 验证码 |
| type | 整型 | 非必填 | 1 | - | 1：原邮箱验证，2：新邮箱验证 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户日志 -- GET /user_action_log/:page/

- controller: ``app\home\controller\UserController::user_action_log``
- desc: 用户日志 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 分页 |
| page_size | 整型 | 非必填 | 10 | - | 页数据 |
| action | 整型 | 非必填 | 空字符传 | - | login=登录日志 |
| keywords | 整型 | 非必填 | - | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"",
    ".username":"用户名",
    ".url":"拜访资源",
    ".ip":"ip",
    ".create_time":"时间戳",
  }
}
```

### 地区列表 -- GET /areas/:pid/

- controller: ``app\home\controller\UserController::areas``
- desc: 地区列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | 1 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".area_id":" 地区id",
    ".name":"名称",
    ".pid":"父级id",
  }
}
```

### 获取国家列表 -- GET /country

- controller: ``app\home\controller\UserController::country``
- desc: 获取国家列表 -- 上官🔪

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":" 国家id",
    ".name":"名称",
  }
}
```

### 用户修改密码 -- POST modify_password

- controller: ``app\home\controller\UserController::modifyPassword``
- desc: 用户修改密码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| old_password | 字符串 | 非必填 | 1 | - | - |
| password | 字符串 | 必填 | 1 | - | 新密码 |
| re_password | 字符串 | 必填 | 1 | - | 重复新密码 |
| code | 字符串 | 必填 | 1 | - | 验证码 |
| flag | 字符串 | 必填 | 1 | - | 1为有原密码2为没有原密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 登录短信提醒 --  POST login_sms_reminder

- controller: ``app\home\controller\UserController::loginSmsReminder``
- desc: 登录短信提醒 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| status | 整型 | 必填 | 0 | - | 开启1=开启0=关闭 |
| code | 整型 | 非必填 | 0 | - | 关闭的时候需要短信验证 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 登录提醒关闭验证短信发送 -- GET /remind_send

- controller: ``app\home\controller\UserController::remindSend``
- desc: 登录提醒关闭验证短信发送 -- lyj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| captcha | 整型 | 非必填 | 0 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 登录提醒关闭验证邮件发送 -- GET /remind_email_send

- controller: ``app\home\controller\UserController::remindEmailSend``
- desc: 登录提醒关闭验证邮件发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| captcha | 整型 | 非必填 | 0 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 登录邮件提醒 --  POST login_email_reminder

- controller: ``app\home\controller\UserController::loginEmailReminder``
- desc: 登录邮件提醒 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| status | 整型 | 必填 | 0 | - | 开启1=开启0=关闭 |
| code | 整型 | 非必填 | 0 | - | 关闭的时候需要短信验证 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取地址信息 --  GET get_areas

- controller: ``app\home\controller\UserController::getAreas``
- desc: 获取地址信息 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "areas  - 地址信息(数组)":"",
    "country  - 国家信息(数组)":"",
  }
}
```

### 获取销售员 --  GET get_saler

- controller: ``app\home\controller\UserController::getSaler``
- desc: 获取销售员 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//销售员数据
      "id":"销售员id",
      "user_nickname":"销售员昵称",
      "user_email":"销售员邮箱",
    }]
    "saleset":[{//是否显示销售
    }]
  }
}
```

### 设定销售员 --  POST set_saler

- controller: ``app\home\controller\UserController::setSaler``
- desc: 设定销售员 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 用户id |
| sale_id | 整型 | 非必填 | 1 | - | 销售员id |
| type | 整型 | 非必填 | 1 | - | 1下单2注册 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 注销 -- GET /logOut

- controller: ``app\home\controller\UserController::logOut``
- desc: 注销 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\UserController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\UserController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\UserController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台登录

### 登录、注册页面 -- GET /login_register_index

- controller: ``app\home\controller\LoginController::LoginRegisterIndex``
- desc: 登录、注册页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "allow_second_verify":"是否开启二次验证设置菜单",
    "second_verify_action_home":"需要二次验证的动作,数组(['name'=>'on','name_zh'=>'开机'],",
  }
}
```

### 登录短信验证码发送 -- POST /login_send

- controller: ``app\home\controller\LoginController::mobileSend``
- desc: 登录短信验证码发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| mk | 字符串 | 必填 | - | - | common_list接口返回的msfntk作为cookie写入,并在发送短信时作为token传入 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 手机+验证码登录页面(手机+密码登录页面)获取国际手机区号 -- GET /mobile_login_page

- controller: ``app\home\controller\LoginController::mobileLoginVerifyPage``
- desc: 手机+验证码登录页面(手机+密码登录页面)获取国际手机区号 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| code | 字符串 | 必填 | 1 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 手机+验证码登录 -- POST /mobile_login

- controller: ``app\home\controller\LoginController::mobileLoginVerify``
- desc: 手机+验证码登录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| code | 字符串 | 必填 | 1 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 手机+密码登录 -- POST /login_pass_phone

- controller: ``app\home\controller\LoginController::phonePassLogin``
- desc: 手机+密码登录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| code | 字符串 | 非必填 | 0 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱+密码登录 -- POST /login_pass_email

- controller: ``app\home\controller\LoginController::emailLogin``
- desc: 邮箱+密码登录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮箱 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| code | 字符串 | 非必填 | 1 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推介计划入口 -- GET /aff

- controller: ``app\home\controller\LoginController::aff``
- desc: 推介计划入口 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### api接口登录、验证 -- POST /zjmf_api_login

- controller: ``app\home\controller\LoginController::zjmfApiLogin``
- desc: api接口登录、验证 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 必填 | 1 | - | 用户名(手机号+区号 |
| password | 字符串 | 必填 | 1 | - | 密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池登录供应商 -- POST /resource_login_supplier

- controller: ``app\home\controller\LoginController::resourceLogin``
- desc: 资源池登录供应商 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 必填 | 1 | - | 用户名(手机号+区号 |
| password | 字符串 | 必填 | 1 | - | 密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品服务列表页面 -- GET /product_list_page

- controller: ``app\home\controller\LoginController::getProuductlistPage``
- desc: 产品服务列表页面 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 非必填 | - | - | 产品类型(dcim智简魔方裸金属,dcimcloud智简魔方云)不传则为通用产品 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"产品组ID",
    "name":"产品组名称",
    "headline":"产品组标题",
    "tagline":"产品组标语",
    "order_frm_tpl":"该产品组的购买模板",
    "disabled_gateways":"隐藏的网关，以逗号分隔",
    "hidden":"是否隐藏",
    "order":"排序",
    "create_time":"创建时间",
    "update_time":"修改时间",
    "products":[{//产品信息
      "id":"产品ID",
      "gid":"产品组ID",
      "type":"产品类型",
      "pay_type":"产品周期",
      "qty":"库存",
      "auto_setup":"自动开通：order，下单后；payment：支付后；on：手动审核",
    }]
  }
}
```

### 二次验证页面 -- GET login/second_verify_page

- controller: ``app\home\controller\LoginController::getSecondVerifyPage``
- desc: 二次验证页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 非必填 | 0 | 用户名 | - |
| password | 字符串 | 非必填 | 0 | 密码 | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 二次验证发送验证码 -- POST login/second_verify_send

- controller: ``app\home\controller\LoginController::secondVerifySend``
- desc: 二次验证发送验证码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 0 | 发送方式email,phone | - |
| action | 字符串 | 必填 | 0 | 发送动作(closed关闭二次验证) | - |
| username | 字符串 | 非必填 | 0 | 用户名 | - |
| password | 字符串 | 非必填 | 0 | 密码 | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 验证码图形 -- GET verify

- controller: ``app\home\controller\LoginController::verify``
- desc: 验证码图形 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台注册和密码重置

### 短信注册--验证码发送 -- POST /register_phone_send

- controller: ``app\home\controller\RegisterController::registerPhoneSend``
- desc: 短信注册--验证码发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| mk | 字符串 | 必填 | - | - | common_list接口返回的msfntk作为cookie写入,并在发送短信时作为token传入 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱注册--验证码发送 -- POST /register_email_send

- controller: ``app\home\controller\RegisterController::registerEmailSend``
- desc: 邮箱注册--验证码发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 手机注册 -- POST /register_phone

- controller: ``app\home\controller\RegisterController::registerPhone``
- desc: 手机注册 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 手机号区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| code | 字符串 | 必填 | 1 | - | 验证码 |
| sale_id | 字符串 | 非必填 | 1 | - | 销售员 |
| captcha | 字符串 | 必填 | 1 | - | 图形验证码 |
| fields[自定义字段ID] | 数组 | 非必填 | 1 | - | 值,此字段非必传参数,数组形式 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱注册 -- POST /register_email

- controller: ``app\home\controller\RegisterController::registerEmail``
- desc: 邮箱注册 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 手机号 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| code | 字符串 | 必填 | 1 | - | 验证码 |
| sale_id | 字符串 | 非必填 | 1 | - | 销售员 |
| captcha | 字符串 | 必填 | 1 | - | 图形验证码 |
| fields[自定义字段ID] | 数组 | 非必填 | 1 | - | 值,此字段非必传参数,数组形式 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 短信密码重置发送验证码 -- POST /reset_phone_send

- controller: ``app\home\controller\RegisterController::resetPhoneSend``
- desc: 短信密码重置发送验证码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| mk | 字符串 | 必填 | - | - | common_list接口返回的msfntk作为cookie写入,并在发送短信时作为token传入 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱密码重置发送验证码 -- POST /reset_email_send

- controller: ``app\home\controller\RegisterController::resetEmailSend``
- desc: 邮箱密码重置发送验证码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 手机验证码密码重置 -- POST /reset_phone

- controller: ``app\home\controller\RegisterController::passPhoneReset``
- desc: 手机验证码密码重置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| code | 字符串 | 必填 | 1 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮箱验证码密码重置 -- POST /reset_email

- controller: ``app\home\controller\RegisterController::passEmailReset``
- desc: 邮箱验证码密码重置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮箱 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| code | 字符串 | 必填 | 1 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 设定销售员 --  POST set_saler

- controller: ``app\home\controller\RegisterController::getSalerId``
- desc: 设定销售员 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sale_id | 整型 | 非必填 | 1 | - | 销售员id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台实名认证资料提交

### 认证首页(判断) -- GET /certifi

- controller: ``app\home\controller\CertificationController::certifi``
- desc: 认证首页(判断) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".certifi_message":"认证信息，空，则未认证过;",
    ".auth_user_id":"用户id",
    ".auth_rela_name":"真实姓名",
    ".auth_card_type":"认证方式1=大陆 0 =非大陆",
    ".auth_card_number":"认证卡号",
    ".company_name":"公司名称",
    ".company_organ_code":"公司代码",
    ".img_one":"正面照片",
    ".img_two":"反面照片",
    ".img_three":"公司执照",
    ".status":"认证状态1已认证，2未通过，3待审核，4已提交资料0为认证",
    ".certifi_is_upload":"是否上传图片1=上传2=不上传",
    ".cerify_id":"阿里认证id",
    ".auth_fail":"失败原因",
    ".create_time":"创建时间",
    ".update_time":"修改时间",
    ".certifi.type 认证类型certifi_company=企业认证，certifi_person=个人认证":"",
  }
}
```

### 企业认证资料提交 -- POST /company_certifi_post

- controller: ``app\home\controller\CertificationController::companyCertifiPost``
- desc: 企业认证资料提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| certifi_type | 字符串 | 非必填 | 1 | - | 选择类型 |
| company_name | 字符串 | 必填 | 1 | - | 企业名称 |
| company_organ_code | 字符串 | 必填 | 1 | - | 营业执照号码 |
| real_name | 字符串 | 必填 | 1 | - | 提交人姓名 |
| card_type | tinyint | 必填 | 1 | - | card类型：1内地身份证(默认)；0港澳台身份证 |
| idcard | 字符串 | 必填 | 1 | - | 身份证号 |
| idimage[] | image | 必填 | 1 | - | 身份证正面、反面、公司营业执照（多文件上传） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 个人认证转企业认证 -- POST /person_to_company

- controller: ``app\home\controller\CertificationController::personToCompany``
- desc: 个人认证转企业认证 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| company_name | 字符串 | 必填 | 1 | - | 企业名称 |
| company_organ_code | 字符串 | 必填 | 1 | - | 营业执照号码 |
| real_name | 字符串 | 必填 | 1 | - | 提交人姓名 |
| card_type | tinyint | 必填 | 1 | - | card类型：1内地身份证(默认)；0港澳台身份证 |
| idcard | 字符串 | 必填 | 1 | - | 身份证号 |
| idimage[] | image | 必填 | 1 | - | 身份证正面、反面、公司营业执照（多文件上传） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 查询认证是否完成 -- GET /certifi_ping

- controller: ``app\home\controller\CertificationController::ping``
- desc: 查询认证是否完成 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "status":"400失败，200成功",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\CertificationController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\CertificationController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\CertificationController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\CertificationController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台工单

### 工单创建页面数据 -- GET ticket/ticket_page

- controller: ``app\home\controller\TicketController::getOpenTicketPage``
- desc: 工单创建页面数据 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "have_user":"1",
    "user_info":[{//用户信息d
      "username":"用户名",
      "email":"邮箱",
    }]
    "host_list":"用户产品列表",
    "priority":"优先级",
    "depart":[{//部门d
      "id":"部门id",
      "name":"部门名称",
    }]
    "hosts":[{//产品列表
      "id":"购买产品ID",
      "name":"名称",
    }]
  }
}
```

### 获取部门自定义字段数据 -- GET ticket/get_custom

- controller: ``app\home\controller\TicketController::getTicketCustom``
- desc: 获取部门自定义字段数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| depart_id | number | 必填 | - | - | 部门id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"字段id",
    "fieldname":"字段id",
    "fieldtype":"字段id",
    "description":"字段id",
    "fieldoptions":"",
    "child":"当类型dropdown时会存在，下拉数据",
    "regexpr":"验证正则表达式",
    "required":"是否必填",
  }
}
```

### 获取可用部门列表 -- GET ticket/department

- controller: ``app\home\controller\TicketController::getDepartmentList``
- desc: 获取可用部门列表 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"部门id",
    ".name":"部门名称",
  }
}
```

### 创建工单 -- POST ticket/create

- controller: ``app\home\controller\TicketController::createTicket``
- desc: 创建工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 非必填 | - | - | 姓名 |
| email | 字符串 | 非必填 | - | - | 邮箱 |
| dptid | 整型 | 必填 | - | - | 部门id |
| service | 整型 | 非必填 | - | - | 服务id |
| priority | 字符串 | 非必填 | - | - | 优先级 |
| title | 字符串 | 必填 | - | - | 标题 |
| content | 字符串 | 必填 | - | - | 内容 |
| attachment | file | 非必填 | - | - | 附件 |
| customfield | 数组 | 非必填 | - | - | 自定义字段的值 |
| hostid | 数组 | 非必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".tid":"工单号",
    ".c":"验证标识",
  }
}
```

### 工单详情 -- GET ticket/detail

- controller: ``app\home\controller\TicketController::ticketDetail``
- desc: 工单详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 必填 | - | - | 工单号 |
| c | 字符串 | 非必填 | - | - | 工单标识 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".list.id":"工单id|回复id",
    ".list.type":"类型,t工单,r回复",
    ".list.content":"内容",
    ".list.attachment":"附件",
    ".list.format_time":"时间",
    ".list.user":"发出人",
    ".list.user_type":"用户类型",
    ".list.star":"评价星级(只会在管理员回复有)",
    ".ticket.dptid":"工单部门id",
    ".ticket.title":"工单标题",
    ".ticket.status":"工单状态数组",
    ".ticket.priority":"优先级",
    ".evaluate":"客户是否能评价",
  }
}
```

### 回复工单 -- POST ticket/reply

- controller: ``app\home\controller\TicketController::replyTicket``
- desc: 回复工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 必填 | - | - | 工单tid |
| c | 字符串 | 非必填 | - | - | 工单随机字串 |
| name | 字符串 | 非必填 | - | - | 姓名 |
| email | 字符串 | 非必填 | - | - | 邮箱 |
| content | 字符串 | 非必填 | - | - | 内容 |
| attachment | 字符串 | 非必填 | - | - | 附件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关闭工单 -- POST ticket/close

- controller: ``app\home\controller\TicketController::closeTicket``
- desc: 关闭工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 必填 | - | - | 工单号 |
| c | 字符串 | 非必填 | - | - | 工单标识 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价工单回复 -- POST ticket/evaluate

- controller: ``app\home\controller\TicketController::evaluate``
- desc: 评价工单回复 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 必填 | - | - | 工单号 |
| rid | 整型 | 必填 | - | - | 回复id |
| star | 整型 | 必填 | - | - | 评价星级 |
| type | 字符串 | 非必填 | r | - | 回复类下r |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取工单列表 -- GET ticket/list

- controller: ``app\home\controller\TicketController::getList``
- desc: 获取工单列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| limit | - | 非必填 | - | - | 条数 |
| page | 整型 | 非必填 | - | - | 回复id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".limit":"每页条数",
    ".page":"当前页数",
    ".sum":"总条数",
    ".list.id":"工单id",
    ".list.tid":"工单tid",
    ".list.title":"工单标题",
    ".list.status":"工单状态",
    ".list.last_reply_time":"最后回复时间戳",
    ".list.department_name":"部门名称",
    ".list.show_time":"格式化的最后回复时间",
    ".list.client_unread":"是否有未读回复",
  }
}
```

### 下载回复中附件 -- POST直接访问 ticket/download

- controller: ``app\home\controller\TicketController::downloadAttachment``
- desc: 下载回复中附件 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 必填 | - | - | 工单号 |
| c | 字符串 | 非必填 | - | - | 工单标识 |
| rid | 整型 | 必填 | - | - | 工单回复id |
| index | 整型 | 必填 | - | - | 要下载的附件的index |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 下载附件 -- GET ticket/download

- controller: ``app\home\controller\TicketController::download``
- desc: 下载附件 --      liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | ticket=工单ticket_reply=工单回复 |
| id | 字符串 | 必填 | - | - | 文件所属id |
| filename | 整型 | 必填 | - | - | 返回的附件名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\TicketController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\TicketController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\TicketController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\TicketController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台购物车

### 获取分组 -- POST /cart/productsgroups

- controller: ``app\home\controller\CartController::postProductGroups``
- desc: 获取分组 -- xiong

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 导入商品 -- POST /cart/createproducts

- controller: ``app\home\controller\CartController::postCreateProducts``
- desc: 导入商品 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 批量拉取产品信息的前台会员中心接口 -- GET /cart/hostinfo

- controller: ``app\home\controller\CartController::hostInfo``
- desc: 批量拉取产品信息的前台会员中心接口 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid[] | 整型 | 必填 | - | - | 产品ID，数组 |
| all | 整型 | 非必填 | - | - | 当all=1时，表示需要配置数据 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "hosts":[{//基础数据
      "domain":"主机dedicatedip:ipassignedips：附加ipcreate_time：购买时间nextduedate：到期时间billingcycle：周期billingcycle_zhfirstpaymentamount：首付金额amount：续费金额port：端口username：用户名password：密码initiative_renew：自动续费domainstatus：状态domainstatus_zh",
    }]
  }
}
```

### API概览 -- GET /cart/summary

- controller: ``app\home\controller\CartController::summary``
- desc: API概览 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client":[{//基础数据
      "api_password":"api密钥api_create_time:开通时间agent_count:代理商品数量host_count:API产品数量",
      "active_count":"API产品数量",
      "api_count":"昨日api请求次数ratio:日环比up:1上升，0下降",
    }]
    "form_api":"最近7天每天的api请求次数",
    "free_products":[{//豁免产品
      "id":"name:名称ontrial:试用数量qty:最大购买数量",
    }]
  }
}
```

### 获取所有产品 -- GET /cart/all

- controller: ``app\home\controller\CartController::getProducts``
- desc: 获取所有产品 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".products":"所有在售产品",
    ".count":"产品数量",
  }
}
```

### 获取产品配置 -- GET /cart/get_product_config

- controller: ``app\home\controller\CartController::getProductConfig``
- desc: 获取产品配置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".products":"",
  }
}
```

### 产品搜索-- -- GET /cart/global_search

- controller: ``app\home\controller\CartController::globalSearch``
- desc: 产品搜索-- -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 0 | - | 产品搜索关键词 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".product_groups":"所有产品组信息",
    ".product_groups.id":"产品组id",
    ".product_groups.name":"产品组名称",
    ".product_groups.headline":"产品组标题",
    ".product_groups.tagline":"产品组标签",
    ".product_groups.order":"产品组排序",
    ".currencies":"所有货币信息",
    ".currencies.id":"所有货币信息",
    ".currencies.code":"符号",
    ".currencies.prefix":"前缀",
    ".currencies.suffix":"后缀",
    ".default_currency":"默认选中货币",
    ".products":"此产品组下所有产品的信息",
    ".products.id":"产品ID",
    ".products.type":"产品类型",
    ".products.gid":"产品所属产品组",
    ".products.name":"产品名称",
    ".products.description":"产品描述",
    ".products.pay_method":"付费方式(预付费'prepayment','后付费：postpaid'，)",
    ".products.tax":"税率",
    ".products.order":"产品排序",
    ".products.currencyid.product_price":"此产品currencyid货币下的产品价格(选择周期第一个大于等于0的价格数据及相应的初装费)",
    ".products.currencyid.setup_fee":"此产品currencyid货币下的产品初装费",
    ".products.currencyid.billingcycle":"付费周期",
  }
}
```

### 购物车支付，信用接口 -- GET /cartgateway

- controller: ``app\home\controller\CartController::getGateway``
- desc: 购物车支付，信用接口 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "gateways":" 支付方式",
    "client":[{//客户
      "credit":"余额is_open_credit_limit:1开启信用额支付credit_limit_balance:信用额",
    }]
  }
}
```

### 产品首页 -- GET /cart/index

- controller: ``app\home\controller\CartController::index``
- desc: 产品首页 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| first_gid | 整型 | 非必填 | 1 | - | 一级分组ID |
| gid | 整型 | 非必填 | 1 | - | 产品组ID(可选参数，无此参数，默认显示数据库表第一个产品组及其产品) |
| keywords | 字符串 | 非必填 | 0 | - | 产品搜索关键词 |
| type | 字符串 | 非必填 | 1 | - | 官网应用商店列表uuid |
| currencyid | 整型 | 非必填 | 1 | - | 货币ID（可选） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "first_groups":"一级分组信息",
    ".product_groups":"所有产品组信息",
    ".product_groups.id":"产品组id",
    ".product_groups.name":"产品组名称",
    ".product_groups.headline":"产品组标题",
    ".product_groups.tagline":"产品组标签",
    ".product_groups.order":"产品组排序",
    ".currencies":"所有货币信息",
    ".currencies.id":"所有货币信息",
    ".currencies.code":"符号",
    ".currencies.prefix":"前缀",
    ".currencies.suffix":"后缀",
    ".default_currency":"默认选中货币",
    ".products":"此产品组下所有产品的信息",
    ".products.id":"产品ID",
    ".products.type":"产品类型",
    ".products.gid":"产品所属产品组",
    ".products.name":"产品名称",
    ".products.description":"产品描述",
    ".products.pay_method":"付费方式(预付费'prepayment','后付费：postpaid'，)",
    ".products.tax":"税率",
    ".products.order":"产品排序",
    ".products.product_price":"产品价格",
    ".products.sale_price":"客户折扣价格",
    ".products.currencyid.product_price":"此产品currencyid货币下的产品价格(选择周期第一个大于等于0的价格数据及相应的初装费)",
    ".products.currencyid.setup_fee":"此产品currencyid货币下的产品初装费",
    ".products.currencyid.billingcycle":"付费周期",
    "ontrial":"1时,表示有试用周期,ontrial_cycle表示天数，ontrial_price表示产品价格,ontrial_setup_fee表示初装费；0时，不管;ontrial_cycle_type试用单位",
  }
}
```

### 选择配置页面 -- GET /cart/set_config

- controller: ``app\home\controller\CartController::setConfig``
- desc: 选择配置页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 产品ID |
| billingcycle | 整型 | 必填 | 1 | - | 周期 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":"当周期为ontrial时,会多一个参数pay_ontrial_cycle(试用天数)",
  }
}
```

### 配置页面异步请求计算总价-- -- POST /cart/get_total

- controller: ``app\home\controller\CartController::getTotal``
- desc: 配置页面异步请求计算总价-- -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 产品ID |
| billingcycle | 字符串 | 必填 | 1 | - | 周期名称(比如：day、ontrial、monthly、hour等) |
| qty | 字符串 | 非必填 | 1 | - | 非必传,产品数量 |
| configoption[配置项ID] | 字符串 | 必填 | 1 | - | 配置子项ID(或者数量)后端根据配置项ID的类型判断是子项ID还是数量！ |
| currencyid | 整型 | 非必填 | 1 | - | 货币ID |
| customfield[自定义字段ID] | 整型 | 非必填 | 1 | - | 自定义字段 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":"货币信息",
    "products":[{//产品信息
      "name":"",
      "billingcycle":"",
      "product_setup_fee":"产品初装费",
      "product_price":"产品价格",
      "setupfee_total":"产品初装费（总：包括选择多个数量）",
      "price_total":"产品周期费用（总：包括选择多个数量）",
      "signal_setupfee":"单个产品初装费（产品初装费+配置项初装费）",
      "signal_price":"产品周期费用(单个产品,不含初装费)",
      "child":[{//配置项+子项+价格
        "option_name":"配置项名称",
        "suboption_name":"子项名称",
        "suboption_setup_fee":"子项初装费",
        "suboption_price":"子项价格",
        "suboption_price_total":"子项总价",
        "qty":"数量(拉条的数量,前端需要判断是否有此值)",
      }]
    }]
    "total":"总计",
  }
}
```

### 获取购物车页面数据 -- GET /cart/get_shop_data

- controller: ``app\home\controller\CartController::getShopDataPage``
- desc: 获取购物车页面数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| currency | number | 非必填 | - | - | - |
| pos[] | 数组 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":[{//货币数据
      "id":"货币ID",
      "code":"货币标识",
      "prefix":"货币前缀",
      "suffix":"货币后缀",
    }]
    "cart_products":[{//购物车产品数组
      "productid":"产品ID",
      "productsname":"产品名",
      "serverid":"可用区ID",
      "servername":"可用区域名",
      "billingcycle":"周期",
      "billingcycle_desc":"周期描述",
      "configoptions":"可配置选项",
      "pricing":"该产品价格（不带价格单位）",
      "setup_pricing":"该产品安装费用（不带价格单位）",
      "pricing_show":"该产品价格",
      "setup_pricing_show":"该产品安装费用",
    }]
    "promo":[{//优惠码数据
      "promo_desc":"优惠码描述",
      "promo_desc_str":"优惠码描述(带优惠码)",
      "promo_price":"优惠码抵扣价格",
      "promo_price_str":"优惠码抵扣价格（带价格单位）",
    }]
    "total_pricing":"价格小计（带价格单位）",
    "total_setup_pricing":"安装小计（带价格单位）",
    "subtotal":"总价格小计（带价格单位）",
    "total_price":"合计",
    "total_desc":"合计（带价格单位）",
  }
}
```

### 修改购物车产品数量 -- POST /cart/modify_product_qty

- controller: ``app\home\controller\CartController::modifyProductQty``
- desc: 修改购物车产品数量 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i | number | 必填 | - | - | - |
| qty | number | 必填 | - | - | - |
| pos[] | 数组 | 非必填 | - | - | 购物车已选产品位置 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":[{//货币数据
      "id":"货币ID",
      "code":"货币标识",
      "prefix":"货币前缀",
      "suffix":"货币后缀",
    }]
    "cart_products":[{//购物车产品数组
      "productid":"产品ID",
      "productsname":"产品名",
      "serverid":"可用区ID",
      "servername":"可用区域名",
      "billingcycle":"周期",
      "billingcycle_desc":"周期描述",
      "configoptions":"可配置选项",
      "pricing":"该产品价格（不带价格单位）",
      "setup_pricing":"该产品安装费用（不带价格单位）",
      "pricing_show":"该产品价格",
      "setup_pricing_show":"该产品安装费用",
    }]
    "promo":[{//优惠码数据
      "promo_desc":"优惠码描述",
      "promo_desc_str":"优惠码描述(带优惠码)",
      "promo_price":"优惠码抵扣价格",
      "promo_price_str":"优惠码抵扣价格（带价格单位）",
    }]
    "total_pricing":"价格小计（带价格单位）",
    "total_setup_pricing":"安装小计（带价格单位）",
    "subtotal":"总价格小计（带价格单位）",
    "total_price":"合计",
    "total_desc":"合计（带价格单位）",
  }
}
```

### 结算购物车 -- POST /cart/settle

- controller: ``app\home\controller\CartController::settle``
- desc: 结算购物车 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| payment | 字符串 | 非必填 | - | - | 支付方式 |
| pos[] | 数组 | 非必填 | - | - | 产品在购物车位置标识,i=0,1,2……自然数 |
| checkout | 整型 | 非必填 | - | - | 1直接结算，0加入购物车 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "[type]     [description]":"",
    "invoiceid":"账单id(跳转到网关支付页面，可不携带支付方式)",
  }
}
```

### 添加产品到购物车 -- POST /cart/add_to_shop

- controller: ``app\home\controller\CartController::addToShop``
- desc: 添加产品到购物车 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | number | 必填 | - | - | 产品ID |
| billingcycle | 字符串 | 必填 | - | - | 产品周期 |
| qty | 字符串 | 必填 | - | - | 产品数量 |
| serverid | number | 非必填 | - | - | 服务器可用区ID |
| configoption | 数组 | 必填 | - | - | 产品配置数组 |
| customfield | 数组 | 必填 | - | - | 产品自定义字段数组 |
| currencyid | 数组 | 必填 | - | - | 货币ID |
| os[id] | 数组 | 必填 | - | - | 值：操作系统name |
| host | 字符串 | 非必填 | - | - | 主机名 |
| password | 字符串 | 非必填 | - | - | 密码 |
| hostid | 整型 | 非必填 | - | - | 应用产品ID(当产品为开发者应用时,此字段必传) |
| checkout | 整型 | 非必填 | - | - | 1表示直接结算,0加到购物车 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "i":"当前产品在购物车的位置0,1,2……",
  }
}
```

### 重新编辑产品到购物车--页面 -- GET /cart/edit_to_shop_page

- controller: ``app\home\controller\CartController::editToshopPage``
- desc: 重新编辑产品到购物车--页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i | number | 必填 | - | - | 产品在购物车位置标识 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重新编辑产品到购物车 -- POST /cart/edit_to_shop

- controller: ``app\home\controller\CartController::editToShop``
- desc: 重新编辑产品到购物车 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i | number | 必填 | - | - | 产品在购物车位置标识 |
| billingcycle | 字符串 | 必填 | desc: | - | - |
| qty | 字符串 | 必填 | desc: | - | - |
| serverid | number | 非必填 | - | - | 服务器可用区ID |
| configoption | 数组 | 必填 | - | - | 产品配置数组 |
| customfield | 数组 | 必填 | - | - | 产品自定义字段数组 |
| currencyid | 整型 | 必填 | - | - | 货币ID |
| os[id] | 数组 | 必填 | - | - | 值：操作系统name |
| hostid | 整型 | 非必填 | - | - | 应用产品ID(当产品为开发者应用时,此字段必传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加优惠码到购物车 -- POST /cart/add_promo

- controller: ``app\home\controller\CartController::addPromoToShop``
- desc: 添加优惠码到购物车 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| promo | 字符串 | 必填 | - | - | 优惠码代码 |
| currency | number | 非必填 | - | - | 货币id |
| pos[] | 数组 | 非必填 | - | - | 产品在购物车位置标识,i=0,1,2……自然数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "promo":[{//优惠码数据
      "promo_desc":"优惠码描述",
      "promo_desc_str":"优惠码描述(带优惠码)",
      "promo_price":"优惠码抵扣价格",
      "promo_price_str":"优惠码抵扣价格（带价格单位）",
    }]
    "total_price":"合计",
    "total_desc":"合计（带价格单位）",
    "promo_waring_desc":"可能存在的字段，当优惠码正确，当前列表却不适用优惠的时候会出现",
  }
}
```

### 移除优惠码 -- POST /cart/remove_promo

- controller: ``app\home\controller\CartController::removePromoToShop``
- desc: 移除优惠码 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| currency | number | 非必填 | - | - | - |
| pos[] | 数组 | 非必填 | - | - | 产品在购物车位置标识,i=0,1,2……自然数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total_price":"合计",
    "total_desc":"合计（带价格单位）",
  }
}
```

### 移除购物车中产品 -- POST /cart/remove_product

- controller: ``app\home\controller\CartController::removeProduct``
- desc: 移除购物车中产品 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i | number | 必填 | - | - | 循环购物车列表时的key，移除后需要刷新页面 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 购物车检查结算页面 -- GET /cart/check_page

- controller: ``app\home\controller\CartController::checkoutPage``
- desc: 购物车检查结算页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pos[] | 数组 | 非必填 | - | - | 产品在购物车位置标识,i=0,1,2……自然数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "user_login":"是否登录(true,false)",
    "total_price":"购物车合计",
    "total_desc":"购物车显示合计",
    "gateway_list":[{//网关列表
      "id":"网关id",
      "name":"网关",
      "title":"网关显示名",
    }]
    "user_info":[{//用户信息
      "credit":"余额",
    }]
  }
}
```

### 清空购物车 --  POST /cart/clear

- controller: ``app\home\controller\CartController::clearCart``
- desc: 清空购物车 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 官网应用详情页，版本不要，版本不要，版本不要，版本不要，版本不要，版本不要！！！！！！！！！！ -- GET /cart/app_detail

- controller: ``app\home\controller\CartController::getDeveloperAppDetail``
- desc: 官网应用详情页，版本不要，版本不要，版本不要，版本不要，版本不要，版本不要！！！！！！！！！！ -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 应用ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//应用信息(编辑时才有)
      "name":"应用名称info:应用简述type:应用类型description:应用描述instruction:应用说明icon:应用图标pay_type:销售方式pricing:销售价格unretired_time:发布时间",
    }]
    "currency":"货币",
    "product_type":"应用类型--所有",
    "developer":[{//开发者信息
      "name":"开发者昵称desc:简介",
    }]
    "relation_app":"应用作者更多应用，与应用列表一样",
  }
}
```

### 官网 应用列表 -- GET /cart/index_app

- controller: ``app\home\controller\CartController::indexAppHomeOrigin``
- desc: 官网 应用列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| keywords | 整型 | 非必填 | - | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".products":[{//此产品组下所有产品的信息
    }]
  }
}
```

### 应用商店（客户后台 应用） -- GET cart/market_app

- controller: ``app\home\controller\CartController::getMarketApp``
- desc: 应用商店（客户后台 应用） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用排行榜 -- GET cart/app_ranking_list

- controller: ``app\home\controller\CartController::appRankingList``
- desc: 应用排行榜 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用评论 -- GET cart/app/:id/evaluation

- controller: ``app\home\controller\CartController::appEvaluation``
- desc: 应用评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| score | 字符串 | 非必填 | - | - | 查询分数(1,2,3,4,5) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 购物车商品列表(拉取缓存) --  GET cart/prolist

- controller: ``app\home\controller\CartController::proList``
- desc: 购物车商品列表(拉取缓存) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "fgs":[{//商品信息
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\CartController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\CartController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\CartController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 支付

### 可用【支付】网关列表 -- GET /get_gateways/[:gateways]/

- controller: ``app\home\controller\PayController::getGatewayList``
- desc: 可用【支付】网关列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gateways | 字符串 | 必填 | gateways | - | 模块名[gateways,addons] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"id",
    ".name":"网关名",
    ".title":"描述",
    ".module":"所属模块",
  }
}
```

###  -- GET /recharge_page

- controller: ``app\home\controller\PayController::rechargePage``
- desc: 请设置title注释 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| size | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段(trans_id,amount_in,pay_time,type,gateway) |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| keywords | 字符串 | 非必填 | - | - | 关键字搜索(trans_id,amount_in) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "trans_id":"流水单号",
    "amount_in":"金额",
    "pay_time":"交易日期",
    "type":"来源",
    "gateway":"支付方式",
  }
}
```

### 余额充值 -- POST /recharge

- controller: ``app\home\controller\PayController::recharge``
- desc: 余额充值 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| payment | 字符串 | 必填 | - | - | 网关名(如WxPay) |
| amount | decimal | 必填 | - | - | 金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 账单页面数据 -- POST /invoice_page

- controller: ``app\home\controller\PayController::invoicePage``
- desc: 账单页面数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 账单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_subtotal":"小计",
    "invoice_credit":"账单已使用余额",
    "invoice_total":"合计",
    "due_time":"账单到期时间",
    "item_data":[{//账单子项数据
      "description":"账单子项描述",
      "type":"账单子项类型",
      "amount":"账单子项金额",
    }]
    "gateway_list":"支持的网关数据",
    "user_credit":"用户可用余额",
  }
}
```

### 使用余额--页面 -- GET /use_credit_page

- controller: ``app\home\controller\PayController::useCreditPage``
- desc: 使用余额--页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 要支付的账单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoiceid":"账单ID",
    "invoice_credit":"账单已使用余额",
    "total":"账单总额",
    "credit":"用户余额",
    "amount":"剩余需支付金额",
    "currency":"货币信息",
    "used":"是否已使用余额,1是（打钩），0否",
  }
}
```

### 向账单使用余额 -- POST /apply_credit

- controller: ``app\home\controller\PayController::applyCredit``
- desc: 向账单使用余额 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 账单id |
| use_credit | 浮点型 | 必填 | - | - | 1使用余额,0不使用 |
| enough | 整型 | 非必填 | 0 | - | 全部够才支付 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 向账单使用信用额 -- POST /apply_credit_limit

- controller: ``app\home\controller\PayController::applyCreditLimit``
- desc: 向账单使用信用额 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 账单id |
| use_credit_limit | 浮点型 | 必填 | - | - | 1使用余额,0不使用 |
| enough | 整型 | 非必填 | 0 | - | 全部够才支付 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取网关支付页面数据 -- POST /start_pay

- controller: ``app\home\controller\PayController::startPay``
- desc: 获取网关支付页面数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 要支付的账单id |
| payment | 字符串 | 非必填 | - | - | 支付方式 |
| flag | number | 非必填 | - | - | 是否不调三方支付:1是 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "payment":"支付方式",
    "gateway_list":[{//支付方式列表数据
      "name":"支付方式title:名称",
    }]
    "total":"金额",
    "total_desc":"金额",
    "pay_html":"支付html",
  }
}
```

### 更新支付选择模式 -- POST /change_paymt

- controller: ``app\home\controller\PayController::changePaymt``
- desc: 更新支付选择模式 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\PayController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\PayController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\PayController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\PayController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台新闻

### 新闻分类

- controller: ``app\home\controller\NewsController::newsCate``
- desc: 新闻分类 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 帮助分类

- controller: ``app\home\controller\NewsController::helpCate``
- desc: 帮助分类 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 分类

- controller: ``app\home\controller\NewsController::cateList``
- desc: 分类 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 新闻列表页 -- GET news/list

- controller: ``app\home\controller\NewsController::getList``
- desc: 新闻列表页 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| parent_id | 整型 | 非必填 | 1 | - | 分类id |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页个数 |
| search | 字符串 | 非必填 | - | - | 搜索关键词 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//新闻数据
      "id":"文章id",
      "title":"新闻标题",
      "description":"描述",
      "head_img":"封面图片",
    }]
    "pagecount":"每页显示条数",
    "page":"当前页码",
    "total_page":"总页码",
    "count":"总新闻数量",
  }
}
```

### 工单帮助首页 -- GET news/notice

- controller: ``app\home\controller\NewsController::getNotice``
- desc: 工单帮助首页 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"新闻分类id",
    "title":"新闻分类标题",
    "list":[{//帮助数据
      "id":"文章id",
      "title":"新闻分类标题",
      "description":"描述",
      "head_img":"封面图片",
    }]
  }
}
```

### 获取新闻内容 -- GET news/content

- controller: ``app\home\controller\NewsController::getContent``
- desc: 获取新闻内容 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 新闻id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "new_content":"新闻数据",
    "cat_data":"分类数据",
    "next":"下一个对象，如果没有则为空对象",
    "prev":"上一个",
  }
}
```

### 新闻分类所有数据 -- GET /news/catelist

- controller: ``app\home\controller\NewsController::getCateList``
- desc: 新闻分类所有数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| parent_id | 整型 | 非必填 | - | - | 父级id，默认获取所有分页数据，此参数传0获取所有顶级分类，1获取新闻分类数据，2获取公告数据 |
| status | 字符串 | 非必填 | - | - | 搜索状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//分类数据
      "id":"分类id",
      "parent_id":"父级id",
      "title":"分类名",
      "status":"是否禁用(1/0)",
      "sort":"排序id",
      "list":"子集数据",
    }]
  }
}
```

### 公告列表页 -- GET notice/list

- controller: ``app\home\controller\NewsController::getNoticeList``
- desc: 公告列表页 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| parent_id | 整型 | 非必填 | 1 | - | 分类id |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页个数 |
| search | 字符串 | 非必填 | - | - | 搜索关键词 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//新闻数据
      "id":"文章id",
      "title":"新闻标题",
      "description":"描述",
      "head_img":"封面图片",
    }]
    "pagecount":"每页显示条数",
    "page":"当前页码",
    "total_page":"总页码",
    "count":"总新闻数量",
  }
}
```

### 获取公告内容 -- GET notice/content

- controller: ``app\home\controller\NewsController::getNoticeContent``
- desc: 获取公告内容 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 公告id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "new_content":"新闻数据",
    "cat_data":"分类数据",
    "next":"下一个对象，如果没有则为空对象",
    "prev":"上一个",
  }
}
```


---

## 系统消息

### 获取系统消息列表 -- GET /sys_messgage

- controller: ``app\home\controller\SystemMessageController::getMessageList``
- desc: 获取系统消息列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 非必填 | 0 | - | 消息类型：0-全部，1-工单消息，2-产品消息，3-站内信，4-活动消息 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 10 | - | 每页个数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"消息id",
      "title":"消息标题",
      "content":"内容",
      "attachment":"附件地址",
      "create_time":"创建时间",
      "read_time":"阅读时间，未阅读则为0",
    }]
    "count":"总数量",
    "unread_count":[{//未阅读统计
      "id":"消息分类idname:消息分类名称unread_num:消息分类-未读数量",
    }]
    "total_page":"总页码",
  }
}
```

### 系统消息列表-未读 -- GET /sys_messgage_unread

- controller: ``app\home\controller\SystemMessageController::getUnreadList``
- desc: 系统消息列表-未读 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 阅读消息 -- GET /read_messgage

- controller: ``app\home\controller\SystemMessageController::readSystemMessage``
- desc: 阅读消息 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids | 数组 | 非必填 | - | - | - |
| type | 字符串 | 非必填 | 0 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除消息 -- GET /delete_messgage

- controller: ``app\home\controller\SystemMessageController::deleteSystemMessage``
- desc: 删除消息 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids | 数组 | 非必填 | - | - | - |
| type | 字符串 | 非必填 | 0 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台子账户管理

### 客户子账户管理页面 -- GET contacts/index

- controller: ``app\home\controller\ContactsController::index``
- desc: 客户子账户管理页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 整型 | 非必填 | - | - | 子账户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "contact_list":[{//子账户列表
      "id":"子账户id",
      "username":"用户名",
      "email":"子账户邮箱账号",
    }]
    "cid":"子账户id",
    "contact_data":[{//子账户数据
      "id":"子账户id",
      "username":"用户名",
      "sex":"性别",
      "avatar":"头像地址",
      "companyname":"公司名",
      "email":"邮箱",
      "wechat_id":"微信id",
      "country":"国家",
      "province":"省份",
      "city":"城市",
      "region":"区",
      "address1":"地址一",
      "address2":"地址二",
      "postcode":"邮编",
      "phonenumber":"手机号",
      "permissions":"权限（使用另一个permissions_arr字段）",
      "generalemails":"接收通用邮件通知",
      "invoiceemails":"接收账单通知",
      "productemails":"接收产品邮件通知",
      "supportemails":"接收工单邮件通知",
      "authmodule":"",
      "authdata":"",
      "lastlogin":"",
      "status":"",
    }]
  }
}
```

### 保存/添加子账户 -- POST contacts/save

- controller: ``app\home\controller\ContactsController::save``
- desc: 保存/添加子账户 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 整型 | 非必填 | - | - | 子账户id |
| username | 字符串 | 非必填 | - | - | 用户名 |
| sex | 整型 | 必填 | 0 | - | 性别(0未知，1男，2女) |
| avatar | 字符串 | 非必填 | - | - | 头像地址 |
| companyname | 字符串 | 非必填 | - | - | 公司名 |
| email | 字符串 | 必填 | - | - | 邮箱 |
| wechat_id | 字符串 | 非必填 | - | - | 微信id |
| country | 字符串 | 非必填 | - | - | 国家 |
| province | 字符串 | 非必填 | - | - | 省份 |
| city | 字符串 | 非必填 | - | - | 城市 |
| region | 字符串 | 非必填 | - | - | 区 |
| address1 | 字符串 | 非必填 | - | - | 地址一 |
| address2 | 字符串 | 非必填 | - | - | 地址二 |
| postcode | number | 非必填 | - | - | 邮编 |
| phonenumber | 整型 | 非必填 | - | - | 手机号 |
| generalemails | 整型 | 非必填 | - | - | 接收通用邮件通知(0,1) |
| invoiceemails | 整型 | 非必填 | - | - | 接收账单通知 |
| productemails | 整型 | 非必填 | - | - | 接收产品邮件通知 |
| supportemails | 整型 | 非必填 | - | - | 接收工单邮件通知 |
| status | 整型 | 非必填 | - | - | 状态（1激活，0未激活，2关闭）,激活代表该联系人成为子账户，可以登录管理 |
| password | 字符串 | 非必填 | - | - | 设置的子账户密码 |
| permissions | 数组 | 非必填 | - | - | 权限数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除子账户 -- DELETE contacts/del

- controller: ``app\home\controller\ContactsController::delete``
- desc: 删除子账户 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| cid | 整型 | 必填 | - | - | 子账户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\ContactsController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\ContactsController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\ContactsController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台产品功能及接口

### 获取主机列表 -- GET host/list

- controller: ``app\home\controller\HostController::getList``
- desc: 获取主机列表 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| groupid | 字符串 | 非必填 | other | - | 分组id |
| dcim_area | 字符串 | 非必填 | other | - | 区域搜索(传名称) |
| domain_status | 字符串 | 非必填 | other | - | 按状态搜索(数组方式传参) |
| orderby | 字符串 | 非必填 | id | - | 排序方式('id','domainstatus','productname','regdate','nextduedate','amount'） |
| sort | 字符串 | 非必填 | id | DESC | 排序类型DESC/ASC |
| show_type | 字符串 | 非必填 | id | DESC | 首页index，列表页list |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//主机列表数据
      "id":"主机id",
      "domainstatus":"机器状态(Pending待审核,Active已激活,Suspended暂停,Terminated已删除,Cancelled已取消,Fraud有欺诈,Completed已完成)",
      "dedicatedip":"主ip",
      "assignedips":"分配的IP",
      "nextinvoicedate":"下次续约日期",
      "regdate":"开通时间",
      "nextduedate":"到期时间",
      "notes":"备注",
      "amount":"续费金额",
      "billingcycle":"周期",
      "productname":"产品名称",
      "cycle_desc":"展示周期",
      "price_desc":"展示价格",
      "os":"操作系统",
      "svg":"操作系统图标",
      "area_code":"区域代码",
      "area_name":"区域名称",
    }]
    "page":"当前页数",
    "limit":"每页条数",
    "sum":"总条数",
    "max_page":"总页数",
    "orderby":"排序字段",
    "sort":"排序方向",
    "auth.traffic":"流量图(on开启off关闭)",
    "auth.kvm":"kvm(on开启off关闭)",
    "auth.ikvm":"ikvm(on开启off关闭)",
    "auth.bmc":"重置bmc(on开启off关闭)",
    "auth.reinstall":"重装系统(on开启off关闭)",
    "auth.reboot":"重启(on开启off关闭)",
    "auth.on":"开机(on开启off关闭)",
    "auth.off":"关机(on开启off关闭)",
    "auth.novnc":"novnc(on开启off关闭)",
    "auth.rescue":"救援系统(on开启off关闭)",
    "auth.crack_pass":"重置密码(on开启off关闭)",
    "area":[{//区域信息
      "code":"区域代码",
      "name":"区域名称",
    }]
    "domainstatus":"产品状态",
  }
}
```

### 修改备注 --  POST /host/remark

- controller: ``app\home\controller\HostController::postRemark``
- desc: 修改备注 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostID |
| remark | 字符串 | 必填 | - | - | 备注信息 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加/修改分类 -- POST host/savecate

- controller: ``app\home\controller\HostController::postSaveCate``
- desc: 添加/修改分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cate_id | 整型 | 非必填 | - | - | 分类id，不传递时为添加 |
| cate_name | 字符串 | 必填 | - | - | 分类名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分类 -- DELETE host/cate

- controller: ``app\home\controller\HostController::deleteCate``
- desc: 删除分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cate_id | 整型 | 非必填 | - | - | 分类id，不传递时为添加 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 将主机转移到某个分类 -- POST host/transfercate

- controller: ``app\home\controller\HostController::postTransferCate``
- desc: 将主机转移到某个分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 主机id |
| cate_id | 整型 | 必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品内页数据 id:文件标题 -- GET host/details

- controller: ``app\home\controller\HostController::getDetails``
- desc: 产品内页数据 id:文件标题 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host_data":[{//基础数据
      "ordernum":"订单id",
      "productid":"产品id",
      "serverid":"服务器id",
      "regdate":"产品开通时间",
      "domain":"主机名",
      "payment":"支付方式",
      "firstpaymentamount":"首付金额",
      "firstpaymentamount_desc":"首付金额",
      "amount":"续费金额",
      "amount_desc":"续费金额",
      "billingcycle":"付款周期",
      "billingcycle_desc":"付款周期",
      "nextduedate":"到期时间",
      "nextinvoicedate":"下次帐单时间",
      "dedicatedip":"独立ip",
      "assignedips":"附加ip",
      "ip_num":"IP数量",
      "domainstatus":"产品状态",
      "domainstatus_desc":"产品状态",
      "username":"服务器用户名",
      "password":"服务器密码",
      "suspendreason":"暂停原因",
      "auto_terminate_end_cycle":"是否到期取消",
      "auto_terminate_reason":"取消原因",
      "productname":"产品名",
      "groupname":"产品组名",
      "bwusage":"当前使用流量",
      "bwlimit":"当前使用流量上限(0表示不限)",
      "os":"操作系统",
      "port":"端口",
      "remark":"备注",
    }]
    "config_options":[{//可配置选项
      "name":"配置名",
      "sub_name":"配置项值",
    }]
    "custom_field_data":[{//自定义字段
      "fieldname":"字段名",
      "value":"字段值",
    }]
    "download_data":[{//可下载数据
      "id":"文件id",
    }]
    "module_button":[{//模块按钮
      "type":"default:默认,custom:自定义",
      "type":"func:函数名",
      "type":"name:名称",
    }]
    "module_client_area":"模块页面输出",
    "hook_output":"钩子在本页面的输出，数组，循环显示的html",
    "dcim.flowpacket":[{//当前产品可购买的流量包
      "id":"流量包ID",
      "name":"流量包名称",
      "price":"价格",
      "sale_times":"销售次数",
      "stock":"库存(0不限)",
    }]
    "dcim.auth":"服务器各种操作权限控制(on有权限off没权限)",
    "dcim.area_code":"区域代码",
    "dcim.area_name":"区域名称",
    "dcim.os_group":[{//操作系统分组
      "id":"分组ID",
      "name":"分组名称",
      "svg":"分组svg号",
    }]
    "dcim.os":[{//操作系统数据
      "id":"操作系统ID",
      "name":"操作系统名称",
      "ostype":"操作系统类型(1windows0linux)",
      "os_name":"操作系统真实名称(用来判断具体的版本和操作系统)",
      "group_id":"所属分组ID",
    }]
    "flow_packet_use_list":[{//流量包使用情况
      "name":"流量包名称",
      "capacity":"流量包大小",
      "price":"价格",
      "pay_time":"支付时间",
      "used":"已用流量",
      "used":"已用流量",
    }]
    "host_cancel":" 取消请求数据,空对象",
  }
}
```

### 产品内页数据 -- GET host/product

- controller: ``app\home\controller\HostController::getProduct``
- desc: 产品内页数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host_data":[{//基础数据
      "ordernum":"订单id",
      "productid":"产品id",
      "serverid":"服务器id",
      "regdate":"产品开通时间",
      "domain":"主机名",
      "payment":"支付方式",
      "firstpaymentamount":"首付金额",
      "firstpaymentamount_desc":"首付金额",
      "amount":"续费金额",
      "amount_desc":"续费金额",
      "billingcycle":"付款周期",
      "billingcycle_desc":"付款周期",
      "nextduedate":"到期时间",
      "nextinvoicedate":"下次帐单时间",
      "dedicatedip":"独立ip",
      "assignedips":"附加ip",
      "ip_num":"IP数量",
      "domainstatus":"产品状态",
      "domainstatus_desc":"产品状态",
      "username":"服务器用户名",
      "password":"服务器密码",
      "suspendreason":"暂停原因",
      "auto_terminate_end_cycle":"是否到期取消",
      "auto_terminate_reason":"取消原因",
      "productname":"产品名",
      "groupname":"产品组名",
      "bwusage":"当前使用流量",
      "bwlimit":"当前使用流量上限(0表示不限)",
      "os":"操作系统",
      "port":"端口",
      "remark":"备注",
      "allow_upgrade_config":"是否输出“升级配置项”按钮：1是",
      "allow_upgrade_product":"是否输出“升级产品”按钮：1是",
      "show_traffic_usage":"是否显示用量图",
    }]
    "config_options":[{//可配置选项
      "name":"配置名",
      "sub_name":"配置项值",
    }]
    "module_button":[{//模块按钮
      "type":"default:默认,custom:自定义",
      "type":"func:函数名",
      "type":"name:名称",
    }]
  }
}
```

### 产品内页下载数据 -- GET host/down

- controller: ``app\home\controller\HostController::getDown``
- desc: 产品内页下载数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| productid | 整型 | 必填 | - | - | 产品id |
| domainstatus | 整型 | 必填 | - | - | 主机状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "download_data":[{//基础数据
    }]
  }
}
```

### 产品内页取消原因 -- GET host/cancel

- controller: ``app\home\controller\HostController::getCancel``
- desc: 产品内页取消原因 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 主机id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "download_data":[{//基础数据
    }]
  }
}
```

### 产品内页系统 -- GET host/cloudos

- controller: ``app\home\controller\HostController::getCloudOs``
- desc: 产品内页系统 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| productid | 整型 | 必填 | - | - | 产品id |
| os_config_option_id | 整型 | 必填 | - | - | 操作系统id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "cloud_os":[{//云操作系统
      "id":"操作系统ID",
      "name":"名称",
      "group":"分组id",
    }]
    "cloud_os_group":[{//云操作系统分组
      "id":"分组id",
      "name":"分组名称",
    }]
  }
}
```

### 产品内页图表 -- GET host/chart

- controller: ``app\home\controller\HostController::getChart``
- desc: 产品内页图表 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 主机id |
| api_type | 整型 | 必填 | - | - | api类型 |
| domainstatus | 整型 | 必填 | - | - | 主机状态 |
| type | 整型 | 必填 | - | - | 类型 |
| zjmf_api_id | 整型 | 必填 | - | - | zjmf_api_id |
| dcimid | 整型 | 必填 | - | - | dcimid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":[{//基础数据
    }]
  }
}
```

### 产品内页moudle -- GET host/moudle

- controller: ``app\home\controller\HostController::getMoudle``
- desc: 产品内页moudle -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| productid | 整型 | 必填 | - | - | 产品id |
| host_id | 整型 | 必填 | - | - | 主机id |
| api_type | 整型 | 必填 | - | - | api类型 |
| domainstatus | 整型 | 必填 | - | - | 主机状态 |
| type | 整型 | 必填 | - | - | 类型 |
| zjmf_api_id | 整型 | 必填 | - | - | zjmf_api_id |
| dcimid | 整型 | 必填 | - | - | dcimid |
| bwlimit | 整型 | 必填 | - | - | bwlimit |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":[{//基础数据
    }]
  }
}
```

### 产品内页流量包数据 -- GET host/flowpacket

- controller: ``app\home\controller\HostController::getFlowpacket``
- desc: 产品内页流量包数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| host_id | 整型 | 必填 | - | - | 主机id |
| productid | 整型 | 必填 | - | - | 产品id |
| serverid | 整型 | 必填 | - | - | 服务器id |
| api_type | 整型 | 必填 | - | - | apitype |
| upstream_price_type | 整型 | 必填 | - | - | upstream_price_type |
| zjmf_api_id | 整型 | 必填 | - | - | zjmf_api_id |
| dcimid | 整型 | 必填 | - | - | dcimid |
| upstream_price_value | 整型 | 必填 | - | - | upstream_price_value |
| type | 整型 | 必填 | - | - | 类型 |
| bwlimit | 整型 | 必填 | - | - | bwlimit |
| bwusage | 整型 | 必填 | - | - | bwusage |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品内页数据 id:文件标题 -- GET host/header

- controller: ``app\home\controller\HostController::getHeader``
- desc: 产品内页数据 id:文件标题 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host_data":[{//基础数据
      "ordernum":"订单id",
      "productid":"产品id",
      "serverid":"服务器id",
      "regdate":"产品开通时间",
      "domain":"主机名",
      "payment":"支付方式",
      "firstpaymentamount":"首付金额",
      "firstpaymentamount_desc":"首付金额",
      "amount":"续费金额",
      "amount_desc":"续费金额",
      "billingcycle":"付款周期",
      "billingcycle_desc":"付款周期",
      "nextduedate":"到期时间",
      "nextinvoicedate":"下次帐单时间",
      "dedicatedip":"独立ip",
      "assignedips":"附加ip",
      "ip_num":"IP数量",
      "domainstatus":"产品状态",
      "domainstatus_desc":"产品状态",
      "username":"服务器用户名",
      "password":"服务器密码",
      "suspendreason":"暂停原因",
      "auto_terminate_end_cycle":"是否到期取消",
      "auto_terminate_reason":"取消原因",
      "productname":"产品名",
      "groupname":"产品组名",
      "bwusage":"当前使用流量",
      "bwlimit":"当前使用流量上限(0表示不限)",
      "os":"操作系统",
      "port":"端口",
      "remark":"备注",
      "allow_upgrade_config":"是否输出“升级配置项”按钮：1是",
      "allow_upgrade_product":"是否输出“升级产品”按钮：1是",
      "show_traffic_usage":"是否显示用量图",
    }]
    "config_options":[{//可配置选项
      "name":"配置名",
      "sub_name":"配置项值",
    }]
    "custom_field_data":[{//自定义字段
      "fieldname":"字段名",
      "value":"字段值",
    }]
    "download_data":[{//可下载数据
      "id":"文件id",
    }]
    "module_button":[{//模块按钮
      "type":"default:默认,custom:自定义",
      "type":"func:函数名",
      "type":"name:名称",
    }]
    "module_client_area":[{//模块页面输出
      "key":"键值用于获取内容",
      "name":"名称",
    }]
    "hook_output":"钩子在本页面的输出，数组，循环显示的html",
    "dcim.flowpacket":[{//当前产品可购买的流量包
      "id":"流量包ID",
      "name":"流量包名称",
      "price":"价格",
      "sale_times":"销售次数",
      "stock":"库存(0不限)",
    }]
    "dcim.auth":"服务器各种操作权限控制(on有权限off没权限)",
    "dcim.area_code":"区域代码",
    "dcim.area_name":"区域名称",
    "dcim.os_group":[{//操作系统分组
      "id":"分组ID",
      "name":"分组名称",
      "svg":"分组svg号",
    }]
    "dcim.os":[{//操作系统数据
      "id":"操作系统ID",
      "name":"操作系统名称",
      "ostype":"操作系统类型(1windows0linux)",
      "os_name":"操作系统真实名称(用来判断具体的版本和操作系统)",
      "group_id":"所属分组ID",
    }]
    "flow_packet_use_list":[{//流量包使用情况
      "name":"流量包名称",
      "capacity":"流量包大小",
      "price":"价格",
      "pay_time":"支付时间",
      "used":"已用流量",
      "leave":"剩余流量",
    }]
    "cloud_os":[{//云操作系统
      "id":"操作系统ID",
      "name":"名称",
      "group":"分组id",
    }]
    "cloud_os_group":[{//云操作系统分组
      "id":"分组id",
      "name":"分组名称",
    }]
    "system_config.company_name":"系统公司名",
    "dcimcloud.nat_acl":"远程地址",
    "dcimcloud.nat_web":"建站解析",
    "module_power_status":"是否请求电源状态",
  }
}
```

### 产品转移根据邮箱或手机号获取用户信息 -- POST host/nametouser

- controller: ``app\home\controller\HostController::postNameToUser``
- desc: 产品转移根据邮箱或手机号获取用户信息 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tranfer_name | 字符串 | 必填 | - | - | 查找到邮箱或者手机号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"用户id(调用转移时需要)",
    "username":"用户名(展示给客户确认)",
  }
}
```

### 提交转移请求 -- POST host/transfer

- controller: ``app\home\controller\HostController::postTransfer``
- desc: 提交转移请求 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | number | 必填 | - | - | 主机id |
| remarks | 字符串 | 必填 | - | - | 备注 |
| transfer_uid | number | 必填 | - | - | 接收人id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 取消产品转移(发起端) -- POST host/canceltranfer

- controller: ``app\home\controller\HostController::postCancelTranfer``
- desc: 取消产品转移(发起端) -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| transfer_id | number | 必填 | - | - | 转移id(有请求时会显示到产品内页) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 接收产品转移(接收端) -- POST host/teceivetranfer

- controller: ``app\home\controller\HostController::postReceiveTranfer``
- desc: 接收产品转移(接收端) -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| transfer_id | number | 必填 | - | - | 转移id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 拒绝产品转移(接收端) -- POST host/refusetranfer

- controller: ``app\home\controller\HostController::postRefuseTranfer``
- desc: 拒绝产品转移(接收端) -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| transfer_id | number | 必填 | - | - | 转移id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品续费--页面 -- GET host/renewpage

- controller: ``app\home\controller\HostController::getRenewPage``
- desc: 产品续费--页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id,可传单个值hostid |
| billingcycles | number | 非必填 | - | - | 周期 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host":[{//产品数据
    }]
    "cycle":"可用周期",
    "accounts":[{//充值记录
      "trans_id":"交易流水amount_in:金额pay_time:交易日期type:来源gateway:支付方式",
    }]
  }
}
```

### 产品续费--页面(模板调用) -- GET host/renewpageview

- controller: ``app\home\controller\HostController::getRenewPageView``
- desc: 产品续费--页面(模板调用) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host":[{//产品数据
    }]
    "cycle":"可用周期",
    "accounts":[{//充值记录
      "trans_id":"交易流水amount_in:金额pay_time:交易日期type:来源gateway:支付方式",
    }]
  }
}
```

### 产品续费--页面=--充值记录 -- GET host/hostrecharge

- controller: ``app\home\controller\HostController::getHostRecharge``
- desc: 产品续费--页面=--充值记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | 整型 | 非必填 | - | - | 产品ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "trans_id":"流水单号",
    "amount_in":"金额",
    "pay_time":"交易日期",
    "type":"来源",
    "gateway":"支付方式",
    "amount_out":"退款金额(取负数)",
    "refund":"当refund>0时,表示退款，展示amount_in == amout_out的数据,并取负数",
  }
}
```

### 产品续费 -- POST host/renew

- controller: ``app\home\controller\HostController::postRenew``
- desc: 产品续费 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id,可传单个值hostid |
| billingcycles | number | 非必填 | - | - | 周期 |
| duration | number | 非必填 | - | - | 周期时间，秒 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品设置自动续费 -- POST host/autorenew

- controller: ``app\home\controller\HostController::postAutoRenew``
- desc: 产品设置自动续费 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id,可传单个值hostid |
| initiative_renew | number | 非必填 | - | - | 是否自动续费 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 结算批量续费（页面） -- POST host/batchrenewpage

- controller: ``app\home\controller\HostController::postBatchRenewPage``
- desc: 结算批量续费（页面） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_ids | 数组 | 必填 | - | - | 批量续费的产品数组 |
| cycles[产品ID] | 数组 | 非必填 | - | - | (可选参数,第一次不传,在续费页面修改周期时传递此值)批量续费的产品周期:cycles[38] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":"货币信息",
    "hosts":[{//产品信息
      "id":[{//产品IDname
      }]
    }]
    "total":"总价",
  }
}
```

### 结算批量续费（下单） -- POST host/batchrenew

- controller: ``app\home\controller\HostController::postBatchRenew``
- desc: 结算批量续费（下单） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_ids | 数组 | 必填 | - | - | 批量续费的产品数组 |
| cycles[产品ID] | 数组 | 必填 | - | - | 相应周期数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoiceid":"(跳转到网关支付页面，可不携带支付方式)",
  }
}
```

### 按小时/天的产品续费（支付本周期费用） -- POST host/hourdayrenew

- controller: ``app\home\controller\HostController::postHourDayRenew``
- desc: 按小时/天的产品续费（支付本周期费用） -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | number | 必填 | - | - | 主机id |
| settlement | number | 非必填 | 0 | - | 0/1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "hostname_desc":"产品描述",
    "domainstatus":"产品状态",
    "expire_date":"当前到期时间",
    "cost":"费用",
    "cost_desc":"费用",
    "invoiceid":"当传递settlement为1，结算时返回账单id，跳转到通用账单支付页面",
  }
}
```

### 试用/小时/天 转包年包月 -- POST host/cycletomonyear

- controller: ``app\home\controller\HostController::postCycleToMonYear``
- desc: 试用/小时/天 转包年包月 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | number | 必填 | - | - | 主机id |
| change_cycle | 字符串 | 非必填 | - | - | 相应周期数组 |
| settlement | number | 非必填 | 0 | - | 0/1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "hostname_desc":"产品描述",
    "domainstatus":"产品状态",
    "expire_date":"当前到期时间",
    "cycle_desc_data":"支持的转换周期",
    "cost":"费用",
    "cost_desc":"费用",
    "invoiceid":"当传递settlement为1，结算时返回账单id，跳转到通用账单支付页面",
  }
}
```

### 请求取消页面 -- GET host/cancelpage

- controller: ``app\home\controller\HostController::getCancelPage``
- desc: 请求取消页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 申请取消的产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "info":"显示在页面上的提示信息",
    "reason":"默认两个原因,可选，可填",
    "type":"类型，Immediate(立即)，Endofbilling(等待账单周期结束)。写死在页面上",
  }
}
```

### 提交请求取消请求 -- POST host/cancel

- controller: ``app\home\controller\HostController::postCancel``
- desc: 提交请求取消请求 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 申请取消的产品id |
| type | 字符串 | 必填 | - | - | Immediate(立即),Endofbilling(等待账单周期结束) |
| reason | 字符串 | 必填 | - | - | 申请取消该产品的描述信息 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除 产品取消请求 -- DELETE host/cancel

- controller: ``app\home\controller\HostController::deleteCancel``
- desc: 删除 产品取消请求 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用量信息 --  GET host/trafficusage

- controller: ``app\home\controller\HostController::getTrafficUsage``
- desc: 获取用量信息 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| start | 字符串 | 非必填 | - | - | 开始日期(YYYY-MM-DD) |
| end | 字符串 | 非必填 | - | - | 结束日期(YYYY-MM-DD) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//流量数据
      "time":"横坐标值",
      "value":"纵坐标值(单位Mbps)",
    }]
  }
}
```

### 二次验证页面 --  POST host/secondverifypage

- controller: ``app\home\controller\HostController::postSecondVerify``
- desc: 二次验证页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 必填 | - | - | 验证方式：email、phone等 |
| type | 整型 | 必填 | - | - | 验证方式：email、phone等 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关联下游ID --  POST host/setdownstream

- controller: ``app\home\controller\HostController::postSetDownStream``
- desc: 关联下游ID -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品内页数据 id:文件标题 -- GET host/dedicatedserver

- controller: ``app\home\controller\HostController::getDedicatedServer``
- desc: 产品内页数据 id:文件标题 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| host_id | 整型 | 必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host_data":[{//基础数据
      "ordernum":"订单id",
      "productid":"产品id",
      "serverid":"服务器id",
      "regdate":"产品开通时间",
      "domain":"主机名",
      "payment":"支付方式",
      "firstpaymentamount":"首付金额",
      "firstpaymentamount_desc":"首付金额",
      "amount":"续费金额",
      "amount_desc":"续费金额",
      "billingcycle":"付款周期",
      "billingcycle_desc":"付款周期",
      "nextduedate":"到期时间",
      "nextinvoicedate":"下次帐单时间",
      "dedicatedip":"独立ip",
      "assignedips":"附加ip",
      "ip_num":"IP数量",
      "domainstatus":"产品状态",
      "domainstatus_desc":"产品状态",
      "username":"服务器用户名",
      "password":"服务器密码",
      "suspendreason":"暂停原因",
      "auto_terminate_end_cycle":"是否到期取消",
      "auto_terminate_reason":"取消原因",
      "productname":"产品名",
      "groupname":"产品组名",
      "bwusage":"当前使用流量",
      "bwlimit":"当前使用流量上限(0表示不限)",
      "os":"操作系统",
      "port":"端口",
      "remark":"备注",
      "allow_upgrade_config":"是否输出“升级配置项”按钮：1是",
      "allow_upgrade_product":"是否输出“升级产品”按钮：1是",
      "show_traffic_usage":"是否显示用量图",
    }]
    "config_options":[{//可配置选项
      "name":"配置名",
      "sub_name":"配置项值",
    }]
    "custom_field_data":[{//自定义字段
      "fieldname":"字段名",
      "value":"字段值",
    }]
    "download_data":[{//可下载数据
      "id":"文件id",
    }]
    "module_button":[{//模块按钮
      "type":"default:默认,custom:自定义",
      "type":"func:函数名",
      "type":"name:名称",
    }]
    "module_client_area":[{//模块页面输出
      "key":"键值用于获取内容",
      "name":"名称",
    }]
    "hook_output":"钩子在本页面的输出，数组，循环显示的html",
    "dcim.flowpacket":[{//当前产品可购买的流量包
      "id":"流量包ID",
      "name":"流量包名称",
      "price":"价格",
      "sale_times":"销售次数",
      "stock":"库存(0不限)",
    }]
    "dcim.auth":"服务器各种操作权限控制(on有权限off没权限)",
    "dcim.area_code":"区域代码",
    "dcim.area_name":"区域名称",
    "dcim.os_group":[{//操作系统分组
      "id":"分组ID",
      "name":"分组名称",
      "svg":"分组svg号",
    }]
    "dcim.os":[{//操作系统数据
      "id":"操作系统ID",
      "name":"操作系统名称",
      "ostype":"操作系统类型(1windows0linux)",
      "os_name":"操作系统真实名称(用来判断具体的版本和操作系统)",
      "group_id":"所属分组ID",
    }]
    "flow_packet_use_list":[{//流量包使用情况
      "name":"流量包名称",
      "capacity":"流量包大小",
      "price":"价格",
      "pay_time":"支付时间",
      "used":"已用流量",
      "leave":"剩余流量",
    }]
    "cloud_os":[{//云操作系统
      "id":"操作系统ID",
      "name":"名称",
      "group":"分组id",
    }]
    "cloud_os_group":[{//云操作系统分组
      "id":"分组id",
      "name":"分组名称",
    }]
    "system_config.company_name":"系统公司名",
    "dcimcloud.nat_acl":"远程地址",
    "dcimcloud.nat_web":"建站解析",
    "module_power_status":"是否请求电源状态",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\HostController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\HostController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\HostController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\HostController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 钩子文档

### 在管理区添加工单备注 ticket_add_note

- controller: ``app\home\controller\HooksController::ticket_add_note``
- desc: 在管理区添加工单备注 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| content | 字符串 | 必填 | 0 | - | 备注内容 |
| attachment | 数组 | 必填 | 0 | - | 工单附件储存路径 |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 在管理区回复工单 ticket_admin_reply

- controller: ``app\home\controller\HooksController::ticket_admin_reply``
- desc: 在管理区回复工单 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| replyid | 整型 | 必填 | 0 | - | 工单回复ID |
| dptid | 整型 | 必填 | 0 | - | 工单部门ID |
| dptname | 字符串 | 必填 | 0 | - | 工单部门名称 |
| title | 字符串 | 必填 | 0 | - | 工单标题 |
| content | 字符串 | 非必填 | 0 | - | 回复内容 |
| priority | 字符串 | 非必填 | 0 | - | 工单优先级 |
| admin | 字符串 | 非必填 | 0 | - | 管理员名称 |
| status | 整型 | 非必填 | 0 | - | 工单状态ID |
| status_title | 字符串 | 非必填 | 0 | - | 工单状态名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 关闭工单 ticket_close

- controller: ``app\home\controller\HooksController::ticket_close``
- desc: 关闭工单 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 在管理区删除工单 ticket_delete

- controller: ``app\home\controller\HooksController::ticket_delete``
- desc: 在管理区删除工单 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 数组 | 必填 | 0 | - | 工单ID |
| adminid | 整型 | 必填 | 0 | - | 操作的管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 删除工单工单回复后执行 ticket_delete_reply

- controller: ``app\home\controller\HooksController::ticket_delete_reply``
- desc: 删除工单工单回复后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| replyid | 整型 | 必填 | 0 | - | 工单回复ID |
| adminid | 整型 | 必填 | 0 | - | 操作的管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 工单部门变更后执行 ticket_department_change

- controller: ``app\home\controller\HooksController::ticket_department_change``
- desc: 工单部门变更后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| dptid | 整型 | 必填 | 0 | - | 新部门ID |
| dptname | 字符串 | 必填 | 0 | - | 新部门名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 用户新建工单后执行 ticket_open

- controller: ``app\home\controller\HooksController::ticket_open``
- desc: 用户新建工单后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| tid | 字符串 | 必填 | 0 | - | 工单号 |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| dptid | 整型 | 必填 | 0 | - | 部门ID |
| dptname | 字符串 | 必填 | 0 | - | 部门名称 |
| title | 字符串 | 必填 | 0 | - | 工单标题 |
| content | 字符串 | 必填 | 0 | - | 工单内容 |
| priority | 字符串 | 必填 | 0 | - | 优先级 |
| hostid | 整型 | 必填 | 0 | - | 产品ID |
| attachment | 数组 | 必填 | 0 | - | 附件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 在管理区新建工单 ticket_open_admin

- controller: ``app\home\controller\HooksController::ticket_open_admin``
- desc: 在管理区新建工单 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| tid | 字符串 | 必填 | 0 | - | 工单号 |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| dptid | 整型 | 非必填 | 0 | - | 部门ID |
| dptname | 字符串 | 非必填 | 0 | - | 部门名称 |
| title | 字符串 | 非必填 | 0 | - | 工单标题 |
| content | 字符串 | 非必填 | 0 | - | 工单内容 |
| priority | 字符串 | 非必填 | 0 | - | 优先级high高,medium中,low低 |
| attachment | 数组 | 必填 | 0 | - | 附件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 工单状态被管理员手动改变时执行 ticket_status_change

- controller: ``app\home\controller\HooksController::ticket_status_change``
- desc: 工单状态被管理员手动改变时执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 数组 | 必填 | 0 | - | 工单ID |
| status | 整型 | 必填 | 0 | - | 新状态ID |
| status_title | 字符串 | 必填 | 0 | - | 新状态名称 |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 工单标题变更后执行 ticket_title_change

- controller: ``app\home\controller\HooksController::ticket_title_change``
- desc: 工单标题变更后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| title | 字符串 | 必填 | 0 | - | 新标题 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 用户回复工单后执行 ticket_user_reply

- controller: ``app\home\controller\HooksController::ticket_user_reply``
- desc: 用户回复工单后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ticketid | 整型 | 必填 | 0 | - | 工单ID |
| replyid | 整型 | 必填 | 0 | - | 工单回复ID |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| dptid | 整型 | 必填 | 0 | - | 工单部门ID |
| dptname | 字符串 | 必填 | 0 | - | 工单部门名称 |
| title | 字符串 | 必填 | 0 | - | 工单标题 |
| content | 字符串 | 非必填 | 0 | - | 回复内容 |
| priority | 字符串 | 非必填 | 0 | - | 工单优先级 |
| status | 整型 | 非必填 | 0 | - | 工单状态ID |
| status_title | 字符串 | 非必填 | 0 | - | 工单状态名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 每次定时任务之后执行 after_cron

- controller: ``app\home\controller\HooksController::after_cron``
- desc: 每次定时任务之后执行 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 每次定时任务之前执行 before_cron

- controller: ``app\home\controller\HooksController::before_cron``
- desc: 每次定时任务之前执行 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 每五分钟定时任务之后执行 after_five_minute_cron

- controller: ``app\home\controller\HooksController::after_five_minute_cron``
- desc: 每五分钟定时任务之后执行 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 每天定时任务之后执行 after_daily_cron

- controller: ``app\home\controller\HooksController::after_daily_cron``
- desc: 每天定时任务之后执行 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 每天定时任务之前执行 before_daily_cron

- controller: ``app\home\controller\HooksController::before_daily_cron``
- desc: 每天定时任务之前执行 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 定时任务保存后执行 cron_config_save

- controller: ``app\home\controller\HooksController::cron_config_save``
- desc: 定时任务保存后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 操作的管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块升降级成功之后执行 after_module_change_package

- controller: ``app\home\controller\HooksController::after_module_change_package``
- desc: 模块升降级成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块升降级失败之后执行 after_module_change_package_failed

- controller: ``app\home\controller\HooksController::after_module_change_package_failed``
- desc: 模块升降级失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块重置密码成功之后执行 after_module_crack_password

- controller: ``app\home\controller\HooksController::after_module_crack_password``
- desc: 模块重置密码成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | 整型 | 必填 | 0 | - | 产品ID |
| oldpassword | 字符串 | 必填 | 0 | - | 原密码 |
| newspassword | 字符串 | 必填 | 0 | - | 新密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块重置密码失败之后执行 after_module_crack_password_failed

- controller: ``app\home\controller\HooksController::after_module_crack_password_failed``
- desc: 模块重置密码失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块开通成功之后执行 after_module_create

- controller: ``app\home\controller\HooksController::after_module_create``
- desc: 模块开通成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块开通失败之后执行 after_module_create_failed

- controller: ``app\home\controller\HooksController::after_module_create_failed``
- desc: 模块开通失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块暂停成功之后执行 after_module_suspend

- controller: ``app\home\controller\HooksController::after_module_suspend``
- desc: 模块暂停成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块暂停失败之后执行 after_module_suspend_failed

- controller: ``app\home\controller\HooksController::after_module_suspend_failed``
- desc: 模块暂停失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块删除成功之后执行 after_module_terminate

- controller: ``app\home\controller\HooksController::after_module_terminate``
- desc: 模块删除成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块删除失败之后执行 after_module_terminate_failed

- controller: ``app\home\controller\HooksController::after_module_terminate_failed``
- desc: 模块删除失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块解除暂停成功之后执行 after_module_unsuspend

- controller: ``app\home\controller\HooksController::after_module_unsuspend``
- desc: 模块解除暂停成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块解除暂停之后执行 after_module_unsuspend_failed

- controller: ``app\home\controller\HooksController::after_module_unsuspend_failed``
- desc: 模块解除暂停之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块开机成功之后执行 after_module_on

- controller: ``app\home\controller\HooksController::after_module_on``
- desc: 模块开机成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块开机失败之后执行 after_module_on_failed

- controller: ``app\home\controller\HooksController::after_module_on_failed``
- desc: 模块开机失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块关机成功之后执行 after_module_off

- controller: ``app\home\controller\HooksController::after_module_off``
- desc: 模块关机成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块关机失败之后执行 after_module_off_failed

- controller: ``app\home\controller\HooksController::after_module_off_failed``
- desc: 模块关机失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块重启成功之后执行 after_module_reboot

- controller: ``app\home\controller\HooksController::after_module_reboot``
- desc: 模块重启成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块重启失败之后执行 after_module_reboot_failed

- controller: ``app\home\controller\HooksController::after_module_reboot_failed``
- desc: 模块重启失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块硬关机成功之后执行 after_module_hard_off

- controller: ``app\home\controller\HooksController::after_module_hard_off``
- desc: 模块硬关机成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块硬关机失败之后执行 after_module_hard_off_failed

- controller: ``app\home\controller\HooksController::after_module_hard_off_failed``
- desc: 模块硬关机失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块硬重启成功之后执行 after_module_hard_reboot

- controller: ``app\home\controller\HooksController::after_module_hard_reboot``
- desc: 模块硬重启成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块硬重启失败之后执行 after_module_hard_reboot_failed

- controller: ``app\home\controller\HooksController::after_module_hard_reboot_failed``
- desc: 模块硬重启失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块重装系统成功之后执行 after_module_reinstall

- controller: ``app\home\controller\HooksController::after_module_reinstall``
- desc: 模块重装系统成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块重装系统失败之后执行 after_module_reinstall_failed

- controller: ``app\home\controller\HooksController::after_module_reinstall_failed``
- desc: 模块重装系统失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块救援系统成功之后执行 after_module_rescue_system

- controller: ``app\home\controller\HooksController::after_module_rescue_system``
- desc: 模块救援系统成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块救援系统失败之后执行 after_module_rescue_system_failed

- controller: ``app\home\controller\HooksController::after_module_rescue_system_failed``
- desc: 模块救援系统失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块拉取信息成功之后执行 after_module_sync

- controller: ``app\home\controller\HooksController::after_module_sync``
- desc: 模块拉取信息成功之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块拉取信息失败之后执行 after_module_sync_failed

- controller: ``app\home\controller\HooksController::after_module_sync_failed``
- desc: 模块拉取信息失败之后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |
| msg | 字符串 | 必填 | 0 | - | 失败原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 模块升降级之前执行 before_module_change_package

- controller: ``app\home\controller\HooksController::before_module_change_package``
- desc: 模块升降级之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块重置密码之前执行 before_module_crack_password

- controller: ``app\home\controller\HooksController::before_module_crack_password``
- desc: 模块重置密码之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块开通之前执行 before_module_create

- controller: ``app\home\controller\HooksController::before_module_create``
- desc: 模块开通之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块续费之前执行 before_module_renew

- controller: ``app\home\controller\HooksController::before_module_renew``
- desc: 模块续费之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块暂停之前执行 before_module_suspend

- controller: ``app\home\controller\HooksController::before_module_suspend``
- desc: 模块暂停之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块删除之前执行 before_module_terminate

- controller: ``app\home\controller\HooksController::before_module_terminate``
- desc: 模块删除之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块解除暂停之前执行 before_module_unsuspend

- controller: ``app\home\controller\HooksController::before_module_unsuspend``
- desc: 模块解除暂停之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块开机之前执行 before_module_on

- controller: ``app\home\controller\HooksController::before_module_on``
- desc: 模块开机之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块关机之前执行 before_module_off

- controller: ``app\home\controller\HooksController::before_module_off``
- desc: 模块关机之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块重启之前执行 before_module_reboot

- controller: ``app\home\controller\HooksController::before_module_reboot``
- desc: 模块重启之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块硬关机之前执行 before_module_hard_off

- controller: ``app\home\controller\HooksController::before_module_hard_off``
- desc: 模块硬关机之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块硬重启之前执行 before_module_hard_reboot

- controller: ``app\home\controller\HooksController::before_module_hard_reboot``
- desc: 模块硬重启之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块重装系统之前执行 before_module_reinstall

- controller: ``app\home\controller\HooksController::before_module_reinstall``
- desc: 模块重装系统之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块救援系统之前执行 before_module_rescue_system

- controller: ``app\home\controller\HooksController::before_module_rescue_system``
- desc: 模块救援系统之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 模块拉取信息之前执行 before_module_sync

- controller: ``app\home\controller\HooksController::before_module_sync``
- desc: 模块拉取信息之前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| params | 数组 | 必填 | 0 | - | 参考模块开发的params |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回键值对,键值对将会覆盖原来相同键的params,返回exit_module=true将会中断模块方法":"",
  }
}
```

### 后台手动添加交易流水后执行 after_admin_add_account

- controller: ``app\home\controller\HooksController::after_admin_add_account``
- desc: 后台手动添加交易流水后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| account_id | 整型 | 必填 | 0 | - | 交易流水ID |
| amount_in | 浮点型 | 非必填 | - | - | 收入 |
| amount_out | 浮点型 | 非必填 | - | - | 支出 |
| currency | 字符串 | 非必填 | - | - | 货币代码 |
| description | 字符串 | 非必填 | - | - | 描述 |
| trans_id | 字符串 | 非必填 | - | - | 付款流水号 |
| invoice_id | 整型 | 非必填 | - | - | 账单ID |
| gateway | 字符串 | 非必填 | - | - | 付款方式 |
| refund | 整型 | 非必填 | - | - | 是否退款至余额 |
| uid | 整型 | 非必填 | - | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 后台手动编辑交易流水后执行 after_admin_edit_account

- controller: ``app\home\controller\HooksController::after_admin_edit_account``
- desc: 后台手动编辑交易流水后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| account_id | 整型 | 必填 | 0 | - | 交易流水ID |
| amount_in | 浮点型 | 非必填 | - | - | 收入 |
| amount_out | 浮点型 | 非必填 | - | - | 支出 |
| invoice_id | 整型 | 非必填 | - | - | 账单ID |
| gateway | 字符串 | 非必填 | - | - | 付款方式 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 后台手动删除交易流水后执行 after_admin_delete_account

- controller: ``app\home\controller\HooksController::after_admin_delete_account``
- desc: 后台手动删除交易流水后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| account_id | 整型 | 必填 | 0 | - | 交易流水ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 管理员退出登录执行 admin_logout

- controller: ``app\home\controller\HooksController::admin_logout``
- desc: 管理员退出登录执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 管理员登录执行 admin_login

- controller: ``app\home\controller\HooksController::admin_login``
- desc: 管理员登录执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |
| admin | 字符串 | 必填 | 0 | - | 管理员账号 |
| nickname | 字符串 | 必填 | 0 | - | 管理员昵称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 管理员登录系统验证全通过后执行 auth_admin_login

- controller: ``app\home\controller\HooksController::auth_admin_login``
- desc: 管理员登录系统验证全通过后执行 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "status":"true通过验证/false验证失败",
    "msg":"失败信息",
  }
}
```

### 添加管理员后执行 add_admin

- controller: ``app\home\controller\HooksController::add_admin``
- desc: 添加管理员后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑管理员后执行 edit_admin

- controller: ``app\home\controller\HooksController::edit_admin``
- desc: 编辑管理员后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除管理员后执行 delete_admin

- controller: ``app\home\controller\HooksController::delete_admin``
- desc: 删除管理员后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 管理员手动保存产品后执行 after_admin_edit_service

- controller: ``app\home\controller\HooksController::after_admin_edit_service``
- desc: 管理员手动保存产品后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |
| hostid | 整型 | 必填 | 0 | - | 服务ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品转移后执行 -- GET transfer_service

- controller: ``app\home\controller\HooksController::transfer_service``
- desc: 产品转移后执行 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户id |
| transfer_uid | 整型 | 必填 | 0 | - | 接收用户id |
| hostid | 整型 | 必填 | 0 | - | 服务id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 删除服务后执行。 -- GET service_delete

- controller: ``app\home\controller\HooksController::service_delete``
- desc: 删除服务后执行。 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户id |
| hostid | 整型 | 必填 | 0 | - | 服务id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 删除商品后执行 product_delete

- controller: ``app\home\controller\HooksController::product_delete``
- desc: 删除商品后执行 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 0 | - | 商品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 商品创建后执行 product_create

- controller: ``app\home\controller\HooksController::product_create``
- desc: 商品创建后执行 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 0 | - | 商品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 商品编辑后执行 product_edit

- controller: ``app\home\controller\HooksController::product_edit``
- desc: 商品编辑后执行 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 0 | - | 商品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 在创建取消请求时执行 -- GET cancellation_request

- controller: ``app\home\controller\HooksController::cancellation_request``
- desc: 在创建取消请求时执行 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户id |
| relid | 整型 | 必填 | 0 | - | 服务被取消的ID |
| reason | 整型 | 非必填 | 0 | - | 取消原因 |
| type | 字符串 | 非必填 | 0 | - | 取消类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 产品升级后执行 -- GET after_product_upgrade

- controller: ``app\home\controller\HooksController::after_product_upgrade``
- desc: 产品升级后执行 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| upgradeid | 整型 | 必填 | 0 | - | 升级ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 账单支付后邮件发送前执行 invoice_paid_before_email

- controller: ``app\home\controller\HooksController::invoice_paid_before_email``
- desc: 账单支付后邮件发送前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 账单支付后邮件发送后执行 invoice_paid

- controller: ``app\home\controller\HooksController::invoice_paid``
- desc: 账单支付后邮件发送后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 当账单标记为未支付后执行 invoice_mark_unpaid

- controller: ``app\home\controller\HooksController::invoice_mark_unpaid``
- desc: 当账单标记为未支付后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 当账单标记为已取消后执行 invoice_mark_cancelled

- controller: ``app\home\controller\HooksController::invoice_mark_cancelled``
- desc: 当账单标记为已取消后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 当账单删除后执行 invoice_delete

- controller: ``app\home\controller\HooksController::invoice_delete``
- desc: 当账单删除后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 账单退款后执行 invoice_refunded

- controller: ``app\home\controller\HooksController::invoice_refunded``
- desc: 账单退款后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |
| amount | 浮点型 | 必填 | 0 | - | 退款金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 账单备注后执行 invoice_notes

- controller: ``app\home\controller\HooksController::invoice_notes``
- desc: 账单备注后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 账单ID |
| content | 字符串 | 必填 | 0 | - | 备注内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 创建续费账单后 -- GET renew_invoice_create

- controller: ``app\home\controller\HooksController::renew_invoice_create``
- desc: 创建续费账单后 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 生成的账单id |
| hostid | 整型 | 必填 | 0 | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 创建流量包账单后 -- GET flow_packet_invoice_create

- controller: ``app\home\controller\HooksController::flow_packet_invoice_create``
- desc: 创建流量包账单后 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 生成的账单id |
| hostid | 整型 | 必填 | 0 | - | 产品id |
| price | 浮点型 | 必填 | 0 | - | 流量包价格 |
| name | 字符串 | 必填 | 0 | - | 流量包名称 |
| capacity | 字符串 | 必填 | 0 | - | 流量包大小 |
| flowpacketid | 字符串 | 必填 | 0 | - | 流量包ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 合并账单后执行 -- GET invoice_combine

- controller: ``app\home\controller\HooksController::invoice_combine``
- desc: 合并账单后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 0 | - | 生成的账单id |
| combined_invoice | 数组 | 必填 | 0 | - | 合并的账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 订单审核通过后执行 -- GET order_pass_check

- controller: ``app\home\controller\HooksController::order_pass_check``
- desc: 订单审核通过后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| orderid | 整型 | 必填 | 0 | - | 订单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 订单取消后执行 -- GET order_cancel

- controller: ``app\home\controller\HooksController::order_cancel``
- desc: 订单取消后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| orderid | 整型 | 必填 | 0 | - | 订单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 订单删除后执行 -- GET order_delete

- controller: ``app\home\controller\HooksController::order_delete``
- desc: 订单删除后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| orderid | 整型 | 必填 | 0 | - | 订单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 客户添加后 -- POST client_add

- controller: ``app\home\controller\HooksController::client_add``
- desc: 客户添加后 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| userid | 整型 | 必填 | 1 | - | 用户名ID |
| username | 整型 | 必填 | 1 | - | 用户名 |
| sex | 整型 | 必填 | 1 | - | 性别 |
| avatar | 整型 | 非必填 | 1 | - | 头像 |
| profession | 整型 | 非必填 | 1 | - | 职业 |
| signature | 整型 | 非必填 | 1 | - | 个性签名 |
| companyname | 整型 | 非必填 | 1 | - | 所在公司 |
| email | 整型 | 非必填 | 0 | - | 邮件 |
| country | 整型 | 非必填 | 0 | - | 国家 |
| province | 整型 | 非必填 | 0 | - | 省份 |
| city | 整型 | 非必填 | 0 | - | 城市 |
| region | 整型 | 非必填 | 0 | - | 区 |
| address1 | 整型 | 非必填 | 1 | - | 具体地址1 |
| address2 | 整型 | 非必填 | 1 | - | 具体地址2 |
| postcode | 整型 | 非必填 | 1 | - | 邮编 |
| phone_code | 整型 | 非必填 | 1 | - | 电话区号 |
| phonenumber | 整型 | 非必填 | 1 | - | 电话 |
| notes | 整型 | 非必填 | 0 | - | 管理员备注 |
| groupid | 整型 | 非必填 | 0 | - | 用户组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 客户编辑 -- POST client_edit

- controller: ``app\home\controller\HooksController::client_edit``
- desc: 客户编辑 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| userid | 整型 | 必填 | 1 | - | 用户名ID |
| username | 整型 | 必填 | 1 | - | 用户名 |
| sex | 整型 | 必填 | 1 | - | 性别 |
| avatar | 整型 | 非必填 | 1 | - | 头像 |
| profession | 整型 | 非必填 | 1 | - | 职业 |
| signature | 整型 | 非必填 | 1 | - | 个性签名 |
| companyname | 整型 | 非必填 | 1 | - | 所在公司 |
| email | 整型 | 非必填 | 0 | - | 邮件 |
| country | 整型 | 非必填 | 0 | - | 国家 |
| province | 整型 | 非必填 | 0 | - | 省份 |
| city | 整型 | 非必填 | 0 | - | 城市 |
| region | 整型 | 非必填 | 0 | - | 区 |
| address1 | 整型 | 非必填 | 1 | - | 具体地址1 |
| address2 | 整型 | 非必填 | 1 | - | 具体地址2 |
| postcode | 整型 | 非必填 | 1 | - | 邮编 |
| phone_code | 整型 | 非必填 | 1 | - | 电话区号 |
| phonenumber | 整型 | 非必填 | 1 | - | 电话 |
| notes | 整型 | 非必填 | 0 | - | 管理员备注 |
| groupid | 整型 | 非必填 | 0 | - | 用户组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 关闭客户后 -- GET client_close

- controller: ``app\home\controller\HooksController::client_close``
- desc: 关闭客户后 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| userid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 删除客户前 -- GET pre_client_delete

- controller: ``app\home\controller\HooksController::pre_client_delete``
- desc: 删除客户前 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| userid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 删除客户后 -- GET client_delete

- controller: ``app\home\controller\HooksController::client_delete``
- desc: 删除客户后 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| userid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 自定义图形验证码验证 -- GET custom_captcha_check

- controller: ``app\home\controller\HooksController::custom_captcha_check``
- desc: 自定义图形验证码验证 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | allow_login_phone_captcha | - | 图形验证码验证动作，比如allow_login_phone_captcha等 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "bool|null 返回null表示不支持自定义验证，使用系统默认验证方式;返回true表示验证通过，返回false表示验证失败":"",
  }
}
```

### 添加客户前验证(客户端添加或者管理端添加) -- POST client_details_validate

- controller: ``app\home\controller\HooksController::client_details_validate``
- desc: 添加客户前验证(客户端添加或者管理端添加) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 整型 | 必填 | 1 | - | 用户名 |
| sex | 整型 | 必填 | 1 | - | 性别 |
| avatar | 整型 | 非必填 | 1 | - | 头像 |
| profession | 整型 | 非必填 | 1 | - | 职业 |
| signature | 整型 | 非必填 | 1 | - | 个性签名 |
| companyname | 整型 | 非必填 | 1 | - | 所在公司 |
| email | 整型 | 非必填 | 0 | - | 邮件 |
| country | 整型 | 非必填 | 0 | - | 国家 |
| province | 整型 | 非必填 | 0 | - | 省份 |
| city | 整型 | 非必填 | 0 | - | 城市 |
| region | 整型 | 非必填 | 0 | - | 区 |
| address1 | 整型 | 非必填 | 1 | - | 具体地址1 |
| address2 | 整型 | 非必填 | 1 | - | 具体地址2 |
| postcode | 整型 | 非必填 | 1 | - | 邮编 |
| phone_code | 整型 | 非必填 | 1 | - | 电话区号 |
| phonenumber | 整型 | 非必填 | 1 | - | 电话 |
| notes | 整型 | 非必填 | 0 | - | 管理员备注 |
| groupid | 整型 | 非必填 | 0 | - | 用户组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array.错误信息":"",
  }
}
```

### 用户登录后执行 client_login

- controller: ``app\home\controller\HooksController::client_login``
- desc: 用户登录后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| name | 字符串 | 必填 | 0 | - | 用户名称 |
| ip | 字符串 | 必填 | 0 | - | 登录IP |
| jwt | 字符串 | 必填 | 0 | - | 登录jwt |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 用户API登录后执行 client_api_login

- controller: ``app\home\controller\HooksController::client_api_login``
- desc: 用户API登录后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| name | 字符串 | 必填 | 0 | - | 用户名称 |
| ip | 字符串 | 必填 | 0 | - | 登录IP |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 用户重置密码后执行 client_reset_password

- controller: ``app\home\controller\HooksController::client_reset_password``
- desc: 用户重置密码后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| password | 字符串 | 必填 | 0 | - | 新密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 用户退出登录后执行 client_logout

- controller: ``app\home\controller\HooksController::client_logout``
- desc: 用户退出登录后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "无":"",
  }
}
```

### 前台购物车修改购买产品数量后执行 shopping_cart_modify_num

- controller: ``app\home\controller\HooksController::shopping_cart_modify_num``
- desc: 前台购物车修改购买产品数量后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 0 | - | 产品ID |
| num | 整型 | 必填 | 0 | - | 修改后的数量 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 前台购物车结算后执行 shopping_cart_settle

- controller: ``app\home\controller\HooksController::shopping_cart_settle``
- desc: 前台购物车结算后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| total | 整型 | 必填 | 0 | - | 结算金额(可能是免费) |
| invoiceid | 整型 | 必填 | 0 | - | 生成的账单ID |
| hostid | 数组 | 必填 | 0 | - | 生成的产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 前台购物车添加商品后执行 shopping_cart_add_product

- controller: ``app\home\controller\HooksController::shopping_cart_add_product``
- desc: 前台购物车添加商品后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | number | 必填 | - | - | 产品ID |
| qty | 字符串 | 必填 | - | - | 产品数量 |
| serverid | number | 非必填 | - | - | 服务器可用区ID |
| configoption | 数组 | 必填 | - | - | 产品配置数组 |
| customfield | 数组 | 必填 | - | - | 产品自定义字段数组 |
| currencyid | 数组 | 必填 | - | - | 货币ID |
| host | 字符串 | 非必填 | - | - | 主机名 |
| password | 字符串 | 非必填 | - | - | 密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 前台购物车移除商品后执行 shopping_cart_remove_product

- controller: ``app\home\controller\HooksController::shopping_cart_remove_product``
- desc: 前台购物车移除商品后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | number | 必填 | - | - | 产品ID |
| qty | 字符串 | 必填 | - | - | 产品数量 |
| serverid | number | 非必填 | - | - | 服务器可用区ID |
| configoption | 数组 | 必填 | - | - | 产品配置数组 |
| customfield | 数组 | 必填 | - | - | 产品自定义字段数组 |
| currencyid | 数组 | 必填 | - | - | 货币ID |
| host | 字符串 | 非必填 | - | - | 主机名 |
| password | 字符串 | 非必填 | - | - | 密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 前台购物车清空后执行 shopping_cart_clear

- controller: ``app\home\controller\HooksController::shopping_cart_clear``
- desc: 前台购物车清空后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| data | 数组 | 必填 | - | - | 二维数组(pid=产品ID,billingcycle=购买周期,num=购买数量) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 添加服务器后 -- GET server_add

- controller: ``app\home\controller\HooksController::server_add``
- desc: 添加服务器后 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| serverid | 整型 | 必填 | 0 | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 删除服务器前 -- GET server_delete

- controller: ``app\home\controller\HooksController::server_delete``
- desc: 删除服务器前 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| serverid | 整型 | 必填 | 0 | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 编辑服务器前 -- GET server_edit

- controller: ``app\home\controller\HooksController::server_edit``
- desc: 编辑服务器前 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| serverid | 整型 | 必填 | 0 | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 在删除日志前执行 -- GET before_delete_log

- controller: ``app\home\controller\HooksController::before_delete_log``
- desc: 在删除日志前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| adminid | 整型 | 必填 | 0 | - | 管理员ID |
| type | 字符串 | 必填 | 0 | - | 日志类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 添加系统活动日志 -- GET log_activity

- controller: ``app\home\controller\HooksController::log_activity``
- desc: 添加系统活动日志 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| description | 整型 | 必填 | 0 | - | 描述 |
| user | 整型 | 必填 | 0 | - | 操作名(Sub-Account,Client,System) |
| uid | 整型 | 非必填 | 0 | - | 用户id |
| ipaddress | 字符串 | 非必填 | 0 | - | ip地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 用户推介计划激活后执行 -- GET affiliate_activation

- controller: ``app\home\controller\HooksController::affiliate_activation``
- desc: 用户推介计划激活后执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 用户ID |
| affid | 整型 | 必填 | 0 | - | 推介ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "响应：不支持回复":"",
  }
}
```

### 自定义字段值更新时执行 -- GET custom_field_save

- controller: ``app\home\controller\HooksController::custom_field_save``
- desc: 自定义字段值更新时执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| fieldid | 整型 | 必填 | 0 | - | 自定义字段ID |
| relid | 整型 | 必填 | 0 | - | 关联ID |
| value | 字符串 | 必填 | 0 | - | 自定义字段值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "返回['value'=>'新value']用来覆盖自定义字段值":"",
  }
}
```

### 邮件发送前执行 -- GET before_email_send

- controller: ``app\home\controller\HooksController::before_email_send``
- desc: 邮件发送前执行 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 0 | - | 邮箱 |
| subject | 字符串 | 必填 | 0 | - | 主题 |
| content | 字符串 | 必填 | 0 | - | 邮件正文 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 购物车添加优惠码后执行,只执行一次 -- GET after_shop_add_promo

- controller: ``app\home\controller\HooksController::after_shop_add_promo``
- desc: 购物车添加优惠码后执行,只执行一次 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 字符串 | 必填 | 0 | - | 客户ID |
| id | 字符串 | 必填 | 0 | - | 优惠码ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "['status'=>200/400,'msg'=>'消息'] 200成功,400失败且程序不再执行":"",
  }
}
```

### 签订合同之后 -- GET after_sign_contract

- controller: ``app\home\controller\HooksController::after_sign_contract``
- desc: 签订合同之后 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### header头部,模板钩子 -- GET client_area_head_output

- controller: ``app\home\controller\HooksController::client_area_head_output``
- desc: header头部,模板钩子 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### footer底部,模板钩子 -- GET client_area_footer_output

- controller: ``app\home\controller\HooksController::client_area_footer_output``
- desc: footer底部,模板钩子 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台合同模块

### 产品列表,可签订合同产品 -- GET /contract/host

- controller: ``app\home\controller\ContractController::host``
- desc: 产品列表,可签订合同产品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| keywords | 字符串 | 非必填 | 1 | - | 按关键字搜索 |
| type | 字符串 | 非必填 | 1 | - | 时间筛选类型create_time,nextduedate |
| start_time | 整型 | 非必填 | - | - | 时间筛选:开始时间(到期时间),传时间戳(注意：精确到秒) |
| end_time | 整型 | 非必填 | - | - | 时间筛选:结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "hosts":[{//产品列表
      "id":"产品ID",
      "name":"产品名",
      "domain":"主机",
      "amount":"金额",
      "create_time":"下单时间",
      "nextduedate":"到期时间",
      "pdf_num":"合同编号(关联合同)",
      "status_zh":"付款状态",
    }]
  }
}
```

### 签订合同甲方信息管理 -- POST /contract/base_info

- controller: ``app\home\controller\ContractController::contractBaseInfo``
- desc: 签订合同甲方信息管理 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| address1 | 整型 | 必填 | 1 | - | 地址 |
| phonenumber | 整型 | 必填 | 1 | - | 联系电话 |
| email | 整型 | 必填 | 1 | - | 电子邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建合同 -- POST /contract/contract

- controller: ``app\home\controller\ContractController::contractCreate``
- desc: 创建合同 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 必填 | 1 | - | 购买产品的ID（hostID） |
| tplid | 整型 | 必填 | 1 | - | 合同模板ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 签订合同页面 -- GET /contract/contract_page

- controller: ``app\home\controller\ContractController::contract``
- desc: 签订合同页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提交签名 -- POST /contract/contract_sign

- controller: ``app\home\controller\ContractController::contractSign``
- desc: 提交签名 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |
| sign | 整型 | 必填 | 1 | - | 签名base64字符串 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "addr":"签名保存地址",
  }
}
```

### 签订合同 -- POST /contract/contract/:id

- controller: ``app\home\controller\ContractController::contractPost``
- desc: 签订合同 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |
| sign | 整型 | 必填 | 1 | - | 签名base64字符串 |
| content | 整型 | 必填 | 1 | - | 合同内容,传html |
| type | 整型 | 必填 | 1 | - | 类型：I输出到浏览器 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 合同管理 -- GET /contract/contract

- controller: ``app\home\controller\ContractController::contractList``
- desc: 合同管理 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| domainstatus | 字符串 | 非必填 | 1 | - | 按产品状态搜索 |
| status | 字符串 | 非必填 | 1 | - | 按合同状态搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "lists":[{//合同列表
    }]
  }
}
```

### 查看下载 -- GET /contract/download/:id

- controller: ``app\home\controller\ContractController::download``
- desc: 查看下载 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".pdf_address":"合同地址",
  }
}
```

### 申请邮寄页面 -- GET /contract/post/:id

- controller: ``app\home\controller\ContractController::postPage``
- desc: 申请邮寄页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "voucher":[{//收件人信息
      "usernmae":"姓名detail:地址phone：电话",
    }]
  }
}
```

### 申请邮寄 -- POST /contract/post/:id

- controller: ``app\home\controller\ContractController::postPost``
- desc: 申请邮寄 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |
| voucher_id | 整型 | 必填 | 1 | - | 地址信息ID(仅有返回值时才传) |
| username | 整型 | 必填 | 1 | - | 姓名 |
| phone | 整型 | 必填 | 1 | - | 电话 |
| detail | 整型 | 必填 | 1 | - | 地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_id":"账单ID",
  }
}
```

### 邮寄信息 -- GET /contract/mail/:id

- controller: ``app\home\controller\ContractController::mail``
- desc: 邮寄信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 作废 -- GET /contract/cancel/:id

- controller: ``app\home\controller\ContractController::cancel``
- desc: 作废 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除 -- DELETE /contract/delete/:id

- controller: ``app\home\controller\ContractController::delete``
- desc: 删除 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\ContractController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\ContractController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\ContractController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\ContractController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 文件上传(前台)

### 富文本框上传图片 -- POST /uploads

- controller: ``app\home\controller\UploadController::upload``
- desc: 富文本框上传图片 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| image | file | 必填 | 0 | - | 文件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```

### 上传图片 -- POST /upload_image

- controller: ``app\home\controller\UploadController::uploadImage``
- desc: 上传图片 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| image|file | file | 必填 | 0 | - | 图片 |
| type | 字符串 | 必填 | 0 | - | 类型,如avatar,servers |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```

### 上传文件 --  POST home/upload_file

- controller: ``app\home\controller\UploadController::uploadFile``
- desc: 上传文件 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| filename|file | file | 必填 | 0 | - | 文件 |
| type | 字符串 | 必填 | 0 | - | 类型,如avatar,servers |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```


---

## 消费记录

### 用户消费列表(产品)--订单列表 -- GET /invoices

- controller: ``app\home\controller\UserInvoiceController::index``
- desc: 用户消费列表(产品)--订单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段(id,name,amount,create_time,paid_time,status,payment,type) |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 数组 | 非必填 | - | - | 账单状态(Unpaid待支付，Paid已支付，Cancelled已取消) |
| keywords | 数组 | 非必填 | - | - | 账单状态(Unpaid待支付，Paid已支付，Cancelled已取消) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "hosts":[{//列表
      "id":"账单ID",
      "hostid":"产品ID",
      "amount":"金额",
      "create_time":"创建时间",
      "paid_time":"付款时间",
      "status":"状态",
      "payment":"支付方式",
      "type":"类型",
    }]
  }
}
```

### 删除订单 -- DELETE /invoices/:id

- controller: ``app\home\controller\UserInvoiceController::deleteOrder``
- desc: 删除订单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户账单详情 -- GET /invoices/:id

- controller: ``app\home\controller\UserInvoiceController::read``
- desc: 用户账单详情 -- 上官磨刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 账单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoices":[{//账单
      "id":"账单id",
      "uid":"用户id",
      "subtotal":"总计",
      "status":"支付状态",
      "payment":"支付方式",
      "username":"用户名",
    }]
    "host":[{//账单项目
      "num":[{//产品id
      }]
      "type":"类型",
      "description":"描述",
      "amount":"金额",
    }]
  }
}
```

### 账单 -- GET /get_invoices

- controller: ``app\home\controller\UserInvoiceController::getInvoices``
- desc: 账单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段(id,name,amount,create_time,paid_time,status,payment,type) |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 数组 | 非必填 | - | - | 账单状态(Unpaid待支付，Paid已支付,Refunded已退款) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":"货币",
    "total":"总数",
    "invoices":[{//账单
      "id":"",
      "invoice_num":"",
      "subtotal":"",
      "type":"类型",
      "paid_time":"支付日期",
      "due_time":"逾期",
      "status":"支付状态",
      "payment":"支付方式",
    }]
  }
}
```

### 账单详情 -- GET /get_invoices_detail

- controller: ``app\home\controller\UserInvoiceController::getInvoicesDetail``
- desc: 账单详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "payee":"收款人",
    "detail":[{//账单详情
      "username":"用户名",
      "companyname":"公司名",
      "status":"支付状态",
      "paid_time":"支付时间",
      "payment":"支付方式",
      "subtotal":"总计",
      "total":"小计",
      "credit":"余额",
    }]
    "invoice_items":[{//账单子项
      "type":"类型",
      "description":"描述",
      "amount":"金额",
    }]
  }
}
```

### 交易流水记录 -- GET /accounts_record

- controller: ``app\home\controller\UserInvoiceController::accountsRecord``
- desc: 交易流水记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总计",
    "accounts":[{//基础数据
      "id":"",
      "invoice_id":"账单ID",
      "pay_time":"时间",
      "gateway":"支付方式",
      "payment_zh":"支付方式",
      "description":"描述",
      "type":"类型",
      "type_zh":"类型",
      "amount_in":"金额",
      "invoice_id":"账单ID",
      "trans_id":"流水号",
      "refund":[{//退款
        "id":"退款记录ID",
        "amount_out":"退款金额，取负值",
      }]
    }]
  }
}
```

### 余额支付记录 -- GET /credit_record

- controller: ``app\home\controller\UserInvoiceController::creditRecord``
- desc: 余额支付记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总计",
    "accounts":[{//基础数据
      "id":"",
      "create_time":"时间",
      "relid":"账单ID",
      "description":"描述",
      "type":"类型",
      "amount":"金额",
    }]
  }
}
```

### 信用额支付记录 -- GET /credit_limit_record

- controller: ``app\home\controller\UserInvoiceController::creditLimitRecord``
- desc: 信用额支付记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总计",
    "accounts":[{//基础数据
      "id":"",
      "create_time":"时间",
      "relid":"账单ID",
      "description":"描述",
      "type":"类型",
      "amount":"金额",
    }]
  }
}
```

### 充值记录 -- GET /recharge_record

- controller: ``app\home\controller\UserInvoiceController::rechargeRecord``
- desc: 充值记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总计",
    "accounts":[{//基础数据
      "id":"",
      "pay_time":"时间",
      "gateway":"支付方式",
      "payment_zh":"支付方式",
      "description":"描述",
      "type":"类型",
      "type_zh":"类型",
      "amount_in":"金额",
      "invoice_id":"账单ID",
      "trans_id":"流水号",
    }]
  }
}
```

### 退款记录 -- GET /refund_record

- controller: ``app\home\controller\UserInvoiceController::refundRecord``
- desc: 退款记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总计",
    "accounts":[{//基础数据
      "id":"",
      "pay_time":"时间",
      "description":"退款方式",
      "amount_out":"金额",
      "invoice_id":"账单ID",
      "trans_id":"流水号",
    }]
  }
}
```

### 提现记录 -- GET /withdraw_record

- controller: ``app\home\controller\UserInvoiceController::withdrawRecord``
- desc: 提现记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| user_nickname | 字符串 | 非必填 | - | - | 用户名 |
| status | 整型 | 非必填 | - | - | 状态1待审核2通过3拒绝 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总计",
    "rows":[{//基础数据
      "num":"金额",
      "type":"余额1仅记录2流水支持3",
      "create_time":"时间",
      "status":"1待审核2审核通过3拒绝",
      "reason":"来源",
      "des":"描述",
    }]
  }
}
```

### 合并账单页面 -- GET /get_combine_invoices

- controller: ``app\home\controller\UserInvoiceController::getCombineInvoices``
- desc: 合并账单页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids | 数组 | 必填 | 1 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"多少笔未支付",
    "total":"总金额",
    "invoices":[{//账单数据
    }]
  }
}
```

### 合并账单 -- POST /combine_invoices

- controller: ``app\home\controller\UserInvoiceController::combineInvoices``
- desc: 合并账单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids | 数组 | 必填 | 1 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\UserInvoiceController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\UserInvoiceController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\UserInvoiceController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台日志（所有日志接口）

### 操作日志 -- GET user_logs

- controller: ``app\home\controller\RecordLogController::getUserLogs``
- desc: 操作日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| keywords | 整型 | 非必填 | - | - | 关键字 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "description":"描述",
      "user":"用户",
      "ipaddr":"ip地址",
    }]
    "count":"数量",
  }
}
```

### 操作主机日志 -- GET user_logdcims

- controller: ``app\home\controller\RecordLogController::getUserLogDcs``
- desc: 操作主机日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| keywords | 字符串 | 非必填 | - | - | 通过关键字查询 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "description":"描述",
      "user":"用户",
      "ipaddr":"ip地址",
    }]
    "count":"数量",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\RecordLogController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\RecordLogController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\RecordLogController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\RecordLogController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台对接DCIM管理

### 购买流量包生成账单 --  POST /dcim/buy_flow_packet

- controller: ``app\home\controller\DcimController::buyFlowPacket``
- desc: 购买流量包生成账单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |
| fid | 整型 | 必填 | - | - | 流量包ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoiceid":"账单ID",
    "price":"价格",
  }
}
```

### 购买重装次数生成账单 --  POST /dcim/buy_reinstall_times

- controller: ``app\home\controller\DcimController::buyReinstallTimes``
- desc: 购买重装次数生成账单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoiceid":"账单ID",
  }
}
```

### 验证是否可以重装 --  POST /dcim/check_reinstall

- controller: ``app\home\controller\DcimController::checkReinstall``
- desc: 验证是否可以重装 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "num":"本周已重装次数",
    "max_times":"最大重装次数(0不限)",
    "price":"重装次数价格(返回该参数说明已达上限并且可以购买重装次数)",
  }
}
```

### 开机 --  POST /dcim/on

- controller: ``app\home\controller\DcimController::on``
- desc: 开机 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关机 --  POST /dcim/off

- controller: ``app\home\controller\DcimController::off``
- desc: 关机 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重启 --  POST /dcim/reboot

- controller: ``app\home\controller\DcimController::reboot``
- desc: 重启 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置BMC --  POST /dcim/bmc

- controller: ``app\home\controller\DcimController::bmc``
- desc: 重置BMC -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取kvm --  POST /dcim/kvm

- controller: ``app\home\controller\DcimController::kvm``
- desc: 获取kvm -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "name":"下载的文件名",
    "token":"验证标识",
  }
}
```

### 获取ikvm --  POST /dcim/ikvm

- controller: ``app\home\controller\DcimController::ikvm``
- desc: 获取ikvm -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "name":"下载的文件名",
    "token":"验证标识",
  }
}
```

### 下载java文件 --  GET /dcim/download

- controller: ``app\home\controller\DcimController::download``
- desc: 下载java文件 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 要下载的文件名 |
| token | 字符串 | 必填 | - | - | 验证的表示 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重装系统 --  POST /dcim/reinstall

- controller: ``app\home\controller\DcimController::reinstall``
- desc: 重装系统 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| os | 整型 | 必填 | - | - | 操作系统ID |
| password | 字符串 | 必填 | - | - | 密码(六位以上且由大小写字母数字三种组成) |
| mcon | 整型 | 非必填 | - | - | 附加配置ID |
| action | 整型 | 必填 | - | - | 分区(0默认1附加配置) |
| port | 整型 | 必填 | - | - | 端口号 |
| part_type | 整型 | 非必填 | 0 | - | 分区类型(windows才有0全盘格式化1第一分区格式化) |
| disk | 整型 | 非必填 | 0 | - | 磁盘号(从0开始分区为附加配置时不需要) |
| check_disk_size | 整型 | 非必填 | 0 | - | 是否验证磁盘 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "confirm":"失败时可能会返回,true弹出确认框取消或者继续安装,继续安装把参数check_disk_size=0和其他原有参数重新发起重装即可",
    "price":"重装次数价格(返回该参数说明已达上限并且可以购买重装次数)",
  }
}
```

### 获取重装,救援系统,重置密码进度 --  GET /dcim/resintall_status

- controller: ``app\home\controller\DcimController::getReinstallStatus``
- desc: 获取重装,救援系统,重置密码进度 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "disk_check":[{//弹出错误时
      "value":"disk_part的值",
      "description":"描述",
    }]
    "error_type":"0,1,2,其他(当error_type>0并且progress>=20时弹出磁盘分区错误提示,1Windows磁盘错误,2Windows分区错误,其他Windows磁盘分区提示)",
    "error_msg":"当error_type>0时弹出磁盘分区错误提示信息",
    "disk_info":[{//当显示弹出磁盘分区错误提示
      "disk":"磁盘",
      "part":"分区",
      "size":"大小",
      "type":"类型",
      "windows":"类型",
    }]
    "progress":"进度",
    "windows_finish":"是否是windows已完成",
    "hostid":"当前产品ID",
    "task_type":"类型(0重装系统,1救援系统,2重置密码,3获取硬件信息)",
    "reinstall_msg":"重装信息",
    "crackPwd":[{//当有数据返回时,弹出重置密码用户选择
    }]
    "step":"当前步骤描述",
    "last_result.act":"上次执行操作",
    "last_result.status":"上次执行结果(1成功2失败)",
  }
}
```

### 救援系统 --  POST /dcim/rescue

- controller: ``app\home\controller\DcimController::rescue``
- desc: 救援系统 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| system | 整型 | 必填 | - | - | 操作系统(1Linux2Windows) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置密码 --  POST /dcim/crack_pass

- controller: ``app\home\controller\DcimController::crackPass``
- desc: 重置密码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| password | 字符串 | 必填 | - | - | 密码 |
| other_user | 整型 | 非必填 | 0 | - | 是否重置其他用户(0不是1是) |
| user | 字符串 | 非必填 | - | - | 自定义需要重置的用户名(用户名不能包含中文空格@符) |
| action | 字符串 | 非必填 | - | - | 获取进度有crackPwd时选择用户后传chooseUser |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用量信息 --  GET /dcim/traffic_usage

- controller: ``app\home\controller\DcimController::getTrafficUsage``
- desc: 获取用量信息 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| start | 字符串 | 非必填 | - | - | 开始日期(YYYY-MM-DD) |
| end | 字符串 | 非必填 | - | - | 结束日期(YYYY-MM-DD) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//流量数据
      "time":"横坐标值",
      "value":"纵坐标值(单位Mbps)",
    }]
  }
}
```

### 取消重装,救援,重置密码 --  POST /dcim/cancel_task

- controller: ``app\home\controller\DcimController::cancelReinstall``
- desc: 取消重装,救援,重置密码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重装解除暂停 --  POST /dcim/unsuspend_reinstall

- controller: ``app\home\controller\DcimController::unsuspendReload``
- desc: 重装解除暂停 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| disk_part | 字符串 | 必填 | - | - | 重装返回的disk_part |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 刷新所有电源状态 --  POST /dcim/refresh_all_power_status

- controller: ``app\home\controller\DcimController::refreshPowerStatus``
- desc: 刷新所有电源状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 状态为Active的hostID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//列表数据
      "id":"hostID",
      "status":"状态(on开机off关机error无法连接not_support不支持电源控制)",
      "msg":"状态信息描述",
    }]
  }
}
```

### 获取流量图信息 --  POST /dcim/traffic

- controller: ``app\home\controller\DcimController::traffic``
- desc: 获取流量图信息 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| switch_id | 整型 | 必填 | - | - | 交换机ID |
| port_name | 字符串 | 必填 | - | - | 端口名称 |
| start_time | 整型 | 非必填 | - | - | 开始时间(毫秒时间戳) |
| end_time | 整型 | 非必填 | - | - | 结束时间(毫秒时间戳) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "unit":"流量单位",
    "traffic":[{//流量数据
      "time":"毫秒时间戳",
      "value":"值",
      "type":"类型(in进流量,out出流量)",
    }]
  }
}
```

### novnc --  POST /dcim/novnc

- controller: ``app\home\controller\DcimController::novnc``
- desc: novnc -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### novnc页面 --  GET /dcim/novnc

- controller: ``app\home\controller\DcimController::novncPage``
- desc: novnc页面 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| password | 字符串 | 必填 | - | - | novnc返回的密码 |
| url | 整型 | 必填 | - | - | novnc返回的url |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取是否在重装 --  POST /dcim/check_all_status

- controller: ``app\home\controller\DcimController::checkAllReinstallStatus``
- desc: 获取是否在重装 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 状态为Active的hostID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//列表数据
    }]
  }
}
```

### 获取DCIM产品详情 --  GET /dcim/detail

- controller: ``app\home\controller\DcimController::detail``
- desc: 获取DCIM产品详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "switch":[{//交换机数据
      "switch_id":"连接的交换机ID",
      "name":"端口名称",
    }]
  }
}
```

### 隐藏上次重装/重置密码/救援系统结果 --  POST /dcim/hide_result

- controller: ``app\home\controller\DcimController::hideLastResult``
- desc: 隐藏上次重装/重置密码/救援系统结果 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | hostID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取电源状态 --  POST /dcim/refresh_power_status

- controller: ``app\home\controller\DcimController::refreshServerPowerStatus``
- desc: 获取电源状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power":"电源状态(on开机off关机error无法连接not_support不支持电源控制)",
    "msg":"状态信息描述",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\DcimController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\DcimController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\DcimController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\DcimController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 公共数据

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\CommonController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\CommonController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\CommonController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\CommonController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 产品升降级

### 升降级产品可配置项页面 -- GET /upgrade/index/:hid

- controller: ``app\home\controller\UpgradeController::index``
- desc: 升降级产品可配置项页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| currencyid | number | 非必填 | - | - | 货币ID（可选） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host":[{//原产品信息
      "oid":"可配置项ID",
      "option_name":"可配置项名称",
      "option_type":"类型",
      "qty":"(类型为4时，qty为数量)",
      "suboption_name":"子项名称",
      "subid":"子项ID",
      "fee":"配置子项价格",
      "setupfee":"配置子项初装费",
    }]
    "options":[{//配置项信息,所有配置项
    }]
  }
}
```

### 升降级产品可配置项页面提交(包括使用优惠码) -- POST /upgrade/upgrade_config_post

- controller: ``app\home\controller\UpgradeController::upgradeConfigPost``
- desc: 升降级产品可配置项页面提交(包括使用优惠码) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| currencyid | number | 非必填 | - | - | 货币ID（可选） |
| pormo_code | number | 非必填 | - | - | - |
| configoption[配置项ID] | 字符串 | 必填 | 1 | - | 所选择的子项ID,拉条传数量(当所有配置项都无变化时,不请求接口) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品可配置项页面 -- GET /upgrade/upgrade_config_page

- controller: ``app\home\controller\UpgradeController::getUpgradeConfigPage``
- desc: 升降级产品可配置项页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品可配置项--应用优惠码 -- POST /upgrade/add_promo_code

- controller: ``app\home\controller\UpgradeController::addPromoCodeToConfig``
- desc: 升降级产品可配置项--应用优惠码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| pormo_code | number | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品可配置项--移除优惠码 -- POST /upgrade/remove_promo_code

- controller: ``app\home\controller\UpgradeController::removePromoCodeFromConfig``
- desc: 升降级产品可配置项--移除优惠码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品可配置项--结算 -- POST /upgrade/checkout_config_upgrade

- controller: ``app\home\controller\UpgradeController::checkoutConfigUpgrade``
- desc: 升降级产品可配置项--结算 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品页面 -- GET /upgrade/upgrade_product/:hid

- controller: ``app\home\controller\UpgradeController::upgradeProduct``
- desc: 升降级产品页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| currencyid | number | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":"使用货币信息",
    "old_host":[{//当前产品
      "host":"产品组+产品名",
      "domain":"域名",
      "description":"描述",
      "pid":"产品ID",
    }]
    "host":[{//可升级的产品选项
      "pid":"产品ID",
      "host":"产品组+产品名",
      "description":"描述",
      "cycle":[{//可选周期项
        "price":"产品价格",
        "setup_fee":"初装费",
        "billingcycle":"周期",
        "billingcycle_zh":"中文周期",
      }]
    }]
  }
}
```

### 升降级产品页面提交(包括使用优惠码的情况) -- POST /v10/host/:id/product_upgrade/price

- controller: ``app\home\controller\UpgradeController::v10HostProductUpgradePrice``
- desc: 升降级产品页面提交(包括使用优惠码的情况) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| pid | number | 必填 | - | - | - |
| billingcycle | number | 必填 | - | - | - |
| currencyid | number | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "old_host":[{//原产品信息
      "host":"产品组+产品名",
      "domain":"域名",
    }]
    "des":"描述",
    "amount":"差价",
    "discount":"优惠",
    "amount_total":"支付金额",
    "payment":"支付方式",
    "hid":"原产品ID",
    "pid":"产品ID",
    "billingcycle":"周期",
  }
}
```

### Product upgrade selection -- POST /upgrade/upgrade_product_post

- controller: ``app\home\controller\UpgradeController::upgradeProductPost``
- desc: Product upgrade selection -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品页面 -- GET /upgrade/upgrade_product_page

- controller: ``app\home\controller\UpgradeController::getUpgradeProductPage``
- desc: 升降级产品页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "old_host":[{//原产品信息
      "id":"原产品IDhost:产品名称domain:产品主机",
    }]
    "des":"新产品描述",
    "amount":"小计",
    "discount":"折扣",
    "amount_total":"总计",
    "currency":"货币",
    "promo_code":"优惠码",
    "billingcycle":"周期",
    "billingcycle_zh":"周期中文",
  }
}
```

### 升降级产品--应用优惠码 -- POST /upgrade/add_promo_code_product

- controller: ``app\home\controller\UpgradeController::addPromoToProduct``
- desc: 升降级产品--应用优惠码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| pormo_code | number | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品--移除优惠码 -- POST /upgrade/remove_promo_code_product

- controller: ``app\home\controller\UpgradeController::RemovePromoFromProduct``
- desc: 升降级产品--移除优惠码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 升降级产品结算 -- POST /upgrade/checkout_upgrade_product

- controller: ``app\home\controller\UpgradeController::checkoutProductUpgrade``
- desc: 升降级产品结算 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| payment | number | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "old_host":[{//原产品信息
      "host":"产品组+产品名",
      "domain":"域名",
    }]
    "des":"描述",
    "amount":"差价",
    "discount":"优惠",
    "amount_total":"支付金额",
    "payment":"支付方式",
    "order_id":"订单ID",
    "invoice_id":"账单ID",
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\UpgradeController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\UpgradeController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\UpgradeController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台service module及接口

### 执行模块默认方法 -- POST /provision/default

- controller: ``app\home\controller\ProvisionController::execute``
- desc: 执行模块默认方法 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | int|array | 必填 | - | - | hostid |
| func | 字符串 | 必填 | - | - | 执行的方法 |
| os | 整型 | 必填 | - | - | 重装系统的操作系统id |
| code | 整型 | 必填 | - | - | 验证码 |
| is_api | 整型 | 非必填 | - | - | 1表示是api接口请求,0否(此参数是为了二次验证增加) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取自定义内容 --  GET /provision/custom/content

- controller: ``app\home\controller\ProvisionController::getClientAreaContent``
- desc: 获取自定义内容 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostid |
| key | 字符串 | 必填 | - | - | module_client_area里面的key |
| jwt | 字符串 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "html内容":"",
  }
}
```

### 获取自定义内容 --  POST /zjmf_api/provision/custom/content

- controller: ``app\home\controller\ProvisionController::postClientAreaContent``
- desc: 获取自定义内容 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostid |
| key | 字符串 | 必填 | - | - | module_client_area里面的key |
| api_url | 字符串 | 必填 | - | - | 替换原来模板内的接口地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "html":"html内容",
  }
}
```

### 执行自定义模块方块 --  POST /provision/custom/:id

- controller: ``app\home\controller\ProvisionController::customFunc``
- desc: 执行自定义模块方块 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| func | 字符串 | 必填 | - | - | 执行的方法 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "[type] [description]":"",
  }
}
```

### 获取模块图表数据 --  GET /provision/chart/:id

- controller: ``app\home\controller\ProvisionController::getChartData``
- desc: 获取模块图表数据 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | module_chart里面的type |
| select | 字符串 | 非必填 | - | - | module_chart里面的select的value |
| start | 整型 | 非必填 | - | - | 开始毫秒时间戳 |
| end | 整型 | 非必填 | - | - | 结束毫秒时间戳 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "unit":"单位",
    "chart_type":"line线性图",
    "list":[{//图表数据
      "time":"时间",
    }]
    "label":"对应list鼠标over显示内容",
  }
}
```

### 执行模块自定义按钮方法 -- POST /provision/button

- controller: ``app\home\controller\ProvisionController::execCustomButton``
- desc: 执行模块自定义按钮方法 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务id |
| func | 字符串 | 必填 | - | - | 自定义方法名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\ProvisionController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\ProvisionController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\ProvisionController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\ProvisionController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台用户

### 推荐计划页面 -- GET /affpage

- controller: ``app\home\controller\UserAffiliateController::affpage``
- desc: 推荐计划页面 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推荐计划激活 -- GET /activation

- controller: ``app\home\controller\UserAffiliateController::activation``
- desc: 推荐计划激活 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推荐计划用户激活页面 -- GET /affindex

- controller: ``app\home\controller\UserAffiliateController::affindex``
- desc: 推荐计划用户激活页面 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "visitors":"访问数量",
      "registcount":"注册数量",
      "url":"推荐链接",
      "payamount":"购买数量",
      "balance":"可提现佣金",
      "withdrawn":"已提现佣金",
      "withdrawn":"冻结金额",
      "withdrawn":"已提现佣金",
      "withdrawn":"已提现佣金",
    }]
  }
}
```

### 推荐计划提现 -- POST /withdraw

- controller: ``app\home\controller\UserAffiliateController::withdraw``
- desc: 推荐计划提现 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| num | 浮点型 | 必填 | 0 | - | 提现金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推荐计划提现记录 -- GET /withdrawrecord

- controller: ``app\home\controller\UserAffiliateController::withdrawrecord``
- desc: 推荐计划提现记录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| keywords | 整型 | 非必填 | - | - | 关键字 |
| type | 整型 | 非必填 | - | - | 类型 |
| status | 整型 | 非必填 | - | - | 状态 |
| search_time | 整型 | 非必填 | - | - | 时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "num":"金额",
      "type":"1余额2仅记录3流水支持",
      "user_nickname":"操作人",
      "status":"1待审核2审核通过3拒绝",
      "reason":"拒绝原因",
      "create_time":"时间",
    }]
  }
}
```

### 推荐计划购买记录 -- POST /affbuyrecord

- controller: ``app\home\controller\UserAffiliateController::affbuyrecord``
- desc: 推荐计划购买记录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| keywords | 整型 | 非必填 | - | - | 关键字 |
| status | 整型 | 非必填 | - | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "create_time":"购买时间",
      "subtotal":"订购金额",
      "aff_type":"方式",
      "type":"类型",
      "commission":"佣金",
      "commission_bates":"佣金比例",
      "commission_bates_type":"佣金比例类型1固定2比例",
      "paid_time":"确认时间",
    }]
  }
}
```

### 客户注册列表 -- GET /useraffi_list

- controller: ``app\home\controller\UserAffiliateController::useraffilist``
- desc: 客户注册列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 非必填 | - | - | 用户名 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//用户推介数据
      "id":"用户idcreate_time:创建时间username:用户名companyname:公司名lastlogin:登录时间",
    }]
    "total":[{//总条数
    }]
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\UserAffiliateController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\UserAffiliateController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\UserAffiliateController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\UserAffiliateController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 支持选项资源下载

### 下载支持文件 -- GET download/product_file

- controller: ``app\home\controller\DownController::productFile``
- desc: 下载支持文件 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 下载id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "info":"存在该信息则为用户需要购买相应产品才可下载该文件",
  }
}
```

### 下载应用文件 -- GET download/app_file

- controller: ``app\home\controller\DownController::downloadAppFile``
- desc: 下载应用文件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 应用id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者下载应用文件 -- GET download/developer_file

- controller: ``app\home\controller\DownController::downloadDeveloperFile``
- desc: 开发者下载应用文件 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 应用id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 客户下载应用文件 -- GET download/market_file

- controller: ``app\home\controller\DownController::downloadMarketFile``
- desc: 客户下载应用文件 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 应用id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 返回分类数据 -- GET download/cates

- controller: ``app\home\controller\DownController::cates``
- desc: 返回分类数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cate_id | 整型 | 非必填 | - | - | 不传时为顶级分类，将存在热门下载栏目 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "cate_data":[{//分类数据
      "id":"分类idname:分类名称description:分类描述file_count:该分类下共有多少个可下载文件",
    }]
    "downloads":[{//downloads下载
      "id":"文件id",
      "title":"文件id",
      "description":"文件描述",
      "downloads":"下载数",
      "down_link":"下载链接",
    }]
  }
}
```

### 返回搜索数据 -- POST download/search

- controller: ``app\home\controller\DownController::search``
- desc: 返回搜索数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| search | 字符串 | 必填 | - | - | 搜索关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "downloads":[{//下载数据
      "id":"文件id",
      "title":"文件id",
      "description":"文件描述",
      "downloads":"下载数",
      "down_link":"下载链接",
    }]
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\DownController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\DownController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\DownController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\DownController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 发票管理

### 区域三级联动 -- GET voucher/arealist

- controller: ``app\home\controller\VoucherController::getAreaList``
- desc: 区域三级联动 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "area":"区域",
  }
}
```

### 获取货币 -- GET voucher/currency

- controller: ``app\home\controller\VoucherController::getCurrency``
- desc: 获取货币 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 发票列表 -- GET voucher/voucherlist

- controller: ``app\home\controller\VoucherController::getVoucherList``
- desc: 发票列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总数",
    "voucher":[{//发票信息
      "id":"",
      "create_time":"申请时间",
      "title":"发票抬头",
      "issue_type":"发票性质",
      "issue_type_zh":"发票性质",
      "amount":"发票总额",
      "status":"状态",
      "province":"邮寄地址",
      "city":"邮寄地址",
      "region":"邮寄地址",
      "detail":"详细地址",
      "name":"快递",
      "notes":"备注",
    }]
  }
}
```

### 发票详情 -- GET voucher/voucherdetail

- controller: ``app\home\controller\VoucherController::getVoucherDetail``
- desc: 发票详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 发票ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "voucher":[{//发票信息
      "id":"",
      "create_time":"申请时间",
      "title":"发票抬头",
      "issue_type":"发票性质",
      "issue_type_zh":"发票性质",
      "amount":"发票总额",
      "status":"状态",
      "province":"邮寄地址",
      "city":"邮寄地址",
      "region":"邮寄地址",
      "detail":"详细地址",
      "name":"快递",
      "notes":"备注",
      "price":"邮寄快递价格",
    }]
    "invoices":[{//账单信息
      "description":"产品名称",
      "subtotal":"金额",
      "taxed":"税率",
      "taxed_amount":"税额",
    }]
    "voucher_amount":"开发票扣税",
  }
}
```

### 发票申请列表 -- GET voucher/voucherrequest

- controller: ``app\home\controller\VoucherController::getVoucherRequest``
- desc: 发票申请列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总数",
    "invoices":[{//账单
      "id":"账单ID",
      "subtotal":"金额",
      "type":"类型",
      "type_zh":"类型中文",
      "paid_time":"支付时间",
    }]
  }
}
```

### 开具发票页面 id: title:抬头 issue_type:开具类型 -- GET voucher/issuevoucher

- controller: ``app\home\controller\VoucherController::getIssueVoucher``
- desc: 开具发票页面 id: title:抬头 issue_type:开具类型 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoice_ids | 数组 | 必填 | - | - | 数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "type":"开具类型：person个人,company公司",
    "express":[{//快递信息
      "name":"快递名称",
      "price":"快递价格",
    }]
    "post":[{//邮寄地址
      "id":"地址ID",
      "province":"邮寄地址",
      "city":"邮寄地址",
      "region":"邮寄地址",
      "default":"默认1地址,0否",
    }]
    "title":[{//抬头信息
      "id":"",
      "title":"抬头",
      "issue_type":"开具类型",
    }]
    "invoices":[{//账单信息
      "description":"产品名称",
      "subtotal":"金额",
      "taxed":"税率",
      "taxed_amount":"税额",
    }]
    "voucher_amount":"开发票扣税",
  }
}
```

### 开具发票 -- POST voucher/issuevoucher

- controller: ``app\home\controller\VoucherController::postIssueVoucher``
- desc: 开具发票 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoice_ids[] | 数组 | 必填 | - | - | 数组 |
| type_id | 整型 | 必填 | - | - | 发票类型ID |
| post_id | 整型 | 必填 | - | - | 邮寄地址ID |
| express_id | 整型 | 必填 | - | - | 快递ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 发票信息管理列表 -- GET voucher/voucherinfolist

- controller: ``app\home\controller\VoucherController::getVoucherInfoList``
- desc: 发票信息管理列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "voucher_type":[{//发票信息
      "title":"抬头信息",
      "issue_type":"开具类型person个人，company企业",
      "issue_type_zh":"开具类型person个人，company企业",
      "voucher_type":"发票类型",
      "voucher_type_zh":"发票类型",
      "tax_id":"税务登记号",
    }]
  }
}
```

### 发票信息管理页面 -- GET voucher/voucherinfo

- controller: ``app\home\controller\VoucherController::getVoucherInfo``
- desc: 发票信息管理页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 发票管理信息ID(编辑才传此值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "issue_type":"开具类型",
    "voucher_type":"发票类型",
    "voucher_info":[{//发票信息
      "title":"抬头信息",
      "issue_type":"开具类型person个人，company企业",
      "voucher_type":"发票类型：common普通，dedicated专用",
      "tax_id":"税务登记号",
      "bank":"开户行名称",
      "account":"开户银行账号",
      "address":"公司地址",
      "phone":"联系电话",
    }]
  }
}
```

### 发票信息管理添加、编辑 -- POST voucher/voucherinfo

- controller: ``app\home\controller\VoucherController::postVoucherInfo``
- desc: 发票信息管理添加、编辑 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 发票管理信息ID(编辑才传此值) |
| issue_type | 字符串 | 必填 | - | - | 开具类型person个人,company企业 |
| title | 字符串 | 必填 | - | - | 发票抬头 |
| voucher_type | 字符串 | 必填 | - | - | 发票类型：common普通，dedicated专用 |
| tax_id | 字符串 | 必填 | - | - | 税务登记号 |
| bank | 字符串 | 必填 | - | - | 开户行名称 |
| account | 字符串 | 必填 | - | - | 开户银行账号 |
| address | 字符串 | 必填 | - | - | 公司地址 |
| phone | 字符串 | 必填 | - | - | 联系电话 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 发票信息管理删除 -- DELETE voucher/voucherinfo

- controller: ``app\home\controller\VoucherController::deleteVoucherInfo``
- desc: 发票信息管理删除 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 发票管理信息ID(编辑才传此值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收货地址列表 -- GET voucher/voucherpostlist

- controller: ``app\home\controller\VoucherController::getVoucherPostList``
- desc: 收货地址列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总数",
    "voucher_post":[{//地址信息
      "username":"",
      "phone":"",
      "province":"省",
      "city":"市",
      "region":"区",
      "detail":"详细地址",
      "post":"邮编",
      "default":"1默认",
    }]
  }
}
```

### 收货地址详情 -- GET voucher/voucherpost

- controller: ``app\home\controller\VoucherController::getVoucherPost``
- desc: 收货地址详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 收货地址ID(编辑才传此值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "voucher_post":[{//收货地址详情
      "username":"",
      "phone":"",
      "province":"省",
      "city":"市",
      "region":"区",
      "detail":"详细地址",
      "post":"邮编",
      "default":"1默认",
    }]
  }
}
```

### 收货地址添加、编辑 -- POST voucher/voucherpost

- controller: ``app\home\controller\VoucherController::postVoucherPost``
- desc: 收货地址添加、编辑 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 收货地址ID(编辑才传此值) |
| username | 整型 | 必填 | - | - | 收件人 |
| province | 整型 | 必填 | - | - | 省 |
| city | 整型 | 必填 | - | - | 市 |
| region | 整型 | 必填 | - | - | 区 |
| detail | 整型 | 必填 | - | - | 详细地址 |
| phone | 整型 | 必填 | - | - | - |
| post | 整型 | 必填 | - | - | 邮编 |
| default | 整型 | 必填 | - | - | 是否默认:1默认,0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收货地址删除 -- DELETE voucher/voucherpost

- controller: ``app\home\controller\VoucherController::deleteVoucherPost``
- desc: 收货地址删除 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 收货地址ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 设置默认收货地址 -- POST voucher/voucherdefaultpost

- controller: ``app\home\controller\VoucherController::postVoucherDefaultPost``
- desc: 设置默认收货地址 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 收货地址ID |
| default | 整型 | 必填 | - | - | 1默认，0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\VoucherController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\VoucherController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\VoucherController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\VoucherController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台 用户信用额管理

### 用户信用额列表 -- GET /credit_limit

- controller: ``app\home\controller\CreditLimitController::index``
- desc: 用户信用额列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
  }
}
```

### 用户信用额使用记录 -- GET /credit_limit/list

- controller: ``app\home\controller\CreditLimitController::list``
- desc: 用户信用额使用记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 用户账单列表 -- GET /credit_limit/user_invoice

- controller: ``app\home\controller\CreditLimitController::userInvoice``
- desc: 用户账单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 信用额账单列表 -- GET /credit_limit/user_invoice_detail

- controller: ``app\home\controller\CreditLimitController::creditLimitInvoice``
- desc: 信用额账单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoice_id | 整型 | 非必填 | - | - | 账单ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 已用额度明细 -- GET /credit_limit/used_detail

- controller: ``app\home\controller\CreditLimitController::creditLimitUsed``
- desc: 已用额度明细 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 提前还款 -- POST /credit_limit/prepayment

- controller: ``app\home\controller\CreditLimitController::creditLimitPrepayment``
- desc: 提前还款 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台首页 菜单

### 后台 首页菜单 -- GET /ad_index

- controller: ``app\admin\controller\IndexController::ad_index``
- desc: 后台 首页菜单 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "menus":[{//  菜单列表
      "admin":[{//客户菜单
        "id":"菜单id",
        "name":"菜单名",
        "url":"菜单url",
        "parent":"父级菜单",
        "lang":"多语言key",
      }]
      "110user":"管理员菜单同上",
      "1admin":"插件菜单同上",
    }]
    ".content":"  管理员日志",
  }
}
```

### 搜索 --  GET searchlist

- controller: ``app\admin\controller\IndexController::search``
- desc: 搜索 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| value | number | 非必填 | 1 | - | 全局搜索字段 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array list - 搜索返回列表":"",
  }
}
```

### 更改配置文件 --  GET editlog

- controller: ``app\admin\controller\IndexController::editLog``
- desc: 更改配置文件 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 非必填 | 1 | - | 0关闭1开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 条件搜索页面 --  GET tablelist

- controller: ``app\admin\controller\IndexController::tableList``
- desc: 条件搜索页面 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array list - 搜索返回列表":"",
  }
}
```

### 条件搜索 --  POST searchfornamelist

- controller: ``app\admin\controller\IndexController::searchfornameList``
- desc: 条件搜索 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| table | 字符串 | 非必填 | 1 | - | 表名 |
| key | 字符串 | 非必填 | 1 | - | 全局搜索字段 |
| value | 字符串 | 非必填 | 1 | - | 全局搜索字段 |
| type | 字符串 | 非必填 | 1 | - | 精确还是多选 |
| customfields[自定义字段id(即返回的ab_name的值)] | 数组 | 非必填 | 1 | - | 值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array list - 搜索返回列表":"",
  }
}
```

### 条件搜索页面 --  GET namelist

- controller: ``app\admin\controller\IndexController::nameList``
- desc: 条件搜索页面 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| value | 字符串 | 非必填 | 1 | - | 搜索字段 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array list - 搜索返回列表":"",
  }
}
```


---

## 后台登录

### 异步批量发送 -- POST /admin/async_curl_multi

- controller: ``app\admin\controller\PublicController::asyncCurlMulti``
- desc: 异步批量发送 -- xiong

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 短信推送 -- POST /admin/async_sms_message

- controller: ``app\admin\controller\PublicController::asyncSmsMessage``
- desc: 短信推送 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 邮件推送 -- POST /admin/async_email_message

- controller: ``app\admin\controller\PublicController::asyncEmailMessage``
- desc: 邮件推送 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 异步发送站内信 -- POST /admin/async_system_message

- controller: ``app\admin\controller\PublicController::asyncSystemMessage``
- desc: 异步发送站内信 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 异步开通,TODO 安全校验 -- POST /admin/async_create

- controller: ``app\admin\controller\PublicController::asyncCreateAccount``
- desc: 异步开通,TODO 安全校验 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 异步发送短信 -- POST /admin/async_sms

- controller: ``app\admin\controller\PublicController::asyncSms``
- desc: 异步发送短信 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 异步发送邮件 -- POST /admin/async

- controller: ``app\admin\controller\PublicController::asyncEmail``
- desc: 异步发送邮件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 必填 | 1 | - | 发送邮件类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 获取验证码 -- GET /admin/get_verify_code

- controller: ``app\admin\controller\PublicController::getVerifyCode``
- desc: 获取验证码 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "captcha":" 验证码",
  }
}
```

### 后台登录页面 -- GET /admin/login_page

- controller: ``app\admin\controller\PublicController::adPage``
- desc: 后台登录页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "second_verify_admin：是否开启 后台验证":"",
    "second_verify_action_admin":"是否有登录验证",
  }
}
```

### 二次验证发送验证码 -- POST /admin/second_verify_send

- controller: ``app\admin\controller\PublicController::secondVerifySend``
- desc: 二次验证发送验证码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| action | 字符串 | 必填 | 0 | 发送动作(login登录) | - |
| username | 字符串 | 非必填 | 0 | 管理员用户名(以下两个参数仅action==login时传递) | - |
| password | 字符串 | 非必填 | 0 | 密码 | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 登录 -- POST /admin/login

- controller: ``app\admin\controller\PublicController::ad_login``
- desc: 登录 -- 上官磨刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | str | 必填 | 1 | - | 用户名 |
| password | str | 必填 | 1 | - | 密码 |
| code | str | 必填 | 1 | - | 验证码(手机) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rule":[{// 权限列表
      "list":[{//子权限列表
        "name":"权限名称",
        "title":"权限标题",
      }]
      "name":"子权限名称",
      "title":"子权限标题",
    }]
  }
}
```

### 后台管理员退出 -- GET /admin/logout

- controller: ``app\admin\controller\PublicController::ad_logout``
- desc: 后台管理员退出 -- 上官磨刀

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 后台用户列表 -- GET /admin/getClient

- controller: ``app\admin\controller\PublicController::getClient``
- desc: 后台用户列表 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 后台工单部门列表 -- GET /admin/getTicketDepartment

- controller: ``app\admin\controller\PublicController::getTicketDepartment``
- desc: 后台工单部门列表 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 验证码图形 -- GET verify

- controller: ``app\admin\controller\PublicController::verify``
- desc: 验证码图形 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 生成资源池jwt -- GET /admin/agent/checktoken

- controller: ``app\admin\controller\PublicController::checkToken``
- desc: 生成资源池jwt -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "jwt":"",
  }
}
```


---

## 后台设置

### 管理员密码修改 -- POST /admin/password_reset

- controller: ``app\admin\controller\SetController::passwordPost``
- desc: 管理员密码修改 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| old_password | 字符串 | 必填 | 1 | - | 原始密码 |
| password | 字符串 | 必填 | 1 | - | 新密码 |
| re_password | 字符串 | 必填 | 1 | - | 重复新密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 清除缓存 -- GET /admin/clear_cache

- controller: ``app\admin\controller\SetController::clearCache``
- desc: 清除缓存 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户自定义字段配置页面 -- GET /admin/custom_fields

- controller: ``app\admin\controller\SetController::getCustomFields``
- desc: 用户自定义字段配置页面 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "customfields":[{//自定义字段数据
      "id":"自定义字段id",
      "fieldname":"自定义字段标题",
      "fieldtype":"自定义字段类型（text，link，password，dropdown，tickbox，textarea）",
      "description":"自定义字段描述",
      "fieldoptions":"自定义字段选项，为dropdown时使用",
      "regexpr":"验证数据",
      "adminonly":"是否管理员可见",
      "required":"是否必填",
      "showorder":"是否在订单上显示",
      "showinvoice":"是否在账单上显示",
      "sortorder":"排序字段",
      "showdetail":"是否在产品内页显示",
    }]
    "type_list":[{//类型列表
    }]
  }
}
```

### 保存用户自定义字段 -- POST /admin/custom_fields

- controller: ``app\admin\controller\SetController::postCustomFields``
- desc: 保存用户自定义字段 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| addfieldname | 字符串 | 非必填 | - | - | 添加的字段名称 |
| addfieldtype | 字符串 | 非必填 | dropdown | - | 添加的字段类型 |
| addcustomfielddesc | 字符串 | 非必填 | - | - | 添加的字段描述 |
| addfieldoptions | 字符串 | 非必填 | - | - | 添加字段的选项 |
| addregexpr | 字符串 | 非必填 | - | - | 该字段的正则匹配 |
| addadminonly | 字符串 | 非必填 | - | - | 选中为仅管理员可见 |
| addrequired | 字符串 | 非必填 | - | - | 该字段必填，值为on时 |
| addshoworder | 字符串 | 非必填 | - | - | 在订单上显示，值为on时 |
| addshowinvoice | 字符串 | 非必填 | - | - | 在账单上显示，值为on时 |
| addsortorder | 整型 | 非必填 | - | - | 排序数值 |
| customfieldname | 数组 | 非必填 | - | - | 修改的字段名称 |
| customfieldtype | 数组 | 非必填 | dropdown | - | 修改的字段类型 |
| customfielddesc | 数组 | 非必填 | - | - | 修改的字段描述 |
| customfieldoptions | 数组 | 非必填 | - | - | 修改的字段的选项 |
| customfieldregexpr | 数组 | 非必填 | - | - | 修改的字段的正则匹配 |
| customadminonly | 数组 | 非必填 | - | - | 修改选中为仅管理员可见 |
| customrequired | 数组 | 非必填 | - | - | 修改该字段必填，值为on时 |
| customshoworder | 数组 | 非必填 | - | - | 修改在订单上显示，值为on时 |
| customshowinvoice | 数组 | 非必填 | - | - | 修改在账单上显示，值为on时 |
| customsortorder | 数组 | 非必填 | - | - | 修改排序数值 |
| configoptionlinks | 数组 | 非必填 | - | - | 关联的可配置选项，一维数组，值为int型 |
| upgradepackages | 数组 | 非必填 | - | - | 可升级更改产品的数组，一维数组，值为int型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除用户自定义字段配置 -- POST /admin/del_custom_fields

- controller: ``app\admin\controller\SetController::delCustomFields``
- desc: 删除用户自定义字段配置 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 删除的自定义字段id |
| type | 字符串 | 必填 | - | - | 删除的自定义字段类型(client:用户，product：产品，ticket:工单，) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取备份配置 -- GET /admin/database_backup

- controller: ``app\admin\controller\SetController::databaseBackups``
- desc: 获取备份配置 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "daily_email_backup_status":"是否启用邮件备份",
    "daily_email_backup":"邮件备份地址",
    "daily_ftp_backup_status":"是否启用FTP远程备份",
    "ftp_backup_hostname":"FTP主机",
    "ftp_backup_username":"用户名",
    "ftp_backup_password":"密码",
    "ftp_backup_destination":"远程FTP备份路径",
    "ftp_secure_mode":"SFTP模式",
    "ftp_passive_mode":"FTP被动模式",
  }
}
```

### 测试/保存ftp连接 -- POST /admin/backup_ftp

- controller: ``app\admin\controller\SetController::backupDatabaseFtp``
- desc: 测试/保存ftp连接 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ftp_backup_hostname | 字符串 | 必填 | - | - | FTP主机 |
| ftp_backup_port | number | 必填 | - | - | 端口号 |
| ftp_backup_username | 字符串 | 必填 | - | - | 用户名 |
| ftp_backup_password | 字符串 | 必填 | - | - | 密码 |
| ftp_backup_destination | 字符串 | 必填 | - | - | 远程FTP备份路径 |
| ftp_secure_mode | 整型 | 非必填 | - | - | SFTP模式1,0（与ftp_passive_mode |
| ftp_passive_mode | 整型 | 非必填 | - | - | FTP被动模式 |
| type | 字符串 | 必填 | - | - | 类型(test:测试链接，save:保存) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 停用FTP备份 -- POST /admin/deactivete_ftp

- controller: ``app\admin\controller\SetController::deactivateFtp``
- desc: 停用FTP备份 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 保存并启用邮箱备份 -- POST /admin/backup_email

- controller: ``app\admin\controller\SetController::backupEmail``
- desc: 保存并启用邮箱备份 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| daily_email_backup | 整型 | 非必填 | - | - | 邮箱地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 停用邮箱备份 -- POST /admin/deactivete_email

- controller: ``app\admin\controller\SetController::deactivateEmail``
- desc: 停用邮箱备份 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台用户管理

### 客户列表 -- POST /admin/client_list

- controller: ``app\admin\controller\UserManageController::clientList``
- desc: 客户列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| username | 字符串 | 非必填 | 1 | - | 按客户名搜索 |
| companyname | 字符串 | 非必填 | 1 | - | 按公司名搜索 |
| email | 字符串 | 非必填 | 1 | - | 按邮件搜索 |
| phonenumber | 字符串 | 非必填 | 1 | - | 按手机号搜索 |
| status | 字符串 | 非必填 | 1 | - | 按客户状态搜索 |
| qq | 字符串 | 非必填 | 1 | - | 按qq搜索 |
| custom[自定义字段ID] | 数组 | 非必填 | 1 | - | 值 |
| level | 整型 | 非必填 | 1 | - | 等级ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"客户总数",
    "list":[{//客户列表数据
      "id":"客户ID",
      "username":"客户用户名",
      "company":"公司",
      "phonenumber":"手机号",
      "email":"邮件",
      "sale":"所属销售",
      "amount_in":"收入",
      "amount_out":"支出",
      "credit":"余额",
      "create_time":"创建时间",
      "company_status":"企业认证状态（空：未认证，1已认证，2未通过，3待审核）",
      "person_status":"个人认证状态（空：未认证，1已认证，2未通过，3待审核）",
      "wechat_id":"微信绑定（空：未绑定；有值：绑定）",
      "lastlogin":"最后登录时间",
      "client_status":"客户状态(1激活，0未激活，2关闭)",
      "level":"客户等级",
      "track_status":"跟踪状态",
      "track_status_zh":"跟踪状中文",
      "group_name":"客户分类",
      "group_colour":"客户分类颜色",
      "credit_limit":"信用额",
      "api_open":"api状态,1开启，0关闭，2锁定",
      "api_open_zh":"对应中文",
      "free_products":"豁免产品数量",
    }]
    "search":"搜索字段+自定义字段搜索 键为ID的是自定义字段",
    "search_level":[{//客户等级搜索
      "id":"等级ID",
      "level_name":"名称",
    }]
    "api_status":"第一个搜索框选择API状态时，对应的第二个搜索框数据",
    "allow_resource_api":"当该值为0时,不显示api状态",
  }
}
```

### 单个客户资料详情 -- GET /admin/summary

- controller: ``app\admin\controller\UserManageController::summary``
- desc: 单个客户资料详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "summary":[{//客户资料
      "username":"用户名",
      "companyname":"公司",
      "country":"国家",
      "province":"省",
      "city":"城市",
      "address1":"地址1",
      "know_us":"从何了解我们",
      "phonenumber":"手机号",
      "email":"邮箱",
      "sale_id":"销售id",
      "initiative_renew":"余额是否自动续费(1是0否)",
      "certifi_status":"认证情况",
    }]
    "invoices":[{//财务/账单
      "paid":[{//已支付
        "status_zh":"中文名",
        "num":"数量",
        "total":"总价",
      }]
    }]
    "intotal":"总收入",
    "outtotal":"总支出",
    "income":"收入",
    "credit":"余额",
    "other_info":[{//其他信息
      "status":"用户状态",
      "groupname":"用户组",
      "create_time":"创建时间",
      "register_time":"注册时长",
      "last_login":"最后登录时间",
      "last_login_ip":"最后登录IP",
      "host":"最后登录主机",
    }]
    "hsot_server":[{//产品/服务
      "server":[{//VPS/独服
        "total":"总量",
        "active":"激活数",
        "type_zh":"中文名",
      }]
    }]
    "hosts":[{//产品/服务列表
      "hid":"产品/服务ID",
      "hostname":"产品/服务",
      "amount":"金额",
      "billingcycle":"付款周期",
      "regdate":"开通时间",
      "nextduedate":"到期时间",
      "domainstatus":"状态('Pending':待审核,'Active'已激活,'Suspended'已暂停,'Terminated'已终止,'Cancelled'已取消,'Fraud'欺诈,'Completed'已完成)",
    }]
    "hid":"hostid",
    "accounts_count":"总收入 笔数",
    "accounts_out_count":"总支出 笔数",
    "being_due_host":"即将过期 count笔数，total总计",
    "due_host":"已过期 count笔数，total总计",
    "ticket":"工单，total总数，reply已回复，deal处理中，close已关闭",
  }
}
```

### 客户资源列表 -- GET /admin/client_list_resource

- controller: ``app\admin\controller\UserManageController::clientListRe``
- desc: 客户资源列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| username | 字符串 | 非必填 | 1 | - | 按客户名搜索 |
| companyname | 字符串 | 非必填 | 1 | - | 按公司名搜索 |
| email | 字符串 | 非必填 | 1 | - | 按邮件搜索 |
| phonenumber | 字符串 | 非必填 | 1 | - | 按手机号搜索 |
| status | 字符串 | 非必填 | 1 | - | 按客户状态搜索 |
| qq | 字符串 | 非必填 | 1 | - | 按qq搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"客户总数",
    "list":[{//客户列表数据
      "id":"客户ID",
      "username":"客户用户名",
      "amount_in":"收入",
      "credit":"余额",
      "create_time":"创建时间",
    }]
  }
}
```

### 绑定销售 -- POST /admin/bind_sale

- controller: ``app\admin\controller\UserManageController::hostBindSale``
- desc: 绑定销售 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |
| sale_id | 整型 | 必填 | 1 | - | 销售ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 单个客户产品 -- GET /admin/hostbyuid

- controller: ``app\admin\controller\UserManageController::hostByUid``
- desc: 单个客户产品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |
| currency | 整型 | 必填 | 1 | - | 货币id |
| search_all | 整型 | 必填 | 1 | - | 1是否查询全部 |
| source | 字符串 | 必填 | 1 | - | 工单ticket |
| hostid | 整型 | 必填 | 1 | - | hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":[{//客户资料
    }]
    "hosts":[{//产品/服务列表
      "hid":"产品/服务ID",
      "hostname":"产品/服务",
      "amount":"金额",
      "billingcycle":"付款周期",
      "regdate":"开通时间",
      "nextduedate":"到期时间",
      "domainstatus":"状态('Pending':待审核,'Active'已激活,'Suspended'已暂停,'Terminated'已终止,'Cancelled'已取消,'Fraud'欺诈,'Completed'已完成)",
    }]
    "hid":"hostid",
  }
}
```

### 客户资料修改页 -- GET /admin/profile/:client_id

- controller: ``app\admin\controller\UserManageController::profile``
- desc: 客户资料修改页 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "profile":[{//客户基本信息
      "username":"用户名",
      "sex":"性别",
      "avatar":"头像",
      "profession":"职业",
      "signature":"个性签名",
      "companyname":"所在公司",
      "email":"邮件",
      "country":"国家",
      "province":"省份",
      "city":"城市",
      "region":"区",
      "address1":"具体地址1",
      "postcode":"邮编",
      "phone_code":"电话区号",
      "phonenumber":"电话",
      "currency":"使用货币ID",
      "defaultgateway":"选择默认支付接口",
      "notes":"管理员备注",
      "groupid":"用户组ID",
      "status":"状态（1激活，0未激活，2关闭）",
      "language":"语言",
      "sale_id":"销售id",
      "know_us":"了解途径",
    }]
    "currencies":"货币种类(所有默认值都在profile中找)",
    "country":"国家列表",
    "areas":"区域列表",
    "sms_country":"国际电话区号",
    "gateway":"支付方式",
    "language":"语言列表",
    "sale":"销售列表",
    "client_groups":"用户组列表",
    "client_status":"用户状态列表",
    "customs":[{//用户自定义字段
      "id":"自定义字段ID",
      "fieldname":"字段名称",
      "fieldtype":"类型:text",
      "description":"描述",
      "fieldoptions":"选项",
      "regexpr":"正则匹配",
      "required":"1必填，0非必填",
      "sortorder":"排序",
    }]
    "custom_value":[{//用户自定义字段的值
      "id":"自定义字段ID",
      "value":"值",
    }]
  }
}
```

### 获取推介人 -- GET /admin/profile/getclients/:client_id

- controller: ``app\admin\controller\UserManageController::getClients``
- desc: 获取推介人 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 整型 | 非必填 | - | - | 用户名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 客户资料修改页提交 -- POST /admin/profile_post

- controller: ``app\admin\controller\UserManageController::profilePost``
- desc: 客户资料修改页提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |
| username | 字符串 | 必填 | 1 | - | 用户名 |
| sex | 整型 | 必填 | 1 | - | 性别（0未知，1男，2女） |
| avatar | 字符串 | 非必填 | 1 | - | 头像 |
| profession | 字符串 | 非必填 | 1 | - | 职业 |
| signature | 字符串 | 非必填 | 1 | - | 个性签名 |
| companyname | 字符串 | 非必填 | 1 | - | 所在公司 |
| email | 字符串 | 非必填 | 0 | - | 邮件 |
| qq | 字符串 | 非必填 | 0 | - | qq |
| sale_id | 字符串 | 非必填 | 0 | - | 销售id |
| country | 字符串 | 非必填 | 0 | - | 国家 |
| province | 字符串 | 非必填 | 0 | - | 省份 |
| city | 字符串 | 非必填 | 0 | - | 城市 |
| region | 字符串 | 非必填 | 0 | - | 区 |
| address1 | 字符串 | 非必填 | 1 | - | 具体地址1 |
| postcode | 字符串 | 非必填 | 1 | - | 邮编 |
| phone_code | 整型 | 非必填 | 1 | - | 电话区号 |
| phonenumber | 字符串 | 非必填 | 1 | - | 电话 |
| currency | 整型 | 非必填 | 1 | - | 使用货币ID |
| defaultgateway | 字符串 | 必填 | 1 | - | 选择默认支付接口 |
| notes | 字符串 | 非必填 | 0 | - | 管理员备注 |
| groupid | 整型 | 非必填 | 0 | - | 用户组ID |
| status | 整型 | 非必填 | 0 | - | 状态（0未激活，1激活，2关闭） |
| language | 字符串 | 非必填 | 0 | - | 语言(传zh_cn/zh_xg/en_us等) |
| know_us | 字符串 | 非必填 | 0 | - | 了解途径 |
| custom[id] | 字符串 | 必填 | 0 | - | 自定义字段值.形式：custom[id] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建客户页面 -- GET /admin/create_client

- controller: ``app\admin\controller\UserManageController::createClient``
- desc: 创建客户页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currencies":"货币种类(所有默认值都在profile中找)",
    "country":"国家列表",
    "areas":"区域列表",
    "sms_country":"国际电话区号",
    "gateway":"支付方式",
    "language":"语言列表",
    "client_groups":"用户组列表",
    "client_status":"用户状态列表（0未激活，1激活，2关闭）",
    "customs":[{//用户自定义字段
      "id":"自定义字段ID",
      "fieldname":"字段名称",
      "fieldtype":"类型:text",
      "description":"描述",
      "fieldoptions":"选项",
      "regexpr":"正则匹配",
      "required":"1必填，0非必填",
      "sortorder":"排序",
    }]
  }
}
```

### 创建客户页面提交 -- POST /admin/create_client_post

- controller: ``app\admin\controller\UserManageController::createClientPost``
- desc: 创建客户页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 必填 | 1 | - | 用户名 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| sex | 整型 | 必填 | 1 | - | 性别（0未知，1男，2女） |
| avatar | 字符串 | 非必填 | 1 | - | 头像 |
| profession | 字符串 | 非必填 | 1 | - | 职业 |
| signature | 字符串 | 非必填 | 1 | - | 个性签名 |
| companyname | 字符串 | 非必填 | 1 | - | 所在公司 |
| email | 字符串 | 非必填 | 0 | - | 邮件 |
| country | 字符串 | 非必填 | 0 | - | 国家 |
| province | 字符串 | 非必填 | 0 | - | 省份 |
| city | 字符串 | 非必填 | 0 | - | 城市 |
| region | 字符串 | 非必填 | 0 | - | 区 |
| address1 | 字符串 | 非必填 | 1 | - | 具体地址1 |
| postcode | 字符串 | 非必填 | 1 | - | 邮编 |
| phone_code | 整型 | 非必填 | 1 | - | 电话区号 |
| phonenumber | 字符串 | 非必填 | 1 | - | 电话 |
| currency | 整型 | 非必填 | 1 | - | 使用货币ID |
| defaultgateway | 字符串 | 必填 | 1 | - | 选择默认支付接口 |
| notes | 字符串 | 非必填 | 0 | - | 管理员备注 |
| groupid | 整型 | 非必填 | 0 | - | 用户组ID |
| status | 整型 | 非必填 | 0 | - | 状态（0未激活，1激活，2关闭） |
| language | 字符串 | 非必填 | 0 | - | 语言(传zh_cn/zh_xg/en_us等) |
| know_us | 字符串 | 非必填 | 0 | - | 了解途径 |
| custom[id] | 字符串 | 非必填 | 0 | - | 自定义字段值.形式：custom[id] |
| is_sale | 字符串 | 非必填 | 0 | - | 是否是销售; |
| sale_is_use | 字符串 | 非必填 | 0 | - | 销售是否启用; |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关闭(或者开启)客户 -- GET /admin/close_client/:uid

- controller: ``app\admin\controller\UserManageController::closeClient``
- desc: 关闭(或者开启)客户 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 客户ID |
| type | 字符串 | 必填 | 1 | - | 类型：close关闭客户，open开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除客户 -- GET /admin/delete_client/:uid

- controller: ``app\admin\controller\UserManageController::deleteClient``
- desc: 删除客户 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 0 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 日志记录 -- GET log_record

- controller: ``app\admin\controller\UserManageController::logRecord``
- desc: 日志记录 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当天日志 |
| search_desc | 字符串 | 非必填 | - | - | 通过描述查询 |
| search_ip | 字符串 | 非必填 | - | - | ip地址查询 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| type | 字符串 | 非必填 | asc | - | 可选参数(值：host),服务器里的日志 |
| keywords | 字符串 | 非必填 | asc | - | keywords关键字搜索 |
| uid | 整型 | 非必填 | 1 | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "description":"描述",
      "user":"用户",
      "ipaddr":"ip地址",
    }]
    "count":"数量",
  }
}
```

### 认证列表 -- GET /admin/cerify_list

- controller: ``app\admin\controller\UserManageController::cerify_list``
- desc: 认证列表 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| order | 字符串 | 非必填 | id | - | 排序字段 |
| sort | 字符串 | 非必填 | DESC | - | 排序规则 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 10 | - | 每页数据量 |
| type | 整型 | 必填 | 1 | - | 认证类型1=公司2=个人 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".auth_user_id":"用户id",
    ".auth_rela_name":"真实姓名",
    ".auth_card_type":"认证方式1=大陆 0 =非大陆",
    ".auth_card_number":"认证卡号",
    ".company_name":"公司名称",
    ".company_organ_code":"公司代码",
    ".img_one":"正面照片",
    ".img_two":"反面照片",
    ".img_three":"公司执照",
    ".status":"认证状态1已认证，2未通过，3待审核，4已提交资料",
    ".cerify_id":"阿里认证id",
    ".auth_fail":"失败原因",
    ".create_time":"创建时间",
    ".update_time":"修改时间",
  }
}
```

### 实名认证日志列表 -- GET /admin/cerify_log_list

- controller: ``app\admin\controller\UserManageController::cerifyLogList22``
- desc: 实名认证日志列表 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| order | 字符串 | 非必填 | id | - | 排序字段 |
| sort | 字符串 | 非必填 | DESC | - | 排序规则 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 10 | - | 每页数据量 |
| type | 整型 | 非必填 | 1 | - | 认证类型1=公司2=个人3=个人转公司 |
| status | 整型 | 非必填 | 1 | - | 1已认证，2未通过，3待审核，4已提交资料 |
| uid | 整型 | 非必填 | 1 | - | 用户id |
| keywords | 字符串 | 非必填 | 1 | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".auth_user_id":"用户id",
    ".auth_rela_name":"真实姓名",
    ".auth_card_type":"认证方式1=大陆 0 =非大陆",
    ".auth_card_number":"认证卡号",
    ".company_name":"公司名称",
    ".company_organ_code":"公司代码",
    ".pic":"照片集合",
    ".status":"认证状态1已认证，2未通过，3待审核，4已提交资料",
    ".error":"失败原因",
    ".create_time":"创建时间",
    ".type":"认证类型1=个人2=企业3=个人转企业",
    ".is_newest":"是否最新false/true",
  }
}
```

### 实名认证日志列表 -- GET /admin/cerify_log_list

- controller: ``app\admin\controller\UserManageController::cerifyLogList``
- desc: 实名认证日志列表 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| order | 字符串 | 非必填 | id | - | 排序字段 |
| sort | 字符串 | 非必填 | DESC | - | 排序规则 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 10 | - | 每页数据量 |
| type | 整型 | 非必填 | 1 | - | 认证类型1=公司2=个人3=个人转公司 |
| status | 整型 | 非必填 | 1 | - | 1已认证，2未通过，3待审核，4已提交资料 |
| uid | 整型 | 非必填 | 1 | - | 用户id |
| keywords | 字符串 | 非必填 | 1 | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".auth_user_id":"用户id",
    ".auth_rela_name":"真实姓名",
    ".auth_card_type":"认证方式1=大陆 0 =非大陆",
    ".auth_card_number":"认证卡号",
    ".company_name":"公司名称",
    ".company_organ_code":"公司代码",
    ".pic":"照片集合",
    ".status":"认证状态1已认证，2未通过，3待审核，4已提交资料",
    ".error":"失败原因",
    ".create_time":"创建时间",
    ".type":"认证类型1=个人2=企业3=个人转企业",
    ".is_newest":"是否最新false/true",
  }
}
```

### 实名认证日志历史记录列表 -- GET /admin/cerify_history_log

- controller: ``app\admin\controller\UserManageController::getCerifyHistoryLog``
- desc: 实名认证日志历史记录列表 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 记录id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".auth_user_id":"用户id",
    ".auth_rela_name":"真实姓名",
    ".auth_card_type":"认证方式1=大陆 0 =非大陆",
    ".auth_card_number":"认证卡号",
    ".company_name":"公司名称",
    ".company_organ_code":"公司代码",
    ".pic":"照片集合",
    ".status":"认证状态1已认证，2未通过，3待审核，4已提交资料",
    ".error":"失败原因",
    ".create_time":"创建时间",
    ".type":"认证类型1=个人2=企业3=个人转企业",
    ".is_newest":"是否最新false/true",
  }
}
```

### 客户个人实名认证详情 -- GET /admin/certifi_person_detail/:client_id

- controller: ``app\admin\controller\UserManageController::certifiPersonDetail``
- desc: 客户个人实名认证详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".uid":"用户id",
    ".name":"用户名",
    ".card_type":"身份证类型：1大陆，0非大陆",
    ".idcard":"身份证号码",
    ".status":"认证状态(1已认证，2未通过，3待审核，4提交资料）",
    ".img_one":"身份证正面地址",
    ".img_two":"身份证反面地址",
    ".img_three":"执照照片",
    ".auth_fail":"认证失败原因",
    ".certify_id":"认证ID",
    ".create_time":"认证时间",
    ".company_name":"企业名称",
    ".company_organ_code":"企业代码",
    ".update_time":"",
    ".type":"认证类型1=个人2=企业",
  }
}
```

### 客户个人实名认证修改 -- POST /admin/certifi_person_modify

- controller: ``app\admin\controller\UserManageController::certifiPersonModify``
- desc: 客户个人实名认证修改 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |
| real_name | 整型 | 必填 | 1 | - | 真实姓名 |
| card_type | 整型 | 必填 | 1 | - | 卡类型：0非大陆，1大陆(默认) |
| idcard | 整型 | 必填 | 1 | - | 身份证号 |
| idimage[] | 整型 | 必填 | 1 | - | 多文件上传:身份证正面照片、身份证反面照片 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 客户企业实名认证详情 -- GET /admin/certifi_company_detail/:client_id

- controller: ``app\admin\controller\UserManageController::certifiCompanyDetail``
- desc: 客户企业实名认证详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".uid":"用户id",
    ".name":"用户名",
    ".card_type":"身份证类型：1大陆，0非大陆",
    ".idcard":"身份证号码",
    ".company_name":"企业名称",
    ".company_organ_code":"营业执照号码",
    ".status":"认证状态",
    ".img_one":"身份证正面地址",
    ".img_two":"身份证反面地址",
    ".img_three":"公司营业执照地址",
    ".auth_fail":"认证失败原因",
    ".certify_id":"认证ID",
    ".create_time":"",
    ".update_time":"",
  }
}
```

### 客户企业实名认证修改 -- POST /admin/certifi_company_modify

- controller: ``app\admin\controller\UserManageController::certifiCompanyModify``
- desc: 客户企业实名认证修改 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| company_name | 字符串 | 必填 | 1 | - | 企业名称 |
| company_organ_code | 字符串 | 必填 | 1 | - | 营业执照号码 |
| real_name | 字符串 | 必填 | 1 | - | 提交人姓名 |
| card_type | tinyint | 必填 | 1 | - | card类型：1内地身份证(默认)；0港澳台身份证 |
| idcard | 字符串 | 必填 | 1 | - | 身份证号 |
| idimage[] | image | 必填 | 1 | - | 身份证正面、反面、公司营业执照（多文件上传） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 客户个人认证图片下载 -- GET /admin/certifi_person_download

- controller: ``app\admin\controller\UserManageController::certifiPersonDownload``
- desc: 客户个人认证图片下载 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 整型 | 必填 | 1 | - | 客户ID |
| type | 字符串 | 非必填 | 1 | - | 图片类型：type=idfront(身份证正面照)或者type=idback(身份证反面) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 客户认证图片下载 -- GET /admin/certifi_download

- controller: ``app\admin\controller\UserManageController::certifiDownload``
- desc: 客户认证图片下载 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 记录id |
| type | 整型 | 必填 | 1 | - | 图片类型0=正面1=反面2=执照 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改认证状态 -- POST /admin/certifi_status

- controller: ``app\admin\controller\UserManageController::certifiStatus``
- desc: 修改认证状态 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户id |
| type | 整型 | 必填 | 1 | - | 认证类型1=个人2=企业 |
| status | 整型 | 必填 | 1 | - | 状态1已认证，2未通过，3待审核，4已提交资料 |
| error | 整型 | 非必填 | 1 | - | 驳回原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户账单列表 -- GET /admin/user_invoice

- controller: ``app\admin\controller\UserManageController::userInvoice``
- desc: 用户账单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| hostid | 整型 | 非必填 | - | - | 产品id |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/sort |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 交易流水列表 -- GET /admin/user_productaccounts

- controller: ``app\admin\controller\UserManageController::userProductaccounts``
- desc: 交易流水列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 可选参数,用户ID |
| hid | 整型 | 非必填 | 1 | - | 可选参数,产品ID |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段,username,create_time,gateway,description,amount_in,fees,amount_out |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| show | 字符串 | 非必填 | - | - | 显示类型(amount_in/amount_out) |
| description | 字符串 | 非必填 | - | - | 描述 |
| trans_id | 整型 | 非必填 | - | - | 付款流水号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| gateway | 整型 | 非必填 | - | - | 支付方式 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 用户账单列表 -- GET /admin/user_productinvoice

- controller: ``app\admin\controller\UserManageController::userProductInvoice``
- desc: 用户账单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| hid | 整型 | 非必填 | - | - | 产品ID |
| hostid | 整型 | 非必填 | - | - | 产品id |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/sort |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

###  添加账单 -- POST admin/add_user_invoice

- controller: ``app\admin\controller\UserManageController::addUserInvoice``
- desc: 添加账单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  以该客户登录 -- GET admin/login_by_user/:uid

- controller: ``app\admin\controller\UserManageController::loginByUser``
- desc: 以该客户登录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单ID",
    }]
  }
}
```

###  创建充值账单 -- POST admin/add_recharge_invoice/:uid

- controller: ``app\admin\controller\UserManageController::addRechargeInvoice``
- desc: 创建充值账单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| amount | 整型 | 非必填 | - | - | 金额 |
| notes | 整型 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  请求取消列表 -- GET admin/request_cancel_list

- controller: ``app\admin\controller\UserManageController::requestCancelList``
- desc: 请求取消列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  取消请求原因管理 -- GET admin/request_cancel_reason

- controller: ``app\admin\controller\UserManageController::requestCancelReason``
- desc: 取消请求原因管理 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  取消请求原因增加修改 -- POST admin/request_cancel_reason_post

- controller: ``app\admin\controller\UserManageController::requestCancelReasonPost``
- desc: 取消请求原因增加修改 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 必填 | 1 | - | 1添加2修改 |
| id | 整型 | 必填 | 1 | - | id |
| reason | 整型 | 必填 | 10 | - | 原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除请求原因 -- GET admin/del_reason

- controller: ``app\admin\controller\UserManageController::DelReason``
- desc: 删除请求原因 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  删除取消请求 -- DELETE admin/request_cancel_list/:id

- controller: ``app\admin\controller\UserManageController::deleteCancelRequest``
- desc: 删除取消请求 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  添加跟踪记录 -- POST admin/add_record_log

- controller: ``app\admin\controller\UserManageController::addRecordLog``
- desc: 添加跟踪记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| stime | 整型 | 必填 | 1 | - | 时间戳(前端除1000,用秒) |
| record | 字符串 | 必填 | 1 | - | 记录 |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  添加跟踪记录 补充说明 -- POST admin/add_remark_log

- controller: ``app\admin\controller\UserManageController::addRemarkLog``
- desc: 添加跟踪记录 补充说明 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 记录ID |
| remark | 字符串 | 必填 | 1 | - | 补充说明 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  获取跟踪记录 -- GET admin/track_record

- controller: ``app\admin\controller\UserManageController::getTrackRecord``
- desc: 获取跟踪记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |
| start_time | 整型 | 非必填 | 1 | - | 非必传 |
| end_time | 整型 | 非必填 | 1 | - | 非必传 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "track_status":"跟踪状态,1待开始，2跟进中，3已完成",
    "track_status_zh":"跟踪状态,1待开始，2跟进中，3已完成",
    "list":[{//记录
      "id":"记录ID",
      "des":"记录",
      "remark":[{//补充说明
        "remark":"补充说明",
      }]
      "create_time":"创建时间",
    }]
  }
}
```

###  修改跟踪记录状态 -- POST admin/track_status

- controller: ``app\admin\controller\UserManageController::clientTrackStatus``
- desc: 修改跟踪记录状态 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |
| track_status | 整型 | 必填 | 1 | - | 跟踪状态：1待开始，2跟进中，3已完成 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  获取备注 -- GET admin/get_client_notes

- controller: ``app\admin\controller\UserManageController::getClientNotes``
- desc: 获取备注 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  获取备注 -- POST admin/post_client_notes

- controller: ``app\admin\controller\UserManageController::postClientNotes``
- desc: 获取备注 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |
| notes | 字符串 | 必填 | 1 | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  后台实名认证 -- GET admin/post_client_notes

- controller: ``app\admin\controller\UserManageController::authorInfo``
- desc: 后台实名认证 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

###  后台实名认证提交 -- POST admin/authorSubmit

- controller: ``app\admin\controller\UserManageController::authorSubmit``
- desc: 后台实名认证提交 -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\UserManageController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\UserManageController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\UserManageController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\UserManageController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\UserManageController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\UserManageController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\UserManageController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\UserManageController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 权限管理(管理员分组)

### 管理员组列表 -- GET /admin/rbac

- controller: ``app\admin\controller\RbacController::index``
- desc: 管理员组列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "roles":[{//  菜单列表
      "name":"组名称",
      "user_login":"已指派的管理员",
    }]
    "rule":[{// 权限列表
      "list":[{//子权限列表
        "name":"权限名称",
        "title":"权限标题",
      }]
      "name":"子权限名称",
      "title":"子权限标题",
    }]
  }
}
```

### 添加角色页面（添加管理员分组页面） -- GET admin/rbac/role_page

- controller: ``app\admin\controller\RbacController::addRolePage``
- desc: 添加角色页面（添加管理员分组页面） -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "roles":[{//  菜单列表
    }]
    "rule":[{// 权限列表
      "list":[{//子权限列表
        "name":"权限名称",
        "title":"权限标题",
      }]
      "name":"子权限名称",
      "title":"子权限标题",
    }]
  }
}
```

### 添加角色（添加管理员分组） -- POST admin/rbac

- controller: ``app\admin\controller\RbacController::addRole``
- desc: 添加角色（添加管理员分组） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | str | 必填 | - | - | 名称 |
| remark | str | 非必填 | - | - | 描述 |
| status | 整型 | 必填 | - | - | 状态（1：开启，0：禁用） |
| auth[] | 整型 | 非必填 | - | - | 权限ID组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑角色页面（编辑管理员分组页面） -- GET admin/rbac/:id

- controller: ``app\admin\controller\RbacController::editRolePage``
- desc: 编辑角色页面（编辑管理员分组页面） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | str | 必填 | - | - | 管理员组ID |
| is_display | str | 非必填 | - | - | 0不显示1菜单显示 |
| name | str | 非必填 | - | - | 搜索关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "role":[{//管理员组信息
      "name":"名称",
      "remark":"描述",
    }]
    "is_display  remark":"是否是菜单",
    "auths":"所有权限ID",
    "auth_select":"已选择权限ID",
    "user":"用户",
    "rule":[{// 权限列表
      "list":[{//子权限列表
        "name":"权限名称",
        "title":"权限标题",
      }]
      "name":"子权限名称",
      "title":"子权限标题",
    }]
  }
}
```

### 编辑角色（编辑管理员分组） -- POST admin/rbac/edit

- controller: ``app\admin\controller\RbacController::editRole``
- desc: 编辑角色（编辑管理员分组） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 管理员组ID |
| name | str | 必填 | - | - | 名称 |
| remark | str | 非必填 | - | - | 描述 |
| status | 整型 | 必填 | - | - | 状态（1：开启，0：禁用） |
| auth[] | 整型 | 非必填 | - | - | 权限ID组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除角色(删除管理员组) -- DELETE admin/rbac/:id/

- controller: ``app\admin\controller\RbacController::delete``
- desc: 删除角色(删除管理员组) -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 管理员组id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 权限复制 admin/rbac/copyRole

- controller: ``app\admin\controller\RbacController::copyRole``
- desc: 权限复制 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| role_id | 整型 | 必填 | - | - | 复制的分组id |
| role_name | 字符串 | 必填 | - | - | 分组名称 |
| role_remark | 字符串 | 非必填 | - | - | 说明 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 管理员管理

### 管理员列表 -- GET admin/admin

- controller: ``app\admin\controller\UserController::adminList``
- desc: 管理员列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| search | 字符串 | 非必填 | - | - | 搜索 |
| limit | 整型 | 非必填 | 50 | - | 每页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "page":"当前页数",
    "limit":"每页条数",
    "count":"总条数",
    "max_page":"总页数",
    "list":[{//管理员列表
      "id":"管理员用户id",
      "user_login":"管理员用户名",
      "user_nickname":"管理员姓名",
      "user_email":"邮箱",
      "create_time":"创建时间",
      "user_status":"状态0禁用1可用",
      "last_login_time":"上次登录时间",
      "last_login_ip":"上次登录ip",
      "role":"管理员角色",
      "dept":"工单部门",
      "is_sale":"是否销售0=默认1=是",
      "sale_is_use":"销售是否启用0=默认1=启用",
    }]
  }
}
```

### 管理员添加页面 -- GET admin/create_page

- controller: ``app\admin\controller\UserController::createPage``
- desc: 管理员添加页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "roles：角色信息@":"",
    "depts：部门@":"",
    "lang：语言":"",
  }
}
```

### 管理员添加 -- POST admin/admin

- controller: ``app\admin\controller\UserController::create``
- desc: 管理员添加 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| role_id | 整型 | 必填 | 1 | - | 管理员角色ID(单选) |
| user_login | 字符串 | 必填 | 1 | - | 管理员用户名 |
| user_nickname | 字符串 | 必填 | 1 | - | 管理员姓名 |
| user_email | 字符串 | 必填 | 1 | - | 邮箱 |
| user_pass | 字符串 | 必填 | 1 | - | 密码 |
| signature | 字符串 | 必填 | 1 | - | 签名 |
| user_status | 整型 | 必填 | 1 | - | 管理员状态0:禁用,1:正常 |
| language | 字符串 | 必填 | 1 | - | 语言 |
| dept_id[] | 数组 | 必填 | 1 | - | 部门ID(多选) |
| is_sale | 整型 | 非必填 | 0 | - | 是否销售0=默认1=是 |
| sale_is_use | 整型 | 非必填 | 0 | - | 销售是否启用0=默认1=启用 |
| only_mine | 字符串 | 非必填 | 0 | - | 只能查看自己的销售人员; |
| code | 字符串 | 非必填 | 0 | - | 验证码 |
| is_receive | 字符串 | 非必填 | 0 | - | 是否接收业务类邮件; |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 管理员编辑显示 -- GET admin/admin/:id

- controller: ``app\admin\controller\UserController::updatePage``
- desc: 管理员编辑显示 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "roles：角色信息@":"",
    "depts：部门@":"",
    "lang：语言":"",
    "user":[{//用户
      "user_login":"管理员用户名",
      "user_status":"状态（0：禁用，1：启用）",
      "user_pass":"密码",
      "user_nickname":"昵称",
      "user_email":"邮件",
      "signature":"签名",
      "language":"语言",
      "language":"语言",
      "is_sale":"是否销售0=默认1=是",
      "sale_is_use":"销售是否启用0=默认1=启用",
      "role_id":"角色ID",
    }]
    "dept_select":"已选部门ID",
  }
}
```

### 管理员编辑 -- POST admin/admin/update

- controller: ``app\admin\controller\UserController::update``
- desc: 管理员编辑 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 管理员ID |
| role_id | 整型 | 必填 | 1 | - | 管理员角色ID(单选) |
| user_login | 字符串 | 必填 | 1 | - | 管理员用户名 |
| user_nickname | 字符串 | 必填 | 1 | - | 管理员姓名 |
| user_email | 字符串 | 必填 | 1 | - | 邮箱 |
| user_pass | 字符串 | 非必填 | 1 | - | 密码(不改，就不传) |
| signature | 字符串 | 必填 | 1 | - | 签名 |
| user_status | 整型 | 必填 | 1 | - | 管理员状态0:禁用,1:正常 |
| language | 字符串 | 必填 | 1 | - | 语言 |
| is_sale | 整型 | 非必填 | 0 | - | 是否销售0=默认1=是 |
| sale_is_use | 整型 | 非必填 | 0 | - | 销售是否启用0=默认1=启用 |
| dept_id[] | 数组 | 必填 | 1 | - | 部门ID(多选) |
| only_mine | 字符串 | 非必填 | 0 | - | 只能查看自己的销售人员; |
| is_receive | 字符串 | 非必填 | 0 | - | 是否接收业务类邮件; |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 管理员删除 -- DELETE admin/admin/:id/

- controller: ``app\admin\controller\UserController::delete``
- desc: 管理员删除 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 停用管理员 -- GET admin/ban/:id/

- controller: ``app\admin\controller\UserController::ban``
- desc: 停用管理员 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 启用管理员 -- GET admin/cancel_ban/:id/

- controller: ``app\admin\controller\UserController::cancelBan``
- desc: 启用管理员 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 管理员ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 管理员修改自己信息(页面) -- GET admin/user/edit_self_info_page

- controller: ``app\admin\controller\UserController::editSelfInfoPage``
- desc: 管理员修改自己信息(页面) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "user":[{//用户信息
      "user_login":"用户名user_nickname:真实姓名user_email:邮箱language:语言",
    }]
    "lang":[{//语言列表
    }]
  }
}
```

### 管理员修改自己信息 -- POST admin/user/edit_self_info

- controller: ``app\admin\controller\UserController::editSelfInfo``
- desc: 管理员修改自己信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | 管理员ID |
| user_login | 字符串 | 必填 | 1 | - | 用户名 |
| user_email | 字符串 | 必填 | 1 | - | 邮箱 |
| language | 字符串 | 必填 | 1 | - | 语言 |
| original_pass | 字符串 | 非必填 | 1 | - | 原密码(不修改，此参数不传) |
| user_pass | 字符串 | 非必填 | 1 | - | 新密码(不修改，此参数不传) |
| re_user_pass | 字符串 | 非必填 | 1 | - | 确认密码(不修改，此参数不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 黑名单列表 -- GET admin/user/get_black_list

- controller: ``app\admin\controller\UserController::getBlackList``
- desc: 黑名单列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//黑名单
      "id":"idip:ipcreate_time:创建时间type:类型username:用户名",
    }]
  }
}
```

### 移除黑名单 -- POST admin/user/remove_black_list

- controller: ``app\admin\controller\UserController::removeBlackList``
- desc: 移除黑名单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台优惠码

### 添加优惠码页面 -- GET admin/add_promo_code/page

- controller: ``app\admin\controller\PromoCodeController::addPage``
- desc: 添加优惠码页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "type":"percent百分比,fixed固定金额,override置换价格,free免费安装",
    "products":[{//产品列表
      "pid":"产品ID",
      "gname":"",
      "pname":"",
    }]
    "cycles":"结算周期",
    "config_options":[{//可配置选项
      "id":"可配置选IDoption_name:可配置项名称name:可配置选项组名称",
    }]
  }
}
```

### 添加优惠码 -- POST admin/add_promo_code

- controller: ``app\admin\controller\PromoCodeController::add``
- desc: 添加优惠码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| code | 字符串 | 必填 | - | - | 优惠码 |
| type | 字符串 | 非必填 | percent | - | percent百分比,fixed固定金额,override置换价格,free免费安装 |
| recurring | 整型 | 非必填 | 0 | - | 是否循环优惠 |
| recurfor | 整型 | 非必填 | 0 | - | 循环优惠重复执行次数 |
| value | 浮点型 | 非必填 | 0 | - | 价值 |
| appliesto | 数组 | 非必填 | - | - | 适用的产品id |
| requires | 数组 | 非必填 | - | - | 需要的产品id |
| requires_exist | 整型 | 非必填 | 0 | - | 也可以用于账户中现有的产品 |
| cycles | 数组 | 非必填 | - | - | 结算周期 |
| start_time | 日期 | 非必填 | - | - | 开始日期 |
| expiration_time | 日期 | 非必填 | - | - | 失效日期 |
| max_times | 整型 | 非必填 | - | - | 最大使用次数 |
| lifelong | 整型 | 非必填 | - | - | 终身优惠 |
| one_time | 整型 | 非必填 | - | - | 一次性 |
| only_new_client | 整型 | 非必填 | - | - | 仅适用于新用户 |
| only_old_client | 整型 | 非必填 | - | - | 仅用于老客户 |
| once_per_client | 整型 | 非必填 | - | - | 每个用户只能使用一次 |
| upgrades | 整型 | 非必填 | - | - | 启用产品升级优惠（1启用，0禁用） |
| upgrade_type | 字符串 | 非必填 | - | - | product产品,option可配置选项 |
| upgrade_value | 浮点型 | 非必填 | - | - | 升级优惠值 |
| upgrade_value_type | 字符串 | 非必填 | percent | - | percent百分比,fixed固定金额 |
| upgrade_options | 数组 | 非必填 | - | - | 升级配置选项id |
| notes | 字符串 | 非必填 | - | - | 管理员备注 |
| is_discount | 字符串 | 非必填 | - | - | 那么下单时享有客户折扣的同时，也可使用优惠码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑优惠码页面 -- GET admin/save_promo_code/page

- controller: ``app\admin\controller\PromoCodeController::savePage``
- desc: 编辑优惠码页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 优惠码id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "type":"percent百分比,fixed固定金额,override置换价格,free免费安装",
    "products":[{//产品列表
      "pid":"产品ID",
      "gname":"",
      "pname":"",
    }]
    "cycles":"结算周期",
    "config_options":[{//可配置选项
      "id":"可配置选IDoption_name:可配置项名称name:可配置选项组名称",
    }]
  }
}
```

### 编辑优惠码 -- POST admin/save_promo_code

- controller: ``app\admin\controller\PromoCodeController::save``
- desc: 编辑优惠码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 优惠码id |
| code | 字符串 | 必填 | - | - | 优惠码 |
| type | 字符串 | 非必填 | - | - | percent百分比,fixed固定金额,override置换价格,free免费安装 |
| recurring | 整型 | 非必填 | - | - | 是否循环优惠 |
| recurfor | 整型 | 非必填 | - | - | 循环优惠重复执行次数 |
| value | 浮点型 | 非必填 | - | - | 价值 |
| appliesto | 数组 | 非必填 | - | - | 适用的产品id |
| requires | 数组 | 非必填 | - | - | 需要的产品id |
| requires_exist | 整型 | 非必填 | - | - | 也可以用于账户中现有的产品 |
| cycles | 数组 | 非必填 | - | - | 结算周期 |
| start_time | 日期 | 非必填 | - | - | 开始日期 |
| expiration_time | 日期 | 非必填 | - | - | 失效日期 |
| max_times | 整型 | 非必填 | - | - | 最大使用次数 |
| lifelong | 整型 | 非必填 | - | - | 终身优惠 |
| one_time | 整型 | 非必填 | - | - | 一次性 |
| only_new_client | 整型 | 非必填 | - | - | 仅适用于新用户 |
| only_old_client | 整型 | 非必填 | - | - | 仅用于老客户 |
| once_per_client | 整型 | 非必填 | - | - | 每个用户只能使用一次 |
| upgrades | 整型 | 非必填 | - | - | 启用产品升级优惠 |
| upgrade_type | 字符串 | 非必填 | - | - | product产品,option可配置选项 |
| upgrade_value | 浮点型 | 非必填 | - | - | 升级优惠值 |
| upgrade_value_type | 字符串 | 非必填 | - | - | percent百分比,fixed固定金额 |
| upgrade_options | 数组 | 非必填 | - | - | 升级配置选项id |
| notes | 字符串 | 非必填 | - | - | 管理员备注 |
| is_discount | 字符串 | 非必填 | - | - | 那么下单时享有客户折扣的同时，也可使用优惠码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除优惠码 -- POST admin/delete_promo_code

- controller: ``app\admin\controller\PromoCodeController::delete``
- desc: 删除优惠码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 优惠码id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 优惠码立即过期 -- POST admin/expired_promo_code

- controller: ``app\admin\controller\PromoCodeController::expireImmediately``
- desc: 优惠码立即过期 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 优惠码id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取优惠码列表 -- GET admin/list_promo_code

- controller: ``app\admin\controller\PromoCodeController::getList``
- desc: 获取优惠码列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 非必填 | active | - | all全部,expired过期,active未过期 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"优惠码id",
    ".code":"优惠码",
    ".type":"优惠码类型,percent百分比,fixed固定金额,override置换价格,free免费安装",
    ".recurring":"循环优惠 0否 1是",
    ".max_times":"最多使用次数 0无限制",
    ".used":"已使用次数",
    ".start_time":"开始时间",
    ".expiration_time":"失效时间",
  }
}
```

### 自动生成优惠码 -- GET admin/auto_promo_code

- controller: ``app\admin\controller\PromoCodeController::autoPromoCode``
- desc: 自动生成优惠码 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rand":"优惠码",
  }
}
```


---

## 后台服务器配置

### 服务器列表 -- GET /admin/servers_list

- controller: ``app\admin\controller\ConfigServersController::serverList``
- desc: 服务器列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| gid | 整型 | 非必填 | null | - | 组id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".data":"服务器列表",
    ".data.group_id":"服务器组ID",
    ".data.gname":"服务器组名称",
    ".data.type":"接口类型",
    ".data.server_id":"服务器ID",
    ".data.server_name":"服务器名称",
    ".data.disabled":"1禁用，0使用(默认)",
    ".data.link_status":"连接状态(1成功0失败)",
  }
}
```

### 添加服务器页面 -- GET /admin/servers_add

- controller: ``app\admin\controller\ConfigServersController::addServers``
- desc: 添加服务器页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".data.modules":"接口类型",
    ".data.groups":"接口组",
    ".data.groups.id":"",
    ".data.groups.name":"服务器名称",
    ".data.groups.type":"模块名称",
    ".data.groups.meta":[{//模块原数据
    }]
  }
}
```

### 获取模块对应服务器组 -- POST /admin/get_modules_group

- controller: ``app\admin\controller\ConfigServersController::getModulesGroup``
- desc: 获取模块对应服务器组 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| modules | 字符串 | 必填 | '' | - | 服务器模块名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".data.groups":"服务器组",
  }
}
```

### 添加服务器提交 -- POST /admin/servers_add_post

- controller: ``app\admin\controller\ConfigServersController::addServersPost``
- desc: 添加服务器提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 非必填 | 1 | - | 名称 |
| type | 字符串 | 必填 | '' | - | 接口类型 |
| ip_address | 整型 | 非必填 | 1 | - | ip地址 |
| assigned_ips | 整型 | 非必填 | 1 | - | 其他IP地址 |
| hostname | 整型 | 非必填 | 1 | - | 主机名 |
| gid | 整型 | 非必填 | 1 | - | 服务器组ID（下拉框）单选 |
| noc | img | 非必填 | 1 | - | 数据中心(图片上传) |
| status_address | 整型 | 非必填 | 1 | - | 服务器状态地址 |
| username | 整型 | 非必填 | 1 | - | 用户名 |
| password | 整型 | 非必填 | 1 | - | 密码 |
| accesshash | 整型 | 非必填 | 1 | - | 访问散列值 |
| disabled | 整型 | 非必填 | 1 | - | 1勾选禁用，0使用(默认)(单选框) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑服务器页面 -- GET /admin/edit_servers/:id

- controller: ``app\admin\controller\ConfigServersController::editServers``
- desc: 编辑服务器页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务器ID |
| noc_address | 整型 | 必填 | 1 | - | 图片地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑服务器提交 -- POST /admin/edit_servers_post

- controller: ``app\admin\controller\ConfigServersController::editServersPost``
- desc: 编辑服务器提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务器ID |
| name | 字符串 | 非必填 | 1 | - | 服务器名称 |
| ip_address | 字符串 | 非必填 | 1 | - | 服务器地址 |
| gid | 字符串 | 非必填 | 1 | - | 服务器组ID |
| assigned_ips | 字符串 | 非必填 | 1 | - | 其他地址 |
| noc | img | 非必填 | 1 | - | 数据中心(图片上传) |
| status_address | 整型 | 非必填 | 1 | - | 状态地址 |
| username | 字符串 | 非必填 | 1 | - | 用户名 |
| password | 字符串 | 非必填 | 1 | - | 密码 |
| accesshash | 字符串 | 非必填 | 1 | - | 访问散列值 |
| secure | 字符串 | 非必填 | 1 | - | 安全，1:选中复选框使用 |
| port | 整型 | 非必填 | 1 | - | 端口 |
| disabled | tinyint | 必填 | 1 | - | 是否显示，1默认显示，0不显示(单选框) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除服务器 -- GET /admin/delete_servers/:id

- controller: ``app\admin\controller\ConfigServersController::deleteServers``
- desc: 删除服务器 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 组列表 -- GET /admin/groups_list

- controller: ``app\admin\controller\ConfigServersController::groupsList``
- desc: 组列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".data":"组列表",
  }
}
```

### 创建服务器组页面 -- GET /admin/create_groups

- controller: ``app\admin\controller\ConfigServersController::createGroups``
- desc: 创建服务器组页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".data.servers":"接口列表",
    ".data.mode":"分配方式",
  }
}
```

### 创建服务器组提交 -- POST /admin/create_groups_post

- controller: ``app\admin\controller\ConfigServersController::createGroupsPost``
- desc: 创建服务器组提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| group_name | 字符串 | 非必填 | 0 | - | 服务器组名称 |
| mode | 整型 | 必填 | 1 | - | 分配方式 |
| sid | array|string | 非必填 | '' | - | 接口列表 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑服务器组页面 -- GET /admin/edit_server_groups/:id

- controller: ``app\admin\controller\ConfigServersController::editServerGroups``
- desc: 编辑服务器组页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务器组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".server_group":"服务器组信息",
    ".server_group.name":"服务器组名称",
    ".server_group.type":"选中的模块",
    ".module":"模块列表",
  }
}
```

### 编辑服务器组页面提交 -- POST /admin/edit_server_groups_post

- controller: ``app\admin\controller\ConfigServersController::editServerGroupsPost``
- desc: 编辑服务器组页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务器组ID |
| group_name | 字符串 | 非必填 | 1 | - | 服务器组名称 |
| type | 整型 | 非必填 | 1 | - | 选中模块 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除服务器组 -- GET /admin/delete_server_groups/:id

- controller: ``app\admin\controller\ConfigServersController::deleteServerGroups``
- desc: 删除服务器组 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务器组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务器连接测试 --  GET admin/server_test_link/:id

- controller: ``app\admin\controller\ConfigServersController::testLink``
- desc: 服务器连接测试 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".name":"server_status type",
    ".name":"msg type",
  }
}
```


---

## 可配置选项组

### 可选项配置组列表(本地已测试) -- GET /admin/options/groups_list

- controller: ``app\admin\controller\ConfigOptionsController::groupsList``
- desc: 可选项配置组列表(本地已测试) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| order_method | 整型 | 必填 | 10 | - | ASC,DESC |
| keyword | 字符串 | 非必填 | 1 | - | 按关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".total":"总数",
    ".totalPage":"总页数",
    ".list":"可配置选项组信息",
    ".list.id":"可配置选项组ID",
    ".list.name":"可配置选项组名称",
    ".list.description":"可配置选项组描述",
  }
}
```

### 创建可选项配置组页面(本地已测试) -- GET /admin/options/create_groups

- controller: ``app\admin\controller\ConfigOptionsController::createGroups``
- desc: 创建可选项配置组页面(本地已测试) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | number | 必填 | - | - | 产品组分类1=通用2=裸金属3=魔方云 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".products":"产品信息",
    ".products.pg_name":"产品组名称",
    ".products.p_name":"产品名称",
    ".products.p_id":"",
    ".products.link":"展示方式",
  }
}
```

### 创建可选项配置组页面提交(本地已测试) -- POST /admin/options/create_groups_post

- controller: ``app\admin\controller\ConfigOptionsController::createGroupsPost``
- desc: 创建可选项配置组页面提交(本地已测试) -- wyhcheckSaveLinkAgeLevel

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | - |
| description | 字符串 | 必填 | 1 | - | 可选项配置组描述 |
| products.p_id | 整型 | 非必填 | 1 | - | 产品ID,多选 |
| global | 整型 | 必填 | 1 | - | 是否全局配置项组,是1,0否, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".gid":"可选项配置组ID(前端需要根据gid判断是执行添加还是编辑)",
  }
}
```

### 编辑可配置选项组页面 Configurable Option Groups页面(完成) -- GET /admin/options/edit_groups/:gid

- controller: ``app\admin\controller\ConfigOptionsController::editGroups``
- desc: 编辑可配置选项组页面 Configurable Option Groups页面(完成) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 字符串 | 必填 | 1 | - | 可选项配置组ID |
| type | number | 必填 | - | - | 产品组分类1=通用2=裸金属3=魔方云 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".group":"可选项配置组信息",
    ".group.name":"可选项配置组名称",
    ".group.description":"可选项配置组描述",
    ".group.global":"是否全局配置项组：1是、0否",
    ".pids":"已经选择的产品组--产品ID",
    ".product":"产品信息",
    ".product.pg_name":"产品组名称",
    ".product.p_name":"产品名称",
    ".product.p_id":"产品ID",
    ".product.link":"展示方式",
    ".options":"可选项配置信息",
    ".options.option_name":"可选项配置名称",
    ".options.option_type":"可选项配置类型,1默认Dropdown,2radio,3yes/no,4quantity",
    ".options.order":"可选项配置排序默认0",
    ".options.hidden":"可选项配置是否显示：0默认显示，1隐藏",
    ".options.upgrade":"可选项配置是否可以升降级：1是，0否",
    ".options.is_discount":"可选项配置是否可以用于折扣：1是，0否",
    "edition":"0免费版，1专业版",
  }
}
```

### 编辑可配置选项组页面提交(完成) -- POST /admin/options/edit_groups_post

- controller: ``app\admin\controller\ConfigOptionsController::editGroupsPost``
- desc: 编辑可配置选项组页面提交(完成) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 字符串 | 必填 | 1 | - | 可选项配置组ID |
| name | 字符串 | 非必填 | 1 | - | 可选项配置组名称 |
| description | 字符串 | 非必填 | 1 | - | 可选项配置组描述 |
| productlinks[] | 整型 | 非必填 | 1 | - | 产品ID（多选框） |
| order[] | 整型 | 非必填 | 1 | - | 可配置选项排序,如：order[9],order[10] |
| hidden[] | 整型 | 非必填 | 1 | - | - |
| upgrade[] | 整型 | 非必填 | 1 | - | 是否升级：1默认是，0否 |
| is_discount[] | 整型 | 非必填 | 1 | - | 是否应用优惠：1默认是，0否 |
| is_rebate[] | 整型 | 非必填 | 1 | - | 是否折扣：1默认是，0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加可配置选项Configurable Options页面(完成已测) -- GET /admin/options/add_options_page

- controller: ``app\admin\controller\ConfigOptionsController::addOptionsPage``
- desc: 添加可配置选项Configurable Options页面(完成已测) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 整型 | 必填 | 1 | - | 可选项配置组ID |
| pid | 整型 | 非必填 | 1 | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加可配置选项Configurable Options页面(完成已测) -- POST /admin/options/add_options

- controller: ``app\admin\controller\ConfigOptionsController::addOptions``
- desc: 添加可配置选项Configurable Options页面(完成已测) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 整型 | 必填 | 1 | - | 可选项配置组ID |
| option_name | 字符串 | 必填 | 1 | - | 可选项配置名称 |
| option_type | tinyint | 必填 | 1 | - | 可选项配置类型：1默认Dropdown,2radio,3yes/no,4quantity |
| addoptionname | 字符串 | 必填 | 1 | - | 子项名称 |
| addsortorder | tinyint | 必填 | 1 | - | 排序默认为0 |
| addhidden | tinyint | 必填 | 1 | - | 1隐藏 |
| notes | 字符串 | 非必填 | 1 | - | 备注 |
| qty_stage | 字符串 | 非必填 | 1 | - | 数量阶梯 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "cid":"可配置项ID（页面跳转至编辑页）",
  }
}
```

### 删除可配置选项的子选项 -- GET /admin/options/delete_sub_options/:subid

- controller: ``app\admin\controller\ConfigOptionsController::deleteSubOptions``
- desc: 删除可配置选项的子选项 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| subid | 字符串 | 必填 | 1 | - | 子配置选项ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除可配置选项 -- GET /admin/options/delete_options/:cid

- controller: ``app\admin\controller\ConfigOptionsController::deleteOptions``
- desc: 删除可配置选项 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 字符串 | 必填 | 1 | - | 可配置选项ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除可配置选项组 -- GET /admin/options/delete_groups/:gid

- controller: ``app\admin\controller\ConfigOptionsController::deleteGroups``
- desc: 删除可配置选项组 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 字符串 | 必填 | 1 | - | 可选项配置组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 复制可配置选项组页面 -- GET /admin/options/duplicate_groups

- controller: ``app\admin\controller\ConfigOptionsController::duplicateGroups``
- desc: 复制可配置选项组页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".groups":"所有可选项配置组的信息",
  }
}
```

### 复制可配置选项组页面提交 -- POST /admin/options/duplicate_groups_post

- controller: ``app\admin\controller\ConfigOptionsController::duplicateGroupsPost``
- desc: 复制可配置选项组页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 整型 | 必填 | 1 | - | 可选项配置组ID |
| newname | 字符串 | 必填 | 1 | - | 新的可选项配置组名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 输出系统自带接口有操作系统配置选项的产品 -- GET /admin/options/config_options_check_os

- controller: ``app\admin\controller\ConfigOptionsController::configOptionsCheckOs``
- desc: 输出系统自带接口有操作系统配置选项的产品 -- xiong

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".pid":"产品ID",
    ".name":"产品名称",
    ".type":"产品类型",
  }
}
```

### 拉取系统自带接口操作系统并同步 -- POST /admin/options/config_options_check_os/:pid

- controller: ``app\admin\controller\ConfigOptionsController::configOptionsOs``
- desc: 拉取系统自带接口操作系统并同步 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑可配置项页面 -- GET /admin/options/edit_config/:cid

- controller: ``app\admin\controller\ConfigOptionsController::editConfig``
- desc: 编辑可配置项页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 整型 | 必填 | 1 | - | 可选项配置ID |
| pid | 整型 | 必填 | 1 | - | 产品ID |
| option_type | 整型 | 必填 | 5 | - | 配置项类型操作系统 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".option":"可配置选项信息 unit 单位",
    ".suboptions":"所有子项信息",
    ".pricingsall":"所有子项价格信息",
  }
}
```

### 获取下一级列表 -- GET /admin/options/getNextLinkAgeList

- controller: ``app\admin\controller\ConfigOptionsController::getNextLinkAgeList``
- desc: 获取下一级列表 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 子项id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑可配置项页面提交 -- POST /admin/options/edit_config_post

- controller: ``app\admin\controller\ConfigOptionsController::editConfigPost``
- desc: 编辑可配置项页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 整型 | 必填 | 1 | - | 可选项配置ID |
| configoptionname | 整型 | 必填 | 1 | - | 可配置项名称 |
| configoptiontype | 整型 | 必填 | 1 | - | 类型 |
| notes | 字符串 | 非必填 | 1 | - | 备注 |
| configqtyminimum | 整型 | 非必填 | 1 | - | 当类型为4时，最小值 |
| configqtymaximum | 整型 | 非必填 | 1 | - | 当类型为4时，最大值 |
| optionname[子项ID] | 整型 | 非必填 | 1 | - | 子项名称 |
| sortorder[子项ID] | 整型 | 非必填 | 1 | - | 子项排序 |
| qtyminimum[子项ID] | 整型 | 非必填 | 1 | - | 子项最小值 |
| qtymaximum[子项ID] | 整型 | 非必填 | 1 | - | 子项最大值 |
| price[货币ID][子项ID][osetupfee-tenly] | 整型 | 非必填 | 1 | - | 价格数据 |
| addoptionname | 整型 | 非必填 | 1 | - | 添加的子项名称 |
| addsortorder | 整型 | 非必填 | 1 | - | 添加的子项排序 |
| addhidden | 整型 | 非必填 | 1 | - | 添加的子项:1隐藏，0否 |
| is_discount | 整型 | 非必填 | 1 | - | 可配置项折扣:1开启，0否 |
| qty_stage | 整型 | 非必填 | 1 | - | 当配置项为数量类型时，需要填写此字段 |
| unit | 整型 | 非必填 | 1 | - | 单位:配置项类型为1 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 可配置项（层级联动） -- POST /admin/options/saveLinkAgeLevel

- controller: ``app\admin\controller\ConfigOptionsController::saveLinkAgeLevel``
- desc: 可配置项（层级联动） -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 整型 | 必填 | 1 | - | gid |
| option_name | 字符串 | 必填 | 1 | - | 可选项配置名称 |
| option_type | tinyint | 必填 | 1 | - | 可选项配置类型：1默认Dropdown,2radio,3yes/no,4quantity |
| linkage_pid | 整型 | 必填 | 0 | - | 配置组pid |
| is_discount | 整型 | 非必填 | 1 | - | 可配置项折扣:1开启，0否 |
| option_id | tinyint | 必填 | 0 | - | 可选项配置组ID(没有就不传) |
| notes | 字符串 | 非必填 | 1 | - | 备注 |
| qty_stage | 整型 | 非必填 | 1 | - | 当配置项为数量类型时，需要填写此字段 |
| unit | 整型 | 非必填 | 1 | - | 单位:配置项类型为1 |
| option_sub_name | 字符串 | 必填 | 1 | - | 子项名称 |
| sort_order | tinyint | 非必填 | 0 | - | 排序 |
| sub_linkage_pid | 整型 | 必填 | 0 | - | 子项对应pid |
| hidden | 整型 | 非必填 | 0 | - | 是否隐藏 |
| sub_option_id | tinyint | 必填 | 0 | - | 子项ID(没有就不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改单个配置项 -- POST /admin/options/saveConfigOptionInfo

- controller: ``app\admin\controller\ConfigOptionsController::saveConfigOptionInfo``
- desc: 修改单个配置项 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 整型 | 必填 | 1 | - | gid |
| option_name | 字符串 | 必填 | 1 | - | 可选项配置名称 |
| option_type | tinyint | 必填 | 1 | - | 可选项配置类型：1默认Dropdown,2radio,3yes/no,4quantity |
| linkage_pid | 整型 | 必填 | 0 | - | 配置组pid |
| is_discount | 整型 | 非必填 | 1 | - | 可配置项折扣:1开启，0否 |
| option_id | tinyint | 必填 | 0 | - | 可选项配置组ID(没有就不传) |
| notes | 字符串 | 非必填 | 1 | - | 备注 |
| qty_stage | 整型 | 非必填 | 1 | - | 当配置项为数量类型时，需要填写此字段 |
| unit | 整型 | 非必填 | 1 | - | 单位:配置项类型为1 |
| senior | 整型 | 非必填 | 0 | - | 高级 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 层级联动排序 -- POST /admin/options/saveLinkAgeOrder

- controller: ``app\admin\controller\ConfigOptionsController::saveLinkAgeOrder``
- desc: 层级联动排序 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sub_ids | string|array | 必填 | '' | - | 排好序的id（1,2,3,4） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 层级联动删除 -- POST /admin/options/delLinkAgeSub

- controller: ``app\admin\controller\ConfigOptionsController::delLinkAgeSub``
- desc: 层级联动删除 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sub_id | 整型 | 必填 | 0 | - | 要删除的id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 短信模块及模板配置

### 手机短信配置页面 -- GET /admin/config_message/config_mobile

- controller: ``app\admin\controller\ConfigMessageController::configMobile``
- desc: 手机短信配置页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".shd_allow_sms_send":"开启国内短信设置",
    ".shd_allow_sms_send_global":"开启国际短信设置",
    ".sms_operator":"国内手机运营商",
    ".sms_operator_global":"国际手机运营商",
    ".sms_operator_list":"短信商列表",
  }
}
```

### 手机短信配置页面提交 -- POST /admin/config_message/config_mobile_post

- controller: ``app\admin\controller\ConfigMessageController::configMobilePost``
- desc: 手机短信配置页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_allow_sms_send_cn | 整型 | 必填 | 1 | - | 国内短信开关 |
| shd_allow_sms_send_global | 整型 | 必填 | 1 | - | 国际短信开关 |
| sms_operator | 整型 | 必填 | 1 | - | 短信发送接口 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 模板首页 -- GET /admin/config_message/template_list

- controller: ``app\admin\controller\ConfigMessageController::templateList``
- desc: 模板首页 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sms_operator | 字符串 | 非必填 | 1 | - | 短信运营商：aliyun(默认)或者submail |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 1 | - | 每页多少条 |
| order | 字符串 | 必填 | 1 | - | 排序字段('id','template_id','type','title','content','status') |
| order_method | 字符串 | 必填 | 1 | - | 排序方式:asc |
| template_id | 字符串 | 非必填 | 1 | - | 模板ID(短信运营商提供)搜索 |
| title | 字符串 | 非必填 | 1 | - | 模板标题（搜索） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "smsoperator":[{//运营商
    }]
    "templates":[{//模板信息
      "id":"ID",
      "template_id":"模板ID(短信运营商提供)",
      "type":"0大陆，1非大陆",
      "title":"模板标题",
      "content":"模板内容",
      "remark":"备注",
      "status":"0未提交审核，1正在审核，2审核通过，3未通过审核",
    }]
  }
}
```

### 创建模板页面 -- GET /admin/config_message/create_template_page

- controller: ``app\admin\controller\ConfigMessageController::createTemplatePage``
- desc: 创建模板页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建模板页面可用参数 -- GET /admin/config_message/get_template_desc

- controller: ``app\admin\controller\ConfigMessageController::getTemplateDesc``
- desc: 创建模板页面可用参数 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 短信模块名字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建模板 -- POST /admin/config_message/create_template

- controller: ``app\admin\controller\ConfigMessageController::createTemplate``
- desc: 创建模板 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| range_type | 整型 | 必填 | 1 | - | 0大陆，1非大陆,2营销 |
| sms_operator | 整型 | 必填 | 1 | - | 短信运营商：aliyun或者submail |
| title | 整型 | 必填 | 1 | - | 标题 |
| content | 整型 | 必填 | 1 | - | 内容 |
| remark | 整型 | 非必填 | 1 | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更新模板页面 -- GET /admin/config_message/update_template/:id

- controller: ``app\admin\controller\ConfigMessageController::updateTemplate``
- desc: 更新模板页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".temp":"模板信息",
    ".temp.range_type":"0大陆，1非大陆",
  }
}
```

### 更新模板页面 -- POST /admin/config_message/update_template_post

- controller: ``app\admin\controller\ConfigMessageController::updateTemplatePost``
- desc: 更新模板页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 模板ID |
| range_type | 整型 | 必填 | 1 | - | 0大陆，1非大陆 |
| sms_operator | 整型 | 必填 | 1 | - | 短信运营商：aliyun或者submail |
| title | 整型 | 必填 | 1 | - | 标题 |
| content | 整型 | 必填 | 1 | - | 内容 |
| remark | 整型 | 必填 | 1 | - | 备注 |
| status | 整型 | 必填 | 1 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提交审核(可批量) -- POST /admin/config_message/check_post

- controller: ``app\admin\controller\ConfigMessageController::checkPost``
- desc: 提交审核(可批量) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids[] | 整型 | 必填 | 1 | - | 模板ID（数组） |
| type | 整型 | 必填 | 1 | - | 短信运营商 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "checkmsg":"提交审核情况",
  }
}
```

### 删除模板(可批量) -- GET /admin/config_message/delete_template

- controller: ``app\admin\controller\ConfigMessageController::deleteTemplate``
- desc: 删除模板(可批量) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids或者ids[] | 整型 | 必填 | 1 | - | 模板ID（数组） |
| type | 整型 | 必填 | 1 | - | 短信运营商：aliyun或者submail |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 发送设置页面 -- GET /admin/config_message/set_sms

- controller: ``app\admin\controller\ConfigMessageController::SetSmsTemplate``
- desc: 发送设置页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sms_operator | 字符串 | 非必填 | 1 | - | 短信运营商：aliyun或者submail |
| range_type | 整型 | 非必填 | 1 | - | 0大陆,1非大陆 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "sms_operator":"默认短信运营商",
    "range_type":"0大陆，1非大陆",
    "templates":"模板信息（所有审核通过的模板）",
    "select_temp":"选中的模板",
  }
}
```

### 发送设置页面提交 -- POST /admin/config_message/set_sms_post

- controller: ``app\admin\controller\ConfigMessageController::SetSmsTemplatePost``
- desc: 发送设置页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sms_operator | 字符串 | 必填 | 1 | - | 短信运营商：aliyun或者submail |
| range_type | 整型 | 必填 | 1 | - | 0大陆,1非大陆 |
| generated_invoice | 整型 | 必填 | 1 | - | 生成账单 |
| invoice_pay | 整型 | 必填 | 1 | - | 账单支付 |
| invoice_overdue_pay | 整型 | 必填 | 1 | - | 账单支付逾期 |
| submit_ticket | 整型 | 必填 | 1 | - | 提交工单 |
| ticket_reply | 整型 | 必填 | 1 | - | 工单回复 |
| host_suspend | 整型 | 必填 | 1 | - | 产品暂停提醒 |
| unpay_invoice | 整型 | 必填 | 1 | - | 未支付账单 |
| send_code | 整型 | 必填 | 1 | - | 发送验证码 |
| login_sms_remind | 整型 | 必填 | 1 | - | 登录提醒 |
| order_refund | 整型 | 必填 | 1 | - | 订单退款 |
| admin_order_paid | 整型 | 必填 | 1 | - | 订单支付提醒(管理员) |
| admin_order | 整型 | 必填 | 1 | - | 下订单提醒(管理员) |
| invoice_payment_confirmation | 整型 | 必填 | 1 | - | 订单支付提醒(客户) |
| second_renew_product_reminder | 整型 | 必填 | 1 | - | 产品到期续费第二次提醒(客户) |
| renew_product_reminder | 整型 | 必填 | 1 | - | 产品到期续费第一次提醒(客户) |
| third_invoice_payment_reminder | 整型 | 必填 | 1 | - | 第三次支付未完成提醒(客户) |
| second_invoice_payment_reminder | 整型 | 必填 | 1 | - | 第二次支付未完成提醒(客户) |
| first_invoice_payment_reminder | 整型 | 必填 | 1 | - | 第一次支付未完成提醒(客户) |
| uncertify_reminder | 整型 | 必填 | 1 | - | - |
| new_order_notice | 整型 | 必填 | 1 | - | 下单提醒(客户) |
| admin_product_suspension_faild | 整型 | 必填 | 1 | - | 产无法解除停用状态提醒(管理员) |
| admin_login_success | 整型 | 必填 | 1 | - | 管理员账号登录提醒 |
| admin_new_ticket_reply | 整型 | 必填 | 1 | - | 工单新回复提醒(管理员) |
| admin_new_ticket | 整型 | 必填 | 1 | - | 新工单提醒(管理员) |
| default_product_welcome | 整型 | 必填 | 1 | - | 产品开通提醒(用户) |
| zjmf_dcim_rebuild_system_success | 整型 | 必填 | 1 | - | 重装系统成功通知 |
| service_termination_notification | 整型 | 必填 | 1 | - | 未续期产品删除提醒(用户) |
| service_unsuspension_notification | 整型 | 必填 | 1 | - | 续费成功提醒(用户) |
| service_suspension_notification | 整型 | 必填 | 1 | - | 产品过期停用，续费将重新开启提醒(客户) |
| zjmf_dcim_product_welcome | 整型 | 必填 | 1 | - | zjmf_dcim_product_welcome |
| support_ticket_auto_close_notification | 整型 | 必填 | 1 | - | 工单关闭提醒(客户) |
| support_ticket_opened | 整型 | 必填 | 1 | - | 工单已开通提醒(客户) |
| email_bond_notice | 整型 | 必填 | 1 | - | 成功绑定提醒(客户) |
| registration_success | 整型 | 必填 | 1 | - | 注册成功 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 测试短信模板页面 -- GET /admin/config_message/test_message_template_page

- controller: ``app\admin\controller\ConfigMessageController::testMessageTemplatePage``
- desc: 测试短信模板页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sms_operator | 字符串 | 必填 | 1 | - | 短信供应商，aliyun,submail |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "phone_code":"国际手机区号(传此值)",
    "link":"关联(展示用)",
  }
}
```

### 测试短信模板 -- POST /admin/config_message/test_message_template

- controller: ``app\admin\controller\ConfigMessageController::testMessageTemplate``
- desc: 测试短信模板 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 模板ID |
| sms_operator | 字符串 | 必填 | 1 | - | 短信供应商，aliyun,submail |
| code | 整型 | 非必填 | 1 | - | 国际手机区号 |
| phone | 整型 | 必填 | 1 | - | 手机号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 测试短信 -- POST /admin/config_message/send_sms

- controller: ``app\admin\controller\ConfigMessageController::sendSmsTest``
- desc: 测试短信 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sms_operator | 字符串 | 必填 | 1 | - | 短信供应商，aliyun,submail |
| code | 整型 | 非必填 | 1 | - | 国际手机区号 |
| phone | 整型 | 必填 | 1 | - | 手机号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 短信模板列表 -- GET /admin/config_message/mobiletemplate_list

- controller: ``app\admin\controller\ConfigMessageController::mobiletemplateList``
- desc: 短信模板列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 非必填 | 1 | - | hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "smsoperator":[{//运营商
    }]
    "templates":[{//模板信息
      "id":"ID",
      "template_id":"模板ID(短信运营商提供)",
      "type":"0大陆，1非大陆",
      "title":"模板标题",
      "content":"模板内容",
      "remark":"备注",
      "status":"0未提交审核，1正在审核，2审核通过，3未通过审核",
    }]
    "default":"邮件默认",
  }
}
```

### 邮件模板列表 -- GET /admin/email_template/emailtemplate_list

- controller: ``app\admin\controller\ConfigMessageController::emailtemplateList``
- desc: 邮件模板列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 非必填 | 1 | - | hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".email_list":"邮件列表信息(按type分组显示)",
    ".email_list.type":"类型",
    ".email_list.name":"名称",
    ".email_list.disabled":"0显示默认，1隐藏",
    ".email_list.custom":"0系统邮件默认，1自定义",
    ".default":"邮件默认",
  }
}
```

### 测试邮件 -- POST /admin/config_message/send_email

- controller: ``app\admin\controller\ConfigMessageController::sendEmailTest``
- desc: 测试邮件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 发送邮件短信消息 -- POST /admin/config_message/sendmessage_post

- controller: ``app\admin\controller\ConfigMessageController::sendMessagePost1``
- desc: 发送邮件短信消息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| msgid | 整型 | 非必填 | 1 | - | 短信模板ID |
| msgtype | 整型 | 非必填 | 1 | - | 0，1勾选 |
| emaid | 整型 | 非必填 | 1 | - | 邮件模板ID |
| ematype | 整型 | 非必填 | 1 | - | 0，1勾选 |
| id | 整型 | 必填 | 1 | - | 用户id |
| hid | 整型 | 必填 | 1 | - | hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 货币配置

### 货币配置首页Currencies -- GET /admin/currency/currency_list

- controller: ``app\admin\controller\CurrencyController::currencyList``
- desc: 货币配置首页Currencies -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 第几页 |
| limit | 整型 | 非必填 | 1 | - | 每页几条 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".total":"总数",
    ".totalPage":"总页数",
    ".currencies":"货币信息",
    ".currencies.code":"",
    ".currencies.prefix":"",
    ".currencies.suffix":"",
    ".currencies.format":"",
    ".currencies.rate":"",
    ".currencies.default":"默认为1，，否则为0",
  }
}
```

### 添加货币种类 -- POST /admin/currency/add_currency

- controller: ``app\admin\controller\CurrencyController::addCurrency``
- desc: 添加货币种类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| code | 字符串 | 必填 | 1 | - | 币种 |
| prefix | 字符串 | 必填 | 1 | - | - |
| suffix | 字符串 | 必填 | 1 | - | - |
| format | 字符串 | 必填 | 1 | - | 货币格式(下拉框)：1：1234.56；2：1,234.56 |
| rate | 浮点型 | 必填 | 1 | - | 税率 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑货币种类页面 -- GET /admin/currency/edit_currency/:id

- controller: ``app\admin\controller\CurrencyController::editCurrency``
- desc: 编辑货币种类页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 货币ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".currency":"货币信息",
    ".currency.code":"",
    ".currency.prefix":"",
    ".currency.suffix":"",
    ".currency.format":"货币格式(下拉框)：1：1234.56；2：1,234.56 ;3",
    ".currency.rate":"",
    ".currency.default":"默认为1，，否则为0",
  }
}
```

### 编辑货币种类页面提交 -- POST /admin/currency/edit_currency_post

- controller: ``app\admin\controller\CurrencyController::editCurrencyPost``
- desc: 编辑货币种类页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 币种ID |
| code | 字符串 | 必填 | 1 | - | 币种 |
| prefix | 字符串 | 必填 | 1 | - | - |
| suffix | 字符串 | 必填 | 1 | - | - |
| format | tinyint | 必填 | 1 | - | - |
| rate | number | 必填 | 1 | - | 税率 |
| updatepricing | 字符串 | 非必填 | 1 | - | 是否更新价格信息：是(on) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除货币种类 -- GET /admin/currency/delete_currency/:id

- controller: ``app\admin\controller\CurrencyController::deleteCurrency``
- desc: 删除货币种类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 币种ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更新汇率 -- GET /admin/currency/update_rate

- controller: ``app\admin\controller\CurrencyController::updateRate``
- desc: 更新汇率 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 选择默认货币(可能不需要) -- GET /admin/currency/default_currency/:id

- controller: ``app\admin\controller\CurrencyController::defaultCurrency``
- desc: 选择默认货币(可能不需要) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 货币ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更新价格 -- GET /admin/currency/update_price

- controller: ``app\admin\controller\CurrencyController::updatePrice``
- desc: 更新价格 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台合同模块

### 合同模块基础设置 -- GET /admin/contract/setting

- controller: ``app\admin\controller\ContractController::setting``
- desc: 合同模块基础设置 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "contract_open":"是否开启合同管理",
    "contract_limit_custom":"合同申请时间限制是否自定义：1是，0否",
    "contract_limit":"合同申请时间限制",
    "contract_institutions":"单位名称",
    "contract_phonenumber":"电话",
    "contract_consignee_address":"收件地址",
    "contract_postcode":"邮编",
    "contract_postcode_fee":"邮费",
    "contract_number_custom":"合同编号是否自定义：0默认，1自定义",
    "contract_number":"合同编号",
    "contract_number_prefix":"编号自定义时前缀",
    "contract_pdf_logo":"pdf logo",
    "contract_company_logo":"公司印章",
    "currency":"货币信息",
  }
}
```

### 合同模块基础设置 -- POST /admin/contract/setting

- controller: ``app\admin\controller\ContractController::settingPost``
- desc: 合同模块基础设置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| contract_open | 整型 | 必填 | 1 | - | 是否开启合同管理 |
| contract_limit_custom | 整型 | 必填 | 1 | - | 合同申请时间限制是否自定义：1是，0否 |
| contract_limit | 整型 | 必填 | 1 | - | 合同申请时间限制 |
| contract_institutions | 整型 | 必填 | 1 | - | 单位名称 |
| contract_phonenumber | 整型 | 必填 | 1 | - | 电话 |
| contract_consignee_address | 整型 | 必填 | 1 | - | 收件地址 |
| contract_postcode | 整型 | 必填 | 1 | - | 邮编 |
| contract_postcode_fee | 整型 | 必填 | 1 | - | 邮费 |
| contract_number_custom | 整型 | 必填 | 1 | - | 合同编号是否自定义：0默认，1自定义 |
| contract_number | 整型 | 必填 | 1 | - | 合同编号长度 |
| contract_number_prefix | 整型 | 必填 | 1 | - | 编号自定义时前缀 |
| contract_pdf_logo | 整型 | 必填 | 1 | - | pdf |
| contract_company_logo | 整型 | 必填 | 1 | - | 公司印章,这里传文件名(文件上传调admin/upload_image接口) |
| contract_address | 整型 | 必填 | 1 | - | 单位地址 |
| contract_username | 整型 | 必填 | 1 | - | 联系人 |
| contract_email | 整型 | 必填 | 1 | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加/编辑合同页面 -- GET /admin/contract/detail/[:id]

- controller: ``app\admin\controller\ContractController::detail``
- desc: 添加/编辑合同页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":"产品组--产品信息",
    "contract":[{//合同信息
      "id":"ID",
      "name":"名称",
      "remark":"备注",
      "status":"状态：0关闭(默认)，1显示",
      "force":"1强制合同，0否",
      "suspended":"未签订xx天后操作",
      "suspended_type":"未签订xx天后暂停或无法访问产品内页",
      "base":"是否基础合同：1是，0否",
      "is_post":"1支持邮寄，0否",
      "nocheck":"1无需审核，0否",
      "product_id":"已选择的产品ID",
      "inscribe_custom":"落款信息是否自定义：1自定义，0默认",
      "notes":"提示：0不提示(默认)，1全局提示，2产品页提示",
      "represent":"授权代表",
      "phonenumber":"代表电话",
      "email":"电子邮箱",
      "content":"合同内容",
    }]
    "contract_args":"合同参数",
  }
}
```

### 创建\编辑合同页面提交 -- POST /admin/contract/detail/[:id]

- controller: ``app\admin\controller\ContractController::detailPost``
- desc: 创建\编辑合同页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | 合同ID |
| name | 字符串 | 必填 | 1 | - | 合同名称 |
| status | tinyint | 必填 | 1 | - | 状态：0停用，1启用 |
| force | tinyint | 必填 | 1 | - | 是否强制：0否(默认)，1是 |
| pids[] | 整型 | 必填 | 1 | - | 产品ID(多选) |
| represent | 字符串 | 必填 | 1 | - | 授权代表 |
| phonenumber | 字符串 | 必填 | 1 | - | 代表电话 |
| remark | 字符串 | 必填 | 1 | - | 备注 |
| content | 字符串 | 必填 | 1 | - | 合同内容 |
| suspended | 字符串 | 必填 | 1 | - | 未签订xx天后暂停或产品内容页无法访问 |
| suspended_type | 字符串 | 必填 | 1 | - | 未签订xx天后暂停或产品内容页无法访问:suspended暂停产品，noaccess产品内容页无法访问 |
| inscribe_custom | 字符串 | 必填 | 1 | - | 落款信息是否自定义：1自定义，0默认 |
| base | 字符串 | 必填 | 1 | - | 是否基础合同：1是，0否 |
| email | 字符串 | 必填 | 1 | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 合同模板列表 -- GET /admin/contract/tpl

- controller: ``app\admin\controller\ContractController::tpl``
- desc: 合同模板列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 1 | - | 每页多少条 |
| order | 字符串 | 必填 | 1 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| status | 整型 | 非必填 | 1 | - | 按合同状态搜索 |
| keyword | 整型 | 非必填 | 1 | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "contracts":[{//合同列表信息
    }]
  }
}
```

### 删除合同模板 -- DELETE /admin/contract/tpl/:id

- controller: ``app\admin\controller\ContractController::deleteTpl``
- desc: 删除合同模板 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 合同列表 -- GET /admin/contract/contract

- controller: ``app\admin\controller\ContractController::contract``
- desc: 合同列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 1 | - | 每页多少条 |
| order | 整型 | 必填 | 1 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| domainstatus | 字符串 | 非必填 | 1 | - | 按产品状态搜索 |
| status | 字符串 | 非必填 | 1 | - | 按合同状态搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "contracts":[{//合同列表
      "id":"合同ID",
      "username":"用户名",
      "phonenumber":"电话",
      "companyname":"产品名称",
      "name":"产品名称",
      "domain":"主机名",
      "dedicatedip":"ip",
      "domainstatus_zh":"产品状态中文",
      "status_zh":"合同状态中文",
      "status":"",
      "create_time":"签订时间",
      "paid_time":"付款时间",
      "express_company":"快递公司",
      "express_order":"快递单号",
      "username":"收件人",
      "detail":"收件地址",
      "phone":"手机",
      "is_post":"是否邮寄：1是，0否",
      "force":"是否强制：0否(默认)，1是",
    }]
  }
}
```

### 合同作废 -- POST /admin/contract/cancel

- controller: ``app\admin\controller\ContractController::cancel``
- desc: 合同作废 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids[] | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 合同查看下载 -- GET /admin/contract/download/:id

- controller: ``app\admin\controller\ContractController::download``
- desc: 合同查看下载 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "pdf":"合同地址",
  }
}
```

### 合同邮寄管理 -- POST /admin/contract/contract/:id

- controller: ``app\admin\controller\ContractController::contractPost``
- desc: 合同邮寄管理 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |
| is_post | 整型 | 必填 | 1 | - | 是否邮寄：1是，0否 |
| express_company | 整型 | 必填 | 1 | - | 快递公司 |
| express_order | 整型 | 必填 | 1 | - | 快递单号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 取消邮寄 -- POST /admin/contract/cancel_post/:id

- controller: ``app\admin\controller\ContractController::cancelPost``
- desc: 取消邮寄 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 审核通过 -- POST /admin/contract/check

- controller: ``app\admin\controller\ContractController::check``
- desc: 审核通过 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids[] | 整型 | 必填 | 1 | - | 合同ID,数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除合同 -- POST /admin/contract/delete

- controller: ``app\admin\controller\ContractController::delete``
- desc: 删除合同 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids[] | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 签订合同页面 -- GET /admin/contract/contract_page

- controller: ``app\admin\controller\ContractController::contractPage``
- desc: 签订合同页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 签订合同 -- POST /admin/contract/contract_page/:id

- controller: ``app\admin\controller\ContractController::contractPagePost``
- desc: 签订合同 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 合同ID |
| sign | 整型 | 必填 | 1 | - | 签名base64字符串 |
| content | 整型 | 必填 | 1 | - | 合同内容,传html |
| type | 整型 | 必填 | 1 | - | 类型：I输出到浏览器 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 客户关怀模块

### 搜索条件 -- GET /admin/client_care/search_condition

- controller: ``app\admin\controller\ClientCareController::searchCondition``
- desc: 搜索条件 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".trigger":"搜索条件",
  }
}
```

### 客戶关怀首页(搜索) -- GET /admin/client_care/care_list

- controller: ``app\admin\controller\ClientCareController::careList``
- desc: 客戶关怀首页(搜索) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 1 | - | 每页多少条 |
| order | 整型 | 必填 | 1 | - | 排序字段 |
| order_method | 整型 | 必填 | 10 | - | ASC,DESC |
| name | 字符串 | 非必填 | 1 | - | 按关怀名称搜索 |
| trigger | 字符串 | 非必填 | 1 | - | 按触发条件搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "care_list":[{//客户关怀列表
      "care_id":"客户关怀列表id",
      "name":"关怀名称",
      "trigger":"触发条件",
      "method":"关怀方式",
      "time":"天数",
      "email_template":"邮件模板",
      "message_template":"短信模板",
      "range_type":"1大陆，0非大陆",
      "status":"状态:1可用，0不可用",
      "create_time":"创建时间",
      "update_time":"更新时间",
    }]
  }
}
```

### 添加关怀条件页面 -- GET /admin/client_care/create_care

- controller: ``app\admin\controller\ClientCareController::createCare``
- desc: 添加关怀条件页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".products":"产品组--产品信息",
    ".trigger":"触发条件",
    ".method":"关怀方式",
    ".email_template":"邮件模板",
    ".message_tmeplate":"短信模板",
  }
}
```

### 添加关怀条件页面提交 -- POST /admin/client_care/create_care_post

- controller: ``app\admin\controller\ClientCareController::createCarePost``
- desc: 添加关怀条件页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | - |
| trigger | 字符串 | 必填 | 1 | - | 触发条件 |
| ids[] | 字符串 | 必填 | 1 | - | 产品ID（数组） |
| time | 字符串 | 必填 | 1 | - | 天数 |
| method[] | 字符串 | 必填 | 1 | - | (多选框)关怀方式(邮件email、短信message、微信wechat(暂不考虑)) |
| range_type | 字符串 | 必填 | 1 | - | 1大陆，0非大陆(选择) |
| mailtemp_id | 字符串 | 必填 | 1 | - | 邮件模板ID:根据关怀方式弹出对应的模板选择. |
| message_id | 字符串 | 必填 | 1 | - | 短信模板ID |
| status | 字符串 | 必填 | 1 | - | 状态：1可用(默认),0未用 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑关怀条件页面 -- GET /admin/client_care/edit_care/:id

- controller: ``app\admin\controller\ClientCareController::editCare``
- desc: 编辑关怀条件页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 关怀条件ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".care":"关怀条件信息",
    ".products":"产品信息",
    ".link_products":"选中的产品",
    ".triggers":"触发条件",
    ".method":"关怀方式",
    ".email_template":"所有类型为care的邮件模板",
    ".message_template":"所有短信模板",
  }
}
```

### 编辑关怀条件页面提交 -- POST /admin/client_care/edit_care_post

- controller: ``app\admin\controller\ClientCareController::editCarePost``
- desc: 编辑关怀条件页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 关怀条件ID |
| name | 字符串 | 必填 | 1 | - | 关怀名称 |
| trigger | 字符串 | 必填 | 1 | - | 触发条件 |
| ids[] | 字符串 | 必填 | 1 | - | 产品ID（数组） |
| time | 字符串 | 必填 | 1 | - | 天数 |
| method[] | 字符串 | 必填 | 1 | - | (多选框)关怀方式(邮件email、短信message、微信wechat(暂不考虑)) |
| range_type | 字符串 | 必填 | 1 | - | 1大陆，0非大陆(选择) |
| mailtemp_id | 字符串 | 必填 | 1 | - | 邮件模板ID:根据关怀方式弹出对应的模板选择. |
| message_id | 字符串 | 必填 | 1 | - | 短信模板ID |
| status | 字符串 | 必填 | 1 | - | 状态：1可用(默认),0未用 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除关怀条件 -- GET /admin/client_care/delete_care/:id

- controller: ``app\admin\controller\ClientCareController::deleteCare``
- desc: 删除关怀条件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 关怀条件ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 邮件模板

### 邮件模板列表 -- GET /admin/email_template/email_list

- controller: ``app\admin\controller\EmailTemplateController::emailList``
- desc: 邮件模板列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 第几页 |
| limit | 整型 | 非必填 | 1 | - | 每页几条 |
| keyword | 整型 | 非必填 | 1 | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".total":"总数",
    ".totalPage":"总页数",
    ".email_list":"邮件列表信息(按type分组显示)",
    ".email_list.type":"类型",
    ".email_list.name":"名称",
    ".email_list.disabled":"0显示默认，1隐藏",
    ".email_list.custom":"0系统邮件默认，1自定义",
  }
}
```

### 邮件默认接口修改 -- POST /admin/email_template/operator_switch

- controller: ``app\admin\controller\EmailTemplateController::emailOperatorSwitch``
- desc: 邮件默认接口修改 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email_operator | 字符串 | 必填 | '' | - | 邮件value |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建邮件模板页面 -- GET /admin/email_template/create_template

- controller: ``app\admin\controller\EmailTemplateController::createTemplate``
- desc: 创建邮件模板页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".type":"下拉框：general(普通),product(产品/服务),invoice(账单),support(工单),notification(Notification)",
  }
}
```

### 创建邮件模板提交() -- POST /admin/email_template/create_template_post

- controller: ``app\admin\controller\EmailTemplateController::createTemplatePost``
- desc: 创建邮件模板提交() -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 1 | - | 邮件模板类型：下拉框：general(普通),product(产品/服务),invoice(账单),support(工单),notification(Notification) |
| name | 字符串 | 必填 | 1 | - | 邮件识别名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"邮件模板ID",
  }
}
```

### 管理邮件模板语言页面 -- GET /admin/email_template/manage_language

- controller: ``app\admin\controller\EmailTemplateController::manageLanguages``
- desc: 管理邮件模板语言页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".langs":"语言列表",
    ".lang_used":"被使用的语言",
  }
}
```

### 管理邮件模板语言页面提交 -- POST /admin/email_template/manage_language_post

- controller: ``app\admin\controller\EmailTemplateController::manageLanguagesPost``
- desc: 管理邮件模板语言页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| language | 整型 | 必填 | 1 | - | 语言 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 禁用语言 -- POST /admin/email_template/disabled

- controller: ``app\admin\controller\EmailTemplateController::disabled``
- desc: 禁用语言 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| language | 整型 | 必填 | 1 | - | 语言 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除模板 -- GET /admin/email_template/delete_template/:id

- controller: ``app\admin\controller\EmailTemplateController::deleteTemplate``
- desc: 删除模板 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 模板ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑邮件模板页面 -- GET /admin/email_template/edit_template/:id

- controller: ``app\admin\controller\EmailTemplateController::editTemplate``
- desc: 编辑邮件模板页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 邮件模板ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".emailtemplate":"邮件模板信息",
    ".emailtemplate.type":"邮件模板类型",
    ".emailtemplate.name":"邮件模板名称",
    ".emailtemplate.subject":"邮件模板主题",
    ".emailtemplate.message":"邮件模板消息",
    ".emailtemplate.attachments":"附件，返回的数组 "attachments"",
    ".emailtemplate.fromname":"发件人",
    ".emailtemplate.fromemail":"发件人邮箱",
    ".emailtemplate.disabled":"0默认显示，1隐藏",
    ".emailtemplate.custom":"0系统默认，1自定义",
    ".emailtemplate.language":"邮件模板信息",
    ".emailtemplate.copyto":"副本",
    ".emailtemplate.blind_copy_to":"绑定发送人邮件",
    ".emailtemplate.plaintext":"0默认，1纯文本",
    ".emailtemplate.child":"不同语言模板的主题和内容",
    ".base_args":"基础参数",
    ".client_args":"客户相关参数",
    ".combine":"模板类型相关参数",
  }
}
```

### 编辑邮件模板页面提交 -- POST /admin/email_template/edit_template_post

- controller: ``app\admin\controller\EmailTemplateController::editTemplatePost``
- desc: 编辑邮件模板页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 邮件模板ID |
| fromname | 字符串 | 必填 | 1 | - | - |
| fromemail | 字符串 | 必填 | 1 | - | - |
| copyto | 字符串 | 非必填 | 1 | - | - |
| blind_copy_to | 字符串 | 非必填 | 1 | - | - |
| plaintext | 整型 | 必填 | 1 | - | - |
| disabled | 整型 | 必填 | 1 | - | - |
| oldattachments[] | 字符串 | 非必填 | 1 | - | 旧的附件名称,参数形式oldattachments[0],oldattachments[1] |
| file[] | 字符串 | 必填 | 0 | - | 新上传的附件，有，则显示 |
| subject[id] | 字符串 | 必填 | 1 | - | 其中id是difflangs中的id，同一name不同语言模板的id |
| message[id] | 字符串 | 必填 | 1 | - | 必传参数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 禁用/启用模板 -- POST /admin/email_template/disabled_template

- controller: ``app\admin\controller\EmailTemplateController::disabledTemplate``
- desc: 禁用/启用模板 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 模板id |
| disabled | 整型 | 必填 | 1 | - | 0禁用,1启用 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台帮助中心

### 首页 -- GET /admin/knowledge_base/index

- controller: ``app\admin\controller\KnowledgeBaseController::index``
- desc: 首页 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "categories":[{//文章种类
      "id":"种类ID",
      "name":"种类名称",
      "description":"种类描述",
      "hidden":"0显示，1隐藏",
      "num":"文章数量(当前种类下)",
    }]
    "tags":[{//标签 (标签云形式)
      "tag":"标签名称",
      "num":"标签数量",
    }]
  }
}
```

### 按种类ID取文章数据 -- GET /admin/knowledge_base/category_list/:cid

- controller: ``app\admin\controller\KnowledgeBaseController::categoryList``
- desc: 按种类ID取文章数据 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "article":[{//文章
      "id":"文章ID",
      "title":"标题",
      "article":"内容",
      "views":"查看次数",
      "useful":"点赞次数",
      "hidden":"是否隐藏:0默认显示,1隐藏(单选框)",
      "order":"排序",
      "tag":"标签",
      "public_by":"发布人(文本框)",
      "public_time":"发布时间(文本框,选择时间)",
    }]
  }
}
```

### 按标签取数据 -- POST /admin/knowledge_base/tags_list

- controller: ``app\admin\controller\KnowledgeBaseController::tagsList``
- desc: 按标签取数据 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tag | 字符串 | 必填 | 1 | - | 可选参数：标签名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "article":[{//文章
      "id":"文章ID",
      "title":"标题",
      "article":"内容",
      "views":"查看次数",
      "useful":"点赞次数",
      "hidden":"是否隐藏:0默认显示,1隐藏(单选框)",
      "order":"排序",
      "tag":"标签",
      "public_by":"发布人(文本框)",
      "public_time":"发布时间(文本框,选择时间)",
    }]
  }
}
```

### 添加文章种类 -- POST /admin/knowledge_base/add_category

- controller: ``app\admin\controller\KnowledgeBaseController::addCategory``
- desc: 添加文章种类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | 种类名称 |
| description | 字符串 | 必填 | 1 | - | 种类描述 |
| hidden | 整型 | 必填 | 1 | - | 0默认显示，1隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".cid":"种类ID",
  }
}
```

### 添加文章 -- POST /admin/knowledge_base/add_category

- controller: ``app\admin\controller\KnowledgeBaseController::addArticle``
- desc: 添加文章 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 整型 | 必填 | 1 | - | 种类ID |
| title | 字符串 | 必填 | 1 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".aid":"文章ID",
  }
}
```

### 编辑文章页面 -- GET /admin/knowledge_base/edit_article/:id

- controller: ``app\admin\controller\KnowledgeBaseController::editArticle``
- desc: 编辑文章页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 文章ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "article":[{//文章信息
      "id":"文章ID",
      "title":"标题",
      "article":"内容",
      "views":"查看次数",
      "useful":"点赞次数",
      "hidden":"是否隐藏:0默认显示,1隐藏(单选框)",
      "order":"排序",
      "tag":"标签",
      "public_by":"发布人(文本框)",
      "public_time":"发布时间(文本框,选择时间)",
    }]
    "category":[{//种类列表
      "id":"种类ID",
      "name":"种类名称",
      "description":"种类描述",
    }]
    ".cid":"已选种类",
  }
}
```

### 编辑文章页面提交 -- POST /admin/knowledge_base/edit_article_post

- controller: ``app\admin\controller\KnowledgeBaseController::editArticlePost``
- desc: 编辑文章页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 文章id |
| title | 字符串 | 必填 | 1 | - | 标题 |
| article | 字符串 | 必填 | 1 | - | 内容(富文本编辑) |
| categories[] | 数组 | 必填 | 1 | - | 种类，多选数组传值 |
| views | 整型 | 必填 | 1 | - | 查看次数 |
| useful | 整型 | 必填 | 1 | - | 点赞次数 |
| hidden | 整型 | 必填 | 1 | - | 是否隐藏:0默认显示,1隐藏 |
| login_view | 整型 | 必填 | 1 | - | 登录查看:0默认所有人都可以看，1登录才能查看 |
| host_view | 整型 | 必填 | 1 | - | 是否有激活产品才能查看:0默认所有人可看，1有激活产品才能查看 |
| order | 整型 | 必填 | 1 | - | 排序 |
| tag | 字符串 | 非必填 | 1 | - | 标签,','隔开 |
| public_by | 字符串 | 必填 | 1 | - | 发布人(文本框) |
| public_time | 字符串 | 必填 | 1 | - | 发布时间(文本框,选择时间) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除文章 -- GET /admin/knowledge_base/delete_article/:id

- controller: ``app\admin\controller\KnowledgeBaseController::deleteArticle``
- desc: 删除文章 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 文章id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑种类页面 -- GET /admin/knowledge_base/edit_category/:id

- controller: ``app\admin\controller\KnowledgeBaseController::editCategory``
- desc: 编辑种类页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 种类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "category":[{//文章种类信息
      "name":"名称",
      "description":"描述",
      "hidden":"0默认显示，1隐藏",
    }]
  }
}
```

### 编辑种类页面提交 -- POST /admin/knowledge_base/edit_category_psot

- controller: ``app\admin\controller\KnowledgeBaseController::editCategoryPost``
- desc: 编辑种类页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 种类id |
| name | 字符串 | 必填 | 1 | - | 名称 |
| description | 字符串 | 必填 | 1 | - | 描述 |
| hidden | 整型 | 必填 | 1 | - | 0默认显示，1隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除文章种类 -- GET /admin/knowledge_base/delete_category/:id

- controller: ``app\admin\controller\KnowledgeBaseController::deleteCategory``
- desc: 删除文章种类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 种类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 富文本上传图片 -- POST /admin/knowledge_base/upload

- controller: ``app\admin\controller\KnowledgeBaseController::uploadHandle``
- desc: 富文本上传图片 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| file[] | 整型 | 必填 | 1 | - | 文件(数组)多文件上传 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 基础统计信息

### 基础信息 -- GET /admin/report/base_info

- controller: ``app\admin\controller\ReportController::baseInfo``
- desc: 基础信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| start_time | 整型 | 非必填 | 七天前 | - | 开始时间 |
| end_time | 整型 | 非必填 | 今日 | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "last_version":"最新版本",
    "install_version":"当前版本",
    "install_version":"当前版本",
    "latest_error_crond_time":"最近一次   自动任务状态异常 上次任务结束时间",
    "modules":[{//模块信息
      "modules_name":[{//模块名称
        "todaytotal":"今日收入",
        "thismonth":"本月收入",
        "amounts":"总收入",
        "latest_order_count":"近7天收入",
      }]
    }]
  }
}
```

### 获取信息系统展示模块列表 -- GET /admin/report/get_base_module

- controller: ``app\admin\controller\ReportController::getSystemInfoModulesList``
- desc: 获取信息系统展示模块列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改信息系统展示模块顺序 -- POST /admin/report/update_base_module

- controller: ``app\admin\controller\ReportController::updateSystemInfoModulesSort``
- desc: 修改信息系统展示模块顺序 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台工单部门

### 添加新部门页面 -- GET admin/get_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::addPage``
- desc: 添加新部门页面 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"管理员id",
    "user_login":"用户名",
    "user_nickname":"姓名",
  }
}
```

### 添加新部门 -- POST admin/add_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::add``
- desc: 添加新部门 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 部门名称 |
| description | 字符串 | 非必填 | - | - | 描述 |
| email | 字符串 | 必填 | - | - | 邮件地址 |
| admins | 数组 | 非必填 | - | - | 已指派的管理员 |
| only_reg_client | 整型 | 非必填 | 0 | - | 仅客户 |
| feedback_request | 整型 | 非必填 | 0 | - | 工单评分 |
| hidden | 整型 | 非必填 | 0 | - | 隐藏 |
| host | 字符串 | 非必填 | - | - | 主机名 |
| port | 字符串 | 非必填 | - | - | POP3端口 |
| login | 字符串 | 非必填 | - | - | 邮件地址 |
| password | 字符串 | 非必填 | - | - | 邮箱密码 |
| is_product_order | 整型 | 非必填 | - | - | 开启产品订单 |
| is_open_auto_reply | 整型 | 非必填 | - | - | 开启自动回复 |
| minutes | 整型 | 非必填 | - | - | 时间 |
| time_type | 整型 | 非必填 | m | - | 单位m分钟s秒 |
| bz | 字符串 | 非必填 | - | - | 内容 |
| is_related_upstream | 整型 | 非必填 | - | - | 关联上游(0:否1:是) |
| is_certifi | 整型 | 非必填 | - | - | 是否实名认证,1是,0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改工单部门 -- POST admin/save_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::save``
- desc: 修改工单部门 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 部门id |
| name | 字符串 | 必填 | - | - | 部门名称 |
| description | 字符串 | 非必填 | - | - | 描述 |
| email | 字符串 | 必填 | - | - | 邮件地址 |
| admins | 数组 | 非必填 | - | - | 已指派的管理员 |
| only_reg_client | 整型 | 非必填 | 0 | - | 仅客户 |
| feedback_request | 整型 | 非必填 | 0 | - | 工单评分 |
| hidden | 整型 | 非必填 | 0 | - | 隐藏 |
| host | 字符串 | 非必填 | - | - | 主机名 |
| port | 字符串 | 非必填 | - | - | POP3端口 |
| login | 字符串 | 非必填 | - | - | 邮件地址 |
| password | 字符串 | 非必填 | - | - | 邮箱密码 |
| addfieldname | 字符串 | 非必填 | - | - | 新增自定义字段名称 |
| addsortorder | 整型 | 非必填 | - | - | 新增自定义字段排序 |
| addfieldtype | 字符串 | 非必填 | - | - | 新增自定义字段类型 |
| addcfdesc | 字符串 | 非必填 | - | - | 新增自定义字段描述 |
| addregexpr | 字符串 | 非必填 | - | - | 新增自定义字段验证 |
| addfieldoptions | 字符串 | 非必填 | - | - | 新增自定义字段Select |
| addadminonly | 字符串 | 非必填 | - | - | 新增自定义字段仅管理员可见 |
| addrequired | 字符串 | 非必填 | - | - | 新增自定义字段仅管理员必填 |
| customfieldname | 数组 | 非必填 | - | - | 修改自定义字段名称 |
| customsortorder | 数组 | 非必填 | - | - | 修改自定义字段排序 |
| customfieldtype | 数组 | 非必填 | - | - | 修改自定义字段类型 |
| customcfdesc | 数组 | 非必填 | - | - | 修改自定义字段描述 |
| customregexpr | 数组 | 非必填 | - | - | 修改自定义字段验证 |
| customfieldoptions | 数组 | 非必填 | - | - | 修改自定义字段Select |
| customadminonly | 数组 | 非必填 | - | - | 修改自定义字段仅管理员可见 |
| customrequired | 数组 | 非必填 | - | - | 修改自定义字段仅管理员必填 |
| is_product_order | 整型 | 非必填 | - | - | 开启产品订单 |
| is_open_auto_reply | 整型 | 非必填 | - | - | 开启自动回复 |
| minutes | 整型 | 非必填 | - | - | 时间 |
| time_type | 整型 | 非必填 | m | - | 单位m分钟s秒 |
| bz | 字符串 | 非必填 | - | - | 内容 |
| is_related_upstream | 整型 | 非必填 | - | - | 关联上游(0:否1:是) |
| is_certifi | 整型 | 非必填 | - | - | 是否实名认证,1是,0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除工单部门 -- POST admin/delete_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::delete``
- desc: 删除工单部门 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 部门id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 向后排序 -- POST admin/movedown_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::moveDown``
- desc: 向后排序 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 部门id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 向前排序 -- POST admin/moveup_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::moveUp``
- desc: 向前排序 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 部门id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 工单部门列表 -- GET admin/list_ticket_department

- controller: ``app\admin\controller\TicketDepartmentController::getList``
- desc: 工单部门列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"工单部门id",
    ".name":"部门名称",
    ".description":"描述",
    ".email":"邮件地址",
    ".hidden":"是否隐藏",
    ".order":"排序值",
  }
}
```

### 部门详情 -- GET admin/list_ticket_department/:id

- controller: ``app\admin\controller\TicketDepartmentController::getDetail``
- desc: 部门详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 部门id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"部门id",
    ".name":"部门名称",
    ".description":"描述",
    ".email":"邮件地址",
    ".only_reg_client":"仅客户",
    ".only_client_open":"仅管道回复",
    ".no_auto_reply":"无自动回复",
    ".hidden":"隐藏?",
    ".host":"主机名",
    ".port":"POP3端口",
    ".login":"邮件地址",
    ".password":"邮箱密码",
    ".feedback_request":"反馈请求",
    ".is_certifi":"是否实名认证,1是,0否",
    ".admins":"已指派的管理员",
    ".customfields.id":"自定义字段id",
    ".customfields.fieldname":"自定义字段名称",
    ".customfields.fieldtype":"自定义字段类型",
    ".customfields.description":"自定义字段描述",
    ".customfields.regexpr":"自定义字段验证",
    ".customfields.fieldoptions":"自定义字段select options",
    ".customfields.adminonly":"自定义字段仅管理员可见",
    ".customfields.required":"自定义字段是否必填",
    ".customfields.sortorder":"自定义字段是否排序",
  }
}
```

### 获取自定义字段类型 -- GET admin/get_custom_param_type

- controller: ``app\admin\controller\TicketDepartmentController::getCustomParamType``
- desc: 获取自定义字段类型 -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加自定义字段 -- GET admin/add_ticket_custom_param

- controller: ``app\admin\controller\TicketDepartmentController::addTicketCustomParam``
- desc: 添加自定义字段 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| fieldname | 字符串 | 必填 | - | - | 字段名称 |
| fieldtype | 字符串 | 必填 | - | - | 字段类型 |
| description | 字符串 | 必填 | - | - | 字段描述 |
| ticketId | 整型 | 必填 | - | - | 工单ID |
| fieldoptions | 字符串 | 非必填 | - | - | 下拉选项内容，逗号隔开 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取修改自定义字段的值 -- GET admin/get_ticket_param_val

- controller: ``app\admin\controller\TicketDepartmentController::getTicketParamVal``
- desc: 获取修改自定义字段的值 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| fieldId | 整型 | 必填 | - | - | 自定义字段ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改自定义字段 -- GET admin/edit_ticket_custom_param

- controller: ``app\admin\controller\TicketDepartmentController::editTicketCustomParam``
- desc: 修改自定义字段 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| fieldname | 字符串 | 必填 | - | - | 字段名称 |
| fieldtype | 字符串 | 必填 | - | - | 字段类型 |
| description | 字符串 | 必填 | - | - | 字段描述 |
| ticketId | 整型 | 必填 | - | - | 工单ID |
| fieldoptions | 字符串 | 非必填 | - | - | 下拉选项内容，逗号隔开 |
| fieldId | 整型 | 必填 | - | - | 自定义字段ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除自定义字段 -- GET admin/del_ticket_custom_param

- controller: ``app\admin\controller\TicketDepartmentController::delTicketCustomParam``
- desc: 删除自定义字段 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| fieldId | 整型 | 必填 | - | - | 自定义字段ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台工单传递规则

### 添加新规则页面 -- GET admin/get_ticket_deliver

- controller: ``app\admin\controller\TicketDeliverController::addPage``
- desc: 添加新规则页面 -- xujin

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"部门id",
    "name":"部门名称",
  }
}
```

### 添加新规则 -- POST admin/add_ticket_deliver

- controller: ``app\admin\controller\TicketDeliverController::add``
- desc: 添加新规则 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| departments | 数组 | 必填 | - | - | 关联的部门 |
| products | 数组 | 必填 | - | - | 关联的产品 |
| is_open_auto_reply | 整型 | 非必填 | - | - | 开启自动回复 |
| bz | 字符串 | 非必填 | - | - | 自动回复内容 |
| mask_keywords | 字符串 | 非必填 | - | - | 屏蔽关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改传递规则 -- POST admin/save_ticket_deliver

- controller: ``app\admin\controller\TicketDeliverController::save``
- desc: 修改传递规则 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 规则id |
| departments | 数组 | 必填 | - | - | 关联的部门 |
| products | 数组 | 必填 | - | - | 关联的产品 |
| is_open_auto_reply | 整型 | 非必填 | - | - | 开启自动回复 |
| bz | 字符串 | 非必填 | - | - | 自动回复内容 |
| mask_keywords | 字符串 | 非必填 | - | - | 屏蔽关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除工单传递规则 -- POST admin/delete_ticket_deliver

- controller: ``app\admin\controller\TicketDeliverController::delete``
- desc: 删除工单传递规则 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 规则id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 工单传递规则列表 -- GET admin/list_ticket_deliver

- controller: ``app\admin\controller\TicketDeliverController::getList``
- desc: 工单传递规则列表 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"工单传递规则id",
    ".is_open_auto_reply":"自动回复(0",
    ".bz":"自动回复内容",
    ".mask_keywords":"屏蔽关键字",
    ".departments":"部门",
    ".products":"产品",
  }
}
```


---

## 后台工单状态

### 添加工单状态 -- POST admin/add_ticket_status

- controller: ``app\admin\controller\TicketStatusController::add``
- desc: 添加工单状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | - | - | 状态标题 |
| color | 字符串 | 非必填 | - | - | 颜色css代码 |
| show_active | 整型 | 非必填 | 0 | - | 包括打开的工单 |
| show_await | 整型 | 非必填 | 0 | - | 包括等待回复 |
| auto_close | 整型 | 非必填 | 0 | - | 确定自动关闭 |
| order | 整型 | 非必填 | - | 1 | 排序 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加工单状态 -- POST admin/save_ticket_status

- controller: ``app\admin\controller\TicketStatusController::save``
- desc: 添加工单状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 状态id |
| title | 字符串 | 必填 | - | - | 状态标题 |
| color | 字符串 | 非必填 | - | - | 颜色css代码 |
| show_active | 整型 | 非必填 | 0 | - | 包括打开的工单 |
| show_await | 整型 | 非必填 | 0 | - | 包括等待回复 |
| auto_close | 整型 | 非必填 | 0 | - | 确定自动关闭 |
| order | 整型 | 非必填 | - | 1 | 排序 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除工单状态 -- POST admin/delete_ticket_status

- controller: ``app\admin\controller\TicketStatusController::delete``
- desc: 删除工单状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单状态id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 工单状态列表 -- GET admin/list_ticket_status

- controller: ``app\admin\controller\TicketStatusController::getList``
- desc: 工单状态列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"工单状态id",
    ".title":"标题",
    ".color":"颜色",
    ".show_active":"包括打开的工单",
    ".show_await":"包括等待回复",
    ".auto_close":"自动关闭",
    ".order":"排序",
  }
}
```

### 工单状态详情 -- GET admin/list_ticket_status/:id

- controller: ``app\admin\controller\TicketStatusController::getDetail``
- desc: 工单状态详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 状态id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"工单状态id",
    ".title":"标题",
    ".color":"颜色",
    ".show_active":"包括打开的工单",
    ".show_await":"包括等待回复",
    ".auto_close":"自动关闭",
    ".order":"排序",
  }
}
```


---

## 后台预设回复

### 预设回复列表 -- GET admin/ticket_prereply_list

- controller: ``app\admin\controller\TicketPrereplyController::replyList``
- desc: 预设回复列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "prereply":[{//列表
      "id":"预设回复组ID",
      "name":"预设回复组名称",
      "child":[{//预设回复
        "id":"",
        "title":"标题",
        "content":"内容",
      }]
    }]
  }
}
```

### 添加预设回复分类 -- POST admin/add_ticket_prereply_category

- controller: ``app\admin\controller\TicketPrereplyController::addCategory``
- desc: 添加预设回复分类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 分类名称 |
| parent | 整型 | 非必填 | - | - | 上级分类id(顶级分类不用传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"新增分类id",
  }
}
```

### 编辑预设回复分类页面 -- GET admin/save_ticket_prereply_category/page

- controller: ``app\admin\controller\TicketPrereplyController::editCategoryPage``
- desc: 编辑预设回复分类页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分类ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "category":"新增分类id",
  }
}
```

### 编辑预设回复分类 -- POST admin/save_ticket_prereply_category

- controller: ``app\admin\controller\TicketPrereplyController::editCategory``
- desc: 编辑预设回复分类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分类ID |
| name | 整型 | 必填 | - | - | 分类名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除预设回复分类 -- GET admin/delete_ticket_prereply_category/:id

- controller: ``app\admin\controller\TicketPrereplyController::deleteCategory``
- desc: 删除预设回复分类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 添加预设回复页面 -- GET admin/add_ticket_prereply/page

- controller: ``app\admin\controller\TicketPrereplyController::addPrereplyPage``
- desc: 添加预设回复页面 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".categories.id":"分类id",
    ".categories.name":"分类名称",
  }
}
```

### 添加预设回复 -- POST admin/add_ticket_prereply

- controller: ``app\admin\controller\TicketPrereplyController::addPrereply``
- desc: 添加预设回复 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cid | 整型 | 必填 | - | - | 分类id |
| title | 字符串 | 必填 | - | - | 文章标题 |
| content | 字符串 | 必填 | - | - | 文章内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑预设回复页面 -- GET admin/save_ticket_prereply/page

- controller: ``app\admin\controller\TicketPrereplyController::savePrereplyPage``
- desc: 编辑预设回复页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 预设回复id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "categories":[{//分类
      "id":"分类id",
      "name":"分类名称",
    }]
    "list":[{//详情
      "id":"当前预设回复id",
      "cid":"当前预设回复分类id",
      "title":"当前预设回复标题",
      "content":"当前预设回复内容",
    }]
  }
}
```

### 编辑预设回复 -- POST admin/save_ticket_prereply

- controller: ``app\admin\controller\TicketPrereplyController::savePrereply``
- desc: 编辑预设回复 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 预设回复id |
| cid | 整型 | 非必填 | - | - | 分类id |
| title | 字符串 | 必填 | - | - | 文章标题 |
| content | 字符串 | 非必填 | - | - | 文章内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 搜索预设回复 -- POST admin/search_ticket_prereply

- controller: ``app\admin\controller\TicketPrereplyController::searchPrereply``
- desc: 搜索预设回复 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 非必填 | - | - | 搜索的标题 |
| content | 字符串 | 非必填 | - | - | 搜索的内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"预设回复id",
    ".title":"预设回复标题",
    ".content":"预设回复内容",
  }
}
```

### 删除预设回复 -- DELETE admin/ticket_prereply/:id/

- controller: ``app\admin\controller\TicketPrereplyController::deletePrereply``
- desc: 删除预设回复 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 非必填 | - | - | 预设回复ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台工单

### 新建工单页面 -- GET admin/add_ticket_page

- controller: ``app\admin\controller\TicketController::createPage``
- desc: 新建工单页面 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 整型 | 必填 | - | - | 工单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".client.id":"用户id",
    ".client.username":"用户名",
    ".client.email":"邮箱",
    ".client.companyname":"公司名",
    ".department.id":"部门id",
    ".department.name":"部门名称",
    ".custom_arr 自定义参数列表":"",
  }
}
```

### 新建工单 -- POST admin/add_ticket

- controller: ``app\admin\controller\TicketController::add``
- desc: 新建工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户id |
| name | 字符串 | 非必填 | - | - | 用户名称 |
| email | 字符串 | 非必填 | - | - | 邮箱地址 |
| send | 整型 | 非必填 | 0 | - | 是否发送邮件 |
| cc | 字符串 | 非必填 | - | - | 抄送收件人 |
| title | 字符串 | 必填 | - | - | 主题 |
| dptid | 整型 | 必填 | - | - | 部门id |
| priority | 字符串 | 非必填 | medium | - | 优先级high高,medium中,low低 |
| content | 字符串 | 必填 | - | - | 内容 |
| attachment | file&array | 非必填 | - | - | 附件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 工单列表 -- GET admin/list_ticket

- controller: ``app\admin\controller\TicketController::getList``
- desc: 工单列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 非必填 | - | - | tid |
| email | 字符串 | 非必填 | - | - | 邮件地址 |
| content | 字符串 | 非必填 | - | - | 主题/内容 |
| priority | 字符串 | 非必填 | all | - | 优先级 |
| dptid | 整型 | 非必填 | - | - | 部门id |
| uid | 整型 | 非必填 | - | - | 客户id |
| status | 字符串 | 非必填 | all | - | 状态 |
| limit | 整型 | 非必填 | 10 | - | 条数 |
| page | 整型 | 非必填 | 1 | - | 页数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".limit":"每页条数",
    ".page":"当前页数",
    ".sum":"总条数",
    ".max_page":"最大页数",
    ".list.id":"工单id",
    ".list.tid":"工单tid",
    ".list.uid":"发起工单的用户id",
    ".list.title":"工单标题",
    ".list.status":"工单状态",
    ".list.last_reply_time":"最后回复时间戳",
    ".list.flag_admin":"标记的管理员名称",
    ".list.department_name":"部门名称",
    ".list.user_name":"发起工单的用户名",
    ".list.format_time":"格式化的最后回复时间",
  }
}
```

### 获取客户工单列表 -- GET admin/client_ticket

- controller: ``app\admin\controller\TicketController::getClientTicketPage``
- desc: 获取客户工单列表 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| hostid | 整型 | 必填 | - | - | 产品id |
| order | 字符串 | 必填 | last_reply_time | - | 排序字段 |
| sort | 整型 | 必填 | DESC | - | 排序类型 |
| page | 整型 | 必填 | 1 | - | 页码 |
| limit | 整型 | 必填 | 10 | - | 分页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "ticket_data":[{//工单列表数据
      "id":"工单id",
      "tid":"工单号",
      "dptid":"部门id",
      "title":"工单名",
      "status":"状态",
      "create_time":"创建时间",
      "depart_name":"工单名",
      "last_replay":"上次回复",
    }]
    "opened_this_month":"这个月打开工单",
    "opened_last_month":"上个月打开工单",
    "opened_this_year":"今年打开工单",
    "opened_last_year":"去年打开工单",
  }
}
```

### 回复工单 -- POST admin/reply_ticket

- controller: ``app\admin\controller\TicketController::reply``
- desc: 回复工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |
| content | 字符串 | 必填 | - | - | 回复内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑工单回复 -- POST admin/save_ticket_reply

- controller: ``app\admin\controller\TicketController::saveReply``
- desc: 编辑工单回复 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单回复id|工单id |
| content | 整型 | 必填 | - | - | 新内容 |
| type | 字符串 | 必填 | - | - | 类型t工单,r回复 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 合并工单 -- POST admin/merge_ticket

- controller: ``app\admin\controller\TicketController::mergeTicket``
- desc: 合并工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 工单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修正工单 -- POST admin/close_ticket

- controller: ``app\admin\controller\TicketController::closeTicket``
- desc: 修正工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 工单id |
| status | 字符串 | 必填 | - | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除工单 -- POST admin/delete_ticket

- controller: ``app\admin\controller\TicketController::deleteTicket``
- desc: 删除工单 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 工单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加工单备注 -- POST admin/add_ticket_note

- controller: ``app\admin\controller\TicketController::addNote``
- desc: 添加工单备注 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |
| content | 字符串 | 必填 | - | - | 备注内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除工单备注 -- POST admin/delete_ticket_note

- controller: ``app\admin\controller\TicketController::deleteNote``
- desc: 删除工单备注 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单备注id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除工单回复 -- POST admin/delete_ticket_reply

- controller: ``app\admin\controller\TicketController::deleteReply``
- desc: 删除工单回复 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单备注id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除附件 -- POST admin/delete_ticket_attachment

- controller: ``app\admin\controller\TicketController::deleteAttachment``
- desc: 删除附件 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单回复id或工单备注id或工单id |
| type | 字符串 | 必填 | - | - | id类型,r工单回复,n工单备注,t工单 |
| index | 整型 | 必填 | - | - | 要删除附件的index |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 下载附件 -- GET admin/download_ticket_attachment

- controller: ``app\admin\controller\TicketController::downloadAttachment``
- desc: 下载附件 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单回复id或工单备注id或工单id |
| type | 字符串 | 必填 | - | - | id类型,r工单回复,n工单备注,t工单 |
| index | 整型 | 必填 | - | - | 要下载的附件的index |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取工单详情 -- GET admin/list_ticket/:id

- controller: ``app\admin\controller\TicketController::ticketDetail``
- desc: 获取工单详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".list.id":"工单id|回复id|备注id|工单转移日志id",
    ".list.mode":"工单转移方式(0",
    ".list.mode_zh":"工单转移方式(中文)",
    ".list.desc":"描述",
    ".list.remarks":"备注",
    ".list.from":"发出人",
    ".list.to":"接收人",
    ".list.type":"类型,t工单,r回复,n备注",
    ".list.content":"内容",
    ".list.attachment.name":"附件名称",
    ".list.attachment.img":"附件图片",
    ".list.format_time":"时间",
    ".list.user":"发出人",
    ".list.realname":"管理员真实姓名",
    ".list.user_type":"用户类型",
    ".list.star":"评价星级(只会在管理员回复有)",
    ".ticket.dptid":"工单部门id",
    ".ticket.dpt_name":"工单部门名称",
    ".ticket.title":"工单标题",
    ".ticket.status":"工单状态",
    ".ticket.cc":"抄送收件人",
    ".ticket.uid":"工单用户id",
    ".ticket.flag":"标记的管理员id",
    ".ticket.priority":"优先级",
    ".customfields.id":"自定义字段id",
    ".customfields.fieldname":"自定义字段名称",
    ".customfields.fieldtype":"自定义字段类型",
    ".customfields.description":"自定义字段描述",
    ".customfields.fieldoptions":"自定义字段选项",
    ".customfields.required":"自定义字段是否必填",
    ".customfields.value":"自定义字段值",
  }
}
```

### 工单信息获取产品 -- GET admin/ticket_detail_host

- controller: ``app\admin\controller\TicketController::getTicketDetailHost``
- desc: 工单信息获取产品 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段,username,create_time,gateway,description,amount_in,fees,amount_out |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| uid | 整型 | 必填 | - | - | 用户id |
| hostid | 整型 | 必填 | - | - | hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改工单信息 -- POST admin/save_ticket

- controller: ``app\admin\controller\TicketController::saveTicket``
- desc: 修改工单信息 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |
| dptid | 整型 | 非必填 | - | - | 部门id |
| title | 字符串 | 非必填 | - | - | 标题 |
| status | 字符串 | 非必填 | - | - | 状态 |
| cc | 字符串 | 非必填 | - | - | 抄送收件人 |
| uid | 整型 | 非必填 | - | - | 用户id |
| flag | 整型 | 非必填 | - | - | 标记的管理员id |
| customfield | 数组 | 非必填 | - | - | 自定义字段的值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 工单统计 -- GET admin/ticket_statistics

- controller: ``app\admin\controller\TicketController::ticketStatistics``
- desc: 工单统计 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段,ticket_count,ticket_star_count,ticket_star_sum,ticket_star_1,ticket_star_2,ticket_star_3,ticket_star_4,ticket_star_5 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| start | 整型 | 非必填 | - | - | 开始时间 |
| end | 整型 | 非必填 | - | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".limit":"每页条数",
    ".page":"当前页数",
    ".sum":"总条数",
    ".max_page":"最大页数",
    ".list.id":"管理员id",
    ".list.user_login":"管理员用户名",
    ".list.user_nickname":"管理员昵称",
    ".list.ticket_count":"工单总数",
    ".list.ticket_close":"已关闭工单数量",
    ".list.ticket_star_sum":"工单合计评分",
    ".list.ticket_star_1":"1分工单数量",
    ".list.ticket_star_2":"2分工单数量",
    ".list.ticket_star_3":"3分工单数量",
    ".list.ticket_star_4":"4分工单数量",
    ".list.ticket_star_5":"5分工单数量",
  }
}
```

### 工单接单 -- PUT admin/ticket_receive

- controller: ``app\admin\controller\TicketController::ticketReceive``
- desc: 工单接单 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取工单转移对象 -- GET admin/ticket_transfer_list

- controller: ``app\admin\controller\TicketController::ticketTransferList``
- desc: 获取工单转移对象 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 转移工单 -- PUT admin/ticket_transfer

- controller: ``app\admin\controller\TicketController::ticketTransfer``
- desc: 转移工单 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 工单id |
| mode | 整型 | 必填 | 0 | - | 转移方式0:指定处理人1:移动部门 |
| handle | 整型 | 非必填 | - | - | 处理人id |
| dptid | 整型 | 非必填 | - | - | 部门id |
| remarks | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\TicketController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\TicketController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\TicketController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\TicketController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\TicketController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\TicketController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\TicketController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\TicketController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台统计报表模块

### 年度收入统计 -- GET /admin/year_reports

- controller: ``app\admin\controller\ReportsController::getYearIncomeStatistics``
- desc: 年度收入统计 -- zhoufei

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "month_data":[{//月份统计数据列表
      "month":"月份值",
      "income":"收入",
      "expenses":"支出d",
      "last":"剩余",
    }]
    "all":[{//全年统计数据
      "income":"收入",
      "expenses":"支出",
      "last":"剩余",
    }]
  }
}
```

### 年度收入统计--图表数据 -- GET /admin/year_reports_chart

- controller: ``app\admin\controller\ReportsController::getYearIncomeStatisticsForChart``
- desc: 年度收入统计--图表数据 -- zhoufei

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "month_data":[{//月份统计数据列表
      "month":"月份值",
      "income":"收入",
      "expenses":"支出d",
      "last":"剩余",
    }]
    "all":[{//全年统计数据
      "income":"收入",
      "expenses":"支出",
      "last":"剩余",
    }]
  }
}
```

### 新客户统计 -- GET /admin/new_client

- controller: ``app\admin\controller\ReportsController::getNewClientStatistics``
- desc: 新客户统计 -- zhoufei

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| 日期 | 字符串 | 非必填 | 本年年月值，如2020,08 | - | 年月份值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "day_string":"日期",
    "new_clients_count":"新客户数",
    "new_order_count":"新订单数",
    "complete_order_count":"完成订单数",
    "new_ticket_count":"工单数",
    "reply_ticket_count":"回复工单数",
    "cancel_requests_count":"取消申请数",
  }
}
```

### 收入排名 -- GET /admin/forward_client

- controller: ``app\admin\controller\ReportsController::rankForwardClient``
- desc: 收入排名 -- zhoufei

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//统计数据列表
      "id":"客户id",
      "client_name":"客户名称",
      "company_name":"公司名称",
      "income_sum":"收入",
      "expense_sum":"支出",
      "last":"剩余",
    }]
  }
}
```

### 产品收入 -- GET /admin/product_income

- controller: ``app\admin\controller\ReportsController::productIncome``
- desc: 产品收入 -- zhoufei

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| year | number | 非必填 | 本年数字，如2020 | - | 年份值 |
| month | number | 非必填 | 本月数组，如8月：8 | - | 月份值 |
| search_type | 字符串 | 非必填 | product_group | - | 搜素类型：product_group(产品组)_product_name(产品名) |
| limit | 整型 | 非必填 | 10 | - | 条数 |
| page | 整型 | 非必填 | 1 | - | 页数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//统计数据列表
      "id":"产品组id/产品id",
      "name":"产品组名/产品名",
      "total_amount":"总金额",
      "new_order_amount":"新订购收入",
      "new_order_num":"新订购数量",
      "renew_order_amount":"续费收入",
      "renew_order_num":"续费数量",
    }]
  }
}
```


---

## 后台产品模块

### 产品服务列表页面 -- GET /admin/product_list_page

- controller: ``app\admin\controller\ProductController::getProuductlistPage``
- desc: 产品服务列表页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 整型 | 非必填 | - | - | keywords |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"产品组ID",
    "name":"产品组名称",
    "headline":"产品组标题",
    "tagline":"产品组标语",
    "order_frm_tpl":"该产品组的购买模板",
    "disabled_gateways":"隐藏的网关，以逗号分隔",
    "hidden":"是否隐藏",
    "order":"排序",
    "create_time":"创建时间",
    "update_time":"修改时间",
    "products":[{//产品信息
      "id":"产品ID",
      "gid":"产品组ID",
      "type":"产品类型",
      "pay_type":"产品周期",
      "qty":"库存",
      "auto_setup":"自动开通：order，下单后；payment：支付后；on：手动审核",
    }]
  }
}
```

### 一级分组排序修改 -- POST /admin/update_firstgroupsort

- controller: ``app\admin\controller\ProductController::updateFirstGroupsort``
- desc: 一级分组排序修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | 整型 | 非必填 | - | - | 组ID |
| pre_gid | 整型 | 非必填 | - | - | 移动后前一个gid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品分组排序修改 -- POST /admin/update_groupsort

- controller: ``app\admin\controller\ProductController::updateGroupsort``
- desc: 产品分组排序修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | - | - | 产品组ID |
| gid | 整型 | 非必填 | - | - | 一级组ID |
| pre_pid | 整型 | 非必填 | - | - | 移动后前一个产品组ID |
| current_gid | 整型 | 非必填 | - | - | 当前一级组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品排序修改 -- POST /admin/update_productsort

- controller: ``app\admin\controller\ProductController::updateProductsort``
- desc: 产品排序修改 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | - | - | 产品ID |
| gid | 整型 | 非必填 | - | - | 组ID |
| pre_pid | 整型 | 非必填 | - | - | 移动后前一个产品ID |
| current_gid | 整型 | 非必填 | - | - | 当前产品组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品分组添加页 -- GET /admin/edit_product_group_page

- controller: ``app\admin\controller\ProductController::editGroupPage``
- desc: 产品分组添加页 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 传递时会获取该组数据 |
| type | number | 必填 | - | - | 产品组分类1=通用2=裸金属3=魔方云 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "activeGatewayArr":[{//网关数据
    }]
    "allgateways":[{//已选择网关
    }]
    "firstGroups":[{//一级分组数据
    }]
    "default_page":"系统默认",
  }
}
```

### 保存产品分组信息 -- POST /admin/save_product_group

- controller: ``app\admin\controller\ProductController::saveProductGroup``
- desc: 保存产品分组信息 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 组ID，存在时修改，不存在时添加 |
| name | 字符串 | 必填 | - | - | 组名称 |
| headline | 字符串 | 非必填 | - | - | 产品组标题 |
| tagline | 字符串 | 非必填 | - | - | 产品组标语 |
| order_frm_tpl | 字符串 | 必填 | - | - | 订购表格模板：选默认，传返回的default_page的值 |
| gateways | 数组 | 必填 | - | - | 可用的付款接口（数组） |
| hidden | 字符串 | 必填 | 0 | - | 隐藏，on |
| type | 整型 | 必填 | 0 | - | 产品组分类1=通用产品2=裸金属 |
| gid | 整型 | 必填 | 0 | - | 一级分组ID |
| tpl_type | 字符串 | 必填 | 0 | - | 模板类型:default默认，custom自定义 |
| is_upstream | 字符串 | 非必填 | 0 | - | 是否上游资源 |
| zjfm_api_id | 字符串 | 非必填 | 0 | - | 接口id |
| is_resource | 字符串 | 非必填 | 0 | - | 是否资源池分组 |
| customfields | 数组 | 非必填 | 0 | - | 是否资源池分组(name,value的对象组成的数组) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 验证产品分组别名 -- POST check_product_as

- controller: ``app\admin\controller\ProductController::checkAlias``
- desc: 验证产品分组别名 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| alias | 字符串 | 必填 | - | - | 别名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 一级分组添加页 -- GET /admin/edit_product_first_group_page

- controller: ``app\admin\controller\ProductController::editFirstGroupPage``
- desc: 一级分组添加页 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 传递时会获取该组数据 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 保存一级分组信息 -- POST /admin/save_product_first_group

- controller: ``app\admin\controller\ProductController::saveProductFirstGroup``
- desc: 保存一级分组信息 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 组ID，存在时修改，不存在时添加 |
| name | 字符串 | 必填 | - | - | 组名称 |
| hidden | 字符串 | 必填 | 0 | - | 隐藏，on |
| is_upstream | 字符串 | 非必填 | 0 | - | 是否上游资源 |
| zjmf_api_id | 字符串 | 非必填 | 0 | - | 接口id |
| is_resource | 整型 | 非必填 | 0 | - | 是否资源池分组 |
| customfields | 数组 | 非必填 | 0 | - | 是否资源池分组(name,value的对象组成的数组) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除产品 -- GET /admin/del_product/:id

- controller: ``app\admin\controller\ProductController::delete``
- desc: 删除产品 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除产品组 -- GET /admin/del_product_group/:id

- controller: ``app\admin\controller\ProductController::deleteGroup``
- desc: 删除产品组 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 产品组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除一级组 -- GET /admin/del_product_first_group

- controller: ``app\admin\controller\ProductController::deleteFirstGroup``
- desc: 删除一级组 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 一级组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 复制产品页面 -- GET /admin/product_duplicate_page

- controller: ``app\admin\controller\ProductController::duplicatePage``
- desc: 复制产品页面 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//存在的产品数据
      "id":"产品id",
      "name_desc":"产品描述名",
    }]
  }
}
```

### 复制产品 -- GET /admin/product_duplicate

- controller: ``app\admin\controller\ProductController::duplicate``
- desc: 复制产品 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 必填 | - | - | 原产品id |
| newproductname | - | 必填 | - | - | 新产品名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "pid":"复制的产品ID",
  }
}
```

### 产品添加页面 -- GET /admin/add_product_page

- controller: ``app\admin\controller\ProductController::addPage``
- desc: 产品添加页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | number | 必填 | - | - | 产品组分类1=通用2=裸金属3=魔方云 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "groupdata":[{//产品组数据
      "id":"组ID",
      "name":"组名称",
    }]
    "type":"产品类型",
  }
}
```

### 创建产品 -- POST /admin/create_product

- controller: ``app\admin\controller\ProductController::create``
- desc: 创建产品 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | - | 必填 | - | - | 产品类型(hostingaccount，reselleraccount，server，other, |
| gid | number | 必填 | - | - | 组ID |
| productname | 字符串 | 必填 | - | - | 产品名称 |
| upstream_price_value | 字符串 | 非必填 | - | - | 利润百分比 |
| ptype | 字符串 | 非必填 | - | - | 导航类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取上游产品 -- GET /admin/get_upstream_products

- controller: ``app\admin\controller\ProductController::getUpstreamProducts``
- desc: 获取上游产品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 0 | desc:接口id | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "upstream_currency":"上游货币 USD",
    "currency":"本地货币 CNY",
    "rate":"建议汇率",
  }
}
```

### 同步上游产品信息 TODO 基础信息同步,上游产品信息变更 无法 定位具体某个字段改变,所以只要满足同步条件,就会覆盖产品的所有信息(包括之前用户自己修改的) -- POST /admin/product/sync_product_info

- controller: ``app\admin\controller\ProductController::syncProductInfo``
- desc: 同步上游产品信息 TODO 基础信息同步,上游产品信息变更 无法 定位具体某个字段改变,所以只要满足同步条件,就会覆盖产品的所有信息(包括之前用户自己修改的) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | - | - | 产品ID |
| upstream_pid | 整型 | 必填 | - | - | 上游产品ID |
| zjmf_finance_api_id | 整型 | 必填 | - | - | 魔方财务api |
| api_type | 整型 | 必填 | - | - | 接口类型::zjmf_api(魔方财务api),manual(手动)，normal(通用),resource(资源池) |
| rate | 浮点型 | 非必填 | - | - | 汇率 |
| upstream_price_type | 浮点型 | 非必填 | - | - | 价格方案 |
| upstream_price_value | 浮点型 | 非必填 | - | - | 百分比 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品编辑页面 -- GET /admin/edit_product_page/:id

- controller: ``app\admin\controller\ProductController::editPage``
- desc: 产品编辑页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | number | 必填 | - | - | 产品组分类1=通用2=裸金属3=魔方云 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//产品数据
      "id":"产品ID",
      "type":"产品类型",
      "gid":"产品组ID",
      "name":"产品名称",
      "description":"产品描述:支持",
      "hidden":"产品是否隐藏(0默认,1)",
      "welcome_email":"开通邮件模板ID",
      "stock_control":"库存控制（0不开启默认，1开启）",
      "qty":"库存",
      "tax":"是否缴税(0默认不，1是)",
      "pay_method":"支付方式（预付费prepayment/后付费postpaid）",
      "order":"排序",
      "retired":"下架（选中复选框从管理区产品下拉菜单中隐藏（不适用于已用于此产品的服务））",
      "is_featured":"特性（在支持的订单上更加突出的显示此产品）",
      "allow_qty":"",
      "auto_terminate_email":"",
      "server_group":"服务器组ID",
      "auto_setup":"购买后动作设置(无：手动开通，on：手动审核通过后自动开通，payment：当收到客户首付款时自动开通，order：当客户下单之后（未付款）立即自动开通)",
      "config_options_upgrade":"是否升级可配置选项",
      "upgrade_email":"升级邮件ID",
      "24":"",
      "affiliateonetime":"推介计划：1：一次性支付（默认为循环支付）",
      "affiliate_pay_type":"自定义佣金设置",
      "affiliate_pay_amount":"金额/百分比",
    }]
    "welcomeemail":"开通邮件列表",
    "product_paytype":"付款类型",
    "pricing":[{//价格数组
      "currency":"货币ID",
      "osetupfee":"周期一次性初装费",
      "hsetupfee":"周期小时初装费",
      "dsetupfee":"周期天初装费",
      "ontrialfee":"试用初装费",
      "msetupfee":"月初装费",
      "qsetupfee":"季初装费",
      "ssetupfee":"半年初装费",
      "asetupfee":"年初装费",
      "bsetupfee":"两年初装费",
      "tsetupfee":"三年初装费",
      "foursetupfee":"四年初装费",
      "fivesetupfee":"五年初装费",
      "sixsetupfee":"六年初装费",
      "sevensetupfee":"七年初装费",
      "eightsetupfee":"八年初装费",
      "ninesetupfee":"九年初装费",
      "tensetupfee":"十年初装费",
      "onetime":"一次性费用",
      "hour":"每小时费用",
      "day":"每天费用",
      "ontrial":"试用费用",
      "monthly":"每月费用",
      "quarterly":"每季度费用",
      "semiannually":"每半年费用",
      "annually":"每一年费用",
      "biennially":"每两年费用",
      "triennially":"每三年费用",
      "fourly":"每四年费用",
      "fively":"每五年费用",
      "sixly":"每六年费用",
      "sevenly":"每七年费用",
      "eightly":"每八年费用",
      "ninely":"每九年费用",
      "tenly":"每十年费用",
    }]
    "currencies":[{//当前系统设置的货币
      "id":"货币ID",
      "code":"货币标识",
    }]
    "product_pay_type":[{//支持的付款类型方式
      "pay_type":"free,onetime,recurring",
      "pay_hour_status":"按小时计费",
      "pay_hour_cycle":"按小时计费的付费周期",
      "pay_day_status":"按天计费",
      "pay_day_cycle":"按天计费付费周期",
      "pay_ontrial_status":"按小时计费",
      "pay_ontrial_cycle":"按天计费付费周期",
      "pay_ontrial_num":"试用数量：客户购买此产品周期为试用时的最大购买数量",
      "pay_ontrial_condition":[{//试用条件
        "realname":"是否需要实名认证",
        "wechat":"是否需要微信绑定",
        "phone":"是否需要手机绑定",
        "email":"是否需要邮箱绑定",
      }]
    }]
    "autoterminateemail":"删除邮件列表",
    "server_group":[{//服务器组数据
      "id":"组id",
      "name":"组名称",
      "type":"组模块类型",
    }]
    "customfields_type":[{//自定义数据类型（text，link，password，dropdown，tickbox，textarea）
    }]
    "customfields":[{//自定义字段数据
      "id":"自定义字段id",
      "fieldname":"自定义字段标题",
      "fieldtype":"自定义字段类型",
      "description":"自定义字段描述",
      "fieldoptions":"自定义字段选项，为dropdown时使用",
      "regexpr":"验证数据",
      "adminonly":"是否管理员可见",
      "required":"是否必填",
      "showorder":"是否在订单上显示",
      "showinvoice":"是否在账单上显示",
      "sortorder":"排序字段",
      "showdetail":"是否在产品内页显示",
    }]
    "config_links":[{//选中(分配)的选项组
      "gid":"组id",
    }]
    "config_groups":[{//可配置选项组数据（基础数据）
      "id":"配置组id",
      "name_desc":"展示描述信息",
    }]
    "all_product_data":[{//用于可升级选项的基础数据
      "id":"产品id",
      "name_desc":"产品展示名称",
    }]
    "upgrade_product_ids":"选中的升级产品id，一维数组",
    "upgradeemail":"升级邮件列表()",
    "custom_brokerage":"自定义佣金（percentage：百分比，fixed：固定数额，none：无）默认根据推广设置。为空",
    "download_files":[{//关联的下载数组
      "id":"文件id",
      "title":"显示文件名",
    }]
    "hierarchy_cats":[{//下载文件的分层数据
      "id":"分类id",
      "name":"分类名称",
      "files":[{//存在的可用文件
        "id":"文件id",
        "title":"文件名",
      }]
      "child":[{//子分类
        "id":"分类id",
        "name":"分类名称",
        "files":"存在的可用文件",
      }]
    }]
    "api_type":"接口类型",
  }
}
```

### 保存产品信息 -- POST /admin/edit_product

- controller: ``app\admin\controller\ProductController::edit``
- desc: 保存产品信息 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |
| afpid | 整型 | 必填 | - | - | 产品推荐配置ID |
| type | 字符串 | 必填 | 1 | - | 服务器类型：server,reselleraccount,hostingaccount,other |
| gid | 整型 | 必填 | - | - | 组ID |
| name | 字符串 | 必填 | - | - | 产品名称 |
| groupid | 字符串 | 非必填 | - | - | 显示分类 |
| description | 字符串 | 非必填 | - | - | 产品描述信息 |
| welcome_email | 整型 | 非必填 | - | - | 产品开通邮件 |
| hidden | 整型 | 非必填 | null | - | 是否隐藏产品 |
| retired | 整型 | 非必填 | null | - | 是否下架产品 |
| is_featured | 整型 | 非必填 | null | - | 是否突出显示 |
| stock_control | 字符串 | 非必填 | - | - | 是否控制库存，库存控制(1:启用)默认0 |
| qty | 整型 | 非必填 | - | - | 产品库存 |
| allow_qty | 字符串 | 非必填 | - | - | 选中复选框，如果客户在购买时，订购产品超过1个时，则允许客户自行指定（不需要单独配置）。 |
| prorata_billing | 整型 | 非必填 | - | - | 自定义结算日期(1:启用)，字段弃用 |
| prorata_date | 整型 | 非必填 | - | - | 结算日期(输入您希望从每月的几号开始结算费用)，字段弃用 |
| prorata_charge_next_month | 整型 | 非必填 | - | - | 下月结算(输入从每月几号后订购的产品，将安排在下个月的账单中一起收费)，字段弃用 |
| clientscount | 整型 | 非必填 | - | - | clientscount单个客户购买此产品的数量 |
| pay_type | 字符串 | 非必填 | - | - | 免费free，一次onetime，周期recurring（此字段与按小时和按天和试用必须有一个需要选中） |
| pay_hour_status | 字符串 | 非必填 | - | - | 是否支持按小时计费，on |
| pay_hour_cycle | 整型 | 必填 | - | - | 按小时计费的结算周期，小时 |
| pay_day_status | 字符串 | 非必填 | - | - | 是否支持按天计费，on |
| pay_day_cycle | 整型 | 非必填 | - | - | 按天计费的周期 |
| pay_ontrial_status | 字符串 | 非必填 | - | - | 是否支持试用，on |
| pay_ontrial_cycle | 整型 | 非必填 | - | - | 试用的时间，按小时为单位 |
| pay_ontrial_condition | 数组 | 非必填 | - | - | 试用的条件: |
| pay_ontrial_num | 数组 | 非必填 | - | - | 试用数量 |
| pay_ontrial_cycle_type | 数组 | 非必填 | - | - | 试用时长单位 |
| pay_method | 字符串 | 必填 | prepayment | - | 预付费/后付费 |
| server_type | 字符串 | 非必填 | - | - | 产品模块名 |
| server_group | 整型 | 非必填 | - | - | 产品服务器组ID |
| packageconfigoption[1]-[24] | 数组 | 非必填 | - | - | 产品配置数据 |
| auto_setup | 字符串 | 非必填 | - | - | 无：手动开通，on：手动审核通过后自动开通，payment：当收到客户首付款时自动开通，order：当客户下单之后（未付款）立即自动开通 |
| recurring_cycles | 整型 | 非必填 | - | - | 循环周期限制(弃用) |
| auto_terminate_days | 整型 | 非必填 | - | - | 自动删除/固定周期（弃用） |
| auto_terminate_email | 整型 | 非必填 | - | - | 产品删除邮件配置 |
| config_options_upgrade | 整型 | 非必填 | - | - | 产品是否允许升级可配置选项 |
| upgradeemail | 整型 | 非必填 | - | - | 升级邮件配置 |
| addfieldname | 字符串 | 非必填 | - | - | 添加的字段名称 |
| addfieldtype | 字符串 | 非必填 | dropdown | - | 添加的字段类型 |
| addcustomfielddesc | 字符串 | 非必填 | - | - | 添加的字段描述 |
| addfieldoptions | 字符串 | 非必填 | - | - | 添加字段的选项 |
| addregexpr | 字符串 | 非必填 | - | - | 该字段的正则匹配 |
| addadminonly | 字符串 | 非必填 | - | - | 选中为仅管理员可见 |
| addrequired | 字符串 | 非必填 | - | - | 该字段必填，值为on时 |
| addshoworder | 字符串 | 非必填 | - | - | 在订单上显示，值为on时 |
| addshowinvoice | 字符串 | 非必填 | - | - | 在账单上显示，值为on时 |
| addsortorder | 整型 | 非必填 | - | - | 排序数值 |
| addshowdetail | 整型 | 非必填 | - | - | 在产品内页显示，值为on时 |
| customfieldname | 数组 | 非必填 | - | - | 修改的字段名称 |
| customfieldtype | 数组 | 非必填 | dropdown | - | 修改的字段类型 |
| customfielddesc | 数组 | 非必填 | - | - | 修改的字段描述 |
| customfieldoptions | 数组 | 非必填 | - | - | 修改的字段的选项 |
| customfieldregexpr | 数组 | 非必填 | - | - | 修改的字段的正则匹配 |
| customadminonly | 数组 | 非必填 | - | - | 修改选中为仅管理员可见 |
| customrequired | 数组 | 非必填 | - | - | 修改该字段必填，值为on时 |
| customshoworder | 数组 | 非必填 | - | - | 修改在订单上显示，值为on时 |
| customshowinvoice | 数组 | 非必填 | - | - | 修改在账单上显示，值为on时 |
| customshowdetail | 数组 | 非必填 | - | - | 修改在产品内页显示，值为on时 |
| customsortorder | 数组 | 非必填 | - | - | 修改排序数值 |
| configoptionlinks | 数组 | 非必填 | - | - | 关联的可配置选项，一维数组，值为int型 |
| upgradepackages | 数组 | 非必填 | - | - | 可升级更改产品的数组，一维数组，值为int型 |
| currency | 数组 | 非必填 | - | - | 价格配置currency[货币id][周期/初装],如果用户没有输入需要传递-1.00 |
| currency[1][onetime] | 浮点型 | 必填 | - | - | 1为货币id，一次性价格 |
| currency[1][hour] | 浮点型 | 必填 | - | - | 小时价格 |
| currency[1][day] | 浮点型 | 必填 | - | - | 按天价格 |
| currency[1][ontrial] | 浮点型 | 必填 | - | - | 试用小时价格 |
| currency[1][monthly] | 浮点型 | 必填 | - | - | 月付价格 |
| currency[1][quarterly] | 浮点型 | 必填 | - | - | 季付价格 |
| currency[1][semiannually] | 浮点型 | 必填 | - | - | 半年付价格 |
| currency[1][annually] | 浮点型 | 必填 | - | - | 年付价格 |
| currency[1][biennially] | 浮点型 | 必填 | - | - | 两年 |
| currency[1][triennially] | 浮点型 | 必填 | - | - | 三年 |
| currency[1][fourly] | 浮点型 | 必填 | - | - | 四年 |
| currency[1][fively] | 浮点型 | 必填 | - | - | 五年 |
| currency[1][sixly] | 浮点型 | 必填 | - | - | 六年 |
| currency[1][sevenly] | 浮点型 | 必填 | - | - | 七年 |
| currency[1][eightly] | 浮点型 | 必填 | - | - | 八年 |
| currency[1][ninely] | 浮点型 | 必填 | - | - | 九年 |
| currency[1][tenly] | 浮点型 | 必填 | - | - | 十年 |
| currency[1][onetime] | 浮点型 | 必填 | - | - | 1为货币id，一次性初装价格 |
| currency[1][hsetupfee] | 浮点型 | 必填 | - | - | 小时初装价格 |
| currency[1][dsetupfee] | 浮点型 | 必填 | - | - | 天初装价格 |
| currency[1][ontrialfee] | 浮点型 | 必填 | - | - | 试用小时初装价格 |
| currency[1][msetupfee] | 浮点型 | 必填 | - | - | 月付初装价格 |
| currency[1][qsetupfee] | 浮点型 | 必填 | - | - | 季付初装价格 |
| currency[1][ssetupfee] | 浮点型 | 必填 | - | - | 半年付初装价格 |
| currency[1][asetupfee] | 浮点型 | 必填 | - | - | 年付初装价格 |
| currency[1][bsetupfee] | 浮点型 | 必填 | - | - | 两年初装价格 |
| currency[1][tsetupfee] | 浮点型 | 必填 | - | - | 三年初装价格 |
| currency[1][foursetupfee] | 浮点型 | 必填 | - | - | 四年初装价格 |
| currency[1][fivesetupfee] | 浮点型 | 必填 | - | - | 五年初装价格 |
| currency[1][sixsetupfee] | 浮点型 | 必填 | - | - | 六年初装价格 |
| currency[1][sevensetupfee] | 浮点型 | 必填 | - | - | 七年初装价格 |
| currency[1][eightsetupfee] | 浮点型 | 必填 | - | - | 八年初装价格 |
| currency[1][ninesetupfee] | 浮点型 | 必填 | - | - | 九年初装价格 |
| currency[1][tensetupfee] | 浮点型 | 必填 | - | - | 十年初装价格 |
| affiliate_pay_type | 字符串 | 必填 | 0 | - | 默认,百分比,percentage,固定数额,fixed,无佣金,none |
| affiliate_pay_amount | 字符串 | 必填 | 0.00 | - | 推介支付金额 |
| affiliateonetime | 字符串 | 非必填 | 0 | - | 一次性支付（默认为循环支付）,选中为1 |
| host_show | 字符串 | 非必填 | 0 | - | 主机名显示 |
| host_modify | 字符串 | 非必填 | 0 | - | 主机名修改 |
| host_prefix | 字符串 | 非必填 | 0 | - | 主机名前缀 |
| host_rule_upper | 字符串 | 非必填 | 0 | - | 主机名大写 |
| host_rule_lower | 字符串 | 非必填 | 0 | - | 主机名小写 |
| host_rule_num | 字符串 | 非必填 | 0 | - | 主机名数字 |
| host_rule_len_num | 字符串 | 非必填 | 0 | - | 主机名长度 |
| password_show | 字符串 | 非必填 | 0 | - | 密码显示 |
| password_modify | 字符串 | 非必填 | 0 | - | 密码修改 |
| password_rule_len_num | 字符串 | 非必填 | 0 | - | 密码长度 |
| password_rule_upper | 字符串 | 非必填 | 0 | - | 密码大写 |
| password_rule_lower | 字符串 | 非必填 | 0 | - | 密码小写 |
| password_rule_num | 字符串 | 非必填 | 0 | - | 密码数字 |
| password_rule_special | 字符串 | 非必填 | 0 | - | 密码特殊字符 |
| is_truename | 整型 | 非必填 | 0 | - | 是否开启实名 |
| is_truename | 整型 | 非必填 | 0 | - | 是否开启绑定手机 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 保存产品库存 -- POST /admin/edit_stock

- controller: ``app\admin\controller\ProductController::editStock``
- desc: 保存产品库存 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |
| qty | 整型 | 必填 | - | - | 产品库存 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取上游产品成本价 -- GET /admin/product/get_upstream_price

- controller: ``app\admin\controller\ProductController::getUpstreamPrice``
- desc: 获取上游产品成本价 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "flag":[{//折扣
      "bates":"折扣type:1百分比、2固定金额",
    }]
  }
}
```

### 选择类型 -- POST /admin/product/select_type

- controller: ``app\admin\controller\ProductController::selectType``
- desc: 选择类型 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 非必填 | - | - | type |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除自定义字段 -- POST /admin/product_del_custom

- controller: ``app\admin\controller\ProductController::delCustomField``
- desc: 删除自定义字段 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 自定义字段id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关联相关下载 -- POST /admin/product_manage_downloads

- controller: ``app\admin\controller\ProductController::managedownloads``
- desc: 关联相关下载 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 产品ID |
| adddl | 整型 | 非必填 | - | - | 添加关联ID |
| remdl | 整型 | 非必填 | - | - | 删除关联ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 返回可用文件列表 -- GET /admin/product_selectcates

- controller: ``app\admin\controller\ProductController::selectcates``
- desc: 返回可用文件列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| productid | 整型 | 非必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "cate_data":[{//分类数据
      "id":"分类idname:分类名称description:分类描述file_count:该分类下共有多少个可下载文件",
    }]
    "downloads":[{//downloads下载
      "id":"文件id",
      "title":"文件id",
      "description":"文件描述",
      "downloads":"下载数",
      "down_link":"下载链接",
    }]
  }
}
```

### 返回文件下载列表 -- GET /admin/product_downloadcates

- controller: ``app\admin\controller\ProductController::downloadcates``
- desc: 返回文件下载列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| productid | 整型 | 非必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "cate_data":[{//分类数据
      "id":"分类idname:分类名称description:分类描述file_count:该分类下共有多少个可下载文件",
    }]
    "downloads":[{//downloads下载
      "id":"文件id",
      "title":"文件id",
      "description":"文件描述",
      "downloads":"下载数",
      "down_link":"下载链接",
    }]
  }
}
```

### 添加分类 -- POST /admin/product_downloadcats

- controller: ``app\admin\controller\ProductController::addDownloadcats``
- desc: 添加分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| catid | 整型 | 非必填 | - | - | 父ID |
| title | 字符串 | 非必填 | - | - | 标题，名称 |
| description | 整型 | 非必填 | - | - | 该下载分组的描述 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加文件,同时关联到产品中 -- POST /admin/product_add_downloadflie

- controller: ``app\admin\controller\ProductController::addDownloadFlie``
- desc: 添加文件,同时关联到产品中 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uploadfile | file | 必填 | - | - | 上传的文件(单个文件) |
| id | 整型 | 必填 | - | - | 产品ID |
| catid | 整型 | 必填 | - | - | 分类id，不能为0，不能将文件添加到顶级分类 |
| title | 整型 | 必填 | - | - | 文件标题/名称 |
| description | 整型 | 非必填 | - | - | 文件描述信息 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品分组列表页 -- GET /admin/product/productgroup

- controller: ``app\admin\controller\ProductController::groupList``
- desc: 产品分组列表页 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| group_name | 字符串 | 非必填 | 1 | - | 按分组名搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"列表总数",
    "list":[{//列表表数据
      "group_name":"分组名",
      "pids":"产品组列表",
    }]
  }
}
```

### 添加分组页面 -- GET /admin/product/add_productgrouppage

- controller: ``app\admin\controller\ProductController::addProductgroupPage``
- desc: 添加分组页面 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "group":[{//产品组
      "id":"组id",
      "name":"组名",
      "product":[{//产品
        "id":"产品id",
        "type":"类型",
        "gid":"组id",
        "name":"产品名",
        "description":"描述",
        "pay_method":"付款类型",
        "tax":"税",
      }]
    }]
  }
}
```

### 添加分组 -- POST /admin/product/add_productgroup

- controller: ``app\admin\controller\ProductController::addProductgroup``
- desc: 添加分组 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| group_name | 字符串 | 必填 | 1 | - | 分组名称 |
| pids | 浮点型 | 必填 | 1 | - | 产品集(1,2,3) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑分组页面 -- GET /admin/product/edit_productgrouppage

- controller: ``app\admin\controller\ProductController::editProductgroupPage``
- desc: 编辑分组页面 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "group":[{//产品组
      "id":"组id",
      "name":"组名",
      "product":[{//产品
        "id":"产品id",
        "type":"类型",
        "gid":"组id",
        "name":"产品名",
        "description":"描述",
        "pay_method":"付款类型",
        "tax":"税",
      }]
    }]
    "spg":[{//分组
      "id":"组id",
      "groupname":"组名",
      "pids":[{//产品集(1,2,3)
      }]
    }]
  }
}
```

### 编辑分组 -- POST /admin/product/edit_productgroup

- controller: ``app\admin\controller\ProductController::editProductgroup``
- desc: 编辑分组 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |
| group_name | 字符串 | 必填 | 1 | - | 分组名称 |
| pids | 数组 | 必填 | 1 | - | 产品集(1,2,3) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分组 -- GET /admin/product/del_productgroup

- controller: ``app\admin\controller\ProductController::delProductgroup``
- desc: 删除分组 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 折扣列表 -- GET /admin/product/zklist_page

- controller: ``app\admin\controller\ProductController::zklistPage``
- desc: 折扣列表 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//产品用户组
      "id":"用户组ID",
      "group_name":"用户分组名称",
    }]
    "child":[{//产品组项
      "group_name":"产品分组名称",
      "type":"1折扣2固定金额3优惠",
      "bates":"数值",
    }]
  }
}
```

### 编辑客户产品分组的金额 -- POST /admin/product/edit_userproductgroup

- controller: ``app\admin\controller\ProductController::editUserProductgroup``
- desc: 编辑客户产品分组的金额 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分组ID |
| type | 整型 | 必填 | 1 | - | 类型1折扣2固定金额 |
| bates | 浮点型 | 必填 | 1 | - | 数值 |
| products | 整型 | 必填 | 1 | - | 产品组id |
| user | 整型 | 必填 | 1 | - | 客户组id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台自动任务

### 自动任务设置 -- GET admin/cron_page

- controller: ``app\admin\controller\CronController::detail``
- desc: 自动任务设置 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".cron_command":"CronCommand",
    ".cron_day_start_time":"每天定时任务开始时间",
    ".cron_host_suspend":"是否启用暂停功能",
    ".cron_host_suspend_time":"暂停时间(天)",
    ".cron_host_suspend_send":"是否发送暂停邮件",
    ".cron_host_unsuspend":"是否启用解除暂停",
    ".cron_host_unsuspend_send":"是否发送解除暂停邮件",
    ".cron_host_terminate":"是否启用删除",
    ".cron_host_terminate_time":"删除时间",
    ".cron_invoice_create_default_days":"生成到期账单前天数",
    ".cron_invoice_create_hour":"小时付",
    ".cron_invoice_create_day":"天付",
    ".cron_invoice_create_monthly":"月付",
    ".cron_invoice_create_quarterly":"季付",
    ".cron_invoice_create_semiannually":"半年付",
    ".cron_invoice_create_annually":"年付",
    ".cron_invoice_create_biennially":"两年付",
    ".cron_invoice_create_triennially":"三年付",
    ".cron_invoice_create_fourly":"四年付",
    ".cron_invoice_create_fively":"五年付",
    ".cron_invoice_create_sixly":"六年付",
    ".cron_invoice_create_sevenly":"七年付",
    ".cron_invoice_create_eightly":"八年付",
    ".cron_invoice_create_ninely":"九年付",
    ".cron_invoice_create_tenly":"十年付",
    ".cron_invoice_pay_email":"付款提醒邮件",
    ".cron_invoice_unpaid_email":"账单未付款提醒",
    ".cron_invoice_first_overdue_email":"第1次逾期提醒",
    ".cron_invoice_second_overdue_email":"第2次逾期提醒",
    ".cron_invoice_third_overdue_email":"第3次逾期提醒",
    ".cron_ticket_close_time":"关闭工单时间",
    ".cron_client_delete":"自动删除不活跃用户",
    ".cron_client_delete_time":"不活跃月份",
    ".cron_other_cancel_request":"取消服务请求",
    ".cron_other_client_update":"客户状态更新,0,1,2",
    ".cron_last_run_time":"上次自动任务执行开始时间",
    ".cron_last_run_time_over":"上次自动任务执行结束时间",
    ".diff_run_time":"自动任务执行了多久",
    ".cron_host_terminate_high":"是否开启产品删除功能高级设置",
    ".cron_host_terminate_time_hostingaccount":"虚拟主机删除时间",
    ".cron_host_terminate_time_server":"独立服务器删除时间",
    ".cron_host_terminate_time_cloud":"云服务器删除时间",
    ".cron_host_terminate_time_dcimcloud":"魔方云删除时间",
    ".cron_host_terminate_time_dcim":"魔方裸金属删除时间",
    ".cron_host_terminate_time_software":"软件产品删除时间",
    ".cron_host_terminate_time_cdn":"CDN删除时间",
    ".cron_host_terminate_time_other":"其他服务删除时间",
    ".cron_credit_limit_suspend_time":"暂停时间(信用额)",
    ".cron_credit_limit_invoice_unpaid_email":"账单未付款提醒(信用额)",
    ".cron_credit_limit_invoice_first_overdue_email":"第1次逾期提醒(信用额)",
    ".cron_credit_limit_invoice_second_overdue_email":"第2次逾期提醒(信用额)",
    ".cron_credit_limit_invoice_third_overdue_email":"第3次逾期提醒(信用额)",
  }
}
```

### 保存自动任务 -- POST /admin/save_cron

- controller: ``app\admin\controller\CronController::saveCron``
- desc: 保存自动任务 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cron_day_start_time | 整型 | 非必填 | - | - | 每天定时任务开始时间0-23对应0点到23点 |
| cron_host_suspend | 整型 | 非必填 | - | - | 是否启用暂停功能,0禁用1启用 |
| cron_host_suspend_time | 整型 | 非必填 | - | - | 暂停时间 |
| cron_host_suspend_send | 整型 | 非必填 | - | - | 是否发送暂停邮件,0禁用1启用 |
| cron_host_unsuspend | 整型 | 非必填 | - | - | 是否启用解除暂停,0禁用1启用 |
| cron_host_unsuspend_send | 整型 | 非必填 | - | - | 是否发送解除暂停邮件,0禁用1启用 |
| cron_host_unsuspend_send | 整型 | 非必填 | - | - | 是否发送解除暂停邮件,0禁用1启用 |
| cron_host_terminate | 整型 | 非必填 | - | - | 是否启用删除,0禁用1启用 |
| cron_host_terminate_time | 整型 | 非必填 | - | - | 删除时间 |
| cron_invoice_create_default_days | 字符串 | 非必填 | - | - | 生成到期账单前天数 |
| cron_invoice_create_hour | 字符串 | 非必填 | - | - | 小时付 |
| cron_invoice_create_day | 字符串 | 非必填 | - | - | 天付 |
| cron_invoice_create_monthly | 字符串 | 非必填 | - | - | 月付 |
| cron_invoice_create_quarterly | 字符串 | 非必填 | - | - | 季付 |
| cron_invoice_create_semiannually | 字符串 | 非必填 | - | - | 半年付 |
| cron_invoice_create_annually | 字符串 | 非必填 | - | - | 年付 |
| cron_invoice_create_biennially | 字符串 | 非必填 | - | - | 两年付 |
| cron_invoice_create_triennially | 字符串 | 非必填 | - | - | 三年付 |
| cron_invoice_create_fourly | 字符串 | 非必填 | - | - | 四年付 |
| cron_invoice_create_fively | 字符串 | 非必填 | - | - | 五年付 |
| cron_invoice_create_sixly | 字符串 | 非必填 | - | - | 六年付 |
| cron_invoice_create_sevenly | 字符串 | 非必填 | - | - | 七年付 |
| cron_invoice_create_eightly | 字符串 | 非必填 | - | - | 八年付 |
| cron_invoice_create_ninely | 字符串 | 非必填 | - | - | 九年付 |
| cron_invoice_create_tenly | 字符串 | 非必填 | - | - | 十年付 |
| cron_invoice_pay_email | 整型 | 非必填 | - | - | 付款提醒邮件 |
| cron_invoice_unpaid_email | 整型 | 非必填 | - | - | 账单未付款提醒 |
| cron_invoice_first_overdue_email | 整型 | 非必填 | - | - | 第1次逾期提醒 |
| cron_invoice_second_overdue_email | 整型 | 非必填 | - | - | 第2次逾期提醒 |
| cron_invoice_third_overdue_email | 整型 | 非必填 | - | - | 第3次逾期提醒 |
| cron_ticket_close_time | 整型 | 非必填 | - | - | 关闭工单时间 |
| cron_client_delete | 整型 | 非必填 | - | - | 自动删除不活跃用户 |
| cron_client_delete_time | 整型 | 非必填 | - | - | 不活跃月份 |
| cron_other_cancel_request | 整型 | 非必填 | - | - | 取消服务请求 |
| cron_other_client_update | 整型 | 非必填 | - | - | 客户状态更新,0,1,2 |
| cron_order_unpaid_time_high | 整型 | 非必填 | - | - | 是否开启删除取消功能,0禁用1启用 |
| cron_order_unpaid_time | 整型 | 非必填 | - | - | 订单未付款多少天后删除或取消 |
| cron_order_unpaid_action | 整型 | 非必填 | - | - | 订单未付款多少天后动作,取消或者删除(Delete,Cancelled) |
| cron_credit_limit_suspend_time | 整型 | 非必填 | - | - | 暂停时间(信用额) |
| cron_credit_limit_invoice_unpaid_email | 整型 | 非必填 | - | - | 账单未付款提醒(信用额) |
| cron_credit_limit_invoice_first_overdue_email | 整型 | 非必填 | - | - | 第1次逾期提醒(信用额) |
| cron_credit_limit_invoice_second_overdue_email | 整型 | 非必填 | - | - | 第2次逾期提醒(信用额) |
| cron_credit_limit_invoice_third_overdue_email | 整型 | 非必填 | - | - | 第3次逾期提醒(信用额) |
| cron_recharge_invoice_unpaid_delete | 整型 | 非必填 | - | - | 是否开启自动删除未支付的充值账单功能,0禁用1启用 |
| cron_recharge_invoice_unpaid_delete_time | 整型 | 非必填 | - | - | 未支付的充值账单多少天后删除 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 文档下载

### 文档下载分类页数据 -- GET admin/downloads/list

- controller: ``app\admin\controller\DownloadsController::getList``
- desc: 文档下载分类页数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 分类id，不传递时为顶级分类数据 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "files":"[],空数组，顶级菜单没有文件",
    "level_data":[{//分类数据
      "id":"产品类型",
      "parentid":"父id,0",
      "name":"分类名",
      "description":"分类描述",
      "hidden":"0：显示,1：隐藏",
      "number_of_files":"产品组ID",
    }]
  }
}
```

### 文档下载添加分类 -- POST admin/downloads/create

- controller: ``app\admin\controller\DownloadsController::postCreate``
- desc: 文档下载添加分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 父ID，当前页面分类id，为0时为顶级分类 |
| name | 字符串 | 必填 | - | - | 分类名称 |
| description | 字符串 | 非必填 | - | - | 分类描述 |
| hidden | number | 非必填 | - | - | 是否隐藏,1:隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 文档下载编辑分类页面 -- GET admin/downloadss/edit/:id

- controller: ``app\admin\controller\DownloadsController::getEdit``
- desc: 文档下载编辑分类页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "cats_data":[{//当前分类数据
      "id":"分类id",
      "parentid":"父类id(与分类列表关联显示)",
      "name":"分类名称",
      "description":"分类描述",
      "hidden":"是否隐藏，1(隐藏)",
    }]
    "all_cats_data":[{//可选上级分类
      "id":"分类id",
      "name":"分类名",
    }]
  }
}
```

### 编辑分类保存数据 -- POST admin/downloads/update

- controller: ``app\admin\controller\DownloadsController::postUpdate``
- desc: 编辑分类保存数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 分类id |
| parentcategory | number | 必填 | - | - | 父id |
| name | 字符串 | 必填 | - | - | 分类名称 |
| description | 字符串 | 非必填 | - | - | 分类描述 |
| hidden | number | 非必填 | - | - | 分类是否隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 分类排序 -- POST admin/downloads/updatesort

- controller: ``app\admin\controller\DownloadsController::postUpdateSort``
- desc: 分类排序 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 分类id |
| type | number | 必填 | - | - | 分类排序方式1置顶2置底3拖动 |
| pre_id | 字符串 | 非必填 | - | - | 上一个分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分类数据 -- DELETE admin/downloads/cat/:id

- controller: ``app\admin\controller\DownloadsController::deleteCat``
- desc: 删除分类数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 要删除的分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加文件 -- POST admin/downloads/addfile

- controller: ``app\admin\controller\DownloadsController::postAddFile``
- desc: 添加文件 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cateid | number | 必填 | - | - | 分类id,不能为0，不能在顶级分类下添加文件 |
| type | 字符串 | 必填 | zip | - | zip,zip |
| title | 字符串 | 必填 | - | - | 标题 |
| description | 字符串 | 非必填 | - | - | 描述 |
| filetype | 字符串 | 必填 | - | - | 上传文件的方式：manual,upload |
| filename | 字符串 | 必填 | - | - | 当上传方式为(manual时)，该字段必填 |
| uploadfile | file | 必填 | - | - | 当上传方式为(upload)，该字段必填 |
| uploadfilename | file | 必填 | - | - | 当上传方式为(upload)，该字段必填 |
| clientsonly | number | 非必填 | 0 | - | 1,选中复选框，用户登录之后才能下载该文件。 |
| productdownload | number | 非必填 | 0 | - | 1,选中复选框，需要购买相应的产品/服务后才可下载该文件。 |
| hidden | number | 非必填 | 0 | - | 1,选中复选框从用户中心隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑添加文件页面 -- GET admin/downloads/filepage

- controller: ``app\admin\controller\DownloadsController::getFilePage``
- desc: 编辑添加文件页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 文件id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "file_info":[{//文件信息
      "category":"父id",
      "type":"文件类型",
      "title":"标题",
      "description":"描述",
      "downloads":"下载数量",
      "location":"文件名",
      "clientsonly":"需要登录",
      "productdownload":"产品附件",
      "hidden":"隐藏",
    }]
    "cat_data":[{//分组数据
      "id":"分类id",
      "name":"分类名称",
    }]
  }
}
```

### 保存文件信息 -- POST admin/downloads/savefile

- controller: ``app\admin\controller\DownloadsController::postSaveFile``
- desc: 保存文件信息 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cateid | number | 必填 | - | - | 分类id |
| id | number | 必填 | - | - | 文件id |
| type | 字符串 | 必填 | - | - | 文件类型zip,exe,pdf |
| title | 字符串 | 必填 | - | - | 标题 |
| description | 字符串 | 必填 | - | - | 描述 |
| filetype | 字符串 | 必填 | - | - | 上传文件的方式：manual,upload |
| filename | 字符串 | 必填 | - | - | 当上传方式为(manual时)，该字段必填 |
| uploadfile | file | 必填 | - | - | 当上传方式为(upload)，该字段必填 |
| uploadfilename | file | 必填 | - | - | 当上传方式为(upload)，该字段必填 |
| location | 字符串 | 必填 | - | - | 文件名 |
| downloads | number | 必填 | - | - | 下载数量 |
| clientsonly | number | 非必填 | - | - | 1.需要登录 |
| productdownload | number | 非必填 | - | - | 1.产品附件 |
| hidden | number | 非必填 | - | - | 1.隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加文件 -- POST admin/downloads/uploadfile

- controller: ``app\admin\controller\DownloadsController::postUploadFile``
- desc: 添加文件 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uploadfile | file | 必填 | - | - | 当上传方式为(upload)，该字段必填 |
| type | 整型 | 必填 | - | - | 文件类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除文件 -- GET admin/downloads/file

- controller: ``app\admin\controller\DownloadsController::deleteFile``
- desc: 删除文件 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 文件id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 下载文件 -- GET admin/downloads/file/id/:id

- controller: ``app\admin\controller\DownloadsController::getFile``
- desc: 下载文件 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 附件列表 -- GET admin/downloads/userdownlist

- controller: ``app\admin\controller\DownloadsController::getUserDownList``
- desc: 附件列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | number | 非必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//附件数据
    }]
  }
}
```

### 下载附件 -- GET admin/downloads/getuserfile

- controller: ``app\admin\controller\DownloadsController::getUserFile``
- desc: 下载附件 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | number | 非必填 | - | - | 用户id |
| id | number | 非必填 | - | - | 文件id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加文件 -- POST admin/downloads/uploaduserfile

- controller: ``app\admin\controller\DownloadsController::postUploadUserFile``
- desc: 添加文件 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uploadfile | file | 必填 | - | - | 当上传方式为(upload)，该字段必填 |
| uid | 整型 | 必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除文件 -- GET admin/downloads/userfile

- controller: ``app\admin\controller\DownloadsController::deleteUserFile``
- desc: 删除文件 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 文件id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加用户附件 -- POST admin/downloads/adduserfile

- controller: ``app\admin\controller\DownloadsController::postAddUserFile``
- desc: 添加用户附件 -- uid

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | number | 必填 | - | - | 用户id, |
| name | 字符串 | 必填 | - | - | 标题 |
| remarks | 字符串 | 非必填 | - | - | 备注 |
| filename | 字符串 | 非必填 | - | - | 文件名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑添加附件页面 -- GET admin/downloads/userfilepage

- controller: ``app\admin\controller\DownloadsController::getUserFilePage``
- desc: 编辑添加附件页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 文件id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//文件信息
      "uid":"用户id",
      "name":"附件名",
      "remarks":"备注",
      "downname":"文件名",
    }]
  }
}
```

### 保存附件信息 -- POST admin/downloads/saveuserfile

- controller: ``app\admin\controller\DownloadsController::postSaveUserFile``
- desc: 保存附件信息 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | number | 必填 | - | - | 用户id, |
| name | 字符串 | 必填 | - | - | 标题 |
| remarks | 字符串 | 非必填 | - | - | 备注 |
| filename | 字符串 | 非必填 | - | - | 文件名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 文件下载设置 -- POST /admin/setting

- controller: ``app\admin\controller\DownloadsController::downloadsConfig``
- desc: 文件下载设置 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| enable_file_download | 整型 | 非必填 | - | - | 是否启用文件下载功能,0禁用1启用 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 站点公告

### 站点公告列表数据 -- GET admin/announce/list

- controller: ``app\admin\controller\AnnounceController::getList``
- desc: 站点公告列表数据 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array|\think\Response":"",
  }
}
```

### 删除站点公告 -- DELETE admin/announce/list/:id

- controller: ``app\admin\controller\AnnounceController::deleteList``
- desc: 删除站点公告 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 公告id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 公告内页数据 -- GET admin/announce/manage/:id

- controller: ``app\admin\controller\AnnounceController::getManage``
- desc: 公告内页数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 传入时为编辑页面 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array|\think\Response":"",
    "mulil_data":[{//多语言数据
      "language":"语言标识",
    }]
  }
}
```

### 保存公告 -- POST admin/announce/save

- controller: ``app\admin\controller\AnnounceController::postSave``
- desc: 保存公告 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 非必填 | - | - | 传入时为修改 |
| 日期 | 整型 | 必填 | - | - | 公告日期 |
| title | 字符串 | 必填 | - | - | 标题 |
| announcement | 字符串 | 非必填 | - | - | 内容 |
| hidden | 整型 | 非必填 | - | - | 是否显示， |
| multilang_title | 数组 | 非必填 | - | - | multilang_title[lang], |
| multilang_announcement | 数组 | 非必填 | - | - | multilang_announcement[lang] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台订单管理

### 搜索页面 -- GET admin/order/search_page

- controller: ``app\admin\controller\OrderController::searchPage``
- desc: 搜索页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 可选参数,用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订单列表 -- GET admin/order/search

- controller: ``app\admin\controller\OrderController::index``
- desc: 订单列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |
| sale_id | 整型 | 非必填 | - | - | 1, |
| product_name | 字符串 | 非必填 | - | - | 按产品名称搜索,精确搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "pay_status":"付款状态",
      "status":"状态",
    }]
  }
}
```

### 订单列表 -- GET admin/order/search

- controller: ``app\admin\controller\OrderController::index11``
- desc: 订单列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |
| sale_id | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "pay_status":"付款状态",
      "status":"状态",
    }]
  }
}
```

### 订单提成 -- POST admin/order/order_commission

- controller: ``app\admin\controller\OrderController::indexPost``
- desc: 订单提成 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| username | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//列表
    }]
  }
}
```

### 总提成 -- GET admin/order/saleorder

- controller: ``app\admin\controller\OrderController::indexSale``
- desc: 总提成 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| username | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//列表
    }]
  }
}
```

### 订单提成 -- POST admin/order/searchpost

- controller: ``app\admin\controller\OrderController::indexPostone``
- desc: 订单提成 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 非必填 | - | - | 账单id |
| prefix | 整型 | 非必填 | - | - | 前缀 |
| suffix | 整型 | 非必填 | - | - | 后缀 |
| sale_id | 整型 | 非必填 | - | - | 销售id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//列表
    }]
  }
}
```

### 订单审核 -- GET admin/order/check

- controller: ``app\admin\controller\OrderController::check``
- desc: 订单审核 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids | 整型 | 非必填 | 1 | - | 订单ID：ids或者ids[] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 取消订单 -- GET admin/order/cancel

- controller: ``app\admin\controller\OrderController::cancel``
- desc: 取消订单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ids | 整型 | 非必填 | 1 | - | 订单ID：ids或者ids[] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除订单(可批量) -- DELETE /admin/orders/delete

- controller: ``app\admin\controller\OrderController::delete``
- desc: 删除订单(可批量) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | 整型 | 非必填 | - | - | 订单ID：ids或者ids[] |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取创建订单的基本展示数据 -- GET /admin/order/create_page

- controller: ``app\admin\controller\OrderController::createPage``
- desc: 获取创建订单的基本展示数据 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户ID |
| pid | 整型 | 必填 | 1 | - | 产品ID |
| flag | 整型 | 必填 | 1 | - | 获取周期 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "users":[{//用户组
      "id":"用户id",
      "name":"用户名",
    }]
    "payment":"支付方式",
    "promo_code":" 优惠码",
    "group":[{//产品组
      "id":"组id",
      "name":"组名",
      "product":[{//产品
        "id":"产品id",
        "type":"类型",
        "gid":"组id",
        "name":"产品名",
        "description":"描述",
        "pay_method":"付款类型",
        "tax":"税",
      }]
    }]
    "cycle":"可选周期(free免费.onetime一次性,hour小时,day天,ontrial试用...",
  }
}
```

### 选择配置页面 -- GET /admin/orders/set_config

- controller: ``app\admin\controller\OrderController::setConfig``
- desc: 选择配置页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 产品ID |
| billingcycle | 整型 | 必填 | 1 | - | 所选周期 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 多产品--异步请求计算总价 -- POST /admin/get_total

- controller: ``app\admin\controller\OrderController::getMultiTotal``
- desc: 多产品--异步请求计算总价 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid[] | 字符串 | 必填 | - | - | 产品id（数组）所有参数必传,值可以为空 |
| billingcycle[] | 字符串 | 非必填 | 1 | - | 周期名称(比如：day、one |
| configoption[0][配置项ID] | 字符串 | 非必填 | 1 | - | 配置子项ID(或者数量)后端根据配置项ID的类型判断是子项ID还是数量！ |
| price_override[] | 整型 | 非必填 | 1 | - | 内部价格(首付价格) |
| price_override_renew[] | 整型 | 非必填 | 1 | - | 内部价格(续费价格) |
| qty[] | 整型 | 非必填 | 1 | - | 产品数量 |
| uid | 整型 | 非必填 | - | - | 用户id |
| customfield[0][自定义字段ID] | 整型 | 非必填 | - | - | 值 |
| code | 整型 | 非必填 | - | - | 优惠码（非必传参数,选了传code字符串） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":"货币信息",
    "credit":"客户余额",
    "products":[{//产品信息
      "name":"名称",
      "billingcycle":"周期",
      "product_setup_fee":"产品初装费",
      "product_price":"产品价格",
      "product_price_total_recurring":"循环周期价格",
      "child":[{//配置项+子项+价格
        "option_name":"配置项名称",
        "suboption_name":"子项名称",
        "suboption_setup_fee":"子项初装费",
        "suboption_price":"子项价格",
        "suboption_price_total":"子项总价",
        "qty":"数量(拉条的数量，前端需要判断是否有此值)",
      }]
    }]
    "subtotal":"小计",
    "discount":"优惠折扣",
    "total":"总计",
  }
}
```

### 获取用户 -- GET /admin/order/getclients

- controller: ``app\admin\controller\OrderController::getClients``
- desc: 获取用户 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 整型 | 非必填 | - | - | 用户名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提交订单 -- POST admin/order/create

- controller: ``app\admin\controller\OrderController::save``
- desc: 提交订单 -- 上官

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| payment | 字符串 | 必填 | - | - | 支付方式 |
| promo_code | 字符串 | 非必填 | - | - | 优惠码 |
| status | 字符串 | 非必填 | - | - | 订单状态 |
| adminorderconf | 字符串 | 非必填 | - | - | 确认订单 |
| admingenerateinvoice | 字符串 | 非必填 | - | - | 生成账单 |
| adminsendinvoice | 字符串 | 非必填 | - | - | 发送邮件 |
| use_credit | 整型 | 非必填 | - | - | 使用余额(1or0) |
| ops{} | 字符串 | 必填 | - | - | 产品配置 |
| pid | 字符串 | 必填 | - | - | 产品ID |
| billingcycle | 字符串 | 必填 | - | - | 付款周期 |
| qty | 整型 | 非必填 | - | - | 产品数量 |
| interior_price | 整型 | 非必填 | - | - | 内部价格(首付价格) |
| interior_price_renew | 整型 | 非必填 | - | - | 内部价格(续费价格) |
| configoptions | 整型 | 非必填 | - | - | 配置项+子项ID或数量的json对象 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".status":"",
  }
}
```

### 订单详情 -- GET /admin/orders/:id

- controller: ``app\admin\controller\OrderController::read``
- desc: 订单详情 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 订单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
    ".ip":"用户ip",
    ".country":"国家",
    ".id":"订单id",
    ".uid":"用户id",
    ".status":"订单状态('订单状态：Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈')",
    ".create_time":"时间",
    ".amount":"金额",
    ".promo_code":"优惠码",
    ".payment":"支付方式",
    ".invoice_id":"账单id 为0表示’无账单‘",
    "server":[{//订单项目
      "id":"host_id",
      "name":"产品名",
      "billingcycle":"周期",
      "amount":"金额",
      "username":"用户名",
      "welcome_email":"发送产品开通邮件",
    }]
  }
}
```

### 添加备注 -- POST /admin/orders/notes

- controller: ``app\admin\controller\OrderController::notes``
- desc: 添加备注 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 订单ID |
| notes | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 审核通过 -- POST /admin/orders/active

- controller: ``app\admin\controller\OrderController::active``
- desc: 审核通过 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 订单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改订单状态 -- POST /admin/orders/change_status

- controller: ``app\admin\controller\OrderController::changeStatus``
- desc: 修改订单状态 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 订单ID |
| status | 整型 | 非必填 | - | - | 产品状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建定制优惠码页面 -- GET admin/order/promo_code_page

- controller: ``app\admin\controller\OrderController::customPromoPage``
- desc: 创建定制优惠码页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建定制优惠码 -- POST admin/order/save_promo_code

- controller: ``app\admin\controller\OrderController::customPromo``
- desc: 创建定制优惠码 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| code | 字符串 | 必填 | - | - | 优惠码 |
| type | 字符串 | 非必填 | - | - | percent百分比,fixed固定金额,override置换价格,free免费安装 |
| recurring | 整型 | 非必填 | - | - | 是否循环优惠 |
| recurfor | 整型 | 非必填 | - | - | 循环优惠重复执行次数 |
| value | 浮点型 | 非必填 | - | - | 价值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\OrderController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\OrderController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\OrderController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\OrderController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\OrderController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\OrderController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\OrderController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台账单管理


---

## 后台交易流水

### 搜索页面 -- GET /admin/search_page

- controller: ``app\admin\controller\AccountController::searchPage``
- desc: 搜索页面 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 可选参数,用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 交易流水列表 -- GET /admin/accounts

- controller: ``app\admin\controller\AccountController::index``
- desc: 交易流水列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 可选参数,用户ID |
| pid | 整型 | 非必填 | 1 | - | 可选参数,产品ID |
| sale_id | 整型 | 非必填 | 1 | - | 销售ID |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段,username,create_time,gateway,description,amount_in,fees,amount_out |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| show | 字符串 | 非必填 | - | - | 显示类型(amount_in/amount_out) |
| description | 字符串 | 非必填 | - | - | 描述 |
| trans_id | 整型 | 非必填 | - | - | 付款流水号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| gateway | 整型 | 非必填 | - | - | 支付方式 |
| type | 字符串 | 非必填 | - | - | 类型：all全部\renew续费\host产品\recharge充值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 获取新增必要数据 -- GET /admin/accounts/create

- controller: ``app\admin\controller\AccountController::create``
- desc: 获取新增必要数据 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".users":"用户列表",
    ".currency":"货币",
    ".gateways":"网关",
  }
}
```

### 获取用户对应账单 -- GET /admin/accounts/createinvoice

- controller: ``app\admin\controller\AccountController::createInvoice``
- desc: 获取用户对应账单 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".invoice":"用户列表",
  }
}
```

### 新增流水 -- POST admin/accounts

- controller: ``app\admin\controller\AccountController::save``
- desc: 新增流水 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| pay_time | 整型 | 必填 | - | - | 时间 |
| description | 字符串 | 非必填 | - | - | 描述(描述和账单编号二选一必填) |
| trans_id | 字符串 | 非必填 | - | - | 付款流水号 |
| invoice_id | 整型 | 非必填 | - | - | 账单编号 |
| gateway | 字符串 | 非必填 | - | - | 付款方式[name] |
| amount_in | 浮点型 | 非必填 | - | - | 收入 |
| fees | 字符串 | 非必填 | - | - | 费用 |
| amount_out | 浮点型 | 非必填 | - | - | 支出 |
| currency | 浮点型 | 非必填 | - | - | 货币类型[code] |
| refund | 整型 | 非必填 | - | - | 退款至余额(1or0) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 流水表详情 -- GET admin/accounts/:id

- controller: ``app\admin\controller\AccountController::read``
- desc: 流水表详情 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 流水id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".uid":"客户ID",
    ".currency":"货币",
    ".gateway":"支付网关",
    ".pay_time":"时间",
    ".descripttion":"描述",
    ".amount_in":"收入",
    ".fees":"费用",
    ".amount_out":"支出",
    ".invoice_id":"账单id",
    ".trans_id":"付款流水号",
  }
}
```

### 更新流水表详情 -- PUT admin/accounts/:id

- controller: ``app\admin\controller\AccountController::update``
- desc: 更新流水表详情 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 流水id |
| gateway | 字符串 | 非必填 | - | - | 网关 |
| pay_time | 整型 | 非必填 | - | - | 时间 |
| amount_in | 整型 | 非必填 | - | - | 收入 |
| fees | 整型 | 非必填 | - | - | 费用 |
| amount_out | 整型 | 非必填 | - | - | 支出 |
| invoice_id | 整型 | 非必填 | - | - | 账单id |
| trans_id | 整型 | 非必填 | - | - | 付款流水号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".data":"1or0",
  }
}
```

### 删除流水 -- DELETE /admin/accounts/:id

- controller: ``app\admin\controller\AccountController::delete``
- desc: 删除流水 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | 整型 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\AccountController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\AccountController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\AccountController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\AccountController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\AccountController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\AccountController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\AccountController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\AccountController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台 用户余额管理

### 用户余额列表 -- GET /admin/credit

- controller: ``app\admin\controller\CreditController::index``
- desc: 用户余额列表 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | - |
| page | 整型 | 非必填 | - | - | 起始页 |
| size | 整型 | 非必填 | - | - | 长度 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
  }
}
```

### 创建余额 -- POST /admin/credit

- controller: ``app\admin\controller\CreditController::save``
- desc: 创建余额 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | - |
| amount | 整型 | 非必填 | - | - | - |
| description | 整型 | 非必填 | - | - | 描述 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 余额详情 -- GET /admin/credit/:id

- controller: ``app\admin\controller\CreditController::read``
- desc: 余额详情 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 余额id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更新余额 -- PUT /admin/credit/:id

- controller: ``app\admin\controller\CreditController::update``
- desc: 更新余额 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 余额id |
| description | 整型 | 非必填 | - | - | 余额id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 减少余额 -- POST /admin/credit/reduce

- controller: ``app\admin\controller\CreditController::reduce``
- desc: 减少余额 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 客户ID |
| amount | 整型 | 非必填 | - | - | 金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除余额 -- DELETE /admin/credit/:id

- controller: ``app\admin\controller\CreditController::delete``
- desc: 删除余额 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 余额id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台新闻

### 新闻列表页 -- GET /admin/news/list

- controller: ``app\admin\controller\NewsController::getList``
- desc: 新闻列表页 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| parent_id | 整型 | 非必填 | 1 | - | 父级id1=新闻公告2=帮助中心 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| search | 字符串 | 非必填 | - | - | 搜索关键词 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| page | 整型 | 非必填 | asc | - | 分页 |
| limit | 整型 | 非必填 | asc | - | 页面展示数量 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//新闻数据
      "id":"文章id",
      "title":"新闻标题",
      "hidden":"是否隐藏(1/0)",
      "sort":"排序id",
    }]
    "pagecount":"每页显示条数",
    "page":"当前页码",
    "search":"搜索关键字",
    "orderby":"排序字段",
    "sorting":"asc/desc,顺序或倒叙",
    "total_page":"总页码",
    "count":"总新闻数量",
  }
}
```

### 新闻分类页面数据 -- GET /admin/news/catspage

- controller: ``app\admin\controller\NewsController::getCatsPage``
- desc: 新闻分类页面数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 非必填 | - | - | 搜索标题 |
| status | 字符串 | 非必填 | - | - | 搜索状态 |
| page | 整型 | 非必填 | asc | - | 分页 |
| limit | 整型 | 非必填 | asc | - | 页面展示数量 |
| parent_id | 整型 | 非必填 | asc | - | 父级id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//分类数据
      "id":"分类id",
      "parent_id":"父级id",
      "title":"分类名",
      "status":"是否禁用(1/0)",
      "sort":"排序id",
    }]
    "meta":[{//分页数据
      "limit":"分页",
      "page":"页码",
      "total":"总数",
    }]
  }
}
```

### 新闻分类所有数据 -- GET /admin/news/catelist

- controller: ``app\admin\controller\NewsController::getCateList``
- desc: 新闻分类所有数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| parent_id | 整型 | 非必填 | - | - | 父级id，默认获取所有分页数据，此参数传0获取所有顶级分类 |
| status | 字符串 | 非必填 | - | - | 搜索状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//分类数据
      "id":"分类id",
      "parent_id":"父级id",
      "title":"分类名",
      "status":"是否禁用(1/0)",
      "sort":"排序id",
      "list":"子集数据",
    }]
  }
}
```

### 获取分类id数据 -- GET /admin/news/editcat

- controller: ``app\admin\controller\NewsController::getCatData``
- desc: 获取分类id数据 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分类id，传递时返回该分类数据 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"分类id",
    "title":"分类名",
    "parent_id":"父级id",
    "status":"1=正常0=禁用",
    "sort":"排序号",
  }
}
```

### 添加/编辑分类 -- POST /admin/news/editcat

- controller: ``app\admin\controller\NewsController::postEditCat``
- desc: 添加/编辑分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id，传递时为编辑，为空时为添加 |
| title | 字符串 | 必填 | - | - | 分类名称 |
| parent_id | 整型 | 非必填 | - | - | 父级分类id顶级为0 |
| status | 整型 | 非必填 | 0 | - | 是否禁用分类 |
| hidden | 整型 | 非必填 | 0 | - | 是否隐藏1=是0否 |
| sort | 整型 | 非必填 | 0 | - | 排序号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 验证新闻别名 -- GET /admin/news/checkalias

- controller: ``app\admin\controller\NewsController::getCheckalias``
- desc: 验证新闻别名 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| alias | 字符串 | 必填 | - | - | 别名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分类 -- DELETE /admin/news/cat

- controller: ``app\admin\controller\NewsController::deleteCat``
- desc: 删除分类 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑新闻页面数据 -- GET /admin/news/content

- controller: ``app\admin\controller\NewsController::getContent``
- desc: 编辑新闻页面数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 新闻id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "new_content":"新闻数据",
    "cat_data":"分类数据",
  }
}
```

### 添加/编辑新闻 -- POST /admin/news/editcontent

- controller: ``app\admin\controller\NewsController::postEditContent``
- desc: 添加/编辑新闻 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 新闻id，传递时为编辑，为空时为添加 |
| parent_id | 整型 | 非必填 | - | - | 分类id，不能为0 |
| title | 字符串 | 必填 | - | - | 新闻名称 |
| keywords | 字符串 | 非必填 | - | - | 新闻关键字 |
| label | 字符串 | 非必填 | - | - | 标签 |
| description | 字符串 | 非必填 | - | - | 新闻描述 |
| read | 整型 | 非必填 | - | - | 阅读量 |
| head_img | file | 非必填 | - | - | 封面图（没有读取内容，内容没有随机） |
| content | 字符串 | 非必填 | - | - | 新闻内容 |
| hidden | 整型 | 非必填 | 0 | - | 是否隐藏 |
| sort | 整型 | 非必填 | 0 | - | 排序号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除新闻 -- DELETE /admin/news/content

- controller: ``app\admin\controller\NewsController::deleteContent``
- desc: 删除新闻 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 新闻id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取站务自定义字段列表 -- GET /admin/news/getCustomParam

- controller: ``app\admin\controller\NewsController::getGetCustomParam``
- desc: 获取站务自定义字段列表 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | - |
| limit | 整型 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加站务自定义字段 -- GET /admin/news/addCustomParam

- controller: ``app\admin\controller\NewsController::getAddCustomParam``
- desc: 添加站务自定义字段 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| fieldname | 字符串 | 必填 | - | - | - |
| value | 字符串 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改站务自定义字段 -- GET /admin/news/updateCustomParam

- controller: ``app\admin\controller\NewsController::getUpdateCustomParam``
- desc: 修改站务自定义字段 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | - |
| fieldname | 字符串 | 必填 | - | - | - |
| value | 字符串 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除站务自定义字段的值 -- GET /admin/news/delCustomParam

- controller: ``app\admin\controller\NewsController::getDelCustomParam``
- desc: 删除站务自定义字段的值 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取要修改的站务自定义字段的值 /admin/news/getCustomUpdateVal

- controller: ``app\admin\controller\NewsController::getGetCustomUpdateVal``
- desc: 获取要修改的站务自定义字段的值 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台产品页面

### 后台用户产品服务内页 -- GET /admin/clients_services

- controller: ``app\admin\controller\ClientsServicesController::index``
- desc: 后台用户产品服务内页 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | number | 非必填 | - | - | 用户id |
| hostselect | number | 非必填 | - | - | 产品id |
| productid | number | 非必填 | - | - | 产品/服务id(此参数可不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "uid":"用户id",
    "host_data":"主机数据",
    "hostid":"主机id",
    "host_list":[{//用户购买主机列表
      "id":"主机id",
      "name":"主机名",
    }]
    "product_list":[{//可切换的产品/服务
      "id":"产品组id",
      "groupname":"组名称",
      "list":[{//产品组下产品列表
        "id":"产品id",
        "gid":"产品组id",
        "name":"产品名称",
      }]
    }]
    "server_list":[{//服务器列表
      "id":"服务器idid:服务器id",
    }]
    "products":[{//产品信息
      "id":"产品ID",
      "gid":"产品组ID",
      "type":"产品类型",
      "pay_type":"产品周期",
      "qty":"库存",
      "auto_setup":"自动开通：order，下单后；payment：支付后；on：手动审核",
    }]
    "promo_data":[{//可选优惠码
    }]
    "module_button":[{//模块按钮输出
      "type":"默认(default),自定义(custom)",
      "func":"方法名",
      "name":"方法名",
    }]
    "module_admin_area":[{//后台模块输出标签
      "name":"标签名",
      "content":"展示内容",
    }]
    "custom_field":[{//产品自定义字段数组
      "id":"字段id",
      "fieldname":"字段名",
      "fieldtype":"字段类型",
      "fieldoptions":"字段可选项",
      "dropdown_option":"字段下拉项(当type为dropdown时生效)",
      "regexpr":"正则判定（存在需要判定用户输入）",
    }]
    "custom_field_value":"字段的值(字段id=>字段值)",
    "domain_status_list":"状态列表",
    "gateways_list":[{//可用支付网关列表
      "name":"网关名",
      "title":"标题",
    }]
    "dcim.group":[{//服务器分组信息
      "id":"分组ID",
      "name":"分组名称",
      "svg":"分组svg",
    }]
    "dcim.os":[{//操作系统
      "id":"操作系统ID",
      "name":"操作系统名称",
      "os_name":"操作系统文件名称",
      "ostype":"操作系统类型(1windows0linux)",
      "port":"默认重装端口号",
    }]
    "dcim.auth.create":"开通(on显示off不显示)",
    "dcim.auth.suspend":"暂停(on显示off不显示)",
    "dcim.auth.unsuspend":"解除暂停(on显示off不显示)",
    "dcim.auth.terminate":"删除(on显示off不显示)",
    "dcim.auth.on":"开机(on显示off不显示)",
    "dcim.auth.off":"关机(on显示off不显示)",
    "dcim.auth.reboot":"重启(on显示off不显示)",
    "dcim.auth.bmc":"重置bmc(on显示off不显示)",
    "dcim.auth.kvm":"kvm(on显示off不显示)",
    "dcim.auth.ikvm":"ikvm(on显示off不显示)",
    "dcim.auth.vnc":"vnc(on显示off不显示)",
    "dcim.auth.reinstall":"重装系统(on显示off不显示)",
    "dcim.auth.rescue":"救援系统(on显示off不显示)",
    "dcim.auth.crack_pass":"重置密码(on显示off不显示)",
    "dcim.url":"DCIM详情链接",
    "dcim.server_group":"服务器分组ID",
    "module_upgrade":"是否输出升降级",
    "zjmf_api.id":"接口ID",
    "zjmf_api.name":"接口名称",
    "module_power_status":"是否请求电源状态",
  }
}
```

### 产品内页层级联动 -- GET /admin/adminGetLinkAgeList

- controller: ``app\admin\controller\ClientsServicesController::adminGetLinkAgeList``
- desc: 产品内页层级联动 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户id |
| pid | 整型 | 必填 | - | - | 产品id |
| cid | 整型 | 必填 | - | - | 层级联动最顶级配置项id |
| sub_id | 整型 | 非必填 | - | - | 当前选项id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取产品列表 -- GET /admin/clients_services/get_product_list

- controller: ``app\admin\controller\ClientsServicesController::getProductList``
- desc: 获取产品列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 保存用户产品 -- POST /admin/clients_services/info

- controller: ``app\admin\controller\ClientsServicesController::postInfo``
- desc: 保存用户产品 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id |
| productid | number | 必填 | - | - | 产品id(不更改产品时和之前产品一样) |
| regdate | number | 必填 | - | - | 时间戳，开通时间 |
| firstpaymentamount | 浮点型 | 非必填 | 0.00 | - | 首付金额 |
| serverid | number | 非必填 | - | - | 服务器--传id |
| domain | number | 非必填 | - | - | 主机名 |
| amount | 浮点型 | 非必填 | 0.00 | - | 续费金额 |
| nextduedate | 整型 | 必填 | - | - | 下次到期时间(不修改时两字段相同) |
| termination_date | 整型 | 非必填 | - | - | 终止时间 |
| username | 字符串 | 非必填 | - | - | 用户名 |
| password | 字符串 | 非必填 | - | - | 密码 |
| billingcycle | 字符串 | 必填 | - | - | 周期 |
| payment | 字符串 | 必填 | - | - | 支付方式 |
| domainstatus | 字符串 | 必填 | - | - | 主机状态(Pending,Active,Completed,Suspended,Terminated,Cancelled,Fraud) |
| promoid | number | 非必填 | - | - | 优惠码id |
| dedicatedip | 字符串 | 非必填 | - | - | 独立ip地址 |
| assignedips | 字符串 | 非必填 | - | - | 分配的ip地址 |
| overideautosuspend | 整型 | 非必填 | - | - | 修改暂停时间(1,0) |
| overidesuspenduntil | 整型 | 非必填 | - | - | 暂停时间戳 |
| auto_terminate_end_cycle | 整型 | 非必填 | - | - | 到期后自动删除 |
| auto_terminate_reason | 字符串 | 非必填 | - | - | 到期自动删除原因 |
| notes | 字符串 | 非必填 | - | - | 管理员备注 |
| configoption | 数组 | 非必填 | - | - | 可配置选项数组eg.configoption[12] |
| custom | 数组 | 非必填 | - | - | 自定义字段eg.custom[16] |
| auto_recalcre_curring_price | - | 非必填 | - | - | 重新计算价格选项框，1|0 |
| other | - | 非必填 | - | - | 其他模块标签输出的input框数据 |
| dcimid | 整型 | 非必填 | - | - | 关联ID(产品为魔方云,dcim,代理产品可修改) |
| initiative_renew | 整型 | 非必填 | - | - | 是否自动续费 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 转移产品和服务 -- POST /admin/clients_services/transfer

- controller: ``app\admin\controller\ClientsServicesController::postTransfer``
- desc: 转移产品和服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| transfer_uid | number | 必填 | - | - | 接收用户id |
| hostid | number | 必填 | - | - | 产品id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除产品和服务 -- DELETE /admin/clients_services/host

- controller: ``app\admin\controller\ClientsServicesController::deleteHost``
- desc: 删除产品和服务 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid[] | number|array | 必填 | - | - | 主机id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品续费 -- GET /admin/clients_services/host_renew

- controller: ``app\admin\controller\ClientsServicesController::hostRenew``
- desc: 产品续费 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id,可传单个值hostid |
| billingcycles | number | 非必填 | - | - | 周期，可选参数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 结算批量续费（页面） -- POST /admin/clients_services/host_batch_renew_page

- controller: ``app\admin\controller\ClientsServicesController::postBatchRenewPage``
- desc: 结算批量续费（页面） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 数组 | 必填 | - | - | 客户ID |
| host_ids | 数组 | 必填 | - | - | 批量续费的产品数组 |
| cycles[产品ID] | 数组 | 非必填 | - | - | (可选参数,第一次不传,在续费页面修改周期时传递此值)批量续费的产品周期:cycles[38] |
| amount[产品ID] | 数组 | 必填 | - | - | 产品金额（管理员可自定义） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "currency":"货币信息",
    "hosts":[{//产品信息
      "id":[{//产品IDname
      }]
    }]
    "total":"总价",
    "credit":"余额",
  }
}
```

### 结算批量续费 -- POST /admin/clients_services/host_batch_renew

- controller: ``app\admin\controller\ClientsServicesController::postBatchRenew``
- desc: 结算批量续费 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |
| host_ids | 数组 | 必填 | - | - | 批量续费的产品数组 |
| cycles[产品ID] | 数组 | 必填 | - | - | 相应周期数组 |
| amount[产品ID] | 数组 | 必填 | - | - | 产品金额（管理员可自定义） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoiceid":"(调用标记已支付admin/invoice/paid)",
  }
}
```

### 向账单使用余额 页面 -- POST /admin/clients_services/apply_credit_page

- controller: ``app\admin\controller\ClientsServicesController::getApplyCreditPage``
- desc: 向账单使用余额 页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 账单id |
| uid | 浮点型 | 必填 | - | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 向账单使用余额 -- POST /admin/clients_services/apply_credit

- controller: ``app\admin\controller\ClientsServicesController::applyCredit``
- desc: 向账单使用余额 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | number | 必填 | - | - | 账单id |
| uid | 浮点型 | 必填 | - | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 暂停产品页面 -- GET /admin/clients_services/host_suspend

- controller: ``app\admin\controller\ClientsServicesController::suspendPage``
- desc: 暂停产品页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid | number | 必填 | - | - | 主机id,可传单个值hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 暂停产品(仅改变数据库数据,不做模块动作20211213改) -- POST /admin/clients_services/host_suspend

- controller: ``app\admin\controller\ClientsServicesController::suspend``
- desc: 暂停产品(仅改变数据库数据,不做模块动作20211213改) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | number | 必填 | - | - | 主机id |
| reason | 字符串 | 必填 | - | - | 原因 |
| reason_type | 字符串 | 必填 | - | - | 类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 搜索用户 -- POST /admin/clients_services/searchclient

- controller: ``app\admin\controller\ClientsServicesController::postSearchClient``
- desc: 搜索用户 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| client_id | 字符串 | 非必填 | - | - | 搜索关键字(id，email，username) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client_list":[{//客户列表
      "id":"",
      "email":"邮箱",
      "username":"用户名",
    }]
  }
}
```

### 升降级可配置项 -- POST /admin/clients_services/upgrade_config

- controller: ``app\admin\controller\ClientsServicesController::upgradeConfig``
- desc: 升降级可配置项 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | number | 必填 | - | - | - |
| configoption[配置项ID] | 字符串 | 必填 | 1 | - | 所选择的子项ID,拉条传数量(当所有配置项都无变化时,不请求接口) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 退款页面 -- GET /admin/clients_services/refund_page

- controller: ``app\admin\controller\ClientsServicesController::getRefundPage``
- desc: 退款页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 必填 | - | - | 主机id(产品ID) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoices":[{//账单
      "id":"",
      "subtotal":"金额",
      "type":"类型",
      "type_zh":"类型",
    }]
    "currency":"货币",
    "refund_method":"退款方案",
    "refund_type":"退款类型",
    "refund_amount":"可退款金额",
  }
}
```

### 退款 -- POST /admin/clients_services/refund

- controller: ``app\admin\controller\ClientsServicesController::refund``
- desc: 退款 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 必填 | - | - | 主机id(产品ID) |
| refund_method | 字符串 | 必填 | - | - | 退款方案:'day'=>'按天计算','full'=>'全额退款','custom'=>'自定义' |
| refund_type | 字符串 | 必填 | - | - | 退款类型:'addascredit'=>'退款至余额','only'=>'仅标记退款' |
| amount | 字符串 | 非必填 | - | - | 退款金额(仅自定义传此参数) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\ClientsServicesController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\ClientsServicesController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\ClientsServicesController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\ClientsServicesController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\ClientsServicesController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\ClientsServicesController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\ClientsServicesController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\ClientsServicesController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台取消请求页面

### 获取当前待审核的取消请求 -- GET /admin/cancel_request/list

- controller: ``app\admin\controller\CancelRequestController::getList``
- desc: 获取当前待审核的取消请求 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"ID",
    "relid":"主机id",
    "type":"立即取消(Immediate),等待账单周期结束(Endofbilling)",
    "reason":"取消原因",
    "username":"用户名",
    "hostid":"主机id",
    "uid":"用户id",
    "domainstatus":"主机状态",
    "nextduedate":"到期时间",
    "productname":"产品名称",
    "groupname":"组名称",
    "type_desc":"显示类型描述",
    "product_desc":"显示产品描述",
  }
}
```

### 删除取消请求 -- DELETE /admin/cancel_request/list

- controller: ``app\admin\controller\CancelRequestController::deleteList``
- desc: 删除取消请求 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"ID",
  }
}
```

### 获取已被取消的列表 -- GET /admin/cancel_request/cancellist

- controller: ``app\admin\controller\CancelRequestController::getCancelList``
- desc: 获取已被取消的列表 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"ID",
    "relid":"主机id",
    "type":"立即取消(Immediate),等待账单周期结束(Endofbilling)",
    "reason":"取消原因",
    "username":"用户名",
    "hostid":"主机id",
    "uid":"用户id",
    "domainstatus":"主机状态",
    "nextduedate":"到期时间",
    "productname":"产品名称",
    "groupname":"组名称",
    "type_desc":"显示类型描述",
    "product_desc":"显示产品描述",
  }
}
```


---

## 产品/服务列表页

### 产品/服务列表页数据接口，带搜索 -- GET /admin/host/list

- controller: ``app\admin\controller\HostController::getList``
- desc: 产品/服务列表页数据接口，带搜索 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | number | 非必填 | 1 | - | 页码 |
| pagecount | number | 非必填 | - | - | 每页显示条数 |
| order | 字符串 | 非必填 | id | - | 排序字段(id,uid,productid,billingcycle,payment,nextduedate,dedicatedip,username,productname) |
| sort | 字符串 | 非必填 | ASC | - | 排序方式 |
| product_type | 字符串 | 非必填 | - | - | 产品类型(搜索字段) |
| uid | 整型 | 非必填 | - | - | 用户名(搜索字段) |
| name | 字符串 | 非必填 | - | - | 产品名(搜索字段) |
| server | number | 非必填 | - | - | 服务器id(搜索字段) |
| product | number | 非必填 | - | - | 产品id(搜索字段) |
| payment | 字符串 | 非必填 | - | - | 支付方式(搜索字段) |
| billingcycle | 字符串 | 非必填 | - | - | 付款周期(搜索字段) |
| domainstatus | 字符串 | 非必填 | - | - | 主机状态(搜索字段) |
| domain | 字符串 | 非必填 | - | - | 主机名(搜索字段) |
| ip | 字符串 | 非必填 | - | - | ip(搜索字段) |
| nextduedate | 整型 | 非必填 | - | - | 到期时间 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "base":[{//基础数据(搜索区)
      "billingcycle":"周期",
      "gateway_list":"支付方式",
      "product_list":[{//产品列表
        "id":"分组id",
        "groupname":"分组名称",
        "clild":[{//产品数组
          "id":"产品id",
          "productname":"产品名称",
        }]
      }]
      "product_type":"产品类型",
      "server_list":"服务器列表",
      "domainstatus":"服务器状态",
    }]
    "list":[{//数据列表
      "id":"主机id",
      "dedicatedip":"独立ip",
      "billingcycle":"周期",
      "dedicatedip":"主ip地址",
      "assignedips":"附加ip地址",
      "nextduedate":"到期时间",
      "payment":"付款方式",
      "productid":"产品id",
      "productname":"产品名",
      "productname":"状态'Pending','Active','Suspended','Terminated','Cancelled','Fraud','Completed'",
      "uid":"用户id",
      "amount":"价格",
      "regdate":"开通时间",
      "dedicatedip":"ip地址",
      "type":"产品类型(shared",
      "username":"用户名",
      "sale_name":"显示销售",
    }]
    "pagination":[{//分页相关数据
      "count":"总数量",
      "total_page":"总页码",
      "pagecount":"每页数量",
      "page":"当前页码",
      "orderby":"排序字段",
      "sorting":"排序方式",
    }]
    "search":[{//搜索参数
      "billingcycle":"周期",
      "domainstatus":"主机状态",
      "payment":"支付方式",
      "product":"产品id",
      "product_type":"产品类型",
      "server":"服务器id",
    }]
  }
}
```

### 获取时间类型 -- GET /admin/host/get_timetype

- controller: ``app\admin\controller\HostController::getTimetype``
- desc: 获取时间类型 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据(搜索区)
    }]
  }
}
```

### 获取用户信息 -- POST /admin/host/userInfo

- controller: ``app\admin\controller\HostController::userInfo``
- desc: 获取用户信息 -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\HostController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\HostController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\HostController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\HostController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\HostController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\HostController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\HostController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\HostController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 批量发送邮件页面

### 发送邮件页面数据 -- POST /admin/send_message/emailpage

- controller: ``app\admin\controller\SendMessageController::postEmailPage``
- desc: 发送邮件页面数据 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | 批量发送的类型 |
| selected | 数组 | 必填 | - | - | 发送的一维数组id，和type有关 |
| load_message_id | number | 非必填 | - | - | 传递可以返回相关的已有邮件类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "fromname":"发送人",
    "fromemail":"发送邮箱",
    "type":"当前发送类型",
    "clients":[{//发送用户信息
      "id":"用户id",
      "username":"用户名",
      "email":"邮箱",
    }]
    "email_temp_list":[{//邮件模板列表
      "id":"模板id",
      "name":"模板名",
    }]
  }
}
```

### 发送邮件接口 -- POST /admin/send_message/sendemail

- controller: ``app\admin\controller\SendMessageController::postSendEmail``
- desc: 发送邮件接口 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | 批量发送的类型 |
| selected | 数组 | 必填 | - | - | 发送的一维数组id，和type有关 |
| fromname | 字符串 | 必填 | - | - | 发送人名称 |
| fromemail | email | 必填 | - | - | 发送人邮箱 |
| subject | 字符串 | 必填 | - | - | 主题 |
| cc | 字符串 | 非必填 | - | - | 副本 |
| bcc | 字符串 | 非必填 | - | - | 抄送 |
| message | 字符串 | 必填 | - | - | 内容 |
| attachments | file | 非必填 | - | - | 附件数组array() |
| savename | 字符串 | 非必填 | - | - | 将该模板保存到系统 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 客户子账户管理

### 客户子账户管理页面 -- GET /admin/client_contacts/page

- controller: ``app\admin\controller\ClientsContactsController::getPage``
- desc: 客户子账户管理页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| contactid | 整型 | 非必填 | - | - | 子账户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "uid":"用户id",
    "contact_list":[{//子账户列表
      "id":"子账户id",
      "username":"用户名",
      "email":"子账户邮箱账号",
    }]
    "contactid":"子账户id",
    "contact_data":[{//子账户数据
      "id":"子账户id",
      "username":"用户名",
      "sex":"性别",
      "avatar":"头像地址",
      "companyname":"公司名",
      "email":"邮箱",
      "wechat_id":"微信id",
      "country":"国家",
      "province":"省份",
      "city":"城市",
      "region":"区",
      "address1":"地址一",
      "address2":"地址二",
      "postcode":"邮编",
      "phonenumber":"手机号",
      "permissions":"权限（使用另一个permissions_arr字段）",
      "generalemails":"接收通用邮件通知",
      "invoiceemails":"接收账单通知",
      "productemails":"接收产品邮件通知",
      "supportemails":"接收工单邮件通知",
      "authmodule":"",
      "authdata":"",
      "lastlogin":"",
      "status":"",
    }]
  }
}
```

### 保存/添加子账户 -- POST /admin/client_contacts/save

- controller: ``app\admin\controller\ClientsContactsController::postSave``
- desc: 保存/添加子账户 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| contactid | 整型 | 非必填 | - | - | 子账户id |
| username | 字符串 | 非必填 | - | - | 用户名 |
| sex | 整型 | 必填 | 0 | - | 性别(0未知，1男，2女) |
| avatar | 字符串 | 非必填 | - | - | 头像地址 |
| companyname | 字符串 | 非必填 | - | - | 公司名 |
| email | 字符串 | 必填 | - | - | 邮箱 |
| wechat_id | 字符串 | 非必填 | - | - | 微信id |
| country | 字符串 | 非必填 | - | - | 国家 |
| province | 字符串 | 非必填 | - | - | 省份 |
| city | 字符串 | 非必填 | - | - | 城市 |
| region | 字符串 | 非必填 | - | - | 区 |
| address1 | 字符串 | 非必填 | - | - | 地址一 |
| address2 | 字符串 | 非必填 | - | - | 地址二 |
| postcode | number | 非必填 | - | - | 邮编 |
| phonenumber | 整型 | 非必填 | - | - | 手机号 |
| generalemails | 整型 | 非必填 | - | - | 接收通用邮件通知(0,1) |
| invoiceemails | 整型 | 非必填 | - | - | 接收账单通知 |
| productemails | 整型 | 非必填 | - | - | 接收产品邮件通知 |
| supportemails | 整型 | 非必填 | - | - | 接收工单邮件通知 |
| status | 整型 | 非必填 | - | - | 状态（1激活，0未激活，2关闭） |
| password | 字符串 | 非必填 | - | - | 设置的子账户密码 |
| permissions | 数组 | 非必填 | - | - | 权限数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除子账户 -- DELETE /admin/client_contacts/contact

- controller: ``app\admin\controller\ClientsContactsController::deleteContact``
- desc: 删除子账户 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| contactid | 整型 | 必填 | - | - | 子账户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台用户分组

### 分组列表 -- GET admin/client_group

- controller: ``app\admin\controller\ClientGroupController::index``
- desc: 分组列表 -- 上官🔪

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"",
    "group_name":"  名称",
    "group_colour":"  组颜色",
    "discount_percent":"  折扣百分比",
    "susptermexempt":"  暂停/删除豁免权(1是0否)",
    "separateinvoices":"拆分服务账单",
  }
}
```

### 创建用户组 -- POST admin/client_group

- controller: ``app\admin\controller\ClientGroupController::save``
- desc: 创建用户组 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| group_name | 字符串 | 必填 | - | - | 名称 |
| group_colour | 字符串 | 非必填 | - | - | 组颜色 |
| discount_percent | 整型 | 必填 | - | - | 折扣百分比 |
| susptermexempt | 整型 | 必填 | - | - | 暂停/删除豁免权(1是0否) |
| separateinvoices | 整型 | 非必填 | - | - | 拆分服务账单 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 组详情 -- GET admin/client_group/id

- controller: ``app\admin\controller\ClientGroupController::read``
- desc: 组详情 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "group_name":"  名称",
    "group_colour":"  组颜色",
    "discount_percent":"  折扣百分比",
    "susptermexempt":"  暂停/删除豁免权(1是0否)",
    "separateinvoices":"拆分服务账单",
  }
}
```

### 更新组 -- PUT admin/client_group/:id

- controller: ``app\admin\controller\ClientGroupController::update``
- desc: 更新组 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | - |
| group_name | 字符串 | 必填 | - | - | 名称 |
| group_colour | 字符串 | 必填 | - | - | 组颜色 |
| discount_percent | 整型 | 必填 | - | - | 折扣百分比 |
| susptermexempt | 整型 | 必填 | - | - | 暂停/删除豁免权(1是0否) |
| separateinvoices | 整型 | 必填 | - | - | 拆分服务账单(1是0否) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 删除组 -- DELETE admin/client_group/id

- controller: ``app\admin\controller\ClientGroupController::delete``
- desc: 删除组 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 唯一ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```


---

## 常规设置

### 邮件设置页面 -- GET /admin/config_general/email_indexemail_index_post

- controller: ``app\admin\controller\ConfigGeneralController::emailIndex``
- desc: 邮件设置页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".shd_allow_email_send":"邮件设置,1开启,0关闭",
    ".type":"邮件类型",
    ".charset":"设置发送的邮件的编码",
    ".port":"设置ssl连接smtp服务器的远程服务器端口号",
    ".host":"链接qq域名邮箱的服务器地址",
    ".username":"smtp登录的账号 QQ邮箱即可",
    ".password":"smtp登录的密码 使用生成的授权码",
    ".smtpsecure":"设置使用ssl加密方式登录鉴权",
    ".fromname":" 系统邮件名",
    ".systememail":"系统邮箱名",
    ".subject":"邮件的主题",
    ".body":"邮件内容",
  }
}
```

### 邮件设置页面提交 -- POST /admin/config_general/email_index_post

- controller: ``app\admin\controller\ConfigGeneralController::emailIndexPost``
- desc: 邮件设置页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_allow_email_send | 整型 | 必填 | 1 | - | 邮件设置 |
| type | 字符串 | 必填 | 1 | - | 邮件类型 |
| charset | 字符串 | 必填 | 1 | - | 设置发送的邮件的编码 |
| port | 字符串 | 必填 | 1 | - | 设置ssl连接smtp服务器的远程服务器端口号 |
| host | 字符串 | 必填 | 1 | - | 链接qq域名邮箱的服务器地址 |
| username | 字符串 | 必填 | 1 | - | smtp登录的账号 |
| password | 字符串 | 必填 | 1 | - | smtp登录的密码 |
| smtpsecure | 字符串 | 必填 | 1 | - | 设置使用ssl加密方式登录鉴权 |
| fromname | 字符串 | 必填 | 1 | - | 系统邮件名 |
| systememail | 字符串 | 必填 | 1 | - | 统邮箱名 |
| subject | 字符串 | 必填 | 1 | - | 邮件的主题 |
| body | 字符串 | 必填 | 1 | - | 邮件内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 测试邮件发送 -- POST /admin/config_general/send_email

- controller: ``app\admin\controller\ConfigGeneralController::sendEmailTest``
- desc: 测试邮件发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 支持设置页面 -- GET /admin/config_general/support_index

- controller: ``app\admin\controller\ConfigGeneralController::supportIndex``
- desc: 支持设置页面 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".nologin_send_ticket":"未登录也可发工单",
    ".ticket_reply_order":"工单回复列表顺序asc升序,desc降序",
    ".evaluate_ticket":"允许为工单评价",
    ".product_download":"包括产品下载",
  }
}
```

### 语言列表 -- POST /admin/config_general/lang_list

- controller: ``app\admin\controller\ConfigGeneralController::langList``
- desc: 语言列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".lang":"语言列表",
  }
}
```

### 设置后台系统语言(cookie设置) -- POST /admin/config_general/set_admin_lang

- controller: ``app\admin\controller\ConfigGeneralController::setAdminLang``
- desc: 设置后台系统语言(cookie设置) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| lang | 字符串 | 必填 | 1 | - | 语言：zh-cn,en-us |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 头部底部 -- GET /admin/config_general/header

- controller: ``app\admin\controller\ConfigGeneralController::getHeader``
- desc: 头部底部 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "header":"头部",
    "footer":"底部",
  }
}
```

### 常规设置页面 -- GET /admin/config_general/general

- controller: ``app\admin\controller\ConfigGeneralController::getGeneral``
- desc: 常规设置页面 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "company_name":"公司名",
    "company_email":"默认邮箱地址",
    "domain":"网站域名",
    "system_url":"系统链接",
    "logo_url":"logo地址",
    "invoice_payto":"付款条文",
    "activity_limit":"系统活动日志限制",
    "num_records":"默认每页显示条数",
    "main_tenance_mode":"是否为维护模式",
    "main_tenance_mode_message":"维护模式信息",
    "main_tenance_mode_url":"维护模式重定向的链接",
    "home_ip_check":"前台登录是否禁用ip",
    "admin_ip_check":"后天登录是否禁用ip",
    "main_phone":"手机",
    "main_address":"地址",
    "record_no":"备案号",
    "company_profile":"公司简介",
    "header":"头部",
    "footer":"底部",
    "map":"坐标",
    "sendmsgtimes  每天短信发送次数":"",
    "sendmsgphone 每天短信发送手机个数":"",
    "deletelogtime 删除日志天数":"",
    "cancellation_time 登录注销时间":"",
    "is_themes 是否开启主题":"",
    "themes_templates 主题模板":"",
    "login_header_footer":"是否开启登录头部底部,1开启 0否",
    "login_header":"登录头部",
    "login_footer":"登录底部",
    "cart_product_description":"购物车页面 应用说明",
    "custom_login_background_img":"前台登录背景图地址",
    "custom_login_background_char":"前台登录背景图文字",
    "custom_login_background_description":"登录页描述",
    "clientarea_default_themes":"客户中心模板目录默认",
    "clientarea_themes":"客户中心模板目录",
    "credit_limit":"是否开启前台信用额,1开启 0否",
  }
}
```

### 系统设置统一修改接口（新） -- POST /admin/config_general/newGeneral

- controller: ``app\admin\controller\ConfigGeneralController::postNewGeneral``
- desc: 系统设置统一修改接口（新） -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 系统设置统一获取接口（新） -- POST /admin/config_general/getConfig

- controller: ``app\admin\controller\ConfigGeneralController::postGetConfig``
- desc: 系统设置统一获取接口（新） -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 系统设置选项统一获取接口（新） -- POST /admin/config_general/getConfigOption

- controller: ``app\admin\controller\ConfigGeneralController::postGetConfigOption``
- desc: 系统设置选项统一获取接口（新） -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置页面提交(之前版本) -- POST /admin/config_general/general

- controller: ``app\admin\controller\ConfigGeneralController::postGeneral``
- desc: 常规设置页面提交(之前版本) -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| company_name | 字符串 | 非必填 | - | - | 公司名 |
| company_email | email | 非必填 | - | - | 默认邮箱地址 |
| domain | 字符串 | 必填 | - | - | 网站域名 |
| logo_url | 字符串 | 非必填 | - | - | logo地址 |
| invoice_payto | 字符串 | 非必填 | - | - | 付款条文 |
| system_url | number | 必填 | - | - | 系统链接 |
| activity_limit | number | 必填 | - | - | 系统活动日志限制 |
| num_records | 字符串 | 必填 | - | - | 默认每页显示条数 |
| main_tenance_mode | 字符串 | 必填 | 0 | - | 是否为维护模式 |
| main_tenance_mode_message | 字符串 | 非必填 | 1 | - | 维护模式信息 |
| main_tenance_mode_url | 字符串 | 非必填 | - | - | 维护模式重定向的链接 |
| home_ip_check | 整型 | 非必填 | 0 | - | 前台登录是否禁用ip |
| admin_ip_check | 整型 | 非必填 | 0 | - | 后天登录是否禁用ip |
| logo_url_home | 整型 | 非必填 | 0 | - | 前台logo地址 |
| admin_application | 字符串 | 非必填 | 0 | - | 应用目录文件 |
| server_clause_url | 字符串 | 非必填 | 0 | - | 服务条款地址 |
| privacy_clause_url | 字符串 | 非必填 | 0 | - | 隐私条款地址 |
| main_phone | 字符串 | 非必填 | 0 | - | 手机 |
| main_address | 字符串 | 非必填 | 0 | - | 地址 |
| record_no | 字符串 | 非必填 | 0 | - | 备案号 |
| map | 字符串 | 非必填 | 0 | - | 坐标:29.543593,106.313324 |
| company_profile | 字符串 | 非必填 | 0 | - | 公司简介 |
| per_page_limit | 字符串 | 非必填 | 0 | - | 每页条数 |
| header | 字符串 | 非必填 | - | - | 头部 |
| footer | 字符串 | 非必填 | - | - | 底部 |
| sendmsgtimes | 字符串 | 非必填 | - | - | 每天短信发送次数 |
| sendmsgphone | 字符串 | 非必填 | - | - | 每天短信发送手机个数 |
| deletelogtime | 字符串 | 非必填 | - | - | 删除日志天数 |
| cancellation_time | 字符串 | 非必填 | - | - | 注销时间 |
| is_themes | 字符串 | 非必填 | - | - | 是否开启主题 |
| themes_templates | 字符串 | 非必填 | - | - | 主题模板 |
| login_header_footer | 整型 | 非必填 | - | - | 是否开启前台登陆界面头部、底部 |
| login_header | 字符串 | 非必填 | - | - | 登录头部 |
| login_footer | 字符串 | 非必填 | - | - | 登录底部 |
| cart_product_description | 字符串 | 非必填 | - | - | 购物车页面 |
| custom_login_background_img | 字符串 | 非必填 | - | - | 前台登录背景图地址 |
| custom_login_background_char | 字符串 | 非必填 | - | - | 前台登录背景图文字 |
| custom_login_background_description | 字符串 | 非必填 | - | - | 登录页描述 |
| clientarea_default_themes | 字符串 | 非必填 | - | - | 客户中心模板 |
| credit_limit | 整型 | 非必填 | - | - | 是否开启前台信用额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置页面-本地化 -- GET /admin/config_general/local

- controller: ``app\admin\controller\ConfigGeneralController::getLocal``
- desc: 常规设置页面-本地化 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "charset":"默认字符集(utf-8)",
    "date_format":"后台日期格式",
    "client_date_format":"前台用户显示的日期格式",
    "default_country":"默认国家",
    "language":"默认语言",
    "allow_user_language":"是否允许用户更改系统语言 1,0",
    "tel_cc_input":"是否自动格式化手机号码 1,0",
  }
}
```

### 常规设置-本地化页面提交 -- POST /admin/config_general/local

- controller: ``app\admin\controller\ConfigGeneralController::postLocal``
- desc: 常规设置-本地化页面提交 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| charset | 字符串 | 必填 | - | - | 默认字符集(utf-8) |
| date_format | 字符串 | 必填 | - | - | 后台日期格式 |
| client_date_format | 字符串 | 必填 | - | - | 前台用户显示的日期格式 |
| default_country | 字符串 | 必填 | - | - | 默认国家 |
| language | 字符串 | 必填 | - | - | 默认语言 |
| allow_user_language | 整型 | 必填 | 0 | - | 是否允许用户更改系统语言 |
| tel_cc_input | 整型 | 必填 | - | 0 | 是否自动格式化手机号码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置页面-支持 -- GET /admin/config_general/support

- controller: ``app\admin\controller\ConfigGeneralController::getSupport``
- desc: 常规设置页面-支持 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "nologin_send_ticket":"不登录是否可提交工单",
    "evaluate_ticket":"是否允许客户为工作人员的回复进行评价",
    "ticket_reply_order":"工单回复列表排序",
    "dl_incl_product":"包括产品下载",
  }
}
```

### 常规设置-支持页面提交 -- POST /admin/config_general/support

- controller: ``app\admin\controller\ConfigGeneralController::postSupport``
- desc: 常规设置-支持页面提交 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| nologin_send_ticket | 整型 | 必填 | 0 | - | 不登录是否可提交工单 |
| evaluate_ticket | 整型 | 必填 | 0 | - | 是否允许客户为工作人员的回复进行评价 |
| ticket_reply_order | 字符串 | 必填 | asc | - | 工单回复列表排序 |
| dl_incl_product | 整型 | 必填 | 0 | - | 包括产品下载 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置页面-推介 -- GET /admin/config_general/affiliate

- controller: ``app\admin\controller\ConfigGeneralController::getAffiliate``
- desc: 常规设置页面-推介 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "affiliate_enabled":"是否启用推介",
    "affiliate_invited":"是否启用应邀返利",
    "affiliate_invited_type":"应邀返利类型",
    "affiliate_invited_money":"应邀返利金额",
    "affiliate_bonusde_posit":"推介计划激活赠送金额",
    "affiliate_bates":"推介计划比例",
    "affiliate_type":"推介计划类型 1金额  2百分比",
    "affiliate_cookie":"推荐链接cookie有效期",
    "affiliate_withdraw":"提现最低金额",
    "affiliate_is_authentication":"是否要求先实名",
    "affiliate_delay_commission":"延迟订单支付的天数",
    "affiliate_is_reorder":"是否开启二次订单",
    "affiliate_reorder":"二次订单比例",
    "affiliate_reorder_type":"二次订单比例类型 1金额  2百分比",
    "affiliate_is_renew":"是否开启续费",
    "affiliate_renew":"续费比例",
    "affiliate_renew_type":"续费比例类型 1金额  2百分比",
  }
}
```

### 常规设置-推介页面提交 -- POST /admin/config_general/postaffiliate

- controller: ``app\admin\controller\ConfigGeneralController::postAffiliate``
- desc: 常规设置-推介页面提交 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| affiliate_enabled | 整型 | 非必填 | 0 | - | 是否启用推介 |
| affiliate_bonusde_posit | 浮点型 | 非必填 | 0 | - | 推介计划激活赠送金额 |
| affiliate_bates | 浮点型 | 非必填 | 0 | - | 推介计划比例 |
| affiliate_type | 整型 | 必填 | 0 | - | 推介计划比例类型1金额2百分比 |
| affiliate_cookie | 浮点型 | 非必填 | 0 | - | 推荐链接cookie有效期 |
| affiliate_withdraw | 浮点型 | 非必填 | 0 | - | 提现最低金额 |
| affiliate_is_authentication | 整型 | 非必填 | 0 | - | 是否要求先实名 |
| affiliate_delay_commission | 浮点型 | 非必填 | 0 | - | 延迟订单支付的天数 |
| affiliate_is_reorder | 整型 | 非必填 | 0 | - | 是否开启二次订单 |
| affiliate_reorder | 浮点型 | 非必填 | 0 | - | 二次订单比例 |
| affiliate_reorder_type | 整型 | 必填 | 0 | - | 二次订单比例类型1金额2百分比 |
| affiliate_is_renew | 整型 | 非必填 | 0 | - | 是否开启续费 |
| affiliate_renew | 浮点型 | 非必填 | 0 | - | 续费比例 |
| affiliate_renew_type | 整型 | 必填 | 0 | - | 续费比例比例类型1金额2百分比 |
| affiliate_url | 字符串 | 非必填 | 0 | - | 推荐地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 阶梯列表页 -- GET /admin/affladder

- controller: ``app\admin\controller\ConfigGeneralController::ladderList``
- desc: 阶梯列表页 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"阶梯列表页总数",
    "list":[{//阶梯列表页数据
      "group_name":"营业额",
      "bates":"比例",
      "is_flag":"是否开启",
    }]
  }
}
```

### 添加阶梯 -- POST /admin/aff/add_affladder

- controller: ``app\admin\controller\ConfigGeneralController::addAffLadder``
- desc: 添加阶梯 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| turnover | 字符串 | 必填 | 0 | - | 营业额 |
| bates | 浮点型 | 必填 | 0 | - | 比例 |
| is_flag | 整型 | 必填 | 0 | - | 是否开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑阶梯页面 -- GET /admin/aff/edit_affladderpage

- controller: ``app\admin\controller\ConfigGeneralController::editAffLadderPage``
- desc: 编辑阶梯页面 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 阶梯ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "spg":[{//分组
      "id":"组id",
      "turnover":"营业额",
      "bates":"比例",
      "is_flag":[{//是否开启
      }]
    }]
  }
}
```

### 编辑阶梯 -- POST /admin/aff/edit_affladder

- controller: ``app\admin\controller\ConfigGeneralController::editAffLadder``
- desc: 编辑阶梯 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |
| turnover | 字符串 | 必填 | 1 | - | 营业额 |
| bates | 浮点型 | 必填 | 0 | - | 提成比例 |
| is_flag | 整型 | 必填 | 0 | - | 是否开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除阶梯 -- GET /admin/aff/del_affladder

- controller: ``app\admin\controller\ConfigGeneralController::delAffLadder``
- desc: 删除阶梯 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 阶梯ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置页面-安全 -- GET /admin/config_general/safe

- controller: ``app\admin\controller\ConfigGeneralController::getSafe``
- desc: 常规设置页面-安全 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "required_pwstrength":"密码强度",
    "invalid_logins_banlength":"管理员无法登录时间",
    "pass_strength_list":"密码强度下拉选项",
  }
}
```

### 常规设置-安全页面提交 -- POST /admin/config_general/safe

- controller: ``app\admin\controller\ConfigGeneralController::postSafe``
- desc: 常规设置-安全页面提交 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| required_pwstrength | 字符串 | 非必填 | 0 | - | 密码强度 |
| invalid_logins_banlength | number | 非必填 | 0 | - | 管理员登录失败限制时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置页面-其他 -- GET /admin/config_general/other

- controller: ``app\admin\controller\ConfigGeneralController::getOther``
- desc: 常规设置页面-其他 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "clients_profoptional":"选填的客户配置字段(username",
    "clients_profuneditable":"锁定的客户配置字段(username",
    "show_cancel":"显示取消链接",
    "aff_report":"每月推介报告",
    "display_errors":"开启显示php错误",
    "sql_error_reporting":"SQL 调试模式",
    "hooks_debug_mode":"调试模式钩子",
  }
}
```

### 常规设置-其他页面提交 -- POST /admin/config_general/other

- controller: ``app\admin\controller\ConfigGeneralController::postOther``
- desc: 常规设置-其他页面提交 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| clients_profoptional | 数组 | 非必填 | 0 | - | 选填的客户配置字段(username:姓名,profession:职业,companyname:公司,email:邮箱,phonenumber:手机号) |
| clients_profuneditable | 数组 | 非必填 | 0 | - | 锁定的客户配置字段(username:姓名,profession:职业,companyname:公司,email:邮箱,phonenumber:手机号) |
| show_cancel | number | 必填 | 0 | - | - |
| aff_report | number | 必填 | 0 | - | - |
| display_errors | number | 非必填 | 0 | - | 1,0开启显示php错误 |
| sql_error_reporting | number | 非必填 | 0 | - | 1,0SQL |
| hooks_debug_mode | number | 非必填 | 0 | - | 1,0调试模式钩子 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置-充值页面 -- GET /admin/config_general/recharge

- controller: ``app\admin\controller\ConfigGeneralController::getRecharge``
- desc: 常规设置-充值页面 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| - | - | 非必填 | - | - | - |
| - | - | 非必填 | - | - | - |
| - | - | 非必填 | - | - | - |
| - | - | 非必填 | - | - | - |
| - | - | 非必填 | - | - | - |
| - | - | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "addfunds_enabled":"1.客户可以在用户中心添加余额至账户中",
  }
}
```

### 充值页面提交 -- POST /admin/config_general/recharge

- controller: ``app\admin\controller\ConfigGeneralController::postRecharge``
- desc: 充值页面提交 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| addfunds_enabled | 数组 | 非必填 | 0 | - | 1.0客户可以在用户中心添加余额至账户中 |
| addfunds_minimum | 数组 | 非必填 | 0 | - | 客户在单笔账单中可以支付的最小金额 |
| addfunds_maximum | number | 必填 | 0 | - | 客户在单笔账单中可以支付的最大金额 |
| addfunds_maximum_balance | number | 必填 | 0 | - | 最高余额 |
| addfunds_require_order | number | 非必填 | 0 | - | 1,0需要激活的订单 |
| no_auto_apply_credit | number | 非必填 | 0 | - | 1,0自动应用经常性发票 |
| credit_on_downgrade | number | 非必填 | 0 | - | 1,0降级退还至余额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 常规设置-账单页面 -- GET /admin/config_general/invoice

- controller: ``app\admin\controller\ConfigGeneralController::getInvoice``
- desc: 常规设置-账单页面 -- 上官🔪

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "in_circulation_create":"在每个周期内都生成账单，即使之前的账单未支付",
    "in_pdf":"送包含 PDF 版本附件的账单邮件",
    "in_save_user_info":"生成账单时保留客户资料，防止客户资料更改影响现有的账单",
    "in_batch_pay":" 1,0在客户中心启用批量账单支付选项",
    "in_select_payment":"允许客户选择所需的支付网关",
    "in_unpaid_tick":" 未付订单启用形式发票",
    "in_continuous_pay_num":"启用自动连续付款账单号码",
    "in_continuous_pay_num_type":"可用的自动插入标签： {YEAR} {MONTH} {DAY} {NUMBER}",
    "in_overdue_fine":"滞纳金类型",
    "in_overdue_fine_min":"输入如果计算出来的滞纳金小于此值时，应收取的最低滞纳金金额",
  }
}
```

### 账单配置 -- POST /admin/config_general/invoice

- controller: ``app\admin\controller\ConfigGeneralController::postInvoice``
- desc: 账单配置 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| in_circulation_create | 整型 | 非必填 | 0 | - | 在每个周期内都生成账单，即使之前的账单未支付 |
| in_pdf | 整型 | 非必填 | 0 | - | 送包含 |
| in_save_user_info | 整型 | 必填 | 0 | - | - |
| in_batch_pay | 整型 | 必填 | 0 | - | - |
| in_select_payment | 整型 | 非必填 | 0 | - | 允许客户选择所需的支付网关 |
| in_unpaid_tick | 整型 | 非必填 | 0 | - | 未付订单启用形式发票 |
| in_continuous_pay_num | 整型 | 非必填 | 0 | - | 启用自动连续付款账单号码 |
| in_continuous_pay_num_type | 字符串 | 非必填 | 0 | - | 可用的自动插入标签： |
| in_overdue_fine | 字符串 | 非必填 | 0 | - | 滞纳金类型 |
| in_overdue_fine_min | 字符串 | 非必填 | 0 | - | 输入如果计算出来的滞纳金小于此值时，应收取的最低滞纳金金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 注册登录--页面 -- GET /admin/config_general/register_login_page

- controller: ``app\admin\controller\ConfigGeneralController::registerLoginPage``
- desc: 注册登录--页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 注册登录 -- POST /admin/config_general/register_login

- controller: ``app\admin\controller\ConfigGeneralController::registerLogin``
- desc: 注册登录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| allow_phone | 字符串 | 非必填 | 0 | - | 1允许手机注册登录,0否 |
| allow_email | 字符串 | 非必填 | 0 | - | 1允许邮箱注册登录，0否 |
| allow_wechat | 字符串 | 非必填 | 0 | - | 1允许微信注册登录，0否 |
| allow_email_register_code | 字符串 | 非必填 | 0 | - | 1允许邮件注册发送验证码，0否 |
| wechat_login_appid | 字符串 | 非必填 | 0 | - | 微信注册登录APPID |
| wechat_login_secret | 字符串 | 非必填 | 0 | - | 微信注册登录秘钥 |
| clients_profoptional | 数组 | 非必填 | 0 | - | 选填的客户配置字段(username:姓名,profession:职业,companyname:公司,email:邮箱,phonenumber:手机号) |
| allow_register_phone | 整型 | 非必填 | 0 | - | - |
| allow_register_email | 整型 | 非必填 | 0 | - | - |
| allow_register_wechat | 整型 | 非必填 | 0 | - | - |
| allow_login_phone | 整型 | 非必填 | 0 | - | - |
| allow_login_email | 整型 | 非必填 | 0 | - | - |
| allow_login_wechat | 整型 | 非必填 | 0 | - | - |
| login_register_custom_require[0][name] | 整型 | 非必填 | 0 | - | 名称 |
| login_register_custom_require[0][require] | 整型 | 非必填 | 0 | - | 是否必填 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改最大错误次数 -- POST /admin/config_general/loginErrorMax

- controller: ``app\admin\controller\ConfigGeneralController::postLoginErrorMax``
- desc: 修改最大错误次数 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| login_error_max_num | 字符串 | 非必填 | 0 | - | 1允许手机注册登录,0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 验证码设置 -- GET /admin/config_general/captcha_page

- controller: ``app\admin\controller\ConfigGeneralController::getcaptcha_page``
- desc: 验证码设置 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 验证码设置 -- POST /admin/config_general/register_login_captcha

- controller: ``app\admin\controller\ConfigGeneralController::postregister_login_captcha``
- desc: 验证码设置 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| is_captcha | 字符串 | 非必填 | 0 | - | 是否开启验证码 |
| captcha_length | 字符串 | 非必填 | 0 | - | 验证码长度 |
| captcha_combination | 字符串 | 非必填 | 0 | - | 验证码组合1数字2字母加数字3字母 |
| allow_register_email_captcha | 字符串 | 非必填 | 0 | - | 允许邮件注册显示验证码 |
| allow_register_phone_captcha | 字符串 | 非必填 | 0 | - | 允许手机注册显示验证码 |
| allow_login_phone_captcha | 字符串 | 非必填 | 0 | - | 允许手机登录显示验证码 |
| allow_login_email_captcha | 数组 | 非必填 | 0 | - | 允许邮件登录显示验证码 |
| allow_login_code_captcha | 整型 | 非必填 | 0 | - | 允许验证码登录显示验证码 |
| allow_login_id_captcha | 整型 | 非必填 | 0 | - | 允许id登录显示验证码 |
| allow_phone_forgetpwd_captcha | 整型 | 非必填 | 0 | - | 允许手机忘记密码显示验证码 |
| allow_email_forgetpwd_captcha | 整型 | 非必填 | 0 | - | 允许邮件忘记密码显示验证码 |
| allow_resetpwd_captcha | 整型 | 非必填 | 0 | - | 允许重置密码显示验证码 |
| allow_phone_bind_captcha | 整型 | 非必填 | 0 | - | 允许手机绑定显示验证码 |
| allow_email_bind_captcha | 整型 | 非必填 | 0 | - | 允许邮件绑定显示验证码 |
| allow_cancel_sms_captcha | 整型 | 非必填 | 0 | - | 允许取消登录短信提醒显示验证码 |
| allow_cancel_email_captcha | 整型 | 非必填 | 0 | - | 允许取消登录邮件提醒显示验证码 |
| allow_login_admin_captcha | 整型 | 非必填 | 0 | - | 允许后台登录显示验证码 |
| allow_setpwd_captcha | 整型 | 非必填 | 0 | - | 设置密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 财务设置--页面 -- GET /admin/config_general/invoice_page

- controller: ``app\admin\controller\ConfigGeneralController::invoicePage``
- desc: 财务设置--页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "upgrade_down_product_config":"降级退款至余额",
    "allow_custom_invoice_id":"允许自定义账单ID",
    "custom_invoice_id_start":"下个账单ID起始值",
    "voucher_manager":"发票管理",
    "buy_product_must_bind_phone":"购买商品必须绑定手机",
  }
}
```

### 财务设置 -- POST /admin/config_general/invoice_post

- controller: ``app\admin\controller\ConfigGeneralController::invoicePost``
- desc: 财务设置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| upgrade_down_product_config | 字符串 | 非必填 | 0 | - | 1允许降级退款,0否 |
| allow_custom_invoice_id | 字符串 | 非必填 | 0 | - | 1允许自定义账单ID,0否(勾选后，才展示下面的字段) |
| custom_invoice_id_start | 字符串 | 非必填 | 0 | - | 下个账单ID起始值 |
| voucher_manager | 整型 | 非必填 | 0 | - | 发票管理:1开启,0关闭 |
| buy_product_must_bind_phone | 整型 | 非必填 | 0 | - | 购买商品必须绑定手机:1开启,0关闭 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品分类--页面 -- GET /admin/config_general/productgroup_page

- controller: ``app\admin\controller\ConfigGeneralController::productgroupPage``
- desc: 产品分类--页面 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "groupname":"产品组菜单名",
    "fa_icon":"图标",
  }
}
```

### 产品分类排序 -- GET /admin/config_general/navgrouporder

- controller: ``app\admin\controller\ConfigGeneralController::navGroupOrder``
- desc: 产品分类排序 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 0 | - | 拖曳id |
| suf_id | 整型 | 非必填 | 0 | - | 拖曳后 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品分类--列表 -- GET /admin/config_general/productgroup_list

- controller: ``app\admin\controller\ConfigGeneralController::productgroupList``
- desc: 产品分类--列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 0 | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "groupname":"产品组菜单名",
    "fa_icon":"图标",
  }
}
```

### 产品分类提交 -- POST /admin/config_general/productgroup

- controller: ``app\admin\controller\ConfigGeneralController::productGroupPost``
- desc: 产品分类提交 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 0 | - | id |
| toid | 整型 | 非必填 | 0 | - | toid |
| data | 数组 | 非必填 | 0 | - | 数组 |
| data.id | 整型 | 非必填 | 0 | - | id |
| data.groupname | 字符串 | 非必填 | 0 | - | 分组名 |
| data.fa_icon | 字符串 | 非必填 | 0 | - | 图标 |
| type | 整型 | 非必填 | 0 | - | 1删除0其他 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### API设置 -- GET /admin/config_general/apiconfig

- controller: ``app\admin\controller\ConfigGeneralController::getApiConfig``
- desc: API设置 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".allow_resource_api":"是否开启资源api",
  }
}
```

### 魔方财务资源api设置页面 -- POST /admin/config_general/apiconfig

- controller: ``app\admin\controller\ConfigGeneralController::postApiConfig``
- desc: 魔方财务资源api设置页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| allow_resource_api | 整型 | 非必填 | 0 | 是否开启资源api:0否，1是 | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 二次验证设置 -- GET /admin/config_general/secondverify

- controller: ``app\admin\controller\ConfigGeneralController::getSecondVerify``
- desc: 二次验证设置 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".second_verify_home":"是否开启前台二次验证",
    ".home_action":"前台所有动作",
    ".second_verify_action_home":"前台已选动作",
    ".home_type":"二次验证方式（所有）",
    ".second_verify_action_home_type":"已选二次验证方式",
    "second_verify_admin":"是否开启后台二次验证",
    "admin_action":"后台所有动作",
    "second_verify_action_admin":"后台已选动作",
  }
}
```

### 二次验证设置 -- POST /admin/config_general/secondverify

- controller: ``app\admin\controller\ConfigGeneralController::postSecondVerify``
- desc: 二次验证设置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| second_verify_home | tinyint | 非必填 | 0 | - | 前台是否开启二次验证:1开启默认,0关闭 |
| second_verify_action_home[] | 数组 | 非必填 | 0 | - | 前台动作,数组 |
| second_verify_action_home_type[] | 数组 | 非必填 | 0 | - | 前台验证方式 |
| second_verify_admin | tinyint | 非必填 | 0 | - | 后台是否开启二次验证 |
| second_verify_action_admin[] | 数组 | 非必填 | 0 | - | 后台动作 |
| code | 字符串 | 非必填 | 0 | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订购商品设置页面 -- GET /admin/config_general/buy_product_page

- controller: ``app\admin\controller\ConfigGeneralController::getBuyProductPage``
- desc: 订购商品设置页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| buy_product_must_bind_phone | tinyint | 非必填 | 0 | - | 购买商品必须绑定手机:1开启,0关闭 |
| certifi_isrealname | tinyint | 非必填 | 0 | - | 是否实名 |
| order_page_style | tinyint | 非必填 | 0 | - | 订购页样式0默认,1省份地区 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订购商品设置 -- POST /admin/config_general/buy_product

- controller: ``app\admin\controller\ConfigGeneralController::postBuyProduct``
- desc: 订购商品设置 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| buy_product_must_bind_phone | tinyint | 非必填 | 0 | - | 购买商品必须绑定手机:1开启,0关闭 |
| certifi_isrealname | 字符串 | 必填 | - | - | 是否实名 |
| order_page_style | tinyint | 非必填 | 0 | - | 0默认,1省份地区 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### Debug模式页面 -- GET /admin/config_general/debugmodel

- controller: ``app\admin\controller\ConfigGeneralController::getDebugModel``
- desc: Debug模式页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "shd_debug_model":"1开启debug模式",
    "shd_debug_model_auth":"debug模式授权码",
    "shd_debug_model_expire_time":"到期时间",
  }
}
```

### Debug模式 -- POST /admin/config_general/debugmodel

- controller: ``app\admin\controller\ConfigGeneralController::postDebugModel``
- desc: Debug模式 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_debug_model | tinyint | 非必填 | 0 | - | 1开启debug模式 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 应用商店设置

### 应用管理设置页面 -- GET /admin/config_market/app_manage_index

- controller: ``app\admin\controller\ConfigMarketController::appManageIndex``
- desc: 应用管理设置页面 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".shd_zjmf_finance":"魔方财务,1开启,0关闭",
    ".shd_zjmf_cloud":"魔方云,1开启,0关闭",
    ".shd_zjmf_dcim":"魔方DCIM,1开启,0关闭",
    ".developer_app_type":"应用类型",
  }
}
```

### 应用管理设置页面提交 -- POST /admin/config_market/app_manage_index_post

- controller: ``app\admin\controller\ConfigMarketController::appManageIndexPost``
- desc: 应用管理设置页面提交 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_zjmf_finance | 整型 | 必填 | 1 | - | 魔方财务 |
| shd_zjmf_cloud | 字符串 | 必填 | 1 | - | 魔方云 |
| shd_zjmf_dcim | 字符串 | 必填 | 1 | - | 魔方DCIM |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提现管理设置页面 -- GET /admin/config_market/withdraw_manage_index

- controller: ``app\admin\controller\ConfigMarketController::withdrawManageIndex``
- desc: 提现管理设置页面 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".shd_allow_withdraw":"允许提现,1开启,0关闭",
    ".allow_withdraw_bank":"1允许银行卡提现,0否",
    ".allow_withdraw_alipay":"1允许支付宝提现,0否",
    ".allow_withdraw_bank_BOC":"1允许中国银行提现,0否",
    ".allow_withdraw_bank_ICBC":"1允许中国工商银行提现,0否",
    ".allow_withdraw_bank_ABC":"1允许中国农业银行提现,0否",
    ".allow_withdraw_bank_CCB":"1允许中国建设银行提现,0否",
    ".allow_withdraw_bank_PSBC":"1允许中国邮政银行提现,0否",
    ".minimum_withdrawal_amount":"最低提现金额",
    ".withdrawal_fee":"提现手续费",
    ".withdraw_method":"提现方式",
    ".withdraw_account_bank":"提现银行",
  }
}
```

### 提现管理设置页面提交 -- POST /admin/config_market/withdraw_manage_index_post

- controller: ``app\admin\controller\ConfigMarketController::withdrawManageIndexPost``
- desc: 提现管理设置页面提交 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_allow_withdraw | 整型 | 必填 | 1 | - | 允许提现 |
| allow_withdraw_bank | 字符串 | 必填 | 1 | - | 允许银行卡提现 |
| allow_withdraw_alipay | 字符串 | 必填 | 1 | - | 允许支付宝提现 |
| allow_withdraw_bank_BOC | 字符串 | 必填 | 1 | - | 允许中国银行提现 |
| allow_withdraw_bank_ICBC | 字符串 | 必填 | 1 | - | 允许中国工商银行提现 |
| allow_withdraw_bank_ABC | 字符串 | 必填 | 1 | - | 允许中国农业银行提现 |
| allow_withdraw_bank_CCB | 字符串 | 必填 | 1 | - | 允许中国建设银行提现 |
| allow_withdraw_bank_PSBC | 字符串 | 必填 | 1 | - | 允许中国邮政银行提现 |
| minimum_withdrawal_amount | 字符串 | 必填 | 1 | - | 最低提现金额 |
| withdrawal_fee | 字符串 | 必填 | 1 | - | 提现手续费 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 活动banner列表 -- GET /admin/config_market/activity_banner_list

- controller: ``app\admin\controller\ConfigMarketController::activityBannerList``
- desc: 活动banner列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "activity_banner":[{//活动banner
      "id":"活动banner",
      "IDname":"名称desc:描述banner:banner图url:跳转地址start_time:开始时间end_time:结束时间status:状态",
    }]
  }
}
```

### 新增活动banner -- POST /admin/config_market/activity_banner

- controller: ``app\admin\controller\ConfigMarketController::postActivityBanner``
- desc: 新增活动banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| desc | 字符串 | 必填 | - | - | 描述 |
| banner | 字符串 | 必填 | - | - | banner图 |
| url | 字符串 | 必填 | - | - | banner跳转地址 |
| start_time | 整型 | 必填 | - | - | 活动开始时间 |
| end_time | 整型 | 必填 | - | - | 活动结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改活动banner -- PUT /admin/config_market/activity_banner

- controller: ``app\admin\controller\ConfigMarketController::putActivityBanner``
- desc: 修改活动banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| desc | 字符串 | 必填 | - | - | 描述 |
| banner | 字符串 | 必填 | - | - | banner图 |
| url | 字符串 | 必填 | - | - | banner跳转地址 |
| start_time | 整型 | 必填 | - | - | 活动开始时间 |
| end_time | 整型 | 必填 | - | - | 活动结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除活动banner -- DELETE /admin/config_market/activity_banner

- controller: ``app\admin\controller\ConfigMarketController::deleteActivityBanner``
- desc: 删除活动banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 活动ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 系统相关

### 获取系统信息 -- GET admin/system/commoninfo

- controller: ``app\admin\controller\SystemController::getcommoninfo``
- desc: 获取系统信息 -- xiong

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取版本更新内容 -- GET admin/system/updatecontent

- controller: ``app\admin\controller\SystemController::getUpdateContent``
- desc: 获取版本更新内容 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 系统信息 -- GET admin/system/info

- controller: ``app\admin\controller\SystemController::getInfo``
- desc: 系统信息 -- 上官刃

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "server_ip":"服务器ip",
    "server_name":"域名",
    "server_port":"端口号",
    "server_version":"服务器系统版本",
    "server_system":" 服务器操作系统",
    "php_version":"php版本",
    "include_path":"获取PHP安装路径",
    "php_sapi_name":"PHP运行方式",
    "now_time":"服务器时间",
    "upload_max_filesize":"服务器上传限制",
    "max_execution_time":"服务脚本最大执行时间",
    "memory_limit":"内存占用",
    "processor_identifier":"脚本运行占用最大内存",
    "system_root":"系统根目录",
    "http_accept_language":"获取服务器语言",
    "system_token":"系统唯一识别码",
    "install_version":"系统当前版本",
    "last_version":"系统最新版本",
    "mysql_version":"数据库版本",
    "system_version_type":"版本类型：beta测试版 stable稳定版",
    "zjmf_system_version_type_last":"当前版本：beta测试版 stable稳定版",
  }
}
```

### 系统信息:最新版本号,授权类型 -- GET admin/system/lastversion

- controller: ``app\admin\controller\SystemController::getLastVersion``
- desc: 系统信息:最新版本号,授权类型 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "last_version":"系统最新版本",
    "last_version_check":"no_response时 ，表示未检测到最新版本",
    "license_type":"授权类型",
  }
}
```

### php信息 -- GET admin/system/phpinfo

- controller: ``app\admin\controller\SystemController::getPhpInfo``
- desc: php信息 -- 上官刃

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "report_array":"php信息html",
  }
}
```

### 数据库状态数据 -- GET admin/system/databaseinfo

- controller: ``app\admin\controller\SystemController::getDatabaseInfo``
- desc: 数据库状态数据 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "report_array":[{//表数据
      "name":"表名称",
      "rows":"表行数",
      "size":"表大小",
    }]
    "total_count":"总表数",
    "total_rows":"总行数",
    "total_size":"总大小",
  }
}
```

### 优化数据表 -- GET admin/system/optimizetables

- controller: ``app\admin\controller\SystemController::postOptimizeTables``
- desc: 优化数据表 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 下载数据库备份 -- POST admin/system/downdatabackup

- controller: ``app\admin\controller\SystemController::postDownDataBackup``
- desc: 下载数据库备份 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 切换版本 -- POST admin/system/toggleversion

- controller: ``app\admin\controller\SystemController::postToggleVersion``
- desc: 切换版本 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更新系统 -- GET admin/system/autoupdate

- controller: ``app\admin\controller\SystemController::getAutoUpdate``
- desc: 更新系统 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检测系统更新进度 -- GET admin/system/checkautoupdate

- controller: ``app\admin\controller\SystemController::getCheckAutoUpdate``
- desc: 检测系统更新进度 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更换授权码 -- GET admin/system/authorize

- controller: ``app\admin\controller\SystemController::getAuthorize``
- desc: 更换授权码 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 更换授权码 -- PUT admin/system/license

- controller: ``app\admin\controller\SystemController::putLicense``
- desc: 更换授权码 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| license | 字符串 | 必填 | - | - | 授权码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 数据迁移 -- GET admin/system/datamigrate

- controller: ``app\admin\controller\SystemController::getDataMigrate``
- desc: 数据迁移 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "last_version":"系统最新版本",
  }
}
```

### 路由菜单 -- GET admin/system/systemAuthRuleLanguage

- controller: ``app\admin\controller\SystemController::getSystemAuthRuleLanguage``
- desc: 路由菜单 -- x

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "array":"",
  }
}
```


---

## 日志记录

### 系统日志 -- GET /admin/log_record/systemlog

- controller: ``app\admin\controller\LogRecordController::getSystemLog``
- desc: 系统日志 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当天日志 |
| search_name | 字符串 | 非必填 | - | - | 查找用户名 |
| search_desc | 字符串 | 非必填 | - | - | 通过描述查询 |
| search_ip | 字符串 | 非必填 | - | - | ip地址查询 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "description":"描述",
      "new_desc":"带跳转链接的描述",
      "user":"用户",
      "ipaddr":"ip地址",
    }]
    "search_time":"搜索特定某一天",
    "search_name":"搜索操作用户",
    "search_desc":"搜索描述",
    "search_ip":"搜索ip地址",
    "pagecount":"每页显示条数",
    "page":"当前页码",
    "orderby":"排序字段",
    "sorting":"asc/desc,顺序或倒叙",
    "total_page":"总页码",
    "count":"总新闻数量",
  }
}
```

### 自动任务日志 -- GET /admin/log_record/cronsystemlog

- controller: ``app\admin\controller\LogRecordController::getCronSystemLog``
- desc: 自动任务日志 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当天日志 |
| search_name | 字符串 | 非必填 | - | - | 查找用户名 |
| search_desc | 字符串 | 非必填 | - | - | 通过描述查询 |
| search_ip | 字符串 | 非必填 | - | - | ip地址查询 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "description":"描述",
      "new_desc":"带跳转链接的描述",
      "user":"用户",
      "ipaddr":"ip地址",
    }]
    "search_time":"搜索特定某一天",
    "search_name":"搜索操作用户",
    "search_desc":"搜索描述",
    "search_ip":"搜索ip地址",
    "pagecount":"每页显示条数",
    "page":"当前页码",
    "orderby":"排序字段",
    "sorting":"asc/desc,顺序或倒叙",
    "total_page":"总页码",
    "count":"总新闻数量",
  }
}
```

### 系统管理员登录日志 -- GET /admin/log_record/adminlog

- controller: ``app\admin\controller\LogRecordController::getAdminLog``
- desc: 系统管理员登录日志 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当天日志 |
| search_name | 字符串 | 非必填 | - | - | 查找用户名 |
| search_ip | 字符串 | 非必填 | - | - | ip地址查询 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "page":"页码",
    "log_list":[{//日志数据
      "admin_username":"管理员名",
      "logintime":"登录时间",
      "logouttime":"注销时间",
      "ipaddress":"ip",
      "lastvisit":"最后访问",
    }]
  }
}
```

### 通知日志 -- GET /admin/log_record/notifylog

- controller: ``app\admin\controller\LogRecordController::getNotifyLog``
- desc: 通知日志 -- 萧十一郎

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| search_time | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| message | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| type | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "page":"页码",
    "log_list":[{//日志数据
      "to":"接收人create_time:发送时间message:消息内容type:类型（微信，短信，邮件）subject:主题uid:用户id",
    }]
  }
}
```

### 系统邮件日志 -- GET /admin/log_record/emaillog

- controller: ``app\admin\controller\LogRecordController::getEmailLog``
- desc: 系统邮件日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | ，顺序或倒叙 |
| search_time | 字符串 | 非必填 | - | - | ，查询时间 |
| subject | 字符串 | 非必填 | - | - | ，主题 |
| username | 字符串 | 非必填 | - | - | ，收件人 |
| uid | 字符串 | 非必填 | - | - | ，收件人id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "subject":"主题",
      "username":"收件人",
      "status":"状态1成功",
      "fail_reason":"原因",
    }]
  }
}
```

### 查看邮件信息 -- GET /admin/log_record/emaildetail/:id

- controller: ``app\admin\controller\LogRecordController::getEmailDetail``
- desc: 查看邮件信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "detail":[{//日志数据
      "username":"发送给to:邮件subject:主题message:信息",
    }]
  }
}
```

### 系统短信日志 -- GET /admin/log_record/smslog

- controller: ``app\admin\controller\LogRecordController::getSmsLog``
- desc: 系统短信日志 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| search_time | 字符串 | 非必填 | asc | - | - |
| phone | 整型 | 非必填 | - | - | 手机 |
| username | 字符串 | 非必填 | - | - | 姓名 |
| uid | 整型 | 非必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "phone":"手机",
      "content":"内容",
      "phone_code":"手机验证码",
      "uid":"接收人",
      "username":"接收人",
      "status":"0失败1成功",
      "fail_reason":"失败原因",
    }]
  }
}
```

### 系统短信日志 -- GET /admin/log_record/smslogm

- controller: ``app\admin\controller\LogRecordController::getSmsLogM``
- desc: 系统短信日志 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| uid | 整型 | 非必填 | - | - | uid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "log_list":[{//日志数据
      "create_time":"时间",
      "phone":"手机",
      "content":"内容",
      "phone_code":"手机验证码",
      "uid":"接收人",
      "username":"接收人",
      "status":"0失败1成功",
      "fail_reason":"失败原因",
    }]
  }
}
```

### 站内信日志列表 -- GET /admin/log_record/system_message_log

- controller: ``app\admin\controller\LogRecordController::getSystemMessageLog``
- desc: 站内信日志列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| search_time | 数组 | 非必填 | - | - | 时间段search_time[0] |
| read_type | 整型 | 非必填 | -1 | - | 状态：-1全部，0-未读，1-已读 |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字-主题 |
| username | 字符串 | 非必填 | - | - | 搜索关键字-客户名 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| uid | 整型 | 非必填 | - | - | uid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### Api日志列表 -- GET /admin/log_record/api_log

- controller: ``app\admin\controller\LogRecordController::getApiLog``
- desc: Api日志列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字 |
| time | 字符串 | 非必填 | - | - | 按时间搜索 |
| uid | 字符串 | 非必填 | - | - | 按用户搜索,公共接口调列表,与产品内页客户列表那里调用相同 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序字段 |
| sorting | 字符串 | 非必填 | asc | - | desc/asc，顺序或倒叙 |
| uid | 整型 | 非必填 | - | - | uid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除日志页面 -- GET /admin/log_record/delete_log_page

- controller: ``app\admin\controller\LogRecordController::getDeleteLogPage``
- desc: 删除日志页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 非必填 | - | - | 日志类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "type":"类型",
  }
}
```

### 删除日志页面(二次确认) -- GET /admin/log_record/affirm_delete_log_page

- controller: ``app\admin\controller\LogRecordController::getAffirmDeleteLogPage``
- desc: 删除日志页面(二次确认) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | 日志类型 |
| time | 字符串 | 必填 | - | - | 时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除日志 -- DELETE /admin/log_record/delete_log

- controller: ``app\admin\controller\LogRecordController::deleteLog``
- desc: 删除日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | 日志类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 文件上传

### 富文本框上传图片 -- POST admin/upload

- controller: ``app\admin\controller\UploadController::upload``
- desc: 富文本框上传图片 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| image | file | 必填 | 0 | - | 文件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```

### 上传图片 -- POST admin/upload_image

- controller: ``app\admin\controller\UploadController::uploadImage``
- desc: 上传图片 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| image|file | file | 必填 | 0 | - | 图片 |
| type | 字符串 | 必填 | 0 | - | 类型,如avatar,servers,attachment,contract(合同)富文本上传，直接获取地址展示 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```

### 上传文件 --  POST admin/upload_file

- controller: ``app\admin\controller\UploadController::uploadFile``
- desc: 上传文件 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| filename|file | file | 必填 | 0 | - | 文件 |
| type | 字符串 | 必填 | 0 | - | 类型,如avatar,servers |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```

### 上传授权书 --  POST admin/upload_author

- controller: ``app\admin\controller\UploadController::uploadAuthor``
- desc: 上传授权书 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| filename|file | file | 必填 | 0 | - | 文件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```


---

## 插件管理

### 插件列表 -- GET /admin/pl_index/[:moduleName]/

- controller: ``app\admin\controller\PluginController::plIndex``
- desc: 插件列表 -- shd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| moduleName | 字符串 | 非必填 | - | - | 插件模块名:gateways支付网关,addons插件,certification实名认证 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"id",
    ".status":"'状态;1",
    ".has_admin":"是否有后台管理,0",
    ".name":"'插件标识名,英文字母(惟一)',",
    ".title":"名称",
    ".description":"描述",
    ".module":"所属模块",
  }
}
```

### 修改插件排序 -- POST /admin/pl_sort/[:moduleName]/

- controller: ``app\admin\controller\PluginController::plSort``
- desc: 修改插件排序 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 插件ID |
| pre_id | 整型 | 必填 | - | - | 移动后前一个插件ID |
| moduleName | 整型 | 必填 | - | - | 插件模块名:gateways,addons,servers |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 复制线下支付插件 -- POST /admin/pl_copy

- controller: ``app\admin\controller\PluginController::plCopy``
- desc: 复制线下支付插件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 插件安装 -- POST /admin/pl_install

- controller: ``app\admin\controller\PluginController::plInstall``
- desc: 插件安装 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | - | - | 如GlobalWxpay |
| module | 整型 | 必填 | '' | - | 如gateways |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 插件卸载 -- POST /admin/pl_uninstall

- controller: ``app\admin\controller\PluginController::plUninstall``
- desc: 插件卸载 -- 上官刀

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 卸载插件id |
| default | 整型 | 必填 | - | - | 默认id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 禁用(启用)插件 -- POST /admin/pl_toggle

- controller: ``app\admin\controller\PluginController::plToggle``
- desc: 禁用(启用)插件 -- shd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 插件id |
| default | 整型 | 非必填 | - | - | 选择的默认插件id |
| enable | 字符串 | 必填 | 1 | - | 二选一(值为1) |
| disable | 字符串 | 必填 | 1 | - | 二选一(值为1) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 配置插件 -- GET /admin/pl_setting/[:module]/:id

- controller: ``app\admin\controller\PluginController::plSetting``
- desc: 配置插件 -- shd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | - | - | 插件模id |
| module | 字符串 | 必填 | - | - | 插件模块名:gateways |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"id",
    ".status":"'状态;1",
    ".name":"插件名,",
    ".module":"所属网关名称,",
    "config":[{//配置
      "AppId":[{//配置字段
        "title":"名称.type:字段类型.value:字段值.tip:提示信息",
      }]
    }]
  }
}
```

### 保存插件配置 -- POST /admin/pl_setting_post

- controller: ``app\admin\controller\PluginController::plSettingPost``
- desc: 保存插件配置 -- shd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | - | - | 插件id |
| module | 字符串 | 必填 | - | - | 插件模块名:gateways |
| config[字段] | 字符串 | 必填 | - | - | 配置字段值(如：module_name,seller_id,app_id等) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 插件更新 -- POST /admin/pl_update

- controller: ``app\admin\controller\PluginController::plUpdate``
- desc: 插件更新 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | - | - | 如GlobalWxpay |
| module | 整型 | 必填 | '' | - | 如gateways支付网关,addons插件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台服务模块

### 获取所有模块 -- GET admin/provision/list

- controller: ``app\admin\controller\ProvisionController::getModules``
- desc: 获取所有模块 --      huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".value":"值",
    ".name":"显示的名称",
  }
}
```

### 获取模块metadata -- GET /admin/provision/metadata

- controller: ``app\admin\controller\ProvisionController::getMetaData``
- desc: 获取模块metadata --       huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 非必填 | - | - | 模块名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".DefaultNonSSLPort":"默认非ssl端口",
    ".DefaultSSLPort":"默认ssl端口",
  }
}
```

### 获取模块设置 -- GET /admin/provision/:id

- controller: ``app\admin\controller\ProvisionController::getModuleConfig``
- desc: 获取模块设置 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | - | - | 服务器组id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".name":"名称",
    ".placeholder":"placeholder",
    ".description":"描述",
    ".default":"默认值",
    ".type":"类型text,password,yesno(值 on|off),radio,dropdown,textarea,",
    ".options":"选项,单选和下拉才有",
    ".rows":"文本域属性rows",
    ".cols":"文本域属性cols",
    "module_meta.APIVersion":"API版本",
    "module_meta.HelpDoc":"帮助文档地址",
  }
}
```

### 执行开通,暂停,解除暂停,删除,修改密码 -- POST /admin/provision/default

- controller: ``app\admin\controller\ProvisionController::execute``
- desc: 执行开通,暂停,解除暂停,删除,修改密码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostid |
| func | 字符串 | 必填 | - | - | 执行的方法create开通,suspend暂停,unsuspend解除暂停,terminate删除 |
| reason_type | 字符串 | 非必填 | - | - | 暂停原因类型 |
| reason | 字符串 | 非必填 | - | - | 暂停原因 |
| send | 整型 | 非必填 | 0 | - | 是否发送暂停|解除暂停邮件 |
| password | 字符串 | 非必填 | 0 | - | 重置密码的新密码 |
| os | 整型 | 非必填 | 0 | - | 操作系统id(func=reinstall) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "url":"获取vnc返回的地址(func=vnc)",
    "status":"机器状态(func=status)",
    "des":"机器描述(func=status)",
  }
}
```

### 执行模块自定义方法 -- POST /admin/provision/custom

- controller: ``app\admin\controller\ProvisionController::execAdmin``
- desc: 执行模块自定义方法 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务id |
| func | 整型 | 必填 | - | - | 自定义方法名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```


---

## 后台对接DCIM管理

### 添加服务器 --  POST /admin/dcim/server

- controller: ``app\admin\controller\DcimController::addServer``
- desc: 添加服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| hostname | 字符串 | 必填 | - | - | 地址(IP或者域名) |
| username | 字符串 | 非必填 | - | - | 用户名 |
| password | 字符串 | 非必填 | - | - | 密码 |
| port | 字符串 | 非必填 | - | - | 端口 |
| secure | 整型 | 非必填 | 0 | - | 是否https(0不是1是) |
| disabled | 整型 | 非必填 | 1 | - | 是否启用(0启用1禁用) |
| user_prefix | 字符串 | 非必填 | - | - | 财务标识 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改服务器 --  PUT /admin/dcim/server

- controller: ``app\admin\controller\DcimController::editServer``
- desc: 修改服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |
| name | 字符串 | 必填 | - | - | 名称 |
| hostname | 字符串 | 必填 | - | - | 地址(IP或者域名) |
| username | 字符串 | 非必填 | - | - | 用户名 |
| password | 字符串 | 非必填 | - | - | 密码 |
| port | 字符串 | 非必填 | - | - | 端口 |
| secure | 整型 | 非必填 | 0 | - | 是否https(0不是1是) |
| disabled | 整型 | 非必填 | 1 | - | 是否启用(0启用1禁用) |
| reinstall_times | 整型 | 非必填 | 0 | - | 重装次数 |
| buy_times | 整型 | 非必填 | 0 | - | 启用购买重装 |
| reinstall_price | 浮点型 | 非必填 | 0 | - | 重装价格 |
| bill_type | 字符串 | 非必填 | - | - | 流量计费方式(month自然月last_30days订购日至下月) |
| percent | 数组 | 非必填 | - | - | 流量提醒比例(单个比例范围1-100) |
| tid | 数组 | 非必填 | - | - | 流量提醒邮件模板 |
| traffic | 字符串 | 非必填 | - | - | 流量图(on开启off关闭) |
| kvm | 字符串 | 非必填 | - | - | kvm(on开启off关闭) |
| ikvm | 字符串 | 非必填 | - | - | ikvm(on开启off关闭) |
| bmc | 字符串 | 非必填 | - | - | 重置BMC(on开启off关闭) |
| reinstall | 字符串 | 非必填 | - | - | 重装系统(on开启off关闭) |
| reboot | 字符串 | 非必填 | - | - | 重启(on开启off关闭) |
| on | 字符串 | 非必填 | - | - | 开机(on开启off关闭) |
| off | 字符串 | 非必填 | - | - | 关机(on开启off关闭) |
| novnc | 字符串 | 非必填 | - | - | novnc(on开启off关闭) |
| rescue | 字符串 | 非必填 | - | - | 救援系统(on开启off关闭) |
| crack_pass | 字符串 | 非必填 | - | - | 重置密码(on开启off关闭) |
| enable_ip_custom | 字符串 | 非必填 | - | - | 是否启用IP自定义字段(on开启off关闭) |
| area | 数组 | 非必填 | - | - | 对应区域名称 |
| ip_customid | 整型 | 非必填 | - | - | IP自定义字段ID |
| is_certifi[操作] | 数组 | 非必填 | - | - | 是否实名,1是,0否,详情返回的is_certifi里的操作 |
| user_prefix | 字符串 | 非必填 | - | - | 财务标识 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务器详情 --  GET /admin/dcim/server/:id

- controller: ``app\admin\controller\DcimController::serverDetail``
- desc: 服务器详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"服务器ID",
    "name":"名称",
    "hostname":"服务器地址",
    "username":"用户名",
    "password":"密码",
    "port":"端口",
    "secure":"是否https(0不是1是)",
    "disabled":"是否禁用(0启用1禁用)",
    "reinstall_times":"重装次数限制",
    "buy_times":"超出次数是否可以购买次数",
    "reinstall_price":"重装次数价格",
    "traffic":"流量图(on开启off关闭)",
    "kvm":"kvm(on开启off关闭)",
    "ikvm":"ikvm(on开启off关闭)",
    "bmc":"重置bmc(on开启off关闭)",
    "reinstall":"重装系统(on开启off关闭)",
    "reboot":"重启(on开启off关闭)",
    "on":"开机(on开启off关闭)",
    "off":"关机(on开启off关闭)",
    "novnc":"novnc(on开启off关闭)",
    "rescue":"救援系统(on开启off关闭)",
    "crack_pass":"重置密码(on开启off关闭)",
    "enable_ip_custom":"是否启用自定义IP(on开启off关闭)",
    "area":[{//区域
      "id":"区域ID",
      "area":"区域代码",
      "name":"区域名称",
    }]
    "bill_type":"流量计费方式(month自然月last_30days订购日至下月)",
    "flow_remind":[{//流量提醒设置
    }]
    "email_template":[{//邮件模板
    }]
    "ip_customid":"IP自定义字段ID",
    "is_certifi":"操作实名认证情况(array)",
    "user_prefix":"财务标识",
  }
}
```

### 删除服务器 --  DELETE /admin/dcim/server

- controller: ``app\admin\controller\DcimController::delServer``
- desc: 删除服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务器列表 --  GET /admin/dcim/server

- controller: ``app\admin\controller\DcimController::serverList``
- desc: 服务器列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,name,hostname,server_num,api_status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"服务器ID",
      "name":"服务器名称",
      "hostname":"服务器地址",
      "server_num":"服务器数量",
      "api_status":"连接状态",
      "removable":"是否可以删除",
    }]
  }
}
```

### 获取服务器状态 --  GET /admin/dcim/server/:id/status

- controller: ``app\admin\controller\DcimController::refreshServerStatus``
- desc: 获取服务器状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "server_status":"服务器状态(1连接测试成功,0失败)",
  }
}
```

### 刷新所有服务器状态 --  GET /admin/dcim/server/status

- controller: ``app\admin\controller\DcimController::refreshAllServerStatus``
- desc: 刷新所有服务器状态 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//列表数据
      "id":"服务器ID",
      "status":"服务器状态(0连接失败1连接成功)",
      "msg":"连接描述",
    }]
  }
}
```

### 流量包/重装下单记录 --  GET /admin/dcim/buy_record

- controller: ``app\admin\controller\DcimController::listBuyRecord``
- desc: 流量包/重装下单记录 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,capacity,price,status,sale_times) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"记录ID",
      "uid":"用户ID",
      "price":"价格",
      "status":"0未支付1已付款",
      "create_time":"创建时间",
      "pay_time":"支付时间",
      "username":"用户名",
      "removable":"是否可以删除",
    }]
  }
}
```

### 删除购买记录 --  DELETE /admin/dcim/buy_record

- controller: ``app\admin\controller\DcimController::delRecord``
- desc: 删除购买记录 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 记录ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 流量包列表 --  GET /admin/dcim/flowpacket

- controller: ``app\admin\controller\DcimController::listFlowPacket``
- desc: 流量包列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,capacity,price,status,sale_times) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"流量包ID",
      "name":"流量包名称",
      "capacity":"流量包容量",
      "price":"流量包价格",
      "status":"状态(0禁用1启用)",
      "sale_times":"销售次数",
      "stock":"库存总量(0表示不限)",
      "create_time":"创建时间",
    }]
    "page":"当前页数",
    "limit":"每页条数",
    "sum":"总条数",
    "max_page":"总页数",
    "orderby":"排序字段",
    "sort":"排序方向",
  }
}
```

### 添加流量包页面 --  GET /admin/dcim/flowpacket_page

- controller: ``app\admin\controller\DcimController::addFlowPacketPage``
- desc: 添加流量包页面 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//可用产品
      "id":"产品ID",
      "name":"产品名称",
    }]
  }
}
```

### 修改流量包页面 --  GET /admin/dcim/flowpacket_page/:id

- controller: ``app\admin\controller\DcimController::editFlowPacketPage``
- desc: 修改流量包页面 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | - | - | 流量包ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//可用产品
      "id":"产品ID",
      "name":"产品名称",
    }]
    "flowpacket.id":"流量包ID",
    "flowpacket.name":"流量包名称",
    "flowpacket.capacity":"流量包容量",
    "flowpacket.price":"价格",
    "flowpacket.allow_products":"允许产品ID",
    "flowpacket.status":"状态(0禁用1启用)",
    "flowpacket.create_time":"创建时间",
    "flowpacket.sales_time":"销售次数",
    "flowpacket.stock":"库存",
  }
}
```

### 添加流量包 --  POST /admin/dcim/flowpacket

- controller: ``app\admin\controller\DcimController::addFlowPacket``
- desc: 添加流量包 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| capacity | 整型 | 必填 | - | - | 流量包大小(G)最少1 |
| price | 浮点型 | 必填 | - | - | 价格最少0.01 |
| status | 整型 | 必填 | - | - | 状态(0禁用1启用) |
| stock | 整型 | 非必填 | 0 | - | 库存(0不限) |
| allow_products | 数组 | 非必填 | - | - | 允许的产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改流量包 --  PUT /admin/dcim/flowpacket

- controller: ``app\admin\controller\DcimController::editFlowPacket``
- desc: 修改流量包 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 流量包ID |
| name | 字符串 | 非必填 | - | - | 名称 |
| capacity | 整型 | 非必填 | - | - | 流量包大小(G)最少1 |
| price | 浮点型 | 非必填 | - | - | 价格最少0.01 |
| status | 整型 | 非必填 | - | - | 状态(0禁用1启用) |
| stock | 整型 | 非必填 | - | - | 库存(0不限) |
| allow_products | 数组 | 非必填 | - | - | 允许的产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除流量包 --  DELETE /admin/dcim/flowpacket

- controller: ``app\admin\controller\DcimController::delFlowPacket``
- desc: 删除流量包 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 流量包ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开机 --  POST /admin/dcim/on

- controller: ``app\admin\controller\DcimController::on``
- desc: 开机 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关机 --  POST /admin/dcim/off

- controller: ``app\admin\controller\DcimController::off``
- desc: 关机 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重启 --  POST /admin/dcim/reboot

- controller: ``app\admin\controller\DcimController::reboot``
- desc: 重启 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置BMC --  POST /admin/dcim/bmc

- controller: ``app\admin\controller\DcimController::bmc``
- desc: 重置BMC -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取kvm --  POST /admin/dcim/kvm

- controller: ``app\admin\controller\DcimController::kvm``
- desc: 获取kvm -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "name":"下载的文件名",
  }
}
```

### 获取ikvm --  POST /admin/dcim/ikvm

- controller: ``app\admin\controller\DcimController::ikvm``
- desc: 获取ikvm -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "name":"下载的文件名",
  }
}
```

### 下载java文件 --  GET /admin/dcim/download

- controller: ``app\admin\controller\DcimController::download``
- desc: 下载java文件 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 要下载的文件名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重装系统 --  POST /admin/dcim/reinstall

- controller: ``app\admin\controller\DcimController::reinstall``
- desc: 重装系统 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| os | 整型 | 必填 | - | - | 操作系统ID |
| password | 字符串 | 必填 | - | - | 密码(六位以上且由大小写字母数字三种组成) |
| mcon | 整型 | 非必填 | - | - | 附加配置ID |
| action | 整型 | 必填 | - | - | 分区(0默认1附加配置) |
| port | 整型 | 必填 | - | - | 端口号 |
| part_type | 整型 | 非必填 | 0 | - | 分区类型(windows才有0全盘格式化1第一分区格式化) |
| disk | 整型 | 非必填 | 0 | - | 磁盘号(从0开始分区为附加配置时不需要) |
| check_disk_size | 整型 | 非必填 | 0 | - | 是否验证磁盘 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "confirm":"失败时可能会返回,true弹出确认框取消或者继续安装,继续安装把参数check_disk_size=0和其他原有参数重新发起重装即可",
  }
}
```

### 获取重装,救援系统,重置密码进度 --  GET /admin/dcim/resintall_status

- controller: ``app\admin\controller\DcimController::getReinstallStatus``
- desc: 获取重装,救援系统,重置密码进度 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "disk_check":[{//弹出错误时
      "value":"disk_part的值",
      "description":"描述",
    }]
    "error_type":"0,1,2,其他(当error_type>0并且progress>=20时弹出磁盘分区错误提示,1Windows磁盘错误,2Windows分区错误,其他Windows磁盘分区提示)",
    "error_msg":"当error_type>0时弹出磁盘分区错误提示信息",
    "disk_info":[{//当显示弹出磁盘分区错误提示
      "disk":"磁盘",
      "part":"分区",
      "size":"大小",
      "type":"类型",
      "windows":"类型",
    }]
    "progress":"进度",
    "windows_finish":"是否是windows已完成",
    "hostid":"当前产品ID",
    "task_type":"类型(0重装系统,1救援系统,2重置密码,3获取硬件信息)",
    "reinstall_msg":"重装信息",
    "crackPwd":[{//当有数据返回时,弹出重置密码用户选择
      "user":"可选择的用户",
      "password":"重置的密码",
    }]
    "step":"当前步骤描述",
    "last_result":[{//上次执行结果
      "act":"操作名称",
      "status":"1成功",
      "msg":"描述",
    }]
  }
}
```

### 救援系统 --  POST /admin/dcim/rescue

- controller: ``app\admin\controller\DcimController::rescue``
- desc: 救援系统 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| system | 整型 | 必填 | - | - | 操作系统(1Linux2Windows) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置密码 --  POST /admin/dcim/crack_pass

- controller: ``app\admin\controller\DcimController::crackPass``
- desc: 重置密码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| password | 字符串 | 必填 | - | - | 密码 |
| other_user | 整型 | 非必填 | 0 | - | 是否重置其他用户(0不是1是) |
| user | 字符串 | 非必填 | - | - | 自定义需要重置的用户名(用户名不能包含中文空格@符) |
| action | 字符串 | 非必填 | - | - | 获取进度有crackPwd时选择用户后传chooseUser |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用量信息 --  GET /admin/dcim/traffic_usage

- controller: ``app\admin\controller\DcimController::getTrafficUsage``
- desc: 获取用量信息 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| start | 字符串 | 非必填 | - | - | 开始日期(YYYY-MM-DD) |
| end | 字符串 | 非必填 | - | - | 结束日期(YYYY-MM-DD) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//流量数据
      "time":"横坐标值",
      "value":"纵坐标值(单位Mbps)",
    }]
  }
}
```

### 取消重装,救援,重置密码 --  POST /admin/dcim/cancel_task

- controller: ``app\admin\controller\DcimController::cancelReinstall``
- desc: 取消重装,救援,重置密码 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重装解除暂停 --  POST /admin/dcim/unsuspend_reinstall

- controller: ``app\admin\controller\DcimController::unsuspendReload``
- desc: 重装解除暂停 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| disk_part | 字符串 | 必填 | - | - | 重装返回的disk_part |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取流量图信息 --  POST /admin/dcim/trafiic

- controller: ``app\admin\controller\DcimController::traffic``
- desc: 获取流量图信息 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| switch_id | 整型 | 必填 | - | - | 交换机ID |
| port_name | 字符串 | 必填 | - | - | 端口名称 |
| start_time | 整型 | 非必填 | - | - | 开始时间(毫秒时间戳) |
| end_time | 整型 | 非必填 | - | - | 结束时间(毫秒时间戳) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "unit":"流量单位",
    "traffic":[{//流量数据
      "time":"毫秒时间戳",
      "value":"值",
      "type":"类型(in进流量,out出流量)",
    }]
  }
}
```

### 获取novnc --  POST /admin/dcim/novnc

- controller: ``app\admin\controller\DcimController::novnc``
- desc: 获取novnc -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "password":"vnc密码",
    "url":"vnc地址",
  }
}
```

### novnc页面 --  GET /admin/dcim/novnc

- controller: ``app\admin\controller\DcimController::novncPage``
- desc: novnc页面 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| password | 字符串 | 必填 | - | - | novnc返回的密码 |
| url | 整型 | 必填 | - | - | novnc返回的url |
| host_token | 字符串 | 必填 | - | - | 加密的密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取DCIM产品详情 --  GET /dcim/detail

- controller: ``app\admin\controller\DcimController::detail``
- desc: 获取DCIM产品详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "switch":[{//交换机数据
      "switch_id":"连接的交换机ID",
      "name":"端口名称",
    }]
    "password":"操作系统密码",
    "username":"操作系统名称",
    "os_ostype":"当前操作系统ostype",
    "os_osname":"当前操作系统真实名称",
    "disk_num":"服务器磁盘数量",
  }
}
```

### 获取销售服务器 --  GET /admin/dcim/sales

- controller: ``app\admin\controller\DcimController::getSalesServer``
- desc: 获取销售服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | host |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 100 | - | 每页条数 |
| group | 整型 | 非必填 | - | - | 分组 |
| status | 整型 | 非必填 | - | - | 状态(1空闲3正常) |
| search | 字符串 | 非必填 | - | - | 搜索IP |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"DCIM服务器ID",
      "wltag":"标签",
      "typename":"型号",
      "group_name":"分组",
      "mainip":"主IP",
      "ip_num":"IP数量",
      "in_bw":"进带宽",
      "out_bw":"出带宽",
      "remarks":"备注",
      "status":"状态(1空闲3正常)",
      "email":"客户信息",
      "hostid":"产品ID",
      "uid":"用户ID",
      "self":"是否属于该系统",
      "dcim_url":"DCIM服务器详情链接地址",
      "token":"财务系统唯一标识",
      "type":"服务器类型(rent租用trust托管)",
      "cpu":"显示的cpu",
      "ram":"显示的内存",
      "disk":"显示的磁盘",
      "cpu_detail":[{//CPU详情
        "assign":"采购信息real:实际信息",
      }]
      "ram_detail":[{//内存详情
        "assign":"采购信息real:实际信息",
      }]
      "disk_detail":[{//磁盘详情
        "assign":"采购信息real:实际信息",
      }]
    }]
    "count":"总条数",
    "limit":"每页条数",
    "page":"当前页数",
    "max_page":"最大页数",
    "server_group":[{//分组列表
      "id":"分组ID",
      "name":"分组名称",
    }]
  }
}
```

### 分配设置 --  POST /admin/dcim/assign

- controller: ``app\admin\controller\DcimController::assignServer``
- desc: 分配设置 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostID |
| dcimid | 整型 | 必填 | - | - | Dcim服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除设备ID --  POST /admin/dcim/delete

- controller: ``app\admin\controller\DcimController::delete``
- desc: 删除设备ID -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取电源状态 --  POST /admin/dcim/refresh_power_status

- controller: ``app\admin\controller\DcimController::refreshPowerStatus``
- desc: 获取电源状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | hostID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power":"电源状态(on开机off关机error无法连接not_support不支持电源控制)",
    "msg":"状态信息描述",
  }
}
```


---

## 实名认证配置

### 实名认证设置 -- GET /admin/config_certifi/setting

- controller: ``app\admin\controller\ConfigCertifiController::setting``
- desc: 实名认证设置 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".certifi_is_stop":"未实名暂停产品1=开0=关",
    ".certifi_stop_day":"未实名暂停产品时间/天",
    ".certifi_is_upload":"是否上传身份证1=是0=否",
    ".certifi_open":"是否开启身份认证  1=开启0=关闭",
    ".certifi_isbindphone":"认证手机号是否必须与绑定一致",
    ".certifi_realname":"是否自动更新姓名",
    ".certifi_select":"选中的认证类型",
    ".certifi_select_all":"所有认证类型",
  }
}
```

### 实名认证设置提交 --  POST /admin/config_certifi/settingPost

- controller: ``app\admin\controller\ConfigCertifiController::settingPost``
- desc: 实名认证设置提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| certifi_realname | 字符串 | 必填 | - | - | 是否同步姓名 |
| certifi_is_upload | 整型 | 必填 | - | - | 是否上传图片1=上传0=不上传 |
| certifi_is_stop | 整型 | 必填 | - | - | 未实名暂停产品1=开0=关 |
| certifi_stop_day | 整型 | 必填 | - | - | 未实名暂停产品/天 |
| certifi_open | 整型 | 必填 | - | - | 是否开启身份认证/天 |
| certifi_isbindphone | 字符串 | 必填 | - | - | 绑定手机是否一致 |
| certifi_select[] | 字符串 | 必填 | - | - | 认证类型 |
| artificial_auto_send_msg | 整型 | 必填 | - | - | 人工审核自动发送短信 |
| certifi_business_open | 字符串 | 必填 | - | - | 企业高级设置 |
| certifi_business_is_upload | 字符串 | 必填 | - | - | 营业执照上传 |
| certifi_business_is_author | 字符串 | 必填 | - | - | 授权书上传 |
| certifi_business_author_path | 字符串 | 必填 | - | - | 授权书路径 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 授权书下载 --  GET /admin/config_certifi/authorDown

- controller: ``app\admin\controller\ConfigCertifiController::authorDown``
- desc: 授权书下载 -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 授权书删除 --  GET /admin/config_certifi/authorDel

- controller: ``app\admin\controller\ConfigCertifiController::authorDel``
- desc: 授权书删除 -- xue

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 阿里认证配置数据 -- GET /admin/certifi_alipay_detail

- controller: ``app\admin\controller\ConfigCertifiController::detail``
- desc: 阿里认证配置数据 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".certifi_alipay_biz_code":"类型",
    ".certifi_alipay_public_key":"公钥",
    ".certifi_app_id":"appid",
    ".certifi_merchant_private_key":"私钥",
    ".certifi_is_stop":"未实名暂停产品1=开2=关",
    ".certifi_stop_day":"未实名暂停产品时间/天",
    ".certifi_is_upload":"是否上传身份证1=是2=否",
    ".certifi_open":"是否开启身份认证  1=开启2=关闭",
    ".certifi_type":"认证类型",
    ".certifi_phonethree_appcode":"手机三要素认证appcode",
  }
}
```

### 获取阿里认证类型 --  GET /admin/certifi_alipay_biz_code

- controller: ``app\admin\controller\ConfigCertifiController::alipay_biz_code``
- desc: 获取阿里认证类型 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "string name -配置名称":"",
    "string value -配置值":"",
  }
}
```

### 获取三要素认证类型 --  GET /admin/certifi_three_type

- controller: ``app\admin\controller\ConfigCertifiController::alipay_three_type``
- desc: 获取三要素认证类型 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "string name -配置名称":"",
    "string value -配置值":"",
  }
}
```

### 获取认证类型 --  GET /admin/certifi_type

- controller: ``app\admin\controller\ConfigCertifiController::type``
- desc: 获取认证类型 -- liyongjun

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "string name -配置名称":"",
    "string value -配置值":"",
  }
}
```

### 获取认证类型 --  GET /admin/certifi_types

- controller: ``app\admin\controller\ConfigCertifiController::types``
- desc: 获取认证类型 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "string name -配置名称":"",
    "string value -配置值":"",
  }
}
```

### 修改阿里认证数据 --  PUT /admin/certifi_alipay

- controller: ``app\admin\controller\ConfigCertifiController::update``
- desc: 修改阿里认证数据 -- liyongjun

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| certifi_type | 字符串 | 必填 | - | - | 认证类型 |
| certifi_realname | 字符串 | 必填 | - | - | 是否同步姓名 |
| certifi_appcode | 字符串 | 必填 | - | - | appcode |
| certifi_three_type | 字符串 | 必填 | - | - | 三要素 |
| certifi_alipay_biz_code | 字符串 | 必填 | - | - | 阿里认证类型 |
| certifi_alipay_public_key | 字符串 | 必填 | - | - | 阿里认证公钥 |
| certifi_app_id | 字符串 | 必填 | - | - | 阿里认证appid |
| certifi_merchant_private_key | 字符串 | 必填 | - | - | 阿里认证私钥 |
| certifi_is_upload | 整型 | 必填 | - | - | 是否上传图片1=上传2=不上传 |
| certifi_is_stop | 整型 | 必填 | - | - | 未实名暂停产品1=开2=关 |
| certifi_stop_day | 整型 | 必填 | - | - | 未实名暂停产品/天 |
| certifi_open | 整型 | 必填 | - | - | 是否开启身份认证/天 |
| name | 字符串 | 必填 | - | - | 自定义名字 |
| certifi_isbindphone | 字符串 | 必填 | - | - | 绑定手机是否一致 |
| certifi_isrealname | 字符串 | 必填 | - | - | 是否实名 |
| certifi_phonethree_appcode | 字符串 | 必填 | - | - | 手机三要素appcode |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "string name -配置名称":"",
    "string value -配置值":"",
  }
}
```


---

## 后台全局数据

### 后台全局接口 -- GET /admin/common

- controller: ``app\admin\controller\CommonController::common``
- desc: 后台全局接口 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "company_name":"公司名称",
    "system_url":"系统链接",
    "system_title":"系统标题",
    "domain":"网站域名",
    "gateway":[{//支付方式
      "name":"名称title:标题status:状态module:模块url:地址",
    }]
    "admin":"管理员名称",
    "system_language":"系统语言",
    "config":[{// 配置信息
      "client_certifi_status":[{//客户认证状态
      }]
      "client_status":[{//认证状态
      }]
      "invoice_payment_status":[{//账单支付状态
      }]
      "order_status":[{//订单状态
      }]
      "domainstatus":[{//产品状态
      }]
      "user_is_sale":[{//是否销售状态
      }]
      "user_sale_is_use":[{//销售是否启用
      }]
      "rule":[{//菜单列表
      }]
      "auth":[{//权限集
      }]
      "sale":[{//销售列表
      }]
    }]
    "second_verify_admin":"是否开启后台二次验证",
    "second_verify_action_admin":"开启的二次验证动作",
  }
}
```

### 系统后台消息通知 -- GET /admin/common/info_notice

- controller: ``app\admin\controller\CommonController::infoNotice``
- desc: 系统后台消息通知 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "info":"系统消息通知内容",
  }
}
```

### 系统后台支付方式 -- GET /admin/common/get_getways

- controller: ``app\admin\controller\CommonController::getGetways``
- desc: 系统后台支付方式 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "gateway":"系统后台支付方式",
  }
}
```

### 系统邮件模板列表 -- GET /admin/common/get_email_tem

- controller: ``app\admin\controller\CommonController::getEmailTem``
- desc: 系统邮件模板列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | 'product' |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "email":"系统邮件模板列表",
  }
}
```

### 系统用户分组 -- GET /admin/common/get_client_groups

- controller: ``app\admin\controller\CommonController::getClientGroups``
- desc: 系统用户分组 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client_groups":"系统用户分组",
  }
}
```

### 商品列表 -- GET /admin/common/get_product_list

- controller: ``app\admin\controller\CommonController::getProductList``
- desc: 商品列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 商品id |
| type | 字符串 | 必填 | - | - | 0 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client_groups":"商品列表",
  }
}
```

### 优惠码 -- GET /admin/common/get_promo_code

- controller: ``app\admin\controller\CommonController::getPromoCode``
- desc: 优惠码 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | - | - | 0 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "promo_code":"优惠码",
  }
}
```

### 客户产品列表 -- GET /admin/common/host_list

- controller: ``app\admin\controller\CommonController::getHostList``
- desc: 客户产品列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | id | 必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "host_list":"客户产品列表",
  }
}
```

### 国家列表 -- GET /admin/common/get_sms_country

- controller: ``app\admin\controller\CommonController::getSmsCountry``
- desc: 国家列表 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "promo_code":"国家列表",
  }
}
```

### 可配置项 -- GET /admin/common/product_config_options

- controller: ``app\admin\controller\CommonController::getProductConfigOptions``
- desc: 可配置项 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "config_options":"可配置项",
  }
}
```

### 销售列表 -- GET /admin/common/sale_list

- controller: ``app\admin\controller\CommonController::saleList``
- desc: 销售列表 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上游部门列表 -- GET /admin/common/get_upstream_ticket_department_list

- controller: ``app\admin\controller\CommonController::getUpstreamTicketDepartmentList``
- desc: 上游部门列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | - | - | 上游id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "upstream_ticket_departments":"部门列表",
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\CommonController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\CommonController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\CommonController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\CommonController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\CommonController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\CommonController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\CommonController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\CommonController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 销售管理

### 销售管理列表页 -- GET /admin/salegroup

- controller: ``app\admin\controller\SaleController::groupList``
- desc: 销售管理列表页 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| group_name | 字符串 | 非必填 | 1 | - | 按分组名搜索 |
| bates | 字符串 | 非必填 | 1 | - | 按比例搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"销售管理列表总数",
    "list":[{//销售管理列表表数据
      "group_name":"分组名",
      "bates":"比例",
      "renew_bates":"续费比例",
      "upgrade_bates":"升级比例",
      "is_renew":"是否包含续费计算",
      "updategrade":"是否计算升降级",
      "pids":"产品组列表",
    }]
  }
}
```

### 获取时间类型 -- GET /admin/aff/get_timetype

- controller: ``app\admin\controller\SaleController::getTimetype``
- desc: 获取时间类型 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据(搜索区)
    }]
  }
}
```

### 添加分组页面 -- GET /admin/sale/add_salegrouppage

- controller: ``app\admin\controller\SaleController::addSalegroupPage``
- desc: 添加分组页面 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "group":[{//产品组
      "id":"组id",
      "name":"组名",
      "product":[{//产品
        "id":"产品id",
        "type":"类型",
        "gid":"组id",
        "name":"产品名",
        "description":"描述",
        "pay_method":"付款类型",
        "tax":"税",
      }]
    }]
  }
}
```

### 添加分组 -- POST /admin/sale/add_salegroup

- controller: ``app\admin\controller\SaleController::addSalegroup``
- desc: 添加分组 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| group_name | 字符串 | 必填 | 1 | - | 分组名称 |
| bates | 浮点型 | 必填 | 0 | - | 比例 |
| is_renew | 整型 | 必填 | 0 | - | 是否包含续费计算 |
| updategrade | 整型 | 必填 | 0 | - | 是否计算升降级 |
| pids | 浮点型 | 必填 | 1 | - | 产品集(1,2,3) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑分组页面 -- GET /admin/sale/edit_salegrouppage

- controller: ``app\admin\controller\SaleController::editSalegroupPage``
- desc: 编辑分组页面 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "group":[{//产品组
      "id":"组id",
      "name":"组名",
      "product":[{//产品
        "id":"产品id",
        "type":"类型",
        "gid":"组id",
        "name":"产品名",
        "description":"描述",
        "pay_method":"付款类型",
        "tax":"税",
      }]
    }]
    "spg":[{//分组
      "id":"组id",
      "groupname":"组名",
      "bates":"比例",
      "is_renew":"是否包含续费计算",
      "updategrade":"是否计算升降级",
      "pids":[{//产品集(1,2,3)
      }]
    }]
  }
}
```

### 编辑分组 -- POST /admin/sale/edit_salegroup

- controller: ``app\admin\controller\SaleController::editSalegroup``
- desc: 编辑分组 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |
| group_name | 字符串 | 必填 | 1 | - | 分组名称 |
| bates | 浮点型 | 必填 | 0 | - | 比例 |
| renew_bates | 浮点型 | 必填 | 0 | - | 比例 |
| upgrade_bates | 浮点型 | 必填 | 0 | - | 比例 |
| is_renew | 整型 | 必填 | 0 | - | 是否包含续费计算 |
| updategrade | 整型 | 必填 | 0 | - | 是否计算升降级 |
| pids | 数组 | 必填 | 1 | - | 产品集(1,2,3) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分组 -- GET /admin/sale/del_salegroup

- controller: ``app\admin\controller\SaleController::delSalegroup``
- desc: 删除分组 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 阶梯列表页 -- GET /admin/saleladder

- controller: ``app\admin\controller\SaleController::ladderList``
- desc: 阶梯列表页 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"阶梯列表页总数",
    "list":[{//阶梯列表页数据
      "group_name":"营业额",
      "bates":"比例",
      "is_flag":"是否开启",
    }]
  }
}
```

### 添加阶梯 -- POST /admin/sale/add_saleladder

- controller: ``app\admin\controller\SaleController::addSaleLadder``
- desc: 添加阶梯 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| turnover | 字符串 | 必填 | 0 | - | 营业额 |
| bates | 浮点型 | 必填 | 0 | - | 比例 |
| is_flag | 整型 | 必填 | 0 | - | 是否开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑阶梯页面 -- GET /admin/sale/edit_saleladderpage

- controller: ``app\admin\controller\SaleController::editSaleLadderPage``
- desc: 编辑阶梯页面 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 阶梯ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "spg":[{//分组
      "id":"组id",
      "turnover":"营业额",
      "bates":"比例",
      "is_flag":[{//是否开启
      }]
    }]
  }
}
```

### 编辑阶梯 -- POST /admin/sale/edit_saleladder

- controller: ``app\admin\controller\SaleController::editSaleLadder``
- desc: 编辑阶梯 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 分组ID |
| turnover | 字符串 | 必填 | 1 | - | 营业额 |
| bates | 浮点型 | 必填 | 0 | - | 提成比例 |
| is_flag | 整型 | 必填 | 0 | - | 是否开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除阶梯 -- GET /admin/sale/del_saleladder

- controller: ``app\admin\controller\SaleController::delSaleLadder``
- desc: 删除阶梯 -- 刘国栋

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 阶梯ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 销售管理统计页  测试id默认传3 -- GET /admin/sale/sale_statistics

- controller: ``app\admin\controller\SaleController::saleStatistics``
- desc: 销售管理统计页  测试id默认传3 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 销售id |
| start_time | 整型 | 非必填 | - | - | 时间 |
| time | 整型 | 非必填 | - | - | 时间类型 |
| type | 整型 | 非必填 | 1 | - | 类型1顶部 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "today":[{//今日订单和业绩
      "ordercount":"订单数",
      "total":"业绩",
    }]
    "week":"这周订单和业绩",
    "month":"这月订单和业绩",
    "last_month":"上月订单和业绩",
    "ladder":[{//当前阶梯
      "turnover":"当前（没有就默认）last:下一级",
    }]
    "array":[{//图表
    }]
  }
}
```

### 销售管理统计页 提现记录 测试id默认传3 -- POST /admin/sale/sale_records

- controller: ``app\admin\controller\SaleController::saleRecords``
- desc: 销售管理统计页 提现记录 测试id默认传3 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 非必填 | 1 | - | 姓名 |
| pname | 整型 | 非必填 | 1 | - | 商品 |
| type | 整型 | 非必填 | 1 | - | 类型 |
| id | 整型 | 非必填 | 1 | - | 销售id |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当月日志 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"销售管理列表总数",
    "record":[{//销售管理列表表数据
    }]
  }
}
```

### 销售管理统计页 提现记录 测试id默认传3 -- POST /admin/sale/sale_records

- controller: ``app\admin\controller\SaleController::saleRecordsNew``
- desc: 销售管理统计页 提现记录 测试id默认传3 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 非必填 | 1 | - | 姓名 |
| pname | 整型 | 非必填 | 1 | - | 商品 |
| type | 整型 | 非必填 | 1 | - | 类型 |
| id | 整型 | 非必填 | 1 | - | 销售id |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当月日志 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| time | 整型 | 非必填 | - | - | 时间类型 |
| type | 整型 | 非必填 | 1 | - | 类型1顶部 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"销售管理列表总数",
    "record":[{//销售管理列表表数据
    }]
  }
}
```

### 销售管理统计页 销售列表 -- GET /admin/sale/sale_users

- controller: ``app\admin\controller\SaleController::saleUsers``
- desc: 销售管理统计页 销售列表 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//销售列表
      "user_nickname":"昵称",
      "id":"id",
    }]
  }
}
```

### 销售设置 -- GET admin/sale/adminlist

- controller: ``app\admin\controller\SaleController::adminList``
- desc: 销售设置 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| search | 字符串 | 非必填 | - | - | 搜索 |
| limit | 整型 | 非必填 | 50 | - | 每页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "page":"当前页数",
    "limit":"每页条数",
    "count":"总条数",
    "max_page":"总页数",
    "list":[{//管理员列表
      "id":"管理员用户id",
      "user_login":"管理员用户名",
      "user_nickname":"管理员姓名",
      "user_email":"邮箱",
      "create_time":"创建时间",
      "user_status":"状态0禁用1可用",
      "last_login_time":"上次登录时间",
      "last_login_ip":"上次登录ip",
      "role":"管理员角色",
      "dept":"工单部门",
      "is_sale":"是否销售0=默认1=是",
      "sale_is_use":"销售是否启用0=默认1=启用",
      "only_mine":"只允许查看自己的客户",
      "all_sale":"查看所有销售",
    }]
  }
}
```

### 编辑销售设置 -- POST /admin/sale/edit_adminlist

- controller: ``app\admin\controller\SaleController::editAdminList``
- desc: 编辑销售设置 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 用户id |
| is_sale | 整型 | 非必填 | 1 | - | 是否销售0=默认1=是 |
| sale_is_use | 整型 | 非必填 | 0 | - | 销售是否启用0=默认1=启用 |
| only_mine | 整型 | 非必填 | 0 | - | 只允许查看自己的客户 |
| all_sale | 整型 | 非必填 | 0 | - | 查看所有销售 |
| only_oneself_notice | 整型 | 非必填 | 0 | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 销售配置 -- GET /admin/sale/get_sale_enble

- controller: ``app\admin\controller\SaleController::getSaleEnble``
- desc: 销售配置 -- 萧十一郎

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "sale_setting":"设置0,1，2",
    "sale_reg_setting":"设置0,1，2",
    "sale_auto_setting":"分配设置1，2",
    "only_oneself_notice":"分配设置1，2",
  }
}
```

### 销售配置提交 -- POST /admin/sale/sale_enble

- controller: ``app\admin\controller\SaleController::saleEnblePost``
- desc: 销售配置提交 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| sale_setting | 整型 | 非必填 | 1 | - | 设置 |
| sale_reg_setting | 整型 | 非必填 | 1 | - | 注册设置 |
| sale_auto_setting | 整型 | 非必填 | 1 | - | 分配设置 |
| only_oneself_notice | 整型 | 非必填 | 1 | - | 仅自己客户的工单提醒邮件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\SaleController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\SaleController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\SaleController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\SaleController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\SaleController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\SaleController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\SaleController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\SaleController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台推荐计划

### 推介计划 -- GET /admin/aff

- controller: ``app\admin\controller\AffiliateController::index``
- desc: 推介计划 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 非必填 | - | - | 用户名 |
| visitors | 浮点型 | 非必填 | - | - | 访问量 |
| visitors_type | 字符串 | 非必填 | - | - | 1大于访问量小于2 |
| balance | 浮点型 | 非必填 | - | - | 可提现佣金 |
| balance_type | 字符串 | 非必填 | - | - | 1大于访问量小于2 |
| withdrawn | 浮点型 | 非必填 | - | - | 已提现佣金 |
| withdrawn_type | 字符串 | 非必填 | - | - | 1大于访问量小于2 |
| registcount | 整型 | 非必填 | - | - | 注册数量 |
| registcount_type | 字符串 | 非必填 | - | - | 1大于访问量小于2 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "id":"id",
      "username":"姓名",
      "companyname":"姓名",
      "visitors":"访问数量",
      "registcount":"注册数量",
      "url":"推荐链接",
      "payamount":"订购数量",
      "balance":"可提现佣金",
      "sum":"总佣金",
      "withdrawn":"已提现佣金",
    }]
  }
}
```

### 用户推介配置 -- GET /admin/aff/useraffi_page

- controller: ``app\admin\controller\AffiliateController::useraffiPage``
- desc: 用户推介配置 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 必填 | 1 | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//用户推介数据
      "affiliate_enabled":"是否启用推介affiliate_bates:推介计划比例affiliate_type:比例类型1金额2百分比affiliate_is_reorder:是否开启二次订单affiliate_reorder:二次订单比例affiliate_reorder_type:二次订单比例类型",
      "affiliate_is_renew":"是否开启续费affiliate_renew:续费比例affiliate_renew_type:续费比例类型",
    }]
    "datauser":[{//用户推介计划
      "visitors":"访问数量",
      "registcount":"注册数量",
      "payamount":"订购数量",
      "balance":"可提现佣金",
      "audited_balance":"审核中的佣金",
      "withdrawn":"已提现佣金",
    }]
  }
}
```

### 用户推荐金额修改 -- POST /admin/aff/useraffi_balance

- controller: ``app\admin\controller\AffiliateController::useraffibalance``
- desc: 用户推荐金额修改 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | id | 必填 | 1 | - | 用户id |
| withdrawn | 浮点型 | 非必填 | 1 | - | 已提现金额 |
| balance | 浮点型 | 非必填 | 1 | - | 可提现金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户推荐配置提交 -- POST /admin/aff/useraffi_post

- controller: ``app\admin\controller\AffiliateController::useraffiPost``
- desc: 用户推荐配置提交 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 非必填 | 1 | - | id |
| uid | id | 必填 | 1 | - | 用户id |
| affiliate_enabled | 整型 | 非必填 | 1 | - | 系统默认1自定义2 |
| affiliate_bates | 浮点型 | 非必填 | 1 | - | 推介计划比例 |
| affiliate_type | 整型 | 非必填 | 1 | - | 比例类型1金额2百分比 |
| affiliate_is_reorder | 整型 | 非必填 | 1 | - | 系统默认1自定义2 |
| affiliate_reorder | 浮点型 | 非必填 | 1 | - | 二次订单比例 |
| affiliate_reorder_type | 整型 | 非必填 | 1 | - | 二次订单方式1金额2百分比 |
| affiliate_is_renew | 整型 | 非必填 | 1 | - | 系统默认1自定义2 |
| affiliate_renew | 浮点型 | 非必填 | 1 | - | 续费比例 |
| affiliate_renew_type | 整型 | 非必填 | 1 | - | 续费方式1金额2百分比 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 客户注册列表 -- GET /admin/aff/useraffi_list

- controller: ``app\admin\controller\AffiliateController::useraffilist``
- desc: 客户注册列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 必填 | 1 | - | 用户id |
| username | 字符串 | 非必填 | - | - | 用户名 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//用户推介数据
      "id":"用户idcreate_time:创建时间username:用户名companyname:公司名lastlogin:登录时间",
    }]
    "total":[{//总条数
    }]
  }
}
```

### 提现记录 -- GET /admin/aff/useraffi_record

- controller: ``app\admin\controller\AffiliateController::useraffirecord``
- desc: 提现记录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 必填 | 1 | - | 用户id |
| user_nickname | 字符串 | 非必填 | - | - | 用户名 |
| status | 整型 | 非必填 | - | - | 状态1待审核2通过3拒绝 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "num":"金额",
      "type":"余额1仅记录2流水支持3",
      "user_nickname":"操作人",
      "status":"1待审核2审核通过3拒绝",
      "reason":"拒绝原因",
      "create_time":"时间",
    }]
  }
}
```

### 获取时间类型 -- GET /admin/aff/get_timetype

- controller: ``app\admin\controller\AffiliateController::getTimetype``
- desc: 获取时间类型 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据(搜索区)
    }]
  }
}
```

### 订购记录 -- GET /admin/aff/useraffibuy_record

- controller: ``app\admin\controller\AffiliateController::useraffibuyrecord``
- desc: 订购记录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 必填 | 1 | - | 用户id |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| time | 字符串 | 非必填 | - | - | 时间类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "uid":"客户id",
      "create_time":"订购时间",
      "subtotal":"金额",
      "type":"类型",
      "paid_time":"付款时间",
      "commission":"佣金",
      "paid_status":"状态",
    }]
    "child":[{//子数据
      "domainstatus":"产品状态name:产品名amount:金额commission:佣金type:类型",
    }]
    "total":[{//总数
    }]
  }
}
```

### 产品推荐配置 -- GET /admin/aff/productaffi_page

- controller: ``app\admin\controller\AffiliateController::productaffiPage``
- desc: 产品推荐配置 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | id | 必填 | 1 | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//产品推介数据
    }]
    "affiliate_enabled":"是否启用推介",
    "affiliate_bates":"推介计划比例",
    "affiliate_type":"比例类型 1金额 2百分比",
    "affiliate_is_reorder":"是否开启二次订单",
    "affiliate_reorder":"二次订单比例",
    "affiliate_is_renew":"是否开启续费",
    "affiliate_renew":"续费比例",
  }
}
```

### 产品推荐配置提交 -- POST /admin/aff/productaffi_post

- controller: ``app\admin\controller\AffiliateController::productaffiPost``
- desc: 产品推荐配置提交 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 非必填 | 1 | - | id |
| uid | id | 必填 | 1 | - | 用户id |
| affiliate_enabled | 整型 | 非必填 | 1 | - | 是否启用推介 |
| affiliate_bates | 浮点型 | 非必填 | 1 | - | 推介计划比例 |
| affiliate_type | 整型 | 非必填 | 1 | - | 比例类型 |
| affiliate_is_reorder | 整型 | 非必填 | 1 | - | 是否开启二次订单 |
| affiliate_reorder | 浮点型 | 非必填 | 1 | - | 二次订单比例 |
| affiliate_reorder_type | 整型 | 非必填 | 1 | - | 二次订单方式1金额2百分比 |
| affiliate_is_renew | 整型 | 非必填 | 1 | - | 是否开启续费 |
| affiliate_renew | 浮点型 | 非必填 | 1 | - | 续费比例 |
| affiliate_renew_type | 整型 | 非必填 | 1 | - | 续费方式1金额2百分比 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推荐计划提现记录 -- POST /admin/aff/affiwithdraw_record

- controller: ``app\admin\controller\AffiliateController::affiwithdrawrecord``
- desc: 推荐计划提现记录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 必填 | 1 | - | 用户id |
| user_nickname | 字符串 | 非必填 | - | - | 用户名 |
| status | 整型 | 非必填 | - | - | 状态1待审核2通过3拒绝 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "id":"id",
      "num":"金额",
      "type":"1余额2仅记录3流水支持",
      "user_nickname":"操作人",
      "status":"1待审核2审核通过3拒绝",
      "reason":"拒绝原因",
      "create_time":"时间",
    }]
  }
}
```

### 提现记录审核 -- POST /admin/aff/affiwithdrawsh

- controller: ``app\admin\controller\AffiliateController::affiwithdrawsh``
- desc: 提现记录审核 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | id | 必填 | 1 | - | affid |
| status | 整型 | 非必填 | - | - | 1待审核2审核通过3拒绝 |
| reason | 字符串 | 非必填 | - | - | 拒绝原因 |
| type | 字符串 | 非必填 | - | - | 1余额2仅记录3流水支持 |
| payment | 字符串 | 非必填 | 1 | - | 支付方式 |
| trans_id | 字符串 | 非必填 | 1 | - | 付款流水号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推介计划支付方式 -- GET /admin/aff/gateway_list

- controller: ``app\admin\controller\AffiliateController::gatewaylist``
- desc: 推介计划支付方式 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "gateway":[{//支付方式
    }]
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\AffiliateController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\AffiliateController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\AffiliateController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\AffiliateController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\AffiliateController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\AffiliateController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\AffiliateController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\AffiliateController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台对接魔方云管理

### 添加服务器 --  POST /admin/dcimcloud/server

- controller: ``app\admin\controller\DcimCloudController::addServer``
- desc: 添加服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| hostname | 字符串 | 必填 | - | - | 地址(IP或者域名) |
| username | 字符串 | 非必填 | - | - | 用户名 |
| password | 字符串 | 非必填 | - | - | 密码 |
| port | 字符串 | 非必填 | - | - | 端口 |
| secure | 整型 | 非必填 | 0 | - | 是否https(0不是1是) |
| disabled | 整型 | 非必填 | 1 | - | 是否启用(0启用1禁用) |
| user_prefix | 字符串 | 非必填 | - | - | 财务标识 |
| account_type | 字符串 | 非必填 | - | - | 账号类型(admin=管理员,agent=代理商) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改服务器 --  PUT /admin/dcimcloud/server

- controller: ``app\admin\controller\DcimCloudController::editServer``
- desc: 修改服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |
| name | 字符串 | 必填 | - | - | 名称 |
| hostname | 字符串 | 必填 | - | - | 地址(IP或者域名) |
| username | 字符串 | 非必填 | - | - | 用户名 |
| password | 字符串 | 非必填 | - | - | 密码 |
| port | 字符串 | 非必填 | - | - | 端口 |
| secure | 整型 | 非必填 | 0 | - | 是否https(0不是1是) |
| disabled | 整型 | 非必填 | 1 | - | 是否启用(0启用1禁用) |
| reinstall_times | 整型 | 非必填 | 0 | - | 重装次数 |
| buy_times | 整型 | 非必填 | 0 | - | 启用购买重装 |
| reinstall_price | 浮点型 | 非必填 | 0 | - | 重装价格 |
| user_prefix | 字符串 | 非必填 | - | - | 财务标识 |
| account_type | 字符串 | 非必填 | - | - | 账号类型(admin=管理员,agent=代理商) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务器详情 --  GET /admin/dcimcloud/server/:id

- controller: ``app\admin\controller\DcimCloudController::serverDetail``
- desc: 服务器详情 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"服务器ID",
    "name":"名称",
    "hostname":"服务器地址",
    "username":"用户名",
    "password":"密码",
    "port":"端口",
    "reinstall_times":"重装次数限制",
    "buy_times":"超出次数是否可以购买次数",
    "reinstall_price":"重装次数价格",
    "area":[{//区域
      "id":"区域ID",
      "area":"区域代码",
      "name":"区域名称",
    }]
    "user_prefix":"财务标识",
    "account_type":"账号类型",
  }
}
```

### 删除服务器 --  DELETE /admin/dcimcloud/server

- controller: ``app\admin\controller\DcimCloudController::delServer``
- desc: 删除服务器 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务器列表 --  GET /admin/dcimcloud/server

- controller: ``app\admin\controller\DcimCloudController::serverList``
- desc: 服务器列表 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,name,hostname,server_num,api_status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"服务器ID",
      "name":"服务器名称",
      "hostname":"服务器地址",
      "server_num":"服务器数量",
      "api_status":"连接状态",
      "removable":"是否可以删除",
      "reinstall_times":"重装次数限制",
      "buy_times":"超出次数是否可以购买次数",
      "reinstall_price":"重装次数价格",
      "user_prefix":"财务标识",
      "account_type":"账号类型(admin=管理员,agent=代理商)",
    }]
  }
}
```

### 获取服务器状态 --  GET /admin/dcimcloud/server/:id/status

- controller: ``app\admin\controller\DcimCloudController::refreshServerStatus``
- desc: 获取服务器状态 -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 服务器ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "server_status":"服务器状态(1连接测试成功,0失败)",
  }
}
```

### 刷新所有服务器状态 --  GET /admin/dcimcloud/server/status

- controller: ``app\admin\controller\DcimCloudController::refreshAllServerStatus``
- desc: 刷新所有服务器状态 -- huanghao

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "0":[{//列表数据
      "id":"服务器ID",
      "status":"服务器状态(0连接失败1连接成功)",
      "msg":"连接描述",
    }]
  }
}
```


---

## 上游资源管理模块

### 上游列表 -- GET admin/upper/index

- controller: ``app\admin\controller\UpperReachesController::index``
- desc: 上游列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 非必填 | - | - | id |
| name | 字符串 | 非必填 | - | - | 用户名 |
| phone | 字符串 | 非必填 | - | - | 联系方式 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| type | 字符串 | 非必填 | - | - | 查询类型(all为查全部) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "id":"id",
      "name":"姓名",
      "phone":"联系方式",
      "bz":"备注",
    }]
  }
}
```

### 上游添加 -- POST /admin/upper/addpost

- controller: ``app\admin\controller\UpperReachesController::addPost``
- desc: 上游添加 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 用户名 |
| phone | 字符串 | 必填 | - | - | 联系方式 |
| bz | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上游修改 -- POST /admin/upper/edituppost

- controller: ``app\admin\controller\UpperReachesController::editupPost``
- desc: 上游修改 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | id |
| name | 字符串 | 非必填 | - | - | 用户名 |
| phone | 字符串 | 非必填 | - | - | 联系方式 |
| bz | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上游删除 -- POST /admin/upper/del

- controller: ``app\admin\controller\UpperReachesController::delup``
- desc: 上游删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源管理列表 -- GET /admin/upper/upperindex

- controller: ``app\admin\controller\UpperReachesController::upperIndex``
- desc: 资源管理列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| api_id | 字符串 | 非必填 | - | - | 接口ID(接口里传) |
| in_ip | 字符串 | 非必填 | - | - | 主ip |
| pid | 字符串 | 非必填 | - | - | 上游 |
| keyword | 字符串 | 非必填 | - | - | 关键字 |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| type | 字符串 | 非必填 | - | - | 查询类型(all为查全部) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "id":"id",
      "uname":"上游姓名",
      "ip":"ip",
      "pz":"配置",
      "mark":"备注",
      "ipmi":"ipmi",
      "ipmijq":"ipmi鉴权",
      "total":"成本",
      "names":"关联客户",
      "nextduedate":"到期时间1",
      "paidtime":"到期时间2",
      "button":"控制方式支持功能(status电源状态,on开机,off关机,reboot重启,vnc)",
      "username":"用户名",
      "password":"密码",
    }]
    "apis":[{//上游
    }]
  }
}
```

### 资源配置添加界面 -- GET /admin/upper/addupperpage

- controller: ``app\admin\controller\UpperReachesController::addUpperPage``
- desc: 资源配置添加界面 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "id":"id",
      "name":"上游姓名",
      "phone":"手机",
      "bz":"配置",
    }]
  }
}
```

### 资源配置添加 -- POST /admin/upper/addupperpost

- controller: ``app\admin\controller\UpperReachesController::addUpperPost``
- desc: 资源配置添加 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| in_ip | 字符串 | 必填 | - | - | 主ip |
| ip | 字符串 | 必填 | - | - | ip |
| ipmi | 字符串 | 必填 | - | - | ipmi |
| pz | 字符串 | 必填 | - | - | 配置 |
| id | 字符串 | 必填 | - | - | 上游id |
| root | 字符串 | 必填 | - | - | 用户名 |
| pwd | 字符串 | 必填 | - | - | 密码 |
| total | 浮点型 | 必填 | - | - | 成本 |
| paid_time | 整型 | 必填 | - | - | 到期时间 |
| control_mode | 字符串 | 必填 | - | - | 控制方式(ipmi,not_support) |
| ipmi_version | 字符串 | 必填 | - | - | ipmi版本(1.5,2.0) |
| dcim_client_url | 字符串 | 必填 | - | - | DCIM客户端地址(http头加域名) |
| dcim_client_id | 整型 | 必填 | - | - | 服务器ID(用户名为IP时可不传) |
| mark | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源配置修改界面 -- GET /admin/upper/editupperpage

- controller: ``app\admin\controller\UpperReachesController::editUpperPage``
- desc: 资源配置修改界面 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 非必填 | - | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源配置修改 -- POST /admin/upper/editupperpost

- controller: ``app\admin\controller\UpperReachesController::editUpperPost``
- desc: 资源配置修改 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| in_ip | 字符串 | 必填 | - | - | 主ip |
| ip | 字符串 | 必填 | - | - | ip |
| ipmi | 字符串 | 必填 | - | - | ipmi |
| pz | 字符串 | 必填 | - | - | 配置 |
| id | 字符串 | 必填 | - | - | 上游id |
| root | 字符串 | 必填 | - | - | 用户名 |
| pwd | 字符串 | 必填 | - | - | 密码 |
| total | 浮点型 | 必填 | - | - | 成本 |
| paid_time | 整型 | 必填 | - | - | 到期时间 |
| control_mode | 字符串 | 必填 | - | - | 控制方式(ipmi,not_support) |
| ipmi_version | 字符串 | 必填 | - | - | ipmi版本(1.5,2.0) |
| dcim_client_url | 字符串 | 必填 | - | - | DCIM客户端地址(http头加域名) |
| dcim_client_id | 整型 | 非必填 | - | - | 服务器ID(用户名为IP时可不传) |
| mark | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源配置删除 -- POST /admin/upper/delupper

- controller: ``app\admin\controller\UpperReachesController::delUpper``
- desc: 资源配置删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源分配 -- POST /admin/upper/allotupper

- controller: ``app\admin\controller\UpperReachesController::allotUpper``
- desc: 资源分配 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | id |
| hid | 整型 | 非必填 | - | - | 主机id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源空闲 -- POST /admin/upper/emptyupper

- controller: ``app\admin\controller\UpperReachesController::emptyUpper``
- desc: 资源空闲 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### IPMI获取电源状态 -- GET /admin/upper/ipmi/status

- controller: ``app\admin\controller\UpperReachesController::ipmiStatus``
- desc: IPMI获取电源状态 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### IPMI开机 -- POST /admin/upper/ipmi/on

- controller: ``app\admin\controller\UpperReachesController::ipmiOn``
- desc: IPMI开机 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### IPMI关机 -- POST /admin/upper/ipmi/off

- controller: ``app\admin\controller\UpperReachesController::ipmiOff``
- desc: IPMI关机 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### IPMI重启 -- POST /admin/upper/ipmi/reboot

- controller: ``app\admin\controller\UpperReachesController::ipmiReboot``
- desc: IPMI重启 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### IPMI VNC -- POST /admin/upper/ipmi/vnc

- controller: ``app\admin\controller\UpperReachesController::ipmiVnc``
- desc: IPMI VNC -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "vnc_url":"VNC地址",
  }
}
```

### DCIM客户端获取电源状态 -- GET /admin/upper/dcim_client/status

- controller: ``app\admin\controller\UpperReachesController::dcimClientStatus``
- desc: DCIM客户端获取电源状态 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### DCIM客户端开机 -- POST /admin/upper/dcim_client/on

- controller: ``app\admin\controller\UpperReachesController::dcimClientOn``
- desc: DCIM客户端开机 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### DCIM客户端关机 -- POST /admin/upper/dcim_client/off

- controller: ``app\admin\controller\UpperReachesController::dcimClientOff``
- desc: DCIM客户端关机 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### DCIM客户端重启 -- POST /admin/upper/dcim_client/reboot

- controller: ``app\admin\controller\UpperReachesController::dcimClientReboot``
- desc: DCIM客户端重启 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "power_status":"电源状态on开机,off关机,error错误",
  }
}
```

### DCIM客户端VNC -- POST /admin/upper/dcim_client/vnc

- controller: ``app\admin\controller\UpperReachesController::dcimClientVnc``
- desc: DCIM客户端VNC -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//基础数据
      "password":"密码",
      "url":"VNC地址",
    }]
  }
}
```

### novnc页面 --  GET /admin/upper/dcim_client/vnc

- controller: ``app\admin\controller\UpperReachesController::dcimClientVncPage``
- desc: novnc页面 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| password | 字符串 | 必填 | - | - | novnc返回的密码 |
| url | 整型 | 必填 | - | - | novnc返回的url |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### DCIM客户端重装系统 -- POST /admin/upper/dcim_client/reinstall

- controller: ``app\admin\controller\UpperReachesController::dcimClientReinstall``
- desc: DCIM客户端重装系统 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |
| os | 整型 | 必填 | - | - | 操作系统ID |
| password | 字符串 | 必填 | - | - | 密码(六位以上且由大小写字母数字三种组成) |
| port | 整型 | 必填 | - | - | 端口号 |
| part_type | 整型 | 非必填 | 0 | - | 分区类型(windows才有0全盘格式化1第一分区格式化) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### DCIM客户端破解密码 -- POST /admin/upper/dcim_client/crack_pass

- controller: ``app\admin\controller\UpperReachesController::dcimClientCrackPass``
- desc: DCIM客户端破解密码 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |
| password | 字符串 | 必填 | - | - | 密码(六位以上且由大小写字母数字三种组成) |
| other_user | 整型 | 必填 | - | - | 是否破解其他用户(0:否1:是) |
| user | 字符串 | 非必填 | - | - | 要破解的其他用户名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### DCIM客户端取消重装,救援,重置密码 -- POST /admin/upper/dcim_client/cancel_task

- controller: ``app\admin\controller\UpperReachesController::dcimClientCancelReinstall``
- desc: DCIM客户端取消重装,救援,重置密码 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### DCIM客户端获取重装,重置密码进度 -- POST /admin/upper/dcim_client/resintall_status

- controller: ``app\admin\controller\UpperReachesController::dcimClientReinstallStatus``
- desc: DCIM客户端获取重装,重置密码进度 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "disk_check":[{//弹出错误时
      "value":"disk_part的值",
      "description":"描述",
    }]
    "error_type":"0,1,2,其他(当error_type>0并且progress>=20时弹出磁盘分区错误提示,1Windows磁盘错误,2Windows分区错误,其他Windows磁盘分区提示)",
    "error_msg":"当error_type>0时弹出磁盘分区错误提示信息",
    "disk_info":[{//当显示弹出磁盘分区错误提示
      "disk":"磁盘",
      "part":"分区",
      "size":"大小",
      "type":"类型",
      "windows":"类型",
    }]
    "progress":"进度",
    "windows_finish":"是否是windows已完成",
    "hostid":"当前产品ID",
    "task_type":"类型(0重装系统,1救援系统,2重置密码,3获取硬件信息)",
    "reinstall_msg":"重装信息",
    "crackPwd":[{//当有数据返回时,弹出重置密码用户选择
      "user":"可选择的用户",
      "password":"重置的密码",
    }]
    "step":"当前步骤描述",
    "last_result":[{//上次执行结果
      "act":"操作名称",
      "status":"1成功",
      "msg":"描述",
    }]
  }
}
```

### DCIM客户端获取操作系统 -- POST /admin/upper/dcim_client/get_os

- controller: ``app\admin\controller\UpperReachesController::dcimClientGetOs``
- desc: DCIM客户端获取操作系统 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 资源id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 魔方财务接口管理

### 添加魔方财务API -- POST /admin/zjmf_finance_api

- controller: ``app\admin\controller\ZjmfFinanceApiController::createApi``
- desc: 添加魔方财务API -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| hostname | 字符串 | 必填 | - | - | 地址(IP或者域名) |
| username | 字符串 | 必填 | - | - | 用户名 |
| password | 字符串 | 必填 | - | - | 密码 |
| des | 字符串 | 必填 | - | - | 备注 |
| type | 字符串 | 必填 | - | - | 接口类型：zjmf_api智简魔方，manual手动，v10 |
| contact_way | 字符串 | 必填 | - | - | 联系方式 |
| - | 字符串 | 非必填 | - | - | 自动回复开关 |
| - | 字符串 | 非必填 | - | - | 自动回复账号 |
| - | tinyint | 非必填 | - | - | 前台订购实时更新库存和商品,1开启默认,0关闭 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改魔方财务API -- PUT /admin/zjmf_finance_api

- controller: ``app\admin\controller\ZjmfFinanceApiController::modifyApi``
- desc: 修改魔方财务API -- huanghao

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | id |
| name | 字符串 | 必填 | - | - | 名称 |
| hostname | 字符串 | 必填 | - | - | 接口地址(IP或者域名) |
| username | 字符串 | 必填 | - | - | 用户名 |
| password | 字符串 | 必填 | - | - | 密码 |
| type | 字符串 | 必填 | - | - | 接口类型：zjmf_api智简魔方，manual手动 |
| contact_way | 字符串 | 非必填 | - | - | 联系方式 |
| - | 字符串 | 非必填 | - | - | 自动回复开关 |
| - | 字符串 | 非必填 | - | - | 自动回复账号 |
| - | tinyint | 非必填 | - | - | 前台订购实时更新库存和商品,1开启默认,0关闭 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 魔方财务API详情 -- GET /admin/zjmf_finance_api/:id

- controller: ``app\admin\controller\ZjmfFinanceApiController::detail``
- desc: 魔方财务API详情 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "name":"名称",
    "hostname":"接口地址",
    "username":"用户名",
    "password":"密码",
    "status":"连接状态",
    "product_num":"可售商品总数",
    "create_time":"创建时间戳",
  }
}
```

### 删除魔方财务API -- DELETE /admin/zjmf_finance_api/:id

- controller: ``app\admin\controller\ZjmfFinanceApiController::deleteApi``
- desc: 删除魔方财务API -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 魔方财务API列表 -- GET /admin/zjmf_finance_api

- controller: ``app\admin\controller\ZjmfFinanceApiController::index``
- desc: 魔方财务API列表 -- hh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,name,hostname,server_num,api_status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"ID",
      "name":"名称",
      "hostname":"接口地址",
      "username":"用户名",
      "password":"密码",
      "status":"连接状态(0异常1正常)",
      "product_num":"可售商品数量",
      "set_product_num":"设置商品数量",
      "active_host_num":"正常产品数量",
      "host_num":"总产品数量",
      "type_zh":"接口类型：zjmf_api智简魔方，manual手动",
      "contact_way":"联系方式",
      "autoreply_isopen":"自动回复开关",
      "autoreply_account":"自动回复账号",
    }]
  }
}
```

### 刷新魔方财务API状态 -- GET /admin/zjmf_finance_api/:id/status

- controller: ``app\admin\controller\ZjmfFinanceApiController::refreshStatus``
- desc: 刷新魔方财务API状态 -- hh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### API概览 -- GET /admin/zjmf_finance_api/summary

- controller: ``app\admin\controller\ZjmfFinanceApiController::summary``
- desc: API概览 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client":[{//基础数据
      "api_password":"api密钥api_create_time:开通时间agent_count:代理商品数量host_count:API产品数量",
      "active_count":"API产品数量",
      "api_count":"昨日api请求次数ratio:日环比up:1上升，0下降lock_reason:锁定原因api_lock_time:锁定时间",
    }]
    "form_api":"最近7天每天的api请求次数",
    "free_products":[{//豁免产品
      "id":"name:名称ontrial:试用数量qty:最大购买数量",
    }]
  }
}
```

### 开启/关闭API功能 -- POST admin/zjmf_finance_api/open

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiOpen``
- desc: 开启/关闭API功能 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |
| api_open | 整型 | 必填 | - | - | 1开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置秘钥 -- POST /admin/zjmf_finance_api/reset

- controller: ``app\admin\controller\ZjmfFinanceApiController::resetApiPwd``
- desc: 重置秘钥 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### api锁定 -- POST /admin/zjmf_finance_api/toggle

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiToggle``
- desc: api锁定 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |
| api_open | 整型 | 必填 | - | - | 1开启,0关闭,2锁定 |
| lock_reason | 整型 | 必填 | - | - | 1开启,0关闭,2锁定 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 新增,编辑豁免产品页面 -- GET /admin/zjmf_finance_api/freepage

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiFreePage``
- desc: 新增,编辑豁免产品页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 豁免产品ID(编辑时需要此参数) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "groups":[{//产品组
      "products":"产品",
    }]
    "free_product":[{//豁免产品信息
      "id":"ontrial:试用数量qty:最大购买数量",
    }]
  }
}
```

### 新增,编辑豁免产品 -- POST /admin/zjmf_finance_api/freepage

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiFreePost``
- desc: 新增,编辑豁免产品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |
| id | 整型 | 非必填 | - | - | 豁免产品ID(编辑时需要此参数) |
| pids[] | 数组 | 非必填 | - | - | 产品ID |
| ontrial | 整型 | 非必填 | - | - | 试用数量 |
| qty | 整型 | 非必填 | - | - | 最大购买数量 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除豁免产品 -- DELETE /admin/zjmf_finance_api/freepage

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiFreeDelete``
- desc: 删除豁免产品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 豁免产品ID(编辑时需要此参数) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商品列表 -- GET /admin/zjmf_finance_api/products

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiProducts``
- desc: 商品列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | API |
| keyword | mixed | 非必填 | - | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//列表
      "id":"name：产品名称gname:分类名称qty:本地",
      "upstream_qty":"上游",
      "host_count":"数量",
      "host_active":"激活type_zh：类型billingcycle_zh:周期product_price：价格product_shopping_url：链接",
    }]
    "product_count":"产品 总数",
    "local_qty":"本地 库存 总",
    "upstream_qty":"上游 库存 总",
    "host_total":"数量 总",
    "host_active":"激活 总",
  }
}
```

### 订单列表 -- GET admin/zjmf_finance_api/order

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiOrder``
- desc: 订单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |
| sale_id | 整型 | 非必填 | - | - | 1, |
| zjmf_api_id | 整型 | 非必填 | - | - | 魔方api |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "pay_status":"付款状态",
      "status":"状态",
    }]
  }
}
```

### 订单提成 -- POST admin/zjmf_finance_api/order_commission

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiOrderCom``
- desc: 订单提成 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| username | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//列表
    }]
  }
}
```

### 产品列表 -- GET /admin/zjmf_finance_api/host

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiHost``
- desc: 产品列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | API |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| keyword | mix | 非必填 | - | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//列表
      "id":"name：产品名称dedicatedip:IPusername:用户名domain:主机名password:密码product_price:本地价格cost:成本profit:利润create_time：订购时间nextduedate:到期时间billingcycle:付款周期billingcycle_zhfirstpaymentamount:首付金额amount:续费金额promo_code:优惠码domainstatus:状态domainstatus_zh:状态，包括颜色notes:客户备注remark:管理员备注saler:销售type_zh:类型prefix:货币单位initiative_renew:是否自动续费assignedips:其他ipcompanyname:公司名",
    }]
  }
}
```

### 批量拉取产品信息的前台会员中心接口 -- POST /admin/zjmf_finance_api/upstreamhost

- controller: ``app\admin\controller\ZjmfFinanceApiController::upstreamHost``
- desc: 批量拉取产品信息的前台会员中心接口 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 接口ID |
| hostid[] | 整型 | 必填 | - | - | 产品ID，数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "hosts":[{//基础数据
      "domain":"主机dedicatedip:ipassignedips：附加ipcreate_time：购买时间nextduedate：到期时间billingcycle：周期billingcycle_zhfirstpaymentamount：首付金额amount：续费金额port：端口username：用户名password：密码initiative_renew：自动续费domainstatus：状态domainstatus_zh",
    }]
    "currency":"货币单位",
  }
}
```

### API概览 -- GET /admin/zjmf_finance_api/downstream_summary

- controller: ``app\admin\controller\ZjmfFinanceApiController::downstreamSummary``
- desc: API概览 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | API |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client":[{//基础数据
      "api_password":"api密钥api_create_time:开通时间agent_count:代理商品数量host_count:API产品数量",
      "active_count":"API产品数量",
      "api_count":"昨日api请求次数ratio:日环比up:1上升，0下降",
    }]
    "form_api":"最近7天每天的api请求次数",
    "free_products":[{//豁免产品
      "id":"name:名称ontrial:试用数量qty:最大购买数量",
    }]
  }
}
```

### API日志 -- GET /admin/zjmf_finance_api/logs

- controller: ``app\admin\controller\ZjmfFinanceApiController::apiLog``
- desc: API日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 客户ID，单个客户日志需要传 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| order | 字符串 | 非必填 | id | - | 排序(id,name,hostname,server_num,api_status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search_time | 整型 | 非必填 | - | - | 传入时间戳，返回当天日志 |
| search_desc | 字符串 | 非必填 | - | - | 通过描述查询 |
| search_ip | 字符串 | 非必填 | - | - | ip地址查询 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "logs":[{//日志
    }]
  }
}
```

### 产品添加页面 -- GET /admin/zjmf_finance_api/addpage

- controller: ``app\admin\controller\ZjmfFinanceApiController::addPage``
- desc: 产品添加页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "groupdata":[{//产品组数据
      "id":"组ID",
      "name":"组名称",
    }]
  }
}
```

### 导入商品 -- POST /admin/zjmf_finance_api/inputproduct

- controller: ``app\admin\controller\ZjmfFinanceApiController::inputProduct``
- desc: 导入商品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| gid | number | 必填 | - | - | 组ID |
| productnames[upstream_pid] | 字符串 | 必填 | - | - | 值为产品名称,键为上游产品ID(数组,这里就是上游商品的名称) |
| upstream_price_value | 字符串 | 非必填 | - | - | 利润百分比 |
| ptype | 字符串 | 非必填 | - | - | 导航类型 |
| zjmf_finance_api_id | 整型 | 必填 | - | - | 魔方财务api |
| type[upstream_pid] | 整型 | 必填 | - | - | 模块类型 |
| rate | 浮点型 | 非必填 | - | - | 汇率(上下游汇率不一样时,需要传此值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 手动资源人工记录信息 -- GET /admin/zjmf_finance_api/manualhost

- controller: ``app\admin\controller\ZjmfFinanceApiController::getManualHost``
- desc: 手动资源人工记录信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostid[] | 整型 | 必填 | - | - | 产品ID，数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "upper_manual":[{//基础信息
      "id":"hid:regate:到期时间amount:金额billingcycle:周期dedicatedip:ipassignedips:分配ipcreate_time:开通时间",
    }]
  }
}
```

### 手动资源人工记录信息 -- POST /admin/zjmf_finance_api/manualhost

- controller: ``app\admin\controller\ZjmfFinanceApiController::postManualHost``
- desc: 手动资源人工记录信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 编辑时传 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "upper_manual":[{//基础信息
      "id":"hid:regate:到期时间",
      "amount":"金额billingcycle:周期dedicatedip:ipassignedips:分配ipcreate_time:开通时间",
    }]
  }
}
```

### 获取余额 -- GET /admin/zjmf_finance_api/upstreamcredit

- controller: ``app\admin\controller\ZjmfFinanceApiController::upstreamCredit``
- desc: 获取余额 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 接口ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "credit":"余额",
    "currency":"货币属性",
  }
}
```

### 续费订单列表 -- GET admin/zjmf_finance_api/renew

- controller: ``app\admin\controller\ZjmfFinanceApiController::getRenew``
- desc: 续费订单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| zjmf_api_id | 整型 | 非必填 | - | - | 智简魔方api |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| status | 整型 | 非必填 | - | - | 支付状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "status":"状态",
    }]
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\ZjmfFinanceApiController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\ZjmfFinanceApiController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\ZjmfFinanceApiController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\ZjmfFinanceApiController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\ZjmfFinanceApiController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\ZjmfFinanceApiController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\ZjmfFinanceApiController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\ZjmfFinanceApiController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 营销信息

### 获取营销推送筛选条件 --  GET /admin/sm_type

- controller: ``app\admin\controller\SendMessageBatchController::getSearchParams``
- desc: 获取营销推送筛选条件 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data -类型":"",
    "group -客户分组":"",
    "pgroup -产品分组":"",
    "sale -销售":"",
    "country -国家":"",
    "language -语言":"",
    "domainstatus -主机状态":"",
    "api_type -接口类型":"",
  }
}
```

### 营销信息-推送方式 --  GET /admin/getSendMethod

- controller: ``app\admin\controller\SendMessageBatchController::getSendMethod``
- desc: 营销信息-推送方式 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 短信模板列表 -- GET /admin/mobiletemplate_list

- controller: ``app\admin\controller\SendMessageBatchController::mobiletemplateList``
- desc: 短信模板列表 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| send_type | 字符串 | 必填 | clients | - | clients-按客户，clients_and_host-按商品 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "smsoperator":[{//运营商
    }]
    "templates":[{//模板信息
      "id":"ID",
      "template_id":"模板ID(短信运营商提供)",
      "type":"0大陆，1非大陆",
      "title":"模板标题",
      "content":"模板内容",
      "remark":"备注",
      "status":"0未提交审核，1正在审核，2审核通过，3未通过审核",
    }]
    "default":"邮件默认",
  }
}
```

### 筛选客户集合 -- POST /admin/searchlist

- controller: ``app\admin\controller\SendMessageBatchController::searchList``
- desc: 筛选客户集合 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| send_type | 整型 | 非必填 | 1 | - | 发送类型，clients-按客户，clients_and_host-按商品 |
| client_ids | 数组 | 非必填 | - | - | 客户id |
| client_status | 数组 | 非必填 | - | - | 客户状态 |
| sale_ids | 数组 | 非必填 | - | - | 销售id |
| country | 数组 | 非必填 | - | - | 国家 |
| language | 数组 | 非必填 | - | - | 语言 |
| reg_times | 数组 | 非必填 | - | - | 注册时间，reg_times[0]范围开始天数，reg_times[1]范围结束天数 |
| certifi_status | 数组 | 非必填 | - | - | 实名状态 |
| is_bind_phone | 整型 | 非必填 | - | - | 绑定手机：0-未绑定，1-绑定 |
| is_bind_email | 整型 | 非必填 | - | - | 绑定邮箱：0-未绑定，1-绑定 |
| product_ids | 数组 | 非必填 | - | - | 产品id |
| interface_ids | 数组 | 非必填 | - | - | 接口id |
| domainstatus | 数组 | 非必填 | - | - | 主机状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"客户id",
    "username":"客户姓名",
    "mobile":"客户电话",
    "email":"客户邮箱",
    "host_id":"主机id (筛选类型为 clients_and_host 时返回)",
    "client_id":"主机中的客户id，同id (筛选类型为 clients_and_host 时返回)",
    "host_domain":"主机名 (筛选类型为 clients_and_host 时返回)",
    "product_name":"产品名 (筛选类型为 clients_and_host 时返回)",
    "productid":"产品id (筛选类型为 clients_and_host 时返回)",
    "client_count":"客户总数",
  }
}
```

### 邮件模板列表 -- GET /admin/emailtemplate_list

- controller: ``app\admin\controller\SendMessageBatchController::emailtemplateList``
- desc: 邮件模板列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 非必填 | 1 | - | hostid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".email_list":"邮件列表信息(按type分组显示)",
    ".email_list.type":"类型",
    ".email_list.name":"名称",
    ".email_list.disabled":"0显示默认，1隐藏",
    ".email_list.custom":"0系统邮件默认，1自定义",
    ".default":"邮件默认",
  }
}
```

### 营销推送方式下的 邮件模板参数 -- GET /admin/email_template_params

- controller: ``app\admin\controller\SendMessageBatchController::getEmailTemplateParams``
- desc: 营销推送方式下的 邮件模板参数 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| send_type | 整型 | 非必填 | 1 | - | 发送类型，clients-按客户，clients_and_host-按商品 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 邮件模板基本参数 -- GET /admin/edit_template

- controller: ``app\admin\controller\SendMessageBatchController::editTemplate``
- desc: 邮件模板基本参数 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".base_args":"基础参数",
    ".combine":"模板类型相关参数",
  }
}
```

### 批量发送营销信息 -- POST /admin/sendmessage_post

- controller: ``app\admin\controller\SendMessageBatchController::sendMessagePost``
- desc: 批量发送营销信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| send_type | 数组 | 非必填 | clients | - | 发送类型，clients-按客户，clients_and_host-按商品 |
| send_mothod | 数组 | 必填 | - | - | 发送方式，email-邮件，mobile-手机，wechat-微信，system-站内信 |
| clients | 数组 | 必填 | - | - | 用户信息集，<br>send_type=clients，则包含用户基本信息<br>send_type=clients_and_host，则包含用户基本信息和产品信息 |
| email_subject | 字符串 | 必填 | - | - | 邮件主题 |
| email_attachments | 数组 | 非必填 | - | - | 邮件附件 |
| email_message | 字符串 | 必填 | - | - | 邮件内容 |
| system_subject | 字符串 | 必填 | - | - | 站内信主题 |
| system_attachments | 数组 | 非必填 | - | - | 站内信附件 |
| system_message | 字符串 | 必填 | - | - | 站内信内容 |
| msgid | 整型 | 非必填 | - | - | 短信模板ID |
| is_market | 整型 | 非必填 | 0 | - | 营销信息：0-否，1-是 |
| repeat_sent | 整型 | 非必填 | 0 | - | 重复发送：0-否，1-是 |
| batch_num | 整型 | 非必填 | 30 | - | 批量发送个数 |
| delay_time | 整型 | 非必填 | 10 | - | 间隔时间,秒 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取进度 -- GET /admin/get_progress

- controller: ``app\admin\controller\SendMessageBatchController::getProgress``
- desc: 获取进度 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| identy | 字符串 | 非必填 | 1 | - | 标识符 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".total":"总数",
    ".done":"已完成",
    ".done":"成功",
    ".fail":"失败",
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\SendMessageBatchController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\SendMessageBatchController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\SendMessageBatchController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\SendMessageBatchController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\SendMessageBatchController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\SendMessageBatchController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\SendMessageBatchController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\SendMessageBatchController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 开发者中心(前台)

### 导航菜单 -- GET /developer/navlist

- controller: ``app\home\controller\DeveloperController::getnavlist``
- desc: 导航菜单 -- xiong

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
    }]
  }
}
```

### 开发者入驻 页面 -- GET /developer/developer

- controller: ``app\home\controller\DeveloperController::getDeveloper``
- desc: 开发者入驻 页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".certif":"是否实名认证：0 未认证,1 已认证",
    ".phone":"是否手机绑定",
    ".developer":[{//开发者信息
    }]
  }
}
```

### 开发者入驻 -- POST /developer/developer

- controller: ``app\home\controller\DeveloperController::postDeveloper``
- desc: 开发者入驻 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | 昵称 |
| qq | 字符串 | 必填 | 1 | - | 联系qq |
| email | 字符串 | 必填 | 1 | - | 电子邮箱 |
| web | 字符串 | 必填 | 1 | - | 作者网站 |
| desc | 字符串 | 必填 | 1 | - | 简介 |
| type | 整型 | 必填 | 1 | - | 1开发者2服务商3开发者和服务商 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资料管理 -- PUT /developer/developer

- controller: ``app\home\controller\DeveloperController::putDeveloper``
- desc: 资料管理 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | 昵称 |
| qq | 字符串 | 必填 | 1 | - | 联系qq |
| email | 字符串 | 必填 | 1 | - | 电子邮箱 |
| web | 字符串 | 必填 | 1 | - | 作者网站 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 店铺管理 -- PUT /developer/shop

- controller: ``app\home\controller\DeveloperController::putShop``
- desc: 店铺管理 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| desc | 字符串 | 必填 | - | - | 简介 |
| logo | 字符串 | 必填 | - | - | logo |
| shop_header_type | 整型 | 必填 | 1 | - | 商店头部0:关闭1:banner2:自定义 |
| banner | 字符串 | 必填 | - | - | banner图 |
| banner_url | 字符串 | 非必填 | - | - | banner跳转地址 |
| custom_html | 字符串 | 非必填 | - | - | 自定义html |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用列表 -- GET /developer/developerapplist

- controller: ``app\home\controller\DeveloperController::getDeveloperAppList``
- desc: 应用列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//应用信息
      "id":"应用IDname:名称type:类型info:简述pay_type:出售方式pricing:价格unretired_time:上架时间status:状态:0上架,1下架,2已驳回,3审核中create_time:创建时间update_time:更新时间：当此值>0时,提交时间取此值reason:驳回原因count:出售笔数total_price:出售总额",
    }]
    "currency":"货币信息",
  }
}
```

### 应用详情 -- GET /developer/developerappdetail

- controller: ``app\home\controller\DeveloperController::getDeveloperAppDetail``
- desc: 应用详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传,编辑时传)@ |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//应用信息(编辑时才有)
      "retired":"应用状态:0已上架,1已下架app_status:应用审核状态:应用审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:应用名称info:应用简述type:应用类型version:支持版本description:应用描述instruction:应用说明icon:应用图标pay_type:销售方式pricing:销售价格version_description:版本描述",
    }]
    "product_type":"应用类型--所有",
    "version":"应用版本--所有",
    "currency":"货币",
  }
}
```

### 创建(编辑)应用页面 -- GET /developer/developerapp

- controller: ``app\home\controller\DeveloperController::getDeveloperApp``
- desc: 创建(编辑)应用页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传,编辑时传)@ |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//应用信息(编辑时才有)
      "retired":"应用状态:0已上架,1已下架app_status:应用审核状态:应用审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:应用名称info:应用简述type:应用类型version:支持版本description:应用描述instruction:应用说明icon:应用图标pay_type:销售方式pricing:销售价格version_description:版本描述",
    }]
    "product_type":"应用类型--所有",
    "version":"应用版本--所有",
    "currency":"货币",
  }
}
```

### 创建(编辑)应用 -- POST /developer/developerapp

- controller: ``app\home\controller\DeveloperController::postDeveloperApp``
- desc: 创建(编辑)应用 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传,编辑时传) |
| name | 字符串 | 必填 | 1 | - | 应用名称 |
| uuid | 字符串 | 必填 | 1 | - | 应用标识 |
| type | 字符串 | 必填 | 1 | - | 应用类型 |
| version[] | 字符串 | 必填 | 1 | - | 应用版本:多选（数组）注意：选择所有all时,传version |
| description | 字符串 | 非必填 | 1 | - | 应用描述 |
| app_file[] | 字符串 | 非必填 | 1 | - | 应用文件(数组) |
| icon[] | 字符串 | 非必填 | 1 | - | 应用图标(数组) |
| app_images[] | 字符串 | 非必填 | 1 | - | 应用图片(数组) |
| pay_type | 字符串 | 非必填 | 1 | - | 出售方式 |
| currency[货币ID][周期(onetime,monthly,annually)] | 字符串 | 非必填 | 1 | - | 价格 |
| app_type | 字符串 | 非必填 | 1 | - | 应用所属模块:addons插件，gateways支付接口，servers模块,systems官方应用 |
| version_description | 字符串 | 非必填 | 1 | - | 版本描述 |
| app_version | 字符串 | 必填 | 1 | - | 应用版本 |
| professional_discount | 整型 | 必填 | 1 | - | 专业版折扣 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上/下架应用 -- POST /developer/toggleretired

- controller: ``app\home\controller\DeveloperController::postToggleRetired``
- desc: 上/下架应用 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |
| retired | 整型 | 必填 | 1 | - | 0上架,1下架 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除应用 -- DELETE /developer/developerapp

- controller: ``app\home\controller\DeveloperController::deleteDeveloperApp``
- desc: 删除应用 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 交易流水 -- GET /developer/appaccounts

- controller: ``app\home\controller\DeveloperController::getAppAccounts``
- desc: 交易流水 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传参数,单个应用传此值,获取所有交易流水时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "accounts":[{//交易流水
      "trans_id":"交易流水号username:购买人pay_time:支付时间gateway:支付方式amount_in:支付金额refund:当refund>0时,表示退款，展示amount_in",
    }]
    "count":"总数",
  }
}
```

### 举报通知 -- GET /developer/developerappreports

- controller: ``app\home\controller\DeveloperController::getDeveloperAppReports``
- desc: 举报通知 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传参数,单个应用才传此值,获取所有应用日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因name:应用名称user_login:操作人，user_login为null时,显示username",
    }]
  }
}
```

### 日志 -- GET /developer/developerapplogs

- controller: ``app\home\controller\DeveloperController::getDeveloperAppLogs``
- desc: 日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传参数,单个应用才传此值,获取所有应用日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因name:应用名称user_login:操作人，user_login为null时,显示username",
    }]
  }
}
```

### 应用收益 -- GET /developer/developerappincome

- controller: ``app\home\controller\DeveloperController::getDeveloperAppIncome``
- desc: 应用收益 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传参数,单个应用才传此值,获取所有交易流水时不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "this_month_erver_day_income":"本月每日收益",
    "this_month_erver_day_order":"本月每日订单",
    "last_month_erver_day_order":"上月每日订单",
    "this_day_income":"今日收入",
    "this_day_order":"今日订单量",
    "this_month_income":"本月收入",
    "this_month_order":"本月订单量",
    "total_income":"总收入",
    "total_order":"总订单",
    "sale_info":"销量,total总额,count笔数,name应用名称",
    "currency":"货币信息",
    "near_thirty_every_day_income":"近30天收入",
    "near_thirty_every_day_order":"近30天订单",
    "withdraw":[{//提现信息
      "frozen":"冻结金额",
      "withdrawed":"已提金额",
      "withdrawing":"可提金额",
    }]
  }
}
```

### 已购买应用列表 -- GET /developer/buydeveloperapplist

- controller: ``app\home\controller\DeveloperController::getBuyDeveloperAppList``
- desc: 已购买应用列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 必填 | 1 | - | 产品ID |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//应用信息
      "id":"应用IDproduct_name:应用名称name:开发者amount：费用billingcycle:周期nextduedate:到期时间",
    }]
    "currency":"货币信息",
  }
}
```

### 授权软件交易记录 -- GET /developer/buyappaccounts

- controller: ``app\home\controller\DeveloperController::getBuyAppAccounts``
- desc: 授权软件交易记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 非必填 | 1 | - | 产品id |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "accounts":[{//交易流水
      "trans_id":"交易流水号username:购买人pay_time:支付时间gateway:支付方式amount_in:支付金额refund:当refund>0时,表示退款，展示amount_in",
    }]
    "count":"总数",
  }
}
```

### 活动促销列表 -- GET /developer/activitylist

- controller: ``app\home\controller\DeveloperController::getActivityList``
- desc: 活动促销列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "activity":[{//活动
      "id":"活动IDname:名称desc:描述type:优惠类型object:活动对象start_time:开始时间end_time:结束时间status:状态",
    }]
  }
}
```

### 创建(编辑)活动页面 -- GET /developer/activity

- controller: ``app\home\controller\DeveloperController::getActivity``
- desc: 创建(编辑)活动页面 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 活动ID(非必传,编辑时传)@ |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "activity":[{//活动信息(编辑时才有)
      "id":"活动IDname:名称desc:描述type:优惠类型discount:折扣百分比object:活动对象start_time:开始时间end_time:结束时间status:状态",
    }]
    "product_type":"应用类型--所有",
    "version":"应用版本--所有",
    "currency":"货币",
  }
}
```

### 新增活动 -- POST /developer/activity

- controller: ``app\home\controller\DeveloperController::postActivity``
- desc: 新增活动 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| desc | 字符串 | 必填 | - | - | 描述 |
| type | 整型 | 必填 | - | - | 促销方式(0:打折1:促销价) |
| discount | 整型 | 必填 | - | - | 打折百分比 |
| 对象 | 整型 | 必填 | - | - | 活动对象(0:全部1:专业版2:免费版) |
| start_time | 整型 | 必填 | - | - | 活动开始时间 |
| end_time | 整型 | 必填 | - | - | 活动结束时间 |
| app_price | 数组 | 必填 | - | - | 应用促销价格(传值例如['111':{'onetime':0,'onetime_price':0,'monthly':0,'monthly_price':0,'annually':0,'annually_price':0}],数组下标为应用id,对象里onetime为一次性,onetime_price为一次性价格,monthly为月,annually为年，以此类推) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改活动 -- PUT /developer/activity

- controller: ``app\home\controller\DeveloperController::putActivity``
- desc: 修改活动 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 活动id |
| name | 字符串 | 必填 | - | - | 名称 |
| desc | 字符串 | 必填 | - | - | 描述 |
| type | 整型 | 必填 | - | - | 促销方式(0:打折1:促销价) |
| discount | 整型 | 必填 | - | - | 打折百分比 |
| 对象 | 整型 | 必填 | - | - | 活动对象(0:全部1:专业版2:免费版) |
| start_time | 整型 | 必填 | - | - | 活动开始时间 |
| end_time | 整型 | 必填 | - | - | 活动结束时间 |
| app_price | 数组 | 必填 | - | - | 应用促销价格(传值例如['111':{'onetime':0,'onetime_price':0,'monthly':0,'monthly_price':0,'annually':0,'annually_price':0}],数组下标为应用id,对象里onetime为一次性,onetime_price为一次性价格,monthly为月,annually为年，以此类推) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除活动 -- DELETE /developer/activity

- controller: ``app\home\controller\DeveloperController::deleteActivity``
- desc: 删除活动 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 活动ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务列表 -- GET /developer/developerservicelist

- controller: ``app\home\controller\DeveloperController::getDeveloperServiceList``
- desc: 服务列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//服务信息
      "id":"服务IDname:名称type:类型info:简述pay_type:出售方式pricing:价格unretired_time:上架时间status:状态:0上架,1下架,2已驳回,3审核中create_time:创建时间update_time:更新时间：当此值>0时,提交时间取此值reason:驳回原因count:出售笔数total_price:出售总额",
    }]
    "currency":"货币信息",
  }
}
```

### 服务详情 -- GET /developer/developerservicedetail

- controller: ``app\home\controller\DeveloperController::getDeveloperServiceDetail``
- desc: 服务详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//服务信息(编辑时才有)
      "retired":"服务状态:0已上架,1已下架app_status:服务审核状态:服务审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:服务名称info:服务简述type:服务类型version:支持版本description:服务描述instruction:服务说明icon:服务图标pay_type:销售方式pricing:销售价格version_description:版本描述",
    }]
    "product_type":"服务类型--所有",
    "currency":"货币",
  }
}
```

### 创建(编辑)服务页面 -- GET /developer/developerservice

- controller: ``app\home\controller\DeveloperController::getDeveloperService``
- desc: 创建(编辑)服务页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传,编辑时传)@ |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//服务信息(编辑时才有)
      "retired":"服务状态:0已上架,1已下架app_status:服务审核状态:应用审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:服务名称info:服务简述type:服务类型version:支持版本description:服务描述instruction:服务说明icon:服务图标pay_type:销售方式pricing:销售价格",
    }]
    "product_type":"应用类型--所有",
    "currency":"货币",
  }
}
```

### 创建(编辑)服务 -- POST /developer/developerservice

- controller: ``app\home\controller\DeveloperController::postDeveloperService``
- desc: 创建(编辑)服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID(非必传,编辑时传) |
| name | 字符串 | 必填 | 1 | - | 服务名称 |
| type | 字符串 | 必填 | 1 | - | 服务类型 |
| description | 字符串 | 非必填 | 1 | - | 服务描述 |
| app_images[] | 字符串 | 非必填 | 1 | - | 服务图片(数组) |
| pay_type | 字符串 | 非必填 | 1 | - | 出售方式 |
| currency[货币ID][周期(onetime,monthly,annually)] | 字符串 | 非必填 | 1 | - | 价格 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上/下架服务 -- POST /developer/servicetoggleretired

- controller: ``app\home\controller\DeveloperController::postServiceToggleRetired``
- desc: 上/下架服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID |
| retired | 整型 | 必填 | 1 | - | 0上架,1下架 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除服务 -- DELETE /developer/developerservice

- controller: ``app\home\controller\DeveloperController::deleteDeveloperService``
- desc: 删除服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 交易流水 -- GET /developer/serviceaccounts

- controller: ``app\home\controller\DeveloperController::getServiceAccounts``
- desc: 交易流水 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID(非必传参数,单个服务传此值,获取所有交易流水时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "accounts":[{//交易流水
      "trans_id":"交易流水号username:购买人pay_time:支付时间gateway:支付方式amount_in:支付金额refund:当refund>0时,表示退款，展示amount_in",
    }]
    "count":"总数",
  }
}
```

### 服务举报通知 -- GET /developer/developerservicereports

- controller: ``app\home\controller\DeveloperController::getDeveloperServiceReports``
- desc: 服务举报通知 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID(非必传参数,单个服务才传此值,获取所有服务日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因name:应用名称user_login:操作人，user_login为null时,显示username",
    }]
  }
}
```

### 服务日志 -- GET /developer/developerservicelogs

- controller: ``app\home\controller\DeveloperController::getDeveloperServiceLogs``
- desc: 服务日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID(非必传参数,单个服务才传此值,获取所有应用日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因name:服务名称user_login:操作人，user_login为null时,显示username",
    }]
  }
}
```

### 悬赏开发 -- GET /developer/developerrewardlist

- controller: ``app\home\controller\DeveloperController::getDeveloperRewardList``
- desc: 悬赏开发 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//悬赏信息
      "id":"悬赏IDname:名称total_amount:总金额reward_amount:官方悬赏total_price:购买reward_total_price:打赏withdraw:已提取status:状态status_zh:状态信息",
    }]
    "currency":"货币信息",
  }
}
```

### 悬赏详情 -- GET /developer/developerrewarddetail

- controller: ``app\home\controller\DeveloperController::getDeveloperRewardDetail``
- desc: 悬赏详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":"悬赏信息",
    "product_type":"服务类型--所有",
    "currency":"货币",
  }
}
```

### 交付悬赏页面 -- GET /developer/developerrewardapp

- controller: ``app\home\controller\DeveloperController::getDeveloperRewardApp``
- desc: 交付悬赏页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//应用信息(编辑时才有)
      "retired":"应用状态:0已上架,1已下架app_status:应用审核状态:应用审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:应用名称info:应用简述type:应用类型version:支持版本description:应用描述instruction:应用说明icon:应用图标pay_type:销售方式pricing:销售价格version_description:版本描述",
    }]
    "product_type":"应用类型--所有",
    "version":"应用版本--所有",
    "currency":"货币",
  }
}
```

### 交付悬赏 -- POST /developer/developerrewardapp

- controller: ``app\home\controller\DeveloperController::postDeveloperRewardApp``
- desc: 交付悬赏 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 悬赏ID |
| name | 字符串 | 必填 | 1 | - | 应用名称 |
| uuid | 字符串 | 必填 | 1 | - | 应用标识 |
| info | 字符串 | 非必填 | 1 | - | 应用简述 |
| type | 字符串 | 必填 | 1 | - | 应用类型 |
| version[] | 字符串 | 必填 | 1 | - | 应用版本:多选（数组）注意：选择所有all时,传version |
| description | 字符串 | 非必填 | 1 | - | 应用描述 |
| app_file[] | 字符串 | 非必填 | 1 | - | 应用文件(数组) |
| instruction | 字符串 | 非必填 | 1 | - | 使用说明 |
| icon[] | 字符串 | 非必填 | 1 | - | 应用图标(数组) |
| app_images[] | 字符串 | 非必填 | 1 | - | 应用图片(数组) |
| pay_type | 字符串 | 非必填 | 1 | - | 出售方式 |
| currency[货币ID][周期(onetime,monthly,annually)] | 字符串 | 非必填 | 1 | - | 价格 |
| app_type | 字符串 | 非必填 | 1 | - | 应用所属模块:addons插件，gateways支付接口，servers模块,systems官方应用 |
| version_description | 字符串 | 非必填 | 1 | - | 版本描述 |
| app_version | 字符串 | 必填 | 1 | - | 应用版本 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 悬赏报名 -- POST /developer/developerrewardsignup

- controller: ``app\home\controller\DeveloperController::postDeveloperRewardSignUp``
- desc: 悬赏报名 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 悬赏取消报名 -- DELETE /developer/developerrewardsignup

- controller: ``app\home\controller\DeveloperController::deleteDeveloperRewardSignUp``
- desc: 悬赏取消报名 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 申请退还保证金 -- DELETE /developer/developerrewardreturn

- controller: ``app\home\controller\DeveloperController::deleteDeveloperRewardReturn``
- desc: 申请退还保证金 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上传文件 --  POST /developer/uploadfile

- controller: ``app\home\controller\DeveloperController::postUploadFile``
- desc: 上传文件 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| filename|file | file | 必填 | 0 | - | 文件 |
| type | 字符串 | 必填 | 0 | - | 类型,如avatar,servers |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "上传的文件路径":"",
  }
}
```

### 病毒扫描 --  POST /developer/scannerbaidu

- controller: ``app\home\controller\DeveloperController::postScannerBaidu``
- desc: 病毒扫描 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| savename | 字符串 | 必填 | 0 | - | 文件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "扫描结果":"",
  }
}
```

### 获取病毒扫描结果 --  POST /developer/scannerbaiduresult

- controller: ``app\home\controller\DeveloperController::postScannerBaiduResult``
- desc: 获取病毒扫描结果 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| url | 字符串 | 必填 | 0 | - | 获取扫描结果地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "扫描结果":"",
  }
}
```

### 需求讨论 -- GET /developer/demand

- controller: ``app\home\controller\DeveloperController::getDemand``
- desc: 需求讨论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| type | 字符串 | 非必填 | - | - | 类型(finance,cloud,dcim) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG -- GET /developer/bug

- controller: ``app\home\controller\DeveloperController::getBug``
- desc: BUG -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| classify | 字符串 | 非必填 | - | - | 类型(finance,cloud,dcim,other) |
| status | 字符串 | 非必填 | - | - | 状态 |
| keywords | 字符串 | 非必填 | - | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "status":"状态",
    "classify":"分类",
    "bug":[{//BUG列表
      "id":"BUG",
      "IDuid":"用户IDstatus_zh:状态classify_zh:分类is_bug:是BUGnot_bug:不是BUGtitle:标题username:用户名encounter_num:我也遇到数量views:浏览量is_encounter:我也遇到0未标记1已标记create_time:创建时间",
    }]
  }
}
```

### 互助 -- GET /developer/help

- controller: ``app\home\controller\DeveloperController::getHelp``
- desc: 互助 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| classify | 字符串 | 非必填 | - | - | 类型(finance,cloud,dcim,other) |
| status | 字符串 | 非必填 | - | - | 状态 |
| keywords | 字符串 | 非必填 | - | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "status":"状态",
    "classify":"分类",
    "type":"类型",
    "help":[{//互助列表
      "id":"互助IDuid:用户IDstatus_zh:状态classify_zh:分类type_zh:类型cash:现金integral:魔币title:标题username:用户名like_num:点赞数量views:浏览量create_time:创建时间",
    }]
  }
}
```

### 获取帖子版块信息 --  GET /developer/postspage

- controller: ``app\home\controller\DeveloperController::getPostsPage``
- desc: 获取帖子版块信息 -- xujin

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 锁定需求 --  PUT /developer/demandlock

- controller: ``app\home\controller\DeveloperController::putDemandLock``
- desc: 锁定需求 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 锁定BUG反馈 --  PUT /developer/buglock

- controller: ``app\home\controller\DeveloperController::putBugLock``
- desc: 锁定BUG反馈 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 锁定互助 --  PUT /developer/helplock

- controller: ``app\home\controller\DeveloperController::putHelpLock``
- desc: 锁定互助 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 解除锁定需求 --  PUT /developer/demandunlock

- controller: ``app\home\controller\DeveloperController::putDemandUnlock``
- desc: 解除锁定需求 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 解除锁定BUG反馈 --  PUT /developer/bugunlock

- controller: ``app\home\controller\DeveloperController::putBugUnlock``
- desc: 解除锁定BUG反馈 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 解除锁定互助 --  PUT /developer/helpunlock

- controller: ``app\home\controller\DeveloperController::putHelpUnlock``
- desc: 解除锁定互助 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除需求 --  DELETE /developer/demand

- controller: ``app\home\controller\DeveloperController::deleteDemand``
- desc: 删除需求 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除BUG反馈 --  DELETE /developer/bug

- controller: ``app\home\controller\DeveloperController::deleteBug``
- desc: 删除BUG反馈 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除互助 --  DELETE /developer/help

- controller: ``app\home\controller\DeveloperController::deleteHelp``
- desc: 删除互助 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改需求 --  PUT /developer/demand

- controller: ``app\home\controller\DeveloperController::putDemand``
- desc: 修改需求 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |
| title | 字符串 | 必填 | 0 | - | 标题 |
| content | 字符串 | 必填 | 0 | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改BUG反馈 --  PUT /developer/bug

- controller: ``app\home\controller\DeveloperController::putBug``
- desc: 修改BUG反馈 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |
| title | 字符串 | 必填 | 0 | - | 标题 |
| content | 字符串 | 必填 | 0 | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改互助 --  PUT /developer/help

- controller: ``app\home\controller\DeveloperController::putHelp``
- desc: 修改互助 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |
| title | 字符串 | 必填 | 0 | - | 标题 |
| content | 字符串 | 必填 | 0 | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 置顶需求 --  PUT /developer/demandtop

- controller: ``app\home\controller\DeveloperController::putDemandTop``
- desc: 置顶需求 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |
| order | 整型 | 必填 | 0 | - | 排序 |
| global_top | 整型 | 必填 | 0 | - | 全局置顶0否1是 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 置顶BUG反馈 --  PUT /developer/bugtop

- controller: ``app\home\controller\DeveloperController::putBugTop``
- desc: 置顶BUG反馈 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |
| order | 整型 | 必填 | 0 | - | 排序 |
| global_top | 整型 | 必填 | 0 | - | 全局置顶0否1是 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 置顶互助 --  PUT /developer/helptop

- controller: ``app\home\controller\DeveloperController::putHelpTop``
- desc: 置顶互助 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |
| order | 整型 | 必填 | 0 | - | 排序 |
| global_top | 整型 | 必填 | 0 | - | 全局置顶0否1是 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 取消置顶需求 --  DELETE /developer/demandtop

- controller: ``app\home\controller\DeveloperController::deleteDemandTop``
- desc: 取消置顶需求 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 取消置顶BUG反馈 --  DELETE /developer/bugtop

- controller: ``app\home\controller\DeveloperController::deleteBugTop``
- desc: 取消置顶BUG反馈 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 取消置顶互助 --  DELETE /developer/helptop

- controller: ``app\home\controller\DeveloperController::deleteHelpTop``
- desc: 取消置顶互助 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 移动需求版块 --  PUT /developer/demandsection

- controller: ``app\home\controller\DeveloperController::putDemandSection``
- desc: 移动需求版块 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |
| section | 字符串 | 必填 | 0 | - | 版块 |
| type | 字符串 | 必填 | 0 | - | 类型(互助才有) |
| classify | 字符串 | 必填 | 0 | - | 分类 |
| status | 字符串 | 必填 | 0 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 移动BUG反馈版块 --  PUT /developer/bugsection

- controller: ``app\home\controller\DeveloperController::putBugSection``
- desc: 移动BUG反馈版块 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |
| section | 字符串 | 必填 | 0 | - | 版块 |
| type | 字符串 | 必填 | 0 | - | 类型(互助才有) |
| classify | 字符串 | 必填 | 0 | - | 分类 |
| status | 字符串 | 必填 | 0 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 移动互助版块 --  PUT /developer/helpsection

- controller: ``app\home\controller\DeveloperController::putHelpSection``
- desc: 移动互助版块 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |
| section | 字符串 | 必填 | 0 | - | 版块 |
| type | 字符串 | 必填 | 0 | - | 类型(互助才有) |
| classify | 字符串 | 必填 | 0 | - | 分类 |
| status | 字符串 | 必填 | 0 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改需求分类 --  PUT /developer/demandclassify

- controller: ``app\home\controller\DeveloperController::putDemandClassify``
- desc: 修改需求分类 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |
| classify | 字符串 | 必填 | 0 | - | 分类 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改BUG反馈分类 --  PUT /developer/bugclassify

- controller: ``app\home\controller\DeveloperController::putBugClassify``
- desc: 修改BUG反馈分类 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |
| classify | 字符串 | 必填 | 0 | - | 分类 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改互助分类 --  PUT /developer/helpclassify

- controller: ``app\home\controller\DeveloperController::putHelpClassify``
- desc: 修改互助分类 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |
| classify | 字符串 | 必填 | 0 | - | 分类 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改需求状态 --  PUT /developer/demandstatus

- controller: ``app\home\controller\DeveloperController::putDemandStatus``
- desc: 修改需求状态 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 需求ID |
| status | 字符串 | 必填 | 0 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改BUG反馈状态 --  PUT /developer/bugstatus

- controller: ``app\home\controller\DeveloperController::putBugStatus``
- desc: 修改BUG反馈状态 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | BUG反馈ID |
| status | 字符串 | 必填 | 0 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改互助状态 --  PUT /developer/helpstatus

- controller: ``app\home\controller\DeveloperController::putHelpStatus``
- desc: 修改互助状态 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 0 | - | 互助ID |
| status | 字符串 | 必填 | 0 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取权限 --  GET /developer/auth

- controller: ``app\home\controller\DeveloperController::getAuth``
- desc: 获取权限 -- xujin

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\DeveloperController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\DeveloperController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\DeveloperController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\DeveloperController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 开发者中心

### 搜索页面 -- GET admin/developer/ordersearchpage

- controller: ``app\admin\controller\DeveloperController::getOrderSearchPage``
- desc: 搜索页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 可选参数,用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订单列表 -- GET admin/developer/orderlist

- controller: ``app\admin\controller\DeveloperController::getOrderList``
- desc: 订单列表 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |
| sale_id | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "pay_status":"付款状态",
      "status":"状态",
    }]
  }
}
```

### 开发者列表 -- GET /admin/developer/developerlist

- controller: ``app\admin\controller\DeveloperController::getDeveloperList``
- desc: 开发者列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| developer_status[] | 数组 | 非必填 | 1 | - | 传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "developer":[{//开发者列表
      "name":"昵称phonenumber:手机号certifi_status：认证信息sell_app:可售应用sell:count:销量，total:收入create_time:创建时间status:状态status_zh:中文状态",
    }]
  }
}
```

### 获取开发者详情 -- GET /admin/developer/developerdetail

- controller: ``app\admin\controller\DeveloperController::getDeveloperDetail``
- desc: 获取开发者详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "developer":[{//开发者信息
      "name":"昵称desc:简介status:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败(启用为开，其它都关闭)",
    }]
    "sell_info":"销量信息：total销量,count",
    "app_info":"应用信息,unretired已上架,retired未上架,active可上架,pending审核中,cancelled已驳回",
    "this_day_income":"今日收入",
    "this_day_order":"今日订单量",
    "this_month_income":"本月收入",
    "this_month_order":"本月订单量",
    "total_income":"总收入",
    "total_order":"总订单",
    "sale_info":"销量排行,total总额,count笔数",
    "currency":"货币信息",
    "withdraw":"提现信息",
  }
}
```

### 审核开发者 -- POST /admin/developer/checkdeveloper

- controller: ``app\admin\controller\DeveloperController::postCheckDeveloper``
- desc: 审核开发者 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 开发者ID |
| status | 字符串 | 必填 | 1 | - | Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败 |
| cancelled_reason | 字符串 | 非必填 | 1 | - | 未通过原因(非必传参数),Cancelled必传必填 |
| suspended_reason | 字符串 | 非必填 | 1 | - | 停用原因(非必传参数) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用列表（包括应用审核列表） -- GET /admin/developer/developerapplist

- controller: ``app\admin\controller\DeveloperController::getDeveloperAppList``
- desc: 应用列表（包括应用审核列表） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| developer_status[] | 数组 | 非必填 | 1 | - | (注意：换成下面的参数,这个不传)传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败 |
| status | 整型 | 非必填 | 1 | - | 状态:0上架,1下架,2已驳回,3审核中 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//应用信息
      "id":"应用IDuuid:应用标识nickname:昵称name:应用名称type:类型info:简述pay_type:出售方式pricing:价格unretired_time:上架时间status:状态:0上架,1下架,2已驳回,3审核中,4管理员下架,5用户删除create_time:创建时间update_time:更新时间：当此值>0时,提交时间取此值reason:驳回原因count:出售笔数total_price:出售总额developer_status:开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败app_type_zh:应用模块:addons插件，gateways支付接口，servers模块,systems官方应用",
    }]
    "currency":"货币信息",
  }
}
```

### 上/下架应用 -- POST /admin/developer/toggleretired

- controller: ``app\admin\controller\DeveloperController::postToggleRetired``
- desc: 上/下架应用 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |
| retired | 整型 | 必填 | 1 | - | 0上架,1下架 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用详情 -- GET /admin/developer/developerapp

- controller: ``app\admin\controller\DeveloperController::getDeveloperApp``
- desc: 应用详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//应用信息(编辑时才有)
      "retired":"应用状态:0已上架,1已下架app_status:应用审核状态:应用审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:应用名称info:应用简述type:应用类型version:支持版本(不要此字段)description:应用描述app_file:应用文件instruction:应用说明icon:应用图标pay_type:销售方式pricing:销售价格unretired_time:上架时间",
      "create_time":"提交审核时间",
      "update_time":"sell_info:count销量,total收入certifi_status:认证情况",
    }]
    "developer":[{//开发者信息
      "phonenumber":"手机name:昵称desc:简介",
    }]
    "product_type":"应用类型--所有",
    "version":"应用版本--所有",
    "currency":"货币",
    "developer_app_product_type":"应用类型下拉框，对应关系",
  }
}
```

### 交易流水(开发者应用) -- GET /admin/developer/appaccounts

- controller: ``app\admin\controller\DeveloperController::getAppAccounts``
- desc: 交易流水(开发者应用) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID(非必传参数,单个应用传此值,获取所有交易流水时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "accounts":[{//交易流水
      "trans_id":"交易流水号username:购买人pay_time:支付时间gateway:支付方式amount_in:支付金额name:应用名称p_uid:开发者uidrefund:当refund>0时,表示退款，展示amount_in",
      "currency":"货币信息",
    }]
    "count":"总数",
  }
}
```

### 审核通过/驳回应用 -- POST /admin/developer/checkdeveloperapp

- controller: ``app\admin\controller\DeveloperController::postCheckDeveloperApp``
- desc: 审核通过/驳回应用 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |
| app_status | 整型 | 必填 | 1 | - | 1审核通过,2驳回 |
| reason | 字符串 | 必填 | 1 | - | 驳回原因：非必传参数,当驳回时才传此参数 |
| type | 整型 | 必填 | 1 | - | 0手动上传文件,1自动加密 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用重新加密 -- POST /admin/developer/encryptdeveloperapp

- controller: ``app\admin\controller\DeveloperController::postEncryptDeveloperApp``
- desc: 应用重新加密 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |
| app_file | 字符串 | 必填 | 1 | - | 手动上传文件名 |
| type | 整型 | 必填 | 1 | - | 0手动上传文件,1自动加密 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者应用日志 -- GET admin/developer/developerapplogs

- controller: ``app\admin\controller\DeveloperController::getDeveloperAppLogs``
- desc: 开发者应用日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传参数,单个应用才传此值,获取所有应用日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因",
    }]
  }
}
```

### 删除应用 -- DELETE /admin/developer/developerapp

- controller: ``app\admin\controller\DeveloperController::deleteDeveloperApp``
- desc: 删除应用 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者应用评论 -- GET admin/developer/app_evaluations

- controller: ``app\admin\controller\DeveloperController::getApp_evaluations``
- desc: 开发者应用评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID(非必传参数,单个应用才传此值,获取所有应用日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因",
    }]
  }
}
```

### 开发者应用审核记录 -- GET admin/developer/developerappauditrecords

- controller: ``app\admin\controller\DeveloperController::getDeveloperAppAuditRecords``
- desc: 开发者应用审核记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 应用ID |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "records":[{//记录
      "id":"create_time:时间old_file:旧文件new_file:新文件reason:原因",
    }]
  }
}
```

### 审核记录文件上传 -- POST /admin/developer/uploadfile

- controller: ``app\admin\controller\DeveloperController::postUploadfile``
- desc: 审核记录文件上传 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 审核记录ID |
| app_file | 字符串 | 必填 | 1 | - | 应用文件 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改应用评论 -- PUT /admin/developer/edit_app_evaluation

- controller: ``app\admin\controller\DeveloperController::putEdit_app_evaluation``
- desc: 修改应用评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用评论ID |
| content | 字符串 | 必填 | 1 | - | 内容 |
| score | 字符串 | 必填 | 1 | - | 评分 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 热门应用列表（包括应用审核列表） -- GET /admin/developer/hotapplist

- controller: ``app\admin\controller\DeveloperController::getHotapplist``
- desc: 热门应用列表（包括应用审核列表） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| developer_status[] | 数组 | 非必填 | 1 | - | (注意：换成下面的参数,这个不传)传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//应用信息
      "id":"应用IDuuid:应用标识nickname:昵称name:应用名称type:类型info:简述pay_type:出售方式pricing:价格unretired_time:上架时间create_time:创建时间update_time:更新时间：当此值>0时,提交时间取此值reason:驳回原因count:出售笔数total_price:出售总额developer_status:开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败app_type_zh:应用模块:addons插件，gateways支付接口，servers模块,systems官方应用",
    }]
    "currency":"货币信息",
  }
}
```

### 热门应用列表（包括应用审核列表） -- GET /admin/developer/applist

- controller: ``app\admin\controller\DeveloperController::getApplist``
- desc: 热门应用列表（包括应用审核列表） -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//应用信息
      "id":"应用IDuuid:应用标识name:应用名称",
    }]
  }
}
```

### 修改热门应用排序 -- PUT /admin/developer/hot_app

- controller: ``app\admin\controller\DeveloperController::putHot_app``
- desc: 修改热门应用排序 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用ID |
| order | 整型 | 必填 | 1 | - | 应用排序(1以上的数字) |
| lock | 整型 | 必填 | 0 | - | 是否锁定排序(0:不锁定1:锁定) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推荐作者列表 -- GET /admin/developer/recommendlist

- controller: ``app\admin\controller\DeveloperController::getRecommendlist``
- desc: 推荐作者列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "developer":[{//作者信息
    }]
  }
}
```

### 获取所有开发者 -- GET /admin/developer/developers

- controller: ``app\admin\controller\DeveloperController::getDevelopers``
- desc: 获取所有开发者 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "developer":[{//开发者列表
      "name":"昵称",
    }]
  }
}
```

### 新增推荐作者 -- POST /admin/developer/recommend

- controller: ``app\admin\controller\DeveloperController::postRecommend``
- desc: 新增推荐作者 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 字符串 | 必填 | 1 | - | 用户id |
| order | 整型 | 必填 | 1 | - | 排序(1以上的数字) |
| lock | 整型 | 必填 | 0 | - | 是否锁定排序(0:不锁定1:锁定) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改推荐作者排序 -- PUT /admin/developer/recommend

- controller: ``app\admin\controller\DeveloperController::putRecommend``
- desc: 修改推荐作者排序 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 字符串 | 必填 | 1 | - | 用户id |
| order | 整型 | 必填 | 1 | - | 排序(1以上的数字) |
| lock | 整型 | 必填 | 0 | - | 是否锁定排序(0:不锁定1:锁定) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 移除推荐作者 -- DELETE /admin/developer/recommend

- controller: ``app\admin\controller\DeveloperController::deleteRecommend``
- desc: 移除推荐作者 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用举报审核 -- GET admin/developer/app_reports

- controller: ``app\admin\controller\DeveloperController::getApp_reports``
- desc: 应用举报审核 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| status | 整型 | 非必填 | 1 | - | 状态(0:待处理1:已处理2:已驳回) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "report":[{//举报
      "id":"create_time:时间reason:原因",
    }]
  }
}
```

### 审核通过/驳回应用举报 -- POST /admin/developer/check_app_reports

- controller: ``app\admin\controller\DeveloperController::postCheck_app_reports``
- desc: 审核通过/驳回应用举报 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 应用举报ID |
| status | 整型 | 必填 | 1 | - | 1审核通过,2驳回 |
| remarks | 字符串 | 必填 | 1 | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务列表（包括服务审核列表） -- GET /admin/developer/developerservicelist

- controller: ``app\admin\controller\DeveloperController::getDeveloperServiceList``
- desc: 服务列表（包括服务审核列表） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| developer_status[] | 数组 | 非必填 | 1 | - | (注意：换成下面的参数,这个不传)传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败 |
| status | 整型 | 非必填 | 1 | - | 状态:0上架,1下架,2已驳回,3审核中 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//服务信息
      "id":"服务IDnickname:昵称name:服务名称type:类型info:简述pay_type:出售方式pricing:价格unretired_time:上架时间status:状态:0上架,1下架,2已驳回,3审核中create_time:创建时间update_time:更新时间：当此值>0时,提交时间取此值reason:驳回原因count:出售笔数total_price:出售总额developer_status:开发者状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Review资料审核，Failed资料审核失败",
    }]
    "currency":"货币信息",
  }
}
```

### 上/下架服务 -- POST /admin/developer/servicetoggleretired

- controller: ``app\admin\controller\DeveloperController::postServiceToggleRetired``
- desc: 上/下架服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID |
| retired | 整型 | 必填 | 1 | - | 0上架,1下架 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 服务详情 -- GET /admin/developer/developerservice

- controller: ``app\admin\controller\DeveloperController::getDeveloperService``
- desc: 服务详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//服务信息(编辑时才有)
      "retired":"服务状态:0已上架,1已下架app_status:服务审核状态:服务审核状态:2已驳回,1审核通过，0审核中(非审核通过时,隐藏上、下架按钮)name:服务名称info:服务简述type:服务类型version:支持版本(不要此字段)description:服务描述instruction:服务说明icon:服务图标pay_type:销售方式pricing:销售价格unretired_time:上架时间",
      "create_time":"提交审核时间",
      "update_time":"sell_info:count销量,total收入certifi_status:认证情况",
    }]
    "developer":[{//开发者信息
      "phonenumber":"手机name:昵称desc:简介",
    }]
    "product_type":"服务类型--所有",
    "currency":"货币",
    "developer_app_product_type":"服务类型下拉框，对应关系",
  }
}
```

### 交易流水(开发者服务) -- GET /admin/developer/serviceaccounts

- controller: ``app\admin\controller\DeveloperController::getServiceAccounts``
- desc: 交易流水(开发者服务) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID(非必传参数,单个服务传此值,获取所有交易流水时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "accounts":[{//交易流水
      "trans_id":"交易流水号username:购买人pay_time:支付时间gateway:支付方式amount_in:支付金额name:服务名称p_uid:开发者uidrefund:当refund>0时,表示退款，展示amount_in",
      "currency":"货币信息",
    }]
    "count":"总数",
  }
}
```

### 审核通过/驳回服务 -- POST /admin/developer/checkdeveloperservice

- controller: ``app\admin\controller\DeveloperController::postCheckDeveloperService``
- desc: 审核通过/驳回服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID |
| app_status | 整型 | 必填 | 1 | - | 1审核通过,2驳回 |
| reason | 字符串 | 必填 | 1 | - | 驳回原因：非必传参数,当驳回时才传此参数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者服务日志 -- GET admin/developer/developerservicelogs

- controller: ``app\admin\controller\DeveloperController::getDeveloperServiceLogs``
- desc: 开发者服务日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID(非必传参数,单个服务才传此值,获取所有服务日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因",
    }]
  }
}
```

### 删除服务 -- DELETE /admin/developer/developerservice

- controller: ``app\admin\controller\DeveloperController::deleteDeveloperService``
- desc: 删除服务 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 服务ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者服务评论 -- GET admin/developer/service_evaluations

- controller: ``app\admin\controller\DeveloperController::getService_evaluations``
- desc: 开发者服务评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 服务ID(非必传参数,单个服务才传此值,获取所有服务日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"create_time:时间desc:描述reason:原因",
    }]
  }
}
```

### 需求管理 -- GET admin/developer/demand

- controller: ``app\admin\controller\DeveloperController::getDemand``
- desc: 需求管理 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "demand":[{//需求
    }]
  }
}
```

### 需求内页 -- GET admin/developer/demanddetail

- controller: ``app\admin\controller\DeveloperController::getDemandDetail``
- desc: 需求内页 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求评论 -- GET admin/developer/demandevaluation

- controller: ``app\admin\controller\DeveloperController::getDemandEvaluation``
- desc: 需求评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 需求id,查看具体需求的评论用 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求修改 -- PUT admin/developer/demand

- controller: ``app\admin\controller\DeveloperController::putDemand``
- desc: 需求修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 需求id |
| name | 字符串 | 必填 | - | - | 标题 |
| desc | 字符串 | 必填 | - | - | 详情 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求删除 -- DELETE admin/developer/demand

- controller: ``app\admin\controller\DeveloperController::deleteDemand``
- desc: 需求删除 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 需求id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求采纳 -- POST admin/developer/demandadopt

- controller: ``app\admin\controller\DeveloperController::postDemandAdopt``
- desc: 需求采纳 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 需求id |
| content | 字符串 | 必填 | - | - | 评论 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求取消采纳 -- DELETE admin/developer/demandadopt

- controller: ``app\admin\controller\DeveloperController::deleteDemandAdopt``
- desc: 需求取消采纳 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 需求id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求评论修改 -- PUT admin/developer/demandevaluation

- controller: ``app\admin\controller\DeveloperController::putDemandEvaluation``
- desc: 需求评论修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 评论id |
| content | 字符串 | 必填 | - | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求评论删除 -- DELETE admin/developer/demandevaluation

- controller: ``app\admin\controller\DeveloperController::deleteDemandEvaluation``
- desc: 需求评论删除 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 评论id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 悬赏开发 -- GET admin/developer/developerrewardlist

- controller: ``app\admin\controller\DeveloperController::getDeveloperRewardList``
- desc: 悬赏开发 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//悬赏信息
      "id":"悬赏IDname:名称total_amount:总金额reward_amount:官方悬赏total_price:购买reward_total_price:打赏withdraw:已提取status:状态status_zh:状态信息",
    }]
    "currency":"货币信息",
  }
}
```

### 悬赏详情 -- GET admin/developer/developerrewarddetail

- controller: ``app\admin\controller\DeveloperController::getDeveloperRewardDetail``
- desc: 悬赏详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":"悬赏信息",
    "product_type":"服务类型--所有",
    "currency":"货币",
  }
}
```

### 发布(修改)悬赏 -- POST admin/developer/developerreward

- controller: ``app\admin\controller\DeveloperController::postDeveloperReward``
- desc: 发布(修改)悬赏 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 悬赏ID(非必传,编辑时传) |
| name | 字符串 | 必填 | 1 | - | 需求标题 |
| uuid | 字符串 | 必填 | 1 | - | 应用标识 |
| desc | 字符串 | 非必填 | 1 | - | 需求详情 |
| type | 字符串 | 必填 | 1 | - | 应用类型 |
| version[] | 字符串 | 必填 | 1 | - | 应用版本:多选（数组）注意：选择所有all时,传version |
| pay_type | 字符串 | 非必填 | 1 | - | 出售方式 |
| currency[货币ID][周期(onetime,monthly,annually)] | 字符串 | 非必填 | 1 | - | 价格 |
| app_type | 字符串 | 非必填 | 1 | - | 应用所属模块:addons插件，gateways支付接口，servers模块,systems官方应用 |
| version_description | 字符串 | 非必填 | 1 | - | 版本描述 |
| app_version | 字符串 | 必填 | 1 | - | 应用版本 |
| professional_discount | 整型 | 必填 | 1 | - | 专业版折扣 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除悬赏 -- DELETE /admin/developer/developerreward

- controller: ``app\admin\controller\DeveloperController::deleteDeveloperReward``
- desc: 删除悬赏 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 悬赏ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 悬赏打款 -- POST admin/developer/developerrewardpayment

- controller: ``app\admin\controller\DeveloperController::postDeveloperRewardPayment``
- desc: 悬赏打款 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 悬赏ID |
| amount | 字符串 | 必填 | - | - | 金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 悬赏指定开发者 -- POST admin/developer/developerrewarddesignated

- controller: ``app\admin\controller\DeveloperController::postDeveloperRewardDesignated``
- desc: 悬赏指定开发者 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 悬赏ID |
| uid | 整型 | 必填 | - | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 审核通过/驳回悬赏 -- POST /admin/developer/checkdeveloperreward

- controller: ``app\admin\controller\DeveloperController::postCheckDeveloperReward``
- desc: 审核通过/驳回悬赏 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 悬赏ID |
| status | 整型 | 必填 | 1 | - | 3审核通过,1驳回 |
| reason | 字符串 | 必填 | 1 | - | 驳回原因：非必传参数,当驳回时才传此参数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 调整悬赏进度 -- PUT /admin/developer/developerreward

- controller: ``app\admin\controller\DeveloperController::putDeveloperReward``
- desc: 调整悬赏进度 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 悬赏ID |
| status | 整型 | 必填 | 1 | - | 0悬赏中,1开发中,3保证期,4已完成 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 显示/隐藏悬赏 -- POST /admin/developer/hiddendeveloperreward

- controller: ``app\admin\controller\DeveloperController::postHiddenDeveloperReward``
- desc: 显示/隐藏悬赏 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 悬赏ID |
| hidden | 整型 | 必填 | 1 | - | 0显示,1隐藏 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户搜索 -- GET admin/developer/usersearch

- controller: ``app\admin\controller\DeveloperController::getUserSearch``
- desc: 用户搜索 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 必填 | 1 | - | 搜索关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "user":[{//用户
      "id":"用户IDusername:名称",
    }]
  }
}
```

### 用户管理 -- GET admin/developer/user

- controller: ``app\admin\controller\DeveloperController::getUser``
- desc: 用户管理 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "user":[{//用户
      "uid":"用户IDusername:名称medal:勋章auth:权限",
    }]
    "auth":"权限信息",
    "section":"版块信息",
    "medal":"勋章",
  }
}
```

### 添加管理员 -- POST admin/developer/user

- controller: ``app\admin\controller\DeveloperController::postUser``
- desc: 添加管理员 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户ID |
| auth | 数组 | 非必填 | 1 | - | 版块和权限组成的二维数组 |
| medal | 字符串 | 必填 | 10 | - | 勋章 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改管理员 -- PUT admin/developer/user

- controller: ``app\admin\controller\DeveloperController::putUser``
- desc: 修改管理员 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户ID |
| auth | 数组 | 非必填 | 1 | - | 版块和权限组成的二维数组 |
| medal | 字符串 | 必填 | 10 | - | 勋章 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除管理员 -- DELETE admin/developer/user

- controller: ``app\admin\controller\DeveloperController::deleteUser``
- desc: 删除管理员 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 管理员操作日志 -- GET admin/developer/userlog

- controller: ``app\admin\controller\DeveloperController::getUserLog``
- desc: 管理员操作日志 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "log":[{//日志
      "id":"日志IDuid:用户IDusername:名称desc:描述ip:IPcreate_time:时间",
    }]
  }
}
```

### 通知管理 -- GET admin/developer/noticesetting

- controller: ``app\admin\controller\DeveloperController::getNoticeSetting``
- desc: 通知管理 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "message":[{//通知对应模板
      "id":"通知IDtype:类型email_temp_id:邮件模板ID,0为不启用sms_temp_id:短信模板ID,0为不启用",
    }]
  }
}
```

### 通知管理设置 -- POST admin/developer/noticesetting

- controller: ``app\admin\controller\DeveloperController::postNoticeSetting``
- desc: 通知管理设置 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| help_choose_reply | 数组 | 必填 | 1 | - | 互助回答被采纳 |
| help_reward | 数组 | 必填 | 1 | - | 互助帖子被打赏 |
| knowledge_buy | 数组 | 必填 | 1 | - | 知识被购买 |
| bug_confirm | 数组 | 必填 | 1 | - | BUG被确认 |
| bug_fix | 数组 | 必填 | 1 | - | BUG被修复 |
| demand_sign_up | 数组 | 必填 | 1 | - | 需求有人报名 |
| demand_submit_product | 数组 | 必填 | 1 | - | 需求有人提交作品 |
| demand_choose_developer | 数组 | 必填 | 1 | - | 开发者被选中 |
| posts_reply | 数组 | 必填 | 1 | - | 帖子有新回复 |
| evaluation_reply | 数组 | 必填 | 1 | - | 评论被回复 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 资源池管理

### 用户列表 -- GET /admin/resource/clientslist

- controller: ``app\res\controller\ResourceAdminController::getClientsList``
- desc: 用户列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| status[] | 数组 | 非必填 | 1 | - | 传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订, |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "clients":[{//供应商列表
      "name":"姓名phone:联系电话email:电子邮箱company:公司名称supplier_status:供应商状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "supplier_status_zh":"供应商中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "agent_status":"代理商状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "agent_status_zh":"代理商中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "security_deposit":"",
      "entry_fee":"",
    }]
  }
}
```

### 获取用户详情 -- GET /admin/resource/clients

- controller: ``app\res\controller\ResourceAdminController::getClients``
- desc: 获取用户详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "clients":[{//供应商
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱api_key:API密钥api_open:API开启状态：0关闭,1开通,2锁定api_create_time:API开通时间lock_reason:API锁定原因api_lock_time:API锁定时间supplier_status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "supplier_status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "supplier_cancelled_reason":"",
      "agent_status":"状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "agent_status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "agent_cancelled_reason":"",
    }]
    "invoice_items":[{//缴费信息
      "invoice_id":"账单号type:类型：security_deposit保证金,entry_fee入驻费amount:金额status:支付状态paid_time:支付时间payment:支付方式trans_id:流水号notes:备注",
    }]
  }
}
```

### 修改代理商用户等级 -- PUT admin/resource/agentusergrade

- controller: ``app\res\controller\ResourceAdminController::putAgentUserGrade``
- desc: 修改代理商用户等级 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 代理商用户ID |
| grade_id | 整型 | 非必填 | 1 | - | 用户等级ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 审核列表 -- GET /admin/resource/auditrecords

- controller: ``app\res\controller\ResourceAdminController::getAuditRecords``
- desc: 审核列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| status[] | 数组 | 非必填 | 1 | - | 传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];状态:Pending审核中，Active通过，Cancelled未通过，Wait合同待签订, |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "records":[{//审核列表
      "object":"角色type:类型name:姓名phone:联系电话email:电子邮箱company:公司名称status:状态：Pending审核中，Active通过，Cancelled未通过，Wait合同待签订,",
      "status_zh":"中文状态：Pending审核中，Active通过，Cancelled未通过，Wait合同待签订,",
      "cancelled_reason":"",
      "security_deposit":"",
      "entry_fee":"",
      "amount":"",
    }]
  }
}
```

### 资源池审核 -- POST /admin/resource/checkrecords

- controller: ``app\res\controller\ResourceAdminController::postCheckRecords``
- desc: 资源池审核 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 数组 | 必填 | 1 | - | 审核记录 |
| status | 数组 | 必填 | 1 | - | Active通过，Cancelled未通过，Wait合同待签订，Unpaid未付款 |
| reason | 数组 | 非必填 | 1 | - | 未通过原因(非必传参数),Cancelled必传必填 |
| entry_fee | 数组 | 非必填 | 1 | - | 入驻费 |
| security_deposit | 数组 | 非必填 | 1 | - | 保证金 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 供应商列表 -- GET /admin/resource/supplierlist

- controller: ``app\res\controller\ResourceAdminController::getSupplierList``
- desc: 供应商列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| status[] | 数组 | 非必填 | 1 | - | 传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订, |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "supplier":[{//供应商列表
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "cancelled_reason":"",
      "security_deposit":"",
      "entry_fee":"",
    }]
  }
}
```

### 获取供应商详情 -- GET /admin/resource/supplier

- controller: ``app\res\controller\ResourceAdminController::getSupplier``
- desc: 获取供应商详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "supplier":[{//供应商
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱api_key:API密钥api_open:API开启状态：0关闭,1开通,2锁定api_create_time:API开通时间lock_reason:API锁定原因api_lock_time:API锁定时间status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "cancelled_reason":"",
    }]
    "invoice_items":[{//缴费信息
      "invoice_id":"账单号type:类型：security_deposit保证金,entry_fee入驻费amount:金额status:支付状态paid_time:支付时间payment:支付方式trans_id:流水号notes:备注",
    }]
  }
}
```

### 审核供应商 -- POST /admin/resource/checksupplier

- controller: ``app\res\controller\ResourceAdminController::postCheckSupplier``
- desc: 审核供应商 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 供应商 |
| status | 字符串 | 必填 | 1 | - | Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订, |
| cancelled_reason | 字符串 | 非必填 | 1 | - | 未通过原因(非必传参数),Cancelled必传必填 |
| suspended_reason | 字符串 | 非必填 | 1 | - | 停用原因(非必传参数) |
| entry_fee | 字符串 | 非必填 | 1 | - | 入驻费 |
| security_deposit | 字符串 | 非必填 | 1 | - | 保证金 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 代理商列表 -- GET /admin/resource/agentlist

- controller: ``app\res\controller\ResourceAdminController::getAgentList``
- desc: 代理商列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| status[] | 数组 | 非必填 | 1 | - | 传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "agent":[{//代理商列表
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订status_zh:中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订suspended_reason：",
      "cancelled_reason":"",
    }]
  }
}
```

### 获取代理商详情 -- GET /admin/resource/agent

- controller: ``app\res\controller\ResourceAdminController::getAgent``
- desc: 获取代理商详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "agent":[{//代理商
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱api_key:API密钥api_open:API开启状态：0关闭,1开通,2锁定api_create_time:API开通时间lock_reason:API锁定原因api_lock_time:API锁定时间status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订status_zh:中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订suspended_reason：",
      "cancelled_reason":"",
    }]
  }
}
```

### 审核代理商 -- POST /admin/resource/checkagent

- controller: ``app\res\controller\ResourceAdminController::postCheckAgent``
- desc: 审核代理商 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 代理商 |
| status | 字符串 | 必填 | 1 | - | Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订 |
| cancelled_reason | 字符串 | 非必填 | 1 | - | 未通过原因(非必传参数),Cancelled必传必填 |
| suspended_reason | 字符串 | 非必填 | 1 | - | 停用原因(非必传参数) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置秘钥 -- POST /admin/resource/resetapipwd

- controller: ``app\res\controller\ResourceAdminController::postResetApiPwd``
- desc: 重置秘钥 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### api锁定 -- POST /admin/resource/apitoggle

- controller: ``app\res\controller\ResourceAdminController::postApiToggle``
- desc: api锁定 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户ID |
| api_open | 整型 | 必填 | - | - | 1开启,0关闭,2锁定 |
| lock_reason | 字符串 | 必填 | - | - | 锁定原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 追加入驻费/保证金 -- POST /admin/resource/securitydeposit

- controller: ``app\res\controller\ResourceAdminController::postSecurityDeposit``
- desc: 追加入驻费/保证金 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户ID |
| type | 字符串 | 必填 | - | - | security_deposit保证金entry_fee入驻费 |
| amount | 浮点型 | 必填 | - | - | 追加保证金金额 |
| remarks | 字符串 | 必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 账单列表 -- GET /admin/resource/invoicelist

- controller: ``app\res\controller\ResourceAdminController::getInvoiceList``
- desc: 账单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段['id','uid','name','paid_time','status','phone'], |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "invoices":[{//账单列表
      "id":"账单号name:姓名type:类型：security_deposit保证金，entry_fee入驻费phone:联系电话email:电子邮箱total:金额paid_time:支付时间payment:支付方式status:状态notes:备注",
    }]
  }
}
```

### 启用/停用用户 -- POST /admin/resource/changeclients

- controller: ``app\res\controller\ResourceAdminController::postChangeClients``
- desc: 启用/停用用户 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 用户 |
| status | 字符串 | 必填 | 1 | - | Active启用，Suspended停用 |
| suspended_reason | 字符串 | 非必填 | 1 | - | 停用原因(非必传参数) |
| 对象 | 数组 | 非必填 | 1 | - | 操作对象supplier供应商agent代理商 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商品列表 -- GET /admin/resource/productslist

- controller: ``app\res\controller\ResourceAdminController::getProductsList``
- desc: 商品列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| status[] | 数组 | 非必填 | 1 | - | 传数组,全部['Active','Suspended'],停用['Suspended'],启用['Active'];状态:Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订, |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//商品列表
      "id":"商品IDuid:商家IDname:商品名称supplier:供应商company:供应商公司type:商品类型type_zh:商品中文类型status:商品状态status_zh:商品中文状态upstream_qty:",
      "stock_control":"",
      "qty":"",
      "pricing":"",
      "currency":"",
      "unretired_time":"",
      "create_time":"",
      "update_time":"",
    }]
  }
}
```

### 商品内页 -- GET /admin/resource/products

- controller: ``app\res\controller\ResourceAdminController::getProducts``
- desc: 商品内页 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "products":[{//商品列表
      "id":"商品IDuid:商家IDname:商品名称supplier:供应商company:供应商公司type:商品类型type_zh:商品中文类型status:商品状态status_zh:商品中文状态upstream_qty:",
      "stock_control":"",
      "qty":"",
      "pricing":"",
      "currency":"",
      "unretired_time":"",
      "create_time":"",
      "update_time":"",
    }]
  }
}
```

### 上/下架商品 -- POST /admin/resource/toggleretired

- controller: ``app\res\controller\ResourceAdminController::postToggleRetired``
- desc: 上/下架商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |
| retired | 整型 | 必填 | 1 | - | 0上架,1下架 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除商品 -- DELETE /admin/resource/products

- controller: ``app\res\controller\ResourceAdminController::deleteProducts``
- desc: 删除商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 搜索页面 -- GET admin/resource/ordersearchpage

- controller: ``app\res\controller\ResourceAdminController::getOrderSearchPage``
- desc: 搜索页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 可选参数,用户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订单列表 -- GET admin/resource/orderlist

- controller: ``app\res\controller\ResourceAdminController::getOrderList``
- desc: 订单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |
| sale_id | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "pay_status":"付款状态",
      "status":"状态",
    }]
  }
}
```

### 续费订单列表 -- GET admin/resource/renew

- controller: ``app\res\controller\ResourceAdminController::getRenew``
- desc: 续费订单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| sale_id | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "status":"状态",
    }]
  }
}
```

### 业务列表 -- GET /admin/resource/hostlist

- controller: ``app\res\controller\ResourceAdminController::getHostList``
- desc: 业务列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | number | 非必填 | 1 | - | 页码 |
| pagecount | number | 非必填 | - | - | 每页显示条数 |
| order | 字符串 | 非必填 | id | - | 排序字段(id,uid,productid,billingcycle,payment,nextduedate,dedicatedip,username,productname) |
| sort | 字符串 | 非必填 | ASC | - | 排序方式 |
| product_type | 字符串 | 非必填 | - | - | 产品类型(搜索字段) |
| uid | 整型 | 非必填 | - | - | 用户名(搜索字段) |
| name | 字符串 | 非必填 | - | - | 产品名(搜索字段) |
| server | number | 非必填 | - | - | 服务器id(搜索字段) |
| product | number | 非必填 | - | - | 产品id(搜索字段) |
| payment | 字符串 | 非必填 | - | - | 支付方式(搜索字段) |
| billingcycle | 字符串 | 非必填 | - | - | 付款周期(搜索字段) |
| domainstatus | 字符串 | 非必填 | - | - | 主机状态(搜索字段) |
| domain | 字符串 | 非必填 | - | - | 主机名(搜索字段) |
| ip | 字符串 | 非必填 | - | - | ip(搜索字段) |
| nextduedate | 整型 | 非必填 | - | - | 到期时间 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "base":[{//基础数据(搜索区)
      "billingcycle":"周期",
      "gateway_list":"支付方式",
      "product_list":[{//产品列表
        "id":"分组id",
        "groupname":"分组名称",
        "clild":[{//产品数组
          "id":"产品id",
          "productname":"产品名称",
        }]
      }]
      "product_type":"产品类型",
      "server_list":"服务器列表",
      "domainstatus":"服务器状态",
    }]
    "list":[{//数据列表
      "id":"主机id",
      "dedicatedip":"独立ip",
      "billingcycle":"周期",
      "dedicatedip":"主ip地址",
      "assignedips":"附加ip地址",
      "nextduedate":"到期时间",
      "payment":"付款方式",
      "productid":"产品id",
      "productname":"产品名",
      "productname":"状态'Pending','Active','Suspended','Terminated','Cancelled','Fraud','Completed'",
      "uid":"用户id",
      "amount":"价格",
      "regdate":"开通时间",
      "dedicatedip":"ip地址",
      "type":"产品类型(shared",
      "username":"用户名",
      "sale_name":"显示销售",
    }]
    "pagination":[{//分页相关数据
      "count":"总数量",
      "total_page":"总页码",
      "pagecount":"每页数量",
      "page":"当前页码",
      "orderby":"排序字段",
      "sorting":"排序方式",
    }]
    "search":[{//搜索参数
      "billingcycle":"周期",
      "domainstatus":"主机状态",
      "payment":"支付方式",
      "product":"产品id",
      "product_type":"产品类型",
      "server":"服务器id",
    }]
  }
}
```

### 资源池日志 -- GET admin/resource/logs

- controller: ``app\res\controller\ResourceAdminController::getLogs``
- desc: 资源池日志 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 关联商品ID(非必传参数,单个商品才传此值,获取所有商品日志时不传) |
| uid | 整型 | 非必填 | 1 | - | 关联用户ID(非必传参数,单个用户才传此值,获取所有用户日志时不传) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"idcreate_time:时间desc:描述ip:ipreferer:来源active_name:操作人user_type_cn:操作人角色",
    }]
  }
}
```

### 资源池设置页面 -- GET admin/resource/config

- controller: ``app\res\controller\ResourceAdminController::getConfig``
- desc: 资源池设置页面 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".shd_resource":"资源池,1开启,0关闭",
    ".resource_web_type":"资源池类型,0国内1国际",
    ".shd_resource_auto_sign_for":"自动收货",
    ".shd_resource_refund":"退款期限",
    ".shd_resource_auto_refund":"自动退款",
    ".shd_resource_charge":"提现手续费",
    ".shd_resource_auto_evaluate":"系统自动评价",
    ".shd_resource_handling_model":"手续费模式收取手续费",
  }
}
```

### 资源池设置页面提交 -- POST /admin/resource/config

- controller: ``app\res\controller\ResourceAdminController::postConfig``
- desc: 资源池设置页面提交 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_resource | 整型 | 必填 | 1 | - | 资源池,1开启,0关闭 |
| resource_web_type | 整型 | 必填 | 1 | - | 资源池类型,0国内1国际 |
| shd_resource_auto_sign_for | 整型 | 必填 | 1 | - | 自动收货 |
| shd_resource_refund | 整型 | 必填 | 1 | - | 退款期限 |
| shd_resource_auto_refund | 整型 | 必填 | 1 | - | 自动退款 |
| shd_resource_charge | 浮点型 | 必填 | 1 | - | 提现手续费 |
| shd_resource_auto_evaluate | 整型 | 必填 | 1 | - | 系统自动评价 |
| shd_resource_handling_model | 整型 | 必填 | 1 | - | 手续费模式收取手续费 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户等级列表 -- GET /admin/resource/usergradeList

- controller: ``app\res\controller\ResourceAdminController::getUserGradeList``
- desc: 用户等级列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "grade":[{//用户等级列表
      "name":"名称discount:联系电话direct_order:直接订购",
      "direct_order_amount":"直接订购金额pre_storage:预存",
      "pre_storage_amount":"预存金额monthly_consumption:月消费额",
      "monthly_consumption_amount":"月消费额total_consumption:消费总额",
      "total_consumption_amount":"消费总额",
    }]
  }
}
```

### 用户等级添加 -- POST /admin/resource/usergrade

- controller: ``app\res\controller\ResourceAdminController::postUserGrade``
- desc: 用户等级添加 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | 1 | - | 名称 |
| discount | 浮点型 | 必填 | 1 | - | 折扣百分比 |
| direct_order | 整型 | 必填 | 1 | - | 直接订购 |
| direct_order_amount | 浮点型 | 必填 | 1 | - | 直接订购金额 |
| pre_storage | 整型 | 必填 | 1 | - | 预存 |
| pre_storage_amount | 浮点型 | 必填 | 1 | - | 预存金额 |
| monthly_consumption | 整型 | 必填 | 1 | - | 月消费额 |
| monthly_consumption_amount | 浮点型 | 必填 | 1 | - | 月消费额 |
| total_consumption | 整型 | 必填 | 1 | - | 消费总额 |
| total_consumption_amount | 浮点型 | 必填 | 1 | - | 消费总额 |
| default | 整型 | 必填 | 0 | - | 是否注册默认等级 |
| grade | 整型 | 必填 | - | - | 等级 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改用户等级 -- PUT /admin/resource/usergrade

- controller: ``app\res\controller\ResourceAdminController::putUserGrade``
- desc: 修改用户等级 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | 1 | - | 名称 |
| discount | 浮点型 | 必填 | 1 | - | 折扣百分比 |
| direct_order | 整型 | 必填 | 1 | - | 直接订购 |
| direct_order_amount | 浮点型 | 必填 | 1 | - | 直接订购金额 |
| pre_storage | 整型 | 必填 | 1 | - | 预存 |
| pre_storage_amount | 浮点型 | 必填 | 1 | - | 预存金额 |
| monthly_consumption | 整型 | 必填 | 1 | - | 月消费额 |
| monthly_consumption_amount | 浮点型 | 必填 | 1 | - | 月消费额 |
| total_consumption | 整型 | 必填 | 1 | - | 消费总额 |
| total_consumption_amount | 浮点型 | 必填 | 1 | - | 消费总额 |
| default | 整型 | 必填 | 0 | - | 是否注册默认等级 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除用户等级 -- DELETE /admin/resource/usergrade

- controller: ``app\res\controller\ResourceAdminController::deleteUserGrade``
- desc: 删除用户等级 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商城热门位 -- GET /admin/resource/shophot

- controller: ``app\res\controller\ResourceAdminController::getShopHot``
- desc: 商城热门位 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "type":[{//用户等级列表
      "name":"名称discount:联系电话direct_order:直接订购",
      "direct_order_amount":"直接订购金额pre_storage:预存",
      "pre_storage_amount":"预存金额monthly_consumption:月消费额",
      "monthly_consumption_amount":"月消费额total_consumption:消费总额",
      "total_consumption_amount":"消费总额",
    }]
  }
}
```

### 新增资源池分类 -- POST /admin/resource/shophottype

- controller: ``app\res\controller\ResourceAdminController::postShopHotType``
- desc: 新增资源池分类 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| productid | 数组 | 必填 | - | - | 商品 |
| remarks | 字符串 | 必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池分类新增商品 -- POST /admin/resource/shophotproduct

- controller: ``app\res\controller\ResourceAdminController::postShopHotProduct``
- desc: 资源池分类新增商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 类型 |
| productid | 数组 | 必填 | - | - | 商品 |
| remarks | 字符串 | 必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除资源池分类 -- DELETE /admin/resource/shophottype

- controller: ``app\res\controller\ResourceAdminController::deleteShopHotType``
- desc: 删除资源池分类 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分类ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除资源池分类商品 -- DELETE /admin/resource/shophotproduct

- controller: ``app\res\controller\ResourceAdminController::deleteShopHotProduct``
- desc: 删除资源池分类商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品排序修改 -- PUT /admin/resource/shophotproductsort

- controller: ``app\res\controller\ResourceAdminController::putShopHotProductSort``
- desc: 产品排序修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | - | - | 产品ID |
| gid | 整型 | 非必填 | - | - | 分类ID |
| pre_pid | 整型 | 非必填 | - | - | 移动后前一个产品ID |
| current_gid | 整型 | 非必填 | - | - | 当前分类ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池banner列表 -- GET /admin/resource/resbannerlist

- controller: ``app\res\controller\ResourceAdminController::getResBannerList``
- desc: 资源池banner列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "shd_res_shop_banner":"店铺banner,1开启,0关闭",
    "res_banner":[{//资源池banner
      "id":"资源池banner",
      "IDname":"名称desc:描述banner:banner图url:跳转地址start_time:开始时间end_time:结束时间status:状态",
    }]
  }
}
```

### 开启/关闭店铺banner -- PUT /admin/resource/shopbanner

- controller: ``app\res\controller\ResourceAdminController::putShopBanner``
- desc: 开启/关闭店铺banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_res_shop_banner | 整型 | 必填 | 1 | - | 店铺banner,1开启,0关闭 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 新增资源池banner -- POST /admin/resource/resbanner

- controller: ``app\res\controller\ResourceAdminController::postResBanner``
- desc: 新增资源池banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 名称 |
| desc | 字符串 | 必填 | - | - | 描述 |
| banner | 字符串 | 必填 | - | - | banner图 |
| url | 字符串 | 必填 | - | - | banner跳转地址 |
| start_time | 整型 | 必填 | - | - | 活动开始时间 |
| end_time | 整型 | 必填 | - | - | 活动结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改资源池banner -- PUT /admin/resource/resbanner

- controller: ``app\res\controller\ResourceAdminController::putResBanner``
- desc: 修改资源池banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | bannerID |
| name | 字符串 | 必填 | - | - | 名称 |
| desc | 字符串 | 必填 | - | - | 描述 |
| banner | 字符串 | 必填 | - | - | banner图 |
| url | 字符串 | 必填 | - | - | banner跳转地址 |
| start_time | 整型 | 必填 | - | - | 活动开始时间 |
| end_time | 整型 | 必填 | - | - | 活动结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除资源池banner -- DELETE /admin/resource/resbanner

- controller: ``app\res\controller\ResourceAdminController::deleteResBanner``
- desc: 删除资源池banner -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | bannerID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商家等级列表 -- GET /admin/resource/businessgradeList

- controller: ``app\res\controller\ResourceAdminController::getBusinessGradeList``
- desc: 商家等级列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "grade":[{//商家等级列表
      "name":"名称level:等级advanced_value:进阶值",
    }]
  }
}
```

### 商家等级添加 -- POST /admin/resource/businessgrade

- controller: ``app\res\controller\ResourceAdminController::postBusinessGrade``
- desc: 商家等级添加 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | 1 | - | 名称 |
| level | 整型 | 必填 | 1 | - | 阶梯 |
| advanced_value | 整型 | 必填 | 1 | - | 进阶值 |
| default | 整型 | 必填 | 0 | - | 是否注册默认等级 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改商家等级 -- PUT /admin/resource/businessgrade

- controller: ``app\res\controller\ResourceAdminController::putBusinessGrade``
- desc: 修改商家等级 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | 1 | - | 名称 |
| level | 整型 | 必填 | 1 | - | 阶梯 |
| advanced_value | 整型 | 必填 | 1 | - | 进阶值 |
| default | 整型 | 必填 | 0 | - | 是否注册默认等级 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除商家等级 -- DELETE /admin/resource/businessgrade

- controller: ``app\res\controller\ResourceAdminController::deleteBusinessGrade``
- desc: 删除商家等级 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 货款基础信息 -- GET /admin/resource/salesinfo

- controller: ``app\res\controller\ResourceAdminController::getSalesInfo``
- desc: 货款基础信息 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 供应商ID |
| type | 字符串 | 必填 | 1 | - | 数据类型this_month本月last_month上月all所有 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "sales_amount":"销售总额",
    "supplier_amount":"供应商成本",
    "profit_amount":"利润",
    "withdraw":"已提现",
    "refund":"已退款",
    "available_cash_amount":"可用现金额",
    "this_month_supplier_amount":"本月收入(用户内页货款)",
    "this_month_withdraw":"本月已提现",
    "this_month_profit_amount":"本月资源池利润",
    "supplier_amount":"总收入",
    "withdraw":"总提现",
    "profit_amount":"资源池利润",
    "resource_amount":"担保中",
    "guarantee_amount":"冻结余额",
    "withdrawing":"提现中",
    "refunding":"退款中",
    "refund":"已退款",
    "credit_amount":"可用余额",
    "currency":"货币单位",
  }
}
```

### 交易记录 -- GET /admin/resource/accounts

- controller: ``app\res\controller\ResourceAdminController::getAccounts``
- desc: 交易记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 工单 -- GET /admin/resource/tickets

- controller: ``app\res\controller\ResourceAdminController::getTickets``
- desc: 工单 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 协查审核 -- GET /admin/resource/inspections

- controller: ``app\res\controller\ResourceAdminController::getInspections``
- desc: 协查审核 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 协查详情 -- GET /admin/resource/inspection

- controller: ``app\res\controller\ResourceAdminController::getInspection``
- desc: 协查详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 协查审核通过/驳回 -- PUT /admin/resource/inspection

- controller: ``app\res\controller\ResourceAdminController::putInspection``
- desc: 协查审核通过/驳回 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 协查ID |
| status | 字符串 | 必填 | 1 | - | Active审核通过,Cancelled驳回 |
| reason | 字符串 | 必填 | 1 | - | 驳回原因：非必传参数,当驳回时才传此参数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 退款 -- GET /admin/resource/refunds

- controller: ``app\res\controller\ResourceAdminController::getRefunds``
- desc: 退款 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 退款详情 -- GET /admin/resource/refund

- controller: ``app\res\controller\ResourceAdminController::getRefund``
- desc: 退款详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 退款ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 售后 -- GET /admin/resource/aftersales

- controller: ``app\res\controller\ResourceAdminController::getAfterSales``
- desc: 售后 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 售后详情 -- GET /admin/resource/aftersale

- controller: ``app\res\controller\ResourceAdminController::getAfterSale``
- desc: 售后详情 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 售后ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 售后处理 -- PUT /admin/resource/aftersale

- controller: ``app\res\controller\ResourceAdminController::putAfterSale``
- desc: 售后处理 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 售后ID |
| supplier_reply | 字符串 | 必填 | 1 | - | 给供应商回复 |
| supplier_reply_img | 数组 | 必填 | 1 | - | 给供应商回复图片 |
| agent_reply | 字符串 | 必填 | 1 | - | 给代理商回复 |
| agent_reply_img | 数组 | 必填 | 1 | - | 给代理商回复图片 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 魔力值 -- GET /admin/resource/mana

- controller: ``app\res\controller\ResourceAdminController::getMana``
- desc: 魔力值 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 供应商ID |
| shop_id | 整型 | 必填 | 1 | - | 商店ID |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 创建魔力值记录 -- POST /admin/resource/mana

- controller: ``app\res\controller\ResourceAdminController::postMana``
- desc: 创建魔力值记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 供应商ID |
| shop_id | 整型 | 必填 | 1 | - | 商店ID |
| type | 整型 | 必填 | 1 | - | 0奖励1惩罚 |
| integral | 整型 | 必填 | 10 | - | 积分 |
| amount | 浮点型 | 必填 | 10 | - | 金额 |
| reason | 字符串 | 必填 | 10 | - | 原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 任务队列列表 -- GET /admin/resource/runmaplist

- controller: ``app\res\controller\ResourceAdminController::getRunMapList``
- desc: 任务队列列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字 |
| user | 字符串 | 非必填 | - | - | 用户名关键字 |
| from_type | 整型 | 非必填 | - | - | 来源类型 |
| status | 整型 | 非必填 | - | - | 状态 |
| active_type | 整型 | 非必填 | - | - | 来源类型 |
| status | 整型 | 非必填 | - | - | 执行状态 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序字段 |
| sorting | 字符串 | 非必填 | desc | - | desc/asc，倒叙/顺序 |
| uid | 整型 | 非必填 | 1 | - | 供应商UID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提现记录 -- GET /admin/resource/withdraw

- controller: ``app\res\controller\ResourceAdminController::getWithdraw``
- desc: 提现记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |
| status | 字符串 | 非必填 | 10 | - | Pending,Cancelled,Active |
| type | 字符串 | 非必填 | 10 | - | 收款方式bank,alipay |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "logs":"提现记录=>禅道列表顺序显示",
    "count":"数量",
  }
}
```

### 审核提现记录 -- POST /admin/resource/withdraw

- controller: ``app\res\controller\ResourceAdminController::postWithdraw``
- desc: 审核提现记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 提现记录ID |
| account | 字符串 | 必填 | 1 | - | 流水号 |
| status | 字符串 | 必填 | 1 | - | Active启用，Cancelled未通过 |
| cancelled_reason | 字符串 | 非必填 | 1 | - | 未通过原因(非必传参数),Cancelled必传必填 |
| remarks | 字符串 | 非必填 | 1 | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 类目管理 -- GET /admin/resource/category

- controller: ``app\res\controller\ResourceAdminController::getCategory``
- desc: 类目管理 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "categories":"类目",
  }
}
```

### 类目添加 -- POST /admin/resource/category

- controller: ``app\res\controller\ResourceAdminController::postCategory``
- desc: 类目添加 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 1 | - | 类型server服务器/魔方DCIM,cloud云服务器/魔方云 |
| name | 字符串 | 必填 | 1 | - | 名称 |
| fields | 数组 | 非必填 | 1 | - | 字段(name名称order排序) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 类目修改 -- PUT /admin/resource/category

- controller: ``app\res\controller\ResourceAdminController::putCategory``
- desc: 类目修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | ID |
| type | 字符串 | 必填 | 1 | - | 类型server服务器/魔方DCIM,cloud云服务器/魔方云 |
| name | 字符串 | 必填 | 1 | - | 名称 |
| fields | 字符串 | 非必填 | 1 | - | 字段 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 类目删除 -- DELETE /admin/resource/category

- controller: ``app\res\controller\ResourceAdminController::deleteCategory``
- desc: 类目删除 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上下架店铺 -- POST /admin/resource/shopretired

- controller: ``app\res\controller\ResourceAdminController::postShopRetired``
- desc: 上下架店铺 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | 1 | - | 供应商uid |
| retired | 整型 | 必填 | 1 | - | 1下架,0上架 |
| retired_reason | 字符串 | 非必填 | 1 | - | 下架原因,retired==1时,需要传此参数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 资源方中心(前台)

### 代理商直购升级列表 -- GET /resource/usergradeList

- controller: ``app\res\controller\ResourceHomeController::getUserGradeList``
- desc: 代理商直购升级列表 -- xiong

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "grade":[{//代理商直购升级列表
      "name":"名称direct_order_amount:直接订购金额pre_storage_amount:预存金额monthly_consumption_amount:月消费额total_consumption_amount:消费总额grade:代理商等级",
    }]
  }
}
```

### 代理商升级创建账单 -- POST /resource/usergradeInvoices

- controller: ``app\res\controller\ResourceHomeController::postUserGradeInvoices``
- desc: 代理商升级创建账单 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| user_grade_id | 整型 | 必填 | - | - | 等级ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_id":"账单ID",
  }
}
```

### 资源池入驻 页面 -- GET /resource/resource

- controller: ``app\res\controller\ResourceHomeController::getResource``
- desc: 资源池入驻 页面 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "certif":"是否实名认证：0 未认证,1 已认证",
    "phone":"是否手机绑定",
    "email":"是否邮箱绑定",
    "resource_web_type":"是否为国际版 0 国内版,1 国际版",
    "user":[{//客户信息
      "username":"客户名phonenumber:手机email:邮箱certify_type:认证类型certifi_company企业认证certify_person个人认证",
    }]
    "supplier":[{//供应商信息
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱contract:合同ID，状态为Wait合同待签订时需要status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "cancelled_reason":"",
    }]
    "agent":[{//代理商信息
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱contract:合同ID，状态为Wait合同待签订时需要status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "cancelled_reason":"",
    }]
  }
}
```

### 资源池入驻 -- POST /resource/resource

- controller: ``app\res\controller\ResourceHomeController::postResource``
- desc: 资源池入驻 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 必填 | - | - | 入驻类型1供应商2供应商和代理商3IDC公司代理商4个人代理商 |
| name | 字符串 | 必填 | - | - | 姓名 |
| phone | 字符串 | 必填 | - | - | 联系电话 |
| spare_phone | 字符串 | 必填 | - | - | 备用电话 |
| email | 字符串 | 必填 | - | - | 邮箱 |
| contact_email | 字符串 | 必填 | - | - | 备用邮箱 |
| address | 字符串 | 非必填 | - | - | 联系地址 |
| company | 字符串 | 必填 | 1 | - | 公司名称 |
| company_address | 字符串 | 必填 | 1 | - | 公司地址 |
| company_web | 字符串 | 必填 | 1 | - | 公司网站 |
| company_desc | 字符串 | 必填 | 1 | - | 公司简介 |
| main_business | 字符串 | 必填 | 1 | - | 主营业务 |
| business_license | 数组 | 必填 | 1 | - | 营业执照 |
| telecom_increment_license | 数组 | 必填 | 1 | - | 电信增值许可证 |
| attachment | 数组 | 必填 | 1 | - | 其他附件 |
| spare_contacts_name | 字符串 | 必填 | 1 | - | 备用联系人姓名 |
| spare_contacts_phone | 字符串 | 必填 | 1 | - | 备用联系人电话 |
| spare_contacts_email | 字符串 | 必填 | 1 | - | 备用联系人邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 供应商修改资料 -- PUT /resource/supplier

- controller: ``app\res\controller\ResourceHomeController::putSupplier``
- desc: 供应商修改资料 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 整型 | 必填 | - | - | 入驻类型1供应商2供应商和代理商3IDC公司代理商4个人代理商 |
| name | 字符串 | 必填 | - | - | 姓名 |
| phone | 字符串 | 必填 | - | - | 联系电话 |
| spare_phone | 字符串 | 必填 | - | - | 备用电话 |
| email | 字符串 | 必填 | - | - | 邮箱 |
| contact_email | 字符串 | 必填 | - | - | 备用邮箱 |
| address | 字符串 | 非必填 | - | - | 联系地址 |
| company | 字符串 | 必填 | 1 | - | 公司名称 |
| company_address | 字符串 | 必填 | 1 | - | 公司地址 |
| company_web | 字符串 | 必填 | 1 | - | 公司网站 |
| company_desc | 字符串 | 必填 | 1 | - | 公司简介 |
| main_business | 字符串 | 必填 | 1 | - | 主营业务 |
| business_license | 数组 | 必填 | 1 | - | 营业执照 |
| telecom_increment_license | 数组 | 必填 | 1 | - | 电信增值许可证 |
| attachment | 数组 | 必填 | 1 | - | 其他附件 |
| spare_contacts_name | 字符串 | 必填 | 1 | - | 备用联系人姓名 |
| spare_contacts_phone | 字符串 | 必填 | 1 | - | 备用联系人电话 |
| spare_contacts_email | 字符串 | 必填 | 1 | - | 备用联系人邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 代理商修改资料 -- PUT /resource/agent

- controller: ``app\res\controller\ResourceHomeController::putAgent``
- desc: 代理商修改资料 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | - | - | 姓名 |
| phone | 字符串 | 必填 | - | - | 联系电话 |
| spare_phone | 字符串 | 必填 | - | - | 备用电话 |
| email | 字符串 | 必填 | - | - | 邮箱 |
| contact_email | 字符串 | 必填 | - | - | 备用邮箱 |
| address | 字符串 | 非必填 | - | - | 联系地址 |
| company | 字符串 | 必填 | 1 | - | 公司名称 |
| company_address | 字符串 | 必填 | 1 | - | 公司地址 |
| company_web | 字符串 | 必填 | 1 | - | 公司网站 |
| company_desc | 字符串 | 必填 | 1 | - | 公司简介 |
| main_business | 字符串 | 必填 | 1 | - | 主营业务 |
| business_license | 数组 | 必填 | 1 | - | 营业执照 |
| telecom_increment_license | 数组 | 必填 | 1 | - | 电信增值许可证 |
| attachment | 数组 | 必填 | 1 | - | 其他附件 |
| spare_contacts_name | 字符串 | 必填 | 1 | - | 备用联系人姓名 |
| spare_contacts_phone | 字符串 | 必填 | 1 | - | 备用联系人电话 |
| spare_contacts_email | 字符串 | 必填 | 1 | - | 备用联系人邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 重置秘钥 -- POST /resource/resetapipwd

- controller: ``app\res\controller\ResourceHomeController::postResetApiPwd``
- desc: 重置秘钥 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### api锁定 -- POST /resource/apitoggle

- controller: ``app\res\controller\ResourceHomeController::postApiToggle``
- desc: api锁定 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| api_open | 整型 | 必填 | - | - | 1开启,0关闭,2锁定 |
| lock_reason | 字符串 | 必填 | - | - | 锁定原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收款方式 页面 (编辑) -- GET admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getPayMethodPage

- controller: ``app\res\controller\ResourceHomeController::getPayMethodPage``
- desc: 收款方式 页面 (编辑) -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 收款方式ID(非必传,编辑时才传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".type":"提现类型",
    ".bank":"提现银行",
    "withdraw_method":"收款方式信息(编辑使用，bank时：account_bank，account_name，account_num，account_address；alipay时：username姓名，alipay支付宝账号)",
  }
}
```

### 添加收款方式 （含编辑） -- POST admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postPayMethod

- controller: ``app\res\controller\ResourceHomeController::postPayMethod``
- desc: 添加收款方式 （含编辑） -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 收款方式ID(非必传,编辑时才传) |
| type | 字符串 | 必填 | 1 | - | 收款方式,bank银行,alipay支付宝 |
| account_bank | 字符串 | 非必填 | 1 | - | 开户银行 |
| account_name | 字符串 | 非必填 | 1 | - | 开户名称 |
| account_num | 字符串 | 非必填 | 1 | - | 开户账号 |
| account_address | 字符串 | 非必填 | 1 | - | 开户网点 |
| username | 字符串 | 非必填 | 1 | - | 姓名(这两个个参数收款方式为alipay时传,传就是必填) |
| alipay | 字符串 | 非必填 | 1 | - | 支付宝账号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除收款方式 -- DELETE admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=deletePayMethod

- controller: ``app\res\controller\ResourceHomeController::deletePayMethod``
- desc: 删除收款方式 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 收款方式ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 设置默认收款方式 -- POST admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postDefaultMethod

- controller: ``app\res\controller\ResourceHomeController::postDefaultMethod``
- desc: 设置默认收款方式 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 收款方式ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提现 页面 -- GET admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getWithdrawPage

- controller: ``app\res\controller\ResourceHomeController::getWithdrawPage``
- desc: 提现 页面 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "withdraw_method":"提现方式信息",
    "withdraw_method_default":"默认方式ID",
    "total":"总资金",
    "frozen":"冻结资金",
  }
}
```

### 提现 -- POST admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postWithdraw

- controller: ``app\res\controller\ResourceHomeController::postWithdraw``
- desc: 提现 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 收款方式ID |
| amount | dcimal | 必填 | 1 | - | 金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提现记录 -- GET admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getWithdraw

- controller: ``app\res\controller\ResourceHomeController::getWithdraw``
- desc: 提现记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |
| status | 字符串 | 非必填 | 10 | - | Pending,Cancelled,Active |
| type | 字符串 | 非必填 | 10 | - | 收款方式bank,alipay |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "logs":"提现记录",
    "count":"数量",
  }
}
```

### 自动提现设置页面 -- GET admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getAutoWithdrawConfig

- controller: ``app\res\controller\ResourceHomeController::getAutoWithdrawConfig``
- desc: 自动提现设置页面 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".status":"自动提现,1开启,0关闭",
    ".withdraw_time":"提现时间,1-7对应周一到周日",
    ".withdraw_method":"提现方式ID",
  }
}
```

### 自动提现设置提交 -- POST admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postAutoWithdrawConfig

- controller: ``app\res\controller\ResourceHomeController::postAutoWithdrawConfig``
- desc: 自动提现设置提交 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| status | 整型 | 必填 | 1 | - | 自动提现,1开启,0关闭 |
| withdraw_time | 整型 | 必填 | 1 | - | 提现时间,1-7对应周一到周日 |
| withdraw_method | 整型 | 必填 | 1 | - | 提现方式ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取资源池登录信息(仅仅为了显示出接口文档) -- GET admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceInfo

- controller: ``app\res\controller\ResourceHomeController::getResourceInfo``
- desc: 获取资源池登录信息(仅仅为了显示出接口文档) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "users":[{//账号信息
      "username":"账号",
      "api_key":"密钥",
      "using":"使用中的账号。（为1时默认选择）",
    }]
  }
}
```

### 保存资源池登录信息 -- POST admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceInfo

- controller: ``app\res\controller\ResourceHomeController::postResourceInfo``
- desc: 保存资源池登录信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | 账号ID（编辑时传）注意这里可以添加新账号，当重新输入新账号时，为添加账号 |
| username | 字符串 | 必填 | 1 | - | 账号 |
| api_key | 字符串 | 必填 | 1 | - | 密码 |
| resource_username | 字符串 | 必填 | 1 | - | 资源池账号名称(当无ID，才弹出添加余额及账号名称的弹框) |
| credit | 字符串 | 必填 | 1 | - | 资源池账号默认余额(当无ID，才弹出添加余额及账号名称的弹框) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 测试链接资源池(同时 资源池尝试连接供应商 握手) -- POST admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=testResourceLink

- controller: ``app\res\controller\ResourceHomeController::testResourceLink``
- desc: 测试链接资源池(同时 资源池尝试连接供应商 握手) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 供应商任务队列列表 -- GET admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getRunMapLists

- controller: ``app\res\controller\ResourceHomeController::getSupplierRunMap``
- desc: 供应商任务队列列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字 |
| user | 字符串 | 非必填 | - | - | 用户名关键字 |
| from_type | 整型 | 非必填 | - | - | 来源类型 |
| status | 整型 | 非必填 | - | - | 状态 |
| active_type | 整型 | 非必填 | - | - | 来源类型 |
| status | 整型 | 非必填 | - | - | 执行状态 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| order | 字符串 | 非必填 | create_time | - | 排序字段 |
| sort | 字符串 | 非必填 | desc | - | desc/asc，倒叙/顺序 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取商品汇率 -- GET /resource/rate

- controller: ``app\res\controller\ResourceHomeController::getRate``
- desc: 获取商品汇率 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | - | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取所有产品 -- GET /resource/products

- controller: ``app\res\controller\ResourceHomeController::getProducts``
- desc: 获取所有产品 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".products":"所有在售产品",
    ".count":"产品数量",
  }
}
```

### 更新代理商信息 -- POST /resource/agentinfo

- controller: ``app\res\controller\ResourceHomeController::postAgentInfo``
- desc: 更新代理商信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hostname | 字符串 | 必填 | 1 | - | 代理商域名 |
| admin_url | 字符串 | 必填 | 1 | - | 代理商后台地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源API接口登录、验证(supplier供应商以及agent代理商) -- POST /resource_login

- controller: ``app\res\controller\ResourceHomeController::resourceLogin``
- desc: 资源API接口登录、验证(supplier供应商以及agent代理商) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| username | 字符串 | 必填 | 1 | - | 用户名(手机号+区号 |
| password | 字符串 | 必填 | 1 | - | 密码 |
| type | 字符串 | 必填 | 1 | - | supplier供应商以及agent代理商 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 工单传递 -- GET /resource/resourceticketopen(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceTicketOpen)

- controller: ``app\res\controller\ResourceHomeController::postResourceTicketOpen``
- desc: 工单传递 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ID | 整型 | 必填 | 1 | - | 账户ID |
| config[ticket_open] | 整型 | 必填 | 1 | - | 是否开启工单传递：1是 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 分类列表 -- GET /resource/firstgroups(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceProductsFirstGroupLists)

- controller: ``app\res\controller\ResourceHomeController::getFirstGroups``
- desc: 分类列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 必填 | - | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "fgs":[{//分类信息
      "id":"",
      "description":"分类描述",
      "count":"商品数量",
    }]
  }
}
```

### 分类编辑页面 -- GET /resource/firstgroup(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceProductsFirstGroup)

- controller: ``app\res\controller\ResourceHomeController::getFirstGroup``
- desc: 分类编辑页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分类ID(创建时不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "fg":[{//分类
      "id":"",
      "description":"描述",
      "inner_open":"是否开启内页：1开启，0否默认",
      "inner":"内页html",
      "products":[{//商品列表
        "id":"",
        "name":"名称",
      }]
    }]
  }
}
```

### 分类编辑/创建 -- POST /resource/firstgroup(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceProductsFirstGroup)

- controller: ``app\res\controller\ResourceHomeController::postFirstGroup``
- desc: 分类编辑/创建 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分类ID(编辑时传) |
| name | 整型 | 必填 | 1 | - | 分类名称 |
| description | 整型 | 必填 | 1 | - | 描述 |
| inner_open | 整型 | 必填 | 1 | - | 是否开启内页 |
| inner | 整型 | 必填 | 1 | - | 内页html |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 分类删除 -- GET /resource/firstgroupdelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=deleteResourceProductsFirstGroup)

- controller: ``app\res\controller\ResourceHomeController::getFirstGroupDelete``
- desc: 分类删除 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分类ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 分组编辑页面 -- GET /resource/group(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceProductsGroup)

- controller: ``app\res\controller\ResourceHomeController::getGroup``
- desc: 分组编辑页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分组ID(创建时不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "fgs":[{//分类列表
      "id":"name:名称",
    }]
    "group":[{//分组信息
      "id":"",
      "gid":"分类ID",
      "name":"组名称",
      "headline":"商品组标题",
      "tagline":"商品组标语",
      "hiddden":"1隐藏，0否",
      "tpl_type":"模板类型:default默认，custom自定义",
      "order_frm_tpl":"订购表格模板：选默认，传返回的default_page的值",
    }]
    "default_page":"系统默认",
    "cart_themes":"购物车模板",
  }
}
```

### 分组编辑 -- POST /resource/group(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceProductsGroup)

- controller: ``app\res\controller\ResourceHomeController::postGroup``
- desc: 分组编辑 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分组ID |
| gid | 整型 | 必填 | 1 | - | 分类ID |
| name | 整型 | 必填 | 1 | - | 商品组名称 |
| headline | 整型 | 必填 | 1 | - | 商品组标题 |
| tagline | 整型 | 必填 | 1 | - | 商品组标语 |
| hiddden | 整型 | 必填 | 1 | - | 1隐藏，0否 |
| tpl_type | 字符串 | 必填 | 0 | - | 模板类型:default默认，custom自定义 |
| order_frm_tpl | 字符串 | 必填 | - | - | 订购表格模板：选默认，传返回的default_page的值 |
| is_upstream | 字符串 | 非必填 | 0 | - | 是否上游资源 |
| zjfm_api_id | 字符串 | 非必填 | 0 | - | 接口id |
| type | 字符串 | 非必填 | 0 | - | 默认传1 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分组 -- GET /resource/groupdelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=deleteResourceProductsGroup)

- controller: ``app\res\controller\ResourceHomeController::getGroupDelete``
- desc: 删除分组 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分组ID(创建时不传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池商品列表(供应商调用接口) -- GET /resource/resourceproducts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceProductLists)

- controller: ``app\res\controller\ResourceHomeController::getResourceProducts``
- desc: 资源池商品列表(供应商调用接口) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| keywords | 字符串 | 非必填 | 1 | - | 按关键字搜索 |
| select_type | 字符串 | 非必填 | 1 | - | 筛选类型：type类型，status状态 |
| select_value | 整型 | 非必填 | - | - | 筛选值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "lists":[{//商品列表
      "id":"商品ID",
      "type_zh":"类型",
      "sale":"销量",
      "cancelled_reason":"驳回原因",
      "product_price":"jiage",
      "public":"是否公开",
      "status_zh":"状态",
      "res_retired":"0上架",
      "supplier_pid":"供应商对应商品ID(跳转商品内页用这个ID,并且跳转的是供应商本地的商品)",
    }]
  }
}
```

### 导入商品页面 -- GET /resource/createproducts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getCreateProducts)

- controller: ``app\res\controller\ResourceHomeController::getCreateProducts``
- desc: 导入商品页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 供应商 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "local_groups":[{//本地商品组商品
    }]
    "groups":[{//商品分类
      "id":"分类ID",
      "name":"名称",
      "child":[{//分组信息
        "id":"分组ID",
        "name":"分组名称",
        "count":"产品数量",
      }]
    }]
    "product":[{//商品信息
      "name":"商品名称",
      "type":"类型",
      "gid":"分组ID",
      "description":"商品描述",
      "country":"国家",
      "intro_open":"开启商品介绍：1开启，0关闭",
      "introduction":"商品介绍",
      "price_type":"价格模式:supplier供应价,handling手续费",
    }]
    "grades":[{//客户等级价格设置
      "name":"等级名称",
      "value":"百分比值",
    }]
  }
}
```

### 异步计算系统价格区间 -- GET /resource/productsprice(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getProductsPrice)

- controller: ``app\res\controller\ResourceHomeController::getProductsPrice``
- desc: 异步计算系统价格区间 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 供应商 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "min":"最小值",
    "max":"最大值",
  }
}
```

### 导入商品 -- POST /resource/createproducts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceProduct)

- controller: ``app\res\controller\ResourceHomeController::postCreateProducts``
- desc: 导入商品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 本地商品ID |
| name | 字符串 | 必填 | 1 | - | 商品名称 |
| type | 字符串 | 必填 | 1 | - | 商品类型 |
| fgid | 整型 | 必填 | 1 | - | 传分类ID |
| description | 字符串 | 必填 | 1 | - | 商品描述 |
| country | 字符串 | 必填 | 1 | - | 国家 |
| province | 字符串 | 必填 | 1 | - | 省 |
| city | 字符串 | 必填 | 1 | - | 市 |
| computer_room | 字符串 | 必填 | 1 | - | 机房 |
| price_type | 字符串 | 必填 | 1 | - | 价格模式:supplier供应价(默认模式),handling手续费 |
| percent_value | number | 必填 | 1 | - | 资源池价格百分比 |
| grades[客户等级1-10] | 数组 | 必填 | 1 | - | 手续费模式下,各个等级的价格 |
| intro_open | 整型 | 必填 | 1 | - | 是否开启商品介绍，1开启，0否默认 |
| introduction | 字符串 | 必填 | 1 | - | 商品介绍 |
| category_id | 整型 | 必填 | 1 | - | 类目ID |
| fieldsvalues | 数组 | 必填 | 1 | - | 类目字段 |
| product_price | 浮点型 | 必填 | 1 | - | 商品价格(手续费模式传此字段) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 编辑商品 -- POST /resource/editproducts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceProduct)

- controller: ``app\res\controller\ResourceHomeController::postEditProducts``
- desc: 编辑商品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 整型 | 必填 | 1 | - | 商品名称 |
| type | 整型 | 必填 | 1 | - | 商品类型 |
| gid | 整型 | 必填 | 1 | - | 分组ID |
| description | 整型 | 必填 | 1 | - | 商品描述 |
| country | 字符串 | 必填 | 1 | - | 国家 |
| province | 字符串 | 必填 | 1 | - | 省 |
| city | 字符串 | 必填 | 1 | - | 市 |
| computer_room | 整型 | 必填 | 1 | - | 机房 |
| percent_value | 整型 | 必填 | 1 | - | 资源池价格百分比 |
| grades[客户等级ID] | 整型 | 必填 | 1 | - | 等级百分比值 |
| intro_open | 整型 | 必填 | 1 | - | 是否开启商品介绍，1开启，0否默认 |
| introduction | 整型 | 必填 | 1 | - | 商品介绍 |
| category_id | 整型 | 必填 | 1 | - | 类目ID |
| fieldsvalues | 数组 | 必填 | 1 | - | 类目字段 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 是否公开 -- POST /resource/productspublic(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceProductPublic)

- controller: ``app\res\controller\ResourceHomeController::postProductsPublic``
- desc: 是否公开 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |
| public | tinyint | 必填 | 1 | - | 是否公开：1是，0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 上/下架 -- POST /resource/productsretired(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceProductRetired)

- controller: ``app\res\controller\ResourceHomeController::postProductsRetired``
- desc: 上/下架 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |
| retired | tinyint | 必填 | - | - | 是否下架：1下架， |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除商品 -- GET /resource/productsdelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=deleteResourceProduct)

- controller: ``app\res\controller\ResourceHomeController::getProductsDelete``
- desc: 删除商品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 商品ID |
| supplier_pid | 整型 | 必填 | 1 | - | 对应供应商商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池产品列表 -- GET /resource/resourcehosts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceHosts)

- controller: ``app\res\controller\ResourceHomeController::getResourceHosts``
- desc: 资源池产品列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| keywords | 字符串 | 非必填 | 1 | - | 按关键字搜索 |
| status | 字符串 | 非必填 | 1 | - | 状态搜索 |
| start_time | 整型 | 非必填 | 1 | - | 开始时间 |
| end_time | 整型 | 非必填 | 1 | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//订单信息
      "id":"",
      "name":"",
      "domain":"主机名",
      "dedicatedip":"IP",
      "assignedips":"分配IP",
      "uid":"买家",
      "order_status":"状态",
      "create_time":"订购时间",
      "amount":"金额",
      "billingcycle_zh":"周期",
      "ticket_total":"总工单数",
      "ticket_unclose":"未关闭工单数",
    }]
  }
}
```

### 资源池订单列表 -- GET /resource/resourceorders(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceOrders)

- controller: ``app\res\controller\ResourceHomeController::getResourceOrders``
- desc: 资源池订单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| keywords | 字符串 | 非必填 | 1 | - | 按关键字搜索 |
| status | 字符串 | 非必填 | 1 | - | 订单状态搜索 |
| product_type | 字符串 | 非必填 | 1 | - | 产品类型搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//订单信息
      "id":"",
      "name":"商品名称",
      "domain":"主机名",
      "type_zh":"产品类型",
      "dedicatedip":"IP",
      "assignedips":"分配IP",
      "uid":"买家(舍弃)",
      "username":"买家(使用这个)",
      "order_status":"订单状态",
      "create_time":"订购时间",
      "amount":"金额",
      "notes":"备注",
      "local_orderid":"本地订单ID(链接用此ID跳转至订单内页)为空或null,禁止跳转",
      "hosts":[{//产品信息
        "name":"产品名称",
        "domain":"主机名",
        "billingcycle":"周期",
        "firstpaymentamount":"金额",
      }]
    }]
  }
}
```

### 续费订单(供应商) -- GET /resource/resourcerenew(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceRenew)

- controller: ``app\res\controller\ResourceHomeController::getResourceRenew``
- desc: 续费订单(供应商) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| start_time | 整型 | 非必填 | - | - | 开始时间(时间搜索) |
| end_time | 整型 | 非必填 | - | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//列表
    }]
  }
}
```

### 获取店铺列表 -- GET /resource/resourceshoplists(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopLists)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopLists``
- desc: 获取店铺列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "shops":[{//店铺信息
    }]
  }
}
```

### 获取店铺信息 -- POST /resource/resourceshopinfo(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopInfo)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopInfo``
- desc: 获取店铺信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 店铺ID(编辑时传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "shop":[{//店铺信息
      "name":"店铺名称",
      "img":"标志",
      "banner_open":"是否开启banner",
      "retired":"1下架,0上架",
      "retired_reason":"下架原因",
    }]
  }
}
```

### 提交店铺信息 -- POST /resource/resourceshopinfo(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopInfo)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopInfo``
- desc: 提交店铺信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 店铺ID（编辑时传,页面返回有ID就传） |
| name | 整型 | 必填 | 1 | - | 店铺名称 |
| description | 整型 | 必填 | 1 | - | 介绍 |
| img | 整型 | 必填 | 1 | - | 标志 |
| banner_open | 整型 | 必填 | 1 | - | 开启banner |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除店铺标志 -- POST /resource/resourceshoplogodelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopLogoDelete)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopLogoDelete``
- desc: 删除店铺标志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 店铺ID（编辑时传,页面返回有ID就传,没有ID就不调此接口） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 切换店铺 -- POST /resource/resourceshoptoggle(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopToggle)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopToggle``
- desc: 切换店铺 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 店铺ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 店铺banner列表 -- GET /resource/resourceshopbannerlists(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopBannerLists)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopBannerLists``
- desc: 店铺banner列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| keywords | 字符串 | 非必填 | 1 | - | 按关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "shop":[{//店铺信息
      "name":"店铺名称",
      "img":"标志",
      "banner_open":"是否开启banner",
    }]
  }
}
```

### 获取店铺banner信息 -- GET /resource/resourceshopbanner(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopBanner)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopBanner``
- desc: 获取店铺banner信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | banner |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "shop":[{//店铺信息
      "name":"店铺名称",
      "img":"标志",
      "banner_open":"是否开启banner",
    }]
  }
}
```

### 保存店铺banner -- POST /resource/resourceshopbanner(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopBanner)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopBanner``
- desc: 保存店铺banner -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | banner |
| title | 整型 | 必填 | 1 | - | banner名称 |
| description | 整型 | 必填 | 1 | - | banner描述 |
| url | 整型 | 必填 | 1 | - | banner地址 |
| begin_time | 整型 | 必填 | 1 | - | 开始时间 |
| end_time | 整型 | 必填 | 1 | - | 结束时间 |
| status | 整型 | 必填 | 1 | - | 状态 |
| img | 整型 | 必填 | 1 | - | banner图片(调上传文件接口admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=upload:取返回值savename) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除店铺banner -- GET /resource/resourceshopbannerdelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=deleteResourceShopBanner)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopBannerDelete``
- desc: 删除店铺banner -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | banner |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### banner排序修改 -- POST /resource/resourceshopbannersort(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopBannerSort)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopBannerSort``
- desc: banner排序修改 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | bannerID |
| pre_id | 整型 | 非必填 | - | - | 移动后前一个banner |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 新增分类 -- POST /resource/resourceshophotgroup(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopHotGroup)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopHotGroup``
- desc: 新增分类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| name | 字符串 | 必填 | 1 | - | 分类名称 |
| mark | 数组 | 必填 | 1 | - | 分类备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除分类 -- GET /resource/resourceshophotgroupdelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=deleteResourceShopHotGroup)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopHotGroupDelete``
- desc: 删除分类 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | 分类ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 新增分类商品页面 -- GET /resource/resourceshophotgroup(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopHotGroup)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopHotGroup``
- desc: 新增分类商品页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 新增分类商品 -- POST /resource/resourceshophotgroupproducts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopHotGroupProducts)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopHotGroupProducts``
- desc: 新增分类商品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 分类ID |
| pids[] | 数组 | 必填 | 1 | - | 商品ID数组 |
| mark | 字符串 | 必填 | 1 | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取店铺热门位列表 -- POST /resource/resourceshophotlists(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopHotLists)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopHotLists1``
- desc: 获取店铺热门位列表 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "groups":[{//分类信息
      "id":"",
      "name":"名称",
      "mark":"备注",
      "products":[{//商品信息
        "name":"商品名称",
        "order":"",
        "id":"",
        "type":"类型",
        "country":"国家",
        "computer_room":"机房",
        "price":"价格",
      }]
    }]
  }
}
```

### 热门位排序拖动 -- POST /resource/resourceshophotorder(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopHotOrder)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopHotOrder``
- desc: 热门位排序拖动 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hot_id | 整型 | 必填 | 1 | - | 热门位ID |
| last_hot_id | 整型 | 必填 | 1 | - | - |
| current_gid | 整型 | 必填 | 1 | - | 移动后 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改店铺热门位信息 -- POST /resource/resourceshophot(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceShopHot)

- controller: ``app\res\controller\ResourceHomeController::postResourceShopHot``
- desc: 修改店铺热门位信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hot_id | 整型 | 必填 | 1 | - | 热门位ID |
| order | 整型 | 必填 | 1 | - | 位置 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除店铺热门位信息 -- GET /resource/resourceshophotdelete(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceShopHotDelete)

- controller: ``app\res\controller\ResourceHomeController::getResourceShopHotDelete``
- desc: 删除店铺热门位信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hot_id | 整型 | 必填 | 1 | - | 热门位ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取供应商基础信息(个人资料) -- GET /resource/resourceshophot(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceSupplierInfo)

- controller: ``app\res\controller\ResourceHomeController::getResourceSupplierInfo``
- desc: 获取供应商基础信息(个人资料) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client":[{//身份认证信息
      "img_one":"证件照片",
      "img_two":"证件照片",
    }]
    "supplier":[{//供应商信息
      "type":"类型",
      "name":"姓名phone:联系电话spare_phone:备用电话email:电子邮箱contact_email:备用邮箱address:地址company:公司名称company_address:公司地址company_web:公司网站company_desc:公司简介main_business:主营业务business_license:营业执照telecom_increment_license:电信增值许可证attachment:其他文件spare_contacts_name:备用联系人姓名spare_contacts_phone:备用联系人电话spare_contacts_email:备用联系人邮箱contract:合同ID，状态为Wait合同待签订时需要status:状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "status_zh":"中文状态：Pending审核中，Active启用，Suspended停用，Cancelled未通过，Wait合同待签订,",
      "cancelled_reason":"",
    }]
    "resource_web_type":" 站点性质,0国内版,1国际版",
  }
}
```

### 工单列表 -- GET /resource/resourcetickets(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceTickets)

- controller: ``app\res\controller\ResourceHomeController::getResourceTickets``
- desc: 工单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 非必填 | - | - | tid |
| email | 字符串 | 非必填 | - | - | 邮件地址 |
| content | 字符串 | 非必填 | - | - | 主题/内容 |
| priority | 字符串 | 非必填 | all | - | 优先级 |
| dptid | 整型 | 非必填 | - | - | 部门id |
| uid | 整型 | 非必填 | - | - | 客户id |
| status | 字符串 | 非必填 | all | - | 状态 |
| limit | 整型 | 非必填 | 10 | - | 条数 |
| page | 整型 | 非必填 | 1 | - | 页数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".limit":"每页条数",
    ".page":"当前页数",
    ".sum":"总条数",
    ".max_page":"最大页数",
    ".list.id":"工单id",
    ".list.tid":"工单tid",
    ".list.uid":"发起工单的用户id",
    ".list.title":"工单标题",
    ".list.status":"工单状态",
    ".list.last_reply_time":"最后回复时间戳",
    ".list.flag_admin":"标记的管理员名称",
    ".list.department_name":"部门名称",
    ".list.user_name":"发起工单的用户名",
    ".list.format_time":"格式化的最后回复时间",
  }
}
```

### 退款列表 -- GET /resource/resourcerefundlists(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceRefundLists)

- controller: ``app\res\controller\ResourceHomeController::getResourceRefundLists``
- desc: 退款列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//商品列表
      "id":"退款ID",
      "invoiceid":"账单ID",
      "name":"产品名称",
      "domain":"主机名",
      "uid":"买家",
      "dedicatedip":"IP",
      "order_status":"产品状态",
      "create_time":"订购时间",
      "refund_status":"退款进度（管理操作=>需要根据此字段进行判断,仅'申请中(Pending)'才会有通过、驳回按钮）",
      "refund_status_zh":"退款进度,中文",
      "refund_reason":"退款原因",
      "amount":"退款金额(周期不要，在这里不合理)(后续接口需要调用)",
      "dcimid":"供应商产品ID(后续接口需要调用)",
    }]
  }
}
```

### 通过与驳回 -- POST /resource/resourcerefundstatus(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceRefundStatus)

- controller: ``app\res\controller\ResourceHomeController::postResourceRefundStatus``
- desc: 通过与驳回 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| refund_id | 整型 | 必填 | 1 | - | 退款记录ID |
| status | 字符串 | 必填 | 1 | - | Refunded通过，Cancelled驳回 |
| amount | 整型 | 必填 | 1 | - | 金额（此参数当status==Refunded时才传） |
| dcimid | 整型 | 必填 | 1 | - | 供应商产品ID（此参数当status==Refunded时才传） |
| cancelled_reason | 字符串 | 必填 | 1 | - | 驳回原因（此参数当status==Cancelled时才传） |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 售后列表 -- GET /resource/resourceaftersalelists(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceAfterSaleLists)

- controller: ``app\res\controller\ResourceHomeController::getResourceAfterSaleLists``
- desc: 售后列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//商品列表
      "id":"售后ID",
      "invoiceid":"账单ID",
      "name":"产品名称",
      "domain":"主机名",
      "uid":"买家",
      "dedicatedip":"IP",
      "order_status":"产品状态",
      "create_time":"订购时间",
      "after_sale_status":"投诉状态（管理操作=>需要根据此字段进行判断,仅'申请中(Pending)'才会有处理按钮）",
      "after_sale_status_zh":"投诉状态,中文",
      "reason":"投诉原因",
      "amount":"退款金额(周期不要，在这里不合理)",
      "dcimid":"供应商产品ID",
    }]
  }
}
```

### 售后处理 -- POST /resource/resourceaftersale(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=postResourceAfterSale)

- controller: ``app\res\controller\ResourceHomeController::postResourceAfterSale``
- desc: 售后处理 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| after_sale_id | 整型 | 必填 | 1 | - | 售后记录ID |
| result | 字符串 | 必填 | 1 | - | 处理结果，输入结果 |
| supplier_img[] | 数组 | 必填 | 1 | - | 处理附件,多文件,传数组(调admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=upload上传文件,取savename返回值传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 售后详情 -- GET /resource/resourceaftersaledetail(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceAfterSaleDetail)

- controller: ``app\res\controller\ResourceHomeController::getResourceAfterSaleDetail``
- desc: 售后详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 售后记录ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "id":"售后ID",
    "after_sale_status_zh":"状态",
    "refund":"退款总金额",
    "usename":"买家",
    "after_sale_create_time":"申请时间",
    "amount":"订单金额",
    "prefix":"货币",
    "suffix":"货币",
    "reason":"售后原因",
    "update_time":"处理时间",
    "after_sale_status_zh":"状态",
    "supplier_reply":"回复供应商",
    "supplier_reply_img":"图片",
    "invoiceid":"账单ID",
    "dedicatedip":"IP",
    "username":"买家",
    "firstpaymentamount":"金额",
    "billingcycle_zh":"周期",
    "supplier":"卖家",
    "name":"产品名称",
    "domain":"主机名",
    "order_status":"订单状态",
  }
}
```

### 信息概览(供应商调取) -- GET /resource/resourceinfosummary(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceInfoSummary)

- controller: ``app\res\controller\ResourceHomeController::getResourceInfoSummary``
- desc: 信息概览(供应商调取) -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total_income":"总销售额",
    "this_month_income":"月销售额",
    "withdraw":"已提现",
    "refund":"已退款",
    "under_guarantee":"担保中",
    "available_credit":"可用余额",
    "guarantee_credit":"冻结余额",
    "credit":"余额",
    "active":"激活产品数量",
    "count":"总产品数量",
  }
}
```

### 消费数据 -- GET /resource/resourceinfoconsumption(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceInfoConsumption)

- controller: ``app\res\controller\ResourceHomeController::getResourceInfoConsumption``
- desc: 消费数据 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| start | 整型 | 必填 | 10 | - | 开始时间 |
| end | 整型 | 必填 | 10 | - | 结束时间 |
| type | 字符串 | 必填 | 10 | - | 消费类型 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//商品列表
      "id":"账单",
      "subtotal":"金额",
      "payment":"支付方式",
      "paid_time":"支付时间",
      "type":"消费类型",
      "notes":"备注",
      "local_invoiceid":"本地账单ID（根据此ID跳转至账单内页,若为null，禁止跳转）",
    }]
  }
}
```

### 收入概览 -- GET /resource/resourceinfoincome(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceInfoIncome)

- controller: ``app\res\controller\ResourceHomeController::getResourceInfoIncome``
- desc: 收入概览 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 1 | - | week近一周,month本月,year全年 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "arr":"收入数据,需要根据类型,进行相应显示",
  }
}
```

### 产品信息 -- GET /resource/resourceinfohosts(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getResourceInfoHosts)

- controller: ``app\res\controller\ResourceHomeController::getResourceInfoHosts``
- desc: 产品信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//商品列表
      "id":"",
      "name":"产品名称",
      "dedicatedip":"ip",
      "paid_time":"支付时间",
      "nextduedate":"到期时间",
      "billingcycle_zh":"周期",
      "amount":"金额（成本，利润不确定）",
      "notes":"备注",
    }]
  }
}
```

### 供应商日志 -- GET /resource/supplierLogs(admin/addons?_plugin=idcsmart_res_supplier&_controller=AdminIndex&_action=getSupplierLogs)

- controller: ``app\res\controller\ResourceHomeController::getSupplierLogs``
- desc: 供应商日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| desc | 整型 | 非必填 | '' | - | 描述 |
| timer | 字符串 | 非必填 | '' | - | 时间 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"idcreate_time:时间desc:描述ip:ipreferer:来源active_name:操作人user_type_cn:操作人角色",
    }]
  }
}
```

### 退款详情(代理商)

- controller: ``app\res\controller\ResourceHomeController::getRefundDetail``
- desc: 退款详情(代理商) -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 售后详情

- controller: ``app\res\controller\ResourceHomeController::getAfterSaleDetail``
- desc: 售后详情 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 代理商日志 -- GET /resource/agentLogs

- controller: ``app\res\controller\ResourceHomeController::getAgentLogs``
- desc: 代理商日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| desc | 整型 | 非必填 | '' | - | 描述 |
| timer | 字符串 | 非必填 | '' | - | 时间 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"idcreate_time:时间desc:描述ip:ipreferer:来源active_name:操作人user_type_cn:操作人角色",
    }]
  }
}
```

### 订单评价 -- POST /resource/evaluation

- controller: ``app\res\controller\ResourceHomeController::postEvaluation``
- desc: 订单评价 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |
| type | 字符串 | 必填 | - | - | 类型great好评middle中评bad差评 |
| score | 字符串 | 必填 | - | - | 综合评价 |
| netword_score | 字符串 | 必填 | - | - | 网络质量 |
| hardware_score | 字符串 | 必填 | - | - | 硬件年限 |
| img | 数组 | 必填 | - | - | 图片 |
| id | 整型 | 必填 | - | - | 账单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 资源池商店(前台)

### 资源池商店首页 -- GET /resource/shop/resource

- controller: ``app\res\controller\ResourceShopController::getResource``
- desc: 资源池商店首页 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池商品 -- GET /resource/shop/products

- controller: ``app\res\controller\ResourceShopController::getProducts``
- desc: 资源池商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | 1 | - | 搜索关键字(非必传参数) |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| type | 字符串 | 必填 | 10 | - | 商品类型 |
| area | 字符串 | 必填 | 10 | - | 地区 |
| shop | 字符串 | 必填 | 10 | - | 商家 |
| category | 整型 | 必填 | 10 | - | 类目ID |
| sort | 字符串 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池商店页 -- GET /resource/shop/shop

- controller: ``app\res\controller\ResourceShopController::getShop``
- desc: 资源池商店页 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 10 | - | 商店ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用户收藏应用 -- GET /resource/shop/favorite

- controller: ``app\res\controller\ResourceShopController::getFavorite``
- desc: 获取用户收藏应用 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| type | 字符串 | 必填 | 10 | - | 商品类型, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收藏夹添加商品 -- POST /resource/shop/favorite

- controller: ``app\res\controller\ResourceShopController::postFavorite``
- desc: 收藏夹添加商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 10 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收藏夹移除商品 -- DELETE /resource/shop/favorite

- controller: ``app\res\controller\ResourceShopController::deleteFavorite``
- desc: 收藏夹移除商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 10 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 举报商品 -- POST /resource/shop/report

- controller: ``app\res\controller\ResourceShopController::postReport``
- desc: 举报商品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 10 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 举报通知 -- GET /resource/shop/report

- controller: ``app\res\controller\ResourceShopController::getReport``
- desc: 举报通知 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| status | 字符串 | 必填 | 10 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商品评论 -- GET /resource/shop/evaluation

- controller: ``app\res\controller\ResourceShopController::getEvaluation``
- desc: 商品评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| order | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| type | 字符串 | 非必填 | - | - | 类型great好评bad差评middle中评with_img有图 |
| id | 字符串 | 必填 | 10 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 我的订单 -- GET /resource/shop/orderlist

- controller: ``app\res\controller\ResourceShopController::getOrderList``
- desc: 我的订单 -- 上官🔪

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| status | 字符串 | 非必填 | - | - | 状态(Pending待审核，Active已激活，Completed已完成,Suspend已暂停,Terminated被删除,Cancelled被取消,Fraud有欺诈) |
| ordernum | 整型 | 非必填 | - | - | 订单号 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |
| amount | 整型 | 非必填 | - | - | 金额 |
| uid | 整型 | 非必填 | - | - | 用户 |
| payment | 整型 | 非必填 | - | - | 支付方式 |
| pay_status | 整型 | 非必填 | - | - | 1, |
| sale_id | 整型 | 非必填 | - | - | 1, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表
      "id":"编号",
      "uid":"用户id",
      "create_time":"",
      "username":"",
      "payment":"付款方式",
      "amount":"总计",
      "pay_status":"付款状态",
      "status":"状态",
    }]
  }
}
```

### 我的代理 -- GET /resource/shop/agentproducts

- controller: ``app\res\controller\ResourceShopController::getAgentProducts``
- desc: 我的代理 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 10 | - | 搜索类型 |
| keywords | 字符串 | 必填 | 10 | - | 关键字 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 资源池商店搜索 -- GET /resource/shop/search

- controller: ``app\res\controller\ResourceShopController::getSearch``
- desc: 资源池商店搜索 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 必填 | 10 | - | 关键字 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 分类内页 -- GET /resource/shop/shoptype

- controller: ``app\res\controller\ResourceShopController::getShopType``
- desc: 分类内页 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 10 | - | 分类ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商品内页 -- GET /resource/shop/product

- controller: ``app\res\controller\ResourceShopController::getProduct``
- desc: 商品内页 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 10 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价点赞 -- POST /resource/shop/evaluationlike

- controller: ``app\res\controller\ResourceShopController::postEvaluationLike``
- desc: 评价点赞 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 评价id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价取消点赞 -- DELETE /resource/shop/evaluationlike

- controller: ``app\res\controller\ResourceShopController::deleteEvaluationLike``
- desc: 评价取消点赞 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 评价id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 代理单个商品页面 -- GET /resource/shop/agentproductspage

- controller: ``app\res\controller\ResourceShopController::getAgentProductsPage``
- desc: 代理单个商品页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shop_id | 整型 | 必填 | 1 | - | 店铺ID |
| pid | 整型 | 必填 | 1 | - | 商品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 代理店铺页面 -- GET /resource/shop/agentshop

- controller: ``app\res\controller\ResourceShopController::getAgentShop``
- desc: 代理店铺页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shop_id | 整型 | 必填 | 1 | - | 店铺ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//代理商品信息
      "id":"商品ID",
      "shop_name":"店铺名称",
      "product_name":"商品名称",
      "local_name":"本地名称",
    }]
  }
}
```

### 代理商品 -- POST /resource/shop/agentproducts

- controller: ``app\res\controller\ResourceShopController::postAgentProducts``
- desc: 代理商品 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pids | 整型 | 必填 | 1 | - | 商品ID(数组) |
| profit[pid] | 整型 | 必填 | 1 | - | 利润 |
| name[pid] | 整型 | 必填 | 1 | - | 商品 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 由客户后台资源池中心登录 -- GET /resource/shop/login

- controller: ``app\res\controller\ResourceShopController::getLogin``
- desc: 由客户后台资源池中心登录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| jwt | 整型 | 必填 | 1 | - | jwt |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 代理商

### 资源池登录信息 -- GET /admin/agent/resourceinfo

- controller: ``app\admin\controller\AgentController::getResourceInfo``
- desc: 资源池登录信息 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "username":"账号",
    "password":"密码",
  }
}
```

### 提交资源池登录信息 -- POST /admin/agent/resourceinfo

- controller: ``app\admin\controller\AgentController::postResourceInfo``
- desc: 提交资源池登录信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | API |
| username | 字符串 | 必填 | - | - | 账号 |
| password | 字符串 | 必填 | - | - | API密钥 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 工单传递 -- POST /admin/agent/resourceticketopen

- controller: ``app\admin\controller\AgentController::postResourceTicketOpen``
- desc: 工单传递 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | API |
| ticket_open | 整型 | 必填 | - | - | 是否开启工单传递:1是,0否 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 测试链接 -- POST /admin/agent/linktoresource

- controller: ``app\admin\controller\AgentController::postLinkToResource``
- desc: 测试链接 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 商品列表 -- GET /admin/agent/products

- controller: ``app\admin\controller\AgentController::getProducts``
- desc: 商品列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keyword | mixed | 非必填 | - | - | 关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "products":[{//列表
      "id":"",
      "gname":"分类名称",
      "qty":"本地",
      "upstream_qty":"上游",
      "host_count":"数量",
      "host_active":"激活",
      "billingcycle_zh":"周期",
    }]
    "product_count":"产品 总数",
    "local_qty":"本地 库存 总",
    "upstream_qty":"上游 库存 总",
    "host_total":"数量 总",
    "host_active":"激活 总",
  }
}
```

### 订单列表搜索页面 -- GET admin/agent/ordersearchpage

- controller: ``app\admin\controller\AgentController::getOrderSearchPage``
- desc: 订单列表搜索页面 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| status | 整型 | 非必填 | - | - | 订单状态 |
| order_type | 整型 | 非必填 | - | - | 订购类型 |
| supplier_username | 字符串 | 非必填 | - | - | 卖家 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订单列表 -- GET admin/agent/order

- controller: ``app\admin\controller\AgentController::getOrder``
- desc: 订单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| type | 字符串 | 非必填 | - | - | 订单类型order_type |
| value | 字符串 | 非必填 | - | - | 传返回的type的键 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "rows":[{//订单列表信息
      "id":"资源池订单ID",
      "local_id":"本地订单ID(跳转至订单内页以这个为准)",
      "local_hostid":"本地产品ID(跳转至产品内页以这个为准)",
      "name":"商品名称",
      "domain":"主机名",
      "local_name":"本地商品",
      "local_domain":"本地主机",
      "product_type":"产品类型",
      "dedicatedip":"IP",
      "supplier_username":"供应商用户名(卖家)",
      "agent_username":"代理商用户名(买家?用户?)",
      "local_username":"在代理商购买商品的客户",
      "order_status":"订单状态",
      "create_time":"订购时间",
      "invoice_type_zh":"订单类型",
      "billingcycle_zh":"周期",
      "payment":"代理商",
      "local_payment":"",
      "order_notes":"代理商",
      "local_order_notes":"客户在代理商处的",
      "i_status":"订单支付状态：除了Unpaid外，其他都有退款按钮",
      "evaluation_id":"评论ID,大于0代表已评论",
      "resource_amount":"资源池购买价格",
      "current_rate":"相对资源池货币汇率",
      "after_sale":"售后记录信息(有此值，则显示",
      "hosts":[{//产品信息
        "hostid":"产品ID",
        "local_hostid":"本地产品ID(跳转以至产品内页以这个为准)",
        "name":"产品名称",
        "domain":"主机名",
        "dedicatedip":"IP",
        "billingcycle":"周期",
        "firstpaymentamount":"金额",
        "type":"产品类型",
      }]
    }]
  }
}
```

### 续费订单列表搜索页面 -- GET admin/agent/renewsearchpage

- controller: ``app\admin\controller\AgentController::getRenewSearchPage``
- desc: 续费订单列表搜索页面 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| status | 整型 | 非必填 | - | - | 订单状态 |
| supplier_username | 字符串 | 非必填 | - | - | 卖家 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 续费订单列表 -- GET admin/agent/renew

- controller: ``app\admin\controller\AgentController::getRenew``
- desc: 续费订单列表 -- xiong

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序规则(asc/desc) |
| type | 字符串 | 非必填 | - | - | 订单类型order_type |
| value | 字符串 | 非必填 | - | - | 传返回的type的键 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "rows":[{//订单列表信息
      "id":"资源池账单ID",
      "name":"商品名称",
      "domain":"主机名",
      "dedicatedip":"IP",
      "paid_time":"续费时间",
      "amount":"金额",
      "supplier_username":"供应商用户名(卖家)",
      "payment":"代理商",
      "local_hostid":"本地产品ID(跳转至产品内页以这个为准)",
      "local_name":"本地商品",
      "local_domain":"本地主机",
      "local_invoiceid":"本地账单ID(跳转至账单内页以这个为准)",
      "local_username":"在代理商购买商品的客户",
      "local_payment":"",
    }]
  }
}
```

### 售后详情 -- GET /admin/agent/afterSaleDetail

- controller: ``app\admin\controller\AgentController::getAfterSaleDetail``
- desc: 售后详情 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | after_sale.id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 退款详情 -- GET /admin/agent/refundDetail

- controller: ``app\admin\controller\AgentController::getRefundDetail``
- desc: 退款详情 -- xue

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 账单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 产品列表 -- GET /admin/agent/host

- controller: ``app\admin\controller\AgentController::getHost``
- desc: 产品列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| keywords | mix | 非必填 | - | - | 关键字搜索 |
| status | mix | 非必填 | - | - | 状态搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "status":"状态,搜索使用",
    "rows":[{//产品信息
      "id":"",
      "name":"商品名称",
      "domain":"主机名",
      "dedicatedip":"IP",
      "supplier_username":"卖家",
      "agent_username":"买家(工单先不要)",
      "order_status":"状态",
      "create_time":"订购时间",
      "amount":"金额",
      "billingcycle_zh":"周期",
      "local_hostid":"本地产品ID(跳转至产品内页需要此参数)",
      "local_uid":"本地客户ID(跳转至产品内页需要此参数)",
      "shop_name":"店铺名称",
    }]
  }
}
```

### 协查列表 -- GET /admin/agent/inspectionlists

- controller: ``app\admin\controller\AgentController::getInspectionLists``
- desc: 协查列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| keywords | 字符串 | 非必填 | 1 | - | 按关键字搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "lists":[{//商品列表
      "id":"",
      "username":"买家",
      "dedicatedip":"独立ip",
      "assignedips":"分配ip",
      "create_time":"提交时间",
      "status_zh":"状态",
      "reason":"驳回原因，当status==Cancelled时，才显示",
    }]
  }
}
```

### 上传图片 -- POST admin/agent/upload

- controller: ``app\admin\controller\AgentController::postUpload``
- desc: 上传图片 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| image|file | file | 必填 | 0 | - | 图片 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "savename":"上传的文件路径",
  }
}
```

### 协查申请 -- POST /admin/agent/resourceinspection

- controller: ``app\admin\controller\AgentController::postResourceInspection``
- desc: 协查申请 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| police | 整型 | 必填 | 1 | - | 警官姓名 |
| agency | 整型 | 必填 | 1 | - | 执法机构 |
| ip | 整型 | 必填 | 1 | - | 调取IP |
| orderid | 整型 | 必填 | 1 | - | 订单ID(这里只能选一个) |
| email | 整型 | 必填 | 1 | - | 邮件地址 |
| phone | 整型 | 必填 | 1 | - | 联系电话 |
| police_card | 整型 | 必填 | 1 | - | 警官证((调上传文件接口admin/agent/upload取返回值savename)) |
| law | 整型 | 必填 | 1 | - | 法律文书(调上传文件接口admin/agent/upload取返回值savename) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 匹配ip订单 -- GET /admin/agent/inspectionip

- controller: ``app\admin\controller\AgentController::getInspectionIp``
- desc: 匹配ip订单 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| ip | 整型 | 必填 | 1 | - | 调取IP |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "orders":[{//订单信息
      "id":"订单IDusername:客户companyname:公司名dedicatedip:ipassignedips:ipcreate_time:订购时间status_zh:状态，颜色",
    }]
  }
}
```

### 协查详情 -- GET /admin/agent/inspectiondetail

- controller: ``app\admin\controller\AgentController::getInspectionDetail``
- desc: 协查详情 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 协查ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "inspection":[{//协查信息
      "police":"警官姓名",
      "agency":"执法机构",
      "ip":"调取IP",
      "email":"邮件地址",
      "phone":"联系电话",
      "police_card":"警官证",
      "law":"法律文书",
      "status":"状态：Pending待审核（默认），Active通过，Cancelled驳回",
      "status_zh":"状态：Pending待审核（默认），Active通过，Cancelled驳回",
      "reason":"驳回原因",
    }]
    "client":[{//基础信息
      "username":"用户名",
      "create_time":"创建时间",
      "lastlogin":"最后登录时间",
      "lastloginip":"最后登录ip",
      "register":"注册天数",
    }]
    "agent":[{//认证信息
      "name":"姓名",
      "email":"邮箱",
      "phone":"电话",
      "address":"地址",
      "company":"公司名称",
      "company_address":"公司地址",
      "business_license":"营业执照",
      "img_one":"身份证1",
      "img_two":"身份证2",
    }]
    "order":[{//购买信息
      "id":"",
      "name":"名称",
      "domain":"主机名",
      "type_zh":"产品类型",
      "dedicatedip":"IP",
      "status_zh":"订单状态",
      "create_time":"订购时间",
      "invoice_type_zh":"订单类型",
      "amount":"金额",
      "billingcycle_zh":"周期",
      "payment":"付款方式",
    }]
    "login":[{//登录信息
      "id":"",
      "create_time":"",
      "description":"",
      "user":"",
      "orgin":"",
    }]
  }
}
```

### 退款 -- POST /admin/agent/refund

- controller: ``app\admin\controller\AgentController::postRefund``
- desc: 退款 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoiceid | 整型 | 必填 | 1 | - | 账单ID |
| amount | 整型 | 必填 | 1 | - | 退款金额 |
| subtotal | 整型 | 必填 | 1 | - | 总金额 |
| refund_reason | 字符串 | 必填 | 1 | - | 退款原因 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 申请售后 -- POST /admin/agent/aftersale

- controller: ``app\admin\controller\AgentController::postAfterSale``
- desc: 申请售后 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| orderid | 整型 | 必填 | 1 | - | 订单ID |
| reason | 字符串 | 必填 | 1 | - | 申请原因 |
| agent_img[] | 数组 | 非必填 | 1 | - | 申请附件(调上传文件接口admin/agent/upload取返回值savename),拼接成数组 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 撤销申请 -- POST /admin/agent/unaftersale

- controller: ``app\admin\controller\AgentController::postUnAfterSale``
- desc: 撤销申请 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 订单ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 基础信息 -- GET /admin/agent/baseinfo

- controller: ``app\admin\controller\AgentController::getBaseInfo``
- desc: 基础信息 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"总利润",
    "month":"本月利润",
    "host_total":"产品总数",
    "host_active":"激活产品数",
    "currency":"货币",
    "client":"@",
  }
}
```

### 代理商日志 -- GET /admin/agent/agentLogs

- controller: ``app\admin\controller\AgentController::getAgentLogs``
- desc: 代理商日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| desc | 整型 | 非必填 | '' | - | 描述 |
| timer | 字符串 | 非必填 | '' | - | 时间 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "logs":[{//日志
      "id":"idcreate_time:时间desc:描述ip:ipreferer:来源active_name:操作人user_type_cn:操作人角色",
    }]
  }
}
```

### 消费数据 -- GET /admin/agent/consumption

- controller: ``app\admin\controller\AgentController::getConsumption``
- desc: 消费数据 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |
| start | 整型 | 必填 | 10 | - | 开始时间 |
| end | 整型 | 必填 | 10 | - | 结束时间 |
| type | 字符串 | 必填 | 10 | - | 消费类型 |
| gateway | 字符串 | 必填 | 10 | - | 支付方式,传支付列表的name |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//商品列表
      "id":"账单",
      "subtotal":"金额",
      "payment":"支付方式",
      "paid_time":"支付时间",
      "type":"消费类型",
      "notes":"备注",
    }]
    "gateways":[{//支付列表
    }]
  }
}
```

### 收入概览 -- GET /admin/agent/income

- controller: ``app\admin\controller\AgentController::getIncome``
- desc: 收入概览 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 1 | - | week近一周,month本月,year全年 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "arr":"收入数据,需要根据类型,进行相应显示",
  }
}
```

### 产品信息 -- GET /admin/agent/hostlists

- controller: ``app\admin\controller\AgentController::getHostLists``
- desc: 产品信息 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | ASC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "rows":[{//商品列表
      "id":"",
      "name":"产品名称",
      "dedicatedip":"ip",
      "paid_time":"支付时间",
      "nextduedate":"到期时间",
      "billingcycle_zh":"周期",
      "amount":"售价",
      "cost":"成本",
      "notes":"备注",
    }]
  }
}
```

### 工单列表 -- GET admin/agent/tickets

- controller: ``app\admin\controller\AgentController::getTickets``
- desc: 工单列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| tid | 字符串 | 非必填 | - | - | tid |
| email | 字符串 | 非必填 | - | - | 邮件地址 |
| content | 字符串 | 非必填 | - | - | 主题/内容 |
| priority | 字符串 | 非必填 | all | - | 优先级 |
| dptid | 整型 | 非必填 | - | - | 部门id |
| uid | 整型 | 非必填 | - | - | 客户id |
| status | 字符串 | 非必填 | all | - | 状态 |
| limit | 整型 | 非必填 | 10 | - | 条数 |
| page | 整型 | 非必填 | 1 | - | 页数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".limit":"每页条数",
    ".page":"当前页数",
    ".sum":"总条数",
    ".max_page":"最大页数",
    ".list.id":"工单id",
    ".list.tid":"工单tid",
    ".list.uid":"发起工单的用户id",
    ".list.title":"工单标题",
    ".list.status":"工单状态",
    ".list.last_reply_time":"最后回复时间戳",
    ".list.flag_admin":"标记的管理员名称",
    ".list.department_name":"部门名称",
    ".list.user_name":"发起工单的用户名",
    ".list.format_time":"格式化的最后回复时间",
  }
}
```

### 供应商任务队列列表 -- GET admin/agent/runmaplists

- controller: ``app\admin\controller\AgentController::getRunMapLists``
- desc: 供应商任务队列列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字 |
| user | 字符串 | 非必填 | - | - | 用户名关键字 |
| from_type | 整型 | 非必填 | - | - | 来源类型 |
| status | 整型 | 非必填 | - | - | 状态 |
| active_type | 整型 | 非必填 | - | - | 来源类型 |
| status | 整型 | 非必填 | - | - | 执行状态 |
| page | 整型 | 非必填 | 1 | - | 页码 |
| limit | 整型 | 非必填 | 1 | - | 每页条数 |
| order | 字符串 | 非必填 | create_time | - | 排序字段 |
| sort | 字符串 | 非必填 | desc | - | desc/asc，倒叙/顺序 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 订单评价 -- POST admin/agent/evaluation

- controller: ``app\admin\controller\AgentController::postEvaluation``
- desc: 订单评价 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |
| type | 字符串 | 必填 | - | - | 类型great好评middle中评bad差评 |
| score | 字符串 | 必填 | - | - | 综合评价 |
| netword_score | 字符串 | 必填 | - | - | 网络质量 |
| hardware_score | 字符串 | 必填 | - | - | 硬件年限 |
| img | 数组 | 必填 | - | - | 图片 |
| id | 整型 | 必填 | - | - | 订单id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 生成资源池jwt -- GET /admin/agent/token

- controller: ``app\admin\controller\AgentController::getToken``
- desc: 生成资源池jwt -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "jwt":"",
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\AgentController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\AgentController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\AgentController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\AgentController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\AgentController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\AgentController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\AgentController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\AgentController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 余额提现

### 收款方式 页面 (编辑) -- GET /withdraw/paymethodpage

- controller: ``app\home\controller\WithdrawController::getPayMethodPage``
- desc: 收款方式 页面 (编辑) -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 收款方式ID(非必传,编辑时才传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".type":"提现类型",
    ".bank":"提现银行",
    "withdraw_method":"收款方式信息(编辑使用，bank时：account_bank，account_name，account_num，account_address；alipay时：username姓名，alipay支付宝账号)",
  }
}
```

### 添加收款方式 （含编辑） -- POST /withdraw/paymethod

- controller: ``app\home\controller\WithdrawController::postPayMethod``
- desc: 添加收款方式 （含编辑） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 收款方式ID(非必传,编辑时才传) |
| type | 字符串 | 必填 | 1 | - | 收款方式,bank银行,alipay支付宝 |
| account_bank | 字符串 | 非必填 | 1 | - | 开户银行 |
| account_name | 字符串 | 非必填 | 1 | - | 开户名称 |
| account_num | 字符串 | 非必填 | 1 | - | 开户账号 |
| account_address | 字符串 | 非必填 | 1 | - | 开户网点 |
| username | 字符串 | 非必填 | 1 | - | 姓名(这两个个参数收款方式为alipay时传,传就是必填) |
| alipay | 字符串 | 非必填 | 1 | - | 支付宝账号 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除收款方式 -- DELETE /withdraw/paymethod

- controller: ``app\home\controller\WithdrawController::deletePayMethod``
- desc: 删除收款方式 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 收款方式ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 设置默认收款方式 -- POST /withdraw/defaultmethod

- controller: ``app\home\controller\WithdrawController::postDefaultMethod``
- desc: 设置默认收款方式 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 收款方式ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提现 页面 -- GET /withdraw/withdrawpage

- controller: ``app\home\controller\WithdrawController::getWithdrawPage``
- desc: 提现 页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "withdraw_method":"提现方式信息",
    "withdraw_method_default":"默认方式ID",
    "total":"总资金",
    "frozen":"冻结资金",
    "shd_allow_withdraw":"允许提现,1开启,0关闭",
    "allow_withdraw_bank":"1允许银行卡提现,0否",
    "allow_withdraw_alipay":"1允许支付宝提现,0否",
    "allow_withdraw_bank_BOC":"1允许中国银行提现,0否",
    "allow_withdraw_bank_ICBC":"1允许中国工商银行提现,0否",
    "allow_withdraw_bank_ABC":"1允许中国农业银行提现,0否",
    "allow_withdraw_bank_CCB":"1允许中国建设银行提现,0否",
    "allow_withdraw_bank_PSBC":"1允许中国邮政银行提现,0否",
    "minimum_withdrawal_amount":"最低提现金额",
    "withdrawal_fee":"提现手续费",
  }
}
```

### 提现 -- POST /withdraw/withdraw

- controller: ``app\home\controller\WithdrawController::postWithdraw``
- desc: 提现 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 收款方式ID |
| amount | dcimal | 必填 | 1 | - | 金额 |
| type | 字符串 | 必填 | 1 | - | 类型income收益提现,credit余额提现 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 提现记录 -- GET /withdraw/withdraw

- controller: ``app\home\controller\WithdrawController::getWithdraw``
- desc: 提现记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |
| status | 字符串 | 非必填 | 10 | - | Pending,Cancelled,Active |
| type | 字符串 | 非必填 | 10 | - | 收款方式bank,alipay |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "logs":"提现记录",
    "count":"数量",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\WithdrawController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\WithdrawController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\WithdrawController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\WithdrawController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 提现(后台)

### 提现记录 -- GET admin/withdraw/withdraw

- controller: ``app\admin\controller\WithdrawController::getWithdraw``
- desc: 提现记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 字符串 | 必填 | 10 | - | AESC,DESC |
| status | 字符串 | 非必填 | 10 | - | Pending,Cancelled,Active |
| type | 字符串 | 非必填 | 10 | - | 收款方式bank,alipay |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "logs":"提现记录=>禅道列表顺序显示",
    "count":"数量",
  }
}
```

### 审核提现记录 -- POST /admin/withdraw/withdraw

- controller: ``app\admin\controller\WithdrawController::postWithdraw``
- desc: 审核提现记录 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 提现记录ID |
| status | 字符串 | 必填 | 1 | - | Active启用，Cancelled未通过 |
| cancelled_reason | 字符串 | 非必填 | 1 | - | 未通过原因(非必传参数),Cancelled必传必填 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台对接DCIM授权管理

### 重置授权 --  PUT /admin/dcimauth/reset

- controller: ``app\admin\controller\DcimAuthController::resetAuth``
- desc: 重置授权 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |
| license | 字符串 | 必填 | - | - | 授权码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除授权 --  DELETE /admin/dcimauth

- controller: ``app\admin\controller\DcimAuthController::deleteAuth``
- desc: 删除授权 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |
| license | 字符串 | 必填 | - | - | 授权码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 恢复授权 --  PUT /admin/dcimauth/recover

- controller: ``app\admin\controller\DcimAuthController::recoverAuth``
- desc: 恢复授权 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |
| license | 字符串 | 必填 | - | - | 授权码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取使用数据 --  GET /admin/dcimauth/used

- controller: ``app\admin\controller\DcimAuthController::getUsedData``
- desc: 获取使用数据 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "category":"日期数组",
    "purchases":"购买量数组",
    "usage":"使用量数组",
    "percent_free":"空闲百分比数组",
  }
}
```

### 修改授权 --  PUT /admin/dcimauth

- controller: ``app\admin\controller\DcimAuthController::editAuth``
- desc: 修改授权 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |
| license | 字符串 | 必填 | - | - | 授权码 |
| name | 字符串 | 必填 | - | - | 授权名称 |
| authorize_type | 整型 | 必填 | - | - | 授权类型(0:专业1:普惠) |
| network_type | 整型 | 必填 | - | - | 网络类型(0:内网1:外网) |
| ip | 字符串 | 非必填 | - | - | 授权IP |
| diver_sum | 整型 | 非必填 | - | - | 授权数量 |
| max_accused | 整型 | 非必填 | - | - | 被控数量 |
| lmc_num | 整型 | 非必填 | - | - | 裸金属LMC数量 |
| lc_num | 整型 | 非必填 | - | - | 裸金属LC数量 |
| version | 字符串 | 必填 | - | - | 版本 |
| price | 字符串 | 必填 | - | - | 价格 |
| create_date | 字符串 | 非必填 | - | - | 创建时间 |
| next_date | 字符串 | 非必填 | - | - | 到期时间 |
| addition | 字符串 | 非必填 | - | - | 附加产品 |
| addons | 字符串 | 非必填 | - | - | 特殊产品 |
| expansion | 字符串 | 非必填 | - | - | 扩展功能 |
| remarks | 字符串 | 非必填 | - | - | 价格 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推送热更新 --  PUT /admin/dcimauth/hotfix

- controller: ``app\admin\controller\DcimAuthController::hotfixPush``
- desc: 推送热更新 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推送更新 --  PUT /admin/dcimauth/update

- controller: ``app\admin\controller\DcimAuthController::updatePush``
- desc: 推送更新 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 授权ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 授权详情 --  GET /admin/dcimauth/:id

- controller: ``app\admin\controller\DcimAuthController::detail``
- desc: 授权详情 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| listpages | 整型 | 非必填 | - | - | 授权日志每页数量 |
| page | 整型 | 非必填 | - | - | 授权日志页数 |
| listpages2 | 整型 | 非必填 | - | - | 操作日志每页数量 |
| page2 | 整型 | 非必填 | - | - | 操作日志页数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "detail":[{//详情
      "id":"授权ID",
      "name":"名称",
      "diver_sum":"授权数量",
      "diver_count":"已添加设备数量",
      "hardware":"服务器数量",
      "switch":"交换机数量",
      "hardware_pdu":"PDU设备数量",
      "create_date":"创建时间",
      "next_date":"到期时间",
      "ip":"绑定IP",
      "addition":"支持的附加服务",
      "license":"授权码",
      "remarks":"备注",
      "status":"状态",
      "version":"版本号",
      "authorize_type":"0专业版1普惠版",
      "price":"价格",
      "accused_num":"被控数量",
      "result":"更新结果",
      "max_accused":"最大被控数量",
      "addons":"特殊产品",
      "expansion":"拓展产品",
      "network_type":"0外网部署1内网部署",
      "basic_version":"基础版本",
      "hotfix_version":"热更新版本",
      "hotfix_push_time":"热更新推送时间",
      "hotfix_push_status":"0:允许推送1:禁止推送",
      "update_type":"0:非立刻更新1:立刻更新",
      "update_support_type":"0:beta1:稳定",
      "update_status":"0:不可更新1:可更新",
      "lmc_num":"裸金属LMC数量",
      "lc_num":"裸金属LC数量",
      "accused_ip":"被控IP",
      "hostid":"主机ID",
      "uid":"用户ID",
      "username":"用户名",
      "is_combine":"是否和财务系统关联(true:是false:否)",
    }]
    "addition":"附加服务",
    "addons":"特殊产品",
    "expansion":"拓展产品",
    "log":[{//授权日志
      "list":[{//授权日志
        "id":"授权日志ID",
        "aid":"授权ID",
        "date":"操作时间",
        "ip":"请求者IP",
        "detail":"请求者本身授权信息",
        "status":"1成功,0失败",
        "remarks":"错误原因",
        "id":"操作日志ID",
        "uid":"操作者ID",
        "username":"操作人",
        "description":"操作详细",
        "date":"操作时间",
        "ip":"操作者IP地址",
        "aid":"授权ID",
      }]
      "total":"总页数",
      "current_page":"当前页数",
      "sum":"总数",
      "listpages":"每页数量",
      "list":[{//操作日志
        "id":"授权日志ID",
        "aid":"授权ID",
        "date":"操作时间",
        "ip":"请求者IP",
        "detail":"请求者本身授权信息",
        "status":"1成功,0失败",
        "remarks":"错误原因",
        "id":"操作日志ID",
        "uid":"操作者ID",
        "username":"操作人",
        "description":"操作详细",
        "date":"操作时间",
        "ip":"操作者IP地址",
        "aid":"授权ID",
      }]
      "total":"总页数",
      "current_page":"当前页数",
      "sum":"总数",
      "listpages":"每页数量",
    }]
    "log":[{//操作日志
      "list":[{//授权日志
        "id":"授权日志ID",
        "aid":"授权ID",
        "date":"操作时间",
        "ip":"请求者IP",
        "detail":"请求者本身授权信息",
        "status":"1成功,0失败",
        "remarks":"错误原因",
        "id":"操作日志ID",
        "uid":"操作者ID",
        "username":"操作人",
        "description":"操作详细",
        "date":"操作时间",
        "ip":"操作者IP地址",
        "aid":"授权ID",
      }]
      "total":"总页数",
      "current_page":"当前页数",
      "sum":"总数",
      "listpages":"每页数量",
      "list":[{//操作日志
        "id":"授权日志ID",
        "aid":"授权ID",
        "date":"操作时间",
        "ip":"请求者IP",
        "detail":"请求者本身授权信息",
        "status":"1成功,0失败",
        "remarks":"错误原因",
        "id":"操作日志ID",
        "uid":"操作者ID",
        "username":"操作人",
        "description":"操作详细",
        "date":"操作时间",
        "ip":"操作者IP地址",
        "aid":"授权ID",
      }]
      "total":"总页数",
      "current_page":"当前页数",
      "sum":"总数",
      "listpages":"每页数量",
    }]
  }
}
```

### 授权列表 --  GET /admin/dcimauth

- controller: ``app\admin\controller\DcimAuthController::list``
- desc: 授权列表 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| listpages | 整型 | 非必填 | 50 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,version,hotfix_version,diver_sum,diver_count,create_date,next_date,lastdate,status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"授权ID",
      "name":"名称",
      "diver_sum":"授权数量",
      "diver_count":"已添加设备数量",
      "hardware":"服务器数量",
      "switch":"交换机数量",
      "hardware_pdu":"PDU设备数量",
      "create_date":"创建时间",
      "next_date":"到期时间",
      "ip":"绑定IP",
      "addition":"支持的附加服务",
      "license":"授权码",
      "remarks":"备注",
      "status":"状态",
      "version":"版本号",
      "authorize_type":"0专业版1普惠版",
      "price":"价格",
      "accused_num":"被控数量",
      "result":"更新结果",
      "max_accused":"最大被控数量",
      "addons":"特殊产品",
      "expansion":"拓展产品",
      "network_type":"0外网部署1内网部署",
      "basic_version":"基础版本",
      "hotfix_version":"热更新版本",
      "hotfix_push_time":"热更新推送时间",
      "hotfix_push_status":"0:允许推送1:禁止推送",
      "update_type":"0:非立刻更新1:立刻更新",
      "update_support_type":"0:beta1:稳定",
      "update_status":"0:不可更新1:可更新",
      "lmc_num":"裸金属LMC数量",
      "lc_num":"裸金属LC数量",
      "accused_ip":"被控IP",
      "hostid":"主机ID",
      "uid":"用户ID",
      "username":"用户名",
    }]
  }
}
```

### 停用授权列表 --  GET /admin/dcimauth/disabled

- controller: ``app\admin\controller\DcimAuthController::disabledList``
- desc: 停用授权列表 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| listpages | 整型 | 非必填 | 50 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,version,hotfix_version,diver_sum,diver_count,create_date,next_date,lastdate,status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"授权ID",
      "name":"名称",
      "diver_sum":"授权数量",
      "diver_count":"已添加设备数量",
      "hardware":"服务器数量",
      "switch":"交换机数量",
      "hardware_pdu":"PDU设备数量",
      "create_date":"创建时间",
      "next_date":"到期时间",
      "ip":"绑定IP",
      "addition":"支持的附加服务",
      "license":"授权码",
      "remarks":"备注",
      "status":"状态",
      "version":"版本号",
      "authorize_type":"0专业版1普惠版",
      "price":"价格",
      "accused_num":"被控数量",
      "result":"更新结果",
      "max_accused":"最大被控数量",
      "addons":"特殊产品",
      "expansion":"拓展产品",
      "network_type":"0外网部署1内网部署",
      "basic_version":"基础版本",
      "hotfix_version":"热更新版本",
      "hotfix_push_time":"热更新推送时间",
      "hotfix_push_status":"0:允许推送1:禁止推送",
      "update_type":"0:非立刻更新1:立刻更新",
      "update_support_type":"0:beta1:稳定",
      "update_status":"0:不可更新1:可更新",
      "lmc_num":"裸金属LMC数量",
      "lc_num":"裸金属LC数量",
      "accused_ip":"被控IP",
      "hostid":"主机ID",
      "uid":"用户ID",
      "username":"用户名",
    }]
  }
}
```

### 错误日志列表 --  GET /admin/dcimauth/errorLog

- controller: ``app\admin\controller\DcimAuthController::errorLogList``
- desc: 错误日志列表 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| listpages | 整型 | 非必填 | 50 | - | 每页条数 |
| orderby | 字符串 | 非必填 | 日期 | - | 排序(date) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"授权日志ID",
      "aid":"授权ID",
      "date":"操作时间",
      "ip":"请求者IP",
      "detail":"请求者本身授权信息",
      "status":"1成功,0失败",
      "remarks":"错误原因",
    }]
  }
}
```

### DEBUG信息解密 --  POST /admin/dcimauth/debug

- controller: ``app\admin\controller\DcimAuthController::debugDecrypt``
- desc: DEBUG信息解密 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| data | 字符串 | 必填 | - | - | DEBUG信息 |
| type | 整型 | 必填 | - | - | 解密方式(0:解密web信息1:解密全部) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### DEBUG日志列表 --  GET /admin/dcimauth/debugLog

- controller: ``app\admin\controller\DcimAuthController::debugLogList``
- desc: DEBUG日志列表 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | id | - | 排序(id,description,create_date) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| search | 字符串 | 非必填 | - | - | 搜索 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":[{//列表数据
      "id":"DEBUG日志ID",
      "admin":"操作人",
      "date":"操作时间",
      "ip":"操作IP",
      "description":"结果详情",
      "create_date":"添加时间",
    }]
  }
}
```


---

## 应用商店

### 生成token --  GET /admin/app_store/set_token

- controller: ``app\admin\controller\AppStoreController::setToken``
- desc: 生成token -- xujin

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 校验token --  GET /admin/app_store/check_token

- controller: ``app\admin\controller\AppStoreController::checkToken``
- desc: 校验token -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| token | 字符串 | 必填 | - | - | 密钥 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取已购买应用最新版本 --  GET /admin/app_store/new_version

- controller: ``app\admin\controller\AppStoreController::getNewVersion``
- desc: 获取已购买应用最新版本 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| token | 字符串 | 必填 | - | - | 密钥 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 安装应用 --  POST /admin/app_store/app/:id/install

- controller: ``app\admin\controller\AppStoreController::install``
- desc: 安装应用 -- xujin

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 高级可配置项

### 高级配置页面 -- GET /admin/advanced_options/page

- controller: ``app\admin\controller\AdvancedOptionsController::page``
- desc: 高级配置页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 必填 | 1 | - | 产品ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "options":[{//配置项
      "id":"id",
      "option_name":"名称",
      "option_type":"类型:这里可能需要根据类型判断显示,具体可以问问黄",
      "sub_options":[{//子项
        "id":"子项id",
        "option_name":"子项名称",
        "qty_minimum":"最小值(仅类型为数量使用)",
        "qty_maximum":"最大值",
      }]
    }]
    "link":[{//已关联高级配置
      "id":"条件id,对应创建编辑的->条件关联id",
      "config_id":"条件配置项id",
      "relation":"条件关系:eq相等，neq不相等(前端直接显示传，后端不返回)",
      "sub_id":[{//条件子项id,返回数组
        "qty_minimum":"子项最小值",
        "qty_maximum":"子项最大值",
      }]
      "result":[{//结果数据
        "id":"结果id,对应创建编辑的->结果关联id",
        "config_id":"结果配置项id",
        "relation":"结果关系",
        "sub_id":"结果子项id,返回数组",
      }]
    }]
  }
}
```

### 高级配置创建、编辑 -- POST /admin/advanced_options/create

- controller: ``app\admin\controller\AdvancedOptionsController::create``
- desc: 高级配置创建、编辑 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| link[条件关联id][config_id] | 数组 | 非必填 | 1 | - | 配置项id,以下link参数(非必填都是表示参数都可以不传) |
| link[条件关联id][relation] | 数组 | 非必填 | 1 | - | 条件 |
| link[条件关联id][sub_id][子项ID][qty_minimum] | 数组 | 非必填 | 1 | - | 子项最小值 |
| link[条件关联id][sub_id][子项ID][qty_maximum] | 数组 | 非必填 | 1 | - | 子项最大值 |
| link[条件关联id][result][结果关联id][config_id] | 数组 | 非必填 | 1 | - | 结果 |
| link[条件关联id][result][结果关联id][relation] | 数组 | 非必填 | 1 | - | 结果 |
| link[条件关联id][result][结果关联id][sub_id][子项id][qty_minimum] | 数组 | 非必填 | 1 | - | 结果 |
| link[条件关联id][new_result][0][config_id] | 数组 | 非必填 | 1 | - | - |
| link[条件关联id][new_result][0][relation] | 数组 | 非必填 | 1 | - | - |
| link[条件关联id][new_result][0][sub_id][子项id][qty_minimum] | 数组 | 非必填 | 1 | - | - |
| link[条件关联id][new_result][0][sub_id][子项id][qty_maximum] | 数组 | 非必填 | 1 | - | - |
| new_cid | 整型 | 非必填 | 1 | - | 新增配置项ID |
| new_relation | 字符串 | 非必填 | 1 | - | 条件 |
| new_subid[子项id][qty_minimum] | 数组 | 非必填 | 1 | - | 子项最小值,非数量传0 |
| new_subid[子项id][qty_maximum] | 数组 | 非必填 | 1 | - | 子项最大值,非数量传0 |
| result[0开始的自然数][new_cid] | 数组 | 非必填 | 1 | - | 新增 |
| result[0][new_relation] | 数组 | 非必填 | 1 | - | 新增 |
| result[0][new_subid][子项ID][qty_minimum] | 数组 | 非必填 | 1 | - | 新增 |
| result[0][new_subid][子项ID][qty_maximum] | 数组 | 非必填 | 1 | - | 新增 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除条件 -- DELETE /admin/advanced_options/deletecondition

- controller: ``app\admin\controller\AdvancedOptionsController::deleteCondition``
- desc: 删除条件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 条件ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除结果 -- DELETE /admin/advanced_options/deleteresult

- controller: ``app\admin\controller\AdvancedOptionsController::deleteResult``
- desc: 删除结果 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 结果id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加条件 -- POST /admin/advanced_options/addcondition

- controller: ``app\admin\controller\AdvancedOptionsController::addCondition``
- desc: 添加条件 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| config_id | 整型 | 必填 | 1 | - | 配置项id |
| relation | 字符串 | 必填 | 1 | - | 条件关系 |
| sub_id[子项id][qty_minimum] | 数组 | 必填 | 1 | - | 子项最小值,非数量传0 |
| sub_id[子项id][qty_maximum] | 数组 | 必填 | 1 | - | 子项最大值 |
| result[0][config_id] | 数组 | 必填 | 1 | - | 结果 |
| result[0][relation] | 数组 | 必填 | 1 | - | 结果 |
| result[0][sub_id][子项id][qty_minimum] | 数组 | 必填 | 1 | - | 结果 |
| result[0][sub_id][子项id][qty_maximum] | 数组 | 必填 | 1 | - | 结果 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 添加结果 -- POST /admin/advanced_options/addresult

- controller: ``app\admin\controller\AdvancedOptionsController::addResult``
- desc: 添加结果 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 条件id |
| config_id | 整型 | 必填 | 1 | - | 配置项id |
| relation | 字符串 | 必填 | 1 | - | 条件关系 |
| sub_id[子项id][qty_minimum] | 整型 | 必填 | 1 | - | 子项最小值,非数量传0 |
| sub_id[子项id][qty_maximum] | 整型 | 必填 | 1 | - | 子项最大值 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 客户等级

### 客户等级列表 -- GET /admin/user_level/list

- controller: ``app\admin\controller\UserLevelController::getList``
- desc: 客户等级列表 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"客户总数",
    "list":[{//客户等级列表
      "id":"",
      "level_name":"客户等级名称",
      "expense":"min最小值,max最大值(下同)，day天",
      "buy_num":"",
      "login_times":"",
      "last_login_times":"",
      "renew_times":"",
      "last_renew_times":"",
    }]
  }
}
```

### 编辑规则页面 -- GET /admin/user_level/levelpage

- controller: ``app\admin\controller\UserLevelController::getLevelPage``
- desc: 编辑规则页面 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 规则ID(非必传参数,编辑时才传) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "level_name":"客户等级",
    "expense":"min最小值，max最大值",
    "buy_num":"min最小值，max最大值",
    "login_times":"min最小值，max最大值",
    "last_login_times":"min最小值，max最大值,day天数",
    "renew_times":"min最小值，max最大值",
    "last_renew_times":"min最小值，max最大值,day天数",
  }
}
```

### 创建/编辑规则 -- POST /admin/user_level/level

- controller: ``app\admin\controller\UserLevelController::postLevel``
- desc: 创建/编辑规则 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | 1 | - | 规则ID(非必传参数,编辑时才传) |
| level_name | 字符串 | 必填 | 1 | - | 等级名称 |
| expense_min | 整型 | 非必填 | 1 | - | 支出最小值 |
| expense_max | 整型 | 非必填 | 1 | - | 支出最大值 |
| buy_num_min | 整型 | 非必填 | 1 | - | 购买商品数量最小值 |
| buy_num_max | 整型 | 非必填 | 1 | - | 购买商品数量最大值 |
| login_times_min | 整型 | 非必填 | 1 | - | 累计登陆次数最小值 |
| login_times_max | 整型 | 非必填 | 1 | - | 累计登陆次数最大值 |
| last_login_times_min | 整型 | 非必填 | 1 | - | 最近X天登陆次数 |
| last_login_times_max | 整型 | 非必填 | 1 | - | 最近X天登陆次数 |
| last_login_times_day | 整型 | 非必填 | 1 | - | 最近X天登陆次数 |
| renew_times_min | 整型 | 非必填 | 1 | - | 续费次数最小值 |
| renew_times_max | 整型 | 非必填 | 1 | - | 续费次数最大值 |
| last_renew_times_min | 整型 | 非必填 | 1 | - | 最近X天续费次数 |
| last_renew_times_max | 整型 | 非必填 | 1 | - | 最近X天续费次数 |
| last_renew_times_day | 整型 | 非必填 | 1 | - | 最近X天续费次数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"客户总数",
  }
}
```

### 删除规则 -- DELETE /admin/user_level/level

- controller: ``app\admin\controller\UserLevelController::deleteLevel``
- desc: 删除规则 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 规则ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取销售员客户ids

- controller: ``app\admin\controller\UserLevelController::getAdminSale``
- desc: 获取销售员客户ids -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员 不可以查看sale_id为空的

- controller: ``app\admin\controller\UserLevelController::check``
- desc: 检查当前用户是否为销售员 不可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检查当前用户是否为销售员  可以查看sale_id为空的

- controller: ``app\admin\controller\UserLevelController::check1``
- desc: 检查当前用户是否为销售员  可以查看sale_id为空的 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计

- controller: ``app\admin\controller\UserLevelController::getLadder``
- desc: 获取当前销售员的阶级统计 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取当前销售员的阶级统计(所有)

- controller: ``app\admin\controller\UserLevelController::getLadderforall``
- desc: 获取当前销售员的阶级统计(所有) -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩

- controller: ``app\admin\controller\UserLevelController::getLaddersaleStatistics``
- desc: 当前销售员时间周期获取业绩 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 当前销售员时间周期获取业绩；仅统计总金额

- controller: ``app\admin\controller\UserLevelController::getLaddersaleStatisticsOnlyTotalAccount``
- desc: 当前销售员时间周期获取业绩；仅统计总金额 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取提成总额

- controller: ``app\admin\controller\UserLevelController::getSum``
- desc: 获取提成总额 -- 刘国栋

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台 用户信用额管理

### 用户信用额列表 -- GET /admin/credit_limit

- controller: ``app\admin\controller\CreditLimitController::index``
- desc: 用户信用额列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
  }
}
```

### 调整记录 -- GET /admin/credit_limit/log

- controller: ``app\admin\controller\CreditLimitController::log``
- desc: 调整记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | - |
| page | 整型 | 非必填 | - | - | 起始页 |
| size | 整型 | 非必填 | - | - | 长度 |
| type | 数组 | 非必填 | - | - | 类型：Change |
| keywords | 字符串 | 非必填 | - | - | 描述 |
| start_time | 整型 | 非必填 | - | - | 开始时间 |
| end_time | 整型 | 非必填 | - | - | 结束时间 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".username":"用户名",
  }
}
```

### 创建信用额 -- POST /admin/credit_limit

- controller: ``app\admin\controller\CreditLimitController::save``
- desc: 创建信用额 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| credit_limit | 整型 | 必填 | - | - | 额度 |
| bill_generation_date | 整型 | 必填 | - | - | 账单生成日 |
| bill_repayment_period | 整型 | 必填 | - | - | 还款期限 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改信用额 -- PUT /admin/credit_limit

- controller: ``app\admin\controller\CreditLimitController::update``
- desc: 修改信用额 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |
| credit_limit | 整型 | 必填 | - | - | 额度 |
| bill_generation_date | 整型 | 必填 | - | - | 账单生成日 |
| bill_repayment_period | 整型 | 必填 | - | - | 还款期限 |
| repayment_date | 整型 | 必填 | - | - | 还款日 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关闭信用额 -- DELETE /admin/credit_limit

- controller: ``app\admin\controller\CreditLimitController::delete``
- desc: 关闭信用额 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 用户id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户信用额使用记录 -- GET /admin/credit_limit/list

- controller: ``app\admin\controller\CreditLimitController::list``
- desc: 用户信用额使用记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 用户账单列表 -- GET /admin/credit_limit/user_invoice

- controller: ``app\admin\controller\CreditLimitController::userInvoice``
- desc: 用户账单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | - | - | 用户ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 信用额账单列表 -- GET /admin/credit_limit/user_invoice_detail

- controller: ``app\admin\controller\CreditLimitController::creditLimitInvoice``
- desc: 信用额账单列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| invoice_id | 整型 | 非必填 | - | - | 账单ID |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 页长 |
| order | mix | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | 排序desc/asc |
| payment | 字符串 | 非必填 | - | - | 按付款方式搜索 |
| status | 字符串 | 非必填 | - | - | 按支付状态搜索 |
| create_time | 字符串 | 非必填 | - | - | 按账单生成日搜索 |
| due_time | 字符串 | 非必填 | - | - | 按账单逾期日搜索 |
| paid_time | 字符串 | 非必填 | - | - | 按账单支付日搜索 |
| subtotal_small | 字符串 | 非必填 | - | - | 按总计搜索(小值) |
| subtotal_big | 字符串 | 非必填 | - | - | 按总计搜索(大值) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//账单列表
      "id":"账单IDcreate_time:账单生成日due_time:账单逾期日paid_time:账单支付日subtotal:总计payment:付款方式status:状态(Paid:已支付,Unpaid:未支付,Draft:已草稿,Overdue:已逾期,Cancelled:被取消,Refunded:已退款,Collections:已收藏)",
    }]
  }
}
```

### 客户列表 -- GET /admin/credit_limit/client_list

- controller: ``app\admin\controller\CreditLimitController::clientList``
- desc: 客户列表 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |
| keywords | 字符串 | 非必填 | - | - | 搜索关键字(非必传参数) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total":"客户总数",
    "list":[{//客户列表数据
      "id":"客户ID",
      "username":"客户用户名",
      "phonenumber":"手机号",
      "email":"邮件",
    }]
  }
}
```

### 信用额设置页面 -- GET /admin/credit_limit/config

- controller: ``app\admin\controller\CreditLimitController::getConfig``
- desc: 信用额设置页面 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".shd_credit_limit":"信用额总开关,1开启,0关闭",
    ".shd_credit_limit_amount":"信用额额度设置",
    ".shd_credit_limit_bill_generation_date":"出账日",
    ".shd_credit_limit_bill_repayment_period":"最后还款日",
    ".shd_credit_limit_liquidated_damages":"违约金总开关",
    ".shd_credit_limit_liquidated_damages_percent":"单日违约金百分比",
  }
}
```

### 信用额设置页面提交 -- POST /admin/credit_limit/config

- controller: ``app\admin\controller\CreditLimitController::postConfig``
- desc: 信用额设置页面提交 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| shd_credit_limit | 整型 | 必填 | 1 | - | 信用额总开关,1开启,0关闭 |
| shd_credit_limit_amount | 浮点型 | 必填 | 1 | - | 信用额额度设置 |
| shd_credit_limit_bill_generation_date | 整型 | 必填 | 1 | - | 出账日 |
| shd_credit_limit_bill_repayment_period | 整型 | 必填 | 1 | - | 最后还款日 |
| shd_credit_limit_liquidated_damages | 整型 | 必填 | 1 | - | 违约金总开关,1开启,0关闭 |
| shd_credit_limit_liquidated_damages_percent | 浮点型 | 必填 | 1 | - | 单日违约金百分比 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 后台设置三方登录

### 所有三方登录 -- GET /admin/oauth

- controller: ``app\admin\controller\OauthController::listing``
- desc: 所有三方登录 -- xionglingyuan

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".id":"id",
    ".status":"'状态;1开启;0禁用,3未安装',",
    ".name":"'插件标识名,英文字母(惟一)',",
    ".title":"名称",
    ".description":"描述",
    ".module":"所属模块",
    ".img":"图片",
  }
}
```

### 激活三方登录 -- POST /admin/oauth/active

- controller: ``app\admin\controller\OauthController::active``
- desc: 激活三方登录 -- xionglingyuan

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| dirName | 字符串 | 必填 | - | - | 三方登录模块目录名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 三方登录接口配置信息 -- GET /admin/oauth/config

- controller: ``app\admin\controller\OauthController::config``
- desc: 三方登录接口配置信息 -- xionglingyuan

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 保存三方登录接口参数 -- POST /admin/oauth/config_post

- controller: ``app\admin\controller\OauthController::configSave``
- desc: 保存三方登录接口参数 -- xionglingyuan

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| dirName | 字符串 | 必填 | - | - | 三方登录名称 |
| app[] | 数组 | 必填 | - | - | 三方登录接口参数一维数组，接口配置都提交过来 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 停用 -- POST /admin/oauth/suspend

- controller: ``app\admin\controller\OauthController::suspend``
- desc: 停用 -- xionglingyuan

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| dirName | 字符串 | 必填 | - | - | 三方登录名称 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台三方登录接口

### 三方登录 -- GET /oauth

- controller: ``app\home\controller\OauthController::listing``
- desc: 三方登录 -- xionglingyuan

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".url":"'跳转地址',",
    ".img":"'三方logo图片',",
    ".name":"'模块名称',",
  }
}
```

### 回调地址 -- GET /oauth/callbackInfo

- controller: ``app\home\controller\OauthController::callbackInfo``
- desc: 回调地址 -- xionglingyuan

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".callbackBind":"'0邮箱和手机号任选绑定，1输入手机号绑定，2输入邮箱绑定',",
  }
}
```

### 邮箱登录绑定 -- POST /oauth/bind_login_email

- controller: ``app\home\controller\OauthController::bindLoginEmail``
- desc: 邮箱登录绑定 -- xionglingyuan

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | - | - | 邮箱 |
| code | 字符串 | 必填 | - | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".oauthStatus":"'invalid,绑定信息失效的时候跳转到登录页',",
  }
}
```

### 手机登录绑定 -- POST /oauth/bind_login_phone

- controller: ``app\home\controller\OauthController::bindLoginPhone``
- desc: 手机登录绑定 -- xionglingyuan

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 非必填 | - | - | 手机号区号 |
| phone | 字符串 | 必填 | - | - | 手机号 |
| code | 字符串 | 必填 | - | - | 验证码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".oauthStatus":"'invalid,绑定信息失效的时候跳转到登录页',",
  }
}
```

### 邮箱绑定--验证码发送 -- POST /oauth/bind_email_send

- controller: ``app\home\controller\OauthController::bindEmailSend``
- desc: 邮箱绑定--验证码发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| email | 字符串 | 必填 | 1 | - | 邮箱 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 短信绑定--验证码发送 -- POST /oauth/bind_phone_send

- controller: ``app\home\controller\OauthController::bindPhoneSend``
- desc: 短信绑定--验证码发送 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| phone_code | 字符串 | 必填 | 1 | - | 国际手机区号 |
| phone | 字符串 | 必填 | 1 | - | 手机号 |
| mk | 字符串 | 必填 | - | - | common_list接口返回的msfntk作为cookie写入,并在发送短信时作为token传入 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\OauthController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\OauthController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\OauthController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\OauthController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 绑定、解绑三方登录

### 所有三方登录 -- GET /oauthBind

- controller: ``app\home\controller\OauthBindController::listing``
- desc: 所有三方登录 -- xionglingyuan

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    ".dirName":"'模块目录名称',",
    ".img":"'三方logo图片',",
    ".name":"'模块名称',",
    ".oauth":"'bind已经绑定，unbind未绑定',",
    ".username":"'已绑定用户的昵称',",
    ".url":"'绑定授权地址',",
  }
}
```

### 解绑三方账号 -- POST oauthBind/untie/[:dirName]

- controller: ``app\home\controller\OauthBindController::untie``
- desc: 解绑三方账号 -- xionglingyuan

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\OauthBindController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\OauthBindController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\OauthBindController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\OauthBindController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台应用商店

### 获取用户全部应用信息 -- GET /market/app_version

- controller: ``app\home\controller\MarketPublicController::getAppVersion``
- desc: 获取用户全部应用信息 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用户全部应用信息 -- GET /market/apps

- controller: ``app\home\controller\MarketPublicController::getApps``
- desc: 获取用户全部应用信息 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用开通重试 -- POST /market/app/recreate

- controller: ``app\home\controller\MarketPublicController::appRecreate``
- desc: 应用开通重试 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 主机id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用户收藏应用 -- GET /market/favorite

- controller: ``app\home\controller\MarketPublicController::favoriteApp``
- desc: 获取用户收藏应用 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收藏夹添加应用 -- POST /market/favorite/app/:id

- controller: ``app\home\controller\MarketPublicController::favoriteAppAdd``
- desc: 收藏夹添加应用 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 收藏夹移除应用 -- DELETE /market/favorite/app/:id

- controller: ``app\home\controller\MarketPublicController::favoriteAppDel``
- desc: 收藏夹移除应用 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 官网应用详情页 -- GET /market/app_detail

- controller: ``app\home\controller\MarketPublicController::getDeveloperAppDetail``
- desc: 官网应用详情页 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | - | - | 应用ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "product":[{//应用信息(编辑时才有)
      "name":"应用名称info:应用简述type:应用类型description:应用描述instruction:应用说明icon:应用图标pay_type:销售方式pricing:销售价格unretired_time:发布时间",
    }]
    "currency":"货币",
    "product_type":"应用类型--所有",
    "developer":[{//开发者信息
      "name":"开发者昵称desc:简介",
    }]
    "relation_app":"应用作者更多应用，与应用列表一样",
  }
}
```

### 应用商店（客户后台 应用） -- GET /market/market_app

- controller: ``app\home\controller\MarketPublicController::getMarketApp``
- desc: 应用商店（客户后台 应用） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用商店（应用） -- GET /market/applist

- controller: ``app\home\controller\MarketPublicController::getAppList``
- desc: 应用商店（应用） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用商店（服务） -- GET /market/servicelist

- controller: ``app\home\controller\MarketPublicController::getServiceList``
- desc: 应用商店（服务） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用商店（主题） -- GET /market/templatelist

- controller: ``app\home\controller\MarketPublicController::getTemplateList``
- desc: 应用商店（主题） -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 推荐作者 -- GET /market/recommend_developer

- controller: ``app\home\controller\MarketPublicController::getRecommendDeveloper``
- desc: 推荐作者 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者资料 -- GET /market/developer/:id

- controller: ``app\home\controller\MarketPublicController::getDeveloper``
- desc: 开发者资料 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| order | 字符串 | 非必填 | - | - | 排序字段 |
| sort | 字符串 | 非必填 | - | - | - |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用评论 -- GET /market/app/:id/evaluation

- controller: ``app\home\controller\MarketPublicController::appEvaluation``
- desc: 应用评论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| score | 字符串 | 非必填 | - | - | 查询分数(1,2,3,4,5) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取应用安装域名 -- GET /market/app/install

- controller: ``app\home\controller\MarketPublicController::getAppInstall``
- desc: 获取应用安装域名 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用安装检查 -- GET /market/app/install_check

- controller: ``app\home\controller\MarketPublicController::appInstallCheck``
- desc: 应用安装检查 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 非必填 | - | - | 主机id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用安装检测地址 -- GET /market/app/install_check_url

- controller: ``app\home\controller\MarketPublicController::appInstallCheckUrl``
- desc: 应用安装检测地址 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| hid | 整型 | 非必填 | - | - | 主机id |
| adminAddress | 字符串 | 非必填 | - | - | 网站后台地址 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 应用安装 -- POST /market/app/install

- controller: ``app\home\controller\MarketPublicController::appInstall``
- desc: 应用安装 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| pid | 整型 | 非必填 | - | - | 应用id |
| hid | 整型 | 非必填 | - | - | 主机id |
| adminAddress | 字符串 | 非必填 | - | - | 网站后台地址 |
| password | 字符串 | 非必填 | - | - | 密码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求讨论 -- GET /market/demand

- controller: ``app\home\controller\MarketPublicController::getDemand``
- desc: 需求讨论 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| type | 字符串 | 非必填 | - | - | 类型(finance,cloud,dcim) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求内页 -- GET /market/demand/:id

- controller: ``app\home\controller\MarketPublicController::getDemandDetail``
- desc: 需求内页 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 发布需求 -- POST /market/demand

- controller: ``app\home\controller\MarketPublicController::postDemand``
- desc: 发布需求 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | - | - | 标题 |
| classify | 字符串 | 必填 | - | - | 分类 |
| content | 字符串 | 必填 | - | - | 内容 |
| cash | 浮点型 | 非必填 | - | - | 现金 |
| integral | 字符串 | 非必填 | - | - | 魔币 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求补充说明 --  PUT /market/demand/:id

- controller: ``app\home\controller\MarketPublicController::putDemand``
- desc: 需求补充说明 -- xujin

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | 0 | - | 标题 |
| content | 字符串 | 必填 | 0 | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求投票 -- POST /market/demand/:id/vote

- controller: ``app\home\controller\MarketPublicController::postDemandVote``
- desc: 需求投票 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |
| demand_vote | 整型 | 必填 | - | - | 0打酱油,1支持开发,2无用功能 |
| cash | 浮点型 | 必填 | - | - | 现金 |
| integral | 整型 | 必填 | - | - | 魔币 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者报名 -- POST /market/demand/:id/sign_up

- controller: ``app\home\controller\MarketPublicController::demandSignUp``
- desc: 开发者报名 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开发者提交作品 -- POST /market/demand/:id/submit_product

- controller: ``app\home\controller\MarketPublicController::demandSubmitProduct``
- desc: 开发者提交作品 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| productid | 整型 | 必填 | - | - | 应用ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 选中开发者 -- POST /market/demand/:id/choose_developer

- controller: ``app\home\controller\MarketPublicController::demandChooseDeveloper``
- desc: 选中开发者 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| developer_uid | 整型 | 必填 | - | - | 开发者uid |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 需求回复 -- GET /market/demand/:id/reply

- controller: ``app\home\controller\MarketPublicController::demandReply``
- desc: 需求回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "reply":[{//回复列表
      "id":":回复IDuid:用户IDcash:现金integral:魔币content:内容username:用户名like_num:点赞数量create_time:回复时间reply_count:回复的回复数量",
    }]
  }
}
```

### 需求报名开发者回复 -- GET /market/demand/:id/developer_reply

- controller: ``app\home\controller\MarketPublicController::demandDeveloperReply``
- desc: 需求报名开发者回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "reply":[{//回复列表
      "id":":回复IDuid:用户IDproductid:上架作品IDcontent:内容username:用户名like_num:点赞数量create_time:回复时间reply_count:回复的回复数量",
    }]
  }
}
```

### 打赏 -- POST /market/app/:id/reward

- controller: ``app\home\controller\MarketPublicController::reward``
- desc: 打赏 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| amount | 整型 | 必填 | - | - | 打赏金额 |
| remarks | 字符串 | 非必填 | - | - | 备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 举报应用 -- POST /market/app/:id/report

- controller: ``app\home\controller\MarketPublicController::appReport``
- desc: 举报应用 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 举报通知 -- GET /market/report

- controller: ``app\home\controller\MarketPublicController::getReport``
- desc: 举报通知 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 确认收货 -- POST /market/app/sign_for

- controller: ``app\home\controller\MarketPublicController::appSignFor``
- desc: 确认收货 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| amount | 整型 | 必填 | - | - | 打赏金额 |
| amount | 整型 | 必填 | - | - | 打赏金额 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG -- GET /market/bugs

- controller: ``app\home\controller\MarketPublicController::getBugs``
- desc: BUG -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| classify | 字符串 | 非必填 | - | - | 类型(finance,cloud,dcim,other) |
| status | 字符串 | 非必填 | - | - | 状态 |
| keywords | 字符串 | 非必填 | - | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "status":"状态",
    "classify":"分类",
    "bug":[{//BUG列表
      "id":"BUG",
      "IDuid":"用户IDstatus_zh:状态classify_zh:分类is_bug:是BUGnot_bug:不是BUGtitle:标题username:用户名encounter_num:我也遇到数量views:浏览量is_encounter:我也遇到0未标记1已标记create_time:创建时间",
    }]
  }
}
```

### BUG内页 -- GET /market/bug/:id

- controller: ``app\home\controller\MarketPublicController::getBug``
- desc: BUG内页 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "status":"状态",
    "classify":"分类",
    "bug":[{//BUG
      "id":"BUG",
      "IDuid":"用户IDstatus_zh:状态classify_zh:分类title:标题content:内容username:用户名views:浏览量create_time:创建时间",
    }]
  }
}
```

### 发布BUG -- POST /market/bug

- controller: ``app\home\controller\MarketPublicController::postBug``
- desc: 发布BUG -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | - | - | 标题 |
| classify | 字符串 | 必填 | - | - | 分类dcim魔方DCIM,finance魔方财务,cloud魔方云,other其他 |
| content | 字符串 | 必填 | - | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG补充说明 -- PUT /market/bug/:id

- controller: ``app\home\controller\MarketPublicController::putBug``
- desc: BUG补充说明 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | - | - | 标题 |
| content | 字符串 | 必填 | - | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG回复 -- GET /market/bug/:id/reply

- controller: ``app\home\controller\MarketPublicController::bugReply``
- desc: BUG回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "reply":[{//回复列表
      "id":":回复IDuid:用户IDcontent:内容username:用户名like_num:点赞数量bug_vote:BUG投票1:是BUG2不是BUGbug_fix:BUG修复投票1:已修复create_time:回复时间reply_count:回复的回复数量",
    }]
  }
}
```

### BUG回复 -- POST /market/bug/:id/reply

- controller: ``app\home\controller\MarketPublicController::postBugReply``
- desc: BUG回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG投票 -- POST /market/bug/:id/vote

- controller: ``app\home\controller\MarketPublicController::postBugVote``
- desc: BUG投票 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |
| bug_vote | 整型 | 必填 | - | - | 1是BUG,2不是BUG |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG已修复投票 -- POST /market/bug/:id/fix

- controller: ``app\home\controller\MarketPublicController::postBugFix``
- desc: BUG已修复投票 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### BUG标记我也遇到 -- PUT /market/bug/:id/encounter

- controller: ``app\home\controller\MarketPublicController::putBugEncounter``
- desc: BUG标记我也遇到 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 互助 -- GET /market/helps

- controller: ``app\home\controller\MarketPublicController::getHelps``
- desc: 互助 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |
| classify | 字符串 | 非必填 | - | - | 类型(finance,cloud,dcim,other) |
| status | 字符串 | 非必填 | - | - | 状态 |
| keywords | 字符串 | 非必填 | - | - | 关键字 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"总数",
    "status":"状态",
    "classify":"分类",
    "type":"类型",
    "help":[{//互助列表
      "id":"互助IDuid:用户IDstatus_zh:状态classify_zh:分类type_zh:类型cash:现金integral:魔币title:标题username:用户名like_num:点赞数量views:浏览量create_time:创建时间",
    }]
  }
}
```

### 互助内页 -- GET /market/help/:id

- controller: ``app\home\controller\MarketPublicController::getHelp``
- desc: 互助内页 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "status":"状态",
    "classify":"分类",
    "type":"类型",
    "help":[{//互助
      "id":"互助IDuid:用户IDstatus_zh:状态classify_zh:分类type_zh:类型cash:现金integral:魔币title:标题content:内容username:用户名like_num:点赞数量views:浏览量create_time:创建时间",
    }]
  }
}
```

### 付费知识查看 -- GET /market/knowledge/:id/view

- controller: ``app\home\controller\MarketPublicController::knowledgeView``
- desc: 付费知识查看 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "status":"状态0未成功付费1成功付费",
    "invoice_id":"现金账单ID",
  }
}
```

### 发布互助 -- POST /market/help

- controller: ``app\home\controller\MarketPublicController::postHelp``
- desc: 发布互助 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | - | - | 标题 |
| classify | 字符串 | 必填 | - | - | 分类dcim魔方DCIM,finance魔方财务,cloud魔方云,other其他 |
| type | 字符串 | 必填 | - | - | 类型help求助,knowledge知识分享 |
| content | 字符串 | 必填 | - | - | 内容 |
| reward | 整型 | 必填 | - | - | 奖励0无1有 |
| pay | 整型 | 必填 | - | - | 付费0无1有 |
| cash | 浮点型 | 必填 | - | - | 现金 |
| integral | 整型 | 必填 | - | - | 魔币 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_id":"现金账单ID",
  }
}
```

### 求助补充说明 -- PUT /market/help/:id

- controller: ``app\home\controller\MarketPublicController::putHelp``
- desc: 求助补充说明 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| title | 字符串 | 必填 | - | - | 标题 |
| content | 字符串 | 必填 | - | - | 内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 求助回复 -- GET /market/help/:id/reply

- controller: ``app\home\controller\MarketPublicController::helpReply``
- desc: 求助回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "reply":[{//回复列表
      "id":":回复IDuid:用户IDcontent:内容username:用户名like_num:点赞数量choose:是否选中0否1是create_time:回复时间reply_count:回复的回复数量",
    }]
  }
}
```

### 知识分享回复 -- GET /market/knowledge/:id/reply

- controller: ``app\home\controller\MarketPublicController::knowledgeReply``
- desc: 知识分享回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time,score) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "reply":[{//回复列表
      "id":":回复IDuid:用户IDcash:现金integral:魔币content:内容username:用户名like_num:点赞数量choose:是否选中0否1是create_time:回复时间reply_count:回复的回复数量",
    }]
  }
}
```

### 求助回复 -- POST /market/help/:id/reply

- controller: ``app\home\controller\MarketPublicController::postHelpReply``
- desc: 求助回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |
| cash | 浮点型 | 非必填 | - | - | 打赏现金 |
| integral | 整型 | 非必填 | - | - | 打赏魔币 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_id":"现金账单ID",
  }
}
```

### 知识分享回复 -- POST /market/knowledge/:id/reply

- controller: ``app\home\controller\MarketPublicController::postKnowledgeReply``
- desc: 知识分享回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 回复内容 |
| cash | 浮点型 | 非必填 | - | - | 打赏现金 |
| integral | 整型 | 非必填 | - | - | 打赏魔币 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_id":"现金账单ID",
  }
}
```

### 求助选中回复 -- POST /market/help/:id/choose_reply

- controller: ``app\home\controller\MarketPublicController::helpChooseReply``
- desc: 求助选中回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| replyid | 整型 | 必填 | - | - | 回复ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 互助点赞 -- POST /market/help/:id/like

- controller: ``app\home\controller\MarketPublicController::helpLikeAdd``
- desc: 互助点赞 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 互助取消点赞 -- DELETE /market/help/:id/like

- controller: ``app\home\controller\MarketPublicController::helpLikeDel``
- desc: 互助取消点赞 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 互助点踩 -- POST /market/help/:id/unlike

- controller: ``app\home\controller\MarketPublicController::helpUnlikeAdd``
- desc: 互助点踩 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 互助取消点踩 -- DELETE /market/help/:id/unlike

- controller: ``app\home\controller\MarketPublicController::helpUnlikeDel``
- desc: 互助取消点踩 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 求助追加悬赏 -- POST /market/help/:id/reward

- controller: ``app\home\controller\MarketPublicController::postHelpReward``
- desc: 求助追加悬赏 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| cash | 浮点型 | 非必填 | - | - | 现金 |
| integral | 整型 | 非必填 | - | - | 魔币 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "invoice_id":"现金账单ID",
  }
}
```

### 魔币/现金增减记录 -- GET /market/integral/logs

- controller: ``app\home\controller\MarketPublicController::getIntegralLog``
- desc: 魔币/现金增减记录 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| orderby | 字符串 | 非必填 | create_time | - | 排序(create_time) |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "type":"类型",
    "count":"数量",
    "logs":[{//记录列表
      "id":":记录IDtype_zh:类型description:描述cash:现金integral:魔币cash_credit:现金余额integral_credit:魔币余额create_time:记录时间",
    }]
  }
}
```

### 获取评论回复 -- GET /market/evaluation/:id/reply

- controller: ``app\home\controller\MarketPublicController::getEvaluationReply``
- desc: 获取评论回复 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 非必填 | - | - | 页码 |
| limit | 整型 | 非必填 | - | - | 长度 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "count":"数量",
    "reply":[{//回复列表
      "id":"回复IDuid:用户IDusername:用户response:回复对象content:内容cash_credit:现金余额like_num:点赞数量create_time:回复时间",
    }]
  }
}
```

### 获取全局置顶帖子 -- GET /market/global_top

- controller: ``app\home\controller\MarketPublicController::getGlobalTop``
- desc: 获取全局置顶帖子 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 应用评论(后台)

### 评价应用 -- POST /admin/evaluation/app/:id

- controller: ``app\admin\controller\EvaluationController::appEvaluationAdd``
- desc: 评价应用 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |
| score | 字符串 | 必填 | - | - | 评分 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价需求 -- POST /admin/evaluation/demand/:id

- controller: ``app\admin\controller\EvaluationController::demandEvaluationAdd``
- desc: 评价需求 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除评价 -- DELETE /admin/evaluation/:id

- controller: ``app\admin\controller\EvaluationController::evaluationDel``
- desc: 删除评价 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 追加评价 -- POST /admin/evaluation/:id/evaluate

- controller: ``app\admin\controller\EvaluationController::evaluationEvaluate``
- desc: 追加评价 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价点赞 -- POST /admin/evaluation/:id/like

- controller: ``app\admin\controller\EvaluationController::evaluationLikeAdd``
- desc: 评价点赞 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价取消点赞 -- DELETE /admin/evaluation/:id/like

- controller: ``app\admin\controller\EvaluationController::evaluationLikeDel``
- desc: 评价取消点赞 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 应用评论

### 评价应用 -- POST evaluation/app/:id

- controller: ``app\home\controller\EvaluationController::appEvaluationAdd``
- desc: 评价应用 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |
| score | 字符串 | 必填 | - | - | 评分 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价需求 -- POST evaluation/demand/:id

- controller: ``app\home\controller\EvaluationController::demandEvaluationAdd``
- desc: 评价需求 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 修改评价 -- PUT evaluation/:id

- controller: ``app\home\controller\EvaluationController::putEvaluation``
- desc: 修改评价 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除评价 -- DELETE evaluation/:id

- controller: ``app\home\controller\EvaluationController::evaluationDel``
- desc: 删除评价 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 追加评价 -- POST evaluation/:id/evaluate

- controller: ``app\home\controller\EvaluationController::evaluationEvaluate``
- desc: 追加评价 -- xj

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| content | 字符串 | 必填 | - | - | 评论内容 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价点赞 -- POST evaluation/:id/like

- controller: ``app\home\controller\EvaluationController::evaluationLikeAdd``
- desc: 评价点赞 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 评价取消点赞 -- DELETE evaluation/:id/like

- controller: ``app\home\controller\EvaluationController::evaluationLikeDel``
- desc: 评价取消点赞 -- xj

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## 前台API管理

### 重置秘钥 -- POST /zjmf_finance_api/reset

- controller: ``app\home\controller\ZjmfFinanceApiController::resetApiPwd``
- desc: 重置秘钥 -- wyh

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 开启/关闭API功能 -- POST /zjmf_finance_api/open

- controller: ``app\home\controller\ZjmfFinanceApiController::apiOpen``
- desc: 开启/关闭API功能 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| api_open | 整型 | 必填 | - | - | 1开启 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### API概览 -- GET /zjmf_finance_api/summary

- controller: ``app\home\controller\ZjmfFinanceApiController::summary``
- desc: API概览 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 必填 | - | - | 客户ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "client":[{//基础数据
      "api_password":"api密钥api_create_time:开通时间agent_count:代理商品数量host_count:API产品数量",
      "active_count":"API产品数量",
      "api_count":"昨日api请求次数ratio:日环比up:1上升，0下降lock_reason:锁定原因api_lock_time:锁定时间",
    }]
    "form_api":"最近7天每天的api请求次数",
    "free_products":[{//豁免产品
      "id":"name:名称ontrial:试用数量qty:最大购买数量",
    }]
  }
}
```

### API日志 -- GET /zjmf_finance_api/logs

- controller: ``app\home\controller\ZjmfFinanceApiController::apiLog``
- desc: API日志 -- wyh

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| uid | 整型 | 非必填 | 1 | - | 客户ID，单个客户日志需要传 |
| page | 整型 | 非必填 | 1 | - | 页数 |
| limit | 整型 | 非必填 | 10 | - | 每页条数 |
| order | 字符串 | 非必填 | id | - | 排序(id,name,hostname,server_num,api_status) |
| sort | 字符串 | 非必填 | asc | - | 排序方向(asc,desc) |
| keyword | 整型 | 非必填 | - | - | 关键字查询 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "logs":[{//日志
    }]
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\ZjmfFinanceApiController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\ZjmfFinanceApiController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\ZjmfFinanceApiController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\ZjmfFinanceApiController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## bot前台

### 获取配置绑定设置 --  GET /interflow/accountbind

- controller: ``app\home\controller\InterflowController::interflowAccountBindInfo``
- desc: 获取配置绑定设置 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":"",
    ".id":"记录ID",
    ".i_type":"交互类型",
    ".uid":"用户ID",
    ".i_account":"交互账号",
    ".security_verify":"敏感开关",
    ".security_code":"敏感安全码",
    ".is_bind":"是否绑定",
    ".create_time":"创建时间",
    ".update_time":"最近修改时间",
  }
}
```

### 保存配置绑定设置 --  POST /interflow/accountbind

- controller: ``app\home\controller\InterflowController::interflowAccountBind``
- desc: 保存配置绑定设置 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i_data[Qq][id] | 字符串 | 必填 | - | - | 记录ID(无id是添加,有id是修改) |
| i_data[Qq][i_type] | 字符串 | 必填 | - | - | 交互类型 |
| i_data[Qq][i_account] | 整型 | 必填 | - | - | 交互账号 |
| i_data[Qq][security_verify] | 整型 | 必填 | - | - | 敏感开关 |
| i_data[Qq][security_code] | 字符串 | 必填 | - | - | 敏感安全码 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "json":"",
  }
}
```

### 解除账号绑定 --  DETELE /interflow/accountbind

- controller: ``app\home\controller\InterflowController::interflowAccountBindRelieve``
- desc: 解除账号绑定 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | - | - | 记录ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "":"",
  }
}
```

### 用户目录 -- GET /navindex

- controller: ``app\home\controller\InterflowController::index``
- desc: 用户目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户可添加目录 -- GET /addindex_page

- controller: ``app\home\controller\InterflowController::addindexPage``
- desc: 用户可添加目录 -- lgd

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data":[{//导航列表
      "id":"产品分类ID",
      "groupname":"产品分类name",
      "fa_icon":"图标",
    }]
  }
}
```

### 用户添加目录 -- POST /addindex_post

- controller: ``app\home\controller\InterflowController::addindexPost``
- desc: 用户添加目录 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 用户目录删除 -- POST /addindex_del

- controller: ``app\home\controller\InterflowController::addindexDel``
- desc: 用户目录删除 -- lgd

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 非必填 | - | - | 分类id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


---

## bot后台

### 模板自定义参数 -- GET /admin/interflow/templateParams

- controller: ``app\admin\controller\InterflowSetingController::templateParams``
- desc: 模板自定义参数 -- 请设置auhtor注释

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 接收设置 | 通知设置 -- GET /admin/interflow/seting

- controller: ``app\admin\controller\InterflowSetingController::seting``
- desc: 接收设置 | 通知设置 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| active | 整型 | 必填 | 0 | - | ‘空’接收|‘非空’通知 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "data_name":"接收设置 列表结构",
    "data_name.title":"标题",
    "data_name.is_open":"是否开启",
    "data_name.senior_security":"[高级]敏感验证开关",
    "data_name.senior_i_type":"[高级]敏感验证插件",
    "data_name.is_display_senior":"高级按钮是否展示",
    "data_name":"通知设置 列表结构",
    "data_name.title":"标题",
    "data_name.is_open":"是否开启",
    "data_name.template":"模板",
    "data_name.senior_i_type":"[高级]敏感验证插件",
    "data_name.is_display_senior":"高级按钮是否展示",
  }
}
```

### 接收设置 | 通知设置 -- POST /admin/interflow/seting

- controller: ``app\admin\controller\InterflowSetingController::setingPost``
- desc: 接收设置 | 通知设置 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| active | 整型 | 必填 | 0 | - | ‘空’接收|‘非空’通知 |
| - | - | 非必填 | - | - | - |
| config[i_reboot][title] | 字符串 | 必填 | 1 | - | 标题 |
| config[i_reboot][is_open] | 字符串 | 必填 | 1 | - | 是否开启 |
| config[i_reboot][senior_security] | 字符串 | 必填 | 1 | - | [高级]敏感验证开关 |
| config[i_reboot][senior_i_type] | 字符串 | 必填 | 1 | - | [高级]敏感验证插件 |
| config[i_reboot][is_display_senior] | 字符串 | 必填 | 1 | - | 高级按钮是否展示 |
| - | - | 非必填 | - | - | - |
| config[notice_i_renew][title] | 字符串 | 必填 | 1 | - | 标题 |
| config[notice_i_renew][is_open] | 字符串 | 必填 | 1 | - | 是否开启 |
| config[notice_i_renew][template] | 字符串 | 必填 | 1 | - | 模板 |
| config[notice_i_renew][senior_i_type] | 字符串 | 必填 | 1 | - | [高级]敏感验证插件 |
| config[notice_i_renew][is_display_senior] | 字符串 | 必填 | 1 | - | 高级按钮是否展示 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 获取用户列表 -- GET /admin/interflow/userMap

- controller: ``app\admin\controller\InterflowSetingController::userMap``
- desc: 获取用户列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total 总数数":"",
    "list 数据列表":"",
    "list.id":"用户id",
    "list.username":"用户名",
    "list.companyname":"公司",
    "list.phonenumber":"手机号",
    "list.email":"邮箱",
    "list.i_account绑定账号信息":"",
    "list.i_account.i_account":"账号",
    "list.i_account.is_bind":"是否绑定",
    "list.i_account.i_type":"设备类型",
    "list.execute_log最新指令":"",
    "list.execute_log.id":"设备类型",
    "list.execute_log.device":"设备类型",
    "list.execute_log.execute_message":"指令详细",
    "list.execute_log.account_bot":"设备账号",
    "list.execute_log.account_user":"用户账号",
    "list.execute_log.status":"状态",
    "list.execute_log.create_time":"时间",
  }
}
```

### 指令查询列表 -- GET /admin/interflow/executeMap

- controller: ``app\admin\controller\InterflowSetingController::executeMap``
- desc: 指令查询列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | id查询 |
| keywords | 字符串 | 必填 | 1 | - | 关键字查询 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "total 总数数":"",
    "list 数据列表":"",
    "list.id":"指令id",
    "list.device":"设备类型",
    "list.execute_message":"指令内容",
    "list.account_bot":"设备账号",
    "list.account_user":"用户账号",
    "list.status":"状态",
    "list.create_time":"生成时间",
    "list.message_log.id":"聊天记录id",
    "list.message_log.sender":"发送者",
    "list.message_log.receiver":"接收者",
    "list.message_log.message":"消息",
    "list.message_log.create_time":"发送时间",
  }
}
```

### 获取插件机器人账号列表 -- GET /admin/interflow/botAccount

- controller: ``app\admin\controller\InterflowSetingController::botAccount``
- desc: 获取插件机器人账号列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i_type | 字符串 | 必填 | 1 | - | :插件名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "List":"",
    "List.id":"",
    "List.i_type":"账号类型",
    "List.account":"账号",
    "List.description":"备注",
    "List.status":"账号状态",
  }
}
```

### 添加插件机器人账号 -- POST /admin/interflow/botAccountPost

- controller: ``app\admin\controller\InterflowSetingController::botAccountPost``
- desc: 添加插件机器人账号 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| i_type | 字符串 | 必填 | 1 | - | :插件名 |
| account | 字符串 | 必填 | 1 | - | :账号 |
| description | 字符串 | 必填 | 1 | - | :备注 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 删除插件机器人 -- POST /admin/interflow/botAccountDelete

- controller: ``app\admin\controller\InterflowSetingController::botAccountDelete``
- desc: 删除插件机器人 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | :账号ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 机器人账号登录 -- POST /admin/interflow/botAccountLogin

- controller: ``app\admin\controller\InterflowSetingController::botAccountLogin``
- desc: 机器人账号登录 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | :账号ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 机器人账号退出登录 -- POST /admin/interflow/botAccountLoginOut

- controller: ``app\admin\controller\InterflowSetingController::botAccountLoginOut``
- desc: 机器人账号退出登录 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | :账号ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 检测更新机器账号在线状态 -- POST /admin/interflow/botCheckStatus

- controller: ``app\admin\controller\InterflowSetingController::botCheckStatus``
- desc: 检测更新机器账号在线状态 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 字符串 | 必填 | 1 | - | :账号ID |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 已实现的所有方法 -- GET /admin/interflow/funcLoadList

- controller: ``app\admin\controller\InterflowSetingController::funcLoadList``
- desc: 已实现的所有方法 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 1 | - | system系统内置、developer开发者创建 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":"",
    "list.title":"方法标题",
    "list.description":"方法描述",
    "list.name":"方法名【使用字段】",
  }
}
```

### 已实现的方法的详情 -- GET /admin/interflow/funcLoadContent

- controller: ``app\admin\controller\InterflowSetingController::funcLoadContent``
- desc: 已实现的方法的详情 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| func_name | 字符串 | 必填 | 1 | - | 方法名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":"",
    "list.title":"方法标题",
    "list.description":"方法描述",
    "list.keyword":"关键字",
    "list.type":"类型",
    "list.matching_text":"匹配文本",
    "list.matching_reply":"匹配成功回复",
    "list.assembly":"方法需要组件",
    "list.name":"方法名",
    "list.param方法参数列表":"",
    "list.param.keyword":"参数关键字",
    "list.param.type":"参数类型",
    "list.param.matching_text":"参数匹配文本",
    "list.param.matching_text_preg":"参数匹配文本，对应的正则",
    "list.param.matching_reply":"匹配成功回复",
    "list.param.description":"参数描述",
  }
}
```

### 方法注册进入数据库 -- POST /admin/interflow/funcRegister

- controller: ``app\admin\controller\InterflowSetingController::funcRegister``
- desc: 方法注册进入数据库 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| func_name | 字符串 | 必填 | 1 | - | 方法名 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 已经注册的方法列表 -- GET /admin/interflow/funcRegisterList

- controller: ``app\admin\controller\InterflowSetingController::funcRegisterList``
- desc: 已经注册的方法列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| type | 字符串 | 必填 | 1 | - | system系统内置、developer开发者创建 |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC,DESC |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":"",
    "list.total":"总数量",
    "list.list":"方法描述",
    "list.list.id":"",
    "list.list.title":"方法标题",
    "list.list.func_name":"方法全名",
    "list.list.func_assembly":"需要方法组件",
    "list.list.func_desc":"方法描述",
    "list.list.create_time":"创建时间",
    "list.list.keyword_id":"方法包含关键词组",
    "list.list.keyword_info":"关键词组数据",
    "list.list.keyword_info.kid":"关键词组id",
    "list.list.keyword_info.title":"关键词组标题",
    "list.list.keyword_info.keyword":"关键词名",
  }
}
```

### 创建的关键字 -- POST /admin/interflow/keywordSave

- controller: ``app\admin\controller\InterflowSetingController::keywordSave``
- desc: 创建的关键字 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | 关键字ID（创建不需要.修改需要） |
| title | 整型 | 必填 | 1 | - | 标题 |
| keyword | 整型 | 必填 | 1 | - | 关键字 |
| type | 字符串 | 必填 | 10 | - | 类型 |
| matching_text | 整型 | 必填 | 10 | - | 匹配文本（全文匹配） |
| matching_text_preg | 整型 | 必填 | 10 | - | 匹配文本（正则匹配） |
| matching_reply | 整型 | 必填 | 10 | - | 匹配成功回复 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 已创建的关键字列表 -- GET /admin/interflow/keywordList

- controller: ``app\admin\controller\InterflowSetingController::keywordList``
- desc: 已创建的关键字列表 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| page | 整型 | 必填 | 1 | - | 第几页 |
| limit | 整型 | 必填 | 10 | - | 每页多少条 |
| order | 字符串 | 必填 | 10 | - | 排序字段 |
| sort | 整型 | 必填 | 10 | - | AESC, |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":"",
    "list.total":"总数量",
    "list.list":"方法数据",
    "list.list.id":"id数",
    "list.list.title":"标题",
    "list.list.keyword":"关键词名",
    "list.list.matching_text":"匹配文本（全文匹配）",
    "list.list.matching_text_preg":"匹配文本（正则匹配）",
    "list.list.type":"类型",
    "list.list.matching_reply":"匹配成功回复",
    "list.list.func_name":"方法全名",
    "list.list.create_time":"创建时间",
    "list.keyword_type":"关键词存在类型数组",
  }
}
```

### 执行器开启与关闭 -- POST /admin/interflow/keywordExecuteSwitch

- controller: ``app\admin\controller\InterflowSetingController::keywordExecuteSwitch``
- desc: 执行器开启与关闭 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | id |
| status | 整型 | 必填 | 1 | - | 状态 |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```

### 关键字详情 -- GET /admin/interflow/keywordInfo

- controller: ``app\admin\controller\InterflowSetingController::keywordInfo``
- desc: 关键字详情 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
    "list":"",
    "list.id":"id数",
    "list.title":"标题",
    "list.keyword":"关键词名",
    "list.matching_text":"匹配文本（全文匹配）",
    "list.matching_text_preg":"匹配文本（正则匹配）",
    "list.type":"类型",
    "list.matching_reply":"匹配成功回复",
    "list.func_name":"方法全名",
    "list.create_time":"创建时间",
  }
}
```

### 关键字删除 -- POST /admin/interflow/keywordDel

- controller: ``app\admin\controller\InterflowSetingController::keywordDel``
- desc: 关键字删除 -- 请设置auhtor注释

**params**

| name | type | required | default | other | desc |
| --- | --- | --- | --- | --- | --- |
| id | 整型 | 必填 | 1 | - | id |

**response example**

```json
{
  "status":200/201/203/204/400/401/406,
  "msg":提示信息,
  "data":{
  }
}
```


