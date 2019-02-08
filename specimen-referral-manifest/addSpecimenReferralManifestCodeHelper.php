<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new General($db);
$packageTable="package_details";
$packageTableMap="vl_request_form";
try {
        if(isset($_POST['packageCode']) && trim($_POST['packageCode'])!=""){
            $data=array(
                        'package_code'=>$_POST['packageCode'],
                        'added_by'=>$_SESSION['userId'],
                        'package_status'=>'pending',
                        'request_created_datetime'=>$general->getDateTime()
                        );
            $db->insert($packageTable,$data);
            $lastId = $db->getInsertId();
            if($lastId > 0){
                for($j=0;$j<count($_POST['sampleCode']);$j++){
                    $value = array('sample_package_id'=>$lastId);
                    $db=$db->where('vl_sample_id',$_POST['sampleCode'][$j]);
                    $db->update($packageTableMap,$value); 
                }
                $_SESSION['alertMsg']="Manifest details added successfully";
            }
        }
    header("location:specimenReferralManifestList.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}