<?php
include('../General.php');
$general=new General($db);
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
