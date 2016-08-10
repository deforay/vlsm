<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
require './includes/mail/PHPMailerAutoload.php';
//print_r($_POST);die;
//insert record
$reportData=array('to_mail'=>$_POST['toMail'],
                  'encrypt'=>$_POST['encrypt'],
                  'password'=>$_POST['password'],
                    'comment'=>$_POST['comment'],
                    'batch_id'=>$_POST['batchId'],
                    );
$reportId = $db->insert('report_to_mail', $reportData);
//get email id
$geQuery="SELECT * FROM global_config where name='email'";
$geResult = $db->rawQuery($geQuery);
$gpQuery="SELECT * FROM global_config where name='password'";
$gpResult = $db->rawQuery($gpQuery);
if($reportId){
     //Create a new PHPMailer instance
                $mail = new PHPMailer();
                //Tell PHPMailer to use SMTP
                $mail->isSMTP();
                //Enable SMTP debugging
                // 0 = off (for production use)
                // 1 = client messages
                // 2 = client and server messages
                $mail->SMTPDebug = 0;
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
                $mail->Username = $geResult[0]['value'];
                //Password to use for SMTP authentication
                $mail->Password = $gpResult[0]['value'];
                //Set who the message is to be sent from
                $mail->setFrom($geResult[0]['value']);
                
                //Admin Mail
                  $mail->Subject = 'Report Data';
                  $message='Hi Sir,<br/> PFA';
                  
                    $mail->addAddress($_POST['toMail']);
                    $file_to_attach = 'temporary/'.$_POST['fileName'];

                    $mail->AddAttachment($file_to_attach);
                    $mail->msgHTML($message);
                    if (!$mail->send())
                    {
			$_SESSION['alertMsg']='Unable to send message. Please try later.';
                        echo "Mailer Error: " . $mail->ErrorInfo;die;
                    }
                   else{
                        $_SESSION['alertMsg']='Report sent successfully';
                        header('location:vlResultMail.php');
                   }
}
?>