<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Utilities\MiscUtility;
use App\Services\DatabaseService;
use App\Services\HepatitisService;
use App\Registries\ContainerRegistry;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var HepatitisService $hepatitisService */
$hepatitisService = ContainerRegistry::get(HepatitisService::class);

$hepatitisResults = $hepatitisService->getHepatitisResults();

$arr = $general->getGlobalConfig();

$delimiter = $arr['default_csv_delimiter'] ?? ',';
$enclosure = $arr['default_csv_enclosure'] ?? '"';

if (isset($_SESSION['hepatitisResultQuery']) && trim($_SESSION['hepatitisResultQuery']) != "") {

	$headings = array("S.No.", "Sample ID", "Health Facility Name", "Health Facility Code", "District/County", "Province/State", "Patient ID", "Patient Name", "Patient DoB", "Patient Age", "Patient Gender", "Sample Collection Date", "Is Sample Rejected?", "Rejection Reason", "Sample Tested On", "Result", "Sample Received On", "Date Result Dispatched", "Result Status", "Comments", "Funding Source", "Implementing Partner");
	$output = [];

	$no = 1;
	foreach ($db->rawQueryGenerator($_SESSION['hepatitisResultQuery']) as $aRow) {
		$row = [];
		//date of birth
		$dob = '';
		if ($aRow['patient_dob'] != null && trim($aRow['patient_dob']) != '' && $aRow['patient_dob'] != '0000-00-00') {
			$dob =  date("d-m-Y", strtotime($aRow['patient_dob']));
		}
		//set gender
		switch (strtolower($aRow['patient_gender'])) {
			case 'male':
			case 'm':
				$gender = 'M';
				break;
			case 'female':
			case 'f':
				$gender = 'F';
				break;
			case 'not_recorded':
			case 'notrecorded':
			case 'unreported':
				$gender = 'Unreported';
				break;
			default:
				$gender = '';
				break;
		}

		$sampleRejection = ($aRow['is_sample_rejected'] == 'yes' || ($aRow['reason_for_sample_rejection'] != null && $aRow['reason_for_sample_rejection'] > 0)) ? 'Yes' : 'No';

		if ($_SESSION['instanceType'] == 'remoteuser') {
			$sampleCode = 'remote_sample_code';
		} else {
			$sampleCode = 'sample_code';
		}

		if (!empty($aRow['patient_name'])) {
			$patientFname = ($general->crypto('doNothing', $aRow['patient_name'], $aRow['patient_id']));
		} else {
			$patientFname = '';
		}
		if ($aRow['patient_last_name'] != '') {
			$patientLname = ($general->crypto('doNothing', $aRow['patient_surname'], $aRow['patient_id']));
		} else {
			$patientLname = '';
		}

		if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] == 'yes') {
			$key = base64_decode($general->getGlobalConfig('key'));
			$aRow['patient_id'] = $general->crypto('decrypt', $aRow['patient_id'], $key);
			$patientFname = $general->crypto('decrypt', $patientFname, $key);
			$patientLname = $general->crypto('decrypt', $patientLname, $key);
		}

		$row[] = $no;
		$row[] = $aRow[$sampleCode];
		$row[] = ($aRow['facility_name']);
		$row[] = $aRow['facility_code'];
		$row[] = ($aRow['facility_district']);
		$row[] = ($aRow['facility_state']);
		$row[] = $aRow['patient_id'];
		$row[] = $patientFname . " " . $patientLname;
		$row[] = $dob;
		$aRow['patient_age'] ??= 0;
		$row[] = ($aRow['patient_age'] > 0) ? $aRow['patient_age'] : 0;
		$row[] = $gender;
		$row[] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
		$row[] = $sampleRejection;
		$row[] = $aRow['rejection_reason'];
		$row[] = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime']);
		$row[] = $hepatitisResults[$aRow['result']] ?? $aRow['result'];
		$row[] = DateUtility::humanReadableDateFormat($aRow['sample_received_at_lab_datetime']);
		$row[] = DateUtility::humanReadableDateFormat($aRow['result_printed_datetime']);
		$row[] = $aRow['status_name'];
		$row[] = ($aRow['lab_tech_comments']);
		$row[] = $aRow['funding_source_name'] ?? null;
		$row[] = $aRow['i_partner_name'] ?? null;
		$output[] = $row;
		$no++;
	}


	if (isset($_SESSION['hepatitisResultQueryCount']) && $_SESSION['hepatitisResultQueryCount'] > 75000) {

				$fileName = TEMP_PATH . DIRECTORY_SEPARATOR . 'Hepatitis-Export-Data-' . date('d-M-Y-H-i-s') . '.csv';
				$fileName = MiscUtility::generateCsv($headings, $output, $fileName, $delimiter, $enclosure);
				// we dont need the $output variable anymore
				unset($output);
				echo base64_encode($fileName);
			} else {
				$excel = new Spreadsheet();
				$sheet = $excel->getActiveSheet();
				$sheet->fromArray($headings, null, 'A3');

				foreach ($output as $rowNo => $rowData) {
					$rRowCount = $rowNo + 4;
					$sheet->fromArray($rowData, null, 'A' . $rRowCount);
				}

		$writer = IOFactory::createWriter($excel, IOFactory::READER_XLSX);
		$fileName = TEMP_PATH . DIRECTORY_SEPARATOR . 'Hepatitis-Export-Data-' . date('d-M-Y-H-i-s') . '.xlsx';
		$writer->save($fileName);
		echo base64_encode($fileName);
	}
}
