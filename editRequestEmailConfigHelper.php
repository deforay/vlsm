<?php
print($_POST);die;
session_start();
ob_start();
include('./includes/MysqliDb.php');
$tableName="other_config";
try {
    $fieldValue = '';
    $data=array('value'=>$fieldValue);
    $db=$db->where('name','request_email_field');
    $db->update($tableName,$data);
    $_SESSION['alertMsg']="Request Email Config values updated successfully";
    header("location:otherConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}