<?php
$root = __DIR__;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$phpFile = $root . $path;
if (is_file($phpFile) && substr($phpFile, -4) === '.php') {
    chdir(dirname($phpFile));
    require $phpFile;
    return true;
}
if (is_file($phpFile)) {
    $ext = pathinfo($phpFile, PATHINFO_EXTENSION);
    $mime = ['css'=>'text/css','js'=>'application/javascript','png'=>'image/png','jpg'=>'image/jpeg','gif'=>'image/gif','ico'=>'image/x-icon'];
    if (isset($mime[$ext])) header('Content-Type: '.$mime[$ext]);
    readfile($phpFile);
    return true;
}

// 文件未找到：交给插件通用路由（如 /landing）
// 注意：这里会触发完整的 common.php 启动流程（数据库连接、插件 boot）
$commonFile = $root . '/MPHX/common.php';
if (is_file($commonFile)) {
    require $commonFile;
    if (function_exists('mnbt_plugin_dispatch_route') && mnbt_plugin_dispatch_route()) {
        return true;
    }
}

return false;
