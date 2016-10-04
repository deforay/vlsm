<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/PHPExcel.php');
$general=new Deforay_Commons_General();

if(isset($_SESSION['vlPatientQuery']) && trim($_SESSION['vlPatientQuery'])!=""){
 
 $rResult = $db->rawQuery($_SESSION['vlPatientQuery']);
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Patient Name","Gender","DOB","Age In Years","Age In Months","Patient Pregnant","Patient Breatfeeding","ART Number","Receive SMS","Mobile Number");
 
 $colNo = 0;
 
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
 
 foreach ($headings as $field => $value) {
  
  $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
  $colNo++;
 }
 $sheet->getStyle('A1:J1')->applyFromArray($styleArray);
 
 foreach ($rResult as $aRow) {
  if(isset($aRow['patient_name']) && trim($aRow['patient_name'])!= ''){
    $row = array();
    if(isset($aRow['patient_dob']) && trim($aRow['patient_dob'])!='' && $aRow['patient_dob']!='0000-00-00'){
     $aRow['patient_dob']=$general->humanDateFormat($aRow['patient_dob']);
    }else{
     $aRow['patient_dob']='';
    }
    $row[] = ucwords($aRow['patient_name']." ".$aRow['surname']);
    $row[] = ucwords(str_replace("_"," ",$aRow['gender']));
    $row[] = $aRow['patient_dob'];
    $row[] = $aRow['age_in_yrs'];
    $row[] = $aRow['age_in_mnts'];
    $row[] = ucwords($aRow['is_patient_pregnant']);
    $row[] = ucwords($aRow['is_patient_breastfeeding']);
    $row[] = $aRow['art_no'];
    $row[] = ucwords($aRow['patient_receive_sms']);
    $row[] = $aRow['patient_phone_number'];
    
    $output[] = $row;
  }
 }

 $start = (count($output));
 foreach ($output as $rowNo => $rowData) {
  $colNo = 0;
  foreach ($rowData as $field => $value) {
    $rRowCount = $rowNo + 2;
    $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
    $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
    $sheet->getDefaultRowDimension()->setRowHeight(15);
    $sheet->getCellByColumnAndRow($colNo, $rowNo + 2)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
    $colNo++;
  }
 }
 $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
 $filename = 'vl-patient-result-' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("./temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
 
}
?>