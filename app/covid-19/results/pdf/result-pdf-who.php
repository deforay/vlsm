<?php

// this file is included in covid-19/results/generate-result-pdf.php
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Services\Covid19Service;
use App\Helpers\PdfWatermarkHelper;
use App\Registries\ContainerRegistry;
use App\Helpers\ResultPDFHelpers\Covid19ResultPDFHelper;


/** @var Covid19Service $covid19Service */
$covid19Service = ContainerRegistry::get(Covid19Service::class);
$covid19Results = $covid19Service->getCovid19Results();

$resultFilename = '';

$userRes = $usersService->getUserByID($_SESSION['userId'], 'user_signature');
$userSignaturePath = null;

if (!empty($userRes['user_signature'])) {
    $userSignaturePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $userRes['user_signature'];
}


if (!empty($requestResult)) {
    $_SESSION['rVal'] = MiscUtility::generateRandomString(6);
    $pathFront = TEMP_PATH . DIRECTORY_SEPARATOR .  $_SESSION['rVal'];
    MiscUtility::makeDirectory($pathFront);

    //foreach ($requestResult as $result) {
        $currentTime = DateUtility::getCurrentDateTime();
        $_SESSION['aliasPage'] = $page;
        if (!isset($result['labName'])) {
            $result['labName'] = '';
        }
        $draftTextShow = false;
        //Set watermark text
        for ($m = 0; $m < count($mFieldArray); $m++) {
            if (!isset($result[$mFieldArray[$m]]) || trim((string) $result[$mFieldArray[$m]]) == '' || $result[$mFieldArray[$m]] == null || $result[$mFieldArray[$m]] == '0000-00-00 00:00:00') {
                $draftTextShow = true;
                break;
            }
        }
        // create new PDF document
        $pdf = new Covid19ResultPDFHelper(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $result['lab_id'] . DIRECTORY_SEPARATOR . $result['facilityLogo'])) {
            $logoPrintInPdf = $result['facilityLogo'];
        } else {
            $logoPrintInPdf = $arr['logo'];
        }
        $pdf->setHeading($logoPrintInPdf, $arr['header'], $result['labName'], $title = 'EARLY INFANT DIAGNOSIS PATIENT REPORT');
        // set document information
        $pdf->SetCreator('VLSM');
        $pdf->SetTitle('Covid-19 Patient Report');
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



        // ---------------------------------------------------------

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
        } elseif (isset($result['patient_age']) && trim((string) $result['patient_age']) != '' && trim((string) $result['patient_age']) > 0) {
            $age = $result['patient_age'];
        }

        if (isset($result['sample_collection_date']) && trim((string) $result['sample_collection_date']) != '' && $result['sample_collection_date'] != '0000-00-00 00:00:00') {
            $expStr = explode(" ", (string) $result['sample_collection_date']);
            $result['sample_collection_date'] = DateUtility::humanReadableDateFormat($expStr[0]);
            $sampleCollectionTime = $expStr[1];
        } else {
            $result['sample_collection_date'] = '';
            $sampleCollectionTime = '';
        }
        $sampleReceivedDate = '';
        $sampleReceivedTime = '';
        if (isset($result['sample_received_at_lab_datetime']) && trim((string) $result['sample_received_at_lab_datetime']) != '' && $result['sample_received_at_lab_datetime'] != '0000-00-00 00:00:00') {
            $expStr = explode(" ", (string) $result['sample_received_at_lab_datetime']);
            $sampleReceivedDate = DateUtility::humanReadableDateFormat($expStr[0]);
            $sampleReceivedTime = $expStr[1];
        }
        $sampleDispatchDate = '';
        $sampleDispatchTime = '';
        if (isset($result['result_printed_datetime']) && trim((string) $result['result_printed_datetime']) != '' && $result['result_dispatched_datetime'] != '0000-00-00 00:00:00') {
            $expStr = explode(" ", (string) $result['result_printed_datetime']);
            $sampleDispatchDate = DateUtility::humanReadableDateFormat($expStr[0]);
            $sampleDispatchTime = $expStr[1];
        } else {
            $expStr = explode(" ", $currentTime);
            $sampleDispatchDate = DateUtility::humanReadableDateFormat($expStr[0]);
            $sampleDispatchTime = $expStr[1];
        }

        if (isset($result['sample_tested_datetime']) && trim((string) $result['sample_tested_datetime']) != '' && $result['sample_tested_datetime'] != '0000-00-00 00:00:00') {
            $expStr = explode(" ", (string) $result['sample_tested_datetime']);
            $result['sample_tested_datetime'] = DateUtility::humanReadableDateFormat($expStr[0]) . " " . $expStr[1];
        } else {
            $result['sample_tested_datetime'] = '';
        }

        if (!isset($result['patient_gender']) || trim((string) $result['patient_gender']) == '') {
            $result['patient_gender'] = _translate('Unreported');
        }
        if (isset($result['approvedBy']) && trim((string) $result['approvedBy']) != '') {
            $resultApprovedBy = ($result['approvedBy']);
        } else {
            $resultApprovedBy  = null;
        }

        $vlResult = '';
        $smileyContent = '';
        $showMessage = '';
        $tndMessage = '';
        $messageTextSize = '12px';
        if ($result['result'] != null && trim((string) $result['result']) != '') {
            $resultType = is_numeric($result['result']);
            if ($result['result'] == 'positive') {
                $vlResult = $result['result'];
                //$smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/smiley_frown.png" alt="smile_face"/>';
            } else if ($result['result'] == 'negative') {
                $vlResult = $result['result'];
                $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/smiley_smile.png" alt="smile_face"/>';
            } else if ($result['result'] == 'indeterminate') {
                $vlResult = $result['result'];
                $smileyContent = '';
            }
        }
        if (isset($arr['show_smiley']) && trim((string) $arr['show_smiley']) == "no") {
            $smileyContent = '';
        }
        if ($result['result_status'] == SAMPLE_STATUS\REJECTED) {
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/cross.png" alt="rejected"/>';
        }
        $html = '<table style="padding:0px 2px 2px 2px;">';
        $html .= '<tr>';
        $html .= '<td colspan="3">';
        $html .= '<table style="padding:2px;">';
        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">REQUESTING HEALTH CENTER NAME</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">HEALTH FACILITY CODE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Province/State</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">District/County</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['facility_name']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['facility_code']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['facility_state']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['facility_district']) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3">';
        $html .= '<table style="padding:8px 2px 2px 2px;">';
        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">PATIENT NAME</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">PATIENT ID</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">AGE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SEX</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $patientFname = ($general->crypto('doNothing', $result['patient_name'], $result['patient_id']));
        $patientLname = ($general->crypto('doNothing', $result['patient_surname'], $result['patient_id']));
        if (!empty($result['is_encrypted']) && $result['is_encrypted'] == 'yes') {
            $key = (string) $general->getGlobalConfig('key');
            $result['patient_id'] = $general->crypto('decrypt', $result['patient_id'], $key);
            $patientFname = $general->crypto('decrypt', $patientFname, $key);
            $patientLname = $general->crypto('decrypt', $patientLname, $key);
        }
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $patientFname . ' ' . $patientLname . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['patient_id'] . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $age . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . (str_replace("_", " ", (string) $result['patient_gender'])) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3">';
        $html .= '<table style="padding:8px 2px 2px 2px;">';
        $html .= '<tr>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:2px;border-bottom:1px solid #d3d3d3;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE ID</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE COLLECTION DATE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE RECEIPT DATE</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['sample_code'] . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['sample_collection_date'] . " " . $sampleCollectionTime . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $sampleReceivedDate . " " . $sampleReceivedTime . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SAMPLE TEST DATE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">RESULT RELEASE DATE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">TEST PLATFORM</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['sample_tested_datetime'] . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $sampleDispatchDate . " " . $sampleDispatchTime . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['covid19_test_platform']) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:2px;border-bottom:1px solid #d3d3d3;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE OF SYMPTOM ONSET</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">HAS THE PATIENT HAD CONTACT WITH A CONFIRMED CASE?</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">HAS THE PATIENT HAD A RECENT HISTORY OF TRAVELLING TO AN AFFECTED AREA?</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . DateUtility::humanReadableDateFormat($result['date_of_symptom_onset']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['contact_with_confirmed_case']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['has_recent_travel_history']) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">IF YES, COUNTRY NAME(S)</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">RETURN DATE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['travel_country_names']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . DateUtility::humanReadableDateFormat($result['travel_return_date']) . '</td>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:10px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3">';
        // $html .= '<table style="padding:12px 2px 2px 2px;">';
        $html .= '<table>';
        // $html .= '<tr style="background-color:#dbdbdb;">
        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:40px;font-size:12px;font-weight:normal;">';
        if (!empty($covid19TestInfo) && $arr['covid19_tests_table_in_results_pdf'] == 'yes') {
            /* Test Result Section */
            $html .= '<table border="1">
                                        <tr>
                                            <td align="center" width="15%"><strong>Test No.</strong></td>
                                            <td align="center" width="45%"><strong>Name of the Testkit (or) Test Method used</strong></td>
                                            <td align="center" width="25%"><strong>Date of Testing</strong></td>
                                            <td align="center" width="15%"><strong>Test Result</strong></td>
                                        </tr>';

            foreach ($covid19TestInfo as $indexKey => $rows) {
                $html .= '<tr>
                                            <td align="center" width="15%">' . ($indexKey + 1) . '</td>
                                            <td align="center" width="45%">' . $rows['test_name'] . '</td>
                                            <td align="center" width="25%">' . DateUtility::humanReadableDateFormat($rows['sample_tested_datetime']) . '</td>
                                            <td align="center" width="15%">' . ($rows['result']) . '</td>
                                        </tr>';
            }
            $html .= '</table>';
        }
        $html .= '<table style="padding:10px">
                                            <tr>
                                                <td colspan="2" style="line-height:10px;"></td>
                                            </tr>
                                            <tr style="background-color:#dbdbdb;">
                                                <td style="line-height:70px;font-size:18px;font-weight:normal;"><br>&nbsp;&nbsp;Result &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $covid19Results[$result['result']] . '</td>
                                                <td align="center"><br>' . $smileyContent . '</td>
                                            </tr>
                                        </table>';
        $html .= '</td>';
        $html .= '</tr>';
        //$html .= '<tr style="background-color:#dbdbdb;"><td colspan="2" style="line-height:70px;font-size:18px;font-weight:normal;">&nbsp;&nbsp;Result &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . ($result['result']) . '</td><td >' . $smileyContent . '</td></tr>';
        if ($result['reason_for_sample_rejection'] != '') {
            $html .= '<tr><td colspan="3" style="line-height:26px;font-size:12px;font-weight:bold;text-align:left;">&nbsp;&nbsp;Rejection Reason&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $result['rejection_reason_name'] . '</td></tr>';
        }
        // $html .= '<tr><td colspan="3"></td></tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';

        if (trim((string) $result['lab_tech_comments']) != '') {
            $html .= '<tr>';
            $html .= '<td colspan="3" style="line-height:11px;font-size:11px;font-weight:bold;">LAB COMMENTS&nbsp;&nbsp;:&nbsp;&nbsp;<span style="font-weight:normal;">' . ($result['lab_tech_comments']) . '</span></td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td colspan="3" style="line-height:10px;"></td>';
            $html .= '</tr>';
        }

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:14px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:8px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:2px;border-bottom:1px solid #d3d3d3;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:22px;"></td>';
        $html .= '</tr>';

        if ($result['is_sample_rejected'] == 'no') {
            $html .= '<tr>';
            $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">TESTED BY</td>';
            $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SIGNATURE</td>';
            $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
            $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
            $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
            $html .= '</tr>';
        }

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:8px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">APPROVED BY</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SIGNATURE</td>';
        $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">DATE</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $resultApprovedBy . '</td>';
        if (!empty($userSignaturePath) && file_exists($userSignaturePath)) {
            $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"><img src="' . $userSignaturePath . '" style="width:70px;" /></td>';
        } else {
            $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"></td>';
        }
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . DateUtility::humanReadableDateFormat($result['result_approved_datetime']) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:20px;border-bottom:2px solid #d3d3d3;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3" style="line-height:2px;"></td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td colspan="3">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td style="font-size:10px;text-align:left;">Printed on : ' . $printDate . '&nbsp;&nbsp;' . $printDateTime . '</td>';
        $html .= '<td style="font-size:10px;text-align:left;width:60%;"></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="2" style="font-size:10px;text-align:left;width:60%;"></td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        if ($result['result'] != '' || ($result['result'] == '' && $result['result_status'] == SAMPLE_STATUS\REJECTED)) {
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
            $action = ($_SESSION['userName'] ?: 'System') . ' generated the test result PDF with Patient ID/Code ' . $result['patient_id'];
            $resource = 'print-test-result';
            $data = array(
                'event_type' => $eventType,
                'action' => $action,
                'resource' => $resource,
                'date_time' => $currentTime
            );
            $db->insert($tableName1, $data);
            //Update print datetime in VL tbl.
            $vlQuery = "SELECT result_printed_datetime FROM form_covid19 as vl WHERE vl.covid19_id ='" . $result['covid19_id'] . "'";
            $vlResult = $db->query($vlQuery);
            if ($vlResult[0]['result_printed_datetime'] == null || trim((string) $vlResult[0]['result_printed_datetime']) == '' || $vlResult[0]['result_printed_datetime'] == '0000-00-00 00:00:00') {
                $db->where('covid19_id', $result['covid19_id']);
                $db->update($tableName2, array('result_printed_datetime' => $currentTime, 'result_dispatched_datetime' => $currentTime));
            }
        }
    //}
}
