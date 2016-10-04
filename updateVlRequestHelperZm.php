<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();

$tableName="vl_request_form";
$tableName1="activity_log";
try {
     
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }
     if(!isset($_POST['noResult'])){
          $_POST['noResult'] = '';
     }
     $vldata=array(
          'serial_no'=>$_POST['serialNo'],
          'sample_code'=>$_POST['serialNo'],
          'lab_no'=>$_POST['labNo'],
          'lab_id'=>$_POST['labId'],
          'vl_test_platform'=>$_POST['testingPlatform'],
          'sample_id'=>$_POST['specimenType'],
          'lab_tested_date'=>$_POST['sampleTestingDateAtLab'],
          'absolute_value'=>$_POST['vlResult'],
          'log_value'=>$_POST['vlLog'],
          'comments'=>$_POST['labComments'],
          'result_approved_by'=>$_POST['approvedBy'],
          'result_reviewed_by'=>$_POST['reviewedBy'],
          'rejection'=>$_POST['noResult'],
          'status'=>$_POST['status'],
          'modified_on'=>$general->getDateTime()
        );
          //print_r($vldata);die;
          $db=$db->where('vl_sample_id',$_POST['treamentId']);
          $db->update($tableName,$vldata);
          $_SESSION['alertMsg']="VL result updated successfully";
          header("location:vlResultApproval.php");
    
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}