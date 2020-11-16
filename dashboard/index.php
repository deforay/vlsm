<?php

$title = "Dashboard";

#require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');


/* Total data set length */
$vlFormTotal =  $db->rawQuery("SELECT COUNT(vl_sample_id) as total FROM vl_request_form");
// $aResultTotal = $countResult->fetch_row();
//print_r($aResultTotal);
$labCount = $vlFormTotal[0]['total'];

$facilityTotal =  $db->rawQuery("SELECT COUNT(facility_id) as total FROM facility_details");
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
					<li class="active"><a href="#vlDashboard" data-toggle="tab" onclick="generateDashboard('vl');">Viral Load Tests</a></li>
				<?php } ?>
				<?php if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {  ?>
					<li><a href="#eidDashboard" data-toggle="tab" onclick="generateDashboard('eid');">EID Tests</a></li>
				<?php } ?>
				<?php if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {  ?>
					<li><a href="#covid19Dashboard" data-toggle="tab" onclick="generateDashboard('covid19');">Covid-19 Tests</a></li>
				<?php } ?>
				<?php
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
										<table class="table searchTable" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
											<tr>
												<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
												<td>
													<input type="text" id="vlSampleCollectionDate" name="vlSampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
												</td>
												<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('vl');" value="Search" class="searchBtn btn btn-success btn-sm">
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
							<div class="box">
								<div class="box-body">
								<center> <h3 style="font-weight: 700;">Vl Testing Target </h3> </center>
									<table id="vlMonitoringTable" class="table table-bordered table-striped">
									<thead>
										<tr>
										<th>Facility Name</th>
										<th>Month </th>
										<th> Number of Samples Received </th>
										<th> Number of Samples Rejected </th>
										<th>Number of Samples Tested</th>
										<th>Monthly Test Target</th>
										</tr>
									</thead>
									<tbody>
										<tr>
										<td colspan="10" class="dataTables_empty">Loading data from server</td>
										</tr>
									</tbody>
									</table>
								</div>
							</div>
							<!-- /.box-body -->
							<!-- /.row -->
							<!-- Main row -->
							<!-- /.row (main row) -->

						</section>
						<!-- /. VL content -->
					</div>

				<?php } ?>

				<?php if (isset($recencyConfig['vlsync']) && $recencyConfig['vlsync'] == true) {  ?>
					<div class="tab-pane fade in" id="recencyDashboard">
						<!-- VL content -->
						<section class="content">
							<!-- Small boxes (Stat box) -->
							<div class="row" style="padding-top:10px;padding-bottom:20px;">
								<div class="col-lg-7">
									<form autocomplete="off">
										<table class="table searchTable" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
											<tr>
												<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
												<td>
													<input type="text" id="recencySampleCollectionDate" name="recencySampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
												</td>
												<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('recency')" value="Search" class="searchBtn btn btn-success btn-sm">
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
				<?php } ?>
				<!-- EID START-->
				<?php if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {  ?>

					<div class="tab-pane fade in" id="eidDashboard">
						<!-- EID content -->
						<section class="content">
							<!-- Small boxes (Stat box) -->
							<div class="row" style="padding-top:10px;padding-bottom:20px;">
								<div class="col-lg-7">
									<form autocomplete="off">
										<table class="table searchTable" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
											<tr>
												<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
												<td>
													<input type="text" id="eidSampleCollectionDate" name="eidSampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
												</td>
												<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('eid')" value="Search" class="searchBtn btn btn-success btn-sm">
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
							<div class="box">
								<div class="box-body">
								<center> <h3 style="font-weight: 700;">Eid Testing Target </h3> </center>
									<table id="eidMonitoringTable" class="table table-bordered table-striped">
									<thead>
										<tr>
										<th>Facility Name</th>
										<th>Month </th>
										<th> Number of Samples Received </th>
										<th> Number of Samples Rejected </th>
										<th>Number of Samples Tested</th>
										<th>Monthly Test Target</th>
										</tr>
									</thead>
									<tbody>
										<tr>
										<td colspan="10" class="dataTables_empty">Loading data from server</td>
										</tr>
									</tbody>
									</table>
								</div>
							</div>
							<!-- /.box-body -->
							<!-- /.row -->
							<!-- Main row -->
							<!-- /.row (main row) -->
						</section>
						<!-- /. EID content -->
					</div>

				<?php } ?>
				<!-- EID END -->
				<!-- COVID-19 START-->
				<?php if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {  ?>

					<div class="tab-pane fade in" id="covid19Dashboard">
						<!-- COVID-19 content -->
						<section class="content">
							<!-- Small boxes (Stat box) -->
							<div class="row" style="padding-top:10px;padding-bottom:20px;">
								<div class="col-lg-7">
									<form autocomplete="off">
										<table class="table searchTable" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:0px;width: 98%;margin-bottom: 0px;">
											<tr>
												<td style="vertical-align:middle;"><b>Date Range&nbsp;:</b></td>
												<td>
													<input type="text" id="covid19SampleCollectionDate" name="covid19SampleCollectionDate" class="form-control" placeholder="Select Collection Date" style="width:220px;background:#fff;" />
												</td>
												<td colspan="3">&nbsp;<input type="button" onclick="generateDashboard('covid19')" value="Search" class="searchBtn btn btn-success btn-sm">
													&nbsp;<button class="btn btn-danger btn-sm" onclick="resetSearchVlRequestData('covid19');"><span>Reset</span></button>
												</td>
											</tr>
										</table>
									</form>
								</div>
							</div>
							<div class="row">
								<div id="covid19SampleResultDetails"></div>
								<div class="box-body" id="covid19NoOfSampleCount"></div>
								<div id="covid19PieChartDiv"></div>
							</div>
							<div class="box">
								<div class="box-body">
								<center> <h3 style="font-weight: 700;">Covid-19 Testing Target </h3> </center>
									<table id="covid19MonitoringTable" class="table table-bordered table-striped">
									<thead>
										<tr>
										<th>Facility Name</th>
										<th>Month </th>
										<th> Number of Samples Received </th>
										<th> Number of Samples Rejected </th>
										<th>Number of Samples Tested</th>
										<th>Monthly Test Target</th>
										</tr>
									</thead>
									<tbody>
										<tr>
										<td colspan="10" class="dataTables_empty">Loading data from server</td>
										</tr>
									</tbody>
									</table>
								</div>
							</div>
							<!-- /.box-body -->
							<!-- /.row -->
							<!-- Main row -->
							<!-- /.row (main row) -->
						</section>
						<!-- /. COVID-19 content -->
					</div>

				<?php } ?>
				<!-- COVID-19 END -->
			</div>
		</div>
	</section>
</div>
<script type="text/javascript" src="/assets/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/assets/js/highcharts.js"></script>
<script>
	$(function() {

		$("#myTab li:first-child").addClass("active");
		$("#myTabContent div:first-child").addClass("active");
		$("#myTabContent div:first-child table.searchTable .searchBtn").trigger("click");


		$('#vlSampleCollectionDate,#eidSampleCollectionDate,#covid19SampleCollectionDate,#recencySampleCollectionDate').daterangepicker({
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
		//generateDashboard('vl');
	});

	function generateDashboard(requestType) {
		searchVlRequestData(requestType);
		getNoOfSampleCount(requestType);
		getSamplesOverview(requestType);
		if(requestType == 'eid')
			loadEidRequestData();
		else if(requestType == 'vl')
			loadVlRequestData();
		else if(requestType == 'covid19')
			loadCovid19RequestData();
	}

	function searchVlRequestData(requestType) {
		$.blockUI();
		if (requestType == 'vl') {
			$.post("/dashboard/getSampleResult.php", {
					sampleCollectionDate: $("#vlSampleCollectionDate").val(),
					type: 'vl'
				},
				function(data) {
					if (data != '') {
						$("#vlSampleResultDetails").html(data);
					}
				});
		} else if (requestType == 'recency') {
			$.post("/dashboard/getSampleResult.php", {
					sampleCollectionDate: $("#recencySampleCollectionDate").val(),
					type: 'recency'
				},
				function(data) {
					if (data != '') {
						$("#recencySampleResultDetails").html(data);
					}
				});
		} else if (requestType == 'eid') {
			$.post("/dashboard/getSampleResult.php", {
					sampleCollectionDate: $("#eidSampleCollectionDate").val(),
					type: 'eid'
				},
				function(data) {
					if (data != '') {
						$("#eidSampleResultDetails").html(data);
					}
				});
		} else if (requestType == 'covid19') {
			$.post("/dashboard/getSampleResult.php", {
					sampleCollectionDate: $("#covid19SampleCollectionDate").val(),
					type: 'covid19'
				},
				function(data) {
					if (data != '') {
						$("#covid19SampleResultDetails").html(data);
					}
				});
		}
		$.unblockUI();

	}

	function getNoOfSampleCount(requestType) {
		$.blockUI();
		if (requestType == 'vl') {
			$.post("/dashboard/getSampleCount.php", {
					sampleCollectionDate: $("#vlSampleCollectionDate").val(),
					type: 'vl'
				},
				function(data) {
					if (data != '') {
						$("#vlNoOfSampleCount").html(data);
					}
				});
		} else if (requestType == 'recency') {
			$.post("/dashboard/getSampleCount.php", {
					sampleCollectionDate: $("#recencySampleCollectionDate").val(),
					type: 'recency'
				},
				function(data) {
					if (data != '') {
						$("#recencyNoOfSampleCount").html(data);
					}
				});
		} else if (requestType == 'eid') {
			$.post("/dashboard/getSampleCount.php", {
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
			$.post("/vl/program-management/getSampleStatus.php", {
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
			$.post("/vl/program-management/getSampleStatus.php", {
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
		} else if (requestType == 'covid19') {
			$.post("/covid-19/management/getSampleStatus.php", {
					sampleCollectionDate: $("#covid19SampleCollectionDate").val(),
					batchCode: '',
					facilityName: '',
					sampleType: '',
					type: 'covid19'
				},
				function(data) {
					if ($.trim(data) != '') {
						$("#covid19PieChartDiv").html(data);
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
	function loadEidRequestData() {
    $.blockUI();
    oTable = $('#eidMonitoringTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,
      //"bStateSave" : true,
      "iDisplayLength": 25,
      "bRetrieve": true,
      "aoColumns": [{
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
      ],
      "aaSorting": [
        [0, "asc"]
      ],
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "/eid/management/getEidMonthlyThresholdReport.php",
      "fnServerData": function(sSource, aoData, fnCallback) {
		aoData.push({
          "name": "targetType",
          "value": '1'
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
  function loadVlRequestData() {
    $.blockUI();
    oTable = $('#vlMonitoringTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,
      //"bStateSave" : true,
      "iDisplayLength": 25,
      "bRetrieve": true,
      "aoColumns": [{
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
      ],
      "aaSorting": [
        [0, "asc"]
      ],
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "/vl/program-management/getVlMonthlyThresholdReport.php",
      "fnServerData": function(sSource, aoData, fnCallback) {
		aoData.push({
          "name": "targetType",
          "value": '1'
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

  function loadCovid19RequestData() {
    $.blockUI();
    oTable = $('#covid19MonitoringTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,
      //"bStateSave" : true,
      "iDisplayLength": 25,
      "bRetrieve": true,
      "aoColumns": [{
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
      ],
      "aaSorting": [
        [0, "asc"]
      ],
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "/covid-19/management/getCovid19MonthlyThresholdReport.php",
      "fnServerData": function(sSource, aoData, fnCallback) {
		aoData.push({
          "name": "targetType",
          "value": '1'
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
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>