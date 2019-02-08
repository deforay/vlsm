<?php
include('../includes/MysqliDb.php');
include('../General.php');
$general=new General($db);

$tableName1="batch_details";
$tableName2="vl_request_form";

$batchId = base64_decode($_POST['id']);

$vlQuery="SELECT vl_sample_id from vl_request_form as vl where sample_batch_id=$batchId";
$vlInfo=$db->query($vlQuery);
if(count($vlInfo)>0){
    $value = array('sample_batch_id'=>NULL);
    $db=$db->where('sample_batch_id',$batchId);
    
    $db->update($tableName2,$value);
}

$db=$db->where('batch_id',$batchId);
$delId = $db->delete($tableName1);
if($delId>0){
    echo '1';
}else{
    echo '0';
}
?>