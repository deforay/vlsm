<?php

use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;


// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

try {

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);
    $table = "form_vl";
    $primaryKey = "vl_sample_id";

    $testType = 'vl';

    $sources = array(
        'vlsm' => 'VLSM',
        'vlsts' => 'STS',
        'app' => 'Tablet',
        'api' => 'API',
        'dhis2' => 'DHIS2'
    );
    $sampleReceivedfield = "sample_received_at_lab_datetime";
    if (!empty($_POST['testType'])) {
        $testType = $_POST['testType'];
    }

    if (isset($testType) && $testType == 'vl') {
        $url = "/vl/requests/vl-requests.php";
        $table = "form_vl";
        $testName = 'Viral Load';
    }
    if (isset($testType) && $testType == 'eid') {
        $url = "/eid/requests/eid-requests.php";
        $table = "form_eid";
        $testName = 'EID';
    }
    if (isset($testType) && $testType == 'covid19') {
        $url = "/covid-19/requests/covid-19-requests.php";
        $table = "form_covid19";
        $testName = 'Covid-19';
    }
    if (isset($testType) && $testType == 'hepatitis') {
        $url = "/hepatitis/requests/hepatitis-requests.php";
        $table = "form_hepatitis";
        $testName = 'Hepatitis';
    }
    if (isset($testType) && $testType == 'tb') {
        $url = "/tb/requests/tb-requests.php";
        $table = "form_tb";
        $testName = 'TB';
    }

    $aColumns = ["vl.sample_code", "vl.remote_sample_code", "vl.external_sample_code", ];
    $orderColumns = array('l.facility_name', '', '', '', '', 'vl.source_of_request', 'vl.request_created_datetime');


    $sOffset = $sLimit = null;
    if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
        $sOffset = $_POST['iDisplayStart'];
        $sLimit = $_POST['iDisplayLength'];
    }

    $sOrder = $general->generateDataTablesSorting($_POST, $orderColumns);

    $columnSearch = $general->multipleColumnSearch($_POST['sSearch'], $aColumns);
    $sWhere = [];
    if (!empty($columnSearch) && $columnSearch != '') {
        $sWhere[] = $columnSearch;
    }


    $sQuery = "SELECT l.facility_name as 'labname',
        vl.source_of_request,
        vl.sample_collection_date,
        vl.last_modified_datetime,
        count(*) as 'samples',
        vl.lab_id,
        SUM(CASE WHEN (vl.result is not null AND vl.result not like '' AND result_status = 7) THEN 1 ELSE 0 END) AS 'samplesWithResults',
        SUM(CASE WHEN (vl.is_sample_rejected is not null AND vl.is_sample_rejected like 'yes') THEN 1 ELSE 0 END) AS 'rejected',
        SUM(CASE WHEN (vl.$sampleReceivedfield is not null AND vl.$sampleReceivedfield not like '') THEN 1 ELSE 0 END) AS 'noOfSampleReceivedAtLab',
        SUM(CASE WHEN (vl.result_sent_to_source is not null and vl.result_sent_to_source = 'sent') THEN 1 ELSE 0 END) AS 'noOfResultsReturned',
        MAX(request_created_datetime) AS 'lastRequest'
        FROM $table as vl
        LEFT JOIN facility_details as l ON vl.lab_id = l.facility_id";

    [$start_date, $end_date] = DateUtility::convertDateRange($_POST['dateRange'] ?? '');

    $sWhere[] = " (lab_id is not null AND lab_id not like '' AND lab_id > 0) ";


    if (isset($_POST['dateRange']) && trim((string) $_POST['dateRange']) != '') {
        $sWhere[] = ' DATE(vl.sample_collection_date) BETWEEN "' . $start_date . '" AND "' . $end_date . '"';
    }
    if (isset($_POST['labName']) && trim((string) $_POST['labName']) != '') {
        $sWhere[] = ' vl.lab_id IN (' . $_POST['labName'] . ')';
    }
    if (isset($_POST['srcRequest']) && trim((string) $_POST['srcRequest']) != '') {
        $sWhere[] = ' vl.source_of_request = "' . $_POST['srcRequest'] . '"';
    }

    /* Implode all the where fields for filtering the data */
    if (!empty($sWhere)) {
        $sQuery = $sQuery . ' WHERE ' . implode(" AND ", $sWhere);
    }

    $sQuery = $sQuery . ' GROUP BY source_of_request, lab_id, DATE(vl.sample_collection_date)';
    if (!empty($sOrder) && $sOrder !== '') {
        $sOrder = preg_replace('/\s+/', ' ', $sOrder);
        $sQuery = $sQuery . " ORDER BY " . $sOrder;
    }
    $_SESSION['sourceOfRequestsQuery'] = $sQuery;
    if (isset($sLimit) && isset($sOffset)) {
        $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
    }

    [$rResult, $resultCount] = $db->getDataAndCount($sQuery);


    $output = [
        "sEcho" => (int) $_POST['sEcho'],
        "iTotalRecords" => $resultCount,
        "iTotalDisplayRecords" => $resultCount,
        "aaData" => []
    ];

    foreach ($rResult as $key => $aRow) {
        $params = [$aRow['sample_collection_date'], $aRow['lab_id'], $aRow['source_of_request']];
        if (isset($aRow['samples']) && $aRow['samples'] > 0) {
            $samples = $params;
            $samples[] = SAMPLE_STATUS\RECEIVED_AT_CLINIC;
            $samplesParams = implode("##", $samples);
        }

        if (isset($aRow['noOfSampleReceivedAtLab']) && $aRow['noOfSampleReceivedAtLab'] > 0) {
            $register = $params;
            $register[] = SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB;
            $registerParams = implode("##", $register);
        }

        if (isset($aRow['samplesWithResults']) && $aRow['samplesWithResults'] > 0) {
            $tested = $params;
            $tested[] = SAMPLE_STATUS\ACCEPTED;
            $testedParams = implode("##", $tested);
        }

        if (isset($aRow['rejected']) && $aRow['rejected'] > 0) {
            $rejected = $params;
            $rejected[] = SAMPLE_STATUS\REJECTED;
            $rejectedParams = implode("##", $rejected);
        }

        if (isset($aRow['noOfResultsReturned']) && $aRow['noOfResultsReturned'] > 0) {
            $returned = $params;
            $returned[] = "sent";
            $returnedParams = implode("##", $returned);
        }

        $row = [];
        $row[] = $aRow['labname'];
        $row[] = $testName;
        if (isset($aRow['samples']) && $aRow['samples'] > 0) {
            $row[] = '<a href="javascript:void(0);" class="" style="margin-right: 2px;" title="View History" onclick="showModal(\'' . $url . '?id=' . base64_encode($registerParams) . '\',1200,700);"> ' . $aRow['samples'] . '</a>';
        } else {
            $row[] = $aRow['samples'];
        }
        if (isset($aRow['noOfSampleReceivedAtLab']) && $aRow['noOfSampleReceivedAtLab'] > 0) {
            $row[] = '<a href="javascript:void(0);" class="" style="margin-right: 2px;" title="View History" onclick="showModal(\'' . $url . '?id=' . base64_encode($registerParams) . '\',1200,700);"> ' . $aRow['noOfSampleReceivedAtLab'] . '</a>';
        } else {
            $row[] = $aRow['noOfSampleReceivedAtLab'];
        }
        if (isset($aRow['samplesWithResults']) && $aRow['samplesWithResults'] > 0) {
            $row[] = '<a href="javascript:void(0);" class="" style="margin-right: 2px;" title="View History" onclick="showModal(\'' . $url . '?id=' . base64_encode($testedParams) . '\',1200,700);"> ' . $aRow['samplesWithResults'] . '</a>';
        } else {
            $row[] = $aRow['samplesWithResults'];
        }
        if (isset($aRow['rejected']) && $aRow['rejected'] > 0) {
            $row[] = '<a href="javascript:void(0);" class="" style="margin-right: 2px;" title="View History" onclick="showModal(\'' . $url . '?id=' . base64_encode($rejectedParams) . '\',1200,700);"> ' . $aRow['rejected'] . '</a>';
        } else {
            $row[] = $aRow['rejected'];
        }
        if (isset($aRow['noOfResultsReturned']) && $aRow['noOfResultsReturned'] > 0) {
            $row[] = '<a href="javascript:void(0);" class="" style="margin-right: 2px;" title="View History" onclick="showModal(\'' . $url . '?id=' . base64_encode($returnedParams) . '\',1200,700);"> ' . $aRow['noOfResultsReturned'] . '</a>';
        } else {
            $row[] = $aRow['noOfResultsReturned'];
        }
        $row[] = !empty($sources[$aRow['source_of_request']]) ? $sources[$aRow['source_of_request']] : strtoupper((string) $aRow['source_of_request']);
        $row[] = DateUtility::humanReadableDateFormat($aRow['lastRequest']);

        $output['aaData'][] = $row;
    }
    echo JsonUtility::encodeUtf8Json($output);
} catch (Throwable $e) {
    LoggerUtility::logError($e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'last_db_error' => $db->getLastError(),
        'last_db_query' => $db->getLastQuery()
    ]);
}
