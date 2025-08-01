<?php

use App\Services\UsersService;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

require_once APPLICATION_PATH . '/header.php';


// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_GET = _sanitizeInput($request->getQueryParams());

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);


$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);

$userResult = $usersService->getActiveUsers();

$tQuery = "SELECT module, sample_review_by FROM temp_sample_import WHERE imported_by =? limit 1";

$tResult = $db->rawQueryOne($tQuery, array($_SESSION['userId']));



$module = $_GET['t'];
$machine = base64_decode($_GET['machine']);


$condition = " instrument_id = '$machine'";
$getMachineInfo = $general->getDataByTableAndFields('instruments', array('approved_by', 'reviewed_by'), false, $condition);

$approvedByAttr = json_decode($getMachineInfo[0]['approved_by']);
$reviewedByAttr = json_decode($getMachineInfo[0]['reviewed_by']);
$reviewedBy = $reviewedByAttr->$module;
$approvedBy = $approvedByAttr->$module;

if (!empty($tResult['sample_review_by'])) {
	$reviewBy = $tResult['sample_review_by'];
} else {
	//$reviewBy = $_SESSION['userId'];
	if (!empty($reviewedBy))
		$reviewBy = $reviewedBy;
	else
		$reviewBy = $_SESSION['userId'];
}
if (!empty($approvedBy))
	$approveBy = $approvedBy;
else
	$approveBy = $_SESSION['userId'];

$arr = $general->getGlobalConfig();
$errorInImport = false;
if ($module == 'vl') {

	$rejectionTypeQuery = "SELECT DISTINCT rejection_type
							FROM r_vl_sample_rejection_reasons
							WHERE rejection_reason_status ='active'";
	$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

	//sample rejection reason
	$rejectionQuery = "SELECT *
							FROM r_vl_sample_rejection_reasons
							WHERE rejection_reason_status = 'active'";
	$rejectionResult = $db->rawQuery($rejectionQuery);
} else if ($module == 'eid') {

	$rejectionTypeQuery = "SELECT DISTINCT rejection_type
								FROM r_eid_sample_rejection_reasons
								WHERE rejection_reason_status ='active'";
	$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

	//sample rejection reason
	$rejectionQuery = "SELECT *
						FROM r_eid_sample_rejection_reasons
						WHERE rejection_reason_status = 'active'";
	$rejectionResult = $db->rawQuery($rejectionQuery);
} else if ($module == 'covid19' || $module == 'covid-19') {

	$rejectionTypeQuery = "SELECT DISTINCT rejection_type
								FROM r_covid19_sample_rejection_reasons
								WHERE rejection_reason_status ='active'";
	$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

	//sample rejection reason
	$rejectionQuery = "SELECT *
						FROM r_covid19_sample_rejection_reasons
						WHERE rejection_reason_status = 'active'";
	$rejectionResult = $db->rawQuery($rejectionQuery);
} else if ($module == 'hepatitis') {

	$rejectionTypeQuery = "SELECT DISTINCT rejection_type
								FROM r_hepatitis_sample_rejection_reasons
								WHERE rejection_reason_status ='active'";
	$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

	//sample rejection reason
	$rejectionQuery = "SELECT *
						FROM r_hepatitis_sample_rejection_reasons
						WHERE rejection_reason_status = 'active'";
	$rejectionResult = $db->rawQuery($rejectionQuery);
} else {
	$errorInImport = true;
}


$rejectionReason = '<option value="">-- Select --</option>';
foreach ($rejectionTypeResult as $type) {
	$rejectionReason .= '<optgroup label="' . ($type['rejection_type']) . '">';
	foreach ($rejectionResult as $reject) {
		if ($type['rejection_type'] == $reject['rejection_type']) {
			$rejectionReason .= '<option value="' . $reject['rejection_reason_id'] . '">' . ($reject['rejection_reason_name']) . '</option>';
		}
	}
	$rejectionReason .= '</optgroup>';
}

?>
<style>
	.dataTables_wrapper {
		position: relative;
		clear: both;
		overflow-x: visible !important;
		overflow-y: visible !important;
		padding: 15px 0 !important;
	}

	.sampleType select {
		max-width: 100px;
		width: 100px !important
	}

	#rejectReasonDiv {
		border: 1px solid #ecf0f5;
		box-shadow: 3px 3px 15px #000;
		background-color: #ecf0f5;
		width: 50%;
		display: none;
		padding: 10px;
		border-radius: 10px;

	}

	.arrow-right {
		width: 0;
		height: 0;
		border-top: 15px solid transparent;
		border-bottom: 15px solid transparent;
		border-left: 15px solid #ecf0f5;
		position: absolute;
		left: 100%;
		top: 24px;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			<?= _translate("Imported Results"); ?>
		</h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
			<li class="active">Test Request</li>
		</ol>
	</section>
	<!-- for sample rejection -->
	<div id="rejectReasonDiv">
		<a href="javascript:void(0)" style="float:right;color:red;" title="close" onclick="hideReasonDiv('rejectReasonDiv')"><em class="fa-solid fa-xmark"></em></a>
		<div class="arrow-right"></div>
		<input type="hidden" name="statusDropDownId" id="statusDropDownId" />
		<h3 style="color:red;">
			<?= _translate("Choose Rejection Reason"); ?>
		</h3>
		<select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" onchange="updateRejectionReasonStatus(this);">
			<?php echo $rejectionReason; ?>
		</select>

	</div>
	<!-- Main content -->

	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<?php if (!$errorInImport) { ?>

						<div class="box-header without-border">
							<div class="box-header with-border">
								<ul style="list-style: none;float: right;">
									<li style="float:left;margin-right:40px;"><em class="fa-solid fa-exclamation-circle" style="color:#e8000b;"></em>
										<?= _translate("Sample ID not from VLSM"); ?>
									</li>
									<li style="float:left;margin-right:40px;"><em class="fa-solid fa-exclamation-circle" style="color:#FFC300;"></em>
										<?= _translate("Result already exists for this sample"); ?>
									</li>
									<li style="float:left;margin-right:40px;"><em class="fa-solid fa-exclamation-circle" style="color:#337ab7;"></em>
										<?= _translate("Result for Sample ID from VLSM"); ?>
									</li>
									<li style="float:left;margin-right:20px;"><em class="fa-solid fa-exclamation-circle" style="color:#E0B0FF;"></em>
										<?= _translate("Control/Calibrator"); ?>
									</li>
								</ul>
							</div>
						</div>
						<!-- /.box-header -->
						<div class="box-body">
							<div class="col-md-6 col-sm-6">
								<input type="button" onclick="acceptAllSamples();" value="<?= _translate("Accept All Samples"); ?>" class="btn btn-success btn-sm">
								<br><strong class="text-danger">
									<?= _translate("Only accepts samples that do not have status field already selected"); ?>
								</strong>
							</div>
							<table aria-describedby="table" id="vlRequestDataTable" class="table table-bordered table-striped" aria-hidden="true">
								<thead>
									<tr>
										<th style="width: 23%;">
											<?= _translate("Sample ID"); ?>
										</th>
										<th style="width: 11%;">
											<?= _translate("Sample Collection Date"); ?>
										</th>
										<th style="width: 10%;">
											<?= _translate("Sample Test Date"); ?>
										</th>
										<th style="width: 10%;">
											<?= _translate("Clinic/Site Name"); ?>
										</th>
										<th style="width: 10%;">
											<?= _translate("Batch Code"); ?>
										</th>
										<th style="width: 10%;">
											<?= _translate("Lot Number"); ?>
										</th>
										<th style="width: 10%;">
											<?= _translate("Lot Expiry Date"); ?>
										</th>
										<th style="width: 10%;">
											<?= _translate("Rejection Reason"); ?>
										</th>
										<th style="max-width: 9%;">
											<?= _translate("Sample Type"); ?>
										</th>
										<th style="width: 9%;">
											<?= _translate("Result"); ?>
										</th>
										<th style="width: 9%;">
											<?= _translate("Status"); ?>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="11" class="dataTables_empty">Loading data from server</td>
									</tr>
								</tbody>
							</table>
						</div>
						<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:30px;width: 100%;">
							<tr>
								<input type="hidden" name="checkedTests" id="checkedTests" />
								<input type="hidden" name="checkedTestsIdValue" id="checkedTestsIdValue" />
								<td style=" width: 30%; ">
									<strong>
										<?= _translate("Comments") ?>&nbsp;
									</strong>
									<textarea style="height: 34px;width: 100%;" class="form-control" id="comments" name="comments" placeholder="Comments"></textarea>
								</td>
								<td style=" width: 20%; ">
									<strong>
										<?= _translate("Tested By"); ?><span class="mandatory">*</span>&nbsp;
									</strong>
									<select name="testedBy" id="testedBy" class="select2 form-control" title="Please choose tested by" style="width: 100%;">
										<option value="">-- Select --</option>
										<?php
										foreach ($userResult as $uName) {
										?>
											<option value="<?php echo $uName['user_id']; ?>"><?= $uName['user_name']; ?></option>
										<?php
										}
										?>
									</select>
								</td>
								<td style=" width: 20%; ">
									<strong>
										<?= _translate("Reviewed By"); ?><span class="mandatory">*</span>&nbsp;
									</strong>
									<!--<input type="text" name="reviewedBy" id="reviewedBy" class="form-control" title="Please enter Reviewed By" placeholder ="Reviewed By"/>-->
									<select name="reviewedBy" id="reviewedBy" class="form-control" title="Please choose reviewed by" style="width: 100%;">
										<option value="">-- Select --</option>
										<?php
										foreach ($userResult as $uName) {
										?>
											<option value="<?php echo $uName['user_id']; ?>" <?php echo ($uName['user_id'] == $reviewBy) ? "selected=selected" : ""; ?>><?php echo ($uName['user_name']); ?></option>
										<?php
										}
										?>
									</select>
								</td>
								<td style=" width: 20%; ">
									<strong>
										<?= _translate("Approved By"); ?><span class="mandatory">*</span>&nbsp;
									</strong>
									<!--<input type="text" name="approvedBy" id="approvedBy" class="form-control" title="Please enter Approved By" placeholder ="Approved By"/>-->
									<select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by" style="width: 100%;">
										<option value="">-- Select --</option>
										<?php
										foreach ($userResult as $uName) {
										?>
											<option value="<?php echo $uName['user_id']; ?>" <?php echo ($uName['user_id'] == $approveBy) ? "selected=selected" : ""; ?>><?php echo ($uName['user_name']); ?></option>
										<?php
										}
										?>
									</select>
								</td>
								<td style=" width: 10%; ">
									<br>
									<input type="hidden" name="print" id="print" />
									<input type="hidden" name="module" id="module" value="<?= ($module); ?>" />
									<input type="button" onclick="submitTestStatus();" value="<?= _translate("Save"); ?>" class="btn btn-success btn-sm">
								</td>
							</tr>

						</table>
						<!-- /.box-body -->


					<?php } else { ?>
						<div class="box-body">
							<div class="col-md-12 col-sm-12">
								<br>
								<h3>
									<?= _translate("Either there were no records imported or you seem to have reached this page by mistake."); ?>
									<br><br>
									<?= _translate("Please contact technical support if you need assistance."); ?>
								</h3>
								<br>
								<input type="button" onclick="history.go(-1);" value="<?= _translate("Back"); ?>" class="btn btn-danger btn-sm">
							</div>
						</div>

					<?php } ?>
				</div>
				<!-- /.box -->
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>

	<!-- /.content -->
</div>

<script type="text/javascript">
	var startDate = "";
	var endDate = "";
	var selectedTests = [];
	var selectedTestsIdValue = [];
	$(document).ready(function() {
		$('#testedBy').select2({
			width: '100%',
			placeholder: "Select Tested By"
		});
		$('#reviewedBy').select2({
			width: '100%',
			placeholder: "Select Reviewed By"
		});
		$('#approvedBy').select2({
			width: '100%',
			placeholder: "Select Approved By"
		});
		loadVlRequestData();
	});

	var oTable = null;

	function loadVlRequestData() {
		$.blockUI();
		oTable = $('#vlRequestDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center sampleType",
					"bSortable": false
				},
				{
					"sClass": "center",
					"bSortable": false
				},
				{
					"sClass": "center sampleType",
					"bSortable": false
				},
			],
			"iDisplayLength": 100,
			"fnDrawCallback": function() {
				var oSettings = this.fnSettings();
				var iTotalRecords = oSettings.fnRecordsTotal();
				if (iTotalRecords == 0) {
					window.location.href = "/import-result/importedStatistics.php?t=<?= $module; ?>";
				}
			},
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "/import-result/getImportedResults.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "module",
					"value": '<?= $module; ?>'
				});
				$.ajax({
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": fnCallback,
				});
			},
		});
		$.unblockUI();
	}

	function toggleTest(obj, sampleCode) {
		if (sampleCode == '') {
			alert("Please enter sample id");
			$("#" + obj.id).val('');
			return false;
		}
		if (obj.value == '4') {
			var confrm = confirm("<?= _translate("Do you wish to overwrite this result?"); ?>");
			if (confrm) {
				var pos = $("#" + obj.id).offset();
				$("#rejectReasonDiv").show();
				$("#rejectReasonDiv").css({
					top: Math.round(pos.top) - 30,
					position: 'absolute',
					'z-index': 1,
					right: '15%'
				});
				$("#statusDropDownId").val(obj.id);
				$(".content").css('pointer-events', 'none');
				//return false;
			} else {
				$("#" + obj.id).val('');
				return false;
			}
		} else {
			$("#rejectReasonName" + obj.id).html('');
			$("#rejectReasonDiv").hide();
		}

	}

	function updateRejectionReasonStatus(obj) {
		var rejectDropDown = $("#statusDropDownId").val();
		if (obj.value != '') {
			$("#rejectReasonName" + rejectDropDown).html(
				$("#" + obj.id + " option:selected").text() +
				'<input type="hidden" id="rejectedReasonId' + rejectDropDown + '" name="rejectedReasonId[]" value="' + obj.value + '"/><a href="javascript:void(0)" style="float:right;color:red;" title="cancel" onclick="showRejectedReasonList(' + rejectDropDown + ');"><em class="fa-solid fa-xmark"></em></a>'
			);
		} else {
			$("#rejectedReasonId" + rejectDropDown).val('');
		}
	}

	function showRejectedReasonList(postionId) {
		var pos = $("#" + postionId).offset();
		$("#rejectReasonDiv").show();
		$("#rejectReasonDiv").css({
			top: Math.round(pos.top) - 30,
			position: 'absolute',
			'z-index': 1,
			right: '15%'
		});
		$("#statusDropDownId").val(postionId);
		$(".content").css('pointer-events', 'none');
	}


	function submitTestStatus() {

		if (!checkMissingSampleTestDate()) {
			return false;
		}

		var idArray = [];
		var statusArray = [];
		var rejectReasonArray = [];
		var somethingmissing = false;

		$('[name="status[]"]').each(function() {
			if ($(this).val() == null || $(this).val() == '') {
				//console.log($(this));
				somethingmissing = true;
			}
			idArray.push($(this).attr('id'));
			statusArray.push($(this).val());
			rejectReasonArray.push($("#rejectedReasonId" + $(this).attr('id')).val());
		});

		id = idArray.join();
		status = statusArray.join();
		rejectReasonId = rejectReasonArray.join();
		comments = $("#comments").val();
		testBy = $("#testedBy").val();
		appBy = $("#approvedBy").val();
		reviewedBy = $("#reviewedBy").val();
		moduleName = $("#module").val();
		globalValue = '<?= $arr["user_review_approve"]; ?>';
		if (appBy == reviewedBy && (reviewedBy != '' && appBy != '') && globalValue == 'yes') {
			conf = confirm("<?= _translate("Same person is reviewing and approving result! Do you want to continue?", true); ?>");
			if (conf) {} else {
				return false;
			}
		} else if (appBy == reviewedBy && (reviewedBy != '' && appBy != '') && globalValue == 'no') {
			alert("<?= _translate("Same person is reviewing and approving result. This is not allowed as per your system configuration.", true); ?>");
			return false;
		}

		if (somethingmissing == true) {
			alert("<?= _translate("Please ensure that you have updated the status of all the Controls and Samples"); ?> ");
			$.unblockUI();
			return false;
		}

		if (appBy != '' && somethingmissing == false && testBy != "" && reviewedBy != "") {
			conf = confirm("<?= _translate("Are you sure you want to continue?", true); ?>");
			if (conf) {
				$.blockUI();
				$.post("/import-result/processImportedResults.php", {
						rejectReasonId: rejectReasonId,
						value: id,
						status: status,
						comments: comments,
						testBy: testBy,
						appBy: appBy,
						module: moduleName,
						reviewedBy: reviewedBy,
						format: "html"
					},
					function(data) {
						$.unblockUI();
						if ($("#print").val() == 'print') {
							convertSearchResultToPdf('');
						}
						if (data == 'importedStatistics.php') {
							window.location.href = "/import-result/importedStatistics.php?t=<?= $module; ?>";
						}
						oTable.fnDraw();
						selectedTests = [];
						selectedTestsIdValue = [];
						$("#checkedTests").val('');
						$("#checkedTestsIdValue").val('');
						$("#comments").val('');
					});

			} else {
				oTable.fnDraw();
			}
		} else {
			alert("<?= _translate("Please ensure you have updated the status and the approved by and reviewed by and tested by field", true); ?>");
			return false;
		}
	}

	function submitTestStatusAndPrint() {
		$("#print").val('print');
		submitTestStatus();
	}

	function updateStatus(value, status) {
		if (status != '') {
			conf = confirm("<?= _translate("Do you wish to change the status?", true); ?>");
			if (conf) {
				$.blockUI();
				$.post("/import-result/processImportedResults.php", {
						value: value,
						status: status,
						format: "html"
					},
					function(data) {
						convertSearchResultToPdf('');
						oTable.fnDraw();
						selectedTests = [];
						selectedTestsId = [];
						$("#checkedTests").val('');
						$(".countChecksPending").html(0);
					});
				$.unblockUI();
			} else {
				oTable.fnDraw();
			}
		} else {
			alert("<?= _translate("Please select result status", true); ?>");
		}
	}

	function updateSampleCode(obj, oldSampleCode, tempsampleId) {
		$(obj).fastConfirm({
			position: "right",
			questionText: "<?= _translate("Are you sure you want to rename this Sample?", true); ?>",
			onProceed: function(trigger) {
				var pos = oTable.fnGetPosition(obj);
				$.blockUI();
				$.post("/import-result/updateImportedSample.php", {
						sampleCode: obj.value,
						tempsampleId: tempsampleId
					},
					function(data) {
						if (data == 0) {
							alert("<?= _translate("Something went wrong! Please try again", true); ?>");
							oTable.fnDraw();
						}
					});
				$.unblockUI();
			},
			onCancel: function(trigger) {
				$("#" + obj.id).val(oldSampleCode);
			}
		});
	}

	function updateBatchCode(obj, oldBatchCode, tempsampleId) {
		$(obj).fastConfirm({
			position: "right",
			questionText: "Are you sure you want to rename this Batch?",
			onProceed: function(trigger) {
				var pos = oTable.fnGetPosition(obj);
				$.blockUI();
				$.post("/import-result/updateImportedSample.php", {
						batchCode: obj.value,
						tempsampleId: tempsampleId
					},
					function(data) {
						if (data == 0) {
							alert("<?= _translate("Something went wrong! Please try again", true); ?>");
							oTable.fnDraw();
						}
					});
				$.unblockUI();
			},
			onCancel: function(trigger) {
				$("#" + obj.id).val(oldBatchCode);
			}
		});
	}

	function sampleToControl(obj, oldValue, tempsampleId) {
		$(obj).fastConfirm({
			position: "left",
			questionText: "<?= _translate("Are you sure you want to change this sample?", true); ?>",
			onProceed: function(trigger) {
				var pos = oTable.fnGetPosition(obj);
				$.blockUI();
				$.post("/import-result/updateImportedSample.php", {
						sampleType: obj.value,
						tempsampleId: tempsampleId
					},
					function(data) {
						if (data == 0) {
							alert("<?= _translate("Something went wrong! Please try again", true); ?>");
							oTable.fnDraw();
						}
					});
				$.unblockUI();
			},
			onCancel: function(trigger) {
				$("#" + obj.id).val(oldValue);
			}
		});
	}

	function sampleToControlAlert(number) {
		alert("<?= _translate("Maximum number of controls allowed as per the instrument configuration", true); ?> : " + number);
		oTable.fnDraw();
	}

	function hideReasonDiv(id) {
		$("#" + id).hide();
		$(".content").css('pointer-events', 'auto');
		if ($("#rejectionReason").val() == '') {
			$("#" + $("#statusDropDownId").val()).val('');
		}
	}

	function checkMissingSampleTestDate() {
		var missingTestDateFound = false;

		$('.missing-test-date-flag').each(function() {
			if ($(this).val() === '1') {
				missingTestDateFound = true;
				return false; // break loop
			}
		});

		if (missingTestDateFound) {
			alert("<?= _translate("Action Required: One or more samples do not have a test date. Please update all missing fields before submitting.", true); ?>");
			return false;
		}

		return true;
	}


	function acceptAllSamples() {

		if (!checkMissingSampleTestDate()) {
			return false;
		}
		conf = confirm("<?= _translate("Are you sure you want to mark all samples as Accepted?", true); ?>");
		if (conf) {
			$.blockUI();
			$.post("/import-result/updateAllSampleStatus.php", {},
				function(data) {
					oTable.fnDraw();
					$.unblockUI();
				});

		} else {
			oTable.fnDraw();
		}
	}
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
