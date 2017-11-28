  <?php
  ob_start();
  //check remote user
$rKey = '';
$pdQuery="SELECT * from province_details";
  if($sarr['user_type']=='remoteuser'){
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='".$_SESSION['userId']."'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if($chkUserFcMapResult){
    $pdQuery="SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='".$_SESSION['userId']."'";
    }
    $rKey = 'R';
  }else{
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
  }
$pdResult=$db->query($pdQuery);
  $province = "";
  $province.="<option value=''> -- Sélectionner -- </option>";
  foreach($pdResult as $provinceName){
    $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
  }
  $facility = "";
  $facility.="<option value=''> -- Sélectionner -- </option>";
  foreach($fResult as $fDetails){
    $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
  }
  //get ART list
  $aQuery="SELECT * from r_art_code_details";// where nation_identifier='drc'";
  $aResult=$db->query($aQuery);
  $start_date = date('Y-01-01');
  $end_date = date('Y-12-31');
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
  $svlQuery='SELECT '.$sampleCodeKey.' FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" AND '.$sampleCode.'!="" ORDER BY vl_sample_id DESC LIMIT 1';
  $svlResult=$db->query($svlQuery);
  $prefix = $arr['sample_code_prefix'];
  if($svlResult[0][$sampleCodeKey]!='' && $svlResult[0][$sampleCodeKey]!=NULL){
   $maxId = $svlResult[0][$sampleCodeKey]+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }
  $sKey = ''; $sFormat = '';
  ?>
  <style> .translate-content{  color:#0000FF; font-size:12.5px; }  </style>
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
            <form class="form-inline" method="post" name="addVlRequestForm" id="addVlRequestForm" autocomplete="off" action="addVlRequestHelperDrc.php">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                          <h3 class="box-title">1. Réservé à la structure de soins</h3>
                        </div>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information sur la structure de soins</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                        <?php echo $province; ?>
                                    </select>
                                </td>
                                <td><label for="district">Zone de santé </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                      <option value=""> -- Sélectionner -- </option>
                                    </select>
                                </td>
                                <td><label for="clinicName">Structure/Service </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="clinicName" id="clinicName" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                                      <?php echo $facility;  ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="clinicianName">Demandeur </label></td>
                                <td>
                                    <input type="text" class="form-control" id="clinicianName" name="clinicianName" placeholder="Demandeur" title="Please enter demandeur" style="width:100%;"/>
                                </td>
                                <td><label for="clinicanTelephone">Téléphone </label></td>
                                <td>
                                    <input type="text" class="form-control checkNum" id="clinicanTelephone" name="clinicanTelephone" placeholder="Téléphone" title="Please enter téléphone" style="width:100%;"/>
                                </td>
                                <td><label for="supportPartner">Partenaire dappui </label></td>
                                <td>
                                    <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date de la demande </label></td>
                                <td colspan="5">
                                    <input type="text" class="form-control date" id="dateOfDemand" name="dateOfDemand" placeholder="e.g 09-Jan-1992" title="Please enter date de la demande" style="width:21%;"/>
                                </td>
                            </tr>
                        </table>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information sur le patient </h3>&nbsp;&nbsp;&nbsp;
                        <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Code du patient" title="Please enter code du patient"/>&nbsp;&nbsp;
                        <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Patient Found</b></span>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:14%;"><label for="">Date de naissance </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control date" id="dob" name="dob" placeholder="e.g 09-Jan-1992" title="Please select date de naissance" onchange="getAge();checkARTInitiationDate();" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="ageInYears">Âge en années </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control checkNum" id="ageInYears" name="ageInYears" placeholder="Aannées" title="Please enter àge en années" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="ageInMonths">Âge en mois </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control checkNum" id="ageInMonths" name="ageInMonths" placeholder="Mois" title="Please enter àge en mois" style="width:100%;"/>
                                </td>
                                <td><label for="sex">Sexe </label></td>
                                <td style="width:16%;">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">M</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check sexe">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">F</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check sexe">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="patientArtNo">Code du patient </label></td>
                                <td>
                                    <input type="text" class="form-control" id="patientArtNo" name="patientArtNo" placeholder="Code du patient" title="Please enter code du patient" style="width:100%;"  onchange="checkPatientDetails('vl_request_form','patient_art_no',this,null)"/>
                                </td>
                                <td colspan="2"><label for="isPatientNew">Si S/ ARV </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="isPatientNewYes" name="isPatientNew" value="yes" title="Please check Si S/ ARV">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Non</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="isPatientNewNo" name="isPatientNew" value="no">
                                    </label>
                                </td>
                                <td class="du" style="visibility:hidden;"><label for="">Date du début des ARV </label></td>
                                <td class="du" colspan="3" style="visibility:hidden;">
                                    <input type="text" class="form-control date" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="e.g 09-Jan-1992" title="Please enter date du début des ARV" onchange="checkARTInitiationDate();checkLastVLTestDate();" style="width:100%;"/> (Jour/Mois/Année)
                                </td>
                            </tr>
                            <tr>
                                <td><label>Régime ARV en cours </label></td>
                                <td colspan="7">
                                  <select class="form-control" name="artRegimen" id="artRegimen" title="Please choose régime ARV en cours" onchange="checkARTRegimenValue();" style="width:30%;">
                                    <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($aResult as $arv){ ?>
                                       <option value="<?php echo $arv['art_code']; ?>"><?php echo $arv['art_code']; ?></option>
                                      <?php } if($sarr['user_type']!='vluser'){  ?>
                                      <option value="other">Autre</option>
                                      <?php } ?>
                                  </select>
                                </td>
                            </tr>
                            <tr class="newArtRegimen" style="display:none;">
                                <td><label for="newArtRegimen">Autre, à préciser </label></td>
                                <td colspan="7">
                                    <input type="text" class="form-control" name="newArtRegimen" id="newArtRegimen" placeholder="Régime ARV" title="Please enter régime ARV" style="width:30%;" >
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><label for="hasChangedRegimen">Ce patient a-t-il déjà changé de régime de traitement? </label></td>
                                <td colspan="2">
                                    <label class="radio-inline">Oui </label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="changedRegimenYes" name="hasChangedRegimen" value="yes" title="Please check any of one option">
                                    </label>
                                    <label class="radio-inline">Non </label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="changedRegimenNo" name="hasChangedRegimen" value="no" title="Please check any of one option">
                                    </label>
                                </td>
                                <td><label for="reasonForArvRegimenChange" class="arvChangedElement" style="display:none;">Motif de changement de régime ARV </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control arvChangedElement" id="reasonForArvRegimenChange" name="reasonForArvRegimenChange" placeholder="Motif de changement de régime ARV" title="Please enter motif de changement de régime ARV" style="width:100%;display:none;"/>
                                </td>
                            </tr>
                            <tr class="arvChangedElement" style="display:none;">
                                <td><label for="">Date du changement de régime ARV </label></td>
                                <td colspan="7">
                                    <input type="text" class="form-control date" id="dateOfArvRegimenChange" name="dateOfArvRegimenChange" placeholder="e.g 09-Jan-1992" title="Please enter date du changement de régime ARV" style="width:30%;"/> (Jour/Mois/Année)
                                </td>
                            </tr>
                            <tr>
                                <td><label for="reasonForRequest">Motif de la demande </label></td>
                                <td colspan="2">
                                   <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose motif de la demande" onchange="checkVLTestReason();">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php
                                      foreach($testReason as $tReason){
                                      ?>
                                       <option value="<?php echo $tReason['test_reason_id']; ?>"><?php echo ucwords($tReason['test_reason_name']); ?></option>
                                      <?php } ?>
                                      <option value="other">Autre</option>
                                    </select>
                                </td>
                                <td><label for="viralLoadNo">Charge virale N </label></td>
                                <td colspan="4">
                                    <input type="text" class="form-control" id="viralLoadNo" name="viralLoadNo" placeholder="Charge virale N" title="Please enter charge virale N" style="width:60%;"/>
                                </td>
                            </tr>
                            <tr id="femaleElements" style="display:none;">
                                <td><strong>Si Femme : </strong></td>
                                <td colspan="2">
                                    <label for="breastfeeding">allaitante ?</label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check Si allaitante">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Non</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no">
                                    </label>
                                </td>
                                <td colspan="2"><label for="patientPregnant">Ou enceinte ? </label> 
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Si Ou enceinte ">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Non</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="pregNo" name="patientPregnant" value="no">
                                    </label>
                                </td>
                                <td><label for="trimester">Si Femme  enceinte </label></td>
                                <td colspan="2">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Trimestre 1</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" id="trimester1" name="trimester" value="1" title="Please check trimestre">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Trimestre 2</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" id="trimester2" name="trimester" value="2">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Trimestre 3</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" id="trimester3" name="trimester" value="3">
                                    </label>
                                </td>
                            </tr>
                            <tr class="newVlTestReason" style="display:none;">
                                <td><label for="newVlTestReason">Autre, à préciser </label></td>
                                <td colspan="7">
                                    <input type="text" class="form-control" name="newVlTestReason" id="newVlTestReason" placeholder="Virale Demande Raison" title="Please enter virale demande raison" style="width:30%;" >
                                </td>
                            </tr>
                            <tr>
                                <td><label for="lastViralLoadResult">Résultat dernière charge virale </label></td>
                                <td colspan="7">
                                    <input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Résultat dernière charge virale" title="Please enter résultat dernière charge virale" style="width:30%;"/> copies/ml
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date dernière charge virale (demande) </label></td>
                                <td colspan="7">
                                    <input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="e.g 09-Jan-1992" title="Please enter date dernière charge virale" onchange="checkLastVLTestDate();" style="width:30%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le service demandeur dans la structure de soins</label></td>
                            </tr>
                        </table>
                        <div class="box-header with-border">
                            <h3 class="box-title">Informations sur le prélèvement <small>(A remplir par le préleveur)</small> </h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:20%;"><label for="">Date du prélèvement </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control dateTime" id="sampleCollectionDate" name="sampleCollectionDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date du prélèvement" onchange="checkSampleReceviedDate();checkSampleTestingDate();" style="width:30%;"/>
                                </td>
                            </tr>
                            <?php if(isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled"){ ?>
                              <tr>
                                <td><label for="specimenType">Type d'échantillon </label></td>
                                <td colspan="3">
                                  <select name="specimenType" id="specimenType" class="form-control" title="Please choose type d'échantillon" onchange="checkSpecimenType();" style="width:30%;">
                                    <option value=""> -- Sélectionner -- </option>
                                    <?php foreach($sResult as $type){ ?>
                                     <option value="<?php echo $type['sample_id'];?>"><?php echo ucwords($type['sample_name']);?></option>
                                     <?php } ?>
                                  </select>
                                </td>
                              </tr>
                            <?php } ?>
                            <tr class="plasmaElement" style="display:none;">
                                <td><label for="conservationTemperature">Si plasma,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Température de conservation </label></td>
                                <td>
                                    <input type="text" class="form-control checkNum" id="conservationTemperature" name="conservationTemperature" placeholder="Température de conservation" title="Please enter température de conservation" style="width:80%;"/>°C
                                </td>
                                <td><label for="durationOfConservation">Durée de conservation </label></td>
                                <td>
                                    <input type="text" class="form-control" id="durationOfConservation" name="durationOfConservation" placeholder="e.g 9/1" title="Please enter durée de conservation" style="width:60%;"/>Jour/Heures
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date de départ au Labo biomol </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control dateTime" id="dateDispatchedFromClinicToLab" name="dateDispatchedFromClinicToLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de départ au Labo biomol" style="width:30%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le préleveur </label></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">2. Réservé au Laboratoire de biologie moléculaire </h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:20%;"><label for="">Date de réception de léchantillon </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réception de léchantillon" onchange="checkSampleReceviedDate();" style="width:30%;"/>
                                </td>
                            </tr>
                            <?php if(isset($arr['testing_status']) && trim($arr['testing_status']) == "enabled" && $_SESSION['userType']==''){ ?>
                              <tr>
                                <td><label for="">Décision prise </label></td>
                                <td colspan="3">
                                    <select class="form-control" id="status" name="status" title="Please select décision prise" onchange="checkTestStatus();" style="width:30%;">
                                      <option value="6"> En attente d'approbation Clinique </option>
                                      <option value="7">Echantillon accepté</option>
                                      <option value="4">Echantillon rejeté</option>
                                    </select>
                                </td>
                              </tr>
                            <?php } ?>
                            <tr class="rejectionReason" style="display:none;">
                                <td><label for="rejectionReason">Motifs de rejet </label></td>
                                <td>
                                    <select class="form-control" id="rejectionReason" name="rejectionReason" title="Please select motifs de rejet" onchange="checkRejectionReason();" style="width:80%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($rejectionResult as $rjctReason){ ?>
                                       <option value="<?php echo $rjctReason['rejection_reason_id']; ?>"><?php echo ucwords($rjctReason['rejection_reason_name']); ?></option>
                                      <?php } if($sarr['user_type']!='vluser'){  ?>
                                       <option value="other">Autre</option>
                                       <?php } ?>
                                    </select>
                                </td>
                                <td style="text-align:center;"><label for="newRejectionReason" class="newRejectionReason" style="display:none;">Autre, à préciser </label></td>
                                <td><input type="text" class="form-control newRejectionReason" id="newRejectionReason" name="newRejectionReason" placeholder="Motifs de rejet" title="Please enter motifs de rejet" style="width:90%;display:none;"/></td>
                            </tr>
                            <tr>
                                <td><label for="sampleCode">Code Labo </label> <span class="mandatory">*</span></td>
                                <td colspan="3">
                                    <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Code Labo" title="Please enter code labo" style="width:30%;" onchange="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>',this.id,null,'This sample number already exists.Try another number',null)"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="labId">Nom du laboratoire </label> </td>
                                <td colspan="3">
                                    <select name="labId" id="labId" class="form-control" title="Please choose lab name" style="width:30%;">
                                    <option value=""> -- Select -- </option>
                                    <?php foreach($lResult as $labName){ ?>
                                      <option value="<?php echo $labName['facility_id'];?>" ><?php echo ucwords($labName['facility_name']);?></option>
                                      <?php } ?>
                                  </select>
                                </td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de réalisation de la charge virale </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control date" id="dateOfCompletionOfViralLoad" name="dateOfCompletionOfViralLoad" placeholder="e.g 09-Jan-1992" title="Please enter date de réalisation de la charge virale" style="width:30%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="testingPlatform">Technique utilisée </label></td>
                                <td colspan="3">
                                    <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform" style="width:230px;">
                                      <option value="">-- Select --</option>
                                      <?php foreach($importResult as $mName) { ?>
                                        <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"><?php echo $mName['machine_name'];?></option>
                                        <?php } ?>
                                    </select>
                                        </td>
                                  </tr>
                                <tr>
                                <td><label for="vlResult">Résultat </label></td>
                                <td>
                                    <input type="text" class="form-control checkNum" id="vlResult" name="vlResult" placeholder="Résultat" title="Please enter résultat" onchange="calculateLogValue(this)" style="width:70%;"/>copies/ml
                                </td>
                                <td><label for="vlLog">Log </label></td>
                                <td>
                                    <input type="text" class="form-control checkNum" id="vlLog" name="vlLog" placeholder="Log" title="Please enter log" onchange="calculateLogValue(this)" style="width:70%;"/>copies/ml
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le service effectuant la charge virale </label></td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de remise du résultat </label></td>
                                <td colspan="3">
                                    <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de remise du résultat" onchange="checkSampleTestingDate();" style="width:30%;"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="box-header with-border">
                  <label class="radio-inline" style="margin:0;padding:0;">1. Biffer la mention inutile <br>2. Sélectionner un seul régime de traitement </label>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                <?php } ?>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="formId" id="formId" value="3"/>
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code'];?>"/>
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
                $("#clinicianName").val(details[2]);
              }
          });
      }
      <?php if($arr['sample_code']=='auto'){ ?>
      pNameVal = pName.split("##");
      sCode = '<?php echo date('ymd');?>';
      sCodeKey = '<?php echo $maxId;?>';
      $("#sampleCode").val('<?php echo $rKey;?>'+pNameVal[1]+sCode+sCodeKey);
      $("#sampleCodeFormat").val('<?php echo $rKey;?>'+pNameVal[1]+sCode);
      $("#sampleCodeKey").val(sCodeKey);
      checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>','sampleCode',null,'This sample number already exists.Try another number',null);
      <?php }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
      $("#sampleCode").val('<?php echo $rKey.$prefix.$mnthYr.$maxId;?>');
      $("#sampleCodeFormat").val('<?php echo $rKey.$prefix.$mnthYr;?>');
      $("#sampleCodeKey").val('<?php echo $maxId;?>');
      checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>','sampleCode',null,'This sample number already exists.Try another number',null);
      <?php } ?>
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }else{
      $("#district").html("<option value=''> -- Sélectionner -- </option>");
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
       $("#clinicName").html("<option value=''> -- Sélectionner -- </option>");
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
            $("#clinicianName").val(details[2]);
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
  function checkLastVLTestDate(){
    var artInitiationDate = $("#dateOfArtInitiation").val();
    var dateOfLastVLTest = $("#lastViralLoadTestDate").val();
    if($.trim(artInitiationDate)!= '' && $.trim(dateOfLastVLTest)!= '') {
      if(moment(artInitiationDate).isAfter(dateOfLastVLTest)) {
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
  </script>