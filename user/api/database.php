<?php
require_once __DIR__ . '/../../MPHX/database_backup.function.php';

if($egn == "databasedel")
{
    include("../class.php");
    $id = $_POST['id'];
    $bt_api = new bt_api($btipe,$btkeye);
	$r_data = $bt_api->DatabaseDelete($id);
	logjl($yhc['user'],'数据库备份','删除了ID为'.$id.'的数据库备份',($r_data['msg']=='删除成功'?'删除成功':'删除失败'),$DB);
	if($r_data['msg'] == "删除成功")
	{
	    $user = $yhc['user'];
        $backup_data = $DB->get_row_prepare("select * from MN_zj where user=?", [$user]);
        $backup_max = json_decode($backup_data['backup'],true)["max"];
        $backup_dq = json_decode($backup_data['backup'],true)["dq"];
        if($backup_dq<=$backup_max && $backup_dq>0)
        {
            $backup_cz_array = json_decode($backup_data['backup'],true);
	        $backup_cz_array['dq'] = $backup_dq - 1;
	        $backup_cz_json = json_encode($backup_cz_array);
	        $DB->query_prepare("UPDATE `MN_zj` SET `backup` = ? WHERE `user` = ?", [$backup_cz_json, $user]);
	        json_exit($r_data['msg']);
        }
	    json_exit($r_data['msg']);
	}
	else
	{
	    exit('{"code":"宝塔那边出现了一点小问题请联系开发人员判断错误"}');
	}
	return;
}
if($egn == "databaseadd")
{
    include("../class.php");
    $id = daddslashes($_POST['id']);
    $user = $yhc['user'];
    $backup_data = $DB->get_row_prepare("select * from MN_zj where user=?", [$user]);
    $backup_max = json_decode($backup_data['backup'],true)["max"];
    $backup_qd = json_decode($backup_data['backup'],true)["dq"];
    if($backup_qd>=$backup_max)
    {
        exit('{"code":"你的备份次数用完了"}');
    }
    else
    {
        $bt_api = new bt_api($btipe,$btkeye);
	    $r_data = $bt_api->Databaseadd($id);
	    logjl($yhc['user'],'数据库备份','备份了ID为'.$id.'的数据库',($r_data['msg']=='备份成功!'?'备份成功':'备份失败'),$DB);
	    if($r_data['msg'] == "备份成功!")
	    {
	        $backup_cz_array = json_decode($backup_data['backup'],true);
	        $backup_cz_array['dq'] = $backup_qd + 1;
	        $backup_cz_json = json_encode($backup_cz_array);
	        $DB->query_prepare("UPDATE `MN_zj` SET `backup` = ? WHERE `user` = ?", [$backup_cz_json, $user]);
	        json_exit($r_data['msg']);
	    }
	    else
	    {
	        exit('{"code":"宝塔那边出现了一点小问题请联系开发人员判断错误"}');
	    }
    }
	return;
}
if($egn == "databasedownload")
{
    include("../class.php");
    $filename = isset($_POST['filename']) ? trim((string)$_POST['filename']) : '';
    if($filename === '')
    {
        json_exit_error('备份文件不能为空');
    }
    $bt_api = new bt_api($btipe,$btkeye);
    $backup_list = $bt_api->Databasebackuplist($yhc['hxd']);
    $backup_item = database_backup_find_item(database_backup_list_data($backup_list), $filename);
    if(!$backup_item)
    {
        json_exit_error('备份文件不存在或无权限下载');
    }
    [$backup_path, $backup_name] = database_backup_split_file($backup_item['filename']);
    if($backup_path === '' || $backup_name === '')
    {
        json_exit_error('备份文件路径异常');
    }
    $download_data = $bt_api->wailkq($backup_path, $backup_name);
    $token = database_backup_extract_download_token($download_data);
    if($token === '')
    {
        $message = isset($download_data['msg']) && is_scalar($download_data['msg']) ? (string)$download_data['msg'] : '获取下载链接失败';
        json_exit_error($message);
    }
    $url = database_backup_build_download_url($btipe, $token);
    logjl($yhc['user'],'数据库备份','下载数据库备份'.$backup_item['filename'],'获取下载链接成功',$DB);
    json_exit_success('获取下载链接成功', ['url' => $url]);
	return;
}
if($egn == "databaserestore")
{
    include("../class.php");
    $user = daddslashes($_POST['user']);
    $filename = daddslashes($_POST['filename']);
    $bt_api = new bt_api($btipe,$btkeye);
	$r_data = $bt_api->Databaserestore($filename,$user);
	logjl($yhc['user'],'数据库备份','恢复了数据库备份'.$filename,($r_data['msg']=='备份成功!'?'恢复成功':'恢复失败'),$DB);
	json_exit($r_data['msg']);
	return;
}
if($egn == "databaseaq1")
{
    include("../class.php");
    $dataAccess = daddslashes($_POST['dataAccess']);
    $bt_api = new bt_api($btipe,$btkeye);
	$r_data = $bt_api->SetDatabaseAccess($yhc['sqluser'],$dataAccess);
	json_exit($r_data['msg']);
	return;
}
if($egn == "Delalldatabase")
{
    $name = $yhc['sqldz'];
	$mysqluser = $yhc['sqluser'];
	$mysqpassword = mnbt_secret_decrypt($yhc['sqlpass'] ?? '');
	$mysqldb = $mysqluser;
	$conn = mysqli_connect("localhost",$mysqluser,$mysqpassword,$mysqldb);
	if (!$conn) {
        exit('{"code":"数据库连接失败请联系开发人员"}');
	}
	else
	{
	    $data = mysqli_query($conn,"SHOW TABLES");
	    while($row = mysqli_fetch_array($data))
	    {
	        $table = $row[0];
	        mysqli_query($conn,"DROP TABLE IF EXISTS $table");
	    }
	    json_exit('删除成功');
	}
	logjl($yhc['user'],'清空数据库','清空了所有数据库表','清空成功',$DB);
	return;
}
