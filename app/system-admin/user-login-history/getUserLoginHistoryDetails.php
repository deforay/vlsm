<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$tableName = "user_login_history";
$primaryKey = "history_id";


$aColumns = array('login_id', 'login_attempted_datetime', 'ip_address', 'browser', 'operating_system', 'login_status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

$sTable = $tableName;

$sOffset = $sLimit = null;
if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
    $sOffset = $_POST['iDisplayStart'];
    $sLimit = $_POST['iDisplayLength'];
}



$sOrder = "";
if (isset($_POST['iSortCol_0'])) {
    $sOrder = "";
    for ($i = 0; $i < (int) $_POST['iSortingCols']; $i++) {
        if ($_POST['bSortable_' . (int) $_POST['iSortCol_' . $i]] == "true") {
            $sOrder .= $aColumns[(int) $_POST['iSortCol_' . $i]] . "
                             " . ($_POST['sSortDir_' . $i]) . ", ";
        }
    }
    $sOrder = substr_replace($sOrder, "", -2);
}


$sWhere = "";
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
    $searchArray = explode(" ", (string) $_POST['sSearch']);
    $sWhereSub = "";
    foreach ($searchArray as $search) {
        if ($sWhereSub == "") {
            $sWhereSub .= "(";
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
    $sWhere .= $sWhereSub;
}


/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS ul.history_id,ul.login_id,ul.login_attempted_datetime,ul.login_status,ul.ip_address,ul.browser,ul.operating_system FROM user_login_history as ul";

$start_date = '';
$end_date = '';
$cWhere = [];

if (isset($_POST['userDate']) && trim((string) $_POST['userDate']) != '') {
    $s_c_date = explode("to", (string) $_POST['userDate']);
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = DateUtility::isoDateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = DateUtility::isoDateFormat(trim($s_c_date[1]));
    }
}

if (isset($_POST['userDate']) && trim((string) $_POST['userDate']) != '') {
    if (trim((string) $start_date) == trim((string) $end_date)) {
        $cWhere[] = ' DATE(ul.login_attempted_datetime) = "' . $start_date . '"';
    } else {
        $cWhere[] = ' DATE(ul.login_attempted_datetime) >= "' . $start_date . '" AND DATE(ul.login_attempted_datetime) <= "' . $end_date . '"';
    }
}
// print_r($cWhere);die;

if (isset($_POST['loginId']) && trim((string) $_POST['loginId']) != '') {
    $cWhere[] = ' ul.login_id = "' . $_POST['loginId'] . '"';
}

if ((isset($sWhere) && $sWhere != "") || (count($cWhere) > 0)) {
    $sWhere = ' WHERE ' . $sWhere;
    $sQuery = $sQuery . ' ' . $sWhere . implode(" AND ", $cWhere);
}


if (!empty($sOrder) && $sOrder !== '') {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' ORDER BY ' . $sOrder;
}

if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
// die($sQuery);
// echo $sQuery;
$rResult = $db->rawQuery($sQuery);
// print_r($rResult);
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];


/*
 * Output
 */
$output = array(
    "sEcho" => (int) $_POST['sEcho'],
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => []
);

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = ($aRow['login_id']);
    $row[] = ($aRow['login_attempted_datetime']);
    $row[] = ($aRow['ip_address']);
    $row[] = ($aRow['browser']);
    $row[] = ($aRow['operating_system']);
    $row[] = ($aRow['login_status']);
    $output['aaData'][] = $row;
}

echo json_encode($output);
