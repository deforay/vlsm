<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ob_start();
$general = new \App\Models\General();
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$output = array();
$sheet = $excel->getActiveSheet();

$sQuery = "SELECT vl.sample_collection_date,vl.sample_tested_datetime,vl.sample_received_at_vl_lab_datetime,vl.result_printed_datetime,vl.remote_sample_code,vl.external_sample_code,vl.sample_dispatched_datetime,vl.request_created_by, vl.sample_code from form_vl as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where (vl.sample_collection_date is not null AND vl.sample_collection_date not like '' AND DATE(vl.sample_collection_date) > '1970-01-01')
                        AND (vl.sample_tested_datetime is not null AND vl.sample_tested_datetime not like '' AND DATE(vl.sample_tested_datetime) !='1970-01-01' AND DATE(vl.sample_tested_datetime) NOT LIKE '0000-00-00')
                        AND vl.result is not null
                        AND vl.result != ''";

if (isset($_SESSION['vlTatData']['sWhere']) && !empty($_SESSION['vlTatData']['sWhere'])) {
	$sQuery = $sQuery . $_SESSION['vlTatData']['sWhere'];
}

if (isset($_SESSION['vlTatData']['sOrder']) && !empty($_SESSION['vlTatData']['sOrder'])) {
	$sQuery = $sQuery . " ORDER BY " . $_SESSION['vlTatData']['sOrder'] ;
}
$rResult = $db->rawQuery($sQuery);

$headings = array("Sample Id", "Remote Sample Code","External Sample Code","Sample Collection Date", "Sample Dispatch Date","Sample Received Date in Lab", "Sample Test Date", "Sample Print Date");

$colNo = 1;

$styleArray = array(
	'font' => array(
		'bold' => true,
		'size' => '13',
	),
	'alignment' => array(
		'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
		'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
	),
	'borders' => array(
		'outline' => array(
			'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
		),
	)
);

$borderStyle = array(
	'alignment' => array(
		'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
	),
	'borders' => array(
		'outline' => array(
			'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		),
	)
);

$sheet->mergeCells('A1:AE1');
$nameValue = '';
foreach ($_POST as $key => $value) {
	if (trim($value) != '' && trim($value) != '-- Select --') {
		$nameValue .= str_replace("_", " ", $key) . " : " . $value . "&nbsp;&nbsp;";
	}
}
$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '1')
		->setValueExplicit(html_entity_decode($nameValue), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
foreach ($headings as $field => $value) {
	$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '3')
				->setValueExplicit(html_entity_decode($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
	$colNo++;
}
$sheet->getStyle('A3:H3')->applyFromArray($styleArray);

$no = 1;
foreach ($rResult as $aRow) {
	$row = array();
	//sample collecion date
	$sampleCollectionDate = '';
	if ($aRow['sample_collection_date'] != null && trim($aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
		$expStr = explode(" ", $aRow['sample_collection_date']);
		$sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
	}
	if ($aRow['sample_dispatched_datetime'] != null && trim($aRow['sample_dispatched_datetime']) != '' && $aRow['sample_dispatched_datetime'] != '0000-00-00 00:00:00') {
		$expStr = explode(" ", $aRow['sample_dispatched_datetime']);
		$sampleDispatchedDate =  date("d-m-Y", strtotime($expStr[0]));
	}
	if (isset($aRow['sample_received_at_vl_lab_datetime']) && trim($aRow['sample_received_at_vl_lab_datetime']) != '' && $aRow['sample_received_at_vl_lab_datetime'] != '0000-00-00 00:00:00') {
		$xplodDate = explode(" ", $aRow['sample_received_at_vl_lab_datetime']);
		$sampleRecievedDate = $general->humanReadableDateFormat($xplodDate[0]);
	} else {
		$sampleRecievedDate = '';
	}
	if (isset($aRow['sample_tested_datetime']) && trim($aRow['sample_tested_datetime']) != '' && $aRow['sample_tested_datetime'] != '0000-00-00 00:00:00') {
		$xplodDate = explode(" ", $aRow['sample_tested_datetime']);
		$testDate = $general->humanReadableDateFormat($xplodDate[0]);
	} else {
		$testDate = '';
	}
	if (isset($aRow['result_printed_datetime']) && trim($aRow['result_printed_datetime']) != '' && $aRow['result_printed_datetime'] != '0000-00-00 00:00:00') {
		$xplodDate = explode(" ", $aRow['result_printed_datetime']);
		$printDate = $general->humanReadableDateFormat($xplodDate[0]);
	} else {
		$printDate = '';
	}
	

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
			html_entity_decode($value)
		);
		$colNo++;
	}
}
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
$filename = 'VLSM-TAT-Report-' . date('d-M-Y-H-i-s') . '.xlsx';
$writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
echo base64_encode(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
