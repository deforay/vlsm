<?php
ob_start();
session_start();

include_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . '/models/Eid.php');

$general = new General($db);
$eidModel = new Model_Eid($db);

//$globalConfig = $general->getGlobalConfig();
$systemConfig = $general->getSystemConfig();

$i;

$provinceCode = (isset($_POST['provinceCode']) && !empty($_POST['provinceCode'])) ? $_POST['provinceCode'] : null;
$provinceId = (isset($_POST['provinceId']) && !empty($_POST['provinceId'])) ? $_POST['provinceId'] : null;
$sampleCollectionDate = (isset($_POST['sampleCollectionDate']) && !empty($_POST['sampleCollectionDate'])) ? $_POST['sampleCollectionDate'] : null;

$sampleJson = $eidModel->generateEIDSampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
$sampleData = json_decode($sampleJson, true);


$eidData = array();
$eidData = array(
    'vlsm_country_id' => $_POST['countryId'],
    'sample_collection_date' => $_POST['sampleCollectionDate'],
    'vlsm_instance_id' => $_SESSION['instanceId'],
    'request_created_by' => $_SESSION['userId'],
    'request_created_datetime' => $general->getDateTime(),
    'last_modified_by' => $_SESSION['userId'],
    'last_modified_datetime' => $general->getDateTime()
);

if ($systemConfig['user_type'] == 'remoteuser') {
    $eidData['remote_sample_code'] = $sampleData['sampleCode'];
    $eidData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
    $eidData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
    $eidData['remote_sample'] = 'yes';
    $eidData['result_status'] = 9;
} else {
    $eidData['sample_code'] = $sampleData['sampleCode'];
    $eidData['sample_code_format'] = $sampleData['sampleCodeFormat'];
    $eidData['sample_code_key'] = $sampleData['sampleCodeKey'];
    $eidData['remote_sample'] = 'no';
    $eidData['result_status'] = 6;
}

if (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '' && $_POST['sampleCollectionDate'] != null && $_POST['sampleCollectionDate'] != '') {

    $id = $db->insert("eid_form", $eidData);
}
if ($id > 0) {
    echo $id;
} else {
    echo 0;
}