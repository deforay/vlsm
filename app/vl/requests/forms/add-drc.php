<?php

// imported in addVlRequest.php based on country in global config

ob_start();
//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);
//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);
//check remote user
$rKey = '';
$pdQuery = "SELECT * FROM province_details";
if ($_SESSION['instanceType'] == 'remoteuser') {
	$sampleCodeKey = 'remote_sample_code_key';
	$sampleCode = 'remote_sample_code';
	//check user exist in user_facility_map table
	$chkUserFcMapQry = "SELECT user_id from user_facility_map where user_id='" . $_SESSION['userId'] . "'";
	$chkUserFcMapResult = $db->query($chkUserFcMapQry);
	if ($chkUserFcMapResult) {
		$pdQuery = "SELECT * FROM province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
	}
	$rKey = 'R';
} else {
	$sampleCodeKey = 'sample_code_key';
	$sampleCode = 'sample_code';
	$rKey = '';
}
$pdResult = $db->query($pdQuery);
$province = "";
$province .= "<option value=''> -- Sélectionner -- </option>";
foreach ($pdResult as $provinceName) {
	$province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}
$facility = $general->generateSelectOptions($healthFacilities, null, '-- Sélectionner --');

//get ART list
$aQuery = "SELECT * from r_vl_art_regimen";
$aResult = $db->query($aQuery);

$sKey = '';
$sFormat = '';
?>
<style>
	.translate-content {
		color: #0000FF;
		font-size: 12.5px;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa-solid fa-pen-to-square"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa-solid fa-chart-pie"></i> Home</a></li>
			<li class="active">Add VL Request</li>
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
				<form class="form-horizontal" method="post" name="addVlRequestForm" id="addVlRequestForm" autocomplete="off" action="addVlRequestHelperDrc.php">
					<div class="box-body">
						<div class="box box-default">
							<div class="box-body">
								<div class="box-header with-border">
									<h3 class="box-title">1. Réservé à la structure de soins</h3>
								</div>
								<div class="box-header with-border">
									<h3 class="box-title">Information sur la structure de soins</h3>

								</div>
								<!-- <h4>exemple de code</h4> -->
								<!--<h4 style="display:none;" id="sampleCodeValue"></h4>-->
								<table class="table" style="width:100%">
									<tr>
										<?php if ($_SESSION['instanceType'] == 'remoteuser') { ?>
											<td><label for="sampleCode">Échantillon ID </label></td>
											<td>
												<span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
												<input type="hidden" id="sampleCode" name="sampleCode" />
											</td>
										<?php } else { ?>
											<td><label for="sampleCode">Échantillon ID </label><span class="mandatory">*</span></td>
											<td>
												<input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" readonly placeholder="Échantillon ID" title="Please enter échantillon id" style="width:100%;" onchange="checkSampleNameValidation('form_vl','<?php echo $sampleCode; ?>',this.id,null,'The échantillon id that you entered already exists. Please try another échantillon id',null)" />
											</td>
										<?php } ?>
										<td><label for="serialNo">Recency ID</label></td>
										<td><input type="text" class="form-control" id="serialNo" name="serialNo" placeholder="Recency ID" title="Please enter recency id" style="width:100%;" /></td>
										<td></td>
										<td></td>
									</tr>
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
										<td><label for="clinicName">POINT DE COLLECT </label><span class="mandatory">*</span></td>
										<td>
											<select class="form-control isRequired " name="clinicName" id="clinicName" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
												<?php echo $facility;  ?>
											</select>
										</td>
									</tr>
									<tr>
										<td><label for="clinicianName">Demandeur </label></td>
										<td>
											<input type="text" class="form-control" id="clinicianName" name="clinicianName" placeholder="Demandeur" title="Please enter demandeur" style="width:100%;" />
										</td>
										<td><label for="clinicanTelephone">Téléphone </label></td>
										<td>
											<input type="text" class="form-control forceNumeric" id="clinicanTelephone" name="clinicanTelephone" placeholder="Téléphone" title="Please enter téléphone" style="width:100%;" />
										</td>
										<td><label for="implementingPartner">Partnaire d'appui </label></td>
										<td>
											<!-- <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" style="width:100%;"/> -->
											<select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" style="width:100%;">
												<option value=""> -- Sélectionner -- </option>
												<?php
												foreach ($implementingPartnerList as $implementingPartner) {
												?>
													<option value="<?php echo base64_encode($implementingPartner['i_partner_id']); ?>"><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<td><label for="">Date de la demande </label></td>
										<td>
											<input type="text" class="form-control date" id="dateOfDemand" name="dateOfDemand" placeholder="e.g 09-Jan-1992" title="Please enter date de la demande" style="width:100%;" />
										</td>
										<td><label for="fundingSource">Source de financement </label></td>
										<td>
											<select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" style="width:100%;">
												<option value=""> -- Sélectionner -- </option>
												<?php
												foreach ($fundingSourceList as $fundingSource) {
												?>
													<option value="<?php echo base64_encode($fundingSource['funding_source_id']); ?>"><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
												<?php } ?>
											</select>
										</td>
										<?php if ($_SESSION['instanceType'] == 'remoteuser') { ?>
											<!-- <tr> -->
											<td><label for="labId">Nom du laboratoire <span class="mandatory">*</span></label> </td>
											<td>
												<select name="labId" id="labId" class="form-control isRequired" title="Please choose laboratoire" style="width:100%;">
													<?= $general->generateSelectOptions($testingLabs, null, '-- Sélectionner --'); ?>
												</select>
											</td>
											<!-- </tr> -->
										<?php } ?>
										<!-- <td><label for="implementingPartner">Partenaire de mise en œuvre </label></td>
                                <td>
                                    <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" style="width:100%;">
                                      <option value=""> -- Sélectionner -- </option>
                                      < ?php
                                      foreach($implementingPartnerList as $implementingPartner){
                                      ?>
                                        <option value="< ?php echo base64_encode($implementingPartner['i_partner_id']); ?>">< ?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                      < ?php } ?>
                                    </select>
                                </td> -->
									</tr>

								</table>
								<div class="box-header with-border">
									<h3 class="box-title">Information sur le patient </h3>&nbsp;&nbsp;&nbsp;
									<input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" placeholder="Code du patient" title="Please enter code du patient" />&nbsp;&nbsp;
									<a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa-solid fa-magnifying-glass"></i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Patient Found</b></span>
								</div>
								<table class="table" style="width:100%">
									<tr>
										<td style="width:10% !important;"><label for="">Date de naissance </label></td>
										<td style="width:15% !important;">
											<input type="text" class="form-control date" id="dob" name="dob" placeholder="e.g 09-Jan-1992" title="Please select date de naissance" onchange="getAge();checkARTInitiationDate();" style="width:100%;" />
										</td>
										<td style="width:6% !important;"><label for="ageInYears">Âge en années </label></td>
										<td style="width:19% !important;">
											<input type="text" class="form-control forceNumeric" id="ageInYears" name="ageInYears" placeholder="Aannées" title="Please enter àge en années" onchange="clearDOB(this.value);" style="width:100%;" />
										</td>
										<td style="width:10% !important;"><label for="ageInMonths">Âge en mois </label></td>
										<td style="width:15% !important;">
											<input type="text" class="form-control forceNumeric" id="ageInMonths" name="ageInMonths" placeholder="Mois" title="Please enter àge en mois" onchange="clearDOB(this.value);" style="width:100%;" />
										</td>
										<td style="width:10% !important;text-align:center;"><label for="sex">Sexe </label></td>
										<td style="width:15% !important;">
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
										<td><label for="patientArtNo">Code du patient <span class="mandatory">*</span></label></td>
										<td>
											<input type="text" class="form-control isRequired" id="patientArtNo" name="patientArtNo" placeholder="Code du patient" title="Please enter code du patient" style="width:100%;" onchange="checkPatientDetails('form_vl','patient_art_no',this,null)" />
										</td>
										<td colspan="2"><label for="isPatientNew">Si S/ARV </label>
											<label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="isPatientNewYes" name="isPatientNew" value="yes" title="Please check Si S/ ARV">
											</label>
											<label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Non</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" id="isPatientNewNo" name="isPatientNew" value="no">
											</label>
										</td>
										<td class="du" style="visibility:hidden;"><label for="">Date du début des ARV </label></td>
										<td class="du" style="visibility:hidden;">
											<input type="text" class="form-control date" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="e.g 09-Jan-1992" title="Please enter date du début des ARV" onchange="checkARTInitiationDate();checkLastVLTestDate();" style="width:100%;" /> &nbsp;(Jour/Mois/Année)
										</td>
										<td></td>
										<td></td>
									</tr>
									<tr>
										<td><label>Régime ARV en cours </label></td>
										<td>
											<select class="form-control" name="artRegimen" id="artRegimen" title="Please choose régime ARV en cours" onchange="checkARTRegimenValue();" style="width:100%;">
												<option value=""> -- Sélectionner -- </option>
												<?php foreach ($aResult as $arv) { ?>
													<option value="<?php echo $arv['art_code']; ?>"><?php echo $arv['art_code']; ?></option>
												<?php }
												if ($sarr['sc_user_type'] != 'vluser') {  ?>
													<option value="other">Autre</option>
												<?php } ?>
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
											<br><label class="radio-inline">&nbsp;&nbsp;&nbsp;&nbsp;Oui </label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="changedRegimenYes" name="hasChangedRegimen" value="yes" title="Please check any of one option">
											</label>
											<label class="radio-inline">Non </label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="changedRegimenNo" name="hasChangedRegimen" value="no" title="Please check any of one option">
											</label>
										</td>
										<td colspan="2"><label for="reasonForArvRegimenChange" class="arvChangedElement" style="display:none;">Motif de changement de régime ARV </label></td>
										<td colspan="2">
											<input type="text" class="form-control arvChangedElement" id="reasonForArvRegimenChange" name="reasonForArvRegimenChange" placeholder="Motif de changement de régime ARV" title="Please enter motif de changement de régime ARV" style="width:100%;display:none;" />
										</td>
									</tr>
									<tr class="arvChangedElement" style="display:none;">
										<td><label for="">Date du changement de régime ARV </label></td>
										<td colspan="3">
											<input type="text" class="form-control date" id="dateOfArvRegimenChange" name="dateOfArvRegimenChange" placeholder="e.g 09-Jan-1992" title="Please enter date du changement de régime ARV" style="width:100%;" /> &nbsp;(Jour/Mois/Année)
										</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
									<tr>
										<td><label for="reasonForRequest">Motif de la demande <span class="mandatory">*</span></label></td>
										<td colspan="3">
											<select name="vlTestReason" id="vlTestReason" class="form-control isRequired" title="Please choose motif de la demande" onchange="checkVLTestReason();">
												<option value=""> -- Sélectionner -- </option>
												<?php
												foreach ($testReason as $tReason) {
												?>
													<option value="<?php echo $tReason['test_reason_id']; ?>"><?php echo ucwords($tReason['test_reason_name']); ?></option>
												<?php } ?>
												<option value="other">Autre</option>
											</select>
										</td>
										<td style="text-align:center;"><label for="viralLoadNo">Charge virale N </label></td>
										<td colspan="3">
											<input type="text" class="form-control" id="viralLoadNo" name="viralLoadNo" placeholder="Charge virale N" title="Please enter charge virale N" style="width:100%;" />
										</td>
									</tr>
									<tr class="newVlTestReason" style="display:none;">
										<td><label for="newVlTestReason">Autre, à préciser <span class="mandatory">*</span></label></td>
										<td colspan="2">
											<input type="text" class="form-control" name="newVlTestReason" id="newVlTestReason" placeholder="Virale Demande Raison" title="Please enter virale demande raison" style="width:100%;">
										</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
									<tr id="femaleElements" style="display:none;">
										<td><strong>Si Femme : </strong></td>
										<td colspan="2">
											<label for="breastfeeding">allaitante ?</label>
											<label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check Si allaitante">
											</label>
											<label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Non</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no">
											</label>
										</td>
										<td colspan="5"><label for="patientPregnant">Ou enceinte ? </label>
											<label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Oui</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Si Ou enceinte ">
											</label>
											<label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Non</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" class="" id="pregNo" name="patientPregnant" value="no">
											</label>&nbsp;&nbsp;&nbsp;&nbsp;
											<label for="trimester">Si Femme enceinte </label>
											<label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Trimestre 1</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" id="trimester1" name="trimester" value="1" title="Please check trimestre">
											</label>
											<label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Trimestre 2</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" id="trimester2" name="trimester" value="2">
											</label>
											<label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Trimestre 3</label>
											<label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
												<input type="radio" id="trimester3" name="trimester" value="3">
											</label>
										</td>
									</tr>
									<tr>
										<td><label for="lastViralLoadResult">Résultat dernière charge virale </label></td>
										<td colspan="3">
											<input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Résultat dernière charge virale" title="Please enter résultat dernière charge virale" style="width:100%;" />
										</td>
										<td>copies/ml</td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
									<tr>
										<td><label for="">Date dernière charge virale (demande) </label></td>
										<td colspan="3">
											<input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="e.g 09-Jan-1992" title="Please enter date dernière charge virale" onchange="checkLastVLTestDate();" style="width:100%;" />
										</td>
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
									<h3 class="box-title">Informations sur le prélèvement <small>(A remplir par le préleveur)</small> </h3>
								</div>
								<table class="table" style="width:100%">
									<tr>
										<td><label for="">Date du prélèvement <span class="mandatory">*</span></label></td>
										<td>
											<input type="text" class="form-control dateTime isRequired" id="sampleCollectionDate" name="sampleCollectionDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date du prélèvement" onchange="checkSampleReceviedDate();checkSampleTestingDate();sampleCodeGeneration();" style="width:100%;" />
										</td>
										<td></td>
										<td></td>
									</tr>
									<?php if (isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled") { ?>
										<tr>
											<td><label for="specimenType">Type d'échantillon <span class="mandatory">*</span> </label></td>
											<td>
												<select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose type d'échantillon" onchange="checkSpecimenType();" style="width:100%;">
													<option value=""> -- Sélectionner -- </option>
													<?php foreach ($sResult as $type) { ?>
														<option value="<?php echo $type['sample_id']; ?>"><?php echo ucwords($type['sample_name']); ?></option>
													<?php } ?>
												</select>
											</td>
											<td></td>
											<td></td>
										</tr>
									<?php } ?>
									<tr class="plasmaElement" style="display:none;">
										<td><label for="conservationTemperature">Si plasma,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Température de conservation </label></td>
										<td>
											<input type="text" class="form-control forceNumeric" id="conservationTemperature" name="conservationTemperature" placeholder="Température de conservation" title="Please enter température de conservation" style="width:100%;" />&nbsp;(°C)
										</td>
										<td style="text-align:center;"><label for="durationOfConservation">Durée de conservation </label></td>
										<td>
											<input type="text" class="form-control" id="durationOfConservation" name="durationOfConservation" placeholder="e.g 9/1" title="Please enter durée de conservation" style="width:100%;" />&nbsp;(Jour/Heures)
										</td>
									</tr>
									<tr>
										<td><label for="">Date de départ au Labo biomol </label></td>
										<td>
											<input type="text" class="form-control dateTime" id="dateDispatchedFromClinicToLab" name="dateDispatchedFromClinicToLab" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de départ au Labo biomol" style="width:100%;" />
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
						<?php if ($_SESSION['instanceType'] != 'remoteuser') { ?>
							<div class="box box-primary">
								<div class="box-body">
									<div class="box-header with-border">
										<h3 class="box-title">2. Réservé au Laboratoire de biologie moléculaire </h3>
									</div>
									<table class="table" style="width:100%">
										<tr>
											<td style="width: 25%;"><label for="">Date de réception de l'échantillon <span class="mandatory">*</span> </label></td>
											<td style="width: 25%;">
												<input type="text" class="form-control dateTime isRequired" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réception de l'échantillon" <?php echo $labFieldDisabled; ?> onchange="checkSampleReceviedDate();" style="width:100%;" />
											</td>
											<td style="width: 25%;"><label for="labId">Nom du laboratoire </label> </td>
											<td style="width: 25%;">
												<select name="labId" id="labId" class="form-control" title="Please choose laboratoire" style="width:100%;">
													<?= $general->generateSelectOptions($testingLabs, null, '-- Sélectionner --'); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td style="width: 25%;"><label for="dateOfCompletionOfViralLoad">Date de réalisation de la charge virale </label></td>
											<td style="width: 25%;">
												<input type="text" class="form-control dateTime" id="dateOfCompletionOfViralLoad" name="dateOfCompletionOfViralLoad" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date de réalisation de la charge virale" <?php echo $labFieldDisabled; ?> style="width:100%;" />
											</td>
											<td style="width: 25%;"><label for="testingPlatform">Technique utilisée </label></td>
											<td style="width: 25%;">
												<select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform" <?php echo $labFieldDisabled; ?> style="width:100%;">
													<option value="">-- Sélectionner --</option>
													<?php foreach ($importResult as $mName) { ?>
														<option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']; ?>"><?php echo $mName['machine_name']; ?></option>
													<?php } ?>
												</select>
											</td>
										</tr>
										<tr>
											<td class="hivDetection resultSection" style="display: none;"><?php echo _("HIV Detection"); ?></td>
											<td class="hivDetection resultSection" style="display: none;">
												<select name="hivDetection" id="hivDetection" class="form-control" title="Please choose HIV detection">
													<option value="">-- <?php echo _("Select"); ?> --</option>
													<option value="HIV-1 Detected"><?php echo _("HIV-1 Detected"); ?></option>
													<option value="HIV-1 Not Detected"><?php echo _("HIV-1 Not Detected"); ?></option>
												</select>
											</td>
											<td style="width: 25%;"><label for="">Décision prise </label></td>
											<td style="width: 25%;">
												<select class="form-control" id="isSampleRejected" name="isSampleRejected" title="Please select décision prise" <?php echo $labFieldDisabled; ?> onchange="checkTestStatus();" style="width:100%;">
													<option value=""> -- Sélectionner -- </option>
													<option value="no">Echantillon accepté</option>
													<option value="yes">Echantillon rejeté</option>
												</select>
											</td>
										</tr>
										<tr class="rejectionReason" style="display:none;">
											<td class="rejectionReason" style="display:none;"><label for="rejectionReason">Motifs de rejet <span class="mandatory">*</span></label></td>
											<td class="rejectionReason" style="display:none;">
												<select class="form-control" id="rejectionReason" name="rejectionReason" title="Please select motifs de rejet" <?php echo $labFieldDisabled; ?> onchange="checkRejectionReason();" style="width:100%;">
													<option value=""> -- Sélectionner -- </option>
													<?php foreach ($rejectionResult as $rjctReason) { ?>
														<option value="<?php echo $rjctReason['rejection_reason_id']; ?>"><?php echo ucwords($rjctReason['rejection_reason_name']); ?></option>
													<?php }
													if ($sarr['sc_user_type'] != 'vluser') {  ?>
														<option value="other">Autre</option>
													<?php } ?>
												</select>
											</td>
											<td class="newRejectionReason" style="text-align:center;display:none;"><label for="newRejectionReason" class="newRejectionReason" style="display:none;">Autre, à préciser <span class="mandatory">*</span></label></td>
											<td class="newRejectionReason" style="display:none;"><input type="text" class="form-control newRejectionReason" id="newRejectionReason" name="newRejectionReason" placeholder="Motifs de rejet" title="Please enter motifs de rejet" <?php echo $labFieldDisabled; ?> style="width:100%;display:none;" /></td>
											<td class="col-md-4 rejectionReason" style="display:none;"><?php echo _("Rejection Date"); ?></td>
											<td class="col-md-4 rejectionReason" style="display:none;"><input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select Rejection Date" /></td>
										</tr>
										<tr>
											<td colspan="4" style="height:30px;border:none;"></td>
										</tr>
										<tr class="resultSection">
											<td class="vlResult" style="width: 25%;"><label for="vlResult">Résultat </label></td>
											<td>
												<input type="text" class="vlResult form-control forceNumeric other-failed-results" id="vlResult" name="vlResult" placeholder="Résultat (copies/ml)" title="Please enter résultat" <?php echo $labFieldDisabled; ?> onchange="calculateLogValue(this)" style="width:100%;" />
												<input type="checkbox" class="specialResults other-failed-results" id="vlLt20" name="vlLt20" value="yes" title="Please check VL value">
												< 20<br>
													<input type="checkbox" class="specialResults other-failed-results" id="vlLt40" name="vlLt40" value="yes" title="Please check VL value">
													< 40<br>
														<input type="checkbox" class="specialResults other-failed-results" id="vlLt400" name="vlLt400" value="yes" title="Please check VL value">
														< 400<br>
															<input type="checkbox" class="specialResults other-failed-results" id="vlTND" name="vlTND" value="yes" title="Please check VL value"> Target Not Detected / Non Détecté<br>
															<input type="checkbox" class="labSection specialResults" id="failed" name="failed" value="yes" title="Please check failed"> Failed<br>
											</td>
											<td style="text-align:center;"><label for="vlLog">Log </label></td>
											<td>
												<input type="text" class="vlLog form-control forceNumeric other-failed-results" id="vlLog" name="vlLog" placeholder="Log" title="Please enter log" <?php echo $labFieldDisabled; ?> onchange="calculateLogValue(this)" style="width:100%;" />
											</td>
										</tr>
										<?php if (count($reasonForFailure) > 0) { ?>
											<tr class="reasonForFailure" style="display: none;">
												<td class="reasonForFailure" style="display: none;"><?php echo _("Reason for Failure"); ?></td>
												<td class="reasonForFailure" style="display: none;">
													<select name="reasonForFailure" id="reasonForFailure" class="form-control" title="Please choose reason for failure" style="width: 100%;">
														<?= $general->generateSelectOptions($reasonForFailure, null, '-- Select --'); ?>
													</select>
												</td>
											</tr>
										<?php } ?>
										<tr>
											<td style="width:14%;"><label for="reviewedOn"> Revu le </label></td>
											<td style="width:14%;">
												<input type="text" name="reviewedOn" id="reviewedOn" class="dateTime form-control" placeholder="Revu le" title="Please enter the Revu le" />
											</td>
											<td style="width:14%;"><label for="reviewedBy"> Revu par </label></td>
											<td style="width:14%;">
												<select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="Please choose revu par" style="width: 100%;">
													<?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td>Approuvé le</td>
											<td>
												<input type="text" name="approvedOn" id="approvedOn" class="dateTime form-control" placeholder="Approuvé le" title="Please enter the Approuvé le" />
											</td>
											<td>Approuvé par</td>
											<td>
												<select name="approvedBy" id="approvedBy" class="select2 form-control" title="Please choose Approuvé par" style="width: 100%;">
													<?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
												</select>
											</td>
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
						<?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
							<input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
							<input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
						<?php } ?>
						<a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
						<input type="hidden" name="formId" id="formId" value="3" />
						<input type="hidden" name="vlSampleId" id="vlSampleId" value="" />
						<input type="hidden" name="provinceId" id="provinceId" />
						<input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>" />
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

	function getfacilityDetails(obj) {
		$.blockUI();
		var cName = $("#clinicName").val();
		var pName = $("#province").val();
		if (pName != '' && provinceName && facilityName) {
			facilityName = false;
		}
		if ($.trim(pName) != '') {
			//if (provinceName) {
			$.post("/includes/siteInformationDropdownOptions.php", {
					pName: pName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#clinicName").html(details[0]);
						$("#district").html(details[1]);
						$("#clinicianName").val(details[2]);
					}
				});
			//}
			sampleCodeGeneration();
		} else if (pName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#clinicName").html("<?php echo $facility; ?>");
			$("#clinicName").select2("val", "");
			$("#district").html("<option value=''> -- Sélectionner -- </option>");
		}
		$.unblockUI();
	}

	function sampleCodeGeneration() {
		var pName = $("#province").val();
		$("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
		var sDate = $("#sampleCollectionDate").val();
		if (pName != '' && sDate != '') {
			$.post("/vl/requests/sampleCodeGeneration.php", {
					sDate: sDate,
					pName: pName
				},
				function(data) {
					var sCodeKey = JSON.parse(data);
					$("#sampleCode").val(sCodeKey.sampleCode);
					$("#sampleCodeInText").html(sCodeKey.sampleCode);
					$("#sampleCodeFormat").val(sCodeKey.sampleCodeFormat);
					$("#sampleCodeKey").val(sCodeKey.maxId);
					checkSampleNameValidation('form_vl', '<?php echo $sampleCode; ?>', 'sampleCode', null, 'This sample number already exists.Try another number', null)
				});
		}
	}

	function getfacilityDistrictwise(obj) {
		$.blockUI();
		var dName = $("#district").val();
		var cName = $("#clinicName").val();
		if (dName != '') {
			$.post("/includes/siteInformationDropdownOptions.php", {
					dName: dName,
					cliName: cName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#clinicName").html(details[0]);
					}
				});
		} else {
			$("#clinicName").html("<option value=''> -- Sélectionner -- </option>");
		}
		$.unblockUI();
	}

	function getfacilityProvinceDetails(obj) {
		$.blockUI();
		//check facility name
		var cName = $("#clinicName").val();
		var pName = $("#province").val();
		if (cName != '' && provinceName && facilityName) {
			provinceName = false;
		}
		if (cName != '' && facilityName) {
			$.post("/includes/siteInformationDropdownOptions.php", {
					cName: cName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#province").html(details[0]);
						$("#district").html(details[1]);
						$("#clinicianName").val(details[2]);
					}
				});
		} else if (pName == '' && cName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#clinicName").html("<?php echo $facility; ?>");
		}
		$.unblockUI();
	}
	$("input:radio[name=isPatientNew]").click(function() {
		if ($(this).val() == 'yes') {
			$(".du").css("visibility", "visible");
		} else if ($(this).val() == 'no') {
			$(".du").css("visibility", "hidden");
		}
	});
	$("input:radio[name=gender]").click(function() {
		if ($(this).val() == 'female') {
			$("#femaleElements").show();
		} else if ($(this).val() == 'male') {
			$("#femaleElements").hide();
		}
	});
	$("input:radio[name=hasChangedRegimen]").click(function() {
		if ($(this).val() == 'yes') {
			$(".arvChangedElement").show();
		} else if ($(this).val() == 'no') {
			$(".arvChangedElement").hide();
		}
	});

	function checkVLTestReason() {
		var vlTestReason = $("#vlTestReason").val();
		if (vlTestReason == "other") {
			$(".newVlTestReason").show();
			$("#newVlTestReason").addClass("isRequired");
		} else {
			$(".newVlTestReason").hide();
			$("#newVlTestReason").removeClass("isRequired");
		}
	}

	function checkSpecimenType() {
		var specimenType = $("#specimenType").val();
		if (specimenType == 2) {
			$(".plasmaElement").show();
		} else {
			$(".plasmaElement").hide();
		}
	}

	function checkTestStatus() {
		var status = $("#isSampleRejected").val();
		if (status == 'yes') {
			$(".rejectionReason").show();
			$(".resultSection").hide();
			$("#rejectionReason").addClass('isRequired');
			$("#vlResult").val('').css('pointer-events', 'none');
			$("#vlLog").val('').css('pointer-events', 'none');
			$('.specialResults').prop('checked', false).removeAttr('checked');

		} else {
			$(".resultSection").show();
			$("#rejectionReason").val('');
			$(".rejectionReason").hide();
			$("#rejectionReason").removeClass('isRequired');
			$("#vlResult").css('pointer-events', 'auto');
			$("#vlLog").css('pointer-events', 'auto');

		}
	}
	$('#testingPlatform').change(function() {
		var text = this.value;
		var str1 = text.split("##");
		var str = str1[0];
		if (str1[0] == 'GeneXpert' || str.toLowerCase() == 'genexpert') {
			$('.hivDetection').show();
		} else {
			$('.hivDetection').hide();
		}
	});
	$('#failed').change(function() {
		if ($('#failed').prop('checked')) {
			$('.reasonForFailure').show();
			$('#reasonForFailure').addClass('isRequired');
			$('#other-failed-results').removeClass('isRequired');
			$('#other-failed-results').prop('disabled', true);
		} else {
			$('.reasonForFailure').hide();
			$('#reasonForFailure').removeClass('isRequired');
			$('#other-failed-results').prop('disabled', false);
		}
	});

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

	function checkLastVLTestDate() {
		var artInitiationDate = $("#dateOfArtInitiation").val();
		var dateOfLastVLTest = $("#lastViralLoadTestDate").val();
		if ($.trim(artInitiationDate) != '' && $.trim(dateOfLastVLTest) != '') {
			if (moment(artInitiationDate).isAfter(dateOfLastVLTest)) {
				alert("Dernier test de charge virale Les données ne peuvent pas être antérieures à la date d'initiation de l'ARV!");
				$("#lastViralLoadTestDate").val("");
			}
		}
	}

	function calculateLogValue(obj) {
		if (obj.id == "vlResult") {
			absValue = $("#vlResult").val();
			absValue = Number.parseFloat(absValue).toFixed();
			if (absValue != '' && absValue != 0 && !isNaN(absValue)) {
				$("#vlResult").val(absValue);
				$("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
			} else {
				$("#vlLog").val("");
			}
		}
		if (obj.id == "vlLog") {
			logValue = $("#vlLog").val();
			if (logValue != '' && logValue != 0) {
				var absVal = Math.round(Math.pow(10, logValue) * 100) / 100;
				if (absVal != 'Infinity' && !isNaN(absVal)) {
					$("#vlResult").val(Math.round(Math.pow(10, logValue) * 100) / 100);
				} else {
					$("#vlResult").val('');
				}
			}
		}
	}

	function validateNow() {
		$("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
		flag = deforayValidator.init({
			formId: 'addVlRequestForm'
		});
		if (flag) {
			$('.btn-disabled').attr('disabled', 'yes');
			$(".btn-disabled").prop("onclick", null).off("click");
			$.blockUI();
			<?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
				insertSampleCode('addVlRequestForm', 'vlSampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 3, 'sampleCollectionDate');
			<?php } else { ?>
				document.getElementById('addVlRequestForm').submit();
			<?php } ?>
		}
	}

	function setPatientDetails(pDetails) {
		patientArray = pDetails.split("##");
		console.log(patientArray);
		if ($.trim(patientArray[3]) != '') {
			$("#dob").val(patientArray[3]);
			getAge();
		} else if ($.trim(patientArray[4]) != '' && $.trim(patientArray[4]) != 0) {
			$("#ageInYears").val(patientArray[4]);
		} else if ($.trim(patientArray[5]) != '') {
			$("#ageInMonths").val(patientArray[5]);
		}
		if ($.trim(patientArray[2]) != '') {
			if (patientArray[2] == 'male') {
				$("#genderMale").prop('checked', true);
			} else if (patientArray[2] == 'female') {
				$("#genderFemale").prop('checked', true);
			}
		}
		if ($.trim(patientArray[15]) != '') {
			$("#patientArtNo").val($.trim(patientArray[15]));
		}
		if ($.trim(patientArray[11]) != '') {
			$("#artRegimen").val($.trim(patientArray[11]));
		}
		if ($.trim(patientArray[16]) != '') {
			if (patientArray[16] == 'yes') {
				$("#isPatientNewYes").prop('checked', true);
			} else if (patientArray[16] == 'no') {
				$("#isPatientNewNo").prop('checked', true);
			}
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
				$(".specialResults").not(this).attr('disabled', true);
				//$('.specialResults').not(this).prop('checked', false).removeAttr('checked');
			} else {
				$('#vlResult,#vlLog').attr('readonly', false);
				$(".specialResults").not(this).attr('disabled', false);
			}
		});


		$('#clinicName').select2({
			placeholder: "Select Clinic/Health Center"
		});
		$('#district').select2({
			placeholder: "District"
		});
		$('#province').select2({
			placeholder: "Province"
		});
		$('#labId').select2({
			placeholder: "Select Nom du laboratoire"
		});
		$('#reviewedBy').select2({
			placeholder: "Select Revu par"
		});
		$('#approvedBy').select2({
			placeholder: "Select Approuvé par"
		});
	});
</script>