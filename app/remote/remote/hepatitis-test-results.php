<?php

use App\Services\ApiService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");

//this file receives the lab results and updates in the remote db
$jsonResponse = file_get_contents('php://input');

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);
$app = new ApiService();

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
        } else if ($name === 'result') {
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

            $lab['result_approved_by'] = $usersService->addUserIfNotExists($resultRow['approved_by_name']);
            $lab['result_approved_datetime'] =  DateUtility::getCurrentDateTime();
            // we dont need this now
            //unset($resultRow['approved_by_name']);
        }

        $lab['data_sync'] = 1; //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
        $lab['last_modified_datetime'] = DateUtility::getCurrentDateTime();

        // unset($lab['request_created_by']);
        // unset($lab['last_modified_by']);
        // unset($lab['request_created_datetime']);

        if ($lab['result_status'] != 7 && $lab['result_status'] != 4) {
            unset($lab['result']);
            unset($lab['is_sample_rejected']);
            unset($lab['reason_for_sample_rejection']);
        }

        // Checking if Remote Sample Code is set, if not set we will check if Sample Code is set
        if (isset($lab['remote_sample_code']) && $lab['remote_sample_code'] != '') {
            $sQuery = "SELECT hepatitis_id,sample_code,remote_sample_code,remote_sample_code_key FROM form_hepatitis WHERE remote_sample_code='" . $lab['remote_sample_code'] . "'";
        } else if (isset($lab['sample_code']) && !empty($lab['sample_code']) && !empty($lab['facility_id']) && !empty($lab['lab_id'])) {
            $sQuery = "SELECT hepatitis_id,sample_code,remote_sample_code,remote_sample_code_key FROM form_hepatitis WHERE sample_code='" . $lab['sample_code'] . "' AND facility_id = " . $lab['facility_id'];
        } else {
            $sampleCodes[] = $lab['sample_code'];
            $facilityIds[] = $lab['facility_id'];
            continue;
        }
        //$lab['source_of_request'] = 'vlsts';
        $sResult = $db->rawQuery($sQuery);
        if ($sResult) {
            $db = $db->where('hepatitis_id', $sResult[0]['hepatitis_id']);
            $db->update('form_hepatitis', $lab);
            $id = $sResult[0]['hepatitis_id'];
        } else {
            $db->insert('form_hepatitis', $lab);
            $id = $db->getInsertId();
        }

        if ($id > 0 && isset($lab['sample_code'])) {
            $sampleCodes[] = $lab['sample_code'];
            $facilityIds[] = $lab['facility_id'];
        }
    }
}


$payload = json_encode($sampleCodes);

$general->addApiTracking($transactionId, 'vlsm-system', $counter, 'results', 'hepatitis', $_SERVER['REQUEST_URI'], $jsonResponse, $payload, 'json', $labId);


$currentDateTime = DateUtility::getCurrentDateTime();
if (!empty($sampleCodes)) {
    $sql = 'UPDATE form_hepatitis SET data_sync = ?,
                form_attributes = JSON_SET(COALESCE(form_attributes, "{}"), "$.remoteResultsSync", ?, "$.resultSyncTransactionId", ?)
                WHERE sample_code IN ("' . implode('","', $sampleCodes) . '")';
    $db->rawQuery($sql, array(1, $currentDateTime, $transactionId));
}

if (!empty($facilityIds) && !empty(array_unique(array_filter($facilityIds)))) {
    $facilityIds = array_unique(array_filter($facilityIds));
    $sql = 'UPDATE facility_details 
                    SET facility_attributes = JSON_SET(COALESCE(facility_attributes, "{}"), "$.remoteResultsSync", ?, "$.hepatitisRemoteResultsSync", ?)
                    WHERE facility_id IN (' . implode(",", $facilityIds) . ')';
    $db->rawQuery($sql, array($currentDateTime, $currentDateTime));
}

$sql = 'UPDATE facility_details SET 
            facility_attributes = JSON_SET(COALESCE(facility_attributes, "{}"), "$.lastResultsSync", ?, "$.hepatitisLastResultsSync", ?) 
                WHERE facility_id = ?';
$db->rawQuery($sql, array($currentDateTime, $currentDateTime, $labId));

echo $payload;
