<?php


$title = "Edit Batch Position";


require_once APPLICATION_PATH . '/header.php';
$id = base64_decode($_GET['id']);
if (!isset($id) || trim($id) == '') {
	header("Location:batch-code.php");
}
$content = '';
$displayOrder = [];
$batchQuery = "SELECT * from batch_details as b_d INNER JOIN instruments as i_c ON i_c.config_id=b_d.machine where batch_id=$id";
$batchInfo = $db->query($batchQuery);
// Config control
$configControlQuery = "SELECT * from instrument_controls where config_id=" . $batchInfo[0]['config_id'];
$configControlInfo = $db->query($configControlQuery);
$configControl = [];
foreach ($configControlInfo as $info) {
	if ($info['test_type'] == 'generic-tests') {
		$configControl[$info['test_type']]['noHouseCtrl'] = $info['number_of_in_house_controls'];
		$configControl[$info['test_type']]['noManufacturerCtrl'] = $info['number_of_manufacturer_controls'];
		$configControl[$info['test_type']]['noCalibrators'] = $info['number_of_calibrators'];
	}
}
if (!isset($batchInfo) || empty($batchInfo)) {
	header("Location:batch-code.php");
}
if (isset($batchInfo[0]['label_order']) && trim($batchInfo[0]['label_order']) != '') {
	$jsonToArray = json_decode($batchInfo[0]['label_order'], true);
	if (isset($batchInfo[0]['position_type']) && $batchInfo[0]['position_type'] == 'alpha-numeric') {
		foreach ($general->excelColumnRange('A', 'H') as $value) {
			foreach (range(1, 12) as $no) {
				$alphaNumeric[] = $value . $no;
			}
		}
		for ($j = 0; $j < count($jsonToArray); $j++) {
			$displayOrder[] = $jsonToArray[$alphaNumeric[$j]];
			$xplodJsonToArray = explode("_", $jsonToArray[$alphaNumeric[$j]]);
			if (count($xplodJsonToArray) > 1 && $xplodJsonToArray[0] == "s") {
				$sampleQuery = "SELECT sample_code from form_generic where sample_id=$xplodJsonToArray[1]";
				$sampleResult = $db->query($sampleQuery);
				$label = $sampleResult[0]['sample_code'];
			} else {
				$label = str_replace("_", " ", $jsonToArray[$alphaNumeric[$j]]);
				$label = str_replace("in house", "In-House", $label);
				$label = (str_replace("no of ", " ", $label));
			}
			$content .= '<li class="ui-state-default" id="' . $jsonToArray[$alphaNumeric[$j]] . '">' . $label . '</li>';
		}
	} else {
		for ($j = 0; $j < count($jsonToArray); $j++) {
			$displayOrder[] = $jsonToArray[$j];
			$xplodJsonToArray = explode("_", $jsonToArray[$j]);
			if (count($xplodJsonToArray) > 1 && $xplodJsonToArray[0] == "s") {
				$sampleQuery = "SELECT sample_code from form_generic where sample_id=$xplodJsonToArray[1]";
				$sampleResult = $db->query($sampleQuery);
				$label = $sampleResult[0]['sample_code'];
			} else {
				$label = str_replace("_", " ", $jsonToArray[$j]);
				$label = str_replace("in house", "In-House", $label);
				$label = (str_replace("no of ", " ", $label));
			}
			$content .= '<li class="ui-state-default" id="' . $jsonToArray[$j] . '">' . $label . '</li>';
		}
	}
} else {
	if (isset($configControl['generic-tests']['noHouseCtrl']) && trim($configControl['generic-tests']['noHouseCtrl']) != '' && $configControl['generic-tests']['noHouseCtrl'] > 0) {
		foreach (range(1, $configControl['generic-tests']['noHouseCtrl']) as $h) {
			$displayOrder[] = "no_of_in_house_controls_" . $h;
			$content .= '<li class="ui-state-default" id="no_of_in_house_controls_' . $h . '">In-House Controls ' . $h . '</li>';
		}
	}
	if (isset($configControl['generic-tests']['noManufacturerCtrl']) && trim($configControl['generic-tests']['noManufacturerCtrl']) != '' && $configControl['generic-tests']['noManufacturerCtrl'] > 0) {
		foreach (range(1, $configControl['generic-tests']['noManufacturerCtrl']) as $m) {
			$displayOrder[] = "no_of_manufacturer_controls_" . $m;
			$content .= '<li class="ui-state-default" id="no_of_manufacturer_controls_' . $m . '">Manufacturer Controls ' . $m . '</li>';
		}
	}
	if (isset($configControl['generic-tests']['noCalibrators']) && trim($configControl['generic-tests']['noCalibrators']) != '' && $configControl['generic-tests']['noCalibrators'] > 0) {
		foreach (range(1, $configControl['generic-tests']['noCalibrators']) as $c) {
			$displayOrder[] = "no_of_calibrators_" . $c;
			$content .= '<li class="ui-state-default" id="no_of_calibrators_' . $c . '">Calibrators ' . $c . '</li>';
		}
	}
	$samplesQuery = "SELECT sample_id,sample_code from form_generic where sample_batch_id=$id ORDER BY sample_code ASC";
	$samplesInfo = $db->query($samplesQuery);
	foreach ($samplesInfo as $sample) {
		$displayOrder[] = "s_" . $sample['sample_id'];
		$content .= '<li class="ui-state-default" id="s_' . $sample['sample_id'] . '">' . $sample['sample_code'] . '</li>';
	}
}
?>
<style>
	#sortableRow {
		list-style-type: none;
		margin: 0px 0px 30px 0px;
		padding: 0;
		width: 100%;
		text-align: center;
	}

	#sortableRow li {
		color: #333 !important;
		font-size: 16px;
		border-radius: 10px;
		margin-bottom: 4px;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-pen-to-square"></em> Edit Batch Controls Position</h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
			<li class="active">Batch</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="box box-default">
			<div class="box-header with-border">
				<h4><strong>Batch Code : <?php echo (isset($batchInfo[0]['batch_code'])) ? $batchInfo[0]['batch_code'] : ''; ?></strong></h4>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-horizontal" method='post' name='editBatchControlsPosition' id='editBatchControlsPosition' autocomplete="off" action="editBatchControlsPositionHelper.php">
					<div class="box-body">
						<div class="row" id="displayOrderDetails">
							<div class="col-md-8">
								<ul id="sortableRow">
									<?php
									echo $content;
									?>
								</ul>
							</div>
						</div>
					</div>
					<!-- /.box-body -->
					<div class="box-footer">
						<input type="hidden" name="sortOrders" id="sortOrders" value="<?php echo implode(",", $displayOrder); ?>" />
						<input type="hidden" name="batchId" id="batchId" value="<?php echo htmlspecialchars($id); ?>" />
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
						<a href="batch-code.php" class="btn btn-default"> Cancel</a>
					</div>
					<!-- /.box-footer -->
				</form>
				<!-- /.row -->
			</div>

		</div>
		<!-- /.box -->

	</section>
	<!-- /.content -->
</div>
<script>
	sortedTitle = [];
	$(document).ready(function() {
		function cleanArray(actual) {
			var newArray = [];
			for (var i = 0; i < actual.length; i++) {
				if (actual[i]) {
					newArray.push(actual[i]);
				}
			}
			return newArray;
		}

		$("#sortableRow").sortable({
			opacity: 0.6,
			cursor: 'move',
			update: function() {
				sortedTitle = cleanArray($(this).sortable("toArray"));
				$("#sortOrders").val("");
				$("#sortOrders").val(sortedTitle);
			}
		}).disableSelection();
	});

	function validateNow() {
		flag = deforayValidator.init({
			formId: 'editBatchControlsPosition'
		});

		if (flag) {
			$.blockUI();
			document.getElementById('editBatchControlsPosition').submit();
		}
	}
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';