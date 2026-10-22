<?php 
$shell_get_file=' //获取指定目录的所有文件
#!/bin/bash  
# 指定目录  
dir=/www/wwwroot/  
# 遍历目录及其子目录中的所有文件  
files_array=()  
str=@@#@@  
for file in $(find "$dir" -type f); do  
   if [[ $file == *.html ]] || [[ $file == *.php ]]; then  
       files_array+=("$file$str")  
   fi  
done  
echo ${files_array[@]}  
';          //获取指定目录的所有文件bash(请勿修改！不要修改！禁止修改！)


$shell_list_user='
#!/bin/bash
function get_users
{
        users=`sudo cat /etc/pas[s]wd | tail -n+11 | cut -d: -f1 | cut -d_ -f2`
        echo $users
}
user_list=`get_users`
users=""
for user in $user_list
do
        users=${users}","${user}
done
echo $users
';          //获取Linux用户列表的bash(请勿修改！不要修改！禁止修改！)

$shell_phpset_bash='
#! /bin/sh

phpvs='.$phpvs.'
prefix=/www/server/php/${phpvs}
exec_prefix=${prefix}
sitenames=$3

php_fpm_BIN=${exec_prefix}/sbin/php-fpm
if [ "$3" ] ; then

php_fpm_CONF=/www/server/mnbt_php_conf/${sitenames}/${phpvs}/fpm.conf
php_fpm_PID=${prefix}/var/run/${sitenames}.pid

fi

if [ ! "$3" ] ; then
cmd=`ls -F /www/server/mnbt_php_conf | grep "/$" |  sed "s/\///"`
users=""
for sitename in $cmd
do
ds=`. /etc/rc.d/init.d/php-fpm-${phpvs} $1 $2 ${sitename}`
done
php_fpm_CONF=${prefix}/etc/php-fpm.conf
php_fpm_PID=${prefix}/var/run/php-fpm.pid
fi
php_opts="--fpm-config $php_fpm_CONF --pid $php_fpm_PID"


wait_for_pid () {
	try=0

	while test $try -lt 35 ; do

		case "$1" in
			"created")
			if [ -f "$2" ] ; then
				try=""
				break
			fi
			;;

			"removed")
			if [ ! -f "$2" ] ; then
				try=""
				break
			fi
			;;
		esac

		echo -n .
		try=`expr $try + 1`
		sleep 1

	done

}

case "$1" in
	start)
		echo -n "Starting php-fpm "

		$php_fpm_BIN --daemonize $php_opts

		if [ "$?" != 0 ] ; then
			echo " failed"
			exit 1
		fi

		wait_for_pid created $php_fpm_PID

		if [ -n "$try" ] ; then
			echo " failed"
			exit 1
		else
			echo " done"
		fi
	;;

	stop)
		echo -n "Gracefully shutting down php-fpm "

		if [ ! -r $php_fpm_PID ] ; then
			echo "warning, no pid file found - php-fpm is not running ?"
			exit 1
		fi

		kill -QUIT `cat $php_fpm_PID`

		wait_for_pid removed $php_fpm_PID

		if [ -n "$try" ] ; then
			echo " failed. Use force-quit"
			exit 1
		else
			echo " done"
		fi
	;;

	force-quit)
		echo -n "Terminating php-fpm "

		if [ ! -r $php_fpm_PID ] ; then
			echo "warning, no pid file found - php-fpm is not running ?"
			exit 1
		fi

		kill -TERM `cat $php_fpm_PID`

		wait_for_pid removed $php_fpm_PID

		if [ -n "$try" ] ; then
			echo " failed"
			exit 1
		else
			echo " done"
		fi
	;;

	restart)
		$0 stop
		$0 start
	;;

	reload)

		echo -n "Reload service php-fpm "

		if [ ! -r $php_fpm_PID ] ; then
			echo "warning, no pid file found - php-fpm is not running ?"
			exit 1
		fi

		kill -USR2 `cat $php_fpm_PID`

		echo " done"
	;;

	*)
		echo "Usage: $0 {start|stop|force-quit|restart|reload}"
		exit 1
	;;

esac

';          //PHP的启动/关闭脚本(请勿修改！不要修改！禁止修改！)

$nginx_conf='
	location ~ [^/]\.php(/|$)
	{
	    set $phpvsrc '.$phpvs.';
	    set $phpsockfiles /tmp/$server_name/$phpvsrc.sock;
		try_files $uri =404;
        if ( !-f /www/server/php/$phpvsrc/var/run/$server_name.pid) {
            set $phpsockfiles /tmp/php-cgi-$phpvsrc.sock;
        }
        fastcgi_pass  unix:$phpsockfiles;
		fastcgi_index index.php;
		include fastcgi.conf;
		include pathinfo.conf;
	}
';          //Nginx配置(请勿修改！不要修改！禁止修改！)
$fpm_conf='
listen.backlog = 8192
listen.allowed_clients = 127.0.0.1
listen.owner = www
listen.group = www
listen.mode = 0666
user = $pool
group = www
php_value[session.save_path] = "0;600;/tmp/"$pool
;php2shell缓解(CVE-2026-109618/CVE-2026-109619)：关闭会话上传进度通道，用admin_value租户无法经.user.ini/ini_set覆盖；仅缓解，升级到修复版本才是根治
php_admin_value[session.upload_progress.enabled] = 0
pm = dynamic
pm.max_children = 10
pm.start_servers = 0
pm.min_spare_servers = 0
pm.max_spare_servers = 4
;worker定期回收，限制残留请求状态在慢泄漏中跨请求累积；本池为单租户且max_children=10，500安全
pm.max_requests = 500
request_terminate_timeout = 100
request_slowlog_timeout = 30
slowlog = var/log/slow.log

';          //默认的主机配置文件，如果不懂则请勿修改！！！！！！！！！！！！！！！！！
//乱改改废了的处理方法：去官网下载最新版本然后在压缩包中找到此文件，然后再复制粘贴到这，然后保存，然后再将MNBT所有添加的宝塔的所有PHP版本全部拆卸重装，然后去MNBT后台->宝塔列表最后一个操作列，将所有宝塔全部执行一次[修复配置](正数第二个按钮)即可。
?>