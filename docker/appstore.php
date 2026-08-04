<?php
include __DIR__ . '/head.php';
$me = docker_auth_require();
$title = '应用商店';
$plan = docker_user_plan($me);
list(, $node) = docker_user_node($me);
mnbt_docker_render('appstore', ['title' => $title, 'me' => $me, 'plan' => $plan, 'node' => $node]);
