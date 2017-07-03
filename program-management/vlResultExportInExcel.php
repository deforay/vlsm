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
 
 $headings = array("No.","Sample Code","Health Facility Name","Health Facility Code","District/County","Province/State","Unique ART No.","Patient Name","Date of Birth","Age","Gender","Date of Sample Collection","Sample Type","Date of Treatment Initiation","Current Regimen","Date of Initiation of Current Regimen","Is Patient Pregnant","Is Patient Breastfeeding","ARV Adherence","Indication for Viral Load Testing","Requesting Clinican","Request Date","Rejection","Date Sample Received at PHL","Date Sent to NHRL","Date Results Received at PHL","Value(Results)","Results in Log","Date Results Dispatched Facilities","TAT Result Dispatch(days)","Comments");
 
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

 $sheet->mergeCells('A1:AE1');
 $nameValue = '';
 foreach($_POST as $key=>$value){
   if(trim($value)!='' && trim($value)!='-- Select --'){
     $nameValue .= str_replace("_"," ",$key)." : ".$value."&nbsp;&nbsp;";
   }
 }
 $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($nameValue));
 
 foreach ($headings as $field => $value) {
   $sheet->getCellByColumnAndRow($colNo, 3)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
   $colNo++;
 }
 $sheet->getStyle('A3:AE3')->applyFromArray($styleArray);
 
 $no =1;
 foreach ($rResult as $aRow) {
  $row = array();
  //date of birth
  $dob = '';
  if($aRow['patient_dob']!= NULL && trim($aRow['patient_dob'])!='' && $aRow['patient_dob']!='0000-00-00'){
   $dob =  date("d-m-Y", strtotime($aRow['patient_dob']));
  }
  //set gender
  $gender = '';
  if($aRow['patient_gender'] == 'male'){
    $gender = 'M';
  }else if($aRow['patient_gender'] == 'female'){
    $gender = 'F';
  }else if($aRow['patient_gender'] == 'not_recorded'){
   $gender = 'N/R';
  }
  //sample collecion date
  $sampleCollectionDate = '';
  if($aRow['sample_collection_date']!= NULL && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00 00:00:00'){
   $expStr = explode(" ",$aRow['sample_collection_date']);
   $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
  }
  //treatment initiation date
  $treatmentInitiationDate = '';
  if($aRow['treatment_initiated_date']!= NULL && trim($aRow['treatment_initiated_date'])!='' && $aRow['treatment_initiated_date']!='0000-00-00'){
   $treatmentInitiationDate =  date("d-m-Y", strtotime($aRow['treatment_initiated_date']));
  }
  //date of initiation of current regimen
  $dateOfInitiationOfCurrentRegimen = '';
  if($aRow['date_of_initiation_of_current_regimen']!= NULL && trim($aRow['date_of_initiation_of_current_regimen'])!='' && $aRow['date_of_initiation_of_current_regimen']!='0000-00-00'){
   $dateOfInitiationOfCurrentRegimen =  date("d-m-Y", strtotime($aRow['date_of_initiation_of_current_regimen']));
  }
  //requested date
  $requestedDate = '';
  if($aRow['test_requested_on']!= NULL && trim($aRow['test_requested_on'])!='' && $aRow['test_requested_on']!='0000-00-00'){
   $requestedDate =  date("d-m-Y", strtotime($aRow['test_requested_on']));
  }
  //set ARV adherecne
  $arvAdherence = '';
  if(trim($aRow['arv_adherance_percentage']) == 'good'){
    $arvAdherence = 'Good >= 95%';
  }else if(trim($aRow['arv_adherance_percentage']) == 'fair'){
   $arvAdherence = 'Fair 85-94%';
  }else if(trim($aRow['arv_adherance_percentage']) == 'poor'){
   $arvAdherence = 'Poor <85%';
  }
  //set sample rejection
  $sampleRejection = 'No';
  if(trim($aRow['is_sample_rejected']) == 'yes' || ($aRow['reason_for_sample_rejection']!= NULL && trim($aRow['reason_for_sample_rejection'])!= '' && $aRow['reason_for_sample_rejection'] >0)){
    $sampleRejection = 'Yes';
  }
  //result dispatched date
  $resultDispatchedDate = '';
  if($aRow['result_dispatched_datetime']!= NULL && trim($aRow['result_dispatched_datetime'])!='' && $aRow['result_dispatched_datetime']!='0000-00-00 00:00:00'){
   $expStr = explode(" ",$aRow['result_dispatched_datetime']);
   $resultDispatchedDate =  date("d-m-Y", strtotime($expStr[0]));
  }
  //TAT result dispatched(in days)
  $tatdays = '';
  if(trim($sampleCollectionDate)!= '' && trim($resultDispatchedDate)!= ''){
    $sample_collection_date = strtotime($sampleCollectionDate);
    $result_dispatched_date = strtotime($resultDispatchedDate);
    $dayDiff = $result_dispatched_date - $sample_collection_date;
    $tatdays = (int)floor($dayDiff / (60 * 60 * 24));
  }
  //set result log value
  $logVal = '0.0';
  if($aRow['result_value_log']!= NULL && trim($aRow['result_value_log'])!= ''){
   $logVal = $aRow['result_value_log'];
  }else if($aRow['result_value_absolute']!= NULL && trim($aRow['result_value_absolute'])!= ''){
   $logVal = round(log10($aRow['result_value_absolute']),1);
  }
  $row[] = $no;
  $row[] = $aRow['sample_code'];
  $row[] = ucwords($aRow['facility_name']);
  $row[] = $aRow['facility_code'];
  $row[] = ucwords($aRow['facility_district']);
  $row[] = ucwords($aRow['facility_state']);
  $row[] = $aRow['patient_art_no'];
  $row[] = '';
  $row[] = $dob;
  $row[] = ($aRow['patient_age_in_years']!= NULL && trim($aRow['patient_age_in_years'])!= '' && $aRow['patient_age_in_years'] >0)?$aRow['patient_age_in_years']:0;
  $row[] = $gender;
  $row[] = $sampleCollectionDate;
  $row[] = (isset($aRow['sample_name']))?ucwords($aRow['sample_name']):'';
  $row[] = $treatmentInitiationDate;
  $row[] = $aRow['current_regimen'];
  $row[] = $dateOfInitiationOfCurrentRegimen;
  $row[] = ucfirst($aRow['is_patient_pregnant']);
  $row[] = ucfirst($aRow['is_patient_breastfeeding']);
  $row[] = $arvAdherence;
  $row[] = ucwords(str_replace("_"," ",$aRow['reason_for_vl_testing']));
  $row[] = ucwords($aRow['request_clinician_name']);
  $row[] = $requestedDate;
  $row[] = $sampleRejection;
  $row[] = '';
  $row[] = '';
  $row[] = '';
  $row[] = $aRow['result'];
  $row[] = $logVal;
  $row[] = $resultDispatchedDate;
  $row[] = $tatdays;
  $row[] = ucfirst($aRow['approver_comments']);
  $output[] = $row;
  $no++;
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