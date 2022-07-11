<?php
ob_start();

$type = base64_decode($_GET['t']);
$db = MysqliDb::getInstance();
$title = _("Import ") . strtoupper($type) . _(" Test Results From File");

require_once(APPLICATION_PATH . '/header.php');

$general = new \Vlsm\Models\General();
$query = "SELECT config_id,machine_name,import_machine_file_name FROM import_config WHERE status='active' ORDER BY machine_name ASC";
$iResult = $db->rawQuery($query);

$fQuery = "SELECT * FROM facility_details WHERE facility_type=2";
$fResult = $db->rawQuery($fQuery);

if ($type == 'vl') {
	$lastQuery = "SELECT lab_id FROM form_vl WHERE lab_id is not NULL ORDER BY vl_sample_id DESC LIMIT 1";
} else if ($type == 'eid') {
	$lastQuery = "SELECT lab_id FROM form_eid WHERE lab_id is not NULL ORDER BY eid_id DESC LIMIT 1";
} else if ($type == 'covid19') {
	$lastQuery = "SELECT lab_id FROM form_covid19 WHERE lab_id is not NULL ORDER BY covid19_id DESC LIMIT 1";
} else if ($type == 'hepatitis') {
	$lastQuery = "SELECT lab_id FROM form_hepatitis WHERE lab_id is not NULL ORDER BY hepatitis_id DESC LIMIT 1";
} else if ($type == 'tb') {
	$lastQuery = "SELECT lab_id FROM form_tb WHERE lab_id is not NULL ORDER BY tb_id DESC LIMIT 1";
}

$lastResult = $db->rawQueryOne($lastQuery);


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa-solid fa-pen-to-square"></i> <?php echo _("Import"); ?> <?= strtoupper($type); ?> <?php echo _("Test Results From File"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa-solid fa-chart-pie"></i> <?php echo _("Home"); ?></a></li>
			<li class="active"><?php echo _("Import Result"); ?></li>
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
				<div style="font-size:1.1em;padding:1em;">
					<p style="color:red;"><?php echo _("Please ensure that the Sample IDs in the import file match the Sample IDs in VLSM."); ?> </p>

					<p>
						<?php echo _("To ensure proper result import :") ?>
					<ol>
						<li><?php echo _("Record the Samples in VLSM before sending them to Viral Load machine."); ?></li>
						<li><?php echo _("Create a Batch with the samples recorded in the previous step."); ?></li>
						<li><?php echo _("Use the Batch to test Samples in Viral Load machines."); ?></li>
						<li><?php echo _("Export the result file (containing Sample IDs generated by VLSM) from the VL machine."); ?></li>
						<li><?php echo _("Import the file below. Make sure to pick the right Machine Configuration."); ?></li>
					</ol>
					</p>

				</div>
				<form class="form-horizontal" method='post' name='addImportResultForm' id='addImportResultForm' enctype="multipart/form-data" autocomplete="off" action="addImportResultHelper.php">
					<div class="box-body">
						<div class="wizard_content">
							<div class="row setup-content step" id="step-1" style="display:block;">
								<div class="col-xs-12">
									<div class="col-md-12" id="stepOneForm">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="machineName" class="col-lg-4 control-label"><?php echo _("Configuration Name"); ?> <span class="mandatory">*</span></label>
													<div class="col-lg-7">
														<select name="machineName" id="machineName" class="form-control isRequired" title="<?php echo _('Please select the import machine type');?>" onchange="getConfigMachineName();">
															<option value=""> <?php echo _("-- Select --");?> </option>
															<?php foreach ($iResult as $val) { ?>
																<option value="<?php echo base64_encode($val['import_machine_file_name']); ?>"><?php echo ucwords($val['machine_name']); ?></option>
															<?php } ?>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="machineName" class="col-lg-4 control-label"><?php echo _("Configuration Machine Name"); ?></label>
													<div class="col-lg-7">
														<select name="configMachineName" id="configMachineName" class="form-control" title="<?php echo _('Please select the import config machine name');?>">
															<option value=""> <?php echo _("-- Select --");?> </option>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-lg-4 control-label"><?php echo _("Upload"); ?> <?= strtoupper($type); ?> <?php echo _("File"); ?> <span class="mandatory">*</span></label>
													<div class="col-lg-7">
														<input type="file" class="isRequired" accept=".xls,.xlsx,.csv,.txt" name="resultFile" id="resultFile" title="<?php echo _('Please choose result file');?>">
														<?php echo _("(Upload xls, xlsx, csv, txt format)"); ?>
													</div>
												</div>
											</div>
										</div>

									</div>
								</div>
							</div>
							<div class="row setup-content step" id="step-2">
								<div class="col-xs-12">
									<div class="col-md-12" id="stepTwoForm">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="labId" class="col-lg-4 control-label"><?php echo _("Lab Name"); ?> <span class="mandatory">*</span></label>
													<div class="col-lg-7">
														<select name="labId" id="labId" class="form-control isRequired" title="<?php echo _('Please select the lab name');?>">
															<option value=""> <?php echo _("-- Select --");?> </option>
															<?php
															foreach ($fResult as $val) {
															?>
																<option value="<?php echo base64_encode($val['facility_id']); ?>" <?php echo (isset($lastResult['lab_id']) && $lastResult['lab_id'] == $val['facility_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($val['facility_name']); ?></option>
															<?php } ?>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="row form-group">
											<div class="box-footer">
												<input type="hidden" id="vltestPlatform" name="vltestPlatform" value="" />
												<input type="hidden" id="type" name="type" value="<?php echo $type; ?>" />
												<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _("Submit"); ?></a>
												<a href="/dashboard/index.php" class="btn btn-default"> <?php echo _("Cancel"); ?></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /.box-body -->

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
	function validateNow() {
		flag = deforayValidator.init({
			formId: 'addImportResultForm'
		});
		if (flag) {
			document.getElementById('addImportResultForm').submit();
		}
	}

	$("#machineName").change(function() {
		if ($("#machineName").val() == "") {
			$("#vltestPlatform").val("");
		} else {
			$("#vltestPlatform").val($("#machineName option:selected").text());
		}
	});

	function getConfigMachineName() {
		if ($("#machineName").val() != '') {
			$.post("/import-result/getConfigMachineName.php", {
					configId: $("#machineName").val()
				},
				function(data) {
					$("#configMachineName").html(data);
				});
		}
	}
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>