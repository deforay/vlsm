<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');

$tableName="import_config";

try {
    
    $data=array(
    'machine_name'=>$_POST['configurationName'],
    'log_absolute_val_same_col'=>$_POST['logAndAbsoluteInSameColumn'],
    'sample_id_col'=>$_POST['sampleIdCol'],
    'sample_id_row'=>$_POST['sampleIdRow'],
    'log_val_col'=>$_POST['logValCol'],
    'log_val_row'=>$_POST['logValRow'],
    'absolute_val_col'=>$_POST['absoluteValCol'],
    'absolute_val_row'=>$_POST['absoluteValRow'],
    'text_val_col'=>$_POST['textValCol'],
    'text_val_row'=>$_POST['textValRow']
    );
    //print_r($data);die;
    $db->insert($tableName,$data);    
    
    $_SESSION['alertMsg']="Import config details added successfully";
    header("location:importConfig.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}