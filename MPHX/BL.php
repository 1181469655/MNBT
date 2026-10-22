<?php
$WEBQB='1890';
$SQLQB='1310';

// 版本号唯一数据源是上面的 $WEBQB（如 1850 => V1.85）。
// 全项目对外显示版本一律走下面两个函数，发版只改 $WEBQB 这一处。
// BL.php 会被多处 include（非 once），所以用 function_exists 兜住重复定义。
if (!function_exists('mnbt_version_num')) {
	// 纯数字串：1850 => "1.85"
	function mnbt_version_num()
	{
		return sprintf('%.2f', (int)($GLOBALS['WEBQB'] ?? 0) / 1000);
	}
}
if (!function_exists('mnbt_version')) {
	// 带前缀显示：1850 => "V1.85"
	function mnbt_version($prefix = 'V')
	{
		return $prefix . mnbt_version_num();
	}
}

// php2shell（CVE-2026-109618 / CVE-2026-109619，CVSS 9.2，构造 multipart POST 即可未授权 RCE）
// 的「分支 => 最低已修补版本」表。安装向导（install/install.api.php）与管理端
// （仪表盘启动注入 / admin/sy.php / AJAX system_info）都只读这一份，版本号不再各写一遍。
// 7.4 / 8.0 / 8.1 已 EOL、上游永不修复，故不入表；表外且低于 8.5 的分支即视为 EOL。
// 本函数只描述事实、不做取舍：status 供调用方决定"提示"还是"要操作者确认"，
// 任何调用点都不许用它来拒绝安装——运行环境是用户的，装不装由用户决定。
if (!function_exists('mnbt_php_security_status')) {
	/**
	 * @return array
	 *   status  ：ok（已修复）/ warning（分支有补丁但当前构建过旧）/ eol（分支停更，永不修复）
	 *   msg     ：安装向导用完整说明（含风险自负措辞）
	 *   text    ：面板运行期用短句（仪表盘 PHP 信息卡片）
	 *   min     ：该分支最低已修补版本；分支不在表内时为 ''
	 *   branch  ：形如 8.3 的当前分支
	 */
	function mnbt_php_security_status()
	{
		// 分支 => 该分支修复 php2shell 的最低补丁版本
		$patched_min = array(
			'8.2' => '8.2.35',
			'8.3' => '8.3.36',
			'8.4' => '8.4.27',
			'8.5' => '8.5.12',
		);
		// 建议版本清单由表推导，新增分支时不用再改第二处文案
		$require_list = implode(' / ', array_values($patched_min));
		$require_txt = $require_list . ' 及以上（推荐 8.4.27+）';
		$mitigate_txt = '若确实暂时无法升级 PHP，必须先按《安装部署》文档的安全说明，在 php.ini 与 PHP-FPM 池中设置 session.upload_progress.enabled = 0 作为临时缓解，升级完成后及时复核。';
		$advisory = 'php2shell（CVE-2026-109618 / CVE-2026-109619';
		$branch = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
		$min = isset($patched_min[$branch]) ? $patched_min[$branch] : '';
		$status = 'ok';
		$msg = '';
		$text = '';

		if ($min !== '') {
			if (version_compare(PHP_VERSION, $min, '<')) {
				// 分支有补丁，只是当前构建太旧：明确告知要升到哪个版本
				$status = 'warning';
				$msg = '当前 PHP 版本为 ' . PHP_VERSION . '，' . $branch . ' 分支需升级到 '
					. $min . ' 及以上才能修复 ' . $advisory . '，未授权远程代码执行）。'
					. '本面板建议运行环境使用' . $require_txt . '；未升级仍可继续安装，但风险由您自行承担。' . $mitigate_txt;
				$text = '当前 PHP ' . PHP_VERSION . ' 未修复 php2shell（CVE-2026-109618 / CVE-2026-109619，未授权远程代码执行），请升级到 '
					. $min . ' 及以上；临时缓解可设置 session.upload_progress.enabled = 0。';
			}
			// 同分支内更高的补丁版本（含后续未列入表的小版本）视为已修复
		} elseif (version_compare(PHP_VERSION, '8.5.0', '>=') === false) {
			// 7.x / 8.0 / 8.1 等：分支已停止上游维护，永远不会有官方补丁
			$status = 'eol';
			$msg = '当前 PHP 版本为 ' . PHP_VERSION . '，该分支已停止上游维护，官方不会为 ' . $advisory
				. '，CVSS 9.2，未授权远程代码执行）发布任何补丁，本次安装将长期处于可被利用的状态。'
				. '建议改用' . $require_txt . '；继续安装前请确认您已了解并接受该风险。' . $mitigate_txt;
			$text = '当前 PHP ' . PHP_VERSION . ' 所在分支已停止上游维护，官方不会为 php2shell（CVE-2026-109618 / CVE-2026-109619，CVSS 9.2）'
				. '发布任何补丁，本站长期处于风险中。临时缓解：在 php.ini 与 PHP-FPM 池设置 session.upload_progress.enabled = 0，'
				. '并尽快升级到 ' . $require_list . ' 及以上。';
		}

		return array('status' => $status, 'msg' => $msg, 'text' => $text, 'min' => $min, 'branch' => $branch);
	}
}
?>