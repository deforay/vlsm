<?php

session_unset(); // no need of session in json response


use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\MSH;
use Aranyasen\HL7\Segments\PID;
// PURPOSE : Fetch Results using serial_no field which is used to
// store the recency id from third party apps (for eg. in DRC)

// serial_no field in db was unused so we decided to use it to store recency id

ini_set('memory_limit', -1);
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);
$userDb = new \Vlsm\Models\Users($db);
$user = null;
// The request has to send an Authorization Bearer token 
/* $auth = $general->getHttpValue('Authorization');
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
} */

try {

    $sQuery = "SELECT 
                        vl.*,
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
                        c.iso2 as country_code1,
                        c.iso3 as country_code2,
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
                        LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner limit 1";



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

    // echo "<pre>";print_r($rowData);die;
    $response = array();
    $msg = new Message();
    $msh = new MSH();
    $msg->addSegment($msh); // Message is: "MSH|^~\&|||||20171116140058|||2017111614005840157||2.3|\n"
    foreach($rowData as $row){
        /* Patient Information */
        $pid = new PID();
        $pid->setPatientID($row['patient_id']);
        $pid->setPatientName($row['patient_name']);
        $pid->setMothersMaidenName($row['patient_surname']);
        $pid->setDateTimeOfBirth(strtotime($row['patient_dob']));
        $pid->setSex(strtoupper(substr($row['patient_gender'],0,1)));
        $pid->setPatientAddress($row['patient_address']);
        $pid->setPhoneNumberHome($row['patient_phone_number']);
        $pid->setCountryCode($row['country_code2']);
        $msg->setSegment($pid, 1);
        /* Clinic Information Details */
        $cid = new Segment('CID');
        $cid->setField(2, $row['remote_sample_code']);
        $cid->setField(3, $row['facility_code']);
        $cid->setField(5, $row['facility_state']);
        $cid->setField(6, $row['facility_district']);
        $cid->setField(7, $row['funding_source_name']);
        $cid->setField(8, $row['i_partner_name']);
        $msg->setSegment($cid, 2);
        /* Sample Information */
        $spm = new Segment('SPM');
        $spm->setField(2, $row['sample_code']);
        $spm->setField(4, $row['sample_name']);
        $spm->setField(10, $row['facility_name']);
        $spm->setField(17, strtotime($row['sample_collection_date']));
        $spm->setField(18, $row['sample_received_at_vl_lab_datetime']);
        $spm->setField(21, $row['reason_for_sample_rejection']);
        $spm->setField(24, $row['sample_condition']);
        $msg->setSegment($spm, 3);
        /* Laboratory Information */
        $lap = new Segment('LAB');
        $lap->setField(1, $row['labName']);
        $lap->setField(2, $row['sample_received_at_hub_datetime']);
        $lap->setField(4, $row['result']);
        $lap->setField(5, $row['result_printed_datetime']);
        $lap->setField(6, $row['test_reason_name']);
        $lap->setField(7, $row['type_of_test_requested']);
        $lap->setField(9, $row['batch_code']);
        $lap->setField(10, $row['is_sample_rejected']);
        $lap->setField(12, $row['rejection_reason_name']);
        $lap->setField(13, $row['reviewedBy']);
        $lap->setField(14, $row['approvedBy']);
        $lap->setField(15, $row['labTechnician']);
        $lap->setField(16, $row['approver_comments']);
        $msg->setSegment($lap, 4);
        /* OBR Section */
        $obr = new Segment('OBR');
        $obr->setField(1, $row['status_name']);
        $obr->setField(15, $row['source_of_alert']);
        $msg->setSegment($obr, 5);
        

        $response[] = $msg->toString(true); 
    }
    echo $msg->toString(true);die;
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
