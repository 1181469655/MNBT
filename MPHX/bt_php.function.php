<?php
/**
 * BT节点 PHP 版本管理函数
 *
 * 系统底层函数，独立于任何插件，提供宝塔节点的 PHP 版本管理能力。
 * 在 admin/api/bt.php、admin/api/zj.php、api/api.php 中均直接使用。
 */

if (!defined('IN_CRONLITE')) {
    exit;
}

/**
 * 获取指定节点的 PHP 版本列表（从宝塔 API 实时拉取）。
 * @return array ['ok'=>bool, 'versions'=>array, 'latest'=>string, 'msg'=>string]
 */
function mnbt_node_php_list($btdh)
{
    global $DB;
    $node = $DB->get_row_prepare("SELECT * FROM MN_bt WHERE btdh=? LIMIT 1", [$btdh]);
    if (!$node) {
        return ['ok' => false, 'msg' => '节点不存在'];
    }
    $bt_api_file = defined('SYSTEM_ROOT') ? SYSTEM_ROOT . 'bt_api.php' : ROOT . 'MPHX/bt_api.php';
    if (!is_file($bt_api_file)) {
        return ['ok' => false, 'msg' => 'bt_api 类文件缺失'];
    }
    require_once $bt_api_file;

    $btipe = ($node['ptl'] == 'true' ? 'https' : 'http') . '://' . $node['btip'] . ':' . $node['btdk'];
    $api = new bt_api($btipe, $node['btmy']);
    $result = $api->btapi_listphp();
    if (!is_array($result)) {
        return ['ok' => false, 'msg' => '无法获取 PHP 版本列表', 'raw' => $result];
    }
    // 面板返回业务错误（连接失败/鉴权失败等），透出真实原因，避免误报为「未安装 PHP」
    if (isset($result['status']) && ($result['status'] === false || $result['status'] === 0 || $result['status'] === '0')) {
        return ['ok' => false, 'msg' => isset($result['msg']) ? $result['msg'] : '面板返回错误', 'raw' => $result];
    }
    // 移除纯静态(0)和自定义/其他(1)，与用户端处理保持一致
    unset($result[0], $result[1]);
    $versions = [];
    foreach ($result as $v) {
        if (($v['status'] ?? false) && ($v['version'] ?? '') !== '00' && ($v['version'] ?? '') !== '0') {
            $versions[] = ['version' => $v['version'], 'name' => $v['name'] ?? ('PHP-' . $v['version'])];
        }
    }
    if (empty($versions)) {
        return ['ok' => false, 'msg' => '该节点未安装任何 PHP 版本', 'raw' => $result];
    }
    usort($versions, function ($a, $b) {
        return strcmp($b['version'], $a['version']);
    });
    return ['ok' => true, 'versions' => $versions, 'latest' => $versions[0]['version']];
}

/**
 * 获取节点的当前默认 PHP 版本。
 * 优先读取 mrbts_php，为空时自动检测并保存最新版本。
 * @return string 版本号（空字符串表示无法获取）
 */
function mnbt_node_get_php($btdh)
{
    global $DB;
    $node = $DB->get_row_prepare("SELECT mrbts_php FROM MN_bt WHERE btdh=? LIMIT 1", [$btdh]);
    $php = $node['mrbts_php'] ?? '';
    if ($php !== '' && $php !== '00') {
        return $php;
    }
    $result = mnbt_node_auto_detect_php($btdh);
    return $result['ok'] ? $result['version'] : '';
}

/**
 * 设置节点的默认 PHP 版本。
 */
function mnbt_node_set_php($btdh, $version)
{
    global $DB;
    $version = trim((string)$version);
    if ($version === '') {
        return false;
    }
    return (bool)$DB->query_prepare(
        "UPDATE MN_bt SET mrbts_php=? WHERE btdh=? LIMIT 1",
        [$version, $btdh]
    );
}

/**
 * 自动检测节点最新的 PHP 版本并保存为默认版本。
 * @return array ['ok'=>bool, 'version'=>string, 'msg'=>string]
 */
function mnbt_node_auto_detect_php($btdh)
{
    $result = mnbt_node_php_list($btdh);
    if (!$result['ok']) {
        return $result;
    }
    $latest = $result['latest'];
    if (!mnbt_node_set_php($btdh, $latest)) {
        return ['ok' => false, 'msg' => '保存默认 PHP 版本失败'];
    }
    return ['ok' => true, 'version' => $latest, 'msg' => '已设置默认 PHP 版本为 ' . $latest];
}

/**
 * 获取所有节点列表（含 mrbts_php 信息）。
 */
function mnbt_node_list_all()
{
    global $DB;
    return $DB->get_all_prepare("SELECT * FROM MN_bt ORDER BY id ASC") ?: [];
}
