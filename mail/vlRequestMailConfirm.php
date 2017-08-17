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
$configSyncQuery ="SELECT value FROM global_config where name='sync_path'";
$configSyncResult = $db->rawQuery($configSyncQuery);
$filename = '';
$downloadFile = '';
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
         $allField = array('Sample ID','Urgency','Province','District Name','Clinic Name','Clinician Name','Sample Collection Date','Sample Received Date','Collected by (Initials)','Gender','Date Of Birth','Age in years','Age in months','Is Patient Pregnant?','Is Patient Breastfeeding?','Patient OI/ART Number','Date Of ART Initiation','ART Regimen','Patient consent to SMS Notification?','Patient Mobile Number','Date Of Last Viral Load Test','Result Of Last Viral Load','Viral Load Log','Reason For VL Test','Lab Name','LAB No','VL Testing Platform','Specimen type','Sample Testing Date','Viral Load Result(copiesl/ml)','Log Value','If no result','Rejection Reason','Reviewed By','Approved By','Laboratory Scientist Comments','Status');
         $filedGroup = explode(",",$mailconf['rq_field']);
         //Set heading row
         $colNo = 0;
         foreach ($allField as $heading) {
          if($heading == 'Province'){
            $heading = 'Province/State';
          }else if($heading == 'District Name'){
            $heading = 'District/County';
          }
          $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($heading), PHPExcel_Cell_DataType::TYPE_STRING);
          $cellName = $sheet->getCellByColumnAndRow($colNo,1)->getColumn();
          $sheet->getColumnDimension($cellName)->setVisible(in_array($heading,$filedGroup)?TRUE:FALSE);
          $sheet->getStyle($cellName.'1')->applyFromArray($styleArray);
          $colNo++;
         }
         //Set values
         $output = array();
         for($s=0;$s<count($_POST['sample']);$s++){
            $row = array();
            $sampleQuery="SELECT sample_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."' ORDER BY f.facility_name ASC";
            $sampleResult = $db->rawQuery($sampleQuery);
            for($f=0;$f<count($allField);$f++){
               $field = '';
               $fieldValue = '';
               if($allField[$f] == "Sample ID"){
                  $field = 'sample_code';
               }elseif($allField[$f] == "Urgency"){
                  $field = 'test_urgency';
               }elseif($allField[$f] == "Province"){
                  $field = 'facility_state';
               }elseif($allField[$f] == "District Name"){
                  $field = 'facility_district';
               }elseif($allField[$f] == "Clinic Name"){
                  $field = 'facility_name';
               }elseif($allField[$f] == "Clinician Name"){
                  $field = 'lab_contact_person';
               }elseif($allField[$f] == "Sample Collection Date"){
                  $field = 'sample_collection_date';
               }elseif($allField[$f] == "Sample Received Date"){
                  $field = 'sample_received_at_vl_lab_datetime';
               }elseif($allField[$f] == "Collected by (Initials)"){
                  $field = 'sample_collected_by';
               }elseif($allField[$f] == "Gender"){
                  $field = 'patient_gender';
               }elseif($allField[$f] == "Date Of Birth"){
                  $field = 'patient_dob';
               }elseif($allField[$f] == "Age in years"){
                  $field = 'patient_age_in_years';
               }elseif($allField[$f] == "Age in months"){
                  $field = 'patient_age_in_months';
               }elseif($allField[$f] == "Is Patient Pregnant?"){
                  $field = 'is_patient_pregnant';
               }elseif($allField[$f] == "Is Patient Breastfeeding?"){
                  $field = 'is_patient_breastfeeding';
               }elseif($allField[$f] == "Patient OI/ART Number"){
                  $field = 'patient_art_no';
               }elseif($allField[$f] == "Date Of ART Initiation"){
                  $field = 'date_of_initiation_of_current_regimen';
               }elseif($allField[$f] == "ART Regimen"){
                  $field = 'current_regimen';
               }elseif($allField[$f] == "Patient consent to SMS Notification?"){
                  $field = 'consent_to_receive_sms';
               }elseif($allField[$f] == "Patient Mobile Number"){
                  $field = 'patient_mobile_number';
               }elseif($allField[$f] == "Date Of Last Viral Load Test"){
                  $field = 'last_viral_load_date';
               }elseif($allField[$f] == "Result Of Last Viral Load"){
                  $field = 'last_viral_load_result';
               }elseif($allField[$f] == "Viral Load Log"){
                  $field = 'last_vl_result_in_log';
               }elseif($allField[$f] == "Reason For VL Test"){
                  $field = 'reason_for_vl_testing';
               }elseif($allField[$f] == "Lab Name"){
                  $field = 'lab_id';
               }elseif($allField[$f] == "LAB No"){
                  $field = 'lab_code';
               }elseif($allField[$f] == "VL Testing Platform"){
                  $field = 'vl_test_platform';
               }elseif($allField[$f] == "Specimen type"){
                  $field = 'sample_name';
               }elseif($allField[$f] == "Sample Testing Date"){
                  $field = 'sample_tested_datetime';
               }elseif($allField[$f] == "Viral Load Result(copiesl/ml)"){
                  $field = 'result_value_absolute';
               }elseif($allField[$f] == "Log Value"){
                  $field = 'result_value_log';
               }elseif($allField[$f] == "If no result"){
                  $field = 'is_sample_rejected';
               }elseif($allField[$f] == "Rejection Reason"){
                  $field = 'rejection_reason_name';
               }elseif($allField[$f] == "Reviewed By"){
                  $field = 'result_reviewed_by';
               }elseif($allField[$f] == "Approved By"){
                  $field = 'result_approved_by';
               }elseif($allField[$f] == "Laboratory Scientist Comments"){
                  $field = 'approver_comments';
               }elseif($allField[$f] == "Status"){
                  $field = 'status_name';
               }
               if(!in_array($allField[$f],$filedGroup)){
                  $field = 'hidden';
               }
               if($field == '') { continue; }
               if($field == 'hidden') { $row[] = 'hidden'; continue; }
               if($field ==  'result_reviewed_by'){
                  $fValueQuery="SELECT u.user_name as reviewedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_type LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.reason_for_sample_rejection LEFT JOIN user_details as u ON u.user_id = vl.result_reviewed_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }elseif($field ==  'result_approved_by'){
                  $fValueQuery="SELECT u.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_type LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.reason_for_sample_rejection LEFT JOIN user_details as u ON u.user_id = vl.result_approved_by where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }elseif($field ==  'lab_id'){
                  $fValueQuery="SELECT f.facility_name as labName FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.lab_id=f.facility_id where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }else{
                  $fValueQuery="SELECT $field FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s_type ON s_type.sample_id=vl.sample_type LEFT JOIN r_sample_rejection_reasons as s_r_r ON s_r_r.rejection_reason_id=vl.reason_for_sample_rejection LEFT JOIN r_sample_status as t_s ON t_s.status_id=vl.result_status where vl.vl_sample_id = '".$_POST['sample'][$s]."'";
               }
               $fValueResult = $db->rawQuery($fValueQuery);
               if(count($fValueResult)>0){
                  if($field == 'sample_collection_date' || $field == 'sample_received_at_vl_lab_datetime' || $field == 'sample_tested_datetime'){
                      if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00 00:00:00'){
                        $xplodDate = explode(" ",$fValueResult[0][$field]);
                        $fieldValue=$general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];  
                      }
                  }elseif($field == 'patient_dob' || $field == 'date_of_initiation_of_current_regimen' || $field == 'last_viral_load_date'){
                      if(isset($fValueResult[0][$field]) && trim($fValueResult[0][$field])!= '' && trim($fValueResult[0][$field])!= '0000-00-00'){
                        $fieldValue=$general->humanDateFormat($fValueResult[0][$field]);
                      }
                  }elseif($field ==  'vl_test_platform' || $field ==  'patient_gender' || $field == 'is_sample_rejected'){
                    $fieldValue = ucwords(str_replace("_"," ",$fValueResult[0][$field]));
                  }elseif($field ==  'result_reviewed_by'){
                    $fieldValue = (isset($fValueResult[0]['reviewedBy']))?$fValueResult[0]['reviewedBy']:'';
                  }elseif($field ==  'result_approved_by'){
                    $fieldValue = (isset($fValueResult[0]['approvedBy']))?$fValueResult[0]['approvedBy']:'';
                  }elseif($field ==  'lab_id'){
                    $fieldValue = (isset($fValueResult[0]['labName']))?$fValueResult[0]['labName']:'';
                  }else{
                    $fieldValue = (isset($fValueResult[0][$field]))?$fValueResult[0][$field]:'';
                  }
               }
              $row[] = $fieldValue;
            }
           $output[] = $row;
         }
         //print_r($output);die;
         $start = (count($output));
         foreach ($output as $rowNo => $rowData) {
            $colNo = 0;
            foreach ($rowData as $field => $value) {
              $rRowCount = $rowNo + 2;
              $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
              $sheet->getColumnDimension($cellName)->setVisible(($value == 'hidden')?FALSE:TRUE);
              $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
              $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
              $sheet->getDefaultRowDimension()->setRowHeight(15);
              $sheet->getCellByColumnAndRow($colNo, $rowNo + 2)->setValueExplicit(html_entity_decode(($value == 'hidden')?'':$value), PHPExcel_Cell_DataType::TYPE_STRING);
             $colNo++;
            }
         }
         $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
         $filename = 'vlsm-requests-' . date('d-M-Y-H-i-s') . '.xls';
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
          <div style="text-align:center;"><h4>Facility Name : <?php echo ucwords($_POST['toName']); ?></h4></div>
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
                  <input type="hidden" id="storeFile" name="storeFile" value="no"/>
                  <div class="col-lg-12" style="text-align:center;padding-left:0;">
                      <a href="../mail/vlRequestMail.php" class="btn btn-default"> Cancel</a>&nbsp;
                      <a class="btn btn-primary" href="javascript:void(0);" onclick="confirmRequestMail();"><i class="fa fa-paper-plane" aria-hidden="true"></i> Send</a>
                      <div><code><?php echo ($configSyncResult[0]['value']=='')?'Please enter "Sync Path" in General Config to enable file sharing via shared folder':'' ?></code></div>
                      <p style="margin-top:10px;"><a id="send-mail" href="<?php echo $downloadFile; ?>" style="text-decoration:none;">Click here to download the excel</a></p>
                  </div>
               </div>
            </form>
        </div>
    </div>
</div>
<script>
    function confirmRequestMail(){
      <?php
      if($configSyncResult[0]['value']!=''){
         ?>
         conf = confirm("Do you also want to store this file on the shared directory <?php echo $configSyncResult[0]['value'];?> ?");
         if(conf){
            $("#storeFile").val('yes');
         }else{
            $("#storeFile").val('no');
         }
         <?php
      }
      ?>
        $.blockUI();
        document.getElementById('vlRequestMailConfirmForm').submit();
    }
</script>
<?php
 include('../footer.php');
?>