<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
try{
    session_start();
    include_once('../../startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
    include_once(APPLICATION_PATH.'/General.php');

    $general=new General($db);
    
    // Takes raw data from the request
    $json = file_get_contents('php://input');
    $result = explode('&',$json);
    $data = array();
    $vlReqFromTable="vl_request_form";
    if(isset($result) && count($result) > 0 && $result[0] != ""){
        
        /* To get province and district from facility id */
        $facilityQuery ="SELECT facility_state,facility_district from facility_details WHERE facility_id =".$result[2];
        $facilityResult=$db->query($facilityQuery);
        
        /* Prepare the values to insert */
        $data['remote_sample_code']= $result[0];
        $data['sample_code']= $result[0];
        $data['patient_art_no']= $result[1];
        $data['facility_id']= $result[2];
        $data['patient_province']= $facilityResult[0]['facility_state'];
        $data['patient_district']= $facilityResult[0]['facility_district'];
        $data['sample_collection_date']= date('Y-m-d',strtotime($result[5]));
        // $data['sample_type']= $result[6];
        $data['sample_type']= '2';
        $data['request_created_by']= $result[8];
        $data['lab_id']= $result[7];
        $data['sample_tested_datetime']= $general->getDateTime();
        $data['result_status']= '6';
        $data['data_sync']= '0';
        $data['recency_vl']= 'yes';
        $data['vlsm_country_id']= $general->getGlobalConfig('vl_form');;
        $data['result_status']= '9';
        
        /* Check if request data already placed or not */
        // $vlFormReqQuery ="SELECT vl_sample_id from vl_request_form WHERE remote_sample_code ='".$result[0]."' AND patient_art_no ='".$result[1]."' AND facility_id ='".$result[2]."' AND patient_province ='".$facilityResult[0]['facility_state']."' AND patient_district ='".$facilityResult[0]['facility_district']."' AND sample_collection_date ='".date('Y-m-d',strtotime($result[5]))."' AND sample_type ='".$result[6]."'";
        $vlFormReqQuery ="SELECT vl_sample_id from vl_request_form WHERE remote_sample_code ='".$result[0]."'";
        $vlFormReqResult=$db->query($vlFormReqQuery);
        
        /* If request data not requested then process otherwise send msg */
        if(isset($vlFormReqResult) && count($vlFormReqResult) == 0){
            $db->insert($vlReqFromTable,$data);
            $lastId = $db->getInsertId();
            if(isset($lastId) && $lastId > 0){
                echo 'Viral Load Test Request has been send successfully';
            }else{
                // echo 'Something went wrong try after some time..!';
            }
        }else{
            echo 'Testing request already send for this sample id';
        }
    }else{
        // echo 'Something went wrong try after some time..!';
    }
} 
catch(Exception $e) {
    echo 'Error: ' .$e->getMessage();
}