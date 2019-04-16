<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
//include_once('../startup.php'); include_once(APPLICATION_PATH.'/header.php');
include ('../vendor/autoload.php');
include_once(APPLICATION_PATH.'/General.php');
$confFileName = base64_decode($_POST['machineName']);
$globalConfigQuery = "SELECT * from global_config";
$configResult = $db->query($globalConfigQuery);
$arr = array();
for($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}

$general = new General($db);

include("../import-configs".DIRECTORY_SEPARATOR.$confFileName);
