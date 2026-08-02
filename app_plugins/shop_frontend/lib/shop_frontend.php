<?php
/**
 * 售卖前端 - 工具函数库
 * 提供：配置读取、URL 生成、用户/套餐查询、统一视图渲染。
 */
if (!defined('IN_CRONLITE')) { exit; }

function shop_frontend_asset_url(string $path): string {
    $slug = 'shop_frontend';
    if (function_exists('mnbt_plugin_url')) {
        return mnbt_plugin_url($slug, 'assets/' . ltrim($path, '/'));
    }
    return '../app_plugins/' . $slug . '/assets/' . ltrim($path, '/');
}

function shop_frontend_option(string $key, $default = null) {
    if (function_exists('mnbt_plugin_option_get')) {
        return mnbt_plugin_option_get('shop_frontend', $key, $default);
    }
    return $default;
}

function shop_frontend_get_current_user(): ?array {
    if (empty($_COOKIE['account_token'])) return null;
    $token = $_COOKIE['account_token'];
    $decoded = authcode($token, 'DECODE', SYS_KEY);
    if (empty($decoded)) return null;
    $parts = explode("\t", $decoded, 2);
    if (count($parts) < 2) return null;
    [$uid, $hash] = $parts;
    $uid = (int)$uid;
    if ($uid <= 0) return null;
    global $DB;
    $user = $DB->get_row_prepare("SELECT * FROM MN_plugin_user WHERE id=? AND status=1 LIMIT 1", [$uid]);
    if (!$user) return null;
    if (md5($uid . $user['password_hash'] . SYS_KEY) !== $hash) return null;
    return $user;
}

function shop_frontend_get_plans(): array {
    global $DB;
    $rows = $DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE status='active' ORDER BY sort ASC, id ASC") ?: [];
    return $rows;
}

/**
 * 生成带站点 base path 前缀的 URL（index.php?_r=/path）。
 * 与 user_info / balance / hosting_shop 插件 URL 格式完全一致。
 */
function shop_frontend_url(string $path = ''): string {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($basePath === '.' || $basePath === '/') { $basePath = ''; }
    $p = ltrim($path, '/');
    $qpos = strpos($p, '?');
    if ($qpos !== false) {
        $route = substr($p, 0, $qpos);
        $query = substr($p, $qpos + 1);
        return $basePath . '/index.php?_r=/' . $route . '&' . $query;
    }
    return $basePath . '/index.php?_r=/' . $p;
}

/**
 * 生成核心物理文件 URL（如 /user/idcdl.php），带站点 base path。
 */
function shop_frontend_core_url(string $path = ''): string {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($basePath === '.' || $basePath === '/') { $basePath = ''; }
    return $basePath . '/' . ltrim($path, '/');
}

/**
 * 上传到本插件 assets 的图标 URL 追加版本号（避免浏览器缓存旧图）。
 * $value 为站点配置里保存的值；$type 为 'logo' 或 'favicon'。
 */
function shop_frontend_cached_icon_url($value, string $type): string {
    if (!is_string($value) || $value === '') return '';
    $base = 'assets/' . $type . '.ico';
    if (strpos($value, $base) === false) return $value; // 非本插件上传的自定义 URL 原样返回
    $file = mnbt_plugin_path('shop_frontend') . $base;
    if (is_file($file)) {
        $v = @filemtime($file);
        if ($v) {
            return mnbt_plugin_url('shop_frontend', $base) . '?v=' . $v;
        }
    }
    return $value;
}

/**
 * 站点品牌信息（后台「前端设置」可配置）。
 */
function shop_frontend_brand(): array {
    return [
        'title'   => shop_frontend_option('site_title', '') ?: 'MNBT 云服务',
        'logo'    => shop_frontend_cached_icon_url(shop_frontend_option('site_logo', ''), 'logo'),
        'primary' => shop_frontend_option('site_primary', '') ?: '#4f46e5',
        'footer'  => shop_frontend_option('site_footer', ''),
    ];
}

/**
 * 站点 Favicon URL（带缓存版本号）。
 */
function shop_frontend_favicon(): string {
    return shop_frontend_cached_icon_url(shop_frontend_option('site_favicon', ''), 'favicon');
}

/**
 * 生成品牌 Logo 缩写标记（最多 2 个字符，兼容无 mbstring 环境）。
 */
function shop_frontend_brand_mark(string $title): string {
    if ($title === '') return 'MN';
    if (function_exists('mb_substr')) return mb_substr($title, 0, 2, 'UTF-8');
    return substr($title, 0, 2);
}

/**
 * 渲染统一视图（视图末尾 include layout.php）。
 *
 * 注入变量：current_user / asset_url / has_balance / has_hosting /
 *           brand（站点标题）/ brand_logo / brand_primary / brand_footer / brand_favicon
 *           以及各页面传入的业务数据。
 */
function shop_frontend_render(string $view, array $vars = []): void {
    $brandInfo = shop_frontend_brand();
    $vars['current_user'] = $vars['current_user'] ?? shop_frontend_get_current_user();
    $vars['asset_url'] = function_exists('mnbt_plugin_url') ? mnbt_plugin_url('shop_frontend', 'assets/') : '/app_plugins/shop_frontend/assets/';
    $vars['has_balance'] = function_exists('balance_get');
    $vars['has_hosting'] = function_exists('hosting_plan_list_active');
    $vars['brand'] = $brandInfo['title'];
    $vars['brand_logo'] = $brandInfo['logo'];
    $vars['brand_primary'] = $brandInfo['primary'];
    $vars['brand_footer'] = $brandInfo['footer'];
    $vars['brand_favicon'] = shop_frontend_favicon();
    extract($vars, EXTR_SKIP);
    $viewFile = mnbt_plugin_path('shop_frontend') . 'views/' . $view . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'View not found: ' . htmlspecialchars($view);
        return;
    }
    include $viewFile;
}
