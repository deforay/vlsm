<?php
ob_start();
require_once('../startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general=new General($db);
$tableName="vl_request_form";
try {

    $sQuery = "SELECT vl.vl_sample_id FROM temp_sample_import as tsr JOIN vl_request_form as vl ON vl.sample_code=tsr.sample_code JOIN r_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection where vl.result_status=4";

    $sResult = $db->query($sQuery);

    if(count($sResult)>0)
    {
        foreach($sResult as $sample)
        {
            $status=array(
                'result_status'=>7,
                'reason_for_sample_rejection'=>NULL,
                'data_sync'=>0
            );
            $db=$db->where('vl_sample_id',$sample['vl_sample_id']);
            $db->update('vl_request_form',$status);
        }
    }
    $status=array(
        'result_status'=>7,
    );
    //update all temp sample as accepted
    $result = $db->update('temp_sample_import',$status);
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;