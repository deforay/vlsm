<?php

session_unset(); // no need of session in json response

// PURPOSE : Fetch Results using serial_no field which is used to
// store the recency id from third party apps (for eg. in DRC)

// serial_no field in db was unused so we decided to use it to store recency id

ini_set('memory_limit', -1);
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);
$userDb = new \Vlsm\Models\Users($db);
$user = null;
// The request has to send an Authorization Bearer token 
$auth = $general->getHttpValue('Authorization');
if (!empty($auth)) {
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

try {

    $sQuery="SELECT 
                        vl.covid19_id,
                        vl.sample_code,
                        vl.remote_sample_code,
                        vl.patient_id,
                        vl.patient_name,
                        vl.patient_surname,
                        vl.patient_dob,
                        vl.patient_gender,
                        vl.patient_age,
                        vl.patient_province,
                        vl.patient_district,
                        vl.patient_nationality,
                        vl.patient_city,
                        vl.sample_collection_date,
                        vl.type_of_test_requested,
                        vl.date_of_symptom_onset,
                        vl.sample_condition,
                        vl.contact_with_confirmed_case,
                        vl.has_recent_travel_history,
                        vl.travel_country_names,
                        vl.travel_return_date,
                        vl.sample_tested_datetime,
                        vl.sample_received_at_vl_lab_datetime,
                        vl.is_sample_rejected,
                        vl.result,
                        vl.is_result_authorised,
                        vl.approver_comments,
                        vl.request_created_datetime,
                        vl.result_printed_datetime,
                        vl.testing_point,
                        vl.source_of_alert,
                        vl.source_of_alert_other,
                        rtr.test_reason_name,
                        b.batch_code,
                        ts.status_name,
                        rst.sample_name,
                        f.facility_name,
                        l_f.facility_name as labName,
                        f.facility_code,
                        f.facility_state,
                        f.facility_district,
                        u_d.user_name as reviewedBy,
                        a_u_d.user_name as approvedBy,
                        lt_u_d.user_name as labTechnician,
                        rs.rejection_reason_name,
                        r_f_s.funding_source_name,
                        c.iso_name as nationality,
                        r_i_p.i_partner_name 
                        
                        FROM form_covid19 as vl 
                        
                        LEFT JOIN r_countries as c ON vl.patient_nationality=c.id
                        LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
                        LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
                        LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
                        LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
                        LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
                        LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
                        LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_technician 
                        LEFT JOIN r_covid19_test_reasons as rtr ON rtr.test_reason_id=vl.reason_for_covid19_test 
                        LEFT JOIN r_covid19_sample_type as rst ON rst.sample_id=vl.specimen_type 
                        LEFT JOIN r_covid19_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
                        LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source 
                        LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";



    // if (!empty($recencyId)) {
    //     $recencyId = implode("','", $recencyId);
    //     $sQuery .= " AND serial_no IN ('$recencyId') ";
    // }

    // if (!empty($sampleCode)) {
    //     $sampleCode = implode("','", $sampleCode);
    //     $sQuery .= " AND sample_code IN ('$sampleCode') ";
    // }

    // if (!empty($from) && !empty($to)) {
    //     $sQuery .= " AND DATE(last_modified_datetime) between '$from' AND '$to' ";
    // }

    // $sQuery .= " ORDER BY last_modified_datetime ASC ";
    $rowData = $db->rawQuery($sQuery);

    // No data found
    if (!$rowData) {
        $response = array(
            'status' => 'failed',
            'timestamp' => time(),
            'error' => 'No matching data',
            'data' => $rowData

        );
        // if (isset($user['token-updated']) && $user['token-updated'] == true) {
        //     $response['token'] = $user['newToken'];
        // }
        http_response_code(200);
        echo json_encode($response);
        exit(0);
    }

    $payload = array(
        'status' => 'success',
        'timestamp' => time(),
        'data' => $rowData
    );
    // if (isset($user['token-updated']) && $user['token-updated'] == true) {
    //     $payload['token'] = $user['newToken'];
    // }

    http_response_code(200);
    echo json_encode($payload);
    exit(0);
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
