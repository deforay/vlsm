<?php

session_unset(); // no need of session in json response

// PURPOSE : Fetch Results using serial_no field which is used to
// store the recency id from third party apps (for eg. in DRC)

// serial_no field in db was unused so we decided to use it to store recency id

ini_set('memory_limit', -1);
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);

$tableName = "form_covid19";
$tableName1 = "activity_log";
$testTableName = 'covid19_tests';


$data = json_decode(file_get_contents("php://input"));
// print_r("dc");die;



try {

    if (isset($data->sampleCollectionDate) && trim($data->sampleCollectionDate) != "") {
        $sampleCollectionDate = explode(" ", $data->sampleCollectionDate);
        $data->sampleCollectionDate = $general->dateFormat($sampleCollectionDate[0]) . " " . $sampleCollectionDate[1];
    } else {
        $data->sampleCollectionDate = NULL;
    }
    
    
    //Set sample received date
    if (isset($data->sampleReceivedDate) && trim($data->sampleReceivedDate) != "") {
        $sampleReceivedDate = explode(" ", $data->sampleReceivedDate);
        $data->sampleReceivedDate = $general->dateFormat($sampleReceivedDate[0]) . " " . $sampleReceivedDate[1];
    } else {
        $data->sampleReceivedDate = NULL;
    }
    
    if (isset($data->sampleTestedDateTime) && trim($data->sampleTestedDateTime) != "") {
        $sampleTestedDate = explode(" ", $data->sampleTestedDateTime);
        $data->sampleTestedDateTime = $general->dateFormat($sampleTestedDate[0]) . " " . $sampleTestedDate[1];
    } else {
        $data->sampleTestedDateTime = NULL;
    }
    
    if (isset($data->arrivalDateTime) && trim($data->arrivalDateTime) != "") {
        $arrivalDate = explode(" ", $data->arrivalDateTime);
        $data->arrivalDateTime = $general->dateFormat($arrivalDate[0]) . " " . $arrivalDate[1];
    } else {
        $data->arrivalDateTime = NULL;
    }
    
    
    if (!isset($data->sampleCode) || trim($data->sampleCode) == '') {
        $data->sampleCode = NULL;
    }
    
    if (isset($data->isSampleRejected) && $data->isSampleRejected == 'yes') {
        $data->result = null;
        $status = 4;
    }
    
    
    
    $covid19Data = array(
        // 'vlsm_instance_id'                    => $instanceId,
        'vlsm_country_id'                     => $data->formId,
        'external_sample_code'                => isset($data->externalSampleCode) ? $data->externalSampleCode : null,
        'facility_id'                         => isset($data->facilityId) ? $data->facilityId : null,
        'test_number'                         => isset($data->testNumber) ? $data->testNumber : null,
        'province_id'                         => isset($data->provinceId) ? $data->provinceId : null,
        'lab_id'                              => isset($data->labId) ? $data->labId : null,
        'testing_point'                       => isset($data->testingPoint) ? $data->testingPoint : null,
        'implementing_partner'                => isset($data->implementingPartner) ? $data->implementingPartner : null,
        'source_of_alert'                	  => isset($data->sourceOfAlertPOE) ? $data->sourceOfAlertPOE : null,
        'source_of_alert_other'               => (isset($data->alertPoeOthers) && $data->alertPoeOthers== 'others') ? $data->alertPoeOthers : null,
        'funding_source'                      => isset($data->fundingSource) ? $data->fundingSource : null,
        'patient_id'                          => isset($data->patientId) ? $data->patientId : null,
        'patient_name'                        => isset($data->firstName) ? $data->firstName : null,
        'patient_surname'                     => isset($data->lastName) ? $data->lastName : null,
        'patient_dob'                         => isset($data->patientDob) ? $general->dateFormat($data->patientDob) : null,
        'patient_gender'                      => isset($data->patientGender) ? $data->patientGender : null,
        'is_patient_pregnant'                 => isset($data->isPatientPregnant) ? $data->isPatientPregnant : null,
        'patient_age'                         => isset($data->patientAge) ? $data->patientAge : null,
        'patient_phone_number'                => isset($data->patientPhoneNumber) ? $data->patientPhoneNumber : null,
        'patient_address'                     => isset($data->patientAddress) ? $data->patientAddress : null,
        'patient_province'                    => isset($data->patientProvince) ? $data->patientProvince : null,
        'patient_district'                    => isset($data->patientDistrict) ? $data->patientDistrict : null,
        'patient_city'                    	  => isset($data->patientCity) ? $data->patientCity : null,
        'patient_occupation'                  => isset($data->patientOccupation) ? $data->patientOccupation : null,
        'does_patient_smoke'                  => isset($data->doesPatientSmoke) ? $data->doesPatientSmoke : null,
        'patient_nationality'                 => isset($data->patientNationality) ? $data->patientNationality : null,
        'patient_passport_number'             => isset($data->patientPassportNumber) ? $data->patientPassportNumber : null,
        'flight_airline'                 	  => isset($data->airline) ? $data->airline : null,
        'flight_seat_no'                 	  => isset($data->seatNo) ? $data->seatNo : null,
        'flight_arrival_datetime'             => isset($data->arrivalDateTime) ? $data->arrivalDateTime : null,
        'flight_airport_of_departure'         => isset($data->airportOfDeparture) ? $data->airportOfDeparture : null,
        'flight_transit'          			  => isset($data->transit) ? $data->transit : null,
        'reason_of_visit'          			  => isset($data->reasonOfVisit) ? $data->reasonOfVisit : null,
        'is_sample_collected'                 => isset($data->isSampleCollected) ? $data->isSampleCollected : null,
        'reason_for_covid19_test'             => isset($data->reasonForCovid19Test) ? $data->reasonForCovid19Test : null,
        'type_of_test_requested'              => isset($data->testTypeRequested) ? $data->testTypeRequested : null,
        'specimen_type'                       => isset($data->specimenType) ? $data->specimenType : null,
        'sample_collection_date'              => isset($data->sampleCollectionDate) ? $data->sampleCollectionDate : null,
        'is_sample_post_mortem'               => isset($data->isSamplePostMortem) ? $data->isSamplePostMortem : null,
        'priority_status'                     => isset($data->priorityStatus) ? $data->priorityStatus : null,
        'number_of_days_sick'                 => isset($data->numberOfDaysSick) ? $data->numberOfDaysSick : null,
        'date_of_symptom_onset'               => isset($data->dateOfSymptomOnset) ? $general->dateFormat($data->dateOfSymptomOnset) : null,
        'date_of_initial_consultation'        => isset($data->dateOfInitialConsultation) ? $general->dateFormat($data->dateOfInitialConsultation) : null,
        'fever_temp'        				  => isset($data->feverTemp) ? $data->feverTemp : null,
        'medical_history'        			  => isset($data->medicalHistory) ? $data->medicalHistory : null,
        'recent_hospitalization'   			  => isset($data->recentHospitalization) ? $data->recentHospitalization : null,
        'patient_lives_with_children'		  => isset($data->patientLivesWithChildren) ? $data->patientLivesWithChildren : null,
        'patient_cares_for_children'		  => isset($data->patientCaresForChildren) ? $data->patientCaresForChildren : null,
        'temperature_measurement_method' 	  => isset($data->temperatureMeasurementMethod) ? $data->temperatureMeasurementMethod : null,
        'respiratory_rate' 	  				  => isset($data->respiratoryRate) ? $data->respiratoryRate : null,
        'oxygen_saturation'	  				  => isset($data->oxygenSaturation) ? $data->oxygenSaturation : null,
        'close_contacts'        			  => isset($data->closeContacts) ? $data->closeContacts : null,
        'contact_with_confirmed_case'         => isset($data->contactWithConfirmedCase) ? $data->contactWithConfirmedCase : null,
        'has_recent_travel_history'           => isset($data->hasRecentTravelHistory) ? $data->hasRecentTravelHistory : null,
        'travel_country_names'                => isset($data->countryName) ? $data->countryName : null,
        'travel_return_date'                  => isset($data->returnDate) ? $general->dateFormat($data->returnDate) : null,
        'sample_received_at_vl_lab_datetime'  => isset($data->sampleReceivedDate) ? $data->sampleReceivedDate : null,
        'sample_condition'  				  => isset($data->sampleCondition) ? $data->sampleCondition : (isset($data->specimenQuality) ? $data->specimenQuality : null),
        'lab_technician' 					  => (isset($data->labTechnician) && $data->labTechnician != '') ? $data->labTechnician : '',
        'is_sample_rejected'                  => isset($data->isSampleRejected) ? $data->isSampleRejected : null,
        'result'                              => isset($data->result) ? $data->result : null,
        'other_diseases'                      => (isset($data->otherDiseases) && $data->result != 'positive') ? $data->otherDiseases : null,
        // 'tested_by'                       	  => isset($data->testedBy) ? $data->testedBy : null,
        'is_result_authorised'                => isset($data->isResultAuthorized) ? $data->isResultAuthorized : null,
        'authorized_by'                       => isset($data->authorizedBy) ? $data->authorizedBy : null,
        'authorized_on' 					  => isset($data->authorizedOn) ? $general->dateFormat($data->authorizedOn) : null,
        'rejection_on'	 					  => (isset($data->rejectionDate) && $data->isSampleRejected == 'yes') ? $general->dateFormat($data->rejectionDate) : null,
        'result_status'                       => $status,
        'data_sync'                           => 0,
        'reason_for_sample_rejection'         => (isset($data->sampleRejectionReason) && $data->isSampleRejected == 'yes') ? $data->sampleRejectionReason : null,
        'request_created_datetime'            => $general->getDateTime(),
        'sample_registered_at_lab'            => $general->getDateTime(),
        'last_modified_datetime'              => $general->getDateTime()
    );
    $lock = $general->getGlobalConfig('lock_approved_covid19_samples');
    if($status == 7 && $lock == 'yes'){
        $covid19Data['locked'] = 'yes';
    }
    // echo "<pre>";
    // print_r($covid19Data);die;
    
    $db = $db->where('covid19_id', $data->covid19SampleId);
    $db->delete("covid19_patient_symptoms");
    if (isset($data->symptomDetected) && !empty($data->symptomDetected) || (isset($data->symptom) && !empty($data->symptom))) {
        for ($i = 0; $i < count($data->symptomDetected); $i++) {
            $symptomData = array();
            $symptomData["covid19_id"] = $data->covid19SampleId;
            $symptomData["symptom_id"] = $data->symptomId[$i];
            $symptomData["symptom_detected"] = $data->symptomDetected[$i];
            $symptomData["symptom_details"] 	= (isset($data->symptomDetails[$data->symptomId[$i]]) && count($data->symptomDetails[$data->symptomId[$i]]) > 0) ? json_encode($data->symptomDetails[$data->symptomId[$i]]) : null;
            //var_dump($symptomData);
            $db->insert("covid19_patient_symptoms", $symptomData);
        }
    }
    
    $db = $db->where('covid19_id', $data->covid19SampleId);
    $db->delete("covid19_reasons_for_testing");
    if (!empty($data->reasonDetails)) {
        $reasonData = array();
        $reasonData["covid19_id"] 		= $data->covid19SampleId;
        $reasonData["reasons_id"] 		= $data->reasonForCovid19Test;
        $reasonData["reasons_detected"]	= "yes";
        $reasonData["reason_details"] 	= json_encode($data->reasonDetails);
        //var_dump($reasonData);
        $db->insert("covid19_reasons_for_testing", $reasonData);
    }
    //die;
    $db = $db->where('covid19_id', $data->covid19SampleId);
    $db->delete("covid19_patient_comorbidities");
    if (isset($data->comorbidityDetected) && !empty($data->comorbidityDetected)) {
    
        for ($i = 0; $i < count($data->comorbidityDetected); $i++) {
            $comorbidityData = array();
            $comorbidityData["covid19_id"] = $data->covid19SampleId;
            $comorbidityData["comorbidity_id"] = $data->comorbidityId[$i];
            $comorbidityData["comorbidity_detected"] = $data->comorbidityDetected[$i];
            $db->insert("covid19_patient_comorbidities", $comorbidityData);
        }
    }
    
    // echo "<pre>";print_r($data->testName);die;
    if (isset($data->covid19SampleId) && $data->covid19SampleId != '' && ($data->isSampleRejected == 'no' || $data->isSampleRejected == '')) {
        if (isset($data->testName) && count($data->testName) > 0) {
            foreach ($data->testName as $testKey => $testKitName) {
                if (isset($testKitName) && !empty($testKitName)) {
                    if (isset($data->testDate[$testKey]) && trim($data->testDate[$testKey]) != "") {
                        $testedDateTime = explode(" ", $data->testDate[$testKey]);
                        $data->testDate[$testKey] = $general->dateFormat($testedDateTime[0]) . " " . $testedDateTime[1];
                    } else {
                        $data->testDate[$testKey] = NULL;
                    }
                    $covid19TestData = array(
                        'covid19_id'			=> $data->covid19SampleId,
                        'test_name'				=> ($testKitName == 'other') ? $data->testNameOther[$testKey] : $testKitName,
                        'facility_id'           => isset($data->labId) ? $data->labId : null,
                        'sample_tested_datetime' => date('Y-m-d H:i:s', strtotime($data->testDate[$testKey])),
                        'testing_platform'      => isset($data->testingPlatform[$testKey]) ? $data->testingPlatform[$testKey] : null,
                        'result'				=> $data->testResult[$testKey],
                    );
                    $db->insert($testTableName, $covid19TestData);
                    $covid19Data['sample_tested_datetime'] = date('Y-m-d H:i:s', strtotime($data->testDate[$testKey]));
                }
            }
        }
    } else {
        $db = $db->where('covid19_id', $data->covid19SampleId);
        $db->delete($testTableName);
        $covid19Data['sample_tested_datetime'] = null;
    }
    $id = 0;
    if (isset($data->covid19SampleId) && $data->covid19SampleId != '') {
        // echo "<pre>"; print_r($covid19Data);die;
        $db = $db->where('covid19_id', $data->covid19SampleId);
        $id = $db->update($tableName, $covid19Data);
    }


    $payload = array(
        'status' => 'success',
        'timestamp' => time(),
        'message' => 'Successfully added.'
    );
   

    http_response_code(200);
    echo json_encode($payload);
    exit(0);
} catch (Exception $exc) {

    http_response_code(500);
    $payload = array(
        'status' => 'failed',
        'timestamp' => time(),
        'error' => $exc->getMessage(),
        'data' => array()
    );


    echo json_encode($payload);

    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}
