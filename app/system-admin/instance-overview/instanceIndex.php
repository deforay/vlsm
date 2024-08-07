<?php

use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

$title = _translate("System Instance Overview") . " - " . _translate("System Admin");
require_once(APPLICATION_PATH . '/system-admin/admin-header.php');
$instanceValues = "SELECT * FROM s_vlsm_instance";
$data = $db->rawQuery($instanceValues);
?>
<style>
    .ui_tpicker_second_label,
    .ui_tpicker_second_slider,
    .ui_tpicker_millisec_label,
    .ui_tpicker_millisec_slider,
    .ui_tpicker_microsec_label,
    .ui_tpicker_microsec_slider,
    .ui_tpicker_timezone_label,
    .ui_tpicker_timezone {
        display: none !important;
    }

    .ui_tpicker_time_input {
        width: 100%;
    }

    .table td,
    .table th {
        vertical-align: middle !important;
    }
</style>
<link rel="stylesheet" href="/assets/css/bootstrap.3-3-6.min.css">
<script src="/assets/js/jquery.ajax.min.js"></script>
<script src="/assets/js/jquery-ui.js"></script>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> <em class="fa-solid fa-gears"></em> <?php echo _translate("Instance Overview"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/system-admin/edit-config/index.php"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
            <li class="active"><?php echo _translate("Instance Overview"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box box-default">

            <table aria-describedby="table" class="table table-striped table-bordered table-hover" id="example">
                <tbody>
                    <?php
                    if (count($data) > 0) {
                        foreach ($data as $project) {

                    ?>
                            <thead id="<?php echo $project['vlsm_instance_id']; ?>">
                                <tr>
                                    <th><?php echo _translate("Instance Id"); ?></th>
                                    <td><?php echo $project['vlsm_instance_id']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Added On"); ?></th>
                                    <td><?php echo date('d-M-Y H:i:s', strtotime((string) $project['instance_added_on'])); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Updated On"); ?></th>
                                    <td><?php echo date('d-M-Y H:i:s', strtotime((string) $project['instance_update_on'])); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("VL Last Sync"); ?></th>
                                    <td><span class="editSpan vlLastSync"><?php echo date('d-M-Y H:i:s', strtotime((string) $project['vl_last_dash_sync'])); ?></span>
                                        <input class="editInput vlLastSync form-control input-sm date-time" type="text" name="vl_last_dash_sync" value="<?php echo date('d-M-Y H:i:s', strtotime((string) $project['vl_last_dash_sync'])); ?>" readonly style="display: none;background:#fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("EID Last Sync"); ?></th>
                                    <td><span class="editSpan eidLastSync"><?php echo date('d-M-Y H:i:s', strtotime((string) $project['eid_last_dash_sync'])); ?></span>
                                        <input class="editInput eidLastSync form-control input-sm date-time" type="text" name="eid_last_dash_sync" value="<?php echo date('d-M-Y H:i:s', strtotime((string) $project['eid_last_dash_sync'])); ?>" readonly style="display: none;background:#fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Covid-19 Last Sync"); ?></th>
                                    <td><span class="editSpan covid19LastSync"><?php echo date('d-M-Y H:i:s', strtotime((string) $project['covid19_last_dash_sync'])); ?></span>
                                        <input class="editInput covid19LastSync form-control input-sm date-time" type="text" name="covid19_last_dash_sync" value="<?php echo date('d-M-Y H:i:s', strtotime((string) $project['covid19_last_dash_sync'])); ?>" readonly style="display: none;background:#fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Remote Request Last Sync"); ?></th>
                                    <td><span class="editSpan remoteRequestLastSync"><?php echo date('d-M-Y H:i:s', strtotime((string) $project['last_remote_requests_sync'])); ?></span>
                                        <input class="editInput remoteRequestLastSync form-control input-sm date-time" type="text" name="last_remote_requests_sync" value="<?php echo date('d-M-Y H:i:s', strtotime((string) $project['last_remote_requests_sync'])); ?>" readonly style="display: none;background:#fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Remote Results Last Sync"); ?></th>
                                    <td><span class="editSpan remoteResultsLastSync"><?php echo date('d-M-Y H:i:s', strtotime((string) $project['last_remote_results_sync'])); ?></span>
                                        <input class="editInput remoteResultsLastSync form-control input-sm date-time" type="text" name="last_remote_results_sync" value="<?php echo date('d-M-Y H:i:s', strtotime((string) $project['last_remote_results_sync'])); ?>" readonly style="display: none;background:#fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Remote Reference Last Sync"); ?></th>
                                    <td><span class="editSpan remoteReferenceLastSync"><?php echo date('d-M-Y H:i:s', strtotime((string) $project['last_remote_reference_data_sync'])); ?></span>
                                        <input class="editInput remoteReferenceLastSync form-control input-sm date-time" type="text" name="last_remote_reference_data_sync" value="<?php echo date('d-M-Y H:i:s', strtotime((string) $project['last_remote_reference_data_sync'])); ?>" readonly style="display: none;background:#fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo _translate("Action"); ?></th>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-sm btn-default editBtn" style="float: none;"><span class="glyphicon glyphicon-pencil"></span></button>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-success saveBtn" style="float: none; display: none;"><?php echo _translate("Save"); ?></button>
                                        <button type="button" class="btn btn-sm-default cancelBtn" style="float: none; display: none;"><?php echo _translate("Cancel"); ?></button>
                                    </td>
                                </tr>
                            </thead>
                        <?php
                        }
                        ?>
                </tbody>
            <?php } else {
                        echo _translate("No record found");
                    } ?>

            </table>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>

<script>
    $(document).ready(function() {
        $('.editBtn').on('click', function() {
            //hide edit span
            $(this).closest("thead").find(".editSpan").hide();

            //show edit input
            $(this).closest("thead").find(".editInput").show();

            //hide edit button
            $(this).closest("thead").find(".editBtn").hide();

            //show edit button
            $(this).closest("thead").find(".saveBtn").show();
            $(this).closest("thead").find(".cancelBtn").show();

        });

        $('.cancelBtn').on('click', function() {
            //hide save and cancel button
            $(this).closest("thead").find(".saveBtn").hide();
            $(this).closest("thead").find(".cancelBtn").hide();

            //show edit span
            $(this).closest("thead").find(".editSpan").show();

            //hide edit input
            $(this).closest("thead").find(".editInput").hide();

            //show edit button
            $(this).closest("thead").find(".editBtn").show();

        });

        $('.saveBtn').on('click', function() {
            var trObj = $(this).closest("thead");
            var ID = $(this).closest("thead").attr('id');
            var inputData = $(this).closest("thead").find(".editInput").serialize();
            $.ajax({
                type: 'POST',
                url: 'instanceAction.php',
                dataType: "json",
                data: 'action=edit&id=' + ID + '&' + inputData,
                success: function(response) {
                    if (response.status == 'ok') {
                        var vl_last_dash_sync = moment(response.data.vl_last_dash_sync).format('D-MMM-Y HH:mm:ss');
                        var eid_last_dash_sync = moment(response.data.eid_last_dash_sync).format('D-MMM-Y HH:mm:ss');
                        var covid19_last_dash_sync = moment(response.data.covid19_last_dash_sync).format('D-MMM-Y HH:mm:ss');
                        var last_remote_requests_sync = moment(response.data.last_remote_requests_sync).format('D-MMM-Y HH:mm:ss');
                        var last_remote_results_sync = moment(response.data.last_remote_results_sync).format('D-MMM-Y HH:mm:ss');
                        var last_remote_reference_data_sync = moment(response.data.last_remote_reference_data_sync).format('D-MMM-Y HH:mm:ss');
                        trObj.find(".editSpan.instanceName").text(response.data.instance_facility_name);
                        trObj.find(".editSpan.instanceCode").text(response.data.instance_facility_code);
                        trObj.find(".editSpan.vlLastSync").text(vl_last_dash_sync);
                        trObj.find(".editSpan.eidLastSync").text(eid_last_dash_sync);
                        trObj.find(".editSpan.covid19LastSync").text(covid19_last_dash_sync);
                        trObj.find(".editSpan.remoteRequestLastSync").text(last_remote_requests_sync);
                        trObj.find(".editSpan.remoteResultsLastSync").text(last_remote_results_sync);
                        trObj.find(".editSpan.remoteReferenceLastSync").text(last_remote_reference_data_sync);

                        trObj.find(".editInput.instanceName").text(response.data.instance_facility_name);
                        trObj.find(".editInput.instanceCode").text(response.data.instance_facility_code);
                        trObj.find(".editInput.vlLastSync").text(vl_last_dash_sync);
                        trObj.find(".editInput.eidLastSync").text(eid_last_dash_sync);
                        trObj.find(".editInput.covid19LastSync").text(covid19_last_dash_sync);
                        trObj.find(".editInput.remoteRequestLastSync").text(last_remote_requests_sync);
                        trObj.find(".editInput.remoteResultsLastSync").text(last_remote_results_sync);
                        trObj.find(".editInput.remoteReferenceLastSync").text(last_remote_reference_data_sync);

                        trObj.find(".editInput").hide();
                        trObj.find(".saveBtn").hide();
                        trObj.find(".cancelBtn").hide();
                        trObj.find(".editSpan").show();
                        trObj.find(".editBtn").show();
                    } else {
                        alert(response.msg);
                    }
                }
            });
        });

    });
</script>

<?php

require_once(APPLICATION_PATH . '/system-admin/admin-footer.php');
