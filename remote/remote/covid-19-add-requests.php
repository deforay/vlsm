<?php
//this file is get the data from remote db
$apiResult = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php");
$general = new \Vlsm\Models\General($db);

if ($apiResult['module'] == 'covid19') {

    $removeKeys = array(
        'covid19_id',
        'sample_batch_id',
    );
    $c19Data = array();
    if (!empty($apiResult['data']) && is_array($apiResult['data']) && count($apiResult['data']) > 0) {
        $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . $systemConfig['dbName'] . "' AND table_name='form_covid19'";
        $allColResult = $db->rawQuery($allColumns);
        $columnList = array_map('current', $allColResult);
        $columnList = array_diff($columnList, $removeKeys);
        foreach ($apiResult['data']['c19Data'] as $key => $labData) {
            $request = array();
            $covid19Id = $labData['covid19_id'];
            foreach ($columnList as $colName) {
                if (isset($labData[$colName])) {
                    $request[$colName] = $labData[$colName];
                } else {
                    $request[$colName] = null;
                }
            }

            // before we unset the covid19_id field, let us fetch the
            // test results, comorbidities and symptoms
            $symptoms = (isset($apiResult['data']['symptoms'][$covid19Id]) && !empty($apiResult['data']['symptoms'][$covid19Id])) ? $apiResult['data']['symptoms'][$covid19Id] : array();
            $comorbidities = (isset($apiResult['data']['comorbidities'][$covid19Id]) && !empty($apiResult['data']['comorbidities'][$covid19Id])) ? $apiResult['data']['comorbidities'][$covid19Id] : array();
            $testResults = (isset($apiResult['data']['testResults'][$covid19Id]) && !empty($apiResult['data']['testResults'][$covid19Id])) ? $apiResult['data']['testResults'][$covid19Id] : array();
            $request['last_modified_datetime'] = $general->getDateTime();
            //check exist remote
            $sampleCode = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "")?$labData['remote_sample_code']:$labData['sample_code'];
            $exsvlQuery = "SELECT covid19_id,sample_code FROM form_covid19 AS vl WHERE (remote_sample_code='" . $sampleCode . "' OR sample_code='" . $sampleCode . "')";
            
            $exsvlResult = $db->query($exsvlQuery);
            if ($exsvlResult) {

                $dataToUpdate = array();
                $dataToUpdate['sample_package_code'] = $request['sample_package_code'];
                $dataToUpdate['sample_package_id'] = $request['sample_package_id'];

                $db = $db->where('covid19_id', $exsvlResult[0]['covid19_id']);
                $db->update('form_covid19', $dataToUpdate);
                $updateId  = $exsvlResult[0]['covid19_id'];
                $insertId = $exsvlResult[0]['covid19_id'];
                if($updateId > 0){
                    $c19Data['update'][] = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "")?$labData['remote_sample_code']:$labData['sample_code'];
                }
            } else {
                if (!empty($request['sample_collection_date'])) {
                    $request['request_created_by'] = 0;
                    $request['last_modified_by'] = 0;
                    $request['request_created_datetime'] = $general->getDateTime();
                    $request['data_sync'] = 0;
                    $db->insert('form_covid19', $request);
                    $insertId = $db->getInsertId();
                    if($insertId > 0){
                        $c19Data['insert'][] = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "")?$labData['remote_sample_code']:$labData['sample_code'];
                    }
                }
            }


            $db = $db->where('covid19_id', $insertId);
            $db->delete("covid19_patient_symptoms");
            if (isset($symptoms) && !empty($symptoms)) {

                foreach ($symptoms as $symId => $symValue) {
                    $symptomData = array();
                    $symptomData["covid19_id"] = $insertId;
                    $symptomData["symptom_id"] = $symId;
                    $symptomData["symptom_detected"] = $symValue;
                    $db->insert("covid19_patient_symptoms", $symptomData);
                }
            }

            $db = $db->where('covid19_id', $insertId);
            $db->delete("covid19_patient_comorbidities");
            if (isset($comorbidities) && !empty($comorbidities)) {

                foreach ($comorbidities as $comoId => $comoValue) {
                    $comorbidityData = array();
                    $comorbidityData["covid19_id"] = $insertId;
                    $comorbidityData["comorbidity_id"] = $comoId;
                    $comorbidityData["comorbidity_detected"] = $comoValue;
                    $db->insert("covid19_patient_comorbidities", $comorbidityData);
                }
            }

            $db = $db->where('covid19_id', $insertId);
            $db->delete("covid19_tests");
            if (isset($testResults) && !empty($testResults)) {
                foreach ($testResults as $testValue) {
                    $covid19TestData = array(
                        'covid19_id'            => $insertId,
                        'test_name'             => $testValue['test_name'],
                        'facility_id'           => $testValue['facility_id'],
                        'sample_tested_datetime'=> $testValue['sample_tested_datetime'],
                        'result'                => $testValue['result'],
                    );
                    $db->insert("covid19_tests", $covid19TestData);
                }
            }
        }
    }
    echo json_encode($c19Data);
}
