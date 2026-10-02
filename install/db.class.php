<?php

if(extension_loaded('mysqli')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    class DB {
        private static $link;
		public static function connect($db_host,$db_user,$db_pass,$db_name,$db_port){
			self::$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
			return self::$link;
		}
		public static function connect_errno(){
			return mysqli_connect_errno();
		}
		public static function connect_error(){
			return mysqli_connect_error();
		}
		public static function fetch($q){
			return mysqli_fetch_assoc($q);
		}
		public static function get_row($q){
			$result = mysqli_query(self::$link,$q);
			return mysqli_fetch_assoc($result);
		}
		public static function count($q){
			$result = mysqli_query(self::$link,$q);
			$count = mysqli_fetch_array($result);
			return $count[0];
		}
		public static function query($q){
			return mysqli_query(self::$link,$q);
		}
		public static function escape($str){
			return mysqli_real_escape_string(self::$link,$str);
		}
		public static function affected(){
			return mysqli_affected_rows(self::$link);
		}
		public static function errno(){
			return mysqli_errno(self::$link);
		}
		public static function error(){
			return mysqli_error(self::$link);
		}
		public static function close(){
			return  mysqli_close(self::$link);
		}
	}
} else {
	// PHP 7 起旧 mysql_* 扩展已移除，安装器同样只支持 mysqli
	die('服务器未启用 mysqli 扩展，无法运行安装程序');
}
?>