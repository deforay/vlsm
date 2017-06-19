<?php
include('../header.php');
//include('../includes/MysqliDb.php');
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$sQuery="SELECT * FROM r_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);
$lQuery="SELECT * FROM facility_details where status='active' and facility_type=2";
$lResult = $db->rawQuery($lQuery);
$cQuery="SELECT * FROM facility_details where status='active' and facility_type=1";
$cResult = $db->rawQuery($cQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-book"></i> Sample Rejection Report</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Rejection Result</li>
      </ol>
    </section>

     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
	    <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width:98%;">
		<tr>
		    <td><b>Sample Collection Date&nbsp;:</b></td>
		    <td>
		      <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:220px;background:#fff;"/>
		    </td>
		    <td>&nbsp;<b>Lab &nbsp;:</b></td>
		    <td>
		      <select class="form-control" id="labName" name="labName" title="Please select lab name" style="width:220px;">
		         <option value=""> -- Select -- </option>
				  <?php
				  foreach($lResult as $name){
				  ?>
					<option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']);?></option>
				  <?php
				  }
				  ?>
		      </select>
		    </td>
		</tr>
		<tr>
		    <td>&nbsp;<b>Sample Type&nbsp;:</b></td>
		    <td>
		      <select style="width:220px;" class="form-control" id="sampleType" name="sampleType" title="Please select sample type">
		      <option value=""> -- Select -- </option>
			<?php
			foreach($sResult as $type){
			 ?>
			 <option value="<?php echo $type['sample_id'];?>"><?php echo ucwords($type['sample_name']);?></option>
			 <?php
			}
			?>
		      </select>
		    </td>
		
		    <td>&nbsp;<b>Clinic Name &nbsp;:</b></td>
		    <td>
			  <select class="form-control" id="clinicName" name="clinicName" title="Please select clinic name" multiple="multiple" style="width:220px;">
		         <option value=""> -- Select -- </option>
				  <?php
				  foreach($cResult as $name){
				  ?>
					<option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']);?></option>
				  <?php
				  }
				  ?>
		      </select>
		    </td>
		    
		</tr>
		<tr>
		  <td colspan="4">&nbsp;<input type="button" onclick="searchResultData();" value="Search" class="btn btn-success btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		  </td>
		</tr>
		
	    </table>
            <!-- /.box-header -->
            <div class="box-body" id="pieChartDiv">
              
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
  <script type="text/javascript" src="../assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script src="../assets/js/highchart.js"></script>
  <script>
  $(function () {
    $("#clinicName").select2({placeholder:"Select Clinics"});
    $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
	    separator: ' to ',
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
     searchResultData();
  });
  function searchResultData()
  {
    $.blockUI();
    $.post("../includes/getRejectionResult.php",{sampleCollectionDate:$("#sampleCollectionDate").val(),labName:$("#labName").val(),clinicName:$("#clinicName").val(),sampleType:$("#sampleType").val()},
      function(data){
	  if(data!=''){
	    $("#pieChartDiv").html(data);
	  }
      });
    $.unblockUI();
  }
</script>
 <?php
 include('../footer.php');
 ?>
