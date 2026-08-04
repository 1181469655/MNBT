<?php
include __DIR__ . '/head.php';
$me = docker_auth_require();
$title = '本地镜像';
mnbt_docker_render('image', ['title' => $title, 'me' => $me]);
