<?php
include('../header.php');
//include('../includes/MysqliDb.php');
$tsQuery="SELECT * FROM r_testing_status";
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
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$batQuery="SELECT batch_code FROM batch_details where batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1> <i class="fa fa-book"></i> High VL Result</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">High VL Report</li>
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
		    <td>&nbsp;<b>Batch Code&nbsp;:</b></td>
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
		
		    <td>&nbsp;<b>Facility Name & Code&nbsp;:</b></td>
		    <td>
		      <select class="form-control" id="facilityName" name="facilityName" title="Please select facility name" style="width:220px;">
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
		    
		</tr>
		<tr>
		  <td colspan="4">&nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-success btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		    &nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>
		    </td>
		</tr>
		
	    </table>
	    <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
			<div class="row" style="background:#e0e0e0;float: right !important;padding: 15px;margin-top: -30px;">
			    <div class="col-md-12" >
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code"  class="showhideCheckBox" /> <label for="iCol0">Sample Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="1" id="iCol1" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol1">Sample Collection Date</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="2" id="iCol2" data-showhide="art_no" class="showhideCheckBox"  /> <label for="iCol2">Facility Name</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="3" id="iCol3" data-showhide="patient_name" class="showhideCheckBox"  /> <label for="iCol3">Facility Contact No.</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="4" id="iCol4" data-showhide="facility_name" class="showhideCheckBox"  /> <label for="iCol4">Patient Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="5" id="iCol5" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol5">Patient Name</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="6" id="iCol6" data-showhide="result"  class="showhideCheckBox" /> <label for="iCol6">Patient Phone</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="7" id="iCol7" data-showhide="absolute_value"  class="showhideCheckBox" /> <label for="iCol7">Viral Load (absolute > 1000 cp/ml3)</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="8" id="iCol8" data-showhide="contact_notes"  class="showhideCheckBox" /> <label for="iCol8">Contact Notes</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="9" id="iCol9" data-showhide="contact_complete"  class="showhideCheckBox" /> <label for="iCol9">Contact Completed</label>
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
                  <th>Sample Collection Date</th>
		  <th>Facility Name</th>
		  <th>Facility Contact No.</th>
                  <th>Patient Code</th>
                  <th>Patient Name</th>
                  <th>Patient Phone</th>
                  <th>Viral Load (result > <?php echo $arr['viral_load_threshold_limit'];?> cp/ml3)</th>
                  <th>Contact Notes</th>
                  <th>Contact Completed</th>
		  <?php if(isset($_SESSION['privileges']) && in_array("addContactNotes.php", $_SESSION['privileges'])){ ?>
                  <th>Action</th>
		  <?php } ?>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="9" class="dataTables_empty">Loading data from server</td>
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
     $('#sampleCollectionDate').val("");
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
        
        for(colNo=0;colNo <=9;colNo++){
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
                {"sClass":"center"},
		<?php if(isset($_SESSION['privileges']) && in_array("addContactNotes.php", $_SESSION['privileges'])){ ?>
                {"sClass":"center","bSortable":false},
		<?php } ?>
            ],
            "aaSorting": [[ 1, "DESC" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getHighVlResultDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
			      aoData.push({"name": "batchCode", "value": $("#batchCode").val()});
			      aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
			      aoData.push({"name": "facilityName", "value": $("#facilityName").val()});
			      aoData.push({"name": "sampleType", "value": $("#sampleType").val()});
			      aoData.push({"name": "vlPrint", "value": 'print'});
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
  
  function updateStatus(id,value){
    conf = confirm("Do you wisht to change the contact completed status?");
    if(conf){
    $.post("updateContactCompletedStatus.php",{id:id,value:value},
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
 include('../footer.php');
 ?>