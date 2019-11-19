<?php

require_once('../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
require_once(APPLICATION_PATH . '/models/Vl.php');


$general = new General($db);
$vlObj = new Model_Vl($db);


$sampleQuery = "SELECT vl_sample_id, sample_collection_date, sample_package_code, province_id, sample_code FROM vl_request_form where vl_sample_id IN (" . $_POST['sampleId'].")";
$sampleResult = $db->query($sampleQuery);
$status = 0;
foreach($sampleResult as $sampleRow){

    $provinceCode = null;
    // ONLY IF SAMPLE CODE IS NOT ALREADY GENERATED
    if ($sampleRow['sample_code'] == null || $sampleRow['sample_code'] == '' || $sampleRow['sample_code'] == 'null') {

        $sampleJson = $vlObj->generateVLSampleID($provinceCode, $general->humanDateFormat($sampleRow['sample_collection_date']));
        $sampleData = json_decode($sampleJson, true);
        $vldata['serial_no'] = $sampleData['sampleCode'];
        $vldata['sample_code'] = $sampleData['sampleCode'];
        $vldata['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $vldata['sample_code_key'] = $sampleData['sampleCodeKey'];
        $vldata['result_status'] = 6;

        $db = $db->where('vl_sample_id', $sampleRow['vl_sample_id']);
        $id = $db->update('vl_request_form', $vldata);
        if($id > 0){
            $status = $id;
        }
    }
}
echo $status;
