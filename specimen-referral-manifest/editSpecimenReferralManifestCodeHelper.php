<?php
ob_start();
session_start();
require_once('../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
$general = new General($db);
$packageTable = "package_details";
try {
    if (isset($_POST['packageCode']) && trim($_POST['packageCode']) != "" && count($_POST['sampleCode']) > 0) {
        $lastId = $_POST['packageId'];
        $db->where('package_id', $lastId);
        $db->update($packageTable, array('package_status' => $_POST['packageStatus']));

        if ($lastId > 0) {
            $value = array('sample_package_id'   => null,
                           'sample_package_code' => null);

            if ($_POST['module'] == 'vl') {
                $db = $db->where('sample_package_id', $lastId);
                $db->update('vl_request_form', $value);
            } else if ($_POST['module'] == 'eid') {
                $db = $db->where('sample_package_id', $lastId);
                $db->update('eid_form', $value);
            }
            for ($j = 0; $j < count($_POST['sampleCode']); $j++) {
                $value = array('sample_package_id'   => $lastId,
                               'sample_package_code' => $_POST['packageCode'],
                                'data_sync' => 0);
                if ($_POST['module'] == 'vl') {
                    $db = $db->where('vl_sample_id', $_POST['sampleCode'][$j]);
                    $db->update('vl_request_form', $value);
                } else if ($_POST['module'] == 'eid') {
                    $db = $db->where('eid_id', $_POST['sampleCode'][$j]);
                    $db->update('eid_form', $value);
                }
            }
            $_SESSION['alertMsg'] = "Manifest details updated successfully";
        }
    }
    header("location:specimenReferralManifestList.php?t=" . base64_encode($_POST['module']));
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
