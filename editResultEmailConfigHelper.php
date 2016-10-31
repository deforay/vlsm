<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
$tableName="other_config";
try {
    $fieldValue = '';
    if(isset($_POST['result_email_field'])){
        $fieldValue = implode(',',$_POST['result_email_field']);
    }
    $data=array('value'=>$fieldValue);
    $db=$db->where('name','result_email_field');
    $db->update($tableName,$data);
    $_SESSION['alertMsg']="Result Email Config values updated successfully.";
    header("location:otherConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}