<?php
/**
 * 计费：续费处理与到期维护（暂停/提醒）。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Services;

use MnbtWp\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 计费服务。
 */
class Billing {

	/**
	 * 续费订单支付完成后处理：xf 更新到期时间，必要时恢复主机。
	 *
	 * @param int $order_id 订单 ID（含 _mnbtwp_renew_host_id / _mnbtwp_renew_months meta）。
	 * @return array
	 *
	 * @throws \Exception 续费失败。
	 */
	public static function renew_order( $order_id ) {
		$host_id = (int) get_post_meta( $order_id, '_mnbtwp_renew_host_id', true );
		if ( ! $host_id ) {
			throw new \Exception( __( '续费订单缺少主机信息', 'wp-seller-plugin' ) );
		}
		if ( get_post_meta( $order_id, '_mnbtwp_renewed', true ) ) {
			return array( 'ok' => true, 'msg' => __( '该订单已续费', 'wp-seller-plugin' ) );
		}

		$host = Helpers::get_host( $host_id );
		if ( ! $host ) {
			throw new \Exception( __( '主机不存在', 'wp-seller-plugin' ) );
		}

		$months  = max( 1, (int) get_post_meta( $order_id, '_mnbtwp_renew_months', true ) ?: 1 );
		$base    = ( $host['expire_date'] && '0000-00-00' !== $host['expire_date'] && strtotime( $host['expire_date'] ) > time() )
			? $host['expire_date']
			: date( 'Y-m-d' );
		$new_date = date( 'Y-m-d', strtotime( "+{$months} months", strtotime( $base ) ) );

		$provider = Helpers::get_provider( $host['provider_id'] );
		if ( ! $provider ) {
			throw new \Exception( __( '节点配置不存在', 'wp-seller-plugin' ) );
		}

		$client = Helpers::client_for_provider( $provider );
		$client->renewHost( $host['username'], $new_date );

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'mnbt_hosts',
			array(
				'expire_date' => $new_date,
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'id' => (int) $host['id'] )
		);

		// 已到期/暂停的主机续费后恢复。
		if ( in_array( $host['status'], array( 'expired', 'suspended' ), true ) ) {
			try {
				$client->resumeHost( $host['username'] );
				$wpdb->update(
					$wpdb->prefix . 'mnbt_hosts',
					array( 'status' => 'active', 'updated_at' => current_time( 'mysql' ) ),
					array( 'id' => (int) $host['id'] )
				);
			} catch ( \Exception $e ) { // 恢复失败不影响续费结果。
			}
		}

		update_post_meta( $order_id, '_mnbtwp_renewed', '1' );
		self::notify_renew( $host, $new_date );

		return array( 'ok' => true, 'msg' => __( '续费成功', 'wp-seller-plugin' ), 'expire_date' => $new_date );
	}

	/**
	 * 每日到期维护：过期自动暂停 + 到期提醒。
	 */
	public static function expire_maintenance() {
		global $wpdb;

		$settings   = get_option( 'mnbtwp_settings', array() );
		$grace_days = max( 0, (int) ( $settings['grace_days'] ?? 0 ) );
		$remind_days = max( 0, (int) ( $settings['expire_days'] ?? 3 ) );

		$hosts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mnbt_hosts WHERE status='active'", ARRAY_A );
		$now   = time();

		foreach ( $hosts as $host ) {
			if ( empty( $host['expire_date'] ) || '0000-00-00' === $host['expire_date'] ) {
				continue;
			}
			$expire_ts = strtotime( $host['expire_date'] . ' 23:59:59' );

			// 已过期且超宽限期 → 暂停。
			if ( $expire_ts + $grace_days * DAY_IN_SECONDS < $now ) {
				$provider = Helpers::get_provider( $host['provider_id'] );
				if ( $provider ) {
					try {
						Helpers::client_for_provider( $provider )->suspendHost( $host['username'] );
						$wpdb->update(
							$wpdb->prefix . 'mnbt_hosts',
							array( 'status' => 'expired', 'updated_at' => current_time( 'mysql' ) ),
							array( 'id' => (int) $host['id'] )
						);
					} catch ( \Exception $e ) {
						// 暂停失败留待下次维护重试。
					}
				}
				continue;
			}

			// 到期前提醒（仅一次/每周期一次）。
			if ( $remind_days > 0 ) {
				$remind_ts = $expire_ts - $remind_days * DAY_IN_SECONDS;
				if ( $now >= $remind_ts && $now < $expire_ts ) {
					$last = $host['last_remind'] ? strtotime( $host['last_remind'] ) : 0;
					if ( $last < $remind_ts ) {
						self::send_expire_remind( $host );
						$wpdb->update(
							$wpdb->prefix . 'mnbt_hosts',
							array( 'last_remind' => current_time( 'mysql' ) ),
							array( 'id' => (int) $host['id'] )
						);
					}
				}
			}
		}
	}

	/**
	 * 发送到期提醒邮件。
	 *
	 * @param array $host 主机行。
	 */
	private static function send_expire_remind( array $host ) {
		$user = get_userdata( (int) $host['user_id'] );
		if ( ! $user || empty( $user->user_email ) ) {
			return;
		}
		$subject = sprintf( __( '【%s】主机即将到期', 'wp-seller-plugin' ), get_bloginfo( 'name' ) );
		$body    = '<p>' . esc_html__( '您的主机即将到期，请及时续费以免暂停服务：', 'wp-seller-plugin' ) . '</p>'
			. '<p>主机账号：<code>' . esc_html( $host['username'] ) . '</code></p>'
			. '<p>' . esc_html__( '到期时间：', 'wp-seller-plugin' ) . esc_html( $host['expire_date'] ) . '</p>'
			. '<p>' . esc_html__( '请在「我的主机」中点击续费完成支付。', 'wp-seller-plugin' ) . '</p>';
		Helpers::send_mail( $user->user_email, $subject, $body );
	}

	/**
	 * 发送续费成功邮件。
	 *
	 * @param array  $host     主机行。
	 * @param string $new_date 新到期日期。
	 */
	private static function notify_renew( array $host, $new_date ) {
		$user = get_userdata( (int) $host['user_id'] );
		if ( ! $user || empty( $user->user_email ) ) {
			return;
		}
		$subject = sprintf( __( '【%s】主机续费成功', 'wp-seller-plugin' ), get_bloginfo( 'name' ) );
		$body    = '<p>' . esc_html__( '您的主机已成功续费：', 'wp-seller-plugin' ) . '</p>'
			. '<p>主机账号：<code>' . esc_html( $host['username'] ) . '</code></p>'
			. '<p>' . esc_html__( '新到期时间：', 'wp-seller-plugin' ) . esc_html( $new_date ) . '</p>';
		Helpers::send_mail( $user->user_email, $subject, $body );
	}
}
