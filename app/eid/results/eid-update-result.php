<?php
ob_start();
$title = "Enter EID Result";

require_once(APPLICATION_PATH . '/header.php');

$id = base64_decode($_GET['id']);


$facilitiesDb = new \Vlsm\Models\Facilities();
$usersModel = new \Vlsm\Models\Users();
$healthFacilities = $facilitiesDb->getHealthFacilities('eid');
$testingLabs = $facilitiesDb->getTestingLabs('eid');
$facilityMap = $facilitiesDb->getFacilityMap($_SESSION['userId']);
$userResult = $usersModel->getActiveUsers($facilityMap);
$userInfo = array();
foreach ($userResult as $user) {
	$userInfo[$user['user_id']] = ucwords($user['user_name']);
}
//get import config
$importQuery = "SELECT * FROM import_config WHERE status = 'active'";
$importResult = $db->query($importQuery);

$userQuery = "SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);

//sample rejection reason
$rejectionQuery = "SELECT * FROM r_eid_sample_rejection_reasons where rejection_reason_status = 'active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery = "SELECT DISTINCT rejection_type FROM r_eid_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
//sample status
$statusQuery = "SELECT * FROM r_sample_status where status = 'active' AND status_id NOT IN(9,8,6)";
$statusResult = $db->rawQuery($statusQuery);

$pdQuery = "SELECT * from province_details";
$pdResult = $db->query($pdQuery);

$sQuery = "SELECT * from r_eid_sample_type where status='active'";
$sResult = $db->query($sQuery);

//get vl test reason list
$vlTestReasonQuery = "SELECT * from r_eid_test_reasons where test_reason_status = 'active'";
$vlTestReasonResult = $db->query($vlTestReasonQuery);

$id = base64_decode($_GET['id']);
$eidQuery = "SELECT * from form_eid where eid_id=?";
$eidInfo = $db->rawQueryOne($eidQuery, array($id));


$disable = "disabled = 'disabled'";


$iResultQuery = "SELECT * FROM import_config_machines";
$iResult = $db->rawQuery($iResultQuery);
$machine = array();
foreach ($iResult as $val) {
	$machine[$val['config_machine_id']] = $val['config_machine_name'];
}

?>

<style>
	.disabledForm {
		background: #efefef;
	}

	:disabled,
	.disabledForm .input-group-addon {
		background: none !important;
		border: none !important;
	}

	.ui_tpicker_second_label {
		display: none !important;
	}

	.ui_tpicker_second_slider {
		display: none !important;
	}

	.ui_tpicker_millisec_label {
		display: none !important;
	}

	.ui_tpicker_millisec_slider {
		display: none !important;
	}

	.ui_tpicker_microsec_label {
		display: none !important;
	}

	.ui_tpicker_microsec_slider {
		display: none !important;
	}

	.ui_tpicker_timezone_label {
		display: none !important;
	}

	.ui_tpicker_timezone {
		display: none !important;
	}

	.ui_tpicker_time_input {
		width: 100%;
	}
</style>
<?php

$fileArray = array(
	1 => 'forms/update-southsudan-result.php',
	2 => 'forms/update-zimbabwe-result.php',
	3 => 'forms/update-drc-result.php',
	4 => 'forms/update-zambia-result.php',
	5 => 'forms/update-png-result.php',
	6 => 'forms/update-who-result.php',
	7 => 'forms/update-rwanda-result.php',
	8 => 'forms/update-angola-result.php',
);

require_once($fileArray[$arr['vl_form']]);


?>

<script>
	function updateSampleResult() {
		if ($('#isSampleRejected').val() == "yes") {
			$('.rejected').show();
			$('#sampleRejectionReason').addClass('isRequired');
			$('#sampleTestedDateTime,#result').val('');
			$('#sampleTestedDateTime,#result').removeClass('isRequired');
			$(".result-optional").removeClass("isRequired");
			$('#reviewedBy').addClass('isRequired');
			$('#reviewedOn').addClass('isRequired');
			$('#approvedBy').addClass('isRequired');
			$('#approvedOnDateTime').addClass('isRequired');
		} else if ($('#isSampleRejected').val() == "no") {
			$('.rejected').hide();
			$('#sampleRejectionReason').removeClass('isRequired');
			$('#sampleTestedDateTime').addClass('isRequired');
			$('#result').addClass('isRequired');
			$('#testedBy').addClass('isRequired');
			$('#reviewedBy').addClass('isRequired');
			$('#reviewedOn').addClass('isRequired');
			$('#approvedBy').addClass('isRequired');
			$('#approvedOnDateTime').addClass('isRequired');
		} else {
			$('.rejected').hide();
			$('#sampleRejectionReason').removeClass('isRequired');
			$('#sampleTestedDateTime').removeClass('isRequired');
			$('#result').removeClass('isRequired');
			$('#testedBy').removeClass('isRequired');
			$(".result-optional").removeClass("isRequired");

			$('#reviewedBy').removeClass('isRequired');
			$('#reviewedOn').removeClass('isRequired');
			$('#approvedBy').removeClass('isRequired');
			$('#approvedOnDateTime').removeClass('isRequired');
		}

		if ($('#result').val() == "") {
			$('#sampleTestedDateTime').removeClass('isRequired');
			//$('#result').removeClass('isRequired');
		} else {
			$('#sampleTestedDateTime').addClass('isRequired');
			//$('#result').addClass('isRequired');
		}

	}

	$(document).ready(function() {
		$('#testedBy').select2({
			width: '100%',
			placeholder: "Select Tested By"
		});

		$('#approvedBy').select2({
			width: '100%',
			placeholder: "Select Approved By"
		});
		updateSampleResult();
		$("#isSampleRejected,#result").on("change", function() {
			updateSampleResult();
		});

		$('.date').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'dd-M-yy',
			timeFormat: "hh:mm TT",
			maxDate: "Today",
			yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
		}).click(function() {
			$('.ui-datepicker-calendar').show();
		});
		$('.dateTime').datetimepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'dd-M-yy',
			timeFormat: "HH:mm",
			maxDate: "Today",
			onChangeMonthYear: function(year, month, widget) {
				setTimeout(function() {
					$('.ui-datepicker-calendar').show();
				});
			},
			yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
		}).click(function() {
			$('.ui-datepicker-calendar').show();
		});
		//$('.date').mask('99-aaa-9999');
		//$('.dateTime').mask('99-aaa-9999 99:99');

		$('.result-focus').change(function(e) {
			<?php if (isset($eidInfo['result']) && $eidInfo['result'] != "") { ?>
				var status = false;
				$(".result-focus").each(function(index) {
					if ($(this).val() != "") {
						status = true;
					}
				});
				if (status) {
					$('.change-reason').show();
					$('#reasonForChanging').addClass('isRequired');
				} else {
					$('.change-reason').hide();
					$('#reasonForChanging').removeClass('isRequired');
				}
			<?php } ?>
		});
	});
</script>


<?php
require_once(APPLICATION_PATH . '/footer.php');