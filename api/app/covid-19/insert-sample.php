<?php

try {
    $provinceCode = (isset($_POST['provinceCode']) && !empty($_POST['provinceCode'])) ? $_POST['provinceCode'] : null;
    $provinceId = (isset($_POST['provinceId']) && !empty($_POST['provinceId'])) ? $_POST['provinceId'] : null;
    $sampleCollectionDate = (isset($_POST['sampleCollectionDate']) && !empty($_POST['sampleCollectionDate'])) ? $_POST['sampleCollectionDate'] : null;

    $rowData = false;
    if ($_POST['sampleCode'] != "" && !empty($_POST['sampleCode'])) {
        $sQuery = "SELECT covid19_id, sample_code, sample_code_format, sample_code_key, remote_sample_code, remote_sample_code_format, remote_sample_code_key FROM form_covid19 where sample_code like '%" . $_POST['sampleCode'] . "%' or remote_sample_code like '%" . $_POST['sampleCode'] . "%' limit 1";
        // die($sQuery);
        $rowData = $db->rawQueryOne($sQuery);
        if ($rowData) {
            $sampleData['sampleCode'] = (!empty($rowData['sample_code'])) ? $rowData['sample_code'] : $rowData['remote_sample_code'];
            $sampleData['sampleCodeFormat'] = (!empty($rowData['sample_code_format'])) ? $rowData['sample_code_format'] : $rowData['remote_sample_code_format'];
            $sampleData['sampleCodeKey'] = (!empty($rowData['sample_code_key'])) ? $rowData['sample_code_key'] : $rowData['remote_sample_code_key'];
        } else {
            $sampleJson = $c19Model->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
            $sampleData = json_decode($sampleJson, true);
        }
    } else {
        $sampleJson = $c19Model->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
        $sampleData = json_decode($sampleJson, true);
    }

    if (!isset($_POST['countryId']) || $_POST['countryId'] == '')
        $_POST['countryId'] = '';
    $covid19Data = array();
    $covid19Data = array(
        'vlsm_country_id' => $_POST['countryId'],
        'sample_collection_date' => $_POST['sampleCollectionDate'],
        'vlsm_instance_id' => $_POST['instanceId'],
        'province_id' => $provinceId,
        'request_created_by' => '',
        'request_created_datetime' => $general->getDateTime(),
        'last_modified_by' => '',
        'last_modified_datetime' => $general->getDateTime()
    );

    if ($systemConfig['user_type'] == 'remoteuser') {
        $covid19Data['remote_sample_code'] = $sampleData['sampleCode'];
        $covid19Data['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
        $covid19Data['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
        $covid19Data['remote_sample'] = 'yes';
        $covid19Data['result_status'] = 9;
    } else {
        $covid19Data['sample_code'] = $sampleData['sampleCode'];
        $covid19Data['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $covid19Data['sample_code_key'] = $sampleData['sampleCodeKey'];
        $covid19Data['remote_sample'] = 'no';
        $covid19Data['result_status'] = 6;
    }

    $id = 0;
    if ($rowData) {
        $db = $db->where('covid19_id', $rowData['covid19_id']);
        $id = $db->update("form_covid19", $covid19Data);
        $_POST['covid19SampleId'] = $rowData['covid19_id'];
    } else {
        $id = $db->insert("form_covid19", $covid19Data);
        $_POST['covid19SampleId'] = $id;
    }
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
