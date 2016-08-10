<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
include ('./includes/tcpdf/tcpdf.php');
include ('./includes/fpdi/fpdi.php');
define('UPLOAD_PATH','uploads');
$general=new Deforay_Commons_General();

$requestResult=$db->query($_SESSION['vlRequestSearchResultQuery']);
$_SESSION['nbPages'] = sizeof($requestResult);
$_SESSION['aliasPage'] = 1;
//print_r($requestResult);die;
$pdfNew = new TCPDF();
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
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$_SESSION['aliasPage'].'/'.$_SESSION['nbPages'], 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
                  $this->AddPage('P', array($s['w'], $s['h']));
                  $this->useTemplate($tplidx);
             }
        }
    }
}

if(sizeof($requestResult)> 0){
    $configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
    
    $_SESSION['rVal'] = $general->generateRandomString(6);
    if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal'])) {
      mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']);
    }
    $pathFront = realpath('./uploads/'.$_SESSION['rVal'].'/');
    
    $pages = array();
    $page = 1;
    foreach($requestResult as $result){
        $_SESSION['aliasPage'] = $page;
        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        //$pdf->SetAuthor('Saravanan');
        $pdf->SetTitle('Vl Request Result');
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
        $pdf->SetFont('helveticaI', '', 18);
        
        $pdf->AddPage();
        
        $html = "";
        if(isset($result['sample_collection_date']) && trim($result['sample_collection_date'])!='' && $result['sample_collection_date']!='0000-00-00'){
          $xplodSampleCollectionDate = explode(" ",$result['sample_collection_date']);  
         $result['sample_collection_date']=$general->humanDateFormat($xplodSampleCollectionDate[0]);
        }else{
         $result['sample_collection_date']='N/A';
        }
        if(isset($result['date_of_initiation_of_current_regimen']) && trim($result['date_of_initiation_of_current_regimen'])!='' && $result['date_of_initiation_of_current_regimen']!='0000-00-00'){
         $result['date_of_initiation_of_current_regimen']=$general->humanDateFormat($result['date_of_initiation_of_current_regimen']);
        }else{
         $result['date_of_initiation_of_current_regimen']='N/A';
        }
        if(isset($result['date_sample_received_at_testing_lab']) && trim($result['date_sample_received_at_testing_lab'])!='' && $result['date_sample_received_at_testing_lab']!='0000-00-00'){
         $result['date_sample_received_at_testing_lab']=$general->humanDateFormat($result['date_sample_received_at_testing_lab']);
        }else{
         $result['date_sample_received_at_testing_lab']='N/A';
        }
        if(isset($result['lab_tested_date']) && trim($result['lab_tested_date'])!='' && $result['lab_tested_date']!='0000-00-00'){
         $result['lab_tested_date']=$general->humanDateFormat($result['lab_tested_date']);
        }else{
         $result['lab_tested_date']='N/A';
        }
        if(isset($result['result_reviewed_date']) && trim($result['result_reviewed_date'])!='' && $result['result_reviewed_date']!='0000-00-00'){
         $result['result_reviewed_date']=$general->humanDateFormat($result['result_reviewed_date']);
        }else{
         $result['result_reviewed_date']='N/A';
        }
        $age = "";
        if(isset($result['age_in_yrs']) && trim($result['age_in_yrs'])!=''){
           $age = $result['age_in_yrs'];
        }else{
          if(isset($result['patient_dob']) && trim($result['patient_dob'])!='' && $result['patient_dob']!='0000-00-00'){
            $todayDate = strtotime(date('Y-m-d'));
            $dob = strtotime($result['patient_dob']);
            $difference = $todayDate - $dob;
            $seconds_per_year = 60*60*24*365;
            $age = round($difference / $seconds_per_year);
          }
        }
        
        $html .= '<div style="border:1px solid #333;">';
        $html.='<table style="padding:2px;">';
            if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
              $html .='<tr>';
                $html .='<td colspan="4" style="text-align:center;"><img src="uploads/logo/'.$arr['logo'].'" style="width:80px;height:80px;" alt="logo"></td>';
              $html .='</tr>';
            }
            
            if(isset($arr['header']) && trim($arr['header'])!= '') {
              $html .='<tr>';
                $html .='<td colspan="4" style="text-align:center;font-size:16px;">'.ucwords($arr['header']).'</td>';
              $html .='</tr>';
            }
            $html .='<tr style="line-height:30px;">';
              $html .='<td colspan="2" style="text-align:left;font-size:12px;"><strong>Dispensary</strong></td>';
              $html .='<td colspan="2" style="text-align:left;font-size:12px;"><strong>LAB: '.ucfirst($result['lab_name']).'</strong></td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td colspan="2" style="text-align:center;font-size:14px;"><strong>Viral Load Results</strong></td>';
              $html .='<td colspan="2" style="text-align:center;font-size:14px;"><strong>Historical Information</strong></td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Patient CCC No</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['art_no'].'</td>';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Sample Type</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['sample_name'].'</td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Date Collected</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['sample_collection_date'].'</td>';
              $html .='<td style="text-align:left;font-size:12px;"><strong>ART Intiation Date</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['date_of_initiation_of_current_regimen'].'</td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Date Received</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['date_sample_received_at_testing_lab'].'</td>';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Current Regimen</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['art_code'].'</td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Date Tested</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['lab_tested_date'].'</td>';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Justification</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['justification'].'</td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Age</strong></td>';
              $html .='<td colspan="3" style="text-align:left;font-size:12px;">'.$age.'</td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:14px;"><strong>Test Result</strong></td>';
              $html .='<td colspan="3" style="text-align:left;font-size:12px;"><strong>'.$result['result'].'</strong></td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:14px;"><strong>Comments</strong></td>';
              $html .='<td colspan="3" style="text-align:left;font-size:12px;"><strong>'.ucfirst($result['comments']).'</strong></td>';
            $html .='</tr>';
            $html .='<tr style="line-height:30px;">';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Result Reviewed By</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.ucfirst($result['result_reviewed_by']).'</td>';
              $html .='<td style="text-align:left;font-size:12px;"><strong>Date Reviewed</strong></td>';
              $html .='<td style="text-align:left;font-size:12px;">'.$result['result_reviewed_date'].'</td>';
            $html .='</tr>';
        $html.='</table>';
        $html .= "</div>";
        
        $pdf->writeHTML($html);
        $pdf->lastPage();
        $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
        $pdf->Output($filename,"F");
        $pages[] = $filename;
      $page++;
    }
    
    $resultFilename = '';
    if(count($pages) >0){
        $resultPdf = new Pdf_concat();
        $resultPdf->setFiles($pages);
        $resultPdf->concat();
        $resultFilename = 'vl-request-result-' . date('d-M-Y-H-i-s') . '.pdf';
        $resultPdf->Output(UPLOAD_PATH. DIRECTORY_SEPARATOR .$resultFilename, "F");
        $general->removeDirectory($pathFront);
        unset($_SESSION['rVal']);
    }
    
}

echo $resultFilename;
?>