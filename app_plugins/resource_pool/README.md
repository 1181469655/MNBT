# 资源池插件（resource_pool）

管理员后台的资源池（经销 / 代理配额）管理插件。为每个资源池配置账号、可用节点白名单、
网页空间 / 数据库空间 / 流量总配额、到期日期与状态；从资源池开通的主机会记录归属资源池。

纯插件实现，**不修改任何核心文件，也不改动核心主机表 `MN_zj` 的结构**。

---

## 功能

| 位置 | 功能 |
|------|------|
| 一级菜单「资源池管理」→ 添加资源池 | 新建 / 编辑资源池 |
| 一级菜单「资源池管理」→ 资源池列表 | 列表、搜索、启用/禁用、编辑、删除、从资源池开通主机 |
| 一级菜单「资源池管理」→ 资源主机管理 | 查看所有资源池开通的主机、按资源池筛选、绑定/解除归属、清理失效归属 |
| 主机管理 → 主机列表 | 自动多出一列「资源池」，显示该主机归属哪个资源池 |

## 安装

1. 目录放到 `app_plugins/resource_pool/`
2. 后台 → 插件管理 → **安装** → **启用**
3. **整页刷新**后台（侧栏菜单在框架页渲染，只刷 iframe 不够）

安装时执行 `install.sql`，只建插件自有表 `MN_plugin_respool`。
若表结构有缺失，可在「资源池列表」页点 **修复数据表** 补齐。

## 归属关系怎么存的

**不在 `MN_zj` 上加字段。** 归属关系存在资源池表的 `host_users` 字段里 ——
一个 JSON 数组，元素是 `MN_zj.user`（主机账号）：

```json
["user123456", "shopabc001"]
```

统计与查询时用 `MN_zj.user` 反查主机行：

- 算配额：把该池 `host_users` 里的账号对应的主机 `hxa/hxb/llmax` 的 `max` 累加
- 查主机：`SELECT * FROM MN_zj WHERE user IN (...)`
- 反查归属：把所有池的 `host_users` 展开成 `主机账号 => 资源池` 映射

主机在「主机列表」被删除后，它的账号仍留在 `host_users` 里，但反查不到主机行，
因此**自动不再计入配额与列表**；可用「清理失效归属」清掉残留记录。

一个主机账号只能归属一个资源池，绑定时会做互斥校验。

> 从 1.0.0 升级：1.0.0 曾给 `MN_zj` 加过 `pool_id` 字段。插件启动时会自动把
> `pool_id` 的归属数据搬进 `host_users` 并清空 `pool_id`，之后该字段闲置。
> 插件不会删除这个字段（不动核心表结构），留着也不影响原有功能，可自行手动清理。

## 数据表

只新建一张表 `MN_plugin_respool`：

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int | 主键 |
| `name` | varchar(120) | 资源池名 |
| `username` | varchar(120) | 用户名（唯一） |
| `password` | varchar(255) | 密码 |
| `nodes` | text | 可用节点，`MN_bt.btdh` 的 JSON 数组；`[]` = 不限 |
| `host_users` | text | **归属本池的主机账号**，`MN_zj.user` 的 JSON 数组 |
| `web_space` | int | 网页空间总配额 MB，`0` = 不限 |
| `sql_space` | int | 数据库空间总配额 MB，`0` = 不限 |
| `flow` | int | 流量总配额 GB/月，`0` = 不限 |
| `expire_date` | varchar(50) | 到期日期 `yyyy-mm-dd`，空 = 永不到期 |
| `status` | varchar(20) | 资源池状态 `enabled` / `disabled` |
| `remark` | varchar(500) | 备注 |
| `created_at` / `updated_at` | varchar(50) | 时间戳 |

卸载插件时 `DROP` 掉这张表即可，核心表零残留。

## 配额语义

`web_space` / `sql_space` / `flow` 是该资源池的**总配额**。从本资源池开通主机时，
把该池已归属主机的对应 `max` 值累加，加上本次申请量，超过总配额即拒绝开通。
填 `0` 表示该项不限。

资源池被**禁用**或**已过期**时不能再开通主机，已开通的主机不受影响。

删除资源池不会删除已开通的主机（归属关系随该行一起消失）。
「解除归属」同理，解除后该主机占用的配额从资源池释放。

## 主机列表「资源池」列的实现

不改核心 `templates/default/admin/list.php`。插件通过
`mnbt_register_partial_override('admin', 'head', ...)` 在管理端 `head` 之后注入一段脚本，
包装 `$.fn.bootstrapTable`，在核心初始化主机表时把「资源池」列插到「主机操作」列之前，
按行数据的 `user` 字段匹配资源池名。

- 仅在 `admin/list.php?gn=zj` 生效，其他页面回调直接返回 `null`
- 包装时保留库自身属性（`VERSION` / `locales` / `defaults` 等）
- 全程 `try/catch`，注入失败则静默降级，不影响原页面
- 资源池名做 HTML 转义；未归属的主机显示 `-`
- 映射用 `JSON_FORCE_OBJECT` 编码，避免纯数字主机账号被当成数组下标

「资源主机管理」页是服务端渲染，不依赖上述注入。

## AJAX 接口

均为管理端（`POST admin/ajax.php`），前缀 `p_respool_`：

| `gn` | 说明 |
|------|------|
| `p_respool_save` | 新增 / 编辑资源池（带 `id` 为编辑；密码留空 = 不改） |
| `p_respool_delete` | 删除资源池 |
| `p_respool_status` | 启用 / 禁用 |
| `p_respool_open_host` | 从资源池开通主机 |
| `p_respool_bind_host` | 把已有主机绑定到资源池（`pool_id` + `host_user`） |
| `p_respool_unbind_host` | 解除主机的资源池归属（`host_user`） |
| `p_respool_prune` | 清理失效归属 |
| `p_respool_repair` | 修复数据表 |

## 对外函数

其他插件可直接调用：

```php
rp_get($id);                          // 取资源池
rp_get_by_username($username);        // 按用户名取资源池
rp_list($page, $per_page, $kw, $status);
rp_usage($pool_id);                   // 已分配用量 ['hosts','web','sql','flow']
rp_usage_batch([$id1, $id2]);         // 批量用量，避免 N+1
rp_remaining($pool);                  // 剩余配额，null = 不限
rp_is_usable($pool);                  // 启用且未过期
rp_pool_hosts($pool_id);              // 该池的主机行
rp_all_pool_hosts($pool_id, $kw);     // 全部资源池主机（0=不筛选），含 pool_id/pool_name
rp_unbound_hosts($limit);             // 未归属任何资源池的主机
rp_host_user_map();                   // 主机账号 => ['pool_id','pool_name']
rp_host_user_name_map();              // 主机账号 => 资源池名
rp_find_pool_by_host_user($user);     // 查某主机账号的归属池
rp_bind_host_user($pool_id, $user);   // 绑定
rp_unbind_host_user($user, $pool_id); // 解绑（$pool_id 可省略，自动查）
rp_prune_host_users($pool_id);        // 清理失效归属（省略=全部池）
rp_open_host($pool_id, $args);        // 从资源池开通主机
```

`rp_open_host($pool_id, $args)` 的 `$args`：
`node` / `user` / `pass` / `web_space` / `sql_space` / `flow` / `domain_count` /
`expire_date` / `status`（`'true'`｜`'false'`），返回 `['ok'=>bool,'msg'=>string,'host_id'=>int]`。

开通成功会触发 `host.created` 钩子，`$ctx` 为
`['source'=>'resource_pool', 'pool_id'=>N]`。

## 已知限制

- 资源池密码以明文存储（与核心 `MN_zj.pass` 主机密码一致的做法）。目前资源池只在
  后台使用，没有对外登录入口；若后续要做资源池自助登录，应改为哈希存储。
- 归属按**主机账号**匹配。若在别处把某台主机的 `MN_zj.user` 改掉，归属会失效，
  需重新绑定（核心「编辑主机」弹窗里账号是只读的，正常不会发生）。
- 「资源池」列注入依赖当前 default 管理端主题的 bootstrap-table 用法。换主题后
  若该列不出现，用「资源主机管理」页查看归属即可，功能不受影响。
- 配额按主机的 `max` 配置累加，不是按实际使用量。
