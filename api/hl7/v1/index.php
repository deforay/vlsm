<?php

session_unset(); // no need of session in json response

use Aranyasen\HL7\Message;

ini_set('memory_limit', -1);
header('Content-Type: application/json');
$user = null;
$general = new \Vlsm\Models\General($db);
$userDb = new \Vlsm\Models\Users($db);
$facilityDb = new \Vlsm\Models\Facilities($db);
$c19Db = new \Vlsm\Models\Covid19($db);
$vlDb = new \Vlsm\Models\Vl($db);

$user = null;
try {
    // The request has to send an Authorization Bearer token 
    $auth = $general->getHeader('Authorization');
    if (!empty($auth)) {
        $authToken = str_replace("Bearer ", "", $auth);
        // Check if API token exists
        $user = $userDb->getAuthToken($authToken);
    }
    // If authentication fails then do not proceed
    if (empty($user) || empty($user['user_id'])) {
        $response = array(
            'status' => 'failed',
            'timestamp' => time(),
            'error' => 'Bearer Token Invalid',
            'data' => array()
        );
        http_response_code(401);
        echo json_encode($response);
        exit(0);
    }

    $hl7Msg = file_get_contents("php://input");
    foreach(explode("MSH", $hl7Msg) as $hl7){
        if(isset($hl7) && !empty($hl7) && trim($hl7) != ""){
            $hl7 = 'MSH'.$hl7;
            $msg = new Message($hl7);
            // To get the type of test
            $msh = $msg->getSegmentByIndex(0);
            $type = $msh->getField(9);
            // Get if have any filters
            if ($msg->hasSegment('ZFL')) {
                $filters = $msg->getSegmentsByName('ZFL')[0];
            }
            if ($type[1] == 'RES' || $type[1] == 'QRY') {
                foreach ((array)$filters as $search) {
                    $search = $search;
                    break;
                }
            }
            if ($type[0] == "COVID-19") {
                include("covid-19.php");
            }
            if ($type[0] == "VL") {
                include("vl.php");
            }
            if ($type[0] == "EID") {
                include("eid.php");
            }
        }
    }
} catch (Exception $exc) {

    http_response_code(500);
    $payload = array(
        'status' => 'failed',
        'timestamp' => time(),
        'error' => $exc->getMessage(),
        'data' => array()
    );
    if (isset($user['token-updated']) && $user['token-updated'] == true) {
        $payload['token'] = $user['newToken'];
    }

    echo json_encode($payload);

    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}
