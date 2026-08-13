<?php
/**
 * 插件生命周期：加载、文本域、激活/停用入口。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 插件主类（单例）。
 */
final class Plugin {

	/**
	 * 单例实例。
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * 获取单例。
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 构造：加载依赖并挂载钩子。
	 */
	private function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * 加载插件类文件（按依赖顺序）。
	 */
	private function includes() {
		require_once MNBTWP_DIR . 'includes/class-activator.php';
		require_once MNBTWP_DIR . 'includes/class-crypto.php';
		require_once MNBTWP_DIR . 'includes/class-logger.php';
		require_once MNBTWP_DIR . 'includes/class-helpers.php';
		require_once MNBTWP_DIR . 'includes/mnbt/class-exception.php';
		require_once MNBTWP_DIR . 'includes/mnbt/interface-provider.php';
		require_once MNBTWP_DIR . 'includes/mnbt/class-client.php';
		require_once MNBTWP_DIR . 'includes/services/class-provision.php';
		require_once MNBTWP_DIR . 'includes/services/class-billing.php';
		require_once MNBTWP_DIR . 'includes/services/class-cron.php';
		require_once MNBTWP_DIR . 'includes/woocommerce/class-woo.php';
		require_once MNBTWP_DIR . 'includes/front/class-front.php';

		if ( is_admin() ) {
			require_once MNBTWP_DIR . 'includes/admin/class-admin.php';
		}
	}

	/**
	 * 挂载全局钩子。
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		\MnbtWp\Services\Cron::init();

		if ( class_exists( 'WooCommerce' ) ) {
			new \MnbtWp\Woo\Woo();
		}

		if ( ! is_admin() ) {
			new Front();
		} else {
			new Admin();
		}
	}

	/**
	 * 加载语言包。
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-seller-plugin', false, dirname( plugin_basename( MNBTWP_FILE ) ) . '/languages' );
	}

	/**
	 * 激活钩子。
	 */
	public static function activate() {
		require_once MNBTWP_DIR . 'includes/class-activator.php';
		Activator::activate();
	}

	/**
	 * 停用钩子。
	 */
	public static function deactivate() {
		require_once MNBTWP_DIR . 'includes/class-activator.php';
		Activator::deactivate();
	}
}
