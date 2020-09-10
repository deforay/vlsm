<?php
//this file is get the data from remote db
$data = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php");
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');

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

//$vlQuery="SELECT * FROM eid_form WHERE $condition AND last_modified_datetime > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." DAY)";

//$vlQuery="SELECT * FROM eid_form WHERE $condition AND data_sync=0";

$eidQuery = "SELECT * FROM eid_form WHERE $condition 
          AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY) 
          AND data_sync=0";

//error_log($eidQuery);

$eidRemoteResult = $db->rawQuery($eidQuery);

echo json_encode($eidRemoteResult);
