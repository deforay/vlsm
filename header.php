<?php

if (!isset($_SESSION['userId'])) {
	header("location:/login.php");
}




$general = new \Vlsm\Models\General($db);

/* Crosss Login Block Start */
$crossLoginQuery = "SELECT `login_id`,`password`,`user_name` FROM `user_details` WHERE user_id = '" . $_SESSION['userId'] . "'";
$crossLoginResult = $db->rawQueryOne($crossLoginQuery);
/* Crosss Login Block End */

$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();


$skin = "skin-blue";

$logoName = "<img src='/assets/img/flask.png' style='margin-top:-5px;max-width:22px;'> <span style=''>VLSM</span>";
$smallLogoName = "<img src='/assets/img/flask.png'>";
$systemType = "Lab Sample Management Module";
$shortName = "Sample Management";
if (isset($sarr['user_type']) && $sarr['user_type'] == 'remoteuser') {
	$skin = "skin-red";
	$systemType = "Remote Sample Tracking Module";
	$logoName = "<i class='fa fa-medkit'></i> VLSTS";
	$smallLogoName = "<i class='fa fa-medkit'></i>";
	$shortName = "Sample Tracking";
}

if (isset($systemConfig['instanceName']) && !empty($systemConfig['instanceName'])) {
	$systemType = $systemConfig['instanceName'];
}

if (isset($arr['default_time_zone']) && $arr['default_time_zone'] != '') {
	date_default_timezone_set($arr['default_time_zone']);
} else {
	date_default_timezone_set(!empty(date_default_timezone_get()) ?  date_default_timezone_get() : "UTC");
}
$hideResult = '';
$hideRequest = '';
if (isset($arr['instance_type']) && $arr['instance_type'] != '') {
	if ($arr['instance_type'] == 'Clinic/Lab') {
		$hideResult = "display:none;";
	}
}


$link = $_SERVER['PHP_SELF'];
$link_array = explode('/', $link);

$currentFileName = end($link_array);

// These files don't need privileges check
$skipPrivilegeCheckFiles = array(
	'401.php',
	'404.php',
	'editProfile.php',
	'vlExportField.php'
);

// on the left put intermediate/inner file, on the right put the file
// which has entry in privileges table.
$sharedPrivileges = array(
	'eid-add-batch-position.php'  		=> 'eid-add-batch.php',
	'eid-edit-batch-position.php' 		=> 'eid-edit-batch.php',
	'eid-update-result.php'       		=> 'eid-manual-results.php',
	'covid-19-add-batch-position.php'	=> 'covid-19-add-batch.php',
	'mail-covid-19-results.php'  		=> 'covid-19-print-results.php',
	'covid-19-result-mail-confirm.php'  => 'covid-19-print-results.php',
	'covid-19-edit-batch-position.php' 	=> 'covid-19-edit-batch.php',
	'covid-19-update-result.php'       	=> 'covid-19-manual-results.php',
	'imported-results.php'        		=> 'addImportResult.php',
	'importedStatistics.php'      		=> 'addImportResult.php',
	'covid-19-bulk-import-request.php'	=> 'covid-19-add-request.php',
	'addFacilityTestType.php'			=> 'addFacility.php',
);

// Does the current file share privileges with another privilege ?
$currentFileName = isset($sharedPrivileges[$currentFileName]) ? $sharedPrivileges[$currentFileName] : $currentFileName;


if (!in_array($currentFileName, $skipPrivilegeCheckFiles)) {
	if (isset($_SESSION['privileges']) && !in_array($currentFileName, $_SESSION['privileges'])) {
		header("location:/error/401.php");
	}
}
// if(isset($_SERVER['HTTP_REFERER'])){
//   $previousUrl = $_SERVER['HTTP_REFERER'];
//   $urlLast = explode('/',$previousUrl);
//   if(end($urlLast)=='importedStatistics.php'){
//       $db = $db->where('imported_by', $_SESSION['userId']);
//       $db->delete('temp_sample_import');
//       unset($_SESSION['controllertrack']);
//   }
// }
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('roles.php', 'users.php', 'facilities.php', 'globalConfig.php', 'importConfig.php', 'otherConfig.php'))) {
	$allAdminMenuAccess = true;
} else {
	$allAdminMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('vlRequest.php', 'addVlRequest.php', 'batchcode.php', 'vlRequestMail.php', 'specimenReferralManifestList.php', 'sample-list.php'))) {
	$requestMenuAccess = true;
} else {
	$requestMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('addImportResult.php', 'vlTestResult.php', 'vlResultApproval.php', 'vlResultMail.php', 'vlWeeklyReport.php', 'sampleRejectionReport.php', 'vlMonitoringReport.php'))) {
	$testResultMenuAccess = true;
} else {
	$testResultMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('vl-sample-status.php', 'vlResult.php', 'highViralLoad.php', 'vlControlReport.php'))) {
	$managementMenuAccess = true;
} else {
	$managementMenuAccess = false;
}

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('eid-export-data.php', 'eid-sample-rejection-report.php', 'eid-sample-status.php', 'eid-print-results.php'))) {
	$eidManagementMenuAccess = true;
} else {
	$eidManagementMenuAccess = false;
}
if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('readQRCode.php', 'generate.php'))) {
	$grCodeMenuAccess = true;
} else {
	$grCodeMenuAccess = false;
}
if (isset($_SESSION['privileges']) && in_array(('index.php'), $_SESSION['privileges'])) {
	$dashBoardMenuAccess = true;
} else {
	$dashBoardMenuAccess = false;
}


// EID MENUS


// COVID-19 Menu start

if (isset($_SESSION['privileges']) && array_intersect($_SESSION['privileges'], array('covid-19-export-data.php', 'covid-19-sample-rejection-report.php', 'covid-19-sample-status.php', 'covid-19-print-results.php'))) {
	$covid19ManagementMenuAccess = true;
} else {
	$covid19ManagementMenuAccess = false;
}

// COVID-19 Menu end


$formConfigQuery = "SELECT * from global_config where name='vl_form'";
$formConfigResult = $db->query($formConfigQuery);
?>
<!DOCTYPE html>
<html lang="en-US">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo $shortName . " | " . ((isset($title) && $title != null && $title != "") ? $title : "VLSM"); ?></title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">


	<?php if (isset($sarr['user_type']) && $sarr['user_type'] == 'remoteuser') { ?>
		<link rel="apple-touch-icon" sizes="180x180" href="/vlsts-icons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/vlsts-icons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/vlsts-icons/favicon-16x16.png">
		<link rel="manifest" href="/vlsts-icons/site.webmanifest">
	<?php } else { ?>
		<link rel="apple-touch-icon" sizes="180x180" href="/vlsm-icons/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/vlsm-icons/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/vlsm-icons/favicon-16x16.png">
		<link rel="manifest" href="/vlsm-icons/site.webmanifest">
	<?php } ?>


	<link rel="stylesheet" media="all" type="text/css" href="/assets/css/fonts.css" />

	<link rel="stylesheet" media="all" type="text/css" href="/assets/css/jquery-ui.1.11.0.css" />
	<link rel="stylesheet" media="all" type="text/css" href="/assets/css/jquery-ui-timepicker-addon.css" />

	<!-- Bootstrap 3.3.6 -->
	<link rel="stylesheet" href="/assets/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="/assets/css/font-awesome.min.4.5.0.css">

	<!-- Ionicons -->
	<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">-->
	<!-- DataTables -->
	<link rel="stylesheet" href="/assets/plugins/datatables/dataTables.bootstrap.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="/assets/css/AdminLTE.min.css">
	<!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
	<link rel="stylesheet" href="/assets/css/skins/_all-skins.min.css">
	<!-- iCheck -->

	<link href="/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet" />

	<link href="/assets/css/select2.min.css" rel="stylesheet" />
	<link href="/assets/css/style.css" rel="stylesheet" />
	<link href="/assets/css/deforayModal.css" rel="stylesheet" />
	<link href="/assets/css/jquery.fastconfirm.css" rel="stylesheet" />


	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
	<!-- jQuery 2.2.3 -->

	<script type="text/javascript" src="/assets/js/jquery.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->

	<script type="text/javascript" src="/assets/js/jquery-ui.1.11.0.js"></script>
	<script src="/assets/js/deforayModal.js"></script>
	<script src="/assets/js/jquery.fastconfirm.js"></script>
	<!--<script type="text/javascript" src="assets/js/jquery-ui-sliderAccess.js"></script>-->
	<style>
		.dataTables_empty {
			text-align: center;
		}

		.dataTables_wrapper {
			position: relative;
			clear: both;
			overflow-x: scroll !important;
			overflow-y: visible !important;
			padding: 15px 0 !important;
		}

		.select2-selection__choice__remove {
			color: red !important;
		}

		.select2-container--default .select2-selection--multiple .select2-selection__choice {
			/* background-color: #00c0ef;
			border-color: #00acd6; */
			color: #000 !important;
			font-family: helvetica, arial, sans-serif;
		}

		.skin-blue .sidebar-menu>li.header {
			background: #ddd;
			color: #333;
			font-weight: bold;
		}

		.skin-red .sidebar-menu>li.header {
			background: #ddd;
			color: #333;
			font-weight: bold;
		}
	</style>
</head>

<body class="hold-transition <?php echo $skin; ?> sidebar-mini">
	<div class="wrapper">
		<header class="main-header">
			<!-- Logo -->
			<a href="<?php echo ($dashBoardMenuAccess == true) ? '/dashboard/index.php' : '#'; ?>" class="logo">
				<!-- mini logo for sidebar mini 50x50 pixels -->
				<span class="logo-mini"><b><?php echo $smallLogoName; ?></b></span>
				<!-- logo for regular state and mobile devices -->
				<span class="logo-lg" style="font-weight:bold;"><?php echo $logoName; ?></span>
			</a>
			<!-- Header Navbar: style can be found in header.less -->
			<nav class="navbar navbar-static-top">
				<!-- Sidebar toggle button-->
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>
				<ul class="nav navbar-nav">
					<li>
						<a href="javascript:void(0);return false;"><span style="text-transform: uppercase;font-weight:600;"><?php echo $systemType; ?></span></a>
					</li>
				</ul>
				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<?php if ($recencyConfig['crosslogin']) {
							$password = $crossLoginResult['password'] . $recencyConfig['crossloginSalt']; ?>
							<li class="user-menu">
								<a onclick="setCrossLogin();" href="<?php echo rtrim($recencyConfig['url'], "/") . '/login?u=' . base64_encode($crossLoginResult['login_id']) . '&t=' . hash('sha256', $password) . '&name=' . base64_encode($crossLoginResult['user_name']); ?>" class="btn btn-link"><i class="fa fa-fw fa-external-link"></i> Recency</a>
							</li>
						<?php } ?>
						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<img src="/assets/img/default-user.png" class="user-image" alt="User Image">
								<span class="hidden-xs"><?php if (isset($_SESSION['userName'])) {
															echo $_SESSION['userName'];
														} ?></span>
							</a>
							<ul class="dropdown-menu">
								<!-- Menu Footer-->
								<?php $alignRight = '';
								$showProfileBtn = "style=display:none;";
								if ($arr['edit_profile'] != 'no') {
									$alignRight = "pull-right-xxxxx";
									$showProfileBtn = "style=display:block;";
								} ?>
								<li class="user-footer" <?php echo $showProfileBtn; ?>>
									<a href="/users/editProfile.php" class="">Edit Profile</a>
								</li>
								<li class="user-footer <?php echo $alignRight; ?>">
									<a href="/logout.php">Sign out</a>
								</li>

							</ul>
						</li>
					</ul>
				</div>
			</nav>
		</header>
		<!-- Left side column. contains the logo and sidebar -->
		<aside class="main-sidebar">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<!-- sidebar menu: : style can be found in sidebar.less -->
				<!-- Sidebar user panel -->
				<?php if (isset($arr['logo']) && trim($arr['logo']) != "" && file_exists('uploads' . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])) { ?>
					<div class="user-panel">
						<div align="center">
							<img src="/uploads/logo/<?php echo $arr['logo']; ?>" alt="Logo Image" style="max-width:120px;">
						</div>
					</div>
				<?php } ?>
				<ul class="sidebar-menu">
					<?php if ($dashBoardMenuAccess == true) { ?>
						<li class="allMenu dashboardMenu active">
							<a href="/dashboard/index.php">
								<i class="fa fa-dashboard"></i> <span>Dashboard</span>
							</a>
						</li>
					<?php }
					if ($allAdminMenuAccess == true) { ?>
						<li class="treeview manage">
							<a href="#">
								<i class="fa fa-gears"></i>
								<span>Admin</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<?php if (isset($_SESSION['privileges']) && in_array("roles.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu roleMenu">
										<a href="/roles/roles.php"><i class="fa fa-circle-o"></i> Roles</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("users.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu userMenu">
										<a href="/users/users.php"><i class="fa fa-circle-o"></i> Users</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("facilities.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu facilityMenu">
										<a href="/facilities/facilities.php"><i class="fa fa-circle-o"></i> Facilities</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("facilityMap.php", $_SESSION['privileges']) && ($sarr['user_type'] == 'remoteuser')) { ?>
									<li class="allMenu facilityMapMenu">
										<a href="/facilities/facilityMap.php"><i class="fa fa-circle-o"></i>Facility Map</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("globalConfig.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu globalConfigMenu">
										<a href="/global-config/globalConfig.php"><i class="fa fa-circle-o"></i> General Configuration</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("importConfig.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu importConfigMenu">
										<a href="/import-configs/importConfig.php"><i class="fa fa-circle-o"></i> Import Configuration</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("testRequestEmailConfig.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu requestEmailConfigMenu">
										<a href="/vl/request-mail/testRequestEmailConfig.php"><i class="fa fa-circle-o"></i>Test Request Email/SMS <br>Configuration</a>
									</li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("testResultEmailConfig.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu resultEmailConfigMenu">
										<a href="/vl/result-mail/testResultEmailConfig.php"><i class="fa fa-circle-o"></i>Test Result Email/SMS <br>Configuration</a>
									</li>
								<?php } ?>
							</ul>
						</li>


					<?php } ?>

					<?php
					if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) { ?>

						<li class="header">VIRAL LOAD</li>

						<?php if ($requestMenuAccess == true) { ?>
							<li class="treeview request" style="<?php echo $hideRequest; ?>">
								<a href="#">
									<i class="fa fa-edit"></i>
									<span>Request Management</span>
									<span class="pull-right-container">
										<i class="fa fa-angle-left pull-right"></i>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php
									if (isset($_SESSION['privileges']) && in_array("vlRequest.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlRequestMenu">
											<a href="/vl/requests/vlRequest.php"><i class="fa fa-circle-o"></i> View Test Requests</a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addVlRequest.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu addVlRequestMenu">
											<a href="/vl/requests/addVlRequest.php"><i class="fa fa-circle-o"></i> Add New Request</a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges']) && ($sarr['user_type'] != 'remoteuser')) { ?>
										<li class="allMenu addSamplesFromManifestMenu">
											<a href="/vl/requests/addSamplesFromManifest.php"><i class="fa fa-circle-o"></i> Add Samples from Manifest</a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("batchcode.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu batchCodeMenu">
											<a href="/vl/batch/batchcode.php"><i class="fa fa-circle-o"></i> Manage Batch</a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlRequestMail.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlRequestMailMenu">
											<a href="/mail/vlRequestMail.php"><i class="fa fa-circle-o"></i> E-mail Test Request</a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($sarr['user_type'] == 'remoteuser')) { ?>
										<li class="allMenu specimenReferralManifestListMenu">
											<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('vl'); ?>"><i class="fa fa-circle-o"></i> VL Specimen Manifest</a>
										</li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("sampleList.php", $_SESSION['privileges']) && ($sarr['user_type'] == 'remoteuser')) { ?>
										<!-- <li class="allMenu sampleListMenu">
                                            <a href="/move-samples/sampleList.php"><i class="fa fa-circle-o"></i> Move Samples</a>
                                          </li> -->
									<?php } ?>
								</ul>
							</li>
						<?php }
						if ($testResultMenuAccess == true) { ?>
							<li class="treeview test" style="<?php echo $hideResult; ?>">
								<a href="#">
									<i class="fa fa-tasks"></i>
									<span>Test Result Management</span>
									<span class="pull-right-container">
										<i class="fa fa-angle-left pull-right"></i>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu importResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('vl'); ?>"><i class="fa fa-circle-o"></i> Import Result From File</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlTestResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlTestResultMenu"><a href="/vl/results/vlTestResult.php"><i class="fa fa-circle-o"></i> Enter Result Manually</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlResultApproval.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlResultApprovalMenu"><a href="/vl/results/vlResultApproval.php"><i class="fa fa-circle-o"></i> Approve Results</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlResultMail.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlResultMailMenu"><a href="/mail/vlResultMail.php"><i class="fa fa-circle-o"></i> E-mail Test Result</a></li>
									<?php }  ?>
								</ul>
							</li>
						<?php } ?>

						<?php
						if ($managementMenuAccess == true) { ?>
							<li class="treeview program">
								<a href="#">
									<i class="fa fa-book"></i>
									<span>Management</span>
									<span class="pull-right-container">
										<i class="fa fa-angle-left pull-right"></i>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("vl-sample-status.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu missingResultMenu"><a href="/vl/program-management/vl-sample-status.php"><i class="fa fa-circle-o"></i> Sample Status Report</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlControlReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlControlReport"><a href="/vl/program-management/vlControlReport.php"><i class="fa fa-circle-o"></i> Control Report</a></li>
									<?php } ?>
									<!--<li><a href="#"><i class="fa fa-circle-o"></i> TOT Report</a></li>
                                <li><a href="#"><i class="fa fa-circle-o"></i> VL Suppression Report</a></li>-->
									<?php if (isset($_SESSION['privileges']) && in_array("vlResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlResultMenu"><a href="/vl/program-management/vlResult.php"><i class="fa fa-circle-o"></i> Export Results</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlPrintResult.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlPrintResultMenu"><a href="/vl/results/vlPrintResult.php"><i class="fa fa-circle-o"></i> Print Result</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("highViralLoad.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlHighMenu"><a href="/vl/program-management/highViralLoad.php"><i class="fa fa-circle-o"></i> Clinic Reports</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("patientList.php", $_SESSION['privileges'])) { ?>
										<!--<li class="allMenu patientList"><a href="patientList.php"><i class="fa fa-circle-o"></i> Export Patient List</a></li>-->
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlWeeklyReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlWeeklyReport"><a href="/vl/program-management/vlWeeklyReport.php"><i class="fa fa-circle-o"></i> VL Lab Weekly Report</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("sampleRejectionReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu sampleRejectionReport"><a href="/vl/program-management/sampleRejectionReport.php"><i class="fa fa-circle-o"></i> Sample Rejection Report</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("vlMonitoringReport.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu vlMonitoringReport"><a href="/vl/program-management/vlMonitoringReport.php"><i class="fa fa-circle-o"></i> Sample Monitoring Report</a></li>
									<?php } ?>
								</ul>
							</li>
						<?php
						} ?>

						<?php
						if (isset($arr['enable_qr_mechanism']) && trim($arr['enable_qr_mechanism']) == 'yes' && $grCodeMenuAccess == true) { ?>
							<li class="treeview qr">
								<a href="#">
									<i class="fa fa-qrcode"></i>
									<span>QR Code</span>
									<span class="pull-right-container">
										<i class="fa fa-angle-left pull-right"></i>
									</span>
								</a>
								<ul class="treeview-menu">
									<?php if (isset($_SESSION['privileges']) && in_array("generate.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu generateQRCode"><a href="/qr-code/generate.php"><i class="fa fa-circle-o"></i> Generate QR Code</a></li>
									<?php }
									if (isset($_SESSION['privileges']) && in_array("readQRCode.php", $_SESSION['privileges'])) { ?>
										<li class="allMenu readQRCode"><a href="/qr-code/readQRCode.php"><i class="fa fa-circle-o"></i> Read QR Code</a></li>
									<?php } ?>
								</ul>
							</li>
						<?php } ?>
					<?php } ?>

					<?php if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {  ?>
						<li class="header">EARLY INFANT DIAGNOSIS (EID)</li>
						<li class="treeview eidRequest" style="<?php echo $hideRequest; ?>">
							<a href="#">
								<i class="fa fa-edit"></i>
								<span>Request Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<li class="allMenu eidRequestMenu">
									<a href="/eid/requests/eid-requests.php"><i class="fa fa-circle-o"></i> View Test Requests</a>
								</li>
								<li class="allMenu addEidRequestMenu">
									<a href="/eid/requests/eid-add-request.php"><i class="fa fa-circle-o"></i> Add New Request</a>
								</li>
								<?php if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges']) && ($sarr['user_type'] != 'remoteuser')) { ?>
									<li class="allMenu addSamplesFromManifestEidMenu">
										<a href="/eid/requests/addSamplesFromManifest.php"><i class="fa fa-circle-o"></i> Add Samples from Manifest</a>
									</li>
								<?php } ?>
								<li class="allMenu eidBatchCodeMenu">
									<a href="/eid/batch/eid-batches.php"><i class="fa fa-circle-o"></i> Manage Batch</a>
								</li>
								<?php
								if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($sarr['user_type'] == 'remoteuser')) { ?>
									<li class="allMenu specimenReferralManifestListMenu">
										<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('eid'); ?>"><i class="fa fa-circle-o"></i> EID Specimen Manifest</a>
									</li>
								<?php } ?>
							</ul>
						</li>

					<?php }
					?>


					<?php
					if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true && $testResultMenuAccess == true) { ?>
						<li class="treeview eidResults" style="<?php echo $hideResult; ?>">
							<a href="#">
								<i class="fa fa-tasks"></i>
								<span>Test Result Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidImportResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('eid'); ?>"><i class="fa fa-circle-o"></i> Import Result From File</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("eid-manual-results.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidResultsMenu"><a href="/eid/results/eid-manual-results.php"><i class="fa fa-circle-o"></i> Enter Result Manually</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("eid-result-status.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidResultStatus"><a href="/eid/results/eid-result-status.php"><i class="fa fa-circle-o"></i> Manage Results Status</a></li>
								<?php } ?>
							</ul>
						</li>
					<?php } ?>


					<?php
					if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true && $eidManagementMenuAccess == true) { ?>
						<li class="treeview eidProgramMenu">
							<a href="#">
								<i class="fa fa-book"></i>
								<span>Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<?php if (isset($_SESSION['privileges']) && in_array("eid-sample-status.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidSampleStatus"><a href="/eid/management/eid-sample-status.php"><i class="fa fa-circle-o"></i> Sample Status Report</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("eid-export-data.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidExportResult"><a href="/eid/management/eid-export-data.php"><i class="fa fa-circle-o"></i> Export Results</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("eid-print-results.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidPrintResults"><a href="/eid/results/eid-print-results.php"><i class="fa fa-circle-o"></i> Print Result</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("eid-sample-rejection-report.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidSampleRejectionReport"><a href="/eid/management/eid-sample-rejection-report.php"><i class="fa fa-circle-o"></i> Sample Rejection Report</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("eid-clinic-report.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu eidClinicReport"><a href="/eid/management/eid-clinic-report.php"><i class="fa fa-circle-o"></i> Clinic Report</a></li>
								<?php } ?>
							</ul>
						</li>
					<?php
					} ?>


					<!-- COVID-19 START -->

					<?php if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {  ?>
						<li class="header">COVID-19</li>
						<li class="treeview covid19Request" style="<?php echo $hideRequest; ?>">
							<a href="#">
								<i class="fa fa-edit"></i>
								<span>Request Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<li class="allMenu covid19RequestMenu">
									<a href="/covid-19/requests/covid-19-requests.php"><i class="fa fa-circle-o"></i> View Test Requests</a>
								</li>
								<li class="allMenu addCovid19RequestMenu">
									<a href="/covid-19/requests/covid-19-add-request.php"><i class="fa fa-circle-o"></i> Add New Request</a>
								</li>
								<?php if (isset($_SESSION['privileges']) && in_array("addSamplesFromManifest.php", $_SESSION['privileges']) && ($sarr['user_type'] != 'remoteuser')) { ?>
									<li class="allMenu addSamplesFromManifestCovid19Menu">
										<a href="/covid-19/requests/addSamplesFromManifest.php"><i class="fa fa-circle-o"></i> Add Samples from Manifest</a>
									</li>
								<?php }?>
								<li class="allMenu covid19BatchCodeMenu">
									<a href="/covid-19/batch/covid-19-batches.php"><i class="fa fa-circle-o"></i> Manage Batch</a>
								</li>
								<?php
								if (isset($_SESSION['privileges']) && in_array("specimenReferralManifestList.php", $_SESSION['privileges']) && ($sarr['user_type'] == 'remoteuser')) { ?>
									<li class="allMenu specimenReferralManifestListMenu">
										<a href="/specimen-referral-manifest/specimenReferralManifestList.php?t=<?php echo base64_encode('covid19'); ?>"><i class="fa fa-circle-o"></i> Covid-19 Specimen Manifest</a>
									</li>
								<?php } ?>
							</ul>
						</li>

					<?php }
					?>


					<?php
					if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true && $testResultMenuAccess == true) { ?>
						<li class="treeview covid19Results" style="<?php echo $hideResult; ?>">
							<a href="#">
								<i class="fa fa-tasks"></i>
								<span>Test Result Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<?php if (isset($_SESSION['privileges']) && in_array("addImportResult.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ImportResultMenu"><a href="/import-result/addImportResult.php?t=<?php echo base64_encode('covid19'); ?>"><i class="fa fa-circle-o"></i> Import Result From File</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("covid-19-manual-results.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ResultsMenu"><a href="/covid-19/results/covid-19-manual-results.php"><i class="fa fa-circle-o"></i> Enter Result Manually</a></li>
								<?php }
								if ($arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes' && isset($_SESSION['privileges']) && in_array("covid-19-confirmation-manifest.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ResultsConfirmationMenu"><a href="/covid-19/results/covid-19-confirmation-manifest.php"><i class="fa fa-circle-o"></i> Confirmation Manifest</a></li>
								<?php }
								if ($arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes' && isset($_SESSION['privileges']) && in_array("can-record-confirmatory-tests.php", $_SESSION['privileges']) && ($sarr['user_type'] != 'remoteuser')) { ?>
									<li class="allMenu canRecordConfirmatoryTestsCovid19Menu"><a href="/covid-19/results/can-record-confirmatory-tests.php"><i class="fa fa-circle-o"></i> Record Confirmatory Tests</a></li>
								<?php } 
								if (isset($_SESSION['privileges']) && in_array("covid-19-result-status.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ResultStatus"><a href="/covid-19/results/covid-19-result-status.php"><i class="fa fa-circle-o"></i> Manage Results Status</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("covid-19-print-results.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ResultMailMenu"><a href="/covid-19/mail/mail-covid-19-results.php"><i class="fa fa-circle-o"></i> E-mail Test Result</a></li>
								<?php }  ?>
							</ul>
						</li>
					<?php } ?>


					<?php
					if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true && $covid19ManagementMenuAccess == true) { ?>
						<li class="treeview covid19ProgramMenu">
							<a href="#">
								<i class="fa fa-book"></i>
								<span>Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<?php if (isset($_SESSION['privileges']) && in_array("covid-19-sample-status.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19SampleStatus"><a href="/covid-19/management/covid-19-sample-status.php"><i class="fa fa-circle-o"></i> Sample Status Report</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("covid-19-export-data.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ExportResult"><a href="/covid-19/management/covid-19-export-data.php"><i class="fa fa-circle-o"></i> Export Results</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("covid-19-print-results.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19PrintResults"><a href="/covid-19/results/covid-19-print-results.php"><i class="fa fa-circle-o"></i> Print Result</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("covid-19-sample-rejection-report.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19SampleRejectionReport"><a href="/covid-19/management/covid-19-sample-rejection-report.php"><i class="fa fa-circle-o"></i> Sample Rejection Report</a></li>
								<?php }
								if (isset($_SESSION['privileges']) && in_array("covid-19-clinic-report.php", $_SESSION['privileges'])) { ?>
									<li class="allMenu covid19ClinicReportMenu"><a href="/covid-19/management/covid-19-clinic-report.php"><i class="fa fa-circle-o"></i> Clinic Report</a></li>
								<?php } ?>
							</ul>
						</li>
					<?php
					} ?>


					<!-- COVID-19 END -->

					<!---->
				</ul>
			</section>
			<!-- /.sidebar -->
		</aside>
		<!-- content-wrapper -->
		<div id="dDiv" class="dialog">
			<div style="text-align:center"><span onclick="closeModal();" style="float:right;clear:both;" class="closeModal"></span></div>
			<iframe id="dFrame" src="" style="border:none;" scrolling="yes" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0">some problem</iframe>
		</div>