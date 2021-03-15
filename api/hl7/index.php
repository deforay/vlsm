<?php 


session_unset(); // no need of session in json response


use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\MSH;
use Aranyasen\HL7\Segments\PID;
use Aranyasen\HL7\Segments\OBX;

ini_set('memory_limit', -1);
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);
$userDb = new \Vlsm\Models\Users($db);
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
    // echo $hl7Msg."<br><br><br>";

    $msg = new Message();
    $msh = new MSH();
    $msg->addSegment($msh);

    $spm = new Segment('ZFL');
    $spm->setField(1, ["2020-02-21", "2020-02-21"]); // SAMPLE COLLECTION DATE
    $spm->setField(2, ["Sputum", "Serum"]); // SPECIMEN TYPE
    $spm->setField(3, ["Juru CS", "Rilima CS"]); // FACILITIES
    $spm->setField(4, ["Butaro DH", "Kinoni CS"]); // TESTING LAB
    $spm->setField(5, "yes"); // SAMPLE REJECTED
    $spm->setField(6, "yes"); // SAMPLE NOT TESTED
    $msg->setSegment($spm, 1);

    echo $msg->toString(true);

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