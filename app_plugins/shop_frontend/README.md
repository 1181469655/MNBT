# 售卖前端

接管站点首页的落地页插件，展示品牌信息与套餐列表，所有功能按钮链接到对应插件路由。

## 前置依赖

- `user_info` — 用户注册/登录（`/account/login`、`/account/register`、`/account/logout`）
- `balance` — 余额管理（`/balance`）
- `hosting_shop` — 主机套餐/订单/资产管理（`/shop`、`/shop/orders`、`/shop/order/{id}`）

## 功能

- 接管站点首页 `/`，渲染品牌落地页
- 展示站点标题、Logo、主色调、Hero 标语
- 展示可用套餐信息（前 3 个热门套餐）
- 所有按钮链接到对应插件的 `index.php?_r=/path` 路由
- 后台配置：站点标题、Logo、主色调、强调色、Hero 标语、底部版权、Favicon

## 目录结构

```
shop_frontend/
├── plugin.json              # 插件元信息
├── bootstrap.php            # 入口：首页接管 + 后台菜单 + AJAX
├── lib/shop_frontend.php    # 工具函数（配置读取、用户获取、套餐查询）
├── views/homepage.php       # 首页 HTML 模板（可直接编辑）
├── views/admin/settings.php # 后台设置页
└── README.md
```

## 使用

1. 确保 `user_info`、`balance`、`hosting_shop` 已安装并启用
2. 后台 → 系统管理 → 插件管理 → 安装并启用「售卖前端」
3. 后台 → 售卖前端 → 前端设置 → 配置站点信息
4. 刷新首页查看效果

## 自定义

编辑 `views/homepage.php` 即可修改首页外观。模板中可用变量：

| 变量 | 说明 |
|------|------|
| `$title` | 站点标题 |
| `$logo` | Logo URL |
| `$primary` | 主色调 |
| `$accent` | 强调色 |
| `$hero` | Hero 标语 |
| `$footer` | 底部版权文字 |
| `$favicon` | Favicon URL |
| `$user` | 当前登录用户数组（未登录为 null） |
| `$features` | 特性列表数组（icon/title/desc） |
| `$planCards` | 套餐卡片数组（id/name/desc/price/feats） |
| `$url($path)` | 生成 `index.php?_r=/path` 格式链接 |
