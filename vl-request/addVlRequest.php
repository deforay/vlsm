<?php
ob_start();
$title = "VLSM | Add New Request";
include('../header.php');
include('../General.php');
$general=new Deforay_Commons_General();
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
  //get active sample types
  $sQuery="SELECT * from r_sample_type where status='active'";
  $sResult=$db->query($sQuery);
  $fQuery="SELECT * FROM facility_details where status='active'";
  $fResult = $db->rawQuery($fQuery);
  //get vltest reason details
  $testRQuery="SELECT * FROM r_vl_test_reasons";
  $testReason = $db->rawQuery($testRQuery);
  $pdQuery="SELECT * from province_details";
  $pdResult=$db->query($pdQuery);
  //get suspected treatment failure at
  $suspectedTreatmentFailureAtQuery="SELECT DISTINCT vl_sample_suspected_treatment_failure_at FROM vl_request_form where vlsm_country_id='".$arr['vl_form']."'";
  $suspectedTreatmentFailureAtResult = $db->rawQuery($suspectedTreatmentFailureAtQuery);
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
</style>
    <?php
    if($arr['vl_form']==1){
     include('defaultaddVlRequest.php');
    }else if($arr['vl_form']==2){
     include('addVlRequestZm.php');
    }else if($arr['vl_form']==3){
      include('addVlRequestDrc.php');
    }else if($arr['vl_form']==4){
      include('addVlRequestZam.php');
    }else if($arr['vl_form']==5){
      include('addVlRequestPng.php');
    }else if($arr['vl_form']==6){
      include('addVlRequestWho.php');
    }else if($arr['vl_form']==7){
      include('addVlRequestRwd.php');
    }else if($arr['vl_form']==8){
      include('addVlRequestAng.php');
    }
include('../footer.php');
 ?>
