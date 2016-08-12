<?php
ob_start();
include('./includes/MysqliDb.php');
include('header.php');


$tableName="user_details";


try {
    if(trim($_POST['username'])!='' && trim($_POST['loginId'])!='' && ($_POST['role'])!='' && ($_POST['password'])!=''){
        
    $passwordSalt = '0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later';
    $password = sha1($_POST['password'].$passwordSalt);
    
    $data=array(
    'user_name'=>$_POST['userName'],
    'email'=>$_POST['email'],
    'login_id'=>$_POST['loginId'],
    'password'=>$password,
    'role_id'=>$_POST['role'],
    'status'=>'active'
    );
    $db->insert($tableName,$data);    
    
    $_SESSION['alertMsg']="User details added successfully";
    }
    header("location:users.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}