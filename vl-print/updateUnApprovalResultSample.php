<?php
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
try {
    $result = false;
    if(trim($_POST['sampleCode'])!=''){
        $sampleResult = $db->rawQuery("select sample_code from vl_request_form where sample_code='".$_POST['sampleCode']."'");
        if($sampleResult){
            $sampleDetails = 'Result exists already';
        }else{
            $sampleDetails = 'New Sample';
        }
        $db=$db->where('temp_sample_id',$_POST['tempsampleId']);
        $result = $db->update($tableName,array('sample_code'=>$_POST['sampleCode'],'sample_details'=>$sampleDetails));
}
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;