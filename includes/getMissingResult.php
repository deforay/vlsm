<?php
session_start();
ob_start();
include_once('MysqliDb.php');
include_once('../General.php');


$general=new General($db); // passing $db which is coming from MysqliDb.php


$configFormQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);


$userType = $general->getSystemConfig('user_type');

if($userType != 'remoteuser'){
    $whereCondition = " AND vl.result_status!=9";
    $tsQuery = "select * from r_sample_status where status_id!=9";
}else{
    $whereCondition = '';
    $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM vl_user_facility_map where user_id='".$_SESSION['userId']."'";
    $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
    if($userfacilityMapresult[0]['facility_id']!=null && $userfacilityMapresult[0]['facility_id']!=''){
        $whereCondition = " AND vl.facility_id IN (".$userfacilityMapresult[0]['facility_id'].")   AND remote_sample='yes'";
    }
    $tsQuery = "select * from r_sample_status";
}



$tsResult = $db->rawQuery($tsQuery);
//date
$start_date = '';
$end_date = '';
if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
   //print_r($s_c_date);die;
   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
     $start_date = $general->dateFormat(trim($s_c_date[0]));
   }
   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
     $end_date = $general->dateFormat(trim($s_c_date[1]));
   }
}
$i = 0;




foreach($tsResult as $tsId){
   $tQuery="select COUNT(vl_sample_id) as total,status_id,status_name FROM vl_request_form as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where vl.vlsm_country_id='".$configFormResult[0]['value']."' AND vl.result_status='".$tsId['status_id']."' $whereCondition";
   
   //filter
   $sWhere = '';
   if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
      $sWhere.= ' AND b.batch_code = "'.$_POST['batchCode'].'"';
   }
   if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
      $sWhere.= ' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
   }
   if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
      $sWhere.= ' AND s.sample_id = "'.$_POST['sampleType'].'"';
   }
   if(isset($_POST['facilityName']) && is_array($_POST['facilityName']) && count($_POST['facilityName']) >0){
      $sWhere.= ' AND f.facility_id IN ('.implode(",",$_POST['facilityName']).')';
   }
   $tQuery = $tQuery.' '.$sWhere;
   $tResult[$i] = $db->rawQuery($tQuery);
   $i++;
}
//HVL and LVL Samples
$hvlQuery = '';$lvlQuery = '';
$vlSampleQuery="select COUNT(vl_sample_id) as total,status_id,status_name FROM vl_request_form as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where vl.vlsm_country_id='".$configFormResult[0]['value']."' $whereCondition";
$sWhere = '';
   if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
      $sWhere.= ' AND b.batch_code = "'.$_POST['batchCode'].'"';
   }
   if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
      $sWhere.= ' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
   }
   if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
      $sWhere.= ' AND s.sample_id = "'.$_POST['sampleType'].'"';
   }
   if(isset($_POST['facilityName']) && is_array($_POST['facilityName']) && count($_POST['facilityName']) >0){
      $sWhere.= ' AND f.facility_id IN ('.implode(",",$_POST['facilityName']).')';
   }
   $hvlQuery = $vlSampleQuery.' '.$sWhere ." AND vl.result > 1000 AND vl.result!=''";
   $lvlQuery = $vlSampleQuery.' '.$sWhere ." AND vl.result <= 1000 AND vl.result!='' AND vl.result_status != '4'";
   $vlSampleResult['hvl'] = $db->rawQuery($hvlQuery);
   $vlSampleResult['lvl'] = $db->rawQuery($lvlQuery);
   
   //get LAB TAT
   if($start_date=='' && $end_date=='')
   {
      $date = strtotime(date('Y-m-d').' -1 year');
      $start_date = date('Y-m-d', $date);
      $end_date = date('Y-m-d');
   }
   $tatSampleQuery="select DATE_FORMAT(DATE(sample_collection_date), '%b-%Y') as monthDate,CAST(ABS(AVG(TIMESTAMPDIFF(DAY,sample_tested_datetime,sample_collection_date))) AS DECIMAL (10,2)) as AvgDiff from vl_request_form as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where (vl.sample_collection_date is not null AND vl.sample_collection_date != '' AND DATE(vl.sample_collection_date) !='1970-01-01' AND DATE(vl.sample_collection_date) !='0000-00-00')
                        AND (vl.sample_tested_datetime is not null AND vl.sample_tested_datetime != '' AND DATE(vl.sample_tested_datetime) !='1970-01-01' AND DATE(vl.sample_tested_datetime) !='0000-00-00')
                        AND vl.result is not null
                        AND vl.result != ''
                        AND DATE(vl.sample_collection_date) >= '".$start_date."'
                        AND DATE(vl.sample_collection_date) <= '".$end_date."' AND vl.vlsm_country_id='".$configFormResult[0]['value']."' $whereCondition group by MONTH(vl.sample_collection_date) order by DATE(vl.sample_collection_date)";
   $sWhere = '';
   if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
      $sWhere.= ' AND b.batch_code = "'.$_POST['batchCode'].'"';
   }
   if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
      $sWhere.= ' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
   }
   if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
      $sWhere.= ' AND s.sample_id = "'.$_POST['sampleType'].'"';
   }
   if(isset($_POST['facilityName']) && is_array($_POST['facilityName']) && count($_POST['facilityName']) >0){
      $sWhere.= ' AND f.facility_id IN ('.implode(",",$_POST['facilityName']).')';
   }
   $tatSampleQuery = $tatSampleQuery." ".$sWhere;
   $tatResult = $db->rawQuery($tatSampleQuery);
   $j=0;
   foreach($tatResult as $sRow){
       if($sRow["monthDate"] == null) continue;
       $result['all'][$j] = (isset($sRow["AvgDiff"]) && $sRow["AvgDiff"] > 0 && $sRow["AvgDiff"] != NULL) ? round($sRow["AvgDiff"],2) : 0;
       $result['date'][$j] = $sRow["monthDate"];
       $j++;
   }
?>
<div class="col-xs-12">
          <div class="box">
<div class="box-body" >
    <div id="sampleStatusOverviewContainer" style="float:left;min-width: 480px; height: 480px; max-width: 600px; margin: 0 auto;"></div>
    <div id="samplesVlOverview" style="float:right;min-width: 410px; height: 480px; max-width: 600px; margin: 0 auto;"></div>
</div>
</div>
</div>
<div class="col-xs-12 labAverageTatDiv">
          <div class="box">
<div class="box-body" >
    <div id="labAverageTat" style="padding:15px 0px 5px 0px;float:left;width:100%;"></div>
</div>
</div>
</div>
<script>
    <?php
    if(isset($tResult) && count($tResult)>0){ ?>
      $('#sampleStatusOverviewContainer').highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Samples Status Overview'
                },
                credits: {
                  enabled: false
               },
                tooltip: {
                    pointFormat: 'Samples :<b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        size:'70%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            useHTML: true,
                            format: '<div style="padding-bottom:10px;"><b>{point.name}</b>: {point.y}</div>',
                            style: {
                              width: '100px',
                              //crop:false,
                              //overflow:'none',
                              color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            },
                            distance:10
                        },
                        showInLegend: true
                    }
                },
        series: [{
            colorByPoint: true,
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
            foreach($tResult as $total){
                ?>
                {name:'<?php echo ucwords($total[0]['status_name']);?>',y:<?php echo ucwords($total[0]['total']);?>,url:'../dashboard/vlTestResultStatus.php?id=<?php echo base64_encode($total[0]['status_id']); ?>'},
                <?php
            }
            ?>
            ]
        }]
      });
	  Highcharts.setOptions({
     colors: ['#FF0000', '#50B432']
    });
      $('#samplesVlOverview').highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Samples VL Overview'
                },
                credits: {
                  enabled: false
               },
                tooltip: {
                    pointFormat: 'Samples :<b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        size:'70%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            useHTML: true,
                            format: '<div style="padding-bottom:10px;"><b>{point.name}</b>: {point.y}</div>',
                            style: {
                                width: '100px',
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            },
                            distance:10
                        },
                        showInLegend: true
                    }
                },
        series: [{
            colorByPoint: true,
            data: [
			   {name:'High Viral Load',y:<?php echo ucwords($vlSampleResult['hvl'][0]['total']);?>},
			   {name:'Low Viral Load',y:<?php echo ucwords($vlSampleResult['lvl'][0]['total']);?>},
            ]
        }]
      });
    <?php } if(isset($result) && count($result)>0){ ?>
    $('#labAverageTat').highcharts({
        chart: {
            type: 'line'
        },
        title: {
            text: 'Laboratory Turnaround Time'
        },
        exporting:{
            chartOptions:{
                subtitle: {
                    text:'Laboratory Turnaround Time',
                }
            }
        },
        credits: {
            enabled: false
        },
        xAxis: {
            //categories: ["21 Mar", "22 Mar", "23 Mar", "24 Mar", "25 Mar", "26 Mar", "27 Mar"]
            categories: [<?php
       if(isset($result['date']) && count($result['date'])>0){
            foreach($result['date'] as $date){
                echo "'".$date."',";
            }
       }
            ?>]
        },
        yAxis: {
            title: {
                text: 'Average TAT in Days'
            },
            labels: { formatter: function() { return this.value; } },
            plotLines: [{
                    value: 16,
                    color: 'red',
                    width: 2
                }]
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                cursor: 'pointer',
                point: {
                    events: {
                        click: function (e) {
                          //doLabTATRedirect(e.point.category);
                        }
                    }
                }
            }
        },
        
        series: [
            <?php
            if(isset($result['all'])){
            ?>
            {
            showInLegend: false,
            name: 'Days',
            data: [<?php echo implode(",",$result['all']);?>],
            color : '#1B325F',
        },
        <?php } ?>
        ],
    });
    <?php } ?>
</script>
