<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

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
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$sQuery = "select vl.sample_collection_date,vl.sample_tested_datetime,vl.sample_received_at_lab_datetime,vl.result_printed_datetime,vl.result_mail_datetime,vl.request_created_by,vl.sample_code, vl.remote_sample_code,vl.result_printed_on_lis_datetime,vl.result_printed_on_sts_datetime from form_eid as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where (vl.sample_collection_date > '1970-01-01' AND DATE(vl.sample_collection_date) NOT LIKE '0000-00-00')
                        AND (vl.sample_tested_datetime > '1970-01-01' AND DATE(vl.sample_tested_datetime) NOT LIKE '0000-00-00')
                        AND vl.result is not null
                        AND vl.result != '' ";

if (!empty($_SESSION['eidTatData']['sWhere'])) {
	$sQuery = $sQuery . " AND " . $_SESSION['eidTatData']['sWhere'];
}

if (!empty($_SESSION['eidTatData']['sOrder'])) {
	$sQuery = $sQuery . " ORDER BY " . $_SESSION['eidTatData']['sOrder'];
}
$rResult = $db->rawQuery($sQuery);

$excel = new Spreadsheet();
$output = [];
$sheet = $excel->getActiveSheet();

$headings = array("EID Sample ID", "Sample Collection Date", "Sample Received Date in Lab", "Sample Test Date", "Sample Print Date", "Sample Email Date", "STS Result Print Date", "LIS Result Print Date");

$colNo = 1;

$styleArray = array(
	'font' => array(
		'bold' => true,
		'size' => '13',
	),
	'alignment' => array(
		'horizontal' => Alignment::HORIZONTAL_CENTER,
		'vertical' => Alignment::VERTICAL_CENTER,
	),
	'borders' => array(
		'outline' => array(
			'style' => Border::BORDER_THICK,
		),
	)
);

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
	$sampleRecievedDate = DateUtility::humanReadableDateFormat($aRow['sample_received_at_lab_datetime'] ?? '');
	$testDate = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime'] ?? '');
	$printDate = DateUtility::humanReadableDateFormat($aRow['result_printed_datetime'] ?? '');
	$mailDate = DateUtility::humanReadableDateFormat($aRow['result_mail_datetime'] ?? '');
	$printDateSts = DateUtility::humanReadableDateFormat($aRow['result_printed_on_sts_datetime'] ?? '');
	$printDateLis = DateUtility::humanReadableDateFormat($aRow['result_printed_on_lis_datetime'] ?? '');

	$row[] = $aRow['sample_code'];
	$row[] = $sampleCollectionDate;
	$row[] = $sampleRecievedDate;
	$row[] = $testDate;
	$row[] = $printDate;
	$row[] = $mailDate;
	$row[] = $printDateSts;
	$row[] = $printDateLis;
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
$filename = 'VLSM-EID-TAT-Report-' . date('d-M-Y-H-i-s') . '.xlsx';
$writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
echo base64_encode(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
