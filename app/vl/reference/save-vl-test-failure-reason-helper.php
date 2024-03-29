<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;


if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$tableName = "r_vl_test_failure_reasons";
$primaryKey = "failure_id";
try {
	if (isset($_POST['failureReason']) && trim((string) $_POST['failureReason']) != "") {

		$data = array(
			'failure_reason'    => $_POST['failureReason'],
			'status'        	=> $_POST['status'],
			'updated_datetime'  => DateUtility::getCurrentDateTime()
		);
		if (isset($_POST['failureId']) && $_POST['failureId'] != "") {
			$db->where($primaryKey, base64_decode((string) $_POST['failureId']));
			$lastId = $db->update($tableName, $data);
		} else {
			$data['data_sync'] = 0;
			$db->insert($tableName, $data);
			$lastId = $db->getInsertId();
		}
		if ($lastId > 0) {
			$_SESSION['alertMsg'] = _translate("VL Test Failure Reason Saved Successfully");
			$general->activityLog('VL Test Failure Reason', $_SESSION['userName'] . ' added new vl test failure reason for ' . $_POST['failureReason'], 'vl-reference');
		}
	}
	header("Location:vl-test-failure-reasons.php");
} catch (Exception $e) {
	LoggerUtility::log("error", $e->getMessage(), [
		'file' => __FILE__,
		'line' => __LINE__,
		'trace' => $e->getTraceAsString(),
	]);
}
