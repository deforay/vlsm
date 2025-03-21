<?php

use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;
use PhpOffice\PhpSpreadsheet\IOFactory;




$arr = [];
/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

$tableName = "form_covid19";
$testTableName = 'covid19_tests';
// echo "<pre>";print_r($_FILES);die;
try {
    $arr = $general->getGlobalConfig();

    $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', (string) $_FILES['requestFile']['name']);
    $fileName = str_replace(" ", "-", $fileName);
    $ranNumber = MiscUtility::generateRandomString(12);
    $extension = MiscUtility::getFileExtension($fileName);
    $fileName = $ranNumber . "." . $extension;

    MiscUtility::makeDirectory(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request");

    if (move_uploaded_file($_FILES['requestFile']['tmp_name'], TEMP_PATH . DIRECTORY_SEPARATOR . "import-request" . DIRECTORY_SEPARATOR . $fileName)) {

        $file_info = new finfo(FILEINFO_MIME); // object oriented approach!
        $mime_type = $file_info->buffer(file_get_contents(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request" . DIRECTORY_SEPARATOR . $fileName)); // e.g. gives "image/jpeg"

        $spreadsheet = IOFactory::load(TEMP_PATH . DIRECTORY_SEPARATOR . "import-request" . DIRECTORY_SEPARATOR . $fileName);
        $sheetData   = $spreadsheet->getActiveSheet();
        $sheetData   = $sheetData->toArray(null, true, true, true);
        $returnArray = [];
        $resultArray = array_slice($sheetData, 1);

        foreach ($resultArray as $rowIndex => $rowData) {
            // echo "<pre>";print_r($rowData);die;
            if (!empty($rowData['A'])) {
                $sampleCode = $general->getDataFromOneFieldAndValue('form_covid19', 'sample_code', $rowData['A']);

                $facility = $general->getDataFromOneFieldAndValue('facility_details', 'facility_name', $rowData['D']);
                $testReason = $general->getDataFromOneFieldAndValue('r_covid19_test_reasons', 'test_reason_name', $rowData['R']);
                $sampleType = $general->getDataFromOneFieldAndValue('r_covid19_sample_type', 'sample_name', $rowData['T']);
                $labName = $general->getDataFromOneFieldAndValue('facility_details', 'facility_name', $rowData['W'], 'facility_type = 2');
                $rejectionReason = $general->getDataFromOneFieldAndValue('r_covid19_sample_rejection_reasons', 'rejection_reason_name', $rowData['Y']);

                $result = $general->getDataFromOneFieldAndValue('r_covid19_results', 'result', $rowData['AI']);
                $resultStatus = $general->getDataFromOneFieldAndValue('r_sample_status', 'status_name', $rowData['AM']);
                $labTechnician = $usersService->getOrCreateUser($rowData['AP']);

                if (trim((string) $rowData['S']) != '') {
                    $sampleCollectionDate = date('Y-m-d H:i:s', strtotime((string) $rowData['S']));
                } else {
                    $sampleCollectionDate = null;
                }

                if (trim((string) $rowData['V']) != '') {
                    $sampleReceivedDate = date('Y-m-d H:i:s', strtotime((string) $rowData['V']));
                } else {
                    $sampleReceivedDate = null;
                }

                $data = array(
                    'sample_code'                           => $rowData['A'],
                    'vlsm_country_id'                       => $arr['vl_form'],
                    'source_of_alert'                       => (isset($rowData['B']) && $rowData['B'] != "") ? strtolower(str_replace(" ", "-", (string) $rowData['B'])) : null,
                    'source_of_alert_other'                 => $rowData['C'],
                    'facility_id'                           => $facility['facility_id'] ?? null,
                    'patient_name'                          => $rowData['E'],
                    'patient_id'                            => $rowData['F'],
                    'external_sample_code'                  => $rowData['G'],
                    'patient_dob'                           => date('Y-m-d', strtotime((string) $rowData['H'])),
                    'patient_age'                           => $rowData['I'],
                    'patient_gender'                        => strtolower((string) $rowData['J']),
                    'patient_phone_number'                  => $rowData['K'],
                    'patient_address'                       => $rowData['L'],
                    'patient_province'                      => $rowData['M'],
                    'patient_district'                      => $rowData['N'],
                    'patient_city'                          => $rowData['O'],
                    'patient_nationality'                   => $rowData['P'],
                    'type_of_test_requested'                => $rowData['Q'],
                    'reason_for_covid19_test'               => $testReason['test_reason_id'] ?? null,
                    'sample_collection_date'                => $sampleCollectionDate,
                    'specimen_type'                         => $sampleType['sample_id'] ?? null,
                    'test_number'                           => $rowData['U'],
                    'sample_received_at_lab_datetime'    => $sampleReceivedDate,
                    'lab_id'                                => $labName['facility_id'] ?? null,
                    'is_sample_rejected'                    => strtolower((string) $rowData['X']),
                    'reason_for_sample_rejection'           => (isset($rejectionReason['rejection_reason_id']) && $rejectionReason['rejection_reason_id'] != "") ? $rejectionReason['rejection_reason_id'] : 9999,
                    'rejection_on'                          => date('Y-m-d', strtotime((string) $rowData['Z'])),
                    'result'                                => $result['result_id'],
                    'is_result_authorised'                  => strtolower((string) $rowData['AJ']),
                    'authorized_by'                         => ($rowData['AK']),
                    'authorized_on'                         => date('Y-m-d', strtotime((string) $rowData['AL'])),
                    'last_modified_datetime'                => DateUtility::getCurrentDateTime(),
                    'last_modified_by'                      => $_SESSION['userId'],
                    'result_status'                         => $resultStatus['status_id'] ?? null,
                    'sample_condition'                      => strtolower((string) $rowData['AN']),
                    'patient_passport_number'               => $rowData['A0'],
                    'lab_technician'                        => $labTechnician ?? null,
                );

                if (empty($sampleCode)) {
                    $lastId = $db->insert($tableName, $data);
                } else {
                    $lastId = $sampleCode['covid19_id'];
                    $db->where('covid19_id', $lastId);
                    $db->update($tableName, $data);
                }

                $testData[0]['testRequest']     = $rowData['AA'];
                $testData[0]['testDate']        = $rowData['AB'];
                $testData[0]['testingPlatform'] = $rowData['AC'];
                $testData[0]['testResult']      = $rowData['AD'];
                $testData[1]['testRequest']     = $rowData['AE'];
                $testData[1]['testDate']        = $rowData['AF'];
                $testData[1]['testingPlatform'] = $rowData['AG'];
                $testData[1]['testResult']      = $rowData['AH'];
                if (!empty($testData)) {
                    /* Delete if already exist */
                    $db->where('covid19_id', $lastId);
                    $db->delete($testTableName);

                    foreach ($testData as $testKitName) {
                        $covid19TestData = array(
                            'covid19_id'            => $lastId,
                            'test_name'             => $testKitName['testRequest'],
                            'facility_id'           => $labName['facility_id'] ?? null,
                            'testing_platform'      => $testKitName['testingPlatform'],
                            'sample_tested_datetime' => DateUtility::isoDateFormat($testKitName['testDate'] ?? '', true),
                            'result'                => strtolower((string) $testKitName['testResult']),
                        );
                        $db->insert($testTableName, $covid19TestData);
                        $covid19Data['sample_tested_datetime'] = DateUtility::isoDateFormat($testKitName['testDate'] ?? '', true);
                    }
                }
                $db->where('covid19_id', $lastId);
                $id = $db->update($tableName, $covid19Data);
            }
        }
        $_SESSION['alertMsg'] = "Data imported successfully";
    }
    // echo "<pre>";print_r($returnArray);die;
    header("Location:/covid-19/requests/covid-19-requests.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
}
