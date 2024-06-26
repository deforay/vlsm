<?php

use App\Registries\AppRegistry;

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);


/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$title = _translate("Geographical Divisions");

require_once APPLICATION_PATH . '/header.php';

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_GET = _sanitizeInput($request->getQueryParams());
$id = (isset($_GET['id'])) ? base64_decode((string) $_GET['id']) : null;

if (!isset($id) || $id == "") {
    $_SESSION['alertMsg'] = _translate("Something went wrong in Geographical Divisions edit page");
    header("Location:geographical-divisions-details.php");
}

$geoQuery = "SELECT * from geographical_divisions WHERE geo_status ='active'";
$geoParentInfo = $db->query($geoQuery);
$geoArray = [];
foreach ($geoParentInfo as $type) {
    $geoArray[$type['geo_id']] = ($type['geo_name']);
}
$query = "SELECT * from geographical_divisions where geo_id = $id";
$geoInfo = $db->rawQueryOne($query);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-gears"></em> <?php echo _translate("Edit Geographical Divisions"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
            <li class="active"><?php echo _translate("Edit Geographical Divisions"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _translate("indicates required fields"); ?> &nbsp;</div>
            </div>
            <form class="form-horizontal" method='post' name='geographicalDivisionsDetails' id='geographicalDivisionsDetails' autocomplete="off" enctype="multipart/form-data" action="save-geographical-divisions-helper.php">
                <!-- /.box-header -->
                <div class="box-body">
                    <!-- form start -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="geoName" class="col-lg-4 control-label"><?php echo _translate("Geographical Division Name"); ?> <span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control isRequired" value="<?php echo $geoInfo['geo_name']; ?>" id="geoName" name="geoName" placeholder="<?php echo _translate('Geo Division Name'); ?>" title="<?php echo _translate('Please enter Geographical Division name'); ?>" onblur="checkNameValidation('geographical_divisions','geo_name',this,'<?php echo 'geo_id##' . htmlspecialchars($id); ?>','<?php echo _translate("The Geographical Division name that you entered already exists. Please enter another name"); ?>',null)" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="geoCode" class="col-lg-4 control-label"><?php echo _translate("Geographical Division Code"); ?> <span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control isRequired" value="<?php echo $geoInfo['geo_code']; ?>" id="geoCode" name="geoCode" placeholder="<?php echo _translate('Geographical Divisions code'); ?>" title="<?php echo _translate('Please enter Geographical Division code'); ?>" onblur="checkNameValidation('geographical_divisions','geo_code',this,'<?php echo 'geo_id##' . htmlspecialchars($id); ?>','<?php echo _translate("The Geographical Division code that you entered already exists. Please enter another code"); ?>',null)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="geoParent" class="col-lg-4 control-label"><?php echo _translate("Parent Geographical Division"); ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control" id="geoParent" name="geoParent" placeholder="<?php echo _translate('Parent Division'); ?>" title="<?php echo _translate('Please select Parent division'); ?>">
                                            <?= $general->generateSelectOptions($geoArray, $geoInfo['geo_parent'], _translate("-- Select --")); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="geoStatus" class="col-lg-4 control-label"><?php echo _translate("Status"); ?><span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <select class="form-control isRequired" id="geoStatus" name="geoStatus" title="<?php echo _translate('Please select status'); ?>">
                                            <option value=""><?php echo _translate("--Select--"); ?></option>
                                            <option value="active" <?php echo ($geoInfo['geo_status'] == "active" ? 'selected' : ''); ?>><?php echo _translate("Active"); ?></option>
                                            <option value="inactive" <?php echo ($geoInfo['geo_status'] == "inactive" ? 'selected' : ''); ?>><?php echo _translate("Inactive"); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <input type="hidden" name="geoId" name="geoId" value="<?php echo $_GET['id']; ?>">
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _translate("Submit"); ?></a>
                        <a href="geographical-divisions-details.php" class="btn btn-default"> <?php echo _translate("Cancel"); ?></a>
                    </div>
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
    function validateNow() {

        flag = deforayValidator.init({
            formId: 'geographicalDivisionsDetails'
        });

        if (flag) {
            $.blockUI();
            document.getElementById('geographicalDivisionsDetails').submit();
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
