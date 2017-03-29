<?php
include('../header.php');
//include('../includes/MysqliDb.php');
$tsQuery="SELECT * FROM r_testing_status";
$tsResult = $db->rawQuery($tsQuery);
$configFormQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
$sQuery="SELECT * FROM r_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$batQuery="SELECT batch_code FROM batch_details where batch_status='completed'";
$batResult = $db->rawQuery($batQuery);

//check filters
$collectionDate = '';$batchCode = '';$sampleType = '';$facilityName = '';$gender = '';$status ='';
$lastUrl = strpos($_SERVER['HTTP_REFERER'],"updateVlTestResult.php");
$lastUrl1 = strpos($_SERVER['HTTP_REFERER'],"vlTestResult.php");
if($lastUrl!='' || $lastUrl1!=''){
$collectionDate=(isset($_COOKIE['collectionDate']) && $_COOKIE['collectionDate']!='' ? $_COOKIE['collectionDate'] :  '');
$batchCode=(isset($_COOKIE['batchCode']) && $_COOKIE['batchCode']!='' ? $_COOKIE['batchCode'] :  '');
$sampleType=(isset($_COOKIE['sampleType']) && $_COOKIE['sampleType']!='' ? $_COOKIE['sampleType'] :  '');
$facilityName=(isset($_COOKIE['facilityName']) && $_COOKIE['facilityName']!='' ? $_COOKIE['facilityName'] :  '');
$gender=(isset($_COOKIE['gender']) && $_COOKIE['gender']!='' ? $_COOKIE['gender'] :  '');
$status=(isset($_COOKIE['status']) && $_COOKIE['status']!='' ? $_COOKIE['status'] :  '');
}
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Enter VL Result</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home <?php echo strpos($_SERVER['HTTP_REFERER'],"updateVlTestResult.php");?></a></li>
        <li class="active">Enter VL Result</li>
      </ol>
    </section>

     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
	    <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width:98%;margin-bottom: 0px;">
		<tr>
		    <td><b>Sample Collection Date&nbsp;:</b></td>
		    <td>
		      <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:220px;background:#fff;" value="<?php echo $collectionDate;?>"/>
		    </td>
		    <td>&nbsp;<b>Batch Code&nbsp;:</b></td>
		    <td>
		      <select class="form-control" id="batchCode" name="batchCode" title="Please select batch code" style="width:220px;">
		        <option value=""> -- Select -- </option>
			 <?php
			 foreach($batResult as $code){
			  ?>
			  <option value="<?php echo $code['batch_code'];?>"<?php echo ($batchCode==$code['batch_code'])?"selected='selected'":""?> ><?php echo $code['batch_code'];?></option>
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
			 <option value="<?php echo $type['sample_id'];?>"<?php echo ($sampleType==$type['sample_id'])?"selected='selected'":""?>><?php echo ucwords($type['sample_name']);?></option>
			 <?php
			}
			?>
		      </select>
		    </td>
		</tr>
		<tr>
		    <td><b>Facility :</b></td>
		    <td>
		      <select class="form-control" id="facilityName" name="facilityName" title="Please select facility name" style="width:220px;">
		      <option value=""> -- Select -- </option>
			<?php
			foreach($fResult as $name){
			 ?>
			 <option value="<?php echo $name['facility_id'];?>"<?php echo ($facilityName==$name['facility_id'])?"selected='selected'":""?>><?php echo ucwords($name['facility_name']."-".$name['facility_code']);?></option>
			 <?php
			}
			?>
		      </select>
		    </td>
		    
		
		  <td><b>Gender&nbsp;:</b></td>
		  <td>
		    <select name="gender" id="gender" class="form-control" title="Please choose gender" style="width:220px;">
		      <option value=""> -- Select -- </option>
		      <option value="male"<?php echo ($gender=='male')?"selected='selected'":""?>>Male</option>
		      <option value="female"<?php echo ($gender=='female')?"selected='selected'":""?>>Female</option>
		      <option value="not_recorded"<?php echo ($gender=='not_recorded')?"selected='selected'":""?>>Not Recorded</option>
		    </select>
		  </td>
		  <td><b>Status&nbsp;:</b></td>
		  <td>
		      <select style="width: 220px;" name="status" id="status" class="form-control" title="Please choose status">
			<option value="">-- Select --</option>
			<option value="7"<?php echo ($status=='7')?"selected='selected'":""?>>Accepted</option>
			<option value="4"<?php echo ($status=='4')?"selected='selected'":""?>>Rejected</option>
		      </select>
		    </td>
		</tr>
		<tr>
		  <td colspan="6">&nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-default btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="reset();"><span>Reset</span></button>
			&nbsp;<button class="btn btn-default btn-sm" onclick="convertSearchResultToPdf('');"><span>Result PDF</span></button>
			&nbsp;<a class="btn btn-success btn-sm" href="javascript:void(0);" onclick="exportAllVlTestResult();"><i class="fa fa-cloud-download" aria-hidden="true"></i> Export Excel</a>
			&nbsp;<button class="btn btn-primary btn-sm" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>
		    </td>
		</tr>
	    </table>
            <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
			<div class="row" style="background:#e0e0e0;padding: 15px;margin-top: -5px;">
			    <div class="col-md-12" >
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code"  class="showhideCheckBox" /> <label for="iCol0">Sample Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="1" id="iCol1" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol1">Batch Code</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="2" id="iCol2" data-showhide="art_no" class="showhideCheckBox"  /> <label for="iCol2">Art No</label>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="3" id="iCol3" data-showhide="patient_name" class="showhideCheckBox"  /> <label for="iCol3">Patient's Name</label> <br>
				    </div>
				    <div class="col-md-3">
					    <input type="checkbox" onclick="javascript:fnShowHide(this.value);" value="4" id="iCol4" data-showhide="facility_name" class="showhideCheckBox"  /> <label for="iCol4">Faility Name</label>
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
                  <th>Modified On</th>
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
     <?php
     if(!isset($_COOKIE['collectionDate']) || $_COOKIE['collectionDate']==''){
      ?>
      $('#sampleCollectionDate').val("");
      <?php
     } else if(($lastUrl!='' || $lastUrl1!='') && isset($_COOKIE['collectionDate'])){ ?>
      $('#sampleCollectionDate').val("<?php echo $_COOKIE['collectionDate'];?>");
     <?php } ?>
     
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
                {"sClass":"center","bSortable":false},
            ],
            "aaSorting": [[ 7, "desc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlTestResultDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
		aoData.push({"name": "batchCode", "value": $("#batchCode").val()});
		aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
		aoData.push({"name": "facilityName", "value": $("#facilityName").val()});
	        aoData.push({"name": "sampleType", "value": $("#sampleType").val()});
		aoData.push({"name": "status", "value": $("#status").val()});
		aoData.push({"name": "gender", "value": $("#gender").val()});
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
    document.cookie = "collectionDate="+$("#sampleCollectionDate").val();
    document.cookie = "batchCode="+$("#batchCode").val();
    document.cookie = "sampleType="+$("#sampleType").val();
    document.cookie = "facilityName="+$("#facilityName").val();
    document.cookie = "gender="+$("#gender").val();
    document.cookie = "status="+$("#status").val();
    $.unblockUI();
  }
  
  function convertResultToPdf(id){
    <?php
    if($configFormResult[0]['value'] == 3){
      $path = '../includes/vlRequestDrcResultPdf.php';
    }else if($configFormResult[0]['value'] == 1 || $configFormResult[0]['value'] == 2){
     $path = '../includes/vlRequestResultPdf.php'; 
    }else if($configFormResult[0]['value'] == 4){
     $path = '../includes/vlRequestZamSearchResultPdf.php';  
    }else if($configFormResult[0]['value'] == 5){
     $path = '';  
    }
    ?>
      $.post("<?php echo $path; ?>", { source:'print', id : id},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('../uploads/'+data,'_blank');
	  }
      });
  }
  
  function convertSearchResultToPdf(id){
    $.blockUI();
    <?php
    if($configFormResult[0]['value'] == 3){
      $path = '../includes/vlRequestDrcSearchResultPdf.php';
    }else if($configFormResult[0]['value'] == 1 || $configFormResult[0]['value'] == 2){
     $path = '../includes/vlRequestSearchResultPdf.php'; 
    }else if($configFormResult[0]['value'] == 4){
     $path = '../includes/vlRequestZamSearchResultPdf.php';  
    }else if($configFormResult[0]['value'] == 4){
     $path = '';  
    }
    ?>
    $.post("<?php echo $path;?>", { source:'print',id:id},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('../uploads/'+data,'_blank');
	  }
      });
    $.unblockUI();
  }
  
  function exportAllVlTestResult(){
     $.blockUI();
     $.post("generateVlTestResultExcel.php", { },
      function(data){
	$.unblockUI();
       if(data === "" || data === null || data === undefined){
	 alert('Unable to generate excel..');
       }else{
	 location.href = '../temporary/'+data;
       }
      });
  }
  function reset()
  {
    document.cookie = "collectionDate=";
    document.cookie = "batchCode=";
    document.cookie = "sampleType=";
    document.cookie = "facilityName=";
    document.cookie = "gender=";
    document.cookie = "status=";
    window.location.reload();
  }
</script>
 <?php
 include('../footer.php');
 ?>