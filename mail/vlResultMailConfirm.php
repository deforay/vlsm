<?php
ob_start();
include('../header.php');
include ('../includes/tcpdf/tcpdf.php');
include('../General.php');
define('UPLOAD_PATH','../uploads');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
//get other config values
$geQuery="SELECT * FROM other_config WHERE type = 'result'";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
   $mailconf[$row['name']] = $row['value'];
}
//get logo
$configQuery="SELECT * from global_config WHERE name='logo'";
$configResult=$db->query($configQuery);
//print_r($configResult);die;
if(isset($_POST['toEmail']) && trim($_POST['toEmail'])!="" && count($_POST['sample'])>0){
     if(isset($mailconf['rs_field']) && trim($mailconf['rs_field'])!= ''){
       //Pdf code start
       // create new PDF document
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
      $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
      $pdf->setPageOrientation('L');
      // set document information
      $pdf->SetCreator(PDF_CREATOR);
      //$pdf->SetAuthor('Saravanan');
      $pdf->SetTitle('Vl Request Mail');
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
      $pdf->SetFont('helvetica', '', 8);
      //$pdf = new TCPDF();
      $pdf->AddPage();
      $pdfContent = '';
      $filedGroup = array();
         $filedGroup = explode(",",$mailconf['rs_field']);
         $pdfContent .= '<div style="">';
         if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $configResult[0]['value'])){
              $pdfContent .='<div style="width:100%;text-align:center;"><img src="../uploads/logo/'.$configResult[0]['value'].'" style="width:80px;height:80px;" alt="logo"></div>';
         }
         $pdfContent .='<div style="width:100%;text-align:left;font-size:16px;"><strong>Viral Load Results</strong></div>';
         $pdfContent.='<table style="width;100%;border:1px solid #333;" cellspacing="0" cellpadding="2">';
           $pdfContent.='<tr>';
            $pdfContent.='<td style="border:1px solid #333;"><strong>Sample</strong></td>';
            for($f=0;$f<count($filedGroup);$f++){
              $pdfContent.='<td style="border:1px solid #333;"><strong>'.$filedGroup[$f].'</strong></td>';
            }
           $pdfContent.='</tr>';
           for($s=0;$s<count($_POST['sample']);$s++){
            $sampleQuery="SELECT sample_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
            $sampleResult = $db->rawQuery($sampleQuery);
            $pdfContent.='<tr>';
            $pdfContent.='<td style="border:1px solid #333;"><strong>'.$sampleResult[0]['sample_code'].'</strong></td>';
            for($f=0;$f<count($filedGroup);$f++){
              if($filedGroup[$f] == "Form Serial No"){
                    $field = 'serial_no';
               }elseif($filedGroup[$f] == "Urgency"){
                    $field = 'urgency';
               }elseif($filedGroup[$f] == "Province"){
                    $field = 'state';
               }elseif($filedGroup[$f] == "District Name"){
                    $field = 'district';
               }elseif($filedGroup[$f] == "Clinic Name"){
                    $field = 'facility_name';
               }elseif($filedGroup[$f] == "Clinician Name"){
                    $field = 'lab_contact_person';
               }elseif($filedGroup[$f] == "Sample Collection Date"){
                    $field = 'sample_collection_date';
               }elseif($filedGroup[$f] == "Sample Received Date"){
                    $field = 'date_sample_received_at_testing_lab';
               }elseif($filedGroup[$f] == "Collected by (Initials)"){
                    $field = 'collected_by';
               }elseif($filedGroup[$f] == "Patient First Name"){
                    $field = 'patient_name';
               }elseif($filedGroup[$f] == "Surname"){
                    $field = 'surname';
               }elseif($filedGroup[$f] == "Gender"){
                    $field = 'gender';
               }elseif($filedGroup[$f] == "Date Of Birth"){
                    $field = 'patient_dob';
               }elseif($filedGroup[$f] == "Age in years"){
                    $field = 'age_in_yrs';
               }elseif($filedGroup[$f] == "Age in months"){
                    $field = 'age_in_mnts';
               }elseif($filedGroup[$f] == "Is Patient Pregnant?"){
                    $field = 'is_patient_pregnant';
               }elseif($filedGroup[$f] == "Is Patient Breastfeeding?"){
                    $field = 'is_patient_breastfeeding';
               }elseif($filedGroup[$f] == "Patient OI/ART Number"){
                    $field = 'art_no';
               }elseif($filedGroup[$f] == "Date Of ART Initiation"){
                    $field = 'date_of_initiation_of_current_regimen';
               }elseif($filedGroup[$f] == "ART Regimen"){
                    $field = 'current_regimen';
               }elseif($filedGroup[$f] == "Patient consent to SMS Notification?"){
                    $field = 'patient_receive_sms';
               }elseif($filedGroup[$f] == "Patient Mobile Number"){
                    $field = 'patient_phone_number';
               }elseif($filedGroup[$f] == "Date Of Last Viral Load Test"){
                    $field = 'last_viral_load_date';
               }elseif($filedGroup[$f] == "Result Of Last Viral Load"){
                    $field = 'last_viral_load_result';
               }elseif($filedGroup[$f] == "Viral Load Log"){
                    $field = 'viral_load_log';
               }elseif($filedGroup[$f] == "Reason For VL Test"){
                    $field = 'vl_test_reason';
               }elseif($filedGroup[$f] == "Lab Name"){
                    $field = 'lab_name';
               }elseif($filedGroup[$f] == "VL Testing Platform"){
                    $field = 'vl_test_platform';
               }elseif($filedGroup[$f] == "Specimen type"){
                    $field = 'sample_name';
               }elseif($filedGroup[$f] == "Sample Testing Date"){
                    $field = 'lab_tested_date';
               }elseif($filedGroup[$f] == "Viral Load Result(copiesl/ml)"){
                    $field = 'absolute_value';
               }elseif($filedGroup[$f] == "Log Value"){
                    $field = 'log_value';
               }elseif($filedGroup[$f] == "If no result"){
                    $field = 'rejection';
               }elseif($filedGroup[$f] == "Rejection Reason"){
                    $field = 'rejection_reason_name';
               }elseif($filedGroup[$f] == "Reviewed By"){
                    $field = 'result_reviewed_by';
               }elseif($filedGroup[$f] == "Approved By"){
                    $field = 'result_approved_by';
               }elseif($filedGroup[$f] == "Laboratory Scientist Comments"){
                    $field = 'comments';
               }
               if($field ==  'result_reviewed_by'){
                    $fValueQuery="SELECT u.user_name as reviewedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_reviewed_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }elseif($field ==  'result_approved_by'){
                    $fValueQuery="SELECT u.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_approved_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }else{
                 $fValueQuery="SELECT $field FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }
               $fValueResult = $db->rawQuery($fValueQuery);
               
               $fieldValue = '';
               if(isset($fValueResult) && count($fValueResult)>0){
                    if($field == 'sample_collection_date' || $field == 'date_sample_received_at_testing_lab' || $field == 'lab_tested_date'){
                         if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00 00:00:00'){
                             $xplodDate = explode(" ",$fValueResult[0][$field]);
                             $fieldValue=$general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];  
                         }
                    }elseif($field == 'patient_dob' || $field == 'date_of_initiation_of_current_regimen' || $field == 'last_viral_load_date'){
                         if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00'){
                             $fieldValue=$general->humanDateFormat($fValueResult[0][$field]);
                         }
                    }elseif($field ==  'vl_test_platform' || $field ==  'gender'){
                      $fieldValue = ucwords(str_replace("_"," ",$fValueResult[0][$field]));
                    }elseif($field ==  'result_reviewed_by'){
                      $fieldValue = $fValueResult[0]['reviewedBy'];
                    }elseif($field ==  'result_approved_by'){
                      $fieldValue = $fValueResult[0]['approvedBy'];
                    }else{
                      $fieldValue = $fValueResult[0][$field];
                    }
               }
              $pdfContent.='<td style="border:1px solid #333;">'.$fieldValue.'</td>';
            }
            $pdfContent.='</tr>';
           }
          $pdfContent.='</table>';
          $pdfContent .= '</div>';
          $pdf->writeHTML($pdfContent);
          $pdf->lastPage();
          $pathFront=realpath('../uploads');
          $filename = 'vl-result-form-' . date('d-M-Y-H-i-s') . '.pdf';
          $pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
          $downloadFile1 = '../uploads' . DIRECTORY_SEPARATOR . $_POST['pdfFile'];
          $downloadFile2 = '../uploads' . DIRECTORY_SEPARATOR . $filename;
     }else{
        $_SESSION['alertMsg']='Unable to generate test result pdf. Please check the result fields.';
        header('location:vlResultMail.php');
     }
 }else{
     $_SESSION['alertMsg']='Unable to generate test result pdf. Please try later.';
     header('location:vlResultMail.php');
 }
?>
<style>
   .send-mail:hover{
      text-decoration:underline !important;
   }
</style>
<div class="content-wrapper">
    <div class="box box-default">
        <div class="box-header with-border">
          <div style="text-align:center;"><h4>Facility : <?php echo ucwords($_POST['toName']); ?></h4></div>
        </div>
        <div class="box-body">
            <form id="vlResultMailConfirmForm" name="vlResultMailConfirmForm" method="post" action="vlResultMailHelper.php">
               <div class="row">
                  <div class="col-lg-12" style="text-align:center !important;">
                     <table class="table table-bordered table-striped" style="width:18%;margin-left:41%;">
                         <thead>
                           <tr>
                             <th style="text-align:center;background-color:#71b9e2;color:#FFFFFF;">Selected Sample(s)</th>
                           </tr>
                         </thead>
                         <tbody>
                           <?php
                           for($s=0;$s<count($_POST['sample']);$s++){
                               $sampleQuery="SELECT sample_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
                               $sampleResult = $db->rawQuery($sampleQuery);
                           ?>
                            <tr>
                             <td style="text-align:left;"><?php echo $sampleResult[0]['sample_code']; ?></td>
                            </tr>
                           <?php } ?>
                         </tbody>
                     </table>
                  </div>
               </div>
               <div class="row">
                  <input type="hidden" id="subject" name="subject" value="<?php echo $_POST['subject']; ?>"/>
                  <input type="hidden" id="toEmail" name="toEmail" value="<?php echo $_POST['toEmail']; ?>"/>
                  <input type="hidden" id="reportEmail" name="reportEmail" value="<?php echo $_POST['reportEmail']; ?>"/>
                  <input type="hidden" id="message" name="message" value="<?php echo $_POST['message']; ?>"/>
                  <input type="hidden" id="sample" name="sample" value="<?php echo implode(',',$_POST['sample']); ?>"/>
                  <input type="hidden" id="pdfFile1" name="pdfFile1" value="<?php echo $_POST['pdfFile']; ?>"/>
                  <input type="hidden" id="pdfFile2" name="pdfFile2" value="<?php echo $filename; ?>"/>
                  <div class="col-lg-12" style="text-align:center;padding-left:0;">
                      <a href="../mail/vlResultMail.php" class="btn btn-default"> Cancel</a>&nbsp;
                      <a class="btn btn-primary" href="javascript:void(0);" onclick="confirmResultMail();"><i class="fa fa-paper-plane" aria-hidden="true"></i> Send</a>
                      <p style="margin-top:10px;"><a class="send-mail" href="<?php echo $downloadFile1; ?>" target="_blank" style="text-decoration:none;">Click here to download the result only pdf</a></p>
                      <p style="margin-top:10px;"><a class="send-mail" href="<?php echo $downloadFile2; ?>" target="_blank" style="text-decoration:none;">Click here to download the result pdf </a></p>
                  </div>
               </div>
            </form>
        </div>
    </div>
</div>
<script>
    function confirmResultMail(){
        $.blockUI();
        document.getElementById('vlResultMailConfirmForm').submit();
    }
</script>
<?php
 include('../footer.php');
?>