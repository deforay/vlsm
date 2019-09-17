<?php
ob_start();
session_start();
require_once('../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
//global config
$configQuery = "SELECT * from global_config";
$configResult = $db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
  $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
if ($sarr['user_type'] == 'remoteuser') {
  $vlfmQuery = "SELECT GROUP_CONCAT(DISTINCT vlfm.facility_id SEPARATOR ',') as facilityId FROM vl_user_facility_map as vlfm where vlfm.user_id='" . $_SESSION['userId'] . "'";
  $vlfmResult = $db->rawQuery($vlfmQuery);
}
if ($arr['vl_form'] == '3') {
  $option = "<option value=''> -- Sélectionner -- </option>";
} else {
  $option = "<option value=''> -- Select -- </option>";
}
if (isset($_POST['cName']) && !empty($_POST['cName'])) {
  $id = $_POST['cName'];
  $facilityQuery = "SELECT * from facility_details where facility_id=$id";
  $facilityInfo = $db->query($facilityQuery);
  if ($facilityInfo) {
    $provinceName = $facilityInfo[0]['facility_state'];
    $pdQuery = "SELECT * from province_details"; // where province_name='" . $provinceName . "'";
    $pdResult = $db->query($pdQuery);
    $state = $option;
    foreach($pdResult as $pdRow){
      $selected = '';
      if($facilityInfo[0]['facility_state'] == $pdRow['province_name'] ){
        $selected = "selected='selected'";
      }  
      $state .= "<option value='" . $pdRow['province_name'] . "##" . (isset($pdRow['province_code']) && !empty($pdRow['province_code']) ? : $pdRow['province_name'] ) . "' $selected>" . ($pdRow['province_name']) . "</option>";
    }
    
    $district = '';
    if ($facilityInfo[0]['facility_district'] != '') {
      $district .= $option;
      $district .= "<option value='" . $facilityInfo[0]['facility_district'] . "' selected='selected'>" . ucwords($facilityInfo[0]['facility_district']) . "</option>";
    } else {
      $district .= $option;
    }
    echo $state . "###" . $district . "###" . $facilityInfo[0]['contact_person'];
  }
}
if (isset($_POST['pName']) && !empty($_POST['pName'])) {
  $provinceName = explode("##", $_POST['pName']);
  $dName = '';
  if (isset($_POST['dName']) && trim($_POST['dName']) != '') {
    $dName = " AND facility_district ='" . $_POST['dName'] . "'";
  }
  $facilityQuery = "SELECT * from facility_details where facility_state='" . $provinceName[0] . "' AND status='active'" . $dName;
  if (isset($vlfmResult[0]['facilityId'])) {
    $facilityQuery = $facilityQuery . " AND facility_id IN(" . $vlfmResult[0]['facilityId'] . ")";
  }
  if (isset($_POST['fType']) && trim($_POST['fType']) != '') {
    $facilityQuery = $facilityQuery . " AND facility_type='" . $_POST['fType'] . "'";
  }
  $facilityInfo = $db->query($facilityQuery);
  $facility = '';
  if ($facilityInfo) {
    if (!isset($_POST['comingFromUser'])) {
      $facility .= $option;
    }
    foreach ($facilityInfo as $fDetails) {
      $facility .= "<option data-code='" . $fDetails['facility_code'] . "' data-emails='" . $fDetails['facility_emails'] . "' data-mobile-nos='" . $fDetails['facility_mobile_numbers'] . "' data-contact-person='" . ucwords($fDetails['contact_person']) . "' value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . ' - ' . $fDetails['facility_code'] . "</option>";
    }
  } else {
    // if(isset($_POST['comingFromUser'])){
    //     $option = ' ';
    // }
    $facility .= $option;
  }
  $district = '';
  $facilityDistQuery = "SELECT DISTINCT facility_district from facility_details where facility_state='" . $provinceName[0] . "' AND status='active'";
  if (isset($vlfmResult[0]['facilityId'])) {
    $facilityDistQuery = $facilityDistQuery . " AND facility_id IN(" . $vlfmResult[0]['facilityId'] . ")";
  }
  //echo $facilityDistQuery;die;
  $facilityDistInfo = $db->query($facilityDistQuery);
  if ($facilityDistInfo) {
    $district .= $option;
    foreach ($facilityDistInfo as $districtName) {
      if (trim($districtName['facility_district']) != "") {
        $district .= "<option value='" . $districtName['facility_district'] . "'>" . ucwords($districtName['facility_district']) . "</option>";
      }
    }
  } else {
    $district .= $option;
  }
  echo $facility . "###" . $district . "###" . '';
}
if (isset($_POST['dName']) && trim($_POST['dName']) != '') {
  $distName = $_POST['dName'];
  $facilityQuery = "SELECT * from facility_details where facility_district LIKE '" . $distName . "' AND status='active'";
  if (isset($vlfmResult[0]['facilityId'])) {
    $facilityQuery = $facilityQuery . " AND facility_id IN(" . $vlfmResult[0]['facilityId'] . ")";
  }
  if (isset($_POST['fType']) && trim($_POST['fType']) != '') {
    $facilityQuery = $facilityQuery . " AND facility_type='" . $_POST['fType'] . "'";
  }
  //echo $facilityQuery; die;
  $facilityInfo = $db->query($facilityQuery);
  $facility = '';
  if ($facilityInfo) {
    if (!isset($_POST['comingFromUser'])) {
      $facility .= $option;
    }
    foreach ($facilityInfo as $fDetails) {
      $facility .= "<option data-code='" . $fDetails['facility_code'] . "' data-emails='" . $fDetails['facility_emails'] . "' data-mobile-nos='" . $fDetails['facility_mobile_numbers'] . "' data-contact-person='" . ucwords($fDetails['contact_person']) . "' value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . ' - ' . $fDetails['facility_code'] . "</option>";
    }
  } else {
    if (isset($_POST['comingFromUser'])) {
      $option = ' ';
    }
    $facility .= $option;
  }
  //$facilityQuery = "SELECT * from facility_details where facility_type=2 AND facility_district='".$distName."' AND status='active'";
  $facilityQuery = "SELECT * from facility_details where facility_type=2 AND status='active'";
  $facilityLabInfo = $db->query($facilityQuery);
  $facilityLab = '';
  if ($facilityLabInfo) {
    $facilityLab .= $option;
    foreach ($facilityLabInfo as $fDetails) {
      $facilityLab .= "<option value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . ' - ' . $fDetails['facility_code'] . "</option>";
    }
  } else {
    $facilityLab .= $option;
  }
  echo $facility . "###" . $facilityLab . "###";
}

//this statement coming from user
if (isset($_POST['fType']) && $_POST['fType'] != '' && !isset($_POST['dName']) && !isset($_POST['pName'])) {
  $facilityQuery = "SELECT * from facility_details where facility_type='" . $_POST['fType'] . "' AND status='active'";
  $facilityLabInfo = $db->query($facilityQuery);
  $facilityLab = '';
  if ($facilityLabInfo) {
    foreach ($facilityLabInfo as $fDetails) {
      $facilityLab .= "<option value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . ' - ' . $fDetails['facility_code'] . "</option>";
    }
  } else {
    $facilityLab .= ' ';
  }
  echo $facilityLab;
}
