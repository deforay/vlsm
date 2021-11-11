<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
#require_once('../../startup.php');   

$general = new \Vlsm\Models\General();

$country = $general->getGlobalConfig('vl_form');
$sarr = $general->getSystemConfig();

if (isset($_POST['reportedDate']) && trim($_POST['reportedDate']) != '') {
    $s_t_date = explode("to", $_POST['reportedDate']);
    if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
        $start_date = $general->dateFormat(trim($s_t_date[0]));
    }
    if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
        $end_date = $general->dateFormat(trim($s_t_date[1]));
    }
}



//excel code start
$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $excel->getActiveSheet();
$headingStyle = array(
    'font' => array(
        'bold' => true,
        'size' => '11',
    ),
    'alignment' => array(
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ),
);
$backgroundTitleStyle = array(
    'font' => array(
        'bold' => true,
        'size' => '11',
    ),
    'alignment' => array(
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ),
    'fill' => array(
        'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFF00'),
    ),
);
$backgroundFieldStyle = array(
    'font' => array(
        'bold' => false,
        'size' => '11',
    ),
    'alignment' => array(
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
    ),
);
$styleArray = array(
    'font' => array(
        'bold' => true,
        'size' => '13',
    ),
    'alignment' => array(
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ),
    'borders' => array(
        'outline' => array(
            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ),
    ),
);

$borderStyle = array(
    'alignment' => array(
        //  'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ),
    'borders' => array(
        'outline' => array(
            'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ),
    ),
);

if (isset($_POST['lab']) && trim($_POST['lab']) != '') {
    $labId = ($_POST['lab']);
} else if ($sarr['sc_user_type'] == 'vluser') {
    $labId = ($sarr['sc_testing_lab_id']);
}

//Statistics sheet start
$sheet->mergeCells('A2:A3');
$sheet->mergeCells('B2:B3');
$sheet->mergeCells('C2:C3');
$sheet->mergeCells('D2:D3');
$sheet->mergeCells('E2:E3');
$sheet->mergeCells('F2:F3');
$sheet->mergeCells('G2:H2');
$sheet->mergeCells('I2:L2');
$sheet->mergeCells('M2:N2');
$sheet->mergeCells('O2:P2');
$sheet->mergeCells('Q2:R2');
$sheet->mergeCells('S2:S3');
$sheet->mergeCells('T2:T3');


$c = 0;

$sQuery = "SELECT
		        vl.lab_id, 
                vl.facility_id,
                f.facility_code,
                f.facility_state,
                f.facility_district,
                f.facility_name, 
                lab.facility_name as lab_name,

		SUM(CASE
			WHEN (reason_for_sample_rejection IS NOT NULL AND reason_for_sample_rejection!= '' AND reason_for_sample_rejection!= 0) THEN 1
		        ELSE 0
		           END) AS rejections,
		SUM(CASE
			WHEN ((patient_age_in_years >= 0 AND patient_age_in_years <= 15) AND ((vl.vl_result_category like 'suppressed') AND vl.result IS NOT NULL AND vl.result!= '' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
		             ELSE 0
		           END) AS lt15suppressed,
		SUM(CASE
             WHEN ((patient_age_in_years >= 0 AND patient_age_in_years <= 15) AND vl.result IS NOT NULL AND vl.result!= '' AND vl.vl_result_category like 'suppressed' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00') THEN 1
             ELSE 0
           END) AS lt15NotSuppressed,
		SUM(CASE
             WHEN (patient_age_in_years > 15 AND patient_gender IN ('m','male','M','MALE') AND ((vl.vl_result_category like 'suppressed') AND vl.result IS NOT NULL AND vl.result!= '' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
             ELSE 0
           END) AS gt15suppressedM,
		SUM(CASE
             WHEN (patient_age_in_years > 15 AND patient_gender IN ('m','male','M','MALE') AND vl.result IS NOT NULL AND vl.result!= '' AND vl.vl_result_category like 'suppressed' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00') THEN 1
             ELSE 0
           END) AS gt15NotSuppressedM,
		SUM(CASE
             WHEN (patient_age_in_years > 15 AND patient_gender IN ('f','female','F','FEMALE') AND ((vl.vl_result_category like 'suppressed') AND vl.result IS NOT NULL AND vl.result!= '' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
             ELSE 0
           END) AS gt15suppressedF,
		SUM(CASE
             WHEN (patient_age_in_years > 15 AND patient_gender IN ('f','female','F','FEMALE') AND vl.result IS NOT NULL AND vl.result!= '' AND vl.vl_result_category like 'suppressed' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00') THEN 1
             ELSE 0
           END) AS gt15NotSuppressedF,
		SUM(CASE
             WHEN ((is_patient_pregnant ='Yes' OR is_patient_pregnant ='YES' OR is_patient_pregnant ='yes' OR is_patient_breastfeeding ='Yes' OR is_patient_breastfeeding ='YES' OR is_patient_breastfeeding ='yes') AND ((vl.vl_result_category like 'suppressed') AND vl.result IS NOT NULL AND vl.result!= '' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
             ELSE 0
           END) AS pregSuppressed,
		SUM(CASE
             WHEN ((is_patient_pregnant ='Yes' OR is_patient_pregnant ='YES' OR is_patient_pregnant ='yes' OR is_patient_breastfeeding ='Yes' OR is_patient_breastfeeding ='YES' OR is_patient_breastfeeding ='yes') AND vl.result IS NOT NULL AND vl.result!= '' AND vl.vl_result_category like 'suppressed' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00') THEN 1
             ELSE 0
           END) AS pregNotSuppressed,
		SUM(CASE
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND ((vl.vl_result_category like 'suppressed') AND vl.result IS NOT NULL AND vl.result!= '' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
             ELSE 0
           END) AS genderUnknownSuppressed,
		SUM(CASE
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND vl.result IS NOT NULL AND vl.result!= '' AND vl.vl_result_category like 'suppressed' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00') THEN 1
             ELSE 0
           END) AS genderUnknownNotSuppressed,
		SUM(CASE
             WHEN (((vl.vl_result_category like 'suppressed') AND vl.result IS NOT NULL AND vl.result!= '' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
             ELSE 0
           END) AS totalLessThan1000,
		SUM(CASE
             WHEN ((vl.result IS NOT NULL AND vl.result!= '' AND vl.vl_result_category like 'suppressed' AND sample_tested_datetime is not null AND sample_tested_datetime not like '' AND DATE(sample_tested_datetime) !='1970-01-01' AND DATE(sample_tested_datetime) !='0000-00-00')) THEN 1
             ELSE 0
           END) AS totalGreaterThan1000,
		COUNT(result) as total
		 FROM vl_request_form as vl 
         RIGHT JOIN facility_details as f ON f.facility_id=vl.facility_id
         INNER JOIN facility_details as lab ON lab.facility_id=vl.lab_id
       WHERE vl.lab_id is NOT NULL ";

if (!empty($labId)) {
    $sQuery = $sQuery . " AND vl.lab_id IN ($labId)";
}

if (isset($_POST['reportedDate']) && trim($_POST['reportedDate']) != '') {
    if (trim($start_date) == trim($end_date)) {
        $sQuery = $sQuery . ' AND DATE(vl.sample_tested_datetime) = "' . $start_date . '"';
    } else {
        $sQuery = $sQuery . ' AND DATE(vl.sample_tested_datetime) >= "' . $start_date . '" AND DATE(vl.sample_tested_datetime) <= "' . $end_date . '"';
    }
}

$sQuery = $sQuery . ' GROUP BY vl.lab_id, vl.facility_id';
//error_log($sQuery);
$resultSet = $db->rawQuery($sQuery);

$excelResultSet = array();
foreach ($resultSet as $row) {
    $excelResultSet[$row['lab_name']][] = $row;
}

foreach ($excelResultSet as $vlLab => $labResult) {

    $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($excel, '');
    $excel->addSheet($sheet, $c);
    $vlLab = preg_replace('/\s+/', '', ($vlLab));
    $sheet->setTitle($vlLab);

    $sheet->setCellValue('B1', html_entity_decode('Reported Date ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'], ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('D1', html_entity_decode('Lab Name ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('E1', html_entity_decode(ucwords($vlLab), ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    //$sheet->setCellValue('F1', html_entity_decode('Collection Date ' , ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    //$sheet->setCellValue('G1', html_entity_decode($_POST['collectionDate'] , ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('B2', html_entity_decode('Province/State ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C2', html_entity_decode('District/County ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('D2', html_entity_decode('Site Name ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('E2', html_entity_decode('Facility ID ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('F2', html_entity_decode('No. of Rejections ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('G2', html_entity_decode('Viral Load Result- Peds ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('G3', html_entity_decode('<15 yrs <=1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('H3', html_entity_decode('<15 yrs >1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('I2', html_entity_decode('Viral Load Result- Adults ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('I3', html_entity_decode('>15yrs Male <=1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('J3', html_entity_decode('>15yrs Male >1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('K3', html_entity_decode('>15yrs Female <=1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('L3', html_entity_decode('>15yrs  Female >1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('M2', html_entity_decode('Viral Load Results- Pregnant/Breastfeeding Women ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('M3', html_entity_decode('<= 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('N3', html_entity_decode('> 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('O2', html_entity_decode('Age/Sex Unknown ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('O3', html_entity_decode('Unknown Age/Sex <= 1000ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('P3', html_entity_decode('Unknown Age/Sex > 1000ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('Q2', html_entity_decode('Totals ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('Q3', html_entity_decode('<= 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('R3', html_entity_decode('> 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('S2', html_entity_decode('Total Test per Clinic ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('T2', html_entity_decode('Comments ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

    $sheet->getStyle('B1')->applyFromArray($backgroundTitleStyle);
    $sheet->getStyle('C1')->applyFromArray($backgroundFieldStyle);
    $sheet->getStyle('D1')->applyFromArray($backgroundTitleStyle);
    $sheet->getStyle('E1')->applyFromArray($backgroundFieldStyle);
    //$sheet->getStyle('F1')->applyFromArray($backgroundTitleStyle);
    //$sheet->getStyle('G1')->applyFromArray($backgroundFieldStyle);
    $sheet->getStyle('B2:B3')->applyFromArray($styleArray);
    $sheet->getStyle('C2:C3')->applyFromArray($styleArray);
    $sheet->getStyle('D2:D3')->applyFromArray($styleArray);
    $sheet->getStyle('E2:E3')->applyFromArray($styleArray);
    $sheet->getStyle('F2:F3')->applyFromArray($styleArray);
    $sheet->getStyle('G2:H2')->applyFromArray($styleArray);
    $sheet->getStyle('G3')->applyFromArray($styleArray);
    $sheet->getStyle('H3')->applyFromArray($styleArray);
    $sheet->getStyle('I2:L2')->applyFromArray($styleArray);
    $sheet->getStyle('I3')->applyFromArray($styleArray);
    $sheet->getStyle('J3')->applyFromArray($styleArray);
    $sheet->getStyle('K3')->applyFromArray($styleArray);
    $sheet->getStyle('L3')->applyFromArray($styleArray);
    $sheet->getStyle('M2:N2')->applyFromArray($styleArray);
    $sheet->getStyle('M3')->applyFromArray($styleArray);
    $sheet->getStyle('N3')->applyFromArray($styleArray);
    $sheet->getStyle('O2:P2')->applyFromArray($styleArray);
    $sheet->getStyle('O3')->applyFromArray($styleArray);
    $sheet->getStyle('P3')->applyFromArray($styleArray);
    $sheet->getStyle('Q2:R2')->applyFromArray($styleArray);
    $sheet->getStyle('Q3')->applyFromArray($styleArray);
    $sheet->getStyle('R3')->applyFromArray($styleArray);
    $sheet->getStyle('S2:S3')->applyFromArray($styleArray);
    $sheet->getStyle('T2:T3')->applyFromArray($styleArray);

    $output = array();
    $r = 1;
    if (count($labResult) > 0) {
        foreach ($labResult as $aRow) {
            $row = array();
            $row[] = $r;
            $row[] = ucwords($aRow['facility_state']);
            $row[] = ucwords($aRow['facility_district']);
            $row[] = ucwords($aRow['facility_name']);
            $row[] = $aRow['facility_code'];
            $row[] = $aRow['rejections'];
            $row[] = $aRow['lt15suppressed'];
            $row[] = $aRow['lt15NotSuppressed'];
            $row[] = $aRow['gt15suppressedM'];
            $row[] = $aRow['gt15NotSuppressedM'];
            $row[] = $aRow['gt15suppressedF'];
            $row[] = $aRow['gt15NotSuppressedF'];
            $row[] = $aRow['pregSuppressed'];
            $row[] = $aRow['pregNotSuppressed'];
            $row[] = $aRow['genderUnknownSuppressed'];
            $row[] = $aRow['genderUnknownNotSuppressed'];
            $row[] = $aRow['totalLessThan1000'];
            $row[] = $aRow['totalGreaterThan1000'];
            $row[] = $aRow['total'];
            $row[] = '';
            $output[] = $row;
            $r++;
        }
    } else {
        $row = array();
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $output[] = $row;
        $r++;
    }

    $start = (count($output));
    //$sheet->getDefaultColumnDimension()->setWidth(20);
    foreach ($output as $rowNo => $rowData) {
        $colNo = 1;
        foreach ($rowData as $field => $value) {
            $rRowCount = $rowNo + 4;
            $cellName = $sheet->getCellByColumnAndRow($colNo, $rRowCount)->getColumn();
            $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
            $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
            //$sheet->getDefaultRowDimension()->setRowHeight(15);
            if ($colNo > 5 && $colNo <= 19) {
                $cellDataType = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC;
            } else {
                $cellDataType = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
            }
            $sheet->getCellByColumnAndRow($colNo, $rowNo + 4)->setValueExplicit(html_entity_decode($value), $cellDataType);
            $sheet->getStyleByColumnAndRow($colNo, $rowNo + 4)->getAlignment()->setWrapText(true);
            $colNo++;
        }
    }
    $c++;
}
//Statistics sheet end
if ($c > 0) {
    //Super lab performance sheet end
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
    $filename = 'VLSM-VL-Lab-Weekly-Report-' . date('d-M-Y-H-i-s') . '.xlsx';
    $writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
    echo $filename;
} else {
    echo '';
}
