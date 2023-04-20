<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ob_start();

use App\Models\Covid19;
use App\Models\General;
use App\Models\Users;
use App\Utilities\DateUtils;
use setasign\Fpdi\Tcpdf\Fpdi;

ini_set('memory_limit', -1);
ini_set('max_execution_time', -1);

$tableName1 = "activity_log";
$tableName2 = "form_covid19";
$general = new General();
$users = new Users();
$covid19Obj = new Covid19();

$arr = $general->getGlobalConfig();
$sc = $general->getSystemConfig();
$systemConfig = array_merge($sc, SYSTEM_CONFIG);

if (isset($arr['default_time_zone']) && $arr['default_time_zone'] != '') {
	date_default_timezone_set($arr['default_time_zone']);
} else {
	date_default_timezone_set(!empty(date_default_timezone_get()) ?  date_default_timezone_get() : "UTC");
}
//set mField Array
$mFieldArray = array();
if (isset($arr['r_mandatory_fields']) && trim($arr['r_mandatory_fields']) != '') {
	$mFieldArray = explode(',', $arr['r_mandatory_fields']);
}

//set query
$allQuery = $_SESSION['covid19PrintQuery'];
if (isset($_POST['id']) && trim($_POST['id']) != '') {

	$searchQuery = "SELECT vl.*,f.*,
				l.facility_name as labName,
				l.facility_emails as labEmail,
				l.address as labAddress,
				l.facility_mobile_numbers as labPhone,
				l.facility_state as labState,
				l.facility_district as labCounty,
				l.facility_logo as facilityLogo,
				l.report_format as reportFormat,
				l.header_text as labHeaderText,
				rip.i_partner_name,
				rsrr.rejection_reason_name ,
				u_d.user_name as reviewedBy,
				a_u_d.user_name as approvedBy,
				rfs.funding_source_name,
				c.iso_name as nationality,
				rst.sample_name,
				vl.data_sync as dataSync,
				testres.test_reason_name as reasonForTesting
				FROM form_covid19 as vl
				LEFT JOIN r_countries as c ON vl.patient_nationality=c.id
				LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
				LEFT JOIN facility_details as l ON l.facility_id=vl.lab_id 
				LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
				LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
				LEFT JOIN r_covid19_test_reasons as testres ON testres.test_reason_id=vl.reason_for_covid19_test 
				LEFT JOIN r_covid19_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection 
				LEFT JOIN r_implementation_partners as rip ON rip.i_partner_id=vl.implementing_partner
				LEFT JOIN r_funding_sources as rfs ON rfs.funding_source_id=vl.funding_source 
				LEFT JOIN r_covid19_sample_type as rst ON rst.sample_id=vl.specimen_type 
				WHERE vl.covid19_id IN(" . $_POST['id'] . ")";
} else {
	$searchQuery = $allQuery;
}
//echo($searchQuery);die;
$requestResult = $db->query($searchQuery);
/* Test Results */
if (isset($_POST['type']) && $_POST['type'] == "qr") {
	try {
		$general->trackQrViewPage('covid19', $requestResult[0]['covid19_id'], $requestResult[0]['sample_code']);
	} catch (Exception $exc) {
		error_log($exc->getMessage());
		error_log($exc->getTraceAsString());
	}
}

$_SESSION['nbPages'] = sizeof($requestResult);
$_SESSION['aliasPage'] = 1;
//print_r($requestResult);die;
//header and footer
class MYPDF extends TCPDF
{
	public $logo;
	public $text;
	public $lab;
	public $htitle;
	public $labFacilityId;
	public $formId;
	public $facilityInfo;
	public $resultPrintedDate;
	public $systemConfig;
	public $dataSync;


	//Page header
	public function setHeading($logo, $text, $lab, $title = null, $labFacilityId = null, $formId = null, $facilityInfo = array(), $resultPrintedDate = null, $dataSync = null, $systemConfig = null)
	{
		$this->logo = $logo;
		$this->text = $text;
		$this->lab = $lab;
		$this->htitle = $title;
		$this->labFacilityId = $labFacilityId;
		$this->formId = $formId;
		$this->facilityInfo = $facilityInfo;
		$this->resultPrintedDate = $resultPrintedDate;
		$this->systemConfig = $systemConfig;
		$this->dataSync = $dataSync;
	}
	public function imageExists($filePath)
	{
		return (!empty($filePath) && file_exists($filePath) && !is_dir($filePath) && filesize($filePath) > 0 && false !== getimagesize($filePath));
	}
	//Page header
	public function Header()
	{
		// Logo

		if ($this->htitle != '') {
			if (trim($this->logo) != '') {
				if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo)) {
					$imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo;
					if ($this->formId == 3) {
						$this->Image($imageFilePath, 10, 5, 25, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
					} else {
						$this->Image($imageFilePath, 95, 5, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
					}
				}
			}
			if ($this->formId == 3) {
				$this->SetFont('helvetica', 'B', 16);
				$this->writeHTMLCell(0, 0, 10, 03, $this->text, 0, 0, 0, true, 'C', true);
				if (trim($this->lab) != '') {
					$this->SetFont('helvetica', '', 10);
					$this->writeHTMLCell(0, 0, 10, 10, strtoupper($this->lab), 0, 0, 0, true, 'C', true);
				}
				$this->SetFont('helvetica', 'b', 10);
				$this->writeHTMLCell(0, 0, 10, 18, 'Département de Virologie', 0, 0, 0, true, 'C', true);
				$this->SetFont('helvetica', 'u', 10);
				$this->writeHTMLCell(0, 0, 10, 25, 'Laboratoire National de Reference pour la Grippe et les Virus Respiratoires', 0, 0, 0, true, 'C', true);
				$this->SetFont('helvetica', 'b', 12);
				$this->writeHTMLCell(0, 0, 10, 33, 'RESULTATS DE LABORATOIRE DES ECHANTIONS RESPIRATOIRES', 0, 0, 0, true, 'C', true);
				$this->SetFont('helvetica', 'u', 10);
				$this->writeHTMLCell(0, 0, 10, 40, 'TESTES AU COVID-19 PAR RT-PCR en temps réel N°', 0, 0, 0, true, 'C', true);
				$this->writeHTMLCell(0, 0, 15, 48, '<hr>', 0, 0, 0, true, 'C', true);
			} else {
				$this->SetFont('helvetica', 'B', 16);
				$this->writeHTMLCell(0, 0, 10, 18, $this->text, 0, 0, 0, true, 'C', true);
				if (trim($this->lab) != '') {
					$this->SetFont('helvetica', '', 10);
					$this->writeHTMLCell(0, 0, 10, 25, strtoupper($this->lab), 0, 0, 0, true, 'C', true);
				}
				$this->SetFont('helvetica', '', 12);
				$this->writeHTMLCell(0, 0, 10, 30, 'COVID-19 TEST - PATIENT REPORT', 0, 0, 0, true, 'C', true);
				$this->writeHTMLCell(0, 0, 15, 38, '<hr>', 0, 0, 0, true, 'C', true);
			}
		} else {
			if (trim($this->logo) != '') {
				if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facility-logo" . DIRECTORY_SEPARATOR . $this->labFacilityId . DIRECTORY_SEPARATOR . $this->logo)) {
					$imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'facility-logo' . DIRECTORY_SEPARATOR . $this->labFacilityId . DIRECTORY_SEPARATOR . $this->logo;
					$this->Image($imageFilePath, 16, 13, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
				} else if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo)) {
					$imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $this->logo;
					$this->Image($imageFilePath, 20, 13, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
			}
			if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . 'drc-logo.png')) {
				$imageFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . 'drc-logo.png';
				$this->Image($imageFilePath, 180, 13, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
			}

			// $this->SetFont('helvetica', 'B', 7);
			// $this->writeHTMLCell(30,0,16,28,$this->text, 0, 0, 0, true, 'A', true);(this two lines comment out for drc)
			$this->SetFont('helvetica', '', 14);
			$this->writeHTMLCell(0, 0, 10, 9, 'MINISTERE DE LA SANTE PUBLIQUE', 0, 0, 0, true, 'C', true);
			if ($this->text != '') {
				$this->SetFont('helvetica', '', 12);
				//        $this->writeHTMLCell(0,0,10,16,'PROGRAMME NATIONAL DE LUTTE CONTRE LE SIDA ET IST', 0, 0, 0, true, 'C', true);
				$this->writeHTMLCell(0, 0, 10, 16, strtoupper($this->text), 0, 0, 0, true, 'C', true);
				$thirdHeading = '23';
				$fourthHeading = '28';
				$hrLine = '36';
				$marginTop = '14';
			} else {
				$thirdHeading = '17';
				$fourthHeading = '23';
				$hrLine = '30';
				$marginTop = '9';
			}
			if (trim($this->lab) != '') {
				$this->SetFont('helvetica', '', 9);
				$this->writeHTMLCell(0, 0, 10, $thirdHeading, strtoupper($this->lab), 0, 0, 0, true, 'C', true);
			}
			$this->SetFont('helvetica', '', 12);
			$this->writeHTMLCell(0, 0, 10, $fourthHeading, 'RESULTATS CHARGE VIRALE', 0, 0, 0, true, 'C', true);
			$this->writeHTMLCell(0, 0, 15, $hrLine, '<hr>', 0, 0, 0, true, 'C', true);
		}
	}

	// Page footer
	public function Footer()
	{
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', '', 8);
		if ($this->systemConfig['sc_user_type'] == 'vluser' && $this->dataSync == 0 && ($this->formId == 1 || $this->formId == 3)) {
			$generatedAtTestingLab = " | " . _("Report generated at Testing Lab");
		} else {
			$generatedAtTestingLab = "";
		}
		// Page number
		$this->Cell(0, 10, 'Page' . $_SESSION['aliasPage'] . '/' . $_SESSION['nbPages'] . $generatedAtTestingLab, 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

class PDF_Rotate extends FPDI
{

	var $angle = 0;

	function Rotate($angle, $x = -1, $y = -1)
	{
		if ($x == -1)
			$x = $this->x;
		if ($y == -1)
			$y = $this->y;
		if ($this->angle != 0)
			$this->_out('Q');
		$this->angle = $angle;
		if ($angle != 0) {
			$angle *= M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	function _endpage()
	{
		if ($this->angle != 0) {
			$this->angle = 0;
			$this->_out('Q');
		}
		parent::_endpage();
	}
}

class Watermark extends PDF_Rotate
{

	var $_tplIdx;

	function Header()
	{
		global $fullPathToFile;

		//Put the watermark
		$this->SetFont('helvetica', 'B', 50);
		$this->SetTextColor(148, 162, 204);
		$this->RotatedText(67, 109, 'DRAFT', 45);

		if (is_null($this->_tplIdx)) {
			// THIS IS WHERE YOU GET THE NUMBER OF PAGES
			$this->numPages = $this->setSourceFile($fullPathToFile);
			$this->_tplIdx = $this->importPage(1);
		}
		$this->useTemplate($this->_tplIdx, 0, 0, 200);
	}

	function RotatedText($x, $y, $txt, $angle)
	{
		//Text rotated around its origin
		$this->Rotate($angle, $x, $y);
		$this->Text($x, $y, $txt);
		$this->Rotate(0);
		//$this->SetAlpha(0.7);
	}
}
class Pdf_concat extends FPDI
{
	var $files = array();
	function setFiles($files)
	{
		$this->files = $files;
	}
	function concat()
	{
		foreach ($this->files as $file) {
			$pagecount = $this->setSourceFile($file);
			for ($i = 1; $i <= $pagecount; $i++) {
				$tplidx = $this->ImportPage($i);
				$s = $this->getTemplatesize($tplidx);
				$this->AddPage('P', array($s['w'], $s['h']));
				$this->useTemplate($tplidx);
			}
		}
	}
}

$fileArray = array(
	1 => 'pdf/result-pdf-ssudan.php',
	2 => 'pdf/result-pdf-sierraleone.php',
	3 => 'pdf/result-pdf-drc.php',
	4 => 'pdf/result-pdf-zam.php',
	5 => 'pdf/result-pdf-png.php',
	6 => 'pdf/result-pdf-who.php',
	7 => 'pdf/result-pdf-rwanda.php',
	8 => 'pdf/result-pdf-angola.php',
);

$resultFilename = '';
if (sizeof($requestResult) > 0) {
	$_SESSION['rVal'] = $general->generateRandomString(6);
	$pathFront = (TEMP_PATH . DIRECTORY_SEPARATOR .  $_SESSION['rVal']);
	if (!file_exists($pathFront) && !is_dir($pathFront)) {
		mkdir(TEMP_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal'], 0777, true);
		$pathFront = realpath(TEMP_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']);
	}
	$pages = array();
	$page = 1;
	foreach ($requestResult as $result) {
		//set print time
		if (isset($result['result_printed_datetime']) && $result['result_printed_datetime'] != "") {
			$printedTime = date('Y-m-d H:i:s', strtotime($result['result_printed_datetime']));
		} else {
			$printedTime = DateUtils::getCurrentDateTime();
		}
		$expStr = explode(" ", $printedTime);
		$printDate = DateUtils::humanReadableDateFormat($expStr[0]);
		$printDateTime = $expStr[1];
		$covid19Obj = new Covid19();
		$covid19Results = $covid19Obj->getCovid19Results();
		$countryFormId = $general->getGlobalConfig('vl_form');

		$covid19TestQuery = "SELECT * from covid19_tests where covid19_id= " . $result['covid19_id'] . " ORDER BY test_id ASC";
		$covid19TestInfo = $db->rawQuery($covid19TestQuery);
		// Lab Details
		$labQuery = "SELECT * from facility_details where facility_id= " . $result['lab_id'] . " LIMIT 1";
		$labInfo = $db->rawQueryOne($labQuery);

		$facilityQuery = "SELECT * from form_covid19 as c19 INNER JOIN facility_details as fd ON c19.facility_id=fd.facility_id where covid19_id= " . $result['covid19_id'] . " GROUP BY fd.facility_id LIMIT 1";
		$facilityInfo = $db->rawQueryOne($facilityQuery);
		// echo "<pre>";print_r($covid19TestInfo);die;

		$patientFname = ($general->crypto('doNothing', $result['patient_name'], $result['patient_id']));
		$patientLname = ($general->crypto('doNothing', $result['patient_surname'], $result['patient_id']));

		$signQuery = "SELECT * from lab_report_signatories where lab_id=? AND test_types like '%covid19%' AND signatory_status like 'active' ORDER BY display_order ASC";
		$signResults = $db->rawQuery($signQuery, array($result['lab_id']));
		$currentDateTime = DateUtils::getCurrentDateTime();
		$_SESSION['aliasPage'] = $page;
		if (!isset($result['labName'])) {
			$result['labName'] = '';
		}
		$draftTextShow = false;
		//Set watermark text
		for ($m = 0; $m < count($mFieldArray); $m++) {
			if (!isset($result[$mFieldArray[$m]]) || trim($result[$mFieldArray[$m]]) == '' || $result[$mFieldArray[$m]] == null || $result[$mFieldArray[$m]] == '0000-00-00 00:00:00') {
				$draftTextShow = true;
				break;
			}
		}

		$selectedReportFormats = array();
		if (isset($result['reportFormat']) && $result['reportFormat'] != "") {
			$selectedReportFormats = json_decode($result['reportFormat'], true);
		}
		if (!empty($selectedReportFormats) && !empty($selectedReportFormats['covid19'])) {
			require($selectedReportFormats['covid19']);
		} else {
			require($fileArray[$arr['vl_form']]);
		}
	}
	if (!empty($pages)) {
		$resultPdf = new Pdf_concat();
		$resultPdf->setFiles($pages);
		$resultPdf->setPrintHeader(false);
		$resultPdf->setPrintFooter(false);
		$resultPdf->concat();
		$resultFilename = 'COVID-19-Test-result-' . date('d-M-Y-H-i-s') . "-" . $general->generateRandomString(6) . '.pdf';
		$resultPdf->Output(TEMP_PATH . DIRECTORY_SEPARATOR . $resultFilename, "F");
		$general->removeDirectory($pathFront);
		unset($_SESSION['rVal']);
	}
}
echo base64_encode(TEMP_PATH . DIRECTORY_SEPARATOR . $resultFilename);
