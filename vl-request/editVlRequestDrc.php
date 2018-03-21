    <?php
    ob_start();
    //Funding source list
    $fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
    $fundingSourceList = $db->query($fundingSourceQry);
    //Implementing partner list
    $implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
    $implementingPartnerList = $db->query($implementingPartnerQry);
    //check remote user
    $pdQuery="SELECT * from province_details";
    if($sarr['user_type']=='remoteuser'){
      $sampleCode = 'remote_sample_code';
      //check user exist in user_facility_map table
        $chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='".$_SESSION['userId']."'";
        $chkUserFcMapResult = $db->query($chkUserFcMapQry);
        if($chkUserFcMapResult){
          $pdQuery="SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='".$_SESSION['userId']."'";
        }
    }else{
      $sampleCode = 'sample_code';
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
    //Get selected state
    $stateQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."'";
    $stateResult=$db->query($stateQuery);
    if(!isset($stateResult[0]['facility_state']) || $stateResult[0]['facility_state']== ''){
      $stateResult[0]['facility_state'] = "";
    }
    //district details
    $districtQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$stateResult[0]['facility_state']."'";
    $districtResult=$db->query($districtQuery);
    $provinceQuery="SELECT * from province_details where province_name='".$stateResult[0]['facility_state']."'";
    $provinceResult=$db->query($provinceQuery);
    if(!isset($provinceResult[0]['province_code']) || $provinceResult[0]['province_code']==''){
      $provinceResult[0]['province_code'] = "";
    }
    //get ART list
    $aQuery="SELECT * from r_art_code_details"; // where nation_identifier='drc'";
    $aResult=$db->query($aQuery);
    ?>
    <style>
      .translate-content{
        color:#0000FF;
        font-size:12.5px;
      }
      .du{
        <?php
        if(trim($vlQueryInfo[0]['is_patient_new']) == "" || trim($vlQueryInfo[0]['is_patient_new']) == "no"){ ?>
          visibility:hidden;
        <?php } ?>
      }
      #femaleElements{
        <?php
        if(trim($vlQueryInfo[0]['patient_gender']) == "" || trim($vlQueryInfo[0]['patient_gender']) == "male"){ ?>
          display:none;
        <?php } ?>
      }
   </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Edit Vl Request</li>
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
            <form class="form-horizontal" method="post" name="editVlRequestForm" id="editVlRequestForm" autocomplete="off" action="editVlRequestHelperDrc.php">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">1. Réservé à la structure de soins</h3>
                        </div>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information sur la structure de soins</h3>
                        </div>
                        <!--<h4 id="sampleCodeValue">exemple de code:< ?php echo ($sCode!='') ? $sCode : $vlQueryInfo[0][$sampleCode]; ?></h4>-->
                        <table class="table" style="width:100%">
                            <tr>
                                <td><label for="sampleCode">échantillon id </label><span class="mandatory">*</span></td>
                                <td>
                                   <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="échantillon id" title="Please enter échantillon id" value="<?php echo (isset($sCode) && $sCode!='') ? $sCode : $vlQueryInfo[0][$sampleCode]; ?>" style="width:100%;" onchange="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>',this.id,'<?php echo "vl_sample_id##".$vlQueryInfo[0]["vl_sample_id"];?>','The échantillon id that you entered already exists. Please try another échantillon id',null)"/>
                                </td>
                                <td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($pdResult as $provinceName){ ?>
                                        <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code']; ?>" <?php echo (strtolower($stateResult[0]['facility_state'])."##".strtolower($provinceResult[0]['province_code'])==strtolower($provinceName['province_name'])."##".strtolower($provinceName['province_code']))?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']); ?></option>
                                      <?php } ?>
                                    </select>
                                </td>
                                <td><label for="district">Zone de santé </label><span class="mandatory">*</span></td>
                                <td>
                                  <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                    <option value=""> -- Sélectionner -- </option>
                                    <?php foreach($districtResult as $districtName){ ?>
                                      <option value="<?php echo $districtName['facility_district'];?>" <?php echo ($stateResult[0]['facility_district']==$districtName['facility_district'])?"selected='selected'":""?>><?php echo ucwords($districtName['facility_district']);?></option>
                                      <?php } ?>
                                  </select>
                                </td>
                                <td><label for="clinicName">Structure/Service </label><span class="mandatory">*</span></td>
                                <td>
                                    <select class="form-control isRequired" name="clinicName" id="clinicName" title="Please choose service provider" <!--onchange="getfacilityProvinceDetails(this);"--> style="width:100%;">
                                        <option value=""> -- Sélectionner -- </option>
                                        <?php foreach($fResult as $fDetails){ ?>
                                          <option value="<?php echo $fDetails['facility_id']; ?>" <?php echo ($vlQueryInfo[0]['facility_id']==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="clinicianName">Demandeur </label></td>
                                <td>
                                  <input type="text" class="form-control" id="clinicianName" name="clinicianName" placeholder="Demandeur" title="Please enter demandeur" value="<?php echo $vlQueryInfo[0]['request_clinician_name']; ?>" style="width:100%;"/>
                                </td>
                                <td><label for="clinicanTelephone">Téléphone </label></td>
                                <td>
                                  <input type="text" class="form-control checkNum" id="clinicanTelephone" name="clinicanTelephone" placeholder="Téléphone" title="Please enter téléphone" value="<?php echo $vlQueryInfo[0]['request_clinician_phone_number']; ?>" style="width:100%;"/>
                                </td>
                                <td><label for="supportPartner">Partenaire dappui </label></td>
                                <td>
                                  <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" value="<?php echo $vlQueryInfo[0]['facility_support_partner']; ?>" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date de la demande </label></td>
                                <td>
                                    <input type="text" class="form-control date" id="dateOfDemand" name="dateOfDemand" placeholder="e.g 09-Jan-1992" title="Please enter date de la demande" value="<?php echo $vlQueryInfo[0]['date_test_ordered_by_physician']; ?>" style="width:100%;"/>
                                </td>
                                <td><label for="fundingSource">Source de financement </label></td>
                                <td>
                                    <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php
                                      foreach($fundingSourceList as $fundingSource){
                                      ?>
                                        <option value="<?php echo base64_encode($fundingSource['funding_source_id']); ?>" <?php echo ($fundingSource['funding_source_id'] == $vlQueryInfo[0]['funding_source'])?'selected="selected"':''; ?>><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                                      <?php } ?>
                                    </select>
                                </td>
                                <td><label for="implementingPartner">Partenaire de mise en œuvre </label></td>
                                <td>
                                    <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php
                                      foreach($implementingPartnerList as $implementingPartner){
                                      ?>
                                        <option value="<?php echo base64_encode($implementingPartner['i_partner_id']); ?>" <?php echo ($implementingPartner['i_partner_id'] == $vlQueryInfo[0]['implementing_partner'])?'selected="selected"':''; ?>><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                      <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <?php if($sarr['user_type']=='remoteuser') { ?>
                              <tr>
                                  <td><label for="labId">Nom du laboratoire <span class="mandatory">*</span></label> </td>
                                  <td>
                                      <select name="labId" id="labId" class="form-control isRequired" title="Please choose laboratoire" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($lResult as $labName){ ?>
                                        <option value="<?php echo $labName['facility_id'];?>" <?php echo ($vlQueryInfo[0]['lab_id']==$labName['facility_id'])?"selected='selected'":""?>><?php echo ucwords($labName['facility_name']);?></option>
                                        <?php } ?>
                                    </select>
                                  </td>
                                  <td></td><td></td>
                              </tr>
                            <?php } ?>
                        </table>
                        <div class="box-header with-border">
                            <h3 class="box-title">Information sur le patient </h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:10%;"><label for="">Date de naissance </label></td>
                                <td style="width:15%;">
                                    <input type="text" class="form-control date" id="dob" name="dob" placeholder="e.g 09-Jan-1992" title="Please select date de naissance" onchange="getAge();checkARTInitiationDate();" value="<?php echo $vlQueryInfo[0]['patient_dob']; ?>" style="width:100%;"/>
                                </td>
                                <td style="width:6%;"><label for="ageInYears">Âge en années </label></td>
                                <td style="width:19%;">
                                    <input type="text" class="form-control checkNum" id="ageInYears" name="ageInYears" placeholder="Aannées" title="Please enter àge en années" value="<?php echo $vlQueryInfo[0]['patient_age_in_years']; ?>" onblur="clearDOB(this.value);" style="width:100%;"/>
                                </td>
                                <td style="width:10%;"><label for="ageInMonths">Âge en mois </label></td>
                                <td style="width:15%;">
                                    <input type="text" class="form-control checkNum" id="ageInMonths" name="ageInMonths" placeholder="Mois" title="Please enter àge en mois" value="<?php echo $vlQueryInfo[0]['patient_age_in_months']; ?>" onblur="clearDOB(this.value);" style="width:100%;"/>
                                </td>
                                <td style="width:10%;text-align:center;"><label for="sex">Sexe </label></td>
                                <td style="width:15%;">
                                    <label class="radio-inline" style="padding-left:12px !important;margin-left:0;">M</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check sexe" <?php echo (trim($vlQueryInfo[0]['patient_gender']) == "male")?'checked="checked"':''; ?>>
                                    </label>
                                    <label class="radio-inline" style="padding-left:12px !important;margin-left:0;">F</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check sexe" <?php echo (trim($vlQueryInfo[0]['patient_gender']) == "female")?'checked="checked"':''; ?>>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="patientArtNo">Code du patient </label></td>
                                <td>
                                  <input type="text" class="form-control" id="patientArtNo" name="patientArtNo" placeholder="Code du patient" title="Please enter code du patient" value="<?php echo $vlQueryInfo[0]['patient_art_no']; ?>" style="width:100%;"/>
                                </td>
                                <td colspan="2"><label for="isPatientNew">Si S/ARV </label>
                                  <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                                  <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                    <input type="radio" class="" id="isPatientNewYes" name="isPatientNew" <?php echo($vlQueryInfo[0]['is_patient_new'] == 'yes')?'checked="checked"':''; ?> value="yes" title="Please check Si S/ ARV">
                                  </label>
                                  <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Non</label>
                                  <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                    <input type="radio" class="" id="isPatientNewNo" name="isPatientNew" <?php echo($vlQueryInfo[0]['is_patient_new'] == 'no')?'checked="checked"':''; ?> value="no">
                                  </label>
                                </td>
                                <td class="du"><label for="">Date du début des ARV </label></td>
                                <td class="du">
                                  <input type="text" class="form-control date" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="e.g 09-Jan-1992" title="Please enter date du début des ARV" value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen']; ?>" onchange="checkARTInitiationDate();checkLastVLTestDate();" style="width:100%;"/>&nbsp;(Jour/Mois/Année)
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr>
                                <td><label>Régime ARV en cours </label></td>
                                <td>
                                  <select class="form-control" name="artRegimen" id="artRegimen" title="Please choose régime ARV en cours" onchange="checkARTRegimenValue();" style="width:100%;">
                                    <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($aResult as $arv){ ?>
                                       <option value="<?php echo $arv['art_code']; ?>" <?php echo($arv['art_code'] == $vlQueryInfo[0]['current_regimen'])?'selected="selected"':''; ?>><?php echo $arv['art_code']; ?></option>
                                      <?php } if($sarr['user_type']!='vluser'){  ?>
                                      <option value="other">Autre</option>
                                      <?php } ?>
                                  </select>
                                  <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="Enter Régime ARV" title="Please enter régime ARV" style="width:100%;margin-top:1vh;display:none;">
                                </td>
                                <td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <label for="hasChangedRegimen">Ce patient a-t-il déjà changé de régime de traitement? </label>
                                    <label class="radio-inline">&nbsp;&nbsp;&nbsp;&nbsp;Oui </label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="changedRegimenYes" name="hasChangedRegimen" value="yes" title="Please check any of one option" <?php echo(trim($vlQueryInfo[0]['has_patient_changed_regimen']) == "yes")?'checked="checked"':''; ?>>
                                    </label>
                                    <label class="radio-inline">Non </label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="changedRegimenNo" name="hasChangedRegimen" value="no" title="Please check any of one option" <?php echo(trim($vlQueryInfo[0]['has_patient_changed_regimen']) == "no")?'checked="checked"':''; ?>>
                                    </label>
                                </td>
                                <td colspan="2"><label for="reasonForArvRegimenChange" class="arvChangedElement" style="display:<?php echo(trim($vlQueryInfo[0]['has_patient_changed_regimen']) == "yes")?'':'none'; ?>;">Motif de changement de régime ARV </label></td>
                                <td colspan="2">
                                  <input type="text" class="form-control arvChangedElement" id="reasonForArvRegimenChange" name="reasonForArvRegimenChange" placeholder="Motif de changement de régime ARV" title="Please enter motif de changement de régime ARV" value="<?php echo $vlQueryInfo[0]['reason_for_regimen_change']; ?>" style="width:100%;display:<?php echo(trim($vlQueryInfo[0]['has_patient_changed_regimen']) == "yes")?'':'none'; ?>;"/>
                                </td>
                            </tr>
                            <tr class="arvChangedElement" style="display:<?php echo(trim($vlQueryInfo[0]['has_patient_changed_regimen']) == "yes")?'':'none'; ?>;">
                              <td><label for="">Date du changement de régime ARV </label></td>
                              <td colspan="2">
                                <input type="text" class="form-control date" id="dateOfArvRegimenChange" name="dateOfArvRegimenChange" placeholder="e.g 09-Jan-1992" title="Please enter date du changement de régime ARV" value="<?php echo $vlQueryInfo[0]['regimen_change_date']; ?>" style="width:100%;"/>&nbsp;(Jour/Mois/Année)
                              </td>
                              <td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="reasonForRequest">Motif de la demande </label></td>
                                <td colspan="2">
                                   <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose motif de la demande" onchange="checkVLTestReason();">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($vlTestReasonResult as $tReason){ ?>
                                       <option value="<?php echo $tReason['test_reason_id']; ?>" <?php echo($vlQueryInfo[0]['reason_for_vl_testing'] == $tReason['test_reason_id'])?'selected="selected"':''; ?>><?php echo ucwords($tReason['test_reason_name']); ?></option>
                                      <?php } if($sarr['user_type']!='vluser'){  ?>
                                      <option value="other">Autre</option>
                                      <?php } ?>
                                    </select>
                                </td>
                                <td style="text-align:center;"><label for="viralLoadNo">Charge virale N </label></td>
                                <td colspan="2">
                                  <input type="text" class="form-control" id="viralLoadNo" name="viralLoadNo" placeholder="Charge virale N" title="Please enter charge virale N" value="<?php echo $vlQueryInfo[0]['vl_test_number']; ?>" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr class="newVlTestReason" style="display:none;">
                                <td><label for="newVlTestReason">Autre, à préciser <span class="mandatory">*</span></label></td>
                                <td colspan="2">
                                  <input type="text" class="form-control" name="newVlTestReason" id="newVlTestReason" placeholder="Virale Demande Raison" title="Please enter virale demande raison" style="width:100%;" >
                                </td>
                                <td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr id="femaleElements">
                                <td><strong>Si Femme : </strong></td>
                                <td colspan="2">
                                    <label for="breastfeeding">allaitante ?</label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" <?php echo(trim($vlQueryInfo[0]['is_patient_breastfeeding']) == "yes")?'checked="checked"':''; ?> value="yes" title="Please check Si allaitante">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Non</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" <?php echo(trim($vlQueryInfo[0]['is_patient_breastfeeding']) == "no")?'checked="checked"':''; ?> value="no">
                                    </label>
                                </td>
                                <td colspan="5"><label for="patientPregnant">Ou enceinte ? </label> 
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="pregYes" name="patientPregnant" <?php echo(trim($vlQueryInfo[0]['is_patient_pregnant']) == "yes")?'checked="checked"':''; ?> value="yes" title="Please check Si Ou enceinte ">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Non</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="pregNo" name="patientPregnant" <?php echo(trim($vlQueryInfo[0]['is_patient_pregnant']) == "no")?'checked="checked"':''; ?> value="no">
                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label for="trimester">Si Femme enceinte </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Trimestre 1</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" id="trimester1" name="trimester" <?php echo(trim($vlQueryInfo[0]['pregnancy_trimester']) == "1")?'checked="checked"':''; ?> value="1" title="Please check trimestre">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Trimestre 2</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" id="trimester2" name="trimester" <?php echo(trim($vlQueryInfo[0]['pregnancy_trimester']) == "2")?'checked="checked"':''; ?> value="2">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Trimestre 3</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" id="trimester3" name="trimester" <?php echo(trim($vlQueryInfo[0]['pregnancy_trimester']) == "3")?'checked="checked"':''; ?> value="3">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="lastViralLoadResult">Résultat dernière charge virale </label></td>
                                <td colspan="2">
                                  <input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Résultat dernière charge virale" title="Please enter résultat dernière charge virale" value="<?php echo $vlQueryInfo[0]['last_viral_load_result']; ?>" style="width:100%;"/>
                                </td>
                                <td>copies/ml</td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="">Date dernière charge virale (demande) </label></td>
                                <td colspan="2">
                                  <input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="e.g 09-Jan-1992" title="Please enter date dernière charge virale" value="<?php echo $vlQueryInfo[0]['last_viral_load_date']; ?>" onchange="checkLastVLTestDate();" style="width:100%;"/>
                                </td>
                                <td></td><td></td><td></td><td></td><td></td>
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
                                <td style="width:25%;"><label for="">Date du prélèvement  <span class="mandatory">*</span></label></td>
                                <td style="width:25%;">
                                  <input type="text" class="form-control dateTime isRequired" id="sampleCollectionDate" name="sampleCollectionDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date du prélèvement" value="<?php echo $vlQueryInfo[0]['sample_collection_date']; ?>" onchange="checkSampleReceviedDate();checkSampleTestingDate();" style="width:100%;"/>
                                </td>
                                <td style="width:25%;"></td><td style="width:25%;"></td>
                            </tr>
                            <?php if(isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled"){ ?>
                              <tr>
                                <td><label for="specimenType">Type d'échantillon  <span class="mandatory">*</span></label></td>
                                <td>
                                  <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose type d'échantillon" onchange="checkSpecimenType();" style="width:100%;">
                                    <option value=""> -- Sélectionner -- </option>
                                    <?php foreach($sResult as $type){ ?>
                                     <option value="<?php echo $type['sample_id'];?>" <?php echo($vlQueryInfo[0]['sample_type'] == $type['sample_id'])?'selected="selected"':''; ?>><?php echo ucwords($type['sample_name']);?></option>
                                     <?php } ?>
                                  </select>
                                </td>
                                <td></td><td></td>
                              </tr>
                            <?php } ?>
                            <tr class="plasmaElement" style="display:<?php echo($vlQueryInfo[0]['sample_type'] == 2)?'':'none'; ?>;">
                                <td><label for="conservationTemperature">Si plasma,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Température de conservation </label></td>
                                <td>
                                  <input type="text" class="form-control checkNum" id="conservationTemperature" name="conservationTemperature" placeholder="Température de conservation" title="Please enter température de conservation" value="<?php echo $vlQueryInfo[0]['plasma_conservation_temperature']; ?>" style="width:100%;"/>&nbsp;(°C)
                                </td>
                                <td style="text-align:center;"><label for="durationOfConservation">Durée de conservation </label></td>
                                <td>
                                  <input type="text" class="form-control" id="durationOfConservation" name="durationOfConservation" placeholder="e.g 9/1" title="Please enter durée de conservation" value="<?php echo $vlQueryInfo[0]['plasma_conservation_duration']; ?>" style="width:100%;"/>&nbsp;(Jour/Heures)
                                </td>
                            </tr>
                            <tr>
                                <td><label for="">Date de départ au Labo biomol </label></td>
                                <td>
                                    <input type="text" class="form-control dateTime" id="dateDispatchedFromClinicToLab" name="dateDispatchedFromClinicToLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de départ au Labo biomol" value="<?php echo $vlQueryInfo[0]['date_dispatched_from_clinic_to_lab']; ?>" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr>
                                <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le préleveur </label></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php if($sarr['user_type']!= 'remoteuser') { ?>
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="box-header with-border">
                            <h3 class="box-title">2. Réservé au Laboratoire de biologie moléculaire </h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:25%;"><label for="">Date de réception de léchantillon </label></td>
                                <td style="width:25%;">
                                  <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réception de léchantillon" <?php echo $labFieldDisabled; ?> onchange="checkSampleReceviedDate();" value="<?php echo $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']; ?>" style="width:100%;"/>
                                </td>
                                <td style="width:25%;"></td><td style="width:25%;"></td>
                            </tr>
                            <?php if(isset($arr['testing_status']) && trim($arr['testing_status']) == "enabled"){ ?>
                              <tr style="<?php echo (($_SESSION['userType']=='clinic' || $_SESSION['userType']=='lab') && $vlQueryInfo[0]['result_status']==9) ? 'display:none;':''; ?>">
                                <td><label for="">Décision prise </label></td>
                                <td>
                                    <select class="form-control" id="status" name="status" title="Please select décision prise" <?php echo $labFieldDisabled; ?> onchange="checkTestStatus();" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <option value="6" <?php echo($vlQueryInfo[0]['result_status'] == 6)?'selected="selected"':''; ?>> En attente d'approbation Clinique </option>
                                      <option value="7" <?php echo($vlQueryInfo[0]['result_status'] == 7)?'selected="selected"':''; ?>>Echantillon accepté</option>
                                      <option value="4" <?php echo($vlQueryInfo[0]['result_status'] == 4)?'selected="selected"':''; ?>>Echantillon rejeté</option>
                                    </select>
                                </td>
                                <td></td><td></td>
                              </tr>
                            <?php } ?>
                            <tr class="rejectionReason" style="display:<?php echo($vlQueryInfo[0]['result_status'] == 4)?'':'none'; ?>;">
                                <td><label for="rejectionReason">Motifs de rejet <span class="mandatory">*</span></label></td>
                                <td>
                                    <select class="form-control" id="rejectionReason" name="rejectionReason" title="Please select motifs de rejet" <?php echo $labFieldDisabled; ?> onchange="checkRejectionReason();" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      <?php foreach($rejectionResult as $rjctReason){ ?>
                                       <option value="<?php echo $rjctReason['rejection_reason_id']; ?>" <?php echo($vlQueryInfo[0]['reason_for_sample_rejection'] == $rjctReason['rejection_reason_id'])?'selected="selected"':''; ?>><?php echo ucwords($rjctReason['rejection_reason_name']); ?></option>
                                      <?php } ?>
                                       <option value="other">Autre</option>
                                    </select>
                                </td>
                                <td style="text-align:center;"><label for="newRejectionReason" class="newRejectionReason" style="display:none;">Autre, à préciser <span class="mandatory">*</span></label></td>
                                <td><input type="text" class="form-control newRejectionReason" id="newRejectionReason" name="newRejectionReason" placeholder="Motifs de rejet" title="Please enter motifs de rejet" <?php echo $labFieldDisabled; ?> style="width:100%;display:none;"/></td>
                            </tr>
                            <!-- <tr>
                                <td><label for="sampleCode">Code Labo </label> <span class="mandatory">*</span></td>
                                <td colspan="3">
                                    <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Code Labo" title="Please enter code labo" value="< ?php echo (isset($sCode) && $sCode!='') ? $sCode : $vlQueryInfo[0][$sampleCode]; ?>" style="width:30%;" onchange="checkSampleNameValidation('vl_request_form','< ?php echo $sampleCode;?>',this.id,'< ?php echo "vl_sample_id##".$vlQueryInfo[0]["vl_sample_id"];?>','The sample number that you entered already exists. Please try another number',null)"/>
                                    <input type="hidden" name="sampleCodeCol" value="< ?php echo $vlQueryInfo[0]['sample_code'];?>"/>
                                </td>
                            </tr> -->
                            <tr>
                                <td><label for="labId">Nom du laboratoire </label> </td>
                                <td>
                                    <select name="labId" id="labId" class="form-control" title="Please choose laboratoire" style="width:100%;">
                                    <option value=""> -- Sélectionner -- </option>
                                    <?php foreach($lResult as $labName){ ?>
                                      <option value="<?php echo $labName['facility_id'];?>" <?php echo ($vlQueryInfo[0]['lab_id']==$labName['facility_id'])?"selected='selected'":""?>><?php echo ucwords($labName['facility_name']);?></option>
                                      <?php } ?>
                                  </select>
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de réalisation de la charge virale </label></td>
                                <td>
                                    <input type="text" class="form-control date" id="dateOfCompletionOfViralLoad" name="dateOfCompletionOfViralLoad" placeholder="e.g 09-Jan-1992" title="Please enter date de réalisation de la charge virale" <?php echo $labFieldDisabled; ?> value="<?php echo $vlQueryInfo[0]['result_approved_datetime']; ?>" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>
                            <tr>
                                <td><label for="testingPlatform">Technique utilisée </label></td>
                                <td>
                                  <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform" <?php echo $labFieldDisabled; ?> style="width:100%;">
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach($importResult as $mName) { ?>
                                      <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"<?php echo ($vlQueryInfo[0]['vl_test_platform'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit']==$mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'])?"selected='selected'":""?>><?php echo $mName['machine_name'];?></option>
                                      <?php } ?>
                                  </select>
                                </td>
                                <td></td><td></td>
                                </tr>
                              <tr>
                                <td class="vlResult"><label for="vlResult">Résultat</label></td>
                                <td class="vlResult">
                                  <input type="text" class="vlResult form-control checkNum" id="vlResult" name="vlResult" placeholder="Résultat" title="Please enter résultat" <?php echo $labFieldDisabled; ?> value="<?php echo $vlQueryInfo[0]['result']; ?>" onchange="calculateLogValue(this)" style="width:100%;"/>&nbsp;(copies/ml)
                                </td>
                                <td class="vlLog" style="text-align:center;"><label for="vlLog">Log </label></td>
                                <td class="vlLog">
                                  <input type="text" class="form-control checkNum" id="vlLog" name="vlLog" placeholder="Log" title="Please enter log" value="<?php echo $vlQueryInfo[0]['result_value_log']; ?>" <?php echo $labFieldDisabled; ?> onchange="calculateLogValue(this)" style="width:100%;"/>&nbsp;(copies/ml)
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le service effectuant la charge virale </label></td>
                            </tr>
                            <tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de remise du résultat </label></td>
                                <td>
                                  <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de remise du résultat" value="<?php echo $vlQueryInfo[0]['sample_tested_datetime']; ?>" <?php echo $labFieldDisabled; ?> onchange="checkSampleTestingDate();" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php } ?>
                <div class="box-header with-border">
                  <label class="radio-inline" style="margin:0;padding:0;">1. Biffer la mention inutile <br>2. Sélectionner un seul régime de traitement </label>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="sampleCodeCol" value="<?php echo $vlQueryInfo[0]['sample_code'];?>"/>
                <input type="hidden" id="vlSampleId" name="vlSampleId" value="<?php echo $vlQueryInfo[0]['vl_sample_id']; ?>"/>
                <input type="hidden" name="oldStatus" value="<?php echo $vlQueryInfo[0]['result_status']; ?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
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
     $(document).ready(function() {
        //checkTestStatus();
      if($("#status").val() == 4){
        $(".rejectionReason").show();
        $("#rejectionReason").addClass('isRequired');
        $("#vlResult").val('').css('pointer-events','none');
        $("#vlLog").val('').css('pointer-events','none');
        $(".vlResult, .vlLog").hide();
       //$("#vlResult").removeClass('isRequired');
      }else{
        $(".rejectionReason").hide();
        $("#rejectionReason").removeClass('isRequired');
        $("#vlResult").css('pointer-events','auto');
        $("#vlLog").css('pointer-events','auto');
       
        $(".vlResult, .vlLog").show();
        //$("#vlResult").addClass('isRequired');
      }
     });
     
    function getfacilityDetails(obj){
      $.blockUI();
      var pName = $("#province").val();
      if($.trim(pName)!=''){
            $.post("../includes/getFacilityForClinic.php", { pName : pName},
            function(data){
                if(data!= ""){
                  details = data.split("###");
                  $("#district").html(details[1]);
                }
            });
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
                details = data.split("###");
                $("#clinicName").html(details[0]);
            }
        });
      }else{
         $("#clinicName").html("<option value=''> -- Sélectionner -- </option>");
      }
      $.unblockUI();
    }
    
    $("input:radio[name=hasChangedRegimen]").click(function() {
      if($(this).val() == 'yes'){
         $(".arvChangedElement").show();
      }else if($(this).val() == 'no'){
        $(".arvChangedElement").hide();
      }
    });
    
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
    function checkVLTestReason(){
      var vlTestReason = $("#vlTestReason").val();
      if(vlTestReason == "other"){
        $(".newVlTestReason").show();
        $("#newVlTestReason").addClass("isRequired");
      }else{
        $(".newVlTestReason").hide();
        $("#newVlTestReason").removeClass("isRequired");
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
        $("#rejectionReason").addClass('isRequired');
        $("#vlResult").val('').css('pointer-events','none');
        $("#vlLog").val('').css('pointer-events','none');
        $("#rejectionReason").val('').css('pointer-events','auto');
        $(".vlResult, .vlLog").hide();
       //$("#vlResult").removeClass('isRequired');
      }else{
        $(".rejectionReason").hide();
        $("#rejectionReason").removeClass('isRequired');
        $("#vlResult").css('pointer-events','auto');
        $("#vlLog").css('pointer-events','auto');
        $("#vlResult").val('').css('pointer-events','auto');
        $("#vlLog").val('').css('pointer-events','auto');
        $(".vlResult, .vlLog").show();
        //$("#vlResult").addClass('isRequired');
      }
    }
    function checkRejectionReason(){
      var rejectionReason = $("#rejectionReason").val();
      if(rejectionReason == "other"){
        $(".newRejectionReason").show();
        $("#newRejectionReason").addClass('isRequired');
      }else{
        $(".newRejectionReason").hide();
        $("#newRejectionReason").removeClass('isRequired');
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
        formId: 'editVlRequestForm'
      });
      if(flag){
        $.blockUI();
        document.getElementById('editVlRequestForm').submit();
      }
    }
  </script>