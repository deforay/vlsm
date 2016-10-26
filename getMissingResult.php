<?php
ob_start();
include('./includes/MysqliDb.php');
$tsQuery = "select * from testing_status";
$tsResult = $db->rawQuery($tsQuery);
include('General.php');
$general=new Deforay_Commons_General();
//date
$start_date = '';
$end_date = '';
if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
   //print_r($s_c_date);die;
   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
     $start_date = $general->dateFormat($s_c_date[0]);
   }
   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
     $end_date = $general->dateFormat($s_c_date[1]);
   }
}
$i = 0;
foreach($tsResult as $tsId){
   $tQuery="select COUNT(vl_sample_id) as total,status_id,status_name FROM vl_request_form as vl INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id where vl.status='".$tsId['status_id']."'";
   //check filter
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
   if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!= ''){
      $sWhere.= ' AND f.facility_id = "'.$_POST['facilityName'].'"';
   }
   $tQuery = $tQuery.' '.$sWhere;
   $tResult[$i] = $db->rawQuery($tQuery);
   $i++;
}
?>
<h4>Samples Overview</h4>
<div id="container" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
<script>
    <?php
    if(isset($tResult) && count($tResult)>0){ ?>
      $('#container').highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: ''
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
                        window.open(e.point.url, '_blank');
                        e.preventDefault();
                     }
		}
	    },
            data: [
            <?php
            foreach($tResult as $total){
                ?>
                {name:'<?php echo ucwords($total[0]['status_name']);?>',y:<?php echo ucwords($total[0]['total']);?>,url:'vlTestResultStatus.php?id=<?php echo base64_encode($total[0]['status_id']); ?>'},
                <?php
            }
            ?>
            ]
        }]
      });
    <?php } ?>
</script>