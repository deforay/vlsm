<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$packageTable="package_details";
$packageTableMap="vl_request_form";
try {
        if(isset($_POST['packageCode']) && trim($_POST['packageCode'])!="" && count($_POST['sampleCode'])>0){
            $lastId = $_POST['packageId'];
            $db->where('package_id',$lastId);
            $db->update($packageTable,array('package_status'=>$_POST['packageStatus']));
            if($lastId > 0){
                $value = array('sample_package_id'=>NULL);
                $db=$db->where('sample_package_id',$lastId);
                $db->update($packageTableMap,$value);
                for($j=0;$j<count($_POST['sampleCode']);$j++){
                    $value = array('sample_package_id'=>$lastId);
                    $db=$db->where('vl_sample_id',$_POST['sampleCode'][$j]);
                    $db->update($packageTableMap,$value); 
                }
                $_SESSION['alertMsg']="Manifest details updated successfully";
            }
        }
    header("location:specimenReferralManifestList.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}