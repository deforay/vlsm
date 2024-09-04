<?php

use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;


// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);
try {

    $db->beginReadOnlyTransaction();

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    $key = (string) $general->getGlobalConfig('key');

    /** @var FacilitiesService $facilitiesService */
    $facilitiesService = ContainerRegistry::get(FacilitiesService::class);


    $tableName = "form_vl";
    $primaryKey = "vl_sample_id";
    
    $aColumns = array('vl.patient_art_no', 'vl.patient_first_name', 'vl.patient_age_in_years', 'vl.patient_dob', 'f.facility_name', 'vl.request_clinician_name', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 's.sample_name', 'fd.facility_name', "DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')", 'vl.result');
    $orderColumns = array('vl.patient_art_no', 'vl.patient_first_name', 'vl.patient_age_in_years', 'vl.patient_dob', 'f.facility_name', 'vl.request_clinician_name', 'vl.sample_collection_date', 's.sample_name', 'fd.facility_name', 'vl.sample_tested_datetime', 'vl.result');

    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = $primaryKey;

    $sTable = $tableName;

    $sOffset = $sLimit = null;
    if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
        $sOffset = $_POST['iDisplayStart'];
        $sLimit = $_POST['iDisplayLength'];
    }

    $sWhere = [];

    $sOrder = $general->generateDataTablesSorting($_POST, $orderColumns);

    $columnSearch = $general->multipleColumnSearch($_POST['sSearch'], $aColumns);

    $sWhere = [];
    if (!empty($columnSearch) && $columnSearch != '') {
        $sWhere[] = $columnSearch;
    }


    /*
         * SQL queries
         * Get data to display
        */
    $sQuery = "SELECT  
                vl.vl_sample_id,
                vl.patient_art_no,
                vl.is_encrypted,
                vl.patient_first_name,
                vl.patient_middle_name,
                vl.patient_last_name,
                vl.patient_age_in_years,
                vl.patient_dob,
                vl.request_clinician_name,
                vl.sample_collection_date,
                vl.sample_tested_datetime,
                vl.result,
                f.facility_name,
                s.sample_name,
                fd.facility_name as labName,
                ts.status_name
            FROM form_vl as vl
            LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
            LEFT JOIN facility_details as fd ON fd.facility_id=vl.lab_id
            LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.specimen_type
            INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status ";
    
    $sWhere[] = ' vl.result is not null AND vl.result not like "" AND result_status = ' . SAMPLE_STATUS\ACCEPTED;

    if (isset($_POST['patientId']) && $_POST['patientId'] != "") {
        $sWhere[] = ' vl.patient_art_no like "%' . $_POST['patientId'] . '%"';
    }
    if (isset($_POST['patientName']) && $_POST['patientName'] != "") {
            $sWhere[] = " CONCAT(COALESCE(vl.patient_first_name,''), COALESCE(vl.patient_middle_name,''),COALESCE(vl.patient_last_name,'')) like '%" . $_POST['patientName'] . "%'";
    }

    if ($general->isSTSInstance() && !empty($_SESSION['facilityMap'])) {
        $sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ") ";
    }

    if (!empty($sWhere)) {
        $sQuery = $sQuery . ' WHERE ' . implode(" AND ", $sWhere);
    }

    //$sQuery = $sQuery . ' GROUP BY vl.vl_sample_id';
    //echo $sQuery; die;
    if (!empty($sOrder) && $sOrder !== '') {
        $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
        $sQuery = $sQuery . ' ORDER BY ' . $sOrder;
    }
    $_SESSION['patientTestHistoryResult'] = $sQuery;

    if (isset($sLimit) && isset($sOffset)) {
        $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
    }

    [$rResult, $resultCount] = $db->getQueryResultAndCount($sQuery);


    $_SESSION['patientTestHistoryResultCount'] = $resultCount;

    /*
         * Output
        */
    $output = array(
        "sEcho" => (int) $_POST['sEcho'],
        "iTotalRecords" => $resultCount,
        "iTotalDisplayRecords" => $resultCount,
        "aaData" => []
    );

    foreach ($rResult as $aRow) {

        $aRow['patient_dob'] = DateUtility::humanReadableDateFormat($aRow['patient_dob'] ?? '');
        $aRow['sample_collection_date'] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
        $aRow['sample_tested_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime'] ?? '');
        $print = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _translate("Print") . '" onclick="convertResultToPdf(' . $aRow['vl_sample_id'] . ')"><em class="fa-solid fa-print"></em> ' . _translate("Print") . '</a>';
        $patientFname = $aRow['patient_first_name'] ?? '';
        $patientMname = $aRow['patient_middle_name'] ?? '';
        $patientLname = $aRow['patient_last_name'] ?? '';

        $row = [];
        if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] == 'yes') {
            $aRow['patient_art_no'] = $general->crypto('decrypt', $aRow['patient_art_no'], $key);
            $patientFname = $general->crypto('decrypt', $patientFname, $key);
            $patientMname = $general->crypto('decrypt', $patientMname, $key);
            $patientLname = $general->crypto('decrypt', $patientLname, $key);
        }
        $row[] = $aRow['patient_art_no'];
        $row[] = ($patientFname . " " . $patientMname . " " . $patientLname);
        $row[] = $aRow['patient_age_in_years'];
        $row[] = $aRow['patient_dob'];
        $row[] = ($aRow['facility_name']);
        $row[] = ($aRow['request_clinician_name']);
        $row[] = $aRow['sample_collection_date'];
        $row[] = $aRow['sample_name'];
        $row[] = $aRow['labName'];
        $row[] = $aRow['sample_tested_datetime'];
        $row[] = $aRow['result'];
        $row[] = $print;
        $output['aaData'][] = $row;
    }
    echo JsonUtility::encodeUtf8Json($output);

    $db->commitTransaction();
} catch (Exception $exc) {
    LoggerUtility::log('error', $exc->getMessage(), ['trace' => $exc->getTraceAsString()]);
}