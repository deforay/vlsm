<?php
header('Content-Type: application/json');
require_once('../../../../startup.php');

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
if(!isset($input['patientKey']) && empty($input['patientKey'])){
    $payload = array(
        'status' => 0,
        'message'=> "Patient search not found",
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($payload);
    exit(0);
}
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

echo json_encode($payload);