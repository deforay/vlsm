<?php
ob_start();
#require_once('../startup.php');
require_once(APPLICATION_PATH . '/header.php');
$generalObj = new \Vlsm\Models\General();
$covid19Obj = new \Vlsm\Models\Covid19();
$covid19Results = $covid19Obj->getCovid19Results();

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i><svg style=" width: 20px; " aria-hidden="true" focusable="false" data-prefix="fas" data-icon="viruses" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="svg-inline--fa fa-viruses fa-w-20">
                    <path fill="currentColor" d="M624,352H611.88c-28.51,0-42.79-34.47-22.63-54.63l8.58-8.57a16,16,0,1,0-22.63-22.63l-8.57,8.58C546.47,294.91,512,280.63,512,252.12V240a16,16,0,0,0-32,0v12.12c0,28.51-34.47,42.79-54.63,22.63l-8.57-8.58a16,16,0,0,0-22.63,22.63l8.58,8.57c20.16,20.16,5.88,54.63-22.63,54.63H368a16,16,0,0,0,0,32h12.12c28.51,0,42.79,34.47,22.63,54.63l-8.58,8.57a16,16,0,1,0,22.63,22.63l8.57-8.58c20.16-20.16,54.63-5.88,54.63,22.63V496a16,16,0,0,0,32,0V483.88c0-28.51,34.47-42.79,54.63-22.63l8.57,8.58a16,16,0,1,0,22.63-22.63l-8.58-8.57C569.09,418.47,583.37,384,611.88,384H624a16,16,0,0,0,0-32ZM480,384a32,32,0,1,1,32-32A32,32,0,0,1,480,384ZM346.51,213.33h16.16a21.33,21.33,0,0,0,0-42.66H346.51c-38,0-57.05-46-30.17-72.84l11.43-11.44A21.33,21.33,0,0,0,297.6,56.23L286.17,67.66c-26.88,26.88-72.84,7.85-72.84-30.17V21.33a21.33,21.33,0,0,0-42.66,0V37.49c0,38-46,57.05-72.84,30.17L86.4,56.23A21.33,21.33,0,0,0,56.23,86.39L67.66,97.83c26.88,26.88,7.85,72.84-30.17,72.84H21.33a21.33,21.33,0,0,0,0,42.66H37.49c38,0,57.05,46,30.17,72.84L56.23,297.6A21.33,21.33,0,1,0,86.4,327.77l11.43-11.43c26.88-26.88,72.84-7.85,72.84,30.17v16.16a21.33,21.33,0,0,0,42.66,0V346.51c0-38,46-57.05,72.84-30.17l11.43,11.43a21.33,21.33,0,0,0,30.17-30.17l-11.43-11.43C289.46,259.29,308.49,213.33,346.51,213.33ZM160,192a32,32,0,1,1,32-32A32,32,0,0,1,160,192Zm80,32a16,16,0,1,1,16-16A16,16,0,0,1,240,224Z" class=""></path>
                </svg></i> <?php echo _("Add Covid-19 QC Test Kit"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Covid-19 QC Test Kit"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _("indicates required field"); ?> &nbsp;</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method='post' name='addQcTestKits' id='addQcTestKits' autocomplete="off" enctype="multipart/form-data" action="save-covid19-qc-test-kits-helper.php">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="testKitName" class="col-lg-4 control-label"><?php echo _("Test Kit Name"); ?><span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control isRequired" id="testKitName" name="testKitName" placeholder="<?php echo _('Test Kit Name'); ?>" title="<?php echo _('Please enter Test Kit name'); ?>" onblur='checkNameValidation("r_covid19_qc_testkits", "testkit_name" , this , null, "<?php echo _("The test kit name that you entered already exists. Enter another name"); ?>", null)' />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="testKitStatus" class="col-lg-4 control-label"><?php echo _("Status"); ?> <span class="mandatory">*</span></label>
                                    <div class="col-lg-7">
                                        <select class="form-control isRequired" id="testKitStatus" name="testKitStatus" placeholder="<?php echo _('Status'); ?>" title="<?php echo _('Please select Status'); ?>">
                                            <option value="active"><?php echo _("Active"); ?></option>
                                            <option value="inactive"><?php echo _("Inactive"); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered table-condensed" style="width:100%;">
                        <thead>
                            <tr>
                                <th style="text-align:center;"><?php echo _("QC Test Label"); ?> <span class="mandatory">*</span></th>
                                <th style="text-align:center;"><?php echo _("Expected Result"); ?> <span class="mandatory">*</span></th>
                                <th style="text-align:center;"><?php echo _("Action"); ?></th>
                            </tr>
                        </thead>
                        <tbody id="qcTestTable">
                            <tr>
                                <td>
                                    <input type="text" name="qcTestLable[]" id="qcTestLable1" class="form-control isRequired" placeholder='<?php echo _("QC Test Label"); ?>' title='<?php echo _("Please enter QC test label"); ?>' onblur="checkLabelName(this);" />
                                </td>
                                <td>
                                    <select id="expectedResult1" name="expectedResult[]" class="isRequired form-control" title="Please enter the expected results">
                                        <?= $generalObj->generateSelectOptions($covid19Results, null, "--Select--"); ?>
                                    </select>
                                </td>
                                <td align="center" style="vertical-align:middle;">
                                    <a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;&nbsp;<a class="btn btn-xs btn-default" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _("Submit"); ?></a>
                        <a href="covid19-qc-test-kits.php" class="btn btn-default"> <?php echo _("Cancel"); ?></a>
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
    tableRowId = 2;

    function validateNow() {

        flag = deforayValidator.init({
            formId: 'addQcTestKits'
        });

        if (flag) {
            $.blockUI();
            document.getElementById('addQcTestKits').submit();
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

    function insRow() {
        rl = document.getElementById("qcTestTable").rows.length;
        var a = document.getElementById("qcTestTable").insertRow(rl);
        a.setAttribute("style", "display:none");
        var b = a.insertCell(0);
        var c = a.insertCell(1);
        var d = a.insertCell(2);
        d.setAttribute("align", "center");
        d.setAttribute("style", "vertical-align:middle");

        b.innerHTML = '<input type="text" name="qcTestLable[]" id="qcTestLable' + tableRowId + '" class="isRequired form-control" placeholder="<?php echo _('QC Test Label'); ?>" title="<?php echo _('Please enter qc test label'); ?>" onblur="checkLabelName(this);"/ >';
        c.innerHTML = '<select id="expectedResult' + tableRowId + '" name="expectedResult[]" class="isRequired form-control" title="Please enter the expected results"><option value="">--Select--</option><?php foreach ($covid19Results as $key => $row) { ?><option value="<?php echo $key; ?>"><?php echo $row; ?></option><?php } ?></select>';
        d.innerHTML = '<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;&nbsp;<a class="btn btn-xs btn-default" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>';
        $(a).fadeIn(800);
        tableRowId++;
    }

    function removeAttributeRow(el) {
        $(el).fadeOut("slow", function() {
            el.parentNode.removeChild(el);
            rl = document.getElementById("qcTestTable").rows.length;
            if (rl == 0) {
                insRow();
            }
        });
    }

    function checkLabelName(obj) {
        machineObj = document.getElementsByName("qcTestLable[]");
        for (m = 0; m < machineObj.length; m++) {
            if (obj.value != '' && obj.id != machineObj[m].id && obj.value == machineObj[m].value) {
                alert('Duplicate value not allowed');
                $('#' + obj.id).val('');
            }
        }
    }
</script>

<?php
require_once(APPLICATION_PATH . '/footer.php');
?>