<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
$tableName="other_config";
try {
    foreach ($_POST as $fieldName => $fieldValue) {
        if(trim($fieldName)!= ''){
           if($fieldName =='rq_field'){
                if(count($fieldValue) >0){
                    $fieldValue = implode(',',$fieldValue);
                }else{
                    $fieldValue = '';
                }
           }
           $data=array('value'=>$fieldValue);
           $db=$db->where('name',$fieldName);
           $db->update($tableName,$data);
        }
    }
    $_SESSION['alertMsg']="Test Request Email Config values updated successfully.";
    header("location:testRequestEmailConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}