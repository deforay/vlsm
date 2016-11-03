<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');
$tableName1="batch_details";
$tableName2="vl_request_form";
try {
        if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!=""){
                $sample = array();
                $data=array('batch_code'=>$_POST['batchCode']);
                $db=$db->where('batch_id',$_POST['batchId']);
                $db->update($tableName1,$data);
                $lastId = $_POST['batchId'];
                if($lastId!=0 && $lastId!=''){
                    $value = array('batch_id'=>NULL);
                    $db=$db->where('batch_id',$lastId);
                    $db->update($tableName2,$value);
                    $xplodResultSample = array();
                    if(isset($_POST['resultSample']) && trim($_POST['resultSample'])!=""){
                        $xplodResultSample = explode(",",$_POST['resultSample']);
                    }
                    //Mergeing disabled samples into existing samples
                    if(isset($_POST['sampleCode']) && count($_POST['sampleCode'])>0){
                        if(count($xplodResultSample)>0){
                          $sample = array_unique(array_merge($_POST['sampleCode'],$xplodResultSample));
                        }else{
                           $sample = $_POST['sampleCode'];     
                        }
                    }elseif(count($xplodResultSample)>0){
                        $sample = $xplodResultSample;
                    }
                    for($j=0;$j<=count($sample);$j++){
                        $value = array('batch_id'=>$lastId);
                        $db=$db->where('vl_sample_id',$sample[$j]);
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