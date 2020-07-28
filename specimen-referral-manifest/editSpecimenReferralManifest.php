<?php
ob_start();
$title = "Edit Specimen Referral Manifest";
#require_once('../startup.php'); 
include_once(APPLICATION_PATH.'/header.php');
$id=base64_decode($_GET['id']);
$pQuery = "Select * from package_details where package_id=".$id;
$pResult = $db->rawQuery($pQuery);
if($pResult[0]['package_status']=='dispatch'){
  header("location:packageList.php");
}
if($sarr['user_type']=='remoteuser'){
  $sCode = 'remote_sample_code';
  $vlfmQuery="SELECT GROUP_CONCAT(DISTINCT vlfm.facility_id SEPARATOR ',') as facilityId FROM vl_user_facility_map as vlfm where vlfm.user_id='".$_SESSION['userId']."'";
  $vlfmResult = $db->rawQuery($vlfmQuery);
}else if($sarr['user_type']=='vluser' || $sarr['user_type']=='standalone'){
  $sCode = 'sample_code';
}

$module = isset($_GET['t']) ? base64_decode($_GET['t']) : 'vl';
if($module == 'vl'){
  $query="SELECT vl.sample_code,vl.remote_sample_code,vl.vl_sample_id,vl.sample_package_id FROM vl_request_form as vl where (vl.sample_code IS NOT NULL OR vl.remote_sample_code IS NOT NULL) AND (vl.sample_package_id is null OR vl.sample_package_id='' OR vl.sample_package_id=".$id.") AND vl.vlsm_country_id = ".$global['vl_form'];
} else if($module == 'eid'){
  $query="SELECT vl.sample_code,vl.remote_sample_code,vl.eid_id,vl.sample_package_id FROM eid_form as vl where (vl.sample_code IS NOT NULL OR vl.remote_sample_code IS NOT NULL) AND (vl.sample_package_id is null OR vl.sample_package_id='' OR vl.sample_package_id=".$id.") AND vl.vlsm_country_id = ".$global['vl_form'];
}

if(isset($vlfmResult[0]['facilityId']))
{
  $query = $query." AND facility_id IN(".$vlfmResult[0]['facilityId'].")";
}
$query = $query." ORDER BY vl.request_created_datetime ASC";

$result = $db->rawQuery($query);
// if($sarr['user_type']=='remoteuser'){
//   $sCode = 'remote_sample_code';
// }else if($sarr['user_type']=='vluser'){
//   $sCode = 'sample_code';
// }


?>
<link href="/assets/css/multi-select.css" rel="stylesheet" />
<style>
  .select2-selection__choice{  color:#000000 !important; }
  #ms-sampleCode{width: 110%;}
  .showFemaleSection{display: none;}
  #sortableRow { list-style-type: none; margin: 30px 0px 30px 0px; padding: 0; width: 100%;text-align:center; }
  #sortableRow li{ color:#333 !important; font-size:16px; }
  #alertText{ text-shadow: 1px 1px #eee; }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> Edit Specimen Referral Manifest</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
	<li><a href="/specimen-referral-manifest/specimenReferralManifestList.php"> Manage Specimen Referral Manifest</a></li>
        <li class="active">Edit Specimen Referral Manifest</li>
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
            <form class="form-horizontal" method="post" name="editSpecimenReferralManifestForm" id="editSpecimenReferralManifestForm" autocomplete="off" action="editSpecimenReferralManifestCodeHelper.php">
              <div class="box-body">
	              <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="packageCode" class="col-lg-4 control-label">Manifest Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7" style="margin-left:3%;">
                        <input type="text" class="form-control isRequired" id="packageCode" name="packageCode" placeholder="Manifest Code" title="Please enter manifest code" readonly value="<?php echo strtoupper($pResult[0]['package_code']);?>" />
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="packageCode" class="col-lg-4 control-label">Manifest Status <span class="mandatory">*</span></label>
                        <div class="col-lg-7" style="margin-left:3%;">
                          <select class = "form-control isRequired" name="packageStatus" id="packageStatus" title="Please select manifest status">
                            <option value="">-- Select --</option>
                            <option value="pending" <?php echo ($pResult[0]['package_status']=='pending')?"selected='selected'":''; ?>>Pending</option>
                            <option value="dispatch" <?php echo ($pResult[0]['package_status']=='dispatch')?"selected='selected'":''; ?>>Dispatch</option>
                            <option value="received" <?php echo ($pResult[0]['package_status']=='received')?"selected='selected'":''; ?>>Received</option>
                          </select>
                        </div>
                    </div>
                  </div>
                </div>
		            <div class="row" id="sampleDetails">
		              <div class="col-md-8">
			              <div class="form-group">
				              <div class="col-md-12">
					              <div style="width:60%;margin:0 auto;clear:both;">
						              <a href='#' id='select-all-samplecode' style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
					              </div><br/><br/>
                        <select id='sampleCode' name="sampleCode[]" multiple='multiple' class="search">
                          <?php foreach($result as $sample){ 
                            if($sample[$sCode]!=''){
                              if($module == 'vl'){
                                $sampleId  = $sample['vl_sample_id'];
                              } else if($module == 'eid'){
                                $sampleId  = $sample['eid_id'];
                              }                              
                              ?>
                              
                                <option value="<?php echo $sampleId; ?>" <?php echo ($sample['sample_package_id']==$id) ? 'selected="selected"':''; ?>><?php  echo $sample[$sCode];?></option>
                          <?php } } ?>
                        </select>
				              </div>
			              </div>
		              </div>
		            </div>
		            <div class="row" id="alertText" style="font-size:18px;"></div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
              <input type="hidden" name="packageId" value="<?php echo $pResult[0]['package_id'];?>"/>
              <input type="hidden" class="form-control isRequired" id="module" name="module" placeholder="" title="" readonly value="<?php echo $module; ?>"/>
                <a id="packageSubmit" class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="specimenReferralManifestList.php" class="btn btn-default"> Cancel</a>
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
  <script src="/assets/js/jquery.multi-select.js"></script>
  <script src="/assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript">
   noOfSamples = 100;
  $(document).ready(function() {
    //getSampleCodeDetails();
  } );
	
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editSpecimenReferralManifestForm'
    });
    
    if(flag){
      $.blockUI();
      document.getElementById('editSpecimenReferralManifestForm').submit();
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
      removeDots = removeDots.replace(/\s{2,}/g,' ');
      $.post("/includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
      function(data){
          if(data==='1'){
              alert(alrt);
              duplicateName=false;
              document.getElementById(obj.id).value="";
          }
      });
    }

    function getSampleCodeDetails() {
    $.blockUI();

    $.post("/specimen-referral-manifest/getSpecimenReferralManifestSampleCodeDetails.php", {
        module: $("#module").val()
      },
      function(data) {
        if (data != "") {
          $("#sampleDetails").html(data);
          $("#packageSubmit").attr("disabled", true);
          $("#packageSubmit").css("pointer-events", "none");
        }
      });
    $.unblockUI();
    }   
  </script>
 <?php
 include(APPLICATION_PATH.'/footer.php');
 ?>