<?php
/**
 * 通用工具：节点/主机查询、Client 构造、用户名生成、状态文案。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

use MnbtWp\Mnbt\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 工具类。
 */
class Helpers {

	/**
	 * 按 ID 取节点配置行。
	 *
	 * @param int $id 节点 ID。
	 * @return array|null
	 */
	public static function get_provider( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mnbt_providers WHERE id=%d", (int) $id ), ARRAY_A );
		return $row ? $row : null;
	}

	/**
	 * 根据节点行构造 Client（解密密钥）。
	 *
	 * @param array $provider 节点行。
	 * @return Client
	 */
	public static function client_for_provider( array $provider ) {
		return new Client(
			array(
				'api_url'     => $provider['api_url'],
				'btdh'        => $provider['btdh'],
				'mn_key'      => Crypto::decrypt( $provider['mn_key_enc'] ),
				'mn_keye'     => Crypto::decrypt( $provider['mn_keye_enc'] ),
				'mn_vs'       => (int) $provider['mn_vs'],
				'provider_id' => (int) $provider['id'],
			)
		);
	}

	/**
	 * 按 ID 取主机行。
	 *
	 * @param int $id 主机 ID。
	 * @return array|null
	 */
	public static function get_host( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mnbt_hosts WHERE id=%d", (int) $id ), ARRAY_A );
		return $row ? $row : null;
	}

	/**
	 * 主机状态文案。
	 *
	 * @param string $status 状态码。
	 * @return string
	 */
	public static function host_status_label( $status ) {
		$labels = array(
			'pending'     => __( '待开通', 'wp-seller-plugin' ),
			'provisioning' => __( '开通中', 'wp-seller-plugin' ),
			'active'      => __( '运行中', 'wp-seller-plugin' ),
			'suspended'   => __( '已暂停', 'wp-seller-plugin' ),
			'expired'     => __( '已到期', 'wp-seller-plugin' ),
			'failed'      => __( '开通失败', 'wp-seller-plugin' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * 生成主机用户名（≥6 位，避免与 MNBT 现有用户冲突）。
	 *
	 * @param int $user_id WP 用户 ID。
	 * @return string
	 */
	public static function generate_username( $user_id ) {
		$suffix = substr( str_replace( array( '0', '.' ), '', uniqid( '', true ) ), 0, 4 );
		return 'wp' . (int) $user_id . $suffix;
	}

	/**
	 * 主机控制台地址（MNBT 用户登录页）。
	 *
	 * @param array $provider 节点行。
	 * @return string
	 */
	public static function control_url( array $provider ) {
		return untrailingslashit( $provider['api_url'] ) . '/user/login.php';
	}

	/**
	 * 从 quota_json 读取配额（含用量）。
	 *
	 * @param string $quota_json 主机 quota_json。
	 * @return array ['web'=>['max','used'],'sql'=>...,'flow'=>...]
	 */
	public static function parse_quota( $quota_json ) {
		$data = json_decode( (string) $quota_json, true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return array(
			'web'  => isset( $data['web'] ) && is_array( $data['web'] ) ? $data['web'] : array( 'max' => 0, 'used' => 0 ),
			'sql'  => isset( $data['sql'] ) && is_array( $data['sql'] ) ? $data['sql'] : array( 'max' => 0, 'used' => 0 ),
			'flow' => isset( $data['flow'] ) && is_array( $data['flow'] ) ? $data['flow'] : array( 'max' => 0, 'used' => 0 ),
		);
	}

	/**
	 * 发送邮件（包装 wp_mail，统一发件人）。
	 *
	 * @param string $to      收件人。
	 * @param string $subject 主题。
	 * @param string $body    HTML 正文。
	 * @return bool
	 */
	public static function send_mail( $to, $subject, $body ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		return wp_mail( $to, $subject, $body, $headers );
	}
}
