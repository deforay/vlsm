<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName1="package_details";
$tableName2="r_package_details_map";
try {
        if(isset($_POST['packageCode']) && trim($_POST['packageCode'])!=""){
            $data=array(
                        'package_code'=>$_POST['packageCode'],
                        'added_by'=>$_SESSION['userId'],
                        'request_created_datetime'=>$general->getDateTime()
                        );
            $db->insert($tableName1,$data);
            $lastId = $db->getInsertId();
            if($lastId > 0){
                for($j=0;$j<count($_POST['sampleCode']);$j++){
                    $value = array('package_id'=>$lastId,'sample_id'=>$_POST['sampleCode'][$j]);
                    $db->insert($tableName2,$value); 
                }
                $_SESSION['alertMsg']="Package details added successfully";
            }
        }
    header("location:packageList.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}