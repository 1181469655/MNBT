<?php
/**
 * 一次性迁移脚本：明文密码/密钥 → 密文（AES-256-GCM，enc1: 前缀）
 * 用法：以管理员身份登录后台后，浏览器访问 /admin/migrate_secrets.php
 * 幂等：已迁移（enc1: 前缀 / bcrypt 哈希）的数据自动跳过，可重复执行
 * 全部成功后自动删除自身；有失败则保留本文件，修正后可重跑
 */
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
@set_time_limit(0);

if (empty($islogin)) {
	exit('<!doctype html><meta charset="utf-8">请先以管理员身份登录 <a href="login.php">后台</a>，再执行本脚本。');
}

$results = [];
$errors  = [];

// 迁移清单：表 => 需加密的列
$secret_cols = [
	'MN_zj'             => ['pass', 'sqlpass'],
	'MN_bt'             => ['btmy', 'ktmy', 'qmk'],
	'MN_docker_node'    => ['btmy', 'ktmy', 'qmk'],
	'MN_config'         => ['mailpassword'],
	'plg_dns_provider'  => ['api_secret'],
	'MN_plugin_respool' => ['password'],//资源池（RP_TABLE）；插件停用时表可能仍在，残留明文也一并迁移
];

function msc_table_exists($DB, $table)
{
	$table = str_replace('`', '', $table);
	if (defined('SQLITE')) {
		return (bool)$DB->get_row_prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table]);
	}
	// SHOW TABLES LIKE ? 在部分 MySQL/MariaDB 预处理协议下不可用，统一走 information_schema
	return (bool)$DB->get_row_prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?", [$table]);
}

// 核心表缺失视为错误（正常面板必有），避免检查失灵时"什么都没做却自删"
function msc_missing_table($DB, $table, &$results, &$errors)
{
	if (msc_table_exists($DB, $table)) return true;
	if (in_array($table, ['MN_zj', 'MN_bt', 'MN_config'], true)) {
		$errors[] = "核心表 {$table} 不存在，请确认脚本连接的是面板自己的数据库";
	} else {
		$results[] = "跳过（表/对应插件未安装）：{$table}";
	}
	return false;
}

try {
	// 1. 加宽 varchar 列（enc1: 密文比明文长；SQLite 类型不限制长度，跳过）
	if (!defined('SQLITE')) {
		foreach ($secret_cols as $table => $cols) {
			if (!msc_missing_table($DB, $table, $results, $errors)) continue;
			foreach ($cols as $col) {
				$meta = $DB->get_row_prepare(
					"SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?",
					[$table, $col]
				);
				if (!$meta) { $errors[] = "{$table}.{$col} 列不存在，请确认表结构"; continue; }
				if (!preg_match('/^varchar\((\d+)\)$/i', (string)$meta['COLUMN_TYPE'], $m)) continue;
				if ((int)$m[1] >= 500) continue;
				$alter = "ALTER TABLE `{$table}` MODIFY `{$col}` VARCHAR(500)";
				if (!empty($meta['CHARACTER_SET_NAME'])) $alter .= " CHARACTER SET {$meta['CHARACTER_SET_NAME']} COLLATE {$meta['COLLATION_NAME']}";
				$alter .= ($meta['IS_NULLABLE'] === 'NO') ? ' NOT NULL' : ' NULL';
				if ($meta['COLUMN_DEFAULT'] !== null) $alter .= " DEFAULT '" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$meta['COLUMN_DEFAULT']) . "'";
				if ($DB->query($alter)) {
					$results[] = "加宽列：{$table}.{$col}（varchar({$m[1]}) → varchar(500)）";
				} else {
					$errors[] = "加宽列失败：{$table}.{$col} —— " . $DB->error();
				}
			}
		}
	}

	// 2. 逐列加密（空值与已是密文的行跳过）
	foreach ($secret_cols as $table => $cols) {
		if (!msc_missing_table($DB, $table, $results, $errors)) continue;
		foreach ($cols as $col) {
			$rows = $DB->get_all_prepare("SELECT `id`, `{$col}` AS v FROM `{$table}`");
			$done = 0; $skip = 0; $fail = 0;
			foreach ((array)$rows as $r) {
				$v = (string)$r['v'];
				if ($v === '' || strpos($v, 'enc1:') === 0) { $skip++; continue; }
				$enc = mnbt_secret_encrypt($v);
				if ($enc === $v) { $skip++; continue; }
				if ($DB->query_prepare("UPDATE `{$table}` SET `{$col}`=? WHERE id=?", [$enc, $r['id']])) $done++; else $fail++;
			}
			$results[] = "{$table}.{$col}：新加密 {$done} 条，跳过 {$skip} 条" . ($fail > 0 ? "，失败 {$fail} 条" : "");
			if ($fail > 0) $errors[] = "{$table}.{$col} 有 {$fail} 条写入失败";
		}
	}

	// 3. 管理员密码：明文残留改 bcrypt 哈希（不可逆，与邮箱授权码等可逆密钥分开处理）
	$crow = $DB->get_row_prepare("SELECT pwd FROM MN_config WHERE id=?", [$siteid]);
	$pwd  = (string)($crow['pwd'] ?? '');
	if ($pwd !== '' && !mnbt_admin_password_is_hash($pwd)) {
		if ($DB->query_prepare("UPDATE MN_config SET pwd=? WHERE id=?", [mnbt_admin_password_hash($pwd), $siteid])) {
			$results[] = '管理员密码：明文已改为 bcrypt 哈希存储（所有后台登录会话需重新登录）';
		} else {
			$errors[] = '管理员密码哈希写入失败：' . $DB->error();
		}
	} else {
		$results[] = '管理员密码：已是哈希存储或为空，跳过';
	}
} catch (Throwable $e) {
	$errors[] = $e->getMessage();
}

$out_rows = '';
foreach ($results as $line) $out_rows .= '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
$err_rows = '';
foreach ($errors as $line) $err_rows .= '<li style="color:#c00">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';

if (empty($errors)) {
	$deleted = @unlink(__FILE__);
	echo '<!doctype html><meta charset="utf-8"><title>密文迁移完成</title><body style="font-family:sans-serif;max-width:640px;margin:40px auto">';
	echo '<h3>迁移完成</h3><ul>' . $out_rows . '</ul>';
	echo $deleted
		? '<p>本脚本已自动删除。</p>'
		: '<p style="color:#c00">迁移完成，但脚本自动删除失败，请手动删除 admin/migrate_secrets.php。</p>';
	echo '<p>注意：本管理员会话已失效，请 <a href="login.php">重新登录后台</a>。</p>';
	echo '</body>';
} else {
	echo '<!doctype html><meta charset="utf-8"><title>密文迁移有失败项</title><body style="font-family:sans-serif;max-width:640px;margin:40px auto">';
	echo '<h3>迁移未全部完成</h3><ul>' . $out_rows . '</ul>';
	echo '<p><b>失败项（脚本已保留，处理后刷新本页重跑，已成功部分会自动跳过）：</b></p><ul>' . $err_rows . '</ul>';
	echo '</body>';
}
