<?php
include('header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-book"></i> Export Result
      <!--<ol class="breadcrumb">-->
      <!--  <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>-->
      <!--  <li class="active">Export Result</li>-->
      <!--</ol>-->
      <button class="btn btn-info pull-right" type="button" onclick="exportInexcel()">Export to excel</button>
      </h1>
    </section>

     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <table id="vlPatientDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th>Patient's Name</th>
                  <th>Gender</th>
                  <th>DOB</th>
                  <th>Patient ART Number</th>
		  <th>Mobile Number</th>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="5" class="dataTables_empty">Loading data from server</td>
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
     loadVlRequestData();
  } );
  
  function loadVlRequestData(){
    $.blockUI();
     oTable = $('#vlPatientDataTable').dataTable({
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
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlPatientDetails.php",
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
  }
  
  function exportInexcel() {
    $.blockUI();
    $.post("vlPatientExportInExcel.php",
    function(data){
	  if(data == "" || data == null || data == undefined){
	    $.unblockUI();
	    alert('Unable to generate download');
	  }else{
	    $.unblockUI();
	    window.open('temporary/'+data,'_blank');
	  }
    });
  }
  
</script>
 <?php
 include('footer.php');
 ?>
