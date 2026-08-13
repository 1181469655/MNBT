<?php
/**
 * 我的主机视图（[mnbt_my_hosts]）。
 *
 * @package MnbtWp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mnbtwp_nonce = wp_create_nonce( 'mnbtwp_front' );
?>
<div class="mnbtwp-hosts">
	<?php if ( empty( $hosts ) ) : ?>
		<p><?php esc_html_e( '您还没有购买任何主机。', 'wp-seller-plugin' ); ?></p>
	<?php else : ?>
		<?php foreach ( $hosts as $mnbtwp_host ) : ?>
			<?php
			$mnbtwp_provider = isset( $provider_map[ $mnbtwp_host['provider_id'] ] ) ? $provider_map[ $mnbtwp_host['provider_id'] ] : null;
			$mnbtwp_quota    = Helpers::parse_quota( $mnbtwp_host['quota_json'] );
			$mnbtwp_status   = $mnbtwp_host['status'];
			$mnbtwp_label    = Helpers::host_status_label( $mnbtwp_status );
			$mnbtwp_console  = $mnbtwp_provider ? Helpers::control_url( $mnbtwp_provider ) : '';
			?>
			<div class="mnbtwp-host-card" style="border:1px solid #ddd;border-radius:8px;padding:16px;margin-bottom:16px;background:#fff;">
				<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
					<div>
						<strong style="font-size:16px;"><?php echo esc_html( $mnbtwp_host['site_domain'] ? $mnbtwp_host['site_domain'] : $mnbtwp_host['username'] ); ?></strong>
						<span style="margin-left:8px;padding:2px 8px;border-radius:10px;font-size:12px;
							<?php echo in_array( $mnbtwp_status, array( 'active', 'provisioning' ), true ) ? 'background:#e6f7e6;color:#1a7f37;' : 'background:#fdeaea;color:#c62828;'; ?>">
							<?php echo esc_html( $mnbtwp_label ); ?>
						</span>
					</div>
					<div>
						<?php if ( $mnbtwp_console ) : ?>
							<a class="button" href="<?php echo esc_url( $mnbtwp_console ); ?>" target="_blank" rel="noopener">
								<?php esc_html_e( '打开控制台', 'wp-seller-plugin' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<table style="width:100%;margin-top:12px;border-collapse:collapse;font-size:13px;">
					<tr>
						<td style="padding:4px 0;color:#666;width:90px;"><?php esc_html_e( '主机账号', 'wp-seller-plugin' ); ?></td>
						<td><code><?php echo esc_html( $mnbtwp_host['username'] ); ?></code></td>
						<td style="padding:4px 0;color:#666;width:90px;"><?php esc_html_e( '到期时间', 'wp-seller-plugin' ); ?></td>
						<td>
							<?php
							echo esc_html(
								$mnbtwp_host['expire_date'] && '0000-00-00' !== $mnbtwp_host['expire_date']
									? $mnbtwp_host['expire_date']
									: __( '长期', 'wp-seller-plugin' )
							);
							?>
						</td>
					</tr>
				</table>

				<?php foreach ( array( 'web' => __( '网站空间', 'wp-seller-plugin' ), 'sql' => __( '数据库', 'wp-seller-plugin' ), 'flow' => __( '流量', 'wp-seller-plugin' ) ) as $mnbtwp_key => $mnbtwp_name ) : ?>
					<?php
					$mnbtwp_max  = (int) ( $mnbtwp_quota[ $mnbtwp_key ]['max'] ?? 0 );
					$mnbtwp_used = (int) ( $mnbtwp_quota[ $mnbtwp_key ]['used'] ?? 0 );
					if ( $mnbtwp_max <= 0 ) {
						continue;
					}
					$mnbtwp_pct = min( 100, (int) round( $mnbtwp_used / $mnbtwp_max * 100 ) );
					?>
					<div style="margin-top:8px;">
						<div style="display:flex;justify-content:space-between;font-size:12px;color:#666;">
							<span><?php echo esc_html( $mnbtwp_name ); ?></span>
							<span><?php echo (int) $mnbtwp_used; ?> / <?php echo (int) $mnbtwp_max; ?> MB</span>
						</div>
						<div style="background:#eee;border-radius:4px;height:8px;margin-top:4px;">
							<div style="width:<?php echo (int) $mnbtwp_pct; ?>%;height:8px;border-radius:4px;background:<?php echo $mnbtwp_pct >= 90 ? '#d63638' : '#2271b1'; ?>;"></div>
						</div>
					</div>
				<?php endforeach; ?>

				<div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
					<button type="button" class="button" onclick="mnbtwpAction(<?php echo (int) $mnbtwp_host['id']; ?>,'start')"><?php esc_html_e( '启动', 'wp-seller-plugin' ); ?></button>
					<button type="button" class="button" onclick="mnbtwpAction(<?php echo (int) $mnbtwp_host['id']; ?>,'stop')"><?php esc_html_e( '停止', 'wp-seller-plugin' ); ?></button>
					<button type="button" class="button" onclick="mnbtwpChangePass(<?php echo (int) $mnbtwp_host['id']; ?>)"><?php esc_html_e( '重置密码', 'wp-seller-plugin' ); ?></button>
					<button type="button" class="button" onclick="mnbtwpRenew(<?php echo (int) $mnbtwp_host['id']; ?>)"><?php esc_html_e( '续费', 'wp-seller-plugin' ); ?></button>
					<button type="button" class="button" style="color:#c62828;" onclick="mnbtwpDelete(<?php echo (int) $mnbtwp_host['id']; ?>)"><?php esc_html_e( '删除', 'wp-seller-plugin' ); ?></button>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<style>
.mnbtwp-host-card .button { margin: 0; }
</style>

<script type="text/javascript">
	var mnbtwpFrontNonce = '<?php echo esc_js( $mnbtwp_nonce ); ?>';

	function mnbtwpAjax(data, done) {
		data.nonce = mnbtwpFrontNonce;
		jQuery.post('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', data, function (r) {
			if (r && r.success) {
				done(r);
			} else {
				alert((r && r.data) ? r.data : '<?php echo esc_js( __( '操作失败', 'wp-seller-plugin' ) ); ?>');
			}
		}).fail(function () {
			alert('<?php echo esc_js( __( '网络错误', 'wp-seller-plugin' ) ); ?>');
		});
	}

	function mnbtwpAction(hostId, action) {
		if (action === 'delete') return;
		mnbtwpAjax({ action: 'mnbtwp_host_action', host_action: action, host_id: hostId }, function () {
			location.reload();
		});
	}

	function mnbtwpChangePass(hostId) {
		var pw = prompt('<?php echo esc_js( __( '请输入新密码（至少 6 位）', 'wp-seller-plugin' ) ); ?>');
		if (!pw || pw.length < 6) return;
		mnbtwpAjax({ action: 'mnbtwp_host_action', host_action: 'change_pass', host_id: hostId, password: pw }, function () {
			alert('<?php echo esc_js( __( '密码已重置', 'wp-seller-plugin' ) ); ?>');
		});
	}

	function mnbtwpDelete(hostId) {
		if (!confirm('<?php echo esc_js( __( '删除后站点、数据库、FTP 将被清除且不可恢复，确定删除？', 'wp-seller-plugin' ) ); ?>')) return;
		mnbtwpAjax({ action: 'mnbtwp_host_action', host_action: 'delete', host_id: hostId }, function () {
			location.reload();
		});
	}

	function mnbtwpRenew(hostId) {
		var months = prompt('<?php echo esc_js( __( '请输入续费月数（如 1 / 3 / 6 / 12）', 'wp-seller-plugin' ) ); ?>', '1');
		if (!months || parseInt(months, 10) < 1) return;
		mnbtwpAjax({ action: 'mnbtwp_create_renew', host_id: hostId, months: parseInt(months, 10) }, function (r) {
			if (r.data && r.data.checkout_url) {
				window.location.href = r.data.checkout_url;
			}
		});
	}
</script>
