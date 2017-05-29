<?php
ob_start();
include('../header.php');
//include('../includes/MysqliDb.php');
$id=base64_decode($_GET['id']);
if(!isset($id) || trim($id)== ''){
	header("location:batchcode.php");
}
$content = '';
$displayOrder = array();
$batchQuery="SELECT * from batch_details as b_d INNER JOIN import_config as i_c ON i_c.config_id=b_d.machine where batch_id=$id";
$batchInfo=$db->query($batchQuery);
if(!isset($batchInfo) || count($batchInfo) == 0){
	header("location:batchcode.php");
}
if(isset($batchInfo[0]['label_order']) && trim($batchInfo[0]['label_order'])!= ''){
    $jsonToArray = json_decode($batchInfo[0]['label_order'],true);
	for($j=0;$j<count($jsonToArray);$j++){
		$displayOrder[] = $jsonToArray[$j];
		$xplodJsonToArray = explode("_",$jsonToArray[$j]);
		if(count($xplodJsonToArray)> 1 && $xplodJsonToArray[0] == "s"){
		   $sampleQuery="SELECT sample_code from vl_request_form where vl_sample_id=$xplodJsonToArray[1]";
		   $sampleResult=$db->query($sampleQuery);
		   $label = $sampleResult[0]['sample_code'];
		}else{
		   $label = str_replace("_"," ",$jsonToArray[$j]);
                   $label = str_replace("in house","In-House",$label);
                   $label = ucwords(str_replace("no of "," ",$label));
		}
        $content.='<li class="ui-state-default" id="'.$jsonToArray[$j].'">'.$label.'</li>';
    }
}else{
    if(isset($batchInfo[0]['number_of_in_house_controls']) && trim($batchInfo[0]['number_of_in_house_controls'])!='' && $batchInfo[0]['number_of_in_house_controls']>0){
        for($h=0;$h<$batchInfo[0]['number_of_in_house_controls'];$h++){
           $displayOrder[] = "no_of_in_house_controls_".($h+1);
           $content.='<li class="ui-state-default" id="no_of_in_house_controls_'.($h+1).'">In-House Controls '.($h+1).'</li>';
        }
    }if(isset($batchInfo[0]['number_of_manufacturer_controls']) && trim($batchInfo[0]['number_of_manufacturer_controls'])!='' && $batchInfo[0]['number_of_manufacturer_controls']>0){
        for($m=0;$m<$batchInfo[0]['number_of_manufacturer_controls'];$m++){
           $displayOrder[] = "no_of_manufacturer_controls_".($m+1);	
           $content.='<li class="ui-state-default" id="no_of_manufacturer_controls_'.($m+1).'">Manufacturer Controls '.($m+1).'</li>';
        }
    }if(isset($batchInfo[0]['number_of_calibrators']) && trim($batchInfo[0]['number_of_calibrators'])!='' && $batchInfo[0]['number_of_calibrators']>0){
        for($c=0;$c<$batchInfo[0]['number_of_calibrators'];$c++){
           $displayOrder[] = "no_of_calibrators_".($c+1);	 	
           $content.='<li class="ui-state-default" id="no_of_calibrators_'.($c+1).'">Calibrators '.($c+1).'</li>';
        }
    }
    $samplesQuery="SELECT vl_sample_id,sample_code from vl_request_form where sample_batch_id=$id";
    $samplesInfo=$db->query($samplesQuery);
    foreach($samplesInfo as $sample){
        $displayOrder[] = "s_".$sample['vl_sample_id'];
        $content.='<li class="ui-state-default" id="s_'.$sample['vl_sample_id'].'">'.$sample['sample_code'].'</li>';
    }
}
?>
<style>
    #sortableRow {
		list-style-type: none;
		margin:0px 0px 30px 0px;
		padding: 0;
		width: 100%;
		text-align:center;
	}
    #sortableRow li{
        color:#333 !important;
        font-size:16px;
		border-radius: 10px;
        margin-bottom: 4px;
    }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Edit Batch Controls Position</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Batch</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
			<h4><strong>Batch Code : <?php echo $batchInfo[0]['batch_code']; ?></strong></h4>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method='post'  name='editBatchControlsPosition' id='editBatchControlsPosition' autocomplete="off" action="editBatchControlsPositionHelper.php">
              <div class="box-body">
                <div class="row" id="displayOrderDetails">
                    <div class="col-md-8">
                        <ul id="sortableRow">
                            <?php
                            echo $content;
                            ?>
                        </ul>
                     </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
			    <input type="hidden" name="sortOrders" id="sortOrders" value="<?php echo implode(",",$displayOrder); ?>"/>
			    <input type="hidden" name="batchId" id="batchId" value="<?php echo $id; ?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="batchcode.php" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
<script>
	sortedTitle = [];
	$(document).ready(function() {
		function cleanArray(actual) {
				var newArray = new Array();
				for (var i = 0; i < actual.length; i++) {
						if (actual[i]) {
								newArray.push(actual[i]);
						}
				}
				return newArray;
		}
					
		$("#sortableRow").sortable({
            opacity: 0.6,
			cursor: 'move',
			update: function() {
				sortedTitle = cleanArray($(this).sortable("toArray"));
				$("#sortOrders").val("");
				$("#sortOrders").val(sortedTitle);
            }	
        } ).disableSelection();
	});
	
	function validateNow(){
		flag = deforayValidator.init({
			formId: 'editBatchControlsPosition'
		});
		
		if(flag){
		  $.blockUI();
		  document.getElementById('editBatchControlsPosition').submit();
		}
   }
</script>
<?php
 include('../footer.php');
 ?>