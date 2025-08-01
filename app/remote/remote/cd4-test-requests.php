<?php

use App\Services\ApiService;
use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");

header('Content-Type: application/json');

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var ApiService $apiService */
$apiService = ContainerRegistry::get(ApiService::class);

/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$data = $apiService->getJsonFromRequest($request, true);


$payload = [];
$dataSyncInterval = $general->getGlobalConfig('data_sync_interval') ?? 30;

try {
  $db->beginTransaction();

  $apiRequestId  = $apiService->getHeader($request, 'X-Request-ID');
  $transactionId = $apiRequestId ?? MiscUtility::generateULID();

  $labId = $data['labName'] ?? $data['labId'] ?? null;

  if (empty($labId)) {
    throw new SystemException('Lab ID is missing in the request', 400);
  }

  $facilitiesService = ContainerRegistry::get(FacilitiesService::class);
  $fMapResult = $facilitiesService->getTestingLabFacilityMap($labId);

  if (!empty($fMapResult)) {
    $condition = "(lab_id = $labId OR facility_id IN ($fMapResult))";
  } else {
    $condition = "lab_id = $labId";
  }

  // oldName => newName for existing columns
  $aliasColumns = [
    'sample_type' => 'specimen_type',
    //'patient_art_no' => 'patient_id'
  ];

  // columnName => constantValue for non-existent columns
  $constantColumns = [
    'sample_code_title' => "'auto'"
  ];

  // Start with selecting all columns
  $columnSelection = "*";

  // Add each new column with its alias for existing columns
  foreach ($aliasColumns as $oldName => $newName) {
    $columnSelection .= ", $newName AS $oldName";
  }

  // Add constant columns
  foreach ($constantColumns as $columnName => $constantValue) {
    $columnSelection .= ", $constantValue AS $columnName";
  }

  // Construct the final SQL query
  $sQuery = "SELECT $columnSelection FROM form_cd4 WHERE $condition";

  if (!empty($data['manifestCode'])) {
    $sQuery .= " AND sample_package_code like '{$data['manifestCode']}'";
  } else {
    $sQuery .= " AND data_sync=0 AND last_modified_datetime >= SUBDATE( '" . DateUtility::getCurrentDateTime() . "', INTERVAL $dataSyncInterval DAY)";
  }

  [$rResult, $resultCount] = $db->getDataAndCount($sQuery, returnGenerator: false);

  $sampleIds = $facilityIds = [];
  if ($resultCount > 0) {
    $sampleIds = array_column($rResult, 'cd4_id');
    $facilityIds = array_column($rResult, 'facility_id');
    $payload = JsonUtility::encodeUtf8Json($rResult);
  } else {
    $payload = json_encode([]);
  }

  $general->addApiTracking($transactionId, 'vlsm-system', $resultCount, 'requests', 'cd4', $_SERVER['REQUEST_URI'], JsonUtility::encodeUtf8Json($data), $payload, 'json', $labId);

  if (!empty($facilityIds)) {
    $general->updateTestRequestsSyncDateTime('cd4', $facilityIds, $labId);
  }

  if (!empty($sampleIds)) {
    $updateData = [
      'data_sync' => 1
    ];
    $db->where('cd4_id', $sampleIds, 'IN');
    $db->update('form_cd4', $updateData);
  }

  $db->commitTransaction();
} catch (Throwable $e) {
  $db->rollbackTransaction();

  $payload = json_encode([]);

  if ($db->getLastErrno() > 0) {
    error_log(__FILE__ . ":" . __LINE__ . ":" . $db->getLastErrno());
    error_log(__FILE__ . ":" . __LINE__ . ":" . $db->getLastError());
    error_log(__FILE__ . ":" . __LINE__ . ":" . $db->getLastQuery());
  }
  throw new SystemException($e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage(), $e->getCode(), $e);
}

echo ApiService::generateJsonResponse($payload, $request);
