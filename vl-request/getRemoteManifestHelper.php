<?php
require_once('../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$sampleData = Array();
$sampleQuery = 'SELECT vl_sample_id FROM vl_request_form where sample_code IS NULL AND sample_package_code LIKE "'. $_POST['samplePackageCode'].'"';
$sampleResult = $db->query($sampleQuery);
foreach($sampleResult as $sampleRow){
    array_push($sampleData,$sampleRow['vl_sample_id']);
}
echo implode(',',$sampleData);