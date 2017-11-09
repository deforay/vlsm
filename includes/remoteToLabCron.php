<?php
include('MysqliDb.php');
//global config
$cQuery="SELECT * FROM global_config";
$cResult=$db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
  $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}
$end_date = date('Y-12-31');
$start_date = date('Y-01-01');
  if($arr['sample_code']=='MMYY'){
    $mnthYr = date('my');
    $end_date = date('Y-m-31');
    $start_date = date('Y-m-01');
  }else if($arr['sample_code']=='YY'){
    $mnthYr = date('y');
    $end_date = date('Y-12-31');
    $start_date = date('Y-01-01');
  }
//get remote data
$vlQuery="SELECT * FROM vl_request_form WHERE `last_modified_datetime` < SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";
$vlRemoteResult = $syncdb->rawQuery($vlQuery);
$allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '$sDBNAME' AND table_name='vl_request_form'";
$allColResult = $syncdb->rawQuery($allColumns);
$oneDimensionalArray = array_map('current', $allColResult);
foreach($vlRemoteResult as $key=>$remoteData){
    foreach($oneDimensionalArray as $result){
        $lab[$result] = $remoteData[$result];
    }
    //remove result value
    $removeKeys = array('vl_sample_id','result_value_log','result_value_absolute','result_value_absolute_decimal','result_value_text','result_value_result');
    foreach($removeKeys as $keys){
        unset($lab[$keys]);
    }
    //check wheather sample code empty or not
    if($lab['sample_code']!=''){
        $sQuery = "Select vl_sample_id from vl_request_form where sample_code=".$lab['sample_code'];
        $sResult = $db->rawQuery($sQuery);
        unset($lab['last_modified_datetime']);
        $db=$db->where('vl_sample_id',$sResult[0]['vl_sample_id']);
        $id = $db->insert('vl_request_form',$lab);
    }else{
        $svlQuery='SELECT sample_code_key FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" ORDER BY vl_sample_id DESC LIMIT 1';
        $svlResult=$db->query($svlQuery);
        $prefix = $arr['sample_code_prefix'];
        if($svlResult[0]['sample_code_key']!='' && $svlResult[0]['sample_code_key']!=NULL){
         $maxId = $svlResult[0]['sample_code_key']+1;
         $strparam = strlen($maxId);
         $zeros = substr("000", $strparam);
         $maxId = $zeros.$maxId;
        }else{
         $maxId = '001';
        }
        if($arr['sample_code']=='auto'){
            $lab['serial_no'] = date('ymd');
            $lab['sample_code'] = date('ymd');
            $lab['sample_code_key'] = $maxId;
        }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){
            $lab['serial_no'] = $prefix.$mnthYr.$maxId;
            $lab['sample_code'] = $prefix.$mnthYr.$maxId;
            $lab['sample_code_format'] = $prefix.$mnthYr;
            $lab['sample_code_key'] =  $maxId;
        }
        $lab['result_status'] = 6;
        $id = $db->insert('vl_request_form',$lab);
    }
}
?>