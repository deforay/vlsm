<?php
ob_start();
include('../header.php');
include ('../includes/PHPExcel.php');
include('../General.php');
$general=new Deforay_Commons_General();
//get other config details
$geQuery="SELECT * FROM other_config WHERE type = 'request'";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
   $mailconf[$row['name']] = $row['value'];
}
if(isset($_POST['toEmail']) && trim($_POST['toEmail'])!= '' && count($_POST['sample']) >0){
     $filedGroup = array();
     if(isset($mailconf['rq_field']) && trim($mailconf['rq_field'])!= ''){
          //Excel code start
          $excel = new PHPExcel();
          $sheet = $excel->getActiveSheet();
          $styleArray = array(
          'font' => array(
              'bold' => true,
              'size' => '13',
          ),
          'alignment' => array(
              'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
          ),
          'borders' => array(
              'outline' => array(
                  'style' => \PHPExcel_Style_Border::BORDER_THIN,
              ),
          )
         );
         $borderStyle = array(
               'alignment' => array(
                   'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               ),
               'borders' => array(
                   'outline' => array(
                       'style' => \PHPExcel_Style_Border::BORDER_THIN,
                   ),
               )
          );
         $filedGroup = explode(",",$mailconf['rq_field']);
         $headings = $filedGroup;
         //Set heading row
          $sheet->getCellByColumnAndRow(0, 1)->setValueExplicit(html_entity_decode('Sample'), PHPExcel_Cell_DataType::TYPE_STRING);
          $cellName = $sheet->getCellByColumnAndRow(0,1)->getColumn();
          $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
          $colNo = 1;
         foreach ($headings as $field => $value) {
          $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
          $cellName = $sheet->getCellByColumnAndRow($colNo,1)->getColumn();
          $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
          $colNo++;
         }
         //Set values
         $output = array();
         for($s=0;$s<count($_POST['sample']);$s++){
            $row = array();
            $sampleQuery="SELECT sample_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
            $sampleResult = $db->rawQuery($sampleQuery);
            $row[] = $sampleResult[0]['sample_code'];
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
                    $field = 'lab_id';
               }elseif($filedGroup[$f] == "LAB No"){
                    $field = 'lab_no';
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
               }elseif($filedGroup[$f] == "Status"){
                    $field = 'status_name';
               }
               
               if($field ==  'result_reviewed_by'){
                  $fValueQuery="SELECT u.user_name as reviewedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_reviewed_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }elseif($field ==  'result_approved_by'){
                  $fValueQuery="SELECT u.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN user_details as u ON u.user_id = vl.result_approved_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }elseif($field ==  'lab_id'){
                  $fValueQuery="SELECT f.facility_name as labName FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.lab_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }else{
                  $fValueQuery="SELECT $field FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_id LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.sample_rejection_reason LEFT JOIN r_testing_status as t_s ON t_s.status_id=vl.status where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }
               $fValueResult = $db->rawQuery($fValueQuery);
               $fieldValue = '';
               if(count($fValueResult)>0){
                    if($field == 'sample_collection_date' || $field == 'date_sample_received_at_testing_lab' || $field == 'lab_tested_date'){
                         if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00 00:00:00'){
                             $xplodDate = explode(" ",$fValueResult[0][$field]);
                             $fieldValue=$general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];  
                         }
                    }elseif($field == 'patient_dob' || $field == 'date_of_initiation_of_current_regimen' || $field == 'last_viral_load_date'){
                         if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00'){
                             $fieldValue=$general->humanDateFormat($fValueResult[0][$field]);
                         }
                    }elseif($field ==  'vl_test_platform' || $field ==  'gender' || $field == 'rejection'){
                      $fieldValue = ucwords(str_replace("_"," ",$fValueResult[0][$field]));
                    }elseif($field ==  'result_reviewed_by'){
                      $fieldValue = $fValueResult[0]['reviewedBy'];
                    }elseif($field ==  'result_approved_by'){
                      $fieldValue = $fValueResult[0]['approvedBy'];
                    }elseif($field ==  'lab_id'){
                      $fieldValue = $fValueResult[0]['labName'];
                    }else{
                      $fieldValue = $fValueResult[0][$field];
                    }
               }
              $row[] = $fieldValue;
            }
           $output[] = $row;
         }
          $start = (count($output));
          foreach ($output as $rowNo => $rowData) {
               $colNo = 0;
               foreach ($rowData as $field => $value) {
                 $rRowCount = $rowNo + 2;
                 $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
                 $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                 $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
                 $sheet->getDefaultRowDimension()->setRowHeight(15);
                 $sheet->getCellByColumnAndRow($colNo, $rowNo + 2)->setValueExplicit(html_entity_decode($value), PHPExcel_Cell_DataType::TYPE_STRING);
                 $colNo++;
               }
          }
          $filename = '';
          $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
          $filename = 'vl-request-mail' . date('d-M-Y-H-i-s') . '.xls';
          $pathFront=realpath('../temporary');
          $writer->save($pathFront. DIRECTORY_SEPARATOR . $filename);
          $downloadFile = '../temporary'. DIRECTORY_SEPARATOR . $filename;
     }else{
          $_SESSION['alertMsg']='Unable to generate the test request excel. Please check the request fields.';
          header('location:vlRequestMail.php');
     }
}else{
     $_SESSION['alertMsg']='Unable to generate the test request excel. Please try later.';
     header('location:vlRequestMail.php');
}
?>
<style>
   #send-mail:hover{
      text-decoration:underline !important;
   }
</style>
<div class="content-wrapper">
    <div class="box box-default">
        <div class="box-header with-border">
          <div style="text-align:center;"><h4>Facility : <?php echo ucwords($_POST['toName']); ?></h4></div>
        </div>
        <div class="box-body">
            <form id="vlRequestMailConfirmForm" name="vlRequestMailConfirmForm" method="post" action="vlRequestMailHelper.php">
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
                  <input type="hidden" id="fileName" name="fileName" value="<?php echo $filename; ?>"/>
                  <div class="col-lg-12" style="text-align:center;padding-left:0;">
                      <a href="../mail/vlRequestMail.php" class="btn btn-default"> Cancel</a>&nbsp;
                      <a class="btn btn-primary" href="javascript:void(0);" onclick="confirmRequestMail();"><i class="fa fa-paper-plane" aria-hidden="true"></i> Send</a>
                      <p style="margin-top:10px;"><a id="send-mail" href="<?php echo $downloadFile; ?>" style="text-decoration:none;">Click here to download the excel</a></p>
                  </div>
               </div>
            </form>
        </div>
    </div>
</div>
<script>
    function confirmRequestMail(){
        $.blockUI();
        document.getElementById('vlRequestMailConfirmForm').submit();
    }
</script>
<?php
 include('../footer.php');
?>