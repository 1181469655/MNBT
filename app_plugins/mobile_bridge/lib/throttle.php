<?php
/**
 * mobile_bridge - 限流（复用核心 runtime/temp/throttle 文件桶）
 *
 * 所有 1008 响应必须带 retry_after（交接 §4-13：APP 用它做倒计时）。
 */
if (!defined('IN_CRONLITE')) exit;

if (!function_exists('mnbt_app_client_ip')) {
	function mnbt_app_client_ip()
	{
		return (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
	}
}

if (!function_exists('mnbt_app_throttle_check')) {
	/**
	 * 超限则直接回 1008+retry_after 终止请求；未超限返回窗口起点供计算。
	 * 用法：check 后若本次是失败尝试，调 mnbt_app_throttle_hit。
	 */
	function mnbt_app_throttle_check($scope, $identity, $max, $window)
	{
		if (!function_exists('mnbt_throttle_exceeded') || !mnbt_throttle_exceeded($scope, $identity, $max, $window)) {
			return;
		}
		mnbt_app_err_1008('操作过于频繁，请稍后再试', mnbt_app_throttle_retry_after($scope, $identity, $window));
	}
}

if (!function_exists('mnbt_app_throttle_hit')) {
	function mnbt_app_throttle_hit($scope, $identity, $window)
	{
		if (function_exists('mnbt_throttle_hit')) mnbt_throttle_hit($scope, $identity, $window);
	}
}

if (!function_exists('mnbt_app_throttle_clear')) {
	function mnbt_app_throttle_clear($scope, $identity)
	{
		if (function_exists('mnbt_throttle_clear')) mnbt_throttle_clear($scope, $identity);
	}
}

if (!function_exists('mnbt_app_throttle_retry_after')) {
	/** 窗口自首次计数起算（filemtime），剩余秒数即 retry_after */
	function mnbt_app_throttle_retry_after($scope, $identity, $window)
	{
		$file = dirname(dirname(__DIR__, 2)) . '/runtime/temp/throttle/' . hash('sha256', $scope . '|' . $identity) . '.json';
		$mtime = is_file($file) ? (int)filemtime($file) : 0;
		$left = $mtime > 0 ? ($mtime + $window) - time() : $window;
		return max(1, $left);
	}
}
