<?php
/**
 * Docker 管理 AJAX 模块（admin/ajax.php 分发）
 * 指令前缀：docker_*
 * 依赖：bt_docker（节点容器/配置查询）、docker.member（密码哈希）
 * 节点独立于 MN_bt，存于 MN_docker_node
 */
include_once SYSTEM_ROOT . 'bt_docker.php';
include_once SYSTEM_ROOT . 'docker.member.php';

/** 根据 MN_docker_node.id 构造 bt_docker 实例 */
function docker_admin_node_instance($nodeId)
{
	global $DB;
	$node = $DB->get_row_prepare("SELECT * FROM MN_docker_node WHERE id=? limit 1", [(int)$nodeId]);
	if (!$node) return [null, null];
	$url = ($node['ptl'] === 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
	return [new bt_docker($url, $node['btmy']), $node];
}

// ========================================================================
//  Docker 节点管理（独立于 MN_bt）
// ========================================================================

// ===== 节点列表 =====
if ($egn === 'docker_node_list') {
	$rows = $DB->get_all_prepare("SELECT * FROM MN_docker_node ORDER BY id DESC") ?: [];
	exit(json_encode(['code' => 0, 'msg' => '', 'count' => count($rows), 'data' => $rows], JSON_UNESCAPED_UNICODE));
}

// ===== 添加节点 =====
if ($egn === 'docker_node_add') {
	$name = daddslashes($_POST['name'] ?? '');
	$btip = daddslashes($_POST['btip'] ?? '');
	$btdk = daddslashes($_POST['btdk'] ?? '8888');
	$ptl = daddslashes($_POST['ptl'] ?? 'false');
	$btmy = daddslashes($_POST['btmy'] ?? '');
	$ktmy = daddslashes($_POST['ktmy'] ?? '');
	$qmk = daddslashes($_POST['qmk'] ?? '');
	$qk = daddslashes($_POST['qk'] ?? 'true');
	if ($name === '' || $btip === '' || $btmy === '') json_exit('节点名、面板地址、接口密钥不能为空');
	if ($DB->query_prepare("INSERT INTO MN_docker_node (name,btip,btdk,ptl,btmy,ktmy,qmk,qk,date) VALUES (?,?,?,?,?,?,?,?,?)", [$name, $btip, $btdk, $ptl, $btmy, $ktmy, $qmk, $qk, $date])) {
		mnbt_log($user, 'Docker节点', '添加节点 ' . $name, '添加成功', $DB);
		json_exit('添加成功');
	}
	json_exit('添加失败：' . $DB->error());
}

// ===== 编辑节点 =====
if ($egn === 'docker_node_edit') {
	$id = (int)($_POST['id'] ?? 0);
	$name = daddslashes($_POST['name'] ?? '');
	$btip = daddslashes($_POST['btip'] ?? '');
	$btdk = daddslashes($_POST['btdk'] ?? '8888');
	$ptl = daddslashes($_POST['ptl'] ?? 'false');
	$btmy = daddslashes($_POST['btmy'] ?? '');
	$ktmy = daddslashes($_POST['ktmy'] ?? '');
	$qmk = daddslashes($_POST['qmk'] ?? '');
	$qk = daddslashes($_POST['qk'] ?? 'true');
	if ($id <= 0) json_exit('参数错误');
	if ($name === '' || $btip === '' || $btmy === '') json_exit('节点名、面板地址、接口密钥不能为空');
	if ($DB->query_prepare("UPDATE MN_docker_node SET name=?,btip=?,btdk=?,ptl=?,btmy=?,ktmy=?,qmk=?,qk=? WHERE id=?", [$name, $btip, $btdk, $ptl, $btmy, $ktmy, $qmk, $qk, $id])) {
		mnbt_log($user, 'Docker节点', '编辑节点 ID' . $id, '编辑成功', $DB);
		json_exit('编辑成功');
	}
	json_exit('编辑失败：' . $DB->error());
}

// ===== 删除节点 =====
if ($egn === 'docker_node_del') {
	$id = (int)($_POST['id'] ?? 0);
	$inUse = $DB->get_row_prepare("SELECT id FROM MN_docker_user WHERE ssbt=? limit 1", [$id]);
	if ($inUse) json_exit('该节点下存在用户，无法删除');
	if ($DB->query_prepare("DELETE FROM MN_docker_node WHERE id=? limit 1", [$id])) {
		mnbt_log($user, 'Docker节点', '删除节点 ID' . $id, '删除成功', $DB);
		json_exit('删除成功');
	}
	json_exit('删除失败');
}

// ===== 节点 Docker 配置（检测安装状态）=====
if ($egn === 'docker_node_config') {
	$nodeId = (int)($_POST['node_id'] ?? 0);
	list($bt, ) = docker_admin_node_instance($nodeId);
	if (!$bt) json_exit('节点不存在');
	$r = $bt->get_config();
	exit(json_encode(['code' => 0, 'data' => $r], JSON_UNESCAPED_UNICODE));
}

// ===== 节点容器列表 =====
if ($egn === 'docker_node_containers') {
	$nodeId = (int)($_POST['node_id'] ?? 0);
	list($bt, ) = docker_admin_node_instance($nodeId);
	if (!$bt) json_exit('节点不存在');
	$r = $bt->container_list();
	exit(json_encode(['code' => 0, 'data' => $r], JSON_UNESCAPED_UNICODE));
}

// ========================================================================
//  Docker 用户管理
// ========================================================================

// ===== 用户列表（JOIN 节点名 + 套餐名）=====
if ($egn === 'docker_user_list') {
	$rows = $DB->get_all_prepare("SELECT u.*, p.name AS plan_name, n.name AS node_name FROM MN_docker_user u LEFT JOIN MN_docker_plan p ON u.plan_id=p.id LEFT JOIN MN_docker_node n ON u.ssbt=n.id ORDER BY u.id DESC");
	$list = [];
	if ($rows) foreach ($rows as $r) {
		$r['password_hash'] = null;
		$list[] = $r;
	}
	exit(json_encode(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list], JSON_UNESCAPED_UNICODE));
}

// ===== 添加用户 =====
if ($egn === 'docker_user_add') {
	$username = daddslashes($_POST['username'] ?? '');
	$password = (string)($_POST['password'] ?? '');
	$email = daddslashes($_POST['email'] ?? '');
	$ssbt = (int)($_POST['ssbt'] ?? 0);
	$plan_id = (int)($_POST['plan_id'] ?? 0);
	$datae = daddslashes($_POST['datae'] ?? '0000-00-00');
	if ($username === '' || $password === '') json_exit('账号和密码不能为空');
	if ($ssbt <= 0) json_exit('请选择 Docker 节点');
	if (!$DB->get_row_prepare("SELECT id FROM MN_docker_node WHERE id=? limit 1", [$ssbt])) json_exit('所选节点不存在');
	if ($DB->get_row_prepare("SELECT id FROM MN_docker_user WHERE username=? limit 1", [$username])) json_exit('账号已存在');
	$hash = docker_auth_password_hash($password);
	$plan_id = $plan_id > 0 ? $plan_id : null;
	if ($DB->query_prepare("INSERT INTO MN_docker_user (username,password_hash,email,ssbt,data,datae,qk,plan_id,container_status,created_at) VALUES (?,?,?,?,?,?, 'active',?, 'none',?)", [$username, $hash, $email, $ssbt, $date, $datae, $plan_id, $date])) {
		mnbt_log($user, 'Docker用户', '添加 Docker 用户 ' . $username, '添加成功', $DB);
		json_exit('添加成功');
	}
	json_exit('添加失败：' . $DB->error());
}

// ===== 编辑用户 =====
if ($egn === 'docker_user_edit') {
	$id = (int)($_POST['id'] ?? 0);
	$username = daddslashes($_POST['username'] ?? '');
	$email = daddslashes($_POST['email'] ?? '');
	$ssbt = (int)($_POST['ssbt'] ?? 0);
	$plan_id = (int)($_POST['plan_id'] ?? 0);
	$datae = daddslashes($_POST['datae'] ?? '0000-00-00');
	$qk = daddslashes($_POST['qk'] ?? 'active');
	if ($id <= 0) json_exit('参数错误');
	if ($ssbt <= 0) json_exit('请选择 Docker 节点');
	$plan_id = $plan_id > 0 ? $plan_id : null;
	if ($DB->query_prepare("UPDATE MN_docker_user SET username=?,email=?,ssbt=?,plan_id=?,datae=?,qk=? WHERE id=?", [$username, $email, $ssbt, $plan_id, $datae, $qk, $id])) {
		mnbt_log($user, 'Docker用户', '编辑 Docker 用户 ID' . $id, '编辑成功', $DB);
		json_exit('编辑成功');
	}
	json_exit('编辑失败：' . $DB->error());
}

// ===== 删除用户 =====
if ($egn === 'docker_user_del') {
	$id = (int)($_POST['id'] ?? 0);
	$row = $DB->get_row_prepare("SELECT * FROM MN_docker_user WHERE id=? limit 1", [$id]);
	if (!$row) json_exit('用户不存在');
	if ($DB->query_prepare("DELETE FROM MN_docker_user WHERE id=? limit 1", [$id])) {
		mnbt_log($user, 'Docker用户', '删除 Docker 用户 ' . ($row['username'] ?? $id), '删除成功', $DB);
		json_exit('删除成功');
	}
	json_exit('删除失败');
}

// ===== 重置密码 =====
if ($egn === 'docker_user_reset') {
	$id = (int)($_POST['id'] ?? 0);
	$password = (string)($_POST['password'] ?? '');
	if ($id <= 0 || $password === '') json_exit('参数错误');
	$hash = docker_auth_password_hash($password);
	if ($DB->query_prepare("UPDATE MN_docker_user SET password_hash=? WHERE id=?", [$hash, $id])) {
		mnbt_log($user, 'Docker用户', '重置 Docker 用户 ID' . $id . ' 密码', '重置成功', $DB);
		json_exit('重置成功');
	}
	json_exit('重置失败');
}

// ===== 暂停 / 恢复 =====
if ($egn === 'docker_user_pause' || $egn === 'docker_user_resume') {
	$id = (int)($_POST['id'] ?? 0);
	$qk = $egn === 'docker_user_pause' ? 'paused' : 'active';
	if ($DB->query_prepare("UPDATE MN_docker_user SET qk=? WHERE id=?", [$qk, $id])) {
		mnbt_log($user, 'Docker用户', ($qk === 'paused' ? '暂停' : '恢复') . ' Docker 用户 ID' . $id, '成功', $DB);
		json_exit('操作成功');
	}
	json_exit('操作失败');
}

// ========================================================================
//  套餐管理
// ========================================================================

if ($egn === 'docker_plan_list') {
	$rows = $DB->get_all_prepare("SELECT * FROM MN_docker_plan ORDER BY id DESC") ?: [];
	exit(json_encode(['code' => 0, 'msg' => '', 'count' => count($rows), 'data' => $rows], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'docker_plan_add') {
	$name = daddslashes($_POST['name'] ?? '');
	$jc = daddslashes($_POST['jc'] ?? '');
	$cpu_max = daddslashes($_POST['cpu_max'] ?? '1');
	$mem_max = daddslashes($_POST['mem_max'] ?? '512');
	$jg = daddslashes($_POST['jg'] ?? '0');
	$qk = daddslashes($_POST['qk'] ?? 'true');
	if ($name === '') json_exit('套餐名不能为空');
	if ($DB->query_prepare("INSERT INTO MN_docker_plan (name,jc,cpu_max,mem_max,jg,qk,date) VALUES (?,?,?,?,?,?,?)", [$name, $jc, $cpu_max, $mem_max, $jg, $qk, $date])) {
		mnbt_log($user, 'Docker套餐', '添加套餐 ' . $name, '添加成功', $DB);
		json_exit('添加成功');
	}
	json_exit('添加失败：' . $DB->error());
}

if ($egn === 'docker_plan_edit') {
	$id = (int)($_POST['id'] ?? 0);
	$name = daddslashes($_POST['name'] ?? '');
	$jc = daddslashes($_POST['jc'] ?? '');
	$cpu_max = daddslashes($_POST['cpu_max'] ?? '1');
	$mem_max = daddslashes($_POST['mem_max'] ?? '512');
	$jg = daddslashes($_POST['jg'] ?? '0');
	$qk = daddslashes($_POST['qk'] ?? 'true');
	if ($id <= 0) json_exit('参数错误');
	if ($DB->query_prepare("UPDATE MN_docker_plan SET name=?,jc=?,cpu_max=?,mem_max=?,jg=?,qk=? WHERE id=?", [$name, $jc, $cpu_max, $mem_max, $jg, $qk, $id])) {
		mnbt_log($user, 'Docker套餐', '编辑套餐 ID' . $id, '编辑成功', $DB);
		json_exit('编辑成功');
	}
	json_exit('编辑失败：' . $DB->error());
}

if ($egn === 'docker_plan_del') {
	$id = (int)($_POST['id'] ?? 0);
	$inUse = $DB->get_row_prepare("SELECT id FROM MN_docker_user WHERE plan_id=? limit 1", [$id]);
	if ($inUse) json_exit('该套餐下存在用户，无法删除');
	if ($DB->query_prepare("DELETE FROM MN_docker_plan WHERE id=? limit 1", [$id])) {
		mnbt_log($user, 'Docker套餐', '删除套餐 ID' . $id, '删除成功', $DB);
		json_exit('删除成功');
	}
	json_exit('删除失败：' . $DB->error());
}

// ========================================================================
//  选项数据（节点/套餐下拉，密钥字段不回传）
// ========================================================================
if ($egn === 'docker_options') {
	$nodes = $DB->get_all_prepare("SELECT id,name,btip,btdk,ptl,qk FROM MN_docker_node ORDER BY id") ?: [];
	$plans = $DB->get_all_prepare("SELECT id,name,cpu_max,mem_max,jg,qk FROM MN_docker_plan WHERE qk='true' ORDER BY id") ?: [];
	exit(json_encode(['code' => 0, 'nodes' => $nodes, 'plans' => $plans], JSON_UNESCAPED_UNICODE));
}

return; // 本模块未命中指令，交还主分发
