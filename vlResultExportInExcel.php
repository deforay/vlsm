<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/PHPExcel.php');
$general=new Deforay_Commons_General();

if(isset($_SESSION['vlResultQuery']) && trim($_SESSION['vlResultQuery'])!=""){
 
 $rResult = $db->rawQuery($_SESSION['vlResultQuery']);
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Facility Name","Facility Code","Country","State","Hub Name","Batch Code","Sample Code","Unique ART No","Patient's Name","DOB","Age in years","Age in months","Other Id","Gender","Phone Number","Sample Collected On","Sample Type","Treatment Period","Treatment Initiated On","Current Regimen","Regiment Initiated On","Treatment Details","Patient Is Pregnant","ARC No","Patient Is Breastfeeding","ARV Adherence","Routine Monitoring Last VL Date","Routine Monitoring VL Value","Routine Monitoring Sample Type","VL Test After Suspected treatment failure adherence counseling VL Date","VL Test After Suspected treatment failure adherence counseling VL Value","VL Test After Suspected treatment failure adherence counseling Sample Type","Suspect Treatment Failure VL Date","Suspect Treatment Failure VL Value","Suspect Treatment Failure Sample Type","Clinician Name","Clinician Phone No","Request Date","VL Focal Person","VL Focal Person Phone Number","Email For HF","Lab Name","Lab Contact Person","Lab Phone No.","Sample Received Date","Sample Testing Date","Dispatched Date","Reviewed By","Reviewed Date","Justification","Comments","Log Value","Absolute Value","Text Value","Result","Status");
 
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
 $sheet->getStyle('A1:BD1')->applyFromArray($styleArray);
 
 
 
 foreach ($rResult as $aRow) {
  $row = array();
  if(isset($aRow['patient_dob']) && trim($aRow['patient_dob'])!='' && $aRow['patient_dob']!='0000-00-00'){
   $aRow['patient_dob']=$general->humanDateFormat($aRow['patient_dob']);
  }else{
   $aRow['patient_dob']='';
  }
  
  if(isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00'){
   $aRow['sample_collection_date']=$general->humanDateFormat($aRow['sample_collection_date']);
  }else{
   $aRow['sample_collection_date']='';
  }
  
  if(isset($aRow['treatment_initiated_date']) && trim($aRow['treatment_initiated_date'])!='' && $aRow['treatment_initiated_date']!='0000-00-00'){
   $aRow['treatment_initiated_date']=$general->humanDateFormat($aRow['treatment_initiated_date']);
  }else{
   $aRow['treatment_initiated_date']='';
  }
  
  if(isset($aRow['date_of_initiation_of_current_regimen']) && trim($aRow['date_of_initiation_of_current_regimen'])!='' && $aRow['date_of_initiation_of_current_regimen']!='0000-00-00'){
   $aRow['date_of_initiation_of_current_regimen']=$general->humanDateFormat($aRow['date_of_initiation_of_current_regimen']);
  }else{
   $aRow['date_of_initiation_of_current_regimen']='';
  }
  
  if(isset($aRow['routine_monitoring_last_vl_date']) && trim($aRow['routine_monitoring_last_vl_date'])!='' && $aRow['routine_monitoring_last_vl_date']!='0000-00-00'){
   $aRow['routine_monitoring_last_vl_date']=$general->humanDateFormat($aRow['routine_monitoring_last_vl_date']);
  }else{
   $aRow['routine_monitoring_last_vl_date']='';
  }
  
  if(isset($aRow['vl_treatment_failure_adherence_counseling_last_vl_date']) && trim($aRow['vl_treatment_failure_adherence_counseling_last_vl_date'])!='' && $aRow['vl_treatment_failure_adherence_counseling_last_vl_date']!='0000-00-00'){
   $aRow['vl_treatment_failure_adherence_counseling_last_vl_date']=$general->humanDateFormat($aRow['vl_treatment_failure_adherence_counseling_last_vl_date']);
  }else{
   $aRow['vl_treatment_failure_adherence_counseling_last_vl_date']='';
  }
  
  if(isset($aRow['suspected_treatment_failure_last_vl_date']) && trim($aRow['suspected_treatment_failure_last_vl_date'])!='' && $aRow['suspected_treatment_failure_last_vl_date']!='0000-00-00'){
   $aRow['suspected_treatment_failure_last_vl_date']=$general->humanDateFormat($aRow['suspected_treatment_failure_last_vl_date']);
  }else{
   $aRow['suspected_treatment_failure_last_vl_date']='';
  }
  
  if(isset($aRow['request_date']) && trim($aRow['request_date'])!='' && $aRow['request_date']!='0000-00-00'){
   $aRow['request_date']=$general->humanDateFormat($aRow['request_date']);
  }else{
   $aRow['request_date']='';
  }
  
  if(isset($aRow['date_sample_received_at_testing_lab']) && trim($aRow['date_sample_received_at_testing_lab'])!='' && $aRow['date_sample_received_at_testing_lab']!='0000-00-00'){
   $aRow['date_sample_received_at_testing_lab']=$general->humanDateFormat($aRow['date_sample_received_at_testing_lab']);
  }else{
   $aRow['date_sample_received_at_testing_lab']='';
  }
  
  if(isset($aRow['lab_tested_date']) && trim($aRow['lab_tested_date'])!='' && $aRow['lab_tested_date']!='0000-00-00'){
   $aRow['lab_tested_date']=$general->humanDateFormat($aRow['lab_tested_date']);
  }else{
   $aRow['lab_tested_date']='';
  }
  
  if(isset($aRow['date_results_dispatched']) && trim($aRow['date_results_dispatched'])!='' && $aRow['date_results_dispatched']!='0000-00-00'){
   $aRow['date_results_dispatched']=$general->humanDateFormat($aRow['date_results_dispatched']);
  }else{
   $aRow['date_results_dispatched']='';
  }
  
  if(isset($aRow['result_reviewed_date']) && trim($aRow['result_reviewed_date'])!='' && $aRow['result_reviewed_date']!='0000-00-00'){
   $aRow['result_reviewed_date']=$general->humanDateFormat($aRow['result_reviewed_date']);
  }else{
   $aRow['result_reviewed_date']='';
  }
  
  $row[] = ucwords($aRow['facility_name']);
  $row[] = $aRow['facility_code'];
  $row[] = $aRow['country'];
  $row[] = $aRow['state'];
  $row[] = $aRow['hub_name'];
  $row[] = $aRow['batch_code'];
  $row[] = $aRow['sample_code'];
  
  
  $row[] = $aRow['art_no'];
  $row[] = ucwords($aRow['patient_name']);
  $row[] = $aRow['patient_dob'];
  $row[] = $aRow['age_in_yrs'];
  $row[] = $aRow['age_in_mnts'];
  $row[] = $aRow['other_id'];
  $row[] = $aRow['gender'];
  
  $row[] = $aRow['patient_phone_number'];
  $row[] = $aRow['sample_collection_date'];
  $row[] = ucwords($aRow['sample_name']);
  $row[] = $aRow['treatment_initiation'];
  $row[] = $aRow['treatment_initiated_date'];
  $row[] = $aRow['art_code'];
  $row[] = $aRow['date_of_initiation_of_current_regimen'];
  $row[] = $aRow['treatment_details'];
  $row[] = $aRow['is_patient_pregnant'];
  $row[] = $aRow['arc_no'];
  $row[] = $aRow['is_patient_breastfeeding'];
  $row[] = $aRow['arv_adherence'];
  
  
  $row[] = $aRow['routine_monitoring_last_vl_date'];
  $row[] = $aRow['routine_monitoring_value'];
  $row[] = $aRow['routineSampleName'];
  
  $row[] = $aRow['vl_treatment_failure_adherence_counseling_last_vl_date'];
  $row[] = $aRow['vl_treatment_failure_adherence_counseling_value'];
  $row[] = $aRow['failureSampleName'];
  
  $row[] = $aRow['suspected_treatment_failure_last_vl_date'];
  $row[] = $aRow['suspected_treatment_failure_value'];
  $row[] = $aRow['suspectedSampleName'];
  
  $row[] = $aRow['request_clinician'];
  $row[] = $aRow['clinician_ph_no'];
  $row[] = $aRow['request_date'];
  $row[] = $aRow['vl_focal_person'];
  $row[] = $aRow['focal_person_phone_number'];
  $row[] = $aRow['email_for_HF'];
  
  $row[] = $aRow['lab_name'];
  $row[] = $aRow['lab_contact_person'];
  $row[] = $aRow['lab_phone_no'];
  $row[] = $aRow['date_sample_received_at_testing_lab'];
  $row[] = $aRow['lab_tested_date'];
  $row[] = $aRow['date_results_dispatched'];
  
  $row[] = $aRow['result_reviewed_by'];
  $row[] = $aRow['result_reviewed_date'];
  $row[] = $aRow['justification'];
  $row[] = $aRow['comments'];
  $row[] = $aRow['log_value'];
  $row[] = $aRow['absolute_value'];
  $row[] = $aRow['text_value'];
  
  $row[] = ucwords($aRow['result']); 
  
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
 $writer->save("./temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
 
}
?>