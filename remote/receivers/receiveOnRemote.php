<?php
//this file is receive lab data value and update in remote db
$data = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../includes/MysqliDb.php");
include(dirname(__FILE__) . "/../General.php");
$general=new Deforay_Commons_General();

$allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '$sDBNAME' AND table_name='vl_request_form'";
$allColResult = $db->rawQuery($allColumns);
$oneDimensionalArray = array_map('current', $allColResult);
$sampleCode = array();
if(count($data['result'])>0){
foreach($data['result'] as $key=>$remoteData){
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
        $sResult = $db->rawQuery($sQuery);
        if($sResult){
            $lab['data_sync'] = 1;//column data sync value is 1 equal to data sync done.value 0 is not done.
            $lab['last_modified_datetime'] = $general->getDateTime();
            $lab['remote_sample_code'] = $sResult[0]['remote_sample_code'];
            $lab['remote_sample_code_key'] = $sResult[0]['remote_sample_code_key'];
            unset($lab['request_created_by']);
            unset($lab['last_modified_by']);
            unset($lab['request_created_datetime']);
            unset($lab['sample_package_id']);
            $db=$db->where('vl_sample_id',$sResult[0]['vl_sample_id']);
            $id = $db->update('vl_request_form',$lab);
            $sampleCode[] = $lab['sample_code'];
		}
    }
}
}
echo json_encode($sampleCode);