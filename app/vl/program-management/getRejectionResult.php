<?php

use App\Utilities\DateUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

$formId = (int) $general->getGlobalConfig('vl_form');

$tResult = [];
//$rjResult = [];
if (!empty($_POST['sampleCollectionDate'])) {
    $start_date = '';
    $end_date = '';
    $sWhere = [];

    [$start_date, $end_date] = DateUtility::convertDateRange($_POST['sampleCollectionDate']);

    //get value by rejection reason id
    $vlQuery = "SELECT count(*) as `total`, vl.reason_for_sample_rejection,sr.rejection_reason_name,sr.rejection_type,sr.rejection_reason_code,fd.facility_name, lab.facility_name as `labname`,r_c_a.recommended_corrective_action_name
                FROM form_vl as vl
                INNER JOIN r_vl_sample_rejection_reasons as sr ON sr.rejection_reason_id=vl.reason_for_sample_rejection
                LEFT JOIN r_recommended_corrective_actions as r_c_a ON r_c_a.recommended_corrective_action_id=vl.recommended_corrective_action
                INNER JOIN facility_details as fd ON fd.facility_id=vl.facility_id
                INNER JOIN facility_details as lab ON lab.facility_id=vl.lab_id";
    $sWhere[] = ' vl.is_sample_rejected = "yes" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND reason_for_sample_rejection!="" AND reason_for_sample_rejection IS NOT NULL';

    if (isset($_POST['sampleType']) && trim((string) $_POST['sampleType']) != '') {
        $sWhere[] = ' vl.sample_type = "' . $_POST['sampleType'] . '"';
    }
    if (isset($_POST['labName']) && trim((string) $_POST['labName']) != '') {
        $sWhere[] = ' vl.lab_id = "' . $_POST['labName'] . '"';
    }
    if (is_array($_POST['clinicName']) && !empty($_POST['clinicName'])) {
        $sWhere[] = " vl.facility_id IN (" . implode(',', $_POST['clinicName']) . ")";
    }
    if (!empty($_SESSION['facilityMap'])) {
        $sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ")";
    }

    if (!empty($sWhere)) {
        $sWhere = implode(' AND ', $sWhere);
    }
    $vlQuery = $vlQuery . ' where ' . $sWhere . " group by vl.reason_for_sample_rejection,vl.lab_id,vl.facility_id";
    //echo $vlQuery; die;
    $_SESSION['rejectedSamples'] = $vlQuery;
    $tableResult = $db->rawQuery($vlQuery);

    foreach ($tableResult as $tableRow) {
        $tResult[$tableRow['rejection_reason_name']]['total'] += $tableRow['total'];
        $tResult[$tableRow['rejection_reason_name']]['category'] = $tableRow['rejection_type'];

        //$rjResult[$tableRow['rejection_type']]  += $tableRow['total'];
    }
}

if (!empty($tResult)) {
?>
    <div id="container" style="width: 100%; height: 500px; margin: 20px auto;"></div>
    <!-- <div id="rejectedType" style="width: 100%; height: 400px; margin: 20px auto;margin-top:50px;"></div> -->
<?php }
if (!empty($tableResult)) { ?>
    <div class="pull-right">
        <button class="btn btn-success" type="button" onclick="exportInexcel()"><em class="fa-solid fa-cloud-arrow-down"></em>
            <?php echo _translate("Export Excel"); ?>
        </button>
    </div>
<?php } ?>
<table aria-describedby="table" id="vlRequestDataTable" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>
                <?php echo _translate("Lab Name"); ?>
            </th>
            <th>
                <?php echo _translate("Facility Name"); ?>
            </th>
            <th>
                <?php echo _translate("Rejection Reason"); ?>
            </th>
            <th>
                <?php echo _translate("Reason Category"); ?>
            </th>
            <th>
                <?php echo _translate("Recommended Corrective Action"); ?>
            </th>
            <th>
                <?php echo _translate("No. of Samples"); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($tableResult)) {
            foreach ($tableResult as $tableRow) {
        ?>
                <tr data-lab="<?php echo base64_encode((string) $_POST['labName']); ?>" data-facility="<?php echo base64_encode(implode(',', $_POST['clinicName'] ?? [])); ?>" data-daterange="<?= htmlspecialchars((string) $_POST['sampleCollectionDate']); ?>" data-type="rejection">
                    <td>
                        <?php echo ($tableRow['labname']); ?>
                    </td>
                    <td>
                        <?php echo ($tableRow['facility_name']); ?>
                    </td>
                    <td>
                        <?php echo ($tableRow['rejection_reason_name']); ?>
                    </td>
                    <td>
                        <?php echo strtoupper((string) $tableRow['rejection_type']); ?>
                    </td>
                    <td>
                        <?php echo ($tableRow['recommended_corrective_action_name']); ?>
                    </td>
                    <td>
                        <?php echo $tableRow['total']; ?>
                    </td>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>
<script>
    $(function() {
        $("#vlRequestDataTable").DataTable();
    });
    $(document).ready(function() {
        $('#vlRequestDataTable tbody').on('click', 'tr', function() {
            let facilityId = $(this).attr('data-facility');
            let lab = $(this).attr('data-lab');
            let daterange = $(this).attr('data-daterange');
            let type = $(this).attr('data-type');
            let link = "/vl/requests/vl-requests.php?labId=" + lab + "&facilityId=" + facilityId + "&daterange=" + daterange + "&type=" + type;
            window.open(link);
        });
    });

    <?php
    if (!empty($tResult)) { ?>
        $('#container').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: "<?php echo _translate("Sample Rejection Reasons"); ?>"
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: '{point.number}: <strong>{point.y}</strong>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<strong>{point.name}</strong>: {point.y}',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                colorByPoint: true,
                point: {
                    events: {
                        click: function(e) {
                            e.preventDefault();
                        }
                    }
                },
                data: [
                    <?php
                    foreach ($tResult as $reasonName => $values) {
                    ?> {
                            name: '<?php echo $reasonName; ?>',
                            y: <?php echo ($values['total']); ?>,
                            number: '<?php echo ($values['category']); ?>'
                        },
                    <?php
                    }
                    ?>
                ]
            }]
        });
    <?php }

    if (!empty($rjResult)) { ?>
        $('#rejectedType').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: "<?php echo _translate("Sample Rejection by Categories"); ?>"
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: '{point.name}: <strong>{point.y}</strong>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<strong>{point.name}</strong>: {point.y}',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                colorByPoint: true,
                point: {
                    events: {
                        click: function(e) {
                            e.preventDefault();
                        }
                    }
                },
                data: [
                    <?php
                    foreach ($rjResult as $key => $total) {
                    ?> {
                            name: '<?php echo ($key); ?>',
                            y: <?php echo ($total); ?>
                        },
                    <?php
                    }
                    ?>
                ]
            }]
        });
    <?php } ?>
</script>
