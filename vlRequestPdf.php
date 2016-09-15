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
        $this->SetFont('helvetica', 'B');
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
$pdf->SetFont('helvetica', 'I', 10);

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
    
$sTypeQuery="SELECT * FROM r_sample_type where form_identification='".$arr['vl_form']."'";
$sTypeResult = $db->rawQuery($sTypeQuery);

$fQuery="SELECT * from vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where treament_id=$id";
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

if(isset($result[0]['date_sample_received_at_testing_lab']) && trim($result[0]['date_sample_received_at_testing_lab'])!='' && $result[0]['date_sample_received_at_testing_lab']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['date_sample_received_at_testing_lab']);
 $result[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['date_sample_received_at_testing_lab']='';
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

//if(isset($result[0]['switch_to_tdf_last_vl_date']) && trim($result[0]['switch_to_tdf_last_vl_date'])!='' && trim($result[0]['switch_to_tdf_last_vl_date'])!='0000-00-00'){
// $result[0]['switch_to_tdf_last_vl_date']=$general->humanDateFormat($result[0]['switch_to_tdf_last_vl_date']);
//}else{
// $result[0]['switch_to_tdf_last_vl_date']='';
//}
//if(isset($result[0]['missing_last_vl_date']) && trim($result[0]['missing_last_vl_date'])!='' && trim($result[0]['missing_last_vl_date'])!='0000-00-00'){
// $result[0]['missing_last_vl_date']=$general->humanDateFormat($result[0]['missing_last_vl_date']);
//}else{
// $result[0]['missing_last_vl_date']='';
//}

if(isset($result[0]['sample_testing_date']) && trim($result[0]['sample_testing_date'])!='' && trim($result[0]['sample_testing_date'])!='0000-00-00'){
 $result[0]['sample_testing_date']=$general->humanDateFormat($result[0]['sample_testing_date']);
}else{
 $result[0]['sample_testing_date']='';
}

if(trim($result[0]['current_regimen'])!=''){
$aQuery="SELECT * from r_art_code_details where art_id=".$result[0]['current_regimen'];
$aResult=$db->query($aQuery);
}else{
    $aResult[0]['art_code'] = '';
}
if(trim($result[0]['sample_id'])!=''){
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
$rVlresult = $db->rawQuery($rVlQuery);
}else{
$rVlresult[0]['sample_name']= '';    
}
//Failure VL
if($result[0]['suspected_treatment_failure_sample_type']!=''){
$fVlQuery="SELECT * FROM r_sample_type where ".$result[0]['suspected_treatment_failure_sample_type'];
$fVlResult = $db->rawQuery($fVlQuery);
}else{
$fVlResult[0]['sample_name']     = '';
}
// Missing VL
if($result[0]['missing_sample_type']!=''){
$mVlQuery="SELECT * FROM r_sample_type where ".$result[0]['missing_sample_type'];
$mVlResult = $db->rawQuery($mVlQuery);
}else{
$mVlResult[0]['sample_name']     = '';
}
//Switch to tdf VL
if($result[0]['switch_to_tdf_sample_type']!=''){
$sVlQuery="SELECT * FROM r_sample_type where ".$result[0]['suspected_treatment_failure_sample_type'];
$sVlResult = $db->rawQuery($sVlQuery);
}else{
$sVlResult[0]['sample_name']     = '';
}

//rejection facility and reason
$rejectionfQuery="SELECT * FROM facility_details where facility_id='".$result[0]['sample_rejection_facility']."'";
$rejectionfResult = $db->rawQuery($rejectionfQuery);

$rejectionrQuery="SELECT * FROM r_sample_rejection_reasons where rejection_reason_id='".$result[0]['sample_rejection_reason']."'";
$rejectionrResult = $db->rawQuery($rejectionrQuery);

    //set sample type
    $div = '';
    foreach($sTypeResult as $sType){
     if($result[0]['sample_id']==$sType['sample_id'])
     {
      $div .= '<input type="checkbox" name="check[]" id="name'.$sType['sample_id'].'" value="'.$sType['sample_name'].'" checked="checked" readonly="true"/>&nbsp;'.$sType['sample_name'];
     }else{
      $div .= '<input type="checkbox" name="check[]" id="name'.$sType['sample_id'].'" value="'.$sType['sample_name'].'" readonly="true"/>&nbsp;'.$sType['sample_name'];
     }
     
    }
    //check urgency
    if($result[0]['urgency']=='normal'){
    $urgency = '<td>:&nbsp;<input type="radio" name="urgency" value="normal" checked="checked"  readonly="true"/>Normal&nbsp;<input type="radio" name="urgency" value="urgent"  readonly="true"/>Urgent</td>';
    }else if($result[0]['urgency']=='urgent'){
     $urgency = '<td>:&nbsp;<input type="radio" name="urgency" value="normal"  readonly="true"/>Normal&nbsp;<input type="radio" name="urgency" value="urgent" checked="checked"  readonly="true"/>Urgent</td>';
    }else{
     $urgency = '<td>:&nbsp;<input type="radio" name="urgency" value="normal"  readonly="true"/>Normal&nbsp;<input type="radio" name="urgency" value="urgent"  readonly="true"/>Urgent</td>';
    }
    //check gender
    if($result[0]['gender']=='male'){
    $gender = '<td>:&nbsp;<input type="radio" name="gender" value="male" checked="checked"  readonly="true"/>Male&nbsp;<input type="radio" name="gender" value="female"  readonly="true"/>Female</td>';
    }else if($result[0]['gender']=='female'){
     $gender = '<td>:&nbsp;<input type="radio" name="gender" value="male"  readonly="true"/>Male&nbsp;<input type="radio" name="gender" value="female" checked="checked"  readonly="true"/>Female</td>';
    }else{
     $gender = '<td>:&nbsp;<input type="radio" name="gender" value="male"  readonly="true"/>Male&nbsp;<input type="radio" name="gender" value="female"  readonly="true"/>Female</td>';
    }
    if($result[0]['is_patient_pregnant']=='yes'){
    $prg = '<td>:&nbsp;<input type="radio" name="pregnant" value="yes" checked="checked"  readonly="true"/>Yes&nbsp;<input type="radio" name="pregnant" value="no" readonly="true"/>No</td>';
    }else if($result[0]['is_patient_pregnant']=='no'){
     $prg = '<td>:&nbsp;<input type="radio" name="pregnant" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="pregnant" value="no" checked="checked" readonly="true"/>No</td>';
    }else{
     $prg = '<td>:&nbsp;<input type="radio" name="pregnant" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="pregnant" value="no" readonly="true"/>No</td>';
    }
    if($result[0]['is_patient_breastfeeding']=='yes'){
    $breast = '<td>:&nbsp;<input type="radio" name="breast" value="yes" checked="checked" readonly="true"/>Yes&nbsp;<input type="radio" name="breast" value="no" readonly="true"/>No</td>';
    }else if($result[0]['is_patient_breastfeeding']=='no'){
     $breast = '<td>:&nbsp;<input type="radio" name="breast" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="breast" value="no" checked="checked" readonly="true"/>No</td>';
    }else{
     $breast = '<td>:&nbsp;<input type="radio" name="breast" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="breast" value="no" readonly="true"/>No</td>';
    }
    if($result[0]['patient_receive_sms']=='yes'){
    $sms = '<td>:&nbsp;<input type="radio" name="sms" value="yes" checked="checked" readonly="true"/>Yes&nbsp;<input type="radio" name="sms" value="no" readonly="true"/>No</td>';
    }else if($result[0]['patient_receive_sms']=='no'){
     $sms = '<td>:&nbsp;<input type="radio" name="breast" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="sms" value="no" checked="checked" readonly="true"/>No</td>';
    }else{
     $sms = '<td>:&nbsp;<input type="radio" name="breast" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="sms" value="no" readonly="true"/>No</td>';
    }
    if($result[0]['rejection']=='yes'){
    $reject = '<td>:&nbsp;<input type="radio" name="reject" value="yes" checked="checked" readonly="true"/>Yes&nbsp;<input type="radio" name="reject" value="no" readonly="true"/>No</td>';
    }else if($result[0]['rejection']=='no'){
     $reject = '<td>:&nbsp;<input type="radio" name="reject" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="reject" value="no" checked="checked" readonly="true"/>No</td>';
    }else{
     $reject = '<td>:&nbsp;<input type="radio" name="reject" value="yes" readonly="true"/>Yes&nbsp;<input type="radio" name="reject" value="no" readonly="true"/>No</td>';
    }
    
        $html = '';
        $html.='<table style="padding:5px;border:2px solid #333;">';
        $html .='<tr>';
        if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
         $html .='<td style="text-align:center;border-right:2px solid #333;padding:3px 0px 3px 0px;"><img src="uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;" alt="logo"></td>';
        }
        if(isset($arr['header']) && trim($arr['header'])!= '') {
            $html .='<td colspan="2" style="text-align:center;font-size:15px;border-right:2px solid #333;font-weight:bold;padding:3px 0px 3px 0px;">'.ucwords($arr['header']).'</td>';
        }
        $html.='<td style="text-align:center;font-size:14px;">'.$result[0]['art_no'].'</td>';
        $html .='</tr>';
        $html.='</table><br/><br/>';
        $html.='<table><tr><td><h4>Facility Details</h4>';
        $html.='<table style="padding:5px;width:98%;border:2px solid #333;">';
         $html.='<tr>';
          $html.='<td>Facility:'.ucwords($result[0]['facility_name']).'&nbsp;State:'.ucwords($result[0]['state']).'</td>';
         $html.='</tr>';
         $html.='<tr>';
          $html.='<td>Hub:'.ucwords($result[0]['hub_name']).'&nbsp;District:'.ucwords($result[0]['district']).'</td>';
         $html.='</tr>';
         $html.='<tr>';
          $html.='<td>Urgency'.ucwords($urgency).'</td>';
         $html.='</tr>';
        $html.='</table></td>';
        $html.='<td><h4>Sample Details</h4><table style="padding:5px;border:2px solid #333;">';
         $html.='<tr style="width:98%;">';
          $html.='<td>Date Of Sample Collection:'.$result[0]['sample_collection_date'].'</td>';
         $html.='</tr>';
         $html.='<tr style="width:98%;">';
          $html.='<td>Sample Received Date:'.$result[0]['date_sample_received_at_testing_lab'].'</td>';
         $html.='</tr>';
         $html.='<tr>';
          $html.='<td>Sample Type:'.$div.'</td>';
         $html.='</tr>';
        $html.='</table></td></tr>';
        $html.='</table>';
        $html.='<h4>Patient Details</h4>';
        $html.='<table style="padding:5px;border:2px solid #333;">';
        $html.='<tr><td>Unique ART No.</td><td>:&nbsp;'.$result[0]['art_no'].'</td><td>Sample Code</td><td>:&nbsp;'.$result[0]['sample_code'].'</td></tr>';
        $html.='<tr><td>Other Id</td><td>:&nbsp;'.$result[0]['other_id'].'</td><td>Patient Name</td><td>:&nbsp;'.$result[0]['patient_name'].'</td></tr>';
        $html.='<tr><td>Date Of Birth</td><td>:&nbsp;'.$result[0]['patient_dob'].'</td><td>Gender</td>'.$gender.'</tr>';
        $html.='<tr><td>Age In years</td><td>:&nbsp;'.$result[0]['age_in_yrs'].'</td><td>Age In Month</td><td>:&nbsp;'.$result[0]['age_in_mnts'].'</td></tr>';
        $html.='<tr><td>Patient consent to receive SMS?</td><td>&nbsp;'.$sms.'</td><td>Ph Number</td><td>:&nbsp;'.$result[0]['patient_phone_number'].'</td></tr>';
        $html.='<tr><td>Location</td><td colspan="3">:&nbsp;'.$result[0]['location'].'</td></tr>';
        $html.='<tr><td>Request Clinician</td><td>:&nbsp;'.$result[0]['request_clinician'].'</td><td>Phone No.</td><td>:&nbsp;'.$result[0]['clinician_ph_no'].'</td></tr>';
        $html.='<tr><td>Request Date</td><td>:&nbsp;'.$result[0]['sample_testing_date'].'</td><td>VL Focal Person</td><td>:&nbsp;'.$result[0]['vl_focal_person'].'</td></tr>';
        $html.='<tr><td>Phone Number</td><td>:&nbsp;'.$result[0]['focal_person_phone_number'].'</td><td>Email for HF</td><td>:&nbsp;'.$result[0]['email_for_HF'].'</td></tr>';
        $html.='<tr><td>Justification</td><td>:&nbsp;'.$result[0]['justification'].'</td><td>Rejection</td><td>&nbsp;'.$reject.'</td></tr>';
        $html.='<tr><td>Rejection Facility</td><td>:&nbsp;'.ucwords($rejectionfResult[0]['facility_name']).'</td><td>Rejection Reason</td><td>:&nbsp;'.ucwords($rejectionrResult[0]['rejection_reason_name']).'</td></tr>';
        $html.='</table>';
        $html.='<h4>Treatment Details</h4>';
        $html.='<table style="padding:5px;border:2px solid #333;">';
        $html.='<tr><td>How long has this patient been on treatment ?</td><td>:&nbsp;'.$result[0]['treatment_initiation'].'</td><td>Treatment Initiated On</td><td>:&nbsp;'.$result[0]['treatment_initiated_date'].'</td></tr>';
        $html.='<tr><td>Current Regimen</td><td>:&nbsp;'.$aResult[0]['art_code'].'</td><td>Current Regimen Initiated On</td><td>:&nbsp;'.$result[0]['date_of_initiation_of_current_regimen'].'</td></tr>';
        $html.='<tr><td>Which line of treatment is Patient on ?</td><td colspan="3">:&nbsp;'.$result[0]['treatment_details'].'</td></tr>';
        $html.='<tr><td>Is Patient Pregnant ?</td><td>'.$prg.'</td><td>If Pregnant, ARC No.</td><td>:&nbsp;'.$result[0]['arc_no'].'</td></tr>';
        //$html.='<tr><td>Is Patient Breastfeeding?</td>'.$breast.'<td>ARV Adherence</td><td>:&nbsp;'.$result[0]['arv_adherence'].'</td></tr>';
        $html.='<tr><td>Is Patient Breastfeeding?</td><td colspan="3">'.$breast.'</td></tr>';
        $html.='</table><br/><br/><br/><br/>';
        $html.='<h4>Indication For Viral Load Testing</h4>';
        $html.='<table style="padding:5px;border:2px solid #333;">';
        if($result[0]['viral_load_indication']=='routine'){
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="1" checked="checked" readonly="true"/>Routine Monitoring</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="2" readonly="true"/>Repeat VL test after suspected treatment failure adherence counseling</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rVlresult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="3" readonly="true"/>Suspect Treatment Failure</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['suspected_treatment_failure_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['suspected_treatment_failure_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="4" readonly="true" />Switch to TDF</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="5" readonly="true"/>Missing</td></tr>';
        }else if($result[0]['viral_load_indication']=='failure'){
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="1" readonly="true"/>Routine Monitoring</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="2" readonly="true" checked="checked"/>Repeat VL test after suspected treatment failure adherence counseling</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rVlresult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="3" readonly="true"/>Suspect Treatment Failure</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['suspected_treatment_failure_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['suspected_treatment_failure_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="4" readonly="true" />Switch to TDF</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="5" readonly="true"/>Missing</td></tr>';
        }else if($result[0]['viral_load_indication']=='suspect'){
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="1" readonly="true"/>Routine Monitoring</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="2" readonly="true" />Repeat VL test after suspected treatment failure adherence counseling</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rVlresult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="3" readonly="true" checked="checked"/>Suspect Treatment Failure</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['suspected_treatment_failure_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['suspected_treatment_failure_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="4" readonly="true" />Switch to TDF</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="5" readonly="true"/>Missing</td></tr>';
        }else if($result[0]['viral_load_indication']=='switch'){
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="1" readonly="true"/>Routine Monitoring</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="2" readonly="true" />Repeat VL test after suspected treatment failure adherence counseling</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rVlresult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="3" readonly="true"/>Suspect Treatment Failure</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="4" readonly="true" checked="checked"/>Switch to TDF</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="5" readonly="true"/>Missing</td></tr>';
        }else if($result[0]['viral_load_indication']=='missing'){
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="1" readonly="true"/>Routine Monitoring</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="2" readonly="true" />Repeat VL test after suspected treatment failure adherence counseling</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rVlresult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="3" readonly="true"/>Suspect Treatment Failure</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="4" readonly="true" />Switch to TDF</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="5" readonly="true" checked="checked"/>Missing</td></tr>';
        }else{
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="1" readonly="true"/>Routine Monitoring</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['routine_monitoring_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rtResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="2" readonly="true"/>Repeat VL test after suspected treatment failure adherence counseling</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$rVlresult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="3" readonly="true"/>Suspect Treatment Failure</td></tr><tr><td>Last VL Date &nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'].'</td><td>VL Value&nbsp;&nbsp;:&nbsp;'.$result[0]['vl_treatment_failure_adherence_counseling_value'].'</td><td>Sample Type&nbsp;&nbsp;:&nbsp;'.$fVlResult[0]['sample_name'].'</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="4" readonly="true" />Switch to TDF</td></tr>';
         $html.='<tr><td colspan="3"><input type="checkbox" name="routine" value="5" readonly="true" />Missing</td></tr>';
        }
        $html.='<tr><td colspan="2">ARV Adherence &nbsp;&nbsp;:&nbsp;&nbsp;'.ucwords($result[0]['arv_adherence']).'</td><td colspan="1">Enhance Session &nbsp;&nbsp;:&nbsp;&nbsp;'.ucwords($result[0]['enhance_session']).'</td></tr>';
        $html.='</table>';
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-form-' . date('d-M-Y-H-i-s') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>