<?php
ob_start();
//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);
//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);

$province = "";
$province .= "<option value=''> -- Sélectionner -- </option>";
foreach ($pdResult as $provinceName) {
  $province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}

$facility = $general->generateSelectOptions($healthFacilities, $vlQueryInfo['facility_id'], '-- Sélectionner --');
//Get selected state
$stateQuery = "SELECT * from facility_details where facility_id='" . $vlQueryInfo['facility_id'] . "'";
$stateResult = $db->query($stateQuery);
if (!isset($stateResult[0]['facility_state'])) {
  $stateResult[0]['facility_state'] = '';
}
//district details
$districtQuery = "SELECT DISTINCT facility_district from facility_details where facility_state='" . $stateResult[0]['facility_state'] . "'";
$districtResult = $db->query($districtQuery);

$provinceQuery = "SELECT * from province_details where province_name='" . $stateResult[0]['facility_state'] . "'";
$provinceResult = $db->query($provinceQuery);
if (!isset($provinceResult[0]['province_code'])) {
  $provinceResult[0]['province_code'] = '';
}

//get ART list
$aQuery = "SELECT * from r_vl_art_regimen";
$aResult = $db->query($aQuery);

//Set plasma storage temp.
if (isset($vlQueryInfo['sample_type']) && $vlQueryInfo['sample_type'] != 2) {
  $vlQueryInfo['plasma_storage_temperature'] = '';
}

$disable = "disabled = 'disabled'";
?>
<style>
  .translate-content {
    color: #0000FF;
    font-size: 12.5px;
  }

  .du {
    <?php
    if (trim($vlQueryInfo['is_patient_new']) == "" || trim($vlQueryInfo['is_patient_new']) == "no") { ?>visibility: hidden;
    <?php } ?>
  }

  #femaleElements {
    <?php
    if (trim($vlQueryInfo['patient_gender']) == "" || trim($vlQueryInfo['patient_gender']) == "male") { ?>display: none;
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
    
    <div class="box box-default">
      <div class="box-header with-border">
        <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
      </div>
      <!-- /.box-header -->
      <div class="box-body">
        <!-- form start -->
        <form class="form-horizontal" method="post" name="updateVlRequestForm" id="updateVlRequestForm" autocomplete="off" action="updateVlRequestHelperDrc.php">
          <div class="box-body">
            <div class="box box-default">
              <div class="box-body">
                <div class="box-header with-border">
                  <h3 class="box-title">1. Réservé à la structure de soins</h3>
                </div>
                <div class="box-header with-border">
                  <h3 class="box-title">Information sur la structure de soins</h3>
                </div>
                <!-- <h4 id="sampleCodeValue">exemple de code:< ?php echo $vlQueryInfo['sample_code']; ?></h4>-->
                <table class="table" style="width:100%">
                  <tr>
                    <td><label for="sampleCode">Échantillon id </label></td>
                    <td>
                      <input type="text" class="form-control" id="sampleCode" name="sampleCode" placeholder="Échantillon id" title="Please enter échantillon id" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['sample_code']; ?>" style="width:100%;" />
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><label for="province">Province </label></td>
                    <td>
                      <select class="form-control" name="province" id="province" title="Please choose province" <?php echo $disable; ?> style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($pdResult as $provinceName) { ?>
                          <option value="<?php echo $provinceName['province_name'] . "##" . $provinceName['province_code']; ?>" <?php echo (strtolower($stateResult[0]['facility_state']) . "##" . strtolower($provinceResult[0]['province_code']) == strtolower($provinceName['province_name']) . "##" . strtolower($provinceName['province_code'])) ? "selected='selected'" : "" ?>><?php echo ucwords($provinceName['province_name']); ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td><label for="district">Zone de santé </label></td>
                    <td>
                      <select class="form-control" name="district" id="district" title="Please choose district" <?php echo $disable; ?> style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($districtResult as $districtName) {
                        ?>
                          <option value="<?php echo $districtName['facility_district']; ?>" <?php echo ($stateResult[0]['facility_district'] == $districtName['facility_district']) ? "selected='selected'" : "" ?>><?php echo ucwords($districtName['facility_district']); ?></option>
                        <?php
                        }
                        ?>
                      </select>
                    </td>
                    <td><label for="clinicName">Nom de l'installation </label></td>
                    <td>
                      <select class="form-control" name="clinicName" id="clinicName" title="Please choose service provider" <?php echo $disable; ?> style="width:100%;">
                        <?= $facility; ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="clinicianName">Demandeur </label></td>
                    <td>
                      <input type="text" class="form-control" id="clinicianName" name="clinicianName" placeholder="Demandeur" title="Please enter demandeur" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['request_clinician_name']; ?>" style="width:100%;" />
                    </td>
                    <td><label for="clinicanTelephone">Téléphone </label></td>
                    <td>
                      <input type="text" class="form-control checkNum" id="clinicanTelephone" name="clinicanTelephone" placeholder="Téléphone" title="Please enter téléphone" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['request_clinician_phone_number']; ?>" style="width:100%;" />
                    </td>
                    <td><label for="supportPartner">Partnaire d'appui </label></td>
                    <td>
                      <!-- <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" <?php echo $disable; ?> value="< ?php echo $vlQueryInfo['facility_support_partner']; ?>" style="width:100%;"/> -->
                      <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" <?php echo $disable; ?> style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($implementingPartnerList as $implementingPartner) {
                        ?>
                          <option value="<?php echo base64_encode($implementingPartner['i_partner_id']); ?>" <?php echo ($implementingPartner['i_partner_id'] == $vlQueryInfo['implementing_partner']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="">Date de la demande </label></td>
                    <td>
                      <input type="text" class="form-control date" id="dateOfDemand" name="dateOfDemand" placeholder="e.g 09-Jan-1992" title="Please enter date de la demande" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['date_test_ordered_by_physician']; ?>" style="width:100%;" />
                    </td>
                    <td><label for="fundingSource">Source de financement </label></td>
                    <td>
                      <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" <?php echo $disable; ?> style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($fundingSourceList as $fundingSource) {
                        ?>
                          <option value="<?php echo base64_encode($fundingSource['funding_source_id']); ?>" <?php echo ($fundingSource['funding_source_id'] == $vlQueryInfo['funding_source']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <!-- <td><label for="implementingPartner">Partenaire de mise en œuvre </label></td>
                                <td>
                                    <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" <?php echo $disable; ?> style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      < ?php
                                      foreach($implementingPartnerList as $implementingPartner){
                                      ?>
                                        <option value="< ?php echo base64_encode($implementingPartner['i_partner_id']); ?>" <?php echo ($implementingPartner['i_partner_id'] == $vlQueryInfo['implementing_partner']) ? 'selected="selected"' : ''; ?>>< ?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                      < ?php } ?>
                                    </select>
                                </td> -->
                  </tr>
                </table>
                <div class="box-header with-border">
                  <h3 class="box-title">Information sur le patient </h3>
                </div>
                <table class="table" style="width:100%">
                  <tr>
                    <td style="width:10%;"><label for="">Date de naissance </label></td>
                    <td style="width:15%;">
                      <input type="text" class="form-control date" id="dob" name="dob" placeholder="e.g 09-Jan-1992" title="Please select date de naissance" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['patient_dob']; ?>" style="width:100%;" />
                    </td>
                    <td style="width:6%;"><label for="ageInYears">Âge en années </label></td>
                    <td style="width:19%;">
                      <input type="text" class="form-control checkNum" id="ageInYears" name="ageInYears" placeholder="Aannées" title="Please enter àge en années" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['patient_age_in_years']; ?>" style="width:100%;" />
                    </td>
                    <td style="width:10%;"><label for="ageInMonths">Âge en mois </label></td>
                    <td style="width:15%;">
                      <input type="text" class="form-control checkNum" id="ageInMonths" name="ageInMonths" placeholder="Mois" title="Please enter àge en mois" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['patient_age_in_months']; ?>" style="width:100%;" />
                    </td>
                    <td style="width:10%;text-align:center;"><label for="sex">Sexe </label></td>
                    <td style="width:15%;">
                      <label class="radio-inline" style="padding-left:12px !important;margin-left:0;">M</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="genderMale" name="gender" <?php echo $disable; ?> value="male" title="Please check sexe" <?php echo (trim($vlQueryInfo['patient_gender']) == "male") ? 'checked="checked"' : ''; ?>>
                      </label>
                      <label class="radio-inline" style="padding-left:12px !important;margin-left:0;">F</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="genderFemale" name="gender" <?php echo $disable; ?> value="female" title="Please check sexe" <?php echo (trim($vlQueryInfo['patient_gender']) == "female") ? 'checked="checked"' : ''; ?>>
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="patientArtNo">Code du patient </label></td>
                    <td>
                      <input type="text" class="form-control" id="patientArtNo" name="patientArtNo" placeholder="Code du patient" title="Please enter code du patient" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['patient_art_no']; ?>" style="width:100%;" />
                    </td>
                    <td colspan="2">
                      <label for="isPatientNew">Si S/ARV </label>
                      <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="isPatientNewYes" name="isPatientNew" <?php echo ($vlQueryInfo['is_patient_new'] == 'yes') ? 'checked="checked"' : ''; ?> value="yes" <?php echo $disable; ?> title="Please check Si S/ARV">
                      </label>
                      <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Non</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="isPatientNewNo" name="isPatientNew" <?php echo ($vlQueryInfo['is_patient_new'] == 'no') ? 'checked="checked"' : ''; ?> <?php echo $disable; ?> value="no">
                      </label>
                    </td>
                    <td class="du"><label for="">Date du début des ARV </label></td>
                    <td class="du">
                      <input type="text" class="form-control date" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="e.g 09-Jan-1992" title="Please enter date du début des ARV" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['date_of_initiation_of_current_regimen']; ?>" style="width:100%;" /> &nbsp;(Jour/Mois/Année) </span>
                    </td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><label>Régime ARV en cours </label></td>
                    <td>
                      <select class="form-control" name="artRegimen" id="artRegimen" title="Please choose régime ARV en cours" <?php echo $disable; ?> style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($aResult as $arv) {
                        ?>
                          <option value="<?php echo $arv['art_code']; ?>" <?php echo ($arv['art_code'] == $vlQueryInfo['current_regimen']) ? 'selected="selected"' : ''; ?>><?php echo $arv['art_code']; ?></option>
                        <?php
                        }
                        ?>
                        <option value="other">Autre</option>
                      </select>
                      <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="Enter Régime ARV" title="Please enter régime ARV" style="width:100%;margin-top:1vh;display:none;">
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="4">
                      <label for="hasChangedRegimen">Ce patient a-t-il déjà changé de régime de traitement? </label>
                      <label class="radio-inline">&nbsp;&nbsp;&nbsp;&nbsp;Oui </label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="changedRegimenYes" name="hasChangedRegimen" value="yes" title="Please check any of one option" <?php echo $disable; ?> <?php echo (trim($vlQueryInfo['has_patient_changed_regimen']) == "yes") ? 'checked="checked"' : ''; ?>>
                      </label>
                      <label class="radio-inline">Non </label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="changedRegimenNo" name="hasChangedRegimen" value="no" title="Please check any of one option" <?php echo $disable; ?> <?php echo (trim($vlQueryInfo['has_patient_changed_regimen']) == "no") ? 'checked="checked"' : ''; ?>>
                      </label>
                    </td>
                    <td colspan="2"><label for="reasonForArvRegimenChange" class="arvChangedElement" style="display:<?php echo (trim($vlQueryInfo['has_patient_changed_regimen']) == "yes") ? '' : 'none'; ?>;">Motif de changement de régime ARV </label></td>
                    <td colspan="2">
                      <input type="text" class="form-control arvChangedElement" id="reasonForArvRegimenChange" name="reasonForArvRegimenChange" placeholder="Motif de changement de régime ARV" title="Please enter motif de changement de régime ARV" value="<?php echo $vlQueryInfo['reason_for_regimen_change']; ?>" <?php echo $disable; ?> style="width:100%;display:<?php echo (trim($vlQueryInfo['has_patient_changed_regimen']) == "yes") ? '' : 'none'; ?>;" />
                    </td>
                  </tr>
                  <tr class="arvChangedElement" style="display:<?php echo (trim($vlQueryInfo['has_patient_changed_regimen']) == "yes") ? '' : 'none'; ?>;">
                    <td><label for="">Date du changement de régime ARV </label></td>
                    <td colspan="2">
                      <input type="text" class="form-control date" id="dateOfArvRegimenChange" name="dateOfArvRegimenChange" placeholder="e.g 09-Jan-1992" title="Please enter date du changement de régime ARV" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['regimen_change_date']; ?>" style="width:100%;" /> (Jour/Mois/Année)
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><label for="reasonForRequest">Motif de la demande </label></td>
                    <td colspan="2">
                      <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose motif de la demande" <?php echo $disable; ?>>
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($vlTestReasonResult as $tReason) {
                        ?>
                          <option value="<?php echo $tReason['test_reason_id']; ?>" <?php echo ($vlQueryInfo['reason_for_vl_testing'] == $tReason['test_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($tReason['test_reason_name']); ?></option>
                        <?php } ?>
                        <option value="other">Autre</option>
                      </select>
                    </td>
                    <td style="text-align:center;"><label for="viralLoadNo">Charge virale N </label></td>
                    <td colspan="2">
                      <input type="text" class="form-control" id="viralLoadNo" name="viralLoadNo" placeholder="Charge virale N" title="Please enter charge virale N" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['vl_test_number']; ?>" style="width:100%;" />
                    </td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr class="newVlTestReason" style="display:none;">
                    <td><label for="newVlTestReason">Autre, à préciser <span class="mandatory">*</span></label></td>
                    <td>
                      <input type="text" class="form-control" name="newVlTestReason" id="newVlTestReason" placeholder="Virale Demande Raison" title="Please enter virale demande raison" <?php echo $disable; ?> style="width:100%;">
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr id="femaleElements">
                    <td><label for="breastfeeding">Si Femme : </label></td>
                    <td colspan="2">
                      <label for="breastfeeding">allaitante ?</label>
                      <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" <?php echo (trim($vlQueryInfo['is_patient_breastfeeding']) == "yes") ? 'checked="checked"' : ''; ?> value="yes" <?php echo $disable; ?> title="Please check Si allaitante">
                      </label>
                      <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Non</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" <?php echo (trim($vlQueryInfo['is_patient_breastfeeding']) == "no") ? 'checked="checked"' : ''; ?> value="no" <?php echo $disable; ?>>
                      </label>
                    </td>
                    <td colspan="5"><label for="patientPregnant">Ou enceinte ? </label>
                      <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="pregYes" name="patientPregnant" <?php echo (trim($vlQueryInfo['is_patient_pregnant']) == "yes") ? 'checked="checked"' : ''; ?> value="yes" <?php echo $disable; ?> title="Please check Si Ou enceinte ">
                      </label>
                      <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Non</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" class="" id="pregNo" name="patientPregnant" <?php echo (trim($vlQueryInfo['is_patient_pregnant']) == "no") ? 'checked="checked"' : ''; ?> value="no" <?php echo $disable; ?>>
                      </label>&nbsp;&nbsp;&nbsp;&nbsp;
                      <label for="trimester">Si Femme enceinte </label>
                      <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Trimestre 1</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" id="trimester1" name="trimester" <?php echo (trim($vlQueryInfo['pregnancy_trimester']) == "1") ? 'checked="checked"' : ''; ?> value="1" <?php echo $disable; ?> title="Please check trimestre">
                      </label>
                      <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Trimestre 2</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" id="trimester2" name="trimester" <?php echo (trim($vlQueryInfo['pregnancy_trimester']) == "2") ? 'checked="checked"' : ''; ?> value="2" <?php echo $disable; ?>>
                      </label>
                      <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Trimestre 3</label>
                      <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="radio" id="trimester3" name="trimester" <?php echo (trim($vlQueryInfo['pregnancy_trimester']) == "3") ? 'checked="checked"' : ''; ?> value="3" <?php echo $disable; ?>>
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="lastViralLoadResult">Résultat dernière charge virale </label></td>
                    <td colspan="2">
                      <input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Résultat dernière charge virale" title="Please enter résultat dernière charge virale" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['last_viral_load_result']; ?>" style="width:100%;" />
                    </td>
                    <td>copies/ml</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><label for="">Date dernière charge virale (demande) </label></td>
                    <td colspan="2">
                      <input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="e.g 09-Jan-1992" title="Please enter date dernière charge virale" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['last_viral_load_date']; ?>" style="width:100%;" />
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="8"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le service demandeur dans la structure de soins</label></td>
                  </tr>
                </table>
                <div class="box-header with-border">
                  <h3 class="box-title">Informations sur le prélèvement </h3>
                </div>
                <table class="table" style="width:100%">
                  <tr>
                    <td style="width:25%;"><label for="">Date du prélèvement </label></td>
                    <td style="width:25%;">
                      <input type="text" class="form-control dateTime" id="sampleCollectionDate" name="sampleCollectionDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date du prélèvement" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['sample_collection_date']; ?>" style="width:100%;" />
                    </td>
                    <td style="width:25%;"></td>
                    <td style="width:25%;"></td>
                  </tr>
                  <?php
                  if (isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled") {
                  ?>
                    <tr>
                      <td><label for="specimenType">Type déchantillon </label></td>
                      <td>
                        <select name="specimenType" id="specimenType" class="form-control" title="Please choose type déchantillon" <?php echo $disable; ?> style="width:100%;">
                          <option value=""> -- Sélectionner -- </option>
                          <?php
                          foreach ($sResult as $type) {
                          ?>
                            <option value="<?php echo $type['sample_id']; ?>" <?php echo ($vlQueryInfo['sample_type'] == $type['sample_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($type['sample_name']); ?></option>
                          <?php
                          }
                          ?>
                        </select>
                      </td>
                      <td></td>
                      <td></td>
                    </tr>
                  <?php } ?>
                  <tr class="plasmaElement" style="display:<?php echo ($vlQueryInfo['sample_type'] == 2) ? '' : 'none'; ?>;">
                    <td><label for="conservationTemperature">Si plasma,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Température de conservation </label></td>
                    <td>
                      <input type="text" class="form-control checkNum" id="conservationTemperature" name="conservationTemperature" placeholder="Température de conservation" title="Please enter température de conservation" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['plasma_conservation_temperature']; ?>" style="width:100%;" />&nbsp;(°C)
                    </td>
                    <td style="text-align:center;"><label for="durationOfConservation">Durée de conservation </label></td>
                    <td>
                      <input type="text" class="form-control" id="durationOfConservation" name="durationOfConservation" placeholder="e.g 9/1" title="Please enter durée de conservation" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['plasma_conservation_duration']; ?>" style="width:100%;" />&nbsp;(Jour/Heures)
                    </td>
                  </tr>
                  <tr>
                    <td><label for="">Date de départ au Labo biomol </label></td>
                    <td>
                      <input type="text" class="form-control dateTime" id="dateDispatchedFromClinicToLab" name="dateDispatchedFromClinicToLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de départ au Labo biomol" <?php echo $disable; ?> value="<?php echo $vlQueryInfo['date_dispatched_from_clinic_to_lab']; ?>" style="width:100%;" />
                    </td>
                    <td></td>
                    <td></td>
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
                    <td style="width:25%;"><label for="">Date de réception de léchantillon <span class="mandatory">*</span></label></td>
                    <td style="width:25%;">
                      <input type="text" class="form-control dateTime isRequired" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réception de léchantillon" value="<?php echo $vlQueryInfo['sample_received_at_vl_lab_datetime']; ?>" onchange="checkSampleReceviedDate();" style="width:100%;" />
                    </td>
                    <td style="width:25%;"></td>
                    <td style="width:25%;"></td>
                  </tr>
                  <?php
                  if (isset($arr['testing_status']) && trim($arr['testing_status']) == "enabled") {
                  ?>
                    <tr>
                      <td><label for="">Décision prise </label></td>
                      <td>
                        <select class="form-control" id="status" name="status" title="Please select décision prise" onchange="checkTestStatus();" style="width:100%;">
                          <option value=""> -- Sélectionner -- </option>
                          <!-- <option value="6" <?php echo ($vlQueryInfo['result_status'] == 6) ? 'selected="selected"' : ''; ?>> En attente d'approbation Clinique </option> -->
                          <option value="7" <?php echo ($vlQueryInfo['result_status'] == 7) ? 'selected="selected"' : ''; ?>>Echantillon accepté</option>
                          <option value="4" <?php echo ($vlQueryInfo['result_status'] == 4) ? 'selected="selected"' : ''; ?>>Echantillon rejeté</option>
                        </select>
                      </td>
                      <td></td>
                      <td></td>
                    </tr>
                  <?php } ?>
                  <tr class="rejectionReason" style="display:<?php echo ($vlQueryInfo['result_status'] == 4) ? '' : 'none'; ?>;">
                    <td><label for="rejectionReason">Motifs de rejet <span class="mandatory">*</span> </label></td>
                    <td>
                      <select class="form-control" id="rejectionReason" name="rejectionReason" title="Please select motifs de rejet" onchange="checkRejectionReason();" style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php
                        foreach ($rejectionResult as $rjctReason) {
                        ?>
                          <option value="<?php echo $rjctReason['rejection_reason_id']; ?>" <?php echo ($vlQueryInfo['reason_for_sample_rejection'] == $rjctReason['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($rjctReason['rejection_reason_name']); ?></option>
                        <?php } ?>
                        <option value="other">Autre</option>
                      </select>
                    </td>
                    <td style="text-align:center;"><label for="newRejectionReason" class="newRejectionReason" style="display:none;">Autre, à préciser <span class="mandatory">*</span></label></td>
                    <td><input type="text" class="form-control newRejectionReason" id="newRejectionReason" name="newRejectionReason" placeholder="Motifs de rejet" title="Please enter motifs de rejet" style="width:100%;display:none;" /></td>
                  </tr>
                  <!-- <tr>
                                <td><label for="sampleCode">Code Labo </label> <span class="mandatory">*</span></td>
                                <td>
                                    <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Code Labo" title="Please enter code labo" value="< ?php echo $vlQueryInfo['sample_code']; ?>" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr> -->
                  <tr>
                    <td><label for="labId">Nom du laboratoire <span class="mandatory">*</span> </label> </td>
                    <td>
                      <select name="labId" id="labId" class="form-control isRequired" title="Please choose laboratoire" style="width:100%;">
                        <?= $general->generateSelectOptions($testingLabs, $vlQueryInfo['lab_id'], '-- Sélectionner --'); ?>
                      </select>
                    </td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="4" style="height:30px;border:none;"></td>
                  </tr>
                  <tr>
                    <td><label for="">Date de réalisation de la charge virale <span class="mandatory">*</span></label></td>
                    <td>
                      <input type="text" class="form-control dateTime isRequired" id="dateOfCompletionOfViralLoad" name="dateOfCompletionOfViralLoad" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réalisation de la charge virale" value="<?php echo $vlQueryInfo['sample_tested_datetime']; ?>" style="width:100%;" />
                    </td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><label for="testingPlatform">Technique utilisée <span class="mandatory">*</span></label></td>
                    <td>
                      <select class="isRequired form-control" id="testingPlatform" name="testingPlatform" title="Please select technique utilisée" style="width:100%;">
                        <option value=""> -- Sélectionner -- </option>
                        <?php foreach ($importResult as $mName) { ?>
                          <option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']; ?>" <?php echo ($vlQueryInfo['vl_test_platform'] == $mName['machine_name']) ? 'selected="selected"' : ''; ?>><?php echo $mName['machine_name']; ?></option>
                        <?php } ?>
                      </select>
                    </td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="vlResult"><label for="vlResult">Résultat <span class="mandatory">*</span></label></td>
                    <td class="vlResult">
                      <input type="text" class="vlResult form-control isRequired checkNum" id="vlResult" name="vlResult" placeholder="Résultat (copies/ml)" title="Please enter résultat" value="<?php echo $vlQueryInfo['result']; ?>" onchange="calculateLogValue(this)" style="width:100%;" />
                      <input type="checkbox" class="specialResults" id="vlLt20" name="vlLt20" value="yes" title="Please check VL value" <?php echo ($vlQueryInfo['result'] == '< 20' || $vlQueryInfo['result'] == '<20') ? 'checked="checked"' : ''; ?>>
                      < 20<br>
                        <input type="checkbox" class="specialResults" id="vlLt40" name="vlLt40" value="yes" title="Please check VL value" <?php echo ($vlQueryInfo['result'] == '< 40' || $vlQueryInfo['result'] == '<40') ? 'checked="checked"' : ''; ?>>
                        < 40<br>
                          <input type="checkbox" class="specialResults" id="vlLt400" name="vlLt400" value="yes" title="Please check VL value" <?php echo ($vlQueryInfo['result'] == '< 400' || $vlQueryInfo['result'] == '<400') ? 'checked="checked"' : ''; ?>>
                          < 400<br>
                            <input type="checkbox" class="specialResults" id="vlTND" name="vlTND" value="yes" title="Please check VL value" <?php echo in_array(strtolower($vlQueryInfo['result']), array('target not detected', 'non détecté', 'non détecté', 'non detecte', 'non detectee', 'tnd', 'bdl', 'below detection level')) ? 'checked="checked"' : ''; ?>> Target Not Detected / Non Détecté
                    </td>
                    <td class="vlLog" style="text-align:center;"><label for="vlLog">Log </label></td>
                    <td class="vlLog">
                      <input type="text" class="vlLog form-control checkNum" id="vlLog" name="vlLog" placeholder="Log" title="Please enter log" value="<?php echo $vlQueryInfo['result_value_log']; ?>" onchange="calculateLogValue(this)" style="width:100%;" />
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4"><label class="radio-inline" style="margin:0;padding:0;">A remplir par le service effectuant la charge virale </label></td>
                  </tr>
                  <!--<tr><td colspan="4" style="height:30px;border:none;"></td></tr>
                            <tr>
                                <td><label for="">Date de remise du résultat </label></td>
                                <td>
                                  <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de remise du résultat" value="< ?php echo $vlQueryInfo['result_printed_datetime']; ?>" onchange="checkSampleTestingDate();" style="width:100%;"/>
                                </td>
                                <td></td><td></td>
                            </tr>-->
                </table>
              </div>
            </div>
            <div class="box-header with-border">
              <label class="radio-inline" style="margin:0;padding:0;">1. Biffer la mention inutile <br>2. Sélectionner un seul régime de traitement </label>
            </div>
          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <input type="hidden" id="rSrc" name="rSrc" value="er" />
            <input type="hidden" id="dubPatientArtNo" name="dubPatientArtNo" value="<?php echo $vlQueryInfo['patient_art_no']; ?>" />
            <input type="hidden" id="vlSampleId" name="vlSampleId" value="<?php echo $vlQueryInfo['vl_sample_id']; ?>" />
            <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
            <a href="vlTestResult.php" class="btn btn-default"> Cancel</a>
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
  $(document).ready(function() {
    $('.date').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
    }).click(function() {
      $('.ui-datepicker-calendar').show();
    });

    $('.dateTime').datetimepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "HH:mm",
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
    }).click(function() {
      $('.ui-datepicker-calendar').show();
    });

    $('.date').mask('99-aaa-9999');
    $('.dateTime').mask('99-aaa-9999 99:99');

    if ($("#status").val() == 4) {
      $(".rejectionReason").show();
      $("#rejectionReason").addClass('isRequired');
      $("#vlResult").val('').css('pointer-events', 'none');
      $("#vlLog").val('').css('pointer-events', 'none');
      $(".vlResult, .vlLog").hide();
      $("#vlResult").removeClass('isRequired');
    } else {
      $(".rejectionReason").hide();
      $("#rejectionReason").removeClass('isRequired');
      $("#vlResult").css('pointer-events', 'auto');
      $("#vlLog").css('pointer-events', 'auto');
      $(".vlResult, .vlLog").show();
      $("#vlResult").addClass('isRequired');
    }
  });

  function checkTestStatus() {
    var status = $("#status").val();
    if (status == 4) {
      $('.specialResults').prop('checked', false).removeAttr('checked');
      $('#vlResult').attr('disabled', false);
      $('#vlLog').attr('disabled', false);
      $(".rejectionReason").show();
      $("#rejectionReason").addClass('isRequired');
      $("#vlResult").val('').css('pointer-events', 'none');
      $("#vlLog").val('').css('pointer-events', 'none');
      $("#rejectionReason").val('').css('pointer-events', 'auto');
      $(".vlResult, .vlLog").hide();
      $("#vlResult").removeClass('isRequired');
    } else {
      $(".rejectionReason").hide();
      $("#rejectionReason").removeClass('isRequired');
      $("#vlResult").css('pointer-events', 'auto');
      $("#vlLog").css('pointer-events', 'auto');
      $("#vlResult").val('').css('pointer-events', 'auto');
      $("#vlLog").val('').css('pointer-events', 'auto');
      $(".vlResult, .vlLog").show();
      $("#vlResult").addClass('isRequired');
    }
  }

  function checkRejectionReason() {
    var rejectionReason = $("#rejectionReason").val();
    if (rejectionReason == "other") {
      $(".newRejectionReason").show();
      $("#newRejectionReason").addClass('isRequired');
    } else {
      $(".newRejectionReason").hide();
      $("#newRejectionReason").removeClass('isRequired');
    }
  }

  function checkSampleReceviedDate() {
    var sampleCollectionDate = $("#sampleCollectionDate").val();
    var sampleReceivedDate = $("#sampleReceivedDate").val();
    if ($.trim(sampleCollectionDate) != '' && $.trim(sampleReceivedDate) != '') {
      //Set sample coll. datetime
      splitSampleCollDateTime = sampleCollectionDate.split(" ");
      splitSampleCollDate = splitSampleCollDateTime[0].split("-");
      var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2] + ", " + splitSampleCollDate[0]);
      var monthDigit = sampleCollOn.getMonth();
      var smplCollYear = splitSampleCollDate[2];
      var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit) + parseInt(1));
      smplCollMonth = (smplCollMonth < 10) ? '0' + smplCollMonth : smplCollMonth;
      var smplCollDate = splitSampleCollDate[0];
      sampleCollDateTime = smplCollYear + "-" + smplCollMonth + "-" + smplCollDate + " " + splitSampleCollDateTime[1] + ":00";
      //Set sample rece. datetime
      splitSampleReceivedDateTime = sampleReceivedDate.split(" ");
      splitSampleReceivedDate = splitSampleReceivedDateTime[0].split("-");
      var sampleReceivedOn = new Date(splitSampleReceivedDate[1] + splitSampleReceivedDate[2] + ", " + splitSampleReceivedDate[0]);
      var monthDigit = sampleReceivedOn.getMonth();
      var smplReceivedYear = splitSampleReceivedDate[2];
      var smplReceivedMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit) + parseInt(1));
      smplReceivedMonth = (smplReceivedMonth < 10) ? '0' + smplReceivedMonth : smplReceivedMonth;
      var smplReceivedDate = splitSampleReceivedDate[0];
      sampleReceivedDateTime = smplReceivedYear + "-" + smplReceivedMonth + "-" + smplReceivedDate + " " + splitSampleReceivedDateTime[1] + ":00";
      //Check diff
      if (moment(sampleCollDateTime).diff(moment(sampleReceivedDateTime)) > 0) {
        alert("L'échantillon de données reçues ne peut pas être antérieur à la date de collecte de l'échantillon!");
        $("#sampleReceivedDate").val("");
      }
    }
  }

  function checkSampleTestingDate() {
    var sampleCollectionDate = $("#sampleCollectionDate").val();
    var sampleTestingDate = $("#sampleTestingDateAtLab").val();
    if ($.trim(sampleCollectionDate) != '' && $.trim(sampleTestingDate) != '') {
      //Set sample coll. date
      splitSampleCollDateTime = sampleCollectionDate.split(" ");
      splitSampleCollDate = splitSampleCollDateTime[0].split("-");
      var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2] + ", " + splitSampleCollDate[0]);
      var monthDigit = sampleCollOn.getMonth();
      var smplCollYear = splitSampleCollDate[2];
      var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit) + parseInt(1));
      smplCollMonth = (smplCollMonth < 10) ? '0' + smplCollMonth : smplCollMonth;
      var smplCollDate = splitSampleCollDate[0];
      sampleCollDateTime = smplCollYear + "-" + smplCollMonth + "-" + smplCollDate + " " + splitSampleCollDateTime[1] + ":00";
      //Set sample testing date
      splitSampleTestedDateTime = sampleTestingDate.split(" ");
      splitSampleTestedDate = splitSampleTestedDateTime[0].split("-");
      var sampleTestingOn = new Date(splitSampleTestedDate[1] + splitSampleTestedDate[2] + ", " + splitSampleTestedDate[0]);
      var monthDigit = sampleTestingOn.getMonth();
      var smplTestingYear = splitSampleTestedDate[2];
      var smplTestingMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit) + parseInt(1));
      smplTestingMonth = (smplTestingMonth < 10) ? '0' + smplTestingMonth : smplTestingMonth;
      var smplTestingDate = splitSampleTestedDate[0];
      sampleTestingAtLabDateTime = smplTestingYear + "-" + smplTestingMonth + "-" + smplTestingDate + " " + splitSampleTestedDateTime[1] + ":00";
      //Check diff
      if (moment(sampleCollDateTime).diff(moment(sampleTestingAtLabDateTime)) > 0) {
        alert("La date d'essai de l'échantillon ne peut pas être antérieure à la date de collecte de l'échantillon!");
        $("#sampleTestingDateAtLab").val("");
      }
    }
  }

  function calculateLogValue(obj) {
    if (obj.id == "vlResult") {
      absValue = $("#vlResult").val();
      if (absValue != '' && absValue != 0) {
        $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
      } else {
        $("#vlLog").val('');
      }
    }
    if (obj.id == "vlLog") {
      logValue = $("#vlLog").val();
      if (logValue != '' && logValue != 0) {
        var absVal = Math.round(Math.pow(10, logValue) * 100) / 100;
        if (absVal != 'Infinity') {
          $("#vlResult").val(Math.round(Math.pow(10, logValue) * 100) / 100);
        } else {
          $("#vlResult").val('');
        }
      }
    }
  }

  function validateNow() {
    flag = deforayValidator.init({
      formId: 'updateVlRequestForm'
    });
    if (flag) {
      $.blockUI();
      document.getElementById('updateVlRequestForm').submit();
    }
  }




  $(document).ready(function() {

    $('#vlResult, #vlLog').on('input', function(e) {
      if (this.value != '') {
        $('.specialResults').attr('disabled', true);
      } else {
        $('.specialResults').attr('disabled', false);
      }
    });

    $('.specialResults').change(function() {
      if ($(this).is(':checked')) {
        $('#vlResult, #vlLog').val('');
        $('#vlResult,#vlLog').attr('readonly', true);
        $('#vlResult, #vlLog').removeClass('isRequired');
        $(".specialResults").not(this).attr('disabled', true);
        $('.specialResults').not(this).prop('checked', false).removeAttr('checked');
      } else {
        $('#vlResult,#vlLog').attr('readonly', false);
        $('#vlResult').addClass('isRequired');
        $(".specialResults").not(this).attr('disabled', false);
      }
    });



    if ($(".specialResults:checked").length > 0) {
      $('#vlResult, #vlLog').val('');
      $('#vlResult,#vlLog').attr('readonly', true);
      $('#vlResult, #vlLog').removeClass('isRequired');
      //$(".specialResults").not(this).attr('disabled',true);
      //$('.specialResults').not(this).prop('checked', false).removeAttr('checked');
    }
    if ($('#vlResult, #vlLog').val() != '') {
      $(".specialResults").attr('disabled', true);
      $('#vlResult').addClass('isRequired');
    }

  });
</script>
<?php
//include(APPLICATION_PATH.'/footer.php');
?>