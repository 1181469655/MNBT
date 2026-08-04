<?php
/**
 * Docker 控制台 AJAX 分发
 * 入口：POST docker/ajax.php?gn=<操作>
 * 认证：docker_token（login/logout 除外，login 需已生成 CSRF token）
 */
include __DIR__ . '/head.php';
@header('Content-Type: application/json; charset=UTF-8');
mnbt_csrf_validate_request();

$gn = $_GET['gn'] ?? '';

// —— 登录 / 登出（无需 docker 登录态）——
if ($gn === 'login') {
	$username = daddslashes($_POST['username'] ?? '');
	$password = (string)($_POST['password'] ?? '');
	if ($username === '' || $password === '') {
		docker_json(100, '账号或密码不能为空');
	}
	$row = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? limit 1", [$username]);
	if (!$row) {
		docker_json(100, '账号或密码错误');
	}
	if (!docker_auth_password_verify($password, $row['password_hash'])) {
		docker_json(100, '账号或密码错误');
	}
	if ($row['qk'] === 'paused') {
		docker_json(100, '该账户已被暂停，请联系管理员');
	}
	if ($row['qk'] === 'expired' || $row['qk'] === 'pruned') {
		docker_json(100, '该账户已到期，请联系管理员续费');
	}
	if ($row['datae'] !== '0000-00-00' && strtotime($date) - strtotime($row['datae']) > 0) {
		$DB->query_prepare("UPDATE MN_docker_user SET qk='expired', expired_at=? WHERE id=?", [$row['datae'], $row['id']]);
		docker_json(100, '该账户已到期，请联系管理员续费');
	}
	docker_auth_login($row['id'], $row['password_hash']);
	mnbt_log($username, 'Docker登录', 'Docker 控制台登录成功', '登录成功', $DB);
	docker_json(200, '登录成功');
}
if ($gn === 'logout') {
	mnbt_log(docker_auth_current()['username'] ?? '未知', 'Docker登录', 'Docker 控制台登出', '登出', $DB);
	docker_auth_logout();
	docker_json(200, '已登出');
}

// —— 以下操作均需 docker 登录态 ——
$me = docker_auth_require();
if (!$me) {
	docker_json(401, '请先登录');
}
list($bt, $nodeInfo) = docker_user_node($me);
if (!$bt) {
	docker_json(100, '所属节点不存在，请联系管理员');
}

// —— 我的容器（单容器隔离）——
// 宝塔 get_list 的 ports 字段恒为空数组，端口/IP/参数信息实际来自 get_installed_apps
if ($gn === 'my_container') {
	$res = $bt->installed_apps();
	$container = docker_find_my_installed_app($me, $res);
	// 同步容器状态
	if ($container) {
		$status = strtolower((string)($container['status'] ?? ''));
		$mapped = 'running';
		if (strpos($status, 'exit') !== false || strpos($status, 'stop') !== false) $mapped = 'stopped';
		elseif (strpos($status, 'creat') !== false) $mapped = 'creating';
		elseif (strpos($status, 'run') !== false) $mapped = 'running';
		$cid = (string)($container['container_id'] ?? '');
		$cid = substr($cid, 0, 64);
		if ($me['container_id'] !== $cid || $me['container_status'] !== $mapped) {
			$DB->query_prepare("UPDATE MN_docker_user SET container_id=?, container_status=? WHERE id=?", [$cid, $mapped, $me['id']]);
			// 同步到 $me，确保返回给前端的是最新状态（否则前端会一直看到 creating）
			$me['container_id'] = $cid;
			$me['container_status'] = $mapped;
		}
	} else if ($me['container_status'] === 'running' || $me['container_status'] === 'stopped') {
		// 应用列表中找不到，可能已被删除
		$DB->query_prepare("UPDATE MN_docker_user SET container_id=NULL, container_status='none', service_name=NULL WHERE id=?", [$me['id']]);
		$me['container_id'] = null;
		$me['container_status'] = 'none';
		$me['service_name'] = null;
		$container = null;
	}
	// 优先用 server_ip（节点外网 IP），否则回退到 host_ip
	$containerIp = $container['server_ip'] ?? ($container['host_ip'] ?? '');
	docker_json(200, 'ok', [
		'container' => $container,
		'me'        => array_merge($me, ['password_hash' => null]),
		// 节点 IP 优先取容器返回的 server_ip（最准确），否则回退到 MN_docker_node.btip
		'node'      => [
			'btip' => $containerIp ?: ($nodeInfo['btip'] ?? ''),
			'ptl'  => $nodeInfo['ptl'] ?? 'false',
		],
	]);
}

// —— 容器启停重启（仅操作自己的容器）——
if (in_array($gn, ['container_start', 'container_stop', 'container_restart'], true)) {
	if (empty($me['container_id'])) {
		docker_json(100, '您还没有容器，请先在应用商店创建');
	}
	$res = $bt->container_list();
	$container = docker_find_my_container($me, $res);
	if (!$container) {
		docker_json(100, '未找到您的容器，可能已被删除');
	}
	$cid = $container['id'] ?? ($container['Id'] ?? '');
	$cname = ltrim($container['name'] ?? ($container['Names'][0] ?? ''), '/');
	$map = ['container_start' => 'container_start', 'container_stop' => 'container_stop', 'container_restart' => 'container_restart'];
	$r = $bt->{$map[$gn]}($cid, $cname);
	docker_json(($r['status'] ?? $r['code'] ?? false) ? 200 : 100, $r['msg'] ?? ($r['message'] ?? '操作完成'), ['raw' => $r]);
}

// —— 镜像列表 ——
if ($gn === 'image_list') {
	$r = $bt->image_list();
	docker_json(200, 'ok', ['data' => $r]);
}

// —— 存储卷列表 ——
if ($gn === 'volume_list') {
	$r = $bt->volume_list();
	docker_json(200, 'ok', ['data' => $r]);
}

// —— Compose 模板 + 项目 ——
if ($gn === 'compose_list') {
	$tpl = $bt->template_list();
	$proj = $bt->project_list();
	docker_json(200, 'ok', ['templates' => $tpl, 'projects' => $proj]);
}

// —— 应用商店列表 ——
if ($gn === 'app_list') {
	$r = $bt->app_list();
	docker_json(200, 'ok', ['data' => $r]);
}

// —— 单个应用详情（前端按 appname 过滤）——
if ($gn === 'app_detail') {
	$appname = daddslashes($_POST['appname'] ?? '');
	if ($appname === '') docker_json(100, '缺少应用名');
	$r = $bt->app_list();
	$list = $r['data'] ?? $r;
	$found = null;
	if (is_array($list)) {
		foreach ($list as $app) {
			if (($app['appname'] ?? '') === $appname) { $found = $app; break; }
		}
	}
	if (!$found) docker_json(100, '应用不存在');
	docker_json(200, 'ok', ['app' => $found]);
}

// —— 依赖查询 ——
if ($gn === 'app_dependence') {
	$appname = daddslashes($_POST['appname'] ?? '');
	$r = $bt->app_dependence($appname);
	docker_json(200, 'ok', ['data' => $r]);
}

// —— 创建应用（开通容器）单容器模型 + 配额校验 ——
if ($gn === 'app_create') {
	// 单容器：已有容器则拒绝
	if (!empty($me['container_id']) || !empty($me['service_name'])) {
		docker_json(100, '每个账户仅允许创建一个容器，如需重建请联系管理员先删除现有容器');
	}
	$app_name = daddslashes($_POST['app_name'] ?? '');
	$m_version = daddslashes($_POST['m_version'] ?? '');
	$s_version = daddslashes($_POST['s_version'] ?? '');
	// s_version 允许为空：部分应用（如 frps）只有 m_version=latest 无子版本
	// 宝塔后端会用 m_version 作为镜像 tag；若强制 s_version='0' 会拼出 latest.0 无效 tag
	if ($app_name === '' || $m_version === '') {
		docker_json(100, '应用参数不完整');
	}
	// 配额校验
	$plan = docker_user_plan($me);
	$cpu_max = $plan ? (float)$plan['cpu_max'] : 1;
	$mem_max = $plan ? (float)$plan['mem_max'] : 512;
	$cpus = (int)($_POST['cpus'] ?? 0);
	$memory_limit = (int)($_POST['memory_limit'] ?? 0);
	// 0 = 不限制；宝塔后端用 int() 转换，必须整数
	if ($cpus < 0) $cpus = 0;
	if ($memory_limit < 0) $memory_limit = 0;
	if ($cpus > $cpu_max) $cpus = (int)floor($cpu_max);
	if ($memory_limit > $mem_max) $memory_limit = (int)floor($mem_max);

	// 组装 service_name（唯一，前缀 mnbt_ + 用户名净化）
	$cleanUser = preg_replace('/[^a-z0-9]/', '', strtolower($me['username']));
	if ($cleanUser === '') $cleanUser = 'u' . $me['id'];
	$service_name = 'mnbt_' . substr($cleanUser, 0, 20);

	// 收集应用专属参数（透传 env/field 中除通用参数外的值）
	$builtins = ['app_name' => 1, 'service_name' => 1, 'm_version' => 1, 's_version' => 1, 'cpus' => 1, 'memory_limit' => 1, 'allow_access' => 1, 'gn' => 1, 'MNBT_CSRF_TOKEN' => 1];
	$params = [
		'app_name'      => $app_name,
		'service_name'  => $service_name,
		'm_version'     => $m_version,
		's_version'     => $s_version,
		'allow_access'  => (string)($_POST['allow_access'] ?? '1'),
		'cpus'          => (string)$cpus,
		'memory_limit'  => (string)$memory_limit,
	];
	foreach ($_POST as $k => $v) {
		if (!isset($builtins[$k]) && is_string($v)) {
			$params[daddslashes($k)] = daddslashes($v);
		}
	}

	$r = $bt->app_create($params);
	$ok = ($r['status'] ?? false) || ((($r['code'] ?? 1) === 0) && (($r['status'] ?? false) === true));
	if ($ok) {
		$spec = ['app_name' => $app_name, 'm_version' => $m_version, 's_version' => $s_version, 'cpus' => $cpus, 'memory_limit' => $memory_limit, 'params' => $params];
		$DB->query_prepare("UPDATE MN_docker_user SET service_name=?, app_name=?, container_spec=?, container_status='creating' WHERE id=?", [$service_name, $app_name, json_encode($spec, JSON_UNESCAPED_UNICODE), $me['id']]);
		mnbt_log($me['username'], 'Docker容器', '创建容器 ' . $app_name . ' (' . $service_name . ')', '创建中', $DB);
		docker_json(200, '应用创建请求已提交，请耐心等待 1-5 分钟初始化', ['service_name' => $service_name]);
	}
	docker_json(100, '创建失败：' . ($r['msg'] ?? ($r['message'] ?? '未知错误')), ['raw' => $r]);
}

docker_json(100, '未知操作');
