<?php
//this file is get the data from remote db
$data = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php");

$labId = $data['labName'];

if (empty($labId)) {
  exit(0);
}

$general = new \Vlsm\Models\General($db);
$dataSyncInterval = $general->getGlobalConfig('data_sync_interval');
$dataSyncInterval = !empty($dataSyncInterval) ? $dataSyncInterval : 30;

//get facility map id
$facilityMapQuery = "SELECT facility_id FROM vl_facility_map where vl_lab_id= ?";
$fMapResult = $db->rawQuery($facilityMapQuery, $labId);
if (count($fMapResult) > 0) {
  $fMapResult = array_map('current', $fMapResult);
  $fMapResult = implode(",", $fMapResult);
} else {
  $fMapResult = null;
}

if (!empty($fMapResult)) {
  $condition = "(lab_id =" . $labId . " OR facility_id IN (" . $fMapResult . "))";
} else {
  $condition = "lab_id =" . $labId;
}

$vlQuery = "SELECT * FROM vl_request_form 
                    WHERE $condition ";

if (!empty($data['manifestCode'])) {
  $vlQuery .= " AND sample_package_code like '" . $data['manifestCode'] . "%'";
} else {
  $vlQuery .= " AND data_sync=0 AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY)";
}


$vlRemoteResult = $db->rawQuery($vlQuery);
echo json_encode($vlRemoteResult);
