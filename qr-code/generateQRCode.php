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
    $sQuery="SELECT vl.*,f.*,ts.*,s.*,l.facility_name as labName,b.batch_code,rby.user_name as resultReviewedBy,aby.user_name as resultApprovedBy,cby.user_name as requestCreatedBy,lmby.user_name as lastModifiedBy,r_f.facility_name as rejectionFacility,r_r_r.rejection_reason_name as rejectionReason,r_s_r.sample_name as routineSampleType,r_s_ac.sample_name as acSampleType,r_s_f.sample_name as failureSampleType,form.form_name from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as rby ON rby.user_id = vl.result_reviewed_by LEFT JOIN user_details as aby ON aby.user_id = vl.result_approved_by LEFT JOIN user_details as cby ON cby.user_id = vl.request_created_by LEFT JOIN user_details as lmby ON lmby.user_id = vl.last_modified_by LEFT JOIN facility_details as r_f ON r_f.facility_id = vl.sample_rejection_facility LEFT JOIN r_sample_rejection_reasons as r_r_r ON r_r_r.rejection_reason_id = vl.reason_for_sample_rejection LEFT JOIN r_sample_type as r_s_r ON r_s_r.sample_id = vl.last_vl_sample_type_routine LEFT JOIN r_sample_type as r_s_ac ON r_s_ac.sample_id = vl.last_vl_sample_type_failure_ac LEFT JOIN r_sample_type as r_s_f ON r_s_f.sample_id = vl.last_vl_sample_type_failure LEFT JOIN form_details as form ON form.vlsm_country_id = vl.vlsm_country_id LEFT JOIN facility_details as l ON l.facility_id=vl.lab_id WHERE vl.vlsm_country_id = $country AND vl.sample_batch_id=$id";
    $sResult=$db->query($sQuery);
    if(count($sResult) > 0){
        $_SESSION['nbPages'] = sizeof($sResult) * 2;
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
                $this->writeHTMLCell(0,0,10,18,'VIRAL LOAD TEST REQUEST', 0, 0, 0, true, 'C', true);
                if(trim($this->lab)!= ''){
                  $this->SetFont('helvetica', '', 9);
                  $this->writeHTMLCell(0,0,10,26,strtoupper($this->lab), 0, 0, 0, true, 'C', true);
                }
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
        foreach($sResult as $vl){
            $_SESSION['aliasPage'] = $page;
            // create new PDF document
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT,true, 'UTF-8', false);
            $pdf->setHeading($arr['logo'],$arr['header'],(isset($vl['labName']))?$vl['labName']:'');
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
            $fResult = array();
            if(trim($vl['lab_id'])!= '' && $vl['lab_id']!= null && $vl['lab_id'] >0){
              $fQuery="SELECT * FROM facility_details WHERE facility_type ='2' AND facility_id='".$vl['lab_id']."'";
              $fResult = $db->query($fQuery);
            }
            $routineSampleType = (isset($vl['routineSampleType']))?$vl['routineSampleType']:'';
            $acSampleType = (isset($vl['acSampleType']))?$vl['acSampleType']:'';
            $failureSampleType = (isset($vl['failureSampleType']))?$vl['failureSampleType']:'';
            $sampleType = (isset($vl['sample_name']))?$vl['sample_name']:'';
            $rejectionFacility = (isset($vl['rejectionFacility']))?$vl['rejectionFacility']:'';
            $rejectionReason = (isset($vl['rejectionReason']))?$vl['rejectionReason']:'';
            $batchCode = (isset($vl['batch_code']))?$vl['batch_code']:'';
            $resultReviewedBy = (isset($vl['resultReviewedBy']))?$vl['resultReviewedBy']:'';
            $resultApprovedBy = (isset($vl['resultApprovedBy']))?$vl['resultApprovedBy']:'';
            $requestCreatedBy = (isset($vl['requestCreatedBy']))?$vl['requestCreatedBy']:'';
            $lastModifiedBy = (isset($vl['lastModifiedBy']))?$vl['lastModifiedBy']:'';
            $vlCountry = (isset($vl['form_name']))?$vl['form_name']:$country;
            $qrText[] = $vl['facility_code'];
            $qrText[] = $vl['facility_name'];
            $qrText[] = $vl['facility_state'];
            $qrText[] = $vl['facility_district'];
            $qrText[] = $vl['facility_hub_name'];
            $qrText[] = $vl['facility_sample_id'];
            $qrText[] = $vl['request_clinician_name'];
            $qrText[] = $vl['request_clinician_phone_number'];
            $qrText[] = $vl['facility_support_partner'];
            $qrText[] = $vl['physician_name'];
            $qrText[] = $vl['date_test_ordered_by_physician'];
            $qrText[] = $vl['sample_collection_date'];
            $qrText[] = $vl['sample_collected_by'];
            $qrText[] = $vl['facility_mobile_numbers'];
            $qrText[] = $vl['facility_emails'];
            $qrText[] = $vl['test_urgency'];
            $qrText[] = $vl['patient_art_no'];
            $qrText[] = $vl['patient_anc_no'];
            $qrText[] = $vl['patient_nationality'];
            $qrText[] = $vl['patient_other_id'];
            $qrText[] = $vl['patient_first_name'];
            $qrText[] = $vl['patient_last_name'];
            $qrText[] = $vl['patient_dob'];
            $qrText[] = $vl['patient_gender'];
            $qrText[] = $vl['patient_age_in_years'];
            $qrText[] = $vl['patient_age_in_months'];
            $qrText[] = $vl['consent_to_receive_sms'];
            $qrText[] = $vl['patient_mobile_number'];
            $qrText[] = $vl['patient_location'];
            $qrText[] = $vl['vl_focal_person'];
            $qrText[] = $vl['vl_focal_person_phone_number'];
            $qrText[] = $vl['patient_address'];
            $qrText[] = $vl['is_patient_new'];
            $qrText[] = $vl['patient_art_date'];
            $qrText[] = $vl['reason_for_vl_testing'];
            $qrText[] = $vl['is_patient_pregnant'];
            $qrText[] = $vl['is_patient_breastfeeding'];
            $qrText[] = $vl['pregnancy_trimester'];
            $qrText[] = $vl['date_of_initiation_of_current_regimen'];
            $qrText[] = $vl['last_vl_date_routine'];
            $qrText[] = $vl['last_vl_result_routine'];
            $qrText[] = $routineSampleType;
            $qrText[] = $vl['last_vl_date_failure_ac'];
            $qrText[] = $vl['last_vl_result_failure_ac'];
            $qrText[] = $acSampleType;
            $qrText[] = $vl['last_vl_date_failure'];
            $qrText[] = $vl['last_vl_result_failure'];
            $qrText[] = $failureSampleType;
            $qrText[] = $vl['has_patient_changed_regimen'];
            $qrText[] = $vl['reason_for_regimen_change'];
            $qrText[] = $vl['regimen_change_date'];
            $qrText[] = $vl['arv_adherance_percentage'];
            $qrText[] = $vl['is_adherance_poor'];
            $qrText[] = $vl['last_vl_result_in_log'];
            $qrText[] = $vl['vl_test_number'];
            $qrText[] = $vl['number_of_enhanced_sessions'];
            $qrText[] = $vl['sample_code'];
            $qrText[] = $sampleType;
            $qrText[] = $vl['is_sample_rejected'];
            $qrText[] = $rejectionFacility;
            $qrText[] = $rejectionReason;
            $qrText[] = $vl['plasma_conservation_temperature'];
            $qrText[] = $vl['plasma_conservation_duration'];
            $qrText[] = $vl['vl_test_platform'];
            $qrText[] = $vl['status_name'];
            $qrText[] = (isset($fResult[0]['facility_code']))?$fResult[0]['facility_code']:'';
            $qrText[] = (isset($fResult[0]['facility_name']))?$fResult[0]['facility_name']:'';
            $qrText[] = $vl['lab_contact_person'];
            $qrText[] = $vl['lab_phone_number'];
            $qrText[] = (isset($fResult[0]['facility_emails']))?$fResult[0]['facility_emails']:'';
            $qrText[] = $vl['sample_received_at_vl_lab_datetime'];
            $qrText[] = $vl['sample_tested_datetime'];
            $qrText[] = $batchCode;
            $qrText[] = $vl['result_dispatched_datetime'];
            $qrText[] = $vl['result_value_log'];
            $qrText[] = $vl['result_value_absolute'];
            $qrText[] = $vl['result_value_text'];
            $qrText[] = $vl['result'];
            $qrText[] = $vl['approver_comments'];
            $qrText[] = $resultReviewedBy;
            $qrText[] = $vl['result_reviewed_datetime'];
            $qrText[] = $resultApprovedBy;
            $qrText[] = $vl['result_approved_datetime'];
            $qrText[] = $vl['result_printed_datetime'];
            $qrText[] = $vl['result_sms_sent_datetime'];
            $qrText[] = $vlInstanceId;
            $qrText[] = $vlCountry;
            $qrText[] = $vl['is_request_mail_sent'];
            $qrText[] = $vl['is_result_mail_sent'];
            $qrText[] = $vl['is_result_sms_sent'];
            $qrText[] = $vl['manual_result_entry'];
            $qrText[] = $requestCreatedBy;
            $qrText[] = $vl['request_created_datetime'];
            $qrText[] = $lastModifiedBy;
            $qrText[] = $vl['last_modified_datetime'];
            $qrText[] = $vl['import_machine_file_name'];
            $qrText[] = $vl['current_regimen'];
            $qrText[] = $vl['test_requested_on'];
            $qrText[] = $vl['sample_reordered'];
            $qrText[] = $vl['treatment_initiated_date'];
            //$qrText[] = $vl['reason_for_vl_result_changes'];
            //generate string
            $qrString = urlencode(implode(',',$qrText));
            $style = array(
                'border' => 2,
                'vpadding' => 'auto',
                'hpadding' => 'auto',
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, //array(255,255,255)
                'module_width' => 1, // width of a single module in points
                'module_height' => 1 // height of a single module in points
            );
            $pdf->write2DBarcode($qrString, 'QRCODE,L', 80, 100, 50, 50, $style, 'N');
            
            if($country=='7'){
              include('generateRwdForm.php');
            }
            
            $pdf->AddPage();
            $_SESSION['aliasPage'] = $page+1;
            $page = $page+1;
            $pdf->writeHTML($html);
            $pdf->lastPage();
            $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
            
            $pdf->Output($filename,"F");
            $pages[] = $filename;
            $qrText = array();
           $page++;
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
    $resultFilename = 'vl-test-request-' . date('d-M-Y-H-i-s') .'.pdf';
    $resultPdf->Output(UPLOAD_PATH. DIRECTORY_SEPARATOR. "qrcode" . DIRECTORY_SEPARATOR. $resultFilename, "F");
    $general->removeDirectory($pathFront);
    unset($_SESSION['rVal']);
 }
echo $resultFilename;
