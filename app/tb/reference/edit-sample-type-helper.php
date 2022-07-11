<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  


$general = new \Vlsm\Models\General();



$tableName = "r_tb_sample_type";
$sampleId = base64_decode($_POST['sampleId']);

try {
	if (isset($_POST['sampleName']) && trim($_POST['sampleName']) != "") {


		$data = array(
			'sample_name' => $_POST['sampleName'],
			'status' => $_POST['sampleStatus'],
			'updated_datetime' => $general->getDateTime(),
		);

        $db = $db->where('sample_id', $sampleId);
        $db->update($tableName, $data);

		$_SESSION['alertMsg'] = "Sample details updated successfully";
		$general->activityLog('update-sample-type', $_SESSION['userName'] . ' updated new reference sample type' . $_POST['sampleName'], 'reference-tb-sample type');
	}
	header("location:tb-sample-type.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}