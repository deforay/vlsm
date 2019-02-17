<?php
session_start();
ob_start();
include_once('../includes/MysqliDb.php');
include_once('../General.php');

$general=new General($db); // passing $db which is coming from MysqliDb.php

$configFormQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
$cDate = date('Y-m-d');
$lastSevenDay = date('Y-m-d', strtotime('-7 days'));

$u = $general->getSystemConfig('user_type');

if($u != 'remoteuser'){
    $whereCondition = "result_status!=9 AND ";
}else{
    $whereCondition = "";
    //get user facility map ids
    $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM vl_user_facility_map where user_id='".$_SESSION['userId']."'";
    $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
    if($userfacilityMapresult[0]['facility_id']!=null && $userfacilityMapresult[0]['facility_id']!=''){
        $whereCondition = "facility_id IN (".$userfacilityMapresult[0]['facility_id'].")  AND remote_sample='yes' AND";
    }
}


if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
   //print_r($s_c_date);die;
   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
     $lastSevenDay = $general->dateFormat(trim($s_c_date[0]));
   }
   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
     $cDate = $general->dateFormat(trim($s_c_date[1]));
   }
}
$sWhere = '';


$waitingTotal = 0;
$rejectedTotal = 0;
$receivedTotal = 0;
$dFormat = '';
$waitingDate = '';
$rejectedDate = '';
$i = 0;

//get waiting data
$waitingQuery="SELECT COUNT(vl_sample_id) as total FROM vl_request_form as vl JOIN facility_details as f ON f.facility_id=vl.facility_id WHERE $whereCondition vl.vlsm_country_id = '".$configFormResult[0]['value']."' " . " AND (vl.result is null or vl.result = '') AND (vl.is_sample_rejected like 'no' or vl.is_sample_rejected is null or vl.is_sample_rejected = '')";

$waitingResult[$i] = $db->rawQuery($waitingQuery);//waiting result
if($waitingResult[$i][0]['total']!= 0){
  $waitingTotal = $waitingTotal + $waitingResult[$i][0]['total'];
  $waitingResult[$i]['date'] = $dFormat;
  $waitingDate = $dFormat;
}else{
  unset($waitingResult[$i]);
}


// Samples Accession
$accessionQuery = 'SELECT DATE(vl.sample_collection_date) as `collection_date`, COUNT(vl_sample_id) as `count` FROM vl_request_form as vl JOIN facility_details as f ON f.facility_id=vl.facility_id where '.$whereCondition.' DATE(vl.sample_collection_date) <= "'.$cDate.'" AND DATE(vl.sample_collection_date) >= "'.$lastSevenDay.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'" group by `collection_date` order by `collection_date`';
$tRes = $db->rawQuery($accessionQuery);//overall result
$tResult = array();
foreach($tRes as $tRow){
    $receivedTotal += $tRow['count'];
    $tResult[] = array('total' => $tRow['count'], 'date' => $tRow['collection_date']);
}

//Samples Tested
$sampleTestedQuery = 'SELECT DATE(vl.sample_tested_datetime) as `test_date`, COUNT(vl_sample_id) as `count` FROM vl_request_form as vl JOIN facility_details as f ON f.facility_id=vl.facility_id where '.$whereCondition.' DATE(vl.sample_tested_datetime) <= "'.$cDate.'" AND DATE(vl.sample_tested_datetime) >= "'.$lastSevenDay.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'" group by `test_date` order by `test_date`';
$tRes = $db->rawQuery($sampleTestedQuery);//overall result
$acceptedResult = array();
foreach($tRes as $tRow){
    $acceptedTotal += $tRow['count'];
    $acceptedResult[] = array('total' => $tRow['count'], 'date' => $tRow['test_date']);
}

//Rejected Samples
$sampleRejectedQuery = 'SELECT DATE(vl.sample_collection_date) as `collection_date`, COUNT(vl_sample_id) as `count` FROM vl_request_form as vl JOIN facility_details as f ON f.facility_id=vl.facility_id where '.$whereCondition.' vl.is_sample_rejected="yes" AND DATE(vl.sample_collection_date) <= "'.$cDate.'" AND DATE(vl.sample_collection_date) >= "'.$lastSevenDay.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'" group by `collection_date` order by `collection_date`';
$tRes = $db->rawQuery($sampleRejectedQuery);//overall result
$rejectedResult = array();
foreach($tRes as $tRow){
    $rejectedTotal += $tRow['count'];
    $rejectedResult[] = array('total' => $tRow['count'], 'date' => $tRow['collection_date']);
}

?>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
    <div class="dashboard-stat2 bluebox" style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-green-sharp">
                    <span data-counter="counterup" data-value="<?php echo $receivedTotal; ?>"><?php echo $receivedTotal; ?></span>
                </h3>
                <small class="font-green-sharp">SAMPLES REGISTERED</small><br>
                <small class="font-green-sharp" style="font-size:0.75em;">in selected range</small>
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesReceivedChart" width="210" height="150" style="min-height:150px;"></div>
    </div>
</div>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
    <div class="dashboard-stat2 " style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-blue-sharp">
                    <span data-counter="counterup" data-value="<?php echo $acceptedTotal; ?>"><?php echo $acceptedTotal; ?></span>
                </h3>
                <small class="font-blue-sharp">SAMPLES TESTED</small><br>
                <small class="font-blue-sharp"  style="font-size:0.75em;">In Selected Range</small>
                <!--<small class="font-blue-sharp"><?php echo $acceptedDate;?></small>-->
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesTestedChart" width="210" height="150" style="min-height:150px;"></div>
    </div>
</div>

<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 ">
    <div class="dashboard-stat2 " style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-red-haze">
                    <span data-counter="counterup" data-value="<?php echo $rejectedTotal; ?>"><?php echo $rejectedTotal; ?></span>
                </h3>
                <small class="font-red-haze">SAMPLES REJECTED</small><br>
                <small class="font-red-haze" style="font-size:0.75em;">In Selected Range</small>
                <!--<small class="font-red-haze"><?php echo $rejectedDate;?></small>-->
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesRejectedChart" width="210" height="150" style="min-height:150px;"></div>
    </div>
</div>

<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 ">
    <div class="dashboard-stat2 " style="cursor:pointer;">
        <div class="display">
            <div class="number">
                <h3 class="font-purple-soft">
                    <span data-counter="counterup" data-value="<?php echo $waitingTotal; ?>"><?php echo $waitingTotal; ?></span>
                </h3>
                <small class="font-purple-soft">SAMPLES WITH NO RESULTS</small><br>
                <small class="font-purple-soft"  style="font-size:0.75em;">As of today</small>
                <!--<small class="font-purple-soft"><?php echo $waitingDate;?></small>-->
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesWaitingChart" width="210" height="150" style="min-height:150px;"></div>
    </div>
</div>

<script>
    <?php
    //if(isset($tResult) && count($tResult)>0){
        if($receivedTotal>0){
        ?>
      
    $('#samplesReceivedChart').highcharts({
        chart: {
            type: 'column',
            height: 150
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        credits: {
            enabled: false
         },
        xAxis: {
            categories: [
            <?php
            foreach($tResult as $tRow){
                echo '"'.ucwords($tRow['date']).'",';
            }
            ?>],
            crosshair: true,          
            scrollbar: {
                enabled: true
            },            
        },
        yAxis: {
            min: 0,
            title: {
                text: null
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0,
                cursor: 'pointer',
                //point: {
                //    events: {
                //        click: function () {
                //            window.location.href='/labs/samples-accession';
                //        }
                //    }
                //}                
            }
        },
        series: [{
            showInLegend: false,  
            name: 'Samples',
            data: [<?php
            foreach($tResult as $tRow){
                echo ucwords($tRow['total']).",";
            }
            ?>]

        }],
        colors : ['#2ab4c0'],
    });
    <?php } 
    //waiting result
    if($waitingTotal>0){ ?>
    $('#samplesWaitingChart').highcharts({
        chart: {
            type: 'column',
            height: 150
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        credits: {
            enabled: false
         },
        xAxis: {
            categories: [<?php
            foreach($waitingResult as $total){
                echo "'".ucwords($total['date'])."',";
            }
            ?>],
            crosshair: true,
            scrollbar: {
                enabled: true
            },              
        },
        yAxis: {
            min: 0,
            title: {
                text: null
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0,
                cursor: 'pointer',
            }
        },
        series: [{
            showInLegend: false,  
            name: 'Samples',
            data: [<?php
            foreach($waitingResult as $total){
                echo ucwords($total[0]['total']).",";
            }
            ?>]

        }],
        colors : ['#8877a9']
    });
    <?php }
    if($acceptedTotal>0){
    ?>
      
$('#samplesTestedChart').highcharts({
        chart: {
            type: 'column',
            height: 150
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        credits: {
            enabled: false
         },
        xAxis: {
            categories: [<?php
            foreach($acceptedResult as $tRow){
                echo "'".ucwords($tRow['date'])."',";
            }
            ?>],
            crosshair: true,
            scrollbar: {
                enabled: true
            },              
        },
        yAxis: {
            min: 0,
            title: {
                text: null
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0,
                cursor: 'pointer',
            }
        },
        series: [{
            showInLegend: false,  
            name: 'Samples',
            data: [<?php
            foreach($acceptedResult as $tRow){
                echo ucwords($tRow['total']).",";
            }
            ?>]

        }],
        colors : ['#f36a5a']
    });        
<?php }
if($rejectedTotal>0){
?>
    
    
$('#samplesRejectedChart').highcharts({
        chart: {
            type: 'column',
            height: 150
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        credits: {
            enabled: false
         },
        xAxis: {
            categories: [<?php
            foreach($rejectedResult as $tRow){
                echo "'".ucwords($tRow['date'])."',";
            }
            ?>],
            crosshair: true,
            scrollbar: {
                enabled: true
            },              
        },
        yAxis: {
            min: 0,
            title: {
                text: null
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0,
                cursor: 'pointer',
            }
        },
        series: [{
            showInLegend: false,  
            name: 'Samples',
            data: [<?php
            foreach($rejectedResult as $tRow){
                echo ucwords($tRow['total']).",";
            }
            ?>]

        }],
        colors : ['#5C9BD1']
    });   
    <?php } 
    //}
    ?>
</script>
