<?php

use App\Services\Covid19Service;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var Covid19Service $covid19Service */
$covid19Service = ContainerRegistry::get(Covid19Service::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

$queryParams = explode(',', $_POST['sampleId']);
$placeholders = implode(', ', array_fill(0, count($queryParams), '?'));

$sampleQuery = "SELECT covid19_id,
                sample_collection_date,
                sample_package_code,
                province_id,
                sample_code
                FROM form_covid19 WHERE covid19_id IN ($placeholders)";
$sampleResult = $db->rawQuery($sampleQuery, $queryParams);

$status = 0;
foreach ($sampleResult as $sampleRow) {

    $provinceCode = null;
    if (!empty($sampleRow['province_id'])) {
        $provinceQuery = "SELECT * FROM geographical_divisions WHERE geo_id= " . $sampleRow['province_id'];
        $provinceResult = $db->rawQueryOne($provinceQuery);
        $provinceCode = $provinceResult['geo_code'];
    }
    if (!empty($_POST['testDate'])) {
        $testDate = explode(" ", $_POST['testDate']);
        $_POST['testDate'] = DateUtility::isoDateFormat($testDate[0]);
        $_POST['testDate'] .= " " . $testDate[1];
    } else {
        $_POST['testDate'] = null;
    }
    // ONLY IF SAMPLE CODE IS NOT ALREADY GENERATED
    if ($sampleRow['sample_code'] == null || $sampleRow['sample_code'] == '' || $sampleRow['sample_code'] == 'null') {

        $sampleCodeParams = [];
        $sampleCodeParams['sampleCollectionDate'] = DateUtility::humanReadableDateFormat($formData['sample_collection_date'] ?? '');
        $sampleCodeParams['provinceCode'] = $provinceCode;

        $sampleJson = $covid19Service->getSampleCode($sampleCodeParams);
        $sampleData = json_decode($sampleJson, true);
        $covid19Data = [];
        $covid19Data['sample_code'] = $sampleData['sampleCode'];
        $covid19Data['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $covid19Data['sample_code_key'] = $sampleData['sampleCodeKey'];
        $covid19Data['result_status'] = SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB;
        $covid19Data['data_sync'] = 0;
        if (!empty($_POST['testDate'])) {
            $covid19Data['sample_tested_datetime'] = null;
            $covid19Data['sample_received_at_vl_lab_datetime'] = $_POST['testDate'];
        }
        $covid19Data['last_modified_by'] = $_SESSION['userId'];
        $covid19Data['last_modified_datetime'] = DateUtility::getCurrentDateTime();

        $db = $db->where('covid19_id', $sampleRow['covid19_id']);
        $id = $db->update('form_covid19', $covid19Data);
        if ($id === true) {
            $status = $id;
        }
    }
}
echo $status;