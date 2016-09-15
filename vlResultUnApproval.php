<?php
include('header.php');
$tsQuery="SELECT * FROM testing_status";
$tsResult = $db->rawQuery($tsQuery);
?>
<style>
    .dataTables_wrapper{
      position: relative;
    clear: both;
    overflow-x: visible !important;
    overflow-y: visible !important;
    padding: 15px 0 !important;
    }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Imported Results</h1>
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
            <div class="box-header with-border">
			  <div class="box-header with-border">
			  <div class="col-md-4 col-sm-4">
				<input type="hidden" name="checkedTests" id="checkedTests"/>
				<select style="" class="form-control" id="status" name="status" title="Please select test status" >
				  <option value="">-- Select --</option>
				  <option value="7">Accepted</option>
				  <option value="1">Hold</option>
				  <option value="4">Rejected</option>
				  
				</select>
				</div>
			  <div class="col-md-2 col-sm-2"><input type="button" onclick="submitTestStatus();" value="Update" class="btn btn-success btn-sm"></div>
			  <ul style="list-style: none;float: right;">
	    <li><i class="fa fa-square" aria-hidden="true" style="color: #e8000b"></i> - Unknown Sample</li>
	    <li><i class="fa fa-square" aria-hidden="true" style="color: #86c0c8"></i> - Existing Result</li>
	    <li><i class="fa fa-square" aria-hidden="true" style="color: #337ab7"></i> - Result for Sample</li>
	    <li><i class="fa fa-square" aria-hidden="true" style="color: #7d8388"></i> - Control</li>
	    </ul>
            </div>
	      <span><b style="color: #f03033;">Note:-</b>When you leave from this page,these records will be deleted.</span>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="vlRequestDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th style="width: 1%;"><input type="checkbox" id="checkTestsData" onclick="toggleAllVisible()"/></th>
		  <th style="width: 13%;">Sample Code</th>
		  <th style="width: 11%;">Batch Code</th>
                  <th style="width: 18%;">Lab Name</th>
                  <th style="width: 11%;">Sample Type</th>
                  <th style="width: 9%;">Result</th>
                  <th style="width: 16%;">Action</th>
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
   var startDate = "";
   var endDate = "";
   var selectedTests=[];
   var selectedTestsId=[];
  $(document).ready(function() {
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
            
            "bRetrieve": true,                        
            "aoColumns": [
		{"sClass":"center","bSortable":false},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center","bSortable":false},
		{"sClass":"center","bSortable":false},
            ],
            //"aaSorting": [[ 1, "desc" ]],
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
            "sAjaxSource": "getVlResultsForUnApproval.php",
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
	 $(".countChecksPending").text(selectedTests.length);
    }
      
    function toggleAllVisible(){
        //alert(tabStatus);
	$(".checkTests").each(function(){
	     $(this).prop('checked', false);
	     selectedTests.splice( $.inArray(this.value, selectedTests), 1 );
	     selectedTestsId.splice( $.inArray(this.id, selectedTestsId), 1 );
	 });
	 if ($("#checkTestsData").is(':checked')) {
	 $(".checkTests").each(function(){
	     $(this).prop('checked', true);
		 selectedTests.push(this.value);
		 selectedTestsId.push(this.id);
	 });
     } else{
	$(".checkTests").each(function(){
	     $(this).prop('checked', false);
	     selectedTests.splice( $.inArray(this.value, selectedTests), 1 );
	     selectedTestsId.splice( $.inArray(this.id, selectedTestsId), 1 );
	 });
     }
     $("#checkedTests").val(selectedTests.join());
     $(".countChecksPending").text(selectedTests.length);
   }
   
   function submitTestStatus(){
    if($("#status").val()!='' && $("#checkedTests").val()!=''){
      conf=confirm("Do you wish to change the status ?");
      if(conf){
	$.blockUI();
	$.post("updateUnApprovalResultStatus.php", { value : $("#checkedTests").val(),status:$("#status").val(), format: "html"},
	       function(data){
		oTable.fnDraw();
		selectedTests = [];
		selectedTestsId = [];
		$("#checkedTests").val('');
		$("#status").val('');
		$(".countChecksPending").html(0);
	       });
	$.unblockUI();
      }else{
      oTable.fnDraw();
      }
    }
   else{
      alert("Please select the status and atleast one checkbox");
    }
   }
  function updateStatus(value,status){
    if(status!=''){
      conf=confirm("Do you wish to change the status ?");
      if(conf){
	$.blockUI();
	$.post("updateUnApprovalResultStatus.php", { value : value,status:status, format: "html"},
	       function(data){
		oTable.fnDraw();
		selectedTests = [];
		selectedTestsId = [];
		$("#checkedTests").val('');
		$(".countChecksPending").html(0);
	       });
	$.unblockUI();
      }else{
      oTable.fnDraw();
      }
    }
   else{
      alert("Please select the status.");
    }
   }
  
 
</script>
 <?php
 include('footer.php');
 ?>
