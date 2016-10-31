<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
require './includes/mail/PHPMailerAutoload.php';
//get & set email details
$geQuery="SELECT * FROM other_config";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
     $mailconf[$row['name']] = $row['value'];
}
if(isset($_POST['toEmail']) && trim($_POST['toEmail'])!="" && count($_POST['sample'])>0){
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
    $mail->Username = $mailconf['email'];
    //Password to use for SMTP authentication
    $mail->Password = $mailconf['password'];
    //Set who the message is to be sent from
    $mail->setFrom($mailconf['email']);
          
    $subject="";
    if(isset($_POST['subject']) && trim($_POST['subject'])!=""){
         $subject=$_POST['subject'];
    }
    
    $message='';
    $message.=ucfirst($_POST['message']).'<br><br>';
    if(isset($_POST['type']) && trim($_POST['type'])=="request"){
        $filedGroup = array();
        $requestQuery="SELECT * FROM other_config WHERE name='request_email_field'";
        $requestResult = $db->rawQuery($requestQuery);
        if(isset($requestResult) && trim($requestResult[0]['value'])!= ''){
            $filedGroup = explode(",",$requestResult[0]['value']);
        }
         $message.='<table style="width;100%;border:1px solid #333;" cellspacing="0" cellpadding="2">';
           $message.='<tr>';
            $message.='<td style="border:1px solid #333;">Sample</td>';
            for($f=0;$f<count($filedGroup);$f++){
              $message.='<td style="border:1px solid #333;">'.$filedGroup[$f].'</td>';
            }
           $message.='</tr>';
           for($s=0;$s<count($_POST['sample']);$s++){
            $sampleQuery="SELECT vl.sample_code,vl.vl_sample_id,vl.facility_id,f.facility_name,f.facility_code FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where (batch_id is NULL OR batch_id='') AND vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
            $sampleResult = $db->rawQuery($sampleQuery);
            $message.='<tr>';
            $message.='<td style="border:1px solid #333;">'.ucwords($sampleResult[0]['sample_code'])." - ".ucwords($sampleResult[0]['facility_name']).'</td>';
            for($f=0;$f<count($filedGroup);$f++){
              $message.='<td style="border:1px solid #333;"></td>';
            }
            $message.='</tr>';
           }
        $message.='</table>';
    }elseif(isset($_POST['type']) && trim($_POST['type'])=="result"){
         $filedGroup = array();
        $resultQuery="SELECT * FROM other_config WHERE name='result_email_field'";
        $resultResult = $db->rawQuery($resultQuery);
        if(isset($resultResult) && trim($resultResult[0]['value'])!= ''){
            $filedGroup = explode(",",$resultResult[0]['value']);
        }
         $message.='<table style="width;100%;border:1px solid #333;" cellspacing="0" cellpadding="2">';
           $message.='<tr>';
            $message.='<td style="border:1px solid #333;">Sample</td>';
            for($f=0;$f<count($filedGroup);$f++){
              $message.='<td style="border:1px solid #333;">'.$filedGroup[$f].'</td>';
            }
           $message.='</tr>';
           for($s=0;$s<count($_POST['sample']);$s++){
            $sampleQuery="SELECT vl.sample_code,vl.vl_sample_id,vl.facility_id,f.facility_name,f.facility_code FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where (batch_id is NULL OR batch_id='') AND vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
            $sampleResult = $db->rawQuery($sampleQuery);
            $message.='<tr>';
            $message.='<td style="border:1px solid #333;">'.ucwords($sampleResult[0]['sample_code'])." - ".ucwords($sampleResult[0]['facility_name']).'</td>';
            for($f=0;$f<count($filedGroup);$f++){
              $message.='<td style="border:1px solid #333;"></td>';
            }
            $message.='</tr>';
           }
        $message.='</table>';
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
    $mail->msgHTML($message);
    if (!$mail->send()){
         $_SESSION['alertMsg']='Unable to send mail. Please try later.';
         error_log("Mailer Error: " . $mail->ErrorInfo);
         header('location:vlMail.php');
    }else{
         $_SESSION['alertMsg']='Email sent successfully';
         header('location:vlMail.php');
    }
}
?>