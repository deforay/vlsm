<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
//include('../header.php');
include ('../includes/PHPExcel.php');
include('../General.php');
$confFileName=base64_decode($_POST['machineName']);


$globalConfigQuery  = "SELECT * from global_config";
$configResult = $db->query($globalConfigQuery);
$arr          = array();
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}

$general = new Deforay_Commons_General();




include("../import-configs".DIRECTORY_SEPARATOR.$confFileName);
