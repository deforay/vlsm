<?php
ob_start();
session_start();
include('MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
//global config
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery ="SELECT * from system_config";
$systemConfigResult=$db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
  $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
if($sarr['user_type']=='remoteuser'){
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    $rKey = 'R';
}else{
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
$sExpDT = explode(" ",$_POST['sDate']);
$dtFormat = $general->dateFormat($sExpDT[0]);
$sExpDate = explode("-",$dtFormat);
$sExpDate[0] = substr($sExpDate[0], -2);
$start_date = date($sExpDate[0].'-01-01');
$end_date = date($sExpDate[0].'-12-31');
$mnthYr = date($sExpDate[0]);
if($arr['sample_code']=='MMYY'){
    $mnthYr = date($sExpDate[1].$sExpDate[0]);
}else if($arr['sample_code']=='YY'){
    $mnthYr = date($sExpDate[0]);
}
$auto = date($sExpDate[0].$sExpDate[1].$sExpDate[2]);
$svlQuery='SELECT '.$sampleCodeKey.' FROM vl_request_form as vl WHERE DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'" AND '.$sampleCode.'!="" ORDER BY vl_sample_id DESC LIMIT 1';
$svlResult=$db->query($svlQuery);
if(isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey]!='' && $svlResult[0][$sampleCodeKey]!=NULL){
 $maxId = $svlResult[0][$sampleCodeKey]+1;
 $strparam = strlen($maxId);
 $zeros = substr("000", $strparam);
 $maxId = $zeros.$maxId;
}else{
 $maxId = '001';
}
echo json_encode(array('maxId'=>$maxId,'mnthYr'=>$mnthYr,'auto'=>$auto));