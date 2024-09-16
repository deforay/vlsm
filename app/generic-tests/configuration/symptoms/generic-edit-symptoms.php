<?php

use App\Registries\AppRegistry;

$title = _translate("Symptoms");

require_once APPLICATION_PATH . '/header.php';

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_GET = _sanitizeInput($request->getQueryParams());
$id = (isset($_GET['id'])) ? base64_decode((string) $_GET['id']) : null;

$tQuery = "SELECT * from r_generic_symptoms where symptom_id=$id";
$symptomInfo = $db->query($tQuery);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-gears"></em> <?php echo _translate("Edit Symptoms"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
            <li class="active"><?php echo _translate("Symptoms"); ?></li>
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
                <form class="form-horizontal" method='post' name='editSymptomsInfo' id='editSymptomsInfo' autocomplete="off" action="save-symptoms-helper.php">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="symptomName" class="col-lg-4 control-label"><?php echo _translate("Symptom Name"); ?><span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control isRequired" id="symptomName" name="symptomName" placeholder="<?php echo _translate('Symptom Name'); ?>" title="<?php echo _translate('Please enter symptom name'); ?>" onblur="checkNameValidation('r_generic_symptoms','symptom_name',this,'<?php echo "symptom_id##" . $symptomInfo[0]['symptom_id']; ?>','<?php echo _translate("This symptom name that you entered already exists.Try another name"); ?>',null)" value="<?php echo $symptomInfo[0]['symptom_name']; ?>" />
                                        <input type="hidden" name="symptomId" id="symptomId" value="<?php echo base64_encode((string) $symptomInfo[0]['symptom_id']); ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="symptomCode" class="col-lg-4 control-label"><?php echo _translate("Symptom Code"); ?><span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control isRequired" id="symptomCode" name="symptomCode" placeholder="<?php echo _translate('Symptom Code'); ?>" title="<?php echo _translate('Please enter symptom code'); ?>" onblur="checkNameValidation('r_generic_symptoms','symptom_code',this,'<?php echo "symptom_id##" . $symptomInfo[0]['symptom_id']; ?>','<?php echo _translate("This symptom code that you entered already exists.Try another code"); ?>',null)" value="<?php echo $symptomInfo[0]['symptom_code']; ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="col-lg-4 control-label"><?php echo _translate("Status"); ?><span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <select class="form-control isRequired" id="status" name="status" title="<?php echo _translate('Please select status'); ?>">
                                            <option value=""><?php echo _translate("--Select--"); ?></option>
                                            <option value="active" <?php echo ($symptomInfo[0]['symptom_status'] == 'active') ? "selected='selected'" : "" ?>><?php echo _translate("Active"); ?></option>
                                            <option value="inactive" <?php echo ($symptomInfo[0]['symptom_status'] == 'inactive') ? "selected='selected'" : "" ?>><?php echo _translate("Inactive"); ?></option>
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
                        <a href="generic-symptoms.php" class="btn btn-default"> <?php echo _translate("Cancel"); ?></a>
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

<script nonce="<?= $_SESSION['nonce']; ?>" type="text/javascript">
    function validateNow() {

        flag = deforayValidator.init({
            formId: 'editSymptomsInfo'
        });

        if (flag) {
            $.blockUI();
            document.getElementById('editSymptomsInfo').submit();
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
</script>

<?php
require_once APPLICATION_PATH . '/footer.php';
