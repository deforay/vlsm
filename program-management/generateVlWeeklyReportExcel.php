<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new Deforay_Commons_General();
$configQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($configQuery);
$country = $configResult[0]['value'];
if(isset($_SESSION['vlStatisticsQuery']) && trim($_SESSION['vlStatisticsQuery'])!=""){
    //excel code start
    $excel = new PHPExcel();
    $sheet = $excel->getActiveSheet();
    $headingStyle = array(
        'font' => array(
            'bold' => true,
            'size' => '11',
        ),
        'alignment' => array(
            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        )
    );
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
    
    if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
      $s_t_date = explode("to", $_POST['reportedDate']);
      if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
        $start_date = $general->dateFormat(trim($s_t_date[0]));
      }
      if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
        $end_date = $general->dateFormat(trim($s_t_date[1]));
      }
    }
    
    if(isset($_POST['lab']) && $_POST['lab']!= '' && count(array_filter($_POST['lab']))> 0){
       $lab = implode(',',$_POST['lab']);
       $vlLabQuery="SELECT * FROM facility_details where facility_id IN ('$lab') AND status='active'";
       $vlLabResult = $db->rawQuery($vlLabQuery);
    }else{
       $vlLabQuery="SELECT * FROM facility_details where facility_type = 2 AND status='active'";
       $vlLabResult = $db->rawQuery($vlLabQuery);
    }
    
    $sheet->mergeCells('C1:E1');
    
    $sheet->setCellValue('B1', html_entity_decode('Reported Date ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('C1', html_entity_decode($_POST['reportedDate'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('B2', html_entity_decode('Province ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('C2', html_entity_decode('District ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('D2', html_entity_decode('Super Lab Name ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('E2', html_entity_decode('IPSL ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('F2', html_entity_decode('Total Number of VL samples Received at Laboratory ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('G2', html_entity_decode('Total Number of Viral load tests done (inc failed tests) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('H2', html_entity_decode('No. of Samples Not Tested ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('I2', html_entity_decode('Assay Failure Rate(%) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('J2', html_entity_decode('Average Result TAT (lab) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('K2', html_entity_decode('Average Result TAT -Total (from sample  collection to results getting to the facility/hub) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('L2', html_entity_decode('Viral Load PT date ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('M2', html_entity_decode('Viral Load PT Result (Pass/ Fail) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('N2', html_entity_decode('Viral Load PT Corrective Actions Completed (Yes/No/NA) ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('O2', html_entity_decode('Red Flags & Highlights ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
    
    $sheet->getStyle('B1')->applyFromArray($headingStyle);
    $sheet->getStyle('C1')->applyFromArray($headingStyle);
    $sheet->getStyle('B2')->applyFromArray($styleArray);
    $sheet->getStyle('C2')->applyFromArray($styleArray);
    $sheet->getStyle('D2')->applyFromArray($styleArray);
    $sheet->getStyle('E2')->applyFromArray($styleArray);
    $sheet->getStyle('F2')->applyFromArray($styleArray);
    $sheet->getStyle('G2')->applyFromArray($styleArray);
    $sheet->getStyle('H2')->applyFromArray($styleArray);
    $sheet->getStyle('I2')->applyFromArray($styleArray);
    $sheet->getStyle('J2')->applyFromArray($styleArray);
    $sheet->getStyle('K2')->applyFromArray($styleArray);
    $sheet->getStyle('L2')->applyFromArray($styleArray);
    $sheet->getStyle('M2')->applyFromArray($styleArray);
    $sheet->getStyle('N2')->applyFromArray($styleArray);
    $sheet->getStyle('O2')->applyFromArray($styleArray);
    
    $output = array();
    $r=1;
    foreach ($vlLabResult as $vlLab) {
       $sQuery="SELECT vl.vl_sample_id,vl.sample_collection_date,vl.date_sample_received_at_testing_lab,vl.lab_tested_date,vl.date_results_dispatched FROM vl_request_form as vl WHERE vl.lab_id = '".$vlLab['facility_id']."' AND vl.form_id = '".$country."'";
       if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
          if (trim($start_date) == trim($end_date)) {
            $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
          }else{
            $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
          }
       }
       $sResult = $db->rawQuery($sQuery);
       $noOfSampleReceivedAtLab = array();
       $noOfSampleTested = array();
       $noOfSampleNotTested = array();
       $resultTat = array();
       $resultDTat = array();
       foreach($sResult as $result){
         $sampleCollectionDate = '';
         $dateOfSampleReceivedAtTestingLab = '';
         $labTestedDate = '';
         $dateResultDispatched = '';
         if(trim($result['sample_collection_date'])!= '' && $result['sample_collection_date'] != NULL && $result['sample_collection_date'] != '0000-00-00 00:00:00'){
            $sampleCollectionDate = $result['sample_collection_date'];
         }
         if(trim($result['date_sample_received_at_testing_lab'])!= '' && $result['date_sample_received_at_testing_lab'] != NULL && $result['date_sample_received_at_testing_lab'] != '0000-00-00 00:00:00'){
            $dateOfSampleReceivedAtTestingLab = $result['date_sample_received_at_testing_lab'];
            $noOfSampleReceivedAtLab[] = $result['vl_sample_id'];
         }
         if(trim($result['lab_tested_date'])!= '' && $result['lab_tested_date'] != NULL && $result['lab_tested_date'] != '0000-00-00 00:00:00'){
            $labTestedDate = $result['lab_tested_date'];
            $noOfSampleTested[] = $result['vl_sample_id'];
         }else{
            $noOfSampleNotTested[] = $result['vl_sample_id'];
         }
         if(trim($result['date_results_dispatched'])!= '' && $result['date_results_dispatched'] != NULL && $result['date_results_dispatched'] != '0000-00-00 00:00:00'){
            $dateResultDispatched = $result['date_results_dispatched'];
         }
         if(trim($dateOfSampleReceivedAtTestingLab)!= '' && trim($labTestedDate)!= ''){
            $lab_tested_date = strtotime($labTestedDate);
            $date_of_sample_received_at_testing_lab = strtotime($dateOfSampleReceivedAtTestingLab);
            $daydiff = $lab_tested_date - $date_of_sample_received_at_testing_lab;
            $tat = (int)floor($daydiff / (60 * 60 * 24));
            $resultTat[] = $tat;
         }
         if(trim($sampleCollectionDate)!= '' && trim($dateResultDispatched)!= ''){
            $date_result_dispatched = strtotime($dateResultDispatched);
            $sample_collection_date = strtotime($sampleCollectionDate);
            $daydiff = $date_result_dispatched - $sample_collection_date;
            $tatD = (int)floor($daydiff / (60 * 60 * 24));
            $resultDTat[] = $tatD;
         }
       }
       $row = array();
       $row[] = $r;
       $row[] = ucwords($vlLab['state']);
       $row[] = ucwords($vlLab['district']);
       $row[] = ucwords($vlLab['facility_name']);
       $row[] = '';
       $row[] = count($noOfSampleReceivedAtLab);
       $row[] = count($noOfSampleTested);
       $row[] = count($noOfSampleNotTested);
       $row[] = '';
       $row[] = (count($resultTat) >0)?round(array_sum($resultTat)/count($resultTat)):0;
       $row[] = (count($resultDTat) >0)?round(array_sum($resultDTat)/count($resultDTat)).' - '.count($resultDTat):0;
       $row[] = '';
       $row[] = '';
       $row[] = '';
       $row[] = '';
       $output[] = $row;
     $r++;
    }
   
    $start = (count($output));
    foreach ($output as $rowNo => $rowData) {
        $colNo = 0;
        foreach ($rowData as $field => $value) {
          $rRowCount = $rowNo + 3;
          $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
          $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
          $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
          $sheet->getDefaultRowDimension()->setRowHeight(15);
          $sheet->getCellByColumnAndRow($colNo, $rowNo + 3)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
          $colNo++;
        }
    }
    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $filename = 'vl-weekly-report-' . date('d-M-Y-H-i-s') . '.xls';
    $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
    echo $filename;
}else{
    echo '';
}
?>