<?php
/**
 * 卸载脚本：默认保留数据（避免误删客户主机记录）。
 * 如需彻底删除数据，取消下方注释。
 *
 * @package MnbtWp
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$mnbtwp_keep = get_option( 'mnbtwp_settings', array() );
$mnbtwp_keep = is_array( $mnbtwp_keep ) ? $mnbtwp_keep : array();

if ( empty( $mnbtwp_keep['keep_data_on_del'] ) ) {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mnbt_providers" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mnbt_hosts" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mnbt_api_log" );
	delete_option( 'mnbtwp_settings' );
}
