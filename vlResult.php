
<?php
include('header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Manage VL Result</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage VL Result</li>
      </ol>
    </section>

     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
	    <table cellpadding="1" cellspacing="3" style="margin-left:10%;margin-top:30px;">
		<tr>
		    <td><b>Sample Collection Date&nbsp;:&nbsp;</b></td>
		    <td>
		      <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:200px;background:#fff;"/>
		    </td>
		    <td>&nbsp;&nbsp;<b>Batch Code&nbsp;:&nbsp;</b></td>
		    <td>
			<input type="text" id="batchCode" name="batchCode" class="form-control" placeholder="Enter Batch Code"/>
		    </td>
		    <td>&nbsp;&nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-success btn-sm"></td>
		    <td>&nbsp;&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button></td>
			<td>
			&nbsp;&nbsp;
            <button class="btn btn-info" type="button" onclick="exportInexcel()">Export to excel</button>
	        
			</td>
		</tr>
	    </table>
            
            <!-- /.box-header -->
            <div class="box-body">
              <table id="vlRequestDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
				  <th>Sample Code</th>
                  <th>Batch Code</th>
                  <th>Unique ART No</th>
                  <th>Patient's Name</th>
				  <th>Facility Name</th>
                  <th>Facility Code</th>
                  <th>Sample Type</th>
                  <th>Result</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="10" class="dataTables_empty">Loading data from server</td>
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
  <script type="text/javascript" src="assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript">
   var startDate = "";
   var endDate = "";
  $(document).ready(function() {
     $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
            startDate: moment().subtract('days', 29),
            endDate: moment(),
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
            }
        },
        function(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
      });
     $('#sampleCollectionDate').val("");
     loadVlRequestData();
  } );
  
  var oTable = null;
  function loadVlRequestData(){
     oTable = $('#vlRequestDataTable').dataTable({
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
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center","bSortable":false},
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlResultDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
			  aoData.push({"name": "batchCode", "value": $("#batchCode").val()});
			  aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
              $.ajax({
                  "dataType": 'json',
                  "type": "POST",
                  "url": sSource,
                  "data": aoData,
                  "success": fnCallback
              });
            }
        });
  }
  
  function searchVlRequestData(){
    oTable.fnDraw();
  }
    
  function convertPdf(id){
      $.post("vlRequestPdf.php", { id : id, format: "html"},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('uploads/'+data,'_blank');
	  }
	  
      });
  }
  
  function convertResultToPdf(id){
      $.post("vlRequestResultPdf.php", { id : id},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('uploads/'+data,'_blank');
	  }
	  
      });
  }
  function exportInexcel() {
    $.post("vlResultExportInExcel.php",
    function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('temporary/'+data,'_blank');
	  }
    });
  }
</script>
 <?php
 include('footer.php');
 ?>
