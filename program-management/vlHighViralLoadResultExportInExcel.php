<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new Deforay_Commons_General();

if(isset($_SESSION['highViralResult']) && trim($_SESSION['highViralResult'])!=""){
  $rResult = $db->rawQuery($_SESSION['highViralResult']);
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Facility Name","Patient's Name","Patient ART no.","Patient phone no.","Sample Collection Date","Lab Name","Vl value in cp/ml");
 
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
   if(trim($value)!='' && trim($value)!='-- Select --'){
     $nameValue .= str_replace("_"," ",$key)." : ".$value."&nbsp;&nbsp;";
   }
 }
 $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($nameValue));
 
 foreach ($headings as $field => $value) {
   $sheet->getCellByColumnAndRow($colNo, 3)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
   $colNo++;
 }
 $sheet->getStyle('A3:F3')->applyFromArray($styleArray);
 $vlSampleId = array();
 foreach ($rResult as $aRow) {
  $row = array();
  //sample collecion date
  $sampleCollectionDate = '';
  if($aRow['sample_collection_date']!= NULL && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00 00:00:00'){
   $expStr = explode(" ",$aRow['sample_collection_date']);
   $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
  }
  
    $row[] = ucwords($aRow['facility_name']);
    $row[] = ucwords($aRow['patient_first_name']).' '.ucwords($aRow['patient_last_name']);
    $row[] = $aRow['patient_art_no'];
    $row[] = $aRow['patient_mobile_number'];
    $row[] = $sampleCollectionDate;
    $row[] = $aRow['labName'];
    $row[] = $aRow['result'];
    $vlSampleId[] = $aRow['vl_sample_id'];
  $output[] = $row;
 }
 if($_POST['markAsComplete']=='true'){
  $vlId = implode(",",$vlSampleId);
  $db->rawQuery("UPDATE vl_request_form SET contact_complete_status = 'yes' WHERE vl_sample_id IN (".$vlId.")");
 }

 $start = (count($output))+2;
 foreach ($output as $rowNo => $rowData) {
  $colNo = 0;
  foreach ($rowData as $field => $value) {
    $rRowCount = $rowNo + 4;
    $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
    $sheet->getDefaultRowDimension()->setRowHeight(18);
    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
    $sheet->getCellByColumnAndRow($colNo, $rowNo + 4)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 4)->getAlignment()->setWrapText(true);
    $colNo++;
  }
 }
 $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
 $filename = 'VLSM-High-Viral-Load-Report' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
 
}