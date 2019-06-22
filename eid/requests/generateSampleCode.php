<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
//global config
$configQuery = "SELECT * from global_config";
$configResult = $db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
$rKey = '';
$sampleCodeKey = 'sample_code_key';
$sampleCode = 'sample_code';
if ($sarr['user_type'] == 'remoteuser') {
    $rKey = 'R';
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
}
$sampleColDateTimeArray = explode(" ", $_POST['sDate']);
$sampleCollectionDate = $general->dateFormat($sampleColDateTimeArray[0]);
$sampleColDateArray = explode("-", $sampleCollectionDate);
$samColDate = substr($sampleColDateArray[0], -2);
$start_date = $sampleColDateArray[0] . '-01-01';
$end_date = $sampleColDateArray[0] . '-12-31';
$mnthYr = $samColDate[0];

if ($arr['sample_code'] == 'MMYY') {
    $mnthYr = $sampleColDateArray[1] . $samColDate;
} else if ($arr['sample_code'] == 'YY') {
    $mnthYr = $samColDate;
}

$auto = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];
if (isset($_POST['sampleFrom'])) {
    $svlQuery = 'SELECT ' . $sampleCodeKey . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND province_id=' . $_POST['provinceId'] . ' AND ' . $sampleCode . ' IS NOT NULL AND ' . $sampleCode . '!= "" ORDER BY ' . $sampleCodeKey . ' DESC LIMIT 1';

    $svlResult = $db->query($svlQuery);

    if (isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey] != '' && $svlResult[0][$sampleCodeKey] != null) {
        $maxId = $svlResult[0][$sampleCodeKey] + 1;
        $strparam = strlen($maxId);
        $zeros = (isset($_POST['autoTyp']) && trim($_POST['autoTyp']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
        $maxId = $zeros . $maxId;
    } else {
        $maxId = (isset($_POST['autoTyp']) && trim($_POST['autoTyp']) == 'auto2') ? '0001' : '001';
    }
    $sCode = $rKey . "R" . date('y') . $_POST['provinceCode'] . "VL" . $maxId;
    $j = 1;
    do {
        $sQuery = "select sample_code from eid_form as vl where sample_code='" . $sCode . "'";
        $svlResult = $db->query($sQuery);
        if (!$svlResult) {
            $maxId;
            break;
        } else {
            $x = $maxId + 1;
            $strparam = strlen($x);
            $zeros = (isset($_POST['autoTyp']) && trim($_POST['autoTyp']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
            $maxId = $zeros . $x;
            $sCode = $rKey . "R" . date('y') . $_POST['provinceCode'] . "VL" . $maxId;
        }
    } while ($sCode);

} else {
    $svlQuery = 'SELECT ' . $sampleCodeKey . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND ' . $sampleCode . '!="" ORDER BY ' . $sampleCodeKey . ' DESC LIMIT 1';

    $svlResult = $db->query($svlQuery);
    if (isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey] != '' && $svlResult[0][$sampleCodeKey] != null) {
        $maxId = $svlResult[0][$sampleCodeKey] + 1;
        $strparam = strlen($maxId);
        $zeros = (isset($_POST['autoTyp']) && trim($_POST['autoTyp']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
        $maxId = $zeros . $maxId;
    } else {
        $maxId = (isset($_POST['autoTyp']) && trim($_POST['autoTyp']) == 'auto2') ? '0001' : '001';
    }
}

echo json_encode(array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $auto));