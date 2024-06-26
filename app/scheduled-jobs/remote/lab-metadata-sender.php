<?php

$cliMode = php_sapi_name() === 'cli';
if ($cliMode) {
    require_once(__DIR__ . "/../../../bootstrap.php");
}

//this file gets the data from the local database and updates the remote database
use App\Services\ApiService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;
use App\Utilities\MiscUtility;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var ApiService $apiService */
$apiService = ContainerRegistry::get(ApiService::class);

// only for LIS instances
if ($general->isLISInstance() === false) {
    exit(0);
}

$labId = $general->getSystemConfig('sc_testing_lab_id');
$version = VERSION;

// putting this into a variable to make this editable
$systemConfig = SYSTEM_CONFIG;

$lastUpdatedOn = $db->getValue('s_vlsm_instance', 'last_lab_metadata_sync');

$remoteUrl = $general->getRemoteURL();

if (empty($remoteUrl)) {
    LoggerUtility::log('error', "Please check if STS URL is set");
    exit(0);
}
try {
    // Checking if the network connection is available
    if ($apiService->checkConnectivity($remoteUrl . '/api/version.php?labId=' . $labId . '&version=' . $version) === false) {
        LoggerUtility::log('error', "No network connectivity while trying remote sync.");
        return false;
    }

    $transactionId = MiscUtility::generateUUID();

    $payload = [
        "transactionId" => $transactionId,
        "labId" => $labId,
        "x-api-key" => MiscUtility::generateUUID(),
    ];

    $url = $remoteUrl . '/remote/remote/lab-metadata-receiver.php';

    // LAB STORAGE
    if (!empty($lastUpdatedOn)) {
        $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    }
    $labStorage = $db->get('lab_storage');
    if (!empty($labStorage)) {
        $payload["labStorage"] = $labStorage;
    }

    // LAB STORAGE HISTORY
    if (!empty($lastUpdatedOn)) {
        $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    }
    $labStorageHistory = $db->get('lab_storage_history');
    if (!empty($labStorageHistory)) {
        $payload["labStorageHistory"] = $labStorageHistory;
    }

    // PATIENTS
    if (!empty($lastUpdatedOn)) {
        $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    }
    $patients = $db->get('patients');

    if (!empty($patients)) {
        $payload["patients"] = $patients;
    }

    // INSTRUMENTS
    if (!empty($lastUpdatedOn)) {
        $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    }
    $instruments = $db->get('instruments');
    if (!empty($instruments)) {
        $payload["instruments"] = $instruments;
    }

    // INSTRUMENT MACHINES
    if (!empty($lastUpdatedOn)) {
        $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    }
    $instrumentMachines = $db->get('instrument_machines');

    if (!empty($instrumentMachines)) {
        $payload["instrumentMachines"] = $instrumentMachines;
    }

    // INSTRUMENT CONTROLS
    if (!empty($lastUpdatedOn)) {
        $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    }
    $instrumentControls = $db->get('instrument_controls');

    if (!empty($instrumentControls)) {
        $payload["instrumentControls"] = $instrumentControls;
    }

    // CONFIG
    // if (!empty($lastUpdatedOn)) {
    //     $db->where(' (updated_datetime > "' . $lastUpdatedOn . '" OR updated_datetime IS NULL)');
    // }
    // $globalConfig = $db->get('global_config');

    // if (!empty($globalConfig)) {
    //     $payload["globalConfig"] = $globalConfig;
    // }

    $jsonResponse = $apiService->post($url, $payload);
    $instanceId = $general->getInstanceId();
    $db->where('vlsm_instance_id', $instanceId);
    $id = $db->update('s_vlsm_instance', ['last_lab_metadata_sync' => DateUtility::getCurrentDateTime()]);
} catch (Exception $exc) {
    LoggerUtility::log("error", __FILE__ . ":" . $exc->getMessage(), [
        'file' => $exc->getFile(),
        'line' => $exc->getLine(),
        'trace' => $exc->getTraceAsString(),
    ]);
}
