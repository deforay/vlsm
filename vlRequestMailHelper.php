<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
include ('./includes/PHPExcel.php');
include('General.php');
require './includes/mail/PHPMailerAutoload.php';
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
//get other config details
$geQuery="SELECT * FROM other_config WHERE type = 'request'";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
   $mailconf[$row['name']] = $row['value'];
}
if(isset($_POST['toEmail']) && trim($_POST['toEmail'])!="" && count($_POST['sample'])>0){
     $filedGroup = array();
     if(isset($mailconf['rq_field']) && trim($mailconf['rq_field'])!= ''){
          //Excel code start
          $excel = new PHPExcel();
          $sheet = $excel->getActiveSheet();
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
         $filedGroup = explode(",",$mailconf['rq_field']);
         $headings = $filedGroup;
         //Set heading row
          $sheet->getCellByColumnAndRow(0, 1)->setValueExplicit(html_entity_decode('Sample'), PHPExcel_Cell_DataType::TYPE_STRING);
          $cellName = $sheet->getCellByColumnAndRow(0,1)->getColumn();
          $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
          $colNo = 1;
         foreach ($headings as $field => $value) {
          $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
          $cellName = $sheet->getCellByColumnAndRow($colNo,1)->getColumn();
          $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
          $colNo++;
         }
         //Set values
         $output = array();
         for($s=0;$s<count($_POST['sample']);$s++){
            $row = array();
            $sampleQuery="SELECT sample_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
            $sampleResult = $db->rawQuery($sampleQuery);
            $row[] = $sampleResult[0]['sample_code'];
            for($f=0;$f<count($filedGroup);$f++){
               if($filedGroup[$f] == "Form Serial No"){
                    $field = 'serial_no';
               }elseif($filedGroup[$f] == "Urgency"){
                    $field = 'urgency';
               }elseif($filedGroup[$f] == "Province"){
                    $field = 'state';
               }elseif($filedGroup[$f] == "District Name"){
                    $field = 'district';
               }elseif($filedGroup[$f] == "Clinic Name"){
                    $field = 'facility_name';
               }elseif($filedGroup[$f] == "Clinician Name"){
                    $field = 'lab_contact_person';
               }elseif($filedGroup[$f] == "Sample Collection Date"){
                    $field = 'sample_collection_date';
               }elseif($filedGroup[$f] == "Sample Received Date"){
                    $field = 'date_sample_received_at_testing_lab';
               }elseif($filedGroup[$f] == "Collected by (Initials)"){
                    $field = 'collected_by';
               }elseif($filedGroup[$f] == "Patient First Name"){
                    $field = 'patient_name';
               }elseif($filedGroup[$f] == "Surname"){
                    $field = 'surname';
               }elseif($filedGroup[$f] == "Gender"){
                    $field = 'gender';
               }elseif($filedGroup[$f] == "Date Of Birth"){
                    $field = 'patient_dob';
               }elseif($filedGroup[$f] == "Age in years"){
                    $field = 'age_in_yrs';
               }elseif($filedGroup[$f] == "Age in months"){
                    $field = 'age_in_mnts';
               }elseif($filedGroup[$f] == "Is Patient Pregnant?"){
                    $field = 'is_patient_pregnant';
               }elseif($filedGroup[$f] == "Is Patient Breastfeeding?"){
                    $field = 'is_patient_breastfeeding';
               }elseif($filedGroup[$f] == "Patient OI/ART Number"){
                    $field = 'art_no';
               }elseif($filedGroup[$f] == "Date Of ART Initiation"){
                    $field = 'date_of_initiation_of_current_regimen';
               }elseif($filedGroup[$f] == "ART Regimen"){
                    $field = 'current_regimen';
               }elseif($filedGroup[$f] == "Patient consent to SMS Notification?"){
                    $field = 'patient_receive_sms';
               }elseif($filedGroup[$f] == "Patient Mobile Number"){
                    $field = 'patient_phone_number';
               }elseif($filedGroup[$f] == "Date Of Last Viral Load Test"){
                    $field = 'last_viral_load_date';
               }elseif($filedGroup[$f] == "Result Of Last Viral Load"){
                    $field = 'last_viral_load_result';
               }elseif($filedGroup[$f] == "Viral Load Log"){
                    $field = 'viral_load_log';
               }elseif($filedGroup[$f] == "Reason For VL Test"){
                    $field = 'vl_test_reason';
               }elseif($filedGroup[$f] == "Lab Name"){
                    $field = 'lab_name';
               }elseif($filedGroup[$f] == "VL Testing Platform"){
                    $field = 'vl_test_platform';
               }elseif($filedGroup[$f] == "Specimen type"){
                    $field = 'sample_name';
               }elseif($filedGroup[$f] == "Sample Testing Date"){
                    $field = 'lab_tested_date';
               }elseif($filedGroup[$f] == "Viral Load Result(copiesl/ml)"){
                    $field = 'absolute_value';
               }elseif($filedGroup[$f] == "Log Value"){
                    $field = 'log_value';
               }elseif($filedGroup[$f] == "If no result"){
                    $field = 'rejection';
               }elseif($filedGroup[$f] == "Rejection Reason"){
                    $field = 'rejection_reason_name';
               }elseif($filedGroup[$f] == "Reviewed By"){
                    $field = 'result_reviewed_by';
               }elseif($filedGroup[$f] == "Approved By"){
                    $field = 'result_approved_by';
               }elseif($filedGroup[$f] == "Laboratory Scientist Comments"){
                    $field = 'comments';
               }
               if($field ==  'result_reviewed_by'){
                    $fValueQuery="SELECT u.user_name as reviewedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_reviewed_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }elseif($field ==  'result_approved_by'){
                    $fValueQuery="SELECT u.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_approved_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }else{
                 $fValueQuery="SELECT $field FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }
               $fValueResult = $db->rawQuery($fValueQuery);
               $fieldValue = '';
               if(isset($fValueResult) && count($fValueResult)>0){
                    if($field == 'sample_collection_date' || $field == 'date_sample_received_at_testing_lab' || $field == 'lab_tested_date'){
                         if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00 00:00:00'){
                             $xplodDate = explode(" ",$fValueResult[0][$field]);
                             $fieldValue=$general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];  
                         }
                    }elseif($field == 'patient_dob' || $field == 'date_of_initiation_of_current_regimen' || $field == 'last_viral_load_date'){
                         if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00'){
                             $fieldValue=$general->humanDateFormat($fValueResult[0][$field]);
                         }
                    }elseif($field ==  'vl_test_platform' || $field ==  'gender'){
                      $fieldValue = ucwords(str_replace("_"," ",$fValueResult[0][$field]));
                    }elseif($field ==  'result_reviewed_by'){
                      $fieldValue = $fValueResult[0]['reviewedBy'];
                    }elseif($field ==  'result_approved_by'){
                      $fieldValue = $fValueResult[0]['approvedBy'];
                    }else{
                      $fieldValue = $fValueResult[0][$field];
                    }
               }
              $row[] = $fieldValue;
            }
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
          $filename = '';
          $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
          $filename = 'vl-request-mail' . date('d-M-Y-H-i-s') . '.xls';
          $pathFront=realpath('./temporary');
          $writer->save($pathFront. DIRECTORY_SEPARATOR . $filename);
          //Excel code end
          //Mail code start
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
          $mail->Username = $mailconf['rq_email'];
          //Password to use for SMTP authentication
          $mail->Password = $mailconf['rq_password'];
          //Set who the message is to be sent from
          $mail->setFrom($mailconf['rq_email']);
          $subject="";
          if(isset($_POST['subject']) && trim($_POST['subject'])!=""){
               $subject=$_POST['subject'];
          }
          $mail->Subject = $subject;
          //Set To EmailId(s)
          if(isset($_POST['toEmail']) && trim($_POST['toEmail'])!= ''){
              $xplodAddress = explode(",",$_POST['toEmail']);
              for($to=0;$to<count($xplodAddress);$to++){
                 $mail->addAddress($xplodAddress[$to]);
              }
          }
          //Set CC EmailId(s)
          if(isset($_POST['cc']) && trim($_POST['cc'])!= ''){
              $xplodCc = explode(",",$_POST['cc']);
              for($cc=0;$cc<count($xplodCc);$cc++){
                 $mail->AddCC($xplodCc[$cc]);
              }
          }
          //Set BCC EmailId(s)
          if(isset($_POST['bcc']) && trim($_POST['bcc'])!= ''){
              $xplodBcc = explode(",",$_POST['bcc']);
              for($bcc=0;$bcc<count($xplodBcc);$bcc++){
                 $mail->AddBCC($xplodBcc[$bcc]);
              }
          }
          $file_to_attach = $pathFront. DIRECTORY_SEPARATOR . $filename;
          $mail->AddAttachment($file_to_attach);
          $message='';
          if(isset($_POST['message']) && trim($_POST['message'])!=""){
             $message =ucfirst($_POST['message']);
          }
          $mail->msgHTML($message);
          if ($mail->send()){
                //Update request mail sent flag
                for($s=0;$s<count($_POST['sample']);$s++){
                    $sampleQuery="SELECT vl_sample_id FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
                    $sampleResult = $db->rawQuery($sampleQuery);
                    $db=$db->where('vl_sample_id',$sampleResult[0]['vl_sample_id']);
                    $db->update($tableName,array('request_mail_sent'=>'yes')); 
               }
               $_SESSION['alertMsg']='Email sent successfully';
               header('location:vlRequestMail.php');
          }else{
               $_SESSION['alertMsg']='Unable to send mail. Please try later.';
               error_log("Mailer Error: " . $mail->ErrorInfo);
               header('location:vlRequestMail.php');
          }
     }else{
          $_SESSION['alertMsg']='Unable to send mail. Please check the request fields.';  
          header('location:vlRequestMail.php');
     }
}else{
     $_SESSION['alertMsg']='Unable to send mail. Please try later.';
     header('location:vlRequestMail.php');
}
?>