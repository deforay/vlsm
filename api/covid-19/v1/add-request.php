<?php

session_unset(); // no need of session in json response

// PURPOSE : Fetch Results using serial_no field which is used to
// store the recency id from third party apps (for eg. in DRC)

// serial_no field in db was unused so we decided to use it to store recency id

ini_set('memory_limit', -1);
header('Content-Type: application/json');
$c19Model = new \Vlsm\Models\Covid19($db);
$general = new \Vlsm\Models\General($db);
$userDb = new \Vlsm\Models\Users($db);
$user = null;

$data = json_decode(file_get_contents("php://input"),true);
if(isset($data['api_token']) && $data['api_token']!='')
{
    $auth = $data['api_token'];
    $authToken = str_replace("Bearer ", "", $auth);
    /* Check if API token exists */
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
$sampleFrom = '';
// echo $c19Model->generateCovid19SampleCode($data['province'], $data['sampleCollectionDate'], $sampleFrom);
// die;
// $data = json_decode(file_get_contents("php://input"),true);
// insertSampleCode('addCovid19RequestForm', 'covid19SampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 3, 'sampleCollectionDate');
$data['api'] = "yes";
$_POST = $data;
include_once(APPLICATION_PATH . '/covid-19/requests/insert-sample.php');
include_once(APPLICATION_PATH . '/covid-19/requests/covid-19-add-request-helper.php');

try {

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
