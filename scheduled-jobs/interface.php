<?php

require_once(__DIR__ . "/../startup.php");

require_once(APPLICATION_PATH . '/includes/MysqliDb.php');
//require_once(APPLICATION_PATH . '/models/General.php');
//require_once(APPLICATION_PATH . '/models/Users.php');

if (!isset($interfaceConfig['enabled']) || $interfaceConfig['enabled'] === false) {
    error_log('Interfacing is not enabled. Please enable it in configuration.');
    exit;
}

$vlsmDb  = $db; // assigning to another variable to avoid confusion

$usersModel = new \Vlsm\Models\Users($vlsmDb);

$interfacedb = new MysqliDb(
    $interfaceConfig['dbHost'],
    $interfaceConfig['dbUser'],
    $interfaceConfig['dbPassword'],
    $interfaceConfig['dbName'],
    $interfaceConfig['dbPort']
);



//$general = new \Vlsm\Models\General($vlsmDb);

//$lowVlResults = $general->getLowVLResultTextFromImportConfigs();



//get the value from interfacing DB
$interfaceQuery = "SELECT * FROM orders WHERE result_status = 1 AND lims_sync_status=0";

$interfaceInfo = $interfacedb->query($interfaceQuery);
$numberOfResults = 0;
if (count($interfaceInfo) > 0) {
    foreach ($interfaceInfo as $key => $result) {
        $vlQuery = "SELECT vl_sample_id FROM vl_request_form WHERE sample_code = '" . $result['test_id'] . "'";
        $vlInfo = $db->rawQueryOne($vlQuery);

        if (isset($vlInfo['vl_sample_id'])) {
            $absDecimalVal = null;
            $absVal = null;
            $logVal = null;
            $txtVal = null;
            //set result in result fields
            if (trim($result['results']) != "") {

                $vlResult = trim($result['results']);
                $unit = trim($result['test_unit']);

                if (strpos($unit, 'Log (Copies / mL)') !== false) {
                    if (is_numeric($vlResult)) {
                        $logVal = $vlResult;
                        $vlResult = $absVal = $absDecimalVal = round((float) round(pow(10, $logVal) * 100) / 100);
                    } else {
                        if (strpos($vlResult, "<") !== false) {
                            $logVal = str_replace("<", "", $vlResult);
                            $absDecimalVal = round((float) round(pow(10, $logVal) * 100) / 100);
                            $txtVal = $vlResult = $absVal = "< " . trim($absDecimalVal);
                        } else if (strpos($vlResult, ">") !== false) {
                            $logVal = str_replace(">", "", $vlResult);
                            $absDecimalVal = round((float) round(pow(10, $logVal) * 100) / 100);
                            $txtVal = $vlResult = $absVal = "> " . trim($absDecimalVal);
                        } else {
                            $vlResult = $txtVal = trim($result['results']);
                        }
                    }
                }
                if (strpos($unit, '10') !== false) {
                    $unitArray = explode(".", $unit);
                    $exponentArray = explode("*", $unitArray[0]);
                    $multiplier = pow($exponentArray[0], $exponentArray[1]);
                    $vlResult = $vlResult * $multiplier;
                    $unit = $unitArray[1];
                }

                if (strpos($vlResult, 'E+') !== false || strpos($vlResult, 'E-') !== false) {
                    if (strpos($vlResult, '< 2.00E+1') !== false) {
                        $vlResult = "< 20";
                        //$vlResultCategory = 'Suppressed';
                    } else {
                        $vlResultArray = explode("(", $vlResult);
                        $exponentArray = explode("E", $vlResultArray[0]);
                        $multiplier = pow(10, $exponentArray[1]);
                        $vlResult = round($exponentArray[0] * $multiplier, 2);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal), 2);
                    }
                }

                if (is_numeric($vlResult)) {
                    $absVal = (float) trim($vlResult);
                    $absDecimalVal = (float) trim($vlResult);
                    $logVal = round(log10($absDecimalVal), 2);
                } else {
                    if (strpos($vlResult, "<") !== false) {
                        $vlResult = str_replace("<", "", $vlResult);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal), 2);
                        $absVal = "< " . (float) trim($vlResult);
                    } else if (strpos($vlResult, ">") !== false) {
                        $vlResult = str_replace(">", "", $vlResult);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal), 2);
                        $absVal = "> " . (float) trim($vlResult);
                    } else {
                        $txtVal = trim($result['results']);
                    }
                }
            }

            $userId = $usersModel->addUserIfNotExists($result['tested_by']);

            $data = array(
                'tested_by' => $userId,
                'result_approved_by' => $userId,
                'result_approved_datetime' => $result['authorised_date_time'],
                'sample_tested_datetime' => $result['result_accepted_date_time'],
                'result_value_log' => $logVal,
                'result_value_absolute' => $absVal,
                'result_value_absolute_decimal' => $absDecimalVal,
                'result_value_text' => $txtVal,
                'result' => $vlResult,
                'vl_test_platform' => $result['machine_used'],
                'result_status' => 7,
                'data_sync' => 0
            );

            $db = $db->where('vl_sample_id', $vlInfo['vl_sample_id']);
            $vlUpdateId = $db->update('vl_request_form', $data);
            $numberOfResults++;
            if ($vlUpdateId) {
                $interfaceData = array(
                    'lims_sync_status' => 1,
                    'lims_sync_date_time' => date('Y-m-d H:i:s'),
                );
                $interfacedb = $interfacedb->where('id', $result['id']);
                $interfaceUpdateId = $interfacedb->update('orders', $interfaceData);
            }
        } else {

            $eidQuery = "SELECT eid_id FROM eid_form WHERE sample_code = '" . $result['test_id'] . "'";
            $eidInfo = $db->rawQueryOne($eidQuery);
            if (isset($eidInfo['eid_id'])) {

                $absDecimalVal = null;
                $absVal = null;
                $logVal = null;
                $txtVal = null;
                //set result in result fields
                if (trim($result['results']) != "") {

                    if (strpos(strtolower($result['results']), 'not detected') !== false) {
                        $eidResult = 'negative';
                    } else if ((strpos(strtolower($result['results']), 'detected') !== false) || (strpos(strtolower($result['results']), 'passed') !== false)) {
                        $eidResult = 'positive';
                    } else {
                        $eidResult = 'indeterminate';
                    }
                }

                $data = array(
                    'tested_by' => $result['tested_by'],
                    'result_approved_by' => $result['tested_by'],
                    'result_approved_datetime' => $result['authorised_date_time'],
                    'sample_tested_datetime' => $result['result_accepted_date_time'],
                    'result' => $eidResult,
                    'eid_test_platform' => $result['machine_used'],
                    'result_status' => 7,
                    'data_sync' => 0
                );

                $db = $db->where('eid_id', $eidInfo['eid_id']);
                $eidUpdateId = $db->update('eid_form', $data);
                $numberOfResults++;
                if ($eidUpdateId) {
                    $interfaceData = array(
                        'lims_sync_status' => 1,
                        'lims_sync_date_time' => date('Y-m-d H:i:s'),
                    );
                    $interfacedb = $interfacedb->where('id', $result['id']);
                    $interfaceUpdateId = $interfacedb->update('orders', $interfaceData);
                }
            } else {
                $interfaceData = array(
                    'lims_sync_status' => 2,
                    'lims_sync_date_time' => date('Y-m-d H:i:s'),
                );
                $interfacedb = $interfacedb->where('id', $result['id']);
                $interfaceUpdateId = $interfacedb->update('orders', $interfaceData);
            }
        }
    }

    if ($numberOfResults > 0) {
        $importedBy = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'AUTO';
        $general = new \Vlsm\Models\General($vlsmDb);
        $general->resultImportStats($numberOfResults, 'interface', $importedBy);
    }
}
