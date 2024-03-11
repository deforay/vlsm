<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Services\UsersService;
use App\Utilities\MiscUtility;
use App\Utilities\LoggerUtility;


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);
try {

    $db->beginReadOnlyTransaction();

    $tableName = "r_recommended_corrective_actions";
    $primaryKey = "recommended_corrective_action_id";
    $testType = $_POST['testType'];

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    /** @var UsersService $usersService */
    $usersService = ContainerRegistry::get(UsersService::class);

    $sarr = $general->getSystemConfig();


    $aColumns = array('recommended_corrective_action_name', 'status');

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

    $sQuery = "SELECT * FROM $tableName";
    if (isset($testType) && $testType != "") {
        $sWhere = "test_type = '$testType'";
    }
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
        "sEcho" => (int) $_POST['sEcho'],
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => []
    );
    $editRequest = false;
    if (_isAllowed("/common/reference/edit-recommended-corrective-action.php?testType=vl")) {
        $editRequest = true;
    }
    //echo $editRequest; die;
    foreach ($rResult as $aRow) {

        $row = [];
        $row[] = ($aRow['recommended_corrective_action_name']);
        $row[] = ($aRow['status']);

        if ($editRequest) {
            $edit = '<a href="/common/reference/edit-recommended-corrective-action.php?testType=vl&id=' . base64_encode((string) $aRow[$primaryKey]) .  '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _translate("Edit") . '"><em class="fa-solid fa-pen-to-square"></em> ' . _translate("Edit") . '</em></a>';
        }

        $actions = "";
        if ($editRequest) {
            $actions .= $edit;
        }
        $row[] = $actions;
        $output['aaData'][] = $row;
    }

    echo MiscUtility::convertToUtf8AndEncode($output);

    $db->commitTransaction();
} catch (Exception $exc) {
    LoggerUtility::log('error', $exc->getMessage(), ['trace' => $exc->getTraceAsString()]);
}
