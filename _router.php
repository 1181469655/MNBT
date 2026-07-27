<?php
$root = realpath(__DIR__); // 获取绝对路径，消除符号链接影响
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. 安全过滤：去除路径中的 ../ 和 ./，防止路径遍历
$safePath = str_replace(['../', './', '..\\', '.\\'], '', $path);
// 确保路径以 / 开头
if (substr($safePath, 0, 1) !== '/') {
    $safePath = '/' . $safePath;
}

$phpFile = $root . $safePath;

// 2. 核心防御：使用 realpath 解析真实路径，并检查是否在允许的 $root 目录下
$realFile = realpath($phpFile);

if ($realFile !== false && strpos($realFile, $root) === 0) {

    // 处理 PHP 文件
    if (is_file($realFile) && substr($realFile, -4) === '.php') {
        chdir(dirname($realFile));
        require $realFile;
        return true;
    }

    // 处理静态资源
    if (is_file($realFile)) {
        $ext = pathinfo($realFile, PATHINFO_EXTENSION);
        $mime = [
            'css'=>'text/css',
            'js'=>'application/javascript',
            'png'=>'image/png',
            'jpg'=>'image/jpeg',
            'gif'=>'image/gif',
            'ico'=>'image/x-icon'
        ];
        if (isset($mime[$ext])) {
            header('Content-Type: ' . $mime[$ext]);
        }
        readfile($realFile);
        return true;
    }
}

// 文件未找到或路径非法：交给插件通用路由
$commonFile = $root . '/MPHX/common.php';
if (is_file($commonFile)) {
    require $commonFile;
    if (function_exists('mnbt_plugin_dispatch_route') && mnbt_plugin_dispatch_route()) {
        return true;
    }
}

return false;