<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$id=base64_decode($_GET['id']);
$fQuery="SELECT vl.*,f.facility_name,f.facility_code,f.hub_name,f.state,f.district from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where treament_id=$id";
$result=$db->query($fQuery);

$uQuery = "Select * from user_details where user_id=".$result[0]['result_reviewed_by'];
$uResult=$db->query($uQuery);
if($uResult){
 $uName = $uResult[0]['user_name'];
 $uId = $uResult[0]['user_id'];
}else{
 $uName = $_SESSION['userId'];
 $uId = $_SESSION['userName'];
}

if(isset($result[0]['patient_dob']) && trim($result[0]['patient_dob'])!='' && $result[0]['patient_dob']!='0000-00-00'){
 $result[0]['patient_dob']=$general->humanDateFormat($result[0]['patient_dob']);
}else{
 $result[0]['patient_dob']='';
}

if(isset($result[0]['sample_collection_date']) && trim($result[0]['sample_collection_date'])!='' && $result[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$result[0]['sample_collection_date']);
 $result[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $result[0]['sample_collection_date']='';
}

if(isset($result[0]['treatment_initiated_date']) && trim($result[0]['treatment_initiated_date'])!='' && trim($result[0]['treatment_initiated_date'])!='0000-00-00'){
 $result[0]['treatment_initiated_date']=$general->humanDateFormat($result[0]['treatment_initiated_date']);
}else{
 $result[0]['treatment_initiated_date']='';
}

if(isset($result[0]['date_of_initiation_of_current_regimen']) && trim($result[0]['date_of_initiation_of_current_regimen'])!='' && trim($result[0]['date_of_initiation_of_current_regimen'])!='0000-00-00'){
 $result[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($result[0]['date_of_initiation_of_current_regimen']);
}else{
 $result[0]['date_of_initiation_of_current_regimen']='';
}

if(isset($result[0]['routine_monitoring_last_vl_date']) && trim($result[0]['routine_monitoring_last_vl_date'])!='' && trim($result[0]['routine_monitoring_last_vl_date'])!='0000-00-00'){
 $result[0]['routine_monitoring_last_vl_date']=$general->humanDateFormat($result[0]['routine_monitoring_last_vl_date']);
}else{
 $result[0]['routine_monitoring_last_vl_date']='';
}

if(isset($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']) && trim($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'])!='' && trim($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date'])!='0000-00-00'){
 $result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']=$general->humanDateFormat($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']);
}else{
 $result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']='';
}

if(isset($result[0]['suspected_treatment_failure_last_vl_date']) && trim($result[0]['suspected_treatment_failure_last_vl_date'])!='' && trim($result[0]['suspected_treatment_failure_last_vl_date'])!='0000-00-00'){
 $result[0]['suspected_treatment_failure_last_vl_date']=$general->humanDateFormat($result[0]['suspected_treatment_failure_last_vl_date']);
}else{
 $result[0]['suspected_treatment_failure_last_vl_date']='';
}
if(isset($result[0]['switch_to_tdf_last_vl_date']) && trim($result[0]['switch_to_tdf_last_vl_date'])!='' && trim($result[0]['switch_to_tdf_last_vl_date'])!='0000-00-00'){
 $result[0]['switch_to_tdf_last_vl_date']=$general->humanDateFormat($result[0]['switch_to_tdf_last_vl_date']);
}else{
 $result[0]['switch_to_tdf_last_vl_date']='';
}
if(isset($result[0]['missing_last_vl_date']) && trim($result[0]['missing_last_vl_date'])!='' && trim($result[0]['missing_last_vl_date'])!='0000-00-00'){
 $result[0]['missing_last_vl_date']=$general->humanDateFormat($result[0]['missing_last_vl_date']);
}else{
 $result[0]['missing_last_vl_date']='';
}

if(isset($result[0]['request_date']) && trim($result[0]['request_date'])!='' && trim($result[0]['request_date'])!='0000-00-00'){
 $result[0]['request_date']=$general->humanDateFormat($result[0]['request_date']);
}else{
 $result[0]['request_date']='';
}

if(isset($result[0]['date_sample_received_at_testing_lab']) && trim($result[0]['date_sample_received_at_testing_lab'])!='' && trim($result[0]['date_sample_received_at_testing_lab'])!='0000-00-00'){
 $result[0]['date_sample_received_at_testing_lab']=$general->humanDateFormat($result[0]['date_sample_received_at_testing_lab']);
}else{
 $result[0]['date_sample_received_at_testing_lab']='';
}

if(isset($result[0]['lab_tested_date']) && trim($result[0]['lab_tested_date'])!='' && trim($result[0]['lab_tested_date'])!='0000-00-00'){
 $result[0]['lab_tested_date']=$general->humanDateFormat($result[0]['lab_tested_date']);
}else{
 $result[0]['lab_tested_date']='';
}

if(isset($result[0]['date_results_dispatched']) && trim($result[0]['date_results_dispatched'])!='' && trim($result[0]['date_results_dispatched'])!='0000-00-00'){
 $result[0]['date_results_dispatched']=$general->humanDateFormat($result[0]['date_results_dispatched']);
}else{
 $result[0]['date_results_dispatched']='';
}

if(isset($result[0]['result_reviewed_date']) && trim($result[0]['result_reviewed_date'])!='' && trim($result[0]['result_reviewed_date'])!='0000-00-00'){
 $result[0]['result_reviewed_date']= $general->humanDateFormat($result[0]['result_reviewed_date']);
}else{
 $result[0]['result_reviewed_date']= $general->humanDateFormat(date('Y-m-d'));
}

$query="SELECT * from r_art_code_details where parent_art=0";
$qResult=$db->query($query);
$artCode=[];
foreach($qResult as $val){
 $aQuery="SELECT * from r_art_code_details where parent_art=".$val['art_id'];
 $aResult=$db->query($aQuery);
 foreach($aResult as $vl){
  $artCode[$val['art_code']][$vl['art_id']]=$vl['art_code'];
 }
}
$sampleTypeQuery="SELECT * FROM r_sample_type";
$sampleTypeResult = $db->rawQuery($sampleTypeQuery);
$sampleType='<option value="">-- Select --</option>';
//get test status values
$tsQuery="SELECT * FROM testing_status";
$tsResult = $db->rawQuery($tsQuery);
?>
<link rel="stylesheet" href="assets/css/easy-autocomplete.min.css">
<script type="text/javascript" src="assets/js/jquery.easy-autocomplete.min.js"></script>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
   <style>
          /*.hide-calendar .ui-datepicker-calendar {
          display: none;
      }*/
      .ui_tpicker_second_label {
        display: none !important;
       }.ui_tpicker_second_slider {
        display: none !important;
       }.ui_tpicker_millisec_label {
        display: none !important;
       }.ui_tpicker_millisec_slider {
        display: none !important;
       }.ui_tpicker_microsec_label {
        display: none !important;
       }.ui_tpicker_microsec_slider {
        display: none !important;
       }.ui_tpicker_timezone_label {
        display: none !important;
       }.ui_tpicker_timezone {
        display: none !important;
       }.ui_tpicker_time_input{
        width:100%;
       }
       #toogleResultDiv{
        display:none;
       }
   </style>
   <link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
   <link rel="stylesheet" media="all" type="text/css" href="assets/css/jquery-ui-timepicker-addon.css" />
    <section class="content-header">
      <h1>Edit VL Request</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Edit VL Request</li>
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
            <form class="form-horizontal" method='post' name='editVlRequestForm' id='editVlRequestForm' autocomplete="off"  action="editVlRequestHelper.php">
              <div class="box-body">
               <div class="row">
                   <div class="col-md-12"><h4><a id="vlrfa" href="javascript:void(0);" onclick="formToggler('-');">VL Request Form Details <i class="fa fa-minus"></i></a></h4></div>
               </div>
             <div id="toogleFormDiv">
              <div class="box box-default">
            <div class="box-header with-border">
               <div class="pull-left"><h3 class="box-title">Facility Information</h3></div>
               <div class="pull-right"><a id="clearFInfo" href="javascript:void(0);" onclick="clearFacilitiesInfo();" class="btn btn-danger btn-sm" style="padding-right:10px;">Clear</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="showModal('facilitiesModal.php',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Facility <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="hidden" id="facilityId" name="facilityId" value="<?php echo $result[0]['facility_id']; ?>"/>
                        <input type="text" class="form-control isRequired" id="facilityName" name="facilityName" placeholder="Facility" title="Please enter facility" value="<?php echo $result[0]['facility_name']; ?>" readonly/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State/Province</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="state" name="state" placeholder="State" value="<?php echo $result[0]['state']; ?>" readonly/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Linked Hub Name (If Applicable)</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" value="<?php echo $result[0]['hub_name']; ?>" readonly/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="district" class="col-lg-4 control-label">District</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="district" name="district" placeholder="District" title="Please enter district" value="<?php echo $result[0]['district']; ?>" readonly />
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.box-footer-->
              
          <div class="box box-primary">
            <div class="box-header with-border">
              <div class="pull-left"><h3 class="box-title">Patient Details</h3></div>
              <div class="pull-right"><a href="javascript:void(0);" onclick="showModal('vlRequestModal.php',1100,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="artNo" class="col-lg-4 control-label">Unique ART No. <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <input type="text" class="form-control isRequired" id="artNo" name="artNo" placeholder="ART Number" title="Please enter art number" value="<?php echo $result[0]['art_no']; ?>" />
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleCode" class="col-lg-4 control-label">Sample Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Sample Code" title="Please enter the sample code" value="<?php echo $result[0]['sample_code']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="otrId" class="col-lg-4 control-label">Other Id</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="otrId" name="otrId" placeholder="Enter Other Id" title="Please enter Other Id" value="<?php echo $result[0]['other_id']; ?>"/>
                        </div>
                    </div>
                   </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientName" class="col-lg-4 control-label">Patient's Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="patientName" name="patientName" placeholder="Patient Name" title="Please enter patient name" value="<?php echo $result[0]['patient_name']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                     <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Date of Birth</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="dob" name="dob" placeholder="Enter DOB" title="Enter patient date of birth" value="<?php echo $result[0]['patient_dob']; ?>"/>
                        </div>
                    </div>
                  </div>
                     <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender" class="col-lg-4 control-label">Gender <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                         <input type="radio"  id="genderMale" name="gender" value="male" title="Please check gender" <?php echo ($result[0]['gender']=='male')?"checked='checked'":""?>> Male
                        </label>
                        <label class="radio-inline">
                         <input type="radio" class="isRequired" id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo ($result[0]['gender']=='female')?"checked='checked'":""?>> Female
                        </label>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInYrs" class="col-lg-4 control-label">Age in years</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="ageInYrs" name="ageInYrs" placeholder="Enter age in years" title="Please enter age in years" value="<?php echo $result[0]['age_in_yrs']; ?>"/>
                        <p class="help-block"><small>If DOB Unkown</small></p>
                        </div>
                    </div>
                  </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInMtns" class="col-lg-4 control-label">Age in months</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="ageInMtns" data-calendar="false" name="ageInMtns" placeholder="Enter Age in months" title="Please enter age in" value="<?php echo $result[0]['age_in_mnts']; ?>"/>
                        <p class="help-block"><small>If age < 1 years </small></p>
                        </div>
                    </div>
                  </div>
                    
                  
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Patient Phone No." title="Please enter patient Phone No" value="<?php echo $result[0]['patient_phone_number']; ?>"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="location" class="col-lg-4 control-label">Location/District Code</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="patientLocation" name="patientLocation" placeholder="Enter Patient location/district code" title="Please enter patient location/district code" value="<?php echo $result[0]['location']; ?>" />
                        </div>
                    </div>
                  </div>
                </div>
                 
                 
            </div>
            <!-- /.box-footer-->
          </div>
               
               <div class="box box-danger ">
            <div class="box-header with-border">
              <h3 class="box-title">Sample Information </h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Sample Collected On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control readonly" readonly='readonly' id="sampleCollectionDate" name="sampleCollectionDate" placeholder="Enter Sample Collection Date" title="Please enter sample collection date" value="<?php echo $result[0]['sample_collection_date']; ?>" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Sample Type <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <select class="form-control isRequired" name='sampleType' id='sampleType' title="Please select sample type">
                           <option value="">-- Select --</option>
                           <?php
                           foreach ($sampleTypeResult as $row) {
                           ?>
                           <option value="<?php echo $row['sample_id']; ?>" <?php echo ($result[0]['sample_id']==$row['sample_id'])?"selected='selected'":""?>><?php echo ucwords($row['sample_name']); ?></option>
                           <?php
                           }
                           ?>
                         </select>
                        </div>
                    </div>
                  </div>                       
                </div>
            </div>
            <!-- /.box-footer-->
          </div>
                
                <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Treatment Information</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatPeriod" class="col-lg-4 control-label">How long has this patient been on treatment ?</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="treatPeriod" name="treatPeriod" placeholder="Enter Treatment Period" title="Please enter how long has this patient been on treatment" value="<?php echo $result[0]['treatment_initiation']; ?>"/>
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatmentInitiatiatedOn" class="col-lg-4 control-label">Treatment Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="treatmentInitiatiatedOn" name="treatmentInitiatiatedOn" placeholder="Treatment Initiated On" title="Please enter treatment initiated date" value="<?php echo $result[0]['treatment_initiated_date']; ?>" />
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="currentRegimen" class="col-lg-4 control-label">Current Regimen</label>
                        <div class="col-lg-7">
                        <select class="form-control " id="currentRegimen" name="currentRegimen" placeholder="Enter Current Regimen" title="Please enter current regimen">
                         <option value="">-- Select --</option>
                         <?php
                         foreach($artCode as $pKey=>$parentRow){
                         ?>
                         <optgroup label="<?php echo $pKey ?>">
                         <?php
                         foreach($parentRow as $key=>$val){
                         ?>
                          <option value="<?php echo $key; ?>" <?php echo ($result[0]['current_regimen']==$key)?"selected='selected'":""?>><?php echo $val; ?></option>
                         <?php
                         }
                         ?>
                         </optgroup>
                         <?php
                         }
                         ?>
                        </select>
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Current Regimen Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="regimenInitiatedOn" name="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" value="<?php echo $result[0]['date_of_initiation_of_current_regimen']; ?>"/>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <div class="form-group">
                        <label for="treatmentDetails" class="col-lg-2 control-label">Which line of treatment is Patient on ?</label>
                        <div class="col-lg-10">
                            <textarea class="form-control" id="treatmentDetails" name="treatmentDetails" placeholder="Enter treatment details" title="Please enter treatment details"><?php echo $result[0]['treatment_details']; ?></textarea>
                        </div>
                    </div>
                  </div>    
                </div>
                <div class="row femaleElements" <?php echo($result[0]['gender'] == 'male')?'style="display:none;"':''; ?>>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="pregYes" class="col-lg-4 control-label">Is Patient Pregnant ?</label>
                        <div class="col-lg-7">                        
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);" <?php echo ($result[0]['is_patient_pregnant']=='yes')?"checked='checked'":""?>> Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);" <?php echo ($result[0]['is_patient_pregnant']=='no')?"checked='checked'":""?>> No
                          </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArcNo" class="col-lg-4 control-label">If Pregnant, ARC No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="arcNo" name="arcNo" placeholder="Enter ARC no." title="Please enter arc no" value="<?php echo $result[0]['arc_no']; ?>"/>
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6 femaleElements" <?php echo($result[0]['gender'] == 'male')?'style="display:none;"':''; ?>>
                    <div class="form-group">
                        <label for="breastfeeding" class="col-lg-4 control-label">Is Patient Breastfeeding?</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);" <?php echo ($result[0]['is_patient_breastfeeding']=='yes')?"checked='checked'":""?>> Yes
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);" <?php echo ($result[0]['is_patient_breastfeeding']=='no')?"checked='checked'":""?>> No
                          </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="receiveSms" class="col-lg-4 control-label">Patient consent to receive SMS?</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);" <?php echo ($result[0]['patient_receive_sms']=='yes')?"checked='checked'":""?>> Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);" <?php echo ($result[0]['patient_receive_sms']=='no')?"checked='checked'":""?>> No
                       </label>
                        </div>
                    </div>
                  </div>                    
                </div>
                
            </div>
            <!-- /.box-footer-->
          </div>
               
                
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Indication for viral load testing</h3>
              <small>(Please tick one):(To be completed by clinician)</small>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
              <div class="col-md-6">
                 <div class="form-group">
                     <label for="ArvAdherence" class="col-lg-4 control-label">ARV Adherence </label>
                     <div class="col-lg-7">
                     <!--<input type="text" class="form-control" id="arvAdherence" name="arvAdherence" placeholder="Enter ARV Adherence" title="Please enter ARV adherence" />-->
                     <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence">
                      <option value="">--select--</option>
                      <option value="good" <?php echo ($result[0]['arv_adherence']=='good')?"selected='selected'":""?>>Good >= 95%</option>
                      <option value="fair" <?php echo ($result[0]['arv_adherence']=='fair')?"selected='selected'":""?>>Fair (85-94%)</option>
                      <option value="poor" <?php echo ($result[0]['arv_adherence']=='poor')?"selected='selected'":""?>>Poor < 85%</option>
                     </select>
                     </div>
                 </div>
               </div>
             </div>
             <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                             <?php
                             $checked = '';
                             $display = '';
                             if($result[0]['routine_monitoring_last_vl_date']!='' || $result[0]['routine_monitoring_value']!='' || $result[0]['routine_monitoring_sample_type']!=''){
                              $checked = 'checked="checked"';
                              $display = 'block';
                             }else{
                              $checked = '';
                              $display = 'none';
                             }
                             ?>
                               <input type="radio" class="" id="RmTesting" name="stViralTesting" value="Routine Monitoring" title="Please check routine monitoring" <?php echo $checked;?> onclick="showTesting('RmTesting');">
                                <strong>Routine Monitoring</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RmTesting hideTestData" style="display: <?php echo $display;?>;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['routine_monitoring_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['routine_monitoring_value']; ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <!--<input type="text" class="form-control" id="RmTestingSampleType" name="RmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />-->
                        <select class="form-control viralTestData" id="rmTestingSampleType" name="rmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                         <option value="">-- Select --</option>
                           <?php
                           foreach ($sampleTypeResult as $row) {
                           ?>
                           <option value="<?php echo $row['sample_id']; ?>" <?php echo ($result[0]['routine_monitoring_sample_type']==$row['sample_id'])?"selected='selected'":""?>><?php echo ucwords($row['sample_name']); ?></option>
                           <?php
                           }
                           ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
                
                <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                             <?php
                             $checked = '';
                             $display = '';
                             if($result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']!='' || $result[0]['vl_treatment_failure_adherence_counseling_value']!='' || $result[0]['vl_treatment_failure_adherence_counseling_sample_type']!=''){
                              $checked = 'checked="checked"';
                              $display = 'block';
                             }else{
                              $checked = '';
                              $display = 'none';
                             }
                             ?>
                                <input type="radio" class="" id="RepeatTesting" name="stViralTesting" value="male" title="Repeat VL test after suspected treatment failure adherence counseling" <?php echo $checked;?> onclick="showTesting('RepeatTesting');">
                                <strong>Repeat VL test after suspected treatment failure adherence counseling</strong>
                            </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RepeatTesting hideTestData" style="display: <?php echo $display;?>;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['vl_treatment_failure_adherence_counseling_value']; ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="repeatTestingSampleType" name="repeatTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <option value="">-- Select --</option>
                           <?php
                           foreach ($sampleTypeResult as $row) {
                           ?>
                           <option value="<?php echo $row['sample_id']; ?>" <?php echo ($result[0]['vl_treatment_failure_adherence_counseling_sample_type']==$row['sample_id'])?"selected='selected'":""?>><?php echo ucwords($row['sample_name']); ?></option>
                           <?php
                           }
                           ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
                
                <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                             <?php
                             $checked = '';
                             $display = '';
                             if($result[0]['suspected_treatment_failure_last_vl_date']!='' || $result[0]['suspected_treatment_failure_value']!='' || $result[0]['suspected_treatment_failure_sample_type']!=''){
                              $checked = 'checked="checked"';
                              $display = 'block';
                             }else{
                              $checked = '';
                              $display = 'none';
                             }
                             ?>
                                <input type="radio" class="" id="suspendTreatment" name="stViralTesting" value="male" title="Suspect Treatment Failure" <?php echo $checked;?> onclick="showTesting('suspendTreatment');">
                                <strong>Suspect Treatment Failure</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row suspendTreatment hideTestData" style="display: <?php echo $display;?>;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['suspected_treatment_failure_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['suspected_treatment_failure_value']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="suspendTreatmentSampleType" name="suspendTreatmentSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <option value="">-- Select --</option>
                           <?php
                           foreach ($sampleTypeResult as $row) {
                           ?>
                           <option value="<?php echo $row['sample_id']; ?>" <?php echo ($result[0]['suspected_treatment_failure_sample_type']==$row['sample_id'])?"selected='selected'":""?>><?php echo ucwords($row['sample_name']); ?></option>
                           <?php
                           }
                           ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
                <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                                <?php
                                $checked = '';
                                $display = '';
                                if($result[0]['switch_to_tdf_last_vl_date']!='' || $result[0]['switch_to_tdf_value']!='' || $result[0]['switch_to_tdf_sample_type']!=''){
                                 $checked = 'checked="checked"';
                                 $display = 'block';
                                }else{
                                 $checked = '';
                                 $display = 'none';
                                }
                                ?>
                                <input type="radio" class="" id="switchToTDF" name="stViralTesting" value="switch" title="Switch to TDF" <?php echo $checked;?> onclick="showTesting('switchToTDFTreatment');">
                                <strong>Switch to TDF</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row switchToTDFTreatment hideTestData"  style="display: <?php echo $display;?>;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="switchToTDFLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="switchToTDFLastVLDate" name="switchToTDFLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['switch_to_tdf_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="switchToTDFVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="switchToTDFVlValue" name="switchToTDFVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['switch_to_tdf_value']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="switchToTDFSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="switchToTDFSampleType" name="switchToTDFSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                         <option value="">--select--</option>
                          <?php
                           foreach ($sampleTypeResult as $row) {
                           ?>
                           <option value="<?php echo $row['sample_id']; ?>" <?php echo ($result[0]['switch_to_tdf_sample_type']==$row['sample_id'])?"selected='selected'":""?>><?php echo ucwords($row['sample_name']); ?></option>
                           <?php
                           }
                           ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
               <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                                <?php
                                $checked = '';
                                $display = '';
                                if($result[0]['missing_last_vl_date']!='' || $result[0]['missing_value']!='' || $result[0]['missing_sample_type']!=''){
                                 $checked = 'checked="checked"';
                                 $display = 'block';
                                }else{
                                 $checked = '';
                                 $display = 'none';
                                }
                                ?>
                                <input type="radio" class="" id="missing" name="stViralTesting" value="missing" title="Missing" <?php echo $checked;?> onclick="showTesting('missingTreatment');">
                                <strong>Missing</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row missingTreatment hideTestData" style="display: <?php echo $display;?>;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="missingLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date viralTestData readonly" readonly='readonly' id="missingLastVLDate" name="missingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['missing_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="missingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control viralTestData" id="missingVlValue" name="missingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['missing_value']; ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="missingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control viralTestData" id="missingSampleType" name="missingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                         <option value="">--select--</option>
                          <?php
                           foreach ($sampleTypeResult as $row) {
                           ?>
                           <option value="<?php echo $row['sample_id']; ?>" <?php echo ($result[0]['missing_sample_type']==$row['sample_id'])?"selected='selected'":""?>><?php echo ucwords($row['sample_name']); ?></option>
                           <?php
                           }
                           ?>
                        </select>
                        </div>
                    </div>
                  </div>                   
                </div>
            </div>
            <!-- /.box-footer-->
          </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestClinician" class="col-lg-4 control-label">Request Clinician</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="requestClinician" name="requestClinician" placeholder="Enter Clinician" title="Please enter clinician name" value="<?php echo $result[0]['request_clinician']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="clinicianPhone" class="col-lg-4 control-label">Phone No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="clinicianPhone" name="clinicianPhone" placeholder="Clinician Phone No." title="Please enter phone no." value="<?php echo $result[0]['clinician_ph_no']; ?>"/>
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestDate" class="col-lg-4 control-label">Request Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="requestDate" name="requestDate" placeholder="Request Date" placeholder="Request Date" title="Please enter request date" value="<?php echo $result[0]['request_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlFocalPerson" class="col-lg-4 control-label">VL Focal Person</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter VL Focal Person" value="<?php echo $result[0]['vl_focal_person']; ?>"/>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="vlPhoneNumber" name="vlPhoneNumber" placeholder="VL Focal Person Phone Number" title=" Please enter vl focal person phone number" value="<?php echo $result[0]['focal_person_phone_number']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="emailHf" class="col-lg-4 control-label">Email for HF</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" value="<?php echo $result[0]['email_for_HF']; ?>"/>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="justification" class="col-lg-4 control-label">Justification</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="justification" name="justification" placeholder="Enter Justification" title="Please enter justification" value="<?php echo $result[0]['justification']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="rejection" class="col-lg-4 control-label">Rejected by Clinic <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                         <input type="radio" class="isRequired" id="rejectionYes" name="rejection" value="yes" title="Please check rejection" <?php echo ($result[0]['rejection']=='yes')?"checked='checked'":""?>> Yes
                         </label>
                         <label class="radio-inline">
                          <input type="radio" id="rejectionNo" name="rejection" value="no" title="Please check rejection" <?php echo ($result[0]['rejection']=='no')?"checked='checked'":""?>> No
                         </label>
                        </div>
                    </div>
                  </div>                                    
               </div>
              </div>
             <div class="row">
                <div class="col-md-12"><h4><a id="lra" href="javascript:void(0);" onclick="resultToggler('+');">Lab/Result Details <i class="fa fa-plus"></i></a></h4></div>
             </div>
             
            <div id="toogleResultDiv" class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Lab Details</h3>
              <div class="pull-right"><a href="javascript:void(0);" onclick="showModal('facilitiesLabModal.php',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labName" class="col-lg-4 control-label">Lab Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labName" name="labName" placeholder="Enter Lab Name" title="Please enter lab name" value="<?php echo $result[0]['lab_name']; ?>"/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labContactPerson" class="col-lg-4 control-label">Lab Contact Person </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labContactPerson" name="labContactPerson" placeholder="Enter Lab Contact Person Name" title="Please enter lab contact person name" value="<?php echo $result[0]['lab_contact_person']; ?>"/>
                        </div>
                    </div>
                   </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labPhoneNo" class="col-lg-4 control-label">Phone Number </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labPhoneNo" name="labPhoneNo" placeholder="Enter Lab Phone No." title="Please enter lab phone no." value="<?php echo $result[0]['lab_phone_no']; ?>"/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Sample Received at Testing Lab</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Select Sample Received Date" title="Select sample received date" value="<?php echo $result[0]['date_sample_received_at_testing_lab']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Sample Testing Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleTestedOn" name="sampleTestedOn" placeholder="Select Sample Testing Date" title="Select sample testing date" value="<?php echo $result[0]['lab_tested_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Results Dispatched</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Select Result Dispatched Date" title="Select result dispatched date" value="<?php echo $result[0]['date_results_dispatched']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="reviewedBy" class="col-lg-4 control-label">Reviewed By</label>
                        <div class="col-lg-7">
                        <input type="hidden" class="form-control" id="reviewedBy" name="reviewedBy" readonly="readonly" placeholder="Enter Reviewed By Name" title="Please enter reviewed by name" value="<?php echo $uId; ?>"/>
                        <input type="text" class="form-control readonly" placeholder="Enter Reviewed By Name" readonly="readonly" value="<?php echo $uName; ?>"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Reviewed Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="reviewedOn" name="reviewedOn" placeholder="Select Reviewed Date" title="Select reviewed date" value="<?php echo $result[0]['result_reviewed_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12"><h4>Result Details</h4></div>
                </div>
                 
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="logValue" class="col-lg-4 control-label">Log Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="logValue" name="logValue" placeholder="Enter Log Value" title="Please enter log value" value="<?php echo $result[0]['log_value']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="absoluteValue" class="col-lg-4 control-label">Absolute Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="absoluteValue" name="absoluteValue" placeholder="Enter Absolute Value" title="Please enter absolute value" value="<?php echo $result[0]['absolute_value']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="textValue" class="col-lg-4 control-label">Text Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="textValue" name="textValue" placeholder="Enter Text Value" title="Please enter text value" value="<?php echo $result[0]['text_value']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="result" class="col-lg-4 control-label">Result</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="result" name="result" placeholder="Enter Result" title="Please enter result" value="<?php echo $result[0]['result']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                 <br>
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="comments" class="col-lg-4 control-label">Comments</label>
                        <div class="col-lg-7">
                         <textarea class="form-control" id="comments" name="comments" row="4" placeholder="Enter Comments" title="Please enter comments"><?php echo $result[0]['comments']; ?></textarea>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="col-lg-4 control-label">Status</label>
                        <div class="col-lg-7">
                         <select class="form-control" id="status" name="status" title="Please select test status">
			    <?php
                            foreach($tsResult as $status){
                             ?>
                             <option value="<?php echo $status['status_id']; ?>" <?php echo ($status['status_id']==$result[0]['status']) ? 'selected="selected"':'';?>><?php echo ucwords($status['status_name']);?></option>
                             <?php
                            }
                            ?>
			  </select>
                        </div>
                    </div>
                  </div>
                 </div>
            </div>
            <!-- /.box-footer-->
          </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" id="treamentId" name="treamentId" value="<?php echo base64_encode($result[0]['treament_id']); ?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validate();return false;">Save and Next</a>
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
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
  
  <script type="text/javascript">
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editVlRequestForm'
    });
    $("#saveNext").val('save');
    if(flag){
      document.getElementById('editVlRequestForm').submit();
    }
  }
  function validate(){
    flag = deforayValidator.init({
        formId: 'editVlRequestForm'
    });
    $("#saveNext").val('next');
    if(flag){
      document.getElementById('editVlRequestForm').submit();
    }
  }
  
  $(document).ready(function() {
     $('.date').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "hh:mm TT",
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     });
     $('#sampleCollectionDate').datetimepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd-M-yy',
      timeFormat: "HH:mm:ss",
      yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
      });
       checkPatientReceivesms('<?php echo $result[0]['patient_receive_sms'];?>');
   });

    function checkPatientIsPregnant(val){
     if(val=='yes'){
      $('#arcNo').addClass('isRequired');
     }else{
       $('#arcNo').removeClass('isRequired');
     }
    }
    
    function checkPatientIsBreastfeeding(val){
     if(val=='yes'){
      $('#arvAdherence').addClass('isRequired');
     }else{
       $('#arvAdherence').removeClass('isRequired');
     }
    }
    
    function showTesting(chosenClass){
     $(".viralTestData").val('');
     $(".hideTestData").hide();
     $("."+chosenClass).show();
    }
    
    function resultToggler(symbol) {
      if(symbol == "+"){
          $("#toogleResultDiv").slideToggle();
          $("#lra").html('Lab/Result Details <i class="fa fa-minus"></i>');
          $("#lra").attr("onclick", "resultToggler('-')");
      }else{
        $("#toogleResultDiv").slideToggle();
        $("#lra").html('Lab/Result Details <i class="fa fa-plus"></i>');
        $("#lra").attr("onclick", "resultToggler('+')");
      }
    }
    
    function formToggler(symbol){
      if(symbol == "-"){
          $("#toogleFormDiv").slideToggle();
          $("#vlrfa").html('VL Request Form Details <i class="fa fa-plus"></i>');
          $("#vlrfa").attr("onclick", "formToggler('+')");
      }else{
        $("#toogleFormDiv").slideToggle();
        $("#vlrfa").html('VL Request Form Details <i class="fa fa-minus"></i>');
        $("#vlrfa").attr("onclick", "formToggler('-')");
      }
    }
    
    function setFacilityDetails(fDetails){
      $("#facilityId").val("");
      $("#facilityName").val("");
      $("#state").val("");
      $("#hubName").val("");
      facilityArray = fDetails.split("##");
      $("#facilityId").val(facilityArray[0]);
      $("#facilityName").val(facilityArray[1]);
      $("#state").val(facilityArray[2]);
      $("#hubName").val(facilityArray[3]);
      $("#district").val(facilityArray[6]);
      $("#facilityName,#state,#hubName,#district").prop('readonly',true);
      $("#clearFInfo").show();
    }
    
    function clearFacilitiesInfo(){
      $("#facilityId").val("");
      $("#facilityName").val("");
      $("#state").val("");
      $("#hubName").val("");
      $("#district").val("");
      $("#facilityName,#state,#hubName,#district").prop('readonly',false);
      $("#clearFInfo").hide();
    }
    
    function setPatientDetails(ptDetails){
      $("#artNo").val("");
      $("#sampleCode").val("");
      $("#otrId").val("");
      $("#patientName").val("");
      $("#dob").val("");
      $("#genderMale").prop('checked',false);
      $("#genderFemale").prop('checked',false);
      $(".femaleElements").show();
      $("#ageInYrs").val("");
      $("#ageInMtns").val("");
      $("#patientPhoneNumber").val("");
      $("#patientLocation").val("");
      patientArray = ptDetails.split("##");
      $("#artNo").val(patientArray[0]);
      $("#sampleCode").val(patientArray[1]);
      $("#otrId").val(patientArray[2]);
      $("#patientName").val(patientArray[3]);
      $("#dob").val(patientArray[4]);
      if(patientArray[5].toLowerCase() == 'male'){
        $("#genderMale").prop('checked',true);
        $(".femaleElements").hide();
      }else if(patientArray[5].toLowerCase() == 'female'){
        $("#genderFemale").prop('checked',true);
        $(".femaleElements").show();
      }
      $("#ageInYrs").val(patientArray[6]);
      $("#ageInMtns").val(patientArray[7]);
      $("#patientPhoneNumber").val(patientArray[8]);
      $("#patientLocation").val(patientArray[9]);
    }
    
    $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male'){
         $(".femaleElements").hide();
      }else if($(this).val() == 'female'){
        $(".femaleElements").show();
      }
    });
    function setFacilityLabDetails(fDetails){
      $("#labName").val("");
      facilityArray = fDetails.split("##");
      $("#labName").val(facilityArray[1]);
    }
    function checkPatientReceivesms(val)
    {
     if(val=='yes'){
      $('#patientPhoneNumber').addClass('isRequired');
     }else{
       $('#patientPhoneNumber').removeClass('isRequired');
     }
    }
  </script>
 <?php
 include('footer.php');
 ?>
