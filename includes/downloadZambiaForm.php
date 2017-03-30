<?php
ob_start();
include('MysqliDb.php');
include('General.php');
include ('tcpdf/tcpdf.php');
define('UPLOAD_PATH','../uploads');

$pathFront=realpath('../uploads');
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
if(isset($arr['default_time_zone']) && trim($arr['default_time_zone'])!= ''){
  date_default_timezone_set($arr['default_time_zone']);
}else{
  date_default_timezone_set("Europe/London");
}
//header and footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        //$image_file = K_PATH_IMAGES.'logo_example.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        //$this->SetFont('helvetica', 'B', 20);
        // Title
        //$this->Cell(0, 15, 'VL Request Form Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        //// Position at 15 mm from bottom
        //$this->SetY(-15);
        //// Set font
        //$this->SetFont('times', '', 8);
        //// Page number
        //$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPageOrientation('L');
// set document information
$pdf->SetCreator(PDF_CREATOR);
//$pdf->SetAuthor('Saravanan');
$pdf->SetTitle('Vl Request Result Form');
//$pdf->SetSubject('TCPDF Tutorial');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
//if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
//    require_once(dirname(__FILE__).'/lang/eng.php');
//    $pdf->setLanguageArray($l);
//}

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', '', 11);
$pdf->AddPage();
  $html = '';
    $html.='<table style="width:100%;padding:2px;">';
      $html.='<tr>';
      $html.='<td style="width:18%;">';
        $html.='<table style="width:100%;padding:2px;">';
          $html.='<tr><td style="line-height:140px;"></td></tr>';
          $html.='<tr><td style="line-height:25px;">Date of sample collection</td></tr>';
          $html.='<tr><td style="line-height:25px;">--------/---------/----------------</td></tr>';
          $html.='<tr><td style="line-height:25px;">Time of sample collection</td></tr>';
          $html.='<tr><td style="line-height:25px;">-----------------/-----------------</td></tr>';
          $html.='<tr><td style="line-height:25px;">Patient name</td></tr>';
          $html.='<tr><td style="line-height:25px;">-----------------------------------</td></tr>';
          $html.='<tr><td style="line-height:25px;">Patient of number</td></tr>';
          $html.='<tr><td style="line-height:25px;">-----------------------------------</td></tr>';
          $html.='<tr><td style="line-height:25px;">Patient phone number</td></tr>';
          $html.='<tr><td style="line-height:25px;">-----------------------------------</td></tr>';
          $html.='<tr><td style="line-height:25px;">Viral load result</td></tr>';
          $html.='<tr><td style="line-height:25px;">------------------------ copies/ml</td></tr>';
          $html.='<tr><td style="line-height:25px;">Date VL received</td></tr>';
          $html.='<tr><td style="line-height:25px;">--------/---------/----------------</td></tr>';
        $html.='</table>';
      $html.='</td>';
      $html.='<td style="width:82%;border-left:1px dotted #c3c3c3;">';
        $html.='<table style="width:100%;padding:2px 2px 2px 4px;">';
          $html.='<tr>';
              $html.='<td colspan="2" style="margin-top:-80px;width:88%;"><h1>Viral Load Laboratory Request Form</h1></td>';
              if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                $html .='<td rowspan="2"><img src="../uploads/logo/'.$arr['logo'].'" style="width:60px;height:60px;float:right;" alt="logo"></td>';
              }else{
                $html.='<td rowspan="2"></td>';
              }
          $html.='</tr>';
          $html.='<tr>';
            $html.='<td style="line-height:25px;text-align:left;">Province &nbsp;&nbsp;---------------------------------------------------</td>';
            $html.='<td style="line-height:25px;text-align:left;">District &nbsp;&nbsp;---------------------------------------------------</td>';
          $html.='</tr>';
          $html.='<tr>';
            $html.='<td style="line-height:25px;text-align:left;">Clinic name &nbsp;&nbsp;---------------------------------------------------</td>';
            $html.='<td style="line-height:25px;text-align:left;">Sample collection date &nbsp;&nbsp;--------/---------/----------------</td>';
            $html.='<td style="line-height:25px;text-align:left;">Time &nbsp;&nbsp;--------/--------</td>';
          $html.='</tr>';
          $html.='<tr>';
            $html.='<td style="line-height:25px;text-align:left;">Clinician name &nbsp;&nbsp;-----------------------------------------------</td>';
            $html.='<td style="line-height:25px;text-align:left;">First Name &nbsp;&nbsp;---------------------------------------------------</td>';
            $html.='<td style="line-height:25px;text-align:left;">Surname &nbsp;&nbsp;-------------</td>';
          $html.='</tr>';
          $html.='<tr><td colspan="3" style="line-height:10px;"></td></tr>';
          $html.='<tr>';
             $html.='<td colspan="3" style="line-height:25px;text-align:left;">Patient ART number &nbsp;&nbsp;--------------------------------------------</td>';
          $html.='</tr>';
          $html.='<tr>';
            $html.='<td colspan="3">';
            $html.='<table style="width:100%;">';
               $html.='<tr>';
                $html.='<td style="line-height:25px;text-align:left;width:25%;">Sex <input type="checkbox" name="gender" value="male"/>Male&nbsp;<input type="checkbox" name="gender" value="female" />Female</td>';
                $html.='<td style="line-height:25px;text-align:left;width:35%;">Date of ART initiation &nbsp;&nbsp;--------/---------/----------------</td>';
                $html.='<td style="line-height:25px;text-align:left;width:40%;">Current ART regimen &nbsp;&nbsp;-----------------------</td>';
              $html.='</tr>';
              $html.='<tr>';
                  $html.='<td style="line-height:25px;text-align:left;">DOB &nbsp;&nbsp;--------/---------/----------------</td>';
                  $html.='<td style="line-height:25px;text-align:left;">If no DOB, Age &nbsp;&nbsp;----------- years</td>';
                  $html.='<td style="line-height:25px;text-align:left;">If < 1 year, Age&nbsp;&nbsp; ---------- months</td>';
              $html.='</tr>';
              $html.='<tr>';
                  $html.='<td style="line-height:25px;text-align:left;">Currently pregnant<input type="checkbox" name="x" value="yes"/>Yes&nbsp;<input type="checkbox" name="x" value="no" />No</td>';
                  $html.='<td colspan="2" style="line-height:25px;text-align:left;">Currently breastfeeding<input type="checkbox" name="x" value="yes"/>Yes&nbsp;<input type="checkbox" name="x" value="no" />No</td>';
              $html.='</tr>';
            $html.='</table>';
            $html.='</td>';
          $html.='</tr>';
          $html.='<tr><td colspan="3" style="line-height:10px;"></td></tr>';
          $html.='<tr>';
            $html.='<td colspan="3">';
            $html.='<table style="width:100%;">';
                $html.='<tr>';
                  $html.='<td style="line-height:25px;text-align:left;width:50%;">Date of last viral load tested  &nbsp;&nbsp;--------/---------/----------------</td>';
                  $html.='<td style="line-height:25px;text-align:left;width:50%;">Result last viral load &nbsp;&nbsp;------------------------ copies/ml</td>';
                $html.='</tr>';
                $html.='<tr>';
                    $html.='<td colspan="2" style="line-height:25px;text-align:left;">Reason viral load requested <input type="checkbox" name="request" value="routine"/>Routine &nbsp;<input type="checkbox" name="request" value="targetfail" />Target clinical failure&nbsp;<input type="checkbox" name="request" value="targetfail" />Targeted immunological failure</td>';
                $html.='</tr>';
                $html.='<tr>';
                  $html.='<td colspan="2" style="line-height:25px;text-align:left;">Other -------------------------------------------------------------------------</td>';
                $html.='</tr>';
                $html.='<tr>';
                  $html.='<td style="line-height:25px;text-align:left;">If after enhanced adherence: Poor adherence was identified <input type="checkbox" name="x" value="yes"/>Yes &nbsp;<input type="checkbox" name="x" value="no" />No</td>';
                  $html.='<td style="line-height:25px;text-align:left;">Number of enhanced sessions &nbsp;<input type="checkbox" name="x" value="1"/>1&nbsp;<input type="checkbox" name="x" value="2" />2&nbsp;<input type="checkbox" name="x" value="3"/>3&nbsp;<input type="checkbox" name="x" value="4" />4</td>';
                $html.='</tr>';
            $html.='</table>';
            $html.='</td>';
          $html.='</tr>';
          $html.='<tr><td colspan="3" style="line-height:10px;"></td></tr>';
          $html.='<tr>';
            $html.='<td colspan="3" style="font-size:13px;text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>FOR LABORATORY USE ONLY</strong></td>';
          $html.='</tr>';
          $html.='<tr>';
            $html.='<td colspan="3">';
            $html.='<table style="width:100%;">';
              $html.='<tr>';
                $html.='<td colspan="2" style="line-height:30px;text-align:left;width:60%;">VL platform <input type="checkbox" name="request" value="routine"/>BioMerieux &nbsp;<input type="checkbox" name="request" value="targetfail" />Roche &nbsp;<input type="checkbox" name="request" value="targetfail" />Abbott &nbsp;<input type="checkbox" name="request" value="poc" />POC</td><td rowspan="3"></td>';
              $html.='</tr>';
              $html.='<tr>';
                $html.='<td colspan="2" style="line-height:30px;text-align:left;">Specimen type <input type="checkbox" name="specimenType" value="edadbs"/>EDTA DBS &nbsp;<input type="checkbox" name="specimenType" value="ppdbs" />PP DBS &nbsp;<input type="checkbox" name="specimenType" value="dbs" />DBS &nbsp;<input type="checkbox" name="specimenType" value="plasma" />PLASMA &nbsp;<input type="checkbox" name="specimenType" value="blood" />WHOLE BLOOD</td>';
              $html.='</tr>';
              $html.='<tr>';
                $html.='<td colspan="2" style="line-height:30px;text-align:left;">Test method<input type="checkbox" name="testMethod" value="individual"/>Individual &nbsp;<input type="checkbox" name="testMethod" value="minipool" />Minipool &nbsp;<input type="checkbox" name="testMethod" value="other" />Other pooling algorithm</td>';
              $html.='</tr>';
              $html.='<tr>';
                $html.='<td style="line-height:40px;text-align:left;">Date of result  &nbsp;&nbsp;--------/---------/----------------</td>';
                $html.='<td colspan="2" style="line-height:40px;text-align:left;">Viral load result &nbsp;&nbsp;----------------------- copies/ml</td>';
              $html.='</tr>';
              $html.='<tr>';
                $html.='<td colspan="2" style="line-height:25px;text-align:left;">If no result &nbsp;&nbsp;-----------------------------------------------------------------------------------------------------</td>';
                $html.='<td style="line-height:25px;text-align:left;">Approved by &nbsp;&nbsp;---------------------------------</td>';
              $html.='</tr>';
              $html.='<tr><td colspan="3" style="line-height:10px;"></td></tr>';
              $html.='<tr>';
                $html.='<td style="text-align:left;">Laboratory officer comments </td>';
                $html.='<td colspan="2" style="text-align:left;">Date received stamp</td>';
              $html.='</tr>';
               $html.='<tr>';
                $html.='<td style="line-height:30px;text-align:left;">-------------------------------------------------------- </td>';
                $html.='<td colspan="2" style="line-height:30px;text-align:left;">--------/---------/----------------</td>';
              $html.='</tr>';
            $html.='</table>';
            $html.='</td>';
          $html.='</tr>';
        $html.='</table>';
      $html.='</td>';
      $html.='</tr>';
    $html.='</table>';
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-zambia-form.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>