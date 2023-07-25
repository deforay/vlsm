<?php

use App\Services\UsersService;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;
use App\Services\GeoLocationsService;

require_once APPLICATION_PATH . '/header.php';
/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var GeoLocationsService $geolocationService */
$geolocationService = ContainerRegistry::get(GeoLocationsService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_GET = $request->getQueryParams();

$fQuery = "SELECT * FROM facility_type";
$fResult = $db->rawQuery($fQuery);
$pResult = $general->fetchDataFromTable('geographical_divisions', "geo_parent = 0 AND geo_status='active'");

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);
$userResult = $usersService->getAllUsers();

$userInfo = [];
foreach ($userResult as $user) {
	$userInfo[$user['user_id']] = ($user['user_name']);
}

$reportFormats = [];
if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] === true) {
	$reportFormats['covid19'] = $general->activeReportFormats('covid-19');
}
if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] === true) {
	$reportFormats['eid'] = $general->activeReportFormats('eid');
}
if (isset(SYSTEM_CONFIG['modules']['vl']) && SYSTEM_CONFIG['modules']['vl'] === true) {
	$reportFormats['vl'] = $general->activeReportFormats('vl');
}

if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] === true) {
	$reportFormats['hepatitis'] = $general->activeReportFormats('hepatitis');
}

if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] === true) {
	$reportFormats['tb'] = $general->activeReportFormats('tb');
}
if (isset(SYSTEM_CONFIG['modules']['generic-tests']) && SYSTEM_CONFIG['modules']['generic-tests'] === true) {
	$reportFormats['generic-tests'] = $general->activeReportFormats('generic-tests');
}
$geoLocationParentArray = $geolocationService->fetchActiveGeolocations();
if (isset($_GET['total'])) {
	$addedRecords = $_GET['total'] - $_GET['notAdded'];
}
?>
<style>
	.ms-choice {
		border: 0px solid #aaa;
	}
</style>
<link href="/assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="/assets/css/jquery.multiselect.css" type="text/css" />
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-hospital"></em>
			<?php echo _("Upload Facilities in Bulk"); ?>
		</h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em>
					<?php echo _("Home"); ?>
				</a></li>
			<li class="active">
				<?php echo _("Facilities"); ?>
			</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="box box-default">
			<div class="box-header with-border">
				<div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span>
					<?php echo _("indicates required field"); ?> &nbsp;
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-horizontal" method='post' name='uploadFacilityForm' id='uploadFacilityForm' autocomplete="off" enctype="multipart/form-data" action="upload-facilities-helper.php">
					<div class="box-body">
						<div class="row">
							<div class="col-md-12">
								<?php if (isset($_GET['total']) && $_GET['total'] > 0) { ?>
									<h3 style="margin-left:100px; color:green;"><?= _("Total number of records in file"); ?> : <?= $_GET['total']; ?> | <?= _("Number of Facilities added"); ?> : <?= $addedRecords; ?> | <?= _("Number of Facilities not added"); ?> : <?= $_GET['notAdded']; ?></h3>
									<?php if ($_GET['notAdded'] > 0) { ?>
										<a class="text-success" style="text-decoration:underline;margin-left:74px; margin-bottom:10px;" href="/temporary/INCORRECT-FACILITY-ROWS.xlsx" download>Download the Excel sheet of incorrect rows of facilities</a><br><br>
									<?php } ?>
								<?php } ?>

								<div class="form-group">
									<label for="facilityName" class="col-lg-2 control-label">
										<?= _("Upload File"); ?> <span class="mandatory">*</span>
									</label>
									<div class="col-lg-8">
										<input type="file" class="form-control isRequired" id="facilitiesInfo" name="facilitiesInfo" placeholder="<?php echo _('Facility Name'); ?>" title="<?= _('Click to upload file'); ?>" onblur='checkNameValidation("facility_details","facility_name",this,null,"<?php echo _("The facility name that you entered already exists.Enter another name"); ?>",null)' />
										<a class="text-primary" style="text-decoration:underline;" href="/files/facilities/Facilities_Bulk_Upload_Excel_Format.xlsx" download><?= _("Click here to download the Excel format for uploading facilities in bulk"); ?></a>
									</div>
								</div>
							</div>

						</div>


					</div>

			</div>
			<!-- /.box-body -->
			<div class="box-footer">
				<input type="hidden" name="selectedUser" id="selectedUser" />
				<a class="btn btn-primary" href="javascript:void(0);" onclick="document.getElementById('uploadFacilityForm').submit();return false;">
					<?php echo _("Submit"); ?>
				</a>
				<a href="facilities.php" class="btn btn-default">
					<?php echo _("Cancel"); ?>
				</a>
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

<?php
require_once APPLICATION_PATH . '/footer.php';