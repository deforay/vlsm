<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
//include_once(APPLICATION_PATH.'/models/General.php');
$general = new \Vlsm\Models\General($db);

$module = $_POST['module'];

if($module == 'vl'){
    include_once('process-vl.php');
}else if($module == 'eid'){
    include_once('process-eid.php');
}else if($module == 'covid19'){
    include_once('process-covid-19.php');
}
