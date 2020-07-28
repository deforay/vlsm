<?php
ob_start();
session_start();

#require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . '/models/Covid19.php');
// echo "<pre>";print_r($_POST);
$general = new General($db);
$covid19Model = new Model_Covid19($db);
//$globalConfig = $general->getGlobalConfig();
$systemConfig = $general->getSystemConfig();

$i;
try {
    $provinceCode = (isset($_POST['provinceCode']) && !empty($_POST['provinceCode'])) ? $_POST['provinceCode'] : null;
    $provinceId = (isset($_POST['provinceId']) && !empty($_POST['provinceId'])) ? $_POST['provinceId'] : null;
    $sampleCollectionDate = (isset($_POST['sampleCollectionDate']) && !empty($_POST['sampleCollectionDate'])) ? $_POST['sampleCollectionDate'] : null;

    $sampleJson = $covid19Model->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
    $sampleData = json_decode($sampleJson, true);

    $sampleDate = explode(" ", $_POST['sampleCollectionDate']);
    $_POST['sampleCollectionDate'] = $general->dateFormat($sampleDate[0]) . " " . $sampleDate[1];
    
    $covid19Data = array();
    $covid19Data = array(
        'vlsm_country_id' => $_POST['countryId'],
        'sample_collection_date' => $_POST['sampleCollectionDate'],
        'vlsm_instance_id' => $_SESSION['instanceId'],
        'request_created_by' => $_SESSION['userId'],
        'request_created_datetime' => $general->getDateTime(),
        'last_modified_by' => $_SESSION['userId'],
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
    if (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '' && $_POST['sampleCollectionDate'] != null && $_POST['sampleCollectionDate'] != '') {
        $id = $db->insert("form_covid19", $covid19Data);
    }
    if ($id > 0) {
        echo $id;
    } else {
        echo 0;
    }
}
catch(Exception $e) {
    echo 'Message: ' .$e->getMessage();
}
