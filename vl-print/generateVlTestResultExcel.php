<?php
ob_start();
include('../includes/MysqliDb.php');
include ('../includes/PHPExcel.php');
include('../General.php');
$general=new Deforay_Commons_General();
//get other config details
$geQuery="SELECT * FROM other_config WHERE type = 'result'";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
   $mailconf[$row['name']] = $row['value'];
}

$filedGroup = array();
if(isset($mailconf['rs_field']) && trim($mailconf['rs_field'])!= ''){
     //Excel code start
     $excel = new PHPExcel();
     $sheet = $excel->getActiveSheet();
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
    $filedGroup = explode(",",$mailconf['rs_field']);
    $headings = $filedGroup;
    //Set heading row
     $sheet->getCellByColumnAndRow(0, 1)->setValueExplicit(html_entity_decode('Sample'), PHPExcel_Cell_DataType::TYPE_STRING);
     $cellName = $sheet->getCellByColumnAndRow(0,1)->getColumn();
     $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
     $colNo = 1;
    foreach ($headings as $field => $value) {
     $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
     $cellName = $sheet->getCellByColumnAndRow($colNo,1)->getColumn();
     $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
     $colNo++;
    }
    //Set query and values
    $where = '';
    if(isset($_POST['rltSampleType']) && trim($_POST['rltSampleType'])== 'result'){
      $where = 'where result != ""';
    }else if(isset($_POST['rltSampleType']) && trim($_POST['rltSampleType'])== 'noresult'){
      $where = 'where result IS NULL OR result = ""';
    }
    $sampleQuery='SELECT vl_sample_id,sample_code FROM vl_request_form '.$where;
    $sampleResult = $db->rawQuery($sampleQuery);
      $output = array();
      foreach($sampleResult as $sample){
         $row = array();
         $row[] = $sample['sample_code'];
         for($f=0;$f<count($filedGroup);$f++){
            if($filedGroup[$f] == "Form Serial No"){
               $field = 'serial_no';
            }elseif($filedGroup[$f] == "Urgency"){
                 $field = 'urgency';
            }elseif($filedGroup[$f] == "Province"){
                 $field = 'state';
            }elseif($filedGroup[$f] == "District Name"){
                 $field = 'district';
            }elseif($filedGroup[$f] == "Clinic Name"){
                 $field = 'facility_name';
            }elseif($filedGroup[$f] == "Clinician Name"){
                 $field = 'lab_contact_person';
            }elseif($filedGroup[$f] == "Sample Collection Date"){
                 $field = 'sample_collection_date';
            }elseif($filedGroup[$f] == "Sample Received Date"){
                 $field = 'date_sample_received_at_testing_lab';
            }elseif($filedGroup[$f] == "Collected by (Initials)"){
                 $field = 'collected_by';
            }elseif($filedGroup[$f] == "Gender"){
                 $field = 'gender';
            }elseif($filedGroup[$f] == "Date Of Birth"){
                 $field = 'patient_dob';
            }elseif($filedGroup[$f] == "Age in years"){
                 $field = 'age_in_yrs';
            }elseif($filedGroup[$f] == "Age in months"){
                 $field = 'age_in_mnts';
            }elseif($filedGroup[$f] == "Is Patient Pregnant?"){
                 $field = 'is_patient_pregnant';
            }elseif($filedGroup[$f] == "Is Patient Breastfeeding?"){
                 $field = 'is_patient_breastfeeding';
            }elseif($filedGroup[$f] == "Patient OI/ART Number"){
                 $field = 'art_no';
            }elseif($filedGroup[$f] == "Date Of ART Initiation"){
                 $field = 'date_of_initiation_of_current_regimen';
            }elseif($filedGroup[$f] == "ART Regimen"){
                 $field = 'current_regimen';
            }elseif($filedGroup[$f] == "Patient consent to SMS Notification?"){
                 $field = 'patient_receive_sms';
            }elseif($filedGroup[$f] == "Patient Mobile Number"){
                 $field = 'patient_phone_number';
            }elseif($filedGroup[$f] == "Date Of Last Viral Load Test"){
                 $field = 'last_viral_load_date';
            }elseif($filedGroup[$f] == "Result Of Last Viral Load"){
                 $field = 'last_viral_load_result';
            }elseif($filedGroup[$f] == "Viral Load Log"){
                 $field = 'viral_load_log';
            }elseif($filedGroup[$f] == "Reason For VL Test"){
                 $field = 'vl_test_reason';
            }elseif($filedGroup[$f] == "Lab Name"){
                 $field = 'lab_name';
            }elseif($filedGroup[$f] == "VL Testing Platform"){
                 $field = 'vl_test_platform';
            }elseif($filedGroup[$f] == "Specimen type"){
                 $field = 'sample_name';
            }elseif($filedGroup[$f] == "Sample Testing Date"){
                 $field = 'lab_tested_date';
            }elseif($filedGroup[$f] == "Viral Load Result(copiesl/ml)"){
                 $field = 'absolute_value';
            }elseif($filedGroup[$f] == "Log Value"){
                 $field = 'log_value';
            }elseif($filedGroup[$f] == "If no result"){
                 $field = 'rejection';
            }elseif($filedGroup[$f] == "Rejection Reason"){
                 $field = 'rejection_reason_name';
            }elseif($filedGroup[$f] == "Reviewed By"){
                 $field = 'result_reviewed_by';
            }elseif($filedGroup[$f] == "Approved By"){
                 $field = 'result_approved_by';
            }elseif($filedGroup[$f] == "Laboratory Scientist Comments"){
                 $field = 'comments';
            }
            if($field ==  'result_reviewed_by'){
               $fValueQuery="SELECT u.user_name as reviewedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_reviewed_by where vl.vl_sample_id = '".$sample['vl_sample_id']."'";
            }elseif($field ==  'result_approved_by'){
                $fValueQuery="SELECT u.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_approved_by where vl.vl_sample_id = '".$sample['vl_sample_id']."'";
            }else{
              $fValueQuery="SELECT $field FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason where vl.vl_sample_id = '".$sample['vl_sample_id']."'";
            }
            $fValueResult = $db->rawQuery($fValueQuery);
            $fieldValue = '';
            if(count($fValueResult) >0){
                if($field == 'sample_collection_date' || $field == 'date_sample_received_at_testing_lab' || $field == 'lab_tested_date'){
                    if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00 00:00:00'){
                        $xplodDate = explode(" ",$fValueResult[0][$field]);
                        $fieldValue=$general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];  
                    }
               }elseif($field == 'patient_dob' || $field == 'date_of_initiation_of_current_regimen' || $field == 'last_viral_load_date'){
                    if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00'){
                        $fieldValue=$general->humanDateFormat($fValueResult[0][$field]);
                    }
               }elseif($field ==  'vl_test_platform' || $field ==  'gender'){
                 $fieldValue = ucwords(str_replace("_"," ",$fValueResult[0][$field]));
               }elseif($field ==  'result_reviewed_by'){
                 $fieldValue = $fValueResult[0]['reviewedBy'];
               }elseif($field ==  'result_approved_by'){
                 $fieldValue = $fValueResult[0]['approvedBy'];
               }else{
                 $fieldValue = $fValueResult[0][$field];
               }
            }
           $row[] = $fieldValue;
         }
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
     $filename = '';
     $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
     $filename = 'vl-test-result-' . date('d-M-Y-H-i-s') . '.xls';
     $pathFront=realpath('../temporary');
     $writer->save($pathFront. DIRECTORY_SEPARATOR . $filename);
    echo $filename;
}else{
    echo $filename = '';
}