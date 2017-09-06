<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
$tableName="facility_details";
$tableName1="province_details";

try {
    if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!=""){
        if(trim($_POST['state'])!=""){
            $strSearch = (isset($_POST['provinceNew']) && trim($_POST['provinceNew'])!= '' && $_POST['state'] == 'other')?$_POST['provinceNew']:$_POST['state'];
            $facilityQuery="SELECT province_name from province_details where province_name='".$strSearch."'";
            $facilityInfo=$db->query($facilityQuery);
	    if(isset($facilityInfo[0]['province_name'])){
		$_POST['state'] = $facilityInfo[0]['province_name'];
	    }else{
		$data=array(
		  'province_name'=>$_POST['provinceNew']
		);
		$db->insert($tableName1,$data);
		$_POST['state'] = $_POST['provinceNew'];
	    }
        }
	$instanceId = '';
	if(isset($_SESSION['instanceId'])){
	    $instanceId = $_SESSION['instanceId'];
	}
	$email = '';
	if(isset($_POST['reportEmail']) && trim($_POST['reportEmail'])!=''){
	    $expEmail = explode(",",$_POST['reportEmail']);
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
        'vlsm_instance_id'=>$instanceId,
        'other_id'=>$_POST['otherId'],
        'facility_mobile_numbers'=>$_POST['phoneNo'],
        'address'=>$_POST['address'],
        'country'=>$_POST['country'],
        'facility_state'=>$_POST['state'],
        'facility_district'=>$_POST['district'],
        'facility_hub_name'=>$_POST['hubName'],
        'latitude'=>$_POST['latitude'],
        'longitude'=>$_POST['longitude'],
        'facility_emails'=>$_POST['email'],
        'report_email'=>$email,
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