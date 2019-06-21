<?php
include_once('startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH . '/models/General.php');
$general=new General($db);
$uQuery = "SELECT * FROM user_details";
$uResult=$db->query($uQuery);
if($uResult){
    foreach($uResult as $uData){
        $userId= $general->generateUserID();
        $db->where('user_id',$uData['user_id']);
        $data=array('user_alpnum_id'=>$userId);
        $db->update('user_details',$data);
        //update vl_request_form table modified_user_id
        $db->rawQuery("update vl_request_form set last_modified_by='".$userId."' where last_modified_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set request_created_by='".$userId."' where request_created_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set result_reviewed_by='".$userId."' where result_reviewed_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set result_approved_by='".$userId."' where result_approved_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set sample_collected_by='".$userId."' where sample_collected_by ='".$uData['user_id']."'");
        
        //update vl_user_facility_map user id
        $db->rawQuery("update vl_user_facility_map set user_id='".$userId."' where user_id ='".$uData['user_id']."'");
        $db->rawQuery("update user_details set user_id='".$userId."'");
    }
}