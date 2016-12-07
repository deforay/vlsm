<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
$tableName2="log_result_updates";
try {
     //Set sample received date
    if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
        $sampleReceivedDate = explode(" ",$_POST['sampleReceivedDate']);
        $_POST['sampleReceivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
    }else{
       $_POST['sampleReceivedDate'] = NULL;
    }
    //Set sample rejection reason
    if(isset($_POST['status']) && trim($_POST['status']) != ''){
        if($_POST['status'] == 4){
            if(trim($_POST['rejectionReason']) == "other" && trim($_POST['newRejectionReason']!= '')){
                $data=array(
                'rejection_reason_name'=>$_POST['newRejectionReason'],
                'rejection_reason_status'=>'active'
                );
                $id=$db->insert('r_sample_rejection_reasons',$data);
                $_POST['rejectionReason'] = $id;
            }
        }else{
            $_POST['rejectionReason'] = NULL;
        }
    }
     //Set sample testing date
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }else{
         $_POST['sampleTestingDateAtLab'] = NULL;
     }
     //Set Date of Completion of Viral Load
    if(isset($_POST['dateOfCompletionOfViralLoad']) && trim($_POST['dateOfCompletionOfViralLoad'])!=""){
        $_POST['dateOfCompletionOfViralLoad']=$general->dateFormat($_POST['dateOfCompletionOfViralLoad']);  
    }else{
        $_POST['dateOfCompletionOfViralLoad'] = NULL;
    }
    if(!isset($_POST['sampleCode']) || trim($_POST['sampleCode'])== ''){
        $_POST['sampleCode'] = NULL;
    }
        $vldata=array(
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
          'sample_code'=>$_POST['sampleCode'],
          'lab_no'=>$_POST['sampleCode'],
          'serial_no'=>$_POST['sampleCode'],
          'date_of_completion_of_viral_load'=>$_POST['dateOfCompletionOfViralLoad'],
          'vl_test_platform'=>$_POST['testingPlatform'],
          'log_value'=>$_POST['vlLog'],
          'result'=>$_POST['vlResult'],
          'lab_tested_date'=>$_POST['sampleTestingDateAtLab'],
          'modified_on'=>$general->getDateTime()
        );
        if(isset($_POST['status']) && trim($_POST['status'])!= ''){
            $vldata['status'] = $_POST['status'];
            if(isset($_POST['rejectionReason'])){
                $vldata['sample_rejection_reason'] = $_POST['rejectionReason'];
            }
        }
        $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
        $db->update($tableName,$vldata);
        $_SESSION['alertMsg']="VL result updated successfully";
         //Add event log
        $eventType = 'update-vl-result-drc';
        $action = ucwords($_SESSION['userName']).' updated a result data with the patient code '.$_POST['dubPatientArtNo'];
        $resource = 'vl-result-drc';
         $data=array(
        'event_type'=>$eventType,
        'action'=>$action,
        'resource'=>$resource,
        'date_time'=>$general->getDateTime()
        );
        $db->insert($tableName1,$data);
        //Add update result log
        $data=array(
        'user_id'=>$_SESSION['userId'],
        'vl_sample_id'=>$_POST['vlSampleId'],
        'updated_on'=>$general->getDateTime()
        );
        $db->insert($tableName2,$data);
        header("location:vlTestResult.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}