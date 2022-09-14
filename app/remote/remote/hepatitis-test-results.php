<?php

require_once(dirname(__FILE__) . "/../../../startup.php");

//this file receives the lab results and updates in the remote db
$jsonResponse = file_get_contents('php://input');

$general = new \Vlsm\Models\General();
$usersModel = new \Vlsm\Models\Users();
$app = new \Vlsm\Models\App();

$transactionId = $general->generateUUID();

$sampleCode = array();
$labId = null;
if (!empty($jsonResponse) && $jsonResponse != '[]') {
    $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . SYSTEM_CONFIG['dbName'] . "' AND table_name='form_hepatitis'";
    $allColResult = $db->rawQuery($allColumns);
    $oneDimensionalArray = array_map('current', $allColResult);
    $counter = 0;


    $lab = array();
    $options = [
        'decoder' => new \JsonMachine\JsonDecoder\ExtJsonDecoder(true)
    ];
    $parsedData = \JsonMachine\Items::fromString($jsonResponse, $options);

    $parsedData = \JsonMachine\Items::fromString($jsonResponse, $options);
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
        $lab = array();
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

            $lab['result_approved_by'] = $usersModel->addUserIfNotExists($resultRow['approved_by_name']);
            $lab['result_approved_datetime'] =  $general->getCurrentDateTime();
            // we dont need this now
            //unset($resultRow['approved_by_name']);
        }

        $lab['data_sync'] = 1; //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
        $lab['last_modified_datetime'] = $general->getCurrentDateTime();

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
            $sampleCode[] = $lab['sample_code'];
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
            $sampleCode[] = $lab['sample_code'];
        }
    }
}


$payload = json_encode($sampleCode);

if ($counter > 0) {
    $general->addApiTracking($transactionId, 'vlsm-system', $counter, 'results', 'hepatitis', null, $jsonResponse, $payload, 'json', $labId);
}

echo $payload;
