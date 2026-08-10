<?php
/**
 * TDesign 主页主题（home scope）落地页入口
 *
 * 由 MPHX/frontend.php 的 mnbt_home_render() 渲染，注入 mnbt_home_data() 变量：
 *   $site_title / $site_logo / $site_primary / $site_hero / $site_footer / $favicon
 *   $notice / $show_notice / $show_plans / $logged_in / $has_shop / $has_user / $has_site
 *   $plans（套餐卡）/ $blocks（插件扩展区块）
 *   $url($path) 路由 URL / $coreUrl($path) 核心文件 URL
 *
 * 本入口加载 home SPA 构建产物（templates/tdesign/home/dist/），
 * 售卖系统全部页面（商店/订单/资产/余额/账户）由 SPA 通过 API 渲染。
 */
if (!defined('IN_CRONLITE')) { exit('Access Denied'); }

$td_dist = __DIR__ . '/dist';
$td_js   = $td_dist . '/assets/index.js';
$td_css  = $td_dist . '/assets/index.css';
$td_ver  = is_file($td_js) ? (string)@filemtime($td_js) : (string)time();

$boot = [
	'siteTitle'    => $site_title ?? 'MNBT',
	'siteLogo'     => $site_logo ?? '',
	'sitePrimary'  => $site_primary ?? '#4f46e5',
	'siteHero'     => $site_hero ?? '',
	'siteFooter'   => $site_footer ?? '',
	'beianInfo'    => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('beian_info', '') : ''),
	'policeBeian'  => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('ps_beian', '') : ''),
	// 页脚/联系方式/关于/首页 banner 文字（主题自定义设置，空值回退内置默认）
	'footerAbout'  => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('footer_about', '致力于为客户提供稳定、安全、高性能的虚拟主机与云计算服务。') : ''),
	'contactQq'    => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('contact_qq', '994752422') : ''),
	'contactEmail' => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('contact_email', 'support@mnbt.example') : ''),
	'contactAddress' => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('contact_address', '北京市朝阳区 · 数据中心园区') : ''),
	'contactHours' => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('contact_hours', '工作日 9:00 - 21:00 · 7×24 工单系统') : ''),
	'aboutIntro'   => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('about_intro', '') : ''),
	'aboutImage'   => (function_exists('mnbt_home_asset') && function_exists('mnbt_home_theme_setting') ? mnbt_home_asset((string)mnbt_home_theme_setting('about_image', '')) : ''),
	'bannerTexts'  => [
		[
			'title'       => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_title_1', '高性能虚拟主机') : '高性能虚拟主机'),
			'subtitle'    => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_subtitle_1', '即买即用 · 自动开通 · 秒级部署') : '即买即用 · 自动开通 · 秒级部署'),
			'description' => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_desc_1', '全 SSD 存储与 BGP 多线接入，支付完成后自动开通，分钟级上线，为企业和开发者打造稳定高效的主机平台。') : '全 SSD 存储与 BGP 多线接入，支付完成后自动开通，分钟级上线，为企业和开发者打造稳定高效的主机平台。'),
		],
		[
			'title'       => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_title_2', '专业团队支持') : '专业团队支持'),
			'subtitle'    => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_subtitle_2', '7×24 小时全天候技术支持') : '7×24 小时全天候技术支持'),
			'description' => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_desc_2', '经验丰富的运维与开发团队随时待命，从建站到运维全程护航，让您专注于业务本身。') : '经验丰富的运维与开发团队随时待命，从建站到运维全程护航，让您专注于业务本身。'),
		],
		[
			'title'       => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_title_3', '企业级安全防护') : '企业级安全防护'),
			'subtitle'    => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_subtitle_3', 'DDoS 清洗 · WAF 规则 · 每日备份') : 'DDoS 清洗 · WAF 规则 · 每日备份'),
			'description' => (function_exists('mnbt_home_theme_setting') ? (string)mnbt_home_theme_setting('banner_desc_3', '内置安全防护体系与自动备份能力，SSL 一键签发，全面保障您的数据与业务安全。') : '内置安全防护体系与自动备份能力，SSL 一键签发，全面保障您的数据与业务安全。'),
		],
	],
	'favicon'      => $favicon ?? '',
	'notice'       => $notice ?? '',
	'showNotice'   => !empty($show_notice),
	'showPlans'    => !empty($show_plans),
	'loggedIn'     => !empty($logged_in),
	'hasShop'      => !empty($has_shop),
	'hasBalance'   => !empty($has_balance),
	'hasUser'      => !empty($has_user),
	'hasSite'      => !empty($has_site),
	'plans'        => $plans ?? [],
	'blocks'       => $blocks ?? [],
	'base'         => function_exists('mnbt_home_base') ? mnbt_home_base() : '',
	'conf'         => $conf ?? [],
	'theme'        => 'tdesign',
	'version'      => '0.3.0',
	'entry'        => 'landing',
];

// SPA 需要访问的 API 入口（index.php?_r=/xxx/api/xxx）
$boot['routeBase'] = (function_exists('mnbt_home_base') ? mnbt_home_base() : '') . '/index.php?_r=';
// 核心文件入口（如 user/、admin/）
$boot['coreBase']  = function_exists('mnbt_home_core_url') ? mnbt_home_core_url('') : '/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars($site_title ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?></title>
<?php if (!empty($favicon)): ?><link rel="icon" href="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>" /><?php endif; ?>
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_asset_url('css/materialdesignicons.min.css'), ENT_QUOTES, 'UTF-8') ?>" />
<?php if (is_file($td_css)): ?>
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_theme_url('dist/assets/index.css', 'home'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>" />
<?php endif; ?>
<style>
  html, body, #app { margin: 0; padding: 0; min-height: 100%; }
  .td-boot-missing { max-width: 540px; margin: 12vh auto; padding: 32px; border-radius: 12px; background: #fff; border: 1px solid #e7e7e7; font-family: system-ui, -apple-system, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif; color: #1a2e28; }
  .td-boot-missing h2 { margin: 0 0 12px; font-size: 18px; color: #d54941; }
  .td-boot-missing code { background: #f3f3f3; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
  .td-boot-missing p { margin: 10px 0; line-height: 1.7; font-size: 14px; }
</style>
</head>
<body>
<div id="app">
<?php if (!is_file($td_js)): ?>
  <div class="td-boot-missing">
    <h2>TDesign 主页主题尚未构建</h2>
    <p>请在服务器或本机执行:</p>
    <p><code>cd templates/tdesign/spa &amp;&amp; npm install &amp;&amp; npm run build:home</code></p>
    <p>构建产物应位于 <code>templates/tdesign/home/dist/</code></p>
  </div>
<?php endif; ?>
</div>
<script>
window.__TD_BOOT__ = <?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<?php if (is_file($td_js)): ?>
<script type="module" src="<?= htmlspecialchars(mnbt_theme_url('dist/assets/index.js', 'home'), ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>"></script>
<?php endif; ?>
</body>
</html>
