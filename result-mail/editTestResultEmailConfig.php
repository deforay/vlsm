<?php
ob_start();
include('../header.php');
$otherConfigQuery ="SELECT * from other_config WHERE type='result'";
$otherConfigResult=$db->query($otherConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($otherConfigResult); $i++) {
    $arr[$otherConfigResult[$i]['name']] = $otherConfigResult[$i]['value'];
}

$requestArr = array();
//Set selected field
if(isset($arr['rs_field']) && trim($arr['rs_field'])!= ''){
  $explodField = explode(",",$arr['rs_field']);
  for($f=0;$f<count($explodField); $f++){
    $requestArr[] = $explodField[$f];
  }
}
?>
<link href="../assets/css/multi-select.css" rel="stylesheet" />
<style>
    .ms-container{
        width:100%;
    }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="fa fa-gears"> Edit Test Result Email Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="testResultEmailConfig.php"><i class="fa fa-dashboard"></i> Manage Test Result Email/SMS Config</a></li>
        <li class="active">Edit Test Result Email Configuration</li>
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
            <form class="form-horizontal" name="editTestResultEmailConfigForm" id="editTestResultEmailConfigForm" method="post"  autocomplete="off" action="editTestResultEmailConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="rs_email" class="col-lg-3 control-label">Email Id (gmail only)*</label>
                      <div class="col-lg-9">
                        <input type="text" class="form-control isEmail isRequired" id="rs_email" name="rs_email" placeholder="eg.hello.vl@gmail.com" title="Please enter email" value="<?php echo $arr['rs_email']; ?>">
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="rs_password" class="col-lg-3 control-label">Password *</label>
                      <div class="col-lg-9">
                        <input type="text" class="form-control isRequired" id="rs_password" name="rs_password" placeholder="Password" title="Please enter password" value="<?php echo $arr['rs_password']; ?>">
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="rs_field" class="col-lg-3 control-label">Choose Fields *</label>
                        <div class="col-lg-9">
                           <div style="width:100%;margin:0 auto;clear:both;">
                            <a href="#" id="select-all-field" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href="#" id="deselect-all-field" style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                            </div><br/><br/>
                            <select id="rs_field" name="rs_field[]" multiple="multiple" class="search isRequired" title="Please select email fields">
                                <option value="Form Serial No" <?php echo(in_array("Form Serial No",$requestArr)?"selected='selected'":""); ?>>Form Serial No</option>
                                <option value="Urgency" <?php echo(in_array("Urgency",$requestArr)?"selected='selected'":""); ?>>Urgency</option>
                                <option value="Province" <?php echo(in_array("Province",$requestArr)?"selected='selected'":""); ?>>Province</option>
                                <option value="District Name" <?php echo(in_array("District Name",$requestArr)?"selected='selected'":""); ?>>District Name</option>
                                <option value="Clinic Name" <?php echo(in_array("Clinic Name",$requestArr)?"selected='selected'":""); ?>>Clinic Name</option>
                                <option value="Clinician Name" <?php echo(in_array("Clinician Name",$requestArr)?"selected='selected'":""); ?>>Clinician Name</option>
                                <option value="Sample Collection Date" <?php echo(in_array("Sample Collection Date",$requestArr)?"selected='selected'":""); ?>>Sample Collection Date</option>
                                <option value="Sample Received Date" <?php echo(in_array("Sample Received Date",$requestArr)?"selected='selected'":""); ?>>Sample Received Date</option>
                                <option value="Collected by (Initials)" <?php echo(in_array("Collected by (Initials)",$requestArr)?"selected='selected'":""); ?>>Collected by (Initials)</option>
                                <option value="Gender" <?php echo(in_array("Gender",$requestArr)?"selected='selected'":""); ?>>Gender</option>
                                <option value="Date Of Birth" <?php echo(in_array("Date Of Birth",$requestArr)?"selected='selected'":""); ?>>Date Of Birth</option>
                                <option value="Age in years" <?php echo(in_array("Age in years",$requestArr)?"selected='selected'":""); ?>>Age in years</option>
                                <option value="Age in months" <?php echo(in_array("Age in months",$requestArr)?"selected='selected'":""); ?>>Age in months</option>
                                <option value="Is Patient Pregnant?" <?php echo(in_array("Is Patient Pregnant?",$requestArr)?"selected='selected'":""); ?>>Is Patient Pregnant?</option>
                                <option value="Is Patient Breastfeeding?" <?php echo(in_array("Is Patient Breastfeeding?",$requestArr)?"selected='selected'":""); ?>>Is Patient Breastfeeding?</option>
                                <option value="Patient OI/ART Number" <?php echo(in_array("Patient OI/ART Number",$requestArr)?"selected='selected'":""); ?>>Patient OI/ART Number</option>
                                <option value="Date Of ART Initiation" <?php echo(in_array("Date Of ART Initiation",$requestArr)?"selected='selected'":""); ?>>Date Of ART Initiation</option>
                                <option value="ART Regimen" <?php echo(in_array("ART Regimen",$requestArr)?"selected='selected'":""); ?>>ART Regimen</option>
                                <option value="Patient consent to SMS Notification?" <?php echo(in_array("Patient consent to SMS Notification?",$requestArr)?"selected='selected'":""); ?>>Patient consent to SMS Notification?</option>
                                <option value="Patient Mobile Number" <?php echo(in_array("Patient Mobile Number",$requestArr)?"selected='selected'":""); ?>>Patient Mobile Number</option>
                                <option value="Date Of Last Viral Load Test" <?php echo(in_array("Date Of Last Viral Load Test",$requestArr)?"selected='selected'":""); ?>>Date Of Last Viral Load Test</option>
                                <option value="Result Of Last Viral Load" <?php echo(in_array("Result Of Last Viral Load",$requestArr)?"selected='selected'":""); ?>>Result Of Last Viral Load</option>
                                <option value="Viral Load Log" <?php echo(in_array("Viral Load Log",$requestArr)?"selected='selected'":""); ?>>Viral Load Log</option>
                                <option value="Reason For VL Test" <?php echo(in_array("Reason For VL Test",$requestArr)?"selected='selected'":""); ?>>Reason For VL Test</option>
                                <option value="Lab Name" <?php echo(in_array("Lab Name",$requestArr)?"selected='selected'":""); ?>>Lab Name</option>
                                <option value="LAB No" <?php echo(in_array("LAB No",$requestArr)?"selected='selected'":""); ?>>LAB No</option>
                                <option value="VL Testing Platform" <?php echo(in_array("VL Testing Platform",$requestArr)?"selected='selected'":""); ?>>VL Testing Platform</option>
                                <option value="Specimen type" <?php echo(in_array("Specimen type",$requestArr)?"selected='selected'":""); ?>>Specimen type</option>
                                <option value="Sample Testing Date" <?php echo(in_array("Sample Testing Date",$requestArr)?"selected='selected'":""); ?>>Sample Testing Date</option>
                                <option value="Viral Load Result(copiesl/ml)" <?php echo(in_array("Viral Load Result(copiesl/ml)",$requestArr)?"selected='selected'":""); ?>>Viral Load Result(copiesl/ml)</option>
                                <option value="Log Value" <?php echo(in_array("Log Value",$requestArr)?"selected='selected'":""); ?>>Log Value</option>
                                <option value="If no result" <?php echo(in_array("If no result",$requestArr)?"selected='selected'":""); ?>>If no result</option>
                                <option value="Rejection Reason" <?php echo(in_array("Rejection Reason",$requestArr)?"selected='selected'":""); ?>>Rejection Reason</option>
                                <option value="Reviewed By" <?php echo(in_array("Reviewed By",$requestArr)?"selected='selected'":""); ?>>Reviewed By</option>
                                <option value="Approved By" <?php echo(in_array("Approved By",$requestArr)?"selected='selected'":""); ?>>Approved By</option>
                                <option value="Laboratory Scientist Comments" <?php echo(in_array("Laboratory Scientist Comments",$requestArr)?"selected='selected'":""); ?>>Laboratory Scientist Comments</option>
                            </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="testResultEmailConfig.php" class="btn btn-default"> Cancel</a>
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
       $('#rs_field').multiSelect('select_all');
       return false;
     });
     $('#deselect-all-field').click(function(){
       $('#rs_field').multiSelect('deselect_all');
       return false;
     });
   });
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editTestResultEmailConfigForm'
    });
    
    if(flag){
        $.blockUI();
      document.getElementById('editTestResultEmailConfigForm').submit();
    }
  }
</script>
  
 <?php
 include('../footer.php');
 ?>