<?php

use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_GET = $request->getQueryParams();

if (isset($_GET['type']) && $_GET['type'] == 'vl') {
	$title = "Viral Load";
} elseif (isset($_GET['type']) && $_GET['type'] == 'eid') {
	$title = "Early Infant Diagnosis";
} elseif (isset($_GET['type']) && $_GET['type'] == 'covid19') {
	$title = "Covid-19";
} elseif (isset($_GET['type']) && $_GET['type'] == 'hepatitis') {
	$title = "Hepatitis";
} elseif (isset($_GET['type']) && $_GET['type'] == 'tb') {
	$title = "TB";
} elseif (isset($_GET['type']) && $_GET['type'] == 'generic-tests') {
	$title = "Lab Tests";
}

$title = _($title . " | Add Batch");
require_once APPLICATION_PATH . '/header.php';

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);
$healthFacilites = $facilitiesService->getHealthFacilities($_GET['type']);

$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");


//Get active machines
$testPlatformResult = $general->getTestingPlatforms($_GET['type']);
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');
$maxId = $general->createBatchCode();
//Set last machine label order
$machinesLabelOrder = [];
foreach ($testPlatformResult as $machine) {
    $lastOrderQuery = "SELECT label_order from batch_details WHERE machine ='" . $machine['config_id'] . "' ORDER BY request_created_datetime DESC";
    $lastOrderInfo = $db->query($lastOrderQuery);
    if (isset($lastOrderInfo[0]['label_order']) && trim($lastOrderInfo[0]['label_order']) != '') {
        $machinesLabelOrder[$machine['config_id']] = implode(",", json_decode($lastOrderInfo[0]['label_order'], true));
    } else {
        $machinesLabelOrder[$machine['config_id']] = '';
    }
}
//print_r($machinesLabelOrder);
?>
<link href="/assets/css/multi-select.css" rel="stylesheet" />
<style>
    .select2-selection__choice {
        color: #000000 !important;
    }

    #ms-sampleCode {
        width: 100%;
    }

    .showFemaleSection {
        display: none;
    }

    #sortableRow {
        list-style-type: none;
        margin: 30px 0px 30px 0px;
        padding: 0;
        width: 100%;
        text-align: center;
    }

    #sortableRow li {
        color: #333 !important;
        font-size: 16px;
    }

    #alertText {
        text-shadow: 1px 1px #eee;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-pen-to-square"></em> <?php echo _("Create Batch"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Batch"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _("indicates required field"); ?> &nbsp;</div>
            </div>
            <table aria-describedby="table" class="table" aria-hidden="true" style="margin-top:20px;width: 100%;">
                <tr>
                    <th style="width: 20%;" scope="col"><?php echo _("Testing Platform"); ?>&nbsp;<span class="mandatory">*</span> </th>
                    <td style="width: 30%;">
                        <select name="machine" id="machine" class="form-control isRequired" title="<?php echo _('Please choose machine'); ?>">
                            <option value=""> <?php echo _("-- Select --"); ?> </option>
                            <?php foreach ($testPlatformResult as $machine) {
                                $labelOrder = $machinesLabelOrder[$machine['config_id']]; ?>
                                <option value="<?php echo $machine['config_id']; ?>" data-no-of-samples="<?php echo $machine['max_no_of_samples_in_a_batch']; ?>"><?= $machine['machine_name']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <th style="width: 20%;" scope="col"><?php echo _("Facility"); ?></th>
                    <td style="width: 30%;">
                        <select style="width: 100%;" class="form-control" id="facilityName" name="facilityName" title="<?php echo _('Please select facility name'); ?>" multiple="multiple">
                            <?= $facilitiesDropdown; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th style="width: 20%;" scope="col"><?php echo _("Sample Collection Date"); ?></th>
                    <td style="width: 30%;">
                        <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control daterange" placeholder="<?php echo _('Select Collection Date'); ?>" readonly style="width:100%;background:#fff;" />
                    </td>
                    <th style="width: 20%;" scope="col"><?php echo _("Date Sample Receieved at Lab"); ?></th>
                    <td style="width: 30%;">
                        <input type="text" id="sampleReceivedAtLab" name="sampleReceivedAtLab" class="form-control daterange" placeholder="<?php echo _('Select Received at Lab Date'); ?>" readonly style="width:100%;background:#fff;" />
                    </td>
                </tr>
                <tr>
                    <th style="width: 20%;" scope="col"><?php echo _("Positions"); ?></th>
                    <td style="width: 30%;">
                        <select id="positions-type" class="form-control" title="<?php echo _('Please select the postion'); ?>">
                            <option value="numeric"><?php echo _("Numeric"); ?></option>
                            <option value="alpha-numeric"><?php echo _("Alpha Numeric"); ?></option>
                        </select>
                    </td>
                    <th scope="col"></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4">&nbsp;<input type="button" onclick="getSampleCodeDetails();" value="<?php echo _('Filter Samples'); ?>" class="btn btn-success btn-sm">
                        &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?php echo _("Reset Filters"); ?></span></button>
                    </td>
                </tr>
            </table>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="addBatchForm" id="addBatchForm" autocomplete="off" action="save-batch-helper.php">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batchCode" class="col-lg-4 control-label"><?php echo _("Batch Code"); ?> <span class="mandatory">*</span></label>
                                    <div class="col-lg-7" style="margin-left:3%;">
                                        <input type="text" class="form-control isRequired" id="batchCode" name="batchCode" placeholder="<?php echo _('Batch Code'); ?>" title="<?php echo _('Please enter batch code'); ?>" value="<?php echo date('Ymd') . $maxId; ?>" onblur='checkNameValidation("batch_details","batch_code",this,null,"<?php echo _("This batch code already exists.Try another batch code"); ?>",null)' />
                                        <input type="hidden" name="batchCodeKey" id="batchCodeKey" value="<?php echo $maxId; ?>" />
                                        <input type="hidden" name="platform" id="platform" value="" />
                                        <input type="hidden" name="positions" id="positions" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="sampleDetails">
                            <div class="col-md-5">
                                <select name="sampleCode[]" id="search" class="form-control" size="8" multiple="multiple">

                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="button" id="search_rightAll" class="btn btn-block"><em class="fa-solid fa-forward"></em></button>
                                <button type="button" id="search_rightSelected" class="btn btn-block"><em class="fa-sharp fa-solid fa-chevron-right"></em></button>
                                <button type="button" id="search_leftSelected" class="btn btn-block"><em class="fa-sharp fa-solid fa-chevron-left"></em></button>
                                <button type="button" id="search_leftAll" class="btn btn-block"><em class="fa-solid fa-backward"></em></button>
                            </div>

                            <div class="col-md-5">
                                <select name="to[]" id="search_to" class="form-control" size="8" multiple="multiple"></select>
                            </div>
                        </div>
                        <div class="row col-md-12" id="alertText" style="font-size:20px;"></div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="hidden" name="selectedSample" id="selectedSample" />
                        <input type="hidden" name="type" id="type" value="<?php echo $_GET['type'];?>" />
                        <a id="batchSubmit" class="btn btn-primary" href="javascript:void(0);" title="<?php echo _('Please select machine'); ?>" onclick="validateNow();return false;"><?php echo _("Save and Next"); ?></a>
                        <a href="batches.php?type=<?php echo $_GET['type'];?>" class="btn btn-default"> <?php echo _("Cancel"); ?></a>
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
<script type="text/javascript" src="/assets/js/multiselect.min.js"></script>
<script type="text/javascript" src="/assets/js/jasny-bootstrap.js"></script>
<script src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">
    var startDate = "";
    var endDate = "";
    noOfSamples = 0;
    sortedTitle = [];
    $(document).ready(function() {
        $('#search').multiselect({
			search: {
				left: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
				right: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
			},
			fireSearch: function(value) {
				return value.length > 2;
			},
			afterMoveToRight: function($left, $right, $options) {
				const count = $right.find('option').length;
				if (count > 0) {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + count + '/' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				} else {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				}
			},
			afterMoveToLeft: function($left, $right, $options) {
				const count = $right.find('option').length;
				if (count > 0) {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + count + '/' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				} else {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				}
			}
		});
        $("#facilityName").select2({
            placeholder: "<?php echo _("Select Facilities"); ?>"
        });

        $('.daterange').daterangepicker({
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
        $('.daterange').val("");
    });

    function validateNow() {
        var selVal = [];
        $('#search_to option').each(function(i, selected) {
            selVal[i] = $(selected).val();
        });
        $("#selectedSample").val(selVal);
        var selected = $("#machine").find('option:selected');
        noOfSamples = selected.data('no-of-samples');
        if (noOfSamples < selVal.length) {
            alert("<?= _("You have selected more than allowed number of samples"); ?>");
            return false;
        }

        if (selVal == "") {
            alert("<?= _("Please select one or more samples"); ?>");
            return false;
        }

        flag = deforayValidator.init({
            formId: 'addBatchForm'
        });
        if (flag) {
            $("#positions").val($('#positions-type').val());
            $.blockUI();
            document.getElementById('addBatchForm').submit();
        }
    }

    function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
        let removeDots = obj.value.replace(/\./g, "");
        removeDots = removeDots.replace(/\,/g, "");
        removeDots = removeDots.replace(/\s{2,}/g, ' ');

        $.post("/includes/checkDuplicate.php", {
                tableName: tableName,
                fieldName: fieldName,
                value: removeDots.trim(),
                fnct: fnct,
                format: "html"
            },
            function(data) {
                if (data === '1') {
                    alert(alrt);
                    duplicateName = false;
                    document.getElementById(obj.id).value = "";
                }
            });
    }

    function getSampleCodeDetails() {

        var machine = $("#machine").val();
        if (machine == null || machine == '') {
            $.unblockUI();
            alert("<?php echo _("You have to choose a testing platform to proceed"); ?>");
            return false;
        }
        var fName = $("#facilityName").val();

        $.blockUI();
        $.post("get-samples-batch.php", {
                sampleCollectionDate: $("#sampleCollectionDate").val(),
                sampleReceivedAtLab: $("#sampleReceivedAtLab").val(),
                type : '<?php echo $_GET['type'];?>',
                fName: fName
            },
            function(data) {
                if (data != "") {
                    $("#sampleDetails").html(data);
                }
            });
        $.unblockUI();
    }

    $("#machine").change(function() {
        var self = this.value;
        if (self != '') {
            getSampleCodeDetails();
            $("#platform").val($("#machine").val());
            var selected = $(this).find('option:selected');
            noOfSamples = selected.data('no-of-samples');
            $('#alertText').html("<?php echo _("You have picked"); ?> " + $("#machine option:selected").text() + " <?php echo _("testing platform and it has limit of maximum"); ?> " + noOfSamples + " <?php echo _("samples per batch"); ?>");
        } else {
            $('.ms-list').html('');
            $('#alertText').html('');
        }
    });
</script>
<?php require_once APPLICATION_PATH . '/footer.php';