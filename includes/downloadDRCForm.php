<?php
session_start();
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
$html .= '<div style="font-size:10px;font-family:Times New Roman;">';
  $html.='<table style="padding:2px;">';
        if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
          $html .='<tr>';
            $html .='<td colspan="6" style="text-align:center;"><img src="../uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;" alt="logo"></td>';
          $html .='</tr>';
        }
        $html .='<tr>';
         $html .='<td colspan="6" style="text-align:left;"><h1>Viral Load Laboratory Request Form</h1></td>';
        $html .='</tr>';
        $html.='<tr>';
            $html.='<td colspan="6" style="line-height:30px;"><h3>1. Réservé à la structure de soins</h3></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="6"><h3>Information sur la structure de soins</h3></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Province</td>';
            $html.='<td style="line-height:20px;">-------------------------------</td>';
            $html.='<td style="line-height:20px;">Zone de santé</td>';
            $html.='<td style="line-height:20px;">-------------------------------</td>';
            $html.='<td style="line-height:20px;">Structure/Service</td>';
            $html.='<td style="line-height:20px;">-------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Demandeur</td>';
            $html.='<td style="line-height:20px;">-------------------------------</td>';
            $html.='<td style="line-height:20px;">Téléphone </td>';
            $html.='<td style="line-height:20px;">-------------------------------</td>';
            $html.='<td style="line-height:20px;">Partenaire d?appui</td>';
            $html.='<td style="line-height:20px;">-------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Date de la demande </td>';
            $html.='<td colspan="5" style="line-height:20px;">-------/-------/---------------</td>';
        $html.='</tr>';
    $html.='</table>';
    $html.='<table>';
        $html.='<tr>';
            $html.='<td colspan="8"><h3>Information sur le patient</h3></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Date de naissance </td>';
            $html.='<td style="line-height:20px;">------/-----/------------</td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Âge en années  </td>';
            $html.='<td style="line-height:20px;">-------------------------</td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Âge en mois  </td>';
            $html.='<td style="line-height:20px;">-------------------------</td>';
            $html.='<td style="line-height:20px;text-align:center;">&nbsp;&nbsp;Sexe  </td>';
            $html.='<td style="line-height:20px;"> M <input type="radio" name="gender" value="male"/>&nbsp;&nbsp;F <input type="radio" name="gender" value="female"/></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Code du patient </td>';
            $html.='<td style="line-height:20px;">------/-----/------------</td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Si S/ ARV  </td>';
            $html.='<td style="line-height:20px;"> Oui <input type="radio" name="isPatientNew" value="yes" />&nbsp;&nbsp;Non <input type="radio" name="isPatientNew" value="no" /></td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Date du début des ARV   </td>';
            $html.='<td colspan="3" style="line-height:20px;">------/-----/------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Régime ARV en cours </td>';
            $html.='<td colspan="7" style="line-height:20px;">-------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Ce patient a-t-il déjà changé de régime de traitement? </td>';
            $html.='<td colspan="2" style="line-height:20px;text-align:center;">&nbsp;&nbsp;Oui <input type="radio" name="hasChangedRegimen" value="yes" />&nbsp;&nbsp;Non <input type="radio" name="hasChangedRegimen" value="no" /></td>';
            $html.='<td colspan="2" style="line-height:20px;">Motif de changement de régime ARV </td>';
            $html.='<td colspan="2" style="line-height:20px;">-------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date du changement de régime ARV </td>';
            $html.='<td colspan="6" style="line-height:20px;">------/-----/------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Motif de la demande </td>';
            $html.='<td colspan="2" style="line-height:20px;">-------------------------</td>';
            $html.='<td colspan="2" style="line-height:20px;">Charge virale N </td>';
            $html.='<td colspan="2" style="line-height:20px;">-------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td style="line-height:20px;">Si Femme : </td>';
            $html.='<td style="line-height:20px;">allaitante ? </td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Oui <input type="radio" name="breastfeeding" value="yes" />&nbsp;&nbsp;Non <input type="radio" name="breastfeeding" value="no" /></td>';
            $html.='<td style="line-height:20px;text-align:center;">Ou enceinte ? </td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Oui <input type="radio" name="patientPregnant" value="yes" />&nbsp;&nbsp;Non <input type="radio" name="patientPregnant" value="no" /></td>';
            $html.='<td style="line-height:20px;">&nbsp;&nbsp;Si Femme enceinte </td>';
            $html.='<td colspan="2" style="line-height:20px;">&nbsp;&nbsp;Trimestre 1<input type="radio" name="trimestre" value="trimestre1" />&nbsp;&nbsp;Trimestre 2<input type="radio" name="trimestre" value="trimestre2" />&nbsp;&nbsp;Trimestre 3<input type="radio" name="trimestre" value="trimestre3" /></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Résultat dernière charge virale </td>';
            $html.='<td colspan="6" style="line-height:20px;">------------------------- copies/ml </td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date dernière charge virale (demande) </td>';
            $html.='<td colspan="6" style="line-height:20px;">------/-----/------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="8" style="line-height:20px;">A remplir par le service demandeur dans la structure de soins </td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="8"><h3>Informations sur le prélèvement <span style="font-size:10px;">(A remplir par le préleveur)</span></h3></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date du prélèvement </td>';
            $html.='<td colspan="6" style="line-height:20px;">--------/-------/--------------- ------/------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Type déchantillon  </td>';
            $html.='<td colspan="6" style="line-height:20px;">---------------------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Si plasma, &nbsp;&nbsp;Température de conservation  </td>';
            $html.='<td colspan="2" style="line-height:20px;">--------------------------------------------- °C</td>';
            $html.='<td colspan="2" style="text-align:center;">Durée de conservation </td>';
            $html.='<td colspan="2" style="line-height:20px;">--------/-------- Jour/Heures </td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date de départ au Labo biomol </td>';
            $html.='<td colspan="6" style="line-height:20px;">--------/-------/--------------- ------/------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="8" style="line-height:30px;"><h3>2. Réservé au Laboratoire de biologie moléculaire </h3></td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date de réception de l?échantillon </td>';
            $html.='<td colspan="6" style="line-height:20px;">--------/-------/--------------- ------/------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Décision prise </td>';
            $html.='<td colspan="6" style="line-height:20px;">---------------------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Motifs de rejet </td>';
            $html.='<td colspan="6" style="line-height:20px;">---------------------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Code Labo </td>';
            $html.='<td colspan="6" style="line-height:20px;">---------------------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date de réalisation de la charge virale </td>';
            $html.='<td colspan="6" style="line-height:20px;">-----------/-----------/---------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Technique utilisée </td>';
            $html.='<td colspan="6" style="line-height:20px;">---------------------------------------------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Résultat </td>';
            $html.='<td colspan="2" style="line-height:20px;">------------------------------------ copies/ml </td>';
            $html.='<td colspan="2" style="line-height:20px;text-align:center;">Log </td>';
            $html.='<td colspan="2" style="line-height:20px;">------------------------------------ copies/ml </td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="8" style="line-height:20px;">A remplir par le service effectuant la charge virale </td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="2" style="line-height:20px;">Date de remise du résultat </td>';
            $html.='<td colspan="6" style="line-height:20px;">--------/-------/-------------- ------/------</td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="8">1. Biffer la mention inutile </td>';
        $html.='</tr>';
        $html.='<tr>';
            $html.='<td colspan="8">2. Sélectionner un seul régime de traitement </td>';
        $html.='</tr>';
  $html.='</table>';
$html .= "</div>";
$pdf->writeHTML(utf8_encode($html));
$pdf->lastPage();
$filename = 'vl-drc-form.pdf';
$pdf->Output($pathFront . DIRECTORY_SEPARATOR . $filename,"F");
echo $filename;
?>