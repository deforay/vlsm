<?php
ob_start();
include('./includes/MysqliDb.php');
include('header.php');


$tableName="user_details";
$userId=base64_decode($_POST['userId']);

try {
    
    $data=array(
    'user_name'=>$_POST['userName'],
    'email'=>$_POST['email'],
    'phone_number'=>$_POST['mobileNo'],
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
    
    $_SESSION['alertMsg']="User details updated successfully";
    header("location:users.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}