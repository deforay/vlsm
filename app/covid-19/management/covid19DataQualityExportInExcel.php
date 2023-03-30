<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
  


 
$general=new \Vlsm\Models\General();
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

//system config
$systemConfigQuery ="SELECT * from system_config"; 
$systemConfigResult=$db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
     $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}

if(isset($_SESSION['vlIncompleteForm']) && trim($_SESSION['vlIncompleteForm'])!=""){
     // error_log($_SESSION['vlIncompleteForm']);
     $rResult = $db->rawQuery($_SESSION['vlIncompleteForm']);

     $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
     $output = array();
     $sheet = $excel->getActiveSheet();

     $headings = array('Sample Code','Remote Sample Code',"Sample Collection Date","Batch Code", "Patient Id.", "Patient Name","Facility Name","Province/State","District/County","Sample Type","Result","Status");
     if ($_SESSION['instanceType'] == 'standalone') {
		if (($key = array_search("Remote Sample Code", $headings)) !== false) {
			unset($headings[$key]);
		}
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

     $sheet->mergeCells('A1:AE1');
     $nameValue = '';
     foreach($_POST as $key=>$value){
          if(trim($value)!='' && trim($value)!='-- Select --'){
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
     $sheet->getStyle('A3:M3')->applyFromArray($styleArray);

     foreach ($rResult as $aRow) {
          $row = array();
          //sample collecion date
          $sampleCollectionDate = '';
          if($aRow['sample_collection_date']!= null && trim($aRow['sample_collection_date'])!='' && $aRow['sample_collection_date']!='0000-00-00 00:00:00'){
               $expStr = explode(" ",$aRow['sample_collection_date']);
               $sampleCollectionDate =  date("d-m-Y", strtotime($expStr[0]));
          }

          if($aRow['remote_sample']=='yes'){
               $decrypt = 'remote_sample_code';
          
          }else{
               $decrypt = 'sample_code';
          }

          $patientFname = ($general->crypto('decrypt',$aRow['patient_name'],$aRow[$decrypt]));

          $row[] = $aRow['sample_code'];
          if($sarr['sc_user_type']!='standalone'){
            $row[] = $aRow['remote_sample_code'];
             }
          $row[] = $sampleCollectionDate;
          $row[] = $aRow['batch_code'];
          $row[] = $aRow['patient_id'];
          $row[] = ($patientFname);
          $row[] = ($aRow['facility_name']);
          $row[] = ($aRow['facility_state']);
          $row[] = ($aRow['facility_district']);
          $row[] = ($aRow['sample_name']);
          $row[] = $aRow['result'];
          $row[] = ($aRow['status_name']);
          $output[] = $row;
     }

     $start = (count($output))+2;
     foreach ($output as $rowNo => $rowData) {
          $colNo = 1;
          $rRowCount = $rowNo + 4;
          foreach ($rowData as $field => $value) {
               $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
                html_entity_decode($value));
               $colNo++;
          }
     }
     $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xlsx');
     $filename = 'VLSM-Data-Quality-report' . date('d-M-Y-H-i-s') . '.xlsx';
     $writer->save(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
     echo base64_encode(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);

}
