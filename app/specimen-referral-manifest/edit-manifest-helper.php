<?php

use App\Services\TestsService;
use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;


// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody(), nullifyEmptyStrings: true);

if (empty($_POST['testingLab']) || 0 === (int) $_POST['testingLab']) {
    $_SESSION['alertMsg'] = _translate("Please select the Testing lab", true);;
    header("Location:/specimen-referral-manifest/edit-manifest.php?t=" . ($_POST['module']));
}


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$tableName = TestsService::getTestTableName($_POST['module']);
$primaryKey = TestsService::getTestPrimaryKeyColumn($_POST['module']);

$packageTable = "package_details";
try {
    $db->beginTransaction();
    $selectedSample = MiscUtility::desqid($_POST['selectedSample'], returnArray: true);
    $uniqueSampleId = array_unique($selectedSample);
    $numberOfSamples = count($selectedSample);
    if (isset($_POST['packageCode']) && trim((string) $_POST['packageCode']) != "" && !empty($selectedSample)) {

        $db->where('sample_package_code', $_POST['packageCode']);
        $db->update($tableName, [
            'sample_package_id'   => null,
            'sample_package_code' => null
        ]);

        $lastId = $_POST['packageId'];

        $db->where('package_id', $lastId);
        $previousData = $db->getOne($packageTable);
        $oldReason = json_decode($previousData['manifest_change_history']);

        $newReason = ['reason' => $_POST['reasonForChange'], 'changedBy' => $_SESSION['userId'], 'date' => DateUtility::getCurrentDateTime()];
        $oldReason[] = $newReason;
        $db->where('package_id', $lastId);
        $db->update($packageTable, [
            'lab_id' => $_POST['testingLab'],
            'number_of_samples' => $numberOfSamples,
            'package_status' => $_POST['packageStatus'],
            'manifest_change_history' => json_encode($oldReason),
            'last_modified_datetime' => DateUtility::getCurrentDateTime()
        ]);

        if ($lastId > 0) {
            //for ($j = 0; $j < count($selectedSample); $j++) {
                $dataToUpdate = [
                    'sample_package_id'   => $lastId,
                    'sample_package_code' => $_POST['packageCode'],
                    'last_modified_datetime' => DateUtility::getCurrentDateTime(),
                    'data_sync' => 0
                ];

                $formAttributes['manifest'] = [
                    "number_of_samples" => $numberOfSamples,
                ];

                $formAttributes = JsonUtility::jsonToSetString(json_encode($formAttributes), 'form_attributes');
                $dataToUpdate['form_attributes'] = $db->func($formAttributes);

                //$db->where($primaryKey, $uniqueSampleId[$j]);
                $db->where($primaryKey, $selectedSample, 'IN');
                $db->update($tableName, $dataToUpdate);
            //}

            // In case some records dont have lab_id in the testing table
            // let us update them to the selected lab
            $dataToUpdate = [
                'lab_id' => $_POST['testingLab'],
                'last_modified_datetime' => DateUtility::getCurrentDateTime(),
                'data_sync' => 0
            ];

            $db->where('sample_package_code', $_POST['packageCode']);
            $db->where('lab_id IS NULL OR lab_id = 0');
            $db->update($tableName, $dataToUpdate);

            $_SESSION['alertMsg'] = "Manifest details updated successfully";
        }
    }

    //Add event log
    $eventType = 'edit-manifest';
    $action = $_SESSION['userName'] . ' updated Manifest - ' . $_POST['packageCode'];
    $resource = 'specimen-manifest';

    $general->activityLog($eventType, $action, $resource);
    $db->commitTransaction();
    header("Location:view-manifests.php?t=" . ($_POST['module']));
} catch (Throwable $e) {
    $db->rollbackTransaction();
    LoggerUtility::log('error',  $e->getMessage(),[
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'last_db_query' => $db->getLastQuery(),
        'last_db_error' => $db->getLastError()
    ]);
}
