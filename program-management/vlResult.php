<?php
include('../header.php');
//include('../includes/MysqliDb.php');
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
//config  query
$configQuery="SELECT * from global_config";
$configResult=$db->query($configQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$sQuery="SELECT * FROM r_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);
$fQuery="SELECT * FROM facility_details where status='active' and facility_type !=2";
$fResult = $db->rawQuery($fQuery);
$vlLabQuery="SELECT * FROM facility_details where status='active' and facility_type =2";
$vlLabResult = $db->rawQuery($vlLabQuery);
$batQuery="SELECT batch_code FROM batch_details where batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
?>
  <style>
    .select2-selection__choice{
      color:black !important;
    }
  </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-book"></i> Export Result
      <!--<ol class="breadcrumb">-->
      <!--  <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>-->
      <!--  <li class="active">Export Result</li>-->
      <!--</ol>-->
      
      </h1>
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
		    <td><b>Batch Code&nbsp;:</b></td>
		    <td>
		      <select class="form-control" id="batchCode" name="batchCode" title="Please select batch code" style="width:220px;">
		         <option value=""> -- Select -- </option>
			 <?php
			 foreach($batResult as $code){
			  ?>
			  <option value="<?php echo $code['batch_code'];?>"><?php echo $code['batch_code'];?></option>
			  <?php
			 }
			 ?>
		      </select>
		    </td>

		    <td><b>Sample Type&nbsp;:</b></td>
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
		</tr>
		<tr>		
		    <td><b>Facility Name :</b></td>
		    <td>
		      <select class="form-control" id="facilityName" name="facilityName" title="Please select facility name" multiple="multiple" style="width:220px;">
		      <option value=""> -- Select -- </option>
			  <?php
			  foreach($fResult as $name){
			   ?>
			   <option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']."-".$name['facility_code']);?></option>
			   <?php
			  }
			  ?>
		      </select>
		    </td>
		    <td><b>VL Lab :</b></td>
		    <td>
		      <select class="form-control" id="vlLab" name="vlLab" title="Please select vl lab" style="width:220px;">
		      <option value=""> -- Select -- </option>
			  <?php
			  foreach($vlLabResult as $vlLab){
			   ?>
			   <option value="<?php echo $vlLab['facility_id'];?>"><?php echo ucwords($vlLab['facility_name']."-".$vlLab['facility_code']);?></option>
			   <?php
			  }
			  ?>
		      </select>
		    </td>
		    <td><b>Sample Test Date&nbsp;:</b></td>
		    <td>
		      <input type="text" id="sampleTestDate" name="sampleTestDate" class="form-control" placeholder="Select Sample Test Date" readonly style="width:220px;background:#fff;"/>
		    </td>
		</tr>
		<tr>
		    <td><b>Viral Load &nbsp;:</b></td>
		    <td>
		      <select class="form-control" id="vLoad" name="vLoad" title="Please select batch code" style="width:220px;">
		        <option value=""> -- Select -- </option>
			<option value="<=<?php echo $arr['viral_load_threshold_limit'];?>"><= <?php echo $arr['viral_load_threshold_limit'];?> cp/ml</option>
			<option value="><?php echo $arr['viral_load_threshold_limit'];?>">> <?php echo $arr['viral_load_threshold_limit'];?> cp/ml</option>
		      </select>
		    </td>
		    <td><b>Last Print Date&nbsp;:</b></td>
		    <td>
		      <input type="text" id="printDate" name="printDate" class="form-control" placeholder="Select Print Date" readonly style="width:220px;background:#fff;"/>
		    </td>
		    <td><b>Gender&nbsp;:</b></td>
		    <td>
		      <select name="gender" id="gender" class="form-control" title="Please choose gender" style="width:220px;">
			<option value=""> -- Select -- </option>
			<option value="male">Male</option>
			<option value="female">Female</option>
			<option value="not_recorded">Not Recorded</option>
		      </select>
		    </td>
		</tr>
		<tr>
		  <td><b>Status&nbsp;:</b></td>
		  <td>
		      <select name="status" id="status" class="form-control" title="Please choose status">
			<option value=""> -- Select -- </option>
			<option value="7">Accepted</option>
			<option value="4">Rejected</option>
			<option value="6">Awaiting Clinic Approval</option>
		      </select>
		  </td>
		  <td><b>Show only Reordered Samples&nbsp;:</b></td>
		    <td>
		      <select name="showReordSample" id="showReordSample" class="form-control" title="Please choose record sample">
			  <option value=""> -- Select -- </option>
			  <option value="yes">Yes</option>
			  <option value="no" selected="selected">No</option>
		      </select>
		    </td>
		  <td><b>Pregnant&nbsp;:</b></td>
		    <td>
		      <select name="patientPregnant" id="patientPregnant" class="form-control" title="Please choose pregnant option">
			  <option value=""> -- Select -- </option>
			  <option value="yes">Yes</option>
			  <option value="no">No</option>
		      </select>
		    </td>
		</tr>
		<tr>
		  <td colspan="4">
		    &nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		    &nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-default btn-sm">
		    &nbsp;<button class="btn btn-success" type="button" onclick="exportInexcel()"><i class="fa fa-cloud-download" aria-hidden="true"></i> Export to excel</button>
		  </td>
		</tr>
		
	    </table>
	    <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
			<div class="row" style="background:#e0e0e0;padding: 15px;margin-top: -25px;">
			    <div class="col-md-12" >
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code"  class="showhideCheckBox" /> <label for="iCol0">Sample Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="1" id="iCol1" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol1">Batch Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="2" id="iCol2" data-showhide="patient_art_no" class="showhideCheckBox"  /> <label for="iCol2">Art No</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="3" id="iCol3" data-showhide="patient_first_name" class="showhideCheckBox"  /> <label for="iCol3">Patient's Name</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="4" id="iCol4" data-showhide="facility_name" class="showhideCheckBox"  /> <label for="iCol4">Facility Name</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="5" id="iCol5" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol5">Sample Type</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="6" id="iCol6" data-showhide="result"  class="showhideCheckBox" /> <label for="iCol6">Result</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="7" id="iCol7" data-showhide="status_name"  class="showhideCheckBox" /> <label for="iCol7">Status</label>
				    </div>
				    
				</div>
			    </div>
			</span>
            
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
  <script type="text/javascript" src="../assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript">
   var startDate = "";
   var endDate = "";
   var selectedTests=[];
   var selectedTestsId=[];
   var oTable = null;
  $(document).ready(function() {
     $("#facilityName").select2({placeholder:"Select Facilities"});
     $('#sampleCollectionDate,#sampleTestDate,#printDate').daterangepicker({
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
    
     $('#printDate').val("");
     $('#sampleCollectionDate').val("");
     $('#sampleTestDate').val("");
     loadVlRequestData();
     
     $(".showhideCheckBox").change(function(){
            if($(this).attr('checked')){
                idpart = $(this).attr('data-showhide');
                $("#"+idpart+"-sort").show();
            }else{
                idpart = $(this).attr('data-showhide');
                $("#"+idpart+"-sort").hide();
            }
        });
        
        $("#showhide").hover(function(){}, function(){$(this).fadeOut('slow')});
        
        for(colNo=0;colNo <8;colNo++){
            $("#iCol"+colNo).attr("checked",oTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
            if(oTable.fnSettings().aoColumns[colNo].bVisible){
                $("#iCol"+colNo+"-sort").show();    
            }else{
                $("#iCol"+colNo+"-sort").hide();    
            }
        }
  } );
  
  function fnShowHide(iCol)
    {
        var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
        oTable.fnSetColumnVis( iCol, bVis ? false : true );
    }
  
  
  function loadVlRequestData(){
    $.blockUI();
     oTable = $('#vlRequestDataTable').dataTable({
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            //"bStateSave" : true,
            "iDisplayLength": 100,
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
                {"sClass":"center","bSortable":false},
            ],
            "aaSorting": [[ 0, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlResultDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
			  aoData.push({"name": "batchCode", "value": $("#batchCode").val()});
			  aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
			  aoData.push({"name": "sampleTestDate", "value": $("#sampleTestDate").val()});
			  aoData.push({"name": "printDate", "value": $("#printDate").val()});
			  aoData.push({"name": "facilityName", "value": $("#facilityName").val()});
			  aoData.push({"name": "vlLab", "value": $("#vlLab").val()});
			  aoData.push({"name": "sampleType", "value": $("#sampleType").val()});
			  aoData.push({"name": "vLoad", "value": $("#vLoad").val()});
			  aoData.push({"name": "status", "value": $("#status").val()});
			  aoData.push({"name": "gender", "value": $("#gender").val()});
			  aoData.push({"name": "showReordSample", "value": $("#showReordSample").val()});
			  aoData.push({"name": "patientPregnant", "value": $("#patientPregnant").val()});
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
  
  function searchVlRequestData(){
    $.blockUI();
    oTable.fnDraw();
    $.unblockUI();
  }
    
  function convertSearchResultToPdf(id){
    <?php
    $path = '';
    if($arr['vl_form'] == 3){
      $path = '../result-pdf/vlRequestDrcSearchResultPdf.php';
    }else {
      $path = '../result-pdf/vlRequestSearchResultPdf.php'; 
    }
    ?>
      $.post("<?php echo $path; ?>", {source:'print',id : id},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('../uploads/'+data,'_blank');
	  }
      });
  }
  
  function exportInexcel() {
    $.blockUI();
    oTable.fnDraw();
    $.post("vlResultExportInExcel.php",{Sample_Collection_Date:$("#sampleCollectionDate").val(),Batch_Code:$("#batchCode  option:selected").text(),Sample_Type:$("#sampleType  option:selected").text(),Facility_Name:$("#facilityName  option:selected").text(),sample_Test_Date:$("#sampleTestDate").val(),Viral_Load:$("#vLoad  option:selected").text(),Print_Date:$("#printDate").val(),Gender:$("#gender  option:selected").text(),Status:$("#status  option:selected").text(),Show_Reorder_Sample:$("#showReordSample option:selected").text()},
    function(data){
	  if(data == "" || data == null || data == undefined){
	  $.unblockUI();
	      alert('Unable to generate excel..');
	  }else{
		$.unblockUI();
	     location.href = '../temporary/'+data;
	  }
    });
    
  }
</script>
 <?php
 include('../footer.php');
 ?>
