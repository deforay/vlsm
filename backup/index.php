<?php
require_once('../startup.php');  
include_once(APPLICATION_PATH.'/includes/MysqliDb.php');


if(!isset($_SESSION['userId'])){
    header("location:/login.php");
}


if($_SESSION['roleId'] != 1){
    header("location:/index.php");
}





$filename = 'vlsm-bkp-' . date("dmYHis") .'-'. rand(). '.sql';

if(file_exists($systemConfig['mysqlDump'])){
 exec($systemConfig['mysqlDump'].' --user='.$systemConfig['dbUser'].' --password="'.$systemConfig['dbPassword'].'" --host='.$systemConfig['dbHost'].' --port='.$systemConfig['dbPort'].' --databases '.$systemConfig['dbName'].'  > '. $filename); 
}else{
 echo "mysqldump path needs to be configured correctly.";
 die;exit;
}




$file = (__DIR__).DIRECTORY_SEPARATOR.$filename;
$newfilePath = '../../backup'. DIRECTORY_SEPARATOR.$filename;
if(!is_dir('../../backup')){
 mkdir("../../backup");
 if (!copy($file, $newfilePath)) {
        echo "failed to copy $file...";
    }else{
        unlink($file);
    }
}else{
 if (!copy($file, $newfilePath)) {
        echo "failed to copy $file...";
    }else{
        unlink($file);
    }
}