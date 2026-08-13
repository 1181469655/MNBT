<?php
/**
 * WooCommerce 集成：商品扩展、支付回调开通/续费、续费下单。
 *
 * @package MnbtWp
 */

namespace MnbtWp\Woo;

use MnbtWp\Helpers;
use MnbtWp\Services\Billing;
use MnbtWp\Services\Provision;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce 集成。
 */
class Woo {

	/**
	 * 构造：挂载钩子。
	 */
	public function __construct() {
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_meta_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'on_order_paid' ), 20, 1 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'on_order_paid' ), 20, 1 );
	}

	/**
	 * 商品常规设置面板输出 MNBT 主机套餐字段。
	 */
	public function product_meta_fields() {
		global $post;
		$pid = (int) $post->ID;

		$enabled   = 'yes' === get_post_meta( $pid, '_mnbtwp_enabled', true );
		$provider  = (int) get_post_meta( $pid, '_mnbtwp_provider_id', true );
		$webdx     = (int) get_post_meta( $pid, '_mnbtwp_webdx', true );
		$sqldx     = (int) get_post_meta( $pid, '_mnbtwp_sqldx', true );
		$flow      = (int) get_post_meta( $pid, '_mnbtwp_ll', true );
		$ymbds     = (int) get_post_meta( $pid, '_mnbtwp_ymbds', true );
		$months    = (int) get_post_meta( $pid, '_mnbtwp_period_months', true ) ?: 12;

		global $wpdb;
		$providers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mnbt_providers WHERE qk='true' ORDER BY id ASC", ARRAY_A );

		echo '<div class="options_group show_if_simple">';
		echo '<p class="form-field"><label style="font-weight:600;">' . esc_html__( 'MNBT 主机套餐', 'wp-seller-plugin' ) . '</label></p>';

		woocommerce_wp_checkbox(
			array(
				'id'      => '_mnbtwp_enabled',
				'label'   => __( '作为 MNBT 主机套餐', 'wp-seller-plugin' ),
				'cbvalue' => 'yes',
			)
		);

		echo '<p class="form-field _mnbtwp_fields">';
		echo '<label for="_mnbtwp_provider_id">' . esc_html__( '节点', 'wp-seller-plugin' ) . '</label>';
		echo '<select id="_mnbtwp_provider_id" name="_mnbtwp_provider_id" class="select short">';
		echo '<option value="0">' . esc_html__( '选择节点', 'wp-seller-plugin' ) . '</option>';
		foreach ( $providers as $p ) {
			$sel = ( $provider === (int) $p['id'] ) ? ' selected' : '';
			echo '<option value="' . (int) $p['id'] . '"' . $sel . '>' . esc_html( $p['name'] ) . '</option>';
		}
		echo '</select></p>';

		woocommerce_wp_text_input( array( 'id' => '_mnbtwp_webdx', 'label' => __( '网站空间 (MB)', 'wp-seller-plugin' ), 'type' => 'number', 'value' => $webdx ) );
		woocommerce_wp_text_input( array( 'id' => '_mnbtwp_sqldx', 'label' => __( '数据库空间 (MB)', 'wp-seller-plugin' ), 'type' => 'number', 'value' => $sqldx ) );
		woocommerce_wp_text_input( array( 'id' => '_mnbtwp_ll', 'label' => __( '流量 (MB，0=不限)', 'wp-seller-plugin' ), 'type' => 'number', 'value' => $flow ) );
		woocommerce_wp_text_input( array( 'id' => '_mnbtwp_ymbds', 'label' => __( '域名绑定数', 'wp-seller-plugin' ), 'type' => 'number', 'value' => $ymbds ) );
		woocommerce_wp_text_input( array( 'id' => '_mnbtwp_period_months', 'label' => __( '周期 (月)', 'wp-seller-plugin' ), 'type' => 'number', 'value' => $months ) );
		echo '</div>';

		// 当启用开关变化时显隐字段。
		?>
		<script type="text/javascript">
		jQuery(function ($) {
			function mnbtwpToggle() {
				if ($('#_mnbtwp_enabled').prop('checked')) {
					$('._mnbtwp_fields').show();
				} else {
					$('._mnbtwp_fields').hide();
				}
			}
			$('#_mnbtwp_enabled').on('change', mnbtwpToggle);
			mnbtwpToggle();
		});
		</script>
		<?php
	}

	/**
	 * 保存商品 meta。
	 *
	 * @param int $post_id 商品 ID。
	 */
	public function save_product_meta( $post_id ) {
		if ( ! empty( $_POST['_mnbtwp_enabled'] ) && 'yes' === $_POST['_mnbtwp_enabled'] ) {
			update_post_meta( $post_id, '_mnbtwp_enabled', 'yes' );
		} else {
			delete_post_meta( $post_id, '_mnbtwp_enabled' );
		}
		update_post_meta( $post_id, '_mnbtwp_provider_id', max( 0, (int) ( $_POST['_mnbtwp_provider_id'] ?? 0 ) ) );
		update_post_meta( $post_id, '_mnbtwp_webdx', max( 0, (int) ( $_POST['_mnbtwp_webdx'] ?? 0 ) ) );
		update_post_meta( $post_id, '_mnbtwp_sqldx', max( 0, (int) ( $_POST['_mnbtwp_sqldx'] ?? 0 ) ) );
		update_post_meta( $post_id, '_mnbtwp_ll', max( 0, (int) ( $_POST['_mnbtwp_ll'] ?? 0 ) ) );
		update_post_meta( $post_id, '_mnbtwp_ymbds', max( 0, (int) ( $_POST['_mnbtwp_ymbds'] ?? 0 ) ) );
		update_post_meta( $post_id, '_mnbtwp_period_months', max( 1, (int) ( $_POST['_mnbtwp_period_months'] ?? 12 ) ) );
	}

	/**
	 * 订单支付完成：续费订单走续费，否则走开通。
	 *
	 * @param int $order_id 订单 ID。
	 */
	public function on_order_paid( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		try {
			if ( get_post_meta( $order_id, '_mnbtwp_renew_host_id', true ) ) {
				Billing::renew_order( $order_id );
			} else {
				Provision::provision_order( $order_id );
			}
		} catch ( \Exception $e ) {
			// 记录失败（开通失败可在后台重试；续费失败待支付单手动处理）。
			$order->add_order_note( 'MNBT 处理失败：' . $e->getMessage() );
		}
	}

	/**
	 * 创建续费订单（含原商品 + 续费 meta），返回订单对象。
	 *
	 * @param int $host_id 主机 ID。
	 * @param int $months  续费月数。
	 * @return \WC_Order
	 *
	 * @throws \Exception 原商品/订单缺失时抛出。
	 */
	public static function create_renew_order( $host_id, $months ) {
		$host = Helpers::get_host( $host_id );
		if ( ! $host ) {
			throw new \Exception( __( '主机不存在', 'wp-seller-plugin' ) );
		}
		$months = max( 1, (int) $months );

		$order = $host['order_id'] ? wc_get_order( (int) $host['order_id'] ) : null;
		$product_id = 0;
		if ( $order ) {
			foreach ( $order->get_items() as $item ) {
				$pid = (int) $item->get_product_id();
				if ( 'yes' === get_post_meta( $pid, '_mnbtwp_enabled', true ) ) {
					$product_id = $pid;
					break;
				}
			}
		}
		if ( ! $product_id ) {
			throw new \Exception( __( '找不到原主机商品', 'wp-seller-plugin' ) );
		}
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			throw new \Exception( __( '原商品不存在', 'wp-seller-plugin' ) );
		}

		$order = wc_create_order( array( 'customer_id' => (int) $host['user_id'] ) );
		$order->add_product( $product, $months );
		$order->add_meta_data( '_mnbtwp_renew_host_id', (int) $host_id );
		$order->add_meta_data( '_mnbtwp_renew_months', $months );
		$order->set_status( 'pending' );
		$order->calculate_totals();
		$order->save();

		return $order;
	}
}
