<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);
$userDb = new \Vlsm\Models\Users($db);
$covid19Obj = new \Vlsm\Models\Covid19($db);
$facilitiesDb = new \Vlsm\Models\Facilities($db);

$input = json_decode(file_get_contents("php://input"), true);

$auth = $general->getHeader('Authorization');
if (!empty($auth)) {
    $authToken = str_replace("Bearer ", "", $auth);
    /* Check if API token exists */
    $user = $userDb->getAuthToken($authToken);
}

// If authentication fails then do not proceed
if (empty($user) || empty($user['user_id'])) {
    $response = array(
        'status' => 'failed',
        'timestamp' => time(),
        'error' => 'Bearer Token Invalid',
        'data' => array()
    );
    http_response_code(401);
    echo json_encode($response);
    exit(0);
}
/* $check = $app->fetchAuthToken($input);
if (!isset($user['user_id']) && $user['user_id'] != "") {
    $payload = array(
        'status' => 0,
        'message'=> $check['message'],
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($payload);
    exit(0);
} */

$data = array();
/* Source of Alert list */
$sourceOfAlertList = array();
$sourceOfAlert = array('Hotline', 'Community Surveillance', 'POE', 'Contact Tracing', 'Clinic', 'Sentinel Site', 'Screening', 'Others');
foreach ($sourceOfAlert as $key => $src) {
    $sourceOfAlertList[$key]['value'] = strtolower(str_replace(" ", "-", $src));
    $sourceOfAlertList[$key]['show'] = $src;
}
$data['sourceOfAlertList'] = $sourceOfAlertList;

/* Province Details */
$data['provinceList'] = $app->getProvinceDetails($check['data']['user_id'], true);

/* District Details */
$data['districtList'] = $app->getDistrictDetails($check['data']['user_id'], true);

/* Health Facility Details */
// $data['healthFacilitiesList'] = $app->getHealthFacilities('covid19', $check['data']['user_id'], true, 1);

/* Funding Source List */
$fundingSourceList = array();
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceResult = $db->query($fundingSourceQry);
foreach ($fundingSourceResult as $funding) {
    $fundingSourceList[$funding['funding_source_id']] = $funding['funding_source_name'];
}
$data['fundingSourceList'] = $app->generateSelectOptions($fundingSourceList);

/* Implementing Partner Details */
$implementingPartnerList = array();
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerResult = $db->query($implementingPartnerQry);
foreach ($implementingPartnerResult as $key => $ip) {
    $implementingPartnerList[$key]['value'] = strtolower(str_replace(" ", "-", $ip['i_partner_id']));
    $implementingPartnerList[$key]['show'] = $ip['i_partner_name'];
}
$data['implementingPartnerList'] = $implementingPartnerList;

/* Nationality Details */
$nationalityQry = "SELECT * FROM `r_countries` ORDER BY `iso_name` ASC";
$nationalityResult = $db->query($nationalityQry);
foreach ($nationalityResult as $key => $nrow) {
    $nationalityList[$key]['show'] = ucwords($nrow['iso_name']) . ' (' . $nrow['iso3'] . ')';
    $nationalityList[$key]['value'] = $nrow['id'];
}
$data['nationalityList'] = $nationalityList;

/* Type of Test Request */
$typeOfTestReqList = array();
$typeOfTestReqResult = array('Real Time RT-PCR', 'RDT-Antibody', 'RDT-Antigen', 'ELISA');
foreach ($typeOfTestReqResult as $key => $req) {
    $typeOfTestReqList[$key]['value'] = ($key + 1);
    $typeOfTestReqList[$key]['show'] = $req;
}
$data['typeOfTestRequestList'] = $typeOfTestReqList;

$data['covid19ReasonsForTestingList'] = $app->generateSelectOptions($covid19Obj->getCovid19ReasonsForTesting());
$data['specimenTypeResultList'] = $app->generateSelectOptions($covid19Obj->getCovid19SampleTypes());
foreach (range(1, 5) as $key => $req) {
    $typeOfTestReqList[$key]['value'] = $req;
    $typeOfTestReqList[$key]['show'] = $req;
}
$data['testingPoint'] = $typeOfTestReqList;
$data['testingLabsList'] = $app->getHealthFacilities('covid19', null, true, 2);
/* Type of Test Request */
$qualityList = array();
$qualityResults = array('Good', 'Poor');
foreach ($qualityResults as $key => $req) {
    $qualityList[$key]['value'] = strtolower($req);
    $qualityList[$key]['show'] = $req;
}
$data['qualityList'] = $qualityList;

/* Rejected Reason*/
$rejectionTypeQuery = "SELECT DISTINCT rejection_type FROM r_covid19_sample_rejection_reasons WHERE rejection_reason_status ='active' GROUP BY rejection_type";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
$rejectionReason = array();
foreach ($rejectionTypeResult as $key => $type) {
    $rejectionReason[$key]['show'] = ucwords($type['rejection_type']);
    $rejectionQuery = "SELECT * FROM r_covid19_sample_rejection_reasons where rejection_reason_status = 'active' AND rejection_type LIKE '" . $type['rejection_type'] . "%'";
    $rejectionResult = $db->rawQuery($rejectionQuery);
    foreach ($rejectionResult as $subKey => $reject) {
        $rejectionReason[$key]['reasons'][$subKey]['value'] = $reject['rejection_reason_id'];
        $rejectionReason[$key]['reasons'][$subKey]['show'] = ucwords($reject['rejection_reason_name']);
    }
}
$data['rejectedReasonList'] = $rejectionReason;

/* Testing Platform Details */
$testPlatformList = array();
$testPlatformResult = $general->getTestingPlatforms('covid19');
foreach ($testPlatformResult as $row) {
    $testPlatformList[$row['machine_name']] = $row['machine_name'];
}
$data['testPlatformList'] = $app->generateSelectOptions($testPlatformList);

/* Lab Technician Details */
$labTechnicians = $userDb->getActiveUserInfo();
foreach ($labTechnicians as $labTech) {
    $labTechniciansList[$labTech['user_id']] = ucwords($labTech['user_name']);
}
$data['labTechniciansList'] = $app->generateSelectOptions($labTechniciansList);
$data['resultsList'] = $app->generateSelectOptions($covid19Obj->getCovid19Results());
$data['symptomsList'] = $app->generateSelectOptions($covid19Obj->getCovid19Symptoms());
$data['comorbiditiesList'] = $app->generateSelectOptions($covid19Obj->getCovid19Comorbidities());

$payload = array(
    'status' => 1,
    'message' => 'Success',
    'data' => $data,
    'timestamp' => $general->getDateTime()
);

echo json_encode($payload);
