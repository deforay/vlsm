<?php
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$cDate = date('Y-m-d');
$lastSevenDay = date('Y-m-d', strtotime('-7 days'));

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
$sWhere.= ' where DATE(vl.sample_collection_date) <= "'.$cDate.'" AND DATE(vl.sample_collection_date) >= "'.$lastSevenDay.'" ';
$vlQuery = $vlQuery.$sWhere;
$vlResult = $db->rawQuery($vlQuery);

$i = 0;
$waitingTotal = 0;
$acceptedTotal = 0;
$rejectedTotal = 0;
$receivedTotal = 0;
foreach($vlResult as $vlData){
   $tQuery="select COUNT(vl_sample_id) as total FROM vl_request_form as vl";
   $date = $vlData['YEAR(sample_collection_date)']."-".$vlData['MONTH(sample_collection_date)']."-".$vlData['DAY(sample_collection_date)'];
   $dFormat = date("d M", strtotime($date));

   //check filter
   $sWhere = '';
   $waitingWhere = '';
   $acceptedWhere = '';
   $rejectedWhere = '';
   if(isset($cDate) && trim($cDate)!= ''){
      $sWhere.= ' where DATE(vl.sample_collection_date) >= "'.$date.' 00:00:00" AND DATE(vl.sample_collection_date) <= "'.$date.' 23:59:59"';
   }
   //get waiting data
    $waitingWhere.= ' and vl.status=6';
    $waitingQuery = $tQuery.' '.$sWhere.$waitingWhere;
    $waitingResult[$i] = $db->rawQuery($waitingQuery);//waiting result
    if($waitingResult[$i][0]['total']!=0){
    $waitingTotal = $waitingTotal + $waitingResult[$i][0]['total'];
    $waitingResult[$i]['date'] = $dFormat;
    }else{
    unset($waitingResult[$i]);
    }
   //get accepted data
    $acceptedWhere.= ' and vl.status=7';
    $acceptedQuery = $tQuery.' '.$sWhere.$acceptedWhere;
    $acceptedResult[$i] = $db->rawQuery($acceptedQuery);//accepted result
    if($acceptedResult[$i][0]['total']!=0){
    $acceptedTotal = $acceptedTotal + $acceptedResult[$i][0]['total'];
    $acceptedResult[$i]['date'] = $dFormat;
    }else{
    unset($acceptedResult[$i]);
    }
   //get rejected data
    $rejectedWhere.= ' and vl.status=4';
    $rejectedQuery = $tQuery.' '.$sWhere.$rejectedWhere;
    $rejectedResult[$i] = $db->rawQuery($rejectedQuery);//rejected result
    if($rejectedResult[$i][0]['total']!=0){
    $rejectedTotal = $rejectedTotal + $rejectedResult[$i][0]['total'];
    $rejectedResult[$i]['date'] = $dFormat;
    }else{
    unset($rejectedResult[$i]);
    }
   
    $tQuery = $tQuery.' '.$sWhere;
    $tResult[$i] = $db->rawQuery($tQuery);//overall result
    if($tResult[$i][0]['total']!=0){
    $receivedTotal = $receivedTotal + $tResult[$i][0]['total'];
    $tResult[$i]['date'] = $dFormat;
    }else{
    unset($tResult[$i]);
    }
   $i++;
}
//print_r($waitingResult);die;
?>
<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 ">
    <div class="dashboard-stat2 bluebox" style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-green-sharp">
                    <span data-counter="counterup" data-value="<?php echo $receivedTotal; ?>"><?php echo $receivedTotal; ?></span>
                </h3>
                <small class="font-green-sharp">SAMPLES ACCESSION</small><br>
                <small class="font-green-sharp"><?php echo $dFormat;?></small>
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesReceivedChart" width="210" height="150"></div>
    </div>
</div>
<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="dashboard-stat2 " style="cursor:pointer;">
        <div class="display">
            <div class="number">
                <h3 class="font-purple-soft">
                    <span data-counter="counterup" data-value="<?php echo $waitingTotal; ?>"><?php echo $waitingTotal; ?></span>
                </h3>
                <small class="font-purple-soft">SAMPLES WAITING</small><br>
                <small class="font-purple-soft"><?php echo $dFormat;?></small>
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesWaitingChart" width="210" height="150"></div>
    </div>
</div>
<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="dashboard-stat2 " style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-blue-sharp">
                    <span data-counter="counterup" data-value="<?php echo $acceptedTotal; ?>"><?php echo $acceptedTotal; ?></span>
                </h3>
                <small class="font-blue-sharp">SAMPLES TESTED</small><br>
                <small class="font-blue-sharp"><?php echo $dFormat;?></small>
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesTestedChart" width="210" height="150"></div>
    </div>
</div>
<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="dashboard-stat2 " style="cursor:pointer;" >
        <div class="display">
            <div class="number">
                <h3 class="font-red-haze">
                    <span data-counter="counterup" data-value="<?php echo $rejectedTotal; ?>"><?php echo $rejectedTotal; ?></span>
                </h3>
                <small class="font-red-haze">SAMPLES REJECTED</small><br>
                <small class="font-red-haze"><?php echo $dFormat;?></small>
            </div>
            <div class="icon">
                <i class="icon-pie-chart"></i>
            </div>
        </div>
        <div id="samplesRejectedChart" width="210" height="150"></div>
    </div>
</div>
<script>
    <?php
    if(isset($tResult) && count($tResult)>0){
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
        xAxis: {
            categories: [
            <?php
            foreach($tResult as $total){
                echo "'".ucwords($total['date'])."',";
            }
            ?>],
            crosshair: true
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
            name: 'Samples Received',
            data: [<?php
            foreach($tResult as $total){
                echo ucwords($total[0]['total']).",";
            }
            ?>]

        }],
        colors : ['#2ab4c0']
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
        xAxis: {
            categories: [<?php
            foreach($waitingResult as $total){
                echo "'".ucwords($total['date'])."',";
            }
            ?>],
            crosshair: true
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
            name: 'Samples Waiting',
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
        xAxis: {
            categories: [<?php
            foreach($acceptedResult as $total){
                echo "'".ucwords($total['date'])."',";
            }
            ?>],
            crosshair: true
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
            name: 'Samples Tested',
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
        xAxis: {
            categories: [<?php
            foreach($rejectedResult as $total){
                echo "'".ucwords($total['date'])."',";
            }
            ?>],
            crosshair: true
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
            name: 'Samples Rejected',
            data: [<?php
            foreach($rejectedResult as $total){
                echo ucwords($total[0]['total']).",";
            }
            ?>]

        }],
        colors : ['#5C9BD1']
    });   
    <?php } }?>
</script>