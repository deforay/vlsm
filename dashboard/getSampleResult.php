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
$vlQuery = "select DISTINCT YEAR(sample_collection_date), MONTH(sample_collection_date), DAY(sample_collection_date)  from vl_request_form as vl ";
$sWhere.= ' where '.$whereCondition.' DATE(vl.sample_collection_date) <= "'.$cDate.'" AND DATE(vl.sample_collection_date) >= "'.$lastSevenDay.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'"';
$vlQuery = $vlQuery.$sWhere;


$vlResult = $db->rawQuery($vlQuery);

$waitingTotal = 0;
$rejectedTotal = 0;
$receivedTotal = 0;
$dFormat = '';
$waitingDate = '';
$rejectedDate = '';
$i = 0;

//get waiting data
$waitingQuery="select COUNT(vl_sample_id) as total FROM vl_request_form as vl where $whereCondition vl.vlsm_country_id = '".$configFormResult[0]['value']."' " . " AND (vl.result is null or vl.result = '')";

$waitingResult[$i] = $db->rawQuery($waitingQuery);//waiting result
if($waitingResult[$i][0]['total']!= 0){
  $waitingTotal = $waitingTotal + $waitingResult[$i][0]['total'];
  $waitingResult[$i]['date'] = $dFormat;
  $waitingDate = $dFormat;
}else{
  unset($waitingResult[$i]);
}

foreach($vlResult as $vlData){
   $tQuery="select COUNT(vl_sample_id) as total FROM vl_request_form as vl where $whereCondition vl.vlsm_country_id = '".$configFormResult[0]['value']."'";
   $date = $vlData['YEAR(sample_collection_date)']."-".$vlData['MONTH(sample_collection_date)']."-".$vlData['DAY(sample_collection_date)'];
   $dFormat = date("d M", strtotime($date));
   //filter
   $sWhere = '';
   
   $rejectedWhere = '';
   if(isset($cDate) && trim($cDate)!= ''){
      $sWhere.= ' AND DATE(vl.sample_collection_date) >= "'.$date.' 00:00:00" AND DATE(vl.sample_collection_date) <= "'.$date.' 23:59:59"';
   }

    
   //get rejected data
    $rejectedWhere.= " AND vl.is_sample_rejected='yes'";
    $rejectedQuery = $tQuery.' '.$sWhere.$rejectedWhere;
    $rejectedResult[$i] = $db->rawQuery($rejectedQuery);//rejected result
    if($rejectedResult[$i][0]['total']!= 0){
      $rejectedTotal = $rejectedTotal + $rejectedResult[$i][0]['total'];
      $rejectedResult[$i]['date'] = $dFormat;
      $rejectedDate = $dFormat;
    }else{
      unset($rejectedResult[$i]);
    }
   
    $tQuery = $tQuery.' '.$sWhere;
    $tResult[$i] = $db->rawQuery($tQuery);//overall result
    if($tResult[$i][0]['total']!= 0){
      $receivedTotal = $receivedTotal + $tResult[$i][0]['total'];
      $tResult[$i]['date'] = $dFormat;
    }else{
      unset($tResult[$i]);
    }
   $i++;
}

//for sample tested
$stWhere = '';
$stVlQuery = "select DISTINCT YEAR(sample_tested_datetime), MONTH(sample_tested_datetime), DAY(sample_tested_datetime) from vl_request_form as vl";
$stWhere.= ' where '.$whereCondition.' DATE(vl.sample_tested_datetime) <= "'.$cDate.'" AND DATE(vl.sample_tested_datetime) >= "'.$lastSevenDay.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'"';
$stVlQuery = $stVlQuery.$stWhere;
$stVlResult = $db->rawQuery($stVlQuery);

$j=0;
$acceptedTotal = 0;
$acceptedDate = '';
foreach($stVlResult as $vlData){
   $tQuery="select COUNT(vl_sample_id) as total FROM vl_request_form as vl where $whereCondition vl.vlsm_country_id = '".$configFormResult[0]['value']."'";
   $date = $vlData['YEAR(sample_tested_datetime)']."-".$vlData['MONTH(sample_tested_datetime)']."-".$vlData['DAY(sample_tested_datetime)'];
   $dFormat = date("d M", strtotime($date));

   //filter
   $sWhere = '';
   if(isset($cDate) && trim($cDate)!= ''){
      $sWhere.= ' AND DATE(vl.sample_tested_datetime) >= "'.$date.' 00:00:00" AND DATE(vl.sample_tested_datetime) <= "'.$date.' 23:59:59"';
   }
   $acceptedQuery = $tQuery.' '.$sWhere;

   //echo ($acceptedQuery); die;

   $acceptedResult[$j] = $db->rawQuery($acceptedQuery);
   if($acceptedResult[$j][0]['total']!= 0){
     $acceptedTotal = $acceptedTotal + $acceptedResult[$j][0]['total'];
     $acceptedResult[$j]['date'] = $dFormat;
     $acceptedDate = $dFormat;
   }else{
     unset($acceptedResult[$j]);
   }
  $j++;
}
?>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
    <div class="dashboard-stat2 bluebox" style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-green-sharp">
                    <span data-counter="counterup" data-value="<?php echo $receivedTotal; ?>"><?php echo $receivedTotal; ?></span>
                </h3>
                <small class="font-green-sharp">SAMPLES ACCESSION</small><br>
                <small class="font-green-sharp" style="font-size:0.75em;">in selected range</small>
                <!--<small class="font-green-sharp"><?php echo $dFormat;?></small>-->
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
                <small class="font-purple-soft">SAMPLES WAITING</small><br>
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
            foreach($tResult as $total){
                echo '"'.ucwords($total['date']).'",';
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
            foreach($tResult as $total){
                echo ucwords($total[0]['total']).",";
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
            foreach($acceptedResult as $total){
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
            foreach($acceptedResult as $total){
                echo ucwords($total[0]['total']).",";
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
            foreach($rejectedResult as $total){
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
            foreach($rejectedResult as $total){
                echo ucwords($total[0]['total']).",";
            }
            ?>]

        }],
        colors : ['#5C9BD1']
    });   
    <?php } 
    //}
    ?>
</script>
