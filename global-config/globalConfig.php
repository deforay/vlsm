<?php
include('../header.php');
?>
<style>
  #globalConfigDataTable_length{display: none;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-gears"></i> General Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">General Configuration</li>
      </ol>
    </section>

     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
	      <?php if(isset($_SESSION['privileges']) && in_array("editGlobalConfig.php", $_SESSION['privileges'])){ ?>
              <a href="editGlobalConfig.php" class="btn btn-primary pull-right"> <i class="fa fa-pencil"></i> Edit General Config</a>
	      <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="globalConfigDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th>Config Name</th>
                  <th>Value</th>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="2" class="dataTables_empty">Loading data from server</td>
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
  <script type="text/javascript">
  var oTable = null;  
  $(document).ready(function() {
    $.blockUI();
     oTable = $('#globalConfigDataTable').dataTable({
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            
            "bRetrieve": true,                        
            "aoColumns": [
                {"sClass":"center"},
                {"sClass":"center"}
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getGlobalConfigDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
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
  } );
</script>
 <?php
 include('../footer.php');
 ?>
