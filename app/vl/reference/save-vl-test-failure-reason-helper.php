<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;
use App\Services\SecurityService;

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
	SecurityService::redirect("/vl/reference/vl-test-failure-reasons.php");
} catch (Throwable $e) {
	LoggerUtility::log("error", $e->getMessage(), [
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'trace' => $e->getTraceAsString(),
	]);
}
