<?php
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
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
$vlQuery="SELECT * FROM vl_request_form WHERE data_sync=0 AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";
$vlRemoteResult = $db->rawQuery($vlQuery);
$allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '$sDBNAME' AND table_name='vl_request_form'";
$allColResult = $db->rawQuery($allColumns);
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
				$sQuery = "Select vl_sample_id,sample_code,remote_sample_code,remote_sample_code_key from vl_request_form where remote_sample_code='".$lab['remote_sample_code']."'";
        $sResult = $remotedb->rawQuery($sQuery);
        if($sResult){
					$lab['data_sync'] = 1;//column data sync value is 1 equal to data sync done.value 0 is not done.
					$lab['last_modified_datetime'] = $general->getDateTime();
					$lab['remote_sample_code'] = $sResult[0]['remote_sample_code'];
					$lab['remote_sample_code_key'] = $sResult[0]['remote_sample_code_key'];
					unset($lab['request_created_by']);unset($lab['last_modified_by']);unset($lab['request_created_datetime']);
					$remotedb=$remotedb->where('vl_sample_id',$sResult[0]['vl_sample_id']);
					$id = $remotedb->update('vl_request_form',$lab);
					//update in lab database
					$db = $db->where('sample_code',$lab['sample_code']);
					$id = $db->update('vl_request_form',array('data_sync'=>1,'remote_sample_code'=>$sResult[0]['remote_sample_code'],'remote_sample_code_key'=>$sResult[0]['remote_sample_code_key']));
				}
    }
}
}
?>