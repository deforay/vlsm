<?php
ob_start();
include('MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$configFormQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
//date
$start_date = '';
$end_date = '';
$sWhere ='';
if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
   //print_r($s_c_date);die;
   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
     $start_date = $general->dateFormat(trim($s_c_date[0]));
   }
   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
     $end_date = $general->dateFormat(trim($s_c_date[1]));
   }
   //get value by rejection reason id
   $vlQuery = "select vl.reason_for_sample_rejection,sr.rejection_reason_name,sr.rejection_type,sr.rejection_reason_code from vl_request_form as vl inner join r_sample_rejection_reasons as sr ON sr.rejection_reason_id=vl.reason_for_sample_rejection";
   $sWhere.= ' where DATE(vl.sample_collection_date) <= "'.$end_date.'" AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND vl.vlsm_country_id = "'.$configFormResult[0]['value'].'" AND reason_for_sample_rejection!="" AND reason_for_sample_rejection IS NOT NULL';
   $vlQuery = $vlQuery.$sWhere." group by reason_for_sample_rejection";
   $vlResult = $db->rawQuery($vlQuery);
   $rejectionType = array();
   foreach($vlResult as $rejectedResult){
	  $tQuery="select COUNT(vl_sample_id) as total,vl.sample_collection_date FROM vl_request_form as vl INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_type where vl.vlsm_country_id='".$configFormResult[0]['value']."' AND vl.reason_for_sample_rejection=".$rejectedResult['reason_for_sample_rejection'];
	  //filter
	  $sWhere = '';
	  if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	    $sWhere.= ' AND DATE(vl.sample_collection_date) <= "'.$end_date.' 23:59:00" AND DATE(vl.sample_collection_date) >= "'.$start_date.' 00:00:00"';
	  }
	  if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
	    $sWhere.= ' AND s.sample_id = "'.$_POST['sampleType'].'"';
	  }
	  if(isset($_POST['labName']) && trim($_POST['labName'])!= ''){
	    $sWhere.= ' AND vl.lab_id = "'.$_POST['labName'].'"';
	  }
	  if(isset($_POST['clinicName']) && is_array($_POST['clinicName']) && count($_POST['clinicName']) > 0){
	    $sWhere.= " AND vl.facility_id IN (".implode(',',$_POST['clinicName']).")";
	  }
	  $tQuery = $tQuery.' '.$sWhere;
	  $tResult[$rejectedResult['rejection_reason_code']] = $db->rawQuery($tQuery);
	  $tResult[$rejectedResult['rejection_reason_code']][0]['rejection_reason_name'] = $rejectedResult['rejection_reason_name']; 
	  $tableResult[$rejectedResult['rejection_reason_code']] = $db->rawQuery($tQuery);
	  if($tableResult[$rejectedResult['rejection_reason_code']][0]['total']==0){
		 unset($tableResult[$rejectedResult['rejection_reason_code']]);
	  }else{
		$tableResult[$rejectedResult['rejection_reason_code']][0]['rejection_type'] = $rejectedResult['rejection_type'];
		 $tableResult[$rejectedResult['rejection_reason_code']][0]['rejection_reason_name'] = $rejectedResult['rejection_reason_name']; 
	  }
	  $rejectionType[] = $rejectedResult['rejection_type'];
   }
   //get value by rejection type
   $rejType = array_unique($rejectionType);
   foreach($rejType as $type){
   $rjQuery="select COUNT(vl_sample_id) as total FROM vl_request_form as vl INNER JOIN 	r_sample_rejection_reasons as sr ON sr.rejection_reason_id=vl.reason_for_sample_rejection INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_type where vl.vlsm_country_id='".$configFormResult[0]['value']."' AND sr.rejection_type='".$type."'";
   $sWhere = '';
	  if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	    $sWhere.= ' AND DATE(vl.sample_collection_date) <= "'.$end_date.' 23:59:00" AND DATE(vl.sample_collection_date) >= "'.$start_date.' 00:00:00"';
	  }
	  if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
	    $sWhere.= ' AND s.sample_id = "'.$_POST['sampleType'].'"';
	  }
	  if(isset($_POST['labName']) && trim($_POST['labName'])!= ''){
	    $sWhere.= ' AND vl.lab_id = "'.$_POST['labName'].'"';
	  }
	  if(isset($_POST['clinicName']) && is_array($_POST['clinicName']) && count($_POST['clinicName']) > 0){
	    $sWhere.= " AND vl.facility_id IN (".implode(',',$_POST['clinicName']).")";
	  }
	  $rjQuery = $rjQuery.' '.$sWhere;
	  $rjResult[$type] = $db->rawQuery($rjQuery);
   }
}
if(count($tResult[$rejectedResult['rejection_reason_code']])>0){
?>
<div id="container" style="min-width: 410px; height: 400px; max-width: 600px; margin: 0 auto;"></div>
<!--<div id="rejectedType" style="min-width: 410px; height: 400px; max-width: 600px; margin: 0 auto;float:right;"></div>-->
<?php } ?>
<table id="vlRequestDataTable" class="table table-bordered table-striped">
   <thead>
      <tr>
         <th>Sample Collection Date</th>
         <th>Rejection Reason</th>
         <th>Reason Type</th>
         <th>No. Of Records</th>
      </tr>
   </thead>
   <tbody>
		 <?php
		 if(isset($tableResult) && count($tableResult)>0){
			foreach($tableResult as $key=>$rejectedData){
			   ?>
			   <tr>
				  <td><?php $dateExp = explode(" ",$rejectedData[0]['sample_collection_date']);
				  echo $general->humanDateFormat($dateExp[0]);?></td>
				  <td><?php echo ucwords($rejectedData[0]['rejection_reason_name']);?></td>
				  <td><?php echo strtoupper($rejectedData[0]['rejection_type']);?></td>
				  <td><?php echo $rejectedData[0]['total'];?></td>
			   </tr>
			   <?php
			}
		 }
		 ?>
   </tbody>
</table>
<script>
   $(function () {
	  $("#vlRequestDataTable").DataTable();
	});
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
                    text: 'Sample Rejection Reason'
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
            foreach($tResult as $key=>$total){
                ?>
                {name:'<?php echo ucwords($key);?>',y:<?php echo ucwords($total[0]['total']);?>,number:'<?php echo ucwords($total[0]['rejection_reason_name']);?>'},
                <?php
            }
            ?>
            ]
        }]
      });
    <?php }
	
    if(isset($rjResult) && count($rjResult)>0){ ?>
      $('#rejectedType').highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Sample Rejection Reason Type'
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
            foreach($rjResult as $key=>$total){
                ?>
                {name:'<?php echo ucwords($key);?>',y:<?php echo ucwords($total[0]['total']);?>},
                <?php
            }
            ?>
            ]
        }]
      });
    <?php } ?>
</script>
