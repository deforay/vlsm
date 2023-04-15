<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  


$general = new \App\Models\General();



$tableName = "r_covid19_comorbidities";
$comorbidityId = base64_decode($_POST['comorbidityId']);

try {
	if (isset($_POST['comorbidityName']) && trim($_POST['comorbidityName']) != "") {


		$data = array(
			'comorbidity_name' => $_POST['comorbidityName'],
			'comorbidity_status' => $_POST['comorbidityStatus'],
			'updated_datetime' => $general->getCurrentDateTime(),
		);

        $db = $db->where('comorbidity_id', $comorbidityId);
        $db->update($tableName, $data);

		$_SESSION['alertMsg'] = "Comorbidity details updated successfully";
		$general->activityLog('update-comorbidity', $_SESSION['userName'] . ' updated new reference comorbidity ' . $_POST['comorbidityName'], 'reference-covid19-comorbidity');
	}
	header("location:covid19-comorbidities.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
