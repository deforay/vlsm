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
     $instanceId = '';
     if(isset($_SESSION['instanceId'])){
        $instanceId = $_SESSION['instanceId'];
     }
     //lab
     if(isset($_POST['newLab']) && trim($_POST['newLab'])!="" && trim($_POST['labId']) == 'other'){
          $labQuery ="SELECT facility_id FROM facility_details where facility_name='".$_POST['newLab']."' OR facility_name='".strtolower($_POST['newLab'])."' OR facility_name='".ucfirst(strtolower($_POST['newLab']))."'";
          $labResult = $db->rawQuery($labQuery);
          if(!isset($labResult[0]['facility_id'])){
             $data=array(
             'facility_name'=>$_POST['newLab'],
             'vlsm_instance_id'=>$instanceId,
             'facility_type'=>2,
             'country'=>4,
             'status'=>'active'
             );
             $id=$db->insert('facility_details',$data);
             $_POST['labId'] = $id;
          }else{
             $_POST['labId'] = $labResult[0]['facility_id'];
          }
     }
     //sample received date
     if(isset($_POST['sampleReceivedOn']) && trim($_POST['sampleReceivedOn'])!=""){
        $sampleReceivedDateLab = explode(" ",$_POST['sampleReceivedOn']);
        $_POST['sampleReceivedOn']=$general->dateFormat($sampleReceivedDateLab[0])." ".$sampleReceivedDateLab[1];  
     }else{
        $_POST['sampleReceivedOn'] = NULL;
     }
     //sample testing date at lab
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
        $sampleTestingDateAtLab = explode(" ",$_POST['sampleTestingDateAtLab']);
        $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateAtLab[0])." ".$sampleTestingDateAtLab[1];  
     }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
     }
     //set repeat sample and rejection reason
     $repeatSampleCollection = NULL;
     $rejectionReason = NULL;
     if(isset($_POST['sampleValidity']) && $_POST['sampleValidity']=='invalid'){
          if(isset($_POST['repeatSampleCollection']) && $_POST['repeatSampleCollection']!=""){
               $repeatSampleCollection = $_POST['repeatSampleCollection'];
          }
          if(isset($_POST['newRejectionReason']) && trim($_POST['newRejectionReason'])!="" && trim($_POST['rejectionReason']) =="other"){
               $rejectionReasonQuery ="SELECT rejection_reason_id FROM r_sample_rejection_reasons where rejection_reason_name='".$_POST['newRejectionReason']."' OR rejection_reason_name='".strtolower($_POST['newRejectionReason'])."' OR rejection_reason_name='".ucfirst(strtolower($_POST['newRejectionReason']))."'";
               $rejectionResult = $db->rawQuery($rejectionReasonQuery);
               if(!isset($rejectionResult[0]['rejection_reason_id'])){
                  $data=array(
                  'rejection_reason_name'=>$_POST['newRejectionReason'],
                  'rejection_type'=>'general',
                  'rejection_reason_status'=>'active'
                  );
                  $id=$db->insert('r_sample_rejection_reasons',$data);
                  $rejectionReason = $id;
               }else{
                  $rejectionReason = $rejectionResult[0]['rejection_reason_id'];
               }
          }else{
             $rejectionReason = $_POST['rejectionReason'];
          }
     }
     //reviewed by date time
     if(isset($_POST['reviewedByDatetime']) && trim($_POST['reviewedByDatetime'])!=""){
        $reviewedByDatetime = explode(" ",$_POST['reviewedByDatetime']);
        $_POST['reviewedByDatetime']=$general->dateFormat($reviewedByDatetime[0])." ".$reviewedByDatetime[1];
     }else{
        $_POST['reviewedByDatetime'] = NULL;
     }
      $vldata=array(
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='') ? $_POST['labId'] :  NULL,
          'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedOn'],
          'sample_tested_datetime'=>$_POST['sampleTestingDateAtLab'],
          'sample_test_quality'=>(isset($_POST['sampleValidity']) && $_POST['sampleValidity']!='') ? $_POST['sampleValidity'] :  NULL,
          'repeat_sample_collection'=>$repeatSampleCollection,
          'reason_for_sample_rejection'=>$rejectionReason,
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' && $_POST['result'] == 'actual_copies') ? $_POST['vlResult'] :  NULL,
          'result_value_absolute_decimal'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' && $_POST['result'] == 'actual_copies') ? number_format((float)$_POST['vlResult'], 2, '.', '') :  NULL,
          'result'=>(isset($_POST['result']) && trim($_POST['result'])!= '' && trim($_POST['result']) == 'actual_copies') ? $_POST['vlResult'] : $_POST['result'],
          'result_reviewed_by'=>(isset($_POST['reviewedBy']) && $_POST['reviewedBy']!='') ? $_POST['reviewedBy'] :  NULL,
          'result_reviewed_datetime'=>$_POST['reviewedByDatetime'],
          'lab_contact_person'=>(isset($_POST['labContactPerson']) && $_POST['labContactPerson']!='') ? $_POST['labContactPerson'] :  NULL,
          'approver_comments'=>(isset($_POST['labComments']) && $_POST['labComments']!='') ? $_POST['labComments'] :  NULL,
          'result_status'=>(isset($_POST['status']) && $_POST['status']!='') ? $_POST['status'] :  NULL,
          'last_modified_by'=>$_SESSION['userId'],
          'last_modified_datetime'=>$general->getDateTime()
       );
       //echo "<pre>";var_dump($vldata);die;
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
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}