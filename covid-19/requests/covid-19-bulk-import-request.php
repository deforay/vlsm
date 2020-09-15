<?php
ob_start();
$title = "Import Test Request From File";
#require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');
$general = new \Vlsm\Models\General($db);
$fileName = UPLOAD_PATH. DIRECTORY_SEPARATOR . 'import-request' . DIRECTORY_SEPARATOR . 'Participant_Bulk_Import_Excel_Format_covid19.xlsx';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa fa-edit"></i> Import Test Request From File</h1>
    <ol class="breadcrumb">
      <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Import Request</li>
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
        <form class="form-horizontal" method='post' name='addImportRequestForm' id='addImportRequestForm' enctype="multipart/form-data" autocomplete="off" action="bulk-import-request-helper.php">
          <div class="box-body">
            <div class="wizard_content">
              <div class="row setup-content step" id="step-1" style="display:block;">
                <div class="col-xs-12">
                  <div class="col-md-12" id="stepOneForm">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label class="col-lg-4 control-label" for="requestFile">Upload File <span class="mandatory">*</span></label>
                          <div class="col-lg-7">
                            <input type="file" class="isRequired" name="requestFile" id="requestFile" title="Please choose result file">
                            (Upload xls, xlsx, csv format)
                          </div>
                        </div>
                      </div>
                      <?php if(file_exists($fileName)) {?>
                      <div class="col-md-6">
                        <a href="<?php echo '/uploads/import-request/Participant_Bulk_Import_Excel_Format_covid19.xlsx';?>" target="_blank" class="btn btn-sm btn-primary" download><i class="fa fa-download"></i> Download Example Format</a>
                      </div>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row setup-content step" id="step-2">
                <div class="col-xs-12">
                  <div class="col-md-12" id="stepTwoForm">
                    <div class="row form-group">
                      <div class="box-footer">
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                        <a href="/dashboard/index.php" class="btn btn-default"> Cancel</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /.box-body -->

          <!-- /.box-footer -->
        </form>
        <!-- /.row -->
      </div>

    </div>
    <!-- /.box -->

  </section>
  <!-- /.content -->
</div>

<script type="text/javascript">
  function validateNow() {
    flag = deforayValidator.init({
      formId: 'addImportRequestForm'
    });
    if (flag) {
      document.getElementById('addImportRequestForm').submit();
    }
  }
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>