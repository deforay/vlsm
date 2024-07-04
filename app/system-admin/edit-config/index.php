<?php

use App\Services\CommonService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\TestsService;

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

$formQuery = "SELECT * FROM s_available_country_forms ORDER by form_name ASC";
$formResult = $db->query($formQuery);
$testName = TestsService::getTestTypes();
$globalConfig = $general->getGlobalConfig();

?>
  <link rel="stylesheet" media="all" type="text/css" href="/assets/css/selectize.css" />

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
                <h3 class="panel-title"><?php echo _translate("System Settings"); ?></h3>
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Database Host Name"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <input id="dbHostName" value="<?= SYSTEM_CONFIG['database']['host']; ?>" type="text" class="form-control" name="dbHostName" placeholder="<?= _translate("Please enter database host name"); ?>" title="<?= _translate("Please enter database host name"); ?>" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Database Username"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <input id="dbUserName" value="<?= SYSTEM_CONFIG['database']['username']; ?>" type="text" class="form-control" name="dbUserName" placeholder="<?= _translate("Please enter database user name"); ?>" title="<?= _translate("Please enter database user name"); ?>" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Database Password"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <input id="dbPassword" value="<?= SYSTEM_CONFIG['database']['password']; ?>" type="text" class="form-control" name="dbPassword" placeholder="<?= _translate("Please enter database password"); ?>" title="<?= _translate("Please enter database password"); ?>" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Database Name"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <input id="dbName" value="<?= SYSTEM_CONFIG['database']['db']; ?>" type="text" class="form-control" name="dbName" placeholder="<?= _translate("Please enter database name"); ?>" title="<?= _translate("Please enter database name"); ?>" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Database Port"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <input id="dbPort" value="<?= SYSTEM_CONFIG['database']['port']; ?>" type="text" class="form-control" name="dbPort" placeholder="<?= _translate("Please enter database port"); ?>" title="<?= _translate("Please enter database port"); ?>" />
                      </div>
                    </div>
                  </div>                 
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h3 class="panel-title"><?php echo _translate("Instance Settings"); ?></h3>
              </div>
              <div class="panel-body">
                <div class="row">
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
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("STS URL"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <input id="remoteUrl" type="text" class="form-control lis-input" name="remoteUrl" value="<?= $general->getRemoteUrl(); ?>" placeholder="<?= _translate("STS URL"); ?>" title="<?= _translate("Please enter the STS URL"); ?>" onchange="checkSTSUrl(this.value);" />
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
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Enabled Modules"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <select class="" name="enabledModules[]" id="enabledModules" title="<?php echo _translate('Please select the tests'); ?>" multiple="multiple">
                <option value=""><?= _translate("-- Choose Modules to Enable --"); ?></option>
                <?php foreach ($testName as $key => $val) {
                ?>
                  <option value="<?= $key; ?>" <?php if(isset(SYSTEM_CONFIG['modules'][$key]) && SYSTEM_CONFIG['modules'][$key]==true) echo "selected='selected'"; ?>><?= $val['testName']; ?></option>
                <?php
                } ?>
              </select>
                      </div>
                    </div>
                  </div>  
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="timezone" class="col-lg-4 control-label"><?php echo _translate("Viral Load Form"); ?> <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                      <select class="form-control isRequired readPage select2" name="vl_form" id="vl_form" title="<?php echo _translate('Please select the viral load form'); ?>">
                        <option value=""></option>
                        <?php
                        foreach ($formResult as $val) {
                        ?>
                          <option value="<?php echo $val['vlsm_country_id']; ?>" <?php echo ($val['vlsm_country_id'] == $globalConfig['vl_form']) ? "selected='selected'" : "" ?>><?php echo $val['form_name']; ?></option>
                        <?php
                        }
                        ?>
                      </select>                      
                    </div>
                    </div>
                  </div>  
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
<script type="text/javascript" src="/assets/js/selectize.js"></script>

<script>
  $(document).ready(function() {
    enableLab();
    $(".select2").select2();

      $("#enabledModules").selectize({
        plugins: ["restore_on_backspace", "remove_button", "clear_button"],
      });
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
