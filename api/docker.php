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

api_json_exit(100, '未知指令');
