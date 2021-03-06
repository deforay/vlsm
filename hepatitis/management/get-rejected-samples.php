<?php
ob_start();
#require_once('../../startup.php');  


$general = new \Vlsm\Models\General($db);

$facilitiesDb = new \Vlsm\Models\Facilities($db);
$facilityMap = $facilitiesDb->getFacilityMap($_SESSION['userId']);

$formId = $general->getGlobalConfig('vl_form');

$tResult = array();
//$rjResult = array();
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $start_date = '';
    $end_date = '';
    $sWhere = '';
    $s_c_date = explode("to", $_POST['sampleCollectionDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = $general->dateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = $general->dateFormat(trim($s_c_date[1]));
    }
    //get value by rejection reason id
    $vlQuery = "select count(*) as `total`, vl.reason_for_sample_rejection,sr.rejection_reason_name,sr.rejection_type,sr.rejection_reason_code,fd.facility_name, lab.facility_name as `labname`
                FROM form_hepatitis as vl
                INNER JOIN r_hepatitis_sample_rejection_reasons as sr ON sr.rejection_reason_id=vl.reason_for_sample_rejection
                INNER JOIN facility_details as fd ON fd.facility_id=vl.facility_id
                INNER JOIN facility_details as lab ON lab.facility_id=vl.lab_id
                ";
                
    $sWhere .= ' where vl.is_sample_rejected = "yes" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND vl.vlsm_country_id = "' . $formId . '" AND reason_for_sample_rejection!="" AND reason_for_sample_rejection IS NOT NULL';

    if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
        $sWhere .= ' AND s.sample_id = "' . $_POST['sampleType'] . '"';
    }
    if (isset($_POST['labName']) && trim($_POST['labName']) != '') {
        $sWhere .= ' AND vl.lab_id = "' . $_POST['labName'] . '"';
    }
    if (isset($_POST['clinicName']) && is_array($_POST['clinicName']) && count($_POST['clinicName']) > 0) {
        $sWhere .= " AND vl.facility_id IN (" . implode(',', $_POST['clinicName']) . ")";
    }
    if (!empty($facilityMap)) {
        $sWhere .= " AND vl.facility_id IN ($facilityMap)";
    }

    $vlQuery = $vlQuery . $sWhere . " group by vl.reason_for_sample_rejection,vl.lab_id,vl.facility_id";

    $tableResult = $db->rawQuery($vlQuery);
    // print_r($vlQuery);die;

    foreach ($tableResult as $tableRow) {
        if (!isset($tResult[$tableRow['rejection_reason_name']])) {
            $tResult[$tableRow['rejection_reason_name']] = array('total' => null, 'category' => null);
        }
        $tResult[$tableRow['rejection_reason_name']]['total'] += $tableRow['total'];
        $tResult[$tableRow['rejection_reason_name']]['category'] = $tableRow['rejection_type'];

        //$rjResult[$tableRow['rejection_type']]  += $tableRow['total'];
    }
}

if (isset($tResult) && count($tResult) > 0) {
?>
    <div id="container" style="width: 100%; height: 500px; margin: 20px auto;"></div>
    <!-- <div id="rejectedType" style="width: 100%; height: 400px; margin: 20px auto;margin-top:50px;"></div> -->
<?php }
if (isset($tableResult) && count($tableResult) > 0) { ?>
    <div class="pull-right">
        <button class="btn btn-success" type="button" onclick="exportInexcel()"><i class="fa fa-cloud-download" aria-hidden="true"></i> Export Excel</button>
    </div>
<?php } ?>
<table id="vlRequestDataTable" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>Lab Name</th>
            <th>Facility Name</th>
            <th>Rejection Reason</th>
            <th>Reason Category</th>
            <th>No. of Samples</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($tableResult) && count($tableResult) > 0) {
            foreach ($tableResult as $tableRow) {
        ?>
                <tr>
                    <td><?php echo ucwords($tableRow['labname']); ?></td>
                    <td><?php echo ucwords($tableRow['facility_name']); ?></td>
                    <td><?php echo ucwords($tableRow['rejection_reason_name']); ?></td>
                    <td><?php echo strtoupper($tableRow['rejection_type']); ?></td>
                    <td><?php echo $tableRow['total']; ?></td>
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
    <?php
    if (isset($tResult) && count($tResult) > 0) { ?>
        $('#container').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Sample Rejection Reasons'
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: '{point.number}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y}',
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
                            //console.log(e.point.url);
                            // window.open(e.point.url, '_blank');
                            e.preventDefault();
                        }
                    }
                },
                data: [
                    <?php
                    foreach ($tResult as $reasonName => $values) {
                    ?> {
                            name: '<?php echo $reasonName; ?>',
                            y: <?php echo ucwords($values['total']); ?>,
                            number: '<?php echo ucwords($values['category']); ?>'
                        },
                    <?php
                    }
                    ?>
                ]
            }]
        });
    <?php }

    if (isset($rjResult) && count($rjResult) > 0) { ?>
        $('#rejectedType').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Sample Rejection by Categories'
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: '{point.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y}',
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
                            //console.log(e.point.url);
                            // window.open(e.point.url, '_blank');
                            e.preventDefault();
                        }
                    }
                },
                data: [
                    <?php
                    foreach ($rjResult as $key => $total) {
                    ?> {
                            name: '<?php echo ucwords($key); ?>',
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