<?php


use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\HepatitisService;
use App\Utilities\DateUtility;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var HepatitisService $hepatitisService */
$hepatitisService = ContainerRegistry::get(HepatitisService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();


$queryParams = explode(',', $_POST['sampleId']);
$placeholders = implode(', ', array_fill(0, count($queryParams), '?'));

$sampleQuery = "SELECT hepatitis_id,
                sample_collection_date,
                sample_package_code,
                province_id,
                sample_code
                FROM form_hepatitis WHERE hepatitis_id IN ($placeholders)";
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
        $sampleCodeParams['sampleCollectionDate'] = DateUtility::humanReadableDateFormat($sampleRow['sample_collection_date'] ?? '');
        $sampleCodeParams['prefix'] = $sampleRow['hepatitis_test_type'] ?? null;
        $sampleCodeParams['provinceCode'] = $provinceCode;

        $sampleJson = $hepatitisService->getSampleCode($sampleCodeParams);
        $sampleData = json_decode($sampleJson, true);
        $hepatitisData = [];
        $hepatitisData['sample_code'] = $sampleData['sampleCode'];
        $hepatitisData['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $hepatitisData['sample_code_key'] = $sampleData['sampleCodeKey'];
        $hepatitisData['result_status'] = SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB;
        $hepatitisData['data_sync'] = 0;
        $hepatitisData['last_modified_datetime'] = DateUtility::getCurrentDateTime();
        if (!empty($_POST['testDate'])) {
            $hepatitisData['sample_tested_datetime'] = null;
            $hepatitisData['sample_received_at_vl_lab_datetime'] = $_POST['testDate'];
        }
        $hepatitisData['last_modified_by'] = $_SESSION['userId'];
        $hepatitisData['last_modified_datetime'] = DateUtility::getCurrentDateTime();

        $db = $db->where('hepatitis_id', $sampleRow['hepatitis_id']);
        $id = $db->update('form_hepatitis', $hepatitisData);
        if ($id === true) {
            $status = $id;
        }
    }
}
echo $status;