<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
  


 
$general=new \App\Models\General();
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

//system config
$systemConfigQuery ="SELECT * from system_config";
$systemConfigResult=$db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
     $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}


if(isset($_SESSION['highViralResult']) && trim($_SESSION['highViralResult'])!=""){
     $rResult = $db->rawQuery($_SESSION['highViralResult']);

     $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
     $output = array();
     $sheet = $excel->getActiveSheet();
     $headings = array('Sample Code','Remote Sample Code',"Facility Name","Patient ART no.","Patient's Name","Patient phone no.","Sample Collection Date","Sample Tested Date","Lab Name","VL Result in cp/ml");
     if($sarr['sc_user_type']=='standalone') {
     $headings = array('Sample Code',"Facility Name","Patient ART no.","Patient's Name","Patient phone no.","Sample Collection Date","Sample Tested Date","Lab Name","VL Result in cp/ml");
     }

     $colNo = 1;

     $styleArray = array(
          'font' => array(
               'bold' => true,
               'size' => '13',
          ),
          'alignment' => array(
               'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
               'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
          ),
          'borders' => array(
               'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
               ),
          )
     );

     $borderStyle = array(
          'alignment' => array(
               'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
          ),
          'borders' => array(
               'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
               ),
          )
     );

     $sheet->mergeCells('A1:AE1');
     $nameValue = '';

     $filters = array(
          'hvlSampleTestDate' => 'Sample Test Date',
          'hvlBatchCode' => 'Batch Code',
          'hvlSampleType' => 'Sample Type',
          'hvlFacilityName' => 'Facility Name',
          'hvlContactStatus' => 'Contact Status',
          'hvlGender' => 'Gender',
          'hvlPatientPregnant' => 'Is Patient Pregnant',
          'hvlPatientBreastfeeding' => 'Is Patient Breastfeeding'
     );

     foreach($_POST as $key=>$value){
          if(trim($value)!='' && trim($value)!='-- Select --' && trim($key)!='markAsComplete'){
               $nameValue .= str_replace("_"," ",$key)." : ".$value."&nbsp;&nbsp;";
          }
     }
     $sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '1')
		->setValueExplicit(html_entity_decode($nameValue), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

     foreach ($headings as $field => $value) {
          $sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '3')
				->setValueExplicit(html_entity_decode($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
          $colNo++;
     }
     $sheet->getStyle('A3:A3')->applyFromArray($styleArray);
     $sheet->getStyle('B3:B3')->applyFromArray($styleArray);
     $sheet->getStyle('C3:C3')->applyFromArray($styleArray);
     $sheet->getStyle('D3:D3')->applyFromArray($styleArray);
     $sheet->getStyle('E3:E3')->applyFromArray($styleArray);
     $sheet->getStyle('F3:F3')->applyFromArray($styleArray);
     $sheet->getStyle('G3:G3')->applyFromArray($styleArray);
     $sheet->getStyle('H3:H3')->applyFromArray($styleArray);
     $sheet->getStyle('I3:I3')->applyFromArray($styleArray);
     if($sarr['sc_user_type']!='standalone') {
        $sheet->getStyle('J3:J3')->applyFromArray($styleArray);
     }

     $vlSampleId = array();
     foreach ($rResult as $aRow) {
          $row = array();
          //sample collecion date
          $sampleCollectionDate = '';$sampleTestDate = '';
          if($aRow['sample_collection_date']!= null && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00 00:00:00'){
               $expStr = explode(" ",$aRow['sample_collection_date']);
               $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
          }
          if($aRow['sample_tested_datetime']!= null && trim($aRow['sample_tested_datetime'])!='' && $aRow['sample_tested_datetime']!='0000-00-00 00:00:00'){
               $expStr = explode(" ",$aRow['sample_tested_datetime']);
               $sampleTestDate =  date("d-m-Y", strtotime($expStr[0]));
          }

          if($aRow['patient_first_name']!=''){
               $patientFname = ($general->crypto('decrypt',$aRow['patient_first_name'],$aRow['patient_art_no']));
          }else{
               $patientFname = '';
          }
          if($aRow['patient_middle_name']!=''){
               $patientMname = ($general->crypto('decrypt',$aRow['patient_middle_name'],$aRow['patient_art_no']));
          }else{
               $patientMname = '';
          }
          if($aRow['patient_last_name']!=''){
               $patientLname = ($general->crypto('decrypt',$aRow['patient_last_name'],$aRow['patient_art_no']));
          }else{
               $patientLname = '';
          }
          $row[] = $aRow['sample_code'];
          if($sarr['sc_user_type']!='standalone'){
           $row[] = $aRow['remote_sample_code'];
            }
          $row[] = ($aRow['facility_name']);
          $row[] = $aRow['patient_art_no'];
          $row[] = ($patientFname." ".$patientMname." ".$patientLname);
          $row[] = $aRow['patient_mobile_number'];
          $row[] = $sampleCollectionDate;
          $row[] = $sampleTestDate;
          $row[] = $aRow['labName'];
          $row[] = $aRow['result'];
          $vlSampleId[] = $aRow['vl_sample_id'];
          $output[] = $row;
     }
     if($_POST['markAsComplete']=='true'){
          $vlId = implode(",",$vlSampleId);
          if(!empty($vlId))
               $db->rawQuery("UPDATE form_vl SET contact_complete_status = 'yes' WHERE vl_sample_id IN (".$vlId.")");
     }

     $start = (count($output))+2;
     foreach ($output as $rowNo => $rowData) {
          $colNo = 1;
          $rRowCount = $rowNo + 4;
          foreach ($rowData as $field => $value) {
               $sheet->setCellValue(
				Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
				html_entity_decode($value)
			);
               $colNo++;
          }
     }
     $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
     $filename = 'VLSM-High-Viral-Load-Report' . date('d-M-Y-H-i-s') . '.xlsx';
     $writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
     echo $filename;

}
