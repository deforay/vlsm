<?php
$title = _("Enter TB Result");
require_once(APPLICATION_PATH . '/header.php');

$general = new \Vlsm\Models\General();
$facilitiesDb = new \Vlsm\Models\Facilities();

$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);

$configFormQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);

$sQuery = "SELECT * FROM r_tb_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);

$fQuery = "SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);

$batQuery = "SELECT batch_code FROM batch_details where test_type ='tb' AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
//check filters
$collectionDate = '';
$batchCode = '';
$sampleType = '';
$facilityName = array();
$gender = '';
$status = 'no_result';
$lastUrl1 = '';
$lastUrl2 = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    $lastUrl1 = strpos($_SERVER['HTTP_REFERER'], "updateVlTestResult.php");
    $lastUrl2 = strpos($_SERVER['HTTP_REFERER'], "vlTestResult.php");
}
if ($lastUrl1 != '' || $lastUrl2 != '') {
    $collectionDate = (isset($_COOKIE['collectionDate']) && $_COOKIE['collectionDate'] != '') ? $_COOKIE['collectionDate'] : '';
    $batchCode = (isset($_COOKIE['batchCode']) && $_COOKIE['batchCode'] != '') ? $_COOKIE['batchCode'] : '';

    $facilityName = (isset($_COOKIE['facilityName']) && $_COOKIE['facilityName'] != '') ? explode(',', $_COOKIE['facilityName']) : array();

    $status = (isset($_COOKIE['status']) && $_COOKIE['status'] != '') ? $_COOKIE['status'] : '';
}
$testingLabs = $facilitiesDb->getTestingLabs('tb');
$testingLabsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");
?>
<style>
    .select2-selection__choice {
        color: black !important;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa-solid fa-pen-to-square"></i> <?php echo _("Enter TB Result Manually"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/dashboard/index.php"><i class="fa-solid fa-chart-pie"></i> <?php echo _("Home"); ?> </a></li>
            <li class="active"><?php echo _("Enter TB Result Manually"); ?></li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width:98%;margin-bottom: 0px;">
                        <tr>
                            <td><b><?php echo _("Sample Collection Date"); ?>&nbsp;:</b></td>
                            <td>
                                <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="<?php echo _('Select Collection Date'); ?>" readonly style="width:220px;background:#fff;" value="<?php echo $collectionDate; ?>" />
                            </td>
                            <td>&nbsp;<b><?php echo _("Batch Code"); ?>&nbsp;:</b></td>
                            <td>
                                <select class="form-control" id="batchCode" name="batchCode" title="<?php echo _('Please select batch code'); ?>" style="width:220px;">
                                    <option value=""> <?php echo _("-- Select --"); ?> </option>
                                    <?php foreach ($batResult as $code) { ?>
                                        <option value="<?php echo $code['batch_code']; ?>" <?php echo ($batchCode == $code['batch_code']) ? "selected='selected'" : "" ?>><?php echo $code['batch_code']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>


                            <td><b><?php echo _("Facility Name"); ?> :</b></td>
                            <td>
                                <select class="form-control" id="facilityName" name="facilityName" title="<?php echo _('Please select facility name'); ?>" multiple="multiple" style="width:220px;">
                                    <option value=""> <?php echo _("-- Select --"); ?> </option>
                                    <?php foreach ($fResult as $name) { ?>
                                        <option value="<?php echo $name['facility_id']; ?>" <?php echo (in_array($name['facility_id'], $facilityName)) ? "selected='selected'" : "" ?>><?php echo ucwords($name['facility_name'] . "-" . $name['facility_code']); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><b><?php echo _("Testing Lab"); ?> :</b></td>
                            <td>
                                <select class="form-control" id="vlLab" name="vlLab" title="<?php echo _('Please select vl lab'); ?>" style="width:220px;">
                                    <?= $testingLabsDropdown; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="<?php echo _("Search"); ?>" class="btn btn-default btn-sm">
                                &nbsp;<button class="btn btn-danger btn-sm" onclick="reset();"><span><?php echo _("Reset"); ?></span></button>
                                &nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span><?php echo _("Manage Columns"); ?></span></button>
                            </td>
                        </tr>
                    </table>
                    <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
                        <div class="row" style="background:#e0e0e0;padding: 15px;margin-top: -5px;">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code" class="showhideCheckBox" /> <label for="iCol0"><?php echo _("Sample Code"); ?></label>
                                </div>
                                <?php $i = 0;
                                if ($sarr['sc_user_type'] != 'standalone') {
                                    $i = 1; ?>
                                    <div class="col-md-3">
                                        <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i; ?>" id="iCol<?php echo $i; ?>" data-showhide="remote_sample_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Remote Sample Code"); ?></label>
                                    </div>
                                <?php } ?>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Batch Code"); ?></label>
                                </div>
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
                                    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Sample Type"); ?></label> <br>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="result" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Result"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="modified_on" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Modified On"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="status_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Status"); ?></label>
                                </div>

                            </div>
                        </div>
                    </span>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="">
                            <select name="status" id="status" class="form-control" title="<?php echo _('Please choose result status'); ?>" style="width:220px;margin-top:30px;" onchange="searchVlRequestData();">
                                <option value=""> <?php echo _("-- Select --"); ?> </option>
                                <option value="no_result" <?php echo ($status == 'no_result') ? "selected='selected'" : "" ?>><?php echo _("Results Not Recorded"); ?></option>
                                <option value="result" <?php echo ($status == 'result') ? "selected='selected'" : "" ?>><?php echo _("Results Recorded"); ?></option>
                                <option value="reject" <?php echo ($status == 'reject') ? "selected='selected'" : "" ?>><?php echo _("Rejected Samples"); ?></option>
                            </select>
                        </div>

                        <br>

                        <table id="vlRequestDataTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo _("Sample Code"); ?></th>
                                    <?php if ($sarr['sc_user_type'] != 'standalone') { ?>
                                        <th><?php echo _("Remote Sample"); ?> <br /><?php echo _("Code"); ?></th>
                                    <?php } ?>
                                    <th><?php echo _("Batch Code"); ?></th>
                                    <th><?php echo _("Facility Name"); ?></th>
                                    <th><?php echo _("Patient ID"); ?></th>
                                    <th><?php echo _("Patient Name"); ?></th>
                                    <th><?php echo _("Result"); ?></th>
                                    <th><?php echo _("Modified On"); ?></th>
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
<script type="text/javascript" src="/assets/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">
    var startDate = "";
    var endDate = "";
    var selectedTests = [];
    var selectedTestsId = [];
    var oTable = null;
    $(document).ready(function() {
        $("#facilityName").select2({
            placeholder: "<?php echo _("Select Facilities"); ?>"
        });
        $("#vlLab").select2({
            placeholder: "<?php echo _("Select Vl Lab"); ?>"
        });
        $('#sampleCollectionDate').daterangepicker({
                locale: {
                    cancelLabel: 'Clear'
                },
                format: 'DD-MMM-YYYY',
                separator: ' to ',
                startDate: moment().subtract(29, 'days'),
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
        <?php
        if (!isset($_COOKIE['collectionDate']) || $_COOKIE['collectionDate'] == '') {
        ?>
            $('#sampleCollectionDate').val("");
        <?php
        } else if (($lastUrl1 != '' || $lastUrl2 != '') && isset($_COOKIE['collectionDate'])) { ?>
            $('#sampleCollectionDate').val("<?php echo $_COOKIE['collectionDate']; ?>");
        <?php } ?>

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
                },
                <?php if ($sarr['sc_user_type'] != 'standalone') { ?> {
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
                    "sClass": "center",
                    "bSortable": false
                },
                {
                    "sClass": "center",
                    "bSortable": false
                }
            ],
            <?php if ($sarr['sc_user_type'] != 'standalone') { ?> "aaSorting": [
                    [7, "desc"]
                ],
            <?php } else { ?> "aaSorting": [
                    [6, "desc"]
                ],
            <?php } ?> "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "tb-samples-for-manual-result-entry.php",
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
                    "name": "facilityName",
                    "value": $("#facilityName").val()
                });
                aoData.push({
                    "name": "vlLab",
                    "value": $("#vlLab").val()
                });
                aoData.push({
                    "name": "status",
                    "value": $("#status").val()
                });

                aoData.push({
                    "name": "from",
                    "value": "enterresult"
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
        $.blockUI();
        oTable.fnDraw();
        document.cookie = "collectionDate=" + $("#sampleCollectionDate").val();
        document.cookie = "batchCode=" + $("#batchCode").val();
        document.cookie = "sampleType=" + $("#sampleType").val();
        document.cookie = "facilityName=" + $("#facilityName").val();
        document.cookie = "gender=" + $("#gender").val();
        document.cookie = "status=" + $("#status").val();
        $.unblockUI();
    }

    function reset() {
        document.cookie = "collectionDate=";
        document.cookie = "batchCode=";
        document.cookie = "sampleType=";
        document.cookie = "facilityName=";
        document.cookie = "gender=";
        document.cookie = "status=";
        window.location.reload();
    }
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>