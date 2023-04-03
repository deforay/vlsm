<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
ob_start();



$general = new \Vlsm\Models\General();
$tableName = "form_eid";
$tableName1 = "activity_log";
$tableName2 = "log_result_updates";


try {
  //Set sample received date
  if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != "") {
    $sampleReceivedDate = explode(" ", $_POST['sampleReceivedDate']);
    $_POST['sampleReceivedDate'] = $general->isoDateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
  } else {
    $_POST['sampleReceivedDate'] = null;
  }

  if (isset($_POST['sampleTestedDateTime']) && trim($_POST['sampleTestedDateTime']) != "") {
    $sampleTestedDate = explode(" ", $_POST['sampleTestedDateTime']);
    $_POST['sampleTestedDateTime'] = $general->isoDateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
  } else {
    $_POST['sampleTestedDateTime'] = null;
  }

  if (isset($_POST['approvedOnDateTime']) && trim($_POST['approvedOnDateTime']) != "") {
    $approvedOnDateTime = explode(" ", $_POST['approvedOnDateTime']);
    $_POST['approvedOnDateTime'] = $general->isoDateFormat($approvedOnDateTime[0]) . " " . $approvedOnDateTime[1];
  } else {
    $_POST['approvedOnDateTime'] = null;
  }

  if (isset($_POST['reviewedOn']) && trim($_POST['reviewedOn']) != "") {
    $reviewedOn = explode(" ", $_POST['reviewedOn']);
    $_POST['reviewedOn'] = $general->isoDateFormat($reviewedOn[0]) . " " . $reviewedOn[1];
  } else {
    $_POST['reviewedOn'] = null;
  }
  if (isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn']) != "") {
    $resultDispatchedOn = explode(" ", $_POST['resultDispatchedOn']);
    $_POST['resultDispatchedOn'] = $general->isoDateFormat($resultDispatchedOn[0]) . " " . $resultDispatchedOn[1];
  } else {
    $_POST['resultDispatchedOn'] = null;
  }

  $eidData = array(
    'sample_received_at_vl_lab_datetime' => $_POST['sampleReceivedDate'],
    'eid_test_platform'                 => isset($_POST['eidPlatform']) ? $_POST['eidPlatform'] : null,
    'import_machine_name'               => isset($_POST['machineName']) ? $_POST['machineName'] : null,
    'sample_tested_datetime'            => $_POST['sampleTestedDateTime'],
    'is_sample_rejected'                => isset($_POST['isSampleRejected']) ? $_POST['isSampleRejected'] : null,
    'lab_id'                            => isset($_POST['labId']) ? $_POST['labId'] : null,
    'result'                            => isset($_POST['result']) ? $_POST['result'] : null,
    'tested_by'                         => (isset($_POST['testedBy']) && $_POST['testedBy'] != '') ? $_POST['testedBy'] :  null,
    'lab_tech_comments'                 => (isset($_POST['labTechCmt']) && $_POST['labTechCmt'] != '') ? $_POST['labTechCmt'] :  null,
    'result_approved_by'                => (isset($_POST['approvedBy']) && $_POST['approvedBy'] != '') ? $_POST['approvedBy'] :  null,
    'result_approved_datetime'          => (isset($_POST['approvedBy']) && $_POST['approvedBy'] != '') ? $_POST['approvedOnDateTime'] :  null,
    'revised_by'                        => (isset($_POST['revised']) && $_POST['revised'] == "yes") ? $_SESSION['userId'] : null,
    'revised_on'                        => (isset($_POST['revised']) && $_POST['revised'] == "yes") ? $general->getCurrentDateTime() : null,
    'result_reviewed_by'                => (isset($_POST['reviewedBy']) && $_POST['reviewedBy'] != "") ? $_POST['reviewedBy'] : null,
    'result_reviewed_datetime'          => (isset($_POST['reviewedOn']) && $_POST['reviewedOn'] != "") ? $_POST['reviewedOn'] : null,
    'result_dispatched_datetime'        => (isset($_POST['resultDispatchedOn']) && $_POST['resultDispatchedOn'] != "") ? $_POST['resultDispatchedOn'] : null,
    'reason_for_changing'               => (!empty($_POST['reasonForChanging']) && !empty($_POST['reasonForChanging'])) ? $_POST['reasonForChanging'] : null,
    'result_status'                     => 8,
    'data_sync'                         => 0,
    'reason_for_sample_rejection'       => isset($_POST['sampleRejectionReason']) ? $_POST['sampleRejectionReason'] : null,
    'rejection_on'                      => isset($_POST['rejectionDate']) ? $general->isoDateFormat($_POST['rejectionDate']) : null,
    'last_modified_by'                  => $_SESSION['userId'],
    'result_printed_datetime'           => null,
    'last_modified_datetime'            => $general->getCurrentDateTime()
  );


  if (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] == 'yes') {
    $eidData['result'] = null;
    $eidData['result_status'] = 4;
  }

  //var_dump($eidData);die;

  $db = $db->where('eid_id', $_POST['eidSampleId']);
  $id = $db->update($tableName, $eidData);
  error_log($db->getLastError());

  $_SESSION['alertMsg'] = _("EID result updated successfully");
  //Add event log
  $eventType = 'update-vl-result-drc';
  $action = $_SESSION['userName'] . ' updated a result for the EID sample no. ' . $_POST['sampleCode'];
  $resource = 'eid-result-drc';

  $general->activityLog($eventType, $action, $resource);

  $data = array(
    'user_id' => $_SESSION['userId'],
    'vl_sample_id' => $_POST['eidSampleId'],
    'test_type' => 'eid',
    'updated_on' => $general->getCurrentDateTime()
  );
  $db->insert($tableName2, $data);

  header("location:eid-manual-results.php");
} catch (Exception $exc) {
  error_log($exc->getMessage());
  error_log($exc->getTraceAsString());
}
