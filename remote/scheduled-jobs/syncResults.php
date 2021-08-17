<?php
//this fille is get the data from lab db and update in remote db

include(dirname(__FILE__) . "/../../startup.php");
$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);

if (!isset($systemConfig['remoteURL']) || $systemConfig['remoteURL'] == '') {
    echo "Please check your remote url";
    exit();
}

$systemConfig['remoteURL'] = rtrim($systemConfig['remoteURL'], "/");

$url = $systemConfig['remoteURL'] . '/remote/remote/facilityMap.php';
$data = array(
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
if (trim($sarr['sc_testing_lab_id']) == '') {
    $sarr['sc_testing_lab_id'] = "''";
}

if (isset($fMapResult) && $fMapResult != '' && $fMapResult != null) {
    $where = "(lab_id =" . $sarr['sc_testing_lab_id'] . " OR facility_id IN (" . $fMapResult . "))";
} else {
    $where = "lab_id =" . $sarr['sc_testing_lab_id'];
}

// VIRAL LOAD TEST RESULTS
if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {
    $vlQuery = "SELECT vl.*, a.user_name as 'approved_by_name' 
            FROM `vl_request_form` AS vl 
            LEFT JOIN `user_details` AS a ON vl.result_approved_by = a.user_id 
            WHERE result_status NOT IN (9) 
            AND (facility_id != '' AND facility_id is not null) 
            AND (sample_code !='' AND sample_code is not null) 
            AND data_sync=0";
    // AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";
    //echo $vlQuery;die;
    $vlLabResult = $db->rawQuery($vlQuery);



    $url = $systemConfig['remoteURL'] . '/remote/remote/testResults.php';

    $data = array(
        "result" => $vlLabResult,
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
    $result = json_decode($curl_response, true);

    if (!empty($result) && count($result) > 0) {
        //foreach ($result as $code) {
        $db = $db->where("sample_code IN ('" . implode("','", $result) . "')");
        $id = $db->update('vl_request_form', array('data_sync' => 1));
        //}
    }
    if (count($vlLabResult) > 0) {
        $trackId = $app->addApiTracking('', count($vlLabResult), 'results', 'vl', $url, $sarr['sc_testing_lab_id'], 'sync-api');
    }
}


// EID TEST RESULTS

if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {

    $eidQuery = "SELECT vl.*, a.user_name as 'approved_by_name' 
                    FROM `eid_form` AS vl 
                    LEFT JOIN `user_details` AS a ON vl.result_approved_by = a.user_id 
                    WHERE result_status NOT IN (9) 
                    AND sample_code !='' 
                    AND sample_code is not null 
                    AND data_sync=0"; // AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";

    $eidLabResult = $db->rawQuery($eidQuery);

    $url = $systemConfig['remoteURL'] . '/remote/remote/eid-test-results.php';
    $data = array(
        "result" => $eidLabResult,
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
    $result = json_decode($curl_response, true);

    if (!empty($result) && count($result) > 0) {
        //foreach ($result as $code) {
        $db = $db->where("sample_code IN ('" . implode("','", $result) . "')");
        $id = $db->update('eid_form', array('data_sync' => 1));
        //}
    }
    if (count($vlLabResult) > 0) {
        $trackId = $app->addApiTracking('', count($eidLabResult), 'results', 'eid', $url, $sarr['sc_testing_lab_id'], 'sync-api');
    }
}



// COVID-19 TEST RESULTS

if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {

    $covid19Query = "SELECT c19.*, a.user_name as 'approved_by_name' 
                    FROM `form_covid19` AS c19 
                    LEFT JOIN `user_details` AS a ON c19.result_approved_by = a.user_id 
                    WHERE result_status NOT IN (9) 
                    AND sample_code !='' 
                    AND sample_code is not null 
                    AND data_sync=0"; // AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";

    $c19LabResult = $db->rawQuery($covid19Query);

    $forms = array();
    foreach ($c19LabResult as $row) {
        $forms[] = $row['covid19_id'];
    }

    $covid19Obj = new \Vlsm\Models\Covid19($db);
    $symptoms = $covid19Obj->getCovid19SymptomsByFormId($forms);
    $comorbidities = $covid19Obj->getCovid19ComorbiditiesByFormId($forms);
    $testResults = $covid19Obj->getCovid19TestsByFormId($forms);

    $url = $systemConfig['remoteURL'] . '/remote/remote/covid-19-test-results.php';
    $data = array(
        "result" => $c19LabResult,
        "testResults" => $testResults,
        "symptoms" => $symptoms,
        "comorbidities" => $comorbidities,
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
    $result = json_decode($curl_response, true);

    if (!empty($result) && count($result) > 0) {
        //foreach ($result as $code) {
        $db = $db->where("sample_code IN ('" . implode("','", $result) . "')");
        $id = $db->update('form_covid19', array('data_sync' => 1));
        //}
    }
    if (count($vlLabResult) > 0) {
        $trackId = $app->addApiTracking('', count($c19LabResult), 'results', 'covid19', $url, $sarr['sc_testing_lab_id'], 'sync-api');
    }
}

// Hepatitis TEST RESULTS

if (isset($systemConfig['modules']['hepatitis']) && $systemConfig['modules']['hepatitis'] == true) {

    $hepQuery = "SELECT hep.*, a.user_name as 'approved_by_name' 
                    FROM `form_hepatitis` AS hep 
                    LEFT JOIN `user_details` AS a ON hep.result_approved_by = a.user_id 
                    WHERE result_status NOT IN (9) 
                    AND sample_code !='' 
                    AND sample_code is not null 
                    AND data_sync=0"; // AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";

    $hepLabResult = $db->rawQuery($hepQuery);

    // $forms = array();
    // foreach ($hepLabResult as $row) {
    //     $forms[] = $row['hepatitis_id'];
    // }

    // $hepatitisObj = new \Vlsm\Models\Hepatitis($db);
    // $risks = $hepatitisObj->getRiskFactorsByHepatitisId($forms);
    // $comorbidities = $hepatitisObj->getComorbidityByHepatitisId($forms);

    $url = $systemConfig['remoteURL'] . '/remote/remote/hepatitis-test-results.php';
    $data = array(
        "result" => $hepLabResult,
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
    $result = json_decode($curl_response, true);

    if (!empty($result) && count($result) > 0) {
        //foreach ($result as $code) {
        $db = $db->where("sample_code IN ('" . implode("','", $result) . "')");
        $id = $db->update('form_hepatitis', array('data_sync' => 1));
        //}
    }
    if (count($vlLabResult) > 0) {
        $trackId = $app->addApiTracking('', count($hepLabResult), 'results', 'hepatitis', $url, $sarr['sc_testing_lab_id'], 'sync-api');
    }
}
/* Get instance id for update last_remote_results_sync */
$instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");

/* Update last_remote_results_sync in s_vlsm_instance */
$db = $db->where('vlsm_instance_id', $instanceResult['vlsm_instance_id']);
$id = $db->update('s_vlsm_instance', array('last_remote_results_sync' => $general->getDateTime()));
