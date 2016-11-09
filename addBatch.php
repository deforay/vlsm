<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
//Get active machines
$importConfigQuery="SELECT * FROM import_config WHERE status ='active'";
$importConfigResult = $db->rawQuery($importConfigQuery);
$query="SELECT vl.sample_code,vl.vl_sample_id,vl.facility_id,f.facility_name,f.facility_code FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where batch_id is NULL OR batch_id='' ORDER BY f.facility_name ASC";
$result = $db->rawQuery($query);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$sQuery="SELECT * FROM r_sample_type where status='active'";
$sResult = $db->rawQuery($sQuery);

$start_date = date('Y-m-d');
$end_date = date('Y-m-d');
$batchQuery='select MAX(batch_code_key) FROM batch_details as bd where DATE(bd.created_on) >= "'.$start_date.'" AND DATE(bd.created_on) <= "'.$end_date.'"';
$batchResult=$db->query($batchQuery);

if($batchResult[0]['MAX(batch_code_key)']!='' && $batchResult[0]['MAX(batch_code_key)']!=NULL){
 $maxId = $batchResult[0]['MAX(batch_code_key)']+1;
 $lngth = strlen($maxId);
 if($lngth==1){
    $maxId = "00".$maxId;
 }else if($lngth==2){
    $maxId = "0".$maxId; 
 }else if($lngth==3){
    $maxId = $maxId; 
 }
}else{
 $maxId = '001';
}
//Set last machine label order
$machinesLabelOrder = array();
foreach($importConfigResult as $machine) {
	$lastOrderQuery = "SELECT label_order from batch_details WHERE machine ='". $machine['config_id']."' ORDER BY created_on DESC";
	$lastOrderInfo=$db->query($lastOrderQuery);
	if(isset($lastOrderInfo[0]['label_order']) && trim($lastOrderInfo[0]['label_order'])!=''){
			$machinesLabelOrder[$machine['config_id']] = implode(",",json_decode($lastOrderInfo[0]['label_order'],true));
	}else{
		$machinesLabelOrder[$machine['config_id']] = '';
	}
}
//print_r($machinesLabelOrder);
?>
<link href="assets/css/multi-select.css" rel="stylesheet" />
<style>
  .select2-selection__choice{
	  color:#000000 !important;
  }
  #ms-sampleCode{width: 110%;}
  .showPregnant{display: none;}
		#sortableRow { list-style-type: none; margin: 30px 0px 30px 0px; padding: 0; width: 100%;text-align:center; }
		#sortableRow li{
			  color:#333 !important;
					font-size:16px;
		}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Create Batch</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Batch</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
				<table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width: 80%;">
					<tr>
						<td>&nbsp;<b>Sample Collection Date&nbsp;:</b></td>
						<td>
								<input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:275px;background:#fff;"/>
							</td>
							<td>&nbsp;<b>Sample Type&nbsp;:</b></td>
							<td>
								<select class="form-control" id="sampleType" name="sampleType" title="Please select sample type">
						<option value=""> -- Select -- </option>
							<?php
							foreach($sResult as $type){
							 ?>
							 <option value="<?php echo $type['sample_id'];?>"><?php echo ucwords($type['sample_name']);?></option>
							 <?php
							}
							?>
						</select>
							</td>
					</tr>
					<tr>
						 <td>&nbsp;<b>Facility Name & Code&nbsp;:</b></td>
							<td>
								<select style="width: 275px;" class="form-control" id="facilityName" name="facilityName" title="Please select facility name"  multiple="multiple">
							<!--<option value="">-- Select --</option>-->
								<?php
								foreach($fResult as $name){
								 ?>
								 <option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']."-".$name['facility_code']);?></option>
								 <?php
								}
								?>
								</select>
							</td>
							<td><b>Gender&nbsp;:</b></td>
							<td>
								<select name="gender" id="gender" class="form-control" title="Please choose gender" onchange="enablePregnant(this);">
									<option value=""> -- Select -- </option>
									<option value="male">Male</option>
									<option value="female">Female</option>
									<option value="not_recorded">Not Recorded</option>
								</select>
							</td>
					</tr>
					<tr>
						<td class="showPregnant"><b>Pregnant&nbsp;:</b></td>
						<td class="showPregnant">
							<input type="radio" name="pregnant" title="Please choose type" class="pregnant" id="prgYes" value="yes" disabled="disabled"/>&nbsp;&nbsp;Yes
							<input type="radio" name="pregnant" title="Please choose type" class="pregnant" id="prgNo" value="no" disabled="disabled"/>&nbsp;&nbsp;No
						</td>
						<td class=""><b>Urgency&nbsp;:</b></td>
						<td class="">
							<input type="radio" name="urgency" title="Please choose urgency type" class="urgent" id="urgentYes" value="normal"/>&nbsp;&nbsp;Normal
							<input type="radio" name="urgency" title="Please choose urgency type" class="urgent" id="urgentYes" value="urgent"/>&nbsp;&nbsp;Urgent
						</td>
					</tr>
					<tr>
						<td>&nbsp;<input type="button" onclick="getSampleCodeDetails();" value="Search" class="btn btn-success btn-sm">
							&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
							</td>
					</tr>
	    </table>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method='post'  name='addBatchForm' id='addBatchForm' autocomplete="off" action="addBatchCodeHelper.php">
              <div class="box-body">
								<div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="batchCode" class="col-lg-4 control-label">Batch Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7" style="margin-left:3%;">
                        <input type="text" class="form-control isRequired" id="batchCode" name="batchCode" placeholder="Batch Code" title="Please enter batch code" value="<?php echo date('Ymd').$maxId;?>" onblur="checkNameValidation('batch_details','batch_code',this,null,'This batch code already exists.Try another batch code',null)" />
			                  <input type="hidden" name="batchCodeKey" id="batchCodeKey" value="<?php echo $maxId;?>"/>
                        </div>
                    </div>
                  </div>
                </div>
								<div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="machine" class="col-lg-4 control-label">Choose Machine <span class="mandatory">*</span></label>
                        <div class="col-lg-7" style="margin-left:3%;">
																									<select name="machine" id="machine" class="form-control isRequired" title="Please choose machine">
																										<option value=""> -- Select -- </option>
																										<?php
																										foreach($importConfigResult as $machine) {
																											$labelOrder = $machinesLabelOrder[$machine['config_id']];
																										?>
																													<option value="<?php echo $machine['config_id']; ?>" data-no-of-samples="<?php echo $machine['max_no_of_samples_in_a_batch']; ?>"><?php echo ucwords($machine['machine_name']); ?></option>
																										<?php } ?>
																									</select>
                        </div>
                    </div>
                  </div>
                </div>
							  <div class="row" id="sampleDetails">
									<div class="col-md-8">
											<div class="form-group">
													<div class="col-md-12">
														<div class="col-md-12">
															 <div style="width:60%;margin:0 auto;clear:both;">
																<a href='#' id='select-all-samplecode' style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
																</div><br/><br/>
															<select id='sampleCode' name="sampleCode[]" multiple='multiple' class="search">
															<?php
															foreach($result as $sample){
																?>
																<option value="<?php echo $sample['vl_sample_id'];?>"><?php  echo $sample['sample_code']." - ".ucwords($sample['facility_name']);?></option>
																<?php
															}
															?>
														</select>
														</div>
												</div>
											</div>
										</div>
							  </div>
               
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
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
  
  <script src="assets/js/jquery.multi-select.js"></script>
  <script src="assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript" src="assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript">
  var startDate = "";
  var endDate = "";
	 noOfSamples = 0;
		sortedTitle = [];
  $(document).ready(function() {
	    $("#facilityName").select2({placeholder:"Select Facilities"});
     $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
	          separator: ' to ',
            startDate: moment().subtract('days', 29),
            endDate: moment(),
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
            }
        },
        function(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
      });
     $('#sampleCollectionDate').val("");
  } );
	
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addBatchForm'
    });
    
    if(flag){
      $.blockUI();
      document.getElementById('addBatchForm').submit();
    }
  }
	
   //$("#auditRndNo").multiselect({height: 100,minWidth: 150});
   $(document).ready(function() {
      $('.search').multiSelect({
       selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
       selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
       afterInit: function(ms){
	 var that = this,
	     $selectableSearch = that.$selectableUl.prev(),
	     $selectionSearch = that.$selectionUl.prev(),
	     selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
	     selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';
     
	 that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
	 .on('keydown', function(e){
	   if (e.which === 40){
	     that.$selectableUl.focus();
	     return false;
	   }
	 });
     
	 that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
	 .on('keydown', function(e){
	   if (e.which == 40){
	     that.$selectionUl.focus();
	     return false;
	   }
	 });
       },
       afterSelect: function(){
				//console.log(noOfSamples);
				if(this.qs2.cache().matchedResultsCount == noOfSamples){
					alert("You have selected Maximum no. of sample "+this.qs2.cache().matchedResultsCount);
					$(".ms-selectable").css("pointer-events","none");
				}
				 this.qs1.cache();
				 this.qs2.cache();
       },
       afterDeselect: function(){
				if(this.qs2.cache().matchedResultsCount < noOfSamples){
					$(".ms-selectable").css("pointer-events","auto");
				}
				 this.qs1.cache();
				 this.qs2.cache();
       }
     });
      
      $('#select-all-samplecode').click(function(){
       $('#sampleCode').multiSelect('select_all');
       return false;
     });
     $('#deselect-all-samplecode').click(function(){
       $('#sampleCode').multiSelect('deselect_all');
       return false;
     });
     
      if(noOfSamples == 0){
	      $(".ms-selectable,#select-all-samplecode").css("pointer-events","none");
      } else if(<?php echo count($result); ?> >= noOfSamples) {
        $("#select-all-samplecode").css("pointer-events","none");
      }
   });
   function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback)
    {
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                duplicateName=false;
                document.getElementById(obj.id).value="";
            }
        });
    }
    
    function getSampleCodeDetails(){
      $.blockUI();
      var fName = $("#facilityName").val();
      var sName = $("#sampleType").val();
      var sCode = $("#sampleCode").val();
      var gender= $("#gender").val();
      var prg =   $("input:radio[name=pregnant]");
      if(prg[0].checked==false && prg[1].checked==false){
	       pregnant = '';
      }else{
	       pregnant = $('input[name=pregnant]:checked').val();
      }
      var urgent = $('input[name=urgency]:checked').val();
      $.post("getSampleCodeDetails.php", { fName : fName,sCode : sCode,sName:sName,sampleCollectionDate:$("#sampleCollectionDate").val(),gender:gender,pregnant:pregnant,urgent:urgent},
      function(data){
	  if(data != ""){
	    $("#sampleDetails").html(data);
	  }
      });
      $.unblockUI();
    }
    function enablePregnant(obj){
      if(obj.value=="female"){
				$(".showPregnant").show();
				$(".pregnant").prop("disabled",false);
				}else{
				$(".showPregnant").hide();
				$(".pregnant").prop("checked",false);
				$(".pregnant").attr("disabled","");
      }
    }
		
		$("#machine").change(function(){
			var self = this.value;
			if(self!= ''){
       var selected = $(this).find('option:selected');
       noOfSamples = selected.data('no-of-samples');
						if(noOfSamples == 0){
							$(".ms-selectable,#select-all-samplecode").css("pointer-events","none");
							$(".ms-selectable").css("pointer-events","none");
							$('#sampleCode').multiSelect('deselect_all');
						}else if(<?php echo count($result); ?> >= noOfSamples) {
							if($("#sampleCode :selected").length < noOfSamples){
									$("#select-all-samplecode").css("pointer-events","none");
									$(".ms-selectable").css("pointer-events","auto");
							}else{
									$("#select-all-samplecode").css("pointer-events","none");
									$(".ms-selectable").css("pointer-events","none");
							}
						}else{
							$(".ms-selectable,#select-all-samplecode").css("pointer-events","auto");
							$("#select-all-samplecode").css("pointer-events","auto");
							$(".ms-selectable").css("pointer-events","auto");
						}
			  }else{
						 noOfSamples = 0;
							$(".ms-selectable,#select-all-samplecode").css("pointer-events","none");
							$(".ms-selectable").css("pointer-events","none");
							$('#sampleCode').multiSelect('deselect_all');
					}
    });
  </script>
  
 <?php
 include('footer.php');
 ?>
