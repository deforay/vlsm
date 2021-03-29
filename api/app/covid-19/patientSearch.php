<?php
header('Content-Type: application/json');
require_once('../../../startup.php');

$general = new \Vlsm\Models\General($db);
$input = json_decode(file_get_contents("php://input"),true);
$queryParams = array($input['authToken'], $input['userId']);
$admin = $db->rawQuery("SELECT user_id,user_name,phone_number,login_id,status FROM user_details as ud WHERE ud.api_token = ? AND ud.user_id = ? ", $queryParams);
if (isset($input['authToken']) && !empty($input['authToken']) && isset($input['userId']) && !empty($input['userId'])) {
    if(count($admin) > 0)
    {
        $artNo=$input['patientKey'];
        //global config
        $db->startTransaction();
        $cQuery="SELECT * FROM global_config";
        $cResult=$db->query($cQuery);
        $arr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($cResult); $i++) {
        $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
        }
        $count = 0;
        $pQuery="SELECT * FROM form_covid19 where vlsm_country_id='".$arr['vl_form']."' AND (patient_id like '%".$artNo."%' OR patient_name like '%".$artNo."%' OR patient_surname like '%".$artNo."%' OR patient_phone_number like '%".$artNo."%')";
        $pResult = $db->rawQuery($pQuery);
        $db->commit();
        $payload = array(
            'status' => 1,
            'message'=>'Success',
            'data' => $pResult,
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
else 
{
    $payload = array(
    'status' => 0,
    'message'=>'Please Send all the credentials',
    'timestamp' => $general->getDateTime()
    );
}
echo json_encode($payload);