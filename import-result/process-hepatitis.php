<?php

// this file is included in /import-result/procesImportedResults.php

$fileName = null;
$importedBy = $_SESSION['userId'];

try {
    $numberOfResults = 0;

    $arr = $general->getGlobalConfig();
    $printSampleCode = array();

    $importNonMatching = (isset($arr['import_non_matching_sample']) && $arr['import_non_matching_sample'] == 'no') ? false : true;
    $instanceQuery = "SELECT * FROM s_vlsm_instance";
    $instanceResult = $db->query($instanceQuery);
    $result = '';
    $id = explode(",", $_POST['value']);
    $status = explode(",", $_POST['status']);
    $rejectedReasonId = explode(",", $_POST['rejectReasonId']);
    if ($_POST['value'] != '') {
        for ($i = 0; $i < count($id); $i++) {
            $sQuery = "SELECT * FROM temp_sample_import WHERE imported_by ='$importedBy' AND temp_sample_id='" . $id[$i] . "'";
            $rResult = $db->rawQuery($sQuery);
            $fileName = $rResult[0]['import_machine_file_name'];

            if (isset($rResult[0]['approver_comments']) && $rResult[0]['approver_comments'] != "") {
                $comments = $rResult[0]['approver_comments']; //
                if ($_POST['comments'] != "") {
                    $comments .= " - " . $_POST['comments'];
                }
            } else {
                $comments = $_POST['comments'];
            }

            if ($rResult[0]['sample_type'] != 'S' && $rResult[0]['sample_type'] != 's') {
                $data = array(
                    'control_code'                  => $rResult[0]['sample_code'],
                    'lab_id'                        => $rResult[0]['lab_id'],
                    'control_type'                  => $rResult[0]['sample_type'],
                    'lot_number'                    => $rResult[0]['lot_number'],
                    'lot_expiration_date'           => $rResult[0]['lot_expiration_date'],
                    'sample_tested_datetime'        => !empty($rResult[0]['sample_tested_datetime']) ? $rResult[0]['sample_tested_datetime'] : $general->getDateTime(),
                    'result'                        => $rResult[0]['result'],
                    'tested_by'                     => $_POST['testBy'],
                    'approver_comments'             => $comments,
                    'result_reviewed_by'            => $rResult[0]['result_reviewed_by'],
                    'result_reviewed_datetime'      => $general->getDateTime(),
                    'result_approved_by'            => $_POST['appBy'],
                    'result_approved_datetime'      => $general->getDateTime(),
                    'vlsm_country_id'               => $arr['vl_form'],
                    'import_machine_file_name'                     => $rResult[0]['import_machine_file_name'],
                    'imported_date_time'            => $rResult[0]['result_imported_datetime'],
                );
                if ($status[$i] == 4) {
                    $data['is_sample_rejected'] = 'yes';
                    $data['reason_for_sample_rejection'] = $rejectedReasonId[$i];
                    $data['hbv_vl_count'] = null;
                    $data['hcv_vl_count'] = null;
                } else {
                    $data['is_sample_rejected'] = 'no';
                }
                $data['status'] = $status[$i];

                $bquery = "SELECT * FROM batch_details WHERE batch_code='" . $rResult[0]['batch_code'] . "'";
                $bvlResult = $db->rawQuery($bquery);
                if ($bvlResult) {
                    $data['batch_id'] = $bvlResult[0]['batch_id'];
                } else {
                    $batchResult = $db->insert('batch_details', array('test_type' => 'vl', 'batch_code' => $rResult[0]['batch_code'], 'batch_code_key' => $rResult[0]['batch_code_key'], 'sent_mail' => 'no', 'request_created_datetime' => $general->getDateTime()));
                    $data['batch_id'] = $db->getInsertId();
                }

                $db->insert('vl_imported_controls', $data);
            } else {

                $data = array(
                    'result_reviewed_datetime' => $rResult[0]['result_reviewed_datetime'],
                    'result_reviewed_by' => $_POST['reviewedBy'],
                    'hepatitis_test_platform' => $rResult[0]['vl_test_platform'],
                    'import_machine_name' => $rResult[0]['import_machine_name'],
                    'approver_comments' => $comments,
                    'lot_number' => $rResult[0]['lot_number'],
                    'lot_expiration_date' => $rResult[0]['lot_expiration_date'],
                    'sample_tested_datetime'        => !empty($rResult[0]['sample_tested_datetime']) ? $rResult[0]['sample_tested_datetime'] : $general->getDateTime(),
                    'lab_id' => $rResult[0]['lab_id'],
                    'import_machine_file_name' => $rResult[0]['import_machine_file_name'],
                    'manual_result_entry' => 'no',
                );
                if ($status[$i] == '1') {
                    $data['result_reviewed_by'] = $_POST['reviewedBy'];
                    $data['facility_id'] = $rResult[0]['facility_id'];
                    $data['sample_code'] = $rResult[0]['sample_code'];
                    $data['batch_code'] = $rResult[0]['batch_code'];
                    $data['sample_type'] = $rResult[0]['sample_type'];
                    $data['status'] = $status[$i];
                    $data['import_batch_tracking'] = $_SESSION['controllertrack'];
                    $result = $db->insert('hold_sample_import', $data);
                } else {
                    $data['tested_by'] = $_POST['testBy'];
                    $data['sample_tested_datetime'] = $rResult[0]['sample_tested_datetime'];
                    $data['request_created_by'] = $rResult[0]['result_reviewed_by'];
                    $data['request_created_datetime'] = $general->getDateTime();
                    $data['last_modified_by'] = $rResult[0]['result_reviewed_by'];
                    $data['last_modified_datetime'] = $general->getDateTime();
                    $data['result_approved_by'] = $_POST['appBy'];
                    $data['result_approved_datetime'] = $general->getDateTime();
                    $sampleVal = $rResult[0]['sample_code'];

                    $query = "SELECT hepatitis_id, hcv_vl_count, hbv_vl_count,hepatitis_test_type, result_status FROM form_hepatitis WHERE sample_code='" . $sampleVal . "'";
                    $hepResult = $db->rawQuery($query);


                    $testType = strtolower($hepResult['hepatitis_test_type']);
                    if ($testType == 'hbv') {
                        $resultField = "hbv_vl_count";
                        $otherField = "hcv_vl_count";
                    } else if ($testType == 'hcv') {
                        $resultField = "hcv_vl_count";
                        $otherField = "hbv_vl_count";
                    }

                    if ($status[$i] == '4') {
                        $data['is_sample_rejected'] = 'yes';
                        $data['reason_for_sample_rejection'] = $rejectedReasonId[$i];
                        $data[$resultField] = null;
                        $data[$otherField] = null;
                    } else {

                        $data['is_sample_rejected'] = 'no';
                        $data['reason_for_sample_rejection'] = null;
                        $data[$otherField] = null;
                        if (!empty(trim($rResult[0]['result_value_text'])) && $rResult[0]['result_value_text'] != '') {
                            $data[$resultField] = $rResult[0]['result_value_text'];
                        } else if ($rResult[0]['result_value_absolute'] != '') {
                            $data[$resultField] = $rResult[0]['result_value_absolute'];
                        } else if ($rResult[0]['result_value_log'] != '') {
                            $data[$resultField] = $rResult[0]['result_value_log'];
                        }
                    }
                    //get bacth code
                    $bquery = "SELECT * FROM batch_details WHERE batch_code='" . $rResult[0]['batch_code'] . "'";
                    $bvlResult = $db->rawQuery($bquery);
                    if ($bvlResult) {
                        $data['sample_batch_id'] = $bvlResult[0]['batch_id'];
                    } else {
                        $batchResult = $db->insert('batch_details', array('batch_code' => $rResult[0]['batch_code'], 'batch_code_key' => $rResult[0]['batch_code_key'], 'sent_mail' => 'no', 'request_created_datetime' => $general->getDateTime()));
                        $data['sample_batch_id'] = $db->getInsertId();
                    }

                    $data['result_status'] = $status[$i];
                    $data['sample_code'] = $rResult[0]['sample_code'];


                    if (count($hepResult) > 0) {
                        $data['vlsm_country_id'] = $arr['vl_form'];
                        $data['data_sync'] = 0;

                        $db = $db->where('sample_code', $rResult[0]['sample_code']);
                        $result = $db->update('form_hepatitis', $data);
                    } else {
                        if ($importNonMatching == false) continue;
                        $data['sample_code'] = $rResult[0]['sample_code'];
                        $data['vlsm_country_id'] = $arr['vl_form'];
                        $data['vlsm_instance_id'] = $instanceResult[0]['vlsm_instance_id'];
                        $db->insert('form_hepatitis', $data);
                    }
                    $printSampleCode[] = "'" . $rResult[0]['sample_code'] . "'";
                }
            }
            $db = $db->where('temp_sample_id', $id[$i]);
            $result = $db->update('temp_sample_import', array('temp_sample_status' => 1));
        }
        if (file_exists(TEMP_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $rResult[0]['import_machine_file_name'])) {
            copy(TEMP_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $rResult[0]['import_machine_file_name'], UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $rResult[0]['import_machine_file_name']);
        }
    }
    //get all accepted data result
    $accQuery = "SELECT * FROM temp_sample_import as tsr LEFT JOIN form_hepatitis as vl ON vl.sample_code=tsr.sample_code where  imported_by ='$importedBy' AND tsr.result_status='7'";
    $accResult = $db->rawQuery($accQuery);
    if ($accResult) {
        for ($i = 0; $i < count($accResult); $i++) {


            $query = "SELECT hepatitis_id, hcv_vl_count, hbv_vl_count,hepatitis_test_type, result_status FROM form_hepatitis WHERE sample_code='" . $accResult[$i]['sample_code'] . "'";
            $hepResult = $db->rawQuery($query);

            $testType = strtolower($hepResult['hepatitis_test_type']);
            if ($testType == 'hbv') {
                $resultField = "hbv_vl_count";
                $otherField = "hcv_vl_count";
            } else if ($testType == 'hcv') {
                $resultField = "hcv_vl_count";
                $otherField = "hbv_vl_count";
            }

            $data = array(
                // 'lab_name' => $accResult[$i]['lab_name'],
                // 'lab_contact_person' => $accResult[$i]['lab_contact_person'],
                // 'lab_phone_number' => $accResult[$i]['lab_phone_number'],
                //'sample_received_at_vl_lab_datetime' => $accResult[$i]['sample_received_at_vl_lab_datetime'],
                //'result_reviewed_datetime' => $accResult[$i]['result_reviewed_datetime'],
                'result_reviewed_by' => $_POST['reviewedBy'],
                'approver_comments' => $_POST['comments'],
                'lot_number' => $accResult[$i]['lot_number'],
                'lot_expiration_date' => $accResult[$i]['lot_expiration_date'],
                'sample_tested_datetime' => $accResult[$i]['sample_tested_datetime'],
                'lab_id' => $accResult[$i]['lab_id'],
                'tested_by'                     => $_POST['testBy'],
                'request_created_by' => $accResult[$i]['result_reviewed_by'],
                'request_created_datetime' => $general->getDateTime(),
                'last_modified_datetime' => $general->getDateTime(),
                'result_approved_by' => $_POST['appBy'],
                'result_approved_datetime' => $general->getDateTime(),
                'import_machine_file_name' => $accResult[$i]['import_machine_file_name'],
                'manual_result_entry' => 'no',
                //'result_status'=>'7',
                'hepatitis_test_platform' => $accResult[$i]['vl_test_platform'],
                'import_machine_name' => $accResult[$i]['import_machine_name'],
            );

            if ($accResult[$i]['result_status'] == '4') {
                $data['is_sample_rejected'] = 'yes';
                $data['reason_for_sample_rejection'] = $rejectedReasonId[$i];
            } else {
                $data['result_status'] = $status[$i];
                $data['is_sample_rejected'] = 'no';
                $data['reason_for_sample_rejection'] = null;
                $data[$otherField] = null;
                if (!empty(trim($accResult[$i]['result_value_text'])) && $accResult[$i]['result_value_text'] != '') {
                    $data[$resultField] = trim($accResult[$i]['result_value_text']);
                } else if ($accResult[$i]['result_value_absolute'] != '') {
                    $data[$resultField] = $accResult[$i]['result_value_absolute'];
                } else if ($accResult[$i]['result_value_log'] != '') {
                    $data[$resultField] = $accResult[$i]['result_value_log'];
                }
            }
            //get bacth code
            $bquery = "SELECT * FROM batch_details WHERE batch_code='" . $accResult[$i]['batch_code'] . "'";
            $bvlResult = $db->rawQuery($bquery);
            if ($bvlResult) {
                $data['sample_batch_id'] = $bvlResult[0]['batch_id'];
            } else {
                $batchResult = $db->insert('batch_details', array('batch_code' => $accResult[$i]['batch_code'], 'batch_code_key' => $accResult[$i]['batch_code_key'], 'sent_mail' => 'no', 'request_created_datetime' => $general->getDateTime()));
                $data['sample_batch_id'] = $db->getInsertId();
            }
            $data['data_sync'] = 0;
            $db = $db->where('sample_code', $accResult[$i]['sample_code']);
            $result = $db->update('form_hepatitis', $data);

            $numberOfResults++;

            $printSampleCode[] = "'" . $accResult[$i]['sample_code'] . "'";
            if (file_exists(TEMP_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name']) && !is_dir(TEMP_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name'])) {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-result")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-result", 0777, true);
                }
                copy(TEMP_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name'], UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name']);
            }
            $db = $db->where('temp_sample_id', $accResult[$i]['temp_sample_id']);
            $result = $db->update('temp_sample_import', array('temp_sample_status' => 1));
        }
    }
    $sCode = implode(', ', $printSampleCode);
    $samplePrintQuery = "SELECT vl.*,s.sample_name,b.*,ts.*,f.facility_name,l_f.facility_name as labName,f.facility_code,f.facility_state,f.facility_district, u_d.user_name as reviewedBy,a_u_d.user_name as approvedBy ,rs.rejection_reason_name FROM form_hepatitis as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id LEFT JOIN r_hepatitis_sample_type as s ON s.sample_id=vl.sample_type INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by LEFT JOIN r_hepatitis_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection";
    $samplePrintQuery .= ' where vl.sample_code IN ( ' . $sCode . ')'; // Append to condition
    $_SESSION['hepatitisPrintSearchResultQuery'] = $samplePrintQuery;
    $stQuery = "SELECT * FROM temp_sample_import as tsr LEFT JOIN form_hepatitis as vl ON vl.sample_code=tsr.sample_code where imported_by ='$importedBy' AND tsr.sample_type='s'";
    $stResult = $db->rawQuery($stQuery);

    if ($numberOfResults > 0) {
        $importedBy = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'AUTO';
        $general->resultImportStats($numberOfResults, $fileName, $importedBy);
    }
    echo "importedStatistics.php";
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
