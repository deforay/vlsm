<?php
include('./includes/MysqliDb.php');
include('General.php');

$general=new General($db);

$vlQuery="SELECT vl_sample_id,sample_code,remote_sample_code,patient_first_name,patient_middle_name,patient_last_name,remote_sample from vl_request_form";
$vlQueryInfo=$db->query($vlQuery);
$vldata = array();
$c = count($vlQueryInfo);
$i = 1;
foreach($vlQueryInfo as $samples){
if($i == $c){
    $msg = "Patient name encrypted successfully!";
}
    if($samples['remote_sample']=='yes'){
        $sampleCode = $samples['remote_sample_code'];
    }else{
        $sampleCode = $samples['sample_code'];
    }
    $vldata['patient_first_name'] = $general->crypto('encrypt',$samples['patient_first_name'],$sampleCode);
    $vldata['patient_middle_name'] = $general->crypto('encrypt',$samples['patient_middle_name'],$sampleCode);
    $vldata['patient_last_name'] = $general->crypto('encrypt',$samples['patient_last_name'],$sampleCode);

    $db->where('vl_sample_id',$samples['vl_sample_id']);
    $db->update('vl_request_form',$vldata);
    $i++;
}
echo $msg;
?>