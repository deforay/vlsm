<?php
//this file is get the value from remote and update in lab db
include(dirname(__FILE__) . "/../includes/MysqliDb.php");
include(dirname(__FILE__) . "/../General.php");
$general=new Deforay_Commons_General();
if(!isset($REMOTEURL) || $REMOTEURL=='')
{
    echo "Please check your remote url";
    die;
}
//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
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
//get remote data
if(trim($sarr['lab_name'])==''){
  $sarr['lab_name'] = "''";
}
$url = $REMOTEURL.'/remote/receivers/receiveOnLab.php';
$data = array(
    'labName'=>$sarr['lab_name'],
    "Key"=>"vlsm-lab-Data--",
);
//open connection
$ch = curl_init($url);
$json_data = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data))
);
// execute post
$curl_response = curl_exec($ch);
//print_r($curl_response);die;
//close connection
curl_close($ch);
$result = json_decode($curl_response, true);
if(count($result)>0){
    $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '$DBNAME' AND table_name='vl_request_form'";
    $allColResult = $db->rawQuery($allColumns);
    $oneDimensionalArray = array_map('current', $allColResult);
    foreach($result as $key=>$remoteData){
        foreach($oneDimensionalArray as $result){
          $lab[$result] = $remoteData[$result];
        }
        $removeKeys = array('vl_sample_id','result_value_log','result_value_absolute','result_value_absolute_decimal','result_value_text','result_value',
        'sample_tested_datetime','sample_received_at_vl_lab_datetime','result_dispatched_datetime','is_sample_rejected','reason_for_sample_rejection','result_approved_by');
        foreach($removeKeys as $keys){
            unset($lab[$keys]);
        }
        //check wheather sample code empty or not
        if($lab['sample_code']!='' && $lab['sample_code']!=0 && $lab['sample_code']!=NULL){
            $sQuery = "Select vl_sample_id from vl_request_form where sample_code='".$lab['sample_code']."'";
            $sResult = $db->rawQuery($sQuery);
            $lab['data_sync'] = 1;//column data sync value is 1 equal to data sync done.value 0 is not done.
            unset($lab['request_created_by']);unset($lab['last_modified_by']);unset($lab['request_created_datetime']);
            $lab['last_modified_datetime'] = $general->getDateTime();
            $db=$db->where('vl_sample_id',$sResult[0]['vl_sample_id']);
            $id = $db->update('vl_request_form',$lab);
            $samplePackageId = $lab['sample_package_id'];
        }else{
            //check exist remote
            $exsvlQuery="SELECT vl_sample_id,sample_code FROM vl_request_form as vl WHERE remote_sample_code='".$lab['remote_sample_code']."'";
            $exsvlResult=$db->query($exsvlQuery);
            if($exsvlResult){
            }else{
                if($lab['sample_collection_date']!='' && $lab['sample_collection_date']!=null && $lab['sample_collection_date']!='0000-00-00 00:00:00')
                {
                    $sExpDT = explode(" ",$lab['sample_collection_date']);
                    $sExpDate = explode("-",$sExpDT[0]);
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
                    $svlQuery='SELECT sample_code_key FROM vl_request_form as vl WHERE DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'" ORDER BY vl_sample_id DESC LIMIT 1';
                    $svlResult=$db->query($svlQuery);
                    $prefix = $arr['sample_code_prefix'];
                    if(isset($svlResult[0]['sample_code_key']) && $svlResult[0]['sample_code_key']!='' && $svlResult[0]['sample_code_key']!=NULL){
                    $maxId = $svlResult[0]['sample_code_key']+1;
                    $strparam = strlen($maxId);
                    $zeros = substr("000", $strparam);
                    $maxId = $zeros.$maxId;
                    }else{
                    $maxId = '001';
                    }
                    if($arr['sample_code']=='auto'){
                        $lab['serial_no'] = $auto.$maxId;
                        $lab['sample_code'] = $auto.$maxId;
                        $lab['sample_code_key'] = $maxId;
                    }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){
                        $lab['serial_no'] = $prefix.$mnthYr.$maxId;
                        $lab['sample_code'] = $prefix.$mnthYr.$maxId;
                        $lab['sample_code_format'] = $prefix.$mnthYr;
                        $lab['sample_code_key'] =  $maxId;
                    }
                    $lab['request_created_by'] = 0;
                    $lab['last_modified_by'] = 0;
                    $lab['request_created_datetime'] = $general->getDateTime();
                    $lab['last_modified_datetime'] = $general->getDateTime();
                    //$lab['result_status'] = 6;
                    $lab['data_sync'] = 1;//column data_sync value is 1 equal to data_sync done.value 0 is not done.
                    $id = $db->insert('vl_request_form',$lab);
                }
            }
        }
    }
}