<?php

use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\GeoLocationsService;

$title = _translate("Export Data");

require_once APPLICATION_PATH . '/header.php';
/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

/** @var GeoLocationsService $geolocationService */
$geolocationService = ContainerRegistry::get(GeoLocationsService::class);

$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$arr = $general->getGlobalConfig();

$sQuery = "SELECT * FROM r_vl_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);


$healthFacilites = $facilitiesService->getHealthFacilities('vl');
$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");
$testingLabs = $facilitiesService->getTestingLabs('vl');
$testingLabsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");


$batQuery = "SELECT batch_code FROM batch_details where test_type ='vl' AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
//Funding source list
$fundingSourceList = $general->getFundingSources();

//Implementing partner list
$implementingPartnerList = $general->getImplementationPartners();

$state = $geolocationService->getProvinces("yes");
$formId = (int) $general->getGlobalConfig('vl_form');
$lastModifiedColumnPosition =  15;

if ($formId == COUNTRY\CAMEROON) {
	$lastModifiedColumnPosition += 2;
}
?>
<style>
	.select2-selection__choice {
		color: black !important;
	}

	.select2-selection--multiple {
		max-height: 100px;
		width: auto;
		overflow-y: scroll !important;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-book"></em>
			<?php echo _translate("Export Result"); ?>
			<!--<ol class="breadcrumb">-->
			<!--  <li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li>-->
			<!--  <li class="active">Export Result</li>-->
			<!--</ol>-->

		</h1>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box" id="filterDiv">
					<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;">
						<tr>
							<td><strong>
									<?php echo _translate("Sample Collection Date"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control daterangefield" placeholder="<?php echo _translate('Select Collection Date'); ?>" style="width:220px;background:#fff;" />
							</td>
							<td><strong>
									<?php echo _translate("Sample Received at Lab Date"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="sampleReceivedDate" name="sampleReceivedDate" class="form-control daterangefield" placeholder="<?php echo _translate('Select Received Date'); ?>" style="width:220px;background:#fff;" />
							</td>

							<td><strong>
									<?php echo _translate("Sample Type"); ?>&nbsp;:
								</strong></td>
							<td>
								<select style="width:220px;" class="form-control" id="sampleType" name="sampleType" title="<?php echo _translate('Please select sample type'); ?>">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<?php
									foreach ($sResult as $type) {
									?>
										<option value="<?php echo $type['sample_id']; ?>"><?= $type['sample_name']; ?>
										</option>
									<?php
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Province/State"); ?> :
								</strong></td>
							<td>
								<select class="form-control select2-element" id="state" onchange="getByProvince(this.value)" name="state" title="<?php echo _translate('Please select Province/State'); ?>">
									<?= $general->generateSelectOptions($state, null, _translate("-- Select --")); ?>
								</select>
							</td>

							<td><strong>
									<?php echo _translate("District/County"); ?> :
								</strong></td>
							<td>
								<select class="form-control select2-element" id="district" name="district" title="<?php echo _translate('Please select Province/State'); ?>" onchange="getByDistrict(this.value		)">
								</select>
							</td>
							<td><strong>
									<?php echo _translate("Facility Name"); ?> :
								</strong></td>
							<td>
								<select class="form-control" id="facilityName" name="facilityName" title="<?php echo _translate('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
									<?= $facilitiesDropdown; ?>
								</select>
							</td>

						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Testing Lab"); ?> :
								</strong></td>
							<td>
								<select class="form-control" id="vlLab" name="vlLab" title="<?php echo _translate('Please select Testing Lab'); ?>" style="width:220px;">
									<?= $testingLabsDropdown; ?>
								</select>
							</td>
							<td><strong>
									<?php echo _translate("Sample Test Date"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="sampleTestDate" name="sampleTestDate" class="form-control daterangefield" placeholder="<?php echo _translate('Select Sample Test Date'); ?>" readonly style="width:220px;background:#fff;" />
							</td>
							<td><strong>
									<?php echo _translate("Viral Load Suppression"); ?> &nbsp;:
								</strong></td>
							<td>
								<select class="form-control" id="vLoad" name="vLoad" title="Please select Viral Load Suppression" style="width:220px;">
									<option value="">
										<?= _translate("-- Select --"); ?>
									</option>
									<option value="suppressed"><?= _translate("Suppressed"); ?></option>
									<option value="not suppressed"><?= _translate("Not Suppressed"); ?></option>
								</select>
							</td>

						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Last Print Date"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="printDate" name="printDate" class="form-control daterangefield" placeholder="<?php echo _translate('Select Print Date'); ?>" readonly style="width:220px;background:#fff;" />
							</td>
							<td><strong>
									<?php echo _translate("Request Creation Date"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="requestCreatedDatetime" name="requestCreatedDatetime" class="form-control daterangefield" placeholder="<?php echo _translate('Select Request Created Datetime'); ?>" readonly style="width:220px;background:#fff;" />
							</td>
							<td><strong>
									<?php echo _translate("Status"); ?>&nbsp;:
								</strong></td>
							<td>
								<select name="status" id="status" class="form-control" title="<?php echo _translate('Please choose status'); ?>" onchange="checkSampleCollectionDate();">
									<option value=""><?php echo _translate("All Status"); ?></option>
									<option value="<?= SAMPLE_STATUS\ACCEPTED; ?>" selected=selected><?php echo _translate("Accepted"); ?></option>
									<option value="<?= SAMPLE_STATUS\REJECTED; ?>"><?php echo _translate("Rejected"); ?></option>
									<option value="<?= SAMPLE_STATUS\PENDING_APPROVAL; ?>"><?php echo _translate("Awaiting Approval"); ?></option>
									<option value="<?= SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB; ?>"><?php echo _translate("Registered At Testing Lab"); ?></option>
									<option value="<?= SAMPLE_STATUS\EXPIRED ?>"><?php echo _translate("Expired"); ?></option>
									<option value="<?= SAMPLE_STATUS\TEST_FAILED ?>"><?php echo _translate("Failed/Invalid"); ?></option>
								</select>
							</td>

						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Show only Reordered Samples"); ?>&nbsp;:
								</strong></td>
							<td>
								<select name="showReordSample" id="showReordSample" class="form-control" title="<?php echo _translate('Please choose record sample'); ?>">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<option value="yes">
										<?php echo _translate("Yes"); ?>
									</option>
									<option value="no">
										<?php echo _translate("No"); ?>
									</option>
								</select>
							</td>
							<td colspan="2">
								<div class="col-md-12">
									<div class="col-md-6">
										<strong>
											<?php echo _translate("Pregnant"); ?>&nbsp;:
										</strong>
										<select name="patientPregnant" id="patientPregnant" class="form-control" title="<?php echo _translate('Please choose pregnant option'); ?>">
											<option value="">
												<?php echo _translate("-- Select --"); ?>
											</option>
											<option value="yes">
												<?php echo _translate("Yes"); ?>
											</option>
											<option value="no">
												<?php echo _translate("No"); ?>
											</option>
										</select>
									</div>
									<div class="col-md-6">
										<strong>
											<?php echo _translate("Breastfeeding"); ?>&nbsp;:
										</strong>
										<select name="breastFeeding" id="breastFeeding" class="form-control" title="<?php echo _translate('Please choose pregnant option'); ?>">
											<option value="">
												<?php echo _translate("-- Select --"); ?>
											</option>
											<option value="yes">
												<?php echo _translate("Yes"); ?>
											</option>
											<option value="no">
												<?php echo _translate("No"); ?>
											</option>
										</select>
									</div>
								</div>
							</td>
							<td><strong>
									<?php echo _translate("Batch Code"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="batchCode" name="batchCode" class="form-control autocomplete" placeholder="<?php echo _translate('Enter Batch Code'); ?>" style="background:#fff;" />
							</td>

						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Funding Sources"); ?>&nbsp;:
								</strong></td>
							<td>
								<select class="form-control" name="fundingSource" id="fundingSource" title="<?php echo _translate('Please choose funding source'); ?>">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<?php
									foreach ($fundingSourceList as $fundingSource) {
									?>
										<option value="<?php echo base64_encode((string) $fundingSource['funding_source_id']); ?>">
											<?= $fundingSource['funding_source_name']; ?></option>
									<?php } ?>
								</select>
							</td>
							<td><strong>
									<?php echo _translate("Implementing Partners"); ?>&nbsp;:
								</strong></td>
							<td>
								<select class="form-control" name="implementingPartner" id="implementingPartner" title="<?php echo _translate('Please choose implementing partner'); ?>">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<?php
									foreach ($implementingPartnerList as $implementingPartner) {
									?>
										<option value="<?php echo base64_encode((string) $implementingPartner['i_partner_id']); ?>">
											<?= $implementingPartner['i_partner_name']; ?></option>
									<?php } ?>
								</select>
							</td>
							<td><strong>
									<?php echo _translate("Sex"); ?>&nbsp;:
								</strong></td>
							<td><select name="gender" id="gender" class="form-control" title="<?php echo _translate('Please select sex'); ?>" style="width:100%;" onchange="hideFemaleDetails(this.value)">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<option value="male">
										<?php echo _translate("Male"); ?>
									</option>
									<option value="female">
										<?php echo _translate("Female"); ?>
									</option>
									<option value="unreported">
										<?php echo _translate("Unreported"); ?>
									</option>
								</select>
							</td>

						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Community Sample"); ?>&nbsp;:
								</strong></td>
							<td>
								<select name="communitySample" id="communitySample" class="form-control" title="<?php echo _translate('Please choose community sample'); ?>" style="width:100%;">
									<option value="">
										<?php echo _translate("-- Select --"); ?>
									</option>
									<option value="yes">
										<?php echo _translate("Yes"); ?>
									</option>
									<option value="no">
										<?php echo _translate("No"); ?>
									</option>
								</select>

							</td>

							<td><strong>
									<?php echo _translate("Export with Patient ID and Name"); ?>&nbsp;:
								</strong></td>
							<td>
								<select name="patientInfo" id="patientInfo" class="form-control" title="<?php echo _translate('Please choose community sample'); ?>" style="width:100%;">
									<option value="yes">
										<?php echo _translate("Yes"); ?>
									</option>
									<option value="no">
										<?php echo _translate("No"); ?>
									</option>
								</select>

							</td>
							<td><strong>
									<?php echo _translate("Patient ID"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="patientId" name="patientId" class="form-control" placeholder="<?php echo _translate('Enter Patient ID'); ?>" style="background:#fff;" />
							</td>

						</tr>
						<tr>
							<td><strong>
									<?php echo _translate("Patient Name"); ?>&nbsp;:
								</strong></td>
							<td>
								<input type="text" id="patientName" name="patientName" class="form-control" placeholder="<?php echo _translate('Enter Patient Name'); ?>" style="background:#fff;" />
							</td>

						</tr>
						<tr>
							<td colspan="6">
								&nbsp;<button onclick="searchVlRequestData();" value="Search" class="btn btn-primary btn-sm"><span>
										<?php echo _translate("Search"); ?>
									</span></button>

								&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>
										<?php echo _translate("Clear Search"); ?>
									</span></button>

								&nbsp;<button class="btn btn-sm btn-success" type="button" onclick="exportInexcel()"><em class="fa-solid fa-cloud-arrow-down"></em>
									<?php echo _translate("Download"); ?>
								</button>

								&nbsp;<button class="btn btn-sm btn-default pull-right" onclick="$('#showhide').fadeToggle();return false;"><span>
										<?php echo _translate("Manage Columns"); ?>
									</span></button>
							</td>
						</tr>

					</table>
					<span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
						<div class="row" style="background:#e0e0e0;padding: 15px;margin-top: -25px;">
							<div class="col-md-12">
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code" class="showhideCheckBox" /> <label for="iCol0">
										<?php echo _translate("Sample ID"); ?>
									</label>
								</div>
								<?php $i = 0;
								if (!$general->isStandaloneInstance()) {
									$i = 1; ?>
									<div class="col-md-3">
										<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i; ?>" id="iCol<?php echo $i; ?>" data-showhide="remote_sample_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Remote Sample ID"); ?></label>
									</div>
								<?php } ?>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Batch Code"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_art_no" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Art No"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_first_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Patient's Name"); ?></label> <br>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="facility_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Facility Name"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="lab_id" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Lab Name"); ?></label>
								</div>
								<?php if ($formId == COUNTRY\CAMEROON) { ?>
									<div class="col-md-3">
										<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="lab_assigned_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Lab Assigned_code"); ?></label>
									</div>
								<?php } ?>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_collection_date" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Sample Collection Date"); ?></label>
									<br>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Sample Type"); ?></label> <br>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_tested_datetime" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Sample Tested On"); ?></label> <br>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="result" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Result"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="status_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Status"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="funding_source" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Funding Source"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="implementing_partner" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Implementing Partner"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="request_created_datetime" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Request Created On"); ?></label>
								</div>
								<div class="col-md-3">
									<input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="last_modified_on" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _translate("Last Modified On"); ?></label>
								</div>
							</div>
						</div>
					</span>

					<!-- /.box-header -->
					<div class="box-body">
						<table aria-describedby="table" id="vlRequestDataTable" class="table table-bordered table-striped" aria-hidden="true">
							<thead>
								<tr>
									<th>
										<?php echo _translate("Sample ID"); ?>
									</th>
									<?php if (!$general->isStandaloneInstance()) { ?>
										<th>
											<?php echo _translate("Remote Sample ID"); ?>
										</th>
									<?php } ?>
									<th>
										<?php echo _translate("Batch Code"); ?>
									</th>
									<th>
										<?php echo _translate("Unique ART No"); ?>
									</th>
									<th>
										<?php echo _translate("Patient's Name"); ?>
									</th>
									<?php if ($formId == COUNTRY\CAMEROON) { ?>
										<th>
											<?php echo _translate("Universal Health Coverage"); ?>
										</th>
									<?php } ?>
									<th scope="row">
										<?php echo _translate("Facility Name"); ?>
									</th>
									<th>
										<?php echo _translate("Lab Name"); ?>
									</th>
									<?php if ($formId == COUNTRY\CAMEROON) { ?>
										<th>
											<?php echo _translate("Lab Assigned Code"); ?>
										</th>
									<?php } ?>
									<th scope="row">
										<?php echo _translate("Sample Collection Date"); ?>
									</th>
									<th>
										<?php echo _translate("Sample Type"); ?>
									</th>
									<th>
										<?php echo _translate("Sample Tested On"); ?>
									</th>
									<th>
										<?php echo _translate("Result"); ?>
									</th>
									<th scope="row">
										<?php echo _translate("Status"); ?>
									</th>
									<th>
										<?php echo _translate("Funding Source"); ?>
									</th>
									<th>
										<?php echo _translate("Implementing Partner"); ?>
									</th>
									<th>
										<?php echo _translate("Request Created On"); ?>
									</th>
									<th>
										<?php echo _translate("Last Modified On"); ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="13" class="dataTables_empty">
										<?php echo _translate("Loading data from server"); ?>
									</td>
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
<script src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">
	let searchExecuted = false;
	var startDate = "";
	var endDate = "";
	var selectedTests = [];
	var selectedTestsId = [];
	var oTable = null;
	$(document).ready(function() {
		$("#batchCode").autocomplete({
			source: function(request, response) {
				// Fetch data
				$.ajax({
					url: "/batch/getBatchCodeHelper.php",
					type: 'post',
					dataType: "json",
					data: {
						search: request.term,
						type: 'vl'
					},
					success: function(data) {
						response(data);
					}

				});
			}
		});
		$("#state").select2({
			placeholder: "<?php echo _translate("Select Province"); ?>"
		});
		$("#district").select2({
			placeholder: "<?php echo _translate("Select District"); ?>"
		});
		$("#facilityName").select2({
			placeholder: "<?php echo _translate("Select Facilities"); ?>"
		});
		$("#vlLab").select2({
			placeholder: "<?php echo _translate("Select Testing Lab"); ?>"
		});

		$('.daterangefield').daterangepicker({
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
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'Last 90 Days': [moment().subtract(89, 'days'), moment()],
					'Last 120 Days': [moment().subtract(119, 'days'), moment()],
					'Last 180 Days': [moment().subtract(179, 'days'), moment()],
					'Last 12 Months': [moment().subtract(12, 'month').startOf('month'), moment().endOf('month')],
					'Previous Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
					'Current Year To Date': [moment().startOf('year'), moment()]
				}
			},
			function(start, end) {
				startDate = start.format('YYYY-MM-DD');
				endDate = end.format('YYYY-MM-DD');
			});

		$('.daterangefield').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});


		$('#printDate').val("");
		$('#sampleCollectionDate, #requestCreatedDatetime, #sampleReceivedDate').val("");
		//$('#sampleTestDate').val("");

		loadVlRequestData();

		$(".showhideCheckBox").change(function() {
			if ($(this).attr('checked')) {
				idpart = $(this).attr('data-showhide');
				$("#" + idpart + "-sort").show();
			} else {
				idpart = $(this).attr('data-showhide');
				$("#" + idpart + "-sort").hide();
			}
		});

		$("#showhide").hover(function() {}, function() {
			$(this).fadeOut('slow')
		});
		var i = '<?php echo $i; ?>';
		for (colNo = 0; colNo <= i; colNo++) {
			$("#iCol" + colNo).attr("checked", oTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
			if (oTable.fnSettings().aoColumns[colNo].bVisible) {
				$("#iCol" + colNo + "-sort").show();
			} else {
				$("#iCol" + colNo + "-sort").hide();
			}
		}

		$("#filterDiv input, #filterDiv select").on("change", function() {
			searchExecuted = false;
		});

	});

	function fnShowHide(iCol) {
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, bVis ? false : true);
	}

	function loadVlRequestData() {
		$.blockUI();
		oTable = $('#vlRequestDataTable').dataTable({
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bInfo": true,
			"bScrollCollapse": true,
			//"bStateSave" : true,
			"iDisplayLength": 100,
			"bRetrieve": true,
			"aoColumns": [{
					"sClass": "center"
				}, <?php
					if (!$general->isStandaloneInstance()) {
					?> {
						"sClass": "center"
					}, <?php
					} ?> {
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
				<?php if ($formId == COUNTRY\CAMEROON) {
					echo '{
						"sClass": "center"

					},';
				} ?> {
					"sClass": "center"
				},
				<?php if ($formId == COUNTRY\CAMEROON) {
					echo '{
						"sClass": "center"

					},';
				} ?> {
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
				}
			],
			"aaSorting": [
				[<?= $lastModifiedColumnPosition; ?>, "desc"]
			],
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "/vl/program-management/get-data-export.php",
			"fnServerData": function(sSource, aoData, fnCallback) {
				aoData.push({
					"name": "batchCode",
					"value": $("#batchCode").val()
				});
				aoData.push({
					"name": "sampleCollectionDate",
					"value": $("#sampleCollectionDate").val()
				});
				aoData.push({
					"name": "requestCreatedDatetime",
					"value": $("#requestCreatedDatetime").val()
				});
				aoData.push({
					"name": "sampleTestDate",
					"value": $("#sampleTestDate").val()
				});
				aoData.push({
					"name": "sampleReceivedDate",
					"value": $("#sampleReceivedDate").val()
				});
				aoData.push({
					"name": "printDate",
					"value": $("#printDate").val()
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
					"name": "facilityName",
					"value": $("#facilityName").val()
				});
				aoData.push({
					"name": "vlLab",
					"value": $("#vlLab").val()
				});
				aoData.push({
					"name": "sampleType",
					"value": $("#sampleType").val()
				});
				aoData.push({
					"name": "vLoad",
					"value": $("#vLoad").val()
				});
				aoData.push({
					"name": "status",
					"value": $("#status").val()
				});
				aoData.push({
					"name": "gender",
					"value": $("#gender").val()
				});
				aoData.push({
					"name": "communitySample",
					"value": $("#communitySample").val()
				});
				aoData.push({
					"name": "showReordSample",
					"value": $("#showReordSample").val()
				});
				aoData.push({
					"name": "patientPregnant",
					"value": $("#patientPregnant").val()
				});
				aoData.push({
					"name": "breastFeeding",
					"value": $("#breastFeeding").val()
				});
				aoData.push({
					"name": "fundingSource",
					"value": $("#fundingSource").val()
				});
				aoData.push({
					"name": "implementingPartner",
					"value": $("#implementingPartner").val()
				});
				aoData.push({
					"name": "patientId",
					"value": $("#patientId").val()
				});
				aoData.push({
					"name": "patientName",
					"value": $("#patientName").val()
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
		oTable.fnDraw();
		$.unblockUI();
	}

	function exportInexcel() {
		if (searchExecuted === false) {
			searchVlRequestData();
		}
		var withAlphaNum = null;
		$.blockUI();
		oTable.fnDraw();
		$.post('export-vl-results.php', {
				Sample_Collection_Date: $("#sampleCollectionDate").val(),
				Batch_Code: $("#batchCode  option:selected").text(),
				Sample_Type: $("#sampleType  option:selected").text(),
				Facility_Name: $("#facilityName  option:selected").text(),
				sample_Test_Date: $("#sampleTestDate").val(),
				Viral_Load: $("#vLoad  option:selected").text(),
				Print_Date: $("#printDate").val(),
				Sex: $("#gender  option:selected").text(),
				patientInfo: $("#patientInfo  option:selected").val(),
				Community_Sample: $("#communitySample  option:selected").text(),
				Status: $("#status  option:selected").text(),
				Show_Reorder_Sample: $("#showReordSample option:selected").text(),
				withAlphaNum: withAlphaNum
			},
			function(data) {
				if (data == "" || data == null || data == undefined) {
					$.unblockUI();
					alert("<?php echo _translate("Unable to generate excel"); ?>.");
				} else {
					$.unblockUI();
					window.open('/download.php?f=' + data, '_blank');
				}
			});
	}

	function hideFemaleDetails(value) {
		if (value == 'female') {
			$("#patientPregnant").attr("disabled", false);
			$("#breastFeeding").attr("disabled", false);
		} else {
			$('select#patientPregnant').val('');
			$('select#breastFeeding').val('');
			$("#patientPregnant").attr("disabled", true);
			$("#breastFeeding").attr("disabled", true);
		}
	}

	// function convertSearchResultToPdf(id) {
	// 	$.blockUI();
	// 	$.post("/vl/results/generate-result-pdf.php", {
	// 			source: 'export-result',
	// 			id: id
	// 		},
	// 		function(data) {
	// 			$.unblockUI();
	// 			if (data === "" || data === null || data === undefined) {
	// 				alert('Cannot generate Result PDF');
	// 			} else {
	// 				window.open('/download.php?f=' + data, '_blank');
	// 			}
	// 		});
	// }

	function checkSampleCollectionDate() {
		if ($("#sampleCollectionDate").val() == "" && $("#status").val() == 4) {
			alert("<?php echo _translate("Please select Sample Collection Date Range"); ?>");
		} else if ($("#sampleTestDate").val() == "" && $("#status").val() == 7) {
			alert("<?php echo _translate("Please select Sample Test Date Range"); ?>");
		}
	}

	function getByProvince(provinceId) {
		$("#district").html('');
		$("#facilityName").html('');
		$("#vlLab").html('');
		$.post("/common/get-by-province-id.php", {
				provinceId: provinceId,
				districts: true,
				facilities: true,
				labs: true
			},
			function(data) {
				Obj = $.parseJSON(data);
				$("#district").html(Obj['districts']);
				$("#facilityName").html(Obj['facilities']);
				$("#vlLab").html(Obj['labs']);
			});
	}

	function getByDistrict(districtId) {
		$("#facilityName").html('');
		$("#vlLab").html('');
		$.post("/common/get-by-district-id.php", {
				districtId: districtId,
				facilities: true,
				labs: true
			},
			function(data) {
				Obj = $.parseJSON(data);
				$("#facilityName").html(Obj['facilities']);
				$("#vlLab").html(Obj['labs']);
			});
	}
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
