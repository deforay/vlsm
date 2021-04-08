<?php
header('Content-Type: application/json');
require_once('../../../startup.php');

$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);

$input = json_decode(file_get_contents("php://input"),true);
$check = $app->fetchAuthToken($input);
if (isset($check['status']) && !empty($check['status']) && $check['status'] == false) {
    $payload = array(
        'status' => 0,
        'message'=> $check['message'],
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($payload);
    exit(0);
}

$data = array();
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$data['fundingSourceList'] = $db->query($fundingSourceQry);
/* To get testing platform names */
// Nationality
$nationalityQry = "SELECT * FROM `r_countries` ORDER BY `iso_name` ASC";
$nationalityResult = $db->query($nationalityQry);

foreach ($nationalityResult as $key=>$nrow) {
    $nationalityList[$key]['show'] = ucwords($nrow['iso_name']) . ' (' . $nrow['iso3'] . ')';
    $nationalityList[$key]['value'] = $nrow['id'];
}
$data['nationalityList'] = $nationalityList;
$pdQuery = "SELECT * from province_details";
if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "SELECT user_id FROM vl_user_facility_map WHERE user_id='" . $_SESSION['userId'] . "'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if ($chkUserFcMapResult) {
        $pdQuery = "SELECT * FROM province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
    }
    $rKey = 'R';
} else {
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
$data['province'] = $db->query($pdQuery);

$covid19Obj = new \Vlsm\Models\Covid19($db);
$facilitiesDb = new \Vlsm\Models\Facilities($db);

$data['healthFacilities'] = $app->getHealthFacilities('covid19', $check['data']['user_id'], true, 1);
$data['covid19ReasonsForTesting'] = $app->generateSelectOptions($covid19Obj->getCovid19ReasonsForTesting());
$data['specimenTypeResult'] = $app->generateSelectOptions($covid19Obj->getCovid19SampleTypes());
$data['testingLabs'] = $app->generateSelectOptions($facilitiesDb->getTestingLabs('covid19'));
//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$data['implementingPartnerList'] = $db->query($implementingPartnerQry);
$payload = array(
    'status' => 1,
    'message'=>'Success',
    'data' => $data,
    'timestamp' => $general->getDateTime()
);

echo json_encode($payload);