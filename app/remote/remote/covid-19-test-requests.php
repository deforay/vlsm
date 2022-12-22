<?php
require_once(dirname(__FILE__) . "/../../../startup.php");

header('Content-Type: application/json');

$general = new \Vlsm\Models\General();

$origData = $jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);


$payload = array();

$labId = $data['labName'] ?: $data['labId'] ?: null;

if (empty($labId)) {
  exit(0);
}


$transactionId = $general->generateUUID();

$dataSyncInterval = $general->getGlobalConfig('data_sync_interval');
$dataSyncInterval = (isset($dataSyncInterval) && !empty($dataSyncInterval)) ? $dataSyncInterval : 30;

$facilityDb = new \Vlsm\Models\Facilities();
$fMapResult = $facilityDb->getTestingLabFacilityMap($labId);

if (!empty($fMapResult)) {
  $condition = "(lab_id =" . $labId . " OR facility_id IN (" . $fMapResult . "))";
} else {
  $condition = "lab_id =" . $labId;
}

$covid19Query = "SELECT * FROM form_covid19 
                    WHERE $condition ";

if (!empty($data['manifestCode'])) {
  $covid19Query .= " AND data_sync=0 AND sample_package_code like '" . $data['manifestCode'] . "%'";
} else {
  $covid19Query .= " AND data_sync=0 AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY)";
}


$covid19RemoteResult = $db->rawQuery($covid19Query);

$data  = $sampleIds = $facilityIds = array();
$counter = 0;
if ($db->count > 0) {
  $counter = $db->count;
  $sampleIds = array_column($covid19RemoteResult, 'covid19_id');
  $facilityIds = array_column($covid19RemoteResult, 'facility_id');

  $covid19Obj = new \Vlsm\Models\Covid19();
  $symptoms = $covid19Obj->getCovid19SymptomsByFormId($sampleIds);
  $comorbidities = $covid19Obj->getCovid19ComorbiditiesByFormId($sampleIds);
  $testResults = $covid19Obj->getCovid19TestsByFormId($sampleIds);

  $data = array();
  $data['result'] = $covid19RemoteResult;
  $data['symptoms'] = $symptoms;
  $data['comorbidities'] = $comorbidities;
  $data['testResults'] = $testResults;


  $db->where('covid19_id', $sampleIds, 'IN')
    ->update('form_covid19', array('data_sync' => 1));
}


$payload = json_encode($data);

$general->addApiTracking($transactionId, 'vlsm-system', $counter, 'requests', 'covid19', null, $origData, $payload, 'json', $labId);

$currentDateTime = $general->getCurrentDateTime();
if (!empty($sampleIds)) {
  $sql = 'UPDATE form_covid19 SET data_sync = ?,
              form_attributes = JSON_SET(form_attributes, "$.remoteRequestsSync", ?)
              WHERE covid19_id IN (' . implode(",", $sampleIds) . ')';
  $db->rawQuery($sql, array(1, $currentDateTime));
}

if (!empty($facilityIds)) {
  $sql = 'UPDATE facility_details 
            SET facility_attributes = JSON_SET(facility_attributes, "$.remoteRequestsSync", ?) 
            WHERE facility_id IN (' . implode(",", $facilityIds) . ')';
  $db->rawQuery($sql, array($currentDateTime));
}

// Whether any data got synced or not, we will update sync datetime for the lab
$sql = 'UPDATE facility_details SET facility_attributes = JSON_SET(facility_attributes, "$.lastRequestsSync", ?) WHERE facility_id = ?';
$db->rawQuery($sql, array($currentDateTime, $labId));

echo $payload;
