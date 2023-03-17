<?php
$title = _("Print Covid-19 Results");

require_once(APPLICATION_PATH . '/header.php');
/** @var MysqliDb $db */

$batQuery = "SELECT batch_code FROM batch_details where test_type ='covid19' AND batch_status='completed'";

try {
    $batResult = $db->rawQuery($batQuery);
} catch (Exception $e) {
}

$general = new \Vlsm\Models\General();
$facilitiesDb = new \Vlsm\Models\Facilities();
$geoLocationDb = new \Vlsm\Models\GeoLocations();
$healthFacilites = $facilitiesDb->getHealthFacilities('covid19');
$testingLabs = $facilitiesDb->getTestingLabs('covid19');

$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");
$labsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");
$state = $geoLocationDb->getProvinces("yes");

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
        <h1><em class="fa-solid fa-print"></em> <?php echo _("Print Covid-19 Results"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Print Covid-19 Results"); ?></li>
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
                                    <ul id="myTab" class="nav nav-tabs" style="font-size:1.4em;">
                                        <li class="active"><a href="#notPrintedData" data-toggle="tab"><?php echo _("Results not yet Printed"); ?> </a></li>
                                        <li><a href="#printedData" data-toggle="tab"><?php echo _("Results already Printed"); ?> </a></li>
                                    </ul>
                                    <div id="myTabContent" class="tab-content">
                                        <div class="tab-pane fade in active" id="notPrintedData">
                                            <table class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;">
                                                <tr>
                                                    <td><strong><?php echo _("Sample Collection Date"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="<?php echo _('Select Collection Date'); ?>" readonly style="width:220px;background:#fff;" />
                                                    </td>
                                                    <!--    <td><strong><?php echo _("Batch Code"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <select class="form-control" id="batchCode" name="batchCode" title="<?php echo _('Please select batch code'); ?>" style="width:220px;">
                                                            <option value=""> <?php echo _("-- Select --"); ?> </option>
                                                            <?php foreach ($batResult as $code) { ?>
                                                                <option value="<?php echo $code['batch_code']; ?>"><?php echo $code['batch_code']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>-->
                                                    <td><strong><?php echo _("Province/State"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <select class="form-control select2-element" id="state" onchange="getByProvince(this.value)" name="state" title="<?php echo _('Please select Province/State'); ?>">
                                                            <?= $general->generateSelectOptions($state, null, _("-- Select --")); ?>
                                                        </select>
                                                    </td>

                                                    <td><strong><?php echo _("District/County"); ?> :</strong></td>
                                                    <td>
                                                        <select class="form-control select2-element" id="district" name="district" title="<?php echo _('Please select Province/State'); ?>" onchange="getByDistrict(this.value)">
                                                        </select>
                                                    </td>


                                                </tr>
                                                <tr>
                                                    <td><strong><?php echo _("Sample Test Date"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="sampleTestDate" name="sampleTestDate" class="form-control" placeholder="<?php echo _('Select Sample Test Date'); ?>" readonly style="width:220px;background:#fff;" />
                                                    </td>
                                                    <td><strong><?php echo _("Facility Name"); ?> :</strong></td>
                                                    <td>
                                                        <select class="form-control" id="facility" name="facility" title="<?php echo _('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
                                                            <?= $facilitiesDropdown; ?>
                                                        </select>
                                                    </td>
                                                    <td><strong><?php echo _("Testing Labs"); ?> :</strong></td>
                                                    <td>
                                                        <select class="form-control" id="labId" name="labId" title="<?php echo _('Please select testing labs'); ?>" multiple="multiple" style="width:220px;">
                                                            <?= $labsDropdown; ?>
                                                        </select>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td><strong><?php echo _("Patient ID"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="patientId" name="patientId" class="form-control" placeholder="<?php echo _('Enter Patient ID'); ?>" style="background:#fff;" />
                                                    </td>
                                                    <td><strong><?php echo _("Patient Name"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="patientName" name="patientName" class="form-control" placeholder="<?php echo _('Enter Patient Name'); ?>" style="background:#fff;" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _("Search"); ?>" class="btn btn-success btn-sm">
                                                        &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?php echo _("Reset"); ?></span></button>
                                                        &nbsp;<button class="btn btn-default btn-sm" onclick="convertSearchResultToPdf('');"><span><?php echo _("Result PDF"); ?></span></button>
                                                        &nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span><?php echo _("Manage Columns"); ?></span></button>
                                                    </td>
                                                </tr>

                                            </table>
                                            <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
                                                <div class="row" style="background:#e0e0e0;float: right !important;padding: 15px;margin-top: -30px;">
                                                    <div class="col-md-12">
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="1" id="iCol1" data-showhide="sample_code" class="showhideCheckBox" /> <label for="iCol1"><?php echo _("Sample Code"); ?></label>
                                                        </div>
                                                        <?php $i = 1;
                                                        if ($_SESSION['instanceType'] != 'standalone') {
                                                            $i = 2; ?>
                                                            <div class="col-md-3">
                                                                <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i; ?>" id="iCol<?php echo $i; ?>" data-showhide="remote_sample_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Remote Sample Code"); ?></label>
                                                            </div>
                                                        <?php } ?>
                                                       
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_art_no" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Art No"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_first_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Patient's Name"); ?></label> <br>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="facility_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Facility Name"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="lab_id" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Testing Lab"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
															<input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="province" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Province/State"); ?></label>
														</div>
														<div class="col-md-3">
															<input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="district" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("District/Country"); ?></label>
														</div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Sample Type"); ?></label> <br>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="result" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Result"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="last_modified_datetime" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Last Modified On"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="status_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Status"); ?></label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </span>

                                            <table id="notPrintedTable" class="table table-bordered table-striped" aria-hidden="true">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="checkRowsData" onclick="toggleAllVisible()" /></th>
                                                        <th><?php echo _("Sample Code"); ?></th>
                                                        <?php if ($_SESSION['instanceType'] != 'standalone') { ?>
                                                            <th><?php echo _("Remote Sample"); ?> <br /><?php echo _("Code"); ?></th>
                                                        <?php } ?>
                                                        <th><?php echo _("Patient ID"); ?></th>
                                                        <th><?php echo _("Patient Name"); ?></th>
                                                        <th><?php echo _("Facility Name"); ?></th>
                                                        <th><?php echo _("Testing Lab"); ?></th>
                                                        <th><?php echo _("Province/State"); ?></th>
                                                        <th><?php echo _("District/County"); ?></th>
                                                        <th><?php echo _("Sample Type"); ?></th>
                                                        <th><?php echo _("Result"); ?></th>
                                                        <th><?php echo _("Last Modified On"); ?></th>
                                                        <th><?php echo _("Status"); ?></th>
                                                        <th><?php echo _("Action"); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="10" class="dataTables_empty"><?php echo _("Loading data from server"); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <input type="hidden" name="checkedRows" id="checkedRows" />
                                            <input type="hidden" name="totalSamplesList" id="totalSamplesList" />
                                        </div>
                                        <div class="tab-pane fade" id="printedData">
                                            <table class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:98%;">
                                                <tr>
                                                    <td><strong><?php echo _("Sample Collection Date"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="printSampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="<?php echo _('Select Collection Date'); ?>" readonly style="width:220px;background:#fff;" />
                                                    </td>
                                                    <!--  <td><strong><?php echo _("Batch Code"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <select class="form-control" id="printBatchCode" name="batchCode" title="<?php echo _('Please select batch code'); ?>" style="width:220px;">
                                                            <option value=""> <?php echo _("-- Select --"); ?> </option>
                                                            <?php
                                                            foreach ($batResult as $code) {
                                                            ?>
                                                                <option value="<?php echo $code['batch_code']; ?>"><?php echo $code['batch_code']; ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>-->

                                                    <td><strong><?php echo _("Province/State"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <select class="form-control select2-element" id="printState" onchange="getByPrintProvince(this.value)" name="state" title="<?php echo _('Please select Province/State'); ?>">
                                                            <?= $general->generateSelectOptions($state, null, _("-- Select --")); ?>
                                                        </select>
                                                    </td>

                                                    <td><strong><?php echo _("District/County"); ?> :</strong></td>
                                                    <td>
                                                        <select class="form-control select2-element" id="printDistrict" name="district" title="<?php echo _('Please select Province/State'); ?>" onchange="getByPrintDistrict(this.value)">
                                                        </select>
                                                    </td>


                                                </tr>
                                                <tr>
                                                    <td><strong><?php echo _("Sample Test Date"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="printSampleTestDate" name="sampleTestDate" class="form-control" placeholder="<?php echo _('Select Sample Test Date'); ?>" readonly style="width:220px;background:#fff;" />
                                                    </td>
                                                    <td><strong><?php echo _("Facility Name"); ?> :</strong></td>
                                                    <td>
                                                        <select class="form-control" id="printFacility" name="facility" title="<?php echo _('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
                                                            <?= $facilitiesDropdown; ?>
                                                        </select>
                                                    </td>
                                                    <td><strong><?php echo _("Testing Labs"); ?> :</strong></td>
                                                    <td>
                                                        <select class="form-control" id="printLabId" name="printLabId" title="<?php echo _('Please select testing labs'); ?>" multiple="multiple" style="width:220px;">
                                                            <?= $labsDropdown; ?>
                                                        </select>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td><strong><?php echo _("Patient ID"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="printPatientId" name="patientId" class="form-control" placeholder="<?php echo _('Enter Patient ID'); ?>" style="background:#fff;" />
                                                    </td>
                                                    <td><strong><?php echo _("Patient Name"); ?>&nbsp;:</strong></td>
                                                    <td>
                                                        <input type="text" id="printPatientName" name="patientName" class="form-control" placeholder="<?php echo _('Enter Patient Name'); ?>" style="background:#fff;" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6">&nbsp;<input type="button" onclick="searchPrintedVlRequestData();" value="<?php echo _("Search"); ?>" class="btn btn-success btn-sm">
                                                        &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?php echo _("Reset"); ?></span></button>
                                                        &nbsp;<button class="btn btn-default btn-sm" onclick="convertSearchResultToPdf('','printData');"><span><?php echo _("Result PDF"); ?></span></button>
                                                        &nbsp;<button class="btn btn-primary btn-sm" onclick="$('#printShowhide').fadeToggle();return false;"><span><?php echo _("Manage Columns"); ?></span></button>
                                                    </td>
                                                </tr>

                                            </table>
                                            <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="printShowhide" class="">
                                                <div class="row" style="background:#e0e0e0;float: right !important;padding: 15px;margin-top: -30px;">
                                                    <div class="col-md-12">
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="1" id="printiCol1" data-showhide="sample_code" class="printShowhideCheckBox" /> <label for="printiCol1"><?php echo _("Sample Code"); ?></label>
                                                        </div>
                                                        <?php $i = 1;
                                                        if ($_SESSION['instanceType'] != 'standalone') {
                                                            $i = 2; ?>
                                                            <div class="col-md-3">
                                                                <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i; ?>" id="printiCol<?php echo $i; ?>" data-showhide="remote_sample_code" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Remote Sample Code"); ?></label>
                                                            </div>
                                                        <?php } ?>
                                                      
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="patient_art_no" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Art No"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="patient_first_name" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Patient's Name"); ?></label> <br>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="facility_name" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Facility Name"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="lab_id" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Testing Lab"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
															<input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="province" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Province/State"); ?></label>
														</div>
														<div class="col-md-3">
															<input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="district" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("District/Country"); ?></label>
														</div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="sample_name" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Sample Type"); ?></label> <br>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="result" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Result"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="last_modified_datetime" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Last Modified On"); ?></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="checkbox" onclick="javascript:printfnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="printiCol<?php echo $i; ?>" data-showhide="status_name" class="printShowhideCheckBox" /> <label for="printiCol<?php echo $i; ?>"><?php echo _("Status"); ?></label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </span>
                                            <table id="alreadyPrintedTable" class="table table-bordered table-striped" aria-hidden="true">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="checkPrintedRowsData" onclick="toggleAllPrintedVisible()" /></th>
                                                        <th><?php echo _("Sample Code"); ?></th>
                                                        <?php if ($_SESSION['instanceType'] != 'standalone') { ?>
                                                            <th><?php echo _("Remote Sample"); ?> <br /><?php echo _("Code"); ?></th>
                                                        <?php } ?>
                                                        <th><?php echo _("Patient ID"); ?></th>
                                                        <th><?php echo _("Patient Name"); ?></th>
                                                        <th><?php echo _("Facility Name"); ?></th>
                                                        <th><?php echo _("Testing Lab"); ?></th>
                                                        <th><?php echo _("Province/State"); ?></th>
                                                        <th><?php echo _("District/County"); ?></th>
                                                        <th><?php echo _("Sample Type"); ?></th>
                                                        <th><?php echo _("Result"); ?></th>
                                                        <th><?php echo _("Last Modified On"); ?></th>
                                                        <th><?php echo _("Status"); ?></th>
                                                        <th><?php echo _("Action"); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="10" class="dataTables_empty"><?php echo _("Loading data from server"); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <input type="hidden" name="checkedPrintedRows" id="checkedPrintedRows" />
                                            <input type="hidden" name="totalSamplesPrintedList" id="totalSamplesPrintedList" />
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
    var startDate = "";
    var endDate = "";
    var selectedRows = [];
    var selectedRowsId = [];
    var selectedPrintedRows = [];
    var selectedPrintedRowsId = [];
    var oTable = null;
    var opTable = null;
    $(document).ready(function() {
        $("#state, #printState").select2({
            placeholder: "<?php echo _("Select Province"); ?>"
        });
        $("#district, #printDistrict").select2({
            placeholder: "<?php echo _("Select District"); ?>"
        });
        $("#facility,#printFacility, #labId, #printLabId").select2({
            placeholder: "<?php echo _("Select Facilities"); ?>"
        });
        $('#sampleCollectionDate,#sampleTestDate,#printSampleCollectionDate,#printSampleTestDate').daterangepicker({
                locale: {
                    cancelLabel: "<?= _("Clear"); ?>",
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
        $('#sampleCollectionDate,#sampleTestDate,#printSampleCollectionDate,#printSampleTestDate').val("");
        loadVlRequestData();
        loadPrintedVlRequestData();
        $(".showhideCheckBox").change(function() {
            if ($(this).attr('checked')) {
                idpart = $(this).attr('data-showhide');
                $("#" + idpart + "-sort").show();
            } else {
                idpart = $(this).attr('data-showhide');
                $("#" + idpart + "-sort").hide();
            }
        });
        $(".printShowhideCheckBox").change(function() {
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
        $("#printShowhide").hover(function() {}, function() {
            $(this).fadeOut('slow')
        });
        var i = <?php echo $i; ?>;
        for (colNo = 0; colNo <= i; colNo++) {
            $("#iCol" + colNo).attr("checked", oTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
            if (oTable.fnSettings().aoColumns[colNo].bVisible) {
                $("#iCol" + colNo + "-sort").show();
            } else {
                $("#iCol" + colNo + "-sort").hide();
            }
        }
        for (colNo = 0; colNo <= i; colNo++) {
            $("#printiCol" + colNo).attr("checked", opTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
            if (opTable.fnSettings().aoColumns[colNo].bVisible) {
                $("#printiCol" + colNo + "-sort").show();
            } else {
                $("#printiCol" + colNo + "-sort").hide();
            }
        }
    });

    function fnShowHide(iCol) {
        var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
        oTable.fnSetColumnVis(iCol, bVis ? false : true);
    }

    function printfnShowHide(iCol) {
        var bVis = opTable.fnSettings().aoColumns[iCol].bVisible;
        opTable.fnSetColumnVis(iCol, bVis ? false : true);
    }

    function loadVlRequestData() {
        $.blockUI();
        oTable = $('#notPrintedTable').dataTable({
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            "bStateSave": true,
            "iDisplayLength": 100,
            //"bRetrieve": true,                    
            "aoColumns": [{
                    "sClass": "center",
                    "bSortable": false
                },
                {
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
                {
                    "sClass": "center",
                    "bSortable": false
                },
            ],
            <?php if ($_SESSION['instanceType'] != 'standalone') { ?> "aaSorting": [
                    [11, "desc"]
                ],
            <?php } else { ?> "aaSorting": [
                    [10, "desc"]
                ],
            <?php } ?> "fnDrawCallback": function() {
                var checkBoxes = document.getElementsByName("chk[]");
                len = checkBoxes.length;
                for (c = 0; c < len; c++) {
                    if (jQuery.inArray(checkBoxes[c].id, selectedRowsId) != -1) {
                        checkBoxes[c].setAttribute("checked", true);
                    }
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "/covid-19/results/get-results-for-print.php",
            "fnServerData": function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "state",
                    "value": $("#state").val()
                });
                aoData.push({
                    "name": "district",
                    "value": $("#district").val()
                });
                aoData.push({
                    "name": "patientId",
                    "value": $("#patientId").val()
                });
                aoData.push({
                    "name": "patientName",
                    "value": $("#patientName").val()
                });
                aoData.push({
                    "name": "sampleCollectionDate",
                    "value": $("#sampleCollectionDate").val()
                });
                aoData.push({
                    "name": "facilityName",
                    "value": $("#facility").val()
                });
                aoData.push({
                    "name": "labId",
                    "value": $("#labId").val()
                });
                aoData.push({
                    "name": "vlPrint",
                    "value": 'not-print'
                });
                aoData.push({
                    "name": "sampleTestDate",
                    "value": $("#sampleTestDate").val()
                });

                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(json) {
                        $("#totalSamplesList").val(json.iTotalDisplayRecords);
                        fnCallback(json);
                    }
                });
            }
        });
        $.unblockUI();
    }

    function loadPrintedVlRequestData() {
        $.blockUI();
        opTable = $('#alreadyPrintedTable').dataTable({
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
                    "sClass": "center",
                    "bSortable": false
                },
                {
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
                {
                    "sClass": "center",
                    "bSortable": false
                },
            ],
            <?php if ($_SESSION['instanceType'] != 'standalone') { ?> "aaSorting": [
                    [11, "desc"]
                ],
            <?php } else { ?> "aaSorting": [
                    [10, "desc"]
                ],
            <?php } ?> "fnDrawCallback": function() {
                var checkBoxes = document.getElementsByName("chkPrinted[]");
                len = checkBoxes.length;
                for (c = 0; c < len; c++) {
                    if (jQuery.inArray(checkBoxes[c].id, selectedPrintedRowsId) != -1) {
                        checkBoxes[c].setAttribute("checked", true);
                    }
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "/covid-19/results/get-results-for-print.php",
            "fnServerData": function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "state",
                    "value": $("#printState").val()
                });
                aoData.push({
                    "name": "district",
                    "value": $("#printDistrict").val()
                });
                aoData.push({
                    "name": "patientId",
                    "value": $("#printPatientId").val()
                });
                aoData.push({
                    "name": "patientName",
                    "value": $("#printPatientName").val()
                });
                aoData.push({
                    "name": "sampleCollectionDate",
                    "value": $("#printSampleCollectionDate").val()
                });
                aoData.push({
                    "name": "facilityName",
                    "value": $("#prinFacility").val()
                });
                aoData.push({
                    "name": "labId",
                    "value": $("#printLabId").val()
                });
                aoData.push({
                    "name": "vlPrint",
                    "value": 'print'
                });

                aoData.push({
                    "name": "sampleTestDate",
                    "value": $("#printSampleTestDate").val()
                });
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(json) {
                        $("#totalSamplesPrintedList").val(json.iTotalDisplayRecords);
                        fnCallback(json);
                    }
                });
            }
        });
        $.unblockUI();
    }

    function searchVlRequestData() {
        $.blockUI();
        oTable.fnDraw();
        $.unblockUI();
    }

    function searchPrintedVlRequestData() {
        $.blockUI();
        opTable.fnDraw();
        $.unblockUI();
    }

    function resultPDF(id, newData) {
        $.blockUI();
        <?php
        $path = '';
        $path = '/covid-19/results/generate-result-pdf.php';
        ?>
        $.post("<?php echo $path; ?>", {
                source: 'print',
                id: id,
                newData: newData
            },
            function(data) {
                if (data == "" || data == null || data == undefined) {
                    $.unblockUI();
                    alert("<?php echo _("Unable to generate download"); ?>");
                } else {
                    $.unblockUI();
                    oTable.fnDraw();
                    opTable.fnDraw();
                    window.open('/download.php?f=' + data, '_blank');
                }
            });
    }

    function convertSearchResultToPdf(id, newData = null) {
        $.blockUI();
        <?php
        $path = '';
        $path = '/covid-19/results/generate-result-pdf.php';
        ?>
        if (newData == null) {
            var rowsLength = selectedRows.length;
            var totalCount = $("#totalSamplesList").val();
            var checkedRow = $("#checkedRows").val();
        } else {
            var rowsLength = selectedPrintedRows.length;
            var totalCount = $("#totalSamplesPrintedList").val();
            var checkedRow = $("#checkedPrintedRows").val();
        }
        if (rowsLength != 0 && rowsLength > 100) {
            $.unblockUI();
            alert("<?php echo _("You have selected"); ?> " + rowsLength + " <?php echo _("results out of the maximum allowed 100 at a time"); ?>");
            return false;
        } else if (totalCount != 0 && totalCount > 100 && rowsLength == 0) {
            $.unblockUI();
            alert("<?php echo _("Maximum 100 results allowed to print at a time"); ?>");
            return false;
        } else {
            id = checkedRow;
        }
        $.post("<?php echo $path; ?>", {
                source: 'print',
                id: id,
                newData: newData
            },
            function(data) {
                if (data == "" || data == null || data == undefined) {
                    $.unblockUI();
                    alert("<?php echo _("Unable to generate download"); ?>");
                } else {
                    $.unblockUI();
                    if (newData == null) {
                        selectedRows = [];
                        $(".checkRows").prop('checked', false);
                        $("#checkRowsData").prop('checked', false);
                        oTable.fnDraw();
                    } else {
                        selectedPrintedRows = [];
                        $(".checkPrintedRows").prop('checked', false);
                        $("#checkPrintedRowsData").prop('checked', false);
                    }
                    window.open('/download.php?f=' + data, '_blank');
                }
            });
    }

    function checkedRow(obj) {
        if ($(obj).is(':checked')) {
            if ($.inArray(obj.value, selectedRows) == -1) {
                selectedRows.push(obj.value);
                selectedRowsId.push(obj.id);
            }
        } else {
            selectedRows.splice($.inArray(obj.value, selectedRows), 1);
            selectedRowsId.splice($.inArray(obj.id, selectedRowsId), 1);
            $("#checkRowsData").attr("checked", false);
        }
        $("#checkedRows").val(selectedRows.join());
    }

    function checkedPrintedRow(obj) {
        if ($(obj).is(':checked')) {
            if ($.inArray(obj.value, selectedRows) == -1) {
                selectedPrintedRows.push(obj.value);
                selectedPrintedRowsId.push(obj.id);
            }
        } else {
            selectedPrintedRows.splice($.inArray(obj.value, selectedPrintedRows), 1);
            selectedPrintedRowsId.splice($.inArray(obj.id, selectedPrintedRowsId), 1);
            $("#checkPrintedRowsData").attr("checked", false);
        }
        $("#checkedPrintedRows").val(selectedPrintedRows.join());
    }

    function toggleAllVisible() {
        //alert(tabStatus);
        $(".checkRows").each(function() {
            $(this).prop('checked', false);
            selectedRows.splice($.inArray(this.value, selectedRows), 1);
            selectedRowsId.splice($.inArray(this.id, selectedRowsId), 1);
        });
        if ($("#checkRowsData").is(':checked')) {
            $(".checkRows").each(function() {
                $(this).prop('checked', true);
                selectedRows.push(this.value);
                selectedRowsId.push(this.id);
            });
        } else {
            $(".checkRows").each(function() {
                $(this).prop('checked', false);
                selectedRows.splice($.inArray(this.value, selectedRows), 1);
                selectedRowsId.splice($.inArray(this.id, selectedRowsId), 1);
                $("#status").prop('disabled', true);
            });
        }
        $("#checkedRows").val(selectedRows.join());
    }

    function toggleAllPrintedVisible() {
        //alert(tabStatus);
        $(".checkPrintedRows").each(function() {
            $(this).prop('checked', false);
            selectedPrintedRows.splice($.inArray(this.value, selectedPrintedRows), 1);
            selectedPrintedRowsId.splice($.inArray(this.id, selectedPrintedRowsId), 1);
        });
        if ($("#checkPrintedRowsData").is(':checked')) {
            $(".checkPrintedRows").each(function() {
                $(this).prop('checked', true);
                selectedPrintedRows.push(this.value);
                selectedPrintedRowsId.push(this.id);
            });
        } else {
            $(".checkPrintedRows").each(function() {
                $(this).prop('checked', false);
                selectedPrintedRows.splice($.inArray(this.value, selectedPrintedRows), 1);
                selectedPrintedRowsId.splice($.inArray(this.id, selectedPrintedRowsId), 1);
                $("#status").prop('disabled', true);
            });
        }
        $("#checkedPrintedRows").val(selectedPrintedRows.join());
    }

    function getByProvince(provinceId) {
        $("#district").html('');
        $("#facility").html('');
        $("#labId").html('');
        $.post("/common/get-by-province-id.php", {
                provinceId: provinceId,
                districts: true,
                facilities: true,
                labs: true,
            },
            function(data) {
                Obj = $.parseJSON(data);
                $("#district").html(Obj['districts']);
                $("#facility").html(Obj['facilities']);
                $("#labId").html(Obj['labs']);
            });

    }

    function getByPrintProvince(provinceId) {
        $("#printDistrict").html('');
        $("#printFacility").html('');
        $("#printLabId").html('');
        $.post("/common/get-by-province-id.php", {
                provinceId: provinceId,
                districts: true,
                facilities: true,
                labs: true,
            },
            function(data) {
                Obj = $.parseJSON(data);
                $("#printDistrict").html(Obj['districts']);
                $("#printFacility").html(Obj['facilities']);
                $("#printLabId").html(Obj['labs']);
            });

    }

    function getByDistrict(districtId) {
        $("#facility").html('');
        $("#labId").html('');
        $.post("/common/get-by-district-id.php", {
                districtId: districtId,
                facilities: true,
                labs: true,
            },
            function(data) {
                Obj = $.parseJSON(data);
                $("#facility").html(Obj['facilities']);
                $("#labId").html(Obj['labs']);
            });

    }

    function getByPrintDistrict(districtId) {
        $("#printFacility").html('');
        $("#printLabId").html('');
        $.post("/common/get-by-district-id.php", {
                districtId: districtId,
                facilities: true,
                labs: true,
            },
            function(data) {
                Obj = $.parseJSON(data);
                $("#printFacility").html(Obj['facilities']);
                $("#printLabId").html(Obj['labs']);
            });

    }
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
