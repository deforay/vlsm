<?php
//this fille is get the data from lab db and update in remote db

include(dirname(__FILE__) . "/../../startup.php");
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');

if (!isset($systemConfig['remoteURL']) || $systemConfig['remoteURL'] == '') {
    echo "Please check your remote url";
    die;
}

$systemConfig['remoteURL'] = rtrim($systemConfig['remoteURL'], "/");

$url = $systemConfig['remoteURL'] . '/remote/remote/facilityMap.php';
$data = array(
    "Key" => "vlsm-lab-Data--",
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
$result = json_decode($curl_response, true);
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
//get facility map id

if ($result != "" && count($result) > 0) {
    $fMapResult = implode(",", $result);
} else {
    $fMapResult = "";
}
//get remote data
if (trim($sarr['lab_name']) == '') {
    $sarr['lab_name'] = "''";
}

if (isset($fMapResult) && $fMapResult != '' && $fMapResult != null) {
    $where = "(lab_id =" . $sarr['lab_name'] . " OR facility_id IN (" . $fMapResult . "))";
} else {
    $where = "lab_id =" . $sarr['lab_name'];
}

// VIRAL LOAD TEST RESULTS

$vlQuery = "SELECT vl.*, a.user_name as 'approved_by_name' FROM `vl_request_form` AS vl LEFT JOIN `user_details` AS a ON vl.result_approved_by = a.user_id WHERE result_status NOT IN (9) AND (facility_id != '' AND facility_id is not null) AND (sample_code !='' AND sample_code is not null) AND data_sync=0"; 
// AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";
//echo $vlQuery;die;
$vlLabResult = $db->rawQuery($vlQuery);



$url = $systemConfig['remoteURL'] . '/remote/remote/testResults.php';

$data = array(
    "result" => $vlLabResult,
    "Key" => "vlsm-lab-Data--",
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
$result = json_decode($curl_response, true);

if (!empty($result) && count($result) > 0) {
    foreach ($result as $code) {
        $db = $db->where('sample_code', $code);
        $id = $db->update('vl_request_form', array('data_sync' => 1));
    }
}

// EID TEST RESULTS

if (isset($eidConfig['enabled']) && $eidConfig['enabled'] == true) {

    $eidQuery = "SELECT vl.*, a.user_name as 'approved_by_name' FROM `eid_form` AS vl LEFT JOIN `user_details` AS a ON vl.result_approved_by = a.user_id WHERE result_status NOT IN (9) AND sample_code !='' AND sample_code is not null AND data_sync=0"; // AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";

    $vlLabResult = $db->rawQuery($eidQuery);

    $url = $systemConfig['remoteURL'] . '/remote/remote/eid-test-results.php';
    $data = array(
        "result" => $vlLabResult,
        "Key" => "vlsm-lab-Data--",
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
    $result = json_decode($curl_response, true);

    if (!empty($result) && count($result) > 0) {
        foreach ($result as $code) {
            $db = $db->where('sample_code', $code);
            $id = $db->update('eid_form', array('data_sync' => 1));
        }
    }
}
