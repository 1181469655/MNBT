<?php
if(!defined('IN_CRONLITE'))exit();

function monitor_query_ignore_duplicate_column($DB, $sql) {
    $ret = @$DB->query($sql);
    return $ret !== false ? $ret : false;
}

function monitor_allowed_methods() {
    return ['GET', 'POST', 'HEAD'];
}

function monitor_normalize_method($method) {
    $method = strtoupper(trim((string)$method));
    return in_array($method, monitor_allowed_methods(), true) ? $method : 'GET';
}

function monitor_resource_exceeds_threshold($percent, $threshold) {
    return (float)$percent > (float)$threshold;
}

function monitor_normalize_interval($taskType, $interval) {
    if ($taskType === 'resource') return 180;
    return max(15, (int)$interval);
}

function monitor_ensure_tables($DB) {
    $DB->query("CREATE TABLE IF NOT EXISTS `MN_monitor_task` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user` varchar(250) NOT NULL,
        `name` varchar(250) NOT NULL,
        `task_type` varchar(30) NOT NULL DEFAULT 'url',
        `url` varchar(1000) NOT NULL,
        `resource_type` varchar(30) NOT NULL DEFAULT '',
        `resource_threshold` int(11) NOT NULL DEFAULT 80,
        `method` varchar(10) NOT NULL DEFAULT 'GET',
        `interval_seconds` int(11) NOT NULL DEFAULT 60,
        `timeout_seconds` int(11) NOT NULL DEFAULT 10,
        `status_rule` varchar(30) NOT NULL DEFAULT 'eq',
        `status_value` varchar(100) NOT NULL DEFAULT '200',
        `content_rule` varchar(30) NOT NULL DEFAULT 'none',
        `content_value` text,
        `fail_threshold` int(11) NOT NULL DEFAULT 1,
        `notify_email` varchar(10) NOT NULL DEFAULT 'true',
        `enabled` varchar(10) NOT NULL DEFAULT 'true',
        `last_run` varchar(50) DEFAULT NULL,
        `next_run` varchar(50) DEFAULT NULL,
        `last_status` varchar(20) DEFAULT NULL,
        `last_code` int(11) DEFAULT NULL,
        `last_error` text,
        `fail_count` int(11) NOT NULL DEFAULT 0,
        `created_at` varchar(50) NOT NULL,
        `updated_at` varchar(50) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_user` (`user`),
        KEY `idx_next_run` (`enabled`,`next_run`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    monitor_query_ignore_duplicate_column($DB, "ALTER TABLE `MN_monitor_task` ADD `task_type` varchar(30) NOT NULL DEFAULT 'url' AFTER `name`");
    monitor_query_ignore_duplicate_column($DB, "ALTER TABLE `MN_monitor_task` ADD `resource_type` varchar(30) NOT NULL DEFAULT '' AFTER `url`");
    monitor_query_ignore_duplicate_column($DB, "ALTER TABLE `MN_monitor_task` ADD `resource_threshold` int(11) NOT NULL DEFAULT 80 AFTER `resource_type`");

    $DB->query("CREATE TABLE IF NOT EXISTS `MN_monitor_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `task_id` int(11) NOT NULL,
        `user` varchar(250) NOT NULL,
        `url` varchar(1000) NOT NULL,
        `http_code` int(11) DEFAULT NULL,
        `response_time` int(11) DEFAULT NULL,
        `check_status` varchar(20) NOT NULL,
        `error_message` text,
        `response_excerpt` text,
        `notified` varchar(10) NOT NULL DEFAULT 'false',
        `created_at` varchar(50) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_task` (`task_id`),
        KEY `idx_user` (`user`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

    $DB->query("CREATE TABLE IF NOT EXISTS `MN_notice_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user` varchar(250) NOT NULL,
        `type` varchar(50) NOT NULL,
        `title` varchar(250) NOT NULL,
        `content` text NOT NULL,
        `level` varchar(20) NOT NULL DEFAULT 'info',
        `is_read` varchar(10) NOT NULL DEFAULT 'false',
        `created_at` varchar(50) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_user_read` (`user`,`is_read`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    monitor_query_ignore_duplicate_column($DB, "ALTER TABLE `MN_notice_log` ADD `level` varchar(20) NOT NULL DEFAULT 'info' AFTER `content`");
    monitor_query_ignore_duplicate_column($DB, "ALTER TABLE `MN_notice_log` ADD `is_read` varchar(10) NOT NULL DEFAULT 'false' AFTER `level`");
}

function monitor_add_notice($DB, $user, $type, $title, $content, $level = 'info') {
    $DB->query_prepare("INSERT INTO `MN_notice_log` (`user`,`type`,`title`,`content`,`level`,`is_read`,`created_at`) VALUES (?,?,?,?,?,?,?)", [$user, $type, $title, $content, $level, 'false', date('Y-m-d H:i:s')]);
}

function monitor_is_safe_url($url) {
    $parts = parse_url($url);
    if (!$parts || !isset($parts['scheme']) || !isset($parts['host'])) return false;
    if (!in_array(strtolower($parts['scheme']), ['http','https'], true)) return false;
    $host = $parts['host'];
    if (in_array(strtolower($host), ['localhost'], true)) return false;
    $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
    if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) return false;
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

function monitor_status_match($code, $rule, $value) {
    $code = (int)$code;
    $value = trim((string)$value);
    if ($rule === 'eq') return $code === (int)$value;
    if ($rule === 'neq') return $code !== (int)$value;
    if ($rule === 'gte') return $code >= (int)$value;
    if ($rule === 'lte') return $code <= (int)$value;
    if ($rule === 'in') return in_array((string)$code, array_map('trim', explode(',', $value)), true);
    if ($rule === 'not_in') return !in_array((string)$code, array_map('trim', explode(',', $value)), true);
    if ($rule === 'range' && strpos($value, '-') !== false) {
        [$min, $max] = array_map('intval', explode('-', $value, 2));
        return $code >= $min && $code <= $max;
    }
    return false;
}

function monitor_content_match($body, $rule, $value) {
    if ($rule === 'none' || $rule === '') return true;
    $value = (string)$value;
    if ($rule === 'contains') return strpos($body, $value) !== false;
    if ($rule === 'not_contains') return strpos($body, $value) === false;
    return true;
}

function monitor_check_url($task) {
    $url = $task['url'];
    if (!monitor_is_safe_url($url)) {
        return ['ok'=>false,'code'=>0,'time'=>0,'error'=>'URL不安全或不允许访问内网地址','body'=>''];
    }
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    // 跟随跳转只允许 http/https，防止 302 指向 file:// gopher:// 等协议打到本地资源
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    curl_setopt($ch, CURLOPT_TIMEOUT, max(1, min(30, (int)$task['timeout_seconds'])));
    curl_setopt($ch, CURLOPT_USERAGENT, 'MNBT-Monitor/1.0');
    curl_setopt($ch, CURLOPT_HEADER, false);
    $method = monitor_normalize_method($task['method'] ?? 'GET');
    if ($method === 'HEAD') {
        curl_setopt($ch, CURLOPT_NOBODY, true);
    } elseif ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    }
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $time = (int)round((microtime(true) - $start) * 1000);
    if ($body === false) $body = '';
    $body = substr((string)$body, 0, 262144);
    $ok = $err === '' && monitor_status_match($code, $task['status_rule'], $task['status_value']) && monitor_content_match($body, $task['content_rule'], $task['content_value']);
    if ($err === '' && !$ok) $err = '规则不匹配';
    return ['ok'=>$ok,'code'=>$code,'time'=>$time,'error'=>$err,'body'=>$body];
}

function monitor_resource_percent($userRow, $resourceType) {
    if ($resourceType === 'web') $data = json_decode($userRow['hxa'] ?? '', true) ?: [];
    elseif ($resourceType === 'sql') $data = json_decode($userRow['hxb'] ?? '', true) ?: [];
    elseif ($resourceType === 'traffic') $data = json_decode($userRow['llmax'] ?? '', true) ?: [];
    else return null;
    if (!isset($data['max'], $data['dq']) || (float)$data['max'] <= 0) return null;
    $used = (float)$data['dq'];
    $max = (float)$data['max'];
    if ($resourceType === 'traffic') $max = $max * 1024 * 1024 * 1024;
    return round($used / $max * 100, 2);
}

function monitor_resource_name($resourceType) {
    if ($resourceType === 'web') return '网页空间';
    if ($resourceType === 'sql') return '数据库空间';
    if ($resourceType === 'traffic') return '本月流量';
    return '资源';
}

function monitor_send_mail($to, $subject, $message) {
    global $conf;
    if (!$to || empty($conf['mailhost']) || empty($conf['mailuser'])) return false;
    $autoload = ROOT.'mail/vendor/autoload.php';
    if (!is_file($autoload)) return false;
    require_once $autoload;
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $conf['mailhost'];
        $mail->SMTPAuth = true;
        $mail->Username = $conf['mailuser'];
        $mail->Password = $conf['mailpassword'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $conf['mailport'];
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($conf['mailuser'], 'MNBT监控');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
