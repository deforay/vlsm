<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH . '/models/General.php');
$general = new General($db);

// echo "<pre>";var_dump($_POST);die;


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


	if (isset($_POST['patientDob']) && trim($_POST['patientDob']) != "") {
		$_POST['patientDob'] = $general->dateFormat($_POST['patientDob']);
	} else {
		$_POST['patientDob'] = NULL;
	}

	if (isset($_POST['dateOfSymptomOnset']) && trim($_POST['dateOfSymptomOnset']) != "") {
		$_POST['dateOfSymptomOnset'] = $general->dateFormat($_POST['dateOfSymptomOnset']);
	} else {
		$_POST['dateOfSymptomOnset'] = NULL;
	}

	if (isset($_POST['returnDate']) && trim($_POST['returnDate']) != "") {
		$_POST['returnDate'] = $general->dateFormat($_POST['returnDate']);
	} else {
		$_POST['returnDate'] = NULL;
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
		'patient_dob'                         => isset($_POST['patientDob']) ? $_POST['patientDob'] : null,
		'patient_gender'                      => isset($_POST['patientGender']) ? $_POST['patientGender'] : null,
		'patient_age'                         => isset($_POST['patientAge']) ? $_POST['patientAge'] : null,
		'patient_phone_number'                => isset($_POST['patientPhoneNumber']) ? $_POST['patientPhoneNumber'] : null,
		'patient_address'                     => isset($_POST['patientAddress']) ? $_POST['patientAddress'] : null,
		'patient_province'                    => isset($_POST['patientProvince']) ? $_POST['patientProvince'] : null,
		'patient_district'                    => isset($_POST['patientDistrict']) ? $_POST['patientDistrict'] : null,
		'specimen_type'                       => isset($_POST['specimenType']) ? $_POST['specimenType'] : null,
		'sample_collection_date'              => isset($_POST['sampleCollectionDate']) ? $_POST['sampleCollectionDate'] : null,
		'is_sample_post_mortem'               => isset($_POST['isSamplePostMortem']) ? $_POST['isSamplePostMortem'] : null,
		'priority_status'                     => isset($_POST['priorityStatus']) ? $_POST['priorityStatus'] : null,
		'date_of_symptom_onset'               => isset($_POST['dateOfSymptomOnset']) ? $_POST['dateOfSymptomOnset'] : null,
		'contact_with_confirmed_case'         => isset($_POST['contactWithConfirmedCase']) ? $_POST['contactWithConfirmedCase'] : null,
		'has_recent_travel_history'           => isset($_POST['hasRecentTravelHistory']) ? $_POST['hasRecentTravelHistory'] : null,
		'travel_country_names'                => isset($_POST['countryName']) ? $_POST['countryName'] : null,
		'travel_return_date'                  => isset($_POST['returnDate']) ? $_POST['returnDate'] : null,
		'sample_received_at_vl_lab_datetime'  => isset($_POST['sampleReceivedDate']) ? $_POST['sampleReceivedDate'] : null,
		// 'sample_tested_datetime'              => isset($_POST['sampleTestedDateTime']) ? $_POST['sampleTestedDateTime'] : null,
		'sample_tested_datetime'              => $general->getDateTime(),
		'is_sample_rejected'                  => isset($_POST['isSampleRejected']) ? $_POST['isSampleRejected'] : null,
		'result'                              => isset($_POST['result']) ? $_POST['result'] : null,
		'is_result_authorised'                => isset($_POST['isResultAuthorized']) ? $_POST['isResultAuthorized'] : null,
		'authorized_by'                       => isset($_POST['authorizedBy']) ? $_POST['authorizedBy'] : null,
		'authorized_on' 					  => isset($_POST['authorizedOn']) ? $general->dateFormat($_POST['authorizedOn']) : null,
		'result_status'                       => $status,
		'data_sync'                           => 0,
		'reason_for_sample_rejection'         => isset($_POST['sampleRejectionReason']) ? $_POST['sampleRejectionReason'] : null,
		'request_created_by'                  => $_SESSION['userId'],
		'request_created_datetime'            => $general->getDateTime(),
		'sample_registered_at_lab'            => $general->getDateTime(),
		'last_modified_by'                    => $_SESSION['userId'],
		'last_modified_datetime'              => $general->getDateTime()
	);

	// echo "<pre>";
	// var_dump($covid19Data);die;
	
	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '') {
		// echo "<pre>"; print_r($covid19Data);die;
		$db = $db->where('covid19_id', $_POST['covid19SampleId']);
		$id = $db->update($tableName, $covid19Data);
	}
	if (isset($_POST['covid19SampleId']) && $_POST['covid19SampleId'] != '' && ($_POST['isSampleRejected'] == 'no' || $_POST['isSampleRejected'] == '')) {
		if(isset($_POST['testName']) && count($_POST['testName']) > 0){
			foreach($_POST['testName'] as $testKey=>$testKitName){
				if (isset($_POST['testDate'][$testKey]) && trim($_POST['testDate'][$testKey]) != "") {
					$testedDateTime = explode(" ", $_POST['testDate'][$testKey]);
					$_POST['testDate'][$testKey] = $general->dateFormat($testedDateTime[0]) . " " . $testedDateTime[1];
				} else {
					$_POST['testDate'][$testKey] = NULL;
				}
				$covid19TestData = array(
					'covid19_id'			=> $_POST['covid19SampleId'],
					'test_name'				=> $testKitName,
					'sample_tested_datetime'=> date('Y-m-d H:i:s',strtotime($_POST['testDate'][$testKey])),
					'result'				=> $_POST['testResult'][$testKey],
				);
				$db->insert($testTableName,$covid19TestData);
			}
		}
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
