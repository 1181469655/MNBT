# 售卖前端

统一商店前端皮肤插件：

1. **首页接管**：接管站点首页 `/`，渲染品牌落地页（展示站点标题、Logo、主色调、Hero 标语、套餐列表）。
2. **页面接管**：统一 `user_info`（登录/注册/资料/密码）、`balance`（余额/充值）、`hosting_shop`（套餐/下单/资产/订单）的全部**用户端页面**，提供一致化的现代化布局。
3. **共用导航**：首页与所有功能页共用同一个顶栏导航（`views/partials/navbar.php`），可在一处跳转全部板块（首页 / 主机套餐 / 我的主机 / 我的订单 / 我的余额 / 个人信息 / 登录注册），风格与主色完全统一。
4. 业务逻辑（登录鉴权、下单、充值、支付回调）仍由原插件处理，本插件只负责页面外壳与数据展示，不修改任何核心文件。

## 前置依赖

- `user_info` — 用户注册/登录（`/account/login`、`/account/register`、`/account/profile`、`/account/password`）
- `balance` — 余额管理（`/balance`、`/balance/recharge`）
- `hosting_shop` — 主机套餐/订单/资产管理（`/shop`、`/shop/order/{id}`、`/shop/assets`、`/shop/orders`）

## 实现原理

- 首页：`mnbt_register_home()` 接管 `/`。
- 页面：通过 `mnbt_register_route()` 以 **priority=5**（低于原插件默认 10）注册同名 GET 路由，
  先于原插件匹配并渲染统一页面；POST API 与支付回调 `/pay/*` 一律不接管。
- 管理端页面不接管。

## 目录结构

```
shop_frontend/
├── plugin.json              # 插件元信息
├── bootstrap.php            # 入口：首页接管 + 后台设置 + 页面接管路由
├── lib/shop_frontend.php    # 工具函数（配置读取、URL、用户/套餐查询、统一渲染）
├── assets/style.css         # 统一页面样式（主色跟随后台配置 --brand）
├── views/
│   ├── layout.php           # 功能页统一布局（顶栏/页脚，含 CSRF 自动注入）
│   ├── partials/navbar.php  # 共用顶栏导航（首页与所有功能页共用）
│   ├── homepage.php         # 首页落地页模板（共用顶栏导航）
│   ├── auth_login.php       # 登录
│   ├── auth_register.php    # 注册
│   ├── auth_profile.php     # 个人信息
│   ├── auth_password.php    # 修改密码
│   ├── balance.php          # 我的余额
│   ├── balance_recharge.php # 余额充值
│   ├── shop.php             # 主机套餐
│   ├── shop_order.php       # 购买下单
│   ├── shop_assets.php      # 我的主机
│   ├── shop_orders.php      # 我的订单
│   └── admin/settings.php   # 后台前端设置
└── README.md
```

## 使用

1. 确保 `user_info`、`balance`、`hosting_shop` 已安装并启用
2. 后台 → 系统管理 → 插件管理 → 安装并启用「售卖前端」
3. 后台 → 售卖前端 → 前端设置 → 配置站点标题 / Logo（可上传 ICO）/ Favicon（可上传 ICO）/ 主色调（色盘）/ Hero 标语 / 底部版权
4. 刷新前台页面查看效果

## 自定义

- 首页：编辑 `views/homepage.php`。
- 用户端统一页面：编辑 `views/layout.php`（布局）与 `assets/style.css`（样式），各页面模板在 `views/` 下。
- **共用导航**：编辑 `views/partials/navbar.php`（首页与所有功能页同步生效）。
- 主色调在后台「前端设置」配置后，会同步应用到所有用户端页面与首页（`--brand` 变量）。
