<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');

$tableName1="batch_details";
$tableName2="vl_request_form";
try {
        if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!=""){
        $data=array('batch_code'=>$_POST['batchCode']);
        $db=$db->where('batch_id',$_POST['batchId']);
        $db->update($tableName1,$data);
        $lastId = $_POST['batchId'];
        if($lastId!=0 && $lastId!=''){
                $value = array('batch_id'=>NULL);
                $db=$db->where('batch_id',$lastId);
                $db->update($tableName2,$value);
            for($j=0;$j<=count($_POST['sampleCode']);$j++){
                $treamentId = $_POST['sampleCode'][$j];
                $value = array('batch_id'=>$lastId);
                $db=$db->where('treament_id',$treamentId);
                $db->update($tableName2,$value);
            }
            $_SESSION['alertMsg']="Batch code updated successfully";
        }
        }
        header("location:batchcode.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}