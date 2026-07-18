<?php
/**
 * 售卖前端 - 工具函数库
 */
if (!defined('IN_CRONLITE')) { exit; }

function shop_frontend_plugin_path(): string {
    return __DIR__;
}

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

function shop_frontend_api_url(string $path): string {
    if (function_exists('shop_frontend_route_url')) {
        return shop_frontend_route_url('shop_api/' . ltrim($path, '/'));
    }
    return 'index.php?_r=/shop_api/' . ltrim($path, '/');
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

function shop_frontend_get_user_assets(int $userId): array {
    global $DB;
    return $DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_asset WHERE user_id=? AND status='active' ORDER BY id DESC", [$userId]) ?: [];
}

function shop_frontend_get_user_orders(int $userId, int $page = 1, int $perPage = 15): array {
    global $DB;
    $total = (int)$DB->count_prepare("SELECT count(*) FROM MN_plugin_hosting_order WHERE user_id=?", [$userId]);
    $offset = ($page - 1) * $perPage;
    $list = $DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_order WHERE user_id=? ORDER BY id DESC LIMIT ?,?", [$userId, $offset, $perPage]) ?: [];
    return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
}

function shop_frontend_get_user_balance(int $userId): array {
    global $DB;
    $row = $DB->get_row_prepare("SELECT * FROM MN_plugin_balance WHERE user_id=?", [$userId]);
    return ['balance_cents' => (int)($row['balance'] ?? 0)];
}

function shop_frontend_get_balance_logs(int $userId, int $page = 1, int $perPage = 15): array {
    global $DB;
    $total = (int)$DB->count_prepare("SELECT count(*) FROM MN_plugin_balance_log WHERE user_id=?", [$userId]);
    $offset = ($page - 1) * $perPage;
    $list = $DB->get_all_prepare("SELECT * FROM MN_plugin_balance_log WHERE user_id=? ORDER BY id DESC LIMIT ?,?", [$userId, $offset, $perPage]) ?: [];
    return ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
}

function shop_frontend_get_payment_methods(): array {
    if (!function_exists('mnbt_apply_filters')) return [];
    return mnbt_apply_filters('payment_methods', []);
}

function shop_frontend_get_hosting_nodes(): array {
    global $DB;
    return $DB->get_all_prepare("SELECT * FROM MN_bt WHERE 1") ?: [];
}
