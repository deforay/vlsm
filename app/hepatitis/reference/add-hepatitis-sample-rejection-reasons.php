<?php


require_once APPLICATION_PATH . '/header.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><em class="fa-solid fa-square-h"></em> <?php echo _translate("Add Hepatitis Sample Rejection Reasons"); ?></h1>
    <ol class="breadcrumb">
      <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
      <li class="active"><?php echo _translate("Hepatitis Sample Rejection Reasons"); ?></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="box box-default">
      <div class="box-header with-border">
        <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _translate("indicates required fields"); ?> &nbsp;</div>
      </div>
      <!-- /.box-header -->
      <div class="box-body">
        <!-- form start -->
        <form class="form-horizontal" method='post' name='addSampleRejcForm' id='addSampleRejcForm' autocomplete="off" enctype="multipart/form-data" action="save-hepatitis-sample-rejection-reasons-helper.php">
          <div class="box-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionReasonName" class="col-lg-4 control-label"><?php echo _translate("Rejection Reason Name"); ?><span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <input type="text" class="form-control isRequired" id="rejectionReasonName" name="rejectionReasonName" placeholder="<?php echo _translate('Rejection Reason Name'); ?>" title="<?php echo _translate('Please enter Rejection Reason name'); ?>" onblur='checkNameValidation("r_hepatitis_sample_rejection_reasons","rejection_reason_name",this,null,"<?php echo _translate("The Rejection Reason name that you entered already exists.Enter another Rejection Reason name"); ?>",null)' />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionType" class="col-lg-4 control-label"><?php echo _translate("Rejection Type"); ?> <span class="mandatory">*</span> <em class="fas fa-edit"></em></label>
                  <div class="col-lg-7">
                    <select class="form-control isRequired select2 editableSelect" id="rejectionType" name="rejectionType" placeholder="<?php echo _translate('Rejection Type'); ?>" title="<?php echo _translate('Please enter Rejection Type'); ?>">
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionReasonCode" class="col-lg-4 control-label"><?php echo _translate("Rejection Reason Code"); ?><span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <input type="text" class="form-control isRequired" id="rejectionReasonCode" name="rejectionReasonCode" placeholder="<?php echo _translate('Rejection Reason Code'); ?>" title="<?php echo _translate('Please enter Rejection Reason Code'); ?>" onblur='checkNameValidation("r_hepatitis_sample_rejection_reasons","rejection_reason_code",this,null,"<?php echo _translate("The Rejection Reason code that you entered already exists.Enter another Rejection Reason code"); ?>",null)' />
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rejectionReasonStatus" class="col-lg-4 control-label"><?php echo _translate("Rejection Reason Status"); ?><span class="mandatory">*</span></label>
                  <div class="col-lg-7">
                    <select class="form-control isRequired" id="rejectionReasonStatus" name="rejectionReasonStatus" placeholder="<?php echo _translate('Rejection Reason Status'); ?>" title="<?php echo _translate('Please enter Rejection Reason Status'); ?>">
                      <option value="active"><?php echo _translate("Active"); ?></option>
                      <option value="inactive"><?php echo _translate("Inactive"); ?></option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <br>

          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _translate("Submit"); ?></a>
            <a href="hepatitis-sample-rejection-reasons.php" class="btn btn-default"> <?php echo _translate("Cancel"); ?></a>
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
    editableSelect('rejectionType', 'rejection_type', 'r_hepatitis_sample_rejection_reasons', 'Rejection type');
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
    let removeDots = obj.value.replace(/\./g, "");
    removeDots = removeDots.replace(/\,/g, "");
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

  function addNewRejectionType(id) {
    checkValue = $("#" + id + " option:selected").html();
    if (checkValue != '') {
      $.post("/includes/addNewField.php", {
          value: checkValue,
          mode: 'addNewRejectionType'
        },
        function(data) {
          console.log(data)
        });
    }
  }
</script>

<?php
require_once APPLICATION_PATH . '/footer.php';
