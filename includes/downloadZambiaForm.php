<?php
ob_start();
include('MysqliDb.php');
include('General.php');
include ('tcpdf/tcpdf.php');
define('UPLOAD_PATH','../uploads');
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
        $this->SetFont('helvetica', '');
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
//$pdf->SetAuthor('Saravanan');
$pdf->SetTitle('Viral Load Laboratory Request Form');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set font
$pdf->SetFont('helvetica', '', 10);

$pathFront=realpath('../uploads');
//$pdf = new TCPDF();
$pdf->AddPage();
$general=new Deforay_Commons_General();

$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
        $html = '';
        $html.='<table style="padding:2px;">';
            $html.='<tr>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Province ---------------------------------</td>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">District ---------------------------------</td>';
                if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                $html .='<td><img src="../uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;float:right;" alt="logo"></td>';
                }
            $html.='</tr>';
            $html.='<tr>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Clinic name-------------------------------</td>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Sample Colloection date-------/-------/-----</td>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Time------------</td>';
            $html.='</tr>';
            $html.='<tr>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Clinician name-----------------------------</td>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">First name------------------------------</td>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Surname-----------------</td>';
            $html.='</tr>';
            $html.='<tr>';
                $html.='<td style="line-height:25px;font-size:13px;font-weight:bold;text-align:left;">Patient ART Number -----------------------------</td><td></td><td></td>';
                
            $html.='</tr>';
        $html.='</table>';
$pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-form-' . date('d-M-Y-H-i-s') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>