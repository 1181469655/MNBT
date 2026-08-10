---
title: MNBT 独立主页系统 PRD
description: 将主页接管能力下沉为核心一等公民、内置默认主页的 V1.84 规划（评审中）
---

# MNBT 独立主页系统 PRD

> 版本：v1.0（P0 规划）
> 日期：2026-08-09
> 状态：评审中
> 分支：dev/v1.84
> 关联文档：[主题开发手册](../development/theme/guide.md)、[API.md](../api/overview.md)、`app_plugins/shop_frontend/README.md`（现有主页实现）

---

## 1. 背景与目标

### 1.1 背景

MNBT 当前的主页（站点根路径 `/` 的落地页）替换完全依赖插件 `shop_frontend` 实现：

- 访问 `/` → `index.php` 依次执行：插件通用路由 → 插件首页接管（`mnbt_plugin_dispatch_home()`，MPHX/plugin.php#L1209-L1251）→ 默认 `header("Location:user")`
- `shop_frontend` 通过 `mnbt_register_home(callback, 100)` 注册首页接管，渲染品牌落地页（`app_plugins/shop_frontend/views/homepage.php`）

### 1.2 现状问题

| 问题 | 说明 |
|------|------|
| 强依赖插件引擎 | 主页接管必须依赖 `mnbt_plugins_boot()` 启动的插件 bootstrap，插件未装/未启用即无主页 |
| 强依赖业务插件 | `shop_frontend` 依赖 `user_info` / `balance` / `hosting_shop`，依赖缺失时首页同样不可用 |
| 无开箱即用体验 | 全新安装后访问 `/` 直接跳转 `user` 登录面板，不符合售卖型站点的落地页预期 |
| 能力耦合 | 主页（落地页）与用户端页面皮肤（account/balance/shop 页面）耦合在同一个插件内，职责不单一 |

### 1.3 目标

1. **主页系统核心化**：将主页接管能力下沉为核心一等公民（`MPHX` 层），不依赖插件引擎即可提供主页
2. **内置默认主页**：全新安装即可获得品牌落地页（标题 / Logo / Hero / 主色 / 公告 / 套餐），开箱即用
3. **保留插件扩展性**：插件仍可通过 `mnbt_register_home` 覆盖主页（插件优先），且提供区块级扩展钩子
4. **核心设置入口**：后台「系统设置」新增「主页设置」，不依赖插件管理页

### 1.4 范围

| 期次 | 内容 |
|------|------|
| **P0（本期）** | 核心主页引擎（`MPHX/frontend.php`）+ 内置默认主页模板（`templates/default/home/`）+ `MN_config` 配置字段 + 升级 SQL + 后台「主页设置」页（含保存接口）+ 区块扩展钩子 + 与 `shop_frontend` 并存兼容 |
| **P1（下期）** | `tdesign` 主题专属主页模板定制、区块排序/启停 UI、统计/客服等扩展区块示例、主页预览 |

---

## 2. 总体架构

### 2.1 架构图

```
┌─────────────────────────────────────────────────────────────┐
│                     访问站点根路径 /                          │
└──────────────────────────────┬──────────────────────────────┘
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                      index.php（分发入口）                     │
│   ① mnbt_plugin_dispatch_route()   通用路由（插件，保留）        │
│   ② mnbt_plugin_dispatch_home()    首页接管（插件，保留）        │
│   ③ mnbt_home_dispatch()           ★ 新增：核心主页系统          │
│   ④ header("Location:user")        默认兜底（保留）             │
└──────────────────────┬──────────────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────────────┐
│            MPHX/frontend.php（核心主页引擎）                    │
│  mnbt_home_enabled()     读取配置开关                          │
│  mnbt_home_render()      数据组装（品牌/公告/套餐/扩展区块）      │
│  mnbt_home_resolve()     主题模板解析（user主题home/ → 回退default）│
└──────────────┬──────────────────────────────┬────────────────┘
               ▼                              ▼
┌─────────────────────────────┐   ┌─────────────────────────────┐
│  templates/{theme}/home/     │   │  MN_config 配置字段          │
│  index.php（默认主页落地页）    │   │  home_enable/title/hero/... │
│  default 内置 + 主题可覆盖     │   │  （+update_v184_home.sql）  │
└─────────────────────────────┘   └─────────────────────────────┘
```

**核心模型**：主页 = 核心引擎 + 主题模板。引擎负责分发与数据组装（与插件解耦），模板负责渲染（跟随主题，`default` 兜底）。

### 2.2 请求分发时序

```
请求 /（路径为 / 时）
  1. mnbt_plugin_dispatch_route()    —— 插件通用路由（/landing、/pay/* 等），保留现有行为
  2. mnbt_plugin_dispatch_home()     —— 插件首页接管（shop_frontend 等），返回 true 即终止
  3. mnbt_home_dispatch()            —— 若 home_enable=true 且无插件接管 → 渲染内置默认主页
  4. header("Location:user")         —— 关闭内置主页或未启用时的兜底（兼容旧行为）
```

> 插件优先覆盖天然成立：`mnbt_register_home` 默认 priority=10（`shop_frontend` 为 100），先于内置主页执行；内置主页仅在**全部插件 handler 均返回 false/null** 后兜底。

### 2.3 文件结构

```
MPHX/
├── common.php                # 修改：include MPHX/frontend.php（在 theme.php 之后）
└── frontend.php              # ★ 新增：主页引擎（分发 / 数据组装 / 模板解析 / 区块钩子）

index.php                     # 修改：在步骤②之后插入 mnbt_home_dispatch()

templates/
├── default/
│   └── home/
│       └── index.php         # ★ 新增：默认主页落地页（内联样式，无额外静态资源依赖）
└── tdesign/
    └── home/                 # P1：tdesign 主题可覆盖（本期不建）

admin/
├── api/setting.php           # 修改：新增 gn=save_home_settings 保存接口
└── set.php                   # 参考：传统 gn 分发模式（本项走 tdesign SPA 设置 Tab，见 §4）

templates/tdesign/spa/src/    # 修改：系统设置页新增「主页设置」Tab（P0 可选，见 §4.1）

update/
└── update_v184_home.sql      # ★ 新增：ALTER TABLE MN_config 追加 home_* 字段（幂等）
```

> 主题解析不新增 scope：主页模板直接按 `templates/{当前 user 主题}/home/` 查找，未命中回退 `templates/default/home/`（与 `mnbt_theme_resolve` 的回退策略一致，但独立实现，不动现有三个 scope）。

---

## 3. 核心设计

### 3.1 主页引擎 `MPHX/frontend.php`

```php
// 入口：index.php 调用。仅路径为 / 时生效。
function mnbt_home_dispatch(): bool;

// 配置读取：home_enable / home_title / home_hero / home_primary / home_logo / home_favicon / home_footer / home_show_notice / home_show_plans
function mnbt_home_enabled(): bool;
function mnbt_home_option(string $key, $default = null);

// 数据组装：返回模板变量数组
function mnbt_home_data(): array;

// 模板解析：templates/{user主题}/home/index.php → 回退 templates/default/home/index.php
function mnbt_home_resolve(): ?string;

// 渲染：组装数据 → 加载模板 → exit
function mnbt_home_render(): void;
```

关键实现约束：

- 引擎内所有数据查询做**存在性守卫**（`function_exists` / `mnbt_plugin_enabled`），hosting_shop、user_info 等插件缺失时自动降级，绝不抛错
- 仅 `GET /` 触发；`/home` 等其余路径不拦截
- 复用 `mnbt_csrf_inject_html` 输出过滤，模板内链接统一走 `mnbt_plugin_request_info()['base']` 前缀（兼容子目录部署）

### 3.2 配置字段（`MN_config` 新增）

> 站点名称（`name`）、网站公告（`gg`）、版权（`hxp`）已存在，直接复用；仅新增缺失字段。

| 字段 | 类型 | 默认 | 说明 |
|------|------|------|------|
| `home_enable` | varchar(10) | `true` | 是否启用内置默认主页（false 时回到旧行为：跳转 user） |
| `home_title` | text | 空 | 站点标题，空则回退 `MN_config.name` |
| `home_hero` | text | `高性能虚拟主机，即买即用` | Hero 标语 |
| `home_primary` | varchar(10) | `#4f46e5` | 主色调 |
| `home_logo` | text | 空 | Logo URL（支持上传到 `imsetes/upload_logo/` 或手动填写） |
| `home_favicon` | text | 空 | Favicon URL，空则回退 `imsetes/images/logo-ico.png` |
| `home_footer` | text | 空 | 底部版权，空则回退 `MN_config.hxp` |
| `home_show_notice` | varchar(10) | `true` | 显示公告区（数据源 `MN_config.gg`） |
| `home_show_plans` | varchar(10) | `true` | 显示套餐区（hosting_shop 启用且存在有效套餐时） |

**升级 SQL `update/update_v184_home.sql`**（参考 `install/1.79To1.81.sql` 先例）：

```sql
ALTER TABLE `MN_config` ADD COLUMN `home_enable` varchar(10) NOT NULL DEFAULT 'true' AFTER `pay_methods`;
ALTER TABLE `MN_config` ADD COLUMN `home_title` text NOT NULL AFTER `home_enable`;
ALTER TABLE `MN_config` ADD COLUMN `home_hero` text NOT NULL AFTER `home_title`;
ALTER TABLE `MN_config` ADD COLUMN `home_primary` varchar(10) NOT NULL DEFAULT '#4f46e5' AFTER `home_hero`;
ALTER TABLE `MN_config` ADD COLUMN `home_logo` text NOT NULL AFTER `home_primary`;
ALTER TABLE `MN_config` ADD COLUMN `home_favicon` text NOT NULL AFTER `home_logo`;
ALTER TABLE `MN_config` ADD COLUMN `home_footer` text NOT NULL AFTER `home_favicon`;
ALTER TABLE `MN_config` ADD COLUMN `home_show_notice` varchar(10) NOT NULL DEFAULT 'true' AFTER `home_footer`;
ALTER TABLE `MN_config` ADD COLUMN `home_show_plans` varchar(10) NOT NULL DEFAULT 'true' AFTER `home_show_notice`;
```

> 升级脚本需兼容重复执行（先 `SHOW COLUMNS` 判断或安装流程中统一处理，沿用仓库现有 `update_v183_docker.sql` 的幂等约定）。

### 3.3 内置默认主页模板 `templates/default/home/index.php`

**页面骨架**（参照 `shop_frontend/views/homepage.php` 的落地页结构，样式内联，不引入外部静态资源）：

| 区块 | id/顺序 | 数据来源 | 显示条件 |
|------|---------|----------|----------|
| 顶栏 | navbar | `home_title` + `home_logo` + 当前登录态 | 始终 |
| Hero | hero | `home_hero`、`home_primary`；按钮「查看套餐」「用户登录」 | 始终 |
| 公告区 | notice（order 10） | `MN_config.gg` | `home_show_notice=true` 且公告非空 |
| 套餐区 | plans（order 20） | `MN_plugin_hosting_plan`（status=active） | `home_show_plans=true` 且 hosting_shop 启用且有套餐 |
| 扩展区块 | blocks（order ≥ 30） | 插件通过 `home.blocks` 过滤器注入 | 有注入时 |
| 页脚 | footer | `home_footer`（回退 `hxp`） | 始终 |

**链接策略**（不写死插件路由，保证无插件也可用）：

| 目标 | 链接 |
|------|------|
| 查看套餐 | 有插件路由时 → `index.php?_r=/shop`（hosting_shop 路由）；无插件时隐藏「查看套餐」按钮 |
| 用户登录 | → `user/login.php`（核心，始终可用）；插件 `user_info` 启用时 → `index.php?_r=/account/login` |
| 注册 | `user_info` 启用时 → `index.php?_r=/account/register`，否则隐藏 |

### 3.4 区块扩展钩子

引擎渲染扩展区块时调用：

```php
$blocks = mnbt_apply_filters('home.blocks', []);
// 每项结构：['id'=>string, 'title'=>string, 'html'=>string, 'order'=>int]
// 引擎按 order 升序渲染，内置 notice(10)/plans(20)，插件区块建议 order>=30
```

插件示例（`bootstrap.php` 内）：

```php
mnbt_add_filter('home.blocks', function ($blocks) {
    $blocks[] = [
        'id'    => 'support',
        'title' => '技术支持',
        'html'  => '<div class="home-block-support">7×24 小时工单响应</div>',
        'order' => 30,
    ];
    return $blocks;
});
```

---

## 4. 后台设置

### 4.1 入口

- **P0 主路径**：后台「系统设置」新增「主页设置」Tab（tdesign SPA 的 Settings 页，复用现有设置 Tab 机制；若 SPA 设置 Tab 改造成本高，则先落传统页 `admin/set.php?gn=home`，P1 迁入 SPA）
- 不新增插件菜单、不依赖插件管理页

### 4.2 设置项

| 设置项 | 控件 | 说明 |
|--------|------|------|
| 启用内置主页 | 开关 | `home_enable`，关闭后恢复旧行为（跳转 user） |
| 站点标题 | 输入框 | `home_title`，留空回退系统名称 |
| Hero 标语 | 输入框 | `home_hero` |
| 主色调 | 色盘 + 十六进制输入 | `home_primary`，同步应用到内置主页 CSS 变量 |
| 站点 Logo | 上传（支持 png/jpg/ico）+ URL 输入 | `home_logo`，复用 `imsetes/upload_logo/` 目录 |
| Favicon | 上传 + URL 输入 | `home_favicon` |
| 底部版权 | 输入框 | `home_footer`，留空回退系统版权 |
| 显示公告区 | 开关 | `home_show_notice` |
| 显示套餐区 | 开关 | `home_show_plans` |

### 4.3 保存接口

- `admin/ajax.php` → `admin/api/setting.php` 新增 `gn=save_home_settings`
- 仅管理员可调（沿用 `$islogin` 校验 + `mnbt_csrf_validate_request`）
- 写入 `MN_config` 对应字段（`UPDATE MN_config SET home_*=?, ... WHERE id=?`）
- 返回 `json_exit_success`，SPA/传统页 toast 提示「保存成功」

---

## 5. 兼容性与迁移

### 5.1 与 shop_frontend 并存

| 场景 | 行为 |
|------|------|
| 未启用任何插件 | 内置默认主页直接生效（开箱即用） |
| shop_frontend 启用 | 其 `mnbt_register_home(…, 100)` 先执行，渲染插件主页（**行为不变**） |
| 后台关闭内置主页 | 回到旧行为：插件接管或跳转 user |
| shop_frontend 卸载/禁用 | 内置默认主页自动接管，前台不出现空白页 |

### 5.2 向后兼容

- `mnbt_register_home` / `mnbt_plugin_dispatch_home` API 不变，所有现有插件无需改动
- `index.php` 仅插入一个新增函数调用，原分发链路保留
- 无新增依赖表；`MN_config` 仅追加列，不破坏既有读写

### 5.3 升级路径

1. 执行 `update/update_v184_home.sql` 追加配置字段（随版本升级流程）
2. `common.php` include `frontend.php`，`index.php` 插入 `mnbt_home_dispatch()`
3. 部署 `templates/default/home/index.php`
4. 老用户升级后默认 `home_enable=true`，立即获得内置主页（若已启用 shop_frontend，行为不变）

---

## 6. 验收标准

| # | 验收项 |
|---|--------|
| 1 | 全新安装、未启用任何插件：访问 `/` 渲染内置默认主页，不跳转 user |
| 2 | 内置主页展示标题 / Hero / 主色 / 页脚；公告与套餐区按开关与数据存在性正确显隐 |
| 3 | 后台「主页设置」保存后前台即时生效（标题 / Hero / 颜色 / Logo / 开关） |
| 4 | 启用 shop_frontend 后其主页优先接管，行为与升级前一致 |
| 5 | 插件通过 `home.blocks` 注入的区块按 order 渲染 |
| 6 | `update_v184_home.sql` 可重复执行不报错；`MN_config` 既有读写不受影响 |
| 7 | 子目录部署（`example.com/mnbt/`）下主页内链接均带 base 前缀，不 404 |

---

## 7. 风险与开放问题

| 项 | 说明 | 对策 |
|----|------|------|
| 内置主页与插件主页样式差异 | 内置默认主页为轻量落地页，插件主页（shop_frontend）更丰富 | 并存策略下样式互不影响；P1 提供 tdesign 专属模板缩小差异 |
| 套餐区强依赖 hosting_shop 数据表 | 表结构变更可能导致查询异常 | 引擎内 `@` 抑制 + `mnbt_plugin_enabled('hosting_shop')` 前置守卫，查询失败静默降级 |
| SPA 设置 Tab 接入成本 | tdesign 后台为构建产物，改动需重新 `npm run build:admin` | P0 若评估成本高，先落传统页 `admin/set.php?gn=home`，P1 迁入 SPA |
| 区块钩子滥用（未转义 HTML） | 插件注入 HTML 存在 XSS 面 | 区块内容由插件自行负责转义，文档明确约定；引擎仅做 `mnbt_csrf_inject_html` 输出处理 |
