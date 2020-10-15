<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$general = new \Vlsm\Models\General($db);

$size = 8;
if(isset($_POST['size']) && $_POST['size'] != ""){
    $size = $_POST['size'];
} 
echo $general->generateRandomString($size);