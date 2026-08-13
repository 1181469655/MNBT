<?php
/**
 * 开通编排：订单支付成功后创建主机（幂等 + 失败记录可重试）。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Services;

use MnbtWp\Crypto;
use MnbtWp\Helpers;
use MnbtWp\Mnbt\Exception as MnbtException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 开通服务。
 */
class Provision {

	/**
	 * 根据订单开通主机。
	 *
	 * 幂等：订单已开通（_mnbtwp_provisioned）则直接返回。
	 * 失败：抛出异常并记录 _mnbtwp_provision_error，可后台重试。
	 *
	 * @param int $order_id 订单 ID。
	 * @return array ['ok'=>bool, 'msg'=>string, 'host_id'=>int]
	 *
	 * @throws \Exception 开通失败。
	 */
	public static function provision_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			throw new \Exception( __( '订单不存在', 'wp-seller-plugin' ) );
		}

		if ( get_post_meta( $order_id, '_mnbtwp_provisioned', true ) ) {
			return array(
				'ok'      => true,
				'msg'     => __( '该订单已开通', 'wp-seller-plugin' ),
				'host_id' => (int) get_post_meta( $order_id, '_mnbtwp_host_id', true ),
			);
		}

		// 定位主机商品。
		$product_id = 0;
		foreach ( $order->get_items() as $item ) {
			$pid = (int) $item->get_product_id();
			if ( 'yes' === get_post_meta( $pid, '_mnbtwp_enabled', true ) ) {
				$product_id = $pid;
				break;
			}
		}
		if ( ! $product_id ) {
			throw new \Exception( __( '订单中无主机商品', 'wp-seller-plugin' ) );
		}

		$provider_id = (int) get_post_meta( $product_id, '_mnbtwp_provider_id', true );
		$provider    = Helpers::get_provider( $provider_id );
		if ( ! $provider || 'true' !== $provider['qk'] ) {
			throw new \Exception( __( '节点配置不可用', 'wp-seller-plugin' ) );
		}

		$user_id  = (int) $order->get_user_id();
		$username = Helpers::generate_username( $user_id );
		$password = wp_generate_password( 12, true );

		$months  = max( 1, (int) get_post_meta( $product_id, '_mnbtwp_period_months', true ) ?: 12 );
		$expire  = date( 'Y-m-d', strtotime( "+{$months} months" ) );
		$webdx   = (int) get_post_meta( $product_id, '_mnbtwp_webdx', true );
		$sqldx   = (int) get_post_meta( $product_id, '_mnbtwp_sqldx', true );
		$flow    = (int) get_post_meta( $product_id, '_mnbtwp_ll', true );
		$ymbds   = (int) get_post_meta( $product_id, '_mnbtwp_ymbds', true );

		$client = Helpers::client_for_provider( $provider );
		try {
			$client->createHost(
				array(
					'username' => $username,
					'password' => $password,
					'webdx'    => $webdx,
					'sqldx'    => $sqldx,
					'll'       => $flow,
					'ymbds'    => $ymbds,
					'dqtime'   => $expire,
				)
			);
		} catch ( MnbtException $e ) {
			update_post_meta( $order_id, '_mnbtwp_provision_error', $e->getMessage() );
			throw $e;
		}

		global $wpdb;
		$quota = wp_json_encode(
			array(
				'web'  => array( 'max' => $webdx, 'used' => 0 ),
				'sql'  => array( 'max' => $sqldx, 'used' => 0 ),
				'flow' => array( 'max' => $flow, 'used' => 0 ),
			)
		);

		$wpdb->insert(
			$wpdb->prefix . 'mnbt_hosts',
			array(
				'provider_id'  => $provider_id,
				'order_id'     => (int) $order_id,
				'user_id'      => $user_id,
				'username'     => $username,
				'password_enc' => Crypto::encrypt( $password ),
				'site_domain'  => '',
				'status'       => 'active',
				'expire_date'  => $expire,
				'quota_json'   => $quota,
				'created_at'   => current_time( 'mysql' ),
				'updated_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		$host_id = (int) $wpdb->insert_id;

		update_post_meta( $order_id, '_mnbtwp_provisioned', '1' );
		update_post_meta( $order_id, '_mnbtwp_host_id', $host_id );
		delete_post_meta( $order_id, '_mnbtwp_provision_error' );

		self::notify( $user_id, $username, $password, $provider, $expire );

		return array(
			'ok'      => true,
			'msg'     => __( '主机开通成功', 'wp-seller-plugin' ),
			'host_id' => $host_id,
		);
	}

	/**
	 * 发送开通通知邮件（含账号/密码/控制台地址）。
	 *
	 * @param int    $user_id  WP 用户 ID。
	 * @param string $username 主机用户名。
	 * @param string $password 明文密码。
	 * @param array  $provider 节点行。
	 * @param string $expire   到期日期。
	 */
	private static function notify( $user_id, $username, $password, array $provider, $expire ) {
		$user = get_userdata( $user_id );
		if ( ! $user || empty( $user->user_email ) ) {
			return;
		}
		$subject = sprintf( __( '【%s】主机开通成功', 'wp-seller-plugin' ), get_bloginfo( 'name' ) );
		$body    = '<p>' . esc_html__( '您购买的虚拟主机已开通：', 'wp-seller-plugin' ) . '</p>'
			. '<p>主机账号：<code>' . esc_html( $username ) . '</code></p>'
			. '<p>主机密码：<code>' . esc_html( $password ) . '</code></p>'
			. '<p>' . esc_html__( '到期时间：', 'wp-seller-plugin' ) . esc_html( $expire ) . '</p>'
			. '<p>' . esc_html__( '控制台：', 'wp-seller-plugin' ) . '<a href="' . esc_url( Helpers::control_url( $provider ) ) . '">' . esc_html( Helpers::control_url( $provider ) ) . '</a></p>'
			. '<p style="color:#888;">' . esc_html__( '请妥善保管账号密码，可在「我的主机」中随时重置密码。', 'wp-seller-plugin' ) . '</p>';
		Helpers::send_mail( $user->user_email, $subject, $body );
	}
}
