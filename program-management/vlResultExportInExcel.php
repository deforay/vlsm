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
 
 $headings = array("Sample ID","Batch Code","Urgency","Province/State","District/County","Clinic Name","Clinician Name","Sample Collection Date","Sample Received Date","Collected By","Patient Name","Gender","DOB","Age In Years","Age In Months","Patient Pregnant","Patient BreastFeeding","ART Number","ART Initiation","ART Regimen","SMS Notification","Mobile Number","Date Of Last Viral Load Test","Result Of Last Viral Load","Viral Load Log","Reason For VL Test","LAB Name","LAB No.","VL Testing Platform","Specimen Type","Sample Testing Date","Last Print On","Viral Load Result","No Result","Rejection Reason","Reviewed By","Approved By","Approved On","Comments","Status");
 
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

 $sheet->mergeCells('A1:Z1');
 $nameValue = '';
 foreach($_POST as $key=>$value){
   if(trim($value)!='' && trim($value)!='-- Select --'){
     $nameValue .= str_replace("_"," ",$key)." : ".$value."&nbsp;&nbsp;";
   }
 }
 $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($nameValue));

 //if($_POST['sampleCollectionDate']!='' || $_POST['batchCode']!='-- Select --' || $_POST['sampleType']!='-- Select --' || $_POST['facilityName']!='-- Select --' || $_POST['sampleTestDate']!='' || $_POST['vLoad']!='-- Select --' || $_POST['printDate']!='' || $_POST['gender']!='-- Select --' || $_POST['status']!='-- Select --' || $_POST['showReordSample']!='-- Select --')
 //{
 // 
 //}
 
 foreach ($headings as $field => $value) {
  $sheet->getCellByColumnAndRow($colNo, 3)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
  $colNo++;
 }
 $sheet->getStyle('A3:AN3')->applyFromArray($styleArray);
 
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
  
  if(isset($aRow['sample_tested_datetime']) && trim($aRow['sample_tested_datetime'])!='' && $aRow['sample_tested_datetime']!='0000-00-00'){
   $expStr=explode(" ",$aRow['sample_tested_datetime']);
   $aRow['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['sample_tested_datetime']='';
  }
  if(isset($aRow['last_viral_load_date']) && trim($aRow['last_viral_load_date'])!='' && $aRow['last_viral_load_date']!='0000-00-00'){
 $aRow['last_viral_load_date']=$general->humanDateFormat($aRow['last_viral_load_date']);
  }else{
   $aRow['last_viral_load_date']='';
  }
  
  if(isset($aRow['sample_received_at_vl_lab_datetime']) && trim($aRow['sample_received_at_vl_lab_datetime'])!='' && $aRow['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['sample_received_at_vl_lab_datetime']);
   $aRow['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['sample_received_at_vl_lab_datetime']='';
  }
  if(isset($aRow['result_approved_datetime']) && trim($aRow['result_approved_datetime'])!='' && $aRow['result_approved_datetime']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['result_approved_datetime']);
   $aRow['result_approved_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['result_approved_datetime']='';
  }
  
  if(isset($aRow['result_printed_datetime']) && trim($aRow['result_printed_datetime'])!='' && $aRow['result_printed_datetime']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$aRow['result_printed_datetime']);
   $aRow['result_printed_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $aRow['result_printed_datetime']='';
  }
  
  $row[] = $aRow['sample_code'];
  $row[] = $aRow['batch_code'];
  $row[] = ucwords($aRow['test_urgency']);
  $row[] = ucwords($aRow['facility_state']);
  $row[] = ucwords($aRow['facility_district']);
  $row[] = ucwords($aRow['facility_name']);
  $row[] = ucwords($aRow['lab_contact_person']);
  $row[] = $aRow['sample_collection_date'];
  $row[] = $aRow['sample_received_at_vl_lab_datetime'];
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
  $row[] = $aRow['lab_code'];
  $row[] = ucwords($aRow['vl_test_platform']);
  $row[] = $aRow['sample_name'];
  $row[] = $aRow['sample_tested_datetime'];
  $row[] = $aRow['result_printed_datetime'];
  $vlResult = '';
  if(isset($aRow['result_value_absolute']) && trim($aRow['result_value_absolute'])!= ''){
    $vlResult = $aRow['result_value_absolute'];
   }elseif(isset($aRow['result_value_log']) && trim($aRow['result_value_log'])!= ''){
    $vlResult = $aRow['result_value_log'];
   }elseif(isset($aRow['result_value_text']) && trim($aRow['result_value_text'])!= ''){
    $vlResult = $aRow['result_value_text'];
   }
  $row[] = $vlResult;
  
  $row[] = ucwords(str_replace("_"," ",$aRow['is_sample_rejected']));
  $row[] = ucwords($aRow['rejection_reason_name']);
  $row[] = ucwords($aRow['reviewedBy']);
  $row[] = ucwords($aRow['approvedBy']);
  $row[] = $aRow['result_approved_datetime'];
  $row[] = $aRow['approver_comments'];
  $row[] = ucwords($aRow['status_name']);
  
  $output[] = $row;
 }

 $start = (count($output))+2;
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
 $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
 $filename = 'vl-result-' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
 
}
?>