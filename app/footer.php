<?php

use App\Services\CommonService;
use App\Services\SystemService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var SystemService $systemService */
$systemService = ContainerRegistry::get(SystemService::class);

$supportEmail = trim((string) $general->getGlobalConfig('support_email'));


$remoteUrl = $general->getRemoteURL();


if (_isAllowed("sync-history.php")) {
	$syncHistory = "/common/reference/sync-history.php";
} else {
	$syncHistory = "javascript:void(0);";
}

$syncLatestTime = $general->getLastRemoteSyncDateTime();

if (empty($syncLatestTime)) {
	$syncHistoryDisplay = "display:none;";
} else {
	$syncHistoryDisplay = "display:inline;";
}

?>

<footer class="main-footer">

	<div class="row">
		<div class="col-lg-8 col-sm-8">
			<small><?= _translate("This project is supported by the U.S. President's Emergency Plan for AIDS Relief (PEPFAR) through the U.S.
		Centers for Disease Control and Prevention (CDC)."); ?>
			</small>
			<br>
			<small class="text-muted"><a href="javascript:void(0);" onclick="clearCache();" style="font-size:0.8em;"><?= _translate("Clear Cache"); ?></a></small>
		</div>
		<div class=" col-lg-4 col-sm-4">

			<small class="pull-right" style="font-weight:bold;">&nbsp;&nbsp;
				<?php echo "v" . VERSION; ?>
			</small>
			<?php

			if (!empty($remoteUrl) && isset($_SESSION['userName']) && $general->isLISInstance()) { ?>

				<small class="pull-right">
					<a href="javascript:syncRemoteData();">
						<?= _translate("Force Remote Sync"); ?>
					</a>&nbsp;&nbsp;
				</small>

			<?php
			}
			?>
			<br>
			<span class="syncHistoryDiv" style="float:right;font-size:x-small;<?= $syncHistoryDisplay ?>" class="pull-right">
				<a href="<?= $syncHistory; ?>" class="text-muted">
					<?= _translate("Last synced at"); ?>
					<span class="lastSyncDateTime">
						<?= $syncLatestTime; ?>
					</span>
				</a>
			</span>
		</div>
	</div>
	<?php if (!empty($supportEmail)) { ?>
		<small><a href="javascript:void(0);" onclick="showModal('/support/index.php?fUrl=<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>', 900, 520);">Support</a></small>
	<?php } ?>


</footer>
</div>

<script type="text/javascript" src="/assets/js/toastify.js"></script>
<script type="text/javascript" src="/assets/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="/assets/js/js.cookie.js"></script>
<script type="text/javascript" src="/assets/js/select2.min.js"></script>
<script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/assets/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="/assets/js/dayjs.min.js"></script>
<script type="text/javascript" src="/assets/js/dayjs.customParseFormat.js"></script>
<script type="text/javascript" src="/assets/js/dayjs.utc.js"></script>
<script type="text/javascript" src="/assets/js/dayjs.timezone.js"></script>
<script type="text/javascript" src="/assets/js/app.min.js"></script>
<script type="text/javascript" src="/assets/js/deforayValidation.js"></script>
<script type="text/javascript" src="/assets/js/jquery.maskedinput.js"></script>
<script type="text/javascript" src="/assets/js/jquery.blockUI.js"></script>
<script type="text/javascript" src="/assets/js/highcharts.js"></script>
<script type="text/javascript" src="/assets/js/highcharts-exporting.js"></script>
<script type="text/javascript" src="/assets/js/highcharts-offline-exporting.js"></script>
<script type="text/javascript" src="/assets/js/highcharts-accessibility.js"></script>
<script type="text/javascript" src="/assets/js/summernote.min.js"></script>
<script type="text/javascript" src="/assets/js/selectize.js"></script>


<?php require_once(WEB_ROOT . '/assets/js/main.js.php'); ?>
<?php require_once(WEB_ROOT . '/assets/js/dates.js.php'); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php
		$alertMsg = $_SESSION['alertMsg'] ?? '';
		if ($alertMsg !== '') {
		?>
			alert("<?= $alertMsg; ?>");
		<?php
			unset($_SESSION['alertMsg']);
		}
		unset($_SESSION['alertMsg']);

		$isLogged = $_SESSION['logged'] ?? '';
		if ($isLogged !== '') {
		?>
			setCrossLogin();
		<?php }
		// if instance facility name is not set, let us show the modal

		if (empty($_SESSION['instance']['facilityName']) || ($general->isLISInstance() && $_SESSION['instance']['labId'] == null)) {
		?>
			showModal('/new-instance.php', 900, 420);
		<?php } ?>

	});


	<?php
	if (!empty($arr['display_encrypt_pii_option']) && $arr['display_encrypt_pii_option'] == "yes") {
	?>
		$('.encryptPIIContainer').show();
	<?php
	} else {
	?>
		$('.encryptPIIContainer').hide();
	<?php
	}
	?>
</script>
</body>

</html>
