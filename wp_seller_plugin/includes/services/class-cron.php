<?php
/**
 * 定时任务：每日到期维护 + 状态/用量同步。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Services;

use MnbtWp\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 定时任务调度。
 */
class Cron {

	/**
	 * 注册定时任务回调。
	 */
	public static function init() {
		add_action( 'mnbtwp_daily_maintenance', array( __CLASS__, 'daily' ) );
		add_action( 'mnbtwp_sync_hosts', array( __CLASS__, 'sync' ) );
	}

	/**
	 * 每日到期维护。
	 */
	public static function daily() {
		Billing::expire_maintenance();
	}

	/**
	 * 同步主机状态与用量（ztcx），带锁防并发。
	 */
	public static function sync() {
		if ( get_transient( 'mnbtwp_sync_lock' ) ) {
			return;
		}
		set_transient( 'mnbtwp_sync_lock', 1, 10 * MINUTE_IN_SECONDS );

		global $wpdb;
		$hosts = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}mnbt_hosts WHERE status IN ('active','suspended','expired')",
			ARRAY_A
		);

		foreach ( $hosts as $host ) {
			$provider = Helpers::get_provider( $host['provider_id'] );
			if ( ! $provider ) {
				continue;
			}
			try {
				$result = Helpers::client_for_provider( $provider )->getHostStatus( $host['username'] );
				$data   = isset( $result['data'] ) && is_array( $result['data'] ) ? $result['data'] : array();

				$quota = Helpers::parse_quota( $host['quota_json'] );
				if ( isset( $data['quota'] ) && is_array( $data['quota'] ) ) {
					$q = $data['quota'];
					$quota['web']['max']  = (int) ( $q['web_size_max'] ?? $quota['web']['max'] );
					$quota['web']['used'] = (int) ( $q['web_size_used'] ?? $quota['web']['used'] );
					$quota['sql']['max']  = (int) ( $q['sql_size_max'] ?? $quota['sql']['max'] );
					$quota['sql']['used'] = (int) ( $q['sql_size_used'] ?? $quota['sql']['used'] );
					$quota['flow']['max'] = (int) ( $q['flow_max'] ?? $quota['flow']['max'] );
					$quota['flow']['used'] = (int) ( $q['flow_used'] ?? $quota['flow']['used'] );
				}

				$site_domain = isset( $data['user']['domain'] ) ? (string) $data['user']['domain'] : '';

				$wpdb->update(
					$wpdb->prefix . 'mnbt_hosts',
					array(
						'site_domain' => $site_domain ? $site_domain : $host['site_domain'],
						'quota_json'  => wp_json_encode( $quota ),
						'updated_at'  => current_time( 'mysql' ),
					),
					array( 'id' => (int) $host['id'] )
				);
			} catch ( \Exception $e ) {
				// 单台同步失败不影响其他主机。
				continue;
			}
		}

		delete_transient( 'mnbtwp_sync_lock' );
	}
}
