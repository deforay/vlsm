<?php

use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\SystemService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

$title = _translate("Audit Trail");
require_once APPLICATION_PATH . '/header.php';

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$archiveDbExists = false;
if (
	!empty(SYSTEM_CONFIG['archive']) &&
	SYSTEM_CONFIG['archive']['enabled'] === true &&
	!empty(SYSTEM_CONFIG['archive']['database']['host']) &&
	!empty(SYSTEM_CONFIG['archive']['database']['username'])
) {
	$archiveDbExists = true;
	$archiveDbName = SYSTEM_CONFIG['archive']['database']['db'];
	$db->addConnection('archive', SYSTEM_CONFIG['archive']['database']);
}


// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());

$activeModules = SystemService::getActiveModules();

if (isset($_POST['testType'])) {
	$tableName = $_POST['testType'];
	$sampleCode = $_POST['sampleCode'];
	$tableName2 = str_replace('audit_', '', (string) $tableName);
} else {
	$tableName = "";
	$sampleCode = "";
	$tableName2 = "";
}

function getColumns($db, $tableName)
{
	$columnsSql = "SELECT COLUMN_NAME
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_SCHEMA = ? AND table_name=?
					ORDER BY ordinal_position";
	return $db->rawQuery($columnsSql, [SYSTEM_CONFIG['database']['db'], $tableName]);
}

// function getColumnValues($db, $tableName, $sampleCode)
// {
// 	$sql = "SELECT a.*,
// 				modifier.user_name as last_modified_by,
// 				creator.user_name as req_created_by,
// 				tester.user_name as tested_by,
// 				approver.user_name as result_approved_by,
// 				riewer.user_name as result_reviewed_by
// 				FROM $tableName as a
// 				LEFT JOIN user_details as creator ON a.request_created_by = creator.user_id
// 				LEFT JOIN user_details as modifier ON a.last_modified_by = modifier.user_id
// 				LEFT JOIN user_details as tester ON a.tested_by = tester.user_id
// 				LEFT JOIN user_details as approver ON a.result_approved_by = approver.user_id
// 				LEFT JOIN user_details as riewer ON a.result_reviewed_by = riewer.user_id
// 				WHERE sample_code = ? OR remote_sample_code = ? OR unique_id like ?";
// 	return $db->rawQuery($sql, [$sampleCode, $sampleCode, $sampleCode]);
// }

function getColumnValues($db, $tableName, $sampleCode, $archiveDbExists, $archiveDbName)
{
	$mainDbName = SYSTEM_CONFIG['database']['db'];
	$baseQuery = "SELECT a.*,
                    modifier.user_name as last_modified_by,
                    creator.user_name as req_created_by,
                    tester.user_name as tested_by,
                    approver.user_name as result_approved_by,
                    riewer.user_name as result_reviewed_by
                FROM %s.$tableName as a
                LEFT JOIN user_details as creator ON a.request_created_by = creator.user_id
                LEFT JOIN user_details as modifier ON a.last_modified_by = modifier.user_id
                LEFT JOIN user_details as tester ON a.tested_by = tester.user_id
                LEFT JOIN user_details as approver ON a.result_approved_by = approver.user_id
                LEFT JOIN user_details as riewer ON a.result_reviewed_by = riewer.user_id
                WHERE sample_code = ? OR remote_sample_code = ? OR unique_id like ?";

	$queries = [];
	$queries[] = sprintf($baseQuery, $mainDbName);

	if ($archiveDbExists && !empty($archiveDbName) && $archiveDbName !== '' && $archiveDbName !== $mainDbName) {
		$queries[] = sprintf($baseQuery, $archiveDbName);
	}

	$unionQuery = implode(" UNION ", $queries);
	return $db->rawQuery($unionQuery, [$sampleCode, $sampleCode, "%$sampleCode%"]);
}



$resultColumn = getColumns($db, $tableName);

?>
<style>
	.current {
		display: block;
		overflow-x: auto;
		white-space: nowrap;
	}
</style>
<link href="/assets/css/multi-select.css" rel="stylesheet" />
<link href="/assets/css/buttons.dataTables.min.css" rel="stylesheet" />


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-clock-rotate-left"></em> <?php echo _translate("Audit Trail"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
			<li class="active"><?php echo _translate("Audit Trail"); ?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">

			<div class="col-xs-12">
				<div class="box">

					<form name="form1" action="audit-trail.php" method="post" id="searchForm">

						<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;">
							<tr>
								<td><strong><?php echo _translate("Test Type"); ?>&nbsp;:</strong></td>
								<td>
									<select id="testType" name="testType" class="form-control" placeholder="<?php echo _translate('Please select the Test types'); ?>">
										<option value="">-- Choose Test Type--</option>
										<?php if (!empty($activeModules) && in_array('vl', $activeModules)) { ?>
											<option <?php echo (isset($_POST['testType']) && $_POST['testType'] == 'audit_form_vl') ? "selected='selected'" : ""; ?> value="audit_form_vl"><?php echo _translate("Viral Load"); ?></option>
										<?php }
										if (!empty($activeModules) && in_array('eid', $activeModules)) { ?>
											<option <?php echo (isset($_POST['testType']) && $_POST['testType'] == 'audit_form_eid') ? "selected='selected'" : ""; ?> value="audit_form_eid"><?php echo _translate("Early Infant Diagnosis"); ?></option>
										<?php }
										if (!empty($activeModules) && in_array('covid19', $activeModules)) { ?>
											<option <?php echo (isset($_POST['testType']) && $_POST['testType'] == 'audit_form_covid19') ? "selected='selected'" : ""; ?> value="audit_form_covid19"><?php echo _translate("Covid-19"); ?></option>
										<?php }
										if (!empty($activeModules) && in_array('hepatitis', $activeModules)) { ?>
											<option <?php echo (isset($_POST['testType']) && $_POST['testType'] == 'audit_form_hepatitis') ? "selected='selected'" : ""; ?> value='audit_form_hepatitis'><?php echo _translate("Hepatitis"); ?></option>
										<?php }
										if (!empty($activeModules) && in_array('tb', $activeModules)) { ?>
											<option <?php echo (isset($_POST['testType']) && $_POST['testType'] == 'audit_form_tb') ? "selected='selected'" : ""; ?> value='audit_form_tb'><?php echo _translate("TB"); ?></option>
										<?php }
										if (!empty($activeModules) && in_array('cd4', $activeModules)) { ?>
											<option <?php echo (isset($_POST['testType']) && $_POST['testType'] == 'audit_form_cd4') ? "selected='selected'" : ""; ?> value='audit_form_cd4'><?php echo _translate("CD4"); ?></option>
										<?php } ?>
									</select>
								</td>
								<td>&nbsp;<strong><?php echo _translate("Sample ID"); ?>&nbsp;:</strong></td>
								<td>
									<input type="text" value="<?= htmlspecialchars((string) $_POST['sampleCode']); ?>" name="sampleCode" id="sampleCode" class="form-control" />
								</td>
							<tr>
								<td colspan="4">&nbsp;<input type="submit" value="<?php echo _translate("Submit"); ?>" class="btn btn-success btn-sm">
									&nbsp;<button type="reset" class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?= _translate('Reset'); ?></span></button>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>

			<?php
			if (!empty($sampleCode)) {
				$posts = getColumnValues($db, $tableName, $sampleCode, $archiveDbExists, $archiveDbName);

			?>
				<div class="col-xs-12">
					<div class="box">
						<!-- /.box-header -->
						<div class="box-body">
							<?php if (!empty($posts)) { ?>
								<h3> Audit Trail for Sample <?php echo htmlspecialchars((string) $sampleCode); ?></h3>
								<select name="auditColumn[]" id="auditColumn" class="" multiple="multiple">
									<?php
									//echo '<pre>'; print_r($resultColumn); die;
									$i = 0;
									foreach ($resultColumn as $col) {
									?>
										<option value="<?php echo $i; ?>"><?php echo $col['COLUMN_NAME']; ?></option>
									<?php
										$i++;
									}
									?>
								</select>

								<table aria-describedby="table" id="auditTable" class="table-bordered table table-striped table-hover" aria-hidden="true">
									<thead>
										<tr>
											<?php

											$colArr = [];
											foreach ($resultColumn as $col) {
												$colArr[] = $col['COLUMN_NAME'];
											?>
												<th>
													<?php
													echo $col['COLUMN_NAME'];
													?>
												</th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php

										for ($i = 0; $i < count($posts); $i++) {
										?>
											<tr>
												<?php
												for ($j = 0; $j < count($colArr); $j++) {

													if (($j > 3) && ($i > 0) && $posts[$i][$colArr[$j]] != $posts[$i - 1][$colArr[$j]]) {
														echo '<td style="background-color: orange;" >' . $posts[$i][$colArr[$j]] . '</td>';
													} else {
														echo '<td>' . $posts[$i][$colArr[$j]] . '</td>';
													}
												?>
												<?php }
												?>
											</tr>
										<?php } ?>

									</tbody>

								</table>

								<p>
								<h3> Current Record for Sample <?php echo htmlspecialchars((string) $sampleCode); ?></h3>
								</p>
								<table aria-describedby="table" class="current table table-striped table-hover table-bordered" aria-hidden="true">
									<thead>
										<tr>
											<?php
											$resultColumn = getColumns($db, $tableName2);
											$posts = getColumnValues($db, $tableName, $sampleCode, $archiveDbExists, $archiveDbName);
											foreach ($resultColumn as $col) {
											?>
												<th>
													<?php
													echo $col['COLUMN_NAME'];
													?>
												</th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<tr>
											<?php
											for ($i = 0; $i < count($posts); $i++) {
											?>

												<?php
												for ($j = 3; $j < count($colArr); $j++) {
												?>
													<td>
														<?php
														echo $posts[$i][$colArr[$j]];
														?>
													</td>
												<?php }
												?>

											<?php
											}

											?>
										</tr>
									</tbody>
								</table>
							<?php } else {
								echo '<h3 align="center">Records are not available for this sample id. Please enter  valid sample id</h3>';
							}
							?>
						</div>
					</div>
					<!-- /.box -->
				</div>
				<!-- /.col -->
			<?php
			}
			?>
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>




<script src="/assets/js/dataTables.buttons.min.js"></script>
<script src="/assets/js/jszip.min.js"></script>
<script src="/assets/js/buttons.html5.min.js"></script>
<script type="text/javascript">
	function printString(columnNumber) {
		// To store result (Excel column name)
		let columnName = [];

		while (columnNumber > 0) {
			// Find remainder
			let rem = columnNumber % 26;

			// If remainder is 0, then a
			// 'Z' must be there in output
			if (rem === 0) {
				columnName.push("Z");
				columnNumber = Math.floor(columnNumber / 26) - 1;
			} else // If remainder is non-zero
			{
				columnName.push(String.fromCharCode((rem - 1) + 'A'.charCodeAt(0)));
				columnNumber = Math.floor(columnNumber / 26);
			}
		}

		// Reverse the string and print result
		return columnName.reverse().join("");
	}
	$(document).ready(function() {

		$("#auditColumn").selectize({
			plugins: ["restore_on_backspace", "remove_button", "clear_button"],
		});
		table = $("#auditTable").DataTable({
			dom: 'Bfrtip',
			buttons: [{
				extend: 'excelHtml5',
				exportOptions: {
					columns: ':visible'
				},
				text: 'Export To Excel',
				title: 'AuditTrailSample-<?php echo $sampleCode; ?>',
				extension: '.xlsx',
				customize: function(xlsx) {
					var sheet = xlsx.xl.worksheets['sheet1.xml'];
					// Map used to map column index to Excel index

					var excelMap = [];
					b = 0;
					for (a = 1; a <= 226; a++) {
						excelMap[b] = printString(a);
						b++;
					}
					var count = 0;
					var skippedHeader = 0;

					$('row', sheet).each(function() {
						var row = this;
						if (skippedHeader === 2) {
							//             var colour = $('tbody tr:eq('+parseInt(count)+') td:eq(2)').css('background-color');

							// Output first row
							if (count === 0) {
								console.log(this);
							}

							for (td = 0; td < 226; td++) {

								// Output cell contents for first row
								if (count === 0) {
									console.log($('c[r^="' + excelMap[td] + '"]', row).text());
								}
								var colour = $(table.cell(':eq(' + count + ')', td).node()).css('background-color');

								if (colour === 'rgb(255, 165, 0)' || colour === 'orange') {
									$('c[r^="' + excelMap[td] + '"]', row).attr('s', '35');
								}

							}
							count++;
						} else {
							skippedHeader++;
						}
					});
				}
			}],
			scrollY: '250vh',
			scrollX: true,
			scrollCollapse: true,
			paging: false,
			"aaSorting": [1, "asc"],
		});


		$('#auditColumn').on("change", function(e) {

			var columns = $(this).val()

			if (columns === "" || columns == null) {
				table.columns().visible(true);
			} else {
				table.columns().visible(false);
				table.columns(columns).visible(true);
			}

		});
	});
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
