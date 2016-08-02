<?php
ob_start();
include('./includes/MysqliDb.php');
$tableName="vl_request_form";
try {
    $data=array(
    'result'=>$_POST['result'],
    );
    $db=$db->where('treament_id',$_POST['treatmentId']);
    //print_r($data);die;
    $db->update($tableName,$data);       
    
    $data = $_POST['treatmentId'];
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $data;