<?php


use App\Services\DatabaseService;
use App\Services\VlService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);




$arr = $general->getGlobalConfig();


if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'alphanumeric') {
	$sampleClass = '';
	$maxLength = '';
	if ($arr['max_length'] != '' && $arr['sample_code'] == 'alphanumeric') {
		$maxLength = $arr['max_length'];
		$maxLength = "maxlength=" . $maxLength;
	}
} else {
	$sampleClass = '';
	$maxLength = '';
	if ($arr['max_length'] != '') {
		$maxLength = $arr['max_length'];
		$maxLength = "maxlength=" . $maxLength;
	}
}

$rKey = '';
$sampleCodeKey = 'sample_code_key';
$sampleCode = 'sample_code';
$prefix = $arr['sample_code_prefix'];

if ($general->isSTSInstance()) {
	$rKey = 'R';
	$sampleCodeKey = 'remote_sample_code_key';
	$sampleCode = 'remote_sample_code';
	if (!empty($vlQueryInfo['remote_sample']) && $vlQueryInfo['remote_sample'] == 'yes') {
		$sampleCode = 'remote_sample_code';
	} else {
		$sampleCode = 'sample_code';
	}
}
//sample rejection reason
$rejectionQuery = "SELECT * FROM r_vl_sample_rejection_reasons";
$rejectionResult = $db->rawQuery($rejectionQuery);

$bQuery = "SELECT * FROM batch_details
			WHERE test_type like 'vl' or test_type is NULL
			ORDER BY last_modified_datetime DESC";
$bResult = $db->rawQuery($bQuery);
// get instruments
$importQuery = "SELECT * FROM instruments WHERE status = 'active'";
$importResult = $db->query($importQuery);

$aQuery = "SELECT * from r_vl_art_regimen WHERE art_status like 'active' ORDER by parent_art ASC, art_code ASC";
$aResult = $db->query($aQuery);

$sQuery = "SELECT * FROM r_vl_sample_type where status='active'";
$sResult = $db->query($sQuery);


$province = $general->getUserMappedProvinces($_SESSION['facilityMap']);
//facility details
$facilityQuery = "SELECT * FROM facility_details where facility_id= ? AND status='active'";
$facilityResult = $db->rawQuery($facilityQuery, array($vlQueryInfo['facility_id']));
if (!isset($facilityResult[0]['facility_state']) || $facilityResult[0]['facility_state'] == '') {
	$facilityResult[0]['facility_state'] = "";
}
$stateName = $facilityResult[0]['facility_state'];
$stateId = $facilityResult[0]['facility_state_id'];
$stateQuery = "SELECT * FROM geographical_divisions WHERE geo_id= ? AND geo_status='active'";
$stateResult = $db->rawQuery($stateQuery, [$stateId]);
if (!isset($stateResult[0]['geo_code']) || $stateResult[0]['geo_code'] == '') {
	$stateResult[0]['geo_code'] = "";
}
//district details
$districtQuery = "SELECT DISTINCT facility_district FROM facility_details WHERE facility_state='" . $stateName . "'";
$districtResult = $db->query($districtQuery);

$province = $general->getUserMappedProvinces($_SESSION['facilityMap']);

$facility = $general->generateSelectOptions($healthFacilities, $vlQueryInfo['facility_id'], '-- Select --');


if (isset($vlQueryInfo['failed_test_date']) && trim((string) $vlQueryInfo['failed_test_date']) != '' && trim((string) $vlQueryInfo['failed_test_date']) != '0000-00-00 00:00:00') {
	$failedDate = explode(" ", (string) $vlQueryInfo['failed_test_date']);
	$vlQueryInfo['failed_test_date'] = DateUtility::humanReadableDateFormat($failedDate[0]) . " " . $failedDate[1];
} else {
	$vlQueryInfo['failed_test_date'] = '';
}
if (isset($vlQueryInfo['art_cd_date']) && trim((string) $vlQueryInfo['art_cd_date']) != '' && $vlQueryInfo['art_cd_date'] != '0000-00-00') {
	$vlQueryInfo['art_cd_date'] = DateUtility::humanReadableDateFormat($vlQueryInfo['art_cd_date']);
} else {
	$vlQueryInfo['art_cd_date'] = '';
}
if (isset($vlQueryInfo['qc_date']) && trim((string) $vlQueryInfo['qc_date']) != '' && $vlQueryInfo['qc_date'] != '0000-00-00') {
	$vlQueryInfo['qc_date'] = DateUtility::humanReadableDateFormat($vlQueryInfo['qc_date']);
} else {
	$vlQueryInfo['qc_date'] = '';
}
if (isset($vlQueryInfo['report_date']) && trim((string) $vlQueryInfo['report_date']) != '' && $vlQueryInfo['report_date'] != '0000-00-00') {
	$vlQueryInfo['report_date'] = DateUtility::humanReadableDateFormat($vlQueryInfo['report_date']);
} else {
	$vlQueryInfo['report_date'] = '';
}
if (isset($vlQueryInfo['clinic_date']) && trim((string) $vlQueryInfo['clinic_date']) != '' && $vlQueryInfo['clinic_date'] != '0000-00-00') {
	$vlQueryInfo['clinic_date'] = DateUtility::humanReadableDateFormat($vlQueryInfo['clinic_date']);
} else {
	$vlQueryInfo['clinic_date'] = '';
}

?>
<style>
	.ui_tpicker_second_label {
		display: none !important;
	}

	.ui_tpicker_second_slider {
		display: none !important;
	}

	.ui_tpicker_millisec_label {
		display: none !important;
	}

	.ui_tpicker_millisec_slider {
		display: none !important;
	}

	.ui_tpicker_microsec_label {
		display: none !important;
	}

	.ui_tpicker_microsec_slider {
		display: none !important;
	}

	.ui_tpicker_timezone_label {
		display: none !important;
	}

	.ui_tpicker_timezone {
		display: none !important;
	}

	.ui_tpicker_time_input {
		width: 100%;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-pen-to-square"></em> VIRAL LOAD LABORATORY REQUEST FORM </h1>
		<ol class="breadcrumb">
			<li><a href="/dashboard/index.php"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
			<li class="active">Add Vl Request</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="box box-default">
			<div class="box-header with-border">
				<div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?= _translate("indicates required fields"); ?> &nbsp;</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-inline" method='post' name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="editVlRequestHelper.php">
					<div class="box-body">
						<div class="box box-default">
							<div class="box-body">

								<div class="row">
									<div class="col-xs-3 col-md-3">
										<div class="form-group">
											<?php if ($general->isSTSInstance()) { ?>
												<label class="labels" for="sampleCode">Laboratory ID </label><br>
												<span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;">
													<?php echo $vlQueryInfo[$sampleCode]; ?>
												</span>
												<input type="hidden" class="" id="sampleCode" name="sampleCode" value="<?php echo $vlQueryInfo[$sampleCode]; ?>" />
											<?php } else { ?>
												<label class="labels" for="sampleCode">Laboratory ID <span class="mandatory">*</span></label>
												<input type="text" class="form-control isRequired " id="sampleCode" name="sampleCode" <?php echo $maxLength; ?> placeholder="Enter Sample ID" title="<?= _translate("Please make sure you have selected Sample Collection Date and Requesting Facility"); ?>" value="<?php echo $vlQueryInfo[$sampleCode]; ?>" style="width:100%;" readonly="readonly" />
												<input type="hidden" name="sampleCodeCol" value="<?= ($vlQueryInfo['sample_code']); ?>" />
											<?php } ?>

										</div>
									</div>

									<?php if ($general->isSTSInstance()) { ?>

										<div class="col-xs-3 col-md-3">
											<div class="">
												<label class="labels" for="labId">VL Testing Hub <span class="mandatory">*</span></label>
												<select name="labId" id="labId" class="form-control isRequired" title="Please choose a VL testing hub">
													<?= $general->generateSelectOptions($testingLabs, $vlQueryInfo['lab_id'], '-- Select --'); ?>
												</select>
											</div>
										</div>

									<?php } ?>
								</div>
								<br />
								<table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
									<tr>
										<td colspan="6" style="font-size: 18px; font-weight: bold;">Section 1: Clinic
											Information</td>
									</tr>
									<tr>
										<td style="width:13%" class="labels">
											<label for="province">Province <span class="mandatory">*</span></label>
										</td>
										<td style="width:20%">
											<select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getfacilityDetails(this);">
												<option value=""> -- Select -- </option>
												<?php foreach ($pdResult as $provinceName) { ?>
													<option value="<?php echo $provinceName['geo_name'] . "##" . $provinceName['geo_code']; ?>" <?php echo (strtolower((string) $facilityResult[0]['facility_state']) . "##" . $stateResult[0]['geo_code'] == strtolower((string) $provinceName['geo_name']) . "##" . $provinceName['geo_code']) ? "selected='selected'" : "" ?>>
														<?php echo ($provinceName['geo_name']); ?></option>;
												<?php } ?>
											</select>
										</td>
										<td style="width:13%" class="labels">
											<label for="district">District <span class="mandatory">*</span></label>
										</td>
										<td style="width:20%">
											<select class="form-control isRequired" name="district" id="district" title="Please choose district" onchange="getfacilityDistrictwise(this);" style="width:100%;">
												<option value=""> -- Select -- </option>
												<?php
												foreach ($districtResult as $districtName) {
												?>
													<option value="<?php echo $districtName['facility_district']; ?>" <?php echo ($facilityResult[0]['facility_district'] == $districtName['facility_district']) ? "selected='selected'" : "" ?>><?php echo ($districtName['facility_district']); ?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td style="width:14%" class="labels">
											<label for="facilityId">Clinic/Ward <span class="mandatory">*</span></label>
										</td>
										<td style="width:20%">
											<select class="form-control isRequired" id="facilityId" name="facilityId" title="Please select clinic/ward" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
												<?= $facility; ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="labels">
											<label for="reqClinician">Requesting Medical Officer <span class="mandatory">*</span></label>
										</td>
										<td>
											<input type="text" class="form-control isRequired" name="reqClinician" id="reqClinician" placeholder="Officer Name" title="Enter Medical Officer Name" style="width:100%;" value="<?php echo $vlQueryInfo['request_clinician_name']; ?>">
										</td>
										<td class="labels">
											<label for="reqClinicianPhoneNumber">Clinic/Ward Telephone </label>
										</td>
										<td>
											<input type="text" class="form-control phone-number" name="reqClinicianPhoneNumber" id="reqClinicianPhoneNumber" placeholder="Telephone" title="Enter Telephone" style="width:100%;" value="<?php echo $vlQueryInfo['request_clinician_phone_number']; ?>">
										</td>
										<td class="labels">
											<label for="requestDate">Date Requested </label>
										</td>
										<td>
											<input type="text" class="form-control date" name="requestDate" id="requestDate" placeholder="Date" title="Enter Date" style="width:100%;" value="<?php echo $vlQueryInfo['test_requested_on']; ?>">
										</td>
									</tr>
									<tr>
										<td colspan="6" style="font-size: 18px; font-weight: bold;">Section 2: Patient
											Information</td>
									</tr>
									<tr class="encryptPIIContainer">
										<th scope="row" style="width:15% !important"><label for="childId"><?= _translate('Encrypt PII'); ?> </label></th>
										<td>
											<select name="encryptPII" id="encryptPII" class="form-control" title="<?= _translate('Encrypt PII'); ?>">
												<option value=""><?= _translate('--Select--'); ?></option>
												<option value="no" <?php echo ($vlQueryInfo['is_encrypted'] == "no") ? "selected='selected'" : ""; ?>><?= _translate('No'); ?></option>
												<option value="yes" <?php echo ($vlQueryInfo['is_encrypted'] == "yes") ? "selected='selected'" : ""; ?>><?= _translate('Yes'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td class="labels">
											<label for="artNo">Patient ID <span class="mandatory">*</span></label>
										</td>
										<td>
											<input type="text" class="form-control isRequired patientId" placeholder="Enter Patient ID" name="artNo" id="artNo" title="Please enter Clinic ID" value="<?= ($vlQueryInfo['patient_art_no']); ?>" style="width:100%;" />
										</td>
										<td class="labels">
											<label for="gender"><?= _translate("Sex"); ?> &nbsp;&nbsp;</label>
										</td>
										<td colspan="1">
											<select class="form-control" name="gender" id="gender" title="Please choose patient gender" style="width:100%;" onchange="">
												<option value="">-- Select --</option>
												<option value="male" <?php echo ($vlQueryInfo['patient_gender'] == 'male') ? "selected='selected' " : "" ?>>Male</option>
												<option value="female" <?php echo ($vlQueryInfo['patient_gender'] == 'female') ? "selected='selected' " : "" ?>>Female</option>
												<option value="unreported" <?php echo ($vlQueryInfo['patient_gender'] == 'unreported') ? "selected='selected' " : "" ?>>Unreported</option>
											</select>

										</td>
									</tr>
									<?php if ($vlQueryInfo['patient_gender'] == 'female') { ?>
										<tr class="femaleFactor">
											<td class="labels">
												<label for="patientPregnant">Patient Pregnant ?</label>
											</td>
											<td>
												<select class="form-control" name="patientPregnant" id="patientPregnant" title="Please choose if patient is pregnant" style="width:100%;" onchange="">
													<option value="">-- Select --</option>
													<option value="yes" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'yes') ? "selected='selected' " : "" ?>>Yes</option>
													<option value="no" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'no') ? "selected='selected' " : "" ?>>No</option>
													<option value="no" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'not_reported') ? "selected='selected' " : "" ?>>Not Reported</option>
												</select>
											</td>
											<td class="labels">
												<label for="breastfeeding">Patient Breastfeeding ?</label>
											</td>
											<td>
												<select class="form-control" name="breastfeeding" id="breastfeeding" title="Please choose if patient is breastfeeding" onchange="" style="width:100%;">
													<option value=""> -- Select -- </option>
													<option value="yes" <?php echo ($vlQueryInfo['is_patient_breastfeeding'] == 'yes') ? "selected='selected' " : "" ?>>Yes</option>
													<option value="no" <?php echo ($vlQueryInfo['is_patient_breastfeeding'] == 'no') ? "selected='selected' " : "" ?>>No</option>
													<option value="no" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'not_reported') ? "selected='selected' " : "" ?>>Not Reported</option>
												</select>
											</td>
											<td></td>
											<td></td>
										</tr>
									<?php } ?>
									<tr>
										<td class="labels"><label for="dob">Date Of Birth</label></td>
										<td>
											<input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" value="<?= ($vlQueryInfo['patient_dob']); ?>" onchange="getAge();" style="width:100%;" />
										</td>
										<td class="labels"><label for="ageInYears">If DOB unknown, Age in Years</label>
										</td>
										<td>
											<input type="text" name="ageInYears" id="ageInYears" class="form-control forceNumeric" maxlength="2" placeholder="Age in Year" title="Enter age in years" value="<?= ($vlQueryInfo['patient_age_in_years']); ?>" />
										</td>
										<td class="labels"><label for="ageInMonths">If Age < 1, Age in Months </label>
										</td>
										<td>
											<input type="text" name="ageInMonths" id="ageInMonths" class="form-control forceNumeric" maxlength="2" placeholder="Age in Month" title="Enter age in months" value="<?= ($vlQueryInfo['patient_age_in_months']); ?>" />
										</td>

									</tr>
									<tr>

									</tr>
									<tr>
										<td colspan="6" style="font-size: 18px; font-weight: bold;">Section 3: ART
											Information</td>
									</tr>
									<tr>
										<td class="labels">
											<label for="lineOfTreatment">Line of Treatment </label>
										</td>
										<td>
											<label class="radio-inline">
												<input type="radio" class="" id="firstLine" name="lineOfTreatment" value="1" <?php echo ($vlQueryInfo['line_of_treatment'] == 1) ? 'checked="checked"' : ''; ?> title="Please check ART Line"> First
												Line
											</label><br>
											<label class="radio-inline">
												<input type="radio" class="" id="secondLine" name="lineOfTreatment" value="2" <?php echo ($vlQueryInfo['line_of_treatment'] == 2) ? 'checked="checked"' : ''; ?> title="Please check ART Line"> Second
												Line
											</label>
										</td>
										<td class="labels">
											<label for="cdCells">CD4(cells/ul) </label>
										</td>
										<td>
											<input type="text" class="form-control" name="cdCells" id="cdCells" placeholder="CD4 Cells" title="CD4 Cells" style="width:100%;" value="<?php echo $vlQueryInfo['art_cd_cells']; ?>">
										</td>
										<td class="labels">
											<label for="cdDate">CD4 Date </label>
										</td>
										<td>
											<input type="text" class="form-control date" name="cdDate" id="cdDate" placeholder="CD4 Date" title="Enter CD4 Date" style="width:100%;" value="<?php echo $vlQueryInfo['art_cd_date']; ?>">
										</td>
									</tr>
									<tr>
										<td class="labels">
											<label for="artRegimen">Current Regimen </label>
										</td>
										<td>
											<select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" onchange="checkValue();" style="width:100%;">
												<option value=""> -- Select -- </option>
												<?php
												foreach ($aResult as $parentRow) {
												?>
													<option value="<?php echo $parentRow['art_code']; ?>" <?php echo ($vlQueryInfo['current_regimen'] == $parentRow['art_code']) ? "selected='selected'" : "" ?>><?php echo $parentRow['art_code']; ?>
													</option>
												<?php
												}
												?>
												<option value="other">Other</option>
											</select>
											<input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="New Art Regimen" title="Please enter new ART regimen" style="display:none;width:100%;margin-top:1vh;">
										</td>
										<td class="labels">
											<label for="regimenInitiatedOn">Current Regimen Start Date</label>
										</td>
										<td>
											<input type="text" class="form-control date" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Start Date" title="Enter Start Date" style="width:100%;" value="<?php echo $vlQueryInfo['date_of_initiation_of_current_regimen']; ?>">
										</td>
										<td colspan="2" class="clinicalStage labels"><label for="breastfeeding">WHO
												Clinical Stage</label>&nbsp;&nbsp;
											<label class="radio-inline">
												<input type="radio" id="clinicalOne" name="clinicalStage" value="one" title="WHO Clinical Statge" <?php echo ($vlQueryInfo['who_clinical_stage'] == 'one') ? "checked='checked'" : "" ?>>I
											</label>
											<label class="radio-inline">
												<input type="radio" id="clinicalTwo" name="clinicalStage" value="two" title="WHO Clinical Statge" <?php echo ($vlQueryInfo['who_clinical_stage'] == 'two') ? "checked='checked'" : "" ?>>II
											</label>
											<label class="radio-inline">
												<input type="radio" id="clinicalThree" name="clinicalStage" value="three" title="WHO Clinical Statge" <?php echo ($vlQueryInfo['who_clinical_stage'] == 'three') ? "checked='checked'" : "" ?>>III
											</label>
											<label class="radio-inline">
												<input type="radio" id="clinicalFour" name="clinicalStage" value="four" title="WHO Clinical Statge" <?php echo ($vlQueryInfo['who_clinical_stage'] == 'four') ? "checked='checked'" : "" ?>>IV
											</label>
										</td>
									</tr>
									<tr>
										<td colspan="6" style="font-size: 18px; font-weight: bold;">Section 4: Reason
											For Testing</td>
									</tr>
									<?php
									$vlTestReasonQueryRow = "SELECT * from r_vl_test_reasons where test_reason_id='" . trim((string) $vlQueryInfo['reason_for_vl_testing']) . "' OR test_reason_name = '" . trim((string) $vlQueryInfo['reason_for_vl_testing']) . "'";
									$vlTestReasonResultRow = $db->query($vlTestReasonQueryRow);
									?>
									<tr>
										<td colspan="3" class="routine">
											<label class="labels" for="routine">Routine</label><br />
											<label class="radio-inline">
												&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="routineOne" name="reasonForVLTesting" value="First VL, routine monitoring (On ART for at least 6 months)" title="Please Check Routine" <?php echo ($vlQueryInfo['reason_testing_png'] == 'First VL, routine monitoring (On ART for at least 6 months)' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'First VL, routine monitoring (On ART for at least 6 months)') ? "checked='checked'" : "" ?>>First VL, routine monitoring (On ART for at least 6 months)
											</label>
											<label class="radio-inline">
												<input type="radio" id="routineTwo" name="reasonForVLTesting" value="Annual routine follow-up VL (Previous VL < 1000 cp/mL)" title="Please Check Routine" <?php echo ($vlQueryInfo['reason_testing_png'] == 'Annual routine follow-up VL (Previous VL < 1000 cp/mL)' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'Annual routine follow-up VL (Previous VL < 1000 cp/mL)') ? "checked='checked'" : "" ?>>Annual routine follow-up VL (Previous VL < 1000 cp/mL) </label>
										</td>
										<td colspan="3" class="suspect">
											<label class="labels" for="suspect">Suspected Treatment
												Failure</label><br />
											<label class="radio-inline">
												<input type="radio" id="suspectOne" name="reasonForVLTesting" value="Suspected TF" title="Please Suspected TF" <?php echo ($vlQueryInfo['reason_testing_png'] == 'Suspected TF' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'Suspected TF') ? "checked='checked'" : "" ?>>Suspected TF
											</label>
											<label class="radio-inline">
												<input type="radio" id="suspectTwo" name="reasonForVLTesting" value="Follow-up VL after EAC (Previous VL >= 1000 cp/mL)" title="Please Suspected TF" <?php echo ($vlQueryInfo['reason_testing_png'] == 'Follow-up VL after EAC (Previous VL >= 1000 cp/mL)' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'Follow-up VL after EAC (Previous VL >= 1000 cp/mL)') ? "checked='checked'" : "" ?>>Follow-up VL after EAC (Previous VL >= 1000 cp/mL)
											</label>
										</td>
									</tr>
									<tr>
										<td colspan="3">
											<label class="labels" for="defaulter">Defaulter/ LTFU/ Poor
												Adherer</label><br />
											<label class="radio-inline">
												<input type="radio" id="defaulter" name="reasonForVLTesting" value="VL (after 3 months EAC)" title="Check Defaulter/ LTFU/ Poor Adherer" <?php echo ($vlQueryInfo['reason_testing_png'] == 'VL (after 3 months EAC)' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'VL (after 3 months EAC)') ? "checked='checked'" : "" ?>>VL (after 3 months EAC)
											</label>&nbsp;&nbsp;
										</td>
										<td colspan="3">
											<label for="other">Other</label><br />
											<label class="radio-inline">
												<input type="radio" id="other" name="reasonForVLTesting" value="Re-collection requested by lab" title="Please check Other" <?php echo ($vlQueryInfo['reason_testing_png'] == 'Re-collection requested by lab' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'Re-collection requested by lab') ? "checked='checked'" : "" ?>>Re-collection requested by lab
											</label>
											<label class="labels" for="reason">&nbsp;&nbsp;&nbsp;&nbsp;Reason</label>
											<label class="radio-inline">
												<input type="text" class="form-control" id="reason" name="reason" placeholder="Enter Reason" title="Enter Reason" style="width:100%;" <?php echo ($vlQueryInfo['reason_testing_png'] == 'Re-collection requested by lab' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'Re-collection requested by lab') ? "" : "readonly='readonly'" ?> value="<?php echo $vlQueryInfo['reason_for_vl_testing_other']; ?>" />
											</label>
										</td>
									</tr>
									<tr>
										<td colspan="2" style="font-size: 18px; font-weight: bold;">Section 5: Specimen
											information </td>
										<td colspan="4" style="font-size: 18px; font-weight: bold;"> Type of sample to
											transport</td>
									</tr>
									<tr>
										<td class="labels">
											<label for="sampleCollectionDate">Collection date <span class="mandatory">*</span></label>
										</td>
										<td>
											<input type="text" class="form-control isRequired" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please enter sample collection date" style="width:100%;" value="<?php echo $vlQueryInfo['sample_collection_date']; ?>" onchange="checkCollectionDate(this.value);">
											<span class="expiredCollectionDate" style="color:red; display:none;"></span>
										</td>
										<td colspan="4" class="typeOfSample">
											<label class="radio-inline">
												<input type="radio" id="dbs" name="typeOfSample" value="DBS" title="Check DBS" <?php echo ($vlQueryInfo['sample_to_transport'] == 'DBS') ? "checked='checked'" : "" ?>>DBS
											</label>
											<label class="radio-inline" style="width:46%;">
												<input type="radio" id="wholeBlood" name="typeOfSample" value="Whole blood" title="Check Whole blood" style="margin-top:10px;" <?php echo ($vlQueryInfo['sample_to_transport'] == 'Whole blood') ? "checked='checked'" : "" ?>>Whole Blood
												<input type="text" name="wholeBloodOne" id="wholeBloodOne" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo['whole_blood_ml']; ?>" />&nbsp; x
												&nbsp;<input type="text" name="wholeBloodTwo" id="wholeBloodTwo" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo['whole_blood_vial']; ?>" />&nbsp;vial(s)
											</label>
											<label class="radio-inline" style="width:42%;">
												<input type="radio" id="plasma" name="typeOfSample" value="Plasma" title="Check Plasma" style="margin-top:10px;" <?php echo ($vlQueryInfo['sample_to_transport'] == 'Plasma') ? "checked='checked'" : "" ?>>Plasma
												<input type="text" name="plasmaOne" id="plasmaOne" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo['plasma_ml']; ?>" />&nbsp;ml x
												&nbsp;<input type="text" name="plasmaTwo" id="plasmaTwo" class="form-control" style="width: 20%;" value="<?php echo $vlQueryInfo['plasma_vial']; ?>" />&nbsp;vial(s)
											</label>
										</td>
									</tr>
									<tr>
										<td class="labels">
											<label for="collectedBy">Specimen Collected by</label>
										</td>
										<td>
											<input type="text" class="form-control" name="collectedBy" id="collectedBy" placeholder="Collected By" title="Enter Collected By" style="width:100%;" value="<?php echo $vlQueryInfo['sample_collected_by']; ?>">
										</td>
										<td class="labels"><label for="processTime">For onsite plasma processing
												only</label></td>
										<td>
											<input type="text" name="processTime" id="processTime" class="form-control" style="width: 100%;" placeholder="Time" title="Processing Time" value="<?php echo $vlQueryInfo['plasma_process_time']; ?>" />
										</td>
										<td class="labels">
											<label for="processTech">Processing Tech</label>
										</td>
										<td>
											<input type="text" name="processTech" id="processTech" class="form-control" style="width: 100%;" placeholder="Processing Tech" title="Processing Tech" value="<?php echo $vlQueryInfo['plasma_process_tech']; ?>" />
										</td>
									</tr>
									<tr>
										<td colspan="6" style="font-size: 18px; font-weight: bold;">CPHL Use Only </td>
									</tr>
									<tr>
										<td class="labels"><label for="isSampleRejected">Sample Quality</label></td>
										<td>
											<label class="radio-inline">
												<input type="radio" id="sampleQtyAccept" name="isSampleRejected" value="no" title="Check Sample Quality" <?php echo ($vlQueryInfo['is_sample_rejected'] == 'no') ? "checked='checked'" : "" ?>>Accept
											</label>
											<label class="radio-inline">
												<input type="radio" id="sampleQtyReject" name="isSampleRejected" value="yes" title="Check Sample Quality" <?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "checked='checked'" : "" ?>>Reject
											</label>
										</td>
										<td class="rejectionReason" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "" : "none"; ?>">
											<label for="rejectionReason">Reason <span class="mandatory">*</span></label>
										</td>
										<td class="rejectionReason" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "" : "none"; ?>">
											<select name="rejectionReason" id="rejectionReason" class="form-control <?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "isRequired" : ""; ?>" title="Please choose reason" style="width:100%;">
												<option value="">-- Select --</option>
												<?php foreach ($rejectionResult as $reject) { ?>
													<option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($vlQueryInfo['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? "selected='selected'" : "" ?>><?= $reject['rejection_reason_name']; ?></option>
												<?php } ?>
											</select>
										</td>
										<td class="rejectionReason labels" style="display: <?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "" : "none"; ?>">
											Rejection Date<span class="mandatory">*</span></td>
										<td class="rejectionReason" style="display: <?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "" : "none"; ?>">
											<input value="<?php echo DateUtility::humanReadableDateFormat($vlQueryInfo['rejection_on']); ?>" class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select Rejection Date" />
										</td>
									</tr>
									<tr>
										<td class="labId labels"><label for="labId">Laboratory Name</label></td>
										<td>
											<select name="labId" id="labId" class="form-control" title="Please choose lab name" style="width:100%;">
												<?= $general->generateSelectOptions($testingLabs, $vlQueryInfo['lab_id'], '-- Select --'); ?>
											</select>
										</td>
										<td class="specimenType labels"><label for="specimenType">Sample Type
												Received</label></td>
										<td>
											<select name="specimenType" id="specimenType" class="form-control" title="Please choose Specimen type" style="width:100%;">
												<option value=""> -- Select -- </option>
												<?php foreach ($sResult as $name) { ?>
													<option value="<?php echo $name['sample_id']; ?>" <?php echo ($vlQueryInfo['specimen_type'] == $name['sample_id']) ? "selected='selected'" : "" ?>><?= $name['sample_name']; ?></option>
												<?php } ?>
											</select>
										</td>
										<td class="sampleReceivedDate labels"><label for="sampleReceivedDate">Date
												Received</label></td>
										<td>
											<input type="text" class="form-control" name="sampleReceivedDate" id="sampleReceivedDate" placeholder="Received Date" title="Enter Received Date" style="width:100%;" value="<?php echo $vlQueryInfo['sample_received_at_lab_datetime']; ?>">
										</td>
									</tr>
									<tr>
										<td class="techName labels"><label for="techName">Lab Tech. Name</label></td>
										<td>
											<input type="text" class="form-control" name="techName" id="techName" placeholder="Enter Lab Technician Name" title="Please enter lab technician name" style="width:100%;" value="<?php echo $vlQueryInfo['tech_name_png']; ?>">
										</td>
										<td class="labels"><label for="testDate">Test date</label></td>
										<td>
											<input type="text" class="form-control" name="testDate" id="testDate" placeholder="Test Date" title="Enter Testing Date" style="width:100%;" value="<?php echo $vlQueryInfo['sample_tested_datetime']; ?>">
										</td>
										<td class="labels"><label for="testingPlatform">Testing Platform</label></td>
										<td>
											<select name="testingPlatform" id="testingPlatform" onchange="getVlResults('testingPlatform','possibleVlResults', 'cphlvlResult');getVlResults('testingPlatform','finalPossibleVlResults', 'finalViralLoadResult');" class="form-control" title="Please choose VL Testing Platform" style="width:100%;">
												<option value="">-- Select --</option>
												<?php foreach ($importResult as $mName) { ?>
													<option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] . '##' . $mName['instrument_id']; ?>" <?php echo ($vlQueryInfo['vl_test_platform'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] == $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']) ? "selected='selected'" : "" ?>><?php echo $mName['machine_name']; ?>
													</option>
												<?php
												}
												?>
											</select>
										</td>

									</tr>
									<tr>
										<td class="vlResult" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "none" : ""; ?>">
											<label for="vlResult">VL result</label>
										</td>
										<td class="vlResult resultInputContainer" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "none" : ""; ?>">
											<input list="possibleVlResults" disabled="disabled" type="text" class="form-control" name="cphlVlResult" id="cphlvlResult" placeholder="VL Result" title="Enter VL Result" style="width:100%;" value="<?php echo $vlQueryInfo['cphl_vl_result']; ?>">
											<datalist id="possibleVlResults"></datalist>
										</td>
										<!--<td class="vlresultequ" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "" : "none"; ?>"></td>
										<td class="vlresultequ" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? "" : "none"; ?>"></td>-->
										<td class="labels"><label for="batchQuality">Batch quality</label></td>
										<td>
											<label class="radio-inline">
												<input type="radio" id="passed" name="batchQuality" value="passed" title="Batch Quality" <?php echo ($vlQueryInfo['batch_quality'] == 'passed') ? "checked='checked'" : "" ?>>Passed
											</label>
											<label class="radio-inline">
												<input type="radio" id="failed" name="batchQuality" value="failed" title="Batch Quality" <?php echo ($vlQueryInfo['batch_quality'] == 'failed') ? "checked='checked'" : "" ?>>Failed
											</label>
										</td>
										<td class="labels"><label for="testQuality">Sample test quality</label></td>
										<td>
											<label class="radio-inline">
												<input type="radio" id="passed" name="testQuality" value="passed" title="Test Quality" <?php echo ($vlQueryInfo['sample_test_quality'] == 'passed') ? "checked='checked'" : "" ?>>Passed
											</label>
											<label class="radio-inline">
												<input type="radio" id="failed" name="testQuality" value="invalid" title="Test Quality" <?php echo ($vlQueryInfo['sample_test_quality'] == 'invalid') ? "checked='checked'" : "" ?>>Invalid
											</label>
										</td>
									</tr>
									<tr>
										<td class="labels"><label for="batchNo">Batch</label></td>
										<td>
											<select name="batchNo" id="batchNo" class="form-control batchSelect2" title="Please choose batch number" style="width:100%;">
												<option value="">-- Select --</option>
												<?php foreach ($bResult as $bName) { ?>
													<option value="<?php echo $bName['batch_id']; ?>" <?php echo ($vlQueryInfo['sample_batch_id'] == $bName['batch_id']) ? "selected='selected'" : "" ?>><?php echo $bName['batch_code']; ?>
													</option>
												<?php
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row" colspan="6" style="font-size: 18px; font-weight: bold;">For
											failed / invalid runs only</th>
									</tr>
									<tr>
										<td class="labels"><label for="testDate">Repeat Test date</label></td>
										<td>
											<input type="text" class="form-control" name="failedTestDate" id="failedTestDate" placeholder="Test Date" title="Enter Testing Date" style="width:100%;" value="<?php echo $vlQueryInfo['failed_test_date']; ?>">
										</td>
										<td class="labels"><label for="failedTestingTech">Testing Platform</label></td>
										<td>
											<select name="failedTestingTech" id="failedTestingTech" onchange="getVlResults('failedTestingTech','failedPossibleVlResults', 'failedvlResult');" class="form-control" title="Please choose VL Testing Platform" style="width:100%;">
												<option value="">-- Select --</option>
												<?php foreach ($importResult as $mName) { ?>
													<option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] . '##' . $mName['instrument_id']; ?>" <?php echo ($vlQueryInfo['failed_test_tech'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] == $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit']) ? "selected='selected'" : "" ?>><?php echo $mName['machine_name']; ?>
													</option>
												<?php
												}
												?>
											</select>
										</td>
										<td class="labels"><label for="failedvlResult">VL result</label></td>
										<td class="resultInputContainer">
											<input list="failedPossibleVlResults" disabled="disabled" type="text" class="form-control" name="failedvlResult" id="failedvlResult" placeholder="VL Result" title="Enter VL Result" style="width:100%;" value="<?php echo $vlQueryInfo['failed_vl_result']; ?>">
											<datalist id="failedPossibleVlResults"></datalist>
										</td>
									</tr>
									<tr>
										<td class="labels"><label for="failedbatchQuality">Batch quality</label></td>
										<td>
											<label class="radio-inline">
												<input type="radio" id="passed" name="failedbatchQuality" value="passed" title="Batch Quality" <?php echo ($vlQueryInfo['failed_batch_quality'] == 'passed') ? "checked='checked'" : "" ?>>Passed
											</label>
											<label class="radio-inline">
												<input type="radio" id="failed" name="failedbatchQuality" value="failed" title="Batch Quality" <?php echo ($vlQueryInfo['failed_batch_quality'] == 'failed') ? "checked='checked'" : "" ?>>Failed
											</label>
										</td>
										<td class="labels"><label for="failedtestQuality">Sample test quality</label>
										</td>
										<td>
											<label class="radio-inline">
												<input type="radio" id="passed" name="failedtestQuality" value="passed" title="Test Quality" <?php echo ($vlQueryInfo['failed_sample_test_quality'] == 'passed') ? "checked='checked'" : "" ?>>Passed
											</label>
											<label class="radio-inline">
												<input type="radio" id="failed" name="failedtestQuality" value="invalid" title="Test Quality" <?php echo ($vlQueryInfo['failed_sample_test_quality'] == 'invalid') ? "checked='checked'" : "" ?>>Invalid
											</label>
										</td>
										<td class="labels"><label for="failedbatchNo">Batch</label></td>
										<td>
											<select name="failedbatchNo" id="failedbatchNo" class="form-control batchSelect2" title="Please choose batch number" style="width:100%;">
												<option value="">-- Select --</option>
												<?php foreach ($bResult as $bName) { ?>
													<option value="<?php echo $bName['batch_id']; ?>" <?php echo ($vlQueryInfo['failed_batch_id'] == $bName['batch_id']) ? "selected='selected'" : "" ?>><?php echo $bName['batch_code']; ?>
													</option>
												<?php
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="labels"><label for="finalViralLoadResult">Final Viral Load
												Result(copies/mL)</label></td>
										<td class="resultInputContainer">
											<input list="finalPossibleVlResults" disabled="disabled" type="text" class="form-control" name="finalViralLoadResult" id="finalViralLoadResult" placeholder="Final VL Result" title="Enter VL Result" style="width:100%;" value="<?php echo $vlQueryInfo['result']; ?>">
											<datalist id="finalPossibleVlResults"></datalist>
										</td>
										<td class="labels"><label for="testQuality">QC Tech Name</label></td>
										<td>
											<input type="text" class="form-control" name="qcTechName" id="qcTechName" placeholder="QC Tech Name" title="Enter QC Tech Name" style="width:100%;" value="<?php echo $vlQueryInfo['qc_tech_name']; ?>">
										</td>
										<td class="labels"><label for="reportDate">Report Date</label></td>
										<td>
											<input type="text" class="form-control date" name="reportDate" id="reportDate" placeholder="Report Date" title="Enter Report Date" style="width:100%;" value="<?php echo $vlQueryInfo['report_date']; ?>">
										</td>
									</tr>
									<tr>
										<td class="labels"><label for="
										">QC Tech Signature</label></td>
										<td>
											<input type="text" class="form-control" name="qcTechSign" id="qcTechSign" placeholder="QC Tech Signature" title="Enter QC Tech Signature" style="width:100%;" value="<?php echo $vlQueryInfo['qc_tech_sign']; ?>">
										</td>
										<td class="labels"><label for="testQuality">QC Date</label></td>
										<td>
											<input type="text" class="form-control date" name="qcDate" id="qcDate" placeholder="QC Date" title="Enter QC Date" style="width:100%;" value="<?php echo $vlQueryInfo['qc_date']; ?>">
										</td>
										<td></td>
										<td></td>
									</tr>
									<tr>
										<td style="width:14%;" class="labels"><label for="reviewedOn"> Reviewed On
											</label></td>
										<td style="width:14%;">
											<input type="text" name="reviewedOn" value="<?php echo $vlQueryInfo['result_reviewed_datetime']; ?>" id="reviewedOn" class="dateTime form-control" placeholder="Reviewed on" title="Please enter the reviewed on" />
										</td>
										<td style="width:14%;" class="labels"><label for="reviewedBy"> Reviewed By
											</label></td>
										<td style="width:14%;">
											<select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="Please choose reviewed by" style="width: 100%;">
												<?= $general->generateSelectOptions($userInfo, $vlQueryInfo['result_reviewed_by'], '-- Select --'); ?>
											</select>
										</td>
										<td style="width:14%;" class="labels"><label for="approvedOnDateTime"> Approved
												On </label></td>
										<td style="width:14%;">
											<input type="text" name="approvedOnDateTime" value="<?php echo $vlQueryInfo['result_approved_datetime']; ?>" id="approvedOnDateTime" class="dateTime form-control" placeholder="Approved on" title="Please enter the approved on" />
										</td>
									</tr>
									<tr>
										<td style="width:14%;" class="labels"><label for="approvedBy"> Approved By
											</label></td>
										<td style="width:14%;">
											<select name="approvedBy" id="approvedBy" class="select2 form-control" title="Please choose approved by" style="width: 100%;">
												<?= $general->generateSelectOptions($userInfo, $vlQueryInfo['result_approved_by'], '-- Select --'); ?>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<!-- /.box-body -->
					<div class="box-footer">
						<input type="hidden" name="revised" id="revised" value="no" />
						<input type="hidden" name="vlSampleId" id="vlSampleId" value="<?= ($vlQueryInfo['vl_sample_id']); ?>" />
						<input type="hidden" name="isRemoteSample" value="<?= ($vlQueryInfo['remote_sample']); ?>" />
						<input type="hidden" name="reasonForResultChangesHistory" id="reasonForResultChangesHistory" value="<?php echo $vlQueryInfo['reason_for_result_changes']; ?>" />
						<input type="hidden" name="oldStatus" value="<?= ($vlQueryInfo['result_status']); ?>" />
						<input type="hidden" name="countryFormId" id="countryFormId" value="<?php echo $arr['vl_form']; ?>" />
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
						<a href="/vl/requests/vl-requests.php" class="btn btn-default"> Cancel</a>
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
<script>
	let provinceName = true;
	let facilityName = true;

	function getVlResults(testingPlatformId, datalistId, vlResultId) {
		const testingVal = $('#' + testingPlatformId).val();
		if (testingVal == "") {
			$("#" + vlResultId).val("");
			$("#" + vlResultId).attr("disabled", true);
			return false;
		}
		const str1 = testingVal.split("##");
		const platformId = str1[3];
		$("#" + datalistId).html('');
		$.post("/vl/requests/getVlResults.php", {
				instrumentId: platformId,
			},
			function(data) {
				// alert(data);
				if (data != "") {
					$("#" + datalistId).html(data);
					$("#" + vlResultId).attr("disabled", false);
				}
			});
	}

	$(document).ready(function() {
		checkCollectionDate('<?php echo $vlQueryInfo['sample_collection_date']; ?>');
		getVlResults('testingPlatform', 'possibleVlResults', 'cphlvlResult');
		getVlResults('failedTestingTech', 'failedPossibleVlResults', 'failedvlResult');
		getVlResults('testingPlatform', 'finalPossibleVlResults', 'finalViralLoadResult');
		$("#gender").change(function() {
			if ($(this).val() == "female")
				$(".femaleFactor").show();
			else
				$(".femaleFactor").hide();
		});
		$("input[name='typeOfSample']").click(function() {
			if ($(this).val() == "DBS") {
				$("#plasmaOne,#plasmaTwo").val("");
				$("#wholeBloodOne,#wholeBloodTwo").val("");
			} else if ($(this).val() == "Whole blood") {
				$("#plasmaOne,#plasmaTwo").val("");
			} else if ($(this).val() == "Plasma") {
				$("#wholeBloodOne,#wholeBloodTwo").val("");
			}
		});

		//getfacilityProvinceDetails($("#facilityId").val());
		$('.date').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
			timeFormat: "HH:mm",
			yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
		}).click(function() {
			$('.ui-datepicker-calendar').show();
		});
		let dateFormatMask = '<?= $_SESSION['jsDateFormatMask'] ?? '99-aaa-9999'; ?>';
		$('.date').mask(dateFormatMask);

		$('#sampleCollectionDate,#sampleReceivedDate,#testDate,#failedTestDate').mask('<?= $_SESSION['jsDateFormatMask'] ?? '99-aaa-9999'; ?> 99:99');

		$('#sampleCollectionDate,#sampleReceivedDate,#testDate,#failedTestDate').datetimepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
			timeFormat: "HH:mm",
			onChangeMonthYear: function(year, month, widget) {
				setTimeout(function() {
					$('.ui-datepicker-calendar').show();
				});
			},
			yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
		}).click(function() {
			$('.ui-datepicker-calendar').show();
		});

		$('#processTime').timepicker({
			changeMonth: true,
			changeYear: true,
			timeFormat: "HH:mm",
			yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
		}).click(function() {
			$('.ui-datepicker-calendar').hide();
		});
		getAge();

		$('#labId').select2({
			placeholder: "Select Laboratory Name"
		});
		$('#reviewedBy').select2({
			placeholder: "Select Reviewed By"
		});
		$('#approvedBy').select2({
			placeholder: "Select Approved By"
		});

		$('#artRegimen').select2({
			placeholder: "Select ART Regimen"
		});

		$('.batchSelect2').select2({
			placeholder: "Select Batch"
		});

		$('#labId').select2({
			placeholder: "Select Testing Lab Name"
		});
		$('#facilityId').select2({
			placeholder: "Select Clinic/Health Center"
		});
		$('#district').select2({
			placeholder: "District"
		});
		$('#province').select2({
			placeholder: "Province"
		});

	});

	function validateNow() {

		clearDatePlaceholderValues('input.date, input.dateTime');

		flag = deforayValidator.init({
			formId: 'vlRequestForm'
		});
		$('.isRequired').each(function() {
			($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
		});
		$("#saveNext").val('save');
		if (flag) {
			$.blockUI();
			document.getElementById('vlRequestForm').submit();
		}
	}

	function getfacilityDetails(obj) {
		$.blockUI();
		var cName = $("#facilityId").val();
		var pName = $("#province").val();
		$('#reqClinicianPhoneNumber').val('');
		if (pName != '' && provinceName && facilityName) {
			facilityName = false;
		}
		if (pName != '') {
			if (provinceName) {
				$.post("/includes/siteInformationDropdownOptions.php", {
						pName: pName,
						testType: 'vl'
					},
					function(data) {
						if (data != "") {
							details = data.split("###");
							$("#facilityId").html(details[0]);
							$("#district").html(details[1]);
							$("#clinicianName").val(details[2]);
						}
					});
			}
		} else if (pName == '' && cName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#facilityId").html("<?php echo $facility; ?>");
		}
		$.unblockUI();
	}

	function getfacilityDistrictwise(obj) {
		$.blockUI();
		var dName = $("#district").val();
		var cName = $("#facilityId").val();
		$('#reqClinicianPhoneNumber').val('');
		if (dName != '') {
			$.post("/includes/siteInformationDropdownOptions.php", {
					dName: dName,
					cliName: cName,
					testType: 'vl'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#facilityId").html(details[0]);
					}
				});
		}
		$.unblockUI();
	}

	function getfacilityProvinceDetails(obj) {
		$.blockUI();
		//$('#reqClinicianPhoneNumber').val($("#facilityId").find(":selected").attr("data-mobile-nos"));
		$.unblockUI();
		//   check facility name
		//    var cName = $("#facilityId").val();
		//    var pName = $("#province").val();
		//    if(cName!='' && provinceName && facilityName){
		//      provinceName = false;
		//    }
		//    if(cName!='' && facilityName){
		//      $.post("/includes/siteInformationDropdownOptions.php", { cName : cName},
		//      function(data){
		//	  if(data != ""){
		//            details = data.split("###");
		//            $("#province").html(details[0]);
		//            $("#district").html(details[1]);
		//            $("#clinicianName").val(details[2]);
		//	  }
		//      });
		//    }else if(pName=='' && cName==''){
		//      provinceName = true;
		//      facilityName = true;
		//      $("#province").html("< ?php echo $province;?>");
		//      $("#facilityId").html("< ?php echo $facility;?>");
		//    }
	}

	function checkValue() {
		var artRegimen = $("#artRegimen").val();
		if (artRegimen == 'other') {
			$(".newArtRegimen").show();
			$("#newArtRegimen").addClass("isRequired");
		} else {
			$(".newArtRegimen").hide();
			$("#newArtRegimen").removeClass("isRequired");
		}
	}

	function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
		var removeDots = obj.value.replace(/\./g, "");
		removeDots = removeDots.replace(/\,/g, "");
		//str=obj.value;
		removeDots = removeDots.replace(/\s{2,}/g, ' ');
		$.post("/includes/checkDuplicate.php", {
				tableName: tableName,
				fieldName: fieldName,
				value: removeDots.trim(),
				fnct: fnct,
				format: "html"
			},
			function(data) {
				if (data === '1') {
					alert(alrt);
					$('#' + obj.id).val('');
					duplicateName = false;
				}
			});
	}

	$("input:radio[name=isSampleRejected]").on("change", function() {
		if ($(this).val() == 'yes') {
			$(".rejectionReason,.vlresultequ").show();
			$(".reasonequ,.vlResult").hide();
			$('#rejectionReason').addClass("isRequired");
		} else {
			$(".reasonequ,.vlResult").show();
			$(".rejectionReason,.vlresultequ").hide();
			$('#rejectionReason').removeClass("isRequired");
		}
	})
	$("input:radio[name=reasonForVLTesting]").on("change", function() {
		if ($(this).val() == 'Re-collection requested by lab') {
			$('#reason').addClass("isRequired").attr('readonly', false);
		} else {
			$('#reason').removeClass("isRequired").attr('readonly', true).val('');
		}
	})
</script>
