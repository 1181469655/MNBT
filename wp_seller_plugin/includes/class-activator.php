<?php
/**
 * 激活/停用逻辑：建表、默认配置、定时任务注册。
 *
 * @package MnbtWp
 */

namespace MnbtWp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 激活器。
 */
class Activator {

	/**
	 * 激活：建表 + 默认设置 + 注册定时任务。
	 */
	public static function activate() {
		self::create_tables();
		self::defaults();
		self::schedule_cron();
	}

	/**
	 * 停用：清理定时任务（数据保留）。
	 */
	public static function deactivate() {
		self::clear_cron();
	}

	/**
	 * 创建插件数据表。
	 */
	private static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix;

		$tables = array();

		// MNBT 节点连接配置（可多节点）。
		$tables[] = "CREATE TABLE {$prefix}mnbt_providers (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL DEFAULT '',
			api_url varchar(255) NOT NULL DEFAULT '',
			btdh varchar(50) NOT NULL DEFAULT '',
			mn_key_enc varchar(255) NOT NULL DEFAULT '',
			mn_keye_enc varchar(255) NOT NULL DEFAULT '',
			mn_vs int(11) NOT NULL DEFAULT 15,
			qk varchar(20) NOT NULL DEFAULT 'true',
			created_at datetime DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY qk (qk)
		) $charset_collate;";

		// 主机记录（本地状态缓存，购买开通后写入）。
		$tables[] = "CREATE TABLE {$prefix}mnbt_hosts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			provider_id bigint(20) unsigned NOT NULL DEFAULT 0,
			order_id bigint(20) unsigned NOT NULL DEFAULT 0,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			username varchar(64) NOT NULL DEFAULT '',
			password_enc varchar(255) NOT NULL DEFAULT '',
			site_domain varchar(128) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'pending',
			expire_date varchar(20) NOT NULL DEFAULT '',
			quota_json text NOT NULL,
			last_remind datetime DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY username (username),
			KEY status (status)
		) $charset_collate;";

		// API 调用日志（脱敏）。
		$tables[] = "CREATE TABLE {$prefix}mnbt_api_log (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			provider_id bigint(20) unsigned NOT NULL DEFAULT 0,
			action varchar(50) NOT NULL DEFAULT '',
			params_json text NOT NULL,
			code int(11) NOT NULL DEFAULT 0,
			msg text NOT NULL,
			duration float NOT NULL DEFAULT 0,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY action (action),
			KEY created_at (created_at)
		) $charset_collate;";

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}

	/**
	 * 默认设置。
	 */
	private static function defaults() {
		if ( false === get_option( 'mnbtwp_settings' ) ) {
			add_option(
				'mnbtwp_settings',
				array(
					'expire_days'      => 3, // 到期前提醒天数。
					'grace_days'       => 0, // 宽限期（天）。
					'keep_data_on_del' => 1, // 卸载时保留数据。
				)
			);
		}
	}

	/**
	 * 注册定时任务：每日到期维护 + 每 10 分钟状态同步。
	 */
	public static function schedule_cron() {
		if ( ! wp_next_scheduled( 'mnbtwp_daily_maintenance' ) ) {
			wp_schedule_event( time() + 60, 'daily', 'mnbtwp_daily_maintenance' );
		}
		if ( ! wp_next_scheduled( 'mnbtwp_sync_hosts' ) ) {
			wp_schedule_event( time() + 120, 'twicedaily', 'mnbtwp_sync_hosts' );
		}
	}

	/**
	 * 清理定时任务。
	 */
	private static function clear_cron() {
		foreach ( array( 'mnbtwp_daily_maintenance', 'mnbtwp_sync_hosts' ) as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
		}
	}
}
