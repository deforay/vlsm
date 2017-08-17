<?php
session_start();

if(!isset($_SESSION['userId'])){
    header("location:../login.php");
}


if($_SESSION['roleId'] != 1){
    header("location:../index.php");
}


include('../includes/MysqliDb.php');


$filename = 'vlsm-bkp-' . date("dmYHis") .'-'. rand(). '.sql';

if(file_exists($MYSQLDUMP)){
 exec($MYSQLDUMP.' --user='.$USER.' --password="'.$PASSWORD.'" --host='.$HOST.' --port='.$PORT.' --databases '.$DBNAME.'  > '. $filename); 
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