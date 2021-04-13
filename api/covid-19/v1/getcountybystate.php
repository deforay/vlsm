<?php
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);

$input = json_decode(file_get_contents("php://input"),true);
$check = $app->fetchAuthToken($input);
if (isset($check['status']) && !empty($check['status']) && $check['status'] == false) {
    $payload = array(
        'status' => 0,
        'message'=> $check['message'],
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($payload);
    exit(0);
}

if(isset($input['province']) && !empty($input['province'])){
    $data = array();
    $db->where("f.facility_state", $input['province']);
    $data['facilityInfo'] = $db->setQueryOption('DISTINCT')->get('facility_details f', null, array('facility_district'));        
    $payload = array(
        'status' => 1,
        'message'=>'Success',
        'data' => $data,
        'timestamp' => $general->getDateTime()
    );
} else{
    $payload = array(
        'status' => 0,
        'message'=>'Please send province details',
        'timestamp' => $general->getDateTime()
    );
}
echo json_encode($payload);