<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH.'/models/Eid.php');
include_once(APPLICATION_PATH.'/models/General.php');
$eidModel = new Model_Eid($db);
echo $eidModel->generateEIDSampleCode($_POST['pName'], $_POST['sDate']);