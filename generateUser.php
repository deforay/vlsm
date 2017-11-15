<?php
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$uQuery = "SELECT * FROM user_details";
$uResult=$db->query($uQuery);
if($uResult){
    foreach($uResult as $uData){
        $idOne = $general->generateRandomString(8);
        $idTwo = $general->generateRandomString(4);
        $idThree = $general->generateRandomString(4);
        $idFour = $general->generateRandomString(4);
        $idFive = $general->generateRandomString(12);
        $db->where('user_id',$uData['user_id']);
        $data=array('user_alpnum_id'=>$idOne."-".$idTwo."-".$idThree."-".$idFour."-".$idFive);
        $db->update('user_details',$data);
        //update vl_request_form table modified_user_id
        $db->rawQuery("update vl_request_form set last_modified_by='".$data['user_alpnum_id']."' where last_modified_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set request_created_by='".$data['user_alpnum_id']."' where request_created_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set result_reviewed_by='".$data['user_alpnum_id']."' where result_reviewed_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set result_approved_by='".$data['user_alpnum_id']."' where result_approved_by ='".$uData['user_id']."'");
        $db->rawQuery("update vl_request_form set sample_collected_by='".$data['user_alpnum_id']."' where sample_collected_by ='".$uData['user_id']."'");
        
        //update vl_user_facility_map user id
        $db->rawQuery("update vl_user_facility_map set user_id='".$data['user_alpnum_id']."' where user_id ='".$uData['user_id']."'");
    }
}
