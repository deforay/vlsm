<?php

use App\Services\VlService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/** @var VlService $vlService */
$vlService = ContainerRegistry::get(VlService::class);


// Define the style array for border
$styleArray = [
     'borders' => [
          'allBorders' => [
               'borderStyle' => Border::BORDER_THIN,
               'color' => ['argb' => '000000'],
          ],
     ],
     'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_LEFT,
          'vertical' => Alignment::VERTICAL_CENTER,
     ],
];

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$keyFromGlobalConfig = $general->getGlobalConfig('key');

/** @var DateUtility $dateTimeUtil */
$dateTimeUtil = new DateUtility();
$headerStyleArray = [
     'font' => [
          'bold' => true,
          'size' => '13',
     ],
     'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical' => Alignment::VERTICAL_CENTER,
     ]
];
$sQuery = "SELECT
               vl.patient_art_no,
               DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y') as sampleDate,
               f.facility_name,
               f.facility_code,
               s.sample_name,
               i.machine_name,
               l.facility_name as labName,
               vl.patient_age_in_years,
               UCASE(vl.patient_gender),
               UCASE(vl.is_patient_pregnant),
               UCASE(vl.is_patient_breastfeeding),
               vl.is_encrypted,
               DATE_FORMAT(vl.treatment_initiated_date,'%d-%b-%Y') as artStartDate,
               vl.current_regimen,
               DATE_FORMAT(vl.date_of_initiation_of_current_regimen,'%d-%b-%Y') as regStartDate,
               vl.result
          FROM form_vl as vl
          LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
          LEFT JOIN r_vl_sample_type as s ON vl.sample_type=s.sample_id
          LEFT JOIN instruments as i ON vl.vl_test_platform=i.machine_name
          INNER JOIN facility_details as l ON vl.lab_id=l.facility_id";

$sWhere[] =  " vl.vl_result_category = 'not suppressed' AND vl.patient_age_in_years IS NOT NULL AND vl.patient_gender IS NOT NULL AND vl.current_regimen IS NOT NULL ";

/* State filter */
if (isset($_POST['state']) && trim($_POST['state']) != '') {
     $sWhere[] = " f.facility_state_id = '" . $_POST['state'] . "' ";
}

/* District filters */
if (isset($_POST['district']) && trim($_POST['district']) != '') {
     $sWhere[] = " f.facility_district_id = '" . $_POST['district'] . "' ";
}
/* Facility filter */
if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
     $sWhere[] =  ' f.facility_id IN (' . $_POST['facilityName'] . ')';
}

if (isset($_POST['gender']) && $_POST['gender'] != '') {
     $sWhere[] = ' vl.patient_gender = "' . $_POST['gender'] . '"';
 }
 

if (isset($_POST['pregnancy']) && trim($_POST['pregnancy']) != '') {
     $sWhere[] = " vl.is_patient_pregnant = '" . $_POST['pregnancy'] . "' ";
}

if (isset($_POST['breastfeeding']) && trim($_POST['breastfeeding']) != '') {
     $sWhere[] = " vl.is_patient_breastfeeding = '" . $_POST['breastfeeding'] . "' ";
}

if (isset($_POST['minAge']) && isset($_POST['maxAge'])) {
     if(is_numeric($_POST['minAge']) && is_numeric($_POST['maxAge']) && ($_POST['maxAge'] >= $_POST['minAge']))
     {
          $sWhere[] = " vl.patient_age_in_years BETWEEN " . $_POST['minAge'] . " AND ".$_POST['maxAge']."";
     }
     else{
          echo "age"; exit();
     }
}

/* Sample collection date filter */
$sampleCollectionDate = $dateTimeUtil->convertDateRange($_POST['sampleCollectionDate']);
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     if (trim($sampleCollectionDate[0]) == trim($sampleCollectionDate[1])) {
          $sWhere[] =  '  DATE(vl.sample_collection_date) = "' . $sampleCollectionDate[0] . '"';
     } else {
          $sWhere[] =  '  DATE(vl.sample_collection_date) >= "' . $sampleCollectionDate[0] . '" AND DATE(vl.sample_collection_date) <= "' . $sampleCollectionDate[1] . '"';
     }
}
/* Sample test date filter */
$sampleTestDate = $dateTimeUtil->convertDateRange($_POST['sampleTestDate']);
if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
     if (trim($sampleTestDate[0]) == trim($sampleTestDate[1])) {
          $sWhere[] = '  DATE(vl.sample_tested_datetime) = "' . $sampleTestDate[0] . '"';
     } else {
          $sWhere[] =  '  DATE(vl.sample_tested_datetime) >= "' . $sampleTestDate[0] . '" AND DATE(vl.sample_tested_datetime) <= "' . $sampleTestDate[1] . '"';
     }
}

if (!empty($_SESSION['facilityMap'])) {
     $sWhere[] =  "  vl.facility_id IN (" . $_SESSION['facilityMap'] . ")   ";
}

if (!empty($sWhere)) {
     $sWhere = implode(" AND ", $sWhere);
     $sQuery = $sQuery . ' WHERE ' . $sWhere;
}
$sQuery = $sQuery . " ORDER BY f.facility_name asc, patient_art_no asc, sample_collection_date asc";

$rResult = $db->rawQuery($sQuery);

if (!$rResult) {
     return null;
}

// Separate the data into two arrays
$vfData = [];
$vlnsData = [];


$patientIds = [];
$output = [];
$headings = [
     'Patient ID',
     'Sample Date',
     'Facility Name',
     'Facility Code',
     'Sample Name',
     'Testing Platform',
     'Lab Name',
     'Age',
     'Gender',
     'Pregnant',
     'Breastfeeding',
     'ART Start Date',
     'Regimen',
     'Current Regimen Start Date',
     'VL Result'
];
// Separate the data into two arrays
$vfData = [];
$vlnsData = [];
$patientIds = [];
foreach ($rResult as $aRow) {

     if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] === 'yes') {
          $aRow['patient_art_no'] = CommonService::decrypt($aRow['patient_art_no'], base64_decode($keyFromGlobalConfig));
     }
     unset($aRow['is_encrypted']);
     $patientId = trim($aRow['patient_art_no']);
     $aRow['result'] = $vlService->extractViralLoadValue($aRow['result']);
     $vfData[] = $aRow;
     $vlnsData[] = $aRow;
     // Check if patient id already there in array
     if (in_array(trim($patientId), $patientIds)) {
          // If there we remove vlsndata for this dublication
          foreach ($vlnsData as $key => $vlnsDataRow) {
               if (trim($vlnsDataRow['patient_art_no']) === trim($patientId)) {
                    unset($vlnsData[$key]);
               }
          }
     } else {
          $patientIds[] = trim($patientId);
     }
}
foreach ($vlnsData as $vlnsKey => $vlnsDataRow) {
     foreach ($vfData as $key => $vfDataRow) {
          if ($vfDataRow['patient_art_no'] === $vlnsDataRow['patient_art_no']) {
               unset($vfData[$key]);
          }
     }
}

$vfData = array_combine(range(1, count($vfData)), array_values($vfData));
$vlnsData = array_combine(range(1, count($vlnsData)), array_values($vlnsData));
$colNo = 1;
$vlnsColNo = 1;
$excel = new Spreadsheet();
$vlnsSheet = $excel->getActiveSheet();
$vfSheet = $excel->createSheet();

$vfSheet->setTitle('Virologic Failure');
$vlnsSheet->setTitle('VL - Not Suppressed');
foreach ($headings as $field => $value) {
     $vfSheet->setCellValue(Coordinate::stringFromColumnIndex($colNo) . '1', html_entity_decode($value));
     $colNo++;
     $vlnsSheet->setCellValue(Coordinate::stringFromColumnIndex($vlnsColNo) . '1', html_entity_decode($value));
     $vlnsColNo++;
}
$vfSheet->getStyle('A1:O1')->applyFromArray($headerStyleArray);
$currentPatientId = null;
$startRow = 2; // Start from the second row as the first row is the header
foreach ($vfData as $rowNo => $rowData) {
     $colNo = 1;
     $rRowCount = $rowNo + 1; // +2 because Excel rows are 1-indexed and the header row
     foreach ($rowData as $field => $value) {
          $vfSheet->setCellValue(
               Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
               html_entity_decode($value)
          );
          $colNo++;
     }
     // If the patient ID changes, merge the cells of the previous patient and update the start row and current patient ID
     if ($rowData['patient_art_no'] !== $currentPatientId && $currentPatientId !== null) {
          $vfSheet->mergeCells('A' . $startRow . ':A' . ($rRowCount - 1));
          $startRow = $rRowCount;
     }
     $currentPatientId = $rowData['patient_art_no'];
}
// Merge the cells of the last patient
$vfSheet->mergeCells('A' . $startRow . ':A' . ($rRowCount ?? ($startRow + 1)));


// Get the highest row and column numbers
$highestRow = $vfSheet->getHighestRow(); // e.g. 10
$highestCol = $vfSheet->getHighestColumn(); // e.g 'F'

// Apply the border style to all cells
$vfSheet->getStyle('A1:' . $highestCol . $highestRow)->applyFromArray($styleArray);

foreach (range('A', 'O') as $columnID) {
     $vfSheet->getColumnDimension($columnID)->setAutoSize(true);
}

$vlnsSheet->getStyle('A1:O1')->applyFromArray($headerStyleArray);
foreach ($vlnsData as $rowNo => $rowData) {
     $colNo = 1;
     $rRowCount = $rowNo + 1;
     foreach ($rowData as $field => $value) {
          $vlnsSheet->setCellValue(
               Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
               html_entity_decode($value)
          );
          $colNo++;
     }
}


// Get the highest row and column numbers
$highestRow = $vlnsSheet->getHighestRow(); // e.g. 10
$highestCol = $vlnsSheet->getHighestColumn(); // e.g 'F'

$vlnsSheet->getStyle('A1:' . $highestCol . $highestRow)->applyFromArray($styleArray);

foreach (range('A', 'O') as $columnID) {
     $vlnsSheet->getColumnDimension($columnID)->setAutoSize(true);
}

$writer = IOFactory::createWriter($excel, IOFactory::READER_XLSX);
$filename = TEMP_PATH . DIRECTORY_SEPARATOR . 'VLSM-HIGH-VL-AND-VIROLOGIC-FAILURE-REPORT-' . date('d-M-Y-H-i-s') . '-' . $general->generateRandomString(5) . '.xlsx';
$writer->save($filename);
echo base64_encode($filename);
