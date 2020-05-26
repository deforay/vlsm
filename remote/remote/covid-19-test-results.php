<?php
//this file is receive lab data value and update in remote db
$data = json_decode(file_get_contents('php://input'), true);

require_once(dirname(__FILE__) . "/../../startup.php");
require_once(APPLICATION_PATH . '/includes/MysqliDb.php');
require_once(APPLICATION_PATH . '/models/General.php');

$cQuery = "SELECT * FROM global_config";
$cResult = $db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
    $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}

$general = new General($db);

$allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . $systemConfig['dbName'] . "' AND table_name='form_covid19'";
$allColResult = $db->rawQuery($allColumns);
$oneDimensionalArray = array_map('current', $allColResult);
$sampleCode = array();
if (count($data['result']) > 0) {
    $lab = array();
    foreach ($data['result'] as $key => $remoteData) {
        foreach ($oneDimensionalArray as $result) {
            if (isset($remoteData[$result])) {
                $lab[$result] = $remoteData[$result];
            } else {
                $lab[$result] = null;
            }
        }

        // before we unset the covid19_id field, let us fetch the
        // test results, comorbidities and symptoms

        $symptoms = (isset($data['symptoms'][$lab['covid19_id']]) && !empty($data['symptoms'][$lab['covid19_id']])) ? $data['symptoms'][$lab['covid19_id']] : array();
        $comorbidities = (isset($data['comorbidities'][$lab['covid19_id']]) && !empty($data['comorbidities'][$lab['covid19_id']])) ? $data['comorbidities'][$lab['covid19_id']] : array();
        $testResults = (isset($data['testResults'][$lab['covid19_id']]) && !empty($data['testResults'][$lab['covid19_id']])) ? $data['testResults'][$lab['covid19_id']] : array();


        //remove fields that we DO NOT NEED here
        $removeKeys = array(
            'covid19_id',
            'sample_package_id',
            'sample_package_code',
            //'last_modified_by',
            'request_created_by',
        );
        foreach ($removeKeys as $keys) {
            unset($lab[$keys]);
        }



        if (isset($remoteData['approved_by_name']) && $remoteData['approved_by_name'] != '') {

            $userQuery = 'select user_id from user_details where user_name = "' . $remoteData['approved_by_name'] . '" or user_name = "' . strtolower($remoteData['approved_by_name']) . '"';
            $userResult = $db->rawQuery($userQuery);
            if (isset($userResult[0]['user_id'])) {
                // NO NEED TO DO ANYTHING SINCE $lab['result_approved_by'] is already there
                //$lab['result_approved_by'] = $userResult[0]['user_id'];
            } else {
                $userId = $general->generateUserID();
                $userData = array(
                    'user_id' => $userId,
                    'user_name' => $remoteData['approved_by_name'],
                    'role_id' => 4,
                    'status' => 'inactive'
                );
                $db->insert('user_details', $userData);
                $lab['result_approved_by'] = $userId;
            }
            // we dont need this now
            //unset($remoteData['approved_by_name']);
        }

        $lab['data_sync'] = 1; //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
        $lab['last_modified_datetime'] = $general->getDateTime();

        unset($lab['request_created_by']);
        unset($lab['last_modified_by']);
        unset($lab['request_created_datetime']);

        if ($lab['result_status'] != 7 && $lab['result_status'] != 4) {
            unset($lab['result']);
            unset($lab['is_sample_rejected']);
            unset($lab['reason_for_sample_rejection']);
        }

        // Checking if Remote Sample Code is set, if not set we will check if Sample Code is set
        if (isset($lab['remote_sample_code']) && $lab['remote_sample_code'] != '') {
            $sQuery = "SELECT covid19_id,sample_code,remote_sample_code,remote_sample_code_key FROM form_covid19 WHERE remote_sample_code='" . $lab['remote_sample_code'] . "'";
        } else if (isset($lab['sample_code']) && $lab['sample_code'] != '' && !empty($lab['facility_id'])) {
            $sQuery = "SELECT covid19_id,sample_code,remote_sample_code,remote_sample_code_key FROM form_covid19 WHERE sample_code='" . $lab['sample_code'] . "' AND facility_id = " . $lab['facility_id'];
        } else {

            $sampleCode[] = $lab['sample_code'];
            continue;
        }

        $sResult = $db->rawQuery($sQuery);
        if ($sResult) {
            $db = $db->where('covid19_id', $sResult[0]['covid19_id']);
            $id = $db->update('form_covid19', $lab);
        } else {
            $id = $db->insert('form_covid19', $lab);
        }

        $db = $db->where('form_id', $id);
        $db->delete("covid19_patient_symptoms");
        if (isset($symptoms) && !empty($symptoms)) {

            foreach ($symptoms as $symId => $symValue) {
                $symptomData = array();
                $symptomData["form_id"] = $id;
                $symptomData["symptom_id"] = $symId;
                $symptomData["symptom_detected"] = $symValue;
                $db->insert("covid19_patient_symptoms", $symptomData);
            }
        }

        $db = $db->where('form_id', $id);
        $db->delete("covid19_patient_comorbidities");
        if (isset($comorbidities) && !empty($comorbidities)) {

            foreach ($comorbidities as $comoId => $comoValue) {
                $comorbidityData = array();
                $comorbidityData["form_id"] = $id;
                $comorbidityData["comorbidity_id"] = $comoId;
                $comorbidityData["comorbidity_detected"] = $comoValue;
                $db->insert("covid19_patient_comorbidities", $comorbidityData);
            }
        }
        $db = $db->where('covid19_id', $id);
        $db->delete("covid19_tests");
        if (isset($testResults) && !empty($testResults)) {

            foreach ($testResults as $testValue) {
                $covid19TestData = array(
					'covid19_id'			=> $id,
                    'test_name'				=> $testValue['test_name'],
                    'facility_id'           => isset($lab['facility_id']) ? $lab['facility_id'] : null,
					'sample_tested_datetime' => $testValue['sample_tested_datetime'],
					'result'				=> $testValue['result'],
				);
                $db->insert("covid19_tests", $covid19TestData);
            }
        }

        if ($id > 0 && isset($lab['sample_code'])) {
            $sampleCode[] = $lab['sample_code'];
        }
    }
}

echo json_encode($sampleCode);
