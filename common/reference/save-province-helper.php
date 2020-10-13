<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

#require_once('../startup.php');  

$general = new \Vlsm\Models\General($db);

$tableName = "province_details";
$primaryKey = "province_id";

try {
	if (isset($_POST['provinceName']) && trim($_POST['provinceName']) != "") {

		$data = array(
			'province_name' 	=> $_POST['provinceName'],
			'province_code' 	=> $_POST['provinceCode'],
			'updated_datetime'	=> $general->getDateTime()
		);
		if(isset($_POST['provinceId']) && $_POST['provinceId'] != ""){
			$db = $db->where($primaryKey, base64_decode($_POST['provinceId']));
        	$lastId = $db->update($tableName, $data);
		} else{
			$data['data_sync'] = 0;
			$db->insert($tableName, $data);
			$lastId = $db->getInsertId();
		}
        if($lastId > 0){
            $_SESSION['alertMsg'] = "Province details saved successfully";
            $general->activityLog('Province details', $_SESSION['userName'] . ' added new province for ' . $_POST['provinceName'], 'common-reference');
        }
	}
	header("location:province-details.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
