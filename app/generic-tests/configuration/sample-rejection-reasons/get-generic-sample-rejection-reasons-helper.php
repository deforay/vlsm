<?php


use App\Services\UsersService;
use App\Registries\ContainerRegistry;

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();


$tableName = "r_generic_sample_rejection_reasons";
$primaryKey = "rejection_reason_id";
//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = [];
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */

$aColumns = array('rejection_reason_name', 'rejection_type', 'rejection_reason_code', 'rejection_reason_status');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

$sTable = $tableName;
/*
 * Paging
 */
$sLimit = null;
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

$sQuery = "SELECT * FROM $tableName";

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

$aResultFilterTotal = $db->rawQuery("SELECT * FROM $tableName $sWhere order by $sOrder");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length */
$aResultTotal = $db->rawQuery("select COUNT($primaryKey) as total FROM $tableName");
// $aResultTotal = $countResult->fetch_row();
//print_r($aResultTotal);
$iTotal = $aResultTotal[0]['total'];

/*
 * Output
 */
$output = array(
    "sEcho" => intval($_POST['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => []
);

foreach ($rResult as $aRow) {
    $status = '<select class="form-control" name="status[]" id="' . $aRow['rejection_reason_id'] . '" title="' . _translate("Please select status") . '" onchange="updateStatus(this,\'' . $aRow['rejection_reason_status'] . '\')">
               <option value="active" ' . ($aRow['rejection_reason_status'] == "active" ? "selected=selected" : "") . '>' . _translate("Active") . '</option>
               <option value="inactive" ' . ($aRow['rejection_reason_status'] == "inactive" ? "selected=selected" : "") . '>' . _translate("Inactive") . '</option>
               </select><br><br>';
    $row = [];
    $row[] = ($aRow['rejection_reason_name']);
    $row[] = ucwords((string) $aRow['rejection_type']);
    $row[] = ($aRow['rejection_reason_code']);
    if ($usersService->isAllowed("/generic-tests/configuration/sample-rejection-reasons/generic-edit-sample-rejection-reasons.php") && $sarr['sc_user_type'] != 'vluser') {
        $row[] = $status;
        // $row[] = '<a href="generic-edit-rejection-reasons.php?id=' . base64_encode($aRow['rejection_reason_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _translate("Edit") . '"><em class="fa-solid fa-pen-to-square"></em> ' . _translate("Edit") . '</em></a>';
    } else {
        $row[] = ucwords((string) $aRow['rejection_reason_status']);
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
