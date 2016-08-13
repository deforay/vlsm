<?php
include('header.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Send Request via Mail</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Result to Mail</li>
      </ol>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header with-border">
              
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="mailDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Batch Code</th>
                  <th>Mail Sent</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="4" class="dataTables_empty">Loading data from server</td>
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
  $(function () {
    //$("#example1").DataTable();
   
  });
  $(document).ready(function() {
	
        oTable = $('#mailDataTable').dataTable({	
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
				{"sClass":"center"},
                {"sClass":"center","bSortable":false},
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlRequestBatchCodeDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
	      aoData.push({"name": "vlResult", "value": 'mail'});
              $.ajax({
                  "dataType": 'json',
                  "type": "POST",
                  "url": sSource,
                  "data": aoData,
                  "success": fnCallback
              });
            }
        });
       
	} );
</script>
 <?php
 include('footer.php');
 ?>
