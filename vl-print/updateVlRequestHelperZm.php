<?php
session_start();
ob_start();
require_once('../startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general=new General($db);
$tableName="vl_request_form";
$tableName1="activity_log";
$tableName2="log_result_updates";
try {
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }
     if(!isset($_POST['noResult'])){
          $_POST['noResult'] = '';
     }
     $_POST['result'] = '';
     if($_POST['vlResult']!=''){
          $_POST['result'] = $_POST['vlResult'];
     }else if($_POST['vlLog']!=''){
          $_POST['result'] = $_POST['vlLog'];
     }
     //check vl result textbox changes
     $viralLoadData = array('result_value_absolute'=>$_POST['vlResult'],'result_value_log'=>$_POST['vlLog']);
     $db = $db->where('vl_sample_id',$_POST['treamentId']);
     $vloadResultUpdate = $db->update($tableName,$viralLoadData);
     
     if($_POST['testingPlatform']!=''){
          $platForm = explode("##",$_POST['testingPlatform']);
          $_POST['testingPlatform'] = $platForm[0];
          }
       $vldata=array(
          'serial_no'=>$_POST['serialNo'],
          'sample_code'=>$_POST['serialNo'],
          'lab_code'=>(isset($_POST['labNo']) && $_POST['labNo']!='' ? $_POST['labNo'] :  NULL),
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='' ? $_POST['labId'] :  NULL),
          'vl_test_platform'=>(isset($_POST['testingPlatform']) && $_POST['testingPlatform']!='' ? $_POST['testingPlatform'] :  NULL),
          'sample_tested_datetime'=>(isset($_POST['sampleTestingDateAtLab']) && $_POST['sampleTestingDateAtLab']!='' ? $_POST['sampleTestingDateAtLab'] :  NULL),
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'result_value_log'=>(isset($_POST['vlLog']) && $_POST['vlLog']!='' ? $_POST['vlLog'] :  NULL),
          'approver_comments'=>(isset($_POST['labComments']) && $_POST['labComments']!='' ? $_POST['labComments'] :  NULL),
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL),
          'result_reviewed_by'=>(isset($_POST['reviewedBy']) && $_POST['reviewedBy']!='' ? $_POST['reviewedBy'] :  NULL),
          'is_sample_rejected'=>(isset($_POST['noResult']) && $_POST['noResult']!='' ? $_POST['noResult'] :  NULL),
          'last_modified_datetime'=>$general->getDateTime(),
          'data_sync'=>0
        );
          if(isset($_POST['specimenType']) && trim($_POST['specimenType'])!= ''){
               $vldata['sample_type']=$_POST['specimenType'];
          }else{
               $vldata['sample_type'] = null;
          }
          if(isset($_POST['status']) && trim($_POST['status'])!= ''){
               $vldata['result_status']=$_POST['status'];
          }
          //print_r($vldata);die;
          if($vloadResultUpdate){
            $vldata['manual_result_entry']='yes';
            $vldata['import_machine_file_name']='';
          }
          $db=$db->where('vl_sample_id',$_POST['treamentId']);
          $db->update($tableName,$vldata);
          $_SESSION['alertMsg']="VL result updated successfully";
          //Add update result log
          $data=array(
          'user_id'=>$_SESSION['userId'],
          'vl_sample_id'=>$_POST['treamentId'],
          'test_type'=>'vl',
          'updated_on'=>$general->getDateTime()
          );
          $db->insert($tableName2,$data);
          header("location:vlTestResult.php");
    
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}