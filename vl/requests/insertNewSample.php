<?php
include_once '../../startup.php';
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . '/models/Vl.php');

$general = new General($db);
$vlModel = new Model_Vl($db);


$globalConfig = $general->getGlobalConfig();
$systemConfig = $general->getSystemConfig();

$id = 0;


$provinceCode = (isset($_POST['provinceCode']) && !empty($_POST['provinceCode'])) ? $_POST['provinceCode'] : null;
$provinceId = (isset($_POST['provinceId']) && !empty($_POST['provinceId'])) ? $_POST['provinceId'] : null;
$sampleCollectionDate = (isset($_POST['sampleCollectionDate']) && !empty($_POST['sampleCollectionDate'])) ? $_POST['sampleCollectionDate'] : null;


if(empty($sampleCollectionDate)){
    echo 0; exit();
}

// PNG FORM CANNOT HAVE PROVINCE EMPTY
if ($globalConfig['vl_form'] == 5) {
    if(empty($provinceId)){
        echo 0; exit();
    }
}

$sampleJson = $vlModel->generateVLSampleID($provinceCode, $sampleCollectionDate, null, $provinceId);
$sampleData = json_decode($sampleJson, true);


$sampleDate = explode(" ", $_POST['sampleCollectionDate']);
$_POST['sampleCollectionDate'] = $general->dateFormat($sampleDate[0]) . " " . $sampleDate[1];


$vlData = array();
$vlData = array(
    'vlsm_country_id' => $_POST['countryId'],
    'sample_collection_date' => $_POST['sampleCollectionDate'],
    'vlsm_instance_id' => $_SESSION['instanceId'],
    'province_id' => $provinceId,
    'request_created_by' => $_SESSION['userId'],
    'request_created_datetime' => $general->getDateTime(),
    'last_modified_by' => $_SESSION['userId'],
    'last_modified_datetime' => $general->getDateTime()
);

if ($systemConfig['user_type'] == 'remoteuser') {
    $vlData['remote_sample_code'] = $sampleData['sampleCode'];
    $vlData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
    $vlData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
    $vlData['remote_sample'] = 'yes';
    $vlData['result_status'] = 9;
} else {
    $vlData['sample_code'] = $sampleData['sampleCode'];
    $vlData['sample_code_format'] = $sampleData['sampleCodeFormat'];
    $vlData['sample_code_key'] = $sampleData['sampleCodeKey'];
    $vlData['remote_sample'] = 'no';
    $vlData['result_status'] = 6;
}

if (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '' && $_POST['sampleCollectionDate'] != null && $_POST['sampleCollectionDate'] != '') {
    $id = $db->insert("vl_request_form", $vlData);
}
if ($id > 0) {
    echo $id;
} else {
    echo 0;
}
