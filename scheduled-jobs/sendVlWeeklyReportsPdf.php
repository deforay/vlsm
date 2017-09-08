<?php
include('../includes/General.php');
require '../includes/mail/PHPMailerAutoload.php';
$general=new Deforay_Commons_General();
$reportFilename = '';
$postdata = $_POST;
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-7 days'));
if(!isset($postdata['reportedDate'])){
   $_POST['reportedDate'] = $general->humanDateFormat($start_date).' to '.$general->humanDateFormat($end_date);
}
include('../program-management/generateVlWeeklyReportPdf.php');
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
$mail->Username = 'zfmailexample@gmail.com';
//Password to use for SMTP authentication
$mail->Password = 'mko)(*&^12345';
//Set who the message is to be sent from
$mail->setFrom('zfmailexample@gmail.com');
$subject="VLSM - Weekly Report - ".$_POST['reportedDate'];
$mail->Subject = $subject;
//Set to emailid(s)
$configQuery ="SELECT value from global_config where name='manager_email'";
$configResult=$db->query($configQuery);
if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= ''){
   $xplodAddress = explode(",",$configResult[0]['value']);
   for($to=0;$to<count($xplodAddress);$to++){
      $mail->addAddress($xplodAddress[$to]);
   }
   $pathFront=realpath('../uploads/');
   $file_to_attach = $pathFront. DIRECTORY_SEPARATOR. $reportFilename;
   $mail->AddAttachment($file_to_attach);
   $message ='Hi Manager,<br>Please find the attached viral load weekly report '.$_POST['reportedDate'];
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
      error_log('weekly reports mail sent--'.$_POST['reportedDate']);
   }else{
      error_log('weekly reports mail send error--');
   }
}else{
     error_log('weekly reports mail send error--to email id is missing--');
}