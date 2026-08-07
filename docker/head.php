<?php
/**
 * Docker 控制器公共引导
 * 引入 MNBT 核心环境 + Docker API 封装 + Docker 独立认证
 */
if (!defined('IN_CRONLITE')) {
	define('IN_CRONLITE', true);
}
include dirname(__DIR__) . '/MPHX/common.php';
include_once SYSTEM_ROOT . 'bt_docker.php';
include_once SYSTEM_ROOT . 'bt_proxy.php';
include_once SYSTEM_ROOT . 'docker.member.php';

/**
 * 根据 Docker 用户所属节点构造 bt_docker 实例
 * @param array $dockerUser MN_docker_user 行
 * @return array [bt_docker|null, MN_docker_node行|null]
 */
function docker_user_node($dockerUser)
{
	global $DB;
	$node = $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? limit 1", [(int)$dockerUser['ssbt']]);
	if (!$node || $node['qk'] !== 'true') {
		return [null, null];
	}
	$url = ($node['ptl'] === 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
	return [new bt_docker($url, $node['btmy']), $node];
}

/**
 * 根据 Docker 用户所属节点构造 bt_proxy 实例
 */
function docker_user_proxy($dockerUser)
{
	global $DB;
	$node = $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? limit 1", [(int)$dockerUser['ssbt']]);
	if (!$node || $node['qk'] !== 'true') {
		return null;
	}
	$url = ($node['ptl'] === 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
	return new bt_proxy($url, $node['btmy']);
}

/**
 * 获取 Docker 用户套餐
 */
function docker_user_plan($dockerUser)
{
	global $DB;
	if (empty($dockerUser['plan_id'])) {
		return null;
	}
	return $DB->get_row_prepare("SELECT * FROM MN_docker_plan WHERE id=? limit 1", [$dockerUser['plan_id']]);
}

/**
 * 输出 JSON 并退出
 */
function docker_json($code, $msg = '', $extra = [])
{
	@header('Content-Type: application/json; charset=UTF-8');
	$result = array_merge([
		'success' => ((int)$code === 200 || $code === true),
		'code'    => $code,
		'msg'     => $msg,
	], $extra);
	exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}

/**
 * 在用户容器列表中找到属于自己的那一个容器（按 service_name / container_id 匹配）
 * @param array $dockerUser
 * @param array $containers bt_docker::container_list() 返回的容器数组
 * @return array|null
 */
function docker_find_my_container($dockerUser, $containers)
{
	if (!is_array($containers)) {
		return null;
	}
	// 宝塔 get_list 返回 {container_list: [...], online_cpus, mem_total, gpu}
	// 兼容 data / 直接数组 两种历史结构
	$list = $containers['container_list'] ?? $containers['data'] ?? $containers;
	if (!is_array($list)) {
		return null;
	}
	$sn = (string)($dockerUser['service_name'] ?? '');
	$cid_stored = (string)($dockerUser['container_id'] ?? '');
	foreach ($list as $c) {
		$name = ltrim((string)($c['name'] ?? ($c['Names'][0] ?? '')), '/');
		// 宝塔字段为 container_id，兼容 id/Id
		$cid = (string)($c['container_id'] ?? $c['id'] ?? $c['Id'] ?? '');
		// 1) service_name 精确匹配
		if ($sn !== '' && $name === $sn) {
			return $c;
		}
		// 2) container_id 精确匹配
		if ($cid_stored !== '' && $cid !== '' && $cid === $cid_stored) {
			return $c;
		}
		// 3) compose 命名匹配：<service_name>-<app>-N 或 <service_name>_<app>_N
		//    宝塔 create_app 创建的容器 name 形如 mnbt_xxx-frps-1
		if ($sn !== '' && $name !== '' && strpos($name, $sn) === 0) {
			$next = strlen($name) > strlen($sn) ? $name[strlen($sn)] : '';
			if ($next === '' || $next === '-' || $next === '_') {
				return $c;
			}
		}
	}
	return null;
}

/**
 * 在已安装应用列表中找到属于自己的那一个应用（按 service_name 匹配）
 * 宝塔 get_installed_apps 返回的应用信息比 get_list 更丰富（含端口/IP/参数）
 * @param array $dockerUser
 * @param array $apps bt_docker::installed_apps() 返回的应用数组
 * @return array|null
 */
function docker_find_my_installed_app($dockerUser, $apps)
{
	if (!is_array($apps)) {
		return null;
	}
	$list = $apps['data'] ?? $apps;
	if (!is_array($list)) {
		return null;
	}
	$sn = (string)($dockerUser['service_name'] ?? '');
	$cid_stored = (string)($dockerUser['container_id'] ?? '');
	foreach ($list as $a) {
		// 1) service_name 精确匹配（宝塔 get_installed_apps[].service_name = create_app 时传入的值）
		if ($sn !== '' && (string)($a['service_name'] ?? '') === $sn) {
			return $a;
		}
		// 2) container_id 精确匹配
		$cid = (string)($a['container_id'] ?? '');
		if ($cid_stored !== '' && $cid !== '' && $cid === $cid_stored) {
			return $a;
		}
	}
	return null;
}
