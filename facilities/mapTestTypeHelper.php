<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
#require_once('../startup.php');  
$general = new \Vlsm\Models\General($db);

$params     = $_POST['facilityType'];
$testType   = $_POST['testType'];

if($params == "testing-labs"){
    $tableName ="testing_labs";
} else{
    $tableName = "health_facilities";
}

try {
    $_POST['mappedFacilities'] = json_decode($_POST['mappedFacilities'], true);
    if(isset($_POST['mappedFacilities']) && count($_POST['mappedFacilities']) > 0){
        $db = $db->where('test_type', $testType);
        $id = $db->delete($tableName);
        $currentDateTime = $general->getDateTime();
        
		foreach($_POST['mappedFacilities'] as $facility){
			$data=array(
				'test_type'     =>$testType,
                'facility_id'   => $facility,
                'updated_datetime'  => $currentDateTime
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