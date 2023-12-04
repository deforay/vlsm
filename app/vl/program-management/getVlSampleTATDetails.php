<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
//$whereCondition = '';
$tableName = "form_vl";
$primaryKey = "vl_sample_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
if ($_SESSION['instanceType'] == 'remoteuser') {
	$sampleCode = 'remote_sample_code';
} else {
	$sampleCode = 'sample_code';
}
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_received_at_lab_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.result_printed_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.result_mail_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.result_printed_on_sts_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.result_printed_on_lis_datetime,'%d-%b-%Y')");
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'vl.sample_collection_date', 'vl.sample_received_at_lab_datetime', 'vl.sample_tested_datetime', 'vl.result_printed_datetime', 'vl.result_mail_datetime', 'vl.result_printed_on_sts_datetime', 'vl.result_printed_on_lis_datetime');

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



$sOrder = "";
if (isset($_POST['iSortCol_0'])) {
	$sOrder = "";
	for ($i = 0; $i < (int) $_POST['iSortingCols']; $i++) {
		if ($_POST['bSortable_' . (int) $_POST['iSortCol_' . $i]] == "true") {
			$sOrder .= $orderColumns[(int) $_POST['iSortCol_' . $i]] . "
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
	$searchArray = explode(" ", (string) $_POST['sSearch']);
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



/*
 * SQL queries
 * Get data to display
 */
$aWhere = '';
$sQuery = "SELECT vl.sample_code,
				vl.sample_collection_date,
				vl.sample_tested_datetime,
				vl.sample_received_at_lab_datetime,
				vl.result_printed_datetime,
				vl.remote_sample_code,
				vl.external_sample_code,
				vl.sample_dispatched_datetime,
				vl.request_created_by,
				vl.result_printed_on_lis_datetime,
				vl.result_printed_on_sts_datetime from form_vl as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where (vl.sample_collection_date > '1970-01-01')
                        AND (vl.sample_tested_datetime > '1970-01-01' )
                        AND vl.result is not null
                        AND vl.result != '' ";
if ($_SESSION['instanceType'] == 'remoteuser') {
	if (!empty($_SESSION['facilityMap'])) {
		$sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ")";
	}
} else {
	$sWhere[] = " vl.result_status != " . SAMPLE_STATUS\RECEIVED_AT_CLINIC;
}
[$start_date, $end_date] = DateUtility::convertDateRange($_POST['sampleCollectionDate'] ?? '');

[$labStartDate, $labEndDate] = DateUtility::convertDateRange($_POST['sampleReceivedDateAtLab'] ?? '');

[$testedStartDate, $testedEndDate] = DateUtility::convertDateRange($_POST['sampleTestedDate'] ?? '');
if (isset($_POST['batchCode']) && trim((string) $_POST['batchCode']) != '') {
	$sWhere[] = ' b.batch_code = "' . $_POST['batchCode'] . '"';
}
if (!empty($_POST['sampleCollectionDate'])) {
	if (trim((string) $start_date) == trim((string) $end_date)) {
		$sWhere[] = ' DATE(vl.sample_collection_date) like  "' . $start_date . '"';
	} else {
		$sWhere[] = ' (DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '")';
	}
}
if (isset($_POST['sampleReceivedDateAtLab']) && trim((string) $_POST['sampleReceivedDateAtLab']) != '') {
	if (trim((string) $labStartDate) == trim((string) $labEndDate)) {
		$sWhere[] = ' DATE(vl.sample_received_at_lab_datetime) = "' . $labStartDate . '"';
	} else {
		$sWhere[] = ' DATE(vl.sample_received_at_lab_datetime) >= "' . $labStartDate . '" AND DATE(vl.sample_received_at_lab_datetime) <= "' . $labEndDate . '"';
	}
}

if (isset($_POST['sampleTestedDate']) && trim((string) $_POST['sampleTestedDate']) != '') {
	if (trim((string) $testedStartDate) == trim((string) $testedEndDate)) {
		$sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $testedStartDate . '"';
	} else {
		$sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $testedStartDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $testedEndDate . '"';
	}
}
if (isset($_POST['sampleType']) && trim((string) $_POST['sampleType']) != '') {
	$sWhere[] = ' s.sample_id = "' . $_POST['sampleType'] . '"';
}
if (isset($_POST['facilityName']) && trim((string) $_POST['facilityName']) != '') {
	$sWhere[] = ' f.facility_id IN (' . $_POST['facilityName'] . ')';
}
if (!empty($sWhere)) {
	$_SESSION['vlTatData']['sWhere'] = $sWhere = ' AND ' . implode(" AND ", $sWhere);
	$sQuery = $sQuery . $sWhere;
}
if (!empty($sOrder)) {
	$_SESSION['vlTatData']['sOrder'] = $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
	$sQuery = $sQuery . " ORDER BY " . $sOrder;
}


[$rResult, $resultCount]  = $general->getQueryResultAndCount($sQuery, null, $sLimit, $sOffset, true);

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
	if (isset($aRow['sample_collection_date']) && trim((string) $aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
		$aRow['sample_collection_date'] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
	} else {
		$aRow['sample_collection_date'] = '';
	}
	if (isset($aRow['sample_received_at_lab_datetime']) && trim((string) $aRow['sample_received_at_lab_datetime']) != '' && $aRow['sample_received_at_lab_datetime'] != '0000-00-00 00:00:00') {
		$aRow['sample_received_at_lab_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_received_at_lab_datetime']);
	} else {
		$aRow['sample_received_at_lab_datetime'] = '';
	}
	if (isset($aRow['sample_tested_datetime']) && trim((string) $aRow['sample_tested_datetime']) != '' && $aRow['sample_tested_datetime'] != '0000-00-00 00:00:00') {
		$aRow['sample_tested_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime']);
	} else {
		$aRow['sample_tested_datetime'] = '';
	}
	if (isset($aRow['result_printed_datetime']) && trim((string) $aRow['result_printed_datetime']) != '' && $aRow['result_printed_datetime'] != '0000-00-00 00:00:00') {
		$aRow['result_printed_datetime'] = DateUtility::humanReadableDateFormat($aRow['result_printed_datetime']);
	} else {
		$aRow['result_printed_datetime'] = '';
	}
	if (isset($aRow['sample_dispatched_datetime']) && trim((string) $aRow['sample_dispatched_datetime']) != '' && $aRow['sample_dispatched_datetime'] != '0000-00-00 00:00:00') {
		$aRow['sample_dispatched_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_dispatched_datetime']);
	} else {
		$aRow['sample_dispatched_datetime'] = '';
	}
	if (isset($aRow['result_printed_on_sts_datetime']) && trim((string) $aRow['result_printed_on_sts_datetime']) != '' && $aRow['result_printed_on_sts_datetime'] != '0000-00-00 00:00:00') {
		$aRow['result_printed_on_sts_datetime'] = DateUtility::humanReadableDateFormat($aRow['result_printed_on_sts_datetime']);
	} else {
		$aRow['result_printed_on_sts_datetime'] = '';
	}
	if (isset($aRow['result_printed_on_lis_datetime']) && trim((string) $aRow['result_printed_on_lis_datetime']) != '' && $aRow['result_printed_on_lis_datetime'] != '0000-00-00 00:00:00') {
		$aRow['result_printed_on_lis_datetime'] = DateUtility::humanReadableDateFormat($aRow['result_printed_on_lis_datetime']);
	} else {
		$aRow['result_printed_on_lis_datetime'] = '';
	}
	$row = [];
	$row[] = $aRow['sample_code'];
	$row[] = $aRow['remote_sample_code'];
	$row[] = $aRow['external_sample_code'];
	$row[] = $aRow['sample_collection_date'];
	$row[] = $aRow['sample_dispatched_datetime'];
	$row[] = $aRow['sample_received_at_lab_datetime'];
	$row[] = $aRow['sample_tested_datetime'];
	$row[] = $aRow['result_printed_datetime'];
	$row[] = $aRow['result_printed_on_sts_datetime'];
	$row[] = $aRow['result_printed_on_lis_datetime'];


	$output['aaData'][] = $row;
}

echo json_encode($output);
