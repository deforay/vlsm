<?php
session_start();
ob_start();
require_once('../startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
$tableName="move_samples";
$tableName2="move_samples_map";

try {
    $data=array(
        'moved_from_lab_id'=>$_POST['labId'],
        'moved_to_lab_id'=>$_POST['labNameTo'],
        'moved_on'=>date('Y-m-d'),
        'moved_by'=>$_SESSION['userId'],
        'reason_for_moving'=>$_POST['reasonForMoving'],
        'move_approved_by'=>$_POST['approveBy'],
        );
        $id = $db->insert($tableName,$data);
        if($id > 0 && count($_POST['sampleCode'])>0){
            $c = count($_POST['sampleCode']);
            for($j = 0; $j <= $c; $j++){
                if(isset($_POST['sampleCode'][$j]) && $_POST['sampleCode'][$j]!=''){
				    $data=array(
					    'move_sample_id'=>$id,
					    'vl_sample_id'=>$_POST['sampleCode'][$j],
				    );
                    $db->insert($tableName2,$data);
                }
            }
            $_SESSION['alertMsg']="Sample List added!";
        }else{
            $_SESSION['alertMsg']="Something went wrong!";
        }
        header("location:sampleList.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
