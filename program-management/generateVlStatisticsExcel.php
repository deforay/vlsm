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
if(isset($_SESSION['vlStatisticsQuery']) && trim($_SESSION['vlStatisticsQuery'])!=""){
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
 
 $sheet->mergeCells('C1:E1');
 $sheet->mergeCells('G1:T1');
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
 if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
    $s_t_date = explode("to", $_POST['reportedDate']);
    if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
      $start_date = $general->dateFormat(trim($s_t_date[0]));
    }
    if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
      $end_date = $general->dateFormat(trim($s_t_date[1]));
    }
 }
 if(isset($_POST['lab']) && $_POST['lab']!= '' && count(array_filter($_POST['lab']))> 0){
    $lab = implode(',',$_POST['lab']);
    $vlLabQuery="SELECT * FROM facility_details where facility_id IN ('$lab') AND status='active'";
    $vlLabResult = $db->rawQuery($vlLabQuery);
 }else{
    $vlLabQuery="SELECT * FROM facility_details where facility_type = 2 AND status='active'";
    $vlLabResult = $db->rawQuery($vlLabQuery);
 }
 //echo $vlLabQuery;die;
 foreach($vlLabResult as $vlLab){
    $sQuery="SELECT vl.facility_id,f.state,f.district,f.facility_name FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id WHERE vl.lab_id = '".$vlLab['facility_id']."' AND vl.form_id = '".$country."'";
    if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
        if (trim($start_date) == trim($end_date)) {
          $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
        }else{
          $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
        }
    }
    $sQuery = $sQuery.'GROUP BY vl.facility_id';
    //echo $sQuery;die;
    $sResult = $db->rawQuery($sQuery);
    if(count($sResult)>0){
        $vlLabName = explode(' ',$vlLab['facility_name']);
        $sheet = new PHPExcel_Worksheet($excel, '');
        $excel->addSheet($sheet, 0);
        $sheet->setTitle('Viral Load statistics '.ucwords($vlLabName[0]));
        
        $sheet->setCellValue('B1', html_entity_decode('Reported Date ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('F1', html_entity_decode('Super Lab Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('G1', html_entity_decode(ucwords($vlLab['facility_name']), ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
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
        
        $sheet->getStyle('B1')->applyFromArray($headingStyle);
        $sheet->getStyle('F1')->applyFromArray($headingStyle);
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
           $totalQuery = 'SELECT vl_sample_id,patient_dob,gender,is_patient_pregnant,is_patient_breastfeeding,result,rejection,sample_rejection_reason FROM vl_request_form as vl where vl.facility_id = '.$aRow['facility_id'].' AND vl.lab_id = '.$vlLab['facility_id'].' AND vl.form_id = '.$country;
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
           $row[] = '';
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
    }
 }
 $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
 $filename = 'vl-statistics-report-' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
}else{
    echo '';
}
?>