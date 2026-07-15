<?php
$mnbt_admin_node_actions = [
    'listnode',
    'addnode',
    'delnode',
    'setnodestatus',
    'nodeconfig',
    'nodeping',
    'nodeforbiddenscan',
    'listnodetask',
    'listforbiddenscan',
    'listforbiddenmatch',
    'nodestats',
    'get_global_keywords',
    'clearoldscans',
    'savescancfg',
    'nodeloglist',
    'nodelogcontent',
    'nodelogclear',
    'nodeloglevel',
    'nodelogstats',
    'reset_sitestats',
];
if (!in_array($egn, $mnbt_admin_node_actions, true)) return;
require_once ROOT . 'MPHX/node.function.php';
mnbt_node_ensure_tables($DB);

function mnbt_admin_node_exit($success, $msg, $extra = []) {
    $data = array_merge([
        'success' => (bool)$success,
        'code' => $success ? 1 : 0,
        'msg' => $msg,
    ], $extra);
    exit(json_encode($data, JSON_UNESCAPED_UNICODE));
}

function mnbt_admin_node_sort($sort, $allowed, $default) {
    $sort = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$sort);
    return isset($allowed[$sort]) ? $allowed[$sort] : $allowed[$default];
}

function mnbt_admin_node_order($order) {
    return strtoupper((string)$order) === 'DESC' ? 'DESC' : 'ASC';
}

function mnbt_admin_node_row($DB, $id) {
    return $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `id`=? LIMIT 1", [(int)$id]);
}

function mnbt_admin_node_site_url() {
    $url = trim((string)($_POST['mnbt_url'] ?? ''));
    return $url === '' ? mnbt_node_default_base_url() : rtrim($url, '/');
}

function mnbt_admin_node_keywords($text) {
    $text = str_replace(["\r\n", "\r", ";", "\xEF\xBC\x9B", "\xEF\xBC\x8C"], "\n", (string)$text);
    $parts = preg_split('/[\n,]+/', $text);
    $keywords = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '' && !in_array($part, $keywords, true)) $keywords[] = $part;
    }
    return $keywords;
}

function mnbt_admin_node_log($user, $title, $content, $DB) {
    if (function_exists('logjl')) logjl($user, $title, $content, '操作成功', $DB);
}

if($egn=='listnode') {
    $limit = max(1, min(100, (int)($_POST['limit'] ?? 10)));
    $page = max(1, (int)($_POST['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $sortMap = [
        'id' => 'n.id',
        'node_id' => 'n.node_id',
        'node_name' => 'n.node_name',
        'bt_id' => 'n.bt_id',
        'status' => 'n.status',
        'enabled' => 'n.enabled',
        'ip' => 'n.ip',
        'version' => 'n.version',
        'last_heartbeat' => 'n.last_heartbeat',
        'created_at' => 'n.created_at',
        'updated_at' => 'n.updated_at',
    ];
    $sort = mnbt_admin_node_sort($_POST['sort'] ?? 'id', $sortMap, 'id');
    $order = mnbt_admin_node_order($_POST['sortOrder'] ?? 'DESC');
    $keyword = trim((string)($_POST['keyword'] ?? ''));
    $status = trim((string)($_POST['status'] ?? ''));
    $where = 'WHERE 1';
    $params = [];
    if ($keyword !== '') {
        $where .= " AND (n.node_id LIKE ? OR n.node_name LIKE ? OR n.ip LIKE ? OR b.btdh LIKE ? OR b.btip LIKE ?)";
        $like = '%' . $keyword . '%';
        array_push($params, $like, $like, $like, $like, $like);
    }
    if ($status === 'enabled') {
        $where .= " AND n.enabled='true'";
    } elseif ($status === 'disabled') {
        $where .= " AND n.enabled='false'";
    }
    $total = $DB->count_prepare("SELECT count(*) FROM `MN_node` n LEFT JOIN `MN_bt` b ON n.bt_id=b.id {$where}", $params);
    $rows = $DB->get_all_prepare("SELECT n.*, b.btdh, b.btip FROM `MN_node` n LEFT JOIN `MN_bt` b ON n.bt_id=b.id {$where} ORDER BY {$sort} {$order} LIMIT {$offset},{$limit}", $params);
    if (!is_array($rows)) $rows = [];
    foreach ($rows as &$row) {
        $row['display_status'] = mnbt_node_effective_status($row);
        $row['capabilities_text'] = implode(', ', mnbt_node_normalize_json($row['capabilities'] ?? '[]'));
    }
    unset($row);
    exit(json_encode(['total' => (int)$total, 'rows' => $rows], JSON_UNESCAPED_UNICODE));
}

if($egn=='addnode') {
    $btId = (int)($_POST['bt_id'] ?? 0);
    if ($btId <= 0) mnbt_admin_node_exit(false, '请选择要绑定的宝塔服务器');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [$btId]);
    if (!$bt) mnbt_admin_node_exit(false, '宝塔服务器不存在');
    $nodeName = trim((string)($_POST['node_name'] ?? ''));
    if ($nodeName === '') $nodeName = (string)($bt['btdh'] ?? ('node-' . $btId));
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    if ($nodeId !== '' && !preg_match('/^[a-zA-Z0-9_.:-]{1,64}$/', $nodeId)) {
        mnbt_admin_node_exit(false, '节点ID只能包含字母、数字、点、横线、下划线和冒号，最长64位');
    }
    if ($nodeId !== '' && $DB->get_row_prepare("SELECT id FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId])) {
        mnbt_admin_node_exit(false, '节点ID已存在，请更换或直接复制原节点配置');
    }
    $registered = mnbt_node_register($DB, $btId, $nodeName, $nodeId);
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$registered['node_id']]);
    $config = mnbt_node_admin_config($conf, $node, mnbt_admin_node_site_url(), (int)($_POST['interval_seconds'] ?? 10));
    mnbt_admin_node_log($user, '节点管理', '新增节点 '.$registered['node_id'], $DB);
    mnbt_admin_node_exit(true, '新增节点成功', [
        'id' => (int)($node['id'] ?? 0),
        'node_id' => $registered['node_id'],
        'config' => $config,
        'config_json' => json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

if($egn=='delnode') {
    $id = (int)($_POST['id'] ?? 0);
    $node = mnbt_admin_node_row($DB, $id);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $nodeId = $node['node_id'];
    $DB->query_prepare("DELETE FROM `MN_node_task` WHERE `node_id`=?", [$nodeId]);
    $DB->query_prepare("DELETE FROM `MN_node_nonce` WHERE `node_id`=?", [$nodeId]);
    $DB->query_prepare("DELETE FROM `MN_forbidden_scan` WHERE `node_id`=?", [$nodeId]);
    $DB->query_prepare("DELETE FROM `MN_forbidden_match` WHERE `node_id`=?", [$nodeId]);
    $ok = $DB->query_prepare("DELETE FROM `MN_node` WHERE `id`=? LIMIT 1", [$id]);
    if ($ok && $DB->affected() > 0) {
        mnbt_admin_node_log($user, '节点管理', '删除节点 '.$nodeId, $DB);
        mnbt_admin_node_exit(true, '删除成功');
    }
    mnbt_admin_node_exit(false, '删除失败'.$DB->error());
}

if($egn=='setnodestatus') {
    $id = (int)($_POST['id'] ?? 0);
    $node = mnbt_admin_node_row($DB, $id);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $enabled = (($_POST['enabled'] ?? 'true') === 'true') ? 'true' : 'false';
    $status = $enabled === 'true' ? 'offline' : 'disabled';
    $ok = $DB->query_prepare("UPDATE `MN_node` SET `enabled`=?, `status`=?, `updated_at`=? WHERE `id`=?", [$enabled, $status, date('Y-m-d H:i:s'), $id]);
    if ($ok && $DB->affected() > 0) {
        mnbt_admin_node_log($user, '节点管理', ($enabled === 'true' ? '启用节点 ' : '停用节点 ').$node['node_id'], $DB);
        mnbt_admin_node_exit(true, $enabled === 'true' ? '启用成功' : '停用成功');
    }
    mnbt_admin_node_exit(false, '操作失败'.$DB->error());
}

if($egn=='nodeconfig') {
    $id = (int)($_POST['id'] ?? 0);
    $node = mnbt_admin_node_row($DB, $id);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $config = mnbt_node_admin_config($conf, $node, mnbt_admin_node_site_url(), (int)($_POST['interval_seconds'] ?? 10));
    mnbt_admin_node_exit(true, '获取成功', [
        'config' => $config,
        'config_json' => json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

if($egn=='nodeping') {
    $id = (int)($_POST['id'] ?? 0);
    $node = mnbt_admin_node_row($DB, $id);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    if (($node['enabled'] ?? 'true') !== 'true') mnbt_admin_node_exit(false, '节点已停用，不能下发任务');
    $taskId = mnbt_node_create_task($DB, $node['node_id'], 'ping', [
        'created_by' => 'admin',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    mnbt_admin_node_log($user, '节点管理', '下发Ping任务 '.$taskId, $DB);
    mnbt_admin_node_exit(true, 'Ping任务已下发', ['task_id' => $taskId]);
}

if($egn=='nodeforbiddenscan') {
    $id = (int)($_POST['id'] ?? 0);
    $node = mnbt_admin_node_row($DB, $id);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    if (($node['enabled'] ?? 'true') !== 'true') mnbt_admin_node_exit(false, '节点已停用，不能下发任务');
    $root = trim((string)($_POST['root'] ?? ''));
    if ($root === '') mnbt_admin_node_exit(false, '请填写扫描目录');
    $keywords = mnbt_admin_node_keywords($_POST['keywords'] ?? '');
    if (empty($keywords)) mnbt_admin_node_exit(false, '请填写违禁词');
    $site = trim((string)($_POST['site'] ?? ''));
    $maxFileSizeMb = max(1, min(50, (int)($_POST['max_file_size_mb'] ?? 5)));
    $maxMatches = max(1, min(5000, (int)($_POST['max_matches'] ?? 1000)));
    $taskId = mnbt_node_create_task($DB, $node['node_id'], 'forbidden_scan', [
        'site' => $site,
        'root' => $root,
        'keywords' => $keywords,
        'max_file_size' => $maxFileSizeMb * 1024 * 1024,
        'max_matches' => $maxMatches,
        'scan_changed_only' => false,
        'scan_mode' => 'full',
    ]);
    mnbt_admin_node_log($user, '节点管理', '下发违禁词扫描任务 '.$taskId, $DB);
    mnbt_admin_node_exit(true, '违禁词扫描任务已下发', ['task_id' => $taskId]);
}

if($egn=='listnodetask') {
    $limit = max(1, min(100, (int)($_POST['limit'] ?? 10)));
    $page = max(1, (int)($_POST['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $sortMap = [
        'id' => 'id',
        'task_id' => 'task_id',
        'node_id' => 'node_id',
        'action' => 'action',
        'status' => 'status',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'finished_at' => 'finished_at',
    ];
    $sort = mnbt_admin_node_sort($_POST['sort'] ?? 'id', $sortMap, 'id');
    $order = mnbt_admin_node_order($_POST['sortOrder'] ?? 'DESC');
    $where = 'WHERE 1';
    $params = [];

    // 节点筛选
    $id = (int)($_POST['node_pk'] ?? 0);
    if ($id > 0) {
        $node = mnbt_admin_node_row($DB, $id);
        if ($node) {
            $where .= ' AND `node_id`=?';
            $params[] = $node['node_id'];
        }
    }

    // 状态筛选
    $status = trim((string)($_POST['status'] ?? ''));
    if ($status === 'pending' || $status === 'running' || $status === 'success' || $status === 'failed') {
        $where .= ' AND `status`=?';
        $params[] = $status;
    }

    // 任务类型筛选
    $action = trim((string)($_POST['action'] ?? ''));
    if ($action === 'ping' || $action === 'forbidden_scan') {
        $where .= ' AND `action`=?';
        $params[] = $action;
    }

    $total = $DB->count_prepare("SELECT count(*) FROM `MN_node_task` {$where}", $params);
    $rows = $DB->get_all_prepare("SELECT * FROM `MN_node_task` {$where} ORDER BY {$sort} {$order} LIMIT {$offset},{$limit}", $params);
    if (!is_array($rows)) $rows = [];
    foreach ($rows as &$row) {
        $payload = mnbt_node_normalize_json($row['payload'] ?? '');
        $result = mnbt_node_normalize_json($row['result'] ?? '');
        $row['payload_text'] = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $row['result_text'] = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    unset($row);
    exit(json_encode(['total' => (int)$total, 'rows' => $rows], JSON_UNESCAPED_UNICODE));
}

if($egn=='listforbiddenscan') {
    $limit = max(1, min(100, (int)($_POST['limit'] ?? 10)));
    $page = max(1, (int)($_POST['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $sortMap = [
        'id' => 'id',
        'task_id' => 'task_id',
        'node_id' => 'node_id',
        'site' => 'site',
        'status' => 'status',
        'matches_count' => 'matches_count',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];
    $sort = mnbt_admin_node_sort($_POST['sort'] ?? 'id', $sortMap, 'id');
    $order = mnbt_admin_node_order($_POST['sortOrder'] ?? 'DESC');
    $where = 'WHERE 1';
    $params = [];

    // 节点筛选
    $id = (int)($_POST['node_pk'] ?? 0);
    if ($id > 0) {
        $node = mnbt_admin_node_row($DB, $id);
        if ($node) {
            $where .= ' AND `node_id`=?';
            $params[] = $node['node_id'];
        }
    }

    // 时间筛选
    $timeFilter = trim((string)($_POST['time_filter'] ?? ''));
    if ($timeFilter === 'today') {
        $where .= ' AND DATE(created_at)=?';
        $params[] = date('Y-m-d');
    } elseif ($timeFilter === 'yesterday') {
        $where .= ' AND DATE(created_at)=?';
        $params[] = date('Y-m-d', strtotime('-1 day'));
    } elseif ($timeFilter === 'week') {
        $where .= ' AND created_at >= ?';
        $params[] = date('Y-m-d', strtotime('-7 days'));
    } elseif ($timeFilter === 'month') {
        $where .= ' AND created_at >= ?';
        $params[] = date('Y-m-d', strtotime('-30 days'));
    }

    // 状态筛选
    $statusFilter = trim((string)($_POST['status_filter'] ?? ''));
    if ($statusFilter === 'success') {
        $where .= ' AND `status`=?';
        $params[] = 'success';
    } elseif ($statusFilter === 'failed') {
        $where .= ' AND `status`=?';
        $params[] = 'failed';
    } elseif ($statusFilter === 'has_matches') {
        $where .= ' AND matches_count > 0';
    }

    $total = $DB->count_prepare("SELECT count(*) FROM `MN_forbidden_scan` {$where}", $params);
    $rows = $DB->get_all_prepare("SELECT * FROM `MN_forbidden_scan` {$where} ORDER BY {$sort} {$order} LIMIT {$offset},{$limit}", $params);
    if (!is_array($rows)) $rows = [];
    exit(json_encode(['total' => (int)$total, 'rows' => $rows], JSON_UNESCAPED_UNICODE));
}

if($egn=='listforbiddenmatch') {
    $limit = max(1, min(100, (int)($_POST['limit'] ?? 10)));
    $page = max(1, (int)($_POST['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $taskId = trim((string)($_POST['task_id'] ?? ''));
    $where = 'WHERE 1';
    $params = [];
    if ($taskId !== '') {
        $where .= ' AND `task_id`=?';
        $params[] = $taskId;
    }
    $total = $DB->count_prepare("SELECT count(*) FROM `MN_forbidden_match` {$where}", $params);
    $rows = $DB->get_all_prepare("SELECT * FROM `MN_forbidden_match` {$where} ORDER BY id DESC LIMIT {$offset},{$limit}", $params);
    if (!is_array($rows)) $rows = [];
    exit(json_encode(['total' => (int)$total, 'rows' => $rows], JSON_UNESCAPED_UNICODE));
}

// 获取统计数据
if($egn=='nodestats') {
    $now = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // 节点统计
    $totalNodes = $DB->count_prepare("SELECT COUNT(*) FROM `MN_node`", []);
    $onlineNodes = 0;
    $offlineNodes = 0;
    $nodes = $DB->get_all_prepare("SELECT * FROM `MN_node`", []);
    if (is_array($nodes)) {
        foreach ($nodes as $node) {
            $status = mnbt_node_effective_status($node);
            if ($status === 'online') $onlineNodes++;
            else $offlineNodes++;
        }
    }

    // 今日命中统计
    $todayMatches = $DB->count_prepare("SELECT SUM(matches_count) FROM `MN_forbidden_scan` WHERE DATE(created_at)=?", [$now]) ?: 0;

    // 待处理任务统计
    $pendingTasks = $DB->count_prepare("SELECT COUNT(*) FROM `MN_node_task` WHERE `status`='pending'", []);

    mnbt_admin_node_exit(true, 'ok', [
        'data' => [
            'total_nodes' => (int)$totalNodes,
            'online' => (int)$onlineNodes,
            'offline' => (int)$offlineNodes,
            'today_matches' => (int)$todayMatches,
            'pending_tasks' => (int)$pendingTasks,
        ]
    ]);
}

// 获取全局违禁词配置
if($egn=='get_global_keywords') {
    global $conf;
    $content = $conf['wjsccnr'] ?? '';
    $keywords = [];
    if ($content) {
        $lines = preg_split('/[\r\n]+/', $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && strpos($line, '#') !== 0) {
                $keywords[] = $line;
            }
        }
    }
    mnbt_admin_node_exit(true, 'ok', [
        'data' => implode("\n", $keywords)
    ]);
}

// 清理旧扫描记录
if($egn=='clearoldscans') {
    $days = (int)($_POST['days'] ?? 7);
    if ($days < 1) $days = 7;
    if ($days > 365) $days = 365;
    $cutoffDate = date('Y-m-d H:i:s', strtotime('-'.$days.' days'));

    // 先查出要清理的 task_id，避免不同 MySQL 版本对子查询删除支持不一致
    $oldScans = $DB->get_all_prepare("SELECT task_id FROM `MN_forbidden_scan` WHERE `created_at` < ?", [$cutoffDate]);
    if (!is_array($oldScans) || count($oldScans) === 0) {
        mnbt_admin_node_exit(true, $days.'天前没有可清理的扫描记录', [
            'deleted_scans' => 0,
            'deleted_matches' => 0,
            'deleted_tasks' => 0,
        ]);
    }

    $deletedScans = 0;
    $deletedMatches = 0;
    $deletedTasks = 0;
    foreach ($oldScans as $scan) {
        $taskId = (string)($scan['task_id'] ?? '');
        if ($taskId === '') continue;

        $matchCount = $DB->count_prepare("SELECT COUNT(*) FROM `MN_forbidden_match` WHERE `task_id`=?", [$taskId]);
        $DB->query_prepare("DELETE FROM `MN_forbidden_match` WHERE `task_id`=?", [$taskId]);
        $deletedMatches += (int)$matchCount;

        $DB->query_prepare("DELETE FROM `MN_forbidden_scan` WHERE `task_id`=?", [$taskId]);
        $deletedScans++;

        // 同步清理对应的节点任务日志，避免任务列表里还显示旧扫描任务
        $taskCount = $DB->count_prepare("SELECT COUNT(*) FROM `MN_node_task` WHERE `task_id`=? AND `action`='forbidden_scan'", [$taskId]);
        $DB->query_prepare("DELETE FROM `MN_node_task` WHERE `task_id`=? AND `action`='forbidden_scan'", [$taskId]);
        $deletedTasks += (int)$taskCount;
    }

    mnbt_admin_node_exit(true, '清理完成：扫描记录 '.$deletedScans.' 条，命中记录 '.$deletedMatches.' 条，任务记录 '.$deletedTasks.' 条', [
        'deleted_scans' => $deletedScans,
        'deleted_matches' => $deletedMatches,
        'deleted_tasks' => $deletedTasks,
    ]);
}

if($egn=='savescancfg') {
    $skg = ($_POST['skg'] ?? 'false') === 'true' ? 'true' : 'false';
    $snr = (string)($_POST['snr'] ?? '');
    $sgbfx = ($_POST['sgbfx'] ?? 'true') === 'true' ? 'true' : 'false';
    $sml = trim((string)($_POST['sml'] ?? '/www/wwwroot'));
    $stqml = trim((string)($_POST['stqml'] ?? '.git,node_modules,vendor,runtime,cache,logs'));
    $stqhz = trim((string)($_POST['stqhz'] ?? '.jpg,.png,.gif,.webp,.mp4,.zip,.rar,.7z,.pdf,.woff,.ttf'));
    $sdzmax = max(1, (int)($_POST['sdzmax'] ?? 5)) * 1024 * 1024;
    $sdhmax = max(1, (int)($_POST['sdhmax'] ?? 1000));
    $sqzcskg = ($_POST['sqzcskg'] ?? 'true') === 'true' ? 'true' : 'false';
    $sqzcs = trim((string)($_POST['sqzcs'] ?? '0 3 * * *'));

    $currentKg = $conf['wjsckg'] ?? 'false';
    $wasDisabled = ($currentKg !== 'true');
    $isNowEnabled = ($skg === 'true');

    if (function_exists('logjl')) logjl($user, '违禁词扫描设置', '对违禁词扫描设置进行了修改', '修改成功', $DB);

    $sql = "UPDATE `MN_config` SET `wjsckg` = ?, `wjsccnr` = ?, `wjsckgqbfx` = ?, `wjscml` = ?, `wjstqml` = ?, `wjstqhz` = ?, `wjscdzmax` = ?, `wjscdhmax` = ?, `wjscqzcskg` = ?, `wjscqzcs` = ? WHERE `id` = 1";
    if(!$DB->query_prepare($sql, [$skg, $snr, $sgbfx, $sml, $stqml, $stqhz, $sdzmax, $sdhmax, $sqzcskg, $sqzcs])) {
        mnbt_admin_node_exit(false, '数据库操作失败请联系开发人员判断错误');
    }

    if($wasDisabled && $isNowEnabled) {
        $keywords = [];
        if($snr) {
            $lines = preg_split('/[\r\n]+/', $snr);
            foreach($lines as $line) {
                $line = trim($line);
                if($line && strpos($line, '#') !== 0) $keywords[] = $line;
            }
        }
        if(!empty($keywords)) {
            $nodes = $DB->get_all_prepare("SELECT * FROM `MN_node` WHERE `enabled`='true'", []);
            if(!$nodes) $nodes = [];
            $triggeredCount = 0;
            foreach($nodes as $node) {
                try {
                    mnbt_node_create_task($DB, $node['node_id'], 'forbidden_scan', [
                        'site' => '',
                        'root' => $sml,
                        'keywords' => $keywords,
                        'max_file_size' => $sdzmax,
                        'max_matches' => $sdhmax,
                        'scan_changed_only' => false,
                        'scan_mode' => 'full',
                        'auto_trigger' => true,
                        'trigger_reason' => '扫描开关已开启',
                    ]);
                    $triggeredCount++;
                } catch(Exception $e) {}
            }
            if($triggeredCount > 0) {
                mnbt_admin_node_exit(true, '修改成功，已向 '.$triggeredCount.' 个节点下发全量扫描任务');
            }
        }
    }

    mnbt_admin_node_exit(true, '修改成功');
}

if($egn=='nodeloglist') {
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    if ($nodeId === '') mnbt_admin_node_exit(false, '缺少节点ID');
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [(int)($node['bt_id'] ?? 0)]);
    if (!$bt) mnbt_admin_node_exit(false, '节点未绑定宝塔面板');
    try {
        $btApi = new bt_api($bt['panel'], $bt['btkey']);
        $result = $btApi->pluginRequest('mnbt_connector', 'get_log_list', []);
        if (!is_array($result) || !($result['status'] ?? false)) {
            mnbt_admin_node_exit(false, '获取日志列表失败：' . ($result['msg'] ?? '未知错误'));
        }
        mnbt_admin_node_exit(true, '获取成功', ['data' => $result['data'] ?? [], 'total_count' => $result['total_count'] ?? 0]);
    } catch(Exception $e) {
        mnbt_admin_node_exit(false, '连接节点失败：' . $e->getMessage());
    }
}

if($egn=='nodelogcontent') {
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    $logFile = trim((string)($_POST['log_file'] ?? ''));
    $offset = max(0, (int)($_POST['offset'] ?? 0));
    $limit = max(1000, min(500000, (int)($_POST['limit'] ?? 50000)));
    $keyword = trim((string)($_POST['keyword'] ?? ''));
    $level = trim((string)($_POST['level'] ?? ''));
    if ($nodeId === '') mnbt_admin_node_exit(false, '缺少节点ID');
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [(int)($node['bt_id'] ?? 0)]);
    if (!$bt) mnbt_admin_node_exit(false, '节点未绑定宝塔面板');
    try {
        $btApi = new bt_api($bt['panel'], $bt['btkey']);
        $result = $btApi->pluginRequest('mnbt_connector', 'get_log_content', [
            'file' => $logFile,
            'offset' => $offset,
            'limit' => $limit,
            'keyword' => $keyword,
            'level' => $level,
        ]);
        if (!is_array($result) || !($result['status'] ?? false)) {
            mnbt_admin_node_exit(false, '获取日志内容失败：' . ($result['msg'] ?? '未知错误'));
        }
        mnbt_admin_node_exit(true, '获取成功', [
            'data' => $result['data'] ?? [],
            'total_lines' => $result['total_lines'] ?? 0,
            'file_size' => $result['file_size'] ?? 0,
            'current_offset' => $result['current_offset'] ?? 0,
            'has_more' => $result['has_more'] ?? false,
        ]);
    } catch(Exception $e) {
        mnbt_admin_node_exit(false, '连接节点失败：' . $e->getMessage());
    }
}

if($egn=='nodelogclear') {
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    $logFile = trim((string)($_POST['log_file'] ?? ''));
    if ($nodeId === '') mnbt_admin_node_exit(false, '缺少节点ID');
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [(int)($node['bt_id'] ?? 0)]);
    if (!$bt) mnbt_admin_node_exit(false, '节点未绑定宝塔面板');
    try {
        $btApi = new bt_api($bt['panel'], $bt['btkey']);
        $result = $btApi->pluginRequest('mnbt_connector', 'clear_log', ['file' => $logFile]);
        if (!is_array($result) || !($result['status'] ?? false)) {
            mnbt_admin_node_exit(false, '清空日志失败：' . ($result['msg'] ?? '未知错误'));
        }
        if (function_exists('logjl')) logjl($user, '节点日志', '清空节点 '.$nodeId.' 日志', '操作成功', $DB);
        mnbt_admin_node_exit(true, $result['msg'] ?? '清空成功');
    } catch(Exception $e) {
        mnbt_admin_node_exit(false, '连接节点失败：' . $e->getMessage());
    }
}

if($egn=='nodeloglevel') {
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    $level = trim((string)($_POST['level'] ?? ''));
    if ($nodeId === '') mnbt_admin_node_exit(false, '缺少节点ID');
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [(int)($node['bt_id'] ?? 0)]);
    if (!$bt) mnbt_admin_node_exit(false, '节点未绑定宝塔面板');
    try {
        $btApi = new bt_api($bt['panel'], $bt['btkey']);
        if ($level !== '') {
            if (!in_array($level, ['DEBUG', 'INFO', 'WARNING', 'ERROR'], true)) {
                mnbt_admin_node_exit(false, '无效的日志级别');
            }
            $result = $btApi->pluginRequest('mnbt_connector', 'set_log_level', ['level' => $level]);
            if (!is_array($result) || !($result['status'] ?? false)) {
                mnbt_admin_node_exit(false, '设置日志级别失败：' . ($result['msg'] ?? '未知错误'));
            }
            if (function_exists('logjl')) logjl($user, '节点日志', '设置节点 '.$nodeId.' 日志级别为 '.$level, '操作成功', $DB);
            mnbt_admin_node_exit(true, $result['msg'] ?? '设置成功');
        } else {
            $result = $btApi->pluginRequest('mnbt_connector', 'get_log_level', []);
            if (!is_array($result) || !($result['status'] ?? false)) {
                mnbt_admin_node_exit(false, '获取日志级别失败：' . ($result['msg'] ?? '未知错误'));
            }
            mnbt_admin_node_exit(true, '获取成功', [
                'level' => $result['level'] ?? 'INFO',
                'available_levels' => $result['available_levels'] ?? [],
            ]);
        }
    } catch(Exception $e) {
        mnbt_admin_node_exit(false, '连接节点失败：' . $e->getMessage());
    }
}

if($egn=='nodelogstats') {
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    $logFile = trim((string)($_POST['log_file'] ?? ''));
    if ($nodeId === '') mnbt_admin_node_exit(false, '缺少节点ID');
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [(int)($node['bt_id'] ?? 0)]);
    if (!$bt) mnbt_admin_node_exit(false, '节点未绑定宝塔面板');
    try {
        $btApi = new bt_api($bt['panel'], $bt['btkey']);
        $result = $btApi->pluginRequest('mnbt_connector', 'get_worker_log_stats', ['file' => $logFile]);
        if (!is_array($result) || !($result['status'] ?? false)) {
            mnbt_admin_node_exit(false, '获取日志统计失败：' . ($result['msg'] ?? '未知错误'));
        }
        mnbt_admin_node_exit(true, '获取成功', ['data' => $result['data'] ?? []]);
    } catch(Exception $e) {
        mnbt_admin_node_exit(false, '连接节点失败：' . $e->getMessage());
    }
}

if($egn=='reset_sitestats') {
    $nodeId = trim((string)($_POST['node_id'] ?? ''));
    $site = trim((string)($_POST['site'] ?? ''));
    if ($nodeId === '') mnbt_admin_node_exit(false, '缺少节点ID');
    if ($site === '') mnbt_admin_node_exit(false, '缺少站点名称');
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node) mnbt_admin_node_exit(false, '节点不存在');
    $bt = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `id`=? LIMIT 1", [(int)($node['bt_id'] ?? 0)]);
    if (!$bt) mnbt_admin_node_exit(false, '节点未绑定宝塔面板');
    try {
        $btApi = new bt_api($bt['panel'], $bt['btkey']);
        $result = $btApi->pluginRequest('mnbt_connector', 'reset_site_stats', ['site' => $site]);
        if (!is_array($result) || !($result['status'] ?? false)) {
            mnbt_admin_node_exit(false, '重置失败：' . ($result['msg'] ?? '未知错误'));
        }
        if (function_exists('logjl')) logjl($user, '节点统计', '重置节点 '.$nodeId.' 站点 '.$site.' 统计数据', '操作成功', $DB);
        mnbt_admin_node_exit(true, $result['msg'] ?? '重置成功', ['deleted' => $result['deleted'] ?? []]);
    } catch(Exception $e) {
        mnbt_admin_node_exit(false, '连接节点失败：' . $e->getMessage());
    }
}

return;
