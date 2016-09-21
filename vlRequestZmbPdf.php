<?php
//print_r($result);die;
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/tcpdf/tcpdf.php');
define('UPLOAD_PATH','uploads');
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
$pdf->SetTitle('Vl Request Form');
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

$pathFront=realpath('./uploads');
//$pdf = new TCPDF();
$pdf->AddPage();
$general=new Deforay_Commons_General();
$id=$_POST['id'];
$fQuery="SELECT * from vl_request_form as vl where treament_id=$id";
$result=$db->query($fQuery);

if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['sample_collection_date']);
 $result[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['sample_collection_date']='';
}
if(isset($result[0]['date_sample_received_at_testing_lab']) && trim($result[0]['date_sample_received_at_testing_lab'])!='' && trim($result[0]['date_sample_received_at_testing_lab'])!='0000-00-00'){
 $result[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($result[0]['date_sample_received_at_testing_lab']);
}else{
 $result[0]['date_sample_received_at_testing_lab']='';
}

$configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    $html = '';
    $html.='<table style="padding:5px;border:2px solid #333;">';
    $html .='<tr>';
    if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
     $html .='<td style="text-align:center;border-right:2px solid #333;padding:3px 0px 3px 0px;"><img src="uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;" alt="logo"></td>';
    }
    if(isset($arr['header']) && trim($arr['header'])!= '') {
        $html .='<td colspan="2" style="text-align:center;font-size:15px;border-right:2px solid #333;font-weight:bold;padding:3px 0px 3px 0px;">'.ucwords($arr['header']).'</td>';
    }
    $html .='</tr>';
    $html.='</table><br/><br/>';
    $html.='<table style="padding:5px;width:98%;border:2px solid #333;">';
    $html.='<tr>';
    $html.='<td>Serial No:'.$result[0]['serial_no'].'&nbsp;</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Date Of Sample Collection and Time:'.$result[0]['sample_collection_date'].'</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Patient Name and Surname:'.$result[0]['patient_name']." ".$result[0]['surname'].'</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Patient OI/ART Number:'.$result[0]['art_no'].'</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Patient Phone Number:'.$result[0]['patient_phone_number'].'</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Viral Load Result:'.$result[0]['result'].'</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Viral Load Log Result:'.$result[0]['log_value'].'</td>';
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td>Date VL receive:'.$result[0]['date_sample_received_at_testing_lab'].'</td>';
    $html.='</tr>';
   $html.='</table>';
   $pdf->writeHTML($html);
$pdf->lastPage();
$filename = 'vl-form-' . date('d-M-Y-H-i-s') . '.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>