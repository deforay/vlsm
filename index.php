<?php
include('header.php');
/* Total data set length */
  $vlFormTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form");
 // $aResultTotal = $countResult->fetch_row();
 //print_r($aResultTotal);
  $labCount = $vlFormTotal[0]['total'];
  
  $facilityTotal =  $db->rawQuery("select COUNT(facility_id) as total FROM facility_details");
  $facilityCount = $facilityTotal[0]['total'];
//  //Update Query
//  $uQ = "select * FROM vl_request_form";
//  $uResult = $db->rawQuery($uQ);
//  foreach($uResult as $rlt){
//    if(isset($rlt['sample_testing_date']) && trim($rlt['sample_testing_date'])!= '' && trim($rlt['sample_testing_date'])!= "0000-00-00 00:00:00"){
//      $db=$db->where('vl_sample_id',$rlt['vl_sample_id']);
//      //print_r($data);die;
//      $data = array(
//		    'lab_tested_date'=>$rlt['sample_testing_date'],
//		  );
//      $db->update("vl_request_form",$data);
//    }
//  }
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        <small>Control panel</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?php echo $facilityCount;?></h3>
              <p>Facilities</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="facilities.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?php echo $labCount; ?></h3>

              <p>Lab Requests</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="vlRequest.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body" id="pieChartDiv">
            </div>
          </div>
        </div>
      </div>
      
      <!-- /.row -->
      <!-- Main row -->
      <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
  </div>
  <script src="assets/js/highchart.js"></script>
  <script>
    $(function () {
    $.post("getMissingResult.php",{sampleCollectionDate:'',batchCode:'',facilityName:'',sampleType:''},
      function(data){
	  if(data!=''){
	    $("#pieChartDiv").html(data);
	  }
      });
    });
  </script>
 <?php
 include('footer.php');
 ?>
