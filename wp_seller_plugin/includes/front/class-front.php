<?php
/**
 * 前台：我的主机 Shortcode + 前端 AJAX。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

use MnbtWp\Services\Provision;
use MnbtWp\Woo\Woo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 前台控制器。
 */
class Front {

	/**
	 * 构造：注册 Shortcode 与 AJAX。
	 */
	public function __construct() {
		add_shortcode( 'mnbt_my_hosts', array( $this, 'shortcode_my_hosts' ) );
		add_action( 'wp_ajax_mnbtwp_host_action', array( $this, 'ajax_host_action' ) );
		add_action( 'wp_ajax_nopriv_mnbtwp_host_action', array( $this, 'ajax_denied' ) );
		add_action( 'wp_ajax_mnbtwp_create_renew', array( $this, 'ajax_create_renew' ) );
		add_action( 'wp_ajax_nopriv_mnbtwp_create_renew', array( $this, 'ajax_denied' ) );
	}

	/**
	 * 未登录 AJAX 拒绝。
	 */
	public function ajax_denied() {
		wp_send_json_error( __( '请先登录', 'wp-seller-plugin' ) );
	}

	/**
	 * [mnbt_my_hosts] Shortcode：列出当前用户主机。
	 *
	 * @param array $atts Shortcode 属性。
	 * @return string
	 */
	public function shortcode_my_hosts( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( '请先登录后查看您的主机。', 'wp-seller-plugin' ) . '</p>';
		}

		global $wpdb;
		$user_id = get_current_user_id();
		$hosts   = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mnbt_hosts WHERE user_id=%d ORDER BY id DESC", $user_id ),
			ARRAY_A
		);

		$provider_map = array();
		foreach ( $hosts as $h ) {
			if ( ! isset( $provider_map[ $h['provider_id'] ] ) ) {
				$provider_map[ $h['provider_id'] ] = Helpers::get_provider( $h['provider_id'] );
			}
		}

		ob_start();
		include MNBTWP_DIR . 'includes/front/views/my-hosts.php';
		return ob_get_clean();
	}

	/**
	 * 主机操作：start / stop / change_pass / delete。
	 */
	public function ajax_host_action() {
		check_ajax_referer( 'mnbtwp_front', 'nonce' );
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( '请先登录', 'wp-seller-plugin' ) );
		}

		$action = sanitize_key( $_POST['host_action'] ?? '' );
		$host_id = (int) ( $_POST['host_id'] ?? 0 );
		$host    = Helpers::get_host( $host_id );

		if ( ! $host || (int) $host['user_id'] !== get_current_user_id() ) {
			wp_send_json_error( __( '无权操作', 'wp-seller-plugin' ) );
		}
		$provider = Helpers::get_provider( $host['provider_id'] );
		if ( ! $provider ) {
			wp_send_json_error( __( '节点不可用', 'wp-seller-plugin' ) );
		}

		try {
			$client = Helpers::client_for_provider( $provider );
			switch ( $action ) {
				case 'start':
					$client->startSite( $host['username'] );
					break;
				case 'stop':
					$client->stopSite( $host['username'] );
					break;
				case 'change_pass':
					$password = sanitize_text_field( $_POST['password'] ?? '' );
					if ( mb_strlen( $password ) < 6 ) {
						wp_send_json_error( __( '密码不能少于 6 位', 'wp-seller-plugin' ) );
					}
					$client->changePassword( $host['username'], $password );
					global $wpdb;
					$wpdb->update(
						$wpdb->prefix . 'mnbt_hosts',
						array( 'password_enc' => Crypto::encrypt( $password ), 'updated_at' => current_time( 'mysql' ) ),
						array( 'id' => (int) $host['id'] )
					);
					break;
				case 'delete':
					$client->deleteHost( $host['username'] );
					global $wpdb;
					$wpdb->delete( $wpdb->prefix . 'mnbt_hosts', array( 'id' => (int) $host['id'] ), array( '%d' ) );
					break;
				default:
					wp_send_json_error( __( '未知操作', 'wp-seller-plugin' ) );
			}
			wp_send_json_success( __( '操作成功', 'wp-seller-plugin' ) );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * 创建续费订单并返回结账支付地址。
	 */
	public function ajax_create_renew() {
		check_ajax_referer( 'mnbtwp_front', 'nonce' );
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( '请先登录', 'wp-seller-plugin' ) );
		}
		$host_id = (int) ( $_POST['host_id'] ?? 0 );
		$months  = max( 1, (int) ( $_POST['months'] ?? 1 ) );
		$host    = Helpers::get_host( $host_id );
		if ( ! $host || (int) $host['user_id'] !== get_current_user_id() ) {
			wp_send_json_error( __( '无权操作', 'wp-seller-plugin' ) );
		}
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( __( '未启用 WooCommerce', 'wp-seller-plugin' ) );
		}
		try {
			$order = Woo::create_renew_order( $host_id, $months );
			wp_send_json_success(
				array(
					'checkout_url' => $order->get_checkout_payment_url(),
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
