<?php
ob_start();
require_once('../../startup.php');  
include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general=new General($db);

$module = 'C19';
$general = new General($db);

$tableName1="covid19_positive_confirmation_manifest";
$tableName2="form_covid19";
try {

    if(count($_POST['sampleCode']) > 0){
        foreach($_POST['sampleCode'] as $sample){
            $sampleQuery = "SELECT covid19_id, sample_collection_date, sample_package_code, province_id, sample_code, positive_test_manifest_code FROM form_covid19 where covid19_id IN (" . $sample . ") ORDER BY covid19_id";
            $sampleResult = $db->rawQueryOne($sampleQuery);
            if(isset($sampleResult['covid19_id']) && $sampleResult['covid19_id'] != ''){

                $provinceCode = null;
                if (isset($sampleResult['province_id']) && !empty($sampleResult['province_id'])) {
                    $provinceQuery = "SELECT * FROM province_details WHERE province_id= " . $sampleResult['province_id'];
                    $provinceResult = $db->rawQueryOne($provinceQuery);
                    if($provinceResult){
                        $provinceCode = $provinceResult['province_code'];
                    }
                }
                if ($sampleResult['positive_test_manifest_code'] == null || $sampleResult['positive_test_manifest_code'] == '' || $sampleResult['positive_test_manifest_code'] == 'null') {
                    
                    $packageNo = strtoupper($module) . date('ymd') . $provinceCode . $sampleResult['covid19_id'] .  strtoupper($general->generateRandomString(6));
                    
                    $data=array(
                        'manifest_code'             =>  $packageNo,
                        'manifest_status'           =>  'active',
                        'module'                    =>  $module,
                        'added_by'                  =>  $_SESSION['userId'],
                        'request_created_datetime'  =>  $general->getDateTime()
                    );
                                
                    $db->insert($tableName1,$data);
                    $lastConfirmationManifestId = $db->getInsertId();
    
                    $covid19Data = array();
                    $covid19Data['positive_test_manifest_id']   = $lastConfirmationManifestId;
                    $covid19Data['positive_test_manifest_code'] = $packageNo;
                    
                    $db=$db->where('covid19_id',$sampleResult['covid19_id']);
                    $db->update($tableName2,$covid19Data);
                }
            }
        }
        header("location:/covid-19/results/covid-19-confirmation-manifest.php");
    }else{
        header("location:/covid-19/results/covid-19-add-confirmation-manifest.php");
    }
} catch (Exception $exc) {
    echo ($exc->getMessage());
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}