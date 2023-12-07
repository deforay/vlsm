<?php

use App\Registries\ContainerRegistry;
use App\Services\FacilitiesService;
use App\Services\GeoLocationsService;

$title = _translate("Hepatitis | Clinics Report");

require_once APPLICATION_PATH . '/header.php';

$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);

//$arr = $general->getGlobalConfig();


/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

/** @var GeoLocationsService $geolocationService */
$geolocationService = ContainerRegistry::get(GeoLocationsService::class);


$healthFacilites = $facilitiesService->getHealthFacilities('hepatitis');
$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");
$testingLabs = $facilitiesService->getTestingLabs('hepatitis');
$testingLabsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");



$sQuery = "SELECT * FROM r_covid19_sample_type WHERE `status`='active'";
$sResult = $db->rawQuery($sQuery);

$batQuery = "SELECT batch_code FROM batch_details WHERE test_type='hepatitis' AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);

//sample rejection reason
$rejectionTypeQuery = "SELECT DISTINCT rejection_type FROM r_hepatitis_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

$rejectionQuery = "SELECT * FROM r_hepatitis_sample_rejection_reasons where rejection_reason_status = 'active'";
$rejectionResult = $db->rawQuery($rejectionQuery);

$rejectionReason = "";
foreach ($rejectionTypeResult as $type) {
	$rejectionReason .= '<optgroup label="' . ($type['rejection_type']) . '">';
	foreach ($rejectionResult as $reject) {
		if ($type['rejection_type'] == $reject['rejection_type']) {
			$rejectionReason .= '<option value="' . $reject['rejection_reason_id'] . '">' . ($reject['rejection_reason_name']) . '</option>';
		}
	}
	$rejectionReason .= '</optgroup>';
}

$state = $geolocationService->getProvinces("yes");

?>
<style>
	.select2-selection__choice {
		color: #000000 !important;
	}

	.center {
		/*text-align:left;*/
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1> <em class="fa-solid fa-book"></em> <?php echo _translate("Clinic Reports"); ?></h1>
		<ol class="breadcrumb">
			<!-- <li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li> -->
			<li> <em class="fa-solid fa-book"></em> <?php echo _translate("Hepatitis"); ?></li>
			<li><?php echo _translate("Management"); ?></li>
			<li class="active"><?php echo _translate("Clinic Reports"); ?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<!-- /.box-header -->
					<div class="box-body">
						<div class="widget">
							<div class="widget-content">
								<div class="bs-example bs-example-tabs">
									<ul id="myTab" class="nav nav-tabs">
										<li class="active"><a href="#highViralLoadReport" data-toggle="tab"><?php echo _translate("Positivity Report"); ?></a></li>
										<li><a href="#sampleRjtReport" data-toggle="tab"><?php echo _translate("Sample Rejection Report"); ?></a></li>
										<li><a href="#notAvailReport" data-toggle="tab"><?php echo _translate("Results Not Available Report"); ?></a></li>
										<li><a href="#incompleteFormReport" data-toggle="tab"><?php echo _translate("Data Quality Check"); ?></a></li>
									</ul>
									<div id="myTabContent" class="tab-content">
										<div class="tab-pane fade in active" id="highViralLoadReport">
											<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;padding: 3%;">
												<tr>
													<td><strong><?php echo _translate("Sample Test Date"); ?>&nbsp;:</strong></td>
													<td>
														<input type="text" id="hvlSampleTestDate" name="hvlSampleTestDate" class="form-control stDate" placeholder="<?php echo _translate('Select Sample Test Date'); ?>" readonly style="width:220px;background:#fff;" onchange="setSampleTestDate(this)" />
													</td>
													<td>&nbsp;<strong><?php echo _translate("Batch Code"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="hvlBatchCode" name="hvlBatchCode" title="<?php echo _translate('Please select batch code'); ?>" style="width:220px;">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<?php
															foreach ($batResult as $code) {
															?>
																<option value="<?php echo $code['batch_code']; ?>"><?php echo $code['batch_code']; ?></option>
															<?php
															}
															?>
														</select>
													</td>
													<td>&nbsp;<strong><?php echo _translate("Sample Type"); ?>&nbsp;:</strong></td>
													<td>
														<select style="width:220px;" class="form-control" id="hvlSampleType" name="sampleType" title="<?php echo _translate('Please select sample type'); ?>">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<?php
															foreach ($sResult as $type) {
															?>
																<option value="<?php echo $type['sample_id']; ?>"><?= $type['sample_name']; ?></option>
															<?php
															}
															?>
														</select>
													</td>
												</tr>
												<tr>
													<td><strong><?php echo _translate("Province/State"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control select2-element" id="state" onchange="getByProvince('district','hvlFacilityName',this.value)" name="state" title="<?php echo _translate('Please select Province/State'); ?>">
															<?= $general->generateSelectOptions($state, null, _translate("-- Select --")); ?>
														</select>
													</td>

													<td><strong><?php echo _translate("District/County"); ?> :</strong></td>
													<td>
														<select class="form-control select2-element" id="district" name="district" title="<?php echo _translate('Please select Province/State'); ?>" onchange="getByDistrict('hvlFacilityName',this.value)">
														</select>
													</td>
													<td>&nbsp;<strong><?php echo _translate("Facility Name & Code"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="hvlFacilityName" name="hvlFacilityName" title="<?php echo _translate('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
															<?= $facilitiesDropdown; ?>
														</select>
													</td>

												</tr>
												<tr>
													<td>&nbsp;<strong><?php echo _translate("Contact Status"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="hvlContactStatus" name="hvlContactStatus" title="<?php echo _translate('Please select contact status'); ?>" style="width:220px;">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<option value="yes"><?php echo _translate("Completed"); ?></option>
															<option value="no"><?php echo _translate("Not Completed"); ?></option>
															<option value="all" selected="selected"><?php echo _translate("All"); ?></option>
														</select>
													</td>
													<td><strong><?php echo _translate("Gender"); ?>&nbsp;:</strong></td>
													<td>
														<select name="hvlGender" id="hvlGender" class="form-control" title="<?php echo _translate('Please choose gender'); ?>" style="width:220px;" onchange="">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<option value="male"><?php echo _translate("Male"); ?></option>
															<option value="female"><?php echo _translate("Female"); ?></option>
															<option value="not_recorded"><?php echo _translate("Not Recorded"); ?></option>
														</select>
													</td>
													<td></td>
												</tr>
												<tr>
													<td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _translate("Search"); ?>" class="btn btn-success btn-sm">
														&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?= _translate('Reset'); ?></span></button>
														<button class="btn btn-success btn-sm" type="button" onclick="exportHighViralLoadInexcel()"><em class="fa-solid fa-cloud-arrow-down"></em> <?php echo _translate("Export to excel"); ?></button>
													</td>
												</tr>
											</table>

											<table aria-describedby="table" id="highViralLoadReportTable" class="table table-bordered table-striped" aria-hidden="true">
												<thead>
													<tr>
														<th><?php echo _translate("Sample ID"); ?></th>
														<?php if ($_SESSION['instanceType'] != 'standalone') { ?>
															<th><?php echo _translate("Remote Sample ID"); ?></th>
														<?php } ?>
														<th scope="row"><?php echo _translate("Facility Name"); ?></th>
														<th><?php echo _translate("Patient's ID"); ?></th>
														<th><?php echo _translate("Patient's Name"); ?></th>
														<th scope="row"><?php echo _translate("Sample Collection Date"); ?></th>
														<th><?php echo _translate("Sample Tested Date"); ?></th>
														<th scope="row"><?php echo _translate("Testing Lab"); ?></th>
														<th><?php echo _translate("HCV VL Result"); ?></th>
														<th><?php echo _translate("HBV VL Result"); ?></th>
														<th scope="row"><?php echo _translate("Status"); ?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td colspan="7" class="dataTables_empty"><?php echo _translate("Loading data from server"); ?></td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="tab-pane fade" id="sampleRjtReport">
											<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;padding: 3%;">
												<tr>
													<td><strong><?php echo _translate("Sample Test Date"); ?>&nbsp;:</strong></td>
													<td>
														<input type="text" id="rjtSampleTestDate" name="rjtSampleTestDate" class="form-control stDate daterange" placeholder="<?php echo _translate('Select Sample Test Date'); ?>" readonly style="width:220px;background:#fff;" onchange="setSampleTestDate(this)" />
													</td>
													<td>&nbsp;<strong><?php echo _translate("Batch Code"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="rjtBatchCode" name="rjtBatchCode" title="<?php echo _translate('Please select batch code'); ?>" style="width:220px;">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<?php
															foreach ($batResult as $code) {
															?>
																<option value="<?php echo $code['batch_code']; ?>"><?php echo $code['batch_code']; ?></option>
															<?php
															}
															?>
														</select>
													</td>
													<td>&nbsp;<strong><?php echo _translate("Sample Type"); ?>&nbsp;:</strong></td>
													<td>
														<select style="width:220px;" class="form-control" id="rjtSampleType" name="sampleType" title="<?php echo _translate('Please select sample type'); ?>">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<?php
															foreach ($sResult as $type) {
															?>
																<option value="<?php echo $type['sample_id']; ?>"><?= $type['sample_name']; ?></option>
															<?php
															}
															?>
														</select>
													</td>
												</tr>
												<tr>
													<td><strong><?php echo _translate("Province/State"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control select2-element" id="rjtState" onchange="getByProvince('rjtDistrict','rjtFacilityName',this.value)" name="rjtState" title="<?php echo _translate('Please select Province/State'); ?>">
															<?= $general->generateSelectOptions($state, null, _translate("-- Select --")); ?>
														</select>
													</td>

													<td><strong><?php echo _translate("District/County"); ?> :</strong></td>
													<td>
														<select class="form-control select2-element" id="rjtDistrict" name="rjtDistrict" title="<?php echo _translate('Please select Province/State'); ?>" onchange="getByDistrict('rjtFacilityName',this.value)">
														</select>
													</td>
													<td>&nbsp;<strong><?php echo _translate("Facility Name & Code"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="rjtFacilityName" name="facilityName" title="<?php echo _translate('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
															<?= $facilitiesDropdown; ?>
														</select>
													</td>

												</tr>
												<tr>
													<td><strong><?php echo _translate("Gender"); ?>&nbsp;:</strong></td>
													<td>
														<select name="rjtGender" id="rjtGender" class="form-control" title="<?php echo _translate('Please choose gender'); ?>" style="width:220px;" onchange="">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<option value="male"><?php echo _translate("Male"); ?></option>
															<option value="female"><?php echo _translate("Female"); ?></option>
															<option value="not_recorded"><?php echo _translate("Not Recorded"); ?></option>
														</select>
													</td>
													<td><strong><?php echo _translate("Rejection Reason"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Please choose reason for rejection">
															<option value=''> -- Select -- </option>
															<?php echo $rejectionReason; ?>
														</select>
													</td>
												</tr>
												<tr>
													<td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _translate("Search"); ?>" class="btn btn-success btn-sm">
														&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?= _translate('Reset'); ?></span></button>
														<button class="btn btn-success btn-sm" type="button" onclick="exportRejectedResultInexcel()"><em class="fa-solid fa-cloud-arrow-down"></em> <?php echo _translate("Export to excel"); ?></button>
													</td>
												</tr>
											</table>
											<table aria-describedby="table" id="sampleRjtReportTable" class="table table-bordered table-striped" aria-hidden="true">
												<thead>
													<tr>
														<th><?php echo _translate("Sample ID"); ?></th>
														<?php if ($_SESSION['instanceType'] != 'standalone') { ?>
															<th><?php echo _translate("Remote Sample ID"); ?></th>
														<?php } ?>
														<th scope="row"><?php echo _translate("Facility Name"); ?></th>
														<th><?php echo _translate("Patient's ID"); ?></th>
														<th><?php echo _translate("Patient's Name"); ?></th>
														<th scope="row"><?php echo _translate("Sample Collection Date"); ?></th>
														<th><?php echo _translate("Testing Lab Name"); ?></th>
														<th><?php echo _translate("Rejection Reason"); ?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td colspan="6" class="dataTables_empty"><?php echo _translate("Loading data from server"); ?></td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="tab-pane fade" id="notAvailReport">
											<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;padding: 3%;">
												<tr>
													<td><strong><?php echo _translate("Sample Collection Date"); ?>&nbsp;:</strong></td>
													<td>
														<input type="text" id="noResultSampleTestDate" name="noResultSampleTestDate" class="form-control stDate daterange" placeholder="<?php echo _translate('Select Sample Collection Date'); ?>" readonly style="width:220px;background:#fff;" onchange="setSampleTestDate(this)" />
													</td>
													<td>&nbsp;<strong><?php echo _translate("Batch Code"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="noResultBatchCode" name="noResultBatchCode" title="<?php echo _translate('Please select batch code'); ?>" style="width:220px;">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<?php
															foreach ($batResult as $code) {
															?>
																<option value="<?php echo $code['batch_code']; ?>"><?php echo $code['batch_code']; ?></option>
															<?php
															}
															?>
														</select>
													</td>
													<td>&nbsp;<strong><?php echo _translate("Sample Type"); ?>&nbsp;:</strong></td>
													<td>
														<select style="width:220px;" class="form-control" id="noResultSampleType" name="sampleType" title="<?php echo _translate('Please select sample type'); ?>">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<?php
															foreach ($sResult as $type) {
															?>
																<option value="<?php echo $type['sample_id']; ?>"><?= $type['sample_name']; ?></option>
															<?php
															}
															?>
														</select>
													</td>
												</tr>
												<tr>
													<td><strong><?php echo _translate("Province/State"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control select2-element" id="noResultState" onchange="getByProvince('noResultDistrict','noResultFacilityName',this.value)" name="rjtState" title="<?php echo _translate('Please select Province/State'); ?>">
															<?= $general->generateSelectOptions($state, null, _translate("-- Select --")); ?>
														</select>
													</td>

													<td><strong><?php echo _translate("District/County"); ?> :</strong></td>
													<td>
														<select class="form-control select2-element" id="noResultDistrict" name="noResultDistrict" title="<?php echo _translate('Please select Province/State'); ?>" onchange="getByDistrict('noResultFacilityName',this.value)">
														</select>
													</td>
													<td>&nbsp;<strong><?php echo _translate("Facility Name & Code"); ?>&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="noResultFacilityName" name="facilityName" title="<?php echo _translate('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
															<?= $facilitiesDropdown; ?>
														</select>
													</td>


												</tr>
												<tr>
													<td><strong><?php echo _translate("Gender"); ?>&nbsp;:</strong></td>
													<td>
														<select name="noResultGender" id="noResultGender" class="form-control" title="<?php echo _translate('Please choose gender'); ?>" style="width:220px;" onchange="">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<option value="male"><?php echo _translate("Male"); ?></option>
															<option value="female"><?php echo _translate("Female"); ?></option>
															<option value="not_recorded"><?php echo _translate("Not Recorded"); ?></option>
														</select>
													</td>
												</tr>
												<tr>
													<td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _translate("Search"); ?>" class="btn btn-success btn-sm">
														&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?= _translate('Reset'); ?></span></button>
														<button class="btn btn-success btn-sm" type="button" onclick="exportNotAvailableResultInexcel()"><em class="fa-solid fa-cloud-arrow-down"></em> <?php echo _translate("Export to excel"); ?></button>
													</td>
												</tr>
											</table>
											<table aria-describedby="table" id="notAvailReportTable" class="table table-bordered table-striped" aria-hidden="true">
												<thead>
													<tr>
														<th><?php echo _translate("Sample ID"); ?></th>
														<?php if ($_SESSION['instanceType'] != 'standalone') { ?>
															<th><?php echo _translate("Remote Sample ID"); ?></th>
														<?php } ?>
														<th scope="row"><?php echo _translate("Facility Name"); ?></th>
														<th><?php echo _translate("Patient's ID"); ?></th>
														<th><?php echo _translate("Patient's Name"); ?></th>
														<th scope="row"><?php echo _translate("Sample Collection Date"); ?></th>
														<th><?php echo _translate("Testing Lab Name"); ?></th>
														<th><?php echo _translate("Sample Status"); ?></th>

													</tr>
												</thead>
												<tbody>
													<tr>
														<td colspan="4" class="dataTables_empty"><?php echo _translate("Loading data from server"); ?></td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="tab-pane fade" id="incompleteFormReport">
											<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;padding: 3%;">
												<tr>
													<td><strong><?php echo _translate("Sample Collection Date"); ?>&nbsp;:</strong></td>
													<td>
														<input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="<?php echo _translate('Select Sample Collection Date'); ?>" readonly style="width:220px;background:#fff;" />
													</td>
													<td>&nbsp;<strong>Fields&nbsp;:</strong></td>
													<td>
														<select class="form-control" id="formField" name="formField" multiple="multiple" title="<?php echo _translate('Please fields'); ?>" style="width:220px;">
															<option value=""> <?php echo _translate("-- Select --"); ?> </option>
															<option value="sample_code"><?php echo _translate("Sample ID"); ?></option>
															<option value="sample_collection_date"><?php echo _translate("Sample Collection Date"); ?></option>
															<option value="sample_batch_id"><?php echo _translate("Batch Code"); ?></option>
															<option value="patient_id"><?php echo _translate("Patient ID"); ?></option>
															<option value="patient_name"><?php echo _translate("Patient's Name"); ?></option>
															<option value="facility_id"><?php echo _translate("Facility Name"); ?></option>
															<option value="specimen_type"><?php echo _translate("Sample Type"); ?></option>
															<option value="result"><?php echo _translate("Result"); ?></option>
															<option value="result_status"><?php echo _translate("Status"); ?></option>
														</select>
													</td>
												</tr>

												<tr>
													<td colspan="4">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _translate("Search"); ?>" class="btn btn-success btn-sm">
														&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?= _translate('Reset'); ?></span></button>
														<button class="btn btn-success btn-sm" type="button" onclick="exportDataQualityInexcel()"><em class="fa-solid fa-cloud-arrow-down"></em> <?php echo _translate("Export to excel"); ?></button>
													</td>
												</tr>
											</table>
											<table aria-describedby="table" id="incompleteReport" class="table table-bordered table-striped" aria-hidden="true">
												<thead>
													<tr>
														<th><?php echo _translate("Sample ID"); ?></th>
														<?php if ($_SESSION['instanceType'] != 'standalone') { ?>
															<th><?php echo _translate("Remote Sample ID"); ?></th>
														<?php } ?>
														<th scope="row"><?php echo _translate("Sample Collection Date"); ?></th>
														<th><?php echo _translate("Batch Code"); ?></th>
														<th><?php echo _translate("Patient's Name"); ?></th>
														<th scope="row"><?php echo _translate("Facility Name"); ?></th>
														<th><?php echo _translate("Province/State"); ?></th>
														<th><?php echo _translate("District/County"); ?></th>
														<th><?php echo _translate("Sample Type"); ?></th>
														<th><?php echo _translate("HCV VL Result"); ?></th>
														<th><?php echo _translate("HBV VL Result"); ?></th>
														<th scope="row"><?php echo _translate("Status"); ?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td colspan="12" class="dataTables_empty"><?php echo _translate("Loading data from server"); ?></td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div><!-- /.box-body -->
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
	let searchExecuted = false;
	var oTableViralLoad = null;
	var oTableRjtReport = null;
	var oTablenotAvailReport = null;
	var oTableincompleteReport = null;
	$(document).ready(function() {
		$("#state,#rjtState,#noResultState").select2({
			placeholder: "<?php echo _translate("Select Province"); ?>"
		});
		$("#district,#rjtDistrict,#noResultDistrict").select2({
			placeholder: "<?php echo _translate("Select District"); ?>"
		});
		$("#hvlFacilityName,#rjtFacilityName,#noResultFacilityName").select2({
			placeholder: "<?php echo _translate("Select Facilities"); ?>"
		});
		$("#formField").select2({
			placeholder: "<?php echo _translate("Select Fields"); ?>"
		});
		$('#hvlSampleTestDate,#rjtSampleTestDate,#noResultSampleTestDate,#sampleCollectionDate').daterangepicker({
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
		$('#hvlSampleTestDate,#rjtSampleTestDate,#noResultSampleTestDate,#sampleCollectionDate').val('');
		highViralLoadReport();
		sampleRjtReport();
		notAvailReport();
		incompleteForm();
		$("#highViralLoadReport input, #highViralLoadReport select, #sampleRjtReport input, #sampleRjtReport select, #notAvailReport input, #notAvailReport select, #incompleteFormReport input, #incompleteFormReport select").on("change", function() {
			searchExecuted = false;
		});
	});

	function highViralLoadReport() {
		$.blockUI();
		oTableViralLoad = $('#highViralLoadReportTable').dataTable({
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
				<?php if ($_SESSION['instanceType'] != 'standalone') { ?> {
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
					"sClass": "center",
					"bSortable": false
				},
			],
			"aaSorting": [
				[<?= ($_SESSION['instanceType'] != 'standalone') ? 6 : 5; ?>, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "get-positive-hepatitis-result-details.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "hvlBatchCode",
					"value": $("#hvlBatchCode").val()
				});
				aoData.push({
					"name": "hvlSampleTestDate",
					"value": $("#hvlSampleTestDate").val()
				});
				aoData.push({
					"name": "state",
					"value": $("#state").val()
				});
				aoData.push({
					"name": "district",
					"value": $("#district").val()
				});
				aoData.push({
					"name": "hvlFacilityName",
					"value": $("#hvlFacilityName").val()
				});
				aoData.push({
					"name": "hvlSampleType",
					"value": $("#hvlSampleType").val()
				});
				aoData.push({
					"name": "hvlContactStatus",
					"value": $("#hvlContactStatus").val()
				});
				aoData.push({
					"name": "hvlGender",
					"value": $("#hvlGender").val()
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

	function sampleRjtReport() {
		$.blockUI();
		oTableRjtReport = $('#sampleRjtReportTable').dataTable({
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
				<?php if ($_SESSION['instanceType'] != 'standalone') { ?> {
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
			],
			"aaSorting": [
				[<?= ($_SESSION['instanceType'] != 'standalone') ? 5 : 4; ?>, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "get-sample-rejection-report.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "rjtBatchCode",
					"value": $("#rjtBatchCode").val()
				});
				aoData.push({
					"name": "rjtSampleTestDate",
					"value": $("#rjtSampleTestDate").val()
				});
				aoData.push({
					"name": "rjtState",
					"value": $("#rjtState").val()
				});
				aoData.push({
					"name": "rjtDistrict",
					"value": $("#rjtDistrict").val()
				});
				aoData.push({
					"name": "rjtFacilityName",
					"value": $("#rjtFacilityName").val()
				});
				aoData.push({
					"name": "rjtSampleType",
					"value": $("#rjtSampleType").val()
				});
				aoData.push({
					"name": "rjtGender",
					"value": $("#rjtGender").val()
				});
				aoData.push({
					"name": "sampleRejectionReason",
					"value": $("#sampleRejectionReason").val()
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

	function notAvailReport() {
		$.blockUI();
		oTablenotAvailReport = $('#notAvailReportTable').dataTable({
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
				<?php if ($_SESSION['instanceType'] != 'standalone') { ?> {
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
				}
			],
			"aaSorting": [
				[<?= ($_SESSION['instanceType'] != 'standalone') ? 5 : 4; ?>, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "get-result-not-available.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "noResultBatchCode",
					"value": $("#noResultBatchCode").val()
				});
				aoData.push({
					"name": "noResultSampleTestDate",
					"value": $("#noResultSampleTestDate").val()
				});
				aoData.push({
					"name": "noResultState",
					"value": $("#noResultState").val()
				});
				aoData.push({
					"name": "noResultDistrict",
					"value": $("#noResultDistrict").val()
				});
				aoData.push({
					"name": "noResultFacilityName",
					"value": $("#noResultFacilityName").val()
				});
				aoData.push({
					"name": "noResultSampleType",
					"value": $("#noResultSampleType").val()
				});
				aoData.push({
					"name": "noResultGender",
					"value": $("#noResultGender").val()
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

	function incompleteForm() {
		$.blockUI();
		oTableincompleteReport = $('#incompleteReport').dataTable({
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
				<?php if ($_SESSION['instanceType'] != 'standalone') { ?> {
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
					"sClass": "center"
				},
			],
			"aaSorting": [
				[<?= ($_SESSION['instanceType'] != 'standalone') ? 2 : 1; ?>, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "data-quality-check.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "sampleCollectionDate",
					"value": $("#sampleCollectionDate").val()
				});
				aoData.push({
					"name": "formField",
					"value": $("#formField").val()
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
		searchExecuted = true;
		$.blockUI();
		oTableViralLoad.fnDraw();
		oTableRjtReport.fnDraw();
		oTablenotAvailReport.fnDraw();
		//incompleteForm();
		oTableincompleteReport.fnDraw();
		$.unblockUI();
	}

	function updateStatus(id, value) {
		conf = confirm("<?php echo _translate("Do you wisht to change the contact completed status?"); ?>");
		if (conf) {
			$.post("/vl/program-management/updateContactCompletedStatus.php", {
					id: id,
					value: value
				},
				function(data) {
					alert("<?php echo _translate("Status updated successfully"); ?>");
					oTableViralLoad.fnDraw();
				});
		} else {
			oTableViralLoad.fnDraw();
		}
	}

	function exportHighViralLoadInexcel() {
		if (searchExecuted === false) {
			searchVlRequestData();
		}
		var markAsComplete = false;
		confm = confirm("<?php echo _translate("Do you want to mark these as complete ?"); ?>");
		if (confm) {
			var markAsComplete = true;
		}
		$.blockUI();
		$.post("/hepatitis/management/hepatitis-clinic-result-exportIn-excel.php", {
				Sample_Test_Date: $("#hvlSampleTestDate").val(),
				Batch_Code: $("#hvlBatchCode  option:selected").text(),
				Sample_Type: $("#hvlSampleType  option:selected").text(),
				Facility_Name: $("#hvlFacilityName  option:selected").text(),
				Gender: $("#hvlGender  option:selected").text(),
				markAsComplete: markAsComplete
			},
			function(data) {
				if (data == "" || data == null || data == undefined) {
					$.unblockUI();
					alert("<?php echo _translate("Unable to generate the excel file"); ?>");
				} else {
					$.unblockUI();
					window.open('/download.php?f=' + data, '_blank');
				}
			});
	}

	function exportRejectedResultInexcel() {
		if (searchExecuted === false) {
			searchVlRequestData();
		}
		$.blockUI();
		$.post("/hepatitis/management/hepatitis-rejected-result-exportIn-excel.php", {
				Sample_Test_Date: $("#rjtSampleTestDate").val(),
				Batch_Code: $("#rjtBatchCode  option:selected").text(),
				Sample_Type: $("#rjtSampleType  option:selected").text(),
				Facility_Name: $("#rjtFacilityName  option:selected").text(),
				Gender: $("#rjtGender  option:selected").text()
			},
			function(data) {
				if (data == "" || data == null || data == undefined) {
					$.unblockUI();
					alert("<?php echo _translate("Unable to generate the excel file"); ?>");
				} else {
					$.unblockUI();
					window.open('/download.php?f=' + data, '_blank');
				}
			});
	}

	function exportNotAvailableResultInexcel() {
		if (searchExecuted === false) {
			searchVlRequestData();
		}
		$.blockUI();
		$.post("/hepatitis/management/hepatitis-not-available-result-export-in-excel.php", {
				Sample_Test_Date: $("#noResultSampleTestDate").val(),
				Batch_Code: $("#noResultBatchCode  option:selected").text(),
				Sample_Type: $("#noResultSampleType  option:selected").text(),
				Facility_Name: $("#noResultFacilityName  option:selected").text(),
				Gender: $("#noResultGender  option:selected").text()
			},
			function(data) {
				if (data == "" || data == null || data == undefined) {
					$.unblockUI();
					alert("<?php echo _translate("Unable to generate the excel file"); ?>");
				} else {
					$.unblockUI();
					window.open('/download.php?f=' + data, '_blank');
				}
			});
	}

	function exportDataQualityInexcel() {
		if (searchExecuted === false) {
			searchVlRequestData();
		}
		$.blockUI();
		$.post("/hepatitis/management/hepatitis-data-quality-export-in-excel.php", {
				Sample_Collection_Date: $("#sampleCollectionDate").val(),
				Field_Name: $("#formField  option:selected").text()
			},
			function(data) {
				if (data == "" || data == null || data == undefined) {
					$.unblockUI();
					alert("<?php echo _translate("Unable to generate the excel file"); ?>");
				} else {
					$.unblockUI();
					window.open('/download.php?f=' + data, '_blank');
				}
			});
	}



	function setSampleTestDate(obj) {
		$(".stDate").val($("#" + obj.id).val());
	}

	function getByProvince(districtId, facilityId, provinceId) {
		$("#" + districtId).html('');
		$("#" + facilityId).html('');
		$.post("/common/get-by-province-id.php", {
				provinceId: provinceId,
				districts: true,
				facilities: true,
				facilityCode: true
			},
			function(data) {
				Obj = $.parseJSON(data);
				$("#" + districtId).html(Obj['districts']);
				$("#" + facilityId).html(Obj['facilities']);
			});

	}

	function getByDistrict(facilityId, districtId) {
		$("#" + facilityId).html('');
		$.post("/common/get-by-district-id.php", {
				districtId: districtId,
				facilities: true,
				facilityCode: true
			},
			function(data) {
				Obj = $.parseJSON(data);
				$("#" + facilityId).html(Obj['facilities']);
			});
	}
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
