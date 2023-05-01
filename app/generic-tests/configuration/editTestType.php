<?php


require_once(APPLICATION_PATH . '/header.php');

$id = base64_decode($_GET['id']);
$tQuery = "SELECT * from r_test_types where test_type_id=$id";
$testTypeInfo = $db->query($tQuery);
$testAttribute=json_decode($testTypeInfo[0]['test_form_config'],true);
$testResultAttribute=json_decode($testTypeInfo[0]['test_results_config'],true);

$stQuery = "SELECT * from r_sample_types where sample_type_status='active'";
$sampleTypeInfo=$db->query($stQuery);

$tQuery = "SELECT * from r_testing_reasons where test_reason_status='active'";
$testReasonInfo=$db->query($tQuery);

$symQuery = "SELECT * from r_symptoms where symptom_status='active'";
$symptomInfo=$db->query($symQuery);

$testSampleMapQuery = "SELECT * from generic_test_sample_type_map where test_type_id=$id";
$testSampleMapInfo=$db->query($testSampleMapQuery);
$testSampleId=[]
foreach($testSampleMapInfo as $val){
	$testSampleId[]=$val['sample_type_id'];
}
$testReasonMapQuery = "SELECT * from generic_test_reason_map where test_type_id=$id";
$testReasonMapInfo=$db->query($testReasonMapQuery);
$testReasonId=[]
foreach($testReasonMapInfo as $val){
	$testReasonId[]=$val['test_reason_id'];
}
$testSymptomsMapQuery = "SELECT * from generic_test_symptoms_map where test_type_id=$id";
$testSymptomsMapInfo=$db->query($testSymptomsMapQuery);
$testSymptomsId=[]
foreach($testSymptomsMapInfo as $val){
	$testSymptomsId[]=$val['symptom_id'];
}
?>
<style>
	.tooltip-inner {
		background-color: #fff;
		color: #000;
		border: 1px solid #000;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-sharp fa-solid fa-gears"></em> <?php echo _("Edit Test Type"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
			<li class="active"><?php echo _("Edit Test Type"); ?></li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="box box-default">
			<div class="box-header with-border">
				<div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _("indicates required field"); ?> &nbsp;</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-horizontal" method='post' name='editTestTypeForm' id='editTestTypeForm' autocomplete="off" action="editTestTypeHelper.php">
					<div class="box-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="testStandardName" class="col-lg-4 control-label"><?php echo _("Test Standard Name"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<input type="text" class="form-control isRequired" id="testStandardName" name="testStandardName" placeholder='<?php echo _("Test Standard Name"); ?>' title='<?php echo _("Please enter standard name"); ?>' value="<?php echo $testTypeInfo[0]['test_standard_name']; ?>" onblur="checkNameValidation('r_test_types','test_standard_name',this,'<?php echo "test_type_id##" . $testTypeInfo[0]['test_type_id']; ?>','<?php echo _("This test standard name that you entered already exists.Try another name");?>',null)" />
										<input type="hidden" name="testTypeId" id="testTypeId" value="<?php echo base64_encode($testTypeInfo[0]['test_type_id']); ?>" />
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label for="testGenericName" class="col-lg-4 control-label"><?php echo _("Test Generic Name"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
									<input type="text" class="form-control isRequired" id="testGenericName" name="testGenericName" placeholder='<?php echo _("Test Generic Name"); ?>' title='<?php echo _("Please enter the test generic name"); ?>' value="<?php echo $testTypeInfo[0]['test_generic_name']; ?>" onblur="checkNameValidation('r_test_types','test_generic_name',this,'<?php echo "test_type_id##" . $testTypeInfo[0]['test_type_id']; ?>','<?php echo _("This test generic name that you entered already exists.Try another name");?>',null)"/>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="testShortCode" class="col-lg-4 control-label"><?php echo _("Test Short Code"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<input type="text" class="form-control isRequired" id="testShortCode" name="testShortCode" placeholder='<?php echo _("Test Short Code"); ?>' title='<?php echo _("Please enter short code"); ?>' onblur="checkNameValidation('r_test_types','test_short_code',this,'<?php echo "test_type_id##" . $testTypeInfo[0]['test_type_id']; ?>','<?php echo _("This test short code that you entered already exists.Try another code");?>',null)"  value="<?php echo $testTypeInfo[0]['test_short_code']; ?>"/>
									</div>
								</div>
							</div>
						
							<div class="col-md-6">
								<div class="form-group">
									<label for="testLoincCode" class="col-lg-4 control-label"><?php echo _("Test LOINC Code"); ?></label>
									<div class="col-lg-7">
										<input type="text" class="form-control" id="testLoincCode" name="testLoincCode" placeholder='<?php echo _("Test LOINC Code"); ?>' title='<?php echo _("Please enter test loinc code"); ?>' value="<?php echo $testTypeInfo[0]['test_loinc_code']; ?>" onblur="checkNameValidation('r_test_types','test_loinc_code',this,'<?php echo "test_type_id##" . $testTypeInfo[0]['test_type_id']; ?>','<?php echo _("This test loinc code that you entered already exists.Try another code");?>',null)"  value="<?php echo $testTypeInfo[0]['test_loinc_code']; ?>"/>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="sampleType" class="col-lg-4 control-label"><?php echo _("Sample Type"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<select class="form-control isRequired" name='sampleType[]' id='sampleType' title="<?php echo _('Please select the sample type');?>" multiple>
											<option value="">--Select--</option>
											<?php
											foreach($sampleTypeInfo as $sampleType){
											?>
											<option value="<?php echo $sampleType['sample_type_id'];?>" <?php echo in_array($sampleType['sample_type_id'],$testSampleId) ? "selected='selected'" : "" ?>><?php echo $sampleType['sample_type_name'];?></option>
											<?php
											}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="testingReason" class="col-lg-4 control-label"><?php echo _("Testing Reason"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<select class="form-control isRequired" name='testingReason[]' id='testingReason' title="<?php echo _('Please select the testing reason');?>" multiple>
											<option value="">--Select--</option>
											<?php
											foreach($testReasonInfo as $testReason){
											?>
											<option value="<?php echo $testReason['test_reason_id'];?>" <?php echo in_array($testReason['test_reason_id'],$testReasonId) ? "selected='selected'" : "" ?>><?php echo $testReason['test_reason'];?></option>
											<?php
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="symptoms" class="col-lg-4 control-label"><?php echo _("Symptoms"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<select class="form-control isRequired" name='symptoms[]' id='symptoms' title="<?php echo _('Please select the symptoms');?>" multiple>
											<option value="">--Select--</option>
											<?php
											foreach($symptomInfo as $val){
											?>
											<option value="<?php echo $val['symptom_id'];?>" <?php echo in_array($val['symptom_id'],$testSymptomsId) ? "selected='selected'" : "" ?>><?php echo $val['symptom_name'];?></option>
											<?php
											}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="status" class="col-lg-4 control-label"><?php echo _("Status"); ?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<select class="form-control isRequired" name='status' id='status' title="<?php echo _('Please select the status');?>">
											<option value="active" <?php echo ($testTypeInfo[0]['test_status'] == 'active') ? "selected='selected'" : "" ?>><?php echo _("Active");?></option>
											<option value="inactive" <?php echo ($testTypeInfo[0]['test_status'] == 'inactive') ? "selected='selected'" : "" ?>><?php echo _("Inactive");?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
						
						<div class="box-header">
							<h3 class="box-title "><?php echo _("Form Configuration"); ?></h3>
						</div>
						
						<div class="box-body">
							<table border="0" class="table table-striped table-bordered table-condensed" aria-hidden="true" style="width:100%;">
								<thead>
									<tr>
										<th style="text-align:center;"><?php echo _("Field Name"); ?> <span class="mandatory">*</span></th>
										<th style="text-align:center;"><?php echo _("Field Type"); ?> <span class="mandatory">*</span></th>
										<th style="text-align:center;"><?php echo _("Is it Mandatory?"); ?> <span class="mandatory">*</span></th>
										<th style="text-align:center;width:15%;"><?php echo _("Section"); ?> <span class="mandatory">*</span></th>
										<th style="text-align:center;"><?php echo _("Action"); ?></th>
									</tr>
								</thead>
								<tbody id="attributeTable">
									<?php
									$n=count($testAttribute['field_name']);
									if ($n > 0) {
										for ($i=0;$i<$n;$i++){
									?>
									<tr>
										<td>
											<input type="text" name="fieldName[]" id="fieldName<?php echo $i ?>" class="form-control fieldName isRequired" placeholder='<?php echo _("Field Name"); ?>' title='<?php echo _("Please enter field name"); ?>' onblur="checkDublicateName(this, 'fieldName');" value="<?php echo $testAttribute['field_name'][$i]; ?>"/>
											<input type="hidden" name="fieldId[]" id="fieldId<?php echo $i ?>" class="form-control isRequired" value="<?php echo $testAttribute['field_id'][$i]; ?>"/>
										</td>
										<td>
											<select class="form-control isRequired" name="fieldType[]" id="fieldType<?php echo $i ?>" title="<?php echo _('Please select the field type');?>">
												<option value=""> <?php echo _("-- Select --");?> </option>
												<option value="number" <?php echo ($testAttribute['field_type'][$i] == 'number') ? "selected='selected'" : "" ?>><?php echo _("Number");?></option>
												<option value="text" <?php echo ($testAttribute['field_type'][$i] == 'text') ? "selected='selected'" : "" ?>><?php echo _("Text");?></option>
												<option value="date" <?php echo ($testAttribute['field_type'][$i] == 'date') ? "selected='selected'" : "" ?>><?php echo _("Date");?></option>
											</select>
										</td>
										<td>
											<select class="form-control isRequired" name="mandatoryField[]" id="mandatoryField<?php echo $i ?>" title="<?php echo _('Please select is it mandatory');?>">
												<option value="yes" <?php echo ($testAttribute['mandatory_field'][$i] == 'yes') ? "selected='selected'" : "" ?>><?php echo _("Yes");?></option>
												<option value="no" <?php echo ($testAttribute['mandatory_field'][$i] == 'no') ? "selected='selected'" : "" ?>><?php echo _("No");?></option>
											</select>
										</td>
										<td>
											<select class="form-control isRequired" name="section[]" id="section<?php echo $i ?>" title="<?php echo _('Please select the section');?>" onchange="checkSection('<?php echo $i ?>')">
												<option value=""> <?php echo _("-- Select --");?> </option>
												<option value="facility" <?php echo ($testAttribute['section'][$i] == 'facility') ? "selected='selected'" : "" ?>><?php echo _("Facility");?></option>
												<option value="patient" <?php echo ($testAttribute['section'][$i] == 'patient') ? "selected='selected'" : "" ?>><?php echo _("Patient");?></option>
												<option value="specimen" <?php echo ($testAttribute['section'][$i] == 'specimen') ? "selected='selected'" : "" ?>><?php echo _("Specimen");?></option>
												<option value="lap" <?php echo ($testAttribute['section'][$i] == 'lap') ? "selected='selected'" : "" ?>><?php echo _("Lab");?></option>
												<option value="other" <?php echo ($testAttribute['section'][$i] == 'other') ? "selected='selected'" : "" ?>><?php echo _("Other");?></option>
											</select>
											<input type="text" name="sectionOther[]" id="sectionOther<?php echo $i ?>" class="form-control" placeholder='<?php echo _("Section Other"); ?>' title='<?php echo _("Please enter section other"); ?>' style="<?php echo ($testAttribute['section'][$i] == 'other') ? "" : "display:none;" ?>" value="<?php echo ($testAttribute['section'][$i] == 'other') ? $testAttribute['section_other'][$i] : "" ?>"/>
										</td>
										<td align="center" style="vertical-align:middle;">
											<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="insRow();"><em class="fa-solid fa-plus"></em></a>&nbsp;&nbsp;<a class="btn btn-xs btn-default" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><em class="fa-solid fa-minus"></em></a>
										</td>
									</tr>
									<?php 
										}
									}else { ?>
									<tr>
										<td>
											<input type="text" name="fieldName[]" id="fieldName1" class="form-control fieldName isRequired" placeholder='<?php echo _("Field Name"); ?>' title='<?php echo _("Please enter field name"); ?>' onblur="checkDublicateName(this, 'fieldName');" />
										</td>
										<td>
											<select class="form-control isRequired" name="fieldType[]" id="fieldType1" title="<?php echo _('Please select the field type');?>">
												<option value=""> <?php echo _("-- Select --");?> </option>
												<option value="number"><?php echo _("Number");?></option>
												<option value="text"><?php echo _("Text");?></option>
												<option value="date"><?php echo _("Date");?></option>
											</select>
										</td>
										<td>
											<select class="form-control isRequired" name="mandatoryField[]" id="mandatoryField1" title="<?php echo _('Please select is it mandatory');?>">
												<option value="yes"><?php echo _("Yes");?></option>
												<option value="no" selected><?php echo _("No");?></option>
											</select>
										</td>
										<td align="center" style="vertical-align:middle;">
											<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="insRow();"><em class="fa-solid fa-plus"></em></a>&nbsp;&nbsp;<a class="btn btn-xs btn-default" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><em class="fa-solid fa-minus"></em></a>
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<div class="box-header">
							<h3 class="box-title "><?php echo _("Test Results Configuration"); ?></h3>
						</div>
						<div class="box-body">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="resultType" class="col-lg-4 control-label"><?php echo _("Result Type"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<select class="form-control isRequired" name='resultType' id='resultType' onchange="checkResultType();">
												<option value=""> <?php echo _("-- Select --");?> </option>
												<option value="qualitative" <?php echo ($testResultAttribute['result_type'] == 'qualitative') ? "selected='selected'" : "" ?>><?php echo _("Qualitative");?></option>
												<option value="quantitative" <?php echo ($testResultAttribute['result_type'] == 'quantitative') ? "selected='selected'" : "" ?>><?php echo _("Quantitative");?></option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="row" id="qualitativeDiv" style="display:none;">
								<div class="col-md-8">
									<div class="form-group">
										<label for="qualitativeResult" class="col-lg-3 control-label"><?php echo _("Result"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control" id="qualitativeResult" name="qualitativeResult" placeholder='<?php echo _("Comma Separated Qualitative Result"); ?>' title='<?php echo _("Please enter qualitative result"); ?>' value="<?php echo (isset($testResultAttribute['qualitative_result'])) ? implode(",",$testResultAttribute['qualitative_result']) : "" ?> "/>
										</div>
									</div>
								</div>
							</div>
							<div class="row" id="quantitativeDiv" style="display:none;">
								<div class="box-header">
									<h4 class="box-title "><?php echo _("Result"); ?></h4>
								</div>
								
								<div class="col-md-6">
									<div class="form-group">
										<label for="highValueName" class="col-lg-4 control-label"><?php echo _("High Value Name"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<input type="text" class="form-control quantitativeResult" id="highValueName" name="highValueName" placeholder='<?php echo _("Enter High Value Name"); ?>' title='<?php echo _("Please enter high value name"); ?>' value="<?php echo (isset($testResultAttribute['high_value_name'])) ? $testResultAttribute['high_value_name'] : "" ?> "/>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="highValue" class="col-lg-4 control-label"><?php echo _("High Value"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<input type="text" class="form-control forceNumeric quantitativeResult" id="highValue" name="highValue" placeholder='<?php echo _("Enter High Value"); ?>' title='<?php echo _("Please enter high value"); ?>' value="<?php echo (isset($testResultAttribute['high_value'])) ? $testResultAttribute['high_value'] : "" ?> "/>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="lowValueName" class="col-lg-4 control-label"><?php echo _("Low Value Name"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<input type="text" class="form-control quantitativeResult" id="lowValueName" name="lowValueName" placeholder='<?php echo _("Enter Low Value Name"); ?>' title='<?php echo _("Please enter low value name"); ?>' value="<?php echo (isset($testResultAttribute['low_value_name'])) ? $testResultAttribute['low_value_name'] : "" ?> "/>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="lowValue" class="col-lg-4 control-label"><?php echo _("Low Value"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<input type="text" class="form-control forceNumeric quantitativeResult" id="lowValue" name="lowValue" placeholder='<?php echo _("Enter Low Value"); ?>' title='<?php echo _("Please enter low value"); ?>' value="<?php echo (isset($testResultAttribute['low_value'])) ? $testResultAttribute['low_value'] : "" ?> "/>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="thresholdValueName" class="col-lg-4 control-label"><?php echo _("Threshold Value Name"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<input type="text" class="form-control quantitativeResult" id="thresholdValueName" name="thresholdValueName" placeholder='<?php echo _("Enter Threshold Value Name"); ?>' title='<?php echo _("Please enter threshold value name"); ?>' value="<?php echo (isset($testResultAttribute['threshold_value_name'])) ? $testResultAttribute['threshold_value_name'] : "" ?>"/>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="thresholdValue" class="col-lg-4 control-label"><?php echo _("Threshold Value"); ?> <span class="mandatory">*</span></label>
										<div class="col-lg-7">
											<input type="text" class="form-control forceNumeric quantitativeResult" id="thresholdValue" name="thresholdValue" placeholder='<?php echo _("Enter Threshold Value"); ?>' title='<?php echo _("Please enter threshold value"); ?>' value="<?php echo (isset($testResultAttribute['threshold_value'])) ? $testResultAttribute['threshold_value'] : "" ?>"/>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
					<!-- /.box-body -->
					<div class="box-footer">
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _("Submit"); ?></a>
						<a href="testType.php" class="btn btn-default"> <?php echo _("Cancel"); ?></a>
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
	tableRowId = <?php echo $n+1; ?>;

	$(document).ready(function() {
		$('input').tooltip();
		checkResultType();
		$("#sampleType").select2({
			placeholder: "<?php echo _("Select Sample Type"); ?>"
		});
		$("#testingReason").select2({
			placeholder: "<?php echo _("Select Testing Reason"); ?>"
		});
		$("#symptoms").select2({
			placeholder: "<?php echo _("Select Symptoms"); ?>"
		});
	});

	function validateNow() {
		flag = deforayValidator.init({
			formId: 'editTestTypeForm'
		});

		if (flag) {
			$.blockUI();
			document.getElementById('editTestTypeForm').submit();
		}
	}

	function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
		$.post("/includes/checkDuplicate.php", {
				tableName: tableName,
				fieldName: fieldName,
				value: obj.value.trim(),
				fnct: fnct,
				format: "html"
			},
			function(data) {
				if (data === '1') {
					alert(alrt);
					document.getElementById(obj.id).value = "";
				}
			});
	}


	function insRow() {
		rl = document.getElementById("attributeTable").rows.length;
		var a = document.getElementById("attributeTable").insertRow(rl);
		a.setAttribute("style", "display:none");
		var b = a.insertCell(0);
		var c = a.insertCell(1);
		var d = a.insertCell(2);
		var e = a.insertCell(3);
		var f = a.insertCell(4);
		
		f.setAttribute("align", "center");
		f.setAttribute("style", "vertical-align:middle");

		b.innerHTML = '<input type="text" name="fieldName[]" id="fieldName' + tableRowId + '" class="isRequired fieldName form-control" placeholder="<?php echo _('Field Name'); ?>" title="<?php echo _('Please enter field name'); ?>" onblur="checkDublicateName(this, \'fieldName\');"/ ><input type="hidden" name="fieldId[]" id="fieldId'+tableRowId+'" class="form-control isRequired" />';
		c.innerHTML = '<select class="form-control isRequired" name="fieldType[]" id="fieldType' + tableRowId + '" title="<?php echo _('Please select the field type');?>">\
							<option value=""> <?php echo _("-- Select --");?> </option>\
							<option value="number"><?php echo _("Number");?></option>\
							<option value="text"><?php echo _("Text");?></option>\
							<option value="date"><?php echo _("Date");?></option>\
						</select>';
		d.innerHTML = '<select class="form-control isRequired" name="mandatoryField[]" id="mandatoryField' + tableRowId + '" title="<?php echo _('Please select is it mandatory');?>">\
							<option value="yes"><?php echo _("Yes");?></option>\
							<option value="no" selected><?php echo _("No");?></option>\
						</select>';
		e.innerHTML = '<select class="form-control isRequired" name="section[]" id="section' + tableRowId + '" title="<?php echo _('Please select the section');?>" onchange="checkSection(' + tableRowId + ')">\
						<option value=""> <?php echo _("-- Select --");?> </option>\
						<option value="facility"><?php echo _("Facility");?></option>\
						<option value="patient"><?php echo _("Patient");?></option>\
						<option value="specimen"><?php echo _("Specimen");?></option>\
						<option value="lap"><?php echo _("Lab");?></option>\
						<option value="other"><?php echo _("Other");?></option>\
					</select>\
					<input type="text" name="sectionOther[]" id="sectionOther' + tableRowId + '" class="form-control" placeholder="<?php echo _("Section Other"); ?>" title="<?php echo _("Please enter section other"); ?>" style="display:none;"/>';
		f.innerHTML = '<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="insRow();"><em class="fa-solid fa-plus"></em></a>&nbsp;&nbsp;<a class="btn btn-xs btn-default" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><em class="fa-solid fa-minus"></em></a>';
		$(a).fadeIn(800);
		generateRandomString(tableRowId);
		tableRowId++;
	}

	function removeAttributeRow(el) {
		$(el).fadeOut("slow", function() {
			el.parentNode.removeChild(el);
			rl = document.getElementById("attributeTable").rows.length;
			if (rl == 0) {
				insRow();
			}
		});
	}

	function checkDublicateName(obj,name) {
		dublicateObj = document.getElementsByName(name+"[]");
		for (m = 0; m < dublicateObj.length; m++) {
			if (obj.value != '' && obj.id != dublicateObj[m].id && obj.value == dublicateObj[m].value) {
				alert('Duplicate value not allowed');
				$('#' + obj.id).val('');
			}
		}
	}
	function checkResultType(){
		resultType=$("#resultType").val();
		if(resultType=='qualitative'){
			$("#qualitativeDiv").show();
			$("#quantitativeDiv").hide();
			$("#qualitativeResult").addClass("isRequired");
			$(".quantitativeResult").removeClass("isRequired");
			$('.quantitativeResult').each(function() {
        		$(this).val('');
    		});
		}else{
			$("#qualitativeDiv").hide();
			$("#quantitativeDiv").show();
			$("#qualitativeResult").removeClass("isRequired");
			$(".quantitativeResult").addClass("isRequired");
			$("#qualitativeResult").val('');
		}
	}
	function checkSection(rowId){
		sectionVal=$("#section"+rowId).val();
		if(sectionVal=="other"){
			$("#sectionOther"+rowId).addClass("isRequired");
			$("#sectionOther"+rowId).show();
		}else{
			$("#sectionOther"+rowId).hide();
			$("#sectionOther"+rowId).removeClass("isRequired");
			$("#sectionOther"+rowId).val('');
		}
	}

	function generateRandomString(rowId) {
		$.post("/includes/generateRandomString.php", {
				format: "html"
			},
			function(data) {
				$("#fieldId"+rowId).val(data);
			});
	}
</script>

<?php
require_once(APPLICATION_PATH . '/footer.php');
