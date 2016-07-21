<?php
//print_r($result);die;
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$id=base64_decode($_GET['id']);
$fQuery="SELECT * from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where treament_id=$id";
$result=$db->query($fQuery);

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

if(isset($result[0]['date_results_dispatched']) && trim($result[0]['date_results_dispatched'])!='' && trim($result[0]['date_results_dispatched'])!='0000-00-00'){
 $result[0]['date_results_dispatched']=$general->humanDateFormat($result[0]['date_results_dispatched']);
}else{
 $result[0]['date_results_dispatched']='';
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
$sampleType='<option value="">--Select--</option>';

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
   </style>
   <link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" media="all" type="text/css" href="assets/css/jquery-ui-timepicker-addon.css" />
    <section class="content-header">
      <h1>Edit Vl Request</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Vl Request</li>
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
            <form class="form-horizontal" method='post' name='addVlRequestForm' id='addVlRequestForm' autocomplete="off"  action="editVlRequestHelper.php">
              <div class="box-body">                 
              <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Facility Information</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Health Facility Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type='hidden' id="facilityId"  name="facilityId" class="facilityDatas" value="<?php echo $result[0]['facility_id']; ?>" />
                        <input type='hidden' id="newfacilityName"  name="newfacilityName" />
                        <select class="form-control" id="facilityName" name="facilityName" placeholder="Health Facility Name"></select>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Facility Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired facilityDatas" id="facilityCode" name="facilityCode" placeholder="Facility Code" title="Please enter facility code" value="<?php echo $result[0]['facility_code']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="country" class="col-lg-4 control-label">Country</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control facilityDatas" id="country" name="country" placeholder="Country" value="<?php echo $result[0]['country']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control facilityDatas" id="state" name="state" placeholder="State" value="<?php echo $result[0]['state']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Hub Name</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control facilityDatas" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" value="<?php echo $result[0]['hub_name']; ?>"/>
                        </div>
                    </div>
                  </div>                   
                </div>              
              </div>
            </div>
            <!-- /.box-footer-->
          </div>
              
                  <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Patient Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="artNo" class="col-lg-4 control-label">Unique ART No. <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <select class="form-control isRequired" id="artNo" name="artNo" placeholder="ART Number" title="Please enter art number">
                         <option>--Select--</option>
                         <?php
                         foreach($artCode as $pKey=>$parentRow){
                         ?>
                         <optgroup label="<?php echo $pKey ?>">
                         <?php
                         foreach($parentRow as $key=>$val){
                         ?>
                          <option value="<?php echo $key; ?>" <?php echo ($result[0]['art_no']==$key)?"selected='selected'":""?>><?php echo $val; ?></option>
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
                        <label for="patientName" class="col-lg-4 control-label">Patient's Name <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="patientName" name="patientName" placeholder="patient Name" title="Please enter patient name" value="<?php echo $result[0]['patient_name']; ?>"/>
                        <input type="hidden" class="form-control isRequired" id="treamentId" name="treamentId" placeholder="patient Name" title="" value="<?php echo base64_encode($result[0]['treament_id']); ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Date of Birth</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime " readonly='readonly' id="dob" name="dob" placeholder="Enter DOB" title="Enter patient date of birth" value="<?php echo $result[0]['patient_dob']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="otrId" class="col-lg-4 control-label">Other Id</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="otrId" name="otrId" placeholder="Enter Other Id" title="Please enter Other Id" value="<?php echo $result[0]['other_id']; ?>"/>
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
                        <p class="help-block"><small>If age < 2 years </small></p>
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="genderMale" class="col-lg-4 control-label">Gender</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                         <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo ($result[0]['gender']=='male')?"checked='checked'":""?>> Male
                        </label>
                        <label class="radio-inline">
                         <input type="radio" id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo ($result[0]['gender']=='female')?"checked='checked'":""?>> Female
                        </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Patient Phone No." title="Please enter patient Phone No" value="<?php echo $result[0]['patient_phone_number']; ?>"/>
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
                        <input type="text" class="form-control" readonly='readonly' id="sampleCollectionDate" name="sampleCollectionDate" placeholder="Enter Sample Collection Date" title="Please enter sample collection date" value="<?php echo $result[0]['sample_collection_date']; ?>" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Sample Type <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <select class="form-control isRequired" name='sampleType' id='sampleType' title="Please select sample type">
                           <option value="">--Select--</option>
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
                        <label for="treatmentInitiatiatedOn" class="col-lg-4 control-label">Treatment Initiatiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="treatmentInitiatiatedOn" name="treatmentInitiatiatedOn" placeholder="Treatment Initiatiated On" title="Please enter treatment initiatiated date" value="<?php echo $result[0]['treatment_initiated_date']; ?>" />
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="currentRegimen" class="col-lg-4 control-label">Current Regimen</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control " id="currentRegimen" name="currentRegimen" placeholder="Enter Current Regimen" title="Please enter current regimen" value="<?php echo $result[0]['current_regimen']; ?>" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Current Regimen Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="regimenInitiatedOn" name="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" value="<?php echo $result[0]['date_of_initiation_of_current_regimen']; ?>"/>
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
                <div class="row">
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
                    <div class="col-md-6">
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
                        <label for="ArvAdherence" class="col-lg-4 control-label">ARV Adherence </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="arvAdherence" name="arvAdherence" placeholder="Enter ARV Adherence" title="Please enter ARV adherence" value="<?php echo $result[0]['arv_adherence']; ?>" />
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
              <small>(please tick one):(To be completed by clinician)</small>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             <div class="row">                
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-lg-12">
                            <label class="radio-inline">
                                <!--<input type="checkbox" class="isRequired" id="RmTesting" name="rmViralTesting" value="Routine Monitoring" title="Please check routine monitoring" onclick="showTesting('RmTesting');">-->
                                <strong>Routine Monitoring</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RmTesting ">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['routine_monitoring_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['routine_monitoring_value']; ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <!--<input type="text" class="form-control" id="RmTestingSampleType" name="RmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />-->
                        <select class="form-control" id="rmTestingSampleType" name="rmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                         <option value="">--Select--</option>
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
                                <!--<input type="checkbox" class="isRequired" id="RepeatTesting" name="repeatViralTesting" value="male" title="Repeat VL test after suspected treatment failure adherence counseling" onclick="showTesting('RepeatTesting');">-->
                                <strong>Repeat VL test after suspected treatment failure adherence counseling</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RepeatTesting">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['vl_treatment_failure_adherence_counseling_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['vl_treatment_failure_adherence_counseling_value']; ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control" id="repeatTestingSampleType" name="repeatTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <option value="">--Select--</option>
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
                                <!--<input type="checkbox" class="isRequired" id="suspendTreatment" name="stViralTesting" value="male" title="Suspect Treatment Failure" onclick="showTesting('suspendTreatment');">-->
                                <strong>Suspect Treatment Failure</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row suspendTreatment">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $result[0]['suspected_treatment_failure_last_vl_date']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $result[0]['suspected_treatment_failure_value']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control" id="suspendTreatmentSampleType" name="suspendTreatmentSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <option value="">--Select--</option>
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
                        <input type="text" class="form-control dateTime" readonly='readonly' id="requestDate" name="requestDate" placeholder="Request Date" placeholder="Request Date" title="Please enter request date" value="<?php echo $result[0]['request_date']; ?>"/>
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
                        <label for="sampleReceivedOn" class="col-lg-4 control-label">Date sample received at testing Lab</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received On" title="Please enter sample received on" value="<?php echo $result[0]['date_sample_received_at_testing_lab']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="despachedOn" class="col-lg-4 control-label">Date Results Despatched</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control dateTime" readonly='readonly' id="despachedOn" name="despachedOn" placeholder="Results Despatched" value="<?php echo $result[0]['date_results_dispatched']; ?>"/>
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="rejection" class="col-lg-4 control-label">Rejection <span class="mandatory">*</span></label>
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
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
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
        formId: 'addVlRequestForm'
    });
    
    if(flag){
      document.getElementById('addVlRequestForm').submit();
    }
  }
  
  $(document).ready(function() {
     var data = [{ id: <?php echo $result[0]['facility_id']; ?>, text: '<?php echo $result[0]['facility_name']; ?>' }];

  $("#facilityName").select2({
    data: data
  })
  $('.facilityDatas').attr('readonly', true);

     $("#facilityName").select2({
      allowClear: true,
      placeholder: "Enter Facility Name",
      ajax: {
      url: "getFacilitySearch.php",
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term, // search term
          page: 10
        };
      },
       processResults: function (data, params) {
        // parse the results into the format expected by Select2
        // since we are using custom formatting functions we do not need to
        // alter the remote JSON dsata, except to indicate that infinite
        // scrolling can be used
        params.page = 10;
        return {
          results: data.result,        
        };
       },
      },
      escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
      minimumInputLength: 1
     });
   
    
     $('#facilityName').on("select2:select", function(e) {
      //console.log(e.params.data);
      if (e.params.data.id==0) {
        $('.facilityDatas').val('');
        $('.facilityDatas').removeAttr('readonly', true);
        $("#newfacilityName").val(e.params.data.text);
      }else{
       $("#facilityId").val(e.params.data.id);
       $("#facilityCode").val(e.params.data.facilityCode);
       $("#country").val(e.params.data.country);
       $("#state").val(e.params.data.state);
       $("#hubName").val(e.params.data.hubName);
       $('.facilityDatas').attr('readonly', true);
       $("#newfacilityName").val('');
      }
     });
     $('#facilityName').on("select2:unselect", function(e) {
      $('.facilityDatas').val('');
      $('.facilityDatas').removeAttr('readonly', true);
      $("#newfacilityName").val('');
     });
     
     $('.dateTime').datepicker({
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
    
  </script>
 <?php
 include('footer.php');
 ?>
