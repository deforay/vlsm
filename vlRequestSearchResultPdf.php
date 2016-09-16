<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/tcpdf/tcpdf.php');
include ('./includes/fpdi/fpdi.php');
define('UPLOAD_PATH','uploads');
$general=new Deforay_Commons_General();
$printedTime = $general->getDateTime();
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
        $this->SetFont('helvetica', 'I', 8);
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
        $pdf->SetFont('helveticaI', '', 18);
        
        $pdf->AddPage();
        //Set Age
        $age = 'Unknown';
        if(isset($result['age_in_yrs']) && trim($result['age_in_yrs'])!=''){
          $age = $result['age_in_yrs'];
        }else{
          if(isset($result['patient_dob']) && trim($result['patient_dob'])!='' && $result['patient_dob']!='0000-00-00'){
            $todayDate = strtotime(date('Y-m-d'));
            $dob = strtotime($result['patient_dob']);
            $difference = $todayDate - $dob;
            $seconds_per_year = 60*60*24*365;
            $age = round($difference / $seconds_per_year);
          }
        }
        if(isset($result['sample_collection_date']) && trim($result['sample_collection_date'])!='' && $result['sample_collection_date']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['sample_collection_date']);
          $result['sample_collection_date']=$general->humanDateFormat($expStr[0]);
          $sampleCollectionTime = $expStr[1];
        }else{
          $result['sample_collection_date']='';
        }
        if(isset($result['date_sample_received_at_testing_lab']) && trim($result['date_sample_received_at_testing_lab'])!='' && $result['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['date_sample_received_at_testing_lab']);
          $result['date_sample_received_at_testing_lab']=$general->humanDateFormat($expStr[0]);
          $sampleReceivedTime = $expStr[1];
        }else{
          $result['date_sample_received_at_testing_lab']='';
        }
        if(isset($result['last_viral_load_result']) && trim($result['last_viral_load_result'])!='' && $result['last_viral_load_result']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['last_viral_load_result']);
          $result['last_viral_load_result']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
        }else{
          $result['last_viral_load_result']='';
        }
        if(isset($result['last_viral_load_date']) && trim($result['last_viral_load_date'])!='' && $result['last_viral_load_date']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['last_viral_load_date']);
          $result['last_viral_load_date']=$general->humanDateFormat($expStr[0]);
          $lastViralLoadResultTime = $expStr[1];
        }else{
          $result['last_viral_load_date']='';
        }
        if(!isset($result['patient_receive_sms']) || trim($result['patient_receive_sms'])== ''){
          $result['patient_receive_sms'] = 'missing';
        }
        if(!isset($result['gender']) || trim($result['gender'])== ''){
          $result['gender'] = 'not reported';
        }
        $vlResult = '';
        if(isset($result['absolute_value']) && trim($result['absolute_value'])!= ''){
          $vlResult = $result['absolute_value'];
        }elseif(isset($result['log_value']) && trim($result['log_value'])!= ''){
          $vlResult = $result['log_value'];
        }elseif(isset($result['text_value']) && trim($result['text_value'])!= ''){
          $vlResult = $result['text_value'];
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
        $smileyContent = '';
        if(isset($arr['show_smiley']) && trim($arr['show_smiley']) == "yes"){
         if(isset($result['absolute_value']) && trim($result['absolute_value'])!= '' && trim($result['absolute_value']) > 1000){
           $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_frown.png" alt="frown_face"/>';
         }else if(isset($result['absolute_value']) && trim($result['absolute_value'])!= '' && trim($result['absolute_value']) <= 1000){
           $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_smile.png" alt="smile_face"/>';
         }
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
             $html .='<td colspan="4" style="text-align:left;"><h3>Viral Load Results</h3></td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Clinic code</td>';
             $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result['facility_code'].'</td>';
             $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucwords($result['facility_name']).'</td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Clinician name</td>';
             $html .='<td colspan="3" style="line-height:22px;font-size:10px;font-weight:bold;text-align:left;">'.ucwords($result['request_clinician']).'</td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td colspan="4" style="line-height:2px;border-bottom:2px solid #333;"></td>';
            $html .='</tr>';
            $html .='<tr>';
              $html .='<td colspan="3">';
               $html .='<table>';
                $html .='<tr>';
                  $html .='<td colspan="4" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">'.ucwords($result['lab_name']).'</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lab number</td>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Barcode number</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.ucwords($result['lab_name']).'</td>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$result['serial_no'].'</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Patient Id</td>';
                  $html .='<td colspan="3" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">'.$result['art_no'].'</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">First name</td>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Surname</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">'.ucwords($result['patient_name']).'</td>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">'.ucwords($result['surname']).'</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Consent to SMS</td>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Mobile number</td>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Age</td>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Sex</td>';
                 $html .='</tr>';
                 $html .='<tr>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucwords($result['patient_receive_sms']).'</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result['patient_phone_number'].'</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$age.'</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;font-weight:bold;text-align:left;">'.ucwords($result['gender']).'</td>';
                 $html .='</tr>';
               $html .='</table>';
              $html .='</td>';
              $html .='<td></td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td colspan="3">';
              $html .='<table cellspacing="6" style="border:2px solid #333;">';
                $html .='<tr>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Sample Collection Date</td>';
                  $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Date of Viral Load Result</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result['sample_collection_date'].'</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$sampleCollectionTime.'</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result['date_sample_received_at_testing_lab'].'</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$sampleReceivedTime.'</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Specimen Type</td>';
                  $html .='<td colspan="3" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucwords($result['sample_name']).'</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td colspan="4" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Result of viral load(copies/ml)</td>';
                $html .='</tr>';
                $html .='<tr>';
                    $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$vlResult.'</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Reviewed by</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$resultReviewedBy.'</td>';
                  $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Approved by</td>';
                  $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$resultApprovedBy.'</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Lab comments</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucfirst($result['comments']).'</td>';
                $html .='</tr>';
              $html .='</table>';
             $html .='</td>';
             $html .='<td style="text-align:left;">'.$smileyContent.'</td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Previous results</td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td colspan="2" style="font-size:10px;font-weight:bold;">Previous Sample Collection Date</td>';
             $html .='<td colspan="2" style="font-size:10px;font-style:italic;">'.$result['last_viral_load_date'].'</td>';
            $html .='</tr>';
            $html .='<tr>';
             $html .='<td colspan="2" style="font-size:10px;font-weight:bold;">Result of previous viral load(copies/ml)</td>';
             $html .='<td colspan="2" style="font-size:10px;font-style:italic;">'.$result['last_viral_load_result'].'</td>';
            $html .='</tr>';
            $html .='<tr>';
              $html .='<td colspan="4" style="line-height:40px;border-bottom:1px solid #333;"></td>';
            $html .='</tr>';
            $html .='<tr>';
            $html .='<td colspan="4">';
            $html .='<table>';
              $html .='<tr>';
                $html .='<td style="font-size:10px;text-align:left;width:60%;"><img src="assets/img/smiley_smile.png" alt="smile_face" style="width:16px;height:16px;"/> = VL < = 1000 copies/ml: Continue on current regimen</td>';
                $html .='<td style="font-size:10px;font-style:italic;text-align:left;">Print date '.$printDate.'&nbsp;&nbsp;&nbsp;&nbsp;Time '.$printDateTime.'</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="2" style="font-size:10px;text-align:left;width:60%;"><img src="assets/img/smiley_frown.png" alt="frown_face" style="width:16px;height:16px;"/> = VL > 1000 copies/ml: copies/ml: Clinical and counselling action required</td>';
              $html .='</tr>';
              $html .='</table>';
            $html .='</td>';
          $html .='</tr>';
        $html.='</table>';
        $html .= "</div>";
        $pdf->writeHTML($html);
        $pdf->lastPage();
        $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
        $pdf->Output($filename,"F");
        $pages[] = $filename;
      $page++;
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