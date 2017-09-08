<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new Deforay_Commons_General();
$configQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($configQuery);
$country = $configResult[0]['value'];
if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
   $s_t_date = explode("to", $_POST['reportedDate']);
   if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
     $start_date = $general->dateFormat(trim($s_t_date[0]));
   }
   if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
     $end_date = $general->dateFormat(trim($s_t_date[1]));
   }
}
 //excel code start
 $excel = new PHPExcel();
 $sheet = $excel->getActiveSheet();
 $headingStyle = array(
     'font' => array(
         'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     )
 );
 $backgroundTitleStyle = array(
     'font' => array(
         'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     ),
      'fill' => array(
         'type' => PHPExcel_Style_Fill::FILL_SOLID,
         'color' => array('rgb' => 'FFFF00')
      )
 );
 $backgroundFieldStyle = array(
     'font' => array(
         'bold' => false,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT
     )
 );
 $styleArray = array(
     'font' => array(
         'bold' => true,
         'size' => '13',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
         'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
     ),
     'borders' => array(
         'outline' => array(
             'style' => \PHPExcel_Style_Border::BORDER_THIN,
         ),
     )
 );
 
 $borderStyle = array(
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     ),
     'borders' => array(
         'outline' => array(
             'style' => \PHPExcel_Style_Border::BORDER_THIN,
         ),
     )
 );
 
 if(isset($_POST['lab']) && $_POST['lab']!= '' && count(array_filter($_POST['lab']))> 0){
    $lab = implode(',',$_POST['lab']);
    $vlLabQuery="SELECT * FROM facility_details where facility_id IN ($lab) AND status='active'";
    $vlLabResult = $db->rawQuery($vlLabQuery);
 }else{
    $vlLabQuery="SELECT * FROM facility_details where facility_type = 2 AND status='active'";
    $vlLabResult = $db->rawQuery($vlLabQuery);
 }
 //echo $vlLabQuery;die;
 
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
 foreach($vlLabResult as $vlLab){
    $sQuery="SELECT
		 vl.facility_id,f.facility_code,f.facility_state,f.facility_district,f.facility_name,
		
		SUM(CASE
			 WHEN (result_status = 4) THEN 1
		             ELSE 0
		           END) AS rejections,

		SUM(CASE 
			 WHEN (patient_age_in_years <= 14 AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
		             ELSE 0
		           END) AS lt14lt1000, 
		SUM(CASE 
             WHEN (patient_age_in_years <= 14 AND result > 1000) THEN 1
             ELSE 0
           END) AS lt14gt1000,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='male') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS gt14lt1000M,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='male') AND result > 1000) THEN 1
             ELSE 0
           END) AS gt14gt1000M,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS gt14lt1000F,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND result > 1000) THEN 1
             ELSE 0
           END) AS gt14gt1000F,	
		SUM(CASE 
             WHEN ((is_patient_pregnant ='yes') OR (is_patient_breastfeeding ='yes') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS preglt1000,	
		SUM(CASE 
             WHEN ((is_patient_pregnant ='yes') OR (is_patient_breastfeeding ='yes') AND result > 1000) THEN 1
             ELSE 0
           END) AS preggt1000,           	           	
		SUM(CASE 
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS ult1000, 
		SUM(CASE 
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND result > 1000) THEN 1
             ELSE 0
           END) AS ugt1000,               
		SUM(CASE 
             WHEN ((result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS totalLessThan1000,     
		SUM(CASE 
             WHEN ((result > 1000)) THEN 1
             ELSE 0
           END) AS totalGreaterThan1000,
		COUNT(result) as total
		 FROM vl_request_form as vl RIGHT JOIN facility_details as f ON f.facility_id=vl.facility_id
       WHERE vl.lab_id = ".$vlLab['facility_id']." AND vl.vlsm_country_id = ".$country;
    if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
        if (trim($start_date) == trim($end_date)) {
          $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) = "'.$start_date.'"';
        }else{
          $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
        }
    }
    if(isset($_POST['searchData']) && trim($_POST['searchData'])!= ''){
        //$sQuery = $sQuery.' AND (f.facility_state LIKE "%'.$_POST['searchData'].'%" OR f.facility_district LIKE "%'.$_POST['searchData'].'%" OR f.facility_name LIKE "%'.$_POST['searchData'].'%")';
    }
    $sQuery = $sQuery.' GROUP BY vl.facility_id';
    $sResult = $db->rawQuery($sQuery);
    //error_log($sQuery);
      $sheet = new PHPExcel_Worksheet($excel, '');
      $excel->addSheet($sheet, $c);
      $sheet->setTitle($vlLab['facility_name']);
      
      $sheet->setCellValue('B1', html_entity_decode('Reported Date ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('D1', html_entity_decode('Super Lab Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('E1', html_entity_decode(ucwords($vlLab['facility_name']), ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('B2', html_entity_decode('Province/State ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('C2', html_entity_decode('District/County ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('D2', html_entity_decode('Site Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('E2', html_entity_decode('IPSL ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('F2', html_entity_decode('No. of Rejections ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('G2', html_entity_decode('Viral Load Result- Peds ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('G3', html_entity_decode('<14 yrs <=1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('H3', html_entity_decode('<14 yrs >1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('I2', html_entity_decode('Viral Load Result- Adults ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('I3', html_entity_decode('>14yrs Male <=1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('J3', html_entity_decode('>14yrs Male >1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('K3', html_entity_decode('>14yrs Female <=1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('L3', html_entity_decode('>14yrs  Female >1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('M2', html_entity_decode('Viral Load Results- Pregnant/Breastfeeding Women ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('M3', html_entity_decode('<= 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('N3', html_entity_decode('> 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('O2', html_entity_decode('Age/Sex Unknown ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('O3', html_entity_decode('Unknown Age/Sex <= 1000ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('P3', html_entity_decode('Unknown Age/Sex > 1000ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('Q2', html_entity_decode('Totals ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('Q3', html_entity_decode('<= 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('R3', html_entity_decode('> 1000 copies/ml ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('S2', html_entity_decode('Total Test per Clinic ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('T2', html_entity_decode('Comments ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
      
      $sheet->getStyle('B1')->applyFromArray($backgroundTitleStyle);
      $sheet->getStyle('C1')->applyFromArray($backgroundFieldStyle);
      $sheet->getStyle('D1')->applyFromArray($backgroundTitleStyle);
      $sheet->getStyle('E1')->applyFromArray($backgroundFieldStyle);
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
      $r=1;
      if(count($sResult) > 0){
         foreach($sResult as $aRow) {
            $row = array();
             $row[] = $r;
             $row[] = ucwords($aRow['facility_state']);
             $row[] = ucwords($aRow['facility_district']);
             $row[] = ucwords($aRow['facility_name']);
             $row[] = $aRow['facility_code'];
             $row[] = $aRow['rejections'];
             $row[] = $aRow['lt14lt1000'];
             $row[] = $aRow['lt14gt1000'];
             $row[] = $aRow['gt14lt1000M'];
             $row[] = $aRow['gt14gt1000M'];			
             $row[] = $aRow['gt14lt1000F'];
             $row[] = $aRow['gt14gt1000F'];
             $row[] = $aRow['preglt1000'];
             $row[] = $aRow['preggt1000'];
             $row[] = $aRow['ult1000'];
             $row[] = $aRow['ugt1000'];
             $row[] = $aRow['totalLessThan1000'];
             $row[] = $aRow['totalGreaterThan1000'];
             $row[] = $aRow['total'];
             $row[] = '';
            $output[] = $row;
          $r++;
         }
      }else{
         $row = array();
            $row[] = $r;
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;			
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = 0;
            $row[] = '';
           $output[] = $row;
         $r++;
      }
     
      $start = (count($output));
      foreach ($output as $rowNo => $rowData) {
       $colNo = 0;
       foreach ($rowData as $field => $value) {
         $rRowCount = $rowNo + 4;
         $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
         $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
         $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
         $sheet->getDefaultRowDimension()->setRowHeight(15);
         $sheet->getCellByColumnAndRow($colNo, $rowNo + 4)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
         $colNo++;
       }
      }
    $c++;
  }
 //Statistics sheet end
 //Super lab performance sheet start
 if($c > 0){
   $sheet = new PHPExcel_Worksheet($excel, '');
   $excel->addSheet($sheet, $c);
   $sheet->setTitle('Super Lab Performance Report');
    
   $sheet->setCellValue('A1', html_entity_decode('VL ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('B1', html_entity_decode('Reported Date ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('B2', html_entity_decode('Province/State ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('C2', html_entity_decode('District/County ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('D2', html_entity_decode('Super Lab Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('E2', html_entity_decode('IPSL ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('F2', html_entity_decode('Total Number of VL samples Received at Laboratory ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('G2', html_entity_decode('Total Number of Viral load tests done (inc failed tests) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('H2', html_entity_decode('No. of Samples Not Tested ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('I2', html_entity_decode('Assay Failure Rate(%) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('J2', html_entity_decode('Average Result TAT (lab) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('K2', html_entity_decode('Average Result TAT -Total (from sample  collection to results getting to the facility/hub) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   
   $sheet->getStyle('A1')->applyFromArray($headingStyle);
   $sheet->getStyle('B1')->applyFromArray($backgroundTitleStyle);
   $sheet->getStyle('B2')->applyFromArray($styleArray);
   $sheet->getStyle('C2')->applyFromArray($styleArray);
   $sheet->getStyle('D2')->applyFromArray($styleArray);
   $sheet->getStyle('E2')->applyFromArray($styleArray);
   $sheet->getStyle('F2')->applyFromArray($styleArray);
   $sheet->getStyle('G2')->applyFromArray($styleArray);
   $sheet->getStyle('H2')->applyFromArray($styleArray);
   $sheet->getStyle('I2')->applyFromArray($styleArray);
   $sheet->getStyle('J2')->applyFromArray($styleArray);
   $sheet->getStyle('K2')->applyFromArray($styleArray);
   $output = array();
    $r=1;
    foreach ($vlLabResult as $vlLab) {
       $sQuery="SELECT vl.vl_sample_id,vl.sample_collection_date,vl.sample_received_at_vl_lab_datetime,vl.sample_tested_datetime,vl.result_printed_datetime,vl.result,f.facility_name FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id WHERE vl.lab_id = '".$vlLab['facility_id']."' AND vl.vlsm_country_id = '".$country."'";
       if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
          if (trim($start_date) == trim($end_date)) {
            $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) = "'.$start_date.'"';
          }else{
            $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
          }
       }
       if(isset($_POST['searchData']) && trim($_POST['searchData'])!= ''){
          $sQuery = $sQuery.' AND (f.facility_state LIKE "%'.$_POST['searchData'].'%" OR f.facility_district LIKE "%'.$_POST['searchData'].'%" OR f.facility_name LIKE "%'.$_POST['searchData'].'%")';
       }
       $sResult = $db->rawQuery($sQuery);
       $noOfSampleReceivedAtLab = array();
       $noOfSampleTested = array();
       $noOfSampleNotTested = array();
       $resultTat = array();
       $resultDTat = array();
       $assayFailures = array();
       foreach($sResult as $result){
         $sampleCollectionDate = '';
         $dateOfSampleReceivedAtTestingLab = '';
         $dateResultPrinted = '';
         if(trim($result['sample_collection_date'])!= '' && $result['sample_collection_date'] != NULL && $result['sample_collection_date'] != '0000-00-00 00:00:00'){
            $sampleCollectionDate = $result['sample_collection_date'];
         }
         
         if(trim($result['sample_received_at_vl_lab_datetime'])!= '' && $result['sample_received_at_vl_lab_datetime'] != NULL && $result['sample_received_at_vl_lab_datetime'] != '0000-00-00 00:00:00'){
            $dateOfSampleReceivedAtTestingLab = $result['sample_received_at_vl_lab_datetime'];
            $noOfSampleReceivedAtLab[] = $result['vl_sample_id'];
         }
         
         if(trim($result['sample_tested_datetime'])!= '' && $result['sample_tested_datetime'] != NULL && $result['sample_tested_datetime'] != '0000-00-00 00:00:00'){
            $noOfSampleTested[] = $result['vl_sample_id'];
         }else{
            if(trim($result['sample_received_at_vl_lab_datetime'])!= '' && $result['sample_received_at_vl_lab_datetime'] != NULL && $result['sample_received_at_vl_lab_datetime'] != '0000-00-00 00:00:00'){
               $noOfSampleNotTested[] = $result['vl_sample_id'];
            }
         }
         
         if(trim($result['result_printed_datetime'])!= '' && $result['result_printed_datetime'] != NULL && $result['result_printed_datetime'] != '0000-00-00 00:00:00'){
            $dateResultPrinted = $result['result_printed_datetime'];
         }
         
         if(trim($dateOfSampleReceivedAtTestingLab)!= '' && trim($dateResultPrinted)!= ''){
            $date_result_printed = strtotime($dateResultPrinted);
            $date_of_sample_received_at_testing_lab = strtotime($dateOfSampleReceivedAtTestingLab);
            $daydiff = $date_result_printed - $date_of_sample_received_at_testing_lab;
            $tat = (int)floor($daydiff / (60 * 60 * 24));
            $resultTat[] = $tat;
         }
         if(trim($sampleCollectionDate)!= '' && trim($dateResultPrinted)!= ''){
            $date_result_printed = strtotime($dateResultPrinted);
            $sample_collection_date = strtotime($sampleCollectionDate);
            $daydiff = $date_result_printed - $sample_collection_date;
            $tatD = (int)floor($daydiff / (60 * 60 * 24));
            $resultDTat[] = $tatD;
         }
         if(trim(strtolower($result['result']))== 'failed' || trim(strtolower($result['result']))== 'fail'){
            $assayFailures[] = $result['vl_sample_id'];
         }
       }
       $row = array();
       $row[] = $r;
       $row[] = ucwords($vlLab['facility_state']);
       $row[] = ucwords($vlLab['facility_district']);
       $row[] = ucwords($vlLab['facility_name']);
       $row[] = $vlLab['facility_code'];
       $row[] = count($noOfSampleReceivedAtLab);
       $row[] = count($noOfSampleTested);
       $row[] = count($noOfSampleNotTested);
       $row[] = (count($sResult) >0) ? (round(count($assayFailures)/count($sResult)))*100 : 0;
       $row[] = (count($resultTat) >0) ? round(array_sum($resultTat)/count($resultTat)) : 0;
       $row[] = (count($resultDTat) >0) ? (round(array_sum($resultDTat)/count($resultDTat)) - count($resultDTat)) : 0;
       $output[] = $row;
     $r++;
    }
   
    $start = (count($output));
    foreach ($output as $rowNo => $rowData) {
        $colNo = 0;
        foreach ($rowData as $field => $value) {
          $rRowCount = $rowNo + 3;
          $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
          $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
          $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
          $sheet->getDefaultRowDimension()->setRowHeight(15);
          $sheet->getCellByColumnAndRow($colNo, $rowNo + 3)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
          $colNo++;
        }
    }
   //Super lab performance sheet end
   $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
   $filename = 'vl-lab-weekly-report-' . date('d-M-Y-H-i-s') . '.xls';
   $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
   echo $filename;
 }else{
   echo ''; 
 }