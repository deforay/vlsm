<?php
ob_start();
include('./includes/MysqliDb.php');
include('header.php');


$tableName="facility_details";


try {
    if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!=""){
        $data=array(
        'facility_name'=>$_POST['facilityName'],
        'facility_code'=>$_POST['facilityCode'],
        'phone_number'=>$_POST['phoneNo'],
        'address'=>$_POST['address'],
        'country'=>$_POST['country'],
        'state'=>$_POST['state'],
        'hub_name'=>$_POST['hubName'],
        'facility_type'=>$_POST['facilityType'],
        'status'=>'active'
        );
        //print_r($data);die;
        $db->insert($tableName,$data);    
        $_SESSION['alertMsg']="Facility details added successfully";
    }
    header("location:facilities.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}