<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\GenericTestsService;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$generic = ContainerRegistry::get(GenericTestsService::class);

$tableName = "r_test_types";
$tableName2 = "generic_test_sample_type_map";
$tableName3 = "generic_test_reason_map";
$tableName4 = "generic_test_symptoms_map";
$tableName5 = "generic_test_failure_reason_map";
$tableName6 = "generic_sample_rejection_reason_map";
$tableName7 = "generic_test_result_units_map";
$testAttribute = [];
$_POST['testStandardName'] = trim($_POST['testStandardName']);
$i=0;
foreach($_POST['fdropDown'] as $val)
{
    $_POST['fdropDown'][$i] = substr($val, 0, -1);
    $i++;
}

try {
   // echo '<pre>'; print_r($_POST['resultConfig']['test_result_unit']); die;
    if (!empty($_POST['testStandardName'])) {
        $testAttribute['field_id'] = $_POST['fieldId'];
        $testAttribute['field_name'] = $_POST['fieldName'];
        $testAttribute['field_type'] = $_POST['fieldType'];
        $testAttribute['drop_down'] = $_POST['fdropDown'];
        $testAttribute['mandatory_field'] = $_POST['mandatoryField'];
        $testAttribute['section'] = $_POST['section'];
        $testAttribute['section_other'] = $_POST['sectionOther'];

        $data = array(
            'test_standard_name' => $_POST['testStandardName'],
            'test_generic_name' => $_POST['testGenericName'],
            'test_short_code' => $_POST['testShortCode'],
            'test_loinc_code' => !empty($_POST['testLoincCode']) ? $_POST['testLoincCode'] : null,
            'test_form_config' => json_encode($testAttribute),
            'test_results_config' => json_encode($_POST['resultConfig']),
            'test_status' => $_POST['status']
        );

        $id = $db->insert($tableName, $data);
        $lastId = $db->getInsertId();
        if ($lastId != 0) {

            if (!empty($_POST['sampleType'])) {
                foreach ($_POST['sampleType'] as $val) {
                    $value = array('sample_type_id' => $val, 'test_type_id' => $lastId);
                    $db->insert($tableName2, $value);
                }
            }

            if (!empty($_POST['testingReason'])) {
                foreach ($_POST['testingReason'] as $val) {
                    if(!is_numeric($val)){
                        $val = $generic->quickInsert('r_generic_test_reasons', array('test_reason_code', 'test_reason', 'test_reason_status'), array($general->generateRandomString(5), $val, 'active'));
                    }
                    $value = array('test_reason_id' => $val, 'test_type_id' => $lastId);
                    $db->insert($tableName3, $value);
                }
            }

            if (!empty($_POST['symptoms'])) {
                foreach ($_POST['symptoms'] as $val) {
                    $value = array('symptom_id' => $val, 'test_type_id' => $lastId);
                    $db->insert($tableName4, $value);
                }
            }

            if (!empty($_POST['testFailureReason'])) {
                foreach ($_POST['testFailureReason'] as $val) {
                    if(!is_numeric($val)){
                        $val = $generic->quickInsert('r_generic_test_failure_reasons', array('test_failure_reason_code', 'test_failure_reason', 'test_failure_reason_status'), array($general->generateRandomString(5), $val, 'active'));
                    }
                    $value = array('test_failure_reason_id' => $val, 'test_type_id' => $lastId);
                    $db->insert($tableName5, $value);
                }
            }

            if (!empty($_POST['rejectionReason'])) {
                foreach ($_POST['rejectionReason'] as $val) {
                    if(!is_numeric($val)){
                        $val = $generic->quickInsert('r_generic_sample_rejection_reasons', array('rejection_reason_code', 'rejection_reason_name', 'rejection_reason_status'), array($general->generateRandomString(5), $val, 'active'));
                    }
                    $value = array('rejection_reason_id' => $val, 'test_type_id' => $lastId);
                    $db->insert($tableName6, $value);
                }
            }

            if (!empty($_POST['resultConfig']['test_result_unit'])) {
                foreach ($_POST['resultConfig']['test_result_unit'] as $val) {
                    $value = array('unit_id' => $val, 'test_type_id' => $lastId);
                    $db->insert($tableName7, $value);
                }
            }
        }
        $_SESSION['alertMsg'] = _("Test type added successfully");
    }
    //error_log($db->getLastError());
    header("Location:test-type.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
