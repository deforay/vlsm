<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();



$general = new \Vlsm\Models\General();

//Add event log
$eventType = 'log-out';
$action = $_SESSION['userName'] . ' logged out';
$resource = 'user';
$general->activityLog($eventType, $action, $resource);

// Unset all the session variables.
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();
header("location:/login/login.php");