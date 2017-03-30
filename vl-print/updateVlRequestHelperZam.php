<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
$tableName2="log_result_updates";
try {
        if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
        }
        if($_POST['testingPlatform']!=''){
          $platForm = explode("##",$_POST['testingPlatform']);
          $_POST['testingPlatform'] = $platForm[0];
        }
       $vldata=array(
            'vl_test_platform'=>(isset($_POST['testingPlatform']) && $_POST['testingPlatform']!='' ? $_POST['testingPlatform'] :  NULL) ,
            'lab_tested_date'=>(isset($_POST['sampleTestingDateAtLab']) && $_POST['sampleTestingDateAtLab']!='' ? $_POST['sampleTestingDateAtLab'] :  NULL) ,
            'absolute_value'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL) ,
            'result'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL) ,
            'comments'=>(isset($_POST['labComments']) && $_POST['labComments']!='' ? $_POST['labComments'] :  NULL) ,
            'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL) ,
            'sample_type'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='' ? $_POST['specimenType'] :  NULL) ,
            'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='' ? $_POST['testMethods'] :  NULL),
            'result_status'=>(isset($_POST['status']) && $_POST['status']!='' ? $_POST['status'] :  NULL) ,
            'is_sample_rejected'=>(isset($_POST['noResult']) && $_POST['noResult']!='' ? $_POST['noResult'] :  NULL) ,
            'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
            'modified_on'=>$general->getDateTime()
        );
          $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
          $db->update($tableName,$vldata);
          $_SESSION['alertMsg']="VL result updated successfully";
          //Add update result log
          $data=array(
          'user_id'=>$_SESSION['userId'],
          'vl_sample_id'=>$_POST['vlSampleId'],
          'updated_on'=>$general->getDateTime()
          );
          $db->insert($tableName2,$data);
          header("location:vlResultApproval.php");
     
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}