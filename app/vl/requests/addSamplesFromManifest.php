<?php
$title = _translate("Add Samples from Manifest");

require_once APPLICATION_PATH . '/header.php';

$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$configFormQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
$sQuery = "SELECT * FROM r_vl_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);
$fQuery = "SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$batQuery = "SELECT batch_code FROM batch_details where test_type = 'vl' AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
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
		<h1><em class="fa-solid fa-plus"></em>
			<?php echo _translate("Add Samples from Manifest"); ?>
		</h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em>
					<?php echo _translate("Home"); ?>
				</a></li>
			<li class="active">
				<?php echo _translate("Test Request"); ?>
			</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width: 98%;margin-bottom: 0px;display: block;">
						<tr>
							<td style="width:20%;vertical-align:middle;"><strong>
									<?php echo _translate("Enter Sample Manifest Code"); ?> :
								</strong></td>
							<td style="width:70%;vertical-align:middle;">
								<input type="text" id="samplePackageCode" name="samplePackageCode" class="form-control" placeholder="<?php echo _translate('Sample Manifest Code'); ?>" title="<?php echo _translate('Please enter the sample manifest code'); ?>" style="background:#fff;" />
								<input type="hidden" id="sampleId" name="sampleId" />
							</td>
							<td style="width:10%;">
								<button class="btn btn-primary btn-sm pull-right" style="margin-right:5px;" onclick="getSampleCode();return false;"><span>
										<?php echo _translate("Submit"); ?>
									</span></button>
							</td>
						</tr>
						<tr class="activateSample" style="display:none;">
							<th scope="row" style="width:20%;vertical-align:middle;">
								<?php echo _translate("Sample Received at Testing Lab"); ?> :
							</th>
							<td style="width:70%;vertical-align:middle;"><input type="text" name="testDate" id="testDate" class="form-control dateTime" placeholder="Sample Received at Testing Lab" title="Please select when the samples were received at the Testing Lab" readonly />
							</td>

							<td style="width:10%;">
								<a class="btn btn-success btn-sm pull-right" style="margin-right:5px;" href="javascript:void(0);" onclick="activateSamplesFromManifest();"><em class="fa-solid fa-square-check"></em> Activate Samples</a>
							</td>
						</tr>
					</table>
					<!-- /.box-header -->
					<div class="box-body table-responsive">
						<table aria-describedby="table" id="vlManifestDataTable" class="table table-bordered table-striped table-vcenter">
							<thead>
								<tr>
									<th>
										<?php echo _translate("Sample ID"); ?>
									</th>
									<?php if ($_SESSION['instanceType'] != 'standalone') { ?>
										<th>
											<?php echo _translate("Remote Sample ID"); ?>
										</th>
									<?php } ?>
									<th>
										<?php echo _translate("Sample Collection Date"); ?>
									</th>
									<th>
										<?php echo _translate("Batch Code"); ?>
									</th>
									<th>
										<?php echo _translate("Unique ART No"); ?>
									</th>
									<th>
										<?php echo _translate("Patient's Name"); ?>
									</th>
									<th scope="row">
										<?php echo _translate("Facility Name"); ?>
									</th>
									<th>
										<?php echo _translate("Province/State"); ?>
									</th>
									<th>
										<?php echo _translate("District/County"); ?>
									</th>
									<th>
										<?php echo _translate("Sample Type"); ?>
									</th>
									<th>
										<?php echo _translate("Result"); ?>
									</th>
									<th>
										<?php echo _translate("Last Modified Date"); ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="13" class="dataTables_empty" style="text-align:center;">
										<?php echo _translate("Please enter the manifest code then submit!", true); ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

				</div>
				<!-- /.box -->

			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
<script type="text/javascript" src="/assets/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>

<script type="text/javascript">
	var oTable = null;

	function loadVlRequestData() {
		$.blockUI();
		if (oTable) {
			$("#vlManifestDataTable").dataTable().fnDestroy();
		}

		oTable = $('#vlManifestDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,
			//"bDestroy": true,
			"bStateSave": true,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
				},
				<?php if ($_SESSION['instanceType'] != 'standalone') { ?> {
						"sClass": "center"
					},
				<?php } ?> {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}, {
					"sClass": "center"
				}
			],
			"aaSorting": [
				[<?php echo ($sarr['sc_user_type'] == 'remoteuser' || $sarr['sc_user_type'] == 'vluser') ? 11 : 10 ?>, "desc"]
			],
			"fnDrawCallback": function() {
				var checkBoxes = document.getElementsByName("chk[]");
				len = checkBoxes.length;
				for (c = 0; c < len; c++) {
					if (jQuery.inArray(checkBoxes[c].id, selectedTestsId) != -1) {
						checkBoxes[c].setAttribute("checked", true);
					}
				}
			},
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "/vl/requests/getManifestInGridHelper.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "samplePackageCode",
					"value": $("#samplePackageCode").val()
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

	function getSampleCode() {
		if ($("#samplePackageCode").val() != "") {
			$.blockUI();
			loadVlRequestData();
			$.post("/vl/requests/getRemoteManifestHelper.php", {
					samplePackageCode: $("#samplePackageCode").val()
				},
				function(data) {
					$.unblockUI();
					console.log(data);
					if (data != "") {
						$('.activateSample').show();
						$('#sampleId').val(data);
					} else {
						<?php if (isset($_SESSION['instanceType']) && $_SESSION['instanceType'] == 'vluser') { ?>
							forceSyncRequestsByManifestCode($("#samplePackageCode").val(), 'vl');
						<?php } ?>
					}
				});
		} else {
			alert('<?php echo _translate("Please enter the Sample Manifest Code then hit submit", true); ?>');
		}
	}

	/* Remote Syn only package code matches */
	<?php if (isset($_SESSION['instanceType']) && $_SESSION['instanceType'] == 'vluser') { ?>
		var remoteUrl = '<?php echo SYSTEM_CONFIG['remoteURL']; ?>';

		function forceSyncRequestsByManifestCode(manifestCode, forceSyncModule) {
			$.blockUI({
				message: '<h3><?php echo _translate("Trying to sync manifest", true); ?><br><?php echo _translate("Please wait", true); ?>...</h3>'
			});

			if (remoteSync && remoteUrl != null && remoteUrl != '') {
				var jqxhr = $.ajax({
						url: "/scheduled-jobs/remote/requestsSync.php?manifestCode=" + manifestCode + "&forceSyncModule=" + forceSyncModule,
					})
					.done(function(data) {
						//console.log(data);
						//alert( "success" );
					})
					.fail(function() {
						$.unblockUI();
						// alert("Unable to do STS Sync. Please contact technical team for assistance.");
					})
					.always(function() {
						$.unblockUI();
						$.post("/vl/requests/getRemoteManifestHelper.php", {
								samplePackageCode: $("#samplePackageCode").val()
							},
							function(data) {
								$.unblockUI();
								if (data != "") {
									$('.activateSample').show();
									$('#sampleId').val(data);
									oTable.fnDraw();
								}
							});
					});
			}
		}
	<?php } ?>

	function activateSamplesFromManifest() {
		if ($("#testDate").val() == "") {
			alert("<?= _translate("Please select when the samples were received at the Testing Lab", true); ?>");
			return false;
		}
		$.blockUI();
		$.post("/vl/requests/activate-samples-from-manifest.php", {
				sampleId: $("#sampleId").val(),
				testDate: $("#testDate").val()
			},
			function(data) {
				if (data > 0) {
					alert('<?php echo _translate("Samples from this Manifest have been activated", true); ?>');
					$('.activateSample').hide();
				}
				oTable.fnDraw();
				$.unblockUI();
			});
	}
	$(document).ready(function() {
		$('.dateTime').datetimepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
			timeFormat: "HH:mm",
			maxDate: "Today",
			onSelect: function(date) {
				var dt2 = $('#sampleDispatchedDate');
				var startDate = $(this).datetimepicker('getDate');
				var minDate = $(this).datetimepicker('getDate');
				////dt2.datetimepicker('setDate', minDate);
				startDate.setDate(startDate.getDate() + 1000000);
				dt2.datetimepicker('option', 'maxDate', "Today");
				dt2.datetimepicker('option', 'minDate', minDate);
				dt2.datetimepicker('option', 'minDateTime', minDate);
			}
		});
	});
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
