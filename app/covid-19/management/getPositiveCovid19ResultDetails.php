<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$tableName = "form_covid19";
$primaryKey = "covid19_id";
//config  query
$configQuery = "SELECT * from global_config";
$configResult = $db->query($configQuery);
$arr = [];
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = [];
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}

$thresholdLimit = $arr['viral_load_threshold_limit'];
/* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'f.facility_name', 'vl.patient_name', 'vl.patient_id', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')", 'fd.facility_name', 'vl.result');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'f.facility_name', 'vl.patient_id', 'vl.patient_name', 'vl.sample_collection_date', 'vl.sample_tested_datetime', 'fd.facility_name', 'vl.result');
if ($_SESSION['instanceType'] == 'remoteuser') {
    $sampleCode = 'remote_sample_code';
} elseif ($_SESSION['instanceType'] == 'standalone') {
    $aColumns = array_values(array_diff($aColumns, ['vl.remote_sample_code']));
    $orderColumns = array_values(array_diff($orderColumns, ['vl.remote_sample_code']));
}

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

$sTable = $tableName;
/*
         * Paging
         */
$sOffset = $sLimit = null;
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
    $sWhere[] = $sWhereSub;
}



/*
         * SQL queries.
         * Get data to display
        */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS vl.*,f.*,s.*,b.*,fd.facility_name as labName FROM form_covid19 as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as fd ON fd.facility_id=vl.lab_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.specimen_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where vl.result_status=7 AND vl.result > " . $thresholdLimit;

if (isset($_POST['hvlBatchCode']) && trim((string) $_POST['hvlBatchCode']) != '') {
    $sWhere[] =  ' b.batch_code LIKE "%' . $_POST['hvlBatchCode'] . '%"';
}

[$start_date, $end_date] = DateUtility::convertDateRange($_POST['hvlSampleTestDate'] ?? '');
if (isset($_POST['hvlSampleTestDate']) && trim((string) $_POST['hvlSampleTestDate']) != '') {
    if (trim((string) $start_date) == trim((string) $end_date)) {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $start_date . '"';
    } else {
        $sWhere[] =  ' DATE(vl.sample_tested_datetime) >= "' . $start_date . '" AND DATE(vl.sample_tested_datetime) <= "' . $end_date . '"';
    }
}
if (isset($_POST['hvlSampleType']) && $_POST['hvlSampleType'] != '') {
    $sWhere[] = ' s.sample_id = "' . $_POST['hvlSampleType'] . '"';
}
if (isset($_POST['state']) && trim((string) $_POST['state']) != '') {
    $sWhere[] = " f.facility_state_id = '" . $_POST['state'] . "' ";
}
if (isset($_POST['district']) && trim((string) $_POST['district']) != '') {
    $sWhere[] = " f.facility_district_id = '" . $_POST['district'] . "' ";
}
if (isset($_POST['hvlFacilityName']) && $_POST['hvlFacilityName'] != '') {
    $sWhere[] = ' f.facility_id IN (' . $_POST['hvlFacilityName'] . ')';
}
if (isset($_POST['hvlGender']) && $_POST['hvlGender'] != '') {
    $sWhere[] = ' vl.patient_gender = "' . $_POST['hvlGender'] . '"';
}
if (isset($_POST['hvlPatientPregnant']) && $_POST['hvlPatientPregnant'] != '') {
    $sWhere[] = ' vl.is_patient_pregnant = "' . $_POST['hvlPatientPregnant'] . '"';
}
if (isset($_POST['hvlPatientBreastfeeding']) && $_POST['hvlPatientBreastfeeding'] != '') {
    $sWhere[] = ' vl.is_patient_breastfeeding = "' . $_POST['hvlPatientBreastfeeding'] . '"';
}
$dWhere = '';

if (!empty($_SESSION['facilityMap'])) {
    $sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ")";
}


//$sQuery = $sQuery . ' ' . $sWhere;
if (!empty($sWhere)) {
    $sWhere = ' AND ' . implode(' AND ', $sWhere);
    $sQuery = $sQuery . ' ' . $sWhere;
}
$sQuery = $sQuery . ' group by vl.covid19_id';
//echo $sQuery; die();
if (!empty($sOrder)) {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}
$_SESSION['highViralResult'] = $sQuery;
if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
// error_log($sQuery);

//echo $sQuery;
$rResult = $db->rawQuery($sQuery);
// print_r($rResult);
/* Data set length after filtering

$aResultFilterTotal = $db->rawQuery("SELECT vl.*,f.*,s.*,b.*,fd.facility_name as labName FROM form_covid19 as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as fd ON fd.facility_id=vl.lab_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.specimen_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where vl.result_status=7 AND vl.result > $thresholdLimit $sWhere group by vl.covid19_id order by $sOrder");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length
$aResultTotal =  $db->rawQuery("select COUNT(covid19_id) as total FROM form_covid19 as vl where result_status=7 AND result > $thresholdLimit AND vlsm_country_id='" . $arr['vl_form'] . "' $dWhere");
$iTotal = $aResultTotal[0]['total'];*/

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];
$_SESSION['highViralResultCount'] = $iTotal;

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
    if (isset($aRow['sample_collection_date']) && trim((string) $aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
        $aRow['sample_collection_date'] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
    } else {
        $aRow['sample_collection_date'] = '';
    }
    if (isset($aRow['sample_tested_datetime']) && trim((string) $aRow['sample_tested_datetime']) != '' && $aRow['sample_tested_datetime'] != '0000-00-00 00:00:00') {
        $aRow['sample_tested_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime']);
    } else {
        $aRow['sample_tested_datetime'] = '';
    }
    if ($aRow['remote_sample'] == 'yes') {
        $decrypt = 'remote_sample_code';
    } else {
        $decrypt = 'sample_code';
    }
    /*$patientFname = $general->crypto('doNothing', $aRow['patient_first_name'], $aRow[$decrypt]);
    $patientMname = $general->crypto('doNothing', $aRow['patient_middle_name'], $aRow[$decrypt]);
    $patientLname = $general->crypto('doNothing', $aRow['patient_surname'], $aRow[$decrypt]);*/
    if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] == 'yes') {
        $key = (string) $general->getGlobalConfig('key');
        $aRow['patient_id'] = $general->crypto('decrypt', $aRow['patient_id'], $key);
        $aRow['patient_name'] = $general->crypto('decrypt', $aRow['patient_name'], $key);
    }
    $row = [];
    $row[] = $aRow['sample_code'];
    if ($_SESSION['instanceType'] != 'standalone') {
        $row[] = $aRow['remote_sample_code'];
    }
    $row[] = ($aRow['facility_name']);
    $row[] = $aRow['patient_id'];
    $row[] = $aRow['patient_name'];
    $row[] = $aRow['sample_collection_date'];
    $row[] = $aRow['sample_tested_datetime'];
    $row[] = $aRow['labName'];
    $row[] = $aRow['result'];
    $row[] = '';
    /* $row[] = '<select class="form-control" name="status" id=' . $aRow['covid19_id'] . ' title="Please select status" onchange="updateStatus(this.id,this.value)">
                            <option value=""> -- Select -- </option>
                            <option value="yes" ' . ($aRow['contact_complete_status'] == "yes" ? "selected=selected" : "") . '>Yes</option>
                            <option value="no" ' . ($aRow['contact_complete_status'] == "no" ? "selected=selected" : "") . '>No</option>
                        </select>'; */
    $output['aaData'][] = $row;
}
echo json_encode($output);
