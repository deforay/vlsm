<?php
$title = "Api Stats";
#require_once('../startup.php'); 
include('../admin-header.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1> <i class="fa fa-gears"></i> Api Stats</h1>
    <ol class="breadcrumb">
      <li><a href="/system-admin/edit-config/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Manage Api Stats</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">


        <div class="box">
          <!-- /.box-header -->
          <div class="box-body">
            <table id="apiStatsDataTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Requested On</th>
                  <th>Number of Records</th>
                  <th>Request Type</th>
                  <th>Test_Type</th>
                  <th>Api Url</th>
                  <th>Date Format</th>
                  
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="dataTables_empty">Loading data from server</td>
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
  $(function() {
    //$("#example1").DataTable();

  });
  $(document).ready(function() {
    $.blockUI();
    oTable = $('#apiStatsDataTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,

      "bRetrieve": true,
      "aoColumns": [{
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
        [0, "desc"]
      ],
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "getApiStatsDetails.php",
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
</script>
<?php
include('../admin-footer.php');

?>