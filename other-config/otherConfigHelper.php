<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
$tableName="other_config";

try {
    foreach ($_POST as $fieldName => $fieldValue) {
        if(trim($fieldName)!= ''){
           $data=array('value'=>$fieldValue);
           $db=$db->where('name',$fieldName);
           $db->update($tableName,$data);
        }
    }
    $_SESSION['alertMsg']="Other Config values updated successfully";
    header("location:testResultEmailConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}