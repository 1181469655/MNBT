<?php
/**
 * official_site 插件 - 官网首页引擎（V1.88 自核心独立主页系统迁入）
 *
 * 站点根路径 / 的落地页由本插件经 mnbt_register_home（priority 9000）接管渲染，
 * 大优先级保持原核心默认主页的"最后兜底"语义：shop_frontend 等第三方首页接管
 * （priority 100）仍然优先生效。配置存于插件 options（MN_plugin_option），
 * 首次引导时从 MN_config.home_* 旧列一次性迁移（V1.84~V1.87 升级路径）。
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/* ============================================================
 *  配置读取（插件 options）
 * ============================================================ */

function official_site_home_option($key, $default = null)
{
	$v = mnbt_plugin_option_get('official_site', $key, null);
	return ($v !== null && $v !== '') ? $v : $default;
}

function official_site_home_enabled()
{
	return official_site_home_option('home_enable', 'true') === 'true';
}

/**
 * 主页内容设置字段定义（原 tdesign theme.php 经 mnbt_register_home_setting 声明的
 * 字段，随主页一起迁入插件；后台「官网内容 → 主页设置」页据此渲染与保存）。
 */
function official_site_home_fields()
{
	static $fields = null;
	if ($fields !== null) {
		return $fields;
	}
	$fields = [
		['key' => 'beian_info', 'label' => 'ICP 备案信息', 'type' => 'text', 'default' => '', 'placeholder' => '如：京ICP备12345678号', 'hint' => '显示在主页页脚,自动添加工信部备案查询链接'],
		['key' => 'ps_beian', 'label' => '公安备案信息', 'type' => 'text', 'default' => '', 'placeholder' => '如：京公网安备11010802020266号', 'hint' => '显示在主页页脚（可选）,自动添加全国公安网站备案查询链接'],
		['key' => 'footer_about', 'label' => '页脚公司简介', 'type' => 'textarea', 'default' => '致力于为客户提供稳定、安全、高性能的虚拟主机与云计算服务。', 'placeholder' => '显示在主页页脚公司简介栏的一句话介绍', 'hint' => ''],
		['key' => 'contact_qq', 'label' => '客服 QQ（群号）', 'type' => 'text', 'default' => '994752422', 'placeholder' => '如：994752422', 'hint' => '显示在页脚联系方式与联系我们页面'],
		['key' => 'contact_email', 'label' => '服务邮箱', 'type' => 'text', 'default' => 'support@mnbt.example', 'placeholder' => '如：support@example.com', 'hint' => '显示在页脚联系方式与联系我们页面'],
		['key' => 'contact_address', 'label' => '公司地址', 'type' => 'text', 'default' => '北京市朝阳区 · 数据中心园区', 'placeholder' => '', 'hint' => '显示在页脚联系方式与联系我们页面'],
		['key' => 'contact_hours', 'label' => '客服支持时间', 'type' => 'text', 'default' => '工作日 9:00 - 21:00 · 7×24 工单系统', 'placeholder' => '', 'hint' => '显示在联系我们页面客服支持栏'],
		['key' => 'about_intro', 'label' => '关于我们 · 平台简介', 'type' => 'textarea', 'default' => "MNBT 是一家面向企业和开发者的云计算服务商，专注于虚拟主机、云服务器、域名与安全防护等基础设施服务。依托高性能节点与全自动化部署体系，帮助用户以极低的成本快速上线业务。\n\n我们坚持\"稳定、安全、简单\"的产品理念，通过持续的技术迭代和完善的售后服务，已为大量个人站长与中小企业提供可靠的托管服务。", 'placeholder' => '关于我们页面「平台简介」区块内容,空行分段', 'hint' => ''],
		['key' => 'about_image', 'label' => '关于我们 · 配图', 'type' => 'image', 'default' => '', 'placeholder' => '上传或填写图片 URL（可选）,未设置时显示默认占位', 'hint' => '关于我们页面简介右侧的配图,建议宽高比 4:3'],
		['key' => 'banner_title_1', 'label' => 'Banner① 标题', 'type' => 'text', 'default' => '高性能虚拟主机', 'placeholder' => '首页第一张轮播大标题', 'hint' => ''],
		['key' => 'banner_subtitle_1', 'label' => 'Banner① 副标题', 'type' => 'text', 'default' => '即买即用 · 自动开通 · 秒级部署', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_desc_1', 'label' => 'Banner① 描述', 'type' => 'textarea', 'default' => '全 SSD 存储与 BGP 多线接入，支付完成后自动开通，分钟级上线，为企业和开发者打造稳定高效的主机平台。', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_title_2', 'label' => 'Banner② 标题', 'type' => 'text', 'default' => '专业团队支持', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_subtitle_2', 'label' => 'Banner② 副标题', 'type' => 'text', 'default' => '7×24 小时全天候技术支持', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_desc_2', 'label' => 'Banner② 描述', 'type' => 'textarea', 'default' => '经验丰富的运维与开发团队随时待命，从建站到运维全程护航，让您专注于业务本身。', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_title_3', 'label' => 'Banner③ 标题', 'type' => 'text', 'default' => '企业级安全防护', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_subtitle_3', 'label' => 'Banner③ 副标题', 'type' => 'text', 'default' => 'DDoS 清洗 · WAF 规则 · 每日备份', 'placeholder' => '', 'hint' => ''],
		['key' => 'banner_desc_3', 'label' => 'Banner③ 描述', 'type' => 'textarea', 'default' => '内置安全防护体系与自动备份能力，SSL 一键签发，全面保障您的数据与业务安全。', 'placeholder' => '', 'hint' => ''],
	];
	return $fields;
}

/** 主页内容设置（JSON，原 MN_config.home_theme_settings）全部已存值 */
function official_site_home_settings_all()
{
	$v = mnbt_plugin_option_get('official_site', 'home_theme_settings', '');
	// mnbt_plugin_option_get 对 JSON 字符串会自动解码为数组
	if (is_array($v)) {
		return $v;
	}
	if (is_string($v) && $v !== '') {
		$decoded = json_decode($v, true);
		if (is_array($decoded)) {
			return $decoded;
		}
	}
	return [];
}

/** 读取单个内容设置值（模板用）：已存值 → 字段默认值 → 调用方默认值 */
function official_site_home_setting(string $key, $default = null)
{
	$all = official_site_home_settings_all();
	if (isset($all[$key]) && $all[$key] !== '') {
		return $all[$key];
	}
	foreach (official_site_home_fields() as $f) {
		if ($f['key'] === $key) {
			return $f['default'];
		}
	}
	return $default;
}

/* ============================================================
 *  旧配置一次性迁移（MN_config.home_* 列 → 插件 options）
 * ============================================================ */

function official_site_home_migrate_legacy()
{
	if (mnbt_plugin_option_get('official_site', 'home_legacy_migrated', '') !== '') {
		return;
	}
	mnbt_plugin_option_set('official_site', 'home_legacy_migrated', 'v1');
	global $DB, $siteid;
	if (!isset($DB) || !is_object($DB)) {
		return;
	}
	// 旧列不存在（V1.88 起全新安装）时直接结束；容忍升级 SQL 已先行 DROP
	$cols = [];
	foreach ((array)@$DB->get_all_prepare("SHOW COLUMNS FROM `MN_config` LIKE 'home\\_%'") ?: [] as $c) {
		if (!empty($c['Field'])) {
			$cols[] = $c['Field'];
		}
	}
	if (!$cols) {
		return;
	}
	$row = $DB->get_row_prepare(
		"SELECT `" . implode('`,`', $cols) . "` FROM MN_config WHERE id=? LIMIT 1",
		[isset($siteid) && $siteid ? (int)$siteid : 1]
	);
	if (!$row) {
		return;
	}
	foreach ($cols as $c) {
		// home_theme 是已下线的"主页主题"选择，不再迁移
		if ($c === 'home_theme' || (string)$row[$c] === '') {
			continue;
		}
		mnbt_plugin_option_set('official_site', $c, (string)$row[$c]);
	}
}

/* ============================================================
 *  数据组装与渲染（原 mnbt_home_data / mnbt_home_render）
 * ============================================================ */

/** 组装主页数据（模板注入变量） */
function official_site_home_data(): array
{
	global $DB, $conf;

	$siteTitle = official_site_home_option('home_title', '') ?: ($conf['name'] ?? 'MNBT');

	$data = [
		'site_title'    => $siteTitle,
		'site_logo'     => mnbt_home_asset(official_site_home_option('home_logo', '') ?: 'imsetes/upload_logo/logo.index.png'),
		'site_primary'  => official_site_home_option('home_primary', '#4f46e5'),
		'site_hero'     => official_site_home_option('home_hero', '高性能虚拟主机，即买即用'),
		'site_footer'   => official_site_home_option('home_footer', '') ?: ($conf['hxp'] ?? ''),
		'favicon'       => mnbt_home_asset(official_site_home_option('home_favicon', '') ?: 'imsetes/images/logo-ico.png'),
		// 公告（系统公告 MN_config.gg）与区块开关
		'notice'        => $conf['gg'] ?? '',
		'show_notice'   => official_site_home_option('home_show_notice', 'true') === 'true',
		'show_plans'    => official_site_home_option('home_show_plans', 'true') === 'true',
		// 登录态与能力探测（不强依赖插件）
		'logged_in'     => isset($_COOKIE['user_token']) && $_COOKIE['user_token'] !== '',
		'has_shop'      => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('hosting_shop'),
		'has_balance'   => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('balance'),
		'has_user'      => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('user_info'),
		'has_site'      => true,
		'has_docker'    => function_exists('mnbt_plugin_enabled') && mnbt_plugin_enabled('docker_shop'),
		// 业务数据
		'plans'         => [],
		'blocks'        => [],
		// URL 生成回调
		'url'           => function (string $path = '') { return mnbt_home_url($path); },
		'coreUrl'       => function (string $path = '') { return mnbt_home_core_url($path); },
	];

	// 套餐区（hosting_shop 启用且有有效套餐时展示，查询失败静默降级为空）
	if ($data['has_shop'] && $data['show_plans'] && isset($DB)) {
		$rows = @$DB->get_all_prepare("SELECT * FROM MN_plugin_hosting_plan WHERE status='active' ORDER BY sort ASC, id ASC") ?: [];
		foreach ($rows as $p) {
			$minPrice = 0;
			if ((int)($p['price_month_cents'] ?? 0) > 0) {
				$minPrice = (int)$p['price_month_cents'] / 100;
			}
			if ((int)($p['price_year_cents'] ?? 0) > 0) {
				$yearPrice = (int)$p['price_year_cents'] / 100;
				if ($minPrice == 0 || $yearPrice / 12 < $minPrice) {
					$minPrice = $yearPrice / 12;
				}
			}
			$feats = [];
			if (!empty($p['spec_web']))    $feats[] = '网页空间 ' . $p['spec_web'] . ' MB';
			if (!empty($p['spec_sql']))    $feats[] = '数据库 ' . $p['spec_sql'] . ' MB';
			if (!empty($p['spec_flow']))   $feats[] = '月流量 ' . $p['spec_flow'] . ' GB';
			if (!empty($p['spec_domain'])) $feats[] = '可绑定 ' . $p['spec_domain'] . ' 个域名';
			$data['plans'][] = [
				'id'    => (int)$p['id'],
				'name'  => (string)$p['name'],
				'desc'  => (string)($p['description'] ?? ''),
				'price' => $minPrice > 0 ? '¥' . number_format($minPrice, 2) . ' 起/月' : '免费',
				'feats' => $feats,
			];
		}
	}

	// 插件扩展区块（home.blocks 过滤器，按 order 升序；仅取结构化字段）
	if (function_exists('mnbt_apply_filters')) {
		$blocks = mnbt_apply_filters('home.blocks', []);
		if (is_array($blocks)) {
			$clean = [];
			foreach ($blocks as $b) {
				if (!is_array($b) || !isset($b['html'])) {
					continue;
				}
				$clean[] = [
					'id'    => (string)($b['id'] ?? ''),
					'title' => (string)($b['title'] ?? ''),
					'html'  => (string)$b['html'],
					'order' => (int)($b['order'] ?? 50),
				];
			}
			usort($clean, function ($a, $b) {
				return $a['order'] - $b['order'];
			});
			$data['blocks'] = $clean;
		}
	}

	return $data;
}

/** 渲染官网首页（组装数据 → include 插件入口模板 → 终止请求） */
function official_site_home_render(): void
{
	$path = mnbt_plugin_path('official_site') . 'views/tdesign/home.php';
	if (!is_file($path)) {
		http_response_code(500);
		echo 'Home template not found';
		exit;
	}
	if (!headers_sent()) {
		@header('Content-Type: text/html; charset=UTF-8');
	}
	$vars = official_site_home_data();
	$bufferLevel = ob_get_level();
	ob_start('mnbt_csrf_inject_html');
	try {
		// 入口模板在函数作用域内 include，展开全局变量（$conf/$DB 等）
		extract($GLOBALS, EXTR_SKIP);
		extract($vars, EXTR_SKIP);
		include $path;
	} finally {
		while (ob_get_level() > $bufferLevel) {
			ob_end_flush();
		}
	}
	exit;
}

/** 首页接管回调（bootstrap 注册，priority 9000；后台可经主页设置临时关停） */
function official_site_home_handle($ctx)
{
	if (!official_site_home_enabled()) {
		return false;
	}
	official_site_home_render();
	return true;
}
