<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new General();

if(isset($_SESSION['resultNotAvailable']) && trim($_SESSION['resultNotAvailable'])!=""){
  $rResult = $db->rawQuery($_SESSION['resultNotAvailable']);
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Facility Name","Patient ART no.","Patient Name","Sample Collection Date","Lab Name");
 
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
 $sheet->getStyle('A3:A3')->applyFromArray($styleArray);
 $sheet->getStyle('B3:B3')->applyFromArray($styleArray);
 $sheet->getStyle('C3:C3')->applyFromArray($styleArray);
 $sheet->getStyle('D3:D3')->applyFromArray($styleArray);
 $sheet->getStyle('E3:E3')->applyFromArray($styleArray);
 
 foreach ($rResult as $aRow) {
  $row = array();
  //sample collecion date
  $sampleCollectionDate = '';
  if($aRow['sample_collection_date']!= NULL && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00 00:00:00'){
   $expStr = explode(" ",$aRow['sample_collection_date']);
   $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
  }
  if($aRow['remote_sample']=='yes'){
    $sampleId = $aRow['remote_sample_code'];
  }else{
    $sampleId = $aRow['sample_code'];
  }
  if($aRow['patient_first_name']!=''){
    $patientFirstName = $general->crypto('decrypt',$aRow['patient_first_name'],$sampleId);
  }else{
    $patientFirstName = '';
  }
  if($aRow['patient_middle_name']!=''){
    $patientMiddleName = $general->crypto('decrypt',$aRow['patient_middle_name'],$sampleId);
  }else{
    $patientMiddleName = '';
  }
  if($aRow['patient_last_name']!=''){
    $patientLastName = $general->crypto('decrypt',$aRow['patient_last_name'],$sampleId);
  }else{
    $patientLastName = '';
  }

    $row[] = ucwords($aRow['facility_name']);
    $row[] = $aRow['patient_art_no'];
    $row[] = ucwords($patientFirstName).ucwords($patientMiddleName).ucwords($patientLastName);
    $row[] = $sampleCollectionDate;
    $row[] = ucwords($aRow['labName']);
  $output[] = $row;
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
 $filename = 'VLSM-Results-not-available-report' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
 
}
?>