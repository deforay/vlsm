<?php
ob_start();
#require_once('../../startup.php');


$vLModel = new \Vlsm\Models\Vl($db);

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
