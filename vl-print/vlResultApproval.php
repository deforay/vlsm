<?php
include('../header.php');
//include('../includes/MysqliDb.php');
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$sQuery="SELECT * FROM r_sample_type";
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
      <h1><i class="fa fa-edit"></i> Results Approval</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Test Request</li>
      </ol>
    </section>

     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
	    <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width: 98%;">
		<tr>
		    <td style=""><b>Sample Collection Date&nbsp;:</b></td>
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
		  <td colspan="3">&nbsp;<input type="button" onclick="searchVlRequestData();" value="Search" class="btn btn-success btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		    
		    </td>
		</tr>
		
	    </table>
            <div class="box-header with-border">
		<div class="col-md-5 col-sm-5">
		    <input type="hidden" name="checkedTests" id="checkedTests"/>
		    <select style="" class="form-control" id="status" name="status" title="Please select test status" disabled="disabled">
		      <option value="">-- Select at least one sample to apply bulk action --</option>
		      <option value="7">Accepted</option>
		      <option value="4">Rejected</option>
		      <option value="2">Lost</option>
		    </select>
		</div>
		<div class="col-md-2 col-sm-2"><input type="button" onclick="submitTestStatus();" value="Apply" class="btn btn-success btn-sm"></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="vlRequestDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th><input type="checkbox" id="checkTestsData" onclick="toggleAllVisible()"/></th>
		  <th>Sample Code</th>
                  <th>Sample Collection Date</th>
                  <th>Batch Code</th>
                  <th>Unique ART No</th>
                  <th>Patient's Name</th>
		  <th>Facility Name</th>
                  <th>Sample Type</th>
                  <th>Result</th>
                  <th>Last Modified on</th>
                  <th>Status</th>
		  <?php if(isset($_SESSION['privileges']) && (in_array("editVlRequest.php", $_SESSION['privileges'])) || (in_array("viewVlRequest.php", $_SESSION['privileges']))){ ?>
                  <!--<th>Action</th>-->
		  <?php } ?>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="13" class="dataTables_empty">Loading data from server</td>
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
  } );
  
  var oTable = null;
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
		{"sClass":"center","bSortable":false},
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
                //{"sClass":"center","bSortable":false},
            ],
            "aaSorting": [[ 9, "desc" ]],
	    "fnDrawCallback": function() {
		var checkBoxes=document.getElementsByName("chk[]");
                len = checkBoxes.length;
                for(c=0;c<len;c++){
                    if (jQuery.inArray(checkBoxes[c].id, selectedTestsId) != -1 ){
			checkBoxes[c].setAttribute("checked",true);
                    }
                }
	    },
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlResultsForApproval.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
	      aoData.push({"name": "batchCode", "value": $("#batchCode").val()});
	      aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
	      aoData.push({"name": "facilityName", "value": $("#facilityName").val()});
	      aoData.push({"name": "sampleType", "value": $("#sampleType").val()});
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
    
  function convertPdf(id){
      $.post("../includes/vlRequestPdf.php", { id : id, format: "html"},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate download');
	  }else{
	      window.open('../uploads/'+data,'_blank');
	  }
	  
      });
  }
  
  
  function toggleTest(obj){
	 if ($(obj).is(':checked')) {
	     if($.inArray(obj.value, selectedTests) == -1){
		 selectedTests.push(obj.value);
		 selectedTestsId.push(obj.id);
	     }
	 } else {
	     selectedTests.splice( $.inArray(obj.value, selectedTests), 1 );
	     selectedTestsId.splice( $.inArray(obj.id, selectedTestsId), 1 );
	     $("#checkTestsData").attr("checked",false);
	 }
	 $("#checkedTests").val(selectedTests.join());
	 if(selectedTests.length!=0){
	  $("#status").prop('disabled', false);
	 }else{
	  $("#status").prop('disabled', true);
	 }
	 
    }
      
    function toggleAllVisible(){
        //alert(tabStatus);
	$(".checkTests").each(function(){
	     $(this).prop('checked', false);
	     selectedTests.splice( $.inArray(this.value, selectedTests), 1 );
	     selectedTestsId.splice( $.inArray(this.id, selectedTestsId), 1 );
	     $("#status").prop('disabled', true);
	 });
	 if ($("#checkTestsData").is(':checked')) {
	 $(".checkTests").each(function(){
	     $(this).prop('checked', true);
		 selectedTests.push(this.value);
		 selectedTestsId.push(this.id);
	 });
	 $("#status").prop('disabled', false);
     } else{
	$(".checkTests").each(function(){
	     $(this).prop('checked', false);
	     selectedTests.splice( $.inArray(this.value, selectedTests), 1 );
	     selectedTestsId.splice( $.inArray(this.id, selectedTestsId), 1 );
	     $("#status").prop('disabled', true);
	 });
     }
     $("#checkedTests").val(selectedTests.join());
   }
   
   function submitTestStatus(){
    var stValue = $("#status").val();
    var testIds = $("#checkedTests").val();
    if(stValue!='' && testIds!=''){
      conf=confirm("Do you wish to change the test status ?");
      if (conf) {
    $.post("updateTestStatus.php", { status : stValue,id:testIds},
      function(data){
	  if(data != ""){
	    $("#checkedTests").val('');
	    selectedTests = [];
	    selectedTestsId = [];
	    $("#checkTestsData").attr("checked",false);
	    $("#status").val('');
	    $("#status").prop('disabled', true);
	    oTable.fnDraw();
	    alert('Updated successfully.');
	  }
      });
      }
    }else{
      alert("Please be checked atleast one checkbox.");
    }
   }
  function updateStatus(obj)
  {
   if(obj.value!=''){
     conf=confirm("Do you wish to change the status ?");
     if (conf) {
   $.post("updateTestStatus.php", { status : obj.value,id:obj.id},
     function(data){
       if(data != ""){
	 $("#checkedTests").val('');
	   selectedTests = [];
	   selectedTestsId = [];
	   $("#checkTestsData").attr("checked",false);
	   $("#status").val('');
	   $("#status").prop('disabled', true);
	   oTable.fnDraw();
	   alert('Updated successfully.');
       }
     });
   }
  }
  }
//  
//  function printBarcode(tId) {
//    $.post("printBarcode.php",{id:tId},
//      function(data){
//	  if(data == "" || data == null || data == undefined){
//	    alert('Unable to generate download');
//	  }else{
//	    window.open('../uploads/barcode/'+data,'_blank');
//	  }
//    });
//  }
</script>
 <?php
 include('../footer.php');
 ?>
