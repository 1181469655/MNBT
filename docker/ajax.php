<?php
/**
 * Docker 控制台 AJAX 分发
 * 入口：POST docker/ajax.php?gn=<操作>
 * 认证：docker_token（login/logout 除外，login 需已生成 CSRF token）
 */
include __DIR__ . '/head.php';
@header('Content-Type: application/json; charset=UTF-8');
mnbt_csrf_validate_request();

// 兼容两种传参：默认主题把 gn 放在 URL 查询串（$_GET），SPA 放在 POST body（$_POST）
$gn = $_GET['gn'] ?? $_POST['gn'] ?? '';

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
		// 磁盘用量采集：从容器安装路径获取磁盘占用大小
		$containerPath = (string)($container['path'] ?? '');
		if ($containerPath !== '') {
			$sizeResult = $bt->get_path_size($containerPath);
			$diskSize = (int)($sizeResult['size'] ?? 0);
			if ($diskSize > 0) {
				$DB->query_prepare("UPDATE MN_docker_user SET disk_usage=?, disk_usage_at=? WHERE id=?", [$diskSize, $date, $me['id']]);
				$me['disk_usage'] = $diskSize;
				$me['disk_usage_at'] = $date;
			}
			// 磁盘超限自动停机：超过配额且容器正在运行
			$plan = docker_user_plan($me);
			$diskMax = $plan ? (int)$plan['disk_max'] : 0;
			if ($diskMax > 0 && $diskSize > $diskMax * 1048576 && $mapped === 'running') {
				$cid = (string)($container['container_id'] ?? '');
				$cname = ltrim((string)($container['name'] ?? ($container['Names'][0] ?? '')), '/');
				if ($cid !== '') {
					$bt->container_stop($cid, $cname);
					$DB->query_prepare("UPDATE MN_docker_user SET container_status='stopped' WHERE id=?", [$me['id']]);
					$me['container_status'] = 'stopped';
					$mapped = 'stopped';
					mnbt_log($me['username'], 'Docker容器', '磁盘超限自动停机：用量 ' . round($diskSize / 1048576, 1) . 'MB / 配额 ' . $diskMax . 'MB', '已停机', $DB);
				}
			}
		}
	} else if ($me['container_status'] === 'running' || $me['container_status'] === 'stopped') {
		// 应用列表中找不到，可能已被删除
		$DB->query_prepare("UPDATE MN_docker_user SET container_id=NULL, container_status='none', service_name=NULL, disk_usage=0, disk_usage_at=NULL WHERE id=?", [$me['id']]);
		$me['container_id'] = null;
		$me['container_status'] = 'none';
		$me['service_name'] = null;
		$me['disk_usage'] = 0;
		$me['disk_usage_at'] = null;
		$container = null;
	}
	// 优先用 server_ip（节点外网 IP），否则回退到 host_ip
	$containerIp = $container['server_ip'] ?? ($container['host_ip'] ?? '');
	docker_json(200, 'ok', [
		'container' => $container,
		'me'        => array_merge($me, ['password_hash' => null]),
		'plan'      => docker_user_plan($me),
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

// —— 删除容器（卸载应用）——
if ($gn === 'container_remove') {
	if (empty($me['service_name'])) {
		docker_json(100, '您还没有容器，请先在应用商店创建');
	}
	// 从 installed_apps 获取宝塔安装 ID（remove_app 的 id 参数，非 Docker container_id）
	$apps = $bt->installed_apps();
	$myApp = docker_find_my_installed_app($me, $apps);
	$appId = (string)($myApp['id'] ?? '');
	if ($appId === '') {
		docker_json(100, '未找到应用安装 ID，请刷新后重试', ['raw' => $myApp]);
	}
	$r = $bt->app_remove(['id' => $appId, 'delete_data' => '0']);
	$ok = ($r['status'] ?? false) || (($r['code'] ?? 1) === 0);
	if ($ok) {
		$DB->query_prepare("UPDATE MN_docker_user SET container_id=NULL, container_status='none', service_name=NULL, app_name=NULL, container_spec=NULL, disk_usage=0, disk_usage_at=NULL WHERE id=?", [$me['id']]);
		mnbt_log($me['username'], 'Docker容器', '删除容器 ' . $me['service_name'] . ' (id=' . $appId . ')', '删除成功', $DB);
		docker_json(200, '容器已删除');
	}
	docker_json(100, '删除失败：' . ($r['msg'] ?? ($r['message'] ?? '未知错误')), ['raw' => $r]);
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

	// —— 端口冲突检测：检查请求的端口是否已被同节点其他容器占用 ——
	$requestedPorts = [];
	foreach ($params as $k => $v) {
		// 识别端口参数：key 含 _port 或值看起来像端口号（1-65535 的纯数字字符串）
		if (is_string($v) && ctype_digit($v) && (int)$v >= 1 && (int)$v <= 65535) {
			$requestedPorts[(int)$v] = $k;
		}
	}
	if (!empty($requestedPorts)) {
		$installed = $bt->installed_apps();
		$existingPorts = [];
		$list = $installed['data'] ?? $installed;
		if (is_array($list)) {
			foreach ($list as $app) {
				$appPorts = $app['port'] ?? [];
				foreach ($appPorts as $p) {
					$existingPorts[(int)$p] = ($app['service_name'] ?? '') . '/' . ($app['apptitle'] ?? $app['appname'] ?? '');
				}
			}
		}
		$conflicts = [];
		foreach ($requestedPorts as $port => $key) {
			if (isset($existingPorts[$port])) {
				$conflicts[] = $port . '（参数 ' . $key . '，已被 ' . $existingPorts[$port] . ' 占用）';
			}
		}
		if (!empty($conflicts)) {
			mnbt_log($me['username'], 'Docker容器', '创建容器 ' . $app_name . ' 端口冲突：' . implode('; ', $conflicts), '创建失败', $DB);
			docker_json(100, '端口冲突：' . implode('；', $conflicts) . '。请返回应用商店，系统将自动分配新端口。');
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

// —— 反向代理管理 ——
// 获取容器端口列表（供反向代理页面选择）
if ($gn === 'container_ports') {
	$res = $bt->installed_apps();
	$container = docker_find_my_installed_app($me, $res);
	$ports = [];
	if ($container) {
		$rawPorts = $container['port'] ?? [];
		foreach ($rawPorts as $p) {
			$ports[] = (int)$p;
		}
	}
	sort($ports);
	docker_json(200, 'ok', ['ports' => $ports]);
}

if ($gn === 'proxy_list') {
	$px = docker_user_proxy($me);
	if (!$px) docker_json(100, '所属节点不可用');
	$r = $px->proxy_list(1, 200);
	$list = $r['data'] ?? $r;
	if (!is_array($list)) $list = [];
	docker_json(200, 'ok', ['data' => $list]);
}

if ($gn === 'proxy_create') {
	$px = docker_user_proxy($me);
	if (!$px) docker_json(100, '所属节点不可用');
	// 配额检查
	$plan = docker_user_plan($me);
	$proxyMax = $plan ? (int)$plan['proxy_max'] : 0;
	if ($proxyMax > 0) {
		$existing = $px->proxy_list(1, 200);
		$existingList = $existing['data'] ?? $existing;
		$count = is_array($existingList) ? count($existingList) : 0;
		if ($count >= $proxyMax) {
			docker_json(100, '反向代理数量已达上限（' . $proxyMax . '个），请先删除后再添加');
		}
	}
	$domains = daddslashes($_POST['domains'] ?? '');
	$port = (int)($_POST['port'] ?? 0);
	$proto = daddslashes($_POST['proto'] ?? 'http');
	$ip = daddslashes($_POST['ip'] ?? '127.0.0.1');
	$proxy_path = daddslashes($_POST['proxy_path'] ?? '/');
	$remark = daddslashes($_POST['remark'] ?? '');
	if ($domains === '' || $port <= 0) {
		docker_json(100, '域名和容器端口不能为空');
	}
	// 代理目标，IP 默认 127.0.0.1（容器与本机同机部署），协议由用户选择
	$proto = ($proto === 'https') ? 'https' : 'http';
	$proxy_pass = $proto . '://' . $ip . ':' . $port;
	$r = $px->proxy_create([
		'domains'    => $domains,
		'proxy_pass' => $proxy_pass,
		'proxy_path' => $proxy_path ?: '/',
		'remark'     => $remark,
	]);
	$ok = ($r['status'] ?? false) || (($r['code'] ?? 1) === 0);
	if ($ok) {
		mnbt_log($me['username'], 'Docker代理', '创建反向代理 ' . $domains . ' → ' . $proxy_pass, '创建成功', $DB);
		docker_json(200, '反向代理创建成功');
	}
	docker_json(100, '创建失败：' . ($r['msg'] ?? ($r['message'] ?? '未知错误')), ['raw' => $r]);
}

if ($gn === 'proxy_delete') {
	$px = docker_user_proxy($me);
	if (!$px) docker_json(100, '所属节点不可用');
	$id = (int)($_POST['id'] ?? 0);
	$site_name = daddslashes($_POST['site_name'] ?? '');
	if ($id <= 0 || $site_name === '') docker_json(100, '参数错误');
	$r = $px->proxy_delete($id, $site_name);
	$ok = ($r['status'] ?? false) || (($r['code'] ?? 1) === 0);
	if ($ok) {
		mnbt_log($me['username'], 'Docker代理', '删除反向代理 #' . $id . ' ' . $site_name, '删除成功', $DB);
		docker_json(200, '反向代理已删除');
	}
	docker_json(100, '删除失败：' . ($r['msg'] ?? ($r['message'] ?? '未知错误')), ['raw' => $r]);
}

docker_json(100, '未知操作');
