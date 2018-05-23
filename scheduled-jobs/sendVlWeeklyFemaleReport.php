<?php
session_start();
ob_start();

require(__DIR__ . "/../includes/MysqliDb.php");
require(__DIR__ . "/../includes/mail/PHPMailerAutoload.php");
require(__DIR__ . "/../General.php");
require(__DIR__ . "/../includes/PHPExcel.php");



$general=new General();

$query ="SELECT * from s_vlsm_instance";
$qResult=$db->query($query);
$facilityName = ucwords($qResult[0]['instance_facility_name']);

$configQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($configQuery);
$country = $configResult[0]['value'];

$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-7 days'));

$dateRange=$general->humanDateFormat($start_date)." to ".$general->humanDateFormat($end_date);

$sQuery="SELECT
	vl.facility_id,f.facility_code,f.facility_state,f.facility_district,f.facility_name,
	SUM(CASE
		WHEN (patient_gender = 'female') THEN 1
			ELSE 0
		END) AS totalFemale,
	SUM(CASE
		WHEN ((is_patient_pregnant ='yes') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
			ELSE 0
		END) AS preglt1000,
	SUM(CASE
		WHEN ((is_patient_pregnant ='yes')  AND result > 1000) THEN 1
			ELSE 0
		END) AS preggt1000,
	SUM(CASE
		WHEN ((is_patient_breastfeeding ='yes') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
			ELSE 0
		END) AS bflt1000,
	SUM(CASE
		WHEN ((is_patient_breastfeeding ='yes') AND result > 1000) THEN 1
			ELSE 0
		END) AS bfgt1000,
	SUM(CASE
		WHEN (patient_age_in_years > 15 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
			ELSE 0
		END) AS gt15lt1000F,
	SUM(CASE
		WHEN (patient_age_in_years > 15 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND result > 1000) THEN 1
			ELSE 0
		END) AS gt15gt1000F,
	SUM(CASE
		WHEN (patient_age_in_years <= 15 AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
			ELSE 0
		END) AS lt15lt1000,
	SUM(CASE
		WHEN (patient_age_in_years <= 15 AND result > 1000) THEN 1
			ELSE 0
		END) AS lt15gt1000,
	SUM(CASE
		WHEN ((patient_age_in_years ='' OR patient_age_in_years IS NULL) AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
			ELSE 0
		END) AS ltUnKnownAgelt1000,
	SUM(CASE
		WHEN ((patient_age_in_years ='' OR patient_age_in_years IS NULL)  AND result > 1000) THEN 1
			ELSE 0
		END) AS ltUnKnownAgegt1000
	FROM vl_request_form as vl RIGHT JOIN facility_details as f ON f.facility_id=vl.facility_id
	where vl.patient_gender='female'  AND vl.vlsm_country_id =".$country;
	
    $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
    $sQuery = $sQuery.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
	$sQuery = $sQuery.' GROUP BY vl.facility_id';
	
	$rResult = $db->rawQuery($sQuery);
	if(count($rResult)>0){
		$excel = new PHPExcel();
		$output = array();
		$sheet = $excel->getActiveSheet();
		$headings = array("Province/State","District/County","Site Name","Total Female","Pregnant <=1000 cp/ml","Pregnant >1000 cp/ml","Breastfeeding <=1000 cp/ml","Breastfeeding >1000 cp/ml","Age > 15 <=1000 cp/ml","Age > 15 >1000 cp/ml","Age Unknown <=1000 cp/ml","Age Unknown >1000 cp/ml","Age <=15 <=1000 cp/ml","Age <=15 >1000 cp/ml");
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

		$sheet->mergeCells('A1:I1');
	
		$nameValue="Sample test date ".$dateRange." and Sample collection date ".$dateRange;
		$sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($nameValue));
	 
		foreach ($headings as $field => $value) {
		  $sheet->getCellByColumnAndRow($colNo, 3)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
		  $colNo++;
		}
		$sheet->getStyle('A3:N3')->applyFromArray($styleArray);
 
		foreach ($rResult as $aRow) {
			$row = array();
			$row[] = ucwords($aRow['facility_state']);
			$row[] = ucwords($aRow['facility_district']);
			$row[] = ucwords($aRow['facility_name']);
			$row[] = $aRow['totalFemale'];
			$row[] = $aRow['preglt1000'];
			$row[] = $aRow['preggt1000'];
			$row[] = $aRow['bflt1000'];
			$row[] = $aRow['bfgt1000'];			
			$row[] = $aRow['gt15lt1000F'];
			$row[] = $aRow['gt15gt1000F'];
			$row[] = $aRow['ltUnKnownAgelt1000'];
			$row[] = $aRow['ltUnKnownAgegt1000'];
			$row[] = $aRow['lt15lt1000'];
			$row[] = $aRow['lt15gt1000'];
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
		$filename = 'VLSM-Lab-Female-Weekly-Report-' . date('d-M-Y-H-i-s') . '.xls';
		$writer->save("../temporary". DIRECTORY_SEPARATOR . $filename);
		//echo $filename;
		//mail part start
		//Create a new PHPMailer instance
		$mail = new PHPMailer();
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 2;
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host = 'smtp.gmail.com';
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 587;
		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'tls';
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;
		$mail->SMTPKeepAlive = true; 
		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = $emailUserName;
		//Password to use for SMTP authentication
		$mail->Password = $emailPassword;
		//Set who the message is to be sent from
		$mail->setFrom($emailUserName);
		if(trim($facilityName)!=""){
			$facilityName=" - ".$facilityName;
		}
		if(trim($dateRange)!=""){
			$dateRange=" - ".$dateRange;
		}
		$subject="Viral Load LIS - Female Weekly Report ".$facilityName.$dateRange;
		$mail->Subject = $subject;
		//Set to emailid(s)
		$configQuery ="SELECT * from global_config where name='manager_email'";
		$configResult=$db->query($configQuery);
		if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= ''){
		   $xplodAddress = explode(",",$configResult[0]['value']);
		   for($to=0;$to<count($xplodAddress);$to++){
			  $mail->addAddress($xplodAddress[$to]);
		   }
		   $pathFront=realpath('../temporary');
		   $file_to_attach = $pathFront. DIRECTORY_SEPARATOR. $filename;
		   $mail->AddAttachment($file_to_attach);
		   $message ='Hi Manager,<br>Please find the attached viral load female weekly report '.$dateRange;
		   $message = nl2br($message);
		   $mail->msgHTML($message);
		   $mail->SMTPOptions = array(
			 'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			 )
		   );
		   if($mail->send()){
			  error_log('female weekly reports mail sent--'.$dateRange);
		   }else{
			  error_log('female weekly reports mail send error--');
		   }
		}else{
			 error_log('female weekly reports mail send error--to email id is missing--');
		}
	}
?>