<?php

session_unset(); // no need of session in json response

ini_set('memory_limit', -1);
header('Content-Type: application/json');
try {
    $c19Model = new \Vlsm\Models\Covid19($db);
    $general = new \Vlsm\Models\General($db);
    $userDb = new \Vlsm\Models\Users($db);
    $user = null;
    $app = new \Vlsm\Models\App($db);
    $globalConfig = $general->getGlobalConfig();
    $systemConfig = $general->getSystemConfig();

    $input = json_decode(file_get_contents("php://input"), true);
    $check = $app->fetchAuthToken($input);
    if (isset($check['status']) && !empty($check['status']) && $check['status'] == false) {
        $payload = array(
            'status' => 0,
            'message' => $check['message'],
            'timestamp' => $general->getDateTime()
        );
        echo json_encode($payload);
        exit(0);
    }

    $data = $input;
    $sampleFrom = '';
    $data['formId'] = $data['countryId'] = $general->getGlobalConfig('vl_form');
    $sQuery = "SELECT vlsm_instance_id from s_vlsm_instance";
    $rowData = $db->rawQuery($sQuery);
    $data['instanceId'] = $rowData[0]['vlsm_instance_id'];
    $sampleFrom = '';

    $data['api'] = "yes";
    $_POST = $data;
    include_once('insert-sample.php');
    include_once('covid-19-add-request-helper.php');
} catch (Exception $exc) {

    http_response_code(500);
    $payload = array(
        'status' => 'failed',
        'timestamp' => time(),
        'error' => $exc->getMessage(),
        'data' => array()
    );
    echo json_encode($payload);

    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}
