<?php
header('Content-Type: application/json');
require_once('../../../startup.php');


$general = new \Vlsm\Models\General($db);
$input = json_decode(file_get_contents("php://input"),true);
$queryParams = array($input['authToken'], $input['userId']);
$admin = $db->rawQuery("SELECT user_id,user_name,phone_number,login_id,status FROM user_details as ud WHERE ud.api_token = ? AND ud.user_id = ? ", $queryParams);
if (isset($input['authToken']) && !empty($input['authToken']) && isset($input['userId']) && !empty($input['userId']) && isset($input['province']) && !empty($input['province'])) {
    if(count($admin) > 0)
    {
        $data = array();
        $db->where("f.facility_state", $input['province']);
        $data['facilityInfo'] = $db->setQueryOption('DISTINCT')->get('facility_details f', null, array('facility_district'));        
        $payload = array(
            'status' => 1,
            'message'=>'Success',
            'data' => $data,
            'timestamp' => $general->getDateTime()
        );
    }
    else
    {
        $payload = array(
            'status' => 2,
            'message'=>'Api token is invalid.',
            'timestamp' => $general->getDateTime()
        );
    }
}
else {
    $payload = array(
        'status' => 0,
        'message'=>'Please Send all the credentials',
        'timestamp' => $general->getDateTime()
    );
}
echo json_encode($payload);