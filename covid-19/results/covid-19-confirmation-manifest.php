<?php
$title = "Add Samples from Manifest";
require_once('../../startup.php');
include_once(APPLICATION_PATH . '/header.php');

$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$configFormQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
$sQuery = "SELECT * FROM r_covid19_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);
$fQuery = "SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$batQuery = "SELECT batch_code FROM batch_details where batch_status='completed'";
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
		<h1><i class="fa fa-check-square-o"></i> Confirmation Test Manifest</h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Covid-19 Confirmation Test Manifest</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width: 98%;margin-bottom: 0px;">
						<tr>
							<td>
								<?php if (isset($_SESSION['privileges']) && in_array("covid-19-add-confirmation-manifest.php", $_SESSION['privileges'])) { ?>
									<a href="/covid-19/results/covid-19-add-confirmation-manifest.php" class="btn btn-primary btn-sm pull-right"> <i class="fa fa-plus"></i> Add new Covid-19 Confirmation Manifest</a>
								<?php } ?>
							</td>
						</tr>
					</table>
					<!-- /.box-header -->
					<div class="box-body table-responsive">
						<table id="covid19ManifestDataTable" class="table table-bordered table-striped table-vcenter">
							<thead>
								<tr>
									<th>Sample Code</th>
									<th>Manifest Code</th>
									<?php if ($sarr['user_type'] != 'standalone') { ?>
										<th>Remote Sample <br />Code</th>
									<?php } ?>
									<th>Sample Collection<br /> Date</th>
									<th>Batch Code</th>
									<th>Facility Name</th>
									<th>Patient ID</th>
									<th>Patient Name</th>
									<th>Province/State</th>
									<th>District/County</th>
									<th>Result</th>
									<th>Status</th>
									<?php if (isset($_SESSION['privileges']) && in_array("generate-confirmation-manifest.php", $_SESSION['privileges'])) { ?>
									<th>Action</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="12" class="dataTables_empty" style="text-align:center;">Please enter the manifest code then submit!</td>
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
<script type="text/javascript" src="../assets/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="../assets/plugins/daterangepicker/daterangepicker.js"></script>

<?php
if (isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off") {
	if ($global['bar_code_printing'] == 'dymo-labelwriter-450') {
?>
		<script src="../assets/js/DYMO.Label.Framework.js"></script>
		<script src="../configs/dymo-format.js"></script>
		<script src="../assets/js/dymo-print.js"></script>
	<?php
	} else if ($global['bar_code_printing'] == 'zebra-printer') {
	?>
		<script src="../assets/js/zebra-browserprint.js.js"></script>
		<script src="../configs/zebra-format.js"></script>
		<script src="../assets/js/zebra-print.js"></script>
<?php
	}
}
?>

<script type="text/javascript">
	var oTable = null;
	$(document).ready(function() {
		oTable = $('#covid19ManifestDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,
			"bStateSave": true,
			//"bDestroy": true,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
				},
				<?php if ($sarr['user_type'] != 'standalone') { ?> {
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
				}, {
					"sClass": "center"
				}
			],
			"aaSorting": [
				[<?php echo ($sarr['user_type'] == 'remoteuser' || $sarr['user_type'] == 'vluser') ? 9 : 8 ?>, "desc"]
			],
			"fnDrawCallback": function() {},
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "/covid-19/results/getConfirmManifestInGridHelper.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				$.ajax({
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": fnCallback
				});
			}
		});
	});
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>