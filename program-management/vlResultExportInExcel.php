<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new Deforay_Commons_General();

if(isset($_SESSION['vlResultQuery']) && trim($_SESSION['vlResultQuery'])!=""){
 
 $rResult = $db->rawQuery($_SESSION['vlResultQuery']);
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Serial No.","Batch Code","Urgency","Province","District","Clinic Name","Clinician Name","Sample Collection Date","Sample Received Date","Collected By","Patient Name","Gender","DOB","Age In Years","Age In Months","Patient Pregnant","Patient BreastFeeding","ART Number","ART Initiation","ART Regimen","SMS Notification","Mobile Number","Date Of Last Viral Load Test","Result Of Last Viral Load","Viral Load Log","Reason For VL Test","LAB Name","LAB No.","VL Testing Platform","Specimen Type","Sample Testing Date","Last Print On","Viral Load Result","No Result","Rejection Reason","Reviewed By","Approved By","Approved On","Comments","Status");
 
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
 $sheet->getStyle('A1:AN1')->applyFromArray($styleArray);
 
 foreach ($rResult as $aRow) {
  $row = array();
  if(isset($aRow['patient_dob']) && trim($aRow['patient_dob'])!='' && $aRow['patient_dob']!='0000-00-00'){
   $aRow['patient_dob']=$general->humanDateFormat($aRow['patient_dob']);
  }else{
   $aRow['patient_dob']='';
  }
  
  if(isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['sample_collection_date']);
   $aRow['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['sample_collection_date']='';
  }
  
  if(isset($aRow['date_of_initiation_of_current_regimen']) && trim($aRow['date_of_initiation_of_current_regimen'])!='' && $aRow['date_of_initiation_of_current_regimen']!='0000-00-00'){
   $aRow['date_of_initiation_of_current_regimen']=$general->humanDateFormat($aRow['date_of_initiation_of_current_regimen']);
  }else{
   $aRow['date_of_initiation_of_current_regimen']='';
  }
  
  if(isset($aRow['lab_tested_date']) && trim($aRow['lab_tested_date'])!='' && $aRow['lab_tested_date']!='0000-00-00'){
   $expStr=explode(" ",$aRow['lab_tested_date']);
   $aRow['lab_tested_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['lab_tested_date']='';
  }
  if(isset($aRow['last_viral_load_date']) && trim($aRow['last_viral_load_date'])!='' && $aRow['last_viral_load_date']!='0000-00-00'){
 $aRow['last_viral_load_date']=$general->humanDateFormat($aRow['last_viral_load_date']);
  }else{
   $aRow['last_viral_load_date']='';
  }
  
  if(isset($aRow['date_sample_received_at_testing_lab']) && trim($aRow['date_sample_received_at_testing_lab'])!='' && $aRow['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['date_sample_received_at_testing_lab']);
   $aRow['date_sample_received_at_testing_lab']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['date_sample_received_at_testing_lab']='';
  }
  if(isset($aRow['result_approved_on']) && trim($aRow['result_approved_on'])!='' && $aRow['result_approved_on']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['result_approved_on']);
   $aRow['result_approved_on']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['result_approved_on']='';
  }
  
  if(isset($aRow['date_result_printed']) && trim($aRow['date_result_printed'])!='' && $aRow['date_result_printed']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['date_result_printed']);
   $aRow['date_result_printed']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['date_result_printed']='';
  }
  
  $row[] = $aRow['serial_no'];
  $row[] = $aRow['batch_code'];
  $row[] = ucwords($aRow['test_urgency']);
  $row[] = ucwords($aRow['facility_state']);
  $row[] = ucwords($aRow['facility_district']);
  $row[] = ucwords($aRow['facility_name']);
  $row[] = ucwords($aRow['lab_contact_person']);
  $row[] = $aRow['sample_collection_date'];
  $row[] = $aRow['date_sample_received_at_testing_lab'];
  $row[] = $aRow['sample_collected_by'];
  $row[] = ucwords($aRow['patient_first_name'].$aRow['patient_last_name']);
  $row[] = ucwords(str_replace("_"," ",$aRow['patient_gender']));
  $row[] = $aRow['patient_dob'];
  $row[] = $aRow['patient_age_in_years'];
  $row[] = $aRow['patient_age_in_months'];
  $row[] = ucwords($aRow['is_patient_pregnant']);
  $row[] = ucwords($aRow['is_patient_breastfeeding']);
  $row[] = $aRow['patient_art_no'];
  $row[] = $aRow['date_of_initiation_of_current_regimen'];
  $row[] = $aRow['current_regimen'];
  $row[] = ucwords($aRow['consent_to_receive_sms']);
  $row[] = $aRow['patient_mobile_number'];
  $row[] = $aRow['last_viral_load_date'];
  $row[] = $aRow['last_viral_load_result'];
  $row[] = $aRow['last_vl_result_in_log'];
  $row[] = ucwords($aRow['test_reason_name']);
  $row[] = ucwords($aRow['labName']);
  $row[] = $aRow['lab_no'];
  $row[] = ucwords($aRow['vl_test_platform']);
  $row[] = $aRow['sample_name'];
  $row[] = $aRow['lab_tested_date'];
  $row[] = $aRow['date_result_printed'];
  $vlResult = '';
  if(isset($aRow['absolute_value']) && trim($aRow['absolute_value'])!= ''){
       $vlResult = $aRow['absolute_value'];
   }elseif(isset($aRow['log_value']) && trim($aRow['log_value'])!= ''){
       $vlResult = $aRow['log_value'];
   }elseif(isset($aRow['text_value']) && trim($aRow['text_value'])!= ''){
       $vlResult = $aRow['text_value'];
   }
  $row[] = $vlResult;
  
  $row[] = ucwords(str_replace("_"," ",$aRow['is_sample_rejected']));
  $row[] = ucwords($aRow['rejection_reason_name']);
  $row[] = ucwords($aRow['reviewedBy']);
  $row[] = ucwords($aRow['approvedBy']);
  $row[] = $aRow['result_approved_on'];
  $row[] = $aRow['comments'];
  $row[] = ucwords($aRow['status_name']);
  
  $output[] = $row;
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
 $filename = 'vl-result-' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
 
}
?>