<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Services\VlService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var VlService $vlService */
$vlService = ContainerRegistry::get(VlService::class);
$tableName = "form_generic";
$testTableName = "generic_test_results";
$vl_result_category = null;
$logVal = null;
$absDecimalVal = null;
$absVal = null;
$txtVal = null;
try {
    //var_dump($_POST);die;
    $instanceId = '';
    if (isset($_SESSION['instanceId'])) {
        $instanceId = $_SESSION['instanceId'];
    }
    $testingPlatform = '';
    if (isset($_POST['testPlatform']) && trim((string) $_POST['testPlatform']) != '') {
        $platForm = explode("##", (string) $_POST['testPlatform']);
        $testingPlatform = $platForm[0];
    }
    if (isset($_POST['sampleReceivedDate']) && trim((string) $_POST['sampleReceivedDate']) != "") {
        $sampleReceivedDateLab = explode(" ", (string) $_POST['sampleReceivedDate']);
        $_POST['sampleReceivedDate'] = DateUtility::isoDateFormat($sampleReceivedDateLab[0]) . " " . $sampleReceivedDateLab[1];
    } else {
        $_POST['sampleReceivedDate'] = null;
    }


    if (isset($_POST['sampleReceivedAtHubOn']) && trim((string) $_POST['sampleReceivedAtHubOn']) != "") {
        $sampleReceivedAtHubOn = explode(" ", (string) $_POST['sampleReceivedAtHubOn']);
        $_POST['sampleReceivedAtHubOn'] = DateUtility::isoDateFormat($sampleReceivedAtHubOn[0]) . " " . $sampleReceivedAtHubOn[1];
    } else {
        $_POST['sampleReceivedAtHubOn'] = null;
    }

    if (isset($_POST['approvedOn']) && trim((string) $_POST['approvedOn']) != "") {
        $approvedOn = explode(" ", (string) $_POST['approvedOn']);
        $_POST['approvedOn'] = DateUtility::isoDateFormat($approvedOn[0]) . " " . $approvedOn[1];
    } else {
        $_POST['approvedOn'] = null;
    }


    if (isset($_POST['sampleTestingDateAtLab']) && trim((string) $_POST['sampleTestingDateAtLab']) != "") {
        $sampleTestingDateAtLab = explode(" ", (string) $_POST['sampleTestingDateAtLab']);
        $_POST['sampleTestingDateAtLab'] = DateUtility::isoDateFormat($sampleTestingDateAtLab[0]) . " " . $sampleTestingDateAtLab[1];
    } else {
        $_POST['sampleTestingDateAtLab'] = null;
    }
    if (isset($_POST['resultDispatchedOn']) && trim((string) $_POST['resultDispatchedOn']) != "") {
        $resultDispatchedOn = explode(" ", (string) $_POST['resultDispatchedOn']);
        $_POST['resultDispatchedOn'] = DateUtility::isoDateFormat($resultDispatchedOn[0]) . " " . $resultDispatchedOn[1];
    } else {
        $_POST['resultDispatchedOn'] = null;
    }

    if (isset($_POST['newRejectionReason']) && trim((string) $_POST['newRejectionReason']) != "") {
        $rejectionReasonQuery = "SELECT rejection_reason_id FROM r_generic_sample_rejection_reasons where rejection_reason_name='" . $_POST['newRejectionReason'] . "' OR rejection_reason_name='" . strtolower((string) $_POST['newRejectionReason']) . "' OR rejection_reason_name='" . (strtolower((string) $_POST['newRejectionReason'])) . "'";
        $rejectionResult = $db->rawQuery($rejectionReasonQuery);
        if (!isset($rejectionResult[0]['rejection_reason_id'])) {
            $data = array(
                'rejection_reason_name' => $_POST['newRejectionReason'],
                'rejection_type' => 'general',
                'rejection_reason_status' => 'active'
            );
            $id = $db->insert('r_generic_sample_rejection_reasons', $data);
            $_POST['rejectionReason'] = $id;
        } else {
            $_POST['rejectionReason'] = $rejectionResult[0]['rejection_reason_id'];
        }
    }

    $isRejected = false;
    $resultStatus = SAMPLE_STATUS\PENDING_APPROVAL; // Awaiting Approval
    if (($_POST['isSampleRejected'] ?? null) === 'yes') {
        $isRejected = true;
        $resultStatus = SAMPLE_STATUS\REJECTED; // Rejected
    }

    $reasonForChanges = '';
    $allChange = '';
    if (isset($_POST['reasonForResultChangesHistory']) && $_POST['reasonForResultChangesHistory'] != '') {
        $allChange = $_POST['reasonForResultChangesHistory'];
    }
    if (isset($_POST['reasonForResultChanges']) && trim((string) $_POST['reasonForResultChanges']) != '') {
        $reasonForChanges = $_SESSION['userName'] . '##' . $_POST['reasonForResultChanges'] . '##' . DateUtility::getCurrentDateTime();
    }
    if (!empty($allChange) && !empty($reasonForChanges)) {
        $allChange = $reasonForChanges . 'vlsm' . $allChange;
    } elseif (trim($reasonForChanges) != '') {
        $allChange = $reasonForChanges;
    }
    if (isset($_POST['reviewedOn']) && trim((string) $_POST['reviewedOn']) != "") {
        $reviewedOn = explode(" ", (string) $_POST['reviewedOn']);
        $_POST['reviewedOn'] = DateUtility::isoDateFormat($reviewedOn[0]) . " " . $reviewedOn[1];
    } else {
        $_POST['reviewedOn'] = null;
    }

    $interpretationResult = null;
    /* if(isset($_POST['resultType']) && isset($_POST['testType']) && !empty($_POST['resultType']) && !empty($_POST['testType'])){
        $interpretationResult = $genericTestsService->getInterpretationResults($_POST['testType'], $_POST['result']);
    } */
    if (!empty($_POST['resultInterpretation'])) {
        $interpretationResult = $_POST['resultInterpretation'];
    }

    $vldata = array(
        'vlsm_instance_id' => $instanceId,
        'lab_id' => (isset($_POST['labId']) && $_POST['labId'] != '') ? $_POST['labId'] : null,
        'test_platform' => $testingPlatform,
        'sample_received_at_hub_datetime' => $_POST['sampleReceivedAtHubOn'],
        'sample_received_at_testing_lab_datetime' => $_POST['sampleReceivedDate'],
        'sample_tested_datetime' => $_POST['sampleTestingDateAtLab'],
        'reason_for_testing' => (isset($_POST['reasonForTesting']) && $_POST['reasonForTesting'] != '') ? $_POST['reasonForTesting'] : null,
        'result_dispatched_datetime' => !empty($_POST['resultDispatchedOn']) ? $_POST['resultDispatchedOn'] : null,
        'is_sample_rejected' => (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] != '') ? $_POST['isSampleRejected'] : null,
        'reason_for_sample_rejection' => (isset($_POST['rejectionReason']) && $_POST['rejectionReason'] != '') ? $_POST['rejectionReason'] : null,
        'rejection_on' => (!empty($_POST['rejectionDate'])) ? DateUtility::isoDateFormat($_POST['rejectionDate']) : null,
        'result' => $_POST['result'] ?: null,
        'final_result_interpretation' => $interpretationResult,
        'result_reviewed_by' => (isset($_POST['reviewedBy']) && $_POST['reviewedBy'] != "") ? $_POST['reviewedBy'] : "",
        'result_reviewed_datetime' => (isset($_POST['reviewedOn']) && $_POST['reviewedOn'] != "") ? $_POST['reviewedOn'] : null,
        'testing_lab_focal_person' => (isset($_POST['vlFocalPerson']) && $_POST['vlFocalPerson'] != '') ? $_POST['vlFocalPerson'] : null,
        'testing_lab_focal_person_phone_number' => (isset($_POST['vlFocalPersonPhoneNumber']) && $_POST['vlFocalPersonPhoneNumber'] != '') ? $_POST['vlFocalPersonPhoneNumber'] : null,
        'tested_by' => (isset($_POST['testedBy']) && $_POST['testedBy'] != '') ? $_POST['testedBy'] : null,
        'result_approved_by' => (isset($_POST['approvedBy']) && $_POST['approvedBy'] != '') ? $_POST['approvedBy'] : null,
        'result_approved_datetime' => (isset($_POST['approvedBy']) && $_POST['approvedBy'] != '') ? $_POST['approvedOn'] : null,
        'lab_tech_comments' => (isset($_POST['labComments']) && trim((string) $_POST['labComments']) != '') ? trim((string) $_POST['labComments']) : null,
        'reason_for_test_result_changes' => $allChange,
        'revised_by' => (isset($_POST['revised']) && $_POST['revised'] == "yes") ? $_SESSION['userId'] : null,
        'revised_on' => (isset($_POST['revised']) && $_POST['revised'] == "yes") ? DateUtility::getCurrentDateTime() : null,
        'last_modified_by' => $_SESSION['userId'],
        'last_modified_datetime' => DateUtility::getCurrentDateTime(),
        'manual_result_entry' => 'yes',
        'result_status' => $resultStatus,
        'data_sync' => 0,
        'sub_tests' => implode("##", $_POST['subTestResult']),
        'result_printed_datetime' => null
    );


    if (isset($_POST['isSampleRejected']) && $_POST['isSampleRejected'] == 'yes') {
        $vldata['result_status'] = SAMPLE_STATUS\REJECTED;
    }

    if (isset($_POST['vlSampleId']) && $_POST['vlSampleId'] != '' && ($_POST['isSampleRejected'] == 'no' || $_POST['isSampleRejected'] == '')) {
        if (!empty($_POST['testName'])) {
            $db->where('generic_id', $_POST['vlSampleId']);
            $db->delete($testTableName);
            foreach ($_POST['testName'] as $subTestName => $subTests) {
                foreach ($subTests as $testKey => $testKitName) {
                    if (!empty($testKitName)) {
                        $testData = array(
                            'generic_id' => $_POST['vlSampleId'],
                            'sub_test_name' => $subTestName,
                            'result_type' => $_POST['resultType'][$subTestName],
                            'test_name' => ($testKitName == 'other') ? $_POST['testNameOther'][$subTestName][$testKey] : $testKitName,
                            'facility_id' => $_POST['labId'] ?? null,
                            'sample_tested_datetime' => DateUtility::isoDateFormat($_POST['testDate'][$subTestName][$testKey] ?? '', true),
                            'testing_platform' => $_POST['testingPlatform'][$subTestName][$testKey] ?? null,
                            'kit_lot_no' => (str_contains((string)$testKitName, 'RDT')) ? $_POST['lotNo'][$subTestName][$testKey] : null,
                            'kit_expiry_date' => (str_contains((string)$testKitName, 'RDT')) ? DateUtility::isoDateFormat($_POST['expDate'][$subTestName][$testKey]) : null,
                            'result_unit' => $_POST['testResultUnit'][$subTestName][$testKey],
                            'result' => $_POST['testResult'][$subTestName][$testKey],
                            'final_result' => $_POST['finalResult'][$subTestName],
                            'final_result_unit' => $_POST['finalTestResultUnit'][$subTestName],
                            'final_result_interpretation' => $_POST['resultInterpretation'][$subTestName]
                        );
                        $db->insert('generic_test_results', $testData);
                    }
                }
            }
        }
    } else {
        $db->where('generic_id', $_POST['vlSampleId']);
        $db->delete($testTableName);
        $covid19Data['sample_tested_datetime'] = null;
    }

    $db->where('sample_id', $_POST['vlSampleId']);
    $id = $db->update($tableName, $vldata);
    //var_dump($db->getLastError());die;
    if ($id === true) {
        $_SESSION['alertMsg'] = _translate("Lab Tests results updated successfully");
    } else {
        $_SESSION['alertMsg'] = _translate("Please try again later");
    }

    header("Location:generic-test-results.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
