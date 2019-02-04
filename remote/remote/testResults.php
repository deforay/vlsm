<?php
//this file is receive lab data value and update in remote db
$data = json_decode(file_get_contents('php://input'), true);

include dirname(__FILE__) . "/../../includes/MysqliDb.php";
include dirname(__FILE__) . "/../../General.php";

$cQuery = "SELECT * FROM global_config";
$cResult = $db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
    $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}

$general = new General();

$allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '$DBNAME' AND table_name='vl_request_form'";
$allColResult = $db->rawQuery($allColumns);
$oneDimensionalArray = array_map('current', $allColResult);
$sampleCode = array();
if (count($data['result']) > 0) {
    $lab = array();
    foreach ($data['result'] as $key => $remoteData) {
        foreach ($oneDimensionalArray as $result) {
            if (isset($remoteData[$result])) {
                $lab[$result] = $remoteData[$result];
            }else{
                $lab[$result] = null;
            }
        }
        //remove result value
        $removeKeys = array('vl_sample_id');
        foreach ($removeKeys as $keys) {
            unset($lab[$keys]);
        }
        //check wheather sample code empty or not
        if (isset($lab['remote_sample_code']) && $lab['remote_sample_code'] != '') {
            $sQuery = "SELECT vl_sample_id,sample_code,remote_sample_code,remote_sample_code_key FROM vl_request_form WHERE remote_sample_code='" . $lab['remote_sample_code'] . "'";
            $sResult = $db->rawQuery($sQuery);
            if ($sResult) {
                $lab['result_printed_datetime'] = null;
                $lab['data_sync'] = 1; //column data sync value is 1 equal to data sync done.value 0 is not done.
                $lab['last_modified_datetime'] = $general->getDateTime();
                $lab['remote_sample_code'] = $sResult[0]['remote_sample_code'];
                $lab['remote_sample_code_key'] = $sResult[0]['remote_sample_code_key'];
                unset($lab['request_created_by']);
                unset($lab['last_modified_by']);
                unset($lab['request_created_datetime']);
                unset($lab['sample_package_id']);
                if ($lab['result_status'] != 7 && $lab['result_status'] != 4) {
                    unset($lab['result']);
                    unset($lab['result_value_log']);
                    unset($lab['result_value_absolute']);
                    unset($lab['result_value_text']);
                    unset($lab['result_value_absolute_decimal']);
                }
                $db = $db->where('vl_sample_id', $sResult[0]['vl_sample_id']);
                $id = $db->update('vl_request_form', $lab);
                if ($id > 0 && isset($lab['sample_code'])) {
                    $sampleCode[] = $lab['sample_code'];
                }
            }
        }
    }
}
echo json_encode($sampleCode);
