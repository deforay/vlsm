<?php

use App\Services\DatabaseService;
use App\Services\EidService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Utilities\LoggerUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;



/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

try {

     $db->beginReadOnlyTransaction();

     /** @var CommonService $general */
     $general = ContainerRegistry::get(CommonService::class);


     /** @var EidService $eidService */
     $eidService = ContainerRegistry::get(EidService::class);
     $eidResults = $eidService->getEidResults();

     $barCodeEnabled = $general->getGlobalConfig('bar_code_printing');
     $key = (string) $general->getGlobalConfig('key');


     $tableName = "form_eid";
     $primaryKey = "eid_id";

     /* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
     $sampleCode = 'sample_code';

     $aColumns = array('vl.sample_code', 'vl.remote_sample_code', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 'b.batch_code', 'l_f.facility_name', 'f.facility_name', 'vl.child_id', 'vl.child_name', 'vl.mother_id', 'vl.mother_name', 'f.facility_state', 'f.facility_district', 'vl.result', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y %H:%i:%s')", 'ts.status_name');
     $orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'vl.sample_collection_date', 'b.batch_code', 'labName', 'f.facility_name', 'vl.child_id', 'vl.child_name', 'vl.mother_id', 'vl.mother_name', 'f.facility_state', 'f.facility_district', 'vl.result', 'vl.last_modified_datetime', 'ts.status_name');

     if ($_SESSION['instanceType'] == 'remoteuser') {
          $sampleCode = 'remote_sample_code';
     } else if ($_SESSION['instanceType'] == 'standalone') {
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

     $sQuery = "SELECT vl.*, f.*,
     b.batch_code,
     ts.status_name,
     f.facility_name,
     l_f.facility_name as labName,
     f.facility_code,
     f.facility_state,
     f.facility_district,
     u_d.user_name as reviewedBy,
     a_u_d.user_name as approvedBy,
     rs.rejection_reason_name,
     r_f_s.funding_source_name,
     r_i_p.i_partner_name

     FROM form_eid as vl

     LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
     LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id
     LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status
     LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
     LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
     LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by
     LEFT JOIN r_eid_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection
     LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source
     LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";

     //echo $sQuery;die;
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
               $sWhere[] = ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
          }
     }

     if (isset($_POST['sampleReceivedDateAtLab']) && trim((string) $_POST['sampleReceivedDateAtLab']) != '') {
          if (trim((string) $labStartDate) == trim((string) $labEndDate)) {
               $sWhere[] = ' DATE(vl.sample_received_at_lab_datetime) = "' . $labStartDate . '"';
          } else {
               $sWhere[] = ' DATE(vl.sample_received_at_lab_datetime) >= "' . $labStartDate . '" AND DATE(vl.sample_received_at_lab_datetime) <= "' . $labEnddate . '"';
          }
     }

     if (isset($_POST['sampleTestedDate']) && trim((string) $_POST['sampleTestedDate']) != '') {
          if (trim((string) $testedStartDate) == trim((string) $testedEndDate)) {
               $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $testedStartDate . '"';
          } else {
               $sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $testedStartDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $testedEndDate . '"';
          }
     }
     if (isset($_POST['facilityName']) && trim((string) $_POST['facilityName']) != '') {
          $sWhere[] = ' f.facility_id IN (' . $_POST['facilityName'] . ')';
     }
     if (isset($_POST['district']) && trim((string) $_POST['district']) != '') {
          $sWhere[] = " f.facility_district_id = '" . $_POST['district'] . "' ";
     }
     if (isset($_POST['state']) && trim((string) $_POST['state']) != '') {
          $sWhere[] = " f.facility_state_id = '" . $_POST['state'] . "' ";
     }
     if (isset($_POST['vlLab']) && trim((string) $_POST['vlLab']) != '') {
          $sWhere[] = ' vl.lab_id IN (' . $_POST['vlLab'] . ')';
     }
     if (isset($_POST['gender']) && trim((string) $_POST['gender']) != '') {
          if (trim((string) $_POST['gender']) == "not_recorded") {
               $sWhere[] = ' vl.child_gender="not_recorded" OR vl.child_gender="" OR vl.child_gender IS NULL';
          } else {
               $sWhere[] = ' vl.child_gender IN ("' . $_POST['gender'] . '")';
          }
     }
     /* Sample status filter */
     if (isset($_POST['status']) && trim((string) $_POST['status']) != '') {
          $sWhere[] = '  (vl.result_status IS NOT NULL AND vl.result_status =' . $_POST['status'] . ')';
     }
     if (isset($_POST['showReordSample']) && trim((string) $_POST['showReordSample']) != '') {
          $sWhere[] = ' vl.sample_reordered IN ("' . $_POST['showReordSample'] . '")';
     }
     if (isset($_POST['fundingSource']) && trim((string) $_POST['fundingSource']) != '') {
          $sWhere[] = ' vl.funding_source IN ("' . base64_decode((string) $_POST['fundingSource']) . '")';
     }
     if (isset($_POST['implementingPartner']) && trim((string) $_POST['implementingPartner']) != '') {
          $sWhere[] = ' vl.implementing_partner IN ("' . base64_decode((string) $_POST['implementingPartner']) . '")';
     }

     if (isset($_POST['srcOfReq']) && trim((string) $_POST['srcOfReq']) != '') {
          $sWhere[] = ' vl.source_of_request like "' . $_POST['srcOfReq'] . '"';
     }

     if (isset($_POST['reqSampleType']) && trim((string) $_POST['reqSampleType']) == 'result') {
          $sWhere[] = ' vl.result != "" ';
     } else if (isset($_POST['reqSampleType']) && trim((string) $_POST['reqSampleType']) == 'noresult') {
          $sWhere[] = ' (vl.result IS NULL OR vl.result = "") ';
     }
     /* Source of request show model conditions */
     if (isset($_POST['dateRangeModel']) && trim((string) $_POST['dateRangeModel']) != '') {
          $sWhere[] = ' DATE(vl.sample_collection_date) like "' . DateUtility::isoDateFormat($_POST['dateRangeModel']) . '"';
     }
     if (isset($_POST['srcOfReqModel']) && trim((string) $_POST['srcOfReqModel']) != '') {
          $sWhere[] = ' vl.source_of_request like "' . $_POST['srcOfReqModel'] . '" ';
     }
     if (isset($_POST['labIdModel']) && trim((string) $_POST['labIdModel']) != '') {
          $sWhere[] = ' vl.lab_id like "' . $_POST['labIdModel'] . '" ';
     }
     if (isset($_POST['srcStatus']) && $_POST['srcStatus'] == 4) {
          $sWhere[] = ' vl.is_sample_rejected is not null AND vl.is_sample_rejected like "yes"';
     }
     if (isset($_POST['srcStatus']) && $_POST['srcStatus'] == 6) {
          $sWhere[] = " vl.sample_received_at_lab_datetime is NOT NULL AND DATE(vl.sample_received_at_lab_datetime) > '0000-00-00')";
     }
     if (isset($_POST['srcStatus']) && $_POST['srcStatus'] == 7) {
          $sWhere[] = ' vl.result is not null AND vl.result not like "" AND result_status = ' . SAMPLE_STATUS\ACCEPTED;
     }
     if (isset($_POST['srcStatus']) && $_POST['srcStatus'] == "sent") {
          $sWhere[] = ' vl.result_sent_to_source is not null and vl.result_sent_to_source = "sent"';
     }
     if (isset($_POST['childId']) && $_POST['childId'] != "") {
          $sWhere[] = ' vl.child_id like "%' . $_POST['childId'] . '%"';
     }
     if (isset($_POST['childName']) && $_POST['childName'] != "") {
          $sWhere[] = ' vl.child_name like "%' . $_POST['childName'] . '%"';
     }
     if (isset($_POST['motherId']) && $_POST['motherId'] != "") {
          $sWhere[] = ' vl.mother_id like "%' . $_POST['motherId'] . '%"';
     }
     if (isset($_POST['motherName']) && $_POST['motherName'] != "") {
          $sWhere[] = ' vl.mother_name like "%' . $_POST['motherName'] . '%"';
     }
     if (isset($_POST['rejectedSamples']) && $_POST['rejectedSamples'] != "") {
          $sWhere[] = ' (vl.is_sample_rejected like "' . $_POST['rejectedSamples'] . '" OR vl.is_sample_rejected is null OR vl.is_sample_rejected like "")';
     }


     if ($_SESSION['instanceType'] == 'remoteuser') {
          if (!empty($_SESSION['facilityMap'])) {
               $sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ")   ";
          }
     } elseif (!$_POST['hidesrcofreq']) {
          $sWhere[] = ' vl.result_status != ' . SAMPLE_STATUS\RECEIVED_AT_CLINIC;
     }
     if (!empty($sWhere)) {
          $_SESSION['eidRequestData']['sWhere'] = $sWhere = implode(" AND ", $sWhere);
          $sQuery = $sQuery . ' WHERE ' . $sWhere;
     }
     //die($sQuery);
     if (!empty($sOrder)) {
          $_SESSION['eidRequestData']['sOrder'] = $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
          $sQuery = $sQuery . " ORDER BY " . $sOrder;
     }
     $_SESSION['eidRequestSearchResultQuery'] = $sQuery;

     [$rResult, $resultCount] = $general->getQueryResultAndCount($sQuery, null, $sLimit, $sOffset, true);

     $_SESSION['eidRequestSearchResultQueryCount'] = $resultCount;

     /*
 * Output
 */
     $output = array(
          "sEcho" => (int) $_POST['sEcho'],
          "iTotalRecords" => $resultCount,
          "iTotalDisplayRecords" => $resultCount,
          "aaData" => []
     );
     $editRequest = false;
     $syncRequest = false;
     if (_isAllowed("/eid/requests/eid-edit-request.php")) {
          $editRequest = true;
          $syncRequest = true;
     }

     foreach ($rResult as $aRow) {

          $vlResult = '';
          $edit = '';
          $sync = '';
          $barcode = '';
          $aRow['sample_collection_date'] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date'] ?? '');
          $aRow['last_modified_datetime'] = DateUtility::humanReadableDateFormat($aRow['last_modified_datetime'] ?? '');



          $row = [];

          //$row[]='<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['eid_id'] . '"  value="' . $aRow['eid_id'] . '" onclick="toggleTest(this);"  />';
          $row[] = $aRow['sample_code'];
          if ($_SESSION['instanceType'] != 'standalone') {
               $row[] = $aRow['remote_sample_code'];
          }
          if (!empty($aRow['is_encrypted']) && $aRow['is_encrypted'] == 'yes') {
               $aRow['child_id'] = $general->crypto('decrypt', $aRow['child_id'], $key);
               $aRow['child_name'] = $general->crypto('decrypt', $aRow['child_name'], $key);
               $aRow['mother_id'] = $general->crypto('decrypt', $aRow['mother_id'], $key);
               $aRow['mother_name'] = $general->crypto('decrypt', $aRow['mother_name'], $key);
          }
          $row[] = $aRow['sample_collection_date'];
          $row[] = $aRow['batch_code'];
          $row[] = ($aRow['labName']);
          $row[] = ($aRow['facility_name']);
          $row[] = $aRow['child_id'];
          $row[] = $aRow['child_name'];
          $row[] = $aRow['mother_id'];
          $row[] = $aRow['mother_name'];

          $row[] = ($aRow['facility_state']);
          $row[] = ($aRow['facility_district']);
          $row[] = ucwords($eidResults[$aRow['result']] ?? $aRow['result']);
          $row[] = $aRow['last_modified_datetime'];
          $row[] = ($aRow['status_name']);

          if ($editRequest) {
               $edit = '<a href="eid-edit-request.php?id=' . base64_encode((string) $aRow['eid_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _translate("Edit") . '"><em class="fa-solid fa-pen-to-square"></em> ' . _translate("Edit") . '</em></a>';
               if ($aRow['result_status'] == 7 && $aRow['locked'] == 'yes') {
                    if (!_isAllowed("/eid/requests/edit-locked-eid-samples")) {
                         $edit = '<a href="javascript:void(0);" class="btn btn-default btn-xs" style="margin-right: 2px;" title="' . _translate("Locked") . '" disabled><em class="fa-solid fa-lock"></em>' . _translate("Locked") . '</a>';
                    }
               }
          }

          if ($syncRequest && $_SESSION['instanceType'] == 'vluser' && ($aRow['result_status'] == 7 || $aRow['result_status'] == 4)) {
               if ($aRow['data_sync'] == 0) {
                    $sync = '<a href="javascript:void(0);" class="btn btn-info btn-xs" style="margin-right: 2px;" title="' . _translate("Sync Sample") . '" onclick="forceResultSync(\'' . ($aRow['sample_code']) . '\')"> ' . _translate("Sync") . '</a>';
               }
          } else {
               $sync = "";
          }

          if (isset($barCodeEnabled) && $barCodeEnabled != "off") {
               $fac = ($aRow['facility_name']) . " | " . $aRow['sample_collection_date'];
               $barcode = '<br><a href="javascript:void(0)" onclick="printBarcodeLabel(\'' . $aRow[$sampleCode] . '\',\'' . $fac . '\')" class="btn btn-default btn-xs" style="margin-right: 2px;" title="' . _translate("Barcode") . '"><em class="fa-solid fa-barcode"></em> ' . _translate("Barcode") . ' </a>';
          }


          $actions = "";
          if ($editRequest) {
               $actions .= $edit;
          }
          if ($syncRequest) {
               $actions .= $sync;
          }
          if (!$_POST['hidesrcofreq']) {
               $row[] = $actions . $barcode;
          }
          $output['aaData'][] = $row;
     }
     echo MiscUtility::convertToUtf8AndEncode($output);

     $db->commitTransaction();
} catch (Exception $exc) {
     LoggerUtility::log('error', $exc->getMessage(), ['trace' => $exc->getTraceAsString()]);
}
