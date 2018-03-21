<?php
ob_start();
$title = "VLSM | Enter VL Result";
include('../header.php');
include('../General.php');
$general = new Deforay_Commons_General();
$id = base64_decode($_GET['id']);
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}

//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);

$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);

//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2' AND status ='active'";
$lResult = $db->rawQuery($lQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons where rejection_reason_status = 'active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
//sample status
$statusQuery="SELECT * FROM r_sample_status where status = 'active' AND status_id NOT IN(9,8,6)";
$statusResult = $db->rawQuery($statusQuery);

$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);

$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);

//get vl test reason list
$vlTestReasonQuery="SELECT * from r_vl_test_reasons where test_reason_status = 'active'";
$vlTestReasonResult=$db->query($vlTestReasonQuery);

//get suspected treatment failure at
$suspectedTreatmentFailureAtQuery="SELECT DISTINCT vl_sample_suspected_treatment_failure_at FROM vl_request_form where vlsm_country_id='".$arr['vl_form']."'";
$suspectedTreatmentFailureAtResult = $db->rawQuery($suspectedTreatmentFailureAtQuery);

$vlQuery="SELECT * from vl_request_form where vl_sample_id=$id";
$vlQueryInfo=$db->query($vlQuery);

if(isset($vlQueryInfo[0]['patient_dob']) && trim($vlQueryInfo[0]['patient_dob'])!='' && $vlQueryInfo[0]['patient_dob']!='0000-00-00'){
 $vlQueryInfo[0]['patient_dob']=$general->humanDateFormat($vlQueryInfo[0]['patient_dob']);
}else{
 $vlQueryInfo[0]['patient_dob']='';
}

if(isset($vlQueryInfo[0]['sample_collection_date']) && trim($vlQueryInfo[0]['sample_collection_date'])!='' && $vlQueryInfo[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_collection_date']);
 $vlQueryInfo[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_collection_date']='';
}

if(isset($vlQueryInfo[0]['treatment_initiated_date']) && trim($vlQueryInfo[0]['treatment_initiated_date'])!='' && $vlQueryInfo[0]['treatment_initiated_date']!='0000-00-00'){
 $vlQueryInfo[0]['treatment_initiated_date']=$general->humanDateFormat($vlQueryInfo[0]['treatment_initiated_date']);
}else{
 $vlQueryInfo[0]['treatment_initiated_date']='';
}

if(isset($vlQueryInfo[0]['date_of_initiation_of_current_regimen']) && trim($vlQueryInfo[0]['date_of_initiation_of_current_regimen'])!='' && $vlQueryInfo[0]['date_of_initiation_of_current_regimen']!='0000-00-00'){
 $vlQueryInfo[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($vlQueryInfo[0]['date_of_initiation_of_current_regimen']);
}else{
 $vlQueryInfo[0]['date_of_initiation_of_current_regimen']='';
}

if(isset($vlQueryInfo[0]['test_requested_on']) && trim($vlQueryInfo[0]['test_requested_on'])!='' && $vlQueryInfo[0]['test_requested_on']!='0000-00-00'){
 $vlQueryInfo[0]['test_requested_on']=$general->humanDateFormat($vlQueryInfo[0]['test_requested_on']);
}else{
 $vlQueryInfo[0]['test_requested_on']='';
}

if(isset($vlQueryInfo[0]['sample_received_at_vl_lab_datetime']) && trim($vlQueryInfo[0]['sample_received_at_vl_lab_datetime'])!='' && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_received_at_vl_lab_datetime']);
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']='';
}

if(isset($vlQueryInfo[0]['sample_tested_datetime']) && trim($vlQueryInfo[0]['sample_tested_datetime'])!='' && $vlQueryInfo[0]['sample_tested_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_tested_datetime']);
 $vlQueryInfo[0]['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_tested_datetime']='';
}

if(isset($vlQueryInfo[0]['result_dispatched_datetime']) && trim($vlQueryInfo[0]['result_dispatched_datetime'])!='' && $vlQueryInfo[0]['result_dispatched_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['result_dispatched_datetime']);
 $vlQueryInfo[0]['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['result_dispatched_datetime']='';
}
if(isset($vlQueryInfo[0]['last_viral_load_date']) && trim($vlQueryInfo[0]['last_viral_load_date'])!='' && $vlQueryInfo[0]['last_viral_load_date']!='0000-00-00'){
 $vlQueryInfo[0]['last_viral_load_date']=$general->humanDateFormat($vlQueryInfo[0]['last_viral_load_date']);
}else{
 $vlQueryInfo[0]['last_viral_load_date']='';
}
//Set Date of demand
if(isset($vlQueryInfo[0]['date_test_ordered_by_physician']) && trim($vlQueryInfo[0]['date_test_ordered_by_physician'])!='' && $vlQueryInfo[0]['date_test_ordered_by_physician']!='0000-00-00'){
  $vlQueryInfo[0]['date_test_ordered_by_physician']=$general->humanDateFormat($vlQueryInfo[0]['date_test_ordered_by_physician']);
}else{
  $vlQueryInfo[0]['date_test_ordered_by_physician']='';
}
//Has patient changed regimen section
if(trim($vlQueryInfo[0]['has_patient_changed_regimen']) == "yes"){
  if(isset($vlQueryInfo[0]['regimen_change_date']) && trim($vlQueryInfo[0]['regimen_change_date'])!='' && $vlQueryInfo[0]['regimen_change_date']!='0000-00-00'){
    $vlQueryInfo[0]['regimen_change_date']=$general->humanDateFormat($vlQueryInfo[0]['regimen_change_date']);
  }else{
    $vlQueryInfo[0]['regimen_change_date']='';
  }
}else{
  $vlQueryInfo[0]['reason_for_regimen_change'] = '';
  $vlQueryInfo[0]['regimen_change_date'] = '';
}
//Set Dispatched From Clinic To Lab Date
if(isset($vlQueryInfo[0]['date_dispatched_from_clinic_to_lab']) && trim($vlQueryInfo[0]['date_dispatched_from_clinic_to_lab'])!='' && $vlQueryInfo[0]['date_dispatched_from_clinic_to_lab']!='0000-00-00 00:00:00'){
   $expStr=explode(" ",$vlQueryInfo[0]['date_dispatched_from_clinic_to_lab']);
   $vlQueryInfo[0]['date_dispatched_from_clinic_to_lab']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
 }else{
   $vlQueryInfo[0]['date_dispatched_from_clinic_to_lab']='';
 }
 //Set Date of result printed datetime
if(isset($vlQueryInfo[0]['result_printed_datetime']) && trim($vlQueryInfo[0]['result_printed_datetime'])!="" && $vlQueryInfo[0]['result_printed_datetime']!='0000-00-00 00:00:00'){
  $expStr = explode(" ",$vlQueryInfo[0]['result_printed_datetime']);
  $vlQueryInfo[0]['result_printed_datetime'] = $general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
  $vlQueryInfo[0]['result_printed_datetime'] = '';
}
//reviewed datetime
if(isset($vlQueryInfo[0]['result_reviewed_datetime']) && trim($vlQueryInfo[0]['result_reviewed_datetime'])!='' && $vlQueryInfo[0]['result_reviewed_datetime']!= null && $vlQueryInfo[0]['result_reviewed_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['result_reviewed_datetime']);
 $vlQueryInfo[0]['result_reviewed_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['result_reviewed_datetime']='';
}
?>
    <style>
      :disabled {background:white !important;}
      .ui_tpicker_second_label {
       display: none !important;
      }.ui_tpicker_second_slider {
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
    </style>
    <?php
    if($arr['vl_form']==1){
     include('defaultupdateVlTestResult.php');
    }else if($arr['vl_form']==2){
     include('updateVlTestResultZm.php');
    }else if($arr['vl_form']==3){
     include('updateVlTestResultDrc.php');
    }else if($arr['vl_form']==4){
     include('updateVlTestResultZam.php');
    }else if($arr['vl_form']==5){
     include('updateVlTestResultPng.php');
    }else if($arr['vl_form']==6){
     include('updateVlTestResultWho.php');
    }else if($arr['vl_form']==7){
     include('updateVlTestResultRwd.php');
    }else if($arr['vl_form']==8){
     include('updateVlTestResultAng.php');
    }
include('../footer.php');
 ?>