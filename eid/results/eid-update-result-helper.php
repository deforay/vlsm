<?php
session_start();
ob_start();
include_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$tableName = "eid_form";
$tableName1 = "activity_log";
$tableName2 = "log_result_updates";


try {
  //Set sample received date
  if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != "") {
    $sampleReceivedDate = explode(" ", $_POST['sampleReceivedDate']);
    $_POST['sampleReceivedDate'] = $general->dateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
  } else {
    $_POST['sampleReceivedDate'] = NULL;
  }

  if (isset($_POST['sampleTestedDateTime']) && trim($_POST['sampleTestedDateTime']) != "") {
    $sampleTestedDate = explode(" ", $_POST['sampleTestedDateTime']);
    $_POST['sampleTestedDateTime'] = $general->dateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
  } else {
    $_POST['sampleTestedDateTime'] = NULL;
  }
  


  $eidData=array(
    'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedDate'],
    'sample_tested_datetime'=>$_POST['sampleTestedDateTime'],
    'is_sample_rejected'=>$_POST['isSampleRejected'],
    'result'=>$_POST['result'],
    'result_status'=>6,
    'reason_for_sample_rejection'=>$_POST['sampleRejectionReason'],
    'last_modified_by'=>$_SESSION['userId'],
    'last_modified_datetime'=>$general->getDateTime()
  );  

  $db=$db->where('eid_id',$_POST['eidSampleId']);
  $id=$db->update($tableName,$eidData);

  $_SESSION['alertMsg'] = "EID result updated successfully";
  //Add event log
  $eventType = 'update-vl-result-drc';
  $action = ucwords($_SESSION['userName']) . ' updated a result for the EID sample no. ' . $_POST['sampleCode'];
  $resource = 'eid-result-drc';

  $general->activityLog($eventType, $action, $resource);

  // $data = array(
  //   'user_id' => $_SESSION['userId'],
  //   'vl_sample_id' => $_POST['vlSampleId'],
  //   'updated_on' => $general->getDateTime()
  // );
  // $db->insert($tableName2, $data);

  header("location:eid-manual-results.php");
} catch (Exception $exc) {
  error_log($exc->getMessage());
  error_log($exc->getTraceAsString());
}
