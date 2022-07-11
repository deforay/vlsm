<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();

$tblName = 's_vlsm_instance';
$general = new \Vlsm\Models\General();

if (isset($_POST['instance_facility_name']) && trim($_POST['instance_facility_name']) != "") {
    $instanceName = $_POST['instance_facility_name'];
} else {
    $instanceName = NULL;
}

if (isset($_POST['instance_facility_code']) && trim($_POST['instance_facility_code']) != "") {
    $instanceCode = $_POST['instance_facility_code'];
} else {
    $instanceCode = NULL;
}

if (isset($_POST['vl_last_dash_sync']) && trim($_POST['vl_last_dash_sync']) != "") {
    $vlLastSync = explode(" ", $_POST['vl_last_dash_sync']);
$_POST['vl_last_dash_sync'] = $general->dateFormat($vlLastSync[0]) . " " . $vlLastSync[1];
} else {
    $_POST['vl_last_dash_sync'] = NULL;
}

if (isset($_POST['eid_last_dash_sync']) && trim($_POST['eid_last_dash_sync']) != "") {
    $eidLastSync = explode(" ", $_POST['eid_last_dash_sync']);
$_POST['eid_last_dash_sync'] = $general->dateFormat($eidLastSync[0]) . " " . $eidLastSync[1];
} else {
    $_POST['eid_last_dash_sync'] = NULL;
}

if (isset($_POST['covid19_last_dash_sync']) && trim($_POST['covid19_last_dash_sync']) != "") {
    $covid19LastSync = explode(" ", $_POST['covid19_last_dash_sync']);
$_POST['covid19_last_dash_sync'] = $general->dateFormat($covid19LastSync[0]) . " " . $covid19LastSync[1];
} else {
    $_POST['covid19_last_dash_sync'] = NULL;
}

if (isset($_POST['last_remote_requests_sync']) && trim($_POST['last_remote_requests_sync']) != "") {
    $lastRemoteRequestSync = explode(" ", $_POST['last_remote_requests_sync']);
$_POST['last_remote_requests_sync'] = $general->dateFormat($lastRemoteRequestSync[0]) . " " . $lastRemoteRequestSync[1];
} else {
    $_POST['last_remote_requests_sync'] = NULL;
}

if (isset($_POST['last_remote_results_sync']) && trim($_POST['last_remote_results_sync']) != "") {
    $lastRemoteResultsSync = explode(" ", $_POST['last_remote_results_sync']);
$_POST['last_remote_results_sync'] = $general->dateFormat($lastRemoteResultsSync[0]) . " " . $lastRemoteResultsSync[1];
} else {
    $_POST['last_remote_results_sync'] = NULL;
}

if (isset($_POST['last_remote_reference_data_sync']) && trim($_POST['last_remote_reference_data_sync']) != "") {
    $lastRemoteReferenceSync = explode(" ", $_POST['last_remote_reference_data_sync']);
$_POST['last_remote_reference_data_sync'] = $general->dateFormat($lastRemoteReferenceSync[0]) . " " . $lastRemoteReferenceSync[1];
} else {
    $_POST['last_remote_reference_data_sync'] = NULL;
}

if(($_POST['action'] == 'edit') && !empty($_POST['id'])){
    //update data
    $userData = array(
        'instance_facility_name' => $instanceName,
        'instance_facility_code' => $instanceCode,
        'vl_last_dash_sync' => $_POST['vl_last_dash_sync'],
        'eid_last_dash_sync' => $_POST['eid_last_dash_sync'],
        'covid19_last_dash_sync' => $_POST['covid19_last_dash_sync'],
        'last_remote_requests_sync' => $_POST['last_remote_requests_sync'],
        'last_remote_results_sync' => $_POST['last_remote_results_sync'],
        'last_remote_reference_data_sync' => $_POST['last_remote_reference_data_sync']
    );
    $condition = $db->where('vlsm_instance_id', $_POST['id']);
    $update = $db->update($tblName, $userData, $condition);
    if($update){
        $returnData = array(
            'status' => 'ok',
            'msg' => _("Instance data has been updated successfully."),
            'data' => $userData
        );
    }else{
        $returnData = array(
            'status' => 'error',
            'msg' => _("Some problem occurred, please try again."),
            'data' => ''
        );
    }
    
    echo json_encode($returnData);
}
die();
?>