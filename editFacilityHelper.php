<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');
$tableName="facility_details";
$facilityId=base64_decode($_POST['facilityId']);
$tableName1="province_details";
try {
    if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!=""){
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
	if(trim($_POST['reportEmail'])!=''){
	    $expEmail = explode(",",$_POST['reportEmail']);
	    $email = '';
	    for($i=0;$i<count($expEmail);$i++){
		$reportEmail = filter_var($expEmail[$i], FILTER_VALIDATE_EMAIL);
		if($reportEmail!=''){
		if($email!=''){
		$email.= ",".$reportEmail;
		}else{
		$email.= $reportEmail;    
		}
		}
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
			'latitude'=>$_POST['latitude'],
			'longitude'=>$_POST['longitude'],
			'email'=>$_POST['email'],
			'report_email'=>$email,
			'contact_person'=>$_POST['contactPerson'],
			'facility_type'=>$_POST['facilityType'],
			'status'=>$_POST['status']
        );
        //print_r($data);die;
        $db=$db->where('facility_id',$facilityId);
        $db->update($tableName,$data);    
        $_SESSION['alertMsg']="Facility details updated successfully";
    }
    header("location:facilities.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}