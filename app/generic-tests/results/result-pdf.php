<?php

// This file is included in /vl/results/generate-result-pdf.php


use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Helpers\PdfWatermarkHelper;
use App\Helpers\PdfConcatenateHelper;
use App\Registries\ContainerRegistry;
use App\Services\GenericTestsService;
use App\Helpers\ResultPDFHelpers\GenericTestsResultPDFHelper;

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/** @var GenericTestsService $genericTestsService */
$genericTestsService = ContainerRegistry::get(GenericTestsService::class);

$resultFilename = $showHideTable = '';
if (!empty($requestResult)) {
     $_SESSION['rVal'] = MiscUtility::generateRandomString(6);
     $showHideTable = (string) ($general->getGlobalConfig('generic_tests_table_in_results_pdf')) ?? 'no';
     $pathFront = TEMP_PATH . DIRECTORY_SEPARATOR .  $_SESSION['rVal'];
     MiscUtility::makeDirectory($pathFront);
     $pages = [];
     $page = 1;
     foreach ($requestResult as $result) {
          $currentTime = DateUtility::getCurrentDateTime();

          $testResultUnits = $genericTestsService->getTestResultUnit($result['testType']);
          $testUnits = [];
          foreach ($testResultUnits as $key => $unit) {
               $testUnits[$unit['unit_id']] = $unit['unit_name'];
          }

          $testTypeQuery = "SELECT * FROM r_test_types WHERE test_type_id= ?";
          $testTypeResult = $db->rawQueryOne($testTypeQuery, [$result['testType']]);
          $testResultsAttribute = json_decode((string) $testTypeResult['test_results_config'], true);
          $subTestKey = [];
          foreach ($testResultsAttribute['result_type'] as $key => $resultType) {
               if ($resultType == 'quantitative') {
                    $subTestKey[$testResultsAttribute['sub_test_name'][$key]] = $key;
               }
          }
          // echo "<pre>";print_r($testResultsAttribute);die;

          // $genericTestQuery = "SELECT res.*, m.test_method_name from generic_test_results as res INNER JOIN r_generic_test_methods AS m ON m.test_method_id=res.test_name where res.generic_id=? ORDER BY res.test_id ASC";
          // $genericTestInfo = $db->rawQuery($genericTestQuery, array($result['sample_id']));
          $genericTestQuery = "SELECT * FROM generic_test_results WHERE generic_id=? ORDER BY test_id ASC";
          $genericTestInfo = $db->rawQuery($genericTestQuery, [$result['sample_id']]);
          // $testedBy = null;
          if (!empty($result['tested_by'])) {
               $testedByRes = $usersService->getUserByID($result['tested_by'], array('user_name', 'user_signature'));
               if ($testedByRes) {
                    $testedBy = $testedByRes['user_name'];
               }
          }
          $reviewedBy = null;
          if (!empty($result['result_reviewed_by'])) {
               $reviewedByRes = $usersService->getUserByID($result['result_reviewed_by'], array('user_name', 'user_signature'));
               if ($reviewedByRes) {
                    $reviewedBy = $reviewedByRes['user_name'];
               }
          }

          $revisedBy = null;
          $revisedByRes = [];
          if (!empty($result['revised_by'])) {
               $revisedByRes = $usersService->getUserByID($result['revised_by'], array('user_name', 'user_signature'));
               if ($revisedByRes) {
                    $revisedBy = $revisedByRes['user_name'];
               }
          }

          $revisedBySignaturePath = $reviewedBySignaturePath = $testedBySignaturePath = null;
          if (!empty($testedByRes['user_signature'])) {
               $testedBySignaturePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $testedByRes['user_signature'];
          }
          if (!empty($reviewedByRes['user_signature'])) {
               $reviewedBySignaturePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $reviewedByRes['user_signature'];
          }
          if (!empty($revisedByRes['user_signature'])) {
               $revisedBySignaturePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $revisedByRes['user_signature'];
          }

          $resultApprovedBy = '';
          $userSignaturePath = null;
          if (!empty($result['result_approved_by'])) {
               $resultApprovedByRes = $usersService->getUserByID($result['result_approved_by'], array('user_name', 'user_signature'));
               if ($resultApprovedByRes) {
                    $resultApprovedBy = $resultApprovedByRes['result_approved_by'];
               }
               if (!empty($resultApprovedByRes['user_signature'])) {
                    $userSignaturePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $resultApprovedByRes['user_signature'];
               }
          }

          if (isset($result['approvedBy']) && trim((string) $result['approvedBy']) != '') {
               $resultApprovedBy = ($result['approvedBy']);
               $userRes = $usersService->getUserByID($result['result_approved_by'], 'user_signature');
          } else {
               $resultApprovedBy  = null;
          }

          $userSignaturePath = null;
          if (!empty($userRes['user_signature'])) {
               $userSignaturePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $userRes['user_signature'];
          }
          $_SESSION['aliasPage'] = $page;
          if (!isset($result['labName'])) {
               $result['labName'] = '';
          }
          $draftTextShow = false;
          //Set watermark text
          /* echo "<pre>";
          print_r($mFieldArray);die; */
          if (isset($mFieldArray) && count($mFieldArray) > 0) {
               for ($m = 0; $m < count($mFieldArray); $m++) {
                    if (!isset($result[$mFieldArray[$m]]) || trim((string) $result[$mFieldArray[$m]]) == '' || $result[$mFieldArray[$m]] == null || $result[$mFieldArray[$m]] == '0000-00-00 00:00:00') {
                         $draftTextShow = true;
                         break;
                    }
               }
          }

          // create new PDF document
          $pdf = new GenericTestsResultPDFHelper(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
          if (MiscUtility::isImageValid(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $result['lab_id'] . DIRECTORY_SEPARATOR . $result['facilityLogo'])) {
               $logoPrintInPdf = UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $result['lab_id'] . DIRECTORY_SEPARATOR . $result['facilityLogo'];
          } else {
               $logoPrintInPdf = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $arr['logo'];
          }
          $pdf->setHeading($logoPrintInPdf, $arr['header'], $result['labName'], $title = 'OTHER LAB TESTS PATIENT REPORT', null, $result['test_standard_name']);
          // set document information
          $pdf->SetCreator('VLSM');
          $pdf->SetTitle('OTHER LAB TESTS PATIENT REPORT');
          //$pdf->SetSubject('TCPDF Tutorial');
          //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

          // set default header data
          $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

          // set header and footer fonts
          $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
          $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

          // set default monospaced font
          $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

          // set margins
          $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 14, PDF_MARGIN_RIGHT);
          $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
          $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

          // set auto page breaks
          $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

          // set image scale factor
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);



          // set font
          $pdf->SetFont('helvetica', '', 18);

          $pdf->AddPage();
          if (!isset($result['facility_code']) || trim((string) $result['facility_code']) == '') {
               $result['facility_code'] = '';
          }
          if (!isset($result['facility_state']) || trim((string) $result['facility_state']) == '') {
               $result['facility_state'] = '';
          }
          if (!isset($result['facility_district']) || trim((string) $result['facility_district']) == '') {
               $result['facility_district'] = '';
          }
          if (!isset($result['facility_name']) || trim((string) $result['facility_name']) == '') {
               $result['facility_name'] = '';
          }
          if (!isset($result['labName']) || trim((string) $result['labName']) == '') {
               $result['labName'] = '';
          }
          //Set Age
          $age = 'Unknown';
          if (isset($result['patient_dob']) && trim((string) $result['patient_dob']) != '' && $result['patient_dob'] != '0000-00-00') {
               $todayDate = strtotime(date('Y-m-d'));
               $dob = strtotime((string) $result['patient_dob']);
               $difference = $todayDate - $dob;
               $seconds_per_year = 60 * 60 * 24 * 365;
               $age = round($difference / $seconds_per_year);
          } elseif (isset($result['patient_age_in_years']) && trim((string) $result['patient_age_in_years']) != '' && trim((string) $result['patient_age_in_years']) > 0) {
               $age = $result['patient_age_in_years'];
          } elseif (isset($result['patient_age_in_months']) && trim((string) $result['patient_age_in_months']) != '' && trim((string) $result['patient_age_in_months']) > 0) {
               if ($result['patient_age_in_months'] > 1) {
                    $age = $result['patient_age_in_months'] . ' months';
               } else {
                    $age = $result['patient_age_in_months'] . ' month';
               }
          }

          if (isset($result['sample_collection_date']) && trim((string) $result['sample_collection_date']) != '' && $result['sample_collection_date'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", (string) $result['sample_collection_date']);
               $result['sample_collection_date'] = date('d/M/Y', strtotime($expStr[0]));
               $sampleCollectionTime = $expStr[1];
          } else {
               $result['sample_collection_date'] = '';
               $sampleCollectionTime = '';
          }
          $sampleReceivedDate = '';
          $sampleReceivedTime = '';
          if (isset($result['sample_received_at_lab_datetime']) && trim((string) $result['sample_received_at_lab_datetime']) != '' && $result['sample_received_at_lab_datetime'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", (string) $result['sample_received_at_lab_datetime']);
               $sampleReceivedDate = date('d/M/Y', strtotime($expStr[0]));
               $sampleReceivedTime = $expStr[1];
          }
          $sampleDispatchDate = '';
          $sampleDispatchTime = '';
          if (isset($result['result_printed_datetime']) && trim((string) $result['result_printed_datetime']) != '' && $result['result_dispatched_datetime'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", (string) $result['result_printed_datetime']);
               $sampleDispatchDate = date('d/M/Y', strtotime($expStr[0]));
               $sampleDispatchTime = $expStr[1];
          } else {
               $expStr = explode(" ", $currentTime);
               $sampleDispatchDate = date('d/M/Y', strtotime($expStr[0]));
               $sampleDispatchTime = $expStr[1];
          }

          if (isset($result['sample_tested_datetime']) && trim((string) $result['sample_tested_datetime']) != '' && $result['sample_tested_datetime'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", (string) $result['sample_tested_datetime']);
               $result['sample_tested_datetime'] = date('d/M/Y', strtotime($expStr[0])) . " " . $expStr[1];
          } else {
               $result['sample_tested_datetime'] = '';
          }

          if (isset($result['result_reviewed_datetime']) && trim((string) $result['result_reviewed_datetime']) != '' && $result['result_reviewed_datetime'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", (string) $result['result_reviewed_datetime']);
               $result['result_reviewed_datetime'] = date('d/M/Y', strtotime($expStr[0])) . " " . $expStr[1];
          } else {
               $result['result_reviewed_datetime'] = '';
          }

          if (isset($result['result_approved_datetime']) && trim((string) $result['result_approved_datetime']) != '' && $result['result_approved_datetime'] != '0000-00-00 00:00:00') {
               $expStr = explode(" ", (string) $result['result_approved_datetime']);
               $result['result_approved_datetime'] = date('d/M/Y', strtotime($expStr[0])) . " " . $expStr[1];
          } else {
               $result['result_approved_datetime'] = '';
          }

          if (isset($result['last_viral_load_date']) && trim((string) $result['last_viral_load_date']) != '' && $result['last_viral_load_date'] != '0000-00-00') {
               $result['last_viral_load_date'] = date('d/M/Y', strtotime((string) $result['last_viral_load_date']));
               $result['last_viral_load_date'] = date('d/M/Y', strtotime($result['last_viral_load_date']));
          } else {
               $result['last_viral_load_date'] = '';
          }
          if (!isset($result['patient_gender']) || trim((string) $result['patient_gender']) == '') {
               $result['patient_gender'] = _translate('Unreported');
          }

          $smileyContent = '';
          $showMessage = '';
          $tndMessage = '';
          $messageTextSize = '12px';
          $vlResult = trim((string) $result['result']);

          if (isset($arr['show_smiley']) && trim((string) $arr['show_smiley']) == "no") {
               $smileyContent = '';
          }
          if ($result['result_status'] == SAMPLE_STATUS\REJECTED) {
               $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/cross.png" style="width:50px;" alt="rejected"/>';
          }
          $html = '<table style="padding:4px 2px 2px 2px;width:100%;">';
          $html .= '<tr>';

          $html .= '<td colspan="3">';
          $html .= '<table style="padding:2px;">';
          $html .= '<tr>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">REQUESTING HEALTH FACILITY NAME</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">HEALTH FACILITY CODE</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">STATE</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">COUNTY</td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ($result['facility_name']) . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ($result['facility_code']) . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ($result['facility_state']) . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ($result['facility_district']) . '</td>';
          $html .= '</tr>';
          $html .= '</table>';
          $html .= '</td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td colspan="3">';
          $html .= '<table style="padding:4px 2px 2px 2px;">';
          $html .= '<tr>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">PATIENT NAME</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">EPID NUMBER</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">REASON FOR VL TESTING</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
          $html .= '</tr>';
          $html .= '<tr>';

          $patientFname = ($general->crypto('doNothing', $result['patient_first_name'], $result['patient_id']));


          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $patientFname . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['patient_id'] . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ($result['test_reason']) . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;"></td>';
          $html .= '</tr>';
          $html .= '</table>';
          $html .= '</td>';
          $html .= '</tr>';

          $html .= '<tr>';
          $html .= '<td colspan="3">';
          $html .= '<table style="padding:8px 2px 2px 2px;">';
          $html .= '<tr>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">AGE</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SEX</td>';
          if ($result['patient_gender'] == 'female') {
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">BREAST FEEDING</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">PREGNANCY STATUS</td>';
          } else {
               //$html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">LOINC CODE</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
          }
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $age . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ucwords(str_replace("_", " ", (string) $result['patient_gender'])) . '</td>';
          if ($result['patient_gender'] == 'female') {
               $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . (str_replace("_", " ", (string) $result['is_patient_breastfeeding'])) . '</td>';
               $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . (str_replace("_", " ", (string) $result['is_patient_pregnant'])) . '</td>';
          } else {
               // $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['test_loinc_code'] . '</td>';
               $html .= '<td colspan="2" style="line-height:10px;font-size:10px;text-align:left;"></td>';
               $html .= '<td colspan="2" style="line-height:10px;font-size:10px;text-align:left;"></td>';
          }
          $html .= '</tr>';

          if (
               (isset($result['request_clinician_name']) && !empty($result['request_clinician_name'])) ||
               (isset($result['request_clinician_phone_number']) && !empty($result['request_clinician_phone_number'])) ||
               (isset($result['facility_emails']) && !empty($result['facility_emails']))
          ) {
               $html .= '<tr>';
               $html .= '<td colspan="4" style="line-height:2px;border-bottom:2px solid #d3d3d3;"></td>';
               $html .= '</tr>';
               $html .= '<tr>';
               $html .= '<td colspan="2" style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">REQUESTING CLINICIAN NAME</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">TEL</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">EMAIL</td>';
               $html .= '</tr>';
               $html .= '<tr>';
               $html .= '<td colspan="2" style="line-height:10px;font-size:10px;text-align:left;">' . ucwords($result['request_clinician_name']) . '</td>';
               $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['request_clinician_phone_number'] . '</td>';
               $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['facility_emails'] . '</td>';
               $html .= '</tr>';
          }
          $html .= '</table>';
          $html .= '</td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td colspan="3" style="line-height:2px;border-bottom:2px solid #d3d3d3;"></td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td style="line-height:12px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE ID</td>';
          $html .= '<td style="line-height:12px;font-size:11px;font-weight:bold;text-align:left;">LABORATORY NUMBER</td>';
          $html .= '<td style="line-height:12px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE COLLECTION DATE</td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['sample_code'] . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['laboratory_number'] . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['sample_collection_date'] . " " . $sampleCollectionTime . '</td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td style="line-height:12px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE RECEIPT DATE</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE REJECTION STATUS</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE TEST DATE</td>';
          $html .= '</tr>';
          $rejectedStatus = (!empty($result['is_sample_rejected']) && $result['is_sample_rejected'] == 'yes') ? 'Rejected' : 'Not Rejected';
          $html .= '<tr>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $sampleReceivedDate . " " . $sampleReceivedTime . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $rejectedStatus . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $result['sample_tested_datetime'] . '</td>';
          $html .= '</tr>';

          $html .= '<tr>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">RESULT RELEASE DATE</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE TYPE</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . $sampleDispatchDate . " " . $sampleDispatchTime . '</td>';
          $html .= '<td style="line-height:10px;font-size:10px;text-align:left;">' . ($result['sample_type_name']) . '</td>';
          $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
          $html .= '</tr></table>';
          //echo '<pre>'; print_r($genericTestInfo); die;
          if (!empty($genericTestInfo) && $showHideTable == 'yes') {
               $w = 25;
               if (isset($result['sub_tests']) && !empty($result['sub_tests'])) {
                    $titleTest = "Range";
               } else {
                    $titleTest = "Platform";
               }
               /* Test Result Section */
               $innerHtml .= '<table border="0" style="padding:5px;">';
               $innerHtml .= '<tr>
                    <th align="left" style="width:' . $w . '%;line-height:15px;font-size:11px;font-weight:bold;border-right-color:white;border-bottom-color:black;border-top-color:black;">Test Name</th>
                    <th align="left" style="width:' . $w . '%;line-height:15px;font-size:11px;font-weight:bold;border-left-color:white;border-right-color:white;border-bottom-color:black;border-top-color:black;">Result</th>';
               $innerHtml .= '<th align="left" style="width:' . $w . '%;line-height:15px;font-size:11px;font-weight:bold;border-left-color:white;border-right-color:white;border-bottom-color:black;border-top-color:black;">' . $titleTest . '</th>';
               $innerHtml .= '<th align="left" style="width:' . $w . '%;line-height:15px;font-size:11px;font-weight:bold;border-left-color:white;border-bottom-color:black;border-top-color:black;">Unit</th>
               </tr>';
               if (isset($result['sub_tests']) && !empty($result['sub_tests'])) {
                    $subTestsList = explode("##", $result['sub_tests']);
                    $subTestCnt = count($subTestsList);
                    $n = 1;
                    foreach ($subTestsList as $key => $subTestName) {
                         $finalResult = [];
                         $lastLineBorder = '';
                         if (($subTestCnt - 1) == $n) {
                              $lastLineBorder = 'border-bottom-color:black';
                         }
                         $innerHtml .= '<tr><td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . $subTestName . '</td>';
                         foreach ($genericTestInfo as $indexKey => $rows) {
                              if (strtolower($subTestName) == $rows['sub_test_name']) {
                                   $finalResult['finalResult'] = $rows['final_result'];
                                   $finalResult['finalResultUnit'] = $testUnits[$rows['final_result_unit']];
                                   // $finalResult['finalResultInterpretation'] = $rows['final_result_interpretation'];
                                   $n++;
                              }
                         }
                         $rangeTxt = '<span style="color:black;">';
                         if (isset($subTestKey[$subTestName]) && !empty($subTestKey[$subTestName])) {
                              if (($testResultsAttribute['quantitative']['high_range'][$subTestKey[$subTestName]] <= $finalResult['finalResult']) || ($testResultsAttribute['quantitative']['threshold_range'][$subTestKey[$subTestName]] < $finalResult['finalResult'])) {
                                   $highRange = $testResultsAttribute['quantitative']['high_range'][$subTestKey[$subTestName]];
                              }
                              if ($testResultsAttribute['quantitative']['threshold_range'][$subTestKey[$subTestName]] == $finalResult['finalResult']) {
                                   $thresholdRange = $testResultsAttribute['quantitative']['threshold_range'][$subTestKey[$subTestName]];
                              }
                              if (($testResultsAttribute['quantitative']['low_range'][$subTestKey[$subTestName]] >= $finalResult['finalResult']) || ($testResultsAttribute['quantitative']['threshold_range'][$subTestKey[$subTestName]] >= $finalResult['finalResult'])) {
                                   $lowRange = $testResultsAttribute['quantitative']['low_range'][$subTestKey[$subTestName]];
                              }
                         }

                         if (isset($finalResult['finalResult']) && !empty($finalResult['finalResult'])) {
                              $innerHtml .= '<td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . ucwords($finalResult['finalResult']) . '</td>';
                         } else {
                              $innerHtml .= '<td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';"></td>';
                         }

                         if ((isset($highRange) && !empty($highRange)) || isset($thresholdRange) && !empty($thresholdRange) || isset($lowRange) && !empty($lowRange)) {
                              $innerHtml .= '<td align="left" style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">
                              <span>' . $testResultsAttribute['quantitative']['high_range'][$subTestKey[$subTestName]] . '</span>-
                              <span>' . $testResultsAttribute['quantitative']['threshold_range'][$subTestKey[$subTestName]] . '</span>-
                              <span>' . $testResultsAttribute['quantitative']['low_range'][$subTestKey[$subTestName]] . '</span>
                              </td>';
                         } else {
                              $innerHtml .= '<td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">&nbsp;&nbsp;&nbsp; -</td>';
                         }
                         if (isset($finalResult['finalResultUnit']) && !empty($finalResult['finalResultUnit'])) {
                              $innerHtml .= '<td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . $finalResult['finalResultUnit'] . '</td>';
                         } else {
                              $innerHtml .= '<td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">&nbsp; -</td>';
                         }

                         $innerHtml .= '</tr>';
                         $n++;
                    }
               } else {
                    foreach ($genericTestInfo as $indexKey => $rows) {
                         $lastLineBorder = '';
                         if ((count($genericTestInfo) - 1) == $indexKey) {
                              $lastLineBorder = 'border-bottom-color:black';
                         }
                         $innerHtml .= '<tr>';
                         $innerHtml .= '     <td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . $rows['test_name'] . '</td>';
                         $innerHtml .= '     <td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . ucwords($rows['result']) . '</td>';
                         $innerHtml .= '     <td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . ucwords($rows['testing_platform']) . '</td>';
                         $innerHtml .= '     <td style="line-height:10px;font-size:11px;' . $lastLineBorder . ';">' . $testUnits[$rows['result_unit']] . '</td>';
                         $innerHtml .= '</tr>';
                    }
               }
               $innerHtml .= '</table>';
               $html .= $innerHtml;
          }
          $html .= '<table style="padding:4px 2px 2px 2px;width:100%;">';
          $html .= '<tr>';

          $html .= '<td colspan="3"><br><br>';
          $html .= '<table style="padding:10px 2px 2px 2px;">';
          $html .= '<tr style="background-color:#dbdbdb;"><td colspan="2" style="line-height:26px;font-size:12px;font-weight:bold;">&nbsp;&nbsp;Result &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $vlResult . '</td><td >' . $smileyContent . '</td></tr>';
          if ($result['reason_for_sample_rejection'] != '') {
               $html .= '<tr><td colspan="3" style="line-height:26px;font-size:12px;font-weight:bold;text-align:left;">&nbsp;&nbsp;Rejection Reason&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $result['rejection_reason_name'] . '</td></tr>';
          }
          if (str_contains(strtolower((string)$result['vl_test_platform']), 'abbott')) {
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:8px;font-size:8px;padding-top:8px;">Abbott Linear Detection range: 839 copies/mL - 10 million copies/mL</td>';
               $html .= '</tr>';
          }
          //$html .= '<tr><td colspan="3"></td></tr>';
          $html .= '</table>';
          $html .= '</td>';
          $html .= '</tr>';
          if (trim((string) $showMessage) != '') {
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:13px;font-size:' . $messageTextSize . ';text-align:left;">' . $showMessage . '</td>';
               $html .= '</tr>';
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:16px;"></td>';
               $html .= '</tr>';
          }
          if (trim($tndMessage) != '') {
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:13px;font-size:18px;text-align:left;">' . $tndMessage . '</td>';
               $html .= '</tr>';
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:16px;"></td>';
               $html .= '</tr>';
          }

          $html .= '<tr>';
          $html .= '<td colspan="3" style="line-height:2px;border-bottom:2px solid #d3d3d3;"></td>';
          $html .= '</tr>';
          /* $html .= '<tr>';
          $html .= '<td colspan="3" style="line-height:15px;font-size:11px;font-weight:bold;">TEST PLATFORM &nbsp;&nbsp;:&nbsp;&nbsp; <span style="font-weight:normal;">' . ($result['test_platform']) . '</span></td>';
          $html .= '</tr>';

          $html .= '<tr>';
          $html .= '<td colspan="3" style="line-height:2px;border-bottom:2px solid #d3d3d3;"></td>';
          $html .= '</tr>';
          if ($result['is_sample_rejected'] == 'no') {
               if (!empty($testedBy) && !empty($result['sample_tested_datetime'])) {
                    $html .= '<tr>';
                    $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">TESTED BY</td>';
                    $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SIGNATURE</td>';
                    $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE</td>';
                    $html .= '</tr>';

                    $html .= '<tr>';
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $testedBy . '</td>';
                    if (!empty($testedBySignaturePath) && MiscUtility::isImageValid(($testedBySignaturePath))) {
                         $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"><img src="' . $testedBySignaturePath . '" style="width:50px;" /></td>';
                    } else {
                         $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
                    }
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['sample_tested_datetime'] . '</td>';
                    $html .= '</tr>';
               }
          } */
          if (!empty($reviewedBy)) {
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:8px;"></td>';
               $html .= '</tr>';

               $html .= '<tr>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">REVIEWED BY</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SIGNATURE</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE</td>';
               $html .= '</tr>';

               $html .= '<tr>';
               $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $reviewedBy . '</td>';
               if (!empty($reviewedBySignaturePath) && MiscUtility::isImageValid($reviewedBySignaturePath)) {
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"><img src="' . $reviewedBySignaturePath . '" style="width:50px;" /></td>';
               } else {
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
               }
               $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . (!empty($result['result_reviewed_datetime']) ? $result['result_reviewed_datetime'] : $result['sample_tested_datetime']) . '</td>';
               $html .= '</tr>';
          }

          if (!empty($revisedBy)) {

               $html .= '<tr>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">REPORT REVISED BY</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SIGNATURE</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE</td>';
               $html .= '</tr>';

               $html .= '<tr>';
               $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $revisedBy . '</td>';
               if (!empty($revisedBySignaturePath) && MiscUtility::isImageValid($revisedBySignaturePath)) {
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"><img src="' . $revisedBySignaturePath . '" style="width:70px;" /></td>';
               } else {
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
               }
               $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . date('d/M/Y', strtotime((string) $result['revised_on'])) . '</td>';
               $html .= '</tr>';
          }

          $html .= '<tr>';
          $html .= '<td colspan="3" style="line-height:8px;"></td>';
          $html .= '</tr>';
          if (!empty($resultApprovedBy) && !empty($result['result_approved_datetime'])) {
               $html .= '<tr>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">APPROVED BY</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SIGNATURE</td>';
               $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE</td>';
               $html .= '</tr>';

               $html .= '<tr>';
               $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $resultApprovedBy . '</td>';
               if (!empty($userSignaturePath) && MiscUtility::isImageValid(($userSignaturePath))) {
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"><img src="' . $userSignaturePath . '" style="width:50px;" /></td>';
               } else {
                    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
               }

               $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['result_approved_datetime'] . '</td>';
               $html .= '</tr>';
          }

          if (!empty($result['lab_tech_comments'])) {

               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:20px;"></td>';
               $html .= '</tr>';
               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:11px;font-size:11px;text-align:left;"><strong>Lab Comments:</strong> ' . $result['lab_tech_comments'] . '</td>';
               $html .= '</tr>';

               $html .= '<tr>';
               $html .= '<td colspan="3" style="line-height:2px;"></td>';
               $html .= '</tr>';
          }
          $html .= '<tr>';
          $html .= '<td colspan="3" style="line-height:2px;border-bottom:2px solid #d3d3d3;"></td>';
          $html .= '</tr>';
          $html .= '<tr>';
          $html .= '<td colspan="3">';
          $html .= '<table>';
          $html .= '<tr>';
          $html .= '<td style="font-size:10px;text-align:left;width:75%;"></td>';
          $html .= '<td style="font-size:10px;text-align:left;">Printed on : ' . $printDate . '&nbsp;&nbsp;' . $printDateTime . '</td>';
          $html .= '</tr>';
          $html .= '</table>';
          $html .= '</td>';
          $html .= '</tr>';
          $html .= '</table>';

          if ($vlResult != '' || ($vlResult == '' && $result['result_status'] == SAMPLE_STATUS\REJECTED)) {
               $pdf->writeHTML($html);
               $pdf->lastPage();
               $filename = $pathFront . DIRECTORY_SEPARATOR . 'p' . $page . '.pdf';
               $pdf->Output($filename, "F");
               if ($draftTextShow) {
                    //Watermark section
                    $watermark = new PdfWatermarkHelper();
                    $watermark->setFullPathToFile($filename);
                    $fullPathToFile = $filename;
                    $watermark->Output($filename, "F");
               }
               $pages[] = $filename;
               $page++;
          }
          if (isset($_POST['source']) && trim((string) $_POST['source']) == 'print') {
               //Add event log
               $eventType = 'print-result';
               $action = $_SESSION['userName'] . ' generated the test result PDF with Patient ID/Code ' . $result['patient_id'];
               $resource = 'print-test-result';
               $data = array(
                    'event_type' => $eventType,
                    'action' => $action,
                    'resource' => $resource,
                    'date_time' => $currentTime
               );
               $db->insert($tableName1, $data);
               //Update print datetime in VL tbl.
               $vlQuery = "SELECT result_printed_datetime FROM form_generic as vl WHERE vl.sample_id ='" . $result['sample_id'] . "'";
               $vlResult = $db->query($vlQuery);
               if ($vlResult[0]['result_printed_datetime'] == null || trim((string) $vlResult[0]['result_printed_datetime']) == '' || $vlResult[0]['result_printed_datetime'] == '0000-00-00 00:00:00') {
                    $db->where('sample_id', $result['sample_id']);
                    $db->update($tableName2, array('result_printed_datetime' => $currentTime, 'result_dispatched_datetime' => $currentTime));
               }
          }
     }

     if (!empty($pages)) {
          $resultPdf = new PdfConcatenateHelper();
          $resultPdf->setFiles($pages);
          $resultPdf->setPrintHeader(false);
          $resultPdf->setPrintFooter(false);
          $resultPdf->concat();
          $resultFilename = 'VLSM-LAB-TESTS-RESULT-' . date('d-M-Y-H-i-s') . "-" . MiscUtility::generateRandomString(6) . '.pdf';
          $resultPdf->Output(TEMP_PATH . DIRECTORY_SEPARATOR . $resultFilename, "F");
          MiscUtility::removeDirectory($pathFront);
          unset($_SESSION['rVal']);
     }
}

echo base64_encode(TEMP_PATH . DIRECTORY_SEPARATOR . $resultFilename);
