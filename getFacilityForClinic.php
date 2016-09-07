<?php
ob_start();
include('./includes/MysqliDb.php');
$id=$_POST['cName'];
$facilityQuery="SELECT * from facility_details where facility_id=$id";
$facilityInfo=$db->query($facilityQuery);
if($facilityInfo){
    echo $facilityInfo[0]['state']."##".$facilityInfo[0]['district']."##".$facilityInfo[0]['contact_person'];
}else{
    echo "";
}
?>