  <?php
    ob_start();
    include('../General.php');
    //get province list
    $pdQuery="SELECT * from province_details";
    $pdResult=$db->query($pdQuery);
    //get lab facility list
    $fQuery="SELECT * FROM facility_details where status='active'";
    $fResult = $db->rawQuery($fQuery);
    //get lab facility details
    $lQuery="SELECT * FROM facility_details where facility_type='2'";
    $lResult = $db->rawQuery($lQuery);
    //get import config
      $importQuery="SELECT * FROM import_config WHERE status = 'active'";
      $importResult=$db->query($importQuery);
      
      $userQuery="SELECT * FROM user_details where status='active'";
      $userResult = $db->rawQuery($userQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionResult = $db->rawQuery($rejectionQuery);

    $artRegimenQuery="SELECT DISTINCT headings FROM r_art_code_details WHERE nation_identifier ='ang'";
    $artRegimenResult = $db->rawQuery($artRegimenQuery);
    $province = "";
    $province.="<option value=''> -- Selecione -- </option>";
    foreach($pdResult as $provinceName){
      $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
    }
    $facility = "";
    $facility.="<option value=''> -- Selecione -- </option>";
    foreach($fResult as $fDetails){
      $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
    }
    
    //get ART list
    $aQuery="SELECT * from r_art_code_details";// where nation_identifier='drc'";
    $aResult=$db->query($aQuery);
    //get Sample type
    $sQuery="SELECT * from r_sample_type where status='active'";
    $sResult=$db->query($sQuery);
    //global config
    $cSampleQuery="SELECT * FROM global_config";
    $cSampleResult=$db->query($cSampleQuery);
    $arr = array();
    for ($i = 0; $i < sizeof($cSampleResult); $i++) {
      $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
    }
  if($arr['sample_code']=='MMYY'){
    $mnthYr = date('my');
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-31');
  }else if($arr['sample_code']=='YY'){
    $mnthYr = date('y');
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
  }  
  
  //$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="3" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
  $svlQuery='SELECT sample_code_key FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" ORDER BY vl_sample_id DESC LIMIT 1';
  $svlResult=$db->query($svlQuery);
  
  $prefix = $arr['sample_code_prefix'];
  if($svlResult[0]['sample_code_key']!='' && $svlResult[0]['sample_code_key']!=NULL){
   $maxId = $svlResult[0]['sample_code_key']+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }
  $sKey = '';
  $sFormat = '';
    ?>
    <style>
      .ui_tpicker_second_label {
       display: none !important;
      }
      .ui_tpicker_second_slider {
       display: none !important;
      }.ui_tpicker_millisec_label {
       display: none !important;
      }.ui_tpicker_millisec_slider {
       display: none !important;
      }.ui_tpicker_microsec_label {
       display: none !important;
      }.ui_tpicker_microsec_slider {
       display: none !important;
      }.ui_tpicker_timezone_label {
       display: none !important;
      }.ui_tpicker_timezone {
       display: none !important;
      }.ui_tpicker_time_input{
       width:100%;
      }
      .translate-content{
        color:#0000FF;
        font-size:12.5px;
      }
   </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add Vl Request</li>
      </ol> 
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-inline" method="post" name="addVlRequestForm" id="addVlRequestForm" autocomplete="off" action="addVlRequestHelperAng.php">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                          <h3 class="box-title">SOLICITAÇÃO DE QUANTIFICAÇÃO DE CARGA VIRAL DE VIH</h3>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">A.UNIDADE DE</h3>
                            </div>
                            <table class="table" style="width:100%">
                                <tr>
                                    <td><label for="province">Província </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="province" id="province" title="Please choose província" onchange="getfacilityDetails(this);" style="width:100%;">
                                            <?php echo $province; ?>
                                        </select>
                                    </td>
                                    <td><label for="district">Município </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="district" id="district" title="Please choose município" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                          <option value=""> -- Selecione -- </option>
                                        </select>
                                    </td>
                                    <td><label for="clinicName">Nome da Unidade </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="clinicName" id="clinicName" title="Please choose Nome da Unidade" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                                          <?php echo $facility;  ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="sector">Serviço/Sector </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="sector" id="sector" placeholder="Serviço/Sector" title="Please enter Serviço/Sector"/>
                                    </td>
                                    <td><label for="reqClinician">Nome do solicitante </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="reqClinician" id="reqClinician" placeholder="Nome do solicitante" title="Please enter Nome do solicitante"/>
                                    </td>
                                    <td><label for="category">Categoria </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control" name="category" id="category" title="Please choose Categoria" style="width:100%;">
                                          <option value="">-- Selecione --</option>
                                          <option value="nurse">Enfermeiro/a</option>
                                          <option value="clinician">Médico/a</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="profNumber">Nº da Ordem </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="profNumber" id="profNumber" placeholder="Nº da Ordem" title="Please enter Nº da Ordem"/>
                                    </td>
                                    <td><label for="contactNo">Contacto </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="contactNo" id="contactNo" placeholder="Contacto" title="Please enter Contacto"/>
                                    </td>
                                    <td><label for="requestDate">Data da solicitação </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control date" name="requestDate" id="requestDate" placeholder="Data da solicitação" title="Please choose Data da solicitação"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header with-border">
                               <!-- <h3 class="box-title">Information sur le patient </h3>&nbsp;&nbsp;&nbsp;
                            <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Code du patient" title="Please enter code du patient"/>&nbsp;&nbsp;
                            <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Patient Found</b></span>-->
                            <h4>B. DADOS DO PACIENTE</h4>
                            </div>
                            <table class="table" style="width:100%">
                                <tr>
                                    <td style="width:14%;"><label for="patientFirstName">Nome completo </label></td>
                                    <td style="width:14%;">
                                        <input type="text" class="form-control " id="patientFirstName" name="patientFirstName" placeholder="Nome completo" title="Please enter Nome completo" style="width:100%;"/>
                                    </td>
                                    <td style="width:14%;"><label for="patientArtNo">Nº Processo Clínico </label></td>
                                    <td style="width:14%;">
                                        <input type="text" class="form-control " id="patientArtNo" name="patientArtNo" placeholder="Nº Processo Clínico" title="Please enter Nº Processo Clínico" style="width:100%;" onchange="checkNameValidation('vl_request_form','patient_art_no',this,null)"/>
                                    </td>
                                    <td><label for="sex">Género </label></td>
                                    <td style="width:16%;">
                                        <label class="radio-inline" style="padding-left:10px !important;margin-left:0;">Masculino</label>
                                        <label class="radio-inline" style="width:2%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check sexe">
                                        </label>
                                        <label class="radio-inline" style="padding-left:10px !important;margin-left:0;">Feminino</label>
                                        <label class="radio-inline" style="width:2%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check sexe">
                                        </label>
                                    </td>
                                    <td style="width:14%;"><label for="ageInMonths">Data de nascimento </label></td>
                                    <td style="width:14%;">
                                        <input type="text" class="form-control date" id="dob" name="dob" placeholder="Data de nascimento" title="Please enter Data de nascimento" onchange="setDobMonthYear();" style="width:100%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="ageInMonths"> Idade (em meses se < 1 ano) </label></td>
                                    <td>
                                        <input type="text" class="form-control checkNum" id="ageInMonths" name="ageInMonths" placeholder="Mois" title="Please enter àge en mois" style="width:100%;"/>
                                    </td>
                                    <td colspan="3"><label for="responsiblePersonName">Nome da Mãe/ Pai/ Familiar responsáve </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="responsiblePersonName" name="responsiblePersonName" placeholder="Nome da Mãe/ Pai/ Familiar responsáve" title="Please enter Nome da Mãe/ Pai/ Familiar responsáve" style="width:100%;" />
                                    </td>
                                    <td><label for="patientDistrict">Município </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="patientDistrict" name="patientDistrict" placeholder="Município" title="Please enter Município" style="width:100%;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td ><label for="patientProvince">Província </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="patientProvince" name="patientProvince" placeholder="Província" title="Please enter Província" style="width:100%;" />
                                    </td>
                                    <td><label for="patientPhoneNumber">Contacto </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Contacto" title="Please enter Contacto" style="width:100%;"/>
                                    </td>
                                    <td><label for="consentReceiveSms">Autoriza contacto </label></td>
                                    <td style="width:16%;">
                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Sim</label>
                                        <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="consentReceiveSmsYes" name="consentReceiveSms" value="yes" title="Please check Autoriza contacto">
                                        </label>
                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Não</label>
                                        <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="consentReceiveSmsNo" name="consentReceiveSms" value="no" title="Please check Autoriza contacto">
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">C. INFORMAÇÃO DE TRATAMENTO</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:14%;"><label for="">Data de início de TARV </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control date" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="e.g 09-Jan-1992" title="Please select Data de início de TARV"  style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="ageInYears"> Esquema de TARV actual </label></td>
                                <td style="width:14%;">
                                    <select class="form-control " id="artRegimen" name="artRegimen" placeholder="Esquema de TARV actual" title="Please enter Esquema de TARV actual" style="width:100%;"  onchange="checkARTValue();">
                                          <option value="">-- Select --</option>
                                        <?php foreach($artRegimenResult as $heading) { ?>
                                        <optgroup label="<?php echo ucwords($heading['headings']); ?>">
                                          <?php
                                          foreach($aResult as $regimen){
                                            if($heading['headings'] == $regimen['headings']){
                                            ?>
                                            <option value="<?php echo $regimen['art_code']; ?>"><?php echo $regimen['art_code']; ?></option>
                                            <?php
                                            }
                                          }
                                          ?>
                                        </optgroup>
                                        <?php } ?>
                                        <option value="other">Other</option>
                                  </select>
                                  <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" style="width:100%;display:none;margin-top:2px;" >
                                </td>
                                <td><label for="sex">Linha de TARV actua </label></td>
                                <td style="width:32%;">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Primeira</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="lineTrtFirst" name="lineTreatment" value="1" title="Please check Linha de TARV actua">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Segunda</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="lineTrtSecond" name="lineTreatment" value="2" title="Please check Linha de TARV actua">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Terceira</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="lineTrtThird" name="lineTreatment" value="3" title="Please check Linha de TARV actua">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><label for="sex">Se o paciente está em 2ª ou 3ª linha de TARV, indique o tipo de falência </label></td>
                                <td colspan="3">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">N/A</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentNoResult" name="lineTreatmentRefType" value="na" title="Please check indique o tipo de falência">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Virológica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentVirological" name="lineTreatmentRefType" value="virological" title="Please check indique o tipo de falência">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Imunológica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentimmunological" name="lineTreatmentRefType" value="immunological" title="Please check indique o tipo de falência">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Clínica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentClinical" name="lineTreatmentRefType" value="clinical" title="Please check indique o tipo de falência">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">Refira em que grupo(s) o paciente se enquadra</td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGeneralPopulation" name="patientGroup" value="general_population" title="Please check População geral">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">População geral (adulto, criança ou mulheres não grávidas)</label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientKeyPopulation" name="patientGroup" value="key_population" title="Please check População chave – especifique">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">População chave – especifique</label>
                                    
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">HSH/Trans</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGroupKeyMSM" name="patientGroupKeyOption" value="msm" title="Please check HSH/Trans">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">TS</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGroupKeySW" name="patientGroupKeyOption" value="sw" title="Please check TS">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Outro</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGroupKeyOther" name="patientGroupKeyOption" value="other" title="Please check Outro">
                                    </label>
                                    <input type="text" class="form-control" name="patientGroupKeyOtherText" id="patientGroupKeyOtherText" title="Please enter value"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientPregnantWoman" name="patientGroup" value="pregnant" title="Please check Mulher gestante">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Mulher gestante – indique a data provável do parto</label>
                                    <input type="text" class="form-control date" name="patientPregnantWomanDate" id="patientPregnantWomanDate" placeholder="e.g 09-Jan-1992" title="Please enter data provável do parto"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="breastFeeding" name="patientGroup" value="breast_feeding" title="Please check Mulher lactante">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Mulher lactante</label>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">D. INDICAÇÃO PARA SOLICITAÇÃO DE CARGA VIRAL</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Monitoria de rotina</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="routineMonitoring" name="indicateVlTesing" value="routine" title="Please check Monitoria de rotina">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Diagnóstico de criança exposta </label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="exposeChild" name="indicateVlTesing" value="exposed_child_diagnoss" title="Please check Diagnóstico de criança exposta">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Suspeita de falência de tratamento</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="suspectedTreatment" name="indicateVlTesing" value="suspected_treatment_failure" title="Please check Suspeita de falência de tratamento">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Repetição após CV≥ 1000 cp/mL</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="repetition" name="indicateVlTesing" value="repetition" title="Please check Repetição após CV≥ 1000 cp/mL">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Falência clínica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="clinicalFailure" name="indicateVlTesing" value="clinical_failure" title="Please check Falência clínica">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Falência imunológica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="immunologicalFailure" name="indicateVlTesing" value="immunological_failure" title="Please check Falência imunológica">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:14%;"><label for="">Se aplicável: data da última carga viral </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control date" id="lastVlDate" name="lastVlDate" placeholder="e.g 09-Jan-1992" title="Please select data da última carga viral" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="lastVlResult"> Resultado da última carga vira </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="lastVlResult" name="lastVlResult" placeholder="Resultado da última carga vira" title="Please enter Resultado da última carga vira" style="width:100%;"/>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">E. UNIDADE DE COLHEITA</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:14%;"><label for="fName">Nome da Unidade de colheita (se diferente da Unidade de solicitação) </label></td>
                                <td style="width:14%;">
                                    <select class="form-control isRequired" name="fName" id="fName" title="Please choose Nome de colheita" style="width:100%;" >
                                          <?php echo $facility;  ?>
                                    </select>
                                </td>
                                <td style="width:14%;"><label for="ageInYears"> Local de colheita </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control checkNum" id="ageInYears" name="ageInYears" placeholder="Aannées" title="Please enter àge en années" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="sampleCollectionDate"> Data Hora de colheita </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control dateTime" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Data Hora de colheita" title="Please enter Data Hora de colheita" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:14%;"><label for="reqClinician">Responsável pela colheita </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="reqClinician" name="reqClinician" placeholder="Responsável pela colheita" title="Please select Responsável pela colheita" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="reqClinicianPhoneNumber"> Contacto </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" placeholder="Contacto" title="Please enter Contacto" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="sampleType"> Tipo de amostra </label></td>
                                <td style="width:14%;">
                                    <select name="specimenType" id="specimenType" class="form-control" title="Please choose Tipo de amostra">
                                      <option value="">-- Selecione --</option>
                                      <?php
                                        foreach($sResult as $name){
                                         ?>
                                         <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                                         <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:14%;"><label for="">Data da recepção da amostra </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control dateTime" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="e.g 09-Jan-1992" title="Please select Data da recepção da amostra" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="ageInYears"> Responsável da recepção </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control checkNum" id="ageInYears" name="ageInYears" placeholder="Aannées" title="Please enter àge en années" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="sampleCode">  Nº de amostra </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="sampleCode" name="sampleCode" placeholder="Nº de amostra" title="Please enter Nº de amostra" style="width:100%;"/>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                    <div class="box-header with-border">
                      <h3 class="box-title">Laboratory Information</h3>
                    </div>
                    <table class="table" style="width:100%">
                      <tr>
                          <td style="width:14%;"><label for="">Lab Name</label></td>
                          <td style="width:14%;">
                              <select name="labId" id="labId" class="form-control" title="Please choose lab">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($lResult as $labName){
                                  ?>
                                  <option value="<?php echo $labName['facility_id'];?>"><?php echo ucwords($labName['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                          </td>
                          <td style="width:14%;"><label for="ageInYears"> VL Testing Platform </label></td>
                          <td style="width:14%;">
                              <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                                <option value="">-- Select --</option>
                                <?php foreach($importResult as $mName) { ?>
                                  <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"><?php echo $mName['machine_name'];?></option>
                                  <?php
                                }
                                ?>
                              </select>
                          </td>
                          <td style="width:14%;"><label for="sampleReceivedOn">  Date Sample Received at Testing Lab </label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control dateTime" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received Date" title="Please select sample received date"/>
                          </td>
                      </tr>
                      <tr>
                          <td style="width:14%;"><label for="">Sample Testing Date</label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date"/>
                          </td>
                          <td style="width:14%;"><label for="resultDispatchedOn"> Date Results Dispatched </label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control dateTime" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date"/>
                          </td>
                          <td style="width:14%;"><label for="sampleCode"> Sample Rejection</label></td>
                          <td style="width:14%;">
                              <label class="radio-inline">
                               <input class="" id="noResultYes" name="noResult" value="yes" title="Please check one" type="radio"> Yes
                              </label>
                              <label class="radio-inline">
                               <input class="" id="noResultNo" name="noResult" value="no" title="Please check one" type="radio"> No
                              </label>
                          </td>
                      </tr>
                    </table>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-md-4 rejectionReason" style="display:none;">
                            <label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason </label>
                            <div class="col-lg-7">
                              <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" onchange="checkRejectionReason();">
                                <option value="">-- Select --</option>
                                <?php foreach($rejectionTypeResult as $type) { ?>
                                <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                  <?php
                                  foreach($rejectionResult as $reject){
                                    if($type['rejection_type'] == $reject['rejection_type']){
                                    ?>
                                    <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                    <?php
                                    }
                                  }
                                  ?>
                                </optgroup>
                                <?php } ?>
                                <option value="other">Other (Please Specify) </option>
                              </select>
                              <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">
                            </div>
                        </div>
                        <div class="col-md-4 vlResult">
                            <label class="col-lg-5 control-label" for="vlResult">Viral Load Result (copiesl/ml) </label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" style="width:100%;" onchange="calculateLogValue(this)"/>
                              <input type="checkbox" class="" id="tnd" name="tnd" value="yes" title="Please check tnd"> Target Not Detected<br>
                              <input type="checkbox" class="" id="bdl" name="bdl" value="yes" title="Please check bdl"> Below Detection Level
                            </div>
                        </div>
                        <div class="col-md-4 vlResult">
                            <label class="col-lg-5 control-label" for="vlLog">Viral Load Log </label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Viral Load Log" title="Please enter viral load log" style="width:100%;" onchange="calculateLogValue(this);"/>
                            </div>
                        </div>
                        
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
                            <div class="col-lg-7">
                              <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($userResult as $uName){
                                  ?>
                                  <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$_SESSION['userId'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="col-lg-2 control-label" for="labComments">Laboratory Scientist Comments </label>
                            <div class="col-lg-10">
                              <textarea class="form-control" name="labComments" id="labComments" placeholder="Lab comments" style="width:100%"></textarea>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                    </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code'];?>"/>
                <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                <?php } ?>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="formId" id="formId" value="8"/>
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  <script type="text/javascript">
     changeProvince = true;
     changeFacility = true;
      provinceName = true;
      facilityName = true;
      machineName = true;
     $(document).ready(function() {
        $('.date').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-M-yy',
        yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
       }).click(function(){
           $('.ui-datepicker-calendar').show();
        });
        
        $('.dateTime').datetimepicker({
          changeMonth: true,
          changeYear: true,
          dateFormat: 'dd-M-yy',
          timeFormat: "HH:mm",
          yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
          }).click(function(){
   	    $('.ui-datepicker-calendar').show();
          });
        
        $('.date').mask('99-aaa-9999');
        $('.dateTime').mask('99-aaa-9999 99:99');
     });
     
    function getfacilityDetails(obj){
       $.blockUI();
       var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(pName!='' && provinceName && facilityName){
        facilityName = false;
      }
      if($.trim(pName)!=''){
        if(provinceName){
            $.post("../includes/getFacilityForClinic.php", { pName : pName},
            function(data){
                if(data!= ""){
                  details = data.split("###");
                  $("#clinicName").html(details[0]);
                  $("#district").html(details[1]);
                  //$("#clinicianName").val(details[2]);
                }
            });
        }
        <?php
      if($arr['sample_code']=='auto'){
        ?>
        pNameVal = pName.split("##");
        sCode = '<?php echo date('ymd');?>';
        sCodeKey = '<?php echo $maxId;?>';
        $("#sampleCode").val(pNameVal[1]+sCode+sCodeKey);
        $("#sampleCodeFormat").val(pNameVal[1]+sCode);
        $("#sampleCodeKey").val(sCodeKey);
        <?php
      }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
        $("#sampleCode").val('<?php echo $prefix.$mnthYr.$maxId;?>');
        $("#sampleCodeFormat").val('<?php echo $prefix.$mnthYr;?>');
        $("#sampleCodeKey").val('<?php echo $maxId;?>');
        <?php
      }
      ?>
      }else if(pName=='' && cName==''){
        provinceName = true;
        facilityName = true;
        $("#province").html("<?php echo $province;?>");
        $("#clinicName").html("<?php echo $facility;?>");
      }else{
        $("#district").html("<option value=''> -- Selecione -- </option>");
      }
       $.unblockUI();
    }
    
    function getfacilityDistrictwise(obj){
      $.blockUI();
      var dName = $("#district").val();
      var cName = $("#clinicName").val();
      if(dName!=''){
        $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
        function(data){
            if(data != ""){
              $("#clinicName").html(data);
            }
        });
      }else{
         $("#clinicName").html("<option value=''> -- Selecione -- </option>");
      }
      $.unblockUI();
    }
    function getfacilityProvinceDetails(obj)
    {
      $.blockUI();
       //check facility name
        var cName = $("#clinicName").val();
        var pName = $("#province").val();
        if(cName!='' && provinceName && facilityName){
          provinceName = false;
        }
      if(cName!='' && facilityName){
        $.post("../includes/getFacilityForClinic.php", { cName : cName},
        function(data){
            if(data != ""){
              details = data.split("###");
              $("#province").html(details[0]);
              $("#district").html(details[1]);
              //$("#clinicianName").val(details[2]);
            }
        });
      }else if(pName=='' && cName==''){
        provinceName = true;
        facilityName = true;
        $("#province").html("<?php echo $province;?>");
        $("#clinicName").html("<?php echo $facility;?>");
      }
      $.unblockUI();
    }
    
    function checkCurrentRegimen(){
      var currentRegimen = $("#artRegimen").val();
      if(currentRegimen == "other"){
        $(".newArtRegimen").show();
      }else{
        $(".newArtRegimen").hide();
      }
    }
    
    $("input:radio[name=isPatientNew]").click(function() {
      if($(this).val() == 'yes'){
        $(".du").css("visibility","visible");
      }else if($(this).val() == 'no'){
        $(".du").css("visibility","hidden");
      }
    });
    
    $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'female'){
         $("#femaleElements").show();
      }else if($(this).val() == 'male'){
        $("#femaleElements").hide();
      }
    });
    
    $("input:radio[name=hasChangedRegimen]").click(function() {
      if($(this).val() == 'yes'){
         $(".arvChangedElement").show();
      }else if($(this).val() == 'no'){
        $(".arvChangedElement").hide();
      }
    });
    
    function checkVLTestReason(){
      var vlTestReason = $("#vlTestReason").val();
      if(vlTestReason == "other"){
        $(".newVlTestReason").show();
      }else{
        $(".newVlTestReason").hide();
      }
    }
    
    function checkSpecimenType(){
      var specimenType = $("#specimenType").val();
      if(specimenType == 2){
        $(".plasmaElement").show();
      }else{
        $(".plasmaElement").hide();
      }
    }
    
    function checkTestStatus(){
      var status = $("#status").val();
      if(status == 4){
        $(".rejectionReason").show();
      }else{
        $(".rejectionReason").hide();
      }
    }
    
    function checkRejectionReason(){
      var rejectionReason = $("#rejectionReason").val();
      if(rejectionReason == "other"){
        $(".newRejectionReason").show();
      }else{
        $(".newRejectionReason").hide();
      }
    }
    
    function setDobMonthYear(){
      var today = new Date();
      var dob = $("#dob").val();
      if($.trim(dob) == ""){
        $("#ageInMonths").val("");
        $("#ageInYears").val("");
        return false;
      }
      var dd = today.getDate();
      var mm = today.getMonth();
      var yyyy = today.getFullYear();
      if(dd<10) {
        dd='0'+dd
      } 
      
      if(mm<10) {
        mm='0'+mm
      }
      
      splitDob = dob.split("-");
      var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
      var monthDigit = dobDate.getMonth();
      var dobYear = splitDob[2];
      var dobMonth = isNaN(monthDigit) ? 0 : (monthDigit);
      dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
      var dobDate = (splitDob[0]<10) ? '0'+splitDob[0]: splitDob[0];
      
      var date1 = new Date(yyyy,mm,dd);
      var date2 = new Date(dobYear,dobMonth,dobDate);
      var diff = new Date(date1.getTime() - date2.getTime());
      if((diff.getUTCFullYear() - 1970) == 0){
        $("#ageInMonths").val((diff.getUTCMonth() > 0)? diff.getUTCMonth(): ''); // Gives month count of difference
      }else{
        $("#ageInMonths").val("");
      }
      $("#ageInYears").val((diff.getUTCFullYear() - 1970 > 0)? (diff.getUTCFullYear() - 1970) : ''); // Gives difference as year
    }
    
    function checkSampleReceviedDate(){
      var sampleCollectionDate = $("#sampleCollectionDate").val();
      var sampleReceivedDate = $("#sampleReceivedDate").val();
      if($.trim(sampleCollectionDate)!= '' && $.trim(sampleReceivedDate)!= '') {
        //Set sample coll. datetime
        splitSampleCollDateTime = sampleCollectionDate.split(" ");
        splitSampleCollDate = splitSampleCollDateTime[0].split("-");
        var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2]+", "+splitSampleCollDate[0]);
        var monthDigit = sampleCollOn.getMonth();
        var smplCollYear = splitSampleCollDate[2];
        var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplCollMonth = (smplCollMonth<10) ? '0'+smplCollMonth: smplCollMonth;
        var smplCollDate = splitSampleCollDate[0];
        sampleCollDateTime = smplCollYear+"-"+smplCollMonth+"-"+smplCollDate+" "+splitSampleCollDateTime[1]+":00";
        //Set sample rece. datetime
        splitSampleReceivedDateTime = sampleReceivedDate.split(" ");
        splitSampleReceivedDate = splitSampleReceivedDateTime[0].split("-");
        var sampleReceivedOn = new Date(splitSampleReceivedDate[1] + splitSampleReceivedDate[2]+", "+splitSampleReceivedDate[0]);
        var monthDigit = sampleReceivedOn.getMonth();
        var smplReceivedYear = splitSampleReceivedDate[2];
        var smplReceivedMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplReceivedMonth = (smplReceivedMonth<10) ? '0'+smplReceivedMonth: smplReceivedMonth;
        var smplReceivedDate = splitSampleReceivedDate[0];
        sampleReceivedDateTime = smplReceivedYear+"-"+smplReceivedMonth+"-"+smplReceivedDate+" "+splitSampleReceivedDateTime[1]+":00";
        //Check diff
        if(moment(sampleCollDateTime).diff(moment(sampleReceivedDateTime)) > 0) {
          alert("L'échantillon de données reçues ne peut pas être antérieur à la date de collecte de l'échantillon!");
          $("#sampleReceivedDate").val("");
        }
      }
    }
    
    function checkSampleTestingDate(){
      var sampleCollectionDate = $("#sampleCollectionDate").val();
      var sampleTestingDate = $("#sampleTestingDateAtLab").val();
      if($.trim(sampleCollectionDate)!= '' && $.trim(sampleTestingDate)!= '') {
        //Set sample coll. date
        splitSampleCollDateTime = sampleCollectionDate.split(" ");
        splitSampleCollDate = splitSampleCollDateTime[0].split("-");
        var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2]+", "+splitSampleCollDate[0]);
        var monthDigit = sampleCollOn.getMonth();
        var smplCollYear = splitSampleCollDate[2];
        var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplCollMonth = (smplCollMonth<10) ? '0'+smplCollMonth: smplCollMonth;
        var smplCollDate = splitSampleCollDate[0];
        sampleCollDateTime = smplCollYear+"-"+smplCollMonth+"-"+smplCollDate+" "+splitSampleCollDateTime[1]+":00";
        //Set sample testing date
        splitSampleTestedDateTime = sampleTestingDate.split(" ");
        splitSampleTestedDate = splitSampleTestedDateTime[0].split("-");
        var sampleTestingOn = new Date(splitSampleTestedDate[1] + splitSampleTestedDate[2]+", "+splitSampleTestedDate[0]);
        var monthDigit = sampleTestingOn.getMonth();
        var smplTestingYear = splitSampleTestedDate[2];
        var smplTestingMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplTestingMonth = (smplTestingMonth<10) ? '0'+smplTestingMonth: smplTestingMonth;
        var smplTestingDate = splitSampleTestedDate[0];
        sampleTestingAtLabDateTime = smplTestingYear+"-"+smplTestingMonth+"-"+smplTestingDate+" "+splitSampleTestedDateTime[1]+":00";
        //Check diff
        if(moment(sampleCollDateTime).diff(moment(sampleTestingAtLabDateTime)) > 0) {
          alert("La date d'essai de l'échantillon ne peut pas être antérieure à la date de collecte de l'échantillon!");
          $("#sampleTestingDateAtLab").val("");
        }
      }
    }
    
    function checkARTInitiationDate(){
      var dob = $("#dob").val();
      var artInitiationDate = $("#dateOfArtInitiation").val();
      if($.trim(dob)!= '' && $.trim(artInitiationDate)!= '') {
        //Set DOB date
        splitDob = dob.split("-");
        var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
        var monthDigit = dobDate.getMonth();
        var dobYear = splitDob[2];
        var dobMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
        var dobDate = splitDob[0];
        dobDate = dobYear+"-"+dobMonth+"-"+dobDate;
        //Set ART initiation date
        splitArtIniDate = artInitiationDate.split("-");
        var artInigOn = new Date(splitArtIniDate[1] + splitArtIniDate[2]+", "+splitArtIniDate[0]);
        var monthDigit = artInigOn.getMonth();
        var artIniYear = splitArtIniDate[2];
        var artIniMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        artIniMonth = (artIniMonth<10) ? '0'+artIniMonth: artIniMonth;
        var artIniDate = splitArtIniDate[0];
        artIniDate = artIniYear+"-"+artIniMonth+"-"+artIniDate;
        //Check diff
        if(moment(dobDate).isAfter(artIniDate)) {
          alert("La date d'ouverture de l'ART ne peut pas être antérieure à!");
          $("#dateOfArtInitiation").val("");
        }
      }
    }
    
    function checkLastVLTestDate(){
      var artInitiationDate = $("#dateOfArtInitiation").val();
      var dateOfLastVLTest = $("#lastViralLoadTestDate").val();
      if($.trim(artInitiationDate)!= '' && $.trim(dateOfLastVLTest)!= '') {
        //Set ART initiation date
        splitArtIniDate = artInitiationDate.split("-");
        var artInigOn = new Date(splitArtIniDate[1] + splitArtIniDate[2]+", "+splitArtIniDate[0]);
        var monthDigit = artInigOn.getMonth();
        var artIniYear = splitArtIniDate[2];
        var artIniMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        artIniMonth = (artIniMonth<10) ? '0'+artIniMonth: artIniMonth;
        var artIniDate = splitArtIniDate[0];
        artIniDate = artIniYear+"-"+artIniMonth+"-"+artIniDate;
        //Set Last VL Test date
        splitLastVLTestDate = dateOfLastVLTest.split("-");
        var lastVLTestOn = new Date(splitLastVLTestDate[1] + splitLastVLTestDate[2]+", "+splitLastVLTestDate[0]);
        var monthDigit = lastVLTestOn.getMonth();
        var lastVLTestYear = splitLastVLTestDate[2];
        var lastVLTestMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        lastVLTestMonth = (lastVLTestMonth<10) ? '0'+lastVLTestMonth: lastVLTestMonth;
        var lastVLTestDate = splitLastVLTestDate[0];
        lastVLTestDate = lastVLTestYear+"-"+lastVLTestMonth+"-"+lastVLTestDate;
        //console.log(artIniDate);
        //console.log(lastVLTestDate);
        //Check diff
        if(moment(artIniDate).isAfter(lastVLTestDate)) {
          alert("Dernier test de charge virale Les données ne peuvent pas être antérieures à la date d'initiation de l'ARV!");
          $("#lastViralLoadTestDate").val("");
        }
      }
    }
    
    function calculateLogValue(obj){
      if(obj.id=="vlResult") {
        absValue = $("#vlResult").val();
        if(absValue!='' && absValue!=0){
          $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
        }
      }
      if(obj.id=="vlLog") {
        logValue = $("#vlLog").val();
        if(logValue!='' && logValue!=0){
          var absVal = Math.round(Math.pow(10,logValue) * 100) / 100;
          if(absVal!='Infinity'){
          $("#vlResult").val(Math.round(Math.pow(10,logValue) * 100) / 100);
          }else{
            $("#vlResult").val('');
          }
        }
      }
    }
    
    function validateNow(){
      flag = deforayValidator.init({
        formId: 'addVlRequestForm'
      });
      if(flag){
        $.blockUI();
        document.getElementById('addVlRequestForm').submit();
      }
    }
    function checkNameValidation(tableName,fieldName,obj,fnct)
    {
      if($.trim(obj.value)!=''){
        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : obj.value,fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                showModal('patientModal.php?artNo='+obj.value,900,520);
            }
        });
      }
    }
  function setPatientDetails(pDetails){
      patientArray = pDetails.split("##");
      if($.trim(patientArray[3])!=''){
        $("#dob").val(patientArray[3]);
        getAge();
      }else if($.trim(patientArray[4])!='' && $.trim(patientArray[4]) != 0){
        $("#ageInYears").val(patientArray[4]);
      }else if($.trim(patientArray[5])!=''){
        $("#ageInMonths").val(patientArray[5]);
      }
      
      
      if($.trim(patientArray[2])!=''){
        if(patientArray[2] == 'male'){
        $("#genderMale").prop('checked', true);
        }else if(patientArray[2] == 'female'){
          $("#genderFemale").prop('checked', true);
        }
      }
      if($.trim(patientArray[15])!=''){
      $("#patientArtNo").val($.trim(patientArray[15]));
      }
  }
  function getAge(){
    var dob = $("#dob").val();
    if($.trim(dob) == ""){
      $("#ageInMonths").val("");
      $("#ageInYears").val("");
      return false;
    }
    //calculate age
    splitDob = dob.split("-");
    var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
    var monthDigit = dobDate.getMonth();
    var dobMonth = isNaN(monthDigit) ? 1 : (parseInt(monthDigit)+parseInt(1));
    dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
    dob = splitDob[2]+'-'+dobMonth+'-'+splitDob[0];
    var years = moment().diff(dob, 'years',false);
    var months = (years == 0)?moment().diff(dob, 'months',false):'';
    $("#ageInYears").val(years); // Gives difference as years
    $("#ageInMonths").val(months); // Gives difference as months
  }
  function showPatientList()
  {
    $("#showEmptyResult").hide();
      if($.trim($("#artPatientNo").val())!=''){
        $.post("checkPatientExist.php", { artPatientNo : $("#artPatientNo").val()},
        function(data){
            if(data >= '1'){
                showModal('patientModal.php?artNo='+$.trim($("#artPatientNo").val()),900,520);
            }else{
              $("#showEmptyResult").show();
            }
        });
      }
  }
  function checkARTValue(){
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $("#newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $("#newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
      $('#newArtRegimen').val("");
    }
  }
  function calculateLogValue(obj){
    if(obj.id=="vlResult") {
      absValue = $("#vlResult").val();
      if(absValue!='' && absValue!=0 && !isNaN(absValue)){
        $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
      }else{
        $("#vlLog").val('');
      }
    }
    if(obj.id=="vlLog") {
      logValue = $("#vlLog").val();
      if(logValue!='' && logValue!=0 && !isNaN(logValue)){
        var absVal = Math.round(Math.pow(10,logValue) * 100) / 100;
        if(absVal!='Infinity'){
          $("#vlResult").val(Math.round(Math.pow(10,logValue) * 100) / 100);
        }
      }else{
        $("#vlResult").val('');
      }
    }
  }
  $("input:radio[name=noResult]").click(function() {
    if($(this).val() == 'yes'){
      $('.rejectionReason').show();
      $('.vlResult').css('visibility','hidden');
      $('#rejectionReason').addClass('isRequired');
    }else{
      $('.vlResult').css('visibility','visible');
      $('.rejectionReason').hide();
      $('#rejectionReason').removeClass('isRequired');
      $('#rejectionReason').val('');
    }
  });
  $("input:radio[name=lineTreatment]").click(function() {
    if($(this).val() == '1'){
      $('.lineTreatmentRefType').attr("disabled",true);
    }else{
      $('.lineTreatmentRefType').attr("disabled",false);
    }
  });
  
  $('#tnd').change(function() {
    if($('#tnd').is(':checked')){
      $('#vlResult,#vlLog').attr('readonly',true);
      $('#bdl').attr('disabled',true);
    }else{
      $('#vlResult,#vlLog').attr('readonly',false);
      $('#bdl').attr('disabled',false);
    }
  });
  $('#bdl').change(function() {
    if($('#bdl').is(':checked')){
      $('#vlResult,#vlLog').attr('readonly',true);
      $('#tnd').attr('disabled',true);
    }else{
      $('#vlResult,#vlLog').attr('readonly',false);
      $('#tnd').attr('disabled',false);
    }
  });
  
  $('#vlResult,#vlLog').on('input',function(e){
    if(this.value != ''){
      $('#tnd').attr('disabled',true);
      $('#bdl').attr('disabled',true);
    }else{
      $('#tnd').attr('disabled',false);
      $('#bdl').attr('disabled',false);
    }
  });
  </script>
  
 <?php
 //include('../footer.php');
 ?>
