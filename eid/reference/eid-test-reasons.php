<?php
$title = "EID Test Reasons";
#require_once('../startup.php'); 
include_once(APPLICATION_PATH . '/header.php');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa fa-gears"></i> EID Test Reasons</h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">EID Test Reasons</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header with-border">
						<?php if (isset($_SESSION['privileges']) && in_array("eid-sample-type.php", $_SESSION['privileges']) && (($sarr['user_type'] == 'remoteuser') || ($sarr['user_type'] == 'standalone'))) { ?>
							<a href="add-eid-test-reasons.php" class="btn btn-primary pull-right"> <i class="fa fa-plus"></i> Add EID Test Reasons</a>
						<?php } ?>
						<!--<button class="btn btn-primary pull-right" style="margin-right: 1%;" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>-->
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<table id="testReasonDataTable" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Test Reason</th>
									<th>Status</th>
									<?php if (isset($_SESSION['privileges']) && in_array("eid-sample-type.php", $_SESSION['privileges']) && (($sarr['user_type'] == 'remoteuser') || ($sarr['user_type'] == 'standalone'))) { ?>
										<th>Action</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="3" class="dataTables_empty">Loading data from server</td>
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
<script>
	var oTable = null;
	$(function() {
		//$("#example1").DataTable();
	});
	$(document).ready(function() {
		$.blockUI();
		oTable = $('#testReasonDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,
			"bStateSave": true,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
				},
				{
					"sClass": "center"
				},
				<?php if (isset($_SESSION['privileges']) && in_array("eid-sample-type.php", $_SESSION['privileges']) && (($sarr['user_type'] == 'remoteuser') || ($sarr['user_type'] == 'standalone'))) { ?> {
						"sClass": "center",
						"bSortable": false
					},
				<?php } ?>
			],
			"aaSorting": [
				[0, "asc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "get-eid-test-reasons-helper.php",
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
		$.unblockUI();
	});
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>