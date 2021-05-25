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

$vlQuery = "SELECT * FROM vl_request_form WHERE $condition 
          AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY) 
          AND data_sync=0";
//$vlQuery="SELECT * FROM vl_request_form WHERE $condition AND data_sync=0";
/* Remote Syn only package code matches */
if (!empty($data['pkg']) && !empty($data['module']) && $data['module'] == 'vl') {
  $vlQuery .= " AND sample_package_code like '" . $data['pkg'] . "%'";
}

$vlRemoteResult = $db->rawQuery($vlQuery);
echo json_encode($vlRemoteResult);
