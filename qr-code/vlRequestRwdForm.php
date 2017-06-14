<?php
ob_start();
include('../header.php');
include('../General.php');
$general=new Deforay_Commons_General();
$qrVal = explode(',',$_GET['q']);
if(!isset($qrVal[56]) || $qrVal[56]== '' || $qrVal[56]== null){
  $_SESSION['alertMsg']="OOPS..Please try again later";
  header("location:readQRCode.php");
}
//global config
$cQuery="SELECT * FROM global_config";
$cResult=$db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
  $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}
//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);

$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);

//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2' AND status='active'";
$lResult = $db->rawQuery($lQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

//sample status
$statusQuery="SELECT * FROM r_sample_status where status = 'active'";
$statusResult = $db->rawQuery($statusQuery);

$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
  foreach($pdResult as $provinceName){
    $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
  }

$facility = '';
$facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";
foreach($fResult as $fDetails){
  $facility .= "<option data-code='".$fDetails['facility_code']."' data-emails='".$fDetails['facility_emails']."' data-mobile-nos='".$fDetails['facility_mobile_numbers']."' data-contact-person='".ucwords($fDetails['contact_person'])."' value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
}

//get active sample types
$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='rwd' AND art_status ='active'";
$aResult=$db->query($aQuery);

//existing datas show start
$vlQuery="SELECT * from vl_request_form where sample_code='".$qrVal[56]."'";
$vlQueryInfo=$db->query($vlQuery);
if(isset($vlQueryInfo) && count($vlQueryInfo) > 0){
  $estateName = '';
  $edistrictName = '';
  $efacilityName = '';
  $efacilityCode = '';
  $efacilityEmails = '';
  //facility details
  if(isset($vlQueryInfo[0]['facility_id']) && trim($vlQueryInfo[0]['facility_id'])!= '' && $vlQueryInfo[0]['facility_id']!= null && $vlQueryInfo[0]['facility_id'] >0){
    $efacilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."' AND status='active'";
    $efacilityResult=$db->query($efacilityQuery);
  }
  if(isset($efacilityResult[0]['facility_state'])){
    $estateName = $efacilityResult[0]['facility_state'];
  }
  if(isset($efacilityResult[0]['facility_district'])){
    $edistrictName = $efacilityResult[0]['facility_district'];
  }
  if(isset($efacilityResult[0]['facility_name'])){
    $efacilityName = $efacilityResult[0]['facility_name'];
  }
  if(isset($efacilityResult[0]['facility_code'])){
    $efacilityCode = $efacilityResult[0]['facility_code'];
  }
  if(isset($efacilityResult[0]['facility_emails'])){
    $efacilityEmails = $efacilityResult[0]['facility_emails'];
  }
  //dob
  if(isset($vlQueryInfo[0]['patient_dob']) && trim($vlQueryInfo[0]['patient_dob'])!='' && $vlQueryInfo[0]['patient_dob']!= null && $vlQueryInfo[0]['patient_dob']!='0000-00-00'){
   $vlQueryInfo[0]['patient_dob']=$general->humanDateFormat($vlQueryInfo[0]['patient_dob']);
  }else{
   $vlQueryInfo[0]['patient_dob']='';
  }
  //sample collection date
  if(isset($vlQueryInfo[0]['sample_collection_date']) && trim($vlQueryInfo[0]['sample_collection_date'])!='' && $vlQueryInfo[0]['sample_collection_date']!= null && $vlQueryInfo[0]['sample_collection_date']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$vlQueryInfo[0]['sample_collection_date']);
   $vlQueryInfo[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $vlQueryInfo[0]['sample_collection_date']='';
  }
  //sample type
  if(isset($vlQueryInfo[0]['sample_type']) && trim($vlQueryInfo[0]['sample_type'])!= '' && $vlQueryInfo[0]['sample_type']!= null && $vlQueryInfo[0]['sample_type'] >0){
    $especimenTypeQuery = 'select sample_name from r_sample_type where sample_id = "'.$vlQueryInfo[0]['sample_type'].'"';
    $especimenResult = $db->rawQuery($especimenTypeQuery);
  }
  //treatmnet initiation date
  if(isset($vlQueryInfo[0]['treatment_initiated_date']) && trim($vlQueryInfo[0]['treatment_initiated_date'])!='' && $vlQueryInfo[0]['treatment_initiated_date']!= null && $vlQueryInfo[0]['treatment_initiated_date']!='0000-00-00'){
   $vlQueryInfo[0]['treatment_initiated_date']=$general->humanDateFormat($vlQueryInfo[0]['treatment_initiated_date']);
  }else{
   $vlQueryInfo[0]['treatment_initiated_date']='';
  }
  //current regimen initiation date
  if(isset($vlQueryInfo[0]['date_of_initiation_of_current_regimen']) && trim($vlQueryInfo[0]['date_of_initiation_of_current_regimen'])!='' && $vlQueryInfo[0]['date_of_initiation_of_current_regimen']!= null && $vlQueryInfo[0]['date_of_initiation_of_current_regimen']!='0000-00-00'){
   $vlQueryInfo[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($vlQueryInfo[0]['date_of_initiation_of_current_regimen']);
  }else{
   $vlQueryInfo[0]['date_of_initiation_of_current_regimen']='';
  }
  //requested on
  if(isset($vlQueryInfo[0]['test_requested_on']) && trim($vlQueryInfo[0]['test_requested_on'])!='' && $vlQueryInfo[0]['test_requested_on']!= null && $vlQueryInfo[0]['test_requested_on']!='0000-00-00'){
   $vlQueryInfo[0]['test_requested_on']=$general->humanDateFormat($vlQueryInfo[0]['test_requested_on']);
  }else{
   $vlQueryInfo[0]['test_requested_on']='';
  }
  //lab
  if(isset($vlQueryInfo[0]['lab_id']) && trim($vlQueryInfo[0]['lab_id'])!= '' && $vlQueryInfo[0]['lab_id']!= null && $vlQueryInfo[0]['lab_id'] >0){
    $elabQuery = 'select facility_name from facility_details where facility_id = "'.$vlQueryInfo[0]['lab_id'].'"';
    $elabResult = $db->rawQuery($elabQuery);
  }
  //sample received at testing lab
  if(isset($vlQueryInfo[0]['sample_received_at_vl_lab_datetime']) && trim($vlQueryInfo[0]['sample_received_at_vl_lab_datetime'])!='' && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!= null && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$vlQueryInfo[0]['sample_received_at_vl_lab_datetime']);
   $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']='';
  }
  //sample tested date
  if(isset($vlQueryInfo[0]['sample_tested_datetime']) && trim($vlQueryInfo[0]['sample_tested_datetime'])!='' && $vlQueryInfo[0]['sample_tested_datetime']!= null && $vlQueryInfo[0]['sample_tested_datetime']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$vlQueryInfo[0]['sample_tested_datetime']);
   $vlQueryInfo[0]['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $vlQueryInfo[0]['sample_tested_datetime']='';
  }
  //result dispatched datetime
  if(isset($vlQueryInfo[0]['result_dispatched_datetime']) && trim($vlQueryInfo[0]['result_dispatched_datetime'])!='' && $vlQueryInfo[0]['result_dispatched_datetime']!= null && $vlQueryInfo[0]['result_dispatched_datetime']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$vlQueryInfo[0]['result_dispatched_datetime']);
   $vlQueryInfo[0]['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
  }else{
   $vlQueryInfo[0]['result_dispatched_datetime']='';
  }
  //rejection reason
  if(isset($vlQueryInfo[0]['reason_for_sample_rejection']) && trim($vlQueryInfo[0]['reason_for_sample_rejection']) !='' && $vlQueryInfo[0]['reason_for_sample_rejection'] != null && $vlQueryInfo[0]['reason_for_sample_rejection'] >0){
    $erejectionReasonQuery = 'select rejection_reason_name from r_sample_rejection_reasons where rejection_reason_id = "'.$vlQueryInfo[0]['reason_for_sample_rejection'].'"';
    $erejectionReasonQueryResult = $db->rawQuery($erejectionReasonQuery);
  }
  //approved by
  if(isset($vlQueryInfo[0]['result_approved_by']) && trim($vlQueryInfo[0]['result_approved_by']) !='' && $vlQueryInfo[0]['result_approved_by'] != null && $vlQueryInfo[0]['result_approved_by'] >0){
    $eapprovedUserQuery = 'select user_name from user_details where user_id = "'.$vlQueryInfo[0]['result_approved_by'].'"';
    $eapprovedUserResult = $db->rawQuery($eapprovedUserQuery);
  }
  //result status
  if(isset($vlQueryInfo[0]['result_status']) && trim($vlQueryInfo[0]['result_status']) !='' && $vlQueryInfo[0]['result_status'] != null && $vlQueryInfo[0]['result_status'] >0){
    $etestStatusQuery = 'select status_name from r_sample_status where status_id = "'.$vlQueryInfo[0]['result_status'].'"';
    $etestStatusResult = $db->rawQuery($etestStatusQuery);
  }
  //set reason for changes history
  $erch = '';
  if(isset($vlQueryInfo[0]['reason_for_vl_result_changes']) && $vlQueryInfo[0]['reason_for_vl_result_changes']!= '' && $vlQueryInfo[0]['reason_for_vl_result_changes']!= null){
    $erch.='<h4>Result Changes History</h4>';
    $erch.='<table style="width:100%;">';
    $erch.='<thead><tr style="border-bottom:2px solid #d3d3d3;"><th style="width:20%;">USER</th><th style="width:60%;">MESSAGE</th><th style="width:20%;text-align:center;">DATE</th></tr></thead>';
    $erch.='<tbody>';
    $splitChanges = explode('vlsm',$vlQueryInfo[0]['reason_for_vl_result_changes']);
    for($c=0;$c<count($splitChanges);$c++){
      $getData = explode("##",$splitChanges[$c]);
      $expStr = explode(" ",$getData[2]);
      $changedDate = $general->humanDateFormat($expStr[0])." ".$expStr[1];
      $erch.='<tr><td>'.ucwords($getData[0]).'</td><td>'.ucfirst($getData[1]).'</td><td style="text-align:center;">'.$changedDate.'</td></tr>';
    }
    $erch.='</tbody>';
    $erch.='</table>';
  }
}
//existing values show end
//set province
if(isset($qrVal[2]) && trim($qrVal[2]) != '' && $qrVal[2]!= null){
  $stateQuery="SELECT * from province_details where province_name='".$qrVal[2]."'";
  $stateResult=$db->query($stateQuery);
}
if(!isset($stateResult[0]['province_code'])){
  $stateResult[0]['province_code'] = '';
}
//district details
$districtResult = array();
if(isset($qrVal[2]) && trim($qrVal[2])!= ''){
  $districtQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$qrVal[2]."' AND status='active'";
  $districtResult=$db->query($districtQuery);
}
//facility details
$facilityResult  =array();
if(isset($qrVal[0]) && $qrVal[0] !='' && $qrVal[0]!= null){
  $facilityQuery = 'select * from facility_details where facility_code = "'.$qrVal[0].'"';
  $facilityResult = $db->rawQuery($facilityQuery);
}else if(isset($qrVal[0]) && $qrVal[1] !='' && $qrVal[1]!= null){
  $facilityQuery = 'select * from facility_details where facility_name = "'.$qrVal[1].'"';
  $facilityResult = $db->rawQuery($facilityQuery);
}
if(!isset($facilityResult[0]['facility_id'])){
  $facilityResult[0]['facility_id'] = '';
}
if(!isset($facilityResult[0]['facility_state'])){
  $facilityResult[0]['facility_state'] = '';
}
if(!isset($facilityResult[0]['facility_code'])){
  $facilityResult[0]['facility_code'] = '';
}
if(!isset($facilityResult[0]['facility_emails'])){
  $facilityResult[0]['facility_emails'] = '';
}
//dob
$dob = '';
if(isset($qrVal[22]) && trim($qrVal[22])!='' && $qrVal[22]!= null && $qrVal[22]!='0000-00-00'){
 $dob = $general->humanDateFormat($qrVal[22]);
}
//sample collection date
$sampleCollectionDate = '';
if(isset($qrVal[11]) && trim($qrVal[11])!='' && $qrVal[11]!= null && $qrVal[11]!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$qrVal[11]);
 $sampleCollectionDate = $general->humanDateFormat($expStr[0])." ".$expStr[1];
}
//sample type
if(isset($qrVal[57]) && trim($qrVal[57])!= '' && $qrVal[57]!= null){
  $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$qrVal[57].'"';
  $specimenResult = $db->rawQuery($specimenTypeQuery);
}
//treatment initiated date
$treatmentInitiatedDate  ='';
if(isset($qrVal[99]) && trim($qrVal[99])!='' && $qrVal[99]!= null && $qrVal[99]!='0000-00-00'){
 $treatmentInitiatedDate = $general->humanDateFormat($qrVal[99]);
}
//date of initiation of current regimen
$dateOfInitiationOfCurrentRegimen = '';
if(isset($qrVal[38]) && trim($qrVal[38])!='' && $qrVal[38]!= null && $qrVal[38]!='0000-00-00'){
 $dateOfInitiationOfCurrentRegimen = $general->humanDateFormat($qrVal[38]);
}
//test requested on
$testRequestedOn = '';
if(isset($qrVal[97]) && trim($qrVal[97])!='' && $qrVal[97]!= null && $qrVal[97]!='0000-00-00'){
 $testRequestedOn = $general->humanDateFormat($qrVal[97]);
}
//lab
if(isset($qrVal[65]) && trim($qrVal[65]) !='' && $qrVal[65]!= null){
  $labQuery = 'select facility_id from facility_details where facility_code = "'.$qrVal[65].'"';
  $labResult = $db->rawQuery($labQuery);
}else if(isset($qrVal[66]) && trim($qrVal[66]) !='' && $qrVal[66]!= null){
  $labQuery = 'select facility_id from facility_details where facility_name = "'.$qrVal[66].'"';
  $labResult = $db->rawQuery($labQuery);
}
//sample received at testing lab date
$sampleReceivedAtTestingLabDate = '';
if(isset($qrVal[70]) && trim($qrVal[70])!='' && $qrVal[70]!= null && $qrVal[70]!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$qrVal[70]);
 $sampleReceivedAtTestingLabDate = $general->humanDateFormat($expStr[0])." ".$expStr[1];
}
//sample testing date
$sampleTestingDate = '';
if(isset($qrVal[71]) && trim($qrVal[71])!='' && $qrVal[71]!= null && $qrVal[71]!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$qrVal[71]);
 $sampleTestingDate = $general->humanDateFormat($expStr[0])." ".$expStr[1];
}
//result dispatched date time
$resultDispatchedDateTime = '';
if(isset($qrVal[73]) && trim($qrVal[73])!='' && $qrVal[73]!= null && $qrVal[73]!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$qrVal[73]);
 $resultDispatchedDateTime = $general->humanDateFormat($expStr[0])." ".$expStr[1];
}
//rejection reason
if(isset($qrVal[60]) && trim($qrVal[60]) !='' && $qrVal[60]!= null){
  $rejectionReasonQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.$qrVal[60].'"';
  $rejectionReasonQueryResult = $db->rawQuery($rejectionReasonQuery);
}
//approved by
if(isset($qrVal[81]) && trim($qrVal[81]) !='' && $qrVal[81]!= null){
  $approvedUserQuery = 'select user_id from user_details where user_name = "'.$qrVal[81].'"';
  $approvedUserResult = $db->rawQuery($approvedUserQuery);
}
//result status
if(isset($qrVal[64]) && trim($qrVal[64]) !='' && $qrVal[64]!= null){
  $testStatusQuery = 'select status_id from r_sample_status where status_name = "'.$qrVal[64].'"';
  $testStatusResult = $db->rawQuery($testStatusQuery);
}
//set reason for changes history
$rch = '';
if(isset($qrVal[100]) && trim($qrVal[100]) !='' && $qrVal[100]!= null){
  $rch.='<h4>Result Changes History</h4>';
  $rch.='<table style="width:100%;">';
  $rch.='<thead><tr style="border-bottom:2px solid #d3d3d3;"><th style="width:20%;">USER</th><th style="width:60%;">MESSAGE</th><th style="width:20%;text-align:center;">DATE</th></tr></thead>';
  $rch.='<tbody>';
  $splitChanges = explode('vlsm',$qrVal[100]);
  for($c=0;$c<count($splitChanges);$c++){
    $getData = explode("##",$splitChanges[$c]);
    $expStr = explode(" ",$getData[2]);
    $changedDate = $general->humanDateFormat($expStr[0])." ".$expStr[1];
    $rch.='<tr><td>'.ucwords($getData[0]).'</td><td>'.ucfirst($getData[1]).'</td><td style="text-align:center;">'.$changedDate.'</td></tr>';
  }
  $rch.='</tbody>';
  $rch.='</table>';
}
//current regimen
$aCheckQuery="SELECT art_code from r_art_code_details where nation_identifier='rwd' AND art_code ='".$qrVal[96]."'";
$aCheckResult=$db->query($aCheckQuery);
?>
<style>
  .ui_tpicker_second_label {
       display: none !important;
      }
      .ui_tpicker_second_slider {
       display: none !important;
      }.ui_tpicker_millisec_label {
       display: none !important;
      }.ui_tpicker_millisec_slider {
       display: none !important;
      }.ui_tpicker_microsec_label {
       display: none !important;
      }.ui_tpicker_microsec_slider {
       display: none !important;
      }.ui_tpicker_timezone_label {
       display: none !important;
      }.ui_tpicker_timezone {
       display: none !important;
      }.ui_tpicker_time_input{
       width:100%;
      }
      .table > tbody > tr > td{
        border-top:none;
      }
      .form-control{
        width:100% !important;
      }
      .row{
        margin-top:6px;
      }
</style>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM </h1>
      <ol class="breadcrumb">
        <li><a href="../dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Vl Request Form</li>
      </ol>
    </section>
    <?php
    if(isset($vlQueryInfo[0]['sample_code']) && trim($vlQueryInfo[0]['sample_code'])!= ''){
    ?>
      <section class="content" style="padding-bottom:0;">
        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
          <div class="box-header with-border">
            <div class="pull-left" style="font-size:24px;">Existing VL Information</div>
          </div>
          <div class="box-body">
        <div class="box-body">
          <div class="">
              <div class="box-header with-border">
                  <h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
              </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Sample ID</label><br>
                    <span><?php echo $vlQueryInfo[0]['sample_code']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Sample Reordered</label><br>
                    <span><?php echo ucfirst($vlQueryInfo[0]['sample_reordered']); ?></span>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Province </label><br>
                    <span><?php echo ucwords($estateName); ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                   <label for="">District </label><br>
                   <span><?php echo ucwords($edistrictName); ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Clinic/Health Center </label><br>
                    <span><?php echo ucwords($efacilityName); ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Clinic/Health Center Code </label><br>
                    <span><?php echo ucwords($efacilityCode); ?></span>
                    </div>
                </div>
              </div>
            </div>
          </div>
          <div class="">
              <div class="box-header with-border">
                  <h3 class="box-title">Patient Information</h3>
              </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">ART (TRACNET) No.</label><br>
                    <span><?php echo $vlQueryInfo[0]['patient_art_no']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Date of Birth </label><br>
                    <span><?php echo $vlQueryInfo[0]['patient_dob']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">If DOB unknown, Age in Year </label><br>
                    <span><?php echo $vlQueryInfo[0]['patient_age_in_years']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">If Age < 1, Age in Month </label><br>
                    <span><?php echo $vlQueryInfo[0]['patient_age_in_months']; ?></span>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Patient Name </label><br>
                    <span><?php echo ucwords($vlQueryInfo[0]['patient_first_name']); ?></span>
                  </div>
                </div>  
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Gender</label><br>
                    <span><?php echo ucwords(str_replace('_',' ',$vlQueryInfo[0]['patient_gender'])); ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Phone Number</label><br>
                    <span><?php echo $vlQueryInfo[0]['patient_mobile_number']; ?></span>
                  </div>
                </div>
             </div>
          </div>
          <div class="">
              <div class="box-header with-border">
                  <h3 class="box-title">Sample Information</h3>
              </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Date of Sample Collection </label><br>
                    <span><?php echo $vlQueryInfo[0]['sample_collection_date']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                      <label for="">Sample Type</label><br>
                      <span><?php echo(isset($especimenResult[0]['sample_name']) && trim($especimenResult[0]['sample_name'])!= '')?$especimenResult[0]['sample_name']:''; ?></span>
                    </div>
                  </div>
              </div>
          </div>
          <div class="">
              <div class="box-header with-border">
                  <h3 class="box-title">Treatment Information</h3>
              </div>
            <div class="box-body">
              <div class="row">
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Date of Treatment Initiation</label><br>
                    <span><?php echo $vlQueryInfo[0]['treatment_initiated_date']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                      <label for="">Current Regimen</label><br>
                      <span><?php echo $vlQueryInfo[0]['current_regimen']; ?></span>
                    </div>
                 </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">Date of Initiation of Current Regimen </label><br>
                    <span><?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen']; ?></span>
                  </div>
                </div>
                <div class="col-xs-3 col-md-3">
                  <div class="form-group">
                    <label for="">ARV Adherence </label><br>
                    <span>
                    <?php
                    if($vlQueryInfo[0]['arv_adherance_percentage'] == 'good'){
                      echo 'Good >= 95%';
                    }else if($vlQueryInfo[0]['arv_adherance_percentage'] == 'fair'){
                      echo 'Fair (85-94%)';
                    }else if($vlQueryInfo[0]['arv_adherance_percentage'] == 'poor'){
                      echo 'Poor < 85%';
                    }
                    ?>
                    </span>
                  </div>
                </div>
              </div>
              <?php
              if($vlQueryInfo[0]['patient_gender'] == 'female' || $vlQueryInfo[0]['patient_gender'] == '' || $vlQueryInfo[0]['patient_gender'] == null){
              ?>
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                      <label for="">Is Patient Pregnant? </label><br>
                      <span><?php echo ucwords($vlQueryInfo[0]['is_patient_pregnant']); ?></span>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                      <label for="">Is Patient Breastfeeding? </label><br>
                      <span><?php echo ucwords($vlQueryInfo[0]['is_patient_breastfeeding']); ?></span>
                    </div>
                  </div>
                </div>
              <?php } ?>
            </div>
            <div class="">
              <div class="box-header with-border">
                 <h3 class="box-title">Indication for Viral Load Testing</h3><small> (Please tick one):(To be completed by clinician)</small>
              </div>
              <div class="box-body">
                <?php
                if(trim($vlQueryInfo[0]['reason_for_vl_testing']) =='routine'){ ?>
                  <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label>
                                <?php
                                $rmLastVLTestingDate = '';
                                if(isset($vlQueryInfo[0]['last_vl_date_routine']) && trim($vlQueryInfo[0]['last_vl_date_routine'])!='' && $vlQueryInfo[0]['last_vl_date_routine']!=null && $vlQueryInfo[0]['last_vl_date_routine']!='0000-00-00'){
                                  $rmLastVLTestingDate = $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_routine']);
                                }
                                ?>
                                <strong>#Routine Monitoring</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label class="col-lg-5 control-label">Date of last viral load test</label>
                      <span><?php echo $rmLastVLTestingDate; ?></span>
                    </div>
                    <div class="col-md-6">
                      <label for="" class="col-lg-3 control-label">VL Value</label>
                      <span><?php echo $vlQueryInfo[0]['last_vl_result_routine']; ?> (copies/ml)</span>
                    </div>                 
                  </div>
               <?php } else if(trim($vlQueryInfo[0]['reason_for_vl_testing']) =='failure') { ?>
                  <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label>
                                <?php
                                $facVLTestingDate = '';
                                if(isset($vlQueryInfo[0]['last_vl_date_failure_ac']) && trim($vlQueryInfo[0]['last_vl_date_failure_ac'])!='' && $vlQueryInfo[0]['last_vl_date_failure_ac']!= null && $vlQueryInfo[0]['last_vl_date_failure_ac']!='0000-00-00'){
                                  $facVLTestingDate = $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_failure_ac']);
                                }
                                ?>
                                <strong>#Repeat VL test after suspected treatment failure adherence counselling</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label class="col-lg-5 control-label">Date of last viral load test</label>
                      <span><?php echo $facVLTestingDate; ?></span>
                    </div>
                    <div class="col-md-6">
                      <label for="" class="col-lg-3 control-label">VL Value</label>
                      <span><?php echo $vlQueryInfo[0]['last_vl_result_failure_ac']; ?> (copies/ml)</span>
                    </div>                 
                  </div>
               <?php } else if(trim($vlQueryInfo[0]['reason_for_vl_testing']) =='suspect') { ?>
                  <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label>
                                <?php
                                $stfVLTestingDate = '';
                                if(isset($vlQueryInfo[0]['last_vl_date_failure']) && trim($vlQueryInfo[0]['last_vl_date_failure'])!='' && $vlQueryInfo[0]['last_vl_date_failure']!= null && $vlQueryInfo[0]['last_vl_date_failure']!='0000-00-00'){
                                  $stfVLTestingDate = $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_failure_ac']);
                                }
                                ?>
                                <strong>#Suspect Treatment Failure</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label class="col-lg-5 control-label">Date of last viral load test</label>
                      <span><?php echo $stfVLTestingDate; ?></span>
                    </div>
                    <div class="col-md-6">
                      <label for="" class="col-lg-3 control-label">VL Value</label>
                      <span><?php echo $vlQueryInfo[0]['last_vl_result_failure']; ?> (copies/ml)</span>
                    </div>                 
                  </div>
               <?php } ?>
               <div class="row">
                  <div class="col-md-4">
                      <label for="" class="col-lg-5 control-label">Request Clinician</label>
                      <span><?php echo ucwords($vlQueryInfo[0]['request_clinician_name']); ?> </span>
                  </div>
                  <div class="col-md-4">
                      <label for="" class="col-lg-5 control-label">Phone Number</label>
                      <span><?php echo $vlQueryInfo[0]['request_clinician_phone_number']; ?> </span>
                  </div>
                  <div class="col-md-4">
                      <label class="col-lg-5 control-label" for="">Request Date </label>
                      <span><?php echo $vlQueryInfo[0]['test_requested_on']; ?> </span>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-4">
                      <label for="" class="col-lg-5 control-label">VL Focal Person </label>
                      <span><?php echo ucwords($vlQueryInfo[0]['vl_focal_person']); ?> </span>
                  </div>
                  <div class="col-md-4">
                      <label for="" class="col-lg-5 control-label">VL Focal Person Phone Number</label>
                      <span><?php echo $vlQueryInfo[0]['vl_focal_person_phone_number']; ?> </span>
                  </div>
                  <div class="col-md-4">
                      <label class="col-lg-5 control-label" for="">Email for HF </label>
                      <span><?php echo $efacilityEmails; ?> </span>
                  </div>
               </div>
              </div>
            </div>
            <div class="">
              <div class="box-header with-border">
                <h3 class="box-title">Laboratory Information</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-md-4">
                    <label for="" class="col-lg-5 control-label">Lab Name </label>
                    <span><?php echo (isset($elabResult[0]['facility_name']))?ucwords($elabResult[0]['facility_name']):''; ?> </span>
                  </div>
                  <div class="col-md-4">
                    <label for="" class="col-lg-5 control-label">VL Testing Platform </label>
                    <span><?php echo ucwords($vlQueryInfo[0]['vl_test_platform']); ?> </span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                      <label class="col-lg-5 control-label" for="">Date Sample Received at Testing Lab </label>
                      <span><?php echo $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']; ?> </span>
                  </div>
                  <div class="col-md-4">
                      <label class="col-lg-5 control-label" for="">Sample Testing Date </label>
                      <span><?php echo $vlQueryInfo[0]['sample_tested_datetime']; ?> </span>
                  </div>
                  <div class="col-md-4">
                      <label class="col-lg-5 control-label" for="">Date Results Dispatched </label>
                      <span><?php echo $vlQueryInfo[0]['result_dispatched_datetime']; ?> </span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <label class="col-lg-5 control-label" for="">Sample Rejection </label>
                    <span><?php echo ucfirst($vlQueryInfo[0]['is_sample_rejected']); ?> </span>
                  </div>
                  <div class="col-md-4" style="display:<?php echo($vlQueryInfo[0]['is_sample_rejected'] == 'yes')?'':'none'; ?>;">
                    <label class="col-lg-5 control-label" for="">Rejection Reason </label>
                    <span><?php echo(isset($erejectionReasonQueryResult[0]['rejection_reason_name']))?ucwords($erejectionReasonQueryResult[0]['rejection_reason_name']):''; ?></span>
                  </div>
                  <div class="col-md-4" style="visibility:<?php echo($vlQueryInfo[0]['is_sample_rejected'] == 'yes')?'hidden':'visible'; ?>;">
                    <label class="col-lg-5 control-label" for="">Viral Load Result (copiesl/ml) </label>
                    <span><?php echo $vlQueryInfo[0]['result']; ?> </span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                      <label class="col-lg-5 control-label" for="">Approved By </label>
                      <span><?php echo(isset($eapprovedUserResult[0]['user_name']))?ucwords($eapprovedUserResult[0]['user_name']):''; ?></span>
                  </div>
                  <div class="col-md-8">
                      <label class="col-lg-2 control-label" for="">Laboratory Scientist Comments </label>
                      <span><?php echo ucfirst($vlQueryInfo[0]['approver_comments']); ?> </span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <label class="col-lg-5 control-label" for="">Status</label>
                    <span><?php echo(isset($etestStatusResult[0]['status_name']))?ucwords($etestStatusResult[0]['status_name']):''; ?></span>
                  </div>
                </div>
                <?php
                if(trim($erch)!= ''){
                ?>
                  <div class="row">
                    <div class="col-md-12"><?php echo $erch; ?></div>
                  </div>
                <?php } ?>
            </div>
         </div>
        </div>
      </section>
    <?php } ?>
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <div class="box-body">
          <!-- form start -->
            <form class="form-inline" method="post" name="vlRequestFormRwd" id="vlRequestFormRwd" autocomplete="off" action="vlRequestRwdFormHelper.php">
              <div class="box-body">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Enter Sample ID" title="Please enter sample id" value="<?php echo (isset($qrVal[56]))?$qrVal[56]:''; ?>" style="width:100%;"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleReordered">
                            <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" title="Please check sample reordered" <?php echo(isset($qrVal[98]) && $qrVal[98] == 'yes')?'checked="checked"':''; ?>> Sample Reordered
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getProvinceDistricts(this);">
                            <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                              <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['facility_state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="district">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getFacilities(this);">
                            <option value=""> -- Select -- </option>
                              <?php
                              foreach($districtResult as $districtName){
                                ?>
                                <option value="<?php echo $districtName['facility_district'];?>" <?php echo ($facilityResult[0]['facility_district']==$districtName['facility_district'])?"selected='selected'":""?>><?php echo ucwords($districtName['facility_district']);?></option>
                                <?php
                              }
                             ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fName">Clinic/Health Center <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" id="fName" name="fName" title="Please select clinic/health center name" style="width:100%;" onchange="fillFacilityDetails();">
                              <option data-code="" data-emails="" data-mobile-nos="" value=""> -- Select -- </option>
                              <?php foreach($fResult as $fDetails){ ?>
                                <option data-code="<?php echo $fDetails['facility_code']; ?>" data-emails="<?php echo $fDetails['facility_emails']; ?>" data-mobile-nos="<?php echo $fDetails['facility_mobile_numbers']; ?>" data-contact-person="<?php echo ucwords($fDetails['contact_person']); ?>" value="<?php echo $fDetails['facility_id'];?>" <?php echo ($facilityResult[0]['facility_id'] ==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']);?></option>
                              <?php } ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fCode">Clinic/Health Center Code </label>
                            <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Clinic/Health Center Code" title="Please enter clinic/health center code" value="<?php echo $facilityResult[0]['facility_code']; ?>">
                          </div>
                      </div>
                    </div>
                    <div class="row facilityDetails" style="display:none;">
                      <div class="col-xs-2 col-md-2 femails" style="display:none;"><strong>Clinic Email(s) -</strong></div>
                      <div class="col-xs-2 col-md-2 femails facilityEmails" style="display:none;"></div>
                      <div class="col-xs-2 col-md-2 fmobileNumbers" style="display:none;"><strong>Clinic Mobile No.(s) -</strong></div>
                      <div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:none;"></div>
                      <div class="col-xs-2 col-md-2 fContactPerson" style="display:none;"><strong>Clinic Contact Person -</strong></div>
                      <div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:none;"></div>
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Patient Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="artNo">ART (TRACNET) No. <span class="mandatory">*</span></label>
                          <input type="text" name="artNo" id="artNo" class="form-control isRequired" placeholder="Enter ART Number" title="Enter art number" value="<?php echo (isset($qrVal[16]))?$qrVal[16]:''; ?>"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="dob">Date of Birth </label>
                          <input type="text" name="dob" id="dob" class="form-control date" placeholder="Enter DOB" title="Enter dob" value="<?php echo $dob; ?>" onchange="getDateOfBirth();"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="ageInYears">If DOB unknown, Age in Year </label>
                            <input type="text" name="ageInYears" id="ageInYears" class="form-control checkNum" maxlength="2" placeholder="Age in Year" title="Enter age in years" value="<?php echo (isset($qrVal[24]))?$qrVal[24]:''; ?>"/>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="ageInMonths">If Age < 1, Age in Month </label>
                            <input type="text" name="ageInMonths" id="ageInMonths" class="form-control checkNum" maxlength="2" placeholder="Age in Month" title="Enter age in months" value="<?php echo (isset($qrVal[25]))?$qrVal[25]:''; ?>"/>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="patientFirstName">Patient Name </label>
                            <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter Patient Name" title="Enter patient name" value="<?php echo (isset($qrVal[20]))?$qrVal[20]:''; ?>"/>
                          </div>
                      </div>  
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="gender">Gender</label><br>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo(isset($qrVal[23]) && strtolower($qrVal[23]) == 'male')?'checked="checked"':''; ?>>Male
                          </label>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo(isset($qrVal[23]) && strtolower($qrVal[23]) == 'female')?'checked="checked"':''; ?>>Female
                          </label>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo(isset($qrVal[23]) && strtolower($qrVal[23]) == 'not_recorded')?'checked="checked"':''; ?>>Not Recorded
                          </label>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="patientPhoneNumber">Phone Number</label>
                          <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control checkNum" maxlength="15" placeholder="Enter Phone Number" title="Enter phone number" value="<?php echo (isset($qrVal[27]))?$qrVal[27]:''; ?>"/>
                        </div>
                      </div>
                   </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Sample Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Sample Collection <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" value="<?php echo $sampleCollectionDate; ?>">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                          <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose sample type">
                                <option value=""> -- Select -- </option>
                                <?php
                                foreach($sResult as $name){
                                 ?>
                                 <option value="<?php echo $name['sample_id'];?>" <?php echo(isset($specimenResult[0]['sample_id']) && $specimenResult[0]['sample_id'] == $name['sample_id'])?'selected="selected"':''; ?>><?php echo ucwords($name['sample_name']);?></option>
                                 <?php
                                }
                                ?>
                            </select>
                          </div>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Treatment Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Treatment Initiation</label>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of Treatment Initiated" title="Date Of treatment initiated" value="<?php echo $treatmentInitiatedDate; ?>" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="artRegimen">Current Regimen</label>
                            <select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTValue();">
                                 <option value=""> -- Select -- </option>
                                 <?php
                                 foreach($aResult as $regimen){
                                 ?>
                                  <option value="<?php echo $regimen['art_code']; ?>" <?php echo(isset($qrVal[96]) && $qrVal[96] == $regimen['art_code'])?'selected="selected"':''; ?>><?php echo $regimen['art_code']; ?></option>
                                 <?php
                                 }
                                 ?>
                                 <option value="other" <?php echo(isset($qrVal[96]) && trim($qrVal[96])!='' && $qrVal[96]!= null && count($aCheckResult) == 0)?'selected="selected"':''; ?>>Other</option>
                            </select>
                            <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" value="<?php echo(isset($qrVal[96]) && trim($qrVal[96])!='' && $qrVal[96]!= null && count($aCheckResult) == 0)?$qrVal[96]:''; ?>" style="width:100%;display:<?php echo(isset($qrVal[96]) && trim($qrVal[96])!='' && $qrVal[96]!= null && count($aCheckResult) == 0)?'':'none'; ?>;margin-top:2px;" >
                          </div>
                       </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Initiation of Current Regimen </label>
                          <input type="text" class="form-control date" style="width:100%;" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" value="<?php echo $dateOfInitiationOfCurrentRegimen; ?>">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="arvAdherence">ARV Adherence </label>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose adherence">
                            <option value=""> -- Select -- </option>
                            <option value="good" <?php echo(isset($qrVal[51]) && $qrVal[51]== 'good')?'selected="selected"':''; ?>>Good >= 95%</option>
                            <option value="fair" <?php echo(isset($qrVal[51]) && $qrVal[51]== 'fair')?'selected="selected"':''; ?>>Fair (85-94%)</option>
                            <option value="poor" <?php echo(isset($qrVal[51]) && $qrVal[51]== 'poor')?'selected="selected"':''; ?>>Poor < 85%</option>
                           </select>
                        </div>
                      </div>
                    </div>
                    <div class="row femaleSection" style="display:<?php echo($qrVal[23] == 'female' || $qrVal[23] == '' || $qrVal[23] == null)?'':'none'; ?>">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="patientPregnant">Is Patient Pregnant? </label><br>
                          <label class="radio-inline">
                            <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check one" <?php echo(isset($qrVal[35]) && $qrVal[35] == 'yes')?'checked="checked"':''; ?>> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" <?php echo(isset($qrVal[35]) && $qrVal[35] == 'no')?'checked="checked"':''; ?>> No
                          </label>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="breastfeeding">Is Patient Breastfeeding? </label><br>
                          <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check one" <?php echo(isset($qrVal[36]) && $qrVal[36] == 'yes')?'checked="checked"':''; ?>> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" <?php echo(isset($qrVal[36]) && $qrVal[36] == 'no')?'checked="checked"':''; ?>> No
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                       <h3 class="box-title">Indication for Viral Load Testing</h3><small> (Please tick one):(To be completed by clinician)</small>
                    </div>
                    <div class="box-body">
                      <div class="row">                
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <?php
                                    $checked = '';
                                    $display = '';
                                    if(isset($qrVal[34]) && $qrVal[34] =='routine'){
                                      $checked = 'checked="checked"';
                                      $display = 'block';
                                    }else{
                                      $checked = '';
                                      $display = 'none';
                                    }
                                    $rmLastVLTestingDate = '';
                                    if(isset($qrVal[39]) && trim($qrVal[39])!='' && $qrVal[39]!= null && $qrVal[39]!='0000-00-00'){
                                      $rmLastVLTestingDate = $general->humanDateFormat($qrVal[39]);
                                    }
                                    ?>
                                    <input type="radio" class="" id="rmTesting" name="stViralTesting" value="routine" title="Please check routine monitoring" <?php echo $checked;?> onclick="showTesting('rmTesting');">
                                    <strong>Routine Monitoring</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="row rmTesting hideTestData" style="display:<?php echo $display;?>;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData" id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $rmLastVLTestingDate; ?>"/>
                         </div>
                        </div>
                        <div class="col-md-6">
                             <label for="rmTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control checkNum viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo(isset($qrVal[40]) && $qrVal[40]!= '')?$qrVal[40]:''; ?>"/>
                             (copies/ml)
                         </div>
                       </div>                 
                      </div>
                      <div class="row">                
                        <div class="col-md-8">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <?php
                                    $checked = '';
                                    $display = '';
                                    if(isset($qrVal[34]) && trim($qrVal[34]) =='failure'){
                                      $checked = 'checked="checked"';
                                      $display = 'block';
                                    }else{
                                      $checked = '';
                                      $display = 'none';
                                    }
                                    $repeatVLTestingDate = '';
                                    if(isset($qrVal[42]) && trim($qrVal[42])!='' && $qrVal[42]!= null &&  $qrVal[42]!='0000-00-00'){
                                      $repeatVLTestingDate = $general->humanDateFormat($qrVal[42]);
                                    }
                                    ?>
                                    <input type="radio" class="" id="repeatTesting" name="stViralTesting" value="failure" title="Repeat VL test after suspected treatment failure adherence counseling" <?php echo $checked; ?> onclick="showTesting('repeatTesting');">
                                    <strong>Repeat VL test after suspected treatment failure adherence counselling </strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                     </div>
                     <div class="row repeatTesting hideTestData" style="display:<?php echo $display;?>;">
                       <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control date viralTestData" id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $repeatVLTestingDate; ?>"/>
                            </div>
                      </div>
                       <div class="col-md-6">
                            <label for="repeatTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control checkNum viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo(isset($qrVal[43]) && $qrVal[43]!= '')?$qrVal[43]:''; ?>"/>
                            (copies/ml)
                            </div>
                      </div>                 
                     </div>
                     <div class="row">         
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <?php
                                    $checked = '';
                                    $display = '';
                                    if(isset($qrVal[34]) && trim($qrVal[34]) =='suspect'){
                                      $checked = 'checked="checked"';
                                      $display = 'block';
                                    }else{
                                      $checked = '';
                                      $display = 'none';
                                    }
                                    $suspendTreatmentLastVLTestingDate = '';
                                    if(isset($qrVal[45]) && trim($qrVal[45])!='' && $qrVal[45]!= null &&  $qrVal[45]!='0000-00-00'){
                                      $suspendTreatmentLastVLTestingDate = $general->humanDateFormat($qrVal[45]);
                                    }
                                    ?>
                                    <input type="radio" class="" id="suspendTreatment" name="stViralTesting" value="suspect" title="Suspect Treatment Failure" <?php echo $checked; ?> onclick="showTesting('suspendTreatment');">
                                    <strong>Suspect Treatment Failure</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                     </div>
                     <div class="row suspendTreatment hideTestData" style="display:<?php echo $display;?>;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData" id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $suspendTreatmentLastVLTestingDate; ?>"/>
                             </div>
                       </div>
                        <div class="col-md-6">
                             <label for="suspendTreatmentVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control checkNum viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo(isset($qrVal[46]) && $qrVal[46]!= '')?$qrVal[46]:''; ?>"/>
                             (copies/ml)
                             </div>
                       </div>                 
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <label for="reqClinician" class="col-lg-5 control-label">Request Clinician</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control" id="reqClinician" name="reqClinician" placeholder="Request Clinician" title="Please enter request clinician" value="<?php echo(isset($qrVal[6]) && $qrVal[6]!= '')?$qrVal[6]:''; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="reqClinicianPhoneNumber" class="col-lg-5 control-label">Phone Number</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control checkNum" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter request clinician phone number" value="<?php echo(isset($qrVal[7]) && $qrVal[7]!= '')?$qrVal[7]:''; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="requestDate">Request Date </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control date" id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date" value="<?php echo $testRequestedOn; ?>"/>
                            </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <label for="vlFocalPerson" class="col-lg-5 control-label">VL Focal Person </label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter vl focal person name" value="<?php echo(isset($qrVal[29]) && $qrVal[29]!= '')?$qrVal[29]:''; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="vlFocalPersonPhoneNumber" class="col-lg-5 control-label">VL Focal Person Phone Number</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control checkNum" id="vlFocalPersonPhoneNumber" name="vlFocalPersonPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter vl focal person phone number" value="<?php echo(isset($qrVal[30]) && $qrVal[30]!= '')?$qrVal[30]:''; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="emailHf">Email for HF </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control isEmail" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" value="<?php echo $facilityResult[0]['facility_emails']; ?>"/>
                            </div>
                        </div>
                     </div>
                    </div>
                  </div>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      <h3 class="box-title">Laboratory Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-md-4">
                            <label for="labId" class="col-lg-5 control-label">Lab Name </label>
                            <div class="col-lg-7">
                              <select name="labId" id="labId" class="form-control labSection" title="Please choose lab">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($lResult as $labName){
                                  ?>
                                  <option value="<?php echo $labName['facility_id'];?>" <?php echo(isset($labResult[0]['facility_id']) && $labResult[0]['facility_id'] == $labName['facility_id'])?'selected="selected"':''; ?>><?php echo ucwords($labName['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="testingPlatform" class="col-lg-5 control-label">VL Testing Platform </label>
                            <div class="col-lg-7">
                              <select name="testingPlatform" id="testingPlatform" class="form-control labSection" title="Please choose VL Testing Platform">
                                <option value="">-- Select --</option>
                                <?php foreach($importResult as $mName) { ?>
                                  <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>" <?php echo(isset($qrVal[63]) && $qrVal[63] == $mName['machine_name'])?'selected="selected"':''; ?>><?php echo $mName['machine_name'];?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="sampleReceivedOn">Date Sample Received at Testing Lab </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received Date" title="Please select sample received date" value="<?php echo $sampleReceivedAtTestingLabDate; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="sampleTestingDateAtLab">Sample Testing Date </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date" value="<?php echo $sampleTestingDate; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="resultDispatchedOn">Date Results Dispatched </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date" value="<?php echo $resultDispatchedDateTime; ?>"/>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="noResult">Sample Rejection </label>
                            <div class="col-lg-7">
                              <label class="radio-inline">
                               <input class="labSection" id="noResultYes" name="noResult" value="yes" title="Please check one" type="radio" <?php echo(isset($qrVal[58]) && $qrVal[58] == 'yes')?'selected="selected"':''; ?>> Yes
                              </label>
                              <label class="radio-inline">
                               <input class="labSection" id="noResultNo" name="noResult" value="no" title="Please check one" type="radio" <?php echo(isset($qrVal[58]) && $qrVal[58] == 'no')?'selected="selected"':''; ?>> No
                              </label>
                            </div>
                        </div>
                        <div class="col-md-4 rejectionReason" style="display:<?php echo(isset($qrVal[58]) && $qrVal[58] == 'yes')?'':'none'; ?>;">
                            <label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason </label>
                            <div class="col-lg-7">
                              <select name="rejectionReason" id="rejectionReason" class="form-control labSection" title="Please choose reason" onchange="checkRejectionReason();">
                                <option value="">-- Select --</option>
                                <?php foreach($rejectionTypeResult as $type) { ?>
                                <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                  <?php
                                  foreach($rejectionResult as $reject){
                                    if($type['rejection_type'] == $reject['rejection_type']){
                                    ?>
                                    <option value="<?php echo $reject['rejection_reason_id'];?>" <?php echo(isset($rejectionReasonQueryResult[0]['rejection_reason_id']) && $rejectionReasonQueryResult[0]['rejection_reason_id'] == $reject['rejection_reason_id'])?'selected="selected"':''; ?>><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                    <?php
                                    }
                                  }
                                  ?>
                                </optgroup>
                                <?php } ?>
                                <option value="other" <?php echo(isset($qrVal[60]) && trim($qrVal[60])!='' && $qrVal[60]!= null && count($rejectionReasonQueryResult) == 0)?'selected="selected"':''; ?>>Other (Please Specify) </option>
                              </select>
                              <input type="text" class="form-control labSection newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" vaue="<?php echo(isset($qrVal[60]) && trim($qrVal[60])!='' && $qrVal[60]!= null && count($rejectionReasonQueryResult) == 0)?$qrVal[60]:''; ?>" style="width:100%;display:<?php echo(isset($qrVal[60]) && trim($qrVal[60])!='' && $qrVal[60]!= null && count($rejectionReasonQueryResult) == 0)?'':'none'; ?>;margin-top:2px;">
                            </div>
                        </div>
                        <div class="col-md-4 vlResult" style="visibility:<?php echo(isset($qrVal[58]) && $qrVal[58] == 'yes')?'hidden':'visible'; ?>;">
                            <label class="col-lg-5 control-label" for="vlResult">Viral Load Result (copiesl/ml) </label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control labSection" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" value="<?php echo(isset($qrVal[75]) && $qrVal[75]!= '')?$qrVal[75]:''; ?>" <?php echo(isset($qrVal[77]) && $qrVal[77] == 'Target Not Detected')?'readonly="readonly"':''; ?> style="width:100%;" />
                              <input type="checkbox" class="labSection" id="tnd" name="tnd" value="yes" <?php echo(isset($qrVal[77]) && $qrVal[77] == 'Target Not Detected')?'checked="checked"':''; ?> title="Please check tnd"> Target Not Detected
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
                            <div class="col-lg-7">
                              <select name="approvedBy" id="approvedBy" class="form-control labSection" title="Please choose approved by">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($userResult as $uName){
                                  ?>
                                  <option value="<?php echo $uName['user_id'];?>" <?php echo (isset($approvedUserResult[0]['user_id']) && $approvedUserResult[0]['user_id']==$uName['user_id'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="col-lg-2 control-label" for="labComments">Laboratory Scientist Comments </label>
                            <div class="col-lg-10">
                              <textarea class="form-control labSection" name="labComments" id="labComments" placeholder="Lab comments" style="width:100%"><?php echo(isset($qrVal[78]) && trim($qrVal[78])!= '')?$qrVal[78]:''; ?></textarea>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="status">Status <span class="mandatory">*</span></label>
                            <div class="col-lg-7">
                              <select class="form-control labSection isRequired" id="status" name="status" title="Please select test status">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($statusResult as $status){
                                ?>
                                  <option value="<?php echo $status['status_id']; ?>" <?php echo(isset($testStatusResult[0]['status_id']) && $testStatusResult[0]['status_id'] == $status['status_id'])?'selected="selected"':''; ?>><?php echo ucwords($status['status_name']); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-8 reasonForResultChanges" style="visibility:hidden;">
                            <label class="col-lg-2 control-label" for="reasonForResultChanges">Reason For Changes in Result </label>
                            <div class="col-lg-10">
                              <textarea class="form-control" name="reasonForResultChanges" id="reasonForResultChanges" placeholder="Enter Reason For Result Changes" title="Please enter reason for result changes" style="width:100%;"></textarea>
                            </div>
                        </div>
                      </div>
                      <?php
                      if(trim($rch)!= ''){
                      ?>
                        <div class="row">
                          <div class="col-md-12"><?php echo $rch; ?></div>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
               </div>
              <div class="box-footer">
                <input type="hidden" name="vlSampleCode" id="vlSampleCode" value="<?php echo $qrVal[56]; ?>"/>
                <input type="hidden" name="reasonForResultChangesHistory" id="reasonForResultChangesHistory" value="<?php echo $qrVal[100]; ?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>&nbsp;
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
              </div>
            </form>
      </div>
    </section>
  </div>
  <script>
    provinceName = true;
    facilityName = true;
    $(document).ready(function() {
        $('.date').datepicker({
           changeMonth: true,
           changeYear: true,
           dateFormat: 'dd-M-yy',
           timeFormat: "hh:mm TT",
           maxDate: "Today",
           yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
          }).click(function(){
              $('.ui-datepicker-calendar').show();
         });
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd-M-yy',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                  setTimeout(function() {
                     $('.ui-datepicker-calendar').show();
                  });
            },
            yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
            }).click(function(){
               $('.ui-datepicker-calendar').show();
            });
        $('.date').mask('99-aaa-9999');
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').mask('99-aaa-9999 99:99');
        getDateOfBirth();
        __clone = $("#vlRequestFormRwd .labSection").clone();
        reason = ($("#reasonForResultChanges").length)?$("#reasonForResultChanges").val():'';
        result = ($("#vlResult").length)?$("#vlResult").val():'';
    });
    
    function showTesting(chosenClass){
      $(".viralTestData").val('');
      $(".hideTestData").hide();
      $("."+chosenClass).show();
    }
    
    function getProvinceDistricts(obj){
      $.blockUI();
      var cName = $("#fName").val();
      var pName = $("#province").val();
      if(pName!='' && provinceName && facilityName){
        facilityName = false;
      }
      if(pName!=''){
        if(provinceName){
        $.post("../includes/getFacilityForClinic.php", { pName : pName},
        function(data){
            if(data != ""){
              details = data.split("###");
              $("#district").html(details[1]);
              $("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
              $(".facilityDetails").hide();
              $(".facilityEmails").html('');
              $(".facilityMobileNumbers").html('');
              $(".facilityContactPerson").html('');
            }
        });
        }
        
      }else if(pName=='' && cName==''){
        provinceName = true;
        facilityName = true;
        $("#province").html("<?php echo $province;?>");
        $("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
      }
      $.unblockUI();
  }
  
  function getFacilities(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#fName").val();
    if(dName!=''){
      $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
	  if(data != ""){
            $("#fName").html(data);
            $(".facilityDetails").hide();
            $(".facilityEmails").html('');
            $(".facilityMobileNumbers").html('');
            $(".facilityContactPerson").html('');
	  }
      });
    }
    $.unblockUI();
  }
  
  function fillFacilityDetails(){
    $("#fCode").val($('#fName').find(':selected').data('code'));
    var femails = $('#fName').find(':selected').data('emails');
    var fmobilenos = $('#fName').find(':selected').data('mobile-nos');
    var fContactPerson = $('#fName').find(':selected').data('contact-person');
    if($.trim(femails) !='' || $.trim(fmobilenos) !='' || fContactPerson != ''){
      $(".facilityDetails").show();
    }else{
      $(".facilityDetails").hide();
    }
    ($.trim(femails) !='')?$(".femails").show():$(".femails").hide();
    ($.trim(femails) !='')?$(".facilityEmails").html(femails):$(".facilityEmails").html('');
    ($.trim(fmobilenos) !='')?$(".fmobileNumbers").show():$(".fmobileNumbers").hide();
    ($.trim(fmobilenos) !='')?$(".facilityMobileNumbers").html(fmobilenos):$(".facilityMobileNumbers").html('');
    ($.trim(fContactPerson) !='')?$(".fContactPerson").show():$(".fContactPerson").hide();
    ($.trim(fContactPerson) !='')?$(".facilityContactPerson").html(fContactPerson):$(".facilityContactPerson").html('');
  }
  
  $("input:radio[name=gender]").click(function() {
    if($(this).val() == 'male' || $(this).val() == 'not_recorded'){
      $('.femaleSection').hide();
      $('input[name="breastfeeding"]').prop('checked', false);
      $('input[name="patientPregnant"]').prop('checked', false);
    }else if($(this).val() == 'female'){
      $('.femaleSection').show();
    }
  });
  
  $("input:radio[name=noResult]").click(function() {
    if($(this).val() == 'yes'){
      $('.rejectionReason').show();
      $('.vlResult').css('visibility','hidden');
      $('#rejectionReason').addClass('isRequired');
    }else{
      $('.vlResult').css('visibility','visible');
      $('.rejectionReason').hide();
      $('#rejectionReason').removeClass('isRequired');
      $('#rejectionReason').val('');
    }
  });
  
  $('#tnd').change(function() {
    if($('#tnd').is(':checked')){
      $('#vlResult').attr('readonly',true);
    }else{
      $('#vlResult').attr('readonly',false);
    }
  });
  
  $('#vlResult').on('input',function(e){
    if(this.value != ''){
      $('#tnd').attr('disabled',true);
    }else{
      $('#tnd').attr('disabled',false);
    }
  });
  
  function checkARTValue(){
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $("#newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $("#newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
      $('#newArtRegimen').val("");
    }
  }
  
  function getDateOfBirth(){
      var today = new Date();
      var dob = $("#dob").val();
      if($.trim(dob) == ""){
        $("#ageInMonths").val("");
        $("#ageInYears").val("");
        return false;
      }
      
      var dd = today.getDate();
      var mm = today.getMonth();
      var yyyy = today.getFullYear();
      if(dd<10) {
        dd='0'+dd
      }
      if(mm<10) {
       mm='0'+mm
      }
      
      splitDob = dob.split("-");
      var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
      var monthDigit = dobDate.getMonth();
      var dobYear = splitDob[2];
      var dobMonth = isNaN(monthDigit) ? 0 : (monthDigit);
      dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
      var dobDate = (splitDob[0]<10) ? '0'+splitDob[0]: splitDob[0];
      
      var date1 = new Date(yyyy,mm,dd);
      var date2 = new Date(dobYear,dobMonth,dobDate);
      var diff = new Date(date1.getTime() - date2.getTime());
      if((diff.getUTCFullYear() - 1970) == 0){
        $("#ageInMonths").val(diff.getUTCMonth()); // Gives month count of difference
      }else{
        $("#ageInMonths").val("");
      }
      $("#ageInYears").val((diff.getUTCFullYear() - 1970)); // Gives difference as year
      //console.log(diff.getUTCDate() - 1); // Gives day count of difference
  }
  
  $("#vlRequestFormRwd .labSection").on("change", function() {
      if($.trim(result)!= ''){
        if($("#vlRequestFormRwd .labSection").serialize() == $(__clone).serialize()){
          $(".reasonForResultChanges").css("visibility","hidden");
          $("#reasonForResultChanges").removeClass("isRequired");
        }else{
          $(".reasonForResultChanges").css("visibility","visible");
          $("#reasonForResultChanges").addClass("isRequired");
        }
      }
  });
  
  function checkRejectionReason(){
    var rejectionReason = $("#rejectionReason").val();
    if(rejectionReason == "other"){
      $("#newRejectionReason").show();
      $("#newRejectionReason").addClass("isRequired");
    }else{
      $("#newRejectionReason").hide();
      $("#newRejectionReason").removeClass("isRequired");
      $('#newRejectionReason').val("");
    }
  }
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestFormRwd'
    });
    
    $('.isRequired').each(function () {
      ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
    });
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestFormRwd').submit();
    }
  }
  </script>