<?php

require_once(__DIR__ . "/../startup.php");

require_once(APPLICATION_PATH.'/includes/MysqliDb.php');
require_once(APPLICATION_PATH. '/models/General.php');

if(!isset($interfaceConfig['enabled']) || $interfaceConfig['enabled'] === false){  
    error_log('Interfacing is not enabled. Please enable it in configuration.');
    exit;
}

$vlsmDb  = $db; // assigning to another variable to avoid confusion

$interfacedb = new MysqliDb($interfaceConfig['dbHost'], 
                            $interfaceConfig['dbUser'], 
                            $interfaceConfig['dbPassword'],  
                            $interfaceConfig['dbName'], 
                            $interfaceConfig['dbPort']);



//$general = new General($vlsmDb);

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

                $vlResult = $result['results'];
                $unit = trim($result['test_unit']);

                if (strpos($unit, '10') !== false) {
                    $unitArray = explode(".", $unit);
                    $exponentArray = explode("*", $unitArray[0]);
                    $multiplier = pow($exponentArray[0], $exponentArray[1]);
                    $vlResult = $vlResult * $multiplier;
                    $unit = $unitArray[1];
                }
                
                if (strpos($vlResult, 'E') !== false) {
                    if (strpos($vlResult, '< 2.00E+1') !== false) {
                        $vlResult = "< 20";
                        //$vlResultCategory = 'Suppressed';
                    }else{
                        $vlResultArray = explode("(", $vlResult);
                        $exponentArray = explode("E", $vlResultArray[0]);
                        $multiplier = pow(10, $exponentArray[1]);
                        $vlResult = $exponentArray[0] * $multiplier;
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal),2);                        
                    }
                }                

                if (is_numeric($vlResult)) {
                    $absVal = (float) trim($vlResult);
                    $absDecimalVal = (float) trim($vlResult);
                    $logVal = round(log10($absDecimalVal),2);
                } else {
                    if (strpos("<", $vlResult) !== false) {
                        $vlResult = str_replace("<", "", $vlResult);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal),2);
                        $absVal = "< " . (float) trim($vlResult);
                    } else if (strpos(">", $vlResult) !== false) {
                        $vlResult = str_replace(">", "", $vlResult);
                        $absDecimalVal = (float) trim($vlResult);
                        $logVal = round(log10($absDecimalVal),2);
                        $absVal = "> " . (float) trim($vlResult);
                    } else {
                        $txtVal = trim($result['results']);
                    }
                }
            }

            $data = array(
                'result_approved_by' => $result['tested_by'],
                'result_approved_datetime' => $result['authorised_date_time'],
                'sample_tested_datetime' => $result['result_accepted_date_time'],
                'result_value_log' => $logVal,
                'result_value_absolute' => $absVal,
                'result_value_absolute_decimal' => $absDecimalVal,
                'result_value_text' => $txtVal,
                'result' => $vlResult,
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
        }else{
            $interfaceData = array(
                'lims_sync_status' => 2,
                'lims_sync_date_time' => date('Y-m-d H:i:s'),
            );
            $interfacedb = $interfacedb->where('id', $result['id']);
            $interfaceUpdateId = $interfacedb->update('orders', $interfaceData);            
        }
    }

    if($numberOfResults > 0){
        $importedBy = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'AUTO';
        $general = new General($vlsmDb);
        $general->resultImportStats($numberOfResults, 'interface' , $importedBy);
    }

}