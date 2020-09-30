<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../startup.php';


$general = new \Vlsm\Models\General($db);

// echo "<pre>";
// var_dump($_POST['symptomId']);
// var_dump($_POST);
// die;

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


	if (!isset($_POST['sampleCode']) || trim($_POST['sampleCode']) == '') {
		$_POST['sampleCode'] = NULL;
	}

	if ($sarr['user_type'] == 'remoteuser') {
		$sampleCode = 'remote_sample_code';
		$sampleCodeKey = 'remote_sample_code_key';
	} else {
		$sampleCode = 'sample_code';
		$sampleCodeKey = 'sample_code_key';
	}

	$status = 6;
	if ($sarr['user_type'] == 'remoteuser') {
		$status = 9;
	}


	if (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] == 'yes') {
		$_POST['result'] = null;
		$status = 4;
	}



	$covid19Data = array(
		'vlsm_instance_id'                    => $instanceId,
		'vlsm_country_id'                     => $_POST['formId'],
		'external_sample_code'                => isset($_POST['externalSampleCode']) ? $_POST['externalSampleCode'] : null,
		'facility_id'                         => isset($_POST['facilityId']) ? $_POST['facilityId'] : null,
		'test_number'                         => isset($_POST['testNumber']) ? $_POST['testNumber'] : null,
		'province_id'                         => isset($_POST['provinceId']) ? $_POST['provinceId'] : null,
		'lab_id'                              => isset($_POST['labId']) ? $_POST['labId'] : null,
		'testing_point'                       => isset($_POST['testingPoint']) ? $_POST['testingPoint'] : null,
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
		'patient_city'                    	  => isset($_POST['patientCity']) ? $_POST['patientCity'] : null,
		'patient_occupation'                  => isset($_POST['patientOccupation']) ? $_POST['patientOccupation'] : null,
		'does_patient_smoke'                  => isset($_POST['doesPatientSmoke']) ? $_POST['doesPatientSmoke'] : null,
		'patient_nationality'                 => isset($_POST['patientNationality']) ? $_POST['patientNationality'] : null,
		'patient_passport_number'             => isset($_POST['patientPassportNumber']) ? $_POST['patientPassportNumber'] : null,
		'flight_airline'                 	  => isset($_POST['airline']) ? $_POST['airline'] : null,
		'flight_seat_no'                 	  => isset($_POST['seatNo']) ? $_POST['seatNo'] : null,
		'flight_arrival_datetime'             => isset($_POST['arrivalDateTime']) ? $_POST['arrivalDateTime'] : null,
		'flight_airport_of_departure'         => isset($_POST['airportOfDeparture']) ? $_POST['airportOfDeparture'] : null,
		'flight_transit'          			  => isset($_POST['transit']) ? $_POST['transit'] : null,
		'reason_of_visit'          			  => isset($_POST['reasonOfVisit']) ? $_POST['reasonOfVisit'] : null,
		'is_sample_collected'                 => isset($_POST['isSampleCollected']) ? $_POST['isSampleCollected'] : null,
		'reason_for_covid19_test'             => isset($_POST['reasonForCovid19Test']) ? $_POST['reasonForCovid19Test'] : null,
		'type_of_test_requested'              => isset($_POST['testTypeRequested']) ? $_POST['testTypeRequested'] : null,
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
		'sample_condition'  				  => isset($_POST['sampleCondition']) ? $_POST['sampleCondition'] : (isset($_POST['specimenQuality']) ? $_POST['specimenQuality'] : null),
		'lab_technician' 					  => (isset($_POST['labTechnician']) && $_POST['labTechnician'] != '') ? $_POST['labTechnician'] :  null,
		'is_sample_rejected'                  => isset($_POST['isSampleRejected']) ? $_POST['isSampleRejected'] : null,
		'result'                              => isset($_POST['result']) ? $_POST['result'] : null,
		'other_diseases'                      => (isset($_POST['otherDiseases']) && $_POST['result'] != 'positive') ? $_POST['otherDiseases'] : null,
		'is_result_authorised'                => isset($_POST['isResultAuthorized']) ? $_POST['isResultAuthorized'] : null,
		'authorized_by'                       => isset($_POST['authorizedBy']) ? $_POST['authorizedBy'] : null,
		'authorized_on' 					  => isset($_POST['authorizedOn']) ? $general->dateFormat($_POST['authorizedOn']) : null,
		'rejection_on'	 					  => (isset($_POST['rejectionDate']) && $_POST['isSampleRejected'] == 'yes') ? $general->dateFormat($_POST['rejectionDate']) : null,
		'result_status'                       => $status,
		'data_sync'                           => 0,
		'reason_for_sample_rejection'         => (isset($_POST['sampleRejectionReason']) && $_POST['isSampleRejected'] == 'yes') ? $_POST['sampleRejectionReason'] : null,
		'request_created_by'                  => $_SESSION['userId'],
		'request_created_datetime'            => $general->getDateTime(),
		'sample_registered_at_lab'            => $general->getDateTime(),
		'last_modified_by'                    => $_SESSION['userId'],
		'last_modified_datetime'              => $general->getDateTime()
	);
	// echo "<pre>";
	// print_r($covid19Data);die;

	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$db->delete("covid19_patient_symptoms");
	if (isset($_POST['symptomDetected']) && !empty($_POST['symptomDetected']) || (isset($_POST['symptom']) && !empty($_POST['symptom']))) {
		for ($i = 0; $i < count($_POST['symptomDetected']); $i++) {
			$symptomData = array();
			$symptomData["covid19_id"] = $_POST['covid19SampleId'];
			$symptomData["symptom_id"] = $_POST['symptomId'][$i];
			$symptomData["symptom_detected"] = $_POST['symptomDetected'][$i];
			$symptomData["symptom_details"] 	= (isset($_POST['symptomDetails'][$_POST['symptomId'][$i]]) && count($_POST['symptomDetails'][$_POST['symptomId'][$i]]) > 0) ? json_encode($_POST['symptomDetails'][$_POST['symptomId'][$i]]) : null;
			//var_dump($symptomData);
			$db->insert("covid19_patient_symptoms", $symptomData);
		}
	}

	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$db->delete("covid19_reasons_for_testing");
	if (!empty($_POST['reasonDetails'])) {
		$reasonData = array();
		$reasonData["covid19_id"] 		= $_POST['covid19SampleId'];
		$reasonData["reasons_id"] 		= $_POST['reasonForCovid19Test'];
		$reasonData["reasons_detected"]	= "yes";
		$reasonData["reason_details"] 	= json_encode($_POST['reasonDetails']);
		//var_dump($reasonData);
		$db->insert("covid19_reasons_for_testing", $reasonData);
	}
//die;
	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$db->delete("covid19_patient_comorbidities");
	if (isset($_POST['comorbidityDetected']) && !empty($_POST['comorbidityDetected'])) {

		for ($i = 0; $i < count($_POST['comorbidityDetected']); $i++) {
			$comorbidityData = array();
			$comorbidityData["covid19_id"] = $_POST['covid19SampleId'];
			$comorbidityData["comorbidity_id"] = $_POST['comorbidityId'][$i];
			$comorbidityData["comorbidity_detected"] = $_POST['comorbidityDetected'][$i];
			$db->insert("covid19_patient_comorbidities", $comorbidityData);
		}
	}

	// echo "<pre>";print_r($_POST['testName']);die;
	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '' && ($_POST['isSampleRejected'] == 'no' || $_POST['isSampleRejected'] == '')) {
		if (isset($_POST['testName']) && count($_POST['testName']) > 0) {
			foreach ($_POST['testName'] as $testKey => $testKitName) {
				if (isset($testKitName) && !empty($testKitName)) {
					if (isset($_POST['testDate'][$testKey]) && trim($_POST['testDate'][$testKey]) != "") {
						$testedDateTime = explode(" ", $_POST['testDate'][$testKey]);
						$_POST['testDate'][$testKey] = $general->dateFormat($testedDateTime[0]) . " " . $testedDateTime[1];
					} else {
						$_POST['testDate'][$testKey] = NULL;
					}
					$covid19TestData = array(
						'covid19_id'			=> $_POST['covid19SampleId'],
						'test_name'				=> ($testKitName == 'other') ? $_POST['testNameOther'][$testKey] : $testKitName,
						'facility_id'           => isset($_POST['labId']) ? $_POST['labId'] : null,
						'sample_tested_datetime' => date('Y-m-d H:i:s', strtotime($_POST['testDate'][$testKey])),
						'testing_platform'      => isset($_POST['testingPlatform'][$testKey]) ? $_POST['testingPlatform'][$testKey] : null,
						'result'				=> $_POST['testResult'][$testKey],
					);
					$db->insert($testTableName, $covid19TestData);
					$covid19Data['sample_tested_datetime'] = date('Y-m-d H:i:s', strtotime($_POST['testDate'][$testKey]));
				}
			}
		}
	} else {
		$db = $db->where('covid19_id', $_POST['covid19SampleId']);
		$db->delete($testTableName);
		$covid19Data['sample_tested_datetime'] = null;
	}
	$id = 0;
	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '') {
		// echo "<pre>"; print_r($covid19Data);die;
		$db = $db->where('covid19_id', $_POST['covid19SampleId']);
		$id = $db->update($tableName, $covid19Data);
	}

	if ($id > 0) {
		$_SESSION['alertMsg'] = "Covid-19 test request added successfully";
		//Add event log
		$eventType = 'covid-19-add-request';
		$action = ucwords($_SESSION['userName']) . ' added a new Covid-19 request data with the sample id ' . $_POST['covid19SampleId'];
		$resource = 'covid-19-add-request';

		$general->activityLog($eventType, $action, $resource);
	} else {
		$_SESSION['alertMsg'] = "Unable to add this Covid-19 sample. Please try again later";
	}
	if(isset($_POST['saveNext']) && $_POST['saveNext'] == 'next' && isset($_POST['quickForm']) && $_POST['quickForm'] == "quick"){
		header("location:/covid-19/requests/covid-19-quick-add.php");
	} else{
		header("location:/covid-19/requests/covid-19-requests.php");
	}

	if (isset($_POST['saveNext']) && $_POST['saveNext'] == 'next') {
		header("location:/covid-19/requests/covid-19-add-request.php");
	} else {
		header("location:/covid-19/requests/covid-19-requests.php");
	}
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
