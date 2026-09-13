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
// 候选条件附带 prune_due < 当天：回收上次运行中断遗留的抢占标记（prune_due 被占位但未完成删除的行）
$grace = 7 * 86400;
$pruneCandidates = $DB->get_all_prepare("SELECT * FROM MN_docker_user WHERE qk='expired' AND (prune_due IS NULL OR prune_due < ?) AND expired_at<>'' AND expired_at IS NOT NULL", [$today]) ?: [];
foreach ($pruneCandidates as $u) {
	if (strtotime($u['expired_at']) + $grace > $now) continue; // 未满 7 天
	// 乐观锁抢占：删容器前用条件更新占位，防止与并发续费（api/docker.php xf）交错，
	// affected=0 说明用户已被续费复活或状态已变，跳过该用户，不删其容器
	$DB->query_prepare("UPDATE MN_docker_user SET prune_due=? WHERE id=? AND qk='expired'", [$today, $u['id']]);
	if ($DB->affected() <= 0) {
		$log[] = 'prune-skip: ' . $u['username'] . '（抢占失败，已被续费或状态变更）';
		continue;
	}
	// 删除节点容器：container_id 为空但 service_name 非空时按 service_name 尝试删除（container_del 按 id+name 传参）
	$del_ok = true;
	$del_msg = '';
	if (!empty($u['container_id']) || !empty($u['service_name'])) {
		$node = $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? limit 1", [(int)$u['ssbt']]);
		if ($node) {
			$url = ($node['ptl'] === 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
			$api = new bt_docker($url, $node['btmy']);
			$del_r = $api->container_del((string)($u['container_id'] ?? ''), (string)($u['service_name'] ?? ''));
			if (!($del_r['status'] ?? false)) {
				$del_ok = false;
				$del_msg = (string)($del_r['msg'] ?? '未知错误');
			}
		} else {
			$del_ok = false;
			$del_msg = '节点不存在';
		}
	}
	if (!$del_ok) {
		// 删除失败：不置 pruned（避免出现删不掉容器的死状态），释放抢占标记，下一轮 cron 重试
		$DB->query_prepare("UPDATE MN_docker_user SET prune_due=NULL WHERE id=? AND qk='expired'", [$u['id']]);
		mnbt_log($u['username'], 'Docker清理', '到期删除容器失败：' . $del_msg . '，保留待下轮重试', '删除失败', $DB);
		$log[] = 'prune-failed: ' . $u['username'];
		continue;
	}
	// 置 pruned 同样带 qk='expired' 条件：若期间用户已续费复活则不再改写其状态
	$DB->query_prepare("UPDATE MN_docker_user SET qk='pruned', prune_due=?, container_id=NULL, container_status='none' WHERE id=? AND qk='expired'", [$today, $u['id']]);
	if ($DB->affected() > 0) {
		mnbt_log($u['username'], 'Docker清理', '到期7天删除容器 ' . $u['username'], '已 pruned', $DB);
		$log[] = 'pruned: ' . $u['username'];
	} else {
		mnbt_log($u['username'], 'Docker清理', '置 pruned 前状态已变更（可能已续费），跳过', '警告', $DB);
		$log[] = 'prune-skip: ' . $u['username'] . '（置 pruned 前状态已变更）';
	}
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
