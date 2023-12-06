<?php

use App\Services\DatabaseService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\UsersService;


$title = "TB | Add New Request";

require_once APPLICATION_PATH . '/header.php';
?>
<style>
    .ui_tpicker_second_label,
    .ui_tpicker_second_slider,
    .ui_tpicker_millisec_label,
    .ui_tpicker_millisec_slider,
    .ui_tpicker_microsec_label,
    .ui_tpicker_microsec_slider,
    .ui_tpicker_timezone_label,
    .ui_tpicker_timezone {
        display: none !important;
    }

    .ui_tpicker_time_input {
        width: 100%;
    }

    .table td,
    .table th {
        vertical-align: middle !important;
    }
</style>

<?php
/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/* Get Active users for approved / reviewed / examined by */
$userResult = $usersService->getActiveUsers($_SESSION['facilityMap']);
$userInfo = [];
foreach ($userResult as $user) {
    $userInfo[$user['user_id']] = ($user['user_name']);
}
/* Health facility list */
$healthFacilities = $facilitiesService->getHealthFacilities('tb');

/* Testing lab list */
$testingLabs = $facilitiesService->getTestingLabs('tb');

//sample rejection reason
$rejectionQuery = "SELECT * FROM r_tb_sample_rejection_reasons where rejection_reason_status = 'active'";
$rejectionResult = $db->rawQuery($rejectionQuery);

/* To create a rejection reason group options */
$rejectionReason = "";
$rejectionTypeQuery = "SELECT DISTINCT rejection_type FROM r_tb_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
foreach ($rejectionTypeResult as $type) {
    $rejectionReason .= '<optgroup label="' . ($type['rejection_type']) . '">';
    foreach ($rejectionResult as $reject) {
        if ($type['rejection_type'] == $reject['rejection_type']) {
            $rejectionReason .= '<option value="' . $reject['rejection_reason_id'] . '">' . ($reject['rejection_reason_name']) . '</option>';
        }
    }
    $rejectionReason .= '</optgroup>';
}
//Recommended corrective actions
$condition = "status ='active' AND test_type='tb'";
$correctiveActions = $general->fetchDataFromTable('r_recommended_corrective_actions', $condition);

$minPatientIdLength = 0;
if (isset($arr['tb_min_patient_id_length']) && $arr['tb_min_patient_id_length'] != "") {
    $minPatientIdLength = $arr['tb_min_patient_id_length'];
}

//Funding source list
$fundingSourceList = $general->getFundingSources();

//Implementing partner list
$implementingPartnerList = $general->getImplementationPartners();


$fileArray = array(
    1 => 'forms/add-southsudan.php',
    2 => 'forms/add-sierraleone.php',
    3 => 'forms/add-drc.php',
    4 => 'forms/add-cameroon.php',
    5 => 'forms/add-png.php',
    6 => 'forms/add-who.php',
    7 => 'forms/add-rwanda.php'
);
require($fileArray[$arr['vl_form']]);

?>

<script>
    $(document).ready(function() {
        $('.date').datepicker({
            changeMonth: true,
            changeYear: true,
            onSelect: function() {
                $(this).change();
            },
            dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
            timeFormat: "HH:mm",
            maxDate: "Today",
            yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });

        $('.date-time').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                setTimeout(function() {
                    $('.ui-datepicker-calendar').show();
                });
            },
            onSelect: function(e) {},
            yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });

        $("#patientDob").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
            maxDate: "Today",
            yearRange: <?php echo (date('Y') - 120); ?> + ":" + "<?= date('Y') ?>",
            onSelect: function(dateText, inst) {
                $("#sampleCollectionDate").datepicker("option", "minDate", $("#patientDob").datepicker("getDate"));
                $(this).change();
            }
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });


        $(document).on('focus', ".dateTime", function() {
            $(this).datetimepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
                timeFormat: "HH:mm",
                maxDate: "Today",
                onChangeMonthYear: function(year, month, widget) {
                    setTimeout(function() {
                        $('.ui-datepicker-calendar').show();
                    });
                },
                yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y'); ?>"
            }).click(function() {
                $('.ui-datepicker-calendar').show();
            });
        });


        $('#sampleCollectionDate').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                setTimeout(function() {
                    $('.ui-datepicker-calendar').show();
                });
            },
            onSelect: function(e) {
                $('#sampleReceivedDate').val('');
                $('#sampleReceivedDate').datetimepicker('option', 'minDate', e);
            },
            yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });

        $('#sampleReceivedDate').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: '<?= $_SESSION['jsDateFieldFormat'] ?? 'dd-M-yy'; ?>',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                setTimeout(function() {
                    $('.ui-datepicker-calendar').show();
                });
            },
            onSelect: function(e) {
                $('#sampleTestedDateTime').val('');
                $('#sampleTestedDateTime').datetimepicker('option', 'minDate', e);
            },
            yearRange: <?= (date('Y') - 100); ?> + ":" + "<?= date('Y') ?>"
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });

        let dateFormatMask = '<?= $_SESSION['jsDateFormatMask'] ?? '99-aaa-9999'; ?>';
        $('.date').mask(dateFormatMask);
        $('.dateTime').mask(dateFormatMask + ' 99:99');

        $('#isSampleRejected').change(function(e) {
            if (this.value == 'yes') {
                $('.show-rejection').show();
                $('.test-name-table-input').prop('disabled', true);
                $('.test-name-table').addClass('disabled');
                $('#sampleRejectionReason,#rejectionDate').addClass('isRequired');
                $('#sampleTestedDateTime,#result,.test-name-table-input').removeClass('isRequired');
                $('#result').prop('disabled', true);
                $('#sampleRejectionReason').prop('disabled', false);
            } else {
                $('#rejectionDate').val('');
                $('.show-rejection').hide();
                $('.test-name-table-input').prop('disabled', false);
                $('.test-name-table').removeClass('disabled');
                $('#sampleRejectionReason,#rejectionDate').removeClass('isRequired');
                $('#sampleTestedDateTime,#result,.test-name-table-input').addClass('isRequired');
                $('#result').prop('disabled', false);
                $('#sampleRejectionReason').prop('disabled', true);
                checkPostive();
            }
        });
        $('#hasRecentTravelHistory').change(function(e) {
            if (this.value == 'no' || this.value == 'unknown') {
                $('.historyfield').hide();
                $('#countryName,#returnDate').removeClass('isRequired');
            } else if (this.value == 'yes') {
                $('.historyfield').show();
                $('#countryName,#returnDate').addClass('isRequired');
            }
        });
    });

    function showPatientList() {
        $("#showEmptyResult").hide();
        if ($.trim($("#artPatientNo").val()) != '') {
            $.post("/tb/requests/search-patients.php", {
                    artPatientNo: $.trim($("#artPatientNo").val())
                },
                function(data) {
                    if (data >= '1') {
                        showModal('patientModal.php?artNo=' + $.trim($("#artPatientNo").val()), 900, 520);
                    } else {
                        $("#showEmptyResult").show();
                    }
                });
        }
    }

    function checkSampleNameValidation(tableName, fieldName, id, fnct, alrt) {
        if ($.trim($("#" + id).val()) != '') {
            $.blockUI();
            $.post("/tb/requests/check-sample-duplicate.php", {
                    tableName: tableName,
                    fieldName: fieldName,
                    value: $("#" + id).val(),
                    fnct: fnct,
                    format: "html"
                },
                function(data) {
                    if (data != 0) {
                        // Toastify({
                        //     text: "<?= _translate('This Sample Code already exists', true) ?>",
                        //     duration: 3000,
                        //     style: {
                        //         background: 'red',
                        //     }
                        // }).showToast();
                    }
                });
            $.unblockUI();
        }
    }

    function insertSampleCode(formId, tbSampleId, sampleCode, sampleCodeKey, sampleCodeFormat, countryId, sampleCollectionDate, provinceCode = null, provinceId = null) {
        $.blockUI();
        $.post("/tb/requests/insert-sample.php", {
                sampleCode: $("#" + sampleCode).val(),
                sampleCodeKey: $("#" + sampleCodeKey).val(),
                sampleCodeFormat: $("#" + sampleCodeFormat).val(),
                countryId: countryId,
                sampleCollectionDate: $("#" + sampleCollectionDate).val(),
                provinceCode: provinceCode,
                provinceId: provinceId
            },
            function(data) {

                if (data > 0) {
                    $.unblockUI();
                    document.getElementById("tbSampleId").value = data;
                    document.getElementById(formId).submit();
                } else {
                    $.unblockUI();
                    //$("#sampleCollectionDate").val('');
                    generateSampleCode();
                    alert("<?= _translate("We could not save this form. Please try saving again.", true); ?>");
                }
            });
    }

    function calculateAgeInYears() {
        var dateOfBirth = moment($("#patientDob").val(), '<?= $_SESSION['jsDateRangeFormat'] ?? 'DD-MMM-YYYY'; ?>');
        $("#patientAge").val(moment().diff(dateOfBirth, 'years'));
    }
</script>
<?php

require_once APPLICATION_PATH . '/footer.php';
