<?php
ob_start();
#require_once('../../startup.php');

$general = new \Vlsm\Models\General($db); // passing $db which is coming from startup.php
$whereCondition = '';
$configFormQuery = "SELECT * FROM global_config WHERE `name` ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);

$userType = $general->getSystemConfig('sc_user_type');

$whereCondition = '';

if ($userType == 'remoteuser') {
    $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT `facility_id` ORDER BY `facility_id` SEPARATOR ',') as `facility_id` FROM vl_user_facility_map WHERE user_id='" . $_SESSION['userId'] . "'";
    $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
    if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
        $userfacilityMapresult[0]['facility_id'] = rtrim($userfacilityMapresult[0]['facility_id'], ",");
        $whereCondition = " AND vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")";
    }
}

$tsQuery = "SELECT * FROM `r_sample_status` ORDER BY `status_id`";
$tsResult = $db->rawQuery($tsQuery);
// $sampleStatusArray = array();
// foreach($tsResult as $tsRow){
//     $sampleStatusArray = $tsRow['status_name'];
// }

$sampleStatusColors = array();

$sampleStatusColors[1] = "#dda41b"; // HOLD
$sampleStatusColors[2] = "#9a1c64"; // LOST
$sampleStatusColors[3] = "grey"; // Sample Reordered
$sampleStatusColors[4] = "#d8424d"; // Rejected
$sampleStatusColors[5] = "black"; // Invalid
$sampleStatusColors[6] = "#e2d44b"; // Sample Received at lab
$sampleStatusColors[7] = "#639e11"; // Accepted
$sampleStatusColors[8] = "#7f22e8"; // Sent to Lab
$sampleStatusColors[9] = "#4BC0D9"; // Sample Registered at Health Center

//date
$start_date = '';
$end_date = '';

if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $s_c_date = explode("to", $_POST['sampleCollectionDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = $general->dateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = $general->dateFormat(trim($s_c_date[1]));
    }
}

$tQuery = "SELECT COUNT(covid19_id) as total,status_id,status_name 
                FROM form_covid19 as vl 
                JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
                JOIN facility_details as f ON vl.lab_id=f.facility_id 
                LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
                WHERE vl.vlsm_country_id='" . $configFormResult[0]['value'] . "' $whereCondition";

//filter
$sWhere = '';
if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
    $sWhere .= ' AND b.batch_code = "' . $_POST['batchCode'] . '"';
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $sWhere .= ' AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
}
if (!empty($_POST['labName'])) {
    $sWhere .= ' AND vl.lab_id = ' . $_POST['labName'];
}
$tQuery .= " " . $sWhere;

$tQuery .= " " . "GROUP BY vl.result_status ORDER BY status_id";

//echo $tQuery;die;

$tResult = $db->rawQuery($tQuery);


//HVL and LVL Samples

$vlSuppressionQuery = "SELECT   COUNT(covid19_id) as total,
                                SUM(CASE
                                        WHEN (vl.result = 'positive' and vl.result!='' and vl.result is not null) THEN 1
                                            ELSE 0
                                        END) AS positiveResult,
                                (SUM(CASE
                                        WHEN (vl.result = 'negative' and vl.result!='' and vl.result is not null) THEN 1
                                            ELSE 0
                                        END)) AS negativeResult,
                                (SUM(CASE
                                        WHEN (vl.is_sample_rejected = 'yes' ) THEN 1
                                            ELSE 0
                                        END)) AS rejectedResult,
                                status_id,
                                status_name 
                                
                                FROM form_covid19 as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status JOIN facility_details as f ON vl.lab_id=f.facility_id LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where vl.vlsm_country_id='" . $configFormResult[0]['value'] . "' $whereCondition";

// $sWhere = " AND (vl.result!='' and vl.result is not null) ";

if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
    $sWhere .= ' AND b.batch_code = "' . $_POST['batchCode'] . '"';
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $sWhere .= ' AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
}
if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
    $sWhere .= ' AND s.sample_id = "' . $_POST['sampleType'] . '"';
}
if (!empty($_POST['labName'])) {
    $sWhere .= ' AND vl.lab_id = ' . $_POST['labName'];
}
$vlSuppressionQuery = $vlSuppressionQuery . ' ' . $sWhere;

$vlSuppressionResult = $db->rawQueryOne($vlSuppressionQuery);

//get LAB TAT
if ($start_date == '' && $end_date == '') {
    $date = strtotime(date('Y-m-d') . ' -1 year');
    $start_date = date('Y-m-d', $date);
    $end_date = date('Y-m-d');
}
$tatSampleQuery = "SELECT 
    count(*) as 'totalSamples',
    DATE_FORMAT(DATE(sample_tested_datetime), '%b-%Y') as monthDate,
    ABS(TIMESTAMPDIFF(DAY,sample_tested_datetime,sample_collection_date)) as daydiff,
    CAST(ABS(AVG(TIMESTAMPDIFF(DAY,vl.sample_tested_datetime,vl.sample_collection_date))) AS DECIMAL (10,2)) as AvgTestedDiff,
    CAST(ABS(AVG(TIMESTAMPDIFF(DAY,vl.sample_received_at_vl_lab_datetime,vl.sample_collection_date))) AS DECIMAL (10,2)) as AvgReceivedDiff,
    CAST(ABS(AVG(TIMESTAMPDIFF(DAY,vl.sample_tested_datetime,vl.sample_received_at_vl_lab_datetime))) AS DECIMAL (10,2)) as AvgReceivedTested,
    CAST(ABS(AVG(TIMESTAMPDIFF(DAY,vl.result_printed_datetime,vl.sample_collection_date))) AS DECIMAL (10,2)) as AvgReceivedPrinted,
    CAST(ABS(AVG(TIMESTAMPDIFF(DAY,vl.sample_tested_datetime,vl.result_printed_datetime))) AS DECIMAL (10,2)) as AvgResultPrinted

    FROM form_covid19 as vl 
    INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
    JOIN facility_details as f ON vl.lab_id=f.facility_id 
    LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
    
    WHERE 
    vl.result is not null
    AND vl.result != ''
    AND DATE(vl.sample_tested_datetime) >= '$start_date'
    AND DATE(vl.sample_tested_datetime) <= '$end_date'
    AND vl.vlsm_country_id='" . $configFormResult[0]['value'] . "' $whereCondition";
$sWhere = '';
if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
    $sWhere .= ' AND b.batch_code = "' . $_POST['batchCode'] . '"';
}

if (!empty($_POST['labName'])) {
    $sWhere .= ' AND vl.lab_id = ' . $_POST['labName'];
}

$tatSampleQuery .= " " . $sWhere;
$tatSampleQuery .= " GROUP BY monthDate";
$tatSampleQuery .= " HAVING daydiff < 120";
$tatSampleQuery .= " ORDER BY sample_tested_datetime";

//echo $tatSampleQuery;

$tatResult = $db->rawQuery($tatSampleQuery);
$j = 0;
foreach ($tatResult as $sRow) {
    if ($sRow["monthDate"] == null) {
        continue;
    }

    $result['totalSamples'][$j] = (isset($sRow["totalSamples"]) && $sRow["totalSamples"] > 0 && $sRow["totalSamples"] != null) ? $sRow["totalSamples"] : 'null';
    $result['avgResultPrinted'][$j] = (isset($sRow["AvgResultPrinted"]) && $sRow["AvgResultPrinted"] > 0 && $sRow["AvgResultPrinted"] != null) ? $sRow["AvgResultPrinted"] : 'null';
    $result['sampleTestedDiff'][$j] = (isset($sRow["AvgTestedDiff"]) && $sRow["AvgTestedDiff"] > 0 && $sRow["AvgTestedDiff"] != null) ? round($sRow["AvgTestedDiff"], 2) : 'null';
    $result['sampleReceivedDiff'][$j] = (isset($sRow["AvgReceivedDiff"]) && $sRow["AvgReceivedDiff"] > 0 && $sRow["AvgReceivedDiff"] != null) ? round($sRow["AvgReceivedDiff"], 2) : 'null';
    $result['sampleReceivedTested'][$j] = (isset($sRow["AvgReceivedTested"]) && $sRow["AvgReceivedTested"] > 0 && $sRow["AvgReceivedTested"] != null) ? round($sRow["AvgReceivedTested"], 2) : 'null';
    $result['sampleReceivedPrinted'][$j] = (isset($sRow["AvgReceivedPrinted"]) && $sRow["AvgReceivedPrinted"] > 0 && $sRow["AvgReceivedPrinted"] != null) ? round($sRow["AvgReceivedPrinted"], 2) : 'null';
    $result['date'][$j] = $sRow["monthDate"];
    $j++;
}

$testReasonQuery = "SELECT count(vl.sample_code) AS total, tr.test_reason_name 
                    from form_covid19 as vl 
                    INNER JOIN r_covid19_test_reasons as tr ON vl.reason_for_covid19_test = tr.test_reason_id 
                    JOIN facility_details as f ON vl.facility_id=f.facility_id 
                    LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id";

$sWhere = ' WHERE vl.reason_for_covid19_test IS NOT NULL ' . $whereCondition;
if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
    $sWhere .= ' AND b.batch_code = "' . $_POST['batchCode'] . '"';
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $sWhere .= ' AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
}
if (!empty($_POST['labName'])) {
    $sWhere .= ' AND vl.lab_id = ' . $_POST['labName'];
}
$testReasonQuery = $testReasonQuery . " " . $sWhere;
$testReasonQuery = $testReasonQuery . " GROUP BY tr.test_reason_name";
$testReasonResult = $db->rawQuery($testReasonQuery);
// echo "<pre>";print_r($testReasonResult);die;
?>
<div class="col-xs-12">
    <div class="box">
        <div class="box-body">
            <div id="covid19SampleStatusOverviewContainer" style="float:left;width:100%; margin: 0 auto;"></div>
        </div>
    </div>
    <div class="box">
        <div class="box-body">
            <div id="covid19TestReasonContainer" style="float:left;width:100%; margin: 0 auto;"></div>
        </div>
    </div>
    <div class="box">
        <div class="box-body">
            <div id="covid19SamplesOverview" style="float:right;width:100%;margin: 0 auto;"></div>
        </div>
    </div>
</div>
</div>
<div class="col-xs-12 labAverageTatDiv">
    <div class="box">
        <div class="box-body">
            <div id="covid19LabAverageTat" style="padding:15px 0px 5px 0px;float:left;width:100%;"></div>
        </div>
    </div>
</div>
<script>
    <?php
    if (isset($tResult) && count($tResult) > 0) {
    ?>
        $('#covid19SampleStatusOverviewContainer').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Covid-19 Samples Status Overview'
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: 'Covid-19 Samples :<b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    size: '100%',
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        useHTML: true,
                        format: '<div style="padding-bottom:10px;"><b>{point.name}</b>: {point.y}</div>',
                        style: {

                            //crop:false,
                            //overflow:'none',
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        },
                        distance: 10
                    },
                    showInLegend: true
                }
            },
            series: [{
                colorByPoint: false,
                point: {
                    events: {
                        click: function(e) {
                            //console.log(e.point.url);
                            window.open(e.point.url, '_blank');
                            e.preventDefault();
                        }
                    }
                },
                data: [
                    <?php
                    foreach ($tResult as $tRow) {
                    ?> {
                            name: '<?php echo ($tRow['status_name']); ?>',
                            y: <?php echo ($tRow['total']); ?>,
                            color: '<?php echo $sampleStatusColors[$tRow['status_id']]; ?>',
                            url: '/dashboard/vlTestResultStatus.php?id=<?php echo base64_encode($tRow['status_id']); ?>'
                        },
                    <?php
                    }
                    ?>
                ]
            }]
        });

    <?php

    }

    if (isset($vlSuppressionResult) && (isset($vlSuppressionResult['positiveResult']) || isset($vlSuppressionResult['negativeResult']) || isset($vlSuppressionResult['rejectedResult']))) {

    ?>
        Highcharts.setOptions({
            colors: ['#FF0000', '#50B432', '#ada99c']
        });
        $('#covid19SamplesOverview').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Covid-19 Results'
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: 'Samples :<b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    size: '100%',
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        useHTML: true,
                        format: '<div style="padding-bottom:10px;"><b>{point.name}</b>: {point.y}</div>',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        },
                        distance: 10
                    },
                    showInLegend: true
                }
            },
            series: [{
                colorByPoint: true,
                data: [{
                        name: 'Positive',
                        y: <?php echo (isset($vlSuppressionResult['positiveResult']) && $vlSuppressionResult['positiveResult'] > 0) > 0 ? $vlSuppressionResult['positiveResult'] : 0; ?>
                    },
                    {
                        name: 'Negative',
                        y: <?php echo (isset($vlSuppressionResult['negativeResult']) && $vlSuppressionResult['negativeResult'] > 0) > 0 ? $vlSuppressionResult['negativeResult'] : 0; ?>
                    },
                    {
                        name: 'Rejected',
                        y: <?php echo (isset($vlSuppressionResult['rejectedResult']) && $vlSuppressionResult['rejectedResult'] > 0) > 0 ? $vlSuppressionResult['rejectedResult'] : 0; ?>
                    },
                ]
            }]
        });
    <?php
    }
    if (isset($result) && count($result) > 0) {
    ?>
        $('#covid19LabAverageTat').highcharts({
            chart: {
                type: 'line'
            },
            title: {
                text: 'COVID-19 Laboratory Turnaround Time'
            },
            exporting: {
                chartOptions: {
                    subtitle: {
                        text: 'COVID-19 Laboratory Turnaround Time',
                    }
                }
            },
            credits: {
                enabled: false
            },
            xAxis: {
                //categories: ["21 Mar", "22 Mar", "23 Mar", "24 Mar", "25 Mar", "26 Mar", "27 Mar"]
                categories: [<?php
                                if (isset($result['date']) && count($result['date']) > 0) {
                                    foreach ($result['date'] as $date) {
                                        echo "'" . $date . "',";
                                    }
                                }
                                ?>]
            },
            yAxis: [{
                title: {
                    text: 'Average TAT in Days'
                },
                labels: {
                    formatter: function() {
                        return this.value;
                    }
                }
            }, { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'No. of Tests'
                },
                labels: {
                    format: '{value}'
                },
                opposite: true
            }],
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function(e) {
                                //doLabTATRedirect(e.point.category);
                            }
                        }
                    }
                },
                series: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },

            series: [{
                    type: 'column',
                    name: 'No. of Samples Tested',
                    data: [<?php echo implode(",", $result['totalSamples']); ?>],
                    color: '#7CB5ED',
                    yAxis: 1
                },
                <?php
                if (isset($result['avgResultPrinted'])) {
                ?> {
                        connectNulls: false,
                        showInLegend: true,
                        name: 'Result - Printed',
                        data: [<?php echo implode(",", $result['avgResultPrinted']); ?>],
                        color: '#0f3f6e',
                    },
                <?php
                }
                if (isset($result['sampleReceivedDiff'])) {
                ?> {
                        connectNulls: false,
                        showInLegend: true,
                        name: 'Collected - Received at Lab',
                        data: [<?php echo implode(",", $result['sampleReceivedDiff']); ?>],
                        color: '#edb47c',
                    },
                <?php
                }
                if (isset($result['sampleReceivedTested'])) {
                ?> {
                        connectNulls: false,
                        showInLegend: true,
                        name: 'Received - Tested',
                        data: [<?php echo implode(",", $result['sampleReceivedTested']); ?>],
                        color: '#0f3f6e',
                    },
                <?php
                }
                if (isset($result['sampleTestedDiff'])) {
                ?> {
                        connectNulls: false,
                        showInLegend: true,
                        name: 'Collected - Tested',
                        data: [<?php echo implode(",", $result['sampleTestedDiff']); ?>],
                        color: '#ed7c7d',
                    },
                <?php
                }
                if (isset($result['sampleReceivedPrinted'])) {
                ?> {
                        connectNulls: false,
                        showInLegend: true,
                        name: 'Collected - Printed',
                        data: [<?php echo implode(",", $result['sampleReceivedPrinted']); ?>],
                        color: '#000',
                    },
                <?php
                }
                ?>
            ],
        });
    <?php }
    if (isset($testReasonResult) && count($testReasonResult) > 0) { ?>
        $('#covid19TestReasonContainer').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Covid-19 Test Reasons'
            },
            credits: {
                enabled: false
            },
            tooltip: {
                pointFormat: 'Test Reasons :<b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    size: '100%',
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        useHTML: true,
                        format: '<div style="padding-bottom:10px;"><b>{point.name}</b>: {point.y}</div>',
                        style: {

                            //crop:false,
                            //overflow:'none',
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        },
                        distance: 10
                    },
                    showInLegend: true
                }
            },
            series: [{
                colorByPoint: false,
                point: {
                    /* events: {
                        click: function(e) {
                            //console.log(e.point.url);
                            window.open(e.point.url, '_blank');
                            e.preventDefault();
                        }
                    } */
                },
                data: [
                    <?php
                    foreach ($testReasonResult as $tRow) {
                    ?> {
                            name: '<?php echo ($tRow['test_reason_name']); ?>',
                            y: <?php echo ($tRow['total']); ?>,
                            color: '#<?php echo $general->random_color(); ?>',
                        },
                    <?php
                    }
                    ?>
                ]
            }]
        });
    <?php } ?>
</script>