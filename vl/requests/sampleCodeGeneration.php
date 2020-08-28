<?php
ob_start();
#require_once('../../startup.php');
require_once(APPLICATION_PATH . '/includes/MysqliDb.php');
require_once(APPLICATION_PATH . '/models/Vl.php');

$vLModel = new Model_Vl($db);

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


echo $vLModel->generateVLSampleID($province, $sampleCollectionDate, $sampleFrom);
