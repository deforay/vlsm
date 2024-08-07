<?php

use App\Services\ApiService;
use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\HepatitisService;
use App\Exceptions\SystemException;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");
header('Content-Type: application/json');


/** @var ApiService $apiService */
$apiService = ContainerRegistry::get(ApiService::class);

/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');

try {
    $db->beginTransaction();
    $transactionId = MiscUtility::generateULID();
    $data = $apiService->getJsonFromRequest($request, true);

    $labId = $data['labName'] ?? $data['labId'] ?? null;


    if (empty($labId)) {
        throw new SystemException('Lab ID is missing in the request', 400);
    }

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    $dataSyncInterval = $general->getGlobalConfig('data_sync_interval') ?? 30;


    $facilitiesService = ContainerRegistry::get(FacilitiesService::class);
    $fMapResult = $facilitiesService->getTestingLabFacilityMap($labId);

    if (!empty($fMapResult)) {
        $condition = "(lab_id =" . $labId . " OR facility_id IN (" . $fMapResult . "))";
    } else {
        $condition = "lab_id =" . $labId;
    }

    $sQuery = "SELECT * FROM form_hepatitis
                    WHERE $condition ";

    if (!empty($data['manifestCode'])) {
        $sQuery .= " AND sample_package_code like '" . $data['manifestCode'] . "'";
    } else {
        $sQuery .= " AND data_sync=0 AND last_modified_datetime > SUBDATE( '" . DateUtility::getCurrentDateTime() . "', INTERVAL $dataSyncInterval DAY)";
    }

    [$rResult, $resultCount] = $db->getQueryResultAndCount($sQuery, returnGenerator: false);

    $tableData = [];
    $sampleIds = $facilityIds = [];

    if ($resultCount > 0) {

        $sampleIds = array_column($rResult, 'hepatitis_id');
        $facilityIds = array_column($rResult, 'facility_id');

        /** @var HepatitisService $hepatitisService */
        $hepatitisService = ContainerRegistry::get(HepatitisService::class);
        foreach ($rResult as $r) {
            $tableData[$r['hepatitis_id']] = $r;
            $tableData[$r['hepatitis_id']]['data_from_comorbidities'] = $hepatitisService->getComorbidityByHepatitisId($r['hepatitis_id']);
            $tableData[$r['hepatitis_id']]['data_from_risks'] = $hepatitisService->getRiskFactorsByHepatitisId($r['hepatitis_id']);
        }
    }
    $payload = JsonUtility::encodeUtf8Json(array(
        'labId' => $labId,
        'result' => $tableData,
    ));

    $general->addApiTracking($transactionId, 'vlsm-system', $resultCount, 'requests', 'hepatitis', $_SERVER['REQUEST_URI'], JsonUtility::encodeUtf8Json($data), $payload, 'json', $labId);

    $general->updateTestRequestsSyncDateTime('hepatitis', $facilityIds, $labId);
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

echo $apiService->sendJsonResponse($payload);
