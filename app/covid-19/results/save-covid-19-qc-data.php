<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;




/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$tableName = "qc_covid19";
$tableName1 = "qc_covid19_tests";
$primaryKey = "qc_id";
$primaryKey1 = "qc_test_id";

//var_dump($_POST);

try {
    if (isset($_POST['qcCode']) && trim((string) $_POST['qcCode']) != "") {

        $data = array(
            'unique_id'             => MiscUtility::generateULID(),
            'qc_code'               => $_POST['qcCode'],
            'testkit'               => base64_decode((string) $_POST['testKit']),
            'lot_no'                => $_POST['lotNo'],
            'expiry_date'           => DateUtility::isoDateFormat($_POST['expiryDate']),
            'lab_id'                => $_POST['labName'],
            'testing_point'                => $_POST['testingPoint'],
            'tested_by'             => $_POST['testerName'],
            'qc_received_datetime'    => date("Y-m-d H:s:i", strtotime((string) $_POST['receivedOn'])),
            'qc_tested_datetime'    => date("Y-m-d H:s:i", strtotime((string) $_POST['testedOn'])),
            'created_on'            => DateUtility::getCurrentDateTime(),
            'updated_datetime'            => DateUtility::getCurrentDateTime()
        );
        $exist = false;
        if (isset($_POST['qcDataId']) && $_POST['qcDataId'] != "") {
            /* Suppose while edit they can change the testkit means prev data not needed so we can rease it from DB */
            $exist = $db->rawQueryOne("SELECT qc_id FROM $tableName1 WHERE qc_id = " . base64_decode((string) $_POST['qcDataId']));
            if (isset($exist) && !empty($exist['qc_id'])) {
                $db->where("qc_id", $exist['qc_id']);
                $db->delete($tableName1);
            }

            unset($data['unique_id']);
            unset($data['created_on']);

            $db->where($primaryKey, base64_decode((string) $_POST['qcDataId']));
            $db->update($tableName, $data);
            $lastId = base64_decode((string) $_POST['qcDataId']);
        } else {
            if (!empty($_POST['qcKey'])) {
                $data['qc_code_key'] = $_POST['qcKey'];
            }
            $lastId = $db->insert($tableName, $data);
        }

        //var_dump($lastId);die;
        if ($lastId > 0) {
            foreach ($_POST['testLabel'] as $key => $row) {
                if (isset($_POST['testResults'][$key]) && $_POST['testResults'][$key] != "") {
                    $subData = array(
                        "qc_id"         => $lastId,
                        "test_label"    => $row,
                        "test_result"   => $_POST['testResults'][$key],
                    );

                    $db->insert($tableName1, $subData);

                    /* If ID already exist we can update */
                    // if (isset($_POST['qcTestId'][$key]) && !empty($_POST['qcTestId'][$key])) {
                    //     $db->where($primaryKey1, $_POST['qcTestId'][$key]);
                    //     $db->update($tableName1, $subData);
                    // } else {
                    //     $db->insert($tableName1, $subData);
                    // }
                }
            }

            $_SESSION['alertMsg'] = _translate("Covid-19 QC test kit saved successfully");
            $general->activityLog('Covid-19 qc data', $_SESSION['userName'] . ' added new qc data for ' . $_POST['qcCode'], 'covid19-results');
        }
    }
    header("Location:covid-19-qc-data.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
}
