# 清凉云主题（qingliangyun）v0.1.0

现代化 **用户端** 主题：白绿配色、圆角轻量 UI、响应式布局。  
技术栈：**Vue 3 + Vue Router + Element Plus + Vite**。

> 仅覆盖用户端（`scope: user`）。管理端仍使用系统当前管理端主题。

---

## 特性

| 项 | 说明 |
|----|------|
| 登录页 | 毛玻璃卡片、圆角主按钮、验证码支持 |
| 控制台壳 | 侧栏 + 顶栏，移动端抽屉菜单 |
| 仪表盘 | **圆形进度**展示网页空间 / 数据库 / 流量 |
| 业务页 | 复杂页（set / ftp / 监控等）iframe 复用 `default` 能力，保证功能完整 |
| 路由 | Hash 模式（`#/dashboard`），不改 PHP 控制器 URL |

---

## 目录结构

```
templates/qingliangyun/
├── theme.json                 # 主题元信息
├── README.md                  # 本说明
├── user/                      # PHP 主题入口（mnbt_render 解析）
│   ├── _spa_boot.php          # 注入 __QL_BOOT__ + 加载 dist
│   ├── login.php / index.php / sy.php ...
│   └── dist/                  # ★ Vite 构建产物（需提交，勿 gitignore）
│       └── assets/
│           ├── index.js
│           └── index.css
└── spa/                       # SPA 源码（开发用）
    ├── package.json
    ├── vite.config.js
    ├── .gitignore             # 仅忽略 node_modules 等，不忽略 dist
    ├── index.html
    └── src/
        ├── main.js
        ├── App.vue
        ├── api/               # ajax.php 封装
        ├── router/
        ├── components/
        ├── views/
        └── styles/theme.scss
```

---

## 编译说明

### 环境

- Node.js **18+**（推荐 20 LTS）
- npm 9+ 或 pnpm / yarn

### 安装依赖

```bash
cd templates/qingliangyun/spa
npm install
```

### 开发（可选）

```bash
npm run dev
```

默认 `http://127.0.0.1:5173`。  
开发代理见 `vite.config.js` 的 `server.proxy`（按你的本地域名改 `target`）。

### 生产构建

```bash
cd templates/qingliangyun/spa
npm run build
```

产物输出到：

```
templates/qingliangyun/user/dist/
```

PHP 入口通过 `mnbt_theme_url('dist/assets/index.js')` 加载。  
**请将 `user/dist` 一并提交/部署**，服务器无需安装 Node 即可运行主题。

### 未构建时

打开用户端会显示「清凉云前端尚未构建」提示与编译命令。

---

## 启用主题

1. 确保已 `npm run build` 且存在 `user/dist/assets/index.js`
2. 管理后台 → **系统管理** → **前端模板**
3. **用户端主题** 选择 **清凉云** → 保存  
   或写入文件：`templates/active_user_theme` 内容为 `qingliangyun`

---

## 与 PHP 的对接

### 入口

| 访问 | 控制器 | 主题视图 | SPA 路由 |
|------|--------|----------|----------|
| `/user/login.php` | `user/login.php` | `user/login.php` | `#/login` |
| `/user/index.php` | `user/index.php` | `user/index.php` | `#/dashboard` |
| `/user/sy.php` | `user/sy.php` | `user/sy.php` | `#/dashboard` |

### 启动数据 `window.__QL_BOOT__`

由 `_spa_boot.php` 注入，例如：

```js
{
  siteName, footer, user, loggedIn, needCaptcha,
  productType, ajaxBase: './ajax.php', theme, version
}
```

### AJAX

仍请求 **`./ajax.php`**，`gn` 与官方一致（如 `login`、`indexconf`、`sxsyxx`、`phpxg`）。

### iframe 业务页

为避免递归加载 SPA，iframe 访问加 `?_ql=1`，对应 PHP 视图会 **include default 主题** 的完整页面。

---

## 设计规范（摘要）

- 主色：`#12b886`，浅底 `#f4faf7`，卡片圆角 `16px`
- 字体：系统 UI / 苹方 / 微软雅黑
- 仪表盘配额：`CircleProgress` 圆形进度
- 移动端：侧栏抽屉 + 顶栏折叠

---

## 开发约定

1. **不要改** `user/*.php` 控制器与 `ajax` 接口路径  
2. 新增纯前端页面：在 `spa/src/views` + `router` 添加，再 `npm run build`  
3. 复杂宝塔业务优先 iframe default，保证兼容  
4. `spa/.gitignore` 忽略 `node_modules`，**不忽略** `user/dist`  
5. 版本号同步：`theme.json` 与 `package.json`

---

## 已知限制（0.1.0）

- 设置 / 文件管理 / 一键部署等仍为 iframe 内嵌 default UI  
- 后续版本可将高频设置项逐步改为原生 Element Plus 表单  
- 管理端未提供本主题皮肤  

---

## 版本

- **0.1.0** 首版 SPA 壳 + 登录 + 仪表盘圆形进度 + 业务 iframe 兼容
