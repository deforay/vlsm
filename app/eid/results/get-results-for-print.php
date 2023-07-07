<?php

use App\Services\EidService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$sarr = $general->getSystemConfig();


/** @var EidService $eidService */
$eidService = ContainerRegistry::get(EidService::class);
$eidResults = $eidService->getEidResults();

$tableName = "form_eid";
$primaryKey = "eid_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.sample_code', 'vl.remote_sample_code', 'vl.child_id', 'vl.child_name', 'vl.mother_id', 'mother_name', 'f.facility_name', 'l_f.facility_name', 'f.facility_state', 'f.facility_district', 'vl.result', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y')", 'ts.status_name');
$orderColumns = array('vl.sample_code', 'vl.sample_code', 'vl.remote_sample_code', 'vl.child_id', 'vl.child_name', 'vl.mother_id', 'mother_name', 'f.facility_name', 'l_f.facility_name', 'f.facility_state', 'f.facility_district', 'vl.result', 'vl.last_modified_datetime', 'ts.status_name');
if ($_SESSION['instanceType'] == 'remoteuser') {
    $sampleCode = 'remote_sample_code';
} else if ($_SESSION['instanceType'] == 'standalone') {
    if (($key = array_search("remote_sample_code", $aColumns)) !== false) {
        unset($aColumns[$key]);
        $aColumns = array_values($aColumns);
        unset($orderColumns[$key]);
        $orderColumns = array_values($orderColumns);
    }
}
// print_r($orderColumns);die;
/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

$sTable = $tableName;
/*
 * Paging
 */
$sLimit = "";
if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
    $sOffset = $_POST['iDisplayStart'];
    $sLimit = $_POST['iDisplayLength'];
}

/*
 * Ordering
 */

$sOrder = "";

if (isset($_POST['iSortCol_0'])) {
    $sOrder = "";
    for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
        if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
            $sOrder .= $orderColumns[intval($_POST['iSortCol_' . $i])] . "
               " . ($_POST['sSortDir_' . $i]) . ", ";
        }
    }
    $sOrder = substr_replace($sOrder, "", -2);
}

/*
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */

$sWhere = [];
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
    $searchArray = explode(" ", $_POST['sSearch']);
    $sWhereSub = "";
    foreach ($searchArray as $search) {
        if ($sWhereSub == "") {
            $sWhereSub .= " (";
        } else {
            $sWhereSub .= " AND (";
        }
        $colSize = count($aColumns);

        for ($i = 0; $i < $colSize; $i++) {
            if ($i < $colSize - 1) {
                $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
            } else {
                $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
            }
        }
        $sWhereSub .= ")";
    }
    $sWhere[] = $sWhereSub;
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
        $sWhere[] = $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
    }
}

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS vl.*,b.*,ts.*,imp.*,
            f.facility_name, f.facility_district, f.facility_state,
            l_f.facility_name as labName,
            l_f.facility_logo as facilityLogo,
            l_f.header_text as headerText,
            f.facility_code,f.facility_state,f.facility_district,
            imp.i_partner_name,
            u_d.user_name as reviewedBy,
            a_u_d.user_name as approvedBy,
            rs.rejection_reason_name
            FROM form_eid as vl
            LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
            LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id
            INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status
            LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
            LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
            LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by
            LEFT JOIN r_eid_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
            LEFT JOIN r_implementation_partners as imp ON imp.i_partner_id=vl.implementing_partner";
$start_date = '';
$end_date = '';
$t_start_date = '';
$t_end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $s_c_date = explode("to", $_POST['sampleCollectionDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = DateUtility::isoDateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = DateUtility::isoDateFormat(trim($s_c_date[1]));
    }
}

if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
    $s_t_date = explode("to", $_POST['sampleTestDate']);
    //print_r($s_t_date);die;
    if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
        $t_start_date = DateUtility::isoDateFormat(trim($s_t_date[0]));
    }
    if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
        $t_end_date = DateUtility::isoDateFormat(trim($s_t_date[1]));
    }
}
if (isset($_POST['district']) && trim($_POST['district']) != '') {
    $sWhere[] = ' f.facility_district_id = "' . $_POST['district'] . '"';
}
if (isset($_POST['state']) && trim($_POST['state']) != '') {
    $sWhere[] = ' f.facility_state_id = "' . $_POST['state'] . '"';
}

if (isset($_POST['childId']) && $_POST['childId'] != "") {
    $sWhere[] = ' vl.child_id like "%' . $_POST['childId'] . '%"';
}
if (isset($_POST['childName']) && $_POST['childName'] != "") {
    $sWhere[] = " CONCAT(COALESCE(vl.child_name,''), COALESCE(vl.child_surname,'')) like '%" . $_POST['childName'] . "%'";
}


if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    if (trim($start_date) == trim($end_date)) {
        $sWhere[] = ' DATE(vl.sample_collection_date) like  "' . $start_date . '"';
    } else {
        $sWhere[] = ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
    }
}

if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
    if (trim($t_start_date) == trim($t_end_date)) {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $t_start_date . '"';
    } else {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $t_start_date . '" AND DATE(vl.sample_tested_datetime) <= "' . $t_end_date . '"';
    }
}
if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
    $sWhere[] = ' s.sample_id = "' . $_POST['sampleType'] . '"';
}
if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
    $sWhere[] = ' f.facility_id IN (' . $_POST['facilityName'] . ')';
}
if (isset($_POST['labId']) && trim($_POST['labId']) != '') {
    $sWhere[] = ' vl.lab_id IN (' . $_POST['labId'] . ')';
}
if (isset($_POST['artNo']) && trim($_POST['artNo']) != '') {
    $sWhere[] = " vl.child_id LIKE '%" . $_POST['artNo'] . "%' ";
}
if (isset($_POST['status']) && trim($_POST['status']) != '') {
    if ($_POST['status'] == 'no_result') {
        $statusCondition = '  (vl.result is NULL OR vl.result ="")';
    } else if ($_POST['status'] == 'result') {
        $statusCondition = ' (vl.result is NOT NULL AND vl.result !="")';
    } else {
        $statusCondition = ' vl.result_status = ' . SAMPLE_STATUS\REJECTED;
    }
    $sWhere[] = $statusCondition;
}
if (isset($_POST['gender']) && trim($_POST['gender']) != '') {
    if (trim($_POST['gender']) == "not_recorded") {
        $sWhere[] = ' (vl.patient_gender = "not_recorded" OR vl.patient_gender ="" OR vl.patient_gender IS NULL)';
    } else {
        $sWhere[] = ' vl.patient_gender ="' . $_POST['gender'] . '"';
    }
}
if (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') {
    $sWhere[] = ' vl.funding_source ="' . base64_decode($_POST['fundingSource']) . '"';
}
if (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') {
    $sWhere[] = ' vl.implementing_partner ="' . base64_decode($_POST['implementingPartner']) . '"';
}

// Only approved results can be printed
if (!isset($_POST['status']) || trim($_POST['status']) == '') {
    if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'print') {
        $sWhere[] = " ((vl.result_status = 7 AND vl.result is NOT NULL AND vl.result !='') OR (vl.result_status = 4 AND (vl.result is NULL OR vl.result = ''))) AND result_printed_datetime is NOT NULL AND result_printed_datetime not like ''";
    } else {
        $sWhere[] = " ((vl.result_status = 7 AND vl.result is NOT NULL AND vl.result !='') OR (vl.result_status = 4 AND (vl.result is NULL OR vl.result = ''))) AND (result_printed_datetime is NULL OR result_printed_datetime like '')";
    }
} else {
    $sWhere[] = " vl.result_status != " . SAMPLE_STATUS\RECEIVED_AT_CLINIC;
}
if ($_SESSION['instanceType'] == 'remoteuser') {
    $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
    if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
        $sWhere[] = " vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")  ";
    }
}
if (!empty($sWhere)) {
    $sQuery = $sQuery . ' WHERE' . implode(" AND ", $sWhere);
}
//echo $_SESSION['vlResultQuery'];die;
if (!empty($sOrder)) {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}
$_SESSION['eidPrintQuery'] = $sQuery;
if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//error_log($sQuery);
// die($sQuery);
$rResult = $db->rawQuery($sQuery);
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];

/*
 * Output
 */
$output = array(
    "sEcho" => intval($_POST['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);

foreach ($rResult as $aRow) {
    $row = [];
    if (isset($_POST['vlPrint'])) {
        if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'not-print') {
            $row[] = '<input type="checkbox" name="chk[]" class="checkRows" id="chk' . $aRow['eid_id'] . '"  value="' . $aRow['eid_id'] . '" onclick="checkedRow(this);"  />';
        } else {
            $row[] = '<input type="checkbox" name="chkPrinted[]" class="checkPrintedRows" id="chkPrinted' . $aRow['eid_id'] . '"  value="' . $aRow['eid_id'] . '" onclick="checkedPrintedRow(this);"  />';
        }
        $print = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _("Print") . '" onclick="resultPDF(' . $aRow['eid_id'] . ')"><em class="fa-solid fa-print"></em> ' . _("Print") . '</a>';
    }

    if ($aRow['remote_sample'] == 'yes') {
        $decrypt = 'remote_sample_code';
    } else {
        $decrypt = 'sample_code';
    }

    // $patientFname = ($general->crypto('doNothing', $aRow['child_name'], $aRow[$decrypt]));

    $row[] = $aRow['sample_code'];
    if ($_SESSION['instanceType'] != 'standalone') {
        $row[] = $aRow['remote_sample_code'];
    }
    // $row[] = $aRow['batch_code'];
    $row[] = $aRow['child_id'];
    $row[] = $aRow['child_name'];
    $row[] = $aRow['mother_id'];
    $row[] = $aRow['mother_name'];
    // $row[] = ($patientFname);
    $row[] = ($aRow['facility_name']);
    $row[] = $aRow['labName'];
    $row[] = ($aRow['facility_state']);
    $row[] = ($aRow['facility_district']);
    $row[] = $eidResults[$aRow['result']] ?? $aRow['result'];

    if (isset($aRow['last_modified_datetime']) && trim($aRow['last_modified_datetime']) != '' && $aRow['last_modified_datetime'] != '0000-00-00 00:00:00') {
        $aRow['last_modified_datetime'] = DateUtility::humanReadableDateFormat($aRow['last_modified_datetime'], true);
    } else {
        $aRow['last_modified_datetime'] = '';
    }

    $row[] = $aRow['last_modified_datetime'];
    $row[] = ($aRow['status_name']);
    $row[] = $print;
    $output['aaData'][] = $row;
}

echo json_encode($output);
