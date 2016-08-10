<?php
session_start();
include('./includes/MysqliDb.php');


$tableName1="user_details";

try {
    if(isset($_POST['username']) && trim($_POST['username'])!="" && isset($_POST['password']) && trim($_POST['password'])!=""){
        $passwordSalt = '0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later';
        $password = sha1($_POST['password'].$passwordSalt);
        $adminUsername=$db->escape($_POST['username']);
        $adminPassword=$db->escape($password);
        $params = array($adminUsername,$adminPassword,'active');
        $admin = $db->rawQuery("SELECT ud.user_id,ud.user_name,ud.email,r.role_name,r.role_code FROM user_details as ud INNER JOIN roles as r ON ud.role_id=r.role_id WHERE ud.login_id = ? AND ud.password = ? AND ud.status = ?", $params);
        
        if(count($admin)>0){
            $_SESSION['userId']=$admin[0]['user_id'];
            $_SESSION['userName']=ucwords($admin[0]['user_name']);
            $_SESSION['roleCode']=$admin[0]['role_code'];
            $_SESSION['email']=$admin[0]['email'];
            header("location:index.php");
        }else{
            header("location:login.php");
            $_SESSION['alertMsg']="Please check login credential";
        }
    }else{
        header("location:login.php");
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}