<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

#require_once('../../startup.php');


// echo "<pre>";print_r($_POST);
$general = new \Vlsm\Models\General($db);
$covid19Model = new \Vlsm\Models\Covid19($db);

$globalConfig = $general->getGlobalConfig();
$systemConfig = $general->getSystemConfig();

$i;
try {
    $provinceCode = (isset($_POST['provinceCode']) && !empty($_POST['provinceCode'])) ? $_POST['provinceCode'] : null;
    $provinceId = (isset($_POST['provinceId']) && !empty($_POST['provinceId'])) ? $_POST['provinceId'] : null;
    $sampleCollectionDate = (isset($_POST['sampleCollectionDate']) && !empty($_POST['sampleCollectionDate'])) ? $_POST['sampleCollectionDate'] : null;



    if (empty($sampleCollectionDate)) {
        echo 0;
        exit();
    }

    // PNG FORM CANNOT HAVE PROVINCE EMPTY
    if ($globalConfig['vl_form'] == 5) {
        if (empty($provinceId)) {
            echo 0;
            exit();
        }
    }
    
    $rowData = false;
    if (isset($_POST['api']) && $_POST['api'] = "yes") {
        if($_POST['sampleCode'] != "" && !empty($_POST['sampleCode'])){
            $sQuery = "SELECT covid19_id, sample_code, sample_code_format, sample_code_key, remote_sample_code, remote_sample_code_format, remote_sample_code_key FROM form_covid19 where sample_code like '%".$_POST['sampleCode']."%' or remote_sample_code like '%".$_POST['sampleCode']."%' limit 1";
            // die($sQuery);
            $rowData = $db->rawQueryOne($sQuery);
            if($rowData){
                $sampleData['sampleCode'] = (!empty($rowData['sample_code']))?$rowData['sample_code']:$rowData['remote_sample_code'];
                $sampleData['sampleCodeFormat'] = (!empty($rowData['sample_code_format']))?$rowData['sample_code_format']:$rowData['remote_sample_code_format'];
                $sampleData['sampleCodeKey'] = (!empty($rowData['sample_code_key']))?$rowData['sample_code_key']:$rowData['remote_sample_code_key'];
            }else{
                $sampleJson = $covid19Model->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
                $sampleData = json_decode($sampleJson, true);
            }
        } else{
            $sampleJson = $covid19Model->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
            $sampleData = json_decode($sampleJson, true);
        }
    }else {
        $sampleJson = $covid19Model->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, null, $provinceId);
        $sampleData = json_decode($sampleJson, true);
        $sampleDate = explode(" ", $_POST['sampleCollectionDate']);
        $_POST['sampleCollectionDate'] = $general->dateFormat($sampleDate[0]) . " " . $sampleDate[1];
    }
    if(!isset($_POST['countryId']) || $_POST['countryId'] ==''){
        $_POST['countryId'] = '';
    }
    $covid19Data = array();
    if (isset($_POST['api']) && $_POST['api'] = "yes") {
        $covid19Data = array(
            'vlsm_country_id' => $_POST['countryId'],
            'sample_collection_date' => $_POST['sampleCollectionDate'],
            'vlsm_instance_id' => $_POST['instanceId'],
            'province_id' => $provinceId,
            'request_created_by' => '',
            'request_created_datetime' => $general->getDateTime(),
            'last_modified_by' => '',
            'last_modified_datetime' => $general->getDateTime()
        );
    }
    else
    {
        $covid19Data = array(
            'vlsm_country_id' => $_POST['countryId'],
            'sample_collection_date' => $_POST['sampleCollectionDate'],
            'vlsm_instance_id' => $_SESSION['instanceId'],
            'province_id' => $provinceId,
            'request_created_by' => $_SESSION['userId'],
            'request_created_datetime' => $general->getDateTime(),
            'last_modified_by' => $_SESSION['userId'],
            'last_modified_datetime' => $general->getDateTime()
        );
    }
    
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
    /* echo "<pre>";
	print_r($covid19Data);die; */
    $id = 0;
    if($rowData){
        $db = $db->where('covid19_id', $rowData['covid19_id']);
		$id = $db->update("form_covid19", $covid19Data);
        $_POST['covid19SampleId'] = $rowData['covid19_id'];
    } else{

        if (isset($_POST['api']) && $_POST['api'] = "yes") {
            $id = $db->insert("form_covid19", $covid19Data);
            $_POST['covid19SampleId'] = $id;
        } else {
            if (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '' && $_POST['sampleCollectionDate'] != null && $_POST['sampleCollectionDate'] != '') {
                $id = $db->insert("form_covid19", $covid19Data);
            }
        }
    }
    if (isset($_POST['api']) && $_POST['api'] == "yes") {
        exit();
    }
    if ($id > 0) {
        echo $id;
    } else {
        echo 0;
    }
} catch (Exception $e) {
    echo 'Message: ' . $e->getMessage();
}
