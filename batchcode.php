<?php
include('header.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Manage Batch List</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage Batch List</li>
      </ol>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header with-border">
	      <?php if(isset($_SESSION['privileges']) && in_array("addBatch.php", $_SESSION['privileges'])){ ?>
              <a href="addBatch.php" class="btn btn-primary pull-right"> <i class="fa fa-plus"></i> Add Batch</a>
	      <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="batchCodeDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Batch Code</th>
                  <th>No. of Samples</th>
                  <th>Created On</th>
				  <th> Status</th>
				  <?php if(isset($_SESSION['privileges']) && in_array("editBatch.php", $_SESSION['privileges'])){ ?>
                  <th>Action</th>
		  <?php } ?>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="3" class="dataTables_empty">Loading data from server</td>
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
	
        oTable = $('#batchCodeDataTable').dataTable({
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
				{"sClass":"center","bSortable":false},
                {"sClass":"center"},
		{"sClass":"center"},
		<?php if(isset($_SESSION['privileges']) && in_array("editBatch.php", $_SESSION['privileges'])){ ?>
                {"sClass":"center","bSortable":false},
		<?php } ?>
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getBatchCodeDetails.php",
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
       
	} );
  function generateBarcode(bId){
    $.post("generateBarcode.php",{id:bId},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('uploads/barcode/'+data,'_blank');
	  }
	  
      });
  }
  function updateStatus(id,value)
  {
    conf = confirm("Do you wisht to change the status?");
    if(conf){
    $.post("updateBatchStatus.php",{id:id,value:value},
      function(data){
	  alert("Status updated successfully");
	  oTable.fnDraw();
      });
    }else{
	   oTable.fnDraw();
	}
  }
</script>
 <?php
 include('footer.php');
 ?>
