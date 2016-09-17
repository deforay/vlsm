<?php
ob_start();
include('header.php');

$configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    if($arr['vl_form']==1){
     include('defaultaddVlRequest.php');
    }
    if($arr['vl_form']==2){
     include('addVlRequestZm.php');
    }

include('footer.php');
 ?>
