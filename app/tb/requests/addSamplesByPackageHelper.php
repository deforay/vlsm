<?php





$general = new \Vlsm\Models\General();
$tbObj = new \Vlsm\Models\Tb();


$sampleQuery = "SELECT tb_id, sample_collection_date, sample_package_code, province_id, sample_code FROM form_tb where tb_id IN (" . $_POST['sampleId'] . ") ORDER BY tb_id";
$sampleResult = $db->query($sampleQuery);
$status = 0;
foreach ($sampleResult as $sampleRow) {

    $provinceCode = null;
    if (isset($sampleRow['province_id']) && !empty($sampleRow['province_id'])) {
        $provinceQuery = "SELECT * FROM province_details WHERE province_id= " . $sampleRow['province_id'];
        $provinceResult = $db->rawQueryOne($provinceQuery);
        $provinceCode = $provinceResult['province_code'];
    }

    if (isset($_POST['testDate']) && !empty($_POST['testDate'])) {
        $testDate = explode(" ", $_POST['testDate']);
        $_POST['testDate'] = $general->dateFormat($testDate[0]);
        $_POST['testDate'] .= " " . $testDate[1];
    } else {
        $_POST['testDate'] = null;
    }
    // ONLY IF SAMPLE CODE IS NOT ALREADY GENERATED
    if ($sampleRow['sample_code'] == null || $sampleRow['sample_code'] == '' || $sampleRow['sample_code'] == 'null') {

        $sampleJson = $tbObj->generatetbSampleCode($provinceCode, $general->humanDateFormat($sampleRow['sample_collection_date']));
        $sampleData = json_decode($sampleJson, true);
        $tbData = array();
        $tbData['sample_code'] = $sampleData['sampleCode'];
        $tbData['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $tbData['sample_code_key'] = $sampleData['sampleCodeKey'];
        $tbData['sample_tested_datetime'] = $_POST['testDate'];
        $tbData['result_status'] = 6;
        $tbData['last_modified_by'] = $_SESSION['userId'];
        $tbData['last_modified_datetime'] = $general->getDateTime();

        $db = $db->where('tb_id', $sampleRow['tb_id']);
        $id = $db->update('form_tb', $tbData);
        if ($id > 0) {
            $status = $id;
        }
    }
}
echo $status;