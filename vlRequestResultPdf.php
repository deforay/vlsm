<?php
//print_r($result);die;
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
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
$pdf->SetFont('times', '', 18);

$pathFront=realpath('./uploads');
//$pdf = new TCPDF();
$pdf->AddPage();
$general=new Deforay_Commons_General();
$id=$_POST['id'];
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}

$fQuery="SELECT * from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN r_art_code_details as r_a_c_d ON r_a_c_d.art_id=vl.current_regimen where treament_id=$id";
$result=$db->query($fQuery);

if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00'){
  $xplodSampleCollectionDate = explode(" ",$result[0]['sample_collection_date']);  
 $result[0]['sample_collection_date']=$general->humanDateFormat($xplodSampleCollectionDate[0]);
}else{
 $result[0]['sample_collection_date']='N/A';
}
if(isset($result[0]['date_of_initiation_of_current_regimen']) && trim($result[0]['date_of_initiation_of_current_regimen'])!='' && $result[0]['date_of_initiation_of_current_regimen']!='0000-00-00'){
 $result[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($result[0]['date_of_initiation_of_current_regimen']);
}else{
 $result[0]['date_of_initiation_of_current_regimen']='N/A';
}
if(isset($result[0]['date_sample_received_at_testing_lab']) && trim($result[0]['date_sample_received_at_testing_lab'])!='' && $result[0]['date_sample_received_at_testing_lab']!='0000-00-00'){
 $result[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($result[0]['date_sample_received_at_testing_lab']);
}else{
 $result[0]['date_sample_received_at_testing_lab']='N/A';
}
if(isset($result[0]['lab_tested_date']) && trim($result[0]['lab_tested_date'])!='' && $result[0]['lab_tested_date']!='0000-00-00'){
 $result[0]['lab_tested_date']=$general->humanDateFormat($result[0]['lab_tested_date']);
}else{
 $result[0]['lab_tested_date']='N/A';
}
if(isset($result[0]['result_reviewed_date']) && trim($result[0]['result_reviewed_date'])!='' && $result[0]['result_reviewed_date']!='0000-00-00'){
 $result[0]['result_reviewed_date']=$general->humanDateFormat($result[0]['result_reviewed_date']);
}else{
 $result[0]['result_reviewed_date']='N/A';
}
$age = "";
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

$html = "";
$html .= '<div style="border:1px solid #333;">';
$html.='<table style="padding:2px;">';
    if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
      $html .='<tr>';
        $html .='<td colspan="4" style="text-align:center;"><img src="uploads/logo/'.$arr['logo'].'" alt="logo"></td>';
      $html .='</tr>';
    }
    
    if(isset($arr['header']) && trim($arr['header'])!= '') {
      $html .='<tr>';
        $html .='<td colspan="4" style="text-align:center;font-size:16px;">'.nl2br($arr['header']).'</td>';
      $html .='</tr>';
    }
    $html .='<tr style="line-height:30px;">';
      $html .='<td colspan="2" style="text-align:left;font-size:12px;"><strong>Dispensary</strong></td>';
      $html .='<td colspan="2" style="text-align:left;font-size:12px;"><strong>LAB: '.ucfirst($result[0]['lab_name']).'</strong></td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td colspan="2" style="text-align:center;font-size:14px;"><strong>Viral Load Results</strong></td>';
      $html .='<td colspan="2" style="text-align:center;font-size:14px;"><strong>Historical Information</strong></td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Patient CCC No</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['art_no'].'</td>';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Sample Type</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['sample_name'].'</td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Date Collected</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['sample_collection_date'].'</td>';
      $html .='<td style="text-align:left;font-size:12px;"><strong>ART Intiation Date</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['date_of_initiation_of_current_regimen'].'</td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Date Received</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['date_sample_received_at_testing_lab'].'</td>';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Current Regimen</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['art_code'].'</td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Date Tested</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['lab_tested_date'].'</td>';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Justification</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['justification'].'</td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Age</strong></td>';
      $html .='<td colspan="3" style="text-align:left;font-size:12px;">'.$age.'</td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:14px;"><strong>Test Result</strong></td>';
      $html .='<td colspan="3" style="text-align:left;font-size:12px;"><strong>'.$result[0]['result'].'</strong></td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:14px;"><strong>Comments</strong></td>';
      $html .='<td colspan="3" style="text-align:left;font-size:12px;"><strong>'.ucfirst($result[0]['comments']).'</strong></td>';
    $html .='</tr>';
    $html .='<tr style="line-height:30px;">';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Result Reviewed By</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.ucfirst($result[0]['result_reviewed_by']).'</td>';
      $html .='<td style="text-align:left;font-size:12px;"><strong>Date Reviewed</strong></td>';
      $html .='<td style="text-align:left;font-size:12px;">'.$result[0]['result_reviewed_date'].'</td>';
    $html .='</tr>';
$html.='</table>';
$html .= "</div>";
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-result-form-' . date('d-M-Y') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>