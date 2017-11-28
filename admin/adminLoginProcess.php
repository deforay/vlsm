<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
try {
    if(isset($_POST['username']) && trim($_POST['username'])!="" && isset($_POST['password']) && trim($_POST['password'])!=""){
        $adminUsername = trim($_POST['username']);
        $adminPassword = trim($_POST['password']);
        $params = array($adminUsername,$adminPassword);
        $admin = $db->rawQuery("SELECT * FROM user_admin_details as ud WHERE ud.user_admin_login = ? AND ud.user_admin_password = ?", $params);
        if(count($admin)>0){
            $_SESSION['adminUserId']=$admin[0]['user_admin_id'];
            $_SESSION['adminUserName']=ucwords($admin[0]['user_admin_name']);
            header("location:../edit-config/index.php");
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
?>