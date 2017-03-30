<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
$vlTestReasonTable="r_vl_test_reasons";
$tableName2="log_result_updates";
try {
    if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
        $_POST['dob']=$general->dateFormat($_POST['dob']);  
    }else{
        $_POST['dob'] = NULL;
    }
    
    if(isset($_POST['collectionDate']) && trim($_POST['collectionDate'])!=""){
        $sampleDate = explode(" ",$_POST['collectionDate']);
        $_POST['collectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
    }else{
        $_POST['collectionDate'] = NULL;
    }
    if(isset($_POST['failedTestDate']) && trim($_POST['failedTestDate'])!=""){
        $failedtestDate = explode(" ",$_POST['failedTestDate']);
        $_POST['failedTestDate']=$general->dateFormat($failedtestDate[0])." ".$failedtestDate[1];
    }else{
        $_POST['failedTestDate'] = NULL;
    }
    
    if(isset($_POST['regStartDate']) && trim($_POST['regStartDate'])!=""){
        $_POST['regStartDate']=$general->dateFormat($_POST['regStartDate']);  
    }else{
        $_POST['regStartDate'] = NULL;
    }
    
    if(isset($_POST['receivedDate']) && trim($_POST['receivedDate'])!=""){
        $sampleReceivedDate = explode(" ",$_POST['receivedDate']);
        $_POST['receivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
    }else{
        $_POST['receivedDate'] = NULL;
    }
    if(isset($_POST['testDate']) && trim($_POST['testDate'])!=""){
        $sampletestDate = explode(" ",$_POST['testDate']);
        $_POST['testDate']=$general->dateFormat($sampletestDate[0])." ".$sampletestDate[1];
    }else{
        $_POST['testDate'] = NULL;
    }
    if(isset($_POST['qcDate']) && trim($_POST['qcDate'])!=""){
        $_POST['qcDate']=$general->dateFormat($_POST['qcDate']);
    }else{
        $_POST['qcDate'] = NULL;
    }
    if(isset($_POST['reportDate']) && trim($_POST['reportDate'])!=""){
        $_POST['reportDate']=$general->dateFormat($_POST['reportDate']);
    }else{
        $_POST['reportDate'] = NULL;
    }
    if(isset($_POST['clinicDate']) && trim($_POST['clinicDate'])!=""){
        $_POST['clinicDate']=$general->dateFormat($_POST['clinicDate']);
    }else{
        $_POST['clinicDate'] = NULL;
    }
    
    if($_POST['testingTech']!=''){
        $platForm = explode("##",$_POST['testingTech']);
        $_POST['testingTech'] = $platForm[0];
    }
    if($_POST['failedTestingTech']!=''){
        $platForm = explode("##",$_POST['failedTestingTech']);
        $_POST['failedTestingTech'] = $platForm[0];
    }
    
    $vldata=array(
        'is_sample_rejected'=>(isset($_POST['sampleQuality']) && $_POST['sampleQuality']!='' ? $_POST['sampleQuality'] :  NULL),
        'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
        'batch_quality'=>(isset($_POST['batchQuality']) && $_POST['batchQuality']!='' ? $_POST['batchQuality'] :  NULL),
        'sample_test_quality'=>(isset($_POST['testQuality']) && $_POST['testQuality']!='' ? $_POST['testQuality'] :  NULL),
        'batch_id'=>(isset($_POST['batchNo']) && $_POST['batchNo']!='' ? $_POST['batchNo'] :  NULL),
        'failed_test_date'=>$_POST['failedTestDate'],
        'failed_test_tech'=>(isset($_POST['failedTestingTech']) && $_POST['failedTestingTech']!='' ? $_POST['failedTestingTech'] :  NULL),
        'failed_vl_result'=>(isset($_POST['failedvlResult']) && $_POST['failedvlResult']!='' ? $_POST['failedvlResult'] :  NULL),
        'failed_batch_quality'=>(isset($_POST['failedbatchQuality']) && $_POST['failedbatchQuality']!='' ? $_POST['failedbatchQuality'] :  NULL),
        'failed_sample_test_quality'=>(isset($_POST['failedtestQuality']) && $_POST['failedtestQuality']!='' ? $_POST['failedtestQuality'] :  NULL),
        'failed_batch_id'=>(isset($_POST['failedbatchNo']) && $_POST['failedbatchNo']!='' ? $_POST['failedbatchNo'] :  NULL),
        'lab_id'=>(isset($_POST['laboratoryId']) && $_POST['laboratoryId']!='' ? $_POST['laboratoryId'] :  NULL),
        'sample_type'=>(isset($_POST['sampleType']) && $_POST['sampleType']!='' ? $_POST['sampleType'] :  NULL),
        'date_sample_received_at_testing_lab'=>$_POST['receivedDate'],
        'tech_name_png'=>(isset($_POST['techName']) && $_POST['techName']!='' ? $_POST['techName'] :  NULL),
        'lab_tested_date'=>(isset($_POST['testDate']) && $_POST['testDate']!='' ? $_POST['testDate'] :  NULL),
        'last_viral_load_result'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
        'vl_test_platform'=>(isset($_POST['testingTech']) && $_POST['testingTech']!='' ? $_POST['testingTech'] :  NULL),
        'result'=>(isset($_POST['finalViralResult']) && $_POST['finalViralResult']!='' ? $_POST['finalViralResult'] :  NULL),
        'qc_tech_name'=>(isset($_POST['qcTechName']) && $_POST['qcTechName']!='' ? $_POST['qcTechName'] :  NULL),
        'qc_tech_sign'=>(isset($_POST['qcTechSign']) && $_POST['qcTechSign']!='' ? $_POST['qcTechSign'] :  NULL),
        'qc_date'=>$_POST['qcDate'],
        'clinic_date'=>$_POST['clinicDate'],
        'report_date'=>$_POST['reportDate'],
        );
    
          if(isset($_POST['status']) && trim($_POST['status'])!= ''){
               $vldata['result_status']=$_POST['status'];
          }
        $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
        $id=$db->update($tableName,$vldata);
    
        if($id>0){
          $_SESSION['alertMsg']="VL result updated successfully";
          //Add update result log
          $data=array(
          'user_id'=>$_SESSION['userId'],
          'vl_sample_id'=>$_POST['vlSampleId'],
          'updated_on'=>$general->getDateTime()
          );
          $db->insert($tableName2,$data);
          }else{
             $_SESSION['alertMsg']="Please try again later";
        }
        header("location:vlResultApproval.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}