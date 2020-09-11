<?php
#require_once('../../startup.php'); 

$general=new \Vlsm\Models\General($db);
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
