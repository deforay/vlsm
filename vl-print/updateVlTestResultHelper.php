<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
//include('../header.php');
include('../General.php');

$general=new Deforay_Commons_General();

$tableName="vl_request_form";
$treamentId=(int) $_POST['treamentId'];

try {
   if(isset($_POST['sampleReceivedOn']) && trim($_POST['sampleReceivedOn'])!=""){
               $sampleReceiveDate = explode(" ",$_POST['sampleReceivedOn']);
               $_POST['sampleReceivedOn']=$general->dateFormat($sampleReceiveDate[0])." ".$sampleReceiveDate[1];
          }
          
          if(isset($_POST['sampleTestedOn']) && trim($_POST['sampleTestedOn'])!=""){
               $sampletestDate = explode(" ",$_POST['sampleTestedOn']);
               $_POST['sampleTestedOn']=$general->dateFormat($sampletestDate[0])." ".$sampletestDate[1];
          }
          
          if(isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn'])!=""){
               $sampleDispatchDate = explode(" ",$_POST['resultDispatchedOn']);
               $_POST['resultDispatchedOn']=$general->dateFormat($sampleDispatchDate[0])." ".$sampleDispatchDate[1];
          }
          
          if(isset($_POST['reviewedOn']) && trim($_POST['reviewedOn'])!=""){
               $sampleReviewDate = explode(" ",$_POST['reviewedOn']);
               $_POST['reviewedOn']=$general->dateFormat($sampleReviewDate[0])." ".$sampleReviewDate[1];
          }
   
   $vldata =  array('lab_name'=>$_POST['labName'],
          'lab_contact_person'=>$_POST['labContactPerson'],
          'lab_phone_number'=>$_POST['labPhoneNo'],
          'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedOn'],
          'sample_tested_datetime'=>$_POST['sampleTestedOn'],
          'result_dispatched_datetime'=>$_POST['resultDispatchedOn'],
          'result_reviewed_by'=>$_SESSION['userId'],
          'result_reviewed_datetime'=>$_POST['reviewedOn'],
          'result_value_log'=>$_POST['logValue'],
          'result_value_absolute'=>$_POST['absoluteValue'],
          'result_value_text'=>$_POST['textValue'],
          'result'=>$_POST['result'],
          'approver_comments'=>$_POST['comments'],
          'result_status'=>$_POST['status'],
        );
          //print_r($vldata);die;
          $db=$db->where('vl_sample_id',$treamentId);
          $db->update($tableName,$vldata);
          
          $_SESSION['alertMsg']="VL Result updated successfully";
 header("location:vlTestResult.php"); 
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}