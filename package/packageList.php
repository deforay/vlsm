<?php
$title = "VLSM | Package List";
include('../header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-gears"></i> Package</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Package</li>
      </ol>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <?php if(isset($_SESSION['privileges']) && in_array("addPackage.php", $_SESSION['privileges'])){ ?>
              <a href="addPackage.php" class="btn btn-primary pull-right"> <i class="fa fa-plus"></i> Add Package</a>
	            <?php } ?>
	      <!--<button class="btn btn-primary pull-right" style="margin-right: 1%;" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>-->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="packageDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Package Code</th>
                  <th>No.Of Sample</th>
                  <th>Added On</th>
		              <?php if(isset($_SESSION['privileges']) && in_array("editPackage.php", $_SESSION['privileges'])){ ?>
                  <th>Action</th>
		              <?php } ?>
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
  });
  $(document).ready(function() {
	$.blockUI();
        oTable = $('#packageDataTable').dataTable({	
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            "bStateSave" : true,
            "bRetrieve": true,                        
            "aoColumns": [
                {"sClass":"center"},
                {"sClass":"center","bSortable":false},
                {"sClass":"center"},
		<?php if(isset($_SESSION['privileges']) && in_array("editPackage.php", $_SESSION['privileges'])){ ?>
                {"sClass":"center","bSortable":false},
		<?php } ?>
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getPackageCodeDetails.php",
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
  function generateBarcode(pId){
    $.post("generateBarcode.php",{id:pId},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate barcode');
	  }else{
	      window.open('.././uploads/package_barcode/'+data,'_blank');
	  }
	  
      });
  }
</script>
 <?php
 include('../footer.php');
 ?>
