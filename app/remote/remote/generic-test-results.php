<?php

use App\Services\DatabaseService;
use JsonMachine\Items;
use App\Services\ApiService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Services\CommonService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");

ini_set('memory_limit', -1);
set_time_limit(0);
ini_set('max_execution_time', 300000);

/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);


try {
    $db->beginTransaction();
    //this file receives the lab results and updates in the remote db
    //$jsonResponse = $contentEncoding = $request->getHeaderLine('Content-Encoding');

    /** @var ApiService $apiService */
    $apiService = ContainerRegistry::get(ApiService::class);

    /** @var Laminas\Diactoros\ServerRequest $request */
    $request = $GLOBALS['request'];
    $jsonResponse = $apiService->getJsonFromRequest($request);


    $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = ? AND table_name='form_generic'";
    $allColResult = $db->rawQuery($allColumns, [SYSTEM_CONFIG['database']['db']]);
    $oneDimensionalArray = array_map('current', $allColResult);

    $transactionId = $general->generateUUID();

    $sampleCodes = $facilityIds = [];
    $labId = null;
    if (!empty($jsonResponse) && $jsonResponse != '[]' && MiscUtility::isJSON($jsonResponse)) {


        $resultData = [];
        $testResultsData = [];
        $options = [
            'decoder' => new ExtJsonDecoder(true)
        ];
        $parsedData = Items::fromString($jsonResponse, $options);
        foreach ($parsedData as $name => $data) {
            if ($name === 'labId') {
                $labId = $data;
            } else if ($name === 'testResults') {
                $testResultsData = $data;
            } else if ($name === 'result') {
                $resultData = $data;
            }
        }

        $counter = 0;
        foreach ($resultData as $key => $resultRow) {

            $counter++;
            $lab = [];
            foreach ($oneDimensionalArray as $columnName) {
                if (isset($resultRow[$columnName])) {
                    $lab[$columnName] = $resultRow[$columnName];
                } else {
                    $lab[$columnName] = null;
                }
            }
            //remove unwan  ted columns
            $unwantedColumns = array(
                'sample_id',
                'sample_package_id',
                'sample_package_code',
                //'last_modified_by',
                'request_created_by',
                'result_printed_datetime'
            );
            foreach ($unwantedColumns as $removeColumn) {
                unset($lab[$removeColumn]);
            }


            if (isset($resultRow['approved_by_name']) && $resultRow['approved_by_name'] != '') {

                $lab['result_approved_by'] = $usersService->getOrCreateUser($resultRow['approved_by_name']);
                $lab['result_approved_datetime'] = DateUtility::getCurrentDateTime();
                // we dont need this now
                //unset($resultRow['approved_by_name']);
            }


            //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
            $lab['data_sync'] = 1;
            $lab['last_modified_datetime'] = DateUtility::getCurrentDateTime();

            // unset($lab['request_created_by']);
            // unset($lab['last_modified_by']);
            // unset($lab['request_created_datetime']);

            if ($lab['result_status'] != SAMPLE_STATUS\ACCEPTED && $lab['result_status'] != SAMPLE_STATUS\REJECTED) {
                unset($lab['result']);
                unset($lab['is_sample_rejected']);
                unset($lab['reason_for_sample_rejection']);
            }



            try {
                // Checking if Remote Sample ID is set, if not set we will check if Sample ID is set
                if (isset($lab['remote_sample_code']) && $lab['remote_sample_code'] != '') {
                    //error_log("INSIDE REMOTE");
                    $sQuery = "SELECT sample_id,sample_code,remote_sample_code,remote_sample_code_key
                                FROM form_generic WHERE remote_sample_code= ?";
                    $sResult = $db->rawQueryOne($sQuery, [$lab['remote_sample_code']]);
                } elseif (isset($lab['sample_code']) && $lab['sample_code'] != '') {
                    //error_log("INSIDE LOCAL");
                    $sQuery = "SELECT sample_id,sample_code,remote_sample_code,remote_sample_code_key
                                FROM form_generic WHERE sample_code=? AND facility_id = ?";
                    $sResult = $db->rawQueryOne($sQuery, [$lab['sample_code'], $lab['facility_id']]);
                }


                $formAttributes = $general->jsonToSetString(
                    $lab['form_attributes'],
                    'form_attributes'
                );
                $lab['form_attributes'] = !empty($formAttributes) ? $db->func($formAttributes) : null;
                if (!empty($sResult)) {
                    $db->where('sample_id', $sResult['sample_id']);
                    $id = $db->update('form_generic', $lab);
                    $sampleId = $sResult['sample_id'];
                } else {
                    $id = $db->insert('form_generic', $lab);
                    $sampleId = $db->getInsertId();
                }
            } catch (Exception $e) {
                error_log($db->getLastError());
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                continue;
            }

            if ($id === true && isset($lab['sample_code'])) {
                $sampleCodes[] = $lab['sample_code'];
                $facilityIds[] = $lab['facility_id'];
            }

            foreach ($testResultsData as $genId => $testResults) {
                $db = $db->where('generic_id', $sampleId);
                $db->delete("generic_test_results");
                foreach ($testResults as $testId => $test) {
                    $testResultValues = array(
                        "generic_id" => $sampleId,
                        "test_name" => $test['test_name'],
                        "facility_id" => $test['facility_id'],
                        "sample_tested_datetime" => $test['sample_tested_datetime'],
                        "testing_platform" => $test['testing_platform'],
                        "result" => $test['result'],
                        "updated_datetime" => DateUtility::getCurrentDateTime()
                    );
                    $_id[$testId] = $db->insert("generic_test_results", $testResultValues);
                }
            }
        }
    }

    $payload = json_encode($sampleCodes);

    $general->addApiTracking($transactionId, 'vlsm-system', $counter, 'results', 'generic-tests', $_SERVER['REQUEST_URI'], $jsonResponse, $payload, 'json', $labId);

    $general->updateResultSyncDateTime('generic', 'form_generic', $sampleCodes, $transactionId, $facilityIds, $labId);
    $db->commitTransaction();
} catch (Exception $e) {
    $db->rollbackTransaction();

    error_log($db->getLastError());
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    throw new SystemException($e->getMessage(), $e->getCode(), $e);
}

echo $payload;
