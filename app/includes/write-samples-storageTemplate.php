<?php

use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

ini_set('memory_limit', -1);
set_time_limit(0);
ini_set('max_execution_time', 20000);

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody(), nullifyEmptyStrings: true);

if (!empty($_POST['batchOrManifestCodeValue'])) {

    // Define paths
    $originalFile = WEB_ROOT . '/files/storage/Storage_Bulk_Upload_Excel_Format.xlsx';
    $fileName = 'Storage_Bulk_Upload_Excel_Format_' . time() . '.xlsx';
    $tempFile = TEMP_PATH . DIRECTORY_SEPARATOR . $fileName;

    // Copy original file to a temporary location
    if (copy($originalFile, $tempFile)) {
        $condition = ' where pd.package_code like "' . $_POST['batchOrManifestCodeValue'] . '" OR b.batch_code like "' . $_POST['batchOrManifestCodeValue'] . '"';

        $query = "SELECT  vl.sample_code,vl.patient_art_no  FROM form_vl as vl
                    LEFT JOIN package_details as pd ON vl.sample_package_code = pd.package_code
                    LEFT JOIN batch_details as b ON b.batch_id = vl.sample_batch_id " . $condition;

        $sampleResult = $db->rawQuery($query);

        $spreadsheet = IOFactory::load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();

        if (!empty($sampleResult)) {

            foreach ($sampleResult as $rowNo => $data) {
                $rRowCount = $rowNo + 2;
                $sheet->fromArray($data, null, 'A' . $rRowCount);
            }

            $writer = IOFactory::createWriter($spreadsheet, IOFactory::READER_XLSX);
            $writer->save($tempFile);

            // Return the path to the temporary file for download
            echo '/temporary/' . $fileName;
        } else {
            echo false;
        }
    } else {
        echo false;
    }
} else {
    echo '/files/storage/Storage_Bulk_Upload_Excel_Format.xlsx';
}
