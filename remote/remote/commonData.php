<?php
//get data from remote db send to lab db
include(dirname(__FILE__) . "/../../startup.php");  
include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$data = json_decode(file_get_contents('php://input'), true);
if($data['Key']=='vlsm-get-remote'){
    //r_vl_sample_type
    $sTypeQuery = "select * from r_vl_sample_type";
    $sTypeResult = $db->query($sTypeQuery);
    //art code deatils
    $artCodeQuery = "select * from r_art_code_details";
    if($data['artCodeUpdateTime']!=''){
        $artCodeQuery .= " where updated_datetime >'".$data['artCodeUpdateTime']."'";
    }
    $artCodeResult = $db->query($artCodeQuery);
    //rejection reason
    $rejectQuery = "select * from r_sample_rejection_reasons";
    if($data['rjtUpdateTime']!=''){
        $rejectQuery .= " where updated_datetime >'".$data['rjtUpdateTime']."'";
    }
    $rejectResult = $db->query($rejectQuery);
    //province details
    $provinceQuery = "select * from province_details";
    if($data['provinceUpdateTime']!=''){
        $provinceQuery .= " where updated_datetime >'".$data['provinceUpdateTime']."'";
    }
    $provinceResult = $db->query($provinceQuery);
    //facility data
    $facilityQuery = "select * from facility_details";
    if($data['facilityUpdateTime']!=''){
        $facilityQuery .= " where updated_datetime >'".$data['facilityUpdateTime']."'";
    }
    $facilityResult = $db->query($facilityQuery);
    echo json_encode(array('sampleType'=>$sTypeResult,'artCode'=>$artCodeResult,'rejectReason'=>$rejectResult,'province'=>$provinceResult,'facilityResult'=>$facilityResult));
}
?>