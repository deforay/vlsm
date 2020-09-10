<?php
#require_once('../../startup.php'); 
include_once(APPLICATION_PATH.'/models/General.php');
$general=new \Vlsm\Models\General($db);
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
