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


$dataSyncInterval = $general->getGlobalConfig('data_sync_interval');
$dataSyncInterval = !empty($dataSyncInterval) ? $dataSyncInterval : 30;
$app = new \Vlsm\Models\App();

$counter = 0;

$facilityDb = new \Vlsm\Models\Facilities();
$fMapResult = $facilityDb->getTestingLabFacilityMap($labId);

if (!empty($fMapResult)) {
  $condition = "(lab_id =" . $labId . " OR facility_id IN (" . $fMapResult . "))";
} else {
  $condition = "lab_id =" . $labId;
}

$vlQuery = "SELECT * FROM form_vl 
                    WHERE $condition ";

if (!empty($data['manifestCode'])) {
  $vlQuery .= " AND data_sync=0 AND sample_package_code like '" . $data['manifestCode'] . "%'";
} else {
  $vlQuery .= " AND data_sync=0 AND last_modified_datetime >= SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY)";
}

$vlRemoteResult = $db->rawQuery($vlQuery);

if ($db->count > 0) {
  
  $counter = $db->count;

  $sampleIds = array_column($vlRemoteResult, 'vl_sample_id');
  $db->where('vl_sample_id', $sampleIds, 'IN')
    ->update('form_vl', array('data_sync' => 1));

  $payload = json_encode($vlRemoteResult);
} else {
  $payload = json_encode([]);
}


$general->addApiTracking('vlsm-system', $counter, 'requests', 'vl', null, $origData, $payload, 'json', $labId);



echo $payload;
