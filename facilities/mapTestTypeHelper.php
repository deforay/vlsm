<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
#require_once('../startup.php');  
$params     = $_POST['facilityType'];
$testType   = $_POST['testType'];

if($params == "testing-labs"){
    $tableName ="testing_labs";
} else{
    $tableName = "health_facilities";
}

try {
    if(isset($_POST['facilities']) && count($_POST['facilities']) > 0){
        $db = $db->where('test_type', $testType);
        $id = $db->delete($tableName);
        
		foreach($_POST['facilities'] as $facility){
			$data=array(
				'test_type'     =>$testType,
				'facility_id'   => $facility,
            );
			$db->insert($tableName,$data);
		}
        $_SESSION['alertMsg']="Facility Mapped to Selected Test Type successfully";
    }
    header("location:facilities.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}