<?php
ob_start();
session_start();
include('MysqliDb.php');
include('General.php');
$general=new General();
$tableName="vl_request_form";
//system config
$id = '';
    $systemConfigQuery ="SELECT * from system_config";
    $systemConfigResult=$db->query($systemConfigQuery);
    $sarr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
      $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
    }
    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
        $sampleDate = explode(" ",$_POST['sampleCollectionDate']);
        $_POST['sampleCollectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
   }else{
       $_POST['sampleCollectionDate'] = NULL;
   }

    if($sarr['user_type']=='remoteuser'){
        $sampleCode = 'remote_sample_code';
        $sampleCodeKey = 'remote_sample_code_key';
    }else{
        $sampleCode = 'sample_code';
        $sampleCodeKey = 'sample_code_key';
    }
    $existSampleQuery ="SELECT ".$sampleCode.",".$sampleCodeKey." FROM vl_request_form where ".$sampleCode." ='".trim($_POST['sampleCode'])."'";
    $existResult = $db->rawQuery($existSampleQuery);
    if(isset($existResult[0][$sampleCodeKey]) && $existResult[0][$sampleCodeKey]!=''){
        $sCode = $existResult[0][$sampleCodeKey] + 1;
        $strparam = strlen($sCode);
        $zeros = substr("000", $strparam);
        $maxId = $zeros.$sCode;
        $_POST['sampleCode'] = $_POST['sampleCodeFormat'].$maxId;
        $_POST['sampleCodeKey'] = $maxId;
    }
    $vldata = array(
                    'vlsm_country_id'=>$_POST['countryId'],
                    'vlsm_instance_id'=>$_SESSION['instanceId'],
                    'request_created_by'=>$_SESSION['userId'],
                    'request_created_datetime'=>$general->getDateTime(),
                    'last_modified_by'=>$_SESSION['userId'],
                    'last_modified_datetime'=>$general->getDateTime(),
                    'manual_result_entry'=>'yes',
                    'result_status'=>9,
                    'sample_code_format'=>(isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat']!='') ? $_POST['sampleCodeFormat'] :  NULL,
                    );

    if($sarr['user_type']=='remoteuser'){
        $vldata['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL;
        $vldata['remote_sample_code_key'] = (isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey']!='') ? $_POST['sampleCodeKey'] :  NULL;
        $vldata['remote_sample'] = 'yes';
    }else{
        $vldata['sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL;
        $vldata['serial_no'] = (isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL;
        $vldata['sample_code_key'] = (isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey']!='') ? $_POST['sampleCodeKey'] :  NULL;
    }
    if(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' && $_POST['sampleCollectionDate']!=NULL && $_POST['sampleCollectionDate']!=''){
    $id=$db->insert($tableName,$vldata);
    }
    if($id>0){
        echo $id;
    }else{
        echo 0;
    }




    
