<?php
ob_start();
#require_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');
$rejQuery = "SELECT * from rejection_type";
$rejInfo = $db->query($rejQuery);
$id = base64_decode($_GET['id']);
$rsnQuery = "SELECT * from r_covid19_sample_rejection_reasons where rejection_reason_id=$id";
$rsnInfo = $db->query($rsnQuery);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa fa-gears"></i> Add Covid-19 Sample Rejection Reasons</h1>
    <ol class="breadcrumb">
      <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Covid-19 Sample Rejection Reasons</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    
    <div class="box box-default">
      <div class="box-header with-border">
        <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
      </div>
      <!-- /.box-header -->
      <div class="box-body">
        <!-- form start -->
        <form class="form-horizontal" method='post' name='addSampleRejcForm' id='addSampleRejcForm' autocomplete="off" enctype="multipart/form-data" action="edit-rejection-reason-helper.php">
          <div class="box-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionReasonName" class="col-lg-4 control-label">Rejection Reason Name <span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <input type="text" class="form-control isRequired" id="rejectionReasonName" name="rejectionReasonName" value="<?php echo $rsnInfo[0]['rejection_reason_name']; ?>" placeholder="Rejection Reason Name" title="Please enter Rejection Reason name" />
                    <input type="hidden" class="form-control isRequired" id="rejectionReasonId" name="rejectionReasonId" value="<?php echo base64_encode($rsnInfo[0]['rejection_reason_id']); ?>" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionType" class="col-lg-4 control-label">Rejection Type</label>
                  <div class="col-lg-7">
                    <select class="form-control select2 isRequired" id="rejectionType" name="rejectionType" placeholder="Rejection Type" title="Please enter Rejection Type"  onchange="addNewRejectionType(this.id);">
                      <option value=""> -- Select -- </option>
                      <?php
                        foreach ($rejInfo as $type) {
                            if(strtolower($rsnInfo[0]['rejection_type']) == strtolower($type['rejection_type'])){
                      ?>
                        <option value="<?php echo $type['rejection_type']; ?>" selected><?php echo $type['rejection_type']; ?></option>
                      <?php
                            }else{?>
                                <option value="<?php echo $type['rejection_type']; ?>"><?php echo $type['rejection_type']; ?></option>
                      <?php }
                        }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionReasonCode" class="col-lg-4 control-label">Rejection Reason Code <span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <input type="text" class="form-control isRequired" value="<?php echo $rsnInfo[0]['rejection_reason_code']; ?>" id="rejectionReasonCode" name="rejectionReasonCode" placeholder="Rejection Reason Code" title="Please enter Rejection Reason Code" />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionReasonStatus" class="col-lg-4 control-label">Rejection Reason Status</label>
                  <div class="col-lg-7">
                    <select class="form-control isRequired" id="rejectionReasonStatus" name="rejectionReasonStatus" placeholder="Rejection Reason Status" title="Please enter Rejection Reason Status"  >
                        <option value="active" <?php echo($rsnInfo[0]['rejection_reason_status']=="active" ? 'selected':''); ?> >Active</option>
                        <option value="inactive" <?php echo($rsnInfo[0]['rejection_reason_status']=="inactive" ? 'selected':''); ?> >Inactive</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <br>

          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
            <a href="covid19-sample-rejection-reasons.php" class="btn btn-default"> Cancel</a>
          </div>
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
$(document).ready(function() {
  $(".select2").select2();
  $(".select2").select2({
    tags: true
  });
});
  function validateNow() {
   
    flag = deforayValidator.init({
      formId: 'addSampleRejcForm'
    });

    if (flag) {
      $.blockUI();
      document.getElementById('addSampleRejcForm').submit();
    }
  }

  function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
    var removeDots = obj.value.replace(/\./g, "");
    var removeDots = removeDots.replace(/\,/g, "");
    //str=obj.value;
    removeDots = removeDots.replace(/\s{2,}/g, ' ');

    $.post("/includes/checkDuplicate.php", {
        tableName: tableName,
        fieldName: fieldName,
        value: removeDots.trim(),
        fnct: fnct,
        format: "html"
      },
      function(data) {
        if (data === '1') {
          alert(alrt);
          document.getElementById(obj.id).value = "";
        }
      });
  }

  function addNewRejectionType(id){
    checkValue = $("#"+id+" option:selected").html();
    if(checkValue!='')
    {
      $.post("/includes/addNewField.php", {
        value: checkValue,
        mode:'addNewRejectionType'
      },
      function(data) {
        console.log(data)
      });
    }
  }

</script>

<?php
include(APPLICATION_PATH . '/footer.php');
?>