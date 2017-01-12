<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
require '../includes/mail/PHPMailerAutoload.php';
$tableName="vl_request_form";
//get other config details
$geQuery="SELECT * FROM other_config WHERE type = 'request'";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
   $mailconf[$row['name']] = $row['value'];
}
if(isset($_POST['toEmail']) && trim($_POST['toEmail'])!=''){
     if(isset($mailconf['rq_field']) && trim($mailconf['rq_field'])!= ''){
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
          $mail->Port = 25;
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
          if(isset($_POST['reportEmail']) && trim($_POST['reportEmail'])!= ''){
              $xplodCc = explode(",",$_POST['reportEmail']);
              for($cc=0;$cc<count($xplodCc);$cc++){
                 $mail->AddCC($xplodCc[$cc]);
              }
          }
          $pathFront=realpath('../temporary');
          $file_to_attach = $pathFront. DIRECTORY_SEPARATOR. $_POST['fileName'];
          $mail->AddAttachment($file_to_attach);
          $message='';
          if(isset($_POST['message']) && trim($_POST['message'])!=""){
             $message =ucfirst($_POST['message']);
          }
          $mail->msgHTML($message);
          if ($mail->send()){
                //Update request mail sent flag
                $sampleArray = explode(',',$_POST['sample']);
                for($s=0;$s<count($sampleArray);$s++){
                    $sampleQuery="SELECT vl_sample_id FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$sampleArray[$s]."'";
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
         $_SESSION['alertMsg']='Unable to send mail. Please try later.';
        header('location:vlRequestMail.php');
      }
}else{
    $_SESSION['alertMsg']='Unable to send mail. Please try later.';
     header('location:vlRequestMail.php');
}