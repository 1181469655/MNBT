<?php
/**
 * API 日志页视图。
 *
 * @package MnbtWp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'MNBT API 日志', 'wp-seller-plugin' ); ?></h1>
	<?php if ( empty( $logs ) ) : ?>
		<p class="description"><?php esc_html_e( '暂无日志。配置节点并测试连接后，这里会记录每次 API 调用（密钥已脱敏）。', 'wp-seller-plugin' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '节点', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '动作', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '参数', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( 'Code', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '消息', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '耗时(s)', 'wp-seller-plugin' ); ?></th>
					<th><?php esc_html_e( '时间', 'wp-seller-plugin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $logs as $mnbtwp_log ) : ?>
					<tr>
						<td><?php echo (int) $mnbtwp_log['id']; ?></td>
						<td><?php echo (int) $mnbtwp_log['provider_id']; ?></td>
						<td><code><?php echo esc_html( $mnbtwp_log['action'] ); ?></code></td>
						<td style="max-width:300px;word-break:break-all;">
							<code style="font-size:11px;"><?php echo esc_html( $mnbtwp_log['params_json'] ); ?></code>
						</td>
						<td>
							<?php if ( 200 === (int) $mnbtwp_log['code'] ) : ?>
								<span style="color:#00a32a;"><?php echo (int) $mnbtwp_log['code']; ?></span>
							<?php else : ?>
								<span style="color:#d63638;"><?php echo (int) $mnbtwp_log['code']; ?></span>
							<?php endif; ?>
						</td>
						<td style="max-width:240px;"><?php echo esc_html( $mnbtwp_log['msg'] ); ?></td>
						<td><?php echo esc_html( $mnbtwp_log['duration'] ); ?></td>
						<td><?php echo esc_html( $mnbtwp_log['created_at'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
