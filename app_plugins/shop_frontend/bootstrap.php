<?php
/**
 * 售卖前端 - 美观首页
 * 接管站点首页，展示品牌信息与套餐列表，所有功能链接跳转到对应插件
 */
if (!defined('IN_CRONLITE')) { exit; }

$pluginDir = __DIR__;
require_once $pluginDir . '/lib/shop_frontend.php';

/* ============================================================
 *  1) 首页接管
 * ============================================================ */
mnbt_register_home(function ($ctx) {
    shop_frontend_render_homepage();
    return true;
}, 100);

/* ============================================================
 *  2) 首页渲染
 * ============================================================ */
function shop_frontend_render_homepage() {
    $title   = shop_frontend_option('site_title', 'MNBT 主机售卖');
    $logo    = shop_frontend_option('site_logo', '');
    $primary = shop_frontend_option('site_primary', '#1867C0');
    $accent  = shop_frontend_option('site_accent', '#FF5722');
    $hero    = shop_frontend_option('site_hero', '高性能虚拟主机，即买即用');
    $footer  = shop_frontend_option('site_footer', '© ' . date('Y') . ' MNBT. All rights reserved.');
    $favicon = shop_frontend_option('site_favicon', '');

    $plans = shop_frontend_get_plans();
    $user = shop_frontend_get_current_user();

    // URL 构建 — 与 user_info / balance / hosting_shop 插件完全一致的格式
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($base === '.' || $base === '/') { $base = ''; }
    $url = function(string $path) use ($base): string {
        $p = ltrim($path, '/');
        return $base . '/index.php?_r=/' . $p;
    };

    $features = [
        ['icon' => 'mdi-shield-check',  'title' => '99.9% 在线率', 'desc' => '企业级硬件架构，稳定可靠，SLA 保障'],
        ['icon' => 'mdi-flash',         'title' => '极速部署',     'desc' => '支付成功后自动开通，即买即用，无需等待'],
        ['icon' => 'mdi-headset',       'title' => '专业支持',     'desc' => '技术团队 7×24 小时响应，工单优先处理'],
        ['icon' => 'mdi-server',        'title' => '高性能节点',   'desc' => '全 SSD 存储，BGP 多线接入，低延迟体验'],
        ['icon' => 'mdi-backup-restore','title' => '自动备份',     'desc' => '每日自动备份数据，灾备无忧'],
        ['icon' => 'mdi-lock',          'title' => '安全防护',     'desc' => 'DDoS 清洗 + WAF 规则，网站安全有保障'],
    ];

    $planCards = [];
    foreach ($plans as $p) {
        $minPrice = 0;
        if ($p['price_month_cents'] > 0) {
            $minPrice = $p['price_month_cents'] / 100;
        }
        if ($p['price_year_cents'] > 0) {
            $yearPrice = $p['price_year_cents'] / 100;
            if ($minPrice == 0 || $yearPrice / 12 < $minPrice) {
                $minPrice = $yearPrice / 12;
            }
        }
        $priceStr = $minPrice > 0 ? '¥' . number_format($minPrice, 2) . ' 起/月' : '免费';
        $feats = [];
        if (!empty($p['spec_web']))    $feats[] = "网页空间 " . $p['spec_web'] . " MB";
        if (!empty($p['spec_sql']))    $feats[] = "数据库 " . $p['spec_sql'] . " MB";
        if (!empty($p['spec_flow']))   $feats[] = "月流量 " . $p['spec_flow'] . " GB";
        if (!empty($p['spec_domain'])) $feats[] = "可绑定 " . $p['spec_domain'] . " 个域名";
        $planCards[] = [
            'id'    => $p['id'],
            'name'  => $p['name'],
            'desc'  => $p['description'] ?? '',
            'price' => $priceStr,
            'feats' => $feats,
        ];
    }

    include __DIR__ . '/views/homepage.php';
    exit;
}

/* ============================================================
 *  3) 管理员页面
 * ============================================================ */
mnbt_register_page('admin', 'settings', 'views/admin/settings.php', '售卖前端设置');

mnbt_register_menu('admin', [
    'title' => '售卖前端',
    'icon'  => 'mdi-store',
    'order' => 59,
    'children' => [
        ['title' => '前端设置', 'page' => 'settings', 'icon' => 'mdi-cog', 'multitabs' => true],
    ],
]);

/* ============================================================
 *  4) 管理员 AJAX：保存设置
 * ============================================================ */
mnbt_register_ajax('admin', 'shop_frontend_save_settings', function () {
    mnbt_plugin_require_admin();
    $fields = ['site_title','site_logo','site_primary','site_accent','site_hero','site_footer','site_favicon'];
    foreach ($fields as $f) {
        mnbt_plugin_option_set('shop_frontend', $f, trim((string)($_POST[$f] ?? '')));
    }
    echo json_encode(['code'=>'保存成功'], JSON_UNESCAPED_UNICODE);
    exit;
});
