<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\VlService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/** @var MysqliDb $db */
/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var VlService $vlService */
$vlService = ContainerRegistry::get(VlService::class);
$tableName = "generic_tests";
$tableName1 = "activity_log";
$vlTestReasonTable = "r_vl_test_reasons";
$fDetails = "facility_details";
$vl_result_category = null;

$systemType = $general->getSystemConfig('sc_user_type');

try {
    if (isset($_POST['api']) && $_POST['api'] == "yes") {
    } else {
        $validateField = array($_POST['sampleCode'], $_POST['sampleCollectionDate']);
        $chkValidation = $general->checkMandatoryFields($validateField);
        if ($chkValidation) {
            $_SESSION['alertMsg'] = _("Please enter all mandatory fields to save the test request");
            header("Location:addVlRequest.php");
            die;
        }
    }

    $resultStatus = 6;
  
    if ($_SESSION['instanceType'] == 'remoteuser' && $_SESSION['accessType'] == 'collection-site') {
        $resultStatus = 9;
    }
    $countryFormId = '';
    if(isset($_POST['countryFormId']) && $_POST['countryFormId']!="")
        $countryFormId = $_POST['countryFormId'];
    //add province
    $splitProvince = explode("##", $_POST['province']);
    if (isset($splitProvince[0]) && trim($splitProvince[0]) != '') {
        $provinceQuery = "SELECT * from geographical_divisions where geo_name='" . $splitProvince[0] . "'";
        $provinceInfo = $db->query($provinceQuery);
        if (!isset($provinceInfo) || count($provinceInfo) == 0) {
            $db->insert('geographical_divisions', array('geo_name' => $splitProvince[0], 'geo_code' => $splitProvince[1]));
        }
    }
    if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != "") {
        $_POST['sampleCollectionDate'] = DateUtility::isoDateFormat($_POST['sampleCollectionDate'], true);
    } else {
        $_POST['sampleCollectionDate'] = null;
    }
    if (isset($_POST['sampleDispatchedDate']) && trim($_POST['sampleDispatchedDate']) != "") {
        $_POST['sampleDispatchedDate'] = DateUtility::isoDateFormat($_POST['sampleDispatchedDate'], true);
    } else {
        $_POST['sampleDispatchedDate'] = null;
    }
    if (isset($_POST['dob']) && trim($_POST['dob']) != "") {
        $_POST['dob'] = DateUtility::isoDateFormat($_POST['dob']);
    } else {
        $_POST['dob'] = null;
    }
    if(isset($_POST['countryFormId']) && $_POST['countryFormId']=='3'){
        if (!isset($_POST['isPatientNew']) || trim($_POST['isPatientNew']) == '') {
            $_POST['isPatientNew'] = null;
            $_POST['dateOfArtInitiation'] = null;
        } else if ($_POST['isPatientNew'] == "yes") {
            //Ser ARV initiation date
            if (isset($_POST['dateOfArtInitiation']) && trim($_POST['dateOfArtInitiation']) != "") {
                $_POST['dateOfArtInitiation'] = DateUtility::isoDateFormat($_POST['dateOfArtInitiation']);
            } else {
                $_POST['dateOfArtInitiation'] = null;
            }
        } else if ($_POST['isPatientNew'] == "no") {
            $_POST['dateOfArtInitiation'] = null;
        }
    }
    else
    {
        if (isset($_POST['dateOfArtInitiation']) && trim($_POST['dateOfArtInitiation']) != "") {
        $_POST['dateOfArtInitiation'] = DateUtility::isoDateFormat($_POST['dateOfArtInitiation']);
        } else {
            $_POST['dateOfArtInitiation'] = null;
        }
    }

    if (isset($_POST['regimenInitiatedOn']) && trim($_POST['regimenInitiatedOn']) != "") {
        $_POST['regimenInitiatedOn'] = DateUtility::isoDateFormat($_POST['regimenInitiatedOn']);
    } else {
        $_POST['regimenInitiatedOn'] = null;
    }

    if (isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen']) != "") {
        $artQuery = "SELECT art_id, art_code FROM r_vl_art_regimen
                        WHERE (art_code='" . $_POST['newArtRegimen'] . "' OR art_code='" . strtolower($_POST['newArtRegimen']) . "' OR art_code='" . (strtolower($_POST['newArtRegimen'])) . "')";
        $artResult = $db->rawQuery($artQuery);
        if (!isset($artResult[0]['art_id'])) {
            $data = array(
                'art_code' => $_POST['newArtRegimen'],
                'parent_art' => $countryFormId,
                'updated_datetime' => DateUtility::getCurrentDateTime(),
            );
            $result = $db->insert('r_vl_art_regimen', $data);
            $_POST['artRegimen'] = $_POST['newArtRegimen'];
        } else {
            $_POST['artRegimen'] = $artResult[0]['art_code'];
        }
    }

    if (!isset($_POST['hasChangedRegimen']) || trim($_POST['hasChangedRegimen']) == '') {
        $_POST['hasChangedRegimen'] = null;
        $_POST['reasonForArvRegimenChange'] = null;
        $_POST['dateOfArvRegimenChange'] = null;
    }
    if (trim($_POST['hasChangedRegimen']) == "no") {
        $_POST['reasonForArvRegimenChange'] = null;
        $_POST['dateOfArvRegimenChange'] = null;
    } else if (trim($_POST['hasChangedRegimen']) == "yes") {
        if (isset($_POST['dateOfArvRegimenChange']) && trim($_POST['dateOfArvRegimenChange']) != "") {
            $_POST['dateOfArvRegimenChange'] = DateUtility::isoDateFormat($_POST['dateOfArvRegimenChange']);
        }
    }

    //Set last VL test date
    if (isset($_POST['lastViralLoadTestDate']) && trim($_POST['lastViralLoadTestDate']) != "") {
        $_POST['lastViralLoadTestDate'] = DateUtility::isoDateFormat($_POST['lastViralLoadTestDate']);
    } else {
        $_POST['lastViralLoadTestDate'] = null;
    }

     //Sample type section
     if (isset($_POST['specimenType']) && trim($_POST['specimenType']) != "") {
        if (trim($_POST['specimenType']) != 2) {
            $_POST['conservationTemperature'] = null;
            $_POST['durationOfConservation'] = null;
        }
    } else {
        $_POST['specimenType'] = null;
        $_POST['conservationTemperature'] = null;
        $_POST['durationOfConservation'] = null;
    }

    //update facility code
    if (isset($_POST['fCode']) && trim($_POST['fCode']) != '') {
        $fData = array('facility_code' => $_POST['fCode']);
        $db = $db->where('facility_id', $_POST['fName']);
        $id = $db->update($fDetails, $fData);
    }
    //update facility emails
    //if(trim($_POST['emailHf'])!=''){
    //   $fData = array('facility_emails'=>$_POST['emailHf']);
    //   $db=$db->where('facility_id',$_POST['fName']);
    //   $id=$db->update($fDetails,$fData);
    //}
    if (isset($_POST['gender']) && trim($_POST['gender']) == 'male') {
        $_POST['patientPregnant'] = '';
        $_POST['breastfeeding'] = '';
    }
    $instanceId = '';
    if (isset($_SESSION['instanceId'])) {
        $instanceId = $_SESSION['instanceId'];
    }

    if (empty($instanceId) && $_POST['instanceId']) {
        $instanceId = $_POST['instanceId'];
    }
    $testingPlatform = '';
    if (isset($_POST['testingPlatform']) && trim($_POST['testingPlatform']) != '') {
        $platForm = explode("##", $_POST['testingPlatform']);
        $testingPlatform = $platForm[0];
    }
    if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != "") {
        $_POST['sampleReceivedDate'] = DateUtility::isoDateFormat($_POST['sampleReceivedDate'], true);
    } else {
        $_POST['sampleReceivedDate'] = null;
    }
    if (isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab']) != "") {
        $_POST['sampleTestingDateAtLab'] = DateUtility::isoDateFormat($_POST['sampleTestingDateAtLab'], true);
    } else {
        $_POST['sampleTestingDateAtLab'] = null;
    }

    if (isset($_POST['sampleReceivedAtHubOn']) && trim($_POST['sampleReceivedAtHubOn']) != "") {
        $_POST['sampleReceivedAtHubOn'] = DateUtility::isoDateFormat($_POST['sampleReceivedAtHubOn'], true);
    } else {
        $_POST['sampleReceivedAtHubOn'] = null;
    }

    if (isset($_POST['approvedOn']) && trim($_POST['approvedOn']) != "") {
        $_POST['approvedOn'] = DateUtility::isoDateFormat($_POST['approvedOn'], true);
    } else {
        $_POST['approvedOn'] = null;
    }
    if (isset($_POST['dateOfDemand']) && trim($_POST['dateOfDemand']) != "") {
        $_POST['dateOfDemand'] = DateUtility::isoDateFormat($_POST['dateOfDemand']);
    } else {
        $_POST['dateOfDemand'] = null;
    }

    if (isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn']) != "") {
        $_POST['resultDispatchedOn'] = DateUtility::isoDateFormat($_POST['resultDispatchedOn'], true);
    } else {
        $_POST['resultDispatchedOn'] = null;
    }

    if (isset($_POST['newRejectionReason']) && trim($_POST['newRejectionReason']) != "") {
        $rejectionReasonQuery = "SELECT rejection_reason_id FROM r_vl_sample_rejection_reasons where rejection_reason_name='" . $_POST['newRejectionReason'] . "' OR rejection_reason_name='" . strtolower($_POST['newRejectionReason']) . "' OR rejection_reason_name='" . (strtolower($_POST['newRejectionReason'])) . "'";
        $rejectionResult = $db->rawQuery($rejectionReasonQuery);
        if (!isset($rejectionResult[0]['rejection_reason_id'])) {
            $data = array(
                'rejection_reason_name' => $_POST['newRejectionReason'],
                'rejection_type' => 'general',
                'rejection_reason_status' => 'active',
                'updated_datetime' => DateUtility::getCurrentDateTime(),
            );
            $id = $db->insert('r_vl_sample_rejection_reasons', $data);
            $_POST['rejectionReason'] = $id;
        } else {
            $_POST['rejectionReason'] = $rejectionResult[0]['rejection_reason_id'];
        }
    }

    $isRejected = false;
    if (isset($_POST['noResult']) && $_POST['noResult'] == 'yes') {
        $vl_result_category = 'rejected';
        $isRejected = true;
        $resultStatus = 4;
        $_POST['vlResult'] = '';
        $_POST['vlLog'] = '';

    }

    if (isset($_POST['vlResult']) && $_POST['vlResult'] == 'Below Detection Level' && $isRejected === false) {
        $finalResult = $_POST['vlResult'] = $_POST['vlResult']  ?: 'Below Detection Level';
        $_POST['vlResult'] = 'Below Detection Level';
        $_POST['vlLog'] = null;
    } else if ((isset($_POST['vlResult']) && $_POST['vlResult'] == 'Failed') || in_array(strtolower($_POST['vlResult']), ['fail', 'failed', 'failure'])) {
        $finalResult = $_POST['vlResult'] = $_POST['vlResult']  ?: 'Failed';
        $_POST['vlLog'] = null;
        $_POST['hivDetection'] = null;
        $resultStatus = 5; // Invalid/Failed
    } else if ((isset($_POST['vlResult']) && $_POST['vlResult'] == 'Error') || in_array(strtolower($_POST['vlResult']), ['error', 'err'])) {
        $finalResult = $_POST['vlResult'] = $_POST['vlResult']  ?: 'Error';
        $_POST['vlLog'] = null;
        $_POST['hivDetection'] = null;
        $resultStatus = 5; // Invalid/Failed
    } else if ((isset($_POST['vlResult']) && $_POST['vlResult'] == 'No Result') || in_array(strtolower($_POST['vlResult']), ['no result', 'no'])) {
        $finalResult = $_POST['vlResult'] = $_POST['vlResult']  ?: 'No Result';
        $_POST['vlLog'] = null;
        $_POST['hivDetection'] = null;
        $resultStatus = 11; // No Result
    } else if (isset($_POST['vlResult']) && trim(!empty($_POST['vlResult']))) {

        $resultStatus = 8; // Awaiting Approval

        $interpretedResults = $vlService->interpretViralLoadResult($_POST['vlResult']);

        //Result is saved as entered
        $finalResult  = $_POST['vlResult'];

        $logVal = $interpretedResults['logVal'];
        $absDecimalVal = $interpretedResults['absDecimalVal'];
        $absVal = $interpretedResults['absVal'];
        $txtVal = $interpretedResults['txtVal'];
    }

    $_POST['result'] = '';
    if (isset($_POST['vlResult']) && trim($_POST['vlResult']) != '') {
        $_POST['result'] = $_POST['vlResult'];
    } else if ($_POST['vlLog'] != '') {
        $_POST['result'] = $_POST['vlLog'];
    }

    if ($_SESSION['instanceType'] == 'remoteuser') {
        $sampleCode = 'remote_sample_code';
        $sampleCodeKey = 'remote_sample_code_key';
    } else {
        $sampleCode = 'sample_code';
        $sampleCodeKey = 'sample_code_key';
    }

    //set vl test reason
    if (isset($_POST['reasonForVLTesting']) && trim($_POST['reasonForVLTesting']) != "") {
        if(!is_numeric($_POST['reasonForVLTesting']))
        {
        $reasonQuery = "SELECT test_reason_id FROM r_vl_test_reasons
                        WHERE test_reason_name='" . $_POST['reasonForVLTesting'] . "'";
            $reasonResult = $db->rawQuery($reasonQuery);
            if (isset($reasonResult[0]['test_reason_id']) && $reasonResult[0]['test_reason_id'] != '') {
                $_POST['reasonForVLTesting'] = $reasonResult[0]['test_reason_id'];
            } else {
                $data = array(
                    'test_reason_name' => $_POST['reasonForVLTesting'],
                    'test_reason_status' => 'active'
                );
                $id = $db->insert('r_vl_test_reasons', $data);
                $_POST['reasonForVLTesting'] = $id;
            }
        }
    }

    if (isset($_POST['reviewedOn']) && trim($_POST['reviewedOn']) != "") {
        $reviewedOn = explode(" ", $_POST['reviewedOn']);
        $_POST['reviewedOn'] = DateUtility::isoDateFormat($reviewedOn[0]) . " " . $reviewedOn[1];
    } else {
        $_POST['reviewedOn'] = null;
    }

    if (isset($_POST['treatmentIndication']) && $_POST['treatmentIndication'] == "Other") {
        $_POST['treatmentIndication'] = $_POST['newTreatmentIndication'] . '_Other';
    }

    $finalResult = (isset($_POST['hivDetection']) && $_POST['hivDetection'] != '') ? $_POST['hivDetection'] . ' ' . $finalResult :  $finalResult;

    $vldata = array(
        'vlsm_instance_id'                      => $instanceId, 
        'vlsm_country_id'                       => 1,
        'sample_reordered'                      => (isset($_POST['sampleReordered']) && $_POST['sampleReordered'] != '') ? $_POST['sampleReordered'] :  'no',
        'sample_code_format'                    => (isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat'] != '') ? $_POST['sampleCodeFormat'] :  null,
        'external_sample_code'                  => (isset($_POST['serialNo']) && $_POST['serialNo'] != '' ? $_POST['serialNo'] : null),
        'facility_id'                           => (isset($_POST['fName']) && $_POST['fName'] != '') ? $_POST['fName'] :  null,
        'sample_collection_date'                => $_POST['sampleCollectionDate'],
        'sample_dispatched_datetime'            => $_POST['sampleDispatchedDate'],
        'patient_gender'                        => (isset($_POST['gender']) && $_POST['gender'] != '') ? $_POST['gender'] :  null,
        'patient_dob'                           => $_POST['dob'],
        'patient_age_in_years'                  => (isset($_POST['ageInYears']) && $_POST['ageInYears'] != '') ? $_POST['ageInYears'] :  null,
        'patient_age_in_months'                 => (isset($_POST['ageInMonths']) && $_POST['ageInMonths'] != '') ? $_POST['ageInMonths'] :  null,
        'is_patient_pregnant'                   => (isset($_POST['patientPregnant']) && $_POST['patientPregnant'] != '') ? $_POST['patientPregnant'] :  null,
        'is_patient_breastfeeding'              => (isset($_POST['breastfeeding']) && $_POST['breastfeeding'] != '') ? $_POST['breastfeeding'] :  null,
        'pregnancy_trimester'                   => (isset($_POST['trimester']) && $_POST['trimester'] != '') ? $_POST['trimester'] :  null,
        'patient_has_active_tb'                 => (isset($_POST['activeTB']) && $_POST['activeTB'] != '') ? $_POST['activeTB'] :  null,
        'patient_active_tb_phase'               => (isset($_POST['tbPhase']) && $_POST['tbPhase'] != '') ? $_POST['tbPhase'] :  null,
        'patient_art_no'                        => (isset($_POST['artNo']) && $_POST['artNo'] != '') ? $_POST['artNo'] :  null,
        'is_patient_new'                        => $_POST['isPatientNew'],
        'treatment_duration'                    => (isset($_POST['treatmentDuration']) && $_POST['treatmentDuration'] != '') ? $_POST['treatmentDuration'] :  null,
        'treatment_indication'                  => (isset($_POST['treatmentIndication']) && $_POST['treatmentIndication'] != '') ? $_POST['treatmentIndication'] :  null,
        'treatment_initiated_date'              => $_POST['dateOfArtInitiation'],
        'current_regimen'                       => (isset($_POST['artRegimen']) && $_POST['artRegimen'] != '') ? $_POST['artRegimen'] :  null,
        'has_patient_changed_regimen'           => $_POST['hasChangedRegimen'],
        'reason_for_regimen_change'             => $_POST['reasonForArvRegimenChange'],
        'regimen_change_date'                   => $_POST['dateOfArvRegimenChange'],
        'line_of_treatment'                     => (isset($_POST['lineOfTreatment']) && $_POST['lineOfTreatment'] != '') ? $_POST['lineOfTreatment'] :  null,
        'line_of_treatment_failure_assessed'    => (isset($_POST['lineOfTreatmentFailureAssessed']) && $_POST['lineOfTreatmentFailureAssessed'] != '') ? $_POST['lineOfTreatmentFailureAssessed'] :  null,
        'date_of_initiation_of_current_regimen' => $_POST['regimenInitiatedOn'],
        'patient_mobile_number'                 => (isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber'] != '') ? $_POST['patientPhoneNumber'] :  null,
        'consent_to_receive_sms'                => (isset($_POST['receiveSms']) && $_POST['receiveSms'] != '') ? $_POST['receiveSms'] :  null,
        'sample_type'                           => (isset($_POST['specimenType']) && $_POST['specimenType'] != '') ? $_POST['specimenType'] :  null,
        'plasma_conservation_temperature'       => $_POST['conservationTemperature'],
        'plasma_conservation_duration'          => $_POST['durationOfConservation'],
        'arv_adherance_percentage'              => (isset($_POST['arvAdherence']) && $_POST['arvAdherence'] != '') ? $_POST['arvAdherence'] :  null,
        'reason_for_vl_testing'                 => (isset($_POST['reasonForVLTesting'])) ? $_POST['reasonForVLTesting'] : null,
        'last_viral_load_result'                => (isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult'] != '') ? $_POST['lastViralLoadResult'] :  null,
        'last_viral_load_date'                  => $_POST['lastViralLoadTestDate'], 
        'community_sample'                      => (isset($_POST['communitySample'])) ? $_POST['communitySample'] : null,
        'last_vl_date_routine'                  => (isset($_POST['rmTestingLastVLDate']) && $_POST['rmTestingLastVLDate'] != '') ? DateUtility::isoDateFormat($_POST['rmTestingLastVLDate']) :  null,
        'last_vl_result_routine'                => (isset($_POST['rmTestingVlValue']) && $_POST['rmTestingVlValue'] != '') ? $_POST['rmTestingVlValue'] :  null,
        'last_vl_sample_type_routine'           => (isset($_POST['rmLastVLTestSampleType']) && $_POST['rmLastVLTestSampleType'] != '') ? $_POST['rmLastVLTestSampleType'] :  null,
        'last_vl_date_failure_ac'               => (isset($_POST['repeatTestingLastVLDate']) && $_POST['repeatTestingLastVLDate'] != '') ? DateUtility::isoDateFormat($_POST['repeatTestingLastVLDate']) :  null,
        'last_vl_result_failure_ac'             => (isset($_POST['repeatTestingVlValue']) && $_POST['repeatTestingVlValue'] != '') ? $_POST['repeatTestingVlValue'] :  null,
        'last_vl_sample_type_failure_ac'        => (isset($_POST['repeatLastVLTestSampleType']) && $_POST['repeatLastVLTestSampleType'] != '') ? $_POST['repeatLastVLTestSampleType'] :  null,
        'last_vl_date_failure'                  => (isset($_POST['suspendTreatmentLastVLDate']) && $_POST['suspendTreatmentLastVLDate'] != '') ? DateUtility::isoDateFormat($_POST['suspendTreatmentLastVLDate']) :  null,
        'last_vl_result_failure'                => (isset($_POST['suspendTreatmentVlValue']) && $_POST['suspendTreatmentVlValue'] != '') ? $_POST['suspendTreatmentVlValue'] :  null,
        'last_vl_sample_type_failure'           => (isset($_POST['suspendLastVLTestSampleType']) && $_POST['suspendLastVLTestSampleType'] != '') ? $_POST['suspendLastVLTestSampleType'] :  null,
        'request_clinician_name'                => (isset($_POST['reqClinician']) && $_POST['reqClinician'] != '') ? $_POST['reqClinician'] :  null,
        'request_clinician_phone_number'        => (isset($_POST['reqClinicianPhoneNumber']) && $_POST['reqClinicianPhoneNumber'] != '') ? $_POST['reqClinicianPhoneNumber'] :  null,
        'test_requested_on'                     => (isset($_POST['requestDate']) && $_POST['requestDate'] != '') ? DateUtility::isoDateFormat($_POST['requestDate']) :  null,
        'vl_focal_person'                       => (isset($_POST['vlFocalPerson']) && $_POST['vlFocalPerson'] != '') ? $_POST['vlFocalPerson'] :  null,
        'vl_focal_person_phone_number'          => (isset($_POST['vlFocalPersonPhoneNumber']) && $_POST['vlFocalPersonPhoneNumber'] != '') ? $_POST['vlFocalPersonPhoneNumber'] :  null,
        'lab_id'                                => (isset($_POST['labId']) && $_POST['labId'] != '') ? $_POST['labId'] :  null,
        'vl_test_platform'                      => $testingPlatform,
        'sample_received_at_hub_datetime'       => $_POST['sampleReceivedAtHubOn'],
        'sample_received_at_vl_lab_datetime'    => $_POST['sampleReceivedDate'],
        'sample_tested_datetime'                => $_POST['sampleTestingDateAtLab'],
        'result_dispatched_datetime'            => $_POST['resultDispatchedOn'],
        'result_value_hiv_detection'            => (isset($_POST['hivDetection']) && $_POST['hivDetection'] != '') ? $_POST['hivDetection'] :  null,
        'reason_for_failure'                    => (isset($_POST['reasonForFailure']) && $_POST['reasonForFailure'] != '') ? $_POST['reasonForFailure'] :  null,
        'is_sample_rejected'                    => (isset($_POST['noResult']) && $_POST['noResult'] != '') ? $_POST['noResult'] :  null,
        'reason_for_sample_rejection'           => (isset($_POST['rejectionReason']) && $_POST['rejectionReason'] != '') ? $_POST['rejectionReason'] :  null,
        'rejection_on'                          => (!empty($_POST['rejectionDate'])) ? DateUtility::isoDateFormat($_POST['rejectionDate']) : null,
        'result_value_absolute'                 => $absVal ?: null,
        'result_value_absolute_decimal'         => $absDecimalVal ?: null,
        'result_value_text'                     => $txtVal ?: null,
        'result'                                => $finalResult ?: null,
        'result_value_log'                      => $logVal ?: null,
        'result_reviewed_by'                    => (isset($_POST['reviewedBy']) && $_POST['reviewedBy'] != "") ? $_POST['reviewedBy'] : null,
        'result_reviewed_datetime'              => (isset($_POST['reviewedOn']) && $_POST['reviewedOn'] != "") ? $_POST['reviewedOn'] : null,
        'tested_by'                             => (isset($_POST['testedBy']) && $_POST['testedBy'] != '') ? $_POST['testedBy'] :  null,
        'result_approved_by'                    => (isset($_POST['approvedBy']) && $_POST['approvedBy'] != '') ? $_POST['approvedBy'] :  null,
        'result_approved_datetime'              => (isset($_POST['approvedOn']) && $_POST['approvedOn'] != '') ? $_POST['approvedOn'] :  null,
        'date_test_ordered_by_physician'        => $_POST['dateOfDemand'],
        'lab_tech_comments'                     => (isset($_POST['labComments']) && trim($_POST['labComments']) != '') ? trim($_POST['labComments']) :  null,
        'result_status'                         => $resultStatus,
        'funding_source'                        => (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') ? base64_decode($_POST['fundingSource']) : null,
        'implementing_partner'                  => (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') ? base64_decode($_POST['implementingPartner']) : null,
        'vl_test_number'                        => (isset($_POST['viralLoadNo']) && $_POST['viralLoadNo'] != '') ? $_POST['viralLoadNo'] :  null,
        // 'request_created_by'                 => $_SESSION['userId'],
        'request_created_datetime'              => $db->now(),
        // 'last_modified_by'                   => $_SESSION['userId'],
        'last_modified_datetime'                => $db->now(),
        'manual_result_entry'                   => 'yes',
        'vl_result_category'                    => $vl_result_category
    );

    if (isset($systemType) && ($systemType == "vluser" || $systemType == "standalone")) {
        $vldata['source_of_request'] = 'vlsm';
    } elseif (isset($systemType) && ($systemType == "remoteuser")) {
        $vldata['source_of_request'] = 'vlsts';
    } elseif (!empty($_POST['api']) && $_POST['api'] == "yes") {
        $vldata['source_of_request'] = 'api';
    }
    if (isset($_POST['api']) && $_POST['api'] == "yes") {
    } else {
        $vldata['request_created_by'] =  $_SESSION['userId'];
        $vldata['last_modified_by'] =  $_SESSION['userId'];
    }

    $vldata['vl_result_category'] = $vlService->getVLResultCategory($vldata['result_status'], $vldata['result']);
    if ($vldata['vl_result_category'] == 'failed' || $vldata['vl_result_category'] == 'invalid') {
        $vldata['result_status'] = 5;
    } elseif ($vldata['vl_result_category'] == 'rejected') {
        $vldata['result_status'] = 4;
    }

    if (isset($_POST['cdDate']) && trim($_POST['cdDate']) != "") {
        $_POST['cdDate'] = DateUtility::isoDateFormat($_POST['cdDate']);
    } else {
            $_POST['cdDate'] = null;
    }

    if (isset($_POST['failedTestDate']) && trim($_POST['failedTestDate']) != "") {
        $failedtestDate = explode(" ", $_POST['failedTestDate']);
        $_POST['failedTestDate'] = DateUtility::isoDateFormat($failedtestDate[0]) . " " . $failedtestDate[1];
   } else {
        $_POST['failedTestDate'] = null;
   }
    if(isset($_POST['failedTestingTech']) && $_POST['failedTestingTech'] != '') {
        $platForm = explode("##", $_POST['failedTestingTech']);
        $_POST['failedTestingTech'] = $platForm[0];
    }
    if (isset($_POST['qcDate']) && trim($_POST['qcDate']) != "") {
        $_POST['qcDate'] = DateUtility::isoDateFormat($_POST['qcDate']);
   } else {
        $_POST['qcDate'] = null;
   }

   if (isset($_POST['reportDate']) && trim($_POST['reportDate']) != "") {
        $_POST['reportDate'] = DateUtility::isoDateFormat($_POST['reportDate']);
   } else {
        $_POST['reportDate'] = null;
   }

    //For PNG form
    $pngSpecificFields = [];
    if(isset($_POST['countryFormId']) && $_POST['countryFormId']=='5')
    {
        $pngSpecificFields['art_cd_cells'] = $_POST['cdCells'];
        $pngSpecificFields['art_cd_date'] = $_POST['cdDate'];
        $pngSpecificFields['who_clinical_stage'] = $_POST['clinicalStage'];
        $pngSpecificFields['sample_to_transport'] = (isset($_POST['typeOfSample']) && $_POST['typeOfSample'] != '' ? $_POST['typeOfSample'] : null);
        $pngSpecificFields['whole_blood_ml'] = (isset($_POST['wholeBloodOne']) && $_POST['wholeBloodOne'] != '' ? $_POST['wholeBloodOne'] : null);
        $pngSpecificFields['whole_blood_vial'] = (isset($_POST['wholeBloodTwo']) && $_POST['wholeBloodTwo'] != '' ? $_POST['wholeBloodTwo'] : null);
        $pngSpecificFields['plasma_ml'] = (isset($_POST['plasmaOne']) && $_POST['plasmaOne'] != '' ? $_POST['plasmaOne'] : null);
        $pngSpecificFields['plasma_vial'] = (isset($_POST['plasmaTwo']) && $_POST['plasmaTwo'] != '' ? $_POST['plasmaTwo'] : null);
        $pngSpecificFields['plasma_process_time'] = (isset($_POST['processTime']) && $_POST['processTime'] != '' ? $_POST['processTime'] : null);
        $pngSpecificFields['plasma_process_tech'] = (isset($_POST['processTech']) && $_POST['processTech'] != '' ? $_POST['processTech'] : null);
        $pngSpecificFields['sample_collected_by'] = (isset($_POST['collectedBy']) && $_POST['collectedBy'] != '' ? $_POST['collectedBy'] : null);
        $pngSpecificFields['tech_name_png'] = (isset($_POST['techName']) && $_POST['techName'] != '') ? $_POST['techName'] : null;
        $pngSpecificFields['cphl_vl_result'] = (isset($_POST['cphlvlResult']) && $_POST['cphlvlResult'] != '' ? $_POST['cphlvlResult'] : null);
        $pngSpecificFields['batch_quality'] = (isset($_POST['batchQuality']) && $_POST['batchQuality'] != '' ? $_POST['batchQuality'] : null);
        $pngSpecificFields['sample_test_quality'] = (isset($_POST['testQuality']) && $_POST['testQuality'] != '' ? $_POST['testQuality'] : null);
        $pngSpecificFields['sample_batch_id'] = (isset($_POST['batchNo']) && $_POST['batchNo'] != '' ? $_POST['batchNo'] : null);
        $pngSpecificFields['failed_test_date'] = $_POST['failedTestDate'];
        $pngSpecificFields['failed_test_tech'] = (isset($_POST['failedTestingTech']) && $_POST['failedTestingTech'] != '') ? $_POST['failedTestingTech'] : null;
        $pngSpecificFields['failed_vl_result'] = (isset($_POST['failedvlResult']) && $_POST['failedvlResult'] != '' ? $_POST['failedvlResult'] : null);
        $pngSpecificFields['failed_batch_quality'] = (isset($_POST['failedbatchQuality']) && $_POST['failedbatchQuality'] != '' ? $_POST['failedbatchQuality'] : null);
        $pngSpecificFields['failed_sample_test_quality'] = (isset($_POST['failedtestQuality']) && $_POST['failedtestQuality'] != '' ? $_POST['failedtestQuality'] : null);
        $pngSpecificFields['failed_batch_id'] = (isset($_POST['failedbatchNo']) && $_POST['failedbatchNo'] != '' ? $_POST['failedbatchNo'] : null);
        $pngSpecificFields['result'] = (isset($_POST['finalViralResult']) && trim($_POST['finalViralResult']) != '') ? $_POST['finalViralResult'] : null;
        $pngSpecificFields['qc_tech_name'] = (isset($_POST['qcTechName']) && $_POST['qcTechName'] != '' ? $_POST['qcTechName'] : null);
        $pngSpecificFields['qc_tech_sign'] = (isset($_POST['qcTechSign']) && $_POST['qcTechSign'] != '' ? $_POST['qcTechSign'] : null);
        $pngSpecificFields['qc_date'] = $_POST['qcDate'];
        $pngSpecificFields['report_date'] = $_POST['reportDate'];

    }
    $vldata = array_merge($vldata, $pngSpecificFields);

    $vldata['patient_first_name'] = $general->crypto('doNothing', $_POST['patientFirstName'], $vldata['patient_art_no']);
    $id = 0;
   //echo '<pre>'; print_r($vldata); die;

    if (isset($_POST['vlSampleId']) && $_POST['vlSampleId'] != '') {
        $db = $db->where('vl_sample_id', $_POST['vlSampleId']);
        $id = $db->update($tableName, $vldata);
    } else {
        //check existing sample code

        $existSampleQuery = "SELECT " . $sampleCode . "," . $sampleCodeKey . " FROM generic_tests where " . $sampleCode . " ='" . trim($_POST['sampleCode']) . "'";
        $existResult = $db->rawQuery($existSampleQuery);
        if (isset($existResult[0][$sampleCodeKey]) && $existResult[0][$sampleCodeKey] != '') {
            if ($existResult[0][$sampleCodeKey] != '') {
                $sCode = $existResult[0][$sampleCodeKey] + 1;
                $strparam = strlen($sCode);
                $zeros = substr("000", $strparam);
                $maxId = $zeros . $sCode;
                $_POST['sampleCode'] = $_POST['sampleCodeFormat'] . $maxId;
                $_POST['sampleCodeKey'] = $maxId;
            } else {
                $_SESSION['alertMsg'] = _("Please check your sample ID");
                header("Location:add-request.php");
            }
        }
        // print_r($_POST['sampleCode']);die;

        if ($_SESSION['instanceType'] == 'remoteuser') {
            $vldata['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] :  null;
            $vldata['remote_sample_code_key'] = (isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey'] != '') ? $_POST['sampleCodeKey'] :  null;
            $vldata['remote_sample'] = 'yes';
        } else {
            $vldata['sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] :  null;
            //$vldata['sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] :  null;
            $vldata['sample_code_key'] = (isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey'] != '') ? $_POST['sampleCodeKey'] :  null;
        }
        $vldata['sample_code_format'] = (isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat'] != '') ? $_POST['sampleCodeFormat'] :  null;
        $id = $db->insert($tableName, $vldata);
    }
    if (!empty($_POST['api']) && $_POST['api'] == "yes") {
        $payload = array(
            'status' => 'success',
            'timestamp' => time(),
            'message' => 'Successfully added.'
        );
        http_response_code(200);
        echo json_encode($payload);
        exit(0);
    } else {
        if ($id > 0) {
            $_SESSION['alertMsg'] = _("VL request added successfully");
            //Add event log

            $eventType = 'add-vl-request-sudan';
            $action = $_SESSION['userName'] . ' added a new request data with the sample code ' . $_POST['sampleCode'];
            $resource = 'vl-request-ss';

            $general->activityLog($eventType, $action, $resource);

            $barcode = "";
            if (isset($_POST['printBarCode']) && $_POST['printBarCode'] == 'on') {
                $s = $_POST['sampleCode'];
                $facQuery = "SELECT * FROM facility_details where facility_id=" . $_POST['fName'];
                $facResult = $db->rawQuery($facQuery);
                $f = ($facResult[0]['facility_name']) . " | " . $_POST['sampleCollectionDate'];
                $barcode = "?barcode=true&s=$s&f=$f";
            }

            if (isset($_POST['saveNext']) && $_POST['saveNext'] == 'next') {
                header("Location:add-request.php");
            } else {
                header("Location:add-request.php");
            }
        } else {
            $_SESSION['alertMsg'] = _("Please try again later");
            header("Location:view-request.php");
        }
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}