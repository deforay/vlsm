<?php

// File included in import-file-helper.php
use App\Registries\AppRegistry;
use App\Services\DatabaseService;
use League\Csv\Reader;
use League\Csv\Statement;
use App\Services\BatchService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Exceptions\SystemException;
use App\Services\TestResultsService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = $request->getParsedBody();

try {

    /** @var TestResultsService $testResultsService */
    $testResultsService = ContainerRegistry::get(TestResultsService::class);

    $testResultsService->clearPreviousImportsByUser($_SESSION['userId'], 'covid19');

    $_SESSION['controllertrack'] = $testResultsService->getMaxIDForHoldingSamples();

    $allowedExtensions = array(
        'txt',
    );
    if (
        isset($_FILES['resultFile']) && $_FILES['resultFile']['error'] !== UPLOAD_ERR_OK
        || $_FILES['resultFile']['size'] <= 0
    ) {
        throw new SystemException('Please select a file to upload', 400);
    }

    $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', htmlspecialchars(basename((string) $_FILES['resultFile']['name'])));
    $fileName = str_replace(" ", "-", $fileName);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileName = $_POST['fileName'] . "." . $extension;



    $resultFile = realpath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results") . DIRECTORY_SEPARATOR . $fileName;
    if (move_uploaded_file($_FILES['resultFile']['tmp_name'], $resultFile)) {

        /** @var BatchService $batchService */
        $batchService = ContainerRegistry::get(BatchService::class);
        [$maxBatchCodeKey, $newBatchCode] = $batchService->createBatchCode();

        //load the CSV document from a file path
        $csv = Reader::createFromPath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results" . DIRECTORY_SEPARATOR . $fileName);
        $csv->setDelimiter("\t");

        $stmt = new Statement();
        $topRecords = $stmt->limit(18)->process($csv);

        $metaRecords = [];
        foreach ($topRecords as $topRecord) {
            if (empty($topRecord[0])) {
                continue;
            }
            $metaRecords[$topRecord[0]] = $topRecord[1];
        }





        $csv->setHeaderOffset(20); //set the CSV header offset - including empty lines





        // get records starting from the specified row number
        // remember this does not count empty lines
        // So in your file you may have the data starting from line 20
        // but there are 2 empty lines, so we instruct to offset 18
        $records = $stmt->offset(18)->process($csv);
        $m = 0;
        $infoFromFile = [];
        foreach ($records as $record) {
            $m++;

            $sampleCode = "";
            $batchCode = "";
            $sampleType = "";
            $txtVal = "";
            $resultFlag = "";

            $sampleCode = $record['SAMPLE ID'];
            $sampleType = $record['SAMPLE TYPE'];

            //$batchCode = $record[$batchCodeCol];
            $resultFlag = $record['FLAGS'];
            //$reviewBy = $record[$reviewByCol];

            // Changing date to European format for strtotime - https://stackoverflow.com/a/5736255

            $testingDate = date('Y-m-d H:i', strtotime($metaRecords['RUN COMPLETION TIME']));
            $result = '';

            $result = strtolower($record['INTERPRETATION']);



            $lotNumberVal = $record['REAGENT LOT NUMBER'];

            if (trim($record["REAGENT LOT EXPIRATION DATE"]) != '') {
                $lotExpirationDateVal = (date('Y-m-d', strtotime($record["REAGENT LOT EXPIRATION DATE"])));
            }

            if ($sampleType == 'Patient') {
                $sampleType = 'S';
            } else if ($sampleType == 'Control') {

                if ($sampleCode == 'COV-2_POS') {
                    $sampleType = 'HPC';
                    $sampleCode = $sampleCode . '-' . $lotNumberVal;
                } else if ($sampleCode == 'COV-2_NEG') {
                    $sampleType = 'NC';
                    $sampleCode = $sampleCode . '-' . $lotNumberVal;
                }
            }

            $batchCode = "";


            if ($sampleCode == "") {
                $sampleCode = $sampleType . $m;
            }


            if (!isset($infoFromFile[$sampleCode])) {
                $infoFromFile[$sampleCode] = array(
                    "sampleCode" => $sampleCode,
                    "resultFlag" => $resultFlag,
                    "testingDate" => $testingDate,
                    "sampleType" => $sampleType,
                    "batchCode" => $batchCode,
                    "lotNumber" => $lotNumberVal,
                    "result" => $result,
                    "lotExpirationDate" => $lotExpirationDateVal,
                );
            }
        }







        $inc = 0;
        foreach ($infoFromFile as $sampleCode => $d) {
            if ($d['sampleCode'] == $d['sampleType'] . $inc) {
                $d['sampleCode'] = '';
            }
            $data = array(
                'module' => 'covid19',
                'lab_id' => base64_decode((string) $_POST['labId']),
                'vl_test_platform' => $_POST['vltestPlatform'],
                'import_machine_name' => $_POST['configMachineName'],
                'result_reviewed_by' => $_SESSION['userId'],
                'sample_code' => $d['sampleCode'],
                'sample_type' => $d['sampleType'],
                'sample_tested_datetime' => $d['testingDate'],
                'result_status' => '6',
                'import_machine_file_name' => $fileName,
                'lab_tech_comments' => $d['resultFlag'],
                'lot_number' => $d['lotNumber'],
                'lot_expiration_date' => $d['lotExpirationDate'],
                'result' => $d['result'],
            );

            if ($batchCode == '') {
                $data['batch_code'] = $newBatchCode;
                $data['batch_code_key'] = $maxBatchCodeKey;
            } else {
                $data['batch_code'] = $batchCode;
            }
            //get username
            if (!empty($d['reviewBy'])) {

                /** @var UsersService $usersService */
                $usersService = ContainerRegistry::get(UsersService::class);
                $data['sample_review_by'] = $usersService->getOrCreateUser($d['reviewBy']);
            }

            $query = "select facility_id,covid19_id,result from form_covid19 where sample_code='" . $sampleCode . "'";
            $vlResult = $db->rawQuery($query);
            //insert sample controls
            $scQuery = "SELECT r_sample_control_name from r_sample_controls where r_sample_control_name='" . trim($d['sampleType']) . "'";
            $scResult = $db->rawQuery($scQuery);
            if (!$scResult) {
                $scData = array('r_sample_control_name' => trim($d['sampleType']));
                $scId = $db->insert("r_sample_controls", $scData);
            }
            if (!empty($vlResult) && !empty($sampleCode)) {
                if (!empty($vlResult[0]['result'])) {
                    $data['sample_details'] = 'Result already exists';
                } else {
                    $data['result_status'] = '7';
                }
                $data['facility_id'] = $vlResult[0]['facility_id'];
            } else {
                $data['sample_details'] = 'New Sample';
            }

            if ($sampleCode != '' || $batchCode != '' || $sampleType != '') {
                $data['result_imported_datetime'] = DateUtility::getCurrentDateTime();
                $data['imported_by'] = $_SESSION['userId'];
                $id = $db->insert("temp_sample_import", $data);
            }
            $inc++;
        }
    }

    $_SESSION['alertMsg'] = "Results imported successfully";
    //Add event log
    $eventType = 'import';
    $action = $_SESSION['userName'] . ' imported a new test result with the sample id ' . $sampleCode;
    $resource = 'import-results-manually';
    $general->activityLog($eventType, $action, $resource);

    header("Location:/import-result/imported-results.php?t=$type");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
