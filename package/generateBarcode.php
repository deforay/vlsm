<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include ('../includes/tcpdf/tcpdf.php');
include('../General.php');
//define('UPLOAD_PATH','../uploads');
$general=new Deforay_Commons_General();
$id=base64_decode($_POST['id']);
if($id >0){
    if (!file_exists(UPLOAD_PATH. DIRECTORY_SEPARATOR . "package_barcode") && !is_dir(UPLOAD_PATH. DIRECTORY_SEPARATOR."package_barcode")) {
        mkdir(UPLOAD_PATH. DIRECTORY_SEPARATOR."package_barcode");
    }
    $configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    $bQuery="SELECT * from package_details as pd where package_id=$id";
    $bResult=$db->query($bQuery);
    $dateQuery="SELECT sample_tested_datetime,result_reviewed_datetime,pd.package_code,pd.package_id from package_details as pd Join vl_request_form as vl ON vl.sample_package_id=pd.package_id where pd.package_id='".$id."' AND (sample_tested_datetime IS NOT NULL AND sample_tested_datetime!= '' AND sample_tested_datetime!= '00000-00-00 00:00:00') LIMIT 1";
    $dateResult=$db->query($dateQuery);
    $resulted = '';
    $reviewed = '';
    if(isset($dateResult[0]['sample_tested_datetime']) && $dateResult[0]['sample_tested_datetime']!= '' && $dateResult[0]['sample_tested_datetime']!= NULL && $dateResult[0]['sample_tested_datetime']!= '0000-00-00 00:00:00'){
        $sampleTestedDate = explode(" ",$dateResult[0]['sample_tested_datetime']);
        $resulted=$general->humanDateFormat($sampleTestedDate[0])." ".$sampleTestedDate[1];
    }if(isset($dateResult[0]['result_reviewed_datetime']) && $dateResult[0]['result_reviewed_datetime']!= '' && $dateResult[0]['result_reviewed_datetime']!= NULL && $dateResult[0]['result_reviewed_datetime']!= '0000-00-00 00:00:00'){
        $resultReviewdDate = explode(" ",$dateResult[0]['result_reviewed_datetime']);
        $reviewed=$general->humanDateFormat($resultReviewdDate[0])." ".$resultReviewdDate[1];
    }
    if(count($bResult)>0){
        // Extend the TCPDF class to create custom Header and Footer
        class MYPDF extends TCPDF {
            public function setHeading($logo,$text,$package,$resulted,$reviewed) {
                $this->logo = $logo;
                $this->text = $text;
                $this->package = $package;
                $this->resulted = $resulted;
                $this->reviewed = $reviewed;
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
                        $this->Image($image_file,15, 10, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    }
                }
                $this->SetFont('helvetica', '', 7);
                $this->writeHTMLCell(30,0,10,26,$this->text, 0, 0, 0, true, 'A', true);
                $this->SetFont('helvetica', '', 13);
                $this->writeHTMLCell(0,0,0,10,'Package : '.$this->package, 0, 0, 0, true, 'C', true);
                $this->writeHTMLCell(0,0,0,20,'Package Worksheet', 0, 0, 0, true, 'C', true);
                $this->SetFont('helvetica', '', 9);
                $this->writeHTMLCell(0,0,144,10,'Resulted : '.$this->resulted, 0, 0, 0, true, 'C', true);
                $this->writeHTMLCell(0,0,144,16,'Reviewed : '.$this->reviewed, 0, 0, 0, true, 'C', true);
                $html='<hr/>';
                $this->writeHTMLCell(0, 0,10,32, $html, 0, 0, 0, true, 'J', true);
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
        
        $pdf->setHeading($arr['logo'],$arr['header'],$bResult[0]['package_code'],$resulted,$reviewed);
        
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
        $pdf->SetMargins(PDF_MARGIN_LEFT, 36, PDF_MARGIN_RIGHT);
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
    
    $tbl = '<table cellspacing="0" cellpadding="3" style="width:100%;border-top:1px solid #333;border-bottom:1px solid #333;">
            <thead>
                <tr nobr="true">
                    <td align="center" width="6%"><strong>Pos.</strong></td>
                    <td align="center" width="27%"><strong>Sample ID</strong></td>
                    <td align="center" width="39%"><strong>Barcode</strong></td>
                    <td align="center" width="14%"><strong>LOT NO <br>DATE</strong></td>
                    <td align="center" width="14%"><strong>VL Result</strong></td>
                </tr>
            </thead>';
    $tbl.='</table>';
    $sampleCounter = 1;
        $sQuery="SELECT sample_code,lot_number,lot_expiration_date,result from package_details as pd  Join vl_request_form as vl ON vl.sample_package_id=pd.package_id where pd.package_id=$id";
        $result=$db->query($sQuery);
        foreach($result as $sample){
            $params = $pdf->serializeTCPDFtagParameters(array($sample['sample_code'], 'C39', '', '','' ,7, 0.25,array('border'=>false,'align' => 'C','padding'=>1, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>false, 'font'=>'helvetica', 'fontsize'=>10, 'stretchtext'=>2),'N'));
            $lotDetails = '';
            $lotExpirationDate = '';
            if(isset($sample['lot_expiration_date']) && $sample['lot_expiration_date'] != '' && $sample['lot_expiration_date']!= NULL && $sample['lot_expiration_date'] != '0000-00-00'){
                if(trim($sample['lot_number'])!= ''){
                    $lotExpirationDate.='<br>';
                }
                $lotExpirationDate.= $general->humanDateFormat($sample['lot_expiration_date']);
            }
            $lotDetails = $sample['lot_number'].$lotExpirationDate;
            
            $tbl.='<table cellspacing="0" cellpadding="3" style="width:100%">';
            $tbl.='<tr>';
            $tbl.='<td align="center" width="6%" style="vertical-align:middle;border-bottom:1px solid #333;">'.$sampleCounter.'.</td>';
            $tbl.='<td align="center" width="27%" style="vertical-align:middle;border-bottom:1px solid #333;">'.$sample['sample_code'].'</td>';
            $tbl.='<td align="center" width="39%" style="vertical-align:middle;border-bottom:1px solid #333;"><tcpdf method="write1DBarcode" params="'.$params.'" /></td>';
            $tbl.='<td align="center" width="14%" style="vertical-align:middle;border-bottom:1px solid #333;">'.$lotDetails.'</td>';
            $tbl.='<td align="center" width="14%" style="vertical-align:middle;border-bottom:1px solid #333;">'.$sample['result'].'</td>';
            $tbl.='</tr>';
            $tbl .='</table>';
          $sampleCounter++;
       } 
    
    $pdf->writeHTMLCell('', '', 12,$pdf->getY(),$tbl, 0, 1, 0, true, 'C', true);
    $filename = trim($bResult[0]['package_code']).'.pdf';
    $pdf->Output('.././uploads'. DIRECTORY_SEPARATOR.'package_barcode'. DIRECTORY_SEPARATOR.$filename, "F");
    echo $filename;
  }
}
?>