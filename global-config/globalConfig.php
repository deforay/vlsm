<?php
$title = "Configuration";
#require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');
?>
<style>
	#globalConfigDataTable_length {
		display: none;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa fa-gears"></i> General Configuration</h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">General Configuration</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header with-border row" style=" margin: 15px; border: 0px; ">
						<label for="category" class="col-sm-2">Category :</label></th>
						<select class="form-control col-sm-4" id="category" name="category" title="Please select module to filter" style="width:220px;" onchange="getConfig();">
							<option value=""> -- Select -- </option>
							<option value="vl">VL</option>
							<option value="eid">EID</option>
							<option value="covid19">Covid19</option>
						</select>
						<?php if (isset($_SESSION['privileges']) && in_array("editGlobalConfig.php", $_SESSION['privileges'])) { ?>
							<div class="col-sm-6 pull-right">
								<a href="editGlobalConfig.php?e=1" class="btn btn-primary pull-right"> <i class="fa fa-pencil"></i> Edit General Config</a>
							</div>
						<?php } ?>
						<br>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<table id="globalConfigDataTable" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th style="width:55%;">Config Name</th>
									<th>Value</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="2" class="dataTables_empty">Loading data from server</td>
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
<script type="text/javascript">
	var oTable = null;
	$(document).ready(function() {
		$.blockUI();
		oTable = $('#globalConfigDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,

			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
				},
				{
					"sClass": "center"
				}
			],
			"aaSorting": [
				[0, "asc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"iDisplayLength": 100,
			"sAjaxSource": "getGlobalConfigDetails.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({ "name":"category","value": $("#category").val()} );
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

	function getConfig(){
		$.blockUI();
		oTable.fnDraw();
		$.unblockUI();
	}
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>