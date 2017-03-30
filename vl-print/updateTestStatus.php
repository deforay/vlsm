<?php
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
try {
    $id= explode(",",$_POST['id']);
    for($i=0;$i<count($id);$i++){
        $status=array(
            'result_status'=>$_POST['status']
        );
        $db=$db->where('vl_sample_id',$id[$i]);
        $db->update($tableName,$status);
        $result = $id[$i];
    }
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;