<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
require '../includes/mail/PHPMailerAutoload.php';
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new Deforay_Commons_General();
$configQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($configQuery);
$country = $configResult[0]['value'];
$postdata = $_POST;
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-7 days'));
if(!isset($postdata['reportedDate'])){
   $_POST['reportedDate'] = $general->humanDateFormat($start_date).' to '.$general->humanDateFormat($end_date);
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
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
     )
 );
 $backgroundStyle = array(
     'font' => array(
         'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
     ),
      'fill' => array(
         'type' => PHPExcel_Style_Fill::FILL_SOLID,
         'color' => array('rgb' => 'FFFF00')
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
 
 
  $vlLabQuery="SELECT * FROM facility_details where facility_type = 2 AND status='active'";
  $vlLabResult = $db->rawQuery($vlLabQuery);
 //echo $vlLabQuery;die;
 
 //Statistics sheet start
 $sheet->mergeCells('C1:D1');
 $sheet->mergeCells('F1:G1');
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
    $sQuery="SELECT vl.facility_id,f.state,f.district,f.facility_name,f.facility_code FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id WHERE vl.lab_id = '".$vlLab['facility_id']."' AND vl.form_id = '".$country."'";
    if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
        if (trim($start_date) == trim($end_date)) {
          $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
        }else{
          $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
        }
    }
    $sQuery = $sQuery.' GROUP BY vl.facility_id';
    $sResult = $db->rawQuery($sQuery);
    if(count($sResult)>0){
        $vlLabName = explode(' ',$vlLab['facility_name']);
        $sheet = new PHPExcel_Worksheet($excel, '');
        $excel->addSheet($sheet, $c);
        $sheet->setTitle('Viral Load Statistics '.ucwords($vlLabName[0]));
        
        $sheet->setCellValue('B1', html_entity_decode('Reported Date ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('E1', html_entity_decode('Super Lab Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('F1', html_entity_decode(ucwords($vlLab['facility_name']), ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('B2', html_entity_decode('Province ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('C2', html_entity_decode('District ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
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
        $sheet->setCellValue('S2', html_entity_decode('Total Test per Facility ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('T2', html_entity_decode('Comments ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        
        $sheet->getStyle('B1')->applyFromArray($backgroundStyle);
        $sheet->getStyle('E1')->applyFromArray($backgroundStyle);
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
        foreach ($sResult as $aRow) {
          //No. of tests per facility & calculate others
           $totalQuery = 'SELECT vl.vl_sample_id,vl.patient_dob,vl.gender,vl.is_patient_pregnant,vl.is_patient_breastfeeding,vl.result,vl.rejection,vl.sample_rejection_reason,f.facility_name,f.facility_code FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id where vl.facility_id = '.$aRow['facility_id'].' AND vl.lab_id = '.$vlLab['facility_id'].' AND vl.form_id = '.$country;
           if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
                if (trim($start_date) == trim($end_date)) {
                  $totalQuery = $totalQuery.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
                }else{
                  $totalQuery = $totalQuery.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
                }
           }
           $totalResult = $db->rawQuery($totalQuery);
           $lte14n1000 = array();
           $lte14ngt1000 = array();
           $gt14mnlte1000 = array();
           $gt14mn1000 = array();
           $gt14fnlte1000 = array();
           $gt14fn1000 = array();
           $isPatientPergnantrbfeedingnlte1000 = array();
           $isPatientPergnantrbfeedingngt1000 = array();
           $unknownxnlte1000 = array();
           $unknownxngt1000 = array();
           $lte1000total = array();
           $gt1000total = array();
           $rejection = array();
           foreach($totalResult as $tRow){
               if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
                   $lte1000total[] = $tRow['vl_sample_id'];
               }else if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
                  $gt1000total[] = $tRow['vl_sample_id'];
               }
               if($tRow['patient_dob']!= NULL && $tRow['patient_dob']!= '' && $tRow['patient_dob']!= '0000-00-00'){
                   $age = floor((time() - strtotime($tRow['patient_dob'])) / 31556926);
                   if($age > 14){
                       if($tRow['gender'] == 'male' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
                           $gt14mnlte1000[] = $tRow['vl_sample_id'];
                       }else if($tRow['gender'] == 'male' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
                           $gt14mn1000[] = $tRow['vl_sample_id'];
                       }else if($tRow['gender'] == 'female' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
                           if(($tRow['is_patient_pregnant']!= NULL && $tRow['is_patient_pregnant']!= '' && $tRow['is_patient_pregnant'] == 'yes') || ($tRow['is_patient_breastfeeding']!= NULL && $tRow['is_patient_breastfeeding']!= '' && $tRow['is_patient_breastfeeding'] == 'yes')){
                               $isPatientPergnantrbfeedingnlte1000[] = $tRow['vl_sample_id'];
                           }
                          $gt14fnlte1000[] = $tRow['vl_sample_id'];
                       }else if($tRow['gender'] == 'female' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
                           if(($tRow['is_patient_pregnant']!= NULL && $tRow['is_patient_pregnant']!= '' && $tRow['is_patient_pregnant'] == 'yes') || ($tRow['is_patient_breastfeeding']!= NULL && $tRow['is_patient_breastfeeding']!= '' && $tRow['is_patient_breastfeeding'] == 'yes')){
                               $isPatientPergnantrbfeedingngt1000[] = $tRow['vl_sample_id'];
                           }
                          $gt14fn1000[] = $tRow['vl_sample_id'];
                       }
                   }else{
                       if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
                          $lte14n1000[] = $tRow['vl_sample_id'];
                       }else if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
                         $lte14ngt1000[] = $tRow['vl_sample_id'];
                       }
                   }
               }else{
                   if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
                       $unknownxnlte1000[] = $tRow['vl_sample_id'];
                   }else if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
                       $unknownxngt1000[] = $tRow['vl_sample_id'];
                   }
               }
               if(($tRow['rejection']!= NULL && $tRow['rejection']!= '') || ($tRow['sample_rejection_reason']!= NULL && $tRow['sample_rejection_reason']!= '' && $tRow['sample_rejection_reason'] >0)){
                   $rejection[] = $tRow['vl_sample_id'];
               }
           }
           $row = array();
           $row[] = $r;
           $row[] = ucwords($aRow['state']);
           $row[] = ucwords($aRow['district']);
           $row[] = ucwords($aRow['facility_name']);
           $row[] = $aRow['facility_code'];
           $row[] = count($rejection);
           $row[] = count($lte14n1000);
           $row[] = count($lte14ngt1000);
           $row[] = count($gt14mnlte1000);
           $row[] = count($gt14mn1000);
           $row[] = count($gt14fnlte1000);
           $row[] = count($gt14fn1000);
           $row[] = count($isPatientPergnantrbfeedingnlte1000);
           $row[] = count($isPatientPergnantrbfeedingngt1000);
           $row[] = count($unknownxnlte1000);
           $row[] = count($unknownxngt1000);
           $row[] = count($lte1000total);
           $row[] = count($gt1000total);
           $row[] = count($totalResult);
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
  }
 //Statistics sheet end
 //Super lab performance sheet start
 if($c > 0){
   $sheet = new PHPExcel_Worksheet($excel, '');
   $excel->addSheet($sheet, $c);
   $sheet->setTitle('Super Lab Performance Report');
   
   $sheet->mergeCells('C1:D1');
    
   $sheet->setCellValue('A1', html_entity_decode('VL ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('B1', html_entity_decode('Reported Date ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('B2', html_entity_decode('Province ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('C2', html_entity_decode('District ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('D2', html_entity_decode('Super Lab Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('E2', html_entity_decode('IPSL ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('F2', html_entity_decode('Total Number of VL samples Received at Laboratory ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('G2', html_entity_decode('Total Number of Viral load tests done (inc failed tests) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('H2', html_entity_decode('No. of Samples Not Tested ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('I2', html_entity_decode('Assay Failure Rate(%) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('J2', html_entity_decode('Average Result TAT (lab) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   $sheet->setCellValue('K2', html_entity_decode('Average Result TAT -Total (from sample  collection to results getting to the facility/hub) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
   
   $sheet->getStyle('A1')->applyFromArray($headingStyle);
   $sheet->getStyle('B1')->applyFromArray($backgroundStyle);
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
       $sQuery="SELECT vl.vl_sample_id,vl.sample_collection_date,vl.date_sample_received_at_testing_lab,vl.lab_tested_date,vl.date_result_printed,vl.result,f.facility_name FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id WHERE vl.lab_id = '".$vlLab['facility_id']."' AND vl.form_id = '".$country."'";
       if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
          if (trim($start_date) == trim($end_date)) {
            $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
          }else{
            $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
          }
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
         $labTestedDate = '';
         $dateResultPrinted = '';
         if(trim($result['sample_collection_date'])!= '' && $result['sample_collection_date'] != NULL && $result['sample_collection_date'] != '0000-00-00 00:00:00'){
            $sampleCollectionDate = $result['sample_collection_date'];
         }
         if(trim($result['date_sample_received_at_testing_lab'])!= '' && $result['date_sample_received_at_testing_lab'] != NULL && $result['date_sample_received_at_testing_lab'] != '0000-00-00 00:00:00'){
            $dateOfSampleReceivedAtTestingLab = $result['date_sample_received_at_testing_lab'];
            $noOfSampleReceivedAtLab[] = $result['vl_sample_id'];
         }
         if(trim($result['lab_tested_date'])!= '' && $result['lab_tested_date'] != NULL && $result['lab_tested_date'] != '0000-00-00 00:00:00'){
            $labTestedDate = $result['lab_tested_date'];
            $noOfSampleTested[] = $result['vl_sample_id'];
         }else{
            //For sample not tested..
            if(trim($result['date_sample_received_at_testing_lab'])!= '' && $result['date_sample_received_at_testing_lab'] != NULL && $result['date_sample_received_at_testing_lab'] != '0000-00-00 00:00:00'){
               $noOfSampleNotTested[] = $result['vl_sample_id'];
            }
         }
         if(trim($result['date_result_printed'])!= '' && $result['date_result_printed'] != NULL && $result['date_result_printed'] != '0000-00-00 00:00:00'){
            $dateResultPrinted = $result['date_result_printed'];
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
         if(trim($result['result'])== 'failed' || trim($result['result'])== 'fail'){
            $assayFailures[] = $result['vl_sample_id'];
         }
       }
       $row = array();
       $row[] = $r;
       $row[] = ucwords($vlLab['state']);
       $row[] = ucwords($vlLab['district']);
       $row[] = ucwords($vlLab['facility_name']);
       $row[] = $vlLab['facility_code'];
       $row[] = count($noOfSampleReceivedAtLab);
       $row[] = count($noOfSampleTested);
       $row[] = count($noOfSampleNotTested);
       $row[] = (count($sResult) >0)?(round(count($assayFailures)/count($sResult)))*100:0;
       $row[] = (count($resultTat) >0)?round(array_sum($resultTat)/count($resultTat)):0;
       $row[] = (count($resultDTat) >0)?round(array_sum($resultDTat)/count($resultDTat)).' - '.count($resultDTat):0;
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
   $filename = 'vl-weekly-report-' . date('d-M-Y-H-i-s') . '.xls';
   $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
    //mail part start
    //Create a new PHPMailer instance
    $mail = new PHPMailer();
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 2;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';
    //Set the hostname of the mail server
    $mail->Host = 'smtp.gmail.com';
    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 25;
    //Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'tls';
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    $mail->SMTPKeepAlive = true; 
    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = 'zfmailexample@gmail.com';
    //Password to use for SMTP authentication
    $mail->Password = 'mko)(*&^12345';
    //Set who the message is to be sent from
    $mail->setFrom('zfmailexample@gmail.com');
    $subject="VLSM - Weekly Report - ".$_POST['reportedDate'];
    $mail->Subject = $subject;
    //Set to emailid(s)
    $configQuery ="SELECT * from global_config where name='manager_email'";
    $configResult=$db->query($configQuery);
    if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= ''){
       $xplodAddress = explode(",",$configResult[0]['value']);
       for($to=0;$to<count($xplodAddress);$to++){
          $mail->addAddress($xplodAddress[$to]);
       }
       $pathFront=realpath('../temporary');
       $file_to_attach = $pathFront. DIRECTORY_SEPARATOR. $filename;
       $mail->AddAttachment($file_to_attach);
       $message ='Hi Manager,<br>Please find the attached viral load weekly report '.$_POST['reportedDate'];
       $message = nl2br($message);
       $mail->msgHTML($message);
       if($mail->send()){
          error_log('weekly reports mail sent--'.$_POST['reportedDate']);
       }else{
          error_log('weekly reports mail send error--');
       }
    }else{
         error_log('weekly reports mail send error--to email id is missing--');
    }
   }
?>