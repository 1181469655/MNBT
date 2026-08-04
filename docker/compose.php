<?php
include __DIR__ . '/head.php';
$me = docker_auth_require();
$title = 'Compose 与项目';
mnbt_docker_render('compose', ['title' => $title, 'me' => $me]);
