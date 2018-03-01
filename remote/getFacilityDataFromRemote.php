<?php
//this file in remote
include(dirname(__FILE__) . "/../includes/MysqliDb.php");
$facilityMapQuery = "SELECT facility_id FROM vl_facility_map";
$fMapResult=$db->query($facilityMapQuery);
if(count($fMapResult)>0){
  $fMapResult = array_map('current', $fMapResult);
}else{
    $fMapResult = "";
}
echo json_encode($fMapResult);
?>