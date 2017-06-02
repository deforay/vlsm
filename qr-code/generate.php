<?php
include('../header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-qrcode"></i> Generate QR Code</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Generate QR Code</li>
      </ol>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <table id="generateQRCodeDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Batch Code</th>
                  <th>No. of Samples</th>
                  <th>No. of Samples Tested</th>
                  <th>No. of Samples Low Level</th>
                  <th>No. of Samples High Level</th>
                  <th>Last Tested Date</th>
                  <th>Created On</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="8" class="dataTables_empty">Loading data from server</td>
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
  $(document).ready(function() {
    $.blockUI();
        oTable = $('#generateQRCodeDataTable').dataTable({
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            //"bStateSave" : true,
            "bRetrieve": true,
            "aoColumns": [
                {"sClass":"center"},
	        {"sClass":"center","bSortable":false},
	        {"sClass":"center","bSortable":false},
	        {"sClass":"center","bSortable":false},
	        {"sClass":"center","bSortable":false},
	        {"sClass":"center","bSortable":false},
                {"sClass":"center"},
                {"sClass":"center","bSortable":false}
            ],
            "aaSorting": [[ 6, "desc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "../batch/getBatchCodeDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
              aoData.push({"name": "fromSource", "value": 'qr'});
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
  
  function generateQRcode(bId){
     $.blockUI();
     $.post("../qr-code/generateQRCode.php",{id:bId},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate QR code');
	  }else{
	      window.open('../uploads/qrcode/'+data,'_blank');
	  }
	  $.unblockUI();
      });
  }
</script>
 <?php
 include('../footer.php');
 ?>