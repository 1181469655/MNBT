<?php
/**
 * 根 vendor 手动自动加载器
 *
 * 本目录存放手工 vendor 的类库（与 composer 管理的 mail/vendor 互不影响）：
 *  - fastknife/ajcaptcha  行为验证码服务端（GPL-3.0，见 fastknife/ajcaptcha/LICENSE）
 *
 * 说明：fastknife/ajcaptcha 无任何第三方包依赖（仅 PHP 扩展 gd/openssl/iconv/json），
 * 因此无需 composer，直接按其 composer.json 的 PSR-4 约定注册即可。
 */
spl_autoload_register(function ($class) {
	$prefix = 'Fastknife\\';
	$base_dir = __DIR__ . '/fastknife/ajcaptcha/src/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (is_file($file)) {
		require $file;
	}
});
