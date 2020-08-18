<?php

header('Content-Type: application/json');

include_once(APPLICATION_PATH . "/includes/MysqliDb.php");
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . "/vendor/autoload.php");


session_unset(); // no need of session in json response
$general = new General($db);

$serialNo = isset($_POST['s']) && !empty($_POST['s']) ? $_POST['s'] : null;
$apiKey = isset($_POST['x-api-key']) && !empty($_POST['x-api-key']) ? $_POST['x-api-key'] : null;

$data = json_decode(file_get_contents('php://input'), true);




if (!$apiKey) {
    $response = array(
        'status' => 'failed',
        'data' => 'API Key invalid',
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($response);
    exit(0);
}

if (!$serialNo) {
    $response = array(
        'status' => 'failed',
        'data' => 'Serial Number missing in request',
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($response);
    exit(0);
}

try {

    $sQuery = "SELECT vl.*,s.sample_name,s.status as sample_type_status,
                    ts.*,f.facility_name,l_f.facility_name as labName,
                    f.facility_code,f.facility_state,f.facility_district,
                    f.facility_mobile_numbers,f.address,f.facility_hub_name,
                    f.contact_person,f.report_email,f.country,f.longitude,
                    f.latitude,f.facility_type,f.status as facility_status,
                    ft.facility_type_name,lft.facility_type_name as labFacilityTypeName,
                    l_f.facility_name as labName,l_f.facility_code as labCode,
                    l_f.facility_state as labState,l_f.facility_district as labDistrict,
                    l_f.facility_mobile_numbers as labPhone,l_f.address as labAddress,
                    l_f.facility_hub_name as labHub,l_f.contact_person as labContactPerson,
                    l_f.report_email as labReportMail,l_f.country as labCountry,
                    l_f.longitude as labLongitude,l_f.latitude as labLatitude,
                    l_f.facility_type as labFacilityType,
                    l_f.status as labFacilityStatus,tr.test_reason_name,
                    tr.test_reason_status,rsrr.rejection_reason_name,
                    rsrr.rejection_reason_status 
                        FROM vl_request_form as vl 
                        LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
                        LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
                        LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.sample_type 
                        INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
                        LEFT JOIN r_vl_test_reasons as tr ON tr.test_reason_id=vl.reason_for_vl_testing 
                        LEFT JOIN facility_type as ft ON ft.facility_type_id=f.facility_type 
                        LEFT JOIN facility_type as lft ON lft.facility_type_id=l_f.facility_type 
                        LEFT JOIN r_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection
                        
                        WHERE serial_no is not null AND serial_no like '$serialNo' ";



    //$sQuery .= " LIMIT 1 ";



    $aRow = $db->rawQueryOne($sQuery);

    if (!$aRow) {
        $response = array(
            'status' => 'failed',
            'data' => 'Invalid Serial Number',
            'timestamp' => $general->getDateTime()
        );
        echo json_encode($response);
        exit(0);
    }


    $output = array();


    $row = array();

    
    $row['sample_code']                         = $aRow['sample_code'];
    $row['collection_facility_name']            = ($aRow['facility_name']);
    $row['testing_lab_name']                    = ($aRow['labName']);
    $row['sample_type']                         = $aRow['sample_name'];
    $row['sample_collection_date']              = $aRow['sample_collection_date'];
    $row['sample_received_at_vl_lab_datetime']  = $aRow['sample_received_at_vl_lab_datetime'];
    $row['sample_registered_at_lab']            = $aRow['sample_registered_at_lab'];
    $row['sample_tested_datetime']              = $aRow['sample_tested_datetime'];
    $row['is_sample_rejected']                  = $aRow['is_sample_rejected'];
    $row['rejection_reason']                    = $aRow['rejection_reason_name'];
    $row['result']                              = $aRow['result'];
    $row['result_approved_datetime']            = $aRow['result_approved_datetime'];



    $currentDate = $general->getDateTime();
    $payload = array(
        'status' => 'success',
        'data' => $row,
        'timestamp' => $general->getDateTime()
    );

    echo json_encode($payload);
    exit(0);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}
