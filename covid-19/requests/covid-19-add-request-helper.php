<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH . '/models/General.php');
$general = new General($db);

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
		'facility_id'                         => isset($_POST['facilityId']) ? $_POST['facilityId'] : null,
		'province_id'                         => isset($_POST['provinceId']) ? $_POST['provinceId'] : null,
		'lab_id'                              => isset($_POST['labId']) ? $_POST['labId'] : null,
		'implementing_partner'                => isset($_POST['implementingPartner']) ? $_POST['implementingPartner'] : null,
		'funding_source'                      => isset($_POST['fundingSource']) ? $_POST['fundingSource'] : null,
		'patient_id'                          => isset($_POST['patientId']) ? $_POST['patientId'] : null,
		'patient_name'                        => isset($_POST['firstName']) ? $_POST['firstName'] : null,
		'patient_surname'                     => isset($_POST['lastName']) ? $_POST['lastName'] : null,
		'patient_dob'                         => isset($_POST['patientDob']) ? $general->dateFormat($_POST['patientDob']) : null,
		'patient_gender'                      => isset($_POST['patientGender']) ? $_POST['patientGender'] : null,
		'patient_age'                         => isset($_POST['patientAge']) ? $_POST['patientAge'] : null,
		'patient_phone_number'                => isset($_POST['patientPhoneNumber']) ? $_POST['patientPhoneNumber'] : null,
		'patient_address'                     => isset($_POST['patientAddress']) ? $_POST['patientAddress'] : null,
		'patient_province'                    => isset($_POST['patientProvince']) ? $_POST['patientProvince'] : null,
		'patient_district'                    => isset($_POST['patientDistrict']) ? $_POST['patientDistrict'] : null,
		'patient_occupation'                  => isset($_POST['patientOccupation']) ? $_POST['patientOccupation'] : null,
		'patient_nationality'                 => isset($_POST['patientNationality']) ? $_POST['patientNationality'] : null,
		'flight_airline'                 	   => isset($_POST['airline']) ? $_POST['airline'] : null,
		'flight_seat_no'                 	   => isset($_POST['seatNo']) ? $_POST['seatNo'] : null,
		'flight_arrival_datetime'              => isset($_POST['arrivalDateTime']) ? $_POST['arrivalDateTime'] : null,
		'flight_airport_of_departure'          => isset($_POST['airportOfDeparture']) ? $_POST['airportOfDeparture'] : null,
		'flight_transit'          			   => isset($_POST['transit']) ? $_POST['transit'] : null,
		'reason_of_visit'          			   => isset($_POST['reasonOfVisit']) ? $_POST['reasonOfVisit'] : null,
		'is_sample_collected'                 => isset($_POST['isSampleCollected']) ? $_POST['isSampleCollected'] : null,
		'reason_for_covid19_test'             => isset($_POST['reasonForCovid19Test']) ? $_POST['reasonForCovid19Test'] : null,
		'specimen_type'                       => isset($_POST['specimenType']) ? $_POST['specimenType'] : null,
		'sample_collection_date'              => isset($_POST['sampleCollectionDate']) ? $_POST['sampleCollectionDate'] : null,
		'is_sample_post_mortem'               => isset($_POST['isSamplePostMortem']) ? $_POST['isSamplePostMortem'] : null,
		'priority_status'                     => isset($_POST['priorityStatus']) ? $_POST['priorityStatus'] : null,
		'date_of_symptom_onset'               => isset($_POST['dateOfSymptomOnset']) ? $general->dateFormat($_POST['dateOfSymptomOnset']) : null,
		'date_of_initial_consultation'        => isset($_POST['dateOfInitialConsultation']) ? $general->dateFormat($_POST['dateOfInitialConsultation']) : null,
		'fever_temp'        				  => isset($_POST['feverTemp']) ? $_POST['feverTemp'] : null,
		'close_contacts'        			  => isset($_POST['closeContacts']) ? $_POST['closeContacts'] : null,
		'contact_with_confirmed_case'          => isset($_POST['contactWithConfirmedCase']) ? $_POST['contactWithConfirmedCase'] : null,
		'has_recent_travel_history'           => isset($_POST['hasRecentTravelHistory']) ? $_POST['hasRecentTravelHistory'] : null,
		'travel_country_names'                => isset($_POST['countryName']) ? $_POST['countryName'] : null,
		'travel_return_date'                  => isset($_POST['returnDate']) ? $general->dateFormat($_POST['returnDate']) : null,
		'sample_received_at_vl_lab_datetime'  => isset($_POST['sampleReceivedDate']) ? $_POST['sampleReceivedDate'] : null,
		'is_sample_rejected'                  => isset($_POST['isSampleRejected']) ? $_POST['isSampleRejected'] : null,
		'result'                              => isset($_POST['result']) ? $_POST['result'] : null,
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
	if($_POST['formId'] == 3) {
		$covid19Data['suspected_case_drc'] 			= isset($_POST['suspectedCase'])?$_POST['suspectedCase']:null;
		$covid19Data['probable_case_drc'] 			= isset($_POST['probableCase'])?$_POST['probableCase']:null;
		$covid19Data['confirme_case_drc'] 			= isset($_POST['confirmeCase'])?$_POST['confirmeCase']:null;
		$covid19Data['contact_case_drc'] 			= isset($_POST['contactCase'])?$_POST['contactCase']:null;
		$covid19Data['respiratory_rate_option_drc']	= isset($_POST['respiratoryRateSelect'])?$_POST['respiratoryRateSelect']:null;
		$covid19Data['respiratory_rate_drc'] 		= (isset($_POST['respiratoryRate']) && $_POST['respiratoryRateSelect'] == 'yes')?$_POST['respiratoryRate']:null;
		$covid19Data['oxygen_saturation_option_drc']= isset($_POST['oxygenSaturationSelect'])?$_POST['oxygenSaturationSelect']:null;
		$covid19Data['oxygen_saturation_drc'] 		= (isset($_POST['oxygenSaturation']) && $_POST['oxygenSaturationSelect'] == 'yes')?$_POST['oxygenSaturation']:null;
		$covid19Data['sick_days_drc'] 				= isset($_POST['sickDays'])?$_POST['sickDays']:null;
		$covid19Data['onset_illness_date_drc'] 		= isset($_POST['onsetIllnessDate'])?$general->dateFormat($_POST['onsetIllnessDate']):null;
		$covid19Data['medical_background_drc'] 		= isset($_POST['medicalBackground'])?$_POST['medicalBackground']:null;
		if($_POST['medicalBackground'] == 'yes'){
			foreach($_POST['medicalBg'] as $index=>$val){
				$covid19Data[$index] = isset($val)?$val:null;
			}
		}
		$covid19Data['conacted_14_days_drc'] 		= isset($_POST['conacted14Days'])?$_POST['conacted14Days']:null;
		$covid19Data['smoke_drc'] 					= isset($_POST['smoke'])?$_POST['smoke']:null;
		$covid19Data['profession_drc'] 				= isset($_POST['profession'])?$_POST['profession']:null;
		$covid19Data['confirmation_lab_drc'] 		= isset($_POST['confirmationLab'])?$_POST['confirmationLab']:null;
		$covid19Data['result_pscr_drc'] 			= isset($_POST['resultPcr'])?$general->dateFormat($_POST['resultPcr']):null;
	}

	// echo "<pre>";
	// var_dump($covid19Data);die;

	$db = $db->where('covid19_id', $_POST['covid19SampleId']);
	$db->delete("covid19_patient_symptoms");
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


	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '' && ($_POST['isSampleRejected'] == 'no' || $_POST['isSampleRejected'] == '')) {
		if (isset($_POST['testName']) && count($_POST['testName']) > 0) {
			foreach ($_POST['testName'] as $testKey => $testKitName) {
				if (isset($_POST['testDate'][$testKey]) && trim($_POST['testDate'][$testKey]) != "") {
					$testedDateTime = explode(" ", $_POST['testDate'][$testKey]);
					$_POST['testDate'][$testKey] = $general->dateFormat($testedDateTime[0]) . " " . $testedDateTime[1];
				} else {
					$_POST['testDate'][$testKey] = NULL;
				}
				$covid19TestData = array(
					'covid19_id'			=> $_POST['covid19SampleId'],
					'test_name'				=> $testKitName,
					'facility_id'           => isset($_POST['labId']) ? $_POST['labId'] : null,
					'sample_tested_datetime'=> date('Y-m-d H:i:s', strtotime($_POST['testDate'][$testKey])),
					'result'				=> $_POST['testResult'][$testKey],
				);
				$db->insert($testTableName, $covid19TestData);
				$covid19Data['sample_tested_datetime'] = date('Y-m-d H:i:s', strtotime($_POST['testDate'][$testKey]));
			}
		}
	} else {
		$db = $db->where('covid19_id', $_POST['covid19SampleId']);
		$db->delete($testTableName);
		$covid19Data['sample_tested_datetime'] = null;
	}

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
	if (isset($_POST['saveNext']) && $_POST['saveNext'] == 'next') {
		header("location:/covid-19/requests/covid-19-add-request.php");
	} else {
		header("location:/covid-19/requests/covid-19-requests.php");
	}
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
