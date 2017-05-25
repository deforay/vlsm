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
 
 $headings = array("Serial No.","Batch Code","Urgency","Province","District","Clinic Name","Clinician Name","Sample Collection Date","Sample Received Date","Collected By","Patient Name","Gender","DOB","Age In Years","Age In Months","Patient Pregnant","Patient BreastFeeding","ART Number","ART Initiation","ART Regimen","SMS Notification","Mobile Number","Date Of Last Viral Load Test","Result Of Last Viral Load","Viral Load Log","Reason For VL Test","LAB Name","LAB No.","VL Testing Platform","Specimen Type","Sample Testing Date","Last Print On","Viral Load Result","No Result","Rejection Reason","Reviewed By","Approved By","Approved On","Comments","Status");
 
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
 $sheet->mergeCells('H19:I19');
 $sheet->mergeCells('J19:M19');
 $sheet->mergeCells('B20:F20');
 $sheet->setCellValue('B20', html_entity_decode('Female' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G20:G20');
 $sheet->mergeCells('H20:I20');
 $sheet->mergeCells('J20:M20');
 $sheet->mergeCells('B21:F21');
 $sheet->setCellValue('B21', html_entity_decode('Not Specified' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G21:G21');
 $sheet->mergeCells('H21:I21');
 $sheet->mergeCells('J21:M21');
 $sheet->mergeCells('B22:F22');
 $sheet->setCellValue('B22', html_entity_decode('Total' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G22:G22');
 $sheet->mergeCells('H22:I22');
 $sheet->mergeCells('J21:M21');
 
 $sheet->mergeCells('A23:A23');
 $sheet->setCellValue('A23', html_entity_decode('Q1.3' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B23:M23');
 $sheet->setCellValue('B23', html_entity_decode('Age' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 
 $sheet->mergeCells('B24:F24');
 $sheet->setCellValue('B24', html_entity_decode('<1' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G24:G24');
 $sheet->mergeCells('H24:I24');
 $sheet->mergeCells('J24:M24');
 $sheet->mergeCells('B25:F25');
 $sheet->setCellValue('B25', html_entity_decode('1-9' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G25:G25');
 $sheet->mergeCells('H25:I25');
 $sheet->mergeCells('J25:M25');
 $sheet->mergeCells('B26:F26');
 $sheet->setCellValue('B26', html_entity_decode('10-14' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G26:G26');
 $sheet->mergeCells('H26:I26');
 $sheet->mergeCells('J26:M26');
 $sheet->mergeCells('B27:F27');
 $sheet->setCellValue('B27', html_entity_decode('<15(Subtotal)' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G27:G27');
 $sheet->mergeCells('H27:I27');
 $sheet->mergeCells('J27:M27');
 $sheet->mergeCells('B28:F28');
 $sheet->setCellValue('B28', html_entity_decode('15-19' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G28:G28');
 $sheet->mergeCells('H28:I28');
 $sheet->mergeCells('J28:M28');
 $sheet->mergeCells('B29:F29');
 $sheet->setCellValue('B29', html_entity_decode('20-24' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G29:G29');
 $sheet->mergeCells('H29:I29');
 $sheet->mergeCells('J29:M29');
 $sheet->mergeCells('B30:F30');
 $sheet->setCellValue('B30', html_entity_decode('15-24' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G30:G30');
 $sheet->mergeCells('H30:I30');
 $sheet->mergeCells('J30:M30');
 $sheet->mergeCells('B31:F31');
 $sheet->setCellValue('B31', html_entity_decode('25+' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G31:G31');
 $sheet->mergeCells('H31:I31');
 $sheet->mergeCells('J31:M31');
 $sheet->mergeCells('B32:F32');
 $sheet->setCellValue('B32', html_entity_decode('Total' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G32:G32');
 $sheet->mergeCells('H32:I32');
 $sheet->mergeCells('J32:M32');
 $sheet->mergeCells('A33:A33');
 $sheet->setCellValue('A33', html_entity_decode('Q1.4' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B33:F33');
 $sheet->setCellValue('B33', html_entity_decode('Pregnant Women' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G33:G33');
 $sheet->mergeCells('H33:I33');
 $sheet->mergeCells('J33:M33');
 $sheet->mergeCells('A34:A34');
 $sheet->setCellValue('A34', html_entity_decode('Q1.5' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('B34:F34');
 $sheet->setCellValue('B34', html_entity_decode('Women that are breastfeeding' , ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
 $sheet->mergeCells('G34:G34');
 $sheet->mergeCells('H34:I34');
 $sheet->mergeCells('J34:M34');
 $sheet->getStyle('A13:F34')->applyFromArray($questionStyle);
 $sheet->getStyle('B18')->applyFromArray($sexquestionStyle);
 $sheet->getStyle('B23')->applyFromArray($sexquestionStyle);
 
 
 $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
 $filename = 'vl-result-' . date('d-M-Y-H-i-s') . '.xls';
 $writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
 echo $filename;
}
?>