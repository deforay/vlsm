<?php

use App\Utilities\DateUtility;

use App\Services\UsersService;
use App\Registries\ContainerRegistry;

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

$tableName = "r_generic_symptoms";
$primaryKey = "symptom_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */

$aColumns = array('symptom_name', 'symptom_code', 'symptom_status', 'updated_datetime');

/* Indexed column (used for fast and accurate table cardinality) */
//$sIndexColumn = $primaryKey;

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
            $sOrder .= $aColumns[intval($_POST['iSortCol_' . $i])] . "
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

$sWhere = "";
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
    $searchArray = explode(" ", $_POST['sSearch']);
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

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere .= $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
        } else {
            $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
        }
    }
}

/*
 * SQL queries
 * Get data to display
 */

$sQuery = "SELECT * FROM r_generic_symptoms";

if (!empty($sWhere)) {
    $sWhere = ' WHERE ' . $sWhere;
    $sQuery = $sQuery . ' ' . $sWhere;
}

if (!empty($sOrder)) {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}

if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//die($sQuery);
// echo $sQuery;
$rResult = $db->rawQuery($sQuery);
// print_r($rResult);
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQuery("SELECT * FROM r_generic_symptoms $sWhere order by $sOrder");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length */
$aResultTotal = $db->rawQuery("SELECT * FROM r_generic_symptoms");
// $aResultTotal = $countResult->fetch_row();
//print_r($aResultTotal);
$iTotal = count($aResultTotal);
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
    //$expDateTime=explode(" ",$aRow['updated_datetime']);
    $row[] = ($aRow['symptom_name']);
    $row[] = ($aRow['symptom_code']);
    $row[] = ucwords($aRow['symptom_status']);
    $row[] = $aRow['updated_datetime'] = DateUtility::humanReadableDateFormat($aRow['updated_datetime'], true);
    if ($usersService->isAllowed("/generic-tests/configuration/symptoms/generic-edit-symptoms.php")) {
        $row[] = '<a href="generic-edit-symptoms.php?id=' . base64_encode($aRow['symptom_id']) . '" class="btn btn-default btn-xs" style="margin-right: 2px;" title="' . _("Edit") . '"><em class="fa-solid fa-pen-to-square"></em> ' . _("Edit") . '</em></a>';
    }
    $output['aaData'][] = $row;
}
echo json_encode($output);
