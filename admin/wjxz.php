<?php
include("../MPHX/common.php");
@header('Content-Type: text/html; charset=UTF-8');
$egn=$_GET['gn'];
if($islogin==1){}else exit('{"code":"请登陆"}');
?>

<?php
$ifsz=$_GET['idh'];
if(isset($ifsz)){
$res=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$ifsz]);
$file=$res['cxwz'];//需要下载的文件
$file = iconv("utf-8","gbk//IGNORE",$file);
if(!file_exists($file)){//判断文件是否存在
    echo "文件不存在";
    exit();
}

$file_name=$res['name'].'.zip';
$file_name = iconv("utf-8","gbk//IGNORE",$file_name);
$file_size=filesize("$file");
ob_clean();
flush();
header("Content-Description: File Transfer");
header("Content-Type:application/force-download");
header("Content-Length: {$file_size}");
header("Content-Disposition:attachment; filename={$file_name}");
readfile("$file");
exit;
}elseif($egn=='cxfile'){
$file='../filecx/export_file.zip';//需要下载的文件
$file = iconv("utf-8","gbk//IGNORE",$file);
if(!file_exists($file)){//判断文件是否存在
    echo "导出的打包文件不存在！您可以重新导出！";
    exit();
}
$file_name='export_file'.mt_rand(10,100).'.zip';
$file_name= iconv("utf-8","gbk//IGNORE",$file_name);
$file_size=filesize("$file");
ob_clean();
flush();
header("Content-Description: File Transfer");
header("Content-Type:application/force-download");
header("Content-Length: {$file_size}");
header("Content-Disposition:attachment; filename={$file_name}");
readfile("$file");
//@unlink($file);
exit;
}
?>


<?php
if($_GET['ne']=='sw'){
$file_name='mnbt_swapidc.zip';
$file="./mnbt/mnbt.sw.zip";//需要下载的文件
}else{
// 魔方对接插件下载：优先从源码目录动态打包（mnbthost 虚拟主机 + mnbtdocker Docker），
// 保证下载到的始终是当前版本；ZipArchive 不可用或打包失败时回退旧静态包
$file_name='mnbt_mofang.zip';
$zip_path='../filecx/mnbt_mofang.zip';
$build_ok=false;
if(class_exists('ZipArchive')){
    $zip=new ZipArchive();
    if($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)===true){
        $src_dirs=['mnbthost'=>'../mf_modules/servers/mnbthost','mnbtdocker'=>'../mf_modules/servers/mnbtdocker'];
        $has_file=false;
        foreach($src_dirs as $top=>$dir){
            if(!is_dir($dir))continue;
            $files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach($files as $f){
                if(!$f->isFile())continue;
                $zip->addFile($f->getPathname(), $top.'/'.substr($f->getPathname(), strlen($dir)+1));
                $has_file=true;
            }
        }
        $build_ok=$has_file && $zip->close();
    }
}
if($build_ok && file_exists($zip_path)){
    $file=$zip_path;
    $file_name='mnbt_mofang.zip';
}else{
    $file="./mnbt/mnbt.mr.zip";//需要下载的文件（旧静态包兜底）
}
}
$file = iconv("utf-8","gbk//IGNORE",$file);
if(!file_exists($file)){//判断文件是否存在
    echo "文件不存在";
    exit();
}

$file_size=filesize("$file");
ob_clean();
flush();
header("Content-Description: File Transfer");
header("Content-Type:application/force-download");
header("Content-Length: {$file_size}");
header("Content-Disposition:attachment; filename={$file_name}");
readfile("$file");
exit;
?>