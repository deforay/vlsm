<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  

$general = new \App\Models\General();



$tableName = "r_covid19_test_reasons";

try {
	if (isset($_POST['testReasonName']) && trim($_POST['testReasonName']) != "") {


		$data = array(
            'test_reason_name' => $_POST['testReasonName'],
            'parent_reason' => $_POST['parentReason'],
			'test_reason_status' => $_POST['testReasonStatus'],
			'updated_datetime' => $general->getCurrentDateTime(),
		);

		$db->insert($tableName, $data);
		$lastId = $db->getInsertId();

		$_SESSION['alertMsg'] = _("COVID 19 Test reasons details added successfully");
		$general->activityLog('add-test-reasons', $_SESSION['userName'] . ' added new reference test reasons' . $_POST['testReasonName'], 'reference-covid19-test-reasons');
	}
	header("location:covid19-test-reasons.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
