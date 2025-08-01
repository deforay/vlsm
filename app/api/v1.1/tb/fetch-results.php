<?php

use App\Services\TbService;
use App\Services\ApiService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

ini_set('memory_limit', -1);
set_time_limit(0);
ini_set('max_execution_time', 20000);



/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

/** @var TbService $tbService */
$tbService = ContainerRegistry::get(TbService::class);

/** @var ApiService $apiService */
$apiService = ContainerRegistry::get(ApiService::class);


/** @var Slim\Psr7\Request $request */
$request = AppRegistry::get('request');

//$origJson = $request->getBody()->getContents();
$origJson = $apiService->getJsonFromRequest($request);
if (JsonUtility::isJSON($origJson) === false) {
    throw new SystemException("Invalid JSON Payload", 400);
}
$input = $request->getParsedBody();

$user = null;
/* For API Tracking params */
$requestUrl = $_SERVER['HTTP_HOST'];
$requestUrl .= $_SERVER['REQUEST_URI'];
$authToken = ApiService::extractBearerToken($request);
$user = $usersService->findUserByApiToken($authToken);
try {
    $transactionId = MiscUtility::generateULID();
    $sQuery = "SELECT
        vl.app_sample_code                      as appSampleCode,
        vl.unique_id                            as uniqueId,
        vl.tb_id                                as tbId,
        vl.sample_code                          as sampleCode,
        vl.sample_reordered                     as sampleReordered,
        vl.referring_unit                       as referringUnit,
        vl.remote_sample_code                   as remoteSampleCode,
        vl.facility_id                          as facilityId,
        f.facility_name                         as facilityName,
        vl.sample_requestor_name                as sampleRequestorName,
        vl.sample_requestor_phone               as sampleRequestorPhone,
        vl.specimen_quality                     as specimenQuality,
        vl.lab_id                               as labId,
        vl.patient_id                           as patientId,
        vl.patient_name                         as firstName,
        vl.patient_surname                      as lastName,
        vl.patient_dob                          as patientDob,
        vl.patient_gender                       as patientGender,
        vl.patient_age                          as patientAge,
        vl.patient_address                      as patientAddress,
        vl.patient_type                         as patientType,
        vl.hiv_status                           as hivStatus,
        vl.reason_for_tb_test                   as reasonFortbTest,
        vl.tests_requested                      as testTypeRequested,
        vl.specimen_type                        as specimenType,
        vl.sample_collection_date               as sampleCollectionDate,
        vl.sample_received_at_lab_datetime      as sampleReceivedDate,
        vl.sample_dispatched_datetime           as sampleDispatchedDate,
        vl.is_sample_rejected                   as isSampleRejected,
        vl.result                               as result,
        vl.rejection_on                         as rejectionDate,
        vl.result_status                        as resultStatus,
        vl.sample_tested_datetime               as sampleTestedDate,
        l_f.facility_name                       as labName,
        vl.result,
        vl.xpert_mtb_result                     as xpertMtbResult,
        vl.is_sample_rejected                   as sampleRejected,
        vl.lab_tech_comments                    as approverComments,
        vl.request_created_datetime             as requestedDate,
        vl.result_printed_datetime              as resultPrintedDate,
        vl.last_modified_datetime               as updatedOn,
        vl.lab_technician                       as labTechnician,
        vl.lab_reception_person                 as labReceptionPerson,
        rs.rejection_reason_name                as rejectionReason,
        vl.rejection_on                         as rejectionDate,
        vl.funding_source                       as fundingSource,
        r_f_s.funding_source_name               as fundingSourceName,
        vl.implementing_partner                 as implementingPartner,
        r_i_p.i_partner_name                    as implementingPartnerName,
        gdp.geo_name                            as province,
        gdp.geo_id                              as provinceId,
        ts.status_name                          as resultStatusName,
        vl.revised_by                           as revisedBy,
        r_r_b.user_name                         as revisedByName,
        vl.revised_on                           as revisedOn,
        vl.tested_by                            as testedBy,
        t_b.user_name                           as testedByName,
        vl.result_approved_by                   as approvedBy,
        a_u_d.user_name                         as approvedByName,
        vl.result_approved_datetime             as approvedOn,
        u_d.user_name                           as reviewedBy,
        u_d.user_name                           as reviewedByName,
        vl.result_reviewed_datetime             as reviewedOn,
        lt_u_d.user_name                        as labTechnicianName,
        vl.reason_for_changing                  as reasonFortbResultChanges

        FROM form_tb as vl
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
        LEFT JOIN r_tb_sample_type as rst ON rst.sample_id=vl.specimen_type
        LEFT JOIN r_tb_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
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

    /* To check the sample id filter */
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
        $where[] = " (vl.patient_name like '" . $input['patientName'] . "' OR vl.patient_surname like '" . $input['patientName'] . "')";
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
        'transactionId' => $transactionId,
        'data' => $rowData ?? []
    ];
} catch (Throwable $exc) {
    http_response_code(500);
    $payload = [
        'status' => 'failed',
        'timestamp' => time(),
        'transactionId' => $transactionId,
        'error' => _translate('Failed to process this request. Please contact the system administrator if the problem persists'),
        'data' => []
    ];
    LoggerUtility::logError($exc->getMessage(), [
        'file' => $exc->getFile(),
        'line' => $exc->getLine(),
        'requestUrl' => $requestUrl,
        'stacktrace' => $exc->getTraceAsString()
    ]);
}
$payload = JsonUtility::encodeUtf8Json($payload);
$general->addApiTracking($transactionId, $user['user_id'], count($rowData ?? []), 'fetch-results', 'tb', $_SERVER['REQUEST_URI'], $origJson, $payload, 'json');

//echo $payload
echo ApiService::generateJsonResponse($payload, $request);
