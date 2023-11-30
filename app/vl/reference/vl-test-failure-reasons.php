<?php
$title = _translate("VL Test Failure Reasons");

require_once APPLICATION_PATH . '/header.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-flask-vial"></em> <?php echo _translate("VL Test Failure Reasons"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
            <li class="active"><?php echo _translate("VL Test Failure Reasons"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <?php if (!empty($_SESSION['privileges']) && array_key_exists("vl-art-code-details.php", $_SESSION['privileges']) && $sarr['sc_user_type'] != 'vluser') { ?>
                            <a href="add-vl-test-failure-reason.php" class="btn btn-primary pull-right"> <em class="fa-solid fa-plus"></em> <?php echo _translate("Add VL Test Reason"); ?></a>
                        <?php } ?>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table aria-describedby="table" id="vlTestFailureReasonDataTable" class="table table-bordered table-striped" aria-hidden="true">
                            <thead>
                                <tr>
                                    <th scope="row"><?php echo _translate("Failure Reason"); ?></th>
                                    <th scope="row"><?php echo _translate("Status"); ?></th>
                                    <?php if (!empty($_SESSION['privileges']) && array_key_exists("vl-art-code-details.php", $_SESSION['privileges']) && $sarr['sc_user_type'] != 'vluser') { ?>
                                        <th scope="row">Action</th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="dataTables_empty"><?php echo _translate("Loading data from server"); ?></td>
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
<script>
    var oTable = null;
    $(function() {});
    $(document).ready(function() {
        $.blockUI();
        oTable = $('#vlTestFailureReasonDataTable').dataTable({
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            "bStateSave": true,
            "bRetrieve": true,
            "aoColumns": [{
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center",
                    "bSortable": false
                }
            ],
            "aaSorting": [
                [0, "asc"]
            ],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "get-vl-test-failure-reasons-helper.php",
            "fnServerData": function(sSource, aoData, fnCallback) {
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
    });

    function updateStatus(obj, optVal) {
        if (obj.value != '') {
            conf = confirm("<?php echo _translate("Are you sure you want to change the status?"); ?>");
            if (conf) {
                $.post("update-vl-test-failure-reason-status.php", {
                        status: obj.value,
                        id: obj.id
                    },
                    function(data) {
                        if (data > 0) {
                            alert("<?php echo _translate("Updated successfully"); ?>.");
                            oTable.fnDraw();
                        }
                    });
            } else {
                window.top.location.href = window.top.location;
            }
        }
    }
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
