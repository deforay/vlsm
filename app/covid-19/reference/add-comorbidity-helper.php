<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  


$general = new \App\Models\General();



$tableName = "r_covid19_comorbidities";

try {
	if (isset($_POST['comorbidityName']) && trim($_POST['comorbidityName']) != "") {


		$data = array(
			'comorbidity_name' => $_POST['comorbidityName'],
			'comorbidity_status' => $_POST['comorbidityStatus'],
			'updated_datetime' => $general->getCurrentDateTime(),
		);

		$db->insert($tableName, $data);
		$lastId = $db->getInsertId();

		$_SESSION['alertMsg'] = _("Comorbidity details added successfully");
		$general->activityLog('add-comorbidity', $_SESSION['userName'] . ' added new reference comorbidity ' . $_POST['comorbidityName'], 'reference-covid19-comorbidity');
	}
	header("location:covid19-comorbidities.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
