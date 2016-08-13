<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
$id=base64_decode($_GET['id']);
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Send Report</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Email Request</li>
      </ol>
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
          <form class="form-horizontal" method='post'  name='sendReport' id='sendReport' autocomplete="off" action="sendReportToMailHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="mailSubject" class="col-lg-4 control-label">Email Subject <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="mailSubject" name="mailSubject" placeholder="Email Subject" title="Please enter mail subject"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="toMail" class="col-lg-4 control-label">To Mail <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired isEmail" id="toMail" name="toMail" placeholder="To Mail" title="Please enter to mail"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row" style="display:none;">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="col-lg-4 control-label">Do you Want Encrypt? </label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                        <input type="radio" id="yes" name="encrypt" title="Please choose yes or no" value="yes" onclick="showPassword(this)" />Yes
                        </label>
                        <label class="radio-inline">
                        <input type="radio" id="no" name="encrypt" title="Please choose yes or no" value="no" onclick="showPassword(this)" checked="checked" />No
                        </label>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6" id="passDetails" style="display: none;">
                    <div class="form-group">
                        <label for="password" class="col-lg-4 control-label">Password </label>
                        <div class="col-lg-7">
                        <input type="password" class="form-control" id="password" name="password" title="Please enter password" placeholder="Please enter Password"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="toMail" class="col-lg-4 control-label">Message </label>
                        <div class="col-lg-7">
                        <textarea class="form-control " id="comment" name="comment" title="Please enter comment" placeholder="Please enter message"></textarea>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="encValue" id="encValue" value="no"/>
                <input type="hidden" name="fileName" id="fileName"/>
                <input type="hidden" name="batchId" id="batchId" value="<?php echo base64_encode($id);?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="addReport();return false;">Submit</a>
                <a href="vlRequestMail.php" class="btn btn-default"> Cancel</a>
              </div>
              </form>
              <!-- /.box-footer -->
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  
  
  <script type="text/javascript">

  function addReport(){
    flag = deforayValidator.init({
        formId: 'sendReport'
    });
    if(flag){
     sendReport();
    }
  }
  
  function sendReport()
  {
    $.post("vlRequestExportInExcel.php",{pass:$("#password").val(),encValue:$("#encValue").val(),batchId:$("#batchId").val()},
    function(data){
        if(data!=''){
          $("#fileName").val(data);
          document.getElementById('sendReport').submit();
        }else{
          alert("Something went wrong!.")
        }
    });
  }
  function showPassword(obj)
  {
    if(obj.value=="yes"){
        $("#passDetails").show();
        $("#password").addClass("isRequired");
        $("#encValue").val('yes');
    }else{
        $("#passDetails").hide();
        $("#password").removeClass("isRequired");
        $("#encValue").val('no');
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
