<?php
include(dirname(__FILE__) . "/../../includes/MysqliDb.php");
include(dirname(__FILE__) . "/../../General.php");
$general=new Deforay_Commons_General();
//system config
$systemConfigQuery ="SELECT * from system_config";
$systemConfigResult=$db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
  $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
//global config
$cQuery="SELECT * FROM global_config";
$cResult=$db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
  $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}
//get facility map id
$facilityMapQuery = "SELECT facility_id FROM vl_facility_map";
$fMapResult=$remotedb->query($facilityMapQuery);
if(count($fMapResult)>0){
  $fMapResult = array_map('current', $fMapResult);
  $fMapResult = implode(",",$fMapResult);
}else{
  $fMapResult = "''";
}
//get remote data
if(trim($sarr['lab_name'])==''){
  $sarr['lab_name'] = "''";
}


if(isset($fMapResult) && $fMapResult != '' && $fMapResult != null){
  $where = "(lab_id =".$sarr['lab_name']." OR facility_id IN (".$fMapResult."))";
}else{
  $where = "lab_id =".$sarr['lab_name'];
}


$vlQuery="SELECT * FROM vl_request_form WHERE data_sync=0 AND $where AND `last_modified_datetime` > SUBDATE( NOW(), INTERVAL ". $arr['data_sync_interval']." HOUR)";
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
					unset($lab['request_created_by']);unset($lab['last_modified_by']);unset($lab['request_created_datetime']);unset($lab['sample_package_id']);
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