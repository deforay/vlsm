<?php
//print_r($result);die;
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./tcpdf/tcpdf.php');

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
$pdf->SetTitle('Vl Request Form');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

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

$fQuery="SELECT * from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where treament_id=$id";
$result=$db->query($fQuery);

if(isset($result[0]['patient_dob']) && trim($result[0]['patient_dob'])!='' && $result[0]['patient_dob']!='0000-00-00'){
 $result[0]['patient_dob']=$general->humanDateFormat($result[0]['patient_dob']);
}else{
 $result[0]['patient_dob']='';
}

if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['sample_collection_date']);
 $result[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['sample_collection_date']='';
}

if(isset($result[0]['treatment_initiated_date']) && trim($result[0]['treatment_initiated_date'])!='' && trim($result[0]['treatment_initiated_date'])!='0000-00-00'){
 $result[0]['treatment_initiated_date']=$general->humanDateFormat($result[0]['treatment_initiated_date']);
}else{
 $result[0]['treatment_initiated_date']='';
}

if(isset($result[0]['date_of_initiation_of_current_regimen']) && trim($result[0]['date_of_initiation_of_current_regimen'])!='' && trim($result[0]['date_of_initiation_of_current_regimen'])!='0000-00-00'){
 $result[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($result[0]['date_of_initiation_of_current_regimen']);
}else{
 $result[0]['date_of_initiation_of_current_regimen']='';
}

if(isset($result[0]['routine_monitoring_last_vl_date']) && trim($result[0]['routine_monitoring_last_vl_date'])!='' && trim($result[0]['routine_monitoring_last_vl_date'])!='0000-00-00'){
 $result[0]['routine_monitoring_last_vl_date']=$general->humanDateFormat($result[0]['routine_monitoring_last_vl_date']);
}else{
 $result[0]['routine_monitoring_last_vl_date']='';
}

if(isset($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']) && trim($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'])!='' && trim($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'])!='0000-00-00'){
 $result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']=$general->humanDateFormat($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']);
}else{
 $result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']='';
}

if(isset($result[0]['suspected_treatment_failure_last_vl_date']) && trim($result[0]['suspected_treatment_failure_last_vl_date'])!='' && trim($result[0]['suspected_treatment_failure_last_vl_date'])!='0000-00-00'){
 $result[0]['suspected_treatment_failure_last_vl_date']=$general->humanDateFormat($result[0]['suspected_treatment_failure_last_vl_date']);
}else{
 $result[0]['suspected_treatment_failure_last_vl_date']='';
}

if(isset($result[0]['request_date']) && trim($result[0]['request_date'])!='' && trim($result[0]['request_date'])!='0000-00-00'){
 $result[0]['request_date']=$general->humanDateFormat($result[0]['request_date']);
}else{
 $result[0]['request_date']='';
}

if(isset($result[0]['date_sample_received_at_testing_lab']) && trim($result[0]['date_sample_received_at_testing_lab'])!='' && trim($result[0]['date_sample_received_at_testing_lab'])!='0000-00-00'){
 $result[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($result[0]['date_sample_received_at_testing_lab']);
}else{
 $result[0]['date_sample_received_at_testing_lab']='';
}

if(isset($result[0]['date_results_dispatched']) && trim($result[0]['date_results_dispatched'])!='' && trim($result[0]['date_results_dispatched'])!='0000-00-00'){
 $result[0]['date_results_dispatched']=$general->humanDateFormat($result[0]['date_results_dispatched']);
}else{
 $result[0]['date_results_dispatched']='';
}
if($result[0]['current_regimen']!=''){
$aQuery="SELECT * from r_art_code_details where art_id=".$result[0]['current_regimen'];
$aResult=$db->query($aQuery);
}else{
    $aResult[0]['art_code'] = '';
}
if($result[0]['sample_id']!=''){
$sampleTypeQuery="SELECT * FROM r_sample_type where ".$result[0]['sample_id'];
$sampleTypeResult = $db->rawQuery($sampleTypeQuery);
}else{
    $sampleTypeResult[0]['sample_name'] = '';
}
//routine monitor
if($result[0]['routine_monitoring_sample_type']!=''){
$rtQuery="SELECT * FROM r_sample_type where ".$result[0]['routine_monitoring_sample_type'];
$rtResult = $db->rawQuery($rtQuery);
}else{
$rtResult[0]['sample_name']     = '';
}
//Repeat VL
if($result[0]['vl_treatment_failure_adherence_counseling_sample_type']!=''){
$rVlQuery="SELECT * FROM r_sample_type where ".$result[0]['vl_treatment_failure_adherence_counseling_sample_type'];
$rVlResult = $db->rawQuery($rVlQuery);
}else{
$rVlResult[0]['sample_name']= '';    
}
//Failure VL
if($result[0]['suspected_treatment_failure_sample_type']!=''){
$fVlQuery="SELECT * FROM r_sample_type where ".$result[0]['suspected_treatment_failure_sample_type'];
$fVlResult = $db->rawQuery($fVlQuery);
}else{
$fVlResult[0]['sample_name']     = '';
}
$html = "";
$html.='<table border="1" style="font-size:13px;line-height:20px;">';
$html.='<h4 style="">Facility Details</h4>';
$html.='<tr style=""><td style="vertical-align: middle;">Health Facility Name</td><td style="vertical-align: middle">'.ucwords($result[0]['facility_name']).'</td><td style="vertical-align: middle">Facility Code</td><td style="vertical-align: middle">'.ucwords($result[0]['facility_code']).'</td></tr>';
$html.='<tr style=""><td style="vertical-align: middle">Country</td><td style="vertical-align: middle">'.ucwords($result[0]['country']).'</td><td style="vertical-align: middle">State</td><td style="vertical-align: middle">'.ucwords($result[0]['state']).'</td></tr>';
$html.='<tr style=""><td style="vertical-align: middle">Hub Name</td><td colspan="3" style="vertical-align: middle">'.ucwords($result[0]['hub_name']).'</td></tr>';
$html.='<h4>Patient Details</h4>';
$html.='<tr><td style="vertical-align: middle">Sample Code</td><td style="vertical-align: middle">'.ucwords($result[0]['sample_code']).'</td><td style="vertical-align: middle">Unique Art No.</td><td style="vertical-align: middle">'.$result[0]['art_no'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle">Patient Name</td><td style="vertical-align: middle">'.ucwords($result[0]['patient_name']).'</td><td>Date of Birth</td><td style="vertical-align: middle">'.$result[0]['patient_dob'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle">Age in years</td><td style="vertical-align: middle">'.$result[0]['age_in_yrs'].'</td><td>Age in months</td><td style="vertical-align: middle">'.$result[0]['age_in_mnts'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle">Other Id</td><td style="vertical-align: middle">'.$result[0]['other_id'].'</td><td>Gender</td><td style="vertical-align: middle">'.ucwords($result[0]['gender']).'</td></tr>';
$html.='<tr><td style="vertical-align: middle">Phone Number</td><td colspan="3" style="vertical-align: middle">'.$result[0]['patient_phone_number'].'</td></tr>';
$html.='<h4>Sample Information</h4>';
$html.='<tr><td style="vertical-align: middle;">Sample Collected On</td><td style="vertical-align: middle;">'.$result[0]['sample_collection_date'].'</td><td style="vertical-align: middle;">Sample Type </td><td style="vertical-align: middle;">'.$sampleTypeResult[0]['sample_name'].'</td></tr>';
$html.='<h4>Treatment Information</h4>';
$html.='<tr><td style="vertical-align: middle;">How long has this patient been on treatment ?</td><td style="vertical-align: middle;">'.$result[0]['treatment_initiation'].'</td><td style="vertical-align: middle;">Treatment Initiatiated On </td><td style="vertical-align: middle;">'.$result[0]['treatment_initiated_date'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Current Regimen</td><td style="vertical-align: middle;">'.$aResult[0]['art_code'].'</td><td style="vertical-align: middle;">Current Regimen Initiated On </td><td style="vertical-align: middle;">'.$result[0]['date_of_initiation_of_current_regimen'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Which line of treatment is Patient on ?</td><td colspan="3" style="vertical-align: middle;">'.$result[0]['treatment_details'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Is Patient Pregnant ? </td><td style="vertical-align: middle;">'.ucwords($result[0]['is_patient_pregnant']).'</td><td style="vertical-align: middle;">If Pregnant, ARC No.</td><td style="vertical-align: middle;">'.$result[0]['arc_no'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Is Patient Breastfeeding ? </td><td style="vertical-align: middle;">'.ucwords($result[0]['is_patient_breastfeeding']).'</td><td style="vertical-align: middle;">ARV Adherence </td><td style="vertical-align: middle;">'.$result[0]['arv_adherence'].'</td></tr>';
$html.='<h4>Indication For Viral Load Testing</h4>';
$checked = '';
$display = '';
if($result[0]['routine_monitoring_last_vl_date']!='' || $result[0]['routine_monitoring_value']!='' || $result[0]['routine_monitoring_sample_type']!=''){
 $html.='<tr><td><strong>Routine Monitoring</strong></td><td colspan="3" style="vertical-align: middle;">Last VL Date &nbsp;&nbsp;&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; VL Value &nbsp;&nbsp;&nbsp;'.$result[0]['routine_monitoring_value'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Sample Type &nbsp;&nbsp;&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
}else if($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']!='' || $result[0]['vl_treatment_failure_adherence_counseling_value']!='' || $result[0]['vl_treatment_failure_adherence_counseling_sample_type']!=''){
 $html.='<tr><td><strong>Repeat VL test after suspected treatment failure adherence counseling</strong></td><td colspan="3" style="vertical-align: middle;">Last VL Date &nbsp;&nbsp;&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; VL Value &nbsp;&nbsp;&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Sample Type &nbsp;&nbsp;&nbsp;'.$rVlResult[0]['sample_name'].'</td></tr>';
}else if($result[0]['suspected_treatment_failure_last_vl_date']!='' || $result[0]['suspected_treatment_failure_value']!='' || $result[0]['suspected_treatment_failure_sample_type']!=''){
    $html.='<tr><td><strong>Suspect Treatment Failure</strong></td><td colspa="3" style="vertical-align: middle;">Last VL Date &nbsp;&nbsp;&nbsp;'.$result[0]['suspected_treatment_failure_last_vl_date'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; VL Value &nbsp;&nbsp;&nbsp;'.$result[0]['suspected_treatment_failure_value'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Sample Type &nbsp;&nbsp;&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
}
$html.='<tr style="padding-top: 40px;"><td style="vertical-align: middle;">Request Clinician </td><td style="vertical-align: middle;">'.$result[0]['request_clinician'].'</td><td style="vertical-align: middle;">Phone No. </td><td style="vertical-align: middle;">'.$result[0]['clinician_ph_no'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Request Date </td><td style="vertical-align: middle;">'.$result[0]['request_date'].'</td><td style="vertical-align: middle;">VL Focal Person </td><td style="vertical-align: middle;">'.$result[0]['vl_focal_person'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Phone Number </td><td>'.$result[0]['focal_person_phone_number'].'</td><td style="vertical-align: middle;">Email for HF </td><td style="vertical-align: middle;">'.$result[0]['email_for_HF'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Date sample received at testing Lab </td><td style="vertical-align: middle;">'.$result[0]['date_sample_received_at_testing_lab'].'</td><td style="vertical-align: middle;">Date Results Despatched </td><td style="vertical-align: middle;">'.$result[0]['date_results_dispatched'].'</td></tr>';
$html.='<tr><td style="vertical-align: middle;">Rejection </td><td colspan="3" style="vertical-align: middle;">'.ucwords($result[0]['rejection']).'</td></tr>';
$html.='</table>';
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-form-' . date('d-M-Y') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>