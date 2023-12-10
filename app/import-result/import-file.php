<?php

use App\Registries\AppRegistry;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_GET = $request->getQueryParams();

$type = $_GET['t'];
$title = _translate("Import") . " " . strtoupper((string) $type) . " " . _translate("test results from file");

require_once APPLICATION_PATH . '/header.php';

/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$query = "SELECT config_id,machine_name,import_machine_file_name
            FROM instruments
            WHERE status='active'
            ORDER BY machine_name ASC";
$iResult = $db->rawQuery($query);

$fQuery = 'SELECT * FROM facility_details as f
            INNER JOIN testing_labs as t ON t.facility_id=f.facility_id
            WHERE t.test_type = ?
                AND f.facility_type=2
                AND (f.facility_attributes->>"$.allow_results_file_upload" = "yes"
                    OR f.facility_attributes->>"$.allow_results_file_upload" IS NULL)
            ORDER BY f.facility_name ASC';
$fResult = $db->rawQuery($fQuery, array($type));

if ($type == 'vl') {
	$lastQuery = "SELECT lab_id FROM form_vl WHERE lab_id is not NULL ORDER BY vl_sample_id DESC LIMIT 1";
} elseif ($type == 'eid') {
	$lastQuery = "SELECT lab_id FROM form_eid WHERE lab_id is not NULL ORDER BY eid_id DESC LIMIT 1";
} elseif ($type == 'covid19') {
	$lastQuery = "SELECT lab_id FROM form_covid19 WHERE lab_id is not NULL ORDER BY covid19_id DESC LIMIT 1";
} elseif ($type == 'hepatitis') {
	$lastQuery = "SELECT lab_id FROM form_hepatitis WHERE lab_id is not NULL ORDER BY hepatitis_id DESC LIMIT 1";
} elseif ($type == 'tb') {
	$lastQuery = "SELECT lab_id FROM form_tb WHERE lab_id is not NULL ORDER BY tb_id DESC LIMIT 1";
}

$lastResult = $db->rawQueryOne($lastQuery);


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-list-check"></em> <?php echo _translate("Import"); ?> <?= strtoupper(htmlspecialchars((string) $type)); ?> <?php echo _translate("Test Results From File"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
			<li class="active"><?php echo _translate("Import Result"); ?></li>
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
					<p style="color:red;"><?php echo _translate("Please ensure that the Sample IDs in the import file match the Sample IDs in VLSM."); ?> </p>

					<p>
						<?php echo _translate("To ensure proper result import :") ?>
					<ol>
						<li><?php echo _translate("Record the Samples in LIS before sending them to Testing machine."); ?></li>
						<li><?php echo _translate("Create a Batch with the samples recorded in the previous step."); ?></li>
						<li><?php echo _translate("Use the Batch to test Samples in Viral Load machines."); ?></li>
						<li><?php echo _translate("Export the result file (containing Sample IDs generated by VLSM) from the VL machine."); ?></li>
						<li><?php echo _translate("Import the file below. Make sure to pick the right Machine Configuration."); ?></li>
					</ol>
					</p>


					<form class="form-horizontal" method='post' name='importFIleForm' id='importFIleForm' enctype="multipart/form-data" autocomplete="off" action="import-file-helper.php">
						<div class="box-body">
							<div class="wizard_content">
								<div class="row setup-content step" id="step-1" style="display:block;">
									<div class="col-xs-12">
										<div class="col-md-12" id="stepOneForm">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="machineName" class="col-lg-4 control-label"><?php echo _translate("Instrument Name"); ?> <span class="mandatory">*</span></label>
														<div class="col-lg-7">
															<select name="machineName" id="machineName" class="form-control isRequired" title="<?php echo _translate('Please select the import machine type'); ?>" onchange="getConfigMachineName();">
																<option value=""> <?php echo _translate("-- Select --"); ?> </option>
																<?php foreach ($iResult as $val) { ?>
																	<option value="<?php echo base64_encode((string) $val['import_machine_file_name']); ?>"><?php echo ($val['machine_name']); ?></option>
																<?php } ?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="machineName" class="col-lg-4 control-label"><?php echo _translate("Configuration Machine Name"); ?></label>
														<div class="col-lg-7">
															<select name="configMachineName" id="configMachineName" class="form-control" title="<?php echo _translate('Please select the import config machine name'); ?>">
																<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-lg-4 control-label"><?php echo _translate("Upload"); ?> <?= strtoupper(htmlspecialchars((string) $type)); ?> <?php echo _translate("File"); ?> <span class="mandatory">*</span></label>
														<div class="col-lg-7">
															<input type="file" class="isRequired" accept=".xls,.xlsx,.csv,.txt" name="resultFile" id="resultFile" title="<?php echo _translate('Please choose result file'); ?>">
															<?php echo _translate("(Upload xls, xlsx, csv, txt format)"); ?>
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
														<label for="labId" class="col-lg-4 control-label"><?php echo _translate("Lab Name"); ?> <span class="mandatory">*</span></label>
														<div class="col-lg-7">
															<select name="labId" id="labId" class="form-control isRequired" title="<?php echo _translate('Please select the lab name'); ?>">
																<option value=""> <?php echo _translate("-- Select --"); ?> </option>
																<?php
																foreach ($fResult as $val) {
																?>
																	<option value="<?php echo base64_encode((string) $val['facility_id']); ?>" <?php echo (isset($lastResult['lab_id']) && $lastResult['lab_id'] == $val['facility_id']) ? "selected='selected'" : ""; ?>><?php echo ($val['facility_name']); ?></option>
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
													<input type="hidden" id="dateFormat" name="dateFormat" value="" />
													<input type="hidden" id="fileName" name="fileName" value="" />
													<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _translate("Submit"); ?></a>
													<a href="/dashboard/index.php" class="btn btn-default"> <?php echo _translate("Cancel"); ?></a>
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
		var _filename = $("#configMachineName").find(':selected').data("filename");
		var _dateformat = $("#configMachineName").find(':selected').data("dateformat");
		$('#dateFormat').val(_dateformat);
		$('#fileName').val(_filename);
		flag = deforayValidator.init({
			formId: 'importFIleForm'
		});
		if (flag) {
			document.getElementById('importFIleForm').submit();
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
require_once APPLICATION_PATH . '/footer.php';
