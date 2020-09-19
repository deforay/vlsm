<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
#require_once('../startup.php');


$general = new \Vlsm\Models\General($db);
$facilitiesDb = new \Vlsm\Models\Facilities($db);

$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();

$facilityMap = $facilitiesDb->getFacilityMap($_SESSION['userId']);

if ($arr['vl_form'] == '3') {
  $option = "<option value=''> -- Sélectionner -- </option>";
} else {
  $option = "<option value=''> -- Select -- </option>";
}

if (!empty($_POST['testType'])) {
}

$facilityIdRequested = !empty($_POST['cName']) ? $_POST['cName'] : null;
$provinceRequested = !empty($_POST['pName']) ? $_POST['pName'] : null;
$districtRequested = !empty($_POST['dName']) ? $_POST['dName'] : null;
$facilityTypeRequested = !empty($_POST['fType']) ? $_POST['fType'] : null;

if (!empty($facilityIdRequested)) {

  $facilityQuery = "SELECT * FROM facility_details WHERE facility_id=$facilityIdRequested";
  $facilityInfo = $db->rawQueryOne($facilityQuery);
  if ($facilityInfo) {
    $provinceName = $facilityInfo['facility_state'];
    $pdQuery = "SELECT * FROM province_details";
    $pdResult = $db->query($pdQuery);
    $state = $option;
    foreach ($pdResult as $pdRow) {
      $selected = '';
      if ($facilityInfo['facility_state'] == $pdRow['province_name']) {
        $selected = "selected='selected'";
      }
      $state .= "<option data-code='" . $pdRow['province_code'] . "' data-province-id='" . $pdRow['province_id'] . "' data-name='" . $pdRow['province_name'] . "' value='" . $pdRow['province_name'] . "##" . $pdRow['province_code'] . "' $selected>" . ucwords($pdRow['province_name']) . "</option>";
    }

    $district = '';
    if ($facilityInfo['facility_district'] != '') {
      $district .= $option;
      $district .= "<option value='" . $facilityInfo['facility_district'] . "' selected='selected'>" . ucwords($facilityInfo['facility_district']) . "</option>";
    } else {
      $district .= $option;
    }
    echo $state . "###" . $district . "###" . $facilityInfo['contact_person'];
  }
} else if (!empty($provinceRequested)) {

  $provinceName = explode("##", $provinceRequested);
  $dName = '';
  if (!empty($districtRequested)) {
    $dName = " AND facility_district ='" . $districtRequested . "'";
  }
  $facilityQuery = "SELECT * FROM facility_details WHERE facility_state='" . $provinceName[0] . "' AND `status`='active'" . $dName;
  if (!empty($facilityMap)) {
    $facilityQuery = $facilityQuery . " AND facility_id IN(" . $facilityMap . ")";
  }
  if (!empty($facilityTypeRequested)) {
    $facilityQuery = $facilityQuery . " AND facility_type='" . $facilityTypeRequested . "'";
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
  $facilityDistQuery = "SELECT DISTINCT facility_district FROM facility_details WHERE facility_state='" . $provinceName[0] . "' AND `status`='active'";
  if (!empty($facilityMap)) {
    $facilityDistQuery = $facilityDistQuery . " AND facility_id IN(" . $facilityMap . ")";
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
} else if (!empty($districtRequested)) {
  $distName = $districtRequested;
  $facilityQuery = "SELECT * FROM facility_details where facility_district LIKE '" . $distName . "' AND `status`='active'";
  if (!empty($facilityMap)) {
    $facilityQuery = $facilityQuery . " AND facility_id IN(" . $facilityMap . ")";
  }
  if (!empty($facilityTypeRequested)) {
    $facilityQuery = $facilityQuery . " AND facility_type='" . $facilityTypeRequested . "'";
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
      $option = '';
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
