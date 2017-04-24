<?php
ob_start();
include('MysqliDb.php');
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
        $state.="<option value=''> -- Select -- </option>";
            $state .= "<option value='".$facilityInfo[0]['facility_state']."##".$pdResult[0]['province_code']."##".$facilityInfo[0]['facility_code']."' selected='selected'>".ucwords($facilityInfo[0]['facility_state'])."</option>";
    }else{
        $state.="<option value=''> -- Select -- </option>";
    }
    
    $district = '';
    if($facilityInfo[0]['facility_district']!=''){
        $district.="<option value=''> -- Select -- </option>";
            $district .= "<option value='".$facilityInfo[0]['facility_district']."' selected='selected'>".ucwords($facilityInfo[0]['facility_district'])."</option>";
    }else{
        $district.="<option value=''> -- Select -- </option>";
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
    $facilityQuery="SELECT * from facility_details where facility_state='".$provinceName[0]."'".$dName;
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){
        $facility.="<option data-code='' value=''> -- Select -- </option>";
        foreach($facilityInfo as $fDetails){
            $facility .= "<option data-code='".$fDetails['facility_code']."' value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
        }
    }else{
        $facility.="<option data-code='' value=''> -- Select -- </option>";
    }
    $district = '';
    $facilityDistQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$provinceName[0]."'";
    $facilityDistInfo=$db->query($facilityDistQuery);
    if($facilityDistInfo){
        $district.="<option value=''> -- Select -- </option>";
        foreach($facilityDistInfo as $districtName){
            if(trim($districtName['facility_district'])!=""){
               $district .= "<option value='".$districtName['facility_district']."'>".ucwords($districtName['facility_district'])."</option>";
            }
        }
    }else{
        $district.="<option value=''> -- Select -- </option>";
    }
    echo $facility."###".$district."###".'';
}
if(isset($_POST['dName']) && trim($_POST['dName'])!=''){
   $distName=$_POST['dName'];
    $facilityQuery="SELECT * from facility_details where facility_district='".$distName."'";
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){ ?>
        <option data-code='' value=''> -- Select -- </option>
        <?php
        foreach($facilityInfo as $fDetails){ ?>
            <option data-code="<?php echo $fDetails['facility_code']; ?>" value="<?php echo $fDetails['facility_id'];?>" <?php echo ($_POST['cliName']==$fDetails['facility_id'])?'selected="selected"':'';?>><?php echo ucwords($fDetails['facility_name']);?></option>
            <?php
        }
    }else{ ?>
        <option data-code='' value=''> -- Select -- </option>
        <?php
    }
}

?>