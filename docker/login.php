<?php
include __DIR__ . '/head.php';
docker_auth_guest_only();
$title = 'Docker 控制台登录';
mnbt_docker_render('login', ['title' => $title]);
