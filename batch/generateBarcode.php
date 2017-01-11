<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include ('../includes/tcpdf/tcpdf.php');
define('UPLOAD_PATH','../uploads');
$id=base64_decode($_POST['id']);
if($id >0){
    //if (!file_exists('uploads') && !is_dir('uploads')) {
    //    mkdir('uploads');
    //}
        
    if (!file_exists(UPLOAD_PATH. DIRECTORY_SEPARATOR . "barcode") && !is_dir(UPLOAD_PATH. DIRECTORY_SEPARATOR."barcode")) {
        mkdir(UPLOAD_PATH. DIRECTORY_SEPARATOR."barcode");
    }
    $lQuery="SELECT * from global_config where name='logo'";
    $lResult=$db->query($lQuery);
   
    $hQuery="SELECT * from global_config where name='header'";
    $hResult=$db->query($hQuery);
    
    $bQuery="SELECT * from batch_details as b_d INNER JOIN import_config as i_c ON i_c.config_id=b_d.machine where batch_id=$id";
    $bResult=$db->query($bQuery);
    if(count($bResult)>0){
        // Extend the TCPDF class to create custom Header and Footer
        class MYPDF extends TCPDF {
            public function setHeading($logo,$header) {
                $this->header = $header;
                $this->logo = $logo;
            }
            //Page header
            public function Header() {
                // Logo
                //$image_file = K_PATH_IMAGES.'logo_example.jpg';
                //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // Set font
                if(trim($this->logo)!=""){
                    if (file_exists('../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo)) {
                        $image_file = '../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo;
                        $this->Image($image_file,10, 10, 25, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    }
                }
    
                $this->SetFont('helvetica', '', 15);
                $this->header=str_replace("<div","<span",trim($this->header));
                $this->header=str_replace("</div>","</span><br/>",$this->header);
    
                $this->writeHTMLCell(0,0,35,10,$this->header, 0, 0, 0, true, 'C', true);
                $html='<hr/>';
                $this->writeHTMLCell(0, 0,10,35, $html, 0, 0, 0, true, 'J', true);
                //$this->Cell(0, 15,$this->header, 0, false, 'C', 0, '', 0, false, 'M', 'M');
            }
        
            // Page footer
            public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);
                // Page number
                $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }

        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->setHeading($lResult[0]['value'],$hResult[0]['value']);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Generate Barcode');
        $pdf->SetSubject('Barcode');
        $pdf->SetKeywords('Generate Barcode');
    
        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
    
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
    
        // set font
        $pdf->SetFont('helvetica', '', 10);
    
        // add a page
        $pdf->AddPage();
    
    $tbl = '<table cellspacing="0" cellpadding="3" border="1" style="width:100%">
            <thead>
                <tr nobr="true" style="background-color:#71b9e2;color:#FFFFFF;">
                    <td align="center" width="8%">S.No.</td>
                    <td align="center" width="27%">Sample ID</td>
                    <td align="center" width="65%">Barcode</td>
                </tr>
            </thead>';
    $tbl.='</table>';
    if(isset($bResult[0]['label_order']) && trim($bResult[0]['label_order'])!= ''){
        $jsonToArray = json_decode($bResult[0]['label_order'],true);
        $sampleCounter = 1;
        for($j=0;$j<count($jsonToArray);$j++){
            if($pdf->getY()>=250){
                $pdf->AddPage();
            }
            $xplodJsonToArray = explode("_",$jsonToArray[$j]);
            if(count($xplodJsonToArray)>1 && $xplodJsonToArray[0] == "s"){
                $sampleQuery="SELECT sample_code from vl_request_form where vl_sample_id=$xplodJsonToArray[1]";
                $sampleResult=$db->query($sampleQuery);
                
                $params = $pdf->serializeTCPDFtagParameters(array($sampleResult[0]['sample_code'], 'C39', '', '','' ,15, 0.25,array('border'=>false,'align' => 'C','padding'=>1, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>false, 'font'=>'helvetica', 'fontsize'=>10, 'stretchtext'=>2),'N'));
                
                $tbl.='<table cellspacing="0" cellpadding="3" border="1" style="width:100%">';
                $tbl.='<tr>';
                $tbl.='<td align="center" width="8%" style="vertical-align:middle;">'.$sampleCounter.'.</td>';
                $tbl.='<td align="center" width="27%" style="vertical-align:middle;">'.$sampleResult[0]['sample_code'].'</td>';
                $tbl.='<td align="center" width="65%" style="vertical-align:middle;"><tcpdf method="write1DBarcode" params="'.$params.'" /></td>';
                $tbl.='</tr>';
                $tbl.='</table>';
            }else{
                $label = str_replace("_"," ",$jsonToArray[$j]);
                $label = str_replace("in house","In-House",$label);
                $label = ucwords(str_replace("no of "," ",$label));
                $tbl.='<table cellspacing="0" cellpadding="3" border="1" style="width:100%">';
                $tbl.='<tr>';
                $tbl.='<td align="center" width="8%" style="vertical-align:middle;">'.$sampleCounter.'.</td>';
                $tbl.='<td align="center" width="27%" style="vertical-align:middle;">'.$label.'</td>';
                $tbl.='<td align="center" width="65%" style="vertical-align:middle;"></td>';
                $tbl.='</tr>';
                $tbl.='</table>';
            }
         $sampleCounter++;
        } 
    }else{
        $noOfInHouseControls = 0;
        if(isset($bResult[0]['number_of_in_house_controls']) && $bResult[0]['number_of_in_house_controls'] !='' && $bResult[0]['number_of_in_house_controls']!=NULL){
            $noOfInHouseControls = $bResult[0]['number_of_in_house_controls'];
            for($i=1;$i<=$bResult[0]['number_of_in_house_controls'];$i++){
                $tbl.='<table cellspacing="0" cellpadding="3" border="1" style="width:100%">
                     <tr nobr="true">
                    <td align="center" width="8%" style="vertical-align:middle;">'.$i.'.</td>
                    <td align="center" width="27%" style="vertical-align:middle;">In-House Controls '. $i.'</td>
                    <td align="center" width="65%" style="vertical-align:middle;"></td>
                </tr></table>';
            }
        }
        $noOfManufacturerControls = 0;
        if(isset($bResult[0]['number_of_manufacturer_controls']) && $bResult[0]['number_of_manufacturer_controls'] !='' && $bResult[0]['number_of_manufacturer_controls']!=NULL){
            $noOfManufacturerControls = $bResult[0]['number_of_manufacturer_controls'];
            for($i=1;$i<=$bResult[0]['number_of_manufacturer_controls'];$i++){
                $sNo = $noOfInHouseControls+$i;
                $tbl.='<table cellspacing="0" cellpadding="3" border="1" style="width:100%">
                    <tr nobr="true">
                    <td align="center" width="8%" style="vertical-align:middle;">'.$sNo.'.</td>
                    <td align="center" width="27%" style="vertical-align:middle;">Manufacturer Controls '. $i.'</td>
                    <td align="center" width="65%" style="vertical-align:middle;"></td>
                </tr></table>';
            }
        }
        $noOfCalibrators = 0;
        if(isset($bResult[0]['number_of_calibrators']) && $bResult[0]['number_of_calibrators'] !='' && $bResult[0]['number_of_calibrators']!=NULL){
            $noOfCalibrators = $bResult[0]['number_of_calibrators'];
            for($i=1;$i<=$bResult[0]['number_of_calibrators'];$i++){
                $sNo = $noOfInHouseControls+$noOfManufacturerControls+$i;
                $tbl.='<table cellspacing="0" cellpadding="3" border="1" style="width:100%">
                    <tr nobr="true">
                    <td align="center" width="8%" style="vertical-align:middle;">'.$sNo.'.</td>
                    <td align="center" width="27%" style="vertical-align:middle;">Calibrators '. $i.'</td>
                    <td align="center" width="65%" style="vertical-align:middle;"></td>
                </tr></table>';
            }
        }
        $sampleCounter = ($noOfInHouseControls+$noOfManufacturerControls+$noOfCalibrators+1);
        $sQuery="SELECT sample_code from vl_request_form where batch_id=$id";
        $result=$db->query($sQuery);
        foreach($result as $sample){
            if($pdf->getY()>=250){
              $pdf->AddPage();
            }
            $params = $pdf->serializeTCPDFtagParameters(array($sample['sample_code'], 'C39', '', '','' ,15, 0.25,array('border'=>false,'align' => 'C','padding'=>1, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>false, 'font'=>'helvetica', 'fontsize'=>10, 'stretchtext'=>2),'N'));
            
            $tbl.='<table cellspacing="0" cellpadding="3" border="1" style="width:100%">';
            $tbl.='<tr>';
            $tbl.='<td align="center" width="8%" style="vertical-align:middle;">'.$sampleCounter.'.</td>';
            $tbl.='<td align="center" width="27%" style="vertical-align:middle;">'.$sample['sample_code'].'</td>';
            $tbl.='<td align="center" width="65%" style="vertical-align:middle;"><tcpdf method="write1DBarcode" params="'.$params.'" /></td>';
            $tbl.='</tr>';
            $tbl .='</table>';
          $sampleCounter++;
       } 
    }
    $pdf->writeHTMLCell('', '', 12,$pdf->getY(),$tbl, 0, 1, 0, true, 'C', true);
    $filename = trim($bResult[0]['batch_code']).'.pdf';
    $pdf->Output('.././uploads'. DIRECTORY_SEPARATOR.'barcode'. DIRECTORY_SEPARATOR.$filename, "F");
    echo $filename;
  }
}
?>
