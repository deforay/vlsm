<?php

use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

$title = _translate("Manual EID Results");

require_once APPLICATION_PATH . '/header.php';


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);


// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_COOKIE = _sanitizeInput($request->getCookieParams());

$healthFacilites = $facilitiesService->getHealthFacilities('eid');
$testingLabs = $facilitiesService->getTestingLabs('eid');
$testingLabsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");
$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");

$batQuery = "SELECT batch_code FROM batch_details
				WHERE test_type = 'eid'
				AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
//check filters
$collectionDate = '';
$batchCode = '';
$sampleType = '';
$facilityName = [];
$gender = '';
$status = 'no_result';
$lastUrl1 = '';
$lastUrl2 = '';
?>
<style>
	.select2-selection__choice {
		color: black !important;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-list-check"></em>
			<?php echo _translate("Manual EID Results"); ?>
		</h1>
		<ol class="breadcrumb">
			<li><a href="/dashboard/index.php"><em class="fa-solid fa-chart-pie"></em>
					<?php echo _translate("Home"); ?>
				</a></li>
			<li class="active">
				<?php echo _translate("Manual EID Results"); ?>
			</li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;margin-bottom: 0px;">
						<tr>
							<td><strong>
									<?php echo _translate("Sample Collection Date"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="<?php echo _translate('Select Collection Date'); ?>" readonly style="width:220px;background:#fff;" value="<?php echo $collectionDate; ?>" />
							</td>
							<td>&nbsp;<strong>
									<?php echo _translate("Batch Code"); ?>&nbsp;:
								</strong></td>
							<td>
								<select class="form-control" id="batchCode" name="batchCode" title="<?php echo _translate('Please select batch code'); ?>" style="width:220px;">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<?php
									foreach ($batResult as $code) {
									?>
										<option value="<?php echo $code['batch_code']; ?>" <?php echo ($batchCode == $code['batch_code']) ? "selected='selected'" : "" ?>><?php echo $code['batch_code']; ?></option>
									<?php
									}
									?>
								</select>
							</td>
							<td><strong>
									<?php echo _translate("Facility Name"); ?> :
								</strong></td>
							<td>
								<select class="form-control" id="facilityName" name="facilityName" title="<?php echo _translate('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
									<?= $facilitiesDropdown; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Testing Lab"); ?> :
								</strong></td>
							<td>
								<select class="form-control" id="vlLab" name="vlLab" title="<?php echo _translate('Please select Testing Lab'); ?>" style="width:220px;">
									<?= $testingLabsDropdown; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _translate("Search"); ?>" class="btn btn-default btn-sm">
								&nbsp;<button class="btn btn-danger btn-sm" onclick="reset();"><span>
										<?= _translate('Reset'); ?>
									</span></button>
								&nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span>
										<?php echo _translate("Manage Columns"); ?>
									</span></button>
							</td>
						</tr>
					</table>
					<span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
						<div class="row" style="background:#e0e0e0;padding: 15px;margin-top: -5px;">
							<div class="col-md-12">
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code" class="showhideCheckBox" /> <label for="iCol0">
										<?php echo _translate("Sample ID"); ?>
									</label>
								</div>
								<?php $i = 0;
								if (!$general->isStandaloneInstance()) {
									$i = 1; ?>
									<div class="col-md-3">
										<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i; ?>" id="iCol<?php echo $i; ?>" data-showhide="remote_sample_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Remote Sample ID"); ?></label>
									</div>
								<?php } ?>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Batch Code"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_art_no" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Art No"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_first_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Patient's Name"); ?></label> <br>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="facility_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Facility Name"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Sample Type"); ?></label> <br>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="result" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Result"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="modified_on" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Modified On"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="status_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Status"); ?></label>
								</div>

							</div>
						</div>
					</span>
					<div class="box-body">
						<div class="">
							<select name="status" id="status" class="form-control" title="<?php echo _translate('Please choose result status'); ?>" style="width:220px;margin-top:30px;" onchange="searchVlRequestData();">
								<option value="">
									<?php echo _translate("-- Select --"); ?>
								</option>
								<option value="no_result" <?php echo ($status == 'no_result') ? "selected='selected'" : "" ?>><?php echo _translate("Results Not Recorded"); ?></option>
								<option value="result" <?php echo ($status == 'result') ? "selected='selected'" : "" ?>>
									<?php echo _translate("Results Recorded"); ?></option>
								<option value="reject" <?php echo ($status == 'reject') ? "selected='selected'" : "" ?>>
									<?php echo _translate("Rejected Samples"); ?></option>
							</select>
						</div>

						<br>

						<table aria-describedby="table" id="vlRequestDataTable" class="table table-bordered table-striped" aria-hidden="true">
							<thead>
								<tr>
									<th>
										<?php echo _translate("Sample ID"); ?>
									</th>
									<?php if (!$general->isStandaloneInstance()) { ?>
										<th>
											<?php echo _translate("Remote Sample ID"); ?>
										</th>
									<?php } ?>
									<th>
										<?php echo _translate("Batch Code"); ?>
									</th>
									<th scope="row">
										<?php echo _translate("Facility Name"); ?>
									</th>
									<th>
										<?php echo _translate("Child's ID"); ?>
									</th>
									<th>
										<?php echo _translate("Child's Name"); ?>
									</th>
									<th>
										<?php echo _translate("Mother's ID"); ?>
									</th>
									<th>
										<?php echo _translate("Mother's Name"); ?>
									</th>
									<th>
										<?php echo _translate("Result"); ?>
									</th>
									<th>
										<?php echo _translate("Modified On"); ?>
									</th>
									<th scope="row">
										<?php echo _translate("Status"); ?>
									</th>
									<th>
										<?php echo _translate("Action"); ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="10" class="dataTables_empty">
										<?php echo _translate("Loading data from server"); ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
<script src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">
	var startDate = "";
	var endDate = "";
	var selectedTests = [];
	var selectedTestsId = [];
	var oTable = null;
	$(document).ready(function() {
		$("#facilityName").select2({
			placeholder: "<?php echo _translate("Select Facilities"); ?>"
		});
		$("#vlLab").select2({
			placeholder: "<?php echo _translate("Select Testing Lab"); ?>"
		});
		$('#sampleCollectionDate').daterangepicker({
				locale: {
					cancelLabel: "<?= _translate("Clear", true); ?>",
					format: 'DD-MMM-YYYY',
					separator: ' to ',
				},
				showDropdowns: true,
				alwaysShowCalendars: false,
				startDate: moment().subtract(28, 'days'),
				endDate: moment(),
				maxDate: moment(),
				ranges: {
					'Today': [moment(), moment()],
					'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			},
			function(start, end) {
				startDate = start.format('YYYY-MM-DD');
				endDate = end.format('YYYY-MM-DD');
			});
		<?php
		if (!isset($_COOKIE['collectionDate']) || $_COOKIE['collectionDate'] == '') {
		?>
			$('#sampleCollectionDate').val("");
		<?php
		} else if (isset($_COOKIE['collectionDate'])) { ?>
			$('#sampleCollectionDate').val("<?= ($_COOKIE['collectionDate']); ?>");
		<?php } ?>

		loadVlRequestData();
		$(".showhideCheckBox").change(function() {
			if ($(this).attr('checked')) {
				idpart = $(this).attr('data-showhide');
				$("#" + idpart + "-sort").show();
			} else {
				idpart = $(this).attr('data-showhide');
				$("#" + idpart + "-sort").hide();
			}
		});

		$("#showhide").hover(function() {}, function() {
			$(this).fadeOut('slow')
		});
		var i = '<?php echo $i; ?>';
		for (colNo = 0; colNo <= i; colNo++) {
			$("#iCol" + colNo).attr("checked", oTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
			if (oTable.fnSettings().aoColumns[colNo].bVisible) {
				$("#iCol" + colNo + "-sort").show();
			} else {
				$("#iCol" + colNo + "-sort").hide();
			}
		}
	});

	function fnShowHide(iCol) {
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, bVis ? false : true);
	}

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
			//"bStateSave" : true,
			"iDisplayLength": 100,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
				},
				<?php if (!$general->isStandaloneInstance()) { ?> {
						"sClass": "center"
					},
				<?php } ?> {
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				{
					"sClass": "center",
					"bSortable": false
				}
			],
			"aaSorting": [
				[<?= (!$general->isStandaloneInstance()) ? 9 : 8; ?>, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "eid-samples-for-manual-result-entry.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "batchCode",
					"value": $("#batchCode").val()
				});
				aoData.push({
					"name": "sampleCollectionDate",
					"value": $("#sampleCollectionDate").val()
				});
				aoData.push({
					"name": "facilityName",
					"value": $("#facilityName").val()
				});
				aoData.push({
					"name": "vlLab",
					"value": $("#vlLab").val()
				});
				aoData.push({
					"name": "status",
					"value": $("#status").val()
				});

				aoData.push({
					"name": "from",
					"value": "enterresult"
				});
				$.ajax({
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": fnCallback
				});
			}
		});
		$.unblockUI();
	}

	function searchVlRequestData() {
		$.blockUI();
		oTable.fnDraw();
		document.cookie = "collectionDate=" + $("#sampleCollectionDate").val();
		document.cookie = "batchCode=" + $("#batchCode").val();
		document.cookie = "sampleType=" + $("#sampleType").val();
		document.cookie = "facilityName=" + $("#facilityName").val();
		document.cookie = "gender=" + $("#gender").val();
		document.cookie = "status=" + $("#status").val();
		$.unblockUI();
	}

	function reset() {
		document.cookie = "collectionDate=";
		document.cookie = "batchCode=";
		document.cookie = "sampleType=";
		document.cookie = "facilityName=";
		document.cookie = "gender=";
		document.cookie = "status=";
		window.location.reload();
	}
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
