<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$primaryKey = "api_track_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$aColumns = array('transaction_id', 'number_of_records', 'request_type', 'test_type', "api_url", "DATE_FORMAT(requested_on,'%d-%b-%Y')");
$orderColumns = array('transaction_id', 'number_of_records', 'request_type', 'test_type', 'api_url', 'requested_on');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

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
$sWhere[] = ' number_of_records > 0 ';
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
     $searchArray = explode(" ", (string) $_POST['sSearch']);
     $sWhereSub = "";
     foreach ($searchArray as $search) {
          $sWhereSub .= " (";
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
$aWhere = '';
$sQuery = '';

$sQuery = "SELECT a.* FROM track_api_requests as a";

[$startDate, $endDate] = DateUtility::convertDateRange($_POST['dateRange'] ?? '');

if (isset($_POST['dateRange']) && trim((string) $_POST['dateRange']) != '') {
     $sWhere[] = ' DATE(a.requested_on) >= "' . $startDate . '" AND DATE(a.requested_on) <= "' . $endDate . '"';
}

if (isset($_POST['syncedType']) && trim((string) $_POST['syncedType']) != '') {
     $sWhere[] = ' a.request_type like "' . $_POST['syncedType'] . '"';
}
if (isset($_POST['testType']) && trim((string) $_POST['testType']) != '') {
     $sWhere[] = ' a.test_type like "' . $_POST['testType'] . '"';
}

/* Implode all the where fields for filtering the data */
if (!empty($sWhere)) {
     $sQuery = $sQuery . ' WHERE ' . implode(" AND ", $sWhere);
}

if (!empty($sOrder)) {
     $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
     $sQuery = $sQuery . " ORDER BY " . $sOrder;
}
$_SESSION['auditLogQuery'] = $sQuery;


$rResult = $db->rawQuery($sQuery);

[$rResult, $resultCount] = $general->getQueryResultAndCount($sQuery, null, $sLimit, $sOffset);

/*
* Output
*/
$output = array(
     "sEcho" => intval($_POST['sEcho']),
     "iTotalRecords" => $resultCount,
     "iTotalDisplayRecords" => $resultCount,
     "aaData" => []
);
foreach ($rResult as $key => $aRow) {
     $row = [];
     $row[] = $aRow['transaction_id'];
     $row[] = $aRow['number_of_records'];
     $row[] = str_replace("-", " ", ((string) $aRow['request_type']));
     $row[] = strtoupper((string) $aRow['test_type']);
     $row[] = $aRow['api_url'];
     $row[] = DateUtility::humanReadableDateFormat($aRow['requested_on'], true);
     $row[] = '<a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result" onclick="showModal(\'show-params.php?id=' . base64_encode((string) $aRow[$primaryKey]) . '\',1200,720);"> Show Params</a>';
     $output['aaData'][] = $row;
}
echo json_encode($output);
