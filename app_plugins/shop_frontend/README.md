# 售卖前端

基于 Vue 3 + Vuetify 3 的 SPA 售卖网站插件。

## 前置依赖

- `user_info` — 用户注册/登录/个人信息
- `balance` — 余额管理
- `hosting_shop` — 主机套餐/订单/资产管理

## 功能

- 接管系统首页，渲染 SPA 售卖网站
- 套餐浏览 / 下单购买
- 用户登录注册
- 个人信息 / 余额管理 / 我的主机 / 订单
- 后台可配置站点名称、Logo、主题色

## 目录结构

```
shop_frontend/
├── plugin.json
├── bootstrap.php           # 路由注册 / 首页接管 / 后台菜单
├── lib/shop_frontend.php   # 工具函数
├── views/
│   ├── entry.php           # SPA HTML 入口（由 Vite build 或 CDN 加载）
│   ├── admin/settings.php  # 后台设置页
│   └── spa/                # Vue 3 + Vuetify 3 源码
│       ├── package.json
│       ├── vite.config.js
│       ├── index.html      # 开发入口
│       └── src/
│           ├── main.js
│           ├── App.vue
│           ├── router/
│           ├── views/
│           ├── components/
│           ├── stores/
│           ├── api/
│           └── styles/
└── assets/
```

## 开发

```bash
cd app_plugins/shop_frontend/views/spa
npm install
npm run dev
```

Vite 开发服务器已配置 proxy 到 PHP 后端，可直接开发调试。

## 构建

```bash
npm run build
```

构建产物输出到 `views/spa/dist/`，PHP 入口自动加载。
