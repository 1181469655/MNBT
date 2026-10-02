<?php
/**
 * 在线文件管理模块（V1.86 重构版）
 *
 * 所有文件操作均转发到节点宝塔面板 /files 接口，动作与参数对齐官方文档：
 * https://docs.bt.cn/api/files/
 *
 * 通用约定：
 * - 仅处理 $egn 命中白名单的请求，其余请求直接返回，由 ajax.php 继续分发；
 * - 除回收站外，路径一律使用“站点相对路径”（以 / 开头，站点根目录为 /），
 *   后端统一规范化，禁止 .. 穿越，确保用户只能操作自己站点目录内的文件；
 * - 响应统一为 {success, qk, code, msg, data...}，qk=1 成功 / qk=4 失败；
 * - 每个写操作均写入 MN_log 操作日志。
 */

$file_actions = [
    'file_list', 'file_read', 'file_save', 'file_create',
    'file_delete', 'file_delete_batch', 'file_rename', 'file_copy',
    'file_compress', 'file_unzip', 'file_size',
    'file_upload_prepare', 'file_upload', 'file_download',
    'file_access', 'file_access_set',
    'recycle_list', 'recycle_restore', 'recycle_delete', 'recycle_clear', 'recycle_switch',
];
if (!in_array($egn, $file_actions, true)) {
    return;
}

// ---------------------------------------------------------------------------
//  路径与响应工具（函数定义带存在性保护，避免重复包含时冲突）
// ---------------------------------------------------------------------------

if (!function_exists('mnbt_file_norm_path')) {
    /**
     * 规范化站点相对路径：返回以 / 开头、无冗余分隔符、不含 . / .. 穿越段的路径。
     * 非法输入返回 null。根目录返回 '/'。
     */
    function mnbt_file_norm_path($path)
    {
        if (!is_string($path) || $path === '') return null;
        if (strpos($path, "\0") !== false) return null;
        if (strpos($path, '\\') !== false) return null;
        if (substr($path, 0, 1) !== '/') return null;
        $path = preg_replace('#/+#', '/', $path);
        $segments = explode('/', substr($path, 1));
        $out = [];
        foreach ($segments as $seg) {
            if ($seg === '' || $seg === '.') continue;
            if ($seg === '..') return null;
            $out[] = $seg;
        }
        return '/' . implode('/', $out);
    }
}

if (!function_exists('mnbt_file_check_name')) {
    /** 校验单级文件/目录名：禁止空名、路径分隔符、穿越段与控制字符 */
    function mnbt_file_check_name($name)
    {
        if (!is_string($name) || $name === '' || $name === '.' || $name === '..') return false;
        if (strpos($name, '/') !== false || strpos($name, '\\') !== false) return false;
        if (strpos($name, "\0") !== false) return false;
        if (preg_match('/[\x00-\x1f\x7f]/', $name)) return false;
        return true;
    }
}

if (!function_exists('mnbt_file_base')) {
    /** 站点在节点上的绝对根目录（不含尾部斜杠） */
    function mnbt_file_base()
    {
        global $os_xt, $yhc;
        return rtrim($os_xt . $yhc['sqldz'], '/');
    }
}

if (!function_exists('mnbt_file_abs')) {
    /** 站点相对路径 → 节点绝对路径（mnbt_file_norm_path 校验通过后调用） */
    function mnbt_file_abs($rel)
    {
        return mnbt_file_base() . ($rel === '/' ? '' : $rel);
    }
}

if (!function_exists('mnbt_file_rel_child')) {
    /** 目录相对路径 + 单级文件名 → 相对路径 */
    function mnbt_file_rel_child($dir, $name)
    {
        return ($dir === '/' ? '' : $dir) . '/' . $name;
    }
}

if (!function_exists('mnbt_file_is_ok')) {
    /** 判断宝塔接口返回是否成功（兼容 status 布尔/字符串 与 msg='success' 两种风格） */
    function mnbt_file_is_ok($r)
    {
        if (!is_array($r)) return false;
        if (isset($r['status'])) {
            return $r['status'] === true || $r['status'] === 'true' || $r['status'] === 1 || $r['status'] === '1';
        }
        return (($r['msg'] ?? '') === 'success');
    }
}

if (!function_exists('mnbt_file_names_from_post')) {
    /** 读取文件名数组参数：支持数组与 JSON 字符串两种提交形式 */
    function mnbt_file_names_from_post($key = 'names')
    {
        $raw = $_POST[$key] ?? [];
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (!is_array($raw)) return null;
        $names = [];
        foreach ($raw as $v) {
            if (!is_string($v)) return null;
            $v = trim($v);
            if ($v !== '') $names[] = $v;
        }
        return array_values(array_unique($names));
    }
}

// ---------------------------------------------------------------------------
//  公共上下文
// ---------------------------------------------------------------------------

include_once(__DIR__ . '/../../class.php');
$api = new bt_api($btipe, $btkeye);

// ---------------------------------------------------------------------------
//  目录浏览
// ---------------------------------------------------------------------------

if ($egn === 'file_list') {
    $path = mnbt_file_norm_path($_POST['path'] ?? '');
    if ($path === null) json_exit_error('目录格式错误！');
    $page = max(1, (int)($_POST['page'] ?? 1));
    $limit = (int)($_POST['limit'] ?? 100);
    if (!in_array($limit, [20, 50, 100, 200, 500, 1000], true)) $limit = 100;
    $sort = in_array($_POST['sort'] ?? '', ['name', 'size', 'mtime'], true) ? $_POST['sort'] : 'name';
    $reverse = (($_POST['sortOrder'] ?? 'asc') === 'desc') ? 'True' : 'False';

    $abs = mnbt_file_abs($path);
    $r = $api->GetLogshqwjlo($abs, $reverse, $sort, (string)$limit, (string)$page);
    if (!is_array($r)) $r = [];

    // 节点未按请求路径返回（如站点目录被移动/异常），强制回落站点根，防止越权浏览
    $paths = $path;
    $ret = isset($r['PATH']) ? rtrim((string)$r['PATH'], '/') : '';
    if ($ret !== '' && strcasecmp($ret, rtrim($abs, '/')) !== 0) {
        $r = $api->GetLogshqwjlo(mnbt_file_base(), $reverse, $sort, (string)$limit, (string)$page);
        if (!is_array($r)) $r = [];
        $paths = '/';
    }

    $rows = array_merge(
        dirfiles($r['DIR'] ?? [], 'dir')['file'],
        dirfiles($r['FILES'] ?? [], 'file')['file']
    );
    $total = 0;
    if (preg_match('/共(\d+)条/', (string)($r['PAGE'] ?? ''), $m)) $total = (int)$m[1];
    if ($total === 1 && empty($rows)) $total = 0;
    json_exit_success('获取成功', ['rows' => $rows, 'total' => $total, 'path' => $paths]);
}

// ---------------------------------------------------------------------------
//  文件内容读写
// ---------------------------------------------------------------------------

if ($egn === 'file_read') {
    $path = mnbt_file_norm_path($_POST['path'] ?? '');
    if ($path === null || $path === '/') json_exit_error('文件不存在！');
    $r = $api->hqwjnr(mnbt_file_abs($path));
    if (!is_array($r)) $r = [];
    $content = $r['DATA'] ?? $r['data'] ?? null;
    if ($content !== null && !is_scalar($content)) $content = null;
    if ($content === null) {
        // 部分面板版本成功时也可能不携带 DATA，此时视为空文件
        if (!empty($r['status'])) {
            $content = '';
        } else {
            json_exit_error((string)($r['msg'] ?? '文件读取失败！'));
        }
    }
    $encoding = '';
    foreach (['ENCODING', 'encoding'] as $k) {
        if (!empty($r[$k]) && is_string($r[$k])) {
            $encoding = $r[$k];
            break;
        }
    }
    json_exit_success('获取成功', ['content' => (string)$content, 'encoding' => $encoding]);
}

if ($egn === 'file_save') {
    $path = mnbt_file_norm_path($_POST['path'] ?? '');
    if ($path === null || $path === '/') json_exit_error('被保存文件不存在！');
    if (basename($path) === '.user.ini') json_exit_error('错误！禁止修改配置文件(.user.ini)！');
    // 文件内容原样保存，不做任何转义
    $content = (string)($_POST['content'] ?? '');
    $r = $api->setwj([$content, mnbt_file_abs($path)]);
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '修改文件', '修改了文件' . $path, $ok ? '修改成功' : '修改失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('保存成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '保存失败') : '保存失败');
}

if ($egn === 'file_create') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    $type = ($_POST['type'] ?? 'file') === 'dir' ? 'dir' : 'file';
    if ($dir === null) json_exit_error('目录格式错误！');
    if (!mnbt_file_check_name($name)) json_exit_error('名称格式错误！');
    if ($name === '.user.ini') json_exit_error('错误！禁止创建配置文件(.user.ini)！');
    $abs = mnbt_file_abs(mnbt_file_rel_child($dir, $name));
    $r = $type === 'dir' ? $api->xjwjj($abs) : $api->xjwj($abs);
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    $lx = $type === 'dir' ? '新建目录' : '新建文件';
    logjl($yhc['user'], $lx, $lx . mnbt_file_rel_child($dir, $name), $ok ? '新建成功' : '新建失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('创建成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '创建失败') : '创建失败');
}

// ---------------------------------------------------------------------------
//  删除 / 重命名 / 复制剪切
// ---------------------------------------------------------------------------

if ($egn === 'file_delete') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    $type = ($_POST['type'] ?? 'file') === 'dir' ? 'dir' : 'file';
    if ($dir === null) json_exit_error('目录格式错误！');
    if (!mnbt_file_check_name($name)) json_exit_error('文件名格式错误！');
    if ($name === '.user.ini') json_exit_error('错误！禁止删除配置文件(.user.ini)！');
    if ($type === 'file') {
        $r = $api->delwj(mnbt_file_abs(mnbt_file_rel_child($dir, $name)));
    } else {
        // delwjj 内部含运行目录/域名绑定子目录保护
        $r = $api->delwjj($dir, $name, [$yhc['btid'], mnbt_file_base()]);
    }
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '文件删除', '删除了文件' . mnbt_file_rel_child($dir, $name), $ok ? '删除成功' : '删除失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('删除成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '删除失败') : '删除失败');
}

if ($egn === 'file_delete_batch') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $names = mnbt_file_names_from_post('names');
    if ($dir === null) json_exit_error('目录格式错误！');
    if ($names === null || empty($names)) json_exit_error('您未选择需要删除的文件或目录！');
    foreach ($names as $n) {
        if (!mnbt_file_check_name($n)) json_exit_error('文件名格式错误：' . $n);
        if ($n === '.user.ini') json_exit_error('错误！禁止删除配置文件(.user.ini)！');
    }
    // xzdelwj 内部含运行目录/域名绑定子目录保护
    $r = $api->xzdelwj($dir, json_encode($names, JSON_UNESCAPED_UNICODE), [$yhc['btid'], mnbt_file_base()]);
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '文件删除', '批量删除了' . count($names) . '个文件', $ok ? '删除成功' : '删除失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('删除成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '删除失败') : '删除失败');
}

if ($egn === 'file_rename') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $old = trim((string)($_POST['oldname'] ?? ''));
    $new = trim((string)($_POST['newname'] ?? ''));
    if ($dir === null) json_exit_error('目录格式错误！');
    if (!mnbt_file_check_name($old)) json_exit_error('原文件名格式错误！');
    if (!mnbt_file_check_name($new)) json_exit_error('新文件名格式错误！');
    if ($old === '.user.ini') json_exit_error('错误！禁止重命名配置文件(.user.ini)！');
    if ($new === '.user.ini') json_exit_error('错误！该文件(.user.ini)已存在！');
    if ($old === $new) json_exit_error('新名称与原名称相同！');
    // cxname 内部含运行目录/域名绑定子目录保护
    $r = $api->cxname([mnbt_file_base(), $dir, $old, $new]);
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '重命名', '将' . $old . '重命名为' . $new, $ok ? '重命名成功' : '重命名失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('重命名成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '重命名失败') : '重命名失败');
}

if ($egn === 'file_copy') {
    $ypath = mnbt_file_norm_path($_POST['ypath'] ?? '');
    $xpath = mnbt_file_norm_path($_POST['xpath'] ?? '');
    $names = mnbt_file_names_from_post('names');
    $type = ($_POST['type'] ?? 'copy') === 'cut' ? 'cut' : 'copy';
    if ($ypath === null) json_exit_error('原目录格式错误！');
    if ($xpath === null) json_exit_error('粘贴目录格式错误！');
    if ($names === null || empty($names)) json_exit_error('错误！您未选择任何文件！');
    foreach ($names as $n) {
        if (!mnbt_file_check_name($n)) json_exit_error('文件名格式错误：' . $n);
        if ($n === '.user.ini') json_exit_error('错误！禁止操作配置文件(.user.ini)！');
    }
    if ($ypath === $xpath) json_exit_error('错误！原目录与粘贴目录不能相同！');
    if ($xpath !== '/') {
        foreach ($names as $n) {
            if (substr($xpath, 0, mb_strlen($ypath . $n . '/')) === $ypath . $n . '/') {
                json_exit_error('错误的逻辑，从' . $ypath . $n . '粘贴到' . $xpath . '有包含关系，存在无限循环复制风险！');
            }
        }
    }
    // 逐个文件复制：避免宝塔批量复制接口造成跨站点粘贴
    $yes = 0;
    $no = 0;
    foreach ($names as $n) {
        $r = $api->filecopy(
            mnbt_file_abs(mnbt_file_rel_child($ypath, $n)),
            mnbt_file_abs(mnbt_file_rel_child($xpath, $n))
        );
        mnbt_file_is_ok(is_array($r) ? $r : []) ? $yes++ : $no++;
    }
    if ($type === 'cut') {
        $api->xzdelwj($ypath, json_encode($names, JSON_UNESCAPED_UNICODE), [$yhc['btid'], mnbt_file_base()]);
    }
    $cz = $type === 'cut' ? '剪切' : '复制';
    logjl($yhc['user'], '文件操作', $cz . '了' . count($names) . '个文件', $no === 0 ? '操作成功' : "成功{$yes}个，失败{$no}个", $DB);
    if ($no === 0) json_exit_success($cz . '成功！');
    exit(json_encode(['qk' => 4, 'code' => "{$cz}成功{$yes}个文件，{$cz}失败{$no}个文件"], JSON_UNESCAPED_UNICODE));
}

// ---------------------------------------------------------------------------
//  压缩 / 解压 / 大小统计
// ---------------------------------------------------------------------------

if ($egn === 'file_compress') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $dest = mnbt_file_norm_path($_POST['dest'] ?? '');
    $names = mnbt_file_names_from_post('names');
    $type = in_array($_POST['type'] ?? '', ['zip', 'tar.gz', 'rar', '7z'], true) ? $_POST['type'] : 'zip';
    if ($dir === null) json_exit_error('目录格式错误！');
    if ($dest === null || $dest === '/') json_exit_error('压缩包存放路径格式错误！');
    if ($names === null || empty($names)) json_exit_error('错误！您未选择任何文件！');
    foreach ($names as $n) {
        if (!mnbt_file_check_name($n)) json_exit_error('文件名格式错误：' . $n);
    }
    // 宝塔 Zip 接口要求压缩包名与压缩类型后缀一致
    if (!preg_match('/\.' . preg_quote($type, '/') . '$/i', $dest)) {
        $dest .= '.' . $type;
    }
    // 宝塔 Zip 的 path 为解析 sfile 相对名的基准目录，需带尾部斜杠
    $r = $api->fileysr(implode(',', $names), mnbt_file_abs($dest), $type, mnbt_file_abs($dir) . '/');
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '文件压缩', '压缩了' . count($names) . '个文件到' . $dest, $ok ? '压缩成功' : '压缩失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('压缩成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '压缩失败') : '压缩失败');
}

if ($egn === 'file_unzip') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    $dest = mnbt_file_norm_path($_POST['dest'] ?? '');
    if ($dir === null) json_exit_error('目录格式错误！');
    if ($dest === null) json_exit_error('解压到的目录格式错误！');
    if (!mnbt_file_check_name($name)) json_exit_error('文件名格式错误！');
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext === '7z') json_exit_error('暂不支持解压 7z 格式，请使用 zip / tar.gz / rar 压缩包！');
    $type = ($ext === 'gz' || $ext === 'tgz') ? 'tar.gz' : ($ext === 'rar' ? 'rar' : 'zip');
    $coding = ($_POST['coding'] ?? 'UTF-8') === 'GBK' ? 'GBK' : 'UTF-8';
    $password = (string)($_POST['password'] ?? '');
    $r = $api->GetLogsjywj(
        mnbt_file_abs(mnbt_file_rel_child($dir, $name)),
        mnbt_file_abs($dest) . '/',
        $coding,
        $password,
        $type
    );
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '解压文件', '解压了文件' . mnbt_file_rel_child($dir, $name) . '到' . $dest, $ok ? '解压成功' : '解压失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('解压成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '解压失败') : '解压失败');
}

if ($egn === 'file_size') {
    $path = mnbt_file_norm_path($_POST['path'] ?? '');
    if ($path === null || $path === '/') json_exit_error('路径错误！');
    $r = $api->hqsize(mnbt_file_abs($path));
    $size = 0;
    if (is_array($r) && isset($r['size']) && is_numeric($r['size'])) $size = (float)$r['size'];
    json_exit_success('获取成功', ['size' => $size]);
}

// ---------------------------------------------------------------------------
//  分片上传（配额校验 + 断点续传）
// ---------------------------------------------------------------------------

if ($egn === 'file_upload_prepare') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    $size = (string)($_POST['size'] ?? '0');
    if ($dir === null) json_exit_error('目录格式错误！');
    if (!mnbt_file_check_name($name)) json_exit_error('文件名格式错误！');
    if ($name === '.user.ini') json_exit_error('禁止上传配置文件(.user.ini)！');
    if (!ctype_digit($size)) $size = '0';
    $r = $api->fileupa(mnbt_file_abs($dir) . '/' . $name . '.' . $size . '.upload.tmp');
    $offset = 0;
    if (is_array($r) && mnbt_file_is_ok($r)) {
        $m = $r['msg'] ?? 0;
        if (is_array($m)) {
            $offset = is_numeric($m['size'] ?? null) ? (float)$m['size'] : 0;
        } elseif (is_numeric($m)) {
            $offset = (float)$m;
        }
    }
    json_exit_success('获取成功', ['size' => $offset]);
}

if ($egn === 'file_upload') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    $start = is_numeric($_POST['start'] ?? null) ? (float)$_POST['start'] : 0;
    $total = is_numeric($_POST['size'] ?? null) ? (float)$_POST['size'] : 0;
    if ($dir === null) json_exit_error('目录格式错误！');
    if (!mnbt_file_check_name($name)) json_exit_error('上传的文件名格式错误！');
    if ($name === '.user.ini') json_exit_error('禁止上传配置文件(.user.ini)！');
    if (empty($_FILES['file']) || !is_array($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'] ?? '')) {
        json_exit_error('上传的分片数据为空！');
    }
    // 网页空间配额校验（hxa: {max: 总量MB, dq: 已用MB}）
    $websize = json_decode((string)($yhc['hxa'] ?? ''), true);
    if (!is_array($websize)) $websize = [];
    $max = (float)($websize['max'] ?? 0);
    $used = (float)($websize['dq'] ?? 0);
    $mb = round($total / 1048576);
    if ($max > 0 && $mb > $max) json_exit_error('错误！上传的文件大于您的最大可用网页空间，无法上传此文件！');
    if ($max > 0 && $max <= $used) json_exit_error('错误！网页空间已满！');
    if ($max > 0 && $mb > $max - $used) json_exit_error('错误！上传的文件大于当前可使用的网页空间，请清理空间后再试！');
    // 宝塔 upload 的 f_path 与面板一致需带尾部斜杠
    $r = $api->fileups(mnbt_file_abs($dir) . '/', $_FILES['file'], (string)$start, $name, (string)$total);
    if (is_numeric($r)) {
        // 宝塔返回已接收到的偏移量，分片未传完
        exit(json_encode(['qk' => 1, 'code' => '分片上传成功', 'size' => (float)$r, 'done' => false], JSON_UNESCAPED_UNICODE));
    }
    if (is_array($r) && mnbt_file_is_ok($r)) {
        exit(json_encode(['qk' => 1, 'code' => '上传成功！', 'done' => true], JSON_UNESCAPED_UNICODE));
    }
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '上传失败') : '上传失败');
}

// ---------------------------------------------------------------------------
//  文件下载（宝塔外链，计入当月流量）
// ---------------------------------------------------------------------------

if ($egn === 'file_download') {
    $dir = mnbt_file_norm_path($_POST['path'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    if ($dir === null) json_exit_error('目录格式错误！');
    if (!mnbt_file_check_name($name)) json_exit_error('文件名格式错误！');
    $r = $api->GetLogshqwjlo(mnbt_file_abs($dir));
    $list = dirfiles(is_array($r) ? ($r['FILES'] ?? []) : [], 'file')['file'];
    $file = null;
    foreach ($list as $val) {
        if (($val['name'] ?? '') === $name) {
            $file = $val;
            break;
        }
    }
    if (!$file) json_exit_error('文件不存在！');
    $data = [];
    if (!empty($file['download'])) {
        // 外链已开启：直接取链接；若带有密码则关闭后重新开启
        $data = $api->wailhq($file['download']);
        if (!is_array($data)) $data = [];
        if (($data['password'] ?? '') !== '') {
            $api->wailgb($file['download']);
            $data = $api->wailkq(mnbt_file_abs($dir) . '/', $name);
            if (!is_array($data)) $data = [];
        }
    } else {
        $data = $api->wailkq(mnbt_file_abs($dir) . '/', $name);
        if (!is_array($data)) $data = [];
    }
    $token = (string)($data['msg']['token'] ?? '');
    if ($token === '') json_exit_error('创建下载链接失败，请稍后再试！');
    // 流量计算：下载文件消耗的流量计入当月已用流量
    $llzd = json_decode((string)($yhc['llmax'] ?? ''), true);
    if (!is_array($llzd)) $llzd = [];
    $llzd['dq'] = (float)($llzd['dq'] ?? 0) + (float)($file['size'] ?? 0);
    $DB->query_prepare("update `MN_zj` set `llmax`=? where `id`=?", [json_encode($llzd, JSON_UNESCAPED_UNICODE), $yhid]);
    logjl($yhc['user'], '文件下载', '获取了文件' . mnbt_file_rel_child($dir, $name) . '的下载链接', '成功', $DB);
    json_exit_success('获取成功', ['url' => $btipe . '/down/' . $token]);
}

// ---------------------------------------------------------------------------
//  文件权限（docs.bt.cn/api/files → GetFileAccess / SetFileAccess）
// ---------------------------------------------------------------------------

if ($egn === 'file_access') {
    $path = mnbt_file_norm_path($_POST['path'] ?? '');
    if ($path === null || $path === '/') json_exit_error('路径错误！');
    $r = $api->file_access_get(mnbt_file_abs($path));
    $access = '';
    if (is_array($r)) {
        foreach (['msg', 'data', 'access'] as $k) {
            if (isset($r[$k]) && (is_string($r[$k]) || is_numeric($r[$k]))) {
                $access = trim((string)$r[$k]);
                break;
            }
        }
    } elseif (is_string($r) || is_numeric($r)) {
        $access = trim((string)$r);
    }
    if (!preg_match('/^[0-7]{3,4}$/', $access)) json_exit_error('节点未返回有效的文件权限信息！');
    json_exit_success('获取成功', ['access' => $access]);
}

if ($egn === 'file_access_set') {
    $path = mnbt_file_norm_path($_POST['path'] ?? '');
    if ($path === null || $path === '/') json_exit_error('路径错误！');
    $access = preg_replace('/[^0-7]/', '', (string)($_POST['access'] ?? ''));
    if (!preg_match('/^[0-7]{3,4}$/', $access)) json_exit_error('权限格式错误！请输入如 644 / 755 的权限值');
    $r = $api->file_access_set(mnbt_file_abs($path), $access);
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '文件权限', '将' . $path . '权限设置为' . $access, $ok ? '修改成功' : '修改失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('修改成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '修改失败') : '修改失败');
}

// ---------------------------------------------------------------------------
//  回收站（docs.bt.cn/api/files → Get_Recycle_bin / Re_Recycle_bin /
//  Delete_Recycle_bin / Close_Recycle_bin / Recycle_bin）
// ---------------------------------------------------------------------------

if ($egn === 'recycle_list') {
    $page = (string)max(1, (int)($_POST['page'] ?? 1));
    $r = $api->recycle_list($page);
    if (!is_array($r)) $r = [];
    exit(json_encode([
        'qk' => 1,
        'code' => '获取成功',
        'list' => is_array($r['list'] ?? null) ? array_values($r['list']) : [],
        'status' => !empty($r['status']),
        'status_db' => !empty($r['status_db']),
    ], JSON_UNESCAPED_UNICODE));
}

if ($egn === 'recycle_restore' || $egn === 'recycle_delete') {
    $rname = trim((string)($_POST['rname'] ?? ''));
    if ($rname === '' || strpos($rname, '/') !== false || strpos($rname, '\\') !== false || strpos($rname, '..') !== false) {
        json_exit_error('参数错误！');
    }
    $r = $egn === 'recycle_restore' ? $api->recycle_restore($rname) : $api->recycle_delete($rname);
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    $lx = $egn === 'recycle_restore' ? '恢复回收站文件' : '删除回收站文件';
    logjl($yhc['user'], '回收站', $lx . $rname, $ok ? '操作成功' : '操作失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('操作成功');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '操作失败') : '操作失败');
}

if ($egn === 'recycle_clear') {
    $r = $api->recycle_clear();
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '回收站', '清空了回收站', $ok ? '操作成功' : '操作失败：' . (is_array($r) ? (string)($r['msg'] ?? '未知错误') : '未知错误'), $DB);
    if ($ok) json_exit_success('回收站已清空');
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '操作失败') : '操作失败');
}

if ($egn === 'recycle_switch') {
    $r = $api->recycle_switch();
    $ok = mnbt_file_is_ok(is_array($r) ? $r : []);
    logjl($yhc['user'], '回收站', '切换了文件回收站开关', $ok ? '操作成功' : '操作失败', $DB);
    if ($ok) json_exit_success((string)($r['msg'] ?? '设置成功'));
    json_exit_error(is_array($r) ? (string)($r['msg'] ?? '操作失败') : '操作失败');
}
