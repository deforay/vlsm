<?php
ob_start();
session_start();
include('MysqliDb.php');
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
    $vlfmQuery="SELECT GROUP_CONCAT(DISTINCT vlfm.facility_id SEPARATOR ',') as facilityId FROM vl_user_facility_map as vlfm where vlfm.user_id='".$_SESSION['userId']."'";
    $vlfmResult = $db->rawQuery($vlfmQuery);
}
if($arr['vl_form']=='3'){
  $option = "<option value=''> -- Sélectionner -- </option>";
}else{
  $option = "<option value=''> -- Select -- </option>";
}
if(isset($_POST['cName'])){
    $id=$_POST['cName'];
    $facilityQuery="SELECT * from facility_details where facility_id=$id";
    $facilityInfo=$db->query($facilityQuery);
    if($facilityInfo){
        $provinceName = $facilityInfo[0]['facility_state'];
        $pdQuery="SELECT * from province_details where province_name='".$provinceName."'";
        $pdResult=$db->query($pdQuery);
        $state = '';
        if($facilityInfo[0]['facility_state']!=''){
            $state.=$option;
                $state .= "<option value='".$facilityInfo[0]['facility_state']."##".$pdResult[0]['province_code']."##".$facilityInfo[0]['facility_code']."' selected='selected'>".ucwords($facilityInfo[0]['facility_state'])."</option>";
        }else{
            $state.=$option;
        }
        
        $district = '';
        if($facilityInfo[0]['facility_district']!=''){
            $district.=$option;
                $district .= "<option value='".$facilityInfo[0]['facility_district']."' selected='selected'>".ucwords($facilityInfo[0]['facility_district'])."</option>";
        }else{
            $district.=$option;
        }
        echo $state."###".$district."###".$facilityInfo[0]['contact_person'];
    }
}
if(isset($_POST['pName'])){
    $provinceName=explode("##",$_POST['pName']);
    $dName = '';
    if(isset($_POST['dName']) && trim($_POST['dName'])!=''){
     $dName = " AND facility_district ='".$_POST['dName']."'";
    }
    $facilityQuery="SELECT * from facility_details where facility_state='".$provinceName[0]."' AND status='active'".$dName;
    if(isset($vlfmResult[0]['facilityId']))
    {
      $facilityQuery = $facilityQuery." AND facility_id IN(".$vlfmResult[0]['facilityId'].")";
    }
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){
        $facility.=$option;
        foreach($facilityInfo as $fDetails){
            $facility .= "<option data-code='".$fDetails['facility_code']."' data-emails='".$fDetails['facility_emails']."' data-mobile-nos='".$fDetails['facility_mobile_numbers']."' data-contact-person='".ucwords($fDetails['contact_person'])."' value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name']).' - '.$fDetails['facility_code']."</option>";
        }
    }else{
        $facility.=$option;
    }
    $district = '';
    $facilityDistQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$provinceName[0]."' AND status='active'";
    if(isset($vlfmResult[0]['facilityId']))
    {
      $facilityDistQuery = $facilityDistQuery." AND facility_id IN(".$vlfmResult[0]['facilityId'].")";
    }
    $facilityDistInfo=$db->query($facilityDistQuery);
    if($facilityDistInfo){
        $district.=$option;
        foreach($facilityDistInfo as $districtName){
            if(trim($districtName['facility_district'])!=""){
               $district .= "<option value='".$districtName['facility_district']."'>".ucwords($districtName['facility_district'])."</option>";
            }
        }
    }else{
        $district.=$option;
    }
    echo $facility."###".$district."###".'';
}
if(isset($_POST['dName']) && trim($_POST['dName'])!=''){
    $distName = $_POST['dName'];
    $facilityQuery = "SELECT * from facility_details where facility_type=1 AND facility_district='".$distName."' AND status='active'";
    if(isset($vlfmResult[0]['facilityId'])){
      $facilityQuery = $facilityQuery." AND facility_id IN(".$vlfmResult[0]['facilityId'].")";
    }
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){
        $facility .= $option;
        foreach($facilityInfo as $fDetails){
          $facility .= "<option data-code='".$fDetails['facility_code']."' data-emails='".$fDetails['facility_emails']."' data-mobile-nos='".$fDetails['facility_mobile_numbers']."' data-contact-person='".ucwords($fDetails['contact_person'])."' value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name']).' - '.$fDetails['facility_code']."</option>";
        }
    }else{
      $facility .= $option;
    }
    //$facilityQuery = "SELECT * from facility_details where facility_type=2 AND facility_district='".$distName."' AND status='active'";
    $facilityQuery = "SELECT * from facility_details where facility_type=2 AND status='active'";
    $facilityLabInfo = $db->query($facilityQuery);
    $facilityLab = '';
    if($facilityLabInfo){
        $facilityLab .= $option;
        foreach($facilityLabInfo as $fDetails){
          $facilityLab .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name']).' - '.$fDetails['facility_code']."</option>";
        }
    }else{
      $facilityLab .= $option;
    }
  echo $facility ."###".$facilityLab."###";
}
?>