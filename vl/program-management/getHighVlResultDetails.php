<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$general = new \Vlsm\Models\General();

$facilitiesDb = new \Vlsm\Models\Facilities();
$facilityMap = $facilitiesDb->getFacilityMap($_SESSION['userId']);

$tableName = "vl_request_form";
$primaryKey = "vl_sample_id";
//config  query
$configQuery = "SELECT * from global_config";
$configResult = $db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}

/* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'f.facility_name', 'vl.patient_first_name', 'vl.patient_art_no', 'vl.patient_mobile_number', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')", 'fd.facility_name', 'vl.result');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'f.facility_name', 'vl.patient_art_no', 'vl.patient_first_name', 'vl.patient_mobile_number', 'vl.sample_collection_date', 'vl.sample_tested_datetime', 'fd.facility_name', 'vl.result');
if ($_SESSION['instanceType'] == 'remoteuser') {
    $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
    $aColumns = array('vl.sample_code', 'f.facility_name', 'vl.patient_first_name', 'vl.patient_art_no', 'vl.patient_mobile_number', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')", 'fd.facility_name', 'vl.result');
    $orderColumns = array('vl.sample_code', 'f.facility_name', 'vl.patient_art_no', 'vl.patient_first_name', 'vl.patient_mobile_number', 'vl.sample_collection_date', 'vl.sample_tested_datetime', 'fd.facility_name', 'vl.result');
}

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

$sWhere = "";
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
    $sWhere = " AND ";
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
$aWhere = '';
$sQuery = "SELECT SQL_CALC_FOUND_ROWS vl.*,f.facility_name, b.batch_code,fd.facility_name as labName 
    FROM vl_request_form as vl 
    INNER JOIN facility_details as f ON vl.facility_id=f.facility_id 
    INNER JOIN facility_details as fd ON fd.facility_id=vl.lab_id  
    LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
    WHERE vl_result_category like 'not suppressed' AND reason_for_vl_testing != 9999 AND vl.lab_id is NOT NULL ";
$start_date = '';
$end_date = '';

if (isset($_POST['hvlBatchCode']) && trim($_POST['hvlBatchCode']) != '') {
    $sWhere = $sWhere . ' AND b.batch_code LIKE "%' . $_POST['hvlBatchCode'] . '%"';
}
if (isset($_POST['hvlContactStatus']) && trim($_POST['hvlContactStatus']) != '') {
    if ($_POST['hvlContactStatus'] != 'all') {
        $sWhere = $sWhere . ' AND contact_complete_status = "' . $_POST['hvlContactStatus'] . '"';
    }
}

if (isset($_POST['hvlSampleTestDate']) && trim($_POST['hvlSampleTestDate']) != '') {
    $s_c_date = explode("to", $_POST['hvlSampleTestDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = $general->dateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = $general->dateFormat(trim($s_c_date[1]));
    }
    if (trim($start_date) == trim($end_date)) {
        $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) = "' . $start_date . '"';
    } else {
        $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) >= "' . $start_date . '" AND DATE(vl.sample_tested_datetime) <= "' . $end_date . '"';
    }
}
if (isset($_POST['hvlSampleType']) && $_POST['hvlSampleType'] != '') {
    $sWhere = $sWhere . ' AND vl.sample_type = "' . $_POST['hvlSampleType'] . '"';
}
if (isset($_POST['hvlFacilityName']) && $_POST['hvlFacilityName'] != '') {
    $sWhere = $sWhere . ' AND f.facility_id IN (' . $_POST['hvlFacilityName'] . ')';
}
if (isset($_POST['hvlGender']) && $_POST['hvlGender'] != '') {
    $sWhere = $sWhere . ' AND vl.patient_gender = "' . $_POST['hvlGender'] . '"';
}
if (isset($_POST['hvlPatientPregnant']) && $_POST['hvlPatientPregnant'] != '') {
    $sWhere = $sWhere . ' AND vl.is_patient_pregnant = "' . $_POST['hvlPatientPregnant'] . '"';
}
if (isset($_POST['hvlPatientBreastfeeding']) && $_POST['hvlPatientBreastfeeding'] != '') {
    $sWhere = $sWhere . ' AND vl.is_patient_breastfeeding = "' . $_POST['hvlPatientBreastfeeding'] . '"';
}

if ($_SESSION['instanceType'] == 'remoteuser') {
    if (!empty($facilityMap)) {
        $sWhere = $sWhere . " AND vl.facility_id IN (" . $facilityMap . ") ";
    }
}

$sQuery = $sQuery . ' ' . $sWhere;
//$sQuery = $sQuery . ' group by vl.vl_sample_id';
if (isset($sOrder) && $sOrder != "") {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}
$_SESSION['highViralResult'] = $sQuery;
if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//echo($sQuery);
$rResult = $db->rawQuery($sQuery);
// print_r($rResult);

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
    if (isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
        $xplodDate = explode(" ", $aRow['sample_collection_date']);
        $aRow['sample_collection_date'] = $general->humanDateFormat($xplodDate[0]);
    } else {
        $aRow['sample_collection_date'] = '';
    }
    if (isset($aRow['sample_tested_datetime']) && trim($aRow['sample_tested_datetime']) != '' && $aRow['sample_tested_datetime'] != '0000-00-00 00:00:00') {
        $xplodDate = explode(" ", $aRow['sample_tested_datetime']);
        $aRow['sample_tested_datetime'] = $general->humanDateFormat($xplodDate[0]);
    } else {
        $aRow['sample_tested_datetime'] = '';
    }
    if ($aRow['remote_sample'] == 'yes') {
        $decrypt = 'remote_sample_code';
    } else {
        $decrypt = 'sample_code';
    }
    $patientFname = $general->crypto('decrypt', $aRow['patient_first_name'], $aRow[$decrypt]);
    $patientMname = $general->crypto('decrypt', $aRow['patient_middle_name'], $aRow[$decrypt]);
    $patientLname = $general->crypto('decrypt', $aRow['patient_last_name'], $aRow[$decrypt]);
    $row = array();
    $row[] = $aRow['sample_code'];
    if ($sarr['sc_user_type'] != 'standalone') {
        $row[] = $aRow['remote_sample_code'];
    }
    $row[] = ucwords($aRow['facility_name']);
    $row[] = $aRow['patient_art_no'];
    $row[] = ucwords($patientFname . " " . $patientMname . " " . $patientLname);
    $row[] = $aRow['patient_mobile_number'];
    $row[] = $aRow['sample_collection_date'];
    $row[] = $aRow['sample_tested_datetime'];
    $row[] = $aRow['labName'];
    $row[] = $aRow['result'];
    $row[] = '<select class="form-control" name="status" id=' . $aRow['vl_sample_id'] . ' title="Please select status" onchange="updateStatus(this.id,this.value)">
                            <option value=""> -- Select -- </option>
                            <option value="yes" ' . ($aRow['contact_complete_status'] == "yes" ? "selected=selected" : "") . '>Yes</option>
                            <option value="no" ' . ($aRow['contact_complete_status'] == "no" ? "selected=selected" : "") . '>No</option>
                        </select>';
    $output['aaData'][] = $row;
}
echo json_encode($output);
