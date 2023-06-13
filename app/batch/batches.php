<?php
// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_GET = $request->getQueryParams();


$showPatientName = false;
if (isset($_GET['type']) && $_GET['type'] == 'vl') {
	$_title = "Viral Load";
    $refTable = "form_vl";
    $refPrimaryColumn = "vl_sample_id";
    $patientIdColumn = 'patient_art_no';
    $patientFirstName = 'patient_first_name';
    $patientLastName = 'patient_last_name';
    $worksheetName = 'Viral Load Test Worksheet';
} elseif (isset($_GET['type']) && $_GET['type'] == 'eid') {
	$_title = "Early Infant Diagnosis";
    $refTable = "form_eid";
    $refPrimaryColumn = "eid_id";
    $patientIdColumn = 'child_id';
    $patientFirstName = 'child_name';
    $patientLastName = 'child_surname';
    $worksheetName = 'EID Test Worksheet';
} elseif (isset($_GET['type']) && $_GET['type'] == 'covid19') {
	$_title = "Covid-19";
    $refTable = "form_covid19";
    $refPrimaryColumn = "covid19_id";
    $patientIdColumn = 'patient_id';
    $patientFirstName = 'patient_name';
    $patientLastName = 'patient_surname';
    $worksheetName = 'Covid-19 Test Worksheet';
} elseif (isset($_GET['type']) && $_GET['type'] == 'hepatitis') {
	$_title = "Hepatitis";
    $refTable = "form_hepatitis";
    $refPrimaryColumn = "hepatitis_id";
    $patientIdColumn = 'patient_id';
    $patientFirstName = 'patient_name';
    $patientLastName = 'patient_surname';
    $worksheetName = 'Hepatitis Test Worksheet';
    $showPatientName = true;
} elseif (isset($_GET['type']) && $_GET['type'] == 'tb') {
	$_title = "TB";
    $refTable = "form_tb";
    $refPrimaryColumn = "tb_id";
    $patientIdColumn = 'patient_id';
    $patientFirstName = 'patient_name';
    $patientLastName = 'patient_surname';
    $worksheetName = 'TB Test Worksheet';
    $showPatientName = true;
} elseif (isset($_GET['type']) && $_GET['type'] == 'generic-tests') {
	$_title = "Lab Tests";
    $refTable = "form_generic";
    $refPrimaryColumn = "sample_id";
    $patientIdColumn = 'patient_id';
    $patientFirstName = 'patient_first_name';
    $patientLastName = 'patient_last_name';
    $worksheetName = 'Lab Test Worksheet';
    $showPatientName = true;
	$genericHide = "style='display:none;'";
}
$title = _( $_title . " | Batches");

require_once APPLICATION_PATH . '/header.php';
$testTypeQuery = "SELECT * FROM r_test_types where test_status='active' ORDER BY test_standard_name ASC";
$testTypeResult = $db->rawQuery($testTypeQuery);
?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-pen-to-square"></em> <?php echo _("Manage ".$_title." Batches"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
			<li class="active"><?php echo _("Manage Batches"); ?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<?php if(!empty($_GET['type']) && $_GET['type'] == 'generic-tests') { ?>
						<div class="box-header with-border">
							<div class="row">
								<div class="col-xs-6 col-md-6">
									<div class="form-group" style="margin-left:30px; margin-top:30px;">
										<label for="testType">Test Type</label>
										<select class="form-control" name="testType" id="testType" title="Please choose test type" style="width:100%;" onchange="getBatchForm(this)">
											<option value=""> -- Select -- </option>
											<?php foreach ($testTypeResult as $testType) { ?>
												<option value="<?php echo $testType['test_type_id']; ?>"><?php echo $testType['test_standard_name'] . ' (' . $testType['test_loinc_code'] . ')' ?></option>
											<?php } ?>
										</select>
										<span class="batchAlert" style="font-size:1.1em;color: red;">Choose test type to see relavent sample bacthes</span>
									</div>
								</div>
								<div class="col-xs-6 col-md-6">
									<div class="box-header with-border batchDiv" <?php echo $genericHide;?>>
										<?php if (isset($_SESSION['privileges']) && in_array("/batch/add-batch.php?type=".$_GET['type'], $_SESSION['privileges'])) { ?>
											<a href="add-batch.php?type=<?php echo $_GET['type'];?>" class="btn btn-primary pull-right"> <em class="fa-solid fa-plus"></em> <?php echo _("Create New Batch"); ?></a>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					<?php }else{ ?>
						<div class="box-header with-border batchDiv" <?php echo $genericHide;?>>
							<?php if (isset($_SESSION['privileges']) && in_array("/batch/add-batch.php?type=".$_GET['type'], $_SESSION['privileges'])) { ?>
								<a href="add-batch.php?type=<?php echo $_GET['type'];?>" class="btn btn-primary pull-right"> <em class="fa-solid fa-plus"></em> <?php echo _("Create New Batch"); ?></a>
							<?php } ?>
						</div>
					<?php } ?>
					<!-- /.box-header -->
					<div class="box-body batchDiv" <?php echo $genericHide;?>>
						<table aria-describedby="table" id="batchCodeDataTable" class="table table-bordered table-striped" aria-hidden="true" >
							<thead>
								<tr>
									<th scope="col"><?php echo _("Batch Code"); ?></th>
									<th scope="col"><?php echo _("No. of Samples"); ?></th>
									<th scope="col"><?php echo _("No. of Samples Tested"); ?></th>
									<th scope="col"><?php echo _("Last Tested Date"); ?></th>
									<th scope="col"><?php echo _("Created On"); ?></th>
									<?php if (isset($_SESSION['privileges']) && in_array("/batch/edit-batch.php?type=".$_GET['type'], $_SESSION['privileges'])) { ?>
										<th scope="col"><?php echo _("Action"); ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="5" class="dataTables_empty"><?php echo _("Loading data from server"); ?></td>
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
	$(document).ready(function() {
		$.blockUI();
		oTable = $('#batchCodeDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,
			//"bStateSave" : true,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
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
					"sClass": "center"
				},
				<?php if (isset($_SESSION['privileges']) && in_array("/batch/edit-batch.php?type=".$_GET['type'], $_SESSION['privileges'])) { ?> {
						"sClass": "center",
						"bSortable": false
					},
				<?php } ?>
			],
			"aaSorting": [
				[4, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "get-batches.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "type",
					"value": "<?php echo $_GET['type'];?>"
				});
				aoData.push({
					"name": "testType",
					"value": $('#testType').val()
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
	});

	

	function deleteBatchCode(bId, batchCode) {
		var conf = confirm("<?php echo _("Are you sure you want to delete Batch"); ?> : " + batchCode + "?\n<?php echo _("This action cannot be undone."); ?>");
		if (conf) {
			$.post("delete-batch.php", {
					id: bId,
					type: '<?php echo $_GET['type'];?>'
				},
				function(data) {
					if (data == 1) {
						alert("<?php echo _("Batch deleted"); ?>");
					} else {
						alert("<?php echo _("Something went wrong. Please try again!"); ?>");
					}
					oTable.fnDraw();
				});
		}
	}

	function getBatchForm(obj) {
		if(obj.value != ""){
			$(".batchDiv").show();
			oTable.fnDraw();
			$('.batchAlert').hide();
		}else{
			$(".batchDiv").hide();
			$('.batchAlert').show();
		}
	}
</script>


<?php

require_once APPLICATION_PATH . '/footer.php';
