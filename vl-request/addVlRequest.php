<?php
ob_start();
include('../header.php');

$configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
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
      include('addVlRequestMz.php');
    }
include('../footer.php');
 ?>
