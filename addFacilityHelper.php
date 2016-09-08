<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');

$tableName="facility_details";
$tableName1="province_details";

try {
    if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!="" && trim($_POST['facilityCode'])!=''){
         if(trim($_POST['state'])!=""){
            $strSearch = $_POST['state'];
            $facilityQuery="SELECT * from province_details where province_name='".$strSearch."'";
            $facilityInfo=$db->query($facilityQuery);
            if($facilityInfo){
            }else{
            $data=array(
              'province_name'=>$_POST['state'],
            );
            $result=$db->insert($tableName1,$data);
            }
        }
        $data=array(
        'facility_name'=>$_POST['facilityName'],
        'facility_code'=>$_POST['facilityCode'],
        'other_id'=>$_POST['otherId'],
        'phone_number'=>$_POST['phoneNo'],
        'address'=>$_POST['address'],
        'country'=>$_POST['country'],
        'state'=>$_POST['state'],
        'district'=>$_POST['district'],
        'hub_name'=>$_POST['hubName'],
        'email'=>$_POST['email'],
        'contact_person'=>$_POST['contactPerson'],
	'facility_type'=>$_POST['facilityType'],
        'status'=>'active'
        );
        
        $db->insert($tableName,$data);
        $_SESSION['alertMsg']="Facility details added successfully";
    }
    header("location:facilities.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}