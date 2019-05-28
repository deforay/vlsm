<?php
ob_start();
$title = "VLSM | EID | Add New Request";
include_once('../../startup.php');
include_once(APPLICATION_PATH . '/header.php');
include_once(APPLICATION_PATH . '/General.php');
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
</style>



<?php
if ($sarr['user_type'] == 'remoteuser') {
    $labFieldDisabled = 'disabled="disabled"';
    $vlfmQuery = "SELECT GROUP_CONCAT(DISTINCT vlfm.facility_id SEPARATOR ',') as facilityId FROM vl_user_facility_map as vlfm where vlfm.user_id='" . $_SESSION['userId'] . "'";
    $vlfmResult = $db->rawQuery($vlfmQuery);
}

$general = new General($db);


if (isset($vlfmResult[0]['facilityId'])) {
    $condition = $condition . " AND facility_id IN(" . $vlfmResult[0]['facilityId'] . ")";
}
$fResult = $general->fetchDataFromTable('facility_details', $condition);


$arr = $general->getGlobalConfig();

$fileArray = array(
    1 => 'add-southsudan.php',
    2 => 'add-zimbabwe.php',
    3 => 'add-drc.php',
    4 => 'add-zambia.php',
    5 => 'add-png.php',
    6 => 'add-who.php',
    7 => 'add-rwanda.php',
    8 => 'add-angola.php',
);

require_once($fileArray[$arr['vl_form']]);

?>

<script>
    $(document).ready(function() {
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
        $('.date').mask('99-aaa-9999');
        $('.dateTime').mask('99-aaa-9999 99:99');
    });


    function checkSampleNameValidation(tableName, fieldName, id, fnct, alrt) {

    }

    function insertSampleCode(formId, eidSampleId, sampleCode, sampleCodeKey, sampleCodeFormat, countryId, sampleCollectionDate, provinceCode = null, provinceId = null) {
        $.blockUI();
        $.post("/eid/requests/insert-sample.php", {
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
                    document.getElementById("eidSampleId").value = data;
                    document.getElementById(formId).submit();
                } else {
                    $.unblockUI();
                    //$("#sampleCollectionDate").val('');
                    sampleCodeGeneration();
                    alert("We could not save this form. Please try saving again.");
                }
            });
    }
</script>



<?php

include_once(APPLICATION_PATH . '/footer.php');
