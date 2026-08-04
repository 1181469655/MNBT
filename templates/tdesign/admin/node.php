<?php
$tab = isset($_GET['tab']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$_GET['tab']) : '';
$td_entry = 'node';
$td_hash  = $tab === 'scan' ? '#/node/scan' : '#/node';
include __DIR__ . '/_spa_boot.php';
