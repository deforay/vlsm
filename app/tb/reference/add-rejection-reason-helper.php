<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  


$general = new \Vlsm\Models\General();



$tableName = "r_tb_sample_rejection_reasons";

try {
	if (isset($_POST['rejectionReasonName']) && trim($_POST['rejectionReasonName']) != "") {

		$data = array(
			'rejection_reason_name' => $_POST['rejectionReasonName'],
            'rejection_type' => $_POST['rejectionType'],
            'rejection_reason_status' => $_POST['rejectionReasonStatus'],
            'rejection_reason_code' => $_POST['rejectionReasonCode'],
			'updated_datetime' => $general->getDateTime(),
		);

		$db->insert($tableName, $data);
		$lastId = $db->getInsertId();

		$_SESSION['alertMsg'] = _("TB Sample Rejection Reasons details added successfully");
		$general->activityLog('add-Sample Rejection Reasons', $_SESSION['userName'] . ' added new reference Sample Rejection Reasons ' . $_POST['rejectionReasonName'], 'reference-tb-Sample Rejection Reasons');
	}
	header("location:tb-sample-rejection-reasons.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}