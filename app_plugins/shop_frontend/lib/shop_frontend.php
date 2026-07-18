<?php
/**
 * 售卖前端 - 工具函数库
 */
if (!defined('IN_CRONLITE')) { exit; }

function shop_frontend_asset_url(string $path): string {
    $slug = 'shop_frontend';
    if (function_exists('mnbt_plugin_asset_url')) {
        return mnbt_plugin_asset_url($slug, $path);
    }
    return '../app_plugins/' . $slug . '/' . ltrim($path, '/');
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
    $rows = $DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE status=1 ORDER BY sort ASC, id ASC") ?: [];
    return $rows;
}
