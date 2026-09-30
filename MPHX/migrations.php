<?php
if (!defined('IN_CRONLITE')) exit();
/**
 * 版本化迁移链引擎（V1.85 起）
 *
 * 目标：更新包里 update/ 目录按版本各放一个小增量 SQL（update_v183_xxx.sql、
 * update_1860_xxx.sql…），执行器读库里的"已应用版本"游标，只把游标之后、
 * 目标版本之前的迁移按升序依次跑一遍，做到任意老版本一步跳到目标版本。
 *
 * 为什么这么设计：
 * - 游标用独立表 MN_dbver（自举建表），不塞 MN_config，避免和业务配置耦合、
 *   也留下每个版本何时应用的审计。
 * - 语句切分支持 DELIMITER，因为幂等的"存储过程守护列存在"写法（v183/v184）
 *   必须整段（含过程体内的分号）作为一个语句执行，朴素按 ; 切会崩。
 * - 容忍"对象已存在"类错误码，配合迁移文件本身的幂等写法，存量老站从头重跑也安全。
 * 只定义函数，不产生任何输出；入参用 $dbconfig 自建连接，调用方无需传 DB 层。
 */

/** 迁移重跑时"对象/列/索引已存在"或"已删除"类错误码，视为幂等跳过；其余按致命错误停止 */
function mnbt_migrations_tolerant_errno()
{
	// 1050 表已存在 1060 列重复 1061 键名重复(MySQL) 1826 索引重复(MariaDB) 1091 无法删除不存在列/索引
	return array(1050, 1060, 1061, 1826, 1091);
}

/**
 * 迁移文件名 → 版本整数（与 $WEBQB / tag_to_version 同一套：主版本*1000+次版本*10+补丁）
 * 支持两种写法：3 位 update_v183_xxx.sql（=>1830）、4 位 update_1860_xxx.sql / update_v1860_xxx.sql（=>1860）
 * 不符合命名（如旧的单文件 update.sql）返回 0，交由链忽略
 */
function mnbt_migration_version_from_name($path)
{
	$name = basename((string)$path);
	if (!preg_match('/^update_v?(\d{3,4})_.*\.sql$/i', $name, $m)) return 0;
	$ds = $m[1];
	if (strlen($ds) >= 4) {
		$major = (int)$ds[0];
		$minor = (int)substr($ds, 1, 2);
		$patch = (int)substr($ds, 3, 1);
	} else { // 3 位：形如 183 => 主1 次83
		$major = (int)$ds[0];
		$minor = (int)substr($ds, 1, 2);
		$patch = 0;
	}
	return $major * 1000 + $minor * 10 + min(9, $patch);
}

/**
 * DELIMITER-aware 语句切分：
 * - 识别 `DELIMITER xxx` 指令行切换结束符
 * - 跳过 -- / # 行注释与 &#47;* *&#47; 块注释（注释后的换行保留，避免相邻行粘连）
 * - 单引号/双引号/反引号内的分隔符与注释不参与切分，反斜杠转义（反引号除外）
 * @return array 去掉空白与纯注释后的完整语句列表
 */
function mnbt_sql_statements($sql)
{
	$sql = (string)$sql;
	$stmts = array();
	$delim = ';';
	$buf = '';
	$len = strlen($sql);
	$i = 0;
	$q = '';            // 当前引号：'' 无，或 ' " `
	$line_start = true; // 是否处于行首（允许出现 DELIMITER 指令）
	while ($i < $len) {
		$c = $sql[$i];
		if ($q !== '') { // 引号内：只关心转义与闭合，其余原样进 buf
			if ($c === '\\' && $q !== '`') {
				$buf .= $c;
				if ($i + 1 < $len) $buf .= $sql[$i + 1];
				$i += 2;
				continue;
			}
			if ($c === $q) { $buf .= $c; $q = ''; $i++; $line_start = false; continue; }
			$buf .= $c;
			if ($c === "\n" || $c === "\r") $line_start = true;
			$i++;
			continue;
		}
		// 引号外：先处理注释
		if ($c === '-' && $i + 1 < $len && $sql[$i + 1] === '-') { // -- 行注释
			$j = $i + 2;
			while ($j < $len && $sql[$j] !== "\n") $j++;
			$i = $j; // 停在换行上，交给下面的普通分支保留换行
			continue;
		}
		if ($c === '#') { // # 行注释
			$j = $i + 1;
			while ($j < $len && $sql[$j] !== "\n") $j++;
			$i = $j;
			continue;
		}
		if ($c === '/' && $i + 1 < $len && $sql[$i + 1] === '*') { // 块注释
			$j = $i + 2;
			while ($j < $len && !($sql[$j] === '*' && $j + 1 < $len && $sql[$j + 1] === '/')) $j++;
			$i = ($j < $len) ? $j + 2 : $j;
			continue;
		}
		// 引号开始
		if ($c === "'" || $c === '"' || $c === '`') {
			$q = $c; $buf .= $c; $i++; $line_start = false;
			continue;
		}
		// DELIMITER 指令（必须位于行首且当前语句缓冲为空）；结束符可能是 ';'，故用 \S+ 捕获
		if ($line_start && trim($buf) === '' && preg_match('/^[ \t]*DELIMITER[ \t]+(\S+)/i', substr($sql, $i), $md)) {
			$delim = $md[1];
			$j = $i;
			while ($j < $len && $sql[$j] !== "\n") $j++;
			$i = $j; $buf = '';
			continue;
		}
		// 结束符匹配
		$dl = strlen($delim);
		if ($dl > 0 && substr($sql, $i, $dl) === $delim) {
			$s = trim($buf);
			if ($s !== '') $stmts[] = $s;
			$buf = ''; $i += $dl; $line_start = false;
			continue;
		}
		// 普通字符入缓冲
		if ($c === "\n" || $c === "\r") { $line_start = true; }
		elseif ($c === ' ' || $c === "\t") { /* 维持 line_start */ }
		else { $line_start = false; }
		$buf .= $c;
		$i++;
	}
	$s = trim($buf);
	if ($s !== '') $stmts[] = $s;
	return $stmts;
}

/** 用 $dbconfig 建一个 mysqli（关闭异常模式，保持 query 返回 false 的旧语义）；失败写 $err 返回 null */
function mnbt_migrations_db($dbconfig, &$err)
{
	$err = '';
	if (!class_exists('mysqli')) {
		$err = '服务器未启用 mysqli 扩展，无法执行升级 SQL';
		return null;
	}
	if (function_exists('mysqli_report')) @mysqli_report(MYSQLI_REPORT_OFF);
	$port = !empty($dbconfig['port']) ? (int)$dbconfig['port'] : 3306;
	try {
		$db = @new mysqli($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname'], $port);
	} catch (Throwable $e) {
		$err = '连接数据库出错：' . $e->getMessage();
		return null;
	}
	if (mysqli_connect_errno()) {
		$err = '连接数据库出错：' . mysqli_connect_error();
		return null;
	}
	$db->set_charset('utf8');
	$db->query("set sql_mode = ''");
	return $db;
}

/** 建游标表（自举，幂等） */
function mnbt_migrations_ensure($db, &$err)
{
	$sql = "CREATE TABLE IF NOT EXISTS `MN_dbver` ("
		. "`version` int(11) NOT NULL,"
		. "`file` varchar(191) NOT NULL DEFAULT '',"
		. "`applied_at` datetime NOT NULL,"
		. "PRIMARY KEY (`version`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
	if ($db->query($sql) === false) {
		$err = '创建迁移记录表 MN_dbver 失败：' . $db->error;
		return false;
	}
	return true;
}

/** 当前库已应用到的版本（高水位游标）；无记录返回 0 */
function mnbt_migrations_cursor($db)
{
	$r = $db->query("SELECT MAX(`version`) AS v FROM `MN_dbver`");
	if (!$r) return 0;
	$row = $r->fetch_assoc();
	if (is_object($r)) $r->free();
	return isset($row['v']) ? (int)$row['v'] : 0;
}

/** 记录某版本已应用（REPLACE 防主键冲突） */
function mnbt_migrations_record($db, $version, $file)
{
	$version = (int)$version;
	$now = date('Y-m-d H:i:s');
	$stmt = $db->prepare("REPLACE INTO `MN_dbver`(`version`,`file`,`applied_at`) VALUES(?,?,?)");
	if ($stmt) {
		$stmt->bind_param('iss', $version, $file, $now);
		$stmt->execute();
		$stmt->close();
		return;
	}
	// 兜底：预处理不可用时走转义拼接
	$safe_file = $db->real_escape_string($file);
	$db->query("REPLACE INTO `MN_dbver`(`version`,`file`,`applied_at`) VALUES($version,'$safe_file','$now')");
}

/** 顺序执行一组语句；容忍"已存在"类错误，其余即视为致命并停止 */
function mnbt_migrations_exec($db, $stmts, &$err)
{
	$tol = mnbt_migrations_tolerant_errno();
	foreach ($stmts as $st) {
		if (trim($st) === '') continue;
		$res = $db->query($st);
		if ($res === false) {
			if (in_array($db->errno, $tol, true)) { continue; }
			$preview = function_exists('mb_substr') ? mb_substr($st, 0, 140) : substr($st, 0, 420);
			$err = 'SQL 错误 ' . $db->errno . '：' . $db->error . '（语句：' . $preview . '…）';
			return false;
		}
		if (is_object($res)) $res->free();
	}
	return true;
}

/** 扫描迁移目录，返回按版本升序的迁移项 [{version,name,file}] */
function mnbt_migrations_find($dir)
{
	$out = array();
	$paths = glob(rtrim($dir, '/') . '/update_*.sql');
	if (!is_array($paths)) return $out;
	foreach ($paths as $p) {
		$v = mnbt_migration_version_from_name($p);
		if ($v <= 0) continue; // 不符合命名规范（如遗留单文件 update.sql）忽略
		$out[] = array('version' => $v, 'name' => basename($p), 'file' => $p);
	}
	usort($out, function ($a, $b) {
		if ($a['version'] === $b['version']) return strcmp($a['name'], $b['name']);
		return $a['version'] - $b['version'];
	});
	return $out;
}

/**
 * 执行迁移链：只跑「游标 < 版本 <= 目标」的迁移，按升序，成功一个记一个。
 * 中途失败即停止并保留后续文件（游标停在最后成功版本，修复后重跑可续）。
 * @return array 已成功应用的迁移文件名列表
 */
function mnbt_migrations_run($dbconfig, $dir, $target, &$err)
{
	$applied = array();
	$err = '';
	$files = mnbt_migrations_find($dir);
	if (!$files) return $applied; // 无迁移文件视为无需变更
	$db = mnbt_migrations_db($dbconfig, $conn_err);
	if ($db === null) { $err = $conn_err; return $applied; }
	if (!mnbt_migrations_ensure($db, $err)) { $db->close(); return $applied; }
	$cursor = mnbt_migrations_cursor($db);
	foreach ($files as $f) {
		if ($f['version'] > $target) break;     // 已按版本升序，后续都超目标
		if ($f['version'] <= $cursor) continue;  // 游标之前的高水位已应用
		$sql = @file_get_contents($f['file']);
		if ($sql === false) { $err = '无法读取迁移文件 ' . $f['name']; break; }
		$stmts = mnbt_sql_statements($sql);
		if (!$stmts && trim($sql) !== '') { $err = '迁移文件未解析出任何语句：' . $f['name']; break; }
		if (!mnbt_migrations_exec($db, $stmts, $err)) { $err = '迁移 ' . $f['name'] . '（版本 ' . $f['version'] . '）失败：' . $err; break; }
		mnbt_migrations_record($db, $f['version'], $f['name']);
		$applied[] = $f['name'];
	}
	$db->close();
	if ($err !== '') return array(); // 有错：整链视为失败，交由上层保留文件、提示手工处理
	return $applied;
}

/**
 * 基线播种：全新安装 / 安装向导升级后调用，把游标抬到目标版本，
 * 之后在线更新只跑更高版本的增量，不会重放已到位的历史迁移。
 * 已到位的表/列由安装流程本身保证，这里只是记账。
 */
function mnbt_migrations_seed($dbconfig, $version, &$err)
{
	$version = (int)$version;
	$err = '';
	if ($version <= 0) { $err = '基线版本号无效'; return false; }
	$db = mnbt_migrations_db($dbconfig, $conn_err);
	if ($db === null) { $err = $conn_err; return false; }
	if (!mnbt_migrations_ensure($db, $err)) { $db->close(); return false; }
	if (mnbt_migrations_cursor($db) < $version) {
		mnbt_migrations_record($db, $version, '__baseline__');
	}
	$db->close();
	return true;
}
?>
