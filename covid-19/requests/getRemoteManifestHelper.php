<?php
#require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new \Vlsm\Models\General($db);
$sampleData = array();
$sampleQuery = 'SELECT covid19_id FROM form_covid19 where sample_code IS NULL AND sample_package_code LIKE "'. $_POST['samplePackageCode'].'"';
$sampleResult = $db->query($sampleQuery);
foreach($sampleResult as $sampleRow){
    array_push($sampleData,$sampleRow['covid19_id']);
}
echo implode(',',$sampleData);