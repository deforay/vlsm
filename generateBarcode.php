<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
include ('./includes/tcpdf/tcpdf.php');
$id=base64_decode($_POST['id']);

if($id>0){
    if (!file_exists('uploads') && !is_dir('uploads')) {
        mkdir('uploads');
    }
        
    if (!file_exists('uploads'. DIRECTORY_SEPARATOR . "barcode") && !is_dir('uploads'. DIRECTORY_SEPARATOR."barcode")) {
        mkdir('uploads'. DIRECTORY_SEPARATOR."barcode");
    }
    
    $query="SELECT * from batch_details where batch_id=$id";
    $bResult=$db->query($query);
    
    $fQuery="SELECT treament_id,sample_code from vl_request_form where batch_id=$id";
    $result=$db->query($fQuery);
    
    
    if(count($result)>0){
        // Extend the TCPDF class to create custom Header and Footer
        class MYPDF extends TCPDF {
            public function setHeading($header) {
                $this->header = $header;
            }
            //Page header
            public function Header() {
                // Logo
                //$image_file = K_PATH_IMAGES.'logo_example.jpg';
                //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // Set font
                $this->SetFont('helvetica','B',18);
                $this->writeHTMLCell(0,0,0, 5, "Generate Barcode ".$this->header." Batch Id", 0, 0, 0, true, 'C', true);
                $html='<hr/>';
                $this->writeHTMLCell(0, 0,10,20, $html, 0, 0, 0, true, 'J', true);
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
        
        $pdf->setHeading(ucwords($bResult[0]['batch_code']));
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('Generate Barcode');
        $pdf->SetSubject('Barcode');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
    
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
        
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
    
        // set font
        $pdf->SetFont('times', 'BI', 12);
    
        // add a page
        $pdf->AddPage();
    
        // define barcode style
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => true,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'times',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        
        foreach($result as $val){
            $pdf->write1DBarcode($val['sample_code'],'C39','','','',18, 0.4, $style,'N');
            $pdf->Ln();
        }
    
        $filename = $bResult[0]['batch_code'].'.pdf';
        $pdf->Output('uploads'. DIRECTORY_SEPARATOR.'barcode'. DIRECTORY_SEPARATOR.$filename, "F");
        echo $filename;
    }
}
?>