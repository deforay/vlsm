<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include ('../includes/PHPExcel.php');
$general=new Deforay_Commons_General();
$formConfigQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($formConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
if(isset($_SESSION['vlMonitoringResultQuery']) && trim($_SESSION['vlMonitoringResultQuery'])!=""){
 $rResult = $db->rawQuery($_SESSION['vlMonitoringResultQuery']);
 
 //get current quarter total samples tested
 $sQuery="SELECT vl.facility_id,

         SUM(CASE
               WHEN (result < 1000) THEN 1 ELSE 0 END) AS lt1000,
         
         SUM(CASE
               WHEN (result >= 1000) THEN 1 ELSE 0 END) AS gt1000,
         
         SUM(CASE
               WHEN (result < 1000 AND (patient_gender='male' OR patient_gender='MALE')) THEN 1 ELSE 0 END) AS ltMale1000,
         
         SUM(CASE
               WHEN (result >= 1000 AND (patient_gender='male' OR patient_gender='MALE')) THEN 1 ELSE 0 END) AS gtMale1000,
         
         SUM(CASE
               WHEN (result < 1000 AND (patient_gender='female' OR patient_gender='FEMALE')) THEN 1 ELSE 0 END) AS ltFemale1000,
         
         SUM(CASE
               WHEN (result >= 1000 AND (patient_gender='female' OR patient_gender='FEMALE')) THEN 1 ELSE 0 END) AS gtFemale1000,
         
         SUM(CASE
               WHEN (result < 1000 AND (patient_gender='not_specified')) THEN 1 ELSE 0 END) AS ltNotSpecified1000,
         
         SUM(CASE
               WHEN (result >= 1000 AND (patient_gender='not_specified')) THEN 1 ELSE 0 END) AS gtNotSpecified1000,
         
         SUM(CASE
               WHEN (result < 1000 AND (patient_gender='male' OR patient_gender='MALE') AND (patient_gender='female' OR patient_gender='FEMALE') AND (patient_gender='not_specified')) THEN 1 ELSE 0 END) AS ltTotalGender1000,
         
         SUM(CASE
               WHEN (result >= 1000 AND (patient_gender='male' OR patient_gender='MALE') AND (patient_gender='female' OR patient_gender='FEMALE') AND (patient_gender='not_specified')) THEN 1 ELSE 0 END) AS gtTotalGender1000,
         
		SUM(CASE 
             WHEN (patient_age_in_years ='' AND patient_age_in_months<=12 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeOne1000,
		SUM(CASE 
             WHEN (patient_age_in_years ='' AND patient_age_in_months<=12 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeOne1000,
		
        SUM(CASE 
             WHEN (patient_age_in_years >= 1 AND patient_age_in_years<=9 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeOnetoNine1000,
        SUM(CASE 
             WHEN (patient_age_in_years >= 1 AND patient_age_in_years<=9 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeOnetoNine1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years >= 10 AND patient_age_in_years<=14 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeTentoFourteen1000,
        SUM(CASE 
             WHEN (patient_age_in_years >= 10 AND patient_age_in_years<=14 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeTentoFourteen1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years<=15 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeTotalFifteen1000,
        SUM(CASE 
             WHEN (patient_age_in_years<=15 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeTotalFifteen1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years >= 15 AND patient_age_in_years<=19 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeFifteentoNineteen1000,
        SUM(CASE 
             WHEN (patient_age_in_years >= 15 AND patient_age_in_years<=19 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeFifteentoNineteen1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years >= 20 AND patient_age_in_years<=24 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeTwentytoTwentyFour1000,
        SUM(CASE 
             WHEN (patient_age_in_years >= 20 AND patient_age_in_years<=24 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeTwentytoTwentyFour1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years >= 15 AND patient_age_in_years<=24 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeFifteentoTwentyFour1000,
        SUM(CASE 
             WHEN (patient_age_in_years >= 15 AND patient_age_in_years<=24 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeFifteentoTwentyFour1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years >= 25 AND result < 1000) THEN 1 ELSE 0 END) AS ltAgetwentyFive1000,
        SUM(CASE 
             WHEN (patient_age_in_years >= 25 AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgetwentyFive1000,
        
        SUM(CASE 
             WHEN (patient_age_in_years !='' AND result < 1000) THEN 1 ELSE 0 END) AS ltAgeTotal1000,
        SUM(CASE 
             WHEN (patient_age_in_years !='' AND result >= 1000) THEN 1 ELSE 0 END) AS gtAgeTotal1000,
        
        SUM(CASE 
             WHEN (is_patient_pregnant = 'yes' AND result < 1000) THEN 1 ELSE 0 END) AS ltPatientPregnant1000,
        SUM(CASE 
             WHEN (patient_age_in_years = 'yes' AND result >= 1000) THEN 1 ELSE 0 END) AS gtPatientPregnant1000,
        
        SUM(CASE 
             WHEN (is_patient_breastfeeding = 'yes' AND result < 1000) THEN 1 ELSE 0 END) AS ltPatientBreastFeeding1000,
        SUM(CASE 
             WHEN (is_patient_breastfeeding = 'yes' AND result >= 1000) THEN 1 ELSE 0 END) AS gtPatientBreastFeeding1000
		 FROM vl_request_form as vl 
       WHERE vl.vlsm_country_id = '".$arr['vl_form']."'";
       $start_date = '';
       $end_date = '';
       if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
          $s_c_date = explode(" to ", $_POST['sampleCollectionDate']);
          //print_r($s_c_date);die;
          if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
            $start_date = trim($s_c_date[0])."-01";
          }
          if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
            $end_date = trim($s_c_date[1])."-31";
          }
       }
       $sWhere ='';
       if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
         if (trim($start_date) == trim($end_date)) {
             $sWhere = 'DATE(vl.sample_collection_date) = "'.$start_date.'"';
         }
        }
        $sQuery = $sQuery.' '.$sWhere. ' AND vl.result!=""';
    $sResult = $db->rawQuery($sQuery);
    //echo "<pre>";print_r($sResult);die;
 
 $excel = new PHPExcel();
 $output = array();
 $sheet = $excel->getActiveSheet();
  
 $colNo = 0;
 
 $headingStyle = array(
     'font' => array(
         'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
     )
 );
 $backgroundStyle = array(
     'font' => array(
         'bold' => true,
         'size' => '13',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     ),
      'fill' => array(
         'type' => PHPExcel_Style_Fill::FILL_SOLID,
         'color' => array('rgb' => 'A9A9A9')
      )
 );
 $questionStyle = array(
     'font' => array(
         //'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
     ),
      'fill' => array(
         'type' => PHPExcel_Style_Fill::FILL_SOLID,
         'color' => array('rgb' => 'A9A9A9')
      )
 );
 $sexquestionStyle = array(
     'font' => array(
         //'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
     ),
      'fill' => array(
         'type' => PHPExcel_Style_Fill::FILL_SOLID,
         'color' => array('rgb' => 'A9A9A9')
      )
 );
 $styleArray = array(
     'font' => array(
         //'bold' => true,
         'size' => '11',
     ),
     'alignment' => array(
         'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
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
 $atomcolumns = '';
 $atomcolumns .= "Country:______________________________&nbsp;&nbsp;&nbsp;";
 $atomcolumns .= "Region/Province:________________&nbsp;&nbsp;&nbsp;";
 $atomcolumns .= "City:________________\n\n";
 $atomcolumns .= "Laboratory Name:__________________________________\n\n";
 $atomcolumns .= "Reporting POC Name:________________";
 $atomcolumns .= "Title:________________";
 $atomcolumns .= "Email:________________\n\n";
 $atomcolumns .= "Date:________________";
 $atomcolumns .= "Reporting Quarter:________________";
 $sheet->getStyle('A1')->applyFromArray($headingStyle);
 $sheet->getStyle('A1')->applyFromArray($backgroundStyle);
 $sheet->getStyle('A3')->applyFromArray($styleArray);
 $sheet->mergeCells('A1:M2');
 $sheet->setCellValue('A1', html_entity_decode('Viral Load Quarterly Monitoring Tool: ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('A3:M10');
 $sheet->setCellValue('A3', html_entity_decode($atomcolumns , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('A11:A12');
 $sheet->mergeCells('B11:F12');
 $sheet->setCellValue('B11', html_entity_decode('Question' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G11:I12');
 $sheet->setCellValue('G11', html_entity_decode('Value' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J11:M12');
 $sheet->setCellValue('J11', html_entity_decode('Comments' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->getStyle('B11')->applyFromArray($backgroundStyle);
 $sheet->getStyle('G11')->applyFromArray($backgroundStyle);
 $sheet->getStyle('J11')->applyFromArray($backgroundStyle);
 //question one start
 $sheet->mergeCells('A13:A14');
 $sheet->setCellValue('A13', html_entity_decode('Q1' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B13:F14');
 $sheet->setCellValue('B13', html_entity_decode('Number Of Viral Load tests reported by the laboratory during the current quarter' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G13:I14');
 $sheet->setCellValue('G13', html_entity_decode(count($rResult) , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J13:M14');
 $sheet->getStyle('A13')->applyFromArray($questionStyle);
 $sheet->getStyle('B13')->applyFromArray($questionStyle);
 $sheet->mergeCells('A15:A16');
 $sheet->setCellValue('A15', html_entity_decode('Q1.1' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B15:F17');
 $sheet->setCellValue('B15', html_entity_decode('Of the number of Viral Load test results reported by lab,how many were: ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G15:G16');
 $sheet->setCellValue('G15', html_entity_decode('Suppressed < 1000 copies/mL ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('H15:I16');
 $sheet->mergeCells('H17:I17');
 $sheet->setCellValue('G17', html_entity_decode($sResult[0]['lt1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H17', html_entity_decode($sResult[0]['gt1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H15', html_entity_decode('Suppressed Failure >= 1000 copies/mL ' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J15:M17');
 $sheet->getStyle('A15')->applyFromArray($questionStyle);
 $sheet->getStyle('B15')->applyFromArray($questionStyle);
 $sheet->getStyle('G15')->applyFromArray($questionStyle);
 $sheet->getStyle('H15')->applyFromArray($questionStyle);
 $sheet->mergeCells('A18:A18');
 $sheet->setCellValue('A18', html_entity_decode('Q1.2' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B18:M18');
 $sheet->setCellValue('B18', html_entity_decode('Sex' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 
 $sheet->mergeCells('B19:F19');
 $sheet->setCellValue('B19', html_entity_decode('Male' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G19:G19');
 $sheet->setCellValue('G19', html_entity_decode($sResult[0]['ltMale1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H19', html_entity_decode($sResult[0]['gtMale1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('H19:I19');
 $sheet->mergeCells('J19:M19');
 $sheet->mergeCells('B20:F20');
 $sheet->setCellValue('B20', html_entity_decode('Female' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G20:G20');
 $sheet->mergeCells('H20:I20');
 $sheet->setCellValue('G20', html_entity_decode($sResult[0]['ltFemale1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H20', html_entity_decode($sResult[0]['gtFemale1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J20:M20');
 $sheet->mergeCells('B21:F21');
 $sheet->setCellValue('B21', html_entity_decode('Not Specified' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G21:G21');
 $sheet->mergeCells('H21:I21');
 $sheet->setCellValue('G21', html_entity_decode($sResult[0]['ltNotSpecified1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H21', html_entity_decode($sResult[0]['gtNotSpecified1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J21:M21');
 $sheet->mergeCells('B22:F22');
 $sheet->setCellValue('B22', html_entity_decode('Total' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G22:G22');
 $sheet->mergeCells('H22:I22');
 $sheet->setCellValue('G22', html_entity_decode($sResult[0]['ltTotalGender1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H22', html_entity_decode($sResult[0]['gtTotalGender1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J22:M22');
 
 $sheet->mergeCells('A23:A23');
 $sheet->setCellValue('A23', html_entity_decode('Q1.3' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B23:M23');
 $sheet->setCellValue('B23', html_entity_decode('Age' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 
 $sheet->mergeCells('B24:F24');
 $sheet->setCellValue('B24', html_entity_decode('<1' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G24:G24');
 $sheet->mergeCells('H24:I24');
 $sheet->setCellValue('G24', html_entity_decode($sResult[0]['ltAgeOne1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H24', html_entity_decode($sResult[0]['gtAgeOne1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J24:M24');
 $sheet->mergeCells('B25:F25');
 $sheet->setCellValue('B25', html_entity_decode('1-9' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G25:G25');
 $sheet->mergeCells('H25:I25');
 $sheet->setCellValue('G25', html_entity_decode($sResult[0]['ltAgeOnetoNine1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H25', html_entity_decode($sResult[0]['gtAgeOnetoNine1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J25:M25');
 $sheet->mergeCells('B26:F26');
 $sheet->setCellValue('B26', html_entity_decode('10-14' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G26:G26');
 $sheet->mergeCells('H26:I26');
 $sheet->setCellValue('G26', html_entity_decode($sResult[0]['ltAgeTentoFourteen1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H26', html_entity_decode($sResult[0]['gtAgeTentoFourteen1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J26:M26');
 $sheet->mergeCells('B27:F27');
 $sheet->setCellValue('B27', html_entity_decode('<15(Subtotal)' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G27:G27');
 $sheet->mergeCells('H27:I27');
 $sheet->setCellValue('G27', html_entity_decode($sResult[0]['ltAgeTotalFifteen1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H27', html_entity_decode($sResult[0]['gtAgeTotalFifteen1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J27:M27');
 $sheet->mergeCells('B28:F28');
 $sheet->setCellValue('B28', html_entity_decode('15-19' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G28:G28');
 $sheet->mergeCells('H28:I28');
 $sheet->setCellValue('G28', html_entity_decode($sResult[0]['ltAgeFifteentoNineteen1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H28', html_entity_decode($sResult[0]['gtAgeFifteentoNineteen1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J28:M28');
 $sheet->mergeCells('B29:F29');
 $sheet->setCellValue('B29', html_entity_decode('20-24' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G29:G29');
 $sheet->mergeCells('H29:I29');
 $sheet->setCellValue('G29', html_entity_decode($sResult[0]['ltAgeTwentytoTwentyFour1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H29', html_entity_decode($sResult[0]['gtAgeTwentytoTwentyFour1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J29:M29');
 $sheet->mergeCells('B30:F30');
 $sheet->setCellValue('B30', html_entity_decode('15-24' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G30:G30');
 $sheet->mergeCells('H30:I30');
 $sheet->setCellValue('G30', html_entity_decode($sResult[0]['ltAgeFifteentoTwentyFour1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H30', html_entity_decode($sResult[0]['gtAgeFifteentoTwentyFour1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J30:M30');
 $sheet->mergeCells('B31:F31');
 $sheet->setCellValue('B31', html_entity_decode('25+' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G31:G31');
 $sheet->mergeCells('H31:I31');
 $sheet->setCellValue('G31', html_entity_decode($sResult[0]['ltAgetwentyFive1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H31', html_entity_decode($sResult[0]['gtAgetwentyFive1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J31:M31');
 $sheet->mergeCells('B32:F32');
 $sheet->setCellValue('B32', html_entity_decode('Total' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G32:G32');
 $sheet->mergeCells('H32:I32');
 $sheet->setCellValue('G32', html_entity_decode($sResult[0]['ltAgeTotal1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H32', html_entity_decode($sResult[0]['gtAgeTotal1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J32:M32');
 $sheet->mergeCells('A33:A33');
 $sheet->setCellValue('A33', html_entity_decode('Q1.4' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B33:F33');
 $sheet->setCellValue('B33', html_entity_decode('Pregnant Women' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G33:G33');
 $sheet->mergeCells('H33:I33');
 $sheet->setCellValue('G33', html_entity_decode($sResult[0]['ltPatientPregnant1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H33', html_entity_decode($sResult[0]['gtPatientPregnant1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J33:M33');
 $sheet->mergeCells('A34:A34');
 $sheet->setCellValue('A34', html_entity_decode('Q1.5' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B34:F34');
 $sheet->setCellValue('B34', html_entity_decode('Women that are breastfeeding' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G34:G34');
 $sheet->mergeCells('H34:I34');
 $sheet->setCellValue('G34', html_entity_decode($sResult[0]['ltPatientBreastFeeding1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->setCellValue('H34', html_entity_decode($sResult[0]['gtPatientBreastFeeding1000'] , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('J34:M34');
 $sheet->getStyle('A13:F34')->applyFromArray($questionStyle);
 $sheet->getStyle('B18')->applyFromArray($sexquestionStyle);
 $sheet->getStyle('B23')->applyFromArray($sexquestionStyle);
 $sheet->getStyle('B34')->applyFromArray($sexquestionStyle);
 $sheet->getStyle('B33')->applyFromArray($sexquestionStyle);
 
 $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
 $filename = 'vl-result-' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
}
?>