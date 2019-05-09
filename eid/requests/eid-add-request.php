<?php
ob_start();
$title = "VLSM | EID | Add EID Request";
include_once('../../startup.php');
include_once(APPLICATION_PATH.'/header.php');
include_once(APPLICATION_PATH.'/General.php');



// if($sarr['user_type']=='remoteuser'){
//     $labFieldDisabled = 'disabled="disabled"';
//     $vlfmQuery="SELECT GROUP_CONCAT(DISTINCT vlfm.facility_id SEPARATOR ',') as facilityId FROM vl_user_facility_map as vlfm where vlfm.user_id='".$_SESSION['userId']."'";
//     $vlfmResult = $db->rawQuery($vlfmQuery);
// }

$general=new General($db);
$arr = $general->getGlobalConfig();

if($arr['vl_form']==1){
    include('eid-add-southsudan.php');
}else if($arr['vl_form']==2){
    include('eid-add-zimbabwe.php');
}else if($arr['vl_form']==3){
    include('eid-add-drc.php');
}else if($arr['vl_form']==4){
    include('eid-add-zambia.php');
}else if($arr['vl_form']==5){
    include('eid-add-png.php');
}else if($arr['vl_form']==6){
    include('eid-add-who.php');
}else if($arr['vl_form']==7){
    include('eid-add-rwanda.php');
}else if($arr['vl_form']==8){
    include('eid-add-angola.php');
}


include_once(APPLICATION_PATH.'/footer.php');