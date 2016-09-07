<?php
ob_start();
include('./includes/MysqliDb.php');
if(isset($_POST['cName'])){
$id=$_POST['cName'];
$facilityQuery="SELECT * from facility_details where facility_id=$id";
$facilityInfo=$db->query($facilityQuery);
if($facilityInfo){
    $provinceid = $facilityInfo[0]['state'];
    $pQuery="SELECT * FROM province_details where province_id='".$provinceid."'";
    $pResult = $db->rawQuery($pQuery);
    $state = '';
    if($pResult){
        $state.="<option value=''>--select--</option>";
        foreach($pResult as $province){
            $state .= "<option value='".$province['province_id']."'>".ucwords($province['province_name'])."</option>";
        }
    }else{
        $state.="<option value=''>--select--</option>";
    }
    $facilityQuery="SELECT * from facility_details where state='".$provinceid."'";
    $facilityInfo=$db->query($facilityQuery);
    $district = '';
    if($facilityInfo){
        $district.="<option value=''>--select--</option>";
        foreach($facilityInfo as $districtName){
            $district .= "<option value='".$districtName['district']."'>".ucwords($districtName['district'])."</option>";
        }
    }else{
        $district.="<option value=''>--select--</option>";
    }
    echo $state."##".$district."##".$facilityInfo[0]['contact_person'];
}
}
if(isset($_POST['pName'])){
   $id=$_POST['pName'];
    $facilityQuery="SELECT * from facility_details where state=$id";
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
            $district .= "<option value='".$districtName['district']."'>".ucwords($districtName['district'])."</option>";
        }
    }else{
        $district.="<option value=''>--select--</option>";
    }
    echo $facility."##".$district."##".'';
}

?>