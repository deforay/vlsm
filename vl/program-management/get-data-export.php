<?php
if (session_status() == PHP_SESSION_NONE) {
     session_start();
}


$general = new \Vlsm\Models\General();
$facilitiesDb = new \Vlsm\Models\Facilities();

$facilityMap = $facilitiesDb->getFacilityMap($_SESSION['userId']);

$gconfig = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();

$tableName = "form_vl";
$primaryKey = "vl_sample_id";
/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_art_no', 'vl.patient_first_name', 'f.facility_name', 's.sample_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_art_no', 'vl.patient_first_name', 'f.facility_name', 's.sample_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
$sampleCode = 'sample_code';
if ($_SESSION['instanceType'] == 'remoteuser') {
     $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
     $aColumns = array('vl.sample_code', 'b.batch_code', 'vl.patient_art_no', 'vl.patient_first_name', 'f.facility_name', 's.sample_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
     $orderColumns = array('vl.sample_code', 'b.batch_code', 'vl.patient_art_no', 'vl.patient_first_name', 'f.facility_name', 's.sample_name', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name');
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

$sWhere = " WHERE reason_for_vl_testing != 9999 ";
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
     $sWhere .= " AND " . $sWhereSub;
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS
                        vl.vl_sample_id,
                        vl.sample_code,
                        vl.remote_sample_code,
                        vl.patient_art_no,
                        vl.patient_first_name,
                        vl.patient_middle_name,
                        vl.patient_last_name,
                        vl.patient_dob,
                        vl.patient_gender,
                        vl.patient_age_in_years,
                        vl.sample_collection_date,
                        vl.treatment_initiated_date,
                        vl.date_of_initiation_of_current_regimen,
                        vl.test_requested_on,
                        vl.sample_tested_datetime,
                        vl.arv_adherance_percentage,
                        vl.is_sample_rejected,
                        vl.reason_for_sample_rejection,
                        vl.result_value_log,
                        vl.result_value_absolute,
                        vl.result,
                        vl.current_regimen,
                        vl.is_patient_pregnant,
                        vl.is_patient_breastfeeding,
                        vl.request_clinician_name,
                        vl.approver_comments,
                        vl.sample_received_at_hub_datetime,							
                        vl.sample_received_at_vl_lab_datetime,							
                        vl.result_dispatched_datetime,	
                        vl.result_printed_datetime,	
                        vl.result_status,
                        s.sample_name,
                        b.batch_code,
                        ts.status_name,
                        f.facility_name,
                        testingLab.facility_name as lab_name,
                        f.facility_code,
                        f.facility_state,
                        f.facility_district,
                        u_d.user_name as reviewedBy,
                        a_u_d.user_name as approvedBy,
                        rs.rejection_reason_name,
                        tr.test_reason_name,
                        r_f_s.funding_source_name,
                        r_i_p.i_partner_name 
                        
                        FROM form_vl as vl 
                        
                        INNER JOIN facility_details as f ON vl.facility_id=f.facility_id 
                        INNER JOIN facility_details as testingLab ON vl.lab_id=testingLab.facility_id 
                        LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.sample_type 
                        LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
                        LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
                        LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
                        LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
                        LEFT JOIN r_vl_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
                        INNER JOIN r_vl_test_reasons as tr ON tr.test_reason_id=vl.reason_for_vl_testing 
                        LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source 
                        LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";
//echo $sQuery;die;
/* Sample collection date filter */
$start_date = '';
$end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     $s_c_date = explode("to", $_POST['sampleCollectionDate']);
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
/* Sample type filter */
if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
     $sWhere = $sWhere . ' AND vl.sample_type IN (' . $_POST['sampleType'] . ')';
}
/* Facility id filter */
if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
     $sWhere = $sWhere . ' AND f.facility_id IN (' . $_POST['facilityName'] . ')';
}
/* VL lab id filter */
if (isset($_POST['vlLab']) && trim($_POST['vlLab']) != '') {
     $sWhere = $sWhere . ' AND vl.lab_id IN (' . $_POST['vlLab'] . ')';
}
/* Sample test date filter */
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
/* Viral load filter */
if (isset($_POST['vLoad']) && trim($_POST['vLoad']) != '') {
     if ($_POST['vLoad'] == 'suppressed') {
          $sWhere = $sWhere .  " AND vl.vl_result_category ='suppressed' AND vl.result_status = 7 ";
     } else {
          $sWhere = $sWhere .  " AND vl.vl_result_category ='not suppressed' AND vl.result_status = 7 ";
     }
}
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
/* Gender filter */
if (isset($_POST['gender']) && trim($_POST['gender']) != '') {
     if (trim($_POST['gender']) == "not_recorded") {
          $sWhere = $sWhere . ' AND (vl.patient_gender = "not_recorded" OR vl.patient_gender ="" OR vl.patient_gender IS NULL)';
     } else {
          $sWhere = $sWhere . ' AND vl.patient_gender ="' . $_POST['gender'] . '"';
     }
}
/* Sample status filter */
if (isset($_POST['status']) && trim($_POST['status']) != '') {
     $sWhere = $sWhere . ' AND vl.result_status =' . $_POST['status'];
}
/* Show only recorded sample filter */
if (isset($_POST['showReordSample']) && trim($_POST['showReordSample']) == 'yes') {
     $sWhere = $sWhere . ' AND vl.sample_reordered ="yes"';
}
/* Is patient pregnant filter */
if (isset($_POST['patientPregnant']) && trim($_POST['patientPregnant']) != '') {
     $sWhere = $sWhere . ' AND vl.is_patient_pregnant ="' . $_POST['patientPregnant'] . '"';
}
/* Is patient breast feeding filter */
if (isset($_POST['breastFeeding']) && trim($_POST['breastFeeding']) != '') {
     $sWhere = $sWhere . ' AND vl.is_patient_breastfeeding ="' . $_POST['breastFeeding'] . '"';
}
/* Batch code filter */
if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
     $sWhere = $sWhere . ' AND b.batch_code = "' . $_POST['batchCode'] . '"';
}
/* Funding src filter */
if (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') {
     $sWhere = $sWhere . ' AND vl.funding_source ="' . base64_decode($_POST['fundingSource']) . '"';
}
/* Implemening partner filter */
if (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') {
     $sWhere = $sWhere . ' AND vl.implementing_partner ="' . base64_decode($_POST['implementingPartner']) . '"';
}
/* Assign date time filters */
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     if (trim($start_date) == trim($end_date)) {
          $sWhere = $sWhere . ' AND DATE(vl.sample_collection_date) = "' . $start_date . '"';
     } else {
          $sWhere = $sWhere . ' AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
     }
}
if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '' && $_POST['status'] == 7) {
     if (trim($sTestDate) == trim($eTestDate)) {
          $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) = "' . $sTestDate . '"';
     } else {
          $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) >= "' . $sTestDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $eTestDate . '"';
     }
}
if (isset($_POST['printDate']) && trim($_POST['printDate']) != '') {
     if (trim($sPrintDate) == trim($eTestDate)) {
          $sWhere = $sWhere . ' AND DATE(vl.result_printed_datetime) = "' . $sPrintDate . '"';
     } else {
          $sWhere = $sWhere . ' AND DATE(vl.result_printed_datetime) >= "' . $sPrintDate . '" AND DATE(vl.result_printed_datetime) <= "' . $ePrintDate . '"';
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
     if (!empty($facilityMap)) {
          $sWhere = $sWhere . " AND vl.facility_id IN (" . $facilityMap . ")   ";
          $cWhere = " AND vl.facility_id IN (" . $facilityMap . ")  ";
     }
}
$sQuery = $sQuery . ' ' . $sWhere;


if (isset($sOrder) && $sOrder != "") {
     $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
     $sQuery = $sQuery . ' ORDER BY ' . $sOrder;
}


$_SESSION['vlResultQuery'] = $sQuery;

if (isset($sLimit) && isset($sOffset)) {
     $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
// die($sQuery);
$rResult = $db->rawQuery($sQuery);
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];

// /* Total data set length */
// $aResultTotal =  $db->rawQueryOne("SELECT COUNT(vl_sample_id) as total FROM form_vl as vl where result_status!=9 $cWhere");
// // $aResultTotal = $countResult->fetch_row();
// $iTotal = $aResultTotal['total'];

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

     $patientFname = ucwords($general->crypto('decrypt', $aRow['patient_first_name'], $aRow['patient_art_no']));
     $patientMname = ucwords($general->crypto('decrypt', $aRow['patient_middle_name'], $aRow['patient_art_no']));
     $patientLname = ucwords($general->crypto('decrypt', $aRow['patient_last_name'], $aRow['patient_art_no']));

     $row = array();
     $row[] = $aRow['sample_code'];
     if ($sarr['sc_user_type'] != 'standalone') {
          $row[] = $aRow['remote_sample_code'];
     }
     $row[] = $aRow['batch_code'];
     $row[] = $aRow['patient_art_no'];
     $row[] = ucwords($patientFname . " " . $patientMname . " " . $patientLname);
     $row[] = ucwords($aRow['facility_name']);
     $row[] = ucwords($aRow['lab_name']);
     $row[] = ucwords($aRow['sample_name']);
     $row[] = $aRow['result'];
     $row[] = ucwords($aRow['status_name']);
     $row[] = (isset($aRow['funding_source_name']) && trim($aRow['funding_source_name']) != '') ? ucwords($aRow['funding_source_name']) : '';
     $row[] = (isset($aRow['i_partner_name']) && trim($aRow['i_partner_name']) != '') ? ucwords($aRow['i_partner_name']) : '';
     $row[] = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _("View") . '" onclick="convertSearchResultToPdf(' . $aRow['vl_sample_id'] . ');"><i class="fa fa-file-text"></i> ' . _("Result PDF") . '</a>';

     $output['aaData'][] = $row;
}

echo json_encode($output);