<?php

use App\Services\GenericTestsService;
use App\Services\ApiService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Exceptions\SystemException;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

ini_set('memory_limit', '1G');
set_time_limit(30000);
ini_set('max_execution_time', 20000);



/** @var Slim\Psr7\Request $request */
$request = $GLOBALS['request'];

$origJson = $request->getBody()->getContents();
$input = $request->getParsedBody();


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

/** @var GenericTestsService $genericService */
$genericService = ContainerRegistry::get(GenericTestsService::class);

/** @var ApiService $app */
$app = ContainerRegistry::get(ApiService::class);


$arr = $general->getGlobalConfig();
$user = null;
/* For API Tracking params */
$requestUrl = $_SERVER['HTTP_HOST'];
$requestUrl .= $_SERVER['REQUEST_URI'];
$authToken = $general->getAuthorizationBearerToken();
$user = $usersService->getUserByToken($authToken);
try {
    $transactionId = $general->generateUUID();
    $sQuery = "SELECT * FROM form_generic as vl
        LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
        LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id
        LEFT JOIN geographical_divisions as gdd ON f.facility_district_id=gdd.geo_id
        LEFT JOIN geographical_divisions as gdp ON vl.province_id=gdp.geo_id
        LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status
        LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
        LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
        LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by
        LEFT JOIN user_details as r_r_b ON r_r_b.user_id=vl.revised_by
        LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_technician
        LEFT JOIN user_details as t_b ON t_b.user_id=vl.tested_by
        LEFT JOIN r_generic_sample_types as rst ON rst.sample_id=vl.specimen_type
        LEFT JOIN r_generic_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
        LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source
        LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";


    $where = [];
    if (!empty($user)) {
        $facilityMap = $facilitiesService->getUserFacilityMap($user['user_id'], 1);
        if (!empty($facilityMap)) {
            $where[] = " vl.facility_id IN (" . $facilityMap . ")";
        } else {
            $where[] = " (request_created_by = '" . $user['user_id'] . "')";
        }
    }

    /* To check the uniqueId filter */
    $uniqueId = $input['uniqueId'] ?? [];
    if (!empty($uniqueId)) {
        $uniqueId = implode("','", $uniqueId);
        $where[] = " unique_id IN ('$uniqueId')";
    }

    /* To check the sample code filter */
    $sampleCode = $input['sampleCode'] ?? [];
    if (!empty($sampleCode)) {
        $sampleCode = implode("','", $sampleCode);
        $where[] = " (sample_code IN ('$sampleCode') OR remote_sample_code IN ('$sampleCode') )";
    }
    /* To check the facility and date range filter */
    $from = $input['sampleCollectionDate'][0];
    $to = $input['sampleCollectionDate'][1];
    if (!empty($from) && !empty($to)) {
        $where[] = " DATE(sample_collection_date) between '$from' AND '$to' ";
    }

    if (!empty($input['lastModifiedDateTime'])) {
        $where[] = " DATE(vl.request_created_datetime) >= '" . DateUtility::isoDateFormat($input['lastModifiedDateTime']) . "'";
    }

    $facilityId = $input['facility'] ?? [];
    if (!empty($facilityId)) {
        $facilityId = implode("','", $facilityId);
        $where[] = " vl.facility_id IN ('$facilityId') ";
    }
    if (!empty($input['patientId'])) {
        $patientId = implode("','", $input['patientId']);
        $where[] = " vl.patient_id IN ('" . $patientId . "') ";
    }

    if (!empty($input['patientName'])) {
        $where[] = " (vl.patient_first_name like '" . $input['patientName'] . "' OR vl.patient_last_name like '" . $input['patientName'] . "')";
    }

    $sampleStatus = $input['sampleStatus'];
    if (!empty($sampleStatus)) {
        $sampleStatus = implode("','", $sampleStatus);
        $where[] = " result_status IN ('$sampleStatus') ";
    }
    $whereStr = "";
    if (!empty($where)) {
        $whereStr = " WHERE " . implode(" AND ", $where);
    }
    $sQuery .= $whereStr . " ORDER BY vl.last_modified_datetime DESC limit 100 ";
    $rowData = $db->rawQuery($sQuery);

    if (!empty($rowData)) {
        foreach ($rowData as $key => $row) {
            $rowData[$key]['tbTests'] = $tbService->getTbTestsByFormId($row['tbId']);
        }
    }

    http_response_code(200);
    $payload = [
        'status' => 'success',
        'timestamp' => time(),
        'data' => $rowData ?? []
    ];
} catch (SystemException $exc) {
    $payload = [
        'status' => 'failed',
        'timestamp' => time(),
        'error' => $exc->getMessage(),
        'data' => []
    ];
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
$payload = json_encode($payload);
$general->addApiTracking($transactionId, $user['user_id'], count($rowData), 'fetch-results', 'tb', $_SERVER['REQUEST_URI'], $origJson, $payload, 'json');
echo $payload;