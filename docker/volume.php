<?php
include __DIR__ . '/head.php';
$me = docker_auth_require();
$title = '存储卷';
mnbt_docker_render('volume', ['title' => $title, 'me' => $me]);
