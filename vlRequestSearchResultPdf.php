<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/tcpdf/tcpdf.php');
include ('./includes/fpdi/fpdi.php');
define('UPLOAD_PATH','uploads');
$tableName1="activity_log";
$tableName2="vl_request_form";
$general=new Deforay_Commons_General();
$configQuery="SELECT value FROM global_config WHERE name = 'default_time_zone'";
$configResult=$db->query($configQuery);
if(isset($configResult) && count($configResult)> 0){
  date_default_timezone_set($configResult[0]['value']);
}else{
  date_default_timezone_set("Europe/London");
}
$printedTime = date('Y-m-d H:i:s');
$expStr=explode(" ",$printedTime);
$printDate =$general->humanDateFormat($expStr[0]);
$printDateTime = $expStr[1];
$requestResult=$db->query($_SESSION['vlRequestSearchResultQuery']);
$_SESSION['nbPages'] = sizeof($requestResult);
$_SESSION['aliasPage'] = 1;
//print_r($requestResult);die;
$pdfNew = new TCPDF();
//header and footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        //$image_file = K_PATH_IMAGES.'logo_example.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        //$this->SetFont('helvetica', 'B', 20);
        // Title
        //$this->Cell(0, 15, 'VL Request Form Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$_SESSION['aliasPage'].'/'.$_SESSION['nbPages'], 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

class Pdf_concat extends FPDI {
    var $files = array();
 
    function setFiles($files) {
        $this->files = $files;
    }
 
    function concat() {
        foreach($this->files AS $file) {
             $pagecount = $this->setSourceFile($file);
             for ($i = 1; $i <= $pagecount; $i++) {
                  $tplidx = $this->ImportPage($i);
                  $s = $this->getTemplatesize($tplidx);
                  $this->AddPage('P', array($s['w'], $s['h']));
                  $this->useTemplate($tplidx);
             }
        }
    }
}

if(sizeof($requestResult)> 0){
    $configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    
    $_SESSION['rVal'] = $general->generateRandomString(6);
    if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal'])) {
      mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']);
    }
    $pathFront = realpath('./uploads/'.$_SESSION['rVal'].'/');
    
    $pages = array();
    $page = 1;
    foreach($requestResult as $result){
        $_SESSION['aliasPage'] = $page;
        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        //$pdf->SetAuthor('Saravanan');
        $pdf->SetTitle('Vl Request Result');
        //$pdf->SetSubject('TCPDF Tutorial');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        
        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);
        //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // set some language-dependent strings (optional)
        //if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        //    require_once(dirname(__FILE__).'/lang/eng.php');
        //    $pdf->setLanguageArray($l);
        //}
        
        // ---------------------------------------------------------
        
        // set font
        $pdf->SetFont('helvetica', '', 18);
        
        $pdf->AddPage();
        if(!isset($result['facility_code']) || trim($result['facility_code']) == ''){
           $result['facility_code'] = '';
        }
        if(!isset($result['state']) || trim($result['state']) == ''){
           $result['state'] = '';
        }
        if(!isset($result['district']) || trim($result['district']) == ''){
           $result['district'] = '';
        }
        if(!isset($result['facility_name']) || trim($result['facility_name']) == ''){
           $result['facility_name'] = '';
        }
        if(!isset($result['labName']) || trim($result['labName']) == ''){
           $result['labName'] = '';
        }
        //Set Age
        $age = 'Unknown';
        if(isset($result['patient_dob']) && trim($result['patient_dob'])!='' && $result['patient_dob']!='0000-00-00'){
          $todayDate = strtotime(date('Y-m-d'));
          $dob = strtotime($result['patient_dob']);
          $difference = $todayDate - $dob;
          $seconds_per_year = 60*60*24*365;
          $age = round($difference / $seconds_per_year);
        }elseif(isset($result['age_in_yrs']) && trim($result['age_in_yrs'])!='' && trim($result['age_in_yrs']) >0){
          $age = $result['age_in_yrs'];
        }elseif(isset($result['age_in_mnts']) && trim($result['age_in_mnts'])!='' && trim($result['age_in_mnts']) >0){
          if($result['age_in_mnts'] > 1){
            $age = $result['age_in_mnts'].' months';
          }else{
            $age = $result['age_in_mnts'].' month';
          }
        }
        
        if(isset($result['sample_collection_date']) && trim($result['sample_collection_date'])!='' && $result['sample_collection_date']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['sample_collection_date']);
          $result['sample_collection_date']=$general->humanDateFormat($expStr[0]);
          $sampleCollectionTime = $expStr[1];
        }else{
          $result['sample_collection_date']='';
          $sampleCollectionTime = '';
        }
        $sampleReceivedDate='';
        $sampleReceivedTime='';
        if(isset($result['date_sample_received_at_testing_lab']) && trim($result['date_sample_received_at_testing_lab'])!='' && $result['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['date_sample_received_at_testing_lab']);
          $sampleReceivedDate=$general->humanDateFormat($expStr[0]);
          $sampleReceivedTime =$expStr[1];
        }
        
        if(isset($result['lab_tested_date']) && trim($result['lab_tested_date'])!='' && $result['lab_tested_date']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['lab_tested_date']);
          $result['lab_tested_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
        }else{
          $result['lab_tested_date']='';
        }
        
        if(isset($result['last_viral_load_date']) && trim($result['last_viral_load_date'])!='' && $result['last_viral_load_date']!='0000-00-00'){
          $result['last_viral_load_date']=$general->humanDateFormat($result['last_viral_load_date']);
        }else{
          $result['last_viral_load_date']='';
        }
        if(!isset($result['patient_receive_sms']) || trim($result['patient_receive_sms'])== ''){
          $result['patient_receive_sms'] = 'missing';
        }
        if(!isset($result['gender']) || trim($result['gender'])== ''){
          $result['gender'] = 'not reported';
        }
        if(isset($result['reviewedBy']) && trim($result['reviewedBy'])!= ''){
          $resultReviewedBy = ucwords($result['reviewedBy']);
        }else{
          $resultReviewedBy  = '';
        }
        if(isset($result['approvedBy']) && trim($result['approvedBy'])!= ''){
          $resultApprovedBy = ucwords($result['approvedBy']);
        }else{
          $resultApprovedBy  = '';
        }
        $vlResult = '';
        $smileyContent = '';
        $showMessage = '';
        $tndMessage = '';
        $resultTextSize = '12px';
        $messageTextSize = '12px';
        if($result['result']!= NULL && trim($result['result'])!= '') {
          $resultType = is_numeric($result['result']);
          if(in_array(strtolower(trim($result['result'])), array("tnd","target not detected"))){
            $vlResult = 'TND*';
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_smile.png" alt="smile_face"/>';
            $showMessage = 'Viral load adequately controlled : continue current regimen';
            $tndMessage = 'TND* - Target not Detected';
            $resultTextSize = '18px';
          }else if(in_array(strtolower(trim($result['result'])), array("failed","fail","no_sample"))){
            $vlResult = $result['result'];
            $smileyContent = '';
            $showMessage = '';
            $messageTextSize = '14px';
          }else if(trim($result['result']) > 1000 && $result['result']<=10000000){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_frown.png" alt="frown_face"/>';
            $showMessage = 'High Viral Load - need assessment for enhanced adherence or clinical assessment for possible switch to second line.';
            $messageTextSize = '15px';
          }else if(trim($result['result']) <= 1000 && $result['result']>=20){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_smile.png" alt="smile_face"/>';
            $showMessage = 'Viral load adequately controlled : continue current regimen';
          }else if(trim($result['result'] > 10000000) && $resultType){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_frown.png" alt="frown_face"/>';
            //$showMessage = 'Value outside machine detection limit';
          }else if(trim($result['result'] < 20) && $resultType){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_smile.png" alt="smile_face"/>';
            //$showMessage = 'Value outside machine detection limit';
          }else if(trim($result['result']=='<20')){
            $vlResult = '&lt;20';
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_smile.png" alt="smile_face"/>';
            $showMessage = 'Viral load adequately controlled : continue current regimen <br/> Value is outside machine testing limit, cannot be less than 20';
          }else if(trim($result['result']=='>10000000')){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_frown.png" alt="frown_face"/>';
            $showMessage = 'High Viral Load - need assessment for enhanced adherence or clinical assessment for possible switch to second line.<br/>Value is outside machine testing limit, cannot be greater than 10M';
          }else if($result['vl_test_platform']=='Roche'){
            //
            $chkSign = '';
              $smileyShow = '';
              $chkSign = strchr($result['result'],'>');
              if($chkSign!=''){
                $smileyShow =str_replace(">","",$result['result']);
                $vlResult = $result['result'];
                //$showMessage = 'Invalid value';
              }
              $chkSign = '';
              $chkSign = strchr($result['result'],'<');
              if($chkSign!=''){
                $smileyShow =str_replace("<","",$result['result']);
                $vlResult = str_replace("<","&lt;",$result['result']);
                //$showMessage = 'Invalid value';
              }
              if($smileyShow!='' && $smileyShow <= 1000){
                $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_smile.png" alt="smile_face"/>';
              }else if($smileyShow!='' && $smileyShow > 1000){
                $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_frown.png" alt="frown_face"/>';
              }
              //
          }
        }
        if(isset($arr['show_smiley']) && trim($arr['show_smiley']) == "no"){
          $smileyContent = '';
        }
        if($result['status']=='4'){
        $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/cross.png" alt="rejected"/>';
      }
        
        $html = '';
        $html .= '<div style="">';
            $html.='<table style="padding:2px;">';
              if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                $html .='<tr>';
                  $html .='<td colspan="4" style="text-align:center;"><img src="uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;" alt="logo"></td>';
                $html .='</tr>';
              }
              $html .='<tr>';
               $html .='<td colspan="4" style="text-align:left;"><h4>Viral Load Results</h4></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Clinic code</td>';
               $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$result['facility_code'].'</td>';
               $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Province</td>';
               $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.strtoupper($result['state']).'</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="4">';
                $html .='<table>';
                 $html .='<tr>';
                  $html .='<td style="width:50%;"></td>';
                   $html .='<td style="width:25%;line-height:14px;font-size:13px;font-weight:bold;text-align:left;">District</td>';
                  $html .='<td style="width:25%;line-height:14px;font-size:12px;text-align:left;">&nbsp;'.strtoupper($result['district']).'</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="width:50%;"></td>';
                   $html .='<td style="width:25%;line-height:14px;font-size:13px;font-weight:bold;text-align:left;">Clinic Name</td>';
                  $html .='<td style="width:25%;line-height:14px;font-size:12px;text-align:left;">&nbsp;'.strtoupper($result['facility_name']).'</td>';
                $html .='</tr>';
                $html .='</table>';
                $html .='</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Clinician name</td>';
               $html .='<td colspan="3" style="line-height:22px;font-size:10px;text-align:left;">'.ucwords($result['lab_contact_person']).'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="4" style="line-height:2px;border-bottom:2px solid #333;"></td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="4">';
                 $html .='<table>';
                  $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Testing Lab</td>';
                    $html .='<td colspan="4" style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['labName']).'</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Lab number</td>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Barcode number</td>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Sample Collection Date</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.$result['lab_no'].'</td>';
                    $html .='<td style="line-height:22px;font-size:13px;text-align:left;">'.$result['serial_no'].'</td>';
                    $html .='<td style="line-height:22px;font-size:13px;text-align:left;">'.$result['sample_collection_date']." ".$sampleCollectionTime.'</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="5" style="line-height:2px;border-bottom:2px solid #333;"></td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="5" style="line-height:2px;"></td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Patient OI / ART Number</td>';
                    $html .='<td colspan="3" style="line-height:22px;font-size:13px;text-align:left;">'.$result['art_no'].'</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">First Name</td>';
                    $html .='<td colspan="3" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Surname</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['patient_name']).'</td>';
                    $html .='<td colspan="3" style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['surname']).'</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Consent to SMS</td>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Mobile number</td>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Age</td>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Sex</td>';
                   $html .='</tr>';
                   $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['patient_receive_sms']).'</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$result['patient_phone_number'].'</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$age.'</td>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.ucwords(str_replace("_"," ",$result['gender'])).'</td>';
                   $html .='</tr>';
                 $html .='</table>';
                $html .='</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="4" style="line-height:2px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3">';
                $html .='<table cellspacing="6" style="border:2px solid #333;">';
                  $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Sample Received Date</td>';
                    $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Date of Viral Load Result</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$sampleReceivedDate.'</td>';
          $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$sampleReceivedTime.'</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.$result['lab_tested_date'].'</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Specimen type</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['sample_name']).'</td>';
                    $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Testing Platform</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['vl_test_platform']).'</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td colspan="4" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Result of viral load(copies/ml)</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td colspan="4" style="line-height:22px;font-size:'.$resultTextSize.';text-align:left;">'.$vlResult.'</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Reviewed by</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$resultReviewedBy.'</td>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Approved by</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$resultApprovedBy.'</td>';
                  $html .='</tr>';
                  
                  if(isset($result['rejection_reason_name']) && $result['rejection_reason_name'] != null && trim($result['rejection_reason_name']) != ""){
                  $html .='<tr>';
                    $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Rejected Reason</td>';
                    $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result['rejection_reason_name']).'</td>';
                  $html .='</tr>';
                  }
                  
                  if(trim($showMessage)!= ''){
                    $html .='<tr>';
                      $html .='<td colspan="4" style="line-height:22px;font-size:'.$messageTextSize.';text-align:left;">'.$showMessage.'</td>';
                    $html .='</tr>';
                    $html .='<tr>';
                      $html .='<td colspan="4" style="line-height:4px;"></td>';
                    $html .='</tr>';
                  }
                  if(trim($tndMessage)!= ''){
                    $html .='<tr>';
                      $html .='<td colspan="4" style="line-height:22px;font-size:18px;text-align:left;">'.$tndMessage.'</td>';
                    $html .='</tr>';
                    $html .='<tr>';
                      $html .='<td colspan="4" style="line-height:6px;"></td>';
                    $html .='</tr>';
                  }
                  $html .='<tr>';
                    $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Lab comments</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td colspan="4" style="line-height:22px;font-size:12px;text-align:left;">'.ucfirst($result['comments']).'</td>';
                  $html .='</tr>';
                $html .='</table>';
               $html .='</td>';
               $html .='<td style="text-align:left;">';
                $html.='<table><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td>'.$smileyContent.'</td></tr></table>';
               $html .='</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Previous results</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="2" style="font-size:10px;font-weight:bold;text-align:left;">Previous Sample Collection Date</td>';
               $html .='<td colspan="2" style="font-size:10px;text-align:left;">'.$result['last_viral_load_date'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="2" style="font-size:10px;font-weight:bold;text-align:left;">Result of previous viral load(copies/ml)</td>';
               $html .='<td colspan="2" style="font-size:10px;text-align:left;">'.$result['last_viral_load_result'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="4" style="line-height:72px;border-bottom:1px solid #333;"></td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="4">';
                 $html .='<table>';
                  $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:4px;"></td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td style="font-size:10px;text-align:left;width:60%;"><img src="assets/img/smiley_smile.png" alt="smile_face" style="width:10px;height:10px;"/> = VL < = 1000 copies/ml: Continue on current regimen</td>';
                    $html .='<td style="font-size:10px;text-align:left;">Printed on : '.$printDate.'&nbsp;&nbsp;'.$printDateTime.'</td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td colspan="2" style="line-height:4px;"></td>';
                  $html .='</tr>';
                  $html .='<tr>';
                    $html .='<td colspan="2" style="font-size:10px;text-align:left;width:60%;"><img src="assets/img/smiley_frown.png" alt="frown_face" style="width:10px;height:10px;"/> = VL > 1000 copies/ml: copies/ml: Clinical and counselling action required</td>';
                  $html .='</tr>';
                 $html .='</table>';
                $html .='</td>';
              $html .='</tr>';
          $html.='</table>';
        $html .= "</div>";
        if($result['result']!=''){
          $pdf->writeHTML($html);
          $pdf->lastPage();
          $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
          $pdf->Output($filename,"F");
          $pages[] = $filename;
        $page++;
        }
      if(isset($_POST['source']) && trim($_POST['source']) == 'print'){
        //Add event log
        $eventType = 'print-result';
        $action = ucwords($_SESSION['userName']).' print the test result with patient code '.$result['art_no'];
        $resource = 'print-test-result';
        $data=array(
        'event_type'=>$eventType,
        'action'=>$action,
        'resource'=>$resource,
        'date_time'=>$general->getDateTime()
        );
        $db->insert($tableName1,$data);
        //Update print datetime in VL tbl.
        $db=$db->where('vl_sample_id',$result['vl_sample_id']);
        $db->update($tableName2,array('date_result_printed'=>$general->getDateTime()));
      }
    }
    
    $resultFilename = '';
    if(count($pages) >0){
        $resultPdf = new Pdf_concat();
        $resultPdf->setFiles($pages);
        $resultPdf->concat();
        $resultFilename = 'vl-request-result-' . date('d-M-Y-H-i-s') . '.pdf';
        $resultPdf->Output(UPLOAD_PATH. DIRECTORY_SEPARATOR .$resultFilename, "F");
        $general->removeDirectory($pathFront);
        unset($_SESSION['rVal']);
    }
    
}

echo $resultFilename;
?>