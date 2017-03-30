<?php
session_start();
ob_start();
include('MysqliDb.php');
include('General.php');
include ('tcpdf/tcpdf.php');
define('UPLOAD_PATH','../uploads');
$configQuery="SELECT value FROM global_config WHERE name = 'default_time_zone'";
$configResult=$db->query($configQuery);
if(isset($configResult) && count($configResult)> 0){
  date_default_timezone_set($configResult[0]['value']);
}else{
  date_default_timezone_set("Europe/London");
}
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
$pdf->SetFont('helvetica', '', 18);

$pathFront=realpath('../uploads');
//$pdf = new TCPDF();
$pdf->AddPage();
$general=new Deforay_Commons_General();
$printedTime = date('Y-m-d H:i:s');
$expStr=explode(" ",$printedTime);
$printDate =$general->humanDateFormat($expStr[0]);
$printDateTime = $expStr[1];
$tableName1="activity_log";
$tableName2="vl_request_form";
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$id=$_POST['id'];
$fQuery="SELECT vl.vl_sample_id,vl.sample_code,vl.serial_no,vl.patient_first_name,vl.patient_last_name,vl.patient_dob,vl.patient_age_in_years,vl.patient_age_in_months,vl.patient_art_no,vl.patient_gender,vl.consent_to_receive_sms,vl.patient_mobile_number,vl.sample_collection_date,vl.request_clinician_phone_number,vl.sample_testing_date,vl.sample_received_at_vl_lab_datetime,vl.lab_name,vl.request_clinician_name,vl.lab_phone_number,vl.sample_tested_datetime,vl.lab_code,vl.result_value_log,vl.result_value_absolute,vl.result_value_text,vl.result,vl.approver_comments,vl.result_reviewed_by,vl.last_viral_load_result,vl.last_viral_load_date,vl.result_reviewed_datetime,vl.result_approved_by,vl.vl_test_platform,vl.result_status,rs.rejection_reason_name,f.facility_name,l_f.facility_name as labName,f.facility_code,f.facility_state,f.facility_district,s.sample_name,u_d.user_name as reviewedBy,a_u_d.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id LEFT JOIN r_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by WHERE vl_sample_id=$id";
$result=$db->query($fQuery);
if(!isset($result[0]['facility_code']) || trim($result[0]['facility_code']) == ''){
  $result[0]['facility_code'] = '';
}
if(!isset($result[0]['facility_state']) || trim($result[0]['facility_state']) == ''){
  $result[0]['facility_state'] = '';
}
if(!isset($result[0]['facility_district']) || trim($result[0]['facility_district']) == ''){
  $result[0]['facility_district'] = '';
}
if(!isset($result[0]['facility_name']) || trim($result[0]['facility_name']) == ''){
  $result[0]['facility_name'] = '';
}
if(!isset($result[0]['labName']) || trim($result[0]['labName']) == ''){
  $result[0]['labName'] = '';
}
//Set Age
$age = 'Unknown';
if(isset($result[0]['patient_age_in_years']) && trim($result[0]['patient_age_in_years'])!='' && trim($result[0]['patient_age_in_years']) >0){
  $age = $result[0]['patient_age_in_years'];
}elseif(isset($result[0]['patient_age_in_months']) && trim($result[0]['patient_age_in_months'])!='' && trim($result[0]['patient_age_in_months']) >0){
  $age = "0.".$result[0]['patient_age_in_months'];
}elseif(isset($result[0]['patient_dob']) && trim($result[0]['patient_dob'])!='' && $result[0]['patient_dob']!='0000-00-00'){
  $todayDate = strtotime(date('Y-m-d'));
  $dob = strtotime($result[0]['patient_dob']);
  $difference = $todayDate - $dob;
  $seconds_per_year = 60*60*24*365;
  $age = round($difference / $seconds_per_year);
}

if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$result[0]['sample_collection_date']);
  $result[0]['sample_collection_date']=$general->humanDateFormat($expStr[0]);
  $sampleCollectionTime = $expStr[1];
}else{
  $result[0]['sample_collection_date']='';
  $sampleCollectionTime = '';
}
$sampleReceivedDate='';
$sampleReceivedTime='';
if(isset($result[0]['sample_received_at_vl_lab_datetime']) && trim($result[0]['sample_received_at_vl_lab_datetime'])!='' && $result[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$result[0]['sample_received_at_vl_lab_datetime']);
  $sampleReceivedDate=$general->humanDateFormat($expStr[0]);
  $sampleReceivedTime =$expStr[1];
}

if(isset($result[0]['sample_tested_datetime']) && trim($result[0]['sample_tested_datetime'])!='' && $result[0]['sample_tested_datetime']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$result[0]['sample_tested_datetime']);
  $result[0]['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
  $result[0]['sample_tested_datetime']='';
}

if(isset($result[0]['last_viral_load_date']) && trim($result[0]['last_viral_load_date'])!='' && $result[0]['last_viral_load_date']!='0000-00-00'){
  $result[0]['last_viral_load_date']=$general->humanDateFormat($result[0]['last_viral_load_date']);
}else{
  $result[0]['last_viral_load_date']='';
}

if(!isset($result[0]['patient_gender']) || trim($result[0]['patient_gender'])== ''){
  $result[0]['patient_gender'] = 'not reported';
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
$vlResult = '';
$smileyContent = '';
$showMessage = '';
$tndMessage = '';
$resultTextSize = '12px';
$messageTextSize = '12px';
if($result[0]['result']!= NULL && trim($result[0]['result'])!= '') {
  if(in_array(strtolower(trim($result[0]['result'])), array("tnd","target not detected"))){
    $vlResult = 'TND*';
    $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
    $showMessage = 'Charge Virale correctement validée: continuer le régime en cours';
    $tndMessage = 'TND* - Cible non détectée';
    $resultTextSize = '18px';
  }else if(in_array(strtolower(trim($result[0]['result'])), array("failed","fail","no_sample"))){
    $vlResult = $result[0]['result'];
    $smileyContent = '';
    $showMessage = '';
    $messageTextSize = '14px';
  }else if(trim($result[0]['result']) > 1000 && $result[0]['result']<=10000000){
    $vlResult = $result[0]['result'];
    $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_frown.png" alt="frown_face"/>';
    $showMessage = 'Charge Virale élevée - evaluation pour un renforcement de l?adhérence ou une évaluation Clinique pour un éventuel passage à la seconde ligne.';
    $messageTextSize = '16px';
  }else if(trim($result[0]['result']) <= 1000 && $result[0]['result']>=20){
    $vlResult = $result[0]['result'];
    $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
    $showMessage = 'Charge Virale correctement validée: continuer le régime en cours';
  }else if(trim($result[0]['result']=='<20')){
    $vlResult = '&lt;20';
    $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
    $showMessage = 'Charge Virale correctement validée: continuer le régime en cours.<br/>La valeur est inférieure à la limite de detection ou moins de 20.';
  }else if(trim($result[0]['result']=='>10000000')){
    $vlResult = $result[0]['result'];
    $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_frown.png" alt="frown_face"/>';
    $showMessage = 'Charge Virale élevée: Evaluation pour un renforcement de l?adhérence ou une évaluation clinique pour un éventuel passage à la seconde ligne.<br/>La valeur est supérieure à 10 000.';
  }
}
if($result[0]['rejection_reason_name']!=NULL){
  $result[0]['rejection_reason_name'] = $result[0]['rejection_reason_name'];
}else{
  $result[0]['rejection_reason_name'] = '';
}
if(isset($arr['show_smiley']) && trim($arr['show_smiley']) == "no"){
  $smileyContent = '';
}
if($result[0]['result_status']=='4'){
  $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/cross.png" alt="rejected"/>';
}

$html = '';
$html .= '<div style="">';
  $html.='<table style="padding:2px;">';
    if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
      $html .='<tr>';
        $html .='<td colspan="4" style="text-align:center;"><img src="../uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;" alt="logo"></td>';
      $html .='</tr>';
    }
    $html .='<tr>';
     $html .='<td colspan="4" style="text-align:left;"><h4>Viral Load Results</h4></td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Code Clinique</td>';
     $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$result[0]['facility_code'].'</td>';
     $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Province</td>';
     $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.strtoupper($result[0]['facility_state']).'</td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td colspan="4">';
      $html .='<table>';
      $html .='<tr>';
        $html .='<td style="width:50%;"></td>';
         $html .='<td style="width:25%;line-height:14px;font-size:13px;font-weight:bold;text-align:left;">Zone de santé</td>';
        $html .='<td style="width:25%;line-height:14px;font-size:12px;text-align:left;">&nbsp;'.strtoupper($result[0]['facility_district']).'</td>';
      $html .='</tr>';
      $html .='</table>';
      $html .='</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Nom clinicien</td>';
     $html .='<td colspan="3" style="line-height:22px;font-size:10px;font-weight:bold;text-align:left;">'.ucwords($result[0]['request_clinician_name']).'</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="4" style="line-height:2px;border-bottom:2px solid #333;"></td>';
    $html .='</tr>';
    $html .='<tr>';
      $html .='<td colspan="4">';
       $html .='<table>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Code Labo</td>';
          $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;"></td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Date du prélèvement</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.$result[0]['sample_code'].'</td>';
          $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;"></td>';
          $html .='<td style="line-height:22px;font-size:13px;text-align:left;">'.$result[0]['sample_collection_date']." ".$sampleCollectionTime.'</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="5" style="line-height:2px;border-bottom:2px solid #333;"></td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="5" style="line-height:2px;"></td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Code du patient</td>';
          $html .='<td colspan="3" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">'.$result[0]['patient_art_no'].'</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Âge</td>';
          $html .='<td colspan="3" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Sexe</td>';
         $html .='</tr>';
         $html .='<tr>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.$age.'</td>';
          $html .='<td colspan="3" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">'.ucwords(str_replace("_"," ",$result[0]['patient_gender'])).'</td>';
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
          $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Date de réception de léchantillon</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Date de remise du résultat</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$sampleReceivedDate.'</td>';
          $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.$sampleReceivedTime.'</td>';
          $html .='<td colspan="2" style="line-height:22px;font-size:12px;text-align:left;">'.$result[0]['sample_tested_datetime'].'</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Type d’échantillon</td>';
          $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result[0]['sample_name']).'</td>';
          $html .='<td style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Technique utilisée</td>';
          $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords(str_replace("_"," ",$result[0]['vl_test_platform'])).'</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="4" style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Résultat(copies/ml)</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="4" style="line-height:22px;font-size:'.$resultTextSize.';text-align:left;">'.$vlResult.'</td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td style="line-height:22px;font-size:13px;font-weight:bold;text-align:left;">Motifs de rejet</td>';
          $html .='<td style="line-height:22px;font-size:12px;text-align:left;">'.ucwords($result[0]['rejection_reason_name']).'</td>';
        $html .='</tr>';
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
      $html .='</table>';
      
     $html .='</td>';
     $html .='<td style="text-align:left;">';
       $html.='<table><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr><tr><td>'.$smileyContent.'</td></tr></table>';
       
     $html .='</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="4" style="line-height:22px;font-size:12px;font-weight:bold;text-align:left;">Résultats précédents</td>';
    $html .='</tr>';
    $html .='<tr>';
     $html .='<td colspan="2" style="font-size:10px;font-weight:bold;text-align:left;">Date dernière charge virale (demande)</td>';
     $html .='<td colspan="2" style="font-size:10px;text-align:left;">'.$result[0]['last_viral_load_date'].'</td>';
    $html .='</tr>';
    
    $html .='<tr>';
     $html .='<td colspan="2" style="font-size:10px;font-weight:bold;text-align:left;">Résultat dernière charge virale(copies/ml)</td>';
     $html .='<td colspan="2" style="font-size:10px;text-align:left;">'.$result[0]['last_viral_load_result'].'</td>';
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
          $html .='<td style="font-size:10px;text-align:left;width:60%;"><img src="../assets/img/smiley_smile.png" alt="smile_face" style="width:10px;height:10px;"/> = VL < = 1000 copies/ml: Continuer le régime en cours</td>';
          $html .='<td style="font-size:10px;text-align:left;">Imprimé : '.$printDate.'&nbsp;&nbsp;'.$printDateTime.'</td>';
        $html .='</tr>';
        
        $html .='<tr>';
          $html .='<td colspan="2" style="line-height:4px;"></td>';
        $html .='</tr>';
        $html .='<tr>';
          $html .='<td colspan="2" style="font-size:10px;text-align:left;width:60%;"><img src="../assets/img/smiley_frown.png" alt="frown_face" style="width:10px;height:10px;"/> = VL > 1000 copies/ml: Une visite pour conseil et bilan clinique est requise</td>';
        $html .='</tr>';
       $html .='</table>';
      $html .='</td>';
    $html .='</tr>';
  $html.='</table>';
$html .= "</div>";
$pdf->writeHTML(utf8_encode($html));
$pdf->lastPage();
$filename = 'vl-result-form-' . date('d-M-Y-H-i-s') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");

if(isset($_POST['source']) && trim($_POST['source']) == 'print'){
  //Add event log
  $eventType = 'print-result';
  $action = ucwords($_SESSION['userName']).' print the test result with patient code '.$result[0]['patient_art_no'];
  $resource = 'print-test-result';
  $data=array(
  'event_type'=>$eventType,
  'action'=>$action,
  'resource'=>$resource,
  'date_time'=>$general->getDateTime()
  );
  $db->insert($tableName1,$data);
  //Update print datetime in VL tbl.
  $db=$db->where('vl_sample_id',$result[0]['vl_sample_id']);
  $db->update($tableName2,array('result_printed_datetime'=>$general->getDateTime()));
}
echo $filename;
?>