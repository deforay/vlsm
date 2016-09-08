<?php
ob_start();
include('./includes/MysqliDb.php');
if(isset($_POST['cName'])){
$id=$_POST['cName'];
$facilityQuery="SELECT * from facility_details where facility_id=$id";
$facilityInfo=$db->query($facilityQuery);
if($facilityInfo){
    $provinceName = $facilityInfo[0]['state'];
    $pdQuery="SELECT * from province_details where province_name='".$provinceName."'";
    $pdResult=$db->query($pdQuery);
    $state = '';
    if($facilityInfo[0]['state']!=''){
        $state.="<option value=''>--select--</option>";
            $state .= "<option value='".$facilityInfo[0]['state']."##".$pdResult[0]['province_code']."'>".ucwords($facilityInfo[0]['state'])."</option>";
    }else{
        $state.="<option value=''>--select--</option>";
    }
    
    $facilityQuery="SELECT * from facility_details where state='".$provinceName."'";
    $facilityInfo=$db->query($facilityQuery);
    $district = '';
    if($facilityInfo){
        $district.="<option value=''>--select--</option>";
        foreach($facilityInfo as $districtName){
            if($districtName['district']!=''){
            $district .= "<option value='".$districtName['district']."'>".ucwords($districtName['district'])."</option>";
            }
        }
    }else{
        $district.="<option value=''>--select--</option>";
    }
    echo $state."###".$district."###".$facilityInfo[0]['contact_person'];
}
}
if(isset($_POST['pName'])){
   $provinceName=explode("##",$_POST['pName']);
    $facilityQuery="SELECT * from facility_details where state='".$provinceName[0]."'";
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){
        $facility.="<option value=''>--select--</option>";
        foreach($facilityInfo as $fDetails){
            $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
        }
    }else{
        $facility.="<option value=''>--select--</option>";
    }
    $district = '';
    if($facilityInfo){
        $district.="<option value=''>--select--</option>";
        foreach($facilityInfo as $districtName){
            if(trim($districtName['district'])!=""){
            $district .= "<option value='".$districtName['district']."'>".ucwords($districtName['district'])."</option>";
            }
        }
    }else{
        $district.="<option value=''>--select--</option>";
    }
    echo $facility."###".$district."###".'';
}

?>