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
        $state.="<option value=''> -- Select -- </option>";
            $state .= "<option value='".$facilityInfo[0]['state']."##".$pdResult[0]['province_code']."'>".ucwords($facilityInfo[0]['state'])."</option>";
    }else{
        $state.="<option value=''> -- Select -- </option>";
    }
    
    $district = '';
    if($facilityInfo[0]['district']!=''){
        $district.="<option value=''> -- Select -- </option>";
            $district .= "<option value='".$facilityInfo[0]['district']."'>".ucwords($facilityInfo[0]['district'])."</option>";
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
    $dName = " AND district='".$_POST['dName']."'";
   }
    $facilityQuery="SELECT * from facility_details where state='".$provinceName[0]."'".$dName;
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){
        $facility.="<option value=''> -- Select -- </option>";
        foreach($facilityInfo as $fDetails){
            $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
        }
    }else{
        $facility.="<option value=''> -- Select -- </option>";
    }
    $district = '';
    $facilityDistQuery="SELECT DISTINCT district from facility_details where state='".$provinceName[0]."'";
    $facilityDistInfo=$db->query($facilityDistQuery);
    if($facilityDistInfo){
        $district.="<option value=''> -- Select -- </option>";
        foreach($facilityDistInfo as $districtName){
            if(trim($districtName['district'])!=""){
            $district .= "<option value='".$districtName['district']."'>".ucwords($districtName['district'])."</option>";
            }
        }
    }else{
        $district.="<option value=''> -- Select -- </option>";
    }
    echo $facility."###".$district."###".'';
}
if(isset($_POST['dName']) && trim($_POST['dName'])!=''){
   $distName=$_POST['dName'];
    $facilityQuery="SELECT * from facility_details where district='".$distName."'";
    $facilityInfo=$db->query($facilityQuery);
    $facility = '';
    if($facilityInfo){ ?>
        <option value=''> -- Select -- </option>
        <?php
        foreach($facilityInfo as $fDetails){ ?>
            <option value="<?php echo $fDetails['facility_id'];?>" <?php echo ($_POST['cliName']==$fDetails['facility_id'])?'selected="selected"':'';?>><?php echo ucwords($fDetails['facility_name']);?></option>
            <?php
        }
    }else{ ?>
        <option value=''> -- Select -- </option>
        <?php
    }
}

?>