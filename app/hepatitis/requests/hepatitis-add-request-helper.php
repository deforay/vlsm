<?php

use App\Utilities\DateUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Services\PatientsService;
use App\Registries\ContainerRegistry;


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var PatientsService $patientsService */
$patientsService = ContainerRegistry::get(PatientsService::class);

$tableName = "form_hepatitis";
$tableName1 = "activity_log";

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');

$_POST = _sanitizeInput($request->getParsedBody(), nullifyEmptyStrings: true);

try {
	$instanceId = '';
	if (isset($_SESSION['instanceId'])) {
		$instanceId = $_SESSION['instanceId'];
	}

	if (isset($_POST['sampleCollectionDate']) && trim((string) $_POST['sampleCollectionDate']) != "") {
		$sampleCollectionDate = explode(" ", (string) $_POST['sampleCollectionDate']);
		$_POST['sampleCollectionDate'] = DateUtility::isoDateFormat($sampleCollectionDate[0]) . " " . $sampleCollectionDate[1];
	} else {
		$_POST['sampleCollectionDate'] = null;
	}


	//Set sample received date
	if (isset($_POST['sampleReceivedDate']) && trim((string) $_POST['sampleReceivedDate']) != "") {
		$sampleReceivedDate = explode(" ", (string) $_POST['sampleReceivedDate']);
		$_POST['sampleReceivedDate'] = DateUtility::isoDateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
	} else {
		$_POST['sampleReceivedDate'] = null;
	}

	if (isset($_POST['sampleTestedDateTime']) && trim((string) $_POST['sampleTestedDateTime']) != "") {
		$sampleTestedDate = explode(" ", (string) $_POST['sampleTestedDateTime']);
		$_POST['sampleTestedDateTime'] = DateUtility::isoDateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
	} else {
		$_POST['sampleTestedDateTime'] = null;
	}

	if (!isset($_POST['sampleCode']) || trim((string) $_POST['sampleCode']) == '') {
		$_POST['sampleCode'] = null;
	}

	if ($general->isSTSInstance()) {
		$sampleCode = 'remote_sample_code';
		$sampleCodeKey = 'remote_sample_code_key';
	} else {
		$sampleCode = 'sample_code';
		$sampleCodeKey = 'sample_code_key';
	}

	$status = SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB;
	if ($general->isSTSInstance() && $_SESSION['accessType'] == 'collection-site') {
		$status = SAMPLE_STATUS\RECEIVED_AT_CLINIC;
	}

	$resultSentToSource = 'pending';

	if (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] == 'yes') {
		$_POST['hcvCount'] = $_POST['hbvCount'] = null;
		$resultSentToSource = 'pending';
	} elseif (empty($_POST['hcvCount']) && empty($_POST['hbvCount'])) {
		$resultSentToSource = null;
	}

	$_POST['reviewedOn'] = DateUtility::isoDateFormat($_POST['reviewedOn'] ?? '', true);
	$_POST['approvedOn'] = DateUtility::isoDateFormat($_POST['approvedOn'] ?? '', true);

	$testingPlatform = null;
	$instrumentId = null;
	if (isset($_POST['hepatitisPlatform']) && trim((string) $_POST['hepatitisPlatform']) != '') {
		$platForm = explode("##", (string) $_POST['hepatitisPlatform']);
		$testingPlatform = $platForm[0];
		$instrumentId = $platForm[1];
	}

	//Update patient Information in Patients Table
	//$patientsService->savePatient($_POST, 'form_hepatitis');

	//$systemGeneratedCode = $patientsService->getSystemPatientId($_POST['patientId'], $_POST['patientGender'], DateUtility::isoDateFormat($_POST['dob'] ?? ''));


	$hepatitisData = array(
		'vlsm_instance_id' => $instanceId,
		'vlsm_country_id' => $_POST['formId'],
		'external_sample_code' => $_POST['externalSampleCode'] ?? null,
		'hepatitis_test_type' => $_POST['hepatitisTestType'] ?? 'hcv',
		'facility_id' => $_POST['facilityId'] ?? null,
		//'system_patient_code' => $systemGeneratedCode,
		'test_number' => $_POST['testNumber'] ?? null,
		'province_id' => $_POST['provinceId'] ?? null,
		'lab_id' => $_POST['labId'] ?? null,
		'implementing_partner' => $_POST['implementingPartner'] ?? null,
		'funding_source' => $_POST['fundingSource'] ?? null,
		'patient_id' => $_POST['patientId'] ?? null,
		'patient_name' => $_POST['firstName'] ?? null,
		'patient_surname' => $_POST['lastName'] ?? null,
		'patient_dob' => isset($_POST['dob']) ? DateUtility::isoDateFormat($_POST['dob']) : null,
		'patient_gender' => $_POST['patientGender'] ?? null,
		'patient_age' => $_POST['patientAge'] ?? null,
		'patient_marital_status' => $_POST['maritalStatus'] ?? null,
		'patient_insurance' => $_POST['insurance'] ?? null,
		'patient_phone_number' => $_POST['patientPhoneNumber'] ?? null,
		'patient_address' => $_POST['patientAddress'] ?? null,
		'patient_province' => $_POST['patientProvince'] ?? null,
		'patient_district' => $_POST['patientDistrict'] ?? null,
		'patient_city' => $_POST['patientCity'] ?? null,
		'patient_occupation' => $_POST['patientOccupation'] ?? null,
		'patient_nationality' => $_POST['patientNationality'] ?? null,
		'hbv_vaccination' => $_POST['HbvVaccination'] ?? null,
		'is_sample_collected' => $_POST['isSampleCollected'] ?? null,
		'type_of_test_requested' => $_POST['testTypeRequested'] ?? null,
		'reason_for_vl_test' => $_POST['reasonVlTest'] ?? null,
		'specimen_type' => $_POST['specimenType'] ?? null,
		'sample_collection_date' => $_POST['sampleCollectionDate'] ?? null,
		'sample_received_at_lab_datetime' => $_POST['sampleReceivedDate'] ?? null,
		'sample_tested_datetime' => $_POST['sampleTestedDateTime'] ?? null,
		'vl_testing_site' => $_POST['vlTestingSite'] ?? null,
		'sample_condition' => $_POST['sampleCondition'] ?? ($_POST['specimenQuality'] ?? null),
		'is_sample_rejected' => ($_POST['isSampleRejected'] ?? null),
		'hbsag_result' => $_POST['HBsAg'] ?? null,
		'anti_hcv_result' => $_POST['antiHcv'] ?? null,
		'result' => $_POST['result'] ?? null,
		'hcv_vl_count' => $_POST['hcvCount'] ?? null,
		'hbv_vl_count' => $_POST['hbvCount'] ?? null,
		'hepatitis_test_platform' => $testingPlatform ?? null,
		'instrument_id' => $instrumentId ?? null,
		'import_machine_name' => $_POST['machineName'] ?? null,
		'is_result_authorised' => $_POST['isResultAuthorized'] ?? null,
		'result_reviewed_by' => (isset($_POST['reviewedBy']) && $_POST['reviewedBy'] != "") ? $_POST['reviewedBy'] : null,
		'result_reviewed_datetime' => (isset($_POST['reviewedOn']) && $_POST['reviewedOn'] != "") ? $_POST['reviewedOn'] : null,
		'authorized_by' => $_POST['authorizedBy'] ?? null,
		'social_category' => $_POST['socialCategory'] ?? null,
		'authorized_on' => isset($_POST['authorizedOn']) ? DateUtility::isoDateFormat($_POST['authorizedOn']) : null,
		'rejection_on' => (isset($_POST['rejectionDate']) && $_POST['isSampleRejected'] == 'yes') ? DateUtility::isoDateFormat($_POST['rejectionDate']) : null,
		'result_status' => $status,
		'result_sent_to_source' => $resultSentToSource,
		'data_sync' => 0,
		'reason_for_sample_rejection' => (isset($_POST['sampleRejectionReason']) && $_POST['isSampleRejected'] == 'yes') ? $_POST['sampleRejectionReason'] : null,
		'request_created_by' => $_SESSION['userId'],
		'request_created_datetime' => DateUtility::getCurrentDateTime(),
		'sample_registered_at_lab' => DateUtility::getCurrentDateTime(),
		'last_modified_by' => $_SESSION['userId'],
		'last_modified_datetime' => DateUtility::getCurrentDateTime(),
		'result_modified'  => 'no',
		'lab_technician' => (isset($_POST['labTechnician']) && $_POST['labTechnician'] != '') ? $_POST['labTechnician'] : $_SESSION['userId']
	);

	if ($general->isLISInstance() || $general->isStandaloneInstance()) {
		$hepatitisData['source_of_request'] = 'vlsm';
	} elseif ($general->isSTSInstance()) {
		$hepatitisData['source_of_request'] = 'vlsts';
	}

	if (isset($_POST['hepatitisSampleId']) && $_POST['hepatitisSampleId'] != 0) {

		$db->where('hepatitis_id', $_POST['hepatitisSampleId']);
		$db->delete("hepatitis_patient_comorbidities");
		if (!empty($_POST['comorbidity'])) {

			foreach ($_POST['comorbidity'] as $id => $value) {
				$comorbidityData = [];
				$comorbidityData["hepatitis_id"] = $_POST['hepatitisSampleId'];
				$comorbidityData["comorbidity_id"] = $id;
				$comorbidityData["comorbidity_detected"] = (isset($value) && $value == 'other') ? $_POST['comorbidityOther'][$id] : $value;
				$db->insert("hepatitis_patient_comorbidities", $comorbidityData);
			}
		}
		// For Save Risk factors
		$db->where('hepatitis_id', $_POST['hepatitisSampleId']);
		$db->delete("hepatitis_risk_factors");
		if (!empty($_POST['riskFactors'])) {

			foreach ($_POST['riskFactors'] as $id => $value) {
				$riskFactorsData = [];
				$riskFactorsData["hepatitis_id"] = $_POST['hepatitisSampleId'];
				$riskFactorsData["riskfactors_id"] = $id;
				$riskFactorsData["riskfactors_detected"] = (isset($value) && $value == 'other') ? $_POST['riskFactorsOther'][$id] : $value;
				$db->insert("hepatitis_risk_factors", $riskFactorsData);
			}
		}

		$hepatitisData['is_encrypted'] = 'no';
		if (isset($_POST['encryptPII']) && $_POST['encryptPII'] == 'yes') {
			$key = (string) $general->getGlobalConfig('key');
			$encryptedPatientId = $general->crypto('encrypt', $hepatitisData['patient_id'], $key);
			$encryptedPatientName = $general->crypto('encrypt', $hepatitisData['patient_name'], $key);
			$encryptedPatientSurName = $general->crypto('encrypt', $hepatitisData['patient_surname'], $key);

			$hepatitisData['patient_id'] = $encryptedPatientId;
			$hepatitisData['patient_name'] = $encryptedPatientName;
			$hepatitisData['patient_surname'] = $encryptedPatientSurName;
			$hepatitisData['is_encrypted'] = 'yes';
		}


		$id = false;
		if (isset($_POST['hepatitisSampleId']) && $_POST['hepatitisSampleId'] != '') {
			$db->where('hepatitis_id', $_POST['hepatitisSampleId']);
			$id = $db->update($tableName, $hepatitisData);
		}
	} else {
		$id = false;
	}



	if ($id === true) {
		$_SESSION['alertMsg'] = _translate("Hepatitis test request added successfully");
		//Add event log
		$eventType = 'hepatitis-add-request';
		$action = $_SESSION['userName'] . ' added a new hepatitis request with the Sample ID/Code  ' . $_POST['hepatitisSampleId'];
		$resource = 'hepatitis-add-request';

		$general->activityLog($eventType, $action, $resource);
	} else {
		$_SESSION['alertMsg'] = _translate("Unable to add this hepatitis sample. Please try again later");
	}

	if (isset($_POST['saveNext']) && $_POST['saveNext'] == 'next') {
		header("Location:/hepatitis/requests/hepatitis-add-request.php");
	} else {
		header("Location:/hepatitis/requests/hepatitis-requests.php");
	}
} catch (Exception $e) {
	LoggerUtility::log("error", $e->getMessage(), [
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'trace' => $e->getTraceAsString(),
	]);
}
