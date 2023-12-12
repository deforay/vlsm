<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
     session_start();
}



$formConfigQuery = "SELECT * from global_config where name='vl_form'";
$configResult = $db->query($formConfigQuery);
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
/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);


$tableName = "form_hepatitis";
$primaryKey = "hepatitis_id";
/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'CONCAT(COALESCE(vl.patient_name,""), COALESCE(vl.patient_surname,""))', 'f.facility_name', 'vl.hcv_vl_result', 'vl.hbv_vl_result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'vl.hcv_vl_result', 'vl.hbv_vl_result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
$sampleCode = 'sample_code';
if ($_SESSION['instanceType'] == 'remoteuser') {
     $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
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
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS
               vl.*,
               b.batch_code,
               ts.status_name,
               f.facility_name,
               l.facility_name as labName,
               f.facility_code,
               f.facility_state,
               f.facility_district,
               u_d.user_name as reviewedBy,
               a_u_d.user_name as approvedBy,
               lt_u_d.user_name as labTechnician,
               rs.rejection_reason_name as rejection_reason

               FROM form_hepatitis as vl

               LEFT JOIN r_countries as c ON vl.patient_nationality=c.id
               LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
               LEFT JOIN facility_details as l ON vl.lab_id=l.facility_id
               LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status
               LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
               LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
               LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by
               LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_reception_person
               LEFT JOIN r_hepatitis_test_reasons as rtr ON rtr.test_reason_id=vl.reason_for_hepatitis_test
               LEFT JOIN r_hepatitis_sample_type as rst ON rst.sample_id=vl.specimen_type
               LEFT JOIN r_hepatitis_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
               LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source
               LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";

//echo $sQuery;die;
[$start_date, $end_date] = DateUtility::convertDateRange($_POST['sampleCollectionDate'] ?? '');
/* Sample recieved date filter */
[$rstart_date, $rend_date] = DateUtility::convertDateRange($_POST['sampleRecievedDate'] ?? '');
/* Sample tested date filter */
[$sTestDate, $eTestDate] = DateUtility::convertDateRange($_POST['sampleTestDate'] ?? '');
/* Sample print date filter */
[$sPrintDate, $ePrintDate] = DateUtility::convertDateRange($_POST['printDate'] ?? '');

//$sQuery = $sQuery.' '.$sWhere;
if (isset($_POST['batchCode']) && trim((string) $_POST['batchCode']) != '') {
     $sWhere[] = ' b.batch_code = "' . $_POST['batchCode'] . '"';
}
if (isset($_POST['state']) && trim((string) $_POST['state']) != '') {
     $sWhere[] = " f.facility_state_id = '" . $_POST['state'] . "' ";
}
if (isset($_POST['district']) && trim((string) $_POST['district']) != '') {
     $sWhere[] = " f.facility_district_id = '" . $_POST['district'] . "' ";
}
if (isset($_POST['facilityName']) && trim((string) $_POST['facilityName']) != '') {
     $sWhere[] = ' vl.facility_id = "' . $_POST['facilityName'] . '"';
}

if (isset($_POST['testingLab']) && trim((string) $_POST['testingLab']) != '') {
     $sWhere[] = ' vl.lab_id = "' . $_POST['testingLab'] . '"';
}

if (!empty($_POST['sampleCollectionDate'])) {
     if (trim((string) $start_date) == trim((string) $end_date)) {
          $sWhere[] = ' DATE(vl.sample_collection_date) like  "' . $start_date . '"';
     } else {
          $sWhere[] = ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
     }
}
if (isset($_POST['sampleRecievedDate']) && trim((string) $_POST['sampleRecievedDate']) != '') {
     if (trim((string) $rstart_date) == trim((string) $rend_date)) {
          $sWhere[] = ' DATE(vl.sample_received_at_lab_datetime) = "' . $rstart_date . '"';
     } else {
          $sWhere[] = ' DATE(vl.sample_received_at_lab_datetime) >= "' . $rstart_date . '" AND DATE(vl.sample_received_at_lab_datetime) <= "' . $rend_date . '"';
     }
}
if (isset($_POST['sampleTestDate']) && trim((string) $_POST['sampleTestDate']) != '') {
     if (trim((string) $sTestDate) == trim((string) $eTestDate)) {
          $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $sTestDate . '"';
     } else {
          $sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $sTestDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $eTestDate . '"';
     }
}
if (isset($_POST['printDate']) && trim((string) $_POST['printDate']) != '') {
     if (trim((string) $sPrintDate) == trim((string) $eTestDate)) {
          $sWhere[] = ' DATE(vl.result_printed_datetime) like "' . $sPrintDate . '"';
     } else {
          $sWhere[] = ' DATE(vl.result_printed_datetime) >= "' . $sPrintDate . '" AND DATE(vl.result_printed_datetime) <= "' . $ePrintDate . '"';
     }
}
if (isset($_POST['hcvVLoad']) && trim((string) $_POST['hcvVLoad']) != '') {

     $sWhere[] = ' vl.hcv_vl_result = "' . $_POST['hcvVLoad'] . '"';
}
if (isset($_POST['hbvVLoad']) && trim((string) $_POST['hbvVLoad']) != '') {

     $sWhere[] = ' vl.hbv_vl_result = "' . $_POST['hbvVLoad'] . '"';
}

if (isset($_POST['status']) && trim((string) $_POST['status']) != '') {
     $sWhere[] = ' vl.result_status =' . $_POST['status'];
}
if (isset($_POST['fundingSource']) && trim((string) $_POST['fundingSource']) != '') {
     $sWhere[] = ' vl.funding_source ="' . base64_decode((string) $_POST['fundingSource']) . '"';
}
if (isset($_POST['implementingPartner']) && trim((string) $_POST['implementingPartner']) != '') {
     $sWhere[] = ' vl.implementing_partner ="' . base64_decode((string) $_POST['implementingPartner']) . '"';
}
if (isset($_POST['patientId']) && trim((string) $_POST['patientId']) != '') {
     $sWhere[] = " vl.patient_id LIKE '%" . $_POST['patientId'] . "%' ";
}
if (isset($_POST['patientName']) && $_POST['patientName'] != "") {
     $sWhere[] = " CONCAT(COALESCE(vl.patient_name,''), COALESCE(vl.patient_surname,'')) like '%" . $_POST['patientName'] . "%'";
}
$sWhere[] = ' vl.result_status != ' . SAMPLE_STATUS\RECEIVED_AT_CLINIC;


if (!empty($_SESSION['facilityMap'])) {
     $sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ") ";
}

if (!empty($sWhere)) {
     $sWhere = ' WHERE ' . implode(' AND ', $sWhere);
} else {
     $sWhere = "";
}
$sQuery = $sQuery . ' ' . $sWhere;

if (!empty($sOrder)) {
     $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
     $sQuery = $sQuery . ' order by ' . $sOrder;
}

$_SESSION['hepatitisResultQuery'] = $sQuery;

if (isset($sLimit) && isset($sOffset)) {
     $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}

$rResult = $db->rawQuery($sQuery);

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];

$_SESSION['hepatitisResultQueryCount'] = $iTotal;

$output = array(
     "sEcho" => (int) $_POST['sEcho'],
     "iTotalRecords" => $iTotal,
     "iTotalDisplayRecords" => $iFilteredTotal,
     "aaData" => []
);

foreach ($rResult as $aRow) {

     $patientFname = ($general->crypto('doNothing', $aRow['patient_name'], $aRow['patient_id']));
     $patientLname = ($general->crypto('doNothing', $aRow['patient_surname'], $aRow['patient_id']));

     $row = [];
     $row[] = $aRow['sample_code'];
     if ($_SESSION['instanceType'] != 'standalone') {
          $row[] = $aRow['remote_sample_code'];
     }

     if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] == 'yes') {
          $key = (string) $general->getGlobalConfig('key');
          $aRow['patient_id'] = $general->crypto('decrypt', $aRow['patient_id'], $key);
          $patientFname = $general->crypto('decrypt', $patientFname, $key);
          $patientLname = $general->crypto('decrypt', $patientLname, $key);
     }

     $row[] = $aRow['batch_code'];
     $row[] = $aRow['patient_id'];
     $row[] = $patientFname . " " . $patientLname;
     $row[] = $aRow['facility_name'];
     $row[] = $aRow['hcv_vl_result'];
     $row[] = $aRow['hbv_vl_result'];
     $row[] = $aRow['status_name'];
     $row[] = $aRow['funding_source_name'] ?? null;
     $row[] = $aRow['i_partner_name'] ?? null;
     if ($aRow['is_result_authorised'] == 'yes') {
          $row[] = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _translate("View") . '" onclick="convertSearchResultToPdf(' . $aRow['hepatitis_id'] . ')"><em class="fa-solid fa-file-lines"></em> ' . _translate("Result PDF") . '</a>';
          $row[] = '';
     } else {
          $row[] = '<a href="javascript:void(0);" class="btn btn-default btn-xs disabled" style="margin-right: 2px;" title="' . _translate("View") . '"><em class="fa-solid fa-ban"></em> ' . _translate("Not Authorized") . '</a>';
     }

     $output['aaData'][] = $row;
}

echo json_encode($output);
