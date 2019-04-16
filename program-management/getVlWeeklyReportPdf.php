<?php
include_once(APPLICATION_PATH.'/General.php');
$general=new General($db);
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
