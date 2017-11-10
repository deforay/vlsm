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
$vlQuery="SELECT * FROM vl_request_form WHERE `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";
$vlRemoteResult = $syncdb->rawQuery($vlQuery);
$allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '$DBNAME' AND table_name='vl_request_form'";
$allColResult = $syncdb->rawQuery($allColumns);
$oneDimensionalArray = array_map('current', $allColResult);
if(count($vlRemoteResult)>0){
foreach($vlRemoteResult as $key=>$remoteData){
    foreach($oneDimensionalArray as $result){
        $lab[$result] = $remoteData[$result];
    }
    //remove result value
    $removeKeys = array('vl_sample_id');
    foreach($removeKeys as $keys){
        unset($lab[$keys]);
    }
    //check wheather sample code empty or not
    if($lab['remote_sample_code']!=''){
        $sQuery = "Select vl_sample_id from vl_request_form where remote_sample_code='".$lab['remote_sample_code']."'";
        $sResult = $db->rawQuery($sQuery);
        unset($lab['last_modified_datetime']);
        $db=$db->where('vl_sample_id',$sResult[0]['vl_sample_id']);
        $id = $db->update('vl_request_form',$lab);
    }else{
        $svlQuery='SELECT remote_sample_code_key FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" ORDER BY vl_sample_id DESC LIMIT 1';
        $svlResult=$db->query($svlQuery);
        $prefix = $arr['sample_code_prefix'];
        if($svlResult[0]['remote_sample_code_key']!='' && $svlResult[0]['remote_sample_code_key']!=NULL){
         $maxId = $svlResult[0]['remote_sample_code_key']+1;
         $strparam = strlen($maxId);
         $zeros = substr("000", $strparam);
         $maxId = $zeros.$maxId;
        }else{
         $maxId = '001';
        }
        if($arr['sample_code']=='auto'){
            $lab['remote_sample_code'] = date('ymd');
            $lab['remote_sample_code_key'] = $maxId;
        }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){
            $lab['remote_sample_code'] = $prefix.$mnthYr.$maxId;
            $lab['remote_sample_code_key'] =  $maxId;
        }
        $lab['result_status'] = 6;
        $id = $db->insert('vl_request_form',$lab);
    }
}
}
?>