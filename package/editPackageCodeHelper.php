<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName1="package_details";
$tableName2="r_package_details_map";
try {
        if(isset($_POST['packageCode']) && trim($_POST['packageCode'])!="" && count($_POST['sampleCode'])>0){
            $lastId = $_POST['packageId'];
            if($lastId > 0){
                $db->where('package_id',$lastId);
                $db->delete($tableName2);
                for($j=0;$j<count($_POST['sampleCode']);$j++){
                    $value = array('package_id'=>$lastId,'sample_id'=>$_POST['sampleCode'][$j]);
                    $db->insert($tableName2,$value); 
                }
                $_SESSION['alertMsg']="Package details updated successfully";
            }
        }
    header("location:packageList.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}