<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../includes/General.php');
include ('../includes/PHPExcel.php');
$general=new General();

 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
 
 $headings = array("Sample Collection Date","Facility Name","Rejection Reason","Reason Type","No. of Records");
 
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

 $sheet->mergeCells('A1:E1');
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
 $sheet->getStyle('A3:E3')->applyFromArray($styleArray);
 $general=new General();
$configFormQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
//date
$start_date = '';
$end_date = '';
$sWhere ='';
if(isset($_POST['sample_collection_date']) && trim($_POST['sample_collection_date'])!= ''){
   $s_c_date = explode("to", $_POST['sample_collection_date']);
   //print_r($s_c_date);die;
   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
     $start_date = $general->dateFormat(trim($s_c_date[0]));
   }
   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
     $end_date = $general->dateFormat(trim($s_c_date[1]));
   }
   //get value by rejection reason id
   $vlQuery = "select vl.reason_for_sample_rejection,sr.rejection_reason_name,sr.rejection_type,sr.rejection_reason_code,fd.facility_name from vl_request_form as vl inner join r_sample_rejection_reasons as sr ON sr.rejection_reason_id=vl.reason_for_sample_rejection inner join facility_details as fd ON fd.facility_id=vl.facility_id";
   $sWhere.= ' where DATE(vl.sample_collection_date) <= "'.$end_date.'" AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'" AND reason_for_sample_rejection!="" AND reason_for_sample_rejection IS NOT NULL';
   $vlQuery = $vlQuery.$sWhere." group by reason_for_sample_rejection";
   $vlResult = $db->rawQuery($vlQuery);
   $rejectionType = array();
   foreach($vlResult as $rejectedResult){
	  $tQuery="select COUNT(vl_sample_id) as total,vl.sample_collection_date,fd.facility_name FROM vl_request_form as vl INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_type inner join facility_details as fd ON fd.facility_id=vl.facility_id where vl.vlsm_country_id='".$configFormResult[0]['value']."' AND vl.reason_for_sample_rejection=".$rejectedResult['reason_for_sample_rejection'];
	  //filter
	  $sWhere = '';
	  if(isset($_POST['sample_collection_date']) && trim($_POST['sample_collection_date'])!= ''){
	    $sWhere.= ' AND DATE(vl.sample_collection_date) <= "'.$end_date.' 23:59:00" AND DATE(vl.sample_collection_date) >= "'.$start_date.' 00:00:00"';
	  }
	  if(isset($_POST['sample_type']) && trim($_POST['sample_type'])!= ''){
	    $sWhere.= ' AND s.sample_id = "'.$_POST['sample_type'].'"';
	  }
	  if(isset($_POST['lab_name']) && trim($_POST['lab_name'])!= ''){
	    $sWhere.= ' AND vl.lab_id = "'.$_POST['lab_name'].'"';
	  }
	  if(isset($_POST['clinic_name']) && is_array($_POST['clinic_name']) && count($_POST['clinic_name']) > 0){
	    $sWhere.= " AND vl.facility_id IN (".implode(',',$_POST['clinic_name']).")";
	  }
	  $tQuery = $tQuery.' '.$sWhere;
	  $tResult[$rejectedResult['rejection_reason_code']] = $db->rawQuery($tQuery);
	  $tResult[$rejectedResult['rejection_reason_code']][0]['rejection_reason_name'] = $rejectedResult['rejection_reason_name']; 
	  $tableResult[$rejectedResult['rejection_reason_code']] = $db->rawQuery($tQuery);
	  if($tableResult[$rejectedResult['rejection_reason_code']][0]['total']==0){
		 unset($tableResult[$rejectedResult['rejection_reason_code']]);
	  }else{
		$tableResult[$rejectedResult['rejection_reason_code']][0]['rejection_type'] = $rejectedResult['rejection_type'];
		 $tableResult[$rejectedResult['rejection_reason_code']][0]['rejection_reason_name'] = $rejectedResult['rejection_reason_name']; 
	  }
   }
  
}
foreach($tableResult as $key=>$rejectedData){
    $row = array();
    //sample collecion date
    $sampleCollectionDate = '';
    if($rejectedData[0]['sample_collection_date']!= NULL && trim($rejectedData[0]['sample_collection_date'])!='' && $rejectedData[0]['sample_collection_date']!='0000-00-00 00:00:00'){
    $expStr = explode(" ",$rejectedData[0]['sample_collection_date']);
    $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
    }
    $row[] = $sampleCollectionDate;
    $row[] = ucwords($rejectedData[0]['facility_name']);
    $row[] = ucwords($rejectedData[0]['rejection_reason_name']);
    $row[] = ucwords($rejectedData[0]['rejection_type']);
    $row[] = $rejectedData[0]['total'];
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
 $filename = 'VLSM-Rejected-Data-report' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;

?>