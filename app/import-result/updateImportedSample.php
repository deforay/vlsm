<?php

use App\Registries\AppRegistry;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());

$tableName = "temp_sample_import";
try {
    $result = 0;
    if (isset($_POST['batchCode']) && trim((string) $_POST['batchCode']) != '') {
        $batchResult = $db->rawQuery("select batch_code from batch_details where batch_code='" . trim((string) $_POST['batchCode']) . "'");
        if (!$batchResult) {
            $data = array(
                'machine' => 0,
                'batch_code' => trim((string) $_POST['batchCode']),
                'request_created_datetime' => DateUtility::getCurrentDateTime()
            );
            $db->insert("batch_details", $data);
        }
        $db->where('temp_sample_id', $_POST['tempsampleId']);
        $result = $db->update($tableName, array('batch_code' => $_POST['batchCode']));
    } else if (isset($_POST['sampleCode']) && trim((string) $_POST['sampleCode']) != '') {
        $sampleResult = $db->rawQuery("select sample_code from form_vl where sample_code='" . trim((string) $_POST['sampleCode']) . "'");
        if ($sampleResult) {
            $sampleDetails = 'Result already exists';
        } else {
            $sampleDetails = 'New Sample';
        }
        $db->where('temp_sample_id', $_POST['tempsampleId']);
        $result = $db->update($tableName, array('sample_code' => $_POST['sampleCode'], 'sample_details' => $sampleDetails));
    } else if (isset($_POST['sampleType']) && trim((string) $_POST['sampleType']) != '') {
        $sampleControlResult = $db->rawQuery("select r_sample_control_name from r_sample_controls where r_sample_control_name='" . trim((string) $_POST['sampleType']) . "'");
        $db->where('temp_sample_id', $_POST['tempsampleId']);
        $result = $db->update($tableName, array('sample_type' => trim((string) $_POST['sampleType'])));
    }
    echo $result;
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
