<?php
/**
 * 主机总览页视图。
 *
 * @package MnbtWp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mnbtwp_nonce = wp_create_nonce( 'mnbtwp_admin' );
?>
<div class="wrap">
	<h1><?php esc_html_e( '主机管理', 'wp-seller-plugin' ); ?></h1>
	<p class="description"><?php esc_html_e( '所有已开通主机（含购买自动开通与手动开通）。操作会直接调用 MNBT 生效。', 'wp-seller-plugin' ); ?></p>

	<?php if ( ! empty( $failed_orders ) ) : ?>
		<h2 style="margin-top:20px;"><?php esc_html_e( '开通失败订单', 'wp-seller-plugin' ); ?></h2>
		<p class="description"><?php esc_html_e( '以下订单支付成功但自动开通失败，可重试开通。', 'wp-seller-plugin' ); ?></p>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( '订单', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '客户', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '金额', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '错误信息', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '操作', 'wp-seller-plugin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $failed_orders as $mnbtwp_fo ) : ?>
					<?php $mnbtwp_err = get_post_meta( $mnbtwp_fo->get_id(), '_mnbtwp_provision_error', true ); ?>
					<tr>
						<td><a href="<?php echo esc_url( $mnbtwp_fo->get_edit_order_url() ); ?>">#<?php echo (int) $mnbtwp_fo->get_id(); ?></a></td>
						<td><?php echo esc_html( $mnbtwp_fo->get_billing_email() ? $mnbtwp_fo->get_billing_email() : '#' . (int) $mnbtwp_fo->get_user_id() ); ?></td>
						<td><?php echo esc_html( $mnbtwp_fo->get_total() . ' ' . $mnbtwp_fo->get_currency() ); ?></td>
						<td style="color:#c62828;"><?php echo esc_html( $mnbtwp_err ); ?></td>
						<td>
							<button type="button" class="button button-small button-primary" onclick="mnbtwpRetry(<?php echo (int) $mnbtwp_fo->get_id(); ?>)"><?php esc_html_e( '重试开通', 'wp-seller-plugin' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<h2 style="margin-top:20px;"><?php esc_html_e( '主机列表', 'wp-seller-plugin' ); ?></h2>
	<?php if ( empty( $hosts ) ) : ?>
		<p><?php esc_html_e( '暂无主机记录。', 'wp-seller-plugin' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '节点', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '用户名', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '站点', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '状态', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '到期', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '订单', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '操作', 'wp-seller-plugin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $hosts as $mnbtwp_h ) : ?>
					<?php
					$mnbtwp_p = isset( $provider_map[ $mnbtwp_h['provider_id'] ] ) ? $provider_map[ $mnbtwp_h['provider_id'] ] : null;
					$mnbtwp_label = Helpers::host_status_label( $mnbtwp_h['status'] );
					?>
					<tr>
						<td><?php echo (int) $mnbtwp_h['id']; ?></td>
						<td><?php echo $mnbtwp_p ? esc_html( $mnbtwp_p['name'] ) : esc_html( '#' . (int) $mnbtwp_h['provider_id'] ); ?></td>
						<td><code><?php echo esc_html( $mnbtwp_h['username'] ); ?></code></td>
						<td><?php echo esc_html( $mnbtwp_h['site_domain'] ? $mnbtwp_h['site_domain'] : '-' ); ?></td>
						<td>
							<?php if ( in_array( $mnbtwp_h['status'], array( 'active', 'provisioning' ), true ) ) : ?>
								<span style="color:#1a7f37;"><?php echo esc_html( $mnbtwp_label ); ?></span>
							<?php else : ?>
								<span style="color:#c62828;"><?php echo esc_html( $mnbtwp_label ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $mnbtwp_h['expire_date'] && '0000-00-00' !== $mnbtwp_h['expire_date'] ? $mnbtwp_h['expire_date'] : __( '长期', 'wp-seller-plugin' ) ); ?></td>
						<td><?php echo $mnbtwp_h['order_id'] ? '<a href="' . esc_url( get_edit_post_link( (int) $mnbtwp_h['order_id'] ) ) . '">#' . (int) $mnbtwp_h['order_id'] . '</a>' : '-'; ?></td>
						<td>
							<button type="button" class="button button-small" onclick="mnbtwpHostAction(<?php echo (int) $mnbtwp_h['id']; ?>,'start')"><?php esc_html_e( '启动', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small" onclick="mnbtwpHostAction(<?php echo (int) $mnbtwp_h['id']; ?>,'stop')"><?php esc_html_e( '停止', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small" onclick="mnbtwpHostAction(<?php echo (int) $mnbtwp_h['id']; ?>,'suspend')"><?php esc_html_e( '暂停', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small" onclick="mnbtwpHostAction(<?php echo (int) $mnbtwp_h['id']; ?>,'resume')"><?php esc_html_e( '恢复', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small" onclick="mnbtwpPass(<?php echo (int) $mnbtwp_h['id']; ?>)"><?php esc_html_e( '改密', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small" onclick="mnbtwpRenew(<?php echo (int) $mnbtwp_h['id']; ?>)"><?php esc_html_e( '续费', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small" onclick="mnbtwpPackage(<?php echo (int) $mnbtwp_h['id']; ?>)"><?php esc_html_e( '升降级', 'wp-seller-plugin' ); ?></button>
							<button type="button" class="button button-small button-link-delete" onclick="mnbtwpDelete(<?php echo (int) $mnbtwp_h['id']; ?>)"><?php esc_html_e( '删除', 'wp-seller-plugin' ); ?></button>
							<?php if ( 'failed' === $mnbtwp_h['status'] && $mnbtwp_h['order_id'] ) : ?>
								<button type="button" class="button button-small button-primary" onclick="mnbtwpRetry(<?php echo (int) $mnbtwp_h['order_id']; ?>)"><?php esc_html_e( '重试开通', 'wp-seller-plugin' ); ?></button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<script type="text/javascript">
	var mnbtwpAdminNonce = '<?php echo esc_js( $mnbtwp_nonce ); ?>';

	function mnbtwpAdminAjax(data, done) {
		data.nonce = mnbtwpAdminNonce;
		jQuery.post(ajaxurl, data, function (r) {
			if (r && r.success) {
				if (done) done(r); else location.reload();
			} else {
				alert((r && r.data) ? r.data : '<?php echo esc_js( __( '操作失败', 'wp-seller-plugin' ) ); ?>');
			}
		}).fail(function () {
			alert('<?php echo esc_js( __( '网络错误', 'wp-seller-plugin' ) ); ?>');
		});
	}

	function mnbtwpHostAction(id, action) {
		mnbtwpAdminAjax({ action: 'mnbtwp_admin_host_action', host_action: action, host_id: id });
	}

	function mnbtwpPass(id) {
		var pw = prompt('<?php echo esc_js( __( '请输入新密码（至少 6 位）', 'wp-seller-plugin' ) ); ?>');
		if (!pw || pw.length < 6) return;
		mnbtwpAdminAjax({ action: 'mnbtwp_admin_host_action', host_action: 'change_pass', host_id: id, password: pw });
	}

	function mnbtwpRenew(id) {
		var d = prompt('<?php echo esc_js( __( '请输入新到期日期（YYYY-MM-DD）', 'wp-seller-plugin' ) ); ?>');
		if (!d || !/^\d{4}-\d{2}-\d{2}$/.test(d)) return;
		mnbtwpAdminAjax({ action: 'mnbtwp_admin_host_action', host_action: 'renew', host_id: id, setdate: d });
	}

	function mnbtwpPackage(id) {
		var web = prompt('<?php echo esc_js( __( '网站空间 (MB)', 'wp-seller-plugin' ) ); ?>', '0');
		if (web === null) return;
		var sql = prompt('<?php echo esc_js( __( '数据库空间 (MB)', 'wp-seller-plugin' ) ); ?>', '0');
		if (sql === null) return;
		var ll = prompt('<?php echo esc_js( __( '流量 (MB，0=不限)', 'wp-seller-plugin' ) ); ?>', '0');
		if (ll === null) return;
		mnbtwpAdminAjax({
			action: 'mnbtwp_admin_host_action',
			host_action: 'change_package',
			host_id: id,
			websize: parseInt(web, 10) || 0,
			sqlsize: parseInt(sql, 10) || 0,
			ll: parseInt(ll, 10) || 0
		});
	}

	function mnbtwpDelete(id) {
		if (!confirm('<?php echo esc_js( __( '删除后站点、数据库、FTP 将被清除且不可恢复，确定删除？', 'wp-seller-plugin' ) ); ?>')) return;
		mnbtwpAdminAjax({ action: 'mnbtwp_admin_host_action', host_action: 'delete', host_id: id });
	}

	function mnbtwpRetry(orderId) {
		if (!confirm('<?php echo esc_js( __( '确定重试开通该订单？', 'wp-seller-plugin' ) ); ?>')) return;
		mnbtwpAdminAjax({ action: 'mnbtwp_admin_retry_provision', order_id: orderId });
	}
</script>
