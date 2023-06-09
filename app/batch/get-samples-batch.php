<?php


use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();
/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');
/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

if (isset($_POST['type']) && $_POST['type'] == 'vl') {
    $refTable = "form_vl";
    $refPrimaryColumn = "vl_sample_id";
} elseif (isset($_POST['type']) && $_POST['type'] == 'eid') {
    $refTable = "form_eid";
    $refPrimaryColumn = "eid_id";
} elseif (isset($_POST['type']) && $_POST['type'] == 'covid19') {
    $refTable = "form_covid19";
    $refPrimaryColumn = "covid19_id";
} elseif (isset($_POST['type']) && $_POST['type'] == 'hepatitis') {
    $refTable = "form_hepatitis";
    $refPrimaryColumn = "hepatitis_id";
    $showPatientName = true;
} elseif (isset($_POST['type']) && $_POST['type'] == 'tb') {
    $refTable = "form_tb";
    $refPrimaryColumn = "tb_id";
} elseif (isset($_POST['type']) && $_POST['type'] == 'generic-tests') {
    $refTable = "form_generic";
    $refPrimaryColumn = "sample_id";
}

$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

$start_date = '';
$end_date = '';
//global config
$configQuery = "SELECT `value` FROM global_config WHERE name ='vl_form'";
$configResult = $db->query($configQuery);
$country = $configResult[0]['value'];
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $s_c_date = explode("to", $_POST['sampleCollectionDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = DateUtility::isoDateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = DateUtility::isoDateFormat(trim($s_c_date[1]));
    }
}
if (isset($_POST['sampleReceivedAtLab']) && trim($_POST['sampleReceivedAtLab']) != '') {
    $s_c_date = explode("to", $_POST['sampleReceivedAtLab']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $sampleReceivedStartDate = DateUtility::isoDateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $sampleReceivedEndDate = DateUtility::isoDateFormat(trim($s_c_date[1]));
    }
}

$query = "SELECT vl.sample_code,vl.$refPrimaryColumn,vl.facility_id,vl.result_status,vl.sample_batch_id,f.facility_name,f.facility_code FROM $refTable as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id ";
$where [] = " (vl.is_sample_rejected IS NULL OR vl.is_sample_rejected = '' OR vl.is_sample_rejected = 'no') AND (vl.reason_for_sample_rejection IS NULL OR vl.reason_for_sample_rejection ='' OR vl.reason_for_sample_rejection = 0) AND (vl.result is NULL or vl.result = '') AND vl.sample_code!=''";

if (isset($_POST['batchId'])) {
    $where[] = " (sample_batch_id = '" . $_POST['batchId'] . "' OR sample_batch_id IS NULL OR sample_batch_id = '')";
} else {
    $where[] = " (sample_batch_id IS NULL OR sample_batch_id='')";
}

if (isset($_POST['fName']) && is_array($_POST['fName']) && !empty($_POST['fName'])) {
    $where[] = " vl.facility_id IN (" . implode(',', $_POST['fName']) . ")";
}

if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    if (trim($start_date) == trim($end_date)) {
        $where[] = ' DATE(sample_collection_date) = "' . $start_date . '"';
    } else {
        $where[] = ' DATE(sample_collection_date) >= "' . $start_date . '" AND DATE(sample_collection_date) <= "' . $end_date . '"';
    }
}

if (isset($_POST['sampleReceivedAtLab']) && trim($_POST['sampleReceivedAtLab']) != '') {
    if (trim($sampleReceivedStartDate) == trim($sampleReceivedEndDate)) {
        $where[] = ' DATE(sample_received_at_vl_lab_datetime) = "' . $sampleReceivedStartDate . '"';
    } else {
        $where[] = ' DATE(sample_received_at_vl_lab_datetime) >= "' . $sampleReceivedStartDate . '" AND DATE(sample_received_at_vl_lab_datetime) <= "' . $sampleReceivedEndDate . '"';
    }
}
if (!empty($where)) {
    $query = $query . ' WHERE ' . implode(" AND ", $where);
}
$query = $query . " ORDER BY vl.sample_code ASC";
// die($query);
$result = $db->rawQuery($query);
?>

<script type="text/javascript" src="/assets/js/multiselect.min.js"></script>
<script type="text/javascript" src="/assets/js/jasny-bootstrap.js"></script>
<div class="col-md-5">
    <select name="sampleCode[]" id="search" class="form-control" size="8" multiple="multiple">
        <?php foreach ($result as $sample) { 
            if(!isset($_POST['batchId']) || $_POST['batchId'] != $sample['sample_batch_id']){ ?>
            <option value="<?php echo $sample[$refPrimaryColumn]; ?>"  <?php echo (isset($_POST['batchId']) && $_POST['batchId'] == $sample['sample_batch_id'])?"selected='selected'":"";?>><?php echo ($sample['sample_code']) . " - " . ($sample['facility_name']); ?></option>
        <?php }
    } ?>
    </select>
</div>

<div class="col-md-2">
    <button type="button" id="search_rightAll" class="btn btn-block"><em class="fa-solid fa-forward"></em></button>
    <button type="button" id="search_rightSelected" class="btn btn-block"><em class="fa-sharp fa-solid fa-chevron-right"></em></button>
    <button type="button" id="search_leftSelected" class="btn btn-block"><em class="fa-sharp fa-solid fa-chevron-left"></em></button>
    <button type="button" id="search_leftAll" class="btn btn-block"><em class="fa-solid fa-backward"></em></button>
</div>

<div class="col-md-5">
    <select name="to[]" id="search_to" class="form-control" size="8" multiple="multiple">
        <?php foreach ($result as $sample) {
        if(isset($_POST['batchId']) && $_POST['batchId'] == $sample['sample_batch_id']){ ?>
            <option value="<?php echo $sample[$refPrimaryColumn]; ?>"><?php echo ($sample['sample_code']) . " - " . ($sample['facility_name']); ?></option>
        <?php }
        } ?>
    </select>
</div>
<script>
	$(document).ready(function() {
		$('#search').multiselect({
			search: {
				left: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
				right: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
			},
			fireSearch: function(value) {
				return value.length > 2;
			},
			afterMoveToRight: function($left, $right, $options) {
				const count = $right.find('option').length;
				if (count > 0) {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + count + '/' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				} else {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				}
			},
			afterMoveToLeft: function($left, $right, $options) {
				const count = $right.find('option').length;
				if (count > 0) {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + count + '/' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				} else {
					$('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
				}
			}
		});
	});
</script>