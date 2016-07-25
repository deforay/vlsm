<?php
ob_start();
include('./includes/MysqliDb.php');
$strSearch=$_GET['q'];
$facilityQuery="SELECT * from vl_request_form where (art_no like '%$strSearch%')";
$facilityInfo=$db->query($facilityQuery);
        $echoResult = array();
        foreach ($facilityInfo as $row) {
            $echoResult[] = array("id" => $row['art_no'], "text" => ucwords($row['art_no']));
        }
        if (count($echoResult) == 0) {
            $echoResult[] = array("id" => $strSearch, "text" => $strSearch);
        }
        echo json_encode(array("result" => $echoResult));
?>