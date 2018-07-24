<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new General();
//include('../header.php');
$tableName="user_details";
try {
    if(trim($_POST['userName'])!='' && trim($_POST['loginId'])!='' && ($_POST['role'])!='' && ($_POST['password'])!=''){
        
    $passwordSalt = '0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later';
    $password = sha1($_POST['password'].$passwordSalt);
    $idOne = $general->generateRandomString(8);
    $idTwo = $general->generateRandomString(4);
    $idThree = $general->generateRandomString(4);
    $idFour = $general->generateRandomString(4);
    $idFive = $general->generateRandomString(12);
    $data=array(
    'user_id'=>$idOne."-".$idTwo."-".$idThree."-".$idFour."-".$idFive,
    //'user_alpnum_id'=>$idOne."-".$idTwo."-".$idThree."-".$idFour."-".$idFive,
    'user_name'=>$_POST['userName'],
    'email'=>$_POST['email'],
    'login_id'=>$_POST['loginId'],
    'phone_number'=>$_POST['phoneNo'],
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