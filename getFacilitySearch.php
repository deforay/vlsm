<?php
ob_start();
include('./includes/MysqliDb.php');
$strSearch=$_GET['q'];
$facilityQuery="SELECT * from facility_details where (facility_name like '%$strSearch%' OR facility_code like '%$strSearch%') AND status='active'";
$facilityInfo=$db->query($facilityQuery);
        $echoResult = array();
        foreach ($facilityInfo as $row) {
            $echoResult[] = array("id" => $row['facility_id'], "text" => ucwords($row['facility_name']) . ' - ' . $row['facility_code'],"facilityCode"=>$row['facility_code'],"country"=>$row['country'],"state"=>$row['state'],"hubName"=>$row['hub_name']);
        }
        if (count($echoResult) == 0) {
            $echoResult[] = array("id" => "0", "text" => $strSearch);
        }
        echo json_encode(array("result" => $echoResult));
?>