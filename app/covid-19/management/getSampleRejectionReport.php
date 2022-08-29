<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
  


$general = new \Vlsm\Models\General();
$tableName = "form_covid19";
$primaryKey = "covid19_id";
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
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'f.facility_name', 'vl.patient_id', 'vl.patient_name', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 'fd.facility_name', 'rsrr.rejection_reason_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'f.facility_name', 'vl.patient_id', 'vl.patient_name', 'vl.sample_collection_date', 'fd.facility_name', 'rsrr.rejection_reason_name');

if ($sarr['sc_user_type'] == 'standalone') {
    $aColumns = array('vl.sample_code', 'f.facility_name', 'vl.patient_id', 'vl.patient_name', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 'fd.facility_name', 'rsrr.rejection_reason_name');
    $orderColumns = array('vl.sample_code', 'f.facility_name', 'vl.patient_id', 'vl.patient_name', 'vl.sample_collection_date', 'fd.facility_name', 'rsrr.rejection_reason_name');
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

$sWhere = array();
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS vl.*,f.*,s.*,fd.facility_name as labName,rsrr.rejection_reason_name FROM form_covid19 as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as fd ON fd.facility_id=vl.lab_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.specimen_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id JOIN r_vl_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection where vl.is_sample_rejected='yes' AND vl.vlsm_country_id='" . $arr['vl_form'] . "'";
$start_date = '';
$end_date = '';
if (isset($_POST['rjtBatchCode']) && trim($_POST['rjtBatchCode']) != '') {
    $sWhere[] = ' b.batch_code LIKE "%' . $_POST['rjtBatchCode'] . '%"';
}

if (isset($_POST['rjtSampleTestDate']) && trim($_POST['rjtSampleTestDate']) != '') {
    $s_c_date = explode("to", $_POST['rjtSampleTestDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = $general->isoDateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = $general->isoDateFormat(trim($s_c_date[1]));
    }
    if (trim($start_date) == trim($end_date)) {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $start_date . '"';
    } else {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $start_date . '" AND DATE(vl.sample_tested_datetime) <= "' . $end_date . '"';
    }
}
if (isset($_POST['rjtSampleType']) && $_POST['rjtSampleType'] != '') {
    $sWhere[] = ' s.sample_id = "' . $_POST['rjtSampleType'] . '"';
}
if (isset($_POST['rjtFacilityName']) && $_POST['rjtFacilityName'] != '') {
    $sWhere[] = ' f.facility_id IN (' . $_POST['rjtFacilityName'] . ')';
}
if (isset($_POST['rjtGender']) && $_POST['rjtGender'] != '') {
    $sWhere[] =  ' vl.patient_gender = "' . $_POST['rjtGender'] . '"';
}
if (isset($_POST['rjtPatientPregnant']) && $_POST['rjtPatientPregnant'] != '') {
    $sWhere[] = ' vl.is_patient_pregnant = "' . $_POST['rjtPatientPregnant'] . '"';
}
if (isset($_POST['rjtPatientBreastfeeding']) && $_POST['rjtPatientBreastfeeding'] != '') {
    $sWhere[] = ' vl.is_patient_breastfeeding = "' . $_POST['rjtPatientBreastfeeding'] . '"';
}

//$dWhere = '';
if ($_SESSION['instanceType'] == 'remoteuser') {
    //$sWhere = $sWhere." AND request_created_by='".$_SESSION['userId']."'";
    //$dWhere = $dWhere." AND request_created_by='".$_SESSION['userId']."'";
    $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
    if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
        $sWhere[] = " vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")  ";
       // $dWhere = $dWhere . " AND vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ") ";
    }
}
if(isset($sWhere) && count($sWhere)>0)
{
    $sWhere = ' AND '.implode(' AND ',$sWhere);
}
else
{
    $sWhere = "";
}
//echo $sWhere;
$sQuery = $sQuery .' ' . $sWhere;
//echo $sQuery;
$sQuery = $sQuery . ' group by vl.covid19_id';
if (isset($sOrder) && $sOrder != "") {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}
$_SESSION['rejectedViralLoadResult'] = $sQuery;
if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}

// echo $sQuery;die;
$rResult = $db->rawQuery($sQuery);
// print_r($rResult);
/* Data set length after filtering 

$aResultFilterTotal = $db->rawQuery("SELECT vl.*,f.*,s.*,fd.facility_name as labName FROM form_covid19 as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as fd ON fd.facility_id=vl.lab_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.specimen_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id JOIN r_vl_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection where vl.is_sample_rejected='yes' AND vlsm_country_id='" . $arr['vl_form'] . "' $sWhere group by vl.covid19_id order by $sOrder");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length 
$aResultTotal =  $db->rawQuery("select COUNT(covid19_id) as total FROM form_covid19 as vl JOIN r_vl_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection where is_sample_rejected='yes' AND vlsm_country_id='" . $arr['vl_form'] . "' $dWhere");
$iTotal = $aResultTotal[0]['total'];*/

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
        $aRow['sample_collection_date'] = $general->humanReadableDateFormat($xplodDate[0]);
    } else {
        $aRow['sample_collection_date'] = '';
    }
    if ($aRow['remote_sample'] == 'yes') {
        $decrypt = 'remote_sample_code';
    } else {
        $decrypt = 'sample_code';
    }
    /*$patientFname = $general->crypto('decrypt', $aRow['patient_name'], $aRow[$decrypt]);
    $patientMname = $general->crypto('decrypt', $aRow['patient_middle_name'], $aRow[$decrypt]);
    $patientLname = $general->crypto('decrypt', $aRow['patient_last_name'], $aRow[$decrypt]);*/
    $row = array();
    $row[] = $aRow['sample_code'];
    if ($_SESSION['instanceType'] != 'standalone') {
        $row[] = $aRow['remote_sample_code'];
    }
    $row[] = ($aRow['facility_name']);
    $row[] = $aRow['patient_id'];
    $row[] = $aRow['patient_name'];
    $row[] = $aRow['sample_collection_date'];
    $row[] = $aRow['labName'];
    $row[] = $aRow['rejection_reason_name'];
    $output['aaData'][] = $row;
}
echo json_encode($output);
