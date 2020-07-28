<?php
ob_start();
session_start();
#require_once('../startup.php');  
include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/vendor/autoload.php');
include_once(APPLICATION_PATH.'/models/General.php');
$confFileName = base64_decode($_POST['machineName']);
$globalConfigQuery = "SELECT * from global_config";
$configResult = $db->query($globalConfigQuery);
$arr = array();
for($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}

$general = new General($db);




$type = $_POST['type'];

if($type == 'vl'){
    include_once(APPLICATION_PATH.DIRECTORY_SEPARATOR."import-configs".DIRECTORY_SEPARATOR."vl".DIRECTORY_SEPARATOR.$confFileName);
}else if($type == 'eid'){
    include_once(APPLICATION_PATH.DIRECTORY_SEPARATOR."import-configs".DIRECTORY_SEPARATOR."eid".DIRECTORY_SEPARATOR.$confFileName);
}else if($type == 'covid19'){
    include_once(APPLICATION_PATH.DIRECTORY_SEPARATOR."import-configs".DIRECTORY_SEPARATOR."covid-19".DIRECTORY_SEPARATOR.$confFileName);
}


