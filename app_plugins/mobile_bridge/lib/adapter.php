<?php
/**
 * mobile_bridge - 核心 handler 适配器（PRD §6.4）
 *
 * 在插件内 include 核心 user/api/*.php 分支（只读复用，不修改核心文件），
 * ob_start 捕获输出并归一化为标准包络。三条硬规则：
 *  1. 任何情况下不得把 HTML/明文透传给 APP —— 非 JSON 捕获一律转 5000 或按纯文本载荷处理；
 *  2. 包络永远显式携带 success 布尔（APP 判定优先级 success > qk > 数字 code）；
 *  3. 核心 handler 的 exit() 由 shutdown 兜底收尾，防止引擎外层缓冲把错误页吐出去。
 */
if (!defined('IN_CRONLITE')) exit;

$GLOBALS['mnbt_app_replied'] = false;

if (!function_exists('mnbt_app_mark_replied')) {
	// 供 shutdown 判断包络是否已发出（mnbt_app_send 内调用）
	function mnbt_app_mark_replied()
	{
		$GLOBALS['mnbt_app_replied'] = true;
	}
}

if (!function_exists('mnbt_app_fail_from_text')) {
	/** 依据核心历史文案判失败（核心大量分支 json_exit(错误文案) 但 success 恒 true） */
	function mnbt_app_fail_from_text($text)
	{
		$text = (string)$text;
		if ($text === '') return false;
		foreach (['失败', '错误', '不能为空', '请输入', '参数错误', '不存在', '无权限', '用完了', '请登陆', '请登录', '请联系开发人员', '配置错误',
			// 核心把「每个用户最多只能添加5个监控任务」「禁止部署已经下架的程序」这类拦截也当成功返回（json_exit 恒 success=true），
			// 这几个限定词只出现在拦截文案里，成功文案（保存成功/获取成功/ok）不会命中
			'禁止', '不允许', '最多', '只能', '未达到要求', '未填写', '已超过'] as $kw) {
			if (mb_strpos($text, $kw) !== false) return true;
		}
		return false;
	}
}

if (!function_exists('mnbt_app_code_from_text')) {
	/** 「请登陆」类文案 = 会话失效，映射 1001（交接 §2.3）；其余业务失败 1009 */
	function mnbt_app_code_from_text($msg)
	{
		$msg = (string)$msg;
		return (strpos($msg, '请登陆') !== false || strpos($msg, '请登录') !== false) ? 1001 : 1009;
	}
}

if (!function_exists('mnbt_app_looks_like_html')) {
	function mnbt_app_looks_like_html($raw)
	{
		$head = ltrim(substr((string)$raw, 0, 512));
		if ($head === '') return false;
		return strpos((string)$raw, '<!DOCTYPE') !== false
			|| strpos((string)$raw, '<!doctype') !== false
			|| strpos((string)$raw, '<html') !== false
			|| (isset($head[0]) && $head[0] === '<');
	}
}

if (!function_exists('mnbt_app_normalize_raw')) {
	/**
	 * 把捕获到的核心输出归一化为 [success, code, msg, data]。
	 * 形态1 标准包络 / 形态2 qk+msg载荷 / 形态3 裸 code 文案 / 纯文本载荷 / HTML→5000
	 */
	function mnbt_app_normalize_raw($raw)
	{
		$raw = (string)$raw;
		$trimmed = trim($raw);
		if ($trimmed === '') return [false, 5000, 'handler_empty_output', null];

		$dec = json_decode($trimmed, true);
		if (!is_array($dec)) {
			if (mnbt_app_looks_like_html($trimmed)) {
				// sysmsg()/错误页/意外回显：绝不透传
				return [false, 5000, 'handler_html_output', null];
			}
			// 纯文本载荷（如 hqjt 返回的 nginx 配置原文）
			return [true, 0, 'ok', ['content' => $raw]];
		}

		// 形态 1：标准包络（bridge 内部自产也走这里）
		if (array_key_exists('success', $dec)) {
			$success = (bool)$dec['success'];
			$code = isset($dec['code']) && is_numeric($dec['code']) ? (int)$dec['code'] : ($success ? 0 : 1009);
			$msg = isset($dec['msg']) && is_scalar($dec['msg']) ? (string)$dec['msg'] : '';
			$data = isset($dec['data']) && is_array($dec['data']) ? $dec['data'] : [];
			foreach ($dec as $k => $v) {
				if (!in_array($k, ['success', 'code', 'msg', 'redirect', 'data', 'meta'], true) && !isset($data[$k])) {
					$data[$k] = $v;
				}
			}
			if ($success && mnbt_app_fail_from_text($msg)) $success = false;
			if (!$success && $code === 0) $code = mnbt_app_code_from_text($msg);
			return [$success, $code, $msg, $data];
		}

		// 形态 2：{qk,code:文案,msg:载荷}
		if (array_key_exists('qk', $dec)) {
			$success = (string)$dec['qk'] !== '4';
			$msg = isset($dec['code']) && is_scalar($dec['code']) ? (string)$dec['code'] : '';
			$data = [];
			foreach ($dec as $k => $v) {
				if ($k === 'qk' || $k === 'code') continue;
				$data[$k] = $v;
			}
			if (isset($dec['msg'])) {
				if (is_array($dec['msg'])) {
					$data = array_merge($data, $dec['msg']);
				} elseif (is_scalar($dec['msg']) && $msg === '') {
					$msg = (string)$dec['msg'];
				}
			}
			return [$success, $success ? 0 : mnbt_app_code_from_text($msg), $msg, $data];
		}

		// 形态 3：无成功位——核心只在错误分支裸 exit('{"code":"文案"}')，
		// 数据载荷型（listurl/getssl）没有 code 键；故 code 文案默认按失败处理，
		// 仅 'ok'/数字 0/含「成功」且不含失败词样视为成功。
		$msg = '';
		$code = 0;
		$data = [];
		$has_code = array_key_exists('code', $dec);
		foreach ($dec as $k => $v) {
			if ($k === 'code') {
				if (is_numeric($v)) { $code = (int)$v; } else { $msg = (string)$v; }
			} else {
				$data[$k] = $v;
			}
		}
		$positive = !$has_code || ($msg === 'ok') || ($code === 0 && $msg === '')
			|| ($msg !== '' && mb_strpos($msg, '成功') !== false && !mnbt_app_fail_from_text($msg));
		$fail = !$positive
			|| mnbt_app_fail_from_text($msg)
			|| (isset($dec['status']) && ($dec['status'] === false || $dec['status'] === 'false'));
		if ($fail) {
			return [false, mnbt_app_code_from_text($msg), $msg !== '' ? $msg : '操作失败', $data];
		}
		return [true, 0, $msg !== '' ? $msg : 'ok', $data];
	}
}

if (!function_exists('mnbt_app_finish_raw')) {
	/**
	 * 收束一段核心输出：归一化 -> （声明允许时）空输出视为空载荷 -> 执行挂起的 reshape -> 发包络。
	 * 核心分支大多 exit()，所以 reshape 也必须在 shutdown 里跑，两条路径共用这里。
	 */
	function mnbt_app_finish_raw($raw, $action)
	{
		$norm = mnbt_app_normalize_raw($raw);
		if ($norm[0] === false && $norm[1] === 5000) {
			if ($norm[2] === 'handler_empty_output' && !empty($GLOBALS['mnbt_app_allow_empty'])) {
				$norm = [true, 0, 'ok', ['content' => '']];
			} else {
				mnbt_app_err_5000($norm[2] . ' action=' . $action . ' raw_head=' . mb_substr(strip_tags($raw), 0, 200));
			}
		}
		$reshape = $GLOBALS['mnbt_app_pending_reshape'] ?? null;
		$GLOBALS['mnbt_app_pending_reshape'] = null;
		if (is_callable($reshape)) {
			$reshaped = $reshape($norm[0], $norm[1], $norm[2], is_array($norm[3]) ? $norm[3] : []);
			if (is_array($reshaped)) {
				mnbt_app_send($reshaped[0] ?? $norm[0], $reshaped[1] ?? $norm[1], $reshaped[2] ?? $norm[2], $reshaped[3] ?? []);
			}
		}
		mnbt_app_send($norm[0], $norm[1], $norm[2], is_array($norm[3]) ? $norm[3] : []);
	}
}

if (!function_exists('mnbt_app_core_include')) {
	/**
	 * 在函数作用域内包含核心 api 文件：核心代码按全局作用域书写（$yhc/$api/$DB 等），
	 * 必须先 extract($GLOBALS) 注入同名局部变量；EXTR_SKIP 保护本函数自身的参数变量。
	 * $params 传 null 表示“只取函数定义”：不动 $_POST，用不存在的 gn 让核心白名单直接 return。
	 * 返回 include 结果（核心分支 return 时为 true；文件缺失为 false）。
	 */
	function mnbt_app_core_include($coreFile, $gn, $params)
	{
		$GLOBALS['mnbt_app_resolved_gn'] = $gn;
		if ($params !== null) {
			$_POST = array_merge(is_array($params) ? $params : [], ['gn' => $gn]);
		}
		$egn = $gn;
		$GLOBALS['egn'] = $gn;
		extract($GLOBALS, EXTR_SKIP);
		$file = ROOT . 'user/api/' . $coreFile;
		if (!is_file($file)) return false;
		// 核心分支内部全是相对 include（../class.php、../MPHX/monitor.function.php、../fn.php），
		// 面板走 user/ajax.php 时 CWD 就是 ROOT/user；本插件由 index.php 的通用路由承载，
		// CWD 是 ROOT，不切目录这些相对路径会指到 ROOT 之外（include 失败 -> 致命错误 -> 5000）。
		$cwd = getcwd();
		$switched = @chdir(ROOT . 'user');
		try {
			include $file;
		} finally {
			if ($switched && $cwd !== false) @chdir($cwd);
		}
		return true;
	}
}

if (!function_exists('mnbt_app_proxy_run')) {
	/**
	 * 代理执行一个核心 gn：装配参数 -> include handler -> 捕获归一化。
	 * 核心分支大多以 exit() 结束请求，此时本函数不会走到收束，
	 * 由 mnbt_app_register_shutdown 的兜底共用 mnbt_app_finish_raw 完成同样的事。
	 * @param string $coreFile ROOT/user/api/ 下的文件名
	 * @param string $gn       核心 $egn
	 * @param array  $params   已按核心参数名转换好的 POST 字段
	 * @param callable|null $reshape function(bool $success,int $code,string $msg,array $data):array{0:bool,1:int,2:string,3:array}
	 * @param bool $allow_empty 核心分支合法输出空串时置 true（如 hqjt 的空白伪静态文件），否则空输出算 5000
	 */
	function mnbt_app_proxy_run($coreFile, $gn, $params, $reshape = null, $allow_empty = false)
	{
		$level = ob_get_level();
		ob_start();
		$GLOBALS['mnbt_app_pending_reshape'] = $reshape;
		$GLOBALS['mnbt_app_allow_empty'] = $allow_empty;
		if (mnbt_app_core_include($coreFile, $gn, $params) === false) {
			while (ob_get_level() > $level) ob_end_clean();
			$GLOBALS['mnbt_app_pending_reshape'] = null;
			mnbt_app_err_5000('missing_core_api:' . $coreFile);
		}
		$raw = '';
		while (ob_get_level() > $level) $raw .= (string)ob_get_clean();
		mnbt_app_finish_raw($raw, $GLOBALS['mnbt_app_action'] ?? $gn);
	}
}

if (!function_exists('mnbt_app_register_shutdown')) {
	/**
	 * 兜底：核心 handler 大多以 exit()/fatal 结束请求，reshape/send 都跑不到；
	 * 这里统一收束所有缓冲（含路由引擎的外层 ob），把捕获内容归一化成包络 JSON，
	 * 绝不把 HTML/裸文本吐给 APP（红线 3）。
	 */
	function mnbt_app_register_shutdown()
	{
		register_shutdown_function(function () {
			if (empty($GLOBALS['mnbt_app_dispatched']) || !empty($GLOBALS['mnbt_app_replied'])) return;
			// 个别 action（host.overview）会先跑一个以 exit() 收尾的核心分支，再自己补发正确包络。
			// 通用兜底注册在前、会先执行，若不认这个标志就会把核心那句文案当成最终响应发出去，
			// 把 action 自己那份配额数据挤掉，所以这里必须主动让路。
			if (!empty($GLOBALS['mnbt_app_overview_shutdown'])) return;
			$GLOBALS['mnbt_app_replied'] = true; // 防重入
			$err = error_get_last();
			$fatal = $err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
			$raw = '';
			while (ob_get_level() > 0) {
				$raw .= (string)ob_get_clean();
			}
			if (!headers_sent()) {
				@header('Content-Type: application/json; charset=UTF-8');
			}
			if ($fatal) {
				mnbt_app_err_5000('fatal:' . $err['message']);
			}
			mnbt_app_finish_raw($raw, $GLOBALS['mnbt_app_action'] ?? '');
		});
	}
}
