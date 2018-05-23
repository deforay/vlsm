<?php
include('../includes/General.php');
$general=new General();
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
