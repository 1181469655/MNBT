<?php
if (!defined('SYSTEM_ROOT')) exit('Access Denied');

/**
 * 敏感字段统一加解密(AES-256-GCM)。
 * - 密钥 $mnbt_secret_key 存根目录 config.php(gitignored、更新器保护),64 位 hex;
 *   缺失时只有在"库内还没有任何密文"的前提下才自动生成并追加写回,
 *   已有密文时缺密钥=旧密钥丢失,此时抛明确错误而不是换一把新的(换新密钥等于把全部密文判死刑)
 * - 解密只读现有密钥,任何情况下都不会生成或写 config.php
 * - 密文统一 enc1: 前缀;无前缀值原样返回,历史明文在迁移前照常工作
 * - 防的是拖库/备份泄露/注入读表;密钥同机落盘,不防整机沦陷
 */

/** 密钥/加密自身的可读写错误;异常处理器只把这一类的消息原文回给前端,其他异常一律隐藏细节 */
class MNBT_SecretException extends RuntimeException {}

/** 当前 config.php 里的密钥(只读;没有则返回 '') */
function mnbt_secret_config_key()
{
	global $mnbt_secret_key;
	if (isset($mnbt_secret_key) && is_string($mnbt_secret_key) && strlen($mnbt_secret_key) >= 64) {
		return substr($mnbt_secret_key, 0, 64);
	}
	return '';
}

/**
 * 库里是否已经存在 enc1: 密文。用来判断"缺密钥"是不是旧密钥丢失。
 * 结论缓存在进程内;拿不到 $DB 时返回 false 且不缓存(避免早期请求把错误结论固化)。
 */
function mnbt_secret_has_cipher()
{
	static $has = null;
	if ($has !== null) return $has;
	if (!isset($GLOBALS['DB']) || !is_object($GLOBALS['DB'])) return false;
	$DB = $GLOBALS['DB'];
	$targets = [
		['MN_zj', 'pass'], ['MN_zj', 'sqlpass'],
		['MN_bt', 'btmy'], ['MN_bt', 'ktmy'], ['MN_bt', 'qmk'],
		['MN_docker_node', 'btmy'], ['MN_docker_node', 'ktmy'], ['MN_docker_node', 'qmk'],
		['MN_config', 'mailpassword'],
		['plg_dns_provider', 'api_secret'],
		['MN_plugin_respool', 'password'],
	];
	foreach ($targets as $t) {
		// 表可能不存在（插件未装/精简库）：mysqli 驱动下 get_row 会因 fetch(false) 抛 TypeError，
		// PDO 驱动下会抛 PDOException，都按"这张表没有密文"处理，不能让探测本身把页面打挂
		try {
			$row = @$DB->get_row("SELECT 1 AS hit FROM `{$t[0]}` WHERE `{$t[1]}` LIKE 'enc1:%' LIMIT 1");
		} catch (Throwable $e) {
			continue;
		}
		if (!empty($row['hit'])) { $has = true; return $has; }
	}
	$has = false;
	return $has;
}

/** 加密用密钥:没有则按老行为生成一把并追加写回 config.php;已有密文时缺密钥则拒绝生成 */
function mnbt_secret_key()
{
	$key = mnbt_secret_config_key();
	if ($key !== '') return $key;
	if (mnbt_secret_has_cipher()) {
		throw new MNBT_SecretException(
			'库里已存在 enc1: 密文，但 config.php 缺少当初的加密密钥 $mnbt_secret_key。'
			. '不要让它自动生成新密钥——旧密文会永久解不开。'
			. '请从备份恢复原 config.php（或原 $mnbt_secret_key 那一行），'
			. '或把后台里的节点密钥、主机密码重新录入一次后再启用加密。'
		);
	}
	$key = bin2hex(random_bytes(32));
	$file = ROOT . 'config.php';
	$line = "\n\$mnbt_secret_key='" . $key . "';\n";
	if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
		throw new MNBT_SecretException('缺少加密密钥：请确认 config.php 可写，或手动加入 $mnbt_secret_key（64 位 hex）');
	}
	$GLOBALS['mnbt_secret_key'] = $key;
	return $key;
}

/** 密文比明文长，这些列必须留够宽度（表 => 列） */
function mnbt_secret_columns()
{
	return [['MN_zj', 'pass'], ['MN_config', 'mailpassword']];
}

/**
 * 写入前把上面这些列加宽到 500。连接层是 sql_mode=''，varchar 超长不报错只静默截断，
 * 截断后的 enc1: 永远解不开——新库的 MN_zj.pass 原本只有 varchar(50)，等于一装好就把主机密码写坏。
 * 同一次请求内只探测一次；没 DDL 权限时安静跳过，
 * 由 admin/migrate_secrets.php 或 update/ 增量 SQL 负责正式加宽。
 */
function mnbt_secret_ensure_columns()
{
	static $done = false;
	if ($done || defined('SQLITE')) return;
	$done = true;
	if (!isset($GLOBALS['DB']) || !is_object($GLOBALS['DB'])) return;
	$DB = $GLOBALS['DB'];
	foreach (mnbt_secret_columns() as $t) {
		try {
			$meta = $DB->get_row_prepare(
				"SELECT COLUMN_TYPE, IS_NULLABLE, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?",
				[$t[0], $t[1]]
			);
			if (!$meta || !is_array($meta)) continue;
			if (!preg_match('/^varchar\((\d+)\)$/i', (string)$meta['COLUMN_TYPE'], $m)) continue;
			if ((int)$m[1] >= 500) continue;
			$alter = "ALTER TABLE `{$t[0]}` MODIFY `{$t[1]}` VARCHAR(500)";
			if (!empty($meta['CHARACTER_SET_NAME'])) $alter .= " CHARACTER SET {$meta['CHARACTER_SET_NAME']} COLLATE {$meta['COLLATION_NAME']}";
			$alter .= ($meta['IS_NULLABLE'] === 'YES') ? ' NULL' : ' NOT NULL';
			$DB->query($alter);
		} catch (Throwable $e) {
			// 忽略：加宽失败不该打断业务写入，最坏情况仍是截断，由迁移脚本报错兜底
		}
	}
}

/** 明文 → enc1: 密文;空值与已加密值原样返回(幂等) */
function mnbt_secret_encrypt($plain)
{
	if ($plain === null || $plain === '') return $plain;
	if (!is_string($plain)) $plain = (string)$plain;
	if (strpos($plain, 'enc1:') === 0) return $plain;
	mnbt_secret_ensure_columns();
	$key = hex2bin(mnbt_secret_key());
	$nonce = random_bytes(12);
	$tag = '';
	$cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($cipher === false) {
		throw new RuntimeException('secret encrypt failed');
	}
	return 'enc1:' . base64_encode($nonce . $tag . $cipher);
}

/** enc1: 密文 → 明文;无前缀(历史明文)原样返回;解不开返回 '' */
function mnbt_secret_decrypt($stored)
{
	if (!is_string($stored) || strpos($stored, 'enc1:') !== 0) return $stored;
	$key = mnbt_secret_config_key();
	if ($key === '') return '';
	$raw = base64_decode(substr($stored, 5), true);
	if ($raw === false || strlen($raw) < 29) return '';
	$key = hex2bin($key);
	$nonce = substr($raw, 0, 12);
	$tag = substr($raw, 12, 16);
	$cipher = substr($raw, 28);
	$plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
	return $plain === false ? '' : $plain;
}

/**
 * 这个存量值是不是"解不开的密文"(旧密钥丢失或数据损坏)。
 * 空明文从来不会被加密，所以 enc1: 解出空串只可能是失败。
 */
function mnbt_secret_is_unreadable($stored)
{
	return is_string($stored) && strpos($stored, 'enc1:') === 0 && mnbt_secret_decrypt($stored) === '';
}

/**
 * 表单回填字段写回时的防误清空：库里是解不开的密文、这次又传来空值(回填失败的样子)，
 * 就原样保留旧密文——否则一次"打开编辑页再保存"就能把旧密文覆盖成空，连找回密钥的机会都没了。
 */
function mnbt_secret_keep_unreadable($incoming, $stored)
{
	if (($incoming === null || $incoming === '') && mnbt_secret_is_unreadable($stored)) return $stored;
	return mnbt_secret_encrypt($incoming);
}
