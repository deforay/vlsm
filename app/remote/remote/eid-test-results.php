<?php
//this file receives the lab results and updates in the remote db

use App\Registries\AppRegistry;
use JsonMachine\Items;
use App\Services\ApiService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");

ini_set('memory_limit', -1);
set_time_limit(0);
ini_set('max_execution_time', 300000);
try {
    $db->beginTransaction();
    //$jsonResponse = $contentEncoding = $request->getHeaderLine('Content-Encoding');

    /** @var ApiService $apiService */
    $apiService = ContainerRegistry::get(ApiService::class);

    /** @var Laminas\Diactoros\ServerRequest $request */
    $request = AppRegistry::get('request');
    $jsonResponse = $apiService->getJsonFromRequest($request);

    /** @var DatabaseService $db */
    $db = ContainerRegistry::get(DatabaseService::class);

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    /** @var UsersService $usersService */
    $usersService = ContainerRegistry::get(UsersService::class);

    $sampleCodes = $facilityIds = [];
    $labId = null;

    $transactionId = $general->generateUUID();

    if (!empty($jsonResponse) && $jsonResponse != '[]' && MiscUtility::isJSON($jsonResponse)) {

        // Create an array with all column names set to null
        $emptyLabArray = $general->getTableFieldsAsArray('form_eid');

        $unwantedColumns = [
            'eid_id',
            'sample_package_id',
            'sample_package_code',
            //'last_modified_by',
            'request_created_by'
        ];
        $emptyLabArray = MiscUtility::removeFromAssociativeArray($emptyLabArray, $unwantedColumns);

        $resultData = [];
        $options = [
            'decoder' => new ExtJsonDecoder(true)
        ];
        $parsedData = Items::fromString($jsonResponse, $options);
        foreach ($parsedData as $name => $data) {
            if ($name === 'labId') {
                $labId = $data;
            } else if ($name === 'result') {
                $resultData = $data;
            }
        }

        $counter = 0;
        foreach ($resultData as $key => $resultRow) {
            $counter++;
            // Overwrite the values in $emptyLabArray with the values in $resultRow
            $lab = array_merge($emptyLabArray, array_intersect_key($resultRow, $emptyLabArray));

            if (isset($resultRow['approved_by_name']) && $resultRow['approved_by_name'] != '') {

                $lab['result_approved_by'] = $usersService->getOrCreateUser($resultRow['approved_by_name']);
                $lab['result_approved_datetime'] = DateUtility::getCurrentDateTime();
                // we dont need this now
                //unset($resultRow['approved_by_name']);
            }

            $lab['data_sync'] = 1; //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
            $lab['last_modified_datetime'] = DateUtility::getCurrentDateTime();


            if ($lab['result_status'] != SAMPLE_STATUS\ACCEPTED && $lab['result_status'] != SAMPLE_STATUS\REJECTED) {
                $keysToRemove = [
                    'result',
                    'is_sample_rejected',
                    'reason_for_sample_rejection'
                ];
                $lab = MiscUtility::removeFromAssociativeArray($lab, $keysToRemove);
            }

            $primaryKey = 'eid_id';
            $tableName = 'form_eid';
            try {
                // Checking if Remote Sample ID is set, if not set we will check if Sample ID is set
                $conditions = [];
                $params = [];

                if (!empty($lab['unique_id'])) {
                    $conditions[] = "unique_id = ?";
                    $params[] = $lab['unique_id'];
                }
                if (!empty($lab['remote_sample_code'])) {
                    $conditions[] = "remote_sample_code = ?";
                    $params[] = $lab['remote_sample_code'];
                }
                if (!empty($lab['sample_code'])) {
                    if (!empty($lab['lab_id'])) {
                        $conditions[] = "sample_code = ? AND lab_id = ?";
                        $params[] = $lab['sample_code'];
                        $params[] = $lab['lab_id'];
                    } elseif (!empty($lab['facility_id'])) {
                        $conditions[] = "sample_code = ? AND facility_id = ?";
                        $params[] = $lab['sample_code'];
                        $params[] = $lab['facility_id'];
                    }
                }
                $sResult = [];
                if (!empty($conditions)) {
                    $sQuery = "SELECT $primaryKey FROM $tableName WHERE " . implode(' OR ', $conditions);
                    $sResult = $db->rawQueryOne($sQuery, $params);
                }

                $formAttributes = $general->jsonToSetString(
                    $lab['form_attributes'],
                    'form_attributes'
                );
                $lab['form_attributes'] = !empty($formAttributes) ? $db->func($formAttributes) : null;

                if (!empty($sResult)) {
                    $db->where($primaryKey, $sResult[$primaryKey]);
                    $id = $db->update($tableName, $lab);
                } else {
                    $id = $db->insert($tableName, $lab);
                }
            } catch (Throwable $e) {
                if ($db->getLastErrno() > 0) {
                    error_log(__FILE__ . ":" . __LINE__ . ":" . $db->getLastErrno());
                    error_log(__FILE__ . ":" . __LINE__ . ":" . $db->getLastError());
                    error_log(__FILE__ . ":" . __LINE__ . ":" . $db->getLastQuery());
                }
                LoggerUtility::log('error', $e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage());
                continue;
            }

            if ($id === true && isset($lab['sample_code'])) {
                $sampleCodes[] = $lab['sample_code'];
                $facilityIds[] = $lab['facility_id'];
            }
        }
    }


    $payload = json_encode($sampleCodes);


    $general->addApiTracking($transactionId, 'vlsm-system', $counter, 'results', 'eid', $_SERVER['REQUEST_URI'], $jsonResponse, $payload, 'json', $labId);
    $general->updateResultSyncDateTime('eid', $facilityIds, $labId);

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
