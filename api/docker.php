<?php
/**
 * Docker 对外开通 API
 * 入口：POST api/docker.php?gn=kt
 *
 * 鉴权（与 api/api.php 一致）：
 *   mn_key  = 系统后台 API 密钥（$conf['api']）
 *   mn_bh   = Docker 节点编号（MN_docker_node.id）
 *   mn_keye = md5(节点ktmy . 节点qmk)
 *   mn_vs   = 插件版本号（>=15）
 *   username= 待开通 Docker 账号
 *
 * gn=kt 参数：username / password / dqtime(到期,0=永久) / plan_id(可选) / email(可选)
 *
 * 注意：本接口仅开通 Docker 账户，容器由用户登录控制台后自行在应用商店创建（单容器模型）。
 */
@header('Content-Type: application/json; charset=UTF-8');
include("../MPHX/common.php");
include_once SYSTEM_ROOT . 'bt_docker.php';
include_once SYSTEM_ROOT . 'docker.member.php';

function api_json_exit($code, $msg, $extra = []) {
	$result = array_merge([
		'success' => ((int)$code === 200),
		'code'    => $code,
		'msg'     => $msg,
	], $extra);
	exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}
function api_lifecycle_log($type, $content, $status = '记录') {
	global $DB, $user, $bh;
	mnbt_log($user ?: '外部API', $type, 'DockerAPI-' . $bh . ' ' . $content, $status, $DB);
}

if ($conf['apiqk'] === 'false') api_json_exit(100, '错误！API 已关闭！请在系统后台 API 设置处开启');
$gn  = $_GET['gn'] ?? '';
$bh  = $_POST['mn_bh'] ?? '';
$key = $_POST['mn_key'] ?? '';
$keye = $_POST['mn_keye'] ?? '';
$mn_vser = $_POST['mn_vs'] ?? 0;
$user = $_POST['username'] ?? '';
if ($mn_vser < 15) api_json_exit(300, '插件版本不支持当前 MNBT 版本');
if (empty($gn) || empty($bh) || empty($key) || empty($keye) || empty($user)) api_json_exit(100, '错误！表单填写不完整！');
if ($key != $conf['api']) {
	mnbt_log('外部API', 'DockerAPI鉴权', 'DockerAPI-' . $bh . ' ' . $user . ' 系统密钥错误', '鉴权失败', $DB);
	api_json_exit(100, '错误！系统 API 密钥不匹配');
}
$cert = $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? limit 1", [(int)$bh]);
if (!$cert || $cert['qk'] !== 'true') api_json_exit(100, '错误！该 Docker 节点不存在或已被关闭');
if ($keye != md5($cert['ktmy'] . $cert['qmk'])) {
	mnbt_log('外部API', 'DockerAPI鉴权', 'DockerAPI-' . $bh . ' ' . $user . ' 调用密钥错误', '鉴权失败', $DB);
	api_json_exit(100, '错误！节点调用密钥不匹配');
}

// —— 连接验证 ——
if ($gn === 'cfif') {
	api_json_exit(200, '连接验证成功！');
}

// —— 开通 Docker 账户 ——
if ($gn === 'kt') {
	$pass   = (string)($_POST['password'] ?? '');
	$datae  = (($_POST['dqtime'] ?? '0') === '0') ? '0000-00-00' : daddslashes($_POST['dqtime']);
	$plan_id = (int)($_POST['plan_id'] ?? 0);
	$email  = daddslashes($_POST['email'] ?? '');

	if (mb_strlen($user) < 4 || mb_strlen($pass) < 6) {
		api_lifecycle_log('API开通Docker', '开通 ' . $user . ' 失败：账号(≥4)或密码(≥6)过短', '开通失败');
		api_json_exit(100, '错误！账号不少于4位，密码不少于6位');
	}
	if ($DB->get_row_prepare("SELECT id FROM MN_docker_user WHERE username=? limit 1", [$user])) {
		api_lifecycle_log('API开通Docker', '开通 ' . $user . ' 失败：账号已存在', '开通失败');
		api_json_exit(100, '错误！该 Docker 账号已存在');
	}
	// 校验套餐（若指定）
	if ($plan_id > 0) {
		$plan = $DB->get_row_prepare("SELECT * FROM MN_docker_plan WHERE id=? limit 1", [$plan_id]);
		if (!$plan || $plan['qk'] !== 'true') {
			api_lifecycle_log('API开通Docker', '开通 ' . $user . ' 失败：套餐不可用', '开通失败');
			api_json_exit(100, '错误！指定的套餐不存在或已下架');
		}
	} else {
		$plan_id = null;
	}

	$hash = docker_auth_password_hash($pass);
	$nodeId = (int)$bh;
	$ok = $DB->query_prepare(
		"INSERT INTO MN_docker_user (username,password_hash,email,ssbt,data,datae,qk,plan_id,container_status,created_at) VALUES (?,?,?,?,?,?,'active',?, 'none',?)",
		[$user, $hash, $email, $nodeId, $date, $datae, $plan_id, $date]
	);
	if ($ok) {
		api_lifecycle_log('API开通Docker', '开通 ' . $user . ' 成功（节点 ' . $bh . '）', '开通成功');
		if (function_exists('mnbt_do_action')) {
			mnbt_do_action('docker.user.created', ['username' => $user, 'ssbt' => $bh, 'plan_id' => $plan_id], ['source' => 'api']);
		}
		api_json_exit(200, 'Docker 账户开通成功！');
	}
	api_lifecycle_log('API开通Docker', '开通 ' . $user . ' 失败：数据库错误', '开通失败');
	api_json_exit(100, '错误！开通失败：' . $DB->error());
}

// ========== 构建 bt_docker 实例（供后续 gn 使用） ==========
$btipe  = ($cert['ptl'] == 'true' ? 'https' : 'http') . '://' . $cert['btip'] . ':' . $cert['btdk'];
$btkeye = $cert['btmy'];

// ========== gn=zt 暂停 Docker 账户 ==========
if ($gn === 'zt') {
	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) {
		api_lifecycle_log('API暂停Docker', '暂停 ' . $user . ' 失败：账号不存在', '暂停失败');
		api_json_exit(100, '错误！该 Docker 账号不存在');
	}
	if ($urow['qk'] === 'paused' || $urow['qk'] === 'expired') {
		api_json_exit(200, 'Docker 账户已处于暂停/到期状态，无需重复操作');
	}
	// 有容器则停止（失败仅记日志，不阻断暂停流程）
	if (!empty($urow['container_id']) && !empty($urow['service_name'])) {
		$bt = new bt_docker($btipe, $btkeye);
		$stop_r = $bt->container_stop($urow['container_id'], $urow['service_name']);
		if (!($stop_r['status'] ?? false)) {
			mnbt_log('外部API', 'DockerAPI暂停', 'DockerAPI-' . $bh . ' ' . $user . ' 停容器失败：' . ($stop_r['msg'] ?? '未知'), '警告', $DB);
		}
		$DB->query_prepare("UPDATE MN_docker_user SET container_status='stopped' WHERE id=?", [$urow['id']]);
	}
	$DB->query_prepare("UPDATE MN_docker_user SET qk='paused' WHERE id=?", [$urow['id']]);
	api_lifecycle_log('API暂停Docker', '暂停 ' . $user . ' 成功', '暂停成功');
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.paused', $urow, ['source' => 'api']);
	}
	api_json_exit(200, 'Docker 账户已暂停');
}

// ========== gn=jc 恢复 Docker 账户 ==========
if ($gn === 'jc') {
	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) {
		api_lifecycle_log('API恢复Docker', '恢复 ' . $user . ' 失败：账号不存在', '恢复失败');
		api_json_exit(100, '错误！该 Docker 账号不存在');
	}
	if ($urow['qk'] !== 'paused') {
		api_json_exit(100, '错误！该账户当前状态为 ' . $urow['qk'] . '，无法恢复（仅 paused 状态可恢复；expired 请走续费）');
	}
	$DB->query_prepare("UPDATE MN_docker_user SET qk='active' WHERE id=?", [$urow['id']]);
	api_lifecycle_log('API恢复Docker', '恢复 ' . $user . ' 成功', '恢复成功');
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.unpaused', $urow, ['source' => 'api']);
	}
	api_json_exit(200, 'Docker 账户已恢复');
}

// ========== gn=tj 删除 Docker 账户（立即删除容器 + 用户行） ==========
if ($gn === 'tj') {
	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) {
		api_lifecycle_log('API删除Docker', '删除 ' . $user . ' 失败：账号不存在', '删除失败');
		api_json_exit(100, '错误！该 Docker 账号不存在');
	}
	// 有容器则先删除容器（失败则返回错误，不删用户行）
	if (!empty($urow['container_id']) && !empty($urow['service_name'])) {
		$bt = new bt_docker($btipe, $btkeye);
		$del_r = $bt->container_del($urow['container_id'], $urow['service_name']);
		if (!($del_r['status'] ?? false)) {
			api_lifecycle_log('API删除Docker', '删除 ' . $user . ' 失败：删容器失败 ' . ($del_r['msg'] ?? '未知'), '删除失败');
			api_json_exit(100, '错误！删除容器失败：' . ($del_r['msg'] ?? '未知错误'));
		}
	}
	$DB->query_prepare("DELETE FROM MN_docker_user WHERE id=? LIMIT 1", [$urow['id']]);
	api_lifecycle_log('API删除Docker', '删除 ' . $user . ' 成功', '删除成功');
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.deleted', $urow, ['source' => 'api']);
	}
	api_json_exit(200, 'Docker 账户已删除');
}

// ========== gn=xf 续费 ==========
if ($gn === 'xf') {
	$setdate = daddslashes($_POST['setdate'] ?? '');
	if (empty($setdate)) api_json_exit(100, '错误！缺少续费日期参数 setdate');
	$new_datae = ($setdate === '0') ? '0000-00-00' : $setdate;

	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) {
		api_lifecycle_log('API续费Docker', '续费 ' . $user . ' 失败：账号不存在', '续费失败');
		api_json_exit(100, '错误！该 Docker 账号不存在');
	}
	$old_datae = $urow['datae'];
	$updates = "datae='" . $new_datae . "'";
	// 若原 expired 且新到期时间未过 → 恢复 active
	if ($urow['qk'] === 'expired') {
		if ($new_datae === '0000-00-00' || strtotime($date) - strtotime($new_datae) < 0) {
			$updates .= ", qk='active', expired_at=NULL, prune_due=NULL";
			// 尝试启动容器
			if (!empty($urow['container_id']) && !empty($urow['service_name'])) {
				$bt = new bt_docker($btipe, $btkeye);
				$start_r = $bt->container_start($urow['container_id'], $urow['service_name']);
				if ($start_r['status'] ?? false) {
					$DB->query_prepare("UPDATE MN_docker_user SET container_status='running' WHERE id=?", [$urow['id']]);
				}
			}
		}
	}
	$DB->query("UPDATE MN_docker_user SET {$updates} WHERE id=" . intval($urow['id']));
	api_lifecycle_log('API续费Docker', '续费 ' . $user . ' ' . $old_datae . '=>' . $new_datae, '续费成功');
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.renewed', array_merge($urow, ['datae' => $new_datae]), ['source' => 'api', 'old_date' => $old_datae, 'new_date' => $new_datae]);
	}
	api_json_exit(200, 'Docker 账户续费成功');
}

// ========== gn=bg 变更套餐 ==========
if ($gn === 'bg') {
	$new_plan_id = (int)($_POST['plan_id'] ?? 0);
	if ($new_plan_id <= 0) api_json_exit(100, '错误！缺少套餐 ID 参数 plan_id');

	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) {
		api_lifecycle_log('API变更套餐Docker', $user . ' 套餐变更失败：账号不存在', '变更失败');
		api_json_exit(100, '错误！该 Docker 账号不存在');
	}
	$plan = $DB->get_row_prepare("SELECT * FROM MN_docker_plan WHERE id=? LIMIT 1", [$new_plan_id]);
	if (!$plan || $plan['qk'] !== 'true') {
		api_lifecycle_log('API变更套餐Docker', $user . ' 套餐变更失败：套餐不存在或已下架', '变更失败');
		api_json_exit(100, '错误！指定的套餐不存在或已下架');
	}
	$old_plan_id = $urow['plan_id'];
	$DB->query_prepare("UPDATE MN_docker_user SET plan_id=? WHERE id=?", [$new_plan_id, $urow['id']]);
	api_lifecycle_log('API变更套餐Docker', $user . ' 套餐变更 ' . ($old_plan_id ?: '无') . '=>' . $new_plan_id, '变更成功');
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.package_changed', array_merge($urow, ['plan_id' => $new_plan_id]), ['source' => 'api', 'old_plan_id' => $old_plan_id]);
	}
	api_json_exit(200, '套餐变更成功');
}

// ========== gn=czmm 重置密码 ==========
if ($gn === 'czmm') {
	$new_pass = (string)($_POST['password'] ?? '');
	if (mb_strlen($new_pass) < 6) api_json_exit(100, '错误！密码不少于6位');

	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) {
		api_lifecycle_log('API重置密码Docker', $user . ' 重置密码失败：账号不存在', '操作失败');
		api_json_exit(100, '错误！该 Docker 账号不存在');
	}
	$new_hash = docker_auth_password_hash($new_pass);
	$DB->query_prepare("UPDATE MN_docker_user SET password_hash=? WHERE id=?", [$new_hash, $urow['id']]);
	api_lifecycle_log('API重置密码Docker', $user . ' 密码已重置（旧 session 自动失效）', '操作成功');
	if (function_exists('mnbt_do_action')) {
		mnbt_do_action('docker.user.password_reset', $urow, ['source' => 'api']);
	}
	api_json_exit(200, '密码重置成功');
}

// ========== gn=ztcx 状态查询 ==========
if ($gn === 'ztcx') {
	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) api_json_exit(100, '错误！该 Docker 账号不存在');

	$userData = [
		'username'         => $urow['username'],
		'qk'               => $urow['qk'],
		'datae'            => $urow['datae'],
		'plan_id'          => $urow['plan_id'] ? (int)$urow['plan_id'] : null,
		'container_status' => $urow['container_status'] ?: 'none',
		'service_name'     => $urow['service_name'],
		'app_name'         => $urow['app_name'],
		'disk_usage'       => (int)$urow['disk_usage'],
		'disk_usage_at'    => $urow['disk_usage_at'],
	];
	$nodeData = ['btip' => $cert['btip'], 'ptl' => $cert['ptl']];
	$containerData = null;

	// 有容器则查 installed_apps 获取详情
	if (!empty($urow['service_name'])) {
		$bt = new bt_docker($btipe, $btkeye);
		$apps = $bt->installed_apps();
		if (is_array($apps)) {
			$svc = $urow['service_name'];
			foreach ($apps as $app) {
				$asvc = $app['service_name'] ?? '';
				if ($asvc === $svc || (strlen($svc) > 0 && strpos($asvc, $svc) === 0)) {
					$containerData = [
						'service_name' => $asvc,
						'appname'      => $app['appname'] ?? '',
						'apptitle'     => $app['apptitle'] ?? '',
						'appdesc'      => $app['appdesc'] ?? '',
						'status'       => $app['status'] ?? 'unknown',
						'port'         => $app['port'] ?? [],
						'container_id' => $app['container_id'] ?? '',
						'server_ip'    => $app['server_ip'] ?? $cert['btip'],
						'host_ip'      => $app['host_ip'] ?? '',
						'm_version'    => $app['m_version'] ?? '',
						's_version'    => $app['s_version'] ?? '',
						'version'      => $app['version'] ?? '',
						'appinfo'      => $app['appinfo'] ?? [],
					];
					// 同步容器状态到数据库
					$cs = $app['status'] ?? '';
					if (in_array($cs, ['running', 'stopped', 'creating']) && $urow['container_status'] !== $cs) {
						$DB->query_prepare("UPDATE MN_docker_user SET container_status=?, container_id=? WHERE id=?", [$cs, $containerData['container_id'], $urow['id']]);
					}
					break;
				}
			}
		}
	}

	api_json_exit(200, 'ok', [
		'data' => [
			'user'      => $userData,
			'container' => $containerData,
			'node'      => $nodeData,
		],
	]);
}

// ========== gn=sy 用量查询 ==========
if ($gn === 'sy') {
	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) api_json_exit(100, '错误！该 Docker 账号不存在');

	$disk_max = 0;
	if ($urow['plan_id']) {
		$plan = $DB->get_row_prepare("SELECT disk_max FROM MN_docker_plan WHERE id=? LIMIT 1", [(int)$urow['plan_id']]);
		$disk_max = $plan ? (int)$plan['disk_max'] : 0;
	}
	$disk_usage = (int)$urow['disk_usage'];

	// 容器运行中则实时刷新磁盘用量
	if ($urow['container_status'] === 'running' && !empty($urow['service_name'])) {
		$bt = new bt_docker($btipe, $btkeye);
		$apps = $bt->installed_apps();
		if (is_array($apps)) {
			$svc = $urow['service_name'];
			foreach ($apps as $app) {
				$asvc = $app['service_name'] ?? '';
				if (($asvc === $svc || (strlen($svc) > 0 && strpos($asvc, $svc) === 0)) && !empty($app['path'])) {
					$sz = $bt->get_path_size($app['path']);
					if (isset($sz['size'])) {
						$disk_usage = (int)$sz['size'];
						$DB->query_prepare("UPDATE MN_docker_user SET disk_usage=?, disk_usage_at=? WHERE id=?", [$disk_usage, $date, $urow['id']]);
					}
					break;
				}
			}
		}
	}

	$quota_reached = ($disk_max > 0 && $disk_usage >= $disk_max * 1048576);

	api_json_exit(200, 'ok', [
		'data' => [
			'disk_usage'    => $disk_usage,
			'disk_max'      => $disk_max * 1048576, // MB → bytes
			'disk_max_mb'   => $disk_max,
			'unit'          => 'bytes',
			'quota_reached' => $quota_reached,
		],
	]);
}

// ========== gn=start/stop/restart 容器启停 ==========
if ($gn === 'start' || $gn === 'stop' || $gn === 'restart') {
	$urow = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE username=? LIMIT 1", [$user]);
	if (!$urow) api_json_exit(100, '错误！该 Docker 账号不存在');
	if ($urow['qk'] !== 'active') api_json_exit(100, '错误！账户状态为 ' . $urow['qk'] . '，无法操作容器');
	if (empty($urow['container_id']) || empty($urow['service_name'])) api_json_exit(100, '错误！该账户尚未创建容器');

	$bt = new bt_docker($btipe, $btkeye);
	$labels = ['start' => '启动', 'stop' => '停止', 'restart' => '重启'];
	$statusMap = ['start' => 'running', 'stop' => 'stopped', 'restart' => 'running'];

	if ($gn === 'start') {
		$r = $bt->container_start($urow['container_id'], $urow['service_name']);
	} elseif ($gn === 'stop') {
		$r = $bt->container_stop($urow['container_id'], $urow['service_name']);
	} else {
		$r = $bt->container_restart($urow['container_id'], $urow['service_name']);
	}

	if ($r['status'] ?? false) {
		$DB->query_prepare("UPDATE MN_docker_user SET container_status=? WHERE id=?", [$statusMap[$gn], $urow['id']]);
		api_lifecycle_log('DockerAPI容器操作', $labels[$gn] . '容器 ' . $user, '操作成功');
		api_json_exit(200, '容器已' . $labels[$gn]);
	}
	api_lifecycle_log('DockerAPI容器操作', $labels[$gn] . '容器 ' . $user . ' 失败：' . ($r['msg'] ?? '未知'), '操作失败');
	api_json_exit(100, '容器' . $labels[$gn] . '失败：' . ($r['msg'] ?? '未知错误'));
}

api_json_exit(100, '未知指令');
