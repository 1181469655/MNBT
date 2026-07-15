<?php
/*
 * 用户端 URL 监控和通知任务
 * 建议每15秒执行一次：/jk_monitor.php?my=后台API密钥
 */
include("./MPHX/common.php");
include_once("./MPHX/monitor.function.php");
if(($_GET['my'] ?? '') != $conf['api']) exit('密钥错误');
monitor_ensure_tables($DB);

$now = date('Y-m-d H:i:s');
$tasks = $DB->get_all_prepare("SELECT * FROM MN_monitor_task WHERE enabled='true' AND (next_run IS NULL OR next_run='' OR next_run<=?) ORDER BY id ASC LIMIT 50", [$now]) ?: [];
$done = 0;
foreach($tasks as $task) {
    if (($task['task_type'] ?? 'url') === 'resource') {
        $userRow = $DB->get_row_prepare("SELECT * FROM MN_zj WHERE user=? limit 1", [$task['user']]);
        $percent = $userRow ? monitor_resource_percent($userRow, $task['resource_type']) : null;
        $ok = $percent !== null && !monitor_resource_exceeds_threshold($percent, $task['resource_threshold']);
        $result = [
            'ok' => $ok,
            'code' => 0,
            'time' => 0,
            'error' => $percent === null ? '无法读取资源用量' : (monitor_resource_name($task['resource_type']).'已使用'.$percent.'%，超过阈值'.$task['resource_threshold'].'%'),
            'body' => ''
        ];
    } else {
        $result = monitor_check_url($task);
    }
    $status = $result['ok'] ? 'ok' : 'fail';
    $fail_count = $result['ok'] ? 0 : ((int)$task['fail_count'] + 1);
    $notified = 'false';
    $excerpt = substr($result['body'], 0, 500);
    if(!$result['ok'] && $fail_count >= (int)$task['fail_threshold']) {
        $title = '监控异常：'.$task['name'];
        $content = ($task['task_type'] ?? 'url') === 'resource'
            ? '资源监控：'.monitor_resource_name($task['resource_type']).'，'.$result['error']
            : 'URL：'.$task['url'].' 状态码：'.$result['code'].' 耗时：'.$result['time'].'ms 错误：'.$result['error'];
        monitor_add_notice($DB, $task['user'], 'monitor', $title, $content, 'warning');
        $u = $DB->get_row_prepare("SELECT mailuser FROM MN_zj WHERE user=? limit 1", [$task['user']]);
        if(($task['notify_email'] ?? 'true') === 'true' && !empty($u['mailuser'])) monitor_send_mail($u['mailuser'], $title, $content);
        $notified = 'true';
    }
    if($result['ok'] && ($task['last_status'] ?? '') === 'fail') {
        $recover = ($task['task_type'] ?? 'url') === 'resource'
            ? '资源监控已恢复正常：'.monitor_resource_name($task['resource_type'])
            : 'URL：'.$task['url'].' 已恢复正常，状态码：'.$result['code'].'，耗时：'.$result['time'].'ms';
        monitor_add_notice($DB, $task['user'], 'monitor', '监控恢复：'.$task['name'], $recover, 'success');
    }
    $DB->query_prepare("INSERT INTO MN_monitor_log (task_id,user,url,http_code,response_time,check_status,error_message,response_excerpt,notified,created_at) VALUES (?,?,?,?,?,?,?,?,?,?)", [$task['id'],$task['user'],$task['url'],$result['code'],$result['time'],$status,$result['error'],$excerpt,$notified,$now]);
    $next = date('Y-m-d H:i:s', time()+monitor_normalize_interval($task['task_type'] ?? 'url', $task['interval_seconds']));
    $DB->query_prepare("UPDATE MN_monitor_task SET last_run=?,next_run=?,last_status=?,last_code=?,last_error=?,fail_count=? WHERE id=?", [$now,$next,$status,$result['code'],$result['error'],$fail_count,$task['id']]);
    $done++;
}

// 到期提醒、空间/流量通知（每天最多按文案去重一次）
$users = $DB->get_all_prepare("SELECT * FROM MN_zj WHERE qk!='false' LIMIT 1000") ?: [];
foreach($users as $u) {
    if(!empty($u['datae']) && $u['datae']!='0000-00-00') {
        $days = floor((strtotime($u['datae']) - time()) / 86400);
        if(in_array($days, [7,3,1,0], true)) {
            $title = '主机到期提醒';
            $content = '您的主机将在 '.$days.' 天后到期，到期时间：'.$u['datae'];
            $exists = $DB->get_row_prepare("SELECT id FROM MN_notice_log WHERE user=? AND title=? AND content=? AND created_at>=? limit 1", [$u['user'],$title,$content,date('Y-m-d 00:00:00')]);
            if(!$exists) monitor_add_notice($DB, $u['user'], 'expire', $title, $content, 'warning');
        }
    }
    $ll = json_decode($u['llmax'] ?? '', true) ?: [];
    if(isset($ll['max'],$ll['dq']) && (float)$ll['max']>0) {
        $percent = round($ll['dq'] / ((float)$ll['max']*1024*1024*1024) * 100, 2);
        if($percent >= 80) {
            $title = '流量使用提醒';
            $content = '您的本月流量已使用 '.$percent.'%，请留意剩余流量。';
            $exists = $DB->get_row_prepare("SELECT id FROM MN_notice_log WHERE user=? AND title=? AND created_at>=? limit 1", [$u['user'],$title,date('Y-m-d 00:00:00')]);
            if(!$exists) monitor_add_notice($DB, $u['user'], 'traffic', $title, $content, $percent>=100?'danger':'warning');
        }
    }
}

if (function_exists('mnbt_do_action')) {
    mnbt_do_action('cron', ['source' => 'jk_monitor', 'done' => $done]);
}

echo '执行完成，检测任务：'.$done;
