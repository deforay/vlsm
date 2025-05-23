<?php

use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Services\CommonService;
use App\Helpers\PdfWatermarkHelper;
use App\Helpers\ResultPDFHelpers\EIDResultPDFHelper;

// this file is included in eid/results/generate-result-pdf.php

if (!empty($result)) {

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
    $pdf = new EIDResultPDFHelper(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $result['lab_id'] . DIRECTORY_SEPARATOR . $result['facilityLogo'])) {
        $logoPrintInPdf = $result['facilityLogo'];
    } else {
        $logoPrintInPdf = $arr['logo'];
    }
    $pdf->setHeading($logoPrintInPdf, $arr['header'], $result['labName'], $title = 'EARLY INFANT DIAGNOSIS PATIENT REPORT');
    // set document information
    $pdf->SetCreator('VLSM');
    $pdf->SetTitle('Early Infant Diagnosis Patient Report');
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
    if (isset($result['child_dob']) && trim((string) $result['child_dob']) != '' && $result['child_dob'] != '0000-00-00') {
        $todayDate = strtotime(date('Y-m-d'));
        $dob = strtotime((string) $result['child_dob']);
        $difference = $todayDate - $dob;
        $seconds_per_year = 60 * 60 * 24 * 365;
        $age = round($difference / $seconds_per_year);
    } elseif (isset($result['child_age']) && trim((string) $result['child_age']) != '' && trim((string) $result['child_age']) > 0) {
        $age = $result['child_age'];
    }

    $result['sample_collection_date'] = DateUtility::humanReadableDateFormat($result['sample_collection_date'] ?? '', true);
    $result['result_printed_datetime'] = DateUtility::humanReadableDateFormat($result['result_printed_datetime'] ?? DateUtility::getCurrentDateTime(), true);
    $result['sample_received_at_lab_datetime'] = DateUtility::humanReadableDateFormat($result['sample_received_at_lab_datetime'] ?? '', true);
    $result['sample_tested_datetime'] = DateUtility::humanReadableDateFormat($result['sample_tested_datetime'] ?? '', true);

    if (!isset($result['child_gender']) || trim((string) $result['child_gender']) == '') {
        $result['child_gender'] = _translate('Unreported');
    }
    $resultApprovedBy  = null;
    $userRes = [];


    $resultApprovedBy = $result['approvedBy'] ?? null;
    if (empty($resultApprovedBy)) {
        $approvedByInfo = $usersService->getUserNameAndSignature($result['defaultApprovedBy']);
        $resultApprovedBy = $approvedByInfo['user_name'];
        $result['approvedBySignature'] = $approvedByInfo['user_signature'];
    }

    if (empty($result['result_approved_datetime']) && !empty($result['sample_tested_datetime'])) {
        $result['result_approved_datetime'] = $result['sample_tested_datetime'];
    }

    if (empty($result['result_reviewed_datetime']) && !empty($result['sample_tested_datetime'])) {
        $result['result_reviewed_datetime'] = $result['sample_tested_datetime'];
    }

    $approvedBySignaturePath= null;

    if (!empty($result['approvedBySignature'])) {
        $approvedBySignaturePath =  MiscUtility::getFullImagePath($result['approvedBySignature'], UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature");
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
            //$smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/smiley_smile.png" alt="smile_face"/>';
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
    $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">CHILD ID</td>';
    $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">AGE</td>';
    $html .= '<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">SEX</td>';
    $html .= '</tr>';
    $html .= '<tr>';

    $patientFname = ($general->crypto('doNothing', $result['child_name'], $result['child_id']));


    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $patientFname . '</td>';

    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['child_id'] . '</td>';
    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $age . '</td>';
    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . (str_replace("_", " ", (string) $result['child_gender'])) . '</td>';
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
    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['sample_collection_date'] . '</td>';
    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['sample_received_at_lab_datetime'] . '</td>';
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
    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . $result['result_printed_datetime'] . '</td>';
    $html .= '<td style="line-height:11px;font-size:11px;text-align:left;">' . ($result['eid_test_platform']) . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td colspan="3" style="line-height:10px;"></td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td colspan="3">';
    $html .= '<table style="padding:12px 2px 2px 2px;">';

    $html .= '<tr style="background-color:#dbdbdb;"><td colspan="2" style="line-height:70px;font-size:18px;font-weight:normal;">&nbsp;&nbsp;Result &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $eidResults[$result['result']] . '</td><td >' . $smileyContent . '</td></tr>';
    //$html .= '<tr style="background-color:#dbdbdb;"><td colspan="2" style="line-height:70px;font-size:18px;font-weight:normal;">&nbsp;&nbsp;Result &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . ($result['result']) . '</td><td >' . $smileyContent . '</td></tr>';
    if ($result['reason_for_sample_rejection'] != '') {
        $html .= '<tr><td colspan="3" style="line-height:26px;font-size:12px;font-weight:bold;text-align:left;">&nbsp;&nbsp;Rejection Reason&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;' . $result['rejection_reason_name'] . '</td></tr>';
    }

    $html .= '<tr><td colspan="3"></td></tr>';
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
    if (!empty($approvedBySignaturePath) && file_exists($approvedBySignaturePath)) {
        $html .= '<td style="line-height:11px;font-size:11px;text-align:left;"><img src="' . $approvedBySignaturePath . '" style="width:70px;" /></td>';
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
    if ($general->isLISInstance() && $result['data_sync'] == 0) {
        $generatedAtTestingLab = ' | ' . _translate("Report generated at Testing Lab");
    } else {
        $generatedAtTestingLab = "";
    }
    $html .= '<tr>';
    $html .= '<td style="font-size:10px;text-align:left;">Printed on : ' . $printDate . '&nbsp;&nbsp;' . $printDateTime . $generatedAtTestingLab . '</td>';
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
        if (isset($arr['eid_report_qr_code']) && $arr['eid_report_qr_code'] == 'yes' && !empty($general->getRemoteURL())) {
            $keyFromGlobalConfig = $general->getGlobalConfig('key');
            if (!empty($keyFromGlobalConfig)) {
                $encryptedString = CommonService::encrypt($result['unique_id'], base64_decode((string) $keyFromGlobalConfig));
                $remoteURL = $general->getRemoteURL();
                $pdf->write2DBarcode($remoteURL . '/eid/results/view.php?q=' . $encryptedString, 'QRCODE,H', 150, 170, 30, 30, [], 'N');
            }
        }
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
        $action = $_SESSION['userName'] . ' print the test result with child code ' . $result['child_id'];
        $resource = 'print-test-result';
        $data = array(
            'event_type' => $eventType,
            'action' => $action,
            'resource' => $resource,
            'date_time' => DateUtility::getCurrentDateTime()
        );
        $db->insert($tableName1, $data);
        //Update print datetime in VL tbl.
        $vlQuery = "SELECT result_printed_datetime FROM form_eid as vl WHERE vl.eid_id ='" . $result['eid_id'] . "'";
        $vlResult = $db->query($vlQuery);
        if ($vlResult[0]['result_printed_datetime'] == null || trim((string) $vlResult[0]['result_printed_datetime']) == '' || $vlResult[0]['result_printed_datetime'] == '0000-00-00 00:00:00') {
            $db->where('eid_id', $result['eid_id']);
            $db->update($tableName2, array('result_printed_datetime' => DateUtility::getCurrentDateTime()));
        }
    }
}
