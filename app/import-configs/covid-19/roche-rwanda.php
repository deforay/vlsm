<?php

// File included in addImportResultHelper.php

use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

try {
    $dateFormat = (isset($_POST['dateFormat']) && !empty($_POST['dateFormat'])) ? $_POST['dateFormat'] : 'd/m/Y H:i';
    $db = $db->where('imported_by', $_SESSION['userId']);
    $db->delete('temp_sample_import');
    //set session for controller track id in hold_sample_record table
    $cQuery = "SELECT MAX(import_batch_tracking) FROM hold_sample_import";
    $cResult = $db->query($cQuery);
    if ($cResult[0]['MAX(import_batch_tracking)'] != '') {
        $maxId = $cResult[0]['MAX(import_batch_tracking)'] + 1;
    } else {
        $maxId = 1;
    }
    $_SESSION['controllertrack'] = $maxId;

    $allowedExtensions = array(
        'txt',
    );
    if (
        isset($_FILES['resultFile']) && $_FILES['resultFile']['error'] !== UPLOAD_ERR_OK
        || $_FILES['resultFile']['size'] <= 0
    ) {
        throw new SystemException('Please select a file to upload', 400);
    }

    $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', htmlspecialchars(basename($_FILES['resultFile']['name'])));
    $fileName = str_replace(" ", "-", $fileName);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileName = $_POST['fileName'] . "." . $extension;
    // $ranNumber = $general->generateRandomString(12);
    // $fileName = $ranNumber . "." . $extension;

    if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results")) {
        mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results", 0777, true);
    }
    $resultFile = realpath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results") . DIRECTORY_SEPARATOR . $fileName;
    if (move_uploaded_file($_FILES['resultFile']['tmp_name'], $resultFile)) {

        $file_info = new finfo(FILEINFO_MIME); // object oriented approach!
        $mime_type = $file_info->buffer(file_get_contents($resultFile)); // e.g. gives "image/jpeg"

        $bquery = "SELECT MAX(batch_code_key) FROM `batch_details`";
        $bvlResult = $db->rawQuery($bquery);
        if ($bvlResult[0]['MAX(batch_code_key)'] != '' && $bvlResult[0]['MAX(batch_code_key)'] != null) {
            $maxBatchCodeKey = $bvlResult[0]['MAX(batch_code_key)'] + 1;
            $maxBatchCodeKey = "00" . $maxBatchCodeKey;
        } else {
            $maxBatchCodeKey = '001';
        }

        $newBatchCode = date('Ymd') . $maxBatchCodeKey;

        $m = 1;
        $skipTillRow = 5;

        $testDateCol = "B";
        $sampleIdCol = "C";
        $sampleTypeCol = "D";
        $batchCodeVal = "E";
        $resultCol = "G";


        $flagCol = 10;


        $lotNumberCol = 12;
        $reviewByCol = '';
        $lotExpirationDateCol = 13;

        $spreadsheet = IOFactory::load(UPLOAD_PATH . DIRECTORY_SEPARATOR . "imported-results" . DIRECTORY_SEPARATOR . $fileName);
        $sheetData   = $spreadsheet->getActiveSheet();
        $sheetData   = $sheetData->toArray(null, true, true, true);

        // echo "<pre>";
        // var_dump($sheetData);
        // die;

        $infoFromFile = [];
        $testDateRow = "";


        $row = 1;

        foreach ($sheetData as $rowIndex => $rowData) {

            if ($rowIndex < $skipTillRow)
                continue;


            $num = count($rowData);
            $row++;

            $sampleCode = "";
            $batchCode = "";
            $sampleType = "";
            $absDecimalVal = "";
            $absVal = "";
            $logVal = "";
            $txtVal = "";
            $resultFlag = "";

            $sampleCode = $rowData[$sampleIdCol];

            if ($sampleCode == "SAMPLE ID" || $sampleCode == "") {
                continue;
            }

            $sampleType = $rowData[$sampleTypeCol];

            $batchCode = $rowData[$batchCodeCol];
            $resultFlag = $rowData[$flagCol];
            //$reviewBy = $rowData[$reviewByCol];

            // //Changing date to European format for strtotime - https://stackoverflow.com/a/5736255
            // $rowData[$testDateCol] = str_replace("/", "-", $rowData[$testDateCol]);
            // $testingDate = date('Y-m-d H:i', strtotime($rowData[$testDateCol]));
            $result = $absVal = $logVal = $absDecimalVal = $txtVal = '';

            if (strpos(strtolower($rowData[$resultCol]), 'not detected') !== false) {
                $result = 'negative';
            } else if ((strpos(strtolower($rowData[$resultCol]), 'detected') !== false) || (strpos(strtolower($rowData[$resultCol]), 'passed') !== false)) {
                $result = 'positive';
            } else {
                $result = 'indeterminate';
            }


            $lotNumberVal = $rowData[$lotNumberCol];
            if (trim($rowData[$lotExpirationDateCol]) != '') {
                $timestamp = DateTime::createFromFormat('!' . $dateFormat, $rowData[$lotExpirationDateCol]);
                if (!empty($timestamp)) {
                    $timestamp = $timestamp->getTimestamp();
                    $lotExpirationDateVal = date('Y-m-d H:i', $timestamp);
                } else {
                    $lotExpirationDateVal = null;
                }
            }

            $sampleType = $rowData[$sampleTypeCol];
            if ($sampleType == 'Patient') {
                $sampleType = 'S';
            } else if ($sampleType == 'Control') {

                if ($sampleCode == 'HIV_HIPOS') {
                    $sampleType = 'HPC';
                    $sampleCode = $sampleCode . '-' . $lotNumberVal;
                } else if ($sampleCode == 'HIV_LOPOS') {
                    $sampleType = 'LPC';
                    $sampleCode = $sampleCode . '-' . $lotNumberVal;
                } else if ($sampleCode == 'HIV_NEG') {
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
                    "logVal" => ($logVal),
                    "absVal" => $absVal,
                    "absDecimalVal" => $absDecimalVal,
                    "txtVal" => $txtVal,
                    "resultFlag" => $resultFlag,
                    "testingDate" => $testingDate,
                    "sampleType" => $sampleType,
                    "batchCode" => $batchCode,
                    "lotNumber" => $lotNumberVal,
                    "result" => $result,
                    "lotExpirationDate" => $lotExpirationDateVal,
                );
            } else {
                if (isset($logVal) && trim($logVal) != "") {
                    $infoFromFile[$sampleCode]['logVal'] = trim($logVal);
                }
            }

            $m++;
        }



        $inc = 0;
        foreach ($infoFromFile as $sampleCode => $d) {
            if ($d['sampleCode'] == $d['sampleType'] . $inc) {
                $d['sampleCode'] = '';
            }
            $data = array(
                'module' => 'covid19',
                'lab_id' => base64_decode($_POST['labId']),
                'vl_test_platform' => $_POST['vltestPlatform'],
                'import_machine_name' => $_POST['configMachineName'],
                'result_reviewed_by' => $_SESSION['userId'],
                'sample_code' => $d['sampleCode'],
                'result_value_log' => $d['logVal'],
                'sample_type' => $d['sampleType'],
                'result_value_absolute' => $d['absVal'],
                'result_value_text' => $d['txtVal'],
                'result_value_absolute_decimal' => $d['absDecimalVal'],
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
            //get user name
            if (!empty($d['reviewBy'])) {

                /** @var UsersService $usersService */
                $usersService = ContainerRegistry::get(UsersService::class);
                $data['sample_review_by'] = $usersService->addUserIfNotExists($d['reviewBy']);
            }

            $query = "SELECT facility_id,vl_sample_id,result,result_value_log,result_value_absolute,result_value_text,result_value_absolute_decimal from form_vl where sample_code='" . $sampleCode . "'";
            $vlResult = $db->rawQuery($query);
            //insert sample controls
            $scQuery = "select r_sample_control_name from r_sample_controls where r_sample_control_name='" . trim($d['sampleType']) . "'";
            $scResult = $db->rawQuery($scQuery);
            if (!$scResult) {
                $scData = array('r_sample_control_name' => trim($d['sampleType']));
                $scId = $db->insert("r_sample_controls", $scData);
            }
            if ($vlResult && $sampleCode != '') {
                if ($vlResult[0]['result_value_log'] != '' || $vlResult[0]['result_value_absolute'] != '' || $vlResult[0]['result_value_text'] != '' || $vlResult[0]['result_value_absolute_decimal'] != '') {
                    $data['sample_details'] = 'Result already exists';
                } else {
                    $data['result_status'] = '7';
                }
                $data['facility_id'] = $vlResult[0]['facility_id'];
            } else {
                $data['sample_details'] = 'New Sample';
            }
            //echo "<pre>";var_dump($data);echo "</pre>";continue;
            if ($sampleCode != '' || $batchCode != '' || $sampleType != '' || $logVal != '' || $absVal != '' || $absDecimalVal != '') {
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
    $action = $_SESSION['userName'] . ' imported a new test result with the sample code ' . $sampleCode;
    $resource = 'import-results-manually';
    $general->activityLog($eventType, $action, $resource);

    //new log for update in result
    if (isset($id) && $id > 0) {
        $data = array(
            'user_id' => $_SESSION['userId'],
            'vl_sample_id' => $id,
            'test_type' => 'covid19',
            'updated_on' => DateUtility::getCurrentDateTime(),
        );
        $db->insert("log_result_updates", $data);
    }
    header("Location:/import-result/imported-results.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
