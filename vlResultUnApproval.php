<?php
include('header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Imported Results Un Approval</h1>
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
			  <div class="col-md-4 col-sm-4">
				<input type="hidden" name="checkedTests" id="checkedTests"/>
				</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
	      <div style="padding-top:20px;">
                                            <a href="javascript:void(0)" onclick="submitTestStatus($('#approvalPending').val(),'approve')" class="btn btn-success btn-sm" style="margin-bottom: 15px;"><i class="fa fa-cogs"></i>&nbsp;<b>Mark (<span class="countChecksPending">0</span>) as Approved</b></a>
					    <a href="javascript:void(0)" onclick="submitTestStatus($('#approvalPending').val(),'reject')" class="btn btn-danger btn-sm" style="margin-bottom: 15px;"><i class="fa fa-cogs"></i>&nbsp;<b>Mark (<span class="countChecksPending">0</span>) as Reject</b></a>
                                        </div>
              <table id="vlRequestDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th><input type="checkbox" id="checkTestsData" onclick="toggleAllVisible()"/></th>
		  <th>Sample Code</th>
		  <th>Lab Name</th>
                  <th>Sample Details</th>
                  <th>Status</th>
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
            ],
            "aaSorting": [[ 1, "desc" ]],
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
   
   function submitTestStatus(value,status){
    if($("#checkedTests").val()!=0){
      conf=confirm("Do you wish to change the status ?");
      if(conf){
	$.blockUI();
	$.post("updateUnApprovalResultStatus.php", { value : $("#checkedTests").val(),status:status, format: "html"},
	       function(data){
		oTable.fnDraw();
		selectedTests = [];
		selectedTestsId = [];
		$("#checkedTests").val('');
		$(".countChecksPending").html(0);
	       });
	$.unblockUI();
      }
    }
   else{
      alert("Please checked atleast one checkbox.");
    }
   }
  
 
</script>
 <?php
 include('footer.php');
 ?>
