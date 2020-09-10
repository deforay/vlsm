<?php

ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . '/models/Covid19.php');
$general = new \Vlsm\Models\General($db);

// echo "<pre>";print_r($_POST);die;

$tableName = "form_covid19";
$tableName1 = "activity_log";
$testTableName = 'covid19_tests';

try {
	//system config
	$systemConfigQuery = "SELECT * FROM system_config";
	$systemConfigResult = $db->query($systemConfigQuery);
	$sarr = array();
	// now we create an associative array so that we can easily create view variables
	for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
		$sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
	}
	$instanceId = '';
	if (isset($_SESSION['instanceId'])) {
		$instanceId = $_SESSION['instanceId'];
	}

	if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != "") {
		$sampleCollectionDate = explode(" ", $_POST['sampleCollectionDate']);
		$_POST['sampleCollectionDate'] = $general->dateFormat($sampleCollectionDate[0]) . " " . $sampleCollectionDate[1];
	} else {
		$_POST['sampleCollectionDate'] = NULL;
	}

	//Set sample received date
	if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != "") {
		$sampleReceivedDate = explode(" ", $_POST['sampleReceivedDate']);
		$_POST['sampleReceivedDate'] = $general->dateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
	} else {
		$_POST['sampleReceivedDate'] = NULL;
	}

	if (isset($_POST['sampleTestedDateTime']) && trim($_POST['sampleTestedDateTime']) != "") {
		$sampleTestedDate = explode(" ", $_POST['sampleTestedDateTime']);
		$_POST['sampleTestedDateTime'] = $general->dateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
	} else {
		$_POST['sampleTestedDateTime'] = NULL;
	}


	if (isset($_POST['arrivalDateTime']) && trim($_POST['arrivalDateTime']) != "") {
		$arrivalDate = explode(" ", $_POST['arrivalDateTime']);
		$_POST['arrivalDateTime'] = $general->dateFormat($arrivalDate[0]) . " " . $arrivalDate[1];
	} else {
		$_POST['arrivalDateTime'] = NULL;
	}


	if ($sarr['user_type'] == 'remoteuser') {
		$sampleCode = 'remote_sample_code';
		$sampleCodeKey = 'remote_sample_code_key';
	} else {
		$sampleCode = 'sample_code';
		$sampleCodeKey = 'sample_code_key';
	}




	if ($sarr['user_type'] == 'remoteuser') {
		$status = 9;
	}

	if (isset($_POST['oldStatus']) && !empty($_POST['oldStatus'])) {
		$status = $_POST['oldStatus'];
	}

	if ($sarr['user_type'] == 'vluser' && $_POST['oldStatus'] == 9) {
		$status = 6;
	}

	if (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] == 'yes') {
		$_POST['result'] = null;
		$status = 4;
	}


	if ($sarr['user_type'] == 'remoteuser' && $_POST['oldStatus'] == 9) {
		$_POST['status'] = 9;
	} else if ($sarr['user_type'] == 'vluser' && $_POST['oldStatus'] == 9) {
		$_POST['status'] = 6;
	}
	if (isset($_POST['status']) && $_POST['status'] == '') {
		$_POST['status']  = $_POST['oldStatus'];
	}


	$covid19Data = array(
		'facility_id'                         => isset($_POST['facilityId']) ? $_POST['facilityId'] : null,
		'test_number'                         => isset($_POST['testNumber']) ? $_POST['testNumber'] : null,
		'province_id'                         => isset($_POST['provinceId']) ? $_POST['provinceId'] : null,
		'lab_id'                              => isset($_POST['labId']) ? $_POST['labId'] : null,
		'implementing_partner'                => isset($_POST['implementingPartner']) ? $_POST['implementingPartner'] : null,
		'funding_source'                      => isset($_POST['fundingSource']) ? $_POST['fundingSource'] : null,
		'patient_id'                          => isset($_POST['patientId']) ? $_POST['patientId'] : null,
		'patient_name'                        => isset($_POST['firstName']) ? $_POST['firstName'] : null,
		'patient_surname'                     => isset($_POST['lastName']) ? $_POST['lastName'] : null,
		'patient_dob'                         => isset($_POST['patientDob']) ? $general->dateFormat($_POST['patientDob']) : null,
		'patient_gender'                      => isset($_POST['patientGender']) ? $_POST['patientGender'] : null,
		'is_patient_pregnant'                 => isset($_POST['isPatientPregnant']) ? $_POST['isPatientPregnant'] : null,
		'patient_age'                         => isset($_POST['patientAge']) ? $_POST['patientAge'] : null,
		'patient_phone_number'                => isset($_POST['patientPhoneNumber']) ? $_POST['patientPhoneNumber'] : null,
		'patient_address'                     => isset($_POST['patientAddress']) ? $_POST['patientAddress'] : null,
		'patient_province'                    => isset($_POST['patientProvince']) ? $_POST['patientProvince'] : null,
		'patient_district'                    => isset($_POST['patientDistrict']) ? $_POST['patientDistrict'] : null,
		'patient_occupation'                  => isset($_POST['patientOccupation']) ? $_POST['patientOccupation'] : null,
		'patient_nationality'                 => isset($_POST['patientNationality']) ? $_POST['patientNationality'] : null,
		'flight_airline'                 	  => isset($_POST['airline']) ? $_POST['airline'] : null,
		'flight_seat_no'                 	  => isset($_POST['seatNo']) ? $_POST['seatNo'] : null,
		'flight_arrival_datetime'             => isset($_POST['arrivalDateTime']) ? $_POST['arrivalDateTime'] : null,
		'flight_airport_of_departure'         => isset($_POST['airportOfDeparture']) ? $_POST['airportOfDeparture'] : null,
		'flight_transit'          			  => isset($_POST['transit']) ? $_POST['transit'] : null,
		'reason_of_visit'          			  => isset($_POST['reasonOfVisit']) ? $_POST['reasonOfVisit'] : null,
		'is_sample_collected'                 => isset($_POST['isSampleCollected']) ? $_POST['isSampleCollected'] : null,
		'reason_for_covid19_test'             => isset($_POST['reasonForCovid19Test']) ? $_POST['reasonForCovid19Test'] : null,
		'specimen_type'                       => isset($_POST['specimenType']) ? $_POST['specimenType'] : null,
		'sample_collection_date'              => isset($_POST['sampleCollectionDate']) ? $_POST['sampleCollectionDate'] : null,
		'is_sample_post_mortem'               => isset($_POST['isSamplePostMortem']) ? $_POST['isSamplePostMortem'] : null,
		'priority_status'                     => isset($_POST['priorityStatus']) ? $_POST['priorityStatus'] : null,
		'number_of_days_sick'                 => isset($_POST['numberOfDaysSick']) ? $_POST['numberOfDaysSick'] : null,
		'date_of_symptom_onset'               => isset($_POST['dateOfSymptomOnset']) ? $general->dateFormat($_POST['dateOfSymptomOnset']) : null,
		'date_of_initial_consultation'        => isset($_POST['dateOfInitialConsultation']) ? $general->dateFormat($_POST['dateOfInitialConsultation']) : null,
		'fever_temp'        				  => isset($_POST['feverTemp']) ? $_POST['feverTemp'] : null,
		'medical_history'        			  => isset($_POST['medicalHistory']) ? $_POST['medicalHistory'] : null,
		'recent_hospitalization'   			  => isset($_POST['recentHospitalization']) ? $_POST['recentHospitalization'] : null,
		'patient_lives_with_children'		  => isset($_POST['patientLivesWithChildren']) ? $_POST['patientLivesWithChildren'] : null,
		'patient_cares_for_children'		  => isset($_POST['patientCaresForChildren']) ? $_POST['patientCaresForChildren'] : null,
		'temperature_measurement_method' 	  => isset($_POST['temperatureMeasurementMethod']) ? $_POST['temperatureMeasurementMethod'] : null,
		'respiratory_rate' 	  				  => isset($_POST['respiratoryRate']) ? $_POST['respiratoryRate'] : null,
		'oxygen_saturation'	  				  => isset($_POST['oxygenSaturation']) ? $_POST['oxygenSaturation'] : null,
		'close_contacts'        			  => isset($_POST['closeContacts']) ? $_POST['closeContacts'] : null,
		'contact_with_confirmed_case'         => isset($_POST['contactWithConfirmedCase']) ? $_POST['contactWithConfirmedCase'] : null,
		'has_recent_travel_history'           => isset($_POST['hasRecentTravelHistory']) ? $_POST['hasRecentTravelHistory'] : null,
		'travel_country_names'                => isset($_POST['countryName']) ? $_POST['countryName'] : null,
		'travel_return_date'                  => isset($_POST['returnDate']) ? $general->dateFormat($_POST['returnDate']) : null,
		'sample_received_at_vl_lab_datetime'  => isset($_POST['sampleReceivedDate']) ? $_POST['sampleReceivedDate'] : null,
		'sample_condition'  				  => isset($_POST['sampleCondition']) ? $_POST['sampleCondition'] : null,
		'is_sample_rejected'                  => isset($_POST['isSampleRejected']) ? $_POST['isSampleRejected'] : null,
		'result'                              => isset($_POST['result']) ? $_POST['result'] : null,
		'other_diseases'                      => (isset($_POST['otherDiseases']) && $_POST['result'] != 'positive') ? $_POST['otherDiseases'] : null,
		'is_result_authorised'                => isset($_POST['isResultAuthorized']) ? $_POST['isResultAuthorized'] : null,
		'authorized_by'                       => isset($_POST['authorizedBy']) ? $_POST['authorizedBy'] : null,
		'authorized_on' 					  => isset($_POST['authorizedOn']) ? $general->dateFormat($_POST['authorizedOn']) : null,
		'rejection_on'	 					  => (isset($_POST['rejectionDate']) && $_POST['isSampleRejected'] == 'yes') ? $general->dateFormat($_POST['rejectionDate']) : null,
		'reason_for_changing'				  => (isset($_POST['reasonForChanging']) && !empty($_POST['reasonForChanging'])) ? $_POST['reasonForChanging'] : null,
		'result_status'                       => $status,
		'data_sync'                           => 0,
		'reason_for_sample_rejection'         => (isset($_POST['sampleRejectionReason']) && $_POST['isSampleRejected'] == 'yes') ? $_POST['sampleRejectionReason'] : null,
		'sample_registered_at_lab'            => $general->getDateTime(),
		'last_modified_by'                    => $_SESSION['userId'],
		'last_modified_datetime'              => $general->getDateTime()
	);


	if ($sarr['user_type'] == 'remoteuser') {
		//$covid19Data['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] : NULL;
	} else {
		if (isset($_POST['sampleCodeCol']) && $_POST['sampleCodeCol'] != '') {
			//$covid19Data['sample_code'] = (isset($_POST['sampleCodeCol']) && $_POST['sampleCodeCol'] != '') ? $_POST['sampleCodeCol'] : NULL;
		} else {
			$covid19Model = new \Vlsm\Models\Covid19($db);

			$sampleCodeKeysJson = $covid19Model->generateCovid19SampleCode($_POST['provinceCode'], $_POST['sampleCollectionDate']);
			$sampleCodeKeys = json_decode($sampleCodeKeysJson, true);
			$covid19Data['sample_code'] = $sampleCodeKeys['sampleCode'];
			$covid19Data['sample_code_key'] = $sampleCodeKeys['sampleCodeKey'];
			$covid19Data['sample_code_format'] = $sampleCodeKeys['sampleCodeFormat'];
		}
	}

	if (isset($_POST['deletedRow']) && trim($_POST['deletedRow']) != '' && ($_POST['isSampleRejected'] == 'no' || $_POST['isSampleRejected'] == '')) {
		$deleteRows = explode(',', $_POST['deletedRow']);
		foreach ($deleteRows as $delete) {
			$db = $db->where('test_id', base64_decode($delete));
			$db->delete($testTableName);
		}
	}

	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$sid = $db->delete("covid19_patient_symptoms");
	if (isset($_POST['symptomDetected']) && !empty($_POST['symptomDetected'])) {

		for ($i = 0; $i < count($_POST['symptomDetected']); $i++) {
			$symptomData = array();
			$symptomData["covid19_id"] = $_POST['covid19SampleId'];
			$symptomData["symptom_id"] = $_POST['symptomId'][$i];
			$symptomData["symptom_detected"] = $_POST['symptomDetected'][$i];
			$db->insert("covid19_patient_symptoms", $symptomData);
		}
	}

	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$db->delete("covid19_reasons_for_testing");
	if (isset($_POST['responseDetected']) && !empty($_POST['responseDetected'])) {

		for ($i = 0; $i < count($_POST['responseDetected']); $i++) {
			$symptomData = array();
			$symptomData["covid19_id"] = $_POST['covid19SampleId'];
			$symptomData["reasons_id"] = $_POST['responseId'][$i];
			$symptomData["reasons_detected"] = $_POST['responseDetected'][$i];
			$db->insert("covid19_reasons_for_testing", $symptomData);
		}
	}

	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$pid = $db->delete("covid19_patient_comorbidities");
	if (isset($_POST['comorbidityDetected']) && !empty($_POST['comorbidityDetected'])) {

		for ($i = 0; $i < count($_POST['comorbidityDetected']); $i++) {
			$comorbidityData = array();
			$comorbidityData["covid19_id"] = $_POST['covid19SampleId'];
			$comorbidityData["comorbidity_id"] = $_POST['comorbidityId'][$i];
			$comorbidityData["comorbidity_detected"] = $_POST['comorbidityDetected'][$i];
			$db->insert("covid19_patient_comorbidities", $comorbidityData);
		}
	}


	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '' && ($_POST['isSampleRejected'] == 'no' || $_POST['isSampleRejected'] == '')) {
		if (isset($_POST['testName']) && count($_POST['testName']) > 0) {
			foreach ($_POST['testName'] as $testKey => $testerName) {
				if (isset($_POST['testDate'][$testKey]) && trim($_POST['testDate'][$testKey]) != "") {
					$testedDateTime = explode(" ", $_POST['testDate'][$testKey]);
					$_POST['testDate'][$testKey] = $general->dateFormat($testedDateTime[0]) . " " . $testedDateTime[1];
				} else {
					$_POST['testDate'][$testKey] = NULL;
				}
				$covid19TestData = array(
					'covid19_id'			=> $_POST['covid19SampleId'],
					'test_name'				=> $_POST['testName'][$testKey],
					'test_name'				=> ($_POST['testName'][$testKey] == 'other')?$_POST['testNameOther'][$testKey]:$_POST['testName'][$testKey],
					'facility_id'           => isset($_POST['labId']) ? $_POST['labId'] : null,
					'sample_tested_datetime' => $_POST['testDate'][$testKey],
					'result'				=> $_POST['testResult'][$testKey],
				);
				if (isset($_POST['testId'][$testKey]) && $_POST['testId'][$testKey] != '') {
					$db = $db->where('test_id', base64_decode($_POST['testId'][$testKey]));
					$db->update($testTableName, $covid19TestData);
				} else {
					$db->insert($testTableName, $covid19TestData);
				}
				$covid19Data['sample_tested_datetime'] = date('Y-m-d H:i:s', strtotime($_POST['testDate'][$testKey]));
			}
		}
	} else {
		$db = $db->where('covid19_id', $_POST['covid19SampleId']);
		$db->delete($testTableName);
		$covid19Data['sample_tested_datetime'] = null;
	}
	// echo "<pre>";print_r($covid19Data);die;
	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '') {
		$db = $db->where('covid19_id', $_POST['covid19SampleId']);
		$id = $db->update($tableName, $covid19Data);
	}

	if ($id > 0 || $sid > 0 || $pid > 0) {
		$_SESSION['alertMsg'] = "Covid-19 request updated successfully";
		//Add event log
		$eventType = 'update-covid-19-request';
		$action = ucwords($_SESSION['userName']) . ' updated Covid-19 request data with the sample id ' . $_POST['covid19SampleId'];
		$resource = 'covid-19-edit-request';

		$general->activityLog($eventType, $action, $resource);

		// $data=array(
		// 'event_type'=>$eventType,
		// 'action'=>$action,
		// 'resource'=>$resource,
		// 'date_time'=>$general->getDateTime()
		// );
		// $db->insert($tableName1,$data);

	} else {
		$_SESSION['alertMsg'] = "Please try again later";
	}
	header("location:/covid-19/requests/covid-19-requests.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
