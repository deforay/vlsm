<?php
$title = _("Audit Trail");
require_once(APPLICATION_PATH . '/header.php');

$general = new \Vlsm\Models\General();
?>
<style>
    .select2-selection__choice {
        color: black !important;
    }

    th {
        display: revert !important;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> <?php echo _("Source of Requests Reports"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Source of Requests Reports"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="sampleReportsDataTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo _("Lab Name"); ?></th>
                                    <th><?php echo _("No. of Samples Collected"); ?></th>
                                    <th><?php echo _("No. of Samples with Test Result"); ?></th>
                                    <th><?php echo _("No. of Samples Rejected"); ?></th>
                                    <th><?php echo _("Source of Request"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="dataTables_empty"><?php echo _("Loading data from server"); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
    var oTable = null;
    $(document).ready(function() {

        loadVlRequestData();
        $('#dateRange').daterangepicker({
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
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                    'Last 120 Days': [moment().subtract(119, 'days'), moment()],
                    'Last 180 Days': [moment().subtract(179, 'days'), moment()],
                    'Last 12 Months': [moment().subtract(12, 'month').startOf('month'), moment().endOf('month')]
                }
            },
            function(start, end) {
                startDate = start.format('YYYY-MM-DD');
                endDate = end.format('YYYY-MM-DD');
            });

    });

    function loadVlRequestData() {
        $.blockUI();
        oTable = $('#sampleReportsDataTable').dataTable({
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
            }, {
                "sClass": "center"
            }, {
                "sClass": "center"
            }, {
                "sClass": "center"
            }, {
                "sClass": "center"
            }],
            "aaSorting": [3, "desc"],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "/admin/monitoring/get-sources-of-requests.php",
            "fnServerData": function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "dateRange",
                    "value": $("#dateRange").val()
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
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>