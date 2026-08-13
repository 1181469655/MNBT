<?php
/**
 * 连接配置页视图。
 *
 * @package MnbtWp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mnbtwp_nonce = wp_create_nonce( 'mnbtwp_admin' );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'MNBT 连接配置', 'wp-seller-plugin' ); ?></h1>
	<p class="description">
		<?php esc_html_e( '配置 MNBT（梦奈宝塔）节点连接信息。密钥在「系统设置 → API 密钥」与「宝塔列表 → ktmy」处获取。', 'wp-seller-plugin' ); ?>
	</p>

	<div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
		<!-- 左侧：表单 -->
		<div style="min-width:380px;max-width:520px;flex:1;">
			<form id="mnbtwp-provider-form" autocomplete="off">
				<input type="hidden" id="mnbtwp-pid" value="0">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mnbtwp-name"><?php esc_html_e( '名称', 'wp-seller-plugin' ); ?></label></th>
						<td>
							<input type="text" id="mnbtwp-name" class="regular-text" placeholder="<?php esc_attr_e( '如：主节点', 'wp-seller-plugin' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mnbtwp-api-url"><?php esc_html_e( 'MNBT 站点地址', 'wp-seller-plugin' ); ?></label></th>
						<td>
							<input type="text" id="mnbtwp-api-url" class="regular-text" placeholder="https://mnbt.example.com">
							<p class="description"><?php esc_html_e( 'MNBT 站点根地址，接口为 地址/api/api.php', 'wp-seller-plugin' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mnbtwp-btdh"><?php esc_html_e( '宝塔节点代号 (btdh)', 'wp-seller-plugin' ); ?></label></th>
						<td>
							<input type="text" id="mnbtwp-btdh" class="regular-text" placeholder="如 mn123456f">
							<p class="description"><?php esc_html_e( 'MNBT 后台「宝塔列表」中的宝塔编号', 'wp-seller-plugin' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mnbtwp-mn-key"><?php esc_html_e( '系统 API 密钥 (mn_key)', 'wp-seller-plugin' ); ?></label></th>
						<td>
							<input type="password" id="mnbtwp-mn-key" class="regular-text" autocomplete="new-password">
							<p class="description"><?php esc_html_e( 'MNBT 后台「系统设置 → API 密钥」', 'wp-seller-plugin' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mnbtwp-mn-keye"><?php esc_html_e( '节点调用密钥 (mn_keye)', 'wp-seller-plugin' ); ?></label></th>
						<td>
							<input type="password" id="mnbtwp-mn-keye" class="regular-text" autocomplete="new-password">
							<p class="description"><?php esc_html_e( 'MNBT 后台「宝塔列表」ktmy 列 👁 显示的值（md5(ktmy.qmk)）', 'wp-seller-plugin' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mnbtwp-mn-vs"><?php esc_html_e( 'API 版本号 (mn_vs)', 'wp-seller-plugin' ); ?></label></th>
						<td>
							<input type="number" id="mnbtwp-mn-vs" class="small-text" value="15" min="15">
							<p class="description"><?php esc_html_e( '默认 15，MNBT 要求 ≥ 15', 'wp-seller-plugin' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( '启用', 'wp-seller-plugin' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="mnbtwp-qk" checked>
								<?php esc_html_e( '启用该节点', 'wp-seller-plugin' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="button" class="button button-primary" onclick="mnbtwpSaveProvider()"><?php esc_html_e( '保存', 'wp-seller-plugin' ); ?></button>
					<button type="button" class="button" onclick="mnbtwpResetForm()"><?php esc_html_e( '重置', 'wp-seller-plugin' ); ?></button>
				</p>
			</form>
		</div>

		<!-- 右侧：节点列表 -->
		<div style="min-width:520px;flex:2;">
			<h2><?php esc_html_e( '已配置节点', 'wp-seller-plugin' ); ?></h2>
			<?php if ( empty( $providers ) ) : ?>
				<p class="description"><?php esc_html_e( '暂无配置，请在左侧添加。', 'wp-seller-plugin' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'wp-seller-plugin' ); ?></th>
							<th><?php esc_html_e( '名称', 'wp-seller-plugin' ); ?></th>
							<th><?php esc_html_e( 'MNBT 地址', 'wp-seller-plugin' ); ?></th>
							<th><?php esc_html_e( '节点代号', 'wp-seller-plugin' ); ?></th>
							<th><?php esc_html_e( '状态', 'wp-seller-plugin' ); ?></th>
							<th><?php esc_html_e( '操作', 'wp-seller-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $providers as $mnbtwp_p ) : ?>
							<tr>
								<td><?php echo (int) $mnbtwp_p['id']; ?></td>
								<td><?php echo esc_html( $mnbtwp_p['name'] ); ?></td>
								<td><?php echo esc_html( $mnbtwp_p['api_url'] ); ?></td>
								<td><code><?php echo esc_html( $mnbtwp_p['btdh'] ); ?></code></td>
								<td>
									<?php if ( 'true' === $mnbtwp_p['qk'] ) : ?>
										<span class="dashicons dashicons-yes" style="color:#00a32a;" title="<?php esc_attr_e( '启用', 'wp-seller-plugin' ); ?>"></span>
									<?php else : ?>
										<span class="dashicons dashicons-no" style="color:#d63638;" title="<?php esc_attr_e( '禁用', 'wp-seller-plugin' ); ?>"></span>
									<?php endif; ?>
								</td>
								<td>
									<button type="button" class="button button-small" onclick="mnbtwpEditProvider(<?php echo (int) $mnbtwp_p['id']; ?>)"><?php esc_html_e( '编辑', 'wp-seller-plugin' ); ?></button>
									<button type="button" class="button button-small" onclick="mnbtwpTestProvider(<?php echo (int) $mnbtwp_p['id']; ?>)"><?php esc_html_e( '测试连接', 'wp-seller-plugin' ); ?></button>
									<button type="button" class="button button-small button-link-delete" onclick="mnbtwpDeleteProvider(<?php echo (int) $mnbtwp_p['id']; ?>)"><?php esc_html_e( '删除', 'wp-seller-plugin' ); ?></button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	var mnbtwpNonce = '<?php echo esc_js( $mnbtwp_nonce ); ?>';

	function mnbtwpResetForm() {
		document.getElementById('mnbtwp-pid').value = '0';
		document.getElementById('mnbtwp-name').value = '';
		document.getElementById('mnbtwp-api-url').value = '';
		document.getElementById('mnbtwp-btdh').value = '';
		document.getElementById('mnbtwp-mn-key').value = '';
		document.getElementById('mnbtwp-mn-keye').value = '';
		document.getElementById('mnbtwp-mn-vs').value = '15';
		document.getElementById('mnbtwp-qk').checked = true;
	}

	function mnbtwpSaveProvider() {
		var data = {
			action: 'mnbtwp_save_provider',
			nonce: mnbtwpNonce,
			id: document.getElementById('mnbtwp-pid').value,
			name: document.getElementById('mnbtwp-name').value,
			api_url: document.getElementById('mnbtwp-api-url').value,
			btdh: document.getElementById('mnbtwp-btdh').value,
			mn_key: document.getElementById('mnbtwp-mn-key').value,
			mn_keye: document.getElementById('mnbtwp-mn-keye').value,
			mn_vs: document.getElementById('mnbtwp-mn-vs').value,
			qk: document.getElementById('mnbtwp-qk').checked ? '1' : '0'
		};
		jQuery.post(ajaxurl, data, function (r) {
			if (r && r.success) {
				alert(r.data);
				location.reload();
			} else {
				alert((r && r.data) ? r.data : '<?php echo esc_js( __( '保存失败', 'wp-seller-plugin' ) ); ?>');
			}
		}).fail(function () {
			alert('<?php echo esc_js( __( '网络错误', 'wp-seller-plugin' ) ); ?>');
		});
	}

	function mnbtwpEditProvider(id) {
		jQuery.post(ajaxurl, { action: 'mnbtwp_get_provider', nonce: mnbtwpNonce, id: id }, function (r) {
			if (!r || !r.success) {
				alert((r && r.data) ? r.data : '<?php echo esc_js( __( '获取失败', 'wp-seller-plugin' ) ); ?>');
				return;
			}
			var d = r.data;
			document.getElementById('mnbtwp-pid').value = d.id;
			document.getElementById('mnbtwp-name').value = d.name;
			document.getElementById('mnbtwp-api-url').value = d.api_url;
			document.getElementById('mnbtwp-btdh').value = d.btdh;
			document.getElementById('mnbtwp-mn-key').value = d.mn_key;
			document.getElementById('mnbtwp-mn-keye').value = d.mn_keye;
			document.getElementById('mnbtwp-mn-vs').value = d.mn_vs;
			document.getElementById('mnbtwp-qk').checked = (d.qk === 'true');
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	function mnbtwpTestProvider(id) {
		jQuery.post(ajaxurl, { action: 'mnbtwp_test_provider', nonce: mnbtwpNonce, id: id }, function (r) {
			if (r && r.success) {
				alert(r.data);
			} else {
				alert((r && r.data) ? r.data : '<?php echo esc_js( __( '连接失败', 'wp-seller-plugin' ) ); ?>');
			}
		});
	}

	function mnbtwpDeleteProvider(id) {
		if (!confirm('<?php echo esc_js( __( '确定删除该节点配置？不影响 MNBT 中已有主机。', 'wp-seller-plugin' ) ); ?>')) return;
		jQuery.post(ajaxurl, { action: 'mnbtwp_delete_provider', nonce: mnbtwpNonce, id: id }, function (r) {
			if (r && r.success) {
				location.reload();
			} else {
				alert((r && r.data) ? r.data : '<?php echo esc_js( __( '删除失败', 'wp-seller-plugin' ) ); ?>');
			}
		});
	}
</script>
