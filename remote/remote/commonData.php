<?php
//get data from remote db send to lab db
include(dirname(__FILE__) . "/../../startup.php");


$general = new \Vlsm\Models\General($db);

$data = json_decode(file_get_contents('php://input'), true);

if ($data['Key'] == 'vlsm-get-remote') {

    $response = array();

    if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {


        $condition = null;
        if (isset($data['vlRejectionReasonsLastModified']) && !empty($data['vlRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlRejectionReasonsLastModified'] . "'";
        }
        $response['vlRejectionReasons'] = $general->fetchDataFromTable('r_vl_sample_rejection_reasons', $condition);


        $condition = null;
        if (isset($data['vlSampleTypesLastModified']) && !empty($data['vlSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlSampleTypesLastModified'] . "'";
        }
        $response['vlSampleTypes'] = $general->fetchDataFromTable('r_vl_sample_type', $condition);

        $condition = null;
        if (isset($data['vlArtCodesLastModified']) && !empty($data['vlArtCodesLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlArtCodesLastModified'] . "'";
        }
        $response['vlArtCodes'] = $general->fetchDataFromTable('r_vl_art_regimen', $condition);
    }


    if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {

        $condition = null;
        if (isset($data['eidRejectionReasonsLastModified']) && !empty($data['eidRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['eidRejectionReasonsLastModified'] . "'";
        }
        $response['eidRejectionReasons'] = $general->fetchDataFromTable('r_eid_sample_rejection_reasons', $condition);


        $condition = null;
        if (isset($data['eidSampleTypesLastModified']) && !empty($data['eidSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['eidSampleTypesLastModified'] . "'";
        }
        $response['eidSampleTypes'] = $general->fetchDataFromTable('r_eid_sample_type', $condition);
    }

    if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {

        $condition = null;
        if (isset($data['covid19RejectionReasonsLastModified']) && !empty($data['covid19RejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19RejectionReasonsLastModified'] . "'";
        }
        $response['covid19RejectionReasons'] = $general->fetchDataFromTable('r_covid19_sample_rejection_reasons', $condition);


        $condition = null;
        if (isset($data['covid19SampleTypesLastModified']) && !empty($data['covid19SampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19SampleTypesLastModified'] . "'";
        }
        $response['covid19SampleTypes'] = $general->fetchDataFromTable('r_covid19_sample_type', $condition);

        $condition = null;
        if (isset($data['covid19ComorbiditiesLastModified']) && !empty($data['covid19ComorbiditiesLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19ComorbiditiesLastModified'] . "'";
        }
        $response['covid19Comorbidities'] = $general->fetchDataFromTable('r_covid19_comorbidities', $condition);

        $condition = null;
        if (isset($data['covid19ResultsLastModified']) && !empty($data['covid19ResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19ResultsLastModified'] . "'";
        }
        $response['covid19Results'] = $general->fetchDataFromTable('r_covid19_results', $condition);

        $condition = null;
        if (isset($data['covid19SymptomsLastModified']) && !empty($data['covid19SymptomsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19SymptomsLastModified'] . "'";
        }
        $response['covid19Symptoms'] = $general->fetchDataFromTable('r_covid19_symptoms', $condition);

        $condition = null;
        if (isset($data['covid19ReasonForTestingLastModified']) && !empty($data['covid19ReasonForTestingLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19ReasonForTestingLastModified'] . "'";
        }
        $response['covid19ReasonForTesting'] = $general->fetchDataFromTable('r_covid19_test_reasons', $condition);
    }

    if (isset($systemConfig['modules']['hepatitis']) && $systemConfig['modules']['hepatitis'] == true) {

        $condition = null;
        if (isset($data['hepatitisRejectionReasonsLastModified']) && !empty($data['hepatitisRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisRejectionReasonsLastModified'] . "'";
        }
        $response['hepatitisRejectionReasons'] = $general->fetchDataFromTable('r_hepatitis_sample_rejection_reasons', $condition);


        $condition = null;
        if (isset($data['hepatitisSampleTypesLastModified']) && !empty($data['hepatitisSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisSampleTypesLastModified'] . "'";
        }
        $response['hepatitisSampleTypes'] = $general->fetchDataFromTable('r_hepatitis_sample_type', $condition);

        $condition = null;
        if (isset($data['hepatitisComorbiditiesLastModified']) && !empty($data['hepatitisComorbiditiesLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisComorbiditiesLastModified'] . "'";
        }
        $response['hepatitisComorbidities'] = $general->fetchDataFromTable('r_hepatitis_comorbidities', $condition);

        $condition = null;
        if (isset($data['hepatitisResultsLastModified']) && !empty($data['hepatitisResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisResultsLastModified'] . "'";
        }
        $response['hepatitisResults'] = $general->fetchDataFromTable('r_hepatitis_results', $condition);

        $condition = null;
        if (isset($data['hepatitisReasonForTestingLastModified']) && !empty($data['hepatitisReasonForTestingLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisReasonForTestingLastModified'] . "'";
        }
        $response['hepatitisReasonForTesting'] = $general->fetchDataFromTable('r_hepatitis_test_reasons', $condition);
    }


    $condition = null;
    if (isset($data['globalConfigLastModified']) && !empty($data['globalConfigLastModified'])) {
        $condition = "updated_on > '" . $data['globalConfigLastModified'] . "' AND remote_sync_needed = 'yes'";
    }
    $response['globalConfig'] = $general->fetchDataFromTable('global_config', $condition);

    $condition = null;
    if (isset($data['provinceLastModified']) && !empty($data['provinceLastModified'])) {
        $condition = "updated_datetime > '" . $data['provinceLastModified'] . "'";
    }
    $response['province'] = $general->fetchDataFromTable('province_details', $condition);


    $condition = null;
    if (isset($data['facilityLastModified']) && !empty($data['facilityLastModified'])) {
        $condition = "updated_datetime > '" . $data['facilityLastModified'] . "'";
    }
    $response['facilities'] = $general->fetchDataFromTable('facility_details', $condition);

    $condition = null;
    if (isset($data['healthFacilityLastModified']) && !empty($data['healthFacilityLastModified'])) {
        $condition = "updated_datetime > '" . $data['healthFacilityLastModified'] . "'";
    }
    $response['healthFacilities'] = $general->fetchDataFromTable('health_facilities', $condition);

    $condition = null;
    if (isset($data['testingLabsLastModified']) && !empty($data['testingLabsLastModified'])) {
        $condition = "updated_datetime > '" . $data['testingLabsLastModified'] . "'";
    }
    $response['testingLabs'] = $general->fetchDataFromTable('testing_labs', $condition);

    echo json_encode($response);
}
