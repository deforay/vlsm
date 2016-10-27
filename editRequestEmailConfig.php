<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
$otherConfigQuery ="SELECT * from other_config";
$otherConfigResult=$db->query($otherConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($otherConfigResult); $i++) {
    $arr[$otherConfigResult[$i]['name']] = $otherConfigResult[$i]['value'];
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
      <h1 class="fa fa-gears"> Edit Email/SMS Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage Email/SMS Config</li>
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
                        <label for="field" class="col-lg-3 control-label">Choose VL Request Fields</label>
                        <div class="col-lg-9">
                           <div style="width:100%;margin:0 auto;clear:both;">
                            <a href="#" id="select-all-field" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href="#" id="deselect-all-field" style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                            </div><br/><br/>
                            <select id="request_email_field" name="request_email_field[]" multiple="multiple" class="search">
                                <option value="serial_no">Form Serial No</option>
                                <option value="urgency">Urgency</option>
                                <option value="state">Province</option>
                                <option value="district">District Name</option>
                                <option value="facility_id">Clinic Name</option>
                                <option value="lab_contact_person">Clinician Name</option>
                                <option value="sample_collection_date">Sample Collection Date</option>
                                <option value="date_sample_received_at_testing_lab">Sample Received Date</option>
                                <option value="collected_by">Collected by (Initials)</option>
                                <option value="patient_name">Patient First Name</option>
                                <option value="surname">Surname</option>
                                <option value="gender">Gender</option>
                                <option value="patient_dob">Date Of Birth</option>
                                <option value="age_in_yrs">Age in years</option>
                                <option value="age_in_mnts">Age in months</option>
                                <option value="is_patient_pregnant">Is Patient Pregnant ?</option>
                                <option value="is_patient_breastfeeding">Is Patient Breastfeeding?</option>
                                <option value="art_no">Patient OI/ART Number</option>
                                <option value="date_of_initiation_of_current_regimen">Date Of ART Initiation</option>
                                <option value="current_regimen">ART Regimen</option>
                                <option value="patient_receive_sms">Patient consent to SMS Notification?</option>
                                <option value="patient_phone_number">Patient Mobile Number</option>
                                <option value="last_viral_load_date">Date Of Last Viral Load Test</option>
                                <option value="last_viral_load_result">Result Of Last Viral Load</option>
                                <option value="viral_load_log">Viral Load Log</option>
                                <option value="vl_test_reason">Reason For VL Test</option>
                                <option value="lab_id">Lab Name</option>
                                <option value="lab_no">LAB No</option>
                                <option value="vl_test_platform">VL Testing Platform</option>
                                <option value="sample_id">Specimen type</option>
                                <option value="lab_tested_date">Sample Testing Date</option>
                                <option value="absolute_value">Viral Load Result(copiesl/ml)</option>
                                <option value="log_value">Log Value</option>
                                <option value="rejection">If no result</option>
                                <option value="sample_rejection_reason">Rejection Reason</option>
                                <option value="result_reviewed_by">Reviewed By</option>
                                <option value="result_approved_by">Approved By</option>
                                <option value="comments">Laboratory Scientist Comments</option>
                            </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="text" id="d" name="dd" value="ada"/>
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
