<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/font-awesome.min.4.5.0.css">
        <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link href="assets/css/deforayModal.css" rel="stylesheet" />
  
  <script type="text/javascript" src="assets/js/jquery.min.2.0.2.js"></script>
  <script type="text/javascript" src="assets/js/jquery-ui.1.11.0.js"></script>

<?php
ob_start();
//include('header.php');
include('./includes/MysqliDb.php');
$id=base64_decode($_GET['id']);
$sQuery="SELECT * from vl_request_form where treament_id=$id";
$sInfo=$db->query($sQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
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
          <!-- form start -->
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="batchCode" class="col-lg-4 control-label">Result <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="result" name="result" placeholder="Enter Result" title="" value="<?php echo $sInfo[0]['result'];?>" />
                        <input type="hidden" name="treatmentId" id="treatmentId" value="<?php echo $sInfo[0]['treament_id'];?>"/>
                        </div>
                    </div>
                  </div>
                </div>
		
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();">Submit</a>
                <a href="javascript:void(0)" onclick="parent.closeModal()" class="btn btn-default "> Cancel</a>
              </div>
              <!-- /.box-footer -->
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  <!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<script src="assets/js/deforayValidation.js"></script>
  <script type="text/javascript">
  function validateNow()
  {
    if($("#result").val()!=''){
      $.post("updateVlResultHelper.php", { result: $("#result").val(),treatmentId : $("#treatmentId").val(), format: "html"},
      function(data){
	  if(data>0)
	  {
              parent.closeModal();
	      alert("Result Added Successfully");
              parent.window.location.href=window.parent.location.href;
	  }
	  
      });
    }else{
        alert("Please enter result");
    }
  }
  </script>