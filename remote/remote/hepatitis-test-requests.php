<?php
//this file is get the data from remote db
$data = json_decode(file_get_contents('php://input'), true);
require_once(dirname(__FILE__) . "/../../startup.php");



$labId = $data['labName'];

$general = new \Vlsm\Models\General($db);
$dataSyncInterval = $general->getGlobalConfig('data_sync_interval');
$dataSyncInterval = (isset($dataSyncInterval) && !empty($dataSyncInterval)) ? $dataSyncInterval : 30;

//get facility map id
$facilityMapQuery = "SELECT facility_id FROM vl_facility_map where vl_lab_id=" . $labId;
$fMapResult = $db->query($facilityMapQuery);
if (count($fMapResult) > 0) {
    $fMapResult = array_map('current', $fMapResult);
    $fMapResult = implode(",", $fMapResult);
} else {
    $fMapResult = "";
}

if (isset($fMapResult) && $fMapResult != '' && $fMapResult != null) {
    $condition = "(lab_id =" . $labId . " OR facility_id IN (" . $fMapResult . "))";
} else {
    $condition = "lab_id =" . $labId;
}

$hepatitisQuery = "SELECT * FROM form_hepatitis WHERE $condition 
          AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY) 
          AND data_sync=0";

$hepatitisRemoteResult = $db->rawQuery($hepatitisQuery);


$forms = array();
foreach ($hepatitisRemoteResult as $row) {
    $forms[] = $row['hepatitis_id'];
}

$hepatitisObj = new \Vlsm\Models\Hepatitis($db);
$comorbidities = $hepatitisObj->getComorbidityByHepatitisId($forms);
$risks = $hepatitisObj->getRiskFactorsByHepatitisId($forms);

$data = array();
$data['result'] = $hepatitisRemoteResult;
$data['risks'] = $risks;
$data['comorbidities'] = $comorbidities;


echo json_encode($data);
