<?php

use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\FileCacheUtility;
use App\Registries\ContainerRegistry;
use App\Utilities\ImageResizeUtility;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var FileCacheUtility $fileCache */
$fileCache = ContainerRegistry::get(FileCacheUtility::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$instanceTableName = "s_vlsm_instance";

$currentDateTime = DateUtility::getCurrentDateTime();

// unset global config cache so that it can be reloaded with new values
// this is set in CommonService::getGlobalConfig()
$fileCache->delete('app_global_config');

try {

    //remove instance table data
    if (isset($_POST['removedInstanceLogoImage']) && trim((string) $_POST['removedInstanceLogoImage']) != "" && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $_POST['removedInstanceLogoImage'])) {
        unlink(realpath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo") . DIRECTORY_SEPARATOR . $_POST['removedInstanceLogoImage']);
        $data = array('instance_facility_logo' => null);
        $db->where('vlsm_instance_id', $_SESSION['instanceId']);
        $db->update($instanceTableName, $data);
    }
    $removedImage = realpath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage']);
    if (isset($_POST['removedLogoImage']) && trim((string) $_POST['removedLogoImage']) != "" && !empty($removedImage) && file_exists($removedImage)) {
        unlink($removedImage);
        $data = ['value' => null];
        $db->where('name', 'logo');
        $id = $db->update("global_config", $data);
        if ($id) {
            $db->where('name', 'logo');
            $db->update("global_config", [
                "updated_on" => $currentDateTime,
                "updated_by" => $_SESSION['userId']
            ]);
        }
    }

    if (isset($_FILES['instanceLogo']['name']) && $_FILES['instanceLogo']['name'] != "") {
        MiscUtility::makeDirectory(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo");
        $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['instanceLogo']['name'], PATHINFO_EXTENSION));
        $string = $general->generateRandomString(6) . ".";
        $imageName = "logo" . $string . $extension;
        if (move_uploaded_file($_FILES["instanceLogo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $imageName)) {

            $resizeObj = new ImageResizeUtility(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $imageName);
            $resizeObj->resizeToWidth(100);
            $resizeObj->save(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $imageName);


            $image = array('instance_facility_logo' => $imageName);
            $db->where('vlsm_instance_id', $_SESSION['instanceId']);
            $db->update($instanceTableName, $image);
        }
    }
    $instanceData = [
        'instance_facility_name' => $_POST['facilityId'],
        'instance_facility_code' => $_POST['facilityCode'],
        'instance_facility_type' => $_POST['instance_type'],
        'instance_update_on' => $currentDateTime,
    ];
    $db->where('vlsm_instance_id', $_SESSION['instanceId']);
    $updateInstance = $db->update($instanceTableName, $instanceData);
    if ($updateInstance > 0) {
        //Add event log
        $eventType = 'update-instance';
        $action = $_SESSION['userName'] . ' update instance id';
        $resource = 'instance-details';
        $general->activityLog($eventType, $action, $resource);
    }
    if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != "") {
        if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo")) {
            mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo", 0777, true);
        }
        $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['logo']['name'], PATHINFO_EXTENSION));
        $string = $general->generateRandomString(6) . ".";
        $imageName = "logo" . $string . $extension;
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName)) {
            $resizeObj = new ImageResizeUtility(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName);
            if ($_POST['vl_form'] == 4) {
                list($width, $height) = getimagesize(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName);
                if ($width > 240) {
                    $resizeObj->resizeToBestFit(240, 80);
                }
            } else {
                $resizeObj->resizeToWidth(100);
            }
            $resizeObj->save(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName);

            $data = array('value' => $imageName);
            $db->where('name', 'logo');
            $id = $db->update("global_config", $data);
            if ($id) {
                $db->where('name', 'logo');
                $db->update("global_config", [
                    "updated_on" => $currentDateTime,
                    "updated_by" => $_SESSION['userId']
                ]);
            }
        }
    }
    // if (!isset($_POST['r_mandatory_fields'])) {
    //     $data = ['value' => null];
    //     $db->where('name', 'r_mandatory_fields');
    //     $id = $db->update("global_config", $data);
    //     if ($id) {
    //         $db->where('name', 'r_mandatory_fields');
    //         $db->update("global_config", [
    //             "updated_on" => $currentDateTime,
    //             "updated_by" => $_SESSION['userId']
    //         ]);
    //     }
    // }


    unset($_SESSION['APP_LOCALE']);

    foreach ($_POST as $fieldName => $fieldValue) {
        if ($fieldName != 'removedLogoImage') {
            if ($fieldName == 'r_mandatory_fields') {
                $fieldValue = implode(',', $fieldValue);
            }
            $data = array('value' => $fieldValue);
            $db->where('name', $fieldName);
            $id = $db->update("global_config", $data);
            if ($id) {
                $db->where('name', $fieldName);
                $db->update("global_config", [
                    "updated_on" => $currentDateTime,
                    "updated_by" => $_SESSION['userId']
                ]);
            }
        }
    }

    $dateFormat = $_POST['gui_date_format'] ?? 'd-M-Y';
    $general->setGlobalDateFormat($dateFormat);


    /* For Lock approve sample updates */
    if (isset($_POST['lockApprovedVlSamples']) && trim((string) $_POST['lockApprovedVlSamples']) != "") {
        $data = array('value' => trim((string) $_POST['lockApprovedVlSamples']));
        $db->where('name', 'lock_approved_vl_samples');
        $id = $db->update("global_config", $data);
    }
    if (isset($_POST['vl_monthly_target']) && trim((string) $_POST['vl_monthly_target']) != "") {
        $data = array('value' => trim((string) $_POST['vl_monthly_target']));
        $db->where('name', 'vl_monthly_target');
        $id = $db->update("global_config", $data);
    }
    if (isset($_POST['vl_suppression_target']) && trim((string) $_POST['vl_suppression_target']) != "") {
        $data = array('value' => trim((string) $_POST['vl_suppression_target']));
        $db->where('name', 'vl_suppression_target');
        $id = $db->update("global_config", $data);
    }
    if (isset($_POST['lockApprovedEidSamples']) && trim((string) $_POST['lockApprovedEidSamples']) != "") {
        $data = array('value' => trim((string) $_POST['lockApprovedEidSamples']));
        $db->where('name', 'lock_approved_eid_samples');
        $id = $db->update("global_config", $data);
    }
    if (isset($_POST['lockApprovedCovid19Samples']) && trim((string) $_POST['lockApprovedCovid19Samples']) != "") {
        $data = array('value' => trim((string) $_POST['lockApprovedCovid19Samples']));
        $db->where('name', 'lock_approved_covid19_samples');
        $id = $db->update("global_config", $data);
    }
    if (isset($_POST['covid19ReportQrCode']) && trim((string) $_POST['covid19ReportQrCode']) != "") {
        $data = array('value' => trim((string) $_POST['covid19ReportQrCode']));
        $db->where('name', 'covid19_report_qr_code');
        $id = $db->update("global_config", $data);
    }

    if (isset($_POST['covid19ReportType']) && trim((string) $_POST['covid19ReportType']) != "") {
        $data = array('value' => trim((string) $_POST['covid19ReportType']));
        $db->where('name', 'covid19_report_type');
        $id = $db->update("global_config", $data);
        if ($id) {
            $db->where('name', 'logo');
            $db->update("global_config", array(
                "updated_on" => $currentDateTime,
                "updated_by" => $_SESSION['userId']
            ));
        }
    }
    if (isset($_POST['covid19PositiveConfirmatoryTestsRequiredByCentralLab']) && trim((string) $_POST['covid19PositiveConfirmatoryTestsRequiredByCentralLab']) != "") {
        $data = array('value' => trim((string) $_POST['covid19PositiveConfirmatoryTestsRequiredByCentralLab']));
        $db->where('name', 'covid19_positive_confirmatory_tests_required_by_central_lab');
        $id = $db->update("global_config", $data);
        if ($id) {
            $db->where('name', 'logo');
            $db->update("global_config", array(
                "updated_on" => $currentDateTime,
                "updated_by" => $_SESSION['userId']
            ));
        }
    }
    if (isset($_POST['covid19TestsTableInResultsPdf']) && trim((string) $_POST['covid19TestsTableInResultsPdf']) != "") {
        $data = array('value' => trim((string) $_POST['covid19TestsTableInResultsPdf']));
        $db->where('name', 'covid19_tests_table_in_results_pdf');
        $id = $db->update("global_config", $data);
        if ($id) {
            $db->where('name', 'logo');
            $db->update("global_config", array(
                "updated_on" => $currentDateTime,
                "updated_by" => $_SESSION['userId']
            ));
        }
    }
    if (isset($_POST['genericTestsTableInResultsPdf']) && trim((string) $_POST['genericTestsTableInResultsPdf']) != "") {
        $data = array('value' => trim((string) $_POST['genericTestsTableInResultsPdf']));
        $db->where('name', 'generic_tests_table_in_results_pdf');
        $id = $db->update("global_config", $data);
        if ($id) {
            $db->where('name', 'logo');
            $db->update("global_config", array(
                "updated_on" => $currentDateTime,
                "updated_by" => $_SESSION['userId']
            ));
        }
    }

    $_SESSION['alertMsg'] = _translate("Configuration updated successfully");

    //Add event log
    $eventType = 'general-config-update';
    $action = $_SESSION['userName'] . ' updated general config';
    $resource = 'general-config';

    $general->activityLog($eventType, $action, $resource);
    header("Location:editGlobalConfig.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
