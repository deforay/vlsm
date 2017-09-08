<?php
include('../includes/General.php');
$general=new Deforay_Commons_General();
$reportFilename = '';
include('generateVlWeeklyReportPdf.php');
echo $reportFilename;
