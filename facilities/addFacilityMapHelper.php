<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
$tableName="vl_facility_map";
try {
    if(isset($_POST['vlLab']) && trim($_POST['vlLab'])!="" && trim($_POST['facilityTo'])!=''){
		$facilityTo = explode(",",$_POST['facilityTo']);
		for($j = 0; $j < count($facilityTo); $j++){
			$data=array(
				'vl_lab_id'=>$_POST['vlLab'],
				'facility_id'=>$facilityTo[$j],
			);
			$db->insert($tableName,$data);
		}
        $_SESSION['alertMsg']="Facility map details added successfully";
    }
    header("location:facilityMap.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}