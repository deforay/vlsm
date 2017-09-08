<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include ('../includes/tcpdf/tcpdf.php');
include ('../includes/fpdi/fpdi.php');
include ('../includes/fpdf/fpdf.php');
define('UPLOAD_PATH','../uploads');
$tableName1="activity_log";
$tableName2="vl_request_form";

$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$country = $arr['vl_form'];
if(isset($arr['default_time_zone']) && $arr['default_time_zone']!=''){
  date_default_timezone_set($arr['default_time_zone']);
}else{
  date_default_timezone_set("Europe/London");
}
if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
   $s_t_date = explode("to", $_POST['reportedDate']);
   if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
     $start_date = $general->dateFormat(trim($s_t_date[0]));
   }
   if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
     $end_date = $general->dateFormat(trim($s_t_date[1]));
   }
}
if(isset($_POST['lab']) && $_POST['lab']!= '' && count(array_filter($_POST['lab']))> 0){
    $lab = implode(',',$_POST['lab']);
    $vlLabQuery="SELECT * FROM facility_details where facility_id IN ($lab) AND status='active'";
    $vlLabResult = $db->rawQuery($vlLabQuery);
}else{
    $vlLabQuery="SELECT * FROM facility_details where facility_type = 2 AND status='active'";
    $vlLabResult = $db->rawQuery($vlLabQuery);
}
//header and footer
//Pdf code start
    // create new PDF document
  class MYPDF extends TCPDF {
  
     //Page header
      public function setHeading($title,$logo,$text,$lab,$report_date) {
        $this->logo = $logo;
        $this->text = $text;
        $this->lab = $lab;
        $this->title = $title;
        $this->report_date = $report_date;
      }
      //Page header
     public function Header() {
         // Logo
         //$image_file = K_PATH_IMAGES.'logo_example.jpg';
         //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
         // Set font
         if(trim($this->logo)!=''){
             if (file_exists('../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo)) {
               $image_file = '../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo;
               $this->Image($image_file,20, 13, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
             }
         }
         $this->SetFont('helvetica', 'B', 7);
         $this->writeHTMLCell(30,0,16,28,$this->text, 0, 0, 0, true, 'A', true);
         $this->SetFont('helvetica', '', 18);
         $this->writeHTMLCell(0,0,10,18,$this->title, 0, 0, 0, true, 'C', true);
         if(trim($this->lab)!= ''){
           $this->SetFont('helvetica', '', 9);
           $this->writeHTMLCell(0,0,10,26,strtoupper($this->lab), 0, 0, 0, true, 'C', true);
         }
         $this->SetFont('helvetica', '', 9);
         $this->writeHTMLCell(0,0,0,26,'Report Date : '.$this->report_date, 0, 0, 0, true, 'R', true);
         $this->writeHTMLCell(0,0,15,36,'<hr>', 0, 0, 0, true, 'C', true);
     }
  
      // Page footer
      public function Footer() {
          // Position at 15 mm from bottom
          $this->SetY(-15);
          // Set font
          $this->SetFont('helvetica', '', 8);
          // Page number
          $this->Cell(0, 10, 'Page'.$_SESSION['aliasPage'].'/'.$_SESSION['nbPages'], 0, false, 'C', 0, '', 0,false, 'T', 'M');
      }
  }
   
  class Pdf_concat extends FPDI {
       var $files = array();
   
       function setFiles($files) {
           $this->files = $files;
       }
   
       function concat() {
           foreach($this->files AS $file) {
                $pagecount = $this->setSourceFile($file);
                for ($i = 1; $i <= $pagecount; $i++) {
                     $tplidx = $this->ImportPage($i);
                     $s = $this->getTemplatesize($tplidx);
                     $this->AddPage('L', array($s['w'], $s['h']));
                     $this->useTemplate($tplidx);
                }
           }
       }
  }
  
  if(sizeof($vlLabResult)> 0){
    $_SESSION['rVal'] = $general->generateRandomString(6);
    if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal'])) {
      mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']);
    }
    $pathFront = realpath('../uploads/'.$_SESSION['rVal'].'/');
    $pages = array();
    $page = 1;
    $_SESSION['nbPages'] = (count($vlLabResult)+1);
    foreach($vlLabResult as $vlLab){
        $_SESSION['aliasPage'] = $page;
        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
      $pdf->setHeading('VIRAL LOAD STATISTICS',$arr['logo'],$arr['header'],$vlLab['facility_name'],$_POST['reportedDate']);
      $pdf->setPageOrientation('L');
      // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('VIRAL LOAD LAB WEEKLY REPORT');
        //$pdf->SetSubject('TCPDF Tutorial');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '',PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '',PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP+14,PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $pdf->SetFont('helvetica', '', 8);
        $sQuery="SELECT
	
		 vl.facility_id,f.facility_code,f.facility_state,f.facility_district,f.facility_name,
		
		SUM(CASE
			 WHEN (result_status = 4) THEN 1
		             ELSE 0
		           END) AS rejections,

		SUM(CASE 
			 WHEN (patient_age_in_years <= 14 AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
		             ELSE 0
		           END) AS lt14lt1000, 
		SUM(CASE 
             WHEN (patient_age_in_years <= 14 AND result > 1000) THEN 1
             ELSE 0
           END) AS lt14gt1000,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='male') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS gt14lt1000M,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='male') AND result > 1000) THEN 1
             ELSE 0
           END) AS gt14gt1000M,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS gt14lt1000F,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND result > 1000) THEN 1
             ELSE 0
           END) AS gt14gt1000F,	
		SUM(CASE 
             WHEN ((is_patient_pregnant ='yes') OR (is_patient_breastfeeding ='yes') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS preglt1000,	
		SUM(CASE 
             WHEN ((is_patient_pregnant ='yes') OR (is_patient_breastfeeding ='yes') AND result > 1000) THEN 1
             ELSE 0
           END) AS preggt1000,           	           	
		SUM(CASE 
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS ult1000, 
		SUM(CASE 
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND result > 1000) THEN 1
             ELSE 0
           END) AS ugt1000,               
		SUM(CASE 
             WHEN ((result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS totalLessThan1000,     
		SUM(CASE 
             WHEN ((result > 1000)) THEN 1
             ELSE 0
           END) AS totalGreaterThan1000,
		COUNT(result) as total
		 FROM vl_request_form as vl RIGHT JOIN facility_details as f ON f.facility_id=vl.facility_id
       WHERE vl.lab_id = ".$vlLab['facility_id']." AND vl.vlsm_country_id = ".$country;
    if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
        if (trim($start_date) == trim($end_date)) {
          $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) = "'.$start_date.'"';
        }else{
          $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
        }
    }
    if(isset($_POST['searchData']) && trim($_POST['searchData'])!= ''){
        //$sQuery = $sQuery.' AND (f.facility_state LIKE "%'.$_POST['searchData'].'%" OR f.facility_district LIKE "%'.$_POST['searchData'].'%" OR f.facility_name LIKE "%'.$_POST['searchData'].'%")';
    }
    $sQuery = $sQuery.' GROUP BY vl.facility_id';
    $sResult = $db->rawQuery($sQuery);
    //error_log($sQuery);
      $pdf->AddPage();
      //Statistics pdf start
        $html = '';
            $html.='<table style="border:2px solid #f4f4f4;">';
             $html.='<thead>';
                $html.='<tr>';
		  $html.='<th rowspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Province/State</strong></th>';
		  $html.='<th rowspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>District/County</strong></th>';
		  $html.='<th rowspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Site Name</strong></th>';
                  $html.='<th rowspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>IPSL</strong></th>';
                  $html.='<th rowspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>No. of Rejections</strong></th>';
                  $html.='<th colspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Viral Load Results - Peds</strong></th>';
                  $html.='<th colspan="4" align="center" style="border:1px solid #f4f4f4;"><strong>Viral Load Results - Adults</strong></th>';
                  $html.='<th colspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Viral Load Results - Pregnant/Breastfeeding Women</strong></th>';
                  $html.='<th colspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Age/Sex Unknown</strong></th>';
                  $html.='<th colspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Totals</strong></th>';
                  $html.='<th rowspan="2" align="center" style="border:1px solid #f4f4f4;"><strong>Total Test per Clinic</strong></th>';
                $html.='</tr>';
		$html.='<tr>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&lt;= 14 yrs &lt;= 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&lt;= 14 yrs &gt; 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&gt; 14yrs Male &lt; 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&gt; 14yrs Male &gt; 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&gt; 14yrs Female &lt;= 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&gt; 14yrs  Female &gt; 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&lt;= 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&gt; 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Unknown Age/Sex &lt;= 1000ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Unknown Age/Sex &gt; 1000ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&lt;= 1000 cp/ml</strong></th>';
		  $html.='<th align="center" style="border:1px solid #f4f4f4;"> <strong>&gt; 1000 cp/ml</strong></th>';
		$html.='</tr>';
                $html.='</thead>';
                $html.='<tbody>';
                if(count($sResult) > 0){
                  foreach($sResult as $result){
                    $html.='<tr>';
                    $html.='<td style="min-height:20px;border:1px solid #f4f4f4;">'.ucwords($result['facility_state']).'</td>';
                    $html.='<td style="border:1px solid #f4f4f4;">'.ucwords($result['facility_district']).'</td>';
                    $html.='<td style="border:1px solid #f4f4f4;">'.ucwords($result['facility_name']).'</td>';
                    $html.='<td style="border:1px solid #f4f4f4;">'.$result['facility_code'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['rejections'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['lt14lt1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['lt14gt1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['gt14lt1000M'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['gt14gt1000M'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['gt14lt1000F'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['gt14gt1000F'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['preglt1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['preggt1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['ult1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['ugt1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['totalLessThan1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['totalGreaterThan1000'].'</td>';
                    $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$result['total'].'</td>';
                    $html.='</tr>';
                  }
                }else{
                  $html.='<tr>';
                  $html.='<td style="min-height:20px;border:1px solid #f4f4f4;"></td>';
                  $html.='<td style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='<td align="center" style="border:1px solid #f4f4f4;"></td>';
                  $html.='</tr>';
                }
                $html.='</tbody>';
            $html.='</table>';
          $pdf->writeHTML($html);
          $pdf->lastPage();
          $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
          $pdf->Output($filename,"F");
          $pages[] = $filename;
        $page++;
    }
    $_SESSION['aliasPage'] = $page;
    //Statistics pdf end
    if($page > 1){
      //Super lab performance pdf start
      // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //$pdf->setHeaderTemplateAutoreset(true);
        $pdf->setHeading('SUPER LAB PERFORMANCE REPORT',$arr['logo'],$arr['header'],'',$_POST['reportedDate']);
        $pdf->setPageOrientation('L');
      // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('VIRAL LOAD LAB WEEKLY REPORT');
        //$pdf->SetSubject('TCPDF Tutorial');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '',PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '',PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP+14,PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $pdf->SetFont('helvetica', '', 8);
        $pdf->AddPage();
        $html = '';
          $html.='<table style="border:2px solid #f4f4f4;">';
           $html.='<thead>';
              $html.='<tr>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Province/State</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>District/County </strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Super Lab Name </strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>IPSL</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Total Number of VL samples Received at Laboratory</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Total Number of Viral load tests done (inc failed tests)</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>No. of Samples Not Tested</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Assay Failure Rate(%)</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Average Result TAT (lab)</strong></th>';
                $html.='<th align="center" style="border:1px solid #f4f4f4;"><strong>Average Result TAT -Total (from sample  collection to results getting to the facility/hub)</strong></th>';
              $html.='</tr>';
          $html.='</thead>';
          $html.='<tbody>';
            foreach ($vlLabResult as $vlLab) {
             $sQuery="SELECT vl.vl_sample_id,vl.sample_collection_date,vl.sample_received_at_vl_lab_datetime,vl.sample_tested_datetime,vl.result_printed_datetime,vl.result,f.facility_name FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id WHERE vl.lab_id = '".$vlLab['facility_id']."' AND vl.vlsm_country_id = '".$country."'";
             if(isset($_POST['reportedDate']) && trim($_POST['reportedDate'])!= ''){
                if (trim($start_date) == trim($end_date)) {
                  $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) = "'.$start_date.'"';
                }else{
                  $sQuery = $sQuery.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
                }
             }
             if(isset($_POST['searchData']) && trim($_POST['searchData'])!= ''){
                $sQuery = $sQuery.' AND (f.facility_state LIKE "%'.$_POST['searchData'].'%" OR f.facility_district LIKE "%'.$_POST['searchData'].'%" OR f.facility_name LIKE "%'.$_POST['searchData'].'%")';
             }
             $sResult = $db->rawQuery($sQuery);
             $noOfSampleReceivedAtLab = array();
             $noOfSampleTested = array();
             $noOfSampleNotTested = array();
             $resultTat = array();
             $resultDTat = array();
             $assayFailures = array();
             foreach($sResult as $result){
               $sampleCollectionDate = '';
               $dateOfSampleReceivedAtTestingLab = '';
               $dateResultPrinted = '';
               if(trim($result['sample_collection_date'])!= '' && $result['sample_collection_date'] != NULL && $result['sample_collection_date'] != '0000-00-00 00:00:00'){
                  $sampleCollectionDate = $result['sample_collection_date'];
               }
               
               if(trim($result['sample_received_at_vl_lab_datetime'])!= '' && $result['sample_received_at_vl_lab_datetime'] != NULL && $result['sample_received_at_vl_lab_datetime'] != '0000-00-00 00:00:00'){
                  $dateOfSampleReceivedAtTestingLab = $result['sample_received_at_vl_lab_datetime'];
                  $noOfSampleReceivedAtLab[] = $result['vl_sample_id'];
               }
               
               if(trim($result['sample_tested_datetime'])!= '' && $result['sample_tested_datetime'] != NULL && $result['sample_tested_datetime'] != '0000-00-00 00:00:00'){
                  $noOfSampleTested[] = $result['vl_sample_id'];
               }else{
                  if(trim($result['sample_received_at_vl_lab_datetime'])!= '' && $result['sample_received_at_vl_lab_datetime'] != NULL && $result['sample_received_at_vl_lab_datetime'] != '0000-00-00 00:00:00'){
                     $noOfSampleNotTested[] = $result['vl_sample_id'];
                  }
               }
               
               if(trim($result['result_printed_datetime'])!= '' && $result['result_printed_datetime'] != NULL && $result['result_printed_datetime'] != '0000-00-00 00:00:00'){
                  $dateResultPrinted = $result['result_printed_datetime'];
               }
               
               if(trim($dateOfSampleReceivedAtTestingLab)!= '' && trim($dateResultPrinted)!= ''){
                  $date_result_printed = strtotime($dateResultPrinted);
                  $date_of_sample_received_at_testing_lab = strtotime($dateOfSampleReceivedAtTestingLab);
                  $daydiff = $date_result_printed - $date_of_sample_received_at_testing_lab;
                  $tat = (int)floor($daydiff / (60 * 60 * 24));
                  $resultTat[] = $tat;
               }
               if(trim($sampleCollectionDate)!= '' && trim($dateResultPrinted)!= ''){
                  $date_result_printed = strtotime($dateResultPrinted);
                  $sample_collection_date = strtotime($sampleCollectionDate);
                  $daydiff = $date_result_printed - $sample_collection_date;
                  $tatD = (int)floor($daydiff / (60 * 60 * 24));
                  $resultDTat[] = $tatD;
               }
               if(trim(strtolower($result['result']))== 'failed' || trim(strtolower($result['result']))== 'fail'){
                  $assayFailures[] = $result['vl_sample_id'];
               }
             }
             $noOfSampleReceivedAtTestingLab = count($noOfSampleReceivedAtLab);
             $assayFailureRate = (count($sResult) >0) ? (round(count($assayFailures)/count($sResult)))*100 : 0;
             $avgResultTat = (count($resultTat) >0) ? round(array_sum($resultTat)/count($resultTat)) : 0;
             $avgResultTatTotal = (count($resultDTat) >0) ? (round(array_sum($resultDTat)/count($resultDTat)) - count($resultDTat)) : 0;
             $html.='<tr>';
             $html.='<td style="height:20px;border:1px solid #f4f4f4;">'.ucwords($vlLab['facility_state']).'</td>';
             $html.='<td style="border:1px solid #f4f4f4;">'.ucwords($vlLab['facility_district']).'</td>';
             $html.='<td style="border:1px solid #f4f4f4;">'.ucwords($vlLab['facility_name']).'</td>';
             $html.='<td style="border:1px solid #f4f4f4;">'.$vlLab['facility_code'].'</td>';
             $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$noOfSampleReceivedAtTestingLab.'</td>';
             $html.='<td align="center" style="border:1px solid #f4f4f4;">'.count($noOfSampleTested).'</td>';
             $html.='<td align="center" style="border:1px solid #f4f4f4;">'.count($noOfSampleNotTested).'</td>';
             $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$assayFailureRate.'</td>';
             $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$avgResultTat.'</td>';
             $html.='<td align="center" style="border:1px solid #f4f4f4;">'.$avgResultTatTotal.'</td>';
             $html.='</tr>';
            }
          $html.='</tbody>';
        $html.='</table>';
        $pdf->writeHTML($html);
        $pdf->lastPage();
        $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
        $pdf->Output($filename,"F");
        $pages[] = $filename;
        //Super lab performance pdf end
        if(count($pages) >0){
          $resultPdf = new Pdf_concat();
          $resultPdf->setFiles($pages);
          $resultPdf->setPrintHeader(false);
          $resultPdf->setPrintFooter(false);
          $resultPdf->concat();
          $reportFilename = 'vl-lab-weekly-report-' . date('d-M-Y-H-i-s') .'.pdf';
          $resultPdf->Output(UPLOAD_PATH. DIRECTORY_SEPARATOR.$reportFilename, "F");
          $general->removeDirectory($pathFront);
          unset($_SESSION['rVal']);
        }
    }
}
?>