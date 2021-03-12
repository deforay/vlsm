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
// The request has to send an Authorization Bearer token 
$auth = $general->getHeader('Authorization');
// print_r($auth);die;
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
                        LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner limit 5";



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

    $response = array();
    $msg = new Message();
    $msh = new MSH();
    $msg->addSegment($msh); // Message is: "MSH|^~\&|||||20171116140058|||2017111614005840157||2.3|\n"
    foreach($rowData as $row){
        // die(strtoupper(substr($row['patient_gender'],0,1)));
        $check = (in_array($row['patient_gender'], array("female", "male", "other")))?$row['patient_gender']:"other";
        $sex = strtoupper(substr($check,0,1));
        /* Patient Information */
        $pid = new PID();
        $pid->setPatientID($row['patient_id']);
        $pid->setPatientName($row['patient_name']);
        $pid->setMothersMaidenName([$row['patient_name'], $row['patient_surname']]);
        $pid->setDateTimeOfBirth($row['patient_dob']);
        $pid->setSex($sex);
        $pid->setPatientAddress($row['patient_address']);
        $pid->setCountryCode($row['patient_district']);
        $pid->setPhoneNumberHome($row['patient_phone_number']);
        $pid->setSSNNumber($row['external_sample_code']);
        $pid->setNationality($row['nationality']);
        $msg->setSegment($pid, 1);
        /* Sample Information */
        $spm = new Segment('SPM');
        $spm->setField(2, $row['sample_code']);
        $spm->setField(4, $row['sample_name']);
        $spm->setField(10, $row['facility_name']);
        $spm->setField(12, $row['is_sample_collected']);
        $spm->setField(17, $row['sample_collection_date']);
        $spm->setField(18, $row['sample_received_at_vl_lab_datetime']);
        $spm->setField(21, $row['reason_for_sample_rejection']);
        $spm->setField(24, $row['sample_condition']);
        $spm->setField(26, $row['test_number']);
        $msg->setSegment($spm, 2);
        /* OBR Section */
        $obr = new Segment('OBR');
        $obr->setField(1, $row['status_name']);
        $obr->setField(5, $row['priority_status']);
        $obr->setField(6, $row['request_created_datetime']);
        $obr->setField(14, $row['sample_received_at_hub_datetime']);
        $obr->setField(15, $row['source_of_alert']);
        $obr->setField(25, $row['result_status']);
        $obr->setField(26, $row['result']);
        $msg->setSegment($obr, 3);
        /* Clinic Custom Fields Information Details */
        $zci = new Segment('ZCI');
        $zci->setField(1, $row['is_sample_post_mortem']);
        $zci->setField(2, $row['number_of_days_sick']);
        $zci->setField(3, $row['date_of_symptom_onset']);
        $zci->setField(4, $row['date_of_initial_consultation']);
        $zci->setField(5, $row['fever_temp']);
        $zci->setField(6, $row['medical_history']);
        $zci->setField(7, $row['recent_hospitalization']);
        $zci->setField(8, $row['temperature_measurement_method']);
        $zci->setField(9, $row['respiratory_rate']);
        $zci->setField(10, $row['oxygen_saturation']);
        $zci->setField(11, $row['other_diseases']);
        $msg->setSegment($zci, 4);
        /* Patient Custom Fields Information Details */
        $zpi = new Segment('ZPI');
        $zpi->setField(1, $row['patient_occupation']);
        $zpi->setField(2, $row['patient_city']);
        $zpi->setField(3, $row['patient_province']);
        $zpi->setField(4, $row['patient_age']);
        $zpi->setField(5, $row['is_patient_pregnant']);
        $zpi->setField(6, $row['does_patient_smoke']);
        $zpi->setField(7, $row['patient_lives_with_children']);
        $zpi->setField(8, $row['patient_cares_for_children']);
        $zpi->setField(9, $row['close_contacts']);
        $zpi->setField(10, $row['contact_with_confirmed_case']);
        $msg->setSegment($zpi, 5);
        /* Airline Information Details */
        $zai = new Segment('ZAI');
        $zai->setField(1, $row['patient_passport_number']);
        $zai->setField(2, $row['flight_airline']);
        $zai->setField(3, $row['flight_seat_no']);
        $zai->setField(4, $row['flight_arrival_datetime']);
        $zai->setField(5, $row['flight_airport_of_departure']);
        $zai->setField(6, $row['flight_transit']);
        $zai->setField(7, $row['reason_of_visit']);
        $zai->setField(8, $row['has_recent_travel_history']);
        $zai->setField(9, $row['travel_country_names']);
        $zai->setField(10, $row['travel_return_date']);
        $msg->setSegment($zai, 6);
        /*  System Variables Details */
        $zsv = new Segment('ZSV');
        $zsv->setField(1, $row['is_result_authorised']);
        $zsv->setField(2, $row['authorized_by']);
        $zsv->setField(3, $row['authorized_on']);
        $zsv->setField(4, $row['rejection_on']);
        $zsv->setField(5, $row['request_created_datetime']);
        $msg->setSegment($zsv, 7);
        /*  Observation Details */
        $obx = new OBX;
        $obx->setObservationValue($row['result']);
        $msg->setSegment($obx, 8);

        $response[] = $msg->toString(true); 
    }
    // No data found
    if (!$rowData) {
        $response = array(
            'status' => 'failed',
            'timestamp' => time(),
            'error' => 'No matching data',
            'data' => $response

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
        'data' => $response
    );
   
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
