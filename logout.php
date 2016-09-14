<?php
// Initialize the session.
// If you are using session_name("something"), don't forget it now!
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();

$tableName1="activity_log";
//Add event log
$eventType = 'log-out';
$action = ucwords($_SESSION['userName']).' have been logged out';
$resource = 'user-log-out';
$data=array(
'event_type'=>$eventType,
'action'=>$action,
'resource'=>$resource,
'date_time'=>$general->getDateTime()
);
$db->insert($tableName1,$data);
    
// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();
header("location:login.php");
?>