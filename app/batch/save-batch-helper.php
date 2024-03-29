<?php

use App\Registries\AppRegistry;
use App\Services\BatchService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var BatchService $batchService */
$batchService = ContainerRegistry::get(BatchService::class);


$refTable = "form_vl";
$refPrimaryColumn = "vl_sample_id";

if (isset($_POST['type'])) {
    switch ($_POST['type']) {
        case 'vl':
            $refTable = "form_vl";
            $refPrimaryColumn = "vl_sample_id";
            break;
        case 'eid':
            $refTable = "form_eid";
            $refPrimaryColumn = "eid_id";
            break;
        case 'covid19':
            $refTable = "form_covid19";
            $refPrimaryColumn = "covid19_id";
            break;
        case 'hepatitis':
            $refTable = "form_hepatitis";
            $refPrimaryColumn = "hepatitis_id";
            break;
        case 'tb':
            $refTable = "form_tb";
            $refPrimaryColumn = "tb_id";
            break;
        case 'cd4':
            $refTable = "form_cd4";
            $refPrimaryColumn = "cd4_id";
            break;
        case 'generic-tests':
            $refTable = "form_generic";
            $refPrimaryColumn = "sample_id";
            break;
        default:
            throw new SystemException('Invalid test type - ' . $_POST['type'], 500);
    }
}

$tableName1 = "batch_details";
try {


    if (isset($_POST['batchCode']) && trim((string) $_POST['batchCode']) != "") {
        if (!empty($_POST['batchId'])) {
            $id = intval($_POST['batchId']);
            $data = [
                'batch_code' => $_POST['batchCode'],
                'position_type' => $_POST['positions'],
                'machine' => $_POST['machine'],
                'last_modified_by' => $_SESSION['userId'],
                'last_modified_datetime' => DateUtility::getCurrentDateTime()
            ];
            $db->where('batch_id', $id);
            $db->update($tableName1, $data);
            if ($id > 0) {
                $value = ['sample_batch_id' => null];
                $db->where('sample_batch_id', $id);
                $db->update($refTable, $value);
                $xplodResultSample = [];
                if (isset($_POST['selectedSample']) && trim((string) $_POST['selectedSample']) != "") {
                    $xplodResultSample = explode(",", (string) $_POST['selectedSample']);
                }
                $sample = [];
                //Mergeing disabled samples into existing samples
                if (!empty($_POST['sampleCode'])) {
                    if (!empty($xplodResultSample)) {
                        $sample = array_unique(array_merge($_POST['sampleCode'], $xplodResultSample));
                    } else {
                        $sample = $_POST['sampleCode'];
                    }
                } elseif (!empty($xplodResultSample)) {
                    $sample = $xplodResultSample;
                }

                for ($j = 0; $j < count($sample); $j++) {
                    $value = array('sample_batch_id' => $id);
                    $db->where($refPrimaryColumn, $sample[$j]);
                    $db->update($refTable, $value);
                }
                header("Location:add-batch-position.php?type=" . $_POST['type'] . "&id=" . base64_encode($id) . "&position=" . $_POST['positions']);
            }
            //else {
            // header("Location:batches.php?type=" . $_POST['type']);
            //}
        } else {
            $exist = $batchService->doesBatchCodeExist($_POST['batchCode']);
            if ($exist) {
                $_SESSION['alertMsg'] = _translate("Something went wrong. Please try again later.");
                header("Location:batches.php?type=" . $_POST['type']);
            } else {
                $data = [
                    'machine' => $_POST['platform'],
                    'batch_code' => $_POST['batchCode'],
                    'batch_code_key' => $_POST['batchCodeKey'],
                    'position_type' => $_POST['positions'],
                    'test_type' => $_POST['type'],
                    'created_by' => $_SESSION['userId'],
                    'last_modified_by' => $_SESSION['userId'],
                    'last_modified_datetime' => DateUtility::getCurrentDateTime(),
                    'request_created_datetime' => DateUtility::getCurrentDateTime()
                ];

                $db->insert($tableName1, $data);
                $lastId = $db->getInsertId();
                if ($lastId > 0 && trim((string) $_POST['selectedSample']) != '') {
                    $selectedSample = explode(",", (string) $_POST['selectedSample']);
                    $uniqueSampleId = array_unique($selectedSample);
                    for ($j = 0; $j <= count($selectedSample); $j++) {
                        if (isset($uniqueSampleId[$j])) {

                            $vlSampleId = $uniqueSampleId[$j];
                            $value = array('sample_batch_id' => $lastId);
                            $db->where($refPrimaryColumn, $vlSampleId);
                            $db->update($refTable, $value);
                        }
                    }
                    header("Location:add-batch-position.php?type=" . $_POST['type'] . "&id=" . base64_encode($lastId) . "&position=" . $_POST['positions']);
                } else {
                    header("Location:batches.php?type=" . $_POST['type']);
                }
            }
        }
    }
    // header("Location:batches.php?type=" . $_POST['type']);
} catch (Exception $exc) {
    throw new SystemException($exc->getMessage(), 500);
}
