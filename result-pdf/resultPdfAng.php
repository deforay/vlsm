<?php
ob_start();
$tableName1="activity_log";
$tableName2="vl_request_form";
$general=new Deforay_Commons_General();

$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
if(isset($arr['default_time_zone']) && $arr['default_time_zone']!=''){
  date_default_timezone_set($arr['default_time_zone']);
}else{
  date_default_timezone_set("Europe/London");
}
//set mField Array
$mFieldArray = array();
if(isset($arr['r_mandatory_fields']) && trim($arr['r_mandatory_fields'])!= ''){
  $mFieldArray = explode(',',$arr['r_mandatory_fields']);
}
//set print time
$printedTime = date('Y-m-d H:i:s');
$expStr=explode(" ",$printedTime);
$printDate =$general->humanDateFormat($expStr[0]);
$printDateTime = $expStr[1];
//set query
if(isset($_POST['id']) && trim($_POST['id'])!=''){
  if(isset($_POST['resultMail'])){
    $searchQuery="SELECT vl.*,f.*,rst.*,l.facility_name as labName,rsrr.rejection_reason_name FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as rst ON rst.sample_id=vl.sample_type LEFT JOIN facility_details as l ON l.facility_id=vl.lab_id LEFT JOIN r_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection where vl.vl_sample_id IN(".$_POST['id'].")";
  }else{
    $searchQuery = $_SESSION['vlResultQuery']." and vl.vl_sample_id IN(".$_POST['id'].")";
  }
}else{
  $searchQuery = $_SESSION['vlRequestSearchResultQuery'];
}
//error_log($searchQuery);
$requestResult=$db->query($searchQuery);
$_SESSION['nbPages'] = sizeof($requestResult);
$_SESSION['aliasPage'] = 1;
class MYPDFANG extends TCPDF {
      //Page header
      public function setHeading($logo,$text,$lab) {
        $this->logo = $logo;
        //$this->text = $text;
        //$this->lab = $lab;
      }
      //Page header
      public function Header() {
          // Logo
          //$image_file = K_PATH_IMAGES.'logo_example.jpg';
          //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
          // Set font
          if(trim($this->logo)!=''){
              if (file_exists('../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo)) {
                $image_file = '../uploads'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR.$this->logo;
                $this->Image($image_file,95, 3, 15, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
              }
          }
          //$this->SetFont('helvetica', 'B', 7);
          //$this->writeHTMLCell(30,0,16,28,$this->text, 0, 0, 0, true, 'A', true);
          $this->SetFont('helvetica', '', 7);
          $this->writeHTMLCell(0,0,10,18,'República de Angola', 0, 0, 0, true, 'C', true);
          $this->SetFont('helvetica', '', 7);
          $this->writeHTMLCell(0,0,10,22,'Ministério da Saúde', 0, 0, 0, true, 'C', true);
          $this->SetFont('helvetica', '', 7);
          $this->writeHTMLCell(0,0,10,26,'Instituto Nacional de Luta contra a SIDA', 0, 0, 0, true, 'C', true);
          $this->SetFont('helvetica', 'B', 8);
          $this->writeHTMLCell(0,0,10,30,'RELATÓRIO DE RESULTADOS DE QUANTIFICAÇÃO DE CARGA VIRAL DE VIH', 0, 0, 0, true, 'C', true);
          //if(trim($this->lab)!= ''){
           // $this->SetFont('helvetica', '', 9);
            //$this->writeHTMLCell(0,0,10,26,strtoupper($this->lab), 0, 0, 0, true, 'C', true);
          //}
          $this->writeHTMLCell(0,0,15,36,'<hr>', 0, 0, 0, true, 'C', true);
      }
  
      // Page footer
      public function Footer() {
          // Position at 15 mm from bottom
          $this->SetY(-15);
          // Set font
          $this->SetFont('helvetica', '', 8);
          // Page number
          $this->Cell(0, 10, 'Page'.$_SESSION['aliasPage'].'/'.$_SESSION['nbPages'], 0, false, 'C', 0, '', 0,false, 'T', 'M');
      }
  }
  
  class PDF_RotateANG extends FPDI {
  
    var $angle = 0;
    
    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle*=M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
    
    function _endpage() {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
  }
  
  class WatermarkANG extends PDF_RotateANG {
  
    var $_tplIdx;
    
    function Header() {
        global $fullPathToFile;
    
        //Put the watermark
        $this->SetFont('helvetica', 'B', 50);
        $this->SetTextColor(148,162,204);
        $this->RotatedText(67,119,'DRAFT',45);
    
        if (is_null($this->_tplIdx)) {
            // THIS IS WHERE YOU GET THE NUMBER OF PAGES
            $this->numPages = $this->setSourceFile($fullPathToFile);
            $this->_tplIdx = $this->importPage(1);
        }
        $this->useTemplate($this->_tplIdx, 0, 0, 200);
    }
    
    function RotatedText($x, $y, $txt, $angle) {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
        //$this->SetAlpha(0.7);
    }
  }
  class Pdf_concatANG extends FPDI {
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
$resultFilename = '';
if(sizeof($requestResult)> 0){
    $_SESSION['rVal'] = $general->generateRandomString(6);
    if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal'])) {
      mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_SESSION['rVal']);
    }
    $pathFront = realpath('../uploads/'.$_SESSION['rVal'].'/');
    $pages = array();
    $page = 1;
    foreach($requestResult as $result){
        $_SESSION['aliasPage'] = $page;
        if(!isset($result['labName'])){
          $result['labName'] = '';
        }
        $draftTextShow = false;
        //Set watermark text
        for($m=0;$m<count($mFieldArray);$m++){
          if(!isset($result[$mFieldArray[$m]]) || trim($result[$mFieldArray[$m]]) == '' || $result[$mFieldArray[$m]] == null || $result[$mFieldArray[$m]] == '0000-00-00 00:00:00'){
            $draftTextShow = true;
            break;
          }
        }
        // create new PDF document
        $pdf = new MYPDFANG(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT,true, 'UTF-8', false);
        $pdf->setHeading($arr['logo'],$arr['header'],$result['labName']);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        //$pdf->SetAuthor('Pal');
        $pdf->SetTitle('Viral Load Test Result');
        //$pdf->SetSubject('TCPDF Tutorial');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '',PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '',PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP+14,PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

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

        $pdf->AddPage();
        if(!isset($result['facility_code']) || trim($result['facility_code']) == ''){
           $result['facility_code'] = '';
        }
        if(!isset($result['facility_state']) || trim($result['facility_state']) == ''){
           $result['facility_state'] = '';
        }
        if(!isset($result['facility_district']) || trim($result['facility_district']) == ''){
           $result['facility_district'] = '';
        }
        if(!isset($result['facility_name']) || trim($result['facility_name']) == ''){
           $result['facility_name'] = '';
        }
        if(!isset($result['labName']) || trim($result['labName']) == ''){
           $result['labName'] = '';
        }
        //Set Age
        $age = 'Unknown';
        if(isset($result['patient_dob']) && trim($result['patient_dob'])!='' && $result['patient_dob']!='0000-00-00'){
          $dob = $general->humanDateFormat($result['patient_dob']);
        }
        if(isset($result['patient_age_in_months']) && trim($result['patient_age_in_months'])!='' && trim($result['patient_age_in_months']) >0){
          if($result['patient_age_in_months'] > 1){
            $age = $result['patient_age_in_months'].' months';
          }else{
            $age = $result['patient_age_in_months'].' month';
          }
        }

        if(isset($result['sample_collection_date']) && trim($result['sample_collection_date'])!='' && $result['sample_collection_date']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['sample_collection_date']);
          $result['sample_collection_date']=$general->humanDateFormat($expStr[0]);
          $sampleCollectionTime = $expStr[1];
        }else{
          $result['sample_collection_date']='';
          $sampleCollectionTime = '';
        }
        $sampleReceivedDate='';
        $sampleReceivedTime='';
        if(isset($result['sample_received_at_vl_lab_datetime']) && trim($result['sample_received_at_vl_lab_datetime'])!='' && $result['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['sample_received_at_vl_lab_datetime']);
          $sampleReceivedDate=$general->humanDateFormat($expStr[0]);
          $sampleReceivedTime =$expStr[1];
        }

        if(isset($result['sample_tested_datetime']) && trim($result['sample_tested_datetime'])!='' && $result['sample_tested_datetime']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['sample_tested_datetime']);
          $result['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
        }else{
          $result['sample_tested_datetime']='';
        }
        if(isset($result['result_dispatched_datetime']) && trim($result['result_dispatched_datetime'])!='' && $result['result_dispatched_datetime']!='0000-00-00 00:00:00'){
          $expStr=explode(" ",$result['result_dispatched_datetime']);
          $result['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
        }else{
          $result['result_dispatched_datetime']='';
        }

        if(isset($result['last_viral_load_date']) && trim($result['last_viral_load_date'])!='' && $result['last_viral_load_date']!='0000-00-00'){
          $result['last_viral_load_date']=$general->humanDateFormat($result['last_viral_load_date']);
        }else{
          $result['last_viral_load_date']='';
        }
        if(!isset($result['patient_gender']) || trim($result['patient_gender'])== ''){
          $result['patient_gender'] = 'not reported';
        }
        if(isset($result['approvedBy']) && trim($result['approvedBy'])!=''){
          $resultApprovedBy = ucwords($result['approvedBy']);
        }else{
          $resultApprovedBy  = '';
        }
        $lastVlDate = '';$lastVlResult='';
        if($result['reason_for_vl_testing']=='routine'){
            $lastVlDate = (isset($result['last_vl_date_routine']) && $result['last_vl_date_routine']!='') ? $general->humanDateFormat($result['last_vl_date_routine']) :  '';
            $lastVlResult = (isset($result['last_vl_result_routine']) && $result['last_vl_result_routine']!='') ? $result['last_vl_result_routine'] :  '';
        }else if($result['reason_for_vl_testing']=='expose'){
            $lastVlDate = (isset($result['last_vl_date_ecd']) && $result['last_vl_date_ecd']!='') ? $general->humanDateFormat($result['last_vl_date_ecd']) :  '';
            $lastVlResult = (isset($result['last_vl_result_ecd']) && $result['last_vl_result_ecd']!='') ? $result['last_vl_result_ecd'] :  '';
        }else if($result['reason_for_vl_testing']=='suspect'){
            $lastVlDate = (isset($result['last_vl_date_failure']) && $result['last_vl_date_failure']!='') ? $general->humanDateFormat($result['last_vl_date_failure']) :  '';
            $lastVlResult = (isset($result['last_vl_result_failure']) && $result['last_vl_result_failure']!='') ? $result['last_vl_result_failure'] :  '';
        }else if($result['reason_for_vl_testing']=='repetition'){
            $lastVlDate = (isset($result['last_vl_date_failure_ac']) && $result['last_vl_date_failure_ac']!='') ? $general->humanDateFormat($result['last_vl_date_failure_ac']) :  '';
            $lastVlResult = (isset($result['last_vl_result_ac']) && $result['last_vl_result_ac']!='') ? $result['last_vl_result_ac'] :  '';
        }else if($result['reason_for_vl_testing']=='repetition'){
            $lastVlDate = (isset($result['last_vl_date_cf']) && $result['last_vl_date_cf']!='') ? $general->humanDateFormat($result['last_vl_date_cf']) :  '';
            $lastVlResult = (isset($result['last_vl_result_cf']) && $result['last_vl_result_cf']!='') ? $result['last_vl_result_cf'] :  '';
        }else if($result['reason_for_vl_testing']=='repetition'){
            $lastVlDate = (isset($result['last_vl_date_if']) && $result['last_vl_date_if']!='') ? $general->humanDateFormat($result['last_vl_date_if']) :  '';
            $lastVlResult = (isset($result['last_vl_result_if']) && $result['last_vl_result_if']!='') ? $result['last_vl_result_if'] :  '';
        }
        $vlResult = '';
        $smileyContent = '';
        $showMessage = '';
        $tndMessage = '';
        $color  = '';
        $messageTextSize = '12px';
        if($result['result']!= NULL && trim($result['result'])!= '') {
          $resultType = is_numeric($result['result']);
          if(in_array(strtolower(trim($result['result'])),array("tnd","target not detected"))){
            $vlResult = 'TND*';
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
            $showMessage = ucfirst($arr['l_vl_msg']);
            $tndMessage = 'TND* - Target not Detected';
          }else if(in_array(strtolower(trim($result['result'])),array("failed","fail","no_sample"))){
            $vlResult = $result['result'];
            $smileyContent = '';
            $showMessage = '';
            $messageTextSize = '14px';
          }else if(trim($result['result']) > 1000 && $result['result']<=10000000){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_frown.png" alt="frown_face"/>';
            $showMessage = ucfirst($arr['h_vl_msg']);
            $color ="#0000FF";
            $messageTextSize = '15px';
          }else if(trim($result['result']) <= 1000 && $result['result']>=20){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
            $showMessage = ucfirst($arr['l_vl_msg']);
            $color ="#008000";
          }else if(trim($result['result'] > 10000000) && $resultType){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_frown.png" alt="frown_face"/>';
            //$showMessage = 'Value outside machine detection limit';
          }else if(trim($result['result'] < 20) && $resultType){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
            //$showMessage = 'Value outside machine detection limit';
          }else if(trim($result['result'])=='<20'){
            $vlResult = '&lt;20';
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
            $showMessage = ucfirst($arr['l_vl_msg']);
            $color ="#008000";
          }else if(trim($result['result'])=='>10000000'){
            $vlResult = $result['result'];
            $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_frown.png" alt="frown_face"/>';
            $showMessage = ucfirst($arr['h_vl_msg']);
            $color ="#0000FF";
          }else if($result['vl_test_platform']=='Roche'){
            $chkSign = '';
            $smileyShow = '';
            $chkSign = strchr($result['result'],'>');
            if($chkSign!=''){
              $smileyShow =str_replace(">","",$result['result']);
              $vlResult = $result['result'];
              //$showMessage = 'Invalid value';
            }
            $chkSign = '';
            $chkSign = strchr($result['result'],'<');
            if($chkSign!=''){
              $smileyShow =str_replace("<","",$result['result']);
              $vlResult = str_replace("<","&lt;",$result['result']);
              //$showMessage = 'Invalid value';
            }
            if($smileyShow!='' && $smileyShow <= $arr['viral_load_threshold_limit']){
              $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_smile.png" alt="smile_face"/>';
            }else if($smileyShow!='' && $smileyShow > $arr['viral_load_threshold_limit']){
              $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/smiley_frown.png" alt="frown_face"/>';
            }
          }
        }
        if(isset($arr['show_smiley']) && trim($arr['show_smiley']) == "no"){
          $smileyContent = '';
        }
        if($result['result_status']=='4'){
          $smileyContent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../assets/img/cross.png" alt="rejected"/>';
        }
        //limits
        $limit = '';
        if($result['vl_test_platform']!=''){
        $lQuery = "Select lower_limit,higher_limit from import_config where machine_name='".$result['vl_test_platform']."'";
        $lResult = $db->query($lQuery);
          if(isset($lResult[0]['lower_limit']) && $lResult[0]['lower_limit']!='' && $lResult[0]['lower_limit']!=NULL){
            $limit = "Lower Limit:&nbsp;&nbsp;".$lResult[0]['lower_limit']."<br/>"."Higher Limit:&nbsp;".$lResult[0]['higher_limit'];
          }
        }
        $html = '';
        $html .='<b style="font-size:12px;">A. UNIDADE DE SOLICITAÇÃO</b><br/>';
            $html.='<table style="border-spacing: 3px;border:1px solid #000;">';
              $html .='<tr>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Nome da Unidade</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['facility_name']).'</td>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Município</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['facility_district']).'</td>';
               $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Serviço/Sector</td>';
               $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.ucwords($result['requesting_vl_service_sector']).'</td>';
              $html .='</tr>';
              $html .='<tr>';
              $html .='<td colspan="6" style="line-height:10px;"></td>';
             $html .='</tr>';
              $html .='<tr>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Província</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.ucwords($result['facility_state']).'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Nome do solicitante</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.ucwords($result['request_clinician_name']).'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Contacto</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$result['requesting_phone'].'</td>';
             $html .='</tr>';
            $html.='</table>';
            $html .='<b style="font-size:12px;">B. DADOS DO PACIENTE</b><br/>';
            $html.='<table style="border-spacing: 3px;border:1px solid #000;">';
              $html .='<tr>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Nome completo</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['patient_first_name']).'</td>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Nº Processo Clínico</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.$result['patient_art_no'].'</td>';
               $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Género</td>';
               $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.ucwords(str_replace("_"," ",$result['patient_gender'])).'</td>';
              $html .='</tr>';
              $html .='<tr>';
              $html .='<td colspan="6" style="line-height:10px;"></td>';
             $html .='</tr>';
              $html .='<tr>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Data de nascimento</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$dob.'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Idade (em meses se &lt;1ano)</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$age.'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Nome da Mãe/ Pai/ Familiar responsável</td>';
              $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['patient_responsible_person']).'</td>';
             $html .='</tr>';
             $html .='<tr>';
             $html .='<td colspan="6" style="line-height:10px;"></td>';
            $html .='</tr>';
             $html .='<tr>';
             $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Município</td>';
             $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['patient_district']).'</td>';
             $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Província</td>';
             $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['patient_province']).'</td>';
             $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Contacto</td>';
             $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$result['patient_mobile_number'].'</td>';
            $html .='</tr>';
            $html .='<tr>';
            $html .='<td colspan="6" style="line-height:10px;"></td>';
           $html .='</tr>';
            $html .='<tr>';
            $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Autoriza contacto</td>';
            $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['consent_to_receive_sms']).'</td>';
           $html .='</tr>';
            $html.='</table>';
            $html .='<b style="font-size:12px;">C. DADOS DA TESTAGEM</b><br/>';
            $html.='<table style="border-spacing: 3px;border:1px solid #000;">';
              $html .='<tr>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Nº da amostra</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['sample_code']).'</td>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Tipo de amostra</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['sample_name']).'</td>';
               $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Data da colheita de amostra</td>';
               $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$result['sample_collection_date'].'</td>';
              $html .='</tr>';
              $html .='<tr>';
              $html .='<td colspan="6" style="line-height:10px;"></td>';
             $html .='</tr>';
              $html .='<tr>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Data de envio da amostra</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$result['result_dispatched_datetime'].'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Data de recepção da amostra</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$sampleReceivedDate.'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Data da quantificação de CV</td>';
              $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.$result['sample_tested_datetime'].'</td>';
             $html .='</tr>';
            $html.='</table>';
            $html .='<b style="font-size:12px;">D. RESULTADOS DA QUANTIFICAÇÃO DA CARGA VIRAL</b><br/>';
            $html.='<table style="border-spacing: 3px;border:1px solid #000;">';
              $html .='<tr>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Plataforma usada</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['vl_test_platform']).'</td>';
               $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Limites de Detecção</td>';
               $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.$limit.'</td>';
               $html .='<td rowspan="2">'.$smileyContent.'</td>';
              $html .='</tr><br/>';
              $html .='<tr>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Resultado de Carga Viral (cópias/mL)</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$result['result'].'</td>';
              $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Resultado de Carga Viral (Log10)</td>';
              $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.$result['result_value_log'].'</td>';
              $html .='</tr>';
            $html.='</table>';
            if($showMessage!=''){
            $html .='<b style="font-size:12px;">E. RECOMENDAÇÕES</b><br/>';
            $html.='<table style="border-spacing: 5px;border:1px solid #000;">';
              $html .='<tr>';
               $html .='<td colspan="6" style="line-height:10px;font-size:10px;text-align:left;color:'.$color.'">'.$showMessage.'</td>';
              $html .='</tr>';
            $html.='</table>';
            }
            $html .='<b style="font-size:12px;">F. HISTÓRICO DE CARGA VIRAL</b><br/>';
            $html.='<table style="border-spacing: 3px;border:1px solid #000;">';
            $html .='<tr>';
            $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Data da quantificação</td>';
            $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Resultado (cp/mL)</td>';
            $html .='<td style="line-height:11px;font-size:11px;font-weight:bold;text-align:left;">Unidade de Saúde</td>';
           $html .='</tr>';
           $html .='<tr>';
           $lastDate = '';$lastResult = '';
           if($result['last_vl_date_routine']!='' && $result['last_vl_date_routine']!=NULL && $result['last_vl_date_routine']!='0000-00-00'){
            $lastDate = $general->humanDateFormat($result['last_vl_date_routine']);
            $lastResult = $result['last_vl_result_routine'];
           }else if($result['last_vl_date_ecd']!='' && $result['last_vl_date_ecd']!=NULL && $result['last_vl_date_ecd']!='0000-00-00'){
            $lastDate = $general->humanDateFormat($result['last_vl_date_ecd']);
            $lastResult = $result['last_vl_result_ecd'];
           }else if($result['last_vl_date_failure']!='' && $result['last_vl_date_failure']!=NULL && $result['last_vl_date_failure']!='0000-00-00'){
            $lastDate = $general->humanDateFormat($result['last_vl_date_failure']);
            $lastResult = $result['last_vl_result_failure'];
           }else if($result['last_vl_date_failure_ac']!='' && $result['last_vl_date_failure_ac']!=NULL && $result['last_vl_date_failure_ac']!='0000-00-00'){
            $lastDate = $general->humanDateFormat($result['last_vl_date_failure_ac']);
            $lastResult = $result['last_vl_result_failure_ac'];
           }else if($result['last_vl_date_cf']!='' && $result['last_vl_date_cf']!=NULL && $result['last_vl_date_cf']!='0000-00-00'){
            $lastDate = $general->humanDateFormat($result['last_vl_date_cf']);
            $lastResult = $result['last_vl_result_cf'];
           }else if($result['last_vl_date_if']!='' && $result['last_vl_date_if']!=NULL && $result['last_vl_date_if']!='0000-00-00'){
            $lastDate = $general->humanDateFormat($result['last_vl_date_if']);
            $lastResult = $result['last_vl_result_if'];
           }
            $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$lastDate.'</td>';
            $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$lastResult.'</td>';
            $html .='<td style="line-height:11px;font-size:11px;text-align:left;">'.$result["facility_name"].'</td>';
           $html .='</tr>';
          $html.='</table>';
          $html.='<table style="padding:10px;">';
          $html .='<tr>';
           $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Laboratório executor</td>';
           $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['labName']).'</td>';
           $html .='<td style="line-height:10px;font-size:10px;font-weight:bold;text-align:left;">Técnico executor</td>';
           $html .='<td style="line-height:10px;font-size:10px;text-align:left;">'.ucwords($result['lab_contact_person']).'</td>';
          $html .='</tr>';
          $html .='<tr>';
          $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Técnico responsável</td>';
          $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.ucwords($result['vl_focal_person']).'</td>';
          $html .='<td style="line-height:11px;font-size:10px;font-weight:bold;text-align:left;">Data do relatório</td>';
          $html .='<td style="line-height:11px;font-size:10px;text-align:left;">'.date('d-M-Y').'</td>';
         $html .='</tr>';
        $html.='</table>';
        if($result['result']!=''){
          $pdf->writeHTML($html);
          $pdf->lastPage();
          $filename = $pathFront. DIRECTORY_SEPARATOR .'p'.$page. '.pdf';
          $pdf->Output($filename,"F");
          if($draftTextShow){
            //Watermark section
            $watermark = new WatermarkANG();
            $fullPathToFile = $filename;
            $watermark->Output($filename,"F");
          }
          $pages[] = $filename;
        $page++;
        }
      if(isset($_POST['source']) && trim($_POST['source']) == 'print'){
        //Add event log
        $eventType = 'print-result';
        $action = ucwords($_SESSION['userName']).' print the test result with patient code '.$result['patient_art_no'];
        $resource = 'print-test-result';
        $data=array(
        'event_type'=>$eventType,
        'action'=>$action,
        'resource'=>$resource,
        'date_time'=>$general->getDateTime()
        );
        $db->insert($tableName1,$data);
        //Update print datetime in VL tbl.
        $db=$db->where('vl_sample_id',$result['vl_sample_id']);
        $db->update($tableName2,array('result_printed_datetime'=>$general->getDateTime()));
      }
    }

    if(count($pages) >0){
        $resultPdf = new Pdf_concatANG();
        $resultPdf->setFiles($pages);
        $resultPdf->setPrintHeader(false);
        $resultPdf->setPrintFooter(false);
        $resultPdf->concat();
        $resultFilename = 'vl-test-result'.date("d-M-Y-H-i-s").'.pdf';
        $resultPdf->Output(UPLOAD_PATH. DIRECTORY_SEPARATOR.$resultFilename, "F");
        $general->removeDirectory($pathFront);
        unset($_SESSION['rVal']);
    }
}

echo $resultFilename;
?>