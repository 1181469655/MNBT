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
	// 容器列表可能是 ['data'=>[...]] 或直接数组
	$list = $containers['data'] ?? $containers;
	if (!is_array($list)) {
		return null;
	}
	foreach ($list as $c) {
		$name = $c['name'] ?? ($c['Names'][0] ?? '');
		$name = ltrim((string)$name, '/');
		// 按 service_name 或 container_id 匹配
		if (!empty($dockerUser['service_name']) && $name === $dockerUser['service_name']) {
			return $c;
		}
		if (!empty($dockerUser['container_id']) && ((string)($c['id'] ?? '') === (string)$dockerUser['container_id'] || (string)($c['Id'] ?? '') === (string)$dockerUser['container_id'])) {
			return $c;
		}
	}
	return null;
}
