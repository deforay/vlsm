<?php
ob_start();
include('MysqliDb.php');
include('General.php');
include ('tcpdf/tcpdf.php');
define('UPLOAD_PATH','../uploads');
$configQuery="SELECT value FROM global_config WHERE name = 'default_time_zone'";
$configResult=$db->query($configQuery);
if(isset($configResult) && count($configResult)> 0){
  date_default_timezone_set($configResult[0]['value']);
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
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
$pdf->SetFont('helvetica', '', 18);
$pathFront=realpath('../uploads');

$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$pdf->AddPage();
  $html = '';
    $html.='<table style="padding:2px;width:100%;font-size:11px;font-family:Times New Roman;">';
      $html.='<tr>';
          $html.='<td colspan="2" style="margin-top:-80px;width:80%;"><h1>Viral Load Laboratory Request From</h1></td>';
          if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
            $html .='<td rowspan="2"><img src="../uploads/logo/'.$arr['logo'].'" style="width:50px;height:50px;float:right;" alt="logo"></td>';
          }else{
            $html.='<td rowspan="2"></td>';
          }
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">Province &nbsp;&nbsp;---------------------------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">District &nbsp;&nbsp;---------------------------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">Clinic name &nbsp;&nbsp;---------------------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:45%;">Sample Collection date &nbsp;&nbsp;--------/---------/----------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:20%;">Time &nbsp;&nbsp;------/------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">Clinician name &nbsp;&nbsp;---------------------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:45%;">First name &nbsp;&nbsp;--------------------------------------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:20%;">Surname &nbsp;&nbsp;-------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:45%">Patient ART Number &nbsp;&nbsp;-----------------------------------</td>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:45%";>Current ART regimen &nbsp;&nbsp;-----------------------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">Sex(tick one)<input type="checkbox" name="gender" value="male"/>Male&nbsp;<input type="checkbox" name="gender" value="female" />Female</td>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:40%;">Date Of ART initiation &nbsp;&nbsp;--------/---------/----------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:30%;">DOB &nbsp;&nbsp;--------/---------/----------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">If no DOB Age &nbsp;&nbsp;------ years</td>';
          $html.='<td style="line-height:25px;text-align:left;width:35%;">If < 1 year Age&nbsp;&nbsp; ------ months</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:50%;">Currently pregnant(tick one)<input type="checkbox" name="gender" value="yes"/>Yes&nbsp;<input type="checkbox" name="gender" value="no" />No</td>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:50%;">Currently breastfeeding(tick one)<input type="checkbox" name="gender" value="yes"/>Yes&nbsp;<input type="checkbox" name="gender" value="no" />No</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:50%;">Date of last viral load tested  &nbsp;&nbsp;--------/---------/----------------</td>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:50%;">Result last viral load &nbsp;&nbsp;------------- copies/ml</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="line-height:25px;text-align:left;">Reason viral load requested(tick one)<input type="checkbox" name="request" value="routine"/>Routine &nbsp;<input type="checkbox" name="request" value="targetfail" />Target clinical failure&nbsp;<input type="checkbox" name="request" value="targetfail" />Targeted</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="line-height:25px;text-align:left;"> immunological failure<input type="checkbox" name="request" value="repeat"/>Repeat After Enhanced Adherence &nbsp;Other ---------------------------------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="line-height:25px;text-align:left;">If After Enhanced Adherence:Poor Adherence was identified <input type="checkbox" name="gender" value="yes"/>Yes&nbsp;<input type="checkbox" name="gender" value="no" />No</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="line-height:25px;text-align:left;">Number of Enhanced Sessions &nbsp;<input type="checkbox" name="gender" value="1"/>1&nbsp;<input type="checkbox" name="gender" value="2" />2&nbsp;<input type="checkbox" name="gender" value="3"/>3&nbsp;<input type="checkbox" name="gender" value="4" />4</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="font-size:13px;">FOR LABORATORY USE ONLY</td>';
      $html.='</tr><hr/>';
      $html.='<tr>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:75%;">VL Platform(tick one)<input type="checkbox" name="request" value="routine"/>BioMerieux &nbsp;<input type="checkbox" name="request" value="targetfail" />Roche &nbsp;<input type="checkbox" name="request" value="targetfail" />Abbott &nbsp;<input type="checkbox" name="request" value="poc" />POC</td><td rowspan="3"></td>';
      $html.='</tr><hr style="width:75%;"/>';
      $html.='<tr>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:75%;">Test Method(tick one)<input type="checkbox" name="request" value="routine"/>Individual &nbsp;<input type="checkbox" name="request" value="targetfail" />Minipool &nbsp;<input type="checkbox" name="request" value="targetfail" />Other Pooling algorithm</td>';
      $html.='</tr><hr style="width:75%;"/>';
      $html.='<tr>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;">Specimen Type(tick one)<input type="checkbox" name="request" value="routine"/>EDTA DBS &nbsp;<input type="checkbox" name="request" value="targetfail" />PP DBS &nbsp;<input type="checkbox" name="request" value="targetfail" />DBS &nbsp;<input type="checkbox" name="request" value="poc" />PLASMA &nbsp;<input type="checkbox" name="request" value="blood" />WHOLE BLOOD</td>';
      $html.='</tr><hr/>';
      $html.='<tr>';
          $html.='<td style="line-height:25px;text-align:left;width:33%;">Date of result  &nbsp;&nbsp;--------/---------/----------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:39%;">Viral Load result &nbsp;&nbsp;----------------------- copies/ml</td>';
          $html.='<td style="line-height:25px;text-align:left;width:27%";>Approved by &nbsp;&nbsp;------------------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:55%;">If no result &nbsp;&nbsp;------------------------------------------------------------------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:45%;">Date Received stamp  &nbsp;&nbsp;--------/---------/----------------</td>';
      $html.='</tr><hr/>';
      $html.='<tr>';
          $html.='<td colspan="3" style="height:45px;line-height:25px;text-align:left;width:75%;">Laboratory Officer comments &nbsp;&nbsp;------------------------------------------------------</td>';
      $html.='</tr>';
      $html.='<tr><td colspan="3" style="line-height:35px;border-top-style:dotted;border-width:1px;width:100%;">Tear Here</td></tr>';
      $html.='<tr>';
          $html.='<td colspan="2" style="line-height:25px;text-align:left;width:50%;">Sample Collection date &nbsp;&nbsp;--------/---------/---------------- Time &nbsp;&nbsp;-----/------</td>';
          $html.='<td style="line-height:25px;text-align:left;width:50%;">Patient name and surname &nbsp;&nbsp;------------------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="line-height:25px;text-align:left;">Patient OI number &nbsp;&nbsp;---------------------- &nbsp;&nbsp;Patient phone number &nbsp;&nbsp;--------------------------</td>';
      $html.='</tr>';
      $html.='<tr>';
          $html.='<td colspan="3" style="line-height:25px;text-align:left;">Viral Load result &nbsp;&nbsp;--------------- copies/ml &nbsp;&nbsp;Date VL received  &nbsp;&nbsp;--------/---------/----------------</td>';
      $html.='</tr>';
    $html.='</table>';
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-zambia-form.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>