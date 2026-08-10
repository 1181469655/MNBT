<?php
/**
 * TDesign 主题 theme.php
 * 注册 user 和 admin 两个 scope 的菜单渲染器
 */
if (!defined('IN_CRONLITE')) exit;

// 菜单渲染由 _spa_boot.php 中的 _tdboot_render_plugin_menu_html 函数处理
// theme.php 仅需注册渲染器占位,实际 HTML 生成在 _spa_boot.php 中完成

if (function_exists('mnbt_register_theme_menu_renderer')) {
	mnbt_register_theme_menu_renderer('user', function ($items) {
		// 渲染由 _spa_boot.php 处理,此处返回空字符串
		return '';
	});

	mnbt_register_theme_menu_renderer('admin', function ($items) {
		// 渲染由 _spa_boot.php 处理,此处返回空字符串
		return '';
	});
}

// 主页自定义设置字段（后台「前端模板 → 主页内容」自动渲染）
// 底部备案信息：显示在主页页脚,自动链接到工信部/公安备案查询
if (function_exists('mnbt_register_home_setting')) {
	mnbt_register_home_setting([
		'key'         => 'beian_info',
		'label'       => 'ICP 备案信息',
		'type'        => 'text',
		'default'     => '',
		'placeholder' => '如：京ICP备12345678号',
		'hint'        => '显示在主页页脚,自动添加工信部备案查询链接',
	]);

	mnbt_register_home_setting([
		'key'         => 'ps_beian',
		'label'       => '公安备案信息',
		'type'        => 'text',
		'default'     => '',
		'placeholder' => '如：京公网安备11010802020266号',
		'hint'        => '显示在主页页脚（可选）,自动添加全国公安网站备案查询链接',
	]);

	// 页脚简介：显示在主页页脚公司简介栏
	mnbt_register_home_setting([
		'key'         => 'footer_about',
		'label'       => '页脚公司简介',
		'type'        => 'textarea',
		'default'     => '致力于为客户提供稳定、安全、高性能的虚拟主机与云计算服务。',
		'placeholder' => '显示在主页页脚公司简介栏的一句话介绍',
	]);

	// 联系方式：主页页脚「联系方式」栏与「联系我们」页面共用
	mnbt_register_home_setting([
		'key'         => 'contact_qq',
		'label'       => '客服 QQ（群号）',
		'type'        => 'text',
		'default'     => '994752422',
		'placeholder' => '如：994752422',
		'hint'        => '显示在页脚联系方式与联系我们页面',
	]);
	mnbt_register_home_setting([
		'key'         => 'contact_email',
		'label'       => '服务邮箱',
		'type'        => 'text',
		'default'     => 'support@mnbt.example',
		'placeholder' => '如：support@example.com',
		'hint'        => '显示在页脚联系方式与联系我们页面',
	]);
	mnbt_register_home_setting([
		'key'         => 'contact_address',
		'label'       => '公司地址',
		'type'        => 'text',
		'default'     => '北京市朝阳区 · 数据中心园区',
		'placeholder' => '如：北京市朝阳区 · 数据中心园区',
		'hint'        => '显示在页脚联系方式与联系我们页面',
	]);
	mnbt_register_home_setting([
		'key'         => 'contact_hours',
		'label'       => '客服支持时间',
		'type'        => 'text',
		'default'     => '工作日 9:00 - 21:00 · 7×24 工单系统',
		'placeholder' => '如：工作日 9:00 - 21:00 · 7×24 工单系统',
		'hint'        => '显示在联系我们页面客服支持栏',
	]);

	// 关于我们页面：平台简介与配图
	mnbt_register_home_setting([
		'key'         => 'about_intro',
		'label'       => '关于我们 · 平台简介',
		'type'        => 'textarea',
		'default'     => "MNBT 是一家面向企业和开发者的云计算服务商，专注于虚拟主机、云服务器、域名与安全防护等基础设施服务。依托高性能节点与全自动化部署体系，帮助用户以极低的成本快速上线业务。\n\n我们坚持\"稳定、安全、简单\"的产品理念，通过持续的技术迭代和完善的售后服务，已为大量个人站长与中小企业提供可靠的托管服务。",
		'placeholder' => '关于我们页面「平台简介」区块内容,空行分段',
	]);
	mnbt_register_home_setting([
		'key'         => 'about_image',
		'label'       => '关于我们 · 配图',
		'type'        => 'image',
		'default'     => '',
		'placeholder' => '上传或填写图片 URL（可选）,未设置时显示默认占位',
		'hint'        => '关于我们页面简介右侧的配图,建议宽高比 4:3',
	]);

	// 首页 Banner 文字：三张轮播图各自的标题/副标题/描述（留空回退内置默认文案）
	mnbt_register_home_setting([
		'key'         => 'banner_title_1',
		'label'       => 'Banner① 标题',
		'type'        => 'text',
		'default'     => '高性能虚拟主机',
		'placeholder' => '首页第一张轮播大标题',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_subtitle_1',
		'label'       => 'Banner① 副标题',
		'type'        => 'text',
		'default'     => '即买即用 · 自动开通 · 秒级部署',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_desc_1',
		'label'       => 'Banner① 描述',
		'type'        => 'textarea',
		'default'     => '全 SSD 存储与 BGP 多线接入，支付完成后自动开通，分钟级上线，为企业和开发者打造稳定高效的主机平台。',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_title_2',
		'label'       => 'Banner② 标题',
		'type'        => 'text',
		'default'     => '专业团队支持',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_subtitle_2',
		'label'       => 'Banner② 副标题',
		'type'        => 'text',
		'default'     => '7×24 小时全天候技术支持',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_desc_2',
		'label'       => 'Banner② 描述',
		'type'        => 'textarea',
		'default'     => '经验丰富的运维与开发团队随时待命，从建站到运维全程护航，让您专注于业务本身。',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_title_3',
		'label'       => 'Banner③ 标题',
		'type'        => 'text',
		'default'     => '企业级安全防护',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_subtitle_3',
		'label'       => 'Banner③ 副标题',
		'type'        => 'text',
		'default'     => 'DDoS 清洗 · WAF 规则 · 每日备份',
	]);
	mnbt_register_home_setting([
		'key'         => 'banner_desc_3',
		'label'       => 'Banner③ 描述',
		'type'        => 'textarea',
		'default'     => '内置安全防护体系与自动备份能力，SSL 一键签发，全面保障您的数据与业务安全。',
	]);
}
