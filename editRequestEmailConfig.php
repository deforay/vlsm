<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
$otherConfigQuery ="SELECT * from other_config";
$otherConfigResult=$db->query($otherConfigQuery);
$requestEmailConfigQuery ="SELECT * from other_config WHERE name ='request_email_field'";
$requestEmailConfigResult=$db->query($requestEmailConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($otherConfigResult); $i++) {
    $arr[$otherConfigResult[$i]['name']] = $otherConfigResult[$i]['value'];
}
$requestArr = array();
//Set selected field
if(isset($requestEmailConfigResult) && trim($requestEmailConfigResult[0]['value'])!= ''){
  $explodField = explode(",",$requestEmailConfigResult[0]['value']);
  for($f=0;$f<count($explodField); $f++){
    $requestArr[] = $explodField[$f];
  }
}
?>
<link href="assets/css/multi-select.css" rel="stylesheet" />
<style>
    .ms-container{
        width:100%;
    }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="fa fa-gears"> Edit Request Email Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="otherConfig.php"><i class="fa fa-dashboard"></i> Manage Email/SMS Config</a></li>
        <li class="active">Edit Request Email Configuration</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <!--<div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"> </div>
        </div>-->
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method="post" name="editRequestEmailConfigForm" id="editRequestEmailConfigForm" autocomplete="off" action="editRequestEmailConfigHelper.php">
              <div class="box-body">
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="request_email_field" class="col-lg-3 control-label">Choose VL Fields</label>
                        <div class="col-lg-9">
                           <div style="width:100%;margin:0 auto;clear:both;">
                            <a href="#" id="select-all-field" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href="#" id="deselect-all-field" style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                            </div><br/><br/>
                            <select id="request_email_field" name="request_email_field[]" multiple="multiple" class="search">
                                <option value="serial_no" <?php echo(in_array("serial_no",$requestArr)?"selected='selected'":""); ?>>Form Serial No</option>
                                <option value="urgency" <?php echo(in_array("urgency",$requestArr)?"selected='selected'":""); ?>>Urgency</option>
                                <option value="state" <?php echo(in_array("state",$requestArr)?"selected='selected'":""); ?>>Province</option>
                                <option value="district" <?php echo(in_array("district",$requestArr)?"selected='selected'":""); ?>>District Name</option>
                                <option value="facility_id" <?php echo(in_array("facility_id",$requestArr)?"selected='selected'":""); ?>>Clinic Name</option>
                                <option value="lab_contact_person" <?php echo(in_array("lab_contact_person",$requestArr)?"selected='selected'":""); ?>>Clinician Name</option>
                                <option value="sample_collection_date" <?php echo(in_array("sample_collection_date",$requestArr)?"selected='selected'":""); ?>>Sample Collection Date</option>
                                <option value="date_sample_received_at_testing_lab" <?php echo(in_array("date_sample_received_at_testing_lab",$requestArr)?"selected='selected'":""); ?>>Sample Received Date</option>
                                <option value="collected_by" <?php echo(in_array("collected_by",$requestArr)?"selected='selected'":""); ?>>Collected by (Initials)</option>
                                <option value="patient_name" <?php echo(in_array("patient_name",$requestArr)?"selected='selected'":""); ?>>Patient First Name</option>
                                <option value="surname" <?php echo(in_array("surname",$requestArr)?"selected='selected'":""); ?>>Surname</option>
                                <option value="gender" <?php echo(in_array("gender",$requestArr)?"selected='selected'":""); ?>>Gender</option>
                                <option value="patient_dob" <?php echo(in_array("patient_dob",$requestArr)?"selected='selected'":""); ?>>Date Of Birth</option>
                                <option value="age_in_yrs" <?php echo(in_array("age_in_yrs",$requestArr)?"selected='selected'":""); ?>>Age in years</option>
                                <option value="age_in_mnts" <?php echo(in_array("age_in_mnts",$requestArr)?"selected='selected'":""); ?>>Age in months</option>
                                <option value="is_patient_pregnant" <?php echo(in_array("is_patient_pregnant",$requestArr)?"selected='selected'":""); ?>>Is Patient Pregnant ?</option>
                                <option value="is_patient_breastfeeding" <?php echo(in_array("is_patient_breastfeeding",$requestArr)?"selected='selected'":""); ?>>Is Patient Breastfeeding?</option>
                                <option value="art_no" <?php echo(in_array("art_no",$requestArr)?"selected='selected'":""); ?>>Patient OI/ART Number</option>
                                <option value="date_of_initiation_of_current_regimen" <?php echo(in_array("date_of_initiation_of_current_regimen",$requestArr)?"selected='selected'":""); ?>>Date Of ART Initiation</option>
                                <option value="current_regimen" <?php echo(in_array("current_regimen",$requestArr)?"selected='selected'":""); ?>>ART Regimen</option>
                                <option value="patient_receive_sms" <?php echo(in_array("patient_receive_sms",$requestArr)?"selected='selected'":""); ?>>Patient consent to SMS Notification?</option>
                                <option value="patient_phone_number" <?php echo(in_array("patient_phone_number",$requestArr)?"selected='selected'":""); ?>>Patient Mobile Number</option>
                                <option value="last_viral_load_date" <?php echo(in_array("last_viral_load_date",$requestArr)?"selected='selected'":""); ?>>Date Of Last Viral Load Test</option>
                                <option value="last_viral_load_result" <?php echo(in_array("last_viral_load_result",$requestArr)?"selected='selected'":""); ?>>Result Of Last Viral Load</option>
                                <option value="viral_load_log" <?php echo(in_array("viral_load_log",$requestArr)?"selected='selected'":""); ?>>Viral Load Log</option>
                                <option value="vl_test_reason" <?php echo(in_array("vl_test_reason",$requestArr)?"selected='selected'":""); ?>>Reason For VL Test</option>
                                <option value="lab_id" <?php echo(in_array("lab_id",$requestArr)?"selected='selected'":""); ?>>Lab Name</option>
                                <option value="lab_no" <?php echo(in_array("lab_no",$requestArr)?"selected='selected'":""); ?>>LAB No</option>
                                <option value="vl_test_platform" <?php echo(in_array("vl_test_platform",$requestArr)?"selected='selected'":""); ?>>VL Testing Platform</option>
                                <option value="sample_id" <?php echo(in_array("sample_id",$requestArr)?"selected='selected'":""); ?>>Specimen type</option>
                                <option value="lab_tested_date" <?php echo(in_array("lab_tested_date",$requestArr)?"selected='selected'":""); ?>>Sample Testing Date</option>
                                <option value="absolute_value" <?php echo(in_array("absolute_value",$requestArr)?"selected='selected'":""); ?>>Viral Load Result(copiesl/ml)</option>
                                <option value="log_value" <?php echo(in_array("log_value",$requestArr)?"selected='selected'":""); ?>>Log Value</option>
                                <option value="rejection" <?php echo(in_array("rejection",$requestArr)?"selected='selected'":""); ?>>If no result</option>
                                <option value="sample_rejection_reason" <?php echo(in_array("sample_rejection_reason",$requestArr)?"selected='selected'":""); ?>>Rejection Reason</option>
                                <option value="result_reviewed_by" <?php echo(in_array("result_reviewed_by",$requestArr)?"selected='selected'":""); ?>>Reviewed By</option>
                                <option value="result_approved_by" <?php echo(in_array("result_approved_by",$requestArr)?"selected='selected'":""); ?>>Approved By</option>
                                <option value="comments" <?php echo(in_array("comments",$requestArr)?"selected='selected'":""); ?>>Laboratory Scientist Comments</option>
                            </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="otherConfig.php" class="btn btn-default"> Cancel</a>
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
  <script type="text/javascript">
  $(document).ready(function() {
      $('.search').multiSelect({
       selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Field Name'>",
       selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Field Name'>",
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
         this.qs1.cache();
         this.qs2.cache();
       },
       afterDeselect: function(){
        this.qs1.cache();
        this.qs2.cache();
       }
     });
      
      $('#select-all-field').click(function(){
       $('#request_email_field').multiSelect('select_all');
       return false;
     });
     $('#deselect-all-field').click(function(){
       $('#request_email_field').multiSelect('deselect_all');
       return false;
     });
   });
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editRequestEmailConfigForm'
    });
    
    if(flag){
        $.blockUI();
      document.getElementById('editRequestEmailConfigForm').submit();
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
