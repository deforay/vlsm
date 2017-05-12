<?php
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
try {
    $result = false;
    if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!=''){
        $batchResult = $db->rawQuery("select batch_code from batch_details where batch_code='".trim($_POST['batchCode'])."'");
        if($batchResult){}
        else{
            $data=array(
                    'machine'=>0,
                    'batch_code'=>trim($_POST['batchCode']),
                    'request_created_datetime'=>$general->getDateTime()
                    );
            $db->insert("batch_details",$data);
        }
        $db=$db->where('temp_sample_id',$_POST['tempsampleId']);
        $result = $db->update($tableName,array('batch_code'=>$_POST['batchCode']));
    }
    else if(isset($_POST['sampleCode']) && trim($_POST['sampleCode'])!=''){
        $sampleResult = $db->rawQuery("select sample_code from vl_request_form where sample_code='".trim($_POST['sampleCode'])."'");
        if($sampleResult){
            $sampleDetails = 'Result exists already';
        }else{
            $sampleDetails = 'New Sample';
        }
        $db=$db->where('temp_sample_id',$_POST['tempsampleId']);
        $result = $db->update($tableName,array('sample_code'=>$_POST['sampleCode'],'sample_details'=>$sampleDetails));
    }
    else if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!=''){
        $sampleControlResult = $db->rawQuery("select r_sample_control_name from r_sample_controls where r_sample_control_name='".trim($_POST['sampleType'])."'");
        $db=$db->where('temp_sample_id',$_POST['tempsampleId']);
        $result = $db->update($tableName,array('sample_type'=>trim($_POST['sampleType'])));
    }
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;