<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');

$tableName="import_config";
$configId=(int) base64_decode($_POST['configId']);
try {
    
    $importConfigData=array(
    'machine_name'=>$_POST['machineName'],
    'log_absolute_val_same_col'=>$_POST['logAndAbsoluteInSameColumn'],
    'sample_id_col'=>$_POST['sampleIdCol'],
    'sample_id_row'=>$_POST['sampleIdRow'],
    'log_val_col'=>$_POST['logValCol'],
    'log_val_row'=>$_POST['logValRow'],
    'absolute_val_col'=>$_POST['absoluteValCol'],
    'absolute_val_row'=>$_POST['absoluteValRow'],
    'text_val_col'=>$_POST['textValCol'],
    'text_val_row'=>$_POST['textValRow'],
    'status'=>$_POST['status']
    );
    //print_r($data);die;
    $db=$db->where('config_id',$configId);
    //print_r($vldata);die;
    $db->update($tableName,$importConfigData);        
    
    $_SESSION['alertMsg']="Import config details updated successfully";
    header("location:importConfig.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}