<?php
include('../header.php');
$sQuery="SELECT serial_no,vl_sample_id FROM vl_request_form where vlsm_country_id=".$global['vl_form'];
$sResult = $db->rawQuery($sQuery);
?>
<link href="../assets/css/multi-select.css" rel="stylesheet" />
<style>
        .select2-selection__choice{
          color:#000000 !important;
        }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-qrcode"></i> Generate QR Code</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Generate QR Code</li>
      </ol>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
						<table id="advanceFilter" class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width: 60%;margin-bottom: 0px;">
							<tr>
								<td><b>Sample Code :</b></td>
								<td>
									<select style="" multiple="multiple" class="form-control" id="sampleCode" name="sampleCode[]" title="Please select sample code">
										<option value=""> -- Select -- </option>
										<?php
										foreach($sResult as $sCode){
										?>
											<option value="<?php echo $sCode['vl_sample_id'];?>"><?php echo ucwords($sCode['serial_no']);?></option>
										<?php
										}
										?>
									</select>
								</td>
								<td>
								&nbsp;<a class="btn btn-success btn-sm pull-right" style="margin-right:5px;" href="javascript:void(0);" onclick="generateQRcode('');"><i class="fa fa-qrcode" aria-hidden="true"></i> Print QR Code</a>
							</td>
							</tr>
						</table>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="generateQRCodeDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Sample Code</th>
                  <th>Sample Tested</th>
                  <th>Sample Low Level</th>
                  <th>Sample High Level</th>
                  <th>Last Tested Date</th>
                  <th>Created On</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="8" class="dataTables_empty">Loading data from server</td>
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
  <script>
  var oTable = null;
  $(document).ready(function() {
		$('#sampleCode').select2({width:"100%",placeholder:"Choose Samples"});
		$('#sampleCode').on('change',function(){
			oTable.fnDraw();
		});
    $.blockUI();
        oTable = $('#generateQRCodeDataTable').dataTable({
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
								{"sClass":"center","bSortable":false},
								{"sClass":"center","bSortable":false},
								{"sClass":"center","bSortable":false},
								{"sClass":"center","bSortable":false},
                {"sClass":"center"},
                {"sClass":"center","bSortable":false}
            ],
            "aaSorting": [[ 5, "desc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "../qr-code/getSampleCodeDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
              aoData.push({"name": "sampleCode", "value": $('#sampleCode').val()});
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
	
  });
  
  function generateQRcode(bId){
    if($.trim(bId)==''){
	if($('#sampleCode').val()!='' && $('#sampleCode').val()!=null){
	  bId = $('#sampleCode').val();
	}else{
	  alert("Please choose atleast one sample");
	  return false;
	}
    }
    $.blockUI();
    $.post("../qr-code/generateQRCode.php",{id:bId},
     function(data){
	 if(data == "" || data == null || data == undefined){
	     alert('Unable to generate QR code');
	 }else{
	     window.open('../uploads/qrcode/'+$.trim(data),'_blank');
	 }
	 $.unblockUI();
     });
  }
</script>
 <?php
 include('../footer.php');
 ?>