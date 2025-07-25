<?php

use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Helpers\PdfWatermarkHelper;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Helpers\ResultPDFHelpers\Covid19ResultPDFHelper;

if (!class_exists('DRCCovid19PDF3')) {

    class DRCCovid19PDF3 extends Covid19ResultPDFHelper
    {
        //Page header
        public function Header()
        {
            // Logo
            if (!empty($this->htitle) && trim($this->htitle) != '') {

                if (!empty($this->logo) && trim($this->logo) != '') {
                    // Check facility have the logo
                    if (!empty($this->facilityInfo) && !empty($this->facilityInfo['facility_logo']) && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_logo'])) {
                        $imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_logo'];
                        $this->Image($imageFilePath, 10, 5, 25, '', '', '', 'T');
                    } else {
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo)) {
                            $imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo;
                            $this->Image($imageFilePath, 10, 5, 25, '', '', '', 'T');
                        }
                    }
                }
                if (!empty($this->logo) && trim($this->logo) != '') {
                    if (!empty($this->facilityInfo) && !empty($this->facilityInfo['facility_logo']) && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_logo'])) {
                        $imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_logo'];
                        $this->Image($imageFilePath, 175, 5, 25, '', '', '', 'T');
                    } else {
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo)) {
                            $imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo;
                            $this->Image($imageFilePath, 175, 5, 25, '', '', '', 'T');
                        }
                    }
                }
                $this->SetFont('helvetica', 'B', 12);
                $this->writeHTMLCell(0, 0, 0, 5, 'REPUBLIQUE DEMOCRATIQUE DU CONGO', 0, 0, 0, true, 'C');
                $this->SetFont('helvetica', 'B', 10);
                $this->writeHTMLCell(0, 0, 0, 11, $this->text, 0, 0, 0, true, 'C');
                //if (!empty($this->lab) && trim($this->lab) != '') {
                $this->SetFont('helvetica', 'B', 11);
                $this->writeHTMLCell(0, 0, 0, 16, "Archidiocèse de Kinshasa", 0, 0, 0, true, 'C');
                //}
                $this->SetFont('helvetica', '', 10);
                $this->SetTextColor(0, 0, 250);
                $this->writeHTMLCell(0, 0, 0, 22, '<em>Hôpital Saint Joseph</em>', 0, 0, 0, true, 'C');
                $this->SetTextColor(0, 0, 0);
                //$this->SetFont('helvetica', 'U', 11);
                //$this->writeHTMLCell(0, 0, 0, 27, 'Laboratoire National de Référence Pour la Grippe et les virus respiratoires', 0, 0, 0, true, 'C', true);

                $this->SetFont('helvetica', 'B', 10);
                $this->writeHTMLCell(0, 0, 0, 27, 'RÉSULTATS DES LABORATOIRES DES ECHANTILLONS RESPIRATOIRES', 0, 0, 0, true, 'C');
                $this->SetFont('helvetica', 'U', 10);
                $this->writeHTMLCell(0, 0, 0, 32, 'TESTES AU nCOV-19 PAR RT-PCR en temps réel', 0, 0, 0, true, 'C');

                $this->writeHTMLCell(0, 0, 10, 38, '<hr>', 0, 0, 0, true, 'C');

                // Define the path to the image that you want to use as watermark.
                $img_file = UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . "actual-" . $this->logo;
                if (!empty($this->logo) && file_exists($img_file)) {
                } else if (!empty($this->logo) && UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo) {
                    if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo)) {
                        $img_file = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo;
                    }
                } else {
                    $img_file = "";
                }
                // Render the image
                if ($img_file != "") {
                    $this->SetAlpha(0.1);
                    $this->Image($img_file, 20, 75, 150, null, '', '', '', false, 300, 'M');
                }
                $stamp = "";
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . 'stamps' . DIRECTORY_SEPARATOR . 'stamp-1.png')) {
                    $stamp = UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->facilityInfo['facility_id'] . DIRECTORY_SEPARATOR . 'stamps' . DIRECTORY_SEPARATOR . 'stamp-1.png';
                }
                if ($stamp != "") {
                    $this->SetAlpha(0.6);
                    $this->Image($stamp, 50, 160, 50, null);
                    $this->Image($stamp, 145, 160, 50, null);
                }
            }
        }

        // Page footer
        public function Footer()
        {
            $this->writeHTML("<hr>");
            // Set font
            $this->SetFont('helvetica', 'I', 8);
            setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
            if ($this->commonService->isLISInstance() && $this->dataSync == 0) {
                $generatedAtTestingLab = " | " . _translate("Report generated at Testing Lab");
            } else {
                $generatedAtTestingLab = "";
            }

            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::FULL, $_SESSION['APP_TIMEZONE'], IntlDateFormatter::GREGORIAN, "EEEE dd MMMM, Y");
            $this->writeHTML($formatter->format(strtotime((string) $this->resultPrintedDate)) . ' ' . $generatedAtTestingLab . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }
}
$usersService = ContainerRegistry::get(UsersService::class);

// create new PDF document
$pdf = new DRCCovid19PDF3(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $result['lab_id'] . DIRECTORY_SEPARATOR . $result['facilityLogo'])) {
    $logoPrintInPdf = $result['facilityLogo'];
} else {
    $logoPrintInPdf = $arr['logo'];
}

$resultPrintedDate = '';
$resultPrintedTime = '';
if (isset($result['result_printed_datetime']) && trim((string) $result['result_printed_datetime']) != '' && $result['result_dispatched_datetime'] != '0000-00-00 00:00:00') {
    $expStr = explode(" ", (string) $result['result_printed_datetime']);
    $resultPrintedDate = DateUtility::humanReadableDateFormat($expStr[0]);
    $resultPrintedTime = $expStr[1];
} else {
    $expStr = explode(" ", $currentDateTime);
    $resultPrintedDate = DateUtility::humanReadableDateFormat($expStr[0]);
    $resultPrintedTime = $expStr[1];
}
$pdf->setHeading($logoPrintInPdf, $arr['header'], $result['labName'], $title = 'COVID-19 PATIENT REPORT', null, 3, $labInfo, $currentDateTime, $result['dataSync'], $systemConfig);
// set document information
$pdf->SetCreator('VLSM');
$pdf->SetTitle('Covid-19 Rapport du patient');
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
$pdf->SetFooterMargin("20");

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
} else if (isset($result['sample_registered_at_lab']) && trim((string) $result['sample_registered_at_lab']) != '' && $result['sample_registered_at_lab'] != '0000-00-00 00:00:00') {
    $expStr = explode(" ", (string) $result['sample_registered_at_lab']);
    $sampleReceivedDate = DateUtility::humanReadableDateFormat($expStr[0]);
    $sampleReceivedTime = $expStr[1];
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

$userRes = [];
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
$vlResult = '';
$smileyContent = '';
$showMessage = '';
$tndMessage = '';
$messageTextSize = '12px';
if ($result['result'] != null && trim((string) $result['result']) != '') {
    $resultType = is_numeric($result['result']);
    if ($result['result'] == 'positive') {
        $vlResult = $result['result'];
        $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/smiley_frown.png" alt="smile_face"/>';
    } else if ($result['result'] == 'negative') {
        $vlResult = $result['result'];
        $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/smiley_smile.png" alt="smile_face"/>';
    } else if ($result['result'] == 'indeterminate') {
        $vlResult = $result['result'];
        $smileyContent = '';
    }
}
if ($result['result_status'] == SAMPLE_STATUS\REJECTED) {
    $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="/assets/img/cross.png" alt="rejected"/>';
}
if (isset($arr['show_smiley']) && trim((string) $arr['show_smiley']) == "no") {
    $smileyContent = '';
}
$html = '<br><br><br>';
$html .= '<table style="padding:0px 2px 2px 2px;">';
$html .= '<tr>';
$html .= '<td>';
$html .= '<table style="padding:10px;">';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Labid<br><span style="font-size:8;font-weight:normal;">(Lab ID)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . ($result['sample_code']) . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Province<br><span style="font-size:8;font-weight:normal;">(Province/State)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . ($result['patient_province']) . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Zone de santé<br><span style="font-size:8;font-weight:normal;">(County/District)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . ($result['patient_district']) . '</td>';
$html .= '</tr>';

$patientFname = ($general->crypto('doNothing', $result['patient_name'], $result['patient_id']));
$patientLname = ($general->crypto('doNothing', $result['patient_surname'], $result['patient_id']));
if (!empty($result['is_encrypted']) && $result['is_encrypted'] == 'yes') {
    $key = (string) $general->getGlobalConfig('key');
    $result['patient_id'] = $general->crypto('decrypt', $result['patient_id'], $key);
    $patientFname = $general->crypto('decrypt', $patientFname, $key);
    $patientLname = $general->crypto('decrypt', $patientLname, $key);
}
$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Nom de Malade<br><span style="font-size:8;font-weight:normal;">(Patient Name)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $patientLname . ' ' . $patientFname . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Age<br><span style="font-size:8;font-weight:normal;">(Age)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' .  (!empty($age) ? $age . ' ans' : '') . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Sexe<br><span style="font-size:8;font-weight:normal;">(Sex)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . (str_replace("_", " ", (string) $result['patient_gender'])) . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Adresse<br><span style="font-size:8;font-weight:normal;">(Address)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $result['patient_address'] . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Commune<br><span style="font-size:8;font-weight:normal;">(Patient District)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $result['patient_zone'] . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Téléphone<br><span style="font-size:8;font-weight:normal;">(Phone Number)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $result['patient_phone_number'] . '</td>';
$html .= '</tr>';

// $html .= '<tr>';
// $html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Type de Cas</td>';
// $html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
// $html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $result['reasonForTesting'] . '</td>';
// $html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Structure Sanitaire<br><span style="font-size:8;font-weight:normal;">(Facility Name)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . ($result['facility_name']) . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Date de Prélévement<br><span style="font-size:8;font-weight:normal;">(Sample Collection Date)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $result['sample_collection_date'] . " " . $sampleCollectionTime . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="20%" style="line-height:10px;font-size:11px;text-align:left;font-weight:bold;">Date de Réception<br><span style="font-size:8;font-weight:normal;">(Sample Received Date)</span></td>';
$html .= '<td width="5%" style="line-height:10px;font-size:11px;text-align:center;">:</td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;">' . $sampleReceivedDate . " " . $sampleReceivedTime . '</td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;" colspan="2"><strong>Resultats SARS-CoV-2 &nbsp;&nbsp;:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $covid19Results[$result['result']] . '</strong><br><span style="font-size:8;font-weight:normal;">(Result)</span></td>';
$html .= '<td width="50%" style="line-height:10px;font-size:11px;text-align:left;"><strong>Date de Sortie Résultats &nbsp;&nbsp;:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $resultPrintedDate . '&nbsp;&nbsp;' . $resultPrintedTime . '<br><span style="font-size:8;font-weight:normal;">(Result Returned On)</span></td>';
$html .= '</tr>';

$html .= '<tr>';
$html .= '<td width="100%" style="line-height:10px;font-size:11px;text-align:center;" colspan="3">
            <br><br><strong>Fait à Kinshasa, le: </strong>' . DateUtility::humanReadableDateFormat($result['result_approved_datetime']) .
    '<br><span style="font-size:8;font-weight:normal;">(Done in Kinshasa, on)</span></td>';
$html .= '</tr>';


if (empty($result['lab_manager'])) {
    $facilitiesService = ContainerRegistry::get(FacilitiesService::class);
    $labDetails = $facilitiesService->getFacilityById($result['lab_id']);
    if (!empty($labDetails['contact_person'])) {
        $result['lab_manager'] = $labDetails['contact_person'];
    }
}
$labManager = "";
if (!empty($result['lab_manager'])) {
    $labManagerRes = $usersService->getUserByID($result['lab_manager'], 'user_name');
    if ($labManagerRes) {
        $labManager = $labManagerRes['user_name'];
    }
}

$html .= '<tr>';
$html .= '<td colspan="3" style="line-height:12px;font-size:12px;text-align:center;"><br><br><strong>' . $labManager . '</strong><br>Chef de laboratoire de l&lsquo;hôpital Saint Joseph<br><span style="font-size:8;font-weight:normal;">(Lab Manager)</span></td>';
$html .= '</tr>';


/* $html .= '<tr>';
$html .= '<td width="100%" style="line-height:20px;border-bottom:2px solid #d3d3d3;" colspan="3"></td>';
$html .= '</tr>';
$html .= '<tr>';
$html .= '<td width="100%" style="line-height:10px;font-size:11px;text-align:left;color:#545252;" colspan="3"><br><br>' . str_replace($real, $french, $resultPrintedDate) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<em>Department de virologie</em></td>';
$html .= '</tr>'; */

$html .= '</table>';
$html .= '</td></tr></table>';

if ($result['result'] != '' || ($result['result'] == '' && $result['result_status'] == SAMPLE_STATUS\REJECTED)) {
    $viewId = CommonService::encryptViewQRCode($result['unique_id']);
    $pdf->writeHTML($html);

    if (isset($arr['covid19_report_qr_code']) && $arr['covid19_report_qr_code'] == 'yes' && !empty($general->getRemoteURL())) {
        $remoteURL = $general->getRemoteURL();
        $pdf->write2DBarcode($remoteURL . '/covid-19/results/view.php?q=' . urlencode($viewId), 'QRCODE,H', 170, 60, 100, 100, [], 'N');
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
    $action = ($_SESSION['userName'] ?: 'System') . ' generated the test result PDF with Patient ID/Code ' . $result['patient_id'];
    $resource = 'print-test-result';
    $data = array(
        'event_type' => $eventType,
        'action' => $action,
        'resource' => $resource,
        'date_time' => $currentDateTime
    );
    $db->insert($tableName1, $data);
    //Update print datetime in VL tbl.
    $vlQuery = "SELECT result_printed_datetime FROM form_covid19 as vl WHERE vl.covid19_id ='" . $result['covid19_id'] . "'";
    $vlResult = $db->query($vlQuery);
    if ($vlResult[0]['result_printed_datetime'] == null || trim((string) $vlResult[0]['result_printed_datetime']) == '' || $vlResult[0]['result_printed_datetime'] == '0000-00-00 00:00:00') {
        $db->where('covid19_id', $result['covid19_id']);
        $db->update($tableName2, array('result_printed_datetime' => $currentDateTime, 'result_dispatched_datetime' => $currentDateTime));
    }
}
