<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$tableName = "eid_form";
//system config
$id = '';
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != "") {
    $sampleDate = explode(" ", $_POST['sampleCollectionDate']);
    $_POST['sampleCollectionDate'] = $general->dateFormat($sampleDate[0]) . " " . $sampleDate[1];
} else {
    $_POST['sampleCollectionDate'] = null;
}
$rKey = '';

if ($sarr['user_type'] == 'remoteuser') {
    $rKey = 'R';
    $sampleCode = 'remote_sample_code';
    $sampleCodeKey = 'remote_sample_code_key';
} else {
    $sampleCode = 'sample_code';
    $sampleCodeKey = 'sample_code_key';
}

$existSampleQuery = "SELECT " . $sampleCode . "," . $sampleCodeKey . " FROM eid_form where " . $sampleCode . " ='" . trim($_POST['sampleCode']) . "'";
$existResult = $db->rawQuery($existSampleQuery);

if (isset($_POST['provinceId']) && $_POST['provinceId'] != null && $_POST['provinceId'] != '') {
    if (isset($existResult[0][$sampleCodeKey]) && $existResult[0][$sampleCodeKey] != '') {
        //global config
        $configQuery = "SELECT * from global_config";
        $configResult = $db->query($configQuery);
        $arr = array();
        //$prefix = $arr['sample_code_prefix'];
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($configResult); $i++) {
            $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
        }
        $sampleColDateTimeArray = explode(" ", $_POST['sampleCollectionDate']);
        $sampleCollectionDate = $general->dateFormat($sampleColDateTimeArray[0]);
        $sampleColDateArray = explode("-", $sampleCollectionDate);
        $samColDate = substr($sampleColDateArray[0], -2);

        $start_date = $sampleColDateArray[2] . '-01-01';
        $end_date = $sampleColDateArray[2] . '-12-31';

        $svlQuery = 'SELECT ' . $sampleCodeKey . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND province_id=' . $_POST['provinceId'] . ' AND ' . $sampleCode . ' IS NOT NULL AND ' . $sampleCode . '!= "" ORDER BY ' . $sampleCodeKey . ' DESC LIMIT 1';

        $svlResult = $db->query($svlQuery);

        if (isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey] != '' && $svlResult[0][$sampleCodeKey] != null) {
            $maxId = $svlResult[0][$sampleCodeKey] + 1;
            $strparam = strlen($maxId);
            $zeros = substr("0000", $strparam);
            $maxId = $zeros . $maxId;
        } else {
            $maxId = '0001';
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
                $zeros = substr("0000", $strparam);
                $maxId = $zeros . $x;
                $sCode = $rKey . "R" . date('y') . $_POST['provinceCode'] . "VL" . $maxId;
            }
        } while ($sCode);
        $_POST['sampleCode'] = $sCode;
        $_POST['sampleCodeKey'] = $maxId;
    }
} else {
    if (isset($existResult[0][$sampleCodeKey]) && $existResult[0][$sampleCodeKey] != '') {
        $sCode = $existResult[0][$sampleCodeKey] + 1;
        $strparam = strlen($sCode);
        $zeros = substr("000", $strparam);
        $maxId = $zeros . $sCode;
        $_POST['sampleCode'] = $_POST['sampleCodeFormat'] . $maxId;
        $_POST['sampleCodeKey'] = $maxId;
    }
}
$vldata = array(
    'vlsm_country_id' => $_POST['countryId'],
    'sample_collection_date' => $_POST['sampleCollectionDate'],
    'vlsm_instance_id' => $_SESSION['instanceId'],
    'request_created_by' => $_SESSION['userId'],
    'request_created_datetime' => $general->getDateTime(),
    'last_modified_by' => $_SESSION['userId'],
    'last_modified_datetime' => $general->getDateTime(),
    //'manual_result_entry' => 'yes',
    'result_status' => 9,
    'sample_code_format' => (isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat'] != '') ? $_POST['sampleCodeFormat'] : null,
);
if ($sarr['user_type'] == 'remoteuser') {
    $vldata['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] : null;
    $vldata['remote_sample_code_key'] = (isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey'] != '') ? $_POST['sampleCodeKey'] : null;
    $vldata['remote_sample'] = 'yes';
} else {
    $vldata['sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] : null;
    $vldata['sample_code_key'] = (isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey'] != '') ? $_POST['sampleCodeKey'] : null;
}
if (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '' && $_POST['sampleCollectionDate'] != null && $_POST['sampleCollectionDate'] != '') {
    //echo $tableName;
    //echo "<pre>";print_r($vldata);die;
    $id = $db->insert($tableName, $vldata);
}
if ($id > 0) {
    echo $id;
} else {
    echo 0;
}