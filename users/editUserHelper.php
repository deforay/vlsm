<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
//include_once('../startup.php'); include_once(APPLICATION_PATH.'/header.php');


$tableName="user_details";
$tableName2="vl_user_facility_map";
$userId=base64_decode($_POST['userId']);

try {
    if(trim($_POST['userName'])!='' && trim($_POST['loginId'])!='' && ($_POST['role'])!=''){
    $data=array(
    'user_name'=>$_POST['userName'],
    'email'=>$_POST['email'],
    'phone_number'=>$_POST['phoneNo'],
    'login_id'=>$_POST['loginId'],
    'role_id'=>$_POST['role'],
    'status'=>$_POST['status']
    );
    
    if(isset($_POST['password']) && trim($_POST['password'])!=""){
        $passwordSalt = '0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later';
        $data['password'] = sha1($_POST['password'].$passwordSalt);
    }
    
    $db=$db->where('user_id',$userId);
    //print_r($data);die;
    $db->update($tableName,$data);
    $db=$db->where('user_id',$userId);
		$delId = $db->delete($tableName2);
		if($userId!='' && trim($_POST['selectedFacility'])!='')
		{
            $selectedFacility = explode(",",$_POST['selectedFacility']);
            $uniqueFacilityId = array_unique($selectedFacility);
			for($j = 0; $j <= count($uniqueFacilityId); $j++){
                if(isset($uniqueFacilityId[$j])){
				$data=array(
					'facility_id'=>$uniqueFacilityId[$j],
					'user_id'=>$userId,
				);
                $db->insert($tableName2,$data);
                }
			}
		}
    $_SESSION['alertMsg']="User details updated successfully";
    }
    header("location:users.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}