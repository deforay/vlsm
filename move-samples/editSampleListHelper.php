<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
$tableName="move_samples";
$tableName2="move_samples_map";

try {
    $data=array(
        'moved_from_lab_id'=>$_POST['labId'],
        'moved_to_lab_id'=>$_POST['labNameTo'],
        'reason_for_moving'=>$_POST['reasonForMoving'],
        'move_approved_by'=>$_POST['approveBy'],
        );
        $id = base64_decode($_POST['moveSampleId']);
        $db=$db->where('move_sample_id',$id);
        $db->update($tableName,$data);
        if($id > 0 && count($_POST['sampleCode'])>0){
            $c = count($_POST['sampleCode']);
            //first check all samples from move sample map
            $tableSampleId = json_decode($_POST['selectedSampleIdFromtable']);//get value from table
            $userSelectedSampleId = $_POST['sampleCode'];
            $sampleDiff = array_diff($tableSampleId,$userSelectedSampleId);
            for($j = 0; $j <= $c; $j++){
                if(isset($_POST['sampleCode'][$j]) && $_POST['sampleCode'][$j]!=''){
                    $data=array(
					    'move_sample_id'=>$id,
					    'vl_sample_id'=>$_POST['sampleCode'][$j],
				    );
                    if(in_array($_POST['sampleCode'][$j],$tableSampleId)){

                    }else{
                        $db->insert($tableName2,$data);
                    }
                }
            }
            //run query for delete unsync records
            if(count($sampleDiff)>0){
                foreach($sampleDiff as $sampleId){
                    $db=$db->where('move_sample_id',$id)
                            ->where('vl_sample_id',$sampleId)
                            ->where('move_sync_status','0');
                    $delId = $db->delete($tableName2);
                }
            }
            $_SESSION['alertMsg']="Sample List Updated!";
        }else{
            $_SESSION['alertMsg']="Something went wrong!";
        }
        header("location:sampleList.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
