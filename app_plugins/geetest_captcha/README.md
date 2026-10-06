# 极验行为验证 4.0 插件

将 MNBT 三端登录（管理后台 / 用户控制台 / Docker 控制台）的人机验证替换为极验行为验证 4.0。

## 工作原理

基于 MNBT 验证码 provider 扩展点（`captcha.provider` / `spa.boot` 过滤器）实现"安装即退位"：

- **启用并配置密钥后**：登录验证自动切换为极验 4.0（前端 bind 模式弹出验证框，成功后提交
  `captchaToken`，服务端调极验二次校验接口确认），内置滑块验证码自动退位（`captcha.php` 拒答）。
- **停用插件或清空密钥后**：内置滑块验证码自动复位。
- 已有其他验证码插件生效时本插件自动让位（按 filter priority 取第一个）。
- 登录失败限速由 MNBT 核心层执行，与本插件无关，任何模式下均生效。

## 配置

1. 在极验后台 <https://auth.geetest.com/> 注册并创建「行为验证 4.0」验证项目（Web 使用场景）；
2. 进入 MNBT 管理后台 → 侧栏「插件管理」分组 → **极验行为验证 4.0**（或插件管理页顶部快捷入口）→ 设置，
   填写 `captchaId` 与 `captcha_key`；
3. 保存后无需重启，前台登录页刷新即生效。

## 服务端二次校验协议

- 接口：`POST https://gcaptcha4.geetest.com/validate`（application/x-www-form-urlencoded）
- 参数：`lot_number` / `captcha_output` / `pass_token` / `gen_time` / `captcha_id` /
  `sign_token`（`hash_hmac('sha256', lot_number, captcha_key)`）
- `result === 'success'` 放行；接口不可达或返回异常一律拒绝（fail-closed），并记录 error_log。

## 前端适配器契约

`adapter.js` 实现 `window.MNBT_CAPTCHA_ADAPTER = { mount(el, { onSuccess, onFail }), reset() }`。
本插件以**内联模式**（`boot.captcha.inline = true`）运行：`HumanVerify.vue` 检测到 inline 后
不再渲染自带的"点击进行人机验证"按钮，而是把容器直接交给适配器——适配器以 GT4 `popup`
模式 `appendTo` 渲染**极验官方验证按钮**（"点击按钮开始验证"），点击后弹出极验验证窗口。
验证成功后 `getValidate()` 结果 JSON 串作为 `captchaToken` 随登录请求提交；登录失败后
`reset()` 销毁实例并重新初始化（保证验证结果一次性）。

## 文件结构

```
geetest_captcha/
├── plugin.json        # 插件元数据
├── bootstrap.php      # provider/spa.boot 过滤器 + 二次校验 + 设置保存 AJAX
├── adapter.js         # 前端适配器（极验 GT4 bind 模式）
└── admin/settings.php # 后台配置页（captchaId / captcha_key）
```
