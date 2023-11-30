<?php
// Allow from any origin
use App\Services\TbService;
use App\Services\VlService;
use App\Services\ApiService;
use App\Services\EidService;
use App\Services\UsersService;
use App\Services\CommonService;
use App\Services\SystemService;
use App\Services\Covid19Service;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\GenericTestsService;
use App\Services\GeoLocationsService;

/** @var Slim\Psr7\Request $request */
$request = $GLOBALS['request'];

$input = $request->getParsedBody();

$applicationConfig = ContainerRegistry::get('applicationConfig');

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var ApiService $app */
$app = ContainerRegistry::get(ApiService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

/** @var GeoLocationsService $geolocationService */
$geolocationService = ContainerRegistry::get(GeoLocationsService::class);

$transactionId = $general->generateUUID();
$formId = $general->getGlobalConfig('vl_form');

$authToken = $general->getAuthorizationBearerToken();
$user = $usersService->getUserByToken($authToken);

$updatedDateTime = $input['latestDateTime'] ?? null;
/* Status name list */
$statusList = [];
$tsQuery = "SELECT status_id, status_name
                FROM r_sample_status
                WHERE status = 'active'";
$tsResult = $db->rawQuery($tsQuery);
foreach ($tsResult as $row) {
    $statusList[$row['status_id']] = $row['status_name'];
}
// Check if covid-19 module active/inactive
$status = false;
// Funding Sources List
$fundingSourceList = [];
$fundingSourceQry = "SELECT funding_source_id, funding_source_name
                        FROM r_funding_sources
                        WHERE funding_source_status='active' ";
if ($updatedDateTime) {
    $fundingSourceQry .= " AND updated_datetime >= '$updatedDateTime'";
}
$fundingSourceQry .= " ORDER BY funding_source_name ASC";

$fundingSourceResult = $db->query($fundingSourceQry);
foreach ($fundingSourceResult as $funding) {
    $fundingSourceList[$funding['funding_source_id']] = $funding['funding_source_name'];
}
// Implementing Partners List
$implementingPartnerList = [];
$implementingPartnerQry = "SELECT i_partner_id, i_partner_name
                            FROM r_implementation_partners
                            WHERE i_partner_status='active' ";
if ($updatedDateTime) {
    $implementingPartnerQry .= " AND updated_datetime >= '$updatedDateTime'";
}
$implementingPartnerQry .= " ORDER BY i_partner_name ASC";

$implementingPartnerResult = $db->query($implementingPartnerQry);
foreach ($implementingPartnerResult as $key => $ip) {
    $implementingPartnerList[$key]['value'] = strtolower(str_replace(" ", "-", (string) $ip['i_partner_id']));
    $implementingPartnerList[$key]['show'] = $ip['i_partner_name'];
}
// Countries List
$nationalityQry = "SELECT iso_name, iso3, id FROM `r_countries` ORDER BY `iso_name` ASC";
$nationalityResult = $db->query($nationalityQry);
foreach ($nationalityResult as $key => $nrow) {
    $nationalityList[$key]['show'] = ($nrow['iso_name']) . ' (' . $nrow['iso3'] . ')';
    $nationalityList[$key]['value'] = $nrow['id'];
}
$commonResultsList = [];
$commonResult = ['positive', 'negative', 'unknown'];
foreach ($commonResult as $key => $result) {
    $commonResultsList[$key]['value'] = $result;
    $commonResultsList[$key]['show'] = ($result);
}
/* Lab Technician Details */
$facilityMap = $facilitiesService->getUserFacilityMap($user['user_id']);
$userResult = $usersService->getActiveUsers($facilityMap, $updatedDateTime);
$labTechniciansList = [];
foreach ($userResult as $row) {
    $labTechniciansList[$row['user_id']] = ($row['user_name']);
}

$activeModule = SystemService::getActiveModules(true);

$data = [];
$data['formId'] = $formId;
$data['activeModule'] = implode(",", $activeModule);
$data['facilitiesList'] = $app->getAppHealthFacilities(null, $user['user_id'], false, 0, false, null, $updatedDateTime);
$data['geoGraphicalDivision'] = $geolocationService->fetchActiveGeolocations("", "", "no", true, null, $updatedDateTime);
$data['healthFacilitiesList'] = $app->getAppHealthFacilities(null, $user['user_id'], true, 1, false, implode(",", $activeModule), $updatedDateTime);
$data['testingLabsList'] = $app->getTestingLabs(null, $user['user_id'], false, false, implode(",", $activeModule), $updatedDateTime);
/* Province Details */
$data['provinceList'] = $app->getProvinceDetails($user['user_id'], true, $updatedDateTime);
/* District Details */
$data['districtList'] = $app->getDistrictDetails($user['user_id'], true, $updatedDateTime);
$data['implementingPartnerList'] = $implementingPartnerList;
$data['fundingSourceList'] = $app->generateSelectOptions($fundingSourceList);
$data['nationalityList'] = $nationalityList;
$data['labTechniciansList'] = $app->generateSelectOptions($labTechniciansList);
$data['sampleStatusList'] = $app->generateSelectOptions($statusList);

$modules = SYSTEM_CONFIG['modules'];

$rejectionReason = [];
foreach ($modules as $module => $status) {
    $rejectionResult = [];
    $rejectionTypeResult = [];
    $reasons[$module] = [];
    if ($status) {
        if ($module == 'common') {
            continue;
        }
        $module = ($module == 'generic-tests') ? 'generic' : $module;

        $condition = " rejection_reason_status ='active' ";
        if ($updatedDateTime) {
            $condition .= " AND updated_datetime >= '$updatedDateTime'";
        }
        $rejectionTypeResult = $general->getDataByTableAndFields('r_' . $module . '_sample_rejection_reasons', array('rejection_type'), false, $condition, 'rejection_type');
        foreach ($rejectionTypeResult as $key => $type) {
            $reasons[$module][$key]['show'] = ucwords((string) $type['rejection_type']);
            $condition = " rejection_reason_status ='active' AND rejection_type LIKE '" . $type['rejection_type'] . "'";
            if ($updatedDateTime) {
                $condition .= " AND updated_datetime >= '$updatedDateTime'";
            }
            $rejectionResult = $general->getDataByTableAndFields('r_' . $module . '_sample_rejection_reasons', array('rejection_reason_id', 'rejection_reason_name'), false, $condition);
            foreach ($rejectionResult as $subKey => $reject) {
                $reasons[$module][$key]['reasons'][$subKey]['value'] = $reject['rejection_reason_id'];
                $reasons[$module][$key]['reasons'][$subKey]['show'] = ($reject['rejection_reason_name']);
            }
        }
    }
    $rejectionReason[$module] = $reasons[$module];
}
// print_r($responseRejections);die;

if (
    isset($applicationConfig['modules']['covid19'])
    && $applicationConfig['modules']['covid19'] === true
) {

    /** @var Covid19Service $covid19Service */
    $covid19Service = ContainerRegistry::get(Covid19Service::class);


    // Sources of Alerts List
    $sourceOfAlertList = [];
    $sourceOfAlert = [
        'Hotline',
        'Community Surveillance',
        'POE',
        'Contact Tracing',
        'Clinic',
        'Sentinel Site',
        'Screening',
        'Others'
    ];
    foreach ($sourceOfAlert as $key => $src) {
        $sourceOfAlertList[$key]['value'] = strtolower(str_replace(" ", "-", $src));
        $sourceOfAlertList[$key]['show'] = $src;
    }
    $data['covid19']['sourceOfAlertList'] = $sourceOfAlertList;

    /* Province Details */
    $data['covid19']['provinceList'] = $app->getProvinceDetails($user['user_id'], true, $updatedDateTime);
    /* District Details */
    $data['covid19']['districtList'] = $app->getDistrictDetails($user['user_id'], true, $updatedDateTime);
    /* Health Facility Details */
    $data['covid19']['fundingSourceList'] = $app->generateSelectOptions($fundingSourceList);
    $data['covid19']['implementingPartnerList'] = $implementingPartnerList;
    $data['covid19']['nationalityList'] = $nationalityList;

    /* Type of Test Request */
    $typeOfTestReqList = [];
    if ($formId == COUNTRY\DRC) {
        $typeOfTestReqResult = [
            "PCR/RT-PCR",
            "RdRp-SARS Cov-2",
            "GeneXpert",
            "Rapid Antigen Test",
            "Other"
        ];
    } else {
        $typeOfTestReqResult = [
            'Real Time RT-PCR',
            'RDT-Antibody',
            'RDT-Antigen',
            'ELISA',
            'Others'
        ];
    }
    foreach ($typeOfTestReqResult as $key => $req) {
        $typeOfTestReqList[$key]['value'] = $req;
        $typeOfTestReqList[$key]['show'] = $req;
    }
    $data['covid19']['typeOfTestRequestList'] = $typeOfTestReqList;
    $platformTestKits = [];
    $platformTestKitsResult = ['Abbott Panbio™ COVID-19 Ag Test', 'STANDARD™ Q COVID-19 Ag Test', 'LumiraDx ™ SARS-CoV-2 Ag Test', 'Sure Status® COVID-19 Antigen Card Test'];
    foreach ($platformTestKitsResult as $key => $req) {
        $platformTestKits[$key]['value'] = $req;
        $platformTestKits[$key]['show'] = $req;
    }
    $data['covid19']['rdtAntigenOptions'] = $platformTestKits;

    $data['covid19']['covid19ReasonsForTestingList'] = $app->generateSelectOptions($covid19Service->getCovid19ReasonsForTesting($updatedDateTime));
    $data['covid19']['specimenTypeResultList'] = $app->generateSelectOptions($covid19Service->getCovid19SampleTypes($updatedDateTime));
    foreach (range(1, 5) as $key => $req) {
        $testingPoint[$key]['value'] = $req;
        $testingPoint[$key]['show'] = $req;
    }
    $data['covid19']['testingPoint'] = $testingPoint;
    $data['covid19']['testingLabsList'] = $app->getTestingLabs('covid19', null, true, false, $updatedDateTime);
    /* Type of Test Request */
    $qualityList = [];
    $qualityResults = ['Good', 'Poor'];
    foreach ($qualityResults as $key => $req) {
        $qualityList[$key]['value'] = strtolower($req);
        $qualityList[$key]['show'] = $req;
    }
    $data['covid19']['qualityList'] = $qualityList;

    /* Rejected Reason*/
    $data['covid19']['rejectedReasonList'] = $rejectionReason['covid19'];

    /* Testing Platform Details */
    $testPlatformList = [];
    $testPlatformResult = $general->getTestingPlatforms('covid19');
    foreach ($testPlatformResult as $row) {
        $testPlatformList[$row['machine_name']] = $row['machine_name'];
    }
    $data['covid19']['testPlatformList'] = $app->generateSelectOptions($testPlatformList);

    $data['covid19']['resultsList'] = $app->generateSelectOptions($covid19Service->getCovid19Results($updatedDateTime));
    $data['covid19']['symptomsList'] = $app->generateSelectOptions($covid19Service->getCovid19Symptoms($updatedDateTime));
    $data['covid19']['comorbiditiesList'] = $app->generateSelectOptions($covid19Service->getCovid19Comorbidities($updatedDateTime));

    $data['covid19']['statusFilterList'] = [
        ['value' => '7', 'show' => 'Approved'],
        ['value' => '1', 'show' => 'Pending'],
        ['value' => '4', 'show' => 'Rejected']
    ];
    $status = true;
}

// Check if eid module active/inactive
if (isset($applicationConfig['modules']['eid']) && $applicationConfig['modules']['eid'] === true) {

    /** @var EidService $eidService */
    $eidService = ContainerRegistry::get(EidService::class);

    // SITE INFORMATION SECTION
    // Province Details
    $data['eid']['provinceList'] = $app->getProvinceDetails($user['user_id'], true, $updatedDateTime);
    // District Details
    $data['eid']['districtList'] = $app->getDistrictDetails($user['user_id'], true, $updatedDateTime);

    // Infant and Mother's Health Information Section
    $data['eid']['mothersHIVStatus'] = $commonResultsList;

    $motherTreatmentList = [];
    $motherTreatmentArray = ['No ART given', 'Pregnancy', 'Labour/Delivery', 'Postnatal', 'Unknown'];
    foreach ($motherTreatmentArray as $key => $treatment) {
        $motherTreatmentList[$key]['value'] = $treatment;
        $motherTreatmentList[$key]['show'] = $treatment;
    }
    $data['eid']['motherTreatment'] = $motherTreatmentList;
    $data['eid']['rapidTestResult'] = $app->generateSelectOptions($eidService->getEidResults($updatedDateTime));
    $data['eid']['prePcrTestResult'] = $commonResultsList;

    $pcrTestReasonList = [];
    $pcrTestReasonArray = [
        'Confirmation of positive first EID PCR test result',
        'Repeat EID PCR test 6 weeks after stopping breastfeeding for children < 9 months',
        'Positive HIV rapid test result at 9 months or later',
        'Other'
    ];
    foreach ($pcrTestReasonArray as $key => $reason) {
        $pcrTestReasonList[$key]['value'] = $reason;
        $pcrTestReasonList[$key]['show'] = $reason;
    }
    $data['eid']['pcrTestReason'] = $pcrTestReasonList;
    $data['eid']['specimenTypeList'] = $app->generateSelectOptions($eidService->getEidSampleTypes($updatedDateTime));

    /* Rejected Reason*/
    $data['eid']['rejectedReasonList'] = $rejectionReason['eid'];

    /* Testing Platform Details */
    $testPlatformList = [];
    $testPlatformResult = $general->getTestingPlatforms('eid');
    foreach ($testPlatformResult as $row) {
        $testPlatformList[$row['machine_name']] = $row['machine_name'];
    }
    $data['eid']['testPlatformList'] = $app->generateSelectOptions($testPlatformList);

    $data['eid']['resultsList'] = $app->generateSelectOptions($eidService->getEidResults());

    $data['eid']['statusFilterList'] = [
        ['value' => '7', 'show' => 'Approved'],
        ['value' => '1', 'show' => 'Pending'],
        ['value' => '4', 'show' => 'Rejected']
    ];
    $status = true;
}

// Check if vl module active/inactive
if (isset($applicationConfig['modules']['vl']) && $applicationConfig['modules']['vl'] === true) {
    /** @var VlService $vlService */
    $vlService = ContainerRegistry::get(VlService::class);
    /* SAMPLE INFORMATION SECTION */
    $data['vl']['specimenTypeList'] = $app->generateSelectOptions($vlService->getVlSampleTypes($updatedDateTime));
    /* Current regimen */
    $aQuery = "SELECT art_code FROM r_vl_art_regimen where art_status ='active' ";
    if ($updatedDateTime) {
        $aQuery .= " AND updated_datetime >= '$updatedDateTime'";
    }
    $aResult = $db->query($aQuery);

    $regimenResult = [];
    foreach ($aResult as $subKey => $regimen) {
        $regimenResult[$subKey]['value'] = $regimen['art_code'];
        $regimenResult[$subKey]['show'] = $regimen['art_code'];
    }
    $data['vl']['currentRegimenList'] = $regimenResult;
    /* ARV Adherence */
    $data['vl']['arvAdherence'] = [
        ['value' => 'good', 'show' => 'Good >= 95%'],
        ['value' => 'fair', 'show' => 'Fair (85-94%)'],
        ['value' => 'poor', 'show' => 'Poor < 85%']
    ];

    /* Rejected Reason*/
    $data['vl']['rejectedReasonList'] = $rejectionReason['vl'];

    /* Testing Platform Details */
    $testPlatformList = [];
    $testPlatformResult = $general->getTestingPlatforms('vl');
    foreach ($testPlatformResult as $row) {
        $testPlatformList[$row['machine_name']] = $row['machine_name'];
    }
    $data['vl']['testPlatformList'] = $app->generateSelectOptions($testPlatformList);
    $data['vl']['reasonForFailure'] = $vlService->getReasonForFailure(false, $updatedDateTime);

    $data['vl']['statusFilterList'] = [
        ['value' => '7', 'show' => 'Approved'],
        ['value' => '1', 'show' => 'Pending'],
        ['value' => '4', 'show' => 'Rejected']
    ];
    $data['vl']['vlResults'] = $general->fetchDataFromTable('r_vl_results');
    $status = true;
}

// Check if tb module active/inactive
if (isset($applicationConfig['modules']['tb']) && $applicationConfig['modules']['tb'] === true) {

    /** @var TbService $tbService */
    $tbService = ContainerRegistry::get(TbService::class);
    $data['tb']['rapidTestResult'] = $app->generateSelectOptions($tbService->getTbResults(null, $updatedDateTime));

    $data['tb']['specimenTypeList'] = $app->generateSelectOptions($tbService->getTbSampleTypes($updatedDateTime));

    /* Rejected Reason*/
    $data['tb']['rejectedReasonList'] = $rejectionReason['tb'];

    /* Testing Platform Details */
    $testPlatformList = [];
    $testPlatformResult = $general->getTestingPlatforms('tb');
    foreach ($testPlatformResult as $row) {
        $testPlatformList[$row['machine_name']] = $row['machine_name'];
    }
    $data['tb']['testPlatformList'] = $app->generateSelectOptions($testPlatformList);

    $data['tb']['resultsList'] = $app->generateSelectOptions($tbService->getTbResults(null, $updatedDateTime));

    $data['tb']['statusFilterList'] = [
        ['value' => '7', 'show' => 'Approved'],
        ['value' => '1', 'show' => 'Pending'],
        ['value' => '4', 'show' => 'Rejected']
    ];
    $status = true;
}

// Check if lab tests module active/inactive
if (isset($applicationConfig['modules']['generic-tests']) && $applicationConfig['modules']['generic-tests'] === true) {

    /** @var GenericTestsService $tbService */
    $genericService = ContainerRegistry::get(GenericTestsService::class);
    $data['genericTests']['specimenTypeList'] = $app->generateSelectOptions($genericService->getGenericSampleTypes($updatedDateTime));
    $data['genericTests']['resultsList'] = $app->generateSelectOptions($genericService->getGenericResults(null, $updatedDateTime));
    /* Rejected Reason*/
    $data['genericTests']['rejectedReasonList'] = $rejectionReason['generic-tests'];
    /* Testing Platform Details */
    $testPlatformList = [];
    $testPlatformResult = $general->getTestingPlatforms('generic-tests');
    foreach ($testPlatformResult as $row) {
        $testPlatformList[$row['machine_name']] = $row['machine_name'];
    }
    $data['genericTests']['testPlatformList'] = $app->generateSelectOptions($testPlatformList);
    $data['genericTests']['statusFilterList'] = [
        ['value' => '7', 'show' => 'Approved'],
        ['value' => '1', 'show' => 'Pending'],
        ['value' => '4', 'show' => 'Rejected']
    ];
    $status = true;
}

if ($status) {
    $payload = [
        'status' => 1,
        'message' => 'Success',
        'data' => $data,
        'timestamp' => time(),
    ];
} else {
    $payload = [
        'status' => 'failed',
        'timestamp' => time(),
        'error' => 'Please contact system administrator.',
        'data' => []
    ];
    http_response_code(401);
}
$payload = json_encode($payload);
$trackId = $general->addApiTracking(
    $transactionId,
    $user['user_id'],
    1,
    'init',
    'common',
    $_SERVER['REQUEST_URI'],
    $input,
    $payload,
    'json'
);
echo $payload;
