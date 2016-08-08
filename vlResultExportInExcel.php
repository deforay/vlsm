<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
//include ('./includes/PHPExcel');
include ('./includes/PHPExcel.php');

if(isset($_SESSION['vlResultQuery']) && trim($_SESSION['vlResultQuery'])!=""){
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 $rResult = $db->rawQuery($_SESSION['vlResultQuery']);
 
 $headings = array("Sample Code","Batch Code","Unique ART No","Patient's Name","Facility Name","Facility Code","Sample Type",'Result',"Status");
 $colNo = 0;
 foreach ($headings as $field => $value) {
  $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
  $colNo++;
 }
 foreach ($rResult as $aRow) {
  $row = array();
  $row[] = $aRow['sample_code'];
  $row[] = $aRow['batch_code'];
  $row[] = $aRow['art_no'];
  $row[] = ucwords($aRow['patient_name']);
  $row[] = ucwords($aRow['facility_name']);
  $row[] = $aRow['facility_code'];
  $row[] = ucwords($aRow['sample_name']);
  $row[] = ucwords($aRow['result']);
  $row[] = ucwords($aRow['status']);
  
  $output[] = $row;
 }
 
 foreach ($output as $rowNo => $rowData) {
  $colNo = 0;
  foreach ($rowData as $field => $value) {
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