<?php
ob_start();
include('../includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/General.php');
$general=new General($db);
$tableName="vl_request_form";
try {
    $id= explode(",",$_POST['id']);
    for($i=0;$i<count($id);$i++){
        $status=array(
            'result_status'=>$_POST['status'],
            'data_sync'=>0
        );
        if($_POST['status']=='4')
        {
            $status['result_value_log'] = '';
            $status['result_value_absolute'] = '';
            $status['result_value_text'] = '';
            $status['result_value_absolute_decimal'] = '';
            $status['result'] = '';
            $status['is_sample_rejected'] = 'yes';
            $status['reason_for_sample_rejection'] = $_POST['rejectedReason'];
        }else{
            $status['is_sample_rejected'] = 'no';
        }
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