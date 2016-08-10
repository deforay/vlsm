<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
include ('./includes/tcpdf/tcpdf.php');

$id=base64_decode($_POST['id']);
if($id>0){
    $fQuery="SELECT treament_id,sample_code from vl_request_form where treament_id=$id";
    $result=$db->query($fQuery);
    if(count($result)>0){
    
        if (!file_exists('uploads') && !is_dir('uploads')) {
          mkdir('uploads');
        }
        
        if (!file_exists('uploads'. DIRECTORY_SEPARATOR . "barcode") && !is_dir('uploads'. DIRECTORY_SEPARATOR."barcode")) {
          mkdir('uploads'. DIRECTORY_SEPARATOR."barcode");
        }
    
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle($result[0]['sample_code']);
        $pdf->SetSubject($result[0]['sample_code']);
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        
        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
    
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
    
        // ---------------------------------------------------------
        // set a barcode on the page footer
        //$pdf->setBarcode(date('Y-m-d H:i:s'));
    
        // set font
        $pdf->SetFont('times','',10);
        
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
    
        // PRINT VARIOUS 1D BARCODES
        
        // CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
        //$pdf->Cell(0, 0, 'CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9', 0, 1);
        $pdf->write1DBarcode($result[0]['sample_code'],'C39','','','',18, 0.4, $style,'N');
        
        $pdf->Output('uploads'. DIRECTORY_SEPARATOR.'barcode'. DIRECTORY_SEPARATOR .$result[0]['sample_code'].'.pdf',"F");
        echo $result[0]['sample_code'].'.pdf';
    }
}
?>