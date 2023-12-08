<?php
//this file receives the lab results and updates in the remote db

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
    $request = $GLOBALS['request'];
    $jsonResponse = $apiService->getJsonFromRequest($request);

    /** @var DatabaseService $db */
    $db = ContainerRegistry::get('db');

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    /** @var UsersService $usersService */
    $usersService = ContainerRegistry::get(UsersService::class);




    $sampleCodes = $facilityIds = [];
    $labId = null;

    $transactionId = $general->generateUUID();

    if (!empty($jsonResponse) && $jsonResponse != '[]' && MiscUtility::isJSON($jsonResponse)) {


        $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = ? AND table_name='form_eid'";
        $allColResult = $db->rawQuery($allColumns, [SYSTEM_CONFIG['database']['db']]);
        $oneDimensionalArray = array_map('current', $allColResult);

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
            foreach ($oneDimensionalArray as $result) {
                if (isset($resultRow[$result])) {
                    $lab[$result] = $resultRow[$result];
                } else {
                    $lab[$result] = null;
                }
            }
            //remove result value

            $removeKeys = array(
                'eid_id',
                'sample_package_id',
                'sample_package_code',
                //'last_modified_by',
                'request_created_by',
            );
            foreach ($removeKeys as $keys) {
                unset($lab[$keys]);
            }



            if (isset($resultRow['approved_by_name']) && $resultRow['approved_by_name'] != '') {

                $lab['result_approved_by'] = $usersService->getOrCreateUser($resultRow['approved_by_name']);
                $lab['result_approved_datetime'] = DateUtility::getCurrentDateTime();
                // we dont need this now
                //unset($resultRow['approved_by_name']);
            }

            $lab['data_sync'] = 1; //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
            $lab['last_modified_datetime'] = DateUtility::getCurrentDateTime();

            // unset($lab['request_created_by']);
            // unset($lab['last_modified_by']);
            // unset($lab['request_created_datetime']);

            if ($lab['result_status'] != SAMPLE_STATUS\ACCEPTED && $lab['result_status'] != SAMPLE_STATUS\REJECTED) {
                unset($lab['result']);
                unset($lab['is_sample_rejected']);
                unset($lab['reason_for_sample_rejection']);
            }

            $primaryKey = 'eid_id';
            $tableName = 'form_eid';
            try {
                // Checking if Remote Sample ID is set, if not set we will check if Sample ID is set
                if (!empty($lab['remote_sample_code'])) {
                    $sQuery = "SELECT $primaryKey FROM $tableName WHERE remote_sample_code= ?";
                    $sResult = $db->rawQueryOne($sQuery, [$lab['remote_sample_code']]);
                } elseif (!empty($lab['sample_code']) && !empty($lab['lab_id'])) {
                    $sQuery = "SELECT $primaryKey FROM $tableName WHERE sample_code=? AND lab_id = ?";
                    $sResult = $db->rawQueryOne($sQuery, [$lab['sample_code'], $lab['lab_id']]);
                } elseif (!empty($lab['sample_code']) && !empty($lab['facility_id'])) {
                    $sQuery = "SELECT $primaryKey FROM $tableName WHERE sample_code=? AND facility_id = ?";
                    $sResult = $db->rawQueryOne($sQuery, [$lab['sample_code'], $lab['facility_id']]);
                } elseif (!empty($lab['unique_id'])) {
                    $sQuery = "SELECT $primaryKey FROM $tableName WHERE unique_id=?";
                    $sResult = $db->rawQueryOne($sQuery, [$lab['unique_id']]);
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
                    //$db->onDuplicate(array_keys($lab), $primaryKey);
                    $id = $db->insert($tableName, $lab);
                }
            } catch (Exception | SystemException $e) {
                if ($db->getLastErrno() > 0) {
                    error_log($db->getLastErrno());
                    error_log($db->getLastError());
                    error_log($db->getLastQuery());
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


    $general->updateResultSyncDateTime('eid', 'form_eid', $sampleCodes, $transactionId, $facilityIds, $labId);
    $db->commitTransaction();
} catch (Exception | SystemException $e) {
    $db->rollbackTransaction();

    $payload = json_encode([]);

    if ($db->getLastErrno() > 0) {
        error_log($db->getLastErrno());
        error_log($db->getLastError());
        error_log($db->getLastQuery());
    }
    throw new SystemException($e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage(), $e->getCode(), $e);
}

echo $payload;
