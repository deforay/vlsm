<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();

$tableName = "user_details";
$userName = $db->escape($_POST['username']);
$emailId = $db->escape($_POST['email']);
$loginId = $db->escape($_POST['loginid']);
$password = $db->escape($_POST['password']);

$userId = $general->generateUUID();

$general = new \Vlsm\Models\General();
$user = new \Vlsm\Models\Users();

$userPassword = $user->passwordHash($password);

try {
    if (isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
        $insertData = array(
            'user_id'       => $userId,
            'user_name'     => $userName,
            'email'         => $emailId,
            'login_id'      => $loginId,
            'password'      => $userPassword,
            'role_id'       => 1,
            'status'        => 'active'
        );
        $db->insert($tableName, $insertData);
        $_SESSION['alertMsg'] = "New User Added successfully";
    }
    header("location:/login/login.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}