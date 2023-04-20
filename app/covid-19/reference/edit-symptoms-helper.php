<?php

use App\Models\General;
use App\Utilities\DateUtils;


if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

  


$general = new General();



$tableName = "r_covid19_symptoms";
$symptomId = base64_decode($_POST['symptomId']);

try {
	if (isset($_POST['symptomsName']) && trim($_POST['symptomsName']) != "") {


		$data = array(
            'symptom_name' => $_POST['symptomsName'],
            'parent_symptom' => $_POST['parentSymptom'],
			'symptom_status' => $_POST['symptomsStatus'],
			'updated_datetime' => DateUtils::getCurrentDateTime(),
		);

        $db = $db->where('symptom_id', $symptomId);
        $db->update($tableName, $data);

		$_SESSION['alertMsg'] = "Symptom details updated successfully";
		$general->activityLog('update-symptoms', $_SESSION['userName'] . ' updated new reference symptoms' . $_POST['symptomsName'], 'reference-covid19-symptoms');
	}
	header("Location:covid19-symptoms.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
