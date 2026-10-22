<?php
/*
 * config.php 样例：本文件只是给人看的模板，程序不会读它。
 * 真正的 config.php 由安装向导生成（install/install.api.php 的 database_info_wire 步骤），
 * 且已被 .gitignore 排除——所以 GitHub 源码包、在线更新都不会带着它走，也不会被覆盖更新冲掉。
 *
 * 手工部署时才复制成 config.php 并填写，注意两点：
 * 1) $mnbt_secret_key 必须是一把全新的 64 位 hex（生成：php -r "echo bin2hex(random_bytes(32));"），
 *    绝不能从别处复制、更不能多台站共用一把——库里的宝塔节点密钥、主机密码、邮箱授权码
 *    都以 enc1: 密文存着，密钥一把对不上就永久解不开。
 * 2) 已经装好的站点如果 config.php 被覆盖或丢失，请从备份恢复原文件，不要新建一把新密钥。
 */

/*数据库配置*/
$dbconfig = array(
	'host' => 'localhost',
	'port' => 3306,
	'user' => '',
	'pwd'  => '',
	'dbname' => '',
);

/*加密密钥（64位hex），请勿泄露*/
$mnbt_secret_key = '';
