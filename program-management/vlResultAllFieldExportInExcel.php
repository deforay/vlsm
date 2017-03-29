<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
 $general=new Deforay_Commons_General();

 $sQuery="SELECT vl.*,s.sample_name,s.status as sample_type_status,ts.*,f.facility_name,l_f.facility_name as labName,f.facility_code,f.state,f.district,f.phone_number,f.address,f.hub_name,f.contact_person,f.report_email,f.country,f.longitude,f.latitude,f.facility_type,f.status as facility_status,ft.facility_type_name,lft.facility_type_name as labFacilityTypeName,l_f.facility_name as labName,l_f.facility_code as labCode,l_f.state as labState,l_f.district as labDistrict,l_f.phone_number as labPhone,l_f.address as labAddress,l_f.hub_name as LabHub,l_f.contact_person as labContactPerson,l_f.report_email as labReportMail,l_f.country as labCountry,l_f.longitude as labLongitude,l_f.latitude as labLatitude,l_f.facility_type as labFacilityType,l_f.status as labFacilityStatus,tr.test_reason_name,tr.test_reason_status FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN r_testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_vl_test_reasons as tr ON tr.test_reason_id=vl.vl_test_reason LEFT JOIN facility_type as ft ON ft.facility_type_id=f.facility_type LEFT JOIN facility_type as lft ON lft.facility_type_id=l_f.facility_type";

 $rResult = $db->rawQuery($sQuery);
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Serial No.","Instance Id","Gender","Age In Years","Clinic Name","Facility Code","Facility State","Facility District","Facility Phone Number","Facility Address","Facility HUB Name","Facility Contact Person","Facility Report Mail","Facility Country","Facility Longitude","Facility Latitude","Facility Status","Facility Type","Sample Type","Sample Type Status","Sample Collection Date","LAB Name","Lab Code","Lab State","Lab District","Lab Phone Number","Lab Address","Lab HUB Name","Lab Contact Person","Lab Report Mail","Lab Country","Lab Longitude","Lab Latitude","Lab Status","Lab Type","Lab Tested Date","Log Value","Absolute Value","Text Value","Absolute Decimal Value","Result","Testing Reason","Test Reason Status","Testing Status");
 
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
  if($aRow['lab_tested_date']=='0000-00-00 00:00:00')
  {
   $aRow['lab_tested_date'] = '';
  }
  if($aRow['sample_collection_date']=='0000-00-00 00:00:00')
  {
   $aRow['sample_collection_date'] = '';
  }
  $row[] = $aRow['serial_no'];
  $row[] = $aRow['vl_instance_id'];
  $row[] = ucwords(str_replace("_"," ",$aRow['gender']));
  $row[] = $aRow['age_in_yrs'];
  $row[] = ucwords($aRow['facility_name']);
  $row[] = ucwords($aRow['facility_code']);
  $row[] = ucwords($aRow['state']);
  $row[] = ucwords($aRow['district']);
  $row[] = ucwords($aRow['phone_number']);
  $row[] = ucwords($aRow['address']);
  $row[] = ucwords($aRow['hub_name']);
  $row[] = ucwords($aRow['contact_person']);
  $row[] = ucwords($aRow['report_email']);
  $row[] = ucwords($aRow['country']);
  $row[] = ucwords($aRow['longitude']);
  $row[] = ucwords($aRow['latitude']);
  $row[] = ucwords($aRow['facility_status']);
  $row[] = ucwords($aRow['facility_type_name']);
  $row[] = $aRow['sample_name'];
  $row[] = $aRow['sample_type_status'];
  $row[] = $aRow['sample_collection_date'];
  $row[] = ucwords($aRow['labName']);
  $row[] = ucwords($aRow['labCode']);
  $row[] = ucwords($aRow['labState']);
  $row[] = ucwords($aRow['labDistrict']);
  $row[] = $aRow['labPhone'];
  $row[] = $aRow['labAddress'];
  $row[] = $aRow['labHub'];
  $row[] = ucwords($aRow['labContactPerson']);
  $row[] = ucwords($aRow['labReportMail']);
  $row[] = ucwords($aRow['labCountry']);
  $row[] = ucwords($aRow['labLongitude']);
  $row[] = ucwords($aRow['labLatitude']);
  $row[] = ucwords($aRow['labFacilityStatus']);
  $row[] = ucwords($aRow['labFacilityTypeName']);
  $row[] = $aRow['lab_tested_date'];
  $row[] = $aRow['log_value'];
  $row[] = $aRow['absolute_value'];
  $row[] = $aRow['text_value'];
  $row[] = $aRow['absolute_decimal_value'];
  $vlResult = '';
  if(isset($aRow['absolute_value']) && trim($aRow['absolute_value'])!= ''){
       $vlResult = $aRow['absolute_value'];
   }elseif(isset($aRow['log_value']) && trim($aRow['log_value'])!= ''){
       $vlResult = $aRow['log_value'];
   }elseif(isset($aRow['text_value']) && trim($aRow['text_value'])!= ''){
       $vlResult = $aRow['text_value'];
   }
  $row[] = $vlResult;
  $row[] = ucwords($aRow['test_reason_name']);
  $row[] = ucwords($aRow['test_reason_status']);
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
 $filename = 'vl-result-' . date('d-M-Y-H-i-s') . '.csv';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
?>