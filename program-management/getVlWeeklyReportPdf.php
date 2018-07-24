<?php
include('../General.php');
$general=new General();
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
