<?php
//get data from remote db send to lab db
include(dirname(__FILE__) . "/../../startup.php");
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');

$general = new General($db);

$data = json_decode(file_get_contents('php://input'), true);
if ($data['Key'] == 'vlsm-get-remote') {

    $response = array();

    if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {


        $condition = null;
        if (isset($data['vlRejectionReasonsLastModified']) && !empty($data['vlRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlRejectionReasonsLastModified'] . "'";
        }
        $response['vlRejectionReasons'] = $general->fetchDataFromTable('r_sample_rejection_reasons', $condition);


        $condition = null;
        if (isset($data['vlSampleTypesLastModified']) && !empty($data['vlSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlSampleTypesLastModified'] . "'";
        }
        $response['vlSampleTypes'] = $general->fetchDataFromTable('r_vl_sample_type', $condition);

        $condition = null;
        if (isset($data['vlArtCodesLastModified']) && !empty($data['vlArtCodesLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlArtCodesLastModified'] . "'";
        }
        $response['vlArtCodes'] = $general->fetchDataFromTable('r_art_code_details', $condition);
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




    echo json_encode($response);
}
