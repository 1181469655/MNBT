<?php
/**
 * official_site 插件 —— 官网首页（home 作用域）落地页入口
 *
 * 由 lib/home.php 的 official_site_home_render() 渲染，注入 official_site_home_data() 变量：
 *   $site_title / $site_logo / $site_primary / $site_hero / $site_footer / $favicon
 *   $notice / $show_notice / $show_plans / $logged_in / $has_shop / $has_user / $has_site / $has_docker
 *   $plans（套餐卡）/ $blocks（插件扩展区块）
 *   $url($path) 路由 URL / $coreUrl($path) 核心文件 URL
 *
 * 本入口经 vue3-sfc-loader 免构建加载 home SPA 源码（本插件 assets/spa/，V1.88 起
 * 随主页迁入插件），售卖系统全部页面（商店/订单/资产/余额/账户）由 SPA 通过 API 渲染；
 * 共享运行时（td-boot.js）与共享静态资源（spa/src/shared/）仍由 tdesign 主题提供。
 */
if (!defined('IN_CRONLITE')) { exit('Access Denied'); }

$td_base = mnbt_home_base();
$td_theme_base = $td_base . '/templates/tdesign/';

$td_boot_file = MNBT_THEME_ROOT . 'tdesign/assets/td-boot.js';
$td_ver = is_file($td_boot_file) ? (string)@filemtime($td_boot_file) : (string)time();

$boot = [
	'siteTitle'    => $site_title ?? 'MNBT',
	'siteLogo'     => $site_logo ?? '',
	'sitePrimary'  => $site_primary ?? '#4f46e5',
	'siteHero'     => $site_hero ?? '',
	'siteFooter'   => $site_footer ?? '',
	'beianInfo'    => (string)official_site_home_setting('beian_info', ''),
	'policeBeian'  => (string)official_site_home_setting('ps_beian', ''),
	// 页脚/联系方式/关于/首页 banner 文字（主页内容设置，空值回退内置默认）
	'footerAbout'  => (string)official_site_home_setting('footer_about', '致力于为客户提供稳定、安全、高性能的虚拟主机与云计算服务。'),
	'contactQq'    => (string)official_site_home_setting('contact_qq', '994752422'),
	'contactEmail' => (string)official_site_home_setting('contact_email', 'support@mnbt.example'),
	'contactAddress' => (string)official_site_home_setting('contact_address', '北京市朝阳区 · 数据中心园区'),
	'contactHours' => (string)official_site_home_setting('contact_hours', '工作日 9:00 - 21:00 · 7×24 工单系统'),
	'aboutIntro'   => (string)official_site_home_setting('about_intro', ''),
	'aboutImage'   => mnbt_home_asset((string)official_site_home_setting('about_image', '')),
	'bannerTexts'  => [
		[
			'title'       => (string)official_site_home_setting('banner_title_1', '高性能虚拟主机'),
			'subtitle'    => (string)official_site_home_setting('banner_subtitle_1', '即买即用 · 自动开通 · 秒级部署'),
			'description' => (string)official_site_home_setting('banner_desc_1', '全 SSD 存储与 BGP 多线接入，支付完成后自动开通，分钟级上线，为企业和开发者打造稳定高效的主机平台。'),
		],
		[
			'title'       => (string)official_site_home_setting('banner_title_2', '专业团队支持'),
			'subtitle'    => (string)official_site_home_setting('banner_subtitle_2', '7×24 小时全天候技术支持'),
			'description' => (string)official_site_home_setting('banner_desc_2', '经验丰富的运维与开发团队随时待命，从建站到运维全程护航，让您专注于业务本身。'),
		],
		[
			'title'       => (string)official_site_home_setting('banner_title_3', '企业级安全防护'),
			'subtitle'    => (string)official_site_home_setting('banner_subtitle_3', 'DDoS 清洗 · WAF 规则 · 每日备份'),
			'description' => (string)official_site_home_setting('banner_desc_3', '内置安全防护体系与自动备份能力，SSL 一键签发，全面保障您的数据与业务安全。'),
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
	'hasDocker'    => !empty($has_docker),
	'hasZjmf'      => function_exists('mnbt_plugin_enabled') ? mnbt_plugin_enabled('zjmfmanager_reserve') : false,
	'plans'        => $plans ?? [],
	'blocks'       => $blocks ?? [],
	'base'         => $td_base,
	'conf'         => $conf ?? [],
	'theme'        => 'tdesign',
	'version'      => '0.4.0',
	'scope'        => 'home',
	// SPA 源码根：插件自带（home 作用域），@/ → 本插件 assets/spa/
	'srcBase'      => $td_base . '/app_plugins/official_site/assets/spa/',
	// @/shared/ 仍指向 tdesign 主题共享源码（登录背景图、共享样式等）
	'srcAliases'   => ['@/shared/' => $td_theme_base . 'spa/src/shared/'],
	'themeBase'    => $td_theme_base,
	'vendorBase'   => $td_base . '/imsetes/vendor/',
	'entry'        => 'landing',
];

// SPA 需要访问的 API 入口（index.php?_r=/xxx/api/xxx）
$boot['routeBase'] = $td_base . '/index.php?_r=';
// 核心文件入口（如 user/、admin/）
$boot['coreBase']  = mnbt_home_core_url('');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?= htmlspecialchars($site_title ?? 'MNBT', ENT_QUOTES, 'UTF-8') ?></title>
<?php if (!empty($favicon)): ?><link rel="icon" href="<?= htmlspecialchars($favicon, ENT_QUOTES, 'UTF-8') ?>" /><?php endif; ?>
<link rel="stylesheet" href="<?= htmlspecialchars(mnbt_asset_url('css/materialdesignicons.min.css'), ENT_QUOTES, 'UTF-8') ?>" />
<link rel="stylesheet" href="<?= htmlspecialchars($td_base . '/imsetes/vendor/tdesign/tdesign.min.css', ENT_QUOTES, 'UTF-8') ?>" />
<style>
  html, body, #app { margin: 0; padding: 0; min-height: 100%; }
  .td-boot-msg { max-width: 540px; margin: 12vh auto; padding: 32px; border-radius: 12px; background: #fff; border: 1px solid #e7e7e7; font-family: system-ui, -apple-system, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif; color: #1a2e28; text-align: center; }
  .td-boot-msg h2 { margin: 0 0 12px; font-size: 18px; color: #d54941; }
  .td-boot-msg pre { text-align: left; white-space: pre-wrap; word-break: break-all; background: #f3f3f3; padding: 12px; border-radius: 6px; font-size: 12px; max-height: 320px; overflow: auto; }
  .td-boot-msg p { margin: 10px 0; line-height: 1.7; font-size: 14px; color: #4b5b5b; }
  .td-boot-spinner { width: 36px; height: 36px; margin: 8px auto 12px; border: 3px solid #dcdcdc; border-top-color: #0052d9; border-radius: 50%; animation: td-boot-spin 0.8s linear infinite; }
  @keyframes td-boot-spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div id="app">
  <div id="td-boot-status" class="td-boot-msg td-boot-loading">
    <div class="td-boot-spinner"></div>
    <p>正在加载…</p>
  </div>
</div>
<script>
window.__TD_BOOT__ = <?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/vue/vue.global.prod.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/vue-router/vue-router.global.prod.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/axios/axios.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/tdesign/tdesign.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_base . '/imsetes/vendor/vue3-sfc-loader/vue3-sfc-loader.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($td_theme_base . 'assets/td-boot.js', ENT_QUOTES, 'UTF-8') ?>?v=<?= $td_ver ?>"></script>
</body>
</html>
