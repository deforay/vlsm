<?php

ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$arr = array();
$general = new \Vlsm\Models\General($db);
$usersModel = new \Vlsm\Models\Users($db);

$tableName = "form_covid19";
$testTableName = 'covid19_tests';
try {
    $arr = $general->getGlobalConfig();

    $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['requestFile']['name']);
    $fileName = str_replace(" ", "-", $fileName);
    $ranNumber = str_pad(rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileName = $ranNumber . "." . $extension;

    if (!file_exists(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request") && !is_dir(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request")) {
        mkdir(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request");
    }
    if (move_uploaded_file($_FILES['requestFile']['tmp_name'], TEMP_PATH . DIRECTORY_SEPARATOR . "import-request" . DIRECTORY_SEPARATOR . $fileName)) {

        $file_info = new finfo(FILEINFO_MIME); // object oriented approach!
        $mime_type = $file_info->buffer(file_get_contents(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request" . DIRECTORY_SEPARATOR . $fileName)); // e.g. gives "image/jpeg"

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request" . DIRECTORY_SEPARATOR . $fileName);
        $sheetData   = $spreadsheet->getActiveSheet();
        $sheetData   = $sheetData->toArray(null, true, true, true);

        // echo "<pre>";print_r($sheetData);die;
        $returnArray = array();
        foreach ($sheetData as $rowIndex => $rowData) {
            if ($rowIndex != 1) {
                if (isset($rowData['A']) && !empty($rowData['A'])) {
                    $sampleCode = $general->getDublicateDataFromField('form_covid19', 'sample_code', $rowData['A']);

                    $facility = $general->getDublicateDataFromField('facility_details', 'facility_name', $rowData['B']);
                    $testReason = $general->getDublicateDataFromField('r_covid19_test_reasons', 'test_reason_name', $rowData['O']);
                    $sampleType = $general->getDublicateDataFromField('r_covid19_sample_type', 'sample_name', $rowData['Q']);
                    $labName = $general->getDublicateDataFromField('facility_details', 'facility_name', $rowData['T'], 'facility_type');
                    $rejectionReason = $general->getDublicateDataFromField('r_covid19_sample_rejection_reasons', 'rejection_reason_name', $rowData['V']);

                    $result = $general->getDublicateDataFromField('r_covid19_results', 'result', $rowData['AF']);
                    $resultStatus = $general->getDublicateDataFromField('r_sample_status', 'status_name', $rowData['AJ']);
                    $labTechnician = $usersModel->addUserIfNotExists($rowData['AM']);

                    if (trim($rowData['P']) != '') {
                        $sampleCollectionDate = date('Y-m-d H:i:s', strtotime($rowData['P']));
                    } else {
                        $sampleCollectionDate = null;
                    }

                    if (trim($rowData['S']) != '') {
                        $sampleReceivedDate = date('Y-m-d H:i:s', strtotime($rowData['S']));
                    } else {
                        $sampleReceivedDate = null;
                    }

                    $data = array(
                        'sample_code'                           => $rowData['A'],
                        'vlsm_country_id'                       => $arr['vl_form'],
                        'facility_id'                           => $facility['facility_id'],
                        'patient_name'                          => $rowData['C'],
                        'patient_id'                            => $rowData['D'],
                        'patient_dob'                           => date('Y-m-d',strtotime($rowData['E'])),
                        'patient_age'                           => $rowData['F'],
                        'patient_gender'                        => strtolower($rowData['G']),
                        'patient_phone_number'                  => $rowData['H'],
                        'patient_address'                       => $rowData['I'],
                        'patient_province'                      => $rowData['J'],
                        'patient_district'                      => $rowData['K'],
                        'patient_city'                          => $rowData['L'],
                        'patient_nationality'                   => $rowData['M'],
                        'type_of_test_requested'                => $rowData['N'],
                        'reason_for_covid19_test'               => $testReason['test_reason_id'],
                        'sample_collection_date'                => $sampleCollectionDate,
                        'specimen_type'                         => $sampleType['sample_id'],
                        'test_number'                           => $rowData['R'],
                        'sample_received_at_vl_lab_datetime'    => $sampleReceivedDate,
                        'lab_id'                                => $labName['facility_id'],
                        'is_sample_rejected'                    => strtolower($rowData['U']),
                        'reason_for_sample_rejection'           => (isset($rejectionReason['rejection_reason_id']) && $rejectionReason['rejection_reason_id'] != "")?$rejectionReason['rejection_reason_id']:9999,
                        'rejection_on'                          => date('Y-m-d',strtotime($rowData['W'])),
                        'result'                                => $result['result_id'],
                        'is_result_authorised'                  => strtolower($rowData['AG']),
                        'authorized_by'                         => ucwords($rowData['AH']),
                        'authorized_on'                         => date('Y-m-d',strtotime($rowData['AI'])),
                        'last_modified_datetime'                => $general->getDateTime(),
                        'last_modified_by'                      => $_SESSION['userId'],
                        'result_status'                         => $resultStatus['status_id'],
                        'sample_condition'                      => strtolower($rowData['AK']),
                        'patient_passport_number'               => $rowData['AL'],
                        'lab_technician'                        => $labTechnician,
                    );
                    // echo "<pre>";print_r($data);die;
                    if (!$sampleCode) {
                        $lastId = $db->insert($tableName, $data);
                    } else {
                        $lastId = $sampleCode['covid19_id'];
                        $db = $db->where('covid19_id', $lastId);
                        $db->update($tableName, $data);
                    }

                    $testData[0]['testRequest']     = $rowData['X'];
                    $testData[0]['testDate']        = $rowData['Y'];
                    $testData[0]['testingPlatform'] = $rowData['Z'];
                    $testData[0]['testResult']      = $rowData['AA'];
                    $testData[1]['testRequest']     = $rowData['AB'];
                    $testData[1]['testDate']        = $rowData['AC'];
                    $testData[1]['testingPlatform'] = $rowData['AD'];
                    $testData[1]['testResult']      = $rowData['AE'];
                    if(count($testData) > 0){
                        /* Delete if already exist */
                        $db = $db->where('covid19_id', $lastId);
                        $db->delete($testTableName);
                        
                        foreach ($testData as $testKitName) {
                            if (trim($testKitName['testDate']) != '') {
                                $testDate = date('Y-m-d H:i', strtotime($testKitName['testDate']));
                            } else {
                                $testDate = null;
                            }
    
                            $covid19TestData = array(
                                'covid19_id'            => $lastId,
                                'test_name'             => $testKitName['testRequest'],
                                'facility_id'           => $labName['facility_id'],
                                'testing_platform'      => $testKitName['testingPlatform'],
                                'sample_tested_datetime'=> date('Y-m-d H:i:s', strtotime($testDate)),
                                'result'                => strtolower($testKitName['testResult']),
                            );
                            $db->insert($testTableName, $covid19TestData);
                            $covid19Data['sample_tested_datetime'] = date('Y-m-d H:i:s', strtotime($testDate));
                        }
                    }
                    $db = $db->where('covid19_id', $lastId);
                    $id = $db->update($tableName, $covid19Data);
                }
            }
        }
    }
    // echo "<pre>";print_r($returnArray);die;
    $_SESSION['alertMsg'] = "Data imported successfully";
    header("location:/covid-19/requests/covid-19-requests.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
