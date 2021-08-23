<?php
ob_start();
#require_once('../../startup.php');  


$general=new \Vlsm\Models\General($db);

$module = 'C19';
$general = new \Vlsm\Models\General($db);

$tableName1="covid19_positive_confirmation_manifest";
$tableName2="form_covid19";
try {

    if(count($_POST['sampleCode']) > 0 && $_POST['packageCode'] != ''){
        $packageNo = $_POST['packageCode'];
        $data=array(
            'manifest_code'             =>  $packageNo,
            'manifest_status'           =>  'pending',
            'module'                    =>  $module,
            'added_by'                  =>  $_SESSION['userId'],
            'request_created_datetime'  =>  $general->getDateTime()
        );
                    
        $db->insert($tableName1,$data);
        $lastConfirmationManifestId = $db->getInsertId();

        foreach($_POST['sampleCode'] as $sample){
            $sampleQuery = "SELECT covid19_id, sample_collection_date, sample_package_code, province_id, sample_code, positive_test_manifest_code FROM form_covid19 where covid19_id IN (" . $sample . ") ORDER BY covid19_id";
            $sampleResult = $db->rawQueryOne($sampleQuery);
            if(isset($sampleResult['covid19_id']) && $sampleResult['covid19_id'] != ''){

                if ($sampleResult['positive_test_manifest_code'] == null || $sampleResult['positive_test_manifest_code'] == '' || $sampleResult['positive_test_manifest_code'] == 'null') {
                    $covid19Data = array();
                    $covid19Data['positive_test_manifest_id']   = $lastConfirmationManifestId;
                    $covid19Data['positive_test_manifest_code'] = $packageNo;
                    
                    $db=$db->where('covid19_id',$sampleResult['covid19_id']);
                    $db->update($tableName2,$covid19Data);
                }
            }
        }
        $_SESSION['alertMsg'] = "Manifest details added successfully";
        header("location:/covid-19/results/covid-19-confirmation-manifest.php");
    }else{
        $_SESSION['alertMsg'] = "Please select the sample code to processed";
        header("location:/covid-19/results/covid-19-add-confirmation-manifest.php");
    }
} catch (Exception $exc) {
    echo ($exc->getMessage());
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}