<?php

ini_set('memory_limit', -1);

require_once(__DIR__ . "/../../startup.php");
include_once(APPLICATION_PATH . "/includes/MysqliDb.php");
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . "/vendor/autoload.php");

$general = new General($db);

$data = array();

// if forceSync is set as true, we will drop and create tables on VL Dashboard DB
$data['forceSync'] = false; 




$referenceTables = array(
    'facility_details'
);

if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {
    $vlTables = array(
        'r_vl_sample_type',
        'r_vl_test_reasons',
        'r_art_code_details',
        'r_sample_rejection_reasons',
    );

    $referenceTables = array_merge($referenceTables, $vlTables);
}


if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {
    $eidTables = array(
        //'r_eid_results',
        'r_eid_sample_rejection_reasons',
        'r_eid_sample_type',
        //'r_eid_test_reasons',
    );
    $referenceTables = array_merge($referenceTables, $eidTables);
}


if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {
    $covid19Tables = array(
        //'r_covid19_results',
        'r_covid19_comorbidities',
        'r_covid19_sample_rejection_reasons',
        'r_covid19_sample_type',
        'r_covid19_symptoms',
        'r_covid19_test_reasons',
    );
    $referenceTables = array_merge($referenceTables, $covid19Tables);
}


try {

    foreach ($referenceTables as $table) {
        $data[$table]['lastModifiedTime'] = $general->getLastModifiedDateTime($table);
        $data[$table]['tableData'] = $db->get($table);
    }


    var_dump($data);

    die;



    $filename = 'reference-data-' . $currentDate . '.json';
    $fp = fopen(TEMP_PATH . DIRECTORY_SEPARATOR . $filename, 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);


    

    $vldashboardUrl = $general->getGlobalConfig('vldashboard_url');
    $vldashboardUrl = rtrim($vldashboardUrl, "/");
    //$vldashboardUrl = "http://vldashboard";

    $apiUrl = $vldashboardUrl . "/api/vlsm-reference-tables";


    $data = [];
    $data['api-version'] = 'v2';
    $data['referenceFile'] = new CURLFile(TEMP_PATH . DIRECTORY_SEPARATOR . $filename, 'application/json', $filename);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data']
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
