<?php
ob_start();
$title = "VLSM | Edit Package";
include('../header.php');
$id=base64_decode($_GET['id']);
$pQuery = "Select * from package_details where package_id=".$id;
$pResult = $db->rawQuery($pQuery);
$query="SELECT vl.vl_sample_id FROM vl_request_form as vl INNER JOIN r_package_details_map as rp ON rp.sample_id=vl.vl_sample_id WHERE rp.package_id=".$id;
$packageMapResult = $db->rawQuery($query);
$packResult = array_map('current',$packageMapResult);

$rpQuery="SELECT GROUP_CONCAT(DISTINCT rp.sample_id SEPARATOR ',') as sampleId FROM r_package_details_map as rp where rp.package_id!=".$id;
$rpResult = $db->rawQuery($rpQuery);

$query="SELECT vl.sample_code,vl.vl_sample_id FROM vl_request_form as vl where vl.vlsm_country_id = ".$global['vl_form'];
if(isset($rpResult[0]['sampleId'])){
    $query = $query." AND vl_sample_id NOT IN(".$rpResult[0]['sampleId'].")";
}
$query = $query." ORDER BY vl.request_created_datetime ASC";
$result = $db->rawQuery($query);
?>
<link href="../assets/css/multi-select.css" rel="stylesheet" />
<style>
  .select2-selection__choice{
    color:#000000 !important;
  }
  #ms-sampleCode{width: 110%;}
  .showFemaleSection{display: none;}
  #sortableRow { list-style-type: none; margin: 30px 0px 30px 0px; padding: 0; width: 100%;text-align:center; }
  #sortableRow li{
    color:#333 !important;
    font-size:16px;
  }
  #alertText{
    text-shadow: 1px 1px #eee;
  }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Edit Package</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Package</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method="post" name="editPackageForm" id="editPackageForm" autocomplete="off" action="editPackageCodeHelper.php">
              <div class="box-body">
	        <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="packageCode" class="col-lg-4 control-label">Package Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7" style="margin-left:3%;">
                        <input type="text" class="form-control isRequired" id="packageCode" name="packageCode" placeholder="Package Code" title="Please enter Package Code" readonly value="<?php echo strtoupper($pResult[0]['package_code']);?>" />
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
                       <?php foreach($result as $sample){ ?>
                            <option value="<?php echo $sample['vl_sample_id'];?>" <?php echo (in_array($sample['vl_sample_id'],$packResult)) ? 'selected="selected"':''; ?>><?php  echo $sample['sample_code'];?></option>
                        <?php } ?>
                       </select>
				   </div>
			       </div>
			    </div>
		    </div>
		</div>
		<div class="row" id="alertText" style="font-size:18px;"></div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
              <input type="hidden" name="packageId" value="<?php echo $pResult[0]['package_id'];?>"/>
              <a id="packageSubmit" class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="packageList.php" class="btn btn-default"> Cancel</a>
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
  <script src="../assets/js/jquery.multi-select.js"></script>
  <script src="../assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript">
   noOfSamples = 2;
  $(document).ready(function() {
    //getSampleCodeDetails();
  } );
	
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editPackageForm'
    });
    
    if(flag){
      $.blockUI();
      document.getElementById('editPackageForm').submit();
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
            //button disabled/enabled
	     if(this.qs2.cache().matchedResultsCount == noOfSamples){
		alert("You have selected Maximum no. of sample "+this.qs2.cache().matchedResultsCount);
		$("#packageSubmit").attr("disabled",false);
		$("#packageSubmit").css("pointer-events","auto");
	     }else if(this.qs2.cache().matchedResultsCount <= noOfSamples){
	       $("#packageSubmit").attr("disabled",false);
	       $("#packageSubmit").css("pointer-events","auto");
	     }else if(this.qs2.cache().matchedResultsCount > noOfSamples){
	       alert("You have already selected Maximum no. of sample "+noOfSamples);
	       $("#packageSubmit").attr("disabled",true);
	       $("#packageSubmit").css("pointer-events","none");
	     }
	     this.qs1.cache();
	     this.qs2.cache();
       },
       afterDeselect: function(){
         //button disabled/enabled
	  if(this.qs2.cache().matchedResultsCount == 0){
            $("#packageSubmit").attr("disabled",true);
	    $("#packageSubmit").css("pointer-events","none");
          }else if(this.qs2.cache().matchedResultsCount == noOfSamples){
	     alert("You have selected Maximum no. of sample "+this.qs2.cache().matchedResultsCount);
	     $("#packageSubmit").attr("disabled",false);
	     $("#packageSubmit").css("pointer-events","auto");
	  }else if(this.qs2.cache().matchedResultsCount <= noOfSamples){
	    $("#packageSubmit").attr("disabled",false);
	    $("#packageSubmit").css("pointer-events","auto");
	  }else if(this.qs2.cache().matchedResultsCount > noOfSamples){
	    $("#packageSubmit").attr("disabled",true);
	    $("#packageSubmit").css("pointer-events","none");
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
       $("#packageSubmit").attr("disabled",true);
       $("#packageSubmit").css("pointer-events","none");
       return false;
     });
   });
   
   function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback){
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
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
      $.post("getPackageSampleCodeDetails.php",
      function(data){
	  if(data != ""){
	    $("#sampleDetails").html(data);
	    $("#packageSubmit").attr("disabled",true);
	    $("#packageSubmit").css("pointer-events","none");
	  }
      });
      $.unblockUI();
    }
  </script>
 <?php
 include('../footer.php');
 ?>