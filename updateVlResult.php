<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/font-awesome.min.4.5.0.css">
<link rel="stylesheet" href="dist/css/AdminLTE.min.css">
<link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />  
<link rel="stylesheet" media="all" type="text/css" href="assets/css/jquery-ui-timepicker-addon.css" />  
<style>
    .divLabel{
      float:left;
      width:30%;
    }
    .contentLabel{
      float:left;
      width:70%;
    }
    .ui_tpicker_second_label {
    display: none !important;
   }.ui_tpicker_second_slider {
    display: none !important;
   }.ui_tpicker_millisec_label {
    display: none !important;
   }.ui_tpicker_millisec_slider {
    display: none !important;
   }.ui_tpicker_microsec_label {
    display: none !important;
   }.ui_tpicker_microsec_slider {
    display: none !important;
   }.ui_tpicker_timezone_label {
    display: none !important;
   }.ui_tpicker_timezone {
    display: none !important;
   }.ui_tpicker_time_input{
    width:100%;
   }
</style>
<!-- iCheck -->
<link href="assets/css/deforayModal.css" rel="stylesheet" />

<script type="text/javascript" src="assets/js/jquery.min.2.0.2.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui.1.11.0.js"></script>

<?php
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$id=base64_decode($_GET['id']);
$sQuery="SELECT * from vl_request_form where vl_sample_id=$id";
$sInfo=$db->query($sQuery);
if(isset($sInfo[0]['date_sample_received_at_testing_lab']) && trim($sInfo[0]['date_sample_received_at_testing_lab'])!='' && $sInfo[0]['date_sample_received_at_testing_lab']!='0000-00-00'){
 $sInfo[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($sInfo[0]['date_sample_received_at_testing_lab']);
}else{
 $sInfo[0]['date_sample_received_at_testing_lab']='';
}
if(isset($sInfo[0]['date_results_dispatched']) && trim($sInfo[0]['date_results_dispatched'])!='' && $sInfo[0]['date_results_dispatched']!='0000-00-00'){
 $sInfo[0]['date_results_dispatched']=$general->humanDateFormat($sInfo[0]['date_results_dispatched']);
}else{
 $sInfo[0]['date_results_dispatched']='';
}
if(isset($sInfo[0]['result_reviewed_date']) && trim($sInfo[0]['result_reviewed_date'])!='' && $sInfo[0]['result_reviewed_date']!='0000-00-00'){
 $sInfo[0]['result_reviewed_date']=$general->humanDateFormat($sInfo[0]['result_reviewed_date']);
}else{
 $sInfo[0]['result_reviewed_date']='';
}
if(isset($sInfo[0]['lab_tested_date']) && trim($sInfo[0]['lab_tested_date'])!='' && $sInfo[0]['lab_tested_date']!='0000-00-00'){
 $sInfo[0]['lab_tested_date']=$general->humanDateFormat($sInfo[0]['lab_tested_date']);
}else{
 $sInfo[0]['lab_tested_date']='';
}
//get test status values
$tsQuery="SELECT * FROM testing_status";
$tsResult = $db->rawQuery($tsQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Update Result</h1>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
              <div class="row">
		  <table class="table">
		    <tr>
		      <td style="width:50%;border-top:none;">
			<label>Sample Code</label> - <?php echo $sInfo[0]['sample_code'];?>
		      </td>
		      <td style="width:50%;border-top:none;">
			<label>Patient's Name</label> - <?php echo ucwords($sInfo[0]['patient_name']);?>
		      </td>
		    </tr>
		    <tr>
		      <td colspan="2"><strong><h4>Lab Details</h4></strong></td>
		    </tr>
		    <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Lab Name</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="labName" name="labName" placeholder="Enter Lab Name" title="Please enter lab name" value="<?php echo $sInfo[0]['lab_name']; ?>"/></div>
		      </td>
		      <td style="width:50%;">
			<div class="divLabel"><label>Lab Contact Person</label></div> 
			<div class="contentLabel"><input type="text" class="form-control" id="labContactPerson" name="labContactPerson" placeholder="Enter Lab Contact Person Name" title="Please enter lab contact person name" value="<?php echo $sInfo[0]['lab_contact_person'];?>"/></div>
		      </td>
		    </tr>
		    <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Phone Number</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Enter Lab Phone No." title="Please enter lab phone no." value="<?php echo $sInfo[0]['lab_phone_no'];?>"/></div>
		      </td>
		      <td style="width:50%;">
			<div class="divLabel"><label>Date Sample Received at Testing Lab</label></div> 
			<div class="contentLabel"><input type="text" class="form-control date" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Select Sample Received Date" title="Select sample received date" value="<?php echo $sInfo[0]['date_sample_received_at_testing_lab']; ?>" readonly/></div>
		      </td>
		    </tr>
		    <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Sample Testing Date</label></div>
			<div class="contentLabel"><input type="text" class="form-control date" id="sampleTestedOn" name="sampleTestedOn" placeholder="Select Sample Testing Date" title="Select sample testing date" value="<?php echo $sInfo[0]['lab_tested_date']; ?>" readonly/></div>
		      </td>
		      <td style="width:50%;">
			<div class="divLabel"><label>Date Results Dispatched</label></div> 
			<div class="contentLabel"><input type="text" class="form-control date" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Select Result Dispatched Date" title="Select result dispatched date" value="<?php echo $sInfo[0]['date_results_dispatched']; ?>" readonly/></div>
		      </td>
		    </tr>
		    <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Reviewed By</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="reviewedBy" name="reviewedBy" placeholder="Enter Reviewed By Name" title="Please enter reviewed by name" value="<?php echo $sInfo[0]['result_reviewed_by'];?>"/></div>
		      </td>
		      <td style="width:50%;">
			<div class="divLabel"><label>Reviewed Date</label></div> 
			<div class="contentLabel"><input type="text" class="form-control date" id="reviewedOn" name="reviewedOn" placeholder="Select Reviewed Date" title="Select reviewed date" value="<?php echo $sInfo[0]['result_reviewed_date']; ?>" readonly/></div>
		      </td>
		    </tr>
		    <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Justification</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="justification" name="justification" placeholder="Enter Justification" title="Please enter justification" value="<?php echo $sInfo[0]['justification']; ?>"/></div>
		      </td>
		      <td style="width:50%;"></td>
		    </tr>
		    <tr>
		      <td colspan="2"><strong><h4>Result Details</h4></strong></td>
		    </tr>
		    <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Log Value</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="logValue" name="logValue" placeholder="Enter Log Value" title="Please enter log value" value="<?php echo $sInfo[0]['log_value']; ?>"/></div>
		      </td>
		      <td style="width:50%;">
			<div class="divLabel"><label>Absolute Value</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="absoluteValue" name="absoluteValue" placeholder="Enter Absolute Value" title="Please enter absolute value" value="<?php echo $sInfo[0]['absolute_value']; ?>"/></div>
		      </td>
		    </tr>
		     <tr>
		      <td style="width:50%;">
			<div class="divLabel"><label>Text Value</label></div>
			<div class="contentLabel"><input type="text" class="form-control" id="textValue" name="textValue" placeholder="Enter Text Value" title="Please enter text value" value="<?php echo $sInfo[0]['text_value']; ?>"/></div>
		      </td>
		      <td style="width:50%;">
			<div class="divLabel"><label>Result</label></div>
			<div class="contentLabel">
			  <input type="text" class="form-control" id="result" name="result" placeholder="Enter Result" title="Please select result status" value="<?php echo $sInfo[0]['result']; ?>"/></div>
		      </td>
		    </tr>
		    <tr>
		      <td style="width:50%;padding-top:30px;">
			<div class="divLabel"><label>Comments</label></div>
			<div class="contentLabel"><textarea type="text" class="form-control" id="comments" name="comments" row="4" placeholder="Enter Comments" title="Please enter comments"><?php echo $sInfo[0]['comments'];?></textarea></div>
		      </td>
		      <td style="width:50%;padding-top:30px;">
			<div class="divLabel"><label>Status</label></div>
			<div class="contentLabel">
			  <select class="form-control" id="status" name="status" title="Please select test status">
			      <?php
                              foreach($tsResult as $status){
                               ?>
                               <option value="<?php echo $status['status_id']; ?>" <?php echo ($sInfo[0]['status'] == $status['status_id'])?'selected="selected"':''; ?>><?php echo ucwords($status['status_name']);?></option>
                               <?php
                              }
                              ?>
			  </select>
			</div>
		      </td>
		    </tr>
		  </table>
              </div>
        </div>
	<!-- /.box-body -->
	<div class="box-footer">
	  <input type="hidden" id="treamentId" name="treamentId" value="<?php echo base64_encode($sInfo[0]['vl_sample_id']); ?>"/>
	  <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();">Submit</a>&nbsp;
	  <a href="javascript:void(0)" onclick="parent.closeModal()" class="btn btn-default "> Close</a>
	</div>
	<!-- /.box-footer -->
        <!-- /.row -->
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  <!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<script src="assets/js/deforayValidation.js"></script>
  <script type="text/javascript">
  function validateNow(){
    if($("#result").val()!=''){
      $.post("updateVlResultHelper.php", {
	  labName: $("#labName").val(),
	  labContactPerson: $("#labContactPerson").val(),
	  phoneNumber: $("#phoneNumber").val(),
	  sampleReceivedOn: $("#sampleReceivedOn").val(),
	  sampleTestedOn: $("#sampleTestedOn").val(),
	  resultDispatchedOn: $("#resultDispatchedOn").val(),
	  reviewedBy: $("#reviewedBy").val(),
	  reviewedOn: $("#reviewedOn").val(),
	  justification: $("#justification").val(),
	  logValue: $("#logValue").val(),
	  absoluteValue: $("#absoluteValue").val(),
	  textValue: $("#textValue").val(),
	  result: $("#result").val(),
	  comments: $("#comments").val(),
	  status: $("#status").val(),
	  treamentId : $("#treamentId").val(),
	  format: "html"
	},
      function(data){
	  if(data>0){
              parent.closeModal();
	      alert("Result Added Successfully");
              parent.window.location.href=window.parent.location.href;
	  }
	  
      });
    }else{
        alert("Please enter result");
    }
  }
  
  $(document).ready(function() {
    $('.date').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "hh:mm TT",
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     });
  });
  </script>