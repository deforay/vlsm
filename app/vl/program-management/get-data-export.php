<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\MiscUtility;
use App\Utilities\LoggerUtility;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

try {
     $db->beginReadOnlyTransaction();

     /** @var CommonService $general */
     $general = ContainerRegistry::get(CommonService::class);

     /** @var FacilitiesService $facilitiesService */
     $facilitiesService = ContainerRegistry::get(FacilitiesService::class);

     $gconfig = $general->getGlobalConfig();
     $sarr = $general->getSystemConfig();
     $key = (string) $general->getGlobalConfig('key');


     $tableName = "form_vl";
     $primaryKey = "vl_sample_id";

     $aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_art_no', 'vl.patient_first_name', 'f.facility_name', 'testingLab.facility_name', 'vl.sample_collection_date', 's.sample_name', 'vl.sample_tested_datetime', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name', 'vl.request_created_datetime', 'vl.last_modified_datetime');
     $orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_art_no', 'vl.patient_first_name', 'f.facility_name', 'testingLab.facility_name', 'vl.sample_collection_date', 's.sample_name', 'vl.sample_tested_datetime', 'vl.result', 'ts.status_name', 'funding_source_name', 'i_partner_name', 'vl.request_created_datetime', 'vl.last_modified_datetime');
     $sampleCode = 'sample_code';
     if ($general->isSTSInstance()) {
          $sampleCode = 'remote_sample_code';
     } elseif ($general->isStandaloneInstance()) {
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


     $sOrder = $general->generateDataTablesSorting($_POST, $orderColumns);

     $columnSearch = $general->multipleColumnSearch($_POST['sSearch'], $aColumns);

     $sWhere = [];
     if (!empty($columnSearch) && $columnSearch != '') {
          $sWhere[] = $columnSearch;
     }

     $sWhere[] = " (IFNULL(reason_for_vl_testing, 0)  != 9999 or reason_for_vl_testing is null) ";

     $sQuery = "SELECT vl.vl_sample_id,
               vl.sample_code,
               vl.remote_sample_code,
               vl.patient_art_no,
               vl.patient_first_name,
               vl.patient_middle_name,
               vl.patient_last_name,
               vl.patient_dob,
               vl.patient_gender,
               vl.key_population,
               vl.health_insurance_code,
               vl.patient_mobile_number,
               vl.patient_age_in_years,
               vl.sample_collection_date,
               vl.sample_dispatched_datetime,
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
               vl.request_clinician_phone_number,
               vl.lab_tech_comments,
               vl.sample_received_at_hub_datetime,
               vl.sample_received_at_lab_datetime,
               vl.result_dispatched_datetime,
               vl.request_created_datetime,
               vl.result_printed_datetime,
               vl.last_modified_datetime,
               vl.result_status,
               vl.is_encrypted,
               vl.cv_number,
               vl.line_of_treatment,
               vl.vl_test_platform,
               vl.form_attributes,
               vl.vl_result_category,
               vl.result_value_hiv_detection,
               s.sample_name as sample_name,
               b.batch_code,
               ts.status_name,
               f.facility_name,
               testingLab.facility_name as lab_name,
               f.facility_code,
               f.facility_state,
               f.facility_district,
               u_d.user_name as reviewedBy,
               a_u_d.user_name as approvedBy,
               c_u_d.user_name as createdBy,
               rs.rejection_reason_name,
               r_c_a.recommended_corrective_action_name,
               tr.test_reason_name,
               r_f_s.funding_source_name as funding_source_name,
               r_i_p.i_partner_name,
               ins.lower_limit,ins.higher_limit,
               rs.rejection_reason_name as rejection_reason

               FROM form_vl as vl

               LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
               LEFT JOIN facility_details as testingLab ON vl.lab_id=testingLab.facility_id
               LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.specimen_type
               LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status
               LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
               LEFT JOIN user_details as c_u_d ON c_u_d.user_id=vl.request_created_by
               LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
               LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by
               LEFT JOIN r_vl_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
               LEFT JOIN r_vl_test_reasons as tr ON tr.test_reason_id=vl.reason_for_vl_testing
               LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source
               LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner
               LEFT JOIN instruments as ins ON ins.machine_name=vl.vl_test_platform
               LEFT JOIN r_recommended_corrective_actions as r_c_a ON r_c_a.recommended_corrective_action_id=vl.recommended_corrective_action";

     /* Sample collection date filter */
     [$start_date, $end_date] = DateUtility::convertDateRange($_POST['sampleCollectionDate'] ?? '');
     /* Sample recevied date filter */
     [$sSampleReceivedDate, $eSampleReceivedDate] = DateUtility::convertDateRange($_POST['sampleReceivedDate'] ?? '');
     /* Sample type filter */
     if (isset($_POST['sampleType']) && trim((string) $_POST['sampleType']) != '') {
          $sWhere[] =  ' vl.specimen_type IN (' . $_POST['sampleType'] . ')';
     }
     if (isset($_POST['state']) && trim((string) $_POST['state']) != '') {
          $sWhere[] = " f.facility_state_id = '" . $_POST['state'] . "' ";
     }
     if (isset($_POST['district']) && trim((string) $_POST['district']) != '') {
          $sWhere[] = " f.facility_district_id = '" . $_POST['district'] . "' ";
     }
     /* Facility id filter */
     if (isset($_POST['facilityName']) && trim((string) $_POST['facilityName']) != '') {
          $sWhere[] =  ' f.facility_id IN (' . $_POST['facilityName'] . ')';
     }
     /* VL lab id filter */
     if (isset($_POST['vlLab']) && trim((string) $_POST['vlLab']) != '') {
          $sWhere[] =  '  vl.lab_id IN (' . $_POST['vlLab'] . ')';
     }

     [$sTestDate, $eTestDate] = DateUtility::convertDateRange($_POST['sampleTestDate'] ?? '');


     /* Viral load filter */
     if (isset($_POST['vLoad']) && trim((string) $_POST['vLoad']) != '') {
          if ($_POST['vLoad'] === 'suppressed') {
               $sWhere[] =   " vl.vl_result_category like 'suppressed' AND vl.vl_result_category is NOT NULL ";
          } else {
               $sWhere[] =   "  vl.vl_result_category like 'not suppressed' AND vl.vl_result_category is NOT NULL ";
          }
     }

     /* Gender filter */
     if (isset($_POST['gender']) && trim((string) $_POST['gender']) != '') {
          if (trim((string) $_POST['gender']) == "unreported") {
               $sWhere[] =  ' (vl.patient_gender = "unreported" OR vl.patient_gender ="" OR vl.patient_gender IS NULL)';
          } else {
               $sWhere[] =  ' (vl.patient_gender IS NOT NULL AND vl.patient_gender ="' . $_POST['gender'] . '") ';
          }
     }

     if (isset($_POST['communitySample']) && trim((string) $_POST['communitySample']) != '') {
          $sWhere[] =  ' (vl.community_sample IS NOT NULL AND vl.community_sample ="' . $_POST['communitySample'] . '") ';
     }
     /* Sample status filter */
     if (isset($_POST['status']) && trim((string) $_POST['status']) != '') {
          $sWhere[] = '  (vl.result_status IS NOT NULL AND vl.result_status =' . $_POST['status'] . ')';
     }
     /* Show only recorded sample filter */
     if (isset($_POST['showReordSample']) && trim((string) $_POST['showReordSample']) == 'yes') {
          $sWhere[] =  '  (vl.sample_reordered is NOT NULL AND vl.sample_reordered ="yes") ';
     }
     /* Is patient pregnant filter */
     if (isset($_POST['patientPregnant']) && trim((string) $_POST['patientPregnant']) != '') {
          $sWhere[] = '  vl.is_patient_pregnant ="' . $_POST['patientPregnant'] . '"';
     }
     /* Is patient breast feeding filter */
     if (isset($_POST['breastFeeding']) && trim((string) $_POST['breastFeeding']) != '') {
          $sWhere[] = '  vl.is_patient_breastfeeding ="' . $_POST['breastFeeding'] . '"';
     }
     /* Batch code filter */
     if (isset($_POST['batchCode']) && trim((string) $_POST['batchCode']) != '') {
          $sWhere[] =  '  b.batch_code = "' . $_POST['batchCode'] . '"';
     }
     /* Funding src filter */
     if (isset($_POST['fundingSource']) && trim((string) $_POST['fundingSource']) != '') {
          $sWhere[] = '  vl.funding_source ="' . base64_decode((string) $_POST['fundingSource']) . '"';
     }
     /* Implemening partner filter */
     if (isset($_POST['implementingPartner']) && trim((string) $_POST['implementingPartner']) != '') {
          $sWhere[] =  '  vl.implementing_partner ="' . base64_decode((string) $_POST['implementingPartner']) . '"';
     }
     if (isset($_POST['patientId']) && $_POST['patientId'] != "") {
          $sWhere[] = ' vl.patient_art_no like "%' . $_POST['patientId'] . '%"';
     }
     if (isset($_POST['patientName']) && $_POST['patientName'] != "") {
          $sWhere[] = " CONCAT(COALESCE(vl.patient_first_name,''), COALESCE(vl.patient_middle_name,''),COALESCE(vl.patient_last_name,'')) like '%" . $_POST['patientName'] . "%'";
     }
     /* Assign date time filters */
     if (!empty($_POST['sampleCollectionDate'])) {
          if (trim((string) $start_date) == trim((string) $end_date)) {
               $sWhere[] = "  DATE(vl.sample_collection_date) = '$start_date' ";
          } else {
               $sWhere[] =  " DATE(vl.sample_collection_date) BETWEEN '$start_date' AND '$end_date' ";
          }
     }
     if (isset($_POST['sampleTestDate']) && trim((string) $_POST['sampleTestDate']) != '' && $_POST['status'] == 7) {
          if (trim((string) $sTestDate) == trim((string) $eTestDate)) {
               $sWhere[] = "  DATE(vl.sample_tested_datetime) = '$sTestDate' ";
          } else {
               $sWhere[] =  " DATE(vl.sample_tested_datetime) BETWEEN '$sTestDate' AND '$eTestDate' ";
          }
     }
     if (isset($_POST['printDate']) && trim((string) $_POST['printDate']) != '') {
          [$sPrintDate, $ePrintDate] = DateUtility::convertDateRange($_POST['printDate'] ?? '');
          if (trim((string) $sPrintDate) == trim((string) $eTestDate)) {
               $sWhere[] =  '  DATE(vl.result_printed_datetime) = "' . $sPrintDate . '"';
          } else {
               $sWhere[] =  '  DATE(vl.result_printed_datetime) >= "' . $sPrintDate . '" AND DATE(vl.result_printed_datetime) <= "' . $ePrintDate . '"';
          }
     }
     if (isset($_POST['sampleReceivedDate']) && trim((string) $_POST['sampleReceivedDate']) != '') {
          if (trim((string) $sSampleReceivedDate) == trim((string) $eSampleReceivedDate)) {
               $sWhere[] =  '  DATE(vl.sample_received_at_lab_datetime) like "' . $sSampleReceivedDate . '"';
          } else {
               $sWhere[] =  '  DATE(vl.sample_received_at_lab_datetime) >= "' . $sSampleReceivedDate . '" AND DATE(vl.sample_received_at_lab_datetime) <= "' . $eSampleReceivedDate . '"';
          }
     }
     if (isset($_POST['requestCreatedDatetime']) && trim((string) $_POST['requestCreatedDatetime']) != '') {
          $sRequestCreatedDatetime = '';
          $eRequestCreatedDatetime = '';

          $date = explode("to", (string) $_POST['requestCreatedDatetime']);
          if (isset($date[0]) && trim($date[0]) != "") {
               $sRequestCreatedDatetime = DateUtility::isoDateFormat(trim($date[0]));
          }
          if (isset($date[1]) && trim($date[1]) != "") {
               $eRequestCreatedDatetime = DateUtility::isoDateFormat(trim($date[1]));
          }

          if (trim((string) $sRequestCreatedDatetime) == trim((string) $eRequestCreatedDatetime)) {
               $sWhere[] =  '  DATE(vl.request_created_datetime) like "' . $sRequestCreatedDatetime . '"';
          } else {
               $sWhere[] =  '  DATE(vl.request_created_datetime) >= "' . $sRequestCreatedDatetime . '" AND DATE(vl.request_created_datetime) <= "' . $eRequestCreatedDatetime . '"';
          }
     }


     if (!empty($_SESSION['facilityMap'])) {
          $sWhere[] =  "  vl.facility_id IN (" . $_SESSION['facilityMap'] . ")   ";
     }
     if (!empty($sWhere)) {
          $sWhere = implode(" AND ", $sWhere);
     }

     $sQuery = $sQuery . ' WHERE ' . $sWhere;

     if (!empty($sOrder)) {
          $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
          $sQuery = $sQuery . ' ORDER BY ' . $sOrder;
     }

     $_SESSION['vlResultQuery'] = $sQuery;

     if (isset($sLimit) && isset($sOffset)) {
          $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
     }

     [$rResult, $resultCount] = $db->getQueryResultAndCount($sQuery);

     $_SESSION['vlResultQueryCount'] = $resultCount;

     $output = array(
          "sEcho" => (int) $_POST['sEcho'],
          "iTotalRecords" => $resultCount,
          "iTotalDisplayRecords" => $resultCount,
          "aaData" => []
     );

     foreach ($rResult as $aRow) {

          $patientFname = $aRow['patient_first_name'];
          $patientMname = $aRow['patient_middle_name'];
          $patientLname = $aRow['patient_last_name'];

          $row = [];
          $row[] = $aRow['sample_code'];
          if ($_SESSION['instance']['type'] != 'standalone') {
               $row[] = $aRow['remote_sample_code'];
          }
          if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] == 'yes') {
               $aRow['patient_art_no'] = $general->crypto('decrypt', $aRow['patient_art_no'], $key);
               $patientFname = $general->crypto('decrypt', $patientFname, $key);
               $patientMname = $general->crypto('decrypt', $patientMname, $key);
               $patientLname = $general->crypto('decrypt', $patientLname, $key);
          }
          $row[] = $aRow['batch_code'];
          $row[] = $aRow['patient_art_no'];
          $row[] = ($patientFname . " " . $patientMname . " " . $patientLname);
          $row[] = ($aRow['facility_name']);
          $row[] = ($aRow['lab_name']);
          $row[] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
          $row[] = ($aRow['sample_name']);
          $row[] = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime'] ?? '');
          $row[] = $aRow['result'];
          $row[] = $aRow['status_name'];
          $row[] = $aRow['funding_source_name'] ?? null;
          $row[] = $aRow['i_partner_name'] ?? null;
          $row[] = DateUtility::humanReadableDateFormat($aRow['request_created_datetime'], true);
          $row[] = DateUtility::humanReadableDateFormat($aRow['last_modified_datetime'], true);
          //$row[] = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _translate("View") . '" onclick="convertSearchResultToPdf(' . $aRow['vl_sample_id'] . ');"><em class="fa-solid fa-file-lines"></em> ' . _translate("Result PDF") . '</a>';

          $output['aaData'][] = $row;
     }

     echo MiscUtility::convertToUtf8AndEncode($output);

     $db->commitTransaction();
} catch (Exception $exc) {
     LoggerUtility::log('error', $exc->getMessage(), ['trace' => $exc->getTraceAsString()]);
}
