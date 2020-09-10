<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH.'/models/Covid19.php');
include_once(APPLICATION_PATH.'/models/General.php');
$eidModel = new \Vlsm\Models\Covid19($db);



$sampleCollectionDate = $province = '';

if (isset($_POST['provinceCode'])) {
  $province = $_POST['provinceCode'];
} else if (isset($_POST['pName'])) {
  $province = $_POST['pName'];
}

if (isset($_POST['sampleCollectionDate'])) {
  $sampleCollectionDate = $_POST['sampleCollectionDate'];
} else if (isset($_POST['sDate'])) {
  $sampleCollectionDate = $_POST['sDate'];
}

if (isset($_POST['sampleFrom'])) {
  $sampleFrom = $_POST['sampleFrom'];
} else {
  $sampleFrom = '';
}


echo $eidModel->generateCovid19SampleCode($province, $sampleCollectionDate,$sampleFrom);