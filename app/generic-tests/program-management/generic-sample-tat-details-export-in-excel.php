<?php




use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$excel = new Spreadsheet();
$output = [];
$sheet = $excel->getActiveSheet();

$sQuery = "SELECT
vl.sample_collection_date,
vl.sample_tested_datetime,
vl.sample_received_at_lab_datetime,
vl.result_printed_datetime,
vl.remote_sample_code,
vl.external_sample_code,
vl.sample_dispatched_datetime,
vl.request_created_by,
vl.sample_code
from form_generic as vl
INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status
LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
LEFT JOIN r_generic_sample_types as s ON s.sample_type_id=vl.specimen_type
LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
WHERE
(vl.sample_collection_date is not null) AND
(vl.sample_tested_datetime is not null) AND
vl.result is not null AND vl.result != ''";

if (!empty($_SESSION['vlTatData']['sWhere'])) {
	$sQuery .= $_SESSION['vlTatData']['sWhere'];
}

if (!empty($_SESSION['vlTatData']['sOrder'])) {
	$sQuery = $sQuery . " ORDER BY " . $_SESSION['vlTatData']['sOrder'];
}
$rResult = $db->rawQuery($sQuery);

$headings = ["Sample ID", "Remote Sample ID", "External Sample ID", "Sample Collection Date", "Sample Dispatch Date", "Sample Received Date in Lab", "Sample Test Date", "Result Print Date"];

$colNo = 1;

$styleArray = [
	'font' => [
		'bold' => true,
		'size' => '13',
	],
	'alignment' => [
		'horizontal' => Alignment::HORIZONTAL_CENTER,
		'vertical' => Alignment::VERTICAL_CENTER,
	],
	'borders' => [
		'outline' => [
			'style' => Border::BORDER_THICK,
		],
	]
];

$sheet->mergeCells('A1:AE1');
$nameValue = '';
foreach ($_POST as $key => $value) {
	if (trim((string) $value) != '' && trim((string) $value) != '-- Select --') {
		$nameValue .= str_replace("_", " ", $key) . " : " . $value . "&nbsp;&nbsp;";
	}
}
$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '1')
	->setValueExplicit(html_entity_decode($nameValue));
foreach ($headings as $field => $value) {
	$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '3')
		->setValueExplicit(html_entity_decode($value));
	$colNo++;
}
$sheet->getStyle('A3:H3')->applyFromArray($styleArray);

$no = 1;
foreach ($rResult as $aRow) {
	$row = [];
	//sample collecion date
	$sampleCollectionDate = '';
	$sampleCollectionDate =  DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
	if ($aRow['sample_dispatched_datetime'] != null && trim((string) $aRow['sample_dispatched_datetime']) != '' && $aRow['sample_dispatched_datetime'] != '0000-00-00 00:00:00') {
		$sampleDispatchedDate = DateUtility::humanReadableDateFormat($aRow['sample_dispatched_datetime']);
	}
	if (isset($aRow['sample_received_at_lab_datetime']) && trim((string) $aRow['sample_received_at_lab_datetime']) != '' && $aRow['sample_received_at_lab_datetime'] != '0000-00-00 00:00:00') {
		$sampleRecievedDate = DateUtility::humanReadableDateFormat($aRow['sample_received_at_lab_datetime']);
	} else {
		$sampleRecievedDate = '';
	}
	$testDate = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime'] ?? '');
	$printDate = DateUtility::humanReadableDateFormat($aRow['result_printed_datetime'] ?? '');


	$row[] = $aRow['sample_code'];
	$row[] = $aRow['remote_sample_code'];
	$row[] = $aRow['external_sample_code'];
	$row[] = $sampleCollectionDate;
	$row[] = $sampleDispatchedDate;
	$row[] = $sampleRecievedDate;
	$row[] = $testDate;
	$row[] = $printDate;
	$output[] = $row;
	$no++;
}

$start = (count($output)) + 2;
foreach ($output as $rowNo => $rowData) {
	$colNo = 1;
	$rRowCount = $rowNo + 4;
	foreach ($rowData as $field => $value) {
		$sheet->setCellValue(
			Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
			html_entity_decode((string) $value)
		);
		$colNo++;
	}
}
$writer = IOFactory::createWriter($excel, IOFactory::READER_XLSX);
$filename = 'VLSM-LAB-TESTS-TAT-Report-' . date('d-M-Y-H-i-s') . '.xlsx';
$writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
echo urlencode(basename($filename));
