<?php

use JsonMachine\Items;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");

ini_set('memory_limit', '1G');
set_time_limit(30000);
ini_set('max_execution_time', 20000);
set_time_limit(30000);

try {
    $db->startTransaction();
    //this file receives the lab results and updates in the remote db
    $jsonResponse = file_get_contents('php://input');

    /** @var MysqliDb $db */
    $db = ContainerRegistry::get('db');

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    /** @var UsersService $usersService */
    $usersService = ContainerRegistry::get(UsersService::class);

    // /** @var ApiService $app */
    // $app = \App\Registries\ContainerRegistry::get(ApiService::class);


    $transactionId = $general->generateUUID();

    $sampleCodes = $facilityIds = [];
    $labId = null;
    if (!empty($jsonResponse) && $jsonResponse != '[]') {

        $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = ? AND table_name='form_hepatitis'";
        $allColResult = $db->rawQuery($allColumns, [SYSTEM_CONFIG['database']['db']]);

        $oneDimensionalArray = array_map('current', $allColResult);
        $counter = 0;


        $lab = [];
        $options = [
            'decoder' => new ExtJsonDecoder(true)
        ];
        $parsedData = Items::fromString($jsonResponse, $options);

        $parsedData = Items::fromString($jsonResponse, $options);
        foreach ($parsedData as $name => $data) {
            if ($name === 'labId') {
                $labId = $data;
            } elseif ($name === 'result') {
                $resultData = $data;
            }
        }

        $counter = 0;
        foreach ($resultData as $key => $resultRow) {
            $counter++;
            $lab = [];
            foreach ($oneDimensionalArray as $result) {
                if (isset($resultRow[$result])) {
                    $lab[$result] = $resultRow[$result];
                } else {
                    $lab[$result] = null;
                }
            }

            //remove fields that we DO NOT NEED here
            $removeKeys = array(
                'hepatitis_id',
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

            try {
                // Checking if Remote Sample ID is set, if not set we will check if Sample ID is set
                if (isset($lab['remote_sample_code']) && $lab['remote_sample_code'] != '') {
                    $sQuery = "SELECT hepatitis_id,sample_code,remote_sample_code,remote_sample_code_key
                            FROM form_hepatitis WHERE remote_sample_code=?";
                    $sResult = $db->rawQuery($sQuery, [$lab['remote_sample_code']]);
                } elseif (!empty($lab['sample_code']) && !empty($lab['facility_id']) && !empty($lab['lab_id'])) {
                    $sQuery = "SELECT hepatitis_id,sample_code,remote_sample_code,remote_sample_code_key
                            FROM form_hepatitis WHERE sample_code=? AND facility_id = ?";
                    $sResult = $db->rawQuery($sQuery, [$lab['sample_code'], $lab['facility_id']]);
                } else {
                    $sampleCodes[] = $lab['sample_code'];
                    $facilityIds[] = $lab['facility_id'];
                    continue;
                }

                if (!empty($sResult)) {
                    $formAttributes = $general->jsonToSetString(
                        $lab['form_attributes'],
                        'form_attributes'
                    );
                    $lab['form_attributes'] = !empty($formAttributes) ? $db->func($formAttributes) : null;
                    $db = $db->where('hepatitis_id', $sResult[0]['hepatitis_id']);
                    $db->update('form_hepatitis', $lab);
                    $id = $sResult[0]['hepatitis_id'];
                } else {
                    $formAttributes = $general->jsonToSetString(
                        $lab['form_attributes'],
                        'form_attributes'
                    );
                    $lab['form_attributes'] = !empty($formAttributes) ? $db->func($formAttributes) : null;
                    $db->insert('form_hepatitis', $lab);
                    $id = $db->getInsertId();
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
        }
    }


    $payload = json_encode($sampleCodes);

    $general->addApiTracking($transactionId, 'vlsm-system', $counter, 'results', 'hepatitis', $_SERVER['REQUEST_URI'], $jsonResponse, $payload, 'json', $labId);

    $general->updateResultSyncDateTime('hepatitis', 'form_hepatitis', $sampleCodes, $transactionId, $facilityIds, $labId);

    $db->commit();
} catch (Exception $e) {
    $db->rollback();

    error_log($db->getLastError());
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    throw new SystemException($e->getMessage(), $e->getCode(), $e);
}

echo $payload;
