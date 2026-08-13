<?php
/**
 * 后台管理：菜单、连接配置页、API 日志页与 AJAX 处理。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

use MnbtWp\Mnbt\Client;
use MnbtWp\Mnbt\Exception as MnbtException;
use MnbtWp\Services\Provision;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 后台管理类。
 */
class Admin {

	/**
	 * 构造：挂载钩子。
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'wp_ajax_mnbtwp_save_provider', array( $this, 'ajax_save_provider' ) );
		add_action( 'wp_ajax_mnbtwp_get_provider', array( $this, 'ajax_get_provider' ) );
		add_action( 'wp_ajax_mnbtwp_delete_provider', array( $this, 'ajax_delete_provider' ) );
		add_action( 'wp_ajax_mnbtwp_test_provider', array( $this, 'ajax_test_provider' ) );
		add_action( 'wp_ajax_mnbtwp_admin_host_action', array( $this, 'ajax_admin_host_action' ) );
		add_action( 'wp_ajax_mnbtwp_admin_retry_provision', array( $this, 'ajax_admin_retry_provision' ) );
	}

	/**
	 * 注册菜单。
	 */
	public function menu() {
		add_menu_page(
			__( 'MNBT 主机销售', 'wp-seller-plugin' ),
			__( 'MNBT 主机销售', 'wp-seller-plugin' ),
			'manage_options',
			'mnbtwp',
			array( $this, 'page_settings' ),
			'dashicons-cloud',
			56
		);
		add_submenu_page(
			'mnbtwp',
			__( '主机管理', 'wp-seller-plugin' ),
			__( '主机管理', 'wp-seller-plugin' ),
			'manage_options',
			'mnbtwp-hosts',
			array( $this, 'page_hosts' )
		);
		add_submenu_page(
			'mnbtwp',
			__( '连接配置', 'wp-seller-plugin' ),
			__( '连接配置', 'wp-seller-plugin' ),
			'manage_options',
			'mnbtwp',
			array( $this, 'page_settings' )
		);
		add_submenu_page(
			'mnbtwp',
			__( 'API 日志', 'wp-seller-plugin' ),
			__( 'API 日志', 'wp-seller-plugin' ),
			'manage_options',
			'mnbtwp-logs',
			array( $this, 'page_logs' )
		);
	}

	/**
	 * 主机总览页。
	 */
	public function page_hosts() {
		global $wpdb;
		$hosts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mnbt_hosts ORDER BY id DESC LIMIT 500", ARRAY_A );
		$provider_map = array();
		foreach ( $hosts as $h ) {
			if ( ! isset( $provider_map[ $h['provider_id'] ] ) ) {
				$provider_map[ $h['provider_id'] ] = Helpers::get_provider( $h['provider_id'] );
			}
		}
		$failed_orders = array();
		if ( class_exists( 'WooCommerce' ) ) {
			$failed_orders = wc_get_orders(
				array(
					'limit'        => 50,
					'status'       => array( 'pending', 'processing', 'on-hold', 'completed' ),
					'meta_key'     => '_mnbtwp_provision_error',
					'meta_compare' => 'EXISTS',
				)
			);
		}
		include MNBTWP_DIR . 'includes/admin/views/hosts.php';
	}

	/**
	 * 连接配置页。
	 */
	public function page_settings() {
		global $wpdb;
		$providers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mnbt_providers ORDER BY id ASC", ARRAY_A );
		include MNBTWP_DIR . 'includes/admin/views/settings.php';
	}

	/**
	 * API 日志页。
	 */
	public function page_logs() {
		global $wpdb;
		$logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mnbt_api_log ORDER BY id DESC LIMIT 200", ARRAY_A );
		include MNBTWP_DIR . 'includes/admin/views/logs.php';
	}

	/**
	 * 保存/更新节点配置。
	 */
	public function ajax_save_provider() {
		check_ajax_referer( 'mnbtwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '权限不足', 'wp-seller-plugin' ) );
		}

		global $wpdb;
		$id      = (int) ( $_POST['id'] ?? 0 );
		$name    = sanitize_text_field( $_POST['name'] ?? '' );
		$api_url = esc_url_raw( trim( (string) ( $_POST['api_url'] ?? '' ) ) );
		$btdh    = sanitize_text_field( $_POST['btdh'] ?? '' );
		$mn_key  = sanitize_text_field( $_POST['mn_key'] ?? '' );
		$mn_keye = sanitize_text_field( $_POST['mn_keye'] ?? '' );
		$mn_vs   = max( 15, (int) ( $_POST['mn_vs'] ?? 15 ) );
		$qk      = ( ! empty( $_POST['qk'] ) && '1' === (string) $_POST['qk'] ) ? 'true' : 'false';

		if ( '' === $name || '' === $api_url || '' === $btdh || '' === $mn_key || '' === $mn_keye ) {
			wp_send_json_error( __( '参数不能为空', 'wp-seller-plugin' ) );
		}
		if ( ! preg_match( '#^https?://#i', $api_url ) ) {
			wp_send_json_error( __( 'API 地址需以 http(s):// 开头', 'wp-seller-plugin' ) );
		}

		$table = $wpdb->prefix . 'mnbt_providers';

		if ( $id > 0 ) {
			$current = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", $id ), ARRAY_A );
			if ( ! $current ) {
				wp_send_json_error( __( '节点不存在', 'wp-seller-plugin' ) );
			}
			// 密钥未修改则保留原密文，避免每次保存变更密文。
			$mn_key_enc  = ( Crypto::decrypt( $current['mn_key_enc'] ) === $mn_key ) ? $current['mn_key_enc'] : Crypto::encrypt( $mn_key );
			$mn_keye_enc = ( Crypto::decrypt( $current['mn_keye_enc'] ) === $mn_keye ) ? $current['mn_keye_enc'] : Crypto::encrypt( $mn_keye );

			$wpdb->update(
				$table,
				array(
					'name'        => $name,
					'api_url'     => $api_url,
					'btdh'        => $btdh,
					'mn_key_enc'  => $mn_key_enc,
					'mn_keye_enc' => $mn_keye_enc,
					'mn_vs'       => $mn_vs,
					'qk'          => $qk,
					'updated_at'  => current_time( 'mysql' ),
				),
				array( 'id' => $id )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'name'        => $name,
					'api_url'     => $api_url,
					'btdh'        => $btdh,
					'mn_key_enc'  => Crypto::encrypt( $mn_key ),
					'mn_keye_enc' => Crypto::encrypt( $mn_keye ),
					'mn_vs'       => $mn_vs,
					'qk'          => $qk,
					'created_at'  => current_time( 'mysql' ),
					'updated_at'  => current_time( 'mysql' ),
				)
			);
		}

		if ( $wpdb->last_error ) {
			wp_send_json_error( __( '保存失败', 'wp-seller-plugin' ) . '：' . $wpdb->last_error );
		}
		wp_send_json_success( __( '保存成功', 'wp-seller-plugin' ) );
	}

	/**
	 * 获取节点配置（编辑回显，解密密钥）。
	 */
	public function ajax_get_provider() {
		check_ajax_referer( 'mnbtwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '权限不足', 'wp-seller-plugin' ) );
		}
		global $wpdb;
		$id  = (int) ( $_POST['id'] ?? 0 );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mnbt_providers WHERE id=%d", $id ), ARRAY_A );
		if ( ! $row ) {
			wp_send_json_error( __( '节点不存在', 'wp-seller-plugin' ) );
		}
		wp_send_json_success(
			array(
				'id'      => (int) $row['id'],
				'name'    => $row['name'],
				'api_url' => $row['api_url'],
				'btdh'    => $row['btdh'],
				'mn_key'  => Crypto::decrypt( $row['mn_key_enc'] ),
				'mn_keye' => Crypto::decrypt( $row['mn_keye_enc'] ),
				'mn_vs'   => (int) $row['mn_vs'],
				'qk'      => $row['qk'],
			)
		);
	}

	/**
	 * 删除节点配置。
	 */
	public function ajax_delete_provider() {
		check_ajax_referer( 'mnbtwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '权限不足', 'wp-seller-plugin' ) );
		}
		global $wpdb;
		$id = (int) ( $_POST['id'] ?? 0 );
		$wpdb->delete( $wpdb->prefix . 'mnbt_providers', array( 'id' => $id ), array( '%d' ) );
		if ( $wpdb->last_error ) {
			wp_send_json_error( __( '删除失败', 'wp-seller-plugin' ) . '：' . $wpdb->last_error );
		}
		wp_send_json_success( __( '删除成功', 'wp-seller-plugin' ) );
	}

	/**
	 * 连接测试（cfif）。
	 */
	public function ajax_test_provider() {
		check_ajax_referer( 'mnbtwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '权限不足', 'wp-seller-plugin' ) );
		}
		global $wpdb;
		$id  = (int) ( $_POST['id'] ?? 0 );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mnbt_providers WHERE id=%d", $id ), ARRAY_A );
		if ( ! $row ) {
			wp_send_json_error( __( '节点不存在', 'wp-seller-plugin' ) );
		}
		try {
			$client = new Client(
				array(
					'api_url'     => $row['api_url'],
					'btdh'        => $row['btdh'],
					'mn_key'      => Crypto::decrypt( $row['mn_key_enc'] ),
					'mn_keye'     => Crypto::decrypt( $row['mn_keye_enc'] ),
					'mn_vs'       => (int) $row['mn_vs'],
					'provider_id' => (int) $row['id'],
				)
			);
			$result = $client->testConnection();
			/* translators: %s: MNBT 返回消息 */
			wp_send_json_success( sprintf( __( '连接成功：%s', 'wp-seller-plugin' ), $result['msg'] ) );
		} catch ( MnbtException $e ) {
			wp_send_json_error( __( '连接失败', 'wp-seller-plugin' ) . '：' . $e->getMessage() );
		}
	}

	/**
	 * 管理端主机操作：start/stop/suspend/resume/delete/change_pass/renew/change_package。
	 */
	public function ajax_admin_host_action() {
		check_ajax_referer( 'mnbtwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '权限不足', 'wp-seller-plugin' ) );
		}
		$action = sanitize_key( $_POST['host_action'] ?? '' );
		$host_id = (int) ( $_POST['host_id'] ?? 0 );
		$host    = Helpers::get_host( $host_id );
		if ( ! $host ) {
			wp_send_json_error( __( '主机不存在', 'wp-seller-plugin' ) );
		}
		$provider = Helpers::get_provider( $host['provider_id'] );
		if ( ! $provider ) {
			wp_send_json_error( __( '节点不可用', 'wp-seller-plugin' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'mnbt_hosts';

		try {
			$client = Helpers::client_for_provider( $provider );
			switch ( $action ) {
				case 'start':
					$client->startSite( $host['username'] );
					break;
				case 'stop':
					$client->stopSite( $host['username'] );
					break;
				case 'suspend':
					$client->suspendHost( $host['username'] );
					$wpdb->update( $table, array( 'status' => 'suspended', 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $host_id ) );
					break;
				case 'resume':
					$client->resumeHost( $host['username'] );
					$wpdb->update( $table, array( 'status' => 'active', 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $host_id ) );
					break;
				case 'delete':
					$client->deleteHost( $host['username'] );
					$wpdb->delete( $table, array( 'id' => $host_id ), array( '%d' ) );
					break;
				case 'change_pass':
					$password = sanitize_text_field( $_POST['password'] ?? '' );
					if ( mb_strlen( $password ) < 6 ) {
						wp_send_json_error( __( '密码不能少于 6 位', 'wp-seller-plugin' ) );
					}
					$client->changePassword( $host['username'], $password );
					$wpdb->update( $table, array( 'password_enc' => Crypto::encrypt( $password ), 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $host_id ) );
					break;
				case 'renew':
					$setdate = sanitize_text_field( $_POST['setdate'] ?? '' );
					if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $setdate ) ) {
						wp_send_json_error( __( '到期日期格式错误', 'wp-seller-plugin' ) );
					}
					$client->renewHost( $host['username'], $setdate );
					$wpdb->update( $table, array( 'expire_date' => $setdate, 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $host_id ) );
					break;
				case 'change_package':
					$web  = max( 0, (int) ( $_POST['websize'] ?? 0 ) );
					$sql  = max( 0, (int) ( $_POST['sqlsize'] ?? 0 ) );
					$flow = max( 0, (int) ( $_POST['ll'] ?? 0 ) );
					$client->changePackage( $host['username'], array( 'websize' => $web, 'sqlsize' => $sql, 'll' => $flow ) );
					$quota = Helpers::parse_quota( $host['quota_json'] );
					$quota['web']['max']  = $web;
					$quota['sql']['max']  = $sql;
					$quota['flow']['max'] = $flow;
					$wpdb->update( $table, array( 'quota_json' => wp_json_encode( $quota ), 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $host_id ) );
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
	 * 重试开通失败的订单。
	 */
	public function ajax_admin_retry_provision() {
		check_ajax_referer( 'mnbtwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( '权限不足', 'wp-seller-plugin' ) );
		}
		$order_id = (int) ( $_POST['order_id'] ?? 0 );
		if ( ! $order_id ) {
			wp_send_json_error( __( '参数错误', 'wp-seller-plugin' ) );
		}
		try {
			$result = Provision::provision_order( $order_id );
			wp_send_json_success( $result['msg'] );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
