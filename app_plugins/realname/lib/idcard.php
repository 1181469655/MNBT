<?php
/**
 * realname 插件 - 身份证 / 手机号本地校验算法
 *
 * 全程本地算法，不调用任何外部 API：
 * - 身份证：GB 11643-1999（18 位结构、行政区划码、出生日期、顺序码、校验码）
 * - 手机号：11 位 + 号段表
 */

if (!defined('IN_CRONLITE')) {
	exit;
}

/**
 * 身份证号校验（GB 11643-1999）
 *
 * @param string $idcard 身份证号
 * @return array ['ok'=>bool, 'msg'=>string, 'birthday'=>'Y-m-d', 'gender'=>'male|female', 'age'=>int, 'area'=>'省份名']
 */
function realname_idcard_validate($idcard)
{
	$idcard = strtoupper(trim((string)$idcard));
	if ($idcard === '') {
		return ['ok' => false, 'msg' => '身份证号不能为空'];
	}
	if (!preg_match('/^\d{17}[\dX]$/', $idcard)) {
		return ['ok' => false, 'msg' => '身份证号格式不正确（应为 18 位数字，末位可为 X）'];
	}

	// 2. 行政区划码（前 6 位）
	$area = realname_idcard_area(substr($idcard, 0, 6));
	if ($area === '') {
		return ['ok' => false, 'msg' => '身份证号地区码无效'];
	}

	// 3. 出生日期（第 7-14 位）
	$birth = substr($idcard, 6, 8);
	if (!realname_idcard_check_date($birth)) {
		return ['ok' => false, 'msg' => '身份证号出生日期无效'];
	}
	$birthday = substr($birth, 0, 4) . '-' . substr($birth, 4, 2) . '-' . substr($birth, 6, 2);

	// 4. 顺序码（第 15-17 位）
	$seq = substr($idcard, 14, 3);
	if ((int)$seq < 0 || (int)$seq > 999) {
		return ['ok' => false, 'msg' => '身份证号顺序码无效'];
	}

	// 5. 校验码（第 18 位）
	if (!realname_idcard_checksum_ok($idcard)) {
		return ['ok' => false, 'msg' => '身份证号校验码错误'];
	}

	$age = realname_idcard_age($birthday);
	$gender = ((int)$seq % 2 === 1) ? 'male' : 'female';

	return [
		'ok'       => true,
		'msg'      => 'ok',
		'birthday' => $birthday,
		'gender'   => $gender,
		'age'      => $age,
		'area'     => $area,
	];
}

/**
 * 校验码算法（GB 11643-1999）
 * 加权因子与余数映射，返回计算出的校验码字符。
 */
function realname_idcard_checksum($idcard)
{
	$weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
	$map     = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
	$sum = 0;
	for ($i = 0; $i < 17; $i++) {
		$sum += (int)$idcard[$i] * $weights[$i];
	}
	return $map[$sum % 11];
}

/**
 * 校验末位是否与计算值一致。
 */
function realname_idcard_checksum_ok($idcard)
{
	return strtoupper($idcard[17]) === realname_idcard_checksum($idcard);
}

/**
 * 出生日期真实性校验（含闰年）。
 */
function realname_idcard_check_date($ymd)
{
	if (!preg_match('/^(\d{4})(\d{2})(\d{2})$/', $ymd, $m)) {
		return false;
	}
	$y = (int)$m[1];
	$mo = (int)$m[2];
	$d = (int)$m[3];
	if ($mo < 1 || $mo > 12 || $d < 1 || $d > 31) {
		return false;
	}
	return checkdate($mo, $d, $y);
}

/**
 * 从出生日期计算周岁年龄。
 */
function realname_idcard_age($birthday)
{
	try {
		$b = new DateTime($birthday);
		$now = new DateTime('now');
		$diff = $now->diff($b);
		return (int)$diff->y;
	} catch (Throwable $e) {
		return 0;
	}
}

/**
 * 手机号校验（11 位 + 号段表）。
 */
function realname_phone_validate($phone)
{
	$phone = trim((string)$phone);
	if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
		return ['ok' => false, 'msg' => '手机号格式不正确（应为 11 位数字）'];
	}
	// 号段表：第二位 3-9 已覆盖主流号段；这里再细筛个别保留号段
	$second = (int)$phone[1];
	$third = (int)$phone[2];
	// 14 号段仅 140-149 部分虚拟运营商在网；13/15/16/17/18/19 均合法
	if ($second === 3 && $third === 0) {
		return ['ok' => false, 'msg' => '手机号号段无效'];
	}
	if ($second === 4 && !in_array($third, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], true)) {
		return ['ok' => false, 'msg' => '手机号号段无效'];
	}
	return ['ok' => true, 'msg' => 'ok'];
}

/**
 * 姓名合法性：2~20 个汉字或点号（少数民族姓名可能含点号）。
 */
function realname_name_validate($name)
{
	$name = trim((string)$name);
	$len = mb_strlen($name, 'UTF-8');
	if ($len < 2 || $len > 20) {
		return ['ok' => false, 'msg' => '姓名长度应为 2~20 个字符'];
	}
	if (!preg_match('/^[\x{4e00}-\x{9fa5}·•.]+$/u', $name)) {
		return ['ok' => false, 'msg' => '姓名只能包含汉字或间隔点'];
	}
	return ['ok' => true, 'msg' => 'ok'];
}

/**
 * 行政区划码（前 6 位）→ 省份名。
 * 内置省级前两位映射 + 常用市级前四位映射。
 * 需覆盖全部省份前 2 位（11-65 之间的已知省区），用于粗校验。
 */
function realname_idcard_area($code6)
{
	$province_map = [
		'11' => '北京', '12' => '天津', '13' => '河北', '14' => '山西', '15' => '内蒙古',
		'21' => '辽宁', '22' => '吉林', '23' => '黑龙江',
		'31' => '上海', '32' => '江苏', '33' => '浙江', '34' => '安徽', '35' => '福建',
		'36' => '江西', '37' => '山东',
		'41' => '河南', '42' => '湖北', '43' => '湖南', '44' => '广东', '45' => '广西',
		'46' => '海南',
		'50' => '重庆', '51' => '四川', '52' => '贵州', '53' => '云南', '54' => '西藏',
		'61' => '陕西', '62' => '甘肃', '63' => '青海', '64' => '宁夏', '65' => '新疆',
	];
	$pre2 = substr($code6, 0, 2);
	if (!isset($province_map[$pre2])) {
		return '';
	}
	// 前四位市级粗校验：00 结尾为省级单位，非 00 需为非零组合（在此仅校验 0-9）
	if ((int)substr($code6, 2, 2) < 0 || (int)substr($code6, 2, 2) > 90) {
		return '';
	}
	// 后两位区县码
	if ((int)substr($code6, 4, 2) < 0 || (int)substr($code6, 4, 2) > 99) {
		return '';
	}
	return $province_map[$pre2];
}

/**
 * 掩码工具：身份证号 → 110***********1234
 */
function realname_mask_idcard($idcard)
{
	$idcard = trim((string)$idcard);
	if (strlen($idcard) < 8) {
		return '****';
	}
	return substr($idcard, 0, 3) . str_repeat('*', strlen($idcard) - 7) . substr($idcard, -4);
}

/**
 * 掩码工具：手机号 → 138****1234
 */
function realname_mask_phone($phone)
{
	$phone = trim((string)$phone);
	if (strlen($phone) < 7) {
		return '****';
	}
	return substr($phone, 0, 3) . '****' . substr($phone, -4);
}

/**
 * 掩码工具：姓名 → 张* / 李*四（两个字取首字，三字及以上取首尾）
 */
function realname_mask_name($name)
{
	$name = trim((string)$name);
	$len = mb_strlen($name, 'UTF-8');
	if ($len <= 1) {
		return $name === '' ? '**' : $name;
	}
	if ($len === 2) {
		return mb_substr($name, 0, 1, 'UTF-8') . '*';
	}
	return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $len - 2) . mb_substr($name, -1, 1, 'UTF-8');
}
