<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;


if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$tableName = "r_hepatitis_sample_rejection_reasons";
$primaryKey = "rejection_reason_id";

try {
	if (isset($_POST['rejectionReasonName']) && trim((string) $_POST['rejectionReasonName']) != "") {

		$data = array(
			'rejection_reason_name' 	=> $_POST['rejectionReasonName'],
			'rejection_type' 			=> $_POST['rejectionType'],
			'rejection_reason_status'	=> $_POST['rejectionReasonStatus'],
			'rejection_reason_code' 	=> $_POST['rejectionReasonCode'],
			'updated_datetime' 			=> DateUtility::getCurrentDateTime()
		);

		if (isset($_POST['rejectionReasonId']) && $_POST['rejectionReasonId'] != "") {
			$db = $db->where($primaryKey, base64_decode((string) $_POST['rejectionReasonId']));
			$lastId = $db->update($tableName, $data);
		} else {
			$data['data_sync'] = 0;
			$db->insert($tableName, $data);
			$lastId = $db->getInsertId();
		}
		if ($lastId > 0) {
			$_SESSION['alertMsg'] = _translate("Hepatitis Sample Rejection Reasons details added successfully");
			$general->activityLog('Hepatitis Sample Rejection Reasons', $_SESSION['userName'] . ' added new reference Sample Rejection Reasons for  ' . $_POST['rejectionReasonName'], 'hepatitis-reference');
		}
	}
	header("Location:hepatitis-sample-rejection-reasons.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
