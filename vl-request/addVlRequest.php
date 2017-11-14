<?php
ob_start();
$title = "VLSM | Add New Request";
include('../header.php');
    $configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
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
