<?php
//this file is get the value from remote and update in lab db

require_once(dirname(__FILE__) . "/../../startup.php");
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');


$general = new General($db);
if (!isset($systemConfig['remoteURL']) || $systemConfig['remoteURL'] == '') {
    echo "Please check your Remote URL";
    die;
}

$systemConfig['remoteURL'] = rtrim($systemConfig['remoteURL'], "/");

//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
//global config
$cQuery = "SELECT * FROM global_config";
$cResult = $db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
    $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}
//get remote data
if (trim($sarr['lab_name']) == '') {
    $sarr['lab_name'] = "''";
}


// VIRAL LOAD REQUESTS

$url = $systemConfig['remoteURL'] . '/remote/remote/getRequests.php';
$data = array(
    'labName' => $sarr['lab_name'],
    "Key" => "vlsm-lab-data--",
);
//open connection
$ch = curl_init($url);
$json_data = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_data)
    )
);
// execute post
$curl_response = curl_exec($ch);

//close connection
curl_close($ch);
$apiResult = json_decode($curl_response, true);

/*
 ****************************************************************
  VIRAL LOAD TEST REQUESTS
 ****************************************************************
 */

$request = array();
$remoteSampleCodeList = array();
if (count($apiResult) > 0) {
    $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . $systemConfig['dbName'] . "' AND table_name='vl_request_form'";
    $allColResult = $db->rawQuery($allColumns);
    $columnList = array_map('current', $allColResult);
    foreach ($apiResult as $key => $remoteData) {
        foreach ($columnList as $colName) {
            if (isset($remoteData[$colName])) {
                $request[$colName] = $remoteData[$colName];
            } else {
                $request[$colName] = null;
            }
        }
        $unwantedKeys = array(
            'vl_sample_id',
            'sample_batch_id',
            'result_value_log',
            'result_value_absolute',
            'result_value_absolute_decimal',
            'result_value_text',
            'result',
            'sample_tested_datetime',
            'sample_received_at_vl_lab_datetime',
            'result_dispatched_datetime',
            'is_sample_rejected',
            'reason_for_sample_rejection',
            'result_approved_by',
            'request_created_datetime',
            'request_created_by',
            'last_modified_by',
            'data_sync'
        );
        foreach ($unwantedKeys as $removeKey) {
            unset($request[$removeKey]);
        }

        $remoteSampleCodeList[] = $request['remote_sample_code'];
        $request['last_modified_datetime'] = $general->getDateTime();

        //check wheather sample code empty or not
        // if ($request['sample_code'] != '' && $request['sample_code'] != 0 && $request['sample_code'] != null) {
        //     $sQuery = "SELECT vl_sample_id FROM vl_request_form WHERE sample_code='" . $request['sample_code'] . "'";
        //     $sResult = $db->rawQuery($sQuery);
        //     $db = $db->where('vl_sample_id', $sResult[0]['vl_sample_id']);
        //     $id = $db->update('vl_request_form', $request);
        // } else {
        //check exist remote
        $exsvlQuery = "SELECT vl_sample_id,sample_code FROM vl_request_form AS vl WHERE remote_sample_code='" . $request['remote_sample_code'] . "'";
        $exsvlResult = $db->query($exsvlQuery);
        if ($exsvlResult) {

            $dataToUpdate = array();

            $dataToUpdate['sample_package_code'] = $request['sample_package_code'];
            $dataToUpdate['sample_package_id'] = $request['sample_package_id'];

            $db = $db->where('vl_sample_id', $exsvlResult[0]['vl_sample_id']);
            $id = $db->update('vl_request_form', $dataToUpdate);
        } else {
            if ($request['sample_collection_date'] != '' && $request['sample_collection_date'] != null && $request['sample_collection_date'] != '0000-00-00 00:00:00') {
                $request['request_created_by'] = 0;
                $request['last_modified_by'] = 0;
                $request['request_created_datetime'] = $general->getDateTime();
                //column data_sync value is 1 equal to data_sync done.value 0 is not done.
                $request['data_sync'] = 0; 
                $id = $db->insert('vl_request_form', $request);
            }
        }
        //}
    }
}

/* 
  ****************************************************************
  *  EID TEST REQUESTS 
  ****************************************************************
  */

$request = array();
//$remoteSampleCodeList = array();
if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {
    $url = $systemConfig['remoteURL'] . '/remote/remote/eid-test-requests.php';
    $data = array(
        'labName' => $sarr['lab_name'],
        "Key" => "vlsm-lab-data--",
    );
    //open connection
    $ch = curl_init($url);
    $json_data = json_encode($data);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data)
        )
    );
    // execute post
    $curl_response = curl_exec($ch);

    //close connection
    curl_close($ch);
    $apiResult = json_decode($curl_response, true);

    if (count($apiResult) > 0) {
        $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . $systemConfig['dbName'] . "' AND table_name='eid_form'";
        $allColResult = $db->rawQuery($allColumns);
        $columnList = array_map('current', $allColResult);
        foreach ($apiResult as $key => $remoteData) {
            foreach ($columnList as $colName) {
                if (isset($remoteData[$colName])) {
                    $request[$colName] = $remoteData[$colName];
                } else {
                    $request[$colName] = null;
                }
            }
            $removeKeys = array(
                'eid_id',
                'sample_batch_id',
                'result',
                'sample_tested_datetime',
                'sample_received_at_vl_lab_datetime',
                'result_dispatched_datetime',
                'is_sample_rejected',
                'reason_for_sample_rejection',
                'result_approved_by',
                'request_created_by',
                'last_modified_by',
                'request_created_datetime',
                'data_sync'
            );
            foreach ($removeKeys as $keys) {
                unset($request[$keys]);
            }

            $remoteSampleCodeList[] = $request['remote_sample_code'];
            $request['last_modified_datetime'] = $general->getDateTime();

            //check whether sample code empty or not
            // if ($request['sample_code'] != '' && $request['sample_code'] != 0 && $request['sample_code'] != null) {
            //     $sQuery = "SELECT eid_id FROM eid_form WHERE sample_code='" . $request['sample_code'] . "'";
            //     $sResult = $db->rawQuery($sQuery);
            //     $db = $db->where('eid_id', $sResult[0]['eid_id']);
            //     $id = $db->update('eid_form', $request);
            // } else {
            //check exist remote
            $exsvlQuery = "SELECT eid_id,sample_code FROM eid_form AS vl WHERE remote_sample_code='" . $request['remote_sample_code'] . "'";
            $exsvlResult = $db->query($exsvlQuery);
            if ($exsvlResult) {

                $dataToUpdate = array();
                $dataToUpdate['sample_package_code'] = $request['sample_package_code'];
                $dataToUpdate['sample_package_id'] = $request['sample_package_id'];
                
                $db = $db->where('eid_id', $exsvlResult[0]['eid_id']);
                $id = $db->update('eid_form', $dataToUpdate);
            } else {
                if ($request['sample_collection_date'] != '' && $request['sample_collection_date'] != null && $request['sample_collection_date'] != '0000-00-00 00:00:00') {
                    $request['request_created_by'] = 0;
                    $request['last_modified_by'] = 0;
                    $request['request_created_datetime'] = $general->getDateTime();
                    //$request['result_status'] = 6;
                    $request['data_sync'] = 0; //column data_sync value is 1 equal to data_sync done.value 0 is not done.
                    $id = $db->insert('eid_form', $request);
                }
            }
            //}
        }
    }
}
