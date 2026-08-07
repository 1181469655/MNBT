<?php
/*
 * Docker 到期软删与物理清理定时任务
 * 建议每 30 分钟执行一次：/docker_cron.php?my=后台API密钥
 *
 * 流程：
 *   1) active 但已到期 → qk=expired, expired_at=到期时间
 *   2) expired 满 7 天 → 删除节点容器，qk=pruned, prune_due=当天
 *   3) pruned 满 7 天 → 物理删除用户行
 */
include("./MPHX/common.php");
include_once SYSTEM_ROOT . 'bt_docker.php';
if (($_GET['my'] ?? '') != $conf['api']) exit('密钥错误');
@header('Content-Type: text/plain; charset=UTF-8');

$now = time();
$today = date('Y-m-d', $now);
$log = [];

// 1) active → expired
$expiring = $DB->get_all_prepare("SELECT * FROM MN_docker_user WHERE qk='active' AND datae<>'0000-00-00' AND datae<>'' AND datae < ?", [$today]) ?: [];
foreach ($expiring as $u) {
	$DB->query_prepare("UPDATE MN_docker_user SET qk='expired', expired_at=? WHERE id=?", [$u['datae'], $u['id']]);
	mnbt_log($u['username'], 'Docker到期', '账户到期软删 ' . $u['username'], '已标记 expired', $DB);
	$log[] = 'expired: ' . $u['username'];
}

// 2) expired 满 7 天 → 删除容器 → pruned
$grace = 7 * 86400;
$pruneCandidates = $DB->get_all_prepare("SELECT * FROM MN_docker_user WHERE qk='expired' AND prune_due IS NULL AND expired_at<>'' AND expired_at IS NOT NULL") ?: [];
foreach ($pruneCandidates as $u) {
	if (strtotime($u['expired_at']) + $grace > $now) continue; // 未满 7 天
	// 删除节点容器
	if (!empty($u['container_id'])) {
		$node = $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? limit 1", [(int)$u['ssbt']]);
		if ($node) {
			$url = ($node['ptl'] === 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
			$api = new bt_docker($url, $node['btmy']);
			$api->container_del($u['container_id'], $u['service_name'] ?? '');
		}
	}
	$DB->query_prepare("UPDATE MN_docker_user SET qk='pruned', prune_due=?, container_id=NULL, container_status='none' WHERE id=?", [$today, $u['id']]);
	mnbt_log($u['username'], 'Docker清理', '到期7天删除容器 ' . $u['username'], '已 pruned', $DB);
	$log[] = 'pruned: ' . $u['username'];
}

// 3) pruned 满 7 天 → 物理删除
$pruned = $DB->get_all_prepare("SELECT * FROM MN_docker_user WHERE qk='pruned' AND prune_due IS NOT NULL AND prune_due<>''") ?: [];
foreach ($pruned as $u) {
	if (strtotime($u['prune_due']) + $grace > $now) continue;
	$DB->query_prepare("DELETE FROM MN_docker_user WHERE id=? limit 1", [$u['id']]);
	mnbt_log($u['username'], 'Docker清理', '物理删除到期用户 ' . $u['username'], '已删除', $DB);
	$log[] = 'deleted: ' . $u['username'];
}

echo 'docker_cron done @ ' . date('Y-m-d H:i:s') . PHP_EOL;
echo implode(PHP_EOL, $log);
