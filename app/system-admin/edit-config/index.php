<?php

use App\Services\CommonService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

$title = _translate("Edit Configuration");

require_once(APPLICATION_PATH . '/system-admin/admin-header.php');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$arr = $general->getGlobalConfig();

$sarr = $general->getSystemConfig();

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

//get labs
$fResult = $facilitiesService->getAllFacilities(2);
?>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><em class="fa-sharp fa-solid fa-gears"></em> <?php echo _translate("Edit System Configuration"); ?></h1>
    <ol class="breadcrumb">
      <li><a href="index.php"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
      <li class="active"><?php echo _translate("Manage System Config"); ?></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="box box-default">
      <!--<div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"> </div>
        </div>-->
      <!-- /.box-header -->
      <div class="box-body">
        <!-- form start -->
        <form class="form-horizontal" method='post' name='editSystemConfigForm' id='editSystemConfigForm' enctype="multipart/form-data" autocomplete="off" action="systemConfigHelper.php">
          <div class="box-body">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><?php echo _translate("Instance Settings"); ?></h3>
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Timezone"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                        <select class="form-control select2 isRequired" id="default_time_zone" name="default_time_zone" placeholder="<?php echo _translate('Timezone'); ?>" title="<?php echo _translate('Please choose Timezone'); ?>">
                          <option value=""><?= _translate("-- Select --"); ?></option>
                          <?php
                          $timezone_identifiers = DateTimeZone::listIdentifiers();

                          foreach ($timezone_identifiers as $value) {
                          ?>
                            <option <?= ($arr['default_time_zone'] == $value ? 'selected=selected' : ''); ?> value='<?= $value; ?>'> <?= $value; ?></option>;
                          <?php
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="sc_user_type" class="col-lg-4 control-label"><?php echo _translate("Instance Type"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                        <select class="form-control select2" id="sc_user_type" name="sc_user_type" placeholder="<?php echo _translate('Instance Type'); ?>" title="<?php echo _translate('Please choose instance type'); ?>" onchange="enableLab();">
                          <option value=""><?php echo _translate("-- Select --"); ?></option>
                          <option value="standalone" <?php echo $general->isStandaloneInstance() ? "selected='selected'" : "" ?>><?php echo _translate("Standalone"); ?></option>
                          <option value="vluser" <?php echo $general->isLISInstance() ? "selected='selected'" : "" ?>><?php echo _translate("Lab Instance"); ?></option>
                          <option value="remoteuser" <?php echo $general->isSTSInstance() ? "selected='selected'" : "" ?>><?php echo _translate("Remote Instance"); ?></option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7 labName" style="<?php echo ($general->isLISInstance()) ? 'display:show' : 'display:none'; ?>">
                    <div class="form-group">
                      <label for="sc_testing_lab_id" class="col-lg-4 control-label"><?php echo _translate("Lab Name"); ?></label>
                      <div class="col-lg-8">
                        <select class="form-control select2" name="sc_testing_lab_id" id="sc_testing_lab_id" style="width:100%;" title="<?php echo _translate('Please select the lab name'); ?>">
                          <option value=""><?php echo _translate("-- Select --"); ?></option>
                          <?php foreach ($fResult as $labName) { ?>
                            <option value="<?php echo $labName['facility_id']; ?>" <?php echo ($labName['facility_id'] == $sarr['sc_testing_lab_id']) ? "selected='selected'" : "" ?>><?php echo $labName['facility_name']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><?php echo _translate("SMTP Settings"); ?></h3>
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="sup_email" class="col-lg-4 control-label">Email </label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control isEmail" id="sup_email" name="sup_email" placeholder="Email" title="Please enter email" value="<?php echo $sarr['sup_email']; ?>">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="sup_password" class="col-lg-4 control-label">Password </label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="sup_password" name="sup_password" placeholder="Password" title="Please enter password" value="<?php echo $sarr['sup_password']; ?>">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="box-footer">
            <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _translate("Submit"); ?></a>
            <a href="index.php" class="btn btn-default"> <?php echo _translate("Reload"); ?></a>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>
<script>
  $(document).ready(function() {
    enableLab();
    $(".select2").select2();
  });

  function enableLab() {
    if ($("#sc_user_type").val() == 'vluser') {
      $(".labName").show();
      $("#sc_testing_lab_id").addClass("isRequired").css('pointer-events', '');
    } else {
      $(".labName").hide();
      $("#sc_testing_lab_id").removeClass("isRequired").css('pointer-events', 'none').val('');
    }
  }

  function validateNow() {
    flag = deforayValidator.init({
      formId: 'editSystemConfigForm'
    });
    if (flag) {
      $.blockUI();
      document.getElementById('editSystemConfigForm').submit();
    }
  }
</script>
<?php
require_once(APPLICATION_PATH . '/system-admin/admin-footer.php');
