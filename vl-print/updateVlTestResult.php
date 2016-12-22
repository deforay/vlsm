<?php
ob_start();
include('../header.php');
$id=base64_decode($_GET['id']);
$configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    if($arr['vl_form']==1){
     include('defaultupdateVlTestResult.php');
    }else if($arr['vl_form']==2){
     include('updateVlTestResultZm.php');
    }else if($arr['vl_form']==3){
     include('updateVlTestResultDrc.php');
    }else if($arr['vl_form']==4){
     include('updateVlTestResultZam.php');
    }
include('../footer.php');
 ?>
