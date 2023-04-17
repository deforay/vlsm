<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  


$general = new \App\Models\General();



$tableName = "r_covid19_symptoms";

try {
	if (isset($_POST['symptomsName']) && trim($_POST['symptomsName']) != "") {

		$data = array(
            'symptom_name' => $_POST['symptomsName'],
            'parent_symptom' => $_POST['parentSymptom'],
			'symptom_status' => $_POST['symptomsStatus'],
			'updated_datetime' => \App\Utilities\DateUtils::getCurrentDateTime(),
		);

		$db->insert($tableName, $data);
		$lastId = $db->getInsertId();

		$_SESSION['alertMsg'] = _("Symptom details added successfully");
		$general->activityLog('add-symptoms', $_SESSION['userName'] . ' added new reference symptom' . $_POST['symptomsName'], 'reference-covid19-symptoms');
	}
	header("location:covid19-symptoms.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
