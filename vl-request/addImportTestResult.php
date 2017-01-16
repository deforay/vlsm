<?php
ob_start();
include('../header.php');
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Import Test Result</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Import Test Result</li>
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
            <form class="form-horizontal" method="post"  name="addImportTestResultForm" id="addImportTestResultForm" enctype="multipart/form-data" autocomplete="off" action="addImportTestResultHelper.php">
              <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label">Upload File <span class="mandatory">*</span></label>
                                <div class="col-lg-7">
                                <input type="file" class="isRequired" name="requestFile" id="requestFile" title="Please choose result file">
                                (Upload xls, xlsx, csv format)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="box-footer">
                      <a href="../dashboard/index.php" class="btn btn-default"> Cancel</a>&nbsp;&nbsp;
                      <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                    </div>
                </div>
              </div>
              <!-- /.box-body -->
            </form>
          <!-- /.row -->
        </div>
      </div>
      <!-- /.box -->
    </section>
    <!-- /.content -->
  </div>
  
  <script type="text/javascript">
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addImportTestResultForm'
    });
    
    if(flag){
      document.getElementById('addImportTestResultForm').submit();
    }
  }
 </script>
 <?php
 include('../footer.php');
 ?>
