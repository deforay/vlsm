<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
  

$general = new \Vlsm\Models\General();
$tableName = "import_config";
$importMachineTable = "import_config_machines";
$importControlTable = "import_config_controls";
/* echo "<pre>";
print_r($_POST);
die; */
$_POST['configurationName'] = trim($_POST['configurationName']);
try {
    if (!empty($_POST['configurationName'])) {

        if (isset($_POST['supportedTests']) && sizeof($_POST['supportedTests']) > 0) {
            foreach ($_POST['supportedTests'] as $test) {
                $configDir = __DIR__;
                if (!file_exists($configDir)) {
                    mkdir($configDir, 0777, true);
                }
                $configFile = $configDir . DIRECTORY_SEPARATOR . $test . DIRECTORY_SEPARATOR . $_POST['configurationFile'];
                if (!file_exists($configFile)) {
                    $fp = fopen($configFile, 'w');
                    fwrite($fp, '');
                    fclose($fp);
                }
            }
        }
        $matchedTests = array_diff($_POST['userTestType'], $_POST['supportedTests']);
        foreach ($matchedTests as $key => $row) {
            $_POST['reviewedBy'][$key] = "";
            $_POST['approvedBy'][$key] = "";
        }
        $_POST['reviewedBy'] = !empty($_POST['reviewedBy']) ? json_encode(array_combine($_POST['userTestType'], $_POST['reviewedBy'])) : null;
        $_POST['approvedBy'] = !empty($_POST['approvedBy']) ? json_encode(array_combine($_POST['userTestType'], $_POST['approvedBy'])) : null;

        $_POST['supportedTests'] = !empty($_POST['supportedTests']) ? json_encode($_POST['supportedTests']) : null;

        $data = array(
            'machine_name' => $_POST['configurationName'],
            'supported_tests' => $_POST['supportedTests'],
            'import_machine_file_name' => $_POST['configurationFile'],
            'lower_limit' => !empty($_POST['lowerLimit']) ? $_POST['lowerLimit'] : null,
            'higher_limit' => !empty($_POST['higherLimit']) ? $_POST['higherLimit'] : null,
            'max_no_of_samples_in_a_batch' => !empty($_POST['maxNOfSamplesInBatch']) ? $_POST['maxNOfSamplesInBatch'] : null,
            'low_vl_result_text' => !empty($_POST['lowVlResultText']) ? $_POST['lowVlResultText'] : null,
            'reviewed_by' => !empty($_POST['reviewedBy']) ? $_POST['reviewedBy'] : null,
            'approved_by' => !empty($_POST['approvedBy']) ? $_POST['approvedBy'] : null,
            'status' => 'active'
        );
        $id = $db->insert($tableName, $data);
        if ($id > 0 && count($_POST['configMachineName']) > 0) {
            for ($c = 0; $c < count($_POST['configMachineName']); $c++) {
                $pocDev = 'no';
                if (trim($_POST['latitude'][$c]) != '' && trim($_POST['longitude'][$c]) != '') {
                    $pocDev = 'yes';
                }
                if (trim($_POST['configMachineName'][$c]) != '') {
                    $configMachineData = array('config_id' => $id, 'config_machine_name' => $_POST['configMachineName'][$c], 'poc_device' => $pocDev, 'latitude' => $_POST['latitude'][$c], 'longitude' => $_POST['longitude'][$c], 'updated_datetime' => $general->getDateTime());
                    $db->insert($importMachineTable, $configMachineData);
                }
            }
        }

        if ($id > 0 && isset($_POST['testType']) && count($_POST['testType']) > 0) {
            foreach ($_POST['testType'] as $key => $val) {
                if (trim($val) != '') {
                    $configControlData = array('test_type' => $val, 'config_id' => $id, 'number_of_in_house_controls' => $_POST['noHouseCtrl'][$key], 'number_of_manufacturer_controls' => $_POST['noManufacturerCtrl'][$key], 'number_of_calibrators' => $_POST['noCalibrators'][$key]);
                    $db->insert($importControlTable, $configControlData);
                }
            }
        }

        $_SESSION['alertMsg'] = _("Result Import configuration initited for ") . $_POST['configurationName'] . _(". Please proceed to write the import logic in the file ") . $_POST['configurationFile'] . _(" present in import-configs folder");
    }
    error_log($db->getLastError());
    header("location:importConfig.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}