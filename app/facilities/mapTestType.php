<?php
ob_start();
$type = (!empty($_GET['type'])) ? $_GET['type'] : $_GET['type'];
if ($type == 'health-facilities') {
	$title = "Manage Health Facilities";
} else if ($type == 'testing-labs') {
	$title = "Manage Testing Labs";
}

require_once(APPLICATION_PATH . '/header.php');



?>
<link href="/assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
<link href="/assets/css/multi-select.css" rel="stylesheet" />
<style>
	.select2-selection__choice {
		color: #000000 !important;
	}

	.boxWidth,
	.eid_boxWidth {
		width: 10%;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa-solid fa-hospital"></i> <?php echo $title; ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa-solid fa-chart-pie"></i> <?php echo _("Home");?></a></li>
			<li class="active"> <?php echo $title; ?></li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="box box-default">
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-horizontal" method='post' name='facilityTestMapForm' id='facilityTestMapForm' enctype="multipart/form-data" autocomplete="off" action="mapTestTypeHelper.php">
					<div class="box-body">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"> <?php echo $title; ?></h3>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-md-7">
										<div class="form-group">
											<label for="testType" class="col-lg-4 control-label"><?php echo _("Test Type");?></label>
											<div class="col-lg-8">
												<select type="text" class="form-control" id="testType" name="testType" title="<?php echo _('Choose one test type');?>" onchange="selectedTestType(this.value);">
													<option value=""><?php echo _("--Select--");?></option>
													<?php if (isset(SYSTEM_CONFIG['modules']['vl']) && SYSTEM_CONFIG['modules']['vl'] == true) { ?>
														<option value="vl"><?php echo _("Viral Load");?></option>
													<?php }
													if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] == true) { ?>
														<option value="eid"><?php echo _("Early Infant Diagnosis");?></option>
													<?php }
													if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] == true) { ?>
														<option value="covid19"><?php echo _("Covid-19");?></option>
													<?php }
													if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] == true) { ?>
														<option value='hepatitis'><?php echo _("Hepatitis");?></option>
													<?php }
													if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] == true) { ?>
														<option value='tb'><?php echo _("TB");?></option>
													<?php } ?>
												</select>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-7">
										<div class="form-group">
											<label for="facilities" class="col-lg-4 control-label"><?php echo _("Select the");?> <?php echo str_replace("Manage", "", $title); ?> <?php echo _("for test type");?> </label>
											<div class="col-lg-8">
												<div class="form-group">
													<div class="col-md-12">
														<div class="row">
															<div class="col-md-12" style="text-align:justify;">
																<!-- <code>If any of the selected fields are incomplete, the Result PDF appears with a <strong>DRAFT</strong> watermark. Leave right block blank (Deselect All) to disable this.</code> -->
															</div>
														</div>
														<div style="width:100%;margin:10px auto;clear:both;">
															<a href="#" id="select-all-field" style="float:left;" class="btn btn-info btn-xs"><?php echo _("Select All");?>&nbsp;&nbsp;<i class="icon-chevron-right"></i></a> <a href="#" id="deselect-all-field" style="float:right;" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;<?php echo _("Deselect All");?></a>
														</div><br /><br />
														<select id="facilities" name="facilities[]" multiple="multiple" class="search">
														</select>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /.box-body -->
						<div class="box-footer">
							<input type="hidden" name="facilityType" class="form-control" id="facilityType" value="<?php echo $type; ?>" />
							<input type="hidden" name="mappedFacilities" id="mappedFacilities" value="" />
							<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _("Submit");?></a>
							<a href="facilities.php" class="btn btn-default"> <?php echo _("Cancel");?></a>
						</div>
						<!-- /.box-footer -->
					</div>
				</form>
				<!-- /.row -->
			</div>

		</div>
		<!-- /.box -->

	</section>
	<!-- /.content -->
</div>
<script type="text/javascript" src="/assets/js/jasny-bootstrap.js"></script>
<script src="/assets/js/jquery.multi-select.js"></script>
<script src="/assets/js/jquery.quicksearch.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		init();

		$('#select-all-field').click(function() {
			$('#facilities').multiSelect('select_all');
			return false;
		});
		$('#deselect-all-field').click(function() {
			$('#facilities').multiSelect('deselect_all');
			return false;
		});
	});

	function validateNow() {

		let mappedFacilities = JSON.stringify($("#facilities").val());
		$("#mappedFacilities").val(mappedFacilities);
		$("#facilities").val(""); // THIS IS IMPORTANT. TO REDUCE NUMBER OF PHP VARIABLES		
		flag = deforayValidator.init({
			formId: 'facilityTestMapForm'
		});

		if (flag) {
			$.blockUI();
			document.getElementById('facilityTestMapForm').submit();
		}
	}

	function selectedTestType(val) {
		$.blockUI({
			message: '<h3><?php echo _("Trying to get mapped facilities");?> <br><?php echo _("Please wait");?>...</h3>'
		});
		$.post("getTestTypeFacilitiesHelper.php", {
				facilityType: $('#facilityType').val(),
				testType: $('#testType').val()
			},
			function(toAppend) {
				if (toAppend != "") {
					if (toAppend != null && toAppend != undefined) {
						$('.search').html(toAppend)
						$('.search').multiSelect('refresh');
						$.unblockUI();
					}
				}
			});
	}

	function init() {

		$('.search').multiSelect({
			selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='<?php echo _("Enter Field Name");?>'>",
			selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='<?php echo _("Enter Field Name");?>'>",
			afterInit: function(ms) {
				var that = this,
					$selectableSearch = that.$selectableUl.prev(),
					$selectionSearch = that.$selectionUl.prev(),
					selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
					selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

				that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
					.on('keydown', function(e) {
						if (e.which === 40) {
							that.$selectableUl.focus();
							return false;
						}
					});

				that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
					.on('keydown', function(e) {
						if (e.which == 40) {
							that.$selectionUl.focus();
							return false;
						}
					});
			},
			afterSelect: function() {
				this.qs1.cache();
				this.qs2.cache();
			},
			afterDeselect: function() {
				this.qs1.cache();
				this.qs2.cache();
			}
		});
	}
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>