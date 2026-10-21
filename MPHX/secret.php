<?php
if (!defined('SYSTEM_ROOT')) exit('Access Denied');

/**
 * 敏感字段统一加解密(AES-256-GCM)。
 * - 密钥 $mnbt_secret_key 存根目录 config.php(gitignored、更新器保护),64 位 hex;
 *   缺失时首次调用自动生成并追加写回 config.php,写不进去直接抛错(静默降级等于假加密)
 * - 密文统一 enc1: 前缀;解密遇无前缀值原样返回,历史明文在迁移前照常工作
 * - 防的是拖库/备份泄露/注入读表;密钥同机落盘,不防整机沦陷
 */

function mnbt_secret_key()
{
	global $mnbt_secret_key;
	if (isset($mnbt_secret_key) && is_string($mnbt_secret_key) && strlen($mnbt_secret_key) >= 64) {
		return substr($mnbt_secret_key, 0, 64);
	}
	$key = bin2hex(random_bytes(32));
	$file = ROOT . 'config.php';
	$line = "\n\$mnbt_secret_key='" . $key . "';\n";
	if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
		throw new RuntimeException('缺少加密密钥：请确认 config.php 可写，或手动加入 $mnbt_secret_key（64 位 hex）');
	}
	$GLOBALS['mnbt_secret_key'] = $key;
	return $key;
}

/** 明文 → enc1: 密文;空值与已加密值原样返回(幂等) */
function mnbt_secret_encrypt($plain)
{
	if ($plain === null || $plain === '') return $plain;
	if (!is_string($plain)) $plain = (string)$plain;
	if (strpos($plain, 'enc1:') === 0) return $plain;
	$key = hex2bin(mnbt_secret_key());
	$nonce = random_bytes(12);
	$tag = '';
	$cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
	if ($cipher === false) {
		throw new RuntimeException('secret encrypt failed');
	}
	return 'enc1:' . base64_encode($nonce . $tag . $cipher);
}

/** enc1: 密文 → 明文;无前缀(历史明文)原样返回;解密失败返回 '' */
function mnbt_secret_decrypt($stored)
{
	if (!is_string($stored) || strpos($stored, 'enc1:') !== 0) return $stored;
	$raw = base64_decode(substr($stored, 5), true);
	if ($raw === false || strlen($raw) < 29) return '';
	$key = hex2bin(mnbt_secret_key());
	$nonce = substr($raw, 0, 12);
	$tag = substr($raw, 12, 16);
	$cipher = substr($raw, 28);
	$plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
	return $plain === false ? '' : $plain;
}
