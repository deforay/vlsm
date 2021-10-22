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
session_unset(); // no need of session in json response

try {
    ini_set('memory_limit', -1);
    header('Content-Type: application/json');
    $general = new \Vlsm\Models\General($db);
    $userDb = new \Vlsm\Models\Users($db);
    $app = new \Vlsm\Models\App($db);
    $vlModel = new \Vlsm\Models\Vl($db);
    $globalConfig = $general->getGlobalConfig();
    $vlsmSystemConfig = $general->getSystemConfig();
    $user = null;

    $input = json_decode(file_get_contents("php://input"), true);

    /* For API Tracking params */
    $requestUrl = $_SERVER['REQUEST_URI'];
    $params = file_get_contents("php://input");

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
    $roleUser = $userDb->getRoleDetailsUsingUserId($user['user_id']);

    foreach ($input['data'] as $rootKey => $field) {
        $data = $field;
        $sampleFrom = '';
        $data['formId'] = $data['countryId'] = $general->getGlobalConfig('vl_form');
        $sQuery = "SELECT vlsm_instance_id from s_vlsm_instance";
        $rowData = $db->rawQuery($sQuery);
        $data['instanceId'] = $rowData[0]['vlsm_instance_id'];
        $sampleFrom = '';
        /* V1 name to Id mapping */
        if (!is_numeric($data['provinceId'])) {
            $province = explode("##", $data['provinceId']);
            if (isset($province) && count($province) > 0) {
                $data['provinceId'] = $province[0];
            }
            $data['provinceId'] = $general->getValueByName($data['provinceId'], 'province_name', 'province_details', 'province_id', true);
        }
        if (!is_numeric($data['implementingPartner'])) {
            $data['implementingPartner'] = $general->getValueByName($data['implementingPartner'], 'i_partner_name', 'r_implementation_partners', 'i_partner_id');
        }
        if (!is_numeric($data['fundingSource'])) {
            $data['fundingSource'] = $general->getValueByName($data['fundingSource'], 'funding_source_name', 'r_funding_sources', 'funding_source_id');
        }

        $data['api'] = "yes";
        $provinceCode = (isset($data['provinceCode']) && !empty($data['provinceCode'])) ? $data['provinceCode'] : null;
        $provinceId = (isset($data['provinceId']) && !empty($data['provinceId'])) ? $data['provinceId'] : null;
        $sampleCollectionDate = (isset($data['sampleCollectionDate']) && !empty($data['sampleCollectionDate'])) ? $data['sampleCollectionDate'] : null;

        if (empty($sampleCollectionDate)) {
            exit();
        }

        $update = "no";
        $rowData = false;
        if ((isset($data['sampleCode']) && !empty($data['sampleCode'])) || (isset($data['remoteSampleCode']) && !empty($data['uniqueId'])) || (isset($data['uniqueId']) && !empty($data['uniqueId']))) {
            $sQuery = "SELECT vl_sample_id, sample_code, sample_code_format, sample_code_key, remote_sample_code, remote_sample_code_format, remote_sample_code_key FROM vl_request_form ";
            if (isset($data['uniqueId']) && !empty($data['uniqueId'])) {
                $sQuery .= "where unique_id like '" . $data['uniqueId'] . "'";
            } else if (isset($data['sampleCode']) && !empty($data['sampleCode'])) {
                $sQuery .= "where sample_code like '" . $data['sampleCode'] . "'";
            } else if (isset($data['remoteSampleCode']) != "" && !empty($data['remoteSampleCode'])) {
                $sQuery .= "where remote_sample_code like '" . $data['sampleCode'] . "'";
            }
            $sQuery .= "limit 1";
            $rowData = $db->rawQueryOne($sQuery);
            if ($rowData) {
                $update = "yes";
                $sampleData['sampleCode'] = (!empty($rowData['sample_code'])) ? $rowData['sample_code'] : $rowData['remote_sample_code'];
                $sampleData['sampleCodeFormat'] = (!empty($rowData['sample_code_format'])) ? $rowData['sample_code_format'] : $rowData['remote_sample_code_format'];
                $sampleData['sampleCodeKey'] = (!empty($rowData['sample_code_key'])) ? $rowData['sample_code_key'] : $rowData['remote_sample_code_key'];
            } else {
                $sampleJson = $app->generateSampleCode($provinceCode, $sampleCollectionDate, null, $provinceId, null, $user, 'vl');
                $sampleData = json_decode($sampleJson, true);
            }
        } else {
            $sampleJson = $app->generateSampleCode($provinceCode, $sampleCollectionDate, null, $provinceId, null, $user, 'vl');
            $sampleData = json_decode($sampleJson, true);
        }
        if (!isset($data['countryId']) || $data['countryId'] == '') {
            $data['countryId'] = '';
        }
        $vlData = array(
            'vlsm_country_id' => $data['countryId'],
            'sample_collection_date' => $data['sampleCollectionDate'],
            'vlsm_instance_id' => $data['instanceId'],
            'province_id' => $provinceId,
            'request_created_by' => '',
            'request_created_datetime' => $general->getDateTime(),
            'last_modified_by' => '',
            'last_modified_datetime' => $general->getDateTime()
        );
        $vlData['remote_sample_code'] = "";
        $vlData['sample_code'] = "";

        if ($roleUser['access_type'] != 'testing-lab') {
            $vlData['remote_sample_code'] = $sampleData['sampleCode'];
            $vlData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
            $vlData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
            $vlData['remote_sample'] = 'yes';
            $vlData['result_status'] = 9;
        } else {
            $vlData['sample_code'] = $sampleData['sampleCode'];
            $vlData['sample_code_format'] = $sampleData['sampleCodeFormat'];
            $vlData['sample_code_key'] = $sampleData['sampleCodeKey'];
            $vlData['remote_sample'] = 'no';
            $vlData['result_status'] = 6;
        }

        $id = 0;
        if ($rowData) {
            $db = $db->where('vl_sample_id', $rowData['vl_sample_id']);
            $id = $db->update("vl_request_form", $vlData);
            $data['vlSampleId'] = $rowData['vl_sample_id'];
        } else {
            $id = $db->insert("vl_request_form", $vlData);
            $data['vlSampleId'] = $id;
        }
        $tableName = "vl_request_form";
        $tableName1 = "activity_log";

        $instanceId = '';
        if (empty($instanceId) && $data['instanceId']) {
            $instanceId = $data['instanceId'];
        }

        if (empty(trim($data['sampleCode']))) {
            $data['sampleCode'] = NULL;
        }

        $status = 6;
        if ($roleUser['access_type'] != 'testing-lab') {
            $status = 9;
        }

        if (isset($data['isSampleRejected']) && $data['isSampleRejected'] == 'yes') {
            $data['result'] = null;
            $status = 4;
        }

        if (!empty($data['sampleCollectionDate']) && trim($data['sampleCollectionDate']) != "") {
            $sampleCollectionDate = explode(" ", $data['sampleCollectionDate']);
            $data['sampleCollectionDate'] = $general->dateFormat($sampleCollectionDate[0]) . " " . $sampleCollectionDate[1];
        } else {
            $data['sampleCollectionDate'] = NULL;
        }

        if (isset($data['approvedOnDateTime']) && trim($data['approvedOnDateTime']) != "") {
            $approvedOnDateTime = explode(" ", $data['approvedOnDateTime']);
            $data['approvedOnDateTime'] = $general->dateFormat($approvedOnDateTime[0]) . " " . $approvedOnDateTime[1];
        } else {
            $data['approvedOnDateTime'] = NULL;
        }

        if (isset($data['resultDispatchedOn']) && trim($data['resultDispatchedOn']) != "") {
            $resultDispatchedOn = explode(" ", $data['resultDispatchedOn']);
            $data['resultDispatchedOn'] = $general->dateFormat($resultDispatchedOn[0]) . " " . $resultDispatchedOn[1];
        } else {
            $data['resultDispatchedOn'] = NULL;
        }

        //Set sample received date
        if (!empty($data['sampleReceivedDate']) && trim($data['sampleReceivedDate']) != "") {
            $sampleReceivedDate = explode(" ", $data['sampleReceivedDate']);
            $data['sampleReceivedDate'] = $general->dateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
        } else {
            $data['sampleReceivedDate'] = NULL;
        }
        if (!empty($data['sampleTestedDateTime']) && trim($data['sampleTestedDateTime']) != "") {
            $sampleTestedDate = explode(" ", $data['sampleTestedDateTime']);
            $data['sampleTestedDateTime'] = $general->dateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
        } else {
            $data['sampleTestedDateTime'] = NULL;
        }

        if (!empty($data['sampleTestingDateAtLab']) && trim($data['sampleTestingDateAtLab']) != "") {
            $sampleTestedDate = explode(" ", $data['sampleTestingDateAtLab']);
            $data['sampleTestingDateAtLab'] = $general->dateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
        } else {
            $data['sampleTestingDateAtLab'] = NULL;
        }

        if (!empty($data['sampleReceivedAtHubOn']) && trim($data['sampleReceivedAtHubOn']) != "") {
            $sampleReceivedAtHubOn = explode(" ", $data['sampleReceivedAtHubOn']);
            $data['sampleReceivedAtHubOn'] = $general->dateFormat($sampleReceivedAtHubOn[0]) . " " . $sampleReceivedAtHubOn[1];
        } else {
            $data['sampleReceivedAtHubOn'] = NULL;
        }

        if (isset($data['dateOfArtInitiation']) && trim($data['dateOfArtInitiation']) != "") {
            $dateOfArtInitiation = explode(" ", $data['dateOfArtInitiation']);
            $data['dateOfArtInitiation'] = $general->dateFormat($dateOfArtInitiation[0]) . " " . $dateOfArtInitiation[1];
        } else {
            $data['dateOfArtInitiation'] = NULL;
        }

        if (isset($data['patientDob']) && trim($data['patientDob']) != "") {
            $dob = explode(" ", $data['patientDob']);
            $data['patientDob'] = $general->dateFormat($dob[0]) . " " . $dob[1];
        } else {
            $data['patientDob'] = NULL;
        }

        if (isset($data['regimenInitiatedOn']) && trim($data['regimenInitiatedOn']) != "") {
            $regimenInitiatedOn = explode(" ", $data['regimenInitiatedOn']);
            $data['regimenInitiatedOn'] = $general->dateFormat($regimenInitiatedOn[0]) . " " . $regimenInitiatedOn[1];
        } else {
            $data['regimenInitiatedOn'] = NULL;
        }

        if (isset($data['patientGender']) && trim($data['patientGender']) == 'male') {
            $data['patientPregnant'] = '';
            $data['breastfeeding'] = '';
        }

        if (isset($data['tnd']) && $data['tnd'] == 'yes' && $data['isSampleRejected'] == 'no') {
            $data['vlResult'] = 'Target Not Detected';
            $data['vlLog'] = '';
        }
        if (isset($data['bdl']) && $data['bdl'] == 'bdl'  && $data['isSampleRejected'] == 'no') {
            $data['vlResult'] = 'Below Detection Level';
            $data['vlLog'] = '';
        }

        $data['result'] = '';
        if (isset($data['vlResult']) && trim($data['vlResult']) != '') {
            $data['result'] = $data['vlResult'];
        } else if ($data['vlLog'] != '') {
            $data['result'] = $data['vlLog'];
        }

        if (!empty($data['revisedOn']) && trim($data['revisedOn']) != "") {
            $revisedOn = explode(" ", $data['revisedOn']);
            $data['revisedOn'] = $general->dateFormat($revisedOn[0]) . " " . $revisedOn[1];
        } else {
            $data['revisedOn'] = NULL;
        }

        $vlFulldata = array(
            'vlsm_instance_id'                      => $instanceId,
            'vlsm_country_id'                       => $data['formId'],
            'unique_id'                             => isset($data['uniqueId']) ? $data['uniqueId'] : null,
            'app_local_test_req_id'                 => isset($data['localTestReqID']) ? $data['localTestReqID'] : null,
            'sample_code_title'                     => (isset($data['sampleCodeTitle']) && $data['sampleCodeTitle'] != '') ? $data['sampleCodeTitle'] :  'auto',
            'sample_reordered'                      => (isset($data['sampleReordered']) && $data['sampleReordered'] != '') ? $data['sampleReordered'] :  'no',
            'sample_code_format'                    => (isset($data['sampleCodeFormat']) && $data['sampleCodeFormat'] != '') ? $data['sampleCodeFormat'] :  NULL,
            'facility_id'                           => (isset($data['facilityId']) && $data['facilityId'] != '') ? $data['facilityId'] :  NULL,
            'sample_collection_date'                => $data['sampleCollectionDate'],
            'patient_Gender'                        => (isset($data['patientGender']) && $data['patientGender'] != '') ? $data['patientGender'] :  NULL,
            'patient_dob'                           => $data['patientDob'],
            'patient_age_in_years'                  => (isset($data['ageInYears']) && $data['ageInYears'] != '') ? $data['ageInYears'] :  NULL,
            'patient_age_in_months'                 => (isset($data['ageInMonths']) && $data['ageInMonths'] != '') ? $data['ageInMonths'] :  NULL,
            'is_patient_pregnant'                   => (isset($data['patientPregnant']) && $data['patientPregnant'] != '') ? $data['patientPregnant'] :  NULL,
            'is_patient_breastfeeding'              => (isset($data['breastfeeding']) && $data['breastfeeding'] != '') ? $data['breastfeeding'] :  NULL,
            'patient_art_no'                        => (isset($data['patientArtNo']) && $data['patientArtNo'] != '') ? $data['patientArtNo'] :  NULL,
            'treatment_initiated_date'              => $data['dateOfArtInitiation'],
            'current_regimen'                       => (isset($data['artRegimen']) && $data['artRegimen'] != '') ? $data['artRegimen'] :  NULL,
            'date_of_initiation_of_current_regimen' => $data['regimenInitiatedOn'],
            'patient_mobile_number'                 => (isset($data['patientPhoneNumber']) && $data['patientPhoneNumber'] != '') ? $data['patientPhoneNumber'] :  NULL,
            'consent_to_receive_sms'                => (isset($data['receiveSms']) && $data['receiveSms'] != '') ? $data['receiveSms'] :  NULL,
            'sample_type'                           => (isset($data['specimenType']) && $data['specimenType'] != '') ? $data['specimenType'] :  NULL,
            'arv_adherance_percentage'              => (isset($data['arvAdherence']) && $data['arvAdherence'] != '') ? $data['arvAdherence'] :  NULL,
            'reason_for_vl_testing'                 => (isset($data['stViralTesting'])) ? $data['stViralTesting'] : NULL,
            'last_vl_date_routine'                  => (isset($data['rmTestingLastVLDate']) && $data['rmTestingLastVLDate'] != '') ? $general->dateFormat($data['rmTestingLastVLDate']) :  NULL,
            'last_vl_result_routine'                => (isset($data['rmTestingVlValue']) && $data['rmTestingVlValue'] != '') ? $data['rmTestingVlValue'] :  NULL,
            'last_vl_date_failure_ac'               => (isset($data['repeatTestingLastVLDate']) && $data['repeatTestingLastVLDate'] != '') ? $general->dateFormat($data['repeatTestingLastVLDate']) :  NULL,
            'last_vl_result_failure_ac'             => (isset($data['repeatTestingVlValue']) && $data['repeatTestingVlValue'] != '') ? $data['repeatTestingVlValue'] :  NULL,
            'last_vl_date_failure'                  => (isset($data['suspendTreatmentLastVLDate']) && $data['suspendTreatmentLastVLDate'] != '') ? $general->dateFormat($data['suspendTreatmentLastVLDate']) :  NULL,
            'last_vl_result_failure'                => (isset($data['suspendTreatmentVlValue']) && $data['suspendTreatmentVlValue'] != '') ? $data['suspendTreatmentVlValue'] :  NULL,
            'request_clinician_name'                => (isset($data['reqClinician']) && $data['reqClinician'] != '') ? $data['reqClinician'] :  NULL,
            'request_clinician_phone_number'        => (isset($data['reqClinicianPhoneNumber']) && $data['reqClinicianPhoneNumber'] != '') ? $data['reqClinicianPhoneNumber'] :  NULL,
            'test_requested_on'                     => (isset($data['requestDate']) && $data['requestDate'] != '') ? $general->dateFormat($data['requestDate']) :  NULL,
            'vl_focal_person'                       => (isset($data['vlFocalPerson']) && $data['vlFocalPerson'] != '') ? $data['vlFocalPerson'] :  NULL,
            'vl_focal_person_phone_number'          => (isset($data['vlFocalPersonPhoneNumber']) && $data['vlFocalPersonPhoneNumber'] != '') ? $data['vlFocalPersonPhoneNumber'] :  NULL,
            'lab_id'                                => (isset($data['labId']) && $data['labId'] != '') ? $data['labId'] :  NULL,
            'vl_test_platform'                      => (isset($data['testingPlatform']) && $data['testingPlatform'] != '') ? $data['testingPlatform'] :  NULL,
            'sample_received_at_hub_datetime'       => $data['sampleReceivedAtHubOn'],
            'sample_received_at_vl_lab_datetime'    => $data['sampleReceivedDate'],
            'sample_tested_datetime'                => $data['sampleTestingDateAtLab'],
            'result_dispatched_datetime'            => $data['resultDispatchedOn'],
            'is_sample_rejected'                    => (isset($data['isSampleRejected']) && $data['isSampleRejected'] != '') ? $data['isSampleRejected'] : NULL,
            'reason_for_sample_rejection'           => (isset($data['rejectionReason']) && $data['rejectionReason'] != '') ? $data['rejectionReason'] :  NULL,
            'rejection_on'                          => (isset($_POST['rejectionDate']) && $_POST['isSampleRejected'] == 'yes') ? $general->dateFormat($_POST['rejectionDate']) : null,
            'result_value_absolute'                 => (isset($data['vlResult']) && $data['vlResult'] != '' && ($data['vlResult'] != 'Target Not Detected' && $data['vlResult'] != 'Below Detection Level')) ? $data['vlResult'] :  NULL,
            'result_value_absolute_decimal'         => (isset($data['vlResult']) && $data['vlResult'] != '' && ($data['vlResult'] != 'Target Not Detected' && $data['vlResult'] != 'Below Detection Level')) ? number_format((float)$data['vlResult'], 2, '.', '') :  NULL,
            'result'                                => (isset($data['result']) && $data['result'] != '') ? $data['result'] :  NULL,
            'result_value_log'                      => (isset($data['vlLog']) && $data['vlLog'] != '') ? $data['vlLog'] :  NULL,
            'tested_by'                             => (isset($data['testedBy']) && $data['testedBy'] != '') ? $data['testedBy'] :  NULL,
            'result_approved_by'                    => (isset($data['approvedBy']) && $data['approvedBy'] != '') ? $data['approvedBy'] :  NULL,
            'result_approved_datetime'              => (isset($data['approvedBy']) && $data['approvedBy'] != '') ? $data['approvedOnDateTime'] :  NULL,
            'revised_by'                            => (isset($_POST['revisedBy']) && $_POST['revisedBy'] != "") ? $_POST['revisedBy'] : "",
            'revised_on'                            => (isset($_POST['revisedOn']) && $_POST['revisedOn'] != "") ? $_POST['revisedOn'] : "",
            'reason_for_vl_result_changes'          => (!empty($_POST['reasonForVlResultChanges']) && !empty($_POST['reasonForVlResultChanges'])) ? $_POST['reasonForVlResultChanges'] : null,
            'approver_comments'                     => (isset($data['labComments']) && trim($data['labComments']) != '') ? trim($data['labComments']) :  NULL,
            'result_status'                         => $status,
            'funding_source'                        => (isset($data['fundingSource']) && trim($data['fundingSource']) != '') ? base64_decode($data['fundingSource']) : NULL,
            'implementing_partner'                  => (isset($data['implementingPartner']) && trim($data['implementingPartner']) != '') ? base64_decode($data['implementingPartner']) : NULL,
            'request_created_datetime'              => $general->getDateTime(),
            'last_modified_datetime'                => $general->getDateTime(),
            'manual_result_entry'                   => 'yes',
            'vl_result_category'                    => (isset($data['isSampleRejected']) && $data['isSampleRejected'] == 'yes') ? "rejected" : "",
            'source_of_request'                     => "api"
        );
        if (isset($data['patientFullName']) && $data['patientFullName'] != "") {
            $vlFulldata['patient_first_name'] = $general->crypto('encrypt', $data['patientFullName'], $vlFulldata['patient_art_no']);
        }
        if (isset($data['patientMiddleName']) && $data['patientMiddleName'] != "") {
            $vlFulldata['patient_middle_name'] = $general->crypto('encrypt', $data['patientMiddleName'], $vlFulldata['patient_art_no']);
        }
        if (isset($data['patientLastName']) && $data['patientLastName'] != "") {
            $vlFulldata['patient_last_name'] = $general->crypto('encrypt', $data['patientLastName'], $vlFulldata['patient_art_no']);
        }
        if ($rowData) {
            $vlFulldata['last_modified_datetime']  = $general->getDateTime();
            $vlFulldata['last_modified_by']  = $user['user_id'];
        } else {
            $vlFulldata['sample_registered_at_lab']  = $general->getDateTime();
            $vlFulldata['request_created_by']  = $user['user_id'];
        }
        $lock = $general->getGlobalConfig('lock_approved_eid_samples');
        if ($status == 7 && $lock == 'yes') {
            $vlFulldata['locked'] = 'yes';
        }
        $vlFulldata['request_created_by'] =  $user['user_id'];
        $vlFulldata['last_modified_by'] =  $user['user_id'];

        /* echo "<pre>";
        print_r($vlFulldata);
        die; */
        $id = 0;
        if (!empty($data['vlSampleId'])) {
            $db = $db->where('vl_sample_id', $data['vlSampleId']);
            $id = $db->update($tableName, $vlFulldata);
        }
        if ($id > 0) {
            $vlFulldata = $app->getTableDataUsingId($tableName, 'vl_sample_id', $data['vlSampleId']);
            $vlSampleCode = (isset($vlFulldata['sample_code']) && $vlFulldata['sample_code']) ? $vlFulldata['sample_code'] : $vlFulldata['remote_sample_code'];
            if (isset($data['localTestReqID']) && $data['localTestReqID'] != "") {
                $responseData[$rootKey] = array(
                    'status' => 'success',
                    'sampleCode' => $vlSampleCode,
                    'localTestReqID' => $vlFulldata['app_local_test_req_id'],
                );
            } else {
                $responseData[$rootKey] = array(
                    'sampleCode' => $vlSampleCode,
                    'localTestReqID' => $vlFulldata['app_local_test_req_id'],
                );
            }
            http_response_code(200);
        } else {
            if (isset($data['localTestReqID']) && $data['localTestReqID'] != "") {
                $responseData[$rootKey] = array(
                    'status' => 'failed'
                );
            } else {
                $payload = array(
                    'status' => 'failed',
                    'timestamp' => time(),
                    'error' => 'Unable to add this VL sample. Please try again later',
                    'data' => array()
                );
            }
            http_response_code(301);
        }
        $app = new \Vlsm\Models\App($db);
        $trackId = $app->addApiTracking($user['user_id'], $data['vlSampleId'], 'save-request', 'VL', $requestUrl, $params, 'json');
    }
    if ($update == "yes") {
        $msg = 'Successfully updated.';
    } else {
        $msg = 'Successfully added.';
    }
    if (isset($responseData) && count($responseData) > 0) {
        $payload = array(
            'status' => 'success',
            'timestamp' => time(),
            'message' => $msg,
            'data'  => $responseData
        );
    } else {
        $payload = array(
            'status' => 'success',
            'timestamp' => time(),
            'message' => $msg
        );
    }
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


    echo json_encode($payload);

    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}
