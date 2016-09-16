<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/tcpdf/tcpdf.php');
define('UPLOAD_PATH','uploads');
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
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
//$pdf->SetAuthor('Saravanan');
$pdf->SetTitle('Vl Request Result Form');
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

$pathFront=realpath('./uploads');
//$pdf = new TCPDF();
$pdf->AddPage();
$general=new Deforay_Commons_General();
$printedTime = $general->getDateTime();
$expStr=explode(" ",$printedTime);
$printDate =$general->humanDateFormat($expStr[0]);
$printDateTime = $expStr[1];
$tableName1="activity_log";
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$id=$_POST['id'];
$fQuery="SELECT vl.sample_code,vl.serial_no,vl.patient_name,vl.patient_name,vl.surname,vl.patient_dob,vl.art_no,vl.gender,vl.patient_receive_sms,vl.patient_phone_number,vl.sample_collection_date,vl.request_clinician,vl.clinician_ph_no,vl.sample_testing_date,vl.date_sample_received_at_testing_lab,vl.age_in_yrs,vl.lab_name,vl.lab_contact_person,vl.lab_phone_no,vl.lab_tested_date,vl.request_clinician,vl.log_value,vl.absolute_value,vl.text_value,vl.result,vl.comments,vl.result_reviewed_by,vl.last_viral_load_result,vl.result_reviewed_date,f.facility_name,f.facility_code,s.sample_name,u_d.user_name as reviewedBy,a_u_d.user_name as approvedBy FROM vl_request_form as vl INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by WHERE treament_id=$id";
$result=$db->query($fQuery);
//Set Age
$age = 'Unknown';
if(isset($result[0]['age_in_yrs']) && trim($result[0]['age_in_yrs'])!=''){
  $age = $result[0]['age_in_yrs'];
}else{
  if(isset($result[0]['patient_dob']) && trim($result[0]['patient_dob'])!='' && $result[0]['patient_dob']!='0000-00-00'){
    $todayDate = strtotime(date('Y-m-d'));
    $dob = strtotime($result[0]['patient_dob']);
    $difference = $todayDate - $dob;
    $seconds_per_year = 60*60*24*365;
    $age = round($difference / $seconds_per_year);
  }
}
if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$result[0]['sample_collection_date']);
  $result[0]['sample_collection_date']=$general->humanDateFormat($expStr[0]);
  $sampleCollectionTime = $expStr[1];
}else{
  $result[0]['sample_collection_date']='';
  $sampleCollectionTime = '';
}
if(isset($result[0]['sample_testing_date']) && trim($result[0]['sample_testing_date'])!='' && $result[0]['sample_testing_date']!='0000-00-00'){
  $result[0]['sample_testing_date']=$general->humanDateFormat($result[0]['sample_testing_date']);
}else{
  $result[0]['sample_testing_date']='';
}
if(isset($result[0]['last_viral_load_result']) && trim($result[0]['last_viral_load_result'])!='' && $result[0]['last_viral_load_result']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$result[0]['last_viral_load_result']);
  $result[0]['last_viral_load_result']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
  $result[0]['last_viral_load_result']='';
}
if(isset($result[0]['last_viral_load_date']) && trim($result[0]['last_viral_load_date'])!='' && $result[0]['last_viral_load_date']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$result[0]['last_viral_load_date']);
  $result[0]['last_viral_load_date']=$general->humanDateFormat($expStr[0]);
  $lastViralLoadResultTime = $expStr[1];
}else{
  $result[0]['last_viral_load_date']='';
  $lastViralLoadResultTime = '';
}
if(!isset($result[0]['patient_receive_sms']) || trim($result[0]['patient_receive_sms'])== ''){
  $result[0]['patient_receive_sms'] = 'missing';
}
if(!isset($result[0]['gender']) || trim($result[0]['gender'])== ''){
  $result[0]['gender'] = 'not reported';
}
$vlResult = '';
if(isset($result[0]['absolute_value']) && trim($result[0]['absolute_value'])!= ''){
  $vlResult = $result[0]['absolute_value'];
}elseif(isset($result[0]['log_value']) && trim($result[0]['log_value'])!= ''){
  $vlResult = $result[0]['log_value'];
}elseif(isset($result[0]['text_value']) && trim($result[0]['text_value'])!= ''){
  $vlResult = $result[0]['text_value'];
}
if(isset($result[0]['reviewedBy']) && trim($result[0]['reviewedBy'])!= ''){
  $resultReviewedBy = ucwords($result[0]['reviewedBy']);
}else{
  $resultReviewedBy  = '';
}
if(isset($result[0]['approvedBy']) && trim($result[0]['approvedBy'])!= ''){
  $resultApprovedBy = ucwords($result[0]['approvedBy']);
}else{
  $resultApprovedBy  = '';
}
$smileyContent = '';
if(isset($arr['show_smiley']) && trim($arr['show_smiley']) == "yes"){
 if(isset($result[0]['absolute_value']) && trim($result[0]['absolute_value'])!= '' && trim($result[0]['absolute_value']) > 1000){
   $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="assets/img/smiley_frown.png" alt="frown_face"/>';
 }else if(isset($result[0]['absolute_value']) && trim($result[0]['absolute_value'])!= '' && trim($result[0]['absolute_value']) <= 1000){
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
     $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result[0]['facility_code'].'</td>';
     $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucwords($result[0]['facility_name']).'</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Clinician name</td>';
     $html .='<td colspan="3" style="line-height:22px;font-size:10px;font-weight:bold;text-align:left;">'.ucwords($result[0]['request_clinician']).'</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="4" style="line-height:2px;border-bottom:2px solid #333;"></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td colspan="3">';
       $html .='<table>';
        $html .='<tr>';
          $html .='<td colspan="4" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">'.ucwords($result[0]['lab_name']).'</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lab number</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Barcode number</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.ucwords($result[0]['lab_name']).'</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$result[0]['serial_no'].'</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Patient Id</td>';
          $html .='<td colspan="3" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">'.$result[0]['art_no'].'</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">First name</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Surname</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">'.ucwords($result[0]['patient_name']).'</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">'.ucwords($result[0]['surname']).'</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Consent to SMS</td>';
          $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Mobile number</td>';
          $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Age</td>';
          $html .='<td style="line-height:22px;font-size:14px;font-weight:bold;text-align:left;">Sex</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucwords($result[0]['patient_receive_sms']).'</td>';
          $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result[0]['patient_phone_number'].'</td>';
          $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$age.'</td>';
          $html .='<td style="line-height:22px;font-size:12px;font-style:italic;font-weight:bold;text-align:left;">'.ucwords($result[0]['gender']).'</td>';
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
          $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result[0]['sample_collection_date'].'</td>';
          $html .='<td style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$sampleCollectionTime.'</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.$result[0]['sample_testing_date'].'</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Specimen type</td>';
          $html .='<td colspan="3" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucwords($result[0]['sample_name']).'</td>';
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
          $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">Viral load adequately controlled : continue current regimen</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Lab comments</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-style:italic;text-align:left;">'.ucfirst($result[0]['comments']).'</td>';
        $html .='</tr>';
      $html .='</table>';
     $html .='</td>';
     $html .='<td style="text-align:left;">'.$smileyContent.'</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Previous results</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="2" style="font-size:10px;font-weight:bold;text-align:left;">Previous Sample Collection Date</td>';
     $html .='<td colspan="2" style="font-size:10px;font-style:italic;text-align:left;">'.$result[0]['last_viral_load_date'].'</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="2" style="font-size:10px;font-weight:bold;text-align:left;">Result of previous viral load(copies/ml)</td>';
     $html .='<td colspan="2" style="font-size:10px;font-style:italic;text-align:left;">'.$result[0]['last_viral_load_result'].'</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="4" style="line-height:40px;border-bottom:1px solid #333;"></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td colspan="4">';
       $html .='<table>';
        $html .='<tr>';
          $html .='<td style="font-size:10px;text-align:left;width:60%;"><img src="assets/img/smiley_smile.png" alt="smile_face" style="width:14px;height:14px;"/> = VL < = 1000 copies/ml: Continue on current regimen</td>';
          $html .='<td style="font-size:10px;font-style:italic;text-align:left;">Print date '.$printDate.'&nbsp;&nbsp;&nbsp;&nbsp;time '.$printDateTime.'</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="2" style="line-height:10px;"></td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="2" style="font-size:10px;text-align:left;width:60%;"><img src="assets/img/smiley_frown.png" alt="frown_face" style="width:14px;height:14px;"/> = VL > 1000 copies/ml: copies/ml: Clinical and counselling action required</td>';
        $html .='</tr>';
       $html .='</table>';
      $html .='</td>';
    $html .='</tr>';
$html.='</table>';
$html .= "</div>";
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-result-form-' . date('d-M-Y-H-i-s') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
//Add event log
if(isset($_POST['source']) && trim($_POST['source']) == 'print'){
  $eventType = 'print-result';
  $action = ucwords($_SESSION['userName']).' have been print the test result with patient CCC no. '.$result[0]['art_no'];
  $resource = 'print-test-result';
  $data=array(
  'event_type'=>$eventType,
  'action'=>$action,
  'resource'=>$resource,
  'date_time'=>$general->getDateTime()
  );
  $db->insert($tableName1,$data);
}
echo $filename;
?>