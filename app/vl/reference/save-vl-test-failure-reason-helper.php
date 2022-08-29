<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
$general = new \Vlsm\Models\General();

$tableName = "r_vl_test_failure_reasons";
$primaryKey = "failure_id";
try {
	if (isset($_POST['failureReason']) && trim($_POST['failureReason']) != "") {

		$data = array(
			'failure_reason'    => $_POST['failureReason'],
			'status'        	=> $_POST['status'],
			'updated_datetime'  => $general->getCurrentDateTime()
		);
		if (isset($_POST['failureId']) && $_POST['failureId'] != "") {
			$db = $db->where($primaryKey, base64_decode($_POST['failureId']));
			$lastId = $db->update($tableName, $data);
		} else {
			$data['data_sync'] = 0;
			$db->insert($tableName, $data);
			$lastId = $db->getInsertId();
		}
		if ($lastId > 0) {
			$_SESSION['alertMsg'] = _("VL Test Failure Reason Saved Successfully");
			$general->activityLog('VL Test Failure Reason', $_SESSION['userName'] . ' added new vl test failure reason for ' . $_POST['failureReason'], 'vl-reference');
		}
	}
	header("location:vl-test-failure-reasons.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}