<?php

use App\Services\ApiService;
use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
header('Content-Type: application/json');

try {
  $db->beginTransaction();

  /** @var ApiService $apiService */
  $apiService = ContainerRegistry::get(ApiService::class);

  /** @var Laminas\Diactoros\ServerRequest $request */
  $request = AppRegistry::get('request');
  $data = $apiService->getJsonFromRequest($request, true);


  $payload = [];

  $labId = $data['labName'] ?? $data['labId'] ?? null;

  if (empty($labId)) {
    throw new SystemException('Lab ID is missing in the request', 400);
  }

  $apiRequestId  = $apiService->getHeader($request, 'X-Request-ID');
  $transactionId = $apiRequestId ?? MiscUtility::generateULID();

  $dataSyncInterval = $general->getGlobalConfig('data_sync_interval') ?? 30;

  /** @var FacilitiesService $facilitiesService */
  $facilitiesService = ContainerRegistry::get(FacilitiesService::class);

  $fMapResult = $facilitiesService->getTestingLabFacilityMap($labId);

  if (!empty($fMapResult)) {
    $condition = "(lab_id =$labId OR facility_id IN ($fMapResult))";
  } else {
    $condition = "lab_id =$labId";
  }

  $sQuery = "SELECT * FROM form_eid WHERE $condition ";

  if (!empty($data['manifestCode'])) {
    $sQuery .= " AND sample_package_code like '" . $data['manifestCode'] . "'";
  } else {
    $sQuery .= " AND data_sync=0 AND last_modified_datetime > SUBDATE( '" . DateUtility::getCurrentDateTime() . "', INTERVAL $dataSyncInterval DAY)";
  }

  $removeKeys = [
    'sample_code',
    'sample_code_key',
    'sample_code_format',
    'sample_batch_id',
    'sample_received_at_lab_datetime',
    'eid_test_platform',
    'import_machine_name',
    'sample_tested_datetime',
    'is_sample_rejected',
    'lab_id',
    'result',
    'tested_by',
    'lab_tech_comments',
    'result_approved_by',
    'result_approved_datetime',
    'revised_by',
    'revised_on',
    'result_reviewed_by',
    'result_reviewed_datetime',
    'result_dispatched_datetime',
    'reason_for_changing',
    'result_status',
    'data_sync',
    'reason_for_sample_rejection',
    'rejection_on',
    'last_modified_by',
    'result_printed_datetime',
    'last_modified_datetime'
  ];

  [$rResult, $resultCount] = $db->getQueryResultAndCount($sQuery, returnGenerator: false);

  $sampleIds = $facilityIds = [];

  if ($resultCount > 0) {
    $sampleIds = array_column($rResult, 'eid_id');
    $facilityIds = array_column($rResult, 'facility_id');
    $payload = JsonUtility::encodeUtf8Json($rResult);
  } else {
    $payload = json_encode([]);
  }

  $general->addApiTracking($transactionId, 'vlsm-system', $resultCount, 'requests', 'eid', $_SERVER['REQUEST_URI'], JsonUtility::encodeUtf8Json($data), $payload, 'json', $labId);

  if (!empty($facilityIds)) {
    $general->updateTestRequestsSyncDateTime('eid', $facilityIds, $labId);
  }


  if (!empty($sampleIds)) {
    $updateData = [
      'data_sync' => 1
    ];
    $db->where('eid_id', $sampleIds, 'IN');
    $db->update('form_eid', $updateData);
  }


  $db->commitTransaction();
} catch (Throwable $e) {
  $db->rollbackTransaction();

  $payload = json_encode([]);

  if ($db->getLastError()) {
    LoggerUtility::logError($e->getFile() . ":" . $e->getLine() . ":" . $db->getLastErrno());
    LoggerUtility::logError($e->getFile() . ":" . $e->getLine() . ":" . $db->getLastError());
    LoggerUtility::logError($e->getFile() . ":" . $e->getLine() . ":" . $db->getLastQuery());
  }
  throw new SystemException($e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage(), $e->getCode(), $e);
}

echo ApiService::sendJsonResponse($payload, $request);
