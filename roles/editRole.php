<?php
ob_start();
#require_once('../startup.php');
require_once(APPLICATION_PATH . '/header.php');

$id = base64_decode($_GET['id']);
$roleQuery = "SELECT * from roles where role_id=$id";
$roleInfo = $db->query($roleQuery);
/* Not allowed to edit API role */
if (isset($roleInfo[0]['role_code']) && $roleInfo[0]['role_code'] == 'API') {
	header("location:roles.php");
}
$activeModules = array('admin', 'common');

if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {
	$activeModules[] = 'vl';
}
if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {
	$activeModules[] = 'eid';
}
if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {
	$activeModules[] = 'covid19';
}
if (isset($systemConfig['modules']['hepatitis']) && $systemConfig['modules']['hepatitis'] == true) {
	$activeModules[] = 'hepatitis';
}
if (isset($systemConfig['modules']['tb']) && $systemConfig['modules']['tb'] == true) {
	$activeModules[] = 'tb';
}

$resourcesQuery = "SELECT module, GROUP_CONCAT( DISTINCT CONCAT(resources.resource_id,',',resources.display_name) ORDER BY resources.display_name SEPARATOR '##' ) as 'module_resources' FROM `resources` WHERE `module` IN ('" . implode("','", $activeModules) . "') GROUP BY `module` ORDER BY `module` ASC";
$rInfo = $db->query($resourcesQuery);

$priQuery = "SELECT * from roles_privileges_map where role_id=$id";
$priInfo = $db->query($priQuery);
$priId = array();
if ($priInfo) {
	foreach ($priInfo as $id) {
		$priId[] = $id['privilege_id'];
	}
}
?>
<style>
	.labelName {
		font-size: 13px;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa fa-user"></i> <?php echo _("Edit Role");?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa fa-dashboard"></i> <?php echo _("Home");?></a></li>
			<li class="active"><?php echo _("Roles");?></li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="box box-default">
			<div class="box-header with-border">
				<div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _("indicates required field");?> &nbsp;</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-horizontal" method='post' name='roleEditForm' id='roleEditForm' autocomplete="off" action="editRolesHelper.php">
					<div class="box-body">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="userName" class="col-lg-4 control-label"><?php echo _("Role Name");?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<input type="text" class="form-control isRequired" id="roleName" name="roleName" placeholder="<?php echo _('Role Name');?>" title="<?php echo _('Please enter user name');?>" value="<?php echo $roleInfo[0]['role_name']; ?>" onblur="checkNameValidation('roles','role_name',this,'<?php echo "role_id##" . $roleInfo[0]['role_id']; ?>','<?php echo _("This role name that you entered already exists.Try another role name");?>',null)" />
										<input type="hidden" name="roleId" id="roleId" value="<?php echo base64_encode($roleInfo[0]['role_id']); ?>" />
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="email" class="col-lg-4 control-label"><?php echo _("Role Code");?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<input type="text" class="form-control isRequired" id="roleCode" name="roleCode" placeholder="<?php echo _('Role Code');?>" title="<?php echo _('Please enter role code');?>" value="<?php echo $roleInfo[0]['role_code']; ?>" onblur="checkNameValidation('roles','role_code',this,'<?php echo "role_id##" . $roleInfo[0]['role_id']; ?>','<?php echo _("This role code that you entered already exists.Try another role code");?>',null)" />
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="landingPage" class="col-lg-4 control-label"><?php echo _("Landing Page");?></label>
									<div class="col-lg-7">
										<select class="form-control " name='landingPage' id='landingPage' title="<?php echo _('Please select landing page');?>">
											<option value=""> <?php echo _("-- Select --");?> </option>
											<option value="dashboard/index.php" <?php echo ($roleInfo[0]['landing_page'] == 'dashboard/index.php') ? "selected='selected'" : "" ?>><?php echo _("Dashboard");?></option>
											<option value="/vl/requests/addVlRequest.php" <?php echo ($roleInfo[0]['landing_page'] == '/vl/requests/addVlRequest.php') ? "selected='selected'" : "" ?>><?php echo _("Add New Request");?></option>
											<option value="import-result/addImportResult.php" <?php echo ($roleInfo[0]['landing_page'] == 'import-result/addImportResult.php') ? "selected='selected'" : "" ?>><?php echo _("Add Import Result");?></option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="status" class="col-lg-4 control-label"><?php echo _("Status");?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<select class="form-control isRequired" name='status' id='status' title="<?php echo _('Please select the status');?>">
											<option value=""> <?php echo _("-- Select --");?> </option>
											<option value="active" <?php echo ($roleInfo[0]['status'] == 'active') ? "selected='selected'" : "" ?>><?php echo _("Active");?></option>
											<option value="inactive" <?php echo ($roleInfo[0]['status'] == 'inactive') ? "selected='selected'" : "" ?>><?php echo _("Inactive");?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="accessType" class="col-lg-4 control-label"><?php echo _("Access Type");?> <span class="mandatory">*</span></label>
									<div class="col-lg-7">
										<select class="form-control isRequired" name='accessType' id='accessType' title="<?php echo _('Please select access type');?>">
											<option value=""> <?php echo _("-- Select --");?> </option>
											<option value="testing-lab" <?php echo ($roleInfo[0]['access_type'] == 'testing-lab') ? "selected='selected'" : "" ?>><?php echo _("Testing Lab");?></option>
											<option value="collection-site" <?php echo ($roleInfo[0]['access_type'] == 'collection-site') ? "selected='selected'" : "" ?>><?php echo _("Collection Site");?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<fieldset>
							<div class="form-group">
								<label class="col-sm-2 control-label"><?php echo _("Note");?>:</label>
								<div class="col-sm-10">
									<p class="form-control-static"><?php echo _('Unless you choose "access" the people belonging to this role will not be able to access other rights like "add", "edit" etc');?>.</p>
								</div>
							</div>
							<div class="form-group" style="padding-left:138px;">
								<strong><?php echo _("Select All");?></strong> <a style="color: #333;" href="javascript:void(0);" id="cekAllPrivileges"><input type='radio' class='layCek' name='cekUnCekAll' /> <i class='fa fa-check'></i></a>
								&nbsp&nbsp&nbsp&nbsp<strong><?php echo _("Unselect All");?></strong> <a style="color: #333;" href="javascript:void(0);" id="unCekAllPrivileges"><input type='radio' class='layCek' name='cekUnCekAll' /> <i class='fa fa-times'></i></a>
							</div>
							<table class="table table-striped table-hover responsive-utilities jambo_table">
								<?php
								foreach ($rInfo as $moduleRow) {
									echo "<table class='table table-striped responsive-utilities jambo_table'>";
									echo "<tr><th class='bg-primary'><h3>" . strtoupper($moduleRow['module']) . "</h3></th></tr>";

									$moduleResources = explode("##", $moduleRow['module_resources']);

									foreach ($moduleResources as $mRes) {

										$mRes = explode(",", $mRes);

										echo "<tr>";
										echo "<th><h4>";
										echo ($mRes[1]);
								?>
										<small class="pull-right toggler">
											&nbsp;&nbsp;&nbsp;<input type='radio' class='' name='<?= $mRes[1]; ?>' onclick='togglePrivilegesForThisResource("<?= $mRes[0]; ?>",true);'> <?php echo _("All");?>
											&nbsp;&nbsp;&nbsp;<input type='radio' class='' name='<?= $mRes[1]; ?>' onclick='togglePrivilegesForThisResource("<?= $mRes[0]; ?>",false);'> <?php echo _("None");?>
										</small>
								<?php
										echo "</h4></td>";
										echo "</tr>";
										echo "<tr class=''>";
										$pQuery = "SELECT * FROM privileges WHERE resource_id='" . $mRes[0] . "' order by display_name ASC";
										$pInfo = $db->query($pQuery);
										echo "<td style='text-align:center;vertical-align:middle;' class='privilegesNode' id='" . $mRes[0] . "'>";
										foreach ($pInfo as $privilege) {
											if (in_array($privilege['privilege_id'], $priId)) {
												$allowChecked = " checked='' ";
												$denyChecked = "";
											} else {
												$denyChecked = " checked='' ";
												$allowChecked = "";
											}
											echo "<div class='col-lg-3' style='margin-top:5px;border:1px solid #eee;padding:10px;'>
                              <label class='labelName'>" . ucwords($privilege['display_name']) . "</label>
                              <br>
                              <input type='radio' class='cekAll layCek'  name='resource[" . $privilege['privilege_id'] . "]" . "' value='allow' $allowChecked> <i class='fa fa-check'></i>
                              <input type='radio' class='unCekAll layCek'  name='resource[" . $privilege['privilege_id'] . "]" . "' value='deny' $denyChecked>  <i class='fa fa-times'></i>
                          </div>";
										}
										echo "</td></tr>";
									}
									echo "</table>";
								}
								?>

						</fieldset>


					</div>
					<!-- /.box-body -->
					<div class="box-footer">
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _("Submit");?></a>
						<a href="roles.php" class="btn btn-default"> <?php echo _("Cancel");?></a>
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
			formId: 'roleEditForm'
		});

		if (flag) {
			$.blockUI();
			document.getElementById('roleEditForm').submit();
		}
	}

	$("#cekAllPrivileges").click(function() {
		$('.unCekAll').prop('checked', false);
		$('.cekAll').prop('checked', true);
	});

	$("#unCekAllPrivileges").click(function() {
		$('.cekAll').prop('checked', false);
		$('.unCekAll').prop('checked', true);

	});

	function togglePrivilegesForThisResource(obj, checked) {
		if (checked == true) {
			$("#" + obj).find('.cekAll').prop('checked', true);
			$("#" + obj).find('.unCekAll').prop('checked', false);
		} else if (checked == false) {
			$("#" + obj).find('.cekAll').prop('checked', false);
			$("#" + obj).find('.unCekAll').prop('checked', true);
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
</script>

<?php
require_once(APPLICATION_PATH . '/footer.php');
?>