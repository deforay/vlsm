<?php

session_unset(); // no need of session in json response

ini_set('memory_limit', -1);
header('Content-Type: application/json');

$general = new \Vlsm\Models\General();
$userDb = new \Vlsm\Models\Users();
$facilityDb = new \Vlsm\Models\Facilities();
$app = new \Vlsm\Models\App();
$arr = $general->getGlobalConfig();
$user = null;
$input = json_decode(file_get_contents("php://input"), true);
/* echo "<pre>";
print_r($input);
die; */
/* For API Tracking params */
$requestUrl .= $_SERVER['HTTP_HOST'];
$requestUrl .= $_SERVER['REQUEST_URI'];
$params = file_get_contents("php://input");

// The request has to send an Authorization Bearer token 
$auth = $general->getHeader('Authorization');
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

    $sQuery = "SELECT 
        vl.app_sample_code                                   as appSampleCode,
        vl.unique_id                                         as uniqueId,
        vl.eid_id                                            as eidId,
        vl.sample_code                                       as sampleCode,
        vl.remote_sample_code                                as remoteSampleCode,
        vl.vlsm_instance_id                                  as instanceId,
        vl.vlsm_country_id                                   as formId,
        vl.facility_id                                       as facilityId,
        vl.province_id                                       as provinceId,
        vl.lab_id                                            as labId,
        vl.implementing_partner                              as implementingPartner,
        vl.funding_source                                    as fundingSource,
        vl.mother_id                                         as mothersId,
        vl.caretaker_contact_consent                         as caretakerConsentForContact,
        vl.caretaker_phone_number                            as caretakerPhoneNumber,
        vl.caretaker_address                                 as caretakerAddress,
        vl.mother_name                                       as mothersName,
        vl.mother_dob                                        as mothersDob,
        vl.mother_marital_status                             as mothersMaritalStatus,
        vl.mother_treatment                                  as motherTreatment,
        vl.mother_treatment_other                            as motherTreatmentOther,
        vl.mother_treatment_initiation_date                  as motherTreatmentInitiationDate,
        vl.child_id                                          as childId,
        vl.child_name                                        as childName,
        vl.child_surname                                     as childSurName,
        vl.child_dob                                         as childDob,
        vl.child_gender                                      as childGender,
        vl.child_age                                         as childAge,
        vl.child_treatment                                   as childTreatment,
        vl.child_treatment_other                             as childTreatmentOther,
        vl.mother_cd4                                        as mothercd4,
        vl.mother_vl_result                                  as motherVlResult,
        vl.mother_hiv_status                                 as mothersHIVStatus,
        vl.pcr_test_performed_before                         as pcrTestPerformedBefore,
        vl.previous_pcr_result                               as prePcrTestResult,
        vl.last_pcr_date                                     as previousPCRTestDate,
        vl.reason_for_pcr                                    as pcrTestReason,
        vl.has_infant_stopped_breastfeeding                  as hasInfantStoppedBreastfeeding,
        vl.age_breastfeeding_stopped_in_months               as ageBreastfeedingStopped,
        vl.choice_of_feeding                                 as choiceOfFeeding,
        vl.is_cotrimoxazole_being_administered_to_the_infant as isCotrimoxazoleBeingAdministered,
        vl.specimen_type                                     as specimenType,
        vl.sample_collection_date                            as sampleCollectionDate,
        vl.sample_requestor_phone                            as sampleRequestorPhone,
        vl.sample_requestor_name                             as sampleRequestorName,
        vl.rapid_test_performed                              as rapidTestPerformed,
        vl.rapid_test_date                                   as rapidtestDate,
        vl.rapid_test_result                                 as rapidTestResult,
        vl.lab_reception_person                              as labReceptionPerson,
        vl.sample_received_at_vl_lab_datetime                as sampleReceivedDate,
        vl.eid_test_platform                                 as eidPlatform,
        vl.import_machine_name                               as machineName,
        vl.sample_tested_datetime                            as sampleTestedDateTime,
        vl.sample_dispatched_datetime                        as sampleDispatchedOn,
        vl.result_dispatched_datetime                        as resultDispatchedOn,
        vl.is_sample_rejected                                as isSampleRejected,
        COALESCE(vl.result,null)                             as result,
        vl.tested_by                                         as testedBy,
        vl.result_approved_by                                as approvedBy,
        vl.result_approved_datetime                          as approvedOn,
        vl.lab_tech_comments                                 as approverComments,
        vl.result_status                                     as status,
        l_f.facility_name                                    as labName,
        f.facility_district                                  as district,
        f.facility_district_id                               as districtId,
        f.facility_name                                      as facilityName,
        u_d.user_name                                        as reviewedBy,
        vl.result_reviewed_datetime                          as resultReviewedDatetime,
        lt_u_d.user_name                                     as labTechnicianName,
        t_b.user_name                                        as testedByName,
        vl.reason_for_sample_rejection                       as sampleRejectionReason,
        vl.request_created_datetime                          as requestCreatedDatetime,
        vl.rejection_on                                      as rejectionDate,
        p.province_name                                      as provinceName,
        r_f_s.funding_source_name                            as fundingSourceName,
        r_i_p.i_partner_name                                 as implementingPartnerName,
        ts.status_name                                       as resultStatusName,
        vl.revised_by                                        as revisedBy,
        r_r_b.user_name                                      as revisedByName,
        vl.revised_on                                        as revisedOn,
        vl.reason_for_changing                               as reasonForEidResultChanges
        
        FROM form_eid as vl 
        LEFT JOIN province_details as p ON vl.province_id=p.province_id
        LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
        LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
        LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
        LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
        LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
        LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
        LEFT JOIN user_details as r_r_b ON r_r_b.user_id=vl.revised_by 
        LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_technician 
        LEFT JOIN user_details as t_b ON t_b.user_id=vl.tested_by 
        LEFT JOIN r_eid_test_reasons as rtr ON rtr.test_reason_id=vl.reason_for_eid_test 
        LEFT JOIN r_eid_sample_type as rst ON rst.sample_id=vl.specimen_type 
        LEFT JOIN r_eid_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
        LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source 
        LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";


    $where = array();
    if (!empty($user)) {
        $facilityMap = $facilityDb->getFacilityMap($user['user_id'], 1);
        if (!empty($facilityMap)) {
            $where[] = " vl.facility_id IN (" . $facilityMap . ")";
        } else {
            $where[] = " (request_created_by = '" . $user['user_id'] . "' OR vlsm_country_id = '" . $arr['vl_form'] . "')";
        }
    }
    /* To check the sample code filter */

    if (!empty($input['sampleCode'])) {
        $sampleCode = $input['sampleCode'];
        $sampleCode = implode("','", $sampleCode);
        $where[] = " (sample_code IN ('$sampleCode') OR remote_sample_code IN ('$sampleCode') )";
    }
    /* To check the facility and date range filter */
    if (!empty($input['sampleCollectionDate'])) {
        $from = $input['sampleCollectionDate'][0];
        $to = $input['sampleCollectionDate'][1];
        if (!empty($from) && !empty($to)) {
            $where[] = " DATE(sample_collection_date) between '$from' AND '$to' ";
        }
    }

    if (!empty($input['facility'])) {
        $facilityId = implode("','", $input['facility']);
        $where[] = " vl.facility_id IN ('$facilityId') ";
    }

    if (!empty($input['childId'])) {
        $childId = implode("','", $input['childId']);
        $where[] = " vl.child_id IN ('" . $childId . "') ";
    }

    if (!empty($input['childName'])) {
        $where[] = " CONCAT(COALESCE(vl.child_name,''), COALESCE(vl.child_surname,'')) like '%" . $input['childName'] . "%'";
    }

    if (!empty($input['sampleStatus'])) {
        $sampleStatus = $input['sampleStatus'];
        $sampleStatus = implode("','", $sampleStatus);
        $where[] = " result_status IN ('$sampleStatus') ";
    }

    $where = " WHERE " . implode(" AND ", $where);
    $sQuery .= $where . " GROUP BY eid_id ORDER BY last_modified_datetime DESC limit 100;";
    // die($sQuery);
    $rowData = $db->rawQuery($sQuery);

    // No data found
    if (!$rowData) {
        $response = array(
            'status' => 'success',
            'timestamp' => time(),
            'error' => 'No matching data',
            'data' => $rowData

        );

        $app = new \Vlsm\Models\App();
        $trackId = $app->addApiTracking($user['user_id'], count($rowData), 'get-requests', 'eid', $requestUrl, $params, 'json');
        http_response_code(200);
        echo json_encode($response);
        exit(0);
    }
    $payload = array(
        'status' => 'success',
        'timestamp' => time(),
        'data' => $rowData
    );
    if (isset($user['token_updated']) && $user['token_updated'] == true) {
        $payload['token'] = $user['new_token'];
    } else {
        $payload['token'] = null;
    }
    http_response_code(200);
    echo json_encode($payload);
    exit(0);
} catch (Exception $exc) {

    http_response_code(500);
    $payload = array(
        'status' => 'success',
        'timestamp' => time(),
        'error' => $exc->getMessage(),
        'data' => array()
    );
    echo json_encode($payload);

    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}