<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
echo $general->generateEIDSampleCode($_POST['pName'], $_POST['sDate']);