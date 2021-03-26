<?php
//this file is get the data from remote db
$data = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php");



$labId = $data['labName'];


// //global config
// $cQuery="SELECT * FROM global_config";
// $cResult=$db->query($cQuery);
// $arr = array();
// // now we create an associative array so that we can easily create view variables
// for ($i = 0; $i < sizeof($cResult); $i++) {
//   $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
// }

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

//$vlQuery="SELECT * FROM form_covid19 WHERE $condition AND last_modified_datetime > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." DAY)";

//$vlQuery="SELECT * FROM form_covid19 WHERE $condition AND data_sync=0";

$covid19Query = "SELECT * FROM form_covid19 WHERE $condition 
          AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY) 
          AND data_sync=0";

$covid19RemoteResult = $db->rawQuery($covid19Query);


$data = array();

if (!empty($covid19RemoteResult) && count($covid19RemoteResult) > 0) {

  $forms = array();
  foreach ($covid19RemoteResult as $row) {
    $forms[] = $row['covid19_id'];
  }

  $covid19Obj = new \Vlsm\Models\Covid19($db);
  $symptoms = $covid19Obj->getCovid19SymptomsByFormId($forms);
  $comorbidities = $covid19Obj->getCovid19ComorbiditiesByFormId($forms);
  $testResults = $covid19Obj->getCovid19TestsByFormId($forms);

  $data = array();
  $data['result'] = $covid19RemoteResult;
  $data['symptoms'] = $symptoms;
  $data['comorbidities'] = $comorbidities;
  $data['testResults'] = $testResults;

  $updata = array(
    'data_sync' => 1
  );
  $db->where('covid19_id', $forms, 'IN');

  if (!$db->update('form_covid19', $updata))
    error_log('update failed: ' . $db->getLastError());
}
echo json_encode($data);
