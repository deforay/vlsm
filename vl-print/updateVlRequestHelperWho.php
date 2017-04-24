<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName2="log_result_updates";
try {
    $testingPlatform = '';
    if(isset($_POST['testingPlatform']) && trim($_POST['testingPlatform'])!=''){
        $platForm = explode("##",$_POST['testingPlatform']);
        $testingPlatform = $platForm[0];
    }
    if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
        $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
        $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
    }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
    }
    if(isset($_POST['vlResult']) && trim($_POST['vlResult']) != ''){
        $_POST['result'] = $_POST['vlResult'];
     }else{
        $_POST['result'] = NULL;  
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
          'vl_test_platform'=>$testingPlatform,
          'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='') ? $_POST['testMethods'] :  NULL,
          'sample_tested_datetime'=>$_POST['sampleTestingDateAtLab'],
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='') ? $_POST['vlResult'] :  NULL,
          'result'=>(isset($_POST['result']) && $_POST['result']!='') ? $_POST['result'] :  NULL,
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='') ? $_POST['labId'] :  NULL,
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='') ? $_POST['approvedBy'] :  NULL,
          'approver_comments'=>(isset($_POST['labComments']) && trim($_POST['labComments'])!='') ? trim($_POST['labComments']) :  NULL,
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