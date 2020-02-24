<?php
//this file is get the data from remote db
$data = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php"); 
include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');

$labId = $data['labName'];

//$general=new General($db);
//global config
$cQuery="SELECT * FROM global_config";
$cResult=$db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
  $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}
//get facility map id
$facilityMapQuery = "SELECT facility_id FROM vl_facility_map where vl_lab_id=".$labId;
$fMapResult=$db->query($facilityMapQuery);
if(count($fMapResult)>0){
  $fMapResult = array_map('current', $fMapResult);
  $fMapResult = implode(",",$fMapResult);
}else{
  $fMapResult = "";
}

if(isset($fMapResult) && $fMapResult != '' && $fMapResult != null){
  $condition = "(lab_id =".$labId." OR facility_id IN (".$fMapResult."))";
}else{
  $condition = "lab_id =".$labId;
}

$vlQuery="SELECT * FROM vl_request_form WHERE $condition AND last_modified_datetime > SUBDATE( NOW(), INTERVAL 30 DAY) AND data_sync=0";

//$vlQuery="SELECT * FROM vl_request_form WHERE $condition AND data_sync=0";

$vlRemoteResult = $db->rawQuery($vlQuery);

echo json_encode($vlRemoteResult);
