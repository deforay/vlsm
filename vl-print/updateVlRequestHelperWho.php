<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName2="log_result_updates";
try {
    $_POST['result'] = '';
    if($_POST['rmTestingVlValue']!=''){
     $_POST['result'] = $_POST['rmTestingVlValue']; 
    }
    if($_POST['repeatTestingVlValue']!=''){
     $_POST['result'] = $_POST['repeatTestingVlValue']; 
    }
    if($_POST['suspendTreatmentVlValue']!=''){
     $_POST['result'] = $_POST['suspendTreatmentVlValue']; 
    }
    
     $vldata=array(
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'last_vl_date_routine'=>(isset($_POST['rmTestingLastVLDate']) && $_POST['rmTestingLastVLDate']!='' ? $general->dateFormat($_POST['rmTestingLastVLDate']) :  NULL),
          'last_vl_result_routine'=>(isset($_POST['rmTestingVlValue']) && $_POST['rmTestingVlValue']!='' ? $_POST['rmTestingVlValue'] :  NULL),
          'last_vl_date_failure_ac'=>(isset($_POST['repeatTestingLastVLDate']) && $_POST['repeatTestingLastVLDate']!='' ? $general->dateFormat($_POST['repeatTestingLastVLDate']) :  NULL),
          'last_vl_result_failure_ac'=>(isset($_POST['repeatTestingVlValue']) && $_POST['repeatTestingVlValue']!='' ? $_POST['repeatTestingVlValue'] :  NULL),
          'last_vl_date_failure'=>(isset($_POST['suspendTreatmentLastVLDate']) && $_POST['suspendTreatmentLastVLDate']!='' ? $general->dateFormat($_POST['suspendTreatmentLastVLDate']) :  NULL),
          'last_vl_result_failure'=>(isset($_POST['suspendTreatmentVlValue']) && $_POST['suspendTreatmentVlValue']!='' ? $_POST['suspendTreatmentVlValue'] :  NULL),
          'request_clinician_name'=>(isset($_POST['reqClinician']) && $_POST['reqClinician']!='' ? $_POST['reqClinician'] :  NULL),
          'test_requested_on'=>(isset($_POST['requestDate']) && $_POST['requestDate']!='' ? $general->dateFormat($_POST['requestDate']) :  NULL),
          'last_modified_by'=>$_SESSION['userId'],
          'result_status'=>(isset($_POST['status']) && $_POST['status']!='' ? $_POST['status'] :  NULL) ,
          'last_modified_datetime'=>$general->getDateTime(),
          'manual_result_entry'=>'yes'
        );
          $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
          $id=$db->update($tableName,$vldata);
          if($id>0){
               $_SESSION['alertMsg']="VL request updated successfully";
               //Add event log
               $data=array(
                'user_id'=>$_SESSION['userId'],
                'vl_sample_id'=>$_POST['vlSampleId'],
                'updated_on'=>$general->getDateTime()
                );
                $db->insert($tableName2,$data);
                header("location:vlResultApproval.php");
            }else{
               $_SESSION['alertMsg']="Please try again later";
            }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}