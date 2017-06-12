<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include ('../includes/tcpdf/tcpdf.php');
include ('../includes/fpdi/fpdi.php');
include('../General.php');
define('UPLOAD_PATH','../uploads');
$general=new Deforay_Commons_General();
$id=base64_decode($_POST['id']);
$pages = array();
  if($id >0){
    if (!file_exists(UPLOAD_PATH. DIRECTORY_SEPARATOR . "qrcode") && !is_dir(UPLOAD_PATH. DIRECTORY_SEPARATOR."qrcode")) {
        mkdir(UPLOAD_PATH. DIRECTORY_SEPARATOR."qrcode");
    }
    $configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    $country = $arr['vl_form'];
    if(isset($arr['default_time_zone']) && $arr['default_time_zone']!=''){
      date_default_timezone_set($arr['default_time_zone']);
    }else{
      date_default_timezone_set("Europe/London");
    }
    //vl instance id
    $vlInstanceQuery ="SELECT vlsm_instance_id FROM vl_instance";
    $vlInstanceResult = $db->rawQuery($vlInstanceQuery);
    $vlInstanceId = (isset($vlInstanceResult[0]['vlsm_instance_id']))?$vlInstanceResult[0]['vlsm_instance_id']:'';
    //main query
    $sQuery="SELECT vl.*,f.*,ts.*,s.*,b.batch_code,rby.user_name as resultReviewedBy,aby.user_name as resultApprovedBy,cby.user_name as requestCreatedBy,lmby.user_name as lastModifiedBy,r_f.facility_name as rejectionFacility,r_r_r.rejection_reason_name as rejectionReason,r_s_r.sample_name as routineSampleType,r_s_ac.sample_name as acSampleType,r_s_f.sample_name as failureSampleType,form.form_name from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as rby ON rby.user_id = vl.result_reviewed_by LEFT JOIN user_details as aby ON aby.user_id = vl.result_approved_by LEFT JOIN user_details as cby ON cby.user_id = vl.request_created_by LEFT JOIN user_details as lmby ON lmby.user_id = vl.last_modified_by LEFT JOIN facility_details as r_f ON r_f.facility_id = vl.sample_rejection_facility LEFT JOIN r_sample_rejection_reasons as r_r_r ON r_r_r.rejection_reason_id = vl.reason_for_sample_rejection LEFT JOIN r_sample_type as r_s_r ON r_s_r.sample_id = vl.last_vl_sample_type_routine LEFT JOIN r_sample_type as r_s_ac ON r_s_ac.sample_id = vl.last_vl_sample_type_failure_ac LEFT JOIN r_sample_type as r_s_f ON r_s_f.sample_id = vl.last_vl_sample_type_failure LEFT JOIN form_details as form ON form.vlsm_country_id = vl.vlsm_country_id WHERE vl.vlsm_country_id = $country AND vl.sample_batch_id=$id";
    $sResult=$db->query($sQuery);
    if(count($sResult) > 0){
        $_SESSION['nbPages'] = sizeof($sResult);
        $_SESSION['aliasPage'] = 1;
        //header and footer
        class MYPDF extends TCPDF {
          //Page header
          public function setHeading($logo,$text,$lab) {
            $this->logo = $logo;
            $this->text = $text;
            $this->lab = $lab;
          }
          //Page header
          public function Header() {
              // Logo
              //$image_file = K_PATH_IMAGES.'logo_example.jpg';
              //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
              // Set font
              if(trim($this->logo)!=''){
                  if (file_exists('../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo)) {
                    $image_file = '../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo;
                    $this->Image($image_file,20, 13, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                  }
              }
              $this->SetFont('helvetica', 'B', 7);
              $this->writeHTMLCell(30,0,16,28,$this->text, 0, 0, 0, true, 'A', true);
              $this->SetFont('helvetica', '', 18);
              $this->writeHTMLCell(0,0,10,18,'VIRAL LOAD TEST RESULT', 0, 0, 0, true, 'C', true);
              if(trim($this->lab)!= ''){
                $this->SetFont('helvetica', '', 9);
                $this->writeHTMLCell(0,0,10,26,strtoupper($this->lab), 0, 0, 0, true, 'C', true);
              }
              $this->writeHTMLCell(0,0,15,36,'<hr>', 0, 0, 0, true, 'C', true);
          }
      
          // Page footer
          public function Footer() {
              // Position at 15 mm from bottom
              $this->SetY(-15);
              // Set font
              $this->SetFont('helvetica', '', 8);
              // Page number
              $this->Cell(0, 10, 'Page'.$_SESSION['aliasPage'].'/'.$_SESSION['nbPages'], 0, false, 'C', 0, '', 0,false, 'T', 'M');
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
        $_SESSION['rVal'] = $general->generateRandomString(6);
        if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal'])) {
          mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']);
        }
        $pathFront = realpath('../uploads/'.$_SESSION['rVal'].'/');
        $page = 1;
        $qrText = array();
        foreach($sResult as $result){
            $_SESSION['aliasPage'] = $page;
            // create new PDF document
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT,true, 'UTF-8', false);
            $pdf->setHeading($arr['logo'],$arr['header'],(isset($result['labName']))?$result['labName']:'');
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            //$pdf->SetAuthor('Pal');
            $pdf->SetTitle('Viral Load Test Request');
            //$pdf->SetSubject('TCPDF Tutorial');
            //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
    
            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,PDF_HEADER_TITLE, PDF_HEADER_STRING);
    
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '',PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '',PDF_FONT_SIZE_DATA));
    
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP+14,PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }
    
            // ---------------------------------------------------------
    
            // set font
            $pdf->SetFont('helvetica', '', 18);
    
            $pdf->AddPage();
            //sample reorder
            $sampleReorderChecked = '';
            if(trim($result["sample_reordered"]) == "yes"){
              $sampleReorderChecked = "checked='checked'";
            }
            //patient DOB
            if(isset($result['patient_dob']) && trim($result['patient_dob'])!='' && $result['patient_dob']!='0000-00-00'){
              $patientDob=$general->humanDateFormat($result['patient_dob']);
            }else{
              $patientDob='';
            }
            //sample collection date
            if(isset($result['sample_collection_date']) && trim($result['sample_collection_date'])!='' && $result['sample_collection_date']!='0000-00-00 00:00:00'){
              $expStr=explode(" ",$result['sample_collection_date']);
              $result['sample_collection_date']=$general->humanDateFormat($expStr[0]);
              $sampleCollectionTime = $expStr[1];
            }else{
              $result['sample_collection_date']='';
              $sampleCollectionTime = '';
            }
            //treatment initiated date
            if(isset($result['treatment_initiated_date']) && trim($result['treatment_initiated_date'])!='' && $result['treatment_initiated_date']!='0000-00-00'){
              $result['treatment_initiated_date']=$general->humanDateFormat($result['treatment_initiated_date']);
            }else{
              $result['treatment_initiated_date']='';
            }
            //date of initiation current regimen
            if(isset($result['date_of_initiation_of_current_regimen']) && trim($result['date_of_initiation_of_current_regimen'])!='' && $result['date_of_initiation_of_current_regimen']!='0000-00-00'){
              $result['date_of_initiation_of_current_regimen']=$general->humanDateFormat($result['date_of_initiation_of_current_regimen']);
            }else{
              $result['date_of_initiation_of_current_regimen']='';
            }
            //sample received datetime
            if(isset($result['sample_received_at_vl_lab_datetime']) && trim($result['sample_received_at_vl_lab_datetime'])!='' && $result['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
              $expStr=explode(" ",$result['sample_received_at_vl_lab_datetime']);
              $result['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
            }else{
              $result['sample_received_at_vl_lab_datetime']='';
            }
            //sample tested datetime
            if(isset($result['sample_tested_datetime']) && trim($result['sample_tested_datetime'])!='' && $result['sample_tested_datetime']!='0000-00-00 00:00:00'){
            $expStr=explode(" ",$result['sample_tested_datetime']);
            $result['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
            }else{
            $result['sample_tested_datetime']='';
            }
            //result dispatch datetime
            if(isset($result['result_dispatched_datetime']) && trim($result['result_dispatched_datetime'])!='' && $result['result_dispatched_datetime']!='0000-00-00 00:00:00'){
              $expStr=explode(" ",$result['result_dispatched_datetime']);
              $result['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
            }else{
              $result['result_dispatched_datetime']='';
            }
            //test request date
            if(isset($result['test_requested_on']) && trim($result['test_requested_on'])!='' && $result['test_requested_on']!='0000-00-00'){
              $result['test_requested_on']=$general->humanDateFormat($result['test_requested_on']);
            }else{
              $result['test_requested_on']='';
            }
            
            $html = '';
            $html.='<table style="padding:0px 2px 2px 2px;">';
              $html .='<tr>';
               $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Clinic Information: (To be filled by requesting Clinican/Nurse)</h3><hr/></th>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE ID</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><input type="checkbox" name="agree" value="1" checked="checked" />SAMPLE Reordered</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Province</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['sample_code'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['facility_state'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">District</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Clinic/Health Center</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Clinic/Health Center Code</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['facility_district']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['facility_name']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['facility_code'].'</td>';
              $html .='</tr>';
              $html.='</table>';
              
            $html.='<table style="padding:0px 2px 2px 2px;">';
              $html .='<tr>';
               $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Patient Information</h3><hr/></th>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">ART (TRACNET) No. </td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Birth</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">If DOB unknown,Age in Year</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['patient_art_no'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$patientDob.'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['patient_age_in_years'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">If Age < 1, Age in Month</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Patient Name</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Gender</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['patient_age_in_months'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['patient_first_name']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['patient_gender']).'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Phone Number</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="3" style="line-height:11px;font-size:11px;text-align:left;">'.$result['patient_mobile_number'].'</td>';
              $html .='</tr>';
              $html.='</table>';
              
            $html.='<table style="padding:0px 2px 2px 2px;">';
              $html .='<tr>';
               $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Sample Information</h3><hr/></th>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Sample Collection  </td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Type</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['sample_collection_date']." ".$sampleCollectionTime.'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['sample_name'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
              $html .='</tr>';
            $html.='</table>';
            
            $html.='<table style="padding:0px 2px 2px 2px;">';
              $html .='<tr>';
               $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Treatment Information</h3><hr/></th>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Treatment Initiation </td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Current Regimen</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of Initiation of Current Regimen</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['treatment_initiated_date'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['current_regimen'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['date_of_initiation_of_current_regimen'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">ARV Adherence</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Is Patient Pregnant?</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Is Patient Breastfeeding?</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['arv_adherance_percentage']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['is_patient_pregnant'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['is_patient_breastfeeding'].'</td>';
              $html .='</tr>';
              $html.='</table>';
            $html.='<table style="padding:0px 2px 2px 2px;">';
              $html .='<tr>';
               $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Indication for Viral Load Testing (Please tick one):<small>(To be completed by clinician)</small></h3><hr/></th>';
              $html .='</tr>';
              if($result['reason_for_vl_testing']=='routine')
              {
                $html .='<tr>';
                  $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Routine Monitoring</h4></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of last viral load test </td>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Value(copies/ml)</td>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$general->humanDateFormat($result['last_vl_date_routine']).'</td>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['last_vl_result_routine'].'</td>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
                $html .='</tr>';
              }
              else if($result['reason_for_vl_testing']=='failure')
              {
                $html .='<tr>';
                  $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Repeat VL test after suspected treatment failure adherence counselling</h4></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of last viral load test </td>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Value(copies/ml)</td>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$general->humanDateFormat($result['last_vl_date_failure_ac']).'</td>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['last_vl_result_failure_ac'].'</td>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
                $html .='</tr>';
              }
              else if($result['reason_for_vl_testing']=='suspect')
              {
                $html .='<tr>';
                  $html .='<td colspan="3" style="line-height:11px;font-size:12px;font-weight:bold;text-align:left;">Suspect Treatment Failure</td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date of last viral load test </td>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Value(copies/ml)</td>';
                  $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$general->humanDateFormat($result['last_vl_date_failure']).'</td>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['last_vl_result_failure'].'</td>';
                  $html .='<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
                $html .='</tr>';
              }else{
                $html .='<tr>';
                  $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Routine Monitoring</h4></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Repeat VL test after suspected treatment failure adherence counselling</h4></td>';
                $html .='</tr>';
                $html .='<tr>';
                  $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h4>Suspect Treatment Failure</h4></td>';
                $html .='</tr>';
              }
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Request Clinician </td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Phone Number</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Request Date</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['request_clinician_name']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['request_clinician_phone_number'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['test_requested_on'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Focal Person</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Focal Person Phone Number</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Email for HF</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['vl_focal_person']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['vl_focal_person_phone_number'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['facility_emails'].'</td>';
              $html .='</tr>';
              $html.='</table>';
            
            $html.='<table style="padding:0px 2px 2px 2px;">';
              $html .='<tr>';
               $html .='<th colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"><h3>Laboratory Information</h3><hr/></th>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Lab Name </td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">VL Testing Platform</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date Sample Received at Testing Lab</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['facility_name']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['import_machine_name']).'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['sample_received_at_vl_lab_datetime'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Testing Date</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Date Results Dispatched</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Sample Rejection</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['sample_tested_datetime'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['result_dispatched_datetime'].'</td>';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['is_sample_rejected']).'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $approveByColspan = '';
              $html .='<tr>';
              if($result['is_sample_rejected']=='no' || $result['is_sample_rejected']==''){
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Viral Load Result (copiesl/ml)</td>';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Target Not Detected</td>';
              }else{
                $approveByColspan = '2';
               $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Rejection Reason</td>'; 
              }  
               $html .='<td colspan='.$approveByColspan.' style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Approved By</td>';
              $html .='</tr>';
              $html .='<tr>';
              if($result['is_sample_rejected']=='no' || $result['is_sample_rejected']==''){
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['result_value_absolute'].'</td>';
                $targetNotDetected = '';
                if($result['result'] == 'Target Not Detected'){
                  $targetNotDetected = 'Yes';
                }
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$targetNotDetected.'</td>';
              }else{
                $approveByColspan = '2';
                $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result['rejection_reason_name'].'</td>';
              }
                $html .='<td colspan='.$approveByColspan.' style="line-height:11px;font-size:11px;text-align:left;">'.ucwords($result['requestCreatedBy']).'</td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:10px;"></td>';
              $html .='</tr>';
              $html .='<tr>';
               $html .='<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Laboratory Scientist Comments</td>';
              $html .='</tr>';
              $html .='<tr>';
                $html .='<td colspan="3" style="line-height:11px;font-size:11px;text-align:left;">'.$result['approver_comments'].'</td>';
              $html .='</tr>';
              $html.='</table>';
              
            if($result['result']!=''){
              $pdf->writeHTML($html);
              $pdf->lastPage();
              $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
              $pdf->Output($filename,"F");
              $pages[] = $filename;
              $page++;
            }
        }
    }
  }
  $resultFilename = '';
  if(count($pages) >0){
    $resultPdf = new Pdf_concat();
    $resultPdf->setFiles($pages);
    $resultPdf->setPrintHeader(false);
    $resultPdf->setPrintFooter(false);
    $resultPdf->concat();
    $resultFilename = 'vl-qrcode-country-form-' . date('d-M-Y-H-i-s') .'.pdf';
    $resultPdf->Output(UPLOAD_PATH. DIRECTORY_SEPARATOR. "qrcode" . DIRECTORY_SEPARATOR. $resultFilename, "F");
    $general->removeDirectory($pathFront);
 }
echo $resultFilename;