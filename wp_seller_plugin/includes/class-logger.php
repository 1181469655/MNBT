<?php
/**
 * 日志：API 调用日志（脱敏写入 mnbt_api_log 表）。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 日志器。
 */
class Logger {

	/**
	 * 记录一次 API 调用。
	 *
	 * @param int    $provider_id 节点配置 ID。
	 * @param string $action      MNBT gn（cfif/kt/...）。
	 * @param array  $params      请求参数（写入前脱敏）。
	 * @param int    $code        返回 code（200 成功 / 100 业务失败 / 0 网络错误）。
	 * @param string $msg         返回消息或错误信息。
	 * @param float  $duration    耗时（秒）。
	 */
	public static function api( $provider_id, $action, array $params, $code, $msg, $duration ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'mnbt_api_log',
			array(
				'provider_id' => (int) $provider_id,
				'action'      => sanitize_key( (string) $action ),
				'params_json' => wp_json_encode( self::mask( $params ) ),
				'code'        => (int) $code,
				'msg'         => mb_substr( (string) $msg, 0, 500 ),
				'duration'    => round( (float) $duration, 3 ),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%f', '%s' )
		);
	}

	/**
	 * 对敏感参数脱敏。
	 *
	 * @param array $params 原始参数。
	 * @return array
	 */
	private static function mask( array $params ) {
		$masked = $params;
		foreach ( array( 'mn_key', 'mn_keye', 'password' ) as $k ) {
			if ( array_key_exists( $k, $masked ) && '' !== $masked[ $k ] ) {
				$masked[ $k ] = '***';
			}
		}
		return $masked;
	}
}
