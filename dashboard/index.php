<?php

$title = "Dashboard";

require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');


/* Total data set length */
$vlFormTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form");
// $aResultTotal = $countResult->fetch_row();
//print_r($aResultTotal);
$labCount = $vlFormTotal[0]['total'];

$facilityTotal =  $db->rawQuery("select COUNT(facility_id) as total FROM facility_details");
$facilityCount = $facilityTotal[0]['total'];

?>
<link rel="stylesheet" href="/assets/css/components-rounded.min.css">
<style>
	.bluebox,
	.dashboard-stat2 {
		border: 1px solid #3598DC;
	}

	.input-mini {
		width: 100% !important;
	}

	.labAverageTatDiv {
		display: none;
	}
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="bs-example bs-example-tabs">
			<ul id="myTab" class="nav nav-tabs" style="font-size:1.4em;">
				<?php if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {  ?>
					<li class="active"><a href="#vlDashboard" data-toggle="tab">Viral Load Tests</a></li>
				<?php } ?>
				<?php if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {  ?>
					<li><a href="#eidDashboard" data-toggle="tab" onclick="generateDashboard('eid');">EID Tests</a></li>
				<?php }
				if (isset($recencyConfig['vlsync']) && $recencyConfig['vlsync'] == true) {  ?>
					<li><a href="#recencyDashboard" data-toggle="tab" onclick="generateDashboard('recency')">Confirmation Tests for Recency</a></li>
				<?php }  ?>
			</ul>
			<div id="myTabContent" class="tab-content">

				<?php if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {  ?>
					<div class="tab-pane fade in active" id="vlDashboard">
						<!-- VL content -->
						<section class="content">
							<!-- Small boxes (Stat box) -->
							<div class="row" style="padding-top:10px;padding-bottom:20px;">
								<div class="col-lg-7">
									<form autocomplete="off">
										<table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
											<tr>
												<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
												<td>
													<input type="text" id="vlSampleCollectionDate" name="vlSampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
												</td>
												<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('vl');" value="Search" class="btn btn-success btn-sm">
													&nbsp;<button class="btn btn-danger btn-sm" onclick="resetSearchVlRequestData('vl');"><span>Reset</span></button>
												</td>
											</tr>
										</table>
									</form>
								</div>
							</div>
							<div class="row">
								<div id="vlSampleResultDetails"></div>
								<div class="box-body" id="vlNoOfSampleCount"></div>
								<div id="vlPieChartDiv"></div>
							</div>

							<!-- /.row -->
							<!-- Main row -->
							<!-- /.row (main row) -->

						</section>
						<!-- /. VL content -->
					</div>

				<?php } ?>

				<div class="tab-pane fade in" id="recencyDashboard">
					<!-- VL content -->
					<section class="content">
						<!-- Small boxes (Stat box) -->
						<div class="row" style="padding-top:10px;padding-bottom:20px;">
							<div class="col-lg-7">
								<form autocomplete="off">
									<table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
										<tr>
											<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
											<td>
												<input type="text" id="recencySampleCollectionDate" name="recencySampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
											</td>
											<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('recency')" value="Search" class="btn btn-success btn-sm">
												&nbsp;<button class="btn btn-danger btn-sm" onclick="resetSearchVlRequestData('recency');"><span>Reset</span></button>
											</td>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<div class="row">
							<div id="recencySampleResultDetails"></div>
							<div class="box-body" id="recencyNoOfSampleCount"></div>
							<div id="recencyPieChartDiv"></div>
						</div>

						<!-- /.row -->
						<!-- Main row -->
						<!-- /.row (main row) -->

					</section>
					<!-- /. VL content -->
				</div>

				<?php if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {  ?>

				<div class="tab-pane fade in" id="eidDashboard">
					<!-- EID content -->
					<section class="content">
						<!-- Small boxes (Stat box) -->
						<div class="row" style="padding-top:10px;padding-bottom:20px;">
							<div class="col-lg-7">
								<form autocomplete="off">
									<table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
										<tr>
											<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
											<td>
												<input type="text" id="eidSampleCollectionDate" name="eidSampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
											</td>
											<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('eid')" value="Search" class="btn btn-success btn-sm">
												&nbsp;<button class="btn btn-danger btn-sm" onclick="resetSearchVlRequestData('eid');"><span>Reset</span></button>
											</td>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<div class="row">
							<div id="eidSampleResultDetails"></div>
							<div class="box-body" id="eidNoOfSampleCount"></div>
							<div id="eidPieChartDiv"></div>
						</div>
						<!-- /.row -->
						<!-- Main row -->
						<!-- /.row (main row) -->
					</section>
					<!-- /. EID content -->
				</div>

				<?php } ?>

			</div>
		</div>
	</section>
</div>
<script type="text/javascript" src="/assets/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/assets/js/highcharts.js"></script>
<script>
	$(function() {


		$('#vlSampleCollectionDate,#eidSampleCollectionDate,#recencySampleCollectionDate').daterangepicker({
				format: 'DD-MMM-YYYY',
				separator: ' to ',
				startDate: moment().subtract(30, 'days'),
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

		// by default we pass 'vl' because that is first tab
		generateDashboard('vl');
	});

	function generateDashboard(requestType) {
		searchVlRequestData(requestType);
		getNoOfSampleCount(requestType);
		getSamplesOverview(requestType);
	}

	function searchVlRequestData(requestType) {
		$.blockUI();
		if (requestType == 'vl') {
			$.post("getSampleResult.php", {
					sampleCollectionDate: $("#vlSampleCollectionDate").val(),
					type: 'vl'
				},
				function(data) {
					if (data != '') {
						$("#vlSampleResultDetails").html(data);
					}
				});
		} else if (requestType == 'recency') {
			$.post("getSampleResult.php", {
					sampleCollectionDate: $("#recencySampleCollectionDate").val(),
					type: 'recency'
				},
				function(data) {
					if (data != '') {
						$("#recencySampleResultDetails").html(data);
					}
				});
		} else if (requestType == 'eid') {
			$.post("getSampleResult.php", {
					sampleCollectionDate: $("#eidSampleCollectionDate").val(),
					type: 'eid'
				},
				function(data) {
					if (data != '') {
						$("#eidSampleResultDetails").html(data);
					}
				});
		}
		$.unblockUI();

	}

	function getNoOfSampleCount(requestType) {
		$.blockUI();
		if (requestType == 'vl') {
			$.post("getSampleCount.php", {
					sampleCollectionDate: $("#vlSampleCollectionDate").val(),
					type: 'vl'
				},
				function(data) {
					if (data != '') {
						$("#vlNoOfSampleCount").html(data);
					}
				});
		} else if (requestType == 'recency') {
			$.post("getSampleCount.php", {
					sampleCollectionDate: $("#recencySampleCollectionDate").val(),
					type: 'recency'
				},
				function(data) {
					if (data != '') {
						$("#recencyNoOfSampleCount").html(data);
					}
				});
		} else if (requestType == 'eid') {
			$.post("getSampleCount.php", {
					sampleCollectionDate: $("#eidSampleCollectionDate").val(),
					type: 'eid'
				},
				function(data) {
					if (data != '') {
						$("#eidNoOfSampleCount").html(data);
					}
				});
		}
		$.unblockUI();
	}

	function getSamplesOverview(requestType) {
		$.blockUI();
		if (requestType == 'vl') {
			$.post("/program-management/getSampleStatus.php", {
					sampleCollectionDate: $("#vlSampleCollectionDate").val(),
					batchCode: '',
					facilityName: '',
					sampleType: '',
					type: 'vl'
				},
				function(data) {
					if ($.trim(data) != '') {
						$("#vlPieChartDiv").html(data);
						$(".labAverageTatDiv").css("display", "none");
					}
				});
		} else if (requestType == 'recency') {
			$.post("/program-management/getSampleStatus.php", {
					sampleCollectionDate: $("#recencySampleCollectionDate").val(),
					batchCode: '',
					facilityName: '',
					sampleType: '',
					type: 'recency'
				},
				function(data) {
					if ($.trim(data) != '') {
						$("#recencyPieChartDiv").html(data);
						$(".labAverageTatDiv").css("display", "none");
					}
				});
		} else if (requestType == 'eid') {
			$.post("/eid/management/getSampleStatus.php", {
					sampleCollectionDate: $("#eidSampleCollectionDate").val(),
					batchCode: '',
					facilityName: '',
					sampleType: '',
					type: 'eid'
				},
				function(data) {
					if ($.trim(data) != '') {
						$("#eidPieChartDiv").html(data);
						$(".labAverageTatDiv").css("display", "none");
					}
				});
		}
		$.unblockUI();
	}

	function resetSearchVlRequestData(requestType) {
		$('#vlSampleCollectionDate,#eidSampleCollectionDate,#recencySampleCollectionDate').daterangepicker({
				format: 'DD-MMM-YYYY',
				separator: ' to ',
				startDate: moment().subtract(30, 'days'),
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
		generateDashboard(requestType);
	}
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>