<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
#require_once('../startup.php');  
$params     = $_POST['facilityType'];
$testType   = $_POST['testType'];

if($params == "yes"){
    $tableName ="testing_labs";
} else{
    $tableName = "health_facilities";
}
/* To take the facility details */
$facilityQuery = "SELECT facility_id, facility_name FROM facility_details WHERE status = 'active'";
$facilityResult = $db->query($facilityQuery);

$query = "SELECT test_type, facility_id from $tableName where test_type = '$testType'";
$result = $db->query($query);

$inArray = array();
foreach($result as $row){
    if($row['test_type'] == $testType){
        $inArray[] = $row['facility_id'];
    }
}

$html = "";
foreach($facilityResult as $facility){
    $selected = (in_array($facility['facility_id'],$inArray))?"selected='selected'":"";

    $html.= '<option value="'.$facility['facility_id'].'" '.$selected.'>'.$facility['facility_name'].'</option>';
}

echo  $html;