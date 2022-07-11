<?php
if (session_status() == PHP_SESSION_NONE) {
     session_start();
}



$formConfigQuery = "SELECT * from global_config where name='vl_form'";
$configResult = $db->query($formConfigQuery);
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
$general = new \Vlsm\Models\General();
$tbResults = $general->getTbResults();

$tableName = "form_tb";
$primaryKey = "tb_id";
/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'CONCAT(COALESCE(vl.patient_name,""), COALESCE(vl.patient_surname,""))', 'f.facility_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
$sampleCode = 'sample_code';
if ($_SESSION['instanceType'] == 'remoteuser') {
     $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
     $aColumns = array('vl.sample_code', 'b.batch_code', 'vl.patient_id', 'CONCAT(COALESCE(vl.patient_name,""), COALESCE(vl.patient_surname,""))', 'f.facility_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
     $orderColumns = array('vl.sample_code', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
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
$aWhere = '';
$sQuery = "SELECT SQL_CALC_FOUND_ROWS 
vl.tb_id,
vl.sample_code,
vl.remote_sample_code,
vl.patient_id,
vl.patient_name,
vl.patient_surname,
vl.patient_dob,
vl.patient_gender,
vl.patient_age,
vl.sample_collection_date,
vl.sample_tested_datetime,
vl.sample_received_at_lab_datetime,
vl.is_sample_rejected,
vl.result,
vl.lab_tech_comments,
vl.request_created_datetime,
vl.result_printed_datetime,
rtr.test_reason_name,
b.batch_code,
ts.status_name,
rst.sample_name,
f.facility_name,
l_f.facility_name as lab_name,
f.facility_code,
f.facility_state,
f.facility_district,
u_d.user_name as reviewedBy,
a_u_d.user_name as approvedBy,
lt_u_d.user_name as labTechnician,
rs.rejection_reason_name,
r_f_s.funding_source_name,
r_i_p.i_partner_name 

FROM form_tb as vl 
LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_technician 
LEFT JOIN r_tb_test_reasons as rtr ON rtr.test_reason_id=vl.reason_for_tb_test 
LEFT JOIN r_tb_sample_type as rst ON rst.sample_id=vl.specimen_type 
LEFT JOIN r_tb_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source 
LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";
/* Sample collection date filter */
$start_date = '';
$end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     $s_c_date = explode("to", $_POST['sampleCollectionDate']);
     //print_r($s_c_date);die;
     if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
          $start_date = $general->dateFormat(trim($s_c_date[0]));
     }
     if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
          $end_date = $general->dateFormat(trim($s_c_date[1]));
     }
}

/* Sample recevied date filter */
$sSampleReceivedDate = '';
$eSampleReceivedDate = '';
if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != '') {
     $s_p_date = explode("to", $_POST['sampleReceivedDate']);
     if (isset($s_p_date[0]) && trim($s_p_date[0]) != "") {
          $sSampleReceivedDate = $general->dateFormat(trim($s_p_date[0]));
     }
     if (isset($s_p_date[1]) && trim($s_p_date[1]) != "") {
          $eSampleReceivedDate = $general->dateFormat(trim($s_p_date[1]));
     }
}
/* Sample tested date filter */
$sTestDate = '';
$eTestDate = '';
if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
     $s_t_date = explode("to", $_POST['sampleTestDate']);
     if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
          $sTestDate = $general->dateFormat(trim($s_t_date[0]));
     }
     if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
          $eTestDate = $general->dateFormat(trim($s_t_date[1]));
     }
}
/* Sample print date filter */
$sPrintDate = '';
$ePrintDate = '';
if (isset($_POST['printDate']) && trim($_POST['printDate']) != '') {
     $s_p_date = explode("to", $_POST['printDate']);
     if (isset($s_p_date[0]) && trim($s_p_date[0]) != "") {
          $sPrintDate = $general->dateFormat(trim($s_p_date[0]));
     }
     if (isset($s_p_date[1]) && trim($s_p_date[1]) != "") {
          $ePrintDate = $general->dateFormat(trim($s_p_date[1]));
     }
}
/* Sample type filter */
if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
     $sWhere = $sWhere . ' AND vl.specimen_type IN (' . $_POST['sampleType'] . ')';
}
/* Facility ID filter */
if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
     $sWhere[] = ' vl.facility_id = "' . $_POST['facilityName'] . '"';
}
/* Testing lab filter */
if (isset($_POST['testingLab']) && trim($_POST['testingLab']) != '') {
     $sWhere[] = ' vl.lab_id = "' . $_POST['testingLab'] . '"';
}
/* Result filter */
if (isset($_POST['result']) && trim($_POST['result']) != '') {
     $sWhere[] = ' vl.result like "' . $_POST['result'] . '"';
}
/* Status filter */
if (isset($_POST['status']) && trim($_POST['status']) != '') {
     $sWhere[] = ' vl.result_status =' . $_POST['status'];
}
/* Funding src filter */
if (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') {
     $sWhere[] = ' vl.funding_source ="' . base64_decode($_POST['fundingSource']) . '"';
}
/* Implementing partner filter */
if (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') {
     $sWhere[] = ' vl.implementing_partner ="' . base64_decode($_POST['implementingPartner']) . '"';
}
/* Batch code filter */
if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
     $sWhere[] = ' b.batch_code = "' . $_POST['batchCode'] . '"';
}

/* Date time filtering */
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     if (trim($start_date) == trim($end_date)) {
          $sWhere[] = ' DATE(vl.sample_collection_date) = "' . $start_date . '"';
     } else {
          $sWhere[] = ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
     }
}
if (isset($_POST['sampleRecievedDate']) && trim($_POST['sampleRecievedDate']) != '') {
     if (trim($rstart_date) == trim($rend_date)) {
          $sWhere[] = ' DATE(vl.sample_registered_at_lab) = "' . $rstart_date . '"';
     } else {
          $sWhere[] = ' DATE(vl.sample_registered_at_lab) >= "' . $rstart_date . '" AND DATE(vl.sample_registered_at_lab) <= "' . $rend_date . '"';
     }
}
if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
     if (trim($sTestDate) == trim($eTestDate)) {
          $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $sTestDate . '"';
     } else {
          $sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $sTestDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $eTestDate . '"';
     }
}
if (isset($_POST['printDate']) && trim($_POST['printDate']) != '') {
     if (trim($sPrintDate) == trim($eTestDate)) {
          $sWhere[] = ' DATE(vl.result_printed_datetime) = "' . $sPrintDate . '"';
     } else {
          $sWhere[] = ' DATE(vl.result_printed_datetime) >= "' . $sPrintDate . '" AND DATE(vl.result_printed_datetime) <= "' . $ePrintDate . '"';
     }
}
if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != '') {
     if (trim($sSampleReceivedDate) == trim($eSampleReceivedDate)) {
          $sWhere = $sWhere . ' AND DATE(vl.sample_received_at_vl_lab_datetime) = "' . $sSampleReceivedDate . '"';
     } else {
          $sWhere = $sWhere . ' AND DATE(vl.sample_received_at_vl_lab_datetime) >= "' . $sSampleReceivedDate . '" AND DATE(vl.sample_received_at_vl_lab_datetime) <= "' . $eSampleReceivedDate . '"';
     }
}

$cWhere = '';
if ($_SESSION['instanceType'] == 'remoteuser') {
     $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM user_facility_map where user_id='" . $_SESSION['userId'] . "'";
     $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
     if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
          $sWhere[] = " vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")   ";
          $cWhere = " AND vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")  ";
     }
}
$sQuery = $sQuery . ' WHERE result_status is NOT NULL AND' . implode(" AND ", $sWhere);

if (isset($sOrder) && $sOrder != "") {
     $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
     $sQuery = $sQuery . ' order by ' . $sOrder;
}

$_SESSION['tbResultQuery'] = $sQuery;

if (isset($sLimit) && isset($sOffset)) {
     $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
// die($sQuery);
$rResult = $db->rawQuery($sQuery);
/* Data set length after filtering */

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

     $patientFname = ucwords($general->crypto('decrypt', $aRow['patient_name'], $aRow['patient_id']));
     $patientLname = ucwords($general->crypto('decrypt', $aRow['patient_surname'], $aRow['patient_id']));

     $row = array();
     $row[] = $aRow['sample_code'];
     if ($sarr['sc_user_type'] != 'standalone') {
          $row[] = $aRow['remote_sample_code'];
     }
     $row[] = $aRow['batch_code'];
     $row[] = $aRow['patient_id'];
     $row[] = ucwords($patientFname . " " . $patientLname);
     $row[] = ucwords($aRow['facility_name']);
     $row[] = ucwords($aRow['lab_name']);
     $row[] = $tbResults[$aRow['result']];
     $row[] = ucwords($aRow['status_name']);
     $row[] = (isset($aRow['funding_source_name']) && trim($aRow['funding_source_name']) != '') ? ucwords($aRow['funding_source_name']) : '';
     $row[] = (isset($aRow['i_partner_name']) && trim($aRow['i_partner_name']) != '') ? ucwords($aRow['i_partner_name']) : '';
     if ($aRow['is_result_authorised'] == 'yes') {
          $row[] = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _("View") . '" onclick="convertSearchResultToPdf(' . $aRow['tb_id'] . ');"><i class="fa-solid fa-file-lines"></i> ' . _("Result PDF") . '</a>';
     } else {
          $row[] = '<a href="javascript:void(0);" class="btn btn-default btn-xs disabled" style="margin-right: 2px;" title="' . _("View") . '"><i class="fa-solid fa-ban"></i> ' . _("Not Authorized") . '</a>';
     }

     $output['aaData'][] = $row;
}

echo json_encode($output);