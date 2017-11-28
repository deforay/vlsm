<?php
$title = "VLSM | Print VL Results";
include('../header.php');
//include('../includes/MysqliDb.php');
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$configFormQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
$sQuery="SELECT * FROM r_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$batQuery="SELECT batch_code FROM batch_details where batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
?>
<style>
  .select2-selection__choice{
    color:#000000 !important;
  }
  .center{
    /*text-align:left;*/
  }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Print VL Result</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Print VL Result</li>
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
		      <select class="form-control" id="facility" name="facility" title="Please select facility name" multiple="multiple" style="width:220px;">
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
		  <td><b>Gender&nbsp;:</b></td>
		  <td>
		    <select name="gender" id="gender" class="form-control" title="Please choose gender" style="width:220px;">
		      <option value=""> -- Select -- </option>
		      <option value="male">Male</option>
		      <option value="female">Female</option>
		      <option value="not_recorded">Not Recorded</option>
		    </select>
		  </td>
		  <td><b>ART Number&nbsp;:</b></td>
		  <td>
		    <input type="text" id="artNo" name="artNo" class="form-control" placeholder="ART Number" style="width:220px;" onkeyup="searchVlRequestData()"/>
		  </td>
		</tr>
		<tr>
		    <td><b>Sample Test Date&nbsp;:</b></td>
		    <td>
		      <input type="text" id="sampleTestDate" name="sampleTestDate" class="form-control" placeholder="Select Sample Test Date" readonly style="width:220px;background:#fff;"/>
		    </td>
		    <td colspan="4"></td>
		</tr>
		<tr>
		  <td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-success btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		    &nbsp;<button class="btn btn-default btn-sm" onclick="convertSearchResultToPdf('');"><span>Result PDF</span></button>
		    &nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>
		    </td>
		</tr>
		
	    </table>
	    <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
			<div class="row" style="background:#e0e0e0;float: right !important;padding: 15px;margin-top: -30px;">
			    <div class="col-md-12" >
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="1" id="iCol1" data-showhide="sample_code"  class="showhideCheckBox" /> <label for="iCol1">Sample Code</label>
				    </div>
						<?php $i = 1; if($sarr['user_type']!='standalone'){  $i = 2; ?>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i;?>" id="iCol<?php echo $i;?>" data-showhide="remote_sample_code" class="showhideCheckBox"  /> <label for="iCol<?php echo $i;?>">Remote Sample Code</label>
				    </div>
						<?php } ?>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i;?>">Batch Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="patient_art_no" class="showhideCheckBox"  /> <label for="iCol<?php echo $i;?>">Art No</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="patient_first_name" class="showhideCheckBox"  /> <label for="iCol<?php echo $i;?>">Patient's Name</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="facility_name" class="showhideCheckBox"  /> <label for="iCol<?php echo $i;?>">Facility Name</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i;?>">Sample Type</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="result"  class="showhideCheckBox" /> <label for="iCol<?php echo $i;?>">Result</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="last_modified_datetime"  class="showhideCheckBox" /> <label for="iCol<?php echo $i;?>">Last Modified On</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="<?php echo $i = $i+1;?>" id="iCol<?php echo $i;?>" data-showhide="status_name"  class="showhideCheckBox" /> <label for="iCol<?php echo $i;?>">Status</label>
				    </div>
				    
				</div>
			    </div>
			</span>
           
            <!-- /.box-header -->
            <div class="box-body" style="margin-top:-30px;">
              <table id="vlRequestDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
									<th><input type="checkbox" id="checkRowsData" onclick="toggleAllVisible()"/></th>
		  <th>Sample Code</th>
			<?php if($sarr['user_type']!='standalone'){ ?>
		  <th>Remote Sample <br/>Code</th>
			<?php } ?>
                  <th>Batch Code</th>
                  <th>Unique ART No.</th>
                  <th>Patient's Name</th>
		  <th>Facility Name</th>
                  <th>Sample Type</th>
                  <th>Result</th>
                  <th>Last Modified On</th>
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
							<input type="hidden" name="checkedRows" id="checkedRows"/>
							<input type="hidden" name="totalSamplesList" id="totalSamplesList"/>
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
   var selectedRows=[];
   var selectedRowsId=[];
   var oTable = null;
  $(document).ready(function() {
     $("#facility").select2({placeholder:"Select Facilities"});
     $('#sampleCollectionDate,#sampleTestDate').daterangepicker({
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
     $('#sampleCollectionDate,#sampleTestDate').val("");
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
        var i = '<?php echo $i;?>';
        for(colNo=0;colNo <=i;colNo++){
            $("#iCol"+colNo).attr("checked",oTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
            if(oTable.fnSettings().aoColumns[colNo].bVisible){
                $("#iCol"+colNo+"-sort").show();    
            }else{
                $("#iCol"+colNo+"-sort").hide();    
            }
        }
  } );
  
  function fnShowHide(iCol){
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
            "bStateSave" : true,
            "iDisplayLength": 100,
            "bRetrieve": true,                        
            "aoColumns": [
								{"sClass":"center","bSortable":false},
                {"sClass":"center"},
								<?php if($sarr['user_type']!='standalone'){ ?>
                {"sClass":"center"},
								<?php } ?>
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
            "aaSorting": [[ 7, "desc" ]],
						"fnDrawCallback": function() {
							var checkBoxes=document.getElementsByName("chk[]");
              len = checkBoxes.length;
              for(c=0;c<len;c++){
                if (jQuery.inArray(checkBoxes[c].id, selectedRowsId) != -1 ){
									checkBoxes[c].setAttribute("checked",true);
                  }
                }
							},
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlTestResultDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
			      aoData.push({"name": "batchCode", "value": $("#batchCode").val()});
			      aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
			      aoData.push({"name": "facilityName", "value": $("#facility").val()});
			      aoData.push({"name": "sampleType", "value": $("#sampleType").val()});
			      aoData.push({"name": "vlPrint", "value": 'print'});
			      aoData.push({"name": "gender", "value": $("#gender").val()});
			      aoData.push({"name": "artNo", "value": $("#artNo").val()});
			      aoData.push({"name": "sampleTestDate", "value": $("#sampleTestDate").val()});
              $.ajax({
                  "dataType": 'json',
                  "type": "POST",
                  "url": sSource,
                  "data": aoData,
                  "success": function(json){
                      $("#totalSamplesList").val(json.iTotalDisplayRecords);
											fnCallback(json);
                     }
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
  
  function convertResultToPdf(id){
      $.blockUI();
      <?php
      $path = '';
			$path = '../result-pdf/vlRequestSearchResultPdf.php';
      ?>
      $.post("<?php echo $path; ?>", { source:'print', id : id},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      $.unblockUI();
	      alert('Unable to generate download');
	  }else{
	      $.unblockUI();
	      window.open('../uploads/'+data,'_blank');
	  }
      });
  }
  
  function convertSearchResultToPdf(id){
    $.blockUI();
    <?php
    $path = '';
    $path = '../result-pdf/vlRequestSearchResultPdf.php';
    ?>
		if(selectedRows.length!=0 && selectedRows.length > 20 ){
			$.unblockUI();
			alert("You have selected "+ selectedRows.length +" Sample out of the maximum allowed 20 samples");
			return false;
		}else if($("#totalSamplesList").val()!=0 && $("#totalSamplesList").val() > 20 && selectedRows.length==0){
				$.unblockUI();
				alert("Maximum allowed 20 samples to print.");
				return false;
		}else{
			id = $("#checkedRows").val();
		}
    $.post("<?php echo $path; ?>", { source:'print',id : id},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      $.unblockUI();
	      alert('Unable to generate download');
	  }else{
	      $.unblockUI();
				selectedRows = [];
				$(".checkRows").prop('checked', false);
				$("#checkRowsData").prop('checked', false);
	      window.open('../uploads/'+data,'_blank');
	  }
      });
  }
	function checkedRow(obj){
			if ($(obj).is(':checked')) {
	     if($.inArray(obj.value, selectedRows) == -1){
				selectedRows.push(obj.value);
				selectedRowsId.push(obj.id);
	     }
			} else {
	     selectedRows.splice( $.inArray(obj.value, selectedRows), 1 );
	     selectedRowsId.splice( $.inArray(obj.id, selectedRowsId), 1 );
	     $("#checkRowsData").attr("checked",false);
			}
			$("#checkedRows").val(selectedRows.join());
  }
      
    function toggleAllVisible(){
        //alert(tabStatus);
			$(".checkRows").each(function(){
	     $(this).prop('checked', false);
	     selectedRows.splice( $.inArray(this.value, selectedRows), 1 );
	     selectedRowsId.splice( $.inArray(this.id, selectedRowsId), 1 );
			});
			if ($("#checkRowsData").is(':checked')) {
			$(".checkRows").each(function(){
					$(this).prop('checked', true);
				selectedRows.push(this.value);
				selectedRowsId.push(this.id);
			});
			} else{
			$(".checkRows").each(function(){
	     $(this).prop('checked', false);
	     selectedRows.splice( $.inArray(this.value, selectedRows), 1 );
	     selectedRowsId.splice( $.inArray(this.id, selectedRowsId), 1 );
	     $("#status").prop('disabled', true);
			});
     }
     $("#checkedRows").val(selectedRows.join());
   }
</script>
 <?php
 include('../footer.php');
 ?>