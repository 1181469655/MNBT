<?php
/**
 * mobile_bridge - 上下文装配（PRD §6.3）
 *
 * 复刻 user/ajax.php:8-25 的引导，但数据源从 user_token cookie 换成 Bearer 设备令牌。
 * 到期/封禁/主机删除/节点缺失全部返回 JSON 错误码（1003/1004/1005/1006），绝不落 sysmsg() HTML。
 * APP 不发 cookie，member.php 的 sysmsg 分支（面板关闭/到期/封禁）因 isset($_COOKIE['user_token'])
 * 不成立而整体旁路，路由链路上没有 HTML 风险。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_build_context')) {
	/**
	 * 校验令牌并装配核心 handler 依赖的全局上下文。
	 * 成功后置全局：$yhc $yhid $zjid $ssbt $cert $btipe $btkeye $os_xt $l_ler_a
	 *                 $islogins $api $device $GLOBALS['file_api'] 前置依赖
	 * @return array 设备行
	 */
	function mnbt_app_build_context()
	{
		global $DB, $conf, $date;

		$token = mnbt_app_token_from_request();
		if ($token === '') mnbt_app_err(1001, '缺少访问令牌，请重新扫码配对');
		$device = mnbt_app_device_find_by_token($token);
		if (!is_array($device)) mnbt_app_err(1001, '令牌无效或已吊销，请重新扫码配对');

		$yhc = $DB->get_row_prepare("SELECT * FROM `MN_zj` WHERE `id`=? LIMIT 1", [(int)$device['zj_id']]);
		if (!is_array($yhc)) mnbt_app_err(1005, '主机已被删除，请移除该主机条目');

		// 到期：datae 非 0 且已过（判定照抄 member.php:43）
		if (strtotime($date) - strtotime($yhc['datae']) > 0 && $yhc['datae'] !== '0000-00-00') {
			mnbt_app_err(1003, '主机已到期，相关功能已暂停，请联系站长续费');
		}
		if ($yhc['qk'] === 'false') mnbt_app_err(1004, '主机已被封禁，请联系站长处理');

		$ssbt = $yhc['ssbt'];
		$cert = $DB->get_row_prepare("SELECT * FROM `MN_bt` WHERE `btdh`=? LIMIT 1", [$ssbt]);
		if (!is_array($cert)) mnbt_app_err(1006, '宝塔服务器配置错误，请联系管理员');

		$btipe = ($cert['ptl'] === 'true' ? 'https' : 'http') . '://' . $cert['btip'] . ':' . $cert['btdk'];
		$btkeye = $cert['btmy'];
		if ($cert['btos'] === '1' || (int)$cert['btos'] === 1) {
			$os_xt = $conf['hxi'] . '/';
			$l_ler_a = '/etc/hosts';
		} else {
			$os_xt = $conf['hxo'] . '/';
			$l_ler_a = 'C:\\Windows\\System32\\drivers\\etc\\hosts';
		}

		require_once SYSTEM_ROOT . 'bt_api.php';
		$api = new bt_api($btipe, $btkeye);

		$GLOBALS['yhc'] = $yhc;
		$GLOBALS['yhid'] = $yhc['id'];
		$GLOBALS['zjid'] = $yhc['btid'];
		$GLOBALS['ssbt'] = $ssbt;
		$GLOBALS['cert'] = $cert;
		$GLOBALS['btipe'] = $btipe;
		$GLOBALS['btkeye'] = $btkeye;
		$GLOBALS['os_xt'] = $os_xt;
		$GLOBALS['l_ler_a'] = $l_ler_a;
		$GLOBALS['islogins'] = 1;
		$GLOBALS['api'] = $api;
		$GLOBALS['device'] = $device;

		mnbt_app_touch_device($device);
		return $device;
	}
}

if (!function_exists('mnbt_app_log')) {
	/**
	 * 审计日志：沿用核心 logjl()/MN_log，操作者记「{用户名}(APP)」。
	 * 敏感参数（密码/配对码/令牌）绝不进入内容串——调用方只传路径/库名等非敏感标识。
	 */
	function mnbt_app_log($type, $content, $result)
	{
		global $DB, $yhc;
		if (!is_array($yhc)) return;
		logjl($yhc['user'] . '(APP)', $type, $content, $result, $DB);
	}
}
