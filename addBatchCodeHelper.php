<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName1="batch_details";
$tableName2="vl_request_form";
try {
        if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!=""){
                $labelOrder = '';
                if(isset($_POST['sortOrders']) && trim($_POST['sortOrders'])!= ''){
                     $xplodSortOrders = explode(",",$_POST['sortOrders']);
                     $orderArray = array();
                     for($o=0;$o<count($xplodSortOrders);$o++){
                        $orderArray[$o] = $xplodSortOrders[$o];
                     }
                  $labelOrder = json_encode($orderArray,JSON_FORCE_OBJECT);
                }

                $data=array(
                            'machine'=>$_POST['machine'],
                            'batch_code'=>$_POST['batchCode'],
                            'batch_code_key'=>$_POST['batchCodeKey'],
                            'label_order'=>$labelOrder,
                            'created_on'=>$general->getDateTime()
                            );
                $db->insert($tableName1,$data);
                $lastId = $db->getInsertId();
                if($lastId!=0 && $lastId!=''){
                    for($j=0;$j<=count($_POST['sampleCode']);$j++){
                        $treamentId = $_POST['sampleCode'][$j];
                        $value = array('batch_id'=>$lastId);
                        $db=$db->where('vl_sample_id',$treamentId);
                        $db->update($tableName2,$value); 
                    }
                    $_SESSION['alertMsg']="Batch code added successfully";
                    header("location:batchcode.php");
                }
        }else{
                header("location:batchcode.php");
        }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}