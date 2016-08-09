<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
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
foreach ($sampleTypeResult as $row) {
 $sampleType.='<option value="'.$row['sample_id'].'">'.ucwords($row['sample_name']).'</option>';
}
//sample code
$sQuery="select MAX(treament_id) FROM vl_request_form";
$sResult=$db->query($sQuery);
//print_r($sResult[0]['MAX(treament_id)']);die;
if($sResult[0]['MAX(treament_id)']!=''){
 $maxId = $sResult[0]['MAX(treament_id)']+1;
}else{
 $maxId = 1;
}
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
 /*   .hide-calendar .ui-datepicker-calendar {
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
 #toogleDiv{
  display:none;
 }
   </style>
   
    <section class="content-header">
      <h1>Add VL Request</h1>
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
            <form class="form-horizontal" method='post'  name='addVlRequestForm' id='addVlRequestForm' autocomplete="off"  action="addVlRequestHelper.php">
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
                        <input type='hidden' id="facilityId"  name="facilityId" class="facilityDatas" />
                        <input type='hidden' id="newfacilityName"  name="newfacilityName" />
                        <select class="form-control" id="facilityName" name="facilityName" placeholder="Health Facility Name"></select>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Facility Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired facilityDatas" id="facilityCode" name="facilityCode" placeholder="Facility Code" title="Please enter facility code"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control facilityDatas" id="state" name="state" placeholder="State" />
                        </div>
                    </div>
                  </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Linked Hub Name (If Applicable)</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control facilityDatas" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" />
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
                         <input type="hidden" name="artNo" id="artNo"/>
                        <select class="form-control" id="artNoId" name="artNoId" placeholder="ART Number"></select>
                        </div>
                    </div>
                  </div>
                  
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleCode" class="col-lg-4 control-label">Sample Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Sample Code" title="Please enter the sample code" value="<?php echo "VL".date('dmY').$maxId; ?>"/>
                        </div>
                    </div>
                  </div>
                
                  
                  
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientName" class="col-lg-4 control-label">Patient's Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="patientName" name="patientName" placeholder="patient Name" title="Please enter patient name"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="otrId" class="col-lg-4 control-label">Other Id</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="otrId" name="otrId" placeholder="Enter Other Id" title="Please enter Other Id" />
                        </div>
                    </div>
                   </div>
                   
                </div>
              
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Date of Birth</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date patientDatas readonly" readonly='readonly' id="dob" name="dob" placeholder="Enter DOB" title="Enter patient date of birth"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInYrs" class="col-lg-4 control-label">Age in years</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="ageInYrs" name="ageInYrs" placeholder="Enter age in years" title="Please enter age in years" />
                        <p class="help-block"><small>If DOB Unkown</small></p>
                        </div>
                    </div>
                  </div>
                  
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ageInMtns" class="col-lg-4 control-label">Age in months</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="ageInMtns" data-calendar="false" name="ageInMtns" placeholder="Enter Age in months" title="Please enter age in" />
                        <p class="help-block"><small>If age < 2 years </small></p>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="genderMale" class="col-lg-4 control-label">Gender</label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
                         <input type="radio" class="patientDatas" id="genderMale" name="gender" value="male" title="Please check gender"> Male
                        </label>
                        <label class="radio-inline">
                         <input type="radio" class="patientDatas" id="genderFemale" name="gender" value="female" title="Please check gender"> Female
                        </label>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="patientPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Patient Phone No." title="Please enter patient Phone No" />
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
                        <input type="text" class="form-control patientDatas readonly" readonly='readonly' id="sampleCollectionDate" name="sampleCollectionDate" placeholder="Enter Sample Collection Date" title="Please enter hub name" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleType" class="col-lg-4 control-label">Sample Type <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                         <select class="form-control isRequired patientDatas" name='sampleType' id='sampleType' title="Please select sample type">
                           <?php echo $sampleType; ?>
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
                        <input type="text" class="form-control patientDatas" id="treatPeriod" name="treatPeriod" placeholder="Enter Treatment Period" title="Please enter how long has this patient been on treatment" />
                        </div>
                    </div>
                  </div>    
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="treatmentInitiatiatedOn" class="col-lg-4 control-label">Treatment Initiated On</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date patientDatas readonly" readonly='readonly' id="treatmentInitiatiatedOn" name="treatmentInitiatiatedOn" placeholder="Treatment Initiated On" title="Please enter treatment initiated date" />
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="currentRegimen" class="col-lg-4 control-label">Current Regimen</label>
                        <div class="col-lg-7">
                        <select class="form-control patientDatas" id="currentRegimen" name="currentRegimen" placeholder="Enter Current Regimen" title="Please enter current regimen">
                         <option>--Select--</option>
                         <?php
                         foreach($artCode as $pKey=>$parentRow){
                         ?>
                         <optgroup label="<?php echo $pKey ?>">
                         <?php
                         foreach($parentRow as $key=>$val){
                         ?>
                          <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
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
                        <input type="text" class="form-control date patientDatas readonly" readonly='readonly' id="regimenInitiatedOn" name="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" />
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-12">
                    <div class="form-group">
                        <label for="treatmentDetails" class="col-lg-2 control-label">Which line of treatment is Patient on ?</label>
                        <div class="col-lg-10">
                            <textarea class="form-control patientDatas" id="treatmentDetails" name="treatmentDetails" placeholder="Enter treatment details" title="Please enter treatment details"></textarea>
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
                           <input type="radio" class="patientDatas" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);"> Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="patientDatas" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);"> No
                          </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArcNo" class="col-lg-4 control-label">If Pregnant, ARC No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="arcNo" name="arcNo" placeholder="Enter ARC no." title="Please enter arc no" />
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
                             <input type="radio" class="patientDatas" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="patientDatas" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> No
                       </label>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="ArvAdherence" class="col-lg-4 control-label">ARV Adherence </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="arvAdherence" name="arvAdherence" placeholder="Enter ARV Adherence" title="Please enter ARV adherence" />
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
                                <input type="radio" class="" id="RmTesting" name="stViralTesting" value="Routine Monitoring" title="Please check routine monitoring" onclick="showTesting('RmTesting');">
                                <strong>Routine Monitoring</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RmTesting hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date patientDatas viralTestData readonly" readonly='readonly' id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="rmTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <!--<input type="text" class="form-control" id="RmTestingSampleType" name="RmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" />-->
                        <select class="form-control patientDatas viralTestData" id="rmTestingSampleType" name="rmTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                         <?php echo $sampleType; ?>
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
                                <input type="radio" class="" id="RepeatTesting" name="stViralTesting" value="male" title="Repeat VL test after suspected treatment failure adherence counseling" onclick="showTesting('RepeatTesting');">
                                <strong>Repeat VL test after suspected treatment failure adherence counseling</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row RepeatTesting hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date patientDatas viralTestData readonly" readonly='readonly' id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="repeatTestingSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control patientDatas viralTestData" id="repeatTestingSampleType" name="repeatTestingSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <?php echo $sampleType; ?>
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
                                <input type="radio" class="" id="suspendTreatment" name="stViralTesting" value="male" title="Suspect Treatment Failure" onclick="showTesting('suspendTreatment');">
                                <strong>Suspect Treatment Failure</strong>
                            </label>						
                            </div>
                        </div>
                    </div>
                </div>
               <div class="row suspendTreatment hideTestData" style="display: none;">
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentLastVLDate" class="col-lg-4 control-label">Last VL Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date patientDatas viralTestData readonly" readonly='readonly' id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentVlValue" class="col-lg-4 control-label">VL Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-4">
                    <div class="form-group">
                        <label for="suspendTreatmentSampleType" class="col-lg-4 control-label">Sample Type</label>
                        <div class="col-lg-7">
                        <select class="form-control patientDatas viralTestData" id="suspendTreatmentSampleType" name="suspendTreatmentSampleType" placeholder="Enter Sample Type" title="Please enter sample type" >
                          <?php echo $sampleType; ?>
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
                        <input type="text" class="form-control patientDatas" id="requestClinician" name="requestClinician" placeholder="Enter Clinician" title="Please enter clinician name"/>                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="clinicianPhone" class="col-lg-4 control-label">Phone No.</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="clinicianPhone" name="clinicianPhone" placeholder="Clinician Phone No." title="Please enter phone no." />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestDate" class="col-lg-4 control-label">Request Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date patientDatas readonly" readonly='readonly' id="requestDate" name="requestDate" placeholder="Request Date" placeholder="Request Date" title="Please enter request date"/>                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlFocalPerson" class="col-lg-4 control-label">VL Focal Person</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter VL Focal Person" />                       
                        </div>
                    </div>
                  </div>                       
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="vlPhoneNumber" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="vlPhoneNumber" name="vlPhoneNumber" placeholder="VL Focal Person Phone Number" title=" Please enter vl focal person phone number" />                    
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="emailHf" class="col-lg-4 control-label">Email for HF</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control patientDatas" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" />                       
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
                           <input type="radio" class="isRequired patientDatas" id="rejectionYes" name="rejection" value="yes" title="Please check rejection"> Yes
                        </label>
                        <label class="radio-inline">
                           <input type="radio" class="patientDatas" id="rejectionNo" name="rejection" value="no" title="Please check rejection"> No
                        </label>
                        </div>
                    </div>
                  </div>                                    
                </div>
                
                <div class="row">
                   <div class="col-md-12"><h4><a href="javascript:void(0);" onclick="resultToggler();">Lab/Result Details</a></h4></div>
                 </div>
                
                <div class="box box-primary" id="toogleDiv">
                  <div class="box-header with-border">
                    <h3 class="box-title">Lab Details</h3>
                  </div>
                  
                  <div class="box-body">
                  <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labName" class="col-lg-4 control-label">Lab Name </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labName" name="labName" placeholder="Enter Lab Name" title="Please enter lab name"/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labContactPerson" class="col-lg-4 control-label">Lab Contact Person </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labContactPerson" name="labContactPerson" placeholder="Enter Lab Contact Person Name" title="Please enter lab contact person name"/>
                        </div>
                    </div>
                   </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="labPhoneNo" class="col-lg-4 control-label">Phone Number </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="labPhoneNo" name="labPhoneNo" placeholder="Enter Lab Phone No." title="Please enter lab phone no."/>
                        </div>
                    </div>
                   </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Sample Received at Testing Lab</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Select Sample Received Date" title="Select sample received date"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Sample Testing Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="sampleTestedOn" name="sampleTestedOn" placeholder="Select Sample Testing Date" title="Select sample testing date"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Date Results Dispatched</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Select Result Dispatched Date" title="Select result dispatched date"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="reviewedBy" class="col-lg-4 control-label">Reviewed By</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="reviewedBy" name="reviewedBy" placeholder="Enter Reviewed By Name" title="Please enter reviewed by name"/>
                        </div>
                    </div>
                  </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Reviewed Date</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control date readonly" readonly='readonly' id="reviewedOn" name="reviewedOn" placeholder="Select Reviewed Date" title="Select reviewed date"/>
                        </div>
                    </div>
                  </div>
                </div>
                 <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="justification" class="col-lg-4 control-label">Justification</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="justification" name="justification" placeholder="Enter Justification" title="Please enter justification"/>
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
                          <input type="text" class="form-control" id="logValue" name="logValue" placeholder="Enter Log Value" title="Please enter log value"/>
                          </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="absoluteValue" class="col-lg-4 control-label">Absolute Value</label>
                          <div class="col-lg-7">
                          <input type="text" class="form-control" id="absoluteValue" name="absoluteValue" placeholder="Enter Absolute Value" title="Please enter absolute value"/>
                          </div>
                      </div>
                    </div>
                  </div>
                   <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="textValue" class="col-lg-4 control-label">Text Value</label>
                          <div class="col-lg-7">
                          <input type="text" class="form-control" id="textValue" name="textValue" placeholder="Enter Text Value" title="Please enter text value"/>
                          </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="result" class="col-lg-4 control-label">Result</label>
                          <div class="col-lg-7">
                          <input type="text" class="form-control" id="result" name="result" placeholder="Enter Result" title="Please enter result"/>
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
                           <textarea class="form-control" id="comments" name="comments" row="4" placeholder="Enter Comments" title="Please enter comments"></textarea>
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
                               <option value="<?php echo $status['status_id']; ?>"><?php echo ucwords($status['status_name']);?></option>
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
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
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
     
     $("#facilityName").select2({
      allowClear: true,
      placeholder: "Enter Facility Name",
      ajax: {
      //url: "https://api.github.com/search/repositories",
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
     
     $("#artNoId").select2({
      allowClear: true,
      placeholder: "Enter ART Number",
      ajax: {
      //url: "https://api.github.com/search/repositories",
      url: "getArtNumberSearch.php",
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
      if (e.params.data.id==0) {
        $('.facilityDatas').val('');
        $('.facilityDatas').removeAttr('readonly', true);
        $("#newfacilityName").val(e.params.data.text);
      }else{
       $("#facilityId").val(e.params.data.id);
       $("#facilityCode").val(e.params.data.facilityCode);
       //$("#country").val(e.params.data.country);
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
     
     //patiend details populate
     $('#artNoId').on("select2:select", function(e) {
      if (e.params.data.patient==0) {
        $('.patientDatas').val('');
        $("#artNo").val(e.params.data.text);
      }else{
       $("#artNo").val(e.params.data.text);
       $("#patientName").val(e.params.data.patientName);
       $("#dob").val(e.params.data.dob);
       $("#otrId").val(e.params.data.otherId);
       $("#ageInYrs").val(e.params.data.ageYrs);
       $("#ageInMtns").val(e.params.data.ageMnts);
       if(e.params.data.gender=='male'){
        $("#genderMale").attr('checked', 'checked');
       }if(e.params.data.gender=='female'){
        $("#genderFemale").attr('checked', 'checked');
       }
       $("#patientPhoneNumber").val(e.params.data.phNum);
       $("#sampleCollectionDate").val(e.params.data.sampleCollectDate);
       $("#sampleType").val(e.params.data.sampleType);
       $("#treatPeriod").val(e.params.data.trtPeriod);
       $("#treatmentInitiatiatedOn").val(e.params.data.trtInitiateDate);
       $("#currentRegimen").val(e.params.data.crntRegimen);
       $("#regimenInitiatedOn").val(e.params.data.regimenInitiatedOn);
       $("#treatmentDetails").val(e.params.data.details);
       if(e.params.data.pregnant=='no'){
        $("#pregNo").attr('checked', 'checked');
       }if(e.params.data.pregnant=='yes'){
        $("#pregYes").attr('checked', 'checked');
       }if(e.params.data.breastFeed=='yes'){
        $("#breastfeedingYes").attr('checked', 'checked');
       }if(e.params.data.breastFeed=='no'){
        $("#breastfeedingNo").attr('checked', 'checked');
       }
       $("#arcNo").val(e.params.data.arcNo);
       $("#arvAdherence").val(e.params.data.arvAdherence);
       $("#rmTestingLastVLDate").val(e.params.data.rmTestLastDate);
       $("#rmTestingVlValue").val(e.params.data.rmTestVlValue);
       $("#rmTestingSampleType").val(e.params.data.rmSampleType);
       $("#repeatTestingLastVLDate").val(e.params.data.repeatTestingLastVLDate);
       $("#repeatTestingVlValue").val(e.params.data.repeatTestingVlValue);
       $("#repeatTestingSampleType").val(e.params.data.repeatTestingSampleType);
       $("#suspendTreatmentLastVLDate").val(e.params.data.suspendTreatmentLastVLDate);
       $("#suspendTreatmentVlValue").val(e.params.data.suspendTreatmentVlValue);
       $("#suspendTreatmentSampleType").val(e.params.data.suspendTreatmentSampleType);
       $("#requestClinician").val(e.params.data.requestClinician);
       $("#clinicianPhone").val(e.params.data.clinicianPhone);
       $("#requestDate").val(e.params.data.requestDate);
       $("#vlFocalPerson").val(e.params.data.vlFocalPerson);
       $("#vlPhoneNumber").val(e.params.data.vlPhoneNumber);
       $("#emailHf").val(e.params.data.emailHf);
       if(e.params.data.rejection=='yes'){
        $("#rejectionYes").attr('checked', 'checked');
       }if(e.params.data.rejection=='no'){
        $("#rejectionNo").attr('checked', 'checked');
       }
      }
     });
     $('#artNoId').on("select2:unselect", function(e) {
      $('.patientDatas').val('');
      $(".patientDatas").attr('checked', false);
     });
     
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
      timeFormat: "HH:mm",
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
    function showTesting(chosenClass)
    {
     $(".viralTestData").val('');
     $(".hideTestData").hide();
     $("."+chosenClass).show();
    }
    
    function resultToggler() {
      $("#toogleDiv").slideToggle();
    }
  </script>
 <?php
 include('footer.php');
 ?>
