<?php


$title = _translate("Reset Password") . " - " . _translate("System Admin");

use App\Services\UsersService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

require_once APPLICATION_PATH . '/system-admin/admin-header.php';


/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

$sResult = $usersService->getAllUsers();
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1> <em class="fa-solid fa-gears"></em> <?php echo _translate("Reset Password"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/system-admin/edit-config/index.php"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
			<li class="active"><?php echo _translate("Manage Reset Password"); ?></li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width:40%;">
						<tr>
							<td><strong><?php echo _translate("User"); ?>&nbsp;:</strong></td>
							<td>
								<select style="width:100%" class="form-control select2" name="userId" id="userId" placeholder="<?php echo _translate('User'); ?>" onchange="viewUser()">
									<option value="">--Select--</option>
									<?php foreach ($sResult as $data) { ?>
										<option value="<?php echo $data['user_id'] ?>"><?= $data['login_id']; ?> (<?= $data['status']; ?>)</option>
									<?php } ?>
								</select>
							</td>
						</tr>

					</table>
				</div>
				<!-- /.box-header -->

				<!-- <span><i class="fa fa-trash" style="color: red; background"></i></span> -->
				<div class="box viewUser hide" id="viewUser"></div>

				<!-- /.box-body -->

				<!-- /.box -->
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
<script src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script>
	$(document).ready(function() {
		$(".select2").select2();
	});

	function viewUser() {
		if ($('#userId').val() != "") {
			$.ajax({
				async: false,
				url: 'get-user.php?userId=' + $('#userId').val(),
				dataType: 'text',
				success: function(data) {
					$('#viewUser').html(data);
					$('.viewUser').removeClass('hide');
				}
			});
		} else {
			$('.viewUser').addClass('hide');
		}
	}

	function validateNow() {
		flag = deforayValidator.init({
			formId: 'resetPasswordForm'
		});

		if (flag) {
			if ($('.ppwd').val() != '') {
				pwdflag = checkPasswordLength();
			}
			if (pwdflag) {
				$.blockUI();
				document.getElementById('resetPasswordForm').submit();
			}
		}
	}

	function checkPasswordLength() {
		var pwd = $('#confirmPassword').val();
		var regex = /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9!@#\$%\^\&*\)\(+=. _-]+){8,}$/;
		if (regex.test(pwd) == false) {
			alert("<?= _translate("Password must be at least 8 characters long and must include AT LEAST one number, one alphabet and may have special characters.", true) ?>");
			$('.ppwd').focus();
		}
		return regex.test(pwd);
	}

	async function passwordType() {
		document.getElementById('password').type = "text";
		document.getElementById('confirmPassword').type = "text";
		const data = await $.post("/includes/generate-password.php", {
			size: 32
		});
		$("#password").val(data);
		$("#confirmPassword").val(data);
		try {
			const success = await Utilities.copyToClipboard(data);
			if (success) {
				toast.success("<?= _translate("Password generated and copied to clipboard", true); ?>");
			} else {
				console.log('Failed to copy text');
			}
		} catch (error) {
			console.log(error);
		}
	}
</script>
<?php
require_once(APPLICATION_PATH . '/system-admin/admin-footer.php');
