<?php
/**
 * Plugin Name:       MNBT 虚拟主机销售
 * Plugin URI:        https://github.com/MNBT/wp-seller-plugin
 * Description:       在 WordPress 上售卖 MNBT（梦奈宝塔）虚拟主机：商品下单、自动开通、我的主机与到期管理，向 cPanel 式体验靠拢。
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            MNBT
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-seller-plugin
 * Domain Path:       /languages
 *
 * @package MnbtWp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MNBTWP_VERSION', '0.1.0' );
define( 'MNBTWP_FILE', __FILE__ );
define( 'MNBTWP_DIR', plugin_dir_path( __FILE__ ) );
define( 'MNBTWP_URL', plugin_dir_url( __FILE__ ) );

require_once MNBTWP_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( '\MnbtWp\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\MnbtWp\Plugin', 'deactivate' ) );

/**
 * 插件入口单例。
 *
 * @return \MnbtWp\Plugin
 */
function mnbtwp() {
	return \MnbtWp\Plugin::instance();
}

mnbtwp();
